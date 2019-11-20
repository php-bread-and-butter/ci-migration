<?php

// The name of THIS file
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

// Path to the system directory
define('BASEPATH', __DIR__.'/../../../../shells/');

// Path to the front controller (this file) directory
define('FCPATH', dirname(__FILE__).DIRECTORY_SEPARATOR);

// Name of the "system" directory
define('SYSDIR', basename(BASEPATH));

define('APPPATH', __DIR__.'/../../../../apps'.DIRECTORY_SEPARATOR);

define('VIEWPATH', __DIR__.'/../../../../apps/views'.DIRECTORY_SEPARATOR);

define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');

$_SERVER['REMOTE_ADDR'] = $_SERVER['SERVER_NAME'] = '127.0.0.1';

require BASEPATH.'core/CodeIgniter.php';
require BASEPATH.'libraries/Migration.php';

class Suppress extends CI_Migration {

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->library('migration');
		
		if ($this->migration->current(1) === FALSE)
		{
			echo ($this->migration->error_string() != '') ? $this->migration->error_string() : "Migration table dropped successfully.";
		} else {
			echo "Migration table dropped successfully.";
		}
	}

}