<?php

class Module_Ajax_Menu_Save extends Module_Ajax_Json
{
	protected function get_params(Query $query)
	{
		$id = $query->get('id');
		$url = $query->get('url');
		$name = $query->get('name');
		$order = $query->get('order');

		if (empty($url) || empty($name) || empty($id) || empty($order)) {
			$this->error_code = 420;
			return;
		}

		Database::update('head_menu_user',
			array('url' => $url, 'name' => $name), $id);

		$session = Session::get_instance();
		$cookie = $session->get_hash();

		$items = Database::order('order', 'asc')->
			get_table('head_menu_user', array('id', 'order'),
			'cookie = ?', $cookie);

		foreach ($items as &$item) {
			if ($item['id'] == $id) {
				$item['new_order'] = $order;
			}
		}
		unset($item);

		$new_order = 1;
		foreach ($items as &$item) {
			if (!empty($item['new_order'])) {
				continue;
			}

			if ($new_order == $order) {
				$new_order++;
			}

			$item['new_order'] = $new_order;
			$new_order++;
		}
		unset($item);

		foreach ($items as $item) {
			if ($item['new_order'] != $item['order']) {
				Database::update('head_menu_user', array(
					'order' => $item['new_order']
				), $item['id']);
			}
		}

		$this->success = true;
		$this->params = array(
			'name' => $name,
			'url' => $url,
			'order' => $items
		);
	}
}