define([
    'jquery',
    'ko',
    'Magento_Ui/js/form/element/abstract',
    'mage/calendar'
], function ($, ko, Component) {
    'use strict';

    return Component.extend({
        initialize: function () {
            this._super();
            var prevValue = window.checkoutConfig.quoteData.delivery_date;
            var defaultValue = window.checkoutConfig.shipping.delivery_date.default_delivery_date;
            var disabled = window.checkoutConfig.shipping.delivery_date.disabled;
            var blackout = window.checkoutConfig.shipping.delivery_date.blackout;
            var noday = window.checkoutConfig.shipping.delivery_date.noday;
            var hourMin = parseInt(window.checkoutConfig.shipping.delivery_date.hourMin);
            var hourMax = parseInt(window.checkoutConfig.shipping.delivery_date.hourMax);
            var format = window.checkoutConfig.shipping.delivery_date.format;
            if(!format) {
                format = 'yy-mm-dd';
            }
            var disabledDay = disabled.split(",").map(function(item) {
                return parseInt(item, 10);
            });

            ko.bindingHandlers.datetimepicker = {
                init: function (element, valueAccessor, allBindingsAccessor) {
                    var $el = $(element);

                    // Get current date and time
                    var currentDate = new Date();
                    var currentHour = currentDate.getHours();
                    var currentMinute = currentDate.getMinutes();

                    // Get pincode from sessionStorage
                    var pincode = sessionStorage.getItem('pincode_api');

                    // Allowed pincodes
                    var allowedPincodes = ['600017', '600044'];

                    var options = {
                        minDate: 0,
                        dateFormat: format,
                        hourMin: hourMin,
                        hourMax: hourMax,
                        beforeShowDay: function(date) {
                            var string = $.datepicker.formatDate('yy-mm-dd', date);
                            var day = date.getDay();
                            var date_obj = [];

                            function arraySearch(arr,val) {
                                for (var i=0; i<arr.length; i++)
                                    if (arr[i].date === val)
                                        return arr[i].content;
                                return false;
                            }

                            function arrayTooltipClass(arr,val) {
                                for (var i=0; i<arr.length; i++)
                                    if (arr[i].date === val)
                                        return 'redblackday';
                                return 'redday';
                            }

                            // Check if the selected date is today
                            var isToday = $.datepicker.formatDate('yy-mm-dd', currentDate) === string;

                            for(var i = 0; i < blackout.length; i++) {
                                if(blackout[i].date === string) {
                                    date_obj.push(blackout[i].date);
                                }
                            }

                            // Delivery logic based on pincode and time
                            if (allowedPincodes.includes(pincode)) {
                                // If order is before 12:00 PM, allow same-day delivery
                                if (isToday && currentHour < 12) {
                                    return [true];
                                }
                                // If order is after 12:00 PM, next day delivery
                                else if (isToday && currentHour >= 12) {
                                    // Calculate next day
                                    var nextDate = new Date(currentDate);
                                    nextDate.setDate(currentDate.getDate() + 1);
                                    var nextDateString = $.datepicker.formatDate('yy-mm-dd', nextDate);

                                    if (string === nextDateString) {
                                        return [true];
                                    }
                                }
                            }

                            // If pincode doesn't match or other conditions
                            // Default to 3rd day from current date
                            var thirdDate = new Date(currentDate);
                            thirdDate.setDate(currentDate.getDate() + 2);
                            var thirdDateString = $.datepicker.formatDate('yy-mm-dd', thirdDate);

                            // Disable conditions
                            if(date_obj.indexOf(string) !== -1 ||
                               disabledDay.indexOf(day) > -1 ||
                               (isToday && (currentHour > hourMax ||
                                (currentHour === hourMax && currentMinute > 0))) ||
                               (string !== thirdDateString)) {
                                return [false, arrayTooltipClass(blackout,string), arraySearch(blackout,string)];
                            }

                            return [true];
                        }
                    };

                    $el.datetimepicker(options);

                    // Set default date based on pincode and time logic
                    var defaultDate = new Date(currentDate);
                    if (allowedPincodes.includes(pincode)) {
                        if (currentHour < 12) {
                            // Same day if before 12 PM
                            defaultDate = currentDate;
                        } else {
                            // Next day if after 12 PM
                            defaultDate.setDate(currentDate.getDate() + 1);
                        }
                    } else {
                        // Third day for non-matching pincodes
                        defaultDate.setDate(currentDate.getDate() + 2);
                    }

                    $el.datepicker("setDate", defaultDate);

                    var writable = valueAccessor();
                    if (!ko.isObservable(writable)) {
                        var propWriters = allBindingsAccessor()._ko_property_writers;
                        if (propWriters && propWriters.datetimepicker) {
                            writable = propWriters.datetimepicker;
                        } else {
                            return;
                        }
                    }
                    writable($(element).datetimepicker("getDate"));
                },
                update: function (element, valueAccessor) {
                    var widget = $(element).data("DateTimePicker");
                    if (widget) {
                        var date = ko.utils.unwrapObservable(valueAccessor());
                        widget.date(date);
                    }
                }
            };

            return this;
        }
    });
});
