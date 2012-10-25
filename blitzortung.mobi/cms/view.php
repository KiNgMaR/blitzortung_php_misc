<?php

namespace cms;

abstract class page_view
{
	protected $view_key = NULL;
	protected $needs_db = false;
	protected $dbh = NULL;
	protected $text_output = true;

	public function __construct($view_key)
	{
		$this->view_key = $view_key;
	}

	public function needsDb() { return $this->needs_db; }
	public function doesTextOutput() { return $this->text_output; }

	public function assignDbHandle($dbh)
	{
		$this->dbh = $dbh;
	}

	abstract public function render();
}

