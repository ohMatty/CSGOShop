<?php
class Session extends ActiveRecord\Model
{
	public static $belongs_to = [['user']];

	public static function createForLogin($app, $steamID, $user)
	{		
		$steamProfile = $app->steam->getUser($steamID);
		$steamInventory = $app->steam->getInventory($steamID);
		$steamBans = $app->steam->getBans($steamID);

		if($app->config->get('mode') == 'production')
		{
			if(!empty($steamProfile->timecreated) && (time() - $steamProfile->timecreated) < Steam::STEAM_AGE_THRESHOLD) {
				throw new User_TooNew;
			}	
		}

		if(!empty($steamBans->VACBanned)) {
			throw new User_SteamBanned('VAC Banned');
		}

		if(!empty($steamBans->CommunityBanned)) {
			throw new User_SteamBanned('Steam Community Banned');
		}

		if(!empty($steamBans->EconomyBan) && strcmp($steamBans->EconomyBan, 'none') != 0) {
			throw new User_SteamBanned('Steam Economy Banned');
		}

		$hash = Session::createHash($steamID);
		$session = Session::create([
			'hash' => $hash,
			'user_id' => $steamID,
			'user_agent' => $_SERVER['HTTP_USER_AGENT'],
			'ip' => $_SERVER['REMOTE_ADDR']
		]);

		$user->name = $steamProfile->personaname;
		$user->profile_private = ($steamProfile->communityvisibilitystate == 3 ? 0 : 1);
		$user->inventory_private = ($steamInventory ? 0 : 1);
		$user->ip_last = $_SERVER['REMOTE_ADDR'];
		if(empty($user->ip_register)) {
			$user->ip_register = $_SERVER['REMOTE_ADDR'];
			$user->name_register = $steamProfile->personaname;
		}

		$user->save();

		setcookie('csgoshop_session', $hash, time()+(60*60*24*30), '/');
		setcookie('csrf', $session->csrf_token, time()+(60*60*24*30), '/');
	}

	public static function verifySession($app)
	{
		if(isset($_COOKIE['csgoshop_session']))
		{
			$session = Session::find_by_hash($_COOKIE['csgoshop_session'], ['include' => ['user']]);

			if($session)
			{
				$session->user_agent = $_SERVER['HTTP_USER_AGENT'];
				$session->ip = $_SERVER['REMOTE_ADDR'];
				$session->save();

				return $session->user;
			}
			else
			{
				setcookie('csgoshop_session', -1, -1, '/');
				setcookie('csrf', -1, -1, '/');
			}
		}

		return new User;
	}

	public static function createHash($id)
	{
		return md5(rand() . time() . $id);
	}

	public function get_csrf_token()
	{
		return md5($this->user->name . $this->id);
	}
}