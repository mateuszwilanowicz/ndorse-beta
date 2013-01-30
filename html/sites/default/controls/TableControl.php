<?php
/**
 * Renders a table
 */
class TableControl {

    /* $attributes array keys
     *
     * form_action
     * page_size
     * item_count
     * item_search_count // the count of the search results
     * page_number
     * table_id // default should be 'table'
     *
     */
	public static function render($fields, $rows, $attributes = array()) {
		if(!array_key_exists('table_id', $attributes)) { $attributes['table_id'] = 'table'; }
		if(!array_key_exists('form_action', $attributes)) { $attributes['form_action'] = $_SERVER['REQUEST_URI']; }
		if(!array_key_exists('page_size', $attributes)) { $attributes['page_size'] = 20; }
		if(!array_key_exists('item_count', $attributes)) { $attributes['item_count'] = count($rows); }
		if(!array_key_exists('item_search_count', $attributes)) { $attributes['item_search_count'] = $attributes['item_count']; }
		if(!array_key_exists('page_number', $attributes)) { $attributes['page_number'] = 1; }
		if(!array_key_exists('has_pagination', $attributes)) { $attributes['has_pagination'] = $attributes['page_size'] > 0; }
		if(!array_key_exists('class', $attributes)) { $attributes['class'] = ''; }
        if(!array_key_exists('display_header', $attributes)) { $attributes['display_header'] = true; }

		$queryString = rebuildQueryString(array('controller', 'page', $attributes['table_id'] . '_page', 'orderby', 'dir'));
		if (strlen($queryString) > 0)
			$queryString = '?' . $queryString . '&amp;';
		else
			$queryString = '?';
		$output = '';

		$output .= "<table id=\"{$attributes['table_id']}\" class=\"tablecontrol {$attributes['class']}\">\n";
		if($attributes['display_header'] == true) {
    		$output .= "<thead><tr>\n";
    
    		foreach($fields as $key=>$field) {
    			if($key !== 'id') {
    				if(array_key_exists('dir', $field)) {
    				    //pr($attributes['form_action']);
                        if(isset($_GET['dir'])) {
                            $dir = $_GET['dir'] == 'asc' ? 'desc' : 'asc';
                        } else {
                            $dir =  $field['dir'];
                        }
                        $output .= '<th' .
    						(array_key_exists('id', $field) ? ' id="' . $field['id'] . '"' : '') .
    						(array_key_exists('class', $field) ? ' class="' . $field['class'] . '"' : '') .
    						'><a href="' .
    						//$attributes['form_action'] . $queryString .
    						'?orderby=' . $field['table'] .
    						'&amp;dir=' . $dir . '">' .
    						$field['heading'] .
    						"</a></th>\n";
    				} else {
    					$output .= '<th' .
    						(array_key_exists('id', $field) ? ' id="' . $field['id'] . '"' : '') .
    						(array_key_exists('class', $field) ? ' class="' . $field['class'] . '"' : '') .
    						'>' . $field['heading'] . '</th>';
    				}
    			}
    		}
    		$output .= "</tr></thead>\n";
		}

		if ($attributes['item_search_count'] > 0){
			$alternate = false;
			foreach ($rows as $row) {
				$output .= "<tr ";
				if(array_key_exists('id', $fields)) {
					$output .= 'id="' . $attributes['table_id'] . '_row_' . $row[$fields['id']] . '"';
				}
				if(array_key_exists('_class', $row)) {
					$output .= ' class="' . $row['_class'] . ($alternate ? ' alternaterow' : '') . '"';
				} else {
					$output .= $alternate ? ' class="alternaterow"' : '';
				}
				$output .= " >\n";
				$col_no = 1;
				foreach ($fields as $key=>$field) {
					if($key !== 'id') {
						$output .= '<td class="' . $attributes['table_id'] . '_col_' . $col_no . '">' . $row[$field['table']] . "</td>\n";
						++$col_no;
					}
				}
				$alternate = !$alternate;
				$output .= "</tr>\n";
			}
		} else {
			$rc = count($fields);
			if(array_key_exists('id', $fields)) {
				--$rc;
			}
			$output .= '<tr class="placeholder"><td colspan="'.$rc.'"  class="placeholder"><strong>No information to display.</strong></td></tr> ';
		}
		$output .= "</table>\n";

		if($attributes['has_pagination']) {
			$output .= '<div class="table_pagingcontrol">';
			$output .= self::pagination($attributes['page_size'], $attributes['item_search_count'], $attributes['page_number'], $attributes['table_id']);
			$output .= '</div>';
		}
		$output .= '<script type="text/javascript">$("#' . $attributes['table_id'] . '_reset").click(function() { location.href="' . $attributes['form_action'] . '"; });</script>';
		return $output;
	}

	public static function pagination ($pagesize, $itemcount, $pagenumber, $control_id = '', $separator = ' of ') {
		if ($pagesize == 0)
			$totalpages = 1;
		else
			$totalpages = ceil($itemcount / $pagesize);

		if($totalpages == 1) {
			return '';
		}

		$queryString = rebuildQueryString(array('controller', 'page', $control_id . '_pagenum'));
		if (strlen($queryString) > 0)
			$queryString = '?' . $queryString . '&';
		else
			$queryString = '?';

		if(array_key_exists('fltr', $_REQUEST)) {
			$queryString .= 'fltr=' . $_REQUEST['fltr'] . '&';
		}
		if(array_key_exists('srch', $_REQUEST)) {
			$queryString .= 'srch=' . $_REQUEST['srch'] . '&';
		}

		if(!empty($control_id)) {
			$queryString .= $control_id . '_';
		}

		$output = "<div class=\"pagingControl\">\n";
		if ($totalpages > 0) {
			if ($pagenumber > 1) {
				$output .= "\t\t<a href=\"" . $queryString . "pagenum=1\"><span class=\"firstButton\"></span></a>\n";
				$output .= "\t\t<a href=\"" . $queryString . "pagenum=" . ($pagenumber - 1) . "\"><span class=\"prevButton\"></span></a>\n";
			} else {
				$output .= "\t\t<span class=\"firstButton disabled\"></span>\n";
				$output .= "\t\t<span class=\"prevButton disabled\"></span>\n";
			}

			$output .= "\t&nbsp;<select id=\"pageselector\">";
			for($i=1;$i<=$totalpages;$i++) {
				$output .= "\t\t<option value=\"" . $i . '"' . ($pagenumber == $i ? ' selected="selected"' : '') . '>&nbsp;' . $i . ' of ' . $totalpages . "&nbsp;</option>\n";
			}
			$output .= "\t</select>";

			if ($pagenumber < $totalpages) {
				$output .= "\t\t<a href=\"" . $queryString . "pagenum=" . ($pagenumber + 1) . "\"><span class=\"nextButton\"></span></a>\n";
				$output .= "\t\t<a href=\"" . $queryString . "pagenum=" . $totalpages . "\"><span class=\"lastButton\"></span></a>\n";
			} else {
				$output .= "\t\t<span class=\"nextButton disabled\"></span>\n";
				$output .= "\t\t<span class=\"lastButton disabled\"></span>\n";
			}
		}

		$output .= "</div>\n";

		$output .= <<<SCRIPTY

				<script type="text/javascript">
					$('#pageselector').change(function() {
						location.href='{$queryString}pagenum=' + $(this).val();
					});
				</script>

SCRIPTY;

		return $output;

	}

}

?>