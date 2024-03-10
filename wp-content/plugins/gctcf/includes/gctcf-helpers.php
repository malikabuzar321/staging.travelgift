<?php 

function pre($val, $die=false)
{
	$print =  "<pre>".print_r($val, true)."</pre>";
	echo $print;
	if($die)
	{
		die("HERE");
	}
}
function get_travellanda_hotel($hotel_id){
    require_once GCTCF_PATH . '/includes/Travellanda.class.php';
    $travellanda_settings = get_travellanda_api_config();
    $travellanda = new Travellanda();
    $travellanda->setUsername($travellanda_settings['user']);
    $travellanda->setPassword($travellanda_settings['pass']);
    $travellanda->setMode($travellanda_settings['mode']);

    $hotel_details = $travellanda->getHotelDetails(array($hotel_id));
    $hotel_details = json_decode($travellanda->convertToJson($hotel_details['body']), true);
    if(isset($hotel_details['Body'])){
        if(isset($hotel_details['Body']['Hotels'])){
            if(isset($hotel_details['Body']['Hotels']['Hotel'])){
                return $hotel_details['Body']['Hotels']['Hotel'];
            }

        }
    }
    return [];
}
function get_stuba_hotel($hotel_id){
    $hotel_details = [];
    $dir = wp_get_upload_dir();
    $file = $dir['basedir']. '/hotel_api/' . $hotel_id . '.xml';
    $hotel_details = [];
    if(file_exists($file)){
    	$xml = file_get_contents($file);
    	$simple_xml = simplexml_load_string($xml);
        $json = json_encode($simple_xml);
        $hotel_details = json_decode($json);
    }
    return $hotel_details;
}
function get_tbo_hotel($hotel_id){
    $tbo_mode = get_option('options_tboh_api_mode');
    $tbo_username = get_option('options_tboh_'.$tbo_mode.'_username');
    $tbo_password = get_option('options_tboh_'.$tbo_mode.'_password');
    $tbo_url = get_option('options_tboh_'.$tbo_mode.'_url');
    $auth = base64_encode($tbo_username . ":" . $tbo_password);
    $hotel_details = send_tbo_request($tbo_url . 'HotelDetails', ['Hotelcodes' => $hotel_id, 'Language' => 'en'],$auth);
    if(isset($hotel_details['HotelDetails'])){
        return $hotel_details['HotelDetails'][0];
    }
    return [];
}
function convert_tbo_star_rating($words){
	$number = 0;
	switch ($words) {
		case 'OneStar':
			$number = 1;
			break;
		case 'TwoStar':
			$number = 2;
			break;
		case 'ThreeStar':
			$number = 3;
			break;
		case 'FourStar':
			$number = 4;
			break;
		case 'FiveStar':
			$number = 5;
			break;
	}
	return $number;
}
function send_tbo_request($url,$data,$auth){
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => $url,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'POST',
	  CURLOPT_POSTFIELDS =>json_encode($data),
	  CURLOPT_HTTPHEADER => array(
	    'Content-Type: application/json',
	    'Authorization: Basic '.$auth
	  ),
	));
	$response = curl_exec($curl);
	curl_close($curl);
	if($response){
		$tbo_data = json_decode($response,1);
		if($tbo_data['Status']['Code']=='200'){
			return $tbo_data;
		}
	}
	return array();
}

function get_hotles($path)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $path);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_POST, false);

	$hotelData = curl_exec($ch);

	return $hotelData; 
}

function gc4t_get_popular_hotels_list($number) {
	$list = array();
	$hotels = array();
	$popular_hotels = gc4t_get_popular_hotels($number);

	// pre($popular_hotels); exit();
	// foreach ($popular_hotels as $hotel_id => $clicks) {
	// 	$list[] = array(
	// 		'hotel_id' => $hotel_id,
	// 		'clicks' => $clicks,
	// 	);
	// }
	// // pre($list); exit();
	// // usort($list, 'gc4t_hotel_sort');
	// $dir = wp_get_upload_dir();
	// foreach ($list as $hotel) {
	// 	if (file_exists($dir['basedir'] .'/hotel_api/'.$hotel['hotel_id'].'.xml')) {
	// 		$hotels[] = simplexml_load_file($dir['basedir'] .'/hotel_api/'.$hotel['hotel_id'].'.xml');
	// 	}
	// }
	// $count = count($hotels);
	// $fill_in = $number - $count;
	// if ($fill_in > 0) {
	// 	$fpath = $dir['basedir'];
	// 	 $hotel_files=glob($fpath.'/hotel_api/*.xml');
	// 	 $random_hotel_file=array_rand($hotel_files,$fill_in * 2);
	// 	 foreach($random_hotel_file as $i) {
	// 		if (!isset($popular_hotels[$i])) {
	// 			$hotels[] = simplexml_load_file($hotel_files[$i]);
	// 		}
	// 	}
	// }
	// pre($hotels);
	// $dir = wp_get_upload_dir();
	// $feeds = [];
	// foreach($popular_hotels as $index => $hotel){
	// 	if($hotel->feed == 'stuba'){
	// 		if (file_exists($dir['basedir'] .'/hotel_api/'.$hotel->hotel_id.'.xml')) {
	// 			$hotels[] = simplexml_load_file($dir['basedir'] .'/hotel_api/'.$hotel->hotel_id.'.xml');
	// 		}
	// 	} else {
	// 		require_once GCTCF_PATH . '/includes/Travellanda.class.php';
	// 	    $travellanda_settings = get_travellanda_api_config();

	// 	    $travellanda = new Travellanda();
	// 	    $travellanda->setUsername($travellanda_settings['user']);
	// 	    $travellanda->setPassword($travellanda_settings['pass']);
	// 	    $travellanda->setMode($travellanda_settings['mode']);

	// 	    $hotel_details = $travellanda->getHotelDetails(array($hotel->hotel_id));
	// 	    $hotel_details = json_decode($travellanda->convertToJson($hotel_details['body']), true);
	// 	    $hotels[] = $hotel_details;
	// 	}
	// 	$feeds[]=$hotel->feed;
	// 	// pre($feeds);

	// }
	// // return $hotels;
	return array_slice($popular_hotels, 0, $number);
}

function gc4t_get_popular_hotels($number=0) {
	global $wpdb;
	$p_hotels = $wpdb->get_results("SELECT * FROM `hotels_data` ORDER BY `viewed` DESC LIMIT ".$number*2);
	// $p_hs = [];
	// foreach($p_hotels as $ph){
	// 	$p_hs[$ph->hotel_id] = $ph->viewed;
	// }
	// $popular_hotels = json_decode(get_option('gc4t_popular_hotels'), true);
	// $popular_hotels = $p_hs;
	$popular_hotels = $p_hotels;
	return ($popular_hotels) ? $popular_hotels : array();
}

function gc4t_hotel_sort($a, $b) {
	if ($a['clicks'] == $b['clicks']) {
        return 0;
    }
    return ($a['clicks'] > $b['clicks']) ? -1 : 1;
}

function room_without_children($adult)
{
	$adult_str = ' ';
	for($i=0; $i<$adult; $i++)
	{
		$adult_str .= '<Adult/>'; 
	}
	return $adult_str; 
}

/*************************Child section*************************/	
function room_with_children($child)
{
	$child_str = ' ';
	for($i=0; $i<$child; $i++)
	{
		$child_str .= '<Child age="5" />';
	}
	return $child_str;
}

function xml_attribute($object, $attribute)
{
	if(isset($object->{'@attributes'}->{$attribute}))
	{
		return (string) $object->{'@attributes'}->{$attribute};
	}
	else if(isset($object[$attribute]))
	{
		return (string) $object[$attribute];
	}
	return;
}

function gc4t_set_popular_hotels($hotels) {
	update_option('gc4t_popular_hotels', json_encode($hotels));
}

function gc4t_add_popular_hotel($hotel_id) {
	$popular_hotels = gc4t_get_popular_hotels();
	$converted = (string) $hotel_id;
	$popular_hotels[$converted] = (isset($popular_hotels[$converted])) ? $popular_hotels[$converted] + 1 : 1;
	gc4t_set_popular_hotels($popular_hotels);
}

function get_api_config() //stuba
{

    $mode = get_field('_api_mode', 'option');

    if ($mode == 'live') {

        $api_url = get_field('_live_api_url', 'option');
        $api_org = get_field('_live_org', 'option');
        $api_user = get_field('_live_username', 'option');
        $api_pass = get_field('_live_password', 'option');

    } else {

        $api_url = get_field('_sandbox_api_url', 'option');
        $api_org = get_field('_sandbox_org', 'option');
        $api_user = get_field('_sandbox_username', 'option');
        $api_pass = get_field('_sandbox_password', 'option');

    }

    $config = array(
        'url' => $api_url,
        'org' => $api_org,
        'user' => $api_user,
        'pass' => $api_pass,
    );

    return $config;

}
function get_transfer_api_config()
{

    $mode = get_field('_api_mode_transfer', 'option');

    if ($mode == 'live') {

        $api_url = get_field('_live_api_url_transfer', 'option');
        $api_user = get_field('_live_username_transfer', 'option');
        $api_pass = get_field('_live_password_transfer', 'option');

    } else {

        $api_url = get_field('_test_api_url_transfer', 'option');
        $api_user = get_field('_test_username_transfer', 'option');
        $api_pass = get_field('_test_password_transfer', 'option');

    }

    $config = array(
        'url' => $api_url,
        'user' => $api_user,
        'pass' => $api_pass,
    );

    return $config;

}

function get_travellanda_api_config()
{

    $mode = get_field('_api_mode_travellanda', 'option');

    if ($mode == 'live') {

        $api_user = get_field('_live_username_travellanda', 'option');
        $api_pass = get_field('_live_password_travellanda', 'option');

    } else {

        $api_user = get_field('_test_username_travellanda', 'option');
        $api_pass = get_field('_test_password_travellanda', 'option');

    }

    $config = array(
        'user' => $api_user,
        'pass' => $api_pass,
        'mode' => $mode,
    );

    return $config;

}

add_action('rest_api_init', 'gc4t_register_rest_routes');

function gc4t_register_rest_routes() {
	register_rest_route(
		'production/v1',
		'/create-gift-card/',
		array(
			'methods' => 'POST',
			'callback' => 'gc4t_create_gift_card',
			'permission_callback' => function() {
				return current_user_can('manage_woocommerce');
			},
		)
	);

	register_rest_route(
		'production/v1',
		'/cancel-gift-card/',
		array(
			'methods' => 'POST',
			'callback' => 'gc4t_cancel_gift_card',
			'permission_callback' => function() {
				return current_user_can('manage_woocommerce');
			},
		),
	);

	register_rest_route(
		'production/v1',
		'/check-gift-card-balance/',
		array(
			'methods' => 'POST',
			'callback' => 'gc4t_check_gift_card_balance',
			'permission_callback' => function() {
				return current_user_can('manage_woocommerce');
			},
		)
	);

	register_rest_route(
		'production/v1',
		'/create-gift-card-eur/',
		array(
			'methods' => 'POST',
			'callback' => 'gc4t_create_gift_card_eur',
			'permission_callback' => function() {
				return current_user_can('manage_woocomemrce');
			},
		)
	);

	register_rest_route(
		'sandbox/v1',
		'/create-gift-card/',
		array(
			'methods' => 'POST',
			'callback' => 'gc4t_test_create_gift_card',
			'permission_callback' => function() {
				return current_user_can('manage_woocommerce');
			},
		)
	);

	register_rest_route(
		'sandbox/v1',
		'/create-gift-card-eur/',
		array(
			'methods' => 'POST',
			'callback' => 'gc4t_test_create_gift_card_eur',
			'permission_callback' => function() {
				return current_user_can('manage_woocomemrce');
			},
		)
	);

	register_rest_route(
		'sandbox/v1',
		'/cancel-gift-card/',
		array(
			'methods' => 'POST',
			'callback' => 'gc4t_test_cancel_gift_card',
			'permission_callback' => function() {
				return current_user_can('manage_woocommerce');
			},
		),
	);

	register_rest_route(
		'sandbox/v1',
		'/check-gift-card-balance/',
		array(
			'methods' => 'POST',
			'callback' => 'gc4t_test_check_gift_card_balance',
			'permission_callback' => function() {
				return current_user_can('manage_woocommerce');
			},
		)
	);


}

function gc4t_test_cancel_gift_card($data) {
	$response = array(
		'status_code' => 400,
		'error' => '',
		'original_balance' => 0,
		'old_balance' => 0,
		'balance' => 0,
		'voucher_code' => '',
		'order_id' => '',
	);

	$params = $data->get_json_params();
	$sanitized = array_map('sanitize_text_field', $params);

	$voucher_code = (isset($sanitized['voucher_code'])) ? $sanitized['voucher_code'] : '';
	$order_id = (isset($sanitized['order_id'])) ? strtolower($sanitized['order_id']) : '';
	$value = (isset($sanitized['value'])) ? $sanitized['value'] : null;

	//Validation
	if (empty($voucher_code) && empty($order_id)) {
		$response['error'] .= 'Please enter a voucher code or an order ID' . "\r\n"; 
	}
	if (isset($value)) {
		if (is_numeric($value)) {
			if ($value <= 0) {
				$response['error'] .= 'Please enter a value greater than 0' . "\r\n";
			}
		} else {
			$response['error'] .= 'Please enter a numeric value greater than 0' . "\r\n";
		}
	}
	if (empty($response['error'])) {
		$response['error'] = 'invalid request' . "\r\n" . 'voucher not found' . "\r\n";
		if ($voucher_code) {
			$voucher_code = strtolower( $voucher_code );
			$sandbox_test_codes = get_option( 'gctf_sandbox_test_codes', '[]' );
			$sandbox_test_codes = json_decode( $sandbox_test_codes, true );
			$sandbox_test_codes = ( $sandbox_test_codes ) ? $sandbox_test_codes : array();

			if ( isset( $sandbox_test_codes[$voucher_code] ) ) {
				$voucher = $sandbox_test_codes[$voucher_code];
				$response['old_balance'] = $voucher['value'];
				$response['status_code'] = 200;
				$response['original_balance'] = $voucher['original_amount'];
				$response['amount'] = '0';
				$response['error'] = '';
				$response['voucher_code'] = $voucher_code;
				$response['order_id'] = $voucher['order_ref'];

				$response['error'] = '';

				$sandbox_test_codes[$voucher_code]['value'] = 0;
				update_option( 'gctf_sandbox_test_codes', json_encode( $sandbox_test_codes ) );
			}
		} else {
			if ($order_id) {
				$sandbox_test_orders = get_option( 'gctf_sandbox_test_orders', '[]' );
				$sandbox_test_orders = json_decode( $sandbox_test_orders, true );
				$sandbox_test_orders = ( $sandbox_test_orders ) ? $sandbox_test_orders : array();
				
				if ( isset( $sandbox_test_orders[$order_id] ) ) {
					$voucher_code = $sandbox_test_orders[$order_id];
					if ($voucher_code) {
						$voucher_code = strtolower( $voucher_code );
						$sandbox_test_codes = get_option( 'gctf_sandbox_test_codes', '[]' );
						$sandbox_test_codes = json_decode( $sandbox_test_codes, true );
						$sandbox_test_codes = ( $sandbox_test_codes ) ? $sandbox_test_codes : array();

						if ( isset( $sandbox_test_codes[$voucher_code] ) ) {
							$voucher = $sandbox_test_codes[$voucher_code];
							$response['old_balance'] = $voucher['value'];
							$response['status_code'] = 200;
							$response['original_balance'] = $voucher['original_amount'];
							$response['amount'] = '0';
							$response['error'] = '';
							$response['voucher_code'] = $voucher_code;
							$response['order_id'] = $voucher['order_ref'];

							$response['error'] = '';

							$sandbox_test_codes[$voucher_code]['value'] = 0;
							update_option( 'gctf_sandbox_test_codes', json_encode( $sandbox_test_codes ) );
						}
					}
				}
			}
		}
	} else {
		$response['error'] = 'invalid request' . "\r\n" . $response['error'];
	}

	return rest_ensure_response($response);
}

function gc4t_cancel_gift_card($data) {
	$response = array(
		'status_code' => 400,
		'error' => '',
		'original_balance' => 0,
		'old_balance' => 0,
		'balance' => 0,
		'voucher_code' => '',
		'order_id' => '',
	);

	$params = $data->get_json_params();
	$sanitized = array_map('sanitize_text_field', $params);

	$voucher_code = (isset($sanitized['voucher_code'])) ? $sanitized['voucher_code'] : '';
	$order_id = (isset($sanitized['order_id'])) ? strtolower($sanitized['order_id']) : '';
	$value = (isset($sanitized['value'])) ? $sanitized['value'] : null;

	//Validation
	if (empty($voucher_code) && empty($order_id)) {
		$response['error'] .= 'Please enter a voucher code or an order ID' . "\r\n"; 
	}
	if (isset($value)) {
		if (is_numeric($value)) {
			if ($value <= 0) {
				$response['error'] .= 'Please enter a value greater than 0' . "\r\n";
			}
		} else {
			$response['error'] .= 'Please enter a numeric value greater than 0' . "\r\n";
		}
	}
	if (empty($response['error'])) {
		$response['error'] = 'invalid request' . "\r\n" . 'voucher not found' . "\r\n";
		if ($voucher_code) {
			$orders = get_posts(array(
				'posts_per_page' => 1,
				'post_type' => 'shop_order',
				'post_status' => array(
					'wc-completed',
					'wc-pending',
					'wc-processing',
					'wc-on-hold',
					'wc-failed',
				),
				'author' => 1,
				'meta_query' => array(
					array(
						'key' => 'user_giftcard_code',
						'value' => $voucher_code,
					),
					array(
						'key' => 'created_by',
						'value' => get_current_user_id(),
					),
					array(
						'key' => 'method',
						'value' => 'api',
					)
				)
			));

			if ($orders && isset($orders[0])) {
				$order = wc_get_order($orders[0]->ID);
				if ($order) {
					$order->update_status('cancelled', 'Order cancelled by API', true);
					update_post_meta($order->get_id(), 'cancelled_by', get_current_user_id());

					$coupons = get_posts(array(
						'posts_per_page' => 1,
						'post_type' => 'shop_coupon',
						'post_status' => 'publish',
						'author' => get_current_user_id(),
						'title' => $voucher_code,
					));
					if ($coupons && isset($coupons[0])) {
						$response['old_balance'] = get_post_meta($coupons[0]->ID, 'coupon_amount', true);
						update_post_meta($coupons[0]->ID, 'coupon_amount', '0');

						$response['status_code'] = 200;
						$response['original_balance'] = get_post_meta($coupons[0]->ID, 'original_amount', true);
						$response['amount'] = '0';
						$response['error'] = '';
						$response['voucher_code'] = $coupons[0]->post_title;
						$response['order_id'] = 'GC4T-' . $orders[0]->ID;

						$response['error'] = '';
					}
				}
			}
		} else {
			if ($order_id) {
				$order_id = str_replace('gc4t-', '', $order_id);
				$order = wc_get_order($order_id);
				if ($order) {
					$order->update_status('cancelled', 'Order cancelled by API', true);
					update_post_meta($order->get_id(), 'cancelled_by', get_current_user_id());

					$coupons = get_posts(array(
						'posts_per_page' => 1,
						'post_type' => 'shop_coupon',
						'post_status' => 'publish',
						'author' => get_current_user_id(),
						'title' => get_post_meta($order->get_id(), 'user_giftcard_code', true),
					));
					if ($coupons && isset($coupons[0])) {
						$response['old_balance'] = get_post_meta($coupons[0]->ID, 'coupon_amount', true);
						update_post_meta($coupons[0]->ID, 'coupon_amount', '0');

						$response['status_code'] = 200;
						$response['original_balance'] = get_post_meta($coupons[0]->ID, 'original_amount', true);
						$response['amount'] = '0';
						$response['error'] = '';
						$response['voucher_code'] = $coupons[0]->post_title;
						$response['order_id'] = 'GC4T-' . $order_id;
					}
				}
			}
		}
	} else {
		$response['error'] = 'invalid request' . "\r\n" . $response['error'];
	}

	return rest_ensure_response($response);
}

function gc4t_test_check_gift_card_balance($data) {
	$response = array(
		'status_code' => 400,
		'error' => '',
		'balance' => 0,
		'original_balance' => 0,
		'voucher_status' => 'invalid',
		'voucher_code' => '',
	);

	$params = $data->get_json_params();
	$sanitized = array_map('sanitize_text_field', $params);

	$voucher_code = (isset($sanitized['voucher_code'])) ? $sanitized['voucher_code'] : '';
	$voucher_code = strtolower( $voucher_code );
	if (empty($voucher_code)) {
		$response['error'] .= 'Please enter a voucher code' . "\r\n";
	}

	if (empty($response['error'])) {
		//Check for virtual
		$sandbox_test_codes = get_option( 'gctf_sandbox_test_codes', '[]' );
		$sandbox_test_codes = json_decode( $sandbox_test_codes, true );
		$sandbox_test_codes = ( $sandbox_test_codes ) ? $sandbox_test_codes : array();

		if ( isset( $sandbox_test_codes[$voucher_code] ) ) {
			$voucher = $sandbox_test_codes[$voucher_code];
			$date_created = $voucher['date_created'];
			if ($date_created) {
				$response['balance'] = $voucher['value'];
				$response['original_balance'] = $voucher['original_amount'];
				$status = ($voucher['value'] > 0) ? 'active' : 'cancelled_used';
				$response['voucher_status'] = $status;
				$response['voucher_code'] = $voucher_code;
				$response['status_code'] = 200;
			}
		}
	} else {
		$response['error'] = 'invalid request' . "\r\n" . $response['error'];
	}

	return rest_ensure_response($response);
}

function gc4t_check_gift_card_balance($data) {
	$response = array(
		'status_code' => 400,
		'error' => '',
		'balance' => 0,
		'original_balance' => 0,
		'voucher_status' => 'invalid',
		'voucher_code' => '',
	);

	$params = $data->get_json_params();
	$sanitized = array_map('sanitize_text_field', $params);

	$voucher_code = (isset($sanitized['voucher_code'])) ? $sanitized['voucher_code'] : '';
	if (empty($voucher_code)) {
		$response['error'] .= 'Please enter a voucher code' . "\r\n";
	}

	if (empty($response['error'])) {
		//Check for virtual
		$coupon_wp = new WC_Coupon($voucher_code);
		$date_created = $coupon_wp->get_date_created();
		if ($date_created) {
			$response['balance'] = $coupon_wp->get_amount();
			$response['original_balance'] = get_post_meta($coupon_wp->get_id(), 'original_amount', true);
			$status = ($coupon_wp->get_amount() > 0) ? 'active' : 'cancelled_used';
			$response['voucher_status'] = $status;
			$response['voucher_code'] = $voucher_code;
			$response['status_code'] = 200;
		}
	} else {
		$response['error'] = 'invalid request' . "\r\n" . $response['error'];
	}

	return rest_ensure_response($response);
}

function gc4t_test_create_gift_card($data) {
	$response = array(
		'status_code' => 400,
		'error' => '',
		'voucher_code' => '',
		'reference' => '',
		'order_id' => '',
	);

	$params = $data->get_json_params();
	$sanitized = array_map('sanitize_text_field', $params);
	
	$notes = (isset($sanitized['reference'])) ? $sanitized['reference'] : '';
	$value = (isset($sanitized['value'])) ? $sanitized['value'] : 0;
	$value = (is_numeric($value)) ? $value : 0;

	//Validation
	if ($value <= 0) {
		$response['error'] .= 'Please enter a value greater than 0.00.' . "\r\n";
	}
	if ($value > 1000) {
		$response['error'] .= 'Please enter a value less than 1000.00.' . "\r\n";
	}
	if (empty($notes)) {
		$response['error'] .= 'Please enter a reference for the transaction.' . "\r\n";
	}
	if (strlen($notes) > 100) {
		$response['error'] .= 'Please enter a reference less than 100 characters' . "\r\n";
	}

	if (empty($response['error'])) {
		$sandbox_order_id = get_option( 'gctf_sandbox_order_id', 1 );
		$sandbox_test_codes = get_option( 'gctf_sandbox_test_codes', '[]' );
		$sandbox_test_codes = json_decode( $sandbox_test_codes, true );
		$sandbox_test_codes = ( $sandbox_test_codes ) ? $sandbox_test_codes : array();

		$sandbox_test_orders = get_option( 'gctf_sandbox_test_orders', '[]' );
		$sandbox_test_orders = json_decode( $sandbox_test_orders, true );
		$sandbox_test_orders = ( $sandbox_test_orders ) ? $sandbox_test_orders : array();

		$user_giftcard_code = mt_rand(100, 999).'b'.mt_rand(100, 999).'y'.mt_rand(100, 999).'c'.mt_rand(100, 999);
		$response['status_code'] = 200;
		$response['voucher_code'] = $user_giftcard_code;
		$response['order_id'] = 'TEST-' . $sandbox_order_id;
		$response['reference'] = $sanitized['reference'];
		$lower = strtolower($user_giftcard_code);
		$sandbox_test_codes[$lower] = [
			'code' => $lower,
			'value' => $value,
			'original_amount' => $value,
			'order_ref' => 'TEST-' . $sandbox_order_id,
			'order_id' => $sandbox_order_id,
			'reference' => $sanitized['reference'],
			'date_created' => date('Y-m-d H:i:s'),
		];

		$sandbox_test_orders['test-' . $sandbox_order_id] = $lower;
		update_option( 'gctf_sandbox_test_codes', json_encode( $sandbox_test_codes ) );
		update_option( 'gctf_sandbox_test_orders', json_encode( $sandbox_test_orders ) );
		update_option( 'gctf_sandbox_order_id', $sandbox_order_id + 1 );
		
	} else {
		$response['error'] = 'invalid request' . "\r\n" . $response['error'];
	}

	return rest_ensure_response($response);
}

function gc4t_test_create_gift_card_eur($data) {
	$response = array(
		'status_code' => 400,
		'error' => '',
		'voucher_code' => '',
		'reference' => '',
		'order_id' => '',
	);

	$params = $data->get_json_params();
	$sanitized = array_map('sanitize_text_field', $params);
	
	$notes = (isset($sanitized['reference'])) ? $sanitized['reference'] : '';
	$value = (isset($sanitized['value'])) ? $sanitized['value'] : 0;
	$value = (is_numeric($value)) ? $value : 0;

	//Validation
	if ($value <= 0) {
		$response['error'] .= 'Please enter a value greater than 0.00.' . "\r\n";
	}
	if ($value > 1000) {
		$response['error'] .= 'Please enter a value less than 1000.00.' . "\r\n";
	}
	if (empty($notes)) {
		$response['error'] .= 'Please enter a reference for the transaction.' . "\r\n";
	}
	if (strlen($notes) > 100) {
		$response['error'] .= 'Please enter a reference less than 100 characters' . "\r\n";
	}

	if (empty($response['error'])) {
		$sandbox_order_id = get_option( 'gctf_sandbox_order_id', 1 );
		$sandbox_test_codes = get_option( 'gctf_sandbox_test_codes', '[]' );
		$sandbox_test_codes = json_decode( $sandbox_test_codes, true );
		$sandbox_test_codes = ( $sandbox_test_codes ) ? $sandbox_test_codes : array();

		$sandbox_test_orders = get_option( 'gctf_sandbox_test_orders', '[]' );
		$sandbox_test_orders = json_decode( $sandbox_test_orders, true );
		$sandbox_test_orders = ( $sandbox_test_orders ) ? $sandbox_test_orders : array();

		$user_giftcard_code = mt_rand(100, 999).'b'.mt_rand(100, 999).'y'.mt_rand(100, 999).'c'.mt_rand(100, 999);
		$response['status_code'] = 200;
		$response['voucher_code'] = $user_giftcard_code;
		$response['order_id'] = 'TEST-' . $sandbox_order_id;
		$response['reference'] = $sanitized['reference'];
		$lower = strtolower($user_giftcard_code);
		$sandbox_test_codes[$lower] = [
			'code' => $lower,
			'value' => $value,
			'original_amount' => $value,
			'order_ref' => 'TEST-' . $sandbox_order_id,
			'order_id' => $sandbox_order_id,
			'reference' => $sanitized['reference'],
			'date_created' => date('Y-m-d H:i:s'),
		];

		$sandbox_test_orders['test-' . $sandbox_order_id] = $lower;
		update_option( 'gctf_sandbox_test_codes', json_encode( $sandbox_test_codes ) );
		update_option( 'gctf_sandbox_test_orders', json_encode( $sandbox_test_orders ) );
		update_option( 'gctf_sandbox_order_id', $sandbox_order_id + 1 );
		
	} else {
		$response['error'] = 'invalid request' . "\r\n" . $response['error'];
	}

	return rest_ensure_response($response);
}

function gc4t_create_gift_card_eur($data) {
	$response = array(
		'status_code' => 400,
		'error' => '',
		'voucher_code' => '',
		'reference' => '',
		'order_id' => '',
	);

	$params = $data->get_json_params();
	$sanitized = array_map('sanitize_text_field', $params);
	
	$notes = (isset($sanitized['reference'])) ? $sanitized['reference'] : '';
	$value = (isset($sanitized['value'])) ? $sanitized['value'] : 0;
	$value = (is_numeric($value)) ? $value : 0;

	//Validation
	if ($value <= 0) {
		$response['error'] .= 'Please enter a value greater than 0.00.' . "\r\n";
	}
	if ($value > 1000) {
		$response['error'] .= 'Please enter a value less than 1000.00.' . "\r\n";
	}
	if (empty($notes)) {
		$response['error'] .= 'Please enter a reference for the transaction.' . "\r\n";
	}
	if (strlen($notes) > 100) {
		$response['error'] .= 'Please enter a reference less than 100 characters' . "\r\n";
	}

	if (empty($response['error'])) {
		$customer_id = get_current_user_id();
		$customer = new WC_Customer($customer_id);
		if ($customer) {
			$ordered_products = array();
	
			$result = gctcf_convert_amount($value, 'EUR', 'GBP');
			$order_details = array(
				'customer_id' => $customer->get_id(),
				'billing_first_name' => $customer->get_billing_first_name(),
				'billing_last_name' => $customer->get_billing_last_name(),
				'billing_company' => $customer->get_billing_company(),
				'billing_address_1' => $customer->get_billing_address_1(),
				'billing_address_2' => $customer->get_billing_address_2(),
				'billing_city' => $customer->get_billing_city(),
				'billing_state' => $customer->get_billing_state(),
				'billing_postcode' => $customer->get_billing_postcode(),
				'billing_country' => $customer->get_billing_country(),
				'billing_email' => $customer->get_billing_email(),
				'billing_phone' => $customer->get_billing_phone(),
				'shipping_first_name' => $customer->get_shipping_first_name(),
				'shipping_last_name' => $customer->get_shipping_last_name(),
				'shipping_address_1' => $customer->get_shipping_address_1(),
				'shipping_address_2' => $customer->get_shipping_address_2(),
				'shipping_city' => $customer->get_shipping_city(),
				'shipping_state' => $customer->get_shipping_state(),
				'shipping_postcode' => $customer->get_shipping_postcode(),
				'shipping_country' => $customer->get_shipping_country(),
			);
	
			//Product ID
			$product_id = 19033;
			$wc_product = wc_get_product($product_id);
			if ($wc_product) {
				$ordered_products[] = array(
					'qty' => 1,
					'price' => $result['value'],
					'product_id' => $product_id,
					'subtotal' => $result['value'],
					'product' => $wc_product,
				);
	
				//Create Order
				$order = wc_create_order();
				$order->set_customer_id($customer->get_id());
				$order->set_billing_first_name( $order_details['billing_first_name'] );
				$order->set_billing_last_name( $order_details['billing_last_name'] );
				$order->set_billing_company( $order_details['billing_company'] );
				$order->set_billing_address_1( $order_details['billing_address_1'] );
				$order->set_billing_address_2( $order_details['billing_address_2'] );
				$order->set_billing_city( $order_details['billing_city'] );
				$order->set_billing_state( $order_details['billing_state'] );
				$order->set_billing_postcode( $order_details['billing_postcode'] );
				$order->set_billing_country( $order_details['billing_country'] );
				$order->set_billing_email( $order_details['billing_email'] );
				$order->set_billing_phone( $order_details['billing_phone'] );
	
				$order->set_shipping_first_name( $order_details['shipping_first_name'] );
				$order->set_shipping_last_name( $order_details['shipping_last_name'] );
				$order->set_shipping_address_1( $order_details['shipping_address_1'] );
				$order->set_shipping_address_2( $order_details['shipping_address_2'] );
				$order->set_shipping_city( $order_details['shipping_city'] );
				$order->set_shipping_state( $order_details['shipping_state'] );
				$order->set_shipping_postcode( $order_details['shipping_postcode'] );
				$order->set_shipping_country( $order_details['shipping_country'] );
	
				foreach ($ordered_products as $ordered_product) {
					$order->add_product($ordered_product['product'], $ordered_product['qty'], array(
						'subotal' => $ordered_product['subtotal'],
						'total' => $ordered_product['subtotal'],
					));
				}
				$order->add_order_note($notes, false, true);
				$order->calculate_totals();
				$order->save();
				$order->update_status('completed', $sanitized['reference'], true);
	
				update_post_meta($order->get_id(), 'created_by', get_current_user_id());
				update_post_meta($order->get_id(), 'method', 'api');
				update_post_meta($order->get_id(), 'reference', $sanitized['reference']);

				//Create Voucher
				$user_giftcard_code = mt_rand(100, 999).'b'.mt_rand(100, 999).'y'.mt_rand(100, 999).'c'.mt_rand(100, 999);
				update_post_meta( $order->get_id(), 'byconsole_giftcard_amount', $result['value'] );
				update_post_meta( $order->get_id(), 'byconsole_giftcard_message', $notes );
				update_post_meta( $order->get_id(), 'byconsole_giftcard_first_name', $order_details['billing_first_name'] );
				update_post_meta( $order->get_id(), 'byconsole_giftcard_last_name', $order_details['billing_last_name'] );
				update_post_meta( $order->get_id(), 'byconsole_giftcard_email', $order_details['billing_email'] );
				update_post_meta( $order->get_id(), 'user_giftcard_code',$user_giftcard_code );
				update_post_meta( $order->get_id(), 'gc4t_currency', 'EUR' );
				update_post_meta( $order->get_id(), 'gc4t_currency_orginal_amount', $value );
				update_post_meta( $order->get_id(), 'gc4t_currency_amount', $value );
				update_post_meta( $order->get_id(), 'gc4t_conversion_date', $result['date']);
				update_post_meta( $order->get_id(), 'gc4t_conversion_rate', $result['rate']);
				update_post_meta( $order->get_id(), 'gc4t_conversion_value', $result['value']);

				//Convert to coupon
				$discount_type = 'fixed_cart'; // Type: fixed_cart, percent, fixed_product, percent_product
				$byc_coupon = array(
					'post_title' => $user_giftcard_code,
					'post_excerpt' => $notes,
					/*'post_content' => 'Created from ',*/
					'post_status' => 'publish',
					'post_author' => get_current_user_id(),
					'post_type'		=> 'shop_coupon', 
				);
				$byc_coupon_id = wp_insert_post( $byc_coupon );
				update_post_meta( $byc_coupon_id, 'discount_type', $discount_type );
				update_post_meta( $byc_coupon_id, 'coupon_amount', $result['value'] );
				update_post_meta( $byc_coupon_id, 'individual_use', 'no' );
				update_post_meta( $byc_coupon_id, 'product_ids', '' );
				update_post_meta( $byc_coupon_id, 'exclude_product_ids', '' );
				update_post_meta( $byc_coupon_id, 'usage_limit', '' );
				update_post_meta( $byc_coupon_id, 'expiry_date', '' );
				update_post_meta( $byc_coupon_id, 'apply_before_tax', 'yes' );
				update_post_meta( $byc_coupon_id, 'free_shipping', 'no' );
				update_post_meta( $byc_coupon_id, 'original_amount', $result['value'] );
				update_post_meta( $byc_coupon_id, 'gc4t_currency', 'EUR' );
				update_post_meta( $byc_coupon_id, 'gc4t_currency_original_amount', $value );
				update_post_meta( $byc_coupon_id, 'gc4t_currency_amount', $value );
				update_post_meta( $byc_coupon_id, 'gc4t_conversion_date', $result['date']);
				update_post_meta( $byc_coupon_id, 'gc4t_conversion_rate', $result['rate']);
				update_post_meta( $byc_coupon_id, 'gc4t_conversion_value', $result['value']);

				$response['status_code'] = 200;
				$response['voucher_code'] = $user_giftcard_code;
				$response['order_id'] = 'GC4T-' . $order->get_id();
				$response['reference'] = $sanitized['reference'];
			}
		}
	} else {
		$response['error'] = 'invalid request' . "\r\n" . $response['error'];
	}

	return rest_ensure_response($response);
}

function gc4t_create_gift_card($data) {
	$response = array(
		'status_code' => 400,
		'error' => '',
		'voucher_code' => '',
		'reference' => '',
		'order_id' => '',
	);

	$params = $data->get_json_params();
	$sanitized = array_map('sanitize_text_field', $params);
	
	$notes = (isset($sanitized['reference'])) ? $sanitized['reference'] : '';
	$value = (isset($sanitized['value'])) ? $sanitized['value'] : 0;
	$value = (is_numeric($value)) ? $value : 0;

	//Validation
	if ($value <= 0) {
		$response['error'] .= 'Please enter a value greater than 0.00.' . "\r\n";
	}
	if ($value > 1000) {
		$response['error'] .= 'Please enter a value less than 1000.00.' . "\r\n";
	}
	if (empty($notes)) {
		$response['error'] .= 'Please enter a reference for the transaction.' . "\r\n";
	}
	if (strlen($notes) > 100) {
		$response['error'] .= 'Please enter a reference less than 100 characters' . "\r\n";
	}

	if (empty($response['error'])) {
		$customer_id = get_current_user_id();
		$customer = new WC_Customer($customer_id);
		if ($customer) {
			$ordered_products = array();
	
			$order_details = array(
				'customer_id' => $customer->get_id(),
				'billing_first_name' => $customer->get_billing_first_name(),
				'billing_last_name' => $customer->get_billing_last_name(),
				'billing_company' => $customer->get_billing_company(),
				'billing_address_1' => $customer->get_billing_address_1(),
				'billing_address_2' => $customer->get_billing_address_2(),
				'billing_city' => $customer->get_billing_city(),
				'billing_state' => $customer->get_billing_state(),
				'billing_postcode' => $customer->get_billing_postcode(),
				'billing_country' => $customer->get_billing_country(),
				'billing_email' => $customer->get_billing_email(),
				'billing_phone' => $customer->get_billing_phone(),
				'shipping_first_name' => $customer->get_shipping_first_name(),
				'shipping_last_name' => $customer->get_shipping_last_name(),
				'shipping_address_1' => $customer->get_shipping_address_1(),
				'shipping_address_2' => $customer->get_shipping_address_2(),
				'shipping_city' => $customer->get_shipping_city(),
				'shipping_state' => $customer->get_shipping_state(),
				'shipping_postcode' => $customer->get_shipping_postcode(),
				'shipping_country' => $customer->get_shipping_country(),
			);
	
			//Product ID
			$product_id = 204;
			$wc_product = wc_get_product($product_id);
			if ($wc_product) {
				$ordered_products[] = array(
					'qty' => 1,
					'price' => $value,
					'product_id' => $product_id,
					'subtotal' => $value,
					'product' => $wc_product,
				);
	
				//Create Order
				$order = wc_create_order();
				$order->set_customer_id($customer->get_id());
				$order->set_billing_first_name( $order_details['billing_first_name'] );
				$order->set_billing_last_name( $order_details['billing_last_name'] );
				$order->set_billing_company( $order_details['billing_company'] );
				$order->set_billing_address_1( $order_details['billing_address_1'] );
				$order->set_billing_address_2( $order_details['billing_address_2'] );
				$order->set_billing_city( $order_details['billing_city'] );
				$order->set_billing_state( $order_details['billing_state'] );
				$order->set_billing_postcode( $order_details['billing_postcode'] );
				$order->set_billing_country( $order_details['billing_country'] );
				$order->set_billing_email( $order_details['billing_email'] );
				$order->set_billing_phone( $order_details['billing_phone'] );
	
				$order->set_shipping_first_name( $order_details['shipping_first_name'] );
				$order->set_shipping_last_name( $order_details['shipping_last_name'] );
				$order->set_shipping_address_1( $order_details['shipping_address_1'] );
				$order->set_shipping_address_2( $order_details['shipping_address_2'] );
				$order->set_shipping_city( $order_details['shipping_city'] );
				$order->set_shipping_state( $order_details['shipping_state'] );
				$order->set_shipping_postcode( $order_details['shipping_postcode'] );
				$order->set_shipping_country( $order_details['shipping_country'] );
	
				foreach ($ordered_products as $ordered_product) {
					$order->add_product($ordered_product['product'], $ordered_product['qty'], array(
						'subotal' => $ordered_product['subtotal'],
						'total' => $ordered_product['subtotal'],
					));
				}
				$order->add_order_note($notes, false, true);
				$order->calculate_totals();
				$order->save();
				$order->update_status('completed', $sanitized['reference'], true);
	
				update_post_meta($order->get_id(), 'created_by', get_current_user_id());
				update_post_meta($order->get_id(), 'method', 'api');
				update_post_meta($order->get_id(), 'reference', $sanitized['reference']);

				//Create Voucher
				$user_giftcard_code = mt_rand(100, 999).'b'.mt_rand(100, 999).'y'.mt_rand(100, 999).'c'.mt_rand(100, 999);
				update_post_meta( $order->get_id(), 'byconsole_giftcard_amount', $value );
				update_post_meta( $order->get_id(), 'byconsole_giftcard_message', $notes );
				update_post_meta( $order->get_id(), 'byconsole_giftcard_first_name', $order_details['billing_first_name'] );
				update_post_meta( $order->get_id(), 'byconsole_giftcard_last_name', $order_details['billing_last_name'] );
				update_post_meta( $order->get_id(), 'byconsole_giftcard_email', $order_details['billing_email'] );
				update_post_meta( $order->get_id(), 'user_giftcard_code',$user_giftcard_code );

				//Convert to coupon
				$discount_type = 'fixed_cart'; // Type: fixed_cart, percent, fixed_product, percent_product
				$byc_coupon = array(
					'post_title' => $user_giftcard_code,
					'post_excerpt' => $notes,
					/*'post_content' => 'Created from ',*/
					'post_status' => 'publish',
					'post_author' => get_current_user_id(),
					'post_type'		=> 'shop_coupon', 
				);
				$byc_coupon_id = wp_insert_post( $byc_coupon );
				update_post_meta( $byc_coupon_id, 'discount_type', $discount_type );
				update_post_meta( $byc_coupon_id, 'coupon_amount', $value );
				update_post_meta( $byc_coupon_id, 'individual_use', 'no' );
				update_post_meta( $byc_coupon_id, 'product_ids', '' );
				update_post_meta( $byc_coupon_id, 'exclude_product_ids', '' );
				update_post_meta( $byc_coupon_id, 'usage_limit', '' );
				update_post_meta( $byc_coupon_id, 'expiry_date', '' );
				update_post_meta( $byc_coupon_id, 'apply_before_tax', 'yes' );
				update_post_meta( $byc_coupon_id, 'free_shipping', 'no' );
				update_post_meta( $byc_coupon_id, 'original_amount', $value );

				$response['status_code'] = 200;
				$response['voucher_code'] = $user_giftcard_code;
				$response['order_id'] = 'GC4T-' . $order->get_id();
				$response['reference'] = $sanitized['reference'];
			}
		}
	} else {
		$response['error'] = 'invalid request' . "\r\n" . $response['error'];
	}

	return rest_ensure_response($response);
}

//API Checker
add_action('init', 'gc4t_start_api_check_event');
add_action( 'gc4t_api_checker', 'gc4t_do_api_check');

//Scheduled send
add_action('init', 'gc4t_start_giftcard_send_event');
add_action( 'gc4t_giftcard_sender', 'gc4t_do_giftcard_send');

function gc4t_do_giftcard_send() {
	$order_ids = get_posts( array(
		'post_type' => 'shop_order',
		'posts_per_page' => -1,
		'post_status' => array(
			'wc-completed',
		),
		'meta_query' => array(
			array(
				'key' => 'byconsole_giftcard_schedule_send',
				'value' => date('Y-m-d'),
				'compare' => '<=',
			),
			array(
				'key' => 'gctf_send_status',
				'value' => 'sent',
				'compare' => '!=',
			),
		),
		'fields' => 'ids',
	) );

	if ( $order_ids ) {
		foreach ( $order_ids as $order_id ) {
			$order = wc_get_order($order_id);
			if ( $order ) {
				$is_egiftcard = false;
				if (!empty($order->get_items())) {
					foreach ($order->get_items() as $item_id => $item) {

						$product = $item->get_product();
						$attr_type = $product->get_variation_attributes();
						if (!empty($attr_type) && isset($attr_type['attribute_type']) && $attr_type['attribute_type'] == 'eGiftcard') {
							$is_egiftcard = true;
						}
					}
				}

				if ($is_egiftcard) {
					$email  = get_post_meta($order->get_id(), 'byconsole_giftcard_email', true);
					$price = get_post_meta($order->get_id(), 'byconsole_giftcard_amount', true);
					$code = get_post_meta($order->get_id(), 'user_giftcard_code', true);
					$message = get_post_meta($order->get_id(), 'byconsole_giftcard_message', true);
					$first_name = get_post_meta($order->get_id(), 'byconsole_giftcard_first_name', true);
					$last_name = get_post_meta($order->get_id(), 'byconsole_giftcard_last_name', true);
					$name = $first_name . ' ' . $last_name;
					$recipient_name = trim($name);
					ob_start();
					include GCTCF_PATH . 'public/partials/gctcf-giftcard-email.php';
					$html = ob_get_clean();

					$mail_headers  = "MIME-Version: 1.0" . "\r\n";
					$mail_headers .= "Content-type: text/html; charset=" . get_bloginfo('charset') . "" . "\r\n";
					$mail_headers .= "From: " . get_bloginfo() . " <" . get_bloginfo('admin_email') . ">" . "\r\n";
					$to = array($email);
					if ( $order->has_status('completed') ) {
						update_post_meta( $order->get_id(), 'gctf_send_status', 'sent' );
						wp_mail($to, 'New GiftCard', $html, $mail_headers);
					}
				}
			}
		}
	}
}

function gc4t_do_api_check() {
	if (date('H') == 12 || date('H') == 17 || isset($_GET['do_api_check'])) {
		$statuses = array(
			'stuba' => 'FAIL',
			'travellanda' => 'FAIL',
			'a2b_transfers' => 'FAIL',
			'dca_car_hire' => 'FAIL',
			'dsd_attractions' => 'FAIL',
		);
		//Test Stuba
		$settings = get_api_config();
		$api_url = $settings['url'];
		$api_org = $settings['org'];
		$api_user = $settings['user'];
		$api_pass = $settings['pass'];
		$user_xmldata = '<?xml version="1.0" encoding="utf-8"?>
		<AvailabilitySearch>
			<Authority>
				<Org>'.$api_org.'</Org>
				<User>'.$api_user.'</User>
				<Password>'.$api_pass.'</Password>
				<Currency>GBP</Currency>
				<Language>en</Language>
				<TestMode>false</TestMode>
				<DebugMode>false</DebugMode>
				<Version>1.28</Version>
			</Authority>
			<RegionId>52893</RegionId>
			<HotelStayDetails>
				<ArrivalDate>'.date('Y-m-d', strtotime('today +1 month')).'</ArrivalDate>
				<Nights>1</Nights>
				<Nationality>GB</Nationality>'.
				'<Room>
					<Guests>
						<Adult />
					</Guests>
				</Room>
			</HotelStayDetails>
			<DetailLevel>basic</DetailLevel>'.
			'<MaxHotels>5</MaxHotels>'
		.'</AvailabilitySearch>';
		$roomxml_api_url= $api_url;
		$host = [implode(':', ['api.roomsxml.com',443, gethostbyname('api.roomsxml.com')])];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $roomxml_api_url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_ENCODING,  '');
		curl_setopt($ch, CURLOPT_TCP_FASTOPEN, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $user_xmldata);
		curl_setopt($ch, CURLOPT_RESOLVE, $host);
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-type: text/xml',
		));
	
		$room_xml_api_output_for_hotel_search = curl_exec($ch);
		if (stripos($room_xml_api_output_for_hotel_search, '<Currency>GBP</Currency>') !== false) {
			$statuses['stuba'] = 'OK';
		}
	
		//Test Travellanda
		require_once GCTCF_PATH . '/includes/Travellanda.class.php';
	
		$travellanda = new Travellanda();
		$travellanda->setUsername('7e86375d19e083c8397787af1f24398f');
		$travellanda->setPassword('BYpoB34ta9T7');
		$hotel_date_time = date_create_from_format('Y-m-d', date('Y-m-d', strtotime('today +1 month')));
		$hotel_date_time->add(new DateInterval('P1D'));
		$search_params = array(
			'type' => 'city',
			'locations' => array('163202'),
			'check_in_date' => date('Y-m-d', strtotime('today +1 month')),
			'check_out_date' => $hotel_date_time->format('Y-m-d'),
			'available_only' => true,
			'currency' => 'GBP',
			'nationality' => 'GB',
			'rooms' => array(
				array(
					'adult' => 1,
				)
			),
		);
		$travellanda_results = $travellanda->hotelSearch($search_params);
		$travellanda_results = $travellanda_results['body'];
		//echo '<pre>' . print_r(htmlentities($travellanda_results), true) . '</pre>';
		if (stripos($travellanda_results, '<Currency>GBP</Currency>') !== false) {
			$statuses['travellanda'] = 'OK';
		}
	
		//Test A2B Transfers
		$settings = get_transfer_api_config();
	
		$user_xmldata = '<?xml version="1.0" encoding="UTF-8"?><TCOML version="NEWFORMAT">
			<TransferOnly>
			<Availability>
			<Request>
			<Username>'.$settings['user'].'</Username>
			<Password>'.$settings['pass'].'</Password>
			<Lang>EN</Lang>
			<DeparturePointCode>LGW</DeparturePointCode>
			<ArrivalPointCode>NVG</ArrivalPointCode>
			<SectorType>SINGLE</SectorType>
			<ArrDate>' . date('d/m/Y', strtotime('today +1 month')) . '</ArrDate>
			<RetDate></RetDate>
			<ArrTime>08:30</ArrTime>
			<RetTime>0</RetTime>
			<Adults>1</Adults>
			<Children>0</Children>
			<Infants>0</Infants>
			<Vehicletype>1</Vehicletype>
			<Latitude>39.735000</Latitude>
			<Longitude>2.760000</Longitude>
			</Request>
			</Availability>
			</TransferOnly>
		</TCOML>';
	
		//echo htmlentities($user_xmldata);
		$p2p_api_url = $settings['url'];
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $p2p_api_url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_HTTPGET, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $user_xmldata);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-type: text/xml',
			'Content-length: ' . strlen($user_xmldata)
		));
	
		$a2b_xml_api_output_for_hotel_search = curl_exec($ch);   
	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
		curl_close($ch);
	
		if (stripos($a2b_xml_api_output_for_hotel_search, '<TransferOnly>') !== false) {
			$statuses['a2b_transfers'] = 'OK';
		}
	
		//Car hire
		$body = array(
			'DateFrom' => date('d.m.Y', strtotime('today +1 month')) . 'T08:30',
			'DateTo' => date('d.m.Y', strtotime('tomorrow +1 month')) . 'T08:30',
			"PickupLocationID" =>  8924,
			"DropOffLocationID" =>  8924,
			"CurrencyCode" =>  "GBP",
			"Pos" =>  "UK",
	
		);
	
		$body = json_encode($body);
		$dca = new Discover_Cars_Api_Methods();
		$cars = $dca->get_cars($body);
		if ($cars) {
			$statuses['dca_car_hire'] = 'OK';
		}
		//echo '<pre>' . print_r($statuses, true) . '</pre>';

		//Attractions
		$attraction_settings = get_option('attraction_api_option');
		$attraction_api_url = rtrim($attraction_settings['attraction_api_url'],"/"); 
		$attraction_api_user = $attraction_settings['attraction_api_username']; 
		$attraction_api_pass = $attraction_settings['attraction_api_password'];

		$response = wp_remote_get( $attraction_api_url.'/products?limit=10',
			array(
				'timeout'     => 120,
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( $attraction_api_user.':'.$attraction_api_pass),
				),
			)
		);
		$res = wp_remote_retrieve_body( $response );
		$data = json_decode($res, true);
		if (isset($data['meta']['count'])) {
			$statuses['dsd_attractions'] = 'OK';
		}
	
		$email = 'rob@giftcards4travel.co.uk';
		$date = date('Y-m-d H:i:s');
		$message = "GiftCards4Travel Has Run An API TEST - $date\r\n\r\n";
		$message .= "STUBA Hotels: {$statuses['stuba']}\r\n";
		$message .= "Travellanda Hotels: {$statuses['travellanda']}\r\n";
		$message .= "A2B Hotel Transfers: {$statuses['a2b_transfers']}\r\n";
		$message .= "DCA Car Hire: {$statuses['dca_car_hire']}\r\n";
		$message .= "Do Something Different Attractions: {$statuses['dsd_attractions']}\r\n";
		$message = nl2br($message);
		$headers = array('Content-Type: text/html; charset=UTF-8');
		$headers[] = 'From: GiftCards4Travel <website@giftcards4travel.co.uk>';
		wp_mail('robstransport4@gmail.com', 'GC4T API Test - ' . $date, $message, $headers);
		//wp_mail('johnston.jasond@gmail.com', 'GC4T API Test - ' . $date, $message, $headers);
	}
}

function gc4t_start_api_check_event() {
	if (isset($_GET['start_api_check_event'])) {
		if (! wp_next_scheduled ( 'gc4t_api_checker')) {
			wp_schedule_event( time(), 'hourly', 'gc4t_api_checker');
		}
		echo 'Event started';
		exit;
	}

	if (isset($_GET['do_api_check'])) {
		gc4t_do_api_check();
	}
}

function gc4t_start_giftcard_send_event() {
	if (isset($_GET['start_giftcard_send_event'])) {
		if (! wp_next_scheduled ( 'gc4t_giftcard_sender')) {
			wp_schedule_event( time(), 'hourly', 'gc4t_giftcard_sender');
		}
		echo 'Giftcard Send Event started';
		exit;
	}

	if (isset($_GET['do_giftcard_send'])) {
		gc4t_do_giftcard_send();
	}
} 

add_action( 'phpmailer_init', 'gc4t_mailer_config', 10, 1);

function gc4t_mailer_config($mailer){
	if (isset($mailer->From) && $mailer->From == 'website@giftcards4travel.co.uk') {
		$mailer->IsSMTP();
		$mailer->Host = "giftcards4travel.co.uk"; // your SMTP server
		$mailer->Username = 'website@giftcards4travel.co.uk';
		$mailer->Password = 'L1s#wvkG$uGWgF';
		$mailer->SMTPAuth = true;
		$mailer->SMTPSecure = "";
		$mailer->SMTPAutoTLS = false; 
		$mailer->Port = 25;
		$mailer->SMTPDebug = 0; // write 0 if you don't want to see client/server communication in page
		$mailer->CharSet  = "utf-8";
	}
}

function gc4t_import_stuba_regions() {
	global $wpdb;
	$first = true;
	if (($handle = fopen(dirname(__FILE__) . '/stuba-hotels.csv', "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
			if (!$first) {
				$wpdb->insert(
					'hotel_region',
					array(
						'region_id' => esc_sql($data[0]),
						'region_name' => esc_sql($data[1]),
						'state' => esc_sql($data[2]),
						'country_id' => esc_sql($data[3]),
						'country_name' => esc_sql($data[4]),
					),
					array(
						'%d',
						'%s',
						'%s',
						'%d',
						'%s',
					)
				);
			}
			$first = false;
		}
		fclose($handle);
	}
	exit;
}

function gc4t_delete_duplicate_coupons() {
	$coupons = get_posts(array(
		'posts_per_page' => 2200,
		'offset' => $_GET['offset'],
		'post_type' => 'shop_coupon',
		'post_status' => 'any',
		'orderby' => 'ID',
		'order' => 'desc',
	));
	$duplicates = array();
	if ($coupons) {
		foreach ($coupons as $coupon) {
			$duplicates[$coupon->post_title]['coupon'] = $coupon->post_title;
			$duplicates[$coupon->post_title]['ids'][] = $coupon->ID;
			if (isset($duplicates[$coupon->post_title]['count'])) {
				$duplicates[$coupon->post_title]['count']++;
			} else {
				$duplicates[$coupon->post_title]['count'] = 1;
			}
		}
	}
	foreach ($duplicates as $coupon => $duplicate) {
		if ($duplicate['count'] > 1) {
			echo '<pre>' . print_r($duplicate, true) . '</pre>';
			for ($a = 0; $a < $duplicate['count'] - 1; $a++) {
				wp_delete_post($duplicate['ids'][$a], true);
			}
		}
	}


}

function gc4t_generate_gift_cards($data) {
	$response = array(
		'status_code' => 400,
		'error' => '',
		'voucher_code' => '',
		'reference' => '',
		'order_id' => '',
	);

	$sanitized = array_map('sanitize_text_field', $data);
	
	$notes = (isset($sanitized['reference'])) ? $sanitized['reference'] : '';
	$value = (isset($sanitized['value'])) ? $sanitized['value'] : 0;
	$value = (is_numeric($value)) ? $value : 0;

	//Validation
	if ($value <= 0) {
		//$response['error'] .= 'Please enter a value greater than 0.00.' . "\r\n";
	}
	if ($value > 1000) {
		$response['error'] .= 'Please enter a value less than 1000.00.' . "\r\n";
	}
	if (empty($response['error'])) {
		//$customer_id = get_current_user_id();
		//$customer = new WC_Customer($customer_id);
		for ($a = 0; $a < $data['number']; $a++) {
			set_time_limit(90);
			$reference = $notes . '-' . $data['value'] . '-' . sprintf('%03d', ($data['start'] + $a));

			//if ($customer) {
				$ordered_products = array();
				/*$order_details = array(
					'customer_id' => $customer->get_id(),
					'billing_first_name' => $customer->get_billing_first_name(),
					'billing_last_name' => $customer->get_billing_last_name(),
					'billing_company' => $customer->get_billing_company(),
					'billing_address_1' => $customer->get_billing_address_1(),
					'billing_address_2' => $customer->get_billing_address_2(),
					'billing_city' => $customer->get_billing_city(),
					'billing_state' => $customer->get_billing_state(),
					'billing_postcode' => $customer->get_billing_postcode(),
					'billing_country' => $customer->get_billing_country(),
					'billing_email' => $customer->get_billing_email(),
					'billing_phone' => $customer->get_billing_phone(),
					'shipping_first_name' => $customer->get_shipping_first_name(),
					'shipping_last_name' => $customer->get_shipping_last_name(),
					'shipping_address_1' => $customer->get_shipping_address_1(),
					'shipping_address_2' => $customer->get_shipping_address_2(),
					'shipping_city' => $customer->get_shipping_city(),
					'shipping_state' => $customer->get_shipping_state(),
					'shipping_postcode' => $customer->get_shipping_postcode(),
					'shipping_country' => $customer->get_shipping_country(),
				);*/
		
				//Product ID
				$product_id = 203;
				$wc_product = wc_get_product($product_id);
				if ($wc_product) {
					$ordered_products[] = array(
						'qty' => 1,
						'price' => $value,
						'product_id' => $product_id,
						'subtotal' => $value,
						'product' => $wc_product,
					);
		
					//Create Order
					/*$order = wc_create_order();
					$order->set_customer_id($customer->get_id());
					$order->set_billing_first_name( $order_details['billing_first_name'] );
					$order->set_billing_last_name( $order_details['billing_last_name'] );
					$order->set_billing_company( $order_details['billing_company'] );
					$order->set_billing_address_1( $order_details['billing_address_1'] );
					$order->set_billing_address_2( $order_details['billing_address_2'] );
					$order->set_billing_city( $order_details['billing_city'] );
					$order->set_billing_state( $order_details['billing_state'] );
					$order->set_billing_postcode( $order_details['billing_postcode'] );
					$order->set_billing_country( $order_details['billing_country'] );
					$order->set_billing_email( $order_details['billing_email'] );
					$order->set_billing_phone( $order_details['billing_phone'] );
		
					$order->set_shipping_first_name( $order_details['shipping_first_name'] );
					$order->set_shipping_last_name( $order_details['shipping_last_name'] );
					$order->set_shipping_address_1( $order_details['shipping_address_1'] );
					$order->set_shipping_address_2( $order_details['shipping_address_2'] );
					$order->set_shipping_city( $order_details['shipping_city'] );
					$order->set_shipping_state( $order_details['shipping_state'] );
					$order->set_shipping_postcode( $order_details['shipping_postcode'] );
					$order->set_shipping_country( $order_details['shipping_country'] );
		
					foreach ($ordered_products as $ordered_product) {
						$order->add_product($ordered_product['product'], $ordered_product['qty'], array(
							'subotal' => $ordered_product['subtotal'],
							'total' => $ordered_product['subtotal'],
						));
					}
					$order->add_order_note($reference, false, true);
					$order->calculate_totals();
					$order->save();
					$order->update_status('completed', $sanitized['reference'], true);
		
					update_post_meta($order->get_id(), 'created_by', get_current_user_id());
					update_post_meta($order->get_id(), 'method', 'api');
					update_post_meta($order->get_id(), 'reference', $sanitized['reference']);*/
	
					//Create Voucher
					$user_giftcard_code = mt_rand(100, 999).'b'.mt_rand(100, 999).'y'.mt_rand(100, 999).'c'.mt_rand(100, 999);
					/*update_post_meta( $order->get_id(), 'byconsole_giftcard_amount', $value );
					update_post_meta( $order->get_id(), 'byconsole_giftcard_message', $reference );
					update_post_meta( $order->get_id(), 'byconsole_giftcard_first_name', $order_details['billing_first_name'] );
					update_post_meta( $order->get_id(), 'byconsole_giftcard_last_name', $order_details['billing_last_name'] );
					update_post_meta( $order->get_id(), 'byconsole_giftcard_email', $order_details['billing_email'] );
					update_post_meta( $order->get_id(), 'user_giftcard_code',$user_giftcard_code );*/
	
					//Convert to coupon
					$discount_type = 'fixed_cart'; // Type: fixed_cart, percent, fixed_product, percent_product
					$byc_coupon = array(
						'post_title' => $user_giftcard_code,
						'post_excerpt' => $reference,
						'post_content' => $reference,
						'post_status' => 'draft',
						'post_author' => 6,
						'post_type'		=> 'shop_coupon', 
					);
					$byc_coupon_id = wp_insert_post( $byc_coupon );
					update_post_meta( $byc_coupon_id, 'discount_type', $discount_type );
					update_post_meta( $byc_coupon_id, 'coupon_amount', $value );
					update_post_meta( $byc_coupon_id, 'individual_use', 'no' );
					update_post_meta( $byc_coupon_id, 'product_ids', '' );
					update_post_meta( $byc_coupon_id, 'exclude_product_ids', '' );
					update_post_meta( $byc_coupon_id, 'usage_limit', '' );
					update_post_meta( $byc_coupon_id, 'expiry_date', '' );
					update_post_meta( $byc_coupon_id, 'apply_before_tax', 'yes' );
					update_post_meta( $byc_coupon_id, 'free_shipping', 'no' );
					update_post_meta( $byc_coupon_id, 'original_amount', $value );
	
					$response['status_code'] = 200;
					$response['voucher_code'] = $user_giftcard_code;
					$response['order_id'] = 'GC4T-' . $reference;
					$response['reference'] = $reference;

					file_put_contents(dirname(__FILE__) . '/generated.csv', $response['voucher_code'] . ',' . $response['reference'] . ',' . $response['order_id'] . ',' . $data['value'] . "\r\n", FILE_APPEND);
				}
			//}
		}
	} else {
		$response['error'] = 'invalid request' . "\r\n" . $response['error'];
	}
	exit;
}

function gc4t_generate_gift_cards_from_file($data) {
	//header( 'Content-Type: text/csv' );
	//header( 'Content-Disposition: attachment;filename=gc4t_gift_cards.csv');
	/*$fp = fopen('php://output', 'w');
	fputcsv($fp, array(
		'Voucher Code',
		'Reference',
		'Order ID',
		'Value',
	));*/
	$response = array(
		'status_code' => 400,
		'error' => '',
		'voucher_code' => '',
		'reference' => '',
		'order_id' => '',
	);
	echo '<pre>' . print_r($data, true) . '</pre>';
	$sanitized = array_map('sanitize_text_field', $data);
	$user_giftcard_code = $sanitized[0];
	if ($user_giftcard_code) {
		$exists = false;
		$coupon_wp = new WC_Coupon($user_giftcard_code);
		$date_created = $coupon_wp->get_date_created();
		if ($date_created) {
			$exists = true;
		}
	}
	echo '<pre>' . print_r($sanitized, true) . '</pre>';
	if (!$exists) {
		//$notes = (isset($sanitized[1])) ? $sanitized[1] : '';
		$value = (isset($sanitized[3])) ? $sanitized[3] : 0;
		$value = (is_numeric($value)) ? $value : 0;

		//Validation
		if ($value <= 0) {
			$response['error'] .= 'Please enter a value greater than 0.00.' . "\r\n";
		}
		if ($value > 1000) {
			$response['error'] .= 'Please enter a value less than 1000.00.' . "\r\n";
		}

		if (empty($response['error'])) {
			$customer_id = get_current_user_id();
			$customer = new WC_Customer($customer_id);

			set_time_limit(90);
			$reference = $sanitized[1];

			if ($customer) {
				$ordered_products = array();

				$order_details = array(
					'customer_id' => $customer->get_id(),
					'billing_first_name' => $customer->get_billing_first_name(),
					'billing_last_name' => $customer->get_billing_last_name(),
					'billing_company' => $customer->get_billing_company(),
					'billing_address_1' => $customer->get_billing_address_1(),
					'billing_address_2' => $customer->get_billing_address_2(),
					'billing_city' => $customer->get_billing_city(),
					'billing_state' => $customer->get_billing_state(),
					'billing_postcode' => $customer->get_billing_postcode(),
					'billing_country' => $customer->get_billing_country(),
					'billing_email' => $customer->get_billing_email(),
					'billing_phone' => $customer->get_billing_phone(),
					'shipping_first_name' => $customer->get_shipping_first_name(),
					'shipping_last_name' => $customer->get_shipping_last_name(),
					'shipping_address_1' => $customer->get_shipping_address_1(),
					'shipping_address_2' => $customer->get_shipping_address_2(),
					'shipping_city' => $customer->get_shipping_city(),
					'shipping_state' => $customer->get_shipping_state(),
					'shipping_postcode' => $customer->get_shipping_postcode(),
					'shipping_country' => $customer->get_shipping_country(),
				);

				//Product ID
				$product_id = 204;
				$wc_product = wc_get_product($product_id);

				if ($wc_product) {
					$ordered_products[] = array(
						'qty' => 1,
						'price' => $value,
						'product_id' => $product_id,
						'subtotal' => $value,
						'product' => $wc_product,
					);

					//Create Order
					$order = wc_create_order();
					$order->set_customer_id($customer->get_id());
					$order->set_billing_first_name( $order_details['billing_first_name'] );
					$order->set_billing_last_name( $order_details['billing_last_name'] );
					$order->set_billing_company( $order_details['billing_company'] );
					$order->set_billing_address_1( $order_details['billing_address_1'] );
					$order->set_billing_address_2( $order_details['billing_address_2'] );
					$order->set_billing_city( $order_details['billing_city'] );
					$order->set_billing_state( $order_details['billing_state'] );
					$order->set_billing_postcode( $order_details['billing_postcode'] );
					$order->set_billing_country( $order_details['billing_country'] );
					$order->set_billing_email( $order_details['billing_email'] );
					$order->set_billing_phone( $order_details['billing_phone'] );

					$order->set_shipping_first_name( $order_details['shipping_first_name'] );
					$order->set_shipping_last_name( $order_details['shipping_last_name'] );
					$order->set_shipping_address_1( $order_details['shipping_address_1'] );
					$order->set_shipping_address_2( $order_details['shipping_address_2'] );
					$order->set_shipping_city( $order_details['shipping_city'] );
					$order->set_shipping_state( $order_details['shipping_state'] );
					$order->set_shipping_postcode( $order_details['shipping_postcode'] );
					$order->set_shipping_country( $order_details['shipping_country'] );

					foreach ($ordered_products as $ordered_product) {
						$order->add_product($ordered_product['product'], $ordered_product['qty'], array(
							'subotal' => $ordered_product['subtotal'],
							'total' => $ordered_product['subtotal'],
						));
					}
					$order->add_order_note($reference, false, true);
					$order->calculate_totals();
					$order->save();
					$order->update_status('completed', $sanitized['reference'], true);

					update_post_meta($order->get_id(), 'created_by', get_current_user_id());
					update_post_meta($order->get_id(), 'method', 'api');
					update_post_meta($order->get_id(), 'reference', $sanitized['reference']);

					//Create Voucher
					//$user_giftcard_code = mt_rand(100, 999).'b'.mt_rand(100, 999).'y'.mt_rand(100, 999).'c'.mt_rand(100, 999);
					update_post_meta( $order->get_id(), 'byconsole_giftcard_amount', $value );
					update_post_meta( $order->get_id(), 'byconsole_giftcard_message', $reference );
					update_post_meta( $order->get_id(), 'byconsole_giftcard_first_name', $order_details['billing_first_name'] );
					update_post_meta( $order->get_id(), 'byconsole_giftcard_last_name', $order_details['billing_last_name'] );
					update_post_meta( $order->get_id(), 'byconsole_giftcard_email', $order_details['billing_email'] );
					update_post_meta( $order->get_id(), 'user_giftcard_code',$user_giftcard_code );

					//Convert to coupon
					$discount_type = 'fixed_cart'; // Type: fixed_cart, percent, fixed_product, percent_product
					$byc_coupon = array(
						'post_title' => $user_giftcard_code,
						'post_excerpt' => $reference,
						/*'post_content' => 'Created from ',*/
						'post_status' => 'publish',
						'post_author' => get_current_user_id(),
						'post_type'		=> 'shop_coupon', 
					);
					$byc_coupon_id = wp_insert_post( $byc_coupon );
					update_post_meta( $byc_coupon_id, 'discount_type', $discount_type );
					update_post_meta( $byc_coupon_id, 'coupon_amount', $value );
					update_post_meta( $byc_coupon_id, 'individual_use', 'no' );
					update_post_meta( $byc_coupon_id, 'product_ids', '' );
					update_post_meta( $byc_coupon_id, 'exclude_product_ids', '' );
					update_post_meta( $byc_coupon_id, 'usage_limit', '' );
					update_post_meta( $byc_coupon_id, 'expiry_date', '' );
					update_post_meta( $byc_coupon_id, 'apply_before_tax', 'yes' );
					update_post_meta( $byc_coupon_id, 'free_shipping', 'no' );
					update_post_meta( $byc_coupon_id, 'original_amount', $value );

					$response['status_code'] = 200;
					$response['voucher_code'] = $user_giftcard_code;
					$response['order_id'] = 'GC4T-' . $order->get_id();
					$response['reference'] = $reference;
					echo '<pre>' . print_r($response,true) . '</pre>';
					/*fputcsv($fp, array(
						$response['voucher_code'],
						$response['reference'],
						$response['order_id'],
						$data['value'],
					));*/
					file_put_contents(dirname(__FILE__) . '/generated_file.csv', $response['voucher_code'] . ',' . $response['reference'] . ',' . $response['order_id'] . ',' . $data['value'] . "\r\n", FILE_APPEND);
				}
			}
		} else {
			$response['error'] = 'invalid request' . "\r\n" . $response['error'];
		}
		//fclose($fp);
	}
}

function gc4t_init() {
	if (isset($_GET['getCountries'])) {
		require_once( dirname(__FILE__) . '/classes/Travellanda.class.php');

		$travellanda = new Travellanda();
		$travellanda->setUsername('7e86375d19e083c8397787af1f24398f');
		$travellanda->setPassword('BYpoB34ta9T7');

		$travellanda->getCountries();
	}

	if (isset($_GET['getCities'])) {
		require_once( dirname(__FILE__) . '/classes/Travellanda.class.php');

		$travellanda = new Travellanda();
		$travellanda->setUsername('7e86375d19e083c8397787af1f24398f');
		$travellanda->setPassword('BYpoB34ta9T7');

		$travellanda->getCities();
	}

	if (isset($_GET['getAllHotels'])) {
		require_once( dirname(__FILE__) . '/classes/Travellanda.class.php');

		$travellanda = new Travellanda();
		$travellanda->setUsername('7e86375d19e083c8397787af1f24398f');
		$travellanda->setPassword('BYpoB34ta9T7');

		$travellanda->getAllHotels();
	}

	if (isset($_GET['getHotels'])) {
		require_once( dirname(__FILE__) . '/classes/Travellanda.class.php');

		$travellanda = new Travellanda();
		$travellanda->setUsername('7e86375d19e083c8397787af1f24398f');
		$travellanda->setPassword('BYpoB34ta9T7');

		$travellanda->getHotels(416709);
	}

	if (isset($_GET['citySearch'])) {
		require_once( dirname(__FILE__) . '/classes/Travellanda.class.php');
		
		$travellanda = new Travellanda();
		$travellanda->setUsername('7e86375d19e083c8397787af1f24398f');
		$travellanda->setPassword('BYpoB34ta9T7');
		$travellanda->citySearch($_GET['citySearch']);
	}

	if (isset($_GET['import_stuba_regions'])) {
		gc4t_import_stuba_regions();
	}

	if (isset($_GET['generate_gift_cards'])) {
		ini_set('display_errors', 'On');
		gc4t_generate_gift_cards(array(
			'value' => $_POST['value'],
			'reference' => $_POST['reference'],
			'start' => $_POST['start'],
			'number' => $_POST['number'],
		));
	}
	
	if (isset($_GET['generate_gift_cards_from_file'])) {
		ini_set('display_errors', 'On');
		if (($handle = fopen(dirname(__FILE__) . '/' . $_GET['file'] . '.csv', "r")) !== FALSE) {
			$first = true;
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				if (!$first) {
					gc4t_generate_gift_cards_from_file($data);
					//break;
				}
				$first = false;
			}
			fclose($handle);
		}
		exit;
	}

	if (isset($_GET['delete_duplicate_coupons'])) {
		gc4t_delete_duplicate_coupons();
		exit;
	}

	if (isset($_GET['fix_card'])) {
		//update_post_meta($_GET['fix_card'], 'original_amount', $_GET['amount']);
		die;
	}
}

function gc4t_add_submenu_page() {
	add_submenu_page('edit.php?post_type=gc4t_store_voucher',
	'Generate Vouchers',
	'Generate Vouchers',
	'manage_options',
	'gc4t-generate-vouchers',
	'gc4t_generate_vouchers_page');
}

function gc4t_generate_vouchers_page() {
	?>
	<div class="wrap">
		<form action="<?php echo home_url() . '/?generate_gift_cards'; ?>" method="post">
		<h1>Generate vouchers</h1>
		<p>
			<label>Value (GBP):</label>
			<input type="number" name="value" placeholder="0">
		</p>
		<p>
			<label>Reference (eg. Tillo)</label>
			<input type="text" name="reference" placeholder="tiilo">
		</p>
		<p>
			<label>Starting number. (eg. 50)</label>
			<input type="text" name="start" placeholder="1">
		</p>
		<p>
			<label>Number: (How many vouchers)</label>
			<input type="number" name="number" placeholder="10">
		</p>
		<p>
			<input type="submit" value="Generate" name="generate" class="button button-primary button-large">
		</p>
		</form>
	</div>
	<?php
}

add_action('admin_menu', 'gc4t_add_submenu_page');
add_action('init', 'gc4t_init');

function gc4t_generate_attractions_gmc_feed() {
	if (isset($_GET['generate_attractions_gmc_feed'])) {
		set_time_limit(600);
		ini_set('max_execution_time', '900');
		$products = array();

		$attraction_settings = get_option('attraction_api_option');
		$attraction_api_url = rtrim($attraction_settings['attraction_api_url'],"/"); 
		$attraction_api_user = $attraction_settings['attraction_api_username']; 
		$attraction_api_pass = $attraction_settings['attraction_api_password'];

		$count = 0;
		$has_more = true;
		$loop_count = 0;
		$dest = sanitize_text_field($_GET['generate_attractions_gmc_feed']);
		//echo $dest;
		while ($has_more) {
			$response = wp_remote_get( $attraction_api_url.'/products?dest=' . $dest . '&limit=500&view=extended&offset=' . $count,
				array(
					'timeout'     => 120,
					'headers' => array(
						'Authorization' => 'Basic ' . base64_encode( $attraction_api_user.':'.$attraction_api_pass),
					),
				)
			);
			$res = wp_remote_retrieve_body( $response );
			$data = json_decode($res, true);

			if (isset($data['data'])) {
				foreach ($data['data'] as $result) {
					foreach ($result['tickets'] as $ticket) {
						$product = array(
							'id' => 'GF-' . $ticket['ticket_id'],
							'title' => $result['title'] . ' | ' . $ticket['type_description'],
							'description' => $result['desc_short'],
							'google_product_category' => 'Arts & Entertainment > Event Tickets',
							'link' => add_query_arg('id', $ticket['product_id'], get_page_link(get_page_by_path('attraction-details')->ID)),
							'image_link' => $result['img_sml'],
							'condition' => 'new',
							'availability' => 'in stock',
							'price' => $ticket['price_from'] . ' GBP',
							'brand' => 'Gift Cards 4 Travel',
							'product_type' => 'Travel Ticket',
							'mpn' => $ticket['sku'],
						);

						$products[$ticket['ticket_id']] = $product;
					}
				}
			}

			if (isset($data['meta']['count'])) {
				$count += $data['meta']['count'];
				if ($count >= $data['meta']['total_count']) {
					$has_more = false;
				}
			} else {
				$has_more = false;
			}

			$loop_count++;

			//fail safe
			if ($loop_count > 5) {
				$has_more = false;
			}
		}

		$product_count = count($products);
		//echo '<pre>' . print_r($product_count, true) . '</pre>';
		//echo '<pre>' . print_r($count, true) . '</pre>';

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment;filename=attraction_feed_' . strtolower(str_replace(' ', '_', $dest)) . '.csv');
		$handle = fopen('php://output', 'w');
		fputcsv($handle, array(
			'id',
			'title',
			'description',
			'google_product_category',
			'link',
			'image_link',
			'condition',
			'availability',
			'price',
			'brand',
			'product_type',
			'mpn',
		));
		foreach ($products as $product) {
			fputcsv($handle, $product);
		}
		fclose($handle);
		exit;
	}
}

add_action('init', 'gc4t_generate_attractions_gmc_feed');


 function gctcf_register_my_all_bokkings(){
	    add_menu_page( 
	        __( 'All Bookings', 'all_booking' ),
	        'All Bookings',
	        'manage_options',
	        'all-bookings',
	        'my_custom_menu_all_booking_page',
	        'dashicons-menu-alt3"',
	        40
	    ); 
	}
	add_action( 'admin_menu', 'gctcf_register_my_all_bokkings' );
	 
	/**
	 * Display a custom menu page
	 */
	function my_custom_menu_all_booking_page(){
		
	 	echo "<div class='wrap gctcf-all-bookings'>";          
	    echo "<h1>".__( 'All Bookings', 'all_booking' )."</h2>";
	    echo '<nav class="nav-tab-wrapper woo-nav-tab-wrapper nav-tab-active" >';
	            
		    $tabs = array( 'attraction_booking'=> 'Attraction Bookings', 'giftcard_booking' => 'Giftcard Bookings', 'gc4t_hotel_booking' => 'Hotel Bookings', 'transfer_booking' => 'Transfer Bookings' );
		    
		    $current = isset($_GET['tab']) ? $_GET['tab'] : 'attraction_booking';
		    
		    foreach( $tabs as $tab => $name ){
                  
		        $class = ( $tab == $current )    ? ' nav-tab-active' : '';
		        echo "<a class='nav-tab$class' href='?page=all-bookings&tab=$tab'>$name</a>";
	            
		    }
		echo '</nav>';
	      
	     if($current == 'gc4t_hotel_booking'){
	           $args = array(  
				        'post_type' => 'gc4t_hotel_booking',
				        'post_status' => 'private',
				        'posts_per_page' => -1,
				        'orderby' => 'date',
				        'order' => 'DESC',
				        'meta_query' => array(
				        						'relation' => 'AND',
				        						array(
											            'key'     => '_gctcf_booking_status',
											            'compare' => 'EXIST',
											        )
				        					)
				    );
				    $loop = get_posts( $args );

	                  if ($loop) {
				    	echo '<table id="myTable" class="display">
						    <thead>
						        <tr align="left">
						            <th>Full Name</th>
						            <th>Product</th>
						            <th>Supplier Reference Number</th>
						            <th>Email</th>
						            <th>Mobile Number</th>
						            <th>Action</th>
						        </tr>
						    </thead><tbody>';
						    
						  foreach ($loop as $value) {
				    		//$id = get_the_ID();
				    		$feed_type  = get_post_meta( $value->ID, 'gc4t_feed_type', true );
				    		if($feed_type == 'stuba')
				    		{
	                         	$booking_id = get_post_meta( $value->ID, 'gc4t_hotel_booking_id', true );
				    		}
				    		else
				    		{
	                        	$booking_id  = get_post_meta( $value->ID, 'gc4t_booking_id', true );
				    		}
	                        $email  = get_post_meta( $value->ID, 'gc4t_user_email', true );
	                        $phone_number  = get_post_meta( $value->ID, 'gc4t_user_phone', true );
	                        $first_name = get_post_meta( $value->ID, 'gc4t_user_first_name', true );
	                        $last_name = get_post_meta( $value->ID, 'gc4t_user_last_name', true );
	                        // exit;
				    		echo '<tr>
						             <td>' . $first_name . ' ' . ' ' . $last_name . '</td>
						             <td>'.$value->post_title .'</td>
						             <td>'.$booking_id .'</td>
						             <td>'.$email .'</td>
						             <td>'.$phone_number .'</td>
						             <td><a target="_blank" href="'.get_edit_post_link($value->ID).'">View</a></td>
						             					       
						            </tr>';
				    	}
				    	echo '</tbody></table>';
				    	
				    }
				 

		}
	     
		if($current == 'attraction_booking'){        
		           $args = array(  
				        'post_type' => 'attraction_booking',
				        'post_status' => 'publish',
				        'posts_per_page' => -1, 
				    );
				    $loop = get_posts( $args );
	                 
				    if ($loop) {
				    	echo '<table id="myTable" class="display">
						    <thead>
						        <tr align="left">
						            <th>Full Name</th>
						            <th>Product</th>
						            <th>Supplier Reference Number</th>
						            <th>Email</th>
						            <th>Mobile Number</th>
						            <th>Action</th>
						        </tr>
						    </thead><tbody>';
				    	foreach ($loop as $value) {
				    		$meta = get_post_meta( $value->ID );		 
				    		$fullname  = get_post_meta( $value->ID, '_gctcf_billing_info', true );
				    		$products  = get_post_meta( $value->ID, '_gctcf_ticket_data', true );
				    		$result = array_values(json_decode($products, true));			    		
				    		$booking_id  = get_post_meta( $value->ID, '_gctcf_booking_id', true );
				    		$email  = get_post_meta( $value->ID, '_gctcf_order_email', true );
				    		$phone_number  = get_post_meta( $value->ID, '_gctcf_billing_info', true );

				    	echo '<tr>
						        <td>'.$fullname['billing_fullname'] .'</td>
						        <td>'.$result[7] .'</td>
						        <td>'.$booking_id .'</td>
						        <td>'.$email .'</td>
						        <td>'.$phone_number['billing_phoneNumber'] .'</td>
						        <td><a target="_blank" href="'.get_edit_post_link($value->ID).'">View</a></td>					       
						       </tr> ';                   
				    	}
				    	echo '</tbody></table>';
				    }			 

		}
	      if($current == 'transfer_booking'){        
		           $args = array(  
				        'post_type' => 'transfer_booking',
				        'post_status' => 'private',
				        'posts_per_page' => -1, 
				    );
				    $loop = get_posts( $args );
	                 
	                 if ($loop) {
				    	echo '<table id="myTable" class="display">
						    <thead>
						        <tr align="left">
						            <th>Full Name</th>
						            <th>Product</th>
						            <th>Supplier Reference Number</th>
						            <th>Email</th>
						            <th>Mobile Number</th>
						            <th>Action</th>
						        </tr>
						    </thead><tbody>';
				    	foreach ($loop as $value) {
				    		$meta = get_post_meta( $value->ID );	
				    		$first_name  = get_post_meta( $value->ID, 'gc4t_client_first_name', true );
				    		$last_name  = get_post_meta( $value->ID, 'gc4t_client_last_name', true );
				    		$product_name  = get_post_meta( $value->ID, 'gc4t_client_property_name', true );
				    		$reference_number  = get_post_meta( $value->ID, 'gc4t_client_trans_no', true );
				    		$email  = get_post_meta( $value->ID, 'gc4t_client_email', true );
				    		$mobile_number  = get_post_meta( $value->ID, 'gc4t_client_phone_no', true );
				    		

				    	echo '<tr>
						         <td>' . $first_name . ' ' . ' ' . $last_name . '</td>
						         <td>'. $product_name .'</td>
						         <td>'. $reference_number .'</td>
						         <td>'. $email .'</td>
						         <td>'. $mobile_number .'</td>
						         <td><a target="_blank" href="'.get_edit_post_link($value->ID).'">View</a></td>
						        </tr> ';                   
				    	}
				    	echo '</tbody></table>';
				    }			 
	         }


	         if($current == 'giftcard_booking'){        
		           $args = array(
					    'status' => array('wc-pending','wc-processing', 'wc-on-hold','wc-completed','wc-cancelled','wc-refunded','wc-failed'),
					);
					$orders = wc_get_orders( $args );
	                 //echo "<pre>";
	                 //print_r($orders);
	                 if ($orders) {
				    	echo '<table id="myTable" class="display">
						    <thead>
						        <tr align="left">
						            <th>Full Name</th>
						            <th>Product</th>
						            <th>Supplier Reference Number</th>
						            <th>Email</th>
						            <th>Mobile Number</th>
						            <th>Action</th>
						        </tr>
						    </thead><tbody>';
				    	foreach ($orders as $order) {
							if ( is_a( $order, 'WC_Order_Refund' ) ) {
								$order = wc_get_order( $order->get_parent_id() );
							}
				    		$first_name = $order->get_billing_first_name();
				    		$last_name = $order->get_billing_last_name();
				    		$reference_number = $order->get_id();
				    		$email = $order->get_billing_email();
				    		$mobile_number = $order->get_billing_phone();

				    		$product_name = 'Giftcard';
				    		if (!empty($order->get_items())) {
								foreach ($order->get_items() as $item_id => $item) {

									$product = $item->get_product();
									$attr_type = $product->get_variation_attributes();
									if (!empty($attr_type) && isset($attr_type['attribute_type']) && $attr_type['attribute_type'] == 'eGiftcard') {
										$product_name = 'eGiftcard';
									}
								}       
							}
				    		
				    	echo ' <tr>
						         <td>' . $first_name . ' ' . ' ' . $last_name . '</td>
						         <td>'. $product_name .'</td>
						         <td>'. $reference_number .'</td>
						         <td>'. $email .'</td>
						         <td>'. $mobile_number .'</td>
						         <td><a target="_blank" href="'.get_edit_post_link($order->get_id()).'">View</a></td>
						        </tr> ';                   
				    	}
				    	echo '</tbody></table>';
				    }		
				}

	    
	    echo '</div>';
	}

function university_adjust_queries($query){
	if ( is_admin() && $query->is_main_query() && $query->get('post_type') == 'gc4t_hotel_booking' ) {
		$query->set( 'meta_key', '_gctcf_booking_status' );
	    $query->set( 'meta_query', array(
	        array(
	            'key'     => '_gctcf_booking_status',
	            'compare' => 'EXIST',
	        )
	    ) );
	}
}
add_action( 'pre_get_posts', 'university_adjust_queries' );

function gctcf_filter_hotels_by_price($filter_price, $hotels = array())
{
	$merged_results = array();
	if(!empty($hotels))
	{
		$flat_array = [];
		foreach($hotels as $k => $array)
		{
			if(!empty($array['room_types']))
			{
				//usort($array['room_types'],"gctcf_hotel_room_price_sort");
				usort($array['room_types'], function($a, $b) use ($filter_price) {
				    if ($a["total_price"] == $b["total_price"]) {
				        return 0;
				    }
				    if($filter_price == 'asc')
				    {
				    	return ($a["total_price"] < $b["total_price"]) ? -1 : 1;
				    }
				    return ($a["total_price"] > $b["total_price"]) ? -1 : 1;
				});
			    $min_price = $array['room_types'][0]['total_price'];
			    $array['gctcf_min_price'] = $min_price;    
			    $flat_array[$k] = $array;
			}
		   
		}
		//usort($flat_array,"gctcf_hotel_price_sort");
		usort($flat_array, function($a, $b) use ($filter_price) {
		    if ($a["gctcf_min_price"] == $b["gctcf_min_price"]) {
		        return 0;
		    }
		    if($filter_price == 'asc')
		    {
		    	return ($a["gctcf_min_price"] < $b["gctcf_min_price"]) ? -1 : 1;
		    }
		    return ($a["gctcf_min_price"] > $b["gctcf_min_price"]) ? -1 : 1;
		});
		$merged_results = $flat_array;
	}
	return $merged_results;
}

function gctcf_filter_hotels_by_rating($filter, $hotels = array())
{
	// echo $filter;
	//pre($hotels);
	//die;

	if(!empty($hotels))
	{
		usort($hotels, function($a, $b) use ($filter) {
			if (is_array($a['star_rating']) || is_array($b['star_rating'])) {
				return 0;
			}
		    if ($a["star_rating"] == $b["star_rating"]) {
		        return 0;
		    }
		    if($filter == 'asc')
		    {
		    	return ($a["star_rating"] < $b["star_rating"]) ? -1 : 1;
		    }
		    return ($a["star_rating"] > $b["star_rating"]) ? -1 : 1;
			//return ($a["star_rating"] > $b["star_rating"]) ? -1 : 1;
		});
	}

	return $hotels;
}

function gctcf_filter_by_board($gctcf_type, $hotels=array())
{
	if(empty($hotels))	return $hotels;
	$filterArr = array();
	foreach ($hotels as $key => $merged_result)
	{
		$roomArr = array();
		foreach ($merged_result['room_types'] as $room_type_index => $room_type)
		{
			if($gctcf_type == 'bed-breakfast')
			{
				if((strpos($room_type['meal'], "Bed") !== false) || (strpos($room_type['meal'], "Breakfast") !== false))
				{
					$roomArr[] = $room_type;
				}
			}
			else
			{
				if(strpos($room_type['meal'], $gctcf_type) !== false)
				{
					$roomArr[] = $room_type;
				}
			}			
			
		}
		if($roomArr)
		{
			$merged_result['room_types'] = $roomArr;
			$filterArr[$key] = $merged_result;
		}
	}

	return $filterArr;
}

function gctcf_filter_hotels($hotels=array())
{
	if(empty($hotels))	return $hotels;
	$filterArr = array();
	foreach ($hotels as $key => $merged_result)
	{
		$roomArr = array();
		foreach ($merged_result['room_types'] as $room_type_index => $room_type)
		{
			if($room_type['quote_id'] != '')
			{
					$roomArr[] = $room_type;
			}			
			
		}
		if($roomArr)
		{
			$merged_result['room_types'] = $roomArr;
			$filterArr[$key] = $merged_result;
		}
	}

	return $filterArr;
}


function gctcf_redirect_google_ads() {
    if (isset($_REQUEST['gclid'])) {
        wp_redirect('https://www.google.com', 302);
        exit;
    }
}

add_action('init', 'gctcf_redirect_google_ads');

function gctcf_convert_amount($amount = 1, $from = 'EUR', $to = 'GBP') {
	$endpoint = 'convert';
	$access_key = 'WKfSyHQjnYfqnajs2RovD9LGTnXYxSjB';
	$result = array(
		'date' => date('Y-m-d'),
		'rate' => 1,
		'value' => 0,
	);
	// initialize CURL:
	$response = wp_remote_get('https://api.apilayer.com/fixer/'.$endpoint.'?from='.$from.'&to='.$to.'&amount='.$amount.'', array(
		'headers' => array(
			'apikey' => $access_key,
		)
	));
	if (!is_wp_error($response)) {
		if (isset($response['body'])) {
			$response = json_decode($response['body'], true);
			echo '<pre>' . print_r($response, true) . '</pre>';
			if (isset($response['date']) && isset($response['result'])) {
				$result['date'] = $response['date'];
				$result['rate'] = $response['info']['rate'];
				$result['value'] = $response['result'];
			}
		}
	}
	return $result;
}