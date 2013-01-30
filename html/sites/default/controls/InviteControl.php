<?php
	class InviteControl {
		
		public static $inviteFormRules = array(
			'email' => array('validator' => 'email', 'required' => 'true')
		);

		public static function render($args, $fieldErrors = array()) {
			if(!isset($args['email'])) $args['email'] = '';
			//pr($fieldErrors);
			$inviteForm = new FormControl(BASE_URL.'members/invite/','Invite Form','InviteFormClass','POST');
			$inviteForm->setFieldErrors($fieldErrors);
			$inviteForm->textbox('email', 'Invite a Ndoorse member', $args['email']);
			$inviteForm->submit('submit', 'Invite');
			
			return $inviteForm->render();
		}
	}
?>		