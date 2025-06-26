define([
    'jquery',
    'uiComponent',
    'uiRegistry',
    'mageUtils',
    'Magento_Customer/js/customer-data' // Add customer-data for reloading cart
], function ($, Component, registry, utils, customerData) {
    'use strict';

    return Component.extend({
        defaults: {
            minSearchLength: 2,
            addToCartFormSelector: 'form[data-role="searchsuiteautocomplete-tocart-form"]'
        },

        initialize: function () {
            this._super();
            utils.limit(this, 'load', this.searchDelay);
            $(this.inputSelector)
                .unbind('input')
                .on('input', $.proxy(this.load, this))
                .on('input', $.proxy(this.searchButtonStatus, this))
                .on('focus', $.proxy(this.showPopup, this));
            $(document).on('click', $.proxy(this.hidePopup, this));

            $(document).ready($.proxy(this.searchButtonStatus, this));
            $(document).on('click', '#addcart', $.proxy(this.addToCart, this));
        },

        addToCart: function (event) {
            var self = this;
            var $addToCartBtn = $(event.currentTarget);
            var $form = $addToCartBtn.closest(self.addToCartFormSelector);
            var productId = $form.find('input[name="product"]').val();
            var formKey = $form.find('input[name="form_key"]').val();
            var qty = 1;

            $.ajax({
                url: '/home/index/AddToCart',
                method: 'POST',
                showLoader: true,
                dataType: 'json',
                data: {
                    product_id: productId,
                    form_key: formKey,
                    qty: qty
                },
                success: function (response) {
                    if (response.success) {
                        if ($('body').hasClass('checkout-cart-index')) {
                            location.reload();
                        } else {
                            self.reloadCart();
                        }
                        self.reloadCart();
                    } else {
                        alert(response.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            });
        },


        reloadCart: function () {
            var sections = ['cart'];
            customerData.invalidate(sections);
            customerData.reload(sections, true);
        },

        load: function (event) {
            var self = this;
            var searchText = $(self.inputSelector).val();

            if (searchText.length < self.minSearchLength) {
                return false;
            }

            registry.get('searchsuiteautocompleteDataProvider', function (dataProvider) {
                dataProvider.searchText = searchText;
                dataProvider.load();
            });
        },

        showPopup: function (event) {
            var self = this,
                searchField = $(self.inputSelector),
                searchFieldHasFocus = searchField.is(':focus') && searchField.val().length >= self.minSearchLength;

            registry.get('searchsuiteautocomplete_form', function (autocomplete) {
                autocomplete.showPopup(searchFieldHasFocus);
            });
        },

        hidePopup: function (event) {
            if ($(this.searchFormSelector).has($(event.target)).length <= 0) {
                registry.get('searchsuiteautocomplete_form', function (autocomplete) {
                    autocomplete.showPopup(false);
                });
            }
        },

        searchButtonStatus: function (event) {
            var self = this,
                searchField = $(self.inputSelector),
                searchButton = $(self.searchFormSelector + ' ' + self.searchButtonSelector),
                searchButtonDisabled = (searchField.val().length <= 0);

            searchButton.attr('disabled', searchButtonDisabled);
        },

        spinnerShow: function () {
            var spinner = $(this.searchFormSelector);
            spinner.addClass('loading');
        },

        spinnerHide: function () {
            var spinner = $(this.searchFormSelector);
            spinner.removeClass('loading');
        }

    });
});