<?php
class Description extends ActiveRecord\Model
{
	public static $has_many = array(
		array('descriptiontags')
	);

	public static $alias_attribute = array(
		'condition' => 'name');

	public function get_name_st()
	{
		if($this->is_stattrak)
			return substr($this->name, 12);
		return $this->name;
	}

	public function get_exterior()
	{
		$tags = array_map(function($dt) { return $dt->tag; }, $this->descriptiontags);
		$tags = array_filter($tags, function($tag) { return $tag->category == 'Exterior'; });
		$tags = array_values($tags);
		if(empty($tags))
			return false;

		return $tags[0]->name;
	}

	public function get_is_stattrak()
	{
		return $this->checkTags(array('strange'));
	}

	public static function add($id, $meta)
	{
		
		$desc = Description::create([
			'id' => $id,
			'name' => $meta->name,
			'market_name' => $meta->market_name ?: $meta->name,
			'icon_url' => $meta->icon_url,
			'icon_url_large' => (isset($meta->icon_url_large) ? $meta->icon_url_large : ''),
			'name_color' => $meta->name_color ?: '000000'
		]);

		if(!empty($meta->actions)) {
			foreach($meta->actions as $idx => $action) {
				if($action->name == 'Inspect in Game...') {
					$desc->inspect_url_template = $action->link;
					$desc->save();
					break;
				}
			}
		}

		foreach($meta->tags as $idx => $tag_data) {
			$tag = Tag::find('all', array(
				'conditions' => array('category = ? AND category_name = ? AND internal_name = ? AND name = ?', 
				$tag_data->category,
				$tag_data->category_name,
				$tag_data->internal_name,
				$tag_data->name)));

			if(empty($tag)) {
				$tag = new Tag([
					'category' => $tag_data->category,
					'category_name' => $tag_data->category_name,
					'internal_name' => $tag_data->internal_name,
					'name' => $tag_data->name 
					]);

				if(!$tag->is_valid()) {
					$desc->delete();
					return null;
				}
				else
					$tag->save();
			}
			else
				$tag = $tag[0];

			if($tag_data->category == 'Rarity') {
				$desc->name_color = $tag_data->color;
				$desc->save();
			}

			Descriptiontag::create([
				'description_id' => $desc->id,
				'tag_id' => $tag->id
				]);
		}

		return $desc;
	}

	public function checkTags($tags)
	{
		$counter = 0;
		$myTags = $this->descriptiontags;
		$myTags = array_map(function($descriptiontag) { return $descriptiontag->tag->internal_name; }, $myTags);
		foreach($tags as $idx => $tag) {
			if(in_array($tag, $myTags))
				$counter++;
		}

		return $counter;
	}

	public function matchName($query)
	{
		if($query == '')
			return 0;

		$terms = explode(' ', $query);
		$terms = array_filter($terms, function($term) { return $term != '|'; });
		$terms = implode('|', $terms);

		preg_match('/('.$terms.')+/i', $this->market_name, $matches);
		return count($matches);
	}
}

?>