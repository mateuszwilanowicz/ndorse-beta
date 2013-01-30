<?php
	class Ndoorse_Request extends Model {

		protected $requestID;
		protected $userID;
		protected $offering;

		protected $summary;
		protected $description;
		protected $location;		// auto filled on load, do not save
		protected $locationID;
		protected $type;

		protected $datePosted;
		protected $dateUpdated;
		protected $dateExpires;

		protected $status;
		protected $anonymous = false;
		protected $board = true;


		const STATUS_INACTIVE = 0;
		const STATUS_PENDING = 1;
		const STATUS_ACTIVE = 2;

		const TYPE_ADVICE = 1;
		const TYPE_HELP = 2;
		const TYPE_INTRODUCTION = 4;
		const TYPE_MENTORING = 8;

		/**
		 * Load Request by ID and populate object, along with its location
		 * @param int $id
		 * @throws Exception
		 */
		public function loadByID($id) {

			if(empty($id)) {
				throw new Exception('Ndoorse/Request/loadByID: no ID');
			}

			$dbConn = DatabaseConnection::getConnection();

			$stmt = $dbConn->prepareStatement('
					SELECT r.*, l.location
						FROM ndoorse_request r
							LEFT OUTER JOIN ndoorse_location l
								USING(locationID)
						WHERE requestID = :id
				');
			$stmt->bindParameter('id', $id);

			$result = $stmt->execute();
			if($result instanceof Resultset && $result->hasRows()) {
				$row = $result->nextRow();
				return $this->loadFromArray($row);
			}

		}

		/**
		 * Return human-readable expiry date in d/m/y format
		 * @return string
		 */
		public function getExpiryDate() {

			return date('d/m/Y', strtotime($this->dateExpires));

		}

		/**
		 * Return human-readable post date in d/m/y format
		 * @return string
		 */
		public function getPostDate() {

			return date('d/m/Y', strtotime($this->datePosted));

		}

		/**
		 * Check against specified request type
		 * @param int $type
		 * @return boolean
		 */
		public function hasType($type) {

			return $this->type & $type;

		}

		/**
		 * Return array or comma separated string of available types
		 * @param bool $asArray		True to return array, false for comma-separated string.
		 * @return Ambigous <string, array>
		 */
		public function getTypes($asArray = false) {

			$types = array();
			if($this->hasType(self::TYPE_ADVICE)) {
				$types[] = 'Advice';
			}
			if($this->hasType(self::TYPE_HELP)) {
				$types[] = 'Help';
			}
			if($this->hasType(self::TYPE_INTRODUCTION)) {
				$types[] = 'Introduction';
			}
			if($this->hasType(self::TYPE_MENTORING)) {
				$types[] = 'Mentoring';
			}

			return $asArray ? $types : implode(', ', $types);

		}

		public static function getRequests($asCount = false, $status = -1, $args = array(), $asBoard = true) {

			$dbConn = DatabaseConnection::getConnection();

			if($status == -1) {
				$status = self::STATUS_ACTIVE;
			}

			$params = array('status'=>$status);

			if($asCount) {
				$sql = 'SELECT COUNT(*) AS numRequests ';
			} else {
				$sql = 'SELECT r.*, l.location ';
			}

			$sql .= 'FROM ndoorse_request r ';

			if(!$asCount) {
				$sql .= 'LEFT OUTER JOIN ndoorse_member m
							USING(userID)
						LEFT OUTER JOIN ndoorse_location l
							ON r.locationID = l.locationID ';
			}

			$sql .= 'WHERE r.status = :status ';

			if(!$asCount) {
				if($asBoard) {
					$sql .= 'AND dateExpires >= NOW() ';
					$sql .= 'AND board = 1 ';
				}
			}

			if(isset($args['keywords']) && !empty($args['keywords'])) {
				$join = isset($args['keywordoptions']) && $args['keywordoptions'] == 'all' ? ' AND ' : ' OR ';

				$keywords = explode(' ', $args['keywords']);
				$sql .= 'AND (';
				$tmp = array();
				for($i = 0; $i < count($keywords); ++$i) {
					$tmp[] = '(summary LIKE :keyw' . $i . ' OR description LIKE :keyw' . $i . ')';
					$params['keyw' . $i] = '%' . $keywords[$i] . '%';
				}
				$sql .= implode($join, $tmp);
				$sql .= ') ';
			}
			if(isset($args['location']) && !empty($args['location'])) {
				$sql .= ' AND locationID = :location';
				$params['location'] = $args['location'];
			}
			if(isset($args['offering']) && $args['offering'] !== '') {
				$params['offering'] = $args['offering'] == 1 ? 1 : 0;
				$sql .= ' AND offering = :offering ';
			}
			if(isset($args['type_advice'])) {
				$sql .= ' AND type & ' . Ndoorse_Request::TYPE_ADVICE . ' = ' . Ndoorse_Request::TYPE_ADVICE . ' ';
			}
			if(isset($args['type_help'])) {
				$sql .= ' AND type & ' . Ndoorse_Request::TYPE_HELP . ' = ' . Ndoorse_Request::TYPE_HELP . ' ';
			}
			if(isset($args['type_introduction'])) {
				$sql .= ' AND type & ' . Ndoorse_Request::TYPE_INTRODUCTION . ' = ' . Ndoorse_Request::TYPE_INTRODUCTION . ' ';
			}
			if(isset($args['type_mentoring'])) {
				$sql .= ' AND type & ' . Ndoorse_Request::TYPE_MENTORING . ' = ' . Ndoorse_Request::TYPE_MENTORING . ' ';
			}
			if(isset($args['datePosted']) && !empty($args['datePosted'])) {
				$params['startDate'] = $args['datePosted'] . ' 00:00:00';
				$params['endDate'] = $args['datePosted'] . ' 23:59:59';
				$sql .= ' AND datePosted BETWEEN :startDate AND :endDate';
			}

			if(!$asCount) {
				$sql .= '	ORDER BY datePosted ASC, summary ASC';
			}

			$stmt = $dbConn->prepareStatement($sql);

			foreach($params as $key=>$val) {
				$stmt->bindParameter($key, $val);
			}

			$result = $stmt->execute();

			$output = array();
			if($result instanceof Resultset && $result->hasRows()) {
				while($row = $result->nextRow()) {
					if($asCount) {
						return $row['numRequests'];
					}
					$output[] = new Ndoorse_Request($row);
				}
			}
			return $output;
		}

		public static function getStats() {

			$dbConn = DatabaseConnection::getConnection();
			$sql = 'SELECT status, COUNT(*) AS numRequests
						FROM ndoorse_request
						WHERE status > :status
						GROUP BY status';
			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('status', self::STATUS_INACTIVE);
			$result = $stmt->execute();

			$output = array('active'=>0, 'pending'=>0, 'total'=>0);
			if($result instanceof Resultset && $result->hasRows()) {
				while($row = $result->nextRow()) {
					if($row['status'] == self::STATUS_ACTIVE) {
						$output['active'] = $row['numRequests'];
						$output['total'] += $row['numRequests'];
					} else if($row['status'] == self::STATUS_PENDING) {
						$output['pending'] = $row['numRequests'];
						$output['total'] += $row['numRequests'];
					}
				}
			}

			$sql = 'SELECT COUNT(DISTINCT requestID) AS recent
					FROM ndoorse_request
					  WHERE `status` > :status AND datePosted >= date_sub(curdate(), interval WEEKDAY(curdate()) day)';
			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('status', self::STATUS_INACTIVE);
			$result = $stmt->execute();
			if($result instanceof Resultset && $result->hasRows()) {
				$output = array_merge($output, $result->nextRow());
			} else {
				$output['recent'] = 0;
			}

			return $output;

		}

		public static function getStatusOptions() {

			return array(array('value'=>self::STATUS_ACTIVE, 'label'=>'Active'),
						 array('value'=>self::STATUS_PENDING, 'label'=>'Pending'),
						 array('value'=>self::STATUS_INACTIVE, 'label'=>'Inactive')
					);

		}

		public function save() {
			if(!$this->getID()) {
				$this->datePosted = date('Y-m-d H:i:s');
			}
			return $this->saveModel(array('location'));
		}

	}
?>