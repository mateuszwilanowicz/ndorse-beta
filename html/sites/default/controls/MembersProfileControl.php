<?php
	class MembersProfileControl {

		public static $profileFormRules = array(
			'firstname' => array('validator' => 'firstanme', 'required' => 'true'),
			'lastname' => array('validator' => 'lastname', 'required' => 'true'),
			'email' => array('validator' => 'email', 'required' => 'true', 'maxlength' => 90),
		);

		public static $serviceProviderRules = array(
			'serviceprovider' => array('validator' => 'serviceprovider', 'required' => 'true'),
			'position' => array('validator' => 'position', 'required' => 'true')
		);

		public static function render($args, $userProfile, $fieldErrors = array(), $education = array(), $experience = array(), $allServiceProviders = array()) {
			if(!isset($args['email'])) $args['email'] = '';
			$noedit = true;
			//pr($fieldErrors);
			$profileForm = new FormControl(BASE_URL.'members/profile/','Profile Form','ProfileFormClass','POST');
			$profileForm->setFieldErrors($fieldErrors);
			$profileForm->html('<div class="box top-box"><h3>Member Details</h3>');
			$profileForm->file('avatar', 'Profile Picture:');
			$profileForm->textbox('firstname', 'First Name', $userProfile->firstname);
			$profileForm->textbox('lastname', 'Last Name', $userProfile->lastname);
			$profileForm->textbox('address1', 'Address Line 1', $userProfile->address1);
			$profileForm->textbox('address2', 'Address Line 2', $userProfile->address2);
			$profileForm->textbox('region', 'Region', $userProfile->region);
            $profileForm->textbox('location', 'City:', $userProfile->location);
            $profileForm->hidden('locationID', $userProfile->locationID);
			$profileForm->textbox('postcode', 'Postcode', $userProfile->postcode);
			$profileForm->textbox('country', 'Country', $userProfile->country);
			$profileForm->textbox('telhome', 'Home Phone', $userProfile->telhome);
			$profileForm->textbox('telmobile', 'Mobile Phone', $userProfile->telmobile);
			$profileForm->textbox('telwork', 'Work Phone', $userProfile->telwork);
			$profileForm->textbox('jobstatus', 'Job Status', $userProfile->jobstatus);
			$profileForm->textbox('company', 'Company Name', $userProfile->company);
			$profileForm->fixed('identifier', 'Identifier:', $userProfile->identifier);

			$profileForm->textbox('email', 'E-mail', $userProfile->email);


			if(isset($args['1']) && isset($args['2']) ) {
				if(($args['1'] == 'experience' && $args['2'] == 'new')|| ($args['1'] == 'education' && $args['2'] == 'new')|| ($args['1'] == 'serviceprovider' && $args['2'] == 'new')) {
					$noedit = false;
				}
			}

			if(isset($education)) {
				$profileForm->html('<br/><fieldset><legend>Education</legend>');

				foreach($education as $e) {

					$print = true;

					$ys = new DateTime($e['startDate']);
					if(isset($e['startDate'])) {
						$ye = new DateTime($e['endDate']);
						$df = $ys->diff($ye);
						$dr = $df->y;
					} else {
						$dr = 'current';
					}


					if(isset($args['1']) && isset($args['2']) && isset($args['3'])) {
						$print = false;
						$noedit = false;
						if($args['1'] == 'education' && $args['2'] == 'edit' && $args['3'] == $e['educationID']) {
							$educationEntity = new Ndoorse_Education($e);
							$skills = $e['skills'];
							$profileForm->submit('submit', 'Save',true,array('class'=>'floatRight'));
							$profileForm->html('<a href="' . BASE_URL . 'members/profile" class="profile-skills-cancel button"><span>Cancel</span></a>');
							$profileForm->datepicker('startDate', 'Start Date',$educationEntity->startDate);
							$profileForm->datepicker('endDate', 'End Date',$educationEntity->endDate);
							$profileForm->html(EducationEditControl::render($educationEntity,$skills));

							$profileForm->html('</br>');

						} else if($args['1'] == 'education' && $args['2'] == 'remove' && $args['3'] == $e['educationID']) {
							Ndoorse_Member::deleteEducation($e['educationID']);
							redirect(BASE_URL . 'members/profile/');
						} else {
							$print = true;
						}
					}
					if($print)  {

						$label = $e['institution'] . ': ' . $e['courseName'] . ' <br />Degree: ' . $e['description'];
						if(count(explode(',',$e['skills'])) > 0) {
							$label .= '<br /> Skills: ' . $e['skills'];
						}

						$profileForm->html('<label for="education' . $e['educationID'] . '><strong>' . $dr . '</strong></label>');
						if($userProfile->userID == $_SESSION['user']->userID && $noedit) {
							$profileForm->html('<a href="' . BASE_URL . 'members/profile/education/remove/' . $e['educationID'] . '" class="button"><span>Remove</span></a>');
							$profileForm->html('<a href="' . BASE_URL . 'members/profile/education/edit/' . $e['educationID'] . '" class="button"><span>Edit</span></a>');
						}
						$profileForm->html('<div id="education' . $e['educationID'] . '">' . $label);

						$profileForm->html('</div></br>');
					}

				}
				if(isset($args['1']) && isset($args['2']) ) {
					if($args['1'] == 'education' && $args['2'] == 'new') {

						$educationEntity = new Ndoorse_Education();
						$skills = '';

						$profileForm->submit('submit', 'Save', true, array('class'=>'floatRight'));
						$profileForm->html('<a href="' . BASE_URL . 'members/profile/" class="button"><span>Cancel</span></a>');
						$profileForm->datepicker('startDate', 'Start Date');
						$profileForm->datepicker('endDate', 'End Date');
						$profileForm->html(EducationEditControl::render($educationEntity,$skills));

					}
				}
				if($userProfile->userID == $_SESSION['user']->userID && $noedit)
					$profileForm->html('<a href="' . BASE_URL . 'members/profile/education/new/" class="button"><span>Add New</span></a>');

				$profileForm->html('</fieldset>');
			}


			if(isset($experience)) {

				$profileForm->html('<br/><fieldset><legend>Experience</legend>');

				foreach($experience as $e) {
					$print = true;
					if(isset($args['1']) && isset($args['2']) && isset($args['3'])) {
						$print = false;
						$noedit = false;
						if($args['1'] == 'experience' && $args['2'] == 'edit' && $args['3'] == $e['experienceID']) {
							$experienceEntity = new Ndoorse_Experience($e);
							$skills = $e['skills'];
							$profileForm->submit('submit', 'Save Changes', true, array('class'=>'floatRight'));
							$profileForm->html('<a href="' . BASE_URL . 'members/profile/" class="button"><span>Cancel</span></a>');
							$profileForm->datepicker('startDate', 'Start Date', $experienceEntity->startDate);
							$profileForm->datepicker('endDate', 'End Date', $experienceEntity->endDate);
							$profileForm->html(ExperienceEditControl::render($experienceEntity, $skills));
						} elseif($args['1'] == 'experience' && $args['2'] == 'remove' && $args['3'] == $e['experienceID']) {
							Ndoorse_Member::deleteExperience($e['experienceID']);
							redirect(BASE_URL . 'members/profile/');
						} else {
							$print = true;
						}
					}
					if($print)  {
						$ys = new DateTime($e['startDate']);
						if(isset($e['startDate'])) {
							$ye = new DateTime($e['endDate']);
							$df = $ys->diff($ye);
							$dr = $df->y . ' years and ' . $df->m . ' months';
						} else {
							$dr = 'current';
						}

						$label = $e['jobTitle'] . ' at ' . $e['companyName'] . ' ( ' . $dr . ' ) <br /> ' . $e['description'];
						if(count(explode(',',$e['skills'])) > 0) {
							$label .= " <br /> Skills: " . $e['skills'];
						}
						$profileForm->html('<label for="experience' . $e['experienceID']. '"><strong>' . $ys->format('Y') . '</strong></label>');
						if($userProfile->userID == $_SESSION['user']->userID && $noedit) {
							$profileForm->html('<a href="' . BASE_URL . 'members/profile/experience/remove/' . $e['experienceID'] . '" class="button"><span>Remove</span></a>');
							$profileForm->html('<a href="' . BASE_URL . 'members/profile/experience/edit/' . $e['experienceID'] . '" class="button"><span>Edit</span></a>');

						}
						$profileForm->html('<div id="experience' . $e['experienceID']. '">' . $label);

						$profileForm->html('</div><br/>');
					}

				}
				if(isset($args['1']) && isset($args['2']) ) {
					if($args['1'] == 'experience' && $args['2'] == 'new') {
						$experienceEntity = new Ndoorse_Experience();
						$skills = '';
						$profileForm->submit('submit', 'Save',true,array('class'=>'floatRight'));
						$profileForm->html('<a href="' . BASE_URL . 'members/profile/" class="button"><span>Cancel</span></a>');
						$profileForm->datepicker('startDate', 'Start Date');
						$profileForm->datepicker('endDate', 'End Date');
						$profileForm->html(ExperienceEditControl::render($experienceEntity,$skills));

					}
				}

				if($userProfile->userID == $_SESSION['user']->userID && $noedit)
					$profileForm->html('<a href="' . BASE_URL . 'members/profile/experience/new/" class="button"><span>Add New</span></a>');
				$profileForm->html('</fieldset>');
			}

			if(isset($allServiceProviders)) {
			    //pr($allServiceProviders);
                $serviceProviderDefined = false;
				$profileForm->html('<br/><fieldset><legend>Service Providers</legend>');


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
				$profileForm->html('</fieldset>');
			}

			//$profileForm->textbox('companyID', 'companyID', $userProfile->companyID);
			if($userProfile->userID == $_SESSION['user']->userID) {
				$profileForm->submit('submit', 'Save Changes');
			}

			$profileForm->html('</div>');

			//pr($_SESSION['user']);
			return $profileForm;


		}
	}
?>