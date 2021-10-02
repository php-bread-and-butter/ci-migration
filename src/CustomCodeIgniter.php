<?php

/*
 * ------------------------------------------------------
 *  The name of THIS file
 * ------------------------------------------------------
 */
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

/*
 * ------------------------------------------------------
 *  Path to the system directory
 * ------------------------------------------------------
 */
define('BASEPATH', realpath($_SERVER['DOCUMENT_ROOT']) . '/shells/');

/*
 * ------------------------------------------------------
 *  Path to the front controller (this file) directory
 * ------------------------------------------------------
 */
define('FCPATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

/*
 * ------------------------------------------------------
 *  Name of the "system" directorys
 * ------------------------------------------------------
 */
define('SYSDIR', BASEPATH);

define('ROOTDIR', BASEPATH . '../');

define('APPPATH', ROOTDIR . 'apps' . DIRECTORY_SEPARATOR);

define('VIEWPATH', ROOTDIR . 'apps/views' . DIRECTORY_SEPARATOR);

define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');

$_SERVER['REMOTE_ADDR'] = $_SERVER['SERVER_NAME'] = '127.0.0.1';


/*
 * --------------------------------------------------------------------
 * LOAD THE Dotenv
 * --------------------------------------------------------------------
 */
if (getenv('HOME') === false) {
	putenv('HOME=/home/www-data');
}
require_once ROOTDIR . 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createMutable(ROOTDIR);
$dotenv->load();

/*
 * ------------------------------------------------------
 *  Load the framework constants
 * ------------------------------------------------------
 */
if (file_exists(APPPATH . 'config/' . ENVIRONMENT . '/constants.php')) {
	require_once(APPPATH . 'config/' . ENVIRONMENT . '/constants.php');
}

if (file_exists(APPPATH . 'config/constants.php')) {
	require_once(APPPATH . 'config/constants.php');
}
/*
 * ------------------------------------------------------
 *  Load the global functions
 * ------------------------------------------------------
 */
require_once(SYSDIR . 'core/Common.php');

/*
 * ------------------------------------------------------
 *  Start the timer... tick tock tick tock...
 * ------------------------------------------------------
 */
$BM = &load_class('Benchmark', 'core');
$BM->mark('total_execution_time_start');
$BM->mark('loading_time:_base_classes_start');

/*
 * ------------------------------------------------------
 *  Instantiate the hooks class
 * ------------------------------------------------------
 */
$EXT = &load_class('Hooks', 'core');

/*
 * ------------------------------------------------------
 *  Is there a "pre_system" hook?
 * ------------------------------------------------------
 */
$EXT->call_hook('pre_system');

/*
 * ------------------------------------------------------
 *  Instantiate the config class
 * ------------------------------------------------------
 *
 * Note: It is important that Config is loaded first as
 * most other classes depend on it either directly or by
 * depending on another class that uses it.
 *
 */
$CFG = &load_class('Config', 'core');

// Do we have any manually set config items in the index.php file?
if (isset($assign_to_config) && is_array($assign_to_config)) {
	foreach ($assign_to_config as $key => $value) {
		$CFG->set_item($key, $value);
	}
}

/*
 * ------------------------------------------------------
 * Important charset-related stuff
 * ------------------------------------------------------
 *
 * Configure mbstring and/or iconv if they are enabled
 * and set MB_ENABLED and ICONV_ENABLED constants, so
 * that we don't repeatedly do extension_loaded() or
 * function_exists() calls.
 *
 * Note: UTF-8 class depends on this. It used to be done
 * in it's constructor, but it's _not_ class-specific.
 *
 */
$charset = strtoupper(config_item('charset'));
ini_set('default_charset', $charset);

if (extension_loaded('mbstring')) {
	define('MB_ENABLED', TRUE);
	// mbstring.internal_encoding is deprecated starting with PHP 5.6
	// and it's usage triggers E_DEPRECATED messages.
	@ini_set('mbstring.internal_encoding', $charset);
	// This is required for mb_convert_encoding() to strip invalid characters.
	// That's utilized by CI_Utf8, but it's also done for consistency with iconv.
	mb_substitute_character('none');
} else {
	define('MB_ENABLED', FALSE);
}

// There's an ICONV_IMPL constant, but the PHP manual says that using
// iconv's predefined constants is "strongly discouraged".
if (extension_loaded('iconv')) {
	define('ICONV_ENABLED', TRUE);
	// iconv.internal_encoding is deprecated starting with PHP 5.6
	// and it's usage triggers E_DEPRECATED messages.
	@ini_set('iconv.internal_encoding', $charset);
} else {
	define('ICONV_ENABLED', FALSE);
}

if (is_php('5.6')) {
	ini_set('php.internal_encoding', $charset);
}

/*
 * ------------------------------------------------------
 *  Instantiate the UTF-8 class
 * ------------------------------------------------------
 */
$UNI = &load_class('Utf8', 'core');

/*
 * ------------------------------------------------------
 *  Instantiate the URI class
 * ------------------------------------------------------
 */
$URI = &load_class('URI', 'core');

/*
 * ------------------------------------------------------
 *  Instantiate the routing class and set the routing
 * ------------------------------------------------------
 */
$RTR = &load_class('Router', 'core', isset($routing) ? $routing : NULL);

/*
 * ------------------------------------------------------
 *  Instantiate the output class
 * ------------------------------------------------------
 */
$OUT = &load_class('Output', 'core');

/*
 * ------------------------------------------------------
 *	Is there a valid cache file? If so, we're done...
 * ------------------------------------------------------
 */
if ($EXT->call_hook('cache_override') === FALSE && $OUT->_display_cache($CFG, $URI) === TRUE) {
	exit;
}

/*
 * -----------------------------------------------------
 * Load the security class for xss and csrf support
 * -----------------------------------------------------
 */
$SEC = &load_class('Security', 'core');

/*
 * ------------------------------------------------------
 *  Load the Input class and sanitize globals
 * ------------------------------------------------------
 */
$IN	= &load_class('Input', 'core');

/*
 * ------------------------------------------------------
 *  Load the Language class
 * ------------------------------------------------------
 */
$LANG = &load_class('Lang', 'core');

/*
 * ------------------------------------------------------
 *  Load the app controller and local controller
 * ------------------------------------------------------
 *
 */
// Load the base controller class
require_once SYSDIR . 'core/Controller.php';

/**
 * Reference to the CI_Controller method.
 *
 * Returns current CI instance object
 *
 * @return CI_Controller
 */
function &get_instance()
{
	return CI_Controller::get_instance();
}

if (file_exists(APPPATH . 'core/' . $CFG->config['subclass_prefix'] . 'Controller.php')) {
	require_once APPPATH . 'core/' . $CFG->config['subclass_prefix'] . 'Controller.php';
}

// Set a mark point for benchmarking
$BM->mark('loading_time:_base_classes_end');
