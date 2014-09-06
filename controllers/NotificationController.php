<?php
class NotificationController
{
	const MAX_NOTIFS_SHOWN = 10;
	public function all($app)
	{
		if(!$app->user->isLoggedIn())
		{
			$app->output->redirect('/account/login');
		}

		$request = $app->router->flight->request();
		$page = $request->query->p ?: 0;
		$offset = $page * self::MAX_NOTIFS_SHOWN;
		$total = Notification::count(array(
			'conditions' => array('receiver_id = ? AND deleted = ?', $app->user->id, 0))) / self::MAX_NOTIFS_SHOWN;
		$total = min(ceil($total), 3);	// max total of 3 pages to show
		if($offset < 0 || $page > $total)
			$app->output->redirect('/notifications');

		$notifications = Notification::find('all', [
			'conditions' => ['receiver_id = ? AND deleted = ?', $app->user->id, 0], 
			'order' => 'id DESC',
			'limit' => self::MAX_NOTIFS_SHOWN,
			'offset' => $offset,
			'include' => ['user']
		]);



		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('notifications', 'Notifications');

		$app->output->setTitle('Notifications');
		$app->output->setActiveTab('account');

		$app->output->render('notifications.all', ['notifications' => $notifications, 'page_num' => $page, 'total' => $total]);

		// Mark notifications as read after rendering
		foreach($notifications as $idx => $notification) {
			$notification->seen = 1;
			$notification->save();
		}
	}

	public function viewAll($app)
	{
		$notifications = Notification::find('all', [
			'conditions' => ['receiver_id = ? AND deleted = ?', $app->user->id, 0], 
			'order' => 'id DESC'
		]);
		foreach($notifications as $idx => $notification) {
			$notification->seen = 1;
			$notification->save();
		}

		$app->output->redirect('/notifications');
	}

	public function view($app, $id)
	{
		if(!$app->user->isLoggedIn())
		{
			$app->output->redirect('/account/login');
		}

		try {
			$id_dec = $app->hashids->decrypt($id);
			$notification = Notification::find('first', [
				'conditions' => ['id = ? AND receiver_id = ? AND deleted = ?', $id_dec, $app->user->id, 0], 
				'include' => ['user']
			]);

			if(!$notification)
			{
				$app->logger->log('No such Notification found', 'ERROR', array('object' => 'Notification', 'id' => $id, 'id_decrypted' => $id_dec, 'pathway' => 'notifications_view'), 'user');
				$app->output->notFound();
			}

			if(!$notification->seen)
			{
				$notification->seen = 1;
				$notification->save();
			}

			$app->output->addBreadcrumb('', 'CSGOShop');
			$app->output->addBreadcrumb('notifications', 'Notifications');

			$app->output->setActiveTab('account');
			$app->output->setTitle('Notifications');
			$app->output->render('notifications.view', ['notification' => $notification]);
		}
		catch(Hashids_Invalid $e) {
			$app->logger->log('Notification ID given was invalid', 'ERROR', array('object' => 'Notification', 'id' => $id, 'pathway' => 'notifications_view'), 'user');
			$app->output->notFound();
		}
	}

	public function action($app, $id, $action)
	{
		if(!$app->user->isLoggedIn())
		{
			$app->output->redirect('/');
		}

		if(!in_array($action, ['read', 'unread', 'delete']))
		{
			$app->output->redirect('/');
		}

		try {
			$id_dec = $app->hashids->decrypt($id);
			$notification = Notification::find('first', [
				'conditions' => ['id = ? AND receiver_id = ? AND deleted = ?', $id_dec, $app->user->id, 0], 
				'include' => ['user']
			]);

			if(!$notification)
			{
				$app->logger->log('No such Notification found', 'ERROR', array('object' => 'Notification', 'id' => $id, 'id_decrypted' => $id_dec, 'pathway' => 'notifications_action'), 'user');
				$app->output->notFound();
			}

			if($action == 'read' or $action == 'unread')
			{
				$notification->seen = ($action == 'read' ? 1 : 0);
			}

			if($action == 'delete')
			{
				$notification->deleted = 1;
			}

			$notification->save();

			$app->output->redirect('/notifications');
		}
		catch(Hashids_Invalid $e) {
			$app->logger->log('Notification ID given was invalid', 'ERROR', array('object' => 'Notification', 'id' => $id, 'pathway' => 'notifications_action'), 'user');
			$app->output->notFound();
		}
	}
}