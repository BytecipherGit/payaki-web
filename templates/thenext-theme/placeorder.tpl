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
                        <div class="col-md-12">
                            <div class="form-group" id="success" style="text-align:center; display:none;">
                                <h4 style="color:green;">Transaction successfully done.</h4>
                            </div>
                        </div>
                     </div>
                        <div class="row">
                        <div class="col-md-4">
                        </div>
                        <div class="col-md-4">
                        <div class="form-group" style="text-align:center">
                                   <img src="{SITE_URL}storage/logo/multicaixa_express.jpeg" style="width:120px; height: 120px;">
                        </div>
                        <div class="form-group" style="text-align:center">
                                   <h2>Multicaixa Express</h2>
                        </div>
                        
                            <form id="payment_form" class="form-horizontal" method="post" enctype="multipart/form-data" action="{CUSTOMPAYMENT}">
                                <div class="form-group" style="text-align:center">
                                    <div class="col-sm-12">
                                        <input type="text" class="form-control" min="9" placeholder="Enter Mobile Number" name="mobile" id="mobile"/>
                                        <span id="mobile_msg" class="text-danger" style="display:none;">Enter mobile number</span>
                                    </div>
                                </div>
                                <div class="form-group" style="text-align:center">
                                    <div class="col-sm-12"> 
                                        <label><strong>Amount paid :-</strong> Kz {TOTALAMOUNT}.00</label> 
                                    </div>
                                </div>

                                <div class="form-group" style="text-align:center">
                                    <div class="col-sm-12" >
                                        <span class="button set-checkout-item-cart" data-type="{TYPE}" data-amount="{TOTALAMOUNT}" data-item-id="{ITEM}" data-userid="{USER_ID}" data-action="setCheckoutCartItem">Proceed to payment</span>
                                        <!--<input class="btn btn-primary right" style="float:right;" type="submit" name="proceedPayment" value="Proceed to payment"/>-->
                                    </div>
                                </div>
                            </form>
                            <div id="displayTimeForLoader" style="display:none">
                                
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin:auto;background:#fff;display:block;" width="60px" height="60px" viewBox="-50 -50 100 100" preserveAspectRatio="xMidYMid">
<clipPath id="cp">
  <path d="M0 -40.5 A40.5 40.5 0 0 1 0 40.5 A40.5 40.5 0 0 1 0 -40.5 M23.5 -1L23.5 1L30.5 1L30.5 -1Z"></path>
</clipPath>
<g transform="translate(0,0)">
<circle clip-path="url(#cp)" cx="0" cy="0" fill="none" r="26" stroke="#abbd81" stroke-width="5" stroke-dasharray="40.840704496667314 0 0 0 0 163.36281798666926">
<animate attributeName="stroke-dasharray" dur="1s" repeatCount="indefinite" begin="-0.1s" keyTimes="0;0.2;0.4;0.6;0.8;1" values="
0 0 0 0 0 163.36281798666926;
0 0 0 0 0 163.36281798666926;
0 0 81.68140899333463 0 0 163.36281798666926;
0 0 163.36281798666926 0 0 163.36281798666926;
0 0 81.68140899333463 0 0 163.36281798666926;
0 0 0 0 0 163.36281798666926
"></animate>
<animateTransform attributeName="transform" type="rotate" dur="1s" repeatCount="indefinite" begin="-0.1s" values="0;0;0;0;180;360"></animateTransform>
</circle>

<circle cx="0" cy="0" fill="none" r="32" stroke="#f8b26a" stroke-width="5" stroke-dasharray="100.53096491487338 0 0 201.06192982974676">
<animate attributeName="stroke-dasharray" dur="1s" repeatCount="indefinite" begin="0s" values="
0 0 0 0 0 201.06192982974676;
0 0 100.53096491487338 0 0 201.06192982974676;
0 0 100.53096491487338 0 0 201.06192982974676;
0 0 100.53096491487338 0 0 201.06192982974676;
0 0 100.53096491487338 0 0 201.06192982974676;
0 0 0 0 0 201.06192982974676
"></animate>
<animateTransform attributeName="transform" type="rotate" dur="1s" repeatCount="indefinite" begin="0s" values="0;0;0;180;180;360"></animateTransform>
</circle>

<circle cx="0" cy="0" fill="none" r="38" stroke="#e15b64" stroke-width="5" stroke-dasharray="119.38052083641213 0 0 238.76104167282426" transform="rotate(45)">
<animate attributeName="stroke-dasharray" dur="1s" repeatCount="indefinite" begin="0s" keyTimes="0;0.06;0.1;0.3;0.45;0.5;0.7;0.90;1" values="
0 0 89.5353906273091 0 0 238.76104167282426;
0 0 89.5353906273091 0 0 238.76104167282426;
0 0 119.38052083641213 0 0 238.76104167282426;
0 0 119.38052083641213 0 0 238.76104167282426;
0 0 29.845130209103033 0 0 238.76104167282426;
0 0 29.845130209103033 0 0 238.76104167282426;
0 0 119.38052083641213 0 0 238.76104167282426;
0 0 119.38052083641213 0 0 238.76104167282426;
0 0 89.5353906273091 0 0 238.76104167282426
"></animate>
<animateTransform attributeName="transform" type="rotate" dur="1s" repeatCount="indefinite" begin="0s" keyTimes="0;0.06;0.1;0.3;0.5;0.6;0.8;0.90;1" values="-60;0;0;0;180;180;180;180;300"></animateTransform>
</circle>
</g>
</svg>

                                    <span>Wait for authorized payment:</span>
                                    <div id="timer">Loading...</div>
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
