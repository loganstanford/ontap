/**
 * OnTap Admin JavaScript
 *
 * @package OnTap
 * @since   1.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize when DOM is ready
     */
    $(document).ready(function() {
        initManualSync();
    });

    /**
     * Handle manual sync button
     */
    function initManualSync() {
        $('#ontap-manual-sync').on('click', function(e) {
            e.preventDefault();

            var $button = $(this);
            var $spinner = $('#ontap-sync-spinner');
            var $result = $('#ontap-sync-result');

            // Confirm before syncing
            if (!confirm(ontapAdmin.strings.confirmSync)) {
                return;
            }

            // Show spinner and disable button
            $spinner.addClass('is-active');
            $button.prop('disabled', true);
            $result.removeClass('success error').html('');

            // Send AJAX request
            $.ajax({
                url: ontapAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ontap_manual_sync',
                    nonce: ontapAdmin.nonce
                },
                success: function(response) {
                    $spinner.removeClass('is-active');
                    $button.prop('disabled', false);

                    if (response.success) {
                        $result
                            .addClass('success')
                            .html(response.data.message || ontapAdmin.strings.syncSuccess);
                    } else {
                        $result
                            .addClass('error')
                            .html(response.data.message || ontapAdmin.strings.syncError);
                    }
                },
                error: function() {
                    $spinner.removeClass('is-active');
                    $button.prop('disabled', false);
                    $result
                        .addClass('error')
                        .html(ontapAdmin.strings.syncError);
                }
            });
        });
    }

})(jQuery);
