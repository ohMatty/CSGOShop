<?php
class Page extends ActiveRecord\Model
{
	public static $belongs_to = [['user']];

	public function getUser()
	{
		return $this->user;
	}
}