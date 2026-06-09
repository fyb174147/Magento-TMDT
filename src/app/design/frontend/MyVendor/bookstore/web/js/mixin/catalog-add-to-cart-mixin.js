define([
    'jquery',
    'Magento_Customer/js/model/customer',
    'mage/translate'
], function ($, customer, $t) {
    'use strict';

    return function (targetWidget) {
        $.widget('mage.catalogAddToCart', targetWidget, {
            submitForm: function (form) {
                // Chỉ sử dụng hàm chuẩn của Magento, không check thẻ <body> tĩnh nữa
                if (!customer().firstname) {
                    alert($t('Vui lòng đăng nhập tài khoản để trải nghiệm mua sách tại BookStore!'));
                    window.location.href = '/customer/account/login';
                    return false;
                }
                return this._super(form);
            }
        });

        return $.mage.catalogAddToCart;
    };
});