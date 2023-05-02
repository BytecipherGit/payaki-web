{OVERALL_HEADER}
<div id="titlebar" class="margin-bottom-0">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>{LANG_ACCOUNT_SETTING}</h2>
                <!-- Breadcrumbs -->
                <nav id="breadcrumbs">
                    <ul>
                        <li><a href="{LINK_INDEX}">{LANG_HOME}</a></li>
                        <li>{LANG_ACCOUNT_SETTING}</li>
                    </ul>
                </nav>

            </div>
        </div>
    </div>
</div>
<div class="section gray padding-bottom-50">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-12">
                <div class="dashboard-sidebar">
                    <div class="dashboard-sidebar-inner">
                        <div class="dashboard-nav-container">
                            <!-- Responsive Navigation Trigger -->
                            <a href="#" class="dashboard-responsive-nav-trigger">
                                <span class="hamburger hamburger--collapse" >
                                    <span class="hamburger-box">
                                        <span class="hamburger-inner"></span>
                                    </span>
                                </span>
                                <span class="trigger-title">{LANG_DASH_NAVIGATION}</span>
                            </a>

                            <div class="dashboard-nav">
                                <div class="dashboard-nav-inner">
                                    <ul data-submenu-title="{LANG_MY_CLASSIFIED}">
                                        <li><a href="{LINK_DASHBOARD}"><i class="icon-feather-grid"></i> {LANG_DASHBOARD}</a></li>
                                        <li><a href="{LINK_PROFILE}/{USERNAME}"><i class="icon-feather-user"></i> {LANG_PROFILE_PUBLIC}</a></li>
                                        <li><a href="{LINK_MEMBERSHIP}"><i class="icon-feather-gift"></i> {LANG_MEMBERSHIP}</a></li>
                                    </ul>

                                    <ul data-submenu-title="{LANG_MY_ADS}">
                                        <li><a href="{LINK_MYADS}"><i class="icon-feather-briefcase"></i> {LANG_MY_ADS} <span class="nav-tag">{MYADS}</span></a></li>
                                        <li><a href="{LINK_FAVADS}"><i class="icon-feather-heart"></i> {LANG_FAVOURITE_ADS} <span class="nav-tag">{FAVORITEADS}</span></a></li>

                                        <li><a href="{LINK_PENDINGADS}"><i class="icon-feather-clock"></i> {LANG_PENDING_ADS} <span class="nav-tag">{PENDINGADS}</span></a></li>
                                        <li><a href="{LINK_HIDDENADS}"><i class="icon-feather-eye-off"></i> {LANG_HIDDEN_ADS} <span class="nav-tag">{HIDDENADS}</span></a></li>
                                        <li><a href="{LINK_EXPIREADS}"><i class="icon-feather-alert-octagon"></i> {LANG_EXPIRE_ADS} <span class="nav-tag">{EXPIREADS}</span></a></li>
                                        <li><a href="{LINK_RESUBMITADS}"><i class="icon-feather-rotate-cw"></i> {LANG_RESUBMITED_ADS} <span class="nav-tag">{RESUBMITADS}</span></a></li>
                                    </ul>

                                    <ul data-submenu-title="{LANG_MY_ACCOUNT}">
                                        IF('{WCHAT_ON_OFF}'=='on' || '{QUICKCHAT_AJAX_ON_OFF}'=='on' || '{QUICKCHAT_SOCKET_ON_OFF}'=='on'){
                                        <li><a href="{LINK_MESSAGE}"><i class="icon-feather-message-circle"></i> {LANG_MESSAGE}</a></li>
                                        {:IF}
                                        <li><a href="{LINK_TRANSACTION}"><i class="icon-feather-file-text"></i> {LANG_TRANSACTION}</a></li>
                                        <li class="active"><a href="{LINK_ACCOUNT_SETTING}"><i class="icon-feather-settings"></i> {LANG_ACCOUNT_SETTING}</a></li>
                                        <li><a href="{LINK_LOGOUT}"><i class="icon-feather-log-out"></i> {LANG_LOGOUT}</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {AD_INNER_PAGE_SIDEBAR}
            </div>
            <div class="col-lg-9 col-md-12">
                <div class="dashboard-box margin-top-0">
                    <!-- Headline -->
                    <div class="headline">
                        <h3><i class="icon-feather-settings"></i> {LANG_ACCOUNT_SETTING}</h3>
                    </div>
                    <div class="content with-padding">
                        <form method="post" accept-charset="UTF-8">
                            <div class="row">
                                <div class="col-xl-6 col-md-12">
                                    <div class="submit-field">
                                        <h5>{LANG_USERNAME} *</h5>
                                        <div class="input-with-icon-left">
                                            <i class="la la-user"></i>
                                            <input type="text" class="with-border" id="username" name="username" value="{USERNAME}" onBlur="checkAvailabilityUsername()">
                                        </div>
                                        <span id="user-availability-status">IF("{USERNAME_ERROR}"!=""){ {USERNAME_ERROR} {:IF}</span>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-12">
                                    <div class="submit-field">
                                        <h5>{LANG_EMAIL} *</h5>
                                        <div class="input-with-icon-left">
                                            <i class="la la-envelope"></i>
                                            <input type="text" class="with-border" id="email" name="email" value="{EMAIL_FIELD}" onBlur="checkAvailabilityEmail()">
                                        </div>
                                        <span id="email-availability-status">IF("{EMAIL_ERROR}"!=""){ {EMAIL_ERROR} {:IF}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="submit-field">
                                        <h5>{LANG_NEWPASS}</h5>
                                        <input type="password" id="password" name="password" class="with-border" onkeyup="checkAvailabilityPassword()">
                                    </div>
                                </div>

                                <div class="col-xl-6">
                                    <div class="submit-field">
                                        <h5>{LANG_CONPASS}</h5>
                                        <input type="password" id="re_password" name="re_password" class="with-border" onkeyup="checkRePassword()">
                                    </div>
                                </div>
                            </div>
                            <span id="password-availability-status">IF("{PASSWORD_ERROR}"!=""){ {PASSWORD_ERROR} {:IF}</span>
                            <button type="submit" name="submit" class="button ripple-effect">{LANG_SAVE_CHANGES}</button>
                        </form>
                    </div>
                </div>
                <div class="dashboard-box">
                    <div class="headline">
                        <h3><i class="icon-material-outline-description"></i> {LANG_BILLING_DETAILS}</h3>
                    </div>
                    <div class="content">
                        <div class="content with-padding">
                            <div class="notification notice">{LANG_BILLING_DETAILS_NOTES}</div>
                            IF("{BILLING_ERROR}"=="1"){
                            <div class="notification error">{LANG_ALL_FIELDS_REQ}</div>
                            {:IF}
                            <form method="post" accept-charset="UTF-8">
                                <div class="submit-field">
                                    <h5>{LANG_TYPE}</h5>
                                    <select name="billing_details_type" id="billing_details_type"  class="with-border selectpicker" required>
                                        <option value="personal" IF("{BILLING_DETAILS_TYPE}"=="personal"){ selected {:IF}>{LANG_PERSONAL}</option>
                                        <option value="business" IF("{BILLING_DETAILS_TYPE}"=="business"){ selected {:IF}>{LANG_BUSINESS}</option>
                                    </select>
                                </div>
                                <div class="submit-field billing-tax-id">
                                    <h5>IF("{INVOICE_ADMIN_TAX_TYPE}"!=""){ {INVOICE_ADMIN_TAX_TYPE} {ELSE} {LANG_TAX_ID}{:IF}</h5>
                                    <input type="text" id="billing_tax_id" name="billing_tax_id" class="with-border" value="{BILLING_TAX_ID}">
                                </div>
                                <div class="submit-field">
                                    <h5>{LANG_NAME} *</h5>
                                    <input type="text" id="billing_name" name="billing_name" class="with-border" value="{BILLING_NAME}" required>
                                </div>
                                <div class="submit-field">
                                    <h5>{LANG_ADDRESS} *</h5>
                                    <input type="text" id="billing_address" name="billing_address" class="with-border" value="{BILLING_ADDRESS}" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="submit-field">
                                            <h5>{LANG_CITY} *</h5>
                                            <input type="text" id="billing_city" name="billing_city" class="with-border" value="{BILLING_CITY}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="submit-field">
                                            <h5>{LANG_STATE} *</h5>
                                            <input type="text" id="billing_state" name="billing_state" class="with-border" value="{BILLING_STATE}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="submit-field">
                                            <h5>{LANG_ZIPCODE} *</h5>
                                            <input type="text" id="billing_zipcode" name="billing_zipcode" class="with-border" value="{BILLING_ZIPCODE}" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="submit-field">
                                    <h5>{LANG_COUNTRY} *</h5>
                                    <select name="billing_country" id="billing_country" class="with-border selectpicker" data-live-search="true" required>
                                        {LOOP: COUNTRIES}
                                            <option value="{COUNTRIES.code}" {COUNTRIES.selected}>{COUNTRIES.asciiname}</option>
                                        {/LOOP: COUNTRIES}
                                    </select>
                                </div>
                                <button type="submit" name="billing-submit" class="button ripple-effect">{LANG_SAVE_CHANGES}</button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var error = "";
    function checkAvailabilityUsername() {
        jQuery.ajax({
            url: ajaxurl,
            data : {
                action: 'check_availability',
                username: $("#username").val()
            },
            type: "POST",
            success: function (data) {
                if (data != "success") {
                    error = 1;
                    $("#user-availability-status").html(data);
                }
                else {
                    error = 0;
                    $("#user-availability-status").html("");
                }
            },
            error: function () {
            }
        });
    }
    function checkAvailabilityEmail() {
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
                    $("#email-availability-status").html(data);
                }
                else {
                    error = 0;
                    $("#email-availability-status").html("");
                }
                $("#loaderIcon").hide();
            },
            error: function () {
            }
        });
    }
    function checkAvailabilityPassword() {
        var length = $('#password').val().length;
        if (length != 0) {
            var PASSLENG = "{LANG_PASSLENG}";
            if (length < 5 || length > 21) {
                $("#password-availability-status").html("<span class='status-not-available'>" + PASSLENG + "</span>");
            }
            else {
                $("#password-availability-status").html("");
            }
        }

    }

    function checkRePassword(){
        if($('#password').val() != $('#re_password').val()){
            var PASS = "{LANG_PASSNOMATCH}";
            $("#password-availability-status").html("<span class='status-not-available'>" + PASS + "</span>");
        }else{
            $("#password-availability-status").html("");
        }
    }

    jQuery(window).load(function (e) {
        jQuery('#password').val("");
    });
</script>
{OVERALL_FOOTER}
