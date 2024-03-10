<?php ob_start(); ?>
<div class="container">
    <div class="row">
        <?php

        $discount_coupon_code = '';
        $discount_coupon_amount = '';
        $email_content = '';
        $txn_id = $_REQUEST['tx'];
        $test_mode = ($txn_id == 'TEST1234') ? 'true' : 'false';
        $item_number = $_REQUEST['item_number'];
        $booking = get_posts(array('post_type' => 'gc4t_hotel_booking', 'post_status' => 'private', 'posts_per_page' => 1, 'meta_query' => array(array('key' => 'gc4t_feed_type', 'value' => 'travellanda',), array('key' => 'gc4t_paypal_code', 'value' => $item_number,), array('key' => 'gc4t_transaction_id', 'value' => $txn_id,))));



        if ($booking && !empty($booking)) {
            $booking = $booking[0];
            $paypal_unick_code = get_post_meta($booking->ID, 'gc4t_paypal_code', true);
            $discount_coupon_code = get_post_meta($booking->ID, 'gc4t_discount_code', true);
            $discount_coupon_amount = get_post_meta($booking->ID, 'gc4t_discount_amount', true);
            $total_price = get_post_meta($booking->ID, 'gc4t_total_price', true);

            if (!empty($paypal_unick_code)) {
                if ($item_number == $paypal_unick_code) {
                    $payment_status = get_post_meta($booking->ID, 'gc4t_status', true);
                    // pre($payment_status);

                    if ($payment_status == 'completed' || $payment_status == 'pending') {
                        $booking_details = json_decode(get_post_meta($booking->ID, 'gc4t_booking_details', true), true);
                        // pre($booking_details);

                        if ($booking_details) {
                            update_post_meta($booking->ID, 'gc4t_booking_id', $booking_details['hotel_id']);
                            update_post_meta($booking->ID, '_gctcf_booking_status', 1 );
                            require_once GCTCF_PATH . '/includes/Travellanda.class.php';
                            $travellanda_settings = get_travellanda_api_config();

                            $travellanda = new Travellanda();
                            $travellanda->setUsername($travellanda_settings['user']);
                            $travellanda->setPassword($travellanda_settings['pass']);
                            $travellanda->setMode($travellanda_settings['mode']);
                            $reference_number = get_post_meta($booking->ID, 'gc4t_booking_number', true);
                            $hotel_booking = array('option_id' => $booking_details['option_id'], 'reference' => 'GC4T - Booking - ' . $reference_number, 'rooms' => array());
                            foreach ($booking_details['rooms'] as $room_index => $room_details) {
                                $room = array('room_id' => $room_details['room_code'], 'adults' => $room_details['adults'], 'children' => $room_details['children'],);
                                $hotel_booking['rooms'][] = $room;
                            }
                            $booking_result = $travellanda->hotelBooking($hotel_booking);
                            $booking_result = $booking_result['body'];
                            $booking_result = json_decode($travellanda->convertToJson($booking_result), true);
                            file_put_contents(dirname(__FILE__) . '/travellanda_' . $booking->ID . '.txt', print_r($booking_result, true), FILE_APPEND);
                            //echo '<pre>' . print_r($booking_result, true) . '</pre>';
                            //pre($booking_result);
                            //if (isset($booking_result['Body']['HotelBooking'])) { 
        ?>
                            <div style="padding-top:10px">
                                <div class="col-md-12 col-sm-12 col-sx-12">
                                    <h2>Confirm Ticket</h2>
                                    <?php

                                    foreach ($booking_details['rooms'] as $room_index => $room_details) {

                                    ?>
                                        <div class="gc-hotel-booking-confirm-content-inner">
                                            <h5>Room <?php echo $room_index + 1; ?></h5>
                                            <div class="client_confirm_section">
                                                <h3><?php echo $booking_details['hotel_name']; ?></h3>
                                                <ul>
                                                    <li><?php echo '<label>Hotel Booking Id   :</label><strong>' . $booking_details['hotel_id'] . '</strong>'; ?></li>
                                                    <li><?php echo '<label>Hotel Id  :</label><strong>' . $booking_details['option_id'] . '</strong>'; ?></li>
                                                    <li><?php echo '<label>Creation Date  :</label><strong>' . mysql2date('Y-m-d', $booking->post_date) . '</strong>'; ?></li>
                                                    <li><?php echo '<label>Arrival Date  :</label><strong>' . $booking_details['check_in_date'] . '</strong>'; ?></li>
                                                    <li><?php echo '<label>Nights  :</label><strong>' . $booking_details['nights'] . '</strong>'; ?></li>
                                                    <li><?php echo '<label>Price  :</label><strong>' . $total_price . '</strong>'; ?></li>
                                                    <li><?php echo '<label>Room Code :</label><strong>' . $room_details['room_code'] . '</strong>'; ?></li>
                                                    <li><?php echo '<label>Room Name  :</label><strong>' . $room_details['room_name'] . '</strong>'; ?></li>
                                                    <li><?php echo '<label>Meal Type   :</label><strong>' . $room_details['room_meal'] . '</strong>'; ?></li>
                                                </ul>
                                            </div>
                                            <h6>Adults</h6>
                                            <ul>
                                                <?php foreach ($room_details['adults'] as $adult) { ?>
                                                    <li class="hotel_preper_adult"><?php echo $adult['title'] . ' ' . $adult['first_name'] . '  ' . $adult['last_name']; ?></li>
                                                <?php
                                                } ?>
                                            </ul>
                                            <?php if ($room_details['children']) : ?>
                                                <h6>Children</h6>
                                                <ul>
                                                    <?php foreach ($room_details['children'] as $child) { ?>
                                                        <li class="hotel_preper_child"><?php echo $child['first_name'] . ' ' . $child['last_name']; ?></li>
                                                    <?php
                                                    } ?>
                                                </ul>
                                            <?php endif; ?>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                    <div class="gc-hotel-booking-confirm-content-inner booking-details">
                                        <h5>Details</h5>
                                        <ul>
                                            <li>Booking Reference: <?php echo $booking_result['Body']['HotelBooking']['BookingReference']; ?></li>
                                            <li>Your Reference: <?php echo $booking_result['Body']['HotelBooking']['YourReference']; ?></li>
                                            <li>Booking Status: <?php echo $booking_result['Body']['HotelBooking']['BookingStatus']; ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <?php
                            $output = ob_get_contents();
                            ob_end_clean();
                            echo $output;
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
                        <td colspan="3">
                            <h2 style="font-size: 18px; background-color: #fad38f; padding: 10px 15px; text-align: center; margin: 0px;font-family: sans-serif; color: #061d2f; font-weight:600;">Confirm Ticket</h2>
                        </td>
                    </tr>';
                            foreach ($booking_details['rooms'] as $room_index => $room_details) {
                                $email_content .= '
                        <tr>
                            <td colspan="3">
                                <table width="100%" style="max-width: 1200px; margin: 0 auto; padding: 15px; font-family:Arial, Helvetica, sans-serif; border:1px solid #061d2f; color:#061d2f" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td align="left" colspan="3"style="font-size: 20px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;">
                                            <strong>Room ' . ($room_index + 1) . '</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center" colspan="3"style="font-size: 20px;font-family:Arial, Helvetica, sans-serif; text-align: center; padding:5px; padding-top: 20px;">
                                            <strong><u>' . $booking_details['hotel_name'] . '</u></strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;">
                                            <strong>Hotel Booking Id :</strong>' . $booking_details['hotel_id'] . '
                                        </td>
                                        <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;">
                                            <strong>Arrival Date :</strong> ' . $booking_details['check_in_date'] . '
                                        </td>
                                        <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;">
                                            <strong>Room Code :</strong> ' . $room_details['room_code'] . '
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;">
                                            <strong>Hotel Id :</strong> ' . $booking_details['option_id'] . '
                                        </td>
                                        <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;">
                                            <strong>Nights :</strong> ' . $booking_details['nights'] . '
                                        </td>
                                        <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;">
                                            <strong>Room Name :</strong> ' . $room_details['room_name'] . '
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;">
                                            <strong>Creation Date :</strong> ' . mysql2date('Y-m-d', $booking->post_date) . '
                                        </td>
                                        <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;">
                                            <strong>Price :</strong> ' . $total_price . '
                                        </td>
                                        <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;">
                                            <strong>Meal Type :</strong> ' . $room_details['room_meal'] . '
                                        </td>
                                    </tr>';
                                $email_content .= '<tr>
                                        <td align="left" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;">
                                            <strong>Adults</strong>
                                        </td>';
                                if ($room_details['children']) {
                                    $email_content .= '<td align="left" colspan="2" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;">
                                            <strong>Children</strong>
                                        </td>';
                                }
                                $email_content .= '</tr>';
                                $email_content .= '<tr>';
                                $email_content .= '<td align="left" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 0;">';
                                foreach ($room_details['adults'] as $adult) {
                                    $email_content .= '<span style="display:block; width:100%;">' . $adult['title'] . ' ' . $adult['first_name'] . '  ' . $adult['last_name'] . '</span>';
                                }
                                $email_content .= '</td>';
                                if ($room_details['children']) {
                                    $email_content .= '<td align="left" colspan="2"style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 0;">';
                                    foreach ($room_details['children'] as $child) {
                                        $email_content .= '<span style="display:block; width:100%;">' . $child['first_name'] . ' ' . $child['last_name'] . '</span>';
                                    }
                                    $email_content .= '</td>';
                                }
                                $email_content .= '</tr><table><td></tr>';
                            }

                            $email_content .= '
                                            </table>
                                            </br>
                                            <table width="100%" style="max-width: 1200px; margin: 0 auto; padding: 15px; font-family:Arial, Helvetica, sans-serif; border:1px solid #061d2f; color:#061d2f" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td align="left" colspan="3"style="font-size: 20px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Details</strong></td>
                                            </tr>
                                            <tr>
                                                <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 5px;"><strong>Booking Reference : </strong> ' . $booking_result['Body']['HotelBooking']['BookingReference'] . '</td>
                                                <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 5px;"><strong>Your Reference :</strong> ' . $booking_result['Body']['HotelBooking']['YourReference'] . '</td>
                                                <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 5px;"><strong>Booking Status :</strong> ' . $booking_result['Body']['HotelBooking']['BookingStatus'] . '</td>
                                            </tr>
                                            </table>
                                            </br>';
                            $discount_coupen = get_post_meta($booking->ID, '_gctcf_coupon_code', true);
                            if (!empty($discount_coupen)) {
                                $discount_value_p = get_post_meta($booking->ID, '_gctcf_coupon_amount', true);
                                if ($total_price > $discount_value_p) {
                                    $booking_dis_total = $total_price - $discount_value_p;
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
                                                        <td align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;"><strong>£' . $total_price . '</strong></td>
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
                                                    <td align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;"><strong>£' . $total_price . '</strong></td>
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                    <td align="left" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px; border-top:2px solid #777;">Total Price:</td>
                                                    <td align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px; border-top:2px solid #777;"><strong>£' . $total_price . '</strong></td>
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
                            $hotel_booking = isset($_REQUEST['custom']) ? $_REQUEST['custom'] : '';
                            $hotel_book_id = str_replace("travellanda_", "", $hotel_booking);
                            $hotelPost = get_post($hotel_book_id);
                            $user_email = get_the_author_meta('user_email', $hotelPost->post_author);
                            $user_check_mail = get_post_meta($hotel_book_id, 'gc4t_send_user_email', true);
                            if (!empty($user_email)) {
                                $to = $user_email;
                                $group_emails = array('rob@giftcards4travel.co.uk', 'bookings@giftcards4travel.co.uk', 'developersuseonly@gmail.com', 'preeti@cmitexperts.com', 'developer@giftcards4travel.com');
                                $subject = 'Booking confirmation';
                                $headers = array('Content-Type: text/html; charset=UTF-8');
                                if (($user_check_mail == 0) && isset($_GET['payment_status']) && ($_GET['payment_status'] == 'Completed')) {
                                    update_post_meta($hotel_book_id, 'gc4t_send_user_email', 1);
                                    wp_mail($to, $subject, $email_content, $headers);
                                }
                            }

                            ?>
                            <script language=JavaScript src="https://portgk.com/create-sale?client=java&MerchantID=2189&SaleID=<?php echo 'travellanda_' . $reference_number; ?>&Purchases=Hotel,<?php echo $total_price; ?>"></script>
    <noscript><img src="https://portgk.com/create-sale?client=img&MerchantID=2189&SaleID=<?php echo 'travellanda_' . $reference_number; ?>&Purchases=Hotel,<?php echo $total_price; ?>" width="10" height="10" border="0"></noscript>
                            <?php if ($booking_result['Body']['HotelBooking']['BookingStatus'] != 'Confirmed') { ?>
                                <p class="gc-hotel-confirmation-message">Thank you for your payment. You will receive an email with your booking confirmation details as soon as your booking has been confirmed.</p>
                            <?php
                            }
                            //Update voucher details
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
                              } ?>
            <?php
                            //}
                        }
                    }
                }
            }
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
                <h3>please wait<br><span>WE ARE GETTING BOOKING INFO!...</span></h3>
                <h4>
                    <!--<a href="http://urwebdemoonline.com/pyratechsolutions/payment/" style="color:#fff;">Your payment is not completed yet. please click here for complete your payment</a></h4>-->
                    <a href="<?php echo home_url() ?>/<?php echo $_SERVER['REQUEST_URI']; ?>">check again</a>
                </h4>


            </div>
        <?php
        }
        ?>
    </div>
</div>