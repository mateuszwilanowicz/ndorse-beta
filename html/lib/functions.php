<?php
	function __autoload($className) {
		/*
		 * Classes, controls and controllers are never localised, but can be overridden on a site basis.
		 */

		if($className != 'Controller' && substr($className, -10) == 'Controller') {

			// is it a controller?

			if(defined('SITE_PATH') && file_exists(SITE_PATH . 'controllers/' . $className . '.php')) {
				$found = SITE_PATH . 'controllers/' . $className . '.php';
			} else if(file_exists(BASE_PATH . 'controllers/' . $className . '.php')) {
				$found = BASE_PATH . 'controllers/' . $className . '.php';
			}

		} else if(substr($className, -7) == 'Control') {

			// or is it a control?

			if(defined('SITE_PATH') && file_exists(SITE_PATH . 'controls/' . $className . '.php')) {
				$found = SITE_PATH . 'controls/' . $className . '.php';
			} else if(file_exists(BASE_PATH . 'controls/' . $className . '.php')) {
				$found = BASE_PATH . 'controls/' . $className . '.php';
			}

		} else if($className == 'DatabaseConnection' || $className ==  'Resultset' || $className == 'Statement') {

			// if it's a database class, just load it
			$found = BASE_PATH . 'classes/db/' . $className . '.php';

		} else {

			// else it's a bog standard class, see if it's part of a package
			// packaged files are of the format Package_Classname, and are found in /classes/Package/Classname.php

			$class_parts = explode('_', $className);
			if(count($class_parts) > 1) {
				$package = $class_parts[0];
				$className = $class_parts[1];

				if(defined('SITE_PATH') && file_exists(SITE_PATH . 'classes/' . $package . '/' . $className . '.php')) {
					$found = SITE_PATH . 'classes/' . $package . '/' . $className . '.php';
				} else if(file_exists(BASE_PATH . 'classes/' . $package . '/' . $className . '.php')) {
					$found = BASE_PATH . 'classes/' . $package . '/' . $className . '.php';
				}

			} else {

				// or it's just a plain class

				if(defined('SITE_PATH') && file_exists(SITE_PATH . 'classes/core/' . $className . '.php')) {
					$found = SITE_PATH . 'classes/core/' . $className . '.php';
				} else if(file_exists(BASE_PATH . 'classes/core/' . $className . '.php')) {
					$found = BASE_PATH . 'classes/core/' . $className . '.php';
				} else if(file_exists(BASE_PATH . 'classes/payment/' . $className . '.php')) {
					$found = BASE_PATH . 'classes/payment/' . $className . '.php';
				}

			}


		}

		if(isset($found)) {
			if(class_exists('Logger')) {
				//Logger::log("Autoload for \"$className\" found: $found", 'status');
			}
			require_once($found);
		} else {
			if(class_exists('Logger')) {
				//Logger::log("ERR: Autoload for \"$className\" not found.", 'error');
			}
			throw new Exception("Could not find class, controller or control: " . $className);
		}
	}

	function localise($file, $fatal = false) {

		// simplified version to remove language-specific functionality

		if(defined('SITE_PATH') && file_exists_with_log(SITE_PATH . $file)) {
			$found = SITE_PATH . $file;
		} else if(file_exists_with_log(DEFAULT_PATH . $file)) {
			$found = DEFAULT_PATH . $file;
		}

		if (isset($found)) {
			Logger::log("Localise: found $found", 'status');
			return $found;
		} else {
			if($fatal) {
				Logger::log("Localise: could not find $file", 'error');
				throw new Exception('Localise: Could not find ' . $file);
			} else {
				Logger::log("Localise: could not find $file", 'warning');
				return '';
			}
		}
	}

	function handleError($code, $message, $file, $line, $trace = '') {
		if(!DEBUG) {
			try {
				$email = new EmailMessage();

				$recipients = $GLOBALS['site']->getErrorRecipients();
				if(count($recipients) > 0) {
					foreach($recipients as $receiver) {
						$email->addRecipientEmailAddress($receiver);
					}
				} else {
					$email->addRecipient(DEFAULT_ERROR_RECIPIENT);
				}

				if(defined(ADMIN_EMAIL_FROM)) {
					$email->setSenderEmailAddress(ADMIN_EMAIL_FROM);
				} else {
					$email->setSenderEmailAddress('notifications@routedigital.com');
				}

				$email->setSubject(SITE_NAME . ': Error report');
				$email->setTextContent(<<<MSG
Error Report for {SITE_NAME}
$file on line $line

$message ($code)

$trace

This is an automated email, do not reply.
MSG
						);
				$emailTransport = new EmailTransport();
				$emailTransport->sendMessage($email);
			} catch(Exception $e) {
				die('Unrecoverable error: <br />' . $e);
			}
		}
		Logger::log("{$code} - {$message} - {$file} - {$line}", 'error');

		if(defined('SITE_PATH') && file_exists(SITE_PATH . 'layouts/error.php')) {
			require_once(SITE_PATH . 'layouts/error.php');
		} else if(file_exists(DEFAULT_PATH . 'layouts/error.php')) {
			require_once(DEFAULT_PATH . 'layouts/error.php');
		} else {
			die('<h1>Error</h1><p>An error has occurred, please try again later.</p>');
		}

		die();

	}

	function errorHandler($errorNo, $errorStr, $errorFile, $errorLine) {

		handleError($errorNo, $errorStr, $errorFile, $errorLine);

	}

	function exceptionHandler($exception) {

		handleError($exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine(), $exception->getTraceAsString());

	}

	function logOutput($message) {

		$logDir = ROOT_PATH . 'logs/';
		if (!file_exists($logDir)) mkdirs($logDir);
		$logFile = $logDir . defined(SITE_NAME) ? preg_replace('/[^a-z0-9]/', '', strtolower(SITE_NAME)) . '.log' : 'site.log';
		$dateTime = date("d/m/Y H:i:s");
		$message = "$dateTime - $message\r\n";
		file_put_contents($logFile, $message, FILE_APPEND);

	}

	function file_exists_with_log($file) {
		Logger::log("Localise: trying $file", 'status');
		return file_exists($file);
	}

	function redirect($url) {
		header('Location: ' . $url);
		exit();
	}

	function paragraphise($inText) {
		$lines = explode("\n", $inText);
		$outText = "";
		foreach ($lines as $line) {
			$line = trim($line);
			if (strlen($line) > 0)
				$outText .= "<p>" . $line . "</p>\n";
		}
		return $outText;
	}

	function reverseDate($date) {

		$datetime = explode(' ', $date);
		if(count($datetime) > 1) {
			$time = $datetime[1];
		}
		$dateparts = explode('/', $date);
		if(count($dateparts) != 3) {
			return false;
		}
		return implode('-', array_reverse($dateparts));


	}

	/**
	 * Takes the current page query string and removes all parameters found in $exclude
	 * @param $exclude An array of query string variables to not include
	 * @return string the new query string
	 */
	function rebuildQueryString($exclude = array()) {
		if (isset($_SERVER['QUERY_STRING'])) {
			$queryStringParts = explode("&", $_SERVER['QUERY_STRING']);
			$x = 0;
			foreach ($queryStringParts as $queryStringPart) {
				if (in_array(substr($queryStringPart, 0, stripos($queryStringPart, '=')), $exclude))
					unset($queryStringParts[$x]);
				$x++;
			}
			if (sizeof($queryStringParts) > 0)
				return implode('&', $queryStringParts);
		}
		return '';
	}

	function cleanUrl($url) {
		return urlencode(str_replace(" ", "_", $url));
	}

	function isImage($ext) {
		$exts = array('jpg', 'gif', 'png');
		return in_array($ext, $exts);
	}

	function debugMessage($message) {
		if (DEBUG)
			$GLOBALS['debug'][] = $message;
	}

	function pr($var, $kamikaze = true) {
	    
		echo '<pre>';
		print_r($var);
		echo '</pre>';

		if($kamikaze) {
			die();
		}
	}

	function getString($page, $string, $values = array()) {
		if (DEBUG)
			$_SESSION['debug']['strings'][] = $page . ":" . $string;

		global $_STRINGS;

		if (!isset($_STRINGS[$page][$string]))
			require_once(DEFAULT_PATH . 'content/strings_en.php');
		if(!isset($_STRINGS[$page][$string])) {
			return '(nothing)';
		}
		$pageString = $_STRINGS[$page][$string];
		foreach ($values as $key => $value) {
			$pageString = str_replace("%" . ($key + 1) . "%", $value, $pageString);
		}
		// Remove any unreplaced holders
		$pageString = preg_replace("/(%[0-9]+%)/i", "", $pageString);
		return $pageString;
	}

?>