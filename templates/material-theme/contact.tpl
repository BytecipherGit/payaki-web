{OVERALL_HEADER}


    <div id="page-content">
        <div class="container">
            <ol class="breadcrumb">
                <li><a href="{LINK_INDEX}">{LANG_HOME}</a></li>
                <li class="active">{LANG_CONTACT_US}</li>
            </ol>
            <section class="page-title">
                <h1 class="pull-left">{LANG_CONTACT_US}</h1>
                <div class="pull-right featured-contact">
                    <i class="icon_comment_alt"></i>
                    <h4>24/7 {LANG_SUPPORT}</h4>
                    <h3>{PHONE}</h3>
                </div>
            </section>
            <!--end section-title-->
        </div>
        <!--end container-->
        <section>
            <div class="map height-400px" id="singleListingMap" data-latitude="{LATITUDE}" data-longitude="{LONGITUDE}" data-map-icon="fa fa-marker" style="height: 400px"></div>
            <!--end map-->
        </section>
        <section class="block">
            <div class="container">
                <div class="row">
                    <div class="col-md-3 col-sm-3">
                        <h3>{LANG_CONTACT_INFORMATION}</h3>
                        <div class="box">
                            <address>
                                <strong>{LANG_LOCATION}</strong>
                                <figure>{ADDRESS}</figure>
                                <br>
                                <strong>{LANG_PHONE-NO}</strong>
                                <figure><a href="tel:{PHONE}">{PHONE}</a></figure>
                                <br>
                                <strong>{LANG_EMAIL}</strong>
                                <figure><a href="#">{EMAIL}</a></figure>
                            </address>
                        </div>
                    </div>
                    <!--end col-md-3-->
                    <div class="col-md-9 col-sm-9">
                        <h3>{LANG_ENQUIRY_FORM}</h3>
                        <form method="post">
                            <div class="row">
                                <div class="col-md-4 col-sm-4">
                                    <div class="input-field">
                                        <label for="name">{LANG_NAME}</label>
                                        <input type="text" name="name" id="name" required="">
                                    </div>
                                    <!--end input-field-->
                                </div>
                                <!--end col-md-4-->
                                <div class="col-md-4 col-sm-4">
                                    <div class="input-field">
                                        <label for="email">{LANG_EMAIL}</label>
                                        <input type="email" name="email" id="email" required="">
                                    </div>
                                    <!--end input-field-->
                                </div>
                                <!--end col-md-4-->
                                <div class="col-md-4 col-sm-4">
                                    <div class="input-field">
                                        <label for="subject">{LANG_SUBJECT}</label>
                                        <input type="text" name="subject" id="subject" required="">
                                    </div>
                                    <!--end input-field-->
                                </div>
                                <!--end col-md-4-->
                                <div class="col-md-12 col-sm-12">
                                    <div class="input-field">
                                        <label for="message">{LANG_MESSAGE}</label>
                                        <textarea class="materialize-textarea" id="message" rows="4" name="message" required=""></textarea>
                                    </div>
                                </div>
                                <div class="col-md-12 col-sm-12">
                                    <button type="submit" name="Submit" class="btn btn-primary icon shadow">{LANG_SEND_MESSAGE}<i class="fa fa-caret-right"></i></button>
                                </div>
                            </div>
                            <!--end row-->



                            <!--end input-field-->
                        </form>
                    </div>
                    <!--end col-md-9-->
                </div>
                <!--end row-->
            </div>
            <!--end container-->
        </section>
    </div>
    <!--end page-content-->


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
    var path = '{SITE_URL}templates/{TPL_NAME}/';
    var getCity = false;
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
{:IF}

{OVERALL_FOOTER}