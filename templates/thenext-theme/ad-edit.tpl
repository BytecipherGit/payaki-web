{OVERALL_HEADER}
<!-- orakuploader -->
<link type="text/css" href="{SITE_URL}plugins/orakuploader/orakuploader.css" rel="stylesheet"/>
<script type="text/javascript" src="{SITE_URL}plugins/orakuploader/jquery.min.js"></script>
<script type="text/javascript" src="{SITE_URL}plugins/orakuploader/jquery-ui.min.js"></script>
<script type="text/javascript" src="{SITE_URL}plugins/orakuploader/orakuploader.js"></script>
IF("{LANGUAGE_DIRECTION}"=="rtl"){
<link type="text/css" href="{SITE_URL}plugins/orakuploader/orakuploader-rtl.css" rel="stylesheet"/>
{:IF}

<!-- orakuploader -->
IF("{POST_WATERMARK}"=="1"){
<script>
    var watermark_image = 'storage/logo/watermark.png';
</script>
{:IF}
IF("{POST_WATERMARK}"=="0"){
<script>
    var watermark_image = '';
</script>
{:IF}
<script>
    var lang_edit_cat = "{LANG_EDIT_CATEGORY}";
    var lang_upload_images = "{LANG_UPLOAD_IMAGES}";
    var siteurl = '{SITE_URL}';
    var template_name = '{TPL_NAME}';
    var max_image_upload = '{MAX_IMAGE_UPLOAD}';

    // Language Var
    var LANG_MAIN_IMAGE = "{LANG_MAIN_IMAGE}";
    var LANG_LOGGED_IN_SUCCESS = "{LANG_LOGGED_IN_SUCCESS}";
    var LANG_ERROR_TRY_AGAIN = "{LANG_ERROR_TRY_AGAIN}";
    var LANG_HIDDEN = "{LANG_HIDDEN}";
    var LANG_ERROR = "{LANG_ERROR}";
    var LANG_CANCEL = "{LANG_CANCEL}";
    var LANG_DELETED = "{LANG_DELETED}";
    var LANG_ARE_YOU_SURE = "{LANG_ARE_YOU_SURE}";
    var LANG_YOU_WANT_DELETE = "{LANG_YOU_WANT_DELETE}";
    var LANG_YES_DELETE = "{LANG_YES_DELETE}";
    var LANG_AD_DELETED = "{LANG_AD_DELETED}";
    var LANG_SHOW = "{LANG_SHOW}";
    var LANG_HIDE = "{LANG_HIDE}";
    var LANG_HIDDEN = "{LANG_HIDDEN}";
    var LANG_ADD_FAV = "{LANG_ADD_FAVOURITE}";
    var LANG_REMOVE_FAV = "{LANG_REMOVE_FAVOURITE}";
    var LANG_SELECT_CITY = "{LANG_SELECT_CITY}";
    $(document).ready(function(){
        // -------------------------------------------------------------
        //  Intialize orakuploader
        // -------------------------------------------------------------
        $('#item_screen').orakuploader({
            site_url :  siteurl,
            orakuploader_path : 'plugins/orakuploader/',
            orakuploader_main_path : 'storage/products',
            orakuploader_thumbnail_path : 'storage/products/thumb',
            orakuploader_add_image : siteurl+'plugins/orakuploader/images/add.svg',
            orakuploader_watermark : watermark_image,
            orakuploader_add_label : lang_upload_images,
            orakuploader_use_main : true,
            orakuploader_use_sortable : true,
            orakuploader_use_dragndrop : true,
            orakuploader_use_rotation: false,
            orakuploader_resize_to : 800,
            orakuploader_thumbnail_size  : 250,
            orakuploader_maximum_uploads : max_image_upload,
            orakuploader_max_exceeded : max_image_upload,
            orakuploader_hide_on_exceed : true,
            orakuploader_attach_images: [{ITEM_SCREENS}],
            orakuploader_main_changed    : function (filename) {
                $("#mainlabel-images").remove();
                $("div").find("[filename='" + filename + "']").append("<div id='mainlabel-images' class='maintext'>Main Image</div>");
            },
            orakuploader_max_exceeded : function() {
                alert("You exceeded the max. limit of "+max_image_upload+" images.");
            },
            orakuploader_picture_deleted : function(filename) {
                    product_id = {ITEM_ID},
                    action = "removeImage",
                    data = { action: action, product_id: product_id, imagename : filename };
                $.post(ajaxurl, data, function(response) {
                    // Remove Ads image from DOM/Server.
                    if(response != 0) {
                        $item.remove();
                        //alertify.success("Deleted! item has been Deleted.");
                    }else{
                        //alertify.error("Problem in deleting, Please try again.");
                    }
                    //jQuery('.confirm').removeClass('bookme-progress');
                    //swal.close();
                });
            }


        });
    });
</script>
<!-- Select Category Modal -->
<div class="zoom-anim-dialog mfp-hide popup-dialog big-dialog" id="categoryModal">
    <div class="popup-tab-content padding-0 tg-thememodal tg-categorymodal">
        <div class="tg-thememodaldialog">
            <div class="tg-thememodalcontent">
                <div class="tg-title">
                    <strong>{LANG_SELECT} {LANG_CATEGORY}</strong>
                </div>
                <div id="tg-dbcategoriesslider" class="tg-dbcategoriesslider tg-categories owl-carousel select-category post-option">
                    {LOOP: CATEGORY}
                        <div class="tg-category {CATEGORY.selected}" data-ajax-catid="{CATEGORY.id}" data-ajax-action="getsubcatbyidList" data-cat-name="{CATEGORY.name}">
                            <div class="tg-categoryholder">
                                <div class="margin-bottom-10">
                                    IF("{CATEGORY.picture}"==""){
                                    <i class="{CATEGORY.icon}"></i>
                                    {:IF}
                                    IF("{CATEGORY.picture}"!=""){
                                    <img src="{CATEGORY.picture}"/>
                                    {:IF}
                                </div>
                                <h3><a href="javascript:void()">{CATEGORY.name}</a></h3>
                            </div>
                        </div>
                    {/LOOP: CATEGORY}

                </div>
                <ul class="tg-subcategories" style="display: none">
                    <li>
                        <div class="tg-title">
                            <strong>{LANG_SELECT_SUBCATEGORY}</strong><div id="sub-category-loader" style="visibility:hidden"></div>
                        </div>
                        <div class=" tg-verticalscrollbar tg-dashboardscrollbar">
                            <ul id="sub_category">

                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- Select Category Modal -->

<div id="titlebar" class="margin-bottom-0">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>{LANG_EDIT_AD}</h2>
                <!-- Breadcrumbs -->
                <nav id="breadcrumbs">
                    <ul>
                        <li><a href="{LINK_INDEX}">{LANG_HOME}</a></li>
                        <li>{LANG_EDIT_AD}</li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>
<div class="section gray">
    <div class="container">
        <div class="row">
            <div class="col-xl-8 col-md-12">
                <div id="post_error"></div>
                <div class="payment-confirmation-page dashboard-box margin-top-0 padding-top-0 margin-bottom-50" style="display: none">
                    <div class="headline">
                        <h3>{LANG_SUCCESS}</h3>
                    </div>
                    <div class="content with-padding padding-bottom-10">
                        <i class="la la-check-circle"></i>
                        <h1 class="margin-top-30 margin-bottom-30">{LANG_SUCCESS}</h1>
                        <p>{LANG_ADSUCCESS}</p>
                    </div>
                </div>
                <form id="post-advertise-form" action="{LINK_EDIT-AD}?action=edit_ad" method="post" enctype="multipart/form-data" accept-charset="UTF-8">

                    <div class="dashboard-box margin-top-0">
                        <!-- Headline -->
                        <div class="headline">
                            <h3><i class="icon-feather-briefcase"></i> {LANG_ADS_DETAILS}</h3>
                        </div>
                        <div class="content with-padding padding-bottom-10">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="form-group text-center">
                                        <a href="#categoryModal" id="choose-category" class="button popup-with-zoom-anim"><i class="icon-feather-check-circle"></i> &nbsp;{LANG_CHOOSE_CATEGORY}</a>
                                    </div>
                                    <div class="form-group selected-product" id="change-category-btn" IF("{SUBCATEGORY}"==""){ style='display: none' {:IF}>
                                    <ul class="select-category list-inline">
                                        <li id="main-category-text">{CATEGORY}</li>
                                        <li id="sub-category-text">{SUBCATEGORY}</li>
                                        <li class="active"><a href="#categoryModal" class="popup-with-zoom-anim"><i class="icon-feather-edit"></i> {LANG_EDIT}</a></li>
                                    </ul>

                                    <input type="hidden" id="input-maincatid" name="catid" value="{CATID}">
                                    <input type="hidden" id="input-subcatid" name="subcatid" value="{SUBCATID}">
                                </div>
                                <div class="submit-field">
                                    <h5>{LANG_TITLE} *</h5>
                                    <input type="text" class="with-border" name="title" value="{TITLE}" placeholder="{LANG_AD_TITLE}">
                                </div>
                                <div class="submit-field">
                                    <h5>{LANG_DESCRIPTION} *</h5>
                                    <textarea cols="30" rows="5" class="with-border" name="content" placeholder="{LANG_AD_DESCRIPTION}">{DESCRIPTION}</textarea>
                                    <!-- <textarea cols="30" rows="5" class="with-border text-editor" name="content" placeholder="{LANG_AD_DESCRIPTION}">{DESCRIPTION}</textarea> -->
                                </div>

                                <div class="submit-field" id="quickad-photo-field">
                                    <div id="item_screen" orakuploader="on"></div>
                                    <input type="hidden" name="deletePrevImg" id="deletePrevImg" value=""/>
                                </div>
                                IF("{CATID}"=="9" || "{CATID}"=="10"){
                                    <div class="single-page-section">
                                        <h3>Promo Video</h3>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="job-property">
                                                    <!-- Use the video element to embed a video -->
                                                    <video width="600px;" height="auto" controls>
                                                        IF("{PROMO_VIDEO}"!=""){
                                                            <source src="{SITE_URL}/storage/training_video/{PROMO_VIDEO}" type="video/mp4">
                                                        {:IF}
                                                    </video>    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="submit-field">
                                        <h5>Update training promo video / image *</h5>
                                        <h6 style="color:#9C5FA3; font-weight: bold;">Video size should not more then (500MB) & allowed extension ("mp4", "avi", "mov", "mkv")</h6>
                                        <div id="input-container">
                                            <div class="file-input-container">
                                                <input type="file" name="trainingPromoVideo" accept="video/*" class="training-file-input">
                                                <input type="hidden" name="max_size" value="500">
                                            </div>
                                        </div>
                                    </div>
                                    {:IF}

                                IF('{CATID}'=="9"){
                                    <div class="submit-field">
                                        <a href="{SITE_URL}training/{ITEM_ID}" class="button ripple-effect big">Click Here To Manage Training Gallery</a>
                                    </div>
                                {:IF}
                                IF('{CATID}'=="10"){
                                    <div class="submit-field">
                                        <a href="{SITE_URL}event/{ITEM_ID}" class="button ripple-effect big">Click Here To Manage Events</a>
                                    </div>
                                {:IF}
                                <div id="ResponseCustomFields">
                                    {LOOP: CUSTOMFIELDS}
                                        IF('{CUSTOMFIELDS.type}'=="text-field"){
                                        <div class="submit-field">
                                            <h5>{CUSTOMFIELDS.title}</h5>
                                            {CUSTOMFIELDS.textbox}
                                        </div>
                                    {:IF}
                                        IF('{CUSTOMFIELDS.type}'=="textarea"){
                                        <div class="submit-field">
                                            <h5>{CUSTOMFIELDS.title}</h5>
                                            {CUSTOMFIELDS.textarea}
                                        </div>
                                    {:IF}
                                        IF('{CUSTOMFIELDS.type}'=="drop-down"){
                                        <div class="submit-field">
                                            <h5>{CUSTOMFIELDS.title}</h5>
                                            <select class="selectpicker with-border quick-select" name="custom[{CUSTOMFIELDS.id}]" data-name="{CUSTOMFIELDS.id}"
                                                    data-req="{CUSTOMFIELDS.required}">
                                                <option value="" selected>{LANG_SELECT} {CUSTOMFIELDS.title}</option>
                                                {CUSTOMFIELDS.selectbox}
                                            </select>
                                            <div class="quick-error">{LANG_FIELD_REQUIRED}</div>
                                        </div>
                                    {:IF}
                                        IF('{CUSTOMFIELDS.type}'=="radio-buttons"){
                                        <div class="submit-field">
                                            <h5>{CUSTOMFIELDS.title}</h5>
                                            {CUSTOMFIELDS.radio}
                                        </div>
                                    {:IF}
                                        IF('{CUSTOMFIELDS.type}'=="checkboxes"){
                                        <div class="submit-field">
                                            <h5>{CUSTOMFIELDS.title}</h5>
                                            {CUSTOMFIELDS.checkbox}
                                        </div>
                                    {:IF}
                                    {/LOOP: CUSTOMFIELDS}
                                </div>
                                IF('{CATID}'!="10"){
                                <div class="submit-field" id="quickad-price-field">
                                    <h5>{LANG_PRICE}</h5>
                                    <div class="row">
                                        <div class="col-xl-6 col-md-12">
                                            <div class="input-with-icon">
                                                <input class="with-border" type="text" placeholder="{LANG_PRICE}" name="price" value="{PRICE}">
                                                <i class="currency">{USER_CURRENCY_SIGN}</i>
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-md-12 margin-top-12">
                                            <div class="checkbox">
                                                <input type="checkbox" id="negotiable" name="negotiable" value="1" IF("{NEGOTIABLE}"=="1"){ checked {:IF}>
                                                <label for="negotiable"><span class="checkbox-icon"></span> {LANG_NEGOTIATE}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {:IF}
                                <div class="submit-field">
                                    <h5>{LANG_PHONE_NO}</h5>
                                    <div class="row">
                                        <div class="col-xl-6 col-md-12">
                                            <div class="input-with-icon-left">
                                                <i class="flag-img"><img src="{SITE_URL}includes/assets/plugins/flags/images/{USER_COUNTRY}.png"></i>
                                                <input type="text" class="with-border" name="phone" value="{PHONE}">
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-md-12">
                                            <div class="checkbox margin-top-12">
                                                <input type="checkbox" name="hide_phone" id="phone" value="1" IF("{HIDEPHONE}"=="1"){ checked {:IF}>
                                                <label for="phone"><span class="checkbox-icon"></span> {LANG_HIDE}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="submit-field">
                                    <h5>{LANG_CITY} *</h5>
                                    <select id="jobcity" class="with-border" name="city" data-size="7" title="{LANG_SELECT} {LANG_CITY}">
                                        <option value="0" selected="selected">{LANG_SELECT} {LANG_CITY}</option>
                                        IF("{CITY}"!=""){ <option value="{CITY}" selected="selected">{CITYNAME}</option> {:IF}
                                    </select>
                                </div>
                                IF({POST_ADDRESS_MODE}){
                                <div class="submit-field">
                                    <h5>{LANG_ADDRESS}</h5>
                                    <div class="input-with-icon">
                                        <div id="autocomplete-container" data-autocomplete-tip="{LANG_TYPE_ENTER}">
                                            <input class="with-border" type="text" placeholder="{LANG_ADDRESS}" name="location" id="address-autocomplete">
                                        </div>
                                        <div class="geo-location"><i class="fa fa-crosshairs"></i></div>
                                    </div>
                                    <div class="map shadow" id="singleListingMap" data-latitude="{LATITUDE}" data-longitude="{LONGITUDE}"  style="height: 200px"></div>
                                    <small>{LANG_DRAG_MAP_MARKER}</small>
                                </div>
                                {:IF}

                                <input type="hidden" id="latitude" name="latitude"  value="{LATITUDE}"/>
                                <input type="hidden" id="longitude" name="longitude" value="{LONGITUDE}"/>

                                IF("{POST_TAGS_MODE}"=="1"){
                                <div class="submit-field form-group">
                                    <h5>{LANG_TAGS}</h5>
                                    <input class="with-border" type="text" name="tags" value="{TAGS}">
                                    <small>{LANG_TAGS_DETAIL}</small>
                                </div>
                                {:IF}

                                <div class="submit-field form-group">
                                        <h5>Available Days</h5>
                                        <select class="with-border" style="padding: 10px 18px !important;" type="text" name="available_days">
                                        <option value="1" IF("{EXPIRE_DAYS}"=="1"){ selected {:IF}>1 Day</option>
                                        <option value="2" IF("{EXPIRE_DAYS}"=="2"){ selected {:IF}>2 Days</option>
                                        <option value="5" IF("{EXPIRE_DAYS}"=="5"){ selected {:IF}>5 Days</option>
                                        <option value="10" IF("{EXPIRE_DAYS}"=="10"){ selected {:IF}>10 Days</option>
                                        <option value="20" IF("{EXPIRE_DAYS}"=="20"){ selected {:IF}>20 Days</option>
                                        <option value="30" IF("{EXPIRE_DAYS}"=="30"){ selected {:IF}>30 Days</option>
                                        <option value="60" IF("{EXPIRE_DAYS}"=="60"){ selected {:IF}>60 Days</option>
                                        </select>
                                    </div>
                            </div>
                        </div>
                    </div>
            </div>
            IF(!{LOGGED_IN}){
            <div class="dashboard-box">
                <!-- Headline -->
                <div class="headline">
                    <h3><i class="icon-feather-user"></i> {LANG_USER_DETAILS}</h3>
                </div>
                <div class="content with-padding padding-bottom-10">
                    <div class="row">
                        <div class="col-xl-6 col-md-12">
                            <div class="submit-field">
                                <h5>{LANG_FULL_NAME} *</h5>
                                <div class="input-with-icon-left">
                                    <i class="la la-user"></i>
                                    <input type="text" class="with-border" name="user_name" value="{SELLER_NAME}">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-md-12">
                            <div class="submit-field">
                                <h5>{LANG_EMAIL} *</h5>
                                <div class="input-with-icon-left">
                                    <i class="la la-envelope"></i>
                                    <input type="email" class="with-border" name="user_email" value="{SELLER_EMAIL}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {:IF}

            IF({POST_PREMIUM_LISTING}){
            <div class="dashboard-box">
                <div class="headline">
                    <h3><i class="icon-feather-zap"></i> {LANG_MAKE_PREMIUM} <small>({LANG_OPTIONAL})</small></h3>
                </div>
                <div class="content with-padding">
                    <div class="payment">

                        <div class="payment-tab payment-tab-active">
                            <div class="payment-tab-trigger">
                                <input checked id="free" name="make_premium" type="radio" value="0">
                                <label for="free">{LANG_FREE_AD}</label>
                            </div>
                            <div class="payment-tab-content">
                                <p>{LANG_CHECK_BY_TEAM}</p>
                            </div>
                        </div>

                        <div class="payment-tab">
                            <div class="payment-tab-trigger">
                                <input type="radio" name="make_premium" id="make_premium" value="1">
                                <label for="make_premium">{LANG_PREMIUM} <span class="badge green pull-right">{LANG_RECOMMENDED}</span></label>
                            </div>

                            <div class="payment-tab-content">
                                <p>{LANG_UPGRADE_TEXT_INFO}</p>
                                IF("{FEATURED}"!="1"){
                                <div class="row premium-plans">
                                    <div class="col-lg-3">
                                        <div class="checkbox">
                                            <input type="checkbox" id="featured" name="featured" value="1" onchange="fillPrice(this,{FEATURED_PRICE});">
                                            <label for="featured"><span class="checkbox-icon"></span> <span class="badge blue">{LANG_FEATURED}</span></label>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 premium-plans-text">
                                        {LANG_FEATURED_AD_TEXT}
                                    </div>
                                    <div class="col-lg-3 premium-plans-price">
                                        {FEATURED_FEE} {LANG_FOR} {FEATURED_DURATION} {LANG_DAYS}
                                    </div>
                                </div>
                                {:IF}
                                IF("{URGENT}"!="1"){
                                <div class="row premium-plans">
                                    <div class="col-lg-3">
                                        <div class="checkbox">
                                            <input type="checkbox" id="urgent" name="urgent" value="1" onchange="fillPrice(this,{URGENT_PRICE});">
                                            <label for="urgent"><span class="checkbox-icon"></span> <span class="badge yellow">{LANG_URGENT}</span></label>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 premium-plans-text">
                                        {LANG_URGENT_AD_TEXT}
                                    </div>
                                    <div class="col-lg-3 premium-plans-price">
                                        {URGENT_FEE} {LANG_FOR} {URGENT_DURATION} {LANG_DAYS}
                                    </div>
                                </div>
                                {:IF}
                                IF("{HIGHLIGHT}"!="1"){
                                <div class="row premium-plans">
                                    <div class="col-lg-3">
                                        <div class="checkbox">
                                            <input type="checkbox" id="highlight" name="highlight" value="1" onchange="fillPrice(this,{HIGHLIGHT_PRICE});">
                                            <label for="highlight"><span class="checkbox-icon"></span> <span class="badge red">{LANG_HIGHLIGHT}</span></label>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 premium-plans-text">
                                        {LANG_HIGHLIGHT_AD_TEXT}
                                    </div>
                                    <div class="col-lg-3 premium-plans-price">
                                        {HIGHLIGHT_FEE} {LANG_FOR} {HIGHLIGHT_DURATION} {LANG_DAYS}
                                    </div>
                                </div>
                                {:IF}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            {:IF}
            IF("{RESUBMIT}"=="1"){
            <div class="dashboard-box">
                <!-- Headline -->
                <div class="headline">
                    <h3><i class="icon-feather-user"></i> {LANG_MSG_REVIEWER}</h3>
                </div>
                <div class="content with-padding padding-bottom-10">
                    <div class="submit-field">
                        <h5>{LANG_COMMENT} *</h5>
                        <textarea class="with-border" name="comments"></textarea>
                        <small>{LANG_COMMENT_PLACEHOLDER}</small>
                    </div>
                </div>
            </div>
            {:IF}
            <input type="hidden" name="product_id" value="{ITEM_ID}">
            <input type="hidden" name="submit">
            <div class="row margin-top-30 margin-bottom-80" style="align-items: center;">
                <div class="col-6">
                    <button type="submit" id="submit_job_button" name="Submit" class="button ripple-effect big"><i class="icon-feather-plus"></i> {LANG_POST_AD}</button>
                </div>
                <div class="col-6">
                    <div id="ad_total_cost_container" class="text-right" style="display: none">
                        <strong>
                            {LANG_TOTAL}:
                            <span class="currency-sign">{CURRENCY_SIGN}</span>
                            <span id="totalPrice">0</span>
                            <span class="currency-code">{CURRENCY_CODE}</span>
                        </strong>
                    </div>
                </div>
            </div>
            </form>
        </div>
        <div class="col-xl-4 hide-under-992px">
            <div class="dashboard-box margin-top-0">
                <!-- Headline -->
                <div class="headline">
                    <h3><i class="icon-feather-alert-circle"></i> {LANG_TIPS}</h3>
                </div>
                <div class="content with-padding padding-bottom-10">
                    <ul class="list-2">
                        <li>{LANG_POST_JOB_TIPS1}</li>
                        <li>{LANG_POST_JOB_TIPS2}</li>
                        <li>{LANG_POST_JOB_TIPS3}</li>
                        <li>{LANG_POST_JOB_TIPS4}</li>
                    </ul>
                </div>
            </div>

            {AD_POST_PAGE_SIDEBAR}
        </div>
    </div>
</div>
</div>
<script>
    var lang_edit_cat = "<i class='icon-feather-check-circle'></i> &nbsp;{LANG_EDIT_CATEGORY}";
</script>
<link href="{SITE_URL}templates/{TPL_NAME}/css/category-modal.css" type="text/css" rel="stylesheet">
<link href="{SITE_URL}templates/{TPL_NAME}/css/owl.post.carousel.css" type="text/css" rel="stylesheet">
<link href="{SITE_URL}templates/{TPL_NAME}/css/select2.min.css" rel="stylesheet" />
<script src="{SITE_URL}templates/{TPL_NAME}/js/select2.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/i18n/{LANG_CODE}.js"></script>
<script src="{SITE_URL}templates/{TPL_NAME}/js/owl.carousel-category.min.js"></script>
<script src="{SITE_URL}templates/{TPL_NAME}/js/jquery.form.js"></script>
<script src="{SITE_URL}templates/{TPL_NAME}/js/ad_post.js"></script>

IF("{POST_DESC_EDITOR}"=="1"){
    <!-- CRUD FORM CONTENT - crud_fields_scripts stack -->
    <link media="all" rel="stylesheet" type="text/css" href="{SITE_URL}includes/assets/plugins/simditor/styles/simditor.css" />
    <script src="{SITE_URL}includes/assets/plugins/simditor/scripts/mobilecheck.js"></script>
    <script src="{SITE_URL}includes/assets/plugins/simditor/scripts/module.js"></script>
    <script src="{SITE_URL}includes/assets/plugins/simditor/scripts/uploader.js"></script>
    <script src="{SITE_URL}includes/assets/plugins/simditor/scripts/hotkeys.js"></script>
    <script src="{SITE_URL}includes/assets/plugins/simditor/scripts/simditor.js"></script>
    <script>
        /*(function() {
            $(function() {
                var $preview, editor, mobileToolbar, toolbar, allowedTags;
                Simditor.locale = 'en-US';
                toolbar = ['bold','italic','underline','|','ol','ul','blockquote','table','link'];
                mobileToolbar = ["bold", "italic", "underline", "ul", "ol"];
                if (mobilecheck()) {
                    toolbar = mobileToolbar;
                }
                allowedTags = ['br','span','a','img','b','strong','i','strike','u','font','p','ul','ol','li','blockquote','pre','h1','h2','h3','h4','hr','table'];
                editor = new Simditor({
                    textarea: $('.text-editor'),
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
        }).call(this);*/
    </script>
{:IF}

IF({POST_ADDRESS_MODE}){
    IF("{MAP_TYPE}"=="google"){
    <link href="{SITE_URL}includes/assets/plugins/map/google/map-marker.css" type="text/css" rel="stylesheet">
    <script type='text/javascript' src='{SITE_URL}includes/assets/plugins/map/google/jquery-migrate-1.2.1.min.js'></script>
    <script type='text/javascript' src='//maps.google.com/maps/api/js?key={GMAP_API_KEY}&#038;libraries=places%2Cgeometry&#038;ver=2.2.1'></script>
    <script type='text/javascript' src='{SITE_URL}includes/assets/plugins/map/google/richmarker-compiled.js'></script>
    <script type='text/javascript' src='{SITE_URL}includes/assets/plugins/map/google/markerclusterer_packed.js'></script>
    <script type='text/javascript' src='{SITE_URL}includes/assets/plugins/map/google/gmapAdBox.js'></script>
    <script type='text/javascript' src='{SITE_URL}includes/assets/plugins/map/google/maps.js'></script>
    <script>
        var _latitude = '{LATITUDE}';
        var _longitude = '{LONGITUDE}';
        var element = "singleListingMap";
        var path = '{SITE_URL}';
        var getCity = false;
        var getCountry = 'all';
        var color = '{MAP_COLOR}';
        var site_url = '{SITE_URL}';
        simpleMap(_latitude, _longitude, element);
    </script>
    {ELSE}
    <script>
        var openstreet_access_token = '{OPENSTREET_ACCESS_TOKEN}';
    </script>
    <link rel="stylesheet" href="{SITE_URL}includes/assets/plugins/map/openstreet/css/style.css">
    <!-- Leaflet // Docs: https://leafletjs.com/ -->
    <script src="{SITE_URL}includes/assets/plugins/map/openstreet/leaflet.min.js"></script>

    <!-- Leaflet Maps Scripts (locations are stored in leaflet-quick.js) -->
    <script src="{SITE_URL}includes/assets/plugins/map/openstreet/leaflet-markercluster.min.js"></script>
    <script src="{SITE_URL}includes/assets/plugins/map/openstreet/leaflet-gesture-handling.min.js"></script>
    <script src="{SITE_URL}includes/assets/plugins/map/openstreet/leaflet-quick.js"></script>

    <!-- Leaflet Geocoder + Search Autocomplete // Docs: https://github.com/perliedman/leaflet-control-geocoder -->
    <script src="{SITE_URL}includes/assets/plugins/map/openstreet/leaflet-autocomplete.js"></script>
    <script src="{SITE_URL}includes/assets/plugins/map/openstreet/leaflet-control-geocoder.js"></script>
    <script>
        $('#jobcity').on('change', function() {
            var data = $("#jobcity option:selected").val();
            var custom_data= $("#jobcity").select2('data')[0];
            var latitude = custom_data.latitude;
            var longitude = custom_data.longitude;
            var address = custom_data.text;
            $('#latitude').val(latitude);
            $('#longitude').val(longitude);
            if (document.getElementById("singleListingMap") !== null && singleListingMap) {
                $("#address-autocomplete").val(address);
                var newLatLng = new L.LatLng(latitude, longitude);
                singleListingMapMarker.setLatLng(newLatLng);
                singleListingMap.flyTo(newLatLng, 10);
            }
        });
    </script>
    {:IF}
{:IF}
{OVERALL_FOOTER}
