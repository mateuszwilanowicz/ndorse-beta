<?php
	class Ndoorse_Connection extends Model {
		protected $requesterID;
		protected $respondentID;
		protected $connectionStatus;
		protected $date;

		const STATUS_PENDING = 0;
		const STATUS_ACCEPTED = 1;
		const STATUS_DENIED = 2;

		public static function connectionExist($id1, $id2) {
			$dbConn = DatabaseConnection::getConnection();

			$sql = 'SELECT * FROM ndoorse_connection WHERE ( requesterID = :id1 AND respondentID = :id2 ) OR ( requesterID = :id2 AND respondentID = :id1 )';
			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('id1', $id1);
			$stmt->bindParameter('id2', $id2);

			$result = $stmt->execute();
			if($result instanceof Resultset && $result->hasRows()) {
				//$row = $result->nextRow();
				return true;
			}
			return false;

		}

		public static function getConnectionByID($id1, $id2) {
			$dbConn = DatabaseConnection::getConnection();

			$sql = 'SELECT * FROM ndoorse_connection WHERE ( requesterID = :id1 AND respondentID = :id2 ) OR ( requesterID = :id2 AND respondentID = :id1 )';
			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('id1', $id1);
			$stmt->bindParameter('id2', $id2);

			$result = $stmt->execute();
			if($result instanceof Resultset && $result->hasRows()) {
				$row = $result->nextRow();
				return new Ndoorse_Connection($row);
			}
			return false;

		}

		public function saveConnection() {

			$dbConn = DatabaseConnection::getConnection();
			$class = get_class($this);


			$update = $this->requesterID > 0 && $this->respondentID > 0;

			$sql = $update ? 'UPDATE ' : 'INSERT INTO ';
			$sql .=  strtolower($class) . ' SET ';
			$vals = array();
			$sets = array();

			foreach($this as $key=>$val) {
				if($key != 'attributes') {
					$sets[$key] = $key . ' = :' . $key;
				}

			}
			$sql .= implode(', ', $sets);

			if($update) {
				$sql .= ' WHERE respondentID = :respondentID AND requesterID = :requesterID';
			}

			$stmt = $dbConn->prepareStatement($sql);

			foreach($this as $key=>$val) {
				// auto datestamp updates
				if($update && $key == 'updated') {
					$stmt->bindParameter('updated', date('Y-m-d H:i:s'));
				} else if($key != 'attributes') {
					$stmt->bindParameter($key, $this->$key);
				}
			}

			$result = $stmt->execute();

			return $result;

		}

	}


?>
