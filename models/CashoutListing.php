<?php
class CashoutListing extends ActiveRecord\Model
{
	public static $belongs_to = array(
		array('cashoutrequest'),
		array('listing'));
}