{OVERALL_HEADER}
<div id="titlebar" class="margin-bottom-0">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>Book Seat For Event</h2>
                <!-- Breadcrumbs -->
                <nav id="breadcrumbs">
                    <ul>
                        <li><a href="{LINK_INDEX}">{LANG_HOME}</a></li>
                        <li>Book Seat For Event</li>
                    </ul>
                </nav>

            </div>
        </div>
    </div>
</div>
<div class="section gray padding-bottom-50">
    <div class="container">
        <div class="row">
            
            <div class="col-lg-12 col-md-12">
                <div class="dashboard-box margin-top-0">
                    <!-- Headline -->
                    <div class="headline">
                        <h3>Shipping Details </h3>
                    </div>
                    <div class="content with-padding">
                        <div class="row">
                        <div class="col-md-2">
                        </div>
                        <div class="col-md-8">
                            <form class="form-horizontal" method="post" enctype="multipart/form-data" action="{CUSTOMPAYMENT}">
                                <div class="form-group">
                                    <div class="col-sm-12"> 
                                        <input type="text" class="form-control" placeholder="Full Name" name="name" value="{NAME}" required />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12"> 
                                        <textarea class="form-control" rows="5" placeholder="Address" name="address" required >{ADDRESS}</textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <input type="number" class="form-control" min="9" placeholder="Contact number" name="contactNumber" value="{PHONE}" required />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12"> 
                                        <input type="email" class="form-control" placeholder="Email" name="emailAddress" value="{EMAIL}" required />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12" >
                                        <input class="btn btn-primary right" style="float:right;" type="submit" name="proceedPayment" value="Proceed to payment"/>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-2">
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{OVERALL_FOOTER}
