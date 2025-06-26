/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/quote'
], function (Component, priceUtils, quote) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_SalesRule/summary/discount'
        },
        totals: quote.getTotals(),

        /**
         * @return {*|Boolean}
         */
        isDisplayed: function () {
            return this.isFullMode() && this.getPureValue() != 0; //eslint-disable-line eqeqeq
        },

        /**
         * @return {*}
         */
        getCouponCode: function () {
            if (!this.totals()) {
                return null;
            }

            return this.totals()['coupon_code'];
        },

        /**
         * @return {*}
         */
        getCouponLabel: function () {
            if (!this.totals()) {
                return null;
            }

            return this.totals()['coupon_label'];
        },

        /**
         * Get discount title
         *
         * @returns {null|String}
         */
        getTitle: function () {            
            return "MedizinHub Discount";
        },

        /**
         * @return {Number}
         */
        getPureValue: function () {
            var price = 0;

            if (this.totals() && this.totals()['discount_amount']) {
                price = Math.abs(parseFloat(this.totals()['discount_amount']));
            }

            return price;
        },

        /**
         * @return {*|String}
         */        
        getValue: function() {
            var price = this.getPureValue();
            return priceUtils.formatPrice(price, {
                pattern: '%s',
                decimalSymbol: '.',
                groupSymbol: ',',
                groupLength: 3,
                integerRequired: false
            });
        },
    });
});