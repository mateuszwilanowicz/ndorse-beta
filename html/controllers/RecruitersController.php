<?php
	class RecruitersController extends Controller {

		public function __construct() {

			parent::__construct(SITE_NAME . ' Recruiters',  DEFAULT_PATH . 'templates/recruiter.php');
			define('RECRUITERS_URL', BASE_URL . 'recruiters/');
            $this->page->addStylesheet(SITE_URL . 'styles/design.css');
            $this->page->addStylesheet(SITE_URL . 'styles/recruiters.css');

		}

		public function index($args) {
		    $fieldErrors = array();
            if($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($fieldErrors)) {

                $inviteFormRules = InviteControl::$inviteFormRules;
                $fieldErrors = FormValidator::validate($args, $inviteFormRules);
            }

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

            if($_SESSION['user']->level != Ndoorse_Member::LEVEL_RECRUITER)
                redirect(BASE_URL . 'members/');
                //$_SESSION['page_errors'][] = 'Your user level is wrong!';
                
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
            $dir = 'asc';
            if(isset($args['orderby'])) {
                $orderby = $args['orderby'];
                if(isset($args['dir'])) {
                    $dir = $args['dir'] == 'asc' ? 'asc' : 'desc';
                }
            }

            $status = User::STATUS_ACTIVE;
            if(isset($args['status'])) {
                $status = $args['status'];
            }

            $jobs = Ndoorse_Job::getAllJobs(false, false, $attributes['page_number'],$attributes['page_size'], $orderby, $dir,$args);

            $fields = array('id'=>'jobID',
                    array('table'=>'title', 'heading'=>'Job Title'),
                    array('table'=>'company', 'heading'=>'Company'),
                    array('table'=>'location', 'heading'=>'Location'),
                    array('table'=>'datePosted', 'heading'=>'Posted'),
                    array('table'=>'dateExpires', 'heading'=>'Expires')
            );
            $filteredjobs = array();
            $rows = array();
            foreach($jobs as $job) {
                if($job->userID == $_SESSION['user']->userID) {
                    $filteredjobs[] = $job;
                    $rows[] = array(
                        'jobID'=>$job->getID(),
                        'title'=>'<a href="' . RECRUITERS_URL . 'jobs/edit/' . $job->getID() . '/">' . $job->title . '</a>',
                        'company'=>$job->company,
                        'location'=>$job->location,
                        'datePosted'=>$job->getPostDate(),
                        'dateExpires'=>$job->getExpiryDate()
                    );
                }
            }

            $attributes['item_count'] = Ndoorse_Job::getJobs(true, $status);

            $jobTable = TableControl::render($fields, $rows, $attributes);

			$this->page->startBlock('main');
			$holder = array();
            if($_SERVER['REQUEST_METHOD'] == 'POST') {
                $found = false;
                foreach($jobs as $job) {
                    if(isset($args[$job->jobID]) && $args[$job->jobID] == 'on') {
                        $found = true;
                        $holder[] = $job;
                    }
                }
                if(isset($_POST['share'])) {

                    if($found) {
                        // share all the jobs in the $holder selection array
                        // display share form? redirect? overlay?
                        $_SESSION['holder'] = $holder;
                        $_SESSION['page_messages'][] = 'Your jobs have been shared!.';
                        redirect(RECRUITERS_URL  . 'share/');

                    } else {
                        $_SESSION['page_errors'][] = 'No jobs selected to share!.';
                    }


                } elseif(isset($_POST['delete'])) {
                    if($found) {
                        // delete all the jobs in the $holder selection array
                        foreach($holder as $j)
                            $j->delete();
                        $_SESSION['page_messages'][] = 'Your jobs have been deleted!.';
                    } else {
                        $_SESSION['page_errors'][] = 'No jobs selected to delete!.';
                    }


                } elseif(isset($_POST['match'])) {
                    if($found) {
                        // delete all the jobs in the $holder selection array
                        foreach($holder as $j) {
                            Ndoorse_Match::generateMatchesFor($j);
                            $_SESSION['page_messages'][] = 'Matched members for job: ' . $j->title . '.';
                        }
                    } else {
                        $_SESSION['page_errors'][] = 'No jobs selected to match!.';
                    }


                } if(isset($_POST['search'])) {
                    //pr($args,false);
                    //pr($_POST);
                }
            }
            include SITE_PATH . 'layouts/recruiters/jobs.php';
            $this->page->endBlock('main');
            $this->page->render($args,$fieldErrors);
		}

		public function edit($args) {

			if( isset($args[1]) ) {
                $job = new Ndoorse_Job($args[1]);
		    } elseif((isset($args['jobID']) && $args['jobID'] > 0)) {
                $job = new Ndoorse_Job($args['jobID']);
            } else {
                $job = new Ndoorse_Job();
            }

            if($_SERVER['REQUEST_METHOD'] == 'POST') {

                $job->loadFromArray($args);

                $job->locationID = Ndoorse_Location::saveFromPost($args['location'], $args['locationID']);

                $job->anonymous = isset($args['anonymous']) ? 1 : 0;
                $job->board = isset($args['board']) ? 1 : 0;
                $job->userID = $_SESSION['user']->getID();
                $job->status = Ndoorse_Job::STATUS_ACTIVE;

                if(empty($args['dateExpires'])) {
                    $job->dateExpires = date('Y-m-d 00:00:00', strtotime('+1 month'));
                }

                if($job->save()) {
                    $_SESSION['page_messages'][] = 'Your job post has been submitted. We will let you know as soon as it has been approved. <a href="' . RECRUITERS_URL . '/edit/">Post another job</a>';
                    redirect(RECRUITERS_URL . 'jobs/');
                } else {
                    $_SESSION['page_errors'][] = 'Your job post could not be submitted.';
                }
            }

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/recruiters/edit.php';
			$this->page->endBlock('main');
			$this->page->render($args);
		}

        public function match($args) {
            $this->page->startBlock('main');
            if(isset($args[1]) && isset($args[2])) {
                $orderby = 'lastname';
                $dir = 'asc';
                if(isset($args['orderby'])) {
                    $orderby = $args['orderby'];
                    if(isset($args['dir'])) {
                        $dir = $args['dir'] == 'asc' ? 'asc' : 'desc';
                    }
                }
                switch ($args[1]) {
                    case 'job':
                        $job = new Ndoorse_Job($args['2']);
                        $entityID = $args['2'];
                        $entity_type = $args['1'];
                        //$matches = Ndoorse_Match::generateMatchesFor($job);
                        $matches = Ndoorse_Match::getMatchesFor($entityID,$entity_type,$orderby,$dir,$args);
                        //$members = Ndoorse_Member::getAllMembers($orderby,$dir);
                        if($_SERVER['REQUEST_METHOD'] == 'POST') {
                            $holder = array();
                            if(strlen($_SESSION['user']->company)>1) {
                                $rc = ' recruiter @ ' . $_SESSION['user']->company;
                            } else {
                                $rc = '';
                            }

                            foreach($matches as $match) {
                                if(isset($_POST[$match->userID]) && $_POST[$match->userID] == 'on') {
                                    $holder[] = $match;
                                }
                            }

                            if(isset($_POST['share'])) {

                            } elseif(isset($_POST['message'])) {
                                if(!empty($holder)) {
                                    $subject = $_SESSION['user']->getName() . $rc . ' thinks you might want to know about this job';
                                    $params = array('name'=>$_SESSION['user']->getName(),
                                        'url'=>BASE_URL,
                                        'title'=>$job->title,
                                        'company'=>$job->company,
                                        'location'=>$job->location,
                                        'salary'=>$job->getSalary(),
                                        'type'=>$job->getType(),
                                        'hours'=>$job->getHours(),
                                        'date'=>$job->getPostDate(),
                                        'recruitingcompany' => $rc
                                    );
                                    $message = new Ndoorse_Message();
                                    $message->loadTemplate('recruiter_contact', $params);
                                    $message->senderID = $_SESSION['user'];
                                    $message->subject = 'Contact request - Job oportunity';
                                    $message->type = Ndoorse_Message::TYPE_RECRUITER_CONTACT;
                                    $message->data = $job->getID();
                                    if($match->status != Ndoorse_Match::STATUS_DECLINED && $match->status != Ndoorse_Match::STATUS_ACCEPTED)
                                        $message->send($holder->userID);

                                    foreach ($holder as $match) {
                                        if($match->status != Ndoorse_Match::STATUS_DECLINED && $match->status != Ndoorse_Match::STATUS_ACCEPTED)
                                            $match->status = Ndoorse_Match::STATUS_CONTACTED;
                                        $match->save();
                                    }
                                    $_SESSION['page_messages'][] = 'Your messages where sent!';
                                } else {
                                    $_SESSION['page_errors'][] = 'No reciepients selected!';
                                }
                            } elseif(isset($_POST['remove'])) {
                                if(!empty($holder)) {
                                    foreach ($holder as $match) {
                                        $match->delete();
                                    }
                                }
                            }
                            //pr($args,false);
                            //pr($_POST,false);
                        }

                        break;
                    default:
                        $_SESSION['page_errors'][] = 'Nothing to match!';
                        redirect(RECRUITERS_URL);
                        break;
                }

            } else {
                redirect(RECRUITERS_URL);
            }

            include SITE_PATH . 'layouts/recruiters/match.php';
            $this->page->endBlock('main');
            $this->page->render($args);
        }

        public function share($args) {
            $jobs = array();
            if(isset($_SESSION['holder'])) {
                $jobs = $_SESSION['holder'];
            }

            if($_SERVER['REQUEST_METHOD'] == 'POST') {
                if(isset($args['userID'])) {

                    $_SESSION['page_messages'][] = 'You have just shared those jobs with ' . $args['respondent'] . '!';
                } else {
                    $_SESSION['page_errors'][] = 'You must select one of your exiting contacts to share with!';
                }
            }

            $this->page->startBlock('main');
            include SITE_PATH . 'layouts/recruiters/share.php';
            $this->page->endBlock('main');
            $this->page->render($args);
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