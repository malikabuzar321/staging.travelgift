<?php
/**
 * Plugin Name: Offer to Attractions
 * Description: Custom plugin to submit attraction data to a specific database table.
 * Version: 1.0
 * Author: fahad
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


add_action('admin_menu', 'ota_admin_menu');

function ota_admin_menu() {
    add_menu_page(
        'Offer to Attractions',           // Page title
        'Attractions',                    // Menu title
        'manage_options',                 // Capability
        'offer-to-attractions',           // Menu slug
        'ota_admin_page',                 // Function to display the page
        'dashicons-location-alt',         // Icon
        6                                 // Position
    );
}



function ota_admin_page() {
    ?>
    <div class="wrap">
        <h1>Submit Attraction Data</h1>
        <form method="post" action="" enctype="multipart/form-data">
            <?php wp_nonce_field('ota_nonce_action', 'ota_nonce'); ?>
			
			<label for="title">Attraction Id</label>
            <input type="number" name="attraction_id" id="title" required>
			
            <label for="title">Title</label>
            <input type="text" name="title" id="title" required>

            <label for="price_from_adult">Price From Adult</label>
            <input type="number" name="price_from_adult" id="price_from_adult" required>

            <label for="desc_short">Short Description</label>
            <textarea name="desc_short" id="desc_short" required></textarea>

            <label for="img_sml">Image</label>
            <input type="file" name="img_sml" id="img_sml" required>

            <label for="city_name">City Name</label>
            <input type="text" name="city_name" id="city_name" required>

            <label for="destination">Destination</label>
            <input type="text" name="destination" id="destination" required>

            <input type="submit" name="submit" value="Submit">
        </form>
    </div>
    <?php
}



add_action('admin_init', 'ota_handle_form');

function ota_handle_form() {
    if (isset($_POST['submit']) && check_admin_referer('ota_nonce_action', 'ota_nonce')) {
        global $wpdb;
        $table_name = 'attractions_data';

        // Sanitize and prepare text data
        $data = array(
			'attraction_id' => intval($_POST['attraction_id']),
            'title' => sanitize_text_field($_POST['title']),
            'price_from_adult' => intval($_POST['price_from_adult']),
            'desc_short' => sanitize_textarea_field($_POST['desc_short']),
            'city_name' => sanitize_text_field($_POST['city_name']),
            'dest' => sanitize_text_field($_POST['destination']),
        );

        // Handle file upload for 'img_sml'
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $uploadedfile = $_FILES['img_sml'];
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            // File is valid and was successfully uploaded
            $data['img_sml'] = $movefile['url'];
        } else {
            // Handle the error
            echo '<div class="notice notice-error is-dismissible"><p>Image upload failed: ' . esc_html($movefile['error']) . '</p></div>';
            return;
        }

        // Insert data into the database
        $inserted = $wpdb->insert($table_name, $data);

        // Display a notice based on the result
        if ($inserted) {
            echo '<div class="notice notice-success is-dismissible"><p>Successfully submitted.</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>Submission failed.</p></div>';
        }
    }
}

