<?php
	class SignupController extends Controller {
		public function __construct() {
			parent::__construct(SITE_NAME, defined('SITE_TEMPLATE') ? SITE_TEMPLATE : DEFAULT_TEMPLATE);
			$this->page->addStylesheet(SITE_URL . 'styles/design.css');
		}

		public function index($args) {

			if($_SERVER['REQUEST_METHOD'] == 'POST') {

				$signupFormRules = SignupControl::$signupFormRules;
				$fieldErrors = FormValidator::validate($args, $signupFormRules);

				if (sizeof($fieldErrors) > 0 ) {
					$this->page->startBlock('main');
					include SITE_PATH . 'layouts/signup.php';
					$this->page->endBlock('main');
					$this->page->render($args);
				} elseif (!isset($_SESSION['key'])) {
					$newUser = new Ndoorse_Member($args);
					$newUser->username = $newUser->email;
                    $newUser->save();

                    //pr($newUser);
					if(!$newUser::userExist($newUser->username)) {
						$newUser->status = User::STATUS_PENDING;
						$newUser->setPassword($args['password']);
						$_SESSION['page_messages'][] = 'Your sign up is complete. Please wait for your account to be confirmed.';
						redirect(BASE_URL);
					} else {
						// push a new error to the error fields and redisplay the form
						if(isset($args['email'])) $args['email'] = '';
						if(isset($args['confirmemail'])) $args['confirmemail'] = '';
						$fieldErrors['email'] = " already registered, please use a different one!";
						$this->page->startBlock('main');
						include SITE_PATH . 'layouts/signup.php';
						$this->page->endBlock('main');
						$this->page->render($args);
					}

				} else {
					//if key is set load the user bound to this activation code
					$_SESSION['user']->firstname = $args['firstname'];
					$_SESSION['user']->lastname = $args['lastname'];
					$_SESSION['user']->setPassword($args['password']);

					//TODO set activation to 'pending' state and notify the referrer with a message to confirm acount activation
					//delete the activation entry only after admin activation
					//$_SESSION['user']->deleteActivation($_SESSION['key']);

					$_SESSION['page_messages'][] = 'Your account is now awaiting verification.';
					$newConnection = new Ndoorse_Connection();
					$newConnection->requesterID = $_SESSION['user']->getID();
					$newConnection->respondentID = $_SESSION['user']->referrerID;
					$newConnection->connectionStatus = 1;
					$newConnection->saveModel();
					unset($_SESSION['key']);
					redirect(BASE_URL . 'members/');
				}
			} else {

				if(isset($_SESSION['key'])) {
					$args['key'] = $_SESSION['key'];
					$args['email'] = $_SESSION['email'];
					$args['confirmemail'] = $_SESSION['email'];
				}

				$this->page->startBlock('main');
				include SITE_PATH . 'layouts/signup.php';
				$this->page->endBlock('main');
				$this->page->render($args);
			}
		}
	}
?>