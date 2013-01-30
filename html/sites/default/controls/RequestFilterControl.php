<?php
	class RequestFilterControl {

		public static function render($args) {

			$keywordOptions = array('any'=>array('label'=>'Any of these words'), 'all'=>array('label'=>'All of these words'));
			if(isset($args['keywordoptions']) && ($args['keywordoptions'] == 'all' || $args['keywordoptions'] == 'any')) {
				$keywordOptions[$args['keywordoptions']]['checked'] = true;
			} else {
				$keywordOptions['any']['checked'] = true;
			}

			$offeringOptions = array(array('label'=>'(show all)', 'value'=>''), array('label'=>'Looking for', 'value'=>0), array('label'=>'Offering', 'value'=>1));
			if(isset($args['offering']) && isset($offeringOptions[$args['offering']])) {
				$offeringOptions[$args['offering']]['checked'] = true;
			} else {
				$offeringOptions[0]['checked'] = true;
			}

			$locations = Ndoorse_Location::getLocations();

			$form = new FormControl(BASE_URL . 'requests/', 'filters');
			$form->textbox('keywords', 'Keyword Search:', isset($args['keywords']) ? $args['keywords'] : '');
			$form->radio('keywordoptions', '', $keywordOptions);

			$form->select('location', 'Location:', $locations);
			$form->select('offering', 'Type:', $offeringOptions);

			$form->checkbox('type_advice', 'Advice', isset($args['type_advice']), Ndoorse_Request::TYPE_ADVICE);
			$form->checkbox('type_help', 'Help', isset($args['type_help']), Ndoorse_Request::TYPE_HELP);
			$form->checkbox('type_introduction', 'Introduction', isset($args['type_introduction']), Ndoorse_Request::TYPE_INTRODUCTION);
			$form->checkbox('type_mentoring', 'Mentoring', isset($args['type_mentoring']), Ndoorse_Request::TYPE_MENTORING);

			$form->datepicker('datePosted', 'Date posted:', isset($args['datePosted']) ? $args['datePosted'] : date('Y-m-d'));

			$form->submit('search', 'Search');
			//$form->button('resetForm', 'Reset');

			return $form->render();

		}

	}
?>