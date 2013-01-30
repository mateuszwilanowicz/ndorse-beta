<?php
	class Ndoorse_Recommendation extends Model {

        protected $recommendationID;
        protected $referrerID;
		protected $applicantID;
		protected $entityID;
        protected $entity;
        protected $date;

        public static function getStats() {

        	$dbConn = DatabaseConnection::getConnection();

        	$sql = 'SELECT COUNT(DISTINCT recommendationID) AS recent
					FROM ndoorse_recommendation
					  WHERE `date` >= date_sub(curdate(), interval WEEKDAY(curdate()) day)';
			$stmt = $dbConn->prepareStatement($sql);
			$result = $stmt->execute();
			if($result instanceof Resultset && $result->hasRows()) {
				$output = $result->nextRow();
			} else {
				$output = array('recent'=>0);
			}

			return $output;

        }

		public function save() {
			return $this->saveModel();
		}

	}
?>