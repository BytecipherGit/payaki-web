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
                        <h3>Booking Details </h3>
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
                                    <div class="col-sm-12"> 
                                        <input type="email" class="form-control" placeholder="Email" name="emailAddress" value="{EMAIL}" required />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12"> 
                                        <table class="table">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th style="width:10%;">Ticket</th>
                                                    <th style="width:10%;">Price</th>
                                                    <th style="width:10%;">Mode</th>
                                                    <th style="width:10%;">Seat Avl.</th>
                                                    <th style="width:10%;">Booking Qty.</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {LOOP: EVENT_TICKET}
                                                    <tr>
                                                        <td>{EVENT_TICKET.ticket_type}</td>
                                                        <td>{EVENT_TICKET.ticket_price}</td>
                                                        <td>{EVENT_TICKET.selling_mode}</td>
                                                        <td>{EVENT_TICKET.available_quantity}</td>
                                                        <td>
                                                            <input type="hidden" id="ticketId" name="ticketId[]" value="{EVENT_TICKET.id}" >
                                                            <input type="hidden" id="productId" name="productId[]" value="{EVENT_TICKET.product_id}" >
                                                            <input type="hidden" id="price" name="price[]" min="1" max="{EVENT_TICKET.ticket_price}">
                                                            <input type="number" id="quantity" name="quantity[]" min="1" max="{EVENT_TICKET.available_quantity}">
                                                        </td>
                                                    </tr>
                                                {/LOOP: EVENT_TICKET}
                                            </tbody>
                                        </table>
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
