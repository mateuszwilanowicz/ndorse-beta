<div class="col1">
	<div class="box">
		<h2>Edit Job</h2>
	<?php
		echo $jobForm;
	?>
	</div>
</div>
<script type="text/javascript" src="<?= SITE_URL ?>js/autocomplete.js"></script>
<script type="text/javascript">
	$(function() {
		autocompleter($('#location'), $('#locationID'), "<?= BASE_URL ?>ajax/location/autocomplete/");
		autocompleter($('#company'), $('#companyID'), "<?= BASE_URL ?>ajax/serviceprovider/autocomplete/");
	});
</script>