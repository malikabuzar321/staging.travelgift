<?php

/*
Plugin Name: Oxygen Elements for WooCommerce
Author: Soflyy
Author URI: https://oxygenbuilder.com
Description: Build beautiful WooCommerce websites.
Version: 2.0
*/

require_once("admin/includes/updater/edd-updater.php");
define("CT_OXYGEN_WOOCOMMERCE_VERSION", 	"2.0");

add_action('plugins_loaded', 'oxygen_woocommerce_init');
function oxygen_woocommerce_init() {

  // check if WooCommerce installed and active
  if (!class_exists( 'WooCommerce' ) ) {
    return;
  }

  // check if Oxygen installed and active
  if (!class_exists('OxygenElement')) {
      return;
  }

  define("OXY_WOO_ASSETS_PATH", plugins_url("elements/assets", __FILE__));
  define("OXY_WOO_SETTINGS_ICONS", plugins_url("icons", __FILE__));

  add_filter( 'woocommerce_locate_template', 'oxy_woo_template_overrides', 1, 3 );

  require_once('OxyWooEl.php');
  require_once('OxyWooCommerce.php');
  require_once('OxyWooConditions.php');

  

  $OxyWooCommerce = new OxyWooCommerce();
  $OxyWooConditions = new OxyWooConditions();
}

function oxy_woo_template_overrides( $template, $template_name, $template_path ) {
  global $woocommerce;
  $_template = $template;
  if ( ! $template_path ) 
    $template_path = $woocommerce->template_url;

  $template_path  = WP_CONTENT_DIR  . '/oxywoocotemplates/woocommerce/';

  // Look within passed path within the theme - this is priority
  $template = locate_template(
  array(
    $template_path . $template_name,
    $template_name
  )
  );

  if( ! $template && file_exists( $template_path . $template_name ) )
  $template = $template_path . $template_name;

  if ( ! $template )
  $template = $_template;

  return $template;
}
