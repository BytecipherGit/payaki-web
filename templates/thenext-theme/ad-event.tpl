{OVERALL_HEADER}
<style>
    .parallel-div {
        display: inline-block;
        width: 45%; /* Adjust the width as needed */
        margin-right: 2%; /* Add a small margin between the divs */
        vertical-align: top; /* Align the divs to the top */
    }

    /* Optional: Apply styles to make the divs visible */
    .parallel-div {
        padding: 10px;
    }

        .label {
            margin-bottom: 5px;
            color:#000000;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .add-button {
            background-color: #00cc00;
            height: 30px;
            margin-top: 35px;
        }

        .remove-button {
            background-color: #ff3333;
            height: 30px;
            margin-top: 35px;
        }
    
    </style>
<div id="titlebar" class="margin-bottom-0">
    <div class="container">
        <div class="row">
            <div class="col-md-7 col-sm-12">
                <h2>{ITEM_TITLE}</h2>
                <!-- Breadcrumbs -->
                <nav id="breadcrumbs">
                    <ul>
                        <li><a href="{LINK_INDEX}">{LANG_HOME}</a></li>
                        <li><a href="{ITEM_CATLINK}">{ITEM_CATEGORY}</a></li>
                        <li><a href="{ITEM_SUBCATLINK}">{ITEM_SUB_CATEGORY}</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>
<div class="container margin-top-50">
    <div class="row">
        <!-- Content -->
        <div class="col-xl-12 col-lg-12 content-right-offset">
            <form class="form-validate" id="post-advertise-form" action="{LINK_POST-EVENT}/{ITEM_ID}?action=post_event" method="post" enctype="multipart/form-data" accept-charset="UTF-8">
                <div class="dashboard-box margin-top-0">
                    <div class="content with-padding padding-bottom-10">
                        <div class="row">
                            <div class="col-xl-12">
                                <h5 style="color:green">{SUCCESS}</h5>
                                <h5 style="color:red">{CUSTOMERROR}</h5>
                                <div class="submit-field" id="event_container">
                                    <h5>Event Ticket Selling Details</h5>
                                    <div id="container">
                                    <div class="container">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="column">
                                                        <label class="label">Ticket Type:</label>
                                                        <input type="text" name="ticket_type[]">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="column">
                                                        <label class="label">Ticket Price:</label>
                                                        <input type="text" name="ticket_price[]" >
                                                    </div>
                                                </div>
                                                <div class="col-md-3">    
                                                    <div class="column">
                                                        <label class="label">Ticket Quantity:</label>
                                                        <input type="text" name="available_quantity[]">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">    
                                                    <div class="column">
                                                        <label class="label">Selling Mode:</label>
                                                        <select name="selling_mode[]">
                                                            <option value="offline">Offline</option>
                                                            <option value="online">Online</option>
                                                        </select>
                                                    </div>
                                                </div>    
                                                <div class="col-md-1">
                                                    <button type="button" class="add-button" onclick="addFields()">+</button>    
                                                </div>
                                            </div>
                                        </div>
                                    {LOOP: EVENTS}
                                        <input type="hidden" name="id[]" value="{EVENTS.id}">
                                        <input type="hidden" name="product_id[]" value="{EVENTS.product_id}">
                                        <div class="container">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="column">
                                                        <label class="label">Ticket Type:</label>
                                                        <input type="text" name="ticket_type[]" value="{EVENTS.ticket_type}">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="column">
                                                        <label class="label">Ticket Price:</label>
                                                        <input type="text" name="ticket_price[]" value="{EVENTS.ticket_price}">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">    
                                                    <div class="column">
                                                        <label class="label">Ticket Quantity:</label>
                                                        <input type="text" name="available_quantity[]" value="{EVENTS.available_quantity}">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">    
                                                    <div class="column">
                                                        <label class="label">Selling Mode:</label>
                                                        <select name="selling_mode[]">
                                                            <option value="offline" IF("{EVENTS.selling_mode}"=="offline"){ selected="selected"{:IF}>Offline</option>
                                                            <option value="online" IF("{EVENTS.selling_mode}"=="offline"){ selected="selected"{:IF}>Online</option>
                                                        </select>
                                                    </div>
                                                </div>    
                                                <div class="col-md-1">
                                                    <!--<button type="button" class="remove-button" onclick="removeFields(this.parentNode)">-</button>-->
                                                </div>
                                            </div>
                                        </div>
                                    {/LOOP: EVENTS}
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>      
                <input type="hidden" name="submit">
                <div class="row margin-top-30 margin-bottom-80" style="align-items: center; float:right;">
                    <div class="col-12">
                        <button type="submit" id="submit_job_button" name="Submit" class="button ripple-effect big">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src='https://www.google.com/recaptcha/api.js'></script>
<script type='text/javascript' src='{SITE_URL}templates/{TPL_NAME}/js/slick.min.js'></script>
<!-- Start jQuery starReviews -->
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.1.34/jquery.form-validator.min.js"></script>
<link href="{SITE_URL}plugins/starreviews/assets/css/starReviews.css" rel="stylesheet" type="text/css"/>
<script src="{SITE_URL}plugins/starreviews/assets/js/jquery.barrating.js"></script>
<script src="{SITE_URL}plugins/starreviews/assets/js/starReviews.js"></script>

<script type="text/javascript">
    $(document).ready(function () {
        $().reviews('.starReviews');
    });
    IF("{ERROR}"!=""){
        $(window).on('load',function () {
            $('.apply-dialog-button').trigger('click');
        });
    {:IF}
</script>
<!-- END jQuery starReviews -->

IF("{MAP_TYPE}"=="google"){
<link href="{SITE_URL}includes/assets/plugins/map/google/map-marker.css" type="text/css" rel="stylesheet">
<script type='text/javascript' src='{SITE_URL}includes/assets/plugins/map/google/jquery-migrate-1.2.1.min.js'></script>
<script type='text/javascript' src='//maps.google.com/maps/api/js?key={GMAP_API_KEY}&#038;libraries=places%2Cgeometry&#038;ver=2.2.1'></script>
<script type='text/javascript' src='{SITE_URL}includes/assets/plugins/map/google/richmarker-compiled.js'></script>
<script type='text/javascript' src='{SITE_URL}includes/assets/plugins/map/google/markerclusterer_packed.js'></script>
<script type='text/javascript' src='{SITE_URL}includes/assets/plugins/map/google/gmapAdBox.js'></script>
<script type='text/javascript' src='{SITE_URL}includes/assets/plugins/map/google/maps.js'></script>
<script>
    var _latitude = '{ITEM_LAT}';
    var _longitude = '{ITEM_LONG}';
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

    function addFields() {
    var container = document.getElementById("container");
    var newSet = document.createElement("div");
    newSet.classList.add("container");
    newSet.innerHTML = `
    <div class="row">
        <div class="col-md-3">
            <div class="column">
                <label class="label">Ticket Type:</label>
                <input type="text" name="ticket_type[]">
            </div>
        </div>
        <div class="col-md-3">
            <div class="column">
                <label class="label">Ticket Price:</label>
                <input type="text" name="ticket_price[]">
            </div>
        </div>
        <div class="col-md-3">
            <div class="column">
                <label class="label">Ticket Quantity:</label>
                <input type="text" name="available_quantity[]">
            </div>
        </div>
        <div class="col-md-2">
            <div class="column">
                <label class="label">Selling Mode:</label>
                <select name="selling_mode[]">
                    <option value="offline">Offline</option>
                    <option value="online">Online</option>
                </select>
            </div>
        </div>
       
        <div class="col-md-1">
            <button type="button" class="remove-button" onclick="removeFields(this.parentNode)">-</button>
        </div>
        </div>
         
        
    `;

    container.appendChild(newSet);
}

function removeFields(button) {
    var container = document.getElementById("container");
    container.removeChild(button.parentNode.parentNode);
}
    
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

