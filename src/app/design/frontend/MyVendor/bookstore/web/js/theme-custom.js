/**
 * Logic điều khiển giao diện (Toggle theme, modals, navigation)
 */
define([
    'jquery'
], function ($) {
    'use strict';

    return function (config, element) {
        window.toggleTheme = function() {
            const body = document.body;
            const isDark = body.getAttribute('data-theme') === 'dark';
            isDark ? body.removeAttribute('data-theme') : body.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', isDark ? 'light' : 'dark');
        };

        window.openModal = function(view) { 
            $('#authModal').css('display', 'flex');
            window.switchAuth(view);
        };

        window.closeModal = function() { $('#authModal').hide(); };

        window.toggleCart = function(open) { 
            $('#cart-sidebar').toggleClass('open', open); 
        };

        // Các logic khác...
    };
});