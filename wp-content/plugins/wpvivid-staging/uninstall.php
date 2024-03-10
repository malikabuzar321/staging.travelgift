<?php

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
{
    exit;
}

$options=get_option('wpvivid_staging_options',array());
$staging_keep_setting=isset($options['staging_keep_setting']) ? $options['staging_keep_setting'] : true;
if($staging_keep_setting)
{

}
else
{
    delete_option('wpvivid_staging_task_list');
    delete_option('wpvivid_staging_task_cancel');
    delete_option('wpvivid_staging_options');
    delete_option('wpvivid_staging_history');
    delete_option('wpvivid_staging_list');
}
