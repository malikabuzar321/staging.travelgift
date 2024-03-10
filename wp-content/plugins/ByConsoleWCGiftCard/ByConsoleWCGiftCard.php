<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly /** 

/*

* Plugin Name: BYC WooCommerce GiftCard

* Plugin URI: https://www.plugins.byconsole.com/

* Description: Purchase a gift card with the amount you wish. 

* Version: 1.0.0

* Author: Mrinmoy Dalabar 

* Author URI: https://www.byconsole.com 

* Text Domain: ByConsoleWCGiftCard

* Domain Path: /languages

* License: GPL2 

*/

include('inc/admin.php');

/**

* Add a custom field in checkout form

* */

// Hook in

add_action('woocommerce_after_order_notes', 'byconsole_giftcard_checkout_field');

function byconsole_giftcard_checkout_field($checkout)

{

echo '<div id="byconsole_coupon_checkout_field"><h2>' . __('Create your gift card') . '</h2>';

/*woocommerce_form_field('byconsole_giftcard_amount', array(

'type' => 'text',

'class' => array(

'my-field-class form-row-wide'

) ,

'label' => __('Gift card amount') ,

'placeholder' => __('Enter giftcard amount') ,

'required' => true,

));*/

woocommerce_form_field('byconsole_giftcard_message', array(

'type' => 'textarea',

'class' => array(

'my-field-class form-row-wide'

) ,

'label' => __('Gift card message') ,

'placeholder' => __('Enter your giftcard message') ,

'required' => true,

));

woocommerce_form_field('byconsole_giftcard_schedule_send', array(

	'type' => 'text',
	
	'class' => array(
	
	'my-field-class form-row-wide gctf-schedule-send'
	
	) ,
	
	'label' => __('Send date') ,
	
	'placeholder' => __('Optionally schedule E-Giftcard send') ,
	
	'required' => false,
	
	));
echo '<div id="byconsole_coupon_checkout_field"><h3>' . __(' Details of Recipient') . '</h3>';

woocommerce_form_field('byconsole_giftcard_first_name', array(

'type' => 'text',

'class' => array(

'my-field-class form-row-wide'

) ,

'label' => __('First Name') ,

'placeholder' => __('Enter first name here..') ,

'required' => true,

));

woocommerce_form_field('byconsole_giftcard_last_name', array(

'type' => 'text',

'class' => array(

'my-field-class form-row-wide'

) ,

'label' => __('Last Name') ,

'placeholder' => __('Enter last name here..') ,

'required' => true,

));

woocommerce_form_field('byconsole_giftcard_email', array(

'type' => 'text',

'class' => array(

'my-field-class form-row-wide'

) ,

'label' => __('Email') ,

'placeholder' => __('Enter email here..') ,

'required' => true,

));



echo '</div>';

}

//Save the order meta with field value

add_action( 'woocommerce_checkout_update_order_meta', 'byconsole_giftcard_checkout_field_update_order_meta' );

function byconsole_giftcard_checkout_field_update_order_meta($order_id) {

$byconsole_giftcard_amount = 0;	
foreach( WC()->session->cart as $cart_key => $cart_subtotal ){
		
		$byconsole_giftcard_amount += $cart_subtotal['line_subtotal'];
	
		}	


if ( ! empty( $byconsole_giftcard_amount ) ) {

//$POST_byconsole_giftcard_amount=$_POST['byconsole_giftcard_amount'];

update_post_meta( $order_id, 'byconsole_giftcard_amount', $byconsole_giftcard_amount );

}

if ( ! empty( $_POST['byconsole_giftcard_message'] ) ) {

$POST_byconsole_giftcard_message=sanitize_text_field($_POST['byconsole_giftcard_message']);	

update_post_meta( $order_id, 'byconsole_giftcard_message', $POST_byconsole_giftcard_message );

}

if ( ! empty( $_POST['byconsole_giftcard_schedule_send'] ) ) {

	$POST_byconsole_giftcard_schedule_send=sanitize_text_field($_POST['byconsole_giftcard_schedule_send']);	
	
	update_post_meta( $order_id, 'byconsole_giftcard_schedule_send', $POST_byconsole_giftcard_schedule_send );
	
	}

if ( ! empty( $_POST['byconsole_giftcard_first_name'] ) ) {

$POST_byconsole_giftcard_first_name=sanitize_text_field($_POST['byconsole_giftcard_first_name']);	

update_post_meta( $order_id, 'byconsole_giftcard_first_name', $POST_byconsole_giftcard_first_name );

}
if ( ! empty( $_POST['byconsole_giftcard_last_name'] ) ) {

$POST_byconsole_giftcard_last_name=sanitize_text_field($_POST['byconsole_giftcard_last_name']);	

update_post_meta( $order_id, 'byconsole_giftcard_last_name', $POST_byconsole_giftcard_last_name );

}
if ( ! empty( $_POST['byconsole_giftcard_email'] ) ) {

$POST_byconsole_giftcard_email=sanitize_text_field($_POST['byconsole_giftcard_email']);	

update_post_meta( $order_id, 'byconsole_giftcard_email', $POST_byconsole_giftcard_email );

}

$user_giftcard_code = '';

$order = wc_get_order($order_id);
$items = $order->get_items();
$virtual_order = false;
foreach ($items as $item) {
	$wc_product = wc_get_product($item['product_id']);
	if ($wc_product) {
		$product_variation_id = $item->get_variation_id();
		$variation = new WC_Product_Variation( $product_variation_id );
		if ($variation) {
			if ($variation->is_virtual()) {
				$virtual_order = true;
				//file_put_contents(dirname(__FILE__) . '/testing.txt', print_r($variation, true));
				break;
			}
		}
	}
}
if ($virtual_order) {
	$user_giftcard_code = mt_rand(100, 999).'b'.mt_rand(100, 999).'y'.mt_rand(100, 999).'c'.mt_rand(100, 999);
}

if ( ! empty($user_giftcard_code)) {

update_post_meta( $order_id, 'user_giftcard_code',$user_giftcard_code );

//create a counpon with the same code and amount 

//get user id

global $post;

// Get an instance of the WC_Order object

//$order = wc_get_order($order_id);

// Get the user ID from WC_Order methods

//$user_id = $order->get_user_id(); // or $order->get_customer_id();

//$user_id = 1;

//$coupon_code = 'UNIQUECODE'; // Code

//$amount = '10'; // Amount

//$POST_byconsole_giftcard_amount=10;

$discount_type = 'fixed_cart'; // Type: fixed_cart, percent, fixed_product, percent_product

$byc_coupon = array(

'post_title' => $user_giftcard_code,

'post_excerpt' => $POST_byconsole_giftcard_message,

/*'post_content' => 'Created from ',*/

'post_status' => 'publish',

'post_author' => $user_id,

'post_type'		=> 'shop_coupon'

);

$byc_coupon_id = wp_insert_post( $byc_coupon );

// Add meta

update_post_meta( $byc_coupon_id, 'discount_type', $discount_type );

update_post_meta( $byc_coupon_id, 'coupon_amount', $byconsole_giftcard_amount );

update_post_meta( $byc_coupon_id, 'individual_use', 'no' );

update_post_meta( $byc_coupon_id, 'product_ids', '' );

update_post_meta( $byc_coupon_id, 'exclude_product_ids', '' );

update_post_meta( $byc_coupon_id, 'usage_limit', '' );

update_post_meta( $byc_coupon_id, 'expiry_date', '' );

update_post_meta( $byc_coupon_id, 'apply_before_tax', 'yes' );

update_post_meta( $byc_coupon_id, 'free_shipping', 'no' );

update_post_meta( $byc_coupon_id, 'original_amount', $byconsole_giftcard_amount );

update_post_meta( $byc_coupon_id, 'order_id', $order_id );

// end of coupon creation

}

}

/*add_action( 'woocommerce_before_calculate_totals', 'add_custom_price' );

function add_custom_price($checkout){

$custom_price = $_POST['byconsole_coupon_amount_type']; // This will be your custome price  

//$custom_price = 10; // This will be your custome price 

foreach ( $cart_object->cart_contents as $key => $value ) {

//$value['data']->price = $custom_price;

// for WooCommerce version 3+ use: 

$value['data']->set_price($custom_price);

}

}

*/

//add_action( 'woocommerce_before_calculate_totals', 'byconsole_giftcard_change_subtotal_price', 10, 1);

/*function byconsole_giftcard_change_subtotal_price( $cart_object ) {

if ( is_admin() && ! defined( 'DOING_AJAX' ) )

return;

foreach ( $cart_object->get_cart() as $cart_item ) {

## Price calculation ##

//var_dump($cart_item);

## Set the price with WooCommerce compatibility ##

if ( version_compare( WC_VERSION, '3.0', '<' ) ) {

$price = $cart_item['data']->price * 0;

$cart_item['data']->price = $price; // Before WC 3.0

} else {

$price = $cart_item['data']->get_price() * 0;

$cart_item['data']->set_price( $price ); // WC 3.0+

}

}

}*/

/*function byconsole_giftcard_processing_fees() {

global $woocommerce;

$byconsole_giftcard_processing_fees = get_option('byconsole_giftcard_processing_fees');

$woocommerce->cart->add_fee( __('Processing fees', 'woocommerce'), $byconsole_giftcard_processing_fees );

}

add_action( 'woocommerce_cart_calculate_fees', 'byconsole_giftcard_processing_fees' );*/

function byconsole_giftcard_price() {

global $woocommerce;

if ( ! $_POST || ( is_admin() && ! is_ajax() ) ) {

return;

}

if ( isset( $_POST['post_data'] ) ) {

parse_str( $_POST['post_data'], $post_data );

} else {

$post_data = $_POST; // fallback for final checkout (non-ajax)

}

if (isset($post_data['byconsole_giftcard_amount']) && $post_data['byconsole_giftcard_amount']!='') 

{

$byconsole_giftcard_amount = $post_data['byconsole_giftcard_amount'];	

}

else

{

$byconsole_giftcard_amount = 0;

}

//$woocommerce->cart->add_fee( __('Giftcard price', 'woocommerce'), $byconsole_giftcard_amount);

}

//add_action( 'woocommerce_cart_calculate_fees', 'byconsole_giftcard_price' );

function byconsole_giftcard_footer_script(){

?>

<script>

jQuery(document).ready(function(){

/*jQuery("#byconsole_giftcard_amount").change(function(){			

alert();

});*/

jQuery("#byconsole_giftcard_amount").change(function(){	

//alert();

//update_order_review_table(jQuery(this).val(),jQuery('#byconsole_giftcard_amount').val());

jQuery('body').trigger('update_checkout');

//jQuery(document.body).trigger("update_checkout");

//alert("aaaa");

});

});

</script>

<?php

}

add_action('wp_footer','byconsole_giftcard_footer_script',9999);



function byconsole_giftcard_woocommerce_email_after_order_table( $order ) {

$byc_plugin_url = plugins_url();

$byconsole_giftcard_amount=get_post_meta( $order->id, 'byconsole_giftcard_amount', true );

$user_giftcard_code=get_post_meta( $order->id, 'user_giftcard_code', true );

$user_giftcard_message=get_post_meta( $order->id, 'byconsole_giftcard_message', true );

$user_giftcard_schedule_send = get_post_meta( $order->id, 'byconsole_giftcard_schedule_send', true );

$byconsole_giftcard_image_string = "<p><strong>Your Gift Card:</strong></p><br/><img src='".$byc_plugin_url."/ByConsoleWCGiftCard/image/gift-card2.png' alt='Gift card' style='max-width:100% !important;' />";

$byconsole_giftcard_amount_string='<p><strong>Gift card Amount:</strong> ' . $byconsole_giftcard_amount . '</p>';

if ($user_giftcard_code) {
	$user_giftcard_code_string='<p><strong>Gift card Code:</strong> ' . $user_giftcard_code . '</p>';
} else {
	$user_giftcard_code_string='<p><strong>Your gift card will be posted to you shortly</strong></p>';
}

$user_giftcard_massage_view='<p><strong>Gift card Message:</strong> ' . $user_giftcard_message . '</p>';

echo $byconsole_giftcard_amount_string;

echo $user_giftcard_code_string;

echo $user_giftcard_massage_view;

echo $byconsole_giftcard_image_string;

}

add_action("woocommerce_email_after_order_table","byconsole_giftcard_woocommerce_email_after_order_table", 10, 1);



function byconsole_custom_wooemail_headers( $headers, $object, $order ) {



$additional_email_id_by_giftcart=$user_giftcard_code=get_post_meta( $order->id, 'byconsole_giftcard_email', true );;



// replace the emails below to your desire email

$emails = array($additional_email_id_by_giftcart, 'rob@giftcards4travel.co.uk');



switch($object) {



case 'new_order':



$headers .= 'Bcc: ' . implode(',', $emails) . "\r\n";			



break;



case 'customer_processing_order':



case 'customer_completed_order':



case 'customer_invoice':	



default:



}



return $headers;



}

add_filter( 'woocommerce_email_headers', 'byconsole_custom_wooemail_headers', 10, 3);
?>