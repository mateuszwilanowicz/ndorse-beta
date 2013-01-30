<div class="col3">
    <h2>Jobs</h2>
	<div class="box">
		
<?php
	echo JobListControl::render($jobs, $args);
?>
		<p>
		    
			<a class="button default-button" href="<?= BASE_URL ?>jobs/post/"><span>Post a new Job</span></a>
		</p>
	</div>
</div>