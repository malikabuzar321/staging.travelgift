<?php


$attraction_settings = get_option('attraction_api_option');
$attraction_api_url = rtrim($attraction_settings['attraction_api_url'], "/");
$attraction_api_user = $attraction_settings['attraction_api_username'];
$attraction_api_pass = $attraction_settings['attraction_api_password'];

if (isset($_POST['book-now'])) {

  $dateid = isset($_POST['dateid']) ? $_POST['dateid'] : '';
  $book_now_data = $_POST;
  $date1 = isset($_POST['select_date']) ? $_POST['select_date'] : '';
  $date_create = date_create($date1);
  $date = date_format($date_create, "Y-m-d");
  $order = array();
  if ($dateid == '') {
    $apiUrl = $attraction_api_url . '/products/' . $_POST['product_id'] . '?date_from=' . $_POST['date_from'] . '&date_to=' . $_POST['date_to'];
    $response = wp_remote_get($apiUrl, array(
      'timeout' => 120,
      'headers' => array(
        'Authorization' => 'Basic ' . base64_encode($attraction_api_user . ':' . $attraction_api_pass),
      ),
    ));
    $responseBody = wp_remote_retrieve_body($response);
    $result = json_decode($responseBody, true);

    foreach ($result['tickets'] as $value) {

      foreach ($value['availability'] as $value1) {

        if (isset($_POST['select_time'])) {

          if (($value1['date'] == $date) && ($value1['time'] == $_POST['select_time'])) {
            $dateid = $value1['date_id'];
          }
        } else {

          if ($value1['date'] == $date) {
            $dateid = $value1['date_id'];
          }
        }
      }
    }
  }
  $date = Gctcf_Public::gctcf_displayDates(date('Y-m-d'), $_POST['select_date']);
  $avilableDeparture = json_encode($date);

  foreach ($_POST['tickets'] as $ticket) {
    for ($i = 0; $i < $ticket['ticket-qty']; $i++) {
      $order[] = array(
        'date_id'     => $dateid,
        'qty'         => $ticket['ticket-qty'],
        'ticket_id'   => $ticket['ticket_id'],
      );
    }
  }
}



if (isset($_POST['confirm_booking'])) {

  $order_ticketArr = array();
  $pasangers = $_POST['passenger_data'];
  $ticketdata = json_decode(stripslashes($_POST['tickets-data']), true);
  $attributes_value = array();


  if ($ticketdata) {

    $select_date = date_create($ticketdata['select_date']);
    $date_value = date_format($select_date, "Y-m-d");
    $time_value = isset($ticketdata['select_time']) ? $ticketdata['select_time'] : "12:00";


    if ($ticketdata['tickets']) {

      foreach ($ticketdata['tickets'] as $key => $value) {

        if ($value['ticket-qty'] > 0) {

          $ticket_id = $key;
          $passanger_name = $pasangers[$key];
          $pass_count = count($passanger_name);



          if (array_key_exists($key, $pasangers)) {

            if ($value['attributes']) {

              $attr_id = "";
              $date_attr_id = "";
              $veg_attr_id = "";
              $time_attr_id = "";

              foreach ($value['attributes_name'] as $key => $attname) {

                $val = strtolower($attname);
                $name = str_replace(" ", "_", $val);

                if ($attname == "Date") {
                  $date_attr_id = $key;
                  $attributes_value[$ticket_id][$key][] = $date_value;
                }
                if ($attname == "Number of vegetarians") {
                  $veg_attr_id = $key;
                  $attributes_value[$ticket_id][$key][] = 'none';
                }
                if ($attname == "Time") {
                  $time_attr_id = $key;
                  $attributes_value[$ticket_id][$key][] = $time_value;
                }
                if (isset($_POST['passenger_data']) && $_POST['passenger_data']) {
                  foreach ($passanger_name as $pass_att => $data) {
                    if ($name == $pass_att) {
                      $attributes_value[$ticket_id][$key] = $data;
                    }
                  }
                }
              }




              foreach ($value['attributes'] as $key => $attvalue) {

                foreach ($attributes_value as $tic_id => $ticvalue) {
                  foreach ($ticvalue as $att_id => $attrvalue) {
                    if ($attvalue['attribute_id'] == $att_id) {
                      $ticketdata['tickets'][$tic_id]['attributes'][$key]['values'] = $attrvalue;
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  }

  // pre($attributes_value);
  // pre($ticketdata);


  $ticket_count = count($ticketdata['tickets']);
  foreach ($ticketdata['tickets'] as $ticket) {
    if ($ticket['ticket-qty'] > 0) {
      $order_ticketArr[] = array(
        'date_id'     => isset($_POST['date_id']) ? $_POST['date_id'] : '',
        'qty'         => $ticket['ticket-qty'],
        'ticket_id'   => $ticket['ticket_id'],
        'attributes'  => $ticket['attributes'],
      );
    }
  }
  $bodydata = array(
    "departure_date"  => isset($_POST['depature_date']) ? $_POST['depature_date'] : '',
    "email"           => isset($_POST['order_email']) ? $_POST['order_email'] : '',
    "agree_privacy"   => isset($_POST['privacy_policy']) ? $_POST['privacy_policy'] : '',
    "agree_terms"     => isset($_POST['terms_condition']) ? $_POST['terms_condition'] : '',
    "source"          => 'Phone',
    'order_tickets'   => $order_ticketArr,
  );

  $args = array(
    'post_type'     => 'attraction_booking',
    'post_title'    => isset($_POST['full_name']) ? $_POST['full_name'] : '',
    'post_status'   => 'publish'
  );

  $post_id = wp_insert_post($args);
  if (!is_wp_error($post_id)) {

    update_post_meta($post_id, '_gctcf_confiramtion_email', 0);

    if (!empty($bodydata)) {
      update_post_meta($post_id, '_gctcf_api_data', $bodydata);
    }

    if (isset($_POST['passenger_data']) && $_POST['passenger_data']) {
      update_post_meta($post_id, '_gctcf_passenger_name', $_POST['passenger_data']);
    }

    if (isset($_POST['date_id']) && $_POST['date_id']) {
      update_post_meta($post_id, '_gctcf_date_id', $_POST['date_id']);
    }
    if (isset($_POST['order_ticket']) && $_POST['order_ticket']) {
      update_post_meta($post_id, '_gctcf_order_data', $_POST['order_ticket']);
    }
    if (isset($_POST['tickets-data']) && $_POST['tickets-data']) {
      update_post_meta($post_id, '_gctcf_ticket_data', $_POST['tickets-data']);
    }
    if (isset($_POST['order_email']) && $_POST['order_email']) {
      update_post_meta($post_id, '_gctcf_order_email', $_POST['order_email']);
    }
    if (isset($_POST['terms']) && $_POST['terms']) {
      update_post_meta($post_id, '_gctcf_terms', $_POST['terms']);
    }
    if (isset($_POST['privacy']) && $_POST['privacy']) {
      update_post_meta($post_id, '_gctcf_privacy', $_POST['privacy']);
    }


    $billing_country = isset($_POST['country']) ? $_POST['country'] : '';
    $billing_fullname = isset($_POST['full_name']) ? $_POST['full_name'] : '';
    $billing_address1 = isset($_POST['address1']) ? $_POST['address1'] : '';
    $billing_address2 = isset($_POST['address_2']) ? $_POST['address_2'] : '';
    $billing_city = isset($_POST['city_address']) ? $_POST['city_address'] : '';
    $billing_state = isset($_POST['state_address']) ? $_POST['state_address'] : '';
    $billing_pincode = isset($_POST['pin_code']) ? $_POST['pin_code'] : '';
    $billing_contact = isset($_POST['phone_number1']) ? $_POST['phone_number1'] : '';
    $billing_phoneNumber = isset($_POST['phone_number2']) ? $_POST['phone_number2'] : '';
    $billing = array(
      'billing_country'       =>  $billing_country,
      'billing_fullname'      =>  $billing_fullname,
      'billing_address1'      =>  $billing_address1,
      'billing_address2'      =>  $billing_address2,
      'billing_city'          =>  $billing_city,
      'billing_state'         =>  $billing_state,
      'billing_pincode'       =>  $billing_pincode,
      'billing_contact'       =>  $billing_contact,
      'billing_phoneNumber'   =>  $billing_phoneNumber,
    );
    if (!empty($billing)) {
      update_post_meta($post_id, '_gctcf_billing_info', $billing);
    }
    $lead_pass_name = isset($_POST['lead_pass_name']) ? $_POST['lead_pass_name'] : '';
    $depature_date = isset($_POST['depature_date']) ? $_POST['depature_date'] : '';

    $travel_info = array(
      'lead_passenger_name'   => $lead_pass_name,
      'depature_date'         => $depature_date
    );

    if (!empty($travel_info)) {
      update_post_meta($post_id, '_gctcf_travel_info', $travel_info);
    }

    update_post_meta($post_id, '_gctcf_payment_status', 'pending');
  }
}


?>

<?php if (isset($_POST['book-now'])) { ?>
  <form class="custom-form-step" method="post" id="attraction-booking">
    <div class="ticket-box">
      <h3>Ticket details</h3>
      <p><?php echo isset($_POST['product_name']) ? $_POST['product_name'] : ''; ?>, <?php echo isset($_POST['select_date']) ? $_POST['select_date'] : ''; ?>, <?php echo isset($_POST['select_time']) ? $_POST['select_time'] : ''; ?></p>
      <?php if (!empty($_POST['tickets'])) { ?>
        <div class="gctcf-pasenger-box">
          <?php
          $ticket_total = 0;
          foreach ($_POST['tickets'] as $ticket) {
            for ($i = 0; $i < $ticket['ticket-qty']; $i++) {
              $ticket_total += $ticket['ticket-qty']; ?>
              <!-- <div class="form-group-6">
                <label for="passenger_name"></ /?php echo $ticket['type']; ?> <span>*</span></label>
                <input type="text" class="passenger_data" name="passenger_data[<?php echo $ticket['ticket_id']; ?>][passenger_name][]" data-attr="Passenger name" placeholder="Passenger name" required="required">
              </div> -->
              <?php foreach ($ticket['attributes_name'] as $att_name) {
                // echo $att_name;
                if (($att_name != "Date") && ($att_name != "Time") && ($att_name != "Number of vegetarians")) {
                  $val = strtolower($att_name);
                  $name = str_replace(" ", "_", $val);
              ?>
                  <div class="form-group-6">
                    <label for="passenger_name"><?php echo $ticket['type']; ?> <span>*</span></label>
                    <input type="text" class="passenger_data" name="passenger_data[<?php echo $ticket['ticket_id']; ?>][<?php echo $name; ?>][]" data-attr="<?php echo $att_name ?>" placeholder="<?php echo $att_name; ?>" required="required">
                  </div>


          <?php }
              }
            }
          }
          ?>
        </div>
      <?php } ?>
    </div>

    <div class="ticket-box">
      <h3>Checkout</h3>
      <h5>EMAIL</h5>
      <div class="form-group-12">
        <label for="order_email">Order Email <span>*</span></label>
        <input type="email" id="order_email" name="order_email">
      </div>

      <h5>TRAVEL INFORMATION</h5>
      <div class="form-row">
        <div class="form-group form-group-6">
          <label for="lead_pass_name">Lead Passenger Name <span>*</span></label>
          <input type="text" id="lead_pass_name" name="lead_pass_name">
        </div>
        <div class="form-group form-group-6 tour-frm-mbl-use">
          <label for="datepicker">Departure date <span>*</span></label>
          <input type="hidden" id="avilableDeparture" data-avilable='<?php echo $avilableDeparture ?>' autocomplete="off">
          <input type="text" name="depature_date" id="datepickerBooking" autocomplete="off">
        </div>
      </div>

      <h5>BILLING INFORMATION</h5>
      <div class="form-row gift-billing-info">
        <div class="form-group">
          <label for="">Country <span>*</span></label>
          <select name="country" id="">
            <option value="AD">Andorra</option>
            <option value="AU">Australia</option>
            <option value="AT">Austria</option>
            <option value="BE">Belgium</option>
            <option value="BA">Bosnia and Herzegovina</option>
            <option value="BR">Brazil</option>
            <option value="CA">Canada</option>
            <option value="HR">Croatia</option>
            <option value="CY">Cyprus</option>
            <option value="CZ">Czech Republic</option>
            <option value="DK">Denmark</option>
            <option value="FK">Falkland Islands</option>
            <option value="FI">Finland</option>
            <option value="FR">France</option>
            <option value="DE">Germany</option>
            <option value="GI">Gibraltar</option>
            <option value="GR">Greece</option>
            <option value="HU">Hungary</option>
            <option value="IS">Iceland</option>
            <option value="IN">India</option>
            <option value="IE">Ireland</option>
            <option value="IM">Isle of Man</option>
            <option value="IL">Israel</option>
            <option value="IT">Italy</option>
            <option value="LI">Liechtenstein</option>
            <option value="LU">Luxembourg</option>
            <option value="MT">Malta</option>
            <option value="NL">Netherlands</option>
            <option value="NO">Norway</option>
            <option value="PA">Panama</option>
            <option value="PL">Poland</option>
            <option value="PT">Portugal</option>
            <option value="RU">Russia</option>
            <option value="SI">Slovenia</option>
            <option value="ZA">South Africa</option>
            <option value="ES">Spain</option>
            <option value="SE">Sweden</option>
            <option value="CH">Switzerland</option>
            <option value="TR">Turkey</option>
            <option value="AE">United Arab Emirates</option>
            <option value="GB" selected="selected">United Kingdom</option>
            <option value="US">United States</option>
          </select>
        </div>

        <div class="form-group">
          <label for="full_name">Full name <span>*</span></label>
          <input type="text" name="full_name" id="full_name">
        </div>

        <div class="form-group">
          <label for="address1">Address 1 <span>*</span></label>
          <input type="text" name="address1" id="address1">
        </div>

        <div class="form-group">
          <label for="address_2">Address 2 </label>
          <input type="text" name="address_2" id="address_2">
        </div>

        <div class="form-group">
          <label for="city_address">City <span>*</span></label>
          <input type="text" name="city_address" id="city_address">
        </div>


        <div class="form-group">
          <label for="state_address">State</label>
          <input type="text" name="state_address" id="state_address">
        </div>

        <div class="form-group">
          <label for="pin_code">PIN code <span>*</span></label>
          <input type="number" name="pin_code" id="pin_code">
        </div>

        <div class="form-group">
          <label for="phone_number1">Contact Phone</label>
          <input type="number" name="phone_number1" id="phone_number1">
        </div>

        <div class="form-group">
          <label for="phone_number2">Mobile Number</label>
          <input type="number" name="phone_number2" id="phone_number2">
        </div>
      </div>
<!--
      <h5>PAYMENT</h5>
      <div class="gift-payment-info">
        <p class="payment-content">If you're not an agent and are looking for tickets you can find the same great range on our sister site <a href="#" target="_blank">AttractionTickets.com</a></p>
        <p class="payment-content">If you are an agent please <a href="#" target="_blank">login</a> to place an order.</p>
      </div>
-->

      <h5>LEGAL</h5>
      <div class="form-row">
        <div class="form-group-check">
          <input type="checkbox" id="formTerms" name="terms_condition" value="1" required>
          <label for="formTerms">Terms & Conditions <span>*</span></label>
          <p>Please confirm that you have understood and agree to our <a href="#" target="_blank">Terms & Conditions</a></p>
          <p class="gctcf-error"></p>
        </div>

        <div class="form-group-check gc-tour-group-check-mbl-only">
          <input type="checkbox" id="formPrivacy" name="privacy_policy" value="1" required>
          <label for="formPrivacy">Privacy Policy <span>*</span></label>
          <p>Please confirm that you have understood and agree to our <a href="#" target="_blank">Privacy Policy</a></p>
          <p class="gctcf-error"></p>
        </div>
      </div>
    </div>

    <input type="hidden" name="ticket_qty" value='<?php echo $ticket_total; ?>'>
    <input type="hidden" name="post_id" value='<?php echo $post_id; ?>'>
    <input type="hidden" name="date_id" value='<?php echo $dateid; ?>'>
    <input type="hidden" name="order_ticket" value='<?php echo json_encode($order); ?>'>
    <input type="hidden" name="tickets-data" value='<?php echo json_encode($book_now_data); ?>'>


    <button type="submit" class="button confirm-booking" name="confirm_booking">Confirm Booking</button>
  </form>
<?php } ?>
<?php if (isset($_POST['confirm_booking'])) {

  // echo '<pre>';
  // print_r($_POST);
  // echo '</pre>';


  $paypal_unick_code = mt_rand(10000, 99999) . 'BYC' . time();
  if (!empty($paypal_unick_code)) {
    update_post_meta($post_id, '_gctcf_paypal_unick_code', $paypal_unick_code);
  }
  $total_price = $ticketdata['total-price'];
  $product_name = $ticketdata['product_name'];
  $ticket_qty = $_POST['ticket_qty'];
  $booking_email = isset($_POST['order_email']) ? $_POST['order_email'] : '';
?>
  <div class="gc-container">
    <!-- <div class="gc-row attraction-booking-row"> -->
      <div class="gc-sm-12 gc-md-12 attraction_prepare_list attraction_prepare_booking-row">
      <!-- <div class="gc-md-12 gc-sm-12 gc-xs-12 attraction_prepare_list"> -->
        <div class="gc-md-6 gc-sm-6 gc-xs-12 prepare_booking-left">
          <div class="attraction_prepare_section">
            <h5>Booking prepare </h5>
            <div class="attraction-title">
              <h3><?php echo $product_name; ?></h3>
              <span>£<?php echo $total_price; ?></span>
            </div>
            <div class="attraction-booking-date">
              <h6>Booking date : </h6>
              <span><?php echo  !empty($ticketdata['select_time']) ? $ticketdata['select_date'] . ', ' . $ticketdata['select_time'] : $ticketdata['select_date']; ?></span>
            </div>
            <div class="booking-tickets">
              <h6>Tickets</h6>
              <ul>
                <?php foreach ($ticketdata['tickets'] as $key => $value) :
                  if ($value['ticket-qty'] > 0) { ?>
                    <li><?php echo $value['ticket-qty']; ?> x <?php echo $value['type']; ?></li>
                <?php }
                endforeach ?>

              </ul>
            </div>
            <div class="attraction-booking-form-data1">
              <div class="booking-lead-paasengers-name">
                <h6>Lead passenger name</h6>
                <span><?php echo $lead_pass_name; ?></span>
              </div>
              <div class="booking-paasengers-name">
                <h6>Passenger's Name</h6>
                <?php foreach ($_POST['passenger_data'] as $value) : ?>
                  <?php foreach ($value as $key => $pass_name) : ?>
                    <?php foreach ($pass_name as $name) {
                      if ($key == "passenger_name") { ?>
                        <span><?php echo  $name; ?></span>
                    <?php }
                    } ?>
                  <?php endforeach ?>
                <?php endforeach ?>
              </div>
              <div class="booking-departure-date">
                <h6>Departure date</h6>
                <span><?php echo $depature_date; ?></span>
              </div>
              <div class="booking-departure-date">
                <h6>Billing Address</h6>
                <span><?php echo $billing_fullname; ?></span>
                <span><?php echo $billing_phoneNumber; ?></span>
                <span><?php echo $billing_address1; ?>, <?php echo $billing_address2; ?></span>
                <span><?php echo $billing_city; ?>, <?php echo $billing_state; ?>, <?php echo $billing_country; ?></span>
                <span><?php echo $billing_pincode; ?></span>
              </div>
            </div>
          </div>
        </div>
        <div class="gc-md-6 gc-sm-6 gc-xs-12 prepare_booking-right">
          <div id="payment-iframe"></div>
        </div>
      </div>
      <div class="gc-sm-12 gc-md-12 attraction_prepare_list attraction_prepare_booking-row">
        <div class="gc-sm-6 gc-md-6 prepare_booking-left">
          <h4>Gift Card Code</h4>
          <label class="" id="coupon_code_smg"></label>
          <div class="gc-form-group">
            <input type="hidden" name="coupon_code_quote_id" value="<?php echo $post_id; ?>" id="coupon_code_quote_id">
            <input type="text" name="coupon_code_check" placeholder="Enter giftcard4travel gift card Code." class="coupon_code_value" id="coupon_code_value" />
          </div>
          <div class="gc-form-group">
            <input type="submit" name="coupon_code_check_ability" class="" onclick="attractioncouponCode()" value="Apply" />
            <input type="hidden" name="gctcf_amount" value="<?php echo $total_price; ?>">
            <input type="hidden" name="gctcf_coupon_amount" class="gctcf_coupon_amount" value="0">
            <input type="hidden" name="gctcf_coupon_code" class="gctcf_coupon_code" value="">
          </div>
        </div>
        <div class="gc-sm-6 gc-md-6 prepare_booking-right" style="text-align:center;">
          <div class="paypal_section">
            <table>
              <tr>
                <td>Attraction price :</td>
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
                <td><span id="attraction_amount_total"><?php echo '£' . $total_price; ?></span></td>
              </tr>
            </table>
            <div class="gc-md-12 gc-sm-12 poll-right">
              <form action="<?php //echo 'https://www.paypal.com/cgi-bin/webscr';?>" method="post">
                <input type="hidden" name="cmd" value="_xclick">
                <input type="hidden" name="business" value="rob@travelgift.uk">
                <input type="hidden" name="item_name" value="<?php echo $product_name ?>">
                <input type="hidden" name="item_number" value="<?php echo $paypal_unick_code; ?>">
                <input type="hidden" name="amount" class="byc_attraction_amount" value="<?php echo $total_price; ?>">
                <input type="hidden" name="discount_amount" class="byc_coupon_amount" value="0">
                <input type="hidden" name="custom" value="<?php echo $post_id; ?>">
                <input type="hidden" name="quantity" value="1">
                <input type="hidden" name="return" value="<?php echo home_url() ?>/thank-you">
                <input type="hidden" name="notify_url" value="<?php echo home_url() ?>/payment_ipn_file.php">
                <input type="hidden" name="currency_code" value="GBP">

                <input type="hidden" name="full_name" value="<?php echo $billing_fullname; ?>">
                <input type="hidden" name="address1" value="<?php echo $billing_address1; ?>">
                <input type="hidden" name="address2" value="<?php echo $billing_address2; ?>">
                <input type="hidden" name="city" value="<?php echo $billing_city; ?>">
                <input type="hidden" name="state" value="<?php echo $billing_state; ?>">
                <input type="hidden" name="zip" value="<?php echo $billing_pincode; ?>">
                <input type="hidden" name="country" value="<?php echo $billing_country; ?>">

                <input type="hidden" name="email" value="<?php echo $booking_email; ?>">
                <input type="hidden" name="night_phone_a" value="<?php echo $billing_phoneNumber; ?>">
                <?php //echo '<input type="image" name="submit" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynow_LG.gif" alt="PayPal - The safer, easier way to pay online">';
                ?>
              </form>
            </div>
            <?php //echo '<div class="gc-md-12 gc-sm-12"><img src="'.GCTCF_URL.'public/images/paypal_payment.png" /></div>';?>
            <script src="https://sdk.felloh.com/"></script>
          <script type="text/javascript">
              var post_id = "<?=$post_id?>";
              <?php $mode = get_option('options__felloh_api_mode'); if($mode == 'sandbox'): $public_key = get_option('options__felloh_sandbox_public_key');?>
              var SDK = new FellohPayments('payment-iframe', '<?=$public_key?>',{"sandbox":true});
              <?php else: $public_key = get_option('options__felloh_live_public_key');?>
              var SDK = new FellohPayments('payment-iframe', '<?=$public_key?>');
              <?php endif; ?>
              jQuery.ajax({
                url: '<?=get_site_url()?>/wp-admin/admin-ajax.php?action=felloh_create_ecom',
                method: 'POST',
                dataType: 'json',
                data: {"action": "felloh_create_ecom","form_data":jQuery("form").serialize()},
                success: function(response) {
                  if(response.data.length != 0){
                    SDK.render(response.data.id);
                  } else {
                    alert("Could not render payment form. Please try again at some time later.");
                    console.table(response);
                  }
                  // Handle successful response
                  // console.log('Data:', response);
                }
              })
              // SDK.onRender(function(){
              //   $("body iframe").css('border','none');
              // });
              SDK.onSuccess(function(data){
                console.table(data);
                jQuery.ajax({
                  url: "<?=get_site_url()?>/wp-admin/admin-ajax.php?action=attraction_payment",
                  method: "POST",
                  dataType: 'json',
                  data: {'action':'attraction_payment','post_id':post_id,'txn_id':data.transaction.id},
                  success: function(response){
                    if(response){
                      location.href="<?=home_url()?>/thank-you"
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
        </div>
      </div>
    </div>
  </div>

<?php } ?>