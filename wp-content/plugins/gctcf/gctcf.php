<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://cmitexperts.com/
 * @since             1.0.0
 * @package           Gctcf
 *
 * @wordpress-plugin
 * Plugin Name:       Giftcards4travel Custom Functions
 * Plugin URI:        https://cmitexperts.com/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            CMITEXPERTS TEAM
 * Author URI:        https://cmitexperts.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gctcf
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
define( 'GCTCF_VERSION', '1.0.0' );

define('GCTCF_FILE', __FILE__);

define('GCTCF_DIRNAME', basename(dirname(__FILE__)));

define('GCTCF_RELPATH', basename(dirname(__FILE__)) . '/' . basename(__FILE__));

define('GCTCF_PATH', plugin_dir_path(__FILE__));

define('GCTCF_URL', plugin_dir_url(__FILE__));

define('GCTCF_PLUGINPATH', WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)));
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-gctcf-activator.php
 */
function activate_gctcf() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gctcf-activator.php';
	Gctcf_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-gctcf-deactivator.php
 */
function deactivate_gctcf() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gctcf-deactivator.php';
	Gctcf_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_gctcf' );
register_deactivation_hook( __FILE__, 'deactivate_gctcf' );


/**
 * Adding code for functionality to sync data from different APIs to local DB
 * All methods are written in includes/data_sync.php
 */
// Add custom rewrite rule
// Add custom rewrite rule
// function custom_rewrite_rule() {
//     add_rewrite_rule('^data_sync/([^/]+)/?', 'index.php?data_sync=$matches[1]', 'top');
// }
// add_action('init', 'custom_rewrite_rule');

// // Hook into query parsing to execute function based on custom endpoint
// function custom_query_vars($query_vars) {
//     $query_vars[] = 'data_sync';
//     return $query_vars;
// }
// add_filter('query_vars', 'custom_query_vars');

// // Execute function based on custom endpoint
// function execute_data_sync_function() {
//     echo "Before getting query var|";
//     $action = get_query_var('data_sync');
//     echo "After getting query var|";
//     var_dump($action);
//     exit;
//     if ($action) {
//         // Include data_sync.php file
//         require_once plugin_dir_path(__FILE__) . 'includes/data_sync.php';

//         // Call the function based on the action
//         if (function_exists($action)) {
//             call_user_func($action);
//             exit; // Stop further execution
//         } else {
//             // global $wp_query;
//             // $wp_query->set_404();
//             // status_header(404);
//             echo "Function Not Found";
//         }
//     }
// }
// add_action('parse_request', 'execute_data_sync_function');
// exit();



/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-gctcf.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_gctcf() {

	$plugin = new Gctcf();
	$plugin->run();

}
run_gctcf();
