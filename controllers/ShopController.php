<?php
class ShopController
{
	const MAX_STACK_SHOWN = 10;

	public function itemsJSON($app)
	{
		$items = Description::find('all');
		$items = array_unique(array_map(function($desc) { return $desc->name; }, $items));
		$app->output->json($items);
	}

	public function featuredItems($app)
	{
		$request = $app->router->flight->request();
		$tags = array_values($request->query->getData()); // grab internal_name's
		$tags = array_filter($tags, function($tag) { return strcmp($tag, '') != 0; });

		$listings = Listing::find('all', array(
			'conditions' => array('stage = ? AND featured = 1', Listing::STAGE_LIST),
			'include' => 'description'
			));

		// Sort all listings by price DESC
		usort($listings, function ($a, $b) {
			return ($b->price) - ($a->price);
		});

		$categories = array();
		$categories = Tag::find('all', array(
			'select' => 'DISTINCT category, category_name',
			'conditions' => array('category NOT IN (?)', array('ItemSet', 'Weapon', 'Tournament', 'TournamentTeam'))));
		$tags = Tag::find('all');

		if(empty($listings))
			$app->output->alert('There are currently no featured listings.');

		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('featured', 'Featured Items');
		$app->output->setTitle('Featured Items');
		$app->output->setActiveTab('featured');
		$app->output->render('shop.featured', ['listings' => $listings, 'categories' => $categories, 'tags' => $tags]);
	}

	public function browseItems($app)
	{
		$request = $app->router->flight->request();
		$categories = array();
		$categories = Tag::find('all', array(
			'select' => 'DISTINCT category, category_name',
			'conditions' => array('category NOT IN (?)', array('ItemSet', 'Weapon', 'Tournament', 'TournamentTeam'))));
		$tags = Tag::find('all');

		$query_string = '';
		foreach($request->query as $idx => $val) {
			$query_string .= '&'.$idx.'='.urlencode($val);
		}

		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('browse', 'Browse Items');
		$app->output->setTitle('Browse Items');
		$app->output->setActiveTab('browse');
		$app->output->render('shop.browse', ['categories' => $categories, 'tags' => $tags, 'query_string' => $query_string, 'query' => $request->query]);
	}

	// Shows an individual listing's details
	public function showListing($app, $listing_id)
	{
		try {
			$listing_id_dec = $app->hashids->decrypt($listing_id);
			$listing = Listing::find($listing_id_dec, array('include' => 'description'));

			$app->output->addBreadcrumb('', 'CSGOShop');
			$app->output->addBreadcrumb('browse', 'Browse Items');
			$app->output->addBreadcrumb('listing/'.$listing_id, $listing->description->name);
			
			$app->output->setTitle($listing->description->name);
			$app->output->setActiveTab('browse');
			$app->output->render('shop.listing', ['listing' => $listing]);
		}
		catch(Hashids_Invalid $e) {
			$app->logger->log('Listing ID given was invalid', 'ERROR', array('object' => 'Listing', 'id' => $id, 'pathway' => 'listing'), 'user');
			$app->output->notFound();
		}
		catch(ActiveRecord\RecordNotFound $e) {
			$app->logger->log('No such Listing found', 'ERROR', array('object' => 'Listing', 'id' => $id, 'id_dec' => $listing_id_dec, 'pathway' => 'listing'), 'user');
			$app->output->notFound();	
		}
	}

	// Shows stackable listings
	public function showStackListing($app, $listing_id)
	{
		try {
			$listing_id_dec = $app->hashids->decrypt($listing_id);
			$listing = Listing::find($listing_id_dec, array('include' => 'description'));
			$request = $app->router->flight->request();

			if($listing->description->stackable == 1) {
				$listings = Listing::find('all', array(
					'conditions' => array('description_id = ? AND stage = ?', $listing->description->id, Listing::STAGE_LIST),
					'order' => 'price ASC'));

				$page = $request->query->p ?: 0;
				$offset = $page * self::MAX_STACK_SHOWN;
				$total = ceil( count($listings) / self::MAX_STACK_SHOWN );

				if($offset < 0 || $page > $total)
					$app->output->redirect('/listings/'.$listing_id);


				$listings = array_slice($listings, $offset, self::MAX_STACK_SHOWN);
			}
			else
				$app->output->redirect('/listing/'.$listing_id);	


			$app->output->addBreadcrumb('', 'CSGOShop');
			$app->output->addBreadcrumb('browse', 'Browse Items');
			$app->output->addBreadcrumb('listings/'.$listing_id, $listing->description->name.' (Bulk)');
			
			$app->output->setTitle($listing->description->name);
			$app->output->setActiveTab('browse');
			$app->output->render('shop.stacklisting', ['listings' => $listings, 'page_num' => $page, 'total' => $total]);
		}
		catch(Hashids_Invalid $e) {
			$app->logger->log('Listing ID given was invalid', 'ERROR', array('object' => 'Listing', 'id' => $id, 'pathway' => 'listing'), 'user');
			$app->output->notFound();
		}
		catch(ActiveRecord\RecordNotFound $e) {
			$app->logger->log('No such Listing found', 'ERROR', array('object' => 'Listing', 'id' => $id, 'id_dec' => $listing_id_dec, 'pathway' => 'listing'), 'user');
			$app->output->notFound();	
		}
	}

	// AJAX Endpoint for bulk listing requests
	public function requestBulkListing($app)
	{
		if(!$app->user->isLoggedIn())
			$app->output->redirect('/account/login');

		$userid = $app->user->id;
		$request = $app->router->flight->request();
		if($request->method != 'POST')
			$app->output->json(array('error' => true, 'message' => 'Invalid entry'), 400);

		try {
			// SANITY CHECKS
			// Grab item information from user's inventory
			$inventory = $app->steam->getInventory($userid, true);
			$itemid = $request->data->item_id ?: -1;
			if(!isset($inventory[$itemid]))
				throw new Listing_MissingItem;
			$item = $inventory[$itemid];

			// Check to see if items are stackable
			if($item->desc->stackable != 1)
				throw new Bulk_NotStackable;

			// Grab all items for bulk listing
			$items = array_filter($inventory, function($i) use ($item) { return $i->desc->id == $item->desc->id; });
			// Filter out those already in pending listings
			foreach($items as $idx => $item) {
				$flag = Listing::find('all', array(
					'conditions' => array('item_id = ? AND user_id = ? AND stage = ?', $item->id, $userid, Listing::STAGE_REQUEST)));
				$flag = !empty($flag);
				if($flag)
					unset($items[$idx]);
			}

			// Grab enough to fill quantity
			$quantity = $request->data->quantity ?: 1;
			if(empty($items) || count($items) < $quantity)
				throw new Bulk_InsufficientStock('There are not enough items to fill this request. You currently have '.count($items).' '.$item->desc->name.'s.');
			$items = array_slice($items, 0, $quantity);

			// Loop through storage bots to make sure one has enough space
			$bots = Bot::find('all', array(
				'conditions' => array('type = ? AND status = ?', Bot::TYPE_STORAGE, Bot::STATUS_ACTIVE)));
			$flag = false;
			foreach($bots as $idx => $bot) {
				// Too many API calls makes this too volatile to be viable
				// $bot_inventory = $app->steam->getInventory($bot->id, true);
				$bot_inventory = Listing::count('all', array(
					'conditions' => array('bot_id = ? AND stage = ?', $bot->id, Listing::STAGE_LIST)));
				if($bot_inventory + $quantity < $app->config->get('steam.maxInventory')) {
					$flag = true;
					break;
				}
			}
			if(!$flag)
				throw new Listing_StorageFull;

			// Create a listing for each item
			// First, create a parent listing to stack the rest
			$price = $items[0]->desc->price_preset;
			$parent_item = array_pop($items);
			$parent_listing = Listing::create([
				'user_id' => $userid,
				'item_id' => (string)$parent_item->id,
				'description_id' => $parent_item->desc->id,
				'price' => $price,
				'message' => '',
				'screenshot_playside' => null,
				'screenshot_backside' => null,
				'note_playside' => null,
				'note_backside' => null 
			]);
			foreach($items as $idx => $item) {
				Listing::create([
					'user_id' => $userid,
					'item_id' => (string)$item->id,
					'description_id' => $item->desc->id,
					'price' => $price,
					'message' => '',
					'screenshot_playside' => null,
					'screenshot_backside' => null,
					'note_playside' => null,
					'note_backside' => null,
					'parent_listing_id' => $parent_listing->id
				]);
			}

			$app->pusher->trigger('bots', 'requestBulkListing', array(
				'listing_id' => (string)$parent_listing->id,
				'user_id' => (string)$userid,
				'items' => array_map(function($item) { return (string)$item->id; }, array_merge($items, array($parent_item)))
				));

			$notification = Notification::create([
				'user_id' => $parent_listing->user_id,
				'receiver_id' => $parent_listing->user_id,
				'title' => 'APPROVAL',
				'body' => '**Bulk Listing #'.$app->hashids->encrypt($parent_listing->id).' ('.$parent_listing->description->name.' x '.$quantity.') has been requested!** 
A bot will send you a trade offer shortly to store your item.'
			]);

			$app->output->json(array('error' => false, 'listing_id' => $app->hashids->encrypt($parent_listing->id)));
		}
		catch(Exception $e) {
			$app->logger->log('Could not request Bulk Listing ('.get_class($e).')', 'ERROR', array('pathway' => 'requestBulkListing', 'exception' => $e), 'user');

			if($e instanceof SteamAPIException)
				$app->output->json(array('error' => true, 'type' => 'warning', 'message' => 'Steam API could not be reached.'), 503);
			else if($e instanceof Listing_MissingItem)
				$app->output->json(array('error' => true, 'type' => 'warning', 'message' => 'That item does not exist in your inventory.'), 400);
			else if($e instanceof Listing_StorageFull)
				$app->output->json(array('error' => true, 'type' => 'warning', 'message' => 'There is not enough room in a single storage bot to store your items.'), 400);
			else if($e instanceof Bulk_InsufficientStock)
				$app->output->json(array('error' => true, 'type' => 'warning', 'message' => $e->getMessage()), 400);
			else if($e instanceof Bulk_NotUniform)
				$app->output->json(array('error' => true, 'type' => 'warning', 'message' => 'All items must be the same to send a bulk request.'), 400);
			else if($e instanceof Bulk_NotStackable)
				$app->output->json(array('error' => true, 'type' => 'warning', 'message' => 'Unique items are not allowed in bulk requests.'), 400);
			else
				throw $e;
		}
	}

	// AJAX Endpoint for listing requests
	public function requestListing($app)
	{
		if(!$app->user->isLoggedIn())
		{
			$app->output->redirect('/account/login');
		}

		try {
			if(isset($_SERVER["CONTENT_LENGTH"])) {
				if($_SERVER["CONTENT_LENGTH"]>((int)ini_get('post_max_size')*1024*1024)) {
					throw new Listing_InvalidDetails('Your request has exceeded file limits. Try to limit screenshots to under 2MB each.');
				}
			}
			
			// Loop through storage bots to make sure one has enough space
			$bots = Bot::find('all', array(
				'conditions' => array('type = ? AND status = ?', Bot::TYPE_STORAGE, Bot::STATUS_ACTIVE)));
			$flag = false;
			foreach($bots as $idx => $bot) {
				// Too many API calls makes this too volatile to be viable
				// $bot_inventory = $app->steam->getInventory($bot->id, true);
				$bot_inventory = Listing::count('all', array(
					'conditions' => array('bot_id = ? AND stage = ?', $bot->id, Listing::STAGE_LIST)));
				if($bot_inventory + 1 < $app->config->get('steam.maxInventory')) {
					$flag = true;
					break;
				}
			}
			if(!$flag)
				throw new Listing_StorageFull;

			// Grab item information from user's inventory
			$userid = $app->user->id;
			$inventory = $app->steam->getInventory($userid, true);
			$request = $app->router->flight->request();
			$itemid = $request->data->item_id ?: -1;
			if(!isset($inventory[$itemid]))
				throw new Listing_MissingItem;
			$item = $inventory[$itemid];

			$flag = Listing::find('all', array(
				'conditions' => array('item_id = ? AND user_id = ? AND stage = ?', $itemid, $userid, Listing::STAGE_REQUEST)));
			$flag = !empty($flag);
			if($flag)
				throw new Listing_Duplicate;


			// Inspect HTTP request for POST vars to add into DB
			$price = $request->data->price ?: 0;
			$message = $request->data->message ?: '';
			if(!empty($item->desc->price_preset))
				$price = $item->desc->price_preset;
			if($price == 0)
				throw new Listing_InvalidDetails('You must include a price for your listing');
			
			if($item->desc->stackable == 1) {
				// Stackable items don't require screenshots
				$details = array(
					'screenshot_playside' => null,
					'screenshot_backside' => null,
					'note_playside' => null,
					'note_backside' => null 
				);
			}
			else {
				$details = array(
					'screenshot_playside' => $request->data->screenshot_playside ?: null,
					'screenshot_backside' => $request->data->screenshot_backside ?: null,
					'note_playside' => $request->data->note_playside ?: null,
					'note_backside' => $request->data->note_backside ?: null,
					'inspect_url' => 
						preg_replace(
							array('/%owner_steamid%/', '/%assetid%/'), 
							array($userid, $item->asset_id), 
							$item->desc->inspect_url_template
						)
				);
				if(empty($details['screenshot_playside']) || empty($details['note_playside']))
					throw new Listing_InvalidDetails('You must provide a playside screenshot and pattern description');

				// 2MB = 2000000B limit on screenshots since POST request vars are limited
				// Added a bit for wiggle room since base64 encoding adds 33% size
				if((int) (strlen(rtrim($details['screenshot_playside'], '=')) * 3 / 4) > 2100000)
					throw new Listing_InvalidDetails('You have uploaded a playside screenshot that is too large');
				if(!empty($details['screenshot_backside']) && (int) (strlen(rtrim($details['screenshot_backside'], '=')) * 3 / 4) > 2100000)
					throw new Listing_InvalidDetails('You have uploaded a backside screenshot that is too large');

				// Upload screenshots to imgur
				if(!empty($details['screenshot_playside'])) {
					$play = $app->imgur->upload()->string($details['screenshot_playside'])['data'];
					if(isset($play['error']))
						throw new ImgurAPIException($play['error']);
					
					$details['screenshot_playside'] = isset($play['link']) ? $play['link'] : null;
				}

				if(!empty($details['screenshot_backside'])) {
					$back = $app->imgur->upload()->string($details['screenshot_backside'])['data'];
					if(isset($back['error']))
						throw new ImgurAPIException($back['error']);

					$details['screenshot_backside'] = isset($back['link']) ? $back['link'] : null;
				}
			}


			// Create listing
			$listing = Listing::create(array_merge([
				'user_id' => $userid,
				'item_id' => (string)$item->id,
				'description_id' => $item->desc->id,
				'price' => $price,
				'message' => $message,
			], $details));

			$app->pusher->trigger('bots', 'requestListing', array(
				'listing_id' => (string)$listing->id,
				'user_id' => (string)$userid,
				'item_id' => (string)$item->id
				));

			$notification = Notification::create([
				'user_id' => $listing->user_id,
				'receiver_id' => $listing->user_id,
				'title' => 'APPROVAL',
				'body' => '**Listing #'.$app->hashids->encrypt($listing->id).' ('.$listing->description->name.') has been requested!** 
A bot will send you a trade offer shortly to store your item.'
			]);
			$app->output->json(array('error' => false, 'listing_id' => $app->hashids->encrypt($listing->id)));
		}
		catch(Exception $e) {
			$app->logger->log('Could not request Listing ('.get_class($e).')', 'ERROR', array('pathway' => 'requestListing', 'exception' => $e), 'user');

			if($e instanceof SteamAPIException)
				$app->output->json(array('error' => true, 'type' => 'warning', 'message' => 'Steam API could not be reached.'), 503);
			else if($e instanceof ImgurAPIException)
				$app->output->json(array('error' => true, 'type' => 'warning', 'message' => 'imgur API did not accept the uploaded files ('.$e->getMessage().').'), 400);
			else if($e instanceof Listing_MissingItem)
				$app->output->json(array('error' => true, 'type' => 'warning', 'message' => 'Inventory item does not exist.'), 400);
			else if($e instanceof Listing_Duplicate)
				$app->output->json(array('error' => true, 'type' => 'warning', 'message' => 'A listing for that item exists.'), 400);
			else if($e instanceof Listing_StorageFull)
				$app->output->json(array('error' => true, 'type' => 'warning', 'message' => 'There is not enough room in a single storage bot to store your items.'), 400);
			else if($e instanceof Listing_InvalidDetails)
				$app->output->json(array('error' => true, 'type' => 'warning', 'message' => 'Your listing request is invalid: '.$e->getMessage().'.'), 400);
			else
				throw $e;
		}
	}

	// Route for user to manually request cancellation for a listing
	public function takedownListing($app, $listing_id)
	{
		if(!$app->user->isLoggedIn())
		{
			$app->output->redirect('/account/login');
		}

		try {
			$listing_id_dec = $app->hashids->decrypt($listing_id);
			$listing = Listing::find($listing_id_dec);
			if($listing->user_id != $app->user->id)
				return;

			$listings = array($listing);
			$children = $listing->children;
			if(!empty($children))
				$listings = array_merge($listings, $children);

			$auto_flag = false;
			foreach($listings as $idx => $l) {
				$l->request_takedown = 1;
				// Cancellation auto-approved for listings where
				// user has yet to hand over item
				if($l->stage == Listing::STAGE_REQUEST) {
					$l->stage = Listing::STAGE_DELETE;
					$auto_flag = true;
				}

				$l->save();
			}

			if($auto_flag) {
				if(count($listings) > 1) {
					$notification = Notification::create([
						'user_id' => $listing->user_id,
						'receiver_id' => $listing->user_id,
						'title' => 'DELETED',
						'body' => '**Bulk Listing #'.$app->hashids->encrypt($listing->id).' ('.$listing->description->name.' x '.(count($children) + 1).') has been deleted!** 
This is a notification that confirms that the items have been refunded and the listing has been deleted.'
					]);				
				}
				else {
					$notification = Notification::create([
						'user_id' => $listing->user_id,
						'receiver_id' => $listing->user_id,
						'title' => 'DELETED',
						'body' => '**Listing #'.$app->hashids->encrypt($listing->id).' ('.$listing->description->name.') has been deleted!** 
This is a notification that confirms that the item has been refunded and the listing has been deleted.'
					]);
				}
			}

			$app->output->redirect('/account/listings?cancel');
		}
		catch(Hashids_Invalid $e) {
			$app->logger->log('Listing ID given was invalid', 'ERROR', array('object' => 'Listing', 'id' => $id, 'pathway' => 'takedownListing'), 'user');
			$app->output->notFound();
		}
		catch(ActiveRecord\RecordNotFound $e) {
			$app->logger->log('No such Listing found', 'ERROR', array('object' => 'Listing', 'id' => $id, 'id_dec' => $listing_id_dec, 'pathway' => 'takedownListing'), 'user');
			$app->output->notFound();	
		}
	}

	public function addCart($app)
	{
		$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array('listings' => array(), 'bulk' => array());
		$request = $app->router->flight->request();

		if($request->method != 'POST') {
			$app->output->json(array('error' => true, 'message' => 'Invalid entry'), 400);
		}
		
		$exclude_id = $app->config->get('mode') == 'production' ? $app->user->id : -1;

		try {
			$listing_id = $request->data->id ?: -1;
			$listing_id = $app->hashids->decrypt($listing_id);
			$qty = $request->data->qty ?: 1;
			if($qty < 0)
				$app->output->json(array('error' => true, 'message' => 'Invalid quantity.'), 400);
			$added = false;

			$listing = Listing::find($listing_id);
			if($listing->description->stackable == 1) {
				
				if(empty($cart['bulk'][$listing->description_id]))
					$old_qty = 0;
				else
					$old_qty = $cart['bulk'][$listing->description_id]['qty'];

				$new_qty = $old_qty;
				$new_qty += $qty;
				$listings = Listing::find('all', array(
					'conditions' => array('stage = ? AND description_id = ? AND user_id != ?', Listing::STAGE_LIST, $listing->description_id, $exclude_id),
					'order' => 'price ASC',
					'include' => array('description')));
				$new_qty = ($new_qty > count($listings)) ? count($listings) : $new_qty;

				if($new_qty != $old_qty && $new_qty > 0) {
					$added = true;
					$cart['bulk'][$listing->description_id] = array('listing' => $listing_id, 'qty' => $new_qty);
					$remaining = count($listings) - $new_qty;
				}
				else
					$remaining = 0;
			}
			else {
				if(empty($cart['listings'][$listing_id]))
					$added = true;

				if($listing->user_id != $exclude_id) {
					$cart['listings'][$listing_id] = array('listing' => $listing_id, 'qty' => 1);
				}

				$remaining = 0;
			}

			$_SESSION['cart'] = $cart;
			$items_count = array_reduce(array_merge($_SESSION['cart']['listings'], $_SESSION['cart']['bulk']), 
					function($carry, $item) { $carry += $item['qty']; return $carry; }, 0);

			$app->output->json(array('added' => $added, 'items' => $items_count, 'remaining' => $remaining));
		}
		catch(Hashids_Invalid $e) {
			$app->logger->log('Listing ID given was invalid', 'ERROR', array('object' => 'Listing', 'id' => $id, 'pathway' => 'addCart'), 'user');
			$app->output->json(array('error' => true, 'message' => 'No such listing exists'), 400);
		}
		catch(ActiveRecord\RecordNotFound $e) {
			$app->logger->log('No such Listing found', 'ERROR', array('object' => 'Listing', 'id' => $id, 'id_dec' => $listing_id_dec, 'pathway' => 'addCart'), 'user');
			$app->output->json(array('error' => true, 'message' => 'No such listing exists'), 400);
		}
	}

	public function removeCart($app)
	{
		$request = $app->router->flight->request();
		$cart = $_SESSION['cart'];

		if($request->method != 'POST' || (empty($cart['bulk']) && empty($cart['listings']))) {
			$app->output->redirect('/');
		}
		try {
			$type = $request->data->type ?: '';
			$id = $request->data->id ?: 0;
			$id = $app->hashids->decrypt($id);

			$qty = $request->data->qty ?: 1;
			if($qty < 0)
				$app->output->json(array('error' => true, 'message' => 'Invalid quantity.'), 400);

			if($type == 'listings') {
				foreach($cart[$type] as $idx => $item) {
					if($item['listing'] == $id) {
						unset($cart[$type][$idx]);
						break;
					}
				}
			}
			else if($type == 'bulk') {
				$id = Listing::find($id)->description_id;
				if($qty == $cart[$type][$id]['qty'])
					unset($cart[$type][$id]);
				else
					$cart[$type][$id]['qty'] -= $qty;
			}

			$_SESSION['cart'] = $cart;
		}
		catch(Hashids_Invalid $e) {
			$app->logger->log('Listing ID given was invalid', 'ERROR', array('object' => 'Listing', 'id' => $id, 'pathway' => 'addCart'), 'user');
			$app->output->json(array('error' => true, 'message' => 'No such listing exists'), 400);
		}
		catch(ActiveRecord\RecordNotFound $e) {
			$app->logger->log('No such Listing found', 'ERROR', array('object' => 'Listing', 'id' => $id, 'id_dec' => $listing_id_dec, 'pathway' => 'addCart'), 'user');
			$app->output->json(array('error' => true, 'message' => 'No such listing exists'), 400);
		}	
	}

	public function emptyCart($app)
	{
		$_SESSION['cart'] = array('listings' => array(), 'bulk' => array());
		$app->output->redirect('/cart');
	}

	public function showCart($app)
	{
		if(!$app->user->isLoggedIn() && $app->steam->login())
		{
			$app->output->redirect('/cart');
		}

		$order = null;
		$total = 0;
		$updated = false;

		if(isset($_SESSION['order'])) {
			/* 
			 * Edge case: user has reserved an order already
			 */
			try {
				$order = Order::find($_SESSION['order']);
				if($order->status == Order::STATUS_CANCELLED) {
					throw new ActiveRecord\RecordNotFound();
				}

				$cart = $order->toTable();
			}
			catch(ActiveRecord\RecordNotFound $e) {
				unset($_SESSION['order']);
				$app->output->redirect('/cart');
			}
		}
		else {
			/* 
			 * Typical scenario: user has unreserved listings in a cart
			 */
			$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array('listings' => array(), 'bulk' => array());

			if(!(empty($cart['bulk']) && empty($cart['listings']))) {
				$cart = $_SESSION['cart'];

				// Replace outdated items and add in model
				foreach($cart['bulk'] as $description_id => &$item) {
					$listings = Listing::find('all', array(
						'conditions' => array('stage = ? AND description_id = ?', Listing::STAGE_LIST, $description_id),
						'order' => 'price ASC',
						'include' => array('description')));

					if(empty($listings)) {
						$updated = true;
						unset($cart['bulk'][$description_id]);
						unset($_SESSION['cart']['bulk'][$description_id]);
					}
					else {
						$item['listing'] = $listings[0];
						$item['max'] = count($listings);
						$_SESSION['cart']['bulk'][$description_id]['listing'] = $item['listing']->id;
						$_SESSION['cart']['bulk'][$description_id]['max'] = $item['max'];

						if($item['qty'] > $item['max']) {
							$updated = true;
							$item['qty'] = $item['max'];
							$_SESSION['cart']['bulk'][$description_id]['qty'] = $item['max'];
						}

						$item['subtotal'] = array_reduce(array_slice($listings, 0, $item['qty']), function($carry, $listing) {
							$carry += $listing->price;
							return $carry;
						}); 
						$item['unit_price'] = $item['subtotal'] / $item['qty'];
					}
				}	

				// Unique, unstackable items cannot be replaced
				foreach($cart['listings'] as $idx => &$item) {
					$listing = Listing::find($item['listing'], array('include' => array('description')));
					$item['listing'] = $listing;
					$item['max'] = 1;
					$_SESSION['cart']['listings'][$idx]['listing'] = $item['listing']->id;
					$_SESSION['cart']['listings'][$idx]['max'] = $item['max'];

					if($listing->stage != Listing::STAGE_LIST) {
						$updated = true;
						unset($cart['listings'][$idx]);
						unset($_SESSION['cart']['listings'][$idx]);
					}
				}
			}
		}

		$total += array_reduce($cart['bulk'], function($carry, $item) { 
			$carry += $item['subtotal'];
			return $carry;
		});
		$total += array_reduce($cart['listings'], function($carry, $item) { 
			$carry += $item['listing']->price;
			return $carry;
		});

		$total_taxed = round($total * 1.08, 2);	// 8% tax

		if($updated)
			$app->output->alert('Oops, some of the items in your cart are now unavailable! Your cart has been updated.');

		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('cart', 'Cart');
		$app->output->setTitle('My Cart');
		$app->output->setActiveTab('cart');
		$app->output->render('shop.cart', ['cart' => $cart, 'total' => $total, 'total_taxed' => $total_taxed, 'old_order' => $order, 'steamLoginUrl' => $app->steam->loginUrl()]);
	}


	// AJAX Route for processing a user's cart into an order and checking out with a payment gateway provider
	public function order($app)
	{
		if(!$app->user->isLoggedIn())
		{
			$app->output->json(array('error' => true, 'message' => 'You must be logged in to order items.'), 400);
		}

		$headers = getallheaders();
		if(empty($headers['X-CSRFToken']) || strcmp($headers['X-CSRFToken'], $app->user->session->csrf_token) != 0) {
			unset($_SESSION['order']);
			$app->logger->log('Invalid CSRFToken on Checkout', 'ERROR', array('provided_token' => $headers['X-CSRFToken'], 'real_token' => $app->user->session->csrf_token), 'user');
			$app->output->json(array('error' => true, 'message' => 'It looks like a user was trying to make a request on your behalf. Ensure that your system is secure and relogin.'), 400);
		}

		$order = null;
		$success = false;

		if(isset($_SESSION['order'])) {
			/* 
			 * Edge case: user has reserved an order already
			 */
			try {
				$order = Order::find($_SESSION['order']);
				if($order->status == Order::STATUS_CANCELLED) {
					throw new ActiveRecord\RecordNotFound();
				}
				
				$success = true;
			}
			catch(ActiveRecord\RecordNotFound $e) {
				unset($_SESSION['order']);
				$app->output->json(array('error' => true), 400);
			}
		}
		else {
			/* 
			 * Typical scenario: generate an order from cart listings
			 */
			$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array('listings' => array(), 'bulk' => array());
			if(empty($cart['bulk']) && empty($cart['listings'])) {
				$app->logger->log('Checkout with Empty Cart', 'ERROR', array(), 'user');
				$app->output->json(array('error' => true, 'message' => 'Your cart is empty.'), 400);
			}

			$user_id = $app->user->id;
			$cart = $_SESSION['cart'];
			$listings = array();

			// Grab unique listings first
			if(!empty($cart['listings'])) {
				$listings = Listing::find('all', array(
					'conditions' => array('id IN (?)', array_map(function ($item) { return $item['listing']; }, $cart['listings'])),
					'include' => 'description'
					));
			}

			// Add in bulk purchases
			foreach($cart['bulk'] as $description_id => $item) {
				if($item['qty'] < 1) {
					$app->logger->log('Invalid Quantity error on checkout', 'ERROR', array(), 'user');
					$app->output->json(array('error' => true, 'message' => 'You have provided an invalid quantity for your item(s).'), 400);
				}

				$bulk_listings = Listing::find('all', array(
						'conditions' => array('stage = ? AND description_id = ?', Listing::STAGE_LIST, $description_id),
						'order' => 'price ASC',
						'limit' => $item['qty']));

				if(count($bulk_listings) != $item['qty']) {
					throw new Order_ReservationError;
				}
				else 
					$listings = array_merge($listings, $bulk_listings);
			}

			// Reserving Listings for this user
			$order = null;
			$success = Listing::transaction(function () use ($listings, $user_id, &$order)
			{
				$total = 0.00;
				foreach($listings as $idx => &$listing) {
					if($listing->stage != Listing::STAGE_LIST)
						return false;

					$listing->setStage('order');

					// If this is a parent listing, replace choose new parent for children
					$children = $listing->children;
					if(!empty($children)) {
						$new_parent = end($children);

						$listing->parent_listing_id = $new_parent->id;
						$listing->save();
						foreach($children as $idx => $child_listing) {
							$child_listing->parent_listing_id = $new_parent->id;
							$child_listing->save();
						}
						
						$new_parent->parent_listing_id = null;
						$new_parent->save();
					}

					$total += $listing->price;
				}

				$order = Order::create([
					'user_id' => $user_id,
					'total' => $total
					]);
				$order->add($listings);
			});		
		}


		// Initiate checkout via payment gateways
		try {
			if(!$success)
				throw new Order_ReservationError;

			$request = $app->router->flight->request();
			$_SESSION['cart'] = array('listings' => array(), 'bulk' => array());
			$_SESSION['order'] = $order->id;

			if($request->query->checkout == 'coinbase') {
				// Checkout with Coinbase
				$order->provider = 'coinbase';
				$order->save();

				$coinbase = $app->payment->coinbase_button($order, array('success_url' => '/cart/process?checkout=coinbase', 'cancel_url' => '/cart/cancel'));
				$app->output->json(array('error' => false, 'url' => 'https://coinbase.com/checkouts/'.$coinbase->button->code));
			}
			else if($request->query->checkout == 'stripe') {
				// Checkout with Stripe
				$order->provider = 'stripe';
				$order->save();

				$result = $app->payment->stripe_charge($order, $request->data->stripe_token);
				$app->output->json(array('error' => false, 'url' => $app->config->get('core.url').'/cart/process?checkout=stripe&ch='.$result->id));
			}
			else {
				// Checkout with PayPal
				$order->provider = 'paypal';
				$order->save();

				$checkout_url = $app->payment->paypal_SEC($order, '/cart/process', '/cart/cancel');
				$app->output->json(array('error' => false, 'url' => $checkout_url));
			}
		} catch(Order_ReservationError $e) {
			$app->logger->log('Checkout failed ('.get_class($e).')', 'ERROR', array('pathway' => 'order', 'exception' => $e), 'user');
			$app->output->json(array('error' => true, 'message' => 'There was an error ordering the items in your cart. You will be redirected back to your cart shortly.'), 500);
		} catch(Paypal_CheckoutError $e) {
			$app->logger->log('Checkout failed ('.get_class($e).')', 'ERROR', array('pathway' => 'order', 'exception' => $e), 'user');
			$app->output->json(array('error' => true, 'message' => 'There was an error setting up your PayPal Express Checkout. You will be redirected back to your cart shortly.'), 500);
		} catch(Stripe_CardError $e) {
			$body = $e->getJsonBody();
			$err  = $body['error'];

			$app->logger->log('Checkout failed ('.get_class($e).')', 'ERROR', array('pathway' => 'order', 'message' => $err['message']), 'user');
			$app->output->json(array('error' => true, 'message' => 'There was an error processing your Stripe Checkout: '.$err['message'].' You will be redirected back to your cart shortly.'), 500);
		} catch (Stripe_Error $e) {
			$app->logger->log('Checkout failed ('.get_class($e).')', 'ERROR', array('pathway' => 'order', 'exception' => $e), 'user');
			$app->output->json(array('error' => true, 'message' => 'There was an internal error processing your Stripe Checkout and has been logged. You will be redirected back to your cart shortly.'), 500);
		} catch(Exception $e) {
			$app->output->json(array('error' => true, 'message' => 'There was an internal error processing your checkout and has been logged. You will be redirected back to your cart shortly.'), 500);
			throw $e;
		}
	}

	public function resetCart($app)
	{
		$_SESSION['cart'] = array('listings' => array(), 'bulk' => array());
		$app->output->redirect('/cart');
	}

	public function process($app)
	{
		if(!$app->user->isLoggedIn())
		{
			$app->output->redirect('/account/login');
		}

		try {
			$request = $app->router->flight->request();
			$success = false;
			$order_id = $_SESSION['order'];

			if($request->query->checkout == 'coinbase') {
				$cb_order = $app->payment->coinbase_god($order_id);
				$app->logger->log('CHECKOUT PROCESSED (COINBASE)', 'DEBUG', array('transaction' => $cb_order), 'user');
			}
			else if($request->query->checkout == 'stripe') {
				$charge = $app->payment->stripe_retrieve_charge($request->query->ch);
				$app->logger->log('CHECKOUT PROCESSED (STRIPE)', 'DEBUG', array('transaction' => $charge), 'user');
			}
			else {
				$token = $request->query->token;
				$payer_id = $request->query->PayerID;
				$transaction = $app->payment->paypal_process($token, $payer_id, $order_id);
				$app->logger->log('CHECKOUT PROCESSED (PAYPAL)', 'DEBUG', array('transaction' => $transaction), 'user');
			}

			$order = Order::find($order_id);
			$order->status = Order::STATUS_PAID;
			$order->save();
			unset($_SESSION['order']);

			foreach($order->listings as $idx => $listing) {
				$listing->parent_listing_id = null;
				$listing->save();
			}

			// Save identifying transaction information for verification later
			if($request->query->checkout == 'coinbase')
				$order->transaction = $cb_order->id;
			else if($request->query->checkout == 'stripe')
				$order->transaction = $charge->id;
			else
				$order->transaction = $transaction['EMAIL'];
			$order->save();

			if($request->query->checkout == 'coinbase' || $request->query->checkout == 'stripe')
				$order->confirm($app);
			else {
				Notification::create([
					'user_id' => $order->user_id,
					'receiver_id' => $order->user_id,
					'title' => 'REVIEW',
					'body' => '**Order #'.$app->hashids->encrypt($order->id).' has been ordered and put on hold!** Your order has been put on hold to be reviewed by our staff.'
				]);

				foreach($order->listings as $idx => $listing) {
					Notification::create([
						'user_id' => $order->user_id,
						'receiver_id' => $listing->user_id,
						'title' => 'REVIEW',
						'body' => '**Your Listing #'.$app->hashids->encrypt($listing->id).' ('.$listing->description->name.') has been ordered and put on hold!** 
Someone has ordered your item using PayPal. As we review the transaction, your item will be put on hold.'
					]);
				}
			}
			
			$app->output->redirect('/account/invoice/'.($app->hashids->encrypt($order_id)).'?ordered');

		} catch(Paypal_VerificationError $e) {
			$app->logger->log('Checkout failed ('.get_class($e).')', 'ERROR', array('pathway' => 'order', 'exception' => $e), 'user');
			$app->output->alert('We could not process your PayPal Express Checkout due to your account not being verified. You will be redirected back to your cart shortly.');
		} catch(Paypal_CheckoutError $e) {
			$app->logger->log('Checkout failed ('.get_class($e).')', 'ERROR', array('pathway' => 'order', 'exception' => $e), 'user');
			$app->output->alert('There was an error processing up your PayPal Express Checkout. You will be redirected back to your cart shortly.');
		} catch(Stripe_CardError $e) {
			$body = $e->getJsonBody();
			$err  = $body['error'];

			$app->logger->log('Checkout failed ('.get_class($e).')', 'ERROR', array('pathway' => 'order', 'message' => $err['message']), 'user');
			$app->output->alert('There was an error processing your Stripe Checkout: '.$err['message'].' You will be redirected back to your cart shortly.');
		} catch (Stripe_Error $e) {
			$app->logger->log('Checkout failed ('.get_class($e).')', 'ERROR', array('pathway' => 'order', 'exception' => $e), 'user');
			$app->output->alert('There was an internal error processing your Stripe Checkout and has been logged. You will be redirected back to your cart shortly.');
		} catch(Exception $e) {
			$app->output->alert('There was an internal error processing your checkout and has been logged. You will be redirected back to your cart shortly.');
			throw $e;
		}

		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('cart', 'Cart');
		$app->output->setTitle('My Cart');
		$app->output->setActiveTab('cart');
		$app->output->render('shop.checkoutError');
	}

	public function cancel($app)
	{
		if(!$app->user->isLoggedIn())
		{
			$app->output->redirect('/account/login');
		}

		try {
			if(!isset($_SESSION['order']))
				throw new ActiveRecord\RecordNotFound();

			$order = Order::find($_SESSION['order']);
			$order->status = Order::STATUS_CANCELLED;
			$order->save();
			unset($_SESSION['order']);

			foreach($order->listings as $idx => $listing) {
				$listing->setStage('list');
				Notification::create([
					'user_id' => $order->user_id,
					'receiver_id' => $listing->user_id,
					'title' => 'DENIAL',
					'body' => '**An Order for Listing #'.$app->hashids->encrypt($listing->id).' ('.$listing->description->name.') has been cancelled!** 
An order for your item has been cancelled and re-listed. We apologize for the inconvenience and will look into this issue with the user who made the purchase.'
				]);
			}

			Notification::create([
				'user_id' => $order->user_id,
				'receiver_id' => $order->user_id,
				'title' => 'DENIAL',
				'body' => '**Order #'.$app->hashids->encrypt($order->id).' has been cancelled!** Beware that cancelling orders will result in a suspension if repeated due to reservation of item.'
			]);

			$app->pusher->trigger($order->user_id, 'notification', array('message' => '1'));
			$app->output->redirect('/cart');
		}
		catch(ActiveRecord\RecordNotFound $e) {
			$app->logger->log('No such Order found', 'ERROR', array('object' => 'Order', 'id_dec' => $_SESSION['order'], 'pathway' => 'cancelOrder'), 'user');
			$app->output->redirect('/cart');
		}
	}
}