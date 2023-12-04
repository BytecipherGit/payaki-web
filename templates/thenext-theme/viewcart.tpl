{OVERALL_HEADER}
<div id="titlebar" class="margin-bottom-0">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>View Cart</h2>
                <!-- Breadcrumbs -->
                <nav id="breadcrumbs">
                    <ul>
                        <li><a href="{LINK_INDEX}">{LANG_HOME}</a></li>
                        <li>View Cart</li>
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
                        <h3><i class="icon-feather-briefcase"></i> My Cart Details </h3>
                    </div>
                    <div class="content with-padding">
                        <div class="table-responsive">
                            <table id="js-table-list" class="basic-table dashboard-box-list">
                                <tr>
                                    <th class="small-width" style="width:69% !important;">Product Name</th>
                                    <th class="small-width">Price</th>
                                    <th class="small-width">Qty</th>
                                </tr>
                                IF({TOTALITEM}){
                                {LOOP: ITEM}
                                    <tr>
                                        <td>{ITEM.product_name}</td>
                                        <td>{ITEM.display_price}</td>
                                        <td>{ITEM.product_qty}</td>
                                    </tr>
                                {/LOOP: ITEM}
                                <tr>
                                    <td colspan="2">Total Amount : {TOTALAMOUNT}</td>
                                    <td>
                                    <!--<a href="{CHECKOUT}" class="button">Checkout</a>-->
                                    <a href="#" class="btn button btn-default btn-rounded mb-4" data-toggle="modal" data-target="#modalCheckoutForm">
                                   Checkout</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3"><a href="{SITE_URL}" class="button">Countinue Shopping</a></td>
                                </tr>
                                {ELSE}
                                <tr>
                                    <td colspan="3" class="text-center">{LANG_NO_FOUND}</td>
                                </tr>
                                {:IF}


                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="modalCheckoutForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                    <div class="modal-header text-center">
                        <h4 class="modal-title w-100 font-weight-bold">Proceed To Pay</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body mx-3">
                        <div class="md-form mb-5">
                        <label data-error="wrong" data-success="right" for="defaultForm-email">Enter mobile number</label>
                        <input type="text" name="mobile_number" class="form-control" id="mobile_number" placeholder="Enter mobile number" />
                        </div>

                        <div class="md-form mb-4">
                        <label data-error="wrong" data-success="right" for="defaultForm-pass">Amount paid :- </label> {TOTALAMOUNT}
                        <input type="hidden" value="{TOTALAMOUNT}" name="paid_amount" class="form-control" id="paid_amount"/>
                        </div>

                    </div>
                    <div class="modal-footer justify-content-right">
                        <button class="btn button" style="float: right !important;" >Proceed</button>
                    </div>
                    </div>
                </div>
                </div>

        </div>
    </div>
</div>
{OVERALL_FOOTER}
