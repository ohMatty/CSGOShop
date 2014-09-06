<?php
class Profiler
{
	public $queries = [];
	public $steam_requests = [];
	public $app;

	public function __construct(&$app)
	{
		$this->app =& $app;
	}
	
	public function log_query($sql)
	{
		if(count($sql) == 3 && $sql[2] == 'query')
		{
			$this->queries[] = $sql;
		}
	}

	public function log_steam_request($request)
	{
		$this->steam_requests[] = $request;
	}

	public function fetch()
	{
		$query_time = array_reduce($this->queries, function($carry, $element)
		{
			$carry = $carry + $element[1];
			return $carry;
		});

		$request_time = array_reduce($this->steam_requests, function($carry, $element)
		{
			$carry = $carry + $element[1];
			return $carry;
		});

		$data = [
			'queries' => $this->queries,
			'query_time' => round($query_time, 3),
			'steam_requests' => $this->steam_requests,
			'steam_request_time' => round($request_time, 3)
		];

		return $data;
	}
}