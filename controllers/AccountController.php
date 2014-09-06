<?php
class AccountController
{
	const MAX_ORDERS_SHOWN = 10;
	const MAX_CASHOUTS_SHOWN = 5;
	const MAX_LISTINGS_SHOWN = 5;

	public function login($app)
	{
		if($app->user->isLoggedIn() or $app->steam->login())
		{
			$app->output->redirect('/');
		}

		$url = $app->steam->loginUrl();

		$app->logger->log('Login attempt', 'INFO', array('pathway' => 'default'), 'user');
		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('account/login', 'Login');

		$app->output->setTitle('Login');
		$app->output->setActiveTab('account');
		$app->output->render('account.login', ['steamLoginUrl' => $url]);
	}

	public function logout($app)
	{
		if(!$app->user->isLoggedIn())
		{
			$app->output->redirect('/');
		}

		$app->logger->log('Logout', 'INFO', array(), 'user');
		$app->user->logout();
		$app->output->redirect('/');
	}

	public function terms($app)
	{
		if(!$app->user->isLoggedIn())
			$app->output->redirect('/account/login');

		$request = $app->router->flight->request();
		if($request->data->agree == 'on') {
			$app->user->tos_agree = 1;
			$app->user->save();
		}
		
		if($app->user->tos_agree)
			$app->output->redirect('/');
		else {
			$app->logger->log('Redirected to TOS', 'INFO', array(), 'user');

			$page = Page::find(3);
			$app->output->addBreadcrumb('', 'CSGOShop');
			$app->output->addBreadcrumb('account/terms', 'Terms & Conditions');

			$app->output->setTitle('Terms & Conditions');
			$app->output->setActiveTab('account');
			$app->output->render('account.terms', ['pageData' => $page]);
		}
	}

	public function inventory($app)
	{
		if(!$app->user->isLoggedIn())
		{
			$app->output->redirect('/account/login');
		}

		if($app->user->isSiteDeveloper() && $app->router->flight->request()->query->force_refresh)
		{
			$app->steam->getInventory($app->user->id, true);
			$app->output->alert('success', 'Inventory force refreshed');
		}
		
		$listing = Listing::find('first', array('conditions' => array('user_id = ?', $app->user->id)));

		if(empty($listing) && empty($_SESSION['tutorial_listing'])) {
			$app->output->alert('**It seems like this is your first time here.** Check out this video on how to request a listing.


<div class="text-center"><iframe width="420" height="315" src="//www.youtube.com/embed/7asoHPhTeTc" frameborder="0" allowfullscreen></iframe></div>', 'info');
			$_SESSION['tutorial_listing'] = true;
		}

		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('account/inventory', 'Inventory');
	
		$app->output->setTitle('Inventory');
		$app->output->setActiveTab('account');
		$app->output->render('account.inventory', []);
	}

	public function myListings($app)
	{
		if(!$app->user->isLoggedIn())
		{
			$app->output->redirect('/account/login');
		}
		
		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('account/listings', 'My Listings');
		
		$listings = Listing::all(array(
			'conditions' => array('user_id = ? AND ISNULL(parent_listing_id)', $app->user->id),
			'order' => 'updated_at DESC',
			'include' => array('description')
			));

		$request = $app->router->flight->request();
		$page = $request->query->p ?: 0;
		$offset = $page * self::MAX_LISTINGS_SHOWN;
		$total = ceil( count($listings) / self::MAX_LISTINGS_SHOWN );
		if($offset < 0 || $page > $total)
			$app->output->redirect('/account/listings');
		$listings = array_slice($listings, $offset, self::MAX_LISTINGS_SHOWN);

		if(isset($app->router->flight->request()->query->new))
			$app->output->alert('You have successfully requested a new listing.', 'success');
		if(isset($app->router->flight->request()->query->cancel))
			$app->output->alert('Your request to cancel a listing has been sent.', 'success');
		if(empty($listings))
			$app->output->alert('You do not have any listings open.', 'warning');

		$app->output->setTitle('My Listings');
		$app->output->setActiveTab('account');
		$app->output->render('account.listings', ['listings' => $listings, 'page_num' => $page, 'total' => $total]);
	}

	public function activeOrders($app)
	{
		if(!$app->user->isLoggedIn())
		{
			$app->output->redirect('/account/login');
		}

		$request = $app->router->flight->request();
		$page = $request->query->p ?: 0;
		$offset = $page * self::MAX_ORDERS_SHOWN;
		$total = Order::count(array(
			'conditions' => array('user_id = ? AND status IN (?)', $app->user->id, array(Order::STATUS_PAID, Order::STATUS_PAID_CONFIRM)))) / self::MAX_ORDERS_SHOWN;
		$total = ceil($total);
		if($offset < 0 || $page > $total)
			$app->output->redirect('/account/orders/active');

		$orders = Order::find('all', array(
			'conditions' => array(
				'user_id = ? AND status IN (?)', 
				$app->user->id, 
				array(Order::STATUS_PAID, Order::STATUS_PAID_CONFIRM), 
			),
			'offset' => $offset,
			'limit' => self::MAX_ORDERS_SHOWN,
			'order' => 'updated_at DESC'));

		// Filter out orders are completed
		$orders = array_filter($orders, function ($o) {
			return !$o->isComplete();
		});

		if(empty($orders))
			$app->output->alert('You do not currently have orders in transit.', 'warning');

		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('account/orders/', 'Order History');
		$app->output->addBreadcrumb('account/orders/active', 'Active Orders');
		$app->output->setTitle('Active Orders');
		$app->output->setActiveTab('account');
		$app->output->render('account.orders', ['orders' => $orders, 'page_num' => $page, 'total' => $total]);
	}

	public function myOrders($app)
	{
		if(!$app->user->isLoggedIn())
		{
			$app->output->redirect('/account/login');
		}

		$request = $app->router->flight->request();
		$page = $request->query->p ?: 0;
		$offset = $page * self::MAX_ORDERS_SHOWN;
		$total = Order::count(array(
			'conditions' => array('user_id = ?', $app->user->id))) / self::MAX_ORDERS_SHOWN;
		$total = ceil($total);
		if($offset < 0 || $page > $total)
			$app->output->redirect('/account/orders');

		// All of the orders made by a user
		$orders = Order::find('all', array(
			'conditions' => array('user_id = ?', $app->user->id),
			'offset' => $offset,
			'limit' => self::MAX_ORDERS_SHOWN,
			'order' => 'updated_at DESC'));

		if(empty($orders))
			$app->output->alert('You have yet to make/receive any orders.');
		
		$app->output->setTitle('Order History');
		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('account/orders', 'Order History');
		$app->output->setActiveTab('account');
		$app->output->render('account.history', ['orders' => $orders, 'page_num' => $page, 'total' => $total]);
	}

	public function wallet($app)
	{
		if(!$app->user->isLoggedIn())
		{
			$app->output->redirect('/account/login');
		}

		$request = $app->router->flight->request();
		$page = $request->query->p ?: 0;
		$offset = $page * self::MAX_CASHOUTS_SHOWN;
		$page_total = CashoutRequest::count(array(
			'conditions' => array('user_id = ?', $app->user->id))) / self::MAX_CASHOUTS_SHOWN;
		$page_total = ceil($page_total);
		if($offset < 0 || $page > $page_total)
			$app->output->redirect('/account/wallet');

		$cashout_requests = CashoutRequest::find('all', array(
			'conditions' => array('user_id = ?', $app->user->id),
			'limit' => self::MAX_CASHOUTS_SHOWN,
			'offset' => $offset,
			'order' => 'updated_at DESC'));

		$listings = Listing::find('all', array(
			'conditions' => array('user_id = ? AND stage = ?', $app->user->id, Listing::STAGE_COMPLETE)));
		$total = 0;
		$items = array();

		foreach($listings as $idx => $listing) {
			if($listing->description->stackable == 1) {
				if(empty($items[$listing->description->id])) {
					$items[$listing->description->id] = array(
						'listing' => $listing, 
						'qty' => 1, 
						'subtotal' => $listing->price
					);
				}
				else {
					$items[$listing->description->id] = array(
						'listing' => $listing,
						'qty' => $items[$listing->description->id]['qty'] + 1, 
						'subtotal' => $items[$listing->description->id]['subtotal'] + $listing->price
					);	
				}
			}
			else
				$items[$listing->id] = array('listing' => $listing, 'qty' => 1, 'subtotal' => $listing->price);
		}

		foreach($items as $idx => &$item) {
			$item['unit_price'] = $item['subtotal'] / $item['qty'];
		}

		$total += array_reduce($items, function($carry, $item) { 
			$carry += $item['subtotal'];
			return $carry;
		});
		$total_taxed = round($total * 0.95, 2);

		if(empty($items))
			$app->output->alert('You have no listings to cash out on yet. [Why not check on them?]('.$app->config->get('core.url').'/account/listings)');
		if(empty($app->user->paypal))
			$app->output->alert('We ask that all users intending to **cashout using PayPal** store their PayPal e-mail address in their settings.', 'info');
		if($app->user->cooldown)
			$app->output->alert('Your last cashout was made within a week ago--please wait another week until requesting another cashout.');
		
		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('account/wallet', 'My Wallet');
		$app->output->setTitle('Wallet');
		$app->output->setActiveTab('account');
		$app->output->render('account.wallet', ['items' => $items, 'total' => $total, 'total_taxed' => $total_taxed, 'cashout_requests' => $cashout_requests, 'page_num' => $page, 'page_total' => $page_total]);	
	}

	// AJAX Route for generating cashout requests
	public function cashout($app)
	{
		if(!$app->user->isLoggedIn())
		{
			$app->output->redirect('/account/login');
		}
		try {
			$cashout = null;

			CashoutRequest::transaction(function () use ($app, &$cashout) {
				$request = $app->router->flight->request();
				$provider_identifier = $request->data->provider_identifier ?: '';
				$provider = $request->data->provider ?: 'paypal';

				$listings = Listing::find('all', array(
					'conditions' => array('user_id = ? AND stage = ?', $app->user->id, Listing::STAGE_COMPLETE)));

				if(empty($listings))
					throw new Exception('You have submitted an invalid cashout request. There are no listings to cash out.');

				foreach($listings as $idx => &$listing) {
					$listing->setStage('archive');
				}

				$total = 0;
				$total += array_reduce($listings, function($carry, $listing) { 
					$carry += $listing->price;
					return $carry;
				});

				$cashout = CashoutRequest::create([
					'user_id' => $app->user->id,
					'provider' => $provider,
					'provider_identifier' => $provider_identifier,
					'total' => $total,
					'status' => CashoutRequest::STATUS_REQUEST
					]);
				if($cashout->is_invalid())
					throw new Exception('You have submitted an invalid cashout request.');

				$cashout->add($listings);
				$app->user->last_cashout = $cashout->created_at;
				$app->user->save();
			
				switch($cashout->provider) {
					case 'coinbase':
						$result = $app->payment->coinbase_generate_address();
						$cashout->token = $result->token->address;
						$cashout->provider_identifier = $result->token->token_id;
						$cashout->save();
					break;

					case 'stripe':
						$result = $app->payment->stripe_generate_recipient($cashout);
						$cashout->token = $result->id;
						$cashout->save();
					break;

					default:
						$result = $app->payment->paypal_generate_payment(
							$cashout, 
							'/admin/processCashout?cashout_id='.$app->hashids->encrypt($cashout->id), 
							'/admin/cashouts'
						);
						$cashout->token = $result['PayKey'];
						$cashout->save();
					break;
				}
			});
		} 
		catch(PayPal_CashoutError $e) {
			$app->logger->log('Cashout request failed (PayPal_CashoutError)', 'ERROR', array('pathway' => 'paypal', 'exception' => $e), 'user');
			$app->output->json(array('error' => true, 'type' => 'warning', 'message' => $app->output->markdown->text('There was an error with processing your PayPal cashout request. Ensure that the PayPal e-mail in your 
[settings]('.$app->config->get('core.url').'/account/settings) is valid and refresh this page. This issue has been logged.')), 500);
		}
		catch(Coinbase_CashoutError $e) {
			$app->logger->log('Cashout request failed (Coinbase_CashoutError)', 'ERROR', array('pathway' => 'coinbase', 'exception' => $e), 'user');
			$app->output->json(array('error' => true, 'type' => 'warning', 'message' => 'There was an error with processing your Coinbase cashout request. This issue has been logged.'), 500);
		}
		catch(Stripe_Error $e) {
			$app->logger->log('Cashout request failed (Stripe_Error)', 'ERROR', array('pathway' => 'stripe', 'exception' => $e), 'user');
			$app->output->json(array('error' => true, 'type' => 'warning', 'message' => 'There was an error with processing your Stripe cashout request. Ensure that the card that you are entering is a debit card. This issue has been logged.'), 500);
		}
		catch(Exception $e) {
			$app->logger->log('Cashout request failed', 'CRITICAL', array('pathway' => 'unknown', 'exception' => $e), 'user');
			$app->output->json(array('error' => true, 'type' => 'warning', 'message' => 'There was an error with processing your cashout request. This issue has been logged.'), 500);
		}

	}

	public function invoice($app, $order_id = null)
	{
		if(!$app->user->isLoggedIn())
		{
			$app->output->redirect('/account/login');
		}
		try {
			$order_id_dec = $app->hashids->decrypt($order_id);
			$order = Order::find($order_id_dec);
			$listings = array();

			// Only show invoices to buyer or appropriate staff
			if($order->user_id != $app->user->id && !$app->user->isRank('Senior Support Technician')) {
				$app->output->redirect('/cart');
			}

			foreach($order->listings as $idx => $listing) {
				array_push($listings, array(
					'listing' => $listing
				));
			}

			if(isset($app->router->flight->request()->query->ordered))
				$app->output->alert('Your order has successfully been placed and is now in transit.', 'success');

			$app->output->addBreadcrumb('', 'CSGOShop');
			$app->output->addBreadcrumb('account/orders', 'Order History');
			$app->output->addBreadcrumb('cart/invoice/'.$order_id, 'Invoice #'.$order_id);
			$app->output->setTitle('Invoice #'.$order_id);
			$app->output->setActiveTab('cart');
			$app->output->render('shop.invoice', ['order' => $order]);
		}
		catch(ActiveRecord\RecordNotFound $e) {
			$app->logger->log('Order not found', 'ERROR', array('object' => 'Order', 'id' => $id, 'id_dec' => $order_id_dec, 'pathway' => 'invoice'), 'user');
			$app->output->notFound();
		}
		catch(Hashids_Invalid $e) {
			$app->logger->log('Order ID given was invalid', 'ERROR', array('object' => 'Order', 'id' => $id, 'pathway' => 'invoice'), 'user');
			$app->output->notFound();	
		}
	}

	public function settings($app)
	{
		if(!$app->user->isLoggedIn())
		{
			$app->output->redirect('/account/login');
		}
		
		$request = $app->router->flight->request();
		if($request->method == 'POST') {
			$user = $app->user;
			if(!empty($request->data->trade_url)) {
				if(preg_match('/http:\/\/steamcommunity.com\/tradeoffer\/new\/\?partner=(.+)&token=(.+)/', $request->data->trade_url))
					$user->trade_url = $request->data->trade_url;
				else
					$app->output->redirect('/account/settings?invalid_trade_url');
			}
			if(!empty($request->data->paypal_email))
				$user->paypal = $request->data->paypal_email;

			$user->save();
			$app->output->redirect('/account/settings?saved');
		}
		else {
			if(isset($request->query->saved))
				$app->output->alert('Your settings have been updated.', 'success');
			if(isset($request->query->invalid_trade_url))
				$app->output->alert('You have provided an invalid Trade URL.', 'danger');
			

			$app->output->alert('A **Trade URL** is a link provided to third-party sites to send trade offers to a Steam User\'s account without being friended.
CSGOShop uses this link to generate trades between users and our bots for transactions on the site.

You must provide a trade URL in order to use this site. You can find your trade URL at <a target="_blank" href="http://steamcommunity.com/profiles/76561198034369542/tradeoffers/privacy"><strong>the Steam Community website</strong></a>.', 'info');
			$app->output->addBreadcrumb('', 'CSGOShop');
			$app->output->addBreadcrumb('account/settings', 'Settings');
			$app->output->setTitle('Settings');
			$app->output->setActiveTab('account');
			$app->output->render('account.settings', []);
		}
	}
}
