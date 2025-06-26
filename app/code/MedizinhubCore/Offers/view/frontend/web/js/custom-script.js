require(['jquery'], function($) {
    var medicine_offers = $("#medicine_offers_active");
    var lab_test_offers = $("#lab_test_offers_active");
    var doctor_consult_offers = $("#doctor_consult_offers_active");

    $(medicine_offers).click(function(){
        medicine_offers.addClass("active");
        lab_test_offers.removeClass("active");
        doctor_consult_offers.removeClass("active");
    });

    $(lab_test_offers).click(function(){
        lab_test_offers.addClass("active");
        medicine_offers.removeClass("active");
        doctor_consult_offers.removeClass("active");
    });

    $(doctor_consult_offers).click(function(){
        doctor_consult_offers.addClass("active");
        lab_test_offers.removeClass("active");
        medicine_offers.removeClass("active");
    });
    
    $(document).ready(function() {
        $('.copy-code-btn').on('click', function() {
            const code = $(this).data('code');
            const ruleId = $(this).data('rule-id');
            const $tooltip = $(this).siblings('.copy-tooltip');
            
            const $tempTextArea = $('<textarea>').val(code).appendTo('body');
            $tempTextArea.select();
            document.execCommand('copy');
            $tempTextArea.remove();
            
            $tooltip.show();
            setTimeout(() => {
                $tooltip.hide();
            }, 2000);
        });

        $('.view-offer-details').on('click', function() {
            const ruleId = $(this).data('rule-id');
            
            $.ajax({
                url: 'offers/index/coupondetails',
                type: 'GET',
                data: { rule_id: ruleId },
                dataType: 'json',
                success: function(data) {
                    $('#couponModalTitle').text(data.coupon_titles || 'Coupon Details');
                    $('#couponModalDescription1').text(data.description || 'No description available');
                    $('#couponModalDescription2').text(data.coupon_descriptions || 'No description available');
                    $('#offer-modal').show();
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching coupon details:', error);
                    alert('Failed to load coupon details');
                }
            });
        });

        $('.offer-modal-close, .offer-modal-close-btn').on('click', function() {
            $('#offer-modal').hide();
        });

        $(window).on('click', function(event) {
            if ($(event.target).is('#offer-modal')) {
                $('#offer-modal').hide();
            }
        });
    });
});