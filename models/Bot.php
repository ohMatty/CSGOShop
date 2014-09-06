<?php
class Bot extends ActiveRecord\Model
{
	public $app;

	const TYPE_TRADE = 0;
	const TYPE_SELLER = 1;
	const TYPE_STORAGE = 2;

	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;

	const STEAMSTATUS_OFFLINE = 0;
	const STEAMSTATUS_ONLINE = 1;


	public function getType()
	{
		if($this->type == self::TYPE_TRADE)
		{
			return 'Trade Bot';
		}
		elseif($this->type == self::TYPE_SELLER)
		{
			return 'Seller Bot';
		}
		elseif($this->type == self::TYPE_STORAGE)
		{
			return 'Storage Bot';
		}
	}

	public function getStatus()
	{
		if($this->status == self::STATUS_ACTIVE)
		{
			if($this->getOnlineStatus() == self::STEAMSTATUS_ONLINE)
			{
				return '<span class="text-success">Online</span>';
			}
			elseif($this->getOnlineStatus() == self::STEAMSTATUS_OFFLINE)
			{
				return '<span class="text-danger">Offline</span>';
			}
		}
		elseif($this->status == self::STATUS_INACTIVE)
		{
			return  '<span class="text-warning">Inactive</span>';
		}
	}

	public function getOnlineStatus()
	{
		return $this->steam_status;
	}

	public function getAvatar()
	{
		return $this->avatar_url;
	}

	public function getName()
	{
		return $this->name;
	}

	public function requiresSync()
	{
		return ($this->last_sync < time());
	}
}