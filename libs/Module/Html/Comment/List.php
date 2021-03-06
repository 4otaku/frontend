<?php

namespace Otaku\Art\Module;

use Otaku\Framework\Module\HtmlAbstract;
use Otaku\Framework\Query;
use Otaku\Framework\Config;
use Otaku\Framework\Module\Container;
use Otaku\Framework\RequestRead;

class HtmlCommentList extends HtmlAbstract
{
	protected $is_tree = true;
	protected $per_page = 7;
	protected $reverse = true;
	protected $page = 1;
	protected $id = 0;
	protected $id_comment = 0;

	public function __construct(Query $query, $disabled = false) {
		$config = Config::getInstance()->get('comment');
		$this->reverse = (bool) $config['reverse'];
		$this->per_page = (int) $config['per_page'];
		$this->is_tree = (bool) $config['tree'];

		parent::__construct($query, $disabled);
	}

	protected function get_modules(Query $query) {
		return array(
			'list' => new Container(__NAMESPACE__ .
				'\HtmlCommentItem'),
			'paginator' => new HtmlCommentPaginator($query)
		);
	}

	protected function get_params(Query $query)
	{
		if ($query->get('comment_page')) {
			$page = $query->get('comment_page');
			if ($page == 'all') {
				$this->per_page = 99999;
				$page = 1;
			}

			$this->page = max($this->page, (int) $page);
		}

		if ($query->url(0)) {
			$this->id = (int) $query->url(0);
		}

		if ($query->get('id_art')) {
			$this->id = (int) $query->get('id_art');
		}

		if ($query->get('id_comment')) {
			$this->id_comment = (int) $query->get('id_comment');
		}
	}

	protected function make_request() {
		if (!empty($this->id)) {
			return new RequestRead('comment', $this, array(
				'root_only' => (int) $this->is_tree,
				'add_tree' => (int) $this->is_tree,
				'sort_order' => $this->reverse ? 'desc' : 'asc',
				'page' => $this->page,
				'per_page' => $this->per_page,
				'filter' => array(
					'area' => 'art',
					'id_item' => $this->id
				)
			));
		}

		if (!empty($this->id_comment)) {
			$this->modules['paginator']->disable();
			$this->set_param('single_mode', 1);
			return new RequestRead('comment', $this, array(
				'filter' => array(
					'id' => $this->id_comment
				)
			));
		}

		return false;
	}

	public function recieve_data($data) {
		$this->modules['paginator']->set_per_page($this->per_page)
			->set_page($this->page)->set_id($this->id)->recieve_data($data);

		$step = $this->reverse ? -1 : 1;
		$current = $this->reverse ? $data['count'] : 1;
		$current = $current + ($this->page - 1) * $this->per_page * $step;

		$comments = array();
		foreach ($data['data'] as $item) {
			$item['label'] = array($current);
			$item['text'] = strip_tags($item['text']);
			$current = $current + $step;

			if (isset($item['tree'])) {
				foreach ($item['tree'] as $child) {
					$comments[$child['id']] = $child;
				}
			}
			unset($item['tree']);

			$comments[$item['id']] = $item;
		}

		$break = 0;
		while ($this->have_unlabeled($comments) && ++$break < 50) {
			$this->set_labels_to($comments);
		}

		foreach ($comments as &$comment) {
			$comment['max_depth'] = $comment['depth'] =
				count($comment['label']) - 1;
		}
		foreach ($comments as &$comment) {
			if (!isset($comments[$comment['rootparent']])) {
				continue;
			}
			$root = &$comments[$comment['rootparent']];
			$root['max_depth'] = max($root['max_depth'], $comment['depth']);
		}
		unset($root);
		foreach ($comments as &$comment) {
			if (!isset($comments[$comment['rootparent']])) {
				continue;
			}
			$root = $comments[$comment['rootparent']];
			$comment['max_depth'] = $root['max_depth'];
		}

		usort($comments, array($this, 'label_sort'));

		foreach ($comments as $key => &$comment) {
			if ($this->is_tree) {
				$comment['label'] = implode('.', $comment['label']) . ')';
				$comment['list_mode'] = 0;
			} else {
				$comment['label'] = '';
				$comment['list_mode'] = 1;
			}
			$comment['order'] = $key + 1;
		}
		unset($comment);

		$this->modules['list']->recieve_data($comments);
	}

	protected function have_unlabeled($comments) {
		foreach ($comments as $comment) {
			if (!isset($comment['label'])) {
				return true;
			}
		}

		return false;
	}

	protected function set_labels_to(&$comments) {
		foreach ($comments as &$comment) {
			if (isset($comment['label']) ||
				!isset($comments[$comment['parent']]['label'])) {
				continue;
			}

			$parent = &$comments[$comment['parent']];

			if (!isset($parent['links'])) {
				$parent['links'] = array();
			}
			$parent['links'][] = &$comment;
			usort($parent['links'], array($this, 'date_sort'));

			foreach ($parent['links'] as $key => &$child) {
				$label = $parent['label'];
				$label[] = $key + 1;
				$child['label'] = $label;
			}
		}
	}

	protected function date_sort($a, $b) {
		return strtotime($a['sortdate']) > strtotime($b['sortdate']) ?
			1 : -1;
	}

	protected function label_sort($a, $b) {
		$i = 0;
		$count_a = count($a['label']);
		$count_b = count($b['label']);
		while ($i < $count_a && $i < $count_b) {
			if ($a['label'][$i] != $b['label'][$i]) {
				$return = $a['label'][$i] > $b['label'][$i] ? 1 : -1;
				return $this->reverse ? $return * -1 : $return;
			}
			$i++;
		}

		return $count_a > $count_b ? 1 : -1;
	}
}
