<?php

	class XPayRequest extends XPayTransaction {

		public static $VERSION = "3.51";

		public static $REQUEST_TYPE_AUTH = "AUTH";
		public static $REQUEST_TYPE_AUTHREVERSAL = "AUTHREVERSAL";
		public static $REQUEST_TYPE_REFUND = "REFUND";
		public static $REQUEST_TYPE_REFUNDREVERSAL = "REFUNDREVERSAL";
		public static $REQUEST_TYPE_SETTLEMENT = "SETTLEMENT";

		public static $REQUEST_TYPE_ST3DCARDQUERY = "ST3DCARDQUERY";
		public static $REQUEST_TYPE_ST3DAUTH = "ST3DAUTH";

		public static $CARD_TYPE_VISA = 'VISA';
		public static $CARD_TYPE_MASTERCARD = 'MASTERCARD';
		public static $CARD_TYPE_SOLO = 'SOLO';
		public static $CARD_TYPE_MAESTRO = 'MAESTRO';
		public static $CARD_TYPE_DELTA = 'DELTA';
		public static $CARD_TYPE_AMEX = 'AMEX';
		public static $CARD_TYPE_ELECTRON = 'ELECTRON';

		private $amount;				// amount in pence
		private $currency;
		private $settlementDay = 0;
		private $transactionReference;
		private $settleDate = 'NEXT';
		private $settleStatus = 0;
		private $settleAmount;

		private $namePrefix = '';
		private $firstName;
		private $lastName;
		private $nameSuffix = '';
		private $company;
		private $street;
		private $city;
		private $stateProv;
		private $postalCode;
		private $countryCode;
		private $phone;
		private $email;

		private $cardType;
		private $number;
		private $issue = '';
		private $startDate;
		private $expiryDate;
		private $securityCode = '';

		private $transactionVerifier;
		private $parentTransactionReference;

		private $orderReference = '';
		private $orderInformation = '';

		// 3D Secure
		private $accept;
		private $userAgent;
		private $termUrl;
		private $merchantName;
		private $enrolled;
		private $paRes;
		private $mD;

		public function XPayRequest($type) {
			parent::XPayTransaction();

			switch ($type) {
				case self::$REQUEST_TYPE_AUTH:
				case self::$REQUEST_TYPE_AUTHREVERSAL:
				case self::$REQUEST_TYPE_REFUND:
				case self::$REQUEST_TYPE_REFUNDREVERSAL:
				case self::$REQUEST_TYPE_SETTLEMENT:
				case self::$REQUEST_TYPE_ST3DCARDQUERY:
				case self::$REQUEST_TYPE_ST3DAUTH:
					$this->type = $type;
					break;
				default:
					throw new PaymentTypeException('Unknown XPayRequest type.');
			}

		}

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

		public function loadFromTransaction($transaction, $response, $setTransactionReference) {

			foreach ($this as $key => $value) {
				if (isset($transaction->$key)) {
					$this->$key = $transaction->$key = $value;
				}
			}

			$this->transactionVerifier = $response->getTransactionVerifier();
			$this->parentTransactionReference = $response->getTransactionReference();

			if ($setTransactionReference)
				$this->transactionReference = $response->getTransactionReference();

		}

		public function loadFromArgs($args) {

			$this->namePrefix = $args['title'];
			$this->firstName = $args['firstname'];
			$this->lastName = $args['lastname'];
			$this->street = $args['address1'];
			$this->city = $args['city'];
			$this->stateProv = $args['region'];
			$this->postalCode = $args['postcode'];
			$this->email = $_SESSION['user']->email;
			$this->currency = 'GBP';
			$this->orderReference = $_SESSION['user']->getID();

			$this->cardType = $args['cardType'];
			$this->number = $args['cardNumber'];
			$this->issue = $args['cardIssue'];
			$this->startDate = $args['cardStartDateMonth'] . '/' . $args['cardStartDateYear'];
			$this->expiryDate = $args['cardEndDateMonth'] . '/' . $args['cardEndDateYear'];
			$this->securityCode = $args['cardSecurityCode'];
			if(isset($args['telmobile'])) {
				$this->phone = $args['telmobile'];
			} else if(isset($args['telhome'])) {
				$this->phone = $args['telhome'];
			} else {
				$this->phone = $args['telwork'];
			}

		}

		/**
		 * Populate the transaction with data from an order
		 */
		public function loadFromOrder($order) {

			$customer = $order->getCustomer();
			$billingAddress = $order->getBillingAddress();

			$this->namePrefix = $billingAddress->getTitle();
			$this->firstName = $billingAddress->getFirstName();
			$this->lastName = $billingAddress->getLastName();
			$this->company = $billingAddress->getOrganisation();
			$this->street = $billingAddress->getAddress1();
			$this->city = $billingAddress->getTown();
			$this->stateProv = $billingAddress->getRegion();
			$this->postalCode = $billingAddress->getPostalCode();
			$this->countryCode = $billingAddress->getCountry();
			$this->email = $customer->getEmailAddress();

			$this->currency = $order->getCurrency();
			$this->orderReference = $order->getOrderID();

			if (!($order->getPaymentType() instanceof SecureTrading))
				throw new PaymentTypeException('Incorrect PaymentType passed to XPayRequest->loadFromOrder(). It must be of type SecureTrading.');
			$paymentType = $order->getPaymentType();

			$this->cardType = $paymentType->getCardType();
			$this->number = $paymentType->getCardNumber();
			$this->issue = $paymentType->getCardIssueNumber();
			if ($paymentType->getCardStartDateMonth() && $paymentType->getCardStartDateYear())
				$this->startDate = $paymentType->getCardStartDateMonth() . '/' . substr($paymentType->getCardStartDateYear(), -2);
			$this->expiryDate = $paymentType->getCardEndDateMonth() . '/' . substr($paymentType->getCardEndDateYear(), -2);
			$this->securityCode = $paymentType->getCardSecurityCode();

			$this->amount = $order->getTotal()->toPence();

			if ($billingAddress->getTelephoneWork())
				$this->phone = $billingAddress->getTelephoneWork();
			else
				$this->phone = $billingAddress->getTelephoneHome();

		}

		public function renderXML($source = null, $target = null, $parent = null) {

			// We don't use DOMDocument directly anymore, we now use XDOMDocument instead
			// because it automatically escapes html entities correctly unlike DOMDocument
			// $xml = new DOMDocument('1.0');
			$xml = new XDOMDocument('1.0');

			$elRequestBlock = $xml->createElement('RequestBlock');
			$elRequestBlock->setAttribute('Version', self::$VERSION);
			$xml->appendChild($elRequestBlock);

			$elRequest = $xml->createElement('Request');
			$elRequest->setAttribute('Type', $this->type);
			$elRequestBlock->appendChild($elRequest);

			$elOperation = $xml->createElement('Operation');
			$elRequest->appendChild($elOperation);

			$fields = array('siteReference' => $this->siteReference);

			switch ($this->type) {
				case 'AUTH':
				case 'ST3DAUTH';
					$fields['amount'] = $this->amount;
					$fields['currency'] = $this->currency;
					$fields['settlementDay'] = $this->settlementDay;
					break;
				case 'REFUND':
					$fields['amount'] = $this->amount;
					break;
				case 'SETTLEMENT':
					$fields['transactionReference'] = $this->transactionReference;
					$fields['settleDate'] = $this->settleDate;
					$fields['settleStatus'] = $this->settleStatus;
					$fields['settleAmount'] = $this->settleAmount;
					break;
				case 'AUTHREVERSAL':
					$fields['transactionReference'] = $this->transactionReference;
					break;
				case 'ST3DCARDQUERY':
					$fields['termUrl'] = $this->termUrl;
					$fields['merchantName'] = $this->merchantName;
					$fields['amount'] = $this->amount;
					$fields['currency'] = $this->currency;
					break;
			}

			parent::renderXML($fields, $xml, $elOperation);

			if ($this->type != 'SETTLEMENT') {
				$elCustomerInfo = $xml->createElement('CustomerInfo');
				$elRequest->appendChild($elCustomerInfo);

				// 3D Secure card query uses different CustomerInfo children
				if ($this->type == 'ST3DCARDQUERY') {
					$fields = array(
						'accept' => $this->accept,
						'userAgent' => $this->userAgent
					);

					parent::renderXML($fields, $xml, $elCustomerInfo);
				} else {
					// Going postal....
					$elPostal = $xml->createElement('Postal');
					$elCustomerInfo->appendChild($elPostal);

					$fields = array(
						'street' => $this->street,
						'city' => $this->city,
						'stateProv' => $this->stateProv,
						'postalCode' => $this->postalCode,
						'countryCode' => $this->countryCode
					);
					parent::renderXML($fields, $xml, $elPostal);

					$elName = $xml->createElement('Name');
					$elPostal->appendChild($elName);

					$fields = array(
						'namePrefix' => $this->namePrefix,
						'firstName' => $this->firstName,
						'lastName' => $this->lastName,
						'nameSuffix' => $this->nameSuffix
					);
					parent::renderXML($fields, $xml, $elName);

					$elTelecom = $xml->createElement('Telecom');
					$elCustomerInfo->appendChild($elTelecom);

					$elPhone = $xml->createElement('Phone', $this->phone);
					$elTelecom->appendChild($elPhone);

					$elOnline = $xml->createElement('Online');
					$elCustomerInfo->appendChild($elOnline);

					$elEmail = $xml->createElement('Email', $this->email);
					$elOnline->appendChild($elEmail);
				}

				$elPaymentMethod = $xml->createElement('PaymentMethod');
				$elRequest->appendChild($elPaymentMethod);

				$elCreditCard = $xml->createElement('CreditCard');
				$elPaymentMethod->appendChild($elCreditCard);

				switch ($this->type) {
					case 'AUTH':
						$fields = array(
							'type' => $this->cardType,
							'number' => $this->number,
							'issue' => $this->issue,
							'startDate' => $this->startDate,
							'expiryDate' => $this->expiryDate,
							'securityCode' => $this->securityCode
						);
						break;
					case 'ST3DAUTH':
						$fields = array(
							'type' => $this->cardType,
							'number' => $this->number,
							'issue' => $this->issue,
							'startDate' => $this->startDate,
							'expiryDate' => $this->expiryDate,
							'securityCode' => $this->securityCode,
							'parentTransactionReference' => $this->parentTransactionReference
						);
						break;
					case 'ST3DCARDQUERY':
						$fields = array(
							'type' => $this->cardType,
							'number' => $this->number,
							'issue' => $this->issue,
							'startDate' => $this->startDate,
							'expiryDate' => $this->expiryDate,
						);
						break;
					case 'AUTHREVERSAL':
					case 'REFUND':
						$fields = array(
							'transactionVerifier' => $this->transactionVerifier,
							'transactionReference' => $this->transactionReference,
							'parentTransactionReference' => $this->parentTransactionReference
						);
						break;
				}

				parent::renderXML($fields, $xml, $elCreditCard);

				if ($this->type == 'ST3DAUTH') {
					$elST3DAuth = $xml->createElement('ThreeDSecure');
					$elPaymentMethod->appendChild($elST3DAuth);

					$fields = array(
						'enrolled' => $this->enrolled,
						'paRes' => $this->paRes,
						'mD' => $this->mD
					);

					parent::renderXML($fields, $xml, $elST3DAuth);
				}

				if ($this->type != 'REFUND') {
					$elOrder = $xml->createElement('Order');
					$elRequest->appendChild($elOrder);

					$fields = array(
						'orderReference' => $this->orderReference,
						'orderInformation' => $this->orderInformation
					);
					parent::renderXML($fields, $xml, $elOrder);
				}
			}

			$this->addCertificate($xml, $elRequestBlock);

			$xml->preserveWhiteSpace = false;
			$xml->formatOutput = true;
			return $xml->saveXML();

		}

	}

?>