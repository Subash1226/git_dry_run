require(['jquery', 'jquery/ui', 'Magento_Ui/js/modal/modal', 'mage/loader'], function ($, modal) {
    'use strict';

    window.checkAndAddFileInput = function () {
        let userCheck = sessionStorage.getItem('Userlogin');
        if (userCheck === 'true') {
            addFileInput();
        } else {
            let linkRegister = document.createElement('a');
            linkRegister.className = 'customer-register-link';
            document.body.appendChild(linkRegister);
            linkRegister.click();
        }
    };

    document.getElementById('appointment_date').addEventListener('focusout', validateDateTime);
    document.getElementById('time_slot').addEventListener('focusout', validateDateTime);

    function validateDateTime() {
        const dateInput = document.getElementById('appointment_date').value;
        const timeInput = document.getElementById('time_slot').value;
        const dateErrorMessage = document.getElementById('time-slot-error-message');
        dateErrorMessage.style.display = 'none';
        dateErrorMessage.textContent = '';

        if (!dateInput || !timeInput) {
            return;
        }

        // Parse the date input (dd/mm/yyyy) into a Date object
        const dateParts = dateInput.split('/');
        if (dateParts.length !== 3) {
            dateErrorMessage.style.display = 'block';
            dateErrorMessage.textContent = 'Invalid date format. Please use DD/MM/YYYY.';
            return;
        }

        const day = parseInt(dateParts[0], 10);
        const month = parseInt(dateParts[1], 10) - 1; // Month is zero-based in JavaScript Date
        const year = parseInt(dateParts[2], 10);

        const inputDate = new Date(year, month, day);
        const currentDate = new Date();

        // Clear the time part of the dates for comparison
        inputDate.setHours(0, 0, 0, 0);
        currentDate.setHours(0, 0, 0, 0);

        // Validate if the input date is in the past
        if (inputDate < currentDate) {
            dateErrorMessage.style.display = 'block';
            dateErrorMessage.textContent = 'Please select a future date.';
            document.getElementById('appointment_date').value = ''; // Clear the input
            return;
        }

        // If the selected date is today, validate the time
        if (inputDate.getTime() === currentDate.getTime()) {
            const currentTime = new Date().getHours() * 60 + new Date().getMinutes(); // Current time in minutes
            const selectedTimeParts = timeInput.split(':');
            const selectedTime = parseInt(selectedTimeParts[0]) * 60 + parseInt(selectedTimeParts[1]); // Selected time in minutes

            // If the selected time is in the past, show an error message
            if (selectedTime <= currentTime) {
                dateErrorMessage.style.display = 'block';
                dateErrorMessage.textContent = 'Please select a future time.';
                document.getElementById('time_slot').value = ''; // Clear the input
                return;
            }
        }
    }

    function addFileInput() {
        if (previewFileCount >= maxFileInputs) {
            errorMessage.innerText = "You can only upload up to 5 files.";
            return;
        }else {
            errorMessage.innerText = "";
        }

        // Create the file input dynamically
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.name = 'report_docs[]'; // Important for sending files to the backend
        fileInput.accept = '.jpg,.jpeg,.png,.gif,.pdf';
        fileInput.multiple = true;
        fileInput.style.display = 'none'; // Hide the input itself
        fileInput.onchange = () => fileSelected(fileInput);

        // Append the file input to the hidden container instead of the body
        document.getElementById('file-input-container').appendChild(fileInput);

        fileInput.click(); // Trigger the file selection dialog
    }

    function fileSelected(input) {
        const files = input.files;
        const container = document.getElementById('image-preview-container');

        if ((previewFileCount + files.length) > maxFileInputs) {
            errorMessage.innerText = "You can only upload up to 5 files.";
            input.value = ''; // Reset file input
            return;
        }

        for (let file of files) {
            if (!validateFile(file)) {
                input.value = ''; // Reset file input
                return;
            }

            const previewItem = document.createElement('div');
            previewItem.classList.add('preview-item');

            const fileReader = new FileReader();
            fileReader.onload = function (e) {
                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '80px';
                    img.style.height = '120px';
                    previewItem.appendChild(img);
                } else if (file.type === 'application/pdf') {
                    const pdfIcon = document.createElement('img');
                    pdfIcon.src = "<?= $block->getViewFileUrl('MedizinhubCore_Patient::images/pdf.png') ?>";
                    pdfIcon.style.width = '80px';
                    pdfIcon.style.height = '120px';
                    previewItem.appendChild(pdfIcon);
                }

                // Add remove button for the preview item
                const removeButton = document.createElement('a');
                removeButton.classList.add('remove-button');
                removeButton.innerText = 'X';
                removeButton.style.cursor = 'pointer';
                removeButton.onclick = () => removeFileInput(previewItem, file.name);

                previewItem.appendChild(removeButton);
                container.appendChild(previewItem);
                previewFileCount++;
                updateFileCount();

                fileNamesArray.push(file.name); // Store the file name in the array
            };

            fileReader.readAsDataURL(file);
        }
    }

    function validateFile(file) {
        if (!allowedFileTypes.includes(file.type)) {
            errorMessage.innerText = "Unsupported file format. Please upload JPG, JPEG, PNG, GIF, or PDF.";
            return false;
        } else if (file.size > maxSizeMB * 1024 * 1024) {
            errorMessage.innerText = "File size exceeds 5 MB.";
            return false;
        }
        return true;
    }

    function removeFileInput(previewItem, fileName) {
        previewItem.remove();
        previewFileCount--;
        const index = fileNamesArray.indexOf(fileName);
        if (index > -1) {
            fileNamesArray.splice(index, 1);  // Remove the file name from the array
        }
        updateFileCount();
    }

    function updateFileCount() {
        const fileCountInfo = document.getElementById('file-count-info');
        fileCountInfo.innerText = previewFileCount + ' file(s) selected.';
        const label = document.getElementById('prescription-label');
        label.style.display = previewFileCount >= maxFileInputs ? 'none' : 'block';
        errorMessage.innerText = '';
    }

    // Define Variables
    let fileNamesArray = [];
    const modalContent = $('#modal-content');
    const editModalContent = $('#edit-modal-content');
    const nextBtn = $('#next-step-btn');
    const bookAppointmentBtn = $('#book-appointment-btn');
    const codBtn = $('#cod-btn');
    const cancelBtn = $('#cancel-btn');
    const modalBtn = $('#modal-btn');
    const patientListContainer = $('#existing-patient-container');
    const patientList = $('#update-patient-list .patient-preview');
    const progressBar = $('.progress-bar');
    const steps = $('.step');
    const stepTitles = $('.step-title');
    let currentStepIndex = 0;
    const maxSizeMB = 5;
    const maxFileInputs = 5;
    let previewFileCount = 0;
    const allowedFileTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    const errorMessage = document.getElementById('error-message');

    document.getElementById('cod-btn').onclick = function (e) {
        e.preventDefault();
        
        const storedFees = JSON.parse(sessionStorage.getItem('selectedPractitionerFees'));
        const doctorFee = storedFees.totalDoctorFee;
    
        const userDetailsElement = document.getElementById('patient-details');
        const userDetails = userDetailsElement ?
            JSON.parse(userDetailsElement.getAttribute('data-user-details')) :
            {};
    
        $('body').loader().loader('show');
    
        let globalAppointmentId = null;
        let razorpayOrderId = null;
    
        savePatientIssue()
        .then((appointmentId) => {
            console.log('Appointment ID:', appointmentId);
            globalAppointmentId = appointmentId;

            // First AJAX call for Razorpay order creation
            return $.ajax({
                url: '/online-consultation/payment/razorpay',
                method: 'POST',
                data: { doctor_fee: doctorFee }
            });
        })
        .then((response) => {
            $('body').loader().loader('hide');

            if (response.success) {
                razorpayOrderId = response.razorpay_order_id;
                console.log('Razorpay Order ID:', razorpayOrderId);

                var options = {
                    "name": "MedizinHub Pharmacy",
                    "description": "Online Doctor Consultation",
                    "order_id": razorpayOrderId,
                    "prefill": {
                        "name": userDetails.name,
                        "email": userDetails.email,
                        "contact": userDetails.mobile
                    },
                    "theme": { "color": "#3399cc" },
                    "handler": function (paymentResponse) {
                        console.log('Payment Successful:', paymentResponse);

                        // Verify Payment
                        $.ajax({
                            url: '/online-consultation/payment/success',
                            method: 'POST',
                            data: {
                                razorpay_payment_id: paymentResponse.razorpay_payment_id,
                                razorpay_order_id: paymentResponse.razorpay_order_id,
                                razorpay_signature: paymentResponse.razorpay_signature
                            }
                        })
                        .then((successResponse) => {
                            if (successResponse.success) {
                                // Ensure SaveStatus is called after verification
                                return $.ajax({
                                    url: '/online-consultation/payment/SaveStatus',
                                    method: 'POST',
                                    data: {
                                        appointment_id: globalAppointmentId,
                                        order_id: razorpayOrderId,
                                        status: 'success'
                                    }
                                });
                            } else {
                                alert('Payment verification failed: ' + successResponse.message);
                                throw new Error('Payment verification failed');
                            }
                        })
                        .then((saveStatusResponse) => {
                            console.log('Status Response:', saveStatusResponse);

                            if (saveStatusResponse.success) {
                                sessionStorage.removeItem('selectedPractitionerFees');
                                showSuccessPopup('Payment successful! Our Team will call shortly for consultation');
                            } else {
                                console.log('Failed');
                                showFailedPopup('Failed to save payment status. Please contact support.');
                            }
                        })
                        .catch((error) => {
                            console.error('Error:', error);
                            showFailedPopup('Error saving payment status. Please try again.');
                        });
                    },
                    "modal": {
                        "ondismiss": function () {
                            alert('Are you sure you want to close Payment?');
                        }
                    }
                };

                var rzp1 = new Razorpay(options);
                rzp1.open();
            } else {
                alert('Error: ' + response.message);
            }
        })
        .catch((error) => {
            $('body').loader().loader('hide');
            console.error('Failed to save patient issue:', error);
            alert('An error occurred while saving patient details. Please try again.');
        });
    };    

    function showSuccessPopup() {
        $('#successModal').css('display', 'flex');
        $('#go-home').on('click', function () {
            window.location.href = '/';
        });
    }

    $('#description').on('input', function () {
        const maxLength = $(this).attr('maxlength');
        const currentLength = $(this).val().length;
        $('#char-count').text(`${maxLength - currentLength} characters remaining`);
    });

    function showStep(stepIndex) {
        steps.hide();
        steps.filter(`[data-step="${stepIndex + 1}"]`).show();

        const progress = ((stepIndex + 1) / steps.length) * 100;
        progressBar.css('width', `${progress}%`).attr('aria-valuenow', progress);

        stepTitles.each(function () {
            const stepTitleIndex = $(this).data('step');
            $(this).toggleClass('active', stepTitleIndex <= stepIndex + 1);
        });

        if (stepIndex === 0) {
            if ($('#update-patient-list .patient-preview').length > 0) {
                nextBtn.show();
                modalContent.hide();
                editModalContent.hide();
                patientListContainer.show();
            } else {
                nextBtn.hide();
            }
            bookAppointmentBtn.hide();
            codBtn.hide();
        } else if (stepIndex === 1) {
            nextBtn.hide();
            bookAppointmentBtn.show();
            codBtn.hide();
        } else if (stepIndex === 2) {
            nextBtn.hide();
            bookAppointmentBtn.hide();
            codBtn.show();
        }

        // nextBtn.toggle(stepIndex === 0);
        // bookAppointmentBtn.toggle(stepIndex === 1);
        // codBtn.toggle(stepIndex === 2);
    }

    $('#step-title[data-step="1"]').on('click', function () {
        if ($('#update-patient-list .patient-preview').length > 0) {
            nextBtn.show();
            bookAppointmentBtn.hide();
        } else {
            nextBtn.hide();
            bookAppointmentBtn.hide();
        }
    });

    function setConsultantType() {
        const selectedConsultantType = sessionStorage.getItem('selectedConsultantType');
        if (selectedConsultantType) {
            $('#practitionersDropdown').val(selectedConsultantType);
        }
    }

    function updateNextButtonState() {
        const patientList = $('#update-patient-list .patient-preview');
        if (patientList.length === 0) {
            nextBtn.prop('disabled', true);
        } else {
            nextBtn.prop('disabled', false);
        }
    }

    function patientChecker() {
        $.ajax({
            url: '/online-consultation/index/getpatients',
            type: 'GET',
            dataType: 'html',
            success: function (patientResponse) {
                if (empty(patientResponse) || patientResponse.trim().length === 0) {
                    modalContent.show();
                    $('#update-patient-list').html('No patients found.');
                    sessionStorage.setItem("PatientDetail", false);
                    patientListContainer.hide();
                    nextBtn.hide();
                } else {
                    sessionStorage.setItem("PatientDetail", true);
                    patientListContainer.show();
                    modalContent.hide();
                    nextBtn.show();
                    updateNextButtonState();
                }
            },
            error: function (xhr, textStatus, errorThrown) {
                console.error('Failed to fetch patients:', errorThrown);
            }
        });
    }


    function savePatientIssue() {
        let formData = new FormData();
        formData.append('patient_id', $('#selected-patient-id').val());
        formData.append('practitioner_id', $('#practitionersDropdown').val());
        formData.append('hospital_id', $('#hospitalDropdown').val());
        formData.append('appointment_date', $('#appointment_date').val());
        formData.append('time_slot', $('#time_slot').val());
        formData.append('patient_issue', $('#description').val());
        formData.append('file_names', JSON.stringify(fileNamesArray));

        // Append files from the hidden file input container
        const fileInputs = document.querySelectorAll('input[name="report_docs[]"]');
        fileInputs.forEach(fileInput => {
            const files = fileInput.files;
            for (let i = 0; i < files.length; i++) {
                formData.append('report_docs[]', files[i]); // Append each file
            }
        });

        // // Log formData to ensure files are appended (for debugging)
        // for (let pair of formData.entries()) {
        //     console.log(pair[0] + ': ' + pair[1]);
        // }

        $('body').loader().loader('show');
        return $.ajax({
            url: '/online-consultation/appointment/save',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('body').loader().loader('hide');
                if (response.success && response.appointment_id) {
                    console.log('Inserted Appointment ID:', response.appointment_id);
                    resolve(response.appointment_id);
                 } else {
                    alert('An error occurred while booking the appointment.');
                }
            },
            error: function (xhr, textStatus, errorThrown) {
                console.error('Failed to add patient issue:', errorThrown);
                alert('An error occurred while saving the patient issue.');
            }
        });
    }

    function validateField(inputSelector, errorSelector, pattern, errorMessageEmpty, errorMessageInvalid, showError = false) {
        // Ensure the input selector exists
        const inputElement = $(inputSelector);
        if (!inputElement.length) {
            console.error(`Input selector '${inputSelector}' not found.`);
            return false;  // Return false since the element doesn't exist
        }

        // Ensure the error selector exists
        const errorElement = $(errorSelector);
        if (!errorElement.length) {
            console.error(`Error selector '${errorSelector}' not found.`);
            return false;  // Return false since the element doesn't exist
        }

        const value = inputElement.val().trim();
        let isValid = true;

        // Check if value is empty or "0"
        if (value === "" || value === "0") {
            if (showError) {
                errorElement.text(errorMessageEmpty).show();
            }
            isValid = false;
        }
        // Check if the value matches the pattern
        else if (pattern && !pattern.test(value)) {  // Ensure pattern is provided and is valid
            if (showError) {
                errorElement.text(errorMessageInvalid).show();
            }
            isValid = false;
        }
        // If valid, hide the error element
        else {
            errorElement.hide();
        }

        return isValid;
    }

    function validateAllFields(showErrors = false) {
        const validations = [
            validateField('#addfirstname', '#firstname-patient-error-message', /^[A-Za-z ]+$/, 'First name should not be empty.', 'First name should contain only alphabets and spaces.', showErrors),
            validateField('#addlastname', '#lastname-patient-error-message', /^[A-Za-z ]+$/, 'Last name should not be empty.', 'Last name should contain only alphabets and spaces.', showErrors),
            validateField('#addcustomer_email', '#email-patient-error-message', /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/, 'Email should not be empty.', 'Please enter a valid Email address.', showErrors),
            validateField('#addcustomer_gender', '#gender-patient-error-message', /^(male|female|transgender)$/, 'Gender should not be empty.', 'Please select a valid gender.', showErrors),
            validateField('#addtelephone', '#telephone-patient-error-message', /^\d{10}$/, 'Phone number should not be empty.', 'Please enter a valid 10-digit phone number.', showErrors),
            validateField('#addwhatsapp', '#whatsapp-patient-error-message', /^\d{10}$/, 'WhatsApp number should not be empty.', 'Please enter a valid 10-digit WhatsApp number.', showErrors),
            validateField('#addstreet', '#street-patient-error-message', /^[A-Za-z0-9\s,\/]+$/, 'Street should not be empty.', 'Please enter a valid street address.', showErrors),
            validateField('#addcity', '#city-patient-error-message', /^[A-Za-z\s]+$/, 'City should not be empty.', 'Please enter a valid city name.', showErrors),
            validateField('#addpincode', '#pincode-patient-error-message', /^\d{6}$/, 'Pincode should not be empty.', 'Please enter a valid 6-digit pincode.', showErrors)
        ];
        const allValid = validations.every(validation => validation);
        $('#patient-submit-btn').prop('disabled', !allValid);
        return allValid;
    }

    function attachFieldValidation() {
        $('#addfirstname').on('focusout', function () {
            validateField('#addfirstname', '#firstname-patient-error-message', /^[A-Za-z ]+$/, 'First name should not be empty.', 'First name should contain only alphabets and spaces.', true);
        });

        $('#addlastname').on('focusout', function () {
            validateField('#addlastname', '#lastname-patient-error-message', /^[A-Za-z ]+$/, 'Last name should not be empty.', 'Last name should contain only alphabets and spaces.', true);
        });

        $('#addcustomer_email').on('focusout', function () {
            validateField('#addcustomer_email', '#email-patient-error-message', /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/, 'Email should not be empty.', 'Please enter a valid Email address.', true);
        });

        $('#addcustomer_gender').on('change', function () {
            validateField('#addcustomer_gender', '#gender-patient-error-message', /^(male|female|transgender)$/, 'Gender should not be empty.', 'Please select a valid gender.', true);
        });

        $('#addtelephone').on('focusout', function () {
            validateField('#addtelephone', '#telephone-patient-error-message', /^\d{10}$/, 'Phone number should not be empty.', 'Please enter a valid 10-digit phone number.', true);
        });

        $('#addwhatsapp').on('focusout', function () {
            validateField('#addwhatsapp', '#whatsapp-patient-error-message', /^\d{10}$/, 'WhatsApp number should not be empty.', 'Please enter a valid 10-digit WhatsApp number.', true);
        });

        $('#addstreet').on('focusout', function () {
            validateField('#addstreet', '#street-patient-error-message', /^[A-Za-z0-9\s,\/]+$/, 'Street should not be empty.', 'Please enter a valid street address.', true);
        });

        $('#addarea').on('focusout', function () {
            validateField('#addarea', '#area-patient-error-message', /^[A-Za-z0-9\s,\/]+$/, 'Area should not be empty.', 'Please enter a valid area address.', true);
        });

        $('#addcity').on('focusout', function () {
            validateField('#addcity', '#city-patient-error-message', /^[A-Za-z\s]+$/, 'City should not be empty.', 'Please enter a valid city name.', true);
        });

        $('#addpincode').on('focusout', function () {
            validateField('#addpincode', '#pincode-patient-error-message', /^\d{6}$/, 'Pincode should not be empty.', 'Please enter a valid 6-digit pincode.', true);
        });
    }

    function validateAllEditFields(showErrors = false) {
        const validations = [
            validateField('#edit-firstname', '#edit-firstname-error-message', /^[A-Za-z ]+$/, 'Please enter a valid First Name with alphabets and spaces only.', 'Invalid first name.', showErrors),
            validateField('#edit-lastname', '#edit-lastname-error-message', /^[A-Za-z ]+$/, 'Please enter a valid Last Name with alphabets and spaces only.', 'Invalid last name.', showErrors),
            validateField('#edit-email', '#edit-email-error-message', /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/, 'Please enter a valid Email address.', 'Invalid email.', showErrors),
            validateField('#edit-telephone', '#edit-telephone-error-message', /^\d{10}$/, 'Please enter a valid 10-digit phone number.', 'Invalid phone number.', showErrors),
            validateField('#edit-whatsapp', '#edit-whatsapp-error-message', /^\d{10}$/, 'Please enter a valid 10-digit WhatsApp number.', 'Invalid WhatsApp number.', showErrors),
            validateField('#edit-street', '#edit-street-error-message', /^[A-Za-z0-9\s,\/]+$/, 'Please enter a valid street address with alphanumeric characters and spaces.', 'Invalid street address.', showErrors),
            validateField('#edit-area', '#edit-area-error-message', /^[A-Za-z0-9\s,\/]+$/, 'Please enter a valid area address with alphanumeric characters and spaces.', 'Invalid area address.', showErrors),
            validateField('#edit-city', '#edit-city-error-message', /^[A-Za-z\s]+$/, 'Please enter a valid city name with alphabets and spaces only.', 'Invalid city name.', showErrors),
            validateField('#edit-pincode', '#edit-pincode-error-message', /^\d{6}$/, 'Please enter a valid 6-digit pincode.', 'Invalid pincode.', showErrors)
        ];

        const allValid = validations.every(validation => validation);
        $('#patient-edit-btn').prop('disabled', !allValid);
        return allValid;
    }

    function attachFieldset2Validation() {
        $('#practitionersDropdown, #hospitalDropdown, #appointment_date, #time_slot, #description').on('input change', function () {
            validateFieldset2(true);
        });

        validateFieldset2();
    }

    function validateFieldset2(showErrors = false) {
        const validations = [
            validateField('#practitionersDropdown', '#practitioner-error-message', /.+/, 'Please select a practitioner.', '', showErrors),
            validateField('#hospitalDropdown', '#hospital-error-message', /.+/, 'Please select a hospital.', '', showErrors),
            validateField('#appointment_date', '#date-error-message', /^\d{2}\/\d{2}\/\d{4}$/, 'Please enter a valid date.', '', showErrors),
            validateField('#time_slot', '#time-slot-error-message', /^[1-9]\d*$/, 'Please select a time slot.', '', showErrors),  // Now checks for value of 0
            validateField('#description', '#description-error-message', /.+/, 'Please enter a description.', '', showErrors)
        ];

        const allValid = validations.every(validation => validation);
        $('#book-appointment-btn').prop('disabled', !allValid);
        return allValid;
    }

    $('#addfirstname, #addlastname, #addcustomer_email, #addcustomer_gender, #addtelephone, #addwhatsapp, #addstreet, #addarea, #addcity, #addpincode').on('input', function () {
        validateAllFields();
    });

    $('#edit-firstname, #edit-lastname, #edit-age, #edit-email, #edit-telephone, #edit-whatsapp, #edit-street, #edit-area, #edit-city, #edit-pincode').on('input', function () {
        validateAllEditFields();
    });

    function setInitialCheckedPatient() {
        const initialCheckedPatient = $('input[name="patient_entity"]:checked');
        if (initialCheckedPatient.length) {
            $('#selected-patient-id').val(initialCheckedPatient.val());
        }
    }

    function updateHiddenPatientId() {
        $('input[name="patient_entity"]').on('change', function () {
            $('#selected-patient-id').val($(this).val());
        });
    }

    function checkPatientContainer() {
        const patientList = $('#update-patient-list .patient-preview');
        const patientContainer = $('#update-patient-list');
        if (patientList.length === 0) {
            patientListContainer.hide();
            modalContent.show();
        } else {
            patientContainer.show();
            modalContent.hide();
        }
    }

    function setupDatePicker() {
        // Initialize date pickers for #adddob and #edit-dob
        $('#adddob, #edit-dob').datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            yearRange: "-100:+0",
            showAnim: "slideDown",
            showButtonPanel: true,
            maxDate: 0, // Allows only past dates
            beforeShow: function (input, inst) {
                setTimeout(function () {
                    inst.dpDiv.css({
                        "width": $(input).outerWidth() + "px"
                    });
                }, 0);
            },
            onChangeMonthYear: function (year, month, inst) {
                setTimeout(function () {
                    inst.dpDiv.css({
                        "width": $(inst.input).outerWidth() + "px"
                    });
                }, 0);
            }
        }).on('click', function () {
            $(this).datepicker('show');
        });

        // Initialize date picker for #appointment_date
        $('#appointment_date').datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true,
            showAnim: "slideDown",
            showButtonPanel: true,
            minDate: 0, // Allows only today and future dates
            beforeShow: function (input, inst) {
                setTimeout(function () {
                    inst.dpDiv.css({
                        "width": $(input).outerWidth() + "px"
                    });
                }, 0);
            },
            onChangeMonthYear: function (year, month, inst) {
                setTimeout(function () {
                    inst.dpDiv.css({
                        "width": $(inst.input).outerWidth() + "px"
                    });
                }, 0);
            }
        }).on('click', function () {
            $(this).datepicker('show');
        });

        // Calendar icon click handler
        $('.calendar-icon').on('click', function () {
            $(this).siblings('input').datepicker('show');
        });
    }

    function calculateAge(dob) {
        const birthDate = new Date(dob);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDifference = today.getMonth() - birthDate.getMonth();
        if (monthDifference < 0 || (monthDifference === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age;
    }

    function convertDateFormat(dateStr) {
        const parts = dateStr.split('/');
        return `${parts[2]}-${parts[1]}-${parts[0]}`;
    }

    function formatDateToDDMMYY(dateStr) {
        const parts = dateStr.split('-');
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    function initEventListeners() {
        modalBtn.on('click', function (e) {
            e.preventDefault();
            modalContent.show();
            patientListContainer.hide();
            nextBtn.hide();
        });

        $('#adddob, #edit-dob').on('click', function () {
            var inputWidth = $(this).outerWidth();
            $(this).datepicker('widget').css('width', inputWidth + 'px');
            $(this).datepicker('show');
        });

        $('#adddob').datepicker({
            onSelect: function (dateText) {
                const dobValue = $(this).datepicker('getDate');
                if (dobValue) {
                    const age = calculateAge(dobValue);
                    $('#patient-submit-btn').data('age', age);
                }
            }
        });

        function dynamicdoctorFee() {
            const selectedOption = $('#practitionersDropdown option:selected');
            const practitionerId = selectedOption.val();
            const mrp = selectedOption.data('mrp');
            const specialPrice = selectedOption.data('special-price');
            if (practitionerId) {
                console.log(`Practitioner ID: ${practitionerId}`);
                console.log(`MRP: ₹${mrp}`);
                console.log(`Special Price: ₹${specialPrice}`);
                const doctorFee = mrp;
                const totalDoctorFee = specialPrice ? specialPrice : mrp;
                const discount = doctorFee - totalDoctorFee;
                const discountPercentage = Math.round((discount / doctorFee) * 100);
                $('#doctor-fee').text(`₹${doctorFee}`);
                $('#total-amount').text(`₹${totalDoctorFee}`);
                $('#discount').text(`- ₹${discount}`);
                $('.discount-offer').text(`${discountPercentage}% OFF`);
                sessionStorage.setItem('selectedPractitionerFees', JSON.stringify({
                    doctorFee: doctorFee,
                    totalDoctorFee: totalDoctorFee
                }));
            } else {
                $('#doctor-fee').text('');
                $('#total-amount').text('');
                $('#discount').text('');
                $('.discount-offer').text('');
            }
        }


        bookAppointmentBtn.off('click').on('click', function (event) {
            event.preventDefault();
            const allFieldsValid = validateFieldset2(true);
            if (allFieldsValid) {
                dynamicdoctorFee();
                nextBtn.hide();
                bookAppointmentBtn.hide();
                showStep(2);
            }
        });        

        nextBtn.on('click', function () {
            if (currentStepIndex < steps.length - 1) {
                currentStepIndex++;
                showStep(currentStepIndex);
            }
        });

        stepTitles.on('click', function () {
            const clickedStepIndex = $(this).data('step') - 1;
            if ($(this).hasClass('active')) {
                currentStepIndex = clickedStepIndex;
                showStep(currentStepIndex);
            }
        });

        $('.go-back-btn').on('click', function () {
            modalContent.hide();
            editModalContent.hide();
            nextBtn.show();
            patientListContainer.show();
            checkPatientContainer();
        });

        $('#patient-submit-btn').off('click').on('click', function (event) {
            event.preventDefault();
            const allFieldsValid = validateAllFields(true);
            if (!allFieldsValid) {
                return;
            }

            let regionInput = document.getElementById('addregion');
            let regionSelect = document.getElementById('addregion_id');
            regionInput.value = regionSelect.options[regionSelect.selectedIndex].text;

            let dobValue = $('#adddob').val().trim();
            let formattedDob = dobValue ? convertDateFormat(dobValue) : null;

            let calculatedAge = formattedDob ? calculateAge(formattedDob) : null;

            $(this).prop('disabled', true);

            let formData = {
                firstname: $('#addfirstname').val().trim(),
                lastname: $('#addlastname').val().trim(),
                dob: formattedDob,
                blood_group: $('#addblood_group').val() || null,
                customer_age: calculatedAge,
                customer_email: $('#addcustomer_email').val().trim(),
                customer_gender: $('#addcustomer_gender').val().trim(),
                telephone: $('#addtelephone').val().trim(),
                whatsapp: $('#addwhatsapp').val().trim(),
                house_no: $('#addhouse_no').val().trim(),
                street: $('#addstreet').val().trim(),
                area: $('#addarea').val().trim(),
                city: $('#addcity').val().trim(),
                pincode: $('#addpincode').val().trim(),
                region: $('#addregion').val().trim(),
                region_id: $('#addregion_id').val().trim(),
                country_id: $('#addcountry_id').val().trim()
            };
            $('body').loader().loader('show');

            $.ajax({
                url: '/online-consultation/index/savepatient',
                type: 'POST',
                data: formData,
                success: function (response) {
                    $('body').loader().loader('hide');
                    if (response.success) {
                        $.ajax({
                            url: '/online-consultation/index/getpatients',
                            type: 'GET',
                            success: function (patientResponse) {
                                modalContent.hide();
                                nextBtn.css('visibility', 'visible');
                                patientListContainer.show();
                                modalBtn.show();
                                $('#update-patient-list').html(patientResponse);
                                checkPatientContainer();
                                clearFormFields();
                                resetFormState();
                                updateNextButtonState();
                            },
                            error: function (xhr, textStatus, errorThrown) {
                                $('body').loader().loader('hide');
                                console.error('Failed to fetch patients:', errorThrown);
                            }
                        });
                    } else {
                        $('body').loader().loader('hide');
                        alert('Failed to add patient:', response.message);
                        console.error('Failed to add patient:', response.message);
                    }
                },
                error: function (xhr, textStatus, errorThrown) {
                    $('body').loader().loader('hide');
                    console.error('Failed to add patient:', errorThrown);
                },
                complete: function () {
                    $('#patient-submit-btn').prop('disabled', false);
                }
            });
        });

        $('#next-step-btn').on('click', function (e) {
            e.preventDefault(); // Prevent the default button behavior

            // Get the ID of the checked radio button
            let selectedPatientId = $('.patient-preview input[name="patient_entity"]:checked').val();

            if (selectedPatientId) {
                // Assign the selected patient ID to the hidden input field
                $('#selected-patient-id').val(selectedPatientId);
            } else {
                alert('Please select a patient before proceeding.');
            }
        });

        $(document).on('change', 'input[name="patient_entity"]', function () {
            if ($(this).is(':checked')) {
                let patientId = $(this).data('patient-id');
                $('#selected-patient-id').val(patientId);
            }
        });


        $(document).on('click', '.edit-patient', function (e) {
            e.preventDefault();
            let patientId = $(this).data('patient-id');
            let fullName = $(this).data('firstname');
            let lastName = $(this).data('lastname');
            let customerAge = $(this).data('age');
            let customerGender = $(this).data('gender');
            let dob = $(this).data('dob');
            let formattedDob = formatDateToDDMMYY(dob);
            let bloodGroup = $(this).data('blood-group');
            let houseno = $(this).data('house-no');
            let street = $(this).data('street');
            let area = $(this).data('area');
            let city = $(this).data('city');
            let pincode = $(this).data('pincode');
            let telephone = $(this).data('telephone');
            let whatsapp = $(this).data('whatsapp');
            let email = $(this).data('email');
            let region = $(this).data('region');
            let regionId = $(this).data('region-id');
            $('#patient-edit-btn').prop('disabled', false);

            $('#edit-modal-content').find('[name="patient_id"]').val(patientId);
            $('#edit-modal-content').find('[name="firstname"]').val(fullName);
            $('#edit-modal-content').find('[name="lastname"]').val(lastName);
            $('#edit-modal-content').find('[name="customer_age"]').val(customerAge);
            $('#edit-modal-content').find('[name="customer_gender"]').val(customerGender);
            $('#edit-modal-content').find('[name="dob"]').val(formattedDob);
            $('#edit-modal-content').find('[name="blood_group"]').val(bloodGroup);
            $('#edit-modal-content').find('[name="house_no"]').val(houseno);
            $('#edit-modal-content').find('[name="street"]').val(street);
            $('#edit-modal-content').find('[name="area"]').val(area);
            $('#edit-modal-content').find('[name="city"]').val(city);
            $('#edit-modal-content').find('[name="pincode"]').val(pincode);
            $('#edit-modal-content').find('[name="telephone"]').val(telephone);
            $('#edit-modal-content').find('[name="whatsapp"]').val(whatsapp);
            $('#edit-modal-content').find('[name="customer_email"]').val(email);
            $('#edit-modal-content').find('[name="region"]').val(region);
            $('#edit-modal-content').find('[name="region_id"]').val(regionId);

            patientListContainer.hide();
            nextBtn.hide();
            $('#edit-modal-content').show();
        });

        $('#patient-edit-btn').on('click', function (event) {

            event.preventDefault();
            const allFieldsValid = validateAllEditFields(true);
            if (!allFieldsValid) {
                console.error('Validation failed.');
                return;
            }

            let editRegionInput = document.getElementById('edit-region');
            let editRegionSelect = document.getElementById('edit-region_id');
            editRegionInput.value = editRegionSelect.options[editRegionSelect.selectedIndex].text;

            let dobValue = $('#edit-modal-content [name="dob"]').val();
            let formattedDob = dobValue ? convertDateFormat(dobValue) : null;
            let calculatedAge = formattedDob ? calculateAge(formattedDob) : null;

            let formData = {
                patient_id: $('#edit-modal-content [name="patient_id"]').val(),
                first_name: $('#edit-modal-content [name="firstname"]').val(),
                last_name: $('#edit-modal-content [name="lastname"]').val(),
                customer_age: calculatedAge,
                customer_gender: $('#edit-modal-content [name="customer_gender"]').val(),
                dob: formattedDob,
                blood_group: $('#edit-modal-content [name="blood_group"]').val() || null,
                houseNo: $('#edit-modal-content [name="house_no"]').val(),
                street: $('#edit-modal-content [name="street"]').val(),
                area: $('#edit-modal-content [name="area"]').val(),
                city: $('#edit-modal-content [name="city"]').val(),
                pincode: $('#edit-modal-content [name="pincode"]').val(),
                telephone: $('#edit-modal-content [name="telephone"]').val(),
                whatsapp: $('#edit-modal-content [name="whatsapp"]').val(),
                customer_email: $('#edit-modal-content [name="customer_email"]').val(),
                region: $('#edit-modal-content [name="region"]').val(),
                regionId: $('#edit-modal-content [name="region_id"]').val(),
            };

            $('body').loader().loader('show');

            $.ajax({
                url: 'online-consultation/index/updatepatient',
                type: 'POST',
                data: formData,
                success: function (response) {

                    if (response.success) {
                        $.ajax({
                            url: '/online-consultation/index/getpatients',
                            type: 'GET',
                            success: function (patientResponse) {
                                $('body').loader().loader('hide');
                                $('#edit-modal-content').hide();
                                nextBtn.show();
                                patientListContainer.show();
                                $('#update-patient-list').html(patientResponse);
                            },
                            error: function (xhr, textStatus, errorThrown) {
                                $('body').loader().loader('hide');
                                console.error('Failed to fetch patients:', errorThrown);
                            }
                        });
                    }
                },
            });
        });

        $(document).off('click', '.delete-patient').on('click', '.delete-patient', function (e) {
            e.preventDefault();
            let deleteLink = $(this);
            let patientId = deleteLink.data('patient-id');
            let patientListCount = $('.patient-preview').length;
            // Show the modal
            $('#deleteModal').show();

            // Confirm delete button
            $('#confirmDeleteButton').off('click').on('click', function () {
                $('body').loader().loader('show');
                $.ajax({
                    url: '/online-consultation/index/deletepatient',
                    type: 'POST',
                    data: { id: patientId },
                    success: function (response) {
                        $('body').loader().loader('hide');
                        $('#deleteModal').hide();
                        if (response.success) {
                            deleteLink.closest('.patient-preview').remove();
                            patientListCount--;
                            updateNextButtonState();

                            if (patientListCount === 0) {
                                clearFormFields();
                                modalContent.show();
                                patientListContainer.hide();
                                nextBtn.hide();
                            } else {
                                $('#update-patient-list').html(patientResponse);
                                let firstRemainingPatient = $('.patient-preview input[name="patient_entity"]').first();
                                if (firstRemainingPatient.length > 0) {
                                    firstRemainingPatient.prop('checked', true);
                                    $('#selected-patient-id').val(firstRemainingPatient.val());
                                }
                            }
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function () {
                        $('body').loader().loader('hide');
                        alert('An error occurred while deleting the patient.');
                        $('#deleteModal').hide();
                    }
                });
            });

            // Cancel button hides the modal
            $('#cancelButton').off('click').on('click', function () {
                $('#deleteModal').hide();
            });
        });

        $('#sameAsPhone').on('change', function () {
            const phoneInput = $('#addtelephone').val();
            if (this.checked) {
                $('#addwhatsapp').val(phoneInput).prop('readonly', true);
            } else {
                $('#addwhatsapp').val('').prop('readonly', false);
            }
        });

        $('#addtelephone').on('input', function () {
            if ($('#sameAsPhone').prop('checked')) {
                $('#addwhatsapp').val(this.value);
            }
        });
    }

    function initialize() {
        setupDatePicker();
        showStep(currentStepIndex);
        patientChecker();
        setInitialCheckedPatient();
        updateHiddenPatientId();
        initEventListeners();
        setConsultantType();
        updateNextButtonState();
        checkPatientContainer();
        attachFieldValidation();
        attachFieldset2Validation();
        validateFieldset2();
    }

    function clearFormFields() {
        $('#addcustomer_name').val('');
        $('#addcustomer_age').val('');
        $('#addcustomer_email').val('');
        $('#addcustomer_gender').val('');
        $('#addtelephone').val('');
        $('#addwhatsapp').val('');
        $('#addhouse_no').val('');
        $('#addstreet').val('');
        $('#addarea').val('');
        // $('#addcity').val('');
        $('#addpincode').val('');
        // $('#addregion').val('');
        // $('#addregion_id').val('');
        $('#addcountry_id').val('IN');
    }

    function resetFormState() {
        modalContent.hide();
        patientListContainer.show();
        nextBtn.show();
        nextBtn.css('visibility', 'visible');
    }

    $(document).ready(function () {
        setTimeout(() => {
            $('body').loader().loader('hide');
            initialize();
        }, 2100);
        setTimeout(() => {
            $('body').loader().loader('show');
        }, 500);
    });
});
