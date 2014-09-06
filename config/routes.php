<?php
	// TODO: Move links to appropriate controllers..
return [
	'/404' => ['AboutController', 'notfound'],
	'/error' => ['AboutController', 'error'],
	'/banned' => ['AboutController', 'banned'],
	'/preregister' => ['AboutController', 'preregister'],
	
	'/data/listings' => ['DataController', 'listings'],
	'/data/inventory' => ['DataController', 'inventory'],

	// ShopController
	'/' => ['ShopController', 'featuredItems'],
	'/featured' => ['ShopController', 'featuredItems'],
	'/browse' => ['ShopController', 'browseItems'],

	'/listing/@listing_id' => ['ShopController', 'showListing'],
	'/listings/@listing_id' => ['ShopController', 'showStackListing'],
	'/request' => ['ShopController', 'requestListing'],
	'/bulk' => ['ShopController', 'requestBulkListing'],
	'/takedown/@listing_id' => ['ShopController', 'takedownListing'],

	'/cart' => ['ShopController', 'showCart'],
	'/cart/add' => ['ShopController', 'addCart'],
	'/cart/del' => ['ShopController', 'removeCart'],
	'/cart/empty' => ['ShopController', 'emptyCart'],
	'/cart/reset' => ['ShopController', 'resetCart'],
	'/cart/process' => ['ShopController', 'process'],
	'/cart/processCoinbase' => ['ShopController', 'processCoinbase'],
	'/cart/cancel' => ['ShopController', 'cancel'],

	'/order' => ['ShopController', 'order'],
	
	'/items.json' => ['ShopController', 'itemsJSON'],

	// AccountController
	'/account/login' => ['AccountController', 'login'],
	'/account/logout' => ['AccountController', 'logout'],
	'/account/inventory' => ['AccountController', 'inventory'],
	'/account/listings' => ['AccountController', 'myListings'],
	'/account/orders/active' => ['AccountController', 'activeOrders'],
	'/account/orders' => ['AccountController', 'myOrders'],
	'/account/wallet' => ['AccountController', 'wallet'],
	'/account/cashout' => ['AccountController', 'cashout'],
	'/account/invoice/@order_id' => ['AccountController', 'invoice'],
	'/account/settings' => ['AccountController', 'settings'],
	'/account/terms' => ['AccountController', 'terms'],

	// NotificationController
	'/notifications' => ['NotificationController', 'all'],
	'/notifications/mark' => ['NotificationController', 'viewAll'],
	// '/notifications/@id' => ['NotificationController', 'view'],
	// '/notifications/@id/@action' => ['NotificationController', 'action'],

	// SupportController
	'/support' => ['SupportController', 'all'],
	'/support/view/@id' => ['SupportController', 'view'],
	'/support/close/@id' => ['SupportController', 'close'],
	'/support/open/@id' => ['SupportController', 'open'],
	'/support/reply/@id' => ['SupportController', 'reply'],
	'/support/create' => ['SupportController', 'create'],

	// AboutController
	'/help' => ['AboutController', 'info'],
	'/affiliates' => ['AboutController', 'partners'],
	'/staff' => ['AboutController', 'staff'],
	'/bots' => ['AboutController', 'bots'],
	'/terms' => ['AboutController', 'terms'],
	'/privacy' => ['AboutController', 'privacy'],
	'/about/steam' => ['AboutController', 'steamError'],

	// AdminController
	'/admin' => ['AdminController', 'dashboard'],
	'/admin/items' => ['AdminController', 'manageDescriptions'],
	'/admin/listings' => ['AdminController', 'manageListings'],
	'/admin/listings/urgent' => ['AdminController', 'urgentListings'],
	'/admin/listing/@listing_id/@action' => ['AdminController', 'reviewListing'],
	'/admin/feature/@listing_id' => ['AdminController', 'feature'],	
	'/admin/bots' => ['AdminController', 'botSimul'],
	'/admin/pages' => ['AdminController', 'managePages'],	
	'/admin/page/@page_id' => ['AdminController', 'editPage'],	
	'/admin/cashouts' => ['AdminController', 'manageCashouts'],
	'/admin/cashouts/urgent' => ['AdminController', 'urgentCashouts'],
	'/admin/permitCashout/@cashout_id' => ['AdminController', 'permitCashout'],
	'/admin/processCashout' => ['AdminController', 'processCashout'],
	'/admin/users' => ['AdminController', 'manageUsers'],
	'/admin/users/urgent' => ['AdminController', 'urgentUsers'],	
	'/admin/ban/@user_id' => ['AdminController', 'ban'],
	'/admin/unban/@user_id' => ['AdminController', 'unban'],
	'/admin/notify/@user_id' => ['AdminController', 'notify'],
	'/admin/tickets' => ['AdminController', 'manageTickets'],
	'/admin/orders' => ['AdminController', 'manageOrders'],
	'/admin/orders/urgent' => ['AdminController', 'urgentOrders'],
	'/admin/confirm/@order_id' => ['AdminController', 'confirmPayment'],

	// API
	'/api/request' => ['APIController', 'grabItem'],
	'/api/requestComplete' => ['APIController', 'grabItemComplete'],
	'/api/storeComplete' => ['APIController', 'storeComplete'],
	'/api/return' => ['APIController', 'returnItem'],
	'/api/returnComplete' => ['APIController', 'returnItemComplete'],
	'/api/transfer' => ['APIController', 'transferItem'],
	'/api/transferComplete' => ['APIController', 'transferItemComplete'],
	'/api/messages' => ['APIController', 'messages'],
	'/api/tradeUrl' => ['APIController', 'tradeUrl'],
	'/api/requestCancel' => ['APIController', 'cancelListingRequest'],
	'/api/storage' => ['APIController', 'storageBots'],
	'/api/checkout' => ['APIController', 'checkout'],
	'/api/checkin' => ['APIController', 'checkin'],
	'/api/invalidTradeURL' => ['APIController', 'invalid_trade_url'],

	'/api/generateSignature' => ['APIController', 'generateSignature'],
	'/api/cleanup' => ['APIController', 'cleanupOrders']
];