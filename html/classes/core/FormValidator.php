<?php

	class FormValidator {

		public static function validate($fields, $rules, $fieldLabels = array()) {
			$fieldErrors = array();
			if (!is_array($fields) || !is_array($rules))
				throw new Exception('Form validation fields or rules are null');
			foreach ($rules as $field => $fieldRules) {
				if (array_key_exists($field, $fields)) {
					$value = $fields[$field];
					if (is_array($fieldRules)) {
						if (array_key_exists('required', $fieldRules)) {
							if (array_key_exists('checkEmpty', $fieldRules))
								$checkEmpty = $fieldRules['checkEmpty'];
							else
								$checkEmpty = true;
							if ($fieldRules['required'] == true && !self::isValid($value, $checkEmpty))
								$fieldErrors[$field] = getString("formErrors", "isRequired");
						}
						if (array_key_exists('validator', $fieldRules)) {
							switch ($fieldRules['validator']) {
								case 'email':
									if (!self::isEmail($value))
										$fieldErrors[$field] = getString("formErrors", "validEmail");
									break;
								case 'numeric':
									if (!is_numeric($value))
										$fieldErrors[$field] = getString("formErrors", "numeric");
									break;
								case 'datetime':
									if(!empty($value)) {
										$split = explode(' ', $value);
										if(count($split) != 2) {
											$fieldErrors[$field] = 'Not a valid date/time format';
										} else {
											$time = explode(':', $split[1]);
											if(count($time) < 2 || count($time) > 3) {
												$fieldErrors[$field] = 'Not a valid time format';
											} else {
												$hours = (int)$time[0];
												$mins = (int)$time[1];
												if(isset($time[2])) {
													$secs = (int)$time[2];
												}

												if(($hours < 0 || $hours > 23) || ($mins < 0 || $mins > 59) || (isset($secs) && ($secs < 0 || $secs > 59))) {
													$fieldErrors[$field] = 'Not a valid time';
												}
											}
										}
									}
								case 'date':
									if(!empty($value)) {
										$datePart = substr(trim($value), 0, 10);
										if(substr($value, 2, 1) == '/') {
											$date = explode('/', $datePart);
										} else if(substr($value, 2, 1) == '-') {
											$date = explode('-', $datePart);
										} else if(substr($value, 4, 1) == '-') {
											$date = array_reverse(explode('-', $datePart));
										} else {
											$fieldErrors[$field] = 'Not a valid date format';
										}
										if(isset($date)) {
											if(count($date) == 3) {
												$day = (int)$date[0];
												$month = (int)$date[1];
												$year = (int)$date[2];

												if(!checkdate($month, $day, $year)) {
													$fieldErrors[$field] = 'Not a valid date - ' . $day . '/' . $month . '/' . $year ;
												} else {
													// check range if one has been specified
													if(array_key_exists('mindate', $fieldRules)) {
														if(strtotime($datePart) < strtotime($fieldRules['mindate'])) {
															$fieldErrors[$field] = 'Date not valid';
														}
													}
													if(array_key_exists('maxdate', $fieldRules)) {
														if(strtotime($datePart) > strtotime($fieldRules['maxdate'])) {
															$fieldErrors[$field] = 'Date not valid';
														}
													}
												}
											} else {
												$fieldErrors[$field] = 'Not a valid date format';
											}
										}
									}
									break;
							}
						}
						if (array_key_exists('matches', $fieldRules)) {
							if (!self::matches($value, $fields[$fieldRules['matches']]))
								$fieldErrors[$field] = getString("formErrors", "match",
									array(getString('form', $fieldRules['matches'])));
						}
						if (array_key_exists('minlength', $fieldRules)) {
							if (strlen($value) < $fieldRules['minlength'])
								$fieldErrors[$field] = getString("formErrors", "minlength", array($fieldRules['minlength']));
						}
						if (array_key_exists('maxlength', $fieldRules)) {
							if (strlen($value) > $fieldRules['maxlength'])
								$fieldErrors[$field] = getString("formErrors", "maxlength", array($fieldRules['maxlength']));
						}
						if (array_key_exists('between', $fieldRules)) {
							if (!self::between($value, $fieldRules['min'], $fieldRules['max']))
								$fieldErrors[$field] = getString("formErrors", "between", array($fieldRules['min'], $fieldRules['max']));
						}
						if (array_key_exists('requiredOr', $fieldRules)) {
							if (empty($fields[$field]) && empty($fields[$fieldRules['requiredOr']])) {
								$requireOrFieldLabel = "";
								if (array_key_exists($fieldRules['requiredOr'], $fieldLabels))
									$requireOrFieldLabel = $fieldLabels[$fieldRules['requiredOr']];
								$fieldErrors[$field] = getString("formErrors", "requiredOr", array($requireOrFieldLabel));
							}
						}
					}
				} else if (array_key_exists('required', $fieldRules) && !array_key_exists($field, $fields)) {
					$fieldErrors[$field] = getString("formErrors", "isRequired");
				}
			}
			return $fieldErrors;
		}

		public static function isValid($field , $checkEmpty = true) {
			return isset($field) && (!$checkEmpty || !empty($field));
		}

		public static function isEmail($field) {
			if (empty($field))
				return false;
			return preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $field) > 0;
		}

		public static function matches($field1, $field2) {
			return $field1 == $field2;
		}

		public static function between($field, $min, $max) {
			return $field >= $min && $field <= $max;
		}

	}

?>