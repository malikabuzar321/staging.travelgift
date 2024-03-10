<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.giftcards4travel.co.uk/
 * @since      1.0.0
 *
 * @package    Discover_Cars_Api
 * @subpackage Discover_Cars_Api/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<form method="post" action="" novalidate="novalidate">
	<table class="form-table table-api">
    <tr>
        <th><label for="api-id"><?php _e( 'API url', 'discover-cars-api' ); ?></label></th>
        <td>
            <input type="text" class="regular-text" name="dca_api_url" value="<?php echo !empty(get_option('dca_api_url')) ? get_option('dca_api_url') : '';?>" id="api-url" />
            <p class="description"><?php _e( 'Add API url.', 'discover-cars-api' ); ?></p>
        </td>
    </tr>
    <tr>
        <th><label for="api-id"><?php _e( 'API username', 'discover-cars-api' ); ?></label></th>
        <td>
            <input type="text" class="regular-text" name="dca_api_username" value="<?php echo !empty(get_option('dca_api_username')) ? get_option('dca_api_username') : '';?>" id="api-id" />
            <p class="description"><?php _e( 'Add API username.', 'discover-cars-api' ); ?></p>
        </td>
    </tr>
    <tr>
        <th><label for="api-password"><?php _e( 'API password', 'discover-cars-api' ); ?></label></th>
        <td>
            <div class="dca-table-inner">
                <input type="password" class="regular-text dca-password-input hide" name="dca_api_password" value="<?php echo !empty(get_option('dca_api_password')) ? get_option('dca_api_password') : ''  ;?>" id="api-password" /><span class="dca-password-toggle dashicons dashicons-visibility"></span>
            </div>
                <p class="description"><?php _e( 'Add API password.', 'discover-cars-api' ); ?></p>
            
        </td>
    </tr>

    <tr>
        <th><label for="api-id"><?php _e( 'API access token', 'discover-cars-api' ); ?></label></th>
        <td>
            <input type="text" class="regular-text" name="dca_api_token" value="<?php echo !empty(get_option('dca_api_token')) ? get_option('dca_api_token') : '';?>" id="wp-api-id" />
            <p class="description"><?php _e( 'Add API access token.', 'discover-cars-api' ); ?></p>
        </td>
    </tr>
    
    <tr>
        <th><label for="location-update"><?php _e( 'Update locations', 'discover-cars-api' ); ?></label></th>
        <td>
            <input type="submit" name="location-update" id="location-update" class="button button-primary" value="Update"  />
            <p class="description"><?php _e( 'Click on update button to update locations.', 'discover-cars-api' ); ?></p>
        </td>
    </tr>

    <tr>
        <th><label for="policies-update"><?php _e( 'Update fuel policies', 'discover-cars-api' ); ?></label></th>
        <td>
            <input type="submit" name="policies-update" id="policies-update" class="button button-primary" value="Update"  />
            <p class="description"><?php _e( 'Click on update button to update fuel policies.', 'discover-cars-api' ); ?></p>
        </td>
    </tr>
    <tr>
        <th><label for="location-type-update"><?php _e( 'Update location types', 'discover-cars-api' ); ?></label></th>
        <td>
            <input type="submit" name="location-type-update" id="location-type-update" class="button button-primary" value="Update"  />
            <p class="description"><?php _e( 'Click on update button to update location types.', 'discover-cars-api' ); ?></p>
        </td>
    </tr>
    
</table>

<input type="submit" name="dca-submit" id="submit" class="button button-primary" value="Save Changes"  />
</form>
