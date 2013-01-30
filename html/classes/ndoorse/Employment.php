<?php
	class Ndoorse_Employment extends Model {
		
		protected $employmentID;
		protected $memberID;				
		protected $name;
		protected $role;
		protected $sallary;
		protected $location;
		protected $startDate;
		protected $endtDate;
				
		public function years() {
			//return the amout of years spent in the position
		}
	}
?>