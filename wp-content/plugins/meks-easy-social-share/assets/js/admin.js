(function($) {
    
    "use strict";

    $(document).ready(function() {

        // platforms field sortable
        $(".platforms-sortable").sortable({
            revert: false,
            cursor: "move",
            placeholder: "platforms-state-highlight"
        });

        // custom color picker
        $('#meks_ess-custom-color').wpColorPicker();

        // show/hide color picker 
        var color_picker = $('.settings_page_meks-easy-social-share .wp-picker-container');
        var custom_color = $('#meks_ess-color-custom');

        if (!custom_color.is(':checked')) {
            color_picker.hide();
        }

        $('.meks_ess-color input[type="radio"]').on('change', function() {
            if (custom_color.is(':checked')) {
                color_picker.show();
            } else {
                color_picker.hide();
            }
        });


        // disable variant base on style selection
        var style_checked = $('.meks-ess-style input:checked').val();
        if (style_checked == '5' || style_checked == '8') {
            $('.meks-ess-style-variant input').attr('disabled', true);
        }
        $('.meks-ess-style input').on('change', function() {
            var style = $(this).val();
            console.log(style);
            if (style == '5' || style == '8') {
                $('.meks-ess-style-variant input').attr('disabled', true);
            } else {
                $('.meks-ess-style-variant input').attr('disabled', false);
            }
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