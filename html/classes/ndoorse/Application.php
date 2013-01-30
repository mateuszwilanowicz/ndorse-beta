<?php
	class Ndoorse_Application extends Model {

		protected $applicationID;
		protected $jobID;
		protected $userID;

		protected $email;
		protected $cv;
		protected $cover;

		protected $status;

		const STATUS_WITHDRAWN = 0;
		const STATUS_UNREAD = 1;
		const STATUS_READ = 2;
		const STATUS_SHARED = 3;



		public function save() {

			if(empty($this->jobID) || empty($this->userID)) {
				throw new Exception('Ndoorse/Application/Save: No userID or jobID');
			}

			return $this->saveModel();

		}
	}
?>