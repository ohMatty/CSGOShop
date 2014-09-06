<?php
// Wrapper for Hashids library
class Hash {
	private $hashids;

	public function __construct($config)
	{
		require_once './libs/Hashids/Hashids.php';
		$this->hashids = new Hashids\Hashids($config->get('salt'), 5);
	}

	public function decrypt($id_enc)
	{
		$res = $this->hashids->decrypt($id_enc);
		if(empty($res))
			throw new Hashids_Invalid;

		return $res[0];
	}

	public function encrypt($id_dec)
	{
		$res = $this->hashids->encrypt($id_dec);
		if(empty($res))
			throw new Hashids_Invalid;
		
		return $res;
	}
}