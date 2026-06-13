define([
    'mage/utils/wrapper'
], function (wrapper) {
    'use strict';

    var allowedMethods = ['cashondelivery', 'bookstore_vietqr'],
        methodTitles = {
            cashondelivery: 'Thanh toán khi nhận hàng (COD)',
            bookstore_vietqr: 'Chuyển khoản VietQR'
        };

    return function (paymentService) {
        paymentService.setPaymentMethods = wrapper.wrap(
            paymentService.setPaymentMethods,
            function (originalAction, methods) {
                var filteredMethods = (methods || []).filter(function (method) {
                    return allowedMethods.indexOf(method.method) !== -1;
                }).map(function (method) {
                    method.title = methodTitles[method.method] || method.title;
                    return method;
                }).sort(function (left, right) {
                    return allowedMethods.indexOf(left.method) - allowedMethods.indexOf(right.method);
                });

                return originalAction(filteredMethods);
            }
        );

        return paymentService;
    };
});
