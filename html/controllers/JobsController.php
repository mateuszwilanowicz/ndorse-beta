<?php
	class JobsController extends Controller {

		public function __construct() {

            switch($_SESSION['user']->level) {
                case Ndoorse_Member::LEVEL_RECRUITER:
                    define('RECRUITERS_URL', BASE_URL . 'recruiters/');
                    parent::__construct(SITE_NAME . ' Recruiters Jobs',  DEFAULT_PATH . 'templates/recruiter.php');
                    break;
                default:
                    parent::__construct(SITE_NAME . ' Jobs', defined('SITE_TEMPLATE') ? SITE_TEMPLATE : DEFAULT_TEMPLATE);
                    break;
            }
            $this->page->addStylesheet(SITE_URL . 'styles/design.css');
            $this->page->addStylesheet(SITE_URL . 'styles/jobs.css');
		}

        private function application($args) {

        }

		private function checkJob($args) {

			if(!isset($args[1]) || empty($args[1])) {
				$_SESSION['page_errors'][] = 'An error occurred opening this job opportunity.';
				redirect(BASE_URL . 'jobs/');
			}

			$job = new Ndoorse_Job($args[1]);
			if(!$job->getID()) {
				$_SESSION['page_errors'][] = 'Sorry, the job you requested was not found.';
				redirect(BASE_URL . 'jobs/');
			}

			return $job;

		}

		public function index($args) {

			$this->loggedIn();

            $attributes = array();
            if(isset($args['table_pagenum'])) {
                $attributes['page_number'] = (int)$args['table_pagenum'];
            } else {
                $attributes['page_number'] = 1;
            }
            $attributes['page_size'] = 10;

            if(isset($args['orderby'])) {
                $orderby = $args['orderby'];
                if(isset($args['dir'])) {
                    $dir = $args['dir'];
                }
            } else {
                $args['orderby'] = $orderby = 'datePosted';
                $args['dir'] = $dir = 'asc';
            }

			if(isset($args['jobs_page']) && is_numeric($args['jobs_page'])) {
				$page = $args['jobs_page'];
			} else {
				$page = 0;
			}

			$jobs = Ndoorse_Job::getJobs(false, Ndoorse_Job::STATUS_ACTIVE, true, $page, $attributes['page_size'],$orderby,$dir);

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/jobs/list.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}

		public function post($args) {

			$this->loggedIn();

			if((isset($args[1]) && is_int($args[1])) || (isset($args['jobID']) && $args['jobID'] > 0)) {
				$job = new Ndoorse_Job($args[1]);
			} else {
				$job = new Ndoorse_Job();
			}

			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				$job->loadFromArray($args);

				$job->locationID = Ndoorse_Location::saveFromPost($args['location'], $args['locationID']);

				$job->anonymous = isset($args['anonymous']) ? 1 : 0;
				$job->board = isset($args['board']) ? 1 : 0;
				$job->userID = $_SESSION['user']->getID();
				$job->status = Ndoorse_Job::STATUS_PENDING;

				if(empty($args['dateExpires'])) {
					$job->dateExpires = date('Y-m-d 00:00:00', strtotime('+1 month'));
				}

				if($job->save()) {

                    // if match selected - match here
                    // otherwise only recruiter can force job matching from their dashboard
                    if(isset($args['match'])) {
                        Ndoorse_Match::generateMatchesFor($job);
                    }

					$_SESSION['page_messages'][] = 'Your job post has been submitted. We will let you know as soon as it has been approved. <a href="' . BASE_URL . 'jobs/post/">Post another job</a>';
					redirect(BASE_URL . 'jobs/');
				} else {
					$_SESSION['page_errors'][] = 'Your job post could not be submitted.';
				}
			}

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/jobs/edit.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}

		public function view($args) {

			$this->loggedIn();

			$job = $this->checkJob($args);

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/jobs/view.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}

		public function apply($args) {

			$this->loggedIn();

			$job = $this->checkJob($args);

			$fieldErrors = array();

			if($_SERVER['REQUEST_METHOD'] == 'POST') {
				if(isset($args['confirm'])) {
					$fieldErrors = FormValidator::validate($args, JobApplicationControl::$applicationRules);

					if(empty($fieldErrors)) {
						list($success, $output) = Ndoorse_Document::upload('cv', $args['cv_name']);

						if($success || $output == '') { // either a file was successfully uploaded, or no file was selected

							$application = new Ndoorse_Application($args);

							$application->userID = $_SESSION['user']->getID();
							$application->jobID = $job->getID();
							$application->status = Ndoorse_Application::STATUS_UNREAD;

							if($success) {
								$application->cv = $output; // this is the documentID of the CV
							} else if(isset($args['existingCV'])) {
								$application->cv = $args['existingCV'];
							}

							if($application->save()) {

                                $match = new Ndoorse_Match($job->jobID,'job',$_SESSION['user']->getID());
                                $match->userID = $_SESSION['user']->getID();
                                $match->entityID = $job->jobID;
                                $match->entity = 'job';
                                $match->applicationID = $application->getID();
                                $match->type = Ndoorse_Match::TYPE_APLIED;
                                $match->status = Ndoorse_Match::STATUS_NOTCONTACED;
                                $match->save();

								$message = new Ndoorse_Message();
								$message->senderID = $_SESSION['user']->getID();
                                $message->jobID = $job->jobID;
								$message->subject = 'Application for Job: ' . $job->title;
								$message->message = '<p>' . $_SESSION['user']->firstname . ' ' . $_SESSION['user']->lastname . ' applied for your job vacancy.';
								$message->type = Ndoorse_Message::TYPE_JOB_RESPONSE;
								$message->data = $application->getID();
								$message->send(array($job->userID));

								$_SESSION['page_messages'][] = 'Your application for this job opportunity has been submitted.';
								redirect(BASE_URL . 'jobs/view/' . $job->getID() . '/');
							} else {
								$_SESSION['page_errors'][] = 'Your application could not be submitted.';
							}
						} else {
							$_SESSION['page_errors'][] = 'There was a problem uploading your CV: ' . $output;
						}
					} else {
						$_SESSION['page_errors'][] = 'Please check the highlighted fields.';
					}
				} else {
					$_SESSION['page_errors'][] = 'Please confirm that you wish your details to be passed to the recruiter for this role.';
				}
			}
			$applyForm = JobApplicationControl::render($job, $fieldErrors);

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/jobs/apply.php';
			$this->page->endBlock('main');
			$this->page->render($args);

		}

		public function recommend($args) {

			$this->loggedIn();

			$job = $this->checkJob($args);

			$fieldErrors = array();

			if($_SERVER['REQUEST_METHOD'] == 'POST') {

				if(isset($args['recommend_personType']) && ($args['recommend_personType'] == 'network' || $args['recommend_personType'] == 'remote')) {
					$type = $args['recommend_personType'];
				} else if(isset($args['recommend_email']) && !empty($args['recommend_email'])) {
					$type = 'external';
				} else {
					$type = 'network';
				}

				if($type == 'network') {
					$fieldErrors = FormValidator::validate($args, RecommendationControl::$internalRules);
				} else {
					$fieldErrors = FormValidator::validate($args, RecommendationControl::$externalRules);
				}

				if(empty($fieldErrors)) {

					$recommendation = new Ndoorse_Recommendation();
                    $recommendation->entity = "job";
					$recommendation->entityID = $job->getID();
					$recommendation->referrerID = $_SESSION['user']->getID();

                    $subject = $_SESSION['user']->getName() . ' thinks you might want to know about this job';
                    $params = array('name'=>$_SESSION['user']->getName(),
                                    'url'=>BASE_URL,
                                    'title'=>$job->title,
                                    'company'=>$job->company,
                                    'location'=>$job->location,
                                    'salary'=>$job->getSalary(),
                                    'type'=>$job->getType(),
                                    'hours'=>$job->getHours(),
                                    'date'=>$job->getPostDate()
                                    );

					//if($recommendation->save()) {

					if($type == 'remote') {
						// check if existing member
						$member = Ndoorse_Member::getUserByEmail($args['recommend_email']);
						// if we have an existing member, we actually want to send an internal message
						if($member instanceof Ndoorse_Member && $member->getID()) {
							$_SESSION['page_messages'][] = 'This person is already an ndoorse member';
							$type = 'network';
							// clear bits we don't need any more
							$recommendation->applicantID = $member->userID;
							$recommendation->email = '';
						// otherwise we should send an email
						} else {
                            //send and invitation with job details
                            $template = 'job_recommendation';
                            $phantomUserID = $_SESSION['user']->invite($args['recommend_email'], $template, $params, $subject, false);
                            $recommendation->applicantID = $phantomUserID;
                            $args['recommend_userID'] = $phantomUserID;
                            //continue with normal message anyways
                            $type = 'network';
						}

					}

					if($type == 'network') {
						$message = new Ndoorse_Message();
						$message->loadTemplate('network_recommend_job', $params);
						$message->senderID = $_SESSION['user'];
						$message->subject = 'Job Recommendation';
						$message->type = Ndoorse_Message::TYPE_JOB_RECOMMENDATION;
						$message->data = $job->getID();
						$message->send(array($args['recommend_userID']));
					}

                    $recommendation->save();

                    $match = new Ndoorse_Match($job->jobID,'job',$_SESSION['user']->getID());
                    $match->userID = $args['recommend_userID'];
                    $match->entityID = $job->jobID;
                    $match->entity = 'job';
                    $match->recommendeeID = $_SESSION['user']->getID();
                    $match->type = Ndoorse_Match::TYPE_RECOMMENDED;
                    $match->status = Ndoorse_Match::STATUS_NOTCONTACED;
                    $match->save();

					$_SESSION['page_messages'][] = 'Your recommendation has been sent';

					redirect(BASE_URL . 'jobs/view/' . $job->getID() . '/');

				} else {
					$_SESSION['page_errors'][] = 'Please check the highlighted fields';
				}


			}

			$recommendForm = RecommendationControl::render($job->getID(), 'jobs', $fieldErrors);

			$this->page->startBlock('main');
			include SITE_PATH . 'layouts/jobs/recommend.php';
			$this->page->endBlock('main');
			$this->page->render($args);


		}

	}
?>