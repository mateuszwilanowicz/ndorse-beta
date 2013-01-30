function loadExperienceForm(expID) {
	$('#experience_form_wait').show();
	$.post(baseurl + 'ajax/loadExperience/', { expID: expID }, function(data) {
		if(!$.isPlainObject(data) || $.isEmptyObject(data)) {
			alert('Sorry, there was a problem loading the data. Please reload the page and try again.');
		}
		$('#jobTitle').val(data.jobTitle);
		$('#companyName').val(data.companyName);
		$('#job_startDate').val(data.startDate);
		$('#job_endDate').val(data.endDate);
		$('#job_description').val(data.description);
		$('#job_skills').val(data.skills);
		
		$('#experience_form_box').show();
		$('#experience_form_wait').hide();
	}, 'json');

}

function loadEducationForm(eduID) {
	$('#education_form_wait').show();
	$.post(baseurl + 'ajax/loadEducation/', { eduID: eduID }, function(data) {
		if(!$.isPlainObject(data) || $.isEmptyObject(data)) {
			alert('Sorry, there was a problem loading the data. Please reload the page and try again.');
		}
		$('#courseName').val(data.courseName);
		$('#institution').val(data.institution);
		$('#edu_startDate').val(data.startDate);
		$('#edu_endDate').val(data.endDate);
		$('#edu_description').val(data.description);
		$('#edu_skills').val(data.skills);
		
		$('#education_form_box').show();
		$('#education_form_wait').hide();
	}, 'json');

}

$(function() {
	$('#change_avatar').click(function() {
		$(this).hide();
		$('#avatar_form').show();
		return false;
	});
	$('#avatar_form button.cancel').click(function() {
		$('#avatar_form').hide();
		$('#change_avatar').show();
		return false;
	});

	$('#change_details').click(function() {
		$('#details_view').hide();
		$('#details_form').show();
		return false;
	});
	$('#details_form button.cancel').click(function() {
		$('#details_view').show();
		$('#details_form').hide();
		return false;
	});
	
	$('#change_address').click(function() {
		$('#address_view').hide();
		$('#address_form').show();
		return false;
	});
	$('#address_form button.cancel').click(function() {
		$('#address_form').hide();
		$('#address_view').show();
		return false;
	});
	
	$('#change_contacts').click(function() {
		$('#contacts_view').hide();
		$('#contacts_form').show();	
		return false;
	});
	$('#contacts_form button.cancel').click(function() {
		$('#contacts_form').hide();
		$('#contacts_view').show();
		return false;
	});

	$('.experience-edit-button').click(function() {
		$('.profile-experience').hide();
		loadExperienceForm($(this).data('expid'));
		return false;
	});
	$('#experience_form button.cancel').click(function() {
		$('#experience_form_box').hide();
		$('.profile-experience').show();
		return false;
	});

	$('.education-edit-button').click(function() {
		$('.profile-education-course').hide();
		loadEducationForm($(this).data('eduid'));
		return false;
	});
	$('#education_form button.cancel').click(function() {
		$('#education_form_box').hide();
		$('.profile-education-course').show();
		return false;
	});
});