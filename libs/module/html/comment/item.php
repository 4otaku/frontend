<?php

namespace otaku\art;

class Module_Html_Comment_Item extends Module_Html_Abstract
{
	use Trait_Date;

	protected $css = array('comment');
	protected $js = array('comment');

	public function recieve_data($data) {
		$data['date'] = $this->format_time($data['sortdate']);
		if (!empty($data['editdate'])) {
			$data['editdate'] = $this->format_time($data['editdate']);
		}

		$data['uid'] = md5(rand());


		$data['text'] = new Text($data['text']);
		$data['text']->trim()->links2bb();

		parent::recieve_data($data);
	}
}



