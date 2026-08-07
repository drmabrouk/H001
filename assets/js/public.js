/**
 * Healthedia Public JS Script
 * Manages Header mobile navigation, Authentication Form toggling, tabs, and interactive validation.
 */
(function($) {
    'use strict';

    $(document).ready(function() {

        /* ==========================================
           HEADER MOBILE NAVIGATION TOGGLE
           ========================================== */
        $('.healthedia-mobile-toggle').on('click', function(e) {
            e.preventDefault();
            $('.healthedia-mobile-nav').toggleClass('healthedia-open');
        });

        $('.healthedia-mobile-toggle-new').on('click', function(e) {
            e.preventDefault();
            $('.healthedia-mobile-nav-new').toggleClass('healthedia-open');
        });

        // Close mobile nav when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.healthedia-header').length && !$(e.target).closest('.healthedia-header-new').length) {
                $('.healthedia-mobile-nav').removeClass('healthedia-open');
                $('.healthedia-mobile-nav-new').removeClass('healthedia-open');
            }
        });


        /* ==========================================
           PASSWORD VISIBILITY TOGGLE
           ========================================== */
        $(document).on('click', '.healthedia-password-toggle', function(e) {
            e.preventDefault();
            const input = $(this).siblings('input');
            const icon = $(this).find('svg');

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                $(this).addClass('healthedia-visible');
            } else {
                input.attr('type', 'password');
                $(this).removeClass('healthedia-visible');
            }
        });


        /* ==========================================
           AUTHENTICATION FORM SWITCHING
           ========================================== */
        // Switch between login and registration tabs
        $(document).on('click', '.healthedia-tab-btn', function(e) {
            e.preventDefault();
            const targetForm = $(this).data('target');

            // Update tabs active state
            $('.healthedia-tab-btn').removeClass('active');
            $(this).addClass('active');

            // Toggle forms with transition
            $('.healthedia-auth-card-body').removeClass('active');
            $('#' + targetForm).addClass('active');

            // Clear any old message
            $('.healthedia-msg').hide().text('');
        });

        // Switch to Forgot Password
        $(document).on('click', '.healthedia-forgot-link', function(e) {
            e.preventDefault();

            $('.healthedia-auth-card-body').removeClass('active');
            $('#healthedia-form-forgot').addClass('active');
            $('.healthedia-msg').hide().text('');
        });

        // Back to Login from Forgot Password
        $(document).on('click', '.healthedia-back-to-login', function(e) {
            e.preventDefault();

            // Activate login tab
            $('.healthedia-tab-btn').removeClass('active');
            $('.healthedia-tab-btn[data-target="healthedia-form-login"]').addClass('active');

            $('.healthedia-auth-card-body').removeClass('active');
            $('#healthedia-form-login').addClass('active');
            $('.healthedia-msg').hide().text('');
        });


        /* ==========================================
           DASHBOARD SIDEBAR TAB SWITCHING
           ========================================== */
        $(document).on('click', '.healthedia-sidebar-link', function(e) {
            // Check if it's a real logout or external link
            if ($(this).hasClass('healthedia-external')) {
                return;
            }
            e.preventDefault();
            const targetTab = $(this).attr('href').replace('#', '');

            // Set sidebar active class
            $('.healthedia-sidebar-link').removeClass('active');
            $(this).addClass('active');

            // Show active tab panel
            $('.healthedia-dash-tab').removeClass('active');
            $('#tab-' + targetTab).addClass('active');
        });
    });

})(jQuery);
