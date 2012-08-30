<?php

class Module_Html_Sidebar_Info extends Module_Html_Abstract
{
	use Trait_Date, Trait_File, Trait_Module_Art;

	protected $css = ['sidebar'];

	protected function get_params(Query $query) {
		$this->set_param('query', $query->to_url_string());

		$this->set_param('pool_mode', $query->get_pool_mode());
		$this->set_param('pool_value', $query->get_pool_value());
	}

	protected function make_request() {
		return new Request_Art($this->get_query()->url(0), $this);
	}

	public function recieve_data($data) {
		if (!$data['count']) {
			return;
		}

		parent::recieve_data($data['data']);

		if (!empty($data['data']['source'])) {
			$source = new Text($data['data']['source']);
			$this->set_param('source', $source->links2html());
		}

		if (!empty($data['data']['weight'])) {
			$this->set_param('weight',
				$this->format_weight($data['data']['weight']));
		}

		if (
			isset($data['data']['state']) &&
			is_array($data['data']['state']) &&
			in_array('approved', $data['data']['state']) &&
			in_array('tagged', $data['data']['state'])
		) {
			$this->set_param('date_main',
				$this->format_date($data['data']['sortdate']));
		}

		if (!empty($data['data']['created'])) {
			$this->set_param('created',
				$this->format_date($data['data']['created']));
		}
	}
}
