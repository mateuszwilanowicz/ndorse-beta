<?php
	class NavControl {

		public static function render() {

			if(isset($_SESSION['user']) && $_SESSION['user'] instanceof Ndoorse_Member && $_SESSION['user']->getID()) {
			return '<span class="menu-start"></span>' .
					'<a href="' . BASE_URL . '">Home</a>' .
				'<a href="' . BASE_URL . 'members/profile/">Profile</a>'
				. '<a href="' . BASE_URL . 'jobs/">Jobs</a>'
				. '<a href="' . BASE_URL . 'events/">Events</a>'
				. '<a href="' . BASE_URL . 'requests/">Requests</a>'
				. '<a href="' . BASE_URL . 'serviceproviders/">Service Providers</a>'
				. '<a href="' . BASE_URL . 'members/upgrade/">Upgrade</a>'
				. '<span class="menu-end"></span>';
			} else {
				return '<span class="menu-start"></span>' .
					'<a href="' . BASE_URL . '">Home</a>'
					. '<span class="menu-end"></span>';
			}
		}

		public static function recruiter() {
			// temp navigation
			if(isset($_SESSION['user']) && $_SESSION['user'] instanceof Ndoorse_Member) {
				$numMessages = '(' . Ndoorse_Message::getMessageCount('new') . ')';
				//TO BE REPLACED WITH NEWLY SHARED JOBS!
				$numJobs = '(' . Ndoorse_Job::getJobs(true, Ndoorse_Job::STATUS_AWAITINGAPPROVAL) . ')';
			} else {
				$numMessages = '';
				$numUsers = '';
				$numJobs = '';
			}


			return '
			<div class="menu">
                <span class="menu-start"></span><a href="' . RECRUITERS_URL . '">Home</a><a href="' . BASE_URL . 'messages/">Messages ' .
					$numMessages
				. '</a><a href="' . RECRUITERS_URL . 'jobs/">Jobs</a><a href="' . BASE_URL . 'requests/">Requests</a>
            </div>
			';


		}

		public static function admin() {
			// temp navigation
			if(isset($_SESSION['user']) && $_SESSION['user'] instanceof Ndoorse_Member) {
				$numMessages = '(' . Ndoorse_Message::getMessageCount('new') . ')';
				$numUsers = '(' . Ndoorse_Member::getMembers(true, User::STATUS_PENDING) . ')';
				$numJobs = '(' . Ndoorse_Job::getJobs(true, Ndoorse_Job::STATUS_AWAITINGAPPROVAL, false) . ')';
				$numEvents = '(' . Ndoorse_Event::getEventCount(Ndoorse_Event::STATUS_PENDING) . ')';
				$numRequests = '(' . Ndoorse_Request::getRequests(true, Ndoorse_Request::STATUS_PENDING) . ')';
			} else {
				$numMessages = $numUsers = $numJobs = $numEvents = $numRequests ='';
			}


			return '
			<div class="menu">
				<span class="menu-start"></span><a href="' . ADMIN_URL . '">Home
					</a><a href="' . BASE_URL . 'messages/">Messages ' .
					$numMessages
				. '</a><a href="' . ADMIN_URL . 'members/">Members ' .
					$numUsers
				. '</a><a href="' . ADMIN_URL . 'jobs/">Jobs ' .
					$numJobs
				. '</a><a href="' . ADMIN_URL . 'events/">Events ' .
					$numEvents
				. '</a><a href="' . ADMIN_URL . 'requests/">Requests ' .
					$numRequests
				. '</a><a href="' . ADMIN_URL . 'upgrades/">Upgrades
					</a><span class="menu-end"></span>
			</div>
			';


		}

		public static function accountbar() {

			if(isset($_SESSION['user']) && $_SESSION['user'] instanceof Ndoorse_Member && $_SESSION['user']->getID()) {
				$numMessages = Ndoorse_Message::getMessageCount('new');
				switch($numMessages) {
					case 0:
						$numMessages = 'No Messages';
						break;
					case 1:
						$numMessages = '1 New Message';
						break;
					default:
						$numMessages = $numMessages . ' New Messages';
				}
				return '
				<span class="account-user-name">Welcome ' . $_SESSION['user']->firstname . '
					(<a href="' . BASE_URL . 'logout/">log out</a>)
				</span>
				<span class="account-messages">
					<a href="' . BASE_URL . 'messages/">' . $numMessages . '</a>
				</span>
				';
			} else {
				return '
				<span class="account-login">
					<a href="' . BASE_URL . '">Log In</a>
				</span>
				<span class="account-signup">
					<a href="' . BASE_URL . 'signup">Sign Up</a>
				</span>
				';
			}

		}

	}
?>