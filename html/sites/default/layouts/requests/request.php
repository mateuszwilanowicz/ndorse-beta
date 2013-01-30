<h2>Respond</h2>
<div class="col2">
	<div class="box top-box">
		<h3><?= $request->summary ?></h3>
		<p>
			<?= $request->description ?>
		</p>
		<?= RequestSkillsControl::render($request) ?>
		<p>
			Posted: <?= $request->getPostDate() ?>, expires: <?= $request->getExpiryDate() ?>.
		</p>

		<button type="button" id="btn_respond"><span>Respond</span></button>
		<button type="button" id="btn_recommend"><span>Recommend</span></button>
	</div>
</div>

<div class="overlay" id="ovl_respond" style="display: none;">
	<h4>Respond to Request</h4>
	<form id="form_respond" method="post" action="<?= BASE_URL ?>requests/respond/<?= $request->getID() ?>/">
		<label for="message">Your message:</label>
		<textarea id="message" name="message"><?= isset($args['message']) ? $args['message'] : '' ?></textarea>
		<div class="buttonbar">
			<button type="submit"><span>Send</span></button>
			<button type="button" class="cancel"><span>Cancel</span></button>
		</div>
	</form>
</div>

<script type="text/javascript">
var BASE_URL = '<?= BASE_URL ?>';
var recommend_type = 'request';
var recommend_id = '<?= $request->getID() ?>';
$(function() {
	$('#btn_respond').click(function() {
		$('#ovl_respond').show();
	});
	$('.cancel').click(function() {
		$(this).closest('div.overlay').hide();
	});
	$('#type_network').change();
	$('#form_respond').submit(function() {
		if($('#message').val() == '') {
			alert('Please enter a message');
			return false;
		};
	});
});
</script>
<script type="text/javascript" src="<?= SITE_URL ?>js/autocomplete.js"></script>
<script type="text/javascript" src="<?= SITE_URL ?>js/recommend.js"></script>