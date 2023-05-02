{OVERALL_HEADER}
<script src="https://checkout.stripe.com/v2/checkout.js"></script>
<div id="titlebar">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>{LANG_ADVERTISE_WITH_US}</h2>
                <!-- Breadcrumbs -->
                <nav id="breadcrumbs">
                    <ul>
                        <li><a href="{LINK_INDEX}">{LANG_HOME}</a></li>
                        <li>{LANG_ADVERTISE_WITH_US}</li>
                    </ul>
                </nav>

            </div>
        </div>
    </div>
</div>
<div class="container margin-bottom-50">

    <div class="row">
        <div class="col-sm-12">
            <div class="found-section section">
                IF({LOGGED_IN}){
                <div class="section html-pages"><div class="qbm-box"></div></div>
                {ELSE}
                <h1 class="margin-bottom-20">{LANG_LOGIN_REQUIRED}</h1>

                <p>{LANG_LOGIN_REQ_ACCESS}</p>

                <a href="#sign-in-dialog" class="button ripple-effect popup-with-zoom-anim ">{LANG_CLICK_HERE} </a>
                {:IF}
            </div>
        </div>
    </div>
</div>
{OVERALL_FOOTER}
