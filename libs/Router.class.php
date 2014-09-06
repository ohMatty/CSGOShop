<?php
class Router
{
	public $app;

	public $flight;

	public $routes;

	public function __construct(&$app)
	{
		$this->app =& $app;

		$this->includeRoutes();
		$this->includeFlight();

		$this->setupFlight();
		$this->setupRoutes();
	}

	private function includeRoutes()
	{
		if(!file_exists('./config/routes.php'))
		{
			exit;
		}

		$this->routes = require_once './config/routes.php';
	}

	private function includeFlight()
	{
		require_once './libs/flight/autoload.php';
	}

	public function routes()
	{
		return $this->routes;
	}

	private function setupFlight()
	{
		$this->flight = new flight\Engine();

		$app = $this->app;
		$this->flight->map('notFound', function () use (&$app) {
			$app->logger->log('Page not found', 'ERROR');
			$app->output->redirect('/404');
		});

		$this->flight->map('error', function (Exception $ex) use (&$app) {
			if($app->config->get('mode') == 'production')
			{
				$app->logger->log('Uncaught Exception ('.get_class($ex).')', 'CRITICAL', array(
						'code' => $ex->getCode(), 
						'message' => $ex->getMessage(), 
						'trace' => $ex->getTrace()));
				$app->output->redirect('/error');
			}
			else
			{
				echo '<strong>' . $ex->getCode() . '</strong>';
				echo $ex->getMessage() . '<br>';
				print_r($ex->getTrace());
			}
		});
	}

	private function setupRoutes()
	{
		foreach ($this->routes as $uri => $route)
		{
			$controller = $route[0];
			$method = $route[1];
			$app =& $this->app;

			$this->flight->route($uri, function() use($controller, $method, &$app) {
				require_once './controllers/' . $controller . '.php';

				$args = func_get_args();
				$class = new $controller($app);

				array_unshift($args, $app);
				call_user_func_array([$class, $method], $args);
			});
		}
	}

	public function resolve()
	{
		$this->flight->start();
	}
}
