<?php
class Notification extends ActiveRecord\Model
{
	public static $belongs_to = [['user']];

	public function getSender()
	{
		return $this->user;
	}
}