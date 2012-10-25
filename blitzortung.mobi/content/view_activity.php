<?php

namespace bo_mobi;

class view_activity extends \cms\page_view
{
	public function __construct()
	{
		parent::__construct('activity');
	}

	public function render()
	{
		$vars = array();

		\Haanga::Load('activity.tpl', $vars);
	}
}

