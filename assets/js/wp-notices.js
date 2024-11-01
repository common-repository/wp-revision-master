(function($) {
    window.wp_notice = function(type, message, button) {
        var button = (typeof button == 'undefined' || button==true)?'is-dismissible':'';
        var classes = ['success', 'error', 'warning', 'info'];
        if (classes.indexOf(type) != -1) {
            var div = "<div class='notice notice-"+type+" "+button+"'><p>"+message+"</p><button type='button' class='notice-dismiss wp-notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button></div>";
            $(div).insertBefore( $('#tmxrm-revision-master') );
        }
        else {
            console.error('Notice type not supported.');
            return false;
        }
    }

    $(document).on('click', '.wp-notice-dismiss', function () {
       $(this).parent().remove();
    });
})(jQuery);