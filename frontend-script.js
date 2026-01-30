jQuery(document).ready(function ($) {
    // --- Desktop Logic ---
    var menu = $('.ru-floating-menu');
    var toggle = $('.ru-fm-toggle');

    // Check saved state
    if (localStorage.getItem('ru_fm_collapsed') === 'true') {
        menu.addClass('ru-fm-collapsed');
        toggle.find('.dashicons').removeClass('dashicons-arrow-left-alt2').addClass('dashicons-arrow-right-alt2');
    }

    toggle.on('click', function () {
        menu.toggleClass('ru-fm-collapsed');
        var isCollapsed = menu.hasClass('ru-fm-collapsed');

        // Update Icon
        var icon = $(this).find('.dashicons');
        if (isCollapsed) {
            icon.removeClass('dashicons-arrow-left-alt2').addClass('dashicons-arrow-right-alt2');
            localStorage.setItem('ru_fm_collapsed', 'true');
        } else {
            icon.removeClass('dashicons-arrow-right-alt2').addClass('dashicons-arrow-left-alt2');
            localStorage.setItem('ru_fm_collapsed', 'false');
        }
    });

    // --- Mobile Logic ---
    var mobileTrigger = $('.ru-fm-mobile-trigger');
    var popup = $('.ru-fm-mobile-popup');
    var backdrop = $('.ru-fm-mobile-backdrop');
    var closeBtn = $('.ru-fm-popup-close');
    var rotateContainer = $('.ru-fm-rotating-icon');

    // Icon Rotation Logic
    var items = ruFmFrontend.items || [];
    var currentIndex = 0;

    if (items.length > 1) {
        setInterval(function () {
            currentIndex = (currentIndex + 1) % items.length;
            var nextItem = items[currentIndex];
            if (nextItem.icon) {
                // Fade out
                rotateContainer.fadeOut(200, function () {
                    $(this).html('<img src="' + nextItem.icon + '" alt="Menu">');
                    $(this).fadeIn(200);
                });
            }
        }, 3000); // 3 seconds
    }

    // Toggle Popup
    function togglePopup() {
        var wrapper = $('.ru-fm-mobile-wrapper');
        wrapper.toggleClass('active');
    }

    mobileTrigger.on('click', togglePopup);
    backdrop.on('click', togglePopup);
    closeBtn.on('click', togglePopup);
});
