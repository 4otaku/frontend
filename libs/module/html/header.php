<?php

namespace otaku\art;

class Module_Html_Header extends Module_Html_Abstract
{
	protected $css = array('base', 'header', 'overlay', 'setting');
	protected $js = array('overlay', 'setting', 'form', 'personal');

	protected function get_params(Query $query)
	{
		$menu = Database::get_table('head_menu_user',
			array('id', 'url', 'name'),
			'cookie = ? order by `order`',
			Config::get('cookie', 'hash')
		);

		$this->set_param('personal', $menu);
	}

	protected function make_request() {
		return new Request_Read('head_menu', $this);
	}
}
