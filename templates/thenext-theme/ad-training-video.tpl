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
            <form class="form-validate" id="post-advertise-form" action="{LINK_POST-TRAINING-VIDEO}/{ITEM_ID}?action=post_training_video" method="post" enctype="multipart/form-data" accept-charset="UTF-8">
                <div class="dashboard-box margin-top-0">
                    <div class="content with-padding padding-bottom-10">
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="submit-field" id="training_upload_container">
                                    <h6 style="color:green">{SUCCESS}</h6>
                                    <h6 style="color:red">{CUSTOMERROR}</h6>
                                    <h5>Upload training video / image *</h5>
                                    <div id="input-container">
                                        <label for="video_file">Select a video file (MP4, AVI, MOV, MKV only):</label>
                                        <label for="max_size">Maximum file size (500 MB):</label>
                                        <div class="file-input-container">
                                            <input type="hidden" name="productId" id="productId" value="{ITEM_ID}">
                                            <input type="hidden" name="max_size" id="max_size" min="1" value="500" required>
                                            <input type="file" name="trainingVideo" id="trainingVideo" class="training-file-input" accept="video/mp4,video/avi,video/mov,video/x-matroska" required>
                                        </div>
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
        {LOOP: TRAINING_VIDEO}
                <div class="col-xl-12 col-lg-12 content-right-offset">
                    <div class="parallel-div">
                        <!-- Use the video element to embed a video -->
                            <video width="340" height="260" controls>
                                IF("{TRAINING_VIDEO.training_video}"!=""){
                                    <source src="{SITE_URL}/storage/training_video/{TRAINING_VIDEO.training_video}" type="video/mp4">
                                {:IF}
                            </video>    
                    </div>
                    <div class="parallel-div">
                        <button type="submit" onclick="removeTrainingVideo({TRAINING_VIDEO.id},{TRAINING_VIDEO.product_id})" id="submit_job_button" name="Submit" class="button ripple-effect big">Remove Video</button>
                    </div>
                </div>
            {/LOOP: TRAINING_VIDEO} 
    </div>
</div>
<script src='https://www.google.com/recaptcha/api.js'></script>
<script type='text/javascript' src='{SITE_URL}templates/{TPL_NAME}/js/slick.min.js'></script>
<!-- Start jQuery starReviews -->
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.1.34/jquery.form-validator.min.js"></script>
<link href="{SITE_URL}plugins/starreviews/assets/css/starReviews.css" rel="stylesheet" type="text/css"/>
<script src="{SITE_URL}plugins/starreviews/assets/js/jquery.barrating.js"></script>
<script src="{SITE_URL}plugins/starreviews/assets/js/starReviews.js"></script>
<style>
    .starReviews hr { margin: 22px 0;}
    .starReviews h2, .starReviews h3 { margin-bottom: 10px;}
    .starReviews { text-align: left;}
    .starReviews label { font-size: 14px;}
</style>
<script type="text/javascript">
    $(document).ready(function () {
        $().reviews('.starReviews');
    });
    IF("{ERROR}"!=""){
        $(window).on('load',function () {
            $('.apply-dialog-button').trigger('click');
        });
    {:IF}

    //binds to onchange event of your input field
    $('#trainingVideo').bind('change', function() {
        //this.files[0].size gets the size of your file.
        if (this.files[0].size >= 524288000) {
            // File size is within the allowed limit
            alert('File size exceeds the maximum limit (500 MB)');
            window.location.reload();
        }
    });

    function removeTrainingVideo(videoId, productId){
        if(videoId != '' && productId != ''){
            $.ajax({											//the main ajax request
                type: "POST",
                data: "action=removeTrainingVideo&videoId="+videoId+"&productId="+productId,
                url: ajaxurl,
                success: function(data)
                {
                    if(data=='success'){
                        alert('Video successfully removed from gallery');
                        window.location.reload();
                    } 
                }
            });
        }
    }
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

