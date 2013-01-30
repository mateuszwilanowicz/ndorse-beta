<?php
	class ShareJobControl {
		
		public static $signupFormRules = array(
			'respondent' => array('required' => 'true'),
		);

		public static function render($args, $fieldErrors = array()) {
			
			if(!isset($args['respondent'])) $args['respondent'] = '';
					
			$connectForm = new FormControl(RECRUITERS_URL . 'share/post/','Share Form','ShareFormClass','POST');
			//$connectForm->setFieldErrors($fieldErrors);
			$connectForm->textbox('respondent', 'Share with a recruiter', $args['respondent']);
			$connectForm->hidden('userID');
			$connectForm->submit('submit', 'Share');
			
			return $connectForm->render();
			
			
		}
	}
?>