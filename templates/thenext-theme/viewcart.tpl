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
                                    <th class="big-width">Product Name</th>
                                    <th class="small-width">Price</th>
                                    <th class="small-width">Qty</th>
                                </tr>
                                IF({TOTALITEM}){
                                {LOOP: ITEM}
                                    <tr>
                                        <td>{ITEM.product_name}</td>
                                        <td>{ITEM.product_price}</td>
                                        <td>{ITEM.product_qty}</td>
                                    </tr>
                                {/LOOP: ITEM}
                                <tr>
                                    <td colspan="2">Total Amount : $ {TOTALAMOUNT}</td>
                                    <td><a href="{CHECKOUT}" class="button">Checkout</a></td>
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
        </div>
    </div>
</div>
{OVERALL_FOOTER}
