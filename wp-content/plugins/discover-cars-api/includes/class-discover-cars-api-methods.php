<?php

/**
 * The API functionality of the plugin.
 *
 * @link       https://www.giftcards4travel.co.uk/
 * @since      1.0.0
 *
 * @package    Discover_Cars_Api
 */

/**
 * The API functionality of the plugin.
 *
 * Defines the plugin functions to connect Discover cars API
 *
 * @package    Discover_Cars_Api
 * @author     Giftcards4travel <rob@giftcards4travel.co.uk>
 */
class Discover_Cars_Api_Methods {

	
	private $version;
	private $url;
	private $token;
	private $username;
	private $password;
	//private $auth = 'Basic ' . base64_encode( $username . ':' . $password );

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct(  ) {

		$this->url = rtrim(get_option('dca_api_url')).'/Aggregator/';
		$this->token = get_option('dca_api_token');
		$this->username = get_option('dca_api_username');
		$this->password = get_option('dca_api_password');
	}

	/**
	* Get locations from API
	* Method GET
	* Method returns active location available for search
	* @param access_token, lamguage
	*
	* @since    1.0.0
	*/
	public function get_locations($lang='en')
	{
		$url = $this->url.'Locations?access_token='.$this->token.'&language='.$lang;

		$args = array(
				      'headers' => array(
				      		'Content-Type' => 'application/json',
					        'Authorization' => 'Basic ' . base64_encode( $this->username . ':' . $this->password ),
					    ),
				      'timeout'     => 120
				      );

		$response = wp_remote_get( $url, $args );
		$status =  wp_remote_retrieve_response_code($response);
		if($status != 200)	return array('error' => __("No data found", "discover-cars-api"));
		$response = wp_remote_retrieve_body( $response );
		$response = json_decode($response, 1);
		return $response;
	}

	public function get_cars($body)
	{
		$url = $this->url.'GetCars?access_token='.$this->token;

		$args = array(
				      'headers' => array(
				      		'Content-Type' => 'application/json',
					        'Authorization' => 'Basic ' . base64_encode( $this->username . ':' . $this->password ),
					    ),
				      'timeout'     => 240,
				      'body' => $body
				      );

		$response = wp_remote_post( $url, $args );
		//dc($response);
		$status =  wp_remote_retrieve_response_code($response);
		if($status != 200)	return array('error' => __("No data found", "discover-cars-api"));
		$response = wp_remote_retrieve_body( $response );
		$response = json_decode($response, 1);
		return $response;
	}

	public function get_fuel_policies()
	{
		$url = $this->url.'FuelPoliciesTypes?access_token='.$this->token;

		$args = array(
				      'headers' => array(
				      		'Content-Type' => 'application/json',
					        'Authorization' => 'Basic ' . base64_encode( $this->username . ':' . $this->password ),
					    ),
				      'timeout'     => 120
				      );

		$response = wp_remote_get( $url, $args );
		$status =  wp_remote_retrieve_response_code($response);
		if($status != 200)	return array('error' => __("No data found", "discover-cars-api"));
		$response = wp_remote_retrieve_body( $response );
		$response = json_decode($response, 1);
		return $response;
	}

	public function get_location_types()
	{
		$url = $this->url.'PickupLocationTypes?access_token='.$this->token;

		$args = array(
				      'headers' => array(
				      		'Content-Type' => 'application/json',
					        'Authorization' => 'Basic ' . base64_encode( $this->username . ':' . $this->password ),
					    ),
				      'timeout'     => 120
				      );

		$response = wp_remote_get( $url, $args );
		$status =  wp_remote_retrieve_response_code($response);
		if($status != 200)	return array('error' => __("No data found", "discover-cars-api"));
		$response = wp_remote_retrieve_body( $response );
		$response = json_decode($response, 1);
		return $response;
	}

}
