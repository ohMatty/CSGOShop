<?php
class SupportTicket extends ActiveRecord\Model
{
	public static $belongs_to = [['user']];
	
	const STATUS_OPEN = 0;
	const STATUS_CLOSED = 1;
	const STATUS_STAFFREPLY = 2;
	const STATUS_CUSTOMERREPLY = 3;

	public function getStatus()
	{
		if($this->status == self::STATUS_OPEN)
		{
			return 'Open';
		}
		elseif($this->status == self::STATUS_CLOSED)
		{
			return 'Closed';
		}
		elseif($this->status == self::STATUS_STAFFREPLY)
		{
			return 'Waiting for staff reply';
		}
		elseif($this->status == self::STATUS_CUSTOMERREPLY)
		{
			return 'Waiting for customer reply';
		}
	}

	public function getOwner()
	{
		return $this->user;
	}
}