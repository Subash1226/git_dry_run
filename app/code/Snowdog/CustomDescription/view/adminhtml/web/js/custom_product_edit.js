require(['jquery', 'mage/url', 'mage/cookies'], function ($, urlBuilder) {
    'use strict';

    var hasAddedBatchRow = false;

    function getAttributeSetId() {
        var $attributeSetFieldContainer = $('.admin__field[data-index="attribute_set_id"]');
        var selectedValue = $attributeSetFieldContainer.find('[data-role="selected-option"]').text().trim();
        console.log('Attribute Set Value:', selectedValue);
        return selectedValue;
    }

    function handleValue10() {
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
    }

    function handleValueMedicine() {
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
    }

    function applyLogicBasedOnAttributeSet() {
        var attributeSetValue = getAttributeSetId().trim();
        console.log('Attribute Set Value in applyLogicBasedOnAttributeSet:', attributeSetValue);

        if (attributeSetValue === "10") {
            handleValue10();
        } else if (attributeSetValue === "Medicine") {
            handleValueMedicine();
        } else {
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
        }
    }

    function waitForAttributeSetAndApplyLogic(retries = 10, interval = 500) {
        var attributeSetValue = getAttributeSetId();
        if (attributeSetValue && attributeSetValue.trim() !== "") {
            applyLogicBasedOnAttributeSet();
        } else if (retries > 0) {
            console.log(`Waiting for attribute set value. Retries left: ${retries}`);
            setTimeout(function () {
                waitForAttributeSetAndApplyLogic(retries - 1, interval);
            }, interval);
        } else {
            console.log("Failed to get attribute set value after multiple attempts");
        }
    }

    require(['jquery', 'mage/url', 'mage/cookies'], function ($, urlBuilder) {
    'use strict';

    var hasAddedBatchRow = false;

    function getAttributeSetId() {
        var $attributeSetFieldContainer = $('.admin__field[data-index="attribute_set_id"]');
        var selectedValue = $attributeSetFieldContainer.find('[data-role="selected-option"]').text().trim();
        console.log('Attribute Set Value:', selectedValue);
        return selectedValue;
    }

    function handleValue10() {
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
    }

    function handleValueMedicine() {
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
    }

    function applyLogicBasedOnAttributeSet() {
        var attributeSetValue = getAttributeSetId().trim();
        console.log('Attribute Set Value in applyLogicBasedOnAttributeSet:', attributeSetValue);

        if (attributeSetValue === "10") {
            handleValue10();
        } else if (attributeSetValue === "Medicine") {
            handleValueMedicine();
        } else {
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
        }
    }

    function waitForAttributeSetAndApplyLogic(retries = 10, interval = 500) {
        var attributeSetValue = getAttributeSetId();
        if (attributeSetValue && attributeSetValue.trim() !== "") {
            applyLogicBasedOnAttributeSet();
        } else if (retries > 0) {
            console.log(`Waiting for attribute set value. Retries left: ${retries}`);
            setTimeout(function () {
                waitForAttributeSetAndApplyLogic(retries - 1, interval);
            }, interval);
        } else {
            console.log("Failed to get attribute set value after multiple attempts");
        }
    }

    function addLinksIfNotExist() {
        var $contentsContainer = $('.admin__field[data-index="quantity_contents"]');
        if ($contentsContainer.length) {
            if ($contentsContainer.find('.Add-contents-link').length === 0) {
                var addContentsUrl = urlBuilder.build('/admin/catalog/product_attribute/edit/attribute_id/161');
                var $contentsLink = $('<a>', {
                    text: 'Add Contents',
                    href: addContentsUrl,
                    class: 'Add-contents-link',
                    target: '_blank',
                    style: 'padding-left: 15px;'
                });
                $contentsContainer.find('select').after($contentsLink);
            }
        } else {
            console.warn('Contents input element not found.');
        }

        var $categoryContainer = $('.admin__field[data-index="category_check"]');
        var $HsnContainer = $('.admin__field[data-index="hsn_code"]');

        if ($categoryContainer.length === 0) {
            console.warn("Category container not found.");
            return;
        }

        if ($HsnContainer.length === 0) {
            console.warn("HSN container not found.");
            return;
        }

        if ($categoryContainer.find('.Add-category-link').length === 0) {
            var addCategoryUrl = urlBuilder.build('/admin/catalog/product_attribute/edit/attribute_id/158');
            var $link = $('<a>', {
                text: 'Add Category',
                href: addCategoryUrl,
                class: 'Add-category-link',
                target: '_blank',
                style: 'padding: 0 15px;'
            });
            $categoryContainer.find('select').after($link);
        }

        if ($HsnContainer.find('.Add-hsn-link').length === 0) {
            var addHsnUrl = urlBuilder.build('/admin/catalog/product_attribute/edit/attribute_id/150');
            var $Hsnlink = $('<a>', {
                text: 'Add HSN',
                href: addHsnUrl,
                class: 'Add-hsn-link',
                target: '_blank',
                style: 'padding: 0 15px;'
            });
            $HsnContainer.find('select').after($Hsnlink);
        }
    }

    $(document).ready(function () {
        var formKey = $.mage.cookies.get('form_key');
        waitForAttributeSetAndApplyLogic();

        $(document).on('change', 'input[name="attribute_set"]', function () {
            waitForAttributeSetAndApplyLogic();
        });

        $(document).ajaxStop(function () {
            waitForAttributeSetAndApplyLogic();
        });

        $(document).ajaxStop(function () {
            addLinksIfNotExist();
        });
    });
});

function setupPageLayout() {
    var $pageLayoutSelect = $('select[name="product[page_layout]"]');
    if ($pageLayoutSelect.length) {
        $pageLayoutSelect.val('');
        $pageLayoutSelect.prop('disabled', true);
        $pageLayoutSelect.closest('.admin__field').addClass('disabled');
    }
}

    function addLinksIfNotExist() {
        var $contentsContainer = $('.admin__field[data-index="quantity_contents"]');
        if ($contentsContainer.length) {
            if ($contentsContainer.find('.Add-contents-link').length === 0) {
                var addContentsUrl = urlBuilder.build('/admin/catalog/product_attribute/edit/attribute_id/161');
                var $contentsLink = $('<a>', {
                    text: 'Add Contents',
                    href: addContentsUrl,
                    class: 'Add-contents-link',
                    target: '_blank',
                    style: 'padding-left: 15px;'
                });
                $contentsContainer.find('select').after($contentsLink);
            }
        } else {
            console.warn('Contents input element not found.');
        }

        var $categoryContainer = $('.admin__field[data-index="category_check"]');
        var $HsnContainer = $('.admin__field[data-index="hsn_code"]');

        if ($categoryContainer.length === 0) {
            console.warn("Category container not found.");
            return;
        }

        if ($HsnContainer.length === 0) {
            console.warn("HSN container not found.");
            return;
        }

        if ($categoryContainer.find('.Add-category-link').length === 0) {
            var addCategoryUrl = urlBuilder.build('/admin/catalog/product_attribute/edit/attribute_id/158');
            var $link = $('<a>', {
                text: 'Add Category',
                href: addCategoryUrl,
                class: 'Add-category-link',
                target: '_blank',
                style: 'padding: 0 15px;'
            });
            $categoryContainer.find('select').after($link);
        }

        if ($HsnContainer.find('.Add-hsn-link').length === 0) {
            var addHsnUrl = urlBuilder.build('/admin/catalog/product_attribute/edit/attribute_id/150');
            var $Hsnlink = $('<a>', {
                text: 'Add HSN',
                href: addHsnUrl,
                class: 'Add-hsn-link',
                target: '_blank',
                style: 'padding: 0 15px;'
            });
            $HsnContainer.find('select').after($Hsnlink);
        }
    }

    $(document).ready(function () {
        var formKey = $.mage.cookies.get('form_key');
        waitForAttributeSetAndApplyLogic();
        setupPageLayout();  // Ensure page layout is set after AJAX calls

        $(document).on('change', 'input[name="attribute_set"]', function () {
            waitForAttributeSetAndApplyLogic();
        });

        $(document).ajaxStop(function () {
            waitForAttributeSetAndApplyLogic();
        });

        $(document).ajaxStop(function () {
            addLinksIfNotExist();
            setupPageLayout();  // Ensure page layout is set after AJAX calls
        });
    });
});