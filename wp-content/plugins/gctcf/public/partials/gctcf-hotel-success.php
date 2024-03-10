<div class="container">
  <div class="row">

    <?php

    $settings = get_api_config();
    $api_url = $settings['url'];
    $api_org = $settings['org'];
    $api_user = $settings['user'];
    $api_pass = $settings['pass'];

    $discount_coupon_code = '';
    $txn_id = $_REQUEST['tx'];
    $test_mode = ($txn_id == 'TEST1234') ? 'true' : 'false';
    $item_number = $_REQUEST['item_number'];
    global $wpdb;
    $sql_txn = "SELECT * FROM `payment_info` WHERE txn_id = '" . $txn_id . "'";
    $result = $wpdb->get_row($sql_txn);

    if ($result) {
      $paypal_unick_code = $result->paypal_unick_code;
      $hotel__quote_id = $result->hotel_quote_id;
      $discount_coupon_amount = $result->coupon_discount_amount;
      $transaction_id = $result->txn_id;
    }

    if (!empty($paypal_unick_code)) {

      if ($item_number == $paypal_unick_code) {

        $select_sql = "SELECT * FROM `user_login_table` WHERE `paypal_unick_code`='" . $paypal_unick_code . "' AND `hotel_quote_id`='" . $hotel__quote_id . "' ";
        $get_sql_result = $wpdb->get_row($select_sql);



        if ($get_sql_result) {
          $id = $get_sql_result->id;
          $update_sql = "UPDATE `user_login_table` SET `payment_status`='Confirm',`transaction_id`='" . $transaction_id . "',`discount_coupon_value`='" . $discount_coupon_amount . "' WHERE `id`='" . $id . "'";
          $wpdb->query($update_sql);
        } ?>

        <div class="" style="padding-top:10px;">
          <?php
          $sql_hotel_id = "SELECT * FROM `user_login_table` WHERE `payment_status`='Confirm' AND `paypal_unick_code`='" . $paypal_unick_code . "'";
          $sql_result = $wpdb->get_row($sql_hotel_id);

          $hotel_quote_id = isset($sql_result->hotel_quote_id) ? $sql_result->hotel_quote_id : '';
          $hotel_price = isset($sql_result->hotel_price) ? $sql_result->hotel_price : '';
          $discount_coupon_code = isset($sql_result->discount_coupon_code) ? $sql_result->discount_coupon_code : '';
          $discount_coupon_amount = isset($sql_result->discount_coupon_value) ? $sql_result->discount_coupon_value : '';

          $user_email = isset($sql_result->user_email) ? $sql_result->user_email : '';
          $user = get_user_by('email', $user_email);
          $user_id = $user->ID;
          $booking_args = array(
            'post_type'   => 'gc4t_hotel_booking',
            'post_status' => 'private',
            'post_author' => $user_id,
            'meta_query' => array(
              'relation' => 'AND',
              array(
                'key' => 'gc4t_feed_type',
                'value' => 'stuba',
              ),
              array(
                'key' => 'gc4t_booking_paypal_unick_code',
                'value' => $paypal_unick_code,
              )
            )
          );
          $booking_query = new WP_Query($booking_args);
          $booking__post_id = 0;


          /************************Adult Section and Child section********************************/

          $sql_adult_and_child = "SELECT * FROM `booking_detials_for_rooxml` WHERE `hotel_quote_id`='" . $hotel_quote_id . "' AND `paypal_unick_code`='" . $paypal_unick_code . "'ORDER BY id ASC";
          $sql_adult_and_child_list = $wpdb->get_results($sql_adult_and_child);


          $str1 = '<Room><Guests>';
          $str2 = '</Guests></Room>';
          $hotel_room_array_added = '';

          foreach ($sql_adult_and_child_list as $sql_adult_and_child_list_val) {

            $adult_str_added = '';
            $adult_list = explode(',', $sql_adult_and_child_list_val->adult);

            foreach ($adult_list as $adult) {
              $adult_array = explode(' ', $adult);
              $first_name = $adult_array[1];
              $last_name = $adult_array[2];
              $last_name = ($last_name) ? $last_name : $first_name;
              $adult_str_list = '<Adult title="' . $adult_array[0] . '" first="' . $first_name . '" last="' . $last_name . '"></Adult>';
              $adult_str_added = $adult_str_added . $adult_str_list;
            }
            $child_str_added = '';
            $child_list = explode(',', $sql_adult_and_child_list_val->children);
            if (!empty($sql_adult_and_child_list_val->children)) {
              foreach ($child_list as $child) {
                $child_array = explode(' ', $child);
                //print_r($adult_array);
                $first_name = $child_array[1];
                $last_name = $child_array[2];
                $last_name = ($last_name) ? $last_name : $first_name;
                $child_str_list = '<Child age="5" title="' . $child_array[0] . '" first="' . $first_name . '" last="' . $last_name . '"></Child>';

                $child_str_added = $child_str_added . $child_str_list;
              }
            }

            $hotel_room_array = $str1 . $adult_str_added . $child_str_added . $str2;
            $hotel_room_array_added = $hotel_room_array_added . $hotel_room_array;
          }
          /********************************Prepare api cal***********************************************/
          $user_xmldata_prepare = '<?xml version="1.0" encoding="utf-8"?>	 
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
                                      <QuoteId>' . $hotel_quote_id . '</QuoteId>
                                      <HotelStayDetails>' . $hotel_room_array_added . '</HotelStayDetails>
                                      <HotelSearchCriteria>
                                        <AvailabilityStatus>allocation</AvailabilityStatus>
                                        <DetailLevel>basic</DetailLevel>
                                      </HotelSearchCriteria>
                                      <CommitLevel>prepare</CommitLevel>
                                    </BookingCreate>';

          //LIVE URL
          $roomxml_prepare_api_url = $api_url;

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $roomxml_prepare_api_url);
          curl_setopt($ch, CURLOPT_HEADER, false);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $user_xmldata_prepare);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-type: text/xml',
            'Content-length: ' . strlen($user_xmldata_prepare)
          ));
          $room_xml_api_output_for_hotel_result_prepare = curl_exec($ch);


          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

          if (curl_exec($ch) === false) {
            //echo 'Curl error: ' . curl_error($ch);
          } else {
            // echo '<br />';
            //echo 'Room Search operation completed';
          }

          curl_close($ch);

          $room_api_xml_response_for_hotel_result_prepare = simplexml_load_string($room_xml_api_output_for_hotel_result_prepare);

          $hotel_booking_prepare_array = $room_api_xml_response_for_hotel_result_prepare->Booking->HotelBooking;
          $prepare_hotel_total_price_width_parcentage = 0;
          if (!empty($hotel_booking_prepare_array)) {
            foreach ($hotel_booking_prepare_array as $hotel_booking_prepare_value) {
              $prepare_hotel_price = $hotel_booking_prepare_value->TotalSellingPrice;
              $prepare_hotel_price = xml_attribute($prepare_hotel_price, 'amt');

              $prepare_percentage = get_option('travel_hotel_booking_price_by_parcentage');
              $percentage_total = ($prepare_percentage / 100) * $prepare_hotel_price;

              $hotel_total_price_for_prepare = ($prepare_hotel_price + $percentage_total);

              $prepare_hotel_total_price_width_parcentage = $prepare_hotel_total_price_width_parcentage + $hotel_total_price_for_prepare;
            } // foreach 
          } //!empty

          $prepare_hotel_total_price_width_parcentage = sprintf('%01.2f', $prepare_hotel_total_price_width_parcentage);
          if ($prepare_hotel_total_price_width_parcentage == $hotel_price) {

            //Check that data has not been manipulated
            if (($result->hotel_total_price + $result->coupon_discount_amount) == $hotel_price) {
              //Mail about booking reference
              $booking_message = 'A new booking was made on GiftCards4Travel. Quote ID: ' . $hotel__quote_id . "\r\n";
              $booking_message .= 'Paypal Transaction ID: ' . $transaction_id;
              wp_mail('robert.campbell63@btinternet.com', 'Stuba Booking Payment Complete - ' . $hotel__quote_id, $booking_message);
              // wp_mail('ajay@cmitexperts.com', 'Stuba Booking Payment Complete - ' . $hotel__quote_id, $booking_message);
              /********************************Confirm api cal***********************************************/
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
                                <QuoteId>' . $hotel_quote_id . '</QuoteId>
                                <HotelStayDetails>' . $hotel_room_array_added . '</HotelStayDetails>
                                <HotelSearchCriteria>
                                  <AvailabilityStatus>allocation</AvailabilityStatus>
                                  <DetailLevel>basic</DetailLevel>
                                </HotelSearchCriteria>
                                <CommitLevel>confirm</CommitLevel>
                              </BookingCreate>';
              file_put_contents(dirname(__FILE__) . '/test.txt', print_r($_REQUEST, true) . "\r\n" . print_r($result, true) . "\r\n" . print_r($sql_result, true) . "\r\n" . print_r($user_xmldata, true));

              //LIVE URL
              $roomxml_api_url = $api_url;

              $bypass = false;
              $room_xml_api_output_for_hotel_search = '';
              if (!$bypass) {
                $ch = curl_init();
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
                  'Content-length: ' . strlen($user_xmldata)
                ));

                $room_xml_api_output_for_hotel_search = curl_exec($ch);


                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                if (curl_exec($ch) === false) {
                  echo 'Curl error: ' . curl_error($ch);
                } else {
                  // echo '<br />';
                  //echo 'Room Search operation completed';
                }

                curl_close($ch);
              }

              if ($room_xml_api_output_for_hotel_search) {
                file_put_contents(dirname(__FILE__) . '/confirm.txt', print_r($room_xml_api_output_for_hotel_search, true));
                $room_api_xml_response_for_hotel_search = simplexml_load_string($room_xml_api_output_for_hotel_search);
                if (isset($room_api_xml_response_for_hotel_search->Error->Code)) {
                  $error_content = "There was an error returned from a  Stuba Booking Attempt\r\n";
                  $error_content .= "QuoteId: $hotel_quote_id\r\n";
                  $error_content .= "User details:\r\n";
                  $error_content .= $hotel_room_array_added . "\r\n";
                  $error_content .= "Error: " . $room_api_xml_response_for_hotel_search->Error->Code . " - " . $room_api_xml_response_for_hotel_search->Error->Description;
                  wp_mail('robert.campbell63@btinternet.com', 'Stuba Booking Error - ' . $hotel__quote_id, $error_content);
                  // wp_mail('ajay@cmitexperts.com', 'Stuba Booking Error - ' . $hotel__quote_id, $error_content);
                } else {
                  echo '<div class="gc-append-text">Hotel price is append.Total amount refend with 24 hs.Txn id=' . $transaction_id . '</div>';
                } //check for preper price = confirm price.... 

                $hotel_booking_array = $room_api_xml_response_for_hotel_search->Booking->HotelBooking;
                $confirm_hotel_total_price_width_parcentage = 0;
                $count = 1;

                if (!empty($hotel_booking_array)) {
                  echo '<h2>Confirm Ticket</h2>';
                  foreach ($hotel_booking_array as $hotel_booking_array_value) { ?>

                    <div class="col-md-12 col-sm-12 col-sx-12 gc-hotel-booking-sucees-data">

                      <h5>Room <?php echo $count; ?></h5>
                      <div class="client_confirm_section">
                        <h3><?php echo $hotel_booking_array_value->HotelName; ?></h3>
                        <ul>
                          <li><?php echo '<label>Hotel Booking Id   :</label><strong>' . $hotel_booking_array_value->Id;  ?></strong></li>
                          <li><?php echo '<label>Hotel Id  :</label><strong>' . $hotel_booking_array_value->HotelId; ?></strong></li>
                          <li><?php echo '<label>Creation Date  :</label><strong>' . $hotel_booking_array_value->CreationDate; ?></strong></li>
                          <li><?php echo '<label>Arrival Date  :</label><strong>' . $hotel_booking_array_value->ArrivalDate;  ?></strong></li>
                          <li><?php echo '<label>Nights  :</label><strong>' . $hotel_booking_array_value->Nights; ?></strong></li>
                          <?php
                          $hotel_price = $hotel_booking_array_value->TotalSellingPrice;
                          $hotel_price = xml_attribute($hotel_price, 'amt');
                          $percentage = get_option('travel_hotel_booking_price_by_parcentage');
                          $percentage_total = ($percentage / 100) * $hotel_price;
                          $hotel_total_price = ($percentage_total + $hotel_price);


                          $hotel_room_type_code = $hotel_booking_array_value->Room->RoomType;
                          $hotel_room_type_code_view = xml_attribute($hotel_room_type_code, 'code');



                          $hotel_room_type_name = $hotel_booking_array_value->Room->RoomType;
                          $hotel_room_name = xml_attribute($hotel_room_type_name, 'text');



                          $hotel_meal_type = $hotel_booking_array_value->Room->MealType;
                          $hotel_meal_type_view = xml_attribute($hotel_meal_type, 'text');


                          ?>
                          <li><?php echo '<label>Price  :</label><strong>' . $hotel_total_price; ?></strong></li>
                          <li><?php echo '<label>Room Code :</label><strong>' . $hotel_room_type_code_view; ?></strong></li>
                          <li><?php echo '<label>Room Name  :</label><strong>' . $hotel_room_name; ?></strong></li>
                          <li><?php echo '<label>Meal Type   :</label><strong>' . $hotel_meal_type_view; ?></strong></li>
                        </ul>
                      </div>

                      <?php
                      $adult_guest_list = $hotel_booking_array_value->Room->Guests->Adult;
                      echo '<h6>Adult</h6>';
                      $adult_str_guest = '';
                      ?>
                      <ul>
                        <?php
                        foreach ($adult_guest_list as $hotel_guests_list) {
                          $adult_title = xml_attribute($hotel_guests_list, 'title');
                          $adult_first_nmae = xml_attribute($hotel_guests_list, 'first');
                          $adult_last_nmae = xml_attribute($hotel_guests_list, 'last');

                        ?>
                          <li class="hotel_preper_adult"><?php echo $adult_str_guest = $adult_title . ' ' . $adult_first_nmae . '  ' . $adult_last_nmae; ?></li>
                        <?php
                        }
                        ?>
                      </ul>

                      <?php
                      $child_guest_list = $hotel_booking_array_value->Room->Guests->Child;
                      if (!empty($child_guest_list)) {
                        echo '<h6>Child</h6><ul>';
                        $child_str_guest = '';
                        foreach ($child_guest_list as $hotel_child_list) {

                          $child_title = xml_attribute($hotel_child_list, 'title');
                          $child_first_name = xml_attribute($hotel_child_list, 'first');
                          $child_last_name = xml_attribute($hotel_child_list, 'last');
                      ?>
                          <li class="hotel_preper_child"><?php echo $child_str_guest = $child_title . ' ' . $child_first_name . '  ' . $child_last_name; ?></li>
                      <?php
                        }
                        echo '</ul>';
                      }
                      ?>


                    </div>
              <?php

                    $confirm_hotel_total_price_width_parcentage = $confirm_hotel_total_price_width_parcentage + $hotel_total_price;
                    $count++;
                  } // foreach
                  if ($booking_query->have_posts()) {
                    while ($booking_query->have_posts()) {
                      $booking_query->the_post();
                      $booking__post_id = get_the_id();
                    }
                  }
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
                                                <td colspan="3"><h2 style="font-size: 18px; background-color: #fad38f; padding: 10px 15px; text-align: center; margin: 0px;font-family: sans-serif; color: #061d2f; font-weight:600;">Confirm Ticket</h2></td>
                                            </tr>';
                  $i = 1;
                  foreach ($hotel_booking_array as $hotel_booking_array_value) {
                    $email_content .= '
                                              <tr><td colspan="3"><table width="100%" style="max-width: 1200px; margin: 0 auto; padding: 15px; font-family:Arial, Helvetica, sans-serif; border:1px solid #061d2f; color:#061d2f" cellpadding="0" cellspacing="0"><tr>
                                                              <td align="left" colspan="3"style="font-size: 20px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Room ' . $i . '</strong></td>
                                                          </tr>
                                                          <tr>
                                                              <td align="center" colspan="3"style="font-size: 20px;font-family:Arial, Helvetica, sans-serif; text-align: center; padding:5px; padding-top: 20px;"><strong><u>' . $hotel_booking_array_value->HotelName . '</u></strong></td>
                                                          </tr>
                                                          <tr>
                                                              <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Hotel Booking Id :</strong>' . $hotel_booking_array_value->Id . '</td>
                                                              <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Arrival Date :</strong> ' . $hotel_booking_array_value->ArrivalDate . '</td>';
                    $hotel_price = $hotel_booking_array_value->TotalSellingPrice;
                    $hotel_price = xml_attribute($hotel_price, 'amt');
                    $percentage = get_option('travel_hotel_booking_price_by_parcentage');
                    $percentage_total = ($percentage / 100) * $hotel_price;
                    $hotel_total_price = ($percentage_total + $hotel_price);


                    $hotel_room_type_code = $hotel_booking_array_value->Room->RoomType;
                    $hotel_room_type_code_view = xml_attribute($hotel_room_type_code, 'code');



                    $hotel_room_type_name = $hotel_booking_array_value->Room->RoomType;
                    $hotel_room_name = xml_attribute($hotel_room_type_name, 'text');



                    $hotel_meal_type = $hotel_booking_array_value->Room->MealType;
                    $hotel_meal_type_view = xml_attribute($hotel_meal_type, 'text');

                    $email_content .= '<td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Room Code :</strong> ' . $hotel_room_type_code_view . '</td>
                                                          </tr>
                                                          <tr>
                                                              <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Hotel Id :</strong> ' . $hotel_booking_array_value->HotelId . '</td>
                                                              <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Nights :</strong> ' . $hotel_booking_array_value->Nights . '</td>
                                                              <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Room Name :</strong> ' . $hotel_room_name . '</td>
                                                          </tr>
                                                          <tr>
                                                              <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Creation Date :</strong> ' . $hotel_booking_array_value->CreationDate . '</td>
                                                              <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Price :</strong> ' . $hotel_total_price . '</td>
                                                              <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Meal Type :</strong> ' . $hotel_meal_type_view . '</td>
                                                          </tr>';
                    $child_guest_list = $hotel_booking_array_value->Room->Guests->Child;
                    $email_content .= '<tr>
                                                              <td align="left" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Adults</strong></td>';
                    if (!empty($child_guest_list)) {
                      $email_content .= '<td align="left" colspan="2" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Children</strong></td>';
                    }
                    $email_content .= '</tr>';
                    $email_content .= '<tr><td align="left" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 0;">';
                    $adult_guest_list = $hotel_booking_array_value->Room->Guests->Adult;
                    foreach ($adult_guest_list as $hotel_guests_list) {
                      $adult_title = xml_attribute($hotel_guests_list, 'title');
                      $adult_first_nmae = xml_attribute($hotel_guests_list, 'first');
                      $adult_last_nmae = xml_attribute($hotel_guests_list, 'last');
                      $email_content .= '<span style="display:block; width:100%;">' . $adult_title . ' ' . $adult_first_nmae . '  ' . $adult_last_nmae . '</span>';
                    }

                    $email_content .= '</td>';
                    if (!empty($child_guest_list)) {
                      $email_content .= '<td align="left" colspan="2"style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 0;">';
                      foreach ($child_guest_list as $hotel_child_list) {

                        $child_title = xml_attribute($hotel_child_list, 'title');
                        $child_first_name = xml_attribute($hotel_child_list, 'first');
                        $child_last_name = xml_attribute($hotel_child_list, 'last');

                        $email_content .= '<span style="display:block; width:100%;">' . $child_title . ' ' . $child_first_name . ' ' . $child_last_name . '</span>';
                      }
                      $email_content .= '</td>';
                    }
                    $email_content .= '</tr></table>';
                    $i++;
                  }
                  $discount_coupen = get_post_meta($booking__post_id, '_gctcf_coupon_code', true);
                  $booking_total_p = get_post_meta($booking__post_id, '_gctcf_booking_amount', true);
                  if (!empty($discount_coupen)) {
                    $discount_value_p = get_post_meta($booking__post_id, '_gctcf_coupon_amount', true);
                    if ($booking_total_p > $discount_value_p) {
                      $booking_dis_total = $booking_total_p - $discount_value_p;
                    } else {
                      $booking_dis_total = '1.00';
                    }

                    $email_content .= '<table width="100%" style="max-width: 1200px; margin: 0 auto; padding: 15px; font-family:Arial, Helvetica, sans-serif; border:1px solid #061d2f; color:#061d2f" cellpadding="0" cellspacing="0">
                                            <tr>
                                            <td colspan="5" style="padding-top:20px;">
                                              <table width="100%" cellpadding="0" cellspacing="0">
                                              <tr>
                                                <td style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:20px; border:2px solid #ccc;"><strong style="padding-right:15px;">Gift Card Code</strong><span style="color:#061d2f;font-size:16px;font-weight:600;padding:7px 15px;background-color:#f9d28e; display: inline-block;">' . $discount_coupen . '</span></td>
                                                <td style="padding:15px; border:2px solid #ccc; border-left-width:0;"><table width="100%" cellpadding="0" cellspacing="0">
                                                    <tbody>
                                                      <tr>
                                                        <td align="left" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">Transfer Price:</td>
                                                        <td align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;"><strong>£' . $booking_total_p . '</strong></td>
                                                      </tr>
                                                      <tr>
                                                        <td align="left" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">Coupan Price:</td>
                                                        <td  align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;"><strong>£' . $discount_value_p . '</strong></td>
                                                      </tr>
                                                    </tbody>
                                                    <tfoot>
                                                      <tr>
                                                        <td align="left" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px; border-top:2px solid #777;">Total Price:</td>
                                                        <td align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px; border-top:2px solid #777;"><strong>£' . $booking_dis_total . '</strong></td>
                                                      </tr>
                                                    </tfoot>
                                                  </table></td>
                                              </tr>
                                            </table>
                                              </td>
                                              </tr>
                                            </table>';
                  } else {
                    $email_content .= '<table width="100%" style="max-width: 1200px; margin: 0 auto; padding: 15px; font-family:Arial, Helvetica, sans-serif; border:1px solid #061d2f; color:#061d2f" cellpadding="0" cellspacing="0">
                                            <tr>
                                            <td colspan="5" style="padding-top:20px;">
                                            <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>

                                            <td align="right" style=""><table width="100%" cellpadding="0" cellspacing="0" style="max-width:300px;padding:15px; border:2px solid #ccc;">
                                                <tbody>
                                                    <tr>
                                                    <td align="left" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">Transfer Price:</td>
                                                    <td align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;"><strong>£' . $booking_total_p . '</strong></td>
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                    <td align="left" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px; border-top:2px solid #777;">Total Price:</td>
                                                    <td align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px; border-top:2px solid #777;"><strong>£' . $booking_total_p . '</strong></td>
                                                    </tr>
                                                </tfoot>
                                                </table></td>
                                            </tr>
                                            </table>
                                            </td>
                                            </tr>
                                            </table>';
                  }
                  $email_content .= '<p>contact <a href="mailto:bookings@giftcards4travel.co.uk">bookings@giftcards4travel.co.uk</a> for any queries </p></body>
                                            </html>';
                  // echo $email_content;
                  if ($booking_query->have_posts()) {
                    while ($booking_query->have_posts()) {
                      $booking_query->the_post();
                      $booking__post_id = get_the_id();
                      $send_email_to_user = get_post_meta($booking__post_id, 'gc4t_send_user_email', true);
                      update_post_meta($booking__post_id, 'gctcg_booking_response', json_encode($hotel_booking_array));
                      update_post_meta($booking__post_id, '_gctcf_booking_status', 1);
                      update_post_meta($booking__post_id, 'gc4t_booking_transaction_id', $_GET['payment_status']);
                      if (($send_email_to_user == 0) && isset($_GET['payment_status']) && ($_GET['payment_status'] == 'Completed')) {
                        $to = $user_email;

                        $subject = 'Booking confirmation';
                        $headers = array('Content-Type: text/html; charset=UTF-8');
                        update_post_meta($post_id, 'gc4t_send_user_email', 1);
                        wp_mail($to, $subject, $email_content, $headers);
                      }
                    }
                  }
                  wp_reset_postdata();
                } //!epmty
                else {

                  echo '<br>Check Your Email<br>';
                }
              }

              if ($discount_coupon_code) {
                $coupon_wp = new WC_Coupon($discount_coupon_code);

                $amount = $coupon_wp->get_amount();
                if ($amount) {
                  if ($coupon_wp->get_discount_type() != 'percent') {
                    $new_remaining_amount = $amount - $discount_coupon_amount;
                    $coupon_wp->set_amount($new_remaining_amount);
                    $coupon_wp->save();
                  }
                } else {
                  $store_vouchers = get_posts(array(
                    'post_type' => 'gc4t_store_voucher',
                    'post_status' => 'private',
                    'posts_per_page' => 1,
                    'meta_query' => array(
                      array(
                        'key' => 'gc4t_voucher_code',
                        'value' => $discount_coupon_code,
                      )
                    )
                  ));
                  if (isset($store_vouchers[0])) {
                    $voucher_amount = get_post_meta($store_vouchers[0]->ID, 'gc4t_voucher_amount', true);
                    $voucher_amount_remaining = get_post_meta($store_vouchers[0]->ID, 'gc4t_voucher_amount_remaining', true);
                    $new_remaining_amount = $voucher_amount_remaining - $discount_coupon_amount;
                    $new_remaining_amount = max($new_remaining_amount, 0);
                    update_post_meta($store_vouchers[0]->ID, 'gc4t_voucher_amount_remaining', $new_remaining_amount);
                    if ($new_remaining_amount == 0) {
                      update_post_meta($store_vouchers[0]->ID, 'gc4t_voucher_status', 'used');
                    }
                  }
                }
              }
              ?>
              <?php if ($bypass) : ?>
                <p style="text-align:center">Thank you for your payment. You will receive an email with your booking details as soon as your booking has been confirmed.</p>
              <?php endif; ?>
              <script language=JavaScript src="https://portgk.com/create-sale?client=java&MerchantID=2189&SaleID=<?php echo $hotel_quote_id; ?>&Purchases=Hotel,<?php echo $hotel_total_price; ?>"></script>
              <noscript><img src="https://portgk.com/create-sale?client=img&MerchantID=2189&SaleID=<?php echo $hotel_quote_id; ?>&Purchases=Hotel,<?php echo $hotel_total_price; ?>" width="10" height="10" border="0"></noscript>
        </div>
  <?php
            }
          }
        } //check paypal unick code
      } else {
  ?>
  <script>
    function reloadPage() {
      var count = 1;
      if (count < 3) {
        location.reload();
        //alert(count);
        count++;
      }
    }
    setTimeout(reloadPage, 3000);
  </script>

  <div class="gc-payment-check-again">
    <h3>Please wait <br /><span> we are getting booking info!...</span></h3>
    <h4>
      <!--<a href="http://urwebdemoonline.com/pyratechsolutions/payment/" style="color:#fff;">Your payment is not completed yet. please click here for complete your payment</a></h4>-->
      <a href="<?php echo home_url() ?>/<?php echo $_SERVER['REQUEST_URI']; ?>">Check again</a>
    </h4>


  </div>
<?php
      }
?>
  </div>
</div>