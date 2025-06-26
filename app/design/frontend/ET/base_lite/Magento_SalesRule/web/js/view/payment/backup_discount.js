define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_SalesRule/js/action/set-coupon-code',
    'Magento_SalesRule/js/action/cancel-coupon',
    'Magento_SalesRule/js/model/coupon',
    'mage/collapsible',
    'mage/translate'
], function ($, ko, Component, quote, setCouponCodeAction, cancelCouponAction, coupon, collapsible, $t) {
    'use strict';

    var totals = quote.getTotals(),
        couponCode = coupon.getCouponCode(),
        isApplied = coupon.getIsApplied();

    return Component.extend({
        defaults: {
            template: 'Magento_SalesRule/payment/discount'
        },
        couponCode: couponCode,
        isApplied: isApplied,
        isLoading: ko.observable(false),
        discountAmount: ko.observable(0),
        autoAppliedCode: ko.observable(''),
        showDiscountInput: ko.observable(true),
        previousDiscountCode: ko.observable(''),
        previousDiscountAmount: ko.observable(0),
        couponError: ko.observable(''),
        appliedCouponName: ko.observable(''),
        modalIsOpen: ko.observable(false),
        availableCoupons: ko.observableArray([]),


        /**
         * Initialize component with enhanced functionality
         */
        initialize: function () {
            this._super();
            var baseUrl = window.location.origin + '/rest/';

            this.fetchAdminToken(baseUrl).then(() => {
                this.fetchAvailableCoupons(baseUrl);
            }).catch((error) => {
                console.error('Failed to fetch admin token:', error);
                this.couponError($t('Unable to retrieve available coupons.'));
            });

            // Initialize discount amount from totals
            if (totals()) {
                this.updateDiscountFromTotals(totals());
            }

            // Subscribe to quote totals changes
            quote.getTotals().subscribe(function (newTotals) {
                if (newTotals) {
                    this.updateDiscountFromTotals(newTotals);
                }
            }.bind(this));

            // Enhanced input state management
            this.isApplied.subscribe(function(newValue) {
                var discountInput = document.getElementById('discount-code');
                if (discountInput) {
                    this.showDiscountInput(!newValue);
                    discountInput.disabled = newValue;
                }

                // Reset error state when coupon is applied or cancelled
                if (!newValue) {
                    this.couponError('');
                }
            }.bind(this));

            // Add input validation listener
            this.couponCode.subscribe(function(newValue) {
                // Clear previous error when user starts typing
                if (newValue) {
                    this.couponError('');
                }
            }.bind(this));
            this.initializeModal();

            if (totals() && totals()['coupon_code']) {
                this.couponCode(totals()['coupon_code']);
                this.isApplied(true);
                
                // Fetch and display the applied coupon details
                const appliedCoupon = this.availableCoupons().find(
                    coupon => coupon.code === totals()['coupon_code']
                );
                
                if (appliedCoupon) {
                    this.appliedCouponName(appliedCoupon.couponName);
                }
            }

            return this;
        },

        /**
         * Initialize modal functionality
         */
        initializeModal: function() {
            var self = this;

            // Modal open method
            this.openModal = function() {
                // Use the observable method to set modal state
                self.modalIsOpen(true);
                $('#apcu-coupon-modal').addClass('show');
                $('#apcu-coupon-modal .apcu-modal-container').addClass('show');
            };

            // Modal close method
            this.closeModal = function() {
                $('#apcu-coupon-modal .apcu-modal-container').removeClass('show');
                setTimeout(function() {
                    $('#apcu-coupon-modal').removeClass('show');
                    // Use the observable method to set modal state
                    self.modalIsOpen(false);
                }, 300);
            };

            // Bind modal events
            $(document).on('click', '.coupon-popup', this.openModal);
            $(document).on('click', '.apcu-modal-close', this.closeModal);
            $(document).on('click', '#apcu-coupon-modal', function(event) {
                if (event.target === this) {
                    self.closeModal();
                }
            });

            // Handle form submission within modal
            $(document).on('submit', '#apcu-coupon-modal #discount-form', function(e) {
                e.preventDefault();
                self.apply();
                self.closeModal();
            });
        },

        // Optional: Add a method to manually toggle modal if needed
        toggleModal: function() {
            if (this.modalIsOpen()) {
                this.closeModal();
            } else {
                this.openModal();
            }
        },

        /**
         * Update discount amount from totals
         * @param {Object} totalsData
         */
        updateDiscountFromTotals: function(totalsData) {
            if (totalsData) {
                // Update discount amount
                if (totalsData['discount_amount']) {
                    this.discountAmount(Math.abs(totalsData['discount_amount']));
                }
                
                // Sync coupon state with totals
                if (totalsData['coupon_code']) {
                    this.couponCode(totalsData['coupon_code']);
                    this.isApplied(true);
                    
                    // Find and set coupon name
                    const appliedCoupon = this.availableCoupons().find(
                        coupon => coupon.code === totalsData['coupon_code']
                    );
                    
                    if (appliedCoupon) {
                        this.appliedCouponName(appliedCoupon.couponName);
                    }
                }
            }
        },

        /**
         * Get available coupons with additional filtering
         * @returns {Array} Filtered list of available coupons
         */
        getAvailableCoupons: function() {
            var subtotal = parseFloat(quote.getTotals()()['subtotal'] || 0);
            return this.availableCoupons();
        },

        /**
         * Apply a coupon from the available list
         * @param {Object} selectedCoupon Coupon object to apply
         */
        applyAvailableCoupon: function(selectedCoupon) {
            this.couponCode(selectedCoupon.code);
            this.appliedCouponName(selectedCoupon.couponName);
            this.apply();
        },

        /**
         * Apply coupon code with comprehensive validation
         */
        apply: function () {
            // Clear previous error
            this.couponError('');

            if (!this.couponCode()) {
                this.couponError($t('Please enter a coupon code.'));
                return;
            }

            if (this.validate()) {
                this.isLoading(true);

                const availableCoupon = this.availableCoupons().find(
                    coupon => coupon.code === this.couponCode()
                );
        
                // Set the coupon name if found, otherwise clear it
                if (availableCoupon) {
                    this.appliedCouponName(availableCoupon.couponName);
                } else {
                    this.appliedCouponName('');
                }

                // Store previous coupon details before applying new one
                if (this.isApplied()) {
                    this.previousDiscountCode(this.couponCode());
                    this.previousDiscountAmount(this.discountAmount());
                }

                setCouponCodeAction(this.couponCode(), this.isApplied)
                    .done(function () {
                        var currentTotals = quote.getTotals()();
                        if (currentTotals && currentTotals['discount_amount']) {
                            this.discountAmount(Math.abs(currentTotals['discount_amount']));
                        }
                    }.bind(this))
                    .fail(function(error) {
                        this.couponError($t('Invalid coupon code. ') + (error.message || $t('Please try again.')));
                        this.appliedCouponName('');
                    }.bind(this))
                    .always(function () {
                        this.isLoading(false);
                    }.bind(this));
            }
        },

        /**
         * Cancel coupon code with improved reset
         */
        cancel: function () {
            this.isLoading(true);

            // Store current coupon details before cancelling
            this.previousDiscountCode(this.couponCode());
            this.previousDiscountAmount(this.discountAmount());

            cancelCouponAction(this.isApplied)
                .done(function () {
                    this.couponCode('');
                    this.discountAmount(0);
                    this.isApplied(false);
                    this.showDiscountInput(true);
                    this.couponError('');
                    this.appliedCouponName('');
                }.bind(this))
                .fail(function(error) {
                    this.couponError($t('Failed to cancel coupon: ') + (error.message || $t('Unknown error')));
                }.bind(this))
                .always(function () {
                    this.isLoading(false);
                }.bind(this));
        },

        /**
         * Validate coupon form with enhanced error handling
         * @returns {Boolean}
         */
        validate: function () {
            var form = '#discount-form';
            var isValid = $(form).validation() && $(form).validation('isValid');

            if (!isValid) {
                this.couponError($t('Please enter a valid coupon code.'));
            }

            return isValid;
        },

        /**
         * Get formatted discount amount
         * @returns {String}
         */
        getFormattedDiscount: function () {
            return this.getFormattedPrice(this.discountAmount());
        },

        /**
         * Get formatted previous discount amount
         * @returns {String}
         */
        getFormattedPreviousDiscount: function () {
            return this.getFormattedPrice(this.previousDiscountAmount());
        },

        /**
         * Format price with currency symbol
         * @param {Number} amount
         * @returns {String}
         */
        getFormattedPrice: function (amount) {
            return '₹' + parseFloat(amount).toFixed(2);
        },
        /**
         * Fetch admin token for API authentication
         * @returns {Promise}
         */
        fetchAdminToken: function(baseUrl) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: baseUrl + 'all/V1/integration/admin/token',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        username: '',
                        password: ''
                    }),
                    success: (token) => {
                        this.adminToken = token;
                        resolve(token);
                    },
                    error: (xhr, status, error) => {
                        reject(error);
                    }
                });
            });
        },

        fetchAvailableCoupons: function(baseUrl) {
            if (!this.adminToken) {
                console.error('No admin token available');
                return;
            }
        
            // First, fetch the coupon codes and rule IDs
            $.ajax({
                url: baseUrl + 'V1/coupons/search?searchCriteria=all',
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + this.adminToken
                },
                success: (couponResponse) => {
                    // Store coupon codes and their corresponding rule IDs
                    const couponDetails = (couponResponse.items || []).map(coupon => ({
                        code: coupon.code,
                        ruleId: coupon.rule_id
                    }));
        
                    // Fetch details for each coupon rule
                    const apiCoupons = [];
                    const fetchRulePromises = [];
        
                    couponDetails.forEach(detail => {
                        fetchRulePromises.push(
                            this.fetchSalesRuleDetails(detail.ruleId)
                                .then(ruleInfo => {
                                    apiCoupons.push({
                                        code: detail.code,
                                        couponName: ruleInfo.name,
                                        description: ruleInfo.description
                                    });
                                })
                                .catch(error => {
                                    console.error(`Failed to fetch rule ${detail.ruleId}:`, error);
                                })
                        );
                    });
        
                    // Wait for all rule details to be fetched
                    Promise.all(fetchRulePromises)
                        .then(() => {
                            this.availableCoupons(apiCoupons);
                        })
                        .catch(error => {
                            console.error('Failed to fetch all rule details:', error);
                            this.couponError($t('Unable to retrieve coupon details.'));
                        });
                },
                error: (xhr, status, error) => {
                    console.error('Failed to fetch coupons:', error);
                    this.couponError($t('Unable to retrieve available coupons.'));
                }
            });
        },
        
        /**
         * Fetch sales rule details for a specific rule ID
         * @param {Number} ruleId 
         * @returns {Promise}
         */
        fetchSalesRuleDetails: function(ruleId) {
            var baseUrl = window.location.origin + '/rest/'; 
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: baseUrl + `V1/salesRules/${ruleId}`,
                    method: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + this.adminToken
                    },
                    success: (ruleInfo) => {
                        resolve(ruleInfo);
                    },
                    error: (xhr, status, error) => {
                        reject(error);
                    }
                });
            });
        },
    });
});