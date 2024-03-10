<?php

global $wpdb;
// $car = isset($_SESSION['dca_booking_data']) ? $_SESSION['dca_booking_data'] : array();
// print_r($car);
if (isset($_POST['car_book'])) {
	// print_r($_POST);
	$car_name = isset($_POST['itemName']) ? $_POST['itemName'] : '';
	$price = isset($_POST['car_amount']) ? $_POST['car_amount'] : 0;
	$image = isset($_POST['vehicleImage']) ? $_POST['vehicleImage'] : '';
	$seats = isset($_POST['car_seats']) ? $_POST['car_seats'] : 0;
	$bags = isset($_POST['car_bags']) ? $_POST['car_bags'] : 0;
	$doors = isset($_POST['car_doors']) ? $_POST['car_doors'] : 0;
	$ac = isset($_POST['car_ac']) ? $_POST['car_ac'] : false;
	$days = isset($_POST['rentdays']) ? $_POST['rentdays'] : 0;
	$fuel_policy_id = isset($_POST['fuel_policy']) ? $_POST['fuel_policy'] : 0;
	$pickup_id = isset($_POST['pickup_id']) ? $_POST['pickup_id'] : 0;
	$pick_date_time = isset($_POST['pick_date_time']) ? $_POST['pick_date_time'] : '';
	$drop_date_time = isset($_POST['drop_date_time']) ? $_POST['drop_date_time'] : '';
	$pick_location = isset($_POST['pick_location']) ? $_POST['pick_location'] : 0;
	$dropoff_location = isset($_POST['dropoff_location']) ? $_POST['dropoff_location'] : 0;
	$fuel_policy = '';
	$policy_tbl = $wpdb->prefix . 'discovercars_fuel_policies';
	$location_types_tbl = $wpdb->prefix . 'discovercars_location_types';
	if ($fuel_policy_id) {
		$fuel_policy = $wpdb->get_row("SELECT * FROM $policy_tbl WHERE `policy_id` = '$fuel_policy_id'");
	}
	$pickup_location_type = '';
	if ($pickup_id) {
		$pickup_location_type = $wpdb->get_row("SELECT * FROM $location_types_tbl WHERE `location_id` = '$pickup_id'");
	}
	//session_destroy();
?>

	<div class="main-discover-car">
		<div class="ct-section-inner-wrap">
			<h5>Booking prepare </h5>
			<div class="hotel_prepare_list">
				<h3>Car Details</h3>
				<div class="hotel_prepare_section">
					<h6>Car Name: <?= $car_name; ?></h6>
					<div class="dca-vehicle-feature">
						<ul>
							<li class="gc-icon-seats"><?= $seats; ?> seats</li>
							<li class="gc-icon-bags"><?= $bags; ?> bags</li>
							<li class="gc-icon-doors"><?= $doors; ?> doors</li>
							<?php if ($ac) : ?><li class="gc-icon-ac">Air Conditioning</li><?php endif; ?>
						</ul>
					</div>
					<div class="dca-rental-days">
						Rental cost for <?= $days; ?> days : <?= '£' . $price ?>
					</div>
				</div>
			</div>

			<div class="fuel-policy">
				<div class="row">
					<?php if ($fuel_policy) : ?>
						<div class="col-md-6">
							<div class="fuel-policy-details">
								<div class="gc_data_title">Fuel policy</div>
								<div class="gc_data_value"><?= $fuel_policy->name; ?></div>
								<p class="gc_data_desc">
									<?= $fuel_policy->description; ?>
								</p>
							</div>
						</div>
					<?php endif; ?>
					<?php if ($pickup_location_type) : ?>
						<div class="col-md-6">
							<div class="fuel-policy-details">
								<div class="gc_data_title">Pick-up location</div>
								<div class="gc_data_value"><?= $pickup_location_type->name; ?></div>
								<p class="gc_data_desc"><?= $pickup_location_type->description; ?></p>
							</div>
						</div>
					<?php endif ?>
				</div>
			</div>

			<div class="driver-all-details">

				<h3 class="booking-title"><?php _e('Driver details', 'discover-cars-api'); ?></h3>
				<form method="post" id="rob-book-car">
					<input type="hidden" name="pick_date_time" value="<?php echo $pick_date_time; ?>" class="pick_date_time">
                      <input type="hidden" name="drop_date_time" value="<?php echo $drop_date_time; ?>" class="drop_date_time">

					<input type="hidden" name="pick_location" value="<?php echo $pick_location; ?>" class="pick_location">
                  	<input type="hidden" name="dropoff_location" value="<?php echo $dropoff_location; ?>" class="dropoff_location">
					<div class="dca-booking-err"></div>
					<div class="row car-driver-details">
						<div class="form-group col-lg-4">
							<label for="driverTitle" class="driver-title"><?php _e('Title:', 'discover-cars-api'); ?></label>
							<select id="driverTitle" class="select validate-input" data-valid="Please select the Title.">
								<option value="0"><?php _e('-', 'discover-cars-api'); ?></option>
								<option value="mr"><?php _e('Mr.', 'discover-cars-api'); ?></option>
								<option value="mrs"><?php _e('Mrs.', 'discover-cars-api'); ?></option>
								<option value="ms"><?php _e('Ms.', 'discover-cars-api'); ?></option>
								<option value="dr"><?php _e('Dr.', 'discover-cars-api'); ?></option>
							</select>
						</div>
						<div class="form-group col-lg-4">
							<label for="driverName"><?php _e('First name:', 'discover-cars-api'); ?></label>
							<input id="driverName" type="text" name="driver_first_name" class="input-field validate-input driver-first-name" type="text" data-valid="Please enter the first name.">
						</div>
						<div class="form-group col-lg-4">
							<label for="driverLastName"><?php _e('Last name:', 'discover-cars-api'); ?></label>
							<input id="driverLastName" name="driver_last_name" class="input-field validate-input required" type="text" data-valid="Please enter the last name.">
						</div>
						<div class="form-group col-lg-4">
							<label for="driverEmail"><?php _e('Email:', 'discover-cars-api'); ?></label>
							<input id="driverEmail" name="driver_email" class="input-field validate-input required" type="email" tabindex="4" data-valid="Please enter the email." data-valid2="Please enter valid email address.">
						</div>

						<div class="form-group col-lg-4">
							<div class="phone-with-country-code">
								<label for="phoneCountryCode"><?php _e('Phone:', 'discover-cars-api'); ?></label>
								<input id="phoneCountryCode" type="number" name="country_code" class="input-field country-code validate-input" placeholder="+44" data-valid="Please enter the country code." data-valid2="Please enter valid country code.">
								<input id="driverPhone" name="driver-phone" class="input-field driver-phone validate-input" type="number" placeholder="15 1317 2610" data-valid="Please enter the phone number." data-valid2="Please enter valid phone number.">
							</div>
						</div>
						<div class="form-group col-lg-4">
							<label for="phoneCountryCode"><?php _e('Date of birth:', 'discover-cars-api'); ?></label>
							<div class="dob-fir-nd-last dob-day">
								<select id="booking_day" name="dd" class="validate-input" data-valid="Please select day."></select>
							</div>
							<div class="dob-fir-nd-last dob-mon">
								<select id="booking_month" name="mm" class="validate-input" onchange="change_month(this)" data-valid="Please select month."></select>
							</div>
							<div class="dob-fir-nd-last dob-year">
								<select id="booking_year" name="yyyy" class="validate-input" onchange="change_year(this)" data-valid="Please select year."></select>
							</div>
						</div>
						<div class="form-group country-of-residence col-lg-4">
							<label for="residence">Country of residence</label>
							<select id="residence" name="residence_country" class="residence validate-input booking" data-valid="Please select country.">
								<option value="">Select</option>
								<?php foreach (dca_countries() as $key => $country) {
									echo '<option value=' . $key . '>' . $country . '</option>';
								} ?>
							</select>

						</div>
					</div>
					<div class="row car-driver-details-check">
						<div class="form-group col-md-12">
							<input type="checkbox" id="special_offers" name="special-offers" value="special-offers">
							<label for="special_offers"><?php _e('If you don\'t wish to receive emails with special offers and discounts from Discover Cars, please tick the box.', 'discover-cars-api'); ?></label>
						</div>
					</div>
					<input type="button" id="book-now" value="Book Now">
					<div class="row">
						<div class="col-sm-12 col-md-12">
							<h5 style="padding-top:50px;">Gift Card Code</h5>
							<div class="row gc-coupon-code-main">
								<div class="col-sm-6 col-md-6">
									<label class="" id="coupon_code_smg"></label>
									<div class="form-group" style="width:70%; float:left;">
										<input type="hidden" name="coupon_code_quote_id" value="" id="coupon_code_quote_id">
										<input type="text" name="coupon_code_check" placeholder="Enter giftcard4travel gift card code." class="coupon_code_value" id="coupon_code_value" />
									</div>
									<div class="form-group" style=" width:30%; float:left;">
										<input type="button" name="coupon_code_check_ability" class="" onclick="carcouponsearchCode()" value="Apply" style="background: #3585c5;padding: 18px;border-radius: 0;" />
										<input type="hidden" name="car_amount" id="car_amount" value="<?= $price ?>">
										<input type="hidden" name="discount_coupen_code" id="discount_coupen_code">
										<input type="hidden" name="discount_coupen_value" id="discount_coupen_value">
									</div>
								</div>



								<div class="col-sm-6 col-md-6" style="text-align:center;">
									<div class="paypal_section">
										<table>
											<tr>
												<td>Car price :</td>
												<td><?= '£' . $price ?></td>
											</tr>
											<tr>
												<td><span style="display:none;" id="coupon_price_hidden">Coupon price :</span></td>
												<td><span style="display:none;" class="coupon_price_hidden">£<span id="byc_coupon_amount_total"></apan></span></td>
											</tr>
											<tr>
												<td colspan="2">
													<div style="height: 1px;
    background-color: #5b9cd0;"></div>
												</td>
											</tr>
											<tr>
												<td>Total Price :</td>
												<td><span id="car_book_amount_total"><?= '£' . $price ?></span></td>
											</tr>
										</table>
										<div class="col-md-12 col-sm-12 poll-right">
											<input type="hidden" name="total_amount" id="total_amount" value="<?= $price; ?>">
											<input type="hidden" name="seats" id="dca_seats" value="<?= $seats; ?>">
											<input type="hidden" name="bags" id="dca_bags" value="<?= $bags; ?>">
											<input type="hidden" name="doors" id="dca_doors" value="<?= $doors; ?>">
											<input type="hidden" name="air_condition" id="dca_ac" value="<?= $ac; ?>">
											<input type="hidden" name="car_name" id="dca_car_name" value="<?= $car_name; ?>">
											<input type="hidden" name="rent_days" id="dca_rent_days" value="<?= $days; ?>">

											<input type="image" class="book-now_btn" name="submit" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynow_LG.gif" alt="PayPal - The safer, easier way to pay online">
										</div>
										<div class="col-md-12 col-sm-12">
											<img src="<?php echo DCA_URL . 'public/images/paypal_payment.png'; ?>" />
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>
				<!-- <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" id="paypal-form"> -->
				<!-- <input type="hidden" name="business" value="businesscmit@gmail.com"> -->
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post" id="paypal-form" target="_top">
					<!-- Identify your business so that you can collect the payments. -->
					<input type="hidden" name="business" value="rob@giftcards4travel.co.uk">

					<!-- Specify a Buy Now button. -->
					<input type="hidden" name="cmd" value="_xclick">

					<!-- Specify details about the item that buyers will purchase. -->
					<input type='hidden' name='item_name' id='paypal-item' value="<?= $car_name; ?>">
					<input type="hidden" name="custom" id="paypal-booking-id-cstm">
					<input type="hidden" name="amount" id="paypal-amount" value="<?= $price; ?>">
					<input type="hidden" name="discount_amount" class="byc_coupon_amount" value="0">
					<input type="hidden" name="currency_code" value="GBP">

					<!-- Specify URLs -->
					<input type="hidden" name="notify_url" value="<?php echo site_url() . '/cars-thank-you/'; ?>">
					<input type="hidden" name="return" value="<?php echo site_url() . '/cars-thank-you/'; ?>">
					<input type="hidden" name="cancel_return" value="<?php echo site_url(); ?>">
					<!-- Display the payment button. -->
				</form>
				<div class="gc-sm-12 gc-md-12" style="width: 100%;">
                <div id="payment-iframe"></div>
                <script src="https://sdk.felloh.com/"></script>
                <script type="text/javascript">
            	jQuery(function(){
                	jQuery("#book-now").on('click',function(){
					    if (form_error()) {
					      return false;
					    }
					    var data = jQuery("#rob-book-car").serialize();
					    jQuery(".booking-res").remove();
					    var postdata = {
					      action: "dca_paypal_pament",
					      data: data,
					    };
                    	  <?php $mode = get_option('options__felloh_api_mode'); if($mode == 'sandbox'): $public_key = get_option('options__felloh_sandbox_public_key');?>
			              var SDK = new FellohPayments('payment-iframe', '<?=$public_key?>',{"sandbox":true});
			              <?php else: $public_key = get_option('options__felloh_live_public_key');?>
			              var SDK = new FellohPayments('payment-iframe', '<?=$public_key?>');
			              <?php endif; ?>
	                    var booking_id = '';
					    jQuery(".dca-search-heading").hide();
					    jQuery(".dca-loader").fadeIn();
					    jQuery.ajax({
					      type: "POST",
					      dataType: "json",
					      url: "<?=site_url()?>/wp-admin/admin-ajax.php",
					      data: postdata,
					      success: function (response) {
					      	booking_id = response.booking_id;
		                    jQuery.ajax({
		                      url: '<?=get_site_url()?>/wp-admin/admin-ajax.php?action=felloh_create_ecom',
		                      method: 'POST',
		                      dataType: 'json',
		                      data: {"action": "felloh_create_ecom","form_data":jQuery("#paypal-form").serialize()+"&"+jQuery("#rob-book-car").serialize()+"&"+"custom="+booking_id},
		                      success: function(resp) {
		                      	if(resp.data.length != 0){
			                        SDK.render(resp.data.id);
			                        jQuery(".dca-loader").fadeOut();
		                      	} else {
		                      		console.table(response);
          							alert("Could not render payment form. Please try again at some time later.");
		                      	}
		                      }
		                    })
					      },
					    });
					    SDK.onSuccess(function(data){
	                      console.log("Payment Successful");
	                      jQuery.ajax({
			                  url: "<?=get_site_url()?>/wp-admin/admin-ajax.php",
			                  method: "POST",
			                  dataType: 'json',
			                  data: {'action':'dca_payment_update','booking_id':booking_id,'txn_id':data?.transaction?.id,'amt':jQuery("input[name='amount']").val()},
			                  success: function(res){
			                    if(res){
			                      location.href="<?=home_url()?>/cars-thank-you/";
			                    }
			                  },
			                  error: function(xhr, status, error) {
			                    console.error("AJAX Error:", error);
			                }
			                })
	                      console.table(response);
	                    });
	                    SDK.onDecline(function(){
	                      console.log("Payment Declined");
	                    });  
                	})
            	})
                </script>
              </div>

			</div>
		</div>
		<div class="dca-loader" style="display: none;">
			<img src="<?= plugin_dir_url(__FILE__) . 'images/loading-car.gif' ?>" />
		</div>
	</div>
	</div>
	</div>
	<style type="text/css">
		input[type=submit],
		input[type=button],
		textarea,
		select,
		input[type=text],
		input[type=email],
		input[type=tel],
		input[type=time] {
			width: 100%;
		}
	</style>
<?php

}
?>