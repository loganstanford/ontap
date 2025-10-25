/**
 * OnTap Taplist Manager JavaScript
 *
 * @package OnTap
 * @since   1.0.0
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initTaplistManager();
    });

    function initTaplistManager() {
        // Sort by dropdown
        $('#sort-by').on('change', function() {
            var sortBy = $(this).val();
            var url = new URL(window.location.href);

            // Preserve existing parameters
            var taproom = url.searchParams.get('taproom');

            // Build new URL
            var newUrl = url.pathname + '?page=ontap-manage-taplist';
            if (taproom) {
                newUrl += '&taproom=' + taproom;
            }
            newUrl += '&sort_by=' + sortBy;

            window.location.href = newUrl;
        });

        // Initialize sortable for drag-and-drop reordering (only when sorting by tap number)
        var currentSort = $('#sort-by').val();
        if (currentSort === 'tap_number') {
            $('#the-list').sortable({
                handle: '.handle',
                placeholder: 'sortable-placeholder',
                update: function() {
                    // Enable save button when order changes
                    $('#save-tap-order').prop('disabled', false).addClass('button-primary');
                }
            });
            $('#save-tap-order').show();
        } else {
            // Disable drag-drop and hide save button when not sorting by tap number
            if ($('#the-list').hasClass('ui-sortable')) {
                $('#the-list').sortable('destroy');
            }
            $('#save-tap-order').hide();
            $('.handle').css('cursor', 'default').attr('title', 'Drag-drop only available when sorting by Tap Number');
        }

        // Select all checkbox
        $('#select-all-beers').on('change', function() {
            $('.beer-checkbox').prop('checked', $(this).prop('checked'));
        });

        // Availability toggle
        $('.availability-toggle').on('change', function() {
            var $toggle = $(this);
            var itemId = $toggle.data('item-id');
            var isAvailable = $toggle.prop('checked');

            updateAvailability(itemId, isAvailable, $toggle);
        });

        // Tap number input
        $('.tap-number-input').on('change', function() {
            var $input = $(this);
            var itemId = $input.data('item-id');
            var tapNumber = $input.val();

            updateTapNumber(itemId, tapNumber, $input);
        });

        // Remove button
        $('.remove-from-tap').on('click', function() {
            if (!confirm('Are you sure you want to remove this beer from the taplist?')) {
                return;
            }

            var $button = $(this);
            var itemId = $button.data('item-id');
            var $row = $button.closest('tr');

            removeFromTaplist(itemId, $row);
        });

        // Bulk actions
        $('#doaction').on('click', function() {
            var action = $('#bulk-action-selector-top').val();
            var itemIds = [];

            $('.beer-checkbox:checked').each(function() {
                itemIds.push($(this).val());
            });

            if (action === '-1') {
                alert('Please select a bulk action.');
                return;
            }

            if (itemIds.length === 0) {
                alert('Please select at least one beer.');
                return;
            }

            if (action === 'delete' && !confirm('Are you sure you want to remove ' + itemIds.length + ' beer(s) from the taplist?')) {
                return;
            }

            performBulkAction(action, itemIds);
        });

        // Save tap order
        $('#save-tap-order').on('click', function() {
            saveTapOrder();
        });
    }

    function updateAvailability(itemId, isAvailable, $toggle) {
        $toggle.prop('disabled', true);

        $.ajax({
            url: ontapAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ontap_update_availability',
                nonce: ontapAdmin.nonce,
                item_id: itemId,
                is_available: isAvailable
            },
            success: function(response) {
                $toggle.prop('disabled', false);

                if (response.success) {
                    // Toggle row class
                    var $row = $toggle.closest('tr');
                    if (isAvailable) {
                        $row.removeClass('unavailable');
                    } else {
                        $row.addClass('unavailable');
                    }

                    showNotice(response.data.message, 'success');
                } else {
                    // Revert toggle on error
                    $toggle.prop('checked', !isAvailable);
                    showNotice(response.data.message, 'error');
                }
            },
            error: function() {
                $toggle.prop('disabled', false);
                $toggle.prop('checked', !isAvailable);
                showNotice('Failed to update availability', 'error');
            }
        });
    }

    function updateTapNumber(itemId, tapNumber, $input) {
        $input.prop('disabled', true);

        $.ajax({
            url: ontapAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ontap_update_tap_number',
                nonce: ontapAdmin.nonce,
                item_id: itemId,
                tap_number: tapNumber
            },
            success: function(response) {
                $input.prop('disabled', false);

                if (response.success) {
                    showNotice(response.data.message, 'success');
                } else {
                    showNotice(response.data.message, 'error');
                }
            },
            error: function() {
                $input.prop('disabled', false);
                showNotice('Failed to update tap number', 'error');
            }
        });
    }

    function removeFromTaplist(itemId, $row) {
        $row.css('opacity', '0.5');

        $.ajax({
            url: ontapAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ontap_remove_from_taplist',
                nonce: ontapAdmin.nonce,
                item_id: itemId
            },
            success: function(response) {
                if (response.success) {
                    $row.fadeOut(300, function() {
                        $(this).remove();
                        updateStatistics();
                    });
                    showNotice(response.data.message, 'success');
                } else {
                    $row.css('opacity', '1');
                    showNotice(response.data.message, 'error');
                }
            },
            error: function() {
                $row.css('opacity', '1');
                showNotice('Failed to remove item', 'error');
            }
        });
    }

    function performBulkAction(action, itemIds) {
        var $button = $('#doaction');
        $button.prop('disabled', true);

        $.ajax({
            url: ontapAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ontap_bulk_action',
                nonce: ontapAdmin.nonce,
                bulk_action: action,
                item_ids: itemIds
            },
            success: function(response) {
                $button.prop('disabled', false);

                if (response.success) {
                    showNotice(response.data.message, 'success');

                    // Reload page to reflect changes
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    showNotice(response.data.message, 'error');
                }
            },
            error: function() {
                $button.prop('disabled', false);
                showNotice('Failed to perform bulk action', 'error');
            }
        });
    }

    function saveTapOrder() {
        var order = [];

        $('#the-list tr').each(function() {
            var itemId = $(this).data('item-id');
            if (itemId) {
                order.push(itemId);
            }
        });

        var $button = $('#save-tap-order');
        $button.prop('disabled', true).text('Saving...');

        $.ajax({
            url: ontapAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ontap_save_tap_order',
                nonce: ontapAdmin.nonce,
                order: JSON.stringify(order)
            },
            success: function(response) {
                $button.prop('disabled', false).text('Save Order').removeClass('button-primary');

                if (response.success) {
                    showNotice(response.data.message, 'success');

                    // Update tap numbers in the display
                    $('#the-list tr').each(function(index) {
                        $(this).find('.tap-number-input').val(index + 1);
                    });
                } else {
                    showNotice(response.data.message, 'error');
                }
            },
            error: function() {
                $button.prop('disabled', false).text('Save Order');
                showNotice('Failed to save tap order', 'error');
            }
        });
    }

    function updateStatistics() {
        // Recalculate and update statistics
        var totalBeers = $('#the-list tr').length;
        var availableBeers = $('#the-list tr').not('.unavailable').length;
        var unavailableBeers = totalBeers - availableBeers;

        $('.ontap-stats').html(
            '<strong>Statistics:</strong> ' +
            totalBeers + ' beers total, ' +
            availableBeers + ' available, ' +
            unavailableBeers + ' unavailable'
        );
    }

    function showNotice(message, type) {
        var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        $('.wrap h1').after($notice);

        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $notice.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }

})(jQuery);
