define([
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'ko'
], function (Component, quote, ko) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Bookstore_Payment/payment/vietqr'
        },
        bankId: 'BIDV',
        accountNo: '8870380558',
        accountName: 'BOOKSTORE DEMO',
        bankName: 'BIDV',
        qrTemplate: 'compact',

        getCode: function () {
            return 'bookstore_vietqr';
        },

        getTitle: function () {
            return 'Chuyển khoản VietQR';
        },

        initialize: function () {
            this._super();
            this.transferConfirmed = ko.observable(false);

            quote.totals.subscribe(function () {
                this.transferConfirmed(false);
            }, this);

            return this;
        },

        getAmount: function () {
            var total = quote.totals() ? Math.round(quote.totals().grand_total) : 0;
            return total > 0 ? total : 0;
        },

        getTransferContent: function () {
            var quoteId = quote.getQuoteId ? quote.getQuoteId() : '';
            return ('BOOKSTORE ' + quoteId).trim().substring(0, 25);
        },

        getQrUrl: function () {
            return 'https://img.vietqr.io/image/' + encodeURIComponent(this.bankId) +
                '-' + encodeURIComponent(this.accountNo) +
                '-' + encodeURIComponent(this.qrTemplate) +
                '.png?amount=' + encodeURIComponent(this.getAmount()) +
                '&addInfo=' + encodeURIComponent(this.getTransferContent()) +
                '&accountName=' + encodeURIComponent(this.accountName);
        },

        getFormattedAmount: function () {
            return new Intl.NumberFormat('vi-VN').format(this.getAmount()) + ' đ';
        },

        canConfirmPayment: function () {
            return this.transferConfirmed() && this.isPlaceOrderActionAllowed();
        },

        placeOrder: function () {
            if (!this.transferConfirmed()) {
                return false;
            }

            return this._super();
        },

        getData: function () {
            var data = this._super();

            data.additional_data = {
                vietqr_self_confirmed: this.transferConfirmed() ? 1 : 0,
                vietqr_amount: this.getAmount(),
                vietqr_content: this.getTransferContent()
            };

            return data;
        }
    });
});
