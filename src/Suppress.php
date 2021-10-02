<?php
require 'CustomCodeIgniter.php';
require BASEPATH . 'libraries/Migration.php';

class Suppress extends CI_Migration
{

	/** @var integer No of arguments passed to script **/
	protected $argc;
	/** @var array Array of arguments passed to script **/
	protected $argv;
	/** @var string The string used in migration file name **/
	private $version;

	public function __construct()
	{
		parent::__construct();
		global $argc, $argv;
		$this->argv = array_slice($argv, 1);
		$this->argc = $argc;
		$this->version = array_shift($this->argv);

		if ($this->version == NULL) {
			die('Version is required parameter, e.g., php suppress ${VERSION}. Replace ${VERSION} with the number, like 20211002092605');
		}

		$this->load->database();
		$this->load->library('migration');

		if ($this->migration->version($argv[1]) === FALSE) {
			echo ($this->migration->error_string() != '') ? $this->migration->error_string() : "Migration table dropped successfully.";
		} else {
			echo "Migration table dropped successfully.";
		}
	}
}
