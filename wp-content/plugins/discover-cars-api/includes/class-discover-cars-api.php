<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.giftcards4travel.co.uk/
 * @since      1.0.0
 *
 * @package    Discover_Cars_Api
 * @subpackage Discover_Cars_Api/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Discover_Cars_Api
 * @subpackage Discover_Cars_Api/includes
 * @author     Giftcards4travel <rob@giftcards4travel.co.uk>
 */
class Discover_Cars_Api {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Discover_Cars_Api_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'DISCOVER_CARS_API_VERSION' ) ) {
			$this->version = DISCOVER_CARS_API_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'discover-cars-api';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Discover_Cars_Api_Loader. Orchestrates the hooks of the plugin.
	 * - Discover_Cars_Api_i18n. Defines internationalization functionality.
	 * - Discover_Cars_Api_Admin. Defines all hooks for the admin area.
	 * - Discover_Cars_Api_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-discover-cars-api-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-discover-cars-api-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-discover-cars-api-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-discover-cars-api-public.php';

		/**
		 * The class responsible for defining all api methods/endpoints
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-discover-cars-api-methods.php';

		$this->loader = new Discover_Cars_Api_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Discover_Cars_Api_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Discover_Cars_Api_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Discover_Cars_Api_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'dca_add_plugin_page' );
		$this->loader->add_action( 'dca_settings_content', $plugin_admin, 'dca_admin_setting_page_content' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'dca_save_api_settings' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'dca_admin_notice' );
		$this->loader->add_action( 'init', $plugin_admin, 'dca_register_posttype' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'dca_car_boking_metabox' );
		$this->loader->add_filter( 'manage_gfc_booking_posts_columns', $plugin_admin, 'dca_manage_car_booking_columns' );
		$this->loader->add_action( 'manage_gfc_booking_posts_custom_column', $plugin_admin, 'dca_manage_car_booking_columns_callback', 10, 2 );
		$this->loader->add_action( 'pre_get_posts', $plugin_admin, 'dca_filter_booking_in_admin' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Discover_Cars_Api_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init', $plugin_public, 'dca_register_my_session' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );
		$this->loader->add_action('wp_ajax_dca_search_locations', $plugin_public, 'dca_search_locations');
		$this->loader->add_action('wp_ajax_nopriv_dca_search_locations', $plugin_public, 'dca_search_locations');
		$this->loader->add_action('wp_ajax_dca_search_dropoff_locations', $plugin_public, 'dca_search_dropoff_locations');
		$this->loader->add_action('wp_ajax_nopriv_dca_search_dropoff_locations', $plugin_public, 'dca_search_dropoff_locations');
		$this->loader->add_action('wp_ajax_dca_search_results', $plugin_public, 'dca_search_results');
		$this->loader->add_action('wp_ajax_nopriv_dca_search_results', $plugin_public, 'dca_search_results');
		$this->loader->add_action('wp_ajax_dca_booking_form', $plugin_public, 'dca_booking_form');
		$this->loader->add_action('wp_ajax_nopriv_dca_booking_form', $plugin_public, 'dca_booking_form');
		$this->loader->add_action('wp_ajax_dca_paypal_pament', $plugin_public, 'dca_paypal_pament');
		$this->loader->add_action('wp_ajax_nopriv_dca_paypal_pament', $plugin_public, 'dca_paypal_pament');
		$this->loader->add_action('template_redirect', $plugin_public, 'dca_paypal_response');
		$this->loader->add_filter('template_include', $plugin_public, 'dca_template_override');


	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Discover_Cars_Api_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
