{OVERALL_HEADER}
<div id="titlebar" class="margin-bottom-0">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>Place order</h2>
                <!-- Breadcrumbs -->
                <nav id="breadcrumbs">
                    <ul>
                        <li><a href="{LINK_INDEX}">{LANG_HOME}</a></li>
                        <li>Place order</li>
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
                    <!--<div class="headline">
                        <h3>Shipping Details </h3>
                    </div>-->
                    <div class="content with-padding">
                        <div class="row">
                        <div class="col-md-4">
                        </div>
                        <div class="col-md-4">
                            <form class="form-horizontal" method="post" enctype="multipart/form-data" action="{CUSTOMPAYMENT}">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <input type="text" class="form-control" min="9" placeholder="Enter Mobile Number" name="mobile" id="mobile"/>
                                        <span id="mobile_msg" class="text-danger" style="display:none;">Enter mobile number</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12"> 
                                        <label><strong>Amount paid :-</strong> Kz {TOTALAMOUNT}.00</label> 
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-sm-12" >
                                        <span class="button set-checkout-item-cart" data-type="{TYPE}" data-amount="{TOTALAMOUNT}" data-item-id="{ITEM}" data-userid="{USER_ID}" data-action="setCheckoutCartItem">Proceed to payment</span>
                                        <!--<input class="btn btn-primary right" style="float:right;" type="submit" name="proceedPayment" value="Proceed to payment"/>-->
                                    </div>
                                </div>
                            </form>
                            <div id="displayTimeForLoader" style="display:none">
                                <span class="button">Wait for authorized payment:</span>
                                    <div id="timer">Loading...</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                        
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{OVERALL_FOOTER}
