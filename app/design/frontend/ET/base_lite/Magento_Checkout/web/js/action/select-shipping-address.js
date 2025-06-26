/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Checkout/js/model/quote'
], function (quote) {
    'use strict';

    return function (shippingAddress) {

        if (shippingAddress && shippingAddress.postcode) {
            try {
                sessionStorage.setItem('pincode_api', shippingAddress.postcode);
            } catch (error) {
                console.error('Error storing postcode in session storage:', error);
            }
        }

        quote.shippingAddress(shippingAddress);
    };
});
