<?php
class CSGOShop
{
	public $config;
	public $output;
	public $router;
	public $logger;
	public $profiler;

	public $steam;
	public $openid;
	public $pusher;
	public $imgur;

	public $user;
	public $payment;
	public $hashids;

	public function __construct($output = true)
	{
		ini_set('date.timezone', 'GMT');
		
		$this->initClasses();
		
		session_save_path($this->config->get('core.session'));
		session_start();
		
		$this->loadUser();

		if($output)
		{
			$in_redirect_endpoint = false;
			$redirect_endpoints = ['preregister', 'banned', 'account/terms', 'account/settings', 'account/login', 'account/logout'];

			foreach ($redirect_endpoints as $endpoint)
			{
				if(strpos($_SERVER['REQUEST_URI'], $endpoint) !== false)
				{
					$in_redirect_endpoint = true;
				}
			}

			$this->logger->log($_SERVER['REQUEST_URI'], 'DEBUG');

			if($this->config->get('mode') == 'preregistration' && !$in_redirect_endpoint) {
				$this->output->redirect('/preregister');
			}
			if($this->user->isLoggedIn() && $this->user->isRank('Banned') && !$in_redirect_endpoint)
			{
				$this->output->redirect('/banned');
			}
			elseif($this->user->isLoggedIn() and $this->user->tos_agree == 0 && !$in_redirect_endpoint)
			{
				$this->output->redirect('/account/terms');
			}
			elseif($this->user->isLoggedIn() && empty($this->user->trade_url) && !$in_redirect_endpoint)
			{
				$this->output->redirect('/account/settings');
			}

			$this->router->resolve();
		}
	}

	private function initClasses()
	{
		require_once './libs/Config.class.php';
		$this->config = new Config($this);

		require_once './libs/Output.class.php';
		$this->output = new Output($this);

		require_once './libs/Router.class.php';
		$this->router = new Router($this);

		require_once './libs/Steam.class.php';
		$this->steam = new Steam($this);

		require_once './libs/LightOpenID.class.php';
		$this->openid = new LightOpenID($_SERVER['SERVER_NAME']);
		$this->openid->identity = 'http://steamcommunity.com/openid';

		require_once './libs/Pusher/Pusher.php';
		$this->pusher = new Pusher(
			$this->config->get('pusher.appKey'),
			$this->config->get('pusher.appSecret'),
			$this->config->get('pusher.appId'));

		require_once './libs/Payment.class.php';
		$this->payment = new Payment($this);

		require_once './libs/Hash.class.php';
		$this->hashids = new Hash($this->config);

		require_once './libs/imgur-php-wrapper/Imgur.php';
		$this->imgur = new Imgur\Imgur(
			$this->config->get('imgur.appId'),
			$this->config->get('imgur.appSecret'));

		require_once './libs/Log.class.php';
		$this->logger = new Log($this);
		require_once './libs/Exceptions.class.php';

		require_once './libs/Profiler.class.php';
		$this->profiler = new Profiler($this);

		$this->setupActiveRecord();
	}

	private function setupActiveRecord()
	{
		require_once './libs/php-activerecord/ActiveRecord.php';

		$connectionString = sprintf('%s://%s:%s@%s/%s',
			$this->config->get('database.type'),
			$this->config->get('database.user'),
			$this->config->get('database.pass'),
			$this->config->get('database.host'),
			$this->config->get('database.name')
		);

		$cfg = ActiveRecord\Config::instance();
		$cfg->set_model_directory($this->config->get('database.models'));
		$cfg->set_connections([
			'development' => $connectionString
		]);
		$cfg->set_logging(true);
		$cfg->set_logger($this->profiler);
	}

	private function loadUser()
	{
		$this->user = Session::verifySession($this);
	}
}
