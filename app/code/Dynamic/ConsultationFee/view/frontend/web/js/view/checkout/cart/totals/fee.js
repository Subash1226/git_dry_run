define([
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals'
], function (ko, Component, quote, priceUtils, totals) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Dynamic_ConsultationFee/checkout/cart/totals/fee'
        },
        totals: quote.getTotals(),

        isDisplayed: function() {
            return this.getValue() != 0;
        },

        getValue: function() {
            var price = 0;
            if (this.totals() && totals.getSegment('consultation_fee')) {
                price = totals.getSegment('consultation_fee').value;
            }
            return price;
        },

        getFormattedValue: function() {
            return priceUtils.formatPrice(this.getValue(), quote.getPriceFormat());
        },

        getBaseValue: function() {
            var price = 0;
            if (this.totals() && totals.getSegment('consultation_fee')) {
                price = totals.getSegment('consultation_fee').base_value;
            }
            return priceUtils.formatPrice(price, quote.getPriceFormat());
        },

        getTitle: function() {
            return 'Doctor Consultation Fee';
        }
    });
});
