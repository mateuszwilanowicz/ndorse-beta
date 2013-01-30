<?php

	/**
	 * An abstract class that all payment methods on the site must extend
	 *
	 * @author Matt Rink <matt.rink@groovytrain.com>
	 */
	abstract class PaymentType {

		/** @var int $paymentTypeID The ID of the payment type */
		protected $paymentTypeID;
		/** @var string $displayName The name of the payment type */
		protected $displayName;
		/** @var string $description The description of the payment type */
		protected $description;
		/** @var string $className The class name for the payment type */
		protected $className;
		protected $order;
		protected $transaction;
		protected $amount;
		protected $attributes;
		protected $log;

		protected $uses3DSecure = false;

		public function PaymentType() {
			$this->attributes = new Map();
		}


		/**
		 * Returns the paymentTypeID for this payment type
		 * @return integer The paymentTypeID for this payment type
		 */
		public function getPaymentTypeID() {
			return $this->paymentTypeID;
		}


		/**
		 * Sets the paymentTypeID for this payment type
		 * @param integer $paymentTypeID The paymentTypeID for this payment type
		 */
		public function setPaymentTypeID($paymentTypeID) {
			$this->paymentTypeID = $paymentTypeID;
		}


		/**
		 * Returns the displayName for this payment type
		 * @return string The display name for this payment type
		 */
		public function getDisplayName() {
			return $this->displayName;
		}


		/**
		 * Sets the displayName for this payment type
		 * @param string $displayName The display name for this payment type
		 */
		public function setDisplayName($displayName) {
			$this->displayName = $displayName;
		}


		/**
		 * Returns the description for this payment type
		 * @return string The description for this payment type
		 */
		public function getDescription() {
			return $this->description;
		}


		/**
		 * Sets the description for this payment type
		 * @param string $description The description for this payment type
		 */
		public function setDescription($description) {
			$this->description = $description;
		}


		/**
		 * Returns the class name for this payment type
		 * @return string The class name for this payment type
		 */
		public function getClassName() {
			return $this->className;
		}


		/**
		 * Sets the class name for this payment type
		 * @param string $className The class name for this payment type
		 */
		public function setClassName($className) {
			$this->className = $className;
		}


		/**
		 * Sets the order for this payment type
		 * @param Order $order The order for this payment type
		 */
		public function setOrder(Order $order) {
			$this->order = $order;
		}


		/**
		 * Sets the order for this payment type
		 * @param Order $order The order for this payment type
		 */
		public function setAmount($amount) {
			$this->amount = $amount;
		}


		/**
		 * Sets the order for this payment type
		 * @param Order $order The order for this payment type
		 */
		public function getTransaction() {
			return $this->transaction;
		}


		/**
		 * Sets the order for this payment type
		 * @param Order $order The order for this payment type
		 */
		public function setTransaction(Transaction $transaction) {
			$this->transaction = $transaction;
		}


		/**
		 * Returns whether or not this payment type uses 3DSecure or similar payer authorisation.
		 * @return boolean True if the PaymentType uses payer authentication, false if not
		 */
		protected function uses3DSecure() {
			return $this->uses3DSecure;
		}


		/**
		 * Loads this payment type with the values passed in
		 * @param array $values The values to populate this payment type with
		 */
		public static function loadPaymentType($values) {
			$paymentType = new $values['className'];
			if (isset($values['paymentTypeID']) & !empty($values['paymentTypeID']))
				$paymentType->setPaymentTypeID($values['paymentTypeID']);
			if (isset($values['displayName']) & !empty($values['displayName']))
				$paymentType->setDisplayName($values['displayName']);
			if (isset($values['description']) & !empty($values['description']))
				$paymentType->setDescription($values['description']);
			if (isset($values['className']) & !empty($values['className']))
				$paymentType->setClassName($values['className']);
			return $paymentType;
		}


		/**
		 * Return the payment form for this payment type
		 * @param array $args The arguments for the current page method, used to populate the form values
		 * @return FormControl The FormControl for this payment type's payment form
		 */
		public abstract function getPaymentForm($args = array());


		/**
		 * Returns the payment type values as an array
		 * @return mixed[] The payment type values
		 */
		public abstract function toArray();


		/**
		 * Validates the payment form values for this payment type
		 * @param array $args The arguments for the current page method, used to validate the form values
		 * @return array $fieldErrors The validation errors for this payment type's current form values
		 */
		public abstract function validatePaymentForm($args);


		/**
		 * Populate page arguments from this payment type's current values
		 * @param array $args The arguments to be populated
		 */
		public abstract function populateArguments($args);


		/**
		 * Displays the formatted payment details that the user has previously entered
		 * @return string A string containing the formatted payment details
		 */
		public abstract function getPaymentDetails();

		/**
		 * The method to process payment authorisation for this payment type
		 * @return string A string containing an error message from the processor, the string is empty if no error occurred
		 * @throws PaymentTypeException Exception containing an error if one occured.
		 */
		public abstract function processAuthorisation();


		/**
		 * The method to process payment authorisation reversal for this payment type
		 * @return string A string containing an error message from the processor, the string is empty if no error occurred
		 * @throws PaymentTypeException Exception containing an error if one occured.
		 */
		public abstract function processReversal();


		/**
		 * The method to process payment settlement for this payment type
		 * @return string A string containing an error message from the processor, the string is empty if no error occurred
		 * @throws PaymentTypeException Exception containing an error if one occured.
		 */
		public abstract function processSettlement();


		/**
		 * The method to process payment refund for this payment type
		 * @return string A string containing an error message from the processor, the string is empty if no error occurred
		 * @throws PaymentTypeException Exception containing an error if one occured.
		 */
		public abstract function processRefund();


		/**
		 * The method to check if payer authorisation is required for this payment type
		 * @return string A string containing an error message from the processor, the string is empty if no error occurred
		 * @throws PaymentTypeException Exception containing an error if one occured.
		 */
		public abstract function process3DSecureCardQuery();


		/**
		 * The method to process payer authorised payment authorisation for this payment type
		 * @return string A string containing an error message from the processor, the string is empty if no error occurred
		 * @throws PaymentTypeException Exception containing an error if one occured.
		 */
		public abstract function process3DSecureAuthorisation();


		/**
		 * The method to check is payer authorisation has taken place and whether or not a payer authorisation transaction exists
		 * @return boolean Returns true if a payer authorisation transaction exists and requires a payer authorised payment authorisation, false if a payer authorisation transaction does not exist or if is doesn't require payer authorised payment authorisation
		 */
		public abstract function requires3DAuth();


		/**
		 * If process3DSecureCardQuery() indicates that payer authentication is required, this method carries out the
		 * payer authorisation, ie. A redirect a 3DSecure page.
		 */
		public abstract function execute3DAuth();


		/**
		 * Checks whether or not the page currently being processed has returned from a payer authentication.
		 * @return boolean Returns true is we are returning from a third party payment authorisation site, false if not.
		 */
		public abstract function is3DPostBack();


		/**
		 * Checks whether or not a successful authorisation or payer authorised payment authorisation has occurred, false if not
		 * @return boolean Returns true if a successful authorisation or payer authorised payment authorisation has occurred, false if not
		 */
		public abstract function authorisationSuccessful();


		/**
		 * Retrive payment type from the database by paymentTypeID
		 * @param integer $paymentTypeID The paymentTypeID of the payment type to be retrieved
		 * @return PaymentType The payment type for the paymentTypeID
		 * @throws PaymentTypeException The exception thrown if the paymentType cannot be found
		 */
		public static function getPaymentTypeByPaymentTypeID($paymentTypeID) {
			$dbConn = DatabaseConnection::getConnection();
			$result = $dbConn->executeStoredProcedure('spsPaymentTypeByPaymentTypeID', array($paymentTypeID));
			if ($result->getRowCount() != 1)
				throw new PaymentTypeException("Unable to load payment type");
			$row = $result->nextRow();
			$paymentType = PaymentType::loadPaymentType($row);
			$paymentType->loadAttributes();
			return $paymentType;
		}


		/**
		 * Retrieves the attributes for this PaymentType
		 * @return Map Returns a Map containing the attributes
		 */
		public function getAttributes() {
			if ($this->attributes->getCount() == 0)
				$this->loadAttributes();
			return $this->attributes;
		}


		/**
		 * Loads the attributes for this PaymentType
		 */
		public function loadAttributes() {
			$dbConn = DatabaseConnection::getConnection();

			$sql = 'SELECT *
				    FROM core_attribute a
				    LEFT JOIN core_paymenttype_attribute pta
				        ON a.key = pta.attributeKey
				    WHERE a.type = "paymenttype"
				    AND pta.paymentTypeID = :paymentTypeID
				    ORDER BY a.displayOrder';
			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('paymentTypeID', $this->getPaymentTypeID());
			$result = $stmt->execute();

			while ($result->hasRows()) {
				$row = $result->nextRow();
				$attribute = Attribute::loadAttribute($row);
				$this->attributes->add($attribute->getKey(), $attribute);
			}
		}


		/**
		 * Saves a transaction against the current order using the amount, type and attributes passed in
		 * @param integer $amount The amount for the transaction
		 * @param string $type The type of the transaction
		 * @param Map $attributes A Map containing the attributes for this transaction
		 * @return unknown_type
		 */
		protected function saveTransaction($amount, $type, $attributes) {
			$transaction = new Transaction();
			$transaction->setAmount($amount);
			$transaction->setType($type);
			$transaction->setPaymentTypeID($this->paymentTypeID);
			$transaction->setAttributes($attributes);
			$transaction->setTransactionDate(date(DB_DATETIME));

			$orderID = $_SESSION['user']->getID() . '-' . date('YmdHis');

			$dbConn = DatabaseConnection::getConnection();

			$sql = "INSERT INTO core_transaction (orderID, paymentTypeID, amount, `type`, transactionDate)
				VALUES (:orderID, :paymentTypeID, :amount, :type, :transactionDate);";
			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('orderID', $orderID);
			$stmt->bindParameter('paymentTypeID', $this->getPaymentTypeID());
			$stmt->bindParameter('amount', $amount);
			$stmt->bindParameter('type', $type);
			$stmt->bindParameter('transactionDate', $transaction->getTransactionDate());
			$stmt->execute();

			$stmt = $dbConn->prepareStatement("SELECT LAST_INSERT_ID() AS transactionID;");
			$result = $stmt->execute();

			if (!is_object($result))
				throw new PaymentTypeException("Unable to write transaction");
			$row = $result->nextRow();
			$transactionID = $row['transactionID'];

			$transaction->setTransactionID($transactionID);
			$transaction->setOrderID($orderID);

			$sql = 'INSERT INTO core_transaction_attribute (transactionID, attributeKey, `value`) VALUES ';
			$toAdd = array();
			$params = array('transID'=>$transactionID);

			$i = 0;
			foreach ($attributes as $key => $value) {
				$toAdd[$i] = "(:transID, :key$i, :val$i)";
				$params["key$i"] = $key;
				$params["val$i"] = $value;
				++$i;
			}

			$sql .= implode(', ', $toAdd);
			$stmt = $dbConn->prepareStatement($sql);
			foreach($params as $key=>$val) {
				$stmt->bindParameter($key, $val);
			}
			$stmt->execute();
			$this->transaction = $transaction;

		}

	}


	/**
	 * General purpose exception class for the payment types
	 */
	class PaymentTypeException extends Exception {

		public static $FAILED_ADDRESS_VERIFICATION = 1001;
		public static $FAILED_SECURITY_CODE_VERIFICATION = 1002;

		public function PaymentTypeException($message, $code = 0) {
			parent::__construct($message, $code);
		}

	}

?>