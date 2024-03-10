<?php
if(!class_exists('PaidOnResults_WooCommerce_Settings'))
{
        class PaidOnResults_WooCommerce_Settings
        {
                /**
                 * Construct the plugin object
                 */
                public function __construct()
                {
                        // register actions
            add_action('admin_init', array(&$this, 'admin_init'));
                add_action('admin_menu', array(&$this, 'add_menu'));
                } // END public function __construct
                
        /**
         * hook into WP's admin_init action hook
         */
        public function admin_init()
        {
                // register your plugin's settings
                register_setting('paidonresults_woocommerce-group', 'setting_a');
                register_setting('paidonresults_woocommerce-group', 'setting_b');

                // add your settings section
                add_settings_section(
                    'paidonresults_woocommerce-section', 
                    'Paid On Results - WooCommerce - Settings', 
                    array(&$this, 'settings_section_paidonresults_woocommerce'), 
                    'paidonresults_woocommerce'
                );
                
                // add your setting's fields
            add_settings_field(
                'paidonresults_woocommerce-setting_a', 
                'Merchant Account ID', 
                array(&$this, 'settings_field_input_text'), 
                'paidonresults_woocommerce', 
                'paidonresults_woocommerce-section',
                array(
                    'field' => 'setting_a'
                )
            );
            add_settings_field(
                'paidonresults_woocommerce-setting_b', 
                'VAT', 
                array(&$this, 'settings_field_input_text'), 
                'paidonresults_woocommerce', 
                'paidonresults_woocommerce-section',
                array(
                    'field' => 'setting_b'
                )
            );
            // Possibly do additional admin_init tasks
        } // END public static function activate
        
        public function settings_section_paidonresults_woocommerce()
        {
            // Think of this as help text for the section.
            echo '<strong>Merchant Account ID</strong> = the number supplied by <a href="http://www.paidonresults.com/">Paid On Results</a>. If you do not have the number, ask for it from your account manager or the staff member helping get your program live.<p><strong>Exclude VAT</strong> = No/Yes, if you have setup WooCommerce for a standard shop that shows the end customer the total amount including VAT then you can choose to pay commission on the order value excluding VAT by entering YES into this box.';
        }
        
        /**
         * This function provides text inputs for settings fields
         */
        public function settings_field_input_text($args)
        {
            // Get the field name from the $args array
            $field = $args['field'];
            // Get the value of this setting
            $value = get_option($field);
            // echo a proper input type="text"
            echo sprintf('<input type="text" name="%s" id="%s" value="%s" />', $field, $field, $value);
        } // END public function settings_field_input_text($args)
        
        /**
         * add a menu
         */                
        public function add_menu()
        {
            // Add a page to manage this plugin's settings
                add_options_page(
                    'Paid On Results - WooCommerce Settings', 
                    'Paid On Results - WooCommerce', 
                    'manage_options', 
                    'paidonresults_woocommerce', 
                    array(&$this, 'plugin_settings_page')
                );
        } // END public function add_menu()
    
        /**
         * Menu Callback
         */                
        public function plugin_settings_page()
        {
                if(!current_user_can('manage_options'))
                {
                        wp_die(__('You do not have sufficient permissions to access this page.'));
                }
        
                // Render the settings template
                include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
        } // END public function plugin_settings_page()
    } // END class PaidOnResults_WooCommerce_Settings
} // END if(!class_exists('PaidOnResults_WooCommerce_Settings'))