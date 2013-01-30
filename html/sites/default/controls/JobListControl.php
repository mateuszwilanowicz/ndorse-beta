<?php
    class JobListControl {

        public static function render($jobs,$args) {
            switch ($_SESSION['user']->level) {
                case Ndoorse_Member::LEVEL_RECRUITER:

                    $base = RECRUITERS_URL;

                    $headings = array(
                        array('table'=>'chbox', 'heading'=>''),
                        array('table'=>'title', 'heading'=>'Job Title', 'dir'=>'asc'),
                        //array('table'=>'company', 'heading'=>'Company', 'dir'=>'asc'),
                        //array('table'=>'description', 'heading'=>'Description', 'dir'=>'asc'),
                        //array('table'=>'location', 'heading'=>'Location', 'dir'=>'asc'),
                        //array('table'=>'type', 'heading'=>'type', 'dir'=>'asc'),
                        //array('table'=>'minSalary', 'heading'=>'minSalary', 'dir'=>'asc'),
                        //array('table'=>'maxSalary', 'heading'=>'maxSalary', 'dir'=>'asc'),
                        //array('table'=>'datePosted', 'heading'=>'datePosted', 'dir'=>'asc'),
                        //array('table'=>'dateExpires', 'heading'=>'dateExpires', 'dir'=>'asc'),
                        //array('table'=>'skills', 'heading'=>'skills', 'dir'=>'asc'),
                        //array('table'=>'status', 'heading'=>'status', 'dir'=>'asc'),
                        //array('table'=>'notes', 'heading'=>'notes', 'dir'=>'asc'),
                        //array('table'=>'match', 'heading'=>'')
                        array('table'=>'moreinfo', 'heading'=>'')
                    );

                    if (array_key_exists('dir', $args)) {
                        foreach ($headings as $key => $field) {
                            if ($args['orderby']==$headings[$key]['table'] && $args['dir'] == 'desc') {
                                $headings[$key]['dir'] = "asc";
                            }
                        }
                    }

                    // attributes array gives the control extra parameters
                    $attributes = array('table_id'=>'table', 'has_pagination'=>false, 'display_header'=>false);
                    //pr($jobs);
                    $jobsArray = array();
                    foreach($jobs as $job) {
                        $jobInArray = $job->toArray();
                        $jobMatchedMembers = Ndoorse_Match::getNumberOfMatches($job);
                        $jobInArray['chbox'] ='<input type="checkbox" name="'.$job->jobID.'">';
                        $jobInArray['title'] = '<a href=" ' . $base . 'edit/' . $job->id . '"><b style="font-color: black">' . $job->title . '</b></a><br />'.$job->company.'<br />'.$job->location;
                        $jobInArray['moreinfo'] = '<a href=" ' . $base . 'match/job/' . $job->id . '">( ' . $jobMatchedMembers . ' matched candidates)</a><br />'.$job->maxSalary;
                        if($job->status != 2) {
                            switch($job->status) {
                                case 0:
                                    $jobInArray['title'] .= '<br/><i><span style="color: red">Awaitting approval</span></i>';
                                    break;
                                case 1:
                                    $jobInArray['title'] .= '<br/><i><span style="color: red">Awaitting approval</span></i>';
                                    break;
                                case 2:
                                    $jobInArray['title'] .= '<br/>Activated';
                                    break;
                                case 3:
                                    $jobInArray['title'] .= '<br/>Removed';
                                    break;
                            }
                        }
                        $jobsArray[] = $jobInArray;
                    }

                    $output = TableControl::render($headings, $jobsArray, $attributes);

                    return $output;

                    break;

                default:
                    $base = BASE_URL;

                    $headings = array(
                        array('table'=>'title', 'heading'=>'Title', 'dir'=>'asc'),
                        array('table'=>'company', 'heading'=>'Company', 'dir'=>'asc'),
                        //array('table'=>'description', 'heading'=>'description', 'dir'=>'asc'),
                        array('table'=>'location', 'heading'=>'Location', 'dir'=>'asc'),
                        array('table'=>'type', 'heading'=>'Type', 'dir'=>'asc'),
                        array('table'=>'maxSalary', 'heading'=>'Salary', 'dir'=>'asc'),
                        //array('table'=>'maxSalary', 'heading'=>'maxSalary', 'dir'=>'asc'),
                        //array('table'=>'datePosted', 'heading'=>'datePosted', 'dir'=>'asc'),
                        //array('table'=>'dateExpires', 'heading'=>'dateExpires', 'dir'=>'asc'),
                        //array('table'=>'skills', 'heading'=>'skills', 'dir'=>'asc'),
                    );

                    if (array_key_exists('dir', $args)) {
                        foreach ($headings as $key => $field) {
                            if ($args['orderby']==$headings[$key]['table'] && $args['dir'] == 'desc') {
                                $headings[$key]['dir'] = "asc";
                            }
                        }
                    }

                    // attributes array gives the control extra parameters
                    $attributes = array('table_id'=>'table', 'has_pagination'=>false);
                    //pr($jobs);
                    $jobsArray = array();
                    foreach($jobs as $job) {
                        $jobInArray = $job->toArray();
                        $jobInArray['maxSalary'] = $job->getSalary();
                        $jobInArray['type'] = $job->getType();
                        $jobInArray['title'] = '<a href=" ' . $base . 'jobs/view/' . $job->id . '">' . $job->title . '</a>';
                        $jobsArray[] = $jobInArray;
                    }

                    $output = TableControl::render($headings, $jobsArray, $attributes);

                    return $output;

                    break;
            }






        }
    }
/*
?>

<?php

	class JobListControl {

		public static function render($jobs) {

			$output = '<table border="0" class="job-list">';
			$base = BASE_URL;

			foreach($jobs as $job) {
				$company = '';
				$logo = '';
				$by = '';
				$datePosted = $job->getPostDate();

				if(strlen($job->company) > 0) {
					$company = '@ ' . $job->company;
				}
				if(!$job->anonymous) {
					$by = ' by ' . $job->firstname . ' ' . $job->lastname;
				}
                if($_SESSION['user']->level != Ndoorse_Member::LEVEL_RECRUITER) {
                    $output .= <<<JOBROW
                <tr>
                    <td class="job-info" colspan="3">
                        <a href="{$base}jobs/view/{$job->id}/">{$job->title}</a> {$company}
                    </td>
                    <td class="job-actions" rowspan="2">
                        <a class="button" href="{$base}jobs/apply/{$job->id}/">Apply</a>
                        <a class="button" href="{$base}jobs/recommend/{$job->id}/">Recommend</a>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">{$job->description}</td>
                </tr>
                <tr>
                    <td>{$job->location}</td>
                    <td>{$job->hours} / {$job->type}</td>
                    <td>{$job->minSalary} - {$job->maxSalary}</td>
                    <td>Posted {$datePosted}$by</td>
                </tr>
JOBROW;
                } else {
                    $base = RECRUITERS_URL;

                    $output .= <<<JOBROW
                <tr>
                    <td class="job-actions" rowspan="4">
                        <input type="checkbox" name="{$job->id}" />
                    </td>
                    <td class="job-info" colspan="3">
                        <a href="{$base}edit/{$job->id}/">{$job->title}</a> {$company}
                    </td>
                    <td class="job-notes" rowspan="4">{$job->notes}</td>
                    <td class="job-actions" rowspan="4">
                        <a class="button" href="{$base}match/job/{$job->id}/">Match</a>
                        <a class="button" href="{$base}edit/{$job->id}/">Edit</a>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">{$job->description}</td>
                </tr>
                <tr>
                    <td colspan="3">{$job->skills}</td>
                </tr>
                <tr>
                    <td>{$job->location}</td>
                    <td>{$job->hours} / {$job->type}</td>
                    <td>{$job->minSalary} - {$job->maxSalary}</td>

                </tr>
                <tr><td colspan="3">Posted {$datePosted}$by</td>
                </tr>
                <tr><td colspan="5"><br/></td></tr>
JOBROW;
                }

			}

			$output .= '</table>';


			return $output;

		}

	}
 */

?>