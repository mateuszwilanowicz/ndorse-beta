<?php
	class Ndoorse_Message extends Message {

		protected $senderName;		// firstname-lastname of sender
		protected $username;		// firstname-lastname of recipient
		protected $type;			// message type (see constants below)
		protected $data;			// additional data field, usually takes an ID depending on message type
									// this is used to allow custom actions based on request/job/etc

		/*
			To allow the generic methods to work, this is the name (and prefix) of the tables used for messages.
			In this case they are ndoorse_message and ndoorse_message_user
		*/
		public static $table = 'ndoorse_message';

		const TYPE_NORMAL = 0;
		const TYPE_CONTACT_REQUEST = 1;
		const TYPE_JOB_RESPONSE = 2;
		const TYPE_REQUEST_RESPONSE = 3;
		const TYPE_JOB_RECOMMENDATION = 4;
		const TYPE_REQUEST_RECOMMENDATION = 5;
		const TYPE_PROFILE_SHARE = 6;
		const TYPE_INVITE = 7;
        const TYPE_RECRUITER_CONTACT = 8;
        const TYPE_SERVICEPROVIDER_INVITE = 9;

		/**
		 * Special case of constructor, where we can load a message instance for a specific recipient
		 * @param mixed $params
		 * @param int $userID
		 */
		public function __construct($params = null, $userID = null) {

			if(is_null($userID)) {
				return parent::__construct($params);
			} else {
				// if we've been passed a userID, then try to load the message to the specified user
				if(is_numeric($userID)) {
					$this->loadByID($params, $userID);
				}
			}

		}

		/**
		 * Special case of loadByID, to allow loading for a specific userID
		 * @param int $id
		 * @param int $userID
		 * @throws Exception
		 * @return boolean success
		 */
		public function loadByID($id, $userID = null) {

			if(empty($id)) {
				throw new Exception('Ndoorse/Message/loadByID: Invalid ID');
			}

			$userID = is_null($userID) ? $_SESSION['user']->getID() : $userID;

			/*
				Load the message, as well as the name/userIDs of the sending and receiving users
			 */
			$dbConn = DatabaseConnection::getConnection();
			$sql = 'SELECT m.*, CONCAT(u.firstname, " ", u.lastname) AS username, CONCAT(s.firstname, " ", s.lastname) AS senderName, mu.userID, mu.dateRead
					FROM ndoorse_message m
						INNER JOIN ndoorse_message_user mu
							USING(messageID)
						LEFT OUTER JOIN ndoorse_member u
							ON(u.userID = mu.userID)
						LEFT OUTER JOIN ndoorse_member s
							ON(s.userID = m.senderID)
					WHERE m.messageID = :messageID
						AND mu.userID = :userID';

			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('userID', $userID);
			$stmt->bindParameter('messageID', $id);
			$result = $stmt->execute();

			if($result instanceof Resultset && $result->hasRows()) {
				$this->loadFromArray($result->nextRow());
				return true;
			}

			return false;

		}

		/**
		 * Return list of messages for the current user
		 *
		 * Specify pagination parameters to paginate results
		 * @param int $page			page number, leave at 0 to return all results
		 * @param int $pagination 	page size
		 *
		 * @param String $type 		Specify the type parameter to choose the type of messages returned:
		 * 				inbox		messages received by the user
		 *  			deleted		messages received and deleted by the user
		 *  			sent		messages sent by the user
		 *
		 * @return Array			Return array of Message objects, else empty array
		 */
		public static function getMessages($page = 0, $pagination = 20, $type = 'inbox', $orderby = 'dateSent', $dir = 'asc') {

			$dbConn = DatabaseConnection::getConnection();

			$deleted = 0;
			$sent = 0;

			switch($type) {
				case 'deleted':
					$deleted = 1;
					break;
				case 'sent':
					$sent = 1;
					break;
			}

			$dir = $dir == 'desc' ? ' desc' : ' asc';

			$sql = 'SELECT mm.messageID, CONCAT(u.firstname, " ", u.lastname) AS senderName, ';
			$sql .= '	mu.userID AS userID,
						mm.senderID AS senderID,
						subject, dateSent, dateRead
					FROM ndoorse_message mm
						INNER JOIN ndoorse_message_user mu ';
			if($sent) {
				$sql .= ' ON(mm.messageID = mu.messageID) ';
			} else {
				$sql .= ' ON(mm.messageID = mu.messageID AND mu.userID = :userID) ';
			}

			$sql .= '		INNER JOIN ndoorse_member u';
			if($sent) {
				$sql .= '	ON(mu.userID = u.userID) ';
			} else {
				$sql .= '	ON(mm.senderID = u.userID) ';
			}
			$sql .=	'WHERE';
 			if($sent) {
 				$sql .= '	mm.senderID = :userID';
 			} else {
 				$sql .= '	mu.userID = :userID';
 			}
			$sql .=  ' AND deleted = :deleted ';



			switch($orderby) {
				case 'senderName':
					$sql .= ' ORDER BY ' . ($sent ? 'senderName' : 'username') . $dir;
					break;
				case 'dateSent':
				case 'subject':
					$sql .= ' ORDER BY ' . $orderby . $dir;
					break;
				default:
					$sql .= ' ORDER BY dateSent' . $dir;
			}

			if($page > 0) {
				$start = $pagination * ($page - 1);
				$sql .= " LIMIT $start, $pagination";
			}

			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('userID', $_SESSION['user']->getID());
			$stmt->bindParameter('deleted', $deleted);

			$result = $stmt->execute();

			$output = array();
			if($result instanceof Resultset && $result->hasRows()) {
				while($row = $result->nextRow()) {
					$output[] = new Ndoorse_Message($row);
				}
			}

			return $output;

		}

		/**
		 * Return array of names, based on array of userIDs.
		 * This should probably be in the Ndoorse_User class
		 * @param Array $recipients
		 * @throws Exception
		 * @return Array userID=>name
		 */
		public static function getRecipientNames($recipients) {

			if(!is_array($recipients)) {
				throw new Exception('Ndoorse/Message/getRecipientNames: No recipients');
			}

			$dbConn = DatabaseConnection::getConnection();

			$sql = 'SELECT userID, CONCAT(firstname, " ", lastname) AS name FROM ndoorse_member WHERE userID IN (' . implode(',', $recipients) . ')';;
			$stmt = $dbConn->prepareStatement($sql);
			$result = $stmt->execute();

			$output = array();
			if($result instanceof Resultset && $result->hasRows()) {
				while($row = $result->nextRow()) {
					$output[$row['userID']] = $row['name'];
				}
			}
			return $output;

		}

		/**
		 * Delete this message (sets deleted flag to 1, does not physically remove it)
		 * @return boolean
		 */
		public function delete() {

			if($_SESSION['user']->getID() != $this->userID) {
				return false;
			}

			$this->deleted = 1;

			$dbConn = DatabaseConnection::getConnection();
			$stmt = $dbConn->prepareStatement('UPDATE ' . static::$table . '_user SET deleted = 1 WHERE messageID = :messageID AND userID = :userID');
			$stmt->bindParameter('userID', $_SESSION['user']->getID());
			$stmt->bindParameter('messageID', $this->getID());

			return $stmt->execute();

		}

		public function send($recipients, $ignore = array()) {

			return parent::send($recipients, array('type', 'data'));

		}

	}
?>
