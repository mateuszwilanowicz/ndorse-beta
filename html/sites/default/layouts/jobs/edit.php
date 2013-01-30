<h2>Post Job</h2>
<form class="job-form" method="post" action="<?= BASE_URL ?>jobs/post/">
	<div class="col col2 left-col">
		<div class="box top-box">
			<h4>Job Details</h4>
			<p class="textfield">
				<label for="title" class="required">What is the job title?</label>
				<input type="text" id="title" class="job-title" name="title" value="<?= isset($args['title']) ? $args['title'] : '' ?>" />
			</p>
			<p class="textfield">
				<label for="location" class="required">Where is the job based?</label>
				<input type="text" id="location" class="job-location" name="location" value="<?= isset($args['location']) ? $args['location'] : '' ?>" />
				<input type="hidden" id="locationID" class="locationID" value="<?= isset($args['locationID']) ? $args['locationID'] : '' ?>" />
			</p>
			<p>
				<strong>Is the job...</strong>
				<input type="radio" id="type_permanent" class="job-type-permanent job-type" name="type[]" value="P"<?= (isset($args['type']) && $args['type'] == 'P') || !isset($args['type']) ? ' checked="checked"' : '' ?>>
				<label for="type_permanent" class="radiolabel">Permanent</label>
				<input type="radio" id="type_temporary" class="job-type-temporary job-type" name="type[]" value="T"<?= (isset($args['type']) && $args['type'] == 'T') ? ' checked="checked"' : '' ?>>
				<label for="type_temporary" class="radiolabel">Temporary</label>
				<input type="radio" id="type_contract" class="job-type-contract job-type" name="type[]" value="C"<?= (isset($args['type']) && $args['type'] == 'C') ? ' checked="checked"' : '' ?>>
				<label for="type_contract" class="radiolabel">Contract</label>
				<input type="radio" id="type_other" class="job-type-other job-type" name="type[]" value="O"<?= (isset($args['type']) && $args['type'] == 'O') ? ' checked="checked"' : '' ?>>
				<label for="type_other" class="radiolabel">Other</label>
			</p>
			<p>
				<strong>What are the hours?</strong>
				<input type="radio" id="hours_fulltime" class="job-hours-fulltime job-hours" name="hours[]" value="FT"<?= (isset($args['hours']) && $args['hours'] == 'FT') || !isset($args['hours']) ? ' checked="checked"' : '' ?>>
				<label for="hours_fulltime" class="radiolabel">Full Time</label>
				<input type="radio" id="hours_parttime" class="job-hours-parttime job-hours" name="hours[]" value="PT"<?= (isset($args['hours']) && $args['hours'] == 'PT') ? ' checked="checked"' : '' ?>>
				<label for="hours_parttime" class="hours_parttime">Part Time</label>
				<input type="radio" id="hours_other" class="job-hours-other job-hours" name="hours[]" value="O"<?= (isset($args['type']) && $args['type'] == 'O') ? ' checked="checked"' : '' ?>>
				<label for="hours_other" class="radiolabel">Other</label>
			</p>
			<p class="form-last">
				<strong>What is the salary range offered?</strong>&nbsp;&nbsp;
				<label for="minSalary">Between &pound;</label>
				<input type="text" id="minSalary" name="minSalary" class="job-salary" value="<?= isset($args['minSalary']) ? $args['minSalary'] : '' ?>" placeholder="minimum" />
				<label for="maxSalary">and &pound;</label>
				<input type="text" id="maxSalary" name="maxSalary" class="job-salary" value="<?= isset($args['maxSalary']) ? $args['maxSalary'] : '' ?>" placeholder="maximum" />
				per annum
			</p>
		</div>

		<div class="box">
			<h4>Description</h4>
			<p class="textfield">
				<label for="description" class="required">Describe the job opportunity:</label>
				<textarea name="description" id="description" class="job-description" placeholder=""><?= isset($args['description']) ? $args['description'] : '' ?></textarea>
			</p>
			<p class="textfield">
				<label for="companyDescription">Optionally, describe the company:</label>
				<textarea name="companyDescription" id="companyDescription" class="job-companydescription"><?= isset($args['companyDescription']) ? $args['companyDescription'] : '' ?></textarea>
			</p>
			<p class="textfield form-last">
				<label for="skills">List any desired skills or experience:</label>
				<textarea name="skills" id="skills" class="job-skills"><?= isset($args['skills']) ? $args['skills'] : '' ?></textarea>
			</p>
		</div>
	</div>

	<div class="col1 col">
		<div class="box top-box">
			<h4>Options</h4>
			<p>
				<label for="dateExpires" class="required">Closing date for applications:</label>
				<input type="date" name="dateExpires" id="dateExpires" value="<?= isset($args['dateExpires']) ? $args['dateExpires'] : '' ?>" /><br />
				<span class="form-note">Closing date must be within 30 days of posting</span>
			</p>
			<p
<?php
	if(false && !$_SESSION['user']->isLevel(Ndoorse_Member::LEVEL_RECRUITER)) {
		echo ' class="form-last"';
	}
?>>
				<input type="checkbox" value="1" name="anonymous" id="anonymous"<?= isset($args['anonymous']) && $args['anonymous'] ? ' checked="checked"' : '' ?> />
				<label for="anonymous" class="radiolabel">Do not show my name on this listing</label>
			</p>
<?php
	if(true || $_SESSION['user']->isLevel(Ndoorse_Member::LEVEL_RECRUITER)) {
?>
			<p>
				<input type="checkbox" value="1" name="board" id="board"<?= (isset($args['board']) && $args['board']) || !isset($args['board']) ? ' checked="checked"' : '' ?> />
				<label for="board" class="radiolabel">Post this opportunity on the ndoorse Jobs board</label>
			</p>
			<p class="form-last">
				<input type="checkbox" value="1" name="match" id="match"<?= isset($args['match']) && $args['match'] ? ' checked="checked"' : '' ?> />
				<label for="match" class="radiolabel">Match this opportunity against relevant ndoorse members</label>
			</p>
<?php
	}
?>
		</div>
		<div class="box">
			<h4>Submit Opportunity</h4>
			<p class="form-last">
				Please note that all postings must be approved before they are displayed to our members. This should take no longer than <?= MODERATION_TIME_HOURS ?> hours.
			</p>
			<div class="buttonbar">
				<button type="submit"><span>Post Opportunity</span></button>
				<a href="<?= BASE_URL ?>jobs/" class="button"><span>Return to Jobs Board</span></a>
			</div>
		</div>
	</div>
</form>

<script type="text/javascript" src="<?= SITE_URL ?>js/autocomplete.js"></script>
<script type="text/javascript">
	$(function() {
		autocompleter($('#location'), $('#locationID'), "<?= BASE_URL ?>ajax/location/autocomplete/");
	});
</script>