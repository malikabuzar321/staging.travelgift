<?php

defined('ABSPATH') || exit;

require AVLABS_PLUGIN_DIR.'vendor/autoload.php';

use WebPConvert\WebPConvert;
/**
 * @author Vikas Sharma
 */

if(!defined('AVLABS_API_URL'))
{
    define('AVLABS_API_URL' , 'https://avikalabs.com/avlabs-speed-optimization/api/');
}
/// Activation
/// Add default setting on activation
register_activation_hook( AVLABS_PLUGIN_DIR.'/avlabs-speed-optimization.php' , 'avlabs_activation' );

/// Function for Save default setting on active plugin
function avlabs_activation() {
    global  $wp_version;
    $optimization_settings = get_option( 'avlabs_speed_opt_settings' );
    
    if(empty($optimization_settings))
    {
        $default_array = array
        (
            "exclude_urls" => array( ),
            "primary_load_time" => 1500,
            "scondary_load_time" => 10000,
            "dequeue_handler" => array( ),
            "enqueue_handler" => array( ),
            "primary_sort" => array( "0" => 'primary_script_position' ),
            "primary_merge" => array( "0" => 'primary_script_position' ),
            "secondary_sort" => array( "0" => 'secondary_script_position' ),
            "secondary_merge" => array( "0" => 'secondary_script_position' ),
            "font_preload" => array( ),
            "script_manipulation" => array( ),
            "script_url_manipulation" => array( ),
            "html_manipulation" => array( ),
            "critical_css_for_body_class" => array( ),
            "critical_css_before_rocket" => '',
            "critical_css_after_rocket" => '',
            "css_bg_none" => '',
            "css_dequeue_handler" => array( ),
            "css_enqueue_handler" => array( ),
            "exclude_js_files" => array( ),
            "exclude_external_js_files" => array( ),
            "exclude_inline_js" => array( ),
            "css_primary_load_time" => 300,
            "css_bg_load_time" => 2000,
            "css_scondary_load_time" => 10000,
            "logo_div" => '',
            "mobile_menu_selector_index" => 0,
            "mobile_menu_selector" => ''
        );

        update_option( 'avlabs_speed_opt_settings', $default_array );
    }

    // Validate Plugin Activation
    ob_start();
    $args = array(
        'site_url' =>get_bloginfo('url'),
        'admin_email' => get_bloginfo('admin_email'),
        'active'=>'t',
        'api_key' => md5(get_bloginfo('url'))
    );
    $request_string = array(
        'body' => array(
            'action' => 'plugin_activation',
            'request' => serialize($args),
            
        ),
        'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
    );

    // Start checking for an update
    //$raw_response = wp_remote_post('https://avikalabs.com/avlabs-speed-optimization/api/api-key.php', $request_string);
    $raw_response = wp_remote_post(AVLABS_API_URL.'api-key.php', $request_string);
    ob_get_clean();
    
} 

function avlabs_deactivation()
{
    ob_start();
    $args = array(
        'site_url' =>get_bloginfo('url'),
        'admin_email' => get_bloginfo('admin_email'),
        'active'=>'f',
        'api_key' => md5(get_bloginfo('url'))
    );
    $request_string = array(
        'body' => array(
            'action' => 'plugin_deactivation',
            'request' => serialize($args),
            
        ),
        'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
    );

    // Start checking for an update
    //$raw_response = wp_remote_post('https://avikalabs.com/avlabs-speed-optimization/api/api-key.php', $request_string);
    $raw_response = wp_remote_post(AVLABS_API_URL.'api-key.php', $request_string);
    ob_get_clean(); 
}
register_deactivation_hook(AVLABS_PLUGIN_DIR.'/avlabs-speed-optimization.php' , 'avlabs_deactivation' );

class AvlabsAdminSettings
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    private $api_url = AVLABS_API_URL;
    private $plugin_slug = AVLABS_PLUGIN_SLUG;
    /**
     * Start up
     */
    public function __construct()
    {
        // Take over the update check
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_plugin_update'));

        // Take over the Plugin info screen
        add_filter('plugins_api', array($this, 'plugin_api_call'), 10, 3);

        add_action('admin_menu', array($this, 'register_sub_menu_admin_setting'));
        add_action('admin_init', array($this, 'add_setting'));
        add_action('wp_ajax_avlabs_file_upload_font', array($this, 'file_upload_font'));
        add_action('wp_ajax_nopriv_avlabs_file_upload_font', array($this, 'file_upload_font'));
        add_action('wp_ajax_avlabs_remove_font_file', array($this, 'remove_font_file'));
        add_action('wp_ajax_nopriv_avlabs_remove_font_file', array($this, 'remove_font_file'));
        add_action('wp_ajax_avlabs_file_upload_enqueue_handler', array($this, 'file_upload_enqueue_handler'));
        add_action('wp_ajax_nopriv_avlabs_file_upload_enqueue_handler', array($this, 'file_upload_enqueue_handler'));
        add_action('wp_ajax_avlabs_remove_enqueue_handler_file', array($this, 'remove_enqueue_handler_file'));
        add_action('wp_ajax_nopriv_avlabs_remove_enqueue_handler_file', array($this, 'remove_enqueue_handler_file'));
        add_action('wp_ajax_avlabs_file_upload_css_enqueue_handler', array($this, 'file_upload_css_enqueue_handler'));
        add_action('wp_ajax_nopriv_avlabs_file_upload_css_enqueue_handler', array($this, 'file_upload_css_enqueue_handler'));
        add_action('wp_ajax_avlabs_remove_css_enqueue_handler_file', array($this, 'remove_css_enqueue_handler_file'));
        add_action('wp_ajax_nopriv_avlabs_remove_css_enqueue_handler_file', array($this, 'remove_css_enqueue_handler_file'));
        add_action('wp_ajax_avlabs_export_settings_call', array($this, 'export_settings_call'));
        add_action('wp_ajax_nopriv_avlabs_export_settings_call', array($this, 'export_settings_call'));
        add_action('wp_ajax_avlabs_import_file_data', array($this, 'import_file_data'));
        add_action('wp_ajax_nopriv_avlabs_import_file_data', array($this, 'import_file_data'));
        add_action('wp_ajax_avlabs_get_image_folders', array($this, 'get_image_folders'));
        add_action('wp_ajax_nopriv_avlabs_get_image_folders', array($this, 'get_image_folders'));        
        add_action('wp_ajax_avlabs_get_image_count_and_path', array($this, 'get_image_count_and_path'));
        add_action('wp_ajax_nopriv_avlabs_get_image_count_and_path', array($this, 'get_image_count_and_path'));        
        add_action('wp_ajax_avlabs_convert_image_webp', array($this, 'convert_image_webp'));
        add_action('wp_ajax_nopriv_avlabs_convert_image_webp', array($this, 'convert_image_webp'));        
        add_action( 'init', array( $this, 'enqueue_custom_scripts') );
        add_action('init',array($this,'export_settings'),-10);

    }

    function check_for_plugin_update($checked_data)
    {
        global  $wp_version;

        //Comment out these two lines during testing.
        if (empty($checked_data->checked))
            return $checked_data;

        $args = array(
            'slug' => $this->plugin_slug,
            'version' => $checked_data->checked[$this->plugin_slug . '/' . $this->plugin_slug . '.php'],
        );
        $request_string = array(
            'body' => array(
                'action' => 'basic_check',
                'request' => serialize($args),
                'api-key' => md5(get_bloginfo('url'))
            ),
            'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
        );

        // Start checking for an update
        $raw_response = wp_remote_post($this->api_url, $request_string);

        if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
            $response = unserialize($raw_response['body']);

        if (is_object($response) && !empty($response)) // Feed the update data into WP updater
            $checked_data->response[$this->plugin_slug . '/' . $this->plugin_slug . '.php'] = $response;

        return $checked_data;
    }

    function plugin_api_call($def, $action, $args)
    {
        global $wp_version;

        if (!isset($args->slug) || ($args->slug != $this->plugin_slug))
            return false;

        // Get the current version
        $plugin_info = get_site_transient('update_plugins');
        $current_version = $plugin_info->checked[$this->plugin_slug . '/' . $this->plugin_slug . '.php'];
        $args->version = $current_version;

        $request_string = array(
            'body' => array(
                'action' => $action,
                'request' => serialize($args),
                'api-key' => md5(get_bloginfo('url'))
            ),
            'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
        );

        $request = wp_remote_post($this->api_url, $request_string);

        if (is_wp_error($request)) {
            $res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
        } else {
            $res = unserialize($request['body']);

            if ($res === false)
                $res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
        }

        return $res;
    }

    /*
    *  Tabs Scripts
    */
    public function enqueue_custom_scripts()
    {
        wp_register_style('custom_css',  plugin_dir_url(__FILE__) . 'css/custom-css.css', '', '', 'screen');
        wp_enqueue_style('custom_css');
    }

    /**
    * Register Avlabs Speed Setting Submenu
    * @return void
    */
    function register_sub_menu_admin_setting() {
        add_submenu_page( 
            'options-general.php', 'Avlabs Speed Optimization', 'Avlabs Speed', 'manage_options', 'avlabs-speed-optimization', array( $this,'admin_setting_page_html' )
        );
    }


    /**
     * Options Tab navigation callback
     */
    //Admin page html callback
    //Print out html for admin page
    function admin_setting_page_html() {
        // check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
        return;
        }
    
        //Get the active tab from the $_GET param
        $default_tab = null;
        $tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;
    
    ?>
        <!-- Our admin page content should all be inside .wrap -->
        <div class="wrap">
        <!-- Print the page title -->
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <!-- Here are our tabs -->
        <nav class="nav-tab-wrapper">
            <a href="?page=avlabs-speed-optimization" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>">Exclude</a>
            <a href="?page=avlabs-speed-optimization&tab=js" class="nav-tab <?php if($tab==='js'):?>nav-tab-active<?php endif; ?>">Js</a>
            <a href="?page=avlabs-speed-optimization&tab=fontpreload" class="nav-tab <?php if($tab==='fontpreload'):?>nav-tab-active<?php endif; ?>">Font Preload</a>
            <a href="?page=avlabs-speed-optimization&tab=critical_css" class="nav-tab <?php if($tab==='critical_css'):?>nav-tab-active<?php endif; ?>">Critical CSS</a>
            <a href="?page=avlabs-speed-optimization&tab=css" class="nav-tab <?php if($tab==='css'):?>nav-tab-active<?php endif; ?>">CSS</a>
            <a href="?page=avlabs-speed-optimization&tab=html" class="nav-tab <?php if($tab==='html'):?>nav-tab-active<?php endif; ?>">HTML</a>
            <a href="?page=avlabs-speed-optimization&tab=mis" class="nav-tab <?php if($tab==='mis'):?>nav-tab-active<?php endif; ?>">MIS.</a>
            <a href="?page=avlabs-speed-optimization&tab=webp_convert" class="nav-tab <?php if($tab==='webp_convert'):?>nav-tab-active<?php endif; ?>">WebP Convert</a>
            <a href="?page=avlabs-speed-optimization&tab=import_export" class="nav-tab <?php if($tab==='import_export'):?>nav-tab-active<?php endif; ?>">Import/Export</a>
        </nav>
    
        <div class="tab-content">
        <?php switch($tab) :
            case 'js':
                $this->setting_page_callback('admin-js-section');
            break;

            case 'css':
                $this->setting_page_callback('admin-css-section');
            break;

            case 'critical_css':
                $this->setting_page_callback('admin-critical-css-section');
            break;

            case 'fontpreload':
                $this->setting_page_callback('admin-font-reload-section');
            break;

            case 'html':
                $this->setting_page_callback('admin-html-section');
            break;

            case 'mis':
                $this->setting_page_callback('admin-mis-section');
            break;

            case 'import_export':
                $this->setting_page_callback('admin-import-export-section');
            break;

            case 'webp_convert':
                $this->setting_page_callback('admin-webp-convert-section');
            break;
                
            default:
                $this->setting_page_callback('admin-no-chache-section');
            break;
        endswitch; ?>
        </div>
        </div>
        <?php
    }
    /*
    * End function
    */

    /**
     * Options page callback
     */
    public function setting_page_callback($tab)
    {
        // Set class property
        $this->options = get_option( 'avlabs_speed_opt_settings' );
        ?>
        <form method="post" action="options.php" enctype="multipart/form-data">
        <?php
            // This prints out all hidden setting fields
            settings_fields( 'avlabs_admin_group' );
            do_settings_sections( $tab );

            $show_save_btn = true;
            if (isset($_REQUEST['tab'])) {
                if ($_REQUEST['tab'] == 'import_export' || $_REQUEST['tab'] == 'webp_convert') {
                    $show_save_btn = false;
                }
            } 
            
            if ( $show_save_btn )
                submit_button();
            ?>
            </form>
        <?php
    }

    /**
     * Register and add settings for asoft
     */
    public function add_setting()
    {
        // Main Section
        register_setting(
            'avlabs_admin_group', // Option group
            'avlabs_speed_opt_settings', // Option name
            array($this, 'admin_sanitize') // Sanitize
        );

        add_settings_section(
            'avlabs_section_id',
            'Avlabs Admin Settings',
            array($this, 'print_section_info'), // Callback
            'avlabs-admin'
        );

        // No Cache Section Start
        add_settings_section(
            'section_no_cache',
            'Exclude URLs',
            array($this, 'print_section_info'), // Callback
            'admin-no-chache-section'
        );

        add_settings_field(
            'exclude_urls', // PRIMARY LOAD
            'Exclude URLs from Cache',
            array($this, 'exclude_urls_callback'),
            'admin-no-chache-section',
            'section_no_cache'
        );
        // No Cache Section End

        // JS Section Start
        add_settings_section(
            'section_js',
            'JS',
            array($this, 'print_section_info'), // Callback
            'admin-js-section'
        );

        add_settings_field(
            'primary_load_time', // PRIMARY LOAD
            'Primary Load',
            array($this, 'primary_load_callback'),
            'admin-js-section',
            'section_js'
        );

        add_settings_field(
            'scondary_load_time',
            'Secondary Load',
            array($this, 'secondary_load_callback'),
            'admin-js-section',
            'section_js'
        );

        add_settings_field(
            'pre_primary',
            'Pre Primary Js',
            array($this, 'pre_primary_callback'),
            'admin-js-section',
            'section_js'
        );

        add_settings_field(
            'post_primary',
            'Post Primary Js',
            array($this, 'post_primary_callback'),
            'admin-js-section',
            'section_js'
        );

        add_settings_field(
            'pre_secondary',
            'Pre Secondary Js',
            array($this, 'pre_secondary_callback'),
            'admin-js-section',
            'section_js'
        );

        add_settings_field(
            'post_secondary',
            'Post Secondary Js',
            array($this, 'post_secondary_callback'),
            'admin-js-section',
            'section_js'
        );

        add_settings_field(
            'dequeue_handler',
            'Dequeue Handler',
            array($this, 'dequeue_handler_callback'),
            'admin-js-section',
            'section_js'
        );

        add_settings_field(
            'enqueue_handler',
            'Enqueue Handler',
            array($this, 'enqueue_handler_callback'),
            'admin-js-section',
            'section_js'
        );

        add_settings_field(
            'primary_sort',
            'Primary Sort Order',
            array($this, 'primary_sort_callback'),
            'admin-js-section',
            'section_js'
        );

        add_settings_field(
            'primary_merge',
            'Primary Merge Order',
            array($this, 'primary_merge_callback'),
            'admin-js-section',
            'section_js'
        );

        add_settings_field(
            'secondary_sort',
            'Secondary Sort Order',
            array($this, 'secondary_sort_callback'),
            'admin-js-section',
            'section_js'
        );

        add_settings_field(
            'secondary_merge',
            'Secondary Merge Order',
            array($this, 'secondary_merge_callback'),
            'admin-js-section',
            'section_js'
        );

        add_settings_field(
            'script_manipulation',
            'Script manipulation',
            array($this, 'script_manipulation_callback'),
            'admin-js-section',
            'section_js'
        );

        add_settings_field(
            'script_url_manipulation',
            'Script URL manipulation',
            array($this, 'script_url_manipulation_callback'),
            'admin-js-section',
            'section_js'
        );

        add_settings_field(
            'exclude_js_files',
            'Exclude Js Files',
            array($this, 'exclude_js_files_callback'),
            'admin-js-section',
            'section_js'
        );

        add_settings_field(
            'exclude_external_js_files',
            'Exclude External Js Files',
            array($this, 'exclude_external_js_files_callback'),
            'admin-js-section',
            'section_js'
        );

        add_settings_field(
            'exclude_inline_js',
            'Exclude Inline Js',
            array($this, 'exclude_inline_js_callback'),
            'admin-js-section',
            'section_js'
        );
        // JS Section End

        // CSS Section Start
        add_settings_section(
            'section_css',
            'CSS',
            array($this, 'print_section_info'), // Callback
            'admin-css-section'
        );

        add_settings_field(
            'css_bg_none',
            'BG None',
            array($this, 'css_bg_none_callback'),
            'admin-css-section',
            'section_css'
        );

        add_settings_field(
            'css_bg_load_time', // PRIMARY LOAD
            'BG Load',
            array($this, 'css_bg_load_time_callback'),
            'admin-css-section',
            'section_css'
        );

        add_settings_field(
            'css_primary_load_time', // PRIMARY LOAD
            'Primary Load',
            array($this, 'css_primary_load_callback'),
            'admin-css-section',
            'section_css'
        );

        add_settings_field(
            'css_scondary_load_time',
            'Secondary Load',
            array($this, 'css_secondary_load_callback'),
            'admin-css-section',
            'section_css'
        );

        add_settings_field(
            'css_dequeue_handler',
            'Dequeue Handler',
            array($this, 'css_dequeue_handler_callback'),
            'admin-css-section',
            'section_css'
        );

        add_settings_field(
            'css_enqueue_handler',
            'Enqueue Handler',
            array($this, 'css_enqueue_handler_callback'),
            'admin-css-section',
            'section_css'
        );
        // CSS Section End

        // Critical CSS Section Start
         add_settings_section(
            'section_critical_css',
            'CSS',
            array($this, 'print_section_info'), // Callback
            'admin-critical-css-section'
        );

        add_settings_field(
            'critical_css_before_rocket',
            'Critical Css Before Rocket',
            array($this, 'critical_css_before_rocket_callback'),
            'admin-critical-css-section',
            'section_critical_css'
        );

        add_settings_field(
            'critical_css_after_rocket',
            'Critical Css After Rocket',
            array($this, 'critical_css_after_rocket_callback'),
            'admin-critical-css-section',
            'section_critical_css'
        );

        add_settings_field(
            'critical_css_for_body_class',
            'Critical Css For Body Class',
            array($this, 'critical_css_for_body_class_callback'),
            'admin-critical-css-section',
            'section_critical_css'
        );
        // Critical CSS Section End

        // Font Preload Section Start
        add_settings_section(
            'section_font_reload',
            'Font Preload',
            array($this, 'print_section_info'), // Callback
            'admin-font-reload-section'
        );

        add_settings_field(
            'font_preload',
            'Font Preload',
            array($this, 'font_preload_callback'),
            'admin-font-reload-section',
            'section_font_reload'
        );
        // Font Preload Section End

        // HTML Section Start
        add_settings_section(
            'section_html',
            'HTML',
            array($this, 'print_section_info'), // Callback
            'admin-html-section'
        );

        add_settings_field(
            'html_manipulation',
            'HTML Manipulation',
            array($this, 'html_manipulation_callback'),
            'admin-html-section',
            'section_html'
        );
        // HTML Section End

        // MIS Section Start
        add_settings_section(
            'section_mis',
            'MIS.',
            array($this, 'print_section_info'), // Callback
            'admin-mis-section'
        );

        add_settings_field(
            'logo_div',
            'Logo Div',
            array($this, 'logo_div_callback'),
            'admin-mis-section',
            'section_mis'
        );

        add_settings_field(
            'mobile_menu_selector',
            'Mobile Menu Selector',
            array($this, 'mobile_menu_selector_callback'),
            'admin-mis-section',
            'section_mis'
        );

        add_settings_field(
            'mobile_menu_selector_index',
            'Mobile Menu Selector Index',
            array($this, 'mobile_menu_selector_index_callback'),
            'admin-mis-section',
            'section_mis'
        );
        // MIS Section End 

        // Import/Export Section Start
        add_settings_section(
            'section_import_export',
            'Import/Export',
            array($this, 'print_section_info'), // Callback
            'admin-import-export-section'
        );

        add_settings_field(
            'improt_export',
            '',
            array($this, 'import_export_callback'),
            'admin-import-export-section',
            'section_import_export'
        );
        // Import/Export Section End

        // WEBP Convert Section Start
        add_settings_section(
            'section_webp_convert',
            'Convert Image into WebP',
            array($this, 'print_section_info'), // Callback
            'admin-webp-convert-section'
        );

        add_settings_field(
            'convert_image_webp',
            '',
            array($this, 'convert_image_webp_callback'),
            'admin-webp-convert-section',
            'section_webp_convert'
        );
        // WEBP Convert Section End
    }

    /**
     * Function for
     */
    public function admin_sanitize($input)
    {
        $new_input = array();
        $avlabs_setting_data = get_option('avlabs_speed_opt_settings');

        if (isset($input['exclude_urls'])) {
            $new_input['exclude_urls'] = $this->sanitize_textarea_field_to_array('exclude_urls', $input['exclude_urls']);
        } else if (isset($avlabs_setting_data['exclude_urls'])) {   
            $new_input['exclude_urls'] = $avlabs_setting_data['exclude_urls'];
        }        

        if (isset($input['primary_load_time'])) {
            $new_input['primary_load_time'] = sanitize_text_field($input['primary_load_time']);
        } else if (isset($avlabs_setting_data['primary_load_time'])) {   
            $new_input['primary_load_time'] = $avlabs_setting_data['primary_load_time'];
        }

        if (isset($input['scondary_load_time'])) {
            $new_input['scondary_load_time'] = sanitize_text_field($input['scondary_load_time']);
        } else if (isset($avlabs_setting_data['scondary_load_time'])) {   
            $new_input['scondary_load_time'] = $avlabs_setting_data['scondary_load_time'];
        }

        if (isset($input['pre_primary'])) {
            $new_input['pre_primary'] = $this->sanitize_fileupdate_field('pre_primary', $input['pre_primary']);
        } else if (isset($avlabs_setting_data['pre_primary'])) {   
            $new_input['pre_primary'] = $avlabs_setting_data['pre_primary'];
        }

        if (isset($input['post_primary'])) {
            $new_input['post_primary'] = $this->sanitize_fileupdate_field('post_primary', $input['post_primary']);
        } else if (isset($avlabs_setting_data['post_primary'])) {   
            $new_input['post_primary'] = $avlabs_setting_data['post_primary'];
        }

        if (isset($input['pre_secondary'])) {
            $new_input['pre_secondary'] = $this->sanitize_fileupdate_field('pre_secondary', $input['pre_secondary']);
        } else if (isset($avlabs_setting_data['pre_secondary'])) {   
            $new_input['pre_secondary'] = $avlabs_setting_data['pre_secondary'];
        }

        if (isset($input['post_secondary'])) {
            $new_input['post_secondary'] = $this->sanitize_fileupdate_field('post_secondary', $input['post_secondary']);
        } else if (isset($avlabs_setting_data['post_secondary'])) {   
            $new_input['post_secondary'] = $avlabs_setting_data['post_secondary'];
        }

        if (isset($input['dequeue_handler'])) {
            $new_input['dequeue_handler'] = $this->sanitize_textarea_field_to_array('dequeue_handler', $input['dequeue_handler']);
        } else if (isset($avlabs_setting_data['dequeue_handler'])) {   
            $new_input['dequeue_handler'] = $avlabs_setting_data['dequeue_handler'];
        }

        if (isset($input['enqueue_handler'])) {
            $new_input['enqueue_handler'] = $this->sanitize_multiple_field('enqueue_handler', $input['enqueue_handler']);
        } else if (isset($avlabs_setting_data['enqueue_handler'])) {   
            $new_input['enqueue_handler'] = $avlabs_setting_data['enqueue_handler'];
        }

        if (isset($input['primary_sort'])) {
            $new_input['primary_sort'] =  $this->sanitize_textarea_field_to_array('primary_sort', $input['primary_sort']);
        } else if (isset($avlabs_setting_data['primary_sort'])) {   
            $new_input['primary_sort'] = $avlabs_setting_data['primary_sort'];
        }

        if (isset($input['primary_merge'])) {
            $new_input['primary_merge'] =  $this->sanitize_textarea_field_to_array('primary_merge', $input['primary_merge']);
        } else if (isset($avlabs_setting_data['primary_merge'])) {   
            $new_input['primary_merge'] = $avlabs_setting_data['primary_merge'];
        }

        if (isset($input['secondary_sort'])) {
            $new_input['secondary_sort'] =  $this->sanitize_textarea_field_to_array('secondary_sort', $input['secondary_sort']);
        } else if (isset($avlabs_setting_data['secondary_sort'])) {   
            $new_input['secondary_sort'] = $avlabs_setting_data['secondary_sort'];
        }

        if (isset($input['secondary_merge'])) {
            $new_input['secondary_merge'] =  $this->sanitize_textarea_field_to_array('secondary_merge', $input['secondary_merge']);
        } else if (isset($avlabs_setting_data['secondary_merge'])) {   
            $new_input['secondary_merge'] = $avlabs_setting_data['secondary_merge'];
        }

        if (isset($input['font_preload'])) {
            $new_input['font_preload'] =  $this->sanitize_fileupload_field('font_preload', $input['font_preload']);
        } else if (isset($avlabs_setting_data['font_preload'])) {   
            $new_input['font_preload'] = $avlabs_setting_data['font_preload'];
        }

        if (isset($input['script_manipulation'])) {
            $new_input['script_manipulation'] =  $this->sanitize_multiple_field('script_manipulation', $input['script_manipulation']);
        } else if (isset($avlabs_setting_data['script_manipulation'])) {   
            $new_input['script_manipulation'] = $avlabs_setting_data['script_manipulation'];
        }

        if (isset($input['script_url_manipulation'])) {
            $new_input['script_url_manipulation'] =  $this->sanitize_multiple_field('script_url_manipulation', $input['script_url_manipulation']);
        } else if (isset($avlabs_setting_data['script_url_manipulation'])) {   
            $new_input['script_url_manipulation'] = $avlabs_setting_data['script_url_manipulation'];
        }

        if (isset($input['html_manipulation'])) {
            $new_input['html_manipulation'] =  $this->sanitize_multiple_field('html_manipulation', $input['html_manipulation']);
        } else if (isset($avlabs_setting_data['html_manipulation'])) {   
            $new_input['html_manipulation'] = $avlabs_setting_data['html_manipulation'];
        }
        
        if (isset($input['critical_css_for_body_class'])) {
            $new_input['critical_css_for_body_class'] =  $this->sanitize_multiple_field('critical_css_for_body_class', $input['critical_css_for_body_class']);
        } else if (isset($avlabs_setting_data['critical_css_for_body_class'])) {   
            $new_input['critical_css_for_body_class'] = $avlabs_setting_data['critical_css_for_body_class'];
        }

        if (isset($input['critical_css_before_rocket'])) {
            $new_input['critical_css_before_rocket'] = $this->sanitize_textarea_field('critical_css_before_rocket', $input['critical_css_before_rocket']);
        } else if (isset($avlabs_setting_data['critical_css_before_rocket'])) {   
            $new_input['critical_css_before_rocket'] = $avlabs_setting_data['critical_css_before_rocket'];
        }

        if (isset($input['critical_css_after_rocket'])) {
            $new_input['critical_css_after_rocket'] = $this->sanitize_textarea_field('critical_css_after_rocket', $input['critical_css_after_rocket']);
        } else if (isset($avlabs_setting_data['critical_css_after_rocket'])) {   
            $new_input['critical_css_after_rocket'] = $avlabs_setting_data['critical_css_after_rocket'];
        }

        if (isset($input['css_bg_none'])) {
            $new_input['css_bg_none'] = $this->sanitize_textarea_field('css_bg_none', $input['css_bg_none']);
        } else if (isset($avlabs_setting_data['css_bg_none'])) {   
            $new_input['css_bg_none'] = $avlabs_setting_data['css_bg_none'];
        }

        if (isset($input['css_dequeue_handler'])) {
            $new_input['css_dequeue_handler'] = $this->sanitize_textarea_field_to_array('css_dequeue_handler', $input['css_dequeue_handler']);
        } else if (isset($avlabs_setting_data['css_dequeue_handler'])) {   
            $new_input['css_dequeue_handler'] = $avlabs_setting_data['css_dequeue_handler'];
        }

        if (isset($input['css_enqueue_handler'])) {
            $new_input['css_enqueue_handler'] = $this->sanitize_multiple_field('css_enqueue_handler', $input['css_enqueue_handler']);
        } else if (isset($avlabs_setting_data['css_enqueue_handler'])) {   
            $new_input['css_enqueue_handler'] = $avlabs_setting_data['css_enqueue_handler'];
        }

        if (isset($input['exclude_js_files'])) {
            $new_input['exclude_js_files'] = $this->sanitize_textarea_field_to_array('exclude_js_files', $input['exclude_js_files']);
        } else if (isset($avlabs_setting_data['exclude_js_files'])) {   
            $new_input['exclude_js_files'] = $avlabs_setting_data['exclude_js_files'];
        }

        if (isset($input['exclude_external_js_files'])) {
            $new_input['exclude_external_js_files'] = $this->sanitize_textarea_field_to_array('exclude_external_js_files', $input['exclude_external_js_files']);
        } else if (isset($avlabs_setting_data['exclude_external_js_files'])) {   
            $new_input['exclude_external_js_files'] = $avlabs_setting_data['exclude_external_js_files'];
        }

        if (isset($input['exclude_inline_js'])) {
            $new_input['exclude_inline_js'] = $this->sanitize_textarea_field_to_array('exclude_inline_js', $input['exclude_inline_js']);
        } else if (isset($avlabs_setting_data['exclude_inline_js'])) {   
            $new_input['exclude_inline_js'] = $avlabs_setting_data['exclude_inline_js'];
        }

        if (isset($input['css_primary_load_time'])) {
            $new_input['css_primary_load_time'] = sanitize_text_field($input['css_primary_load_time']);
        } else if (isset($avlabs_setting_data['css_primary_load_time'])) {   
            $new_input['css_primary_load_time'] = $avlabs_setting_data['css_primary_load_time'];
        }

        if (isset($input['css_bg_load_time'])) {
            $new_input['css_bg_load_time'] = sanitize_text_field($input['css_bg_load_time']);
        } else if (isset($avlabs_setting_data['css_bg_load_time'])) {   
            $new_input['css_bg_load_time'] = $avlabs_setting_data['css_bg_load_time'];
        }

        if (isset($input['css_scondary_load_time'])) {
            $new_input['css_scondary_load_time'] = sanitize_text_field($input['css_scondary_load_time']);
        } else if (isset($avlabs_setting_data['css_scondary_load_time'])) {   
            $new_input['css_scondary_load_time'] = $avlabs_setting_data['css_scondary_load_time'];
        }

        if (isset($input['logo_div'])) {
            $new_input['logo_div'] = sanitize_text_field($input['logo_div']);
        } else if (isset($avlabs_setting_data['logo_div'])) {   
            $new_input['logo_div'] = $avlabs_setting_data['logo_div'];
        }

        if (isset($input['mobile_menu_selector_index'])) {
            $new_input['mobile_menu_selector_index'] = sanitize_text_field($input['mobile_menu_selector_index']);
        } else if (isset($avlabs_setting_data['mobile_menu_selector_index'])) {   
            $new_input['mobile_menu_selector_index'] = $avlabs_setting_data['mobile_menu_selector_index'];
        }

        if (isset($input['mobile_menu_selector'])) 
        {

            $new_input['mobile_menu_selector'] = sanitize_text_field($input['mobile_menu_selector']);
            $data = $this->set_mobile_menu_selector($new_input['mobile_menu_selector'], $new_input['mobile_menu_selector_index']);

            if (isset($data['file_created'])) 
            {
                if (!in_array($data['file_path'], $new_input['secondary_sort'])) 
                {
                    $new_input['secondary_sort'][] = $data['file_path'];
                }
                if (!in_array($data['file_path'], $new_input['exclude_js_files'])) 
                {
                    $new_input['exclude_js_files'][] = $data['file_path'];
                }

                
                if (count($new_input['enqueue_handler']) == 0) 
                {
                    $new_input['enqueue_handler'][0]['handler_name'] = $data['file_name'];
                    $new_input['enqueue_handler'][0]['file'] = $data['file_name'].'.js';
                } 
                else 
                {
                    $i = 0;
                    foreach ($new_input['enqueue_handler'] as $enqueue_handler) 
                    { 
                        if ( strpos($enqueue_handler['handler_name'],$data['file_name']) !== false ) 
                        {
                            $new_input['enqueue_handler'][$i]['handler_name']    = $data['file_name'];
                            $new_input['enqueue_handler'][$i]['file']            = $data['file_name'].'.js';
                            break;
                        }
                        $i++;
                    }
                    if (count($new_input['enqueue_handler']) == $i) 
                    {
                        $new_input['enqueue_handler'][$i]['handler_name'] =  $data['file_name'];
                        $new_input['enqueue_handler'][$i]['file'] = $data['file_name'].'.js';     
                    }
                }
                

                $i = 0;
                foreach ($new_input['html_manipulation'] as $html_manipulation) 
                {

                    if ( is_numeric(strpos($html_manipulation['to'],"avlabs_mobile_menu_clicked")) ) 
                    {
                        $new_input['html_manipulation'][$i]['from'] = "</body>";
                        $new_input['html_manipulation'][$i]['to'] = $data['html_manipulation_to'];
                        $new_input['html_manipulation'][$i]['action'] = 'post';
                        break;
                    } 
                    $i++;
                }

                if (count($new_input['html_manipulation']) == $i) 
                {
                    $new_input['html_manipulation'][$i]['from'] = "</body>";
                    $new_input['html_manipulation'][$i]['to'] = $data['html_manipulation_to'];
                    $new_input['html_manipulation'][$i]['action'] = 'post';        
                }
                     

            } 
            else
            {
                if (in_array($data['file_path'], $new_input['secondary_sort'])) 
                {
                    $index = array_search($data['file_path'], $new_input['secondary_sort']);
                    unset($new_input['secondary_sort'][$index]);
                }
                if (in_array($data['file_path'], $new_input['exclude_js_files'])) 
                {
                    $index = array_search($data['file_path'], $new_input['exclude_js_files']);
                    unset($new_input['exclude_js_files'][$index]);
                }
                $i = 0;
                foreach ($new_input['html_manipulation'] as $html_manipulation) 
                {

                if ( is_numeric(strpos($html_manipulation['to'],"avlabs_mobile_menu_clicked"))) {
                    unset($new_input['html_manipulation'][$i]);
                }
                    $i++;
                }

                $i = 0;
                foreach ($new_input['enqueue_handler'] as $enqueue_handler) 
                {

                if ( strpos($enqueue_handler['handler_name'],"avlabs-mobile-menu") !== false) {
                    unset($new_input['enqueue_handler'][$i]);
                }
                $i++;
                }
                
            }

        } else if (isset($avlabs_setting_data['mobile_menu_selector'])) 
        {   
            $new_input['mobile_menu_selector'] = $avlabs_setting_data['mobile_menu_selector'];
        }
        return $new_input;
    }

    /**
     * Function for
     */
    public function print_section_info()
    {
        // echo "hello";
    }

    /**
     * Function for
     */
    public function exclude_urls_callback()
    {
    ?>

        <textarea id="exclude_urls" class="avlabs_textarea" name="avlabs_speed_opt_settings[exclude_urls]"><?php echo isset($this->options['exclude_urls']) ?  implode("\n", $this->options['exclude_urls']) : '' ?></textarea>
        <span class="avlabs_tooltip">Enter the url (eg. /url/)</span>

    <?php
    }

    /**
     * Function for
     */
    public function primary_load_callback()
    {
        printf(
            '<input type="number" class="avlabs_textbox" id="primary_load_time" name="avlabs_speed_opt_settings[primary_load_time]" value="%s" />',
            isset($this->options['primary_load_time']) ? esc_attr($this->options['primary_load_time']) : '1500'
        );
    }

    /**
     * Function for
     */
    public function secondary_load_callback()
    {
        printf(
            '<input type="number" class="avlabs_textbox" id="scondary_load_time" name="avlabs_speed_opt_settings[scondary_load_time]" value="%s" />',
            isset($this->options['scondary_load_time']) ? esc_attr($this->options['scondary_load_time']) : '10000'
        );
    }

    /**
     * Function for
     */
    public function pre_primary_callback()
    {
    ?>
        <textarea id="pre_primary" class="avlabs_textarea" name="avlabs_speed_opt_settings[pre_primary]"><?php echo isset($this->options['pre_primary']) ? $this->options['pre_primary'] : '' ?></textarea>
    <?php
    }

    /**
     * Function for
     */
    public function post_primary_callback()
    {
    ?>
        <textarea id="post_primary" class="avlabs_textarea" name="avlabs_speed_opt_settings[post_primary]"><?php echo isset($this->options['post_primary']) ? $this->options['post_primary'] : '' ?></textarea>
    <?php
    }

    /**
     * Function for
     */
    public function pre_secondary_callback()
    {
    ?>
        <textarea id="pre_secondary" class="avlabs_textarea" name="avlabs_speed_opt_settings[pre_secondary]"><?php echo isset($this->options['pre_secondary']) ? $this->options['pre_secondary'] : '' ?></textarea>
    <?php
    }

    /**
     * Function for
     */
    public function post_secondary_callback()
    {
    ?>
        <textarea id="post_secondary" class="avlabs_textarea" name="avlabs_speed_opt_settings[post_secondary]"><?php echo isset($this->options['post_secondary']) ? $this->options['post_secondary'] : '' ?></textarea>
    <?php
    }

    /**
     * Function for
     */
    public function dequeue_handler_callback()
    {
    ?>
        <textarea id="dequeue_handler" class="avlabs_textarea" name="avlabs_speed_opt_settings[dequeue_handler]"><?php echo isset($this->options['dequeue_handler']) ? implode("\n", $this->options['dequeue_handler']) : '' ?></textarea>
    <?php
    }

    /**
     * Function for
     */
    public function enqueue_handler_callback()
    {
    ?>
        <table class="enqueue_handler">
            <thead>
                <tr>
                    <th>Handler Name</th>
                    <th>File</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $row_count = 0;
                if (isset($this->options['enqueue_handler']) && !empty($this->options['enqueue_handler'])) {
                    $enqueue_handler = $this->options['enqueue_handler'];
                    foreach ($enqueue_handler as $handler) {
                        $handler_name = $handler['handler_name'];
                        $file = $handler['file'];
                        if ($row_count > 0) {
                            $row_counts = '_' . $row_count;
                        } else {
                            $row_counts = '';
                        }
                ?>
                        <tr class="enqueue_handler_row avlabs_row" id="enqueue_handler_row<?php echo $row_counts; ?>">
                            <td>
                                <input type="text" id="enqueue_handler_name" name="avlabs_speed_opt_settings[enqueue_handler][handler_name][]" value="<?php echo $handler_name; ?>" />
                            </td>
                            <td>
                                <?php echo $file; ?>
                                <input type="hidden" id="enqueue_handler_files" name="avlabs_speed_opt_settings[enqueue_handler][file][]" value="<?php echo $file; ?>">
                            </td>
                            <td>
                                <?php
                                // if($row_count > 0){
                                echo '<span class="remove_this_enqueue_handler avlabs_remove_btn" id="row_' . $row_count . '" style="">Remove</span>';
                                // } 
                                ?>
                            </td>
                        </tr>
                    <?php
                        $row_count++;
                    } //foreach loop end
                } else {
                    ?>
                    <tr class="enqueue_handler_row avlabs_row" id="enqueue_handler_row<?php echo $row_counts; ?>">
                        <td>
                            <input type="text" id="enqueue_handler_name" name="avlabs_speed_opt_settings[enqueue_handler][handler_name][]" value="" />
                        </td>
                        <td>
                            <input type="file" id="enqueue_handler_file" name="enqueue_handler_file">
                            <input type="hidden" id="enqueue_handler_files" name="avlabs_speed_opt_settings[enqueue_handler][file][]" value="">
                        </td>
                        <td>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"></td>
                    <td><span class="add_this_enqueue_handler button button-primary add_new">Add New</span></td>
                </tr>
            </tfoot>
        </table>

        <script>
            var count_row = <?php if ($row_count > 0) {
                                echo $row_count;
                            } else {
                                echo 1;
                            } ?>;
            jQuery(document).on('click', '.add_this_enqueue_handler', function(event) {

                var newrow = '<tr class="enqueue_handler_row avlabs_row" id="enqueue_handler_row_' + count_row + '"><td><input type="text" id="enqueue_handler_name" name="avlabs_speed_opt_settings[enqueue_handler][handler_name][]" value="" /></td><td><input type="file" id="enqueue_handler_file" name="enqueue_handler_file" > <input type="hidden" id="enqueue_handler_files" name="avlabs_speed_opt_settings[enqueue_handler][file][]" value=""> </td> <td> <span class="remove_this_enqueue_handler avlabs_remove_btn" id="row_' + count_row + '" style="">Remove</span> </td> </tr>';
                jQuery("table.enqueue_handler tbody").append(newrow);
                count_row++;
            });

            jQuery(document).on('click', '.remove_this_enqueue_handler', function(event) {
                $this = jQuery(this);
                var row_remove_id = jQuery(this).parent().siblings('td').find('input[type="hidden"]').val();
                var values = {
                    "action": "avlabs_remove_enqueue_handler_file",
                    "file_name": row_remove_id
                };
                jQuery.ajax({
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    type: 'POST',
                    data: values,
                    success: function(response) {
                        if (response) {

                        }
                    }
                });
                var row_id = $this.parents('tr').attr('id');
                var rowIds = row_id.split('_');
                var id = rowIds[rowIds.length - 1];
                if (jQuery(".enqueue_handler tbody tr").length == 1) {
                    jQuery("#" + row_id + " td input:input").val("");
                    jQuery("#" + row_id + " td").last().html(" ");
                    jQuery("#" + row_id + " td:nth-child(2)").remove();
                    jQuery("#" + row_id + " td").first().after('<td><input type="file" id="enqueue_handler_file" name="enqueue_handler_file" > <input type="hidden" id="enqueue_handler_files" name="avlabs_speed_opt_settings[enqueue_handler][file][]" value=""> </td>');
                    jQuery("#" + row_id + " span").remove();
                } else {
                    jQuery('#' + row_id).remove();
                }
                count_row--;
            });

            jQuery(document).on('change', '#enqueue_handler_file', function() {
                $this = jQuery(this);
                file_data = jQuery(this).prop('files')[0];
                form_data = new FormData();
                form_data.append('enqueue_handler_file', file_data);
                form_data.append('action', 'avlabs_file_upload_enqueue_handler');

                jQuery.ajax({
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    type: 'POST',
                    contentType: false,
                    processData: false,
                    data: form_data,
                    success: function(response) {
                        $this.siblings('#enqueue_handler_files').val(response);
                        $this.hide();
                        $this.parent('td').append(response);
                        var row_id = $this.parents('tr').attr('id');
                        var rowIds = row_id.split('_');
                        var id = rowIds[rowIds.length - 1];
                        if (id == 'row') {
                            $this.parent('td').next().append('<span class="remove_this_enqueue_handler avlabs_remove_btn" id="row_' + count_row + '">Remove</span>');
                        }
                    }
                });
            });
        </script>
    <?php
    }

    /**
     * Function for
     */
    public function primary_sort_callback()
    {
    ?>
        <textarea id="primary_sort" class="avlabs_textarea" style="white-space: pre-line" name="avlabs_speed_opt_settings[primary_sort]"><?php echo isset($this->options['primary_sort']) ?  implode("\n", $this->options['primary_sort']) : 'primary_script_position' ?></textarea>
    <?php
    }

    /**
     * Function for
     */
    public function primary_merge_callback()
    {
    ?>
        <textarea id="primary_merge" class="avlabs_textarea" style="white-space: pre-line" name="avlabs_speed_opt_settings[primary_merge]"><?php echo isset($this->options['primary_merge']) ?  implode("\n", $this->options['primary_merge']) : 'primary_script_position' ?></textarea>
    <?php
    }

    /**
     * Function for
     */
    public function secondary_sort_callback()
    {
    ?>
        <textarea id="secondary_sort" class="avlabs_textarea" style="white-space: pre-line" name="avlabs_speed_opt_settings[secondary_sort]"><?php echo isset($this->options['secondary_sort']) ?  implode("\n", $this->options['secondary_sort']) : 'secondary_script_position' ?></textarea>
    <?php
    }

    /**
     * Function for
     */
    public function secondary_merge_callback()
    {
    ?>
        <textarea id="secondary_merge" class="avlabs_textarea" style="white-space: pre-line" name="avlabs_speed_opt_settings[secondary_merge]"><?php echo isset($this->options['secondary_merge']) ?  implode("\n", $this->options['secondary_merge']) : 'secondary_script_position' ?></textarea>
    <?php
    }

    /**
     * Function for
     */
    public function script_manipulation_callback()
    {
    ?>
        <table class="manipulation">
            <thead>
                <tr>
                    <th>File</th>
                    <th>Action</th>
                    <th>Source</th>
                    <th>Destination</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $row_count = 0;
                if (isset($this->options['script_manipulation']) && !empty($this->options['script_manipulation'])) {
                    $manipulation_data = $this->options['script_manipulation'];
                    foreach ($manipulation_data as $manipulation) {
                        $file = $manipulation['file'];
                        $action = $manipulation['action'];
                        $source = $manipulation['source'];
                        $destination = $manipulation['destination'];
                        if ($row_count > 0) {
                            $row_counts = '_' . $row_count;
                        } else {
                            $row_counts = '';
                        }
                ?>
                        <tr class="manipulation_row avlabs_row" id="manipulation_row<?php echo $row_counts; ?>">
                            <td>
                                <select id="script_manipulation_file" name="avlabs_speed_opt_settings[script_manipulation][file][]">

                                    <option value="primary" <?php if ($file == 'primary') { ?> selected <?php } ?>>primary</option>
                                    <option value="secondary" <?php if ($file == 'secondary') { ?> selected <?php } ?>>secondary</option>
                                </select>
                            </td>
                            <td>
                                <select id="script_manipulation_action" name="avlabs_speed_opt_settings[script_manipulation][action][]">

                                    <option value="replace-in-place" <?php if ($action == 'replace-in-place') { ?> selected <?php } ?>>replace-in-place</option>
                                </select>
                            </td>
                            <td><textarea id="script_manipulation_source" name="avlabs_speed_opt_settings[script_manipulation][source][]"><?php echo $source; ?></textarea></td>
                            <td><textarea id="script_manipulation_destinatio" name="avlabs_speed_opt_settings[script_manipulation][destination][]"><?php echo $destination; ?></textarea></td>
                            <td>
                                <?php
                                if ($row_count > 0) {
                                    echo '<span class="remove_this_manipulation avlabs_remove_btn" id="row_' . $row_count . '" style="">Remove</span>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php
                        $row_count++;
                    } //foreach loop end
                } else {
                    ?>
                    <tr class="manipulation_row avlabs_row">
                        <td>
                            <select id="script_manipulation_file" name="avlabs_speed_opt_settings[script_manipulation][file][]">
                                <option value="">Select</option>
                                <option value="primary">primary</option>
                                <option value="secondary">secondary</option>
                            </select>
                        </td>
                        <td>
                            <select id="script_manipulation_action" name="avlabs_speed_opt_settings[script_manipulation][action][]">
                                <option value="">Select</option>
                                <option value="replace-in-place">replace-in-place</option>
                            </select>
                        </td>
                        <td><textarea id="script_manipulation_source" name="avlabs_speed_opt_settings[script_manipulation][source][]"></textarea></td>
                        <td><textarea id="script_manipulation_destinatio" name="avlabs_speed_opt_settings[script_manipulation][destination][]"></textarea></td>
                        <td></td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"></td>
                    <td><span class="add_this_manipulation button button-primary add_new">Add New</span></td>
                </tr>
            </tfoot>

        </table>
        <script>
            var count_row = <?php if ($row_count > 0) {
                                echo $row_count;
                            } else {
                                echo '1';
                            } ?>;
            jQuery(document).on('click', '.add_this_manipulation', function(event) {

                var newrow = '<tr class="manipulation_row avlabs_row" id="manipulation_row_' + count_row + '"><td><select id="script_manipulation_file" name="avlabs_speed_opt_settings[script_manipulation][file][]" ><option value="" >Select</option> <option value="primary">primary</option> <option value="secondary">secondary</option> </select> </td>  <td> <select id="script_manipulation_action" name="avlabs_speed_opt_settings[script_manipulation][action][]" ><option value="" >Select</option><option value="replace-in-place">replace-in-place</option></select></td><td><textarea id="script_manipulation_source" name="avlabs_speed_opt_settings[script_manipulation][source][]" ></textarea></td><td><textarea id="script_manipulation_destinatio" name="avlabs_speed_opt_settings[script_manipulation][destination][]" ></textarea></td><td><span class="remove_this_manipulation avlabs_remove_btn" id="row_' + count_row + '" style="">Remove</span></td></tr>';
                jQuery("table.manipulation tbody").append(newrow);
            });
            jQuery(document).on('click', '.remove_this_manipulation', function(event) {
                var row_id = jQuery(this).attr('id');
                jQuery("#manipulation_" + row_id).remove();
            });
        </script>
    <?php
    }

    /**
     * Function for
     */
    public function script_url_manipulation_callback()
    {
    ?>
        <table class="manipulation_url">
            <thead>
                <tr>
                    <th>File</th>
                    <th>From</th>
                    <th>To</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $row_count_url = 0;
                if (isset($this->options['script_url_manipulation'])  && !empty($this->options['script_url_manipulation'])) {
                    $manipulation_data = $this->options['script_url_manipulation'];
                    foreach ($manipulation_data as $manipulation) {
                        $from_url   = $manipulation['from'];
                        $to_url     = $manipulation['to'];
                        $file       = $manipulation['file'];
                        if ($row_count_url > 0) {
                            $row_counts_url = '_' . $row_count_url;
                        } else {
                            $row_counts_url = '';
                        }
                ?>
                        <tr class="manipulation_url_row avlabs_row" id="manipulation_row_url<?php echo $row_counts_url; ?>">
                            <td>
                                <select id="script_url_manipulation_file" name="avlabs_speed_opt_settings[script_url_manipulation][file][]">

                                    <option value="primary" <?php if ($file == 'primary') { ?> selected <?php } ?>>primary</option>
                                    <option value="secondary" <?php if ($file == 'secondary') { ?> selected <?php } ?>>secondary</option>
                                </select>
                            </td>
                            <td><input type="text" id="script_url_manipulation_from" name="avlabs_speed_opt_settings[script_url_manipulation][from][]" value="<?php echo $from_url; ?>" /></td>
                            <td><input type="text" id="script_url_manipulation_to" name="avlabs_speed_opt_settings[script_url_manipulation][to][]" value="<?php echo $to_url; ?>" /></td>
                            <td>
                                <?php
                                if ($row_count_url > 0) {
                                    echo '<span class="remove_this_manipulation_url avlabs_remove_btn" id="row_url_' . $row_count_url . '" style="">Remove</span>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php
                        $row_count_url++;
                    } //foreach loop end
                } else {
                    ?>
                    <tr class="manipulation_url_row avlabs_row">
                        <td>
                            <select id="script_url_manipulations_file" name="avlabs_speed_opt_settings[script_url_manipulation][file][]">
                                <option value="">Select</option>
                                <option value="primary">primary</option>
                                <option value="secondary">secondary</option>
                            </select>
                        </td>
                        <td><input type="text" id="script_url_manipulation_from" name="avlabs_speed_opt_settings[script_url_manipulation][from][]" value="" /></td>
                        <td><input type="text" id="script_url_manipulation_to" name="avlabs_speed_opt_settings[script_url_manipulation][to][]" value="" /></td>
                        <td>
                        <td></td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"></td>
                    <td><span class="add_this_manipulation_url button button-primary add_new">Add New</span></td>
                </tr>
            </tfoot>

        </table>
        <script>
            var count_row_url = <?php if ($row_count_url > 0) {
                                    echo $row_count_url;
                                } else {
                                    echo '1';
                                } ?>;
            jQuery(document).on('click', '.add_this_manipulation_url', function(event) {

                var newrowurl = '<tr class="manipulation_row_url avlabs_row" id="manipulation_row_url_' + count_row_url + '"><td><select id="script_url_manipulations_file" name="avlabs_speed_opt_settings[script_url_manipulation][file][]" ><option value="" >Select</option><option value="primary">primary</option><option value="secondary">secondary</option></select></td><td><input type="text" id="script_url_manipulation_from" name="avlabs_speed_opt_settings[script_url_manipulation][from][]" value=""/></td><td><input type="text" id="script_url_manipulation_to" name="avlabs_speed_opt_settings[script_url_manipulation][to][]" value="" /></td>      <td><td><span class="remove_this_manipulation_url avlabs_remove_btn" id="row_url_' + count_row_url + '" style="">Remove</span></td></tr>';
                jQuery("table.manipulation_url tbody").append(newrowurl);
            });
            jQuery(document).on('click', '.remove_this_manipulation_url', function(event) {
                var row_url_id = jQuery(this).attr('id');
                jQuery("#manipulation_" + row_url_id).remove();
            });
        </script>
    <?php
    }

    /**
     * Function for
     */
    public function font_preload_callback()
    {
        printf('<input type="file" id="font_preloads" name="font_preloads" >');
    ?>
        <input type="hidden" id="font_preload" name="avlabs_speed_opt_settings[font_preload]" value="<?php echo isset($this->options['font_preload']) ?  implode(",", $this->options['font_preload']) : '' ?>">
        <table>
            <?php
            if (isset($this->options['font_preload'])) {
                foreach ($this->options['font_preload'] as $font_files) {
                    $file_explode = explode('.', $font_files);
            ?>
                    <tr class="font_file">
                        <td><?php echo $font_files; ?></td>
                        <td><span class="remove_font_file avlabs_remove_btn" id="<?php echo $font_files; ?>">Remove</span></td>
                    </tr>
            <?php
                }
            }
            ?>
        </table>
        <script>
            jQuery('body').on('change', '#font_preloads', function() {
                var before_value = jQuery('#font_preload').val();
                $this = jQuery(this);
                file_data = jQuery(this).prop('files')[0];
                form_data = new FormData();
                form_data.append('font_preloads', file_data);
                form_data.append('action', 'avlabs_file_upload_font');

                jQuery.ajax({
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    type: 'POST',
                    contentType: false,
                    processData: false,
                    data: form_data,
                    success: function(response) {
                        console.log(before_value + response);
                        if (before_value == "" ) {
                            jQuery('#font_preload').val(response);
                        } else {
                            jQuery('#font_preload').val(before_value + "," + response);
                        }
                    }
                });
            });

            jQuery(document).on('click', '.remove_font_file', function(event) {
                $this = jQuery(this);
                var row_remove_id = jQuery(this).attr('id');
                var before_value = jQuery('#font_preload').val();
                var values = {
                    "action": "avlabs_remove_font_file",
                    "file_name": row_remove_id,
                    "last_file_name": before_value
                }
                jQuery.ajax({
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    type: 'POST',
                    data: values,
                    success: function(response) {
                        $this.parents('.font_file').remove();
                        jQuery('#font_preload').val(response);
                    }
                });
            });
        </script>
    <?php
    }

    /**
     * Function for
     */
    public function critical_css_before_rocket_callback()
    {
    ?>
        <textarea id="critical_css_before_rocket" class="avlabs_textarea" name="avlabs_speed_opt_settings[critical_css_before_rocket]"><?php echo isset($this->options['critical_css_before_rocket']) ? $this->options['critical_css_before_rocket'] : '' ?></textarea>
    <?php
    }

    public function critical_css_after_rocket_callback()
    {
    ?>
        <textarea id="critical_css_after_rocket" class="avlabs_textarea" name="avlabs_speed_opt_settings[critical_css_after_rocket]"><?php echo isset($this->options['critical_css_after_rocket']) ? $this->options['critical_css_after_rocket'] : '' ?></textarea>
    <?php
    }


    public function css_bg_none_callback()
    {
    ?>
        <textarea id="css_bg_none" class="avlabs_textarea" name="avlabs_speed_opt_settings[css_bg_none]"><?php echo isset($this->options['css_bg_none']) ? $this->options['css_bg_none'] : '' ?></textarea>
    <?php
    }

    /**
     * Function for
     */
    public function css_dequeue_handler_callback()
    {
    ?>
        <textarea id="css_dequeue_handler" class="avlabs_textarea" name="avlabs_speed_opt_settings[css_dequeue_handler]"><?php echo isset($this->options['css_dequeue_handler']) ? implode("\n", $this->options['css_dequeue_handler']) : '' ?></textarea>
    <?php
    }

    /**
     * Function for
     */
    public function css_enqueue_handler_callback()
    {
    ?>
        <table class="css_enqueue_handler">
            <thead>
                <tr>
                    <th>Handler Name</th>
                    <th>File</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $row_count = 0;
                if (isset($this->options['css_enqueue_handler']) && !empty($this->options['css_enqueue_handler'])) {
                    $dequeue_handler = $this->options['css_enqueue_handler'];
                    foreach ($dequeue_handler as $handler) {
                        $handler_name = $handler['handler_name'];
                        $file = $handler['file'];
                        if ($row_count > 0) {
                            $row_counts = '_' . $row_count;
                        } else {
                            $row_counts = '';
                        }
                ?>
                        <tr class="css_enqueue_handler_row avlabs_row" id="css_enqueue_handler_row<?php echo $row_counts; ?>">
                            <td>
                                <input type="text" id="css_enqueue_handler_name" name="avlabs_speed_opt_settings[css_enqueue_handler][handler_name][]" value="<?php echo $handler_name; ?>" />
                            </td>
                            <td>
                                <?php echo $file; ?>
                                <input type="hidden" id="css_enqueue_handler_files" name="avlabs_speed_opt_settings[css_enqueue_handler][file][]" value="<?php echo $file; ?>">
                            </td>
                            <td>
                                <?php
                                // if($row_count > 0){
                                echo '<span class="remove_this_css_enqueue_handler avlabs_remove_btn" id="row_' . $row_count . '" style="">Remove</span>';
                                //  } 
                                ?>
                            </td>
                        </tr>
                    <?php
                        $row_count++;
                    } //foreach loop end
                } else {
                    ?>
                    <tr class="css_enqueue_handler_row avlabs_row" id="css_enqueue_handler_row<?php echo $row_counts; ?>">
                        <td>
                            <input type="text" id="css_enqueue_handler_name" name="avlabs_speed_opt_settings[css_enqueue_handler][handler_name][]" value="" />
                        </td>
                        <td>
                            <input type="file" id="css_enqueue_handler_file" name="css_enqueue_handler_file">
                            <input type="hidden" id="css_enqueue_handler_files" name="avlabs_speed_opt_settings[css_enqueue_handler][file][]" value="">
                        </td>
                        <td>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"></td>
                    <td><span class="add_this_css_enqueue_handler button button-primary add_new">Add New</span></td>
                </tr>
            </tfoot>
        </table>

        <script>
            var count_row = <?php if ($row_count > 0) {
                                echo $row_count;
                            } else {
                                echo '1';
                            } ?>;
            jQuery(document).on('click', '.add_this_css_enqueue_handler', function(event) {

                var newrow = '<tr class="css_enqueue_handler_row avlabs_row" id="css_enqueue_handler_row_' + count_row + '"><td> <input type="text" id="css_enqueue_handler_name" name="avlabs_speed_opt_settings[css_enqueue_handler][handler_name][]" value=""/></td><td><input type="file" id="css_enqueue_handler_file" name="css_enqueue_handler_file" > <input type="hidden" id="css_enqueue_handler_files" name="avlabs_speed_opt_settings[css_enqueue_handler][file][]" value=""> </td> <td> <span class="remove_this_css_enqueue_handler avlabs_remove_btn" id="row_' + count_row + '" style="">Remove</span> </td> </tr>';
                jQuery("table.css_enqueue_handler tbody").append(newrow);
                count_row++;
            });

            jQuery(document).on('click', '.remove_this_css_enqueue_handler', function(event) {
                $this = jQuery(this);
                var row_remove_id = jQuery(this).parent().siblings('td').find('input[type="hidden"]').val();
                var values = {
                    "action": "avlabs_remove_css_enqueue_handler_file",
                    "file_name": row_remove_id
                };
                jQuery.ajax({
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    type: 'POST',
                    data: values,
                    success: function(response) {
                        if (response) {

                        }
                    }
                });
                var row_id = $this.parents('tr').attr('id');
                var rowIds = row_id.split('_');
                var id = rowIds[rowIds.length - 1];
                if (jQuery(".css_enqueue_handler tbody tr").length == 1) {
                    jQuery("#" + row_id + " td input:input").val("");
                    jQuery("#" + row_id + " td").last().html(" ");
                    jQuery("#" + row_id + " td:nth-child(2)").remove();
                    jQuery("#" + row_id + " td").first().after('<td><input type="file" id="css_enqueue_handler_file" name="css_enqueue_handler_file" > <input type="hidden" id="css_enqueue_handler_files" name="avlabs_speed_opt_settings[css_enqueue_handler][file][]" value=""> </td>');
                    jQuery("#" + row_id + " span").remove();
                } else {
                    jQuery('#' + row_id).remove();
                }
                count_row--;
            });

            jQuery(document).on('change', '#css_enqueue_handler_file', function() {
                $this = jQuery(this);
                file_data = jQuery(this).prop('files')[0];
                form_data = new FormData();
                form_data.append('css_enqueue_handler_file', file_data);
                form_data.append('action', 'avlabs_file_upload_css_enqueue_handler');

                jQuery.ajax({
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    type: 'POST',
                    contentType: false,
                    processData: false,
                    data: form_data,
                    success: function(response) {
                        $this.siblings('#css_enqueue_handler_files').val(response);
                        $this.hide();
                        $this.parent('td').append(response);
                        var row_id = $this.parents('tr').attr('id');
                        var rowIds = row_id.split('_');
                        var id = rowIds[rowIds.length - 1];
                        if (id == 'row') {
                            $this.parent('td').next().append('<span class="remove_this_css_enqueue_handler avlabs_remove_btn" id="row_' + count_row + '">Remove</span>');
                        }
                    }
                });
            });
        </script>
    <?php
    }

    /**
     * Function for
     */
    public function exclude_js_files_callback()
    {
    ?>
        <textarea id="exclude_js_files" class="avlabs_textarea" name="avlabs_speed_opt_settings[exclude_js_files]"><?php echo isset($this->options['exclude_js_files']) ? implode("\n", $this->options['exclude_js_files']) : '' ?></textarea>
    <?php
    }

    /**
     * Function for
     */
    public function exclude_external_js_files_callback()
    {
    ?>
        <textarea id="exclude_external_js_files" class="avlabs_textarea" name="avlabs_speed_opt_settings[exclude_external_js_files]"><?php echo isset($this->options['exclude_external_js_files']) ? implode("\n", $this->options['exclude_external_js_files']) : '' ?></textarea>
    <?php
    }

    /**
     * Function for
     */
    public function exclude_inline_js_callback()
    {
    ?>
        <textarea id="exclude_inline_js" class="avlabs_textarea" name="avlabs_speed_opt_settings[exclude_inline_js]"><?php echo isset($this->options['exclude_inline_js']) ? implode("\n", $this->options['exclude_inline_js']) : '' ?></textarea>
    <?php
    }

    /**
     * Function for
     */
    public function css_primary_load_callback()
    {
        printf(
            '<input type="number" class="avlabs_textbox" id="css_primary_load_time" name="avlabs_speed_opt_settings[css_primary_load_time]" value="%s" />',
            isset($this->options['css_primary_load_time']) ? esc_attr($this->options['css_primary_load_time']) : '300'
        );
    }

    /**
     * Function for
     */
    public function css_bg_load_time_callback()
    {
        printf(
            '<input type="number" class="avlabs_textbox" id="css_bg_load_time" name="avlabs_speed_opt_settings[css_bg_load_time]" value="%s" />',
            isset($this->options['css_bg_load_time']) ? esc_attr($this->options['css_bg_load_time']) : '2000'
        );
    }

    /**
     * Function for
     */
    public function css_secondary_load_callback()
    {
        printf(
            '<input type="number" class="avlabs_textbox" id="css_scondary_load_time" name="avlabs_speed_opt_settings[css_scondary_load_time]" value="%s" />',
            isset($this->options['css_scondary_load_time']) ? esc_attr($this->options['css_scondary_load_time']) : '10000'
        );
    }

    /**
     * Function for
     */
    public function html_manipulation_callback()
    {
    ?>
        <table class="html_manipulation">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>From</th>
                    <th>To</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $row_count_url = 0;
            // echo '<pre>';echo ''; print_r(get_option('avlabs_speed_opt_settings'));echo '</pre>';
                if (isset($this->options['html_manipulation'])  && !empty($this->options['html_manipulation'])) {
                    $manipulation_data = $this->options['html_manipulation'];
                    foreach ($manipulation_data as $manipulation) {
                        $from_url   = $manipulation['from'];
                        $to_url     = $manipulation['to'];
                        $action       = $manipulation['action'];
                        if ($row_count_url > 0) {
                            $row_counts_url = '_' . $row_count_url;
                        } else {
                            $row_counts_url = '';
                        }
                ?>
                        <tr class="html_manipulation_row avlabs_row" id="html_manipulation_row<?php echo $row_counts_url; ?>">
                            <td>
                                <select id="html_manipulation_file" name="avlabs_speed_opt_settings[html_manipulation][action][]">

                                    <option value="pre" <?php if ($action == 'pre') { ?> selected <?php } ?>>Pre</option>
                                    <option value="post" <?php if ($action == 'post') { ?> selected <?php } ?>>Post</option>
                                </select>
                            </td>
                            <td><textarea id="html_manipulation_from" name="avlabs_speed_opt_settings[html_manipulation][from][]"><?php echo $from_url; ?></textarea></td>
                            <td><textarea id="html_manipulation_to" name="avlabs_speed_opt_settings[html_manipulation][to][]"><?php echo $to_url; ?></textarea></td>
                            <td>
                                <?php
                                if ($row_count_url > 0) {
                                    echo '<span class="remove_this_html_manipulation avlabs_remove_btn" id="row_' . $row_count_url . '" style="">Remove</span>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php
                        $row_count_url++;
                    } //foreach loop end
                } else {
                    ?>
                    <tr class="html_manipulation_row avlabs_row">
                        <td>
                            <select id="html_manipulation_action" name="avlabs_speed_opt_settings[html_manipulation][action][]">
                                <option value="">Select</option>
                                <option value="pre">Pre</option>
                                <option value="post">Post</option>
                            </select>
                        </td>
                        <td><textarea id="html_manipulation_from" name="avlabs_speed_opt_settings[html_manipulation][from][]"></textarea></td>
                        <td><textarea id="html_manipulation_to" name="avlabs_speed_opt_settings[html_manipulation][to][]"></textarea></td>
                        <td></td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"></td>
                    <td><span class="add_this_html_manipulation button button-primary add_new">Add New</span></td>
                </tr>
            </tfoot>

        </table>
        <script>
            var count_row_url = <?php if ($row_count_url > 0) {
                                    echo $row_count_url;
                                } else {
                                    echo '1';
                                } ?>;
            jQuery(document).on('click', '.add_this_html_manipulation', function(event) {

                var newrowurl = '<tr class="html_manipulation_row avlabs_row" id="html_manipulation_row_' + count_row_url + '"><td><select id="html_manipulation_action" name="avlabs_speed_opt_settings[html_manipulation][action][]" ><option value="" >Select</option><option value="pre">Pre</option><option value="post">Post</option></select></td><td><textarea id="html_manipulation_from" name="avlabs_speed_opt_settings[html_manipulation][from][]" ></textarea></td><td><textarea id="html_manipulation_to" name="avlabs_speed_opt_settings[html_manipulation][to][]" ></textarea></td> <td><span class="remove_this_html_manipulation avlabs_remove_btn" id="row_' + count_row_url + '" style="">Remove</span></td></tr>';
                jQuery("table.html_manipulation tbody").append(newrowurl);
            });
            jQuery(document).on('click', '.remove_this_html_manipulation', function(event) {
                var row_url_id = jQuery(this).attr('id');
                jQuery("#html_manipulation_" + row_url_id).remove();
            });
        </script>
    <?php
    }

    /**
     * Function for
     */
    public function logo_div_callback()
    {
        printf(
            '<input type="text" class="avlabs_textbox" id="logo_div" name="avlabs_speed_opt_settings[logo_div]" value="%s" />',
            isset($this->options['logo_div']) ? esc_attr($this->options['logo_div']) : ''
        );
    }

    /**
     * Function for
     */
    public function mobile_menu_selector_callback()
    {
        printf(
            '<input type="text" class="avlabs_textbox" id="mobile_menu_selector" name="avlabs_speed_opt_settings[mobile_menu_selector]" value="%s" /><p>(eg. .class_name or #id_name)</p>',
            isset($this->options['mobile_menu_selector']) ? esc_attr($this->options['mobile_menu_selector']) : ''
        );
    }

    /**
     * Function for
     */
    public function mobile_menu_selector_index_callback()
    {
        printf(
            '<input type="number" class="avlabs_textbox" id="mobile_menu_selector" name="avlabs_speed_opt_settings[mobile_menu_selector_index]" value="%s" />',
            isset($this->options['mobile_menu_selector_index']) ?$this->options['mobile_menu_selector_index'] : 0
        );
    }

    /**
     * Function for
     */
    public function import_export_callback()
    {
        echo '<div class="avlabs_setting_footer">';
        echo '<a class="button button-primary avlabs-settings-export">Export</a>';
        echo '<a class="button button-primary avlabs-settings-import">Import</a>';
        echo '</div>';
    ?>
        <script>
            jQuery(document).on('click', '.avlabs-settings-import', function() {
                var html = '<table class="form-table import-file-table" role="presentation"><tbody><tr><th scope="row">Import File</th><td><input type="file" id="import_file" name="import_file">       </td></tr></tbody></table>';
                if (jQuery(".import-file-table").length)
                    jQuery(".import-file-table").remove();
                else 
                    jQuery(this).parent().append(html);
            });

            jQuery(document).on('change', '#import_file', function() {
                $this = jQuery(this);
                file_data = jQuery(this).prop('files')[0];
                form_data = new FormData();
                form_data.append('import_file', file_data);
                form_data.append('action', 'avlabs_import_file_data');

                jQuery.ajax({
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    type: 'POST',
                    contentType: false,
                    processData: false,
                    data: form_data,
                    success: function(response) {
                        console.log(response);
                        $this.parents('.import-file-table').remove();
                        alert("File imported successfully");
                        location.reload();
                    }
                });
            });

            jQuery(document).on('click', '.avlabs-settings-export', function() {
                $this = jQuery(this);

                jQuery.ajax({
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    type: 'POST',
                    data: {action: 'avlabs_export_settings_call'},
                    success: function(response) {
                        console.log(response);
                        location.reload();
                    }
                });
            });
        </script>
    <?php
    }

    public function convert_image_webp_callback() {
        // echo '<a class="button button-primary convert_image_webp avlabs_setting_footer">Convert</a>';
        ?>
        <script>
            jQuery(document).ready( function () {
                $this = jQuery(this);

                jQuery.ajax({
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    type: 'POST',
                    data: {action: 'avlabs_get_image_folders'},
                    success: function(response) {
                        console.log(response);
                        jQuery('.images_folders').html(' ');
                        jQuery('tbody').find('tr').remove();
                        jQuery('tbody').children('.folders_list').remove();
                        jQuery('tbody').append(response);
                       
                    }
                });
            });
            var all_images = 0;
            var converted_images = 0;
            jQuery(document).on('click', '.start_conveting', function () {
                    var folders = new Array();
                    jQuery("input[name='folders']:checked").each(function (index, obj) { 
                        folders.push(jQuery(this).val());
                    });
                    console.log(folders);
                    jQuery.ajax({
                        url: "<?php echo admin_url('admin-ajax.php'); ?>",
                        type: 'POST',
                        data: {action: 'avlabs_get_image_count_and_path', folders: folders},
                        success: function(response) {
                            console.log(response);
                            var data = JSON.parse(response);
                            console.log(data);
                            if (data['total'] > 0) {
                                all_images = data['total'];
                                set_progress_bar();
                                start_convertion(data,0);
                               
                            }
                        }
                    });
                });
                
                var total_converted_size = 0;

                function set_progress_bar() {
                    var percent_completed = 0;
                    var html = '<div class="progress"><div class="percent_complete">'+percent_completed+'%</div><div class="progress-bar"><div class="progress-bar-inner"  style="width:'+percent_completed+'%"> </div></div><div class="clear-both"></div></div>';

                    jQuery(html).insertAfter('.folders_list');
                    jQuery('.folders_list').remove();

                    if (converted_images != 0) {
                        var width = 0;
                        percent_completed = Math.round(converted_images/all_images * 100);

                        console.log(percent_completed);
                        jQuery('.percent_complete').html(percent_completed+"%");
                        jQuery('.progress-bar-inner').css('width', ""+percent_completed+"%" );
                    }                   
                }

                function start_convertion(data,index) {
                    var total_size = data['total_size'];
                    var total_images = data['total'];
                    if (jQuery('.convertion_result').length === 0) {
                        jQuery('<ul class="convertion_result"></ul>').insertAfter('.progress');
                    }
                    // jQuery('.folders_list').remove();

                    if (index < data['images'].length) {
                        var image = data['images'][index]
                        var org_size = image['size'];
                        var webp_size = 0;
                        var file_name = image['path'].split("wp-content/");
                        jQuery('.convertion_result').append('<li>Converting '+file_name[1]+'</li>');

                        jQuery.ajax({
                            url: "<?php echo admin_url('admin-ajax.php'); ?>",
                            type: 'POST',
                            data: {action: 'avlabs_convert_image_webp', image: image['path']},
                            success: function(response) {
                                console.log(response);
                                var result = JSON.parse(response);
                                
                                jQuery('.convertion_result li').last().append('<span class="convertion_status">' + result['status'] + '</span>');
                                if (result['status'] == 'ok') {
                                    var webp_size = result['webpsize'];
                                    var reduction = Math.round((org_size - webp_size)/org_size * 100);
                                    total_converted_size = parseInt(total_converted_size) + parseInt(webp_size)
                                    jQuery('.convertion_result li').last().append('<span class="reduction_status">'+reduction+'%</span>');
                                }
                                converted_images++;
                                set_progress_bar();
                                start_convertion(data,++index);
                                
                            }
                        });
                    } else {
                        var reduction = Math.round((total_size - total_converted_size)/total_size * 100);
                        jQuery('.convertion_result').append('<li class="convertion_completed">Done!</li>');
                        jQuery('.convertion_result').append('<li>Total reduction: <span class="reduction_status">'+reduction+'%</span></li>');
                    }
                }
        </script>
        
        <?php
    }

    /**
     * Function for
     */
    public function critical_css_for_body_class_callback() {
    ?>
        <table class="critical_css_for_body_class">
            <thead>
                <tr>
                    <th>Class</th>
                    <th>Css</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $row_count_url = 0;
            // echo '<pre>';echo ''; print_r(get_option('avlabs_speed_opt_settings'));echo '</pre>';
            // echo '<pre>';echo ''; print_r($this->options);echo '</pre>';exit();
                if (isset($this->options['critical_css_for_body_class'])  && !empty($this->options['critical_css_for_body_class'])) {
                    $critical_css_data = $this->options['critical_css_for_body_class'];
                    foreach ($critical_css_data as $critical_css) {
                        $classes   = $critical_css['classes'];
                        $css        = $critical_css['css'];
                        if ($row_count_url > 0) {
                            $row_counts_url = '_' . $row_count_url;
                        } else {
                            $row_counts_url = '';
                        }
                ?>
                        <tr class="critical_css_for_body_class_row avlabs_row" id="critical_css_for_body_class_row<?php echo $row_counts_url; ?>">
                            <td><textarea id="critical_css_for_body_class_classes" name="avlabs_speed_opt_settings[critical_css_for_body_class][classes][]"><?php echo $classes; ?></textarea></td>
                            <td><textarea id="critical_css_for_body_class_css" name="avlabs_speed_opt_settings[critical_css_for_body_class][css][]"><?php echo $css; ?></textarea></td>
                            <td>
                                <?php
                                if ($row_count_url > 0) {
                                    echo '<span class="remove_this_critical_css_for_body_class avlabs_remove_btn" id="row_' . $row_count_url . '" style="">Remove</span>';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php
                        $row_count_url++;
                    } //foreach loop end
                } else {
                    ?>
                    <tr class="critical_css_for_body_class_row avlabs_row">
                        <td><textarea id="critical_css_for_body_class_classes" name="avlabs_speed_opt_settings[critical_css_for_body_class][classes][]"></textarea></td>
                        <td><textarea id="critical_css_for_body_class_css" name="avlabs_speed_opt_settings[critical_css_for_body_class][css][]"></textarea></td>
                        <td></td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"></td>
                    <td><span class="add_this_critical_css_for_body_class button button-primary add_new">Add New</span></td>
                </tr>
            </tfoot>

        </table>
        <script>
            var count_row_url = <?php if ($row_count_url > 0) {
                                    echo $row_count_url;
                                } else {
                                    echo '1';
                                } ?>;
            jQuery(document).on('click', '.add_this_critical_css_for_body_class', function(event) {

                var newrowurl = '<tr class="critical_css_for_body_class_row avlabs_row" id="critical_css_for_body_class_row_' + count_row_url + '"><td><textarea id="critical_css_for_body_class_classes" name="avlabs_speed_opt_settings[critical_css_for_body_class][classes][]" ></textarea></td><td><textarea id="critical_css_for_body_class_css" name="avlabs_speed_opt_settings[critical_css_for_body_class][css][]" ></textarea></td> <td><span class="remove_this_critical_css_for_body_class avlabs_remove_btn" id="row_' + count_row_url + '" style="">Remove</span></td></tr>';
                jQuery("table.critical_css_for_body_class tbody").append(newrowurl);
            });
            jQuery(document).on('click', '.remove_this_critical_css_for_body_class', function(event) {
                var row_url_id = jQuery(this).attr('id');
                jQuery("#critical_css_for_body_class_" + row_url_id).remove();
            });
        </script>
    <?php
    }

    public function sanitize_textarea_field($field, $value) 
    {
        $fields = [

            'critical_css_before_rocket'       => ['sanitize_text_field'], //     Pattern.
            'critical_css_after_rocket'       => ['sanitize_text_field'], // pattern
            'css_bg_none'       => ['sanitize_text_field'], // pattern
            'css_dequeue_handler'       => ['sanitize_text_field'], // Pattern.
        ];

        if (!isset($fields[$field])) {
            return null;
        }


        return $value;
    }

    /**
     * Function for
     */
    public function sanitize_textarea_field_to_array($field, $value)
    {
        $fields = [
            'enqueue_handler'           => ['sanitize_text_field'], // Pattern.
            'dequeue_handler'           => ['sanitize_text_field'], // Pattern.
            'primary_sort'              => ['sanitize_text_field'], // Pattern.
            'primary_merge'             => ['sanitize_text_field'], // Pattern.
            'secondary_sort'            => ['sanitize_text_field'], // Pattern.
            'secondary_merge'           => ['sanitize_text_field'], // Pattern.
            'exclude_urls'             => ['sanitize_text_field'], // Pattern.
            'exclude_js_files'          => ['sanitize_text_field'], // Pattern.
            'exclude_external_js_files'          => ['sanitize_text_field'], // Pattern.
            'exclude_inline_js'         => ['sanitize_text_field'], // Pattern.
            'css_dequeue_handler'       => ['sanitize_text_field'], // Pattern.
        ];

        if (!isset($fields[$field])) {
            return null;
        }

        $sanitizations = $fields[$field];

        if (!is_array($value)) {
            $value = explode("\n", $value);
        }

        $value = array_map('trim', $value);
        $value = array_filter($value);

        if (!$value) {
            return [];
        }

        // Sanitize.
        foreach ($sanitizations as $sanitization) {
            $value = array_map($sanitization, $value);
        }

        return array_unique($value);
    }

    /**
     * Function for
     */
    public function sanitize_fileupdate_field($field, $value)
    {
        $fields = [
            'pre_primary'       => ['sanitize_text_field'], // Pattern.
            'post_primary'      => ['sanitize_text_field'], // Pattern.
            'pre_secondary'     => ['sanitize_text_field'], // Pattern.
            'post_secondary'    => ['sanitize_text_field'], // Pattern.
            // 'critical_css'      => [ 'sanitize_text_field' ], // Pattern.
        ];

        if (!isset($fields[$field])) {
            return null;
        }
        $file_name = str_replace("_", "-", $field) . ".js";
        if ($value != "") {

            if ($field == 'critical_css') {
                $fp = fopen(AVLABS_PLUGIN_DIR . "/css-critical/css-critical.css", 'w'); //opens file in append mode  
            } else {
                if (!is_dir(AVLABS_JS_DIR)) {
                    mkdir(AVLABS_JS_DIR);
                }
                $fp = fopen(AVLABS_JS_DIR . $file_name, 'w'); //opens file in append mode 
            }
            fwrite($fp, $value);
            fclose($fp);
            return $value;
        } else {
            $this->remove_file(AVLABS_JS_DIR . $file_name);
            return null;
        }
    }

    /**
     * Function for
     */
    public function sanitize_multiple_field($field, $value)
    {
        $fields = [
            'script_manipulation'       => ['sanitize_text_field'], // Pattern.
            'script_url_manipulation'   => ['sanitize_text_field'], // Pattern.
            'html_manipulation'         => ['sanitize_text_field'], // Pattern.
            'enqueue_handler'           => ['sanitize_text_field'], // Pattern.
            'css_enqueue_handler'       => ['sanitize_text_field'], // Pattern.
            'critical_css_for_body_class'     => ['sanitize_text_field'], // Pattern.
        ];

        if (!isset($fields[$field])) {
            return null;
        }

        $sanitizations = $fields[$field];

        if (!$value) {
            return [];
        }

        $data = array();

        if ($field == 'script_manipulation') {
            $field_index = 0;
            for ($i = 0; $i < count($value['file']); $i++) {
                if (!empty($value['file'][$i]) && !empty($value['action'][$i]) && !empty($value['source'][$i])) {
                    $data[$field_index]['file']        = $value['file'][$i];
                    $data[$field_index]['action']      = $value['action'][$i];
                    $data[$field_index]['source']      = $value['source'][$i];
                    $data[$field_index]['destination'] = $value['destination'][$i];
                    $field_index++;
                }
            }
        } else if ($field == 'script_url_manipulation') {
            $field_index = 0;
            for ($i = 0; $i < count($value['from']); $i++) {
                if (!empty($value['file'][$i]) && !empty($value['from'][$i])) {
                    $data[$i]['from']       = $value['from'][$i];
                    $data[$i]['to']         = $value['to'][$i];
                    $data[$i]['file']       = $value['file'][$i];
                    $field_index++;
                }
            }
        } else if ($field == 'html_manipulation') {
            $field_index = 0;
            for ($i = 0; $i < count($value['from']); $i++) {
                if (!empty($value['action'][$i]) && !empty($value['from'][$i])) {
                    $data[$i]['from']       = $value['from'][$i];
                    $data[$i]['to']         = $value['to'][$i];
                    $data[$i]['action']       = $value['action'][$i];
                    $field_index++;
                }
            }
        } else if ($field == 'enqueue_handler' || $field == 'css_enqueue_handler') {
            $field_index = 0;
            for ($i = 0; $i < count($value['handler_name']); $i++) {
                if (!empty($value['handler_name'][$i]) && !empty($value['file'][$i])) {
                    $data[$i]['handler_name']    = $value['handler_name'][$i];
                    $data[$i]['file']            = $value['file'][$i];
                    $field_index++;
                } else if (!empty($value['file'][$i])) {
                    if ($field == 'enqueue_handler')
                        $this->remove_file(AVLABS_JS_DIR . $value['file'][$i]);
                    else if ($field == 'css_enqueue_handler')
                        $this->remove_file(AVLABS_CSS_SECONDARY_DIR . $value['file'][$i]);
                }
            }
        } else if ($field == 'critical_css_for_body_class') {

            $field_index = 0;
            for ($i = 0; $i < count($value['classes']); $i++) {
                if (!empty($value['classes'][$i]) && !empty($value['css'][$i])) {
                    
                    $data[$i]['classes']    = $value['classes'][$i];
                    $data[$i]['css']         = $value['css'][$i];
                    $field_index++;
                }
            }
        }

        // Sanitize.

        return $data;
    }

    /**
     * Function for
     */
    public function sanitize_fileupload_field($field, $value)
    {
        $fields = [
            'font_preload'    => ['sanitize_text_field'], // Pattern.
        ];

        if (!isset($fields[$field])) {
            return null;
        }

        $sanitizations = $fields[$field];

        if (!is_array($value)) {
            $value = explode(",", $value);
        }

        $value = array_map('trim', $value);
        $value = array_filter($value);
        
        if (!$value) {
            return [];
        }

        // Sanitize.
        foreach ($sanitizations as $sanitization) {
            $value = array_map($sanitization, $value);
        }
        return array_unique($value);
    }

    /*
    * Function for
    */
    public function file_upload_font()
    {

        $fileTmpPath = $_FILES['font_preloads']['tmp_name'];
        $fileName = $_FILES['font_preloads']['name'];
        $fileSize = $_FILES['font_preloads']['size'];
        $fileType = $_FILES['font_preloads']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $newFileName = $fileName;

        // directory in which the uploaded file will be moved
        $uploadFileDir = AVLABS_FONTS_PRELOAD_DIR . '/';
        $dest_path = $uploadFileDir . $newFileName;

        if ($dest_path != '' || $dest_path != 0) {
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                
                echo $newFileName;
            }
        }

        die;
    }

    /*
    * Function for
    */
    public function file_upload_enqueue_handler()
    {
        $fileTmpPath = $_FILES['enqueue_handler_file']['tmp_name'];
        $fileName = $_FILES['enqueue_handler_file']['name'];
        $fileSize = $_FILES['enqueue_handler_file']['size'];
        $fileType = $_FILES['enqueue_handler_file']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $newFileName = $fileName;

        // directory in which the uploaded file will be moved
        $uploadFileDir = AVLABS_JS_DIR;
        if (!is_dir(AVLABS_JS_DIR)) {
            mkdir(AVLABS_JS_DIR);
        }
        $dest_path = $uploadFileDir . $newFileName;

        if ($dest_path != '' || $dest_path != 0) {
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                echo $newFileName;
            }
        }

        die;
    }


    /*
    * Function for
    */
    public function file_upload_css_enqueue_handler()
    {
        $fileTmpPath = $_FILES['css_enqueue_handler_file']['tmp_name'];
        $fileName = $_FILES['css_enqueue_handler_file']['name'];
        $fileSize = $_FILES['css_enqueue_handler_file']['size'];
        $fileType = $_FILES['css_enqueue_handler_file']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $newFileName = $fileName;

        // directory in which the uploaded file will be moved
        if (!is_dir(AVLABS_CSS_SECONDARY_DIR)) {
            mkdir(AVLABS_CSS_SECONDARY_DIR);
        }
        $uploadFileDir = AVLABS_CSS_SECONDARY_DIR;
        $dest_path = $uploadFileDir . $newFileName;

        if ($dest_path != '' || $dest_path != 0) {
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                echo $newFileName;
            }
        }

        die;
    }

    /*
    * Function for
    */
    public function remove_font_file()
    {
        $oldFlieName = $_POST['last_file_name'];
        $newFileName = $_POST['file_name'];
        $return_data = str_replace($newFileName, "", $oldFlieName);
        $return_data = str_replace(",,", ",", $return_data);
        $return_data = trim($return_data, ',');
        // directory in which the uploaded file will be moved
        $uploadFileDir = AVLABS_FONTS_PRELOAD_DIR . '/';
        $dest_path = $uploadFileDir . $newFileName;

        if ($dest_path != '' || $dest_path != 0) {
            if (unlink($dest_path)) {
                echo $return_data;
            }
        }

        die;
    }

    /*
    * Function for
    */
    public function remove_enqueue_handler_file()
    {
        $newFileName = $_POST['file_name'];
        // directory in which the uploaded file will be moved
        $uploadFileDir = AVLABS_JS_DIR;
        $dest_path = $uploadFileDir . $newFileName;

        if ($dest_path != '' || $dest_path != 0) {
            if (unlink($dest_path)) {
                echo true;
            } else {
                echo false;
            }
        } else {
            echo false;
        }

        die;
    }

    /*
    * Function for
    */
    public function remove_css_dequeue_handler_file()
    {
        $newFileName = $_POST['file_name'];
        // directory in which the uploaded file will be moved
        $uploadFileDir = AVLABS_PLUGIN_DIR . 'css-critical/';
        $dest_path = $uploadFileDir . $newFileName;

        if ($dest_path != '' || $dest_path != 0) {
            if (unlink($dest_path)) {
                echo true;
            }
        }

        die;
    }

    /*
    * Function for
    */
    public function remove_css_enqueue_handler_file()
    {
        $newFileName = $_POST['file_name'];
        // directory in which the uploaded file will be moved
        
        $dest_path = AVLABS_CSS_SECONDARY_DIR . $newFileName;

        if ($dest_path != '' || $dest_path != 0) {
            if (unlink($dest_path)) {
                echo true;
            } else {
                echo false;
            }
        } else {
            echo false;
        }

        die;
    }

    /*
    * Function for
    */
    public function remove_file($file)
    {
        if ($file != '' || $file != 0) {
            if (file_exists($file)) {
                if (unlink($file)) {
                    return true;
                }
            }
        }
    }

    

    /*
    *  Function for export settings
    */
    public function export_settings() {
        
        if (isset($_COOKIE['avlabs_export_settings']) && $_COOKIE['avlabs_export_settings'] != "") {

           //get avlabs settings data
            $options = get_option('avlabs_speed_opt_settings');
            setcookie('avlabs_export_settings', "", time() - 3600, "/");

            //download avlabs settings file
            header('Content-Disposition: attachment; filename=avlabs-settings.json');
            header("Content-Type: text/plain");
            
            ob_clean();
            flush();
            
            $string = json_encode($options);
            
            echo $string;
            exit();
        }
    }

    /*
    *  Function for import settings
    */
    public function import_file_data()
    {
        global $wpdb;
        $newFileName = $_FILES['import_file']['tmp_name'];
        $txt = fopen($newFileName, "r");
        $data = '';
        while (!feof($txt)) {
            $data = fgets($txt);
        }
        
        $settings = json_decode($data, true);
        update_option('avlabs_speed_opt_settings',$settings);
        die;
    }

    public function export_settings_call() {
        setcookie('avlabs_export_settings', "export", time() + (86400 * 30), "/");
    }

    /**
     * Function for set mobile menu selector js
     */
    public function set_mobile_menu_selector($selector, $selector_index) 
    {
        $file_name = 'avlabs-mobile-menu';
        $file_url = AVLABS_JS_URL.$file_name.'.js';
        $file_path = AVLABS_JS_DIR.$file_name.'.js';
        $response = array();
        $response['file_name'] = $file_name;
        $response['file_path'] = explode($_SERVER['HTTP_HOST'],$file_url)[1];

        if (empty($selector) || (strpos($selector,'.') === false && strpos($selector,'#') === false) ) {
            return $response;
        }
        
        $script = '<script> var avlabs_mobile_menu_clicked = false;';
        $selector_name = substr($selector,1);

        $file = fopen($file_path, 'w');
        $js = ' if(avlabs_mobile_menu_clicked){
                    jQuery("'.$selector.'").trigger("click");
                }'; 

        fwrite($file, $js);
        fclose($file);
        $response['file_created'] = true;

        if(strpos($selector,'.') !== false) {
            $script .= "var avlabs_mobile_menu = document.getElementsByClassName('$selector_name')[$selector_index];";
        } else if(strpos($selector,'#') !== false) {
            $script = $script ."var avlabs_mobile_menu = document.getElementById('$selector_name');";
        } 
        $script = $script .'avlabs_mobile_menu.addEventListener("click", function(){
                avlabs_mobile_menu_clicked = true;
                });
            </script></body>';

        $response['html_manipulation_to'] = $script;

        return $response;
    }

    public function get_image_count_and_path() {
        $remaining_dirs = $_POST['folders'];
        $total_images = 0;
        $total_size = 0;
        $response = array();

        $i = 0;
        while(!empty($remaining_dirs)) {

            $selected_path = array_values($remaining_dirs)[0];
            array_shift($remaining_dirs);

            $images = glob($selected_path."/*.{jpg,jpeg,png,tif,jfif}", GLOB_BRACE);

            if (!empty($images)) {
                // $total_images = $total_images + count($images);
                
                foreach ($images as $image) {
                    $file = str_split($image, strrpos($image,"/")+1);
                    $file_folder = $file[0];
                    $file_name = $file[1];

                    if (!file_exists("$file_folder/$file_name.webp")) { 
                        $total_images++;
                        $total_size = $total_size + filesize($image);
                        $response['images'][$i]['path'] = $image;
                        $response['images'][$i]['size'] = filesize($image);
                        $i++;
                    }
                    
                }
            }
        }
       $response['total'] = $total_images;
        $response['total_size'] = $total_size;
       echo json_encode($response);
        exit();
    }

    public function convert_image_webp() {
        // echo '<pre>';print_r($_POST['image']);echo '</pre>';
        $response = array();
        $image = $_POST['image'];
        $file = str_split($image, strrpos($image,"/")+1);

        $selected_path = $file[0];
        $file_name = str_split($file[1], strrpos($file[1],"."))[0];
        $file_name = $file[1];

        $destination_path = $selected_path;
        $f_destination_path = $destination_path . "/" . $file_name . ".webp";

        $options = [];
        $data = WebPConvert::convert($image, $f_destination_path, $options);
        
        if (!filesize($f_destination_path)) {
            $response['status']="fali";
        } else {
            $response['status']="ok";
            $response['webpsize']=filesize($f_destination_path);
        }
        echo json_encode($response);
        exit();
    }

    public function get_image_folders() {
        $upload_path = wp_upload_dir()['basedir'];
        $remaining_dirs = array($upload_path);
        $image_folders = array();
        
        $i = 0;
        while(!empty($remaining_dirs)) {
            $selected_path = array_values($remaining_dirs)[0];
            array_shift($remaining_dirs);
            $total_images = 0;
            $images = glob($selected_path."/*.{jpg,jpeg,png,tif,jfif}", GLOB_BRACE);
            
            if (!empty($images)) {
                
                foreach($images as $image) {
                    $file_exists = false;
                    $file = str_split($image, strrpos($image,"/")+1);
                    $file_folder = $file[0];
                    $file_name = $file[1];

                    if (file_exists("$file_folder/$file_name.webp")) {
                        $file_exists = true;
                    }

                    if (!$file_exists && !in_array($selected_path, $image_folders)) {
                        // echo $selected_path .'<br>';
                        // echo $i .'<br>';
                        $image_folders[$i]['folder'] = $selected_path;
                        $total_images++;
                    }
                }   
                if (isset($image_folders[$i]['folder'])) {
                    $image_folders[$i]['size'] = $total_images; 
                }            
            }

            $dirs = array_filter(glob($selected_path.'/*'), 'is_dir');   
            if (!empty($dirs)) {
                foreach($dirs as $dir) {
                    $remaining_dirs[] = $dir;
                }
            }
           // print_r($remaining_dirs);
            $i++;            
        }
        // echo '<pre>';print_r($image_folders);echo '</pre>';
        // exit();
        if (!empty($image_folders) ) {        
           echo '<ul class="folders_list">';
            foreach($image_folders as $folder) {
                echo '<li>';
                $name = substr($folder['folder'],strpos($folder['folder'], "uploads"));
                echo $name . " <input type='checkbox' name='folders' value='".$folder['folder']."'> (".$folder['size'].")";
                echo '</li>';
            }
            echo '<li>';
            echo '<a class="button button-primary start_conveting">Start</a>';
            echo '</li></ul>';
        
        }  else {
            echo '<ul class="folders_list"><li>There are no unconverted files</li></ul>';
        } 
        exit();
    }
}


if (is_admin())
    new AvlabsAdminSettings();