<?php
	class PaymentFormControl {

		public static function render($args) {

			$titles = Ndoorse_Member::getTitles();
			FormControl::selectOption($titles, isset($args['title']) ? $args['title'] : $_SESSION['user']->title);

			$countries = Country::getCountries();
			FormControl::selectOption($countries, isset($args['country']) ? $args['country'] : $_SESSION['user']->country);

			$cardTypes = array();
			foreach(SecureTrading::$CARD_TYPES as $key=>$val) {
				$cardTypes[] = array('value'=>$key, 'label'=>$val);
			}
			FormControl::selectOption($cardTypes, isset($args['cardtype']) ? $args['cardtype'] : '');

			$months = array();
			for($i=1;$i<13;++$i) {
				$months[] = array('value'=>$i, 'label'=>str_pad($i, 2, '0', STR_PAD_LEFT));
			}
			$startMonths = $expiryMonths = $months;
			FormControl::selectOption($startMonths, isset($args['cardStartMonth']) ? $args['cardStartMonth'] : date('m'));
			FormControl::selectOption($expiryMonths, isset($args['cardEndMonth']) ? $args['cardEndMonth'] : date('m'));

			$startYears = array();
			$thisYear = date('Y');
			for($i=$thisYear; $i>$thisYear-10; --$i) {
				$startYears[] = array('value'=>$i, 'label'=>$i);
			}
			FormControl::selectOption($startYears, isset($args['cardStartYear']) ? $args['cardStartYear'] : date('Y'));

			$expiryYears = array();
			for($i=$thisYear; $i<$thisYear+15; ++$i) {
				$expiryYears[] = array('value'=>$i, 'label'=>$i);
			}
			FormControl::selectOption($expiryYears, isset($args['cardEndYear']) ? $args['cardEndYear'] : date('Y'));

			$paymentForm = new FormControl(BASE_URL . 'members/confirmupgrade/');
			if(isset($args['upgradeLevel'])) {
				$paymentForm->hidden('upgradeLevel', $args['upgradeLevel']);
				$level = new Ndoorse_Level($args['upgradeLevel']);
			} else if(isset($args[1])) {
				$paymentForm->hidden('upgradeLevel', $args[1]);
				$level = new Ndoorse_Level($args[1]);
			} else {
				pr($_SESSION);
			}

			$levelOptions = array(
					'month'=>array('label'=>'1 Month (£' . $level->priceMonth . ')'),
					'year'=>array('label'=>'1 Year (£' . $level->priceYear . ')'));
			if(isset($args['type']) && $args['type'] == 'year') {
				$levelOptions['year']['checked'] = true;
			} else {
				$levelOptions['month']['checked'] = true;
			}

			$paymentForm->radio('type', 'Upgrade Type:', $levelOptions);
			$paymentForm->textbox('promo', 'Promotional code:', isset($args['promo']) ? $args['promo'] : '');

			$paymentForm->select('title', 'Title:', $titles);
			$paymentForm->textbox('firstname', 'First name:', $_SESSION['user']->firstname);
			$paymentForm->textbox('lastname', 'Last name:', $_SESSION['user']->lastname);

			$paymentForm->select('country', 'Country:', $countries);
			$paymentForm->textbox('address1', 'Address line 1:', isset($args['address1']) ? $args['address1'] : $_SESSION['user']->address1);
			$paymentForm->textbox('address2', 'Address line 2:', isset($args['address2']) ? $args['address2'] : $_SESSION['user']->address2);
			$paymentForm->textbox('city', 'Town/city:', isset($args['city']) ? $args['city'] : '');
			$paymentForm->textbox('region', 'Region:', isset($args['region']) ? $args['region'] : $_SESSION['user']->region);
			$paymentForm->textbox('postcode', 'Postcode:', isset($args['postcode']) ? $args['postcode'] : $_SESSION['user']->postcode);
			$paymentForm->textbox('telhome', 'Home Telephone:', isset($args['telhome']) ? $args['telhome'] : $_SESSION['user']->telhome);
			$paymentForm->textbox('telwork', 'Work Telephone', isset($args['telwork']) ? $args['telwork'] : $_SESSION['user']->telwork);
			$paymentForm->textbox('telmobile', 'Mobile Telephone', isset($args['telmobile']) ? $args['telmobile'] : $_SESSION['user']->telmobile);

			$paymentForm->select('cardType', 'Card Type:', $cardTypes);
			$paymentForm->textbox('cardName', 'Cardholder Name:', isset($args['cardName']) ? $args['cardName'] : $_SESSION['user']->getName());
			$paymentForm->textbox('cardNumber', 'Card Number:', isset($args['cardNumber']) ? $args['cardNumber'] : '');
			$paymentForm->select('cardStartDateMonth', 'Start Date:', $startMonths);
			$paymentForm->select('cardStartDateYear', '', $startYears);
			$paymentForm->select('cardEndDateMonth', 'Expiry Date:', $expiryMonths);
			$paymentForm->select('cardEndDateYear', '', $expiryYears);
			$paymentForm->textbox('cardIssue', 'Issue Number:', isset($args['issue']) ? $args['cardIssue'] : '');
			$paymentForm->textbox('cardSecurityCode', 'CVC:', isset($args['cardSecurityCode']) ? $args['cardSecurityCode'] : '');

			$paymentForm->submit('continue', 'Continue');
			$paymentForm->html('<a href="' . BASE_URL . 'members/upgrade/" class="button"><span>Cancel</span></a>');

			return $paymentForm;
		}
	}