<h2>Post Request</h2>
<div class="col2">
	<div class="box">
		<?= RequestEditControl::render($request); ?>
	</div>
</div>
<script type="text/javascript" src="<?= SITE_URL ?>js/autocomplete.js"></script>
<script type="text/javascript">
	$(function() {
		autocompleter($('#location'), $('#locationID'), "<?= BASE_URL ?>ajax/location/autocomplete/");
	});
</script>