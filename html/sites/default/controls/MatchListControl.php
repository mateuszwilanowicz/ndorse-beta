<?php
    class MatchListControl {

        public static function render($matches, $args) {

            
            /*
            $base = RECRUITERS_URL;
            $output = '<table border="0" class="match-list">';
            
            foreach($matches as $m) {

                $output .= <<<MATCHROW
                <tr>
                    <td>{$m->entityID}</td>
                    <td>{$m->entity}</td>
                    <td>{$m->userID}</td>
                    <td>{$m->status}</td>
                    <td>{$m->date}</td>
                    <td>{$m->applicationID}</td>
                    <td>{$m->modifiedBy}</td>
                    <td>{$m->dateModified}</td>
                </tr>
MATCHROW;

            }
            
            $output .= '</table>';
            return $output;
            */
            
            $base = RECRUITERS_URL;
                    
            $headings = array(
                array('table'=>'chbox', 'heading'=>''),
                array('table'=>'name', 'heading'=>'name', 'dir'=>'asc'),
                array('table'=>'type', 'heading'=>'search type', 'dir'=>'asc'),
                array('table'=>'jobs', 'heading'=>'last positions'),
                //array('table'=>'status', 'heading'=>'status', 'dir'=>'asc'),
                //array('table'=>'date', 'heading'=>'date', 'dir'=>'asc'),
                array('table'=>'application','heading'=>'application','dir'=>'asc'),
                array('table'=>'location','heading'=>'location','dir'=>'asc'),
                //array('table'=>'recommendee','heading'=>'recommendee','dir'=>'asc')
                //array('table'=>'notes', 'heading'=>'notes', 'dir'=>'asc'),
                array('table'=>'message','heading'=>'')
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
            $matchArray = array();
            foreach($matches as $match) {
                //pr($match,false);
                $matchInArray = $match->toArray();
                $matchedMember = new Ndoorse_Member($match->userID);
                $jobs = $matchedMember->getThreeLastJobs($match->userID);
                //pr($jobs,false);
                if($match->status == 2) {
                    $matchInArray['name'] = '<a href="' . BASE_URL . '/members/profile/' . $match->userID . '">' . $match->firstname . ' ' . $match->lastname . '</a>';
                    $matchInArray['message'] = '<a class="button" href="' . BASE_URL . '/messages/write/?to=' . $match->userID . '">Send a message</a>';
                } else {
                    $matchInArray['name'] = '';
                    $matchInArray['message'] = '';
                }
                if(isset($matchInArray['recommendeeID']) && $matchInArray['recommendeeID']!= 0) {
                    $recommendee = new Ndoorse_Member($match->recommendeeID);
                    $matchInArray['recommendee'] = '<a href="' . BASE_URL . 'members/profile/' . $match->recommendeeID . '">' . $recommendee->firstname . ' ' . $recommendee->lastname . '</a>';
                    $matchInArray['recommendee'] = $recommendee->firstname . ' ' . $recommendee->lastname;    
                } else {
                    $matchInArray['recommendee'] = '';
                    
                }
                //pr($matchInArray,false);
                if(isset($matchInArray['applicationID']) && $matchInArray['applicationID'] != 0) {
                    $matchInArray['application'] = '<a href="' . BASE_URL . 'application/view/' . $match->applicationID . '/">view</a>';
                } else {
                    $matchInArray['application'] = '';
                    //$matchInArray['application'] = '<a href="' . BASE_URL . '/application/view/' . $match->applicationID . '/">view</a>';                    
                }
                
                switch($match->status) {
                    case 0:
                        $matchInArray['status'] = 'Not Contacted';
                        break;
                    case 1:
                        $matchInArray['status'] = 'Contacted';
                        break;
                    case 2:
                        $matchInArray['status'] = 'Accepted';
                        break;
                    case 3:
                        $matchInArray['status'] = 'Declined';
                        break;
                    case 4:
                        $matchInArray['status'] = 'Removed';
                        break;
                }
                switch($match->type) {
                    case 0:
                        $matchInArray['type'] = 'Matched';
                        break;
                    case 1:
                        $matchInArray['type'] = 'Recomended';
                        break;
                    case 2:
                        $matchInArray['type'] = 'Manual';
                        break;
                    case 3:
                        $matchInArray['type'] = 'Aplied';
                        break;
                }
                $matchInArray['jobs'] = "";
                foreach($jobs as $job) {
                    $matchInArray['jobs'] .= $job['jobTitle'] . ' @ ' . $job['companyName'] .  '<br />'; 
                }
    
                $matchInArray['chbox'] ='<input type="checkbox" name="'.$match->userID.'">';
                $matchArray[] = $matchInArray; 
            }
            
            $output = TableControl::render($headings, $matchArray, $attributes);
            
            return $output;
                    
            
        }
    }
    
?>