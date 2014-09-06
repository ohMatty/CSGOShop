<?php
// Autoload Monolog + Psr spec
spl_autoload_register(function ($class) {
  $file = __DIR__.strtr($class, '\\', '/').'.php';
  if (file_exists($file)) {
    require_once $file;
    return true;
  }
});

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Log {
	public $app;

	private $channels = array(
		'default' => null,
		'user' => null,
		'api' => null,
		'admin' => null
		);

	public function __construct(&$app)
	{
		$this->app =& $app;
		$output = new StreamHandler('log/error.log', Logger::DEBUG);

		foreach($this->channels as $channel => &$logger) {
			$logger = new Logger($channel);
			$logger->pushHandler($output);
			$logger->pushProcessor(function ($record) use (&$app) {
				$record['extra']['route'] = $app->router->flight->request();
				
				if(empty($app->user) || empty($app->user->session))
					return $record;					

				$user = $app->user;
				$session = $user->session;

				$record['extra']['user'] = array(
					'id' => $user->id,
					'name' => $user->name
					);

				$record['extra']['session'] = array(
					'id' => $session->id,
					'updated_at' => $session->updated_at,
					'user_agent' => $session->user_agent,
					'ip' => $session->ip,
					'$_SESSION' => $_SESSION
					);

				return $record;
			});
		}
	}

	public function log($message, $level = 'DEBUG', $context = array(), $channel = 'default')
	{
		$logger = $this->channels[$channel];
		$logger->addRecord(constant('Monolog\Logger::'.$level), $message, $context);
	}
}