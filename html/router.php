<?php

	$startTime = microtime(true);

	// Initially turn error reporting on for startup - this is then overridden once we've loaded local settings
	ini_set("display_errors","2");
	error_reporting(E_ALL);

	// We define it here so we get a the proper html root
	define("ROOT_PATH", dirname(dirname(__FILE__)) . '/');

	require_once('lib/common.php');

	/* Use holding page */
	/*
	$controller = new DefaultController();
	$controller->holding();
	*/

	$requestURI = trim($_SERVER['REQUEST_URI'], '/');

	if (isset($_REQUEST['controller']) && !empty($_REQUEST['controller'])) {
		$controller = ucfirst($_REQUEST['controller']);
		$controllerName = $controller . 'Controller';
		if (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) {
			$page = $_REQUEST['page'];
		} else {
			$page = 'index';
		}

		// trim trailing slashes
		// trim language code if present
		$requestURI = trim($_SERVER['REQUEST_URI'], '/');
		if (strlen($requestURI) == 2 || (strlen($requestURI) > 2 && $requestURI{2} == '/')) {
			$requestURI = trim(substr($requestURI, 2), '/');
		}
		// remove the query string if there is one
		if (stripos($requestURI, '?') > 0)
			$requestURI = substr($requestURI, 0, stripos($requestURI, '?'));
		// and split it on the slashes
		$components = explode("/", $requestURI);

		// manually merge to preserve keys
		$params = array();

		for ($x = 0; $x < sizeof($components); $x++) {
			if (strlen($components[$x]) > 0)
				$params[$x] = urldecode($components[$x]);
		}
		// remove the controller from the params

		unset($params[0]);

		// add the GET values
		foreach ($_GET as $key => $value) {
			// This corrects page params the have an & in them. PHP thinks that after the & is a new key
			// So we concatenate them and check if they exist in the params from the REQUEST_URI.
			if (!in_array($key . $value, $params))
				$params[$key] = $value;
		}
		// add the POST values
		foreach ($_POST as $key => $value) {
			$params[$key] = $value;
		}

		$GLOBALS['args'] = $params;

		// Check if the controller exists, if it doesn't it is a default page
		if ((defined('SITE_PATH') && file_exists(SITE_PATH . 'controllers/' . $controllerName . '.php')) ||
			(defined('DEFAULT_PATH') && file_exists(DEFAULT_PATH . 'controllers/' . $controllerName . '.php')) ||
			(file_exists(BASE_PATH . 'controllers/' . $controllerName . '.php'))
		 ) {
			$controller = new $controllerName();
		} else {
			// Default controller
			$controller = new DefaultController();
		}

		$GLOBALS['controller'] = $controller;

	if (method_exists($controller, $page)) { // && !($controller instanceof DefaultController)) {

			// handle pages which don't have their own controller but for whom a method exists in DefaultController
			if($controller instanceof DefaultController && method_exists($controller, $params['controller'])) {
				$page = $params['controller'];
				unset($params['controller']);
			}

			if (isset($params[1]))
				unset($params[1]); // Unset the page
			$tmpParams = array();
			foreach ($params as $key => $value)
				is_int($key) ? $tmpParams[$key - 1] = $value : $tmpParams[$key] = $value;
			ksort($tmpParams);
			// call the method of the controller
			call_user_func(array($controller, $page), $tmpParams);
		// if there are more params but there isn't a matching method call index and pass everything as a param
		} else {
			// we append the page param because it isn't actually a page.
			// param[1] is the same value as $_REQUEST['page'] so if $_REQUEST['page'] doesn't match
			// what already in params we use param[1] instead.
			// This usually only happens when the page/param[1] contains an & and apache/php cuts it in half
			// param[0] may not be needed so we might be able to trim all of the below out completely.
			if (isset($_REQUEST['page'])) {
				if (in_array($_REQUEST['page'], $params))
					$params[0] = $_REQUEST['page'];
				else
					$params[0] = $params[1];
			}

			$params['controller'] = $_REQUEST['controller'];
			if (isset($_REQUEST['page']))
				$params['page'] = $_REQUEST['page'];

			ksort($params);
			if (!($controller instanceof DefaultController)) {
				call_user_func(array($controller, 'index'), $params);
			} else if (!isset($_REQUEST['page']) && localise('layouts/'  . $_REQUEST['controller'] . '/index.php')) {
				$controller = new DefaultController();
				$controller->index($params);
			} else if (isset($_REQUEST['page']) && localise('layouts/' . $_REQUEST['controller'] . '/' . $page . '.php')) {
				$controller = new DefaultController();
				$controller->index($params);
			} else {
				header("HTTP/1.0 404 Not Found");
				$controller = new DefaultController();
				$controller->error404($params);
			}
		}
	} else {
		throw new Exception('No controller defined');
	};

	// Toss debug data in here
	if (DEBUG && !($controller instanceof ServiceController)) {
		//echo '<div style="clear: both;"></div>';
		//echo "<a href=\"#\" style=\"font-size: 0.6em;\" onclick=\"document.getElementById('debugOutput').style.display == 'none' ? document.getElementById('debugOutput').style.display = 'block' : document.getElementById('debugOutput').style.display = 'none';\">Display Debug</a><div id=\"debugOutput\" style=\"display: none; width: 100%; text-align: left; background-color: #fff;\"><pre>\n";
		// var_dump(memory_get_peak_usage());
		// var_dump(DatabaseConnection::getConnection()->getQueriesExecuted());
		//pr($GLOBALS, false);
		// var_dump($_SESSION['debug']['strings']);
		// if (isset($GLOBALS['debug']))
		// 	var_dump($GLOBALS['debug']);
		// var_dump(get_included_files());
		//echo "</pre></div>\n";
		unset($_SESSION['debug']);
	}

?>

<!-- Request generated by Antelope in <?= round(microtime(true) - $startTime, 4); ?> seconds -->