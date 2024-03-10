<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.giftcards4travel.co.uk/
 * @since      1.0.0
 *
 * @package    Discover_Cars_Api
 * @subpackage Discover_Cars_Api/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Discover_Cars_Api
 * @subpackage Discover_Cars_Api/admin
 * @author     Giftcards4travel <rob@giftcards4travel.co.uk>
 */
class Discover_Cars_Api_Admin
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
		 * defined in Discover_Cars_Api_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Discover_Cars_Api_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/discover-cars-api-admin.css', array(), $this->version, 'all');
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
		 * defined in Discover_Cars_Api_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Discover_Cars_Api_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/discover-cars-api-admin.js', array('jquery'), $this->version, false);
	}

	/**
	 * Register submenu under settings menu.
	 *
	 * @since    1.0.0
	 */
	public function dca_add_plugin_page()
	{
		add_submenu_page(
			'options-general.php',
			__('Discover cars', 'discover-cars-api'),
			__('Discover cars', 'discover-cars-api'),
			'manage_options',
			'dca-settings',
			array($this, 'dca_admin_settings_page')
		);
	}

	/**
	 * Callback function for submenu.
	 *
	 * @since    1.0.0
	 */
	public function dca_admin_settings_page()
	{ ?>
		<div class="wrap">
			<h1><?php _e('Discover cars', 'discover-cars-api'); ?></h1>
			<?php do_action('dca_settings_content'); ?>
		</div>
		<?php
	}

	/**
	 * Display plugin settings
	 *
	 * @since    1.0.0
	 */
	public function dca_admin_setting_page_content()
	{

		include_once plugin_dir_path(__FILE__) . 'partials/discover-cars-api-admin-display.php';
	}

	/**
	 * Save plugin settings
	 *
	 * @since    1.0.0
	 */
	public function dca_save_api_settings()
	{

		if (isset($_POST['dca-submit'])) {

			if (isset($_POST['dca_api_url'])) {
				update_option('dca_api_url', $_POST['dca_api_url']);
			}
			if (isset($_POST['dca_api_username'])) {
				update_option('dca_api_username', $_POST['dca_api_username']);
			}
			if (isset($_POST['dca_api_password'])) {
				update_option('dca_api_password', $_POST['dca_api_password']);
			}

			if (isset($_POST['dca_api_token'])) {
				update_option('dca_api_token', $_POST['dca_api_token']);
			}
		} else if (isset($_POST['location-update'])) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'discovercars_locations';
			$dca = new Discover_Cars_Api_Methods();
			$locations = $dca->get_locations();

			if (!empty($locations) && !isset($locations['error'])) {
				$wpdb->query("TRUNCATE TABLE $table_name");
				foreach ($locations as $location) {
					$wpdb->insert($table_name, $location);
				}
			}
		} else if (isset($_POST['policies-update'])) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'discovercars_fuel_policies';
			$dca = new Discover_Cars_Api_Methods();
			$policies = $dca->get_fuel_policies();
			//dc($policies, 1);
			if (!empty($policies) && !isset($policies['error'])) {
				$wpdb->query("TRUNCATE TABLE $table_name");
				foreach ($policies as $policy) {
					$data = array('policy_id' => $policy['ID'], 'name' => $policy['Name'], 'description' => $policy['Description']);
					$wpdb->insert($table_name, $data);
				}
			}
		} else if (isset($_POST['location-type-update'])) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'discovercars_location_types';
			$dca = new Discover_Cars_Api_Methods();
			$locations = $dca->get_location_types();
			//dc($policies, 1);
			if (!empty($locations) && !isset($locations['error'])) {
				$wpdb->query("TRUNCATE TABLE $table_name");
				foreach ($locations as $location) {
					$data = array('location_id' => $location['ID'], 'name' => $location['Name'], 'description' => $location['Description']);
					$wpdb->insert($table_name, $data);
				}
			}
		}
	}

	/**
	 * Show notice when settings updated.
	 *
	 * @since    1.0.0
	 */
	public function dca_admin_notice()
	{
		if ($_POST && isset($_GET['page']) == 'dca-settings') { ?>
			<div class="notice notice-success is-dismissible">
				<p><?php _e('Settings saved.', 'discover-cars-api'); ?></p>
			</div>
		<?php
		}
	}

	/**
	 * Register Car Bookings posttype
	 *
	 * @since    1.0.0
	 */
	public function dca_register_posttype()
	{

		$labels = [
			"name" => __("Car Bookings", "discover-cars-api"),
			"singular_name" => __("Car  Booking", "discover-cars-api"),
		];

		$args = [
			"label" => __("Car Bookings", "discover-cars-api"),
			"labels" => $labels,
			"description" => "",
			"public" => false,
			"publicly_queryable" => false,
			"show_ui" => true,
			"show_in_rest" => false,
			"rest_base" => "",
			"rest_controller_class" => "WP_REST_Posts_Controller",
			"has_archive" => false,
			"show_in_menu" => true,
			"show_in_nav_menus" => true,
			"delete_with_user" => false,
			"exclude_from_search" => false,
			"capability_type" => "post",
			"map_meta_cap" => true,
			"hierarchical" => false,
			"rewrite" => ["slug" => "gfc_booking", "with_front" => true],
			"query_var" => true,
			"menu_icon" => "dashicons-car",
			"supports" => ["title"],
			"show_in_graphql" => false,
		];

		register_post_type("gfc_booking", $args);
	}

	/**
	 * Add metabox to Car Bookings posttype
	 *
	 * @since    1.0.0
	 */
	public function dca_car_boking_metabox()
	{
		add_meta_box('booking_details', __('Booking Details', 'discover-cars-api'), array($this, 'booking_details_metabox_callback'), 'gfc_booking', 'normal', 'low');
		add_meta_box('car_details', __('Car Details', 'discover-cars-api'), array($this, 'car_details_metabox_callback'), 'gfc_booking', 'normal', 'low');
		add_meta_box('txn_details', __('Transaction Details', 'discover-cars-api'), array($this, 'txn_details_metabox_callback'), 'gfc_booking', 'normal', 'low');
	}

	/**
	 * Booking Details metabox
	 *
	 * @since    1.0.0
	 */
	public function booking_details_metabox_callback($post)
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'discovercars_locations';

		$postId = $post->ID;
		$driver_title = get_post_meta($postId, 'driver_title', true);
		$driver_first_name = get_post_meta($postId, 'driver_first_name', true);
		$driver_last_name = get_post_meta($postId, 'driver_last_name', true);
		$driver_email = get_post_meta($postId, 'driver_email', true);
		$rob_driverphone = get_post_meta($postId, 'rob_driverphone', true);
		$rob_driverDob = get_post_meta($postId, 'rob_driverDob', true);
		$country = get_post_meta($postId, 'residence', true);
		$countries = dca_countries();
		$residence_country = isset($countries[$country]) ? $countries[$country] : '';
		$rent_days = get_post_meta($postId, 'rent_days', true);
		$pick_date_time = get_post_meta($postId, 'pick_date_time', true);
		$drop_date_time = get_post_meta($postId, 'drop_date_time', true);
		$pick_location = get_post_meta($postId, 'pick_location', true);
		$dropoff_location = get_post_meta($postId, 'dropoff_location', true);

		$pick_locationName = '';
		if($pick_location)
		{
			$pick_location  = $wpdb->get_row("SELECT * FROM $table_name WHERE `LocationID` = '$pick_location'");
			if($pick_location)
			{
				$pick_locationName = $pick_location->Location.' '.$pick_location->Country;
			}
		}
		$dropoff_locationName = '';
		if($dropoff_location)
		{
			$dropoff_location  = $wpdb->get_row("SELECT * FROM $table_name WHERE `LocationID` = '$dropoff_location'");
			if($dropoff_location)
			{
				$dropoff_locationName = $dropoff_location->Location.' '.$dropoff_location->Country;
			}
		}
		?>
		<table class="widefat striped">
			<tr>
				<th> <?php _e('Driver name', 'discover-cars-api'); ?>
				</th>
				<td><?php echo $driver_title . ' ' . $driver_first_name . ' ' . $driver_last_name; ?></td>
			</tr>
			<tr>
				<th> <?php _e('Email', 'discover-cars-api'); ?>
				</th>
				<td><?php echo $driver_email; ?></td>
			</tr>
			<tr>
				<th> <?php _e('Phone', 'discover-cars-api'); ?>
				</th>
				<td><?php echo $rob_driverphone; ?></td>
			</tr>
			<tr>
				<th> <?php _e('Date of Birth', 'discover-cars-api'); ?>
				</th>
				<td><?php echo $rob_driverDob; ?></td>
			</tr>
			<tr>
				<th> <?php _e('Residence', 'discover-cars-api'); ?>
				</th>
				<td> <?php echo $residence_country; ?></td>
			</tr>
			<tr>
				<th> <?php _e('Rent days', 'discover-cars-api'); ?>
				</th>
				<td> <?php echo $rent_days; ?></td>
			</tr>
			<tr>
				<th> <?php _e('Pickup location', 'discover-cars-api'); ?>
				</th>
				<td> <?php echo $pick_locationName; ?></td>
			</tr>
			<tr>
				<th> <?php _e('Dropoff location', 'discover-cars-api'); ?>
				</th>
				<td> <?php echo $dropoff_locationName; ?></td>
			</tr>
			<tr>
				<th> <?php _e('Pickup time', 'discover-cars-api'); ?>
				</th>
				<td> <?php echo str_replace('T', ' ', $pick_date_time); ?></td>
			</tr>
			<tr>
				<th> <?php _e('Dropoff time', 'discover-cars-api'); ?>
				</th>
				<td> <?php echo str_replace('T', ' ', $drop_date_time); ?></td>
			</tr>
		</table>
	<?php
	}

	/**
	 * Car details metabox
	 *
	 * @since    1.0.0
	 */
	public function car_details_metabox_callback($post)
	{

		$postId = $post->ID;
		$car_name = get_post_meta($postId, 'car_name', true);
		$seats = get_post_meta($postId, 'seats', true);
		$bags = get_post_meta($postId, 'bags', true);
		$doors = get_post_meta($postId, 'doors', true);
		$ac = get_post_meta($postId, 'air_condition', true);
		$total_amount = get_post_meta($postId, 'total_amount', true);

	?>
		<table class="widefat striped">
			<tr>
				<th> <?php _e('Car name', 'discover-cars-api'); ?></th>
				<td><?= $car_name; ?></td>
			</tr>
			<tr>
				<th> <?php _e('Seats', 'discover-cars-api'); ?></th>
				<td><?= $seats; ?></td>
			</tr>
			<tr>
				<th> <?php _e('Airbags', 'discover-cars-api'); ?></th>
				<td><?= $bags; ?></td>
			</tr>
			<tr>
				<th> <?php _e('Doors', 'discover-cars-api'); ?> </th>
				<td><?= $doors; ?></td>
			</tr>
			<tr>
				<th> <?php _e('Airconditioning', 'discover-cars-api'); ?></th>
				<td><?php if ($ac)	echo 'Yes'; ?></td>
			</tr>
			<tr>
				<th> <?php _e('Car amount', 'discover-cars-api'); ?></th>
				<td><?= $total_amount; ?></td>
			</tr>

		</table>
	<?php
	}

	/**
	 * Transaction details metabox
	 *
	 * @since    1.0.0
	 */
	public function txn_details_metabox_callback($post)
	{
		$postId = $post->ID;
		// echo '<pre>';
		// print_r(get_post_meta($postId));
		// echo 'asasasas' . get_post_meta($postId, 'discount_coupen_code', true);
		// echo '</pre>';
		$txn_id = get_post_meta($postId, 'txn_id', true);
		$txn_amount = get_post_meta($postId, 'txn_amount', true);
		$txn_status = get_post_meta($postId, 'txn_status', true);
		$send_email = get_post_meta($postId, 'send_confirmation_email', true);
		$coupen_code = get_post_meta($postId, 'discount_coupen_code', true);
		$email = ($send_email == 0) ? 'No' : 'Yes';
	?>
		<table class="widefat striped">
			<tr>
				<th> <?php _e('Transaction id', 'discover-cars-api'); ?></th>
				<td><?php echo $txn_id; ?></td>
			</tr>
			<tr>
				<th> <?php _e('Amount', 'discover-cars-api'); ?></th>
				<td><?php echo $txn_amount; ?></td>
			</tr>
			<tr>
				<th> <?php _e('Status', 'discover-cars-api'); ?> </th>
				<td><?php echo $txn_status; ?></td>
			</tr>
			<tr>
				<th> <?php _e('Email Confirmation sent to user', 'discover-cars-api'); ?> </th>
				<td><?php echo $email; ?></td>
			</tr>
			<tr>
				<th> <?php _e('Coupen code', 'discover-cars-api'); ?> </th>
				<td><?php echo $coupen_code; ?></td>
			</tr>
		</table>
<?php
	}

	public function dca_manage_car_booking_columns($columns)
	{
		$columns = array(
			'cb'          => '&lt;input type="checkbox" />',
			'title'       => __('Title', 'discover-cars-api'),
			'car'         => __('Car', 'discover-cars-api'),
			'price'       => __('Price', 'discover-cars-api'),
			'date'        => __('Date', 'discover-cars-api')
		);
		return $columns;
	}

	public function dca_manage_car_booking_columns_callback($column, $post_id)
	{

		switch ($column) {

			case 'car':

				echo get_post_meta($post_id, 'car_name', true);

				break;

			case 'price':

				echo '£' . get_post_meta($post_id, 'total_amount', true);

				break;
		}
	}

	public function dca_filter_booking_in_admin($query)
	{
		if ( is_admin() && $query->is_main_query() && $query->get('post_type') == 'gfc_booking' )
		{
			$query->set( 'meta_key', '_status' );
		    $query->set( 'meta_query', array(
		        array(
		            'key'     => '_status',
		            'value'   => 'paid'
		        )
		    ) );
		}
	}
}
