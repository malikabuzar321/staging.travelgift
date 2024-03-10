<?php
namespace Pagup\Bialty;
use Pagup\Bialty\Core\Asset;
use Pagup\Bialty\Controllers\DomController;
use Pagup\Bialty\Controllers\NoticeController;
use Pagup\Bialty\Controllers\MetaboxController;
use Pagup\Bialty\Controllers\SettingsController;

//require \Pagup\Bialty\Core\Plugin::path('vendor/persist-admin-notices-dismissal/persist-admin-notices-dismissal.php');

class Settings {

    public function __construct()
    {
        $settings = new SettingsController;
        $metabox = new MetaboxController;

        // Add settings page
        add_action( 'admin_menu', array( &$settings, 'add_settings' ) );

        // Add metabox to post-types
        add_action( 'add_meta_boxes', array(&$metabox, 'add_metabox') );

        // Save meta data
        add_action( 'save_post', array(&$metabox, 'metadata'));

        // Add setting link to plugin page
        $plugin_base = BIALTY_PLUGIN_BASE;
        add_filter( "plugin_action_links_{$plugin_base}", array( &$this, 'setting_link' ) );

        // Add styles and scripts
        add_action( 'admin_enqueue_scripts', array( &$this, 'assets') );

    }

    public function setting_link( $links ) {

        array_unshift( $links, '<a href="admin.php?page=bialty">Settings</a>' );
        return $links;
    }

    public function assets() {

        Asset::style('bialty_styles', 'app.css');
        Asset::script('bialty_script', 'app.js');
    
    }
}

$settings = new Settings;
