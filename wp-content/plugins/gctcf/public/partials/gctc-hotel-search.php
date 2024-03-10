<?php
// pre($_POST,'');

function send_tbo_request_new($auth, $method, $data)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Basic ' . $auth
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    if ($response) {
        $tbo_data = json_decode($response, 1);
        if ($tbo_data['Status']['Code'] == '200') {
            return $tbo_data;
        }
    }
    return array();
}
if (!WC()->session->has_session()) {
    WC()->session->set_customer_session_cookie(true);
}
if (!isset($_REQUEST['hotel_id'])) {
    WC()->session->set('search_request', $_REQUEST);
}

global $wpdb;


// pre($_POST); exit();

// Getting Stuba Config
$settings = get_api_config();
$api_url = $settings['url'];
$api_org = $settings['org'];
$api_user = $settings['user'];
$api_pass = $settings['pass'];

$gctc_show_all = 0;
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
if ($room_with_adults)    array_map('sanitize_text_field', $room_with_adults);

$room_with_children = isset($_REQUEST['hotel_children']) ? $_REQUEST['hotel_children'] : array();
if ($room_with_children)    array_map('sanitize_text_field', $room_with_children);

$rooms_requested = isset($_REQUEST['no_of_room']) ? $_REQUEST['no_of_room'] : array();
if ($rooms_requested)    array_map('sanitize_text_field', $rooms_requested);

$hotel_star_rating = isset($_REQUEST['hotel_star_rating']) ? sanitize_text_field($_REQUEST['hotel_star_rating']) : 0;
$hotel_min_price_val = isset($_REQUEST['hotel_min_price_by_filter']) ? sanitize_text_field($_REQUEST['hotel_min_price_by_filter']) : 0;
$hotel_max_price_val = isset($_REQUEST['hotel_max_price_by_filter']) ? sanitize_text_field($_REQUEST['hotel_max_price_by_filter']) : 300;
$hotel_search_c = '';

$stuba_region = isset($hotel_region_id['stuba'][0]) ? $hotel_region_id['stuba'][0] : '';
$travellanda_regions = isset($hotel_region_id['travellanda']) ? $hotel_region_id['travellanda'] : array();

if ($hotel_star_rating || $hotel_max_price_val) {
    $hotel_search_c .= '<HotelSearchCriteria>';
    if ($hotel_star_rating) {
        $hotel_search_c .= '<MinStars>' . $hotel_star_rating . '</MinStars>';
    }
    if ($hotel_max_price_val) {
        $hotel_search_c .= '<MinPrice>' . $hotel_min_price_val . '</MinPrice>
							<MaxPrice>' . $hotel_max_price_val . '</MaxPrice>';
    }
    $hotel_search_c .= '</HotelSearchCriteria>';
}
//pre($rooms_requested , 1);
$gctcf_ratings = isset($_REQUEST['hotel_sort_ratings']) ? $_REQUEST['hotel_sort_ratings'] : '';
$gctcf_prices = isset($_REQUEST['hotel_sort_prices']) ? $_REQUEST['hotel_sort_prices'] : '';
$gctcf_type = isset($_REQUEST['hotel_sort_type']) ? $_REQUEST['hotel_sort_type'] : '';
$merged_results = $stuba_Arr = $travellanda_Arr = $tbo_Arr = $error = array();

if ((isset($_POST['hotel_list']) && !empty($_POST['hotel_list']))) {

    //Regions array
    $regions = [];
    if($travellanda_regions)
        $regions['travellanda'] = $travellanda_regions;
    if($stuba_region)
        $regions['stuba'] = $stuba_region;
    if($tbo_region)
        $regions['tbo'] = $tbo_region;
    //Check the database for hotels data
    $hotel_search_query = "SELECT * FROM `hotels_data` WHERE ";
    $i = 0;
    foreach($regions as $index => $region){
        if($i > 0){
            $hotel_search_query .= " OR ";    
        }
        if(is_array($region)){
            $hotel_search_query .= " ".$index."_id IN (".implode(', ', $travellanda_regions).")"; 
        } else {
            $hotel_search_query .= " ".$index."_id = ".$region;
        }
        $i++;
    }

    // $hotel_search_query .= " LIMIT 50";

    // echo $hotel_search_query; exit();
    $h_data = $wpdb->get_results($hotel_search_query);
    $hotels_data = []; 
    if($h_data){
        $json_h_data = json_encode($h_data);
        $hotels_data = json_decode($json_h_data,1);
    }
    $is_travellanda = array_search('travellanda', array_column($hotels_data, 'feed','hotel_id'));
    $is_tbo = array_search('tbo', array_column($hotels_data, 'feed','hotel_id'));
    $is_db = true;

    // pre($hotels_data); exit();

    if(!empty($travellanda_regions) ){
        if($is_travellanda){
            $is_db = true;
        } else {
            $is_db = false;
        }
    } 

    if($tbo_region){
        if($is_tbo){
            $is_db = true;
        } else {
            $is_db = false;
        }
    } 

    
    //Searching travellanda API
    if ($country_code && !empty($travellanda_regions) && !$is_travellanda) {
        //Check through travellanda API
        require_once GCTCF_PATH . '/includes/Travellanda.class.php';
        $travellanda_settings = get_travellanda_api_config();
        $travellanda = new Travellanda();
        $travellanda->setUsername($travellanda_settings['user']);
        $travellanda->setPassword($travellanda_settings['pass']);
        $travellanda->setMode($travellanda_settings['mode']);
        
        // Get Travellanda Hotels against city ID
        $travellanda_results = $travellanda->getHotels($travellanda_regions[0]);
        if (!is_wp_error($travellanda_results)){
            //Extract Response Body
            $travellanda_response = json_decode($travellanda_results, 1)['Body'];
            //Check if Body has 'Hotels' //Initial Search
            if(isset($travellanda_response['Hotels'])){
                $tr_hotels = $travellanda_response['Hotels'];
                // Get hotel IDs for details  
                $tr_ids = [];
                if(isset($tr_hotels['Hotel'][0])){
                    foreach ($tr_hotels['Hotel'] as $tr_hotel) {
                        if(is_array($tr_hotel)){
                            $tr_ids[] = $tr_hotel['HotelId'];
                        }
                    }
                } else {
                    $tr_ids[] = $tr_hotels['Hotel']['HotelId'];
                }
                //If there are any hotels
                if($tr_ids){
                    $travellanda_results_detail = $travellanda->getHotelDetails($tr_ids);

                    $travellanda_hotels = $travellanda_results_detail['body'];
                    // pre($travellanda_hotels); exit();
                    $travellanda_results = json_decode($travellanda->convertToJson($travellanda_hotels), true);
                    //If we have hotels detail data
                    if (isset($travellanda_results['Body']['Hotels']['Hotel'])) {
                        foreach ($travellanda_results['Body']['Hotels']['Hotel'] as $hotel_d) {
                            if (is_array($hotel_d['PhoneNumber'])) {
                                $phone = '';
                            } else {
                                $phone = $hotel_d['PhoneNumber'];
                            }
                            //If hotel detail has hotel description
                            if ($hotel_d['Description']) {
                                $travellanda_Arr[] = array(
                                    'feed' => 'travellanda',
                                    'hotel_id' => $hotel_d['HotelId'],
                                    'hotel_name' => $hotel_d['HotelName'],
                                    'hotel_desc' => (!is_array($hotel_d['Description'])) ? $hotel_d['Description'] : '',
                                    'hotel_image' => isset($hotel_d['Images']['Image']) ? $hotel_d['Images']['Image'][0] : GCTCF_URL . 'public/images/hotel-placeholder-img.png',
                                    'star_rating' => $hotel_d['StarRating'],
                                    'address' => $hotel_d['Address'],
                                    'phone' => (!is_array($hotel_d['PhoneNumber'])) ? $hotel_d['PhoneNumber'] : ''
                                );
                            }
                        }
                    }
                }
            }
        } else {
            $error = ['Request Failed. Please try again!'];
        }
    }

     //Searching TBO API
    if ($tbo_region && !$is_tbo ) {
        //get settings
        $tbo_mode = get_option('options_tboh_api_mode');
        $tbo_username = get_option('options_tboh_'.$tbo_mode.'_username');
        $tbo_password = get_option('options_tboh_'.$tbo_mode.'_password');
        $tbo_url = get_option('options_tboh_'.$tbo_mode.'_url');
        
        
        $auth = base64_encode($tbo_username . ":" . $tbo_password);

        $tbo_data = send_tbo_request_new(
            $auth,
            $tbo_url . 'TBOHotelCodeList',
            [
                'CityCode' => $tbo_region,
                'IsDetailedResponse' => true
            ]
        );
        // pre($tbo_data); exit();
        if(isset($tbo_data['Hotels'])){
            foreach ($tbo_data['Hotels'] as $hotel_d) {
                $tbo_Arr[] = array(
                    'feed' => 'tbo',
                    'hotel_id' => $hotel_d['HotelCode'],
                    'hotel_name' => $hotel_d['HotelName'],
                    'hotel_desc' => $hotel_d['Description'],
                    'hotel_image' => isset($hotel_d['Images'])?$hotel_d['Images'][0]:'',
                    'star_rating' => $hotel_d['HotelRating'],
                    'address' => $hotel_d['Address'],
                    'phone' => $hotel_d['PhoneNumber'] ?? ''
                );
            }
        }
    }
}

$merged_hotels_data = array_merge($tbo_Arr, $travellanda_Arr,$hotels_data);


if($merged_hotels_data && !$is_db){
    foreach($merged_hotels_data as $hd){
        if($hd['feed']!='stuba'){
            $check = $wpdb->get_results("SELECT  * FROM `hotels_data` WHERE `hotel_id`=".$hd['hotel_id']);
            if(!$check){
                $new_rec = [
                    'feed' => $hd['feed'],
                    'hotel_id' => $hd['hotel_id'],
                    'hotel_name' => $hd['hotel_name'],
                    'star_rating' => $hd['star_rating'],
                    'hotel_desc' => $hd['hotel_desc'],
                    'address' => $hd['address'],
                    'phone' => $hd['phone'],
                    'hotel_image' => $hd['hotel_image'],
                    'date_updated' => date('Y-m-d H:i:s')    
                ];
                if($hd['feed'] == 'tbo'){
                    $new_rec['tbo_id'] = $tbo_region;
                } elseif($hd['feed'] == 'travellanda') {
                    $new_rec['travellanda_id'] = $travellanda_regions[0];
                }
                $wpdb->insert('hotels_data',$new_rec);
            }
        } else {
            $stuba_Arr[]=$hd;
        }
    }
}


$gctcf_50 = array_slice($merged_hotels_data,0,50);

// pre($gctcf_50); exit();

WC()->session->set('gctcf_50', $gctcf_50);

$gctcf_all = $merged_hotels_data;
WC()->session->set('gctcf_all', $gctcf_all);

$stuba_all = $stuba_Arr;
WC()->session->set('stuba_all', $stuba_all);

$travelnda_all = $travellanda_Arr;
WC()->session->set('travelnda_all', $travelnda_all);

$tbo_all = $tbo_Arr;
WC()->session->set('tbo_all', $tbo_all);

$merged_results = $gctcf_50;

// pre($merged_results); exit();

if (!$hotel_id) : ?>
    <section class="section section_visible gc-clearfix home-form home_serch">
        <div class="gc-container">
            <div class="gc-row">
                <div class="notice" style="background:#f1f1f1;padding: 5px;font-size: 12px;margin-bottom:10px;">
                    <p><strong>Please note all pricing is in GBP (&pound;). If you have a EUR (&euro;) value gift card you can still redeem your card and the amount will be converted to GBP (&pound;) and applied to your purchase. For any queries please contact us at <a style="color:#f8b545;" href="mailto:info@travelgift.uk">info@travelgift.uk</a></strong>
                    <p>
                </div>
                <div class="home-form">
                    <div id="content" class="gc-md-12 gc-sm-12 gc-xs-12">
                        <div class="hotel-title">
                            <h6>ENTER HOTEL OR REGION NAME TO SEE HOTEL(S)</h6>
                        </div>

                        <div role="tabpanel" class="tab-pane active" id="tab_01">
                            <form class="bookform form-inline dc-row" action="" method="post" onsubmit="gctcf_show_loader();">

                                <div class="gc-md-12 gc-sm-12 content_section">
                                    <input type="hidden" id="hotel__country__code" name="country_code" value="<?php echo $country_code; ?>">
                                    <?php
                                    if (isset($_REQUEST['hotel-region-id']) && !empty($_REQUEST['hotel-region-id'])) {
                                    ?>
                                        <p>Hotel Name : <?php echo sanitize_text_field($_REQUEST['hotel-name']); ?></p>

                                        <div class="gc-form-group gc-md-10 gc-sm-10 gc-xs-12 hotel_list_both_padding padding-01" style="width: 83.33%;float: left;padding: 0px 10px;">
                                            <input type="text" name="hotel_name" id="hotel-name" class="form-control" onkeyup="hotel_search()" placeholder=" Destination: Region name...." autocomplete="off" required="required" value="<?php if (!empty($_REQUEST['hotel-region-name'])) {
                                                                                                                                                                                                                                                echo sanitize_text_field($_REQUEST['hotel-region-name']);
                                                                                                                                                                                                                                            } ?>" />

                                            <input type="hidden" name="hotel_regionid" value="<?php if (!empty($_REQUEST['hotel-region-id'])) {
                                                                                                    echo sanitize_text_field($_REQUEST['hotel-region-id']);
                                                                                                }  ?>" id="hotel__region__id" />
                                        </div>

                                    <?php
                                    } else {
                                    ?>

                                        <div class="gc-form-group gc-md-10 gc-sm-10 gc-xs-12 hotel_list_both_padding padding-01" style="width: 83.33%;float: left;padding: 0px 10px;">
                                            <input type="text" style="font-size:14px;margin-bottom: 10px;" name="hotel_name" id="hotel-name" class="form-control" onkeyup="hotel_search()" placeholder=" Destination: Region name..." autocomplete="off" required="required" value="<?php if (!empty($hotel_name)) {
                                                                                                                                                                                                                                                                                        echo sanitize_text_field($hotel_name);
                                                                                                                                                                                                                                                                                    } ?>" />

                                            <input type="hidden" name="hotel_regionid" value="<?php echo htmlentities(json_encode($hotel_region_id)); ?>" id="hotel__region__id" />
                                        </div>

                                    <?php
                                    }
                                    ?>


                                    <div class="gc-form-group gc-md-2">
                                        <input type="submit" name="hotel_list" id="roomxml_hotel_search" class="btn btn-primary btn-block roomxml_hotel_search" value="SEARCH" />
                                    </div>
                                </div>
                                <div class="gc-md-12 gc-sm-12 gc-xs-12 hotel_dropdown_section_hotel_search">
                                    <div class="gc-form-group gc-md-4 gc-sm-6 gc-xs-12 hotel_view_list" id="hotel_result"></div>
                                    <div class="gc-form-group gc-md-8 gc-sm-6 gc-xs-12"></div>
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
                                <option value="asc" <?php if ($gctcf_ratings == 'asc')    echo 'selected'; ?>>Asc</option>
                                <option value="desc" <?php if ($gctcf_ratings == 'desc')    echo 'selected'; ?>>Desc</option>
                            </select>

                        </div>

                        <div class="price_slider_section">
                            <label>Filter by Price</label>
                            <select id="gctcf_prices">
                                <option value="">Default</option>
                                <option value="asc" <?php if ($gctcf_prices == 'asc')    echo 'selected'; ?>>Asc</option>
                                <option value="desc" <?php if ($gctcf_prices == 'desc')    echo 'selected'; ?>>Desc</option>
                            </select>
                        </div>

                        <div class="price_slider_section">
                            <label>Filter by Board Basis</label>
                            <select id="gctcf_type">
                                <option value="">Default</option>
                                <option value="bed-breakfast" <?php if ($gctcf_type == 'bed-breakfast')    echo 'selected'; ?>>Bed & Breakfast</option>
                                <option value="Room Only" <?php if ($gctcf_type == 'Room Only')    echo 'selected'; ?>>Room Only</option>
                                <option value="Half Board" <?php if ($gctcf_type == 'Half Board')    echo 'selected'; ?>>Half Board</option>
                                <option value="Full Board" <?php if ($gctcf_type == 'Full Board')    echo 'selected'; ?>>Full Board</option>
                                <option value="Self Catering" <?php if ($gctcf_type == 'Self Catering')    echo 'selected'; ?>>Self Catering</option>
                                <option value="All Inclusive" <?php if ($gctcf_type == 'All Inclusive')    echo 'selected'; ?>>All Inclusive</option>
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
if (!empty($merged_results)) : ?>
    <section class="section clearfix">
        <div class="total_results px-5" stuba="<?php echo count($stuba_all); ?>" travellanda="<?php echo count($travelnda_all); ?>" tbo="<?php echo count($tbo_all); ?>">
            <?php if (!$hotel_id) : ?> Results: <?php echo count($gctcf_all);
                                                                                                                                                                                        endif; ?></div>
        <div class="container1" id="paging_container3">
            <div class="alt_content">


                <!-- HTML code -->
                <div class="container mx-auto p-5 gc-row hotel__search_result_view <?= $merged_result['feed'] ?>">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($merged_results as $merged_result) :  ?>

                            <?php if (!$hotel_id) : ?>



                                <!-- Card 1 -->
                                <div class="card p-5">
                                    <a class="gctcf-search-result-inner" target="_blank" href="<?= home_url() ?>/hotel-details/?hotel-id=<?= $merged_result['hotel_id'] ?>">
                                        <img class="w-full h-64 object-cover" src="<?= $merged_result['feed']=='stuba'?'https://api.stuba.com'.$merged_result['hotel_image']:$merged_result['hotel_image'] ?>" alt="Image of <?=$merged_result['hotel_name']?>">
                                    </a>
                                    <div class="mt-4">
                                        <a class="gctcf-search-result-inner" target="_blank" href="<?= home_url() ?>/hotel-details/?hotel-id=<?= $merged_result['hotel_id'] ?>">
                                            <h3 class="text-xl font-bold" style="min-height: 56px;"><?php echo $merged_result['hotel_name']; ?></h3>
                                        </a>
                                        <?php 
                                            $paragraph = strip_tags($merged_result['hotel_desc']);  
                                            if(strlen($paragraph) > 250){
                                                $pos = strpos($paragraph, '. ',250);
                                                if ($pos !== false) {
                                                    $result = substr($paragraph, 0, $pos + 2);
                                                } else {
                                                    $result = $paragraph;
                                                }
                                            } else {
                                                $result = $paragraph;
                                            }

                                        ?>
                                        <p class="text-sm mt-1"><?php echo $result; ?></p>
                                       
                                        <div class="flex items-center justify-between mt-3">
                                            <span class="rating">8.6 Excellent</span>
                                            <span class="review">1,698 reviews</span>
                                        </div>
                                        <div class="flex items-center justify-between mt-3">
                                            <span class="price">£629</span>
                                            <span class="price-old">£740</span>
                                        </div>
                                        <div class="flex items-center justify-between mt-3">
                                            <span class="text-xs">for 4 nights</span>
                                            <span class="badge">15% off</span>
                                        </div>
                                    </div>
                                </div>



                            <?php endif; ?>


                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <?php if (count($gctcf_all) >= 100) : ?>
                <div class="gctcf-show-more">
                    <form method="post" id="gctcf-show-all-hotels">
                        <input type="hidden" name="gctc_show_all" value="1">
                        <input type="hidden" name="country_code" value="<?php echo $country_code; ?>">
                        <input type="hidden" name="hotel_name" value="<?php echo $hotel_name; ?>">
                        <input type="hidden" name="hotel_regionid" value='<?php echo $hotel_regionid_encoded; ?>'>
                        <input type="hidden" name="hotel_country_by_region" value="<?php echo $hotel_country_by_region; ?>">
                        <input type="hidden" name="hotel__region__name" value="<?php echo $hotel__region__name; ?>">
                        <input type="hidden" name="hotel_check_in_date" value="<?php echo $hotel_start_date; ?>">
                        <input type="hidden" name="hotel_night" value="<?php echo $hotel_night; ?>">
                        <?php if ($rooms_requested) : ?>
                            <?php
                            for ($i = 1; $i <= count($rooms_requested); $i++) {
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

<?php else : ?>
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
if (!$show_merged && !empty($room_api_xml_response_for_hotel_search->HotelAvailability)) { ?>

    <section class="section gc-clearfix">
        <div class="container" id="paging_container3">
            <div class="alt_content">
                <?php

                if (!empty($room_api_xml_response_for_hotel_search->HotelAvailability)) {
                    $room_api_xml_response = $room_api_xml_response_for_hotel_search->HotelAvailability;
                    $total_hotels = count($room_api_xml_response);
                    $id = 1;
                    foreach ($room_api_xml_response as $hotal_array) {
                ?>




                        <div class="gc-row hotel__search_result_view">
                            <div class="gc-lg-3">
                                <?php
                                $hotel_list = $hotal_array->Hotel;
                                $hotel_name = xml_attribute($hotel_list, 'name');
                                $hotel_id = xml_attribute($hotel_list, 'id');
                                $dir = wp_get_upload_dir();
                                $directory_path =  $dir['basedir'];
                                if (file_exists($directory_path . '/hotel_api/' . $hotel_id . '.xml')) {
                                    $xml_hotel_data = file_get_contents($directory_path . '/hotel_api/' . $hotel_id . '.xml');
                                    $avilable_hotel_facility = simplexml_load_string($xml_hotel_data);
                                }
                                ?>
                                <input type="hidden" name="hotel__id" id="hotel__id" value="<?php echo $hotel_id; ?>" />
                                <?php if (!empty($avilable_hotel_facility->Photo)) { ?>
                                    <div class="property_img"><img src="https://api.stuba.com<?php echo $avilable_hotel_facility->Photo->Url; ?>" alt="image"></div>
                                <?php } else { ?>
                                    <img src="<?php echo GCTCF_URL . 'public/images/hotel-placeholder-img.png'; ?>" alt="image">
                                <?php } ?>
                            </div>

                            <div class="gc-lg-9">
                                <a>
                                    <h3><?php echo $hotel_name; ?></h3>
                                </a>
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
                                $more_hotel = 1;
                                foreach ($hotal_array->Result as $room_type) {
                                    //if($more_hotel<=3){
                                ?>
                                    <div class="gc-md-12 mobile-border-hotel-search">
                                        <form action="<?php echo home_url() ?>/hotel-booking/" method="post" name="fr3" onsubmit="gctcf_show_loader();">
                                            <div class="hidden_file">
                                                <?php
                                                $hotel_room_by_id = xml_attribute($room_type, 'id');
                                                ?>
                                                <input type="hidden" name="hotel_quote_id" value="<?php echo $hotel_room_by_id; ?>" />
                                                <?php
                                                foreach ($room_with_adults as $key => $values) {
                                                ?>
                                                    <input type="hidden" name="numbers_of_adults[<?php echo $key; ?>]" value="<?php echo $values; ?>" />
                                                <?php
                                                }

                                                foreach ($room_with_children as $room_with_children_key => $room_with_children_value) {
                                                ?>

                                                    <input type="hidden" name="numbers_of_child[<?php echo $room_with_children_key ?>]" value="<?php echo $room_with_children_value; ?>" />
                                                <?php
                                                }

                                                foreach ($rooms_requested as $rooms_requested_key => $rooms_requested_value) {
                                                ?>
                                                    <input type="hidden" name="numbers_of_room[<?php echo $rooms_requested_key; ?>]" value="<?php echo $rooms_requested_value; ?>" />
                                                <?php
                                                } ?>

                                            </div>
                                            <div class="gc-md-4 mobile-roomtype-result">
                                                <?php $room_type_by_hotel = $room_type->Room->RoomType;
                                                $room_type_name = xml_attribute($room_type_by_hotel, 'text');
                                                ?>
                                                <p><?php echo $room_type_name; ?></p>
                                                <input type="hidden" name="room_type_name_by_hotel" value="<?php echo $room_type_name; ?>" />
                                            </div>
                                            <div class="gc-md-2 mobile-breakfast-result">
                                                <?php $hotel_meal = $room_type->Room->MealType;
                                                $hotel_meal_name = xml_attribute($hotel_meal, 'text');
                                                ?>
                                                <p><?php echo $hotel_meal_name; ?></p>
                                            </div>
                                            <div class="gc-md-2 list-style-hotel1 mobile-price-result">
                                                <?php $hotel_price = $room_type->Room->Price;
                                                $hotel_total_price = xml_attribute($hotel_price, 'amt');

                                                $percentage = get_option('travel_hotel_booking_price_by_parcentage');
                                                $percentage_total = ($percentage / 100) * $hotel_total_price;
                                                $total_price = ($hotel_total_price + $percentage_total);
                                                ?>

                                                <h5><?php echo '£' . round($total_price, 2); ?></h5>
                                            </div>

                                            <div class="gc-md-2 list-style-hotel1 mobile-facility-result">
                                                <button type="button" class="btn btn-primary mobile_bottom_facility pd-md-1" id="hotel__id_ajax_<?php echo $id; ?>" value="<?php echo $hotel_id; ?>" onclick="hotel_facility_ajax(this.id)" data-toggle="modal" data-feed="stuba" data-target="#product_view"><span>Quick </span><span>View</span></button>
                                            </div>

                                            <div class="gc-md-2 list-style-hotel1 mobile-action-result">
                                                <input type="submit" name="hotel__booking" id="hotel__booking" value="BOOK NOW" class="btn btn-primary border-radius pd-md-1" />
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
                    } ?>
                <?php } ?>
            </div>
            <div class="alt_page_navigation"></div>
            <div class="total_results">Result Hotels: <?php echo $total_hotels; ?></div>
        </div>


    </section>
<?php } else { ?>
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