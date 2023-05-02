{OVERALL_HEADER}
<div id="page-content">
    <div class="container">
        <ol class="breadcrumb">
            <li><a href="{LINK_INDEX}">{LANG_HOME}</a></li>
            <li class="active">{LANG_COUNTRIES}</li>
        </ol>
        <section class="page-title">
            <h1>{LANG_COUNTRIES}</h1>
        </section>


        <section>
            <div class="row">{LOOP: COUNTRYLIST}{COUNTRYLIST.tpl}{/LOOP: COUNTRYLIST}</div>
        </section>
    </div>

<script>
    $('#getCountry').on('click','ul li a', function(e) {
        e.stopPropagation();
        e.preventDefault();

        localStorage.Quick_placeText = "";
        localStorage.Quick_PlaceId = "";
        localStorage.Quick_PlaceType = "";
        var url = $(this).attr('href');
        window.location.href = url;
    });
</script>
{OVERALL_FOOTER}