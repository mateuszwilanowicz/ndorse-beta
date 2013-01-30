<h2><?= $event->title ?></h2>
<div class="box">
	<h3><?= $event->getDateRange() ?></h3>
	<p>
		<?= $event->location ?><br />
		Posted by <?= $event->getPostedBy() ?>
	</p>
	<p>
		<?= $event->details ?>
	</p>
<?php
	if(!empty($tickets)) {
?>
	<table class="event-tickets">
		<thead>
			<tr>
				<th class="event-ticket-type">Ticket Type</th>
				<th class="event-ticket-name">Price</th>
			</tr>
		</thead>
		<tbody>
<?php
		foreach($tickets as $ticket) {
?>
			<tr>
				<td><?= $ticket['name'] ?></td>
				<td><?= $ticket['price'] ?></td>
			</tr>
<?php
		}
?>
		</tbody>
	</table>
<?php
	}
?>

	<div class="buttonbar">
		<a id="btn_recommend" href="<?= BASE_URL ?>events/invite/<?= $event->getID() ?>/" class="button"><span>Invite</span></a>
<?php
	if($event->ticketURL != '') {
		if(substr($event->ticketURL, 0, 4) != 'http') {
			$event->ticketURL = 'http://' . $event->ticketURL;
		}
?>
		<a href="<?= $event->ticketURL ?>" class="button"><span>Buy Tickets</span></a>
<?php
	}
?>
		<a href="<?= BASE_URL ?>events/" class="button"><span>Back to Events Calendar</span></a>
	</div>
</div>
<script>
	var BASE_URL = '<?= BASE_URL ?>';
	var recommend_type = 'event';
	var recommend_id = '<?= $event->getID() ?>';
</script>
<script type="text/javascript" src="<?= SITE_URL ?>js/autocomplete.js"></script>
<script type="text/javascript" src="<?= SITE_URL ?>js/recommend.js"></script>