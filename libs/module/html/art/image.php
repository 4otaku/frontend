<?php

class Module_Html_Art_Image extends Module_Html_Art_Abstract
{
	use Trait_Tag;

	protected $js = ['external/wysibb', 'wysibb', 'image', 'translation',
		'list'];

	public function recieve_data($data) {
		$data['hidden'] = empty($data['tag']) ? false :
			$this->is_filtered($data['tag'], true);

		$data['src_resized'] = Config::get('api', 'image_url') . 'art/' .
			$data['md5'] . '_resize.jpg';
		$data['src_full'] = Config::get('api', 'image_url') . 'art/' .
			$data['md5'] . '.' . $data['ext'];
		$data['src'] = ($data['resized'] && Config::get('art', 'resized')) ?
			$data['src_resized'] : $data['src_full'];

		foreach ($data['translation'] as &$translation) {
			$translation['id'] = $translation['id_translation'];
			$translation['id_art'] = $data['id'];
			$translation['text'] = new Text($translation['text']);
			$translation['text']->html_escape();
		}
		parent::recieve_data($data);
	}
}
