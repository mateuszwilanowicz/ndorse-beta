<?php
	if (!function_exists("quoted_printable_encode")) {
	  /**
	  * Process a string to fit the requirements of RFC2045 section 6.7. Note that
	  * this works, but replaces more characters than the minimum set. For readability
	  * the spaces and CRLF pairs aren't encoded though.
	  */
	  function quoted_printable_encode($string) {
	    return preg_replace('/[^\r\n]{73}[^=\r\n]{2}/', "$0=\r\n",
	      str_replace("%", "=", str_replace("%0D%0A", "\r\n",
	        str_replace("%20"," ",rawurlencode($string)))));
	  }
	}

	/**
	 * Started work on an email class. Decided to use a PEAR clas instead.
	 * This is kept here for now, just in case.
	 */
	class EmailTransport {

		private static $LINE_END = "\r\n";
		private static $TRANSFER_ENCODING_7BIT = "7bit";
		private static $TRANSFER_ENCODING_QUOTED_PRINTABLE = "quoted-printable";

		private $useMail = true;
		private $smtpHostname;
		private $smtpPort = 25;

		private $multipartBoundary;

		public function EmailTransport($smtpHostname = "", $smtpPort = "") {
			if (!empty($smtpHostname) && !empty($smtpPort)) {
				$this->smtpHostname = $smtpHostname;
				$this->smtpPort = $smtpPort;
			} else {
				$this->useMail = true;
			}
			$this->multipartBoundary = "----- " . md5(uniqid()) . " -----";
		}

		public function sendMessage(EmailMessage $emailMessage) {
			if (sizeof($emailMessage->getRecipientEmailAddresses()) == 0)
				throw new EmailException("Unable to send email, no recipients defined.");

			$headers = $emailMessage->getHeaders();
			$subject = $emailMessage->getSubject();
			if (empty($subject))
				throw new EmailException('Unable to send email. No subject set.');

			$headers['Subject'] = $subject;
			$senderEmailAddress = $emailMessage->getSenderEmailAddress();

			if (empty($senderEmailAddress))
				throw new EmailException('Unable to send email. No sender email addess set.');

			$headers['From'] = (!empty($senderEmailAddress[1]) ? $senderEmailAddress[1] . ' <' . $senderEmailAddress[0] . '>' :
					$senderEmailAddress[0]);

					$recipientEmailAddresses = array();
			foreach ($emailMessage->getRecipientEmailAddresses() as $recipientEmailAddress) {
				$recipientEmailAddresses[] = (!empty($recipientEmailAddress[1]) ? $recipientEmailAddress[1] . ' <' . $recipientEmailAddress[0] . '>' :
						$recipientEmailAddress[0]);
			}
			if (sizeof($recipientEmailAddresses) == 0)
				throw new EmailException('Unable to send email. No recipient email addess set.');

			$ccEmailAddresses = array();
			foreach ($emailMessage->getCcEmailAddresses() as $ccEmailAddress) {
				$ccEmailAddresses[] = (!empty($ccEmailAddress[1]) ? $ccEmailAddress[1] . ' <' . $ccEmailAddress[0] . '>' :
						$ccEmailAddress[0]);
			}

			$bccEmailAddresses = array();
			foreach ($emailMessage->getBccEmailAddresses() as $bccEmailAddress) {
				$bccEmailAddresses[] = (!empty($bccEmailAddress[1]) ? $bccEmailAddress[1] . ' <' . $bccEmailAddress[0] . '>' :
						$bccEmailAddress[0]);
			}

			$textContent = $emailMessage->getTextContent();
			$htmlContent = $emailMessage->getHtmlContent();


			if($this->useMail) {

				$recipientEmailAddresses = implode(',', $recipientEmailAddresses);
				$ccEmailAddresses = implode(',', $ccEmailAddresses);

				if (strlen($ccEmailAddresses) > 0)
					$headers['Cc'] = $ccEmailAddresses;

				$bccEmailAddresses = implode(',', $bccEmailAddresses);
				if (strlen($bccEmailAddresses) > 0)
					$headers['Bcc'] = $bccEmailAddresses;

				$emailContent= "";

				if (!empty($textContent) && !empty($htmlContent)) {
					// It is a multipart email
					$headers['MIME-Version'] = '1.0';
					$headers['Content-Type'] = 'multipart/alternative; boundary="' . $this->multipartBoundary . '"';

					$emailContent .= '--' . $this->multipartBoundary . self::$LINE_END;
					$emailContent .= 'Content-Type: text/plain; charset=iso-8859-1' . self::$LINE_END;
					$emailContent .= 'Content-Transfer-Encoding: ' . self::$TRANSFER_ENCODING_7BIT . self::$LINE_END;
					$emailContent .= self::$LINE_END;
					$emailContent .= wordwrap($textContent, 70) . self::$LINE_END;
					$emailContent .= self::$LINE_END;
					$emailContent .= '--' . $this->multipartBoundary . self::$LINE_END;
					$emailContent .= 'Content-Type: text/html; charset=iso-8859-1' . self::$LINE_END;
					$emailContent .= 'Content-Transfer-Encoding: ' . self::$TRANSFER_ENCODING_QUOTED_PRINTABLE . self::$LINE_END;
					$emailContent .= self::$LINE_END;
					$emailContent .= quoted_printable_encode($htmlContent) . self::$LINE_END;
					$emailContent .= self::$LINE_END;
					$emailContent .= '--' . $this->multipartBoundary . '--' . self::$LINE_END;
				} else if (!empty($textContent)) {
					// It is text only
					$emailContent .= wordwrap($textContent, 70) . self::$LINE_END;
				} else if (!empty($htmlContent)) {
					// It is HTML only
					$headers['Content-Type'] = 'text/html; charset=iso-8859-1';
					$headers['Content-Transfer-Encoding'] = self::$TRANSFER_ENCODING_QUOTED_PRINTABLE;

					$emailContent .= quoted_printable_encode($htmlContent) . self::$LINE_END;
				}

				$headerString = "";
				foreach ($headers as $header => $value) {
					$headerString .= $header . ': ' . $value . self::$LINE_END;
				}

				return mail($recipientEmailAddresses, $subject, $emailContent, $headerString, '-fpostmaster@groovytrain.com');
				
				

			} else {

				require_once SITE_CLASSES . 'swift/swift_init.php';

				$message = Swift_Message::newInstance();

				$message->setSubject($subject);

				if(count($senderEmailAddress) == 2) {
					$message->setFrom(array($senderEmailAddress[0]=>$senderEmailAddress[1]));
				}

				$message->setTo($recipientEmailAddresses);

				if(!empty($ccEmailAddresses)) {
					$message->setCc($ccEmailAddresses);
				}

				if(!empty($bccEmailAddresses)) {
					$message->setBcc($bccEmailAddresses);
				}

				if(!empty($headers)) {
					$headerSet = $message->getHeaders();
					foreach($headers as $key=>$value) {
						$headerSet->addTextHeader($key, $value);
					}
				}

				$message->setBody($htmlContent, 'text/html');
				$message->addPart($textContent, 'text/plain');

				//$transport = Swift_MailTransport::newInstance();

				$transport = Swift_SmtpTransport::newInstance(SMTP_HOST, SMTP_PORT)->setUsername('invitation@alfreddunhilllinks.com')->setPassword('adlc140710');
				$mailer = Swift_Mailer::newInstance($transport);

				//$logger = new Swift_Plugins_Loggers_EchoLogger(true);
				//$mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));
				return $mailer->send($message, $failures);
			}

		}

	}

	class EmailMessage {

		private $subject;
		private $senderEmailAddress;
		private $recipientEmailAddresses = array();
		private $ccEmailAddresses = array();
		private $bccEmailAddresses = array();
		private $textContent = "";
		private $htmlContent = "";
		private $headers = array();

		public function EmailMessage() {

			$this->senderEmailAddress = array(EMAIL_FROM, EMAIL_FROM_NAME);

		}

		public function getSubject() {
			return $this->subject;
		}

		public function setSubject($subject) {
			$this->subject = $subject;
		}

		public function getSenderEmailAddress() {
			return $this->senderEmailAddress;
		}

		public function setSenderEmailAddress($senderEmailAddress, $name = "") {
			$this->senderEmailAddress = array($senderEmailAddress, $name);
		}

		public function getRecipientEmailAddresses() {
			return $this->recipientEmailAddresses;
		}

		public function addRecipientEmailAddress($recipientEmailAddress, $name = "") {
			$this->recipientEmailAddresses[] = array($recipientEmailAddress, $name);
		}

		public function getCcEmailAddresses() {
			return $this->ccEmailAddresses;
		}

		public function addCcEmailAddress($ccEmailAddress, $name = "") {
			$this->ccEmailAddresses[] = array($ccEmailAddress, $name);
		}

		public function getBccEmailAddresses() {
			return $this->bccEmailAddresses;
		}

		public function addBccEmailAddress($bccEmailAddress, $name = "") {
			$this->bccEmailAddresses[] = array($bccEmailAddress, $name);
		}

		public function getTextContent() {
			return $this->textContent;
		}

		public function setTextContent($textContent) {
			$this->textContent = $textContent;
		}

		public function getHtmlContent() {
			return $this->htmlContent;
		}

		public function setHtmlContent($htmlContent) {
			$this->htmlContent = $htmlContent;
		}

		public function getHeaders() {
			return $this->headers;
		}

		public function addHeader($header, $content) {
			$this->headers[$header] = $content;
		}

		public function loadTemplate($template, $values) {

			if(!file_exists(SITE_PATH . 'emails/' . $template . '.html')) {
				throw new Exception('EmailMessage/loadTemplate: ' . $template . ' does not exist.');
			}

			$message = file_get_contents(SITE_PATH . 'emails/' . $template . '.html');
			foreach($values as $key=>$val) {
				$message = str_replace('%' . $key . '%', $val, $message);
			}

			$this->htmlContent = $message;

		}

		public function send() {

			$emailTransport = new EmailTransport();
			return $emailTransport->sendMessage($this);

		}

	}

	class EmailException extends Exception {

		public function EmailException($message) {
			parent::__construct($message);
		}

	}

?>