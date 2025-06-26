define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Magento_Customer/js/customer-data',
    'mage/storage',
    'mage/translate',
    'mage/mage',
    'jquery/ui'
], function ($, modal, customerData, storage, $t) {
    'use strict';

    $.widget('cinovic.customerAuthenticationPopup', {
        options: {
            login: '#customer-popup-login',
            nextRegister: '#customer-popup-registration',
            register: '#customer-popup-register',
            prevLogin: '#customer-popup-sign-in',
            otp: '#customer-popup-otp',
            closeButton: '.customer-popup .action-close[data-role="closeBtn"]'
        },
        _timerId: null,

        /**
         * Widget initialization
         * @private
         */
        _create: function () {
            var self = this,
                authentication_options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    title: this.options.popupTitle,
                    buttons: false,
                    modalClass: 'customer-popup',
                    closed: function () {
                    }
                };

            modal(authentication_options, this.element);

            setTimeout(function () {
                self._addCloseButtonClass();
            }, 500);

            this._bindEvents();
            this._ajaxSubmit();
        },

        /**
         * Add class to close button
         * @private
         */
        _addCloseButtonClass: function () {
            var closeButton = this.element.find(this.options.closeButton);
            closeButton.addClass('customer-popup-close');
        },

        /**
         * Bind events
         * @private
         */
        _bindEvents: function () {
            var self = this;

            $('body').on('click', '.customer-login-link, ' + self.options.prevLogin, function () {
                $('.modal-title').css('display', 'none');
                $(self.options.register).modal('closeModal');
                $(self.options.otp).modal('closeModal');
                $(self.options.login).modal('openModal');

                $('#otp').val('');
                $('#phone').val('');
                $('.cuotp_login_lastname').val('');
                $('.cuotp_login_firstname').val('');
                $('.cuotp_login_email').val('');
                return false;
            });

            $('body').on('click', '.customer-register-link, ' + self.options.nextRegister, function () {
                $('.modal-title').css('display', 'block');
                $(self.options.otp).modal('closeModal');
                $(self.options.login).modal('closeModal');
                $(self.options.register).modal('openModal');

                $('#otp').val('');
                $('#phone').val('');
                $('.cuotp_login_lastname').val('');
                $('.cuotp_login_firstname').val('');
                $('.cuotp_login_email').val('');
                return false;
            });

            $('body').on('click', self.options.closeButton, function () {
                self._closePopup();
            });
        },

        /**
         * Close the popup
         * @private
         */
        _closePopup: function () {
            this.element.modal('closeModal');
        },

         /**
         * Close the popup
         * @private
         */
         EmailOtp: function (mobileNumber) {
            var self = this;
            var container = document.createElement("div");
            container.style.marginTop = "30px";
            container.id = "customer-popup-email";
            container.className = "customer-popup-register row";
            container.style.display = "flex";
            container.innerHTML = `
                <div class="col-md-7 otp_image">
                    <img class="otp-log-image" src="http://local.medizinhub.com/static/version1739426705/frontend/ET/base_lite/en_US/Cinovic_Otplogin/images/mobile.png" alt="OTP Login">
                </div>
                <div class="col-md-5">
                    <form class="form-create-account" action="javascript:void(0);" method="post" id="customer-popup-form-email" enctype="multipart/form-data" autocomplete="off" data-mage-init='{"validation":{}}'>
                        <input type="hidden" name="redirect_url" value="">
                        <div class="messages"></div>
                        <fieldset class="fieldset create account">
                            <div class="login-withmobile">
                                <p class="otp-register-title">Sign Up Using Email</p>
                            </div>
                            <div class="field required">
                                <div class="row" style="margin-top: 15px;">
                                    <div class="col-md-12">
                                        <div class="control">
                                            <input
                                                type="email"
                                                name="email"
                                                placeholder="Enter Email Address"
                                                id="customer-email-otp"
                                                required
                                                aria-required="true"
                                                class="input-text"
                                                onfocus="this.style.boxShadow = '0 0 0 0 #fff';"
                                            > <p class="messages-email" style="color: red; display: none;"></p>
                                            <div class="policy-otp">
                                            <p class="otp-information">An OTP will be sent to your email and WhatsApp</p>
                                            </div>
                                        </div>
                                        <div class="otp-submit">
                                            <button class="btn btn-success" type="submit" id="otp-email-account">Continue</button>
                                        </div>
                                    </div>
                                </div>
                                <p class="otp-agree">By signing up, I agree to the <a href="/privacy-policy" target="_blank" class="otp-privacy">Privacy policy</a><br> and <a href="/terms-and-conditions" target="_blank" class="otp-terms">Terms and Condition</a></p>
                            </div>
                        </fieldset>
                    </form>
                </div>
            `;
            document.body.appendChild(container);
            var asideElement = document.querySelector('aside');
            if (asideElement) {
                asideElement.classList.add('customer-popup');
            }

            modal({
                type: 'popup',
                responsive: true,
                innerScroll: true,
                buttons: false
            }, $(container));
            $(container).modal('openModal');
            $('#customer-popup-form-email').on('submit', function (e) {
                e.preventDefault();
                var email = $('#customer-email-otp').val();
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!email) {
                    $('.messages-email').text('Please enter your email address').css('display', 'block');
                    return;
                } else if (!emailRegex.test(email)) {
                    $('.messages-email').text('Please enter a valid email address').css('display', 'block');
                    return;
                } else {
                    $('.messages-email').text('').css('display', 'none');
                }
                $.ajax({
                    url: '/otplogin/account/emailotp',
                    type: 'POST',
                    showLoader: true,
                    data: {
                        email: email,
                        mobileNumber: mobileNumber
                    },
                    success: function (response) {
                        if (response.success) {
                            $(container).modal('closeModal');
                            $(self.options.otp).modal('openModal');
                        }
                    },
                    error: function (xhr, status, error) {
                        $('.messages').text('Failed to send OTP. Please try again.').css('color', 'red');
                        $('#otp-create-account').prop('disabled', false).text('Continue');
                    }
                });
            });
        },

        /**
         * Handle close button click
         * @private
         */
        _handleCloseButtonClick: function () {
            var customerId = sessionStorage.getItem("OtpCustomer");
            if (customerId) {
                var deleteurl = "/otplogin/account/DeleteCustomer";
                $.ajax({
                    url: deleteurl,
                    data: { customer_id: customerId },
                    type: 'POST',
                    async: false,
                    success: function (response) {
                        console.log('Customer deletion initiated successfully.');
                        sessionStorage.removeItem("OtpCustomer");
                    },
                    error: function (xhr, status, error) {
                        console.error('Error initiating customer deletion:', error);
                    }
                });
            } else {
                console.error('Customer ID not found.');
            }
        },

        /**
         * Submit data by Ajax
         * @private
         */
        _ajaxSubmit: function () {
            var self = this,
                form = this.element.find('form'),
                inputElement = form.find('input');

            inputElement.keyup(function (e) {
                self.element.find('.messages').html('');
            });

            form.submit(function (e) {
                if (form.validation('isValid')) {
                    sessionStorage.setItem("Expiry_time", 180);
                    if (form.hasClass('form-create-account')) {
                        $.ajax({
                            url: $(e.target).attr('action'),
                            data: $(e.target).serializeArray(),
                            showLoader: true,
                            type: 'POST',
                            dataType: 'json',
                            success: function (response) {
                                $(self.options.register).modal('closeModal');
                                var mobileNumber = response.mobile_number;
                                var StatusResponse = response.newuser;
                                if (StatusResponse == true) {
                                    self.EmailOtp(mobileNumber);
                                } else {
                                    $(self.options.otp).modal('openModal');
                                }
                                $('<div class="message message-success success"><div>' + response.message + '</div></div>').appendTo('.otpmessages');
                                sessionStorage.setItem("OtpCustomer", response.customer_id);
                                sessionStorage.setItem("customer_otp_status", response.customer_status);
                                var CustomerEmail = response.email;
                                var displayMessage;
                                if (mobileNumber) {
                                    var countryCode = mobileNumber.substring(0, 2);
                                    var mainNumber = mobileNumber.substring(2);
                                    var formattedNumber = "+" + countryCode + " " + mainNumber;
                                    displayMessage = 'OTP sent to ' + formattedNumber;
                                } else if (CustomerEmail) {
                                    displayMessage = 'OTP sent to ' + CustomerEmail;
                                } else {
                                    displayMessage = 'Missing information: Mobile number and email not provided';
                                }
                                if (displayMessage.length > 26) {
                                    displayMessage = displayMessage.substring(0, 26) + "...";
                                }
                                $('#provide-number').html(displayMessage);
                                sessionStorage.setItem("Expiry_time", response.expire_time);
                                self.startTimer();
                            },
                            error: function () {
                                $('<div class="message message-error error"><div>' + response.message + '</div></div>').appendTo('.messages');
                            }
                        });
                    } else if (form.hasClass('form-otp')) {
                        $.ajax({
                            url: $(e.target).attr('action'),
                            data: $(e.target).serializeArray(),
                            showLoader: true,
                            type: 'POST',
                            dataType: 'json',
                            success: function (response) {
                                $(self.options.otp).modal('closeModal');
                                $(self.options.login).modal('openModal');
                                $('<div class="message message-success success"><div>' + response.message + '</div></div>').appendTo('.loginmessages');
                            },
                            error: function () {
                                $('<div class="message message-error error"><div>' + response.message + '</div></div>').appendTo('.messages');
                            }
                        });
                    } else {
                        var submitData = {},
                            formDataArray = $(e.target).serializeArray();
                        formDataArray.forEach(function (entry) {
                            submitData[entry.name] = entry.value;
                        });
                        $('body').loader().loader('show');
                        storage.post(
                            $(e.target).attr('action'),
                            JSON.stringify(submitData)
                        ).done(function (response) {
                            $('body').loader().loader('hide');
                        }).fail(function () {
                            $('body').loader().loader('hide');
                            self._showFailingMessage();
                        });
                    }
                }
                return false;
            });
        },

        /**
         * Start the OTP timer
         * @private
         */
        startTimer: function () {
            if (this._timerId) {
                clearTimeout(this._timerId);
            }
            let expiryTime = sessionStorage.getItem("Expiry_time");
            if (!expiryTime) {
                expiryTime = 180; // Default to 180 seconds if not set
            }
            this.timer(expiryTime);
        },

        /**
         * Timer function for OTP countdown
         * @private
         */
        timer: function (remaining) {
            $('.resendotp-container').hide();
            $('.resendotp').hide();
            var m = Math.floor(remaining / 60);
            var s = remaining % 60;

            m = m < 10 ? '0' + m : m;
            s = s < 10 ? '0' + s : s;
            document.getElementById('timer').innerHTML = m + ':' + s;
            remaining -= 1;
            if (remaining >= 0) {
                this._timerId = setTimeout(() => {
                    this.timer(remaining);
                }, 1000);
                return;
            }
            $('.resendotp-container').css('display', 'inline-block');
            $('.resendotp').css('display', 'inline');
        },


        /**
         * Display messages on the screen
         * @private
         */
        _displayMessages: function (className, message) {
            $('<div class="message ' + className + '"><div>' + message + '</div></div>').appendTo(this.element.find('.messages'));
        },

        /**
         * Showing response results
         * @private
         * @param {Object} response
         * @param {String} locationHref
         */
        _showResponse: function (response) {
            var self = this,
                timeout = 800;
            this.element.find('.messages').html('');
            if (response.errors) {
                this._displayMessages('message-error error', response.message);
            } else {
                this._displayMessages('message-success success', response.message);
            }
            this.element.find('.messages .message').show();
            setTimeout(function () {
                if (!response.errors) {
                    self.element.modal('closeModal');
                }
            }, timeout);
        },

        /**
         * Show the failing message
         * @private
         */
        _showFailingMessage: function () {
            this.element.find('.messages').html('');
            this._displayMessages('message-error error', $t('An error occurred, please try again later.'));
            this.element.find('.messages .message').show();
        }
    });

    return $.cinovic.customerAuthenticationPopup;
});

