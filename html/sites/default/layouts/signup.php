<div class="col1">
	<div class="box">
		<h2>Sign Up</h2>
<?php
	if(!isset($fieldErrors))
		$fieldErrors = array();

	echo SignupControl::render($args, $fieldErrors);
?>
	</div>
</div>