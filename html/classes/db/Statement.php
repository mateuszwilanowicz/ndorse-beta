<?php

	class Statement {

		private $stmt;
		private $boundParams = array();
		private $sql;

		/**
		* The constructor for Statement. It sets the statement to be used.
		*/
		public function __construct($stmt, $sql = "") {
			$this->stmt = $stmt;
			if (DEBUG)
				$this->sql = $sql;
		}

		public function __destruct() {
			unset($this->stmt);
		}

		/**
		 * Binds a parameter to the statement. The type field is optional
		 *
		 * @param $key The key for the parameter, this does not need the leading ":"
		 * @param $value The value for the parameter
		 * @param $type Optional, the PDO type of the parameter. This isn't needed in 99.99999% of cases.
		 */
		public function bindParameter($key, $value, $type = "") {
			if (empty($type) && is_numeric($value)) {
				$this->stmt->bindParam(':' . $key, $value, PDO::PARAM_INT);
			} else if (empty($type) && is_bool($value)) {
				$this->stmt->bindParam(':' . $key, $value, PDO::PARAM_BOOL);
			} else {
				if (empty($type))
					$type = PDO::PARAM_STR;
				// Prevent empty string from going into the DB
				if ($type == PDO::PARAM_STR && empty($value))
					$value = null;
				$this->stmt->bindParam(':' . $key, $value, $type);
			}
			if (DEBUG)
				$this->boundParams[$key] = $value;
		}


		/**
		 * Executes the statement and returns the result set for it.
		 *
		 * @return Resultset The resultset for the query
		 */
		public function execute($params = array()) {
			foreach ($params as $key => $value) {
				$this->bindParameter($key, $value);
				if (DEBUG)
					$this->boundParams[$key] = $value;
			}

			if (DEBUG)
				$startTime = microtime(true);

			try {
				$this->stmt->execute();
			} catch(Exception $e) {
				die($e);
			}

			if (DEBUG)
				$executionTime = microtime(true) - $startTime;

			if (DEBUG) {
				$stack = debug_backtrace();
				$executedQuery = array(
					'query' => trim(str_replace("\t", " ", $this->sql)),
					'params' => $this->boundParams,
					'time' => $executionTime
				);
				if (array_key_exists(1, $stack))
					$executedQuery['stack'] = array(
							'class' => isset($stack[1]['class']) ? $stack[1]['class'] : '',
							'function' => $stack[1]['function']
						);
				DatabaseConnection::getConnection()->addQueryExecuted($executedQuery);
			}

			$result = is_bool($this->stmt) ? $this->stmt : new Resultset($this->stmt);
			// This prevents "execute queries while other unbuffered queries are active" PDO error by
			// finishing up with the result
			$this->stmt->closeCursor();

			return $result;
		}

	}

?>