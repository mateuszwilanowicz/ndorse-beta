<?php
	class JobsFilterControl {

		public static function render($args) {

			$keywordOptions = array('any'=>array('label'=>'Any of these words'), 'all'=>array('label'=>'All of these words'));
			if(isset($args['keywordoptions']) && ($args['keywordoptions'] == 'all' || $args['keywordoptions'] == 'any')) {
				$keywordOptions[$args['keywordoptions']]['checked'] = true;
			} else {
				$keywordOptions['any']['checked'] = true;
			}
            $dir = 'asc';
            if(isset($args['dir'])) {
                $dir = $args['dir'] == 'asc'? 'desc' : 'asc';
            }
        	$locations = Ndoorse_Location::getLocations();
            $statuses = array(array("label"=>"(select)","value"=>""),
                            array("label"=>"AWAITING APPROVAL","value"=>0),
                            array("label"=>"NOT ACTIVATED","value"=>1),
                            array("label"=>"ACTIVE","value"=>2),
                            array("label"=>"REMOVED","value"=>3)
                        );
            FormControl::selectOption($locations, isset($args['location'])?$args['location']:'');
            FormControl::selectOption($statuses, isset($args['status'])?$args['status']:'');
            
			$form = new FormControl(RECRUITERS_URL . 'jobs/', 'filters');
			$form->textbox('keywords', 'Keyword Search:', isset($args['keywords']) ? $args['keywords'] : '');
			$form->radio('keywordoptions', '', $keywordOptions);
			$form->select('location', 'Location:', $locations);
            $form->select('status', 'Status:', $statuses);
            $form->submit('search', 'Search');
			//$form->textbox('industry', 'Industry:', isset($args['industry']) ? $args['industry'] : '');
            //$form->html("<h3>Order By</h3>");
            //$form->html("<a href='".RECRUITERS_URL."jobs/?orderby=title&dir=".$dir."' class='button'>Title</a>");
            //$form->html("<a href='".RECRUITERS_URL."jobs/?orderby=status&dir=".$dir."' class='button'>Status</a>");
            //$form->html("<a href='".RECRUITERS_URL."jobs/?orderby=datePosted&dir=".$dir."' class='button'>Date</a>");
			
			//$form->button('resetForm', 'Reset');

			return $form->render();

		}

	}
?>