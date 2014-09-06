<?php
class Listing extends ActiveRecord\Model
{
	public static $has_one = array(
		array('orderitem'),
		array('cashoutlisting', 'class_name' => 'CashoutListing')
	);

	public static $belongs_to = array(
		array('user'),
		array('description'),
		array('bot'),
		array('checkout_user', 'foreign_key' => 'checkout_user_id', 'class_name' => 'User'),
		array('parent', 'foreign_key' => 'parent_listing_id', 'class_name' => 'Listing')
	);


	/*

	Stages:
		0 - Requested, needs to be traded to Seller BOT
		1 - Waiting review by admin, in Seller BOT inventory
		2 - Approved by admin, in Storage BOT inventory
		3 - Denied by admin, in Seller BOT inventory
		4 - Ordered by a User, needs to be traded to User
		5 - Buyer has received Item
		6 - Denied, returned to User
		7 - Funds collected by Seller from Wallet, Listing is now archived
		8 - Cancelled by admin, in Storage BOT inventory
	*/
	
	const STAGE_REQUEST = 0;
	const STAGE_REVIEW = 1;
	const STAGE_LIST = 2;
	const STAGE_DENY = 3;
	const STAGE_ORDER = 4;
	const STAGE_COMPLETE = 5;
	const STAGE_DELETE = 6;
	const STAGE_ARCHIVE = 7;
	const STAGE_CANCEL = 8;

	public static function add($userid, $itemid, $descid, $message, $price)
	{
		return Listing::create([
			'user_id' => $userid,
			'item_id' => $itemid,
			'description_id' => $descid,
			'message' => $message,
			'price' => $price
		]);
	}

	public function setStage($stage)
	{
		switch($stage) {
			case 'request':
			$this->stage = self::STAGE_REQUEST;
			break;
			
			case 'review':
			$this->stage = self::STAGE_REVIEW;
			break;

			case 'list':
			$this->stage = self::STAGE_LIST;
			break;

			case 'order':
			$this->stage = self::STAGE_ORDER;
			break;

			case 'complete':
			$this->stage = self::STAGE_COMPLETE;
			break;

			case 'deny':
			$this->stage = self::STAGE_DENY;
			break;

			case 'delete':
			$this->stage = self::STAGE_DELETE;
			break;

			case 'archive':
			$this->stage = self::STAGE_ARCHIVE;
			break;

			case 'cancel':
			$this->stage = self::STAGE_CANCEL;
			break;
		}
		
		$this->save();
	}	

	public function toggleFeatured()
	{
		if($this->featured == 1)
			$this->featured = 0;
		else
			$this->featured = 1;

		$this->save();
	}

	public function get_children()
	{
		return Listing::find('all', array(
			'conditions' => array('parent_listing_id = ? AND stage NOT IN (?)', $this->id, array(self::STAGE_ORDER, self::STAGE_COMPLETE, self::STAGE_ARCHIVE))));
	}
}