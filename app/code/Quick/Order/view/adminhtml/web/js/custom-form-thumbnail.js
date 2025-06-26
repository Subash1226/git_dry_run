define(["uiComponent", "Magento_Ui/js/grid/columns/column"], function (
    Component,
    Column
) {
    "use strict";

    return Column.extend({
        defaults: {
            bodyTmpl: "Quick_Order/custom-form-thumbnail",
        },
    });
});
