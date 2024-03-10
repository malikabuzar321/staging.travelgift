<?php
#$dbhost = 'db704811659.db.1and1.com';
#$dbuser = 'dbo704811659';
#$dbpass = 'Agfhj#fd2w';
#$dbhost = 'localhost';
#$dbuser = 'newoxy_gfuser';
#$dbpass = '!H6yve15@@#%$^^eg641';
$dbhost = 'localhost';
$dbuser = 'travelgift';
$dbpass = 'Z!0o877dp';
$conn = mysqli_connect($dbhost, $dbuser, $dbpass);
if (!$conn) {
    die('Could not connect: ' . mysqli_error($conn));

} else {
    echo 'Connected successfully<br />';
}
//die("here");
/*********************************************************************/
/*
$sqlinsert = "INSERT INTO `wp_payment_info`(`id`, `user_id`, `item_name`, `item_number`, `payment_status`, `mc_gross`, `mc_currency`, `txn_id`, `business`, `payer_email`, `current_payment_date`) VALUES (02,2552,'sony',258963,'no',254,'usd',58526,'busniess','dfrfg@jf.vpm','23/06/2011')";
if (mysqli_query($conn, $sqlinsert)) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}
mysqli_close($conn);*/
/************************************************************************/

// Check to see there are posted variables coming into the script
if ($_SERVER['REQUEST_METHOD'] != "POST")
{
    die("No Post Variables");
}
// Initialize the $req variable and add CMD key value pair
$req = 'cmd=_notify-validate';
// Read the post from PayPal
foreach ($_POST as $key => $value) {
    $value = urlencode(stripslashes($value));
    $req .= "&$key=$value";
}
// Now Post all of that back to PayPal's server using curl, and validate everything with PayPal
// We will use CURL instead of PHP for this for a more universally operable script (fsockopen has issues on some environments)


//live test URL....
$url = "https://www.paypal.com/cgi-bin/webscr";
//$url = "https://www.sandbox.paypal.com/cgi-bin/webscr";


$curl_result = $curl_err = '';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded", "Content-Length: " . strlen($req)));
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$curl_result = @curl_exec($ch);
$curl_err = curl_error($ch);
curl_close($ch);

$req = str_replace("&", "\n", $req);  // Make it a nice list in case we want to email it to ourselves for reporting
// Check that the result verifies
if (strpos($curl_result, "VERIFIED") !== false) {
    $req .= "\n\nPaypal Verified OK";
    //mail("johnston.jasond@gmail.com", "IPN interaction verified", "$req");
} else {
    $req .= "\n\nData NOT verified from Paypal!";

    //mail("johnston.jasond@gmail.com", "IPN interaction not verified", "$req");
    //exit();
}

$item_name = $_REQUEST['item_name'];

if ($item_name == 'LateRoomHotel') {

    $item_name = $_REQUEST['item_name'];
    $booking_unick_code = $_REQUEST['item_number'];
    $lateroom_hotel_id = $_REQUEST['custom'];
    $payment_status = $_REQUEST['payment_status'];
    $payment_amount = $_REQUEST['mc_gross'];
    $payment_currency = $_REQUEST['mc_currency'];
    $txn_id = $_REQUEST['txn_id'];
    $receiver_email = $_REQUEST['business'];
    $payer_email = $_REQUEST['payer_email'];
    $current_payment_date = date('Y/m/d H:i:s');
    $coupon_discount_amount = $_REQUEST['discount'];
    $coupon_code = $_REQUEST['quantity'];

    $conn = mysqli_connect($dbhost, $dbuser, $dbpass);

    $sql = 'newoxy_gfwp';

    $db_select = mysqli_select_db($conn, $sql);

    if (!$db_select) {
        die("Database selection failed: " . mysqli_error($conn));
    } else {
        //echo "Database created successfully\n";
    }
    $lateroom_ipn_sql = "INSERT INTO `payment_info_by_lateroom`(`id`, `item_name`, `booking_unick_code`, `lateroom_hotel_id`, `payment_status`, `payment_amount`, `payment_currency`, `txn_id`, `receiver_email`, `payer_email`, `current_payment_date`, `coupon_discount_amount`,`coupon_code`) VALUES ('','" . $item_name . "','" . $booking_unick_code . "','" . $travel_transfer_id . "','" . $payment_status . "','" . $payment_amount . "','" . $payment_currency . "','" . $txn_id . "','" . $receiver_email . "','" . $payer_email . "','" . $current_payment_date . "','" . $coupon_discount_amount . "','" . $coupon_code . "')";

    if (mysqli_query($conn, $lateroom_ipn_sql)) {

        $message = "New record created successfully";
    } else {

        $message = "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
} else if ($item_name == 'Travel') {

    $item_name = $_REQUEST['item_name'];
    $paypal_unick_code = $_REQUEST['item_number'];
    $travel_transfer_id = $_REQUEST['custom'];
    $payment_status = $_REQUEST['payment_status'];
    $payment_amount = $_REQUEST['mc_gross'];
    $payment_currency = $_REQUEST['mc_currency'];
    $txn_id = $_REQUEST['txn_id'];
    $receiver_email = $_REQUEST['business'];
    $payer_email = $_REQUEST['payer_email'];
    $current_payment_date = date("Y/m/d");
    $coupon_discount_amount = $_REQUEST['discount'];


    $conn = mysqli_connect($dbhost, $dbuser, $dbpass);
    //$sql='db704811659';
    $database = 'newoxy_gfwp';
    $db_select = mysqli_select_db($conn, $database);

    if (!$db_select) {

        die("Database selection failed: " . mysqli_error($conn));
    } else {
        //echo "Database created successfully\n";
    }


    $traval_ipn_sql = "INSERT INTO `payment_info_by_transfer`(`id`, `item_name`, `paypal_unick_code`, `travel_transfer_id`, `payment_status`, `payment_amount`, `payment_currency`, `txn_id`, `receiver_email`, `payer_email`, `current_payment_date`, `coupon_discount_amount`) VALUES ('','" . $item_name . "','" . $paypal_unick_code . "','" . $travel_transfer_id . "','" . $payment_status . "','" . $payment_amount . "','" . $payment_currency . "','" . $txn_id . "','" . $receiver_email . "','" . $payer_email . "','" . $current_payment_date . "','" . $coupon_discount_amount . "')";

    if (mysqli_query($conn, $traval_ipn_sql)) {

        $message = "New record created successfully";
    } else {


        $message = "Error: " . $database . "<br>" . mysqli_error($conn);
    }
} else {



    $hotel_name = $_REQUEST['item_name'];
    $paypal_unick_code = $_REQUEST['item_number'];
    $hotel_quote_id = $_REQUEST['custom'];
    $payment_status = strtolower($_REQUEST['payment_status']);
    $payment_amount = $_REQUEST['mc_gross'];
    $payment_currency = $_REQUEST['mc_currency'];
    $txn_id = $_REQUEST['txn_id'];
    $receiver_email = $_REQUEST['business'];
    $payer_email = $_REQUEST['payer_email'];
    $current_payment_date = date("Y/m/d");
    $coupon_discount_amount = $_REQUEST['discount'];

    //mail("samareshbera2016@gmail.com", "NORMAL IPN RESULT YAY MONEY!","Simple mail test...01");

    //Travellanda
    if (strpos($hotel_quote_id, 'travellanda_') !== false) {
        //include(dirname(__FILE__) . '/wp-config.php');
        require_once 'wp-load.php';
        $hotel_quote_id = str_replace('travellanda_', '', $hotel_quote_id);
        update_post_meta($hotel_quote_id, 'gc4t_discount_amount', $coupon_discount_amount);
        update_post_meta($hotel_quote_id, 'gc4t_receiver_email', $receiver_email);
        update_post_meta($hotel_quote_id, 'gc4t_payer_email', $payer_email);
        update_post_meta($hotel_quote_id, 'gc4t_payment_date', date('Y-m-d'));
        update_post_meta($hotel_quote_id, 'gc4t_payment_amount', $payment_amount);
        update_post_meta($hotel_quote_id, 'gc4t_payment_currency', $payment_currency);
        update_post_meta($hotel_quote_id, 'gc4t_status', $payment_status);
        update_post_meta($hotel_quote_id, 'gc4t_transaction_id', $txn_id);
        mail("johnston.jasond@gmail.com", "NORMAL IPN RESULT YAY Amount!", $message);
    } else {
        $conn = mysqli_connect($dbhost, $dbuser, $dbpass);
        //$database = 'newoxy_gfwp';
        $database = 'travelgiftwp';
        $db_select = mysqli_select_db($conn, $database);
        if (!$db_select) {
            die("Database selection failed: " . mysqli_error($conn));
        } else {
            //echo "Database created successfully\n";
        }
        $sql_insert_ipn = "INSERT INTO `payment_info`(`id`, `hotel_name`, `paypal_unick_code`, `hotel_quote_id`, `payment_status`, `hotel_total_price`, `mc_currency`, `txn_id`, `business`, `payer_email`, `current_payment_date`,`ipn_verified`,`coupon_discount_amount`) values('','" . $hotel_name . "','" . $paypal_unick_code . "','" . $hotel_quote_id . "','" . $payment_status . "','" . $payment_amount . "','" . $payment_currency . "','" . $txn_id . "','" . $receiver_email . "','" . $payer_email . "','" . $current_payment_date . "','Y','" . $coupon_discount_amount . "')";

        if (mysqli_query($conn, $sql_insert_ipn)) {
            $message = "New record created successfully";
        } else {
            $message = "Error: " . $database . "<br>" . mysqli_error($conn);
        }


        mysqli_close($conn);
        mail("johnston.jasond@gmail.com", "NORMAL IPN RESULT YAY Amount!", $message);
        /*mail("samareshbera2016@gmail.com", "NORMAL IPN RESULT YAY MONEY!",$message2);*/
    }
}
