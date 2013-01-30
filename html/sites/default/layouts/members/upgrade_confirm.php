<h2>Confirm Upgrade</h2>
<div class="top-box box">
	<p>
		You have chosen to upgrade to <strong><?= $level->title ?></strong>, for
		1 <?= $args['type'] == 'month' ? 'month' : 'year' ?> at
		&pound;<?= $args['type'] == 'month' ? $level->priceMonth : $level->priceYear ?>
	</p>
</div>

<div class="col left-col">
	<div class="box">
		<h3>Billing Address</h3>
		<p>
			<?= $args['firstname'] . ' ' . $args['lastname'] ?><br />
			<?= $args['address1'] ?>
			<?= empty($args['address2']) ? '' : ',<br />' . $args['address2'] ?><br />
			<?= $args['city'] ?> <?= $args['postcode'] ?><br />
			<?= empty($args['region']) ? '' : $args['region'] . '<br />' ?>
			<?= Country::getCountry($args['country']) ?>
		</p>
		<p>
			<?= empty($args['telhome']) ? '' : 'Home: ' . $args['telhome'] . '<br />' ?>
			<?= empty($args['telwork']) ? '' : 'Work: ' . $args['telwork'] . '<br />' ?>
			<?= empty($args['telmobile']) ? '' : 'Mobile: ' . $args['telmobile'] . '<br />' ?>
		</p>
	</div>
</div>
<div class="col">
	<div class="box">
		<h3>Payment Details</h3>
		<p>
			Card Type: <?= ucfirst($args['cardType']) ?><br />
			Name on Card: <?= $args['cardName'] ?><br />
			Card Number: <?= str_replace(array(0,1,2,3,4,5,6,7,8,9), '*', substr($args['cardNumber'], 0, 12)) . substr($args['cardNumber'], 12) ?><br />
			Start Date: <?= $args['cardStartDateMonth']  . '/' . $args['cardStartDateYear'] ?><br />
			Expiry Date: <?= $args['cardEndDateMonth']  . '/' . $args['cardEndDateYear'] ?>
		</p>
	</div>
</div>
<div class="clearer"></div>
<div class="box">
	<p>
		When you click Confirm, your account will be debited with the above amount, and your membership upgrade will be processed.
	</p>
	<p>
		<form method="post" action="<?= BASE_URL ?>members/doupgrade/">
			<button type="submit"><span>Purchase Upgrade</span></button>&nbsp;
			<a href="<?= BASE_URL ?>members/upgrade/" class="button"><span>Modify Details</span></a>&nbsp;
			<a href="<?= BASE_URL ?>members/cancelupgrade/" class="button"><span>Cancel Upgrade</span></a>
		</form>
	</p>
</div>