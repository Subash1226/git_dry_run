define([
    'jquery',
    'ko',
    'uiComponent',
    'underscore',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Magento_Checkout/js/checkout-data',
    'Magento_Customer/js/customer-data'
], function ($, ko, Component, _, selectShippingAddressAction, quote, formPopUpState, checkoutData, customerData) {
    'use strict';

    var countryData = customerData.get('directory-data');

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping-address/address-renderer/default'
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super();
            
            // Enhanced isSelected computation
            this.isSelected = ko.computed(function () {
                var isSelected = false,
                    shippingAddress = quote.shippingAddress(),
                    selectedAddressKey = checkoutData.getSelectedShippingAddress();
                
                // Check if address is selected in the quote model
                if (shippingAddress) {
                    isSelected = shippingAddress.getKey() == this.address().getKey(); //eslint-disable-line eqeqeq
                }
                
                // If not found in quote, check if it's in checkoutData
                if (!isSelected && selectedAddressKey) {
                    isSelected = selectedAddressKey == this.address().getKey(); //eslint-disable-line eqeqeq
                    
                    // If this address is selected in checkoutData but not in quote, apply it
                    if (isSelected && (!shippingAddress || shippingAddress.getKey() !== selectedAddressKey)) {
                        setTimeout(function() {
                            this.applyAddressSelection();
                        }.bind(this), 500);
                    }
                }

                return isSelected;
            }, this);
            
            // Apply any saved address selection when component initializes
            setTimeout(function() {
                this.applySavedAddressSelection();
            }.bind(this), 500);
            
            return this;
        },
        
        /**
         * Apply saved address selection from checkoutData if available
         */
        applySavedAddressSelection: function() {
            var selectedAddressKey = checkoutData.getSelectedShippingAddress();
            
            if (selectedAddressKey && this.address().getKey() == selectedAddressKey) { //eslint-disable-line eqeqeq
                this.applyAddressSelection();
            } else if (!selectedAddressKey) {
                // If no address is selected (new quote), check if this is the default address
                this.selectDefaultAddressIfApplicable();
            }
        },
        
        /**
         * Select default shipping address or first address (ID 1) for new quotes
         */
        selectDefaultAddressIfApplicable: function() {
            // Only run this logic once across all address renderers
            if (window.addressSelectionInitialized) {
                return;
            }
            
            var isDefault = this.address().isDefaultShipping();
            var isFirstAddress = this.address().customerAddressId === '1';
            
            // If this is the default shipping address or has ID 1, select it
            if (isDefault || (!checkoutData.getSelectedShippingAddress() && isFirstAddress)) {
                window.addressSelectionInitialized = true;
                
                // Small delay to ensure all address renderers are initialized
                setTimeout(function() {
                    this.applyAddressSelection();
                }.bind(this), 500);
            }
        },
        
        /**
         * Apply the current address selection to the UI and data model
         */
        applyAddressSelection: function() {
            selectShippingAddressAction(this.address());
            checkoutData.setSelectedShippingAddress(this.address().getKey());
            
            // Update UI to reflect selection
            $('.shipping-address-item').removeClass('selected-item').addClass('not-selected-item');
            $('#address-' + this.address().customerAddressId).removeClass('not-selected-item').addClass('selected-item');
            
            // Ensure radio button is checked
            $('input[name="shipping-address-selection"]').prop('checked', false);
            $('#radio-' + this.address().customerAddressId).prop('checked', true);
        },

        /**
         * @param {String} countryId
         * @return {String}
         */
        getCountryName: function (countryId) {
            return countryData()[countryId] != undefined ? countryData()[countryId].name : ''; //eslint-disable-line
        },

        /**
         * Get customer attribute label
         *
         * @param {*} attribute
         * @returns {*}
         */
        getCustomAttributeLabel: function (attribute) {
            var label;

            if (typeof attribute === 'string') {
                return attribute;
            }

            if (attribute.label) {
                return attribute.label;
            }

            if (_.isArray(attribute.value)) {
                label = _.map(attribute.value, function (value) {
                    return this.getCustomAttributeOptionLabel(attribute['attribute_code'], value) || value;
                }, this).join(', ');
            } else if (typeof attribute.value === 'object') {
                label = _.map(Object.values(attribute.value)).join(', ');
            } else {
                label = this.getCustomAttributeOptionLabel(attribute['attribute_code'], attribute.value);
            }

            return label || attribute.value;
        },

        /**
         * Get option label for given attribute code and option ID
         *
         * @param {String} attributeCode
         * @param {String} value
         * @returns {String|null}
         */
        getCustomAttributeOptionLabel: function (attributeCode, value) {
            var option,
                label,
                options = this.source.get('customAttributes') || {};

            if (options[attributeCode]) {
                option = _.findWhere(options[attributeCode], {
                    value: value
                });

                if (option) {
                    label = option.label;
                }
            } else if (value.file !== null) {
                label = value.file;
            }

            return label;
        },

        /** Set selected customer shipping address  */
        selectAddress: function () {
            checkoutData.setSelectedShippingAddress(this.address().getKey());
            this.applyAddressSelection();
            
            return true;
        },

        /**
         * Edit address.
         */
        editAddress: function () {
            formPopUpState.isVisible(true);
            this.showPopup();
            $('.form-shipping-address').removeClass('shippingAddress_form');
            $('#shipping-new-address-form').removeClass('shippingAddress_formBg');
            this.populateFormWithAddress(this.address());
        },

        /**
         * Update address using AJAX.
         */
        updateAddress: function () {
            var address = this.address();
            var form = $('#co-shipping-form');
            var formData = form.serializeArray();
            var addressId = address.customerAddressId;

            if (!addressId) {
                return;
            }
            formData.push({name: 'address_id', value: addressId});

            $.ajax({
                url: '/home/index/UpdateAddress/id/' + addressId,
                type: 'POST',
                data: $.param(formData),
                showLoader: true,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        location.reload();
                        $('.action-close').click();
                    } else {
                        console.error('Failed to update address:', response.message);
                    }
                }.bind(this),
                error: function (xhr, status, error) {
                    console.error('Failed to update address:', error);
                }
            });
        },

        deleteAddress: function (addressId) {
            var addressItem = $('#address-' + addressId);
            var addressItems = $('.shipping-address-item');

            if (addressItems.length > 1) {
                if (confirm('Are you sure you want to delete this address?')) {
                    var deleteUrl = '/home/index/delete/id/' + addressId;
                    var NewAddress = $('#opc-new-shipping-address');
                    var Popup = $('.new-address-popup');

                    $.ajax({
                        url: deleteUrl,
                        type: 'POST',
                        showLoader: true,
                        dataType: 'json',
                        success: function (response) {
                            if (addressItem.hasClass('selected-item')) {
                                checkoutData.setSelectedShippingAddress(null);

                                if (addressItems.length > 2) {
                                    var firstRemainingAddress = $('.shipping-address-item:first');
                                    var firstAddressId = firstRemainingAddress.attr('id').replace('address-', '');
                                    $('#radio-' + firstAddressId).trigger('click');
                                }
                            }
                            
                            addressItem.remove();
                        },
                        error: function (xhr, status, error) {
                            console.error('AJAX Error:', error);
                        }
                    });
                }
            } else {
                alert('You cannot delete the last address.');
            }
        },

        insertAddress: function () {
            var form = $('#co-shipping-form');
            var formData = form.serializeArray();                       

            $.ajax({
                url: '/home/index/insertAddress',
                type: 'POST',
                data: $.param(formData),
                showLoader: true,
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        localStorage.clear();
                        location.reload();
                        $('.action-close').click();
                    } else {
                        console.error('Failed to Insert address:', response.message);
                    }
                }.bind(this),
                error: function (xhr, status, error) {
                    console.error('Failed to Insert address:', error);
                }
            });
        },

        /**
         * Show popup.
         */
        showPopup: function () {
            $('[data-open-modal="opc-new-shipping-address"]').trigger('click');
        },

        /**
         * Populate form fields with address data.
         */
        populateFormWithAddress: function (address) {
            $('.new-shipping-address-modal .modal-footer').hide();
            $('.action-update-address').show();
            var form = $('#co-shipping-form');
            var formFields = form.find('input, select, textarea');
        
            ko.utils.arrayForEach(formFields, function (field) {

                var fieldName = $(field).attr('name');
                var addressFieldName = fieldName;

                 if (fieldName === 'region_id') {
                    addressFieldName = 'regionId';
                } else if (fieldName === 'country_id') {
                    addressFieldName = 'countryId';
                }
        
                if (address.hasOwnProperty(addressFieldName)) {
                        $(field).val(address[addressFieldName]);
                }               
            });
        
            if (Array.isArray(address.street)) {
                address.street.forEach(function (streetLine, index) {
                    var streetField = form.find(`input[name="street[${index}]"]`);
                    if (streetField.length > 0) {
                        streetField.val(streetLine);
                    } else {
                        var newStreetField = $('<input>')
                            .attr('type', 'text')
                            .attr('name', `street[${index}]`)
                            .val(streetLine)
                            .addClass('input-text');
                        form.append(newStreetField);
                    }
                });
            }
        
            if ($('.action-update-address').length === 0) {
                var addressId = address.customerAddressId;

                if(!addressId){
                    var InsertButton = $('<button/>', {
                        class: 'action primary action-update-address',
                        type: 'button',
                        'data-role': 'action',
                        text: 'Update Address',
                        click: this.insertAddress.bind(this)
                });
                   form.append(InsertButton);
                }else{
                    var updateButton = $('<button/>', {
                    class: 'action primary action-update-address',
                    type: 'button',
                    'data-role': 'action',
                    text: 'Update Address',
                    click: this.updateAddress.bind(this)
                });
                form.append(updateButton);
                }
            }
        }
    });
});