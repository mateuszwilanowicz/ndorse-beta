<h2>Members</h2>
<div class="box top-box">
	<div class="section-nav">
		<a href="?status=<?= User::STATUS_PENDING ?>" class="tab<?= !isset($args['status']) || $args['status'] == 1 ? ' on' : '' ?>">Pending
		</a><a href="?status=<?= User::STATUS_ACTIVE ?>" class="tab<?= isset($args['status']) && $args['status'] == 2 ? ' on' : '' ?>">Current</a>
	</div>
	<div class="tab-content">
	<?php
		echo $membersTable;
	?>
	</div>
</div>