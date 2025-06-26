require(['jquery', 'mage/url'], function ($, urlBuilder) {
    'use strict';

    // Flag to track if a batch row has already been added
    var hasAddedBatchRow = false;

    function getProductType() {
        var $productTypeSelect = $('select[name="product[type]"]');
        var selectedValue = $productTypeSelect.val();
        var selectedText = $productTypeSelect.find('option:selected').text().trim();
        console.log('Product Type Value:', selectedValue);
        console.log('Product Type Text:', selectedText);
        return { value: selectedValue, text: selectedText };
    }

    function handleScheduleProduct() {
        var $priceField = $('input[name="product\\[price\\]"]');
        var $medicineField = $('input[name="product\\[medicine\\]"]');
        var $quantityFieldContainer = $('.admin__field[data-index="quantity_and_stock_status_qty"]');
        var $priceFieldContainer = $('.admin__field[data-index="container_price"]');
        var $ExpiryContainer = $('.admin__field[data-index="expiry"]');
        if ($priceField.length) $priceField.hide();
        if ($medicineField.length) $medicineField.hide();
        if ($quantityFieldContainer.length) $quantityFieldContainer.hide();
        if ($priceFieldContainer.length) $priceFieldContainer.hide();
        if ($ExpiryContainer.length) $ExpiryContainer.hide();

        updatePrescriptionCheck(true);
    }

    function handleNonScheduleProduct() {
        var $priceField = $('input[name="product\\[price\\]"]');
        var $medicineField = $('input[name="product\\[medicine\\]"]');
        var $quantityFieldContainer = $('.admin__field[data-index="quantity_and_stock_status_qty"]');
        var $priceFieldContainer = $('.admin__field[data-index="container_price"]');
        var $ExpiryContainer = $('.admin__field[data-index="expiry"]');

        if ($priceField.length) $priceField.show();
        if ($medicineField.length) $medicineField.show();
        if ($quantityFieldContainer.length) $quantityFieldContainer.show();
        if ($priceFieldContainer.length) $priceFieldContainer.show();
        if ($ExpiryContainer.length) $ExpiryContainer.show();

        updatePrescriptionCheck(false);
    }

    function updatePrescriptionCheck(isSchedule) {
        var $prescriptionCheckSelect = $('select[name="product[prescription_check]"]');
        if ($prescriptionCheckSelect.length) {
            $prescriptionCheckSelect.val(isSchedule ? '37' : '38').change();
            $prescriptionCheckSelect.prop('disabled', true);
        }
    }

    function applyLogicBasedOnProductType() {
        var productType = getProductType();
        console.log('Product Type in applyLogicBasedOnProductType:', productType);

        switch (productType.text) {
            case "SCHEDULE H":
            case "SCHEDULE H1":
            case "SCHEDULE X":
            case "SCHEDULE G":
                handleScheduleProduct();
                break;
            case "NON SCHEDULE":
            default:
                handleNonScheduleProduct();
                break;
        }
    }

    function waitForProductTypeAndApplyLogic(retries = 10, interval = 500) {
        var productType = getProductType();
        if (productType.value && productType.value.trim() !== "") {
            applyLogicBasedOnProductType();
            checkAndAddBatchInformation();
        } else if (retries > 0) {
            console.log(`Waiting for product type value. Retries left: ${retries}`);
            setTimeout(function() {
                waitForProductTypeAndApplyLogic(retries - 1, interval);
            }, interval);
        } else {
            console.log("Failed to get product type value after multiple attempts");
        }
    }

    function checkAndAddBatchInformation() {
        if (hasAddedBatchRow) {
            console.log("Batch row already added, skipping");
            return;
        }

        var $dynamicRows = $('[data-index="custom_descriptions"]');

        if ($dynamicRows.length) {
            var $existingRows = $dynamicRows.find('tr.data-row');

            if ($existingRows.length === 0) {
                var $addButton = $('button[data-index="button_add"]');
                if ($addButton.length === 0) {
                    $addButton = $('.add_batch_info_button');
                }

                if ($addButton.length) {
                    // Click the button to add a new row
                    $addButton.trigger('click');

                    // Wait for the row to be added
                    var checkRowAdded = function() {
                        var $newRows = $dynamicRows.find('tr.data-row');
                        if ($newRows.length > 0) {
                            hasAddedBatchRow = true;
                            console.log("Batch row successfully added");
                        } else {
                            console.warn("Batch row not added yet");
                            setTimeout(checkRowAdded, 100); // Retry after 100ms
                        }
                    };

                    checkRowAdded(); // Start checking
                } else {
                    console.warn("'Add Batch Information' button not found");
                }
            } else {
                hasAddedBatchRow = true;
                console.log("Batch row already exists");
            }
        } else {
            console.warn("Dynamic rows table not found");
        }
    }

    $(document).ready(function () {
        waitForProductTypeAndApplyLogic();

        // Reapply logic when the product type changes
        $(document).on('change', 'select[name="product[type]"]', function () {
            waitForProductTypeAndApplyLogic();
        });

        // Reapply logic after AJAX calls complete
        $(document).ajaxStop(function () {
            waitForProductTypeAndApplyLogic();
        });
    });
});
