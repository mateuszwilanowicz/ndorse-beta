<?php

	class XPayResponse extends XPayTransaction {
		
		// Result codes
		public static $RESULT_SUCCESSFUL = 1;
		public static $RESULT_DECLINED = 2;
		public static $RESULT_FAILED = 0;
		
		// Security responses
		public static $SR_NO_INFO_AVAILABLE = 0;
		public static $SR_DATA_NOT_CHECKED = 1;
		public static $SR_DATA_MATCHED = 2;
		public static $SR_SHOULD_HAVE_SECURITY_CODE = 3;
		public static $SR_DATA_NOT_MATCHED = 4;
		public static $SR_PARTIAL_MATCH = 8;
		
		public static $ERROR_SUCCESS = 0;
		public static $ERROR_SECURITY_CODE = 1;
		public static $ERROR_ADDRESSPOSTCODE = 2;
		public static $ERROR_XPAY_MISC = 3;
		public static $ERROR_BANK_DECLINE = 4;
		public static $ERROR_BANK_AUTH = 5;
		
		private $live;
		private $transactionReference;
		private $authCode;
		private $result;
		private $message;
		private $settleStatus;
		private $securityResponseSecurityCode;
		private $securityResponseAddress;
		private $securityResponsePostCode;
		private $transactionCompletedTimestamp;		// YYYY-MM-DD HH:mm:ss
		private $transactionVerifier;
		
		private $orderReference;
		private $orderInformation;
		
		private $parentTransactionID;
		
		private $enrolled;
		private $html;
		
		private $acsUrl;
		private $termUrl;
		
		private $paReq;
		private $mD;
		
		private $rawXML;	// Useful for debugging and other things...
		
		public function __call($method,$arguments) {
			if(substr($method,0,3) == 'get') {
				$key = strtolower(substr($method,3,1)) . substr($method,4);

				if(isset($this->$key))
					return $this->$key;
			}

			if(substr($method,0,3) == 'set') {
				$key = strtolower(substr($method,3,1)) . substr($method,4);
				$this->$key = $arguments[0];
			}
		}
		
		public function loadFromXML($xmlString) {
			
			$this->rawXML = $xmlString;
			$xml = simplexml_load_string($xmlString);
			
			$this->live = (bool) $xml->attributes()->Live;
			$this->type = (string) $xml->attributes()->Type;
			
			$vars = get_object_vars($this);
			
			foreach ($xml->children() as $parent) {
				foreach ($parent as $child) {
					foreach ($child as $key => $value) {
						$key = strtolower(substr($key, 0, 1)) . substr($key, 1);
						
						if (array_key_exists($key, $vars))
							$this->$key = (string) $value;
					}
				}
			}
			
		}
		
		public function setParentTransactionID($parentTransactionID) {
			$this->parentTransactionID = $parentTransactionID;
		}
		
		public function getXPayMessage() {
			return $this->message;
		}
		
		public function getResultCode() {
			
			switch($this->result) {
			
				// XPay responded with a success code
				case self::$RESULT_SUCCESSFUL:
					// These are our own checks
					
					if ($this->securityResponseSecurityCode != self::$SR_DATA_MATCHED) {
						// If security code did not match we will decline it.
						$error = self::$ERROR_SECURITY_CODE;
					} else if ($this->securityResponsePostCode != self::$SR_DATA_MATCHED || $this->securityResponseAddress != self::$SR_DATA_MATCHED) {
						// if address or postcode don't match we set it aside for manual authentication
						$error = self::$ERROR_ADDRESSPOSTCODE;
					}
					
					// Otherwise it was successful
					if ($this->securityResponseAddress == self::$SR_DATA_MATCHED && $this->securityResponsePostCode == self::$SR_DATA_MATCHED &&
							$this->securityResponseSecurityCode == self::$SR_DATA_MATCHED) {
						$error = self::$ERROR_SUCCESS;
					}
					break;
				
				// XPay failed the request
				case self::$RESULT_FAILED:
					
					$error = self::$ERROR_XPAY_MISC;
					
					break;
									
				// The bank declined the request
				case self::$RESULT_DECLINED:
					
					switch ($this->authCode) {
						// The bank declined it
						case 'DECLINED':
							$error = self::$ERROR_BANK_DECLINE;
							break;
						
						// Call the bank for authorisation
						case 'CALL AUTH CENTRE':
							$error = self::$ERROR_BANK_AUTH;
							break;
						default:
							$error = self::$ERROR_XPAY_MISC;
					}
					
					break;
			}
			
			return $error;
				
		}
		
		public function getMessage($error = "", $onlyXpayMessage = false) {
			
			if ($onlyXpayMessage)
				return $this->message;
			
			if (!isset($error) || empty($error))
				$error = $this->getResultCode($this->result);
			
			switch ($error) {
				case self::$ERROR_SUCCESS:
					return getString('payment', 'success', array($this->orderReference));
					break;
				
				case self::$ERROR_SECURITY_CODE:
					return getString('payment', 'invalidCard');
					break;
				
				case self::$ERROR_ADDRESSPOSTCODE:
					return getString('payment', 'verifyAddress');
					break;
				
				case self::$ERROR_XPAY_MISC:
					if (strlen($this->message) > 0)
						return getString('payment', 'unknownError', array($this->message));
					else
						return getString('payment', 'error');
					break;
				
				case self::$ERROR_BANK_DECLINE:
					return getString('payment', 'invalidCard');
					break;
				
				case self::$ERROR_BANK_AUTH:
					return getString('payment', 'manualVerification');
					break;
			}
			
		}
		
	}

?>