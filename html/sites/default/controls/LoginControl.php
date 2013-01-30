<?php
	class LoginControl {

		public static $signupFormRules = array(
			'username' => array('validator' => 'email', 'required' => 'true'),
			'password' => array('required' => 'true'),
		);

		public static function render($args, $fieldErrors = array()) {

			if(!isset($args['username'])) $args['username'] = '';

			$loginForm = new FormControl(BASE_URL,'Login Form','LoginFormClass','POST');
			$loginForm->setFieldErrors($fieldErrors);
			$loginForm->textbox('username', 'Email:',$args['username']);
			$loginForm->password('password', 'Password:');
			$loginForm->html('<div class="buttonbar">');
			$loginForm->submit('submit', 'Sign in');
			$loginForm->html('<a href="/signup" target="_self" class="button signup"><span>Sign Up</span></a></div>');
			// is BAD !!! $loginForm->button('signup','Sign up','button');
			$loginForm->checkbox('remember', 'Remember my details on this computer');

			return $loginForm->render();


		}
	}
?>