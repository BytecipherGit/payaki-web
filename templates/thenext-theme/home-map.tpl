{OVERALL_HEADER}

<div id="road_map" class="map"></div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <!-- banner-form -->
            <div class="banner-form banner-form-full">
                <form action="{LINK_LISTING}" method="get" id="hero-home-map">
                    <!-- category-change -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="dropdown category-dropdown"><a data-toggle="dropdown" href="#"><span class="change-text">{LANG_SELECT_CATEGORY}</span><i class="fa fa-navicon"></i></a>{CAT_DROPDOWN}</div>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="serachStr" placeholder="{LANG_WHAT} ?" style="padding: 0px;">
                        </div>
                        <div class="col-md-3 banner-icon geo-location"><i class="fa fa-map-marker"></i>
                            <input type="text" class="form-control" id="address-autocomplete" name="location" placeholder="{LANG_WHERE} ?" style=" border-left: 1px solid #e0e0e0;">
                            <input type="hidden" id="latitude" name="latitude"/>
                            <input type="hidden" id="longitude" name="longitude"/>
                            <input type="hidden" id="locality" name="locality"/>
                            <input type="hidden" id="administrative_area_level_2" name="city"/>
                            <input type="hidden" id="administrative_area_level_1" name="state"/>
                            <input type="hidden" id="country" name="country"/>
                        </div>
                        <div class="col-md-2">
                            <input type="hidden" id="input-maincat" name="searchBox" value=""/>
                            <input type="hidden" id="input-subcat" name="subcat" value=""/>
                            <button data-ajax-response='map' data-ajax-auto-zoom="1" type="submit" name="searchform"
                                    class="form-control"><i class="fa fa-search"></i> {LANG_SEARCH}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <!-- banner-form -->
        </div>
    </div>
</div>

<!-- Category Boxes -->
<div class="section margin-top-65">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="section-headline centered margin-bottom-15">
                    <h3>{LANG_ALL_CATEGORIES}</h3>
                </div>
                <div class="categories-container">
                    {LOOP: CAT}
                        <a href="{CAT.catlink}" class="category-box">
                            <div class="category-box-icon margin-bottom-10">
                                IF("{CAT.picture}"==""){
                                <div class="category-icon"><i class="{CAT.icon}"></i></div>
                                {ELSE}
                                <div class="category-icon">
                                    <img class="lazy-load" src="{CAT.picture}" alt="{CAT.main_title}">
                                </div>
                                {:IF}
                            </div>
                            <div class="category-box-counter">{CAT.main_ads_count}</div>
                            <div class="category-box-content">
                                <h3>{CAT.main_title} <small>({CAT.main_ads_count})</small></h3>
                            </div>
                            <div class="category-box-arrow">
                                <i class="fa fa-chevron-right"></i>
                            </div>
                        </a>
                    {/LOOP: CAT}
                </div>
                IF("{TOP_ADSTATUS}"=="1"){
                <div class="quickad-section text-center margin-top-15" id="quickad-top">{TOP_ADSCODE}</div>
                {:IF}
            </div>
        </div>
    </div>
</div>

<!-- Features POST -->
<div class="section margin-top-45 padding-top-65 padding-bottom-65">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="section-headline margin-top-0 margin-bottom-35">
                    <h3>{LANG_PREMIUM_ADS}</h3>
                    <a href="{LINK_LISTING}?filter=premium" class="headline-link">{LANG_VIEW_MORE}</a>
                </div>
                <div class="listings-container grid-layout margin-top-35">
                    <div class="row" style="width: 100%;">
                        {LOOP: ITEM}
                            <div class="col-xl-4">
                                <div class='feat_property IF("{ITEM.highlight}"=="1"){ highlight {:IF}'>
                                    <div class="thumb">
                                        <img class="img-whp lazy-load" src="{SITE_URL}storage/products/thumb/{ITEM.picture}" alt="{ITEM.product_name}">
                                        <div class="thmb_cntnt">
                                            <ul class="tag mb0">
                                                IF("{ITEM.featured}"=="1"){ <li class="list-inline-item featured"><a href="#"> {LANG_FEATURED}</li> {:IF}
                                                IF("{ITEM.urgent}"=="1"){ <li class="list-inline-item urgent"><a href="#"> {LANG_URGENT}</li> {:IF}
                                            </ul>

                                            IF("{ITEM.price}"!="0"){
                                            <a class="fp_price" href="#">{ITEM.price}</a>
                                            {:IF}

                                        </div>
                                    </div>
                                    <div class="details">
                                        <div class="tc_content">
                                            <p class="text-thm"><a href="{ITEM.subcatlink}"><i class="la la-tags"></i> {ITEM.sub_category}</a></p>
                                            <h4><a href="{ITEM.link}">{ITEM.product_name}</a></h4>
                                            <p><i class="la la-map-marker"></i> {ITEM.location}</p>
                                            <ul class="prop_details mb0">
                                                {ITEM.cf_tpl}
                                            </ul>
                                        </div>
                                        <div class="listing-footer">
                                            <a class="author-link" href="{LINK_PROFILE}/{ITEM.username}"><i class="fa fa-user" aria-hidden="true"></i> {ITEM.username}</a>
                                            <span><i class="fa fa-calendar-o" aria-hidden="true"></i> {ITEM.created_at}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/LOOP: ITEM}
                    </div>
                </div>
                IF("{TOP_ADSTATUS}"=="1"){
                <div class="quickad-section text-center margin-top-15" id="quickad-top">{TOP_ADSCODE}</div>
                {:IF}
            </div>
        </div>
    </div>
</div>
<!-- Featured POST / End -->

<!-- Latest POST -->
<div class="section gray padding-top-65 padding-bottom-75">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="section-headline margin-top-0 margin-bottom-35">
                    <h3>{LANG_LATEST_ADS}</h3>
                    <a href="{LINK_LISTING}" class="headline-link">{LANG_VIEW_MORE}</a>
                </div>
                <div class="latest_property listings-container compact-layout margin-top-35">
                    {LOOP: ITEM2}
                        <div class='job-listing IF("{ITEM2.highlight}"=="1"){ highlight {:IF}'>
                            <div class="job-listing-details">
                                <div class="job-listing-company-logo">
                                    <img class="lazy-load" src="{SITE_URL}storage/products/thumb/{ITEM2.picture}" alt="{ITEM2.product_name}">
                                </div>
                                <div class="job-listing-description">

                                    <h3 class="job-listing-title margin-bottom-10"><a href="{ITEM2.link}">{ITEM2.product_name}</a>
                                        IF("{ITEM2.featured}"=="1"){ <div class="badge blue"> {LANG_FEATURED}</div> {:IF}
                                        IF("{ITEM2.urgent}"=="1"){ <div class="badge yellow"> {LANG_URGENT}</div> {:IF}
                                    </h3>
                                    <span class="job-type"><a href="{ITEM2.catlink}"><i class="la la-tags"></i> {ITEM2.category}</a></span>
                                    <div class="job-listing-footer">
                                        <ul class="prop_details">
                                            {ITEM2.cf_tpl}
                                        </ul>
                                        <ul>
                                            <li><a href="{LINK_PROFILE}/{ITEM2.username}"><i class="la la-user"></i> {ITEM2.username}</a></li>
                                            <li><i class="la la-map-marker"></i> {ITEM2.location}</li>
                                            IF("{ITEM2.price}"!="0"){
                                            <li><i class="la la-credit-card"></i> {ITEM2.price}</li>
                                            {:IF}
                                            <li><i class="la la-clock-o"></i> {ITEM2.created_at}</li>
                                        </ul>
                                    </div>
                                </div>

                            </div>

                        </div>
                    {/LOOP: ITEM2}
                </div>
                IF("{BOTTOM_ADSTATUS}"=="1"){
                <div class="quickad-section text-center margin-top-15" id="quickad-top">{BOTTOM_ADSCODE}</div>
                {:IF}
            </div>
        </div>
    </div>
</div>
<!-- Latest POST / End -->

IF({TESTIMONIALS_ENABLE} && {SHOW_TESTIMONIALS_HOME}){
<div class="section gray padding-top-55 padding-bottom-55">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <!-- Section Headline -->
                <div class="section-headline centered margin-top-0 margin-bottom-25">
                    <h3>{LANG_TESTIMONIALS}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="fullwidth-carousel-container margin-top-20">
        <div class="testimonial-carousel testimonials">
            {LOOP: TESTIMONIALS}
                <div class="single-testimonial">
                    <div class="single-inner style-2">
                        <div class="testimonial-content">
                            {TESTIMONIALS.content}
                        </div>
                        <div class="author-info">
                            <div class="image">
                                <img class="lazy-load" src="{SITE_URL}storage/testimonials/{TESTIMONIALS.image}" alt="{TESTIMONIALS.name}">
                            </div>
                            <h5 class="name">{TESTIMONIALS.name}</h5>
                            <span class="designation">{TESTIMONIALS.designation}</span>
                        </div>
                    </div>
                </div>
            {/LOOP: TESTIMONIALS}
        </div>
    </div>
</div>
{:IF}

IF({SHOW_MEMBERSHIPPLAN_HOME}){
<!-- Membership Plans -->
<div class="section padding-top-60 padding-bottom-75">
    <div class="container">
        <div class="row">

            <div class="col-xl-12">
                <!-- Section Headline -->
                <div class="section-headline centered margin-top-0 margin-bottom-75">
                    <h3>{LANG_MEMBERSHIPPLAN}</h3>
                </div>
            </div>


            <div class="col-xl-12">
                <form name="form1" method="post" action="{LINK_MEMBERSHIP}">
                    <div class="billing-cycle-radios margin-bottom-70">
                        IF("{TOTAL_MONTHLY}"!="0"){
                        <div class="radio billed-monthly-radio">
                            <input id="radio-monthly" name="billed-type" type="radio" value="monthly" checked="">
                            <label for="radio-monthly"><span class="radio-label"></span> {LANG_MONTHLY}</label>
                        </div>
                        {:IF}
                        IF("{TOTAL_ANNUAL}"!="0"){
                        <div class="radio billed-yearly-radio">
                            <input id="radio-yearly" name="billed-type" type="radio" value="yearly">
                            <label for="radio-yearly"><span class="radio-label"></span> {LANG_YEARLY}</label>
                        </div>
                        {:IF}
                        IF("{TOTAL_LIFETIME}"!="0"){
                        <div class="radio billed-lifetime-radio">
                            <input id="radio-lifetime" name="billed-type" type="radio" value="lifetime">
                            <label for="radio-lifetime"><span class="radio-label"></span> {LANG_LIFETIME}</label>
                        </div>
                        {:IF}
                    </div>
                    <!-- Pricing Plans Container -->
                    <div class="pricing-plans-container">
                        {LOOP: SUB_TYPES}
                            <!-- Plan -->
                            <div class='pricing-plan IF("{SUB_TYPES.recommended}"=="yes"){ recommended {:IF}'>
                                IF("{SUB_TYPES.recommended}"=="yes"){ <div class="recommended-badge">{LANG_RECOMMENDED}</div> {:IF}
                                <h3>{SUB_TYPES.title}</h3>
                                IF("{SUB_TYPES.id}"=="free" || "{SUB_TYPES.id}"=="trial"){
                                <div class="pricing-plan-label"><strong>
                                        IF("{SUB_TYPES.id}"=="free"){
                                        {LANG_FREE}
                                        {ELSE}
                                        {LANG_TRIAL}
                                        {:IF}
                                    </strong></div>
                                {ELSE}
                                IF("{TOTAL_MONTHLY}"!="0"){
                                <div class="pricing-plan-label billed-monthly-label"><strong>{SUB_TYPES.monthly_price}</strong>/ {LANG_MONTHLY}</div>
                                {:IF}
                                IF("{TOTAL_ANNUAL}"!="0"){
                                <div class="pricing-plan-label billed-yearly-label"><strong>{SUB_TYPES.annual_price}</strong>/ {LANG_YEARLY}</div>
                                {:IF}
                                IF("{TOTAL_LIFETIME}"!="0"){
                                <div class="pricing-plan-label billed-lifetime-label"><strong>{SUB_TYPES.lifetime_price}</strong> {LANG_LIFETIME}</div>
                                {:IF}
                                {:IF}
                                <div class="pricing-plan-features">
                                    <strong>{LANG_FEATURES_OF} {SUB_TYPES.title}</strong>
                                    <ul>
                                        <li>{SUB_TYPES.limit} {LANG_AD_POST_LIMIT}</li>
                                        <li>{SUB_TYPES.duration} {LANG_DAYS} {LANG_AD_EXP_IN}</li>
                                        <li>{LANG_FEATURED_FEE} {CURRENCY_SIGN}{SUB_TYPES.featured_fee} {LANG_FOR} {SUB_TYPES.featured_duration} {LANG_DAYS}</li>
                                        <li>
                                            {LANG_URGENT_FEE} {CURRENCY_SIGN}{SUB_TYPES.urgent_fee} {LANG_FOR} {SUB_TYPES.urgent_duration} {LANG_DAYS}
                                        </li>
                                        <li>
                                            {LANG_HIGHLIGHT_FEE} {CURRENCY_SIGN}{SUB_TYPES.highlight_fee} {LANG_FOR} {SUB_TYPES.highlight_duration} {LANG_DAYS}
                                        </li>
                                        <li>
                                            IF("{SUB_TYPES.top_search_result}"=="yes"){
                                            <span class="icon-text yes"><i class="icon-feather-check-circle margin-right-2"></i></span>
                                            {ELSE}
                                            <span class="icon-text no"><i class="icon-feather-x-circle margin-right-2"></i></span>
                                            {:IF}
                                            {LANG_TOP_SEARCH_RESULT}
                                        </li>
                                        <li>
                                            IF("{SUB_TYPES.show_on_home}"=="yes"){
                                            <span class="icon-text yes"><i class="icon-feather-check-circle margin-right-2"></i></span>
                                            {ELSE}
                                            <span class="icon-text no"><i class="icon-feather-x-circle margin-right-2"></i></span>
                                            {:IF}
                                            {LANG_SHOW_ON_HOME}
                                        </li>
                                        <li>
                                            IF("{SUB_TYPES.show_in_home_search}"=="yes"){
                                            <span class="icon-text yes"><i class="icon-feather-check-circle margin-right-2"></i></span>
                                            {ELSE}
                                            <span class="icon-text no"><i class="icon-feather-x-circle margin-right-2"></i></span>
                                            {:IF}
                                            {LANG_SHOW_IN_HOME_SEARCH}
                                        </li>
                                        {SUB_TYPES.custom_settings}
                                    </ul>
                                </div>
                                IF("{SUB_TYPES.Selected}"=="0"){
                                <button type="submit" class="button full-width margin-top-20 ripple-effect" name="upgrade" value="{SUB_TYPES.id}">{LANG_UPGRADE}</button>
                                {:IF}
                                IF("{SUB_TYPES.Selected}"=="1"){
                                <a href="javascript:void(0);" class="button full-width margin-top-20 ripple-effect">
                                    {LANG_CURRENT_PLAN}
                                </a>
                                {:IF}
                            </div>
                        {/LOOP: SUB_TYPES}
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Membership Plans / End-->
{:IF}

IF({BLOG_ENABLE} && {SHOW_BLOG_HOME}){
<div class="section padding-top-55 padding-bottom-65">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="section-headline centered margin-top-0 margin-bottom-45">
                    <h3>{LANG_RECENT_BLOG}</h3>
                </div>
                <div class="listings-container grid-layout grid-layout-3">
                    {LOOP: RECENT_BLOG}
                        <div class="job-listing blog-listing">
                            <div class="job-listing-details">
                                IF({BLOG_BANNER}){
                                <div class="job-listing-company-logo">
                                    <a href="{RECENT_BLOG.link}">
                                        <img class="lazy-load" src="{SITE_URL}storage/blog/{RECENT_BLOG.image}" alt="{RECENT_BLOG.title}">
                                    </a>
                                </div>
                                {:IF}
                                <div class="job-listing-description">
                                    <div class="blog-cat">{RECENT_BLOG.categories}</div>
                                    <h3 class="job-listing-title"><a href="{RECENT_BLOG.link}">{RECENT_BLOG.title}</a>
                                    </h3>

                                    <p class="job-listing-text margin-top-10">{RECENT_BLOG.description}</p>
                                </div>
                            </div>
                            <div class="job-listing-footer">
                                <ul>
                                    <li>
                                        <img src="{SITE_URL}storage/profile/{RECENT_BLOG.author_pic}" class="author-avatar"> {LANG_BY}
                                        <a href="{RECENT_BLOG.author_link}">{RECENT_BLOG.author}</a>
                                    </li>
                                    <li><i class="la la-clock-o"></i> {RECENT_BLOG.created_at}</li>
                                </ul>
                            </div>
                        </div>
                    {/LOOP: RECENT_BLOG}
                </div>
                IF("{BOTTOM_ADSTATUS}"=="1"){
                <div class="quickad-section text-center margin-top-15" id="quickad-top">{BOTTOM_ADSCODE}</div>
                {:IF}
            </div>
        </div>
    </div>
</div>
{:IF}


<script>
    var loginurl = "{LINK_LOGIN}?ref=index.php";

    (function($) {
        var $window = $(window),
            $html = $('.compact-list-layout');

        $window.resize(function resize(){
            if ($window.width() < 768) {
                return $html.addClass('grid-layout');
            }

            $html.removeClass('grid-layout');
        }).trigger('resize');
    })(jQuery);
</script>

<!-- world-gmap -->
<link href="{SITE_URL}includes/assets/plugins/map/google/map-marker.css" type="text/css" rel="stylesheet">
<script type='text/javascript' src='{SITE_URL}includes/assets/plugins/map/google/jquery-migrate-1.2.1.min.js'></script>
<script type='text/javascript' src='//maps.google.com/maps/api/js?key={GMAP_API_KEY}&#038;libraries=places%2Cgeometry&#038;ver=2.2.1'></script>
<script type='text/javascript' src='{SITE_URL}includes/assets/plugins/map/google/richmarker-compiled.js'></script>
<script type='text/javascript' src='{SITE_URL}includes/assets/plugins/map/google/markerclusterer_packed.js'></script>
<script type='text/javascript' src='{SITE_URL}includes/assets/plugins/map/google/gmapAdBox.js'></script>
<script type='text/javascript' src='{SITE_URL}includes/assets/plugins/map/google/maps.js'></script>
<script>
    $('#address-autocomplete').on('keyup keypress', function (e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });

    var _latitude = {LATITUDE};
    var _longitude = {LONGITUDE};
    var element = "road_map";
    var site_url = '{SITE_URL}';
    var path = '{SITE_URL}';
    var color = '{MAP_COLOR}';
    var optimizedDatabaseLoading = 0;
    var markerTarget = "gmapAdBox";
    var sidebarResultTarget = "sidebar";
    var showMarkerLabels = true;
    var mapDefaultZoom = {ZOOM};
    var getCity = true;
    var Countries = '{SPECIFIC_COUNTRY}';
    if (Countries != "") {
        var getCountry = Countries;
    }
    else {
        var getCountry = "all";
    }

    heroMap(_latitude, _longitude, element, markerTarget, sidebarResultTarget, showMarkerLabels, mapDefaultZoom);
    loginurl = "{LINK_LOGIN}?ref=index.php";
</script>
{OVERALL_FOOTER}
