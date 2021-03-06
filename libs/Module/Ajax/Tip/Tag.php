<?php

namespace Otaku\Art\Module;

class AjaxTipTag extends AjaxTipAbstract
{
	protected $request_type = 'left';
	protected $request_name_field = 'name';
	protected $request_data_fields = array();

	protected function parse_raw_term()
	{
		return parent::parse_raw_term()->cut_on("\t ");
	}
}
