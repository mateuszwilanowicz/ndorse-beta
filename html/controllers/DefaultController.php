<?php
	class DefaultController extends Controller {

		public function __construct() {
			parent::__construct(SITE_NAME, defined('SITE_TEMPLATE') ? SITE_TEMPLATE : DEFAULT_TEMPLATE);
			$this->page->addStylesheet(SITE_URL . 'styles/design.css');
		}

		public function index($args) {

			$fieldErrors = array();

			$layout = 'login';

			if(!isset($_SESSION['user'])) {

				if($_SERVER['REQUEST_METHOD'] == 'POST') {

					if(!isset($args['username']) || empty($args['username']) || !isset($args['password']) || empty($args['password'])) {
						$_SESSION['page_errors'][] = 'Please enter your email address and password to log in';
					} else {
						// if user credential detected log in and redirect to dashboard
						$user = Ndoorse_Member::login($args['username'], $args['password']);

						if($user instanceof Ndoorse_Member) {
							if($user->status == User::STATUS_ACTIVE) {
								session_start();
								$_SESSION['user'] = $user;
								$user->processLogin();
								if($user->level == Ndoorse_Member::LEVEL_NORMAL || $user->level == Ndoorse_Member::LEVEL_PREMIUM) {
									redirect(BASE_URL . 'members/');
								} elseif ($user->level == Ndoorse_Member::LEVEL_SERVICEPROVIDER ) {
                                    redirect(BASE_URL . 'members/');
                                } elseif ($user->level == Ndoorse_Member::LEVEL_RECRUITER || $user->level == Ndoorse_Member::LEVEL_EMPLOYER) {
                                    redirect(BASE_URL . 'recruiters/');
                                } elseif ($user->level == Ndoorse_Member::LEVEL_ADMIN || $user->level == Ndoorse_Member::LEVEL_STAFF) {
									redirect(BASE_URL . 'admin/');
								} else {
									unset($_SESSION['user']);
									$_SESSION['page_errors'][] = 'Invalid user level!';
									redirect(BASE_URL);
								}
							} else {
								session_start();
								$_SESSION['page_errors'][] = 'This user is not activated!';
								redirect(BASE_URL);
							}
						} else {
							$_SESSION['page_errors'][] = 'Username or password incorrect!';
							$fieldErrors['password'] = ' incorrect!';
						}
					}
				}
			} else {
				$user = $_SESSION['user'];
				//pr($user);
				if($user->level == Ndoorse_Member::LEVEL_NORMAL || $user->level == Ndoorse_Member::LEVEL_PREMIUM || $user->level == Ndoorse_Member::LEVEL_SERVICEPROVIDER) {
					redirect(BASE_URL . 'members/');
				} elseif ($user->level == Ndoorse_Member::LEVEL_RECRUITER || $user->level == Ndoorse_Member::LEVEL_EMPLOYER) {
					redirect(BASE_URL . 'recruiters/');
				} elseif ($user->level == Ndoorse_Member::LEVEL_ADMIN || $user->level == Ndoorse_Member::LEVEL_STAFF) {
					redirect(BASE_URL . 'admin/');
				} else {
					unset($_SESSION['user']);
					$_SESSION['page_errors'][] = 'Invalid user level!';
					redirect(BASE_URL);
				}
			}

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/' . $layout . '.php';
			$this->page->endBlock('main');
			$this->page->render($args);
		}

		public function signup($args) {

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/signup.php';
			$this->page->endBlock('main');
			$this->page->render();

		}

		public function logout($args) {
			if(isset($_SESSION['user'])) {
				User::logout();
				$_SESSION['page_messages'][] = 'Goodbye, hope to see you again soon!';
			}
			redirect(BASE_URL);

		}


	}

?>