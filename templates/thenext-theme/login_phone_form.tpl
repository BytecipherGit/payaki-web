{OVERALL_HEADER}
<div id="titlebar">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>{LANG_LOGIN}</h2>
                <!-- Breadcrumbs -->
                <nav id="breadcrumbs">
                    <ul>
                        <li><a href="{LINK_INDEX}">{LANG_HOME}</a></li>
                        <li>{LANG_LOGIN}</li>
                    </ul>
                </nav>

            </div>
        </div>
    </div>
</div>
<div class="container">
    <div class="row">
        <div class="col-xl-5 margin-0-auto">
            <div class="login-register-page">


                <!-- Form -->
                <div id="mobile-form">
                    <div class="reset-form d-block">
                        <div class="welcome-text">
                            <h3>{LANG_LOGIN_WITH_PHONE}</h3>
                            <span id="mobile-status">{LANG_OTP_NOTIFY_1}</span>
                        </div>
                        <form action="mobile_verify" name="pv-form" id="pv-form" method="post">
                            <input required class="input-text with-border" id="verify-mobile" name="mobile_no" type="phone" placeholder="{LANG_PHONE_NO}" >
                            <input type="hidden" value="1" name="submit_mobile"/>
                            <button class="button full-width button-sliding-icon ripple-effect margin-top-20" id="mobile-submit" type="submit" name="submit">{LANG_SEND_OTP} <i class="icon-feather-arrow-right"></i></button>
                        </form>
                    </div>
                    <div class="reset-confirmation d-none">
                        <div class="welcome-text">
                            <h3>{LANG_OTP_VERIFICATION}</h3>
                            <span id="otp-status">{LANG_OTP_NOTIFY_2}: <span class="otp_mobile"></span></span>
                        </div>
                        <form action="otp_verify" name="otp-form" id="otp-form" method="post">
                            <div class="otp-form-content">
                                <div class="input-with-icon-left">
                                    <i class="la la-user"></i>
                                    <input required class="input-text with-border" name="otp_code" type="phone" placeholder="{LANG_OTP_CODE}">
                                </div>
                                <input type="hidden" value="" name="mobile_no" class="otp_mobile_no"/>
                                <input type="hidden" value="1" name="submit_otp"/>
                                <button class="button full-width button-sliding-icon ripple-effect margin-top-20" id="otp-submit" type="submit" name="submit">{LANG_VERIFY_OTP} <i class="icon-feather-arrow-right"></i></button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<div class="margin-top-70"></div>
<!-- Verify Mobile Number popup / End -->
<link href="{SITE_URL}includes/assets/plugins/intlTelInput/css/intlTelInput.css" media="all" rel="stylesheet" type="text/css"/>
<script src="{SITE_URL}includes/assets/plugins/intlTelInput/js/intlTelInput.min.js"></script>
<script src="{SITE_URL}includes/assets/plugins/intlTelInput/js/intlTelInput.utils.js"></script>
<script src="{SITE_URL}includes/assets/plugins/intlTelInput/js/custom.js"></script>
{OVERALL_FOOTER}