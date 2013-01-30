<?php
	class Ndoorse_Notification {

		private $settings = array();

		private $types = array('message', 'recommend', 'job', 'event');

		const ACCESS_NONE = 0;
		const ACCESS_NETWORK = 1;
		const ACCESS_MEMBER = 2;
		const ACCESS_RECRUITER = 4;
		const ACCESS_ADMIN = 8;


		public static function getSettingsForUser($userID) {

			$dbConn = DatabaseConnection::getConnection();

			$stmt = $dbConn->prepareStatement('SELECT * FROM ndoorse_member_notification WHERE userID = :userID');
			$stmt->bindParameter('userID', $userID);
			$result = $stmt->execute();

			$notification = new Ndoorse_Notification();

			if($result instanceof Resultset && $result->hasRows()) {
				while($row = $result->nextRow()) {
					$notification->settings[$row['type']] = new Ndoorse_Member_Notification($row);
				}
			}

			return $notification;

		}

		public function checkType($contactType, $permission) {

			if(!array_key_exists($contactType, $this->settings)) {
				return true;
			}

			if($this->settings[$contactType]->hasPermission($permission)) {
				return true;
			}

			return false;

		}

		public function update($args) {

			foreach($this->types as $type) {
				if(!isset($this->settings[$type])) {
					$this->settings[$type] = new Ndoorse_Member_Notification(array('userID'=>$_SESSION['user']->getID(), 'type'=>$type, 'permissions'=>self::ACCESS_NONE));
				}

				if(isset($args['contact_network_' . $type])) {
					$this->settings[$type]->addPermission(self::ACCESS_NETWORK);
				} else {
					$this->settings[$type]->removePermission(self::ACCESS_NETWORK);
				}

				if(isset($args['contact_member_' . $type])) {
					$this->settings[$type]->addPermission(self::ACCESS_MEMBER);
				} else {
					$this->settings[$type]->removePermission(self::ACCESS_MEMBER);
				}

				if(isset($args['contact_recruiter_' . $type])) {
					$this->settings[$type]->addPermission(self::ACCESS_RECRUITER);
				} else {
					$this->settings[$type]->removePermission(self::ACCESS_RECRUITER);
				}

				if(isset($args['contact_admin_' . $type])) {
					$this->settings[$type]->addPermission(self::ACCESS_ADMIN);
				} else {
					$this->settings[$type]->removePermission(self::ACCESS_ADMIN);
				}

			}

			$this->save();

		}

		public function save() {

			$dbConn = DatabaseConnection::getConnection();

			$sql = 'REPLACE INTO ndoorse_member_notification (userID, `type`, permissions) VALUES ';

			$rows = array();
			$vals = array();

			$vals['userID'] = $_SESSION['user']->getID(); // may want to change this if editing other people's
			foreach($this->types as $type) {
				$rows[] = '(:userID, :type_' . $type . ', :pems_' . $type . ')';
				$vals['type_' . $type] = $type;
				$vals['pems_' . $type] = $this->settings[$type]->permissions;
			}

			$sql .= implode(', ', $rows);
			$stmt = $dbConn->prepareStatement($sql);

			foreach($vals as $key=>$val) {
				$stmt->bindParameter($key, $val);
			}

			return $stmt->execute();

		}

	}

	class Ndoorse_Member_Notification extends Model {

		protected $userID;
		protected $type;

		protected $permissions;

		protected $message;	// not currently used
		protected $email;	// not currently used

		public function addPermission($permission) {

			if(!$this->hasPermission($permission)) {
				$this->permissions += $permission;
			}

		}

		public function removePermission($permission) {

			if($this->hasPermission($permission)) {
				$this->permissions -= $permission;
			}

		}

		public function hasPermission($permission) {
			if(($this->permissions & $permission) == $permission) {
				return true;
			} else {
				return false;
			}

		}

	}

?>