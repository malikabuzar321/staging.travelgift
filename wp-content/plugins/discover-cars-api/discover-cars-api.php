<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.giftcards4travel.co.uk/
 * @since             1.0.0
 * @package           Discover_Cars_Api
 *
 * @wordpress-plugin
 * Plugin Name:       Discover cars API
 * Plugin URI:        https://www.giftcards4travel.co.uk/
 * Description:       Use shortcode [Giftcard-Car-Search] for print car search form.
 * Version:           1.0.0
 * Author:            Giftcards4travel
 * Author URI:        https://www.giftcards4travel.co.uk/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       discover-cars-api
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'DISCOVER_CARS_API_VERSION', '1.0.0' );
define('DCA_URL', plugin_dir_url(__FILE__));
define('DCA_PATH', plugin_dir_path(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-discover-cars-api-activator.php
 */
function activate_discover_cars_api() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-discover-cars-api-activator.php';
	Discover_Cars_Api_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-discover-cars-api-deactivator.php
 */
function deactivate_discover_cars_api() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-discover-cars-api-deactivator.php';
	Discover_Cars_Api_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_discover_cars_api' );
register_deactivation_hook( __FILE__, 'deactivate_discover_cars_api' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-discover-cars-api.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_discover_cars_api() {

	$plugin = new Discover_Cars_Api();
	$plugin->run();

}
run_discover_cars_api();

function dc($val, $flag=false)
{
	echo "<pre>".print_r($val, true)."</pre>";
	if($flag)
	{
		die("here");
	}
}