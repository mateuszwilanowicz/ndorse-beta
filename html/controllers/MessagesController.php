<?php
	class MessagesController extends Controller {

		public function __construct() {
			switch($_SESSION['user']->level) {
				case Ndoorse_Member::LEVEL_RECRUITER:
					define('RECRUITERS_URL', BASE_URL . 'recruiters/');
					$template =  DEFAULT_PATH . 'templates/recruiter.php';
					break;
				case Ndoorse_Member::LEVEL_ADMIN:
					define('ADMIN_URL', BASE_URL . 'admin/');
					$template = DEFAULT_PATH . 'templates/admin.php';
					break;
				default:
					$template = defined('SITE_TEMPLATE') ? SITE_TEMPLATE : DEFAULT_TEMPLATE;
					break;
			}
			parent::__construct(SITE_NAME . ' Messages',  $template);
			$this->page->addStylesheet(SITE_URL . 'styles/design.css');
			$this->page->addStylesheet(SITE_URL . 'styles/messages.css');
		}

		public function index($args) {

			return $this->inbox($args);

		}

		public function inbox($args) {

			$this->loggedIn();

			$orderby = isset($args['orderby']) ? $args['orderby'] : 'dateSent';
			$dir = isset($args['dir']) ? $args['dir'] : 'desc';
			$page = isset($args['inbox_pagenum']) ? $args['inbox_pagenum'] : 1;

			$messages = Ndoorse_Message::getMessages($page, 30, 'inbox', $orderby, $dir);
			$messagecount = Ndoorse_Message::getMessageCount();

			$headings = array('id'=>'userID',
							//array('table'=>'check', 'heading'=>''),
							array('table'=>'status', 'heading'=>''),
							array('table'=>'dateSent', 'heading'=>'Received', 'dir'=>'desc'),
							array('table'=>'senderName', 'heading'=>'From', 'dir'=>'asc'),
							array('table'=>'subject', 'heading'=>'Subject', 'dir'=>'asc'));

			$rows = array();
			foreach($messages as $msg) {
				$unread = strlen($msg->dateRead) < 10;

				$row = array();
				$row['userID'] = $msg->getID();
				$row['_class'] = $unread ? 'unread' : '';
				//$row['check'] = '<input type="checkbox" name="select[' . $msg->getID() . ']" value="1" />';
				$row['status'] = '<span class="message-icon"></span>';
				$row['dateSent'] = $msg->getFormattedDateSent();
				$row['senderName'] = '<a href="' . BASE_URL . 'members/profile/' . $msg->userID . '/">' . $msg->senderName . '</a>';
				$row['subject'] = '<a href="' . BASE_URL . 'messages/read/' . $msg->getID() . '/">' . $msg->subject . '</a>';
				$rows[] = $row;
			}

			$attributes = array('table_id'=>'inbox', 'item_count'=>$messagecount, 'page_size'=>30, 'page_number'=>$page);

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/message/mailbox.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}

		public function sent($args) {

			$this->loggedIn();

			$orderby = isset($args['orderby']) ? $args['orderby'] : 'dateSent';
			$dir = isset($args['dir']) ? $args['dir'] : 'desc';
			$page = isset($args['inbox_pagenum']) ? $args['inbox_pagenum'] : 1;

			$messages = Ndoorse_Message::getMessages($page, 30, 'sent', $orderby, $dir);
			$messagecount = Ndoorse_Message::getMessageCount('sent');

			$headings = array('id'=>'userID',
					//array('table'=>'check', 'heading'=>''),
					array('table'=>'dateSent', 'heading'=>'Sent', 'dir'=>'desc'),
					array('table'=>'senderName', 'heading'=>'To', 'dir'=>'asc'),
					array('table'=>'subject', 'heading'=>'Subject', 'dir'=>'asc'));

			$rows = array();
			foreach($messages as $msg) {
				$row = array();
				$row['userID'] = $msg->getID();
				//$row['check'] = '<input type="checkbox" name="select[' . $msg->getID() . ']" value="1" />';
				$row['dateSent'] = $msg->getFormattedDateSent();
				$row['senderName'] = '<a href="' . BASE_URL . 'members/profile/' . $msg->userID . '/">' . $msg->senderName . '</a>';
				$row['subject'] = '<a href="' . BASE_URL . 'messages/read/' . $msg->getID() . '/">' . $msg->subject . '</a>';
				$rows[] = $row;
			}

			$attributes = array('table_id'=>'inbox', 'item_count'=>$messagecount);

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/message/mailbox.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}

		public function deleted($args) {

			$this->loggedIn();

			$orderby = isset($args['orderby']) ? $args['orderby'] : 'dateSent';
			$dir = isset($args['dir']) ? $args['dir'] : 'desc';
			$page = isset($args['inbox_pagenum']) ? $args['inbox_pagenum'] : 1;

			$messages = Ndoorse_Message::getMessages($page, 30, 'deleted', $orderby, $dir);
			$messagecount = Ndoorse_Message::getMessageCount('deleted');

			$headings = array('id'=>'userID',
				//	array('table'=>'check', 'heading'=>''),
					array('table'=>'dateSent', 'heading'=>'Received', 'dir'=>'desc'),
					array('table'=>'senderName', 'heading'=>'From', 'dir'=>'asc'),
					array('table'=>'subject', 'heading'=>'Subject', 'dir'=>'asc'));

			$rows = array();
			foreach($messages as $msg) {
				$row = array();
				$row['userID'] = $msg->getID();
				//$row['check'] = '<input type="checkbox" name="select[' . $msg->getID() . ']" value="1" />';
				$row['dateSent'] = $msg->dateSent;
				$row['senderName'] = '<a href="' . BASE_URL . 'members/profile/' . $msg->userID . '/">' . $msg->senderName . '</a>';
				$row['subject'] = '<a href="' . BASE_URL . 'messages/read/' . $msg->getID() . '/">' . $msg->subject . '</a>';
				$rows[] = $row;
			}

			$attributes = array('table_id'=>'inbox', 'item_count'=>$messagecount);

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/message/mailbox.php';
			$this->page->endBlock('main');
			$this->page->render($args);


		}

		public function read($args) {

			$this->loggedIn();

			if(!isset($args[1]) || !is_numeric($args[1])) {
				$error = 'Could not find the message you requested.';
				redirect(BASE_URL . 'messages/inbox/');
			} else {
				$message = new Ndoorse_Message($args[1]);
				if(!$message->getID()) {
					$error = 'Could not find the message you requested.';
				} else if($message->userID != $_SESSION['user']->getID() && $message->senderID != $_SESSION['user']->getID()) {
					$error = 'Could not open the specified message.';
				}
			}
			if(isset($error)) {
				redirect(BASE_URL . 'messages/inbox/');
			}

			$message->read();

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/message/read.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}

		public function reply($args) {

			$this->loggedIn();

			if($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($args['messageID'])) {
				$_SESSION['page_errors'][] = 'Sorry, we don\'t know to which message you are trying to reply!';
				redirect(BASE_URL . 'message/inbox/');
			}
            
            //TODO: Rethink the way functional buttons are handled (this looks to messy);
            
			if(isset($args['delete'])) {
				$message = new Ndoorse_Message($args['messageID'], $_SESSION['user']->getID());
				if($message->delete()) {
					$_SESSION['page_messages'][] = 'Message deleted.';
					redirect(BASE_URL . 'messages/inbox/');
				}
				$_SESSION['page_errors'][] = 'Could not delete this message.';
				redirect(BASE_URL . 'messages/read/' . $args['messageID'] . '/');
			} elseif (isset($args['acceptcontact'])) {
                $message = new Ndoorse_Message($args['messageID'], $_SESSION['user']->getID());
                $match = Ndoorse_Match::getbyids($_SESSION['user']->getID(),$message->data,'job');
                $match->status = Ndoorse_Match::STATUS_ACCEPTED;
                $match->save();
                $_SESSION['page_messages'][] = 'Contact request accepted.';
                redirect(BASE_URL . 'messages/read/' . $message->messageID);
			} elseif (isset($args['rejectcontact'])) {
			    $message = new Ndoorse_Message($args['messageID'], $_SESSION['user']->getID());
			    $match = Ndoorse_Match::getbyids($_SESSION['user']->getID(),$message->data,'job');
                $match->status = Ndoorse_Match::STATUS_DECLINED;
                $match->save();
                $_SESSION['page_messages'][] = 'Contact request declined.';
                redirect(BASE_URL . 'messages/read/' . $message->messageID);
            } elseif(isset($args['accept_sp_invite'])) {
                $message = new Ndoorse_Message($args['messageID'], $_SESSION['user']->getID());
                $sp = new Ndoorse_Serviceprovider($message->data);
                $sp->acceptInvite($_SESSION['user']->getID());
                $_SESSION['page_messages'][] = 'Service provider invite accepted.';
                redirect(BASE_URL . 'messages/inbox/');
            } elseif(isset($args['ignore_sp_invite'])) {
                $message = new Ndoorse_Message($args['messageID'], $_SESSION['user']->getID());
                $sp = new Ndoorse_Serviceprovider($message->data);
                $sp->ignoreInvite($_SESSION['user']->getID());
                if($message->delete()) {
                    $_SESSION['page_messages'][] = 'Service provider invite ignored.';
                    redirect(BASE_URL . 'messages/inbox/');
                }
                $_SESSION['page_errors'][] = 'Could not ignore the invite or delete the message.';
                redirect(BASE_URL . 'messages/read/' . $args['messageID'] . '/');
            }

			$writer = MessageControl::render($args['subject'], $args['senderID'], BASE_URL . 'messages/read/' . $args['messageID'] . '/', $args['messageID']);

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/message/write.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}

		public function write($args) {

			$this->loggedIn();
            if(isset($_GET['to'])) {
                $writer = MessageControl::render('',array($_GET['to']));
            } else {
                $writer = MessageControl::render();    
            }
			
            
			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/message/write.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}

		public function send($args) {

			$this->loggedIn();

			if($_SERVER['REQUEST_METHOD'] != 'POST') {
				$_SESSION['page_errors'][] = 'Sorry, you can\'t send an empty message!';
				redirect(BASE_URL . 'messages/inbox/');
			}

			$message = new Ndoorse_Message($args);
			$message->senderID = $_SESSION['user']->getID();

			$recipients = explode(',', $args['userID']);

			if($message->send($recipients)) {
				$_SESSION['page_messages'][] = 'Your message has been sent.';
			} else {
				$_SESSION['page_errors'][] = 'Your message could not be sent.';
			}

			redirect($args['referer']);



		}

	}
?>