<?php

class Module_Html_Art_List extends Module_Html_Art_Abstract
{
	protected $css = array('list');
	protected $js = array();
	protected $query_params = array();

	protected function get_modules(Query $query) {
		return array(
			'title' => new Module_Html_Art_Title($query),
			'search' => new Module_Html_Art_Search($query),
			'paginator' => new Module_Html_Art_Paginator($query),
			'list' => new Module_Html_Container('thumbnail_' . $query->mode())
		);
	}

	protected function make_request() {
		return $this->get_common_request();
	}

	public function recieve_data($data) {
		$query = $this->query->to_url_string();
		foreach ($data['data'] as &$item) {
			$item['query'] = $query;
		}
		unset($item);

		$this->modules['list']->recieve_data($data['data']);
	}
}
