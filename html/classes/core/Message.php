<?php
	/* Antelope Messaging
	 * Basic messaging class, supporting one-to-many messaging, and replies.
	 */
	class Message extends Model {

		protected $messageID;	// unique message ID
		protected $replyToID;	// message ID this message is a reply to
		protected $senderID;	// user ID of sender

		protected $subject;		// message subject
		protected $message;		// message content
		protected $dateSent;	// date message was sent

		/* user specific */
		protected $userID;		// user ID of recipient
		protected $dateRead;	// date recipient read message, null if unread
		protected $deleted;		// deleted flag


		/**
		 * Return number of unread messages for the currently logged-in user
		 * @return int
		 */
		public static function getMessageCount($type = 'inbox') {

			$dbConn = DatabaseConnection::getConnection();

			// return all messages for current user where they've not been read and not been deleted


			$sql = 'SELECT COUNT(*) AS message_count
						FROM ' . static::$table . '_user ';

			switch($type) {
				case 'new':
					$sql .= 'WHERE userID = :userID
							AND dateRead IS NULL
							AND deleted = 0 ';
					break;
				case 'sent':
					$sql .= 'INNER JOIN ' . static::$table . ' USING(messageID)
						WHERE senderID = :userID
						AND deleted = 0 ';
					break;
				case 'deleted':
					$sql .= 'WHERE userID = :userID
						AND deleted = 1 ';
					break;
				default:
					$sql .= 'WHERE userID = :userID
						AND deleted = 0 ';
			}

			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('userID', $_SESSION['user']->getID());
			$result = $stmt->execute();
			if($result instanceof Resultset && $result->hasRows()) {
				$row = $result->nextRow();
				return $row['message_count'];
			}

			// if nothing was returned, they have no new messages
			return 0;

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
		public static function getMessages($page = 0, $pagination = 20, $type = 'inbox') {

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

			$sql = 'SELECT mm.messageID, u.username, subject, dateSent, dateRead
					FROM ' . static::$table . ' mm
						INNER JOIN ' . static::$table . '_user mu
							USING(messageID)
						INNER JOIN core_user u';
			if($sent) {
				$sql .= '	ON(mu.userID = u.userID)';
			} else {
				$sql .= '	ON(mm.senderID = u.senderID)';
			}
			$sql .=	'WHERE';
			if($sent) {
				$sql .= '	senderID = :userID';
			} else {
				$sql .= '	userID = :userID';
			}
			$sql .=  '  AND deleted = :deleted
						ORDER BY dateSent DESC';
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
					$output[] = new Message($row);
				}
			}

			return $output;

		}

		public static function getRecipientsFromList($recipients) {

			if(!is_array($recipients)) {
				$recipients = explode(',', $recipients);
			}
			return $recipients;

		}

		public function getFormattedDateSent() {
			return date('d/m/Y H:i', strtotime($this->dateSent));
		}

		public function getFormattedDateRead() {
			return date('d/m/Y H:i', strtotime($this->dateRead));
		}

		public function loadTemplate($template, $data = array()) {

			if(!file_exists(SITE_PATH . 'messages/' . $template . '.html')) {
				throw new Exception('Message/loadTemplate: ' . $template . ' does not exist.');
			}

			$message = file_get_contents(SITE_PATH . 'messages/' . $template . '.html');
			foreach($data as $key=>$val) {
				$message = str_replace('%' . $key . '%', $val, $message);
			}

			$this->message = $message;

		}


		/**
		 * Send message to one or more recipients.
		 *
		 * @param Array $recipients		userIDs of receipients, single element array for one recipient
		 * @throws Exception
		 */
		public function send($recipients, $fields = array()) {

			// check that some recipients have been specified
			if(empty($recipients) || !is_array($recipients)) {
				throw new Exception('Message/Send: Empty recipient list');
			}
			// check that the message and subject are not blank
			if(empty($this->subject) || empty($this->message)) {
				throw new Exception('Message/Send: Empty message or subject');
			}

			// save the message, and associate it with each person in the list
			$dbConn = DatabaseConnection::getConnection();

			$fields = array_merge($fields, array('replyToID', 'senderID', 'subject', 'message')); // generic fields to write, dateSent will be populated automatically

			$sql = 'INSERT INTO ' . static::$table . ' SET ';
			foreach($fields as $key) {
				if($key == 'senderID' || !empty($this->$key)) {
					$sql .= $key . ' = :' . $key . ', ';
				}
			}
			$sql = substr($sql, 0, strlen($sql) - 2);

			$stmt = $dbConn->prepareStatement($sql);
			foreach($fields as $key) {
				if($key == 'senderID') { // senderID is current user
					$stmt->bindParameter($key, $_SESSION['user']->getID());
				} else if(!empty($this->$key)) {
					$stmt->bindParameter($key, $this->$key);
				}
			}

			if($stmt->execute()) {
				$messageID = $dbConn->getLastID();
			} else {
				throw new Exception('Message/Send: Could not create message');
			}

			$recipientcount = count($recipients);

			// associate the message with all intended recipients
			$sql = 'INSERT INTO ' . static::$table . '_user (messageID, userID) VALUES ';
			$ids = array();
			for($i=0;$i<$recipientcount;++$i) {
				$ids[] = '(:messageID, :id' . $i . ')';
			}
			$sql .= implode(', ', $ids);
			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('messageID', $messageID);
			for($i=0;$i<$recipientcount;++$i) {
				$stmt->bindParameter('id' . $i, $recipients[$i]);
			}

			if(!$stmt->execute()) {
				throw new Exception('Message/Send: Could not associate message with recipients (' . implode(',', $recipients));
			}
			return true;

		}

		public function read() {

			if(!$this->getID()) {
				throw new Exception('Message/Read: No current message');
			}

			if(is_null($this->dateRead)) {
				$dbConn = DatabaseConnection::getConnection();
				$stmt = $dbConn->prepareStatement('UPDATE ' . static::$table . '_user SET dateRead = NOW() WHERE messageID = :messageID AND userID = :userID');
				$stmt->bindParameter('messageID', $this->getID());
				$stmt->bindParameter('userID', $_SESSION['user']->getID());
				return $stmt->execute();
			}
		}


	}
?>