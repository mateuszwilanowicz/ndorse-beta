<h2>Recommend</h2>
<div class="box col2">
	<?php
		echo $recommendForm;
	?>
</div>
<script type="text/javascript" src="<?= SITE_URL ?>js/autocomplete.js"></script>
<script type="text/javascript">
$(function() {
	autocompleter($('#name'), $('#userID'), "<?= BASE_URL ?>members/autocomplete/");
	$('input[type="radio"]').on('change click', function() {
		if($('#personType_network').is(':checked')) {
			$('#emailElement').hide();
			$('#nameElement').show();
		} else {
			$('#emailElement').show();
			$('#nameElement').hide();
		}
	});

	$('#emailElement').hide();
});
</script>