<?php

namespace Otaku\Art\Module;

use Otaku\Framework\Module\Base;
use Otaku\Framework\TraitOutputTpl;
use Otaku\Framework\Query;
use Otaku\Framework\RequestItem;

class AjaxCommentEdit extends Base
{
	use TraitOutputTpl;

	protected $css = ['comment'];
	protected $js = ['external/wysibb', 'wysibb'];

	protected $id = 0;

	protected function get_params(Query $query) {
		$this->id = $query->get('id');
		$this->set_param('id', $this->id);
	}

	protected function make_request() {
		return new RequestItem('comment', $this,
			['filter' => ['id' => $this->id]]);
	}

	public function recieve_data($data) {
		$this->set_param('text', $data['data']['text']);
	}
}
