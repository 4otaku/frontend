<?php

namespace Otaku\Art;

use Otaku\Framework\RequestItem;

class RequestArt extends RequestItem
{
	public function __construct($id, $object, $method = 'recieve_data') {
		$params = array(
			'add_tags' => true,
			'add_state' => true,
			'add_translations' => true,
			'add_similar' => true,
			'add_groups' => true,
			'add_manga' => true,
			'add_packs' => true,
			'add_artist' => true,
			'add_voted' => true,
			'id' => $id,
		);
		parent::__construct('art', $object, $params, $method);
	}
}
