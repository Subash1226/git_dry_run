    define(
        [
            'Magento_Checkout/js/view/summary/abstract-total',
            'Magento_Checkout/js/model/quote',
            'Magento_Catalog/js/price-utils',
            'Magento_Checkout/js/model/totals'
        ],
        function (Component, quote, priceUtils, totals) {
            "use strict";
            return Component.extend({
                defaults: {
                    isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                    template: 'MedizinhubCore_Summary/checkout/summary/discount'
                },
                totals: quote.getTotals(),
                isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,

                isDisplayed: function() {
                    return this.isFullMode();
                },

                getValue: function() {
                    var price = 0;
                    if (this.totals()) {
                        price = totals.getSegment('savings').value;
                    }
                    return priceUtils.formatPrice(price, {
                        pattern: '%s',
                        decimalSymbol: '.',
                        groupSymbol: ',',
                        groupLength: 3,
                        integerRequired: false
                    });
                },

                getBaseValue: function() {
                    var price = 0;
                    if (this.totals()) {
                        price = this.totals().base_fee;
                    }
                    return priceUtils.formatPrice(price, quote.getBasePriceFormat());
                }
            });
        }
    );
