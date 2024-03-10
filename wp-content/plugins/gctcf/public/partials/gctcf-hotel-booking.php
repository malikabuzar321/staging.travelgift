<?php
global $wpdb;
if (isset($_POST['hotel_confirm']) && !empty($_POST['hotel_confirm'])) {
  if (isset($_POST['feed_type']) && $_POST['feed_type'] == 'stuba') {
    $hotel__Quote__id     = $_REQUEST['hotel_quote_id'];
    $adult_and_child_list = $_REQUEST['room'];
    $str1 = '<Room><Guests>';
    $str2 = '</Guests></Room>';
    $singel_room_with_guests_append = '';
    if ($adult_and_child_list) :
      foreach ($adult_and_child_list as $adult_and_child_list_key => $result) {
        $child_str_room_added = '';
        foreach ($result as $key => $value) {
          $adult_str_room_added_for_all_guest = '';
          $child_str_room_added_for_all_guest = '';
          if ($key == 'adult') {
            $adult_str_room_added = '';
            $adult_count          = count($value['title']);
            $i                    = 0;
            for ($j = 1; $j <= $adult_count; $j++) {
              $adult_str_room = '<Adult title="' . $value['title'][$i] . '" first="' . $value['first_nmae'][$i] . '" last="' . $value['last_name'][$i] . '"></Adult>';
              $i++;
              $adult_str_room_added = $adult_str_room_added . $adult_str_room;
            }
          }
          if ($key == 'child') {
            $child_str_room_added = '';
            $child_count          = count($value['first_name']);
            $j                    = 0;
            for ($k = 1; $k <= $child_count; $k++) {
              $child_str_room = '<Child age="5" title="' . $value['title'][$j] . '" first="' . $value['first_name'][$j] . '" last="' . $value['last_name'][$j] . '" />';
              $j++;
              $child_str_room_added = $child_str_room_added . $child_str_room;
            }
          }
          $adult_str_room_added_for_all_guest = $adult_str_room_added_for_all_guest . $adult_str_room_added;
          $child_str_room_added_for_all_guest = $child_str_room_added_for_all_guest . $child_str_room_added;
        }
        $singel_room_with_guests = $str1 . $adult_str_room_added_for_all_guest . $child_str_room_added_for_all_guest . $str2;
        $singel_room_with_guests_append = $singel_room_with_guests_append . $singel_room_with_guests;
      }
    endif;
    /************************API calling********************************/
    $test_mode    = (stripos($_REQUEST['user_email'], 'localhost') !== false) ? 'true' : 'false';
    //$test_mode = 'true';
    $settings = get_api_config();
    $api_url = $settings['url'];
    $api_org = $settings['org'];
    $api_user = $settings['user'];
    $api_pass = $settings['pass'];

    $user_xmldata = '<?xml version="1.0" encoding="utf-8"?>
                      <BookingCreate>
                        <Authority>
                          <Org>' . $api_org . '</Org>
                          <User>' . $api_user . '</User>
                          <Password>' . $api_pass . '</Password>
                          <Currency>GBP</Currency>
                          <Language>en</Language>
                          <TestMode>' . $test_mode . '</TestMode>
                          <DebugMode>' . $test_mode . '</DebugMode>
                          <Version>1.28</Version>
                        </Authority>
                        <QuoteId>' . $hotel__Quote__id . '</QuoteId>
                        <HotelStayDetails>' . $singel_room_with_guests_append . '</HotelStayDetails>
                        <HotelSearchCriteria>
                          <AvailabilityStatus>allocation</AvailabilityStatus>
                          <DetailLevel>basic</DetailLevel>
                        </HotelSearchCriteria>
                        <CommitLevel>prepare</CommitLevel>
                      </BookingCreate>';
    //LIVE URL
    $roomxml_api_url = $api_url;;
    $ch              = curl_init();
    curl_setopt($ch, CURLOPT_URL, $roomxml_api_url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $user_xmldata);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-type: text/xml',
      'Content-length: ' . strlen($user_xmldata),
    ));
    $room_xml_api_output_for_hotel_search = curl_exec($ch);
    if ($test_mode == 'true') {
      echo "----------1--------<br>";
      echo '<pre>' . print_r(htmlentities($user_xmldata, true), 1) . '</pre>';
      echo "----------2--------<br>";
      echo '<pre>' . print_r($_REQUEST, true) . '</pre>';
      echo "----------3--------<br>";
      echo '<pre>' . print_r(htmlentities($room_xml_api_output_for_hotel_search, true), 1) . '</pre>';
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if (curl_exec($ch) === false) {
      //echo 'Curl error: ' . curl_error($ch);
    }
    curl_close($ch);
    $room_api_xml_response_for_hotel_search = simplexml_load_string($room_xml_api_output_for_hotel_search);
    //echo "----------4--------<br>";
    //echo '<pre>' . print_r($room_api_xml_response_for_hotel_search, 1) . '</pre>';
  }
  if (isset($_POST['feed_type']) && $_POST['feed_type'] == 'travellanda') {

    require_once GCTCF_PATH . '/includes/Travellanda.class.php';
    $travellanda_settings = get_travellanda_api_config();

    $travellanda = new Travellanda();
    $travellanda->setUsername($travellanda_settings['user']);
    $travellanda->setPassword($travellanda_settings['pass']);
    $travellanda->setMode($travellanda_settings['mode']);
    $option_id     = sanitize_text_field($_POST['hotel_quote_id']);
    $policy_result = $travellanda->hotelPolicies($option_id);
    $policy_result = $policy_result['body'];
    $policy_result = json_decode($travellanda->convertToJson($policy_result), true);
    //pre($policy_result); 
  }
  $paypal_unick_code = mt_rand(10000, 99999) . 'BYC' . time();
?>
  <div class="gc-container">
    <h5>Booking prepare </h5>

    <?php 

    //pre($_POST);
    if (isset($_POST['feed_type']) && $_POST['feed_type'] == 'stuba') :
    ?>
      <div class="gc-row">
        <?php
        $hotel_booking_array   = isset($room_api_xml_response_for_hotel_search->Booking->HotelBooking) ? $room_api_xml_response_for_hotel_search->Booking->HotelBooking : array();
        $hotel_price_calculate = 0;
        $count                 = 1;
        if (!empty($hotel_booking_array)) {
          foreach ($hotel_booking_array as $hotel_booking_array_value) {
            $hotel_price               = $hotel_booking_array_value->TotalSellingPrice;
            $hotel_price               = xml_attribute($hotel_price, 'amt');
            $percentage                = get_option('travel_hotel_booking_price_by_parcentage');
            $percentage_total          = ($percentage / 100) * $hotel_price;
            $hotel_total_price         = round($percentage_total + $hotel_price, 2);
            $hotel_room_type_code      = $hotel_booking_array_value->Room->RoomType;
            $hotel_room_type_code_view = xml_attribute($hotel_room_type_code, 'code');
            $hotel_room_type_name      = $hotel_booking_array_value->Room->RoomType;
            $hotel_room_name           = xml_attribute($hotel_room_type_name, 'text');
            $hotel_meal_type           = $hotel_booking_array_value->Room->MealType;
            $hotel_meal_type_view      = xml_attribute($hotel_meal_type, 'text');
        ?>
            <div class="gc-md-12 gc-sm-12 gc-xs-12 hotel_prepare_list">
              <div class="gc-md-12 gc-sm-12 gc-xs-12">
                <h6>Room <?php echo $count; ?></h6>
                <div class="hotel_prepare_section">
                  <h3>Hotel Name: <?php echo $hotel_booking_array_value->HotelName; ?></h3>
                  <ul>
                    <li><?php echo 'Creation Date  :' . $hotel_booking_array_value->CreationDate; ?></li>
                    <li><?php echo 'Arrival Date  :' . $hotel_booking_array_value->ArrivalDate; ?></li>
                    <li><?php echo 'Nights  :' . $hotel_booking_array_value->Nights; ?></li>
                    <li><?php echo 'Price :£' . $hotel_total_price; ?></li>
                    <li><?php echo 'Room Code :' . $hotel_room_type_code_view; ?></li>
                    <li><?php echo 'Room Name :' . $hotel_room_name; ?></li>
                    <li><?php echo 'Meal Type :' . $hotel_meal_type_view; ?></li>
                  </ul>
                </div>
                <?php
                $adult_guest_list = $hotel_booking_array_value->Room->Guests->Adult;
                echo '<h6>Adult</h6>';
                $adult_str_guest      = '';
                $adult_str_guest_list = '';
                ?>
                <ul class="gc-booking-adult-detail">
                  <?php
                  if ($adult_guest_list) :
                    foreach ($adult_guest_list as $hotel_guests_list) {
                      $adult_title      = xml_attribute($hotel_guests_list, 'title');
                      $adult_first_nmae = xml_attribute($hotel_guests_list, 'first');
                      $adult_last_nmae  = xml_attribute($hotel_guests_list, 'last');
                  ?>
                      <li class="hotel_preper_adult">
                        <?php echo $adult_str_guest = $adult_title . ' ' . $adult_first_nmae . ' ' . $adult_last_nmae; ?>
                      </li>
                  <?php
                      $adult_str_guest_list = $adult_str_guest_list . $adult_str_guest . ',';
                    }
                  endif;
                  ?>
                </ul>
                <?php
                $child_guest_list = $hotel_booking_array_value->Room->Guests->Child;
                if (!empty($child_guest_list)) {
                ?>
                  <ul class="gc-booking-child-detail">
                    <h6>Child</h6>
                    <?php
                    $child_str_guest      = '';
                    $child_str_guest_list = '';
                    foreach ($child_guest_list as $hotel_child_list) {
                      $child_title      = xml_attribute($hotel_child_list, 'title');
                      $child_first_name = xml_attribute($hotel_child_list, 'first');
                      $child_last_name  = xml_attribute($hotel_child_list, 'last');
                    ?>
                      <li class="hotel_preper_child"><?php echo $child_str_guest = $child_title . ' ' . $child_first_name . '  ' . $child_last_name; ?></li>
                    <?php

                      $child_str_guest_list = $child_str_guest_list . $child_str_guest . ',';
                    }
                    ?>
                  </ul>
                <?php } ?>
              </div>
              <?php
              $hotelCanxFees = $hotel_booking_array_value->Room->CanxFees;
              if ($hotelCanxFees) :
              ?>
                <div class="gc-md-12 gc-sm-12 gc-xs-12">
                  <h6>Hotel Cancellation Fee</h6>
                  <div class="gc-md-6 gc-sm-6 gc-xs-12 hotel_Canxfees_section">
                    <?php

                    foreach ($hotelCanxFees as $hotelCanxFees_key => $hotelCanxFees_value) {
                      $count = count($hotelCanxFees_value);
                      $m     = 1;
                      foreach ($hotelCanxFees_value as $hotelCanxFees_value_key => $hotelCanxFees_obj) {
                        $hotelCanxFees_from_date =  xml_attribute($hotelCanxFees_obj, 'from');

                        echo '<p><span>' . $m . '.&nbsp;' . date('d-m-Y', strtotime($hotelCanxFees_from_date)) . '</span>';
                        $hotelCanxFees_amount = $hotelCanxFees_obj->Amount;
                        echo '<span> £' . round(xml_attribute($hotelCanxFees_amount, 'amt'), 2) . '</span>';
                        echo '</p>';
                        $m++;
                      }
                    }
                    ?>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          <?php
            $count++;
            $hotel_price_calculate = $hotel_price_calculate + $hotel_total_price;
            $hotel_id              = $hotel_booking_array_value->HotelId;
            $booking_id            = $hotel_booking_array_value->Id;
            $room_width_adult      = substr($adult_str_guest_list, 0, -1);
            $room_width_child      = substr($child_str_guest_list, 0, -1);
            $room_booking_sql      = "INSERT INTO `booking_detials_for_rooxml`(`id`, `room_id`, `hotel_id`, `paypal_unick_code`, `room_price`, `adult`, `children`, `room_code`,`hotel_Quote_id`, `feed_type`) VALUES ('','" . $booking_id . "','" . $hotel_id . "','" . $paypal_unick_code . "','" . $hotel_total_price . "','" . $room_width_adult . "','" . $room_width_child . "','" . $hotel_room_type_code_view . "','" . $hotel__Quote__id . "', 'stuba')";
            $wpdb->query($room_booking_sql);
            gc4t_add_popular_hotel($hotel_id);
          }
        } // if(!empty)
        else {
          echo '<h5 style="text-algin:center;">This Information has expired, Please try again after some time.</h5>';
        }

        $hotel_name_by_payment = $hotel_booking_array_value->HotelName;
        $hotel_booking_id      = $hotel_booking_array_value->Id;
        $hotel_id              = $hotel_booking_array_value->HotelId;
        $hotel_total_price     = $hotel_price_calculate;
        $hotel_arrival_date    = $hotel_booking_array_value->ArrivalDate;
        $hotel_no_of_night     = $hotel_booking_array_value->Nights;
        $no__of__room          = count($hotel_booking_array);
        $user_first_name       = $_REQUEST['user_first_name'];
        $user_last_name        = $_REQUEST['user_last_name'];
        $user_mobile_no        = $_REQUEST['user_phone_no'];
        $user_email_name       = $_REQUEST['user_email'];
        $user_address1         = $_REQUEST['user_address_1'];
        $user_country          = '';
        $user_state            = $_REQUEST['user_state'];
        $user_city             = $_REQUEST['user_city'];
        $user_zip_code         = $_REQUEST['user_zip_code'];
        $transaction_id        = '';
        $discount_coupon_value = '';
        $user_password         = mt_rand(100000, 999999);
        $user_booking_date = date("Y/m/d");
        if (!email_exists($_REQUEST['user_email'])) {
          $new_user_id_event = 1;
          $new_user_id       = wp_insert_user(array(
            'user_login'      => $user_email_name,
            'user_pass'       => $user_password,
            'user_email'      => $user_email_name,
            'first_name'      => $user_first_name,
            'last_name'       => $user_last_name,
            'role'            => 'customer',
            'user_registered' => date('Y-m-d H:i:s'),
          ));
          add_user_meta($new_user_id_event, '_new_user_event', $new_user_id);
          //Email Section
          $to      = $_REQUEST['user_email'];
          $subject = 'Travel Gift UK registration Success';
          $message = '<html><body>
                    <div style="width:90%;">
                      <table style="width:90%;">
                        <tbody>
                          <tr>
                            <td>User Id</td>
                            <td>' . $user_email_name . '</td>
                          </tr>
                          <tr>
                            <td>Password</td>
                            <td>' . $user_password . '</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>';
          $message .= '</body></html>';
          $headers = 'From:info@travelgift.uk' . "\r\n"; // Set from headers
          $headers .= "MIME-Version: 1.0\r\n";
          $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
          if (wp_mail($to, $subject, $message, $headers)) {
            echo $succmsg = '<div class="gc-md-12 gc-sm-12 gc-xs-12 gctc-mail-response success">Thank you for registering with Travel Gift UK . Please check your email for Login Details.</div>';
          } else {
            echo $un_succmsg = '<div class="gc-md-12 gc-sm-12 gc-xs-12 gctc-mail-response fail">Unable to send mail, please try after some time!!</div>';
          }
        }
        $customer       = get_user_by('email', $_REQUEST['user_email']);
        $booking_number = get_option('gc4t_hotel_booking_number', '1');
        if ($customer) {
          $booking = array(
            'post_type'   => 'gc4t_hotel_booking',
            'post_status' => 'private',
            'post_author' => $customer->ID,
            'post_title'  => 'Stuba Booking - ' . $booking_number,
          );
          $booking_id = wp_insert_post($booking);
          if ($booking_id) {
            update_post_meta($booking_id, 'gc4t_booking_id', 0);
            update_post_meta($booking_id, 'gc4t_send_user_email', 0);
            update_post_meta($booking_id, 'gc4t_booking_number', $booking_number);
            update_post_meta($booking_id, 'gc4t_booking_details', json_encode($hotel_booking_array));
            update_post_meta($booking_id, 'gc4t_booking_transaction_id', 'Pending');
            update_post_meta($booking_id, 'gc4t_booking_payment_status', $transaction_id);
            update_post_meta($booking_id, 'gc4t_booking_paypal_unick_code', $paypal_unick_code);
            update_post_meta($booking_id, 'gc4t_discount_coupon_value', $discount_coupon_value);
            update_post_meta($booking_id, '_gctcf_coupon_amount', $discount_coupon_value);
            update_post_meta($booking_id, '_gctcf_coupon_code', '');
            update_post_meta($booking_id, 'gc4t_hotel_id', (string)$hotel_id);
            update_post_meta($booking_id, 'gc4t_user_first_name', $user_first_name);
            update_post_meta($booking_id, 'gc4t_user_last_name', $user_last_name);
            update_post_meta($booking_id, 'gc4t_user_email', $user_email_name);
            update_post_meta($booking_id, 'gc4t_user_phone', $user_mobile_no);
            update_post_meta($booking_id, 'gc4t_user_address_one', $user_address1);
            update_post_meta($booking_id, 'gc4t_user_country', $user_country);
            update_post_meta($booking_id, 'gc4t_user_state', $user_state);
            update_post_meta($booking_id, 'gc4t_user_city', $user_city);
            update_post_meta($booking_id, 'gc4t_user_zip_code', $user_zip_code);
            update_post_meta($booking_id, 'gc4t_hotel_quote_id', $hotel__Quote__id);
            update_post_meta($booking_id, 'gc4t_hotel_no_of_room', $no__of__room);
            update_post_meta($booking_id, 'gc4t_hotel_booking_id', (string)$hotel_booking_id);
            update_post_meta($booking_id, 'gc4t_hotel_price', $hotel_total_price);
            update_post_meta($booking_id, 'gc4t_room_code', $hotel_room_type_code_view);
            update_post_meta($booking_id, 'gc4t_room_name', $hotel_room_name);
            update_post_meta($booking_id, 'gc4t_meal_type', $hotel_meal_type_view);
            update_post_meta($booking_id, 'gc4t_user_booking_date', $user_booking_date);
            update_post_meta($booking_id, 'gc4t_hotel_arrival_date', (string)$hotel_arrival_date);
            update_post_meta($booking_id, 'gc4t_hotel_night', (string)$hotel_no_of_night);
            update_post_meta($booking_id, 'gc4t_feed_type', 'stuba');
            update_option('gc4t_hotel_booking_number', $booking_number + 1);
          }
        }
        $user_login_aql = "INSERT INTO `user_login_table`(`id`, `payment_status`, `transaction_id`, `paypal_unick_code`, `discount_coupon_value`, `hotel_id`, `user_first_name`, `user_last_name`, `user_email`, `user_phone`, `user_address_one`, `user_country`, `user_state`, `user_city`, `user_zip_code`, `hotel_quote_id`, `hotel_no_of_room`, `hotel_booking_id`, `hotel_price`, `room_code`, `room_name`, `meal_type`, `user_booking_date`, `hotel_arrival_date`, `hotel_night`) VALUES ('','Pending','" . $transaction_id . "','" . $paypal_unick_code . "','" . $discount_coupon_value . "','" . $hotel_id . "','" . $user_first_name . "','" . $user_last_name . "','" . $user_email_name . "','" . $user_mobile_no . "','" . $user_address1 . "','" . $user_country . "','" . $user_state . "','" . $user_city . "','" . $user_zip_code . "','" . $hotel__Quote__id . "','" . $no__of__room . "','" . $hotel_booking_id . "','" . $hotel_total_price . "','" . $hotel_room_type_code_view . "','" . $hotel_room_name . "','" . $hotel_meal_type_view . "','" . $user_booking_date . "','" . $hotel_arrival_date . "','" . $hotel_no_of_night . "')";
        $wpdb->query($user_login_aql);


        if (!empty($hotel_price_calculate)) {
          ?>
          <div class="gc-sm-12 gc-md-12 hotel_prepare_list hotel_prepare_booking-row row-1">
            <div class="gc-sm-6 gc-md-6 prepare_booking-left">
              <h4>Coupon code</h4>
              <label class="" id="coupon_code_smg"></label>
              <div class="gc-form-group">
                <input type="hidden" name="coupon_code_quote_id" value="<?php echo $hotel__Quote__id; ?>" id="coupon_code_quote_id">
                <input type="hidden" name="hotel_post_id" value="<?php echo $booking_id; ?>" id="hotel_post_id">
                <input type="text" name="coupon_code_check" placeholder="Enter Travel Gift coupon code." class="coupon_code_value" id="coupon_code_value" />
              </div>
              <div class="gc-form-group">
                <input type="submit" name="coupon_code_check_ability" class="" onclick="couponCode()" value="Apply" />
                <input type="hidden" name="gctcf_feed_type" value="<?php echo $_POST['feed_type']; ?>">
                <input type="hidden" name="gctcf_amount" value="<?php echo $hotel_price_calculate; ?>">
                <input type="hidden" name="gctcf_coupon_code" class="gctcf_coupon_code">
                <input type="hidden" name="gctcf_coupon_amount" class="gctcf_coupon_amount">
              </div>
            </div>
            <div class="gc-sm-6 gc-md-6 prepare_booking-right" style="text-align:center;">
              <div class="paypal_section">
                <table>
                  <tr>
                    <td>Hotel price :</td>
                    <td><?php echo '£' . $hotel_price_calculate; ?></td>
                  </tr>
                  <tr>
                    <td><span style="display:none;" id="coupon_price_hidden">Coupon price :</span></td>
                    <td><span style="display:none;" class="coupon_price_hidden">£<span id="byc_coupon_amount_total"></span></span></td>
                  </tr>
                  <tr>
                    <td colspan="2">
                      <div style="    border: 0.4px solid #404040;    background-color: #404040;"></div>
                    </td>
                  </tr>
                  <tr>
                    <td>Total Price :</td>
                    <td><span id="hotel_amount_total"><?php echo '£' . $hotel_price_calculate; ?></span></td>
                  </tr>
                </table>
                <div class="gc-md-12 gc-sm-12 poll-right">
                  <form id="payment_form" action="<?php //echo 'https://www.paypal.com/cgi-bin/webscr'; ?>" method="post">
                    <input type="hidden" name="cmd" value="_xclick">
                    <input type="hidden" name="business" value="rob@travelgift.uk">
                    <!-- <input type="hidden" name="business" value="businesscmit@gmail.com"> -->
                    <input type="hidden" name="item_name" value="<?php echo $hotel_name_by_payment; ?>">
                    <input type="hidden" name="item_number" value="<?php echo $paypal_unick_code; ?>">
                    <input type="hidden" name="amount" class="byc_travel_amount" value="<?php echo $hotel_price_calculate; ?>">
                    <input type="hidden" name="discount_amount" class="byc_coupon_amount" value="0">
                    <input type="hidden" name="custom" value="<?php echo $hotel__Quote__id.time(); ?>">
                    <input type="hidden" name="quantity" value="1">
                    <input type="hidden" name="return" value="<?php echo home_url() ?>/payment-success">
                    <input type="hidden" name="notify_url" value="<?php echo home_url() ?>/payment_ipn_file.php">
                    <input type="hidden" name="currency_code" value="GBP">
                    <!-- Enable override of buyers's address stored with PayPal . -->
                    <!--<input type="hidden" name="address_override" value="<?php //echo $hotel__Quote__id;
                                                                            ?>">-->
                    <!-- Set variables that override the address stored with PayPal. -->
                    <input type="hidden" name="first_name" value="<?php echo $user_first_name; ?>">
                    <input type="hidden" name="last_name" value="<?php echo $user_last_name; ?>">
                    <input type="hidden" name="address1" value="<?php echo $user_address1; ?>">
                    <input type="hidden" name="city" value="<?php echo $user_city; ?>">
                    <input type="hidden" name="state" value="<?php echo $user_state; ?>">
                    <input type="hidden" name="zip" value="<?php echo $user_zip_code; ?>">
                    <!-- <input type="hidden" name="country" value="<?php //echo $user_country;
                                                                    ?>">-->
                    <input type="hidden" name="email" value="<?php echo $user_email_name; ?>">
                    <input type="hidden" name="night_phone_a" value="<?php echo $user_mobile_no; ?>">
                    <?php //echo '<input type="image" name="submit" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynow_LG.gif" alt="PayPal - The safer, easier way to pay online">'; ?>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php
        } // if(!empty($hotel_price_calculate)
        ?>

      </div>
    <?php endif; ?>

    <?php
    if (isset($_POST['feed_type']) && $_POST['feed_type'] == 'travellanda') :
      // pre($_POST, 1);
      $hotel_date_time = date_create_from_format('Y-m-d', $_POST['hotel_start_date']);
      $hotel_date_time->add(new DateInterval('P' . ($_POST['hotel_nights']) . 'D'));
      $room_code       = sanitize_text_field($_POST['room_code']);
      $room_meal       = sanitize_text_field($_POST['room_meal']);
      $room_name       = sanitize_text_field($_POST['room_name']);
      $booking_details = array(
        'feed_type'      => sanitize_text_field($_POST['feed_type']),
        'hotel_id'       => sanitize_text_field($_POST['hotel_id']),
        'option_id'      => sanitize_text_field($option_id),
        'check_in_date'  => sanitize_text_field($_POST['hotel_start_date']),
        'check_out_date' => $hotel_date_time->format('Y-m-d'),
        'nights'         => sanitize_text_field($_POST['hotel_nights']),
        'rooms'          => array(),
        'hotel_name'     => $_POST['hotel_name'],
      );
    ?>
      <div class="gc-row">
        <?php if (isset($policy_result['Body']['OptionId'])) {
          if (isset($policy_result['Body']['OptionId'])) : ?>
            <div class="gc-md-12 gc-sm-12 gc-xs-12 hotel_prepare_list">
              <?php

              $hotel_price      = $policy_result['Body']['TotalPrice'];
              $percentage       = get_option('travel_hotel_booking_price_by_parcentage');
              $percentage_total = ($percentage / 100) * $hotel_price;
              $total_price      = round($percentage_total + $hotel_price, 2); ?>
              <div class="hotel_prepare_section">
                <h3>Hotel Name: <?php echo $_POST['hotel_name']; ?></h3>
                <ul>
                  <li><?php echo 'Creation Date  :' . date('Y-m-d'); ?></li>
                  <li><?php echo 'Arrival Date  :' . $_POST['hotel_start_date']; ?></li>
                  <li><?php echo 'Nights  :' . $_POST['hotel_nights']; ?></li>
                  <li><?php echo 'Price :£' . sprintf('%01.2f', $total_price); ?></li>
                  <li><?php echo 'Room Code :' . $_POST['room_code']; ?></li>
                  <li><?php echo 'Room Name :' . $_POST['room_name']; ?></li>
                  <li><?php echo 'Meal Type :' . $_POST['room_meal']; ?></li>
                </ul>
              </div>
            </div>

            <div class="gc-md-12 gc-sm-12 gc-xs-12 hotel_prepare_list Cancellation-policy-list">
              <?php if (isset($policy_result['Body']['Policies']['Policy'])) : ?>
                <h6>Cancellation Policy</h6>
                <div class="gc-md-6 gc-sm-6 gc-xs-12 hotel_Canxfees_section">
                  <ul>
                    <?php
                    //if (is_array($policy_result['Body']['Policies']['Policy'][0])) :
					if (is_array($policy_result['Body']['Policies']['Policy'])) :
                      foreach ($policy_result['Body']['Policies']['Policy'] as $policy) {
                        if (isset($policy['From']) && $policy['From']) {
                          echo '<li>FROM: ' . $policy['From'] . '</li>';
                        }
                        if (isset($policy['Type']) && $policy['Type']) {
                          echo '<li>TYPE: ' . $policy['Type'] . '</li>';
                        }
                        if (isset($policy['Value']) && $policy['Value']) {
                          echo '<li>AMOUNT: ' . $policy['Value'] . '</li>';
                        }
                      }
                    else :
                      $policy =  $policy_result['Body']['Policies']['Policy'];
                      if (isset($policy['From']) && $policy['From']) {
                        echo '<li>FROM: ' . $policy['From'] . '</li>';
                      }
                      if (isset($policy['Type']) && $policy['Type']) {
                        echo '<li>TYPE: ' . $policy['Type'] . '</li>';
                      }
                      if (isset($policy['Value']) && $policy['Value']) {
                        echo '<li>AMOUNT: ' . $policy['Value'] . '</li>';
                      }
                    endif; ?>
                  </ul>
                </div>
              <?php endif; ?>
              <?php if (isset($policy_result['Body']['Restrictions']['Restriction'])) : ?>
                <h6>Restrictions</h6>
                <div class="gc-md-6 gc-sm-6 gc-xs-12 hotel_Restrictions_section">
                  <?php
                  if (is_array($policy_result['Body']['Restrictions']['Restriction'][0])) :
                    foreach ($policy_result['Body']['Restrictions']['Restriction'] as $Restriction) {
                      if ($Restriction) {
                        echo '<p>' . $Restriction . '</p>';
                      }
                    }
                  else :
                    if (isset($policy_result['Body']['Restrictions']['Restriction'])) {
                      echo '<p>' . $policy_result['Body']['Restrictions']['Restriction'] . '</p>';
                    }
                  endif; ?>
                </div>
              <?php endif; ?>
              <?php if (isset($policy_result['Body']['Alerts']['Alert'])) : ?>
                <h6>Alerts</h6>
                <div class="gc-md-12 gc-sm-12 gc-xs-12 hotel_Alerts_section">
                  <?php
                  //pre($policy_result['Body']);
                  if (is_array($policy_result['Body']['Alerts']['Alert'][0])) :
                    foreach ($policy_result['Body']['Alerts']['Alert'] as $Alert) {
                      if ($Alert) {
                        echo '<p>' . $Alert . '</p>';
                      }
                    }
                  elseif(is_array($policy_result['Body']['Alerts']['Alert'])):
                  {
                    foreach ($policy_result['Body']['Alerts']['Alert'] as $Alert) {
                      if ($Alert) {
                        echo '<p>' . $Alert . '</p>';
                      }
                    }
                  }
                  else :
                    if (isset($policy_result['Body']['Alerts']['Alert'])) {
                      echo '<p>' . $policy_result['Body']['Alerts']['Alert'] . '</p>';
                    }
                  endif; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          <?php
          foreach ($_REQUEST['room'] as $room_number => $room) :
            $room_details = array(
              'room_code' => $room_code . '-1-' . ($room_number - 1),
              'room_name' => $room_name,
              'room_meal' => $room_meal,
              'adults'    => array(),
              'children'  => array(),
            );
          ?>
            <div class="gc-md-12 gc-sm-12 gc-xs-12 hotel_prepare_list gc-room-adult-and-child">
              <div class="gc-md-12 gc-sm-12 gc-xs-12">
                <h5>Room <?php echo $room_number; ?></h5>

                <ul class="gc-booking-adult-detail">
                  <h6>Adults</h6>
                  <?php
                  foreach ($room['adult']['title'] as $adult_index => $adult_title) : ?>
                    <li class="hotel_preper_adult"><?php echo $adult_title . ' ' . $room['adult']['first_nmae'][$adult_index] . ' ' . $room['adult']['last_name'][$adult_index]; ?></li>
                  <?php
                    $normalised_title = 'Mr.';
                    switch ($adult_title) {
                      case 'Mr':
                        $normalised_title = 'Mr.';
                        break;
                      case 'Mrs':
                        $normalised_title = 'Mrs.';
                        break;
                      case 'Ms':
                        $normalised_title = 'Miss.';
                        break;
                    }
                    $room_details['adults'][] = array(
                      'title'      => $normalised_title,
                      'first_name' => $room['adult']['first_nmae'][$adult_index],
                      'last_name'  => $room['adult']['last_name'][$adult_index],
                    );
                  endforeach;
                  ?>
                </ul>

                <?php
                if (isset($room['child'])) :
                  echo '<ul class="gc-booking-child-detail"><h6>Children</h6>';
                  foreach ($room['child']['title'] as $child_index => $child_title) :
                ?>
                    <li class="hotel_preper_child"><?php echo $child_title . ' ' . $room['child']['first_name'][$child_index] . ' ' . $room['child']['last_name'][$child_index]; ?></li>
                <?php $room_details['children'][] = array(
                      'title'      => $child_title,
                      'first_name' => $room['child']['first_name'][$child_index],
                      'last_name'  => $room['child']['last_name'][$child_index],
                    );
                  endforeach;
                  echo '</ul>';
                endif;
                ?>
              </div>
            </div>
          <?php

            $booking_details['rooms'][] = $room_details;
          endforeach;

          //Save to DB
          $user_first_name       = $_REQUEST['user_first_name'];
          $user_last_name        = $_REQUEST['user_last_name'];
          $user_mobile_no        = $_REQUEST['user_phone_no'];
          $user_email_name       = $_REQUEST['user_email'];
          $user_address1         = $_REQUEST['user_address_1'];
          $user_country          = '';
          $user_state            = $_REQUEST['user_state'];
          $user_city             = $_REQUEST['user_city'];
          $user_zip_code         = $_REQUEST['user_zip_code'];
          $transaction_id        = '';
          $discount_coupon_value = '';
          $user_password         = mt_rand(100000, 999999);
          $user_booking_date     = date("Y-m-d");
          if (!email_exists($_REQUEST['user_email'])) {
            $new_user_id_event = 1;
            $new_user_id       = wp_insert_user(array(
              'user_login'      => $user_email_name,
              'user_pass'       => $user_password,
              'user_email'      => $user_email_name,
              'first_name'      => $user_first_name,
              'last_name'       => $user_last_name,
              'role'            => 'customer',
              'user_registered' => date('Y-m-d H:i:s'),
            ));
            add_user_meta($new_user_id_event, '_new_user_event', $new_user_id);
            //Email Section
            $to      = $_REQUEST['user_email'];
            $subject = 'Travel Gift UK registration Success';
            $message = '<html><body>
                      <div style="width:90%;">
                        <table style="width:90%;">
                          <tbody>
                            <tr>
                              <td>User Id</td>
                              <td>' . $user_email_name . '</td>
                            </tr>
                            <tr>
                              <td>Password</td>
                              <td>' . $user_password . '</td>
                            </tr>
                          </tbody></table>
                        </div>';
            $message .= '</body></html>';
            $headers = 'From:info@travelgift.uk' . "\r\n"; // Set from headers
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            if (wp_mail($to, $subject, $message, $headers)) {
              echo $succmsg = '<div class="gc-md-12 gc-sm-12 gc-xs-12 gctc-mail-response success">Thank you for registering withTravel Gift UK . Please check your email for Login Details.</div>';
            } else {
              echo $un_succmsg = '<div class="gc-md-12 gc-sm-12 gc-xs-12 gctc-mail-response fail">Unable to send mail, please try after some time!!</div>';
            }
          }
          $customer       = get_user_by('email', $_REQUEST['user_email']);
          $booking_number = get_option('gc4t_hotel_booking_number', '1');
          if ($customer) {
            $booking = array(
              'post_type'   => 'gc4t_hotel_booking',
              'post_status' => 'private',
              'post_author' => $customer->ID,
              'post_title'  => 'Travellanda Booking - ' . $booking_number,
            );
            $booking_id = wp_insert_post($booking);
            if ($booking_id) {
              update_post_meta($booking_id, 'gc4t_booking_id', 0);
              update_post_meta($booking_id, 'gc4t_booking_number', $booking_number);
              update_post_meta($booking_id, 'gc4t_booking_details', json_encode($booking_details));
              update_post_meta($booking_id, 'gc4t_paypal_code', $paypal_unick_code);
              update_post_meta($booking_id, 'gc4t_transaction_id', $transaction_id);
              update_post_meta($booking_id, 'gc4t_status', 'preparing');
              update_post_meta($booking_id, 'gc4t_price', $hotel_price);
              update_post_meta($booking_id, 'gc4t_percentage', $percentage);
              update_post_meta($booking_id, 'gc4t_discount_value', $discount_coupon_value);
              update_post_meta($booking_id, 'gc4t_discount_code', '');
              update_post_meta($booking_id, 'gc4t_discount_amount', '');
              update_post_meta($booking_id, 'gc4t_booking_date', $user_booking_date);
              update_post_meta($booking_id, 'gc4t_user_address_1', $user_address1);
              update_post_meta($booking_id, 'gc4t_user_first_name', $user_first_name);
              update_post_meta($booking_id, 'gc4t_user_last_name', $user_last_name);
              update_post_meta($booking_id, 'gc4t_user_email', $user_email_name);
              update_post_meta($booking_id, 'gc4t_user_phone', $user_mobile_no);
              update_post_meta($booking_id, 'gc4t_user_state', $user_state);
              update_post_meta($booking_id, 'gc4t_user_city', $user_city);
              update_post_meta($booking_id, 'gc4t_user_zip_code', $user_zip_code);
              update_post_meta($booking_id, 'gc4t_receiver_email', '');
              update_post_meta($booking_id, 'gc4t_payer_email', '');
              update_post_meta($booking_id, 'gc4t_payment_date', '');
              update_post_meta($booking_id, 'gc4t_payment_amount', '');
              update_post_meta($booking_id, 'gc4t_payment_currency', '');
              update_post_meta($booking_id, 'gc4t_feed_type', 'travellanda');
              update_post_meta($booking_id, 'gc4t_total_price', $total_price);
              update_post_meta($booking_id, 'gc4t_send_user_email', 0);
              update_option('gc4t_hotel_booking_number', $booking_number + 1);
            }
          }

          if ($total_price) :
          ?>
            <div class="gc-sm-12 gc-md-12 hotel_prepare_list hotel_prepare_booking-row">
              <div class="notice" style="background:#f1f1f1;padding: 5px;font-size: 12px;margin-bottom:10px;">
                <p><strong>Please note all pricing is in GBP (&pound;). If you have a EUR (&euro;) value gift card you can still redeem your card and the amount will be converted to GBP (&pound;) and applied to your purchase. For any queries please contact us at <a style="color:#f8b545;" href="mailto:info@travelgift.uk">info@travelgift.uk</a></strong><p>
              </div>
              <div class="gc-sm-6 gc-md-6 prepare_booking-left">
                <h4>Gift Card Code</h4>
                <label class="" id="coupon_code_smg"></label>
                <div class="gc-form-group">
                  <input type="hidden" name="coupon_code_quote_id" value="<?php echo $booking_id; ?>" id="coupon_code_quote_id">
                  <input type="hidden" name="hotel_post_id" value="<?php echo $booking_id; ?>" id="hotel_post_id">
                  <input type="text" name="coupon_code_check" placeholder="Enter Travel Gift gift card Code." class="coupon_code_value" id="coupon_code_value" />
                </div>
                <div class="gc-form-group">
                  <input type="submit" name="coupon_code_check_ability" class="" onclick="couponCode()" value="Apply" />
                  <input type="hidden" name="gctcf_feed_type" value="<?php echo $_POST['feed_type']; ?>">
                  <input type="hidden" name="gctcf_amount" value="<?php echo $total_price; ?>">
                  <input type="hidden" name="gctcf_coupon_code" class="gctcf_coupon_code">
                  <input type="hidden" name="gctcf_coupon_amount" class="gctcf_coupon_amount">
                </div>
              </div>
              <div class="gc-sm-6 gc-md-6 prepare_booking-right" style="text-align:center;">
                <div class="paypal_section">
                  <table>
                    <tr>
                      <td>Hotel price :</td>
                      <td><?php echo '£' . $total_price; ?></td>
                    </tr>
                    <tr>
                      <td><span style="display:none;" id="coupon_price_hidden">Coupon price :</span></td>
                      <td><span style="display:none;" class="coupon_price_hidden">£<span id="byc_coupon_amount_total"></span></span></td>
                    </tr>
                    <tr>
                      <td colspan="2">
                        <div style="border: 1px solid #ddd;"></div>
                      </td>
                    </tr>
                    <tr>
                      <td>Total Price :</td>
                      <td><span id="hotel_amount_total"><?php echo '£' . $total_price; ?></span></td>
                    </tr>
                  </table>
                  <div class="gc-md-12 gc-sm-12 poll-right">
                    <form id="payment_form" action="https://www.paypal.com/cgi-bin/webscr" method="post">
                      <!-- <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" target="_blank"> -->
                      <input type="hidden" name="cmd" value="_xclick">
                      <input type="hidden" name="business" value="rob@travelgift.uk">
                      <!-- <input type="hidden" name="business" value="businesscmit@gmail.com"> -->
                      <input type="hidden" name="item_name" value="<?php echo $booking_details['hotel_name']; ?>">
                      <input type="hidden" name="item_number" value="<?php echo $paypal_unick_code; ?>">
                      <input type="hidden" name="amount" class="byc_travel_amount" value="<?php echo $total_price; ?>">
                      <input type="hidden" name="discount_amount" class="byc_coupon_amount" value="0">
                      <input type="hidden" name="custom" value="travellanda_<?php echo $booking_id; ?>">
                      <input type="hidden" name="quantity" value="1">
                      <input type="hidden" name="return" value="<?php echo home_url() ?>/payment-success-travellanda">
                      <input type="hidden" name="notify_url" value="<?php echo home_url() ?>/payment_ipn_file.php">
                      <input type="hidden" name="currency_code" value="GBP">
                      <!-- Enable override of buyers's address stored with PayPal . -->
                      <!--<input type="hidden" name="address_override" value="<?php //echo $hotel__Quote__id;
                                                                              ?>">-->
                      <!-- Set variables that override the address stored with PayPal. -->
                      <input type="hidden" name="first_name" value="<?php echo $user_first_name; ?>">
                      <input type="hidden" name="last_name" value="<?php echo $user_last_name; ?>">
                      <input type="hidden" name="address1" value="<?php echo $user_address1; ?>">
                      <input type="hidden" name="city" value="<?php echo $user_city; ?>">
                      <input type="hidden" name="state" value="<?php echo $user_state; ?>">
                      <input type="hidden" name="zip" value="<?php echo $user_zip_code; ?>">
                      <!-- <input type="hidden" name="country" value="<?php //echo $user_country;
                                                                      ?>">-->
                      <input type="hidden" name="email" value="<?php echo $user_email_name; ?>">
                      <input type="hidden" name="night_phone_a" value="<?php echo $user_mobile_no; ?>">
                      <input type="image" name="submit" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynow_LG.gif" alt="PayPal - The safer, easier way to pay online">
                    </form>
                  </div>
                </div>
              </div>
            </div>
        <?php endif;
        } else {
          echo '<h4 class="no-hotel-available">This Information is expired or not available, Please try again after some time.</h4>';
        }
        ?>
      </div>
    <?php endif; ?>
  </div>
<?php
} else if (isset($_REQUEST['feed_type'])) {
?>
  <div class="gc-container">

    <form action="" method="post" name="">
      <?php
      $feed_type                = sanitize_text_field($_REQUEST['feed_type']);
      $room_code                = sanitize_text_field($_REQUEST['room_code']);
      $room_with_adults_booking = $_REQUEST['numbers_of_adults'];
      $hotel_name               = sanitize_text_field($_REQUEST['hotel_name']);
      $hotel_start_date         = sanitize_text_field($_REQUEST['hotel_start_date']);
      $hotel_nights             = sanitize_text_field($_REQUEST['hotel_nights']);
      $hotel_id                 = sanitize_text_field($_REQUEST['hotel_id']);
      $room_meal                = sanitize_text_field($_REQUEST['room_meal']);
      $room_name                = sanitize_text_field($_REQUEST['room_name']);
      $room_price                = sanitize_text_field($_REQUEST['room_price']);
      $room_with_children_booking = $_REQUEST['numbers_of_child'];
      ?>
      <input type="hidden" name="feed_type" value="<?php echo $feed_type; ?>">
      <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">
      <input type="hidden" name="hotel_quote_id" value="<?php echo $_REQUEST['hotel_quote_id']; ?>" class="" id="" />
      <input type="hidden" name="room_code" value="<?php echo $room_code; ?>">
      <input type="hidden" name="hotel_name" value="<?php echo $hotel_name; ?>">
      <input type="hidden" name="hotel_start_date" value="<?php echo $hotel_start_date; ?>">
      <input type="hidden" name="hotel_nights" value="<?php echo $hotel_nights; ?>">
      <input type="hidden" name="room_meal" value="<?php echo $room_meal; ?>">
      <input type="hidden" name="room_name" value="<?php echo $room_name; ?>">

      <div class="gc-row gc-booking-hotel-form gctcf-hotel-details">
        <div class="notice" style="background:#f1f1f1;padding: 5px;font-size: 12px;margin-bottom:10px;">
            <p><strong>Please note all pricing is in GBP (&pound;). If you have a EUR (&euro;) value gift card you can still redeem your card and the amount will be converted to GBP (&pound;) and applied to your purchase. For any queries please contact us at <a style="color:#f8b545;" href="mailto:info@travelgift.uk">info@travelgift.uk</a></strong><p>
          </div>
        <h5>Hotel Details</h5>
        <div class="gc-md-12 gc-sm-12 gc-xs-12">

          <div class="gc-form-group gc-md-4">
            <label>Hotel Name</label>
            <p><?php echo $hotel_name; ?></p>
          </div>
          <div class="gc-form-group gc-md-4">
            <label>Room Type</label>
            <p><?php echo $room_name; ?></p>
          </div>
          <div class="gc-form-group gc-md-4">
            <label>Breakfast</label>
            <p><?php echo $room_meal; ?></p>
          </div>
          <div class="gc-form-group gc-md-4">
            <label>Nights</label>
            <p><?php echo $hotel_nights; ?></p>
          </div>
          <div class="gc-form-group gc-md-4">
            <label>Price</label>
            <p><?php echo '£' . $room_price; ?></p>
          </div>
        </div>

      </div>

      <div class="gc-row gc-booking-hotel-form">
        <h5>Customer Details</h5>
        <div class="gc-md-12 gc-sm-12 gc-xs-12 gctcf-customer-details">
          <div class="gc-form-group gc-md-6">
            <input type="text" class="form-control" name="user_first_name" placeholder="Enter your first name" required="required">
          </div>
          <div class="gc-form-group gc-md-6">
            <input type="text" class="form-control" name="user_last_name" placeholder="Enter your last name" required="required">
          </div>
          <div class="gc-form-group gc-md-6">
            <input type="email" class="form-control" name="user_email" placeholder="Enter your valid email" required="required">
          </div>
          <div class="gc-form-group gc-md-6">
            <input type="text" class="form-control" name="user_phone_no" placeholder="Enter your valid mobile no" required="required">
            <input type="hidden" class="form-control" name="user_address_1" placeholder="Enter your house no" />
            <input type="hidden" class="form-control" name="user_state" placeholder="Enter your street name" />
            <input type="hidden" class="form-control" name="user_city" placeholder="Enter your city name" />
            <input type="hidden" class="form-control" name="user_zip_code" placeholder="Enter your post code" />
          </div>
        </div>

      </div>
      <?php
      /*********************No of Room*************************/
      //count rooms
      $number_of_rooms = count($_REQUEST['numbers_of_room']);
      ?>
      <input type="hidden" name="no__of_room" value="<?php echo $number_of_rooms; ?>" />

      <?php
      for ($i = 1; $i <= $number_of_rooms; $i++) {
        echo '<h5>Room ' . $i . ' - ' . $_REQUEST['room_type_name_by_hotel'] . '</h5>';
        echo '<div class="gc-hotel-booking-adult-row gctcf-adults">
				<h6>Adult</h6>';
        for ($k = 1; $k <= $room_with_adults_booking[$i]; $k++) {
      ?>
          <div class="gc-md-12 gc-sm-12 gc-xs-12">
            <div class="gc-form-group gc-md-2">
              <div class="dropdown mobile_dropdown_width">
                <select class="selectpicker" name="room[<?php echo $i; ?>][adult][title][]" data-style="btn-white">
                  <option value="Mr">Mr</option>
                  <option value="Ms">Ms</option>
                  <option value="Mrs">Mrs</option>
                </select>
              </div>
            </div>
            <div class="gc-form-group gc-md-5">
              <input type="text" class="form-control" name="room[<?php echo $i; ?>][adult][first_nmae][]" placeholder="Enter your first name" onkeypress="return checkSpcialChar(event)" required="required">
            </div>
            <div class="gc-form-group gc-md-5">
              <input type="text" class="form-control" name="room[<?php echo $i; ?>][adult][last_name][]" placeholder="Enter your last name" onkeypress="return checkSpcialChar(event)" required="required">
            </div>
          </div>
        <?php
        }
        echo '</div>';
        if (!empty($room_with_children_booking[$i])) {
          echo '<div class="gc-hotel-booking-adult-row gctcf-childs">
			<h6>Child</h6>';
        }
        for ($m = 1; $m <= $room_with_children_booking[$i]; $m++) {
        ?>
          <div class="gc-md-12 gc-sm-12 gc-xs-12">
            <div class="gc-form-group gc-md-2 gc-sm-6 gc-xs-12">
              <div class="dropdown">
                <select class="selectpicker" name="room[<?php echo $i; ?>][child][title][]" data-style="btn-white">
                  <option value="Mr">Mstr</option>
                  <option value="Ms">Ms</option>
                </select>
              </div>
            </div>
            <div class="gc-form-group gc-md-5 gc-sm-6 gc-xs-12">
              <input type="text" class="form-control" name="room[<?php echo $i; ?>][child][first_name][]" placeholder="Enter your first name" onkeypress="return checkSpcialChar(event)" required="required" />
            </div>
            <div class="gc-form-group gc-md-5 gc-sm-6 gc-xs-12">
              <input type="text" class="form-control" name="room[<?php echo $i; ?>][child][last_name][]" placeholder="Enter your last name" onkeypress="return checkSpcialChar(event)" required="required" />
            </div>
          </div>
      <?php
        }
        if (!empty($room_with_children_booking[$i])) {
          echo '</div>';
        }
      } //no of room
      ?>

      <div class="gc-md-12 gc-sm-12 gc-xs-12">
        <input type="submit" name="hotel_confirm" id="review-booking-button" class="btn btn-primary btn-block" value="Review Booking" />
      </div>
  </div>
  </form>
  </div>
<?php } ?>
<div class="gc-sm-12 gc-md-12" style="width: 100%;">
  <div id="payment-iframe"></div>
  <script src="https://sdk.felloh.com/"></script>
  <script type="text/javascript">
      <?php $mode = get_option('options__felloh_api_mode'); if($mode == 'sandbox'): $public_key = get_option('options__felloh_sandbox_public_key');?>
      var SDK = new FellohPayments('payment-iframe', '<?=$public_key?>',{"sandbox":true});
      <?php else: $public_key = get_option('options__felloh_live_public_key');?>
      var SDK = new FellohPayments('payment-iframe', '<?=$public_key?>');
      <?php endif; ?>
      jQuery.ajax({
        url: '<?=get_site_url()?>/wp-admin/admin-ajax.php?action=felloh_create_ecom',
        method: 'POST',
        dataType: 'json',
        data: {"action": "felloh_create_ecom","form_data":jQuery("#payment_form").serialize()},
        success: function(response) {
          if(response.data.length != 0){
            SDK.render(response.data.id);
          } else {
            console.table(response);
            alert("Could not render payment form. Please try again at some time later.");
          }
        }
      })
      SDK.onSuccess(function(data){
        var txn_id = data?.transaction?.id;
        jQuery.ajax({
          url: "<?=get_site_url()?>/wp-admin/admin-ajax.php",
          method: "POST",
          dataType: 'json',
          data: {'action':'hotel_booking_payment_confirmation',"form_data":jQuery("#payment_form").serialize()+"&txn_id="+txn_id},
          success: function(response){
            if(response){
              var address = "<?=home_url().($booking_id?'/payment-success-travellanda':'/payment-success')?>?item_number=<?=$paypal_unick_code?>&tx="+txn_id;
              console.log(address);
              location.href=address;
            }
          },
          error: function(xhr, status, error) {
            console.error("AJAX Error:", error);
        }
        })
        console.log("Payment Successful");
      });
      SDK.onDecline(function(){
        console.log("Payment Declined");
      });  
  </script>
</div>