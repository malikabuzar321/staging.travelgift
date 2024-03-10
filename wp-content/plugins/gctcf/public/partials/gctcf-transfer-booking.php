<?php

if (isset($_POST['vehicle_book_now']) && !empty($_POST['vehicle_book_now'])) {

  $vehicle_dept_location = isset($_REQUEST['vehicle_dept_location']) ? $_REQUEST['vehicle_dept_location'] : '';
  $vehicle_arrival_location = isset($_REQUEST['vehicle_arrival_location']) ? $_REQUEST['vehicle_arrival_location'] : '';
  $vehicle_sector_type = isset($_REQUEST['vehicle_sector_type']) ? $_REQUEST['vehicle_sector_type'] : '';
  $vehicle_arriving_date = isset($_REQUEST['vehicle_arriving_date']) ? $_REQUEST['vehicle_arriving_date'] : '';
  $vehicle_arriving_time = isset($_REQUEST['vehicle_arriving_time']) ? $_REQUEST['vehicle_arriving_time'] : '';
  $vehicle_return_date = isset($_REQUEST['vehicle_return_date_two']) ? $_REQUEST['vehicle_return_date_two'] : '';
  $vehicle_return_time = isset($_REQUEST['vehicle_return_time']) ? $_REQUEST['vehicle_return_time'] : '';
  $vehicle_adult = isset($_REQUEST['vehicle_adult']) ? $_REQUEST['vehicle_adult'] : 0;
  $vehicle_children = isset($_REQUEST['vehicle_children']) ? $_REQUEST['vehicle_children'] : 0;
  $vehicle_type = isset($_REQUEST['vehicle_type']) ? $_REQUEST['vehicle_type'] : '';
  $transfer_code = isset($_REQUEST['transfer_code']) ? $_REQUEST['transfer_code'] : '';

  $hotel_latitude = isset($_REQUEST['hotel_latitude']) ? $_REQUEST['hotel_latitude'] : 0;
  $hotel_longitude = isset($_REQUEST['hotel_longitude']) ? $_REQUEST['hotel_longitude'] : 0;


  $settings = get_transfer_api_config();
  $user_xmldata = '<?xml version="1.0" encoding="UTF-8"?>
                    <TCOML version="NEWFORMAT">
                      <TransferOnly>
                        <Booking>
                          <Reserve>
                            <Username>' . $settings['user'] . '</Username>
                            <Password>' . $settings['pass'] . '</Password>      
                            <Lang>EN</Lang>
                            <DeparturePointCode>' . $vehicle_dept_location . '</DeparturePointCode>
                            <ArrivalPointCode>' . $vehicle_arrival_location . '</ArrivalPointCode>
                            <SectorType>' . $vehicle_sector_type . '</SectorType>
                            <ArrDate>' . $vehicle_arriving_date . '</ArrDate>
                            <ArrTime>' . $vehicle_arriving_time . '</ArrTime>
                            <RetDate>' . $vehicle_return_date . '</RetDate>
                            <RetTime>' . $vehicle_return_time . '</RetTime>
                            <Adults>' . $vehicle_adult . '</Adults>
                            <Children>' . $vehicle_children . '</Children>
                            <Infants>0</Infants>
                            <TransferCode>' . $transfer_code . '</TransferCode> 
                            <Latitude></Latitude>
                            <Longitude></Longitude> 
                          </Reserve>
                        </Booking>
                      </TransferOnly>
                    </TCOML>';

  $p2p_api_url = $settings['url'];

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $p2p_api_url);
  curl_setopt($ch, CURLOPT_HEADER, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($ch, CURLOPT_POST, false);
  curl_setopt($ch, CURLOPT_HTTPGET, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $user_xmldata);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-type: text/xml',
    'Content-length: ' . strlen($user_xmldata)
  ));

  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

  $a2b_xml_api_output_for_cabs_search = curl_exec($ch);

  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  if (curl_exec($ch) === false) {
    //echo 'Curl error: ' . curl_error($ch);
  } else {
    // echo '<br />';
    //echo 'Room Search operation completed';

  }

  curl_close($ch);

  $p2p_api_xml_response_for_cars_booking = simplexml_load_string($a2b_xml_api_output_for_cabs_search);
  if (!empty($p2p_api_xml_response_for_cars_booking
    ->TransferOnly
    ->Booking
    ->Reserve
    ->TransacNo)) {
    $client_transac_no = $p2p_api_xml_response_for_cars_booking
      ->TransferOnly
      ->Booking
      ->Reserve->TransacNo;
  }
  if (!empty($p2p_api_xml_response_for_cars_booking
    ->TransferOnly
    ->Booking
    ->Reserve
    ->HolidayValue)) {
    $transfer_holiday_price = $p2p_api_xml_response_for_cars_booking
      ->TransferOnly
      ->Booking
      ->Reserve->HolidayValue;
  }
}

if (!empty($client_transac_no) || isset($_POST['client_transac_no'])) {

  if (isset($_POST['client_confirm_now']) && !empty($_POST['client_confirm_now'])) {

    $paypal_unick_code = $paypal_unick_code = mt_rand(10000, 99999) . 'G4U' . time();

    $client_title = $_REQUEST['title_by_name'];
    $client_first_name = $_REQUEST['client_first_name'];
    $client_last_name = $_REQUEST['client_last_name'];
    $client_city = 0;
    $client_state = 0;
    $client_country = 'Great Britain';
    $client_country_code = $_REQUEST['client_country_name'];
    $client_phone_no = $_REQUEST['client_phone_no'];
    $client_mobile_no = $_REQUEST['client_mobile_no'];
    $client_email = $_REQUEST['client_email'];
    $client_property_name = $_REQUEST['client_property_name'];
    $client_address_1 = $_REQUEST['client_address_1'];
    $client_address_2 = $_REQUEST['client_address_2'];
    $client_refeerence = $_REQUEST['client_refeerence'];
    $client_dept_point = $_REQUEST['client_dept_point'];
    $client_ret_point = $_REQUEST['client_ret_point'];
    $client_dep_info = $_REQUEST['client_dep_info'];
    $client_ret_info = $_REQUEST['client_ret_info'];
    $client_dep_ext_info = $_REQUEST['client_dep_ext_info'];
    $client_ret_ext_info = $_REQUEST['client_ret_ext_info'];
    $client_by_email_send = $_REQUEST['client_send_by_email'];
    $client_remark = $_REQUEST['client_remark'];

    $client_transac_no = $_REQUEST['client_transac_no'];
    $transfer_holiday_price = $_REQUEST['transfer_holiday_price'];
    $payment_status = '';
    $transaction_id = '';
    $discount_coupon_value = $_REQUEST['transfer_gctcf_coupon_amount'];
    $discount_coupon_value = ($discount_coupon_value > 0) ? $discount_coupon_value : 0;
    $discount_coupon_code = $_REQUEST['transfer_gctcf_coupon_code'];

    $user_password = mt_rand(100000, 999999);

    if (!email_exists($client_email)) {
      // $new_user_id_event = 1;
      $user_id = wp_insert_user(array(
        'user_login' => $client_email,
        'user_pass' => $user_password,
        'user_email' => $client_email,
        'first_name' => $client_first_name,
        'last_name' => $client_last_name,
        'role' => 'subscriber',
        'user_registered' => date('Y-m-d H:i:s'),
      ));
      // add_user_meta($new_user_id_event, '_new_user_event', $new_user_id);
      //Email Section
      $to = $client_email;
      $subject = 'giftcard4travel.uk registration Success';
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

      $headers = 'From:admin@giftcard4travel.uk' . "\r\n"; // Set from headers
      $headers .= "MIME-Version: 1.0\r\n";
      $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

      if (mail($to, $subject, $message, $headers)) {

        echo "Thank you for registering to the giftcard4travel.co.uk . Please check your email for Login Details.";
      } else {

        $un_succmsg = "Unable to send mail, please try after sometime!!";
      }
    } else {
      $user = get_user_by('email', $client_email);
      $user_id = $user->ID;
    }
    if ($user_id) {
      $booking = array(
        'post_type'   => 'transfer_booking',
        'post_status' => 'private',
        'post_author' => $user_id,
        'post_title'  => 'Transfer booking',
      );
      $booking_id = wp_insert_post($booking);
      if ($booking_id) {
        update_post_meta($booking_id, 'gc4t_title_by_name', $_POST['title_by_name']);
        update_post_meta($booking_id, 'gc4t_client_first_name', $_POST['client_first_name']);
        update_post_meta($booking_id, 'gc4t_client_last_name', $_POST['client_last_name']);
        update_post_meta($booking_id, 'gc4t_client_email', $_POST['client_email']);
        update_post_meta($booking_id, 'gc4t_client_country_name', $_POST['client_country_name']);
        update_post_meta($booking_id, 'gc4t_client_phone_no', $_POST['client_phone_no']);
        update_post_meta($booking_id, 'gc4t_client_mobile_no', $_POST['client_mobile_no']);
        update_post_meta($booking_id, 'gc4t_client_property_name', $_POST['client_property_name']);
        update_post_meta($booking_id, 'gc4t_client_address_1', $_POST['client_address_1']);
        update_post_meta($booking_id, 'gc4t_client_address_2', $_POST['client_address_2']);
        update_post_meta($booking_id, 'gc4t_client_dept_point', $_POST['client_dept_point']);
        update_post_meta($booking_id, 'gc4t_client_dep_info', $_POST['client_dep_info']);
        update_post_meta($booking_id, 'gc4t_client_dep_ext_info', $_POST['client_dep_ext_info']);
        update_post_meta($booking_id, 'gc4t_client_send_by_email', $_POST['client_send_by_email']);
        update_post_meta($booking_id, 'gc4t_client_refeerence', $_POST['client_refeerence']);
        update_post_meta($booking_id, 'gc4t_client_remark', $_POST['client_remark']);
        update_post_meta($booking_id, 'gc4t_client_trans_no', $client_transac_no);
        update_post_meta($booking_id, 'gc4t_transfer_price', $transfer_holiday_price);
        update_post_meta($booking_id, 'gc4t_discount_coupon_value', $discount_coupon_value);
        update_post_meta($booking_id, 'gc4t_discount_coupon_code', $discount_coupon_code);
        update_post_meta($booking_id, 'gc4t_paypal_unique_code', $paypal_unick_code);
        update_post_meta($booking_id, 'gc4t_payment_status', $payment_status);
        update_post_meta($booking_id, 'gc4t_txn_id', $transaction_id);
        update_post_meta($booking_id, 'gc4t_email_send_to_customer', 0);
      }
    }
    global $wpdb;

    $transfer_booking_sql = "INSERT INTO `transfers_booking_table` (`id`, `payment_status`, `transaction_id`, `paypal_unick_code`, `client_title`, `client_fname`, `client_lname`, `client_city`, `client_state`, `client_country`, `client_country_code`, `client_phone`, `client_mobile`, `client_email`, `client_property_name`, `client_address1`, `client_address2`, `client_refeerence`, `client_dept_point`, `client_ret_point`, `client_dept_info`, `client_ret_info`, `client_dept_ext_info`, `client_ret_ext_info`, `client_email_send`, `client_remark`, `client_trans_no`, `transfer_price`,`discount_coupon_value`, `discount_coupon_code`) VALUES ('','" . $payment_status . "','" . $transaction_id . "','" . $paypal_unick_code . "','" . $client_title . "','" . $client_first_name . "','" . $client_last_name . "','" . $client_city . "','" . $client_state . "','" . $client_country . "','" . $client_country_code . "','" . $client_phone_no . "','" . $client_mobile_no . "','" . $client_email . "','" . $client_property_name . "','" . $client_address_1 . "','" . $client_address_2 . "','" . $client_refeerence . "','" . $client_dept_point . "','" . $client_ret_point . "','" . $client_dep_info . "','" . $client_ret_info . "','" . $client_dep_ext_info . "','" . $client_ret_ext_info . "','" . $client_by_email_send . "','" . $client_remark . "','" . $client_transac_no . "','" . $transfer_holiday_price . "','" . $discount_coupon_value . "','" . $discount_coupon_code . "')";

    if ($wpdb->query($transfer_booking_sql)) {
      //$message="New record created successfully";

?>

      <div class="gctcf-loader">
        <div class="gctcf-loader-wrap"><img src="<?php echo GCTCF_URL . 'public/images/loader.gif'; ?>" /></div>
      </div>
      <form action="<?php //echo 'https://www.paypal.com/cgi-bin/webscr';?>" method="post" id="payment_form">
        <input type="hidden" name="cmd" value="_xclick">
        <input type="hidden" name="business" value="rob@travelgift.uk">
        <input type="hidden" name="item_name" value="Travel">
        <input type="hidden" name="item_number" value="<?php echo $paypal_unick_code; ?>">
        <input type="hidden" name="amount" class="byc_travel_amount" value="<?php echo $transfer_holiday_price; ?>">
        <input type="hidden" name="discount_amount" class="byc_coupon_amount" value="<?php echo $discount_coupon_value; ?>">
        <input type="hidden" name="custom" value="<?php echo $client_transac_no; ?>">
        <input type="hidden" name="quantity" value="1">
        <input type="hidden" name="return" value="<?php echo home_url() ?>/transfer-payment-success">
        <input type="hidden" name="notify_url" value="<?php echo home_url() ?>/payment_ipn_file.php">
        <input type="hidden" name="currency_code" value="GBP">
        <input type="hidden" name="first_name" value="<?php echo $client_first_name; ?>">
        <input type="hidden" name="last_name" value="<?php echo $client_last_name; ?>">
        <input type="hidden" name="address1" value="<?php echo $client_address_1; ?>">
        <input type="hidden" name="country" value="<?php echo $client_country; ?>">
        <input type="hidden" name="email" value="<?php echo $client_email; ?>">
        <input type="hidden" name="night_phone_a" value="<?php echo $client_mobile_no; ?>">
        <?php //echo '<input type="submit" name="submit" id="paypal_onlick_transfer" class="gc-paypal-transfer-wrap" value="Buy now"> ';?>
      </form>
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
              // SDK.onRender(function(){
              //   $("body iframe").css('border','none');
              // });
              SDK.onSuccess(function(data){
                var booking_id = "<?=$booking_id?>";
                var paypal_unick_code = "<?=$paypal_unick_code?>";
                var form_data = {
                  'item_name':'Travel', 
                  'paypal_unick_code':"<?=$paypal_unick_code?>", 
                  'travel_transfer_id':"<?=$client_transac_no?>", 
                  'payment_status':'Paid', 
                  'payment_amount':"<?=$transfer_holiday_price?>", 
                  'payment_currency':"GBP", 
                  'txn_id' : data?.transaction?.id,
                  'receiver_email':'rob@travelgift.uk', 
                  'payer_email':'<?=$client_email?>', 
                  'current_payment_date':"<?=date('Y/m/d H:i:s')?>", 
                  'coupon_discount_amount':<?=$discount_coupon_value?>
                };
                var data = {
                  'action':'transfer_payment',
                  'booking_id':booking_id,
                  'form_data':form_data
                };
                console.table(data);
                console.log("Payment Successful");
                jQuery.ajax({
                  url: "<?=get_site_url()?>/wp-admin/admin-ajax.php?action=transfer_payment",
                  method: "POST",
                  dataType: 'json',
                  data: data,
                  success: function(response){
                    if(response){
                      location.href="<?=home_url()?>/transfer-payment-success?tx="+data.trasaction.id+"&item_number="+booking_id
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
      <!-- <script>
        jQuery(document).ready(function() {
          jQuery('#paypal_onlick_transfer').trigger('click')
        });
      </script> -->
    <?php
    } else {

      //echo $message = "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
  } else {
    ?>
    <div class="gc-container">
      <div class="gc-row">
        <form name="" action="" method="post">

          <div class="gc-md-12 gc-sm-12 ">

            <input type="hidden" name="client_transac_no" value="<?php echo $client_transac_no; ?>" />
            <input type="hidden" name="transfer_holiday_price" value="<?php echo $transfer_holiday_price; ?>" />
            <input type="hidden" name="vehicle_sector_type" value="<?php echo $vehicle_sector_type; ?>" />


            <div class="passenger_details">
              <h5>PASSENGER DETAILS</h5>
            </div>
            <div class="passenger-details-row-sec">
              <div class="gc-md-2 gc-sm-2 align_left">
                <div class="form-group">
                  <label for="Titel">Titel<span style="color: #F44336;">*</span></label>
                  <select class="form-control" name="title_by_name" id="" style="height:40px;">
                    <option value="Mr.">Mr.</option>
                    <option value="Ms.">Ms.</option>
                    <option value="Mrs.">Mrs.</option>
                  </select>
                </div>
              </div>

              <div class="gc-md-5 gc-sm-5 align_left">
                <div class="form-group">
                  <label for="FirstName">First Name<span style="color: #F44336;">*</span></label>
                  <input type="text" name="client_first_name" class="form-control" id="" placeholder="Enter your first name" style="height:40px;" required="required" />
                </div>
              </div>

              <div class="gc-md-5 gc-sm-5 align_left">
                <div class="form-group">
                  <label for="Lastname">Last Name<span style="color: #F44336;">*</span></label>
                  <input type="text" name="client_last_name" class="form-control" id="" placeholder="Enter your last name" required="required" style="height:40px;" />
                </div>
              </div>
            </div>
          </div>

          <div class="gc-md-12 gc-sm-12 transfer-emial-other">

            <div class="gc-md-3 gc-sm-3 align_left">
              <div class="form-group">
                <label for="Email">Email<span style="color: #F44336;">*</span> </label>
                <input type="text" name="client_email" class="form-control" id="" placeholder="Enter your email" required="required" style="height:40px;" />
              </div>
            </div>

            <div class="gc-md-3 gc-sm-3 align_left transfer-country-name">
              <div class="form-group">
                <label for="CountryName">Country Name<span style="color: #F44336;">*</span></label>
                <input type="text" name="client_country_name" class="form-control" onkeyup="country_search()" id="country-name" required="required" placeholder="Enter your country name" style="height:40px;" />
                <input type="hidden" name="client_country_name" class="form-control" id="cars__country__code">
              </div>
              <div id="country_code_result" class="form-group gc-md-2 gc-sm-6 gc-xs-12">
              </div>
            </div>

            <div class="gc-md-3 gc-sm-3 align_left">
              <div class="form-group">
                <label for="PhoneNo">Phone No<span style="color: #F44336;">*</span></label>
                <input type="text" name="client_phone_no" class="form-control" id="" placeholder="Enter your phone no" required="required" style="height:40px;" />
              </div>

            </div>

            <div class="gc-md-3 gc-sm-3 align_left">
              <div class="form-group">
                <label for="MobileNo">Mobile No<span style="color: #F44336;">*</span></label>
                <input type="text" name="client_mobile_no" class="form-control" id="" placeholder="Enter your mobile no" required="required" style="height:40px;" />
              </div>
            </div>


          </div>

          <div class="gc-md-12 gc-sm-12 gc-xs-12 transfer-hidden-div">

            <div class="gc-md-3 gc-sm-6 gc-xs-12 align_left">

            </div>
            <div class="gc-md-3 gc-sm-6 gc-xs-12 align_left">
              <div class="form-group">

              </div>
            </div>
            <div class="gc-md-6 gc-sm-6 gc-xs-12 align_left">

            </div>


          </div>

          <div class="gc-md-12 gc-sm-12 ">
            <div class="passenger_details">
              <h5>ACCOMMODATION DETAILS</h5>
            </div>
            <div class="transfer-accommodation-other">
              <div class="gc-md-3 gc-sm-4 align_left">
                <div class="form-group">
                  <label for="PropertyName">Property Name(Hotel/Resort/Station)<span style="color: #F44336;">*</span></label>
                  <input type="text" name="client_property_name" class="form-control" id="" placeholder="If Transfer starts from Hotel/Resort/Station" required="required" style="height:40px;" />
                </div>
              </div>
              <div class="gc-md-3 gc-sm-4 align_left">
                <div class="form-group">
                  <label for="Address1">Address 1(Hotel/Resort/Station/Airpot)<span style="color: #F44336;">*</span></label>
                  <input type="text" name="client_address_1" class="form-control" id="" placeholder="Hotel/Resort/Station Address" required="required" style="height:40px;" />
                </div>
              </div>
              <div class="gc-md-3 gc-sm-4 align_left">
                <div class="form-group">
                  <label for="Address2">Address 2(Hotel/Resort/Station like road name)</label>
                  <input type="text" name="client_address_2" class="form-control" id="" placeholder="Extra information regarding Hotel/Resort/Station Address like Road name,etc.." style="height:40px;" />
                </div>
              </div>
            </div>
          </div>

          <div class="gc-md-12 gc-sm-12 ">
            <div class="passenger_details">
              <h5>JOURNEY 1: FLIGHT DETAILS</h5>
            </div>
            <div class="transfer-accommodation-other">
              <div class="gc-md-3 gc-sm-4 align_left">
                <div class="form-group">
                  <label for="DeparturePoint">Airport Name<span style="color: #F44336;">*</span></label>
                  <input type="text" name="client_dept_point" class="form-control" value="<?php //echo $vehicle_dept_location;
                                                                                          ?>" required="required" id="" placeholder="From which airport reaching PMI?" style="height:40px;" />
                </div>
              </div>

              <div class="gc-md-3 gc-sm-4 align_left">
                <div class="form-group">
                  <label for="DepartureInformations">Flight Number<span style="color: #F44336;">*</span></label>
                  <input type="text" name="client_dep_info" class="form-control" id="" placeholder="By which flight(number) reaching PMI?" required="required" style="height:40px;" />
                </div>
              </div>

              <div class="gc-md-3 gc-sm-4 align_left">
                <div class="form-group">
                  <label for="DepartureExtraInformation<">Airline Name<span style="color: #F44336;">*</span></label>
                  <input type="text" name="client_dep_ext_info" class="form-control" id="" placeholder="By which airline reaching PMI?" required="required" style="height:40px;" />
                </div>
              </div>
            </div>


          </div>

          <?php
          if ($vehicle_sector_type == 'RETURN') { ?>

            <div class="gc-md-12 gc-sm-12 ">
              <div class="passenger_details">
                <h5>JOURNEY 2: FLIGHT DETAILS</h5>
              </div>
              <div class="transfer-accommodation-other">
                <div class="gc-md-3 gc-sm-4 align_left">
                  <div class="form-group">
                    <label for="ReturnPoint">Airport Name<span style="color: #F44336;">*</span></label>
                    <input type="text" name="client_ret_point" class="form-control" value="<?php //echo $vehicle_arrival_location;
                                                                                            ?>" id="" required="required" placeholder="From PMI, leaving to which airport?" style="height:40px;" />
                  </div>
                </div>

                <div class="gc-md-3 gc-sm-4 align_left">
                  <div class="form-group">
                    <label for="ReturnInformations">Flight Number<span style="color: #F44336;">*</span></label>
                    <input type="text" name="client_ret_info" class="form-control" id="" placeholder="From PMI, leaving by which Flight (number)?" required="required" style="height:40px;" />
                  </div>
                </div>

                <div class="gc-md-3 gc-sm-4 align_left">
                  <div class="form-group">
                    <label for="ReturnExtraInformation">Airline Name<span style="color: #F44336;">*</span></label>
                    <input type="text" name="client_ret_ext_info" class="form-control" id="" placeholder="From PMI, leaving by which airline?" required="required" style="height:40px;" />
                  </div>
                </div>
              </div>

            </div>

          <?php
          } else { ?>
            <div class="gc-md-12 gc-sm-12 transfer-hidden-div">
              <div class="gc-md-4 gc-sm-4 align_left">
                <div class="form-group">
                  <label for="ReturnPoint"></label>
                  <input type="hidden" name="client_ret_point" class="form-control" value="0" id="" placeholder="From PMI, leaving to which airport?" style="height:40px;" />
                </div>
              </div>

              <div class="gc-md-4 gc-sm-4 align_left">
                <div class="form-group">
                  <label for="ReturnInformations"></label>
                  <input type="hidden" name="client_ret_info" value="0" class="form-control" id="" placeholder="From PMI, leaving by which Flight (number)?" style="height:40px;" />
                </div>
              </div>

              <div class="gc-md-4 gc-sm-4 align_left">
                <div class="form-group">
                  <label for="ReturnExtraInformation"></label>
                  <input type="hidden" name="client_ret_ext_info" value="0" class="form-control" id="" placeholder="From PMI, leaving by which airline?" style="height:40px;" />
                </div>
              </div>

            </div>

          <?php
          } ?>

          <div class="gc-md-12 gc-sm-12 ">
            <div class="passenger_details">
              <h5>Extra Information</h5>
            </div>
            <div class="transfer-accommodation-other transfer-extra-other">
              <div class="gc-md-3 gc-sm-4 align_left">
                <div class="form-group">
                  <label for="SendByEmail">Send By Email</label>
                  <select class="form-control" name="client_send_by_email" id="" style="height:40px;">
                    <option value="0">As per account set up</option>
                    <option value="1">Send e-ticket to the email which is provided in Booking Confirm Request along with account set up email.</option>
                  </select>
                </div>
              </div>

              <div class="gc-md-3 gc-sm-4 align_left">
                <div class="form-group">
                  <label for="OwnReferenceNo">Reference no(AgentResID-123)</label>
                  <input type="text" name="client_refeerence" class="form-control" id="" placeholder="Enter your reference no" style="height:40px;" />
                </div>
              </div>

              <div class="gc-md-3 gc-sm-4 align_left extra-information-remark">
                <div class="form-group">
                  <label for="Remark">Remark</label>
                  <textarea class="form-control" id="" name="client_remark" rows="2"></textarea>
                </div>
              </div>
            </div>


          </div>

          <div class="gc-md-12 gc-sm-12 transfer-to-pay">
            <?php if ($transfer_holiday_price) : ?>
              <div class="gc-sm-6 gc-md-6 transfer-booking-coupon">
                <h4>Gift Card Code</h4>
                <label class="" id="coupon_code_smg"></label>
                <div class="transfer-coupon-input">
                  <div class="gc-form-group">
                    <input type="text" name="coupon_code_check" placeholder="Enter giftcard4travel gift card code." class="coupon_code_value" id="coupon_code_value" />
                  </div>
                  <div class="gc-form-group">
                    <input type="button" name="coupon_code_check_ability" class="" onclick="transfer_couponCode()" value="Apply" />
                    <input type="hidden" name="gctcf_amount" value="<?php echo $transfer_holiday_price; ?>">
                    <input type="hidden" name="transfer_gctcf_coupon_amount" value="0">
                    <input type="hidden" name="transfer_gctcf_coupon_code" value="">
                  </div>
                </div>
              </div>
            <?php endif; ?>
            <div class="gc-sm-4">
              <!-- <div class="transfer_price" style="padding-top: 30px;">
  <h2 style="margin:0;">Total Price : <span style="color:#F00;"><?php echo '£' . $transfer_holiday_price; ?></span></h2>
  <span>(balance due)</span>
  </div> -->
              <div class="paypal_section">
                <table>
                  <tr>
                    <td>Transfer price :</td>
                    <td><?php echo '£' . $transfer_holiday_price; ?></td>
                  </tr>
                  <tr>
                    <td><span style="display:none;" id="coupon_price_hidden">Coupon price :</span></td>
                    <td><span style="display:none;" class="coupon_price_hidden">£<span id="byc_coupon_amount_total"></span></span></td>
                  </tr>
                  <tr>
                    <td colspan="2">
                      <div style="border: 0.4px solid #404040;"></div>
                    </td>
                  </tr>
                  <tr>
                    <td>Total Price :</td>
                    <td><span id="transfer_amount_total"><?php echo '£' . $transfer_holiday_price; ?></span><span>(balance due)</span></td>
                  </tr>
                </table>
              </div>
              <div class="transfer_button">
                <input type="submit" name="client_confirm_now" class="btn btn-primary btn-block" value="Buy now" style="width:70%;height:55px;background: #61c1e2;" />
              </div>
              <!-- <div class="payment_images" style="text-align:center;">
                <img src="<?php echo GCTCF_URL; ?>public/images/paypal_payment.png" />
              </div> -->
            </div>




          </div>
        </form>
        

      </div>
    </div>
    <div style="padding-bottom:50px;"></div>
  <?php
  }
} else {
  ?>
  <div class="gc-container">
    <div class="gc-row">
      <div class="gc-md-12 gc-sm-12 " style="padding-top:50px; padding-bottom:50px;">
        <h5 style="text-align: center;"><?php echo $p2p_api_xml_response_for_cars_booking
                                          ->TransferOnly
                                          ->errors
                                          ->error->text; ?> </h5>
      </div>
    </div>
  </div>
<?php

}
?>