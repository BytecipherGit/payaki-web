{OVERALL_HEADER}
<div class="container">
   <div class="row">
      <div class="col-lg-12 col-md-12">
         <div class="dashboard-box margin-top-0" style="margin-top: 39px !important;">
            <!-- Headline -->
            <div class="headline">
               <h3>Checkout Details</h3>
            </div>
            <div class="content with-padding">
               <div class="table-responsive">
                  <form method="post" name="checkout-form" id="checkout-form" action="#" accept-charset="UTF-8" enctype="multipart/form-data">
                     <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6">
                           <div class="form-group">
                              <input type="text" name="name" id="name" class="form-control input-sm" placeholder="Full Name">
                           </div>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                           <div class="form-group">
                              <input type="email" name="email" id="email" class="form-control input-sm" placeholder="Email Address">
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6">
                           <div class="form-group">
                              <input type="text" name="country" id="country" class="form-control input-sm" placeholder="Country">
                           </div>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                           <div class="form-group">
                              <input type="text" name="state" id="state" class="form-control input-sm" placeholder="State">
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6">
                           <div class="form-group">
                              <input type="text" name="city" id="city" class="form-control input-sm" placeholder="City">
                           </div>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                           <div class="form-group">
                              <input type="text" name="pincode" id="pincode" class="form-control input-sm" placeholder="Pincode">
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6">
                           <div class="form-group">
                              <input type="text" name="address" id="address" class="form-control input-sm" placeholder="Full Address">
                           </div>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                           <div class="form-group">
                              <input type="text" name="mobile" id="mobile" class="form-control input-sm" placeholder="Contact Mobile">
                           </div>
                        </div>
                     </div>
                     <input type="hidden" name="amount" id="amount" value="{TOTAL}">
                     <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6">
                           <div class="form-group">
                           </div>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                           <div class="form-group">
                              <button class="button full-width button-sliding-icon ripple-effect margin-top-10" name="submit" type="submit">Checkout</button>
                           </div>
                        </div>
                     </div>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<div class="margin-top-80"></div>
{OVERALL_FOOTER}
