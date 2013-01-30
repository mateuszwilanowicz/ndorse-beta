<?php
	class RequestEditControl {

		public static $validationRules = array('summary'=>array('required'=>true));

		public static function render($request, $formErrors = array()) {

			$offerings = array(array('label'=>'Looking for'), array('label'=>'Offering'));
			if($request->offering) {
				$offerings[1]['checked'] = true;
			} else {
				$offerings[0]['checked'] = true;
			}


			$form = new FormControl(BASE_URL . 'requests/post/');
			$form->hidden('requestID', $request->getID());

			$form->radio('offering', 'This request is:', $offerings);
			$form->textbox('summary', 'Summary:', $request->summary);
			$form->textarea('description', 'Description:', $request->description);
			$form->textbox('location', 'Location:', $request->location);
			$form->hidden('locationID', $request->locationID);
			$form->checkbox('type_advice', 'Advice', $request->type & Ndoorse_Request::TYPE_ADVICE == Ndoorse_Request::TYPE_ADVICE, Ndoorse_Request::TYPE_ADVICE);
			$form->checkbox('type_help', 'Help', $request->type & Ndoorse_Request::TYPE_HELP == Ndoorse_Request::TYPE_HELP, Ndoorse_Request::TYPE_HELP);
			$form->checkbox('type_introduction', 'Introduction', $request->type & Ndoorse_Request::TYPE_INTRODUCTION == Ndoorse_Request::TYPE_INTRODUCTION, Ndoorse_Request::TYPE_INTRODUCTION);
			$form->checkbox('type_mentoring', 'Mentoring', $request->type & Ndoorse_Request::TYPE_MENTORING == Ndoorse_Request::TYPE_MENTORING, Ndoorse_Request::TYPE_MENTORING);
			$form->datepicker('dateExpires', 'Expiry date:', $request->getExpiryDate());
			$form->checkbox('board', 'Post on ndoorse Request board', $request->board, 1);
			$form->checkbox('anonymous', 'Do not show my name in this request', $request->anonymous, 1);
			$form->checkbox('match', 'Match request to relevant profiles', true, 1);

			$form->html('<div class="buttonbar">');
			$form->submit('submit', 'Submit Request');
			$form->html('<a href="' . BASE_URL . 'requests/" class="button cancel"><span>Cancel</span></a></div>');

			if(!empty($formErrors)) {
				$form->setFieldErrors($formErrors);
			}

			return $form->render();

		}

	}
?>