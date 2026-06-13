define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push({
        type: 'bookstore_vietqr',
        component: 'Bookstore_Payment/js/view/payment/method-renderer/vietqr'
    });

    return Component.extend({});
});
