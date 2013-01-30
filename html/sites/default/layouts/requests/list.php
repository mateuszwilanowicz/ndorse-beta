<h2>Requests Board</h2>
<div class="col col2 left-col">
	<div class="request-list box top-box">
	<?php
		foreach($requests as $request) {
	?>
		<div class="request-item">
			<h4><a href="<?= BASE_URL ?>requests/request/<?= $request->getID() ?>/"><?= $request->summary ?></a></h4>
			<p>
				<?= $request->location ?>
			</p>
			<?= RequestSkillsControl::render($request); ?>
			<p>
				Posted: <?= $request->getPostDate() ?>, expires: <?= $request->getExpiryDate() ?>
			</p>
		</div>
	<?php
		}
	?>
	</div>

	<p class="buttonbar">
		<a href="<?= BASE_URL ?>requests/post/" class="button"><span>Post a new Request</span></a>
	</p>
</div>

<div class="col col1">
	<div class="request-filter box top-box">
		<h3>Filter</h3>
		<?= RequestFilterControl::render($args); ?>
	</div>
</div>