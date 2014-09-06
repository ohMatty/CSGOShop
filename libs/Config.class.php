<?php
class Config
{
	public $config;

	public $app;

	public function __construct(&$app)
	{
		$this->app =& $app;
		
		$this->includeConfig();
	}

	private function includeConfig()
	{
		if(!file_exists('./config/app.php'))
		{
			exit;
		}

		$this->config = require_once './config/app.php';
	}

	public function config()
	{
		return $this->config;
	}

	public function get($key)
	{
		$keys = explode(".", $key);
		$config = $this->config();

		foreach ($keys as $key)
		{
			if(isset($config[$key]))
			{
				$config = $config[$key];
			}
		}

		return $config;
	}
}