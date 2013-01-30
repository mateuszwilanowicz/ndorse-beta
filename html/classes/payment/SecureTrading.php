<?php

	require_once('securetrading/XPayTransaction.php');
	require_once('securetrading/XPayRequest.php');
	require_once('securetrading/XPayResponse.php');

	class SecureTrading extends PaymentType {

		public static $PAYMENT_ATT_KEY_AUTHCODE = "XPAYTRANSAUTH";
		public static $PAYMENT_ATT_KEY_REFERENCE = "XPAYTRANSREF";
		public static $PAYMENT_ATT_KEY_RESULT = "XPAYTRANSRESULT";
		public static $PAYMENT_ATT_KEY_TIMESTAMP = "XPAYTRANSTIME";
		public static $PAYMENT_ATT_KEY_VERIFIER = "XPAYTRANSVERI";
		public static $PAYMENT_ATT_KEY_MESSAGE = "XPAYMESSAGE";
		public static $PAYMENT_ATT_KEY_SECURITY_FAIL = "XPAYSECFAIL";
		public static $PAYMENT_ATT_KEY_CARD_TYPE = "XPAYCARDTYPE";
		// 3D Secure
		public static $PAYMENT_ATT_KEY_3D_ENROLLED = "XPAY3DENROLLED";

		// XPay type => Display name
		public static $CARD_TYPES = array(
			'Visa' => 'Visa',
			'MasterCard' => 'MasterCard',
			'Solo' => 'Solo',
			'Maestro' => 'Maestro',
			'Delta' => 'Delta',
			'Amex' => 'American Express',
			'Electron' => 'Electron'
		);

		private $cardType;
		private $cardName;
		private $cardNumber;
		private $cardStartDateMonth;
		private $cardStartDateYear;
		private $cardEndDateMonth;
		private $cardEndDateYear;
		private $cardIssueNumber;
		private $cardSecurityCode;

		private $threeDSecureRedirectHtml;
		private $enrolled;
		private $paRes;
		private $mD;

		private $authorisationSuccessful = false;

		function SecureTrading() {
			parent::PaymentType();
			$this->uses3DSecure = true;
		}

		public function getCardType() {
			return $this->cardType;
		}

		public function getCardName() {
			return $this->cardName;
		}

		public function getCardNumber() {
			return $this->cardNumber;
		}

		public function getObfuscatedCardNumber() {
			return str_repeat('X', 12) . substr($this->getCardNumber(), -4);
		}

		public function getCardStartDateMonth() {
			return str_pad($this->cardStartDateMonth, 2, '0', STR_PAD_LEFT);
		}

		public function getCardStartDateYear() {
			return $this->cardStartDateYear;
		}

		public function getCardEndDateMonth() {
			return str_pad($this->cardEndDateMonth, 2, '0', STR_PAD_LEFT);
		}

		public function getCardEndDateYear() {
			return $this->cardEndDateYear;
		}

		public function getCardIssueNumber() {
			return $this->cardIssueNumber;
		}

		public function getCardSecurityCode() {
			return $this->cardSecurityCode;
		}

		public function getPaymentForm($args = array()) {
			$paymentForm = new FormControl();

			// Populate card types
			$cardTypes = array(
				array('value' => '', 'label' => 'Please select...'),
			);

			$storeCardTypes = array();
			if ($GLOBALS['store']->getAttributes()->contains('XPAYCARDTYPES')) {
				$storeCardTypes = explode('|', $GLOBALS['store']->getAttributes()->get('XPAYCARDTYPES')->getValue());
			}
			foreach (self::$CARD_TYPES as $key => $value) {
				if (sizeof($storeCardTypes) == 0 || in_array($key, $storeCardTypes))
					$cardTypes[] = array('value' => $key, 'label' => $value);
			}

			if (isset($args['cardType'])) {
				for ($i = 0; $i < sizeof($cardTypes); $i++) {
					if ($args['cardType'] == $cardTypes[$i]['value'])
						$cardTypes[$i]['selected'] = true;
				}
			}

			// Populate month selectors
			$cardStartDateMonth = array(
				array('value' => '', 'label' => '--')
			);
			for ($i = 1; $i < 13; $i++)
				$cardStartDateMonth[] = array('value' => $i, 'label' => str_pad($i, 2, '0', STR_PAD_LEFT), 'selected' =>
					isset($args['cardStartDateMonth']) && $args['cardStartDateMonth'] == $i);

			$cardEndDateMonth = array(
				array('value' => '', 'label' => '--')
			);
			for ($i = 1; $i < 13; $i++)
				$cardEndDateMonth[] = array('value' => $i, 'label' => str_pad($i, 2, '0', STR_PAD_LEFT), 'selected' =>
					isset($args['cardEndDateMonth']) && $args['cardEndDateMonth'] == $i);

			// Populate year selectors
			$cardStartDateYear = array(
				array('value' => '', 'label' => '----')
			);
			for ($i = intval(date("Y")); $i > date("Y") - 11; $i--) {
				$cardStartDateYear[] = array('value' => $i, 'label' => $i, 'selected' =>
					isset($args['cardStartDateYear']) && intval($args['cardStartDateYear']) == $i);
			}

			$cardEndDateYear = array(
				array('value' => '', 'label' => '----')
			);
			for ($i = intval(date("Y")); $i < date("Y") + 11; $i++) {
				$cardEndDateYear[] = array('value' => $i, 'label' => $i, 'selected' =>
					isset($args['cardEndDateYear']) && intval($args['cardEndDateYear']) == $i);
			}

			// Create the rest of the form
			$paymentForm->select('cardType', getString('paymentType', 'cardType'), false, $cardTypes);
			$paymentForm->textbox('cardName', getString('paymentType', 'cardName'),
				isset($args['cardName']) ? $args['cardName'] : '');
			$paymentForm->textbox('cardNumber', getString('paymentType', 'cardNumber'),
				isset($args['cardNumber']) ? $args['cardNumber'] : '');
			$paymentForm->html('<div class="cardStartDate">');
			$paymentForm->select('cardStartDateMonth', getString('paymentType', 'cardStartDateMonth'), false, $cardStartDateMonth);
			$paymentForm->select('cardStartDateYear', getString('paymentType', 'cardStartDateYear'), false, $cardStartDateYear);
			$paymentForm->html('</div><div class="cardEndDate">');
			$paymentForm->select('cardEndDateMonth', getString('paymentType', 'cardEndDateMonth'), false, $cardEndDateMonth);
			$paymentForm->select('cardEndDateYear', getString('paymentType', 'cardEndDateYear'), false, $cardEndDateYear);
			$paymentForm->html('</div>');
			$paymentForm->textbox('cardIssueNumber', getString('paymentType', 'cardIssueNumber'),
				isset($args['cardIssueNumber']) ? $args['cardIssueNumber'] : '');
			$paymentForm->textbox('cardSecurityCode', getString('paymentType', 'cardSecurityCode'),
				isset($args['cardSecurityCode']) ? $args['cardSecurityCode'] : '');
			if (getString('paymentType', 'cardSecurityCodeInfo') != '' && !array_key_exists('ajax', $args)) {
				$paymentForm->html('<span id="cardSecurityCodeInfo">' . getString('paymentType', 'cardSecurityCodeInfo') . '</span>');
			}

			if (isset($GLOBALS['paymentFormErrors'][$this->getClassName()]))
				$paymentForm->setFieldErrors($GLOBALS['paymentFormErrors'][$this->getClassName()]);

			return $paymentForm;
		}

		/**
		 * Returns the payment type values as an array
		 * @return mixed[] The payment type values
		 */
		public function toArray() {
			$values = array();
			$values['cardType'] = $this->getCardType();
			$values['cardName'] = $this->getCardName();
			$values['cardNumber'] = $this->getCardNumber();
			$values['cardStartDateMonth'] = $this->getCardStartDateMonth();
			$values['cardStartDateYear'] = $this->getCardStartDateYear();
			$values['cardEndDateMonth'] = $this->getCardEndDateMonth();
			$values['cardEndDateYear'] = $this->getCardEndDateYear();
			$values['cardIssueNumber'] = $this->getCardIssueNumber();
			$values['cardSecurityCode'] = $this->getCardSecurityCode();
			return $values;
		}

		/**
		 * Validate the details passed back when the form is submitted
		 * The card validation is quite complex and so it is easier to do our own validation
		 * rather than using FormValidator.
		 */
		public function validatePaymentForm($args) {
			$formErrors = array();

			// Validate card type
			if (isset($args['cardType']) && in_array($args['cardType'], array_keys(self::$CARD_TYPES)))
				$this->cardType = $args['cardType'];
			else
				$formErrors['cardType'] = array('message' => 'cardTypeError', 'displayLabel' => false);

			// Validate cardholder name
			if (!isset($args['cardName']) || empty($args['cardName']))
				$formErrors['cardName'] = array('message' => 'paymentType', 'displayLabel' => false);
			else
				$this->cardName = trim($args['cardName']);

			// Validate card number
			$cardSeparators = array(" ", "-", "/", "\\");
			if (isset($args['cardNumber'])) {
				$cardNumber = str_replace($cardSeparators, "", $args['cardNumber']);
				if (strlen($cardNumber) >= 10 && strlen($cardNumber) <= 20)
					$this->cardNumber = str_replace($cardSeparators, "", $args['cardNumber']);
				else
					$formErrors['cardNumber'] = array('message' => 'cardNumberError', 'displayLabel' => false);
			} else
				$formErrors['cardNumber'] = array('message' =>'cardNumberError', 'displayLabel' => false);

			// Validate start month
			if (isset($args['cardStartDateMonth']) && is_numeric($args['cardStartDateMonth']) &&
					$args['cardStartDateMonth'] > 0 && $args['cardStartDateMonth'] < 13) {
				$this->cardStartDateMonth = $args['cardStartDateMonth'];
			}

			// Validate start year
			if (isset($args['cardStartDateYear']) && is_numeric($args['cardStartDateYear'])) {
				$this->cardStartDateYear = $args['cardStartDateYear'];
			}

			/* Start date is not a required field
			if (!isset($this->cardStartDateMonth) || $this->cardStartDateMonth > date("m") &&
					!isset($this->cardStartDateYear) || $this->cardStartDateYear > date("Y")) {
				$this->cardStartDateMonth = null;
				$this->cardStartDateYear = null;
				$formErrors['cardStartDateYear'] = array('message' => getString('paymentType', 'cardStartDateError'), 'displayLabel' => false);
			}
			*/

			// Validate end month
			if (isset($args['cardEndDateMonth']) && is_numeric($args['cardEndDateMonth']) &&
					$args['cardEndDateMonth'] > 0 && $args['cardEndDateMonth'] < 13) {
				$this->cardEndDateMonth = $args['cardEndDateMonth'];
			} else
				$formErrors['cardEndDateYear'] = array('message' => 'cardEndDateError', 'displayLabel' => false);

			// Validate end year
			if (isset($args['cardEndDateYear']) && is_numeric($args['cardEndDateYear'])) {
				$this->cardEndDateYear = $args['cardEndDateYear'];
			} else
				$formErrors['cardEndDateYear'] = array('message' => 'cardEndDateError', 'displayLabel' => false);

			if (!isset($this->cardEndDateMonth) || $this->cardEndDateMonth < date("m") &&
					!isset($this->cardEndDateYear) || $this->cardEndDateYear < date("Y")) {
				$this->cardEndDateMonth = null;
				$this->cardEndDateYear = null;
				$formErrors['cardEndDateMonth'] = array('message' => 'cardEndDateError', 'displayLabel' => false);
			}

			// Store issue number
			if (isset($args['cardIssueNumber']) && !empty($args['cardIssueNumber'])) {
				$this->cardIssueNumber = $args['cardIssueNumber'];
			}

			// Validate security code
			if (isset($args['cardSecurityCode']) && !empty($args['cardSecurityCode']) &&
					strlen($args['cardSecurityCode']) >= 3 && strlen($args['cardSecurityCode']) <= 6) {
				$this->cardSecurityCode = $args['cardSecurityCode'];
			} else
				$formErrors['cardSecurityCode'] = array('message' => 'cardSecurityCodeError', 'displayLabel' => false);

			return $formErrors;
		}

		/**
		 * Populate the arguments that are passed to the form with the values stored
		 * in the PaymentType object.
		 * This is used so display the values of the form if the user has previously filled it out.
		 */
		public function populateArguments($args) {
			$args['cardType'] = $this->getCardType();
			$args['cardName'] = $this->getCardName();
			$args['cardNumber'] = $this->getCardNumber();
			$args['cardStartDateMonth'] = $this->getCardStartDateMonth();
			$args['cardStartDateYear'] = $this->getCardStartDateYear();
			$args['cardEndDateMonth'] = $this->getCardEndDateMonth();
			$args['cardEndDateYear'] = $this->getCardEndDateYear();
			$args['cardIssueNumber'] = $this->getCardIssueNumber();
			$args['cardSecurityCode'] = $this->getCardSecurityCode();
			return $args;
		}


		public function getPaymentDetails($html = true) {
			$cardType = self::$CARD_TYPES[$this->getCardType()];
			$cardTypeLabel = getString('paymentType', 'cardType');
			$cardNameLabel = getString('paymentType', 'cardName');
			$cardNumberLabel = getString('paymentType', 'cardNumber');
			$cardStartDateLabel = getString('paymentType', 'cardStartDate');
			$cardEndDateLabel = getString('paymentType', 'cardEndDate');
			$cardIssueNumberLabel = getString('paymentType', 'cardIssueNumber');

			if($html) {
				$out = '<span class="cardType"><span class="label">' . $cardTypeLabel . '</span> ' . $cardType . "</span>\n";
				$out .= '<span class="cardName"><span class="label">' . $cardNameLabel . '</span> ' . $this->getCardName() . "</span>\n";
				$out .= '<span class="cardNumber"><span class="label">' . $cardNumberLabel . '</span> ' . $this->getObfuscatedCardNumber() . "</span>\n";
				if ($this->getCardStartDateMonth() != "" && $this->getCardStartDateYear() != "") {
					$out .= '<span class="cardStartDate"><span class="label">' . $cardStartDateLabel . '</span> ' . $this->getCardStartDateMonth() . '/' . $this->getCardStartDateYear() . "</span>\n";
				}
				$out .= '<span class="cardEndDate"><span class="label">' . $cardEndDateLabel . '</span> ' . $this->getCardEndDateMonth(). '/' . $this->getCardEndDateYear() . "</span>\n";
				if ($this->getCardIssueNumber() != "") {
					$out .= '<span class="cardIssueNumber"><span class="label">' . $cardIssueNumberLabel . '</span> ' . $this->getCardIssueNumber() . "</span>\n";
				}
			} else {
				$out = array();
				$out['type'] = array('label'=>$cardTypeLabel, 'value'=>$cardType);
				$out['name'] = array('label'=>$cardNameLabel, 'value'=>$this->getCardName());
				$out['number'] = array('label'=>$cardNumberLabel, 'value'=>$this->getObfuscatedCardNumber());
				$out['endDate'] = array('label'=>$cardEndDateLabel, 'value'=>$this->getCardEndDateMonth() . '/' . $this->getCardEndDateYear());
				if ($this->getCardIssueNumber() != "") {
					$out['issueNumber'] = array('label'=>$cardIssueNumberLabel, 'value'=>$this->getCardIssueNumber());
				}
				if ($this->getCardStartDateMonth() != "" && $this->getCardStartDateYear() != "") {
					$out['startDate'] = array('label'=>$cardStartDateLabel, 'value'=>$this->getCardStartDateMonth() . '/' . $this->getCardStartDateYear());
				}
			}
			return $out;
		}

		/**
		 * Overriding PaymentType::uses3DSecure() so that we can check the card type
		 * before executing a 3DSecure card query.
		 * @see classes/store/PaymentType::uses3DSecure()
		 */
		public function uses3DSecure() {
			if ($this->cardType == 'Amex') {
				return false;
			} else {
				return $this->uses3DSecure;
			}
		}

		public function requires3DAuth() {
			$st3DCardQueryTransaction = null;
			foreach ($this->order->getTransactions() as $transaction) {
				if ($transaction->getType() == XPayRequest::$REQUEST_TYPE_ST3DCARDQUERY) {
					$st3DCardQueryTransaction = $transaction;
					break;
				}
			}

			if ($st3DCardQueryTransaction == null)
				return false;

			if ($st3DCardQueryTransaction->getAttributes()->getCount() == 0)
				$st3DCardQueryTransaction->loadAttributes();

			$result = $st3DCardQueryTransaction->getAttributes()->get(self::$PAYMENT_ATT_KEY_RESULT)->getValue();
			$enrolled = $st3DCardQueryTransaction->getAttributes()->get(self::$PAYMENT_ATT_KEY_3D_ENROLLED)->getValue();

			if ($result == 0 || $result == 2) {
				return false;
			} else if ($result == 1 && $enrolled == 'Y') {
				return true;
			}

			return false;
		}


		public function execute3DAuth() {
			ob_clean();

			echo $this->threeDSecureRedirectHtml;

			ob_flush();
			exit();
		}


		public function is3DPostBack() {
			if (isset($_REQUEST['PaRes']) && isset($_REQUEST['MD'])) {
				$this->paRes = $_REQUEST['PaRes'];
				$this->mD = $_REQUEST['MD'];
				return true;
			}
			return false;
		}


		public function processAuthorisation($args = array()) {

			$xpayRequest = new XPayRequest(XPayRequest::$REQUEST_TYPE_AUTH);
			$xpayRequest->loadFromArgs($args);
			$xpayRequest->setSettlementDay(1); // immediate settle
			$xpayRequest->setAmount($this->amount * 100);

			return $this->executeRequest($xpayRequest);
		}


		public function processReversal() {

			if (!($this->order instanceof Order))
				throw new PaymentTypeException("Order has not been set");
			if (!($this->transaction instanceof Transaction))
				throw new PaymentTypeException("Transaction has not been set");

			$transactionAttributes = $this->transaction->getAttributes();

			$xpayRequest = new XPayRequest(XPayRequest::$REQUEST_TYPE_AUTHREVERSAL);
			$xpayRequest->loadFromOrder($this->order);
			$xpayRequest->setTransactionReference($transactionAttributes->get(self::$PAYMENT_ATT_KEY_REFERENCE)->getValue());
			$xpayRequest->setParentTransactionReference($transactionAttributes->get(self::$PAYMENT_ATT_KEY_REFERENCE)->getValue());
			$xpayRequest->setTransactionVerifier($transactionAttributes->get(self::$PAYMENT_ATT_KEY_VERIFIER)->getValue());
			$xpayRequest->setAmount($this->transaction->getAmount() * 100);

			return $this->executeRequest($xpayRequest);
		}


		public function processSettlement($args = array()) {

			if (!($this->transaction instanceof Transaction ))
				throw new PaymentTypeException("Transaction has not been set");
			$transactionAttributes = $this->transaction->getAttributes();

			$xpayRequest = new XPayRequest(XPayRequest::$REQUEST_TYPE_SETTLEMENT);
			$xpayRequest->loadFromArgs($args);

			$xpayRequest->setTransactionReference($transactionAttributes->get(self::$PAYMENT_ATT_KEY_REFERENCE)->value);
			$xpayRequest->setParentTransactionReference($transactionAttributes->get(self::$PAYMENT_ATT_KEY_REFERENCE)->value);
			$xpayRequest->setTransactionVerifier($transactionAttributes->get(self::$PAYMENT_ATT_KEY_VERIFIER)->value);

			$xpayRequest->setAmount($this->transaction->getAmount() * 100);
			$xpayRequest->setSettleAmount($this->transaction->getAmount() * 100);

			return $this->executeRequest($xpayRequest);
		}


		public function processRefund() {

			if (!($this->order instanceof Order))
				throw new PaymentTypeException("Order has not been set");
			if (!($this->transaction instanceof Transaction ))
				throw new PaymentTypeException("Transaction has not been set");

			$transactionAttributes = $this->transaction->getAttributes();

			$xpayRequest = new XPayRequest(XPayRequest::$REQUEST_TYPE_REFUND);
			$xpayRequest->loadFromOrder($this->order);
			$xpayRequest->setTransactionReference($transactionAttributes->get(self::$PAYMENT_ATT_KEY_REFERENCE)->getValue());
			$xpayRequest->setParentTransactionReference($transactionAttributes->get(self::$PAYMENT_ATT_KEY_REFERENCE)->getValue());
			$xpayRequest->setTransactionVerifier($transactionAttributes->get(self::$PAYMENT_ATT_KEY_VERIFIER)->getValue());
			$xpayRequest->setAmount($this->amount * 100);

			return $this->executeRequest($xpayRequest);
		}


		public function process3DSecureCardQuery() {

			if (!($this->order instanceof Order))
				throw new PaymentTypeException("Order has not been set");

			$xpayRequest = new XPayRequest(XPayRequest::$REQUEST_TYPE_ST3DCARDQUERY);
			$xpayRequest->setAmount($this->amount * 100);

			if (isset($_SERVER['HTTP_USER_AGENT']))
				$xpayRequest->setUserAgent($_SERVER['HTTP_USER_AGENT']);
			if (isset($_SERVER['HTTP_ACCEPT']))
				$xpayRequest->setAccept($_SERVER['HTTP_ACCEPT']);
			if((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') && !DEBUG) {
				$xpayRequest->setTermUrl('https://' . STORE_URL . STORE_ROOT . 'checkout/secure/?page=framed');
			} else {
				$xpayRequest->setTermUrl('http://' . STORE_URL . STORE_ROOT . 'checkout/secure/?page=framed');
			}
			$xpayRequest->setMerchantName($GLOBALS['store']->getAttributes()->get('XPAYMERCHANTNAME')->getValue());

			$xpayRequest->loadFromOrder($this->order);

			return $this->executeRequest($xpayRequest);
		}


		public function process3DSecureAuthorisation()  {

			if (!($this->order instanceof Order))
				throw new PaymentTypeException("Order has not been set");

			/*
			foreach ($this->order->getTransactions() as $transaction) {
				if ($transaction->getType() == XPayRequest::$REQUEST_TYPE_ST3DAUTH &&
						$transaction->getAttributes()->get(self::$PAYMENT_ATT_KEY_RESULT) != null &&
						$transaction->getAttributes()->get(self::$PAYMENT_ATT_KEY_RESULT)->getValue() == XPayResponse::$RESULT_SUCCESSFUL) {
					// This order already has a valid ST3DAUTH. Don't do another.
					// This prevents extra auths happening if they double click the confirm button.
					return '';
				}
			}
			*/

			$xpayRequest = new XPayRequest(XPayRequest::$REQUEST_TYPE_ST3DAUTH);

			$st3DCardQueryTransaction = null;
			foreach ($this->order->getTransactions() as $transaction) {
				if ($transaction->getType() == XPayRequest::$REQUEST_TYPE_ST3DCARDQUERY) {
					$st3DCardQueryTransaction = $transaction;
					break;
				}
			}

			if ($st3DCardQueryTransaction == null)
				throw new PaymentTypeException('Unable to find ST3DCARDQUERY transaction');

			if ($st3DCardQueryTransaction->getAttributes()->getCount() == 0)
				$st3DCardQueryTransaction->loadAttributes();

			$xpayRequest->setEnrolled($st3DCardQueryTransaction->getAttributes()->get(self::$PAYMENT_ATT_KEY_3D_ENROLLED)->getValue());
			$xpayRequest->setParentTransactionReference($st3DCardQueryTransaction->getAttributes()->get(self::$PAYMENT_ATT_KEY_REFERENCE)->getValue());
			$xpayRequest->setPaRes($this->paRes);
			$xpayRequest->setMD($this->mD);

			$xpayRequest->loadFromOrder($this->order);
			$xpayRequest->setAmount($this->amount * 100);

			return $this->executeRequest($xpayRequest);
		}


		public function authorisationSuccessful() {
			return $this->authorisationSuccessful;
		}


		private function executeRequest(XPayRequest $xpayRequest) {
			// these should be changed to some sort of setting somewhere
			$xpayRequest->setCertificatePath('testroute17967xpaycerts.pem');
			$xpayRequest->setSiteReference('testroute17967');

			$xpayTransaction = new XPayTransaction();
			$xpayTransaction->setHostname('thameslink.office.groovytrain.com:5000');
			//$xpayTransaction->setHostname($this->getAttributes()->get('XPAYSITEHOST')->getValue());
			$xpayTransaction->setRequest($xpayRequest);
			$xpayResponse = $xpayTransaction->sendRequest();

			Logger::log("XPay: " . $xpayRequest->getType() . ' - ' . $xpayResponse->getResult() .
					(strlen($xpayResponse->getXPayMessage()) > 0 ? ' - ' . $xpayResponse->getXPayMessage() : ''), 'info', 2);

			$attributes = new Map();

			$attribute = new Attribute();
			$attribute->key = self::$PAYMENT_ATT_KEY_AUTHCODE;
			$attribute->value = $xpayResponse->getAuthCode();
			$attributes->add(self::$PAYMENT_ATT_KEY_AUTHCODE, $attribute);

			$attribute = new Attribute();
			$attribute->key = self::$PAYMENT_ATT_KEY_REFERENCE;
			$attribute->value = $xpayResponse->getTransactionReference();
			$attributes->add(self::$PAYMENT_ATT_KEY_REFERENCE, $attribute);

			$attribute = new Attribute();
			$attribute->key = self::$PAYMENT_ATT_KEY_RESULT;
			$attribute->value = $xpayResponse->getResult();
			$attributes->add(self::$PAYMENT_ATT_KEY_RESULT, $attribute);

			$attribute = new Attribute();
			$attribute->key = self::$PAYMENT_ATT_KEY_TIMESTAMP;
			$attribute->value =$xpayResponse->getTransactionCompletedTimestamp();
			$attributes->add(self::$PAYMENT_ATT_KEY_TIMESTAMP, $attribute);

			$attribute = new Attribute();
			$attribute->key = self::$PAYMENT_ATT_KEY_VERIFIER;
			$attribute->value = $xpayResponse->getTransactionVerifier();
			$attributes->add(self::$PAYMENT_ATT_KEY_VERIFIER, $attribute);

			if ($xpayResponse->getMessage("", true) != null) {
				$attribute = new Attribute();
				$attribute->key = self::$PAYMENT_ATT_KEY_MESSAGE;
				$attribute->value = $xpayResponse->getMessage("", true);
				$attributes->add(self::$PAYMENT_ATT_KEY_MESSAGE, $attribute);
			}

			if ($xpayRequest->getType() == XPayRequest::$REQUEST_TYPE_AUTH ||
					$xpayRequest->getType() == XPayRequest::$REQUEST_TYPE_ST3DAUTH) {
				$attribute = new Attribute();
				$attribute->key = self::$PAYMENT_ATT_KEY_CARD_TYPE;
				$attribute->value = $this->getCardType();
				$attributes->add(self::$PAYMENT_ATT_KEY_CARD_TYPE, $attribute);
			}

			if (($xpayRequest->getType() == XPayRequest::$REQUEST_TYPE_AUTH ||
					$xpayRequest->getType() == XPayRequest::$REQUEST_TYPE_ST3DAUTH) &&
					($xpayResponse->getSecurityResponsePostCode() == XPayResponse::$SR_DATA_NOT_MATCHED ||
					$xpayResponse->getSecurityResponseAddress() == XPayResponse::$SR_DATA_NOT_MATCHED ||
					$xpayResponse->getSecurityResponseSecurityCode() == XPayResponse::$SR_DATA_NOT_MATCHED)) {

				$failMessage = "";
				if ($xpayResponse->getSecurityResponsePostCode() == XPayResponse::$SR_DATA_NOT_MATCHED)
					$failMessage .= "POST CODE MATCH FAIL\n";
				if ($xpayResponse->getSecurityResponseAddress() == XPayResponse::$SR_DATA_NOT_MATCHED)
					$failMessage .= "ADDRESS MATCH FAIL\n";
				if ($xpayResponse->getSecurityResponseSecurityCode() == XPayResponse::$SR_DATA_NOT_MATCHED)
					$failMessage .= "SECURITY CODE MATCH FAIL";
				$failMessage = rtrim($failMessage);
				$attribute = new Attribute();
				$attribute->key = self::$PAYMENT_ATT_KEY_SECURITY_FAIL;
				$attribute->value = $failMessage;
				$attributes->add(self::$PAYMENT_ATT_KEY_MESSAGE, $attribute);
			}

			if ($xpayRequest->getType() == XPayRequest::$REQUEST_TYPE_ST3DCARDQUERY) {
				$this->threeDSecureRedirectHtml = $xpayResponse->getHtml();
				$this->threeDSecureAcsUrl = $xpayResponse->getAcsUrl();
				$this->threeDSecureMD = $xpayResponse->getMD();
				$this->threeDSecurePaReq = $xpayResponse->getPaReq();

				$attribute = new Attribute();
				$attribute->key = self::$PAYMENT_ATT_KEY_3D_ENROLLED;
				$attribute->value = $xpayResponse->getEnrolled();
				$attributes->add(self::$PAYMENT_ATT_KEY_3D_ENROLLED, $attribute);
			}

			// This is here to save all payments/transactions regardless of their outcome
			$this->saveTransaction(round($xpayRequest->getAmount() / 100, 2), $xpayRequest->getType(), $attributes);

			$error = "";
			if ($xpayResponse->getResult() != XPayResponse::$RESULT_SUCCESSFUL) {
				$this->authorisationSuccessful = false;
				throw new PaymentTypeException($xpayResponse->getMessage());
			} else {
				$this->authorisationSuccessful = true;
			}
			$errorCode = $xpayResponse->getResultCode();
			if ($errorCode == XPayResponse::$ERROR_ADDRESSPOSTCODE && ($xpayRequest->getType() == XPayRequest::$REQUEST_TYPE_AUTH ||
					$xpayRequest->getType() == XPayRequest::$REQUEST_TYPE_ST3DAUTH)) {
				throw new PaymentTypeException('Unable to validate address', PaymentTypeException::$FAILED_ADDRESS_VERIFICATION);
			} else if ($errorCode == XPayResponse::$ERROR_SECURITY_CODE && ($xpayRequest->getType() == XPayRequest::$REQUEST_TYPE_AUTH ||
					$xpayRequest->getType() == XPayRequest::$REQUEST_TYPE_ST3DAUTH)) {
				throw new PaymentTypeException('Unable to verify security code', PaymentTypeException::$FAILED_SECURITY_CODE_VERIFICATION);
			} else if ($errorCode != XPayResponse::$ERROR_SUCCESS) {
				$error = $xpayResponse->getMessage($errorCode);
			}

			return $error;
		}

	}

?>