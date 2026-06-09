define([
    'jquery'
], function ($) {
    'use strict';

    return function (config, element) {
        // Hàm mở Modal Đăng nhập
        window.openModal = function(view) { 
            $('#authModal').addClass('active');
            if (typeof window.switchAuth === 'function') {
                window.switchAuth(view);
            }
        };

        window.closeModal = function() { 
            $('#authModal').removeClass('active'); 
        };

        // Hàm mở/đóng Giỏ hàng
        window.toggleCart = function(open) { 
            if (open) {
                $('#cart-sidebar').addClass('active');
                $('body').addClass('_has-modal');
            } else {
                $('#cart-sidebar').removeClass('active');
                $('body').removeClass('_has-modal');
            }
        };

        // Đóng modal khi click ra ngoài nền mờ
        $(document).on('click', '.sidebar-overlay, .modal-overlay', function() {
            window.closeModal();
            window.toggleCart(false);
        });
    };
});