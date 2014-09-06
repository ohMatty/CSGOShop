<?php
// Controller for service for asynchronous data retrieval
class DataController {
	const MAX_LISTINGS_SHOWN = 12;

	public function listings($app)
	{
		$request = $app->router->flight->request();
		$filter = $request->query->getData();
		$name = '';
		$offset = 0;
		$tags = array();

		// Prepare filtering conditions for listings
		if(!empty($filter)) {
			if(isset($filter['name'])) {
				$name = $filter['name'];
				unset($filter['name']);
			}

			if(isset($filter['offset'])) {
				$offset = $filter['offset'];
				unset($filter['offset']);
			}

			$tags = array_values($filter); // grab internal_name's
			$tags = array_filter($tags, function($tag) { return strcmp($tag, '') != 0; });
		}

		$listings = Listing::find('all', array(
			'conditions' => array('stage = ? AND bot_id IS NOT NULL', Listing::STAGE_LIST),
			'include' => 'description'
			));

		// Stack listings into groups by description
		$descriptions = array();
		foreach($listings as $idx => $listing) {

			// Filter out listings based on search params
			$matches = $listing->description->checkTags($tags);
			// Score listings based on similarity to query name
			$matches += $listing->description->matchName($name);

			if($matches > 0 || (empty($filter) && $name == ''))
				$listing->assign_attribute('matches', $matches);
			else 
				continue;

			// Stack listings based on flag, else put into separate unique slot
			if($listing->description->stackable == 1) {
				if(empty($descriptions[$listing->description->id]))
					$descriptions[$listing->description->id] = array($listing);
				else
					array_push($descriptions[$listing->description->id], $listing);
			}
			else
				array_push($descriptions, array($listing));
		}

		// Sort all descriptions
		// First sort listings in each description by price ASC
		foreach($descriptions as $idx => $listings) {
			usort($listings, function ($a, $b) {
				$diff = $b->matches - $a->matches;
				if($diff == 0)
					return ($a->price) - ($b->price);

				return $diff;
			});
		}
		// Then sort descriptions by score DESC, lowest price DESC
		usort($descriptions, function ($a, $b) {			
			$diff = $b[0]->matches - $a[0]->matches;
			if($diff == 0)
				return ($b[0]->price) - ($a[0]->price);

			return $diff;
		});

		$total_descriptions = count($descriptions);
		$descriptions = array_slice($descriptions, $offset, self::MAX_LISTINGS_SHOWN);

		$descriptions_json = array();
		foreach($descriptions as $idx => &$d) {
			$description_json = array(
				'id' => $app->hashids->encrypt($d[0]->id),
				'name' => $d[0]->description->name,
				'name_st' => $d[0]->description->name,
				'price' => money_format('$%.2n', $d[0]->price),
				'icon_url' => $d[0]->description->icon_url_large ?: $d[0]->description->icon_url,
				'name_color' => $d[0]->description->name_color == 'D2D2D2' ? '000000' : $d[0]->description->name_color,
				'qty' => count($d),
				'offset' => $idx,
				'score' => $d[0]->matches
			);

			if($d[0]->description->stackable == 1)
				$description_json['stackable'] = 1;

			if($d[0]->description->is_stattrak)
				$description_json['is_stattrak'] = 1;

			if($d[0]->description->exterior)
				$description_json['exterior'] = $d[0]->description->exterior;

			if ($idx % 4 == 1)
				$description_json['text_align'] = 'text-left';
			elseif ($idx % 4 == 0)
				$description_json['text_align'] = 'text-right';
			else 
				$description_json['text_align'] = 'text-center';

			array_push($descriptions_json, $description_json);
		}

		$app->output->json(array('descriptions' => $descriptions_json, 'total' => $total_descriptions));
	}	

	public function inventory($app)
	{
		try {
			$id = $app->user->id;
			$inventory = $app->steam->getInventory($id);

			// Filter out items already in listings
			$listings = Listing::all(array('conditions' => array('user_id = ? AND stage != ?', $id, Listing::STAGE_DELETE)));
			foreach($listings as $idx => $listing) {
				unset($inventory[$listing->item_id]);
			}

			$inventory_json = array();
			foreach($inventory as $item_id => $item) {
				print_r($item);
				array_push($inventory_json, array(
					'id' => $item->id,
					'market_name' => $item->desc->market_name,
					'stackable' => $item->desc->stackable,
					'price_preset' => $item->desc->price_preset,
					'icon' => $item->desc->icon_url_large ?: $item->desc->icon_url
					));
			}

			$app->output->json(array(
				'error' => false,
				'items' => $inventory_json
				));
		}
		catch(SteamAPIException $e) {
			$app->logger->log('Inventory fetch failed (SteamAPIException)', 'ERROR', array('pathway' => 'inventory'), 'user');
			$app->output->json(array('error' => true, 'message' => 'Steam API could not be reached.'), 503);
		}
		catch(User_InventoryError $e) {
			$app->logger->log('Inventory fetch failed (User_InventoryError)', 'ERROR', array('pathway' => 'inventory'), 'user');
			$app->output->json(array('error' => true, 'message' => 'Your inventory could not be fetched.'), 500);
		}
	}
}