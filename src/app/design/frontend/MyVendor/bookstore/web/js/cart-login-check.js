define([
    'jquery',
    'Magento_Ui/js/modal/alert'
], function ($, alert) {
    'use strict';

    return function (config, element) {
        // Intercept form submit khi user add-to-cart
        $(document).on('submit', 'form[data-role="tocart-form"], form[action*="checkout/cart/add"]', function(e) {
            // Check xem user có logged in không
            var customerIsLoggedIn = window.customerIsLoggedIn || false;
            
            if (typeof window.customerData !== 'undefined') {
                // Try to get from Magento customerData storage
                try {
                    var sectionsConfig = window.customerSections || {};
                    // Nếu không có customer section, thì chưa login
                    customerIsLoggedIn = false;
                } catch(e) {
                    customerIsLoggedIn = false;
                }
            }

            // Đơn giản hơn: check xem element có attribute nào chứa thông tin login
            // hoặc check qua AJAX
            var isLoggedInCheck = $.ajax({
                url: window.BASE_URL + 'rest/V1/customers/me',
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + (window.customerToken || ''),
                },
                async: false,
                dataType: 'json',
                success: function() {
                    return true;
                },
                error: function() {
                    return false;
                }
            });

            // Cách khác: Kiểm tra localStorage/sessionStorage hoặc class trong HTML
            var bodyClass = $('body').attr('class');
            customerIsLoggedIn = bodyClass && bodyClass.indexOf('logged-in') !== -1;

            if (!customerIsLoggedIn) {
                e.preventDefault();
                e.stopPropagation();
                
                // Hiện thông báo
                alert({
                    title: 'Cần đăng nhập',
                    content: 'Vui lòng đăng nhập hoặc đăng ký tài khoản để mua hàng!',
                    actions: {
                        always: function() {
                            // Chuyển hướng sang trang login
                            window.location.href = window.BASE_URL + 'customer/account/login/';
                        }
                    }
                });
                
                return false;
            }
        });

        // Intercept nút Add to Cart click nếu là button
        $(document).on('click', '.action.tocart, .btn-add-to-cart, [onclick*="addToCart"]', function(e) {
            var bodyClass = $('body').attr('class');
            var customerIsLoggedIn = bodyClass && bodyClass.indexOf('logged-in') !== -1;

            if (!customerIsLoggedIn) {
                e.preventDefault();
                e.stopPropagation();

                if (typeof window.openModal === 'function') {
                    window.openModal('login');
                } else {
                    window.location.href = window.BASE_URL + 'customer/account/login/';
                }
                
                return false;
            }
        });
    };
});
