<?php
    include SITE_PATH . 'layouts/serviceproviders/view.php';
?>
<div class="col col1">
	<div class="request-filter box top-box">
		<h3>Filter</h3>
		<?= ServiceprovidersFilterControl::render($args); ?>
	</div>
</div>