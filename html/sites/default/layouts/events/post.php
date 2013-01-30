<h2>Event Details</h2>
<?php
	echo $form->render();
?>
<div class="clearer"></div>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?= SITE_URL ?>js/autocomplete.js"></script>
<script type="text/javascript">
	$(function() {
		$('#startDatePicker').datepicker({
			dateFormat: 'dd/mm/yy',
			constrainInput: true,
			altField: '#startDate',
			altFormat: 'yy-mm-dd'
		});
		$('#endDatePicker').datepicker({
			dateFormat: 'dd/mm/yy',
			controlType: 'select',
			altField: '#endDate',
			altFormat: 'yy-mm-dd'
		});
		$('#add_ticket').click(function(e) {
			$('#no-tickets').hide();
			$(this).before('<div><span class="formElement"><label>Ticket Name:</label><input type="text" class="element ticket-name" name="ticket_name[]" /></span><span class="formElement"><label>Price: (optional)</label><input type="text" class="element" name="ticket_price[]" /></span><button type="button"><span>Remove</span></button><hr /></div>');
		});
		$('#tickets').on('click', '>div>button', function() {
			var needsConfirm = $(this).parent().find('.ticket-name').val() != '';

			console.log($(this).parent().find('.ticket-name').val());

			if(!needsConfirm || confirm('Are you sure you want to remove this ticket type?')) {
				$(this).parent().remove();
			}

			if($('#tickets>div').length == 0) {
				$('#no-tickets').show();
			}
		});

		autocompleter($('#location'), $('#locationID'), "<?= BASE_URL ?>ajax/location/autocomplete/");
	});
</script>