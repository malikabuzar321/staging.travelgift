<?php

namespace Oxygen\WooElements;

class CartTotal extends \OxyWooEl {

    function name() {
        return 'Cart Total';
    }

    function woo_button_place() {
        return "other";
    }

    function init() {
        add_filter( 'woocommerce_add_to_cart_fragments', array($this,'add_to_cart_woo_ajax_callback') );
    }

    function icon() {
        return plugin_dir_url(__FILE__) . 'assets/'.basename(__FILE__, '.php').'.svg';
    }

    function render($options, $defaults, $content) {
        if (function_exists("WC") && isset(WC()->cart)) {
            echo "<div class='oxy-woo-cart-total'>".WC()->cart->get_total()."</div>";
        }
    }

    function controls() {

        $typography_section = $this->typographySection(
            __("Price Typography"),
            ".woocommerce-Price-amount",
            $this
        );
    }

    function add_to_cart_woo_ajax_callback( $fragments ) {

        ob_start();

        ?>
        <div class="oxy-woo-cart-total"><?php echo WC()->cart->get_total(); ?></div>
        <?php

        $fragments['div.oxy-woo-cart-total'] = ob_get_clean();
        return $fragments;
    }

}

new CartTotal();