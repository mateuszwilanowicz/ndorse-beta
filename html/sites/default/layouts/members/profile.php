<h2>Profile</h2>
<div class="col1 col left-col">
	<div class="box top-box profile-avatar-box">
	<?php
		if(!empty($avatar)) {
	?>
		<img src="<?= SITE_URL ?>images/avatars/<?= $avatar ?>" alt="" id="img_avatar" />
	<?php
		}
	?>
		<a href="#" id="change_avatar" class="button"><span>Change my Profile Image</span></a>
		<form method="post" enctype="multipart/form-data" id="avatar_form" style="display: none;">
			<input type="hidden" name="memberID" value="<?= $userID ?>" />
			<input type="hidden" name="section" value="image" />
			<p>
				<label for="avatar">Change your profile image:</label>
				<input type="file" name="avatar" id="avatar" />
			</p>
			<p>
				<strong>Note:</strong> Your profile image must be at least 250 x 250 pixels, and in JPG, GIF or PNG format, with a maximum size of 2 MB.
			</p>
			<div class="buttonbar">
				<button type="submit" class="save"><span>Save Changes</span></button>
				<button type="button" class="cancel"><span>Cancel</span></button>
			</div>
		</form>
	</div>
</div>

<div class="col2 col">
	<div class="box top-box">
		<h3>Details</h3>
		<div id="details_view">
			<h4><?= $user->getName() ?></h4>
			<p>
				<label for="identifier">Your ndoorse code:</label>
				<input type="text" name="identifier" readonly="readonly" value="<?= $user->identifier ?>" />
			</p>
			<a href="#" id="change_details" class="button"><span>Change my Details</span></a>
		</div>
		<form method="post" id="details_form" style="display: none;">
			<input type="hidden" name="memberID" value="<?= $userID ?>" />
			<input type="hidden" name="section" value="details" />
			<p>
				Your name will only be displayed to people in your network.
			</p>
			<p>
				<label for="firstname">Name:</label>
				<input type="text" name="firstname" class="profile-form-name" value="<?= $user->firstname ?>" placeholder="First Name" />
				<input type="text" name="lastname" class="profile-form-name" value="<?= $user->lastname ?>" placeholder="Surname" />
			</p>
			<p>
				If you would like to change your password, please enter a new one twice below.
			</p>
			<p>
				<label for="password">Password:</label>
				<input type="password" name="password" id="password" placeholder="Password" /><br />
				<input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" />
			</p>
			<p>
				Give this code to people you would like to recommend to join ndoorse.
			</p>
			<p>
				<label for="identifier">Your ndoorse code:</label>
				<input type="text" name="identifier" readonly="readonly" value="<?= $user->identifier ?>" />
			</p>
			<div class="buttonbar">
				<button type="submit" class="save"><span>Save Changes</span></button>
				<button type="button" class="cancel"><span>Cancel</span></button>
			</div>
		</form>
	</div>

	<div class="box">
		<h3>Address Details</h3>
		<div id="address_view">
			<?= $user->getAddress() ?>
			<br />
			<a href="#" class="button" id="change_address"><span>Change my Address</span></a>
		</div>
		<form method="post" id="address_form" style="display: none;">
			<input type="hidden" name="section" value="address" />
			<p>
				<label for="address1">Address:</label>
				<input type="text" name="address1" id="address1" value="<?= $user->address1 ?>" placeholder="Address line 1" /><br />
				<input type="text" name="address2" id="address2" value="<?= $user->address2 ?>" placeholder="Address line 2" />
			</p>
			<p>
				<label for="region">Region:</label>
				<input type="text" name="region" id="region" value="<?= $user->region ?>" />
			</p>
			<p>
				<label for="city">Town/City:</label>
				<input type="text" name="city" id="city" value="<?= $user->city ?>" />
			</p>
			<p>
				<label for="postcode">Postcode:</label>
				<input type="text" name="postcode" id="postcode" value="<?= $user->postcode ?>" />
			</p>
			<p>
				<label for="country">Country:</label>
				<select name="country" id="country">
				<?php
					foreach($countries as $country) {
				?>
					<option value="<?= $country['countrycode'] ?>" <?= $country['countrycode'] == $user->country ? ' selected="selected"' : '' ?>><?= $country['name'] ?></option>
				<?php
					}
				?>
				</select>
			</p>
			<div class="buttonbar">
				<button type="submit" class="save"><span>Save Changes</span></button>
				<button type="button" class="cancel"><span>Cancel</span></button>
			</div>
		</form>
	</div>

	<div class="box">
		<h3>Contact Details</h3>
		<div id="contacts_view">
			<?php
				if(!empty($user->telhome)) {
					echo '<strong>Home Telephone:</strong> ' . $user->telhome . '<br />';
				}
				if(!empty($user->telwork)) {
					echo '<strong>Work Telephone:</strong> ' . $user->telwork . '<br />';
				}
				if(!empty($user->telmobile)) {
					echo '<strong>Mobile Telephone:</strong> ' . $user->telmobile . '<br />';
				}
			?>
			<a href="#" class="button" id="change_contacts"><span>Change my Contact Details</span></a>
		</div>
		<form method="post" id="contacts_form" style="display: none;">
			<input type="hidden" name="section" value="contact" />
			<h4>Telephone:</h4>
			<p>
				<label for="telhome">Home Telephone:</label>
				<input type="text" name="telhome" id="telhome" value="<?= $user->telhome ?>" />
			</p>
			<p>
				<label for="telwork">Work Telephone:</label>
				<input type="text" name="telwork" id="telhome" value="<?= $user->telwork ?>" />
			</p>
			<p>
				<label for="telhome">Mobile Telephone:</label>
				<input type="text" name="telmobile" id="telmobile" value="<?= $user->telmobile ?>" />
			</p>

			<h4>Email</h4>
			<p>
				<label for="email">Email Address:</label>
				<input type="text" name="email" id="email" value="<?= $user->email ?>" placeholder="Email address" /><br />
				<label for="emailconfirm">Confirm Email:</label>
				<input type="text" name="emailconfirm" id="emailconfirm" value="<?= $user->email ?>" placeholder="Confirm email address" />
			</p>
			<div class="buttonbar">
				<button type="submit" class="save"><span>Save Changes</span></button>
				<button type="button" class="cancel"><span>Cancel</span></button>
			</div>
		</form>
	</div>

	<div class="box">
		<h3>Experience</h3>
		<span class="wait" id="experience_form_wait" style="display: none">Please wait...</span>
		<?php
			foreach($experience as $exp) {
		?>
		<div class="profile-experience" id="profile-experience-<?= $exp['experienceID'] ?>">
			<div class="edit-button-right">
				<a href="#" class="button experience-edit-button" data-expid="<?= $exp['experienceID'] ?>"><span>Edit</span></a>
			</div>
			<h5><?= $exp['jobTitle'] ?> at <?= $exp['companyName'] ?></h5>
			<p class="profile-experience-dates">
			<?php
				if(empty($exp['endDate'])) {
					echo 'Since ' . date('F Y', strtotime($exp['startDate']));
				} else {
					echo 'From ' . date('F Y', strtotime($exp['startDate'])) . ' to ' . date('F Y', strtotime($exp['endDate']));
				}
			?>
			</p>
			<p class="profile-experience-description">
				<?= $exp['description'] ?>
			</p>
		</div>
		<?php
			}
		?>
		<div class="box" id="experience_form_box" style="display: none;">
			<h4>Experience Details</h4>
			<form method="post" id="experience_form">
				<input type="hidden" name="experienceID" id="experienceID" value="" />
				<input type="hidden" name="section" value="experience" />
				<p>
					<label for="jobTitle">Position held: </label>
					<input type="text" name="jobTitle" id="jobTitle" value="" placeholder="Job title" />
					<label for="companyName" class="middle-label">at</label>
					<input type="text" name="companyName" id="companyName" value="" placeholder="Company name" />
				</p>
				<p>
					<label for="job_startDate">I worked here from </label>
					<input type="date" name="startDate" id="job_startDate" value="" />
					<label for="job_endDate" class="middle-label">until</label>
					<input type="date" name="endDate" id="job_endDate" value="" /><br />
					(leave the end date blank if this is your current job)
				</p>
				<p>
					Describe your duties and responsibilities during this role.
				</p>
				<p>
					<label for="job_description" class="description-label">Description:</label>
					<textarea name="description" id="job_description" placeholder="Describe your duties and responsibilities"></textarea>
				</p>
				<p>
					List the skills you used or developed during this role.
				</p>
				<p>
					<label for="job_skills" class="skills-label">Skills:</label>
					<input type="text" name="skills" id="job_skills" placeholder="Key skills used or developed in this role" />
				</p>
				<div class="buttonbar">
					<button type="submit" class="save"><span>Save Changes</span></button>
					<button type="button" class="cancel"><span>Cancel</span></button>
				</div>
			</form>
		</div>
	</div>

	<div class="box">
		<h3>Education</h3>
		<span class="wait" id="education_form_wait" style="display: none">Please wait...</span>
		<?php
			foreach($education as $course) {
		?>
		<div class="profile-education-course">
			<div class="edit-button-right">
				<a href="#" class="button education-edit-button" data-eduid="<?= $course['educationID'] ?>"><span>Edit</span></a>
			</div>
			<h5><?= $course['courseName'] ?> at <?= $course['institution'] ?></h5>
			<p class="profile-education-dates">
			<?php
				if(empty($endDate)) {
					echo 'From ' . date('F Y', strtotime($course['startDate'])) . ' to present';
				} else {
					echo 'From ' . date('F Y', strtotime($course['startDate'])) . ' to ' . date('F Y', strtotime($course['endDate']));
				}
			?>
			</p>
			<p class="profile-education-description">
				<?= $course['description'] ?>
			</p>
		</div>
		<?php
			}
		?>
		<div class="box" id="education_form_box" style="display: none;">
			<h4>Education Details</h4>
			<form method="post" id="education_form">
				<input type="hidden" name="educationID" id="educationID" value="" />
				<input type="hidden" name="section" value="education" />
				<p>
					<label for="courseName">I studied </label>
					<input type="text" name="courseName" id="courseName" value="" placeholder="Qualification and Course name" />
					<label for="institution" class="middle-label">at</label>
					<input type="text" name="institution" id="institution" value="" placeholder="Institution" />
				</p>
				<p>
					<label for="edu_startDate">Between</label>
					<input type="date" name="startDate" id="edu_startDate" value="" />
					<label for="edu_endDate" class="middle-label"> and </label>
					<input type="date" name="endDate" id="edu_endDate" value="" />
				</p>
				<p>
					<label for="edu_description" class="description-label">Description:</label>
					<textarea name="description" id="edu_description" placeholder="Describe what the course entailed"></textarea>
				</p>
				<p>
					<label for="edu_skills" class="skills-label">Skills:</label>
					<input type="text" name="skills" id="edu_skills" placeholder="Key skills used or developed on this course" />
				</p>
				<div class="buttonbar">
					<button type="submit" class="save"><span>Save Changes</span></button>
					<button type="button" class="cancel"><span>Cancel</span></button>
				</div>
			</form>
		</div>
	</div>

	<div class="box">
		<form method="post">
			<?= ContactControl::render($notifications, $userProfile) ?>
			<div class="buttonbar">
				<button type="submit" class="save"><span>Save Changes</span></button>
				<button type="button" class="cancel"><span>Cancel</span></button>
			</div>
		</form>
	</div>
	
	<div class="box">
        <h3>Service Providers</h3>
        <?= ServiceProviderProfileControl::render($args, $userProfile, $fieldErrors, $allServiceProviders) ?>
    </div>

</div>

<script type="text/javascript" src="<?= SITE_URL ?>js/autocomplete.js"></script>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>
<script type="text/javascript">
$(function() {
    autocompleter($('#serviceprovider'), $('#serviceproviderID'), "<?= BASE_URL ?>ajax/serviceprovider/autocomplete/");
    autocompleter($('#location'), $('#locationID'), "<?= BASE_URL ?>ajax/location/autocomplete/");
});


</script>



<!--
<div class="col2">
	<div class="box">
<?php
	foreach($education as $e) {
		echo "<b>" . $e['year'] . "</b> " . $e['institution'] . ": " . $e['courseName'] . " | " . $e['description'];
		if(count(explode(',',$e['skills'])) > 1) {
			echo " | Skills: " . $e['skills'];
		}
		echo "<input type='button' value='edit'/>";
		echo "<input type='button' value='delete'/>";
		echo "<br />";
	}
?>
	</div>
	<div>
<?php
	foreach($experience as $e) {
		echo "<b>" . $e['year'] . "</b> " . $e['jobTitle'] . " at " . $e['companyName'] . " for " . $e['duration'] . " | " . $e['description'];
		if(count(explode(',',$e['skills'])) > 1) {
			echo " | Skills: " . $e['skills'];
		}
?>
		<button type="button"><span>Edit</span></button>
		<button type="button"><span>Delete</span></button>
		<br />
<?php
		pr($e, false);
	}
?>
	</div>
</div>
-->
<script type="text/javascript">
	var baseurl = "<?= BASE_URL ?>";
</script>
<script type="text/javascript" src="<?= SITE_URL ?>js/profile_edit.js"></script>