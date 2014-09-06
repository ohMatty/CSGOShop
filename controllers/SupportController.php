<?php
class SupportController
{
	public function all($app)
	{
		if(!$app->user->isLoggedIn())
		{
			$app->output->redirect('/account/login');
		}

		$app->output->addBreadcrumb('', 'CSGOShop');
		$app->output->addBreadcrumb('support', 'Support Tickets');

		$app->output->setTitle('Support Tickets');
		$app->output->setActiveTab('support');

		$tickets = SupportTicket::find('all', [
			'conditions' => ['user_id = ?', $app->user->id], 
			'order' => 'last_reply DESC'
		]);

		$app->output->render('support.all', ['tickets' => $tickets]);
	}

	public function view($app, $id)
	{
		if(!$app->user->isLoggedIn())
		{
			$app->output->redirect('/account/login');
		}

		try {
			$id_dec = $app->hashids->decrypt($id);
			$ticket = SupportTicket::find('first', [
				'conditions' => ['id = ?', $id_dec], 
				'include' => ['user']
			]);

			if(!$ticket || !($app->user->isRank('Support Technician') || $app->user->id == $ticket->user_id))
			{
				$app->output->redirect('/support');
			}

			$replys = SupportReply::find('all', [
				'conditions' => ['support_id = ?', $ticket->id], 
				'order' => 'id ASC'
			]);

			$app->output->addBreadcrumb('', 'CSGOShop');
			$app->output->addBreadcrumb('support', 'Support Tickets');
			$app->output->addBreadcrumb('support/view/' . $id, 'Ticket #' . $id);

			$app->output->setActiveTab('support');
			$app->output->setTitle('Support Tickets');
			$app->output->render('support.view', [
				'ticket' => $ticket, 
				'replys' => $replys
			]);
		}
		catch(Hashids_Invalid $e) {
			$app->logger->log('SupportTicket ID given was invalid', 'ERROR', array('object' => 'SupportTicket', 'id' => $id, 'pathway' => 'view'), 'user');
			$app->output->notFound();
		}
	}

	public function close($app, $id)
	{
		if(!$app->user->isLoggedIn())
		{
			$app->output->redirect('/');
		}

		try {
			$id_dec = $app->hashids->decrypt($id);
			$ticket = SupportTicket::find('first', [
				'conditions' => ['id = ?', $id_dec], 
				'include' => ['user']
			]);

			if(!$ticket || !($app->user->isRank('Support Technician') || $app->user->id == $ticket->user_id))
			{
				$app->output->redirect('/support');
			}

			$ticket->status = SupportTicket::STATUS_CLOSED;
			$ticket->save();

			$app->output->redirect('/support/view/' . $id);
		}
		catch(Hashids_Invalid $e) {
			$app->logger->log('SupportTicket ID given was invalid', 'ERROR', array('object' => 'SupportTicket', 'id' => $id, 'pathway' => 'close'), 'user');
			$app->output->notFound();
		}
	}

	public function open($app, $id)
	{
		if(!$app->user->isLoggedIn())
		{
			$app->output->redirect('/');
		}

		try {
			$id_dec = $app->hashids->decrypt($id);
			$ticket = SupportTicket::find('first', [
				'conditions' => ['id = ?', $id_dec], 
				'include' => ['user']
			]);

			if(!$ticket || !($app->user->isRank('Support Technician') || $app->user->id == $ticket->user_id))
			{
				$app->output->redirect('/support');
			}

			$ticket->status = SupportTicket::STATUS_OPEN;
			$ticket->save();

			$app->output->redirect('/support/view/' . $id);
			
		}
		catch(Hashids_Invalid $e) {
			$app->logger->log('SupportTicket ID given was invalid', 'ERROR', array('object' => 'SupportTicket', 'id' => $id, 'pathway' => 'open'), 'user');
			$app->output->notFound();
		}
	}

	public function reply($app, $id)
	{
		if(!$app->user->isLoggedIn())
		{
			$app->output->redirect('/');
		}

		try {
			$id_dec = $app->hashids->decrypt($id);
			$ticket = SupportTicket::find('first', [
				'conditions' => ['id = ? AND status != ?', $id_dec, SupportTicket::STATUS_CLOSED], 
				'include' => ['user']
			]);

			if(!$ticket || !($app->user->isRank('Support Technician') || $app->user->id == $ticket->user_id))
			{
				$app->output->redirect('/support');
			}


			$request = $app->router->flight->request();

			$reply = SupportReply::create([
				'support_id' => $ticket->id,
				'user_id' => $app->user->id,
				'body' => ($request->data->body ? $request->data->body : ''),
			]);
			$reply->reload();

			$ticket->last_reply = $reply->created_at;
			$ticket->status = $app->user->isRank('Support Technician') && $app->user->id != $ticket->user_id ? SupportTicket::STATUS_CUSTOMERREPLY : SupportTicket::STATUS_STAFFREPLY;
			$ticket->save();

			if($ticket->status == SupportTicket::STATUS_CUSTOMERREPLY) {
				$notification = Notification::create([
					'user_id' => $app->user->id,
					'receiver_id' => $ticket->user_id,
					'title' => 'SUPPORT',
					'body' => 'A staff member has responded to your open support ticket ([#'.$app->hashids->encrypt($ticket->id).']('.$app->config->get('core.url').'/support/view/'.$app->hashids->encrypt($ticket->id).')).'
				]);
			}
			else if($ticket->status == SupportTicket::STATUS_STAFFREPLY) {
				$replies = SupportReply::find('all', [
					'conditions' => ['support_id = ?', $ticket->id], 
					'order' => 'id ASC'
				]);
				$ticket_user = $ticket->user_id;
				$staff = array_filter(
					array_unique(array_map(function($reply) { return $reply->user_id; }, $replies)),
					function($staff_member) use($ticket_user) { return $staff_member != $ticket_user; });

				foreach($staff as $idx => $staff_member) {
					$notification = Notification::create([
						'user_id' => $ticket->user_id,
						'receiver_id' => $staff_member,
						'title' => 'SUPPORT',
						'body' => 'A user has responded to an open support ticket ([#'.$app->hashids->encrypt($ticket->id).']('.$app->config->get('core.url').'/support/view/'.$app->hashids->encrypt($ticket->id).')) that you have addressed.'
					]);
				}
			}

			$app->output->redirect('/support/view/' . $id);
		}
		catch(Hashids_Invalid $e) {
			$app->logger->log('SupportTicket ID given was invalid', 'ERROR', array('object' => 'SupportTicket', 'id' => $id, 'pathway' => 'reply'), 'user');
			$app->output->notFound();
		}
	}

	public function create($app)
	{
		$request = $app->router->flight->request();
		if($request->method != 'POST') {
			$app->output->addBreadcrumb('', 'CSGOShop');
			$app->output->addBreadcrumb('support/', 'Support');
			$app->output->addBreadcrumb('support/create', 'Create a Ticket');

			$app->output->setTitle('Create a Ticket');
			$app->output->setActiveTab('support');
			$app->output->render('support.create', []);
		}
		else {
			SupportTicket::create([
				'user_id' => $app->user->id,
				'subject' => '['.$request->data->category.'] '.$request->data->subject,
				'body' => $request->data->body
				]);

			$app->output->redirect('/support');
		}
	}
}