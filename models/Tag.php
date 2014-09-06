<?php
class Tag extends ActiveRecord\Model
{
	static $validates_exclusion_of = array(
		array('name', 'in' => array(
				'Mission',
				'Container',
				'Tag',
				'Collectible',
				'Pass',
				'Not Painted',
				'C4'
			))
	);
}