<?php
	class ConnectControl {

		public static $signupFormRules = array(
			'respondent' => array('required' => 'true'),
		);

		public static function render($args, $fieldErrors = array()) {

			if(!isset($args['respondent'])) $args['respondent'] = '';

			$connectForm = new FormControl(BASE_URL.'members/connect/','Connect Form','ConnectFormClass','POST');
			//$connectForm->setFieldErrors($fieldErrors);
			$connectForm->textbox('respondent', 'Connect to a Ndoorse member', $args['respondent']);
			$connectForm->hidden('userID');
			$connectForm->submit('submit', 'Connect');

			return $connectForm->render();


		}
	}
?>