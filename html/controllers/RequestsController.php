<?php
	class RequestsController extends Controller {

		public function __construct() {
			parent::__construct(SITE_NAME . ' Requests', defined('SITE_TEMPLATE') ? SITE_TEMPLATE : DEFAULT_TEMPLATE);
			$this->page->addStylesheet(SITE_URL . 'styles/design.css');
			$this->page->addStylesheet(SITE_URL . 'styles/requests.css');
		}

		public function index($args) {

			$this->loggedIn();

			$requests = Ndoorse_Request::getRequests(false, Ndoorse_Request::STATUS_ACTIVE, $args);

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/requests/list.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}

		public function recommend($args) {

			$this->loggedIn();
			if(isset($args[1]) && is_numeric($args[1])) {
				$request = new Ndoorse_Request($args[1]);
			} else {
				$_SESSION['page_errors'][] = 'Sorry, we could not find that request';
				redirect(BASE_URL . 'requests/');
			}

			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				$mode = isset($args['recommend_personType']) && $args['recommend_personType'] == 'remote' ? 'remote' : 'network';

				if($mode == 'network') {
					if(!isset($args['recommend_userID']) || empty($args['recommend_userID'])) {
						$_SESSION['page_errors'][] = 'Please choose one of your connections';
					}

					$targetMember = new Ndoorse_Member($args['recommend_userID']);
					if(!$targetMember->getID()) {
						$_SESSION['page_errors'][] = 'Sorry, the member you chose does not exist.';
					}
				} else {
					if(!isset($args['recommend_email']) || empty($args['recommend_email'])) {
						$_SESSION['page_errors'][] = 'Please enter an email address';
					}

					$member = Ndoorse_Member::getUserByEmail($args['recommend_email']);
					if($member !== false) {
						$mode = 'network';
						$args['recommend_userID'] = $member;
						unset($args['recommend_email']);
					}
				}

				if(empty($_SESSION['page_errors'])) {
					$types = $request->getTypes();
					$content = array('name'=>$_SESSION['user']->firstname, 'summary'=>$request->summary, 'type'=>$types);

					$subject = $_SESSION['user']->firstname . ' ' . $_SESSION['user']->lastname . ' thinks you might be interested in this request';
					$type = Ndoorse_Message::TYPE_REQUEST_RECOMMENDATION;

					if($mode == 'network') {
						$recipient = array($args['recommend_userID']);
					} else {
						$recipient = $args['recommend_email'];
					}
				}

				if(isset($subject) && isset($content) && isset($type) && isset($recipient)) {
					if($mode == 'network') {
						$message = new Ndoorse_Message();
						$message->loadTemplate('request_recommend', $content);

						$message->senderID = $_SESSION['user']->getID();
						$message->subject = $subject;
						$message->type = $type;
						$message->data = $request->getID();

						try {
							$message->send($recipient);
							$_SESSION['page_messages'][] = 'Your recommendation has been sent';
							redirect(BASE_URL . 'requests/request/' . $request->getID());
						} catch(Exception $e) {
							$_SESSION['page_errors'][] = 'Your recommendation could not be sent';
						}
					} else {
						$content['url'] = BASE_URL;
						$template = 'request_recommendation';

						$phantomUserID = $_SESSION['user']->invite($recipient, $template, $content, $subject, false);

						$message = new Ndoorse_Message();
						$message->loadTemplate('request_recommend', $content);
						$message->senderID = $_SESSION['user']->getID();
						$message->subject = $subject;
						$message->type = $type;
						$message->data = $request->getID();
						try {
							$message->send(array($phantomUserID));
							$_SESSION['page_messages'][] = 'Your recommendation has been sent';
							redirect(BASE_URL . 'requests/request/' . $request->getID());
						} catch(Exception $e) {
							$_SESSION['page_errors'][] = 'Your recommendation could not be sent. Please reload the page and try again.';
						}
					}
				}
			}

			redirect(BASE_URL . 'requests/request/' . $request->getID());

		}

		public function request($args) {

			$this->loggedIn();

			if(isset($args[1]) && is_numeric($args[1])) {
				$request = new Ndoorse_Request($args[1]);
			} else {
				$_SESSION['page_errors'][] = 'Sorry, we could not find that request';
				redirect(BASE_URL . 'requests/');
			}

			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				if(isset($args['mode']) && ($args['mode'] == 'message' || $args['mode'] == 'recommend')) {
					if($args['mode'] == 'message') {

						$mode = 'message';

						if(!isset($args['message']) || empty($args['message'])) {
							$_SESSION['page_errors'][] = 'Please enter a message';
						}

						$subject = 'Response to your request';
						$content = $args['message'];
						$type = Ndoorse_Message::TYPE_REQUEST_RESPONSE;
						$recipient = array($request->userID);

					} else {

						$mode = isset($args['type']) && $args['type'] == 'remote' ? 'remote' : 'network';
						pr($mode,false);
						echo "mode dumpt!";
						if($mode == 'network') {
							if(!isset($args['userID']) || empty($args['userID'])) {
								$_SESSION['page_errors'][] = 'Please choose one of your connections';
							}

							$targetMember = new Ndoorse_Member($args['userID']);
							if(!$targetMember->getID()) {
								$_SESSION['page_errors'][] = 'Sorry, the member you entered does not exist.';
							}
						} else {
							if(!isset($args['email']) || empty($args['email'])) {
								$_SESSION['page_errors'][] = 'Please enter an email address';
							}

							$member = Ndoorse_Member::getUserByEmail($args['email']);
							if($member !== false) {
								$mode = 'network';
								$args['userID'] = $member;
								unset($args['email']);
							}
						}

						if(empty($_SESSION['page_errors'])) {

							$types = $request->getTypes();
							$content = array('name'=>$_SESSION['user']->firstname, 'summary'=>$request->summary, 'type'=>$types);

							$subject = $_SESSION['user']->firstname . ' ' . $_SESSION['user']->lastname . ' thinks you might be interested in this request';
							$type = Ndoorse_Message::TYPE_REQUEST_RECOMMENDATION;

							if($mode == 'network') {
								$recipient = array($args['userID']);
							} else {
								$recipient = $args['email'];
							}
						}
					}

					if(isset($subject) && isset($content) && isset($type) && isset($recipient)) {
						if($mode == 'network' || $mode == 'message') {
							$message = new Ndoorse_Message();

							if($args['mode'] == 'message') {
								$message->message = $content;
							} else {
								$message->loadTemplate('request_recommend', $content);
							}

							$message->senderID = $_SESSION['user']->getID();
							$message->subject = $subject;
							$message->type = $type;
							$message->data = $request->getID();

							try {
								$message->send($recipient);
								$_SESSION['page_messages'][] = 'Your message has been sent';
								redirect(BASE_URL . 'requests/request/' . $request->getID());
							} catch(Exception $e) {
								$_SESSION['page_errors'][] = 'Your message could not be sent';
							}
						} else {
							$content['url'] = BASE_URL;
							$template = 'request_recommendation';

							$phantomUserID = $_SESSION['user']->invite($recipient,$template,$content,$subject);

							$message = new Ndoorse_Message();
							$message->loadTemplate('request_recommend', $content);
							$message->senderID = $_SESSION['user']->getID();
							$message->subject = $subject;
							$message->type = $type;
							$message->data = $request->getID();
							try {
								$message->send(array($phantomUserID));
								$_SESSION['page_messages'][] = 'Your message has been sent';
								redirect(BASE_URL . 'requests/request/' . $request->getID());
							} catch(Exception $e) {
								$_SESSION['page_errors'][] = 'Your message could not be sent';
						   }
						}
					}
				} else {
					$_SESSION['page_errors'][] = 'Sorry, we don\'t know what you were trying to do';
				}
			}

			$responseForm = new FormControl(BASE_URL . 'requests/request/' . $args[1] . '/');

			$types = array('network'=>array('label'=>'This person is in my ndoorse network', 'checked'=>true), 'remote'=>array('label'=>'This person is not on ndoorse'));

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/requests/request.php';
			$this->page->endBlock('main');
			$this->page->render($args);
		}

		public function respond($args) {

			$this->loggedIn();

			if(isset($args[1]) && is_numeric($args[1])) {
				$request = new Ndoorse_Request($args[1]);
			} else {
				$_SESSION['page_errors'][] = 'Sorry, we could not find that request';
				redirect(BASE_URL . 'requests/');
			}

			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				if(!isset($args['message']) || empty($args['message'])) {
					$_SESSION['page_errors'][] = 'Please enter a message';
				}

				$subject = 'Response to your request';
				$content = $args['message'];
				$type = Ndoorse_Message::TYPE_REQUEST_RESPONSE;
				$recipient = array($request->userID);

				if(empty($_SESSION['page_errors'])) {
					if(isset($subject) && isset($content) && isset($type) && isset($recipient)) {
						$message = new Ndoorse_Message();
						$message->message = $content;

						$message->senderID = $_SESSION['user']->getID();
						$message->subject = $subject;
						$message->type = $type;
						$message->data = $request->getID();

						try {
							$message->send($recipient);
							$_SESSION['page_messages'][] = 'Your message has been sent';
							redirect(BASE_URL . 'requests/request/' . $request->getID());
						} catch(Exception $e) {
							$_SESSION['page_errors'][] = 'Your message could not be sent';
						}
					} else {
						$_SESSION['page_errors'][] = 'Sorry, there was a problem sending your message.';
					}
				}

				redirect(BASE_URL . 'requests/request/' . $request->getID() . '/');
			}


		}

		public function post($args) {

			$this->loggedIn();

			if((isset($args[1]) && is_int($args[1])) || (isset($args['requestID']) && $args['requestID'] > 0)) {
				$request = new Ndoorse_Request($args[1]);
			} else {
				$request = new Ndoorse_Request();
			}

			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				$request->loadFromArray($args);

				$request->locationID = Ndoorse_Location::saveFromPost($args['location'], $args['locationID']);

				$request->anonymous = isset($args['anonymous']) ? 1 : 0;
				$request->board = isset($args['board']) ? 1 : 0;
				$request->userID = $_SESSION['user']->getID();
				$request->datePosted = date('Y-m-d H:i:s');
				$request->status = Ndoorse_Request::STATUS_PENDING;

				if(empty($args['dateExpires'])) {
					$request->dateExpires = date('Y-m-d 00:00:00', strtotime('+1 month'));
				}

				$request->type = 0;
				if(isset($args['type_advice'])) $request->type += Ndoorse_Request::TYPE_ADVICE;
				if(isset($args['type_help'])) $request->type += Ndoorse_Request::TYPE_HELP;
				if(isset($args['type_introduction'])) $request->type += Ndoorse_Request::TYPE_INTRODUCTION;
				if(isset($args['type_mentoring'])) $request->type += Ndoorse_Request::TYPE_MENTORING;

				if($request->save()) {
					$_SESSION['page_messages'][] = 'Your request has been submitted. We will let you know as soon as it has been approved. <a href="' . BASE_URL . 'jobs/post/">Post another request</a>';
					redirect(BASE_URL . 'requests/');
				} else {
					$_SESSION['page_errors'][] = 'Your request could not be submitted.';
				}
			}

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/requests/edit.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}


	}
?>