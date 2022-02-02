(function($) {
    $(document).ready(function() {

        $('body').on('click', '.meks-themeforest-smart-widget-type', function(){
            var type = $(this).val();
            var wrap = $(this).closest('.widget-content');
            var user = wrap.find('.meks-themeforest-smart-widget-user');
            var cat = wrap.find('.meks-themeforest-smart-widget-cat');
            var order = wrap.find('.meks-themeforest-smart-widget-order');
            if(type == 'popular'){
                user.fadeOut(300);
                cat.fadeOut(300);
                order.fadeOut(300);
            } else if (type == 'latest'){
                user.fadeOut(300);
                cat.fadeIn(300);
                order.fadeOut(300);
            } else {
                //user
                user.fadeIn(300);
                cat.fadeIn(300);
                order.fadeIn(300);
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