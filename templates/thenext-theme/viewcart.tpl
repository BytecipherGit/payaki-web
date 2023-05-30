{OVERALL_HEADER}
<div class="container">
   <div class="row">
      <div class="col-lg-12 col-md-12">
         <div class="dashboard-box margin-top-0">
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
                        <tr>
                           <td><img src="{CART.product_image}" alt="Product Image"></td>
                           <td>
                              {CART.product_name}                                  
                           </td>
                           <td>{CART.quantity}</td>
                           <td>{CART.price}</td>
                        </tr>
                         {/LOOP: CART}
                        <tr>
                           <td align="right" colspan="7"><button type="button" class="button" onclick="window.location.href='http://localhost/payaki-web/membership/changeplan'">Change Plan</button></td>
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
