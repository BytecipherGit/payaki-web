$(document).ready(function(){
	// add item to cart
	$(".add_2_cart").click(function (e) {
		var productId = this.getAttribute("data-id");
		var qty = this.getAttribute("data-qty");
      	if(productId != ''){
			$.ajax({
				url: ajaxurl,
				type: "POST",
				dataType:"json",
				data: {
					product_id: productId,
					product_qty: qty,
					action: 'ajaxaddtocart',
					is_ajax: 1
				  },
			}).done(function(data){	
				if(data.status == true){
					location.reload();
				}
			})
		}
		e.preventDefault();
	});
	
	// update product quantity in cart
    $(".quantity").change(function() {		
		 var element = this;
		 setTimeout(function () { update_quantity.call(element) }, 2000);	
	});	
	function update_quantity() {
		var pcode = $(this).attr("data-code");
		var quantity = $(this).val(); 
		$(this).parent().parent().fadeOut(); 
		$.getJSON( "manage_cart.php", {"update_quantity":pcode, "quantity":quantity} , function(data){		
			window.location.reload();			
		});
	}	
	//Remove items from cart
	$("#shopping-cart-results").on('click', 'a.remove-item', function(e) {
		e.preventDefault(); 
		var pcode = $(this).attr("data-code"); 
		$(this).parent().parent().fadeOut();
		$.getJSON( "manage_cart.php", {"remove_code":pcode} , function(data){
			$("#cart-container").html(data.products); 	
			window.location.reload();			
		});
	});
});