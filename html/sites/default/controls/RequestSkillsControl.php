<?php
	class RequestSkillsControl {

		public static function render($request) {
?>
		<div class="request-skills">
		<?php
			if($request->hasType(Ndoorse_Request::TYPE_ADVICE)) {
		?>
			<span class="skill">Advice</span>
		<?php
			}
		?>
		<?php
			if($request->hasType(Ndoorse_Request::TYPE_HELP)) {
		?>
			<span class="skill">Help</span>
		<?php
			}
		?>
		<?php
			if($request->hasType(Ndoorse_Request::TYPE_INTRODUCTION)) {
		?>
			<span class="skill">Introduction</span>
		<?php
			}
		?>
		<?php
			if($request->hasType(Ndoorse_Request::TYPE_MENTORING)) {
		?>
			<span class="skill">Mentoring</span>
		<?php
			}
		?>
		</div>
<?php
		}

	}