<h2>Jobs</h2>
<div class="box top-box">
	<div class="section-nav">
		<a href="?status=<?= Ndoorse_Job::STATUS_PENDING ?>" class="tab<?= !isset($args['status']) || $args['status'] == 0 ? ' on' : '' ?>">Pending
		</a><a href="?status=<?= Ndoorse_Job::STATUS_ACTIVE ?>" class="tab<?= isset($args['status']) && $args['status'] == 2 ? ' on' : '' ?>">Current</a>
	</div>
	<div class="tab-content">
		<?php
			echo $jobTable;
		?>
	</div>
</div>