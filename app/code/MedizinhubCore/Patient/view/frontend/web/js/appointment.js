document.addEventListener("DOMContentLoaded", function() {
    var mediaUrl = document.getElementById('media-url').getAttribute('data-url');
    var modal = document.getElementById("fileModal");
    var span = document.getElementsByClassName("close")[0];

    // Handle View File button click
    document.querySelectorAll('.view-file').forEach(function(button) {
        button.addEventListener('click', function() {
            var reports = JSON.parse(this.getAttribute('data-reports')); // Decoded JSON array
            var fileList = document.getElementById("file-list");
            fileList.innerHTML = '';  // Clear any existing files

            if (reports.length > 0) {
                reports.forEach(function(report) {
                    // Full URL for image source
                    var img = document.createElement("img");
                    img.src = mediaUrl + report; // Construct full URL for images
                    img.alt = 'Patient Prescription';
                    img.style.width = '15%';  // Adjust image size
                    img.style.margin = '10px';
                    fileList.appendChild(img);
                });
            } else {
                fileList.innerHTML = '<p style="text-align: center;">No reports found.</p>';
            }

            modal.style.display = "block";  // Show modal
        });
    });

    span.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
});

document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('.view-comments').forEach(function(link) {
        link.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent default anchor behavior

            var index = this.getAttribute('data-index'); // Get the index of the clicked button
            var commentsSection = document.getElementById("doctor-comments-" + index); // Target the correct section

            if (commentsSection) {
                // Toggle visibility of the correct comments section
                commentsSection.style.display = commentsSection.style.display === "none" ? "block" : "none";
            } else {
                console.error('Comments section not found for index:', index);
            }
        });
    });
});


document.addEventListener("DOMContentLoaded", function() {
    var mediaUrl = document.getElementById('media-url').getAttribute('data-url');
    var modal = document.getElementById("fileModal");
    var span = document.getElementsByClassName("close")[0];

    // Handle View File button click
    document.querySelectorAll('#view-file').forEach(function(button) {
        button.addEventListener('click', function() {
            // Get the data-reports attribute and parse it
            var reports = this.getAttribute('data-reports');
            
            // Parse the reports only if it's not null or undefined
            if (reports) {
                reports = JSON.parse(reports); // Convert stringified data to an array
            } else {
                reports = []; // Fallback to an empty array
            }

            var fileList = document.getElementById("file-list");
            fileList.innerHTML = '';  // Clear any existing files

            if (reports.length > 0) {
                reports.forEach(function(report) {
                    // Full URL for image source (using pub/media)
                    var img = document.createElement("img");
                    img.src = mediaUrl + report; // Construct full URL for images
                    img.alt = "Doctor Prescription"; // Construct full URL for images
                    img.style.width = '20%';  // Adjust image size
                    img.style.margin = '10px';
                    img.style.cursor = 'pointer'; // Add cursor pointer for interactivity

                    // Optional: add a click event to enlarge image if needed
                    img.addEventListener('click', function() {
                        img.style.width = '80%';  // Enlarge image when clicked
                    });

                    fileList.appendChild(img);
                });
            } else {
                fileList.innerHTML = '<p style="text-align: center;">No reports found.</p>';
            }

            modal.style.display = "block";  // Show modal
        });
    });

    // Close modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // Hide modal on outside click
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
});

document.addEventListener("DOMContentLoaded", function() {
    // Function to trigger file downloads
    function triggerDownload(url) {
        var a = document.createElement('a');
        a.href = url;
        a.download = ''; // Optional: you can set a filename if needed
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    // Handle download in appointment-actions
    document.querySelectorAll('.appointment-actions .download').forEach(function(button) {
        button.addEventListener('click', function() {
            var mediaUrl = document.getElementById('media-url').getAttribute('data-url');
            var reports = JSON.parse(this.getAttribute('data-reports')); // Get the report files as an array
            if (reports.length > 0) {
                reports.forEach(function(report) {
                    var fileUrl = mediaUrl + report; // Full URL of the file
                    triggerDownload(fileUrl); // Trigger the download for each report
                });
            } else {
                alert("No reports available to download.");
            }
        });
    });

    // Handle download in comment-actions
    document.querySelectorAll('.comment-actions .download').forEach(function(button) {
        button.addEventListener('click', function() {
            var mediaUrl = document.getElementById('media-url').getAttribute('data-url');
            var prescription = this.getAttribute('data-prescription'); // Get the prescription URL
            if (prescription) {
                var prescriptionUrl = mediaUrl + prescription; // Full URL of the prescription
                triggerDownload(prescriptionUrl); // Trigger the download for the prescription
            } else {
                alert("No prescription available to download.");
            }
        });
    });
});

// Trigger Razorpay When user wants to Repay
require(['jquery', 'Magento_Ui/js/modal/modal'], function ($) {
    $(document).on('click', '.retry-payment', function (e) {
        e.preventDefault();

        const appointmentId = $(this).data('appointment-id');
        const specialPrice = $(this).closest('.appointment-card').find('.special-price').val() || 0;
        const doctorFee = specialPrice;
        const userDetailsElement = $('#patient-details');
        const userDetails = userDetailsElement.length ?
            JSON.parse(userDetailsElement.attr('data-user-details')) : {};

        $('body').loader().loader('show');

        $.ajax({
            url: '/online-consultation/payment/razorpay',
            method: 'POST',
            data: { doctor_fee: doctorFee, appointment_id: appointmentId },
            success: function (response) {
                console.log(response);
                $('body').loader().loader('hide');

                if (response.success) {
                    if (typeof Razorpay !== 'undefined') {
                        var options = {
                            "key": response.razorpay_key,
                            "name": "MedizinHub Pharmacy",
                            "description": "Online Doctor Consultation",
                            "order_id": response.razorpay_order_id,
                            "prefill": {
                                "name": userDetails.name,
                                "email": userDetails.email,
                                "contact": userDetails.mobile
                            },
                            "theme": { "color": "#3399cc" },
                            "handler": function (razorpayResponse) {
                                
                                // First AJAX call to verify payment
                                $.ajax({
                                    url: '/online-consultation/payment/success',
                                    method: 'POST',
                                    data: {
                                        razorpay_payment_id: razorpayResponse.razorpay_payment_id,
                                        razorpay_order_id: razorpayResponse.razorpay_order_id,
                                        razorpay_signature: razorpayResponse.razorpay_signature
                                    },
                                    success: function (successResponse) {
                                        if (successResponse.success) {
                                            
                                            // Second AJAX call to save payment status
                                            $.ajax({
                                                url: '/online-consultation/payment/SaveStatus',
                                                method: 'POST',
                                                data: {
                                                    appointment_id: appointmentId,
                                                    order_id: razorpayResponse.razorpay_order_id,
                                                    status: 'success' // or 'capture' if applicable
                                                },
                                                success: function (saveStatusResponse) {
                                                   
                                                    if (saveStatusResponse.success) {
                                                        sessionStorage.removeItem('selectedPractitionerFees');
                                                        const card = $(`.appointment-card[data-appointment-id="${appointmentId}"]`);
                                                        const actionsContainer = card.find('.appointment-actions');

                                                        actionsContainer.empty();  // Clear old buttons

                                                        const newButtons = `
                                                            <button class="btn btn-outline-success cancel-appointment">Cancel</button>
                                                            <button class="btn btn-success reschedule">Reschedule</button>
                                                        `;
                                                        actionsContainer.append(newButtons);  // Append new buttons
                                                        showSuccessPopup('Payment successful! Our Team will call shortly for consultation');
                                                    } else {
                                                        showFailedPopup('Failed to save payment status. Please contact support.');
                                                    }
                                                },
                                                error: function () {
                                                    showFailedPopup('Error saving payment status. Please try again.');
                                                }
                                            });

                                        } else {
                                            showFailedPopup('Payment verification failed: ' + successResponse.message);
                                        }
                                    },
                                    error: function () {
                                        showFailedPopup('Failed to verify payment. Please contact support.');
                                    }
                                });
                            },
                            "modal": {
                                "ondismiss": function () {
                                    alert('Are you sure, Do you want to close Payment?');
                                }
                            }
                        };

                        var rzp1 = new Razorpay(options);
                        rzp1.open();
                    } else {
                        console.error('Razorpay is not loaded');
                        alert('Failed to load Razorpay. Please refresh and try again.');
                    }
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function () {
                alert('Failed to create Razorpay order.');
            }
        });
    });

    // Success and failure popup functions
    function showSuccessPopup(message) {
        alert(message);
    }

    function showFailedPopup(message) {
        alert(message);
    }
});


function showSuccessPopup() {
    $('#successModal').css('display', 'flex');
    $('#go-home').on('click', function () {
        window.location.href = '/';
    });
}

////////////// Popup fro Reschedule ////////////
document.addEventListener('DOMContentLoaded', function() {
    const rescheduleButtons = document.querySelectorAll('.reschedule');
    const popup = document.getElementById('reschedulePopup');
    const closeBtn = document.querySelector('.popup-close');
    const rescheduleForm = document.getElementById('rescheduleForm');

    // Open popup when reschedule button is clicked
    rescheduleButtons.forEach(button => {
        button.addEventListener('click', function() {
            popup.style.display = 'flex';
        });
    });

    // Close popup when close button is clicked
    closeBtn.addEventListener('click', function() {
        popup.style.display = 'none';
    });

    // Close popup if clicked outside of popup content
    popup.addEventListener('click', function(event) {
        if (event.target === popup) {
            popup.style.display = 'none';
        }
    });

    // Handle form submission
    rescheduleForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const selectedDate = document.getElementById('selectDate').value;
        const selectedTimeSlot = document.getElementById('selectTimeSlot').value;

        // Here you would typically send the data to the server
        console.log('Rescheduling appointment:', { date: selectedDate, timeSlot: selectedTimeSlot });
        
        // Close popup after form submission
        popup.style.display = 'none';
    });
});

require(['jquery'], function ($) {
    $(document).ready(function () {
        let appointmentId = null;

        // Open the reschedule popup and store appointment ID
        $(".reschedule").click(function () {
            appointmentId = $(this).data("appointment-id"); // Get appointment ID
            $("#reschedulePopup").attr("data-appointment-id", appointmentId); // Set it in modal
            $("#reschedulePopup").show();
        });

        // Close the popup when the close button (X) is clicked
        $(".close").click(function () {
            $("#reschedulePopup").hide();
        });

        // Close the popup when clicking outside the modal
        $(window).click(function (event) {
            if ($(event.target).is("#reschedulePopup")) {
                $("#reschedulePopup").hide();
            }
        });

        // Handle Reschedule Confirmation
        $("#confirmReschedule").click(function () {
            var selectedDate = $("#rescheduleDate").val();
            var selectedTime = $("#rescheduleTime").val();

            if (!selectedDate || !selectedTime) {
                alert("Please select both date and time slot.");
                return;
            }

            if (!appointmentId) {
                alert("Invalid appointment ID.");
                return;
            }

            // Send AJAX request to update appointment
            $.ajax({
                url: '/online-consultation/appointment/reschedule',
                type: 'POST',
                data: {
                    appointment_id: appointmentId, // Pass appointment ID
                    date: selectedDate,
                    time_slot: selectedTime,
                },
                success: function (response) {
                    console.log(response);
                    alert("Appointment successfully rescheduled!");

                    // Update button to "Rescheduled" and disable it
                    $(".reschedule[data-appointment-id='" + appointmentId + "']")
                        .text("Rescheduled")
                        .prop("disabled", true)
                        .removeClass("reschedule btn-success")
                        .addClass("btn-secondary");

                    $("#reschedulePopup").hide();
                },
                error: function () {
                    alert("Error rescheduling appointment. Please try again.");
                }
            });
        });
    });
});


require(['jquery'], function($) {
    $(document).ready(function() {
        // Add loading indicator to the page if it doesn't exist
        if ($('#slot-loading').length === 0) {
            $('<div id="slot-loading" style="display:none;">Loading time slots...</div>').insertAfter('#rescheduleTime');
        }
        
        // Listen for changes to the reschedule date input field
        $('#rescheduleDate').on('change', function() {
            const selectedDate = $(this).val(); // Get the selected date
            if (selectedDate) {
                // Call the function to fetch available time slots for the selected date
                fetchAvailableTimeSlots(selectedDate);
            } else {
                // Clear the time slot dropdown if no date is selected
                $('#rescheduleTime').empty().append('<option value="">Select a Time Slot</option>');
            }
        });
        
        function fetchAvailableTimeSlots(date) {
            console.log("Fetching available time slots for date:", date);
            
            // Get the form key for CSRF protection (from hidden input)
            var formKey = $('input[name="form_key"]').val();
            
            // If form key isn't available, try to get it from mage object
            if (!formKey && typeof window.FORM_KEY !== 'undefined') {
                formKey = window.FORM_KEY;
            }
            
            // Show loading indicator
            $('#rescheduleTime').prop('disabled', true);
            $('#slot-loading').show();
            
            $.ajax({
                url: '/online-consultation/appointment/getAvailableTimeSlots',
                type: 'POST',
                data: {
                    date: date,
                    form_key: formKey
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Server response:', response);
                    
                    // Hide loading indicator
                    $('#slot-loading').hide();
                    $('#rescheduleTime').prop('disabled', false);
                    
                    if (response && response.success && response.data && response.data.available_slots) {
                        updateTimeSlotOptions(response.data.available_slots);
                    } else {
                        // Show error message
                        let errorMessage = 'No time slots available.';
                        if (response && response.data && response.data.error) {
                            errorMessage = response.data.error;
                        }
                        console.warn('Error response:', errorMessage);
                        
                        // Reset the time slot dropdown
                        $('#rescheduleTime').empty().append('<option value="">' + errorMessage + '</option>');
                    }
                },
                error: function(xhr, status, error) {
                    // Hide loading indicator
                    $('#slot-loading').hide();
                    $('#rescheduleTime').prop('disabled', false);
                    
                    console.error('AJAX Error:', status, error);
                    console.log('Response Text:', xhr.responseText);
                    
                    // Try to parse the error response if possible
                    let errorMessage = 'Failed to fetch time slots. Please try again later.';
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse && errorResponse.data && errorResponse.data.error) {
                            errorMessage = errorResponse.data.error;
                        }
                    } catch(e) {
                        // Unable to parse response, use default message
                    }
                    
                    // Reset the time slot dropdown
                    $('#rescheduleTime').empty().append('<option value="">' + errorMessage + '</option>');
                }
            });
        }
        
        function updateTimeSlotOptions(availableSlots) {
            const timeSlotSelect = $('#rescheduleTime');
            timeSlotSelect.empty(); // Clear existing options
            
            if (!availableSlots || availableSlots.length === 0) {
                timeSlotSelect.append('<option value="">No available slots for this date</option>');
                return;
            }
            
            timeSlotSelect.append('<option value="">Select a Time Slot</option>');
            
            // Sort the slots by time if they have a sortable time format
            try {
                availableSlots.sort((a, b) => {
                    // Try to extract time for sorting (assumes format like "9:00 AM - 10:00 AM")
                    const timeA = a.time_slot.split('-')[0].trim();
                    const timeB = b.time_slot.split('-')[0].trim();
                    return new Date('1970/01/01 ' + timeA) - new Date('1970/01/01 ' + timeB);
                });
            } catch(e) {
                console.warn('Could not sort time slots:', e);
                // Continue without sorting if there's an error
            }
            
            // Add the options to the select
            availableSlots.forEach(function(slot) {
                timeSlotSelect.append('<option value="' + slot.id + '">' + slot.time_slot + '</option>');
            });
        }
    });
});

require(['jquery', 'mage/url'], function ($, url) {
    $(document).ready(function () {
        // Hide textarea initially
        $('#other-reason').hide();
        
        // Open Cancel Popup
        $('.cancel-appointment').on('click', function () {
            var appointmentId = $(this).data('appointment-id');
            $('#cancelPopup').attr('data-appointment-id', appointmentId).fadeIn();
            // Reset form when opening
            $('#cancel-reason').val('');
            $('#other-reason').hide().val('');
        });

        // Close Popup
        $('.close, #cancelPopup').on('click', function (event) {
            if ($(event.target).hasClass('close') || event.target.id === 'cancelPopup') {
                $('#cancelPopup').fadeOut();
            }
        });

        // Show/hide textarea based on selected reason
        $('#cancel-reason').on('change', function () {
            if ($(this).val() === 'others') { // Lowercase 'others' to match your HTML
                $('#other-reason').show().focus();
            } else {
                $('#other-reason').hide().val('');
            }
        });

        // Cancel Appointment AJAX Call
        $('#confirmCancel').on('click', function () {
            var appointmentId = $('#cancelPopup').attr('data-appointment-id');
            var selectedReason = $('#cancel-reason').val();
            
            if (!selectedReason) {
                alert("Please select a cancellation reason.");
                return;
            }
            
            var reason;
            if (selectedReason === 'others') {
                var otherReason = $('#other-reason').val().trim();
                if (!otherReason) {
                    alert("Please provide a cancellation reason.");
                    $('#other-reason').focus();
                    return;
                }
                reason = otherReason;
            } else {
                reason = selectedReason;
            }

            if (!appointmentId) {
                alert("Invalid appointment ID.");
                return;
            }

            $.ajax({
                url: url.build('online-consultation/appointment/cancel'),
                type: 'POST',
                data: { appointment_id: appointmentId, reason: reason },
                showLoader: true,
                success: function (response) {
                    if (response.success) {
                        alert('Appointment cancelled successfully.');
                        location.reload();
                    } else {
                        alert(response.message || 'Failed to cancel appointment.');
                    }
                },
                error: function () {
                    alert('Something went wrong.');
                }
            });
        });
    });
});

////////////Appointment Filters ///////////////
require(['jquery'], function ($) {
    $(document).ready(function () {
        $('.filter-btn').on('click', function () {
            var filter = $(this).data('filter');
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');

            var matchFound = false;

            $('.appointment-card').each(function () {
                var card = $(this);
                var cardFilter = card.data('filter');

                if (filter === 'all' || cardFilter === filter) {
                    card.removeClass('hidden');
                    matchFound = true;
                } else {
                    card.addClass('hidden');
                }
            });

            // Show or hide the "No appointments available" message
            if (!matchFound) {
                $('#no-appointments-message').show();
            } else {
                $('#no-appointments-message').hide();
            }
        });
    });
});



