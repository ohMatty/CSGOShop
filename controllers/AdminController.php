<?php
class AdminController {
	const MAX_USERS_SHOWN = 10;

	public function dashboard($app)
	{
		if(!$app->user->isLoggedIn() || !$app->user->isRank('Support Technician'))
		{
			$app->logger->log('Unauthorized access to Admin CP', 'ALERT', array(), 'admin');
			$app->output->redirect('/');
		}

		$listings = Listing::find('all', array(
			'conditions' => array('stage = ? OR (stage = ? AND request_takedown = 1)', Listing::STAGE_REVIEW, Listing::STAGE_LIST),
			'order' => 'stage ASC',
			'include' => array('description')
			));

		$orders = Order::find('all', array(
			'conditions' => array('status = ?', Order::STATUS_PAID_CONFIRM)));
		$now = time();
		$oldest = $now - 2592000; // 1 month in seconds
		$orders_latest = array_filter($orders, function($o) use ($oldest) { return strtotime($o->updated_at->format('db')) > $oldest; });
			
		$taxed_income = array_reduce(
			array_map(function($o) {
				return $o->total_taxed - $o->total;
			}, $orders_latest),
			function($carry, $e) {
				$carry += $e;
				return $carry;
			}
		);

		$total_spent = array_reduce(
			array_map(function($o) {
				return $o->total_taxed;
			}, $orders_latest),
			function($carry, $e) {
				$carry += $e;
				return $carry;
			}
		);

		foreach($listings as $idx => $listing) {
			if(!empty($listing->parent))
				unset($listings[$idx]);
		}
		
		$orders = Order::find('all', array(
			'conditions' => array('status = ?', Order::STATUS_PAID)));		

		$cashout_requests = CashoutRequest::find('all', array(
			'conditions' => array('status = ?', CashoutRequest::STATUS_REQUEST)));

		$users = User::find('all');
		$users = array_filter($users, function($u) { $off = $u->recent_offenses; return !empty($off['listings_cancelled']) || !empty($off['orders_cancelled']); });


		$app->output->addBreadcrumb('admin', 'Dashboard');
		$app->output->setTitle('Dashboard');
		$app->output->setActiveTab('admin');
		if(!$app->user->isRank('Lead Developer'))
			$app->output->render('admin.dashboard', []);
		else
			$app->output->render('admin.dashboard', ['listings' => $listings, 'orders' => $orders, 'cashout_requests' => $cashout_requests, 'users' => $users, 'taxed_income' => $taxed_income, 'orders_latest' => $orders_latest, 'total_spent' => $total_spent]);
	}

	// public function manageDescriptions($app)
	// {
	// 	if(!$app->user->isLoggedIn() || !$app->user->isRank('Lead Developer'))
	// 	{
	// 		$app->logger->log('Unauthorized access to Admin CP', 'ALERT', array(), 'admin');
	// 		$app->output->redirect('/');
	// 	}

	// 	$app->output->redirect('/admin');
	// }

	public function urgentListings($app)
	{
		if(!$app->user->isLoggedIn() || !$app->user->isRank('Senior Support Technician'))
		{
			$app->logger->log('Unauthorized access to Admin CP', 'ALERT', array(), 'admin');
			$app->output->redirect('/');
		}

		$listings = Listing::find('all', array(
			'conditions' => array('stage = ? OR (stage = ? AND request_takedown = 1)', Listing::STAGE_REVIEW, Listing::STAGE_LIST),
			'order' => 'stage ASC',
			'include' => array('description')
			));

		foreach($listings as $idx => $listing) {
			if(!empty($listing->parent))
				unset($listings[$idx]);
		}

		if(empty($listings))
			$app->output->alert('There currently no listings that require your attention. **Sit back and relax!**');

		$app->output->addBreadcrumb('admin', 'Dashboard');
		$app->output->addBreadcrumb('admin/listings', 'Manage Listings');
		$app->output->setTitle('Manage Listings');
		$app->output->setActiveTab('admin');
		$app->output->render('admin.listings', ['listings' => $listings]);
	}

	public function manageListings($app)
	{
		if(!$app->user->isLoggedIn() || !$app->user->isRank('Senior Support Technician'))
		{
			$app->logger->log('Unauthorized access to Admin CP', 'ALERT', array(), 'admin');
			$app->output->redirect('/');
		}

		$listings = Listing::find('all', array(
			'include' => array('description')
			));

		foreach($listings as $idx => $listing) {
			if(!empty($listing->parent))
				unset($listings[$idx]);
		}

		if(empty($listings))
			$app->output->alert('There currently no listings that require your attention. **Sit back and relax!**');

		$app->output->addBreadcrumb('admin', 'Dashboard');
		$app->output->addBreadcrumb('admin/listings', 'Manage Listings');
		$app->output->setTitle('Manage Listings');
		$app->output->setActiveTab('admin');
		$app->output->render('admin.listings', ['listings' => $listings]);
	}

	public function reviewListing($app, $listing_id = null, $action = null)
	{
		if(!$app->user->isLoggedIn() || !$app->user->isRank('Senior Support Technician'))
		{
			$app->logger->log('Unauthorized access to Admin CP', 'ALERT', array(), 'admin');
			$app->output->redirect('/');
		}

		if(empty($listing_id) || !in_array($action, array('approve', 'deny', 'cancel'))) {
			$app->output->redirect('/admin/listings');
		}
		try {

			$listing = Listing::find($listing_id);
			$children = $listing->children;
			if($action == 'approve') {
				if($listing->stage == Listing::STAGE_LIST)
					throw new Admin_ConcurrencyException('Listing has already been approved');
				if($listing->checkout == 1)
					throw new Admin_ConcurrencyException('Listing has been checked out');

				$listing->setStage('list');

				// For bulk listings
				if(!empty($children)) {
					foreach($children as $idx => $child_listing)
						$child_listing->setStage('list');

					$app->pusher->trigger('bots', 'approveBulkListing', array(
						'listing_id' => (string)$listing->id,
						'items' => array_merge(array_map(function($child) { return (string)$child->item_id; }, $children), array((string)$listing->item_id))
						));
				}
				else {
				// For unique listings
					$app->pusher->trigger('bots', 'approveListing', array(
						'listing_id' => (string)$listing->id,
						'item_id' => (string)$listing->item_id
						));
				}

				$app->pusher->trigger($listing->user_id, 'notification', array('message' => '1'));
			}
			else if($action == 'deny') {
				if($listing->stage == Listing::STAGE_DENY)
					throw new Admin_ConcurrencyException('Listing has already been denied');
				if($listing->checkout == 1)
					throw new Admin_ConcurrencyException('Listing has been checked out');

				$listing->setStage('deny');

				if(!empty($children)) {
					foreach($children as $idx => $child_listing)
						$child_listing->setStage('deny');

					$notification = Notification::create([
						'user_id' => $listing->user_id,
						'receiver_id' => $listing->user_id,
						'title' => 'DENIAL',
						'body' => '**Bulk Listing ('.$listing->description->name.' x '.(count($children)+1).') has been denied!** This can be for one of the following reasons:
*	Invalid Pattern
*	Unreasonable Price
*	Fake Screenshots
*	Invalid Description

You will soon receive a trade offer with the items.'
					]);

					$app->pusher->trigger('bots', 'denyBulkListing', array(
						'listing_id' => (string)$listing->id,
						'user_id' => (string)$listing->user_id,
						'items' => array_merge(array_map(function($child) { return (string)$child->item_id; }, $children), array((string)$listing->item_id))
						));
				}
				else {
					$notification = Notification::create([
						'user_id' => $listing->user_id,
						'receiver_id' => $listing->user_id,
						'title' => 'DENIAL',
						'body' => '**Listing #'.$app->hashids->encrypt($listing->id).' ('.$listing->description->name.') has been denied!** This can be for one of the following reasons:
*	Invalid Pattern
*	Unreasonable Price
*	Fake Screenshots
*	Invalid Description

You will soon receive a trade offer with the items.'
					]);

					$app->pusher->trigger('bots', 'denyListing', array(
						'listing_id' => (string)$listing->id,
						'user_id' => (string)$listing->user_id,
						'item_id' => (string)$listing->item_id
						));
				}

				$app->pusher->trigger($listing->user_id, 'notification', array('message' => '1'));
			}
			else if($action == 'cancel') {
				if($listing->stage != Listing::STAGE_LIST)
					throw new Admin_ConcurrencyException('Listing has already been ordered');
				if($listing->checkout == 1)
					throw new Admin_ConcurrencyException('Listing has been checked out');
				
				$listing->setStage('cancel');

				if(!empty($children)) {
					foreach($children as $idx => $child_listing) {
						if($child_listing->stage != Listing::STAGE_LIST)
							continue;
						$child_listing->setStage('cancel');
					}

					$notification = Notification::create([
						'user_id' => $listing->user_id,
						'receiver_id' => $listing->user_id,
						'title' => 'DENIAL',
						'body' => '**Bulk Listing ('.$listing->description->name.' x '.(count($children)+1).') has been cancelled!**
You will soon receive a trade offer with the items.'
					]);

					$app->pusher->trigger('bots', 'cancelBulkListing', array(
						'listing_id' => (string)$listing->id,
						'user_id' => (string)$listing->user_id,
						'bot_id' => (string)$listing->bot_id,
						'items' => array_merge(array_map(function($child) { return (string)$child->item_id; }, $children), array((string)$listing->item_id))
						));
				}
				else {
					$notification = Notification::create([
						'user_id' => $listing->user_id,
						'receiver_id' => $listing->user_id,
						'title' => 'DENIAL',
						'body' => '**Listing #'.$app->hashids->encrypt($listing->id).' ('.$listing->description->name.') has been cancelled!**
You will soon receive a trade offer with the items.'
					]);

					$app->pusher->trigger('bots', 'cancelListing', array(
						'listing_id' => (string)$listing->id,
						'user_id' => (string)$listing->user_id,
						'bot_id' => (string)$listing->bot_id,
						'item_id' => (string)$listing->item_id
					));
				}

				$app->pusher->trigger($listing->user_id, 'notification', array('message' => '1'));
			}
		}
		catch(Admin_ConcurrencyException $e) {
			$app->logger->log('Attempt to modify a Listing that has been modified', 'ERROR', array('object' => 'Listing', 'id' => $listing_id, 'pathway' => 'manageListings'), 'admin');
		}
		catch(ActiveRecord\RecordNotFound $e) {
			$app->logger->log('No such Listing found', 'ERROR', array('object' => 'Listing', 'id' => $listing_id, 'pathway' => 'manageListings'), 'admin');
			// $app->output->notFound();	
		}
		// catch(Hashids_Invalid $e) {
		// 	$app->logger->log('Listing ID given was invalid', 'ERROR', array('object' => 'Listing', 'id' => $id, 'pathway' => 'listing'), 'user');
		// 	$app->output->notFound();
		// }

		$app->output->redirect('/admin/listings');
	}

	public function botSimul($app)
	{
		if(!$app->user->isLoggedIn() || !$app->user->isRank('Lead Developer'))
		{
			$app->logger->log('Unauthorized access to Admin CP', 'ALERT', array(), 'admin');
			$app->output->redirect('/');
		}

		$app->output->addBreadcrumb('admin', 'Dashboard');
		$app->output->addBreadcrumb('admin/bots', 'Bot Simulator');
		
		$listings = Listing::find('all', array(
			'conditions' => array('stage IN (?) OR (stage = ? AND ISNULL(bot_id))', array(Listing::STAGE_REQUEST, Listing::STAGE_REVIEW, Listing::STAGE_DENY, Listing::STAGE_CANCEL), Listing::STAGE_LIST),
			'order' => 'stage ASC',
			'include' => array('description')
			));

		foreach($listings as $idx => $listing) {
			if(!empty($listing->parent))
				unset($listings[$idx]);
		}

		$orders = Order::find('all', array(
			'conditions' => array('status NOT IN (?)', array(Order::STATUS_PAID, Order::STATUS_CANCELLED))));
		$orders = array_filter($orders, function($o) { return !$o->isComplete(); });
		
		$app->output->setTitle('Bots and Payment');
		$app->output->setActiveTab('admin');
		$app->output->render('admin.bots', ['listings' => $listings, 'orders' => $orders]);
	}

	public function urgentOrders($app)
	{
		if(!$app->user->isLoggedIn() || !$app->user->isRank('Senior Support Technician'))
		{
			$app->logger->log('Unauthorized access to Admin CP', 'ALERT', array(), 'admin');
			$app->output->redirect('/');
		}

		$app->output->addBreadcrumb('admin', 'Dashboard');
		$app->output->addBreadcrumb('admin/orders', 'Manage Orders');
		
		$orders = Order::find('all', array(
			'conditions' => array('status = ?', Order::STATUS_PAID)));
		
		if(empty($orders))
			$app->output->alert('There are currently no orders requiring your review at this time. **Get out there and sell some skins!**');

		$app->output->setTitle('Manage Orders');
		$app->output->setActiveTab('admin');
		$app->output->render('admin.orders', ['orders' => $orders]);
	}

	public function manageOrders($app)
	{
		if(!$app->user->isLoggedIn() || !$app->user->isRank('Senior Support Technician'))
		{
			$app->logger->log('Unauthorized access to Admin CP', 'ALERT', array(), 'admin');
			$app->output->redirect('/');
		}

		$app->output->addBreadcrumb('admin', 'Dashboard');
		$app->output->addBreadcrumb('admin/orders', 'Manage Orders');
		
		$orders = Order::find('all');
		if(empty($orders))
			$app->output->alert('There are currently no orders requiring your review at this time. **Get out there and sell some skins!**');

		$app->output->setTitle('Manage Orders');
		$app->output->setActiveTab('admin');
		$app->output->render('admin.orders', ['orders' => $orders]);
	}

	public function managePages($app)
	{
		if(!$app->user->isLoggedIn() || !$app->user->isRank('Lead Developer'))
		{
			$app->output->redirect('/');
		}

		$app->output->addBreadcrumb('admin', 'Dashboard');
		$app->output->addBreadcrumb('admin/pages', 'Manage Pages');
		
		$pages = Page::find('all');

		$app->output->setTitle('Manage Pages');
		$app->output->setActiveTab('admin');
		$app->output->render('admin.pages', ['pages' => $pages]);
	}

	public function editPage($app, $page_id)
	{
		if(!$app->user->isLoggedIn() || !$app->user->isRank('Lead Developer'))
		{
			$app->logger->log('Unauthorized access to Admin CP', 'ALERT', array(), 'admin');
			$app->output->redirect('/');
		}

		$page = Page::find($page_id);
		$request = $app->router->flight->request();

		if($request->method == 'POST') {
			$page->body = $request->data->body;
			$page->user_id = $app->user->id;
			$page->save();
			$app->output->redirect('/admin/pages');
		}
		else {
			$app->output->addBreadcrumb('admin', 'Dashboard');
			$app->output->addBreadcrumb('admin/pages', 'Manage Pages');
			$app->output->addBreadcrumb('admin/page/'.$page_id, $page->title);

			$app->output->setTitle('Edit Page');
			$app->output->setActiveTab('admin');
			$app->output->render('admin.editPage', ['pageData' => $page]);
		}
	}

	public function feature($app, $listing_id = null)
	{
		if(!$app->user->isLoggedIn() || !$app->user->isRank('Senior Support Technician'))
		{
			$app->logger->log('Unauthorized access to Admin CP', 'ALERT', array(), 'admin');
			$app->output->redirect('/');
		}

		try {
			$listing = Listing::find($listing_id);
			$listing->toggleFeatured();
		}
		catch(ActiveRecord\RecordNotFound $e) {

		}

		$app->output->redirect('/admin/listings');
	}

	public function confirmPayment($app, $order_id)
	{
		if(!$app->user->isLoggedIn() || !$app->user->isRank('Managing Director'))
		{
			$app->logger->log('Unauthorized access to Admin CP', 'ALERT', array(), 'admin');
			$app->output->redirect('/');
		}

		try {
			$order = Order::find($order_id);
			$order->confirm($app);
			$app->output->redirect('/admin/orders');
		}
		catch(ActiveRecord\RecordNotFound $e) {
			$app->output->redirect('/admin/orders');
		}
	}

	public function urgentCashouts($app)
	{
		if(!$app->user->isLoggedIn() || !$app->user->isRank('Managing Director'))
		{
			$app->logger->log('Unauthorized access to Admin CP', 'ALERT', array(), 'admin');
			$app->output->redirect('/');
		}

		$paypal_cashout_url = $app->payment->paypal_paykey_url();

		$cashout_requests = CashoutRequest::find('all', array(
			'conditions' => array('status = ?', CashoutRequest::STATUS_REQUEST)));

		if(empty($cashout_requests)) {
			$app->output->alert('You currently have no cashout requests to manage. **More money for you!**');
		}

		$app->output->addBreadcrumb('admin', 'Dashboard');
		$app->output->addBreadcrumb('admin/cashouts', 'Manage Cashouts');

		$app->output->setTitle('Manage Cashouts');
		$app->output->setActiveTab('admin');
		$app->output->render('admin.cashout', ['paypal_cashout_url' => $paypal_cashout_url, 'cashout_requests' => $cashout_requests]);
	}

	public function manageCashouts($app)
	{
		if(!$app->user->isLoggedIn() || !$app->user->isRank('Managing Director'))
		{
			$app->logger->log('Unauthorized access to Admin CP', 'ALERT', array(), 'admin');
			$app->output->redirect('/');
		}

		$paypal_cashout_url = $app->payment->paypal_paykey_url();

		$cashout_requests = CashoutRequest::find('all');

		if(empty($cashout_requests)) {
			$app->output->alert('You currently have no cashout requests to manage. **More money for you!**');
		}

		$app->output->addBreadcrumb('admin', 'Dashboard');
		$app->output->addBreadcrumb('admin/cashouts', 'Manage Cashouts');

		$app->output->setTitle('Manage Cashouts');
		$app->output->setActiveTab('admin');
		$app->output->render('admin.cashout', ['paypal_cashout_url' => $paypal_cashout_url, 'cashout_requests' => $cashout_requests]);
	}

	// Route used for creating sensitive cashout objects via payment gateways
	// i.e. 'unlimited' creation of transfer/transaction objects
	// In the future, might have to be generalized for other services, e.g. Coinbase
	public function permitCashout($app, $cashout_id)
	{
		if(!$app->user->isLoggedIn() || !$app->user->isRank('Managing Director'))
		{
			$app->logger->log('Unauthorized access to Admin CP', 'ALERT', array(), 'admin');
			$app->output->redirect('/');
		}

		try {
			$cashout_id_dec = $app->hashids->decrypt($cashout_id);
			$cashout = CashoutRequest::find($cashout_id_dec);
			$app->payment->stripe_generate_transfer($cashout);
			$app->output->redirect('/admin/processCashout?cashout_id='.$app->hashids->encrypt($cashout->id));
		}
		catch(Hashids_Invalid $e) {
			$app->logger->log('CashoutRequest ID given was invalid', 'ERROR', array('object' => 'CashoutRequest', 'id' => $id, 'pathway' => 'permitCashout'), 'admin');
			$app->output->notFound();
		}
		catch(ActiveRecord\RecordNotFound $e) {
			$app->logger->log('No such CashoutRequest found', 'ERROR', array('object' => 'CashoutRequest', 'id' => $cashout_id, 'id_dec' => $cashout_id_dec, 'pathway' => 'permitCashout'), 'admin');
			$app->output->notFound();	
		}
	}

	// Route used to complete processing of cashouts
	public function processCashout($app)
	{
		if(!$app->user->isLoggedIn() || !$app->user->isRank('Managing Director'))
		{
			$app->logger->log('Unauthorized access to Admin CP', 'ALERT', array(), 'admin');
			$app->output->redirect('/');
		}

		$request = $app->router->flight->request();
		$cashout_id = $request->query->cashout_id ?: -1;

		try {
			$cashout_id_dec = $app->hashids->decrypt($cashout_id);
			$cashout = CashoutRequest::find($cashout_id_dec);
			$cashout->status = CashoutRequest::STATUS_PAID;
			$cashout->save();

			switch($cashout->provider) {
				case 'coinbase':
					Notification::create([
						'user_id' => $cashout->user_id,
						'receiver_id' => $cashout->user_id,
						'title' => 'MONEY',
						'body' => '**Funds for your Cashout Request #'.$app->hashids->encrypt($cashout->id).' have been dispursed!** You have been credited '.money_format('$%.2n', $cashout->total).'. 
[Claim your BTC](http://coinbase.com/claim/'.$cashout->provider_identifier.') at your convenience.'
					]);
				break;

				case 'stripe':
					Notification::create([
						'user_id' => $cashout->user_id,
						'receiver_id' => $cashout->user_id,
						'title' => 'MONEY',
						'body' => '**Funds for your Cashout Request #'.$app->hashids->encrypt($cashout->id).' have been dispursed!** You have been credited '.money_format('$%.2n', $cashout->total).'. From Stripe\'s FAQ:
	For bank accounts, transfers will be available in the bank account the next business day if created before 21:00 UTC (2pm PST). If the transfer fails (due to a typo in the bank details, for example), it can take up to five business days for Stripe to be notified.

	Transfers to debit cards can take 1 to 2 days to complete. However, unlike with bank accounts, we\'ll know instantaneously if the debit card is not valid when it is added to the recipient.'
					]);
				break;				

				default:
					Notification::create([
						'user_id' => $cashout->user_id,
						'receiver_id' => $cashout->user_id,
						'title' => 'MONEY',
						'body' => '**Funds for your Cashout Request #'.$app->hashids->encrypt($cashout->id).' have been dispursed!** You have been credited '.money_format('$%.2n', $cashout->total).' via PayPal.'
					]);
				break;
			}
		}
		catch(Hashids_Invalid $e) {
			$app->logger->log('CashoutRequest ID given was invalid', 'ERROR', array('object' => 'CashoutRequest', 'id' => $id, 'pathway' => 'processCashout'), 'admin');
			$app->output->notFound();
		}
		catch(ActiveRecord\RecordNotFound $e) {
			$app->logger->log('No such CashoutRequest found', 'ERROR', array('object' => 'CashoutRequest', 'id' => $cashout_id, 'id_dec' => $cashout_id_dec, 'pathway' => 'processCashout'), 'admin');
			$app->output->notFound();	
		}

		$app->output->redirect('/admin/cashouts');
	}

	public function manageUsers($app)
	{
		if(!$app->user->isLoggedIn() || !$app->user->isRank('Support Technician'))
		{
			$app->logger->log('Unauthorized access to Admin CP', 'ALERT', array(), 'admin');
			$app->output->redirect('/');
		}

		$users = User::find('all');

		$app->output->addBreadcrumb('admin', 'Dashboard');
		$app->output->addBreadcrumb('admin/users', 'Manage Users');

		$app->output->setTitle('Manage Users');
		$app->output->setActiveTab('admin');
		$app->output->render('admin.users', ['users' => $users]);		
	}

	public function urgentUsers($app)
	{
		if(!$app->user->isLoggedIn() || !$app->user->isRank('Support Technician'))
		{
			$app->logger->log('Unauthorized access to Admin CP', 'ALERT', array(), 'admin');
			$app->output->redirect('/');
		}

		$users = User::find('all');
		$users = array_filter($users, function($u) { $off = $u->recent_offenses; return !empty($off['listings_cancelled']) || !empty($off['orders_cancelled']); });

		$app->output->addBreadcrumb('admin', 'Dashboard');
		$app->output->addBreadcrumb('admin/users', 'Manage Users');

		$app->output->setTitle('Manage Users');
		$app->output->setActiveTab('admin');
		$app->output->render('admin.users', ['users' => $users]);		
	}

	public function ban($app, $user_id)
	{
		if(!$app->user->isLoggedIn() || !$app->user->isRank('Senior Support Technician'))
		{
			$app->logger->log('Unauthorized access to Admin CP', 'ALERT', array(), 'admin');
			$app->output->redirect('/');
		}

		$request = $app->router->flight->request();
		try {
			$user = User::find($user_id);
			if($request->method == 'POST') {
				$user->rank = User::RANK_BANNED;
				$user->save();

				Notification::create(array(
					'user_id' => $app->user->id,
					'receiver_id' => $user->id,
					'title' => 'BAN',
					'body' => $request->data->body
					));

				$app->output->redirect('/admin/users');
			}
			else {
				$app->output->addBreadcrumb('admin', 'Dashboard');
				$app->output->addBreadcrumb('admin/users', 'Manage Users');

				$app->output->setTitle('Ban User');
				$app->output->setActiveTab('admin');
				$app->output->render('admin.ban', ['ban_user' => $user]);
			}
		
		}
		catch(ActiveRecord\RecordNotFound $e) {
			$app->logger->log('No such User found', 'ERROR', array('object' => 'User', 'id' => $user_id, 'pathway' => 'ban'), 'admin');
			$app->output->notFound();
		}
	}

	public function unban($app, $user_id)
	{
		if(!$app->user->isLoggedIn() || !$app->user->isRank('Senior Support Technician'))
		{
			$app->logger->log('Unauthorized access to Admin CP', 'ALERT', array(), 'admin');
			$app->output->redirect('/');
		}

		$request = $app->router->flight->request();
		try {
			$user = User::find($user_id);
			$user->rank = User::RANK_USER;
			$user->save();

			Notification::create(array(
				'user_id' => $app->user->id,
				'receiver_id' => $user->id,
				'title' => '',
				'body' => 'Your ban has been lifted by '.$app->user->name.'.'
				));

			$app->output->redirect('/admin/users');		
		}
		catch(ActiveRecord\RecordNotFound $e) {
			$app->logger->log('No such User found', 'ERROR', array('object' => 'User', 'id' => $user_id, 'pathway' => 'ban'), 'admin');
			$app->output->notFound();
		}
	}

	public function notify($app, $user_id)
	{
		if(!$app->user->isLoggedIn() || !$app->user->isRank('Support Technician'))
		{
			$app->logger->log('Unauthorized access to Admin CP', 'ALERT', array(), 'admin');
			$app->output->redirect('/');
		}

		try {
			$user = User::find($user_id);
			$request = $app->router->flight->request();

			if($request->method == 'POST') {
				Notification::create([
					'user_id' => $app->user->id,
					'receiver_id' => $user->id,
					'title' => $request->data->title,
					'body' => 'Message sent from ['.$app->user->name.'](http://steamcommunity.com/profile/'.$app->user->id.'):'.$request->data->body
					]);
				$app->output->redirect('/admin/users');
			}
			else {
				$app->output->addBreadcrumb('admin', 'Dashboard');
				$app->output->addBreadcrumb('admin/users', 'Manage Users');
				$app->output->addBreadcrumb('admin/notify/'.$user_id, 'Notify User');

				$app->output->setTitle('Notify User');
				$app->output->setActiveTab('admin');
				$app->output->render('admin.notify', ['user_to_notify' => $user]);	
			}
		}
		catch(ActiveRecord\RecordNotFound $e) {
			$app->logger->log('No such User found', 'ERROR', array('object' => 'User', 'id' => $user_id, 'pathway' => 'ban'), 'admin');
			$app->output->notFound();
		}
	}

	public function manageTickets($app)
	{	
		if(!$app->user->isLoggedIn() || !$app->user->isRank('Support Technician'))
		{
			$app->logger->log('Unauthorized access to Admin CP', 'ALERT', array(), 'admin');
			$app->output->redirect('/');
		}

		$tickets = SupportTicket::find('all', array(
			'order' => 'last_reply DESC'
		));

		$app->output->addBreadcrumb('admin', 'Dashboard');
		$app->output->addBreadcrumb('admin/tickets', 'Manage Support Tickets');
		$app->output->setTitle('Manage Support Tickets');
		$app->output->setActiveTab('admin');

		$app->output->render('admin.tickets', ['tickets' => $tickets]);
	}
}
?>