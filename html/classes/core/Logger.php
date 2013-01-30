<?php
	class Logger {

		public static function log($message, $type = 'message', $logID = 1) {

			if(isset($_SESSION['user']) && $_SESSION['user']->getID()) {
				$userID = $_SESSION['user']->getID();
			} else {
				$userID = 0;
			}
			
			if(!DEBUG && defined('DB_DATABASE') && class_exists('DatabaseConnection') /* if table exists */ ) {

				$dbConn = DatabaseConnection::getConnection();

				$stmt = $dbConn->prepareStatement('INSERT INTO core_log SET logID = :logID, `type` = :type, message = :message, userID = :userID, siteID = :siteID');
				$stmt->bindParameter('logID', $logID);
				$stmt->bindParameter('type', $type);
				$stmt->bindParameter('message', $message);
				$stmt->bindParameter('userID', $userID);
				$stmt->bindParameter('siteID', 0); // $GLOBALS['site']);

				try {
					if($stmt->execute()) {
						return;
					}
				} catch(Exception $e) {
					//SHould fall trhough to file looging
				} 
			}

			$logDir = ROOT_PATH . 'logs/';

			if (!file_exists($logDir)) mkdir($logDir);

			$logFile = $logDir . (defined(SITE_NAME) ? preg_replace('/[^a-z0-9]/', '', strtolower(SITE_NAME)) . '_' . $logID . '.log' : 'site' . $logID . '.log');
			$dateTime = date("d/m/Y H:i:s");

			$message = "$dateTime: $type - $message ($userID)\r\n";
			file_put_contents($logFile, $message, FILE_APPEND);



		}

		public static function getList() {

			$output = array();

			if(defined('DB_DATABASE')) {

				$dbConn = DatabaseConnection::getConnection();

				$stmt = $dbConn->prepareStatement('SELECT logID, COUNT(logID) AS entries FROM core_log WHERE siteID = :siteID GROUP BY logID ORDER BY logID');
				$stmt->bindParameter('siteID', $GLOBALS['site']->getID());
				$result = $stmt->execute();

				if($result instanceof Resultset && $result->hasRows()) {
					while($row = $result->nextRow()) {
						$output[] = array('logID'=>$row['logID'], 'entries'=>$row['entries']);
					}
				}

			} else {

				$logDir = ROOT_PATH . 'logs/';
				if (!file_exists($logDir)) {
					return array();
				}

				$logFile = $logDir . defined(SITE_NAME) ? preg_replace('/[^a-z0-9]/', '', strtolower(SITE_NAME)) . '_*.log' : 'site_*.log';

				$files = glob($logFile);

				if(empty($files)) {
					return array();
				}

				foreach($files as $file) {
					if(is_file($file)) {
						$output[] = array('logID'=>'', 'entries'=>'');
					}
				}

			}

			return $output;

		}

		public static function getLog($id) {

			if(empty($id)) {
				throw new Exception('Logger/getLog: No log ID specified');
			}

			$output = array();

			if(defined('DB_DATABASE')) {

				$dbConn = DatabaseConnection::getConnection();

				$stmt = $dbConn->prepareStatement('SELECT dateLogged, `type`, message, userID FROM core_log WHERE logID = :logID AND siteID = :siteID');
				$stmt->bindParameter('logID', $logID);
				$stmt->bindParameter('siteID', $GLOBALS['site']);
				$result = $stmt->execute();

				if($result instanceof Resultset && $result->hasRows()) {
					return $result->getResultsetAsArray();
				}

			} else {

				$logDir = ROOT_PATH . 'logs/';

				if (!file_exists($logDir)) mkdirs($logDir);

				$logFile = $logDir . defined(SITE_NAME) ? preg_replace('/[^a-z0-9]/', '', strtolower(SITE_NAME)) . '_' . $id . '.log' : 'site' . $iD . '.log';

				if(!file_exists($logFile)) {
					throw new Exception('Logger/getLog: Log file does not exist: ' . $logFile);
				}

				$file = fopen($logfile, 'r');
				while($line = fgets($file)) {
					if($line === false) {
						break;
					}

					$date = substr($line, 0, 10);
					$userpos = strrpos($line, '(');
					$user = substr($line, $userpos);
					$user = trim($user, ')');
					$typepos = strpos($line, '-');
					$type = substr($line, 11, $typepos - 11);
					$message = substr($line, $typepos + 1, $userpos - $typepos);

					$output[] = array('dateLogged'=>$date, 'type'=>$type, 'userID'=>$user, 'message'=>$message);
				}
				fclose($file);

			}

			return $output;

		}

	}
?>