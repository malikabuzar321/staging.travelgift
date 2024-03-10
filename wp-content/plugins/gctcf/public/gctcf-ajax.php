<?php
add_action('wp_ajax_gctcf_hotel_search', 'gctcf_hotel_search');
add_action('wp_ajax_nopriv_gctcf_hotel_search', 'gctcf_hotel_search');
add_action('wp_ajax_nopriv_data_sync','data_sync');
add_action('wp_ajax_nopriv_update_hotel_details','update_hotel_details');
add_action('wp_ajax_nopriv_sync_attractions','sync_attractions');

add_action('wp_ajax_nopriv_tbo_download','tbo_download');

add_action('wp_ajax_nopriv_felloh_create_ecom','felloh_create_ecom');
add_action('wp_ajax_felloh_create_ecom','felloh_create_ecom');

add_action('wp_ajax_order_payment_giftcard','order_payment_giftcard');
add_action('wp_ajax_nopriv_order_payment_giftcard','order_payment_giftcard');

add_action('wp_ajax_attraction_payment','attraction_payment');
add_action('wp_ajax_nopriv_attraction_payment','attraction_payment');

add_action('wp_ajax_transfer_payment','transfer_payment');
add_action('wp_ajax_nopriv_transfer_payment','transfer_payment');

add_action('wp_ajax_dca_payment_update','dca_payment_update');
add_action('wp_ajax_nopriv_dca_payment_update','dca_payment_update');

add_action('wp_ajax_hotel_booking_payment_confirmation','hotel_booking_payment_confirmation');
add_action('wp_ajax_nopriv_hotel_booking_payment_confirmation','hotel_booking_payment_confirmation');


// Functions related to Felloh Payment API Integration

function hotel_booking_payment_confirmation(){
	if($_POST['form_data']){
		global $wpdb;
		$form_data = array();
		parse_str($_POST['form_data'],$form_data);
		$hotel_name = $form_data['item_name'];
    $paypal_unick_code = $form_data['item_number'];
    $hotel_quote_id = $form_data['custom'];
    $payment_status = 'completed';
    $payment_amount = $form_data['amount'];
    $payment_currency = $form_data['currency_code'];
    //$txn_id = $form_data['txn_id'];
    $txn_id = $form_data['txn_id'];
    $receiver_email = $form_data['business'];
    $payer_email = $form_data['payer_email'];
    $current_payment_date = date("Y/m/d");
    $coupon_discount_amount = $form_data['discount_amount'];
    //Travellanda
    if (strpos($hotel_quote_id, 'travellanda_') !== false) {
        $hotel_quote_id = str_replace('travellanda_', '', $hotel_quote_id);
        update_post_meta($hotel_quote_id, 'gc4t_discount_amount', $coupon_discount_amount);
        update_post_meta($hotel_quote_id, 'gc4t_receiver_email', $receiver_email);
        update_post_meta($hotel_quote_id, 'gc4t_payer_email', $payer_email);
        update_post_meta($hotel_quote_id, 'gc4t_payment_date', date('Y-m-d'));
        update_post_meta($hotel_quote_id, 'gc4t_payment_amount', $payment_amount);
        update_post_meta($hotel_quote_id, 'gc4t_payment_currency', $payment_currency);
        update_post_meta($hotel_quote_id, 'gc4t_status', $payment_status);
        update_post_meta($hotel_quote_id, 'gc4t_transaction_id', $txn_id);
    } else {
        $wpdb->query("INSERT INTO `payment_info`(`id`, `hotel_name`, `paypal_unick_code`, `hotel_quote_id`, `payment_status`, `hotel_total_price`, `mc_currency`, `txn_id`, `business`, `payer_email`, `current_payment_date`,`ipn_verified`,`coupon_discount_amount`) values('','" . $hotel_name . "','" . $paypal_unick_code . "','" . $hotel_quote_id . "','" . $payment_status . "','" . $payment_amount . "','" . $payment_currency . "','" . $txn_id . "','" . $receiver_email . "','" . $payer_email . "','" . $current_payment_date . "','Y','" . $coupon_discount_amount . "')");
    }
		// pre($form_data);exit();
    
  	echo true;
  	exit();
	}
}

function attraction_payment(){
	if($_POST){
		$post_id = $_POST['post_id'];
		$txn_id = $_POST['txn_id'];
		update_post_meta($post_id,'_gctcf_attraction_booking_txnID',$txn_id);
		if(update_post_meta($post_id, '_gctcf_payment_status', 'Paid')){
			echo true;
		}

	}
	exit();
}

function dca_payment_update(){
	if($_POST){
		$booking_id = $_POST['booking_id'];
		update_post_meta($booking_id, 'txn_id', $_POST['txn_id']);
		update_post_meta($booking_id, 'txn_amount', $_POST['amt']);
		update_post_meta($booking_id, 'txn_status', 'Paid');
		if(update_post_meta($booking_id, '_status', 'Paid')){
			echo true;
		}

	}
	exit();
}

function transfer_payment(){
	global $wpdb;
	if($_POST){
		$post_id = $_POST['booking_id'];
		update_post_meta($post_id, 'gc4t_txn_id', $_POST['form_data']['txn_id']);
		// echo $query; exit();
		if(update_post_meta($post_id, 'gc4t_payment_status', 'Paid')){
			$query = "UPDATE `transfers_booking_table` SET `payment_status` = 'Paid', `transaction_id` = '".$_POST['form_data']['txn_id']."' WHERE `paypal_unick_code` = '".$_POST['form_data']['paypal_unick_code']."'";
			$wpdb->query($query);
			    $data = [
           'item_name' => $_POST['form_data']['item_name'],
           'paypal_unick_code' => $_POST['form_data']['paypal_unick_code'],
           'travel_transfer_id' => $_POST['form_data']['travel_transfer_id'],
           'payment_status' => $_POST['form_data']['payment_status'],
           'payment_amount' => $_POST['form_data']['payment_amount'], 
           'payment_currency' => $_POST['form_data']['payment_currency'],
           'txn_id' => $_POST['form_data']['txn_id'],
           'receiver_email' => $_POST['form_data']['receiver_email'], 
           'payer_email' => $_POST['form_data']['payer_email'], 
           'current_payment_date' => $_POST['form_data']['current_payment_date'], 
           'coupon_discount_amount' => $_POST['form_data']['coupon_discount_amount']
			    ]; 
			echo json_encode([true]);
			// echo true;
		}

	}
	exit();
}

function order_payment_giftcard() {
	if($_POST){
		$order_id = $_POST['order_id'];
		include_once( WC()->plugin_path() . '/includes/wc-order-functions.php' );
		$order = wc_get_order($order_id);
		$order->update_meta_data('transaction_id', $_POST['txn_id']);
		$order->update_status( 'processing' );
		$order->save();
		$order->payment_complete();
		echo json_encode(['redirect'=>$order->get_checkout_order_received_url()]) ;
		exit();
	}
}

function create_order_giftcard(){
	$cart_items = WC()->cart->get_cart();
	$order = wc_create_order();
	foreach ($cart_items as $cart_item_key => $cart_item) {
	    $product_id = $cart_item['product_id'];
	    $quantity = $cart_item['quantity'];
	    $order->add_product(wc_get_product($product_id), $quantity);
	}
	$order->calculate_totals();
	$order_id = $order->save();
	return $order_id;
}

function send_curl($url,$request_type,$headers = array(),$data = array()){
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => $url,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => $request_type,
	  CURLOPT_HTTPHEADER => $headers,
	));
	if($request_type == 'POST' || $request_type == "PUT"){
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
	}
	$response = curl_exec($curl);

	curl_close($curl);
	return $response;
}

function felloh_get_token(){
	$mode = get_option('options__felloh_api_mode');
	if($mode == 'sandbox'){
		$baseurl = get_option('options__felloh_sandbox_url');
		$public_key = get_option('options__felloh_sandbox_public_key');
		$private_key = get_option('options__felloh_sandbox_private_key');
	} else {
		$baseurl = get_option('options__felloh_live_url');
		$public_key = get_option('options__felloh_live_public_key');
		$private_key = get_option('options__felloh_live_private_key');
	}
	$data = [
		"public_key" => $public_key,
		"private_key" => $private_key
	];
	$method = 'token';
	$url = $baseurl.$method;
	$headers[] = 'Content-Type: application/json';
	$token_response = send_curl($url, "POST", $headers, $data);
	$data = json_decode($token_response,1);
	if($data['data']){
		if (!WC()->session->has_session()) {
		    WC()->session->set_customer_session_cookie(true);
		}
		WC()->session->set('felloh_token', $data['data']['token']); 
		WC()->session->set('felloh_mode', $mode);
	} else {
		$data['call'] = "Get Token";
	}
	return $data;
}

function felloh_create_booking($form_data){
	$mode = get_option('options__felloh_api_mode');
	if (!WC()->session->has_session()) {
	    WC()->session->set_customer_session_cookie(true);
	}
	// pre($mode);
	// pre(WC()->session->get('felloh_mode'));
	if(WC()->session->get('felloh_token') && WC()->session->get('felloh_mode')==$mode){
		$token = WC()->session->get('felloh_token');
	} else {
		$token_data = felloh_get_token();
		if($token_data['data']){
			$token = $token_data['data']['token'];
		} else {
			return $token_data;
		}
	}
	parse_str($form_data,$form_data_Arr);
	$mode = get_option('options__felloh_api_mode');
	if($mode == 'sandbox'){
		$baseurl = get_option('options__felloh_sandbox_url');
		$org_code = get_option('options__felloh_sandbox_org_code');
	} else {
		$baseurl = get_option('options__felloh_live_url');
		$org_code = get_option('options__felloh_live_org_code');
	}


	$method = 'agent/bookings';
	$url = $baseurl.$method;

	$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token,
  ];
  if(isset($form_data_Arr['billing_first_name'])){
  	$data = [
      'organisation' => $org_code,
      'customer_name' => $form_data_Arr['billing_first_name']." ".$form_data_Arr['billing_last_name'],
      'email' => $form_data_Arr['billing_email'],
      'gross_amount' => (int)($form_data_Arr['amount']*100),
      'booking_reference' => $form_data_Arr['order_id']
    ];
  } elseif(isset($form_data_Arr['driver_first_name'])){
  	$data = [
      'organisation' => $org_code,
      'customer_name' => $form_data_Arr['driver_first_name']." ".$form_data_Arr['driver_last_name'],
      'email' => $form_data_Arr['driver_email'],
      'gross_amount' => (int)($form_data_Arr['amount']*100),
      'booking_reference' => $form_data_Arr['custom']
    ];
  } else {
    $data = [
      'organisation' => $org_code,
      'customer_name' => $form_data_Arr['full_name']??$form_data_Arr['first_name']." ".$form_data_Arr['last_name'],
      'email' => $form_data_Arr['email'],
      'booking_reference' => $form_data_Arr['custom'],
      'gross_amount' => (int)($form_data_Arr['amount']*100)
    ];
  }
	
  $booking_response = send_curl($url,'PUT',$headers,$data);
  $booking_data = json_decode($booking_response,1);
  if(!$booking_data['data']){
  	$booking_data['call'] = 'Create Booking';
  }
  return $booking_data;
}

function felloh_create_ecom(){
	$mode = get_option('options__felloh_api_mode');
	$form_data = $_POST['form_data'];
	if (!WC()->session->has_session()) {
	    WC()->session->set_customer_session_cookie(true);
	}
	if(WC()->session->get('felloh_token') && WC()->session->get('felloh_mode')==$mode){
		$token = WC()->session->get('felloh_token');
	} else {
		$token_data = felloh_get_token();
		if($token_data['data']){
			$token = $token_data['data']['token'];
		} else {
			echo json_encode($token_data); exit();
		}
	}
	parse_str($form_data,$form_data_Arr);
	$mode = get_option('options__felloh_api_mode');
	if($mode == 'sandbox'){
		$baseurl = get_option('options__felloh_sandbox_url');
		$org_code = get_option('options__felloh_sandbox_org_code');
		$card_status = (bool)get_option('options__felloh_sandbox_card_enabled');
		$open_banking_status = (bool)get_option('options__felloh_sandbox_open_banking_enabled');
	} else {
		$baseurl = get_option('options__felloh_live_url');
		$org_code = get_option('options__felloh_live_org_code');
		$card_status = (bool)get_option('options__felloh_live_card_enabled');
		$open_banking_status = (bool)get_option('options__felloh_live_open_banking_enabled');
	}

	$method = 'agent/ecommerce';
	$url = $baseurl.$method;

	$headers = [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token,
  ];
  // echo $url;
  if(isset($form_data_Arr['billing_first_name'])){
  	$order_id = create_order_giftcard();
  	$booking_data = felloh_create_booking($form_data."&order_id=".$order_id);

  } else {
  	$booking_data = felloh_create_booking($form_data);
  }
  if($booking_data['data']){
		$booking_id = $booking_data['data']['id'];
	} else {
		echo json_encode($booking_data); exit();
	}

  if(isset($form_data_Arr['billing_first_name'])){
  	$data = [
      'organisation' => $org_code,
      'customer_name' => $form_data_Arr['billing_first_name']." ".$form_data_Arr['billing_last_name'],
      'email' => $form_data_Arr['billing_email'],
      'amount' => (int)($form_data_Arr['amount']*100),
      'booking_id' => $booking_id,
      'booking_reference' => $order_id
    ];
  } elseif(isset($form_data_Arr['driver_first_name'])){
  	$data = [
      'organisation' => $org_code,
      'customer_name' => $form_data_Arr['driver_first_name']." ".$form_data_Arr['driver_last_name'],
      'email' => $form_data_Arr['driver_email'],
      'amount' => (int)($form_data_Arr['amount']*100),
      'booking_id' => $booking_id,
      'booking_reference' => $form_data_Arr['custom']
    ];
  } else {
    $data = [
      'organisation' => $org_code,
      'customer_name' => $form_data_Arr['full_name']??$form_data_Arr['first_name']." ".$form_data_Arr['last_name'],
      'email' => $form_data_Arr['email'],
      'amount' => (int)($form_data_Arr['amount']*100),
      'booking_id' => $booking_id,
      'booking_reference' => $form_data_Arr['custom']
    ];
  }
  $data["open_banking_enabled"] = $open_banking_status;
  $data["card_enabled"] = $card_status;
  // pre($data);
  $ecom_response = send_curl($url,'PUT',$headers,$data);
  // echo json_encode($data); 
  $ecom_data = json_decode($ecom_response,1);
  // pre($ecom_data); exit();
  if(isset($form_data_Arr['billing_first_name'])){
    echo json_encode(['ecom_data' => $ecom_data,'order_id'=>$order_id,'booking_id'=>$booking_id]);
  } else {
  	$ecom_data['call'] = "Create Ecom";
  	$ecom_data['booking_id'] = $booking_id;
  	echo json_encode($ecom_data);
  }
  exit();
}

//***************************************************************************************************************************************
// Function related to TBOH Hotels API Integration


function tbo_request($method,$request_type,$data=array()){
	$api_mode = get_option('options_tboh_api_mode');
	if($api_mode == 'test'){
		$api_url = get_option('options_tboh_test_url');
		$api_username = get_option('options_tboh_test_username');
		$api_password = get_option('options_tboh_test_password');
	} else {
		$api_url = get_option('options_tboh_live_url');
		$api_username = get_option('options_tboh_live_username');
		$api_password = get_option('options_tboh_live_password');
	}

	// Create token for Basic Auth
	$token_string = $api_username.":".$api_password;
	$token = base64_encode($token_string);
	$curl = curl_init();
	curl_setopt_array($curl, array(
	  CURLOPT_URL => $api_url.$method,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => $request_type,
	  CURLOPT_HTTPHEADER => array(
	  	'Content-Type: application/json',
	    'Authorization: Basic '.$token
	  ),
	));
	if($request_type=='POST'){
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
	}
	$response = curl_exec($curl);
	curl_close($curl);
	$data = json_decode($response,1); 
	return $data;
}

function convert_tbo_rating($rating){
	$Rating = 0;
	switch ($rating) {
		case 'OneStar':
			$Rating = 1;
			break;
		case 'TwoStar':
			$Rating = 2;
			break;
		case 'ThreeStar':
			$Rating = 3;
			break;
		case 'FourStar':
			$Rating = 4;
			break;
		case 'FiveStar':
			$Rating = 5;
			break;
		
		default:
			$Rating = 6;
			break;
	}
	return $Rating; 
}

function tbo_download(){
	global $wpdb;

	//City name correction to engligh version
	$diacritic_characters = array(
    'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
    'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
    'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
    'À' => 'A', 'È' => 'E', 'Ì' => 'I', 'Ò' => 'O', 'Ù' => 'U',
    'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u',
    'Â' => 'A', 'Ê' => 'E', 'Î' => 'I', 'Ô' => 'O', 'Û' => 'U',
    'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
    'Ä' => 'A', 'Ë' => 'E', 'Ï' => 'I', 'Ö' => 'O', 'Ü' => 'U',
    'ç' => 'c', 'Ç' => 'C', 'ñ' => 'n', 'Ñ' => 'N',
    'ß' => 'ss',
    'ø' => 'o', 'Ø' => 'O', 'å' => 'a', 'Å' => 'A',
    'æ' => 'ae', 'Æ' => 'AE',
    'œ' => 'oe', 'Œ' => 'OE',
    'ÿ' => 'y', 'Ÿ' => 'Y',
    'ž' => 'z', 'Ž' => 'Z',
    'š' => 's', 'Š' => 'S',
    'ý' => 'y', 'Ý' => 'Y',
    'ł' => 'l', 'Ł' => 'L',
    'ń' => 'n', 'Ń' => 'N',
    'ę' => 'e', 'Ę' => 'E',
    'ą' => 'a', 'Ą' => 'A',
    'ć' => 'c', 'Ć' => 'C',
    'ź' => 'z', 'Ź' => 'Z',
    'ż' => 'z', 'Ż' => 'Z'
	);

	// $hotels = $wpdb->get_results("SELECT * FROM `tbo_hotels`");
	// foreach($hotels as $hotel){
	// 	echo $wpdb->update('tbo_hotels',['city_name'=>strtr($hotel->city_name, $diacritic_characters)],['id'=>$hotel->id]);
	// }

	// exit();

	/*
	$cities = $wpdb->get_results("SELECT * FROM `tbo_cities`");
	foreach($cities as $city){
		$converted_city = strtr($city->city_name, $diacritic_characters);
		// $converted_city = iconv('UTF-8', 'ASCII//TRANSLIT', $city->city_name);

		// pre($converted_city);
// 
		$wpdb->update('tbo_cities',['city_name'=>$converted_city],['id'=>$city->id]);
	}
	exit();
	*/
	// Fetching hotel IDs

	// $codes = tbo_request('hotelcodelist',"GET");

	// $ids = $codes['HotelCodes'];
	// // echo sizeof($ids);
	// $chunked_ids = array_chunk($ids, 10000);
	// // pre($chunked_ids); exit();
	// foreach($chunked_ids as $cids){
	// 	$ids_string = implode("), (",$cids);
	// 	// pre("(".$ids_string.")"); 
	// 	$query = "INSERT INTO `tbo_hotels` (`tboh_code`) VALUES (".$ids_string.")";
	// 	// pre($query);
	// 	$ins = $wpdb->query($query);
	// 	pre($ins);
	// }

	// exit();

	// Downloading Hotels

	// Get First city without hotels info downloaded

	

	$cities = $wpdb->get_results("SELECT * FROM `tbo_cities` WHERE `hotels_data` = 0 LIMIT 50");
	if($cities){
		foreach($cities as $city){
			$data = [
				'CityCode' => $city->city_code,
				'IsDetailedResponse' => false
			];
			$hotels = tbo_request('TBOHotelCodeList',"POST",$data);
			if($hotels['Status']['Code']==200){
				$num_rows = 0;
				$total = sizeof($hotels['Hotels']);
				$db_hotels = $wpdb->get_results("SELECT * FROM `tbo_hotels` WHERE `city_name` = '".$city->city_name."'");
				if($db_hotels){
					$num_rows = $wpdb->num_rows;
				}
				if($num_rows != $total){
					foreach($hotels['Hotels'] as $hotel){
						$check_query = "SELECT * FROM `tbo_hotels` WHERE `tboh_code` = ".$hotel['HotelCode'];
						$check = $wpdb->get_results($check_query);
						if(empty($check)){
							$rec = [
								'tboh_code' => $hotel['HotelCode'],
								'name' => $hotel['HotelName'],
								'address' => $hotel['Address'],
								// 'city_id' => $city->city_code,
								'city_name' => strtr($hotel['CityName'], $diacritic_characters),
								'rating' => convert_tbo_rating($hotel['HotelRating']),
								'country_code' => $hotel['CountryCode'],
								'country_name' => $hotel['CountryName'],
								'map' => $hotel['Latitude']."|".$hotel['Longitude'],
								// 'short_desc' => $hotel['Description'],
								// 'facilities' => json_encode($hotel['HotelFacilities']),
								// 'attractions' => json_encode($hotel['Attractions']),
								// 'images' => $hotel[''],
								// 'pincode' => $hotel['PinCode'],
								// 'phone' => $hotel['PhoneNumber'],
								// 'checkin_time' => $hotel[''],
								// 'checkout_time' => $hotel[''],
							];
							pre($hotel['HotelCode']);
							$ins = $wpdb->insert('tbo_hotels',$rec);
						}
					}
				} 
			} else {
				pre($hotels);
			}
			$upd = $wpdb->update('tbo_cities',['hotels_data'=>1],['city_code'=>$city->city_code]);
			if($upd){
				// pre($city->city_code);
			}
			// exit();
		}
	}	

	// Downloading Countries
	/*
	$data = tbo_request("CountryList",'GET');
	pre($data); exit();
	foreach($data['CountryList'] as $country){
		$rec = [
			'country_code' => $country['Code'],
			'country_name' => $country['Name']
		];
		// echo $wpdb->insert('tbo_countries',$rec);

	}
	*/

	// Downloading Cities
	/*
	$countries = $wpdb->get_results("SELECT * FROM `tbo_countries`");
	// pre($countries); exit();
	if($countries){
		foreach($countries as $country){
			// pre($country); exit();
			$data = tbo_request('CityList','POST',['CountryCode'=>$country->country_code]);
			// pre($data['CityList']); exit();
			foreach($data['CityList'] as $city){
				$rec = [
					'city_code' => $city['Code'],
					'city_name' => $city['Name'],
					'country_code' => $country->country_code,
					'country_name' => $country->country_name
				];
				// pre($rec); exit();
				$wpdb->insert('tbo_cities',$rec);
			}
		}
	}
	*/

}




// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

function add_attraction_to_db($data, $city = '', $tag = '', $destination = '') {
	global $wpdb; $upd = 0;
	if($data){
		foreach($data as $a){
			$rec = [
				'attraction_id' => $a['id'],
		        'title' => $a['title'],
		        'updated' =>  $a['updated'],
		        'dest' => $a['dest'],
		        'price_from_adult' => $a['price_from_adult'],
		        'price_from_child' => $a['price_from_child'],
		        'rrp_adult' => $a['rrp_adult'],
		        'rrp_child' => $a['rrp_child'],
		        'price_from_all' => json_encode($a['price_from_all']),
		        'tickets' =>json_encode($a['tickets']),
		        'pricing_method' => $a['pricing_method'],
		        'seasons' => json_encode($a['seasons']),
		        'url' => $a['url'],
		        'refundable' => $a['refundable'],
		        'on_request' => $a['on_request'],
		        'desc_short' => $a['desc_short'],
		        'img_sml' => $a['img_sml'],
		        'city_name' => $city,
		        'tag' => $tag,
		        'destination' => $destination
			];
			$check = $wpdb->get_results("SELECT * FROM `attractions_data` WHERE attraction_id = ".$a['id']);
			// echo "<pre>"; print_r($destination); exit();
			// if(isset($check[0]))
			// {
			// print_r($check);
				$check = $check[0];
			// }
			if($check){
				$rec['city_name'] = $check->city_name!=''?$check->city_name:$city;
				$rec['tag'] = $check->tag!=''?$check->tag:$tag;
				$rec['destination'] = $check->destination!=''?$check->destination:$destination;
				// print_r($rec); exit();

				$upd = $wpdb->update('attractions_data',$rec,['id'=>$check->id]);
			} else {
				$upd = $wpdb->insert('attractions_data',$rec);
			}
		} 
		// $upd = $wpdb->update('travellanda_cities',['attractions'=>1],['id'=>$city->id]);
	}
	return $upd;
}

function sync_attractions() {
	set_time_limit(0);
	ini_set('max_execution_time',0);
	global $wpdb;

	$tags = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."attraction_tags`");
	if($tags) {
		// echo "<pre>"; print_r($tags); exit();
		foreach($tags as $t) {
			$response = wp_remote_get(
				'https://phx.dosomethingdifferent.com/api/products?tags=' . $t->Tag ."&view=extended",
				array(
					'timeout'     => 120,
				)
			);
			$res = wp_remote_retrieve_body($response);
			$attr = json_decode($res, true);
			// echo 'https://phx.dosomethingdifferent.com/api/products?tags=' . $t->Tag ."&view=extended";
			// exit();
			if(isset($attr['data'])){

				add_attraction_to_db($attr['data'], '', $t->Tag);
			}  
		}
	}

	// exit();

	$destinations = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix."attraction_destinations`");
	if($destinations){
	    foreach($destinations as $des) {
		   $response = wp_remote_get(
				'https://phx.dosomethingdifferent.com/api/products?dest=' . str_replace(' ', '_', $des->Title)."&view=extended",
				array(
					'timeout'     => 120,
				)
			);
			$res = wp_remote_retrieve_body($response);
			$attr = json_decode($res, true);
			if(isset($attr['data'])){
				add_attraction_to_db($attr['data'], '', '', $des->Title);
			}  
	    }

	}

	// exit();


	$cities = $wpdb->get_results("SELECT * FROM `travellanda_cities` WHERE attractions < 2");
	if($cities){
		foreach($cities as $city){
			$response = wp_remote_get(
				'https://phx.dosomethingdifferent.com/api/products?title=' . str_replace(' ', '_', $city->city_name)."&view=extended",
				array(
					'timeout'     => 120,
				)
			);
			$res = wp_remote_retrieve_body($response);
			$attr = json_decode($res, true);
			if(isset($attr['data'])){
				add_attraction_to_db($attr['data'],$city->city_name);
			}
		}
	}
}
function data_sync()
{
	global $wpdb;
	print_r($_SESSION); exit();
	require_once GCTCF_PATH . '/includes/Travellanda.class.php';
	$travellanda_settings = get_travellanda_api_config();
	$travellanda = new Travellanda();
	$travellanda->setUsername($travellanda_settings['user']);
	$travellanda->setPassword($travellanda_settings['pass']);
	$travellanda->setMode($travellanda_settings['mode']);

	$countries_sql = "SELECT country_code FROM `travellanda_countries`";
	$countries = $wpdb->get_results($countries_sql);
	$hotels = [];
	$i = 0;
	if($countries){
		foreach($countries as $c){
			$hotels_data = $travellanda->getHotelsAll($c->country_code);
			if($hotels_data['body']){
				$hotels_xml = simplexml_load_string($hotels_data['body']);
			    $json = json_encode($hotels_xml);
			    $hotels = json_decode($json,1)['Body']['Hotels'];
			    if($hotels){
					foreach($hotels['Hotel'] as $h){
						$check = $wpdb->get_results("SELECT * FROM `travellanda_hotels` WHERE hotel_id=".$h['HotelId']);
						if(!$check){
							$wpdb->insert("travellanda_hotels",['hotel_id' => $h['HotelId'],'city_id' => $h['CityId'],'hotel_name' => $h['HotelName']]);
						}
					}
				}
			}
		}
	}

	
    echo '<pre>' . print_r($dets['Body']['Hotels']['Hotel']) . '</pre>'; exit();

}

function update_hotel_details()
{
	set_time_limit(0);
	ini_set('max_execution_time',0);
	global $wpdb;
	require_once GCTCF_PATH . '/includes/Travellanda.class.php';
	$travellanda_settings = get_travellanda_api_config();
	$travellanda = new Travellanda();
	$travellanda->setUsername($travellanda_settings['user']);
	$travellanda->setPassword($travellanda_settings['pass']);
	$travellanda->setMode($travellanda_settings['mode']);

	$Hotels = $wpdb->get_results("SELECT * FROM `travellanda_hotels` WHERE hotel_details=0");
	// echo "<pre>"; print_r($Hotels); exit();
	if($Hotels) {
		$ids = [];
		foreach($Hotels as $H) {
			$check = $wpdb->get_results("SELECT * FROM `travellanda_hotel_details` WHERE hotel_id=".$H->hotel_id);
			if(!$check){
				$ids[]=$H->hotel_id;
				if(sizeof($ids)==100){
		    		$details = $travellanda->getHotelDetails($ids);
		    		$ids = [];
		    		$simple_xml2 = simplexml_load_string($details['body']);
		    		$json2 = json_encode($simple_xml2);
		    		$detail = json_decode($json2,1);
		    		$hotel_details = $detail['Body']['Hotels']['Hotel'];
		    		foreach($hotel_details as $dets){
			    		$rec = [
			    			'hotel_id' => $dets['HotelId'],
			    			'hotel_name' => $dets['HotelName'],
							'city_id' => $dets['CityId'],
							'star_rating' => $dets['StarRating'],
							'latitude' => $dets['Latitude'],
							'longitude' => $dets['Longitude'], 
							'address' => $dets['Address'],
							'location' => json_encode($dets['Location']),
							'phone' => $dets['PhoneNumber'],
							'description' => json_encode($dets['Description']),
							'facilities' => json_encode($dets['Facilities']),
							'images' => json_encode($dets['Images']),
			    		];
		    			$wpdb->insert("travellanda_hotel_details",$rec);
		    			$wpdb->update("travellanda_hotels",['hotel_details'=>1],['id'=>$H->id]);
		    		}
				}
			} else {
				$wpdb->update("travellanda_hotels",['hotel_details'=>1],['id'=>$H->id]);
			}
		}
	}
}

function gctcf_hotel_search()
{
	global $wpdb;
	$html = '';
	// Fetching Stuba Results
	$hotel_region_list = sanitize_text_field($_POST['hotel_region_list']);
	$hotel_region_aql = "SELECT region_id, region_name, country_name FROM `hotel_region` WHERE `region_name` LIKE '%$hotel_region_list%' ORDER BY `id` DESC LIMIT 0,10";
	$hotel_region_array = array();
	$hotel_region_array = $wpdb->get_results($hotel_region_aql);

	// pre($hotel_region_array); 
	//Fetching TBO results
	$tbo_query = "SELECT * FROM `tbo_cities` WHERE `city_name` LIKE '%$hotel_region_list%' ORDER BY `id` DESC LIMIT 0,10";
	$tbo_cities = array();
	$tbo_cities = $wpdb->get_results($tbo_query);
	// pre($tbo_cities); 

	$travellanda_results = array();
	$stuba_results = array();
	// $tbo_results = array();

	require_once GCTCF_PATH . '/includes/Travellanda.class.php';

	$travellanda_settings = get_travellanda_api_config();

	$travellanda = new Travellanda();
	$travellanda->setUsername($travellanda_settings['user']);
	$travellanda->setPassword($travellanda_settings['pass']);
	$travellanda->setMode($travellanda_settings['mode']);

	$travellanda_results = $travellanda->citySearch($hotel_region_list);
	$merged_results = array();

	// Formatting Stuba results
	foreach ($hotel_region_array as $hotel_region_name) {
		$code = strtolower(trim($hotel_region_name->country_name)) . '_' . strtolower(trim($hotel_region_name->region_name));
		$stuba_results[$code]['country_name'] = trim($hotel_region_name->country_name);
		$stuba_results[$code]['country_code'] = '';
		$stuba_results[$code]['city_name'] = trim($hotel_region_name->region_name);
		$stuba_results[$code]['ids']['stuba'][] = trim($hotel_region_name->region_id);
	}

	//Formatting TBO results
	foreach ($tbo_cities as $tbo_city) {
		$code = strtolower(trim($tbo_city->country_name)) . '_' . strtolower(trim($tbo_city->city_name));
		$tbo_results[$code]['country_name'] = trim($tbo_city->country_name);
		$tbo_results[$code]['country_code'] = trim($tbo_city->country_code);
		$tbo_results[$code]['city_name'] = trim($tbo_city->city_name);
		$tbo_results[$code]['ids']['tbo'][] = trim($tbo_city->city_code);
	}
	// pre($tbo_results); exit();
	//Formatting Travellanda Results

	foreach ($travellanda_results as $code => $result) {
		$merged_results[$code]['country_code'] = $result['country_code'];
		$merged_results[$code]['country_name'] = $result['country_name'];
		$merged_results[$code]['city_name'] = $result['city_name'];
		$merged_results[$code]['ids']['travellanda'] = $result['ids'];
		$merged_results[$code]['ids']['stuba'] = array();
		$merged_results[$code]['ids']['tbo'] = array();

	}

	$usa = ['USA', 'US', 'United States'];
	$uk = ['UK', 'United Kingdom'];
	// foreach ($stuba_results as $code => $result) {
	// 	if (strpos($code, 'uk -') !== false) {
	// 		$exploded = explode('_', $code);
	// 		foreach ($merged_results as $merged_code => $merged_result) {
	// 			$merged_exploded = explode('_', $merged_code);
	// 			//compare city name
	// 			if ( (string) $exploded[1] == (string) $merged_exploded[1] && (string)$merged_exploded[0] == 'uk' ) {
	// 				$merged_results[$code] = array(
	// 					'country_code' => $merged_result['country_code'],
	// 					'country_name' => $merged_result['country_name'],
	// 					'city_name' => $merged_result['city_name'],
	// 					'ids' => array(
	// 						'travellanda' => $merged_result['ids']['travellanda'],
	// 						'stuba' => array(),
	// 					)
	// 				);
	// 				unset($merged_results[$merged_code]);
	// 			}
	// 		}
	// 	}
	// 	$merged_results[$code]['country_code'] = (isset($merged_results[$code]['country_code'])) ? $merged_results[$code]['country_code'] : $result['country_code'];
	// 	$merged_results[$code]['country_name'] = $result['country_name'];
	// 	$merged_results[$code]['city_name'] = $result['city_name'];
	// 	$merged_results[$code]['ids']['travellanda'] = (isset($merged_results[$code]['ids']['travellanda'])) ? $merged_results[$code]['ids']['travellanda'] : array();
	// 	$merged_results[$code]['ids']['stuba'] = $result['ids'];
	// }
	// $merged_results1 = array_merge($stuba_results,$merged_results);

	// foreach ($stuba_results as $code => $result) {
	// 	foreach ($merged_results as $merged_code => $merged_result) {
	// 		if($result['city_name'] == $merged_result['city_name'] && ( $result['country_name'] == $merged_result['country_name'] || (in_array($result['country_name'], $usa) && in_array($merged_result['country_name'], $usa) ) || ((in_array($result['country_name'] , $uk) || strpos(strtolower($result['country_name']), 'uk - ')) && (in_array($merged_result['country_name'] , $uk) || strpos(strtolower($merged_result['country_name']), 'uk - ')))) {
	// 			$merged_results[$merged_code]['ids']['stuba'] = $result['ids'];
	// 		} else {
	// 			$merged_results[$code] = $result;
	// 		}
	// 	}
	// }

	foreach ($stuba_results as $code => $result) {
    $foundMatch = false;

	    foreach ($merged_results as $merged_code => $merged_result) {
	        if (
	            $result['city_name'] == $merged_result['city_name'] &&
	            (
	                $result['country_name'] == $merged_result['country_name'] ||
	                (in_array($result['country_name'], $usa) && in_array($merged_result['country_name'], $usa)) ||
	                (
	                    (in_array($result['country_name'], $uk) || str_starts_with(strtolower($result['country_name']), 'uk - ')) &&
	                    (in_array($merged_result['country_name'], $uk) || str_starts_with(strtolower($merged_result['country_name']), 'uk - '))
	                )
	            )
	        ) {
	            $merged_results[$merged_code]['ids']['stuba'] = $result['ids']['stuba'];
	            $foundMatch = true;
	            break; // Exit the inner loop once a match is found
	        }
	    }

	    if (!$foundMatch) {
	        // If no match was found in the inner loop, add the result to $merged_results
	        $merged_results[$code] = $result;
	    }
	}

	// pre($merged_results); exit();

	foreach ($tbo_results as $tbo_code => $tbo_result) {
    $foundMatch = false;

	    foreach ($merged_results as $merged_code => $merged_result) {
	        if (
	            $tbo_result['city_name'] == $merged_result['city_name'] &&
	            (
	                $tbo_result['country_name'] == $merged_result['country_name'] ||
	                (in_array($tbo_result['country_name'], $usa) && in_array($merged_result['country_name'], $usa)) ||
	                (
	                    (in_array($tbo_result['country_name'], $uk) || str_starts_with(strtolower($tbo_result['country_name']), 'uk - ')) &&
	                    (in_array($merged_result['country_name'], $uk) || str_starts_with(strtolower($merged_result['country_name']), 'uk - '))
	                )
	            )
	        ) {
	            $merged_results[$merged_code]['ids']['tbo'] = $tbo_result['ids']['tbo'];
	            $foundMatch = true;
	            break; // Exit the inner loop once a match is found
	        }
	    }

	    if (!$foundMatch) {
	        // If no match was found in the inner loop, add the result to $merged_results
	        $merged_results[$tbo_code] = $tbo_result;
	    }
	}

		// 	$merged_exploded = explode('_', $merged_code);
		// 	//compare city name
		// 	if ( (string) $exploded[1] == (string) $merged_exploded[1] && (string)$merged_exploded[0] == 'uk' ) {
		// 		$merged_results[$code] = array(
		// 			'country_code' => $merged_result['country_code'],
		// 			'country_name' => $merged_result['country_name'],
		// 			'city_name' => $merged_result['city_name'],
		// 			'ids' => array(
		// 				'travellanda' => $merged_result['ids']['travellanda'],
		// 				'stuba' => array(),
		// 			)
		// 		);
		// 		unset($merged_results[$merged_code]);
		// 	}
		// $merged_results[$code]['country_code'] = (isset($merged_results[$code]['country_code'])) ? $merged_results[$code]['country_code'] : $result['country_code'];
		// $merged_results[$code]['country_name'] = $result['country_name'];
		// $merged_results[$code]['city_name'] = $result['city_name'];
		// $merged_results[$code]['ids']['travellanda'] = (isset($merged_results[$code]['ids']['travellanda'])) ? $merged_results[$code]['ids']['travellanda'] : array();
		// $merged_results[$code]['ids']['stuba'] = $result['ids'];



	// pre($merged_results, 1); exit();



	$html .= '<ul id="hotel_result_ul">';
	$count = 1;

	foreach ($merged_results as $code => $merged_result) {
		$html .= '<li data-country-code="' . strtolower($merged_result['country_code']) . '" data-region-ids="' . htmlentities(json_encode($merged_result['ids'])) . '" data-value="' . $merged_result['city_name'] . '--' . $merged_result['country_name'] . '" id="region-id-id-' . $count . '" class="region_id_id" value="' . $code . '">' . $merged_result['city_name'] . '--' . $merged_result['country_name'] . '<input type="hidden" name="hotel__region__code" id="hotel--region--code" class="" value="' . htmlentities(json_encode($merged_result['ids'])) . '" /><input type="hidden" name="hotel__region__name" id="hotel__region__name" class="region-id-id-' . $count . '-region_name" value="' . $merged_result['city_name'] . '" /><input type="hidden" name="hotel_region_country" id="hotel_region_country" class="region-id-id-' . $count . '-country" value="' . $merged_result['country_name'] . '" /></li>';

		$count++;
	}
	$html .= '</ul>';


	echo $html;
	exit();
}

add_action('wp_ajax_gctc_fetch_tour', 'gctc_fetch_tour');
add_action('wp_ajax_nopriv_gctc_fetch_tour', 'gctc_fetch_tour');
function gctc_fetch_tour()
{
	$errors = array(
		'not_found' => 'Tour not found',
	);
	if (!isset($_POST['tour_id']) || $_POST['tour_id'] <= 0) {
		echo json_encode(array('res' => 'error', 'message' => __('No tour found', 'gctcf')));
		exit();
	}
	$post_id = $_POST['tour_id'];
	$tour = get_post($post_id);
	if (empty($tour)) {
		echo json_encode(array('res' => 'error', 'message' => __('No tour found', 'gctcf')));
		exit();
	}
	$img = GCTCF_URL . 'public/images/hotel-placeholder-img.png';
	if (get_the_post_thumbnail_url($post_id)) {
		$img = get_the_post_thumbnail_url($post_id);
	}

	$data = array(
		'res' => 'suucess',
		'tour_id' => $tour->ID,
		'post_content' => apply_filters('the_content', $tour->post_content),
		'post_title' => $tour->post_title,
		'images' => $img,
		'sub_heading' => get_post_meta($tour->ID, 'gc4t_duration', true),
	);
	echo json_encode($data);
	exit();
}

add_action('wp_ajax_gc4t_submit_tour_enquiry', 'gc4t_submit_tour_enquiry');
add_action('wp_ajax_nopriv_gc4t_submit_tour_enquiry', 'gc4t_submit_tour_enquiry');

function gc4t_submit_tour_enquiry()
{
	$errors = array();

	$tour_id = sanitize_text_field($_POST['tour_id']);
	$tour_name = sanitize_text_field($_POST['tour_name']);
	$tour_email = sanitize_text_field($_POST['tour_email']);
	$tour_contact_number = sanitize_text_field($_POST['tour_contact_number']);
	$tour_booking_date = sanitize_text_field($_POST['tour_booking_date']);
	$tour_booking_date_alt = sanitize_text_field($_POST['tour_booking_date_alt']);
	$tour_adults = sanitize_text_field($_POST['tour_adults']);
	$tour_children = sanitize_text_field($_POST['tour_children']);
	$tour_adults = (is_numeric($tour_adults)) ? $tour_adults : 0;
	$tour_children = (is_numeric($tour_children)) ? $tour_children : 0;
	$tour_message = esc_textarea($_POST['tour_message']);

	if (!is_numeric($tour_id)) {
		$errors['tour_id'] = 'Please select a valid tour';
	}
	if (empty($tour_name)) {
		$errors['tour_name'] = 'Please enter your name';
	}
	if (!filter_var($tour_email, FILTER_VALIDATE_EMAIL)) {
		$errors['tour_email'] = 'Please enter a valid email';
	}
	if (empty($tour_contact_number)) {
		$errors['tour_contact_number'] = 'Please enter a contact number';
	}
	if (empty($tour_booking_date)) {
		$errors['tour_booking_date'] = 'Please select a booking date';
	}
	if (empty($tour_booking_date_alt)) {
		$errors['tour_booking_date_alt'] = 'Please select an alternative booking date';
	}
	if ($tour_adults == 0 && $tour_children == 0) {
		$errors['tour_adults'] = 'Please enter number of people to book for';
	}

	if (empty($tour_message)) {
		$errors['tour_message'] = 'Please enter a message';
	}

	if (!empty($errors)) {

		echo json_encode(array('res' => 'error', 'data' => $errors));
		exit();
	}

	$tour = get_post($tour_id);
	$link = get_permalink($tour->ID);
	$message = "\r\n\r\n";
	$message .= "Tour: {$tour->post_title}\r\n";
	$message .= "Link: $link\r\n";
	$message .= "Name: $tour_name\r\n";
	$message .= "Email: $tour_email\r\n";
	$message .= "Contact Number: $tour_contact_number\r\n";
	$message .= "Booking Date: $tour_booking_date\r\n";
	$message .= "Booking Date (Alt.): $tour_booking_date_alt\r\n";
	$message .= "Adults: $tour_adults\r\n";
	$message .= "Children: $tour_children\r\n";
	$message .= "Message:\r\n";
	$message .= $tour_message;
	$email_content .= '<!DOCTYPE html>
						<html>
						<head>
						<title>Booking Email</title>
						<meta charset="UTF-8">
						<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
						</head>
						<body class="booking-email-block" style="padding: 0px; margin: 0px;">
						<table width="100%" style="max-width: 1200px; margin: 0 auto; padding: 15px; font-family:Arial, Helvetica, sans-serif; border:1px solid #061d2f; color:#061d2f" cellpadding="0" cellspacing="0">
						<tr>
							<td colspan="5" style="padding-top:0px;"><h2 style="font-size: 16px; font-weight: 700;background-color: #fad38f; padding: 10px 15px; text-align: left; margin: 0px;font-family: sans-serif; color: #061d2f; text-transform: uppercase;">A tour enquiry was submitted on Giftcards4travel.co.uk</h2></td>
						</tr>
						<tr>
							<td width="25%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Tour</strong></td>
							<td width="25%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left;  padding:5px;padding-top: 20px;"><strong>Link</strong></td>
							<td width="25%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"> <strong>Name</strong></td>
							<td width="25%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"> <strong>Email</strong></td>
						</tr>
						<tr>
							<td align="top" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">' . $tour->post_title . '</td>
							<td align="top" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">' . $link . '</td>
							<td align="top" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">' . $tour_name . '</td>
							<td align="top" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">' . $tour_email . '</td>
						</tr>
						<tr>
							<td width="25%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"> <strong>Contact Number</strong></td>
							<td width="25%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Booking Date</strong></td>
							<td width="25%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Booking Date (Alt.)</strong></td>
							<td width="25%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Adults</strong></td>
						</tr>
						<tr>
							<td style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">' . $tour_contact_number . '</td>
							<td style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">' . $tour_booking_date . '</td>
							<td style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">' . $tour_booking_date_alt . '</td>
							<td style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">' . $tour_adults . '</td>
						</tr>
						<tr>
							<td width="25%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"> <strong>Children</strong></td>
						</tr>
						<tr>
							<td style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">' . $tour_children . '</td>
						</tr>
						<tr>
							<td colspan="5" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px;padding-top: 20px;"><strong>Message:-</strong></td>
						</tr>
						<tr>
							<td colspan="5" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">' . $tour_message . '</td>
						</tr>
						</table>
						</body>
						</html>';
	$headers = array('Content-Type: text/html; charset=UTF-8');
	wp_mail('bookings@giftcards4travel.co.uk', 'New Tour Enquiry Submitted', $email_content, $headers);
	// wp_mail('developersuseonly@gmail.com', 'New Tour Enquiry Submitted', $email_content, $headers);

	echo json_encode(array('res' => 'success', 'message' => 'Thank you for your enquiry'));
	exit();
}

//Hotel quick view ajax
add_action('wp_ajax_gctc_hotel_quick_view', 'gctc_hotel_quick_view');
add_action('wp_ajax_nopriv_gctc_hotel_quick_view', 'gctc_hotel_quick_view');

function gctc_hotel_quick_view()
{

	if (!isset($_POST['hotel__id']) || $_POST['hotel__id'] <= 0 || !isset($_POST['feed']) || empty($_POST['feed'])) {
		echo json_encode(array('res' => 'error', 'message' => __('No hotel found', 'gctcf')));
		exit();
	}
	$hotel_id = $_POST['hotel__id'];
	$feed = $_POST['feed'];
	$html = '';
	ob_start();
	include_once(GCTCF_PATH . 'public/partials/gctc-hotel-quick-view.php');
	$html .= ob_get_clean();

	echo json_encode(array('res' => 'success', 'html' => $html));
	exit();
}

//coupon search ajax
add_action('wp_ajax_gctc_coupon_search', 'gctc_coupon_search');
add_action('wp_ajax_nopriv_gctc_coupon_search', 'gctc_coupon_search');

function gctc_coupon_search()
{

	global $wpdb;
	$amount = 0;
	if (isset($_POST['quote_id'])) {
		$quote_id = sanitize_text_field($_POST['quote_id']);
		$post_id = sanitize_text_field($_POST['post_id']);
		update_post_meta($post_id, '_gctcf_coupon_checked', '');
		if (isset($_POST['feed_type'])) {

			if ($_POST['feed_type'] == 'stuba') {
				$wpdb->query(
					"UPDATE user_login_table SET discount_coupon_code = '' WHERE hotel_quote_id = '$quote_id'"
				);
			}
			if ($_POST['feed_type'] == 'travellanda') {
				update_post_meta($quote_id, 'gc4t_discount_code', '');
			}
		}

		if (!empty($_POST['coupon_code_search'])) {
			$coupon_code = sanitize_text_field($_POST['coupon_code_search']);
			update_post_meta($post_id, '_gctcf_coupon_checked', $coupon_code);
			$discount_type = 'amount';
			$coupon_wp = new WC_Coupon($coupon_code);
			if ($coupon_wp->get_discount_type() == 'percent') {
				$discount_type = 'percent';
			}
			$amount = $coupon_wp->get_amount();
			if (!$amount) {
				$physical_vouchers = get_posts(array(
					'post_type' => 'gc4t_store_voucher',
					'post_status' => 'private',
					'posts_per_page' => 1,
					'meta_query' => array(
						array(
							'key' => 'gc4t_voucher_status',
							'value' => 'active',
						),
						array(
							'key' => 'gc4t_voucher_code',
							'value' => $coupon_code,
						),
					)
				));
				$booking_detail = get_post_meta($post_id, 'gc4t_booking_id', true);
				update_post_meta($post_id, '_gctcf_booking_amount', $_POST['total_price']);
				if (isset($physical_vouchers[0])) {
					$amount = get_post_meta($physical_vouchers[0]->ID, 'gc4t_voucher_amount_remaining', true);
					if ($booking_detail == 0) {
						update_post_meta($post_id, '_gctcf_coupon_amount', $amount);
						update_post_meta($post_id, '_gctcf_coupon_code', $coupon_code);
					}
				} else {
					if ($booking_detail == 0) {
						update_post_meta($post_id, '_gctcf_coupon_amount', '');
						update_post_meta($post_id, '_gctcf_coupon_code', '');
					}
				}
				$total_price = sanitize_text_field($_POST['total_price']);
				$total_price = ($total_price) ? $total_price : 0;
				if ($total_price) {
					$difference = $total_price - $amount;
					if ($difference < 0) {
						$amount = $total_price - 1;
					}
				}
			}
			if ($amount) {
				if (isset($_POST['quote_id'])) {
					$quote_id = sanitize_text_field($_POST['quote_id']);
					if (isset($_POST['feed_type'])) {
						if ($_POST['feed_type'] == 'stuba') {
							$wpdb->query(
								"UPDATE user_login_table SET discount_coupon_code = '$coupon_code' WHERE hotel_quote_id = '$quote_id'"
							);
						}

						if ($_POST['feed_type'] == 'travellanda') {
							update_post_meta($post_id, 'gc4t_discount_code', $coupon_code);
						}
					}
				}
			}
		}
		if ($discount_type == 'percent') {
			$total_price = sanitize_text_field($_POST['total_price']);
			$total_price = ($total_price) ? $total_price : 0;
			if ($total_price) {
				$discount_amount = $total_price / 100 * $amount;
				$amount = sprintf('%01.2f', $discount_amount);
			}
		}
		echo $amount;
	}
	exit;
}


//  Ajax transfer departure location search ajax
add_action('wp_ajax_gctcf_hotel_search_transfer', 'gctcf_hotel_search_transfer');
add_action('wp_ajax_nopriv_gctcf_hotel_search_transfer', 'gctcf_hotel_search_transfer');

function gctcf_hotel_search_transfer()
{
	$hotel_list = $_POST['hotel_name_list'];
	$location_type = $_POST['location_type'];
	//echo $hotel_region_list='kol';
	global $wpdb;
	if ($location_type == 'AP') {

		$hotel_name_aql = "SELECT * FROM `transfers_for_hotel_list` WHERE `location_type`='RT' AND `location_name` LIKE '%$hotel_list%' ORDER BY `id` DESC LIMIT 0,10";
	} elseif ($location_type == 'RT') {

		$hotel_name_aql = "SELECT * FROM `transfers_for_hotel_list` WHERE `location_type`='AP' AND `location_name` LIKE '%$hotel_list%' ORDER BY `id` DESC LIMIT 0,10";
	} else {

		$hotel_name_aql = "SELECT * FROM `transfers_for_hotel_list` WHERE `location_name` LIKE '%$hotel_list%' ORDER BY `id` DESC LIMIT 0,15";
	}

	$hotel_name_array = $wpdb->get_results($hotel_name_aql);
	ob_start();
?>
	<ul id="hotel_result_ul">
		<?php
		$count = 1;
		if ($hotel_name_array) {
			foreach ($hotel_name_array as $hotel_location_name) {
		?>
				<li style="font-size: 14px;cursor: pointer;padding: 5px;border-bottom: 1px solid #fff;list-style:none;" data-value="<?php echo $hotel_location_name->location_name; ?>" id="city-name-by-name_<?php echo $count; ?>" class="hotel_id_id"> <?php echo $hotel_location_name->location_code . '-' . $hotel_location_name->location_name; ?>
					<input type="hidden" value="<?php echo $hotel_location_name->location_code; ?>" name="city_name_by_hidden" id="hotel_code_name" class="city-name-by-name_<?php echo $count; ?>_iata" />
					<input type="hidden" value="<?php echo $hotel_location_name->location_latitude; ?>" name="hotel_name_by_latitude" id="hotel_latitude" class="city-name-by-name_<?php echo $count; ?>_latitude" />
					<input type="hidden" value="<?php echo $hotel_location_name->location_longitude; ?>" name="hotel_name_by_longitude" id="hotel_longitude" class="city-name-by-name_<?php echo $count; ?>_longitude" />
					<input type="hidden" value="<?php echo $hotel_location_name->location_type; ?>" name="hotel_location_by_type" id="hotel_location_by_type" class="city-name-by-name_<?php echo $count; ?>_ltype" />
				</li>
			<?php
				$count++;
			}
		} else {
			?>
			<li style="font-size: 14px;cursor: pointer;padding: 5px;border-bottom: 1px solid #fff;list-style:none;" class="hotel_id_id">
				<?php esc_attr_e('No result found!', 'gctcf'); ?>
			</li>
		<?php
		}
		?>
	</ul>
<?php
	$data = ob_get_clean();
	wp_send_json_success($data);
	// echo $data;
}


//  Ajax transfer arrival location search ajax
add_action('wp_ajax_gctcf_hotel_search_arrival', 'gctcf_hotel_search_arrival');
add_action('wp_ajax_nopriv_gctcf_hotel_search_arrival', 'gctcf_hotel_search_arrival');

function gctcf_hotel_search_arrival()
{
	$hotel_list = $_POST['hotel_name_list'];
	$location_type = $_POST['location_type'];
	//echo $hotel_region_list='kol';
	global $wpdb;
	if ($location_type == 'AP') {

		$hotel_name_aql = "SELECT * FROM `transfers_for_hotel_list` WHERE `location_name` LIKE '%$hotel_list%' ORDER BY `id` DESC LIMIT 0,15";
		//$hotel_name_aql="SELECT * FROM `transfers_for_hotel_list` WHERE `location_type`='RT' AND `location_name` LIKE '%$hotel_list%' ORDER BY `id` DESC LIMIT 0,10";

	} elseif ($location_type == 'RT') {

		$hotel_name_aql = "SELECT * FROM `transfers_for_hotel_list` WHERE `location_type`='AP' AND `location_name` LIKE '%$hotel_list%' ORDER BY `id` DESC LIMIT 0,10";
	} else {

		$hotel_name_aql = "SELECT * FROM `transfers_for_hotel_list` WHERE `location_name` LIKE '%$hotel_list%' ORDER BY `id` DESC LIMIT 0,15";
	}

	$hotel_name_array = $wpdb->get_results($hotel_name_aql);
	ob_start();
?>
	<ul id="hotel_result_ul">
		<?php
		$count = 1;
		if ($hotel_name_array) {
			foreach ($hotel_name_array as $hotel_location_name) {
		?>
				<li style="font-size: 14px;
		cursor: pointer;
		padding: 5px;
		border-bottom: 1px solid #fff;
		list-style:none;" data-value="<?php echo $hotel_location_name->location_name; ?>" id="location-by-hotel_<?php echo $count; ?>" class="hotel_id_by_name"> <?php echo $hotel_location_name->location_code . '-' . $hotel_location_name->location_name; ?>
					<input type="hidden" value="<?php echo $hotel_location_name->location_code; ?>" name="city_name_by_hidden" id="hotel_code_name" class="location-by-hotel_<?php echo $count; ?>_iata" />

					<input type="hidden" value="<?php echo $hotel_location_name->location_latitude; ?>" name="hotel_name_by_latitude" id="hotel_latitude" class="location-by-hotel_<?php echo $count; ?>_latitude" />

					<input type="hidden" value="<?php echo $hotel_location_name->location_longitude; ?>" name="hotel_name_by_longitude" id="hotel_longitude" class="location-by-hotel_<?php echo $count; ?>_longitude" />

					<input type="hidden" value="<?php echo $hotel_location_name->location_type; ?>" name="hotel_location_by_type" id="hotel_location_by_type" class="location-by-hotel_<?php echo $count; ?>_ltype" />

				</li>
			<?php
				$count++;
			}
		} else {
			?>
			<li style="font-size: 14px;cursor: pointer;padding: 5px;border-bottom: 1px solid #fff;list-style:none;" class="hotel_id_id">
				<?php esc_attr_e('No result found!', 'gctcf'); ?>
			</li>
		<?php
		}
		?>
	</ul>
<?php
	$data = ob_get_clean();
	wp_send_json_success($data);
	// echo $data;
}

//  Ajax for country search
add_action('wp_ajax_gctcf_get_countries', 'gctcf_get_countries');
add_action('wp_ajax_nopriv_gctcf_get_countries', 'gctcf_get_countries');
function gctcf_get_countries()
{
	$cars_country_list = $_POST['cars_country_name'];
	global $wpdb;
	$html = '';
	$car_country_aql = "SELECT * FROM `countries` WHERE `name` LIKE '%$cars_country_list%' ORDER BY `id` DESC LIMIT 0,20";
	$car_country_array = $wpdb->get_results($car_country_aql);
	if ($car_country_array) {
		$html .= '<ul id="country_result_ul">';

		foreach ($car_country_array as $car_country_name) {
			$html .= '<li data-value="' . $car_country_name->sortname . '" class="country_id_id">' . $car_country_name->name . '<input type="hidden" value="' . $car_country_name->sortname . '" name="city_name_by_hidden" id="country-chort-name" /></li>';
		}
		$html .= '</ul>';
	}
	echo $html;
	exit();
}

//coupon search ajax
add_action('wp_ajax_gctc_transfer_coupon_search', 'gctc_transfer_coupon_search');
add_action('wp_ajax_nopriv_gctc_transfer_coupon_search', 'gctc_transfer_coupon_search');

function gctc_transfer_coupon_search()
{


	global $wpdb;
	$amount = 0;
	if (!empty($_POST['coupon_code_search'])) {
		$coupon_code = sanitize_text_field($_POST['coupon_code_search']);
		$coupon_wp = new WC_Coupon($coupon_code);

		$amount = $coupon_wp->get_amount();
		if (!$amount) {
			$physical_vouchers = get_posts(array(
				'post_type' => 'gc4t_store_voucher',
				'post_status' => 'private',
				'posts_per_page' => 1,
				'meta_query' => array(
					array(
						'key' => 'gc4t_voucher_status',
						'value' => 'active',
					),
					array(
						'key' => 'gc4t_voucher_code',
						'value' => $coupon_code,
					),
				)
			));
			if (isset($physical_vouchers[0])) {
				$amount = get_post_meta($physical_vouchers[0]->ID, 'gc4t_voucher_amount_remaining', true);
			}
			$total_price = sanitize_text_field($_POST['total_price']);
			$total_price = ($total_price) ? $total_price : 0;
			if ($total_price) {
				$difference = $total_price - $amount;
				if ($difference < 0) {
					$amount = $total_price - 1;
				}
			}
		}
	}
	echo $amount;
	exit;
}

//attractions search ajax
add_action('wp_ajax_gctcf_attractions_search', 'gctcf_attractions_search');
add_action('wp_ajax_nopriv_gctcf_attractions_search', 'gctcf_attractions_search');

function gctcf_attractions_search()
{
	global $wpdb;

	if (!empty($_POST['title'])) {
	    $title = $_POST['title'];
	    $result = $wpdb->get_results("SELECT * FROM `attractions_data` WHERE `city_name` LIKE '%$title%'  OR `dest` LIKE '%$title%'  OR `destination` LIKE '%$title%'  OR `title` LIKE '%$title%'  OR `tag` LIKE '%$title%' ORDER BY `id` DESC");

	    if (!empty($result)) {
	        $html = '<ul>';
	        foreach ($result as $attr) {
	            $html .= '<li><a target="_blank" href="' . site_url('attraction-details?id=') . $attr->attraction_id . '">' . $attr->title . '</a></li>';
	        }
	        $html .= '</ul>';
	    } else {
	    	$response = wp_remote_get('https://phx.dosomethingdifferent.com/api/products?title=' . $title,array('timeout'=> 120,));
			$res = wp_remote_retrieve_body($response);
			$data = json_decode($res, true);
			if (!empty($data['data'])) {
				add_attraction_to_db($data['data']);
				$html = '<ul>';
				foreach ($data['data'] as $result) {
					$html .= '<li><a target="_blank" href="' . site_url('attraction-details?id=') . $result['id'] . '">' . $result['title'] . '</a></li>';
				}
				$html .= '</ul>';
			}
		    else {
		        $html = '<div>No record found.</div>';
		    }
	    } 
	    echo $html;
	    exit();
	}
	/*
	if (!empty($_POST['title'])) {
		$title = $_POST['title'];
		$response = wp_remote_get(
			'https://phx.dosomethingdifferent.com/api/products?title=' . $title,
			array(
				'timeout'     => 120,
			)
		);

		$res = wp_remote_retrieve_body($response);
		$data = json_decode($res, true);

		if (!empty($data['data'])) {
			$html = '<ul>';
			foreach ($data['data'] as $result) {
				$html .= '<li><a target="_blank" href="' . site_url('attraction-details?id=') . $result['id'] . '">' . $result['title'] . '</a></li>';
			}
			$html .= '</ul>';
		} else {
			$html = '<div>No record found.</div>';
		}

		echo $html;
		exit;
	}
	*/
}

//attractions search by destination ajax
add_action('wp_ajax_gctcf_attractions_search_by_destination', 'gctcf_attractions_search_by_destination');
add_action('wp_ajax_nopriv_gctcf_attractions_search_by_destination', 'gctcf_attractions_search_by_destination');

function gctcf_attractions_search_by_destination()
{
	global $wpdb;
	$destinations = isset($_POST['dest']) ? $_POST['dest'] : array();
	$tags = isset($_POST['tags']) ? $_POST['tags'] : array();
	$Product = isset($_POST['Product']) ? $_POST['Product'] : '';

	$data1 = $data2 = $data3 = [];

	$attraction_settings = get_option('attraction_api_option');
	$attraction_api_url = $attraction_settings['attraction_api_url'];
	$attraction_api_url = rtrim($attraction_api_url, '/');
	$attraction_api_user = $attraction_settings['attraction_api_username'];
	$attraction_api_pass = $attraction_settings['attraction_api_password'];

	$allResponse = array();


	//echo $attraction_api_url.'/products?dest='.implode(',', $destinations);
	// if (!empty($destinations)) {
	// 	$response1 = wp_remote_get(
	// 		$attraction_api_url . '/products?dest=' . implode(',', $destinations) . '&view=extended',
	// 		array(
	// 			'timeout'     => 120,
	// 		)
	// 	);
	// 	$res1 = wp_remote_retrieve_body($response1);
	// 	$data1 = json_decode($res1, true);
	// }
	if($destinations){
		$qr1 = "SELECT * FROM `attractions_data` WHERE destination IN ('".implode("', '", $destinations)."')";
		$response1 = $wpdb->get_results($qr1);
		$res1 = json_encode($response1);
		$data1 = json_decode($res1,1);
		foreach($data1 as $in => $d1){
			$data1[$in]['id'] = $d1['attraction_id'];
		}
	}

	// $qr01 = "SELECT * FROM `travellanda_cities` WHERE country_name IN ('".implode("', '", $destinations)."')";
	// $response01 = $wpdb->get_results($qr01);
	// $res01 = json_encode($response01);
	// $data01 = json_decode($res01,1);
	// $data001 = [];
	// if($data01) {
	// 	$cities = [];
	// 	foreach($data01 as $d01) {
	// 		$cities[] = $d01['city_name'];
	// 	}
	// 	$qr001 = "SELECT * FROM `attractions_data` WHERE city_name IN ('".implode("', '", $cities)."')";
	// 	$response001 = $wpdb->get_results($qr001);
	// 	$res001 = json_encode($response001);
	// 	$data001 = json_decode($res001,1);
	// }
	// $data1 = array_merge($data1, $data001);
	// foreach($data1 as $in => $d1){
	// 	$data1[$in]['id'] = $d1['attraction_id'];
	// }

	// echo "<pre>"; print_r($data1); exit();

	//echo $attraction_api_url.'/products?tags='.implode(',', $tags);
	// if (!empty($tags)) {
	// 	$response2 =  wp_remote_get(
	// 		$attraction_api_url . '/products?tags=' . implode(',', $tags) . '&view=extended',
	// 		array(
	// 			'timeout'     => 120,
	// 		)
	// 	);

	// 	$res2 = wp_remote_retrieve_body($response2);
	// 	$data2 = json_decode($res2, true);
	// }
	if($tags) {
		$qr2 = "SELECT * FROM `attractions_data` WHERE tag IN ('".implode("', '", $tags)."')";
		$response2 = $wpdb->get_results($qr2);
		$res2 = json_encode($response2);
		$data2 = json_decode($res2,1);
	}
	// pre($data2); exit();
	// print_r($qr2); exit();
	// if (!empty($data1) && !empty($data2)) {

	if($Product) {
		$qr3 = "SELECT * FROM `attractions_data` WHERE `city_name` LIKE '%$Product%' OR `dest` LIKE '%$Product%' OR `destination` LIKE '%$Product%' OR `tag` LIKE '%$Product%' ORDER BY `id` DESC";
		$response3 = $wpdb->get_results($qr3);
		$res3 = json_encode($response3);
		$data3 = json_decode($res3,1);
		// $allResponse = $data3;
	}
	$allResponse = array_merge($data1, $data2, $data3);
	// pre($allResponse); exit();
	// } else if (!empty($data1)) {
		// $allResponse = $data1;
	// } else if (!empty($data2['data'])) {
		// $allResponse = $data2['data'];
	// }
	// echo "<pre>"; print_r($allResponse); exit();



	$html .= '<div class="gctcf-loader attraction-loader" style="display: none;"><div class="gctcf-loader-wrap"><img src="' . site_url() . '/wp-content/plugins/gctcf/public/images/loader.gif"><div class="loader-message"><p>The result will appear within a few seconds.</p></div></div></div>';
	if (!empty($allResponse)) {

		foreach ($allResponse as $result) {
			$html .= '<div class="tour-view-row">
      					<div class="list-image"> <a target="_blank" href="' . site_url('attraction-details?id=') . $result['id'] . '"> <img src="' . $result['img_sml'] . '" alt="img"></a> </div>
  						<div class="img-desc">
        					<div class="img-heading">
          						<h2> <a target="_blank" href="' . site_url('attraction-details?id=') . $result['id'] . '"> ' . $result['title'] . '</a></h2>
        					</div>
        					<div class="short-des">';
			if ($result['price_from_child'] != '') {
				$html .= '<p>' . $result['desc_short'] . '</p>';
			}
			$html .= '</div>
        					<div class="price-list">';

        	  if($result['price_from_adult'] != 0) :
	            if($result['price_from_child'] != 0):
	              $html .= '<p> Child from <span>£'.$result['price_from_child'].'</span> </p>';
	            endif;
	              $html .= '<p> Adult from <span>£'.$result['price_from_adult'].'</span> </p>';
	          else: $general_price = 0.00; $all_prices = json_decode($result['price_from_all'],1); if(isset($all_prices[0]['price_from']))$general_price = $all_prices[0]['price_from'];
	            if($general_price != 0):
	              $html .= '<p> Price from <span>£'.$general_price.'</span> </p>';
	            endif; 
	          endif;


			// if($result['price_from_adult'] != 0) : if($result['price_from_child'] != 0):
            //     $html .= '<p> Child from <span>£'.$result['price_from_child'].'</span> </p>';
            //  endif; if($result['price_from_adult'] != ''):
            //    $html .= '<p> Adult from <span>£'.$result['price_from_adult'].'</span> </p>';
            //  endif; else:
            //     $html .= '<p> Price from <span>£'.json_decode($destination['price_from_all'],1)['price_from'].'</span> </p>';
			// endif; 

			// if ($result['price_from_child'] != '') {
			// 	$html .= '<p> child from <span>£ ' . $result['price_from_child'] . '</span> </p>';
			// }
			// if ($result['price_from_adult'] != '') {
			// 	$html .= '<p> adult from <span>£ ' . $result['price_from_adult'] . '</span> </p>';
			// }

			$html .= '</div>
        					<p class="loction"> ' . $result['dest'] . ' </p>
      					</div>
    				</div>';
		}
	} else {
		$html = '<div class="no-records-msg">No record found.</div>';
	}

	echo $html;
	exit;
}

add_action('wp_ajax_gctcf_attractions_dateid', 'gctcf_attractions_dateid');
add_action('wp_ajax_nopriv_gctcf_attractions_dateid', 'gctcf_attractions_dateid');

function gctcf_attractions_dateid()
{
	$html = '';
	$date1 = isset($_POST['date']) ? $_POST['date'] : '';
	$date_create = date_create($date1);
	$date = date_format($date_create, "Y-m-d");
	$time = isset($_POST['time']) ? $_POST['time'] : '';
	$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';
	$datefrom = isset($_POST['datefrom']) ? $_POST['datefrom'] : '';
	$dateto = isset($_POST['dateto']) ? $_POST['dateto'] : '';

	if (!empty($product_id) && !empty($datefrom) && !empty($dateto) && !empty($date1)) {

		$apiUrl = 'https://phx.dosomethingdifferent.com/api/products/' . $product_id . '?date_from=' . $datefrom . '&date_to=' . $dateto;

		$response = wp_remote_get($apiUrl, array(
			'timeout'     => 120,
		));

		$responseBody = wp_remote_retrieve_body($response);
		$result = json_decode($responseBody, true);

		foreach ($result['tickets'] as $value) {

			foreach ($value['availability'] as $value1) {

				if (!empty($time)) {
					if ($value1['date'] == $date && $value1['time'] == $time) {
						$dateid = $value1['date_id'];
					}
				} else {
					if ($value1['date'] == $date) {
						$dateid = $value1['date_id'];
					}
				}
			}
		}
		if ($dateid) {
			wp_send_json_success($dateid);
		} else {
			wp_send_json_error('Booking not avialble on this time and date');
		}
	}
	exit;
}

add_action('wp_ajax_gctcf_attractions_avilabletime', 'gctcf_attractions_avilabletime');
add_action('wp_ajax_nopriv_gctcf_attractions_avilabletime', 'gctcf_attractions_avilabletime');

function gctcf_attractions_avilabletime()
{
	$html = '';

	$date1 = isset($_POST['date']) ? $_POST['date'] : '';

	$date_create = date_create($date1);
	$date = date_format($date_create, "Y-m-d");

	$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';
	$datefrom = isset($_POST['datefrom']) ? $_POST['datefrom'] : '';
	$dateto = isset($_POST['dateto']) ? $_POST['dateto'] : '';

	$timeArr = array();

	if (!empty($product_id) && !empty($datefrom) && !empty($dateto) && !empty($date1)) {

		$apiUrl = 'https://phx.dosomethingdifferent.com/api/products/' . $product_id . '?date_from=' . $datefrom . '&date_to=' . $dateto;

		$response = wp_remote_get($apiUrl, array(
			'timeout'     => 120,
		));

		$responseBody = wp_remote_retrieve_body($response);
		$result = json_decode($responseBody, true);

		foreach ($result['tickets'] as $value) {

			foreach ($value['availability'] as $value1) {
				if ($value1['date'] == $date) {
					$timeArr[] = $value1['time'];
				}
			}
		}
	}

	if (!empty($timeArr)) {
		foreach (array_unique($timeArr) as $value1) {
			$html .= '<option value="' . $value1 . '">' . $value1 . '</option>';
		}
		wp_send_json_success(array('html' => $html, 'time' => array_unique($timeArr)));
	} else {
		wp_send_json_error();
	}


	exit;
}

//coupon search ajax
add_action('wp_ajax_gctc_attractioncoupon_search', 'gctc_attractioncoupon_search');
add_action('wp_ajax_nopriv_gctc_attractioncoupon_search', 'gctc_attractioncoupon_search');

function gctc_attractioncoupon_search()
{

	global $wpdb;
	$amount = 0;
	if (isset($_POST['quote_id'])) {
		$quote_id = sanitize_text_field($_POST['quote_id']);
		$total_price = sanitize_text_field($_POST['total_price']);

		update_post_meta($quote_id, '_gctcf_coupon_checked', '');
		if (!empty($_POST['coupon_code_search'])) {
			$discount_type = 'amount';
			$coupon_code = sanitize_text_field($_POST['coupon_code_search']);
			update_post_meta($quote_id, '_gctcf_coupon_checked', $coupon_code);
			$coupon_wp = new WC_Coupon($coupon_code);

			$amount = $coupon_wp->get_amount();
			if (!$amount) {

				$physical_vouchers = get_posts(array(
					'post_type' => 'gc4t_store_voucher',
					'post_status' => 'private',
					'posts_per_page' => 1,
					'meta_query' => array(
						array(
							'key' => 'gc4t_voucher_status',
							'value' => 'active',
						),
						array(
							'key' => 'gc4t_voucher_code',
							'value' => $coupon_code,
						),
					)
				));
				if (isset($physical_vouchers[0])) {
					$amount = get_post_meta($physical_vouchers[0]->ID, 'gc4t_voucher_amount_remaining', true);
					update_post_meta($quote_id, '_gctcf_booking_amount', $total_price);
					update_post_meta($quote_id, '_gctcf_coupon_amount', $amount);
					update_post_meta($quote_id, '_gctcf_coupon_code', $coupon_code);
				} else {
					update_post_meta($quote_id, '_gctcf_booking_amount', $total_price);
					update_post_meta($quote_id, '_gctcf_coupon_amount', '');
					update_post_meta($quote_id, '_gctcf_coupon_code', '');
				}
				$total_price = sanitize_text_field($_POST['total_price']);
				$total_price = ($total_price) ? $total_price : 0;
				if ($total_price) {
					$difference = $total_price - $amount;
					if ($difference < 0) {
						$amount = $total_price - 1;
					}
				}
			}
			if ($discount_type == 'percent') {
				$total_price = sanitize_text_field($_POST['total_price']);
				$total_price = ($total_price) ? $total_price : 0;
				if ($total_price) {
					$discount_amount = $total_price / 100 * $amount;
					$amount = sprintf('%01.2f', $discount_amount);
				}
			}
		}
		echo $amount;
	}
	exit;
}

//hotel load more ajax
add_action('wp_ajax_gctcf_load_more_hotels', 'gctcf_load_more_hotels');
add_action('wp_ajax_nopriv_gctcf_load_more_hotels', 'gctcf_load_more_hotels');

function gctcf_load_more_hotels()
{
	ob_start();
	if (!WC()->session->has_session()) {
	    WC()->session->set_customer_session_cookie(true);
	}
    $offset = isset($_POST['gctcf_offset']) ? $_POST['gctcf_offset'] : 10;
    $gctcf_prices = isset($_POST['gctcf_prices']) ? $_POST['gctcf_prices'] : '';
    $gctcf_ratings = isset($_POST['gctcf_ratings']) ? $_POST['gctcf_ratings'] : '';
    $limit = 100;
    // $hotels = isset($_SESSION['gctcf_all']) ? $_SESSION['gctcf_all'] : array();
    $hotels = WC()->session->get('gctcf_all', array());
    $country_code = isset($_POST['country_code']) ? $_POST['country_code'] : '';
    $hotel_name = isset($_POST['hotel_name']) ? $_POST['hotel_name'] : '';
    $hotel_regionid = isset($_POST['hotel_regionid']) ? $_POST['hotel_regionid'] : '';
    $hotel_country_by_region = isset($_POST['hotel_country_by_region']) ? $_POST['hotel_country_by_region'] : '';
    $hotel__region__name = isset($_POST['hotel__region__name']) ? $_POST['hotel__region__name'] : '';
    $hotel_start_date = isset($_POST['hotel_check_in_date']) ? $_POST['hotel_check_in_date'] : '';
    $hotel_night = isset($_POST['hotel_night']) ? $_POST['hotel_night'] : '';
    $rooms_requested = isset($_POST['no_of_room']) ? $_POST['no_of_room'] : array();
    $room_with_adults = isset($_POST['hotel_adults']) ? $_POST['hotel_adults'] : array();
    $room_with_children = isset($_POST['hotel_children']) ? $_POST['hotel_children'] : array();


    $stuba_hotels = WC()->session->get('stuba_all', array());
    $travelnda_hotels = WC()->session->get('travelnda_all', array());

    // $stuba_hotels =  array();
    // $travelnda_hotels = array();

    $next_stuba = array_slice($stuba_hotels, $offset / 2, 50);
    $next_travelnda = array_slice($travelnda_hotels, $offset / 2, 50);

    // echo "<pre>"; 
    // print_r($next_stuba);
    // print_r($next_travelnda);

    // $hotels = [];
    $hotels = array_merge($next_travelnda, $next_stuba);
	// echo "<pre>"; print_r($hotels); exit();
    
    if(!empty($hotels))
    {
       // $merged_results = array_slice($hotels, $offset, $limit);
        //pre($merged_results);

        foreach ($hotels as $merged_result): ?>
        
            <div class="gc-row hotel__search_result_view">
                <div class="gc-lg-3">
                    <input type="hidden" name="feed_type" id="feed_type" value="<?php echo $merged_result['feed']; ?>">
                    <input type="hidden" name="hotel__id" id="hotel__id" value="<?php echo $merged_result['hotel_id']; ?>">


                    <div class="property_img">
                        <img src="<?php echo $merged_result['hotel_image']; ?>">
                    </div>
                </div>

                <div class="gc-lg-9">
                <div class="search-result-view-content">
                    <a class="gctcf-search-result-inner"><h3><?php echo $merged_result['hotel_name']; ?></h3>
					<span class="rating">
					<?php 
							for($k=0;$k<$merged_result['star_rating'];$k++)
		                    {
		                        echo '<i class="fa fa-star"></i>';
		                    }
					?>
					</span></a>

                    <div class="hotel-list2">
                        <div class="gc-md-4 mobile-room-type-hedaing">
                            <h6>ROOM TYPES</h6>
                        </div>
                        <div class="gc-md-2 mobile-breakfast-hedaing">
                            <h6>Breakfast</h6>
                        </div>
                        <div class="gc-md-2 mobile-price-hedaing">
                            <h6>PRICE</h6>
                        </div>
                        <div class="gc-md-2 mobile-facility-hedaing">
                            <h6>Facility</h6>
                        </div>
                        <div class="gc-md-2 mobile-action-hedaing">
                            <h6>ACTION</h6>
                        </div>
                    </div>

                    <?php foreach ($merged_result['room_types'] as $room_type_index => $room_type) : ?>
                        <?php //if ($room_type_index < 3) : ?>
                            <div class="gc-md-12 gc-sm-12 gc-xs-12 mobile-border-hotel-search">
                                <form action="<?php echo home_url(); ?>/hotel-booking/" method="post" name="fr3" target="_blank">
                                    <div class="hidden_file">
                                        <input type="hidden" name="feed_type" value="<?php echo $room_type['feed']; ?>">
                                        <input type="hidden" name="hotel_name" value="<?php echo $merged_result['hotel_name']; ?>">
                                        <input type="hidden" name="hotel_id" value="<?php echo $merged_result['hotel_id']; ?>">
                                        <input type="hidden" name="hotel_quote_id" value="<?php echo $room_type['quote_id']; ?>">
                                        <input type="hidden" name="room_code" value="<?php echo $room_type['room_code']; ?>">
                                        <input type="hidden" name="room_name" value="<?php echo $room_type['name']; ?>">
                                        <input type="hidden" name="room_meal" value="<?php echo $room_type['meal']; ?>">
                                        <input type="hidden" name="hotel_start_date" value="<?php echo $hotel_start_date; ?>">
                                        <input type="hidden" name="hotel_nights" value="<?php echo $hotel_night; ?>">
                                        <input type="hidden" name="room_price" value="<?php echo round($room_type['total_price'],2);?>">
                                        <?php foreach ($room_with_adults as $key => $values) : ?>
                                            <input type="hidden" name="numbers_of_adults[<?php echo $key; ?>]" value="<?php echo $values; ?>" />
                                        <?php endforeach; ?>
                                        <?php foreach ($room_with_children as $key => $values): ?>
                                            <input type="hidden" name="numbers_of_child[<?php echo $key; ?>]" value="<?php echo $values; ?>" /> 
                                        <?php endforeach; ?>
                                        <?php foreach ($rooms_requested as $key => $values): ?>
                                            <input type="hidden" name="numbers_of_room[<?php echo $key; ?>]" value="<?php echo $values; ?>" />  
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="gc-md-4 gc-sm-6 gc-xs-12 mobile-roomtype-result">
                                        <p><?php echo $room_type['name']; ?></p>
                                        <input type="hidden" name="room_type_name_by_hotel" value="<?php echo $room_type['name']; ?>">
                                    </div>

                                    <div class="gc-md-2 gc-sm-6 gc-xs-12 mobile-breakfast-result">
                                        <p><?php echo $room_type['meal']; ?></p>
                                    </div>

                                    <div class="gc-md-2 gc-sm-6 gc-xs-12 list-style-hotel1 mobile-price-result">
                                        <h5><?php echo '£'.round($room_type['total_price'],2);?></h5>
                                    </div>

                                    <div class="gc-md-2 gc-sm-6 gc-xs-12 list-style-hotel1 mobile-facility-result">
                                        <button type="button" class="btn btn-primary mobile_bottom_facility pd-md-1" id="hotel__id_ajax_<?php echo $merged_result['hotel_id']; ?>" value="<?php echo $merged_result['hotel_id']; ?>" onclick="hotel_facility_ajax(this.id)" data-feed="<?php echo $merged_result['feed']; ?>" data-toggle="modal" data-target="#product_view" style="height:42px;"><span>Quick </span><span>View</span></button>
                                    </div>

                                    <div class="gc-md-2 gc-sm-6 gc-xs-12 list-style-hotel1 mobile-action-result">
                                        <input type="submit" name="hotel__booking" id="hotel__booking" value="BOOK NOW" class="btn btn-primary border-radius pd-md-1"/>
                                    </div>

                                    <div class="mobile_padding_hotel_search"></div>
                                </form>
                            </div>
                        <?php //endif; ?>
                    <?php endforeach; ?>
                </div>
                </div>
            </div>
        <?php endforeach;
    }

    $html = ob_get_clean();
    echo json_encode( 
    		array('html' =>  $html, 'offset' => ($offset + 100), 'count' => count($hotels))
    	);
    	exit;
}
