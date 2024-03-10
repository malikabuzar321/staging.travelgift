<?php

if (class_exists('OxyWooConditions')) {
    return;
}

Class OxyWooConditions {

    public $OxygenConditions;
    
    function __construct() {

        global $OxygenConditions;

        if ($OxygenConditions) {
            $this->OxygenConditions = $OxygenConditions;
            $this->registerConditions();
        }
    }

    public function registerConditions() {

        $this->OxygenConditions->register_condition(
            'Customer Bought Product', 
            array('custom' => true), 
            $this->OxygenConditions->condition_operators['simple'], 
            'oxy_woo_condition_wc_customer_bought_product',
            'WooCo'
        );

        $this->OxygenConditions->register_condition(
            'Product Is In Cart', 
            array('custom' => true), 
            $this->OxygenConditions->condition_operators['simple'], 
            'oxy_woo_condition_product_is_in_cart',
            'WooCo'
        );

        $this->OxygenConditions->register_condition(
            'Is Shop', 
            array('options' => array(true, false)), 
            array('=='), 
            'oxy_woo_condition_is_shop',
            'WooCo'
        );

        $this->OxygenConditions->register_condition(
            'Is WooCommerce Page', 
            array('options' => array(true, false)), 
            array('=='), 
            'oxy_woo_condition_is_woocommerce_page',
            'WooCo'
        );

        $this->OxygenConditions->register_condition(
            'Is Product', 
            array('options' => array(true, false)), 
            array('=='), 
            'oxy_woo_condition_is_product',
            'WooCo'
        );

        $this->OxygenConditions->register_condition(
            'Is Cart', 
            array('options' => array(true, false)), 
            array('=='), 
            'oxy_woo_condition_is_cart',
            'WooCo'
        );

        $this->OxygenConditions->register_condition(
            'Is Checkout', 
            array('options' => array(true, false)), 
            array('=='), 
            'oxy_woo_condition_is_checkout',
            'WooCo'
        );

        $this->OxygenConditions->register_condition(
            'Is Account Page', 
            array('options' => array(true, false)), 
            array('=='), 
            'oxy_woo_condition_is_account_page',
            'WooCo'
        );

        $this->OxygenConditions->register_condition(
            'Is Endpoint', 
            array('options' => array(
                                'any',
                                'order-pay',
                                'order-received',
                                'view-order',
                                'edit-account',
                                'edit-address',
                                'lost-password',
                                'customer-logout',
                                'add-payment-method')
            ), 
            $this->OxygenConditions->condition_operators['simple'], 
            'oxy_woo_condition_is_endpoint',
            'WooCo'
        );

    }
}

function oxy_woo_condition_wc_customer_bought_product($product_id, $operator) {
    
    $bought = wc_customer_bought_product("", get_current_user_id(), $product_id);
    
    if ($operator=="==") {
        return $bought;
    }
    else {
        return !$bought;
    }
}

function oxy_woo_condition_product_is_in_cart($product_id, $operator) {

    if (!WC()->cart) {
        return false;
    }

    $product_cart_id = WC()->cart->generate_cart_id( $product_id );
    $in_cart = WC()->cart->find_product_in_cart( $product_cart_id );

    if ($operator=="==") {
        return $in_cart;
    }
    else {
        return !$in_cart;
    }
}

function oxy_woo_condition_is_shop($value, $operator) {

    global $OxygenConditions;

    $is_shop = is_shop();
    $value = (bool) $value;

    return $OxygenConditions->eval_string($is_shop, $value, $operator);
}

function oxy_woo_condition_is_woocommerce_page($value, $operator) {

    global $OxygenConditions;

    $is_woocommerce = is_woocommerce();
    $value = (bool) $value;

    return $OxygenConditions->eval_string($is_woocommerce, $value, $operator);
}

function oxy_woo_condition_is_product($value, $operator) {

    global $OxygenConditions;

    $is_product = is_product();
    $value = (bool) $value;

    return $OxygenConditions->eval_string($is_product, $value, $operator);
}

function oxy_woo_condition_is_cart($value, $operator) {

    global $OxygenConditions;

    $is_cart = is_cart();
    $value = (bool) $value;

    return $OxygenConditions->eval_string($is_cart, $value, $operator);
}

function oxy_woo_condition_is_checkout($value, $operator) {

    global $OxygenConditions;

    $is_checkout = is_checkout();
    $value = (bool) $value;

    return $OxygenConditions->eval_string($is_checkout, $value, $operator);
}


function oxy_woo_condition_is_account_page($value, $operator) {

    global $OxygenConditions;

    $is_account_page = is_account_page();
    $value = (bool) $value;

    return $OxygenConditions->eval_string($is_account_page, $value, $operator);
}

function oxy_woo_condition_is_endpoint($value, $operator) {

    global $OxygenConditions;


    if ($value=='any') { 
        $is_endpoint = is_wc_endpoint_url();
    }
    else {
        $is_endpoint = is_wc_endpoint_url($value);
    }

    return $OxygenConditions->eval_string($is_endpoint, true, $operator);
}