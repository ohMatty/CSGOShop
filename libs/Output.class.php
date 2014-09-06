<?php
class Output
{
	public $twig;

	public $loader;

	public $app;

	public $alerts = [];

	public $title = 'CSGOShop';

	public $activeTab = 'featured';

	public $breadcrumbs = [];

	public $markdown;

	public function __construct(&$app)
	{
		$this->app =& $app;

		
		$this->includeParsedown();
		$this->includeTwig();
		$this->setupTwig();
	}

	private function includeParsedown() {
		require_once './libs/Parsedown/Parsedown.php';
		require_once './libs/Parsedown/ParsedownExtra.php';
		$this->markdown = new ParsedownExtra();
	}

	private function includeTwig()
	{
		require_once './libs/Twig/Autoloader.php';
		Twig_Autoloader::register();
	}

	private function setupTwig()
	{
		$this->loader = new Twig_Loader_Filesystem($this->app->config->get('template.path'));
		$this->twig = new Twig_Environment($this->loader, [
			'cache' => $this->app->config->get('template.cache'),
			'auto_reload' => $this->app->config->get('template.reload')
		]);
	}

	private function preRenderSetup()
	{
		$app =& $this->app;

		$page = [
			'alerts' => $this->alerts,
			'title' => $this->title,
			'activeTab' => $this->activeTab,
			'breadcrumbs' => $this->breadcrumbs,	
		];

		if($app->user->isSiteDeveloper())
		{
			$page['profiler'] = $app->profiler->fetch();
		}

		$this->twig->addGlobal('page', $page);

		$this->twig->addFunction(new Twig_SimpleFunction('config', function ($key) use (&$app) {
			return $app->config->get($key);
		}));

		$this->twig->addFunction(new Twig_SimpleFunction('relative_time', function ($time = false, $limit = 86400, $format = 'g:i A M jS') {
			if (is_object($time)) $time = $time->format('db');
		    if (is_string($time)) $time = strtotime($time);

		    $now = time();
		    $relative = '';
		    
		    if ($time === $now) $relative = 'now';
		    elseif ($time > $now) {
			    //$relative = 'in the future';
				$diff = $time - $now;

		        if ($diff >= $limit) $relative = date($format, $time);
		        elseif ($diff < 60) {
		            $relative = 'less than one minute';
		        } elseif (($minutes = ceil($diff/60)) < 60) {
		            $relative = $minutes.' minute'.(((int)$minutes === 1) ? '' : 's');
		        } else {
		            $hours = ceil($diff/3600);
		            $relative = 'about '.$hours.' hour'.(((int)$hours === 1) ? '' : 's');
		        }			    
		    }
		    else {
		        $diff = $now - $time;

		        if ($diff >= $limit) $relative = date($format, $time);
		        elseif ($diff < 60) {
		            $relative = 'less than one minute ago';
		        } elseif (($minutes = ceil($diff/60)) < 60) {
		            $relative = $minutes.' minute'.(((int)$minutes === 1) ? '' : 's').' ago';
		        } else {
		            $hours = ceil($diff/3600);
		            $relative = 'about '.$hours.' hour'.(((int)$hours === 1) ? '' : 's').' ago';
		        }
		    }

		    return $relative;
		}));

		$this->twig->addFunction(new Twig_SimpleFunction('markdown', function ($data) {
			return $this->markdown->text($data);
		}));

		$this->twig->addFunction(new Twig_SimpleFunction('hashid', function ($id) use (&$app) {
			return $app->hashids->encrypt($id);
		}));

		$this->twig->addFunction(new Twig_SimpleFunction('truncate', function ($text, $limit = 40) {
			if(strlen($text) < $limit)
				return $text;
			
			$text = $text." ";
			$text = substr($text, 0, $limit);
			$text = substr($text, 0, strrpos($text,' '));
			$text = $text."...";
			return $text;
		}));

		$this->twig->addFunction(new Twig_SimpleFunction('money_format', function ($amount) {
			if(!function_exists('money_format'))
				sprintf('$%.2f', $amount);
				// require_once('./libs/utils/money_format.php');
			return money_format('$%.2n', $amount);
		}));

		$this->twig->addFunction(new Twig_SimpleFunction('imgur_thumb', function ($link, $type = 'm') {
			return preg_replace('/(\.gif|\.jpg|\.png)/', $type.'$1', $link);
		}));

		$this->twig->addGlobal('user', $app->user);
		$this->twig->addGlobal('total_users', count(User::find('all')));
		$this->twig->addGlobal('total_stock', count(Listing::find('all', array('conditions' => array('stage = ?', Listing::STAGE_LIST)))));
		$this->twig->addGlobal('total_ordered', count(Listing::find('all', array('conditions' => array('stage IN (?)', array(Listing::STAGE_COMPLETE, Listing::STAGE_ARCHIVE))))));


		if($app->user->isLoggedIn())
		{
			$notification_count = count(Notification::find('all', ['conditions' => ['receiver_id = ? AND seen = ? AND deleted = ?', $app->user->id, 0, 0]]));
			
			$this->twig->addGlobal('notification_count', $notification_count);
		}
		
		if(!empty($_SESSION['cart'])) {
			$cart_count = array_reduce(array_merge($_SESSION['cart']['listings'], $_SESSION['cart']['bulk']), 
				function($carry, $item) { $carry += $item['qty']; return $carry; }) ?: 0;
		}
		else
			$cart_count = 0;
		$this->twig->addGlobal('cart_count', $cart_count);
		
	}

	public function render($name, $data = [], $output = true)
	{
		$this->preRenderSetup();

		$name = str_replace('.', '/', $name);
		$name = $name . '.tpl';

		$template = $this->twig->render($name, $data);

		if(!$output)
		{
			return $template;
		}
		
		echo $template;
	}

	public function alert($message, $type = 'warning')
	{
		$this->alerts[] = array('message' => $message, 'type' => $type);
	}

	public function setTitle($title)
	{
		$this->title = $title . ' - CSGOShop';
	}

	public function setActiveTab($activeTab)
	{
		$this->activeTab = $activeTab;
	}

	public function addBreadcrumb($link, $text)
	{
		$this->breadcrumbs[] = [
			'link' => $link,
			'text' => $text
		];
	}

	public function redirect($url)
	{
		$this->app->router->flight->redirect($this->app->config->get('core.url') . $url);
	}

	public function notFound()
	{
		$this->app->router->flight->notFound();
	}

	public function page($id)
	{
		$page = Page::find($id);

		$this->setTitle($page->title);
		$this->setActiveTab('about');
		$this->render('about.page', ['pageData' => $page]);
	}

	public function json($data, $code = 200)
	{
		if($this->app->user->isSiteDeveloper()) {
			$data['profiler'] = $this->app->profiler->fetch();
		}

		$this->app->router->flight->json($data, $code);
	}
}