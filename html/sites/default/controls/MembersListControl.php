<?php
    class MembersListControl {

        public static function render($members,$args) {
            /*
            $base = RECRUITERS_URL;
            $output = '<table border="0" class="match-list">';
            
            foreach($members as $m) {

                $output .= <<<MATCHROW
                <tr>
                    <td>{$m->userID}</td>
                    <td>{$m->firstname} {$m->lastname}</td>
                    <td>{$m->email}</td>
                    <td>{$m->jobstatus}</td>
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
                array('table'=>'skills', 'heading'=>'skills'),
                array('table'=>'email', 'heading'=>'email', 'dir'=>'asc'),
                array('table'=>'jobstatus', 'heading'=>'jobstatus', 'dir'=>'asc'),
                //array('table'=>'notes', 'heading'=>'notes', 'dir'=>'asc'),
                
            );
        
            if (array_key_exists('dir', $args)) {
                foreach ($headings as $key => $field) {
                    if ($args['orderby']==$headings[$key]['table'] && $args['dir'] == 'desc') {
                        $headings[$key]['dir'] = "asc";
                    }
                }
            }
            
            // attributes array gives the control extra parameters
            $attributes = array('table_id'=>'all_members_list', 'has_pagination'=>false);
            //pr($jobs);
            $memberArray = array();
            foreach($members as $member) {
                $memberInArray = $member->toArray();
                $memberInArray['skills'] = $member->getSkillsString();
                $memberInArray['name'] = '<a href="' . BASE_URL . '/members/profile/' . $member->userID . '">' . $member->firstname . ' ' . $member->lastname . '</a>';
                $memberInArray['chbox'] ='<input type="checkbox" name="all_'.$member->userID.'">';
                $memberArray[] = $memberInArray; 
            }
            
            $output = TableControl::render($headings, $memberArray, $attributes);
            
            return $output;
            
        }
    }
    
?>