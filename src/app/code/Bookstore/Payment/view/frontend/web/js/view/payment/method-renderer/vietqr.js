define([
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote'
], function (Component, quote) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Bookstore_Payment/payment/vietqr'
        },

        getCode: function () {
            return 'bookstore_vietqr';
        },

        getQrUrl: function () {
            var total = quote.totals() ? Math.round(quote.totals().grand_total) : 0;
            return 'https://img.vietqr.io/image/VCB-0123456789-compact2.png?amount=' + total + '&addInfo=MagentoBookstore';
        }
    });
});
