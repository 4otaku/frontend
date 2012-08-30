<?php

trait Trait_Module_Paginator
{
	abstract protected function get_page();
	abstract protected function get_per_page();
	abstract protected function get_url();

	public function build_pager($count) {
		$curr = $this->get_page();
		$last = ceil($count / $this->get_per_page());
		$start = max($curr - 8, 2);
		$end = min($curr + 9, $last - 1);

		$loop = array();
		if ($end >= $start) {
			for ($i = $start; $i <= $end; $i++) {
				$loop[] = array('current' => $i == $curr, 'page' => $i);
			}
		}

		$url_string = $this->get_url();
		if (!empty($url_string)) {
			$url_string .= '&';
		}

		$this->set_param('curr', $curr);
		$this->set_param('last', $last);
		$this->set_param('start', $start);
		$this->set_param('end', $end);

		$this->set_param('loop', $loop);
		$this->set_param('base', $url_string);
	}
}
