<?php

class Query_Art extends Query
{
	protected $parsed = array();
	protected $other = array();
	protected $forced_per_page = true;
	protected $comparable_keys = array(
		'rating', 'width', 'height', 'weight', 'date'
	);
	protected $equal_keys = array(
		'tag', 'user', 'pack', 'group', 'artist', 'manga', 'md5', 'state'
	);
	protected $other_keys = array(
		'sort', 'order', 'mode', 'page', 'per_page'
	);
	protected $possible_modes = array(
		'art', 'comment', 'pack', 'group', 'manga', 'artist'
	);

	public function __construct($url, $get = array(), $clean = true) {
		parent::__construct($url, $get, $clean);

		$search = array();
		foreach ($this->get() as $key => $items) {
			$is_comparable = in_array($key, $this->comparable_keys);
			$is_equal = in_array($key, $this->equal_keys);
			$is_other = in_array($key, $this->other_keys);

			if (!$is_comparable && !$is_equal) {
				if ($is_other) {
					$item = is_array($items) ? reset($items) : $items;
					if ($key == 'mode' && !in_array($item, $this->possible_modes)) {
						continue;
					}
					$this->other[$key] = $item;
				}
				continue;
			}

			$data = array();
			list($data['is'], $data['not'], $data['more'], $data['less']) =
				$this->parse((array) $items, $is_comparable);
			$data_key = count($data['is']) * 1000 + count($data['more']) * 100 +
				count($data['less']) * 10 + count($data['not']) + 10 * $is_equal +
				array_search($key, $is_comparable ? $this->comparable_keys : $this->equal_keys);
			while (isset($search[$data_key])) {
				$data_key++;
			}
			$search[$data_key] = array('data' => $data, 'type' => $key);
		}

		if (empty($this->other['per_page'])) {
			$this->other['per_page'] = Config::get('pp', 'art');
			$this->forced_per_page = false;
		}

		krsort($search);
		ksort($this->other);
		foreach ($search as $item) {
			$this->parsed[$item['type']] = $item['data'];
		}
	}

	public function parsed() {
		return $this->parsed;
	}

	public function other() {
		return $this->other;
	}

	public function mode() {
		return isset($this->other['mode']) ? $this->other['mode'] :
			reset($this->possible_modes);
	}

	public function page() {
		return isset($this->other['page']) ? $this->other['page'] : 1;
	}

	public function per_page() {
		return $this->other['per_page'];
	}

	public function to_url_string() {
		$parts = array();

		$params = $this->parsed();

		foreach ($params as $key => $param) {
			foreach ($param as $mode => $items) {
				switch ($mode) {
					case 'is': $mode = ''; break;
					case 'more': $mode = '>'; break;
					case 'less': $mode = '<'; break;
					case 'not': $mode = '!'; break;
				}
				foreach ($items as $item) {
					$parts[] = $key . '[]=' . $mode . $item;
				}
			}
		}

		$params = $this->other();
		unset($params['page']);
		if (!$this->forced_per_page) {
			unset($params['per_page']);
		}
		foreach ($params as $key => $param) {
			$parts[] = $key . '=' . $param;
		}

		return implode('&', $parts);
	}

	public function all() {
		return array_merge($this->parsed, $this->other);
	}

	protected function parse($items, $is_comparable) {
		$is = $not = $more = $less = array();
		foreach ($items as $item) {
			if (strpos($item, '!') === 0) {
				$not[] = substr($item, 1);
				continue;
			}
			if (strpos($item, '>') === 0 && $is_comparable) {
				$more[] = substr($item, 1);
				continue;
			}
			if (strpos($item, '<') === 0 && $is_comparable) {
				$less[] = substr($item, 1);
				continue;
			}
			$is[] = $item;
		}
		return array($is, $not, $more, $less);
	}
}
