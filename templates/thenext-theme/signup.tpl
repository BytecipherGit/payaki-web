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
                    <!--<div class="form-group">
                        <div>
                            <input type="phone" class="input-text with-border" placeholder="{LANG_PHONE_NO}" value="{PHONE_FIELD}" id="verify-mobile" name="phone" onBlur="checkAvailabilityPhone()" required/>
                            <input type="hidden" id="country_code" name="country_code"/>
                        </div>
                        <span id="phone-availability-status">IF("{PHONE_ERROR}"!=""){ {PHONE_ERROR} {:IF}</span>
                    </div>-->
                    <div class="form-group">
                        <div>
                            <input type="phone" class="input-text with-border" placeholder="{LANG_PHONE_NO}" value="{PHONE_FIELD}" id="verify-mobile" name="phone" onBlur="checkAvailabilityPhone()" required/>
                            <input type="hidden" id="country_code" name="country_code"/>
                        </div>
                        <span id="phone-availability-status">IF("{PHONE_ERROR}"!=""){ {PHONE_ERROR} {:IF}</span>
                    </div>
                    IF("{SMS_VERIFY_MODE}"!="1"){
                    <!--<div class="form-group">
                        <div>
                            <input type="phone" class="input-text with-border" placeholder="{LANG_PHONE_NO}" value="{PHONE_FIELD}" id="verify-mobile" name="phone" onBlur="checkAvailabilityPhone()" required/>
                        </div>
                        <span id="phone-availability-status">IF("{PHONE_ERROR}"!=""){ {PHONE_ERROR} {:IF}</span>
                    </div>-->
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
                            <i class="la la-address-book"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-book" viewBox="0 0 16 16">
  <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.811V2.828zm7.5-.141c.654-.689 1.782-.886 3.112-.752 1.234.124 2.503.523 3.388.893v9.923c-.918-.35-2.107-.692-3.287-.81-1.094-.111-2.278-.039-3.213.492V2.687zM8 1.783C7.015.936 5.587.81 4.287.94c-1.514.153-3.042.672-3.994 1.105A.5.5 0 0 0 0 2.5v11a.5.5 0 0 0 .707.455c.882-.4 2.303-.881 3.68-1.02 1.409-.142 2.59.087 3.223.877a.5.5 0 0 0 .78 0c.633-.79 1.814-1.019 3.222-.877 1.378.139 2.8.62 3.681 1.02A.5.5 0 0 0 16 13.5v-11a.5.5 0 0 0-.293-.455c-.952-.433-2.48-.952-3.994-1.105C10.413.809 8.985.936 8 1.783z"/>
</svg></i>
                            <select name="id_proof_type" id="idprooftype" class="form-control with-border select-register">
                            <option value="">Select Identification Type</option>
                            <option value="Bilhete de Identidade (BI)" IF("{IDPROOFTYPE_FIELD}"=="Bilhete de Identidade (BI)"){ "selected" {:IF}>Bilhete de Identidade (BI)</option>
                            <option value="Passaporte" IF("{IDPROOFTYPE_FIELD}"=="Passaporte"){ "selected" {:IF}>Passaporte</option>
                            </select>
                        </div>
                        <span id="idprooftype-availability-status">IF("{IDPROOFTYPE_ERROR}"!=""){ {IDPROOFTYPE_ERROR} {:IF}</span>
                    </div>
                    <div class="form-group">
                        <div class="input-with-icon-left">
                        <i class="la la-address-book"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cassette" viewBox="0 0 16 16">
  <path d="M4 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm9-1a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM7 6a1 1 0 0 0 0 2h2a1 1 0 1 0 0-2H7Z"/>
  <path d="M1.5 2A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13ZM1 3.5a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-.691l-1.362-2.724A.5.5 0 0 0 12 10H4a.5.5 0 0 0-.447.276L2.19 13H1.5a.5.5 0 0 1-.5-.5v-9ZM11.691 11l1 2H3.309l1-2h7.382Z"/>
</svg></i>
                            <input type="text" class="input-text with-border" placeholder="ID Proof Number" id="idproofnumber" value="{IDPROOFNUMBER_FIELD}" name="id_proof_number" required/>
                        </div>
                        <span id="idproofnumber-availability-status">IF("{IDPROOFNUMBER_ERROR}"!=""){ {IDPROOFNUMBER_ERROR} {:IF}</span>
                    </div>
                    <div class="form-group">
                    <label>Upload Identification Document</label>
                        <input type="file" class="form-control" id="idproof" name="id_proof" style="padding: 0px 0px !important; height: auto !important;" required/>
                        <span id="idproof-availability-status">IF("{IDPROOF}"!=""){ {IDPROOF} {:IF}</span>
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
