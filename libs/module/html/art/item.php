<?php

class Module_Html_Art_Item extends Module_Html_Art_Abstract
{
	protected $css = array('item', 'sidebar');
	protected $js = array('item');

	protected function get_modules(Query $query) {
		return array(
			'title' => new Module_Html_Art_Title($query),
			'search' => new Module_Html_Art_Search($query),
			'error' => new Module_Html_Art_Error($query, true),
			'image' => new Module_Html_Art_Image($query),
			'info' => new Module_Html_Sidebar_Info($query),
			'tags' => new Module_Html_Sidebar_Tag($query),
			'editmenu' => new Module_Html_Sidebar_Editmenu($query),
			'editfield' => new Module_Html_Art_Editfield($query),
			'recent_comments' => new Module_Html_Sidebar_Comment($query),
			'comment' => new Module_Html_Comment($query)
		);
	}

	protected function get_params(Query $query) {
		$this->set_param('query', $query->to_url_string());
		$this->set_param('id', $query->url(0));
	}

	protected function make_request() {
		$query = $this->get_query();
		$params = $query->other();
		$params['parsed'] = $query->parsed();
		$params['pool_mode'] = $query->get_pool_mode();
		$params['pool_value'] = $query->get_pool_value();

		return new Request_Multi(
			new Request_Art($query->url(0), $this),
			new Request_Art_Nextprev($query->url(0),
				$this, $params, 'recieve_nextprev')
		);
	}

	public function recieve_data($data) {
		if ($data['count'] > 0) {
			$this->recieve_succesful($data['data']);
		} else {
			$this->recieve_error(!$data['success'], $data['errors']);
		}
	}

	protected function recieve_succesful($data) {
		$this->modules['image']->recieve_data($data);
		$this->modules['editmenu']->recieve_additional($data);
		$this->modules['tags']->recieve_data($data['tag']);
	}

	protected function recieve_error($is_critical, $errors) {
		$this->modules['editmenu']->disable();
		$this->modules['editfield']->disable();
		$this->modules['image']->disable();
		$this->modules['tags']->disable();
		$this->modules['info']->disable();
		$this->modules['tags']->disable();
		$this->modules['error']->enable();
	}

	public function recieve_nextprev($data) {
		$this->set_param('next', empty($data['next']) ? false : $data['next']);
		$this->set_param('prev', empty($data['prev']) ? false : $data['prev']);
	}
}
