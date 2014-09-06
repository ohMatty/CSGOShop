<?php
class CashoutRequest extends ActiveRecord\Model
{
	public static $has_many = array(
		array('cashoutlistings', 'class_name' => 'CashoutListing'),
		array('listings', 'through' => 'cashoutlisting', 'class_name' => 'Listing')
	);

	public static $belongs_to = array(
		array('user'));

	static $validates_inclusion_of = array(
		array('provider', 'in' => array(
				'paypal',
				'coinbase',
				'stripe'
			))
	);

	const STATUS_REQUEST = 0;
	const STATUS_PAID = 1;

	public function add($listings)
	{
		foreach($listings as $idx => $listing) {
			CashoutListing::create([
				'listing_id' => $listing->id,
				'cashout_request_id' => $this->id
				]);
		}
	}

	public function get_total_taxed()
	{
		return $this->total;
	}
}