<?php

	/*
	 * The following three classes XPayTransaction, XPayRequest and XPayResponse
	 * represent XPay requests and responses and the XPayTransaction class controls
	 * requests and responses.
	 *
	 * This code is based on Alan's code (alan.hitchin@groovytrain.com) with a few amends and changes,
	 * so all of the kudos goes to him.
	 */

	class XPayTransaction extends AbstractTransaction {

		protected $hostname;
		protected $type;
		protected $siteReference;
		protected $certificatePath;

		private $request;


		public function XPayTransaction() {
		}

		public function getSiteReference() {
			return $this->siteReference;
		}

		public function setSiteReference($siteReference) {
			$this->siteReference = $siteReference;
		}

		public function setCertificatePath($certificatePath) {
			$this->certificatePath = BASE_PATH . 'conf/' . $certificatePath;
		}

		public function setHostname($hostname) {
			$this->hostname = $hostname;
		}

		public function addCertificate($target, $parent) {

			if (file_exists($this->certificatePath)) {
				$certificate = file_get_contents($this->certificatePath);

				$elCertificate = $target->createElement('Certificate', $certificate);
				$parent->appendChild($elCertificate);
			}

		}

		public function renderXML($source = null, $target = null, $parent = null) {
			foreach ($source as $key => $value) {
				$key = ucfirst($key);

				$elTemp = $target->createElement($key, $value);
				$parent->appendChild($elTemp);
			}
		}

		public function setRequest(XPayRequest $request) {
			$this->request = $request;
		}

		public function sendRequest() {
			if (!($this->request instanceof XPayRequest))
				throw new Exception("The request has not been set.");
			$hostname = 'localhost';
			$port = 5000;
			if (isset($this->hostname)) {
				$xpayHostString = $this->hostname;
				$colonPos = strripos($xpayHostString, ':');
				if ($colonPos > -1) {
					$hostname = substr($xpayHostString, 0, $colonPos);
					$port = substr($xpayHostString, $colonPos + 1);
				} else {
					$hostname = $xpayHostString;
				}
			}

			$success = false;

			$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			if ($socket < 0) {
				throw new PaymentTypeException("Unable create socket.");
			} else {
				if (@socket_connect($socket, $hostname, $port) === false) {
					throw new PaymentTypeException("Unable to connect to XPay client at: " . $hostname . ":" . $port);
				} else {
					$xml = $this->request->renderXML();

					$result = socket_write($socket, $xml);

					// if (DEBUG)
					//	file_put_contents('C:/Temp/request_' . time(), $xml);

					$responseXML = "";
					while ($in = socket_read($socket, 2048)) {
						$responseXML .= $in;
					}

					// Remove everything before the first < in the response
					$responseXML = substr($responseXML, strpos($responseXML, "<"));

					// if (DEBUG)
					//	file_put_contents('C:/Temp/response_' . time(), $responseXML);

					if (strlen($responseXML) == 0)
						throw new PaymentTypeException("Recieved zero length response from XPay");

					$success = true;
				}
			}

			if ($success) {
				$response = new XPayResponse();
				$response->loadFromXML($responseXML);
				$response->setParentTransactionID($this->request->getTransactionID());
				return $response;
			}

			return null;
		}

	}

?>