<?php

namespace bo_mobi;

class view_home extends \cms\page_view
{
	public function __construct()
	{
		parent::__construct('home');
	}

	public function render()
	{
		$vars = array();

		\Haanga::Load('home.tpl', $vars);
	}
}

