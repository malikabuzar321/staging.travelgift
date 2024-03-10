<?php if ( ! defined( 'ABSPATH' ) ) exit;
$this->save(); 
$settings = get_option('auto_image_alt');
 ?>
<div class="wrap auto_image_alt">
<h2><?php _e('Auto Image Alt', 'auto-image-alt');?></h2>
<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
<p><strong><?php _e('Support: Auto Image Alt is a new plugin on wordpress, please spend less than a minute to rate us to appreciate our work to make it more stable. :) <a href="https://wordpress.org/support/plugin/auto-image-alt/reviews/?filter=5" class="button button-primary" target="_blank" title="Click Here To Rate Us">Rate Us</a>','file-manager-advanced');?></strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
<form action="" method="post" id="auto_image_alt_form">
<?php wp_nonce_field( 'auto_image_alt_action', 'auto_image_alt_nonce' ); //common ?>
<table class="form-table">

<tbody><tr>
<th scope="row"><label for="enable"><?php _e('Enable', 'equation-editor');?></label></th>
<td>
<input name="enable_auto_image_alt" id="enable_auto_image_alt" value="1" type="checkbox" <?php echo (isset($settings['enable_auto_image_alt']) && $settings['enable_auto_image_alt'] == '1') ? 'checked="checked"' : '';?>><?php _e('Check to enable Auto Image Alt', 'equation-editor');?>
</td>
</tr>
</tbody>
</table>
<p class="submit"><input name="submit" id="submit" class="button button-primary" value="Save Changes" type="submit"></p>
</form>
 <table>
 <tr>
 <th><a href="https://wordpress.org/support/plugin/auto-image-alt/reviews/?filter=5" class="button" target="_blank">Rate Us</a></th>
 <th><a href="http://modalwebstore.com/auto-image-alt" class="button button-primary" target="_blank">Documentation</a></th>
 <th><a href="http://modalwebstore.com/donate" class="button" target="_blank">Donate</a></th>
 </tr>
</table>
<h2><?php _e('Try our other plugins', 'auto-image-alt');?></h2>
 <table class="form-table" border="1" style="text-align:center">
 <tr>
 <td><strong><?php _e('Plugin Name', 'auto-image-alt');?></strong></td>
 <td><strong><?php _e('Plugin Link', 'auto-image-alt');?></strong></td>
 <td><strong><?php _e('Plugin Type', 'auto-image-alt');?></strong></td>
  <td><strong><?php _e('', 'auto-image-alt');?></strong></td>
 </tr>
  <tr>
 <td><?php _e('File Manager Advanced', 'auto-image-alt');?></td>
 <td><a href="https://wordpress.org/plugins/file-manager-advanced/" target="_blank">https://wordpress.org/plugins/file-manager-advanced/</a></td>
 <td><strong><?php _e('Free', 'auto-image-alt');?></strong></td>
 <td><a href="https://wordpress.org/plugins/file-manager-advanced/" target="_blank" class="button">Download Now</a></td>
 </tr>
 <tr>
 <td><?php _e('File Manager Advanced Shortcode', 'auto-image-alt');?></td>
 <td><a href="http://modalwebstore.com/product/file-manager-advanced-shortcode/" target="_blank">http://modalwebstore.com/product/file-manager-advanced-shortcode/</a></td>
 <td><strong><?php _e('PRO', 'auto-image-alt');?></strong></td>
  <td><a href="http://modalwebstore.com/product/file-manager-advanced-shortcode/" target="_blank" class="button button-primary">Download Now</a></td>
 </tr>
 <tr>
 <td><?php _e('Equation Editor', 'auto-image-alt');?></td>
 <td><a href="https://wordpress.org/plugins/equation-editor/" target="_blank">https://wordpress.org/plugins/equation-editor/</a></td>
 <td><strong><?php _e('Free', 'auto-image-alt');?></strong></td>
 <td><a href="https://wordpress.org/plugins/file-manager-advanced/" target="_blank" class="button">Download Now</a></td>
 </tr>
 <tr>
 <td><?php _e('Equation Editor Pro', 'auto-image-alt');?></td>
 <td><a href="http://modalwebstore.com/product/equation-editor-pro/" target="_blank">http://modalwebstore.com/product/equation-editor-pro/</a></td>
 <td><strong><?php _e('PRO', 'auto-image-alt');?></strong></td>
  <td><a href="http://modalwebstore.com/product/equation-editor-pro/" target="_blank" class="button button-primary">Download Now</a></td>
 </tr>
</table>
</div>