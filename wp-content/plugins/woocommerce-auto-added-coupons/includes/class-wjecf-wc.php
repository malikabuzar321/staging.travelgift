<?php

defined( 'ABSPATH' ) or die();

/**
 *
 * Interface to WooCommerce. Handles version differences / backwards compatibility.
 *
 * @since 2.3.7.2
 */
class WJECF_WC {

	/**
	 * Check whether WooCommerce version is greater or equal than $req_version
	 * @param string @req_version The version to compare to
	 * @return bool true if WooCommerce is at least the given version
	 */
	public function check_woocommerce_version( $req_version ) {
		return version_compare( wc()->version, $req_version, '>=' );
	}

	/**
	 * Returns an array of items like stored in WC_Discounts since WC3.2.0
	 *
	 * Note: $item->price is in cents; use wc_remove_number_precision( $item->price ) to compare it to the actual amount
	 *
	 *     $item->key           = $key;
	 *     $item->object        = $cart_item;
	 *     $item->product       = $cart_item['data'];
	 *     $item->quantity      = $cart_item['quantity'];
	 *     $item->price         = self::wc_add_number_precision_deep( $item->product->get_price() ) * $item->quantity;
	 *
	 * @param WC_Discounts|null $wc_discounts
	 * @return array of items (stdClass)
	 */
	public function get_discount_items( $wc_discounts = null ) {
		if ( is_null( $wc_discounts ) ) {
			$wc_discounts = new WJECF_WC_Discounts( WC()->cart );
		}
		return $wc_discounts->get_items();
	}

	/**
	 * Convert a cart_item to an item as generated by WC_Discounts
	 *
	 * @since 2.6.0
	 */
	public function cart_item_to_discount_item( $cart_item, $key = null ) {
		$item           = new stdClass();
		$item->object   = $cart_item;
		$item->product  = $cart_item['data'];
		$item->quantity = $cart_item['quantity'];
		$item->price    = $this->wc_add_number_precision_deep( $item->product->get_price() ) * $item->quantity;

		if ( isset( $key ) ) {
			$item->key = $key;
		} else if ( isset( $cart_item['key'] ) ) {
			$item->key = $cart_item['key'];
		} else {
			//For WC prior to 3.2.0: lookup the cart item key because $cart_item['key'] is not set
			$item->key = array_search( $cart_item, WC()->cart->get_cart() );
		}

		return $item;
	}

	/**
	 * Add precision to an array of number and return an array of int.
	 *
	 * @since  2.6.0 (taken from WC 3.2.3)
	 * @param  array $value Number to add precision to.
	 * @return int
	 */
	public function wc_add_number_precision_deep( $value ) {
		//Since WooCommerce 3.2.0.
		if ( function_exists( 'wc_add_number_precision_deep' ) ) {
			return wc_add_number_precision_deep( $value );
		}

		if ( is_array( $value ) ) {
			foreach ( $value as $key => $subvalue ) {
				$value[ $key ] = $this->wc_add_number_precision_deep( $subvalue );
			}
		} else {
			$value = $this->wc_add_number_precision( $value );
		}
		return $value;
	}

	/**
	 * Add precision to a number and return an int.
	 *
	 * @since  2.6.0 (taken from WC 3.2.3)
	 * @param  float $value Number to add precision to.
	 * @return int
	 */
	public function wc_add_number_precision( $value ) {
		//Since WooCommerce 3.2.0.
		if ( function_exists( 'wc_add_number_precision' ) ) {
			return wc_add_number_precision( $value );
		}

		$precision = pow( 10, wc_get_price_decimals() );  //Since WC 2.3.0
		return intval( round( $value * $precision ) );
	}

	/**
	 * Remove precision from a number and return a float.
	 *
	 * @since  2.6.0 (taken from WC 3.2.3)
	 * @param  float $value Number to add precision to.
	 * @return float
	 */
	public function wc_remove_number_precision( $value ) {
		//Since WooCommerce 3.2.0.
		if ( function_exists( 'wc_remove_number_precision' ) ) {
			return wc_remove_number_precision( $value );
		}

		$precision = pow( 10, wc_get_price_decimals() );  //Since WC 2.3.0
		return $value / $precision;
	}


	/**
	 * Returns a specific item in the cart.
	 *
	 * @param string $cart_item_key Cart item key.
	 * @return array Item data
	 */
	public function get_cart_item( $cart_item_key ) {
		return WC()->cart->get_cart_item( $cart_item_key );
	}

	/**
	 * Get categories of a product (and anchestors)
	 * @param int $product_id
	 * @return array product_cat_ids
	 */
	public function wc_get_product_cat_ids( $product_id ) {
		return wc_get_product_cat_ids( $product_id );
	}

	/**
	 * Coupon types that apply to individual products. Controls which validation rules will apply.
	 *
	 * @since  2.5.0
	 * @return array
	 */
	public function wc_get_product_coupon_types() {
		return wc_get_product_coupon_types();
	}

	public function wc_get_cart_coupon_types() {
		return wc_get_cart_coupon_types();
	}

	/**
	 * Output a list of variation attributes for use in the cart forms.
	 *
	 * @param array $args
	 * @since 2.5.1
	 */
	public function wc_dropdown_variation_attribute_options( $args = array() ) {
		return wc_dropdown_variation_attribute_options( $args );
	}

	/**
	 * Get attibutes/data for an individual variation from the database and maintain its integrity.
	 * @since  2.5.1
	 * @param  int $variation_id
	 * @return array
	 */
	public function wc_get_product_variation_attributes( $variation_id ) {
		return wc_get_product_variation_attributes( $variation_id );
	}

	public function find_matching_product_variation( $product, $match_attributes = array() ) {
		return WC_Data_Store::load( 'product' )->find_matching_product_variation( $product, $match_attributes );
	}

	/**
	 * @since 2.4.0 for WC 2.7 compatibility
	 *
	 * Get a WC_Coupon object
	 * @param WC_Coupon|string|WP_Post $coupon The coupon code or a WC_Coupon object
	 * @return WC_Coupon The coupon object
	 */
	public function get_coupon( $coupon ) {
		if ( $coupon instanceof WP_Post ) {
			$coupon = $coupon->ID;
		}
		if ( ! ( $coupon instanceof WC_Coupon ) ) {
			//By code
			$coupon = new WC_Coupon( $coupon );
		}
		return $coupon;
	}

	/**
	 * Returns the user of the order (in case of wp-admin) or the current user (in case of a frontend cart)
	 *
	 * @since 3.2.7
	 * @param WC_Discounts $wc_discounts
	 * @return WP_User|false
	 */
	public function get_user( $wc_discounts = null ) {
		if ( $wc_discounts->get_object() instanceof WC_Order ) {
			return $wc_discounts->get_object()->get_user();
		} else {
			return wp_get_current_user();
		}
	}

	//INSTANCE

	/**
	 * Singleton Instance
	 *
	 * @static
	 * @return Singleton Instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	protected static $_instance = null;


	/**
	 * Wrap a data object (WC 2.7 introduced WC_Data)
	 * @deprecated 3.0.0
	 * @param type $object
	 * @return WJECF_Wrap
	 */
	public function wrap( $object, $use_pool = true ) {
		_deprecated_function( 'WJECF_WC::wrap', '3.0.0' );
		return WJECF_Wrap::wrap( $object, $use_pool );
	}
}