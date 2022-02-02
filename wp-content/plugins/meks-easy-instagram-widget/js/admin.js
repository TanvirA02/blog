(function($) {
	$(document).ready(function() {

		/* Dynamicaly change span text based on slider input value  */
		$('body').on("input", '.meks-instagram-widget-input-slider', function(e) {
			$(this).next().text($(this).val());
		});

		$('body').on('click', '.meks-notice .notice-dismiss', function(){

            $.ajax( {
                url: ajaxurl,
                method: "POST",
                data: {
                    action: 'meks_remove_notification'
                }
            });

		});
		
	});

})(jQuery);