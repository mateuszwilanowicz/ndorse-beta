<div class="col1">
	<div class="box">
		<h2>Post a Job</h2>
		<?= JobEditControl::render($job); ?>
	</div>
</div>
<script type="text/javascript" src="<?= SITE_URL ?>js/autocomplete.js"></script>
<script type="text/javascript">
	$(function() {
		autocompleter($('#location'), $('#locationID'), "<?= BASE_URL ?>ajax/location/autocomplete/");
	});
</script>