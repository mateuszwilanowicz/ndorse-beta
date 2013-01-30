<?php

	class Attribute extends Model {

		public static $STATUS_DISABLED = 0;
		public static $STATUS_ENABLED = 1;

	    public static $attributeRules = array(
			'key' => array('required' => 'true', 'maxlength' => 16),
			'type' => array('required' => 'true'),
			'name' => array('required' => 'true'),
			'editControl' => array('required' => 'true')
	    );

	    /** @var string The key for the attribute */
		protected $key;
		/** @var string The name of the attribute */
		protected $name;
		/** @var string The value of the attribute */
		protected $value;
		protected $siteID;
		/** @var string The description of the attribute */
		protected $description;
		/** @var int The status of the attribute */
		protected $status;
		/** @var int The display order for the attribute */
		protected $displayOrder;
		protected $values; // used by controls to build simple attribute list
		/** @var string The type of control used for editing */
		protected $editControl;
		/** @var string The possible values of the edit control */
		protected $editControlValues;
		/** @var string The type of attribute */
		protected $type;

		// constuctor / destructor

		public function __construct() {
			$this->values = new Map();
		}

		private function __clone() {}

		// Returns the value of the
		public function __toString() {
			return (string)$this->value;
		}

		/**
		 * Get the item's status as a more friendly text display
		 * @return array(status letter => long description)
		 */
		public function getStatusText() {
			$output = array();

			if($this->status & self::$STATUS_ENABLED) {
				$output['E'] = 'Enabled';
			}

			return $output;
		}


		/**
		* Sets the type
		* @param type The value to be set
		*/
		public function setType($type) {
			$this->type = strtoupper($type);
		}

		public function setEditControlValues($valueString) {
			if(!empty($valueString)) {
				$strings = array();
				$values = explode('|', $valueString);
				foreach($values as $row) {
					$tmp = explode('=', $row);
					if (sizeof($tmp) == 2) {
						$tmp[0] = trim($tmp[0]);
						$tmp[1] = trim($tmp[1]);
						$strings[] = implode('=',$tmp);
					}
				}
				$this->editControlValues = implode('|', $strings);
			} else {
				$this->editControlValues = '';
			}
		}

		public function getEditControlValues($asString = false) {
			if($asString) {
				return $this->editControlValues;
			} else {
				$allValues = explode('|', $this->value);
				$returnValues = array();
				$values = explode('|', $this->editControlValues);
				if(!empty($values)) {
					foreach($values as $row) {
						$tmp = explode('=', $row);
						if(count($tmp) > 1) {
							$tmp[0] = trim($tmp[0]);
							$tmp[1] = trim($tmp[1]);
						}
						$returnValues[] = array('label'=>$tmp[0], 'value'=>$tmp[1], 'selected'=>(in_array($tmp[1],$allValues)));
					}
					return $returnValues;
				}
				return array();
			}
		}

		public function save($oldkey = false) {
			$dbConn = DatabaseConnection::getConnection();

			if(is_null($this->displayOrder)) {
				$this->displayOrder = 0;
			}

			if(empty($oldkey) || !$oldkey) {
				$sql = <<<SQL1
					INSERT INTO tblAttribute(`key`,`type`,`name`,`status`,`description`,`siteID`,`displayOrder`,`editControl`,`editControlValues`)
						VALUES(:key, :type, :name, :status, :description, :siteID, :displayOrder, :editControl, :editControlValues);
SQL1;

			} else {
				$sql = <<<SQL2
					UPDATE tblAttribute SET `key`=:key, `type`=:type, `name`=:name, `status`=:status, `description`=:description, `siteID`=:siteID, `displayOrder`=:displayOrder, `editControl`=:editControl, `editControlValues`=:editControlValues
						WHERE `key` = :oldKey LIMIT 1;
SQL2;
			}
			$stmt = $dbConn->prepareStatement($sql);

			$stmt->bindParameter('key', $this->key);
			$stmt->bindParameter('type', $this->type);
			$stmt->bindParameter('name', $this->name);
			$stmt->bindParameter('status', $this->status);
			$stmt->bindParameter('description', $this->description);
			$stmt->bindParameter('siteID', $GLOBALS['site']->getSiteID());
			$stmt->bindParameter('displayOrder', $this->displayOrder);
			$stmt->bindParameter('editControl', $this->editControl);
			$stmt->bindParameter('editControlValues', $this->editControlValues);

			if(!empty($oldkey) && $oldkey !== false) {
				$stmt->bindParameter('oldKey', $oldkey);
			}

			return $stmt->execute();

		}

		public function loadAttributeFromObject($object) {
			foreach (array_keys(get_object_vars($object)) as $var) {
				$this->$var = $object->$var;
			}
		}

		public static function loadAttribute($attributeData) {
			$attribute = new Attribute();
			$attribute->key = $attributeData['key'];
			$attribute->name = $attributeData['name'];
			$attribute->description = $attributeData['description'];
			if (isset($attributeData['value']))
				$attribute->value = $attributeData['value'];
			$attribute->siteID = $attributeData['siteID'];
			$attribute->status = $attributeData['status'];
			$attribute->displayOrder = $attributeData['displayOrder'];
			$attribute->editControl = $attributeData['editControl'];
			$attribute->editControlValues = $attributeData['editControlValues'];
			$attribute->type = $attributeData['type'];
			return $attribute;
		}

		public static function getAllAttributes() {
			$dbConn = DatabaseConnection::getConnection();

			$stmt = $dbConn->prepareStatement('SELECT * FROM core_attribute WHERE siteID = :siteID ORDER BY `type`, `key`, `name`');
			$stmt->bindParameter('siteID', $GLOBALS['site']->getID());
			$result = $stmt->execute();

			$attributes = new Map();
			while ($result->hasRows()) {
				$row = $result->nextRow();
				$attribute = Attribute::loadAttribute($row);
				$attributes->add($attribute->getKey(), $attribute);
			}
			return $attributes;
		}

		public static function getEmptyAttributeByKey($key) {
			if (empty($key))
				return null;

			$site = $GLOBALS['site'];
			$dbConn = DatabaseConnection::getConnection();
			$sql = "SELECT * FROM core_attribute WHERE siteID = :siteID AND `key` = :key LIMIT 1;";
			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('siteID', $site->getSiteID());
			$stmt->bindParameter('key', $key);
			$result = $stmt->execute();

			if (!$result->hasRows())
				return null;

			$row = $result->nextRow();
			$attribute = Attribute::loadAttribute($row);
			return $attribute;
		}

		public static function getAttributesByType($type) {

			$dbConn = DatabaseConnection::getConnection();

			$stmt = $dbConn->prepareStatement('SELECT *, NULL AS `value` FROM core_attribute WHERE siteID = :siteID AND `type` = :type ORDER BY displayOrder ASC');
			$stmt->bindParameter('siteID', $GLOBALS['site']->getID());
			$stmt->bindParameter('type', $type);
			$result = $stmt->execute();

			$attributes = new Map();
			while ($result->hasRows()) {
				$row = $result->nextRow();
				$attribute = Attribute::loadAttribute($row);
				$attributes->add($attribute->key, $attribute);
			}
			return $attributes;
		}

		// @todo: override this class method?
		public static function getAttributeKeyByValue($value, $type) {
			if (empty($value) || empty($type))
				return null;

			$dbConn = DatabaseConnection::getConnection();

			switch($type) {
				/*
				 * update these for ndoorse

				case 'COMMUNITY':
					$table = 'tblCommunityAttribute';
					break;
				case 'MEMBER':
					$table = 'tblMemberAttribute';
					break;
				case 'MESSAGE':
					$table = 'tblMessageAttribute';
					break;
				case 'DISCUSSION':
					$table = 'tblDiscussionAttribute';
					break;
				case 'IMAGE':
					$table = 'tblImageAttribute';
					break;

					*/
			}

			$sql = "SELECT DISTINCT attributeKey FROM " . $table . " WHERE value = :value;";

			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('value', $value);
			$result = $stmt->execute();

			if ($result->getResultsetAsArray() == 0)
				return null;
			else if ($result->getRowCount() > 1)
				throw new Exception("Unable to determine attribute, too many results returned");

			$row = $result->nextRow();
			return $row['attributeKey'];
		}

	}

?>