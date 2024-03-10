<?php

if(isset($_GET['id']))
{
	  $id = isset($_GET['id']) ? $_GET['id']: 0;

	  $attraction_settings = get_option('attraction_api_option');
	$attraction_api_url = rtrim($attraction_settings['attraction_api_url'],"/"); 
	$attraction_api_user = $attraction_settings['attraction_api_username']; 
	$attraction_api_pass = $attraction_settings['attraction_api_password'];
	
	  $apiUrl = 'https://phx.dosomethingdifferent.com/api/orders/'.$id;

	  $order_response = wp_remote_get($apiUrl, array(
				  'timeout'     => 120,
				  'headers' => array(
						'Authorization' => 'Basic ' . base64_encode( $attraction_api_user . ':' . $attraction_api_pass ),
					),
				)
  );

	  $response_details = wp_remote_retrieve_body( $order_response );
	  $order_result = json_decode($response_details, true);

	  $booking_message = 'A new attraction booking was made on GiftCards4Travel.' . "\r\n";
	  $booking_message .= print_r($order_result, true);
	//$booking_message .= 'Paypal Transaction ID: ' . $transaction_id;
	wp_mail('rob@travelgift.uk', 'Attraction Booking Order Complete', $booking_message);
}
  ?>
<div>
	  <div class="order-info">
		  <div class=""> Placed on <?php echo $order_result['created'];?>
		  <br/>
		  Departure Date: <?php echo $order_result['departure_date'];?>
		  <br/>
		  Total Price: <span>£ <?php echo $order_result['total'];?></span>
		  <br/>	
		  Total commission: <span>£ <?php echo $order_result['trade']['commission_total'];?></span>
		  </div>
	  </div>
	  <div class="order-detail-page">
		  <div class="order-table-content">
		<div class="order-content">
			<h4>Billing Address</h4>
			  <div class="order_address"><?php echo $order_result['billing_address_fname'];?> <?php echo $order_result['billing_address_lname'];?></div>
			  <div class="address_det"><?php echo $order_result['billing_address_street1'];?> <?php echo $order_result['billing_address_city'];?> <?php echo $order_result['billing_address_region'];?> <?php echo $order_result['billing_address_postal_code'];?> <?php echo $order_result['billing_address_country'];?></div>
			  <div class="order-phone"><?php echo $order_result['billing_address_phone'];?></div>
		  </div>
		  <div class="delivery-content">
		  <h4>Delivery Address</h4>
			  <div class="delivery_address"><?php echo $order_result['delivery_address_fname'];?> <?php echo $order_result['delivery_address_lname'];?></div>
			  <?php echo $order_result['delivery_address_region'];?> <?php echo $order_result['delivery_address_postal_code'];?> <?php echo $order_result['delivery_address_country'];?></div>
			  <div class="delivery-phone"><?php echo $order_result['delivery_address_phone'];?></div>
		  </div>
	</div>
	  <div class="tickets-information">
		  <div class="order-pro-info">
			  <?php $i = 1;
				  foreach ($order_result['order_tickets'] as $key => $value){ 
					  if($i == 1){?>
						  <h4 class="pro_title"><?php echo $value['product_title'];?></h4> 
						   <?php foreach ($value['attributes'] as $key => $attribute) {
							   if($attribute['title'] == 'Date'){
								   echo $attribute['values'][0];
							   }
							   if($attribute['title'] == 'Time'){
								   echo $attribute['values'][0];
							   }
						   } ?>
					  <?php } ?>
				 
					  <div class="qty-add">
				   <?php echo $value['qty']; ?><span> x  <?php  echo $value['type']; ?> <?php echo $value['type_description']; ?></span>
				  <?php $i++; 
				  } 
			  ?>
				  </div>
		</div>  
	  </div>
</div>
