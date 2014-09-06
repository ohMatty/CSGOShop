<?php
return [
	'mode' => 'development',

	'template' => [
		'path' => './templates',
		'cache' => './cache',
		'reload' => true
	],

	'database' => [
		'type' => 'mysql',
		'host' => '127.0.0.1',
		'user' => 'root',
		'pass' => 'mysql',
		'name' => '',
		'models' => './models'
	],

	'steam' => [
		'apiKey' => '',
		'maxInventory' => 1050
	],

	'pusher' => [
		'appId' => '',
		'appKey' => '',
		'appSecret' => ''
	],

	'api' => [
		'keys' => array('matchthiscodewithbotsapicode')
	],

	'coinbase' => [
		'key' => '',
		'secret' => ''
	],

	'paypal' => [
		'mode' => 'sandbox',
		'user' => '',
		'pass' => '',
		'signature' => '',
		'currency' => 'USD'
	],

	'stripe' => [
		'key' => '',
		'secret' => ''
	],

	'imgur' => [
		'appId' => '',
		'appSecret' => '',
	],

	'salt' => 'putsomethingstronghere',

	'core' => [
		'url' => '127.0.0.1',
		'static' => '127.0.0.1/static',
		/*
		'session' => '/home/staging/tmp'
		*/
	]
];
