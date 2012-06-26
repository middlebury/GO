$(document).ready(function(){
	
	// Check/Uncheck all
	$('#check_all').click( function() {
		if ($('#check_all').attr('checked')) {
			$('.code_checkbox').attr('checked', true);
		} else {
			$('.code_checkbox').attr('checked', false);
		}
	});
	
}); // End $(document).ready(function(){