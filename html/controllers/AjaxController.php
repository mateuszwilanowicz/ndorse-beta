<?php
	class AjaxController extends Controller {

		public function __construct() {
			parent::__construct(SITE_NAME, SITE_PATH . 'templates/bare.php');
			$this->page->addStylesheet(SITE_URL . 'styles/jquery-ui-1.9.1.custom.min.css');
		}

		public function loggedIn($redirect = false) {
			if(isset($_SESSION['user']) && $_SESSION['user']->getID()) {
				return true;
			}
			die('Sorry, you must be logged in to perform this action.');
		}

		public function index($args) {

		}

		public function location($args) {

			if(isset($args[1])) {
				switch($args[1]) {
					case 'autocomplete':
						if(!isset($args['name'])) {
							die('');
						}

						$locations = Ndoorse_Location::autocomplete($args['name']);

						$output = '';
						foreach($locations as $location) {
							$output .= '<a href="#' . $location['locationID'] . '">' . $location['location'] . '</a>';
						}
						die($output);
						break;
				}
			}

		}

		public function serviceprovider($args) {

			if(isset($args[1])) {
				switch($args[1]) {
					case 'autocomplete':
						if(!isset($args['name'])) {
							die('');
						}

						$serviceproviders = Ndoorse_Serviceprovider::autocomplete($args['name']);

						$output = '';
						foreach($serviceproviders as $serviceprovider) {
							$output .= '<a href="#' . $serviceprovider['serviceproviderID'] . '">' . $serviceprovider['name'] . '</a>';
						}
						die($output);
						break;
				}
			}

		}

		public function name($args) {

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

		public function recommend($args) {

			if(isset($args[1]) && !empty($args[1]) && isset($args[2]) && !empty($args[2])) {
				switch($args[1]) {
					case 'event':
						return $this->recommend_event($args);
					case 'job':
						return $this->recommend_job($args);
					case 'request':
						return $this->recommend_request($args);

				}
			}

			echo 'Sorry, we could not complete this action. Please reload the page and try again.';
			die();

		}

		private function recommend_event($args) {

			$err = false;
			if(!isset($args[2]) || empty($args[2])) {
				$err = 'Sorry, the event you requested was not found.';
			} else {
				$event = Ndoorse_Event::loadEvent($args[2]);
				if(!$event instanceof Ndoorse_Event) {
					if($event == Ndoorse_Event::ERR_LOAD_NOTFOUND) {
						$err = 'Sorry, the event you requested was not found.';
					} else {
						$err = 'Sorry, you do not have permission to view this event.';
					}
				}
			}

			if(!$err) {
				echo RecommendationControl::render($event->getID(), 'events');
			} else {
				echo '<p class="page-message page-error">' . $err . '</p>';
				echo '<p><button type="button" class="cancel"><span>Close</span></button></p>';
			}

		}

		private function recommend_job($args) {

			$this->loggedIn();

			if(!isset($args[2]) || empty($args[2])) {
				echo 'An error occurred opening this job opportunity. Please reload the page and try again.';
				echo '<p><button type="button" class="cancel"><span>Close</span></button></p>';
				die();
			}

			$job = new Ndoorse_Job($args[2]);
			if(!$job->getID()) {
				echo 'Sorry, the job you requested was not found.';
				echo '<p><button type="button" class="cancel"><span>Close</span></button></p>';
				die();
			}

			$recommendForm = RecommendationControl::render($job->getID(), 'jobs');
			echo $recommendForm;

		}

		private function recommend_request($args) {

			$this->loggedIn();

			if(!isset($args[2]) || empty($args[2])) {
				echo 'An error occurred opening this request. Please reload the page and try again.';
				echo '<p><button type="button" class="cancel"><span>Close</span></button></p>';
				die();
			}

			$request = new Ndoorse_Request($args[2]);
			if(!$request->getID()) {
				echo 'Sorry, the request you chose was not found.';
				echo '<p><button type="button" class="cancel"><span>Close</span></button></p>';
				die();
			}

			$recommendForm = RecommendationControl::render($request->getID(), 'requests');
			echo $recommendForm;

		}

		public function loadExperience($args) {

			if(!isset($args['expID'])) {
				die();
			}

			$experience = new Ndoorse_Experience($args['expID']);
			$data = $experience->toArray();

			$data['startDate'] = substr($data['startDate'], 0, 10);
			$data['endDate'] = substr($data['endDate'], 0, 10);

			echo json_encode($data);
			die();

		}

		public function loadEducation($args) {

			if(!isset($args['eduID'])) {
				die();
			}

			$education = new Ndoorse_Education($args['eduID']);
			$data = $education->toArray();

			$data['startDate'] = substr($data['startDate'], 0, 10);
			$data['endDate'] = substr($data['endDate'], 0, 10);

			echo json_encode($data);
			die();

		}

		public function profile($args) {
			if(!isset($args['section'])) {
				die('Sorry, we could not save your update. Please reload the page and try again.');
			}

			switch($args['section']) {
				case 'image':

					break;
				case 'details':

					break;
				case 'address':

					break;
				case 'contact':

					break;
				case 'experience':

					break;
				case 'education':

					break;
				case 'contactprefs':

					break;



			}


		}

	}
?>