define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Customer/js/model/customer'
], function ($, ko, Component, stepNavigator, customer) {
    'use strict';

    return Component.extend({
        initialize: function () {
            this._super();
            $(document).ready(function() {
                this.handleSummaryVisibility();
            }.bind(this));
            
            return this;
        },

        handleSummaryVisibility: function () {
            const currentHash = window.location.hash.replace('#', '');
            this.updateSummaryVisibility(currentHash);
            $(window).on('hashchange', function () {
                const hash = window.location.hash.replace('#', '');
                this.updateSummaryVisibility(hash);
            }.bind(this));
        },

        updateSummaryVisibility: function (step) {
            const $summaryWrapper = $('.opc-summary-wrapper');
            if (step === 'prescription' || step === 'shipping') {
                $summaryWrapper.hide();
            } else {
                setTimeout(function() {
                    $summaryWrapper.show();
                }, 3000);
            }
        }
    });
});