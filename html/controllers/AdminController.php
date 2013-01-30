<?php
	class AdminController extends Controller {

		public function __construct() {

			parent::__construct(SITE_NAME . ' Administration', DEFAULT_PATH . 'templates/admin.php');
			$this->page->addStylesheet(SITE_URL . 'styles/design.css');
			$this->page->addStylesheet(SITE_URL . 'styles/admin.css');
			define('ADMIN_URL', BASE_URL . 'admin/');

		}

		public function index($args) {

			$stats = array();

			$stats['members'] = Ndoorse_Member::getStats();
			$stats['jobs'] = Ndoorse_Job::getStats();
			$stats['events'] = Ndoorse_Event::getStats();
			$stats['requests'] = Ndoorse_Request::getStats();
			$stats['recommendations'] = Ndoorse_Recommendation::getStats();

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/admin/dashboard.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}

		public function members($args) {

			if(isset($args[1])) {
				switch($args[1]) {
					case 'edit':
						return $this->member_edit($args);

				}
			}

			// member management
			$fields = array('id'=>'userID',
						array('table'=>'name', 'heading'=>'Name', 'dir'=>'ASC'),
						array('table'=>'referrer', 'heading'=>'Referred By', 'dir'=>'ASC'),
						array('table'=>'company', 'heading'=>'Company', 'dir'=>'ASC'),
						array('table'=>'login', 'heading'=>'Last Seen', 'dir'=>'ASC'),
						array('table'=>'dateJoined', 'heading'=>'Join Date', 'dir'=>'ASC'),
						array('table'=>'dateApproved', 'heading'=>'Approval Date', 'dir'=>'ASC'),
						array('table'=>'level', 'heading'=>'Level', 'dir'=>'ASC')
					);

			$attributes = array();
			if(isset($args['table_pagenum'])) {
				$attributes['page_number'] = (int)$args['table_pagenum'];
			} else {
				$attributes['page_number'] = 1;
			}
			$attributes['page_size'] = 10;

			$orderby = 'name';
			$dir = 'ASC';
			if(isset($args['orderby'])) {
				$orderby = $args['orderby'];
				if(isset($args['dir'])) {
					$dir = $args['dir'] == 'ASC' ? 'ASC' : 'DESC';
				}
			}

			$status = User::STATUS_PENDING;
			if(isset($args['status'])) {
				$status = $args['status'];
			}

			$members = Ndoorse_Member::getMembers(false, $status, $attributes['page_number'], $attributes['page_size'], $orderby, $dir);

			$rows = array();
			foreach($members as $member) {
				$tmp = array();
				$tmp['userID'] = $member['userID'];
				$tmp['name'] = '<a href="' . ADMIN_URL . 'members/edit/' .  $member['userID'] . '/">' . (empty($member['name']) ? '(unnamed member)' : $member['name']) . '</a>';
				$tmp['referrer'] = '<a href="">' . $member['referrer'] . '</a>';
				$tmp['company'] = '<a href="">' . $member['company'] . '</a>';
				$tmp['login'] = $member['login'] == '(never)' ? $member['login'] : date('d/m/Y', strtotime($member['login']));
				$tmp['dateJoined'] = date('Y', strtotime($member['dateJoined'])) == 1970 ? '' : date('d/m/Y', strtotime($member['dateJoined']));
				$tmp['dateApproved'] = date('Y', strtotime($member['dateApproved'])) == 1970 ? '' : date('d/m/Y', strtotime($member['dateApproved']));
				$tmp['level'] = Ndoorse_Member::getLevelName($member['level']);
				$rows[] = $tmp;
			}


			$attributes['item_count'] = Ndoorse_Member::getMembers(true, $status);
			$membersTable = TableControl::render($fields, $rows, $attributes);

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/admin/members.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}

		private function member_edit($args) {

			if(!isset($args[2])) {
				$member = new Ndoorse_Member();
			} else {
				$member = new Ndoorse_Member($args[2]);
				if(!$member->getID()) {
					$_SESSION['page_errors'][] = 'Sorry, we could not find the member you requested';
					redirect(ADMIN_URL . 'members/');
				}

				if(isset($args[3]) && ($args[3] == 'confirm' || $args[3] == 'decline')) {
					if($args[3] == 'confirm') {
						$member->status = User::STATUS_ACTIVE;
					} else {
						$member->status = User::STATUS_INACTIVE;
					}

					if($member->save()) {
						$_SESSION['page_messages'][] = 'The member\'s profile status has been updated';
						redirect(ADMIN_URL . 'members/');
					} else {
						$_SESSION['page_errors'][] = 'Sorry, the member\'s profile status could not be updated';
					}
				}

				if($member->status == User::STATUS_PENDING) {
					$_SESSION['page_messages'][] = 'This member is not yet confirmed. <a href="confirm/">Confirm</a> or <a href="decline/">Decline</a>';
				}
			}

			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				$validation = FormValidator::validate($args, MembersProfileControl::$profileFormRules);
				$member->loadFromArray($args);

				if(empty($validation)) {
					if($member->save()) {
						if(isset($args['password']) && isset($args['confirm']) && !empty($args['password']) && $args['password'] == $args['confirm']) {
							if($member->setPassword($args['password'])) {
								$_SESSION['page_messages'][] = 'Password updated';
							} else {
								$_SESSION['page_errors'][] = 'Password could not be updated';
							}
						}


						$_SESSION['page_messages'][] = 'Member Updated';
						redirect(ADMIN_URL . 'members/');
					} else {
						$_SESSION['page_errors'][] = 'Sorry, there was a problem saving your changes.';
					}
				} else {
					$_SESSION['page_errors'][] = 'Please check the highlighted fields.';
				}

			}

			$countries = Country::getCountries();
			FormControl::selectOption($countries, empty($member->country) ? 'GB' : $member->country);

			$levels = Ndoorse_Member::getLevels();
			FormControl::selectOption($levels, $member->level);

			$statuses = Ndoorse_Member::getStatuses();
			FormControl::selectOption($statuses, $member->status);

			$form = new FormControl(ADMIN_URL . 'members/edit/' . isset($args[2]) ? $args[2] . '/' : '');
			$form->html('<div class="col left-col"><h3>Settings</h3><div class="box top-box">');
			$form->select('status', 'Status:', $statuses);
			$form->textbox('referrer', 'Referred By:', $member->referrer);
			$form->hidden('referrerID', $member->referrerID);
			$form->select('level', 'User Type:', $levels);
			$form->textbox('identifier', 'Identifier:', $member->identifier);
			$form->html('</div>');

			$form->html('<div class="box">');
			$form->password('password', 'Password:');
			$form->password('confirm', 'Confirm Password:');
			$form->html('</div></div>');


			$form->html('<div class="col"><h3>Address Details</h3><div class="box top-box">');
			$form->textbox('firstname', 'First name:', $member->firstname);
			$form->textbox('lastname', 'Last name:', $member->lastname);
			$form->textbox('address1', 'Address 1:', $member->address1);
			$form->textbox('address2', 'Address 2:', $member->address2);
			$form->textbox('city', 'City:', $member->city);
			$form->textbox('region', 'Region:', $member->region);
			$form->textbox('postcode', 'Postcode:', $member->postcode);
			$form->select('country', 'Country:', $countries);
			$form->html('</div>');

			$form->html('<h3>Contact Details</h3><div class="box">');
			$form->textbox('email', 'Email Address:', $member->email);
			$form->textbox('telhome', 'Home Telephone:', $member->telhome);
			$form->textbox('telwork', 'Work Telephone:', $member->telwork);
			$form->textbox('telmobile', 'Mobile Telephone:', $member->telmobile);
			$form->html('</div></div>');

			$form->submit('save', 'Save Member');
			$form->html('<a href="' . ADMIN_URL . 'members/" class="button"><span>Cancel</span></a>');

			if(isset($validation)) {
				$form->setFieldErrors($validation);
			}

			$memberForm = $form->render();

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/admin/member_edit.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}

		public function jobs($args) {

			if(isset($args[1])) {
				switch($args[1]) {
					case 'edit':
						return $this->job_edit($args);

				}
			}

			$attributes = array();
			if(isset($args['table_pagenum'])) {
				$attributes['page_number'] = (int)$args['table_pagenum'];
			} else {
				$attributes['page_number'] = 1;
			}
			$attributes['page_size'] = 10;

			$orderby = 'title';
			$dir = 'ASC';
			if(isset($args['orderby'])) {
				$orderby = $args['orderby'];
				if(isset($args['dir'])) {
					$dir = $args['dir'] == 'ASC' ? 'ASC' : 'DESC';
				}
			}

			$status = User::STATUS_PENDING;
			if(isset($args['status'])) {
				$status = $args['status'];
			}

			$jobs = Ndoorse_Job::getJobs(false, $status, false, $attributes['page_number'], $attributes['page_size'], $orderby, $dir);

			$fields = array('id'=>'jobID',
					array('table'=>'title', 'heading'=>'Job Title'),
					array('table'=>'company', 'heading'=>'Company'),
					array('table'=>'location', 'heading'=>'Location'),
					array('table'=>'datePosted', 'heading'=>'Posted'),
					array('table'=>'dateExpires', 'heading'=>'Expires')
			);

			$rows = array();
			foreach($jobs as $job) {
				$rows[] = array(
					'jobID'=>$job->getID(),
					'title'=>'<a href="' . ADMIN_URL . 'jobs/edit/' . $job->getID() . '/">' . $job->title . '</a>',
					'company'=>$job->company,
					'location'=>$job->location,
					'datePosted'=>$job->getPostDate(),
					'dateExpires'=>$job->getExpiryDate()
				);
			}

			$attributes['item_count'] = Ndoorse_Job::getJobs(true, $status, false);

			$jobTable = TableControl::render($fields, $rows, $attributes);

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/admin/jobs.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}

		private function job_edit($args) {

			if(!isset($args[2])) {
				$job = new Ndoorse_Job();
			} else {
				$job = new Ndoorse_Job($args[2]);
				if(!$job->getID()) {
					$_SESSION['page_errors'][] = 'Sorry, we could not find the job you requested';
					redirect(ADMIN_URL . 'jobs/');
				}
			}

			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				$job->loadFromArray($args);
				$validation = FormValidator::validate($args, JobEditControl::$formRules);

				if(empty($validation)) {
					if($job->save()) {
						$_SESSION['page_messages'][] = 'Job has been updated';
						redirect(ADMIN_URL . 'jobs/');
					} else {
						$_SESSION['page_errors'][] = 'Sorry, the job could not be updated';
					}
				} else {
					$_SESSION['page_errors'][] = 'Please check the highlighted fields';
				}
			}

			$status = Ndoorse_Job::getStatusOptions();
			FormControl::selectOption($status, $job->status);

			$hours = Ndoorse_Job::getHoursOptions();
			FormControl::selectOption($hours, $job->hours);

			$types = Ndoorse_Job::getTypeOptions();
			FormControl::selectOption($types, $job->type);

			$form = new FormControl(ADMIN_URL . 'jobs/edit/' . (isset($args[2]) ? $args[2] . '/' : ''));
			$form->select('status', 'Status:', $status);
			$form->textbox('title', 'Job Title:', $job->title);
			$form->textarea('description', 'Description:', $job->description);
			$form->textbox('location', 'Location:', $job->location);
			$form->hidden('locationID', $job->locationID);
			$form->textbox('company', 'Company:', $job->company);
			$form->select('hours', 'Hours:', $hours);
			$form->select('type', 'Type:', $types);
			$form->textarea('skills', 'Skills:', $job->skills);
			$form->textbox('minSalary', 'Salary from:', $job->minSalary);
			$form->textbox('maxSalary', 'to:', $job->maxSalary);
			$form->datepicker('dateExpires', 'Expires:', substr($job->dateExpires, 0, 10));
			$form->checkbox('anonymous', 'Hide Creator Details', $job->anonymous, 1);
			$form->checkbox('board', 'Show on Jobs board', $job->board, 1);
			$form->submit('save', 'Save');
			$form->html('<a href="' . ADMIN_URL . 'jobs/" class="button"><span>Cancel</span></a>');

			if(isset($validation)) {
				$form->setFieldErrors($validation);
			}

			$jobForm = $form->render();

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/admin/job_edit.php';
			$this->page->endBlock('main');
			$this->page->render($args);


		}

		public function requests($args) {

			if(isset($args[1])) {
				switch($args[1]) {
					case 'edit':
						return $this->request_edit($args);
				}
			}

			$attributes = array();
			if(isset($args['table_pagenum'])) {
				$attributes['page_number'] = (int)$args['table_pagenum'];
			} else {
				$attributes['page_number'] = 1;
			}
			$attributes['page_size'] = 10;

			$orderby = 'title';
			$dir = 'ASC';
			if(isset($args['orderby'])) {
				$orderby = $args['orderby'];
				if(isset($args['dir'])) {
					$dir = $args['dir'] == 'ASC' ? 'ASC' : 'DESC';
				}
			}

			$args['orderby'] = $orderby;
			$args['dir'] = $dir;
			$status = Ndoorse_Request::STATUS_PENDING;
			if(isset($args['status'])) {
				$status = $args['status'];
			}

			$requests = Ndoorse_Request::getRequests(false, $status, $args, false);

			$fields = array('id'=>'requestID',
					array('table'=>'summary', 'heading'=>'Summary'),
					array('table'=>'location', 'heading'=>'Location'),
					array('table'=>'datePosted', 'heading'=>'Posted'),
					array('table'=>'dateExpires', 'heading'=>'Expires')
			);

			$rows = array();
			foreach($requests as $request) {
				$rows[] = array(
					'requestID'=>$request->getID(),
					'summary'=>'<a href="' . ADMIN_URL . 'requests/edit/' . $request->getID() . '/">' . $request->summary . '</a>',
					'location'=>$request->location,
					'datePosted'=>$request->getPostDate(),
					'dateExpires'=>$request->getExpiryDate()
				);
			}

			$attributes['item_count'] = Ndoorse_Request::getRequests(true, $status);

			$requestTable = TableControl::render($fields, $rows, $attributes);

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/admin/requests.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}

		private function request_edit($args) {

			if(!isset($args[2])) {
				$request = new Ndoorse_Request();
			} else {
				$request = new Ndoorse_Request($args[2]);
			}

			if($_SERVER['REQUEST_METHOD'] == 'POST') {

				$request->loadFromArray($args);
				$validation = FormValidator::validate($args, RequestEditControl::$validationRules);

				if(empty($validation)) {
					$request->type = 0;

					if(isset($args['type_advice'])) $request->type += Ndoorse_Request::TYPE_ADVICE;
					if(isset($args['type_help'])) $request->type += Ndoorse_Request::TYPE_HELP;
					if(isset($args['type_introduction'])) $request->type += Ndoorse_Request::TYPE_INTRODUCTION;
					if(isset($args['type_mentoring'])) $request->type += Ndoorse_Request::TYPE_MENTORING;

					if($request->save()) {
						$_SESSION['page_messages'][] = 'Request has been saved';
						redirect(ADMIN_URL . 'requests/');
					} else {
						$_SESSION['page_errors'][] = 'Sorry, the Request could not be saved';
					}
				} else {
					$_SESSION['page_errors'][] = 'Please check the highlighted fields';
				}

			}

			$statuses = Ndoorse_Request::getStatusOptions();
			FormControl::selectOption($statuses, $request->status);

			$offerings = array(array('label'=>'Looking for'), array('label'=>'Offering'));
			if($request->offering) {
				$offerings[1]['checked'] = true;
			} else {
				$offerings[0]['checked'] = true;
			}

			$form = new FormControl(ADMIN_URL . 'requests/edit/' . (isset($args[2]) ? $args[2] . '/' : ''));

			$form->select('status', 'Status:', $statuses);
			$form->radio('offering', '', $offerings);
			$form->textbox('summary', 'Summary:', $request->summary);
			$form->textarea('description', 'Description:', $request->description);
			$form->textbox('location', 'Location:', $request->location);
			$form->hidden('locationID', $request->locationID);
			$form->checkbox('type_advice', 'Advice', ($request->type & Ndoorse_Request::TYPE_ADVICE) == Ndoorse_Request::TYPE_ADVICE, Ndoorse_Request::TYPE_ADVICE);
			$form->checkbox('type_help', 'Help', ($request->type & Ndoorse_Request::TYPE_HELP) == Ndoorse_Request::TYPE_HELP, Ndoorse_Request::TYPE_HELP);
			$form->checkbox('type_introduction', 'Introduction', ($request->type & Ndoorse_Request::TYPE_INTRODUCTION) == Ndoorse_Request::TYPE_INTRODUCTION, Ndoorse_Request::TYPE_INTRODUCTION);
			$form->checkbox('type_mentoring', 'Mentoring', ($request->type & Ndoorse_Request::TYPE_MENTORING) == Ndoorse_Request::TYPE_MENTORING, Ndoorse_Request::TYPE_MENTORING);
			$form->datepicker('dateExpires', 'Expiry Date:', substr($request->dateExpires, 0, 10));
			$form->checkbox('anonymous', 'Display poster name', $request->anonymous, 1);
			$form->checkbox('board', 'Display on Requests board', $request->board, 1);

			$form->submit('save', 'Save');
			$form->html('<a href="' . ADMIN_URL . 'requests/" class="button"><span>Cancel</span></a>');

			if(isset($validation)) {
				$form->setFieldErrors($validation);
			}

			$requestForm = $form->render();

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/admin/request_edit.php';
			$this->page->endBlock('main');
			$this->page->render($args);
		}

		public function events($args) {

			if(isset($args[1])) {
				switch($args[1]) {
					case 'edit':
						return $this->event_edit($args);

				}
			}

			$attributes = array();
			if(isset($args['table_pagenum'])) {
				$attributes['page_number'] = (int)$args['table_pagenum'];
			} else {
				$attributes['page_number'] = 1;
			}
			$attributes['page_size'] = 10;

			$orderby = 'startDate';
			$dir = 'ASC';
			if(isset($args['orderby'])) {
				$orderby = $args['orderby'];
				if(isset($args['dir'])) {
					$dir = $args['dir'] == 'ASC' ? 'ASC' : 'DESC';
				}
			}

			$status = Ndoorse_Event::STATUS_PENDING;
			if(isset($args['status'])) {
				$status = $args['status'];
			}

			$events = Ndoorse_Event::getEvents(null, null, false, $status);

			$fields = array('id'=>'eventID',
					array('table'=>'title', 'heading'=>'Event Title'),
					array('table'=>'username', 'heading'=>'Owner'),
					array('table'=>'location', 'heading'=>'Location'),
					array('table'=>'startDate', 'heading'=>'Occurs')
				);

			$rows = array();
			//pr($events);
			foreach($events as $day) {
				foreach($day as $event) {
					$rows[] = array(
						'eventID'=>$event->getID(),
						'title'=>'<a href="' . ADMIN_URL . 'events/edit/' . $event->getID() . '/">' . $event->title . '</a>',
						'username'=>'<a href="' . ADMIN_URL . 'members/edit/' . $event->userID . '/">' . $event->username . '</a>',
						'location'=>$event->location,
						'startDate'=>$event->getDateRange()
					);
				}
			}

			$attributes['item_count'] = count($events);

			$eventTable = TableControl::render($fields, $rows, $attributes);

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/admin/events.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}

		public function event_edit($args) {

			if(!isset($args[2])) {
				$event = new Ndoorse_Event();
			} else {
				$event = new Ndoorse_Event($args[2]);
			}

			if($_SERVER['REQUEST_METHOD'] == 'POST') {

				$event->loadFromArray($args);
				$validation = FormValidator::validate($args, Ndoorse_Event::$eventRules);

				if(empty($validation)) {

					if($event->save()) {
						$_SESSION['page_messages'][] = 'Event has been saved';
						redirect(ADMIN_URL . 'events/');
					} else {
						$_SESSION['page_errors'][] = 'Sorry, the Event could not be saved';
					}
				} else {
					$_SESSION['page_errors'][] = 'Please check the highlighted fields';
				}

			}

			$statuses = Ndoorse_Event::getStatusOptions();
			FormControl::selectOption($statuses, $event->status);

			$repeats = Ndoorse_Event::getRepeatOptions();
			FormControl::selectOption($repeats, $event->repeatRule);

			$form = new FormControl(ADMIN_URL . 'events/edit/' . (isset($args[2]) ? $args[2] . '/' : ''));
			$form->select('status', 'Status:', $statuses);
			$form->textbox('title', 'Title:', $event->title);
			$form->textarea('details', 'Details:', $event->details);
			$form->datepicker('startDate', 'Start date:', substr($event->startDate, 0, 10));
			$form->datepicker('endDate', 'End date:', substr($event->endDate, 0, 10));
			$form->textbox('location', 'Location:', $event->location);
			$form->hidden('locationID', $event->locationID);
			$form->select('repeatRule', 'Repeat:', $repeats);
			$form->textbox('ticketURL', 'Ticket Link:', $event->ticketURL);
			$form->checkbox('private', 'This is a private event', $event->private, 1);
			$form->submit('save', 'Save');
			$form->html('<a href="' . ADMIN_URL . 'events/" class="button"><span>Cancel</span></a>');

			$eventForm = $form->render();

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/admin/event_edit.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}

		public function upgrades($args) {

			$upgrades = Ndoorse_Member_Upgrade::getUpgrades();



pr($upgrades);

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/admin/upgrades.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}

	}
?>