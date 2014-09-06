<?php
class Order extends ActiveRecord\Model
{
	public static $belongs_to = array(
		array('user'));

	public static $has_many = array(
		array('orderitems', 'foreign_key' => 'order_id'),
		array('listings', 'through' => 'orderitem', 'foreign_key' => 'order_id')
	);

	const STATUS_UNPAID = 0;
	const STATUS_PAID = 1;
	const STATUS_PAID_CONFIRM = 2;
	const STATUS_CANCELLED = 3;

	const TIMEOUT_LIMIT = 10; // measured in minutes

	public function add($listings)
	{	
		foreach($listings as $idx => $listing) {
			Orderitem::create([
				'listing_id' => $listing->id,
				'order_id' => $this->id
				]);
		}
	}

	public function get_time_limit()
	{
		return strtotime($this->updated_at->format('db')) + self::TIMEOUT_LIMIT * 60;
	}

	public function get_expired()
	{
		return $this->get_time_limit() < time();
	}

	public function get_total_taxed()
	{
		$total = $this->total;
		$total_taxed = round($total * 1.08, 2);	// 8% tax 
		return $total_taxed;
	}

	public function get_trade_url()
	{
		return $this->listings[0]->trade_url;
	}

	public function get_trade_code()
	{
		return $this->listings[0]->trade_code;
	}

	public function isComplete()
	{
		return $this->listings[0]->stage == Listing::STAGE_COMPLETE || $this->listings[0]->stage == Listing::STAGE_ARCHIVE;
	}

	public function toTable()
	{
		$table = array('listings' => array(), 'bulk' => array());

		foreach($this->listings as $idx => $listing) {
			if($listing->description->stackable == 1) {
				
				if(empty($table['bulk'][$listing->description_id])) {
					$old_qty = 0;
					$old_subtotal = 0;
				}
				else {
					$old_qty = $table['bulk'][$listing->description_id]['qty'];
					$old_subtotal = $table['bulk'][$listing->description_id]['subtotal'];
				}

				$table['bulk'][$listing->description_id] = array(
					'listing' => $listing, 
					'qty' => $old_qty + 1,
					'subtotal' => $old_subtotal + $listing->price);
			}
			else
				$table['listings'][$listing->id] = array('listing' => $listing, 'qty' => 1, 'max' => 1);
		}

		foreach($table['bulk'] as $description_id => &$item) {
			$item['max'] = $item['qty'];
			$item['unit_price'] = $item['subtotal'] / $item['qty'];
		}

		return $table;
	}

	// Confirms payment for an order and triggers respective alerts
	// TODO: OH MY GOD PASSING IN THE APP DO YOU WANT TO REGRET THIS LATER??
	public function confirm($app)
	{
		$this->status = Order::STATUS_PAID_CONFIRM;
		$this->save();
		$listings = array();

		foreach($this->listings as $idx => $listing) {
			Notification::create([
				'user_id' => $this->user_id,
				'receiver_id' => $listing->user_id,
				'title' => 'APPROVAL',
				'body' => '**Listing #'.$app->hashids->encrypt($listing->id).' ('.$listing->description->name.') has been ordered!** 
Someone has purchased your item! Once they have completed their order and received the item, funds will be deposited in your CSGOShop wallet.
You can read more about your wallet [here]('.$app->config->get('core.url').'/help#wallet).'
			]);

			array_push($listings, array(
				'order_id' => (string)$this->id,
				'user_id' => (string)$this->user_id,
				'item_id' => (string)$listing->item_id,
				'bot_id' => (string)$listing->bot_id
			));

			$app->pusher->trigger($listing->user_id, 'notification', array('message' => '1'));
		}

		Notification::create([
			'user_id' => $this->user_id,
			'receiver_id' => $this->user_id,
			'title' => 'APPROVAL',
			'body' => '**Payment for Order #'.$app->hashids->encrypt($this->id).'has been confirmed!** 
Your order has been confirmed and a bot will send you a trade offer soon with your purchased items.'
		]);

		$app->pusher->trigger($this->user_id, 'notification', array('message' => '1'));

		$app->pusher->trigger('bots', 'paidOrder', $listings);
	}
}
?>