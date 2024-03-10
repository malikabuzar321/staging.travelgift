<?php
/*
Plugin Name: Auto Image Alt
Plugin URI: http://wordpress.org/plugins/auto-image-alt/
Description: Adds automatically alt attributes in image tag where alt attributes are missing or alt is empty.
Author: jaggskrist
Version: 1.2
Author URI: https://profiles.wordpress.org/jaggskrist
*/
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if(!class_exists('jk_auto_image_alt')) {
class jk_auto_image_alt {
	public function __construct() {
        add_action('init', array(&$this, 'auto_image_alt_scripts'));
		add_action('admin_menu', array(&$this, 'auto_image_alt_menu_page')); 
        register_activation_hook(__FILE__, array(&$this, 'auto_image_alt_install'));		
	}
	public function auto_image_alt_install() {
		                   $settings = get_option('auto_image_alt');
			               $options = array(
					                         'enable_auto_image_alt' => '1',												 
											 );
							if(!$settings['enable_auto_image_alt']) {
								update_option('auto_image_alt', $options);
							}         
		}
	public function auto_image_alt_menu_page() {
		 add_menu_page(
			__( 'Auto Image Alt', 'auto-image-alt' ),
			__( 'Auto Image Alt', 'auto-image-alt' ),
			   'manage_options',
			'auto_image_alt',
			array(&$this, 'auto_image_alt_method'),
			'dashicons-format-gallery'
			);
	}
	public function auto_image_alt_method() {
		if(is_admin() && current_user_can('manage_options')) {
			include('admin/auto_image_alt.php');
		}
    }
	public function auto_image_alt_scripts() {
		if(!is_admin()) {
		  $settings = get_option('auto_image_alt');            
		 if(isset($settings['enable_auto_image_alt']) && $settings['enable_auto_image_alt'] == '1') {
		    wp_enqueue_script( 'auto_image_alt', plugins_url('js/auto_image_alt.js',  __FILE__ ), array('jquery'), '1.1', true);
		 }
		}
	}
	public function save() {
		  if(isset($_POST['submit']) && wp_verify_nonce( $_POST['auto_image_alt_nonce'], 'auto_image_alt_action' )):
		  $save = update_option( 'auto_image_alt', $_POST );
		  if($save){ $this->redirect('?page=auto_image_alt&success=1'); } else { $this->redirect('?page=mw_equation_editor&success=2');}
		  endif;
		}
		public function redirect($url) {
			echo '<script>';
			echo 'window.location.href="'.$url.'"';
			echo '</script>';
		}
}
new jk_auto_image_alt;
}
