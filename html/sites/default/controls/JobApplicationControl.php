<?php
	class JobApplicationControl {

		public static $applicationRules = array(
				'email' => array('validator' => 'email', 'required' => 'true', 'maxlength' => 100)
		);

		public static function render($job, $fieldErrors = array()) {

			$cvs = Ndoorse_Document::getDocuments('cv');
			$cvOptions = array();
			foreach($cvs as $cv) {
				$cvOptions[$cv->getID()] = array('label'=>'CV uploaded on ' . date('d/m/Y \a\t H:i', strtotime($cv->dateUploaded)),
					'additional'=>'(<a href="' . BASE_URL . 'member/document/preview/' . $cv->getID() . '/" target="_blank" class="previewlink">preview</a>)');
			}
			$cvOptions[0] = array('label'=>'Upload a new CV');
			if(count($cvOptions) == 1) {
				$cvOptions[0]['checked'] = true;
			}

			$form = new FormControl(BASE_URL . 'jobs/apply/' . $job->getID() . '/');


			$form->textarea('cover', 'Cover text: (optional)');

			if(count($cvOptions) > 1) {
				$form->radio('existingCV', 'Use an existing CV:', $cvOptions);
			}
			$form->file('cv', 'Upload CV');
			$form->textbox('cv_name', 'CV Title', 'CV uploaded on ' . date('d/m/Y'));
			$form->textbox('email', 'Confirm the best email address to contact you:', $_SESSION['user']->email);

			$form->setFieldErrors($fieldErrors);
			$form->html('<div class="buttonbar">');
			$form->checkbox('confirm', 'Yes, I want to send my profile, CV and contact details to the recruiter for this role.');
			$form->submit('send', 'Send Application');
			$form->html('<a href="' . BASE_URL . 'jobs/view/' . $job->getID() . '/" class="button cancel"><span>Cancel</span></a></div>');

			return $form->render();

		}

	}
?>