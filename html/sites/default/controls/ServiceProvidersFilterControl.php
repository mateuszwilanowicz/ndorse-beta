<?php
	class ServiceprovidersFilterControl {

		public static function render($args) {

			$keywordOptions = array('any'=>array('label'=>'Any of these words'), 'all'=>array('label'=>'All of these words'));
			if(isset($args['keywordoptions']) && ($args['keywordoptions'] == 'all' || $args['keywordoptions'] == 'any')) {
				$keywordOptions[$args['keywordoptions']]['checked'] = true;
			} else {
				$keywordOptions['any']['checked'] = true;
			}

        	$locations = Ndoorse_Location::getLocations();
            $industry = Ndoorse_Serviceprovider::getIndustries();
            FormControl::selectOption($locations, isset($args['location'])?$args['location']:'');
            FormControl::selectOption($industry, isset($args['industry'])?$args['industry']:'');

			$form = new FormControl(BASE_URL . 'serviceproviders/', 'filters');
			$form->textbox('keywords', 'Keyword Search:', isset($args['keywords']) ? $args['keywords'] : '');
			$form->radio('keywordoptions', '', $keywordOptions);

			$form->select('location', 'Location:', $locations);
            $form->select('industry', 'Industry:', $industry);
			//$form->textbox('industry', 'Industry:', isset($args['industry']) ? $args['industry'] : '');

			$form->submit('search', 'Search');
			$form->html('<a class="button" href="' . BASE_URL . 'serviceproviders/"><span>Reset</span></a>');

			return $form->render();

		}

	}
?>