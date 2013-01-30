<?php
	class AbstractTransaction {

		public static $REQUEST_TYPE_AUTH = "AUTH";
		public static $REQUEST_TYPE_3D_AUTH = "ST3DAUTH";
		public static $REQUEST_TYPE_AUTHREVERSAL = "AUTHREVERSAL";
		public static $REQUEST_TYPE_REFUND = "REFUND";
		public static $REQUEST_TYPE_REFUNDREVERSAL = "REFUNDREVERSAL";
		public static $REQUEST_TYPE_SETTLEMENT = "SETTLEMENT";

	}


	class Transaction extends AbstractTransaction  {

		/** @var string $transactionID The ID for the transaction */
		private $transactionID;
		private $orderID;
		private $paymentTypeID;
		/** @var Money $amount The amount that the transaction is for */
		private $amount;
		/** @var string $type The type of transaction */
		private $type;
		/** @var Attribute[] The attributes of the transaction */
		private $attributes;
		/** @var boolean $requiresReversal If true the transaction is awaiting reversal */
		private $requiresReversal = false;
		/** @var string $transactionDate The date the the transaction was created */
		private $transactionDate;

		public function Payment() {
			$this->attributes = new Map();
		}

		public function getTransactionID() {
			return $this->transactionID;
		}

		public function setTransactionID($transactionID) {
			$this->transactionID = $transactionID;
		}

		public function getOrderID() {
			return $this->orderID;
		}

		public function setOrderID($orderID) {
			$this->orderID = $orderID;
		}

		public function getPaymentTypeID() {
			return $this->paymentTypeID;
		}

		public function setPaymentTypeID($paymentTypeID) {
			$this->paymentTypeID = $paymentTypeID;
		}

		public function getAmount() {
			return $this->amount;
		}

		public function setAmount($amount) {
			$this->amount = $amount;
		}

		public function getType() {
			return $this->type;
		}

		public function setType($type) {
			$this->type = $type;
		}

		public function requiresReversal() {
			return $this->requiresReversal;
		}

		public function setRequiresReversal($requiresReversal) {
			$this->requiresReversal = $requiresReversal;
		}

		public function getTransactionDate() {
			return $this->transactionDate;
		}

		public function setTransactionDate($transactionDate) {
			$this->transactionDate = $transactionDate;
		}

		public function getAttributes() {
			return $this->attributes;
		}

		public function setAttributes($attributes) {
			$this->attributes = $attributes;
		}

		/**
		 * This save function does not save changes to anything other the requiresReversalProperty (inc. attributes)
		 * as nothing else should be changed once a transaction has been executed.
		 */
		public function save() {
			$dbConn = DatabaseConnection::getConnection();
			$sql = "UPDATE tblTransaction SET requiresReversal = :requiresReversal WHERE transactionID = :transactionID;";
			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('requiresReversal', $this->requiresReversal);
			$stmt->bindParameter('transactionID', $this->transactionID);
			$stmt->execute();
		}

		public static function loadTransaction($values, $loadAttributes = true) {
			$transaction = new Transaction();
			if (isset($values['transactionID']) && !empty($values['transactionID']))
				$transaction->transactionID = $values['transactionID'];
			if (isset($values['orderID']) && !empty($values['orderID']))
				$transaction->orderID = $values['orderID'];
			if (isset($values['paymentTypeID']) && !empty($values['paymentTypeID']))
				$transaction->paymentTypeID = $values['paymentTypeID'];
			if (isset($values['amount']) && !empty($values['amount']))
				$transaction->amount = $values['amount'];
			if (isset($values['type']) && !empty($values['type']))
				$transaction->type = $values['type'];
			if (isset($values['requiresReversal']) && !empty($values['requiresReversal']))
				$transaction->requiresReversal = $values['requiresReversal'];
			if (isset($values['transactionDate']) && !empty($values['transactionDate']))
				$transaction->transactionDate = $values['transactionDate'];
			if ($loadAttributes && $transaction->getTransactionID() != null)
				$transaction->loadAttributes();
			return $transaction;
		}

		public static function getTransactionsRequiringReversal() {
			$store = $GLOBALS['store'];
			$dbConn = DatabaseConnection::getConnection();
			$sql = "SELECT t.* FROM tblTransaction AS t
				INNER JOIN tblOrder AS o
					ON o.orderID = t.orderID
				WHERE o.storeID = :storeID
				AND t.requiresReversal = true;";
			$stmt = $dbConn->prepareStatement($sql);
			$stmt->bindParameter('storeID', $store->getStoreID());
			$result = $stmt->execute();

			$transactions = new Map();
			while ($result->hasRows()) {
				$row = $result->nextRow();
				$transaction = Transaction::loadTransaction($row, true);
				$transactions->add($transaction->getTransactionID(), $transaction);
			}
			return $transactions;
		}

		public function loadAttributes($attributeData = null) {
			if (!isset($attributeData)) {
				$dbConn = DatabaseConnection::getConnection();
				$attributeData = $dbConn->executeStoredProcedure('spsTransactionAttributeByTransactionID', array($this->transactionID));
			}
			$this->attributes = new Map();
			if ($attributeData instanceof Resultset) {
				while ($attributeData->hasRows()) {
					$data = $attributeData->nextRow();
					$attribute = Attribute::loadAttribute($data);
					$this->attributes->add($attribute->getKey(), $attribute);
				}
			} else {
				foreach ($attributeData as $data) {
					$attribute = Attribute::loadAttribute($data);
					$this->attributes->add($attribute->getKey(), $attribute);
				}
			}
		}

		public function getPaymentType() {
			$dbConn = DatabaseConnection::getConnection();
			$result = $dbConn->executeStoredProcedure('spsPaymentTypeByPaymentTypeID', array($this->paymentTypeID));
			if ($result == false || $result->getRowCount() == 0)
				throw new TransactionException("Unable to load payment type");
			$row = $result->nextRow();
			$paymentType = new $row['className']();
			$paymentType->setPaymentTypeID($row['paymentTypeID']);
			$paymentType->setDisplayName($row['displayName']);
			$paymentType->setDescription($row['description']);
			$paymentType->setClassName($row['className']);
			return $paymentType;
		}


	}


	class TransactionException extends Exception {

		public function TransactionException($message) {
			parent::__construct($message);
		}

	}
?>