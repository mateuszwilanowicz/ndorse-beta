<?php
	class Ndoorse_Event extends Model {

		protected $eventID;
		protected $title;
		protected $details;

		protected $startDate;
		protected $endDate;
		protected $datePosted;

		protected $location;				// autopopulated, not saved
		protected $locationID;
		protected $repeatRule;

		protected $username;				// autopopulated, not saved
		protected $memberLevel;				// autopopulated, not saved
		protected $userID;
		protected $companyID;
		protected $company;					// } autopopulated, not saved
		protected $companyDescription;		// }

		protected $ticketURL;

		protected $private = 0;
		protected $status;

		public static $eventRules = array(
					'title' => array('required' => 'true', 'minlength'=>3, 'maxlength'=>100),
					'details' => array('required' => 'true'),
					'startDate' => array('required' => 'true', 'validator'=>'date'),
					'endDate' => array('validator'=>'date'),
					'location' => array('required' => 'true')
				);

		const STATUS_INACTIVE = 0;
		const STATUS_PENDING = 1;
		const STATUS_ACTIVE = 2;

		const ERR_LOAD_NOTFOUND = 0;
		const ERR_LOAD_PRIVATE = 1;

		public function __construct($params = null) {

			if(is_null($params)) {
				$this->startDate = date('Y-m-d H:i:s');
				$this->endDate = date('Y-m-d H:i:s');
			} else {
				return parent::__construct($params);
			}

		}

		public static function getEvents($month = null, $year = null, $mine = true, $status = 2) {

			if(is_null($month) || !is_int($month) || $month < 1 || $month > 12) {
				$month = date('m');
			}
			if(is_null($year) || !is_int($year) || $year < date('Y') || $year > date('Y') + 1) {
				$year = date('Y');
			}

			$startDate = strtotime('monday this week', mktime(0, 0, 0, $month, 1, $year));
			$endDate = strtotime('sunday this week 23:59:59', mktime(23, 59, 59, $month, date('t', mktime(0,0,0,$month,1,$year)), $year));

			$startDate = date('Y-m-d H:i:s', $startDate);
			$endDate = date('Y-m-d H:i:s', $endDate);

			$dbConn = DatabaseConnection::getConnection();
			$sql = 'SELECT e.*';
			if(!$mine) {
				$sql .= ',l.location, CONCAT_WS(" ", m.firstname, m.lastname) AS username ';
			}
			$sql .= ' FROM ndoorse_event e ';
			if(!$mine) {
				$sql .= 'LEFT OUTER JOIN ndoorse_location l
							USING(locationID)
						 LEFT OUTER JOIN ndoorse_member m
							USING(userID) ';
			}


			$sql .= 'WHERE e.status = :status ';

			if($mine) {
				$sql .= ' AND startDate >= :startDate
						AND endDate <= :endDate ';
				$sql .= 'AND (private = false
							OR (private = true AND userID = :userID)) ';
			}
			$sql .=	'ORDER BY startDate ASC';

			$stmt = $dbConn->prepareStatement($sql);

			$stmt->bindParameter('status', $status);
			if($mine) {
				$stmt->bindParameter('startDate', $startDate);
				$stmt->bindParameter('endDate', $endDate);
				$stmt->bindParameter('userID', $_SESSION['user']->getID());
			}

			$result = $stmt->execute();
			$output = array();
			if($result instanceof Resultset && $result->hasRows()) {
				while($row = $result->nextRow()) {
					$date = date('Y-m-d', strtotime($row['startDate']));
					if(array_key_exists($date, $output)) {
						$output[$date][] = new Ndoorse_Event($row);
					} else {
						$output[$date] = array(new Ndoorse_Event($row));
					}
				}
			}
			return $output;

		}

		public static function getEventCount($status = -1) {

			if($status == -1) {
				$status = self::STATUS_PENDING;
			}

			$dbConn = DatabaseConnection::getConnection();

			$stmt = $dbConn->prepareStatement('SELECT COUNT(*) AS numEvents FROM ndoorse_event WHERE status = :status');
			$stmt->bindParameter('status', $status);

			$result = $stmt->execute();
			if($result instanceof Resultset && $result->hasRows()) {
				$row = $result->nextRow();
				return $row['numEvents'];
			}
			return 0;

		}

		public static function getStats() {

			$dbConn = DatabaseConnection::getConnection();
			$sql = 'SELECT status, COUNT(*) AS numEvents
						FROM ndoorse_event
						WHERE status > :status
						GROUP BY status';
			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('status', self::STATUS_INACTIVE);
			$result = $stmt->execute();

			$output = array('active'=>0, 'pending'=>0, 'total'=>0);
			if($result instanceof Resultset && $result->hasRows()) {
				while($row = $result->nextRow()) {
					if($row['status'] == self::STATUS_ACTIVE) {
						$output['active'] = $row['numEvents'];
						$output['total'] += $row['numEvents'];
					} else if($row['status'] == self::STATUS_PENDING) {
						$output['pending'] = $row['numEvents'];
						$output['total'] += $row['numEvents'];
					}
				}
			}

			$sql = 'SELECT COUNT(DISTINCT eventID) AS recent
					FROM ndoorse_event
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

		public function loadByID($id) {

			if(empty($id)) {
				throw new Exception('Ndoorse/Event/loadByID: no event ID specified');
			}

			$dbConn = DatabaseConnection::getConnection();

			$sql = 'SELECT e.*, l.location, CONCAT_WS(" ", m.firstname,  m.lastname) AS username, c.name AS company, c.description AS companyDescription, m.level
						FROM ndoorse_event e
							LEFT OUTER JOIN ndoorse_location l
								USING(locationID)
							LEFT OUTER JOIN ndoorse_member m
								USING(userID)
							LEFT OUTER JOIN ndoorse_serviceprovider c
								ON(e.companyID = c.serviceproviderID)
					WHERE eventID = :eventID
						AND e.status > ' . self::STATUS_INACTIVE;
			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('eventID', $id);

			$result = $stmt->execute();
			if($result instanceof Resultset && $result->hasRows()) {
				$row = $result->nextRow();
				$this->loadFromArray($row);
				switch($row['level']) {
					case Ndoorse_Member::LEVEL_ADMIN:
					case Ndoorse_Member::LEVEL_STAFF:
						$this->memberLevel = 'ndoorse';
						break;
					case Ndoorse_Member::LEVEL_PREMIUM:
						$this->memberLevel = 'premium';
						break;
					default:
						$this->memberlevel = '';
				}
				return true;
			}
			return false;

		}

		public static function loadEvent($id, $checkUser = true) {

			$event = new Ndoorse_Event($id);
			if(!$event->getID()) {
				return self::ERR_LOAD_NOTFOUND;
			}

			if($event->private && $event->userID != $_SESSION['user']->getID()) {
				return self::ERR_LOAD_PRIVATE;
			}

			return $event;

		}

		public function getStartDay() {

			return date('d/m/Y', strtotime($this->startDate));

		}

		public function getEndDay() {

			return date('d/m/Y', strtotime($this->endDate));

		}

		public function getStartTime() {

			if(!empty($this->startDate)) {
				return date('H:i', strtotime($this->startDate));
			}
			return null;

		}

		public function getEndTime() {

			if(!empty($this->endDate)) {
				return date('H:i', strtotime($this->startDate));
			}
			return null;

		}

		public function getDateRange() {

			$startDate = strtotime($this->startDate);
			$endDate = strtotime($this->endDate);

			$output = date('d/m/Y \a\t g:ia', $startDate);

			if(empty($this->endDate) || date('Y', $endDate) == 1970 || date('ymdHis', $endDate) == date('ymdHis', $startDate)) {
				return $output;
			}

			$output .= ' until ';

			if(date('Ymd', $endDate) == date('Ymd', $startDate)) {
				$output .= date('g:ia');
			} else {
				$output .= date('d/m/Y \a\t g:ia');
			}

			return $output;

		}

		public function getPostedBy() {

			if(empty($this->username) && empty($this->company)) {
				return '';
			}

			if(!empty($this->username)) {
				if(!empty($this->company)) {
					return $this->username . ' at ' . $this->company;
				} else {
					return $this->username;
				}
			} else {
				return $this->company;
			}

		}

		public function getTicketTypes() {

			if(!$this->getID()) {
				throw new Exception('Ndoorse/Event/getTicketTypes: No eventID');
			}

			$dbConn = DatabaseConnection::getConnection();

			$stmt = $dbConn->prepareStatement('SELECT * FROM ndoorse_event_ticket WHERE eventID = :eventID ORDER BY name ASC');
			$stmt->bindParameter('eventID', $this->getID());
			$result = $stmt->execute();

			if($result instanceof Resultset && $result->hasRows()) {
				return $result->getResultsetAsArray();
			}
			return array();

		}

		public function saveTicketTypes($tickets) {

			if(!$this->getID()) {
				throw new Exception('Ndoorse/Event/saveTicketTypes: no eventID');
			}

			$dbConn = DatabaseConnection::getConnection();

			// since we want to preserve our IDs, it's best to do this as a transaction
			$dbConn->beginTransaction();

			// clear out existing ticket types for this event
			$stmt = $dbConn->prepareStatement('DELETE FROM ndoorse_event_ticket WHERE eventID = :eventID');
			$stmt->bindParameter('eventID', $this->getID());
			$stmt->execute();

			// submit new ticket types
			if(!empty($tickets)) {
				$sql = 'INSERT INTO ndoorse_event_ticket (eventID, ticketID, name, price) VALUES ';
				$fields = array();
				$params = array('eventID'=>$this->getID());

				$i = 1;
				foreach($tickets as $ticket) {
					$fields[] = "(:eventID, " . (isset($ticket['id']) ? ":ticket$i" : 'null') .  ", :name$i, :price$i) ";
					if(isset($ticket['id'])) {
						$params["ticket$i"] =  $ticket['id'];
					}
					$params["name$i"] = $ticket['name'];
					$params["price$i"] = isset($ticket['price']) ? $ticket['price'] : 'null';
					++$i;
				}

				$sql .= implode(', ', $fields);

				$stmt = $dbConn->prepareStatement($sql);
				foreach($params as $key=>$val) {
					$stmt->bindParameter($key, $val);
				}

				if($stmt->execute()) {
					// else end transaction
					$dbConn->commitTransaction();
					return true;
				} else {
					// if failed to add new types, rollback
					$dbConn->rollbackTransaction();
					return false;
				}

			}

		}

		public static function getStatusOptions() {

			return array(array('value'=>self::STATUS_ACTIVE, 'label'=>'Active'),
					array('value'=>self::STATUS_PENDING, 'label'=>'Pending'),
					array('value'=>self::STATUS_INACTIVE, 'label'=>'Inactive')
			);

		}

		public static function getRepeatOptions() {

			return array(array('value'=>'', 'label'=>'(no repeat)'),
							array('value'=>'week', 'label'=>'Every week'),
							array('value'=>'month', 'label'=>'Every month'),
							array('value'=>'day', 'label'=>'Every day')
					);

		}

		public function save() {

			if(!$this->getID()) {
				$this->datePosted = date('Y-m-d H:i:s');
			}

			return $this->saveModel(array('company', 'companyDescription', 'location', 'username', 'userClass'));

		}

	}
?>