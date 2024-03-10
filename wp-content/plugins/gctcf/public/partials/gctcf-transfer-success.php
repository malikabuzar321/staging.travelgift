<?php

$discount_coupon_code = '';
$txn_id = isset($_REQUEST['tx']) ? $_REQUEST['tx'] : 0;
$item_number = isset($_REQUEST['item_number']) ? $_REQUEST['item_number'] : 0;

global $wpdb;
$sql_txn = "SELECT * FROM `transfers_booking_table` WHERE txn_id='" . $txn_id . "'";
$result = $wpdb->get_results($sql_txn);


$paypal_unick_code = '';
$discount_coupon_amount = 0;
$transaction_id = 0;
$travel_transfer_id = 0;
foreach ($result as $result_obj) {
	$paypal_unick_code = $result_obj->paypal_unick_code;
	$discount_coupon_amount = $result_obj->coupon_discount_amount;
	$transaction_id = $result_obj->txn_id;
	$travel_transfer_id = $result_obj->travel_transfer_id;
}

if (!empty($paypal_unick_code)) {

	if ($item_number == $paypal_unick_code) {

		$select_sql = "SELECT * FROM `transfers_booking_table` WHERE `paypal_unick_code`='" . $paypal_unick_code . "' AND `client_trans_no`='" . $travel_transfer_id . "' ";
		$get_sql_result = $wpdb->get_results($select_sql);
		//pre($get_sql_result, 1);
		$id = 0;
		foreach ($get_sql_result as $get_sql_result_obj) {

			$id = $get_sql_result_obj->id;
		}

		$update_sql = "UPDATE `transfers_booking_table` SET `payment_status`='Confirm',`transaction_id`='" . $transaction_id . "',`discount_coupon_value`='" . $discount_coupon_amount . "' WHERE `id`='" . $id . "'";
		$wpdb->query($update_sql);


		$travel_client_sql = "SELECT * FROM `transfers_booking_table` WHERE `payment_status`='Confirm' AND `paypal_unick_code`='" . $paypal_unick_code . "'";

		$client_detailes_result = $wpdb->get_results($travel_client_sql);


		foreach ($client_detailes_result as $client_detailes) {
			$client_transac_no = $client_detailes->client_trans_no;
			$client_title = $client_detailes->client_title;
			$client_first_name = $client_detailes->client_fname;
			$client_last_name = $client_detailes->client_lname;
			$client_country_code = $client_detailes->client_country_code;
			$client_phone_no = $client_detailes->client_phone;
			$client_mobile_no = $client_detailes->client_mobile;
			$client_email = $client_detailes->client_email;
			$client_property_name = $client_detailes->client_property_name;
			$client_address_1 = $client_detailes->client_address1;
			$client_address_2 = $client_detailes->client_address2;
			$client_refeerence = $client_detailes->client_refeerence;
			$client_dept_point = $client_detailes->client_dept_point;
			$client_ret_point = $client_detailes->client_ret_point;
			$client_dep_info = $client_detailes->client_dept_info;
			$client_ret_info = $client_detailes->client_ret_info;
			$client_dep_ext_info = $client_detailes->client_dept_ext_info;
			$client_ret_ext_info = $client_detailes->client_ret_ext_info;
			$client_by_email_send = $client_detailes->client_email_send;
			$client_remark = $client_detailes->client_remark;
			$client_transfer_price = $client_detailes->transfer_price;

			$discount_coupon_code = $client_detailes->discount_coupon_code;
			$discount_coupon_amount = $client_detailes->discount_coupon_value;
		}
		$settings = get_transfer_api_config();

		$user_p2p_xmldata = '<?xml version="1.0" encoding="UTF-8"?>
							<TCOML version="NEWFORMAT" sess="">
								<TransferOnly>
									<Booking>
										<Confirm>
											<TransacNo>' . $client_transac_no . '</TransacNo>
											<Username>' . $settings['user'] . '</Username>
								            <Password>' . $settings['pass'] . '</Password> 			
											<AgentBref>' . $client_refeerence . '</AgentBref>
											<PropertyName>' . $client_property_name . '</PropertyName>
											<AccommodationAddress>' . $client_address_1 . '</AccommodationAddress>
											<AccommodationAddress2>' . $client_address_2 . '</AccommodationAddress2>
											<DepPoint>' . $client_dept_point . '</DepPoint>
											<RetPoint>' . $client_ret_point . '</RetPoint>
											<DepInfo>' . $client_dep_info . '</DepInfo>
											<RetInfo>' . $client_ret_info . '</RetInfo>
											<DepExtraInfo>' . $client_dep_ext_info . '</DepExtraInfo>
											<RetExtraInfo>' . $client_ret_ext_info . '</RetExtraInfo>
											<Client>
												<LastName>' . $client_last_name . '</LastName>
												<FirstName>' . $client_first_name . '</FirstName>
												<Title>' . $client_title . '</Title>
												<Phone>' . $client_phone_no . '</Phone>
												<Mobile>' . $client_mobile_no . '</Mobile>
												<CountryCode>' . $client_country_code . '</CountryCode>
												<Email>' . $client_email . '</Email>
											</Client>
											<SendEmailToCustomer>' . $client_by_email_send . '</SendEmailToCustomer>
											<Remark>' . $client_remark . '</Remark>
											<ChaseID></ChaseID>
										</Confirm>
									</Booking>
								</TransferOnly>
							</TCOML>';


		$p2pxml_car_api_url =  $settings['url'];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $p2pxml_car_api_url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $user_p2p_xmldata);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-type: text/xml',
			'Content-length: ' . strlen($user_p2p_xmldata)
		));

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

		$p2p_xml_api_output_for_cars_search = curl_exec($ch);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if (curl_exec($ch) === false) {
			//echo 'Curl error: ' . curl_error($ch);
		} else {
			// echo '<br />';
			//echo 'Room Search operation completed';
		}

		curl_close($ch);
		// pre($p2p_xml_api_output_for_cars_search);
		$cabs_booking_result = simplexml_load_string($p2p_xml_api_output_for_cars_search);

?>


		<div class="gc-container print_this_container">
			<div class="gc-row">
				<p class="print-this-heading">e-Ticket - Please print two copies of this e-Ticket, one each for your arrival and departure service.</p>
				<div class="gc-md-12 gc-sm-12 print_this_container-one-row">
					<div class="gc-md-2 gc-sm-2">
						<label class="cabs_confirm"><b>Lead Passenger</b></label>
						<p><?php echo $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->Client->Title . ' ' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->Client->FirstName . ' ' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->Client->LastName; ?></p>
					</div>
					<div class="gc-md-2 gc-sm-2">
						<label class="cabs_confirm">Transfer Provider</label>
						<p><?php echo $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->SupplierName; ?></p>
					</div>
					<div class="gc-md-2 gc-sm-2">
						<label class="cabs_confirm">Amount</label>
						<p><?php echo '£' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->HolidayValue; ?></p>
					</div>
					<div class="gc-md-3 gc-sm-3">
						<label class="cabs_confirm">Booking Reference No</label>
						<p><?php echo $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->BookingRef; ?> <label>(<?php echo $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->BookingStatus; ?>)</label></p>
					</div>
					<div class="gc-md-3 gc-sm-3">
						<label class="cabs_confirm">Destination</label>
						<p><?php echo $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->PropertyName . ',' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->AccommodationAddress . ',' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->AccommodationAddress2 ?></p>
					</div>
				</div>

				<div class="gc-md-12 gc-sm-12 print_this_container-two-row-main">
					<h5>My journey</h5>
					<div class="print_this_container-two-row print_this_container-one-row">
						<div class="gc-md-3 gc-sm-3">
							<label class="cabs_confirm">From </label>
							<p><?php echo $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->OutboundTransferDetails->OutboundOrigin; ?></p>
						</div>
						<div class="gc-md-3 gc-sm-3">
							<label class="cabs_confirm">To </label>
							<p><?php echo $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->OutboundTransferDetails->OutboundOrigin; ?></p>
						</div>
						<div class="gc-md-3 gc-sm-3">
							<label class="cabs_confirm"><i class="fa fa-calendar-o" aria-hidden="true"></i> Outbound Date </label>
							<p><?php echo $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->OutboundTransferDetails->ArrDate; ?> At <?php echo $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->OutboundTransferDetails->ArrTime; ?>(<?php echo $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->OutboundTransferDetails->DepInfo; ?>)</p>
						</div>
						<div class="gc-md-3 gc-sm-3">
							<label class="cabs_confirm"><i class="fa fa-clock-o" aria-hidden="true"></i> Estimated Transfer Time </label>
							<p>30 minutes</p>
						</div>
					</div>
				</div>

				<div class="print_this_container-two-row print_this_container-one-row">
					<div class="gc-md-3 gc-sm-3">
						<label class="cabs_confirm"><i class="fa fa-taxi" aria-hidden="true"></i> Vehicle Type </label>
						<p><?php echo $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->OutboundTransferDetails->Vehicle; ?></p>
					</div>

					<div class="gc-md-3 gc-sm-3">
						<label class="cabs_confirm"><i class="fa fa-users" aria-hidden="true"></i> Passengers(Adults)</label>
						<p><?php echo $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->Adults; ?></p>
					</div>
					<div class="gc-md-3 gc-sm-3">
						<label class="cabs_confirm"><i class="fa fa-users" aria-hidden="true"></i> Passengers(Children)</label>
						<p><?php echo $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->Children; ?></p>
					</div>
					<div class="gc-md-3 gc-sm-3">
						<label class="cabs_confirm"><i class="fa fa-briefcase" aria-hidden="true"></i> Per Person </label>
						<p>1 x Suitcase</p>
					</div>
				</div>


				<?php
				$vehicle_type_section = $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->SectorType;
				if ($vehicle_type_section == "RETURN") {
				?>
					<div class="gc-md-12 gc-sm-12 print_this_container-two-row-main">
						<h5> My journey(Return)</h5>
						<div class="print_this_container-two-row print_this_container-one-row">
							<div class="gc-md-3 gc-sm-3">
								<label class="cabs_confirm">From </label>
								<p><?php echo $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->ReturnTransferDetails->ReturnOrigin; ?></p>
							</div>
							<div class="gc-md-3 gc-sm-3">
								<label class="cabs_confirm">To </label>
								<p><?php echo $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->ReturnTransferDetails->ReturnDestination; ?></p>
							</div>
							<div class="gc-md-3 gc-sm-3">
								<label class="cabs_confirm"><i class="fa fa-calendar-o" aria-hidden="true"></i> Outbound Date </label>
								<p><?php echo $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->ReturnTransferDetails->RetDate; ?> At <?php echo $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->ReturnTransferDetails->ReturnPickupTime; ?>(<?php echo $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->ReturnTransferDetails->RetInfo; ?>)</p>
							</div>
							<div class="gc-md-3 gc-sm-3">
								<label class="cabs_confirm"><i class="fa fa-clock-o" aria-hidden="true"></i> Estimated Transfer Time </label>
								<p>30 minutes</p>
							</div>
						</div>
					</div>
					<div class="gc-md-12 gc-sm-12">
						<div class="print_this_container-two-row print_this_container-one-row print_this_container-three-row">
							<div class="gc-md-3 gc-sm-3">
								<label class="cabs_confirm"><i class="fa fa-taxi" aria-hidden="true"></i> Vehicle Type </label>
								<p><?php echo $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->ReturnTransferDetails->Vehicle; ?></p>
							</div>

							<div class="gc-md-3 gc-sm-3">
								<label class="cabs_confirm"><i class="fa fa-briefcase" aria-hidden="true"></i> Per Person </label>
								<p>1 x Suitcase</p>
							</div>

							<div class="gc-md-3 gc-sm-3">
								<label class="cabs_confirm">Pick-up time</label>
								<p>Check pick-up time on <a href="http://www.a2btransfers.com/">a2btransfers.com</a> 24 hours prior to departure</p>
							</div>
						</div>
					</div>
				<?php
				}
				?>

				<div class="gc-md-12 gc-sm-12 transferOnly-notes-inner">
					<h5>Notes:-</h5>
					<p><?php echo $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->JoiningIns->JoinLine; ?></p>
				</div>
				<?php
				if ($cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->BookingRef) :

					$booking_message = 'A new transfer booking was made on GiftCards4Travel. Reference: ' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->BookingRef . "\r\n";
					$booking_message .= 'Paypal Transaction ID: ' . $item_number;
					wp_mail('rob@giftcards4travel.co.uk', 'Transfer Booking Payment Complete - ' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->BookingRef, $booking_message);

					if ($discount_coupon_code) :
						$coupon_wp = new WC_Coupon($discount_coupon_code);
        
						$amount = $coupon_wp->get_amount();
						if ($amount) :
							if ($coupon_wp->get_discount_type() != 'percent'):
								$new_remaining_amount = $amount - $discount_coupon_amount;
								$coupon_wp->set_amount($new_remaining_amount);
								$coupon_wp->save();
							endif;
						else :
							$store_vouchers = get_posts(array(
								'post_type' => 'gc4t_store_voucher',
								'post_status' => 'private',
								'posts_per_page' => 1,
								'meta_query' => array(
									array(
										'key' => 'gc4t_voucher_code',
										'value' => $discount_coupon_code,
									),
									array(
										'key' => 'gc4t_voucher_tranfer_ref',
										'compare' => 'NOT EXISTS'
									)
								)
							));
							if (isset($store_vouchers[0])) :



								$voucher_amount = get_post_meta($store_vouchers[0]->ID, 'gc4t_voucher_amount', true);
								$voucher_amount_remaining = get_post_meta($store_vouchers[0]->ID, 'gc4t_voucher_amount_remaining', true);
								$new_remaining_amount = $voucher_amount_remaining - $discount_coupon_amount;
								$new_remaining_amount = max($new_remaining_amount, 0);
								update_post_meta($store_vouchers[0]->ID, 'gc4t_voucher_amount_remaining', $new_remaining_amount);
								update_post_meta($store_vouchers[0]->ID, 'gc4t_voucher_tranfer_ref', $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->BookingRef);
								if ($new_remaining_amount <= 0) :
									update_post_meta($store_vouchers[0]->ID, 'gc4t_voucher_status', 'used');
								endif;
							endif;
						endif;
					endif; ?>
					<script language=JavaScript src="https://portgk.com/create-sale?client=java&MerchantID=2189&SaleID=<?php echo $client_transac_no; ?>&Purchases=Transfer,<?php echo $client_transfer_price; ?>"></script>
					<noscript><img src="https://portgk.com/create-sale?client=img&MerchantID=2189&SaleID=<?php echo $client_transac_no; ?>&Purchases=Transfer,<?php echo $client_transfer_price; ?>" width="10" height="10" border="0"></noscript>
				<?php
				endif; ?>

			</div>
		</div>
		<?php
		$client_email_send = '';
		foreach ($client_detailes_result as $client) {
			$client_email_send = $client->client_email;
		}
		$user = get_user_by('email', $client_email_send);
		$user_id = $user->ID;
		$booking_args = array(
			'post_type'   => 'transfer_booking',
			'post_status' => 'private',
			'post_author' => $user_id,
			'post_title'  => 'Transfer booking',
			'meta_query' => array(
				array(
					'key' => 'gc4t_paypal_unique_code',
					'value' => $paypal_unick_code,
				),
			)
		);
		$the_query = new WP_Query($booking_args);
		$email_content .= '<html>
									<head>
										<title>Booking Email</title>
										<meta charset="UTF-8">
										<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
									</head>
									<body class="booking-email-block" style="padding: 0px; margin: 0px;">
										<table width="100%" style="max-width: 1200px; padding: 15px; font-family:Arial, Helvetica, sans-serif; border:1px solid #061d2f; color:#061d2f" cellpadding="0" cellspacing="0">
											<tr>
												<td colspan="5"><h2 style="font-size: 16px; font-weight: 400;background-color: #fad38f; padding: 10px 15px; text-align: left; margin: 0px;font-family: sans-serif; color: #061d2f;">e-Ticket-Please print two copies of this e-Ticket, oneeach for your arrival and departure service.</h2></td>
											</tr>
											<tr>
												<td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Lead Passenger</strong></td>
												<td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px;padding-top: 20px;"><strong>Transfer Provide</strong></td>
												<td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px;padding-top: 20px;"><strong>Amount</strong></td>
												<td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Booking Reference No</strong></td>
												<td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Destination</strong></td>
											</tr>
											<tr>
												<td style="font-size: 14px;font-family: sans-serif; color: #061d2f;  padding:5px;">' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->Client->Title . ' ' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->Client->FirstName . ' ' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->Client->LastName . '</td>
												<td style="font-size: 14px;font-family: sans-serif; color: #061d2f;  padding:5px;">' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->SupplierName . '</td>
												<td style="font-size: 14px;font-family: sans-serif; color: #061d2f;  padding:5px;">£' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->HolidayValue . '</td>
												<td style="font-size: 14px;font-family: sans-serif; color: #061d2f;  padding:5px;">' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->BookingRef . '(' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->BookingStatus . ')</td>
												<td style="font-size: 14px;font-family: sans-serif; color: #061d2f;  padding:5px;">' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->PropertyName . ',' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->AccommodationAddress . ',' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->AccommodationAddress2 . '</td>
											</tr>
											<tr>
												<td colspan="5" style="padding-top:30px;"><h2 style="font-size: 16px; font-weight: 700;background-color: #fad38f; padding: 10px 15px; text-align: left; margin: 0px;font-family: sans-serif; color: #061d2f; text-transform: uppercase;">My Journey</h2></td>
											</tr>
											<tr>
												<td width="25%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>From</strong></td>
												<td width="25%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left;  padding:5px;padding-top: 20px;"><strong>To</strong></td>
												<td width="25%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><img src="' . GCTCF_URL . 'public/images/calendar-icon.png" width="15" style="vertical-align: middle; padding-right: 5px;"> <strong>Outbound Date</strong></td>
												<td width="25%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><img src="' . GCTCF_URL . 'public/images/times-icon.png" width="15" style="vertical-align: middle; padding-right: 5px;"> <strong>Estimated Transfer Time</strong></td>
											</tr>
											<tr>
												<td style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->OutboundTransferDetails->OutboundOrigin . '</td>
												<td style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->OutboundTransferDetails->OutboundOrigin . '</td>
												<td style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->OutboundTransferDetails->ArrDate . ' At ' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->OutboundTransferDetails->ArrTime . '(' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->OutboundTransferDetails->DepInfo . ')</td>
												<td style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">30 Minutes</td>
											</tr>
											<tr>
												<td width="25%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><img src="' . GCTCF_URL . 'public/images/car-icon.png" width="15" style="vertical-align: middle; padding-right: 5px;"> <strong>Vehicle Type</strong></td>
												<td width="25%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><img src="' . GCTCF_URL . 'public/images/passenger-icon.png" width="15" style="vertical-align: middle; padding-right: 5px;"><strong>Passengers(Adults)</strong></td>
												<td width="25%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><img src="' . GCTCF_URL . 'public/images/passenger-icon.png" width="15" style="vertical-align: middle; padding-right: 5px;"><strong>Passengers(Children)</strong></td>
												<td width="25%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><img src="' . GCTCF_URL . 'public/images/business-icon.png" width="15" style="vertical-align: middle; padding-right: 5px;"><strong>Per Person</strong></td>
											</tr>
											<tr>
												<td style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->OutboundTransferDetails->Vehicle . '</td>
												<td style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->Adults . '</td>
												<td style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->Children . '</td>
												<td style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">1 × Suitcase</td>
											</tr>
											<tr>
												<td colspan="5" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px;padding-top: 20px;"><strong>Notes:-</strong></td>
											</tr>
											<tr>
												<td colspan="5" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">' . $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->JoiningIns->JoinLine . '</td>
											</tr>';
		if ($the_query->have_posts()) {
			$booking_price = $cabs_booking_result->TransferOnly->Booking->Confirm->VoucherInfo->HolidayValue;
			while ($the_query->have_posts()) {
				$the_query->the_post();
				$discount_coupen_code = get_post_meta(get_the_ID(), 'gc4t_discount_coupon_code', true);
				if ($discount_coupen_code != '') {

					$discount_coupen_value = get_post_meta(get_the_ID(), 'gc4t_discount_coupon_value', true);
					if ($booking_price > $discount_coupen_value) {
						$booking_value = $booking_price - $discount_coupen_value;
					} else {
						$booking_value = '1.00';
					}

					if ($booking_price)
						$email_content .= ' <tr>
									<td colspan="5" style="padding-top:20px;">
									<table width="100%" cellpadding="0" cellspacing="0">
									<tr>
									<td style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:20px; border:2px solid #ccc;"><strong style="padding-right: 10px;">Gift Card Code</strong> <span style="color: #061d2f; font-size: 16px; font-weight: 600; padding: 7px 15px; background-color: #f9d28e;">' . $discount_coupen_code . '</span></td>
									<td style="padding:15px; border:2px solid #ccc; border-left-width:0;"><table width="100%" cellpadding="0" cellspacing="0">
										<tbody>
											<tr>
											<td align="left" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">Transfer Price:</td>
											<td align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;"><strong>£' . $booking_price . '</strong></td>
											</tr>
											<tr>
											<td align="left" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">Coupon Price:</td>
											<td  align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;"><strong>£' . $discount_coupen_value . '</strong></td>
											</tr>
										</tbody>
										<tfoot>
											<tr>
											<td align="left" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px; border-top:2px solid #777;">Total Price:</td>
											<td align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px; border-top:2px solid #777;"><strong>£' . $booking_value . '</strong></td>
											</tr>
										</tfoot>
										</table></td>
									</tr>
								</table>
									</td>
									</tr>';
				} else {
					$email_content .= ' <tr>
									<td colspan="5" style="padding-top:20px;">
									<table width="100%" cellpadding="0" cellspacing="0">
									<tr>
									
									<td align="right" style=""><table width="100%" cellpadding="0" cellspacing="0" style="max-width:300px;padding:15px; border:2px solid #ccc;">
										<tbody>
											<tr>
											<td align="left" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">Transfer Price:</td>
											<td align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;"><strong>£' . $booking_price . '</strong></td>
											</tr>
										</tbody>
										<tfoot>
											<tr>
											<td align="left" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px; border-top:2px solid #777;">Total Price:</td>
											<td align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px; border-top:2px solid #777;"><strong>£' . $booking_price . '</strong></td>
											</tr>
										</tfoot>
										</table></td>
									</tr>
								</table>
									</td>
									</tr>';
				}
			}
		}
		$email_content .= '</table><p>contact <a href="mailto:bookings@giftcards4travel.co.uk">bookings@giftcards4travel.co.uk</a> for any queries </p>
									</body>
								</html>';
		// echo $email_content;

		if ($the_query->have_posts()) {
			$to = $client_email_send;
			$group_emails = array('rob@giftcards4travel.co.uk', 'bookings@giftcards4travel.co.uk', 'developersuseonly@gmail.com', 'preeti@cmitexperts.com', 'developer@giftcards4travel.com');
			$subject = 'Booking Cofirmation';
			$headers = array('Content-Type: text/html; charset=UTF-8');
			$body = $email_content;
			while ($the_query->have_posts()) {
				$the_query->the_post();
				$email_send = get_post_meta(get_the_ID(), 'gc4t_email_send_to_customer', true);
				// wp_mail($to, $subject, $body, $headers);
				update_post_meta(get_the_ID(), 'gc4t_payment_request', $_REQUEST);
				if (($email_send == 0) && isset($_GET['payment_status']) && ($_GET['payment_status'] == 'Completed')) {
					update_post_meta(get_the_ID(), 'gc4t_email_send_to_customer', 1);
					wp_mail($to, $subject, $body, $headers);
				}
			}
		}
		wp_reset_postdata();

		?>
		<div class="gc-container">
			<div class="gc-row">
				<div class="gc-md-12 gc-sm-12 ">
					<div class="print_button">
						<input type="button" class="btn btn_print" value="Print" />
					</div>
				</div>
			</div>
		</div>

	<?php
	}
} else {

	?>
	<script>
		function reloadPage() {
			var count = 1;
			if (count < 3) {
				location.reload();
				count++;
			}
		}
		setTimeout(reloadPage, 3000);
	</script>

	<div class="" style="width:20%; margin:auto;">
		<h4 style="text-align:center; color: #fff;text-transform: capitalize;font-size: 15px;background: #0313ea;padding: 10px;border-radius: 10px;">
			<a href="http://demo-website.review/travels/<?php echo $_SERVER['REQUEST_URI']; ?>" style="color:#fff;">Check again</a>
		</h4>

	</div>

<?php

}

?>
<script src="https://rawgit.com/jasonday/printThis/master/printThis.js" type="text/javascript"></script>
<script>
	jQuery(document).ready(function() {
		jQuery(document).on('click', '.btn_print', function() {

			jQuery('.print_this_container').printThis({
				importCSS: true,
				loadCSS: "<?php echo GCTCF_URL . 'public/css/gctcf-main.css'; ?>"
			});
		});
	});
</script>