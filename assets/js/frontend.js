/**
 * OnTap Frontend JavaScript
 *
 * @package OnTap
 * @since   1.0.0
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initTaplistFrontend();
    });

    function initTaplistFrontend() {
        // Initialize each taplist wrapper on the page
        $('.ontap-taplist-wrapper').each(function() {
            var $wrapper = $(this);

            // Initialize search
            initSearch($wrapper);

            // Initialize filters
            initFilters($wrapper);

            // Initialize sort
            initSort($wrapper);

            // Initialize modal
            initModal($wrapper);
        });
    }

    /**
     * Initialize search functionality
     */
    function initSearch($wrapper) {
        var $searchInput = $wrapper.find('.ontap-search-input');

        if ($searchInput.length === 0) {
            return;
        }

        // Debounce search to avoid excessive filtering
        var searchTimeout;

        $searchInput.on('input', function() {
            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(function() {
                var searchTerm = $searchInput.val().toLowerCase();
                filterBeers($wrapper, searchTerm);
            }, 300);
        });
    }

    /**
     * Filter beers by search term
     */
    function filterBeers($wrapper, searchTerm) {
        var $beers = $wrapper.find('.ontap-beer-card, .ontap-beer-list-item, .ontap-beer-row');
        var visibleCount = 0;

        $beers.each(function() {
            var $beer = $(this);
            var title = $beer.find('.ontap-beer-title').text().toLowerCase();
            var style = $beer.find('.ontap-beer-style').text().toLowerCase();
            var description = $beer.find('.ontap-beer-description').text().toLowerCase();

            var isMatch = title.indexOf(searchTerm) > -1 ||
                         style.indexOf(searchTerm) > -1 ||
                         description.indexOf(searchTerm) > -1;

            if (isMatch) {
                // Show with animation
                $beer.removeClass('ontap-hidden ontap-hiding');
                visibleCount++;
            } else {
                // Hide with animation
                animateHide($beer);
            }
        });

        // Show "no results" message
        updateNoResultsMessage($wrapper, visibleCount);
    }

    /**
     * Initialize style filters
     */
    function initFilters($wrapper) {
        var $filterBtns = $wrapper.find('.ontap-filter-btn');

        if ($filterBtns.length === 0) {
            return;
        }

        $filterBtns.on('click', function() {
            var $btn = $(this);
            var styleId = $btn.data('style');

            // Update active state
            $filterBtns.removeClass('active');
            $btn.addClass('active');

            // Filter beers
            filterByStyle($wrapper, styleId);
        });
    }

    /**
     * Filter beers by style
     */
    function filterByStyle($wrapper, styleId) {
        var $beers = $wrapper.find('.ontap-beer-card, .ontap-beer-list-item, .ontap-beer-row');
        var visibleCount = 0;

        if (styleId === 'all') {
            $beers.removeClass('ontap-hidden ontap-hiding');
            visibleCount = $beers.length;
        } else {
            $beers.each(function() {
                var $beer = $(this);
                var styleIds = $beer.data('style-ids');

                if (!styleIds) {
                    animateHide($beer);
                    return;
                }

                var styleIdsArray = styleIds.toString().split(',');
                var hasStyle = styleIdsArray.indexOf(styleId.toString()) > -1;

                if (hasStyle) {
                    // Show with animation
                    $beer.removeClass('ontap-hidden ontap-hiding');
                    visibleCount++;
                } else {
                    // Hide with animation
                    animateHide($beer);
                }
            });
        }

        // Clear search when filtering by style
        $wrapper.find('.ontap-search-input').val('');

        // Update no results message
        updateNoResultsMessage($wrapper, visibleCount);
    }

    /**
     * Animate hiding an element
     */
    function animateHide($element) {
        // Add hiding class to trigger transition
        if (!$element.hasClass('ontap-hidden')) {
            $element.addClass('ontap-hiding');

            // After transition completes, add hidden class
            setTimeout(function() {
                if ($element.hasClass('ontap-hiding')) {
                    $element.addClass('ontap-hidden');
                    $element.removeClass('ontap-hiding');
                }
            }, 300); // Match CSS transition duration
        }
    }

    /**
     * Initialize sort functionality
     */
    function initSort($wrapper) {
        var $sortSelect = $wrapper.find('.ontap-sort-select');

        if ($sortSelect.length === 0) {
            return;
        }

        $sortSelect.on('change', function() {
            var sortBy = $(this).val();
            sortBeers($wrapper, sortBy);
        });
    }

    /**
     * Sort beers
     */
    function sortBeers($wrapper, sortBy) {
        var $container = $wrapper.find('.ontap-taplist');
        var $beers = $container.children('.ontap-beer-card, .ontap-beer-list-item');
        var $rows = $container.find('tbody tr.ontap-beer-row');

        // Handle table layout differently
        if ($rows.length > 0) {
            sortTableRows($rows, sortBy);
            return;
        }

        if ($beers.length === 0) {
            return;
        }

        // Sort beers array
        var beersArray = $beers.toArray();

        beersArray.sort(function(a, b) {
            var $a = $(a);
            var $b = $(b);

            switch (sortBy) {
                case 'name':
                    var nameA = $a.find('.ontap-beer-title').text();
                    var nameB = $b.find('.ontap-beer-title').text();
                    return nameA.localeCompare(nameB);

                case 'abv_asc':
                    var abvA = parseFloat($a.find('.ontap-stat-abv').text()) || 0;
                    var abvB = parseFloat($b.find('.ontap-stat-abv').text()) || 0;
                    return abvA - abvB;

                case 'abv_desc':
                    var abvA = parseFloat($a.find('.ontap-stat-abv').text()) || 0;
                    var abvB = parseFloat($b.find('.ontap-stat-abv').text()) || 0;
                    return abvB - abvA;

                case 'style':
                    var styleA = $a.find('.ontap-beer-style').text();
                    var styleB = $b.find('.ontap-beer-style').text();
                    return styleA.localeCompare(styleB);

                case 'tap_number':
                default:
                    // Already sorted by tap number on page load
                    return 0;
            }
        });

        // Re-append in sorted order
        $.each(beersArray, function(index, beer) {
            $container.append(beer);
        });
    }

    /**
     * Sort table rows
     */
    function sortTableRows($rows, sortBy) {
        var $tbody = $rows.first().parent();
        var rowsArray = $rows.toArray();

        rowsArray.sort(function(a, b) {
            var $a = $(a);
            var $b = $(b);

            switch (sortBy) {
                case 'name':
                    var nameA = $a.find('.ontap-beer-name').text();
                    var nameB = $b.find('.ontap-beer-name').text();
                    return nameA.localeCompare(nameB);

                case 'abv_asc':
                    var abvA = parseFloat($a.find('.ontap-abv').text()) || 0;
                    var abvB = parseFloat($b.find('.ontap-abv').text()) || 0;
                    return abvA - abvB;

                case 'abv_desc':
                    var abvA = parseFloat($a.find('.ontap-abv').text()) || 0;
                    var abvB = parseFloat($b.find('.ontap-abv').text()) || 0;
                    return abvB - abvA;

                case 'style':
                    var styleA = $a.find('.ontap-beer-style').text();
                    var styleB = $b.find('.ontap-beer-style').text();
                    return styleA.localeCompare(styleB);

                case 'tap_number':
                default:
                    var tapA = parseInt($a.find('.ontap-tap-number').text()) || 0;
                    var tapB = parseInt($b.find('.ontap-tap-number').text()) || 0;
                    return tapA - tapB;
            }
        });

        // Re-append in sorted order
        $.each(rowsArray, function(index, row) {
            $tbody.append(row);
        });
    }

    /**
     * Update "no results" message
     */
    function updateNoResultsMessage($wrapper, visibleCount) {
        var $noResults = $wrapper.find('.ontap-no-results-message');

        if (visibleCount === 0) {
            if ($noResults.length === 0) {
                $wrapper.find('.ontap-taplist').after(
                    '<p class="ontap-no-results-message">' +
                    'No beers match your search or filter criteria.' +
                    '</p>'
                );
            }
        } else {
            $noResults.remove();
        }
    }

    /**
     * Initialize beer detail modal
     */
    function initModal($wrapper) {
        var $modalOverlay = null;

        // Create modal on first use
        function createModal() {
            if ($modalOverlay !== null) {
                return;
            }

            $modalOverlay = $('<div class="ontap-modal-overlay">' +
                '<div class="ontap-modal">' +
                    '<button class="ontap-modal-close">&times;</button>' +
                    '<div class="ontap-modal-content"></div>' +
                '</div>' +
            '</div>');

            $('body').append($modalOverlay);

            // Close modal on overlay click
            $modalOverlay.on('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });

            // Close modal on close button click
            $modalOverlay.find('.ontap-modal-close').on('click', closeModal);

            // Close modal on ESC key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $modalOverlay.hasClass('active')) {
                    closeModal();
                }
            });
        }

        function openModal(beerId) {
            createModal();

            // Load beer details via AJAX
            $.ajax({
                url: ontapFrontend.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'ontap_get_beer_details',
                    nonce: ontapFrontend.nonce,
                    beer_id: beerId
                },
                beforeSend: function() {
                    $modalOverlay.find('.ontap-modal-content').html(
                        '<div class="ontap-loading">Loading...</div>'
                    );
                    $modalOverlay.addClass('active');
                    $('body').css('overflow', 'hidden');
                },
                success: function(response) {
                    if (response.success) {
                        $modalOverlay.find('.ontap-modal-content').html(response.data.html);
                    } else {
                        $modalOverlay.find('.ontap-modal-content').html(
                            '<p>Failed to load beer details.</p>'
                        );
                    }
                },
                error: function() {
                    $modalOverlay.find('.ontap-modal-content').html(
                        '<p>Failed to load beer details.</p>'
                    );
                }
            });
        }

        function closeModal() {
            if ($modalOverlay) {
                $modalOverlay.removeClass('active');
                $('body').css('overflow', '');
            }
        }

        // View details button click
        $wrapper.on('click', '.ontap-view-details', function(e) {
            e.preventDefault();
            var beerId = $(this).data('beer-id');
            openModal(beerId);
        });

        // Make cards clickable
        $wrapper.on('click', '.ontap-beer-card, .ontap-beer-list-item, .ontap-beer-row', function(e) {
            // Don't trigger if clicking on a button or link
            if ($(e.target).is('button, a') || $(e.target).closest('button, a').length) {
                return;
            }

            var beerId = $(this).data('beer-id');
            if (beerId) {
                openModal(beerId);
            }
        });
    }

})(jQuery);
