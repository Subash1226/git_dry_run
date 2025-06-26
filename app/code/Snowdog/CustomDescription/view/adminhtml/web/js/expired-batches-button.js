define([
    'Magento_Ui/js/form/components/button',
    'mage/url'
], function (Button, urlBuilder) {
    'use strict';

    return Button.extend({
        defaults: {
            elementTmpl: 'ui/form/element/button',
            buttonClasses: 'export_button'
        },

        initialize: function () {
            this._super();
            this.url = this.url || '#';
            return this;
        },

        action: function () {
            window.location.href = urlBuilder.build(this.url);
        }
    });
});
