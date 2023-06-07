{OVERALL_HEADER}
<!-- Titlebar
================================================== -->
<div id="titlebar" class="gradient">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>{LANG_MEMBERSHIPPLAN}</h2>
                <!-- Breadcrumbs -->
                <nav id="breadcrumbs">
                    <ul>
                        <li><a href="{LINK_INDEX}">{LANG_HOME}</a></li>
                        <li>{LANG_MEMBERSHIPPLAN}</li>
                    </ul>
                </nav>
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
                    <div class="row">
                        {LOOP: SUB_TYPES}
                        <div class="col-12">
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
                        </div>
                        {/LOOP: SUB_TYPES}
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="margin-top-80"></div>
{OVERALL_FOOTER}
