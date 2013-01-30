<?php
	class MembersController extends Controller {

		public function __construct() {
			  switch($_SESSION['user']->level) {
				case Ndoorse_Member::LEVEL_RECRUITER:
					define('RECRUITERS_URL', BASE_URL . 'recruiters/');
					parent::__construct(SITE_NAME . ' Recruiters',  DEFAULT_PATH . 'templates/recruiter.php');
					break;
				default:
					parent::__construct(SITE_NAME . ' Members', defined('SITE_TEMPLATE') ? SITE_TEMPLATE : DEFAULT_TEMPLATE);
					break;
			}
			$this->page->addStylesheet(SITE_URL . 'styles/design.css');
			$this->page->addStylesheet(SITE_URL . 'styles/members.css');
		}

		public function index($args, $fieldErrors = array()) {

			$this->loggedIn();

			if(!isset($fieldErrors)) $fieldErrors = array();

			$connections = $_SESSION['user']->getConnections();
			$requests = array();
			$current = array();
			$pending = array();

			if($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($fieldErrors)) {

				$inviteFormRules = InviteControl::$inviteFormRules;
				$fieldErrors = FormValidator::validate($args, $inviteFormRules);
			}

			foreach($connections as $connection) {
				if($connection['connectionStatus'] == 0 && $connection['respondentID'] == $_SESSION['user']->userID ) {
					$requests[$connection['requesterID']] = $connection;
				} else if($connection['connectionStatus'] == 1 && ( $connection['respondentID'] == $_SESSION['user']->userID || $connection['requesterID'] == $_SESSION['user']->userID ) ) {
					$current[($connection['requesterID'] != $_SESSION['user']->getID() ? $connection['requesterID'] : $connection['respondentID'])] = $connection;
				} else if($connection['connectionStatus'] == 0 && $connection['requesterID'] == $_SESSION['user']->userID ) {
					$pending[$connection['respondentID']] = $connection;
				}
			}

			$this->page->startBlock('main');

			include SITE_PATH . 'layouts/members/members.php';
			$this->page->endBlock('main');
			$this->page->render();

		}

		public function autocomplete($args) {

			if(!isset($args['name'])) {
				die('{}');
			}

			$names = Ndoorse_Member::autocomplete($args['name']);
			$output = '';
			foreach($names as $name) {
				$output .= '<a href="#' . $name['userID'] . '">' . $name['name'] . '</a>';
			}

			die($output);

		}

		public function autocompleteNew($args) {

			if(!isset($args['name'])) {
				die('{}');
			}

			$names = Ndoorse_Member::autocompleteNew($args['name']);
			$output = '';
			foreach($names as $name) {
				$output .= '<a href="#' . $name['userID'] . '">' . $name['name'] . '</a>';
			}

			die($output);

		}

		public function profile($args) { // ,$showUserID = 0 ?????

			$this->loggedIn();
			$fieldErrors = array();

			if(!isset($args[1]) || !is_numeric($args[1])) {
				$user = $_SESSION['user'];
				$userID = $user->userID;
			} else {
				$userID = $args[1];
				$user = new Ndoorse_Member($userID);
				if(!$user->getID()) {
					$_SESSION['page_errors'][] = 'Cannot find the member you requested';
					redirect(BASE_URL);
				}
			}

			$userProfile = $user; // remove this when completely changed over

            $profileFormRules = MembersProfileControl::$profileFormRules;
            $fieldErrors = FormValidator::validate($args, $profileFormRules);
			$allServiceProviders = Ndoorse_Member::getAllServiceProviders($userProfile->userID);
            //pr($allServiceProviders);
			$experience = Ndoorse_Member::getExperience($userID);
			$education = Ndoorse_Member::getEducation($userID);

			$countries = Country::getCountries(true);

			$avatar = Ndoorse_Document::getDocuments('avatar', $userID);
			if(is_array($avatar) && !empty($avatar)) {
				$avatar = reset($avatar);
			}

			if($avatar instanceof Ndoorse_Document) {
				$avatar = basename($avatar->filePath);
			} else {
				$avatar = '';
			}

		   	$notifications = Ndoorse_Notification::getSettingsForUser($_SESSION['user']->getID());

			$dbskills = array();
			$skills = array();
			$stmt = '';
			if($_SERVER['REQUEST_METHOD'] == 'POST') {


				$userProfile->locationID = Ndoorse_Location::saveFromPost($args['location'], $args['locationID']);

				if(!empty($fieldErrors)) {
					$experience = Ndoorse_Member::getExperience($userID);
					$education = Ndoorse_Member::getEducation($userID);
					$this->page->startBlock('main');
					include SITE_PATH . 'layouts/members/profile.php';
					$this->page->endBlock('main');
					$this->page->render();

				} else {
					$userProfile->firstname = $args['firstname'];
					$userProfile->lastname = $args['lastname'];
					$userProfile->address1 = $args['address1'];
					$userProfile->address2 = $args['address2'];
					$userProfile->region = $args['region'];
					$userProfile->postcode = $args['postcode'];
					$userProfile->country = $args['country'];
					$userProfile->telhome = $args['telhome'];
					$userProfile->telwork = $args['telwork'];
					$userProfile->telmobile = $args['telmobile'];
					$userProfile->jobstatus = $args['jobstatus'];
					$userProfile->email = $args['email'];
					$userProfile->locationID = $args['locationID'];
					$userProfile->location = $args['location'];
					$userProfile->save();

					list($success, $output) = Ndoorse_Document::upload('avatar');
					if($success || $output == '') {
						$userProfile->avatar = $output;
						$userProfile->save();
					}

					$notifications->update($args);

					$dbConn = DatabaseConnection::getConnection();

					if(isset($args['skills'])) {
						$skills =  explode(',',$args['skills']);
						foreach($skills as &$s) {
							//strip non alpha numeric
							$s = trim($s);
							$s = '"'.$s.'"';
						}
						$skillString = implode(',',$skills);


						$stmt = $dbConn->prepareStatement('SELECT * FROM ndoorse_skill WHERE name IN ('.$skillString.')');
						$result = $stmt->execute();

						if($result instanceof Resultset) {
							while($row = $result->nextRow()) {
								$dbskills[] = $row;
							}
						}

						$skills =  explode(',',$args['skills']);

						foreach($dbskills as $s) {
							$key = array_search($s['name'], $skills);
							unset($skills[$key]);
						}
						if(count($skills) > 0) {
							foreach($skills as $s) {
								$newSkill = new Ndoorse_Skill();
								$newSkill->name = $s;
								$newSkill->saveModel();
								$id = $newSkill->getID();
								$dbskills[] = array('skillID'=>$id,'name'=>$s);
							}
						}
					}

					if(isset($args['education_id']) && strlen($args['year']) > 3 && strlen($args['institution']) > 3) {

						$educationToSave = new Ndoorse_Education();
						$educationToSave->educationID = $args['education_id'];
						$educationToSave->userID = $_SESSION['user']->userID;
						$educationToSave->institution = $args['institution'];
						$educationToSave->courseName = $args['courseName'];
						$educationToSave->description = $args['description'];
						$educationToSave->year = $args['year'];
						$educationToSave->startDate = $args['startDate'];
						$educationToSave->endDate = $args['endDate'];
						$educationToSave->saveModel();

						$id = $educationToSave->getID();

						if(!empty($args['education_id'])) {
							$id = $args['education_id'];
						} else {
							$id = $educationToSave->educationID;
						}

						$dbConn = DatabaseConnection::getConnection();
						$stmt = $dbConn->prepareStatement('DELETE FROM ndoorse_memberskill WHERE entityID = :id AND entity = "education"');
						$stmt->bindParameter('id', $id);
						$result = $stmt->execute();

						foreach($dbskills as $s) {

							$memberSkills = new Ndoorse_MemberSkill();
							$memberSkills->skillID = $s['skillID'];
							$memberSkills->entity = 'education';
							$memberSkills->entityID = $id;
							$memberSkills->saveModel();

						}
					} elseif(isset($args['experience_id']) && strlen($args['year']) > 3 && strlen($args['jobTitle']) > 3) {
						$experienceToSave = new Ndoorse_Experience();
						$experienceToSave->experienceID = $args['experience_id'];
						$experienceToSave->userID = $_SESSION['user']->userID;
						$experienceToSave->jobTitle = $args['jobTitle'];
						$experienceToSave->companyName = $args['companyName'];
						$experienceToSave->description = $args['description'];
						$experienceToSave->year = $args['year'];
						$experienceToSave->duration = $args['duration'];
						$experienceToSave->startDate = $args['startDate'];
						$experienceToSave->endDate = $args['endDate'];

						$experienceToSave->saveModel();
						$id = $experienceToSave->getID();

						if(!empty($args['experience_id'])) {
							$id = $args['experience_id'];
						}

						$dbConn = DatabaseConnection::getConnection();
						$stmt = $dbConn->prepareStatement('DELETE FROM ndoorse_memberskill WHERE entityID = :id AND entity = "experience"');
						$stmt->bindParameter('id', $id);
						$result = $stmt->execute();

						foreach($dbskills as $s) {

							$memberSkills = new Ndoorse_MemberSkill();
							$memberSkills->skillID = $s['skillID'];
							$memberSkills->entity = 'experience';
							$memberSkills->entityID = $id;
							$memberSkills->saveModel();

						}

						$allServiceProviders = Ndoorse_Member::getAllServiceProviders($userProfile->userID);
						$experience = Ndoorse_Member::getExperience($userID);
						$education = Ndoorse_Member::getEducation($userID);

					} elseif(!empty($args['serviceproviderID'])) {
						$result = Ndoorse_Member::addServiceProvider($args['serviceproviderID'],$args['position']);
						if(!$result) {
							$_SESSION['page_error'][] = 'Could not add this service provider.';
						}


					} elseif(isset($args['serviceprovider'])) {
						redirect(BASE_URL . 'serviceproviders/post/new/' . $args['serviceprovider']);
					}

					$_SESSION['page_messages'][] = 'Profile updated';
				}

			}



			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/members/profile.php';
			$this->page->endBlock('main');
			$this->page->render();

		}

		public function ignore($args) {
			//pr($args,false);
			if(is_numeric($args[1])) {
				$connection = Ndoorse_Connection::getConnectionByID($_SESSION['user']->getID(), $args[1]);
				$connection->connectionStatus = Ndoorse_Connection::STATUS_DENIED;
				$connection->saveConnection();
			}
			redirect(BASE_URL . "members");
		}

		public function confirm($args) {
			//pr($args,false);
			if(is_numeric($args[1])) {
				$connection = Ndoorse_Connection::getConnectionByID($_SESSION['user']->getID(), $args[1]);
				$connection->connectionStatus = Ndoorse_Connection::STATUS_ACCEPTED;
				$connection->saveConnection();
			}
			redirect(BASE_URL . "members");
		}

		public function invite($args) {
			if($this->loggedIn()) {

				$inviteFormRules = InviteControl::$inviteFormRules;
				$fieldErrors = FormValidator::validate($args, $inviteFormRules);

				if($_SERVER['REQUEST_METHOD'] == 'POST' && count($fieldErrors) < 1) {
					if(!Ndoorse_Member::userExist($args['email'])) {
						$phantomUser = new Ndoorse_Member();
						$phantomUser->username = $args['email'];
						$phantomUser->email = $args['email'];
						$phantomUser->referrerID = $_SESSION['user']->getID();
						$phantomUser->save();
						$newID = $phantomUser->getID();
						$activationCode = $phantomUser->createActivation('activate');

						$message = new EmailMessage();
						$message->setSubject($_SESSION['user']->getName() . ' invited you to join Ndoorse Network');

						$params = array('name'=>$_SESSION['user']->getName(),
										'url'=>BASE_URL,
										'title'=>$_SESSION['user']->getName() . ' invited you to join Ndoorse Network',
										'reffererID'=>$_SESSION['user']->getID(),
										'activationCode'=>$activationCode,
										'activationUrl'=>BASE_URL . 'members/activate/?key=' . $activationCode
										);

						$message->loadTemplate('ndoorse_invitation', $params);
						$message->setSenderEmailAddress(EMAIL_FROM, EMAIL_FROM_NAME);
						$message->addRecipientEmailAddress($phantomUser->email);
						$result = $message->send();

						$_SESSION['page_messages'][] = 'Your invitation has been sent';

						redirect(BASE_URL . "members");

					} else {
						// user allready registrated do you want to send a connection invite instead?
						$fieldErrors['email'] = "A member with this email address is already registered, please connect instead.";
						$this->index($args,$fieldErrors);
					}
				} else {
					$this->index($args,$fieldErrors);
				}
			}
		}

		public function activate($args) {
			//pr($args,false);
			if(isset($args['key'])) {
				$user = Ndoorse_Member::loadActivation($args['key']);
				if($user instanceof Ndoorse_Member ) {
					$_SESSION['user'] = $user;
					$_SESSION['key'] = $args['key'];
					$_SESSION['email'] = $user->email;
					redirect(BASE_URL . "signup");
				} else {
					$_SESSION['page_errors'][] = 'Your activation key was not found.';
					redirect(BASE_URL);
				}
			}
		}

		public function connect($args) {
			if($this->loggedIn()) {
				$user = $_SESSION['user'];
				if($_SERVER['REQUEST_METHOD'] == 'POST') {
					if($args['userID']) {
						$connectTo = $args['userID'];
						$user->connect($connectTo);
					} else {
						$connectTo = Ndoorse_Member::getUserIDByName($args['respondent']);
						if(is_numeric($connectTo)) {
							$user->connect($connectTo);
						} else {
							redirect(BASE_URL . "members");
						}
					}
				}
			}
		}

		public function upgrade($args) {

			if(isset($args[1])) {
				$level = new Ndoorse_Level($args[1]);
				if($level->getID()) {
					if(isset($_SESSION['paymentArgs'])) {
						$paymentForm = PaymentFormControl::render($_SESSION['paymentArgs']);
						unset($_SESSION['paymentArgs']);
					} else {
						$paymentForm = PaymentFormControl::render($args);
					}

					$this->page->startBlock('main');
					include SITE_PATH . 'layouts/members/upgrade_payment.php';
					$this->page->endBlock('main');
					$this->page->render();


				} else {
					$_SESSION['page_errors'][] = 'Sorry, the upgrade level you chose is not available';
				}
			}

			$levels = Ndoorse_Level::getLevels();
			$numLevels = count($levels);

			$attributes = array();

			if(isset($levels[0])) {
				foreach($levels[0]->attributes as $att) {
					$attributes[$att->key] = $att->name;
				}
			}

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/members/upgrade_options.php';
			$this->page->endBlock('main');
			$this->page->render();


		}

		public function confirmupgrade($args) {

			$paymentType = new SecureTrading();
			$formErrors = $paymentType->validatePaymentForm($args);
			if(empty($formErrors)) {
				$_SESSION['paymentArgs'] = $args;
				$level = new Ndoorse_Level($args['upgradeLevel']);
				if(!$level->getID()) {
					$_SESSION['page_errors'][] = 'Sorry, the membership level you selected does not exist';
					redirect(BASE_URL . 'members/upgrade/');
				}

				$this->page->startBlock('main');
				include SITE_PATH . 'layouts/members/upgrade_confirm.php';
				$this->page->endBlock('main');
				$this->page->render();
			} else {
				$_SESSION['page_errors'][] = 'Please check the highlighted fields';

				$paymentForm = PaymentFormControl::render($args);
				$paymentForm->setFieldErrors($formErrors);
				$levels = Ndoorse_Level::getLevels();
				$numLevels = count($levels);

				$attributes = array();

				if(isset($levels[0])) {
					foreach($levels[0]->attributes as $att) {
						$attributes[$att->key] = $att->name;
					}
				}

				$this->page->startBlock('main');
				include SITE_PATH . 'layouts/members/upgrade_payment.php';
				$this->page->endBlock('main');
				$this->page->render();
			}
		}

		public function doupgrade($args) {

			if(!isset($_SESSION['paymentArgs']) || $_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['paymentArgs']['upgradeLevel'])) {
				$_SESSION['page_errors'][] = 'Sorry, there has been a problem processing your upgrade. Your card has not been charged.';
				redirect(BASE_URL . 'members/upgrade/');
			}

			$level = new Ndoorse_Level($_SESSION['paymentArgs']['upgradeLevel']);
			if(!$level->getID() || !isset($_SESSION['paymentArgs']['type'])) {
				$_SESSION['page_errors'][] = 'Sorry, the upgrade you requested is not available';
				redirect(BASE_URL . 'members/upgrade/');
			}

			$upgrade = new Ndoorse_Member_Upgrade();
			$upgrade->userID = $_SESSION['user']->getID();
			$upgrade->oldLevel = $_SESSION['user']->level;
			$upgrade->newLevel = $_SESSION['paymentArgs']['upgradeLevel'];
			$upgrade->datePaid = date('Y-m-d H:i:s');

			if($_SESSION['paymentArgs']['type'] == 'month') {
				$amount = $level->priceMonth;
				$upgrade->type = 'month';
				$upgrade->dateExpires = date('Y-m-d 23:59:59', strtotime('+1 month'));
			} else {
				$amount = $level->priceYear;
				$upgrade->type = 'year';
				$upgrade->dateExpires = date('Y-m-d 23:59:59', strtotime('+1 year'));
			}

			$paymentType = new SecureTrading();

			$paymentType->populateArguments($_SESSION['paymentArgs']);
			$paymentType->setAmount($amount);

			try {
				$authResult = $paymentType->processAuthorisation($_SESSION['paymentArgs']);
			} catch(PaymentTypeException $e) {
				switch($e->getCode()) {
					case PaymentTypeException::$FAILED_ADDRESS_VERIFICATION:
						$_SESSION['page_errors'][] = 'Sorry, the address details you entered were not able to be validated';
						break;
					case PaymentTypeException::$FAILED_SECURITY_CODE_VERIFICATION:
						$_SESSION['page_errors'][] = 'Sorry, the security code you entered was not valid';
						break;
					default:
						$_SESSION['page_errors'][] = 'Sorry, there was a problem processing your payment.';
				}

				redirect(BASE_URL . 'members/upgrade/' . $level->getID() . '/');
			}

			if($authResult == '' && $paymentType->authorisationSuccessful()) {
				unset($_SESSION['paymentArgs']);

				$upgrade->transactionID = $paymentType->getTransaction()->getTransactionID();
				if($upgrade->save()) {
					$_SESSION['user']->upgrade($level);
					$_SESSION['page_messages'][] = 'Thank you, your upgrade has been completed.';
					redirect(BASE_URL);
				} else {
					throw new Exception('Upgrade for user ' . $_SESSION['user']->getID() . ' failed to save.');
				}

			} else {
				echo '<h1>FAIL</h1>';
				pr($authResult);
			}
		}

		public function cancelupgrade($args) {

			unset($_SESSION['paymentArgs']);
			redirect(BASE_URL);

		}


	}
?>