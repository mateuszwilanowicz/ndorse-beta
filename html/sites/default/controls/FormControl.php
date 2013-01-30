<?php

	/**
	 * Renders HTML forms based upon the fields passed in to it through its hidden, textbox, password, textarea,
	 * select, radio, checkbox, submit and html methods.
	 * If its field errors are set it displays the errors alongside the appropriate form element.
	 *
	 */
	class FormControl extends Model {

		private $name;
		private $class;
		private $action;
		private $method;
		private $fields = array();
		private $fieldErrors = array();
		private $isUploadForm = false;
		private $isReadOnly = false;


		public function __construct($action = '', $name = '', $class='', $method = 'post', $readonly = false) {
			$this->name = $name;
			$this->action = $action;
			$this->method = $method;
			$this->class = $class;
			$this->isReadOnly = $readonly;
		}

		/**
		 * Resets and removes all elements in the form.
		 */
		public function resetElements() {
			$this->fields = array();
		}

		public function fixed($id, $label, $value = '', $attributes = array()) {
			$field = array('id' => $id, 'type' => 'fixed', 'label' => $label, 'value' => $value);
			foreach ($attributes as $att => $val) {
				$field['attributes'][$att] = $val;
			}
			$this->fields[] = $field;
			return $this;
		}

		public function hidden($id, $value = "", $attributes = array()) {
			$field = array('id' => $id, 'type' => 'hidden', 'value' => $value, 'attributes'=>array());
			foreach ($attributes as $att => $val) {
				$field['attributes'][$att] = $val;
			}
			$this->fields[] = $field;
			return $this;
		}

		public function textbox($id, $label, $value = "", $attributes = array()) {
			$field = array('id' => $id, 'type' => 'textbox', 'label' => $label, 'value' => $value, 'attributes' => array());
			foreach ($attributes as $att => $val) {
				$field['attributes'][$att] = $val;
			}
			$this->fields[] = $field;
			return $this;
		}

		public function datepicker($id, $label, $value = "", $attributes = array()) {
			if(empty($value)) {
				$value = '(select)';
				if(array_key_exists('class', $attributes)) {
					$attributes['class'] .= ' clearonclick';
				} else {
					$attributes['class'] = 'clearonclick';
				}
			}

			$field = array('id' => $id, 'type' => 'datepicker', 'label' => $label, 'value' => $value, 'attributes' => array());
			foreach ($attributes as $att => $val) {
				$field['attributes'][$att] = $val;
			}
            $field['value'] = date('Y-m-d', strtotime($value));
            $field['attributes']['value'] = $field['value'];
			$this->fields[] = $field;

			return $this;
		}

		public function datetimepicker($id, $label, $value = "", $attributes = array()) {
			if(empty($value)) {
				$value = 'dd-mm-yyyy 00:00';
			}

			$field = array('id' => $id, 'type' => 'datetimepicker', 'label' => $label, 'value' => $value, 'attributes' => array());
			foreach ($attributes as $att => $val) {
				$field['attributes'][$att] = $val;
			}
			$this->fields[] = $field;
			return $this;
		}

		public function timepicker($id, $label, $value = "", $attributes = array()) {
			$field = array('id'=>$id, 'type'=>'timepicker', 'label'=>$label, 'value'=>$value, 'attributes'=>array());
			foreach($attributes as $att=>$val) {
				$field[$attributes][$att] = $val;
			}
			$this->fields[] = $field;
			return $this;
		}

		public function password($id, $label, $value = "", $attributes = array()) {
			$field = array('id' => $id, 'type' => 'password', 'label' => $label, 'value' => $value, 'attributes' => array());
			foreach ($attributes as $att => $val) {
				$field['attributes'][$att] = $val;
			}
			$this->fields[] = $field;
			return $this;
		}

		public function file($id, $label, $value = "", $attributes = array()) {
			$field = array('id' => $id, 'type' => 'file', 'label' => $label, 'value' => $value, 'attributes' => array());
			$this->isUploadForm = true;
			foreach ($attributes as $att => $val) {
				$field['attributes'][$att] = $val;
			}
			$this->fields[] = $field;
			return $this;
		}

		public function textarea($id, $label, $value = "", $attributes = array()) {
			$field = array('id' => $id, 'type' => 'textarea', 'label' => $label, 'value' => $value, 'attributes' => array());
			foreach ($attributes as $att => $val) {
				$field['attributes'][$att] = $val;
			}
			$this->fields[] = $field;
			return $this;
		}

		public function select($id, $label, $value = array(), $multiselect = false, $attributes = array()) {
			$field = array('id' => $id, 'type' => 'select', 'label' => $label, 'multiselect' => $multiselect, 'value' => $value, 'attributes' => array());
			foreach ($attributes as $att => $val) {
				$field['attributes'][$att] = $val;
			}
			$this->fields[] = $field;
			return $this;
		}

		public function radio($id, $label, $value = "", $attributes = array()) {
			$field = array('id' => $id, 'type' => 'radio', 'label' => $label, 'value' => $value, 'attributes' => array());
			foreach ($attributes as $att => $val) {
				$field['attributes'][$att] = $val;
			}
			$this->fields[] = $field;
			return $this;
		}

		public function checkbox($id, $label, $checked = false, $value = "", $attributes = array()) {
			$field = array('id' => $id, 'type' => 'checkbox', 'label' => $label, 'checked' => $checked, 'value' => $value, 'attributes' => array());
			foreach ($attributes as $att => $val) {
				$field['attributes'][$att] = $val;
			}
			$this->fields[] = $field;
			return $this;
		}

		public function reset($id, $label, $nameMe = true, $attributes = array()) {
			$field = array('id' => $id, 'type' => 'reset', 'label' => $label, 'nameMe' => $nameMe, 'attributes'=>array());
			foreach ($attributes as $att => $val) {
				$field['attributes'][$att] = $val;
			}
			$this->fields[] = $field;
			return $this;
		}

		public function submit($id, $label, $nameMe = true, $attributes = array()) {
			$field = array('id' => $id, 'type' => 'submit', 'label' => $label, 'nameMe' => $nameMe, 'attributes'=>array());
			foreach ($attributes as $att => $val) {
				$field['attributes'][$att] = $val;
			}
			$this->fields[] = $field;
			return $this;
		}

		public function button($id, $label, $type = 'button', $nameMe = true, $class = null, $attributes = array()) {
			$field = array('id' => $id, 'type' => $type, 'label' => $label, 'nameMe' => $nameMe, 'attributes'=> array('class' => $class));
			foreach ($attributes as $att => $val) {
				$field['attributes'][$att] = $val;
			}
			$this->fields[] = $field;
			return $this;
		}

		public function image($id, $src, $nameMe = true, $attributes = array()) {
			$field = array('id' => $id, 'type' => 'image', 'src' => $src, 'nameMe' => $nameMe, 'attributes'=>array());
			foreach ($attributes as $att => $val) {
				$field['attributes'][$att] = $val;
			}
			$this->fields[] = $field;
			return $this;
		}

		public function html($html) {
			$this->fields[] = array('type' => 'html', 'value' => $html);
			return $this;
		}

		public function setFieldErrors($fieldErrors) {
			$this->fieldErrors = $fieldErrors;
		}

		public function render($displayErrors = true, $escapeEntities = false) {
			// some fields don't need labels
			$doNotNeedLabels = array('submit', 'reset', 'hidden', 'html', 'button', 'submitbutton', 'image'); //'radio'
			$out = "<!-- Start of form {$this->name} -->\n";
			if (!empty($this->action)) {
				$out .= '<form id="' . $this->name . '" action="' . $this->action . '" method="' . strtolower($this->method) .
					'" name="' . $this->name . '"' . ($this->isUploadForm ? ' enctype="multipart/form-data"' : '') . ' class="' . $this->class . '">' . "\n";
				$out .= '<div class="form ' . $this->class . '" id="' . $this->name . 'Container">' . "\n";
			} else {
				$out .= '<div class="form ' . $this->class . '" id="' . $this->name . '" class="' . $this->class . '">' . "\n";
			}

			foreach ($this->fields as $properties) {
				if (isset($properties['id']))
					$field = $properties['id'];

				if ((isset($field)) && ($displayErrors && array_key_exists($field, $this->fieldErrors))) {
					$hasError = true;
				} else {
					$hasError = false;
				}

				if(!in_array($properties['type'], $doNotNeedLabels)) {
					$out .= '<span class="formElement ' . ($properties['type'] == 'radio' || $properties['type'] == 'checkbox' ? 'rightLabel' : '') . '" id="' . $field . 'Element"' .
							(array_key_exists('attributes', $properties) && array_key_exists('hide', $properties['attributes']) && $properties['attributes']['hide'] ?
								' style="display: none;"' : '') . '>' . "\n";
					$label = '<label for="' . $field . '"class="' . ($hasError ? ' formError' : '') . '">' .
							($escapeEntities ? htmlentities($properties['label'], ENT_COMPAT, 'UTF-8') : $properties['label']) . "</label>\n";

					if($properties['type'] != 'checkbox' && $properties['type'] != 'radio') {
						$out .= $label;
					}
				}

				$readonly = $this->isReadOnly && $properties['type'] != 'button';
				if(array_key_exists('attributes', $properties) && array_key_exists('readonly', $properties['attributes'])) {
					$readonly = true;
					unset($properties['attributes']['readonly']);
				}

				$attributes = '';
				$className = '';
				if (isset($properties['attributes'])) {
					foreach ($properties['attributes'] as $att => $val) {
						if($att == 'class') {
							$className .= ' ' . $val;
						} else if($att != 'name' && $att != 'hide') {
							$attributes .= ' ' . $att . '="' . $val . '"';
						}
					}
				}

				if(array_key_exists('attributes', $properties) && array_key_exists('name',$properties['attributes'])) {
					$name = $properties['attributes']['name'];
					unset($properties['attributes']['name']);
				} else if(isset($field)) {
					$name = $field;
				} else {
					$name = '';
				}

				switch ($properties['type']) {
					case 'fixed':
						$out .= '<span class="fixed' . $className . '" id="' . $field . '">' . $properties['value'] . '</span>' . "\n";
						break;
					case 'hidden':
						$out .= '<input type="hidden" class="element' . $className . '" name="' . $name . '" id="' . $field . '" value="' . $properties['value'] . '"' . $attributes . ' />' . "\n";
						break;
					case 'textbox':
						$out .= '<input type="text" class="element' . $className . '" name="' . $name . '" id="' . $field . '" value="' . $properties['value'] .'"' . $attributes . ($readonly ? ' readonly="readonly"' : '') . ' />' . "\n";
						break;
					case 'datepicker':
						$out .= '<input type="date" class="element' . $className . ' datepicker" name="' . $name . '" id="' . $field . '" value="' . $properties['value'] .'"' . $attributes . ($readonly ? ' readonly="readonly"' : '') . ' />' . "\n";
						break;
					case 'datetimepicker':
						$out .= '<input type="datetime" class="element' . $className . ' datetimepicker" name="' . $name . '" id="' . $field . '" value="' . $properties['value'] .'"' . $attributes . ($readonly ? ' readonly="readonly"' : '') . ' />' . "\n";
						break;
					case 'timepicker':
						$hour = date('H');
						$min = date('i');
						if(!empty($properties['value'])) {
							$tmp = explode(':', $properties['value']);
							if(count($tmp) > 1) {
								if($tmp[0] >= 0 && $tmp[0] < 24) {
									$hour = (int)$tmp[0];
								}
								if($tmp[1] >= 0 && $tmp[1] < 60) {
									$min = (int)$tmp[1];
								}
							}
						}

						$hasSecs = isset($attributes['secs']);

						$out .= '<select class="element ' . $className . ' time-hour" name="' . $name . '_hour" id="' . $field . '_hour" ' . $attributes . ($readonly ? ' readonly="readonly"' : '') . '>';
						for($i=0;$i<23;++$i) {
							$out .= '<option value="' . sprintf("%02d", $i) . '"' . ($i == $hour ? ' selected="selected"' : '') . '>' . sprintf("%02d", $i) . '</option>';
						}
						$out .= '</select>:<select class="element ' . $className . ' time-minute" name="' . $name . '_minute" id="' . $field . '_minute" ' . $attributes . ($readonly ? ' readonly="readonly"' : '') . '>';
						for($i=0;$i<60;$i+=5) {
							$out .= '<option value="' . sprintf("%02d", $i) . '"' . ($i >= $min && $i < ($min+5) ? ' selected="selected"' : '') . '>' . sprintf("%02d", $i) . '</option>';
						}
						$out .= '</select>';
						break;
					case 'password':
						$out .= '<input type="password" class="element' . $className . '" name="' . $name . '" id="' . $field . '" value="' . $properties['value'] . '"' . $attributes . ($readonly ? ' readonly="readonly"' : '') . ' />' . "\n";
						break;
					case 'file':
						$out .= '<input type="file" class="element' . $className . '" name="' . $name . '" id="' . $field . '" value="' . $properties['value'] . '"' . $attributes . ($readonly ? ' disabled="disabled"' : '') . ' />' . "\n";
						break;
					case 'textarea':
						$out .= '<textarea class="element' . $className . '" name="' . $name . '" id="' . $field . '"' . $attributes . ($readonly ? ' readonly="readonly"' : '') . '>' . $properties['value'] . '</textarea>' . "\n";
						break;
					case 'select':
						$multiple = ($properties['multiselect'] ? ' multiple="multiple"' : '');
						$out .= '<select class="element' . $className . '" name="' . $name . ($properties['multiselect'] ? '[]' : '') . '" id="' . $field . '"' . $multiple . $attributes . ($readonly ? ' readonly="readonly" disabled="disabled"' : '') . ">\n";
						if($properties['multiselect'] && count($properties['value']) > 0 && !isset($properties['value'][0]['value'])) {
							foreach($properties['value'] as $groupName=>$values) {
								if(!empty($groupName)) {
									$out .= '<optgroup label="' . $groupName . '">';
								}
								foreach($values as $val) {
									$out .= '<option value="' . $val['value'] . '"' . (isset($val['selected']) && $val['selected'] ? ' selected="selected"' : '') . '>' . htmlentities($val['label'], ENT_COMPAT, 'UTF-8') . "</option>\n";
								}
								if(!empty($groupName)) {
									$out .= '</optgroup>';
								}
							}
						} else {
							if(!is_array($properties['value'])) {
								throw new Exception('FormControl: select values not an array (' . $name . ')');
							}
							foreach ($properties['value'] as $values) {
								$out .= '<option value="' . $values['value'] . '"' . (isset($values['selected']) && $values['selected'] ? ' selected="selected"' : '') . '>' . htmlentities($values['label'], ENT_COMPAT, 'UTF-8') . "</option>\n";
							}
						}
						$out .= "</select>\n";
						break;
					case 'radio':
						$out .= '<span class="spanLabel">' . htmlentities($properties['label'], ENT_COMPAT, 'UTF-8') . '</span>';
						foreach ($properties['value'] as $key => $value) {
							$checked = "";
							$out .= '<span class="radioElement">';
							if (array_key_exists('checked', $value))
								$checked = ($value['checked'] == true ? ' checked="checked"' : '');
							$out .= '<input type="radio" class="element' . $className . '" name="' . $name . '" id="' . $field . '_' . $key . '" value="' . $key . '"' . $checked . $attributes . ($readonly ? ' disabled="disabled"' : '') . ' />' . "\n";
							if(array_key_exists('attributes', $properties) && in_array('raw', $properties['attributes'])) {
								$label = $value['label'];
							} else {
								$label = htmlentities($value['label'], ENT_COMPAT, 'UTF-8');
							}
							$out .= '<label for="' . $field . '_' . $key . '" class="radioLabel">' . $label . "</label>\n";
							if(array_key_exists('additional', $value)) {
								$out .= $value['additional'];
							}
							$out .= '</span>';
						}
						break;
					case 'checkbox':
						$checked = ($properties['checked'] == true ? ' checked="checked"' : '');
						$out .= '<input type="checkbox" class="element' . $className . '" name="' . $name . '" id="' . $field . '" value="' . $properties['value'] . '"' . $checked . $attributes . ($readonly ? ' disabled="disabled"' : '') . '/>' . "\n";
						$out .= $label;
						break;
					case 'asubmit':
						$out .= '<input type="submit" class="submit' . $className . '"' . (!isset($properties['nameMe']) || $properties['nameMe'] ? ' name="' . $field . '"' : '') . ' id="' . $field . '" value="' . $properties['label'] . '"' . ($readonly ? ' disabled="disabled"' : '') . ' />' . "\n";
						break;
					case 'reset':
						$out .= '<input type="reset" class="reset' . $className . '"' . (!isset($properties['nameMe']) || $properties['nameMe'] ? ' name="' . $field . '"' : '') . ' id="' . $field . '" value="' . $properties['label'] . ($readonly && !$this->isReadOnly ? ' disabled="disabled"' : '') . '" />' . "\n";
						break;
					case 'button':
						$out .= '<button type="button" class="button' . $className . '"' . (!isset($properties['nameMe']) || $properties['nameMe'] ? ' name="' . $field . '"' : '') . ' id="' . $field . '"' . $attributes . ($readonly ? ' disabled="disabled"' : '') . '><span>' . $properties['label'] . "</span></button>\n";
						break;
					case 'submit':
					case 'submitbutton':
						$out .= '<button type="submit" class="button' . $className . '"' . (!isset($properties['nameMe']) || $properties['nameMe'] ? ' name="' . $field . '"' : '') . ' id="' . $field . '"' . $attributes . ($readonly ? ' disabled="disabled"' : '') . '><span>' . $properties['label'] . "</span></button>\n";
						break;
					case 'image':
						$out .= '<input type="image" class="image' . $className . '" src="' . $properties['src'] . '" ' . (!isset($properties['nameMe']) || $properties['nameMe'] ? ' name="' . $field . '"' : '') . ' id="' . $field . '"' . $attributes . ($readonly ? ' disabled="disabled"' : '') . ' />' . "\n";
						break;
					case 'html':
						$out .= $properties['value'];
						break;
				}
				if ($hasError) {
					if (isset($this->fieldErrors[$field]) && !in_array($properties['type'], $doNotNeedLabels)) {
						$out .= '<span class="formError">';
						if (is_array($this->fieldErrors[$field])) {
							$out .= $this->fieldErrors[$field]['message'];
						} else {
							$out .= $this->fieldErrors[$field];
						}
						$out .= "</span>\n";
					}
				}
				if (!in_array($properties['type'], $doNotNeedLabels)) // Submit buttons and hidden fields don't need labels
					$out .= '</span>' . "\n";
			}
			$out .= "</div>\n";
			if (!empty($this->action))
				$out .= "</form>\n";
			$out .= "<!-- End of form {$this->name} -->\n";
			return $out;
		}

		public function __toString() {
			return $this->render();
		}

		public static function selectOption(&$values, $current = '', $forceNull = false, $isArray = false) {
			if(!is_array($values)) {
				return false;
			}

			if(is_array($current)) {
				foreach($current as $value) {
					selectOption($values, $value, $forceNull, true);
				}
				return true;
			}

			if(empty($current) && $current !== 0) {
				if($forceNull) {
					foreach($values as &$val) {
						if($val['value'] == '' || is_null($val['value'])) {
							$val['selected'] = true;
							return true;
						}
					}
				}
				foreach($values as &$val) {
					if(array_key_exists('default', $val)) {
						$val['selected'] = true;
					} else {
						if(!$isArray && array_key_exists('selected', $val)) {
							unset($val['selected']);
						}
					}
				}
			} else {
				foreach($values as &$val) {
					if($current == $val['value']) {
						$val['selected'] = true;
					} else {
						if(!$isArray && array_key_exists('selected', $val)) {
							unset($val['selected']);
						}
					}
				}
			}
		}

	}

?>