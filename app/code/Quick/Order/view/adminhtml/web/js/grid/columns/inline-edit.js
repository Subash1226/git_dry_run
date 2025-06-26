define(["Magento_Ui/js/grid/columns/column"], function (Column) {
    "use strict";

    return Column.extend({
        defaults: {
            bodyTmpl: "ui/grid/cells/html",
            editable: true,
        },

        /**
         * Get cell edit value
         *
         * @param {Object} record
         * @returns {*}
         */
        getEditValue: function (record) {
            return record[this.index];
        },

        /**
         * Save cell value
         *
         * @param {Object} record
         * @param {*} value
         */
        saveEdit: function (record, value) {
            record[this.index] = value;
        },
    });
});
