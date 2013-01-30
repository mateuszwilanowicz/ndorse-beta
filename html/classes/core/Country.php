<?php
	class Country extends Model {

		protected $countrycode;
		protected $name;

		public static function getCountries($asArray = false) {

			$dbConn = DatabaseConnection::getConnection();

			$result = $dbConn->prepareStatement('SELECT * FROM core_country ORDER BY name ASC')->execute();

			if(!$result instanceof Resultset || !$result->hasRows()) {
				Logger::log('No countries found', 'warning');
				return array();
			}

			if($asArray) {
				return $result->getResultsetAsArray();
			}
			$output = array();
			while($row = $result->nextRow()) {
				$output[] = array('label'=>$row['name'], 'value'=>$row['countrycode']);
			}

			return $output;

		}

		public static function getCountry($code) {

			if(empty($code)) {
				throw new Exception('Core/Country/getCountry: No country code specified');
			}

			$code = strtoupper($code);

			$dbConn = DatabaseConnection::getConnection();
			$stmt = $dbConn->prepareStatement('SELECT name FROM core_country WHERE countrycode = :code LIMIT 1');
			$stmt->bindParameter('code', $code);

			$result = $stmt->execute();
			if($result instanceof Resultset && $result->hasRows()) {
				$row = $result->nextRow();
				return $row['name'];
			}
			return '';

		}

	}
?>