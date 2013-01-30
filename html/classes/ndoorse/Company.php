<?php
	class Ndoorse_Company extends Model {

		protected $companyID;
		protected $name;
		protected $description;
		protected $logoURL;

		protected $status;

		const STATUS_INACTIVE = 0;
		const STATUS_PENDING = 1;
		const STATUS_ACTIVE = 2;




	}
?>