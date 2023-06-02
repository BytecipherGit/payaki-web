{OVERALL_HEADER}
<div class="container">
   <div class="row">
      <div class="col-lg-12 col-md-12">
         <div class="dashboard-box margin-top-0" style="margin-top: 39px !important;">
            <!-- Headline -->
            <div class="headline">
               <h3>Cart Details</h3>
            </div>
            <div class="content with-padding">
               <div class="table-responsive">
                  <table id="js-table-list" class="basic-table dashboard-box-list">
                     <tbody>
                        <tr>
                           <th>Product Image</th>
                           <th>Product Name</th>
                           <th>Product Quantity</th>
                           <th>Expiry Price</th>
                        </tr>
                         {LOOP: CART}
                         <tr style="background-color: #FFFFFF;">
                           <td><img class="cart-image" src="{SITE_URL}storage/products/thumb/{CART.product_image}" alt="Product Image"></td>
                           <td>
                              {CART.product_name}                                  
                           </td>
                           <td>{CART.quantity}</td>
                           <td>{CART.price}</td>
                        </tr>
                         {/LOOP: CART}
                         <tr>
                           <td align="right" colspan="3">Total</td>
                           <td>
                           {TOTAL}
                           </td>
                        </tr>
                        <tr>
                           <td align="right" colspan="4">
                           <a href="{LINK_CHECKOUT}" class="button btn btn-primary">Checkout</a>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
            </div>
            
         </div>
      </div>
   </div>
</div>
<div class="margin-top-80"></div>
{OVERALL_FOOTER}
