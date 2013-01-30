<?php
	class MessageControl {

		public static function render($subject = '', $recipients = array(), $referer = '', $replyTo = 0) {

			if(!empty($recipients)) {
				$recipients = Ndoorse_Message::getRecipientsFromList($recipients);
				$recipientNames = Ndoorse_Message::getRecipientNames($recipients);
			}

			if(empty($referer)) {
				$referer = BASE_URL . 'messages/inbox/';
			}

			$form = new FormControl(BASE_URL . 'messages/send/', 'messageform');

			$form->hidden('referer', $referer);
			$form->hidden('replyToID', $replyTo);
			if(isset($recipientNames)) {
				$form->hidden('userID', implode(', ', $recipients));
				$form->fixed('to', 'To:', implode(', ', $recipientNames));
			} else {
				$form->textbox('to', 'To:');
				$form->hidden('userID');
			}
			$form->textbox('subject', 'Subject:', isset($_REQUEST['subject']) ? $_REQUEST['subject'] : (empty($subject) ? '' : 'RE: ' . $subject));
			$form->textarea('message', 'Message:', isset($_REQUEST['message']) ? $_REQUEST['message'] : '');

			$form->html('<div class="buttonbar">');
			$form->submit('send', 'Send');
			$form->html('<a class="button cancel" href="' . BASE_URL . 'messages/"><span>Cancel</span></a></div>');

			return $form->render();

		}


	}

?>