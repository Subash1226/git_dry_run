require([
    'jquery'
], function($) {
    function validateForm(formData) {
        if (!/^[a-zA-Z\s]+$/.test(formData.customer_name)) {
            alert('Customer Name should contain only alphabets and spaces');
            return false;
        }

        if (!/^\d{10}$/.test(formData.customer_phone)) {
            alert('Phone number must be exactly 10 digits');
            return false;
        }

        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (formData.customer_email && !emailRegex.test(formData.customer_email)) {
            alert('Please enter a valid email address');
            return false;
        }

        var totalAmount = parseFloat(formData.total_amount);
        if (isNaN(totalAmount) || totalAmount <= 0) {
            alert('Total amount must be a positive number');
            return false;
        }

        var cashAmount = parseFloat(formData.cash_amount || '0');
        if (isNaN(cashAmount) || cashAmount < 0 || cashAmount > totalAmount) {
            alert('Cash amount must be a non-negative number and not exceed total amount');
            return false;
        }

        return true;
    }

    function calculateOnlineAmount() {
        var totalAmount = parseFloat($('#custompayment_total_amount').val() || '0');
        var cashAmount = parseFloat($('#custompayment_cash_amount').val() || '0');
        var onlineAmount = totalAmount - cashAmount;
        onlineAmount = Math.max(0, onlineAmount);
        $('#custompayment_online_amount').val(onlineAmount.toFixed(2));
    }

    $(document).on('input', '#custompayment_cash_amount', function() {
        calculateOnlineAmount();
    });

    function loadRazorpayScript(callback) {
        if (window.Razorpay) {
            callback();
            return;
        }

        var script = document.createElement('script');
        script.src = 'https://checkout.razorpay.com/v1/checkout.js';
        script.onload = callback;
        script.onerror = function() {
            console.error('Failed to load Razorpay script');
            alert('Payment gateway loading failed');
            $('body').trigger('processStop');
        };
        document.head.appendChild(script);
    }

    function initiateRazorpayPayment(paymentDetails) {
        if (typeof Razorpay === 'undefined') {
            console.error('Razorpay is not loaded');
            alert('Payment gateway is not available');
            $('body').trigger('processStop');
            return;
        }

        var options = {
            amount: paymentDetails.amount,
            currency: paymentDetails.currency,
            name: 'MedizinHub',
            description: 'Payment Transaction',
            order_id: paymentDetails.order_id,
            handler: function(response) {
                $.ajax({
                    url: '/admin/custominvoice/payment/verify', 
                    type: 'POST',
                    data: {
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_signature: response.razorpay_signature
                    },
                    success: function(verificationResponse) {
                        if (verificationResponse.success) {
                            savePaymentDetails(paymentDetails, response);
                        } else {
                            alert('Payment Verification Failed');
                            $('body').trigger('processStop');
                        }
                    }
                });
            },            
            modal: {
                ondismiss: function() {
                    alert('Payment Popup was Closed. If You Want To Complete the Payment, Please Try Again.');
                    $('body').trigger('processStop');
                }
            },
            prefill: {
                name: paymentDetails.customer_name,
                email: paymentDetails.customer_email,
                contact: paymentDetails.customer_phone
            },
            theme: {
                color: '#3399cc'
            }
        };

        var rzp1 = new Razorpay(options);
        rzp1.open();
    }

    function savePaymentDetails(paymentDetails, paymentResponse) {
        var saveData = {
            customer_name: paymentDetails.customer_name,
            customer_phone: paymentDetails.customer_phone,
            customer_email: paymentDetails.customer_email,
            customer_address: paymentDetails.customer_address,
            payment_description: paymentDetails.payment_description,
            payment_type: paymentDetails.payment_type,
            total_amount: paymentDetails.total_amount,
            cash_amount: paymentDetails.cash_amount,
            online_amount: paymentDetails.online_amount,
            razorpay_payment_id: paymentResponse.razorpay_payment_id,
        };
    
        $.ajax({
            url: '/admin/custominvoice/payment/save',
            type: 'POST',
            data: saveData,
            success: function(saveResponse) {
                if (saveResponse.success) {
                    $("#back").trigger('click');
                } else {
                    alert('Payment Save Failed: ' + (saveResponse.message || 'Unknown error'));
                    $('body').trigger('processStop');
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to save payment details:', error);
                alert('Failed to save payment details. Please try again later.');
                $('body').trigger('processStop');
            }
        });
    }

    function createRazorpayOrder(formData) {
        $('body').trigger('processStart');

        loadRazorpayScript(function() {
            $.ajax({
                url: '/admin/custominvoice/payment/createrazorpayorder',
                type: 'POST',
                data: formData,
                success: function(orderResponse) {
                    if (orderResponse.success) {
                        initiateRazorpayPayment(orderResponse);
                    } else {
                        alert('Failed to create Razorpay order');
                        $('body').trigger('processStop');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Order creation failed:', error);
                    alert('Failed to create order: ' + error);
                    $('body').trigger('processStop');
                }
            });
        });
    }

    $('.custom-button-class').on('click', function(e) {
        e.preventDefault();

        var formData = {};
        $('#edit_form')
            .serializeArray()
            .forEach(function(field) {
                formData[field.name] = field.value;
            });

        if (!validateForm(formData)) {
            return;
        }

        if (!formData.total_amount || parseFloat(formData.total_amount) <= 0) {
            alert('Total amount is required and must be greater than zero.');
            return;
        }

        createRazorpayOrder(formData);
    });
});