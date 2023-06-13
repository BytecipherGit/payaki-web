{OVERALL_HEADER}
<div id="titlebar" class="margin-bottom-0">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>{LANG_DASHBOARD}</h2>
                <!-- Breadcrumbs -->
                <nav id="breadcrumbs">
                    <ul>
                        <li><a href="{LINK_INDEX}">{LANG_HOME}</a></li>
                        <li>{LANG_DASHBOARD}</li>
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
                            <a href="#" class="dashboard-responsive-nav-trigger">
                                <span class="hamburger hamburger--collapse" >
                                    <span class="hamburger-box"><span class="hamburger-inner"></span></span>
                                </span>
                                <span class="trigger-title">{LANG_DASH_NAVIGATION}</span>
                            </a>
                            <div class="dashboard-nav">
                                <div class="dashboard-nav-inner">
                                    <ul data-submenu-title="{LANG_MY_CLASSIFIED}">
                                        <li class="active"><a href="{LINK_DASHBOARD}"><i class="icon-feather-grid"></i> {LANG_DASHBOARD}</a></li>
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
                                        <li><a href="{LINK_ACCOUNT_SETTING}"><i class="icon-feather-settings"></i> {LANG_ACCOUNT_SETTING}</a></li>
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
                    <div class="content with-padding">
                        <div class="row dashboard-profile">
                            <div class="col-xl-6 col-md-6 col-sm-12">
                                <div class="user-img"><img src="{SITE_URL}storage/profile/{AUTHORIMG}" alt="{AUTHORNAME}" class="img-responsive"></div>
                                <div>
                                    <h2>{AUTHORNAME}
                                        IF("{SUB_IMAGE}"!=""){
                                        <img src="{SUB_IMAGE}" alt="{SUB_TITLE}" title="{SUB_TITLE}" width="28px"/>
                                        {:IF}
                                    </h2>
                                    <span><i class="icon-feather-gift"></i> {LANG_MEMBERSHIP}  :
                                        IF("{SUB_TITLE}"!=""){
                                            {SUB_TITLE}
                                        {ELSE}
                                            {LANG_FREE}
                                        {:IF}
                                    </span><br>
                                    <small>{LANG_USERNAME}: {USERNAME}</small>
                                    <style>
                                        .text-success{ color:#28a745 !important}
                                        .text-danger{ color:#dc3545 !important}
                                    </style>
                                    IF("{SMS_VERIFY_MODE}"=="1"){
                                    <div class="agent-page">
                                        <ul class="agent-contact-details">
                                            IF("{PHONE_VERIFY}"=="1"){
                                            <li><i class="icon-feather-phone-call"></i><a href="tel:{PHONE}">{PHONE}</a><img src="{SITE_URL}templates/{TPL_NAME}/images/verified-badge.png" data-tippy-placement="top" title="{LANG_VERIFIED}" width="16px"/>
                                                <a href="#verify-mobile-dialog" class="popup-with-zoom-anim" data-tippy-placement="top" title="{LANG_CHANGE_PNO}"><i class="icon-feather-edit" style="position: relative;left: 6px;top: 3px;"></i></a></li>
                                            {ELSE}
                                            <li class="text-success"><i class="icon-feather-phone-call"></i><a href="#verify-mobile-dialog" class="text-success popup-with-zoom-anim">{LANG_VERIFY_MOBILE_NO}</a></li>
                                            {:IF}
                                        </ul>
                                    </div>
                                    {:IF}
                                </div>
                            </div>
                            <div class="col-xl-6 col-md-6 col-sm-12 text-right">
                                <span class="dashboard-badge"><strong>{FAVORITEADS}</strong><i class="icon-feather-heart"></i> {LANG_FAVOURITES}</span>
                                <span class="dashboard-badge"><strong>{MYADS}</strong><i class="icon-feather-briefcase"></i> {LANG_MY_ADS}</span>
                            </div>
                        </div>
                    </div>
                </div>
                {AD_INNER_PAGE_HORIZONTAL_BANNER}
                <form method="post" accept-charset="UTF-8" enctype="multipart/form-data">
                    <div class="dashboard-box">
                        <!-- Headline -->
                        <div class="headline">
                            <h3><i class="icon-feather-user"></i> {LANG_MY_DETAILS}</h3>
                        </div>
                        <div class="content with-padding">
                            <div class="row">
                                <div class="col-xl-6 col-md-12">
                                    <div class="submit-field">
                                        <h5>{LANG_NAME} *</h5>
                                        <div class="input-with-icon-left">
                                            <i class="la la-user"></i>
                                            <input type="text" class="with-border" name="name" value="{AUTHORNAME}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-12">
                                    <div class="submit-field">
                                        <h5>{LANG_ADDRESS}</h5>
                                        <div class="input-with-icon-left">
                                            <i class="la la-location-arrow"></i>
                                            <input type="text" class="with-border" name="address" value="{ADDRESS}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-12">
                                    <div class="submit-field">
                                        <h5>{LANG_COUNTRY}</h5>
                                        <select name="country" class="multiselect2">
                                            {LOOP: COUNTRY}
                                                <option value="{COUNTRY.asciiname}" IF("{COUNTRY}"=="{COUNTRY.asciiname}"){ selected {:IF}>{COUNTRY.asciiname}</option>
                                            {/LOOP: COUNTRY}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-md-12">
                                    <div class="submit-field">
                                        <h5>{LANG_WEBSITE}</h5>
                                        <div class="input-with-icon-left">
                                            <i class="la la-globe"></i>
                                            <input type="text" class="with-border" name="website" value="{WEBSITE}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="submit-field">
                                        <h5>{LANG_ABOUT_ME}</h5>
                                        <textarea class="with-border" rows="3" name="content">{AUTHORABOUT}</textarea>
                                        <!-- <textarea class="with-border" id="pageContent" rows="2" name="content">{AUTHORABOUT}</textarea> -->
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="submit-field">
                                        <h5>{LANG_AVATAR}</h5>
                                        <div class="BrowseButton">
                                            <input class="BrowseButton-input" type="file" accept="image/*" id="upload" name="avatar"/>
                                            <label class="BrowseButton-button ripple-effect" for="upload">{LANG_CHOOSE_FILE}</label>
                                            <span class="BrowseButton-file-name">{LANG_LOGO_HINT}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="dashboard-box">
                        <!-- Headline -->
                        <div class="headline">
                            <h3><i class="icon-feather-lock"></i> {LANG_SOCIAL_NETWORKS}</h3>
                        </div>

                        <div class="content with-padding">
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="submit-field">
                                        <h5>Facebook</h5>
                                        <input type="text" name="facebook" class="with-border" value="{FACEBOOK}">
                                    </div>
                                </div>

                                <div class="col-xl-6">
                                    <div class="submit-field">
                                        <h5>Twitter</h5>
                                        <input type="text" name="twitter" class="with-border" value="{TWITTER}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="submit-field">
                                        <h5>Pinterest</h5>
                                        <input type="text" name="googleplus" class="with-border" value="{GOOGLEPLUS}">
                                    </div>
                                </div>

                                <div class="col-xl-6">
                                    <div class="submit-field">
                                        <h5>Instagram</h5>
                                        <input type="text" name="instagram" class="with-border" value="{INSTAGRAM}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xl-6">
                                    <div class="submit-field">
                                        <h5>Linked In</h5>
                                        <input type="text" name="linkedin" class="with-border" value="{LINKEDIN}">
                                    </div>
                                </div>

                                <div class="col-xl-6">
                                    <div class="submit-field">
                                        <h5>Youtube</h5>
                                        <input type="text" name="youtube" class="with-border" value="{YOUTUBE}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-box">
                        <!-- Headline -->
                        <div class="headline">
                            <h3><i class="icon-feather-lock"></i> {LANG_NEWSLETTER}</h3>
                        </div>

                        <div class="content with-padding">
                            <div class="row">
                                <div class="col-xl-6 subscribe-category">
                                    <div class="checkbox">
                                        <input type="checkbox" name="notify" id="notify" value="1" onchange="NotifyValueChanged()" IF("{NOTIFY}"=="1"){ checked {:IF}>
                                        <label for="notify"><span class="checkbox-icon"></span> {LANG_NOTIFYEMAIL}</label>
                                    </div>
                                    <div class="skills" style="margin: 0 25px">
                                        {LOOP: CATEGORY}
                                            <div class="checkbox d-block">
                                                <input type="checkbox" name="choice[{CATEGORY.id}]" id="{CATEGORY.id}" value="{CATEGORY.id}" {CATEGORY.selected}>
                                                <label for="{CATEGORY.id}"><span class="checkbox-icon"></span> {CATEGORY.name}</label>
                                            </div>
                                        {/LOOP: CATEGORY}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="padding-30">
                        <button type="submit" name="submit" class="button ripple-effect">{LANG_UPDATE}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Verify Mobile Number popup /
================================================== -->
<div id="verify-mobile-dialog" class="zoom-anim-dialog mfp-hide dialog-with-tabs popup-dialog">
    <ul class="popup-tabs-nav">
        <li><a href="#login">{LANG_VERIFY_MOBILE_NO}</a></li>
    </ul>
    <div class="popup-tabs-container">
        <div class="popup-tab-content" id="mobile-form">
            <div class="reset-form d-block">
                <div class="welcome-text">
                    <h3>{LANG_VERIFY_MOBILE_NO}</h3>
                    <span id="mobile-status">{LANG_OTP_NOTIFY_1}</span>
                </div>
                <form action="dashboard_mobile_verify" name="pv-form" id="pv-form" method="post">
                    <input required class="input-text with-border" id="verify-mobile" name="mobile_no" type="phone" placeholder="{LANG_PHONE_NO}" value="{PHONE}">
                    <input type="hidden" value="1" name="dashboard_verify"/>
                    <input type="hidden" value="1" name="submit_mobile"/>
                    <button class="button full-width button-sliding-icon ripple-effect" id="mobile-submit" type="submit" name="submit">{LANG_SEND_OTP} <i class="icon-feather-arrow-right"></i></button>
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
                        <button class="button full-width button-sliding-icon ripple-effect" id="otp-submit" type="submit" name="submit">{LANG_VERIFY_OTP} <i class="icon-feather-arrow-right"></i></button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
<!-- Verify Mobile Number popup / End -->
<link href="{SITE_URL}includes/assets/plugins/intlTelInput/css/intlTelInput.css" media="all" rel="stylesheet" type="text/css"/>
<script src="{SITE_URL}includes/assets/plugins/intlTelInput/js/intlTelInput.min.js"></script>
<script src="{SITE_URL}includes/assets/plugins/intlTelInput/js/intlTelInput.utils.js"></script>
<script src="{SITE_URL}includes/assets/plugins/intlTelInput/js/custom.js"></script>

<!-- CRUD FORM CONTENT - crud_fields_scripts stack -->
<link media="all" rel="stylesheet" type="text/css" href="{SITE_URL}includes/assets/plugins/simditor/styles/simditor.css" />
<script src="{SITE_URL}includes/assets/plugins/simditor/scripts/mobilecheck.js"></script>
<script src="{SITE_URL}includes/assets/plugins/simditor/scripts/module.js"></script>
<script src="{SITE_URL}includes/assets/plugins/simditor/scripts/uploader.js"></script>
<script src="{SITE_URL}includes/assets/plugins/simditor/scripts/hotkeys.js"></script>
<script src="{SITE_URL}includes/assets/plugins/simditor/scripts/simditor.js"></script>
<script>
    (function() {
        $(function() {
            var $preview, editor, mobileToolbar, toolbar, allowedTags;
            Simditor.locale = 'en-US';
            toolbar = ['bold','italic','underline','fontScale','color','|','ol','ul','blockquote','table','link'];
            mobileToolbar = ["bold", "italic", "underline", "ul", "ol"];
            if (mobilecheck()) {
                toolbar = mobileToolbar;
            }
            allowedTags = ['br','span','a','img','b','strong','i','strike','u','p','ul','ol','li','blockquote','pre','h1','h2','h3','h4','hr','table'];
            editor = new Simditor({
                textarea: $('#pageContent'),
                placeholder: '',
                toolbar: toolbar,
                pasteImage: false,
                defaultImage: '{SITE_URL}includes/assets/plugins/simditor/images/image.png',
                upload: false,
                allowedTags: allowedTags
            });
            $preview = $('#preview');
            if ($preview.length > 0) {
                return editor.on('valuechanged', function(e) {
                    return $preview.html(editor.getValue());
                });
            }
        });
    }).call(this);
</script>

<script type="text/javascript">
    function NotifyValueChanged()
    {
        if($('#notify').is(":checked"))
            $(".skills").show();
        else
            $(".skills").hide();
    }
    NotifyValueChanged();
</script>
{OVERALL_FOOTER}
