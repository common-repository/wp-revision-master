(function($) {

    // Select all
    $(document).on('click', '.tmxrm_checkall', function () {
        if($('.tmxrm_checkall').prop("checked")==true)
            $('.tmxrm_checkbox').prop("checked", true);
        else
            $('.tmxrm_checkbox').prop("checked", false);
    });

    // Delete single revision
    $(document).on('click', '.button-trash-revision', function () {
        var btn = $(this);
        var data = {
            'action': 'tmxrm_trash_revision',
            'id': btn.attr('data-trash'),
            'wpnonce': btn.attr('data-wpnonce')
        };

        $.post(ajaxurl, data, function(response) {
            if(response.success==true) {
                btn.parent().parent().remove();
                window.wp_notice('success', response.data.msg);
            }
            else window.wp_notice('error', response.data.msg);
        });
    });

    // Delete selected revisions
    $(document).on('click', '.button-trash-revision-selected', function () {
        var btn = $(this);
        var val = [];
        $('.tmxrm_checkbox:checkbox:checked').each(function(i){
            val[i] = $(this).val();
        });
        var data = {
            'action': 'tmxrm_trash_revision_selected',
            'id': val,
            'post_id': btn.attr('data-post'),
            'wpnonce': btn.attr('data-wpnonce')
        };

        if(val.length>0)
        $.post(ajaxurl, data, function(response) {
            if(response.success==true) {
                $('.tmxrm_checkbox:checkbox:checked').parent().parent().remove();
                window.wp_notice('success', response.data.msg);
            }
            else window.wp_notice('error', response.data.msg);
        });
    });

    // Limit post revision
    $(document).on('click', '.button-limit-revision', function () {
        var btn = $(this);
        var data = {
            'action': 'tmxrm_limit_single_revision',
            'tmxrm_revision_limit': $('#tmxrm_revision_count_setting').val(),
            'tmxrm_revision_limit_wpnonce': $('#tmxrm_revision_limit_wpnonce').val(),
            'post_ID': btn.attr('data-post'),
            'tmxrm_ajax': true
        };

        $.post(ajaxurl, data, function(response) {
            if(response.success==true) {
                window.wp_notice('success', response.data.msg);
                $('#tmxrm_revision_count_setting').attr('value', data.tmxrm_revision_limit).val(data.tmxrm_revision_limit);
            }
            else window.wp_notice('error', response.data.msg);
        });
    });
})(jQuery);
