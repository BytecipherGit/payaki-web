{OVERALL_HEADER}
<link href="{SITE_URL}templates/{TPL_NAME}/css/post-ad/checkbox-radio.css" type="text/css" rel="stylesheet" >

<style>

    .margin-bottom-70 {
        margin-bottom: 70px!important;
    }
    .billing-cycle-radios .radio label::before {
         top: 6px;
     }
    .billing-cycle-radios .radio label::after {
        top: 9px;
    }
    .billing-cycle-radios{display:block;margin:0 auto;text-align:center}
    .billing-cycle-radios.text-align-left{text-align:left}

    .pricing-plans-container{display:flex}
    .pricing-plan{flex:1;padding:30px;position:relative;margin-right:30px;border-radius:4px;border:1px solid #e0e0e0;box-shadow:0 1px 4px 0 rgba(0,0,0,.05)}
    .pricing-plan:last-of-type{margin-right:0}
    .pricing-plan h3{font-size:20px;font-weight:600}
    .pricing-plan p{margin:0}
    .billed-yearly-label{display:none}
    .billed-yearly .billed-yearly-label{display:block}
    .billed-yearly .billed-monthly-label{display:none}
    .pricing-plan-label{background:#f6f6f6;border-radius:4px;font-size:18px;color:#888;text-align:center;line-height:24px;padding:15px;margin:22px 0}
    .pricing-plan-label strong{font-size:32px;font-weight:700;color:#333;margin-right:5px;line-height:30px}
    .recommended .pricing-plan-label{background-color:var(--theme-color-0_06);color:var(--theme-color-1)}
    .recommended .pricing-plan-label strong{color:var(--theme-color-1)}
    .pricing-plan-features strong{color:#333;font-weight:600;margin-bottom:5px;line-height:24px;display:inline-block}
    .pricing-plan-features ul{padding:0;margin:0}
    .pricing-plan-features ul li{display:block;margin:0;padding:3px 0;line-height:24px}
    .pricing-plan .button:hover,.pricing-plan.recommended .button{color:#fff;background-color:var(--theme-color-1);box-shadow:0 4px 12px var(--theme-color-0_15)}
    .pricing-plan .button{color:var(--theme-color-1);background-color:#fff;border:1px solid var(--theme-color-1);box-shadow:0 4px 12px var(--theme-color-0_1)}
    .pricing-plan .button:hover{box-shadow:0 4px 12px var(--theme-color-0_15)}
    .pricing-plan.recommended{box-shadow:0 0 45px var(--theme-color-0_09)}
    @media (max-width:992px){
        .pricing-plans-container{display:block}
        .pricing-plan{margin-bottom:30px;flex:auto;width:100%}
    }
    .pricing-plan.recommended:last-of-type {
        margin-right: 0;
    }

    .pricing-plan.recommended {
        box-shadow: 0 0 45px rgba(0, 0, 0, .09);
        padding: 35px;
        margin: 0 15px;
    }
    .pricing-plan .recommended-badge {
        background-color: #66676b;
        color: #fff;
        position: absolute;
        width: 100%;
        height: 45px;
        top: -45px;
        left: 0;
        text-align: center;
        border-radius: 4px 4px 0 0;
        font-weight: 600;
        line-height: 45px;
    }
    .pricing-plan .recommended-badge {
        background-color: var(--theme-color-1);
    }
    .billed-yearly-label, .billed-lifetime-label { display: none }
    .billed-yearly .billed-yearly-label, .billed-lifetime .billed-lifetime-label { display: block }
    .billed-yearly .billed-monthly-label, .billed-lifetime .billed-monthly-label { display: none }
    .headline-border-top{border-top:1px solid #e0e0e0}
    .boxed-widget{background-color:#f9f9f9;padding:0;transform:translate3d(0,0,0);z-index:90;position:relative;border-radius:4px;overflow:hidden}
    .boxed-widget-headline{color:#333;font-size:20px;padding:20px 30px;background-color:#f0f0f0;color:#333;position:relative;border-radius:4px 4px 0 0}
    .boxed-widget-headline h3{font-size:20px;padding:0;margin:0}
    .boxed-widget-inner{padding:30px}
    .boxed-widget ul{list-style:none;padding:0;margin:0}
    .boxed-widget ul li span{float:right;color:#333;font-weight:600}
    .boxed-widget ul li{color:#666;padding-bottom:1px}
    .boxed-widget.summary li.total-costs{font-size:18px;border-top:1px solid #e4e4e4;padding-top:18px;margin-top:18px}
    .boxed-widget-footer{border-top:1px solid #e4e4e4;width:100%;padding:20px 30px}
    .boxed-widget-footer .checkbox label{margin-bottom:0}
    .boxed-widget.summary li.total-costs span{font-weight:700;color:var(--theme-color-1)}
    .listing-item-container.compact.order-summary-widget .listing-item{border-radius:4px 4px 0 0;cursor:default;height:240px}
    .listing-item-container.compact.order-summary-widget{margin-bottom:0}
    .listing-item-container.compact.order-summary-widget:hover{transform:none}
    .billing-cycle{display:flex}
    .billing-cycle .radio{flex:1;margin:5px 20px 5px 0}
    .billing-cycle .radio label{border-radius:4px;border:2px solid #eee;padding:25px;height:100%;align-self:center}
    .billing-cycle .radio:last-of-type{margin-right:0}
    .billing-cycle .radio input[type=radio]+label .radio-label{position:relative;top:2px;margin-right:7px}
    .billing-cycle-details{display:block;padding-left:30px}
    .discounted-price-tag,.regular-price-tag{font-size:14px;background:#e0f5d7;color:#449626;border-radius:4px;line-height:20px;padding:4px 10px;flex-grow:0;flex:auto;width:auto;transition:.3s;margin-top:6px;margin-right:5px;display:inline-block}
    .line-through{text-decoration:line-through;background-color:#fbf6dd;color:#a18d29}
    @media (max-width:768px){
        .billing-cycle{display:flex;flex-direction:column}
        .billing-cycle .radio{margin-right:0}
    }
</style>
<!-- Titlebar
================================================== -->
<div id="titlebar" class="margin-bottom-0">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>{LANG_MEMBERSHIP}</h2>
                <div class="breadcrumb-section">
                    <!-- breadcrumb -->
                    <ol class="breadcrumb">
                        <li><a href="{LINK_INDEX}"><i class="fa fa-home"></i> {LANG_HOME}</a></li>
                        <li class="active">{LANG_MEMBERSHIP}</li>
                        <div class="pull-right back-result"><a href="{LINK_DASHBOARD}"><i class="fa fa-angle-double-left"></i> {LANG_BACK_RESULT}</a></div>
                    </ol>
                    <!-- breadcrumb -->
                </div>

            </div>
        </div>
    </div>
</div>
<!-- Page Content
================================================== -->
<div class="container">
    <div class="row">
        <div class="col-xl-12">
            <form name="form1" method="post">
                <div class="billing-cycle-radios margin-bottom-70">

                    IF("{TOTAL_MONTHLY}"!="0"){
                    <div class="radio billed-monthly-radio radio-primary radio-inline">
                        <input id="radio-monthly" name="billed-type" type="radio" value="monthly" checked="">
                        <label for="radio-monthly"><span class="radio-label"></span> {LANG_MONTHLY}</label>
                    </div>
                    {:IF}
                    IF("{TOTAL_ANNUAL}"!="0"){
                    <div class="radio billed-yearly-radio radio-primary radio-inline">
                        <input id="radio-yearly" name="billed-type" type="radio" value="yearly">
                        <label for="radio-yearly"><span class="radio-label"></span> {LANG_YEARLY}</label>
                    </div>
                    {:IF}
                    IF("{TOTAL_LIFETIME}"!="0"){
                    <div class="radio billed-lifetime-radio radio-primary radio-inline">
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
                            <button type="submit" class="btn btn-primary text-align-center margin-top-20 ripple-effect" name="upgrade" value="{SUB_TYPES.id}">{LANG_UPGRADE}</button>
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
<div class="margin-top-80"></div>
{OVERALL_FOOTER}
<script>
    $(document).ready(function () {
        $('.billing-cycle-radios').on("click", function () {
            if ($('.billed-yearly-radio input').is(':checked')) {
                $('.pricing-plans-container').addClass('billed-yearly').removeClass('billed-lifetime');
            }
            if ($('.billed-monthly-radio input').is(':checked')) {
                $('.pricing-plans-container').removeClass('billed-yearly').removeClass('billed-lifetime');
            }
            if ($('.billed-lifetime-radio input').is(':checked')) {
                $('.pricing-plans-container').addClass('billed-lifetime').removeClass('billed-yearly');
            }
        });
    });
</script>
