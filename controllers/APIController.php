<?php
class APIController {

	private function error($app, $message) {
		$app->logger->log('Invalid use of API', 'ALERT', array('message' => $message), 'api');
		$app->output->json(array(
			'error'=>'true', 
			'message'=>$message
			), 400);
		exit();
	}

	private function generateSig($key, $collection)
	{
		$str = '';
		$object_keys = $collection->keys();
		ksort($object_keys);

		foreach($object_keys as $idx => $k) {
			$str .= $collection->{$k};
		}
		return hash_hmac("sha256", $str, $key);
	}

	private function authorize($app, $key, $sig, $data)
	{	
		$apiKeys = $app->config->get('api.keys');

		$mysig = $this->generateSig($key, $data);

		if(in_array($key, $apiKeys) && !strcmp($mysig, $sig)) 
			return;
		else {
			$app->logger->log('Unauthorized entry into API', 'ALERT', array('key' => $key, 'sig' => $sig, 'data' => $data), 'api');
			$app->output->json(array('error' => false, 'message' => 'Unauthorized'), 403);
			exit();
		}
	}

	public function generateSignature($app)
	{
		if($app->config->get('mode') == 'production')
			return;

		$request = $app->router->flight->request();
		if($request->method != 'POST') {
			$this->error($app, 'Invalid entry');
		}

		$app->output->json(array('signature' => $this->generateSig($request->data->key, $request->data)));
	}

	public function grabItem($app)
	{
		$request = $app->router->flight->request();
		if($request->method != 'POST') {
			$this->error($app, 'Invalid entry');
		}

		$this->authorize($app, $request->data->key, $request->query->sig, $request->data);
		try {
			$listing_id = $request->data->listing_id ?: -1;
			$trade_url = $request->data->trade_url ?: '';
			$trade_code = $request->data->trade_code ?: '';
			$bot_id = $request->data->bot_id ?: -1;
			$listing = Listing::find($listing_id);
			$bot = Bot::find($bot_id);

			if($listing->stage != Listing::STAGE_REQUEST)
				$this->error($app, 'Invalid action');
			else if(empty($trade_url) || empty($trade_code))
				$this->error($app, 'Invalid trade url or code');

			$listing->trade_url = $trade_url;
			$listing->trade_code = $trade_code;
			$listing->save();

			$notification = Notification::create([
				'user_id' => $listing->user_id,
				'receiver_id' => $listing->user_id,
				'title' => 'TRADE',
				'body' => '**Your Listing is almost ready!** ['.$bot->name.'](http://steamcommunity.com/profiles/'.$bot->id.') is ready to store your item(s) with a [trade offer]('.$trade_url.') (code: **'.$trade_code.'**).'
			]);

			$app->pusher->trigger($listing->user_id, 'notification', array('message' => '1'));
			$app->pusher->trigger($listing->user_id, 'trade', array('trade_url' => $trade_url));
			$app->output->json(array(
				'error'=>'false', 
				'message'=>'A trade offer for listing '.$listing->id.' has been sent.')
			);
		}
		catch (ActiveRecord\RecordNotFound $e) {
			$this->error($app, 'No such listing or bot');
		}
	}

	public function grabItemComplete($app)
	{
		$request = $app->router->flight->request();
		if($request->method != 'POST') {
			$this->error($app, 'Invalid entry');
		}

		$this->authorize($app, $request->data->key, $request->query->sig, $request->data);
		try {
			$listing_id = $request->data->listing_id ?: -1;
			$listing = Listing::find($listing_id);
			if($listing->stage != Listing::STAGE_REQUEST) {
				$this->error($app, 'Invalid action');
				return;
			}

			// Prep for review and remove expired trade_url
			$listing->setStage('review');
			$listing->trade_url = null;
			$listing->trade_code = null;
			$listing->save();

			// Loop through children for bulk listings and update them as well
			$children = $listing->children;
			if(!empty($children)) {
				foreach($children as $idx => $child_listing) {
					$child_listing->setStage('review');
					$child_listing->trade_url = null;
					$child_listing->trade_code = null;
					$child_listing->save();
				}

				$notification = Notification::create([
					'user_id' => $listing->user_id,
					'receiver_id' => $listing->user_id,
					'title' => 'REVIEW',
					'body' => '**Bulk Listing #'.$app->hashids->encrypt($listing_id).' ('.$listing->description->name.' x '.(count($children)+1).') is now under review!** 
The items have been received pending review by our staff, which can take up to 12 hours.'
				]);
			}
			else {
				$notification = Notification::create([
					'user_id' => $listing->user_id,
					'receiver_id' => $listing->user_id,
					'title' => 'REVIEW',
					'body' => '**Listing #'.$app->hashids->encrypt($listing_id).' ('.$listing->description->name.') is now under review!** 
The item has been received pending review by our staff, which can take up to 12 hours.'
				]);
			}

			$app->pusher->trigger($listing->user_id, 'notification', array('message' => '1'));
			$app->output->json(array(
				'error'=>'false', 
				'message'=>'Listing '.$listing->id.' has been received and put under review.')
			);
		}
		catch (ActiveRecord\RecordNotFound $e) {
			$this->error($app, 'No such listing');
		}
	}

	public function storeComplete($app)
	{
		$request = $app->router->flight->request();
		if($request->method != 'POST') {
			$this->error($app, 'Invalid entry');
		}

		$this->authorize($app, $request->data->key, $request->query->sig, $request->data);
		try {
			$listing_id = $request->data->listing_id ?: -1;
			$bot_id = $request->data->bot_id ?: -1;
			$listing = Listing::find($listing_id);
			$bot = Bot::find($bot_id);
			if($listing->stage != Listing::STAGE_LIST)
				$this->error($app, 'Invalid action');

			$listing->bot_id = $bot->id;
			$listing->save();

			// Loop through children for bulk listings and update them as well
			$children = $listing->children;
			if(!empty($children)) {
				foreach($children as $idx => $child_listing) {
					$child_listing->bot_id = $bot->id;
					$child_listing->save();
				}
				
				$notification = Notification::create([
					'user_id' => $listing->user_id,
					'receiver_id' => $listing->user_id,
					'title' => 'APPROVAL',
					'body' => '**Bulk Listing ('.$listing->description->name.' x '.(count($children)+1).') has been accepted!** 
It will now appear in the [Browse]('.$app->config->get('core.url').'/browse) section.'
				]);
			}
			else {
				$notification = Notification::create([
					'user_id' => $listing->user_id,
					'receiver_id' => $listing->user_id,
					'title' => 'APPROVAL',
					'body' => '**Listing #'.$app->hashids->encrypt($listing->id).' ('.$listing->description->name.') has been accepted!**
It will now appear in the [Browse]('.$app->config->get('core.url').'/browse) section.'
					]);
			}

			$app->pusher->trigger($listing->user_id, 'notification', array('message' => '1'));
			$app->output->json(array(
				'error'=>'false', 
				'message'=>'Listing '.$listing->id.' has been listed.')
			);
		}
		catch (ActiveRecord\RecordNotFound $e) {
			$this->error($app, 'No such listing or bot');
		}
	}

	public function returnItem($app)
	{
		$request = $app->router->flight->request();
		if($request->method != 'POST') {
			$this->error($app, 'Invalid entry');
		}

		$this->authorize($app, $request->data->key, $request->query->sig, $request->data);
		try {
			$listing_id = $request->data->listing_id ?: -1;
			$bot_id = $request->data->bot_id ?: -1;
			$trade_url = $request->data->trade_url ?: '';
			$trade_code = $request->data->trade_code ?: '';
			$listing = Listing::find($listing_id);
			$bot = Bot::find($bot_id);

			if(empty($trade_url) || empty($trade_code))
				$this->error($app, 'Invalid trade url or code');
			else if($listing->stage != Listing::STAGE_DENY && $listing->stage != Listing::STAGE_CANCEL)
				$this->error($app, 'Invalid action');

			$listing->trade_url = $trade_url;
			$listing->trade_code = $trade_code;
			$listing->save();

			$children = $listing->children;
			if(!empty($children)) {
				$notification = Notification::create([
					'user_id' => $listing->user_id,
					'receiver_id' => $listing->user_id,
					'title' => 'TRADE',
					'body' => '**Refund for Bulk Listing #'.$app->hashids->encrypt($listing->id).' ('.$listing->description->name.' x '.(count($children) + 1).') is ready!** 
['.$bot->name.'](http://steamcommunity.com/profiles/'.$bot->id.') is ready to refund your item at this [trade offer]('.$trade_url.') (code: **'.$trade_code.'**).'
				]);
			}
			else {
				$notification = Notification::create([
					'user_id' => $listing->user_id,
					'receiver_id' => $listing->user_id,
					'title' => 'TRADE',
					'body' => '**Refund for Listing #'.$app->hashids->encrypt($listing->id).' ('.$listing->description->name.') is ready!** 
['.$bot->name.'](http://steamcommunity.com/profiles/'.$bot->id.') is ready to refund your item at this [trade offer]('.$trade_url.') (code: **'.$trade_code.'**).'
				]);
			}

			$app->pusher->trigger($listing->user_id, 'notification', array('message' => '1'));
			$app->pusher->trigger($listing->user_id, 'trade', array('trade_url' => $trade_url));
			$app->output->json(array(
				'error'=>'false', 
				'message'=>'A trade offer for listing '.$listing->id.' has been sent.')
			);
		}
		catch (ActiveRecord\RecordNotFound $e) {
			$this->error($app, 'No such listing or bot');
		}
	}
	

	public function returnItemComplete($app)
	{
		$request = $app->router->flight->request();
		if($request->method != 'POST') {
			$this->error($app, 'Invalid entry');
		}

		$this->authorize($app, $request->data->key, $request->query->sig, $request->data);
		try {
			$listing_id = $request->data->listing_id ?: -1;
			$listing = Listing::find($listing_id);
			$listing->setStage('delete');
			$listing->trade_url = null;
			$listing->trade_code = null;
			$listing->save();
			
			// Loop through children for bulk listings and update them as well
			$children = $listing->children;
			if(!empty($children)) {
				foreach($children as $idx => $child_listing) {
					$child_listing->setStage('delete');
					$child_listing->trade_url = null;
					$child_listing->trade_code = null;
					$child_listing->save();
				}

				$notification = Notification::create([
					'user_id' => $listing->user_id,
					'receiver_id' => $listing->user_id,
					'title' => 'DELETED',
					'body' => '**Bulk Listing #'.$app->hashids->encrypt($listing->id).' ('.$listing->description->name.' x '.(count($children) + 1).') has been deleted!** 
This is a notification that confirms that the item has been refunded and the listing has been deleted.'
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

			$app->pusher->trigger($listing->user_id, 'notification', array('message' => '1'));
			$app->output->json(array(
				'error'=>'false', 
				'message'=>'Listing '.$listing->id.' has been returned.')
			);
		}
		catch (ActiveRecord\RecordNotFound $e) {
			$this->error($app, 'No such listing');
		}
	}

	public function transferItem($app)
	{
		$request = $app->router->flight->request();
		if($request->method != 'POST') {
			$this->error($app, 'Invalid entry');
		}

		$this->authorize($app, $request->data->key, $request->query->sig, $request->data);
		try {
			$bot_id = $request->data->bot_id ?: -1;
			$order_id = $request->data->order_id ?: -1;
			$trade_url = $request->data->trade_url ?: '';
			$trade_code = $request->data->trade_code ?: '';
			$bot = Bot::find($bot_id);
			$order = Order::find($order_id);

			if(empty($trade_url) || empty($trade_code))
				$this->error($app, 'Invalid trade url or code');

			foreach($order->listings as $idx => $listing) {
				$listing->trade_url = $trade_url;
				$listing->trade_code = $trade_code;
				$listing->save();
			}

			Notification::create([
				'user_id' => $order->user_id,
				'receiver_id' => $order->user_id,
				'title' => 'TRADE',
				'body' => '**Order #'.$app->hashids->encrypt($order->id).' has arrived!** 
['.$bot->name.'](http://steamcommunity.com/profiles/'.$bot->id.') is ready with your order at this [trade offer]('.$trade_url.') (code: **'.$trade_code.'**).'
			]);

			$app->pusher->trigger($order->user_id, 'trade', array('trade_url' => $trade_url));
			$app->pusher->trigger($order->user_id, 'notification', array('message' => '1'));
			$app->output->json(array(
				'error'=>'false', 
				'message'=>'A trade offer for order '.$order->id.' has been sent.')
			);
		}
		catch (ActiveRecord\RecordNotFound $e) {
			$this->error($app, 'No such order or bot');
		}
	}
	

	public function transferItemComplete($app)
	{
		$request = $app->router->flight->request();
		if($request->method != 'POST') {
			$this->error($app, 'Invalid entry');
		}

		$this->authorize($app, $request->data->key, $request->query->sig, $request->data);
		try {
			$order_id = $request->data->order_id ?: -1;
			$order = Order::find($order_id);

			foreach($order->listings as $idx => $listing) {
				$listing->trade_url = null;
				$listing->trade_code = null;
				$listing->setStage('complete');
			
				Notification::create([
					'user_id' => $order->user_id,
					'receiver_id' => $listing->user_id,
					'title' => 'APPROVAL',
					'body' => '**Listing #'.$app->hashids->encrypt($listing->id).' ('.$listing->description->name.') has been completed!** 
An order for your item has been completed and your CSGOShop wallet has been credited.

You can check your wallet [here]('.$app->config->get('core.url').'/account/wallet).']);
			}
			
			Notification::create([
				'user_id' => $order->user_id,
				'receiver_id' => $order->user_id,
				'title' => 'APPROVAL',
				'body' => '**Order #'.$app->hashids->encrypt($order->id).' has been completed!** 
Your trade has been marked as complete. 

Thank you for using CSGOShop, and we look forward to your business in the future!'
			]);

			$app->pusher->trigger($listing->user_id, 'notification', array('message' => '1'));
			$app->output->json(array(
				'error'=>'false', 
				'message'=>'Order '.$order->id.' has been transferred.')
			);
		}
		catch (ActiveRecord\RecordNotFound $e) {
			$this->error($app, 'No such order');
		}
	}

	public function messages($app)
	{
		$request = $app->router->flight->request();
		if($request->method != 'POST') {
			$this->error($app, 'Invalid entry');
		}

		$this->authorize($app, $request->data->key, $request->query->sig, $request->data);
		$data = array();

		$listings = Listing::find('all', array(
			'conditions' => array('stage = ? AND ISNULL(trade_url)', Listing::STAGE_REQUEST)));
		foreach($listings as $idx => $listing) {
			if(!empty($listing->parent))
				continue;
			$children = $listing->children;
			if(!empty($children)) {
				array_push($data, array(
					'event' => 'requestBulkListing',
					'data' => array(
						'listing_id' => (string)$listing->id,
						'user_id' => (string)$listing->user_id,
						'items' => array_map(function($l) { return (string)$l->item_id; }, array_merge($children, array($listing)))
					)));
			}
			else {
				array_push($data, array(
					'event' => 'requestListing',
					'data' => array(
						'listing_id' => (string)$listing->id,
						'user_id' => (string)$listing->user_id,
						'item_id' => (string)$listing->item_id
					)));
			}
		}

		$listings = Listing::find('all', array(
			'conditions' => array('stage = ? AND ISNULL(bot_id)', Listing::STAGE_LIST)));
		foreach($listings as $idx => $listing) {
			if(!empty($listing->parent))
				continue;
			$children = $listing->children;
			if(!empty($children)) {
				array_push($data, array(
					'event' => 'approveBulkListing',
					'data' => array(
						'listing_id' => (string)$listing->id,
						'items' => array_map(function($l) { return (string)$l->item_id; }, array_merge($children, array($listing)))
					)));
			}
			else {
				array_push($data, array(
					'event' => 'approveListing',
					'data' => array(
						'listing_id' => (string)$listing->id,
						'item_id' => (string)$listing->item_id
					)));
			}
		}		

		$listings = Listing::find('all', array(
			'conditions' => array('stage = ? AND ISNULL(trade_url)', Listing::STAGE_DENY)));
		foreach($listings as $idx => $listing) {
			if(!empty($listing->parent))
				continue;
			$children = $listing->children;
			if(!empty($children)) {
				array_push($data, array(
					'event' => 'denyBulkListing',
					'data' => array(
						'listing_id' => (string)$listing->id,
						'user_id' => (string)$listing->user_id,
						'items' => array_map(function($l) { return (string)$l->item_id; }, array_merge($children, array($listing)))
					)));
			}
			else {
				array_push($data, array(
					'event' => 'denyListing',
					'data' => array(
						'listing_id' => (string)$listing->id,
						'user_id' => (string)$listing->user_id,
						'item_id' => (string)$listing->item_id
					)));
			}
		}

		$listings = Listing::find('all', array(
			'conditions' => array('stage = ? AND ISNULL(trade_url)', Listing::STAGE_CANCEL)));
		foreach($listings as $idx => $listing) {
			if(!empty($listing->parent))
				continue;
			$children = $listing->children;
			if(!empty($children)) {
				array_push($data, array(
					'event' => 'cancelBulkListing',
					'data' => array(
						'listing_id' => (string)$listing->id,
						'user_id' => (string)$listing->user_id,
						'bot_id' => (string)$listing->bot_id,
						'items' => array_map(function($l) { return (string)$l->item_id; }, array_merge($children, array($listing)))
					)));
			}
			else {
				array_push($data, array(
					'event' => 'cancelListing',
					'data' => array(
						'listing_id' => (string)$listing->id,
						'user_id' => (string)$listing->user_id,
						'bot_id' => (string)$listing->bot_id,
						'item_id' => (string)$listing->item_id
					)));
			}
		}		

		$orders = Order::find('all', array(
			'conditions' => array('status = ?', Order::STATUS_PAID_CONFIRM)));
		foreach($orders as $idx => $order) {
			$listings = $order->listings;
			if($listings[0]->stage != Listing::STAGE_ORDER) {
				continue;
			}
			if(!empty($listings[0]->trade_url))
				continue;
			
			$listings = array_map(function($listing) use ($order) { return array(
					'order_id' => (string)$order->id,
					'user_id' => (string)$order->user_id,
					'item_id' => (string)$listing->item_id,
					'bot_id' => (string)$listing->bot_id
				);}, $listings);

			array_push($data, array(
				'event' => 'paidOrder',
				'data' => $listings));
		}

		$app->output->json($data);
	}

	public function cleanupOrders($app)
	{
		$request = $app->router->flight->request();
		if($request->method != 'POST')
			$this->error($app, 'Invalid entry');

		$this->authorize($app, $request->data->key, $request->query->sig, $request->data);

		// CURRENT_TIMESTAMP IS WONKY
		// $orders = Order::find('all', array(
		// 	'select' => 'id, TIMESTAMPDIFF(MINUTE, CURRENT_TIMESTAMP, updated_at) AS difference',
		// 	'having' => 'difference > 10'));

		$orders = Order::find('all', array(
			'conditions' => array('status = ?', Order::STATUS_UNPAID)));
		$now = time();

		foreach($orders as $idx => $order) {
			if($order->time_limit < $now) {
				$order->status = Order::STATUS_CANCELLED;
				$order->save();

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
					'body' => '**Order #'.$app->hashids->encrypt($order->id).' has been automatically cancelled!** Beware that cancelling orders will result in a suspension if repeated due to reservation of item.'
				]);

				$app->pusher->trigger($order->user_id, 'cancelOrder', array());
			}
		}

		$app->output->json(array_map(function ($o) { return $o->id; }, $orders));
	}

	public function tradeUrl($app)
	{
		$request = $app->router->flight->request();
		if($request->method != 'POST') {
			$this->error($app, 'Invalid entry');
		}

		$this->authorize($app, $request->data->key, $request->query->sig, $request->data);

		try {
			$user_id = $request->data->user_id ?: -1;
			$app->output->json(array('user_id' => $user_id, 'trade_url' => User::find($user_id)->trade_url));
		}
		catch (ActiveRecord\RecordNotFound $e) {
			$this->error($app, 'No such user');
		}
	}

	public function cancelListingRequest($app)
	{
		$request = $app->router->flight->request();
		if($request->method != 'POST')
			$this->error($app, 'Invalid entry');

		$this->authorize($app, $request->data->key, $request->query->sig, $request->data);

		try {
			$listing_id = $request->data->listing_id;
			$listing = Listing::find($listing_id);
			if($listing->stage != Listing::STAGE_REQUEST) {
				$this->error($app, 'Invalid action');
				return;				
			}
			
			$listings = array($listing);
			$children = $listing->children;
			if(!empty($children))
				$listings = array_merge($listings, $children);

			foreach($listings as $idx => $l) {
				$l->request_takedown = 1;
				$l->stage = Listing::STAGE_DELETE;
				$l->save();
			}

			if(count($listings) > 1) {
				$notification = Notification::create([
					'user_id' => $listing->user_id,
					'receiver_id' => $listing->user_id,
					'title' => 'DENIAL',
					'body' => '**Bulk Listing #'.$app->hashids->encrypt($listings[0]->id).' ('.$listings[0]->description->name.' x '.count($listings).') has been cancelled!** 
We were unable to retrieve your items from the trade. Your listing has been removed from CSGOShop.'
				]);
			}
			else {
				$notification = Notification::create([
					'user_id' => $listing->user_id,
					'receiver_id' => $listing->user_id,
					'title' => 'DENIAL',
					'body' => '**Listing #'.$app->hashids->encrypt($listings[0]->id).' ('.$listings[0]->description->name.') has been cancelled!** 
We were unable to retrieve your items from the trade. Your listing has been removed from CSGOShop.'
				]);
			}

			$app->output->json(array('error' => false, 'message' => 'Listing id '.$listing_id.' has been cancelled.'));
		}
		catch (ActiveRecord\RecordNotFound $e) {
			$this->error($app, 'No such listing');
		}
	}

	public function storageBots($app)
	{
		$request = $app->router->flight->request();
		if($request->method != 'POST')
			$this->error($app, 'Invalid entry');

		$this->authorize($app, $request->data->key, $request->query->sig, $request->data);

		$bots = Bot::find('all', array(
			'conditions' => array('type = ? AND status = ?', Bot::TYPE_STORAGE, Bot::STATUS_ACTIVE)));
		$bots = array_map(function($b) { return $b->id; }, $bots);
		$app->output->json(array('error' => false, 'bot_ids' => $bots));
	}

	public function checkout($app)
	{
		$request = $app->router->flight->request();
		if($request->method != 'POST')
			$this->error($app, 'Invalid entry');

		$this->authorize($app, $request->data->key, $request->query->sig, $request->data);

		$item_id = $request->data->item_id;
		$user_id = $request->data->user_id;
		if(empty($item_id) || empty($user_id))
			$this->error($app, 'Invalid user or item id');

		try {
			$listing = Listing::find('first', array(
				'conditions' => array('stage = ? AND item_id = ?', Listing::STAGE_REVIEW, $item_id),
				'order' => 'updated_at DESC'));
			$user = User::find($user_id);

			if(empty($listing)) {
				$this->error($app, 'Listing does not exist for that item.');
			}
			else {
				$listing->checkout = 1;
				$listing->checkout_user_id = $user->id;
				$listing->save();
			}

			$app->output->json(array('error' => false, 'message' => 'Listing '.$listing->id.' has been checked out by '.$user->name));
		}
		catch(ActiveRecord\RecordNotFound $e) {
			$this->error($app, 'User not found.');
		}
	}

	public function checkin($app)
	{
		$request = $app->router->flight->request();
		if($request->method != 'POST')
			$this->error($app, 'Invalid entry');

		$this->authorize($app, $request->data->key, $request->query->sig, $request->data);

		$item_id = $request->data->item_id;
		if(empty($item_id))
			$this->error($app, 'Invalid item id');

		$listing = Listing::find('first', array(
			'conditions' => array('stage = ? AND item_id = ?', Listing::STAGE_REVIEW, $item_id),
			'order' => 'updated_at DESC'));

		if(empty($listing)) {
			$this->error($app, 'Listing does not exist for that item.');
		}
		else {
			$listing->checkout = 0;
			$listing->checkout_user_id = null;
			$listing->save();
		}
		
		$app->output->json(array('error' => false, 'message' => 'Listing '.$listing->id.' has been checked back in.'));
	}

	public function invalid_trade_url($app)
	{
		$request = $app->router->flight->request();
		if($request->method != 'POST')
			$this->error($app, 'Invalid entry');

		$this->authorize($app, $request->data->key, $request->query->sig, $request->data);
	
		$user_id = $request->data->user_id;
		if(empty($user_id))
			$this->error($app, 'Invalid user id');

		try {
			$user = User::find($user_id);
			$notification = Notification::create([
				'user_id' => $user->id,
				'receiver_id' => $user->id,
				'title' => 'DENIAL',
				'body' => '**A trade request could not be sent!** 
We were unable to send you a trade offer -- ensure that your Steam trade URL is valid in your [settings]('.$app->config->get('core.url').'/account/settings).'
			]);

			$app->output->json(array('error' => false, 'message' => 'Notification of invalid trade URL has been sent to user '.$user->id.'.'));
		}
		catch(ActiveRecord\RecordNotFound $e) {
			$this->error($app, 'No such user found');	
		}
	}
}
?>