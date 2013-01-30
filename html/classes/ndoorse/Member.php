<?php
	class Ndoorse_Member extends User {

		protected $title;
		protected $firstname;
		protected $lastname;
		protected $jobstatus;

		protected $address1;
		protected $address2;
		protected $region;
		protected $location;
        protected $locationID;
		protected $postcode;
		protected $country;
        protected $company;
        protected $city;
		protected $telhome;
		protected $telmobile;
		protected $telwork;

		protected $level = 0;
		protected $referrer;		// autopopulated, not saved
		protected $referrerID;
		protected $identifier;		// unique identifier for recommendations
		protected $avatar;

		protected $dateJoined;
		protected $dateApproved;
		protected $dateUpdated;
        protected $serviceProviders;

		const LEVEL_NORMAL = 1;
		const LEVEL_PREMIUM = 20;
		const LEVEL_SERVICEPROVIDER = 30;
		const LEVEL_RECRUITER = 40;
		const LEVEL_EMPLOYER = 50;
		const LEVEL_STAFF = 97;
		const LEVEL_ADMIN = 98;


		public function __construct($params = null) {
			if(!is_null($params)) {
				if(is_numeric($params)) {
					return $this->loadModelByID($params, 'userID');
				} else {
					return $this->loadFromArray($params);
				}
			}
		}

        public function getSkillsString() {
            $dbConn = DatabaseConnection::getConnection();

            $sql = 'SELECT s.skillID,s.name from ndoorse_education e
                    LEFT JOIN ndoorse_memberskill ms ON e.educationID = ms.entityID AND ms.entity = "education"
                    LEFT JOIN ndoorse_skill s ON s.skillID = ms.skillID
                    WHERE e.userID = :userID AND s.name IS NOT NULL
                    UNION DISTINCT
                    SELECT s.skillID,s.name from ndoorse_experience e
                    LEFT JOIN ndoorse_memberskill ms ON e.experienceID = ms.entityID AND ms.entity = "experience"
                    LEFT JOIN ndoorse_skill s ON s.skillID = ms.skillID
                    WHERE e.userID = :userID AND s.name IS NOT NULL
                    ORDER BY name ASC';

            $stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('userID', $this->userID);
            $return = array();
            $result = $stmt->execute();

            if($result instanceof Resultset && $result->hasRows()) {
                while($row = $result->nextRow()) {
                   $return[] = $row['name'];
                }

            }
            return implode(',', $return);
        }

        public static function deleteExperience($experienceID) {
            $dbConn = DatabaseConnection::getConnection();

            $sql = 'DELETE FROM ndoorse_experience WHERE experienceID = :experienceID AND userID = :userID';
            $stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('userID', $_SESSION['user']->userID);
            $stmt->bindParameter('experienceID', $experienceID);
            $result = $stmt->execute();

            return $result;

        }

        public static function deleteEducation($educationID) {
            $dbConn = DatabaseConnection::getConnection();

            $sql = 'DELETE FROM ndoorse_education WHERE educationID = :educationID AND userID = :userID';
            $stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('userID', $_SESSION['user']->userID);
            $stmt->bindParameter('educationID', $educationID);
            $result = $stmt->execute();

            return $result;

        }

        public static function addServiceProvider($serviceProviderID, $position = '') {
            $dbConn = DatabaseConnection::getConnection();

            $sql = 'REPLACE INTO ndoorse_serviceprovider_member VALUES (:serviceProviderID,:userID,3,:position)';
            $stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('userID', $_SESSION['user']->userID);
            $stmt->bindParameter('serviceProviderID', $serviceProviderID);
            $stmt->bindParameter('position', $position);
            $result = $stmt->execute();

            return $result;
        }

        public static function deleteServiceProvider($serviceProviderID) {
            //TODO: double check if the current user is the creator of the service provider
            
            $dbConn = DatabaseConnection::getConnection();

            $sql = 'DELETE FROM ndoorse_serviceprovider_member WHERE serviceproviderID = :serviceproviderID';
            $stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('userID', $_SESSION['user']->userID);
            $stmt->bindParameter('serviceproviderID', $serviceProviderID);
            $result = $stmt->execute();

            $sql = 'DELETE FROM ndoorse_serviceprovider WHERE serviceproviderID = :serviceproviderID';
            $stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('serviceproviderID', $serviceProviderID);
            $result = $stmt->execute();

            return $result;
        }

        public static function quitServiceProvider($serviceProviderID) {
            $dbConn = DatabaseConnection::getConnection();

            $sql = 'DELETE FROM ndoorse_serviceprovider_member WHERE serviceproviderID = :serviceproviderID AND memberID = :userID';
            $stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('userID', $_SESSION['user']->userID);
            $stmt->bindParameter('serviceproviderID', $serviceProviderID);
            $result = $stmt->execute();

            return $result;
        }

        public static function getAllServiceProviders($userID) {
            $dbConn = DatabaseConnection::getConnection();

            $sql = 'SELECT * FROM ndoorse_serviceprovider_member WHERE memberID = :userID';
            $stmt = $dbConn->prepareStatement($sql);
            $stmt->bindParameter('userID', $userID);
            $return = array();
            $result = $stmt->execute();
            if($result instanceof Resultset && $result->hasRows()) {
                while($row = $result->nextRow()) {
                   $return[] = new Ndoorse_Serviceprovider($row['serviceproviderID']);
                }

            }
            return $return;
        }

		public static function userExist($username) {
			$dbConn = DatabaseConnection::getConnection();

			$sql = 'SELECT * FROM ndoorse_member WHERE username = :username';
			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('username', $username);

			$result = $stmt->execute();
			if($result instanceof Resultset && $result->hasRows()) {
				//$row = $result->nextRow();
				return true;
			}
			return false;

		}

        public static function getAllMembers($orderby = 'lastname', $dir = 'asc') {
            switch($orderby) {
                case 'name':
                    $orderby = 'lastname';
                    break;
                case 'email':
                    $orderby = 'email';
                    break;
                case 'jobstatus':
                    $orderby = 'jobstatus';
                    break;
                default:
                    $orderby = false;
                    break;
            }
            $dbConn = DatabaseConnection::getConnection();

            $dir = $dir == 'asc' ? 'asc' : 'desc';

            $sql = 'SELECT * FROM ndoorse_member';
            if($orderby) {
                $sql .= ' ORDER BY ' . $orderby . ' ' . $dir;
            }

            $stmt = $dbConn->prepareStatement($sql);

            $result = $stmt->execute();
            $output = array();
            if($result instanceof Resultset && $result->hasRows()) {
                while($row = $result->nextRow())
                    $output[] = new Ndoorse_Member($row);
            }
            return $output;

        }

		public static function getUserIDByName($name) {
			$dbConn = DatabaseConnection::getConnection();

			if(strrpos('@',$name) === false) {
				//firstname lastname provided
				$name = explode(' ',$name);
				if(count($name) < 2) {
					return false;
				} else {
					$firstname = $name[0];
					$lastname = $name[1];

					$sql = 'SELECT * FROM ndoorse_member m
					        LEFT JOIN ndoorse_location l ON m.locationID = m.locationID
                            WHERE firstname = :firstname AND lastname = :lastname';

					$stmt = $dbConn->prepareStatement($sql);
					$stmt->bindParameter('firstname', $firstname);
					$stmt->bindParameter('lastname', $lastname);

					$result = $stmt->execute();
					if($result instanceof Resultset && $result->hasRows()) {
						$row = $result->nextRow();
						return $row['userID'];
					}
					return false;

				}
			} else {
				//email provided
				$sql = 'SELECT * FROM ndoorse_member m
                        LEFT JOIN ndoorse_location l ON m.locationID = m.locationID
				        WHERE username = :username';
				$stmt = $dbConn->prepareStatement($sql);
				$stmt->bindParameter('username', $name);

				$result = $stmt->execute();
				if($result instanceof Resultset && $result->hasRows()) {
					$row = $result->nextRow();
					return $row['userID'];
				}
				return false;
			}
		}

		public static function getUserByEmail($email) {

			$dbConn = DatabaseConnection::getConnection();

			$sql = 'SELECT userID FROM ndoorse_member m
                    LEFT JOIN ndoorse_location l ON m.locationID = m.locationID
			        WHERE email = :email LIMIT 1';
			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('email', $email);

			$result = $stmt->execute();
			if($result instanceof Resultset && $result->hasRows()) {
				$row = $result->nextRow();
				return $row['userID'];
			}
			return false;

		}

		public static function getTitles() {

			return array(
					array('value'=>'Mr', 'label'=>'Mr'),
					array('value'=>'Ms', 'label'=>'Ms'),
					array('value'=>'Mrs', 'label'=>'Mrs'),
					array('value'=>'Miss', 'label'=>'Miss')

					);

		}

		/**
		 * Log most recent successful login
		 * @return bool success
		 */
		public function processLogin() {

			if(!$this->getID()) {
				throw new Exception('ndoorse/member/login: no user');
			}

			$dbConn = DatabaseConnection::getConnection();

			$stmt = $dbConn->prepareStatement('REPLACE INTO ndoorse_member_login (userID, lastLogin) VALUES(:userID, NOW())');
			$stmt->bindParameter('userID', $this->userID);
			return $stmt->execute();

		}

		public function invite($email, $template = 'ndoorse_invitation', $params = array(), $subject = '', $standalone = true) {

			if(!Ndoorse_Member::userExist($email)) {
				$phantomUser = new Ndoorse_Member();
				$phantomUser->username = $email;
				$phantomUser->email = $email;
				$phantomUser->referrerID = $_SESSION['user']->getID();
				$phantomUser->save(array(),'userID');

				$newID = $phantomUser->getID();
				$activationCode = $phantomUser->createActivation('activate');

				$message = new EmailMessage();
				if(strlen($subject) < 3) {
					$subject = $_SESSION['user']->getName() . ' invited you to join Ndoorse Network';
				}
				$message->setSubject($subject);

				$params = array_merge($params, array('name'=>$_SESSION['user']->getName(),
								'url'=>BASE_URL,
								'title'=>$_SESSION['user']->getName() . ' invited you to join Ndoorse Network',
								'referrerID'=>$_SESSION['user']->getID(),
								'activationCode'=>$activationCode,
								'activationUrl'=>BASE_URL . 'members/activate/?key=' . $activationCode
								)
							);

				$message->loadTemplate($template, $params);
				$message->setSenderEmailAddress(EMAIL_FROM, EMAIL_FROM_NAME);
				$message->addRecipientEmailAddress($phantomUser->email);
				$result = $message->send();

				if($standalone) {
					$_SESSION['page_messages'][] = 'Your invitation has been sent.';
				}

				return $phantomUser->userID;
			} else {
				// user already registered, do you want to send a connection invite instead?
				$_SESSION['page_error'][] = "A member with this email is already registered.";

				return false;
			}
		}

		public static function getMembers($asCount = false, $status = -1, $page = 0, $pageSize = 0, $orderBy = 'name', $orderDir = 'ASC', $level = -1) {

			$dbConn = DatabaseConnection::getConnection();

			$sql = 'SELECT ';

			if($asCount) {
				$sql .= 'COUNT(*) AS memberCount ';
			} else {
				$sql .= 'm.userID, CONCAT_WS(" ", m.firstname, m.lastname) AS name,
						CONCAT_WS(" ", r.firstname, r.lastname) AS referrer, c.name AS company, m.status, m.level,
						m.dateJoined, m.dateApproved, IFNULL(l.lastLogin, "(never)") AS login ';
			}

			$sql .=	'	FROM ndoorse_member m';

			if(!$asCount) {
				$sql .= '	LEFT OUTER JOIN (ndoorse_serviceprovider c
								INNER JOIN ndoorse_serviceprovider_member cm
									USING(serviceproviderID))
								ON(cm.memberID = m.userID)
							LEFT OUTER JOIN ndoorse_member r
                                ON(m.referrerID = r.userID)
							LEFT OUTER JOIN ndoorse_member_login l
								ON(l.userID = m.userID) ';
			}

			$wheres = array();

			if($level > -1) {
				$wheres[] = 'm.level = :level';
			}
			if($status > -1) {
				$wheres[] = 'm.status = :status';
			}
			if(!empty($wheres)) {
				$sql .= ' WHERE ' . implode(' AND ', $wheres);
			}

			if(!$asCount) {
				$orderDir = $orderDir == 'ASC' ? 'ASC' : 'DESC';

				switch($orderBy) {
					case 'company':
						$orderBy = 'c.name ' . $orderDir;
					case 'referrer':
						$orderBy = 'r.lastname ' . $orderDir . ', r.firstname ' . $orderDir;
					default:
						$orderBy = 'm.lastname ' . $orderDir . ' , m.firstname ' . $orderDir;
				}

				$sql .= ' ORDER BY ' . $orderBy;

				if($page > 0 && $pageSize > 0) {
					$page = (int)$page;
					$pageSize = (int)$pageSize;

					$sql .= ' LIMIT ' . ($pageSize * ($page - 1)) . ', ' . $pageSize;
				}
			}

			$stmt = $dbConn->prepareStatement($sql);
			if($level > -1) {
				$stmt->bindParameter('level', $level);
			}
			if($status > -1) {
				$stmt->bindParameter('status', $status);
			}

			$result = $stmt->execute();

			if($result instanceof Resultset && $result->hasRows()) {
				if($asCount) {
					$row = $result->nextRow();
					return $row['memberCount'];
				}
				return $result->getResultsetAsArray();
			}
			return array();

		}

		public function connect($respondentID) {
			if(is_numeric($respondentID)) {
				if(!Ndoorse_Connection::connectionExist($_SESSION['user']->getID(), $respondentID)) {
					$newConnection = new Ndoorse_Connection();
					$newConnection->requesterID = $_SESSION['user']->getID();
					$newConnection->respondentID = $respondentID;
					$newConnection->connectionStatus = 0;
					//pr($newConnection);

					$reult = $newConnection->saveModel();
					redirect(BASE_URL . 'members/');
				} else {
					$connection = Ndoorse_Connection::getConnectionByID($_SESSION['user']->getID(), $respondentID);
					if($connection->connectionStatus == 0) {
						$connection->$connectionStatus = Ndoorse_Connection::STATUS_ACCEPTED;
						$connection->saveConnection();
					}
					redirect(BASE_URL . 'members/');
				}
			} else {
				redirect(BASE_URL . 'members/');
			}
		}

		public function getConnections() {
			$dbConn = DatabaseConnection::getConnection();
			$class = get_class($this);

			$tmp = explode('_',$class);
			$suffix = count($tmp) > 1 ? $tmp[1] : $class;

			//$sql = 'select concat_ws(" ", m.firstname, m.lastname) as name, c.*	from ndoorse_member m join (select respondentID as userID, connectionStatus, 1 as requester	from ndoorse_connection c where requesterID = :userID UNION select requesterID, connectionStatus, 0 as requester from ndoorse_connection c where respondentID = :userID) c on m.userID = c.userID';
			//$sql = 'SELECT * FROM ndoorse_connection WHERE requesterID = ' . $this->userID . ' OR respondentID = ' . $this->userID;
			$sql = 'select concat_ws(" ", m.firstname, m.lastname) as name, respondentID, requesterID, connectionStatus
					from ndoorse_connection c
					join ndoorse_member m on (m.userID = c.respondentID AND respondentID <> :userID) OR (m.userID = c.requesterID AND requesterID <> :userID)
					where c.respondentID = :userID or c.requesterID = :userID';

			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('userID', $_SESSION['user']->getID());

			$result = $stmt->execute();
			$connections = Array();

			if($result instanceof Resultset && $result->hasRows()) {
				while($row = $result->nextRow()) {
					$connections[] = $row;
				}
			}
			return $connections;
		}

		public function getName() {
			return $this->firstname . ' ' . $this->lastname;
		}

		public function getID() {
			return empty($this->userID) ? false : $this->userID;
		}

		/**
		 * Return autocomplete matches against current user's network
		 * @param String $text
		 * @return array:
		 */
		public static function autocomplete($text) {

			$dbConn = DatabaseConnection::getConnection();

			$stmt = $dbConn->prepareStatement('
					SELECT userID, CONCAT(firstname, " ", lastname) AS name
						FROM ndoorse_member m
							LEFT OUTER JOIN ndoorse_connection rc
								ON(m.userID = rc.requesterID AND rc.connectionStatus = 1 AND rc.respondentID = :userID)
							LEFT OUTER JOIN ndoorse_connection dc
								ON(m.userID = dc.respondentID AND dc.connectionStatus = 1 AND dc.requesterID = :userID)
						WHERE (firstname LIKE :name
							OR lastname LIKE :name
							OR CONCAT(firstname, " ", lastname) LIKE :name)');
			$stmt->bindParameter('name', $text . '%');
			$stmt->bindParameter('userID', $_SESSION['user']->getID());
			$result = $stmt->execute();

			if($result instanceof Resultset) {
				return $result->getResultsetAsArray();
			}
			return array();

		}

		/**
		 * Return autocomplete matches against ALL users
		 * @fixme This should not be used once we have the network diagram
		 * @param String $text
		 * @return array:
		 */
		public static function autocompleteNew($text) {

			$dbConn = DatabaseConnection::getConnection();

			$stmt = $dbConn->prepareStatement('
					SELECT userID, CONCAT(firstname, " ", lastname) AS name
						FROM ndoorse_member m
						WHERE (firstname LIKE :name
							OR lastname LIKE :name
							OR email LIKE :name)');
			$stmt->bindParameter('name', $text . '%');
			$result = $stmt->execute();

			if($result instanceof Resultset) {
				return $result->getResultsetAsArray();
			}
			return array();

		}

		public static function getExperience($userID) {
			$dbConn = DatabaseConnection::getConnection();
			$experience = array();
			$stmt = $dbConn->prepareStatement('
				SELECT *, GROUP_CONCAT(s.name SEPARATOR ",") as skills from ndoorse_experience e
					LEFT JOIN ndoorse_memberskill ms ON e.experienceID = ms.entityID AND ms.entity = :entity
					LEFT JOIN ndoorse_skill s ON s.skillID = ms.skillID
					WHERE e.userID = :userID
					GROUP BY experienceID
					ORDER BY year desc;
			');

			$stmt->bindParameter('userID', $userID);
			$stmt->bindParameter('entity', 'experience');
			$result = $stmt->execute();

			if($result instanceof Resultset) {
				while($row = $result->nextRow()) {
					$experience[] = $row;
				}
			}
			return $experience;
		}

        public static function getThreeLastJobs($userID) {
            $dbConn = DatabaseConnection::getConnection();
            $experience = array();
            $stmt = $dbConn->prepareStatement('
                SELECT *, GROUP_CONCAT(s.name SEPARATOR ",") as skills from ndoorse_experience e
                    LEFT JOIN ndoorse_memberskill ms ON e.experienceID = ms.entityID AND ms.entity = :entity
                    LEFT JOIN ndoorse_skill s ON s.skillID = ms.skillID
                    WHERE e.userID = :userID
                    GROUP BY experienceID
                    ORDER BY year desc
                    LIMIT 3;
            ');

            $stmt->bindParameter('userID', $userID);
            $stmt->bindParameter('entity', 'experience');
            $result = $stmt->execute();

            if($result instanceof Resultset) {
                while($row = $result->nextRow()) {
                    $experience[] = $row;
                }
            }
            return $experience;
        }

		public static function getEducation($userID) {
			$dbConn = DatabaseConnection::getConnection();
			$education = array();
			$stmt = $dbConn->prepareStatement('
				SELECT *, GROUP_CONCAT(s.name SEPARATOR ",") as skills from ndoorse_education e
					LEFT JOIN ndoorse_memberskill ms ON e.educationID = ms.entityID AND ms.entity = :entity
					LEFT JOIN ndoorse_skill s ON s.skillID = ms.skillID
					WHERE e.userID = :userID
					GROUP BY educationID
					ORDER BY year desc;
			');

			$stmt->bindParameter('userID', $userID);
			$stmt->bindParameter('entity', 'education');
			$result = $stmt->execute();

			if($result instanceof Resultset) {
				while($row = $result->nextRow()) {
					$education[] = $row;
				}
			}
			return $education;
		}

		/**
		 * Return a text description of the member's level
		 * @param int $level
		 * @return string
		 */
		public static function getLevelName($level = 0) {

			switch($level) {
				case self::LEVEL_ADMIN:
					return 'Administrator';
				case self::LEVEL_EMPLOYER:
					return 'Employer';
				case self::LEVEL_NORMAL:
					return 'Member';
				case self::LEVEL_PREMIUM:
					return 'Premium Member';
				case self::LEVEL_RECRUITER:
					return 'Recruiter';
				case self::LEVEL_STAFF:
					return 'Staff';
				default:
					return '(unknown)';
			}

		}

		/**
		 * Return array of member levels suitable for a Select control
		 * @return array
		 */
		public static function getLevels() {

			return array(
				array('value'=>self::LEVEL_ADMIN, 'label'=>'Administrator'),
				array('value'=>self::LEVEL_EMPLOYER, 'label'=>'Employer'),
				array('value'=>self::LEVEL_NORMAL, 'label'=>'Member'),
				array('value'=>self::LEVEL_PREMIUM, 'label'=>'Premium Member'),
				array('value'=>self::LEVEL_RECRUITER, 'label'=>'Recruiter'),
				array('value'=>self::LEVEL_STAFF, 'label'=>'Staff')
			);

		}

		public static function getStatuses() {

			return array(
				array('value'=>self::STATUS_ACTIVE, 'label'=>'Active'),
				array('value'=>self::STATUS_PENDING, 'label'=>'Pending'),
				array('value'=>self::STATUS_INACTIVE, 'label'=>'Inactive')
			);

		}

		public static function getStats() {

			$dbConn = DatabaseConnection::getConnection();

			$sql = 'SELECT COUNT(m.userID) AS `total`,
  						SUM(IF(m.status = 2, 1, 0)) AS active,
  						SUM(IF(m.status = 1, 1, 0)) AS pending,
						SUM(IF(m.dateJoined >= date_sub(curdate(), interval WEEKDAY(curdate()) day), 1, 0)) AS recent
					  FROM ndoorse_member m
  						WHERE m.status > :status';
			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('status', self::STATUS_INACTIVE);
			$result = $stmt->execute();
			if($result instanceof Resultset && $result->hasRows()) {
				$output = $result->nextRow();
			} else {
				$output = array('total'=>0, 'active'=>0, 'pending'=>0);
			}

			$sql = 'SELECT COUNT(DISTINCT l.userID) AS loggedIn
					FROM ndoorse_member m
					  INNER JOIN ndoorse_member_login l
					      USING(userID)
					  WHERE m.status > :status AND l.lastLogin >= date_sub(curdate(), interval WEEKDAY(curdate()) day)';
			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('status', self::STATUS_INACTIVE);
			$result = $stmt->execute();
			if($result instanceof Resultset && $result->hasRows()) {
				$output = array_merge($output, $result->nextRow());
			} else {
				$output['loggedIn'] = 0;
			}

			return $output;

		}

		public function getAddress() {

			$output = $this->address1;
			if(!empty($this->address2)) {
				$output .= '<br />' . $this->address2;
			}
			if(!empty($this->region)) {
				$output .= '<br />' . $this->region;
			}
			if(!empty($this->postcode)) {
				$output .= '<br />' . $this->postcode;
			}
			if(!empty($this->country)) {
				$output .= '<br />' . Country::getCountry($this->country);
			}

			return $output;


		}

		public function upgrade($level) {

			$this->level = $level->getID();

			$dbConn = DatabaseConnection::getConnection();
			$stmt = $dbConn->prepareStatement('UPDATE ndoorse_member SET level = :level WHERE userID = :userID');
			$stmt->bindParameter('level', $this->level);
			$stmt->bindParameter('userID', $this->userID);

			return $stmt->execute();

		}

		public function setPassword($password) {

			if(!$this->getID()) {
				throw new Exception('Ndoorse/Member/SetPassword: no user.');
			}

			$this->password = $this->getPasswordHash($password);

			$dbConn = DatabaseConnection::getConnection();
			$stmt = $dbConn->prepareStatement('UPDATE ndoorse_member SET password = :hash WHERE userID = :userID');
			$stmt->bindParameter('hash', $this->password);
			$stmt->bindParameter('userID', $this->getID());
			return $stmt->execute();

		}

		/**
		 * Check if specified identifier is valid, by checking if it conflicts with existing ones
		 * @param String $identifier
		 * @throws Exception
		 * @return boolean true if valid
		 */
		public static function checkIdentifier($identifier) {

			if(empty($identifier)) {
				return false;
			}

			$dbConn = DatabaseConnection::getConnection();
			$stmt = $dbConn->prepareStatement('SELECT COUNT(*) AS dups FROM ndoorse_member WHERE identifier = :identifier');
			$stmt->bindParameter('identifier', strtoupper($identifier));

			$result = $stmt->execute();
			if($result instanceof Resultset && $result->hasRows()) {
				$row = $result->nextRow();
				if($row['dups'] == 0) {
					return true;
				}
				return false;
			}
			throw new Exception('Ndoorse/Member/checkIdentifer: Query failed');

		}

		public static function generateIdentifier($length = 10) {

			$valid = false;
			$attempts = 0;

			while(!$valid && $attempts < 10) {
				$identifier = strtoupper(self::generateKey($length));
				$valid = self::checkIdentifier($identifier);
				++$attempts;
			}

			if(!$valid) {
				throw new Exception('Ndoorse/Member/generateIdentifier: Failed to generate valid identifier after ' . $attempts . ' attempts');
			}

			return $identifier;

		}

		public function save() {

			if(empty($this->dateJoined)) {
				$this->dateJoined = date('Y-m-d H:i:s');
			}

			if(empty($this->identifier)) {
				$this->identifier = self::generateIdentifier();
			}

            //pr($this);
			return $this->saveModel(array('dateUpdated', 'dateApproved', 'referrer', 'password','serviceProviders','location'), 'userID');

		}

        public static function login($username, $password) {

            self::logout();

            if(empty($username) || empty($password)) {
                throw new Exception('User/Login: Username or password not specified.');
            }

            $dbConn = DatabaseConnection::getConnection();

            $fullClass = get_called_class();
            $className = get_called_class();

            $class_parts = explode('_', $className);
            if(count($class_parts) > 1) {
                $package = $class_parts[0];
                $className = strtolower($class_parts[1]);
                $stmt = $dbConn->prepareStatement('SELECT m.*, l.location FROM ' . strtolower($package) . '_' . strtolower($className) . ' m LEFT JOIN ndoorse_location l ON m.locationID = l.locationID WHERE username = :username LIMIT 1');
                $stmt->bindParameter('username', $username);
            } else {
                $stmt = $dbConn->prepareStatement('SELECT * FROM core_user WHERE username = :username LIMIT 1');
            }

            $result = $stmt->execute();

            if($result instanceof Resultset && $result->hasRows()) {
                $row = $result->nextRow();

                $hasher = new PasswordHash(PASSWORD_PASSES, PASSWORD_PORTABLE);
                if($hasher->CheckPassword($password, $row['password'])) {

                    $user = new $fullClass($row);
                    return $user;
                }
            }

            return false;

        }

	}


	class Ndoorse_Member_Upgrade extends Model {

		protected $userID;
		protected $transactionID;

		protected $oldLevel;
		protected $newLevel;
		protected $datePaid;
		protected $dateExpires;

		public static function getUpgrades($type = 'new') {

			$dbConn = DatabaseConnection::getConnection();

			$sql = 'SELECT m.firstname, m.lastname, u.*, t.*, ol.title AS oldLevel, nl.title AS newLevel
						FROM ndoorse_member_upgrade u
						LEFT OUTER JOIN ndoorse_member m
							USING(userID)
						LEFT OUTER JOIN ndoorse_level ol
							ON(ol.levelID = u.oldLevel)
						LEFT OUTER JOIN ndoorse_level nl
							ON(nl.levelID = u.newLevel)
						LEFT OUTER JOIN core_transaction t
							ON(t.transactionID = u.transactionID)
					';
			switch($type) {
				case 'new':
					$sql .= ' WHERE u.datePaid >= NOW() - INTERVAL 1 MONTH ORDER BY datePaid DESC';
					break;
				case 'expiring':
					$sql .= ' WHERE u.dateExpires <= NOW() + INTERVAL 1 MONTH ORDER BY dateExpires ASC';
					break;
				case 'all':
					$sql .= ' ORDER BY datePaid ASC';
					break;

			}
			$stmt = $dbConn->prepareStatement($sql);
			$result = $stmt->execute();

			if($result instanceof Resultset && $result->hasRows()) {
				return $result->getResultsetAsArray();
			}
			return array();


		}

		public function save() {

			return $this->saveModel();

		}

	}

?>