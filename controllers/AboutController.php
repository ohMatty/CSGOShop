<?php
class AboutController
{
	public function error($app)
	{
		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('/500', '500');	

		$app->output->setTitle('500 Internal server error');
		$app->output->render('about.error');
	}

	public function notfound($app)
	{
		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('/404', '404');	

		$app->output->setTitle('404 Page not Found');
		$app->output->render('about.404');	
	}

	public function banned($app)
	{
		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('/banned', 'Banned Notice');	

		$notification = Notification::find('first', array(
			'conditions' => array('receiver_id = ? AND title = "BAN"', $app->user->id)));
		if(!empty($notification))
			$app->output->alert($notification->body, 'danger');

		$app->output->setTitle('Banned');
		$app->output->render('about.banned');
	}

	public function preregister($app)
	{
		if(!$app->user->isLoggedIn() && $app->steam->login())
		{
			$app->output->redirect('/preregister');
		}
		if($app->config->get('mode') != 'preregistration') {
			$app->output->redirect('/');
		}

		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('/preregister', 'Preregistration');	

		$app->output->setTitle('Preregistration');
		$app->output->render('about.preregister', ['steamLoginUrl' => $app->steam->loginUrl()]);
	}

	public function info($app)
	{
		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('about/info', 'Information');
		
		$app->output->page(1);
	}

	public function partners($app)
	{
		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('about/partners', 'Affiliates');

		$app->output->setTitle('Affiliates');
		$app->output->render('about.partners');
	}

	public function terms($app)
	{
		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('about/terms', 'Terms of Service');

		$app->output->page(3);
	}

	public function privacy($app)
	{
		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('about/privacy', 'Privacy Policy');

		$app->output->page(4);
	}

	public function staff($app)
	{
		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('about/staff', 'Staff Team');

		$app->output->setTitle('Staff Team');
		$app->output->setActiveTab('about');

		$staff = User::all([
			'conditions' => ['rank >= ?', User::RANK_MODERATOR],
			'order' => 'rank DESC'
		]);

		$steamIDs = [];

		foreach ($staff as $user)
		{
			if($user->requiresSync())
			{
				$steamIDs[] = $user->id;
			}
		}

		if(count($steamIDs) > 0)
		{
			$data = $app->steam->getUser($steamIDs);
			
			foreach ($staff as $user)
			{
				if($user->requiresSync())
				{
					$user->name = $data[$user->id]->personaname;
					$user->avatar_url = $data[$user->id]->avatarfull;
					$user->steam_status = ($data[$user->id]->personastate > 0 ? 1 : 0);
					$user->last_sync = time() + 600;
					$user->save();
				}
			}
		}

		$app->output->render('about.staff', ['staff' => $staff]);
	}

	public function bots($app)
	{
		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('about/bots', 'Bots');

		$app->output->setTitle('Bots');
		$app->output->setActiveTab('about');

		$bots = Bot::all(['order' => 'type']);

		$steamIDs = [];

		foreach ($bots as $bot)
		{
			if($bot->requiresSync())
			{
				$steamIDs[] = $bot->id;
			}
		}

		if(count($steamIDs) > 0)
		{
			$data = $app->steam->getUser($steamIDs);
			
			foreach($bots as $bot)
			{
				if($bot->requiresSync())
				{
					$bot->name = $data[$bot->id]->personaname;
					$bot->avatar_url = $data[$bot->id]->avatarfull;
					$bot->steam_status = ($data[$bot->id]->personastate > 0 ? 1 : 0);
					$bot->last_sync = time() + 600;
					$bot->save();
				}
			}
		}

		// Sort bots by status DESC, type DESC
		usort($bots, function($a, $b) {
			if($a->steam_status == $b->steam_status)
				return $a->type > $b->type;
			return $a->steam_status < $b->steam_status;
		});

		$app->output->render('about.bots', ['bots' => $bots]);
	}

	public function steamError($app)
	{
		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('about/steam', 'Steam API Error');

		$app->output->setTitle('Steam API Error');
		$app->output->setActiveTab('about');
		$app->output->render('about.steam');
	}
}