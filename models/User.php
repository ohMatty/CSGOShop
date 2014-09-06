<?php
class User extends ActiveRecord\Model
{
	const RANK_BANNED = 0;
	const RANK_USER = 10;
	const RANK_CUSTOMER = 20;
	const RANK_SELLER = 30;
	const RANK_MODERATOR = 60;
	const RANK_SRMODERATOR = 65;
	const RANK_ADMINISTRATOR = 70;
	const RANK_HRO = 75;
	const RANK_HEADADMINISTRATOR = 80;
	const RANK_OWNER = 90;

	const STEAMSTATUS_OFFLINE = 0;
	const STEAMSTATUS_ONLINE = 1;
	
	const CASHOUT_LIMIT = 604800; // one week in seconds
	const FLAG_OFFENSE_THRESHOLD = 3; // number of offenses before being flagged

	public static $has_many = array(
		array('listings'),
		array('orders')
	);

	public static $has_one = array(
		array('session'));

	public function isLoggedIn()
	{
		return ($this->id ? true : false);
	}

	public function isRank($name)
	{
		if($name == 'Managing Director')
			return ($this->rank >= self::RANK_OWNER);
		elseif($name == 'Technical Director')
			return ($this->rank >= self::RANK_HEADADMINISTRATOR);
		elseif($name == 'Lead Developer')
			return ($this->rank >= self::RANK_ADMINISTRATOR);
		elseif($name == 'Human Resources Officer')
			return ($this->rank >= self::RANK_HRO);
		elseif($name == 'Senior Support Technician')
			return ($this->rank >= self::RANK_SRMODERATOR);
		elseif($name == 'Support Technician')
			return ($this->rank >= self::RANK_MODERATOR);
		elseif($name == 'Seller')
			return ($this->rank >= self::RANK_SELLER);
		elseif($name == 'Customer')
			return ($this->rank >= self::RANK_CUSTOMER);
		elseif($name == 'User')
			return ($this->rank >= self::RANK_USER);
		elseif($name == 'Banned')
			return ($this->rank == self::RANK_BANNED);
	}

	public function getRank()
	{
		if($this->rank >= self::RANK_OWNER)
			return 'Managing Director';
		elseif($this->rank >= self::RANK_HEADADMINISTRATOR)
			return 'Technical Director';
		elseif($this->rank >= self::RANK_HRO)
			return 'Human Resources Officer';
		elseif($this->rank >= self::RANK_ADMINISTRATOR)
			return 'Lead Developer';
		elseif($this->rank >= self::RANK_SRMODERATOR)
			return 'Senior Support Technician';
		elseif($this->rank >= self::RANK_MODERATOR)
			return 'Support Technician';
		elseif($this->rank >= self::RANK_SELLER)
			return 'Seller';
		elseif($this->rank >= self::RANK_CUSTOMER)
			return 'Customer';
		elseif($this->rank >= self::RANK_USER)
			return 'User';
		elseif($this->rank >= self::RANK_BANNED)
			return 'Banned';
	}

	public function getAvatar()
	{
		return $this->avatar_url;
	}

	public function getStatus()
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

	public function getOnlineStatus()
	{
		return $this->steam_status;
	}

	public function requiresSync()
	{
		return ($this->last_sync < time());
	}

	public function logout()
	{
		$session = Session::find_by_hash($_COOKIE['csgoshop_session']);
		$session->delete();

		setcookie('csgoshop_session', -1, -1, '/');
		setcookie('csrf', -1, -1, '/');
	}

	public function get_rank_label()
	{
		return $this->getRank();
	}

	public function get_recent_offenses()
	{
		$listings_cancelled = Listing::find('all', array(
			'conditions' => array('user_id = ? AND stage IN (?)', $this->id, array(Listing::STAGE_DELETE, Listing::STAGE_CANCEL)),
			'order' => 'updated_at DESC'));
		$orders_cancelled = Order::find('all', array(
			'conditions' => array('user_id = ? AND status = ?', $this->id, Order::STATUS_CANCELLED),
			'order' => 'updated_at DESC'));

		// Doing time filter using PHP because all time zone funkiness is handled by server code ;(
		$now = time();
		$oldest = $now - 2592000; // 1 month in seconds
		$listings_cancelled = array_filter($listings_cancelled, function($l) use ($oldest) { return strtotime($l->updated_at->format('db')) > $oldest; });
		$orders_cancelled = array_filter($orders_cancelled, function($o) use ($oldest) { return strtotime($o->updated_at->format('db')) > $oldest; });

		if(count($listings_cancelled) + count($orders_cancelled) < self::FLAG_OFFENSE_THRESHOLD)
			return array('listings_cancelled' => 0, 'orders_cancelled' => 0, 'total' => 0);
		
		return array(
			'listings_cancelled' => $listings_cancelled,
			'orders_cancelled' => $orders_cancelled,
			'total' => count($listings_cancelled) + count($orders_cancelled)
			);
	}

	public function isSiteDeveloper()
	{
		if(!$this->isLoggedIn())
			return false;

		if(!in_array($this->id, ['76561198034369542', '76561198054379814']))
			return false;

		return true;
	}


	public function get_cooldown()
	{
		$last_cashout = CashoutRequest::find('first', array(
			'conditions' => array('user_id = ?', $this->id),
			'order' => 'created_at DESC'
			));

		if(empty($last_cashout))
			return false;

		$diff = time() - strtotime($last_cashout->created_at->format('db'));
		if($diff > self::CASHOUT_LIMIT)
			return false;
		
		return $diff;
	}
}