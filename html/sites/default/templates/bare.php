<?php
	echo PageMessageControl::errors();
	echo PageMessageControl::messages();
?>
<div class="page">
<?= Page::getBlock('main'); ?>
</div>