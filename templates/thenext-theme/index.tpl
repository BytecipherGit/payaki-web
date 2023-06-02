{OVERALL_HEADER}
    <div class="intro-banner" data-background-image="{SITE_URL}storage/banner/{BANNER_IMAGE}">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="banner-headline">
                        <h3><strong>{LANG_HOME_BANNER_HEADING}</strong>
                            <br>
                            <span>{LANG_HOME_BANNER_TAGLINE}</span></h3>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <form autocomplete="off" method="get" action="{LINK_LISTING}" accept-charset="UTF-8">
                    <div class="intro-banner-search-form margin-top-45">
                        <div class="intro-search-field">
                            <input id="intro-keywords" type="text" class="qucikad-ajaxsearch-input" placeholder="{LANG_WHAT_LOOK_FOR}" data-prev-value="0" data-noresult="{LANG_MORE_RESULTS_FOR}">
                            <i class="qucikad-ajaxsearch-close fa fa-times-circle" aria-hidden="true" style="display: none;"></i>
                            <div id="qucikad-ajaxsearch-dropdown" size="0" tabindex="0">
                                <ul>
                                    {LOOP: CATEGORY}
                                    <li class="qucikad-ajaxsearch-li-cats" data-catid="{CATEGORY.slug}">
                                        IF("{CATEGORY.picture}"==""){
                                        <i class="qucikad-as-caticon {CATEGORY.icon}"></i>
                                        {:IF}
                                        IF("{CATEGORY.picture}"!=""){
                                        <img src="{CATEGORY.picture}"/>
                                        {:IF}
                                        <span class="qucikad-as-cat">{CATEGORY.name}</span>
                                    </li>
                                    {/LOOP: CATEGORY}
                                </ul>

                                <div style="display:none" id="def-cats">

                                </div>
                            </div>
                        </div>
                        <div class="intro-search-field with-autocomplete live-location-search">
                            <div class="input-with-icon">
                                <input type="text" id="searchStateCity" name="location" placeholder="{LANG_WHERE}">
                                <i class="la la-map-marker"></i>
                                <div data-option="{AUTO_DETECT_LOCATION}" class="loc-tracking"><i class="fa fa-crosshairs"></i></div>
                                <input type="hidden" name="latitude" id="latitude" value="">
                                <input type="hidden" name="longitude" id="longitude" value="">
                                <input type="hidden" name="placetype" id="searchPlaceType" value="">
                                <input type="hidden" name="placeid" id="searchPlaceId" value="">
                                <input type="hidden" id="input-keywords" name="keywords" value="">
                                <input type="hidden" id="input-maincat" name="cat" value=""/>
                                <input type="hidden" id="input-subcat" name="subcat" value=""/>
                            </div>
                        </div>
                        <div class="intro-search-button">
                            <button class="button ripple-effect">{LANG_SEARCH}</button>
                        </div>
                    </div>
                    </form>

                    {AD_HOME_PAGE_BELOW_SEARCH_SECTION}
                </div>
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
                                    <img class="lazy-load" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAANSURBVBhXYzh8+PB/AAffA0nNPuCLAAAAAElFTkSuQmCC"  data-original="{CAT.picture}" alt="{CAT.main_title}">
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
                    {AD_HOME_PAGE_BELOW_CATEGORY_SECTION}
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
                                        <img class="img-whp lazy-load" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAANSURBVBhXYzh8+PB/AAffA0nNPuCLAAAAAElFTkSuQmCC"  data-original="{SITE_URL}storage/products/thumb/{ITEM.picture}" alt="{ITEM.product_name}">
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
                                            <p><i class="la la la-clock-o"></i>{ITEM.expiretime}</p>
                                            {ITEM.verified}
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
                {AD_HOME_PAGE_BELOW_FEATURED_SECTION}
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
                                    <img class="lazy-load" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAANSURBVBhXYzh8+PB/AAffA0nNPuCLAAAAAElFTkSuQmCC"  data-original="{SITE_URL}storage/products/thumb/{ITEM2.picture}" alt="{ITEM2.product_name}">
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
                                            <li><i class="la la-clock-o"></i> {ITEM2.expiretime}</li>
                                            <li>{ITEM2.verified}</li>
                                        </ul>
                                    </div>
                                </div>

                            </div>

                        </div>
                    {/LOOP: ITEM2}
                </div>
                {AD_HOME_PAGE_BELOW_LATEST_SECTION}
            </div>
        </div>
    </div>
</div>
<!-- Latest POST / End -->

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
<script type='text/javascript' src='//maps.google.com/maps/api/js?key={GMAP_API_KEY}&#038;libraries=places%2Cgeometry&#038;ver=2.2.1'></script>
{OVERALL_FOOTER}
