<?php
	/**
	 * Locations - on entering locations the user should be presented with an autocomplete field which tries to
	 * match existing locations in the system. If the input does not match an existing location, it should be saved.
	 * @author alanh
	 *
	 */
	class Ndoorse_Location extends Model {

		protected $locationID;
		protected $location;
		protected $country;

		public static function getLocations($asSelect = true, $withNull = true) {

			$dbConn = DatabaseConnection::getConnection();

			$stmt = $dbConn->prepareStatement('SELECT * FROM ndoorse_location ORDER BY location ASC');
			$result = $stmt->execute();

			$output = array();
			if($asSelect && $withNull) {
				$output[] = array('value'=>'', 'label'=>'(select)');
			}
			if($result instanceof Resultset && $result->hasRows()) {
				while($row = $result->nextRow()) {
					if($asSelect) {
						$output[] = array('value'=>$row['locationID'], 'label'=>$row['location']);
					} else {
						$output[] = new Ndoorse_Location($row);
					}
				}

			}
			return $output;

		}

		public static function saveFromPost($name, $id) {

			if(empty($name)) {
				return null;
			}
			if(empty($id)) {
				$id = 0;
			}

			$dbConn = DatabaseConnection::getConnection();
			$sql = 'SELECT * FROM ndoorse_location WHERE location LIKE :name OR locationID = :id';
			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('name', $name);
			$stmt->bindParameter('id', $id);

			$result = $stmt->execute();
			if($result instanceof Resultset && $result->hasRows()) {
				while($row = $result->nextRow()) {
					// if a complete match, return existing ID
					if($row['location'] == $name && $row['locationID'] == $id) {
						return $id;
					}
					// if name matches but ID does not, return existing ID
					if($row['location'] == $name) {
						return $row['locationID'];
					}
				}
			}

			// if no match, create new one
			$stmt = $dbConn->prepareStatement('INSERT INTO ndoorse_location SET location = :name');
			$stmt->bindParameter('name', $name);
			if($stmt->execute()) {
				return $dbConn->getLastID();
			}
			throw new Exception('Ndoorse/Location/saveFromPost: could not save new location');

		}

		public static function autocomplete($text) {

			if(empty($text)) {
				return array();
			}

			$dbConn = DatabaseConnection::getConnection();
			$stmt = $dbConn->prepareStatement('SELECT * FROM ndoorse_location WHERE location LIKE :loc');
			$stmt->bindParameter('loc', $text . '%');

			$result = $stmt->execute();
			if($result instanceof Resultset && $result->hasRows()) {
				return $result->getResultsetAsArray();
			}
			return array();

		}

	}
?>