<?php
	class JobEditControl {

		public static $formRules = array(
					'title'=>array('required'=>true),
					'dateExpires'=>array('required'=>true, 'validator'=>'date')
				);

		public static function render($job) {
            //pr($job,false);
			$hours = array(array('value'=>'FT', 'label'=>'Full Time'),
							array('value'=>'PT', 'label'=>'Part Time'));
			$types = array(array('value'=>'P', 'label'=>'Permanent'),
							array('value'=>'T', 'label'=>'Temporary'),
							array('value'=>'C', 'label'=>'Fixed Term Contract'));

			FormControl::selectOption($hours, $job->hours);
			FormControl::selectOption($types, $job->type);

            switch($_SESSION['user']->level) {
                case Ndoorse_Member::LEVEL_RECRUITER:
                    $baseURL = RECRUITERS_URL . 'edit/';
                    $cancelURL = RECRUITERS_URL;
                    $showNotes = true;
                    break;
                default:
                    $baseURL = BASE_URL . 'jobs/post/';
                    $cancelURL = BASE_URL . 'jobs/';
                    $showNotes = false;
                    break;
            }

			$form = new FormControl($baseURL, 'edit_job');

			$form->hidden('jobID', $job->getID());
			$form->textbox('title', 'Job title:', $job->title);
			//$form->textbox('company', 'Company:', $job->company);
			$form->textbox('location', 'Location:', $job->location);
			$form->hidden('locationID', $job->locationID);

			$form->select('type', 'Type:', $types);
			$form->select('hours', 'Hours:', $hours);
			// industry?
			$form->textbox('minSalary', 'Salary range:', $job->minSalary);
			$form->textbox('maxSalary', '', $job->maxSalary);

			$form->textarea('description', 'Job Description:', $job->description);
			$form->textarea('companyDescription', 'Company Description:', $job->company);
			$form->textarea('skills', 'Desired Skills and Experience:', $job->skills);

			$form->datepicker('dateExpires', 'Expiry Date:', $job->getExpiryDate());
			$form->checkbox('board', 'Post on ndoorse Job Board', $job->board, 1);
			$form->checkbox('anonymous', 'Do not show my name and job title', $job->anonymous, 1);
            if($showNotes)
                $form->textarea('notes', 'Notes:', $job->notes);

			if(!$job->getID()) {
				$form->checkbox('match', 'Match job to relevant profiles', 1);
			}

			$form->html('Please note that all jobs are reviewed before being posted.');

			$form->html('<div class="buttonbar">');
			$form->submit('save', 'Submit Job');
			$form->html('<a class="button cancel" href="' . $cancelURL . '"><span>Cancel</span></a></div>');

			return $form->render();

		}

	}
?>