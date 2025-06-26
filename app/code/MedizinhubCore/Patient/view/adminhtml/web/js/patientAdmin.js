require([
    'jquery',
    'mage/mage',
    'Magento_Ui/js/modal/modal'
], function ($, mage, modal) {
    'use strict';

    $(document).ready(function () {

        $(document).on('click', '.thumbnail-image', function () {
            var imgUrl = $(this).data('image-url');
            console.log(imgUrl);
            var modalHtml = `
                <div class="image-modal">
                    <div class="modal-content">
                        <span class="close-modal">&times;</span>
                        <img src="${imgUrl}" style="width:100%;">
                    </div>
                </div>`;
            $('body').append(modalHtml);
            $('.image-modal').show();
        });

        $(document).on('click', '.close-modal', function () {
            $('.image-modal').remove();
        });

        window.openDetailsModal = function (appointmentId) {
            let button = document.getElementById('details-' + appointmentId);
            let details = JSON.parse(button.getAttribute('data-details'));
            let imgUrl = button.getAttribute('data-image-url');

            let $commentElement = $('#details-modal');

            if ($commentElement.length === 0) {
                let commentHtml = `
                <div id="details-modal" style="display: none;" data-appointment-id="${appointmentId}">
                    <div class="modal-content">
                        <h2>Doctor Comment Details</h2>
                        
                        <!-- Image Upload/View Section -->
                        <div id="image-section">
                            <label>Doctor Prescription:</label>
                            <div id="image-view"></div>
                            <div id="image-upload">
                                <input type="file" id="prescription-upload"/>
                            </div>
                        </div>
        
                        <!-- Comments Section -->
                        <div id="comments-section">
                            <label>Comments:</label>
                            <textarea id="doctor-comments" rows="4" cols="50"></textarea>
                        </div>
        
                        <button class="action-primary" onclick="saveDetails()">Save</button>
                    </div>
                </div>`;

                $('body').append(commentHtml);
                $commentElement = $('#details-modal');
            }

            if (imgUrl) {
                let imgHtml = `<img id="prescription-image" src="${imgUrl}" style="width:100%; max-width: 100%;" alt="Doctor Prescription">`;

                $('#image-view').html(imgHtml).show();
                $('#image-upload').hide();
            } else {
                $('#image-view').hide();
                $('#image-upload').show();
            }

            $('#doctor-comments').val(details.comments || '');

            let options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: 'Doctor Comment Details',
                buttons: [{
                    text: $.mage.__('Close'),
                    class: 'action secondary action-hide-popup',
                    click: function () {
                        $commentElement.modal('closeModal');
                    }
                }]
            };

            if (!$commentElement.data('mageModal')) {
                $commentElement.modal(options);
            }
            $commentElement.modal('openModal');
        };

        window.saveDetails = function () {
            let appointmentId = $('#details-modal').data('appointment-id');
            let comments = $('#doctor-comments').val();
            let fileInput = $('#prescription-upload')[0];
            let formData = new FormData();

            formData.append('appointment_id', appointmentId);
            formData.append('comment', comments);
            formData.append('form_key', window.FORM_KEY);

            if (fileInput.files.length > 0) {
                formData.append('doctor_prescription', fileInput.files[0]);
            }
            console.log(formData);

            $.ajax({
                url: '/admin/patient/appointments/saveComment',
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response.success) {
                        alert(response.message);
                        $('#details-modal').modal('closeModal');
                    } else {
                        alert(response.message);
                    }
                },
                error: function () {
                    alert('An error occurred while saving the details.');
                }
            });
        };


        window.viewAddress = function (patientId) {
            var $addressElement = $('#address-details-' + patientId);
            if (!$addressElement.length || !$addressElement.data('address')) {
                console.error('Address details not found for patient ID:', patientId);
                return;
            }

            var addressDetails = $addressElement.data('address');
            var modalHtml = `
                <strong>Address:</strong> ${addressDetails.house_no}, ${addressDetails.street}, ${addressDetails.area}, ${addressDetails.city} - ${addressDetails.postcode}`;

            var $modalContent = $('<div />').html(modalHtml);

            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: 'Address Details',
                buttons: [{
                    text: $.mage.__('Close'),
                    class: 'action secondary action-hide-popup',
                    click: function () {
                        this.closeModal();
                    }
                }]
            };

            $modalContent.modal(options).modal('openModal');
        };
    });
});
