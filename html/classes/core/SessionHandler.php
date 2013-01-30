<?php

	/**
	 * This class overrides PHP's built in session handling functionality to write session data to a database
	 *
	 *  @author Matt Rink <matt.rink@groovytrain.com>
	 */
	class SessionHandler {

		private $dbConn;

		/**
		 * Constructor for the SessionHandler class.
		 */
		public function __construct(){

			$this->dbConn = DatabaseConnection::getConnection();

			session_set_save_handler(
				array($this,"open"),
				array($this,"close"),
				array($this,"read"),
				array($this,"write"),
				array($this,"destroy"),
				array($this,"gc")
			);

    	}

    	/**
		* Returns true to indicate session can be written to
		*
		* @return Boolean
		*/
		public function open() {
			return true;
		}

		/**
		 * Executes gc() to remove expired sessions and returns true to indicate session is closed
		 * It doesn't execute gc() at the moment because that adds alot to the database load.
		 *
		 * @return Boolean
		 */
		public function close() {

			//$this->gc(get_cfg_var("session.gc_maxlifetime"));
			//$this->dbConn->close();
			return true;

		}

		/**
		 * Reads the session data for $sessionID from the database
		 *
		 * @param String $sessionID
		 * @return String
		 */
		public function read($sessionID) {
			// $result = $this->dbConn->executeStoredProcedure('spsSessionBySessionID', array($sessionID));
			$stmt = $this->dbConn->prepareStatement('SELECT * FROM core_session WHERE sessionID = :sessionID LIMIT 1;');
			$stmt->bindParameter('sessionID', $sessionID);

			try {
				$result = $stmt->execute();
			} catch(Exception $e) {
				//Logger::log("Statement execution faild: SELECT * FROM tblSession WHERE sessionID = ".$sessionID." LIMIT 1 - Failed - Returning empty string!");
				return "";
			}

			if (!$result->hasRows()) {
				return "";
			} else {
				$row = $result->nextRow();
				if (isset($row['data'])) {
					return $row['data'];
				} else {
					return "";
				}
			}

		}

		/**
		 * Writes the current session data to the database
		 *
		 * @param String $sessionID
		 * @param String $data
		 * @return Boolean
		 */
		public function write($sessionID, $data) {

			$stmt = $this->dbConn->prepareStatement("INSERT INTO core_session SET siteID = :siteID,
					sessionID = :sessionID, data = :data, lastAccess = NOW()
						ON DUPLICATE KEY UPDATE data = :data, lastAccess = NOW();");
			$stmt->bindParameter('siteID', $GLOBALS['site']->siteID);
			$stmt->bindParameter('sessionID', $sessionID);
			$stmt->bindParameter('data', $data);
			try {
				$result = $stmt->execute();
			} catch(Exception $e) {
				return true;
			}
			$stmt->execute();

			return true;

		}

		/**
		 * Destroys the session data identified by $sessionID
		 *
		 * @param String $sessionID
		 * @return Boolean
		 */
		public function destroy($sessionID) {

			//$this->dbConn->executeStoredProcedure('spdSession', array($sessionID));

			$stmt = $this->dbConn->prepareStatement("DELETE FROM core_session WHERE sessionID = :sessionID;");
			$stmt->bindParameter('sessionID', $sessionID);
			try {
				$result = $stmt->execute();
			} catch(Exception $e) {
				return true;
			}
			return true;

		}

		/**
		 * Removes any sessions that haven't been accessed in $maxLifeTime seconds
		 *
		 * @param Integer $maxLifeTime		-- now ignored
		 * @return Boolean
		 */
		public function gc($maxLifeTime) {
			// we used a scheduled task to accomplish this
			return true;
		}

	}

?>
