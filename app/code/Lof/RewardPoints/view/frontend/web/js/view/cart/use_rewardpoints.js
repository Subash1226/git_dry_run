define([
    'jquery',
    'ko',
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/action/get-payment-information'
], function ($, ko, Component, quote, totals, getPaymentInformationAction) {
    'use strict';

    var isLoggedIn = ko.observable(window.isCustomerLoggedIn);
    var showOnShoppingCart = window.checkoutConfig.rewardpoints.showonshoppingcart;
    var showOnCheckoutPage = window.checkoutConfig.rewardpoints.showoncheckoutpage;
    var ajaxApplyUrl = window.checkoutConfig.rewardpoints.ajaxurl;
    var pointsLabel = window.checkoutConfig.rewardpoints.pointslabel;
    var rewardpoints = window.checkoutConfig.rewardpoints;
    
    var originalAvailablePoints = parseFloat(window.checkoutConfig.rewardpoints.avaiblepoints);

    var originalAvailablePoints = parseFloat(window.checkoutConfig.rewardpoints.avaiblepoints);
    var maxAllowedPercentage = 0.2; // 20%
    var maxApplicablePoints = Math.floor(originalAvailablePoints * maxAllowedPercentage);
    
    var defaultSpendPoints = 0;
    if (window.checkoutConfig.rewardpoints.spendpoints !== undefined && window.checkoutConfig.rewardpoints.spendpoints.value) {
        defaultSpendPoints = parseFloat(rewardpoints.spendpoints.value);
    }

    var spendingRules = window.checkoutConfig.rewardpoints.rules;
    var currentRule = null;

    if (spendingRules && spendingRules.length > 0) {
        currentRule = spendingRules[spendingRules.length - 1];
    } else {
        currentRule = {
            id: 0,
            name: "Default",
            step: 1,
            min: 0,
            max: maxApplicablePoints,
            discount: 1,
            value: 0
        };
    }

    var component = Component.extend({
        defaults: {
            template: 'Lof_RewardPoints/cart/use_rewardpoints'
        },
        isLoggedIn: isLoggedIn,
        pointsLabel: ko.observable(pointsLabel),
        useRewardPoints: ko.observable(defaultSpendPoints > 0),
        availablePoints: ko.observable(originalAvailablePoints),
        maxApplicablePoints: ko.observable(maxApplicablePoints),
        isPointsApplied: ko.observable(defaultSpendPoints > 0),
        appliedPoints: ko.observable(defaultSpendPoints > 0 ? defaultSpendPoints : 0),
        originalPoints: ko.observable(originalAvailablePoints), // Store original points
        
        isShowRewardpoints: function() {
            return this.isLoggedIn && (originalAvailablePoints > 0) && typeof(rewardpoints) != "undefined" &&
                (showOnShoppingCart != 0) && (showOnCheckoutPage != 0);
        },

        initCollapsible: function() {
            var self = this;
            
            setTimeout(function() {
                jQuery(document).off('click', '.lrw-rp-title');
                
                jQuery('#block-lrwuserpoints-summary').on('click', function(event) {
                    event.stopPropagation();
                });
                
                jQuery(document).on('click', '.lrw-rp-title', function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    var collapsible = jQuery('#block-lrwuserpoints');
                    
                    if (!collapsible.hasClass('active')) {
                        collapsible.collapsible('deactivate');
                        jQuery('.lrw-rp-title').removeClass('active'); 
                    } else {
                        collapsible.collapsible('activate');
                        jQuery('.lrw-rp-title').addClass('active');
                    }
                });
                
                if (self.isPointsApplied()) {
                    jQuery('.lrw-rp-title').addClass('lrw-rp-title-applied');
                }
            }, 500);
            
            return this;
        },

        toggleRewardPoints: function() {
            var self = this;
            var usePoints = this.useRewardPoints();
            
            if (!usePoints) {
                this.availablePoints(this.originalPoints());
                var resetMax = Math.floor(this.originalPoints() * maxAllowedPercentage);
                this.maxApplicablePoints(resetMax);
                this.appliedPoints(0);
                this.isPointsApplied(false);
            } else {
                var pointsToApply = Math.floor(this.availablePoints() * maxAllowedPercentage);
                this.maxApplicablePoints(pointsToApply);
                this.appliedPoints(pointsToApply);
                this.isPointsApplied(true);
            }
            
            this.applyPoints(usePoints ? this.maxApplicablePoints() : 0);
            
            if (usePoints) {
                jQuery('.lrw-rp-title').addClass('lrw-rp-title-applied');
            } else {
                jQuery('.lrw-rp-title').removeClass('lrw-rp-title-applied');
            }
            
            var collapsible = jQuery('#block-lrwuserpoints');
            
            if (collapsible.hasClass('active')) {
                collapsible.collapsible('deactivate');
                jQuery('.lrw-rp-title').removeClass('active');
            }
            
            return true;
        },
        
        applyPoints: function(pointsToApply) {
            var self = this;            
            var discount = 0;
            if (currentRule && currentRule.discount) {
                var step = currentRule.step || 1;
                discount = parseFloat((pointsToApply / step) * currentRule.discount);
                if (isNaN(discount)) {
                    discount = 0;
                }
            }
            
            if (ajaxApplyUrl) {
                $.ajax({
                    url: ajaxApplyUrl,
                    data: {
                        isAjax: true,
                        spendpoints: pointsToApply,
                        discount: discount,
                        rule: currentRule.id,
                        stepdiscount: currentRule.step || 1,
                        quote: currentRule.quote || '',
                        rulemin: currentRule.min || 0,
                        rulemax: currentRule.max || maxApplicablePoints
                    },
                    type: 'post',
                    dataType: 'json',
                    beforeSend: function() {
                        totals.isLoading(true);
                    }
                }).done(function(response) {
                    var deferred = $.Deferred();
                    getPaymentInformationAction(deferred);
                    
                    var quoteTotals = ko.toJS(quote.getTotals());
                    quote.rewardpoints = response.rewardpoints;
                    
                    if (response.rewardpoints && response.rewardpoints.avaiblepoints !== undefined) {
                        self.availablePoints(parseFloat(response.rewardpoints.avaiblepoints));
                    } else if (pointsToApply > 0) {
                        var newAvailable = self.originalPoints() - pointsToApply;
                        self.availablePoints(newAvailable);
                    } else {
                        self.availablePoints(self.originalPoints());
                    }
                    
                    if (!self.useRewardPoints()) {
                        self.maxApplicablePoints(Math.floor(self.originalPoints() * maxAllowedPercentage));
                    } else {
                        self.maxApplicablePoints(Math.floor(self.availablePoints() * maxAllowedPercentage));
                    }
                    
                    if (response.total_segments && quoteTotals && quoteTotals.total_segments) {
                        var totalSegments = quoteTotals.total_segments;
                        var rewardSegments = response.total_segments;
                        
                        for (var i = 0; i < rewardSegments.length; i++) {
                            for (var x = 0; x < totalSegments.length; x++) {
                                if (rewardSegments[i].code == totalSegments[x].code) {
                                    totalSegments[x].value = rewardSegments[i].value;
                                }
                            }
                        }
                        
                        quoteTotals.total_segments = totalSegments;
                        quote.setTotals(quoteTotals);
                    }
                    
                    totals.isLoading(false);
                    
                    jQuery('.lrw-points-avail-span').text(self.availablePoints());
                    
                    setTimeout(function() {
                        jQuery(document).off('click', '.lrw-rp-title');
                        jQuery(document).on('click', '.lrw-rp-title', function(event) {
                            event.preventDefault();
                            event.stopPropagation();
                            
                            var collapsible = jQuery('#block-lrwuserpoints');
                            
                            if (collapsible.hasClass('active')) {
                                collapsible.collapsible('deactivate');
                                jQuery('.lrw-rp-title').removeClass('active');
                            } else {
                                collapsible.collapsible('activate');
                                jQuery('.lrw-rp-title').addClass('active');
                            }
                        });
                    }, 300);
                    
                }).fail(function(error) {
                    console.log("Error applying reward points: ", error);
                    totals.isLoading(false);
                });
            }
        }
    });
    
    return component;
});