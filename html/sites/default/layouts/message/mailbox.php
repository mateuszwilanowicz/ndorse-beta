<h2>Messages</h2>
<div class="box top-box">
	<div class="message-options">
		<a href="<?= BASE_URL ?>messages/write/" class="button"><span>New Message</span></a>
	</div>
	<div class="section-nav">
		<a href="<?= BASE_URL ?>messages/inbox/"<?= (isset($args['page']) && $args['page'] == 'inbox') || !isset($args['page']) ? ' class="on tab"' : ' class="tab"' ?>>Inbox
		</a><a href="<?= BASE_URL ?>messages/sent/"<?= (isset($args['page']) && $args['page'] == 'sent') ? ' class="on tab"' : ' class="tab"' ?>>Sent
		</a><a href="<?= BASE_URL ?>messages/deleted/"<?= (isset($args['page']) && $args['page'] == 'deleted') ? ' class="on tab"' : ' class="tab"' ?>>Deleted</a>

	</div>
	<div class="tab-content">
		<?= TableControl::render($headings, $rows, $attributes); ?>
	</div>
</div>