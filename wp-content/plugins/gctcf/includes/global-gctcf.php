<?php
function filter_site_upload_size_limit($size)
{
    // Set the upload size limit to 10 MB for users lacking the 'manage_options' capability.
    if (!current_user_can('manage_options')) {
        // 10 MB.
        $size = 1024 * 10000;
    }
    return $size;
}
add_filter('upload_size_limit', 'filter_site_upload_size_limit', 20);