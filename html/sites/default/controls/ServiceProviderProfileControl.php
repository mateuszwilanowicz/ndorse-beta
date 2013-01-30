<?php
	class ServiceProviderProfileControl {

		public static $serviceProviderRules = array(
			'serviceprovider' => array('validator' => 'serviceprovider', 'required' => 'true'),
			'position' => array('validator' => 'position', 'required' => 'true')
		);

		public static function render($args, $userProfile, $fieldErrors = array(), $allServiceProviders = array()) {

			if(isset($allServiceProviders)) {
			    //pr($allServiceProviders);
                $noedit = true;
			    $profileForm = new FormControl(BASE_URL.'members/profile/','Profile Form','ProfileFormClass','POST');
                $profileForm->setFieldErrors($fieldErrors);
                $serviceProviderDefined = false;

				foreach($allServiceProviders as $s) {
					$profileForm->html('<div>');
					$profileForm->html('<a href="' . BASE_URL . 'serviceproviders/profile/' . $s->serviceproviderID . '/">' . $s->name . '</a>');
					if($userProfile->userID == $_SESSION['user']->userID && $noedit) {
						if($userProfile->userID == $s->userID) {
						  $serviceProviderDefined = true;
                          $profileForm->html(' <a href="' . BASE_URL . 'members/profile/serviceprovider/delete/' . $s->serviceproviderID . '" class="button"><span>Delete</span></a>');
						  $profileForm->html('<a href="' . BASE_URL . 'serviceproviders/post/' . $s->serviceproviderID . '" class="button"><span>Edit</span></a>');
                        } else {
                          $profileForm->html(' <a href="' . BASE_URL . 'members/profile/serviceprovider/quit/' . $s->serviceproviderID . '" class="button"><span>Quit</span></a>');
                        }
					}
					$profileForm->html('</div>');
				}
				if(isset($args['1']) && isset($args['2']) ) {
					if($args['1'] == 'serviceprovider' && $args['2'] == 'new') {
						//$profileForm->html('<input type="hidden' value="' name="serviceproviderID"></input>');
						//$profileForm->html('<label>Name: </label><input type="text' value="' name="serviceprovidername"></input><br/>');
						$profileForm->textbox('serviceprovider','Name','');
						$profileForm->textbox('position','Your Position','');
						$profileForm->hidden('serviceproviderID','');
						$profileForm->submit('submit', 'Save',true,array('class'=>'floatRight'));
						$profileForm->html('<a href="' . BASE_URL . 'members/profile/" class="button"><span>Cancel</span></a>');
					} elseif ($args['1'] == 'serviceprovider' && $args['2'] == 'delete' && isset($args[3])) {
						if(Ndoorse_Member::deleteServiceProvider($args[3])) {
							$_SESSION['page_message'][] = 'Successfully removed the service provider from your profile!';
						} else {
							$_SESSION['page_error'][] = 'Could not remove the service provider!';
						}
						redirect(BASE_URL . 'members/profile');
					} elseif ($args['1'] == 'serviceprovider' && $args['2'] == 'quit' && isset($args[3])) {
                        if(Ndoorse_Member::quitServiceProvider($args[3])) {
                            $_SESSION['page_message'][] = 'You are no longer a member of the service provider!';
                        } else {
                            $_SESSION['page_error'][] = 'Could not quit the service provider!';
                        }
                        redirect(BASE_URL . 'members/profile');
                    }
				}


				if($userProfile->userID == $_SESSION['user']->userID && $noedit && !$serviceProviderDefined)
					$profileForm->html('<a href="' . BASE_URL . 'members/profile/serviceprovider/new/" class="button"><span>Add New</span></a>');
			}

			//$profileForm->textbox('companyID', 'companyID', $userProfile->companyID);

			//$profileForm->html('</div>');

			//pr($_SESSION['user']);
			return $profileForm;


		}
	}
?>