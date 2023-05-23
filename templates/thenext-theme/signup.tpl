{OVERALL_HEADER}
<div id="titlebar">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>{LANG_REGISTER}</h2>
                <!-- Breadcrumbs -->
                <nav id="breadcrumbs">
                    <ul>
                        <li><a href="{LINK_INDEX}">{LANG_HOME}</a></li>
                        <li>{LANG_REGISTER}</li>
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
                <!-- Welcome Text -->
                <div class="welcome-text">
                    <h3 style="font-size: 26px;">{LANG_LETS_CREATE_ACC}</h3>
                    <span>{LANG_ALREADY_HAVE_ACC} <a href="{LINK_LOGIN}">{LANG_LOGIN}</a></span>
                </div>
                IF('{FACEBOOK_APP_ID}'!='facebook' || '{GOOGLE_APP_ID}'!='google'){
                <div class="social-login-buttons">
                    IF('{FACEBOOK_APP_ID}'!='facebook'){
                    <button class="facebook-login ripple-effect" onclick="fblogin()"><i class="fa fa-facebook"></i> {LANG_LOGIN_VIA_FACEBOOK}
                    </button>
                    {:IF}

                    IF('{GOOGLE_APP_ID}'!='google'){
                    <button class="google-login ripple-effect" onclick="gmlogin()"><i class="fa fa-google"></i> {LANG_LOGIN_VIA_GOOGLE}
                    </button>
                    {:IF}
                </div>
                <div class="social-login-separator"><span>{LANG_OR}</span></div>
                {:IF}
                <form method="post" name="register-form" id="register-form" action="#" accept-charset="UTF-8" enctype="multipart/form-data">
                    <div class="form-group">
                        <div class="input-with-icon-left">
                            <i class="la la-user"></i>
                            <input type="text" class="input-text with-border" placeholder="{LANG_FULL_NAME}" value="{NAME_FIELD}" id="name" name="name" onBlur="checkAvailabilityName()" required/>
                        </div>
                        <span id="name-availability-status">IF("{NAME_ERROR}"!=""){ {NAME_ERROR} {:IF}</span>
                    </div>
                    <div class="form-group">
                        <div class="input-with-icon-left">
                            <i class="la la-user"></i>
                            <input type="text" class="input-text with-border" placeholder="{LANG_USERNAME}" value="{USERNAME_FIELD}" id="Rusername" name="username" onBlur="checkAvailabilityUsername()" required/>
                        </div>
                        <span id="user-availability-status">IF("{USERNAME_ERROR}"!=""){ {USERNAME_ERROR} {:IF}</span>
                    </div>
                    

                    <div class="form-group">
                        <div class="input-with-icon-left">
                            <i class="la la-envelope"></i>
                            <input type="text" class="input-text with-border" placeholder="{LANG_EMAIL}" value="{EMAIL_FIELD}" name="email" id="email" onBlur="checkAvailabilityEmail()" required/>
                        </div>
                        <span id="email-availability-status">IF("{EMAIL_ERROR}"!=""){ {EMAIL_ERROR} {:IF}</span>
                    </div>
                    
                    IF("{SMS_VERIFY_MODE}"=="1"){
                    <div class="form-group">
                        <div>
                            <input type="phone" class="input-text with-border" placeholder="{LANG_PHONE_NO}" value="{PHONE_FIELD}" id="verify-mobile" name="phone" onBlur="checkAvailabilityPhone()" required/>
                        </div>
                        <span id="phone-availability-status">IF("{PHONE_ERROR}"!=""){ {PHONE_ERROR} {:IF}</span>
                    </div>
                    {:IF}
                    <div class="form-group">
                        <div class="input-with-icon-left">
                            <i class="la la-unlock"></i>
                            <input type="password" class="input-text with-border" placeholder="{LANG_PASSWORD}" id="Rpassword" name="password" onBlur="checkAvailabilityPassword()" required/>
                        </div>
                        <span id="password-availability-status">IF("{PASSWORD_ERROR}"!=""){ {PASSWORD_ERROR} {:IF}</span>
                    </div>

                    <div class="form-group">
                        <div class="input-with-icon-left">
                            <select name="id_proof_type" id="idprooftype" class="form-control with-border">
                            <option value="">Select Identification Type</option>
                            <option value="State identification (ID) card" IF("{IDPROOFTYPE_FIELD}"=="State identification (ID) card"){ "selected" {:IF}>State identification (ID) card</option>
                            <option value="Driver license" IF("{IDPROOFTYPE_FIELD}"=="Driver license"){ "selected" {:IF}>Driver license</option>
                            <option value="US passport or passport card" IF("{IDPROOFTYPE_FIELD}"=="US passport or passport card"){ "selected" {:IF}>US passport or passport card</option>
                            <option value="US military card" IF("{IDPROOFTYPE_FIELD}"=="US military card"){ "selected" {:IF}>US military card</option>
                            <option value="Military dependents ID card" IF("{IDPROOFTYPE_FIELD}"=="Military dependents ID card"){ "selected" {:IF}>Military dependents ID card</option>
                            <option value="Permanent Resident Card" IF("{IDPROOFTYPE_FIELD}"=="Permanent Resident Card"){ "selected" {:IF}>Permanent Resident Card</option>
                            <option value="Certificate of Citizenship" IF("{IDPROOFTYPE_FIELD}"=="Certificate of Citizenship"){ "selected" {:IF}>Certificate of Citizenship</option>
                            <option value="Certificate of Naturalization" IF("{IDPROOFTYPE_FIELD}"=="Certificate of Naturalization"){ "selected" {:IF}>Certificate of Naturalization</option>
                            <option value="Employment Authorization Document" IF("{IDPROOFTYPE_FIELD}"=="Employment Authorization Document"){ "selected" {:IF}>Employment Authorization Document</option>
                            <option value="Foreign passport" IF("{IDPROOFTYPE_FIELD}"=="Foreign passport"){ "selected" {:IF}>Foreign passport</option>
                            </select>
                        </div>
                        <span id="idprooftype-availability-status">IF("{IDPROOFTYPE_ERROR}"!=""){ {IDPROOFTYPE_ERROR} {:IF}</span>
                    </div>
                    <div class="form-group">
                        <div class="input-with-icon-left">
                            <input type="text" class="input-text with-border" style="padding-left: 25px !important;" placeholder="ID Proof Number" id="idproofnumber" value="{IDPROOFNUMBER_FIELD}" name="id_proof_number" required/>
                        </div>
                        <span id="idproofnumber-availability-status">IF("{IDPROOFNUMBER_ERROR}"!=""){ {IDPROOFNUMBER_ERROR} {:IF}</span>
                    </div>
                    <div class="form-group">
                    <label>Upload ID Proof Document</label>
                        <input type="file" class="form-control" id="idproof" name="id_proof" style="padding: 0px 0px !important; height: auto !important;" required/>
                        <span id="idproof-availability-status">IF("{IDPROOF}"!=""){ {IDPROOF} {:IF}</span>
                    </div>

                    <div class="form-group">
                        <div class="input-with-icon-left">
                            <select name="address_proof_type" id="addressprooftype" class="form-control with-border">
                            <option value="">Select Address Verification Type</option>
                            <option value="Utility bill" IF("{ADDRESSPROOFTYPE_FIELD}"=="Utility bill"){ "selected" {:IF}>Utility bill</option>
                            <option value="Cable TV or internet bill" IF("{ADDRESSPROOFTYPE_FIELD}"=="Cable TV or internet bill"){ "selected" {:IF}>Cable TV or internet bill</option>
                            <option value="Telephone bill" IF("{ADDRESSPROOFTYPE_FIELD}"=="Telephone bill"){ "selected" {:IF}>Telephone bill</option>
                            <option value="Bank statement" IF("{ADDRESSPROOFTYPE_FIELD}"=="Bank statement"){ "selected" {:IF}>Bank statement</option>
                            <option value="Property tax bill" IF("{ADDRESSPROOFTYPE_FIELD}"=="Property tax bill"){ "selected" {:IF}>Property tax bill</option>
                            <option value="Mortgage statement" IF("{ADDRESSPROOFTYPE_FIELD}"=="Mortgage statement"){ "selected" {:IF}>Mortgage statement</option>
                            </select>
                        </div>
                        <span id="addressprooftype-availability-status">IF("{ADDRESSPROOFTYPE_ERROR}"!=""){ {ADDRESSPROOFTYPE_ERROR} {:IF}</span>
                    </div>
                    <div class="form-group">
                        <div class="input-with-icon-left">
                            <input type="text" class="input-text with-border" style="padding-left: 25px !important;" placeholder="Address Proof Number" id="addproofnumber" value="{ADDRESSPROOFNUMBER_FIELD}" name="address_proof_number" required/>
                        </div>
                        <span id="addproofnumber-availability-status">IF("{ADDRESSPROOFNUMBER_ERROR}"!=""){ {ADDRESSPROOFNUMBER_ERROR} {:IF}</span>
                    </div>
                    <div class="form-group">
                        <label>Upload Address Proof Document</label>
                        <input type="file" class="form-control" id="addressproof" name="address_proof" style="padding: 0px 0px !important; height: auto !important;" required/>
                        <span id="addressproof-availability-status">IF("{ADDRESSPROOF_ERROR}"!=""){ {ADDRESSPROOF_ERROR} {:IF}</span>
                    </div>
                    <div class="form-group">
                        <div class="text-center">
                            IF("{RECAPTCHA_MODE}"=="1"){
                            <div style="display: inline-block;" class="g-recaptcha" data-sitekey="{RECAPTCHA_PUBLIC_KEY}"></div>
                            {:IF}
                        </div>
                        <span>IF("{RECAPTCH_ERROR}"!=""){ {RECAPTCH_ERROR} {:IF}</span>
                    </div>
                    <span class="text-center">{LANG_BY_CLICK_REGISTER} <a href="{TERMCONDITION_LINK}" target="_blank">{LANG_TERM_CON}</a> </span>
                    <button class="button full-width button-sliding-icon ripple-effect margin-top-10" name="submit" type="submit">{LANG_REGISTER} <i class="icon-feather-arrow-right"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="margin-top-70"></div>
<script src='https://www.google.com/recaptcha/api.js'></script>
<!-- Verify Mobile Number popup / End -->
<link href="{SITE_URL}includes/assets/plugins/intlTelInput/css/intlTelInput.css" media="all" rel="stylesheet" type="text/css"/>
<script src="{SITE_URL}includes/assets/plugins/intlTelInput/js/intlTelInput.min.js"></script>
<script src="{SITE_URL}includes/assets/plugins/intlTelInput/js/intlTelInput.utils.js"></script>
<script src="{SITE_URL}includes/assets/plugins/intlTelInput/js/custom.js"></script>
<script>

    var error = "";

    function checkAvailabilityName() {
        $("#loaderIcon").show();
        jQuery.ajax({
            url: ajaxurl,
            data : {
                action: 'check_availability',
                name: $("#name").val()
            },
            type: "POST",
            success: function (data) {
                if (data != "success") {
                    error = 1;
                    $("#name").removeClass('has-success');
                    $("#name-availability-status").html(data);
                    $("#name").addClass('has-error mar-zero');
                }
                else {
                    error = 0;
                    $("#name").removeClass('has-error mar-zero');
                    $("#name-availability-status").html("");
                    $("#name").addClass('has-success');
                }
                $("#loaderIcon").hide();
            },
            error: function () {
            }
        });
    }
    function checkAvailabilityUsername() {
        var $item = $("#Rusername").closest('.form-group');
        $("#loaderIcon").show();
        jQuery.ajax({
            url: ajaxurl,
            data : {
                action: 'check_availability',
                username: $("#Rusername").val()
            },
            type: "POST",
            success: function (data) {
                if (data != "success") {
                    error = 1;
                    $item.removeClass('has-success');
                    $("#user-availability-status").html(data);
                    $item.addClass('has-error');
                }
                else {
                    error = 0;
                    $item.removeClass('has-error');
                    $("#user-availability-status").html("");
                    $item.addClass('has-success');
                }
                $("#loaderIcon").hide();
            },
            error: function () {
            }
        });
    }
    function checkAvailabilityEmail() {
        $("#loaderIcon").show();
        jQuery.ajax({
            url: ajaxurl,
            data : {
                action: 'check_availability',
                email: $("#email").val()
            },
            type: "POST",
            success: function (data) {
                if (data != "success") {
                    error = 1;
                    $("#email").removeClass('has-success');
                    $("#email-availability-status").html(data);
                    $("#email").addClass('has-error mar-zero');
                }
                else {
                    error = 0;
                    $("#email").removeClass('has-error mar-zero');
                    $("#email-availability-status").html("");
                    $("#email").addClass('has-success');
                }
                $("#loaderIcon").hide();
            },
            error: function () {
            }
        });
    }
    function checkAvailabilityPhone() {
        $("#loaderIcon").show();
        var getNumber = $('#verify-mobile').intlTelInput("getNumber");
        $('#verify-mobile').val(getNumber);
        jQuery.ajax({
            url: ajaxurl,
            data : {
                action: 'check_availability',
                phone: $('#verify-mobile').val()
            },
            type: "POST",
            success: function (data) {
                if (data != "success") {
                    error = 1;
                    $("#verify-mobile").removeClass('has-success');
                    $("#phone-availability-status").html(data);
                    $("#verify-mobile").addClass('has-error mar-zero');
                }
                else {
                    error = 0;
                    $("#verify-mobile").removeClass('has-error mar-zero');
                    $("#phone-availability-status").html("");
                    $("#verify-mobile").addClass('has-success');
                }
                $("#loaderIcon").hide();
            },
            error: function () {
            }
        });
    }
    function checkAvailabilityPassword() {
        $("#loaderIcon").show();
        jQuery.ajax({
            url: ajaxurl,
            data : {
                action: 'check_availability',
                password: $("#Rpassword").val()
            },
            type: "POST",
            success: function (data) {
                if (data != "success") {
                    error = 1;
                    $("#Rpassword").removeClass('has-success');
                    $("#password-availability-status").html(data);
                    $("#Rpassword").addClass('has-error mar-zero');
                }
                else {
                    error = 0;
                    $("#Rpassword").removeClass('has-error mar-zero');
                    $("#password-availability-status").html("");
                    $("#Rpassword").addClass('has-success');
                }
                $("#loaderIcon").hide();
            },
            error: function () {
            }
        });
    }

</script>

{OVERALL_FOOTER}
