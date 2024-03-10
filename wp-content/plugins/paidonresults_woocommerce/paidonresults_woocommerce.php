<?php
/**
 * Plugin Name: Paid On Results - WooCommerce
 * Plugin URI: http://paidonresults.com
 * Description: Plugin to install the Paid On Results tracking for use with WooCommerce.
 * Version: 3.0.3
 * Author: Paid On Results
 * Author URI: http://paidonresults.com
 * License: A "Slug" license name e.g. GPL2
 */

/*  Copyright 2014  Paid On Results Limited

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if(!class_exists('PaidOnResults_WooCommerce'))
{
        class PaidOnResults_WooCommerce
        {
                /**
                 * Construct the plugin object
                 */
                public function __construct()
                {
                // Initialize Settings
            require_once(sprintf("%s/settings.php", dirname(__FILE__)));
            $PaidOnResults_WooCommerce_Settings = new PaidOnResults_WooCommerce_Settings();
                
                // Register custom post types
            require_once(sprintf("%s/post-types/post_type_template.php", dirname(__FILE__)));
            $Post_Type_Template = new Post_Type_Template();
                } // END public function __construct
            
                /**
                 * Activate the plugin
                 */
                public static function activate()
                {
                        // Do nothing
                } // END public static function activate
        
                /**
                 * Deactivate the plugin
                 */                
                public static function deactivate()
                {
                        // Do nothing
                } // END public static function deactivate
        } // END class PaidOnResults_WooCommerce
} // END if(!class_exists('PaidOnResults_WooCommerce'))

if(class_exists('PaidOnResults_WooCommerce'))
{
        // Installation and uninstallation hooks
        register_activation_hook(__FILE__, array('PaidOnResults_WooCommerce', 'activate'));
        register_deactivation_hook(__FILE__, array('PaidOnResults_WooCommerce', 'deactivate'));

        // instantiate the plugin class
        $paidonresults_woocommerce = new PaidOnResults_WooCommerce();
        
    // Add a link to the settings page onto the plugin page
    if(isset($paidonresults_woocommerce))
    {
        // Add the settings link to the plugins page
        function plugin_settings_link($links)
        { 
            $settings_link = '<a href="options-general.php?page=paidonresults_woocommerce">Settings</a>'; 
            array_unshift($links, $settings_link); 
            return $links; 
        }

        $plugin = plugin_basename(__FILE__); 
        add_filter("plugin_action_links_$plugin", 'plugin_settings_link');
    }
}

add_action( 'woocommerce_thankyou', 'paidonresults_actionhook' );

    function paidonresults_actionhook ( $order_id ) {

        $order = new WC_Order( $order_id );
        $order_discount = $order->get_total_discount();
        $order_coupons = $order->get_coupon_codes();

if(isset($order_coupons[0])){
$voucher_code = $order_coupons[0];
} else {
$voucher_code = '';
}

        $sale_id = $order->get_order_number();
        $order_currency = $order->get_currency();

        $arr = array();
        $product_sub_total = 0;

        foreach( $order->get_items() as $item ) :
        $arr[]= esc_attr( $item['product_id'] ) .','. $item['line_subtotal'];
        $product_sub_total = $product_sub_total + $item['line_subtotal'];
        endforeach;

        $final = implode('|',$arr);

        echo '<p><script type="text/javascript" src="https://portgk.com/create-sale?client=java&MerchantID='.get_option('setting_a').'&SaleID='.$sale_id.'&Purchases='.$final.'&OrderDiscount='.$order_discount.'&VoucherCode='.$voucher_code.'&OrderCurrency='.$order_currency.'&ExcludeVAT='.get_option('setting_b').'"></script><noscript><img src="https://portgk.com/create-sale?client=img&MerchantID='.get_option('setting_a').'&SaleID='.$sale_id.'&Purchases='.$final.'&OrderDiscount='.$order_discount.'&VoucherCode='.$voucher_code.'&OrderCurrency='.$order_currency.'&ExcludeVAT='.get_option('setting_b').'" width="10" height="10" border="0"></noscript></p>' . "
        ";

    } // End paidonresults_actionhook()

add_action( 'wp_footer', 'paidonresults_footercode',1 );

    function paidonresults_footercode($order_id)
    {

        echo '<!-- Paid On Results Code-->
<script language="JavaScript" src="//porjs.com/'.get_option('setting_a').'.js"></script>
<!-- End Paid On Results Code-->

';

    } // End paidonresults_footercode_actionhook()
?>