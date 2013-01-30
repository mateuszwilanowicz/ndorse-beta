<h2><?= $job->title; ?></h2>
<div class="col2 left-col">
	<div class="box top-box">
		<p class="attribution">
		<?php
			if($job->anonymous) {
		?>
			Posted on <?= $job->getPostDate(); ?>
		<?php
			} else {
		?>
			Posted by <?= $job->firstname; ?> <?= $job->lastname; ?> on <?= $job->getPostDate(); ?>
		<?php
			}
		?>
		</p>
		<p class="job-location">
			<?= $job->location; ?>
		</p>
		<p>
			<?= $job->description; ?>
		</p>
		<p>
			<?= $job->skills ?>
		</p>
        <p>Company: <?= $job->company == '' ? 'Not specified' : $job->company; ?></p>

		<p>
			<a href="<?= BASE_URL ?>jobs/apply/<?= $job->id ?>/" class="button"><span>Apply</span></a>
			<a id="btn_recommend" href="<?= BASE_URL ?>jobs/recommend/<?= $job->id; ?>/" class="button"><span>Recommend</span></a>
		</p>
	</div>
</div>
<script type="text/javascript">
	var BASE_URL = '<?= BASE_URL ?>';
	var recommend_type = 'job';
	var recommend_id = '<?= $job->id ?>';
</script>
<script type="text/javascript" src="<?= SITE_URL ?>js/autocomplete.js"></script>
<script type="text/javascript" src="<?= SITE_URL ?>js/recommend.js"></script>