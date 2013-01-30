<?php

	/**
	* A class the defines the methods that a Resultset must implement.
	* @author Matt Rink <matt.rink@groovytrain.com>
	*/
	class Resultset {
		
		private $result;
		private $currentRow = 0;
		
		
		/**
		* The constructor for Resultset. It sets the result to be used.
		*/
		public function __construct($stmt) {
			
			try {
				$this->result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			} catch (PDOException $e) {
				// file_put_contents("C:/temp/php.out", $e->getTraceAsString());
				// file_put_contents("C:/temp/php.out", $e->getMessage());
			}
			
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
		* Gets the number of rows in a Resultset returned by a database query.
		* @return int Returns the number of rows in the Resultset
		*/
		public function getRowCount() {
			return sizeof($this->result);
		}
		
		
		/**
		* Says whether or not the Resultset contains any more unread rows.
		* @return boolean Returns true if more rows are available, false if there isn't
		*/
		public function hasRows() {		
			return $this->currentRow < $this->rowCount;			
		}
		
		
		/**
		* Returns the next row in the Resultset
		* @return mixed Returns an associative array of the fields held in a row or false if now more rows are available
		*/
		public function nextRow() {
			if (!$this->hasRows())
				return false;
			$row = $this->result[$this->currentRow];
			$this->currentRow++;
			return $row;
		}
		
		
		/**
		 * Returns this result set as an associative array
		 * @return array This result set as an associative array
		 */
		public function getResultsetAsArray() {
	
			return $this->result;
			
			/*
			$rows = array();
			$offset = $this->result->current_field;
			$this->result->data_seek(0);
			while ($row = $this->result->fetch_assoc())
				$rows[] = $row;
			$this->result->data_seek($offset);
			
			return $rows;
			*/
			
		}
		
		
		/**
		* Cleans up properly and free the MySQLi result.
		*/
		public function __destruct() {
		
			unset($this->result);
			
		}
	
	}

?>