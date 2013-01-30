
<?php
	class EducationEditControl {
		
		public static function render($education, $skills) {
			
			$educationForm = "<div id='education_'".$education->educationID.">";
			
			$educationForm .= "<label>Graduation Year</label><input type='text' value='".$education->year."' name='year'></input><br/>";
			$educationForm .= "<label>Institution</label><input type='text' value='".$education->institution."' name='institution'></input><br/>";
			$educationForm .= "<label>Course Name</label><input type='text' value='".$education->courseName."' name='courseName'></input><br/>";
			$educationForm .= "<label>Degree</label><input type='text' value='".$education->description."' name='description'></input><br/>";
			$educationForm .= "<label>Skills</label><input type='text' value='".$skills."' name='skills'></input><br/>";
			$educationForm .= "<input type='hidden' value='".$education->educationID."' name='education_id'></input><br/>";
			$educationForm .= "</div>";
			
			return $educationForm;
			
		}
	}

?>
