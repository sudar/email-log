/**
 * JavaScript for Email Log Plugin.
 *
 * http://sudarmuthu.com/wordpress/email-log
 *
 * @author: Sudar <http://sudarmuthu.com>
 */

/*jslint browser: true, devel: true*/
/*global jQuery, document*/
jQuery(document).ready(function($) {
    $(".email_content").click(function() {
        var w = window.open('', 'newwin', 'width=650,height=500'),
            email_id = $(this).attr('id').replace('email_content_',''),
            data = {
                action: 'display_content',
                email_id: email_id
            };

        $.post(ajaxurl, data, function (response) {
            $(w.document.body).html(response);
        });
    });
});
