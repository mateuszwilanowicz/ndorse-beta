<?php
	class User extends Model {

		protected $userID;

		protected $email;

		protected $username;
		protected $password;

		protected $level;
		protected $status;

		const STATUS_INACTIVE = 0;
		const STATUS_PENDING = 1;
		const STATUS_ACTIVE = 2;

		/*
		 * core_user format:
		 */
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
				$stmt = $dbConn->prepareStatement('SELECT * FROM ' . strtolower($package) . '_' . strtolower($className) . ' WHERE username = :username LIMIT 1');
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

		public static function logout() {

			unset($_SESSION['user']);
			session_destroy();

		}

		/*
		 * core_activation format:
		 * key, userID, type, date
		 */
		public static function loadActivation($key) {

			if(empty($key)) {
				throw new Exception('User/LoadActivation: No key specified.');
			}

			$dbConn = DatabaseConnection::getConnection();

			$stmt = $dbConn->prepareStatement('SELECT * FROM core_activation WHERE `key` = :key');
			$stmt->bindParameter('key', $key);
			$result = $stmt->execute();

			if($result instanceof Resultset && $result->hasRows()) {
				$row = $result->nextRow();

				//temporrary fix!!! need to dynamicly detect the class
				//$user = new User($row['userID']);
				$class = get_called_class();
				$user = new $class($row['userID']);
				if(!$user->getID()) {
					Logger::log('error', 'User for activation key: ' . $key . ' not found.');
					return false;
				}

				if($row['type'] == 'activate') {
					$user->status = self::STATUS_ACTIVE;
					$user->saveModel(array(), 'userID');
				}

				return $user;
			}

		}

		public static function deleteActivation($key) {
			if(empty($key)) {
				throw new Exception('User/LoadActivation: No key specified.');
			}

			$dbConn = DatabaseConnection::getConnection();

			$stmt = $dbConn->prepareStatement('SELECT * FROM core_activation WHERE `key` = :key');
			$stmt->bindParameter('key', $key);
			$result = $stmt->execute();

			if($result instanceof Resultset && $result->hasRows()) {

				$stmt = $dbConn->prepareStatement('DELETE from core_activation WHERE `key` = :key');
				$stmt->bindParameter('key', $key);
				if(!$stmt->execute()) {
					Logger::log('warning', 'Could not delete activation key: ' . $key);
					return false;
				} else {
					return true;
				}
			}
		}

		public static function generateKey($length = 25) {
			if(false && function_exists('openssl_random_pseudo_bytes')) {
				$length = 25;
				$strong = TRUE;
		        $password = base64_encode(openssl_random_pseudo_bytes($length, $strong));
		        if($strong == TRUE)
		            return substr($password, 0, $length); //base64 is about 33% longer, so we need to truncate the result
		    }

		    # fallback to mt_rand if php < 5.3 or no openssl available
		    $characters = '0123456789';
		    $characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		    $charactersLength = strlen($characters)-1;
		    $password = '';

		    # select some random characters
		    for ($i = 0; $i < $length; $i++) {
		        $password .= $characters[mt_rand(0, $charactersLength)];
		    }

		    return $password;
		}


		public function createActivation($type, $attempt = 1) {

			$key = self::generateKey();

   			$dbConn = DatabaseConnection::getConnection();
			$stmt = $dbConn->prepareStatement('INSERT INTO core_activation SET userID = :userID, type = :type, `key` = :key');
			$stmt->bindParameter('userID', $this->userID);
			$stmt->bindParameter('type', $type);
			$stmt->bindParameter('key', $key);
			if($stmt->execute()) {
				return $key;
			} else {
				// if insert fails, it could be due to a duplicate key - try up to 5 times
				++$attempt;
				if($attempt > 5) {
					throw new Exception('User/CreateActivation: cannot create activation code');
				}
				return $this->createActivation($type, $attempt);
			}

		}

		protected function getPasswordHash($password) {

			if(strlen($password) > 72) {
				Logger::log('error', 'Password too long');
				return false;
			}

			$hasher = new PasswordHash(PASSWORD_PASSES, PASSWORD_PORTABLE);

			$hash = $hasher->HashPassword($password);
			if(strlen($hash) < 20) {
				Logger::log('error', 'Hash too short');
				return false;
			}

			return $hash;

		}

		public function setPassword($password) {

			if(!$this->getID()) {
				throw new Exception('User/SetPassword: no user.');
			}

			$this->password = $this->getPasswordHash($password);

			$dbConn = DatabaseConnection::getConnection();
			$stmt = $dbConn->prepareStatement('UPDATE core_user SET password = :hash WHERE userID = :userID');
			$stmt->bindParameter('hash', $this->password);
			$stmt->bindParameter('userID', $this->getID());
			return $stmt->execute();

		}


		/* access control */

		/*
		 * isLevel checks whether the current user has the specified level
		 * @return bool
		 */
		public function isLevel($level) {

			return $this->level >= $level;

		}

		/*
		 * checkLevel checks the level using isLevel() and redirects if the current user does not have this access
		 */
		public function checkLevel($level, $redirect = '') {

			if(!isLevel($level)) {
				$_SESSION['page_errors'][] = 'You do not have permission to access this page.';
				redirect(SITE_URL . $redirect);
			}

		}


	}
?>