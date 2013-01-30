<?php
	class ExperienceEditControl {
		
		public static function render($experience,$skills) {
			
			$experienceForm = "<div id='experience_'".$experience->experienceID.">";
			
			$experienceForm .= "<label>Start Year</label><input type='text' value='".$experience->year."' name='year'></input><br/>";
			$experienceForm .= "<label>Duration</label><input type='text' value='".$experience->duration."' name='duration'></input><br/>";
			$experienceForm .= "<label>Job Title</label><input type='text' value='".$experience->jobTitle."' name='jobTitle'></input><br/>";
			$experienceForm .= "<label>Company Name</label><input type='text' value='".$experience->companyName."' name='companyName'></input><br/>";
			$experienceForm .= "<label>Description</label><input type='text' value='".$experience->description."' name='description'></input><br/>";
			$experienceForm .= "<label>Skills</label><input type='text' value='".$skills."' name='skills'></input><br/>";
			$experienceForm .= "<input type='hidden' value='".$experience->experienceID."' name='experience_id'></input>";
			$experienceForm .= "</div>";
			
			return $experienceForm;
			
		}
	}

?>