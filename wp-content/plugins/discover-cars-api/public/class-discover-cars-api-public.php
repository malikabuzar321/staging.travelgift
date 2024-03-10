<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.giftcards4travel.co.uk/
 * @since      1.0.0
 *
 * @package    Discover_Cars_Api
 * @subpackage Discover_Cars_Api/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Discover_Cars_Api
 * @subpackage Discover_Cars_Api/public
 * @author     Giftcards4travel <rob@giftcards4travel.co.uk>
 */

class Discover_Cars_Api_Public
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Discover_Cars_Api_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Discover_Cars_Api_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style('dca-ui', 'https://code.jquery.com/ui/1.11.4/themes/ui-lightness/jquery-ui.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/discover-cars-api-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Discover_Cars_Api_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Discover_Cars_Api_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_register_script('dca-ui-js', 'https://code.jquery.com/ui/1.12.1/jquery-ui.js', array('jquery'), $this->version, false);
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/discover-cars-api-public.js', array('jquery','dca-ui-js'), '1.0.2', false);
		wp_localize_script($this->plugin_name, 'dca', array(
			'ajaxurl' => admin_url('admin-ajax.php')
		));

		wp_enqueue_script('paypal-payment', plugin_dir_url(__FILE__) . 'js/payment.js', array('jquery'), '1.0.2', true);
		wp_localize_script('paypal-payment', 'payment', array('ajax' => admin_url('admin-ajax.php'), 'hostUrl' => site_url()));
	}

	/**
	 * Register shortcode for car search form
	 *
	 * @since    1.0.0
	 */
	public function register_shortcodes()
	{
		add_shortcode('Giftcard-Car-Search', array($this, 'giftcard_car_search_form'));
	}

	/**
	 * Register the session for the site.
	 *
	 * @since    1.0.0
	 */

	public function dca_register_my_session()
	{
		if (!session_id()) {
			session_start(['read_and_close' => true]);
		}
	}

	/**
	 * Render shortcode for car search form
	 *
	 * @since    1.0.0
	 */
	public function giftcard_car_search_form()
	{
		//wp_enqueue_script( 'dca-ui-js' );
		wp_enqueue_script($this->plugin_name);
		ob_start();
		include_once plugin_dir_path(__FILE__) . 'partials/discover-cars-api-search-form.php';
		$html = ob_get_clean();
		return $html;
	}

	/**
	 * Seacrh locations by keyword
	 *
	 * @since    1.0.0
	 */
	public function dca_search_locations()
	{
		$response = array();
		if (!isset($_POST['s']) || empty($_POST['s'])) {
			echo  json_encode($response);
			exit;
		}
		global $wpdb;
		$keyword = $_POST['s'];
		$table_name = $wpdb->prefix . 'discovercars_locations';


		$results  = $wpdb->get_results("SELECT * FROM $table_name WHERE `Location` LIKE '%$keyword%'");

		if (empty($results)) {
			echo  json_encode($response);
			exit;
		}



		foreach ($results as $key => $result) {
			$response[] = array("value" => $result->LocationID, "label" => $result->Location, 'country' => $result->CountryCode);
		}
		echo json_encode($response);
		exit;
	}


	/**
	 * Search cars in API and show results
	 *
	 * @since    1.0.0
	 */
	public function dca_search_results()
	{
		//ini_set('display_errors', 1);
		$pickup_date = isset($_POST['dca-pickup-date']) ? date('d.m.Y', strtotime($_POST['dca-pickup-date'])) : '';
		$pickup_time = isset($_POST['dca-pickup-time']) ? date('H:i', strtotime($_POST['dca-pickup-time'])) : '';
		$pick_date_time = $pickup_date . 'T' . $pickup_time;

		$drop_date = isset($_POST['dca-drop-date']) ? date('d.m.Y', strtotime($_POST['dca-drop-date'])) : '';
		$drop_time = isset($_POST['dca-drop-time']) ? date('H:i', strtotime($_POST['dca-drop-time'])) : '';
		$drop_date_time = $drop_date . 'T' . $drop_time;

		$pick_location = isset($_POST['dca-pickup-id']) ? $_POST['dca-pickup-id'] : '';
		$dropoff_location = isset($_POST['dca-dropoff-id']) ? $_POST['dca-dropoff-id'] : '';
		$body = array(
			'DateFrom' => $pick_date_time,
			'DateTo' => $drop_date_time,
			"PickupLocationID" =>  $pick_location,
			"DropOffLocationID" =>  $dropoff_location,
			"CurrencyCode" =>  "GBP",
			"Pos" =>  "UK",

		);

		$datediff = strtotime($drop_date) - strtotime($pickup_date);
		$days = round($datediff / (60 * 60 * 24));
		$body = json_encode($body);
		$dca = new Discover_Cars_Api_Methods();
		$cars = $dca->get_cars($body);

		// $_SESSION['cars'] = $cars;
		// update_option('_dca_cars', $cars);
		//dc($_SESSION, 1);
		//$cars = get_option('_dca_cars');

		global $wpdb;
		ob_start();
		include_once plugin_dir_path(__FILE__) . 'partials/discover-cars-api-search-results.php';
		$html = ob_get_clean();
		echo $html;
		exit();
	}

	/**
	 * Store car data to session and redirect to car booking page
	 *
	 * @since    1.0.0
	 */
	public function dca_booking_form()
	{
		print_r($_SESSION);
		die;
		unset($_POST['action']);
		$_SESSION['dca_booking_data'] = $_POST;

		echo json_encode(array('link' => site_url() . '/car-hire-2'));
		exit;
	}

	/**
	 * Insert new booking and redirect to paypal for payment
	 *
	 * @since    1.0.0
	 */

	public function dca_paypal_pament()
	{
		$data = $_POST['data'];
		parse_str($data, $output);
		$driver_title 			= $output['driver_title'];
		$driver_first_name      = $output['driver_first_name'];
		$driver_last_name       = $output['driver_last_name'];
		$driver_email       	= $output['driver_email'];
		$rob_driverphone        = $output['country_code'] . ' ' . $output['rob_phone'];
		$rob_driverDob       	= $output['dd'] . '-' . $output['mm'] . '-' . $output['yyyy'];
		$residence       		= $output['residence_country'];
		$total_amount       	= $output['total_amount'];

		$car_name       		= $output['car_name'];
		$seats       			= $output['seats'];
		$bags       			= $output['bags'];
		$doors       			= $output['doors'];
		$air_condition       	= $output['air_condition'];
		$rent_days       		= $output['rent_days'];
		$pick_date_time       	= $output['pick_date_time'];
		$drop_date_time       	= $output['drop_date_time'];
		$pick_location       	= $output['pick_location'];
		$dropoff_location       = $output['dropoff_location'];

		$coupen_code       		= $output['discount_coupen_code'];
		$coupen_value       	= $output['discount_coupen_value'];
		//dc($output, 1);
		$postData = array(
			'post_title' 	=> 'Booking By - ' . $driver_first_name,
			'post_status'	=> 'publish',
			'post_type'		=> 'gfc_booking'
		);

		$booking_id = wp_insert_post($postData);

		if (is_wp_error($booking_id)) {
			echo json_encode(array('res' => 'fail', 'msg' => $booking_id->get_error_message()));
			exit();
		}


		update_post_meta($booking_id, 'discount_coupen_code', $coupen_code);
		update_post_meta($booking_id, 'discount_coupen_value', $coupen_value);
		update_post_meta($booking_id, 'send_confirmation_email', 0);
		update_post_meta($booking_id, 'driver_title', $driver_title);
		update_post_meta($booking_id, 'driver_first_name', $driver_first_name);
		update_post_meta($booking_id, 'driver_last_name', $driver_last_name);
		update_post_meta($booking_id, 'driver_email', $driver_email);
		update_post_meta($booking_id, 'rob_driverphone', $rob_driverphone);
		update_post_meta($booking_id, 'rob_driverDob', $rob_driverDob);
		update_post_meta($booking_id, 'residence', $residence);
		update_post_meta($booking_id, 'total_amount', $total_amount);
		update_post_meta($booking_id, 'car_name', $car_name);
		update_post_meta($booking_id, 'seats', $seats);
		update_post_meta($booking_id, 'bags', $bags);
		update_post_meta($booking_id, 'doors', $doors);
		update_post_meta($booking_id, 'air_condition', $air_condition);
		update_post_meta($booking_id, 'rent_days', $rent_days);
		update_post_meta($booking_id, 'pick_date_time', $pick_date_time);
		update_post_meta($booking_id, 'drop_date_time', $drop_date_time);
		update_post_meta($booking_id, 'pick_location', $pick_location);
		update_post_meta($booking_id, 'dropoff_location', $dropoff_location);
		update_post_meta($booking_id, '_status', 'pending');


		echo json_encode(array('res' => 'success', 'booking_id' => $booking_id));
	
		exit();
	}

	/**
	 * Handle paypal response and save 
	 *
	 * @since    1.0.0
	 */
	public function dca_paypal_response()
	{

		if (is_page('cars-thank-you') && isset($_GET['cm']) && ($_GET['cm'] > 0) && isset($_GET['payment_status'])) {
			$post_id = (isset($_GET['cm']) && $_GET['cm']) ? $_GET['cm'] : '';
			update_post_meta($post_id, 'txn_id', $_GET['txn_id']);
			update_post_meta($post_id, 'txn_amount', $_GET['amt']);
			update_post_meta($post_id, 'txn_status', $_GET['payment_status']);
			$send_email = get_post_meta($post_id, 'send_confirmation_email', true);
			if (isset($_GET['pending_reason'])) {
				update_post_meta($post_id, 'txn_reason', $_GET['pending_reason']);
			}
			if (($_GET['pending_reason'] == 'Completed')  && ($send_email == 0)) {
				update_post_meta($post_id, 'send_confirmation_email', 1);
				update_post_meta($post_id, '_status', 'paid');
				$driver_name = get_post_meta($post_id, 'driver_title', true);
				$driver_first_name = get_post_meta($post_id, 'driver_first_name', true);
				$driver_last_name = get_post_meta($post_id, 'driver_last_name', true);
				$driver_email = get_post_meta($post_id, 'driver_email', true);
				$rob_driverphone = get_post_meta($post_id, 'rob_driverphone', true);
				$rob_driverDob = get_post_meta($post_id, 'rob_driverDob', true);
				$residence = get_post_meta($post_id, 'residence', true);
				$total_amount = get_post_meta($post_id, 'total_amount', true);
				$car_name = get_post_meta($post_id, 'car_name', true);
				$seats = get_post_meta($post_id, 'seats', true);
				$bags = get_post_meta($post_id, 'bags', true);
				$doors = get_post_meta($post_id, 'doors', true);
				$air_condition = get_post_meta($post_id, 'air_condition', true);
				$rent_days = get_post_meta($post_id, 'rent_days', true);

				$coupen = get_post_meta($post_id, 'discount_coupen_code', true);
				$coupen_value = get_post_meta($post_id, 'discount_coupen_value', true);

				$email_content .= '<table width="100%" style="max-width: 1200px; margin: 0 auto; padding: 15px; font-family:Arial, Helvetica, sans-serif; border:1px solid #061d2f; color:#061d2f" cellpadding="0" cellspacing="0">
				<tr>
				  <td colspan="3"><h2 style="font-size: 18px; background-color: #fad38f; padding: 10px 15px; text-align: center; margin: 0px;font-family: sans-serif; color: #061d2f; font-weight:600;">Confirm Ticket</h2></td>
				</tr>
				<tr>
				  <td align="center" colspan="3"style="font-size: 20px;font-family:Arial, Helvetica, sans-serif; text-align: center; padding:5px; padding-top: 20px;"><strong><u>' . $car_name . '</u></strong></td>
				</tr>
				<tr>
				  <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Seats :</strong> ' . $seats . '</td>
				  <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Air bags :</strong> ' . $bags . '</td>
				  <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Doors :</strong> ' . $doors . '</td>
				</tr>
				<tr>
				  <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Air condition :</strong> ' . $air_condition . '</td>
				  <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Rent days :</strong> ' . $rent_days . '</td>
				</tr>
				<tr>
				  <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Price :</strong> ' . $total_amount . '</td>
				  <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Booking status :</strong> Confirm</td>
				</tr>
				
			  </table>
			  </br>
			  <table width="100%" style="max-width: 1200px; margin: 0 auto; padding: 15px; font-family:Arial, Helvetica, sans-serif; border:1px solid #061d2f; color:#061d2f" cellpadding="0" cellspacing="0">
				<tr>
				  <td align="left" colspan="3"style="font-size: 20px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 20px;"><strong>Driver Details</strong></td>
				</tr>
				<tr>
				  <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 5px;"><strong>Driver name : </strong> ' . $driver_name . ' ' . $driver_first_name . ' ' . $driver_last_name . '</td>
				  <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 5px;"><strong>Driver email :</strong> ' . $driver_email . '</td>
				  <td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 5px;"><strong>Driver phone :</strong> ' . $rob_driverphone . '</td>
				</tr>
				<tr>
				<td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 5px;"><strong>Driver dob : </strong> ' . $rob_driverDob . '</td>
				<td width="20%" style="font-size: 16px;font-family:Arial, Helvetica, sans-serif; text-align: left; padding:5px; padding-top: 5px;"><strong>Driver residence : </strong> ' . $residence . '</td>
				</tr>
			  </table>
			  </br>';
				if (!empty($coupen_value) && !empty($coupen)) {
					$coupen_total = ($total_amount > $coupen_value) ? ($total_amount - $coupen_value) : '1.00';
					$email_content .= '<table width="100%" style="max-width: 1200px; margin: 0 auto; padding: 15px; font-family:Arial, Helvetica, sans-serif; border:1px solid #061d2f; color:#061d2f" cellpadding="0" cellspacing="0">
			  <tr>
			  <td colspan="5" style="padding-top:20px;">
				<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
				  <td style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:20px; border:2px solid #ccc;"><strong>Gift Card Code</strong><span style="color:#061d2f;font-size:16px;font-weight:600;padding:7px 15px;background-color:#f9d28e;display:inline-block"> ' . $coupen . '</span></td>
				  <td style="padding:15px; border:2px solid #ccc; border-left-width:0;"><table width="100%" cellpadding="0" cellspacing="0">
					  <tbody>
						<tr>
						  <td align="left" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">Car Price:</td>
						  <td align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;"><strong>£' . $total_amount . '</strong></td>
						</tr>
						<tr>
						  <td align="left" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">Coupan Price:</td>
						  <td  align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;"><strong>£' . $coupen_value . '</strong></td>
						</tr>
					  </tbody>
					  <tfoot>
						<tr>
						  <td align="left" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px; border-top:2px solid #777;">Total Price:</td>
						  <td align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px; border-top:2px solid #777;"><strong>£' . $coupen_total . '</strong></td>
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
					<td ><table width="100%" cellpadding="0" cellspacing="0" style="max-width:300px;padding:15px;border:2px solid #ccc" align="right">
						<tbody>
						  <tr>
							<td align="left" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;">Car Price:</td>
							<td align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px;"><strong>£' . $total_amount . '</strong></td>
						  </tr>
						</tbody>
						<tfoot>
						  <tr>
							<td align="left" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px; border-top:2px solid #777;">Total Price:</td>
							<td align="right" style="font-size: 14px;font-family: sans-serif; color: #061d2f; padding:5px; border-top:2px solid #777;"><strong>£' . $total_amount . '</strong></td>
						  </tr>
						</tfoot>
					  </table></td>
				  </tr>
				</table>
				</td>
				</tr>
			  </table>';
				}
				$email_content .= '<p>contact <a href="mailto:bookings@giftcards4travel.co.uk">bookings@giftcards4travel.co.uk</a> for any queries </p>';
				// echo $email_content;
				// $to = 'developersuseonly@gmail.com';
				$to = $driver_email;
				
				$subject = 'Booking confirmation';
				$headers = array('Content-Type: text/html; charset=UTF-8');
				wp_mail($to, $subject, $email_content, $headers);

				//Mail about booking reference
				$booking_message = 'A new car hire booking was made on GiftCards4Travel. Booking post ID: ' . $post_id . "\r\n";
				wp_mail('rob@giftcards4travel.co.uk', 'Car Hire Booking Complete - ' . $post_id, $booking_message);

				if ($coupen) {
					$coupon_wp = new WC_Coupon($coupen);
				
					$amount = $coupon_wp->get_amount();
					if ($amount) {
						if ($coupon_wp->get_discount_type() != 'percent') {}
							$new_remaining_amount = $amount - $coupen_value;
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
							'value' => $coupen,
							)
						)
						));
						if (isset($store_vouchers[0])) {
							$voucher_amount = get_post_meta($store_vouchers[0]->ID, 'gc4t_voucher_amount', true);
							$voucher_amount_remaining = get_post_meta($store_vouchers[0]->ID, 'gc4t_voucher_amount_remaining', true);
							$new_remaining_amount = $voucher_amount_remaining - $coupen_value;
							$new_remaining_amount = max($new_remaining_amount, 0);
							update_post_meta($store_vouchers[0]->ID, 'gc4t_voucher_amount_remaining', $new_remaining_amount);
							if ($new_remaining_amount == 0) {
								update_post_meta($store_vouchers[0]->ID, 'gc4t_voucher_status', 'used');
							}
						}
					}
				}
			}
		}

	/**
	 * Override car booking page template
	 *
	 * @since    1.0.0
	 */
	public function dca_template_override($template)
	{
		if (is_page('car-hire-2')) {
			return plugin_dir_path(__FILE__) . 'page-car-booking.php';
		}
		return $template;
	}
}

/**
 * All countries array
 *
 * @since    1.0.0
 */
function dca_countries()
{
	$countries = array(
		'AF' => 'Afghanistan', 'AL' => 'Albania', 'DZ' => 'Algeria', 'AS' => 'American Samoa', 'AD' => 'Andorra', 'AO' => 'Angola', 'AI' => 'Anguilla', 'AQ' => 'Antarctica', 'AG' => 'Antigua and Barbuda', 'AR' => 'Argentina', 'AM' => 'Armenia', 'AW' => 'Aruba', 'AU' => 'Australia', 'AT' => 'Austria', 'AZ' => 'Azerbaijan', 'BS' => 'Bahamas', 'BH' => 'Bahrain', 'BD' => 'Bangladesh', 'BB' => 'Barbados', 'BY' => 'Belarus', 'BE' => 'Belgium', 'BZ' => 'Belize', 'BJ' => 'Benin', 'BM' => 'Bermuda', 'BT' => 'Bhutan', 'BO' => 'Bolivia', 'BA' => 'Bosnia and Herzegovina', 'BW' => 'Botswana', 'BV' => 'Bouvet Island', 'BR' => 'Brazil', 'BQ' => 'British Antarctic Territory', 'IO' => 'British Indian Ocean Territory', 'VG' => 'British Virgin Islands', 'BN' => 'Brunei', 'BG' => 'Bulgaria', 'BF' => 'Burkina Faso', 'BI' => 'Burundi', 'KH' => 'Cambodia', 'CM' => 'Cameroon', 'CA' => 'Canada', 'CT' => 'Canton and Enderbury Islands', 'CV' => 'Cape Verde', 'KY' => 'Cayman Islands', 'CF' => 'Central African Republic', 'TD' => 'Chad', 'CL' => 'Chile', 'CN' => 'China', 'CX' => 'Christmas Island', 'CC' => 'Cocos [Keeling] Islands', 'CO' => 'Colombia', 'KM' => 'Comoros', 'CG' => 'Congo - Brazzaville', 'CD' => 'Congo - Kinshasa', 'CK' => 'Cook Islands', 'CR' => 'Costa Rica', 'HR' => 'Croatia', 'CU' => 'Cuba', 'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'CI' => 'Côte d’Ivoire', 'DK' => 'Denmark', 'DJ' => 'Djibouti', 'DM' => 'Dominica', 'DO' => 'Dominican Republic', 'NQ' => 'Dronning Maud Land', 'DD' => 'East Germany', 'EC' => 'Ecuador', 'EG' => 'Egypt', 'SV' => 'El Salvador', 'GQ' => 'Equatorial Guinea', 'ER' => 'Eritrea', 'EE' => 'Estonia', 'ET' => 'Ethiopia', 'FK' => 'Falkland Islands', 'FO' => 'Faroe Islands', 'FJ' => 'Fiji', 'FI' => 'Finland', 'FR' => 'France', 'GF' => 'French Guiana', 'PF' => 'French Polynesia', 'TF' => 'French Southern Territories', 'FQ' => 'French Southern and Antarctic Territories', 'GA' => 'Gabon', 'GM' => 'Gambia', 'GE' => 'Georgia', 'DE' => 'Germany', 'GH' => 'Ghana', 'GI' => 'Gibraltar', 'GR' => 'Greece', 'GL' => 'Greenland', 'GD' => 'Grenada', 'GP' => 'Guadeloupe', 'GU' => 'Guam', 'GT' => 'Guatemala', 'GG' => 'Guernsey', 'GN' => 'Guinea', 'GW' => 'Guinea-Bissau', 'GY' => 'Guyana', 'HT' => 'Haiti', 'HM' => 'Heard Island and McDonald Islands', 'HN' => 'Honduras', 'HK' => 'Hong Kong SAR China', 'HU' => 'Hungary', 'IS' => 'Iceland', 'IN' => 'India', 'ID' => 'Indonesia', 'IR' => 'Iran', 'IQ' => 'Iraq', 'IE' => 'Ireland', 'IM' => 'Isle of Man', 'IL' => 'Israel', 'IT' => 'Italy', 'JM' => 'Jamaica', 'JP' => 'Japan', 'JE' => 'Jersey', 'JT' => 'Johnston Island', 'JO' => 'Jordan', 'KZ' => 'Kazakhstan', 'KE' => 'Kenya', 'KI' => 'Kiribati', 'KW' => 'Kuwait', 'KG' => 'Kyrgyzstan', 'LA' => 'Laos', 'LV' => 'Latvia', 'LB' => 'Lebanon', 'LS' => 'Lesotho', 'LR' => 'Liberia', 'LY' => 'Libya', 'LI' => 'Liechtenstein', 'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'MO' => 'Macau SAR China', 'MK' => 'Macedonia', 'MG' => 'Madagascar', 'MW' => 'Malawi', 'MY' => 'Malaysia', 'MV' => 'Maldives', 'ML' => 'Mali', 'MT' => 'Malta', 'MH' => 'Marshall Islands', 'MQ' => 'Martinique', 'MR' => 'Mauritania', 'MU' => 'Mauritius', 'YT' => 'Mayotte', 'FX' => 'Metropolitan France', 'MX' => 'Mexico', 'FM' => 'Micronesia', 'MI' => 'Midway Islands', 'MD' => 'Moldova', 'MC' => 'Monaco', 'MN' => 'Mongolia', 'ME' => 'Montenegro', 'MS' => 'Montserrat', 'MA' => 'Morocco', 'MZ' => 'Mozambique', 'MM' => 'Myanmar [Burma]', 'NA' => 'Namibia', 'NR' => 'Nauru', 'NP' => 'Nepal', 'NL' => 'Netherlands', 'AN' => 'Netherlands Antilles', 'NT' => 'Neutral Zone', 'NC' => 'New Caledonia', 'NZ' => 'New Zealand', 'NI' => 'Nicaragua', 'NE' => 'Niger', 'NG' => 'Nigeria', 'NU' => 'Niue', 'NF' => 'Norfolk Island', 'KP' => 'North Korea', 'VD' => 'North Vietnam', 'MP' => 'Northern Mariana Islands', 'NO' => 'Norway', 'OM' => 'Oman', 'PC' => 'Pacific Islands Trust Territory', 'PK' => 'Pakistan', 'PW' => 'Palau', 'PS' => 'Palestinian Territories', 'PA' => 'Panama', 'PZ' => 'Panama Canal Zone', 'PG' => 'Papua New Guinea', 'PY' => 'Paraguay', 'YD' => 'People\'s Democratic Republic of Yemen', 'PE' => 'Peru', 'PH' => 'Philippines', 'PN' => 'Pitcairn Islands', 'PL' => 'Poland', 'PT' => 'Portugal', 'PR' => 'Puerto Rico', 'QA' => 'Qatar', 'RO' => 'Romania', 'RU' => 'Russia', 'RW' => 'Rwanda', 'BL' => 'Saint Barthélemy', 'SH' => 'Saint Helena', 'KN' => 'Saint Kitts and Nevis', 'LC' => 'Saint Lucia', 'MF' => 'Saint Martin', 'PM' => 'Saint Pierre and Miquelon', 'VC' => 'Saint Vincent and the Grenadines', 'WS' => 'Samoa', 'SM' => 'San Marino', 'SA' => 'Saudi Arabia', 'SN' => 'Senegal', 'RS' => 'Serbia', 'CS' => 'Serbia and Montenegro', 'SC' => 'Seychelles', 'SL' => 'Sierra Leone', 'SG' => 'Singapore', 'SK' => 'Slovakia', 'SI' => 'Slovenia', 'SB' => 'Solomon Islands', 'SO' => 'Somalia', 'ZA' => 'South Africa', 'GS' => 'South Georgia and the South Sandwich Islands', 'KR' => 'South Korea', 'ES' => 'Spain', 'LK' => 'Sri Lanka', 'SD' => 'Sudan', 'SR' => 'Suriname', 'SJ' => 'Svalbard and Jan Mayen', 'SZ' => 'Swaziland', 'SE' => 'Sweden', 'CH' => 'Switzerland', 'SY' => 'Syria', 'ST' => 'São Tomé and Príncipe', 'TW' => 'Taiwan', 'TJ' => 'Tajikistan', 'TZ' => 'Tanzania', 'TH' => 'Thailand', 'TL' => 'Timor-Leste', 'TG' => 'Togo', 'TK' => 'Tokelau', 'TO' => 'Tonga', 'TT' => 'Trinidad and Tobago', 'TN' => 'Tunisia', 'TR' => 'Turkey', 'TM' => 'Turkmenistan', 'TC' => 'Turks and Caicos Islands', 'TV' => 'Tuvalu', 'UM' => 'U.S. Minor Outlying Islands', 'PU' => 'U.S. Miscellaneous Pacific Islands', 'VI' => 'U.S. Virgin Islands', 'UG' => 'Uganda', 'UA' => 'Ukraine', 'SU' => 'Union of Soviet Socialist Republics', 'AE' => 'United Arab Emirates', 'GB' => 'United Kingdom', 'US' => 'United States', 'ZZ' => 'Unknown or Invalid Region', 'UY' => 'Uruguay', 'UZ' => 'Uzbekistan', 'VU' => 'Vanuatu', 'VA' => 'Vatican City', 'VE' => 'Venezuela', 'VN' => 'Vietnam', 'WK' => 'Wake Island', 'WF' => 'Wallis and Futuna', 'EH' => 'Western Sahara', 'YE' => 'Yemen', 'ZM' => 'Zambia', 'ZW' => 'Zimbabwe', 'AX' => 'Åland Islands'
	);
	return $countries;
}

// add_action('template_redirect', 'dca_prevent_booking_page');
function dca_prevent_booking_page()
{
	$car = isset($_SESSION['dca_booking_data']) ? $_SESSION['dca_booking_data'] : array();
	if (is_page('car-hire-2') && empty($car) && !is_admin() && !isset($_GET['ct_builder'])) {
		wp_redirect(site_url() . '/car-hire');
		exit();
	}

	if (!is_page('car-hire') || !is_page('car-hire-2')) {
		// if( ! session_id() ) {
		//        session_start();
		//    }
		//    die();
		if (session_id()) {

			session_destroy();
		}
	} else {
		//session_destroy();
	}
}


function ajax_init_func()
{
	if ((is_page('car-hire-2') || is_page('car-hire')) && (session_status() != 2)) {
		session_start();
		// echo session_status();
		// echo session_id();
	}
}
add_action('init', 'ajax_init_func');

add_action('wp_ajax_gctc_car_coupon_search', 'gctc_car_coupon_search');
add_action('wp_ajax_nopriv_gctc_car_coupon_search', 'gctc_car_coupon_search');
function gctc_car_coupon_search()
{

	$amount = 0;
	$discount_type = 'amount';
	$total_price = sanitize_text_field($_POST['total_price']);

	if (!empty($_POST['coupon_code_search'])) {
		$coupon_code = sanitize_text_field($_POST['coupon_code_search']);
		$coupon_wp = new WC_Coupon($coupon_code);
		if ($coupon_wp->get_discount_type() == 'percent') {
			$discount_type = 'percent';
		}
		$amount = $coupon_wp->get_amount();
		if (!$amount) {

			$physical_vouchers = get_posts(array(
				'post_type' => 'gc4t_store_voucher',
				'post_status' => 'private',
				'posts_per_page' => 1,
				'meta_query' => array(
					array(
						'key' => 'gc4t_voucher_status',
						'value' => 'active',
					),
					array(
						'key' => 'gc4t_voucher_code',
						'value' => $coupon_code,
					),
				)
			));
			if (isset($physical_vouchers[0])) {
				$amount = get_post_meta($physical_vouchers[0]->ID, 'gc4t_voucher_amount_remaining', true);
			}
			$total_price = sanitize_text_field($_POST['total_price']);
			$total_price = ($total_price) ? (int) $total_price : 0;
			if ($total_price) {
				$difference = $total_price - $amount;
				if ($difference < 0) {
					$amount = $total_price - 1;
				}
			}
		}

		if ($discount_type == 'percent') {
			$total_price = sanitize_text_field($_POST['total_price']);
			$total_price = ($total_price) ? $total_price : 0;
			if ($total_price) {
				$discount_amount = $total_price / 100 * $amount;
				$amount = sprintf('%01.2f', $discount_amount);
			}
		}
	}
	// echo $amount;
	wp_send_json_success(array('amount' => $amount, 'coupen' => $coupon_code));
	exit;
}
