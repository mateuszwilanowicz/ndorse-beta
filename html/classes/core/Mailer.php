<?php
	class Mailer {

		public static function forgot_password($participant, $link) {

			$content_txt = file_get_contents(SITE_TEMPLATES . 'email/forgot_password.txt');
			$content_txt = str_replace('%firstname%', ($participant->getFirstname() == '' ? $participant->getTitle() . ' ' . $participant->getLastname() : $participant->getFirstname()), $content_txt);
			$content_txt = str_replace('%link%', $link, $content_txt);

			$content_html = file_get_contents(SITE_TEMPLATES . 'email/forgot_password.html');
			$content_html = str_replace('%firstname%', ($participant->getFirstname() == '' ? $participant->getTitle() . ' ' . $participant->getLastname() : $participant->getFirstname()), $content_html);
			$content_html = str_replace('%link%', $link, $content_html);
			$content_html = str_replace('%siteimg%', SITE_IMAGES , $content_html);

			$email = new EmailMessage();

			$email->setSubject(SITE_NAME . ': Password Reset');
			$email->setSenderEmailAddress(EMAIL_FROM, EMAIL_FROM_NAME);

			$emails = explode("\n", $participant->getEmail());
			foreach($emails as $emailaddr) {
				$emailaddr = trim($emailaddr, "\r ");
				$email->addRecipientEmailAddress($emailaddr);
			}
			$email->setTextContent($content_txt);
			$email->setHtmlContent($content_html);

			$result = self::send($email);
			if($result) {
				Logger::log('Sent password reset e-mail to user ' . $participant->getSalutation());
			} else {
				pr($result);
				trigger_error('Password e-mail could not be sent to user ' . $participant->getSalutation());
			}
		}

		public static function accepted_invitation($participant) {

			$content_txt = file_get_contents(SITE_TEMPLATES . 'email/accepted_invitation.txt');
			$content_txt = str_replace('%firstname%', ($participant->getFirstname() == '' ? $participant->getTitle() . ' ' . $participant->getLastname() : $participant->getFirstname()), $content_txt);
			$content_txt = str_replace('%link%', $GLOBALS['sitebase'], $content_txt);


			$content_html = file_get_contents(SITE_TEMPLATES . 'email/accepted_invitation.html');
			$content_html = str_replace('%firstname%', ($participant->getFirstname() == '' ? $participant->getTitle() . ' ' . $participant->getLastname() : $participant->getFirstname()), $content_html);
			$content_html = str_replace('%link%', $GLOBALS['sitebase'], $content_html);
			$content_html = str_replace('%siteimg%', SITE_IMAGES , $content_html);

			$email = new EmailMessage();

			$email->setSubject(SITE_NAME . ': Thank You');
			$email->setSenderEmailAddress(EMAIL_FROM, EMAIL_FROM_NAME);
			$emails = explode("\n", $participant->getEmail());
			foreach($emails as $emailaddr) {
				$emailaddr = trim($emailaddr, "\r ");
				$email->addRecipientEmailAddress($emailaddr);
			}
			$email->setTextContent($content_txt);
			$email->setHtmlContent($content_html);

			if(self::send($email)) {
				Logger::log('Sent acceptance confirmation e-mail to user ' . $participant->getSalutation());
			} else {
				trigger_error('Acceptance email could not be sent to user ' . $participant->getSalutation());
			}

			$content_txt = file_get_contents(SITE_TEMPLATES . 'email/accepted_invitation_admin.txt');
			$content_txt = str_replace('%name%', $participant->getSalutation(), $content_txt);
			$content_txt = str_replace('%email%', $participant->getEmail(), $content_txt);
			$content_txt = str_replace('%payment%',  $participant->getPaymentName(), $content_txt);

			$content_html = file_get_contents(SITE_TEMPLATES . 'email/accepted_invitation_admin.html');
			$content_html = str_replace('%name%', $participant->getSalutation(), $content_html);
			$content_html = str_replace('%email%', $participant->getEmail(), $content_html);
			$content_html = str_replace('%payment%',  $participant->getPaymentName(), $content_html);
			$content_html = str_replace('%siteimg%', SITE_IMAGES , $content_html);

			$email = new EmailMessage();

			$email->setSubject(SITE_NAME . ': Accepted Invitation');
			$email->setSenderEmailAddress(EMAIL_FROM, EMAIL_FROM_NAME);
			$adminUsers = explode(',', ADMIN_EMAIL);
			foreach($adminUsers as $adEmail) {
				$email->addRecipientEmailAddress($adEmail);
			}
			$email->setTextContent($content_txt);
			$email->setHtmlContent($content_html);

			if(self::send($email)) {
				Logger::log('Sent acceptance confirmation e-mail for ' . $participant->getSalutation() . ' to admin.');
			} else {
				trigger_error('Acceptance confirmation email for ' . $participant->getSalutation() . ' could not be sent to admin.');
			}

		}

		public static function declined_invitation($participant) {

			$content_txt = file_get_contents(SITE_TEMPLATES . 'email/declined_invitation.txt');
			$content_txt = str_replace('%firstname%', ($participant->getFirstname() == '' ? $participant->getTitle() . ' ' . $participant->getLastname() : $participant->getFirstname()), $content_txt);
			$content_txt = str_replace('%link%', SITE_URL, $content_txt);

			$content_html = file_get_contents(SITE_TEMPLATES . 'email/declined_invitation.html');
			$content_html = str_replace('%firstname%', ($participant->getFirstname() == '' ? $participant->getTitle() . ' ' . $participant->getLastname() : $participant->getFirstname()), $content_html);
			$content_html = str_replace('%link%', SITE_URL, $content_html);
			$content_html = str_replace('%siteimg%', SITE_IMAGES , $content_html);

			$email = new EmailMessage();

			$email->setSubject(SITE_NAME . ': Thank You');
			$email->setSenderEmailAddress(EMAIL_FROM, EMAIL_FROM_NAME);
			$emails = explode("\n", $participant->getEmail());
			foreach($emails as $emailaddr) {
				$emailaddr = trim($emailaddr, "\r ");
				$email->addRecipientEmailAddress($emailaddr);
			}
			$email->setTextContent($content_txt);
			$email->setHtmlContent($content_html);

			if(self::send($email)) {
				Logger::log('Sent decline confirmation e-mail to user ' . $participant->getSalutation());
			} else {
				trigger_error('Decline email could not be sent to user ' . $participant->getSalutation());
			}

			$content_txt = file_get_contents(SITE_TEMPLATES . 'email/declined_invitation_admin.txt');
			$content_txt = str_replace('%name%', $participant->getSalutation(), $content_txt);
			$content_txt = str_replace('%email%', $participant->getEmail(), $content_txt);
			$content_html = file_get_contents(SITE_TEMPLATES . 'email/declined_invitation_admin.html');
			$content_html = str_replace('%name%', $participant->getSalutation(), $content_html);
			$content_html = str_replace('%email%', $participant->getEmail(), $content_html);
			$content_html = str_replace('%siteimg%', SITE_IMAGES , $content_html);

			$email = new EmailMessage();

			$email->setSubject(SITE_NAME . ': Declined Invitation');
			$email->setSenderEmailAddress(EMAIL_FROM, EMAIL_FROM_NAME);
			$adminUsers = explode(',', ADMIN_EMAIL);
			foreach($adminUsers as $adEmail) {
				$email->addRecipientEmailAddress($adEmail);
			}
			$email->setTextContent($content_txt);
			$email->setHtmlContent($content_html);

			if(self::send($email)) {
				Logger::log('Sent decline confirmation e-mail for ' . $participant->getSalutation() . ' to admin.');
			} else {
				trigger_error('Decline confirmation email for ' . $participant->getSalutation() . ' could not be sent to admin.');
			}

		}

		public static function submitted($participant) {

			$content_txt = file_get_contents(SITE_TEMPLATES . 'email/submitted.txt');
			$content_txt = str_replace('%firstname%', ($participant->getFirstname() == '' ? $participant->getTitle() . ' ' . $participant->getLastname() : $participant->getFirstname()), $content_txt);
			$content_txt = str_replace('%link%', $GLOBALS['sitebase'], $content_txt);


			$content_html = file_get_contents(SITE_TEMPLATES . 'email/submitted.html');
			$content_html = str_replace('%firstname%', ($participant->getFirstname() == '' ? $participant->getTitle() . ' ' . $participant->getLastname() : $participant->getFirstname()), $content_html);
			$content_html = str_replace('%link%', $GLOBALS['sitebase'], $content_html);
			$content_html = str_replace('%siteimg%', SITE_IMAGES , $content_html);

			$email = new EmailMessage();

			$email->setSubject(SITE_NAME . ': Thank you for participating in the Alfred Dunhill Links Championship');
			$email->setSenderEmailAddress(EMAIL_FROM, EMAIL_FROM_NAME);
			$emails = explode("\n", $participant->getEmail());
			foreach($emails as $emailaddr) {
				$emailaddr = trim($emailaddr, "\r ");
				$email->addRecipientEmailAddress($emailaddr);
			}
			$email->setTextContent($content_txt);
			$email->setHtmlContent($content_html);

			if(self::send($email)) {
				Logger::log('Sent submission e-mail to user ' . $participant->getSalutation());
			} else {
				trigger_error('Submission email could not be sent to user ' . $participant->getSalutation());
			}

			$adminLink = SITE_URL . 'admin/registration/' . $participant->getID() . '/';

			$content_txt = file_get_contents(SITE_TEMPLATES . 'email/submission_admin.txt');
			$content_txt = str_replace('%name%', $participant->getSalutation(), $content_txt);
			$content_txt = str_replace('%email%', $participant->getEmail(), $content_txt);
			$content_html = str_replace('%adminlink%', $adminLink, $content_txt);

			$content_html = file_get_contents(SITE_TEMPLATES . 'email/submission_admin.html');
			$content_html = str_replace('%name%', $participant->getSalutation(), $content_html);
			$content_html = str_replace('%email%', $participant->getEmail(), $content_html);
			$content_html = str_replace('%adminlink%', $adminLink, $content_html);
			$content_html = str_replace('%siteimg%', SITE_IMAGES , $content_html);

			$email = new EmailMessage();

			$email->setSubject(SITE_NAME . ': Registration Submitted');
			$email->setSenderEmailAddress(EMAIL_FROM, EMAIL_FROM_NAME);
			$adminUsers = explode(',', ADMIN_EMAIL);
			foreach($adminUsers as $adEmail) {
				$email->addRecipientEmailAddress($adEmail);
			}
			$email->setTextContent($content_txt);
			$email->setHtmlContent($content_html);

			if(self::send($email)) {
				Logger::log('Sent submission e-mail for ' . $participant->getSalutation() . ' to admin.');
			} else {
				trigger_error('Submission email for ' . $participant->getSalutation() . ' could not be sent to admin.');
			}

		}

		public static function new_invitation($participant, $activation_link, $username, $password) {

			$original_content_txt = file_get_contents(SITE_TEMPLATES . 'email/new_invitation.txt');
			$original_content_html = file_get_contents(SITE_TEMPLATES . 'email/new_invitation.html');

			$email_addresses = $participant->getEmail();
			$email_addresses = explode("\n", $email_addresses);

			$success = true;

			foreach($email_addresses as $this_email) {

				$this_email = trim($this_email, "\r ");

				$content_txt = $original_content_txt;
				$content_txt = str_replace('%firstname%', ($participant->getFirstname() == '' ? $participant->getTitle() . ' ' . $participant->getLastname() : $participant->getFirstname()), $content_txt);
				$content_txt = str_replace('%link%', $activation_link, $content_txt);
				$content_txt = str_replace('%siteurl%', SITE_URL, $content_txt);
				$content_txt = str_replace('%email%', $this_email, $content_txt);
				$content_txt = str_replace('%password%', $password, $content_txt);
				$content_txt = str_replace('%username%', $username, $content_txt);

				$content_html = $original_content_html;
				$content_html = str_replace('%firstname%', ($participant->getFirstname() == '' ? $participant->getTitle() . ' ' . $participant->getLastname() : $participant->getFirstname()), $content_html);
				$content_html = str_replace('%link%', $activation_link, $content_html);
				$content_html = str_replace('%siteurl%', SITE_URL, $content_html);
				$content_html = str_replace('%email%', $this_email, $content_html);
				$content_html = str_replace('%siteimg%', SITE_IMAGES , $content_html);
				$content_html = str_replace('%password%', $password, $content_html);
				$content_html = str_replace('%username%', $username, $content_html);

				$email = new EmailMessage();

				$email->setSubject('Invitation to Play in the Alfred Dunhill Links Championship');
				$email->setSenderEmailAddress(EMAIL_FROM, EMAIL_FROM_NAME);
				$email->addRecipientEmailAddress($this_email);
				$email->setTextContent($content_txt);
				$email->setHtmlContent($content_html);

				$this_success = self::send($email);
				if(!$this_success) {
					Logger::log('Error when sending invitation e-mail to: ' . $this_email . ' for participant ' . $participant->getID());
				}

				$success = $success || $this_success;

			}

			if($success) {
				Logger::log('Sent invitation e-mail to user ' . $participant->getSalutation());
			} else {
				trigger_error('Invitation email could not be sent to user ' . $participant->getSalutation());
			}

		}

		public static function registration($participant, $activation_link) {

			$original_content_txt = file_get_contents(SITE_TEMPLATES . 'email/registration.txt');
			$original_content_html = file_get_contents(SITE_TEMPLATES . 'email/registration.html');

			$email_addresses = $participant->getEmail();
			$email_addresses = explode("\n", $email_addresses);

			$success = true;

			foreach($email_addresses as $this_email) {

				$this_email = trim($this_email, "\r ");

				$content_txt = $original_content_txt;
				$content_txt = str_replace('%firstname%', ($participant->getFirstname() == '' ? $participant->getTitle() . ' ' . $participant->getLastname() : $participant->getFirstname()), $content_txt);
				$content_txt = str_replace('%link%', $activation_link, $content_txt);
				$content_txt = str_replace('%siteurl%', SITE_URL, $content_txt);
				$content_txt = str_replace('%username%', $participant->getUsername(), $content_txt);

				$content_html = $original_content_html;
				$content_html = str_replace('%firstname%', ($participant->getFirstname() == '' ? $participant->getTitle() . ' ' . $participant->getLastname() : $participant->getFirstname()), $content_html);
				$content_html = str_replace('%link%', $activation_link, $content_html);
				$content_html = str_replace('%siteurl%', SITE_URL, $content_html);
				$content_html = str_replace('%username%', $participant->getUsername(), $content_html);
				$content_html = str_replace('%siteimg%', SITE_IMAGES , $content_html);

				$email = new EmailMessage();

				$email->setSubject('Invitation to Play in the Alfred Dunhill Links Championship');
				$email->setSenderEmailAddress(EMAIL_FROM, EMAIL_FROM_NAME);
				$email->addRecipientEmailAddress($this_email);
				$email->setTextContent($content_txt);
				$email->setHtmlContent($content_html);

				$this_success = self::send($email);
				if(!$this_success) {
					Logger::log('Error when sending registration e-mail to: ' . $this_email . ' for participant ' . $participant->getID());
				}

				$success = $success || $this_success;

			}

			if($success) {
				Logger::log('Sent invitation e-mail to user ' . $participant->getSalutation());
			} else {
				trigger_error('Invitation email could not be sent to user ' . $participant->getSalutation());
			}



		}

		public static function custom_mail($message_txt, $message_html, $subject, $to, $bcc) {

			$content_txt = $message_txt;

			$content_html = file_get_contents(SITE_TEMPLATES . 'email/email_header.html');
			$content_html .= $message_html;
			$content_html .= file_get_contents(SITE_TEMPLATES . 'email/email_footer.html');
			$content_html = str_replace('%siteimg%', SITE_IMAGES , $content_html);

			$email = new EmailMessage();

			$email->setSubject(SITE_NAME . ': ' . $subject);
			$email->setSenderEmailAddress(EMAIL_FROM, EMAIL_FROM_NAME);

			foreach($to as $emailaddr) {
				$emailaddr = trim($emailaddr, "\r ");
				$email->addRecipientEmailAddress($emailaddr);
			}
			foreach($bcc as $emailaddr) {
				$email->addBccEmailAddress($emailaddr);
			}
			$email->setTextContent($content_txt);
			$email->setHtmlContent($content_html);

			if(self::send($email)) {
				Logger::log('Sent custom e-mail');
			} else {
				trigger_error('Error while sending custom e-mails');
			}

		}

		public static function send($email) {

			//return true;

			
			$emailTransport = new EmailTransport();
			
			return $emailTransport->sendMessage($email);

		}

	}
?>