define([
    'jquery',
    'ko',
    'Magento_Ui/js/modal/confirm',
    'Magento_Checkout/js/view/summary',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/step-navigator'
], function(
    $,
    ko,
    confirm,
    Component,
    quote,
    stepNavigator
) {
    'use strict';

    return Component.extend({
        isVisible: function () {
            if (!stepNavigator.isProcessed('shipping')) {
                return false;
            }

            var shippingAddress = quote.shippingAddress();
            if (shippingAddress) {
                console.log("Shipping State: ", shippingAddress.region);
            } else {
                console.log("No shipping address found.");
            }

            return true;

            /*var allowedStates = ['Tamil Nadu', 'Karnataka', 'Andhra Pradesh', 'Kerala', 'Puducherry'];
            if (shippingAddress && shippingAddress.region && allowedStates.includes(shippingAddress.region)) {
                return true;
            }
            confirm({
                title: 'Limited Delivery Area',
                content: 'We apologize for any inconvenience. At this time, we are only able to deliver to Tamil Nadu, Karnataka, Andhra Pradesh, Kerala, and Puducherry. Please update your shipping address to continue.',
                modalClass: 'custom-shipping-modal',
                actions: {
                    confirm: function() {},
                    cancel: function() {}
                },
                buttons: []
            });

            $('<style>')
                .text(
                    '.custom-shipping-modal .modal-content { font-size: 16px; line-height: 1.5; padding-bottom: 36px; }' +
                    '.custom-shipping-modal .modal-title { font-size: 20px; font-weight: bold; }'
                )
                .appendTo('head');

            return false;*/
        },

        initialize: function () {
            var self = this;
            this._super();

            $(function() {
                $('body').on("click", '#place-order-trigger', function () {
                    sessionStorage.removeItem('CartItems');
                    sessionStorage.removeItem('FileCount');
                    sessionStorage.removeItem('prescription');
                    $(".payment-method._active").find('.action.primary.checkout').trigger('click');
                });
            });
        }
    });
});
