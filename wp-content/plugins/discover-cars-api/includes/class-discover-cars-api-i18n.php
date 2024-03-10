<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.giftcards4travel.co.uk/
 * @since      1.0.0
 *
 * @package    Discover_Cars_Api
 * @subpackage Discover_Cars_Api/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Discover_Cars_Api
 * @subpackage Discover_Cars_Api/includes
 * @author     Giftcards4travel <rob@giftcards4travel.co.uk>
 */
class Discover_Cars_Api_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'discover-cars-api',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
