
(function ($) {

	$(document).ready(function () {
		$('#_lp_coming_soon_end_time').datetimepicker(
			{
				dateFormat : 'dd-mm-yy'
			}
		);
		$('#_lp_coming_soon').change(function(){

			if($('#_lp_coming_soon').is(":checked")){
				$('.lpcs_enable_area').removeClass('locked');
			} else {
				$('.lpcs_enable_area').addClass('locked');
			}

		})

	});

})(jQuery);
