define(["Magento_Ui/js/grid/columns/column"], function (Column) {
    "use strict";

    return Column.extend({
        defaults: {
            bodyTmpl: "ui/grid/cells/html",
            linkUrl: "${ $.$data.customer_edit_url }",
            customerName: "${ $.$data.customer_name }",
            fieldClass: {
                "data-grid-cell-content": true,
                "actions-visibility": true,
            },
        },

        getLink: function (row) {
            return this.linkUrl.replace("%customer_id%", row.customer_id);
        },

        getLabel: function (row) {
            return this.customerName;
        },

        getHref: function (row) {
            return this.getLink(row);
        },
    });
});
