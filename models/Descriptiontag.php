<?php
class Descriptiontag extends ActiveRecord\Model
{
	public static $belongs_to = array(
		array('description'),
		array('tag'));
}