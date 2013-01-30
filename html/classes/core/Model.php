<?php
	abstract class Model {

		protected $attributes;

		public function __construct($params = null) {
			if(!is_null($params)) {
				if(is_array($params)) {
					$this->loadFromArray($params);
				} else {
					if(method_exists($this, 'loadByID')) {
						$this->loadByID($params);
					}
					$this->loadModelByID($params);
				}
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
		 *
		 * IMPORTANT :: In order for the magic methods to work the child class properties MUST be protected NOT private
		 *
		 * Magic function for setting variables. Will check to see if there
		 * is an override (of the form "set" + Variable_name) first.
		 *
		 * TODO Performance testing of magic vs. build in get set methods
		 *
		 */
		public function __set($property, $value) {
			// check for an override

			$function = 'set' . ucfirst($property);
			if (method_exists($this, $function)) {
				$this->$function($value);
			} else if (property_exists($this, $property)) {
				//echo "SET " . $property . " TO " . $value . "<br />";
				$this->$property = $value;
			} else {
				// Silent drop is more useful here
				//throw new Exception("Could not set property. No such property: " . $property);
			}
		}

		public function getID() {
			$className = get_class($this);
			$class_parts = explode('_', $className);
			if(count($class_parts) > 1) {
				$package = $class_parts[0];
				$className = strtolower($class_parts[1]);
			}
			$id = $className . 'ID';
			if(empty($this->$id)) {
				return false;
			}
			return $this->$id;

		}

		public function loadFromArray($data) {
			if(!is_array($data)) {
				throw new Exception('Core/Model/loadFromArray: data not an array [' . get_called_class() . ']');
			}
			foreach($this as $key=>$value) {
				if(array_key_exists($key, $data)) {
					$this->$key = $data[$key];
				}
			}
		}

		public function loadModelByID($id, $idfield = null) {
			$dbConn = DatabaseConnection::getConnection();
			$class = get_class($this);

			if(!is_null($idfield)) {
				$suffix = $idfield;
			} else {
				$tmp = explode('_',$class);
				$suffix = count($tmp) > 1 ? $tmp[1] : $class;
				$suffix = strtolower($suffix) . 'ID';
			}


			$sql = 'SELECT * FROM ' . strtolower($class) . ' WHERE ' . $suffix . ' = :id';
			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('id', $id);

			$result = $stmt->execute();
			if($result instanceof Resultset && $result->hasRows()) {
				$row = $result->nextRow();
				return $this->loadFromArray($row);
			}
			return false;

		}

		public function saveModel($excludes = array(), $idfield = null) {

			$dbConn = DatabaseConnection::getConnection();
			$class = get_class($this);

			$update = $this->getID() > 0;

			$sql = $update ? 'UPDATE ' : 'INSERT INTO ';
			$sql .=  strtolower($class) . ' SET ';
			$vals = array();
			$sets = array();

			foreach($this as $key=>$val) {
				if(!in_array($key, $excludes) && $key != 'attributes') {
					$sets[$key] = '`' . $key . '` = :' . $key;
				}
			}
			$sql .= implode(', ', $sets);

			if(!is_null($idfield)) {
				$suffix = $idfield;
			} else {
				$tmp = explode('_', $class);
				$suffix = count($tmp) > 1 ? $tmp[1] : $class;
				$idfield = strtolower($suffix) . 'ID';
			}
			if($update) {
				$sql .= ' WHERE `' . $idfield . '` = :id';
			}

			$stmt = $dbConn->prepareStatement($sql);

			foreach($this as $key=>$val) {
				if(!in_array($key, $excludes) && $key != 'attributes') {
					// auto datestamp updates
					if($update && $key == 'updated') {
						$stmt->bindParameter('updated', date('Y-m-d H:i:s'));
					} else {
						$stmt->bindParameter($key, $this->$key);
					}
				}
			}
			if($update) {
				$stmt->bindParameter('id', $this->getID());
			}

			$result = $stmt->execute();

			if($result && !$update) {
				$this->$idfield = $dbConn->getLastID();
			}
			return $result;

		}

		public function toArray() {
			$output = array();
			foreach($this as $key=>$val) {
				$output[$key] = $val;
			}
			return $output;
		}

	}
?>