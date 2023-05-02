<!DOCTYPE html>
<html lang="{LANG_CODE}" dir="{LANGUAGE_DIRECTION}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{SITE_TITLE}</title>
    <link href="{SITE_URL}includes/assets/login_ajax/css/bootstrap.min.css" rel="stylesheet">
    <link href="{SITE_URL}includes/assets/login_ajax/css/common.css" rel="stylesheet">


    <link href="https://fonts.googleapis.com/css?family=Poppins:400,600,700&display=swap" rel="stylesheet">
    <link href="{SITE_URL}includes/assets/login_ajax/css/theme-03.css" rel="stylesheet">
    <script async>var ajaxurl = "{APP_URL}user-ajax.php";</script>
</head>

<body>
<div class="forny-container">
    <div class="forny-inner">
        <div class="forny-form">

            <!--Login Form-->
            <div class="forny-content show-it" id="login-form">
                <div class="mb-8 text-center forny-logo">
                    <a href="{SITE_URL}"><img src="{SITE_URL}storage/logo/{SITE_LOGO}" style="max-width: 150px"></a>
                </div>
                <div class="text-center">
                    <h4>{LANG_WELCOME_BACK}!</h4>
                    <p class="mb-10" id="login-status">{LANG_ENTER_DETAILS}</p>
                </div>

                <form action="ajaxlogin" id="lg-form">

                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="16" viewBox="0 0 24 16">
                                        <g transform="translate(0)">
                                            <path d="M23.983,101.792a1.3,1.3,0,0,0-1.229-1.347h0l-21.525.032a1.169,1.169,0,0,0-.869.4,1.41,1.41,0,0,0-.359.954L.017,115.1a1.408,1.408,0,0,0,.361.953,1.169,1.169,0,0,0,.868.394h0l21.525-.032A1.3,1.3,0,0,0,24,115.062Zm-2.58,0L12,108.967,2.58,101.824Zm-5.427,8.525,5.577,4.745-19.124.029,5.611-4.774a.719.719,0,0,0,.109-.946.579.579,0,0,0-.862-.12L1.245,114.4,1.23,102.44l10.422,7.9a.57.57,0,0,0,.7,0l10.4-7.934.016,11.986-6.04-5.139a.579.579,0,0,0-.862.12A.719.719,0,0,0,15.977,110.321Z" transform="translate(0 -100.445)"/>
                                        </g>
                                    </svg>
                                </span>
                            </div>
                            <input required  class="form-control" type="text" id="username" placeholder="{LANG_USERNAME} / {LANG_EMAIL}">
                        </div>
                    </div>
                    <div class="form-group password-field">

                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="24" viewBox="0 0 16 24">
                                        <g transform="translate(0)">
                                            <g transform="translate(5.457 12.224)">
                                                <path d="M207.734,276.673a2.543,2.543,0,0,0-1.749,4.389v2.3a1.749,1.749,0,1,0,3.5,0v-2.3a2.543,2.543,0,0,0-1.749-4.389Zm.9,3.5a1.212,1.212,0,0,0-.382.877v2.31a.518.518,0,1,1-1.035,0v-2.31a1.212,1.212,0,0,0-.382-.877,1.3,1.3,0,0,1-.412-.955,1.311,1.311,0,1,1,2.622,0A1.3,1.3,0,0,1,208.633,280.17Z" transform="translate(-205.191 -276.673)"/>
                                            </g>
                                            <path d="M84.521,9.838H82.933V5.253a4.841,4.841,0,1,0-9.646,0V9.838H71.7a1.666,1.666,0,0,0-1.589,1.73v10.7A1.666,1.666,0,0,0,71.7,24H84.521a1.666,1.666,0,0,0,1.589-1.73v-10.7A1.666,1.666,0,0,0,84.521,9.838ZM74.346,5.253a3.778,3.778,0,1,1,7.528,0V9.838H74.346ZM85.051,22.27h0a.555.555,0,0,1-.53.577H71.7a.555.555,0,0,1-.53-.577v-10.7a.555.555,0,0,1,.53-.577H84.521a.555.555,0,0,1,.53.577Z" transform="translate(-70.11)"/>
                                        </g>
                                    </svg>
                                </span>
                            </div>
                            <input required  class="form-control" id="password" type="password" placeholder="{LANG_PASSWORD}">

                            <div class="input-group-append cursor-pointer">
                                <span class="input-group-text">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="16" viewBox="0 0 24 16">
                                        <g transform="translate(0 0)">
                                            <g transform="translate(0 0)">
                                                <path d="M23.609,117.568a15.656,15.656,0,0,0-5.045-4.859,12.823,12.823,0,0,0-6.38-1.811c-.062,0-.309,0-.371,0a12.823,12.823,0,0,0-6.38,1.811,15.656,15.656,0,0,0-5.045,4.859,2.464,2.464,0,0,0,0,2.658,15.656,15.656,0,0,0,5.045,4.859,12.822,12.822,0,0,0,6.38,1.811c.062,0,.309,0,.371,0a12.823,12.823,0,0,0,6.38-1.811,15.656,15.656,0,0,0,5.045-4.859A2.464,2.464,0,0,0,23.609,117.568Zm-17.74,6.489a14.622,14.622,0,0,1-4.712-4.538,1.155,1.155,0,0,1,0-1.245,14.621,14.621,0,0,1,4.712-4.538,12.747,12.747,0,0,1,1.586-.79,8.964,8.964,0,0,0,0,11.9A12.748,12.748,0,0,1,5.869,124.057ZM12,125.75c-3.213,0-5.827-3.074-5.827-6.853s2.614-6.853,5.827-6.853,5.827,3.074,5.827,6.853S15.211,125.75,12,125.75Zm10.841-6.23a14.621,14.621,0,0,1-4.712,4.538,12.737,12.737,0,0,1-1.585.788,8.964,8.964,0,0,0,0-11.9,12.74,12.74,0,0,1,1.587.791,14.622,14.622,0,0,1,4.712,4.538A1.155,1.155,0,0,1,22.839,119.52Z" transform="translate(0.002 -110.897)"/>
                                            </g>
                                            <g transform="translate(9.565 5.565)">
                                                <path d="M205.24,202.8a2.435,2.435,0,1,0,2.435,2.435A2.438,2.438,0,0,0,205.24,202.8Zm0,3.917a1.482,1.482,0,1,1,1.482-1.482A1.483,1.483,0,0,1,205.24,206.721Z" transform="translate(-202.805 -202.804)"/>
                                            </g>
                                        </g>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>


                    <div class="row mt-6 mb-6">
                        <div class="col-6 align-items-center d-flex" style="display: none !important;">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="cb1">
                                <label class="custom-control-label" for="cb1">{LANG_REMEMBER_ME}</label>
                            </div>
                        </div>
                        <div class="col-12 text-right"><a href="#" class="forgot-page">{LANG_FORGOTPASS}</a></div>
                    </div>

                    <div><button class="btn btn-primary btn-block" id="login">{LANG_LOGIN}</button></div>

                </form>
                <div class="mt-5 mb-5 text-center"><span>{LANG_OR_LOG_WITH}</span></div>

                <div class="text-center">
                    IF("{FACEBOOK_APP_ID}"!=""){
                    <div class="d-inline-block mr-1">
                        <button class="btn btn-icon btn-flat text-facebook facebook-login" title="{LANG_LOGIN_FACEBOOK}">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 xmlns:xlink="http://www.w3.org/1999/xlink" width="32" height="32" viewBox="0 0 32 32">
                                <defs>
                                    <style>.a{fill:currentColor;}.b{fill:#fff;fill-rule:evenodd;}</style>
                                    <linearGradient id="a" y1="0.5" x2="1" y2="0.5" gradientUnits="objectBoundingBox">
                                        <stop offset="0" stop-color="#245a9c"/>
                                        <stop offset="1" stop-color="#2b189c"/>
                                    </linearGradient>
                                </defs>
                                <circle class="a" cx="16" cy="16" r="16" transform="translate(0 0)"/>
                                <path class="b" d="M179.516,108.125v9.713a.258.258,0,0,0,.261.255h3.7a.258.258,0,0,0,.261-.255v-9.87h2.679a.259.259,0,0,0,.261-.229l.254-2.972a.255.255,0,0,0-.261-.274h-2.94v-2.116a.9.9,0,0,1,.917-.895h2.063a.258.258,0,0,0,.261-.255V98.255a.258.258,0,0,0-.261-.255H183.22a3.665,3.665,0,0,0-3.71,3.619v2.868h-1.848a.258.258,0,0,0-.261.255v2.972a.258.258,0,0,0,.261.255h1.855Z" transform="translate(-166.208 -91.946)"/>
                            </svg>
                        </button>
                    </div>
                    {:IF}
                    IF("{GOOGLE_APP_ID}"!=""){
                    <div class="d-inline-block mr-1">
                        <button class="btn btn-icon btn-flat text-google gmail-login" title="{LANG_LOGIN_GOOGLE}">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 xmlns:xlink="http://www.w3.org/1999/xlink" width="32" height="32" viewBox="0 0 32 32">
                                <defs>
                                    <style>.a{fill:currentColor;}.b{fill:#fff;}</style>
                                    <linearGradient id="a" x1="0.146" y1="0.146" x2="0.853" y2="0.853" gradientUnits="objectBoundingBox">
                                        <stop offset="0" stop-color="#ff4c3c"/>
                                        <stop offset="0.956" stop-color="#ef053e"/>
                                        <stop offset="1" stop-color="#ee023e"/>
                                    </linearGradient>
                                </defs>
                                <circle class="a" cx="16" cy="16" r="16" transform="translate(0 0)"/>
                                <path class="b" d="M89.15,145.395a.77.77,0,0,0-.007.121v2.86h3.965a3.38,3.38,0,0,1-1.453,2.23,4.254,4.254,0,0,1-2.15.69,4.361,4.361,0,0,1-1.607-.187,4.46,4.46,0,0,1-3.054-3.851,4.293,4.293,0,0,1,.087-1.339,4.373,4.373,0,0,1,4.869-3.409,3.962,3.962,0,0,1,2.177,1.051l2.156-2.156a8.877,8.877,0,0,0-1.313-.964,7.028,7.028,0,0,0-3.509-.958c-.234-.007-.475.013-.71.02a5.771,5.771,0,0,0-1.219.188,7.425,7.425,0,0,0-5.612,6.757,6.9,6.9,0,0,0,.154,2,7.335,7.335,0,0,0,2.893,4.427,7.244,7.244,0,0,0,3.951,1.4,7.8,7.8,0,0,0,2.424-.241,6.432,6.432,0,0,0,3.643-2.391,7.944,7.944,0,0,0,1.272-6.242Z" transform="translate(-76.847 -130.881)"/>
                                <path class="b" d="M330.806,193.278h-2.5V190.7h-2.023v2.578H323.7V195.3h2.578v2.585H328.3V195.3h2.585v-2.023Z" transform="translate(-302.985 -178.663)"/>
                            </svg>
                        </button>
                    </div>
                    {:IF}
                    <!--
                    <div class="d-inline-block">
                        <button class="btn btn-icon btn-flat text-twitter">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 xmlns:xlink="http://www.w3.org/1999/xlink" width="32" height="32" viewBox="0 0 32 32">
                                <defs>
                                    <style>.a{fill:currentColor;}.b{fill:#fff;}</style>
                                    <linearGradient id="a" x1="0.146" y1="0.146" x2="0.854" y2="0.854" gradientUnits="objectBoundingBox">
                                        <stop offset="0" stop-color="#00cdff"/>
                                        <stop offset="1" stop-color="#0080ff"/>
                                    </linearGradient>
                                </defs>
                                <circle class="a" cx="16" cy="16" r="16" transform="translate(0 0)"/>
                                <path class="b" d="M118.972,128.5a7.992,7.992,0,0,1-1.974.557c.319-.052.782-.622.971-.855a3.6,3.6,0,0,0,.658-1.185c.02-.032.033-.078-.007-.1a.115.115,0,0,0-.1.006,10.112,10.112,0,0,1-2.339.887.154.154,0,0,1-.163-.045,1.533,1.533,0,0,0-.2-.207,4.325,4.325,0,0,0-1.133-.693,3.982,3.982,0,0,0-1.739-.278,4.2,4.2,0,0,0-2.977,1.541,4.03,4.03,0,0,0-.795,1.58,4.242,4.242,0,0,0-.039,1.67c.013.091.007.1-.078.091a12.519,12.519,0,0,1-8.038-4.066c-.091-.1-.143-.1-.221.006a4.025,4.025,0,0,0,.7,4.785,6.383,6.383,0,0,0,.5.434,4.065,4.065,0,0,1-1.55-.434c-.091-.058-.143-.026-.15.078a2.8,2.8,0,0,0,.026.46,4.093,4.093,0,0,0,2.541,3.263,2.359,2.359,0,0,0,.515.155,4.691,4.691,0,0,1-1.524.045c-.111-.019-.156.032-.111.142a4.289,4.289,0,0,0,3.2,2.661c.143.026.287.026.436.058-.007.013-.02.013-.026.026a5.065,5.065,0,0,1-2.182,1.153,7.805,7.805,0,0,1-3.316.421c-.176-.026-.215-.026-.267,0s-.007.071.052.117c.228.149.456.278.69.408a11.039,11.039,0,0,0,2.215.881,11.059,11.059,0,0,0,14.631-11.383c0-.123.15-.194.234-.259a7.638,7.638,0,0,0,1.557-1.612.479.479,0,0,0,.1-.311v-.019C119.05,128.444,119.05,128.457,118.972,128.5Z" transform="translate(-93.352 -118.563)"/>
                            </svg>
                        </button>
                    </div>
                    -->

                    <div class="d-inline-block">
                        <button class="btn btn-icon btn-flat text-twitter mobile-page" title="{LANG_LOGIN_WITH_PHONE}">
                            <img src="https://img.icons8.com/flat_round/64/000000/mobile-email.png" width="32px"/>
                        </button>
                    </div>
                </div>

                <div class="text-center mt-6">
                    {LANG_DONT_HAVE_ACCOUNT} <a href="#" class="signup-page">{LANG_REGISTER}</a>
                </div>
            </div>

            <!--Signup Form-->
            <div class="forny-content hide-it" id="signup-form">
                <div class="mb-8 text-center forny-logo">
                    <a href="{SITE_URL}"><img src="{SITE_URL}storage/logo/{SITE_LOGO}" style="max-width: 150px"></a>
                </div>
                <div class="text-center">
                    <h4>{LANG_CREATE_AN_ACCOUNT}</h4>
                    <p class="mb-10" id="signup-status">{LANG_SETUP_AC_TXT}</p>
                </div>
                <form action="ajaxsignup" name="signup-form" id="reg-form" method="post">

                    <div class="form-group">

                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="24" viewBox="0 0 18 24">
                                        <g transform="translate(-61.127)">
                                            <g transform="translate(61.127)">
                                                <path d="M75.6,15.584A3.128,3.128,0,0,1,72.452,12.7a5.374,5.374,0,0,0,1.229-1.234,7.564,7.564,0,0,0,1.331-3.524.537.537,0,0,0,.134-.191,5.891,5.891,0,0,0,.445-2.264A5.275,5.275,0,0,0,70.574,0a4.6,4.6,0,0,0-2.088.5,3.62,3.62,0,0,0-.738.134,4.171,4.171,0,0,0-2.6,2.407,6.062,6.062,0,0,0-.292,3.924,6.386,6.386,0,0,0,.27.831.537.537,0,0,0,.125.185A6.772,6.772,0,0,0,67.8,12.7a3.129,3.129,0,0,1-3.146,2.885,3.689,3.689,0,0,0-3.53,3.706V23.46a.536.536,0,0,0,.532.54H78.595a.536.536,0,0,0,.532-.54V19.291A3.688,3.688,0,0,0,75.6,15.584ZM68.044,1.676a2.588,2.588,0,0,1,.61-.1.526.526,0,0,0,.224-.061,3.576,3.576,0,0,1,1.7-.433,4.2,4.2,0,0,1,3.951,4.41c0,.073,0,.146-.005.218A2.3,2.3,0,0,0,72.862,5H69.234a.974.974,0,0,1-.593-.2,1.006,1.006,0,0,1-.328-.432.649.649,0,0,0-.645-.413.656.656,0,0,0-.592.5,5.033,5.033,0,0,1-1.2,2.188C65.336,4.406,66.3,2.187,68.044,1.676Zm-.463,9.346a6.408,6.408,0,0,1-1.29-3.289,6.123,6.123,0,0,0,1.549-2.2A2.083,2.083,0,0,0,68,5.669a2.021,2.021,0,0,0,1.23.414h3.629a1.264,1.264,0,0,1,1.153.76s0,.008,0,.011c0,3.051-1.744,5.532-3.887,5.532A3.315,3.315,0,0,1,67.581,11.022ZM68.8,13.23a3.821,3.821,0,0,0,2.647,0A4.241,4.241,0,0,0,73,15.78l-2.8,4.041a.091.091,0,0,1-.151,0l-2.8-4.042A4.242,4.242,0,0,0,68.8,13.23Zm9.258,9.69H62.192V19.29a2.612,2.612,0,0,1,2.59-2.629,4.5,4.5,0,0,0,1.553-.333l2.846,4.114a1.153,1.153,0,0,0,.947.5h0a1.153,1.153,0,0,0,.947-.5l2.846-4.113a4.326,4.326,0,0,0,1.552.333,2.612,2.612,0,0,1,2.59,2.629Z" transform="translate(-61.127)"/>
                                            </g>
                                        </g>
                                    </svg>
                                </span>
                            </div>
                            <input required  class="form-control" name="username" type="username" placeholder="{LANG_USERNAME}">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="16" viewBox="0 0 24 16">
                                        <g transform="translate(0)">
                                            <path d="M23.983,101.792a1.3,1.3,0,0,0-1.229-1.347h0l-21.525.032a1.169,1.169,0,0,0-.869.4,1.41,1.41,0,0,0-.359.954L.017,115.1a1.408,1.408,0,0,0,.361.953,1.169,1.169,0,0,0,.868.394h0l21.525-.032A1.3,1.3,0,0,0,24,115.062Zm-2.58,0L12,108.967,2.58,101.824Zm-5.427,8.525,5.577,4.745-19.124.029,5.611-4.774a.719.719,0,0,0,.109-.946.579.579,0,0,0-.862-.12L1.245,114.4,1.23,102.44l10.422,7.9a.57.57,0,0,0,.7,0l10.4-7.934.016,11.986-6.04-5.139a.579.579,0,0,0-.862.12A.719.719,0,0,0,15.977,110.321Z" transform="translate(0 -100.445)"/>
                                        </g>
                                    </svg>
                                </span>
                            </div>
                            <input required  class="form-control" name="email" type="email" placeholder="{LANG_EMAILAD}">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">+91</span>
                            </div>
                            <input required class="form-control" name="phone" type="phone" placeholder="{LANG_PHONE_NO}">
                        </div>
                    </div>
                    <div class="form-group password-field">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="24" viewBox="0 0 16 24">
                                        <g transform="translate(0)">
                                            <g transform="translate(5.457 12.224)">
                                                <path d="M207.734,276.673a2.543,2.543,0,0,0-1.749,4.389v2.3a1.749,1.749,0,1,0,3.5,0v-2.3a2.543,2.543,0,0,0-1.749-4.389Zm.9,3.5a1.212,1.212,0,0,0-.382.877v2.31a.518.518,0,1,1-1.035,0v-2.31a1.212,1.212,0,0,0-.382-.877,1.3,1.3,0,0,1-.412-.955,1.311,1.311,0,1,1,2.622,0A1.3,1.3,0,0,1,208.633,280.17Z" transform="translate(-205.191 -276.673)"/>
                                            </g>
                                            <path d="M84.521,9.838H82.933V5.253a4.841,4.841,0,1,0-9.646,0V9.838H71.7a1.666,1.666,0,0,0-1.589,1.73v10.7A1.666,1.666,0,0,0,71.7,24H84.521a1.666,1.666,0,0,0,1.589-1.73v-10.7A1.666,1.666,0,0,0,84.521,9.838ZM74.346,5.253a3.778,3.778,0,1,1,7.528,0V9.838H74.346ZM85.051,22.27h0a.555.555,0,0,1-.53.577H71.7a.555.555,0,0,1-.53-.577v-10.7a.555.555,0,0,1,.53-.577H84.521a.555.555,0,0,1,.53.577Z" transform="translate(-70.11)"/>
                                        </g>
                                    </svg>
                                </span>
                            </div>

                            <input required  class="form-control" name="password" type="password" placeholder="{LANG_PASSWORD}">
                            <div class="input-group-append cursor-pointer">
                                <span class="input-group-text">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="16" viewBox="0 0 24 16">
                                        <g transform="translate(0 0)">
                                            <g transform="translate(0 0)">
                                                <path d="M23.609,117.568a15.656,15.656,0,0,0-5.045-4.859,12.823,12.823,0,0,0-6.38-1.811c-.062,0-.309,0-.371,0a12.823,12.823,0,0,0-6.38,1.811,15.656,15.656,0,0,0-5.045,4.859,2.464,2.464,0,0,0,0,2.658,15.656,15.656,0,0,0,5.045,4.859,12.822,12.822,0,0,0,6.38,1.811c.062,0,.309,0,.371,0a12.823,12.823,0,0,0,6.38-1.811,15.656,15.656,0,0,0,5.045-4.859A2.464,2.464,0,0,0,23.609,117.568Zm-17.74,6.489a14.622,14.622,0,0,1-4.712-4.538,1.155,1.155,0,0,1,0-1.245,14.621,14.621,0,0,1,4.712-4.538,12.747,12.747,0,0,1,1.586-.79,8.964,8.964,0,0,0,0,11.9A12.748,12.748,0,0,1,5.869,124.057ZM12,125.75c-3.213,0-5.827-3.074-5.827-6.853s2.614-6.853,5.827-6.853,5.827,3.074,5.827,6.853S15.211,125.75,12,125.75Zm10.841-6.23a14.621,14.621,0,0,1-4.712,4.538,12.737,12.737,0,0,1-1.585.788,8.964,8.964,0,0,0,0-11.9,12.74,12.74,0,0,1,1.587.791,14.622,14.622,0,0,1,4.712,4.538A1.155,1.155,0,0,1,22.839,119.52Z" transform="translate(0.002 -110.897)"/>
                                            </g>
                                            <g transform="translate(9.565 5.565)">
                                                <path d="M205.24,202.8a2.435,2.435,0,1,0,2.435,2.435A2.438,2.438,0,0,0,205.24,202.8Zm0,3.917a1.482,1.482,0,1,1,1.482-1.482A1.483,1.483,0,0,1,205.24,206.721Z" transform="translate(-202.805 -202.804)"/>
                                            </g>
                                        </g>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div><button class="btn btn-primary btn-block" id="signup" type="submit">{LANG_REGISTER}</button></div>
                    <div class="line mt-10 mb-6"><span>{LANG_OR} </span></div>
                    <div class="text-center">
                        IF("{FACEBOOK_APP_ID}"!=""){
                        <div class="d-inline-block mr-1">
                            <button class="btn btn-icon btn-flat text-facebook facebook-login" title="{LANG_LOGIN_FACEBOOK}">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     xmlns:xlink="http://www.w3.org/1999/xlink" width="32" height="32" viewBox="0 0 32 32">
                                    <defs>
                                        <style>.a{fill:currentColor;}.b{fill:#fff;fill-rule:evenodd;}</style>
                                        <linearGradient id="a" y1="0.5" x2="1" y2="0.5" gradientUnits="objectBoundingBox">
                                            <stop offset="0" stop-color="#245a9c"/>
                                            <stop offset="1" stop-color="#2b189c"/>
                                        </linearGradient>
                                    </defs>
                                    <circle class="a" cx="16" cy="16" r="16" transform="translate(0 0)"/>
                                    <path class="b" d="M179.516,108.125v9.713a.258.258,0,0,0,.261.255h3.7a.258.258,0,0,0,.261-.255v-9.87h2.679a.259.259,0,0,0,.261-.229l.254-2.972a.255.255,0,0,0-.261-.274h-2.94v-2.116a.9.9,0,0,1,.917-.895h2.063a.258.258,0,0,0,.261-.255V98.255a.258.258,0,0,0-.261-.255H183.22a3.665,3.665,0,0,0-3.71,3.619v2.868h-1.848a.258.258,0,0,0-.261.255v2.972a.258.258,0,0,0,.261.255h1.855Z" transform="translate(-166.208 -91.946)"/>
                                </svg>
                            </button>
                        </div>
                        {:IF}
                        IF("{GOOGLE_APP_ID}"!=""){
                        <div class="d-inline-block mr-1">
                            <button class="btn btn-icon btn-flat text-google gmail-login" title="{LANG_LOGIN_GOOGLE}">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     xmlns:xlink="http://www.w3.org/1999/xlink" width="32" height="32" viewBox="0 0 32 32">
                                    <defs>
                                        <style>.a{fill:currentColor;}.b{fill:#fff;}</style>
                                        <linearGradient id="a" x1="0.146" y1="0.146" x2="0.853" y2="0.853" gradientUnits="objectBoundingBox">
                                            <stop offset="0" stop-color="#ff4c3c"/>
                                            <stop offset="0.956" stop-color="#ef053e"/>
                                            <stop offset="1" stop-color="#ee023e"/>
                                        </linearGradient>
                                    </defs>
                                    <circle class="a" cx="16" cy="16" r="16" transform="translate(0 0)"/>
                                    <path class="b" d="M89.15,145.395a.77.77,0,0,0-.007.121v2.86h3.965a3.38,3.38,0,0,1-1.453,2.23,4.254,4.254,0,0,1-2.15.69,4.361,4.361,0,0,1-1.607-.187,4.46,4.46,0,0,1-3.054-3.851,4.293,4.293,0,0,1,.087-1.339,4.373,4.373,0,0,1,4.869-3.409,3.962,3.962,0,0,1,2.177,1.051l2.156-2.156a8.877,8.877,0,0,0-1.313-.964,7.028,7.028,0,0,0-3.509-.958c-.234-.007-.475.013-.71.02a5.771,5.771,0,0,0-1.219.188,7.425,7.425,0,0,0-5.612,6.757,6.9,6.9,0,0,0,.154,2,7.335,7.335,0,0,0,2.893,4.427,7.244,7.244,0,0,0,3.951,1.4,7.8,7.8,0,0,0,2.424-.241,6.432,6.432,0,0,0,3.643-2.391,7.944,7.944,0,0,0,1.272-6.242Z" transform="translate(-76.847 -130.881)"/>
                                    <path class="b" d="M330.806,193.278h-2.5V190.7h-2.023v2.578H323.7V195.3h2.578v2.585H328.3V195.3h2.585v-2.023Z" transform="translate(-302.985 -178.663)"/>
                                </svg>
                            </button>
                        </div>
                        {:IF}
                    </div>

                    <div class="text-center mt-6">
                        {LANG_ALREADY_HAVE_ACC} <a href="#" class="login-page">{LANG_LOGIN_HERE}</a>
                    </div>
                </form>
            </div>

            <!--Forgot Form-->
            <div class="forny-content hide-it text-center" id="forgot-form">
                <div class="mb-8 text-center forny-logo">
                    <a href="{SITE_URL}"><img src="{SITE_URL}storage/logo/{SITE_LOGO}" style="max-width: 150px"></a>
                </div>
                <div class="text-center">
                    <h4 class="mb-5">{LANG_FORGOTPASS}</h4>
                    <p class="mb-10" id="forgot-status">{LANG_FORGOT_PASS_NOTIFY}</p>
                </div>
                <div class="reset-form d-block">
                    <form class="reset-password-form" action="forgot_pass" name="reset_pass_form" id="reset_pass_form" method="post">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="16" viewBox="0 0 24 16">
                                            <g transform="translate(0)">
                                                <path d="M23.983,101.792a1.3,1.3,0,0,0-1.229-1.347h0l-21.525.032a1.169,1.169,0,0,0-.869.4,1.41,1.41,0,0,0-.359.954L.017,115.1a1.408,1.408,0,0,0,.361.953,1.169,1.169,0,0,0,.868.394h0l21.525-.032A1.3,1.3,0,0,0,24,115.062Zm-2.58,0L12,108.967,2.58,101.824Zm-5.427,8.525,5.577,4.745-19.124.029,5.611-4.774a.719.719,0,0,0,.109-.946.579.579,0,0,0-.862-.12L1.245,114.4,1.23,102.44l10.422,7.9a.57.57,0,0,0,.7,0l10.4-7.934.016,11.986-6.04-5.139a.579.579,0,0,0-.862.12A.719.719,0,0,0,15.977,110.321Z" transform="translate(0 -100.445)"/>
                                            </g>
                                        </svg>
                                    </span>
                                </div>
                                <input required class="form-control" name="email" type="email" placeholder="{LANG_EMAILAD}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button class="btn btn-primary btn-block" id="forgot">{LANG_REQ_PASS}</button>
                            </div>
                        </div>
                        <div class="text-center mt-6"><a href="#" class="login-page">{LANG_GO_BACK}</a></div>
                    </form>
                </div>
                <div class="reset-confirmation d-none">
                    <h4 class="mb-5">{LANG_CONFIRMATION_MAIL_SENT}</h4>
                    <div class="text-success">{LANG_CHECKEMAILFORGOT}</div>
                    <div class="text-center mt-6"><a href="#" class="login-page">{LANG_GO_BACK}</a></div>
                </div>
            </div>

            <!--Mobile OTP Form-->
            <div class="forny-content hide-it text-center" id="mobile-form">
                <div class="mb-8 text-center forny-logo">
                    <a href="{SITE_URL}"><img src="{SITE_URL}storage/logo/{SITE_LOGO}" style="max-width: 150px"></a>
                </div>
                <div class="reset-form d-block">
                    <form action="mobile_verify" name="pv-form" id="pv-form" method="post">
                        <h4 class="mb-5">{LANG_LOGIN_WITH_PHONE}</h4>
                        <p class="mb-10" id="mobile-status">
                            {LANG_OTP_NOTIFY_1}
                        </p>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">+91</span>
                                </div>
                                <input required class="form-control" id="verify-mobile" name="mobile_no" type="phone" placeholder="{LANG_PHONE_NO}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <input type="hidden" value="1" name="submit_mobile"/>
                                <button class="btn btn-primary btn-block" id="mobile-submit" type="submit">{LANG_SEND_OTP}</button>
                            </div>
                        </div>
                        <div class="text-center mt-10"><a href="#" class="login-page">{LANG_GO_BACK}</a></div>
                    </form>
                </div>
                <div class="reset-confirmation d-none">
                    <form action="otp_verify" name="otp-form" id="otp-form" method="post">
                        <h2 class="mb-5">{LANG_OTP_VERIFICATION}</h2>
                        <p class="mb-10" id="otp-status">
                            {LANG_OTP_NOTIFY_2}: <span class="otp_mobile"></span>.
                        </p>
                        <div class="otp-form-content">
                            <div class="form-group">
                                <input required class="form-control" name="otp_code" type="text" placeholder="{LANG_OTP_CODE}">
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <input type="hidden" value="" name="mobile_no" class="otp_mobile_no"/>
                                    <input type="hidden" value="1" name="submit_otp"/>
                                    <button class="btn btn-primary btn-block" id="otp-submit" type="submit">{LANG_VERIFY_OTP}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="text-center mt-10"><a href="#" class="go-back">{LANG_GO_BACK}</a></div>
                </div>
            </div>

        </div>
    </div>

</div>
<script src="{SITE_URL}includes/assets/login_ajax/js/jquery.min.js"></script>
<script src="{SITE_URL}includes/assets/login_ajax/js/bootstrap.min.js"></script>
<script src="{SITE_URL}includes/assets/login_ajax/js/main.js"></script>
<script src="{SITE_URL}includes/assets/login_ajax/js/demo.js"></script>
</body>
</html>