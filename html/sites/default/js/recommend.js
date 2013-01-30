$(function() {
	$('#btn_recommend').click(function() {
		if($('#ovl_recommend').length == 0) {
			$('body').append('<div class="overlay recommend" id="ovl_recommend"></div>');
		}
		$('#ovl_recommend').load(BASE_URL + 'ajax/recommend/' + recommend_type + '/' + recommend_id + '/', '', function() {
			autocompleter($('#recommend_name'), $('#recommend_userID'), BASE_URL + 'members/autocomplete/');
			$('input[type="radio"]').on('change click', function() {
				if($('#recommend_personType_network').is(':checked')) {
					$('#recommend_emailElement').hide();
					$('#recommend_nameElement').show();
				} else {
					$('#recommend_emailElement').show();
					$('#recommend_nameElement').hide();
				}
			});
			$('.cancel').click(function() {
				$('#ovl_recommend').hide();
				return false;
			});
			$('#recommend_emailElement').hide();
			$('#recommend_form').submit(function() {
				if($('#recommend_personType_network').is(':checked')) {
					if($('#recommend_userID').val() == '') {
						alert('Please choose a valid member');
						return false;
					}
				} else {
					if($('#recommend_email').val() == '') {
						alert('Please enter a valid email address');
						return false;
					}
				}
			});
		}).show();
		return false;
	});
});