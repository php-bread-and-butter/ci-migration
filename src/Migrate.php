<?php
require 'CustomCodeIgniter.php';
require BASEPATH . 'libraries/Migration.php';

class Migrate extends CI_Migration
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->library('migration');

		if ($this->migration->latest() === FALSE) {
			echo ($this->migration->error_string() != '') ? $this->migration->error_string() : "Migration table created successfully.";
		} else {
			echo "Migration table created successfully.";
		}
	}
}
