/**
 * OnTap Public JavaScript
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
        initTaplist();
    });

    /**
     * Initialize taplist functionality
     */
    function initTaplist() {
        // Add hover effects
        $('.ontap-beer-item').hover(
            function() {
                $(this).addClass('is-hovered');
            },
            function() {
                $(this).removeClass('is-hovered');
            }
        );

        // Future: Add filtering, search, etc.
    }

})(jQuery);
