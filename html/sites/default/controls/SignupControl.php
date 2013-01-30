<?php
	class SignupControl {

		public static $signupFormRules = array(
			'firstname' => array('validator' => 'firstanme', 'required' => 'true'),
			'lastname' => array('validator' => 'lastname', 'required' => 'true'),
			'email' => array('validator' => 'email', 'required' => 'true', 'maxlength' => 90),
			'confirmemail' => array('required' => 'true', 'matches' => 'email'),
			'password' => array('required' => 'true', 'minlength' => 6),
			'firstname' => array('required' => 'true', 'maxlength' => 45),
			'lastname' => array('required' => 'true', 'maxlength' => 45),
			'password' => array('minlength' => 6),
			'confirmpassword' => array('required' => 'true', 'matches' => 'password')
		);

		public static function render($args, $fieldErrors = array()) {
			if(!isset($args['referrerID'])) $args['referrerID'] = '';
			if(!isset($args['firstname'])) $args['firstname'] = '';
			if(!isset($args['lastname'])) $args['lastname'] = '';
			if(!isset($args['email'])) $args['email'] = '';
			if(!isset($args['confirmemail'])) $args['confirmemail'] = '';
			if(!isset($args['password'])) $args['password'] = '';
			if(!isset($args['confirmpassword'])) $args['confirmpassword'] = '';


			$signupForm = new FormControl('/signup/','Signup Form','SignupFormClass','POST');
			$signupForm->setFieldErrors($fieldErrors);
			if(!isset($args['key']))
				$signupForm->textbox('referrerID', 'Code or Email of the person referring you:', $args['referrerID']);
			$signupForm->textbox('firstname','First Name', $args['firstname']);
			$signupForm->textbox('lastname','Last Name', $args['lastname']);
			if(!isset($args['key'])) {
				$signupForm->textbox('email','Email', $args['email']);
				$signupForm->textbox('confirmemail','Confirm Email', $args['confirmemail']);
			} else {
				$signupForm->fixed('email','Email', $args['email']);
				$signupForm->hidden('email', $args['email']);
				$signupForm->hidden('confirmemail', $args['confirmemail']);
			}
			$signupForm->password('password', 'Password', $args['password']);
			$signupForm->password('confirmpassword', 'Confirm Password', $args['confirmpassword']);

			$signupForm->html('<div class="buttonbar">');
			$signupForm->submit('submit', 'Sign up');
			$signupForm->html('</div>');

			return $signupForm->render();
		}
	}
?>