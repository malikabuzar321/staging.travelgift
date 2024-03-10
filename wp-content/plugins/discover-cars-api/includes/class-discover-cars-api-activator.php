<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.giftcards4travel.co.uk/
 * @since      1.0.0
 *
 * @package    Discover_Cars_Api
 * @subpackage Discover_Cars_Api/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Discover_Cars_Api
 * @subpackage Discover_Cars_Api/includes
 * @author     Giftcards4travel <rob@giftcards4travel.co.uk>
 */
class Discover_Cars_Api_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		global $wpdb;
		  $charset_collate = $wpdb->get_charset_collate();
		  $table_name = $wpdb->prefix . 'discovercars_locations';
		  $sql = "CREATE TABLE IF NOT EXISTS $table_name (
		  					  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `LocationID` varchar(255),
							  `Country` varchar(255) DEFAULT '',
							  `CountryCode` varchar(20) DEFAULT '',
							  `City` varchar(20) DEFAULT '',
							  `Location` text DEFAULT '',
							  `IATA` varchar(255) DEFAULT '',
							  `Latitude` decimal(11,7) DEFAULT 0.0,
							  `Longitude` decimal(11,7) DEFAULT 0.0,
							  `PostCode` varchar(255) DEFAULT '',
							  `created` datetime DEFAULT CURRENT_TIMESTAMP,
							  PRIMARY KEY(id)
		  			)$charset_collate;";

		  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		  dbDelta( $sql );

		  /*
			Add Fuel policies table
		  */

		$table_name = $wpdb->prefix . 'discovercars_fuel_policies';
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		  					  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `policy_id` int(11) NOT NULL,
							  `name` varchar(255) DEFAULT '',
							  `description` text DEFAULT '',
							  `created` datetime DEFAULT CURRENT_TIMESTAMP,
							  PRIMARY KEY(id)
		  			)$charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		/*
			Add location types table
		  */

		$table_name = $wpdb->prefix . 'discovercars_location_types';
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		  					  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `location_id` int(11) NOT NULL,
							  `name` varchar(255) DEFAULT '',
							  `description` text DEFAULT '',
							  `created` datetime DEFAULT CURRENT_TIMESTAMP,
							  PRIMARY KEY(id)
		  			)$charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

}
