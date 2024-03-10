<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://cmitexperts.com/
 * @since      1.0.0
 *
 * @package    Gctcf
 * @subpackage Gctcf/includes
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
 * @package    Gctcf
 * @subpackage Gctcf/includes
 * @author     CMITEXPERTS TEAM <cmitexperts@gmail.com>
 */
class Gctcf {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Gctcf_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'GCTCF_VERSION' ) ) {
			$this->version = GCTCF_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'gctcf';

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
	 * - Gctcf_Loader. Orchestrates the hooks of the plugin.
	 * - Gctcf_i18n. Defines internationalization functionality.
	 * - Gctcf_Admin. Defines all hooks for the admin area.
	 * - Gctcf_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gctcf-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gctcf-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-gctcf-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-gctcf-public.php';

		/**
		 * The file responsible for defining all function that occur in the public-facing and in admin area
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/gctcf-helpers.php';

		/**
		 * The file responsible for defining all ajax function that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/gctcf-ajax.php';

		$this->loader = new Gctcf_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Gctcf_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Gctcf_i18n();

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

		$plugin_admin = new Gctcf_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'gctcf_testimonial_add_meta_box' );
        $this->loader->add_action( 'save_post', $plugin_admin, 'gctcf_testimonial_metabox_save');
        $this->loader->add_filter( 'manage_gctcf_testimonials_posts_columns', $plugin_admin, 'gctcf_testmonial_custom_column');
		$this->loader->add_filter( 'manage_gctcf_testimonials_posts_custom_column', $plugin_admin, 'gctcf_testimonial_column',10,2);
		$this->loader->add_action( 'init', $plugin_admin, 'gctcf_add_option_page' );
		$this->loader->add_action( 'init', $plugin_admin, 'gc4t_register_post_types' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'gc4t_add_submenu_pages' );
		$this->loader->add_action( 'woocommerce_order_status_completed', $plugin_admin , 'gc4t_activate_store_voucher');
		$this->loader->add_action( 'woocommerce_order_status_cancelled', $plugin_admin , 'gc4t_cancel_store_voucher');
		$this->loader->add_action( 'woocommerce_order_status_processing', $plugin_admin , 'gc4t_send_store_voucher');
		$this->loader->add_action( 'woocommerce_order_status_on-hold', $plugin_admin , 'gc4t_pause_store_voucher');
		$this->loader->add_action( 'woocommerce_thankyou', $plugin_admin , 'gc4t_woocommerce_thankyou' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'Byc_travels_themes_plugin_settings_fields' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'gctcf_attraction_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'gctcf_attraction_page_init' );
		$this->loader->add_action( 'init', $plugin_admin, 'attraction_api_admin_fun_callback' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'attraction_admin_notice' );
		//$this->loader->add_action( 'init', $plugin_admin, 'gctcf_attraction_booking' );

		$this->loader->add_action( 'admin_init',$plugin_admin, 'gctcf_admin_hooks');

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Gctcf_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );
		$this->loader->add_action( 'init', $plugin_public, 'gctcf_testimonial_posttype' );
		$this->loader->add_action( 'init', $plugin_public, 'gctcf_testimonial_posttype' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'gctcf_footer' );
		$this->loader->add_action( 'woocommerce_email_after_order_table', $plugin_public, 'gctcf_send_giftcard_mail', 10, 4 );


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
	 * @return    Gctcf_Loader    Orchestrates the hooks of the plugin.
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
