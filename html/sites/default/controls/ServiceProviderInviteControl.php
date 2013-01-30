<?php
	class ServiceProviderInviteControl {

		public static $inviteFormRules = array(
			'respondent' => array('required' => 'true'),
		);

		public static function render($args, $inviteFormErrors = array()) {

			if(!isset($args['respondent'])) $args['respondent'] = '';
                      
			$connectForm = new FormControl(BASE_URL.'serviceproviders/invite/'.$args[1].'/','Connect Form','ConnectFormClass','POST');
			$connectForm->setFieldErrors($inviteFormErrors);
			$connectForm->textbox('respondent', 'Invite a member', $args['respondent']);
			$connectForm->hidden('userID');
			$connectForm->submit('submit', 'Invite');

			return $connectForm->render();


		}
	}
?>