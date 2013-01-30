<h2><?= isset($args['page']) && $args['page'] == 'reply' ? 'Reply' : 'Write Message' ?></h2>
<div class="box top-box">
	<?= $writer ?>
</div>
<?php
	if(!isset($args['page']) || $args['page'] != 'reply') {
?>
<script type="text/javascript" src="<?= SITE_URL ?>js/autocomplete.js"></script>
<script type="text/javascript">
	$(function() {
		autocompleter($('#to'), $('#userID'), "<?= BASE_URL ?>members/autocomplete/");
	});
</script>
<?php
	}
?>