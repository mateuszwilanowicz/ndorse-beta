<?php
	date_default_timezone_set('Europe/London');

	// STEP 1: Path constants

	$requestURI = trim($_SERVER['REQUEST_URI'], '/');

	if (isset($_SERVER['HTTP_HOST']))	{
		define("BASE_URL", (isset($_SERVER['HTTPS']) ? 'https://'  : 'http://') . $_SERVER['HTTP_HOST'] . ($_SERVER['SERVER_PORT'] != "80" ? ":" . $_SERVER['SERVER_PORT'] : '') . '/');
		$hostname = strtolower(gethostbyaddr($_SERVER['SERVER_ADDR']));
	} else {
		// try and find a command line argument instead
		if (isset($argv[1])) {
			$hostname = $argv[1];
			define("BASE_URL", ($_SERVER['HTTPS'] ? 'https://'  : 'http://') . $argv[2] . '/');
		} else {
			$hostname = 'default';
			define('BASE_URL', 'http://localhost/');
		}
	}

	define('DEFAULT_URL', BASE_URL . 'sites/default/');
	define('BASE_PATH', ROOT_PATH . 'html/');
	define('DEFAULT_PATH', BASE_PATH . 'sites/default/');
	define('DEFAULT_TEMPLATE', DEFAULT_PATH . 'templates/default.php');


	// STEP 2: Include common files

	$required  = array(
		// We load a configuration file here depending on the (true) hostname of the machine the code is executing on.
		// You may have to add a localhost entry to your hosts file pointing your hostname to 127.0.0.1
		BASE_PATH . 'conf/settings.' . (strpos($hostname, '.') !== false ? substr($hostname, 0, strpos($hostname, '.')) : $hostname) . '.php',
		BASE_PATH . 'lib/functions.php'
	);

	foreach ($required as $requiredFile) {

		require_once($requiredFile);
	}


	// STEP 3: Error Handling
	if (DEBUG) {
		ini_set("display_errors","2");
		error_reporting(E_ALL);
	} else {
		// don't output errors on production sites
		ini_set("display_errors","Off");
		error_reporting(E_ERROR);
	}

	set_error_handler('errorHandler');
	set_exception_handler('exceptionHandler');


	// STEP 4: Database setup

	// Must be called after baseFunctions.php is included.
	// do not require a database connection

	if(defined('DB_DATABASE')) {
		$dbConn = DatabaseConnection::getConnection();

		// Instantiating this class overrides PHP's built in session handling.
		// SessionHandler writes session data to the database. This must be called after the
		// database connection is setup.
		$sessionHandler = new SessionHandler();
	}

	// We load the site object here because it is required for the session handling.

	$site = Site::getInstance($hostname);
	$GLOBALS['site'] = $site;

	// session_start() must be called after baseFunctions.php is included, because session_start() unserializes
	// any objects in the session then, and if __autoload doesn't exist it can't access the class definition for
	// any objects in the session.
	session_start();

	define('MODERATION_TIME_HOURS', 24);

	define('EMAIL_FROM', 'alan.hitchin@routedigital.com');
	define('EMAIL_FROM_NAME', 'ndoorse');

	define('SITE_URL', BASE_URL .  (empty($site->url) ? 'sites/default/' : $site->url . '/'));
	define('SITE_PATH', BASE_PATH . (empty($site->url) ? 'sites/default/' : $site->url . '/'));

	// password options
	define('PASSWORD_PASSES', 8);
	define('PASSWORD_PORTABLE', false);

	// Stuff for (X)HTML page output
	$GLOBALS['page_settings'] = array(
		'stylesheets' => array(
			'http://fonts.googleapis.com/css?family=Cabin:400,500,600,700'
		),
		'scripts' => array(
			'//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js'
		),
		'analytics' => ''
	);

	// Holds the HTML elements for the page
	$GLOBALS['html'] = array();

?>