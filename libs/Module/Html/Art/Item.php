<?php

namespace Otaku\Art\Module;

use Otaku\Framework\Query;
use Otaku\Art\RequestArt;
use Otaku\Art\RequestArtNextprev;

class HtmlArtItem extends HtmlArtAbstract
{
	protected $css = array('item', 'sidebar');
	protected $js = array('item');
	protected $next = false;

	protected function get_modules(Query $query) {
		return array(
			'title' => new HtmlArtTitle($query),
			'search' => new HtmlArtSearch($query),
			'error' => new HtmlArtError($query, true),
			'image' => new HtmlArtImage($query),
			'info' => new HtmlSidebarInfo($query),
			'tags' => new HtmlSidebarTag($query),
			'editmenu' => new HtmlSidebarEditmenu($query),
			'editfield' => new HtmlArtEditfield($query),
			'recent_comments' => new HtmlSidebarComment($query),
			'comment' => new HtmlComment($query)
		);
	}

	protected function get_params(Query $query) {
		$this->set_param('query', $query->to_url_string());
		$this->set_param('id', $query->url(0));
		$this->set_param('next', false);
		$this->set_param('prev', false);
	}

	protected function make_request() {
		$query = $this->get_query();
		$params = $query->other();
		$params['parsed'] = $query->parsed();
		$params['pool_mode'] = $query->get_pool_mode();
		$params['pool_value'] = $query->get_pool_value();

		$request = new RequestArt($query->url(0), $this);
		$pos = (int) $query->get('pos');
		if ($pos <= 0) {
			return $request;
		}

		return array($request,
			new RequestArtNextprev($pos, $this, $params, 'recieve_nextprev')
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
		$this->set_param('resized', $data['resized']);
		$this->modules['image']->recieve_data($data);
		$this->modules['editmenu']->recieve_additional($data);
		$this->modules['tags']->recieve_data($data['tag']);
	}

	protected function recieve_error($is_critical, $errors) {
        $this->set_param('resized', 0);
		$this->modules['editmenu']->disable();
		$this->modules['editfield']->disable();
		$this->modules['image']->disable();
		$this->modules['tags']->disable();
		$this->modules['info']->disable();
		$this->modules['error']->enable();
	}

	public function recieve_nextprev($data) {
		$query = $this->get_query();
		if (empty($data['current']) || $query->url(0) != $data['current']['id']) {
			return;
		}

		if (!empty($data['next'])) {
			$this->set_param('next', $data['next']['id']);
			$this->set_param('next_pos', $data['next']['pos']);
			$url = $query->to_url_string();
			$this->next = '/' . $data['next']['id'] . '?' .
				($url ? $url . '&' : '') . 'pos=' . $data['next']['pos'];
		}
		if (!empty($data['prev'])) {
			$this->set_param('prev', $data['prev']['id']);
			$this->set_param('prev_pos', $data['prev']['pos']);
		}
	}

	public function get_prefetch() {
		return $this->next ? $this->next : [];
	}
}
