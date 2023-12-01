{OVERALL_HEADER}
<div id="titlebar" class="margin-bottom-0">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>Proceed to payment</h2>
                <!-- Breadcrumbs -->
                <nav id="breadcrumbs">
                    <ul>
                        <li><a href="{LINK_INDEX}">{LANG_HOME}</a></li>
                        <li>Proceed to payment</li>
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
                        <div class="col-md-4">
                        </div>
                        
                            <div class="col-md-6">
                            <div class="text-left">	
                            <h4>Payable Amount : {DISPLAYTOTALAMOUNTPAYBLE}	</h4>
                            <strong>Shipping Address:</strong> <br>
                            <h5>Full Name: {NAME}	</h5>
                            <h5>Address : {ADDRESS}	</h5>
                            <h5>Contact Number : {PHONE}	</h5>
                            <h5>Contact Email : {EMAIL}	</h5>
                            
                            <form class="form-horizontal" action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="POST">
                                <input type='hidden' name='business' value='tiwarilalit601@gmail.com'>
                                <input type='hidden' name='item_name' value='{CARTITEM}'> 
                                <input type='hidden' name='item_number' value="{ORDERID}">
                                <input type='hidden' name='amount' value='{TOTALAMOUNTPAYBLE}'> 
                                <input type='hidden' name='currency_code' value='USD'> 
                                <input type='hidden' name='notify_url' value='{SITE_URL}customnotify'>
                                <input type='hidden' name='return' value='{SITE_URL}customsuccess'>
                                <input type="hidden" name="cmd" value="_xclick"> 
                                <input type="hidden" name="order" value="1">
                                <br>
                                <div class="form-group">
                                    <div class="col-sm-6"> 
                                        <input type="submit" class="btn btn-lg btn-block btn-danger" name="continue_payment" value="Pay Now">				 
                                    </div>
                                </div>
                            </form>
	                        </div>
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
