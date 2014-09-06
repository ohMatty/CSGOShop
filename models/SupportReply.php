<?php
class SupportReply extends ActiveRecord\Model
{
	public static $belongs_to = [['user']];
	
	public function getOwner()
	{
		return $this->user;
	}
}