<?php

class Module_Rss extends Module_Abstract
{
	use Trait_Output_Tpl, Trait_Module_Art_List;

	protected $header = ['Content-type' => 'application/rss+xml'];

	protected function get_modules(Query $query) {
		return [
			'title' => new Module_Html_Art_Title($query),
			'list' => new Module_Container('rss_thumbnail_' . $query->mode())
		];
	}

	protected function make_request() {
		return $this->get_common_request();
	}

	protected function get_params(Query $query) {
		$this->set_param('query', $query->to_url_string());
		$this->set_param('domain', $_SERVER['SERVER_NAME']);
	}

	public function recieve_data($data) {
		$this->modules['list']->recieve_data($data['data']);
	}
}