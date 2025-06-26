define(['jquery'], function($) {
    'use strict';

    return {
        initRazorpayPayment: function() {
            $.ajax({
                url: '/online-consultation/payment/process',
                type: 'POST',
                dataType: 'json', // Ensure you expect a JSON response
                success: function(response) {
                    console.log('response');
                    // Check if the response indicates success
                    if (response.success) {
                        console.log('Success');
                        var options = {
                            "amount": response.amount, // Amount is in currency subunits (e.g., 30000 for ₹300)
                            "currency": response.currency,
                            "name": "MedizinHub",
                            "description": "Order Payment",
                            "order_id": response.orderId, // Razorpay order ID
                            "handler": function(paymentResponse) {
                                console.log('Payment ID:', paymentResponse.razorpay_payment_id);
                                console.log('Order ID:', paymentResponse.razorpay_order_id);
                                console.log('Signature:', paymentResponse.razorpay_signature);
                                // Handle successful payment here, e.g., redirect or show success message
                            },
                            "theme": {
                                "color": "#3399cc"
                            }
                        };
                        console.log('Reached');
                        var rzp1 = new Razorpay(options);
                        rzp1.open();
                    } else {
                        console.error('Error from backend:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to initiate payment:', xhr.responseText);
                }
            });
        }
    };
});
