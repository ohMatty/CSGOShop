<?php
class Steam
{
	public $app;

	public $apiKey;

	const INVENTORY_TIMEOUT = 5;
	const STEAM_AGE_THRESHOLD = 31536000; // a year in seconds
	private static $ITEM_MAP = [
		'duplicate_id' => 'real_id'
	];

	public function __construct(&$app)
	{
		$this->app =& $app;
		$this->apiKey = $app->config->get('steam.apiKey');
	}

	public function login()
	{
		try {
			if($this->app->openid->mode == 'cancel')
			{
				$this->app->output->alert('Steam authentication cancelled');
				$this->app->logger->log('Login failed', 'ERROR', array('message' => 'Steam authentication cancelled'));
				return false;
			}
			elseif($this->app->openid->mode)
			{
				if($this->app->openid->validate())
				{
					$steamID = basename($this->app->openid->identity);
					$this->handleAccountAfterLogin($steamID);
					$this->app->logger->log('Login successful', 'INFO', array('pathway' => 'openid'));
					return true;
				}
				else
				{
					$this->app->output->alert('Steam authentication failed');
					$this->app->logger->log('Login failed (SteamAPIException)', 'ERROR', array('pathway' => 'openid', 'openid_object' => $this->app->openid->data));
					return false;
				}
			}
		}
		catch (SteamAPIException $e) {
			$this->app->logger->log('Login failed (SteamAPIException)', 'ERROR', array('pathway' => 'steam', 'message' => $e->getMessage()));
			$this->app->output->alert('Steam Item API could not be reached. Please try again in a few minutes.');
		}
		catch (User_InventoryError $e) {
			$this->app->logger->log('Login failed (User_InventoryError)', 'ERROR', array('pathway' => 'unknown', 'message' => $e->getMessage()));
			$this->app->output->alert('There was an error in grabbing your Steam inventory.');
		}
		catch (User_TooNew $e) {
			$this->app->logger->log('Login failed (User_TooNew)', 'ERROR');
			$this->app->output->alert('This Steam account is too new. You must use an account that is at least 1 year old.');
		}
		catch (User_SteamBanned $e) {
			$this->app->logger->log('Login failed (User_SteamBanned)', 'ERROR');
			$this->app->output->alert('This Steam account has been banned ('.$e->getMessage().').');
		}

		return false;
	}

	public function loginUrl()
	{
		return $this->app->openid->authUrl();
	}

	// cURL GETing and takes care of timeouts
	private function curlFetch($url, $retries = 5) {
		$start = microtime(true);

		do {
			$retries--;
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);

			$result = curl_exec($ch);
			curl_close($ch);

			if(!empty($result))
			{
				$end = microtime(true);
				$this->app->profiler->log_steam_request([$url, $end-$start]);
				return $result;
			}
		}
		while($retries > 0);
		
		throw new SteamAPIException('Max number of retries');
	}

	public function getUser($steamID)
	{
		if(!is_array($steamID) and !is_string($steamID))
		{
			return false;
		}

		$multiple = false;

		if(is_array($steamID))
		{
			$multiple = true;

			if(count($steamID) > 100)
			{
				return false;
			}

			$steamID = implode(',', $steamID);
		}

		$url = sprintf('http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v001/?key=%s&steamids=%s', $this->apiKey, $steamID);

		$profileJson = $this->curlFetch($url);
		if(empty($profileJson)) {
			throw new SteamAPIException('Server returned empty response');
		}

		$profileJson = json_decode($profileJson);
		$profileJson = $profileJson->response->players->player;

		if($multiple)
		{
			$users = [];

			foreach ($profileJson as $user) {
				$users[$user->steamid] = $user;
			}

			return $users;
		}

		return $profileJson[0];
	}

	// Function that maps descriptions to an inventory
	private function formatInventory($inventory, $descriptions)
	{
		$inventory_items = $inventory;
		$inventory_descs = $descriptions ?: null;
		$inventory = array();

		foreach($inventory_items as $item_id => &$item) {
			try {
				// Search for item description in DB
				$desc_id = ($item->classid).'_'.($item->instanceid);

				// Remap item ids for items with multiple potential ids
				if(!empty(self::$ITEM_MAP[$desc_id])) {
					$desc_id = self::$ITEM_MAP[$desc_id];
				}

				$desc = Description::find($desc_id);
			}
			catch(ActiveRecord\RecordNotFound $e) {
				// If there isn't any item information for this item and no existing 
				// information exists, ignore this item
				if(empty($inventory_descs))
					$desc = null;
				else
					$desc = Description::add($desc_id, $inventory_descs->{$desc_id});
			}

			// If a tag isn't allowed, the item will not show up in the inventory
			if(empty($desc))
				continue;

			$item->desc = $desc;
			$inventory[$item->id] = $item;
		}

		return $inventory;
	}

	// Function that fetches current inventory from Steam API
	private function refreshInventory($steamID)
	{
		$url = sprintf('http://steamcommunity.com/profiles/%s/inventory/json/730/2', $steamID);
		$inventory = json_decode($this->curlFetch($url));

		$url = sprintf('http://api.steampowered.com/IEconItems_730/GetPlayerItems/v001/?key=%s&steamid=%s', $this->apiKey, $steamID);
		$inventory_schema = json_decode($this->curlFetch($url));
		if(empty($inventory) || empty($inventory_schema) || empty($inventory_schema->result)) {
			throw new SteamAPIException('Server returned empty response');
		}
		$inventory_schema = $inventory_schema->result;

		if(empty($inventory->success) || $inventory_schema->status != 1)
		{
			if(!empty($inventory->statusDetail))
				throw new SteamAPIException($inventory_schema->statusDetail);

			return false;
		}

		// Search for item's original ID and replace
		foreach($inventory->rgInventory as $item_id => &$item) {
			foreach($inventory_schema->items as $idx => $item_schema) {
				if($item_schema->id == $item_id) {
					$item->id = $item_schema->original_id;
					$item->asset_id = $item_id;
				}
			}
		}

		return $inventory;
	}


	// Function that grabs a user's inventory, and updates the cached version when necessary
	public function getInventory($steamID, $nocached = false)
	{
		$descriptions = null;
		try {
			$user = User::find($steamID);
			if(empty($user->updated_at) || empty($user->inventory_cached))
				throw new User_RefreshInventory;

			$updated = strtotime($user->updated_at->format('db'));
			$diff = (time() - $updated)/60;			// time difference in minutes
			if($nocached)
				throw new User_RefreshInventory;
			if($diff < self::INVENTORY_TIMEOUT)
				$inventory = json_decode($user->inventory_cached);
			else
				throw new User_RefreshInventory;

		}
		catch(Exception $e) {
			/* Catch if
			 * a1) User has not been updated before (migration)
			 * a2) User needs to be updated
			 * b) User does not exist
			 */

			// Grab inventory from Steam API
			try {
				$inventory = $this->refreshInventory($steamID);
			}
			catch(SteamAPIException $e1) {
				$this->app->logger->log('Fresh inventory grab failed (SteamAPIException)', 'ERROR', array('pathway' => 'steam', 'message' => $e->getMessage()));
				$inventory = array();
			}

			// a) If grabbing inventory doesn't work, use cached version
			// 		otherwise, update the cached version
			if($e instanceof User_RefreshInventory) {
				if(empty($inventory))
					$inventory = json_decode($user->inventory_cached);
				else {
					// Save space by not including descriptions in the DB
					// instead, pass them as a separate argument in case
					// there are new items that we haven't encountered.
					$descriptions = $inventory->rgDescriptions;
					unset($inventory->rgDescriptions);
					$inventory = $inventory->rgInventory;

					$user->inventory_cached = json_encode($inventory);
					$user->save();
				}
			}
			// b) If grabbing the inventory doesn't work for a new user
			//		we have to throw an Exception, since we have no cached
			else if($e instanceof ActiveRecord\RecordNotFound) {
				if(empty($inventory))
					throw new SteamAPIException('Server returned empty response');

				$descriptions = $inventory->rgDescriptions;
				unset($inventory->rgDescriptions);
				$inventory = $inventory->rgInventory;
			}
			else
				throw $e;
		}

		// If inventory is still empty at this point, then cry.
		if(empty($inventory))
			throw new User_InventoryError('Unable to fetch inventory from Steam or cache');
		return $this->formatInventory($inventory, $descriptions);
	}

	public function getBans($steamID)
	{
		$url = sprintf('http://api.steampowered.com/ISteamUser/GetPlayerBans/v001?key=%s&steamids=%s', $this->apiKey, $steamID);

		$bansJSON = $this->curlFetch($url);
		$bansJSON = json_decode($bansJSON);
		if(empty($bansJSON) || empty($bansJSON->players)) {
			throw new SteamAPIException('Server returned empty response');
		}
		return $bansJSON->players[0];
	}

	public function handleAccountAfterLogin($steamID)
	{
		$user = User::find_by_id($steamID);
		if(empty($user)) {
			$user = User::create([
				'id' => $steamID
			]);
		}
		
		Session::createForLogin($this->app, $steamID, $user);
		return true;
	}
}