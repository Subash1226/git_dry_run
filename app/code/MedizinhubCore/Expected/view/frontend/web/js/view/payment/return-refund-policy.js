define([
    'uiComponent',
    'ko',
    'jquery',
], function (Component, ko, $) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'MedizinhubCore_Expected/return-refund-policy'
        },

        initialize: function () {
            var self = this;
            this._super();
        }
    });
});
