<?php

namespace Otaku\Art\Module;

use Otaku\Framework\Module\HtmlAbstract;
use Otaku\Framework\TraitDate;
use Otaku\Framework\Text;

class HtmlCommentItem extends HtmlAbstract
{
	use TraitDate;

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



