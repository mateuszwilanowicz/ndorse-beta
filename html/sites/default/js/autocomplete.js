function autocompleter(control, idControl, url) {
	var controlName = control.attr('id');
	
	control.keyup(function() {
		if(control.val().length > 0) {
			$.post(url, { name: control.val() }, function(data) {
				if(data.length > 0) {
					if($('#' + controlName + '_autocomplete').length > 0) {
						$('#' + controlName + '_autocomplete').html(data).show();
					} else {
						control.after('<div id="' + controlName + '_autocomplete" class="autocomplete">' + data + '</div>');
						$('#' + controlName + '_autocomplete').css({ 'top': (control.css('top') + control.height), 'left': control.css('left') }); 
					}
					$('#' + controlName + '_autocomplete a').on('click', function() {
						control.val($(this).text());
						idControl.val($(this).attr('href').substr(1));
						$('#' + controlName + '_autocomplete').hide();
						return false;
					});
				} else {
					$('#' + controlName + '_autocomplete').html('').hide();
				}
			});
		}
	}).attr('autocomplete', 'off');	
}