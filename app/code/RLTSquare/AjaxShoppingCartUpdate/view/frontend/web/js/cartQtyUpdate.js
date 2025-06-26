define([
    'jquery',
    'Magento_Checkout/js/action/get-totals',
    'Magento_Checkout/js/action/recollect-shipping-rates',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/quote',
    'mage/translate'
], function ($, getTotalsAction, recollectShippingRates, customerData, quote, $t) {
    "use strict";

    function cartQtyUpdate(event) {
        const form = $('form#form-validate');
        const inputField = $(event.target).closest('.custom-qty').find('input');
        const sku = inputField.data('product-sku'); // Using SKU now instead of product ID
        const qty = parseInt(inputField.val(), 10); // Get the quantity entered by the user

        if (!sku || isNaN(qty)) {
            console.error($t('Product SKU or Quantity not found.'));
            return;
        }
        checkSalableQuantity(sku, qty)
            .then(isSalable => {
                if (!isSalable) {
                    alert($t("Sorry, the requested quantity exceeds the available stock. Please adjust your order quantity."));
                    return;
                }
                $('body').trigger('processStart');
                $.ajax({
                    url: form.attr('action'),
                    data: form.serialize(),
                    type: 'POST',
                    showLoader: false,
                    success: function (res) {
                        const parsedResponse = $.parseHTML(res);
                        const updatedForm = $(parsedResponse).find("#form-validate");
                        if (updatedForm.length) {
                            $("#form-validate").replaceWith(updatedForm);
                            customerData.reload(['cart'], true)
                                .then(() => getTotalsAction([], $.Deferred()))
                                .then(() => {
                                    return new Promise(resolve => {
                                        setTimeout(() => {
                                            recollectShippingRates();
                                            resolve();
                                        }, 300);
                                    });
                                })
                                .then(() => {
                                    // updateFreeShippingIndicator();
                                    $('body').trigger('processStop');
                                })
                                .catch(error => {
                                    console.error($t("Cart update error:"), error);
                                    $('body').trigger('processStop');
                                    location.reload();
                                });
                        } else {
                            $('body').trigger('processStop');
                            location.reload();
                            console.error($t("Error: Could not find #form-validate in AJAX response."));
                        }
                    },
                    error: function (xhr, status, error) {
                        $('body').trigger('processStop');
                        console.error($t("AJAX Error:") + ' ' + status + ' ' + error);
                        console.log(xhr.responseText);
                        alert($t("An error occurred while updating the cart. Please try again."));
                    }
                });
            })
            .catch(error => {
                console.error(error);
            });
    }

    function checkSalableQuantity(sku, qty) {
        $('body').trigger('processStart');
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/ajaxshoppingcartupdate/product/getsalablequantity', // Ensure this URL matches your route
                type: 'GET',
                data: { sku: sku },
                success: function (response) {
                    $('body').trigger('processStop');
                    if (response.success) {
                        resolve(qty <= response.salable_quantity);
                    } else {
                        console.error(response.message);
                        reject(response.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error($t("AJAX Error:") + ' ' + status + ' ' + error);
                    console.log(xhr.responseText);
                    reject(error);
                    $('body').trigger('processStop');
                }
            });
        });
    }

    function updateFreeShippingIndicator() {
        $.ajax({
            url: '/freeshippingindicator/ajax/update',
            method: 'POST',
            data: {
                form_key: window.FORM_KEY,
                quote_id: quote.getQuoteId()
            },
            success: function (response) {
                if (response.success) {
                    if (response.html) {
                        $('.freeship-section').replaceWith(response.html);
                    }
                    if (response.cart_data) {
                        const {
                            completion_rate = 0,
                            formatted_remaining = $t('N/A'),
                            is_eligible
                        } = response.cart_data;
                        $('.freeship-progress-bar').css('width', completion_rate + '%');
                        $('.freeship-remaining').text(formatted_remaining);
                        $('.freeship-eligible-message').toggle(!!is_eligible);
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error($t('Free shipping indicator update failed:') + ' ' + error);
                console.log(xhr.responseText);
            }
        });
    }

    $(document).on('change', '.custom-qty input', cartQtyUpdate);
    $(document).on('click', '.custom-qty a', cartQtyUpdate);
});
