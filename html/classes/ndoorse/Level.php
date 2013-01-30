<?php
	class Ndoorse_Level extends Model {

		protected $levelID;
		protected $title;

		protected $recommendations;
		protected $priceMonth;
		protected $priceYear;


		public static function getLevels() {

			$dbConn = DatabaseConnection::getConnection();

			$attributes = Attribute::getAttributesByType('level');

			$sql = 'SELECT *
						FROM ndoorse_level l
						ORDER BY priceYear ASC';

			$stmt = $dbConn->prepareStatement($sql);
			$result = $stmt->execute();

			$output = array();
			if($result instanceof Resultset && $result->hasRows()) {
				while($row = $result->nextRow()) {
					$tmp = new Ndoorse_Level($row);
					$tmp->loadAttributes();

					$output[] = $tmp;
				}
			}
			return $output;

		}

		public function loadAttributes() {

			$dbConn = DatabaseConnection::getConnection();

			$sql = 'SELECT a.*, la.value
						FROM core_attribute a
							LEFT OUTER JOIN ndoorse_level_attribute la
								USING(`key`)
						WHERE a.`type` = "level" AND a.siteID = :siteID AND la.level = :level
						ORDER BY displayOrder ASC';
			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('siteID', $GLOBALS['site']->getID());
			$stmt->bindParameter('level', $this->levelID);
			$result = $stmt->execute();

			if($result instanceof Resultset && $result->hasRows()) {
				while($row = $result->nextRow()) {
					$this->attributes[] = Attribute::loadAttribute($row);
				}
			}

		}

		public function save() {

			return $this->saveModel();

		}

	}
?>