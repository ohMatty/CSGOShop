<?php
class Orderitem extends ActiveRecord\Model {
	public static $belongs_to = array(
		array('order'),
		array('listing'));
}
?>