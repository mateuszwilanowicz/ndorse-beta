<h2>Edit Event</h2>
<div class="box top-box">
<?php
	echo $eventForm;
?>
</div>
<script type="text/javascript" src="<?= SITE_URL ?>js/autocomplete.js"></script>
<script type="text/javascript">
	$(function() {
		autocompleter($('#location'), $('#locationID'), "<?= BASE_URL ?>ajax/location/autocomplete/");
	});
</script>