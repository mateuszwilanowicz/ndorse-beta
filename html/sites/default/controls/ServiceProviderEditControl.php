<?php
	class ServiceProviderEditControl {

		public static function render($serviceProvider, $formErrors = array()) {
		    
            $industry = Ndoorse_Serviceprovider::getIndustries();
            $industryPreset = $serviceProvider->attributes->INDUSTRY->value != ''?$serviceProvider->attributes->INDUSTRY->value:'';
            FormControl::selectOption($industry, $industryPreset);    
                        
			$form = new FormControl(BASE_URL . 'serviceproviders/post/');
            $form->textbox('name', 'Name:', $serviceProvider->name);
			$form->hidden('serviceproviderID', $serviceProvider->getID());
			$form->textarea('description', 'Description:', $serviceProvider->description);
			$form->textbox('location', 'Location:', $serviceProvider->location);
            $form->select('industry', 'Industry:', $industry);
            $twitterBool = $serviceProvider->twitterFeed == 1 ? true : false;
            $form->checkbox('twitterFeed', 'Display Twitter Feed', $twitterBool, $serviceProvider->twitterFeed);
            $form->textbox('twitterFeedUser', 'Twitter Feed User',$serviceProvider->twitterFeedUser);
            $form->textarea('content', 'Content:', $serviceProvider->content);
			$form->hidden('locationID', $serviceProvider->locationID);
            $form->hidden('logoID', $serviceProvider->logoID);
            $form->textbox('oldLogoURL', 'Logo:', $serviceProvider->logoURL);
            $form->file('logo', 'Upload New Logo:');
            
            $form->textbox('oldPDF', 'Brochure:', $serviceProvider->documentURL);
            $form->file('brochure', 'Upload New Brochure:');
			$form->html('<div class="buttonbar">');
			$form->submit('submit', 'Save Serviceprovider');
			$form->html('<a href="' . BASE_URL . 'serviceproviders/" class="button cancel">Cancel</a></div>');

			if(!empty($formErrors)) {
				$form->setFieldErrors($formErrors);
			}

			return $form->render();

		}

	}
?>