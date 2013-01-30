<?php

	/**
	* A class that defines the method that a DatabaseConnection must inherit.
	* It also uses the Singleton pattern so that only one DB connection can be instantiated.
	* There are a few check of the DEBUG constant throughout the methods, if DEBUG is true then
	* some extra debug info is recorded (queries executed, parameters and time taken to execute) and out
	* put at the bottom of the page (see the bottom of router.php).
	* @author Matt Rink <matt.rink@groovytrain.com>
	*/
	class DatabaseConnection {

		private static $instance;

		private $dbConn;

		private $hostname = "";
		private $username = "";
		private $password = "";
		private $database = "";

		private $lastError;

		private $queriesExecuted = array();

		/**
		* Private constructor to prevent external instantiation of this class.
		* (Not really necessary because the class is declared abstract)
		*/
		private function __construct() {
			define("DB_DATE", "Y-m-d");
			define("DB_DATETIME", "Y-m-d H:i:s");
		}

		/**
		 * In case the database connection get destroyed by the garbage collection before the
		 * session is written we write and close the session now.
		 */
		public function __destruct() {
			try {
				session_write_close();
				$this->dbConn = null;
			} catch (Exception $e) {
				// TODO Write to a file this error to work out why this is needed
				// file_put_contents("C:/temp/php.out", $e->getTraceAsString());
			}
		}


		/**
		* Private clone method prevents external instantiation of copies.
		*/
		private function __clone() {
			throw new DatabaseConnectionException("You cannot clone() the DatabaseConnection. Go away and play with something else...");
		}

		/**
		 * Magic function for returning variables. Will check to see if there
		 * is an override (of the form "get" + Variable_name) first.
		 */
		public function __get($property) {
			// check for an override
			$function = 'get' . ucfirst($property);
			if (method_exists($this, $function)) {
				return $this->$function();
			} else if (property_exists($this, $property)) {
				return $this->$property;
			} else {
				throw new Exception("Could not get property. No such property: " . $property);
			}
		}

		/**
		 * Magic function for setting variables. Will check to see if there
		 * is an override (of the form "set" + Variable_name) first.
		 */
		public function __set($property, $value) {
			// check for an override
			$function = 'set' . ucfirst($property);
			if (method_exists($this, $function)) {
				$this->$function($value);
			} else if (property_exists($this, $property)) {
				$this->$property = $value;
			} else {
				throw new Exception("Could not set property. No such property: " . $property);
			}
		}

		/**
		* @return DatabaseConnection Returns an instance of the subclass.
		*/
		public static function getConnection() {
			
			if(!defined('DB_DATABASE')) {
				throw new Exception('DatabaseConnection/GetConnection: No database');
			}

			if (!self::$instance instanceof self) {
				self::$instance = new DatabaseConnection();
				self::$instance->hostname = DB_HOSTNAME;
				self::$instance->username = DB_USERNAME;
				self::$instance->password = DB_PASSWORD;
				self::$instance->database = DB_DATABASE;
			}
			
			// Ensure that DatabaseConnection has all the required classes loaded before returning
			if (!class_exists('Statement')) {
				require_once(BASE_PATH . 'classes/db/Statement.php');
			}
			if (!class_exists('Resultset')) {
				require_once(BASE_PATH . 'classes/db/Resultset.php');
			}
			
			return self::$instance;
		}

		/**
		 * Opens the database connection.
		 * Returns true on success and false on failure
		 * Throws Exception on connect error.
		 */
		public function connect() {

			try {
				$dsn = DB_TYPE . ":host={$this->hostname};dbname={$this->database}";
				if (defined('DB_SOCKET') && DB_SOCKET != "")
					$dsn .= ";unix_socket=" . DB_SOCKET;
				$this->dbConn = new PDO($dsn, $this->username, $this->password);
				if ($this->dbConn->getAttribute(PDO::ATTR_DRIVER_NAME) != 'mysql')
					throw new DatabaseConnectionException("I'm sorry but we only support MySQL currently.");
				$this->dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (PDOException $e) {
				$this->lastError = $e->getMessage();
				throw new DatabaseConnectionException(get_class($this) . "::connect()\nError: " . $e->getMessage());
			}

		}


		/**
		 * Closes and destroys the database connection.
		 */
		public function close() {
			unset($this->dbConn);
		}


		/**
		 * Executes a SQL statement.
		 * Throws Exception on query execution error.
		 *
		 * @param String $query The query to be executed
		 * @return Resultset Returns true on success or false on failure. For SELECT queries it returns a Resultset.
		 */
		public function executeQuery($sql, $recordQuery = true) {

			if (!isset($this->dbConn))
				$this->connect();

			if (DEBUG)
				$startTime = microtime(true);

			$stmt = $this->dbConn->query($sql);

			if (DEBUG)
				$executionTime = microtime(true) - $startTime;

			if (DEBUG && $recordQuery) { // This is debug only info as it increases memory usage
				$stack = debug_backtrace();
				$executedQuery = array('query' => trim(str_replace("\t", " ", $sql)),
					'time' => $executionTime, 'stack' => $stack);
				if (is_bool($stmt))
					$executedQuery['success'] = $stmt;
				else
					$executedQuery['rows_returned'] = sizeof($stmt);
				$this->queriesExecuted[] = $executedQuery;
			}

			$result = is_bool($stmt) ? $stmt : new Resultset($stmt);
			unset($stmt);

			return $result;

		}

		/**
		 * Executes a stored procedure using any parameters passed to it in $params in the order they are in the array.
		 * Throws Exception on query execution error.
		 *
		 * @param String $storedProc The name of the stored procedure
		 * @param Array $params (Optional) Any parameters for the stored procedure in the order they are to be used in
		 * @return Resultset Returns true on success or false on failure. For SELECT queries it returns a Resultset.
		 */
		public function executeStoredProcedure($storedProc, $params = array()) {

			// Calling a stored procedure just call $this->executeQuery(). The query passed in looks like
			// "CALL nameOfTheStoredProcedure(param1, param2, param3);"
			$sql = "CALL " . $storedProc . "(";
			if (isset($params)) {
				for ($i = 0; $i < sizeof($params); $i++) {
					// We clean any parameter that isn't null or a number
					if ($params[$i] != null || is_numeric($params[$i]))
						$param = $this->cleanString($params[$i]);
					else
						$param = $params[$i];

					if ($param === true) { // We guess if it is a boolean
						$sql .= '1';
					} else if ($param === false) {
						$sql .= '0';
					} else if (!isset($param) || $param == null) { // Or a null
						$sql .= "NULL";
					} else if (is_string($param) && $param != 'NULL') { // Or a string that != "NULL"
						$sql .= $param;
					} else {
						$sql .= $param;
					}
					if ($i != sizeof($params) - 1)
						$sql .= ", ";
				}
			}
			$sql .= ");";

			if (!isset($this->dbConn))
				$this->connect();

			if (DEBUG)
				$startTime = microtime(true);

			$result = $this->executeQuery($sql, false);

			if (DEBUG)
				$executionTime = microtime(true) - $startTime;

			if (DEBUG) {
				$stack = debug_backtrace();
				$executedQuery = array('query' => trim(str_replace("\t", " ", $sql)),
					'time' => $executionTime, 'params' => $params
				);
				if (array_key_exists(1, $stack))
					$executedQuery['stack'] = array(
							'class' => $stack[1]['class'],
							'function' => $stack[1]['function']
						);
				$this->queriesExecuted[] = $executedQuery;
			}

			return $result;

		}


		/**
		 * Creates a prepared statement object using the supplied SQL query
		 *
		 * @param String $sql The SQL query ot be executed.
		 * @return Statement A Statement object containing the query.
		 */
		public function prepareStatement($sql) {

			if (!isset($this->dbConn))
				$this->connect();

			$stmt = $this->dbConn->prepare($sql, array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));

			return new Statement($stmt, $sql);

		}


		/**
		 * Begin a transaction
		 */
		public function beginTransaction() {
			$this->dbConn->beginTransaction();
		}


		/**
		 * Commit a transaction
		 */
		public function commitTransaction() {
			$this->dbConn->commit();
		}


		/**
		 * Rollback the previous transaction
		 */
		public function rollbackTransaction() {
			$this->dbConn->rollBack();
		}


		/**
		 * Cleans the string $string by adding slashes to escape quotes and other dangerous characters.
		 *
		 * @param String $string The string to be cleaned.
		 * @return string Returns the escaped string.
		 */
		public function cleanString($string) {
			if ($string === "")
				return "";

			if (!isset($this->dbConn))
				$this->connect();

			$string = trim($string);

			if (get_magic_quotes_gpc())
				$string = stripslashes($string);

			return $this->dbConn->quote($string);
		}


		/**
		* Returns a 2D array containing all of the queries executed and the time in which they executed.
		* @return mixed Returns the escaped string.
		*/
		public function getQueriesExecuted() {
			return $this->queriesExecuted;
		}


		public function addQueryExecuted($query) {
			$this->queriesExecuted[] = $query;
		}

		/**
		 * Returns ID of last inserted row
		 * @return unknown_type
		 */
		public function getLastID() {
			return $this->dbConn->lastInsertId();
		}

		/**
		 * Converts a database timestamp to a unix timestamp
		 * @return String The datetime timestamp to convert
		 */
		public static function dateTimeToUnixTimestamp($datetime) {
			if (empty($datetime))
				return null;
			return strtotime($datetime);
		}


		/**
		 * Returns a formatted date, if the optional $format parameter was passed in it will be
		 * formatted to match the $format parameter
		 *
		 * @param $datetime The datetime timestamp
		 * @param $format Optional, the format for the date
		 * @return String The formatted date
		 */
		public static function getFormattedDatabaseDate($datetime, $format = "jS F Y H:i:s") {
			if (empty($datetime))
				return "";
			$unixTimestamp = self::dateTimeToUnixTimestamp($datetime);
			return date($format, $unixTimestamp);
		}

	}

	class DatabaseConnectionException extends Exception {

		public function DatabaseConnectionException($message) {
			parent::__construct($message);
		}

	}

?>