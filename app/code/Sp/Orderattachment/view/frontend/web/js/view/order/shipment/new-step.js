define(
    [
        'ko',
        'uiComponent',
        'underscore',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Customer/js/model/customer',
        'Sp_Orderattachment/js/view/order/shipment/shipment-attachment',
        'jquery',
        'Magento_Customer/js/customer-data'
    ],
    function (
        ko,
        Component,
        _,
        stepNavigator,
        customer,
        ShipmentAttachment,
        $,
        customerData
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Sp_Orderattachment/order/shipment/attachment-markup'
            },
            isVisible: ko.observable(true),
            attachmentList: ko.observableArray([]),
            isLogedIn: customer.isLoggedIn(),
            stepCode: 'prescription',
            stepTitle: 'Upload Prescription',
            
            checkFileCounts: function() {
                var self = this;
                var FileCheck = sessionStorage.getItem("FileCount");                
                var Add = $('#checkout-prescription-btn');
                var More = $('#checkout-prescription-btns');
                var Consult = $('#consult-fee');
                var Container = $('#attach-container');
                var Required = $('#prescription-required');
                var continue_btn = $('.prescription-continue');

                if (FileCheck >= 1) {
                    Add.css('display', 'none');
                    More.css('display', 'flex');
                    Container.css('margin-top','-12px')
                    Consult.prop('disabled', true);
                    Required.css('display', 'none');
                    More.prop('disabled', false);
                    continue_btn.prop('disabled', false);
                }
                else if (FileCheck >= 4) {
                    More.prop('disabled', true);
                }
                else {
                    More.css('display', 'none');
                    Add.css('display', 'flex');
                    Add.css('margin', '20px 0');
                    Consult.prop('disabled', false);
                    Container.css('margin-top','0px');
                    More.prop('disabled', false);
                    continue_btn.prop('disabled', true);
                }
            },
            
            GetCounts: function() {
                var self = this;
                setTimeout(function() {
                    var FileCheck = sessionStorage.getItem("FileCount");                    
                    var Add = $('#checkout-prescription-btn');
                    var More = $('#checkout-prescription-btns');
                    var Consult = $('#consult-fee');
                    var Container = $('#attach-container');
                    var Required = $('#prescription-required');
                    var continue_btn = $('.prescription-continue');

                    if (FileCheck >= 1) {
                        Add.css('display', 'none');
                        More.css('display', 'flex');
                        Container.css('margin-top','-12px')
                        Consult.prop('disabled', true);
                        Required.css('display', 'none');
                        More.prop('disabled', false);
                        continue_btn.prop('disabled', false);
                    }
                    else if (FileCheck >= 4) {
                        More.prop('disabled', true);
                    }
                    else {
                        More.css('display', 'none');
                        Add.css('display', 'flex');
                        Add.css('margin', '20px 0');
                        Consult.prop('disabled', false);
                        Container.css('margin-top','0px');
                        More.prop('disabled', false);
                        continue_btn.prop('disabled', true);
                    }
                }, 500);
            },
            
            withPrescription: function() {
                var self = this;
                setTimeout(function() {
                    var attachment = $('#order-attachment');
                    var DragChecked = $('#DragClick').prop('checked');
                    var FileCheck = sessionStorage.getItem("FileCount");
                    var continue_btn = $('.prescription-continue');

                    if (FileCheck >= 1) {
                        continue_btn.prop('disabled', false);
                    }
                    else {
                        continue_btn.prop('disabled', true);
                    }

                    if (DragChecked) {
                        sessionStorage.setItem('prescription', 0);
                        attachment.attr('type', 'file');
                        // self.updatePrescriptionStatus(true);
                        document.cookie = 'custom_condition=false; expires=' + new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toUTCString() + '; path=/';
                    }
                }, 1000);
            },

            withPrescription_image: function() {
                var self = this;
                setTimeout(function() {
                    var attachment = $('#order-attachment');
                    var with_prescription_checkbox = $('.with_prescription');
                    with_prescription_checkbox.prop('checked', true);
                    sessionStorage.setItem('prescription', 0);
                    attachment.attr('type', 'file');
                    // self.updatePrescriptionStatus(true);
                    document.cookie = 'custom_condition=false; expires=' + new Date(Date.now() + 30 * 24 * 60* 60 * 1000).toUTCString() + '; path=/';
                }, 1000);
            },

            withOutPrescription: function() {
                window.location.href = "/doctors";
            },

            // updatePrescriptionStatus: function(isPrescriptionRequired){
            //     var self = this;
            //     var cart = customerData.get('cart')();
            //     console.log('Full cart object:', cart);

            //     var cartId = cart && cart.items && cart.items.length > 0 ? cart.items[0].item_id : null;
            //     console.log('Current Cart ID:', cartId);

            //     if (!cartId) {
            //         console.error('Cart ID is missing');
            //         alert('Your cart is empty or you are not logged in.');
            //         return;
            //     }

            //     $.ajax({
            //         url: '/rest/V1/carts/mine/update-prescription-status',
            //         method: 'POST',
            //         dataType: 'json',
            //         data: JSON.stringify({
            //             cartId: cartId,
            //             isPrescriptionRequired: isPrescriptionRequired
            //         }),
            //         contentType: 'application/json',
            //         success: function(response) {
            //             if (response.success) {
            //                 console.log('Prescription status updated successfully');
            //                 console.log('New consultation fee:', response.consultation_fee);
            //                 $('.consultation-fee').text(response.consultation_fee || 'N/A');
            //             } else {
            //                 console.log('Failed to update prescription status');
            //             }
            //         },
            //         error: function(xhr, status, error) {
            //             console.error('API call failed:', error);
            //             alert('There was an error updating the prescription status. Please try again.');
            //         }
            //     });
            // },

            default_prescription: function(refresh) {
                var self = this;
                setTimeout(function() {
                    var default_prescription = sessionStorage.getItem('prescription');
                    var without_prescription_checkbox = $('.without_prescription');
                    var with_prescription_checkbox = $('.with_prescription');
                    var attachment = $('#order-attachment');
                    var isChecked = $('#consult-fee').prop('checked');

                    if (!isChecked && !refresh) {
                        attachment.attr('type', 'file');
                    }else{
                        attachment.attr('type', 'file');
                    }

                    if(default_prescription){
                        if(default_prescription === '1') {
                            attachment.attr('type', 'file');
                            without_prescription_checkbox.prop('checked', true);
                            document.cookie = 'custom_condition=true; expires=' + new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toUTCString() + '; path=/';
                        }else if (default_prescription === '0') {
                            attachment.attr('type', 'file');
                            with_prescription_checkbox.prop('checked', true);
                            document.cookie = 'custom_condition=false; expires=' + new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toUTCString() + '; path=/';
                            var FileCheck = sessionStorage.getItem("FileCount");
                            var continue_btn = $('.prescription-continue');
                            if (FileCheck >= 1) {
                                continue_btn.prop('disabled', false);
                            }
                            else {
                                continue_btn.prop('disabled', true);
                            }
                        }
                    }else{
                        attachment.attr('type', 'file');
                        with_prescription_checkbox.prop('checked', true);
                        document.cookie = 'custom_condition=false; expires=' + new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toUTCString() + '; path=/';
                    }
                }, 1000);
            },

            setImageSource: function() {
                setTimeout(function() {
                    var fullUrl = window.location.href;
                    var baseUrl = fullUrl.split('/').slice(0, 3).join('/');
                    var imageElement = document.getElementById('myImage');
                    var StarimageElement = document.getElementById('StarsImg');
                    if (imageElement && StarimageElement) {
                        imageElement.src = baseUrl + '/static/frontend/ET/base_lite/en_US/Sp_Orderattachment/images/prescription_reference.png';
                        StarimageElement.src = baseUrl + '/static/frontend/ET/base_lite/en_US/Sp_Orderattachment/images/stars.png';
                    } else {
                        console.error('Image element with ID not found.');
                    }
                }, 1000);
            },

            CheckOn: function() {
                $('#order-attachment').trigger('click');
            },

            initialize: function () {
                this._super();
                var self = this;
                var quote = window.checkoutConfig.quoteData;
                this.shipmentAttachmentComponent = new ShipmentAttachment();
                this.allowedExtensions = window.checkoutConfig.spAttachmentExt;
                this.maxFileSize = window.checkoutConfig.spAttachmentSize;
                this.removeItem = window.checkoutConfig.removeItem;
                this.maxFileLimit = window.checkoutConfig.spAttachmentLimit;
                this.invalidExtError = window.checkoutConfig.spAttachmentInvalidExt;
                this.invalidSizeError = window.checkoutConfig.spAttachmentInvalidSize;
                this.invalidLimitError = window.checkoutConfig.spAttachmentInvalidLimit;
                this.uploadUrl = window.checkoutConfig.spAttachmentUpload;
                this.updateUrl = window.checkoutConfig.spAttachmentUpdate;
                this.removeUrl = window.checkoutConfig.spAttachmentRemove;
                this.comment = window.checkoutConfig.spAttachmentComment;
                this.attachments = window.checkoutConfig.attachments;
                this.attachmentTitle = window.checkoutConfig.spAttachmentTitle;
                this.attachmentInfromation = window.checkoutConfig.spAttachmentInfromation;
                this.attachmentList(this.attachments);
                this.files = window.checkoutConfig.totalCount;
                this.prescriptionRequired = ko.observable(this.prescriptionRequired);

                // Process attachments and update thumbnails
                this.attachmentList().forEach(function(attachment) {
                    attachment.thumbnailUrl = ko.computed(function() {
                        var fileExt = attachment.path.split('.').pop().toLowerCase();
                        var fullUrl = window.location.href;
                        var baseUrl = fullUrl.split('/').slice(0, 3).join('/');
                        var iconPath = '/static/frontend/ET/base_lite/en_US/Sp_Orderattachment/images/';

                        if (fileExt === 'pdf') {
                            return baseUrl + iconPath + 'pdf.png';
                        }
                        return attachment.url;
                    });
                });

                // Set FileCount in sessionStorage based on initial attachments
                if (this.attachmentList().length > 0) {
                    sessionStorage.setItem("FileCount", this.attachmentList().length);
                }
                
                // Call methods in proper order
                this.setImageSource();
                this.default_prescription();
                this.GetCounts();
                this.CheckOn();

                if (this.prescriptionRequired()) {
                    // Prescription required logic
                } else {
                    window.location.href = "/checkout/#shipping";
                    setTimeout(function() {
                        sessionStorage.setItem('prescription', 0);
                        document.cookie = 'custom_condition=false; expires=' + new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toUTCString() + '; path=/';
                        $('.upload-prescription').css('display', 'none');
                        $('.opc-progress-bar-item._active').addClass('non-prescription-product');
                    }, 1000);
                }

                stepNavigator.registerStep(
                    this.stepCode,
                    null,
                    this.stepTitle,
                    this.isVisible,
                    _.bind(this.navigate, this),
                    9
                );
                this.navigationExecuted = false;
                return this;
            },
            
            navigateTo: function () {
                console.log("Navigate");
            },
            
            showRowLoader: function() {
                $('body').trigger('processStart');
                this.checkFileCounts();
            },

            hideRowLoader: function() {
               $('body').trigger('processStop');
            },
            
            processingFile: function(file) {
                var error = this.validateFile(file);
                var More = $('#checkout-prescription-btns');
                if (error) {
                    this.addError(error);
                } else {
                    if (this.files >= this.maxFileLimit) {
                        More.prop('disabled', true);
                        return;
                    }
                    if (this.files + 1 > this.maxFileLimit) {
                        More.prop('disabled', true);
                        return;
                    }
                    var uniq = Math.random().toString(32).slice(2);
                    this.upload(file, uniq);
                }
            },

            upload: function(file, pos) {
                var formAttach = new FormData(),
                    self = this;

                this.showRowLoader();
                $('.loader').show();
                formAttach.append($('#order-attachment').attr("name"), file);
                if (window.FORM_KEY) {
                    formAttach.append('form_key', window.FORM_KEY);
                }
                $.ajax({
                    url: this.uploadUrl,
                    type: "POST",
                    data: formAttach,
                    success: function(data) {
                        var result = JSON.parse(data);

                        result.thumbnailUrl = ko.computed(function() {
                            var fileExt = result.path.split('.').pop().toLowerCase();
                            var fullUrl = window.location.href;
                            var baseUrl = fullUrl.split('/').slice(0, 3).join('/');
                            var iconPath = '/static/frontend/ET/base_lite/en_US/Sp_Orderattachment/images/';

                            if (fileExt === 'pdf') {
                                return baseUrl + iconPath + 'pdf.png';
                            }
                            return result.url;
                        });

                        self.attachments.push(result);
                        self.attachmentList(self.attachments);
                        if (result['attachment_count']) {
                            self.files = result['attachment_count'];
                            sessionStorage.setItem("FileCount", self.files);
                        }
                        $('.with_prescription').prop('checked', true);
                        self.hideRowLoader();
                        self.checkFileCounts();

                        if (self.files >= 5) {
                            $('#checkout-prescription-btns').prop('disabled', true);
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        self.addError(thrownError);
                        self.hideRowLoader();
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
            },

            updateComment: function(id, hash, commentElement) {
                var comment = $('#'+commentElement).val();

                if($.trim(comment))
                {
                    var attachParams = {
                        'attachment': id,
                        'hash': hash,
                        'comment': comment,
                        'form_key': window.FORM_KEY
                    },
                    self = this;
                    this.showRowLoader();
                    $('.loader').show();

                    $.ajax({
                        url: this.updateUrl,
                        type: "post",
                        data: $.param(attachParams),
                        success: function(data) {
                            var result = JSON.parse(data);
                            if (!result.success) {
                                self.addError(result.error);
                            }else{
                                if(result['attachment_count']){
                                    self.files = result['attachment_count'];
                                    sessionStorage.setItem("FileCount", self.files);
                                }
                            }
                            self.hideRowLoader();
                            self.checkFileCounts();
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            self.addError(thrownError);
                            self.hideRowLoader();
                        }
                    });
                }
            },
            
            deleteFile: function(id, hash) {
                var attachParams = {
                    'attachment': id,
                    'hash': hash,
                    'form_key': window.FORM_KEY
                };
                var self = this;
                self.showRowLoader();
                $('.loader').show();

                $.ajax({
                    url: this.removeUrl,
                    type: "post",
                    data: $.param(attachParams),
                    success: function(data) {
                        var result = JSON.parse(data);
                        if (result.success) {
                            if(result.attachment_count == 0){
                                sessionStorage.setItem("FileCount", 0);
                                self.checkFileCounts();
                            }else{                               
                                self.files = result.attachment_count;
                                sessionStorage.setItem("FileCount", result.attachment_count);
                                self.checkFileCounts();
                            }
                            $('div.sp-attachment-row[rel="' + hash + '"]').remove();
                        }
                        self.hideRowLoader();
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        self.addError(thrownError);
                        self.hideRowLoader();
                    }
                });
            },

            validateFile: function(file) {
                if (!this.checkFileExtension(file)) {
                    return this.invalidExtError;
                }
                if (!this.checkFileSize(file)) {
                    alert('File size exceeds the limit of 5MB.'); // Display alert message
                    return this.invalidSizeError;
                }
                return null;
            },

            checkFileExtension: function(file) {
                var fileExt = file.name.split(".").pop().toLowerCase();
                var allowedExt = this.allowedExtensions.split(",");
                if (-1 == $.inArray(fileExt, allowedExt)) {
                    return false;
                }
                return true;
            },

            checkFileSize: function(file) {
                // Check if file size is less than 5MB (5 * 1024 * 1024 bytes)
                if ((file.size / 1024) > 5120) {
                    return false; // File size exceeds 5MB
                }
                return true; // File size is within the limit
            },

            addError: function(error) {
                var html = null;
                html = '<div class="sp-attachment-error danger"><strong class="close">X</strong>'+ error +'</div>';
                $('.attachment-container').before(html);
                $(".sp-attachment-error .close").on('click', function() {
                    var el = $(this).closest("div");
                    if (el.hasClass('sp-attachment-error')) {
                        $(el).slideUp('slow', function() {
                            $(this).remove();
                        });
                    }
                });
            },

            getTitle: function() {
                return this.attachmentTitle;
            },
            
            getAttachmentInfo: function() {
                return this.attachmentInfromation;
            },
            
            selectFiles: function() {
                $('#order-attachment').trigger('click');
            },

            fileUpload: function(data, e) {
                var file = e.target.files;
                for (var i = 0; i < file.length; i++) {
                    this.processingFile(file[i]);
                }
            },

            dragEnter: function(data, event) {},

            dragOver: function(data, event) {},

            drop: function(data, event) {
                $('.order-attachment-drag-area').css("border", "2px dashed #1979c3");
                var droppedFiles = event.originalEvent.dataTransfer.files;
                for (var i = 0; i < droppedFiles.length; i++) {
                    this.processingFile(droppedFiles[i]);
                }
            },
            /**
             * The navigate() method is responsible for navigation between checkout step
             * during checkout. You can add custom logic, for example some conditions
             * for switching to your custom step
             */
            navigate: function () {
                if (!this.navigationExecuted) {
                    this.navigateToNextStep();
                    this.navigationExecuted = true;
                }
            },

            /**
             * @returns void
             */
            navigateToNextStep: function () {
                var fileCount = sessionStorage.getItem("FileCount");
                if (fileCount > 0) {
                    var check = document.getElementById('prescription-required');
                    check.style.display = 'none';
                    stepNavigator.next();
                }
                else {
                    var check = document.getElementById('prescription-required');
                    check.style.display = 'flex';
                }
            }
        });
    }
);