<?php

class Request_Count extends Request
{
	public function __construct($api = false, $object = false, $data = array(), $method = 'recieve_data') {
		$data['mode'] = 'count_only';
		parent::__construct($api, $object, $data, $method);
	}
}
