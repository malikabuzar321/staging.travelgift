<?php 

// pre($_POST); exit();

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
function send_tbo_request($method,$data){
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => 'http://api.tbotechnology.in/TBOHolidays_HotelAPI/'.$method,
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
	    'Authorization: Basic dGVzdFRyYXZlbGdpZnQ6WHRyQDIzNzMwNzUx'
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


if (!WC()->session->has_session()) {
    WC()->session->set_customer_session_cookie(true);
}
if(!isset($_REQUEST['hotel_id'])){
	WC()->session->set('search_request', $_REQUEST); 
}

if(isset($_POST))
{
	// echo "<pre>";
 	// print_r($_POST);
 	// exit();
}
global $wpdb;
$settings = get_api_config();
$api_url = $settings['url'];
$api_org = $settings['org'];
$api_user = $settings['user'];
$api_pass = $settings['pass'];


$hotel_id = (isset($_REQUEST['hotel_id'])) ? stripslashes($_REQUEST['hotel_id']) : '';

$hotel_regionid_encoded = (isset($_REQUEST['hotel_regionid'])) ? stripslashes($_REQUEST['hotel_regionid']) : '';
$hotel_region_id = (isset($_REQUEST['hotel_regionid'])) ? json_decode(stripslashes($_REQUEST['hotel_regionid']), true) : array();
$stuba_region = (isset($hotel_region_id['stuba'][0])) ? $hotel_region_id['stuba'][0] : '';
$tbo_region = (isset($hotel_region_id['tbo'][0])) ? $hotel_region_id['tbo'][0] : '';
$travellanda_regions = (isset($hotel_region_id['travellanda'])) ? $hotel_region_id['travellanda'] : array();

$country_code = (isset($_REQUEST['country_code'])) ? sanitize_text_field($_REQUEST['country_code']) : '';
$percentage = get_option('travel_hotel_booking_price_by_parcentage');

$hotel_name = isset($_REQUEST['hotel_name']) ? sanitize_text_field($_REQUEST['hotel_name']) : '';
$hotel_start_date = isset($_REQUEST['hotel_check_in_date']) ? sanitize_text_field($_REQUEST['hotel_check_in_date']) : '';
$hotel_night = isset($_REQUEST['hotel_night']) ? sanitize_text_field($_REQUEST['hotel_night']) : '';
$hotel_country_by_region = isset($_REQUEST['hotel_country_by_region']) ? sanitize_text_field($_REQUEST['hotel_country_by_region']) : '';
$hotel__region__name = isset($_REQUEST['hotel__region__name']) ? sanitize_text_field($_REQUEST['hotel__region__name']) : '';

$room_with_adults = isset($_REQUEST['hotel_adults']) ? $_REQUEST['hotel_adults'] : array();
if($room_with_adults)	array_map('sanitize_text_field', $room_with_adults);

$room_with_children = isset($_REQUEST['hotel_children']) ? $_REQUEST['hotel_children'] : array();
if($room_with_children)	array_map('sanitize_text_field', $room_with_children);

$rooms_requested = isset($_REQUEST['no_of_room']) ? $_REQUEST['no_of_room'] : array();
if($rooms_requested)	array_map('sanitize_text_field', $rooms_requested);

$hotel_star_rating = isset($_REQUEST['hotel_star_rating']) ? sanitize_text_field($_REQUEST['hotel_star_rating']) : 0;
$hotel_min_price_val = isset($_REQUEST['hotel_min_price_by_filter']) ? sanitize_text_field($_REQUEST['hotel_min_price_by_filter']) : 0;
$hotel_max_price_val = isset($_REQUEST['hotel_max_price_by_filter']) ? sanitize_text_field($_REQUEST['hotel_max_price_by_filter']) : 300;
$hotel_search_c = '';

$stuba_region = isset($hotel_region_id['stuba'][0]) ? $hotel_region_id['stuba'][0] : '';
$travellanda_regions = isset($hotel_region_id['travellanda']) ? $hotel_region_id['travellanda'] : array();



if($hotel_star_rating || $hotel_max_price_val)
{
	$hotel_search_c .= '<HotelSearchCriteria>';
	if($hotel_star_rating)
	{
		$hotel_search_c .= '<MinStars>'.$hotel_star_rating.'</MinStars>';
	}
	if($hotel_max_price_val)
	{
		$hotel_search_c .= '<MinPrice>'.$hotel_min_price_val.'</MinPrice>
							<MaxPrice>'.$hotel_max_price_val.'</MaxPrice>';
	}
	$hotel_search_c .= '</HotelSearchCriteria>';
}
//pre($rooms_requested , 1);
$gctcf_ratings = isset($_REQUEST['hotel_sort_ratings']) ? $_REQUEST['hotel_sort_ratings'] : '';
$gctcf_prices = isset($_REQUEST['hotel_sort_prices']) ? $_REQUEST['hotel_sort_prices'] : '';
$gctcf_type = isset($_REQUEST['hotel_sort_type']) ? $_REQUEST['hotel_sort_type'] : '';
$merged_results = $stuba_Arr = $travellanda_Arr = $tbo_Arr = array();

if((isset($_POST['hotel_list']) && !empty($_POST['hotel_list'])) || $hotel_id)
{
	//count rooms
	$gctc_show_all = isset($_REQUEST['gctc_show_all']) ? $_REQUEST['gctc_show_all'] : 0;
	$gctc_show_all = 1;
	$no_of_room = isset($_REQUEST['no_of_room']) ? $_REQUEST['no_of_room'] : array();
	if($no_of_room)	array_map('sanitize_text_field', $no_of_room);
	$number_of_rooms= count($no_of_room);


	// pre($hotel_id); exit();
	if (($country_code && !empty($travellanda_regions)) || $hotel_name || $hotel_id) {
		//check in database
		if($hotel_id){
			$check_tr = $wpdb->get_results("SELECT * FROM `hotels_data` WHERE `hotel_id` = ".$hotel_id);
		} elseif(!$hotel_name){
			$check_tr = $wpdb->get_results("SELECT * FROM `hotels_data` WHERE `travellanda_id` = ".$travellanda_regions[0]);
		} else {
			$check_tr = $wpdb->get_results("SELECT * FROM `hotels_data` WHERE `feed` = 'travellanda' AND `hotel_name` LIKE '%".$hotel_name."%'");
		}
		// pre($check_tr); exit();
		if($check_tr) {
			foreach($check_tr as $index => $value){
				$room_types = json_decode($value->room_types,1);
				$travellanda_Arr[$index] = json_decode(json_encode($value),1);
				$travellanda_Arr[$index]['room_types'] = $room_types;
			}
		} else {
			//Check for travellanda
			require_once GCTCF_PATH . '/includes/Travellanda.class.php';

			$travellanda_settings = get_travellanda_api_config();

			$travellanda = new Travellanda();
			$travellanda->setUsername($travellanda_settings['user']);
			$travellanda->setPassword($travellanda_settings['pass']);
			$travellanda->setMode($travellanda_settings['mode']);
			$hotel_date_time = date_create_from_format('Y-m-d', $hotel_start_date);
			$hotel_date_time->add(new DateInterval('P' . ($hotel_night) . 'D'));
			if($hotel_name){
				$hotel_id_result = $wpdb->get_results("SELECT * FROM `travellanda_hotels` WHERE `hotel_name` LIKE '%".$hotel_name."%'");
				$hotel_ids_for_search = [];
				if($hotel_id_result){
					foreach($hotel_id_result as $h_value){
						$hotel_ids_for_search[] = $h_value->hotel_id;
					}
				}
			}

			// echo "<pre>"; print_r($hotel_ids_for_search); exit();
			if($hotel_ids_for_search){
				$search_params = array(
					'type' => 'hotel',
					'locations' => $hotel_ids_for_search,
					'check_in_date' => $hotel_start_date,
					'check_out_date' => $hotel_date_time->format('Y-m-d'),
					'available_only' => true,
					'currency' => 'GBP',
					'nationality' => 'GB',
					'rooms' => array(),
				);
			} else {
				$search_params = array(
					'type' => 'city',
					'locations' => $travellanda_regions,
					'check_in_date' => $hotel_start_date,
					'check_out_date' => $hotel_date_time->format('Y-m-d'),
					'available_only' => true,
					'currency' => 'GBP',
					'nationality' => 'GB',
					'rooms' => array(),
				);
			}

			for ($a = 1; $a <= $number_of_rooms; $a++) {
				$room = array();
				$room['adult'] = $room_with_adults[$a];

				for ($b = 0; $b < $room_with_children[$a]; $b++) {
					if (!isset($room['child'])) {
						$room['child'] = array();
					}
					$room['child'][] = 5; //set default age
				}
				$search_params['rooms'][] = $room;
			}
			
			$travellanda_results = $travellanda->hotelSearch($search_params);
			if(!is_wp_error($travellanda_results)):
			//echo '<pre>' . print_r($travellanda_results) . '</pre>';
			$travellanda_results = $travellanda_results['body'];
			// echo '<pre>' . print_r(htmlentities($travellanda_results), true) . '</pre>';
			$travellanda_results = json_decode($travellanda->convertToJson($travellanda_results), true);

			if (isset($travellanda_results['Body']['Hotels'])) {
	        //print_r($travellanda_results['Body']);
				$hotel_ids = array();
				$hotels = isset($travellanda_results['Body']['Hotels']['Hotel'])
						    ? (is_array($travellanda_results['Body']['Hotels']['Hotel'])
						        ? $travellanda_results['Body']['Hotels']['Hotel']
						        : [$travellanda_results['Body']['Hotels']['Hotel']])
						    : [];
				

				if(isset($hotels['HotelId'])){
					$temp = [$hotels];
					$hotels = $temp;
					// $hotel_ids[] = $hotels['HotelId'];
				} 
				// pre($hotels); exit();
				foreach ($hotels as $hotel) {
					if(is_array($hotel)){
						$hotel_ids[] = $hotel['HotelId'];
					}
				}
				//echo '<pre>' . print_r($hotel_ids, true) . '</pre>';
				if (!empty($hotel_ids)) {
					$hotel_details = $travellanda->getHotelDetails($hotel_ids);
					$hotel_details = json_decode($travellanda->convertToJson($hotel_details['body']), true);
					$hotel_details = (isset($hotel_details['Body']['Hotels']['Hotel'][0])) ? $hotel_details['Body']['Hotels']['Hotel'] : array($hotel_details['Body']['Hotels']['Hotel']);
					//echo '<pre>' . print_r($hotel_details, true) . '</pre>';
					foreach ($hotels as $index => $hotel) {
						$hotel_img_url = GCTCF_URL.'public/images/hotel-placeholder-img.png';;
						if (isset($hotel_details[$index]['HotelId'])) {
							if ($hotel_details[$index]['HotelId'] == $hotel['HotelId']) {
								$hotel_img_url = $hotel_details[$index]['Images']['Image'][0];
								//echo $hotel_img_url;
							}
						}
						$result = array(
							'feed' => 'travellanda',
							'hotel_id' => $hotel['HotelId'],
							'hotel_name' => $hotel['HotelName'],
							'star_rating' => $hotel['StarRating'],
							'room_types' => array(),
							'hotel_image' => $hotel_img_url,
						);
						if($hotel['Options']['Option'])
						{
							foreach ($hotel['Options']['Option'] as $option) {
								if(!empty($option) && is_array($option)):
									if(isset($option['Rooms'])):
									$room = (isset($option['Rooms']['Room'][0])) ? $option['Rooms']['Room'][0] : $option['Rooms']['Room'];						
									$percentage_total = ($percentage / 100) * $room['RoomPrice'];
									$total_price = ($room['RoomPrice'] + $percentage_total);

									$room_id_format = explode('-', $room['RoomId']);
									$room_type = array(
										'feed' => 'travellanda',
										'quote_id' => $option['OptionId'],
										'name' => $room['RoomName'],
										'meal' => $option['BoardType'],
										'price' => $room['RoomPrice'],
										'percentage' => $percentage,
										'percentage_total' => $percentage_total,
										'total_price' => $total_price,
										'option_total_price' => $option['TotalPrice'],
										'room_code' => $room_id_format[0],
									);

									$result['room_types'][] = $room_type;
								endif;endif;
							}
						}
						if ($result['hotel_name']) {
							$travellanda_Arr[strtolower($result['hotel_name'])] = $result;
							
						}
					}
				}
			}
			endif;
		}	
	}

	
	$dir = wp_get_upload_dir();
	// var_dump($stuba_region); exit();
	if ($stuba_region || $hotel_name)	{

		$check_st = $wpdb->get_results("SELECT * FROM `hotels_data` WHERE `stuba_id` = ".$stuba_region);
		if(!$check_st){
			$check_st = $wpdb->get_results("SELECT * FROM `hotels_data` WHERE `feed` = 'stuba' AND `hotel_name` LIKE '%".$hotel_name."%'");
		}
		if($check_st) {
			foreach($check_st as $index => $value){
				$room_types = json_decode($value->room_types,1);
				$stuba_Arr[$index] = json_decode(json_encode($value),1);
				$stuba_Arr[$index]['room_types'] = $room_types;
			}
			// $stuba_Arr = json_decode(json_encode($check_st),true);
		} else {
			$str1 = '<Room>
						<Guests>';
			$str2 = '</Guests>
						</Room>';
			

			//check guests per room
			$adult_str=' ';
			$child_str=' ';
			//$singel_room_with_guests='';

			$singel_room_with_guests_append='';
			for($i=1;$i<=$number_of_rooms;$i++)
			{
				$room_without_children = room_without_children($room_with_adults[$i]);
				$room_with_children2 = room_with_children($room_with_children[$i]);
				
				$singel_room_with_guests = $str1.$room_without_children.$room_with_children2.$str2;			
				$singel_room_with_guests_append = $singel_room_with_guests_append.$singel_room_with_guests;
			}

			$test_mode = (current_user_can('manage_options')) ? 'true' : 'false';
			$test_mode = 'false';
			$maxHotels = '';
			if($gctc_show_all == 0)
			{
				$maxHotels = '<MaxHotels>20</MaxHotels>';
			}

			$user_xmldata = '<?xml version="1.0" encoding="utf-8"?>
								<AvailabilitySearch>
									<Authority>
										<Org>'.$api_org.'</Org>
										<User>'.$api_user.'</User>
										<Password>'.$api_pass.'</Password>
										<Currency>GBP</Currency>
										<Language>en</Language>
										<TestMode>' . $test_mode . '</TestMode>
										<DebugMode>'  . $test_mode . '</DebugMode>
										<Version>1.28</Version>
									</Authority>
									<RegionId>'.$stuba_region.'</RegionId>
									<HotelStayDetails>
										<ArrivalDate>'.$hotel_start_date.'</ArrivalDate>
										<Nights>'.$hotel_night.'</Nights>
										<Nationality>GB</Nationality>'.
										$singel_room_with_guests_append.
									'</HotelStayDetails>'.$hotel_search_c.'
									<DetailLevel>basic</DetailLevel>'.
									$maxHotels
								.'</AvailabilitySearch>';
			$roomxml_api_url= $api_url;
			$host = [implode(':', ['api.stuba.com',443, gethostbyname('api.stuba.com')])];
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
			$room_api_xml_response_for_hotel_search = simplexml_load_string($room_xml_api_output_for_hotel_search);
			// pre($room_xml_api_output_for_hotel_search); exit();
			$dir = wp_get_upload_dir();
	        $directory_path =  $dir['basedir'];

			if (!empty($room_api_xml_response_for_hotel_search->HotelAvailability)) {
				$room_api_xml_response = $room_api_xml_response_for_hotel_search->HotelAvailability;

				foreach ($room_api_xml_response as $hotel) {
					//echo '<pre>' . print_r($hotel, true) . '</pre>';
					$hotel_info = $hotel->Hotel;
					$hotel_name= xml_attribute($hotel_info, 'name');
					$hotel_id = xml_attribute($hotel_info, 'id');
					$hotel_img_url = GCTCF_URL.'public/images/hotel-placeholder-img.png';

					if (file_exists($directory_path.'/hotel_api/'.$hotel_id.'.xml')) {
						$xml_hotel_data = file_get_contents($directory_path.'/hotel_api/'.$hotel_id.'.xml');
						$avilable_hotel_facility = simplexml_load_string($xml_hotel_data);
						if(!empty($avilable_hotel_facility->Photo))	$hotel_img_url = 'https://api.stuba.com' . $avilable_hotel_facility->Photo->Url;
					}
					$hotel_room_types = $hotel->Result;
					$room_types = array();

					foreach ($hotel_room_types as $index => $hotel_room_type) {
						$room_info = $hotel_room_type->Room->RoomType;
						$room_type_name = xml_attribute($room_info, 'text');
						$room_type_meal = $hotel_room_type->Room->MealType;
						$room_type_meal_name = xml_attribute($room_type_meal, 'text');
						$room_type_price = $hotel_room_type->Room->Price;
						$room_type_price_amt = xml_attribute($room_type_price, 'amt');
						$room_type_code = $hotel_room_type->Room->RoomType;
						$room_type_code = xml_attribute($room_type_code, 'code');

						$percentage_total = ($percentage / 100) * $room_type_price_amt;
						$total_price = ($room_type_price_amt + $percentage_total);

						$room_type = array(
							'feed' => 'stuba',
							'quote_id' => xml_attribute($hotel_room_type, 'id'),
							'name' => $room_type_name,
							'meal' => $room_type_meal_name,
							'price' => $room_type_price_amt,
							'percentage' => $percentage,
							'percentage_total' => $percentage_total,
							'total_price' => $total_price,
							'option_total_price' => $room_type_price_amt,
							'room_code' => $room_type_code,
						);
						$room_types[] = $room_type;
					}
					if ($hotel_name) {
						$stuba_Arr[strtolower($hotel_name)] = array(
							'feed' => 'stuba',
							'hotel_id' => $hotel_id,
							'hotel_image' => $hotel_img_url,
							'hotel_name' => $hotel_name,
							'room_types' => $room_types,
						);
					}
				}
			}

			if($stuba_Arr)
			{
				$stuba_Arr2 = array();
				foreach ($stuba_Arr as $key => $stuba_details)
				{
					$stuba_details['star_rating'] = 0;
					$url = $dir['basedir'].'/hotel_api/'.$stuba_details['hotel_id'].'.xml';

					if(file_exists($url))
					{
						if ($stuba_details['feed'] == 'stuba')
						{
							$url = $dir['baseurl'].'/hotel_api/'.$stuba_details['hotel_id'].'.xml';
							$xml_hotel_data = get_hotles($url);
							$avilable_hotel = simplexml_load_string($xml_hotel_data);
							//pre($avilable_hotel);
							$stars = isset($avilable_hotel->Stars) ? (int) $avilable_hotel->Stars : 0;
							
							$stuba_details['star_rating'] = $stars;
						}
					}
					$stuba_Arr2[$key] = $stuba_details;
				}

				$stuba_Arr = $stuba_Arr2;
			}
		}
	}


	// pre($stuba_Arr); exit();

	if($tbo_region) {
		$tbo_data = send_tbo_request(
			'TBOHotelCodeList',
			[
				'CityCode'=>$tbo_region,
				'IsDetailedResponse'=>'false'
			]
		);
		if($tbo_data){
			$tbo_hotels = $tbo_data['Hotels'];
		}
		foreach($tbo_hotels as $tboh){
			$tbo_ids[] = $tboh['HotelCode'];
		}

		$hotel_search_request_data = [
		    "CheckIn"=> $hotel_start_date,
		    "CheckOut"=> date("Y-m-d",(strtotime($hotel_start_date) + $hotel_night*86400)),
		    "HotelCodes"=> implode(", ",$tbo_ids),
		    "GuestNationality"=> "GB",
		    "PreferredCurrencyCode"=> "GBP",
		    "PaxRooms"=> [
		        [
		            "Adults"=> 1,
		            "Children"=> 0,
		            "ChildrenAges"=> []
		        ]
		    ],
		    "IsDetailResponse"=> true,
		    "ResponseTime"=> 10
		];
		$hotel_search_data = send_tbo_request('Search',$hotel_search_request_data);
		$tbo_available_hotels = $hotel_search_data['HotelResult'];

		foreach($tbo_available_hotels as $index => $tboh_avl){
			$tbo_ids_for_detail[] = $tboh_avl['HotelCode'];
			unset($tbo_available_hotels[$index]);
			$tbo_available_hotels[$tboh_avl['HotelCode']] = $tboh_avl;
		}
		// pre($tbo_available_hotels); exit();
		$hotel_details_request = send_tbo_request('HotelDetails',['Hotelcodes'=>implode(', ',$tbo_ids_for_detail),'Language'=>'EN']);
		$hotel_details = $hotel_details_request['HotelDetails']; 
		$tboh_hotels_final = [];
		foreach($hotel_details as $hotel_d){
			$hotel_data = $tbo_available_hotels[$hotel_d['HotelCode']];
			// $thotel_final = array_merge($tbo_available_hotels[$hotel_d['HotelCode']],$hotel_d);
			// pre($hotel_d); exit();
			foreach($hotel_data['Rooms'] as $room){
				$net_fare = $room['TotalFare'] - $room['TotalTax'];
				$percentage = $room['TotalTax']*100/$room['TotalFare'];
				$tbo_room_type = array(
					'feed' => 'tbo',
					'quote_id' => $room['BookingCode'],
					'name' => $room['Name'][0],
					'meal' => $room['MealType'],
					'price' => $net_fare,
					'percentage' => $percentage,
					'percentage_total' => $room['TotalTax'],
					'total_price' => $room['TotalFare'],
					'option_total_price' => $room['TotalFare'],
					'room_code' => '',
				);
				$tbo_room_types[] = $tbo_room_type;
			}
			// pre($hotel_d); exit();
			if ($hotel_d) {
				// $tbo_Arr[strtolower($hotel_d['HotelName'])] = array(
				$tbo_Arr[] = array(
					'feed' => 'tbo',
					'hotel_id' => $hotel_d['HotelCode'],
					'hotel_image' => $hotel_d['Images'][0],
					'hotel_name' => $hotel_d['HotelName'],
					'room_types' => $tbo_room_types,
					'star_rating' => $hotel_d['HotelRating']
				);
			}
		}

		// foreach ($hotel_room_types as $index => $hotel_room_type) {
		// 	$room_info = $hotel_room_type->Room->RoomType;
		// 	$room_type_name = xml_attribute($room_info, 'text');
		// 	$room_type_meal = $hotel_room_type->Room->MealType;
		// 	$room_type_meal_name = xml_attribute($room_type_meal, 'text');
		// 	$room_type_price = $hotel_room_type->Room->Price;
		// 	$room_type_price_amt = xml_attribute($room_type_price, 'amt');
		// 	$room_type_code = $hotel_room_type->Room->RoomType;
		// 	$room_type_code = xml_attribute($room_type_code, 'code');

		// 	$percentage_total = ($percentage / 100) * $room_type_price_amt;
		// 	$total_price = ($room_type_price_amt + $percentage_total);

		// 	$room_type = array(
		// 		'feed' => 'stuba',
		// 		'quote_id' => xml_attribute($hotel_room_type, 'id'),
		// 		'name' => $room_type_name,
		// 		'meal' => $room_type_meal_name,
		// 		'price' => $room_type_price_amt,
		// 		'percentage' => $percentage,
		// 		'percentage_total' => $percentage_total,
		// 		'total_price' => $total_price,
		// 		'option_total_price' => $room_type_price_amt,
		// 		'room_code' => $room_type_code,
		// 	);
		// 	$room_types[] = $room_type;
		// }
		// if ($hotel_name) {
		// 	$stuba_Arr[strtolower($hotel_name)] = array(
		// 		'feed' => 'stuba',
		// 		'hotel_id' => $hotel_id,
		// 		'hotel_image' => $hotel_img_url,
		// 		'hotel_name' => $hotel_name,
		// 		'room_types' => $room_types,
		// 	);
		// }


		// pre($tbo_Arr); exit();




	}
}

// if(!$travellanda_Arr){
// 	$hotels = $wpdb->get_results("SELECT * FROM `hotels_data` WHERE `feed` = 'travellanda' AND `hotel_name` LIKE '%".$hotel_name."%'", ARRAY_A);
// 	if($hotels){
// 		$travellanda_Arr = $hotels;
// 	}
// }
// if(!$stuba_Arr){
// 	$hotels1 = $wpdb->get_results("SELECT * FROM `hotels_data` WHERE `feed` = 'stuba' AND `hotel_name` LIKE '%".$hotel_name."%'", ARRAY_A);
// 	if($hotels1){
// 		$stuba_Arr = $hotels;
// 	}
// }
// pre($stuba_Arr); exit();
$travellanda_Arr = gctcf_filter_hotels($travellanda_Arr);
$stuba_Arr = gctcf_filter_hotels($stuba_Arr);
$tbo_Arr = gctcf_filter_hotels($tbo_Arr);

// pre($tbo_Arr); exit();

if($gctcf_type)
{
	$travellanda_Arr = gctcf_filter_by_board($travellanda_Arr, $gctcf_type);
	$stuba_Arr = gctcf_filter_by_board($stuba_Arr, $gctcf_type);
}

if($gctcf_prices)
{
	$travellanda_Arr = gctcf_filter_hotels_by_price($travellanda_Arr, $gctcf_prices);
	$stuba_Arr = gctcf_filter_hotels_by_price($stuba_Arr, $gctcf_prices);
}
else if($gctcf_ratings)
{
	$travellanda_Arr = gctcf_filter_hotels_by_rating($travellanda_Arr, $gctcf_ratings);
	$stuba_Arr = gctcf_filter_hotels_by_rating($stuba_Arr, $gctcf_ratings);
}

$gctcf_offset = 100;
$travellanda_50 = array_slice($travellanda_Arr, 0, 50);
$stuba_50 = array_slice($stuba_Arr, 0, 50);

$_SESSION['gctcf_50'] = array_merge($travellanda_50, $stuba_50, $tbo_Arr);

// pre($_SESSION['gctcf_50']); exit();

WC()->session->set('gctcf_50', $_SESSION['gctcf_50']); 
$_SESSION['gctcf_all'] = array_merge($travellanda_Arr, $stuba_Arr);
WC()->session->set('gctcf_all', $_SESSION['gctcf_all']); 
$_SESSION['stuba_all'] = $stuba_Arr;
WC()->session->set('stuba_all', $_SESSION['stuba_all']); 
$_SESSION['travelnda_all'] = $travellanda_Arr;
WC()->session->set('travelnda_all', $_SESSION['travelnda_all']); 


// echo "<pre>"; print_r($travellanda_Arr); print_r($stuba_Arr); exit();
// print_r($_SESSION['gctcf_all']); exit();
// echo $tr = $travellanda_regions[0]; exit();
foreach($_SESSION['gctcf_all'] as $htl){
	$rec = [
		'travellanda_id' => $htl['feed']=='travellanda'?($travellanda_regions[0]??0):0,
		'stuba_id' => $htl['feed']=='stuba'?($stuba_region??0):0,
		'feed' => $htl['feed'],
		'hotel_id' => $htl['hotel_id'],
		'hotel_image' => $htl['hotel_image'],
		'hotel_name' => $htl['hotel_name'],
		'room_types' => json_encode($htl['room_types']),
		'star_rating' => $htl['star_rating']
	];
	$check = $wpdb->get_results('SELECT * FROM `hotels_data` WHERE `hotel_id` = ' . $htl['hotel_id']);
	if(!$check){
		$wpdb->insert('hotels_data',$rec);
	}
	// echo "<pre>"; print_r($rec); exit();
}

$merged_results = $_SESSION['gctcf_50'];
if(!$hotel_id): ?>
<section class="section section_visible gc-clearfix home-form home_serch">
    <div class="gc-container">
        <div class="gc-row">
			<div class="notice" style="background:#f1f1f1;padding: 5px;font-size: 12px;margin-bottom:10px;">
                <p><strong>Please note all pricing is in GBP (&pound;). If you have a EUR (&euro;) value gift card you can still redeem your card and the amount will be converted to GBP (&pound;) and applied to your purchase. For any queries please contact us at <a style="color:#f8b545;" href="mailto:info@travelgift.uk">info@travelgift.uk</a></strong><p>
              </div>
        	<div class="home-form">
	            <div id="content" class="gc-md-12 gc-sm-12 gc-xs-12">
	                <div class="hotel-title">
	                    <h6>ENTER DATE TO SEE PRICES AND AVAILABILITY</h6>
	                 </div>
	               
	                <div role="tabpanel" class="tab-pane active" id="tab_01">
	                    <form class="bookform form-inline dc-row" action="" method="post" onsubmit="gctcf_show_loader();">
	                     
							<div class="gc-md-12 gc-sm-12 content_section">
								<input type="hidden" id="hotel__country__code" name="country_code" value="<?php echo $country_code; ?>">
								<?php 
									if(isset($_REQUEST['hotel-region-id']) && !empty($_REQUEST['hotel-region-id']))
									{ 
								?>
										<p>Hotel Name : <?php echo sanitize_text_field($_REQUEST['hotel-name']); ?></p>
	                    
										<div class="gc-form-group gc-md-6 gc-sm-6 gc-xs-12 hotel_list_both_padding padding-01" >
											<input type="text" name="hotel_name" id="hotel-name" class="form-control" onkeyup="hotel_search()" placeholder=" Destination: Region name...." autocomplete="off" required="required" value="<?php if(!empty($_REQUEST['hotel-region-name'])){ echo sanitize_text_field($_REQUEST['hotel-region-name']);} ?>" />
											
											<input type="hidden" name="hotel_regionid" value="<?php if(!empty($_REQUEST['hotel-region-id'])){ echo sanitize_text_field($_REQUEST['hotel-region-id']);}  ?>" id="hotel__region__id"/>
										</div>
	                        
								<?php 
									} 
									else 
									{
								?>
	                      
										<div class="gc-form-group gc-md-6 gc-sm-6 gc-xs-12 hotel_list_both_padding padding-01" >
											<input type="text" style="font-size:14px;margin-bottom: 10px;" name="hotel_name" id="hotel-name" class="form-control" onkeyup="hotel_search()" placeholder=" Destination: Region name..." autocomplete="off" required="required" value="<?php if(!empty($hotel_name)){ echo sanitize_text_field($hotel_name);} ?>" />
											
											<input type="hidden" name="hotel_regionid" value="<?php echo htmlentities(json_encode($hotel_region_id)); ?>" id="hotel__region__id"/>
										</div>
	                      
							<?php 
									} 
								?>
	                                        
								<div class="gc-form-group gc-md-2 hotel_list_both_padding hotel-mobile-margin-top">
									<div class="input-group">
										<input type="text" class="form-control gctcf-datepicker" value="<?php if(!empty($hotel_start_date)){echo $hotel_start_date; }?>" name="hotel_check_in_date" placeholder="Check in" id="datepicker16" required="required" />
										<div class="input-group-addon"><i class="fa fa-calendar"></i></div>
									</div>
								</div>
								
								<div class="gc-form-group gc-md-2 hotel_list_both_padding">
									<div class="dropdown">
										<select class="selectpicker" name="hotel_night" data-style="btn-white">
											<?php 
											for($i=1; $i<=15; $i++)
											{ 
											?>
												<option <?php if(!empty($hotel_night)){if ($hotel_night== $i ) echo 'selected' ;} ?> value="<?php echo $i ?>"><?php echo $i ?> Night</option>
											<?php 
											} 
											?>
										</select>
									</div>
								</div>
								
								<div class="gc-form-group gc-md-2">
									<input type="submit" name="hotel_list" id="roomxml_hotel_search" class="btn btn-primary btn-block roomxml_hotel_search" value="SEARCH" />
								</div>
							</div>
	                    
							<div class="gc-md-12 gc-sm-12 gc-xs-12 hotel_dropdown_section_hotel_search">
								<div class="gc-form-group gc-md-4 gc-sm-6 gc-xs-12 hotel_view_list" id="hotel_result"></div>
								<div class="gc-form-group gc-md-8 gc-sm-6 gc-xs-12"></div>
							</div>
	                                     
							<div class="gc-md-12 gc-sm-12 gc-xs-12 rooms">
								<div class="gc-md-1">
									<label>Room1</label>
									<input type="hidden" name="no_of_room[1]" class="" value="1" />
								</div>
	                                      
								<div class="gc-md-2">
									<select class="" name="hotel_adults[1]"  data-style="btn-whiter" style="width:100%;">
										<?php 
											for($hotAdui=1; $hotAdui<=10; $hotAdui++)
											{ 
										?>
											<option value="<?php echo $hotAdui; ?>"><?php echo $hotAdui; ?> Adults</option>
										<?php 
											} 
										?>
									</select>    
								</div>
	                                       
								<div class="gc-md-2">
									<select class="" name="hotel_children[1]" data-style="btn-white" style="width:100%;">
										<?php 
											for($hotChil=0; $hotChil<=3; $hotChil++)
											{ 
										?>
												<option value="<?php echo $hotChil; ?>"><?php echo $hotChil; ?> Children</option>
										<?php 
											} 
										?>
									</select> 
								</div>
	                                        
								<div class="gc-md-1 btn_adds">
									<fieldset class="fldst">
										<input type="button" id="btn_room_add_another" class="document_add_another" style="color:#fff;" value="Add" >
									</fieldset>
	                                
	                             <input type="hidden" name="hotel_star_rating" class="hotel_star_rating_option" id="hotel_star_rating_option" value="<?php echo $hotel_star_rating; ?>" />
	                                        
	                            <input type="hidden" name="hotel_min_price_by_filter" class="hotel_min_price_option" id="hotel_min_price_option" value="<?php echo $hotel_min_price_val ?>" />
	                                         
	                            <input type="hidden" name="hotel_max_price_by_filter" class="hotel_max_price_option" id="hotel_max_price_option" value="<?php echo $hotel_max_price_val; ?>" />
								</div>        
							</div>
	                                      
							<div class="gc-sm-12 gc-md-12 gc-xs-12 rooms">
								<div class="room_options">
	    
								</div> 
							</div>
							<input type="hidden" name="hotel_sort_prices" value="<?php echo $gctcf_prices; ?>">  
							<input type="hidden" name="hotel_sort_ratings" value="<?php echo $gctcf_ratings; ?>">   
							<input type="hidden" name="hotel_sort_type" value="<?php echo $gctcf_type; ?>">   
						</form>
					</div>
				</div> 
			</div>                                    
		</div>
    </div>
</section>

<section class="gc-hotel-star-rating">
    <div class="gc-container">    
    	<div class="gc-row">    
    		<div id="content" class="gc-md-12 gc-sm-12 gc-xs-12">
    
			    <h6 class="filtering_by_product">Filtering by Star rating and Price</h6>
			    
			    <div class="star_background_color">
			    
				    <div class="total_star_rating_system">
					    <label>Filter by Rating</label>
					    <select id="gctcf_ratings">
					    	<option value="">Default</option>
					    	<option value="asc" <?php if($gctcf_ratings == 'asc')	echo 'selected'; ?>>Asc</option>
					    	<option value="desc" <?php if($gctcf_ratings == 'desc')	echo 'selected'; ?>>Desc</option>
					    </select>		     
					    
					</div>
				    
				    <div class="price_slider_section">
                       <label>Filter by Price</label>
					    <select id="gctcf_prices">
					    	<option value="">Default</option>
					    	<option value="asc" <?php if($gctcf_prices == 'asc')	echo 'selected'; ?>>Asc</option>
					    	<option value="desc" <?php if($gctcf_prices == 'desc')	echo 'selected'; ?>>Desc</option>
					    </select>	
				    </div>

				    <div class="price_slider_section">
                       <label>Filter by Board Basis</label>
					    <select id="gctcf_type">
					    	<option value="">Default</option>
					    	<option value="bed-breakfast" <?php if($gctcf_type == 'bed-breakfast')	echo 'selected'; ?>>Bed & Breakfast</option>
					    	<option value="Room Only" <?php if($gctcf_type == 'Room Only')	echo 'selected'; ?>>Room Only</option>
					    	<option value="Half Board" <?php if($gctcf_type == 'Half Board')	echo 'selected'; ?>>Half Board</option>
					    	<option value="Full Board" <?php if($gctcf_type == 'Full Board')	echo 'selected'; ?>>Full Board</option>
					    	<option value="Self Catering" <?php if($gctcf_type == 'Self Catering')	echo 'selected'; ?>>Self Catering</option>
					    	<option value="All Inclusive" <?php if($gctcf_type == 'All Inclusive')	echo 'selected'; ?>>All Inclusive</option>
					    </select>	
				    </div>
				    
				    <div class="price_range_apply_section">
				     	<input type="button" name="price_range_apply" class="price_range_apply" id="price_range_apply" value="Apply" />     
				    </div>
			    
			    </div>
    
    		</div>
    
    	</div>
    
    </div>
    
</section>
<?php endif; ?>
<?php 
if (!empty($merged_results)) :?>
	<section class="section clearfix">
    <div class="total_results" stuba="<?php echo count($_SESSION['stuba_all']); ?>" travellanda="<?php echo count($_SESSION['travelnda_all']); ?>"><?php if(!$hotel_id):?> Results: <?php echo count($_SESSION['gctcf_all']); endif;?></div>
		<div class="container"  id="paging_container3"> 
			<div class="alt_content">
            
				<?php foreach ($merged_results as $merged_result):  ?>
					<div class="gc-row hotel__search_result_view">
						<?php if(!$hotel_id): ?>
						<div class="gc-lg-3">
							<input type="hidden" name="feed_type" id="feed_type" value="<?php echo $merged_result['feed']; ?>">
							<input type="hidden" name="hotel__id" id="hotel__id" value="<?php echo $merged_result['hotel_id']; ?>">							
							<div class="property_img">
								<img src="<?php echo $merged_result['hotel_image']; ?>" alt="image">
							</div>
						</div>
						<?php endif; ?>

						<?php if($hotel_id): ?>
						<div style="width: 100%; height: 300px; padding-top: 20px; ">
						<?php else: ?>
						<div class="gc-lg-9">
						<?php endif; ?>
							<?php if($hotel_id): ?>
							<a class="gctcf-search-result-inner" target="_blank" href="<?=home_url()?>/hotel-details/?hotel-id=<?=$merged_result['hotel_id']?>"><h3><?php echo $merged_result['hotel_name']; ?></h3></a>
							<div class="" style="width: 100%; height: 207px;">
								<input type="hidden" name="feed_type" id="feed_type" value="<?php echo $merged_result['feed']; ?>">
								<input type="hidden" name="hotel__id" id="hotel__id" value="<?php echo $merged_result['hotel_id']; ?>">
								<div class="" style="background-image: url('<?=$merged_result['hotel_image']?>'); background-repeat: no-repeat; background-size: cover; background-position-y: center; width: 100%; height: 100%;">
								</div>
							</div>
							</div>
							<?php endif; ?>

                        <div class="search-result-view-content" <?=$hotel_id?'style="max-height: unset"':''?> >
                        	<?php if(!$hotel_id): ?>
							<a class="gctcf-search-result-inner" target="_blank" href="<?=home_url()?>/hotel-details/?hotel-id=<?=$merged_result['hotel_id']?>"><h3><?php echo $merged_result['hotel_name']; ?></h3>
							<span class="rating">
							<?php 
									$star_rating = $merged_result['star_rating'];
									if (!is_numeric($star_rating)) {
										$star_rating = 0;
									}
									for($k=0;$k<$star_rating;$k++)
				                    {
				                        echo '<i class="fa fa-star"></i>';
				                    }
							?>
							</span></a>
							<?php endif; ?>
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
				<?php endforeach; ?>
			</div>
			
			<?php if( count($_SESSION['gctcf_all']) >= 100 ): ?>
				<div class="gctcf-show-more">
					<form  method="post" id="gctcf-show-all-hotels">
						<input type="hidden" name="gctc_show_all" value="1">
						<input type="hidden" name="country_code" value="<?php echo $country_code; ?>">
		              	<input type="hidden" name="hotel_name" value="<?php echo $hotel_name; ?>">
		              	<input type="hidden" name="hotel_regionid" value='<?php echo $hotel_regionid_encoded; ?>'>
		              	<input type="hidden" name="hotel_country_by_region" value="<?php echo $hotel_country_by_region; ?>">
		              	<input type="hidden" name="hotel__region__name" value="<?php echo $hotel__region__name; ?>">
		              	<input type="hidden" name="hotel_check_in_date" value="<?php echo $hotel_start_date; ?>">
		              	<input type="hidden" name="hotel_night" value="<?php echo $hotel_night; ?>">
		              	<?php if($rooms_requested): ?>
		              	<?php 
		              			for ($i=1; $i <= count($rooms_requested); $i++)
		              			{ 
		              	?>
		              				<input type="hidden" name="no_of_room[<?= $i; ?>]" value="<?php echo $rooms_requested[$i]; ?>">
					              	<input type="hidden" name="hotel_adults[<?= $i; ?>]" value="<?php echo $room_with_adults[$i]; ?>">
					              	<input type="hidden" name="hotel_children[<?= $i; ?>]" value="<?php echo $room_with_children[$i]; ?>">
		              	<?php			
		              			}
		              	 ?>
		              	<?php endif; ?>
		              	<input type="hidden" name="action" value="gctcf_load_more_hotels">
		              	<input type="hidden" name="gctcf_offset" value="<?php echo $gctcf_offset; ?>">
		              	<input type="hidden" name="gctcf_ratings" value="<?php echo $gctcf_ratings; ?>">
		              	<input type="hidden" name="gctcf_prices" value="<?php echo $gctcf_prices; ?>">
						<input class="show-all-list" type="button" value="Show More">
					</form>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<div class="modal fade product_view" id="product_view" style="display: none;">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				</div>
				<div class="modal-body">
					<div id="hotel_facility_result">
					
		
					</div>
					
					<div class="modal-header" style="border-bottom:0px solid #fff; text-align:center;">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					</div>   
				</div>
			</div>
		</div>
	</div>

<?php else: ?>
<section class="section gc-clearfix">
	<div class="container">
		<div class="row">
			<div id="content" class="gc-md-12">
				<div class="hotel-title error-notification">
					<h5>There are no hotels that match your requested dates. Please try some other dates. Thank You.</h5>
				</div>
			</div>
		</div>
	</div>
</section>
<?php 
	endif;
$show_merged = true;
if(!$show_merged && !empty($room_api_xml_response_for_hotel_search->HotelAvailability)) { ?>
    
<section class="section gc-clearfix">
	<div class="container"  id="paging_container3"> 
		<div class="alt_content">                
       	<?php
								
			if(!empty($room_api_xml_response_for_hotel_search->HotelAvailability))
			{
				$room_api_xml_response=$room_api_xml_response_for_hotel_search->HotelAvailability;          
          		$total_hotels = count($room_api_xml_response);
				$id=1;
				foreach($room_api_xml_response as $hotal_array){
                ?>           
	 				<div class="gc-row hotel__search_result_view">     
     				<div class="gc-lg-3">
     				<?php
                        $hotel_list=$hotal_array->Hotel;
						$hotel_name=xml_attribute($hotel_list, 'name');
						$hotel_id=xml_attribute($hotel_list, 'id');
						$dir = wp_get_upload_dir();
                        $directory_path =  $dir['basedir'];
                        if (file_exists($directory_path.'/hotel_api/'.$hotel_id.'.xml')) {
                        	$xml_hotel_data = file_get_contents($directory_path.'/hotel_api/'.$hotel_id.'.xml');
							$avilable_hotel_facility =simplexml_load_string($xml_hotel_data);
                      	}
					?>
	                <input type="hidden" name="hotel__id" id="hotel__id" value="<?php echo $hotel_id; ?>" />
                    <?php if(!empty($avilable_hotel_facility->Photo)) { ?>
                            <div class="property_img"><img src="https://api.stuba.com<?php echo $avilable_hotel_facility->Photo->Url; ?>" alt="image"></div>
                    <?php } 
                            else { ?>
                                <img src="<?php echo GCTCF_URL.'public/images/hotel-placeholder-img.png'; ?>" alt="image">
                    <?php }?>
     				</div>
       	
        			<div class="gc-lg-9">
            			<a><h3><?php echo $hotel_name;?></h3></a>
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
                        <?php
							$more_hotel=1;
						  	foreach($hotal_array->Result as $room_type){
								//if($more_hotel<=3){
						   ?>
                                    <div class="gc-md-12 mobile-border-hotel-search">
                                        <form  action="<?php echo home_url() ?>/hotel-booking/" method="post" name="fr3" onsubmit="gctcf_show_loader();">                                            
										    <div class="hidden_file">
                                           	<?php 
											$hotel_room_by_id=xml_attribute($room_type, 'id');
											?>
                                        	<input type="hidden" name="hotel_quote_id" value="<?php echo $hotel_room_by_id; ?>" />                   
                                            <?php												  
												foreach($room_with_adults as $key =>$values){
											?>
                                                	<input type="hidden" name="numbers_of_adults[<?php echo $key; ?>]" value="<?php echo $values; ?>"  />
                                            <?php
                                            	}
												 
												foreach($room_with_children as $room_with_children_key => $room_with_children_value){
												  ?>
                                                 
                                                 <input type="hidden" name="numbers_of_child[<?php echo $room_with_children_key ?>]" value="<?php echo $room_with_children_value; ?>" />
                                            <?php
                                            	}
												 
												foreach($rooms_requested as $rooms_requested_key => $rooms_requested_value){
												?>
                                                  <input type="hidden" name="numbers_of_room[<?php echo $rooms_requested_key; ?>]" value="<?php echo $rooms_requested_value; ?>"  />
                                                <?php 
                                            	} ?>
                                                  
                                            </div>                                                 
                                            <div class="gc-md-4 mobile-roomtype-result">
                                             <?php $room_type_by_hotel=$room_type->Room->RoomType; 
												$room_type_name=xml_attribute($room_type_by_hotel, 'text');
												?>
                                                <p><?php echo $room_type_name; ?></p>
                                                <input type="hidden" name="room_type_name_by_hotel" value="<?php echo $room_type_name; ?>"/>
                                            </div>
                                            <div class="gc-md-2 mobile-breakfast-result">
                                             <?php $hotel_meal=$room_type->Room->MealType; 
											 $hotel_meal_name=xml_attribute($hotel_meal, 'text');
											?>
                                                <p><?php echo $hotel_meal_name;?></p>
                                            </div>
                                            <div class="gc-md-2 list-style-hotel1 mobile-price-result">
	                                            <?php  $hotel_price=$room_type->Room->Price; 
												 $hotel_total_price=xml_attribute($hotel_price, 'amt');
												 
												  $percentage =get_option('travel_hotel_booking_price_by_parcentage');
											      $percentage_total = ($percentage / 100) * $hotel_total_price;
												  $total_price=($hotel_total_price + $percentage_total);
												?>
	                                          
	                                            <h5><?php echo '£'.round($total_price,2);?></h5>
                                            </div>
                                            
                                            <div class="gc-md-2 list-style-hotel1 mobile-facility-result">                                            
                                             	<button type="button" class="btn btn-primary mobile_bottom_facility pd-md-1" id="hotel__id_ajax_<?php echo $id; ?>" value="<?php echo $hotel_id; ?>" onclick="hotel_facility_ajax(this.id)" data-toggle="modal" data-feed="stuba" data-target="#product_view"><span>Quick </span><span>View</span></button>
                                            </div>
                                            	
                                            <div class="gc-md-2 list-style-hotel1 mobile-action-result">
                                            	<input type="submit" name="hotel__booking" id="hotel__booking" value="BOOK NOW" class="btn btn-primary border-radius pd-md-1"/>
                                            </div>
                                            </form>
                                            
                                        	</div>  
                                           <div class="mobile_padding_hotel_search"></div> 
                                           <?php 
											//}//Four hotel display
											$more_hotel++;
                                        }
                                            ?>
                    </div>
 				</div>
				<div class="modal fade product_view" id="product_view" style="display: none;">
					<div class="modal-dialog">
				    <div class="modal-content">
				        <div class="modal-header">
				            <a href="#" data-dismiss="modal" class="class pull-right"><span class="glyphicon glyphicon-remove"></span></a>
				        </div>
				        <div class="modal-body">
				               <div id="hotel_facility_result">
				               

				            </div>
				            
				         <div class="modal-header" style="border-bottom:0px solid #fff; text-align:center;">
				            <a href="#" data-dismiss="modal" class="class "><span class="glyphicon glyphicon-remove"></span></a>
				        </div>   
				        </div>
				    </div>
				</div>
				</div>
	     <?php
		 $id++;
		 
		   }?>
         <?php } ?>
         </div>
<div class="alt_page_navigation"></div>
<div class="total_results">Result Hotels: <?php echo $total_hotels; ?></div>
</div>	


</section>
<?php }else { ?>
<!--<section class="section clearfix">
        <div class="container">
            <div class="gc-row">
                <div id="content" class="gc-md-12">
                    <div class="hotel-title hotel-choose-message">
                        <h5>The date you have chosen is unavalible for this hotel, please choose another date. Thank You.</h5>
                     </div>
                </div>
              </div>
            </div>
       </section>-->
<?php } ?>