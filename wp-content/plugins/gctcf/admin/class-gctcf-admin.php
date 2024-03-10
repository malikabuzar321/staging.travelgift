<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://cmitexperts.com/
 * @since      1.0.0
 *
 * @package    Gctcf
 * @subpackage Gctcf/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Gctcf
 * @subpackage Gctcf/admin
 * @author     CMITEXPERTS TEAM <cmitexperts@gmail.com>
 */
class Gctcf_Admin
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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Gctcf_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Gctcf_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/gctcf-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Gctcf_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Gctcf_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script('datatables-js', 'https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js', array('jquery'), $this->version, true);
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/gctcf-admin.js', array('jquery'), $this->version, true);
	}

	public function gc4t_woocommerce_thankyou($order_id)
	{
		$purchases = array();
		$order = wc_get_order($order_id);
		if ($order) {
			foreach ($order->get_items() as $item_id => $item) {
				$purchases[] = 'GiftCard' . ',' . $item->get_total();
			}
			$purchases = implode('|', $purchases); ?>
			<script language=JavaScript src="https://portgk.com/create-sale?client=java&MerchantID=2189&SaleID=<?php echo $order->get_id(); ?>&Purchases=<?php echo urlencode($purchases); ?>"></script>
			<noscript><img src="https://portgk.com/create-sale?client=img&MerchantID=2189&SaleID=<?php echo $order->get_id(); ?>&Purchases=<?php echo urlencode($purchases); ?>" width="10" height="10" border="0"></noscript>
		<?php
		}
	}

	public function gctcf_testimonial_add_meta_box($post_type)
	{
		add_meta_box(
			'testimonial_data',
			__('Testimonial Details', 'gctcf'),
			array($this, 'gctcf_testimonial_metabox_callback'),
			'gctcf_testimonials',
			'advanced',
			'high'
		);
	}

	public function gctcf_testimonial_metabox_callback($post)
	{
		$value = get_post_meta($post->ID, '_testimonial_designation', true);
		?>
		<div class="wrap">
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th><label for="testimonial_designation"><?php _e('Testimonial Designation', 'gctcf'); ?></label></th>
						<td>
							<input type="text" id="testimonial_designation" class="regular-text" name="testimonial_designation" value="<?php echo esc_attr($value); ?>" />
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	<?php
	}

	public function gctcf_testimonial_metabox_save($post_id)
	{
		if(isset($_POST['post_type'])):
		if ('gctcf_testimonials' == $_POST['post_type']) {
			if (!current_user_can('edit_page', $post_id)) {
				return $post_id;
			}
		} else {
			if (!current_user_can('edit_post', $post_id)) {
				return $post_id;
			}
		}
		endif;
		if(isset($_POST['testimonial_designation'])):
		$testimonial_designation = sanitize_text_field($_POST['testimonial_designation']);
		update_post_meta($post_id, '_testimonial_designation', $testimonial_designation);
		endif;
	}

	public function gctcf_testmonial_custom_column($columns)
	{
		unset($columns['date']);
		$columns['gctcf_designation'] = __('Designation', 'gctcf');
		$columns['date'] = __('Date', 'gctcf');

		return $columns;
	}

	public function gctcf_testimonial_column($column, $post_id)
	{
		switch ($column) {
			case 'gctcf_designation':
				echo get_post_meta($post_id, '_testimonial_designation', true);
				break;
		}
	}

	public function gctcf_add_option_page()
	{
		if (function_exists('acf_add_options_page')) {

			acf_add_options_page(array(
				'page_title' 	=> 'GC4T Settings',
				'menu_title'	=> 'GC4T Settings',
				'menu_slug' 	=> 'gc4t-general-settings',
				'capability'	=> 'edit_posts',
				'redirect'		=> false
			));
		}
	}

	public function gc4t_register_post_types()
	{
		$labels = array(
			'name' => _x('Store Vouchers', 'Post type general name', 'gctcf'),
			'singular_name' => _x('Store Voucher', 'Post type singular name', 'gctcf'),
			'menu_name' => _x('Store Vouchers', 'Admin Menu Text', 'gctcf'),
			'name_admin_bar' => _x('Store Voucher', 'Add New on Toolbar', 'gctcf'),
			'add_new' => __('Add New', 'gctcf'),
			'add_new_item' => __('Add New Store Voucher', 'gctcf'),
			'new_item' => __('New Store Voucher', 'gctcf'),
			'edit_item' => __('Edit Store Voucher', 'gctcf'),
			'view_item' => __('View Store Voucher', 'gctcf'),
			'all_items' => __('All Store Vouchers', 'gctcf'),
			'search_items' => __('Search Store Vouchers', 'gctcf'),
			'parent_item_colon' => __('Parent Store Voucher:', 'gctcf'),
			'not_found' => __('No Store Vouchers Found', 'gctcf'),
			'not_found_in_trash' => __('No Store Vouchers Found In Trash', 'gctcf'),
		);

		$args = array(
			'labels' => $labels,
			'public' => false,
			'public_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'rewrite' => array(
				'slug' => 'store-voucher',
			),
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array(
				'title',
				'author',
			)
		);

		register_post_type('gc4t_store_voucher', $args);

		$labels = array(
			'name' => _x('Hotel Bookings', 'Post type general name', 'gc4t'),
			'singular_name' => _x('Hotel Booking', 'Post type singular name', 'gc4t'),
			'menu_name' => _x('Hotel Bookings', 'Admin Menu Text', 'gc4t'),
			'name_admin_bar' => _x('Hotel Booking', 'Add New on Toolbar', 'gc4t'),
			'add_new' => __('Add New', 'gc4t'),
			'add_new_item' => __('Add New Booking', 'gc4t'),
			'new_item' => __('New Booking', 'gc4t'),
			'edit_item' => __('Edit Booking', 'gc4t'),
			'view_item' => __('View Booking', 'gc4t'),
			'all_items' => __('All Bookings', 'gc4t'),
			'search_items' => __('Search Bookings', 'gc4t'),
			'parent_item_colon' => __('Parent Booking:', 'gc4t'),
			'not_found' => __('No Bookings Found', 'gc4t'),
			'not_found_in_trash' => __('No Bookings Found In Trash', 'gc4t'),
		);

		$args = array(
			'labels' => $labels,
			'public' => false,
			'public_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'rewrite' => array(
				'slug' => 'hotel-bookings',
			),
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array(
				'title',
				'author',
				'editor',
				'thumbnail',
				'custom-fields',
			)
		);

		register_post_type('gc4t_hotel_booking', $args);
	}

	public function gc4t_add_submenu_pages()
	{
		global $byc_admin_settings_lateroom;

		add_submenu_page('edit.php?post_type=gc4t_store_voucher', 'Import Vouchers', 'Import Vouchers', 'manage_options', 'gc4t-import-store-vouchers', array($this, 'gc4t_import_store_vouchers_html'));


		add_menu_page('Hotel & Travel Setting', 'Hotel & Travel Setting', 'manage_options', 'byctravel_general_settings', array($this, 'byc_travel_admin_general_settings_form'));

		$byc_admin_settings_lateroom = add_submenu_page('byctravel_general_settings', 'LateRoom Bookings Details', 'LateRoom Bookings Details', 'manage_options', 'byc_lateroom_hotel_general_settings', array($this, 'byc_lateroom_hotel_general_settings_form'));
	}

	public function gc4t_import_store_vouchers_html()
	{
		if (isset($_POST['import_submit']) && $_POST['wc_product_id']) :
			if (isset($_FILES['import_file'])) :
				if (($_FILES['import_file']['type'] == 'text/csv') || (stripos($_FILES['import_file']['name'], 'csv') !== false)) :
					$dir = wp_get_upload_dir();
					$directory_path =  $dir['basedir'];
					$upload_file = $directory_path . '/voucher_import.csv';
					if (move_uploaded_file($_FILES['import_file']['tmp_name'], $upload_file)) :
						$handle = fopen($upload_file, 'r');
						if ($handle !== false) :
							$first = true;
							$wc_product_id = sanitize_text_field($_POST['wc_product_id']);
							$wc_product = wc_get_product($wc_product_id);
							if ($wc_product) :
								while (($data = fgetcsv($handle, 10000, ',')) !== false) :
									if (!$first) :
										$code = sanitize_text_field(str_replace('\'', '', $data[0]));
										$amount = sanitize_text_field($data[1]);
										if ($code && $amount) :
											$voucher_id = wp_insert_post(array(
												'post_type' => 'gc4t_store_voucher',
												'post_status' => 'private',
												'post_author' => get_current_user_id(),
												'post_title' => $code . ' -  ' . $amount,
											));
											if ($voucher_id) :
												update_post_meta($voucher_id, 'gc4t_voucher_status', 'available');
												update_post_meta($voucher_id, 'gc4t_voucher_amount', $amount);
												update_post_meta($voucher_id, 'gc4t_voucher_amount_remaining', $amount);
												update_post_meta($voucher_id, 'gc4t_voucher_code', $code);
												update_post_meta($voucher_id, 'gc4t_voucher_wc_product_id', $wc_product_id);
												update_post_meta($voucher_id, 'gc4t_voucher_wc_order_id', '');
											endif;
										endif;
									endif;
									$first = false;
								endwhile;
							endif;
							fclose($handle);
							@unlink($upload_file);
						endif;
					endif;
				endif;
			endif;
		endif;
	?>
		<form enctype="multipart/form-data" action="" method="post">
			<label for="wc_product_id">Product</label><br><br>
			<select name="wc_product_id">
				<option value="">Select product</option>
				<?php $products = get_posts(array(
					'post_type' => 'product',
					'post_status' => 'publish',
					'orderby' => 'title',
					'order' => 'asc',
				));
				if ($products) :
					foreach ($products as $product) :
						$wc_product = wc_get_product($product->ID);
						if ($wc_product) :
							if (!$wc_product->is_virtual()) : ?>
								<option value="<?php echo $product->ID; ?>">Gift Card - &pound;<?php echo $wc_product->get_price(); ?></option>
						<?php endif;
						endif; ?>
				<?php
					endforeach;
				endif; ?>
			</select><br><br>
			<label for="import_file">Import File (CSV) - 10 MB Max</label><br><br>
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo 1024 * 1024 * 10; ?>">
			<input type="file" id="import_file" name="import_file">
			<br><br>
			<input type="submit" class="button" name="import_submit" value="Import">
		</form>
	<?php
	}

	public function gc4t_send_store_voucher($order_id)
	{
		$order = new WC_Order($order_id);
		$items = $order->get_items();
		foreach ($items as $item) {
			$wc_product = wc_get_product($item['product_id']);
			if ($wc_product) {
				file_put_contents(dirname(__FILE__) . '/test.txt', print_r($wc_product, true));
				$product_variation_id = $item->get_variation_id();
				$variation = new WC_Product_Variation($product_variation_id);
				if ($variation) {
					if (!$variation->is_virtual()) {
						$vouchers = get_posts(array(
							'post_type' => 'gc4t_store_voucher',
							'post_status' => 'private',
							'orderby' => 'rand',
							'posts_per_page' => $item['quantity'],
							'meta_query' => array(
								array(
									'key' => 'gc4t_voucher_status',
									'value' => 'available',
								),
								array(
									'key' => 'gc4t_voucher_wc_product_id',
									'value' => $item['product_id'],
								)
							)
						));
						if ($vouchers) {
							$codes = array();
							foreach ($vouchers as $voucher) {
								update_post_meta($voucher->ID, 'gc4t_voucher_status', 'pending');
								update_post_meta($voucher->ID, 'gc4t_voucher_wc_order_id', $order_id);
								$codes[] = get_post_meta($voucher->ID, 'gc4t_voucher_code', true);
							}

							$content = "Physical Card Purchased:\r\n\Codes:r\n";
							foreach ($codes as $code) {
								$content .= $code . "\r\n";
							}
							$content .= "\r\nOrder ID: $order_id";

							wp_mail('rob@giftcards4travel.co.uk', 'Physical Card Purchased - ' . $order_id, $content);
						}
					}
				}
			}
		}
	}

	public function gc4t_cancel_store_voucher($order_id)
	{
		$vouchers = get_posts(array(
			'post_type' => 'gc4t_store_voucher',
			'post_status' => 'private',
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => 'gc4t_voucher_wc_order_id',
					'value' => $order_id,
				)
			)
		));
		if ($vouchers) {
			foreach ($vouchers as $voucher) {
				update_post_meta($voucher->ID, 'gc4t_voucher_status', 'available');
				update_post_meta($voucher->ID, 'gc4t_voucher_wc_order_id', '');
			}
		}
	}

	public function gc4t_activate_store_voucher($order_id)
	{
		$vouchers = get_posts(array(
			'post_type' => 'gc4t_store_voucher',
			'post_status' => 'private',
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => 'gc4t_voucher_wc_order_id',
					'value' => $order_id,
				),
				array(
					'key' => 'gc4t_voucher_status',
					'value' => 'pending',
				)
			)
		));
		if ($vouchers) {
			foreach ($vouchers as $voucher) {
				update_post_meta($voucher->ID, 'gc4t_voucher_status', 'active');
			}
		}
	}

	public function gc4t_pause_store_voucher($order_id)
	{
		$vouchers = get_posts(array(
			'post_type' => 'gc4t_store_voucher',
			'post_status' => 'private',
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => 'gc4t_voucher_wc_order_id',
					'value' => $order_id,
				),
				array(
					'key' => 'gc4t_voucher_status',
					'value' => 'active',
				)
			)
		));
		if ($vouchers) {
			foreach ($vouchers as $voucher) {
				update_post_meta($voucher->ID, 'gc4t_voucher_status', 'pending');
			}
		}
	}

	public function byc_travel_admin_general_settings_form()
	{
	?>
		<div class="wrap">
			<h1>Travel Profit Setting</h1>
			<form method="post" class="form_theme_panal" action="options.php">
				<?php
				settings_fields("byctravelthemeoptions");
				do_settings_sections("byc-travel-theme-options");
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function byc_lateroom_hotel_general_settings_form()
	{

		global $wpdb;
		$sql_payment_info = "SELECT * FROM `user_login_table_for_lateroom` WHERE `payment_status`='Confirm' ORDER BY id DESC";
		$payment_info_result = $wpdb->get_results($sql_payment_info);
		$count = 1;

		foreach ($payment_info_result as $payment_info_result_val) {

			$booking_unick_code = $payment_info_result_val->booking_unick_code;
			$payment_status = $payment_info_result_val->payment_status;
			$transaction_id = $payment_info_result_val->transaction_id;
			$user_first_name = $payment_info_result_val->user_first_name;
			$user_last_name = $payment_info_result_val->user_last_name;
			$user_email = $payment_info_result_val->user_email;
			$user_phonos = $payment_info_result_val->user_phonos;
			$hotel_name = $payment_info_result_val->hotel_name;
			$no_of_night = $payment_info_result_val->no_of_night;
			$hotel_price = $payment_info_result_val->total_price;
			$arriving_date = $payment_info_result_val->arriving_date;
			$hotel_id = $payment_info_result_val->hotel_id;
			$booking_date = $payment_info_result_val->user_booking_date;

		?>
			<div class="Booking" style=" border:1px solid #ffa500;display: inline-block; margin-bottom:10px; padding:10px;">
				<h3>Booking: <?php echo $count; ?>&nbsp;(Booking Date:<?php echo $booking_date; ?>)</h3>
				<ul>
					<li style="width:25%; float:left;"><?php echo 'Hotel Name :' . $hotel_name; ?></li>
					<li style="width:25%; float:left;"><?php echo 'Hotel Id : ' . $hotel_id; ?></li>
					<li style="width:25%; float:left;"><?php echo 'Booking Id : ' . $booking_unick_code; ?></li>
					<li style="width:25%; float:left;"><?php echo 'Payment status : ' . $payment_status; ?></li>
					<li style="width:25%; float:left;"><?php echo 'Total price : ' . $hotel_price; ?></li>
					<li style="width:25%; float:left;"><?php echo 'Transaction Id : ' . $transaction_id; ?></li>
					<li style="width:25%; float:left;"><?php echo 'Arriving Date : ' . $arriving_date; ?></li>
					<li style="width:25%; float:left;"><?php echo 'No of night : ' . $no_of_night; ?></li>
				</ul>

				<?php

				$byc_room_gust_sql = "SELECT * FROM `booking_detials_for_lateroom` WHERE `booking_unick_code`='" . $booking_unick_code . "'";
				$byc_room_gust_details = $wpdb->get_results($byc_room_gust_sql);
				$room_count = 1;
				foreach ($byc_room_gust_details as $byc_room_gust_details_val) {
				?>

					<div class="" style="margin-bottom:15px; width:50%; float:left;">

						<div class="confirm_room" style="border: 1px solid #673AB7; padding: 10px;">
							<h3>Room <?php echo $room_count; ?></h3>
							<h5>Room Name :&nbsp;<?php echo $byc_room_gust_details_val->room_name; ?></h5>

							<p>Guest Name :&nbsp;<?php echo $byc_room_gust_details_val->guest_title; ?>&nbsp;<?php echo $byc_room_gust_details_val->guest_first_name; ?>&nbsp;<?php echo $byc_room_gust_details_val->guest_last_name; ?></p>

							<p>Guest Email:&nbsp;<?php echo $byc_room_gust_details_val->guest_email ?></p>

							<p>Cancellation Date:&nbsp;<?php echo $byc_room_gust_details_val->cancellation_date ?></p>


						</div>

					</div>
				<?php
					$room_count++;
				}
				$count++;
				?>
			</div>
		<?php

		}
	}

	public function byc_travel_price_add_by_site()
	{ ?>

		<input type="text" name="travel_hotel_booking_price_by_parcentage" id="travel_hotel_booking_price_by_parcentage" value="<?php echo get_option('travel_hotel_booking_price_by_parcentage') ?>" />%


	<?php
	}


	public function Byc_travels_themes_plugin_settings_fields()
	{
		add_settings_section("byctravelthemeoptions", "All Settings", null, "byc-travel-theme-options");

		add_settings_field("travel_hotel_booking_price_by_parcentage", "Additional hotel price : ", array($this, "byc_travel_price_add_by_site"), "byc-travel-theme-options", "byctravelthemeoptions");


		register_setting("byctravelthemeoptions", "travel_hotel_booking_price_by_parcentage");
	}

	/**
	 * Add options page
	 */
	public function gctcf_attraction_menu()
	{
		// This page will be under "Settings"
		add_options_page(
			'Attraction API Settings',
			'Attraction API Settings',
			'manage_options',
			'attraction-api-setting-admin',
			array($this, 'create_admin_page')
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page()
	{
		// Set class property
		$this->options = get_option('attraction_api_option');
	?>
		<div class="wrap">
			<h1>Attraction API Configurations</h1>
			<form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields('attraction_option_group');
				do_settings_sections('attraction-api-setting-admin');
				submit_button();
				?>
			</form>
		</div>
	<?php
	}

	/**
	 * Register and add settings
	 */
	public function gctcf_attraction_page_init()
	{
		register_setting(
			'attraction_option_group', // Option group
			'attraction_api_option', // Option name
			array($this, 'sanitize') // Sanitize
		);

		add_settings_section(
			'setting_section_id',
			'Attraction API Settings',
			array($this, 'print_section_info'),
			'attraction-api-setting-admin'
		);

		add_settings_field(
			'attraction_api_url',
			'API Username',
			array($this, 'attraction_api_url_callback'),
			'attraction-api-setting-admin',
			'setting_section_id'
		);

		add_settings_field(
			'attraction_api_username',
			'API Username',
			array($this, 'attraction_api_username_callback'),
			'attraction-api-setting-admin',
			'setting_section_id'
		);

		add_settings_field(
			'attraction_api_password',
			'API Password',
			array($this, 'attraction_api_password_callback'),
			'attraction-api-setting-admin',
			'setting_section_id'
		);


		add_settings_section(
			'synchronized_section_id',
			'Attraction API Synchronization',
			array($this, 'synchronized_section_info'),
			'attraction-api-setting-admin'
		);
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize($input)
	{
		$new_input = array();

		if (isset($input['attraction_api_url']))
			$new_input['attraction_api_url'] = sanitize_text_field($input['attraction_api_url']);

		if (isset($input['attraction_api_username']))
			$new_input['attraction_api_username'] = sanitize_text_field($input['attraction_api_username']);

		if (isset($input['attraction_api_password']))
			$new_input['attraction_api_password'] = sanitize_text_field($input['attraction_api_password']);

		return $new_input;
	}

	/** 
	 * Print the Section text
	 */
	public function print_section_info()
	{
		print 'Enter your API details below:';
	}

	public function synchronized_section_info()
	{

		$menu_page_url = menu_page_url('attraction-api-setting-admin', false);

		$synchronize_page_url = add_query_arg('synchronize', 'start', $menu_page_url);

		$reset_page_url = add_query_arg('reset', 'yes', $menu_page_url);

		$get_destination = get_option('_attraction_api_destination_count');
		$get_activities = get_option('_attraction_api_activities_count');
		$get_dosomething = get_option('_attraction_api_dosomething_count');

		print '<style>
	    a.button.button-primary.btn-synchronize {
	        background: #2a9b15;
	        border-color: #2a9b15;
	    }
	    
	    a.button.button-primary.reset-synchronize {
	        background: #e50927;
	        border-color: #e50927;
	    }
	    </style>';

		print '<p class="description">DESTINATIONS - <strong>' . $get_destination . '</strong> | ACTIVITIES - <strong>' . $get_activities . '</strong> | DO SOMETHING - <strong>' . $get_dosomething . '</strong></p>';

		print '<br/ >Please click on Synchronize icon to get updated API results in to databasse.';

		print '&nbsp; &nbsp; <a class="button button-primary btn-synchronize" href="' . $synchronize_page_url . '">Synchronize <span class="dashicons dashicons-update-alt" style="margin-top: 4px; margin-left: 5px;"></span></a>';

		print '&nbsp; &nbsp; <a class="button button-primary reset-synchronize" href="' . $reset_page_url . '">Reset <span class="dashicons dashicons-dismiss" style="margin-top: 4px; margin-left: 5px;"></span></a>';
	}

	/** 
	 * Get the settings option array and print one of its values
	 */
	public function attraction_api_url_callback()
	{
		printf(
			'<input type="text" id="attraction_api_url" name="attraction_api_option[attraction_api_url]" value="%s" />',
			isset($this->options['attraction_api_url']) ? esc_attr($this->options['attraction_api_url']) : ''
		);
	}

	/** 
	 * Get the settings option array and print one of its values
	 */
	public function attraction_api_username_callback()
	{
		printf(
			'<input type="text" id="attraction_api_username" name="attraction_api_option[attraction_api_username]" value="%s" />',
			isset($this->options['attraction_api_username']) ? esc_attr($this->options['attraction_api_username']) : ''
		);
	}

	/** 
	 * Get the settings option array and print one of its values
	 */
	public function attraction_api_password_callback()
	{
		printf(
			'<input type="text" id="attraction_api_password" name="attraction_api_option[attraction_api_password]" value="%s" />',
			isset($this->options['attraction_api_password']) ? esc_attr($this->options['attraction_api_password']) : ''
		);
	}

	public function attraction_api_admin_fun_callback()
	{
		if (isset($_GET['page']) && $_GET['page'] == 'attraction-api-setting-admin' && isset($_GET['synchronize'])) {
			$this->get_destinations_data(); // CALL FUNCTION TO STORE API REPONSE TO DATABASE
		}
		if (isset($_GET['page']) && $_GET['page'] == 'attraction-api-setting-admin' && isset($_GET['reset'])) {
			$this->truncate_activities_dosomething_data();
		}
	}

	public function attraction_admin_notice()
	{
		global $pagenow;

		if ($pagenow == 'options-general.php' && (isset($_GET['page']) && $_GET['page'] == 'attraction-api-setting-admin') && (isset($_GET['reset']) && $_GET['reset'] == 'yes')) {
			echo '<div class="notice notice-success is-dismissible">
	             <p>All Destinations, Activities and Do something listing are empty now.</p>
	         </div>';
		}

		if ($pagenow == 'options-general.php' && (isset($_GET['page']) && $_GET['page'] == 'attraction-api-setting-admin') && (isset($_GET['synchronize']) && $_GET['synchronize'] == 'start')) {
			echo '<div class="notice notice-success is-dismissible">
				<p>All Destinations, Activities and Do something listing are up to date now with API.</p>
			</div>';
		}
	}

	public function truncate_activities_dosomething_data()
	{
		//TRUNCATE TABLE FIRST
		global $wpdb;
		$wpdb->query('TRUNCATE TABLE ' . $wpdb->prefix . 'attraction_destinations');
		$wpdb->query('TRUNCATE TABLE ' . $wpdb->prefix . 'attraction_tags');
		delete_option('_attraction_api_destination_count');
		delete_option('_attraction_api_activities_count');
		delete_option('_attraction_api_dosomething_count');
	}




	public function get_activities_dosomething_data($category = 27)
	{
		$api_url = 'https://phx.dosomethingdifferent.com/api/tags?category=' . $category;

		$response = wp_remote_get(
			esc_url_raw($api_url),
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization'	=> 'Basic ' . base64_encode("DD607:tickets")
				)
			)
		);

		$responseBody = wp_remote_retrieve_body($response);

		$result = json_decode($responseBody);

		if (!empty($result)) {

			global $wpdb;


			if ($category == 27) {

				update_option('_attraction_api_activities_count', $result->meta->total_count);
			}

			if ($category == 24) {

				update_option('_attraction_api_dosomething_count', $result->meta->total_count);
			}


			if (isset($result->data) && !empty($result->data)) {

				foreach ($result->data as $key => $value) {

					$tags_array = array();
					$tags_array['Tag_ID'] = $value->id;
					$tags_array['Tag'] = $value->tag;
					$tags_array['CategoryID'] = $value->category_id;
					$tags_array['Category'] = $value->category;
					$tags_array['ParentID'] = $value->parent_id;
					$tags_array['Parent'] = $value->parent;

					$wpdb->insert($wpdb->prefix . 'attraction_tags', $tags_array);
				}
			}
		}
	}

	public function get_destinations_data()
	{

		$this->truncate_activities_dosomething_data(); // TRUNCATE TABLES 
		$this->get_activities_dosomething_data(24); // //DO SOMETHING TAGS
		$this->get_activities_dosomething_data(27); //ACTIVITIES TAGS

		$api_url = 'https://phx.dosomethingdifferent.com/api/destinations';
		$response = wp_remote_get(
			esc_url_raw($api_url),
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization'	=> 'Basic ' . base64_encode("DD607:tickets")
				)
			)
		);
		$responseBody = wp_remote_retrieve_body($response);
		$result = json_decode($responseBody);
		if (!empty($result)) {

			global $wpdb;

			update_option('_attraction_api_destination_count', $result->meta->total_count);


			if (isset($result->data) && !empty($result->data)) {

				foreach ($result->data as $key => $value) {

					$dest_array = array();
					$dest_array['Dest_ID'] = $value->id;
					$dest_array['Title'] = $value->title;
					$dest_array['Updated'] = $value->updated;
					$dest_array['Parent_id'] = $value->parent_id;
					$dest_array['Img_sml'] = $value->img_sml;
					$dest_array['Description'] = $value->desc;

					$wpdb->insert($wpdb->prefix . 'attraction_destinations', $dest_array);
				}
			}
		}
	}

	public function gctcf_admin_hooks()
	{
		add_filter('post_row_actions', array($this, 'gctcf_list_row_actions'), 10, 2);
		add_action('add_meta_boxes', array($this, 'gctcf_attraction_booking_meta_box'));

		add_filter('manage_attraction_booking_posts_columns', array($this, 'gctcf_attraction_booking_columns'));
		add_action('manage_attraction_booking_posts_custom_column', array($this, 'gctcf_attraction_booking_column'), 10, 2);
	}

	public function gctcf_list_row_actions($actions, $post)
	{
		if ($post->post_type == "attraction_booking") {
			unset($actions['view']);
		}
		return $actions;
	}

	public function gctcf_attraction_booking_meta_box()
	{

		add_meta_box(
			'booking-data',
			__('Booking data', 'gctcf'),
			array($this, 'attraction_booking_meta_box_callback'),
			'attraction_booking'
		);
		add_meta_box(
			'transfer-data',
			__('Booking data', 'gctcf'),
			array($this, 'transfer_booking_meta_box_callback'),
			'transfer_booking'
		);
		add_meta_box(
			'hotel-data',
			__('Booking data', 'gctcf'),
			array($this, 'hotel_booking_meta_box_callback'),
			'gc4t_hotel_booking'
		);
		add_meta_box(
			'gctcf-order-data',
			__('Gift card data', 'gctcf'),
			array($this, 'shop_order_meta_box_callback'),
			'shop_order',
			'side'
		);
	}

	public function shop_order_meta_box_callback($post)
	{
		$reference = get_post_meta($post->ID, 'reference', true);
		$send_status = get_post_meta($post->ID, 'gctf_send_status', true);
		$method = get_post_meta($post->ID, 'method', true);
		$first_name = get_post_meta($post->ID, 'byconsole_giftcard_first_name', true);
		$last_name = get_post_meta($post->ID, 'byconsole_giftcard_last_name', true);
		$email = get_post_meta($post->ID, 'byconsole_giftcard_email', true);
		$code = get_post_meta($post->ID, 'user_giftcard_code', true);
		$message = get_post_meta($post->ID, 'byconsole_giftcard_message', true);
		$amount = get_post_meta($post->ID, 'byconsole_giftcard_amount', true);
	?>
		<div class="wrap">
			<table class="form-table" role="presentation">
			<tr>
					<td><label>First name:</label></td>
					<td><?php echo $first_name; ?></td>
				</tr>
				<tr>
					<td><label>Last name:</label></td>
					<td><?php echo $last_name; ?></td>
				</tr>
				<tr>
					<td><label>Email:</label></td>
					<td><?php echo $email; ?></td>
				</tr>
				<tr>
					<td><label>Code:</label></td>
					<td><?php echo $code; ?></td>
				</tr>
				<tr>
					<td><label>Message:</label></td>
					<td><?php echo $message; ?></td>
				</tr>
				<tr>
					<td><label>Amount:</label></td>
					<td><?php echo sprintf('%01.2f', $amount); ?></td>
				</tr>
				<tr>
					<td><label>Reference:</label></td>
					<td><?php echo $reference; ?></td>
				</tr>
				<tr>
					<td><label>Send status:</label></td>
					<td><?php echo $send_status; ?></td>
				</tr>
				<tr>
					<td><label>Purchase Method:</label></td>
					<td><?php echo $method; ?></td>
				</tr>
			</table>
		</div>
	<?php
	}

	public function attraction_booking_meta_box_callback($post)
	{

		$post_id = $post->ID;

		// pre(get_post_meta($post_id));

		$pay_st = get_post_meta($post_id, '_gctcf_payment_status', true);
		$book_pay_st = get_post_meta($post_id, '_gctcf_payment_status', true);
		$payment_status = !empty($book_pay_st) ? $book_pay_st : $pay_st;

		$booking_id = get_post_meta($post_id, '_gctcf_booking_id', true);

		$billing_data = get_post_meta($post_id, '_gctcf_billing_info', true);
		$address = !empty($billing_data['billing_address2']) ? $billing_data['billing_address1'] . ', ' . $billing_data['billing_address2'] : $billing_data['billing_address1'];

		$travel_data = get_post_meta($post_id, '_gctcf_travel_info', true);
		$passenger_name = get_post_meta($post_id, '_gctcf_passenger_name', true);

		$attraction_info = get_post_meta($post_id, '_gctcf_ticket_data', true);
		$attractionticket_info = json_decode($attraction_info, true);

		$booking_date = !empty($attractionticket_info['select_time']) ? $attractionticket_info['select_date'] . ', ' . $attractionticket_info['select_time'] : $attractionticket_info['select_date'];

		$html .= '<div class="wrap">
					<table class="form-table" role="presentation">
    					<thead>
						    <th>(#' . $booking_id . ')' . $attractionticket_info['product_name'] . '</th>
						    <th><span>£' . $attractionticket_info['total-price'] . '</span></th>
						</thead>
						<tbody>
							<tr>
								<th>Booking date : </th>
								<td>' . $booking_date . '</td>
							</tr>
							<tr>
								<th>Tickets</th>
								<td>';
		foreach ($attractionticket_info['tickets'] as $key => $value) {

			if ($value['ticket-qty'] > 0) {

				$html .= '<p>' . $value['ticket-qty'] . ' x ' . $value['type'] . '</p>';
			}
		}
		$html .= '</td>
							</tr>
							<tr>
								<th>Lead passenger name</th>
								<td>' . $travel_data['lead_passenger_name'] . '</td>
							</tr>
							<tr>
								<th>Passenger\'s Name</th>
								<td>';
		if (!empty($passenger_name)) {
			foreach ($passenger_name as $key => $value) {
				foreach ($value as  $pass_key => $name) {
					foreach ($name as $pass_name) {

						if ($pass_key == "passenger_name") {
							echo $pass_name;
							$html .= '<p>' . $pass_name . '</p>';
						}
					}
				}
			}
		}
		$html .= '</td>
							</tr>
							<tr>
								<th>Departure date</th>
								<td>' . $travel_data['depature_date'] . '</td>
							</tr>
							<tr>
								<th>Payment status</th>
								<td><strong>' . $payment_status . '</strong></td>
							</tr>
							<tr>
								<th>Billing Address</th>
								<td>
									<p>' . $billing_data['billing_fullname'] . '</p>
					              	<p>' . $billing_data['billing_phoneNumber'] . '</p>
					              	<p>' . $address . '</p>
					              	<p>' . $billing_data['billing_city'] . ', ' . $billing_data['billing_state'] . ', ' . $billing_data['billing_country'] . '</p>
					              	<p>' . $billing_data['billing_pincode'] . '</p>
								</td>
							</tr>
						</tbody>
					</table>
				  </div>';

		echo $html;
	}

	public function gctcf_attraction_booking_columns($columns)
	{
		unset($columns['date']);
		$columns['booking_id'] = __('Booking id', 'gctcf');
		$columns['date'] = __('Date', 'gctcf');

		return $columns;
	}

	public function gctcf_attraction_booking_column($column, $post_id)
	{
		switch ($column) {

			case 'booking_id':
				echo get_post_meta($post_id, '_gctcf_booking_id', true);
		}
	}

	public function transfer_booking_meta_box_callback($post)
	{
		$post_id = $post->ID;
		// pre(get_post_meta($post_id)); 
		$title = get_post_meta($post_id, 'gc4t_title_by_name', true);
		$first_name = get_post_meta($post_id, 'gc4t_client_first_name', true);
		$last_name = get_post_meta($post_id, 'gc4t_client_last_name', true);
		$email = get_post_meta($post_id, 'gc4t_client_email', true);
		$phone = get_post_meta($post_id, 'gc4t_client_phone_no', true);
		$phone1 = get_post_meta($post_id, 'gc4t_client_mobile_no', true);
		$property_name = get_post_meta($post_id, 'gc4t_client_property_name', true);
		$address1 = get_post_meta($post_id, 'gc4t_client_address_1', true);
		$address2 = get_post_meta($post_id, 'gc4t_client_address_2', true);
		$dep_point = get_post_meta($post_id, 'gc4t_client_dept_point', true);
		$dep_info = get_post_meta($post_id, 'gc4t_client_dep_info', true);
		$dep_ext_info = get_post_meta($post_id, 'gc4t_client_dep_ext_info', true);
		$txn_no = get_post_meta($post_id, 'gc4t_client_trans_no', true);
		$send_email = get_post_meta($post_id, 'gc4t_email_send_to_customer', true);
		$price = get_post_meta($post_id, 'gc4t_transfer_price', true);
		$user_email = ($send_email == 0) ? 'No' : 'Yes';
	?>
		<div class="wrap">
			<table class="form-table" role="presentation">
				<thead>
					<th>Full name</th>
					<th><span><?= $title . ' ' . $first_name . ' ' . $last_name; ?></span></th>
				</thead>
				<tbody>
					<tr>
						<th>Price : </th>
						<td><?= $price; ?></td>
					</tr>
					<tr>
						<th>Email : </th>
						<td><?= $email ?></td>
					</tr>
					<tr>
						<th>Phone : </th>
						<td>
							<p><?= $phone; ?>, <?= $phone1; ?></p>
						</td>
					</tr>
					<tr>
						<th>Address : </th>
						<td>
							<p><?= $property_name; ?></p>
							<p><?= $address1; ?></p>
							<p><?= $address2; ?></p>
						</td>
					</tr>
					<tr>
						<th>Departure point : </th>
						<td><?= $dep_point; ?></td>
					</tr>
					<tr>
						<th>Departure info : </th>
						<td><?= $dep_info; ?></td>
					</tr>
					<tr>
						<th>Transaction id : </th>
						<td><?= $txn_no; ?></td>
					</tr>
					<tr>
						<th>Confirmation email send to user : </th>
						<td><?= $user_email; ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	<?php
	}
	public function hotel_booking_meta_box_callback($post)
	{
		$post_id = $post->ID;
		//pre(get_post_meta($post_id));
		$booking_data = json_decode(get_post_meta($post_id, 'gc4t_booking_details', true));
		$coupon_amt = get_post_meta($post_id, '_gctcf_coupon_amount', true);
        $coupon_code = get_post_meta($post_id, '_gctcf_coupon_code', true);
                
		if(!is_array($booking_data))
		{
			$booking_data = array($booking_data);
		}
		//pre($booking_data, 1);
		$feed_type = get_post_meta($post_id, 'gc4t_feed_type', true);
		if($feed_type == 'travellanda')
		{
			echo gctcf_get_travellanda_booking_details($post);
		}
		else
		{
			echo gctcf_get_stuba_booking_details($post);
		}
	//pre($booking_data);
	}
}


function gctcf_get_stuba_booking_details($post)
{
	$post_id = $post->ID;
	$booking_data = json_decode(get_post_meta($post_id, 'gc4t_booking_details', true));
	$coupon_amt = get_post_meta($post_id, '_gctcf_coupon_amount', true);
    $coupon_code = get_post_meta($post_id, '_gctcf_coupon_code', true);
            
	if(!is_array($booking_data))
	{
		$booking_data = array($booking_data);
	}
	?>

	<div class="wrap">
		<table class="form-table" role="presentation">
			<tbody>
	<?php 
		foreach ($booking_data as $hotel_value) { 
			$hotel_price = $hotel_value->TotalSellingPrice;
            $hotel_price = xml_attribute($hotel_price, 'amt');
            $percentage = get_option('travel_hotel_booking_price_by_parcentage');
            $percentage_total = ($percentage / 100) * $hotel_price;
            $hotel_total_price = ($percentage_total + $hotel_price);
            $hotel_room_type_code = $hotel_value->Room->RoomType;
            $hotel_room_type_code_view = xml_attribute($hotel_room_type_code, 'code');
            $hotel_room_type_name = $hotel_value->Room->RoomType;
            $hotel_room_name = xml_attribute($hotel_room_type_name, 'text');
            $hotel_meal_type = $hotel_value->Room->MealType;
            $hotel_meal_type_view = xml_attribute($hotel_meal_type, 'text');

            $adult_guest_list = $hotel_value->Room->Guests->Adult;
            if(!is_array($adult_guest_list) && $adult_guest_list)
			{
				$adult_guest_list = array($adult_guest_list);
			}
            $child_guest_list = $hotel_value->Room->Guests->Child;
            if(!is_array($child_guest_list) && $child_guest_list)
			{
				$child_guest_list = array($child_guest_list);
			}
			?>
			<tr>
				<th>Reference: </th>
				<td><?= $hotel_value->Id; ?></td>
			</tr>
			<tr>
				<th>Hotel Name: </th>
				<td><?= $hotel_value->HotelName; ?></td>
			</tr>
			<tr>
				<th>Arrival Date: </th>
				<td><?= $hotel_value->ArrivalDate; ?></td>
			</tr>
			<tr>
				<th>Nights: </th>
				<td><?= $hotel_value->Nights; ?></td>
			</tr>

			<tr>
				<th>Coupon: </th>
				<td><?= $coupon_code; ?></td>
			</tr>
			<tr>
				<th>Discount: </th>
				<td><?= $coupon_amt; ?></td>
			</tr>
			<tr>
				<th>Price: </th>
				<td><?= $hotel_total_price; ?></td>
			</tr>
			<tr>
				<th>Room Code: </th>
				<td><?= $hotel_room_type_code_view; ?></td>
			</tr>
			<tr>
				<th>Room Name: </th>
				<td><?= $hotel_room_name; ?></td>
			</tr>
			<tr>
				<th>Meal Type: </th>
				<td><?= $hotel_meal_type_view; ?></td>
			</tr>
			<?php if($adult_guest_list): ?>
					<tr>
						<th>Adults: </th>
					<?php foreach ($adult_guest_list as $adult) {
							$adult_title = xml_attribute($adult, 'title');
	                        $adult_first_nmae = xml_attribute($adult, 'first');
	                        $adult_last_nmae = xml_attribute($adult, 'last');
					 ?>
						<td><?php echo $adult_str_guest = $adult_title . ' ' . $adult_first_nmae . '  ' . $adult_last_nmae; ?></td>
					<?php } ?>
			<?php endif; ?>

			<?php if($child_guest_list): ?>
					<tr>
						<th>Childs: </th>
					<?php foreach ($child_guest_list as $childs) {
							$child_title = xml_attribute($childs, 'title');
	                        $child_first_name = xml_attribute($childs, 'first');
	                        $child_last_name = xml_attribute($childs, 'last');
					 ?>
						<td><?php echo $child_str_guest = $child_title . ' ' . $child_first_name . '  ' . $child_last_name; ?></td>
					<?php } ?>
			<?php endif; ?>
				
	<?php } ?>
			</tbody>
		</table>
	</div>
<?php
}

function gctcf_get_travellanda_booking_details($post)
{
	$post_id = $post->ID;
	$booking_data = json_decode(get_post_meta($post_id, 'gc4t_booking_details', true));
	$coupon_amt = get_post_meta($post_id, 'gc4t_discount_amount', true);
    $coupon_code = get_post_meta($post_id, 'gc4t_discount_code', true);
            
	if(!is_array($booking_data))
	{
		$booking_data = array($booking_data);
	}
	?>

	<div class="wrap">
		<table class="form-table" role="presentation">
			<tbody>
	<?php

		$hotel_price = get_post_meta($post_id, 'gc4t_price', true);
		$percentage = get_post_meta($post_id, 'gc4t_percentage', true);
		$percentage_total = ($percentage / 100) * $hotel_price;
      	$hotel_total_price      = round($percentage_total + $hotel_price, 2);
      	$ref = get_post_meta($post_id, 'gc4t_booking_id', true);
      	//pre($booking_data);
		foreach ($booking_data as $hotel_value) { 
			
            $hotel_room_type_code = $hotel_value->Room->RoomType;
            $hotel_room_type_code_view = xml_attribute($hotel_room_type_code, 'code');
            $hotel_room_type_name = $hotel_value->Room->RoomType;
            $hotel_room_name = xml_attribute($hotel_room_type_name, 'text');
            
            $rooms = $hotel_value->rooms;
            $adults = $hotel_value->adults;
            $childs = $hotel_value->children;
			?>
			<tr>
				<th>Reference: </th>
				<td><?= $ref; ?></td>
			</tr>
			<tr>
				<th>Hotel Name: </th>
				<td><?= $hotel_value->hotel_name; ?></td>
			</tr>
			<tr>
				<th>Arrival Date: </th>
				<td><?= $hotel_value->check_in_date; ?></td>
			</tr>
			<tr>
				<th>Nights: </th>
				<td><?= $hotel_value->nights; ?></td>
			</tr>

			<tr>
				<th>Coupon: </th>
				<td><?= $coupon_code; ?></td>
			</tr>
			<tr>
				<th>Discount: </th>
				<td><?= $coupon_amt; ?></td>
			</tr>
			<tr>
				<th>Price: </th>
				<td><?= $hotel_total_price; ?></td>
			</tr>
			<?php if($rooms):
					$i = 1;
			 ?>
					<tr>
						<th>Room <?= $i; ?>: </th>
						<td><table>
							
						
			<?php
					foreach ($rooms as $room)
					{
			?>
						<tr>							
							<th>Room code : </th>
							<td><?= $room->room_code; ?></td>
						</tr>
						<tr>							
							<th>Room name : </th>
							<td><?= $room->room_name; ?></td>
						</tr>
						<tr>							
							<th>Room meal : </th>
							<td><?= $room->room_meal; ?></td>
						</tr>
						<?php if($room->adults): ?>
							<tr>							
								<th>Adults : </th>
								<td>
							<?php foreach ($room->adults as $adult) { ?>
									<div class="rooms-data">
									<?= $adult->title.' '.$adult->first_name.' '.$adult->last_name; ?>
									</div>
								<?php } ?>	
							</td></tr>
						<?php endif; ?>
						<?php if($room->children): ?>
							<tr>							
								<th>Childrens : </th>
								<td>
							<?php foreach ($room->children as $children) { ?>
									<div class="rooms-data">
									<?= $children->title.' '.$children->first_name.' '.$children->last_name; ?>
									</div>
								<?php } ?>
								</td>	
							</tr>
						<?php endif; ?>
					<?php
						$i++;
						 }
					?>
						</table></td>
					</tr>
			<?php endif; ?>

				
	<?php } ?>
			</tbody>
		</table>
	</div>
<?php
}