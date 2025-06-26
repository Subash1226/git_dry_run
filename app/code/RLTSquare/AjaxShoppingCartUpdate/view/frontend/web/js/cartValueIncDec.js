define(['jquery'], function ($) {
    "use strict";

    function ajaxCartUpdate() {
        $('.main').on("click", '.alo_qty_dec', function () {
            let input = $(this).parent().find('input');
            let value = parseInt(input.val());
            if (value) {
                input.val(value - 1);
            }
        });

        $('.main').on("click", '.alo_qty_inc', function () {
            let input = $(this).parent().find('input');
            let value = parseInt(input.val());
            input.val(value + 1);
        });
    }

    $('.main').on('click', '.action-delete', function (e) {
        e.preventDefault();
        if (confirm('Do you want to remove this product?')) {
            $(this).closest('form').submit();
        } else {
            return false; 
        }
    });

    $('.loginCheckout').on('click', function () {
        UserLoginCart();
    });
    ajaxCartUpdate();
});
