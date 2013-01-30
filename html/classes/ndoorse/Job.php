<?php
	/**
	 * Job vacancy class
	 *
	 * @author alanh
	 *
	 */
	class Ndoorse_Job extends Model {

		protected $jobID;
		protected $userID;				// userID of poster
		protected $firstname;			// poster's name - autopopulated on load, not saved
		protected $lastname;

		protected $title;				// job title
		protected $description;			// job description
		protected $location;			// location name of job - autopopulated on load, not saved
		protected $locationID;			// location of job

		protected $company;				//

		protected $hours;				// fulltime/parttime
		protected $type;				// permanent/temporary/contract

		protected $skills;				// desired skills @todo is this how we want to do it?
		protected $minSalary;			// salary range
		protected $maxSalary;

		protected $datePosted;			// date the job was posted
		protected $dateExpires;			// date the job posting expires
		protected $anonymous;			// If 1, do not show poster's details
		protected $board = 1;			// show on the job board

		protected $notes;

		protected $status;				// see constants below
		const STATUS_PENDING = 0;		// job deleted/set inactive
		const STATUS_AWAITINGAPPROVAL = 0;
		const STATUS_INACTIVE = 1;		// job awaiting approval
		const STATUS_ACTIVE = 2;		// job has been approved
		const STATUS_REMOVED = 3;

		/**
		 * load job by ID including company and member details
		 * @param int $id
		 * @throws Exception
		 * @return boolean
		 */
		public function loadByID($id) {

			if(empty($id)) {
				throw new Exception('Ndoorse/Job/loadByID: No ID');
			}

			$dbConn = DatabaseConnection::getConnection();
			$sql = 'SELECT j.*, m.firstname, m.lastname, l.location
					FROM ndoorse_job j
						LEFT OUTER JOIN ndoorse_member m
							USING(userID)
						LEFT OUTER JOIN ndoorse_location l
							ON(l.locationID = j.locationID)
					WHERE jobID = :jobID';
			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('jobID', $id);
			$result = $stmt->execute();

			if($result instanceof Resultset && $result->hasRows()) {
				$this->loadFromArray($result->nextRow());
				return true;
			}
			return false;

		}

		/**
		 * Return expiry date formatted dd/mm/yyyy
		 * @return string
		 */
		public function getExpiryDate() {

			return date('d/m/Y', strtotime($this->dateExpires));

		}

		/**
		 * Return date posted formatted dd/mm/yyyy
		 * @return string
		 */
		public function getPostDate() {

			return date('d/m/Y', strtotime($this->datePosted));

		}

		/**
		 * Return filtered list of jobs, paginated
		 *
		 * @param int $page					// page number, or 0 for all
		 * @param int $pagination			// page size
		 * @param String $orderBy			// field to sort by
		 * @param String $dir				// sort order [asc|desc]
		 * @param bool $inDate				// respect expiry date
		 * @param int $status				// job status (see constants)
		 * @return array:Ndoorse_Job
		 */
		public static function getJobs($asCount = false, $status = self::STATUS_ACTIVE, $inDate = true, $page = 0, $pagination = 20, $orderBy = 'datePosted', $dir = '') {

    	    $params = array();

			$dbConn = DatabaseConnection::getConnection();

			$sql = 'SELECT ';
			if($asCount) {
				$sql .= 'COUNT(*) AS jobCount ';
			} else {
				$sql .= 'j.*, m.firstname, m.lastname, l.location ';
			}
			$sql .= '	FROM ndoorse_job j ';
			if(!$asCount) {
				$sql .= 'LEFT OUTER JOIN ndoorse_member m
							USING(userID)
						LEFT OUTER JOIN ndoorse_location l
							ON(l.locationID = j.locationID) ';
			}
			$sql .= ' WHERE ';
			if($inDate) {
				$sql .= ' (dateExpires >= NOW() OR dateExpires = 0) AND ';
			}
			$sql .= ' j.status = :status ';

            if(!$asCount) {
                $sql .= ' ORDER BY ' . $orderBy . ' ' . $dir;

                if($page > 0) {
                    $sql .= ' LIMIT ' . ($page - 1) * $pagination . ', ' . $pagination;
                }
            }

			$stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('status', $status);
       		$result = $stmt->execute();
			$output = array();
			if($result instanceof Resultset && $result->hasRows()) {
				while($row = $result->nextRow()) {
					if($asCount) {
						return $row['jobCount'];
					} else {
						$output[] = new Ndoorse_Job($row);
					}
				}
			}
			return $output;

		}

        public static function getAllJobs($asCount = false, $inDate = true, $page = 0, $pagination = 20, $orderBy = '', $dir = 'asc',$args) {

        	/*
            switch($orderBy) {
                case 'datePosted':
                    $orderBy = 'datePosted';
                    break;
                case 'location':
                    $orderBy = 'location';
                    break;
                case 'company':
                    $orderBy = 'company';
                    break;
                case 'minSalary':
                    $orderBy = 'minSalary';
                    break;
                case 'maxSalary':
                    $orderBy = 'maxSalary';
                    break;
                case 'status':
                    $orderBy = 'status';
                    break;
                case 'type':
                    $orderBy = 'type';
                    break;
                case 'hours':
                    break;
                default:
                    $orderBy = 'datePosted';
            }
            */
            $dir = $dir == 'asc' ? 'asc' : 'desc';
            $params = array();
            $dbConn = DatabaseConnection::getConnection();

            $sql = 'SELECT ';
            if($asCount) {
                $sql .= 'COUNT(*) AS jobCount ';
            } else {
                $sql .= 'j.*, m.firstname, m.lastname, l.location ';
            }
            $sql .= '   FROM ndoorse_job j ';
            if(!$asCount) {
                $sql .= 'LEFT OUTER JOIN ndoorse_member m
                            USING(userID)
                        LEFT OUTER JOIN ndoorse_location l
                            ON(l.locationID = j.locationID) ';
            }
            $sql .= ' WHERE userID = :userID ';
            if($inDate) {
                $sql .= ' AND (dateExpires >= NOW() OR dateExpires = 0) ';
            }
            if(isset($args['keywords']) && !empty($args['keywords'])) {
                $join = isset($args['keywordoptions']) && $args['keywordoptions'] == 'all' ? ' AND ' : ' OR ';

                $keywords = explode(' ', $args['keywords']);
                $sql .= 'AND (';
                $tmp = array();
                for($i = 0; $i < count($keywords); ++$i) {
                    $tmp[] = '(title LIKE :keyw' . $i . ')';
                    $params['keyw' . $i] = '%' . $keywords[$i] . '%';
                }
                $sql .= implode($join, $tmp);
                $sql .= ') ';
            }
            if(isset($args['location']) && !empty($args['location'])) {
                $sql .= ' AND l.locationID = :location';
                $params['location'] = $args['location'];
            }

            if(isset($args['status']) && !empty($args['status'])) {
                $sql .= ' AND j.status = :status';
                $params['status'] = $args['status'];
            }

            if(!$asCount) {
                $sql .= ' ORDER BY ' . $orderBy . ' ' . $dir;

                if($page > 0) {
                    $sql .= ' LIMIT ' . ($page - 1) * $pagination . ', ' . $pagination;
                }
            }
            $stmt = $dbConn->prepareStatement($sql);
            foreach($params as $key=>$val) {
                $stmt->bindParameter($key, $val);
            }
            $stmt->bindParameter('userID', $_SESSION['user']->userID);
            $result = $stmt->execute();


            $output = array();
            if($result instanceof Resultset && $result->hasRows()) {
                while($row = $result->nextRow()) {
                    if($asCount) {
                        return $row['jobCount'];
                    } else {
                        $output[] = new Ndoorse_Job($row);
                    }
                }
            }
            return $output;

        }

		/**
		 * Return an array of statistics for the Admin dashboard
		 * - total number of jobs
		 * - total active jobs
		 * - total pending jobs
		 * - total jobs posted in current week
		 * @return array
		 */
		public static function getStats() {

			$dbConn = DatabaseConnection::getConnection();
			$sql = 'SELECT status, COUNT(*) AS numJobs
						FROM ndoorse_job
						WHERE status > :status
						GROUP BY status';
			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('status', self::STATUS_INACTIVE);
			$result = $stmt->execute();

			$output = array('active'=>0, 'pending'=>0, 'total'=>0);
			if($result instanceof Resultset && $result->hasRows()) {
				while($row = $result->nextRow()) {
					if($row['status'] == self::STATUS_ACTIVE) {
						$output['active'] = $row['numJobs'];
						$output['total'] += $row['numJobs'];
					} else if($row['status'] == self::STATUS_AWAITINGAPPROVAL) {
						$output['pending'] = $row['numJobs'];
						$output['total'] += $row['numJobs'];
					}
				}
			}

			$sql = 'SELECT COUNT(DISTINCT jobID) AS recent
					FROM ndoorse_job
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

		/**
		 * Decode type codes to human-readable form
		 * @return string
		 */
		public function getType() {
			switch($this->type) {
				case 'P':
					return 'Permanent';
				case 'T':
					return 'Temporary';
				case 'C':
					return 'Contract';
				default:
					return 'n/a';
			}
		}

		/**
		 * Decode hour codes to human-readable form
		 * @return string
		 */
		public function getHours() {
			switch($this->hours) {
				case 'FT':
					return 'Full Time';
				case 'PT':
					return 'Part Time';
				default:
					return 'n/a';
			}
		}

		/**
		 * Format salary entry depending on which fields are completed
		 * @return string
		 */
		public function getSalary() {

			if(empty($this->minSalary) && empty($this->maxSalary)) {
				return '';
			} else if(empty($this->minSalary)) {
				return '&pound;' . number_format($this->maxSalary);
			} else if(empty($this->maxSalary)) {
				return '&pound;' . number_format($this->minSalary);
			} else {
				return '&pound;' . number_format($this->minSalary) . ' - &pound;' . number_format($this->maxSalary);
			}

		}

		/**
		 * Return list of status options formatted for FormControl select
		 * @return array
		 */
		public static function getStatusOptions() {

			return array(array('label'=>'Active', 'value'=>self::STATUS_ACTIVE),
						 array('label'=>'Pending', 'value'=>self::STATUS_AWAITINGAPPROVAL),
						 array('label'=>'Inactive', 'value'=>self::STATUS_INACTIVE),
                         array('label'=>'Inactive', 'value'=>self::STATUS_REMOVED));

		}

		/**
		 * Return list of hour options formatted for FormControl select
		 * @return array
		 */
		public static function getHoursOptions() {

			return array(array('label'=>'Full Time', 'value'=>'FT'),
						 array('label'=>'Part Time', 'value'=>'PT'),
						 array('label'=>'n/a', 'value'=>''));

		}

		/**
		 * Return list of type options formatted for FormControl select
		 * @return array
		 */
		public static function getTypeOptions() {

			return array(array('label'=>'Permanent', 'value'=>'P'),
						 array('label'=>'Temporary', 'value'=>'T'),
						 array('label'=>'Contract', 'value'=>'C'),
						 array('label'=>'n/a', 'value'=>''));

		}

		/**
		 * Save job, excluding non-Job fields
		 * @return Resultset
		 */
		public function save() {
			if(!$this->getID()) {
				$this->datePosted = date('Y-m-d H:i:s');
			}
			return $this->saveModel(array('firstname', 'lastname', 'company', 'companyDescription', 'companyLogo', 'location'));
		}

        public function delete() {
            if(!empty($this->jobID)) {
                $dbConn = DatabaseConnection::getConnection();
                $sql = 'DELETE FROM ndoorse_job j
                        WHERE jobID = :jobID';

                $stmt = $dbConn->prepareStatement($sql);
                $stmt->bindParameter('jobID', $this->jobID);
                $result = $stmt->execute();
                if($result instanceof Resultset && $result->hasRows()) {
                    $row = $result->nextRow();
                    return true;
                }
                return false;
            } else {
                return false;
            }
        }

	}
?>