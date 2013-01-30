<?php
	class RecommendationControl {

		public static $internalRules = array(
				'recommend_name' => array('required' => 'true', 'maxlength' => 200)
		);
		public static $externalRules = array(
				'recommend_email' => array('validator' => 'email', 'required' => 'true', 'maxlength' => 100)
		);

		public static function render($id, $base, $fieldErrors = array()) {

			if($base == 'events') {
				$action = 'invite';
				$passiveAction = 'Invitation';
			} else {
				$action = 'recommend';
				$passiveAction = 'Recommendation';
			}

			$personTypes = array('network'=>array('label'=>'This person is in my ndoorse network', 'checked'=>true),
								 'remote'=>array('label'=>'This person is not on ndoorse'));

			$form = new FormControl(BASE_URL . $base . ($base == 'events' ? '/invite/' : '/recommend/') . $id . '/', 'recommend_form');

			$form->radio('recommend_personType', '', $personTypes);
			$form->textbox('recommend_email', 'Email address of the person you wish to ' . $action . ':');
			$form->textbox('recommend_name', 'Name of the person you would like to ' . $action . ':');
			$form->hidden('recommend_userID', '');

			$form->html('<div class="buttonbar">');
			$form->submit('recommend_submit', 'Send ' . $passiveAction);
			$form->html('<a href="' . BASE_URL . $base . '/view/' . $id . '/" class="button cancel"><span>Cancel</span></a></div>');

			$form->setFieldErrors($fieldErrors);

			return $form->render();

		}

	}
?>