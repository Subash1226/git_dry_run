/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Catalog/js/price-utils'
    ],
    function (Component, quote, getPaymentInformationAction, priceUtils) {
        return Component.extend({
            defaults: {
                template: 'Lof_RewardPoints/cart/used_points'
            },
            totals: quote.getTotals(),
            isDisplayed: function() {
                return !!this.getValue();
            },
            getSpendPoints: function(){
                var value = 0;
                var rewardpoints = window.checkoutConfig.rewardpoints;
                if (rewardpoints.spendpoints.value) {
                    value = rewardpoints.spendpoints.value + ' ' + rewardpoints.spendpoints.unit;
                }
                // Add code to refresh rewards data
                var totals = this.totals();
                if (typeof(quote.rewardpoints) != 'undefined') {
                    value = quote.rewardpoints.spendpoints.value + ' ' + quote.rewardpoints.spendpoints.unit;
                }
                return value;
            },
            getValue: function() {
                var value = 0;
                var rewardpoints = window.checkoutConfig.rewardpoints;
                if (rewardpoints.discount.value) {
                    value = Math.round(Math.abs(rewardpoints.discount.value));
                }

                var totals = this.totals();
                if (typeof(quote.rewardpoints) != 'undefined') {
                    if (quote.rewardpoints.discount.value) {
                        value = Math.round(Math.abs(quote.rewardpoints.discount.value));
                    } else {
                        value = 0;
                    }
                }
               
                return value;
            }
        });
    });