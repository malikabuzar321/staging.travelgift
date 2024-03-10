<?php
if (!defined('WPVIVID_STAGING_PLUGIN_DIR'))
{
    die;
}

class WPvivid_New_Staging_Task
{
    public $task;
    public $options;
    public $current_job;
    public $log;
    public $db_des_instance;
    public $replacing_table;

    public $from;
    public $to;

    public function __construct($options=array())
    {
        $this->db_des_instance=false;

        $this->options=new WPvivid_Staging_Option();

        if(!empty($options))
        {
            $this->task=$options;
            $this->log=new WPvivid_Staging_Log();
            $this->log->OpenLogFile($this->task['log_file_name']);
        }
    }

    public function create_new_task($options)
    {
        $task_id=uniqid('wpvivid-');

        $this->task['id']=$task_id;

        $this->task['status']['start_time']=time();
        $this->task['status']['run_time']=time();
        $this->task['status']['timeout']=time();
        $this->task['status']['str']='ready';
        $this->task['status']['resume_count']=0;
        $this->task['jobs']=array();
        $this->task['doing']=false;
        $this->task['current_doing']='';
        $this->task['timeout_limit']=900;
        $this->task['log_file_name']=$task_id.'_staging';

        $this->task['setting']=$options['setting'];

        $this->task['exclude_tables']=$options['exclude_tables'];
        $this->task['exclude_regex']=$options['exclude_regex'];
        $this->task['exclude_files']=$options['exclude_files'];
        $this->setup_task($options);

        $this->task['additional_options']=$options['additional_options'];

        $log=new WPvivid_Staging_Log();
        $log->CreateLogFile( $this->task['log_file_name'],'no_folder','staging');
        $log->CloseFile();
        $this->update_task();

        $this->options->update_option('staging_task_cancel',false);

        $this->options->update_option('wpvivid_staging_history_ex', $options['staging_options']);

        $ret['result']='success';
        $ret['test']=$this->task;
        return $ret;
    }

    public function setup_task($options)
    {
        global $wpdb;
        $this->set_db_connect_option($options['additional_db_options']);

        $this->task['src_path']=$options['src_path'];
        $this->task['des_path']=$options['des_path'];

        $this->task['old_prefix']=$wpdb->base_prefix;
        $this->task['new_prefix']=$options['table_prefix'];

        $this->task['new_site_url']=$options['new_site_url'];
        $this->task['new_home_url']=$options['new_home_url'];

        $this->task['old_site_url']=$options['old_site_url'];
        $this->task['old_home_url']=$options['old_home_url'];
        
        $this->task['permalink_structure'] = get_option( 'permalink_structure','');
        $this->task['login_url'] = wp_login_url();
        $this->task['staging_comment'] = $options['staging_comment'];
        $this->task['is_create_subdomain'] = $options['is_create_subdomain'];
        $this->task['is_mu_single']=isset($options['is_mu_single'])?$options['is_mu_single']:false;

        if($options['create_new_wp'])
        {
            $this->setup_create_new_wp();
        }
        else
        {
            if(is_multisite())
            {
                $this->setup_mu($options);
            }
            $this->setup_create_new_staging($options['staging_options']);
        }
    }

    public function setup_create_new_staging($staging_options)
    {
        $index=0;
        $this->task['jobs']=array();

        $this->task['jobs'][$index]['type']='copy_core';
        $this->task['jobs'][$index]['finished']=0;
        $this->task['jobs'][$index]['progress']=0;
        $index++;


        $this->task['jobs'][$index]['type']='copy_wp_content';
        $this->task['jobs'][$index]['finished']=0;
        $this->task['jobs'][$index]['progress']=0;
        $index++;

        $this->task['jobs'][$index]['type']='copy_plugins';
        $this->task['jobs'][$index]['finished']=0;
        $this->task['jobs'][$index]['progress']=0;
        $index++;

        $this->task['jobs'][$index]['type']='copy_themes';
        $this->task['jobs'][$index]['finished']=0;
        $this->task['jobs'][$index]['progress']=0;
        $index++;

        $this->task['jobs'][$index]['type']='copy_upload';
        $this->task['jobs'][$index]['finished']=0;
        $this->task['jobs'][$index]['progress']=0;
        $index++;

        if($staging_options['additional_file_check'] == '1')
        {
            foreach ($staging_options['additional_file_list'] as $key => $value)
            {
                $this->task['custom'][] = $key;
            }
            $this->task['jobs'][$index]['type']='copy_custom';
            $this->task['jobs'][$index]['finished']=0;
            $this->task['jobs'][$index]['progress']=0;
            $index++;
        }

        $this->task['jobs'][$index]['type']='copy_db';
        $this->task['jobs'][$index]['finished']=0;
        $this->task['jobs'][$index]['progress']=0;
        $index++;

        $this->task['jobs'][$index]['type']='replace_link';
        $this->task['jobs'][$index]['finished']=0;
        $this->task['jobs'][$index]['progress']=0;
        $index++;
        
        $this->task['jobs'][$index]['type']='finish_staging';
        $this->task['jobs'][$index]['finished']=0;
        $this->task['jobs'][$index]['progress']=0;
    }

    public function setup_create_new_wp()
    {
        $this->task['fresh_install']=true;

        $index=0;
        $this->task['jobs']=array();

        $this->task['jobs'][$index]['type']='copy_core';
        $this->task['jobs'][$index]['finished']=0;
        $this->task['jobs'][$index]['progress']=0;
        $index++;

        $this->task['jobs'][$index]['type']='copy_plugins';
        $this->task['jobs'][$index]['finished']=0;
        $this->task['jobs'][$index]['progress']=0;
        $index++;

        $this->task['jobs'][$index]['type']='copy_themes';
        $this->task['jobs'][$index]['finished']=0;
        $this->task['jobs'][$index]['progress']=0;
        $index++;

        $this->task['jobs'][$index]['type']='create_new_wp';
        $this->task['jobs'][$index]['finished']=0;
        $this->task['jobs'][$index]['progress']=0;
        $index++;

        $this->task['jobs'][$index]['type']='finish_wp_install';
        $this->task['jobs'][$index]['finished']=0;
        $this->task['jobs'][$index]['progress']=0;
    }

    public function is_task_finished()
    {
        $finished=true;

        foreach ($this->task['jobs'] as $job)
        {
            if($job['finished']==0)
            {
                $finished=false;
                break;
            }
        }
        return $finished;
    }

    public function get_next_job()
    {
        $job_key=false;
        foreach ($this->task['jobs'] as $key=>$job)
        {
            if($job['finished']==0)
            {
                $job_key=$key;
                break;
            }
        }
        return $job_key;
    }

    public function do_staging_job($key)
    {
        if(!isset($this->task['jobs'][$key]))
        {
            $ret['result']='failed';
            $ret['error']='not found job';
            return $ret;
        }

        if($this->is_task_canceled())
        {
            $ret['result']='failed';
            $ret['error']='Creating staging cancelled.';
            return $ret;
        }

        $this->current_job=$key;
        $job=$this->task['jobs'][$key];
        $this->task['current_job']=$key;
        if(!isset($this->task['jobs'][$key]['start_time']))
        {
            $this->task['jobs'][$key]['start_time']=time();
        }
        $this->update_task();

        if($job['type']=='copy_core')
        {
            $ret= $this->do_copy_core();
            if($ret['result']=='failed')
            {
                return $ret;
            }
        }
        else if($job['type']=='copy_wp_content')
        {
            $ret= $this->do_copy_wp_content();
            if($ret['result']=='failed')
            {
                return $ret;
            }
        }
        else if($job['type']=='copy_plugins')
        {
            $ret= $this->do_copy_plugins();
            if($ret['result']=='failed')
            {
                return $ret;
            }
        }
        else if($job['type']=='copy_themes')
        {
            $ret= $this->do_copy_themes();
            if($ret['result']=='failed')
            {
                return $ret;
            }
        }
        else if($job['type']=='copy_upload')
        {
            $ret= $this->do_copy_upload();
            if($ret['result']=='failed')
            {
                return $ret;
            }
        }
        else if($job['type']=='copy_custom')
        {
            $ret= $this->do_copy_custom();
            if($ret['result']=='failed')
            {
                return $ret;
            }
        }
        else if($job['type']=='copy_db')
        {
            $ret= $this->do_copy_db();
            if($ret['result']=='failed')
            {
                return $ret;
            }
        }
        else if($job['type']=='replace_link')
        {
            $ret= $this->do_replace_link();
            if($ret['result']=='failed')
            {
                return $ret;
            }
        }
        else if($job['type']=='finish_staging')
        {
            $ret= $this->do_finish_staging();
            if($ret['result']=='failed')
            {
                return $ret;
            }
        }
        else if($job['type']=='create_new_wp')
        {
            $ret= $this->do_create_new_wp();
            if($ret['result']=='failed')
            {
                return $ret;
            }
        }
        else if($job['type']=='finish_wp_install')
        {
            $ret= $this->do_finish_wp_install();
            if($ret['result']=='failed')
            {
                return $ret;
            }
        }
        //plugins
        $this->update_task();
        $ret['result']='success';
        return $ret;
    }

    public function do_finish_staging()
    {
        global $wpdb;
        $this->set_staging_site_data();

        $prefix=$this->task['new_prefix'];
        $db=$this->get_des_db_instance();

        $data['id']=$this->task['id'];
        $data['name']=$this->task['des_path'];
        $data['prefix']= $prefix;
        $admin_url = apply_filters('wpvividstg_get_admin_url', '');
        $admin_url .= 'admin.php?page='.apply_filters('wpvivid_white_label_slug', 'WPvivid');
        $data['parent_admin_url']=$admin_url;
        $data['live_site_url']=home_url();
        $data['live_site_staging_url']=apply_filters('wpvividstg_get_admin_url', '').'admin.php?page='.apply_filters('wpvivid_white_label_plugin_name', 'WPvivid_Staging');

        $data['live_site_data']['db_connect']['use_additional_db']=$this->task['db_connect']['use_additional_db'];
        if($this->task['db_connect']['use_additional_db']!==false)
        {
            $data['live_site_data']['db_connect']['dbuser']=DB_USER;
            $data['live_site_data']['db_connect']['dbpassword']=DB_PASSWORD;
            $data['live_site_data']['db_connect']['dbname']=DB_NAME;
            $data['live_site_data']['db_connect']['dbhost']=DB_HOST;
        }

        $data['live_site_data']['path']=untrailingslashit(ABSPATH);
        $data['live_site_data']['prefix']=$wpdb->base_prefix;

        $data['live_site_data']['site_url']=site_url();
        $data['live_site_data']['home_url']=home_url();

        if(isset($this->task['is_mu_single'])&&$this->task['is_mu_single'])
        {
            $data['live_site_data']['mu_single']=true;
            $data['live_site_data']['mu_single_site_id']=$this->task['mu_single_site_id'];

            $data['live_site_data']['site_url']=get_site_url($this->task['mu_single_site_id']);
            $data['live_site_data']['home_url']=get_home_url($this->task['mu_single_site_id']);
        }
        else
        {
            $data['live_site_data']['mu_single']=false;
        }

        if(isset($this->task['path_current_site'])&&!empty($this->task['path_current_site']))
        {
            $data['live_site_data']['path_current_site']=PATH_CURRENT_SITE;
            $data['live_site_data']['main_site_id']=$this->task['main_site_id'];
        }

        $data=serialize($data);

        $wpvivid_options_table=$prefix.'wpvivid_options';
        if($db->get_var("SHOW TABLES LIKE '$wpvivid_options_table'") != $wpvivid_options_table)
        {
            $sql = "CREATE TABLE IF NOT EXISTS $wpvivid_options_table (
                `option_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `option_name` varchar(191) NOT NULL DEFAULT '',
				`option_value` longtext NOT NULL,
				PRIMARY KEY (`option_id`),
				UNIQUE KEY `option_name` (`option_name`)
                );";
            $db->query($sql);
        }

        $update_query = $db->prepare("INSERT INTO $wpvivid_options_table (option_name,option_value) VALUES ('wpvivid_staging_data',%s)", $data);
        $this->log->WriteLog($update_query, 'notice');
        if ($db->get_results($update_query)===false)
        {
            $error=$db->last_error;
            $this->log->WriteLog($error, 'Warning');
        }

        //

        $option=new WPvivid_Staging_Option();
        $staging_sites=$option->get_option('staging_site_data');

        $new_site['id']=$this->task['id'];
        $new_site['create_time']=time();
        $new_site['comment']=$this->task['staging_comment'];

        $new_site['path']=$this->task['des_path'];
        $new_site['site_url']=$this->task['new_site_url'];
        $new_site['home_url']=$this->task['new_home_url'];
        $new_site['prefix']=$this->task['new_prefix'];
        $new_site['old_prefix']=$this->task['old_prefix'];
        $new_site['db_connect']['use_additional_db']=$this->task['db_connect']['use_additional_db'];

        if($new_site['db_connect']['use_additional_db'])
        {
            $new_site['db_connect']['dbuser']=$this->task['db_connect']['dbuser'];
            $new_site['db_connect']['dbpassword']=$this->task['db_connect']['dbpassword'];
            $new_site['db_connect']['dbname']=$this->task['db_connect']['dbname'];
            $new_site['db_connect']['dbhost']=$this->task['db_connect']['dbhost'];
        }

        $new_site['fresh_install']=isset($this->task['fresh_install'])?$this->task['fresh_install']:false;
        $new_site['log_file_name']=$this->task['log_file_name'];

        $new_site['permalink_structure']=$this->task['permalink_structure'];
        $new_site['login_url']=$this->task['login_url'];
        $new_site['is_create_subdomain']=$this->task['is_create_subdomain'];

        if(isset($this->task['is_mu_single'])&&$this->task['is_mu_single'])
        {
            $new_site['mu_single']=true;
            $new_site['mu_single_site_id']=$this->task['mu_single_site_id'];
        }
        else
        {
            $new_site['mu_single']=false;
        }

        if(isset($this->task['path_current_site'])&&!empty($this->task['path_current_site']))
        {
            $new_site['path_current_site']=$this->task['path_current_site'];
            $new_site['main_site_id']=$this->task['main_site_id'];
        }

        $staging_sites[$new_site['id']]=$new_site;
        $option->update_option('staging_site_data', $staging_sites);

        $ret['result']='success';
        $this->task['jobs'][$this->current_job]['finished']=1;
        $this->log->WriteLog('Creating staging site completed successfully.','notice');

        $this->task['status']['resume_count']=0;
        $this->update_task();
        wp_cache_flush();

        do_action('wpvivid_staging_do_additional_option');

        return $ret;
    }

    public function set_staging_site_data()
    {
        global $wpdb;
        $db=$this->get_des_db_instance();

        $prefix=$this->task['new_prefix'];

        $query=$db->prepare("UPDATE {$prefix}options SET option_value = %s WHERE option_name = 'siteurl' or option_name='home'",$this->task['new_site_url']);

        if ($db->get_results($query)===false)
        {
            $error=$db->last_error;
            $this->log->WriteLog($error, 'Warning');
        }

        $update_query=$db->prepare("UPDATE {$prefix}options SET option_value = %s WHERE option_name = 'rewrite_rules'", '');
        //$this->log->WriteLog($update_query, 'notice');
        if ($db->get_results($update_query)===false)
        {
            $error=$db->last_error;
            $this->log->WriteLog($error, 'Warning');
        }

        $update_query=$db->prepare("INSERT INTO {$prefix}options (option_name,option_value) VALUES ('wpvivid_staging_finish',%d)", 1);
        //$this->log->WriteLog($update_query, 'notice');
        if ($db->get_results($update_query)===false)
        {
            $error=$db->last_error;
            $this->log->WriteLog($error, 'Warning');
        }

        $is_overwrite_permalink_structure = $this->task['setting']['staging_overwrite_permalink'];
        if($is_overwrite_permalink_structure == 0)
        {
            $permalink_structure = $this->task['permalink_structure'];
            $update_query = $db->prepare("INSERT INTO {$prefix}options (option_name,option_value) VALUES ('wpvivid_staging_init',%s)", $permalink_structure);

            //$this->log->WriteLog($update_query, 'notice');
            if ($db->get_results($update_query) === false)
            {
                $error = $db->last_error;
                $this->log->WriteLog($error, 'Warning');
            }
        }
        
        $wpvivid_pro_user_res = $wpdb->get_results( $wpdb->prepare( "SELECT option_value FROM {$this->task['old_prefix']}options WHERE option_name = %s", 'wpvivid_pro_user' ) );
        if(!empty($wpvivid_pro_user_res))
        {
            foreach ( $wpvivid_pro_user_res as $value )
            {
                $wpvivid_pro_user = $value->option_value;
                $update_query =$db->prepare("UPDATE {$prefix}options SET option_value = %s WHERE option_name = 'wpvivid_pro_user'", $wpvivid_pro_user);
                //$this->log->WriteLog($update_query, 'notice');
                if ($db->get_results($update_query)===false)
                {
                    $error=$db->last_error;
                    $this->log->WriteLog($error, 'Warning');
                }
            }
        }

        $wpvivid_dashboard_info_res = $wpdb->get_results( $wpdb->prepare( "SELECT option_value FROM {$this->task['old_prefix']}options WHERE option_name = %s", 'wpvivid_dashboard_info' ) );
        if(!empty($wpvivid_dashboard_info_res))
        {
            foreach ( $wpvivid_dashboard_info_res as $value )
            {
                $wpvivid_dashboard_info = $value->option_value;
                $update_query =$db->prepare("UPDATE {$prefix}options SET option_value = %s WHERE option_name = 'wpvivid_dashboard_info'", $wpvivid_dashboard_info);
                //$this->log->WriteLog($update_query, 'notice');
                if ($db->get_results($update_query)===false)
                {
                    $error=$db->last_error;
                    $this->log->WriteLog($error, 'Warning');
                }
            }
        }

        $delete_query = $db->prepare("DELETE FROM {$prefix}options WHERE option_name = %s", 'wpvivid_plugin_install_cache');
        //$this->log->WriteLog($delete_query, 'notice');
        if ($db->get_results($delete_query)===false)
        {
            $error=$db->last_error;
            $this->log->WriteLog($error, 'Warning');
        }

        $delete_query = $db->prepare("DELETE FROM {$prefix}options WHERE option_name = %s", 'elementor_pro_license_key');
        //$this->log->WriteLog($delete_query, 'notice');
        if ($db->get_results($delete_query)===false)
        {
            $error=$db->last_error;
            $this->log->WriteLog($error, 'Warning');
        }

        $delete_query = $db->prepare("DELETE FROM {$prefix}options WHERE option_name = %s", '_elementor_pro_license_data');
        //$this->log->WriteLog($delete_query, 'notice');
        if ($db->get_results($delete_query)===false)
        {
            $error=$db->last_error;
            $this->log->WriteLog($error, 'Warning');
        }

        $delete_query = $db->prepare("DELETE FROM {$prefix}options WHERE option_name = %s", '_elementor_pro_license_data_fallback');
        //$this->log->WriteLog($delete_query, 'notice');
        if ($db->get_results($delete_query)===false)
        {
            $error=$db->last_error;
            $this->log->WriteLog($error, 'Warning');
        }


        $wpvivid_common_settings_res = $wpdb->get_results( $wpdb->prepare( "SELECT option_value FROM {$this->task['old_prefix']}options WHERE option_name = %s", 'wpvivid_common_setting' ) );
        if(!empty($wpvivid_common_settings_res))
        {
            foreach ( $wpvivid_common_settings_res as $value )
            {
                $wpvivid_common_settings = $value->option_value;
                $wpvivid_common_settings = maybe_unserialize( $wpvivid_common_settings );
                if(isset($wpvivid_common_settings['backup_prefix']))
                {
                    unset($wpvivid_common_settings['backup_prefix']);

                    $wpvivid_common_settings = maybe_serialize( $wpvivid_common_settings );

                    $update_query =$db->prepare("UPDATE {$prefix}options SET option_value = %s WHERE option_name = 'wpvivid_common_setting'", $wpvivid_common_settings);
                    //$this->log->WriteLog($update_query, 'notice');
                    if ($db->get_results($update_query)===false)
                    {
                        $error=$db->last_error;
                        $this->log->WriteLog($error, 'Warning');
                    }
                }
            }
        }

        $wpvivid_schedule_settings_res = $wpdb->get_results( $wpdb->prepare( "SELECT option_value FROM {$this->task['old_prefix']}options WHERE option_name = %s", 'wpvivid_schedule_addon_setting' ) );
        if(!empty($wpvivid_schedule_settings_res))
        {
            foreach ( $wpvivid_schedule_settings_res as $value )
            {
                $wpvivid_schedule_settings = $value->option_value;
                $wpvivid_schedule_settings = maybe_unserialize( $wpvivid_schedule_settings );
                foreach ($wpvivid_schedule_settings as $schedule_id => $schedule_value)
                {
                    if(isset($schedule_value['backup']['backup_prefix']))
                    {
                        unset($wpvivid_schedule_settings[$schedule_id]['backup']['backup_prefix']);
                    }
                }

                $wpvivid_schedule_settings = maybe_serialize( $wpvivid_schedule_settings );

                $update_query =$db->prepare("UPDATE {$prefix}options SET option_value = %s WHERE option_name = 'wpvivid_schedule_addon_setting'", $wpvivid_schedule_settings);
                //$this->log->WriteLog($update_query, 'notice');
                if ($db->get_results($update_query)===false)
                {
                    $error=$db->last_error;
                    $this->log->WriteLog($error, 'Warning');
                }
            }
        }

        $wpvivid_incremental_schedule_res = $wpdb->get_results( $wpdb->prepare( "SELECT option_value FROM {$this->task['old_prefix']}options WHERE option_name = %s", 'wpvivid_incremental_schedules' ) );
        if(!empty($wpvivid_incremental_schedule_res))
        {
            foreach ( $wpvivid_incremental_schedule_res as $value )
            {
                $wpvivid_incremental_schedule = $value->option_value;
                $wpvivid_incremental_schedule = maybe_unserialize( $wpvivid_incremental_schedule );
                foreach ($wpvivid_incremental_schedule as $schedule_id => $schedule_value)
                {
                    if(isset($schedule_value['backup']['backup_prefix']))
                    {
                        unset($wpvivid_incremental_schedule[$schedule_id]['backup']['backup_prefix']);
                    }
                }

                $wpvivid_incremental_schedule = maybe_serialize( $wpvivid_incremental_schedule );

                $update_query =$db->prepare("UPDATE {$prefix}options SET option_value = %s WHERE option_name = 'wpvivid_incremental_schedules'", $wpvivid_incremental_schedule);
                //$this->log->WriteLog($update_query, 'notice');
                if ($db->get_results($update_query)===false)
                {
                    $error=$db->last_error;
                    $this->log->WriteLog($error, 'Warning');
                }
            }
        }

        $wpvivid_upload_setting_res = $wpdb->get_results( $wpdb->prepare( "SELECT option_value FROM {$this->task['old_prefix']}options WHERE option_name = %s", 'wpvivid_upload_setting' ) );
        if(!empty($wpvivid_upload_setting_res))
        {
            foreach ( $wpvivid_upload_setting_res as $value )
            {
                $wpvivid_upload_setting = $value->option_value;
                $wpvivid_upload_setting = maybe_unserialize( $wpvivid_upload_setting );
                foreach ($wpvivid_upload_setting as $remote_id => $remote_value)
                {
                    if($remote_id === 'remote_selected'){
                        continue;
                    }
                    else{
                        $insert_query =$db->prepare("INSERT INTO {$prefix}options (option_name,option_value) VALUES ('wpvivid_staging_remote_notice',%d)", 1);
                        //$this->log->WriteLog($insert_query, 'notice');
                        if ($db->get_results($insert_query)===false)
                        {
                            $error=$db->last_error;
                            $this->log->WriteLog($error, 'Warning');
                        }
                        break;
                    }
                }
            }
        }

        $update_query =$db->prepare("UPDATE {$prefix}options SET option_value = %s WHERE option_name = 'blog_public'", '0');
        //$this->log->WriteLog($update_query, 'notice');
        if ($db->get_results($update_query)===false)
        {
            $error=$db->last_error;
            $this->log->WriteLog($error, 'Warning');
        }

        $delete_query = $db->prepare("DELETE FROM {$prefix}options WHERE option_name = %s", 'wpvivid_backup_list');
        //$this->log->WriteLog($delete_query, 'notice');
        if ($db->get_results($delete_query)===false)
        {
            $error=$db->last_error;
            $this->log->WriteLog($error, 'Warning');
        }

        $update_query =$db->prepare("UPDATE {$prefix}options SET option_value = %s WHERE option_name = 'upload_path'", "");
        //$this->log->WriteLog($update_query, 'notice');
        if ($db->get_results($update_query)===false)
        {
            $error=$db->last_error;
            $this->log->WriteLog($error, 'Warning');
        }

        if($this->task['is_mu_single'])
        {
            switch_to_blog($this->task['mu_single_site_id']);
            $current   = get_option( 'active_plugins', array() );
            restore_current_blog();

            if(!in_array('wpvivid-staging/wpvivid-staging.php',$current))
                $current[] = 'wpvivid-staging/wpvivid-staging.php';
            sort( $current );
            $value=serialize($current);
            $update_query = $db->prepare("UPDATE {$prefix}options SET option_value=%s WHERE option_name='active_plugins'" , $value);
            //$this->log->WriteLog($update_query, 'notice');
            if ($db->get_results($update_query)===false)
            {
                $error=$db->last_error;
                $this->log->WriteLog($error, 'Warning');
            }
        }
    }
    
    public function do_copy_core()
    {
        $src_path=$des_path='';
        $this->log->WriteLog('Retrieve the files required to copy.','notice');

        if(!isset($this->task['jobs'][$this->current_job]['offset']))
        {
            $list=$this->get_copy_dir_list('core',$src_path,$des_path);
            $this->task['jobs'][$this->current_job]['files_list']=$list;
            $this->log->WriteLog('Create a cache file.','notice');
            $this->task['jobs'][$this->current_job]['create_cache_file']=$this->create_cache_file($list);

            $this->task['jobs'][$this->current_job]['offset']=0;
            $this->task['jobs'][$this->current_job]['src_path']=$src_path;
            $this->task['jobs'][$this->current_job]['des_path']=$des_path;
        }

        $src_path=$this->task['jobs'][$this->current_job]['src_path'];
        $des_path=$this->task['jobs'][$this->current_job]['des_path'];
        $start=$this->task['jobs'][$this->current_job]['offset'];
        $this->log->WriteLog('Copying files starts from: '.$start,'notice');
        $this->log->WriteLog('Copying files from '.$src_path.' to '.$des_path,'notice');

        while($this->copy_files($this->task['jobs'][$this->current_job]['create_cache_file'],$start,$this->get_files_copy_count(),$src_path,$des_path))
        {
            $this->log->WriteLog('The count of copied files: '.$this->get_files_copy_count(),'notice');
            $this->log->WriteLog('The next copying files starts from:'.$start,'notice');
            $this->task['jobs'][$this->current_job]['offset']=$start;
            $this->update_task();
            if($this->is_time_limit_exceeded())
            {
                $ret['result']='success';
                return $ret;
            }

            if($this->is_task_canceled())
            {
                $ret['result']='failed';
                $ret['error']='Creating staging cancelled.';
                return $ret;
            }
        }
        $this->log->WriteLog('Copying core files is completed.','notice');


        $src_config = $src_path.DIRECTORY_SEPARATOR.'wp-config.php';
        $src_config_temp = $src_path.DIRECTORY_SEPARATOR.'wp-config.php.vividtemp';
        $dec_config = $des_path.DIRECTORY_SEPARATOR.'wp-config.php';

        $this->log->WriteLog('Copy config.php to config.php.vividtemp','notice');
        if(copy($src_config,$src_config_temp))
        {
            @chmod($src_config_temp,0755);

        }
        else
        {
            $this->log->WriteLog('Failed to copy files from '.$src_config.' to '.$src_config_temp.'.','warning');
        }
        $this->change_wp_temp_config();
        $this->log->WriteLog('Copy config.php.vividtemp to config.php','notice');
        if(copy($src_config_temp,$dec_config))
        {
            if(isset($this->task['setting']['force_files_mode'])&&$this->task['setting']['force_files_mode'])
            {
                @chmod($dec_config,0644);
            }
            else
            {
                @chmod($dec_config,0755);
            }
        }
        else
        {
            $this->log->WriteLog('Failed to copy files from '.$src_config_temp.' to '.$dec_config.'.','warning');
        }
        @unlink($src_config_temp);


        if(is_multisite())
        {
            $this->change_htaccess();
        }

        $this->clean_up();

        $ret['result']='success';
        $this->task['jobs'][$this->current_job]['finished']=1;
        $this->task['status']['resume_count']=0;
        $this->update_task();
        return $ret;
    }

    public function do_copy_wp_content()
    {
        $src_path=$des_path='';
        $this->log->WriteLog('Retrieve the files required to copy.','notice');

        if(!isset($this->task['jobs'][$this->current_job]['offset']))
        {
            $list=$this->get_copy_dir_list('wp-content',$src_path,$des_path);
            $this->task['jobs'][$this->current_job]['files_list']=$list;
            $this->log->WriteLog('Create a cache file.','notice');
            $this->task['jobs'][$this->current_job]['create_cache_file']=$this->create_cache_file($list);

            $this->task['jobs'][$this->current_job]['offset']=0;
            $this->task['jobs'][$this->current_job]['src_path']=$src_path;
            $this->task['jobs'][$this->current_job]['des_path']=$des_path;
        }

        $src_path=$this->task['jobs'][$this->current_job]['src_path'];
        $des_path=$this->task['jobs'][$this->current_job]['des_path'];

        $start=$this->task['jobs'][$this->current_job]['offset'];
        $this->log->WriteLog('Copying files starts from: '.$start,'notice');
        $this->log->WriteLog('Copying files from '.$src_path.' to '.$des_path,'notice');

        while($this->copy_files($this->task['jobs'][$this->current_job]['create_cache_file'],$start,$this->get_files_copy_count(),$src_path,$des_path))
        {
            $this->log->WriteLog('The count of copied files: '.$this->get_files_copy_count(),'notice');
            $this->log->WriteLog('The next copying files starts from:'.$start,'notice');
            $this->task['jobs'][$this->current_job]['offset']=$start;
            $this->update_task();
            if($this->is_time_limit_exceeded())
            {
                $ret['result']='success';
                return $ret;
            }

            if($this->is_task_canceled())
            {
                $ret['result']='failed';
                $ret['error']='Creating staging cancelled.';
                return $ret;
            }
        }
        $this->log->WriteLog('Copying wp-content files is completed.','notice');
        $this->clean_up();

        $ret['result']='success';
        $this->task['jobs'][$this->current_job]['finished']=1;
        $this->task['status']['resume_count']=0;
        $this->update_task();
        return $ret;
    }

    public function do_copy_plugins()
    {
        $src_path=$des_path='';
        $this->log->WriteLog('Retrieve the files required to copy.','notice');

        if(!isset($this->task['jobs'][$this->current_job]['offset']))
        {
            $list=$this->get_copy_dir_list('plugins',$src_path,$des_path);
            $this->task['jobs'][$this->current_job]['files_list']=$list;
            $this->log->WriteLog('Create a cache file.','notice');
            $this->task['jobs'][$this->current_job]['create_cache_file']=$this->create_cache_file($list);

            $this->task['jobs'][$this->current_job]['offset']=0;
            $this->task['jobs'][$this->current_job]['src_path']=$src_path;
            $this->task['jobs'][$this->current_job]['des_path']=$des_path;
        }

        $src_path=$this->task['jobs'][$this->current_job]['src_path'];
        $des_path=$this->task['jobs'][$this->current_job]['des_path'];

        $start=$this->task['jobs'][$this->current_job]['offset'];
        $this->log->WriteLog('Copying files starts from: '.$start,'notice');
        $this->log->WriteLog('Copying files from '.$src_path.' to '.$des_path,'notice');

        while($this->copy_files($this->task['jobs'][$this->current_job]['create_cache_file'],$start,$this->get_files_copy_count(),$src_path,$des_path))
        {
            $this->log->WriteLog('The count of copied files: '.$this->get_files_copy_count(),'notice');
            $this->log->WriteLog('The next copying files starts from:'.$start,'notice');
            $this->task['jobs'][$this->current_job]['offset']=$start;
            $this->update_task();
            if($this->is_time_limit_exceeded())
            {
                $ret['result']='success';
                return $ret;
            }

            if($this->is_task_canceled())
            {
                $ret['result']='failed';
                $ret['error']='Creating staging cancelled.';
                return $ret;
            }
        }
        $this->log->WriteLog('Copying plugins files is completed.','notice');
        $this->clean_up();

        $ret['result']='success';
        $this->task['jobs'][$this->current_job]['finished']=1;
        $this->task['status']['resume_count']=0;
        $this->update_task();
        return $ret;
    }

    public function do_copy_themes()
    {
        $src_path=$des_path='';
        $this->log->WriteLog('Retrieve the files required to copy.','notice');

        if(!isset($this->task['jobs'][$this->current_job]['offset']))
        {
            $list=$this->get_copy_dir_list('themes',$src_path,$des_path);
            $this->task['jobs'][$this->current_job]['files_list']=$list;
            $this->log->WriteLog('Create a cache file.','notice');
            $this->task['jobs'][$this->current_job]['create_cache_file']=$this->create_cache_file($list);

            $this->task['jobs'][$this->current_job]['offset']=0;
            $this->task['jobs'][$this->current_job]['src_path']=$src_path;
            $this->task['jobs'][$this->current_job]['des_path']=$des_path;
        }

        $src_path=$this->task['jobs'][$this->current_job]['src_path'];
        $des_path=$this->task['jobs'][$this->current_job]['des_path'];

        $start=$this->task['jobs'][$this->current_job]['offset'];
        $this->log->WriteLog('Copying files starts from: '.$start,'notice');
        $this->log->WriteLog('Copying files from '.$src_path.' to '.$des_path,'notice');

        while($this->copy_files($this->task['jobs'][$this->current_job]['create_cache_file'],$start,$this->get_files_copy_count(),$src_path,$des_path))
        {
            $this->log->WriteLog('The count of copied files: '.$this->get_files_copy_count(),'notice');
            $this->log->WriteLog('The next copying files starts from:'.$start,'notice');
            $this->task['jobs'][$this->current_job]['offset']=$start;
            $this->update_task();
            if($this->is_time_limit_exceeded())
            {
                $ret['result']='success';
                return $ret;
            }

            if($this->is_task_canceled())
            {
                $ret['result']='failed';
                $ret['error']='Creating staging cancelled.';
                return $ret;
            }
        }
        $this->log->WriteLog('Copying themes files is completed.','notice');
        $this->clean_up();

        $ret['result']='success';
        $this->task['jobs'][$this->current_job]['finished']=1;
        $this->task['status']['resume_count']=0;
        $this->update_task();
        return $ret;
    }

    public function do_copy_upload()
    {
        $src_path=$des_path='';
        $this->log->WriteLog('Retrieve the files required to copy.','notice');

        if(!isset($this->task['jobs'][$this->current_job]['offset']))
        {
            $list=$this->get_copy_dir_list('upload',$src_path,$des_path);
            $this->task['jobs'][$this->current_job]['files_list']=$list;
            $this->log->WriteLog('Create a cache file.','notice');
            $this->task['jobs'][$this->current_job]['create_cache_file']=$this->create_cache_file($list);

            $this->task['jobs'][$this->current_job]['offset']=0;
            $this->task['jobs'][$this->current_job]['src_path']=$src_path;
            $this->task['jobs'][$this->current_job]['des_path']=$des_path;
        }

        $src_path=$this->task['jobs'][$this->current_job]['src_path'];
        $des_path=$this->task['jobs'][$this->current_job]['des_path'];

        $start=$this->task['jobs'][$this->current_job]['offset'];
        $this->log->WriteLog('Copying files starts from: '.$start,'notice');
        $this->log->WriteLog('Copying files from '.$src_path.' to '.$des_path,'notice');

        while($this->copy_files($this->task['jobs'][$this->current_job]['create_cache_file'],$start,$this->get_files_copy_count(),$src_path,$des_path))
        {
            $this->log->WriteLog('The count of copied files: '.$this->get_files_copy_count(),'notice');
            $this->log->WriteLog('The next copying files starts from:'.$start,'notice');
            $this->task['jobs'][$this->current_job]['offset']=$start;
            $this->update_task();
            if($this->is_time_limit_exceeded())
            {
                $ret['result']='success';
                return $ret;
            }

            if($this->is_task_canceled())
            {
                $ret['result']='failed';
                $ret['error']='Creating staging cancelled.';
                return $ret;
            }
        }
        $this->log->WriteLog('Copying upload files is completed.','notice');
        $this->clean_up();

        $ret['result']='success';
        $this->task['jobs'][$this->current_job]['finished']=1;
        $this->task['status']['resume_count']=0;
        $this->update_task();
        return $ret;
    }

    public function do_copy_custom()
    {
        $this->log->WriteLog('Retrieve the files required to copy.','notice');

        if(!isset($this->task['jobs'][$this->current_job]['offset']))
        {
            $src_path=$des_path='';
            $list=$this->get_copy_dir_list('custom',$src_path,$des_path);
            $this->task['jobs'][$this->current_job]['files_list']=$list;
            $this->log->WriteLog('Create a cache file.','notice');
            $this->task['jobs'][$this->current_job]['create_cache_file']=$this->create_cache_file($list);

            $this->task['jobs'][$this->current_job]['offset']=0;
            $this->task['jobs'][$this->current_job]['src_path']=$src_path;
            $this->task['jobs'][$this->current_job]['des_path']=$des_path;
        }

        $src_path=$this->task['jobs'][$this->current_job]['src_path'];
        $des_path=$this->task['jobs'][$this->current_job]['des_path'];

        $start=$this->task['jobs'][$this->current_job]['offset'];
        $this->log->WriteLog('Copying files starts from: '.$start,'notice');
        $this->log->WriteLog('Copying files from '.$src_path.' to '.$des_path,'notice');

        while($this->copy_files($this->task['jobs'][$this->current_job]['create_cache_file'],$start,$this->get_files_copy_count(),$src_path,$des_path))
        {
            $this->log->WriteLog('The count of copied files: '.$this->get_files_copy_count(),'notice');
            $this->log->WriteLog('The next copying files starts from:'.$start,'notice');
            $this->task['jobs'][$this->current_job]['offset']=$start;
            $this->update_task();
            if($this->is_time_limit_exceeded())
            {
                $ret['result']='success';
                return $ret;
            }

            if($this->is_task_canceled())
            {
                $ret['result']='failed';
                $ret['error']='Creating staging cancelled.';
                return $ret;
            }
        }
        $this->clean_up();

        $ret['result']='success';
        $this->task['jobs'][$this->current_job]['finished']=1;
        $this->log->WriteLog('Copying custom files is completed.','notice');
        $this->task['status']['resume_count']=0;
        $this->update_task();
        return $ret;
    }

    public function do_copy_db()
    {
        if(!isset($this->task['jobs'][$this->current_job]['tables']))
        {
            $ret=$this->init_tables_data();
            if($ret['result']=='failed')
            {
                return $ret;
            }
            $this->task['jobs'][$this->current_job]['tables']=$ret['tables'];
        }

        if($this->is_same_database())
        {
            global $wpdb;
            $wpdb->query('SET FOREIGN_KEY_CHECKS=0;');
        }
        else
        {
            $des=$this->get_des_db_instance();
            $des->query('SET FOREIGN_KEY_CHECKS=0;');
        }

        foreach ($this->task['jobs'][$this->current_job]['tables'] as $table_name=>$table)
        {
            if($table['finished']==1)
            {
                continue;
            }

            $ret=$this->copy_table($table);
            if($ret['result']=='failed')
            {
                return $ret;
            }

            $this->task['jobs'][$this->current_job]['tables'][$table_name]=$ret['table'];
            $this->update_task();
            if($this->is_time_limit_exceeded())
            {
                $ret['result']='success';
                return $ret;
            }
        }

        $ret['result']='success';
        $this->task['jobs'][$this->current_job]['finished']=1;
        $this->log->WriteLog('Copying db is completed.','notice');
        $this->task['status']['resume_count']=0;
        $this->update_task();
        return $ret;
    }

    public function do_create_new_wp()
    {
        global $wpdb,$current_user;

        $this->log->WriteLog('Start install new wordpress.','notice');

        if (!function_exists('wp_install'))
        {
            require ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        $username=$current_user->user_login;
        $email=$current_user->user_email;
        $title= get_option('blogname');
        $userpassword=$current_user->user_pass;
        $prefix=$this->task['new_prefix'];
        $old_prefix=$this->task['old_prefix'];
        $permalink_structure = $this->task['permalink_structure'];
        $is_overwrite_permalink_structure = $this->task['setting']['staging_overwrite_permalink'];

        $options=get_option('wpvivid_staging_options',array());
        $options=serialize($options);

        $old_wpdb=$wpdb;

        $wpdb=$this->get_des_db_instance();

        $wpdb->set_prefix($prefix);

        $hook_name = 'update_option_blogname';
        if(has_action($hook_name))
        {
            remove_all_actions($hook_name);
        }

        $result = @wp_install($title, $username, $email, 0, '', md5(rand()));
        $this->log->WriteLog(json_encode($result), 'notice');
        $user_id = $result['user_id'];

        $db_field = 'ID';

        if ( ! $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $wpdb->users WHERE $db_field = %s LIMIT 1",
                $user_id
            )
        ) )
        {
            $this->log->WriteLog('User not create ,so we create it.', 'notice');
            $user_id       = wp_create_user( $username, md5(rand()), $email );
            update_user_option( $user_id, 'default_password_nag', true, true );
        }

        $query = $wpdb->prepare("UPDATE {$prefix}users SET user_pass = %s, user_activation_key = '' WHERE ID = %d LIMIT 1", array($userpassword, $user_id));
        $this->log->WriteLog($query, 'notice');
        $wpdb->query($query);

        $update_query ="UPDATE {$prefix}options SET option_value = '{$this->task['new_site_url']}' WHERE option_name = 'siteurl'";
        $this->log->WriteLog($update_query, 'notice');
        if ($wpdb->get_results($update_query)===false)
        {
            $error=$wpdb->last_error;
            $this->log->WriteLog($error, 'Warning');
        }

        $update_query ="UPDATE {$prefix}options SET option_value = '{$this->task['new_home_url']}' WHERE option_name = 'home'";
        $this->log->WriteLog($update_query, 'notice');
        if ($wpdb->get_results($update_query)===false)
        {
            $error=$wpdb->last_error;
            $this->log->WriteLog($error, 'Warning');
        }

        $update_query ="UPDATE {$prefix}options SET option_name='{$prefix}user_roles' WHERE option_name='{$old_prefix}user_roles'";
        $this->log->WriteLog($update_query, 'notice');
        if ($wpdb->get_results($update_query)===false)
        {
            $error=$wpdb->last_error;
            $this->log->WriteLog($error, 'Warning');
        }

        $update_query=$wpdb->prepare("INSERT INTO {$prefix}options (option_name,option_value) VALUES ('wpvivid_staging_finish',%d)", 1);
        $this->log->WriteLog($update_query, 'notice');
        if ($wpdb->get_results($update_query)===false)
        {
            $error=$wpdb->last_error;
            $this->log->WriteLog($error, 'Warning');
        }

        if($is_overwrite_permalink_structure == 0)
        {
            $update_query = "INSERT INTO {$prefix}options (option_name,option_value) VALUES ('wpvivid_staging_init','{$permalink_structure}')";

            $this->log->WriteLog($update_query, 'notice');
            if ($wpdb->get_results($update_query) === false)
            {
                $error = $wpdb->last_error;
                $this->log->WriteLog($error, 'Warning');
            }
        }

        $update_query = $wpdb->prepare("INSERT INTO {$prefix}options (option_name,option_value) VALUES ('wpvivid_staging_options',%s)", $options);
        $this->log->WriteLog($update_query, 'notice');
        if ($wpdb->get_results($update_query)===false)
        {
            $error=$wpdb->last_error;
            $this->log->WriteLog($error, 'Warning');
        }

        $current   = array();
        $current[] = 'wpvivid-staging/wpvivid-staging.php';
        sort( $current );
        $value=serialize($current);
        $update_query = $wpdb->prepare("UPDATE {$prefix}options SET option_value=%s WHERE option_name='active_plugins'" , $value);
        $this->log->WriteLog($update_query, 'notice');
        if ($wpdb->get_results($update_query)===false)
        {
            $error=$wpdb->last_error;
            $this->log->WriteLog($error, 'Warning');
        }

        $wpdb=$old_wpdb;
        $this->log->WriteLog('prefix:'.$old_prefix,'notice');
        $wpdb->set_prefix($old_prefix);

        $path=$this->task['des_path'].DIRECTORY_SEPARATOR.'wp-config.php';
        $data=file_get_contents($path);
        if( $data === false )
        {
            $this->log->WriteLog('wp-config.php not found in '.$path,'notice');
        }
        else
        {
            preg_match( "/define\s*\(\s*['\"]MULTISITE['\"]\s*,\s*(.*)\s*\);/", $data, $matches );
            if( !empty( $matches[1] ) )
            {
                $this->log->WriteLog('MULTISITE found in wp-config.php','notice');
                $pattern = "/define\s*\(\s*['\"]MULTISITE['\"]\s*,\s*(.*)\s*\);.*/";
                $replace = "define('MULTISITE',false); //";
                $data = preg_replace( array($pattern), $replace, $data );
                if( null === ($data) )
                {
                    $this->log->WriteLog('MULTISITE not replace in wp-config.php','notice');
                }
            }
            file_put_contents($path,$data);
        }

        $this->log->WriteLog('finished install new wordpress.','notice');

        $ret['result']='success';
        $this->task['jobs'][$this->current_job]['finished']=1;
        $this->task['status']['resume_count']=0;
        $this->update_task();
        return $ret;
    }

    public function do_finish_wp_install()
    {
        global $wpdb;
        $prefix=$this->task['new_prefix'];
        $db=$this->get_des_db_instance();

        $data['id']=$this->task['id'];
        $data['name']=$this->task['des_path'];
        $data['prefix']= $prefix;
        $admin_url = apply_filters('wpvividstg_get_admin_url', '');
        $admin_url .= 'admin.php?page='.apply_filters('wpvivid_white_label_slug', 'WPvivid');
        $data['parent_admin_url']=$admin_url;
        $data['live_site_url']=home_url();
        $data['live_site_staging_url']=apply_filters('wpvividstg_get_admin_url', '').'admin.php?page='.apply_filters('wpvivid_white_label_plugin_name', 'WPvivid_Staging');

        $data['live_site_data']['db_connect']['use_additional_db']=$this->task['db_connect']['use_additional_db'];
        if($this->task['db_connect']['use_additional_db']!==false)
        {
            $data['live_site_data']['db_connect']['dbuser']=DB_USER;
            $data['live_site_data']['db_connect']['dbpassword']=DB_PASSWORD;
            $data['live_site_data']['db_connect']['dbname']=DB_NAME;
            $data['live_site_data']['db_connect']['dbhost']=DB_HOST;
        }

        $data['live_site_data']['path']=untrailingslashit(ABSPATH);
        $data['live_site_data']['prefix']=$wpdb->base_prefix;

        $data['live_site_data']['site_url']=site_url();
        $data['live_site_data']['home_url']=home_url();

        $data=serialize($data);

        $wpvivid_options_table=$prefix.'wpvivid_options';
        if($db->get_var("SHOW TABLES LIKE '$wpvivid_options_table'") != $wpvivid_options_table)
        {
            $sql = "CREATE TABLE IF NOT EXISTS $wpvivid_options_table (
                `option_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `option_name` varchar(191) NOT NULL DEFAULT '',
				`option_value` longtext NOT NULL,
				PRIMARY KEY (`option_id`),
				UNIQUE KEY `option_name` (`option_name`)
                );";
            $db->query($sql);
        }

        $update_query = $db->prepare("INSERT INTO $wpvivid_options_table (option_name,option_value) VALUES ('wpvivid_staging_data',%s)", $data);
        $this->log->WriteLog($update_query, 'notice');
        if ($db->get_results($update_query)===false)
        {
            $error=$db->last_error;
            $this->log->WriteLog($error, 'Warning');
        }

        //

        $option=new WPvivid_Staging_Option();
        $staging_sites=$option->get_option('staging_site_data');

        $new_site['id']=$this->task['id'];
        $new_site['create_time']=time();
        $new_site['comment']=$this->task['staging_comment'];

        $new_site['path']=$this->task['des_path'];
        $new_site['site_url']=$this->task['new_site_url'];
        $new_site['home_url']=$this->task['new_home_url'];
        $new_site['prefix']=$this->task['new_prefix'];
        $new_site['old_prefix']=$this->task['old_prefix'];
        $new_site['db_connect']['use_additional_db']=$this->task['db_connect']['use_additional_db'];

        if($new_site['db_connect']['use_additional_db'])
        {
            $new_site['db_connect']['dbuser']=$this->task['db_connect']['dbuser'];
            $new_site['db_connect']['dbpassword']=$this->task['db_connect']['dbpassword'];
            $new_site['db_connect']['dbname']=$this->task['db_connect']['dbname'];
            $new_site['db_connect']['dbhost']=$this->task['db_connect']['dbhost'];
        }

        $new_site['fresh_install']=isset($this->task['fresh_install'])?$this->task['fresh_install']:false;
        $new_site['log_file_name']=$this->task['log_file_name'];

        $new_site['permalink_structure']=$this->task['permalink_structure'];
        $new_site['login_url']=$this->task['login_url'];
        $new_site['is_create_subdomain']=$this->task['is_create_subdomain'];

        if(isset($this->task['is_mu_single'])&&$this->task['is_mu_single'])
        {
            $new_site['mu_single']=true;
            $new_site['mu_single_site_id']=$this->task['mu_single_site_id'];
        }
        else
        {
            $new_site['mu_single']=false;
        }

        if(isset($this->task['path_current_site'])&&!empty($this->task['path_current_site']))
        {
            $new_site['path_current_site']=$this->task['path_current_site'];
            $new_site['main_site_id']=$this->task['main_site_id'];
        }

        $staging_sites[$new_site['id']]=$new_site;
        $option->update_option('staging_site_data', $staging_sites);

        $ret['result']='success';
        $this->task['jobs'][$this->current_job]['finished']=1;
        $this->log->WriteLog('Creating staging site completed successfully.','notice');

        $this->task['status']['resume_count']=0;
        $this->update_task();
        wp_cache_flush();
        return $ret;
    }

    public function copy_table($table)
    {
        $ret['result']='success';
        $ret['table']=$table;

        if($table['create']==0)
        {
            $ret=$this->create_table($table['name']);
            if($ret['result']=='success')
            {
                $table['create']=1;
            }
            else
            {
                return $ret;
            }
        }

        while(!$table['finished'])
        {
            if($this->is_task_canceled())
            {
                $ret['result']='failed';
                $ret['error']='Creating staging cancelled.';
                return $ret;
            }

            $ret=$this->copy_table_data($table);

            if($ret['result']=='failed')
            {
                return $ret;
            }

            $table=$ret['table'];

            if($table['finished'])
            {
                $this->log->WriteLog('Copying '.$table['name'].' is completed.','notice');
            }
            else if($this->is_time_limit_exceeded())
            {
                $this->log->WriteLog('Copying '.$table['name'].' offset:'.$table['start'],'notice');
                return $ret;
            }

        }

        return $ret;
    }

    public function copy_table_data($table)
    {
        global $wpdb;
        if($this->task['is_mu_single']&&($table['name']==$wpdb->base_prefix.'users'||$table['name']==$wpdb->base_prefix.'usermeta'))
        {
            $new_table_name=$this->str_replace_first($wpdb->prefix,$this->task['new_prefix'],$table['name']);
        }
        else
        {
            $new_table_name=$this->str_replace_first($this->task['old_prefix'],$this->task['new_prefix'],$table['name']);
        }

        $old_table_name=$table['name'];

        $sum =$wpdb->get_var("SELECT COUNT(1) FROM `{$old_table_name}`");
        if($sum==0)
        {
            $table['finished']=1;
            $ret['result']='success';
            $ret['table']=$table;
            return $ret;
        }

        $count=$this->get_db_insert_count();

        $limit = " LIMIT {$count} OFFSET {$table['start']}";

        if($this->is_same_database())
        {
            $select =  "SELECT * FROM `{$old_table_name}` {$limit}";
            if($wpdb->query( "INSERT INTO `{$new_table_name}` ".$select )===false)
            {
                $error='Failed to insert '.$new_table_name.', error: '.$wpdb->last_error;
                $this->log->WriteLog($error,'warning');

                $start =$wpdb->get_var("SELECT COUNT(1) FROM `{$new_table_name}`");
                if($start===false)
                {
                    $ret['result']='failed';
                    $ret['error']=$error;
                    return $ret;
                }

                $this->log->WriteLog('new start offset '.$start,'warning');
                $limit = " LIMIT {$count} OFFSET {$start}";
                $select =  "SELECT * FROM `{$old_table_name}` {$limit}";
                if($wpdb->query( "INSERT INTO `{$new_table_name}` ".$select )===false)
                {
                    $error='Failed to insert '.$new_table_name.', error: '.$wpdb->last_error;
                    $this->log->WriteLog($error,'error');
                    $ret['result']='failed';
                    $ret['error']=$error;
                    return $ret;
                }
                else
                {
                    $table['start']=$start;
                }
            }
        }
        else
        {
            $des_db=$this->get_des_db_instance();

            $index=$table['start'];
            $rows = $wpdb->get_results( "SELECT * FROM `{$old_table_name}` {$limit}", ARRAY_A );

            foreach ( $rows as $row )
            {
                $des_db->insert($new_table_name,$row);
                $index++;
            }

            if($this->is_time_limit_exceeded())
            {
                $table['start']=$index;
                $ret['result']='success';
                $ret['table']=$table;
                return $ret;
            }

            if($this->is_task_canceled())
            {
                $ret['result']='failed';
                $ret['error']='Creating staging cancelled.';
                return $ret;
            }
        }

        $table['start'] += $count;

        if( $table['start'] > $sum )
        {
            $table['finished']=1;
        }

        $ret['result']='success';
        $ret['table']=$table;
        return $ret;
    }

    public function str_replace_first($from, $to, $content)
    {
        $from = '/'.preg_quote($from, '/').'/';

        return preg_replace($from, $to, $content, 1);
    }
    
    public function create_table($table_name)
    {
        global $wpdb;

        $ret['result']='success';
        if($this->task['is_mu_single']&&($table_name==$wpdb->base_prefix.'users'||$table_name==$wpdb->base_prefix.'usermeta'))
        {
            $new_table_name=$this->str_replace_first($wpdb->prefix,$this->task['new_prefix'],$table_name);
        }
        else
        {
            $new_table_name=$this->str_replace_first($this->task['old_prefix'],$this->task['new_prefix'],$table_name);
        }

        if($this->is_same_database())
        {
            $wpdb->query("DROP TABLE IF EXISTS {$new_table_name}");
            if (false === $wpdb->query("CREATE TABLE `{$new_table_name}` LIKE `{$table_name}`"))
            {
                $error='Failed to create a table. Error:'.$wpdb->last_error;
                $this->log->WriteLog($error,'error');
                $ret['result']='failed';
                $ret['error']=$error;
                return $ret;
            }
        }
        else
        {
            $des_db_instance=$this->get_des_db_instance();
            $des_db_instance->query("DROP TABLE IF EXISTS {$new_table_name}");

            $result = $wpdb->get_results( "SHOW CREATE TABLE `{$table_name}`", ARRAY_A );
            if( isset($result[0]['Create Table']))
            {
                $query=$result[0]['Create Table'];

                $query = str_replace( "CREATE TABLE `{$table_name}`", "CREATE TABLE `{$new_table_name}`", $query );
                $query = str_replace( "CREATE TABLE \"{$table_name}\"", "CREATE TABLE \"{$new_table_name}\"", $query );

                $query = preg_replace_callback( "/CONSTRAINT\s`(\w+)`/", function()
                {
                    return "CONSTRAINT `" . uniqid() . "`";
                }, $query );

                $query = preg_replace_callback( "/REFERENCES\s`(\w+)`/", function($matches)
                {
                    return str_replace($this->task['old_prefix'],$this->task['new_prefix'],$matches[0]);
                }, $query );

                $query = preg_replace_callback( "/CONSTRAINT\s\"(\w+)\"/", function()
                {
                    return "CONSTRAINT `" . uniqid() . "`";
                }, $query );

                $query = preg_replace_callback( "/REFERENCES\s\"(\w+)\"/", function($matches)
                {
                    return str_replace($this->task['old_prefix'],$this->task['new_prefix'],$matches[0]);
                }, $query );

                if(!preg_match( '/PRIMARY KEY\s/', $query ))
                {
                    $des_db_instance->query('SET SQL_REQUIRE_PRIMARY_KEY=0;');
                }

                if( false === $des_db_instance->query( $query ) )
                {
                    $error='Failed to create a table. Error:'.$des_db_instance->last_error.', query:'.$query;
                    $this->log->WriteLog($error,'error');
                    $ret['result']='failed';
                    $ret['error']=$error;
                }
            }
            else
            {
                $error='Failed to retrieve the table structure. Table name: '.$table_name;
                $this->log->WriteLog($error,'error');
                $ret['result']='failed';
                $ret['error']=$error;
            }
        }

        return $ret;
    }

    public function do_replace_link()
    {
        if(!isset($this->task['jobs'][$this->current_job]['tables']))
        {
            $ret=$this->init_replace_tables_data();
            if($ret['result']=='failed')
            {
                return $ret;
            }
            $this->task['jobs'][$this->current_job]['tables']=$ret['tables'];
        }

        if($this->is_same_database())
        {
            global $wpdb;
            $wpdb->query('SET FOREIGN_KEY_CHECKS=0;');
        }
        else
        {
            $des=$this->get_des_db_instance();
            $des->query('SET FOREIGN_KEY_CHECKS=0;');
        }

        foreach ($this->task['jobs'][$this->current_job]['tables'] as $table_name=>$table)
        {
            if($table['finished']==1)
            {
                continue;
            }

            $ret=$this->replace_table($table);
            if($ret['result']=='failed')
            {
                return $ret;
            }

            $this->task['jobs'][$this->current_job]['tables'][$table_name]=$ret['table'];
            $this->update_task();
            if($this->is_time_limit_exceeded())
            {
                $ret['result']='success';
                return $ret;
            }
        }

        $ret['result']='success';
        $this->task['jobs'][$this->current_job]['finished']=1;
        $this->log->WriteLog('Copying db is completed.','notice');
        $this->task['status']['resume_count']=0;
        $this->update_task();
        return $ret;
    }
    
    public function replace_table($table)
    {
        $ret['result']='success';
        $ret['table']=$table;

        while(!$table['finished'])
        {
            if($this->is_task_canceled())
            {
                $ret['result']='failed';
                $ret['error']='Creating staging cancelled.';
                return $ret;
            }

            $ret=$this->replace_table_data($table);

            if($ret['result']=='failed')
            {
                return $ret;
            }

            $table=$ret['table'];

            if($table['finished'])
            {
                $this->log->WriteLog('Replacing '.$table['name'].' is completed.','notice');
            }
            else if($this->is_time_limit_exceeded())
            {
                $this->log->WriteLog($this->get_db_replace_count().' queries of '.$table['name'].' is replaced.','notice');
                return $ret;
            }
        }

        return $ret;
    }

    public function init_replace_data($new_site_url,$old_site_url)
    {
        $this->from=array();
        $this->to=array();

        $new_url_use_https=false;
        if (0 === stripos($new_site_url, 'https://')|| stripos($new_site_url, 'https:\/\/'))
        {
            $new_url_use_https=true;
        }
        else if (0 === stripos($new_site_url, 'http://')|| stripos($new_site_url, 'http:\/\/'))
        {
            $new_url_use_https=false;
        }

        if($old_site_url!=$new_site_url)
        {
            $remove_http_link=$this->get_remove_http_link($old_site_url);
            if($remove_http_link!==false)
            {
                $new_remove_http_link=$this->get_remove_http_link($new_site_url);
                $this->from[]=$remove_http_link;
                $this->to[]=$new_remove_http_link;

                if($new_url_use_https)
                {
                    $this->from[]='http:'.$new_remove_http_link;
                    $this->to[]='https:'.$new_remove_http_link;
                }
                else
                {
                    $this->from[]='https:'.$new_remove_http_link;
                    $this->to[]='http:'.$new_remove_http_link;
                }

                $quote_old_site_url=$this->get_http_link_at_quote($remove_http_link);
                $quote_new_site_url=$this->get_http_link_at_quote($new_remove_http_link);
                $this->from[]=$quote_old_site_url;
                $this->to[]=$quote_new_site_url;
                if($new_url_use_https)
                {
                    $this->from[]='http:'.$quote_new_site_url;
                    $this->to[]='https:'.$quote_new_site_url;
                }
                else
                {
                    $this->from[]='https:'.$quote_new_site_url;
                    $this->to[]='http:'.$quote_new_site_url;
                }
            }
            else
            {
                $remove_http_link=$this->get_remove_http_link_ex($old_site_url);
                if($remove_http_link!==false)
                {
                    $new_remove_http_link=$this->get_remove_http_link_ex($new_site_url);
                    $this->from[]=$remove_http_link;
                    $this->to[]=$new_remove_http_link;

                    if($new_url_use_https)
                    {
                        $this->from[]='http:'.$new_remove_http_link;
                        $this->to[]='https:'.$new_remove_http_link;
                    }
                    else
                    {
                        $this->from[]='https:'.$new_remove_http_link;
                        $this->to[]='http:'.$new_remove_http_link;
                    }
                }
            }

            $tmp_old_site_url = str_replace(':', '%3A', $old_site_url);
            $tmp_old_site_url = str_replace('/', '%2F', $tmp_old_site_url);

            $tmp_new_site_url = str_replace(':', '%3A', $new_site_url);
            $tmp_new_site_url = str_replace('/', '%2F', $tmp_new_site_url);

            $this->from[]=$tmp_old_site_url;
            $this->to[]=$tmp_new_site_url;
        }
    }

    private function get_remove_http_link($url)
    {
        if (0 === stripos($url, 'https://'))
        {
            $mix_link = '//'.substr($url, 8);
        } elseif (0 === stripos($url, 'http://')) {
            $mix_link = '//'.substr($url, 7);
        }
        else
        {
            $mix_link=false;
        }
        return $mix_link;
    }

    private function get_remove_http_link_ex($url)
    {
        if (0 === stripos($url, 'https://'))
        {
            $mix_link = '\/\/'.substr($url, 8);
        } elseif (0 === stripos($url, 'http://')) {
            $mix_link = '\/\/'.substr($url, 7);
        }
        else
        {
            $mix_link=false;
        }
        return $mix_link;
    }

    private function get_http_link_at_quote($url)
    {
        return str_replace('/','\/',$url);
    }

    public function replace_table_data($table)
    {
        $this->init_replace_data($this->task['new_site_url'],$this->task['old_site_url']);

        $replace_count=$this->get_db_replace_count();

        $this->replacing_table=$table['name'];
        $db=$this->get_des_db_instance();
        if(substr($table['name'], strlen($this->task['new_prefix']))=='usermeta')
        {
            $update_query ='UPDATE '.$table['name'].' SET meta_key=REPLACE(meta_key,\''.$this->task['old_prefix'].'\',\''.$this->task['new_prefix'].'\') WHERE meta_key LIKE \''.str_replace('_','\_',$this->task['old_prefix']).'%\';';
            $this->log->WriteLog($update_query, 'notice');
            $this->log->WriteLog('The length of UPDATE statement: '.strlen($update_query), 'notice');
            if ($db->get_results($update_query)===false)
            {
                $error=$db->last_error;
                $this->log->WriteLog($error, 'Warning');
            }
            
            if($this->task['is_mu_single'])
            {
                global $wpdb;
                $update_query ='UPDATE '.$table['name'].' SET meta_key=REPLACE(meta_key,\''.$wpdb->base_prefix.'\',\''.$this->task['new_prefix'].'\') WHERE meta_key LIKE \''.str_replace('_','\_',$wpdb->base_prefix).'%\';';
                $this->log->WriteLog($update_query, 'notice');
                $this->log->WriteLog('The length of UPDATE statement: '.strlen($update_query), 'notice');
                if ($db->get_results($update_query)===false)
                {
                    $error=$db->last_error;
                    $this->log->WriteLog($error, 'Warning');
                }
            }

            $table['finished']=1;
            $ret['result']='success';
            $ret['table']=$table;
            return $ret;
        }

        if(is_multisite())
        {
            if(substr($table['name'], strlen($this->task['new_prefix']))=='blogs')
            {
                $this->log->WriteLog('update mu blogs', 'notice');

                if((preg_match('#^https?://([^/]+)#i', $this->task['new_home_url'], $matches) || preg_match('#^https?://([^/]+)#i', $this->task['new_site_url'], $matches)) && (preg_match('#^https?://([^/]+)#i', $this->task['old_home_url'], $old_matches) || preg_match('#^https?://([^/]+)#i', $this->task['old_site_url'], $old_matches)))
                {
                    $new_string = strtolower($matches[1]);
                    $old_string = strtolower($old_matches[1]);

                    $query = 'SELECT * FROM `'.$table['name'].'`';
                    $result=$db->get_results($query,ARRAY_A);
                    if($result && sizeof($result)>0)
                    {
                        $rows = $result;
                        foreach ($rows as $row)
                        {
                            $update=array();
                            $where=array();

                            $old_domain_data = $row['domain'];
                            $new_domain_data=str_replace($old_string,$new_string,$old_domain_data);

                            $temp_where='`blog_id` = "' . $row['blog_id'] . '"';
                            if (is_callable(array($db, 'remove_placeholder_escape')))
                                $temp_where = $db->remove_placeholder_escape($temp_where);
                            $where[] = $temp_where;
                            $update[] = '`domain` = "' . $new_domain_data . '"';

                            $new_path_data=$this->task['mu_site'][$row['blog_id']]['path_site'];
                            $update[] = '`path` = "' . $new_path_data . '"';

                            if(!empty($update)&&!empty($where))
                            {
                                $update_query = 'UPDATE `'.$table['name'].'` SET '.implode(', ', $update).' WHERE '.implode(' AND ', array_filter($where)).';';
                                $this->log->WriteLog($update_query, 'notice');
                                $db->get_results($update_query);
                            }
                        }
                    }
                }
            }

            if(substr($table['name'], strlen($this->task['new_prefix']))=='site')
            {
                if($this->task['is_create_subdomain'])
                {
                    $this->log->WriteLog('Replace table subdomain staging: true', 'Warning');
                    $this->log->WriteLog('update mu site', 'notice');

                    if((preg_match('#^https?://([^/]+)#i', $this->task['new_home_url'], $matches) || preg_match('#^https?://([^/]+)#i', $this->task['new_site_url'], $matches)) && (preg_match('#^https?://([^/]+)#i', $this->task['old_home_url'], $old_matches) || preg_match('#^https?://([^/]+)#i', $this->task['old_site_url'], $old_matches)))
                    {
                        $new_string = strtolower($matches[1]);
                        $old_string = strtolower($old_matches[1]);

                        $query = 'SELECT * FROM `'.$table['name'].'`';
                        $result=$db->get_results($query,ARRAY_A);
                        if($result && sizeof($result)>0)
                        {
                            $rows = $result;
                            foreach ($rows as $row)
                            {
                                $update=array();
                                $where=array();

                                $old_domain_data = $row['domain'];
                                $new_domain_data=str_replace($old_string,$new_string,$old_domain_data);

                                $temp_where='`id` = "' . $row['id'] . '"';
                                if (is_callable(array($db, 'remove_placeholder_escape')))
                                    $temp_where = $db->remove_placeholder_escape($temp_where);
                                $where[] = $temp_where;
                                $update[] = '`domain` = "' . $new_domain_data . '"';

                                if(!empty($update)&&!empty($where))
                                {
                                    $update_query = 'UPDATE `'.$table['name'].'` SET '.implode(', ', $update).' WHERE '.implode(' AND ', array_filter($where)).';';
                                    $this->log->WriteLog($update_query, 'notice');
                                    $db->get_results($update_query);
                                }
                            }
                        }
                    }
                }
                else
                {
                    $this->log->WriteLog('Replace table subdomain staging: false', 'Warning');
                }
            }

        }

        $skip_table=false;
        if(apply_filters('wpvivid_restore_db_skip_replace_tables',$skip_table,$table['name']))
        {
            $this->log->WriteLog('Ignore table '.$table['name'], 'Warning');
            $table['finished']=1;
            $ret['result']='success';
            $ret['table']=$table;
            return $ret;
        }

        $count =$db->get_var("SELECT COUNT(1) FROM `{$table['name']}`");

        if($count>0)
        {
            $query='DESCRIBE `'.$table['name'].'`';
            $result=$db->get_results($query,ARRAY_A);
            if($result===false)
            {
                $error=$db->last_error;
                $this->log->WriteLog($error, 'Warning');
                $table['finished']=1;
                $ret['result']='success';
                $ret['table']=$table;
                return $ret;
            }
            $columns=array();
            foreach ($result as $data)
            {
                $column['Field']=$data['Field'];
                if($data['Key']=='PRI')
                    $column['PRI']=1;
                else
                    $column['PRI']=0;

                if($data['Type']=='mediumblob')
                {
                    $column['skip']=1;
                }
                $columns[]=$column;
            }

            $update_query='';
            $replace_row=0;
            $start_row=$table['start'];

            $page=$replace_count;

            for ($current_row = $start_row; $current_row <= $count; $current_row += $page)
            {
                $this->log->WriteLog('Start replacing '.$table['name'].' prefix from '.$current_row. ' row.', 'notice');
                $query = 'SELECT * FROM `'.$table['name'].'` LIMIT '.$current_row.', '.$page;

                $replace_row+=$page;
                $result=$db->get_results($query,ARRAY_A);

                if($result && sizeof($result)>0)
                {
                    $rows = $result;
                    $row_offset=$current_row;

                    foreach ($rows as $row)
                    {
                        if( isset( $row['option_value'] ) && strlen( $row['option_value'] ) >= 5000000 )
                        {
                            continue;
                        }

                        $update=array();
                        $where=array();
                        foreach ($columns as $column)
                        {
                            if(isset($column['skip']))
                            {
                                //$this->log->WriteLog('Skip MEDIUMBLOB data.', 'notice');
                                continue;
                            }
                            if($column['Field']=='option_name'&&$row[$column['Field']]=='mainwp_child_subpages')
                            {
                                break;
                            }
                            $old_data = $row[$column['Field']];
                            if (!is_null($old_data))
                            {
                                $size = strlen( $old_data );
                            }
                            else
                            {
                                $size = 0;
                            }
                            if( $size >= 5000000 )
                            {
                                continue;
                            }
                            if($column['PRI']==1)
                            {
                                $db->escape_by_ref($old_data);
                                $temp_where='`'.$column['Field'].'` = "' . $old_data . '"';
                                if (is_callable(array($db, 'remove_placeholder_escape')))
                                    $temp_where = $db->remove_placeholder_escape($temp_where);
                                $where[] = $temp_where;
                            }
                            $skip_row=false;
                            if(apply_filters('wpvivid_restore_db_skip_replace_rows',$skip_row,$table['name'],$column['Field']))
                            {
                                continue;
                            }
                            $new_data=$this->replace_row_data($old_data);
                            if($new_data==$old_data)
                                continue;
                            $db->escape_by_ref($new_data);
                            if (is_callable(array($db, 'remove_placeholder_escape')))
                                $new_data = $db->remove_placeholder_escape($new_data);
                            $update[] = '`'.$column['Field'].'` = "' . $new_data . '"';
                        }
                        if(!empty($update)&&!empty($where))
                        {
                            $temp_query = 'UPDATE `'.$table['name'].'` SET '.implode(', ', $update).' WHERE '.implode(' AND ', array_filter($where)).';';
                            $update_query=$temp_query;

                            if ($db->get_results($update_query)===false)
                            {
                                $error=$db->last_error;
                                $this->log->WriteLog($error, 'Warning');
                            }
                            $update_query='';
                        }

                        $row_offset++;
                        if($this->is_time_limit_exceeded())
                        {
                            $table['start']=$row_offset;

                            $ret['result']='success';
                            $ret['table']=$table;
                            return $ret;
                        }

                        if($this->is_time_limit_exceeded())
                        {
                            $current_row+= $page;
                            break;
                        }

                        if($this->is_task_canceled())
                        {
                            $ret['result']='failed';
                            $ret['error']='Creating staging cancelled.';
                            return $ret;
                        }
                    }
                }
                if(!empty($update_query))
                {
                    $this->log->WriteLog($update_query, 'notice');
                    if ($db->get_results($update_query)===false)
                    {
                        $error=$db->last_error;
                        $this->log->WriteLog($error, 'Warning');
                    }
                }
            }

            if(!empty($update_query))
            {
                $db->get_results($update_query);
            }

            if($current_row >= $count)
            {
                $replace_current_table_finish=1;
            }
            else
            {
                $replace_current_table_finish=0;
            }
        }
        else
        {
            $table['finished']=1;
            $ret['table']=$table;
            $ret['result']='success';
            return $ret;
        }

        $ret['result']='success';
        if($replace_current_table_finish==1)
        {
            $table['finished']=1;
            $this->log->WriteLog('Replacing database tables is completed.', 'notice');
        }
        else
        {
            $table['start'] = $current_row;
        }
        
        if($table['finished'])
        {
            if(substr($table['name'], strlen($this->task['new_prefix']))=='options')
            {
                $update_query ='UPDATE '.$table['name'].' SET option_name=\''.$this->task['new_prefix'].'user_roles\' WHERE option_name=\''.$this->task['old_prefix'].'user_roles\';';
                $this->log->WriteLog($update_query, 'notice');
                $this->log->WriteLog('The length of UPDATE statement: '.strlen($update_query), 'notice');
                if ($db->get_results($update_query)===false)
                {
                    $error=$db->last_error;
                    $this->log->WriteLog($error, 'Warning');
                }
            }
        }

        $ret['table']=$table;
        $ret['result']='success';
        return $ret;
    }

    public function replace_row_data($old_data)
    {
        try{
            $unserialize_data = @unserialize($old_data);
            if($unserialize_data===false)
            {
                $old_data=$this->replace_string_v2($old_data);
            }
            else
            {
                $old_data=$this->replace_serialize_data($unserialize_data);
                $old_data=serialize($old_data);
            }
        }
        catch (Error $error)
        {
            $old_data=$this->replace_string_v2($old_data);
        }

        return $old_data;
    }

    private function replace_serialize_data($data)
    {
        if(is_string($data))
        {
            $serialize_data =@unserialize($data);
            if($serialize_data===false)
            {
                $data=$this->replace_string_v2($data);
            }
            else
            {
                $data=serialize($this->replace_serialize_data($serialize_data));
            }
        }
        else if(is_array($data))
        {
            foreach ($data as $key => $value)
            {
                if(is_string($value))
                {
                    $data[$key]=$this->replace_string_v2($value);
                }
                else if(is_array($value))
                {
                    $data[$key]=$this->replace_serialize_data($value);
                }
                else if(is_object($value))
                {
                    if (is_a($value, '__PHP_Incomplete_Class'))
                    {
                        //
                    }
                    else
                    {
                        $data[$key]=$this->replace_serialize_data($value);
                    }
                }
            }
        }
        else if(is_object($data))
        {
            $temp = $data; // new $data_class();
            if (is_a($data, '__PHP_Incomplete_Class'))
            {

            }
            else
            {
                $props = get_object_vars($data);
                foreach ($props as $key => $value)
                {
                    if (strpos($key, "\0")===0)
                        continue;
                    if(is_string($value))
                    {
                        $temp->$key =$this->replace_string_v2($value);
                    }
                    else if(is_array($value))
                    {
                        $temp->$key=$this->replace_serialize_data($value);
                    }
                    else if(is_object($value))
                    {
                        $temp->$key=$this->replace_serialize_data($value);
                    }
                }
            }
            $data = $temp;
            unset($temp);
        }

        return $data;
    }

    public function replace_string_v2($old_string)
    {
        if(!is_string($old_string))
        {
            return $old_string;
        }


        if(!empty($this->from)&&!empty($this->to))
        {
            $old_string=str_replace($this->from,$this->to,$old_string);
        }

        return $old_string;
    }

    public function is_same_database()
    {
        if($this->task['db_connect']['use_additional_db']===false)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function get_des_db_instance()
    {
        if( $this->db_des_instance===false)
        {
            if($this->task['db_connect']['use_additional_db']===false)
            {
                global $wpdb;
                $this->db_des_instance=$wpdb;
                return $this->db_des_instance;
            }
            else
            {
                $this->db_des_instance=new wpdb($this->task['db_connect']['dbuser'],
                    $this->task['db_connect']['dbpassword'],
                    $this->task['db_connect']['dbname'],
                    $this->task['db_connect']['dbhost']);
                return $this->db_des_instance;
            }
        }
        else
        {
            return $this->db_des_instance;
        }
    }

    public function init_tables_data()
    {
        global $wpdb;
        $this->log->WriteLog('Retrieve the tables required to copy.','notice');

        $tables=array();

        $sql=$wpdb->prepare("SHOW TABLES LIKE %s;", $wpdb->esc_like($this->task['old_prefix']) . '%');
        $result = $wpdb->get_results($sql, OBJECT_K);

        if($result===false)
        {
            $error='Failed to retrieve database tables, error:'.$wpdb->last_error;
            $this->log->WriteLog($error,'error');
            $ret['result']='failed';
            $ret['error']=$error;
            return $ret;
        }

        if(empty($result))
        {
            $error='Tables not found in database.';
            $this->log->WriteLog($error,'error');
            $ret['result']='failed';
            $ret['error']=$error;
            return $ret;
        }

        foreach ($result as $table_name=>$value)
        {
            if($this->is_tables_exclude($table_name))
            {
                continue;
            }

            $table['name']=$table_name;
            $table['create']=0;
            $table['start']=0;
            $table['finished']=0;
            $tables[$table_name]=$table;
        }

        if($this->task['is_mu_single'])
        {
            $sql=$wpdb->prepare("SHOW TABLES LIKE %s;", $wpdb->esc_like($wpdb->base_prefix) . '%');
            $result = $wpdb->get_results($sql, OBJECT_K);
            foreach ($result as $table_name=>$value)
            {
                if($this->is_tables_exclude($table_name))
                {
                    continue;
                }

                $table['name']=$table_name;
                $table['create']=0;
                $table['start']=0;
                $table['finished']=0;
                $tables[$table_name]=$table;
            }
        }

        global $wpdb;
        $all_tables = (array) $wpdb->get_results( "SHOW FULL TABLES", ARRAY_N );
        if(!empty($all_tables) && !empty($tables))
        {
            foreach ($tables as $table_name => $table)
            {
                foreach ($all_tables as $table_arr)
                {
                    if($table_name === $table_arr[0] && $table_arr[1] === 'VIEW')
                    {
                        unset($tables[$table_name]);
                    }
                }
            }
        }

        $ret['result']='success';
        $ret['tables']=$tables;
        return $ret;
    }

    public function init_replace_tables_data()
    {
        global $wpdb;
        $this->log->WriteLog('Retrieve the tables required to replace.','notice');

        $tables=array();

        if($this->is_same_database())
        {
            $db=$wpdb;
        }
        else
        {
            $db=$this->get_des_db_instance();
        }
        
        $sql=$db->prepare("SHOW TABLES LIKE %s;", $db->esc_like($this->task['new_prefix']) . '%');
        $result = $db->get_results($sql, OBJECT_K);

        if($result===false)
        {
            $error='Failed to retrieve database tables, error:'.$db->last_error;
            $this->log->WriteLog($error,'error');
            $ret['result']='failed';
            $ret['error']=$error;
            return $ret;
        }

        if(empty($result))
        {
            $error='Tables not found in database.';
            $this->log->WriteLog($error,'error');
            $ret['result']='failed';
            $ret['error']=$error;
            return $ret;
        }

        foreach ($result as $table_name=>$value)
        {
            $table['name']=$table_name;
            $table['start']=0;
            $table['finished']=0;
            $tables[$table_name]=$table;
        }

        $ret['result']='success';
        $ret['tables']=$tables;
        return $ret;
    }
    
    public function is_tables_exclude($table)
    {
        $arr=$this->task['exclude_tables'];

        if(empty($arr))
            return false;

        return in_array($table, $arr);
    }

    public function clean_up()
    {
        @unlink($this->task['jobs'][$this->current_job]['create_cache_file']);
    }

    public function change_htaccess()
    {
        $des_path=$this->task['des_path'];
        $path=$des_path.DIRECTORY_SEPARATOR.'.htaccess';
        if(file_exists($path))
        {
            if(is_multisite())
            {
                $data=file_get_contents($path);
                //$data = str_replace(PATH_CURRENT_SITE,$mu_option['path_current_site'],$data);
                preg_match( "/RewriteBase\s*(.*)/", $data, $matches );
                if( !empty( $matches[1] ) )
                {
                    $new_rewrite_base = $this->task['path_current_site'];
                    $this->log->WriteLog('RewriteBase found in .htaccess','notice');
                    $pattern = "/RewriteBase\s*(.*)/";
                    $replace = "RewriteBase $new_rewrite_base";
                    $data = preg_replace( array($pattern), $replace, $data );
                    if( null === ($data) )
                    {
                        $this->log->WriteLog('WP_HOME not replace in wp-config.php','notice');
                    }
                }
                file_put_contents($path,$data);
            }
        }
    }

    public function change_wp_temp_config()
    {
        $src_path=$this->task['src_path'];

        $path=$src_path.DIRECTORY_SEPARATOR.'wp-config.php.vividtemp';
        $data=file_get_contents($path);
        if( $data === false )
        {
            $this->log->WriteLog('wp-config.php.vividtemp not found in '.$path,'notice');
            return false;
        }

        $pattern     = '/\$table_prefix\s*=\s*(.*)/';
        $replacement = '$table_prefix = \'' . $this->task['new_prefix'] . '\';';
        $data     = preg_replace( $pattern, $replacement, $data );


        if( $data===null )
        {
            $this->log->WriteLog('table_prefix not found in wp-config.php.vividtemp','notice');
            return false;
        }

        preg_match( "/define\s*\(\s*['\"]WP_HOME['\"]\s*,\s*(.*)\s*\);/", $data, $matches );
        if( !empty( $matches[1] ) )
        {
            $this->log->WriteLog('WP_HOME found in wp-config.php.vividtemp','notice');
            $pattern = "/define\s*\(\s*['\"]WP_HOME['\"]\s*,\s*(.*)\s*\);.*/";
            $replace = "define('WP_HOME','" . $this->task['new_home_url'] . "'); //";
            $data = preg_replace( array($pattern), $replace, $data );
            if( null === ($data) )
            {
                $this->log->WriteLog('WP_HOME not replace in wp-config.php.vividtemp','notice');
                return false;
            }
        }

        preg_match( "/define\s*\(\s*['\"]WP_SITEURL['\"]\s*,\s*(.*)\s*\);/", $data, $matches );
        if( !empty( $matches[1] ) )
        {
            $this->log->WriteLog('WP_SITEURL found in wp-config.php.vividtemp','notice');
            $pattern = "/define\s*\(\s*['\"]WP_SITEURL['\"]\s*,\s*(.*)\s*\);.*/";
            $replace = "define('WP_SITEURL','" .  $this->task['new_site_url'] . "'); //";
            $data = preg_replace( array($pattern), $replace, $data );
            if( null === ($data) )
            {
                $this->log->WriteLog('WP_SITEURL not replace in wp-config.php.vividtemp','notice');
                return false;
            }
        }

        if(is_multisite())
        {
            if($this->task['is_create_subdomain'])
            {
                $this->log->WriteLog('Change wp-config subdomain staging: true.','notice');
                preg_match( "/define\s*\(\s*['\"]DOMAIN_CURRENT_SITE['\"]\s*,\s*(.*)\s*\);/", $data, $matches );
                if( !empty( $matches[1] ) )
                {
                    $this->log->WriteLog('DOMAIN_CURRENT_SITE found in wp-config.php.vividtemp','notice');

                    $old_domain_current_site=$matches[1];
                    $old_site_url=$this->task['old_site_url'];
                    $old_home_url=$this->task['old_home_url'];
                    $new_site_url=$this->task['new_site_url'];
                    $new_home_url=$this->task['new_home_url'];
                    if((preg_match('#^https?://([^/]+)#i', $new_home_url, $matches) || preg_match('#^https?://([^/]+)#i', $new_site_url, $matches)) && (preg_match('#^https?://([^/]+)#i', $old_home_url, $old_matches) || preg_match('#^https?://([^/]+)#i', $old_site_url, $old_matches)))
                    {
                        $new_string = strtolower($matches[1]);
                        $old_string = strtolower($old_matches[1]);
                        $new_domain_current_site=str_replace($old_string,$new_string,$old_domain_current_site);
                        $pattern = "/define\s*\(\s*['\"]DOMAIN_CURRENT_SITE['\"]\s*,\s*(.*)\s*\);.*/";
                        $replace = "define('DOMAIN_CURRENT_SITE', ".$new_domain_current_site."); //";
                        $data = preg_replace( array($pattern), $replace, $data );
                        if( null === ($data) )
                        {
                            $this->log->WriteLog('DOMAIN_CURRENT_SITE not replace in wp-config.php.vividtemp','notice');
                            return false;
                        }
                        else
                        {
                            $this->log->WriteLog('DOMAIN_CURRENT_SITE is replaced in wp-config.php.vividtemp','notice');
                        }
                    }
                }
            }
            else
            {
                $this->log->WriteLog('Change wp-config subdomain staging: false','notice');
            }

            preg_match( "/define\s*\(\s*['\"]PATH_CURRENT_SITE['\"]\s*,\s*(.*)\s*\);/", $data, $matches );
            if( !empty( $matches[1] ) )
            {
                $this->log->WriteLog('PATH_CURRENT_SITE found in wp-config.php.vividtemp','notice');
                $pattern = "/define\s*\(\s*['\"]PATH_CURRENT_SITE['\"]\s*,\s*(.*)\s*\);.*/";
                $replace = "define('PATH_CURRENT_SITE','" .$this->task['path_current_site'] . "'); //";
                $data = preg_replace( array($pattern), $replace, $data );
                if( null === ($data) )
                {
                    $this->log->WriteLog('PATH_CURRENT_SITE not replace in wp-config.php.vividtemp','notice');
                    return false;
                }
            }

            if($this->task['is_mu_single'])
            {
                preg_match( "/define\s*\(\s*['\"]WP_ALLOW_MULTISITE['\"]\s*,\s*(.*)\s*\);/", $data, $matches );
                if( !empty( $matches[1] ) )
                {
                    $this->log->WriteLog('WP_ALLOW_MULTISITE found in wp-config.php.vividtemp','notice');
                    $pattern = "/define\s*\(\s*['\"]WP_ALLOW_MULTISITE['\"]\s*,\s*(.*)\s*\);.*/";
                    $replace = "define('WP_ALLOW_MULTISITE',false); //";
                    $data = preg_replace( array($pattern), $replace, $data );
                    if( null === ($data) )
                    {
                        $this->log->WriteLog('WP_ALLOW_MULTISITE not replace in wp-config.php.vividtemp','notice');
                        return false;
                    }
                }

                preg_match( "/define\s*\(\s*['\"]MULTISITE['\"]\s*,\s*(.*)\s*\);/", $data, $matches );
                if( !empty( $matches[1] ) )
                {
                    $this->log->WriteLog('MULTISITE found in wp-config.php.vividtemp','notice');
                    $pattern = "/define\s*\(\s*['\"]MULTISITE['\"]\s*,\s*(.*)\s*\);.*/";
                    $replace = "define('MULTISITE',false); //";
                    $data = preg_replace( array($pattern), $replace, $data );
                    if( null === ($data) )
                    {
                        $this->log->WriteLog('MULTISITE not replace in wp-config.php.vividtemp','notice');
                        return false;
                    }
                }

                preg_match( "/define\s*\(\s*['\"]UPLOADS['\"]\s*,\s*(.*)\s*\);/", $data, $matches );
                if( !empty( $matches[1] ) )
                {
                    $this->log->WriteLog('UPLOADS found in wp-config.php.vividtemp','notice');
                    $pattern = "/define\s*\(\s*['\"]UPLOADS['\"]\s*,\s*(.*)\s*\);.*/";
                    $replace = "define('UPLOADS','".$this->task['mu_single_upload']."'); //";
                    $data = preg_replace( array($pattern), $replace, $data );
                    if( null === ($data) )
                    {
                        $this->log->WriteLog('MULTISITE not replace in wp-config.php.vividtemp','notice');
                        return false;
                    }
                }
                else
                {
                    preg_match("/if\s*\(\s*\s*!\s*defined\s*\(\s*['\"]ABSPATH['\"]\s*(.*)\s*\)\s*\)/", $data, $matches);
                    if (!empty($matches[0]))
                    {
                        $matches[0];
                        $pattern = "/if\s*\(\s*\s*!\s*defined\s*\(\s*['\"]ABSPATH['\"]\s*(.*)\s*\)\s*\)/";
                        $replace = "define('UPLOADS', '".$this->task['mu_single_upload']."'); \n".
                            "if ( ! defined( 'ABSPATH' ) )";
                        $data = preg_replace( array($pattern), $replace, $data );
                        if (null === ($data))
                        {
                            $this->log->WriteLog('UPLOADS not replace in wp-config.php.vividtemp','notice');
                            return false;
                        }
                    }
                }
            }
        }

        if( $this->task['db_connect']['use_additional_db'])
        {
            $pattern     = "/define\s*\(\s*'DB_NAME'\s*,\s*(.*)\s*\);.*/";
            $replacement = "define( 'DB_NAME', '{$this->task['db_connect']['dbname']}');";
            $data     = preg_replace( $pattern, $replacement, $data );

            $pattern     = "/define\s*\(\s*'DB_USER'\s*,\s*(.*)\s*\);.*/";
            $replacement = "define( 'DB_USER', '{$this->task['db_connect']['dbuser']}');";
            $data     = preg_replace( $pattern, $replacement, $data );

            $pattern     = "/define\s*\(\s*'DB_PASSWORD'\s*,\s*(.*)\s*\);.*/";
            $replacement = "define( 'DB_PASSWORD', '{$this->task['db_connect']['dbpassword']}');";
            $data     = preg_replace( $pattern, $replacement, $data );

            $pattern     = "/define\s*\(\s*'DB_HOST'\s*,\s*(.*)\s*\);.*/";
            $replacement = "define( 'DB_HOST', '{$this->task['db_connect']['dbhost']}');";
            $data     = preg_replace( $pattern, $replacement, $data );
        }

        file_put_contents($path,$data);

        $this->log->WriteLog('Replacing table_prefix in wp-config.php.vividtemp is completed.','notice');
        return true;
    }

    public function get_files_copy_count()
    {
        if(isset($this->task['setting']['staging_file_copy_count']))
            $files_copy_count=$this->task['setting']['staging_file_copy_count'];
        else
            $files_copy_count=WPVIVID_STAGING_FILE_COPY_COUNT_EX;
        return $files_copy_count;
    }

    public function copy_files($cache_file,&$start,$count,$src_path,$des_path)
    {
        $file = new SplFileObject($cache_file);

        if($start==0)
            $file->seek($start);
        else
            $file->seek($start-1);

        $file->setFlags( \SplFileObject::SKIP_EMPTY | \SplFileObject::READ_AHEAD );

        for ( $i = 0; $i < $count; $i++ )
        {
            if( $file->eof() )
            {
                return false;
            }
            $src = $file->fgets();

            $src=trim($src,PHP_EOL);

            if(empty($src))
                continue;

            $start++;

            if(!file_exists($src))
            {
                continue;
            }
            $src=$this -> transfer_path($src);
            $des=str_replace($src_path,$des_path,$src);

            if(is_dir($src))
            {
                @mkdir($des,0755,true);
            }
            else
            {
                if(strpos($src,'wp-config.php')===false)
                {
                    if(copy($src,$des))
                    {
                        if(isset($this->task['setting']['force_files_mode'])&&$this->task['setting']['force_files_mode'])
                        {
                            @chmod($des,0644);
                        }
                        else
                        {
                            @chmod($des,0755);
                        }
                    }
                    else
                    {
                        $this->log->WriteLog('Failed to copy files from '.$src.' to '.$des.'.','warning');
                    }
                }
            }
        }

        $file = null;
        return true;
    }

    public function create_cache_file($list)
    {
        $cache_file_name=WP_CONTENT_DIR.DIRECTORY_SEPARATOR.WPVIVID_STAGING_PATH.DIRECTORY_SEPARATOR.$this->task['id'].'_staging_cache.txt';

        if(file_exists($cache_file_name))
            @unlink($cache_file_name);
        $cache_file=fopen($cache_file_name,'a');
        foreach ($list as $item)
        {
            $exclude_regex=array();
            $exclude_files_regex=array();
            if(isset($item['exclude_regex'])&&$item['exclude_regex']!=false)
            {
                $exclude_regex=$item['exclude_regex'];
            }
            if(isset($item['exclude_files_regex'])&&$item['exclude_files_regex']!=false)
            {
                $exclude_files_regex=$item['exclude_files_regex'];
            }
            //
            if(isset($item['include_regex'])&&$item['include_regex']!=false)
            {
                $include_regex=$item['include_regex'];
            }
            else
            {
                $include_regex=array();
            }

            $this->create_cache_from_folder($cache_file,$item['root'],$item['recursive'],$exclude_regex,$exclude_files_regex,$include_regex);
        }
        fclose($cache_file);
        return $cache_file_name;
    }

    public function create_cache_from_folder($cache_file,$folder,$recursive=false,$exclude_regex=array(),$exclude_files_regex=array(),$include_regex=array())
    {
        $this->getFolder($cache_file,$folder,$recursive,$exclude_regex,$exclude_files_regex,$include_regex);
    }

    public function getFolder($cache_file,$path,$recursive,$exclude_regex,$exclude_files_regex,$include_regex)
    {
        if(is_dir($path))
        {
            $line = $path.PHP_EOL;
            fwrite($cache_file, $line);

            $handler = opendir($path);
            if($handler!==false)
            {
                while (($filename = readdir($handler)) !== false)
                {
                    if ($filename != "." && $filename != "..")
                    {
                        if (is_dir($path . DIRECTORY_SEPARATOR . $filename))
                        {
                            if($recursive&&$this->regex_match($exclude_regex, $path . DIRECTORY_SEPARATOR . $filename, 0))
                            {
                                if(!empty($include_regex))
                                {
                                    if($recursive&&$this->regex_match($include_regex, $path . DIRECTORY_SEPARATOR . $filename, 1))
                                    {
                                        $this->getFolder($cache_file,$path . DIRECTORY_SEPARATOR . $filename,$recursive,$exclude_regex,$exclude_files_regex,$include_regex);
                                    }
                                }
                                else
                                {
                                    $this->getFolder($cache_file,$path . DIRECTORY_SEPARATOR . $filename,$recursive,$exclude_regex,$exclude_files_regex,$include_regex);
                                }
                            }
                        } else {

                            if($this->regex_match($exclude_files_regex, $filename, 0))
                            {
                                if ($this->regex_match($exclude_regex, $path . DIRECTORY_SEPARATOR . $filename, 0))
                                {
                                    if(is_readable($path . DIRECTORY_SEPARATOR . $filename))
                                    {
                                        if (filesize($path . DIRECTORY_SEPARATOR . $filename) < $this->get_exclude_file_size() * 1024 * 1024 || $this->get_exclude_file_size() === 0)
                                        {
                                            $line = $path . DIRECTORY_SEPARATOR . $filename.PHP_EOL;
                                            fwrite($cache_file, $line);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if($handler)
                    @closedir($handler);
            }
        }
    }

    public function get_exclude_file_size()
    {
        if(isset($this->task['setting']['staging_exclude_file_size']))
        {
            $exclude_file_size = $this->task['setting']['staging_exclude_file_size'];
        }
        else {
            $exclude_file_size = WPVIVID_STAGING_MAX_FILE_SIZE_EX;
        }
        return $exclude_file_size;
    }

    private function regex_match($regex_array,$string,$mode)
    {
        if(empty($regex_array))
        {
            return true;
        }

        if($mode==0)
        {
            foreach ($regex_array as $regex)
            {
                if(preg_match($regex,$string))
                {
                    return false;
                }
            }

            return true;
        }

        if($mode==1)
        {
            foreach ($regex_array as $regex)
            {
                if(preg_match($regex,$string))
                {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    public function get_copy_dir_list($key,&$src_path,&$des_path)
    {
        $list=array();
        $exclude_regex=$this->task['exclude_regex'];
        $exclude_files_regex=$this->task['exclude_files'];

        if($key=='core')
        {
            $src_path=$this->task['src_path'];
            $des_path=$this->task['des_path'];
            $dir_info['root']=$this -> transfer_path($src_path);
            $dir_info['recursive']=false;
            $list[]=$dir_info;
            $dir_info['root']=$src_path.DIRECTORY_SEPARATOR.'wp-admin';
            $dir_info['recursive']=true;
            $list[]=$dir_info;
            $dir_info['root']=$src_path.DIRECTORY_SEPARATOR.'wp-includes';
            $list[]=$dir_info;
        }
        else if($key=='wp-content')
        {
            $des_path=$this->get_content_dir($this->task['des_path']);
            $src_path=untrailingslashit($this->get_content_dir($this->task['src_path']));
            $dir_info['root']=$this -> transfer_path($src_path);
            $dir_info['recursive']=true;

            $exclude_regex[]='#^'.preg_quote($this -> transfer_path($this->get_content_dir($this->task['src_path'])).DIRECTORY_SEPARATOR.'updraft', '/').'#';   // Updraft Plus backup directory
            $exclude_regex[]='#^'.preg_quote($this -> transfer_path($this->get_content_dir($this->task['src_path'])).DIRECTORY_SEPARATOR.'ai1wm-backups', '/').'#'; // All-in-one WP migration backup directory
            $exclude_regex[]='#^'.preg_quote($this -> transfer_path($this->get_content_dir($this->task['src_path'])).DIRECTORY_SEPARATOR.'backups', '/').'#'; // Xcloner backup directory
            $exclude_regex[]='#^'.preg_quote($this -> transfer_path($this->get_content_dir($this->task['src_path'])).DIRECTORY_SEPARATOR.'upgrade', '/').'#';
            $exclude_regex[]='#^'.preg_quote($this -> transfer_path($this->get_content_dir($this->task['src_path'])).DIRECTORY_SEPARATOR.'wpvivid', '/').'#';
            $exclude_regex[]='#^'.preg_quote($this -> transfer_path($this->get_content_dir($this->task['src_path'])).DIRECTORY_SEPARATOR.WPVIVID_STAGING_PATH, '/').'#';
            $exclude_regex[]='#^'.preg_quote($this -> transfer_path($this->get_content_dir($this->task['src_path'])).DIRECTORY_SEPARATOR.'cache', '/').'#';
            $exclude_regex[]='#^'.preg_quote($this -> transfer_path($this->get_content_dir($this->task['src_path'])).DIRECTORY_SEPARATOR.'w3tc-config', '/').'#';
            $exclude_regex[]='#^'.preg_quote($this -> transfer_path($this->get_content_dir($this->task['src_path'])).DIRECTORY_SEPARATOR.'Dropbox_Backup', '/').'#';
            $exclude_regex[]='#^'.preg_quote($this -> transfer_path($this->get_content_dir($this->task['src_path'])).DIRECTORY_SEPARATOR.'WPvivid_Image_Optimization', '/').'#';

            $exclude_regex[]='#^'.preg_quote($this -> transfer_path($this->get_upload_dir($this->task['src_path'])), '/').'#';
            $exclude_regex[]='#^'.preg_quote($this->transfer_path($this->get_theme_dir($this->task['src_path'])), '/').'#';
            $exclude_regex[]='#^'.preg_quote($this->transfer_path($this->get_plugin_dir($this->task['src_path'])), '/').'#';

            $exclude_regex[]='#^'.preg_quote($this -> transfer_path($this->task['des_path']), '/').'#';

            global $wpvivid_staging;
            $staging_list = $wpvivid_staging->option->get_option('staging_site_data');

            if(!empty($staging_list))
            {
                foreach ($staging_list as $key => $value)
                {
                    $exclude_regex[]='#^'.preg_quote($this -> transfer_path($value['path']), '/').'$#';
                }
            }

            $dir_info['exclude_regex']=$exclude_regex;
            $dir_info['exclude_files_regex']=$exclude_files_regex;
            $list[]=$dir_info;
        }
        else if($key=='plugins')
        {
            $des_path=$this->get_plugin_dir($this->task['des_path']);
            $src_path=untrailingslashit($this->get_plugin_dir($this->task['src_path']));

            $dir_info['root']=$this -> transfer_path($src_path);
            $dir_info['exclude_regex']=$exclude_regex;
            $dir_info['exclude_files_regex']=$exclude_files_regex;
            $dir_info['recursive']=true;
            $list[]=$dir_info;
        }
        else if($key=='themes')
        {
            $des_path=$this->get_theme_dir($this->task['des_path']);
            $src_path=$this->get_theme_dir($this->task['src_path']);
            $dir_info['root']=$this -> transfer_path($src_path);
            $dir_info['exclude_regex']=$exclude_regex;
            $dir_info['exclude_files_regex']=$exclude_files_regex;
            $dir_info['recursive']=true;
            $list[]=$dir_info;
        }
        else if($key=='upload')
        {
            $des_path=$this->get_upload_dir($this->task['des_path']);
            $src_path=$this->get_upload_dir($this->task['src_path']);
            $dir_info['root']=$this -> transfer_path($src_path);
            $dir_info['exclude_regex']=$exclude_regex;
            $dir_info['exclude_files_regex']=$exclude_files_regex;
            $dir_info['recursive']=true;
            $list[]=$dir_info;
        }
        else
        {
            $src_path=$this->task['src_path'];
            $des_path=$this->task['des_path'];
            foreach ($this->task['custom'] as $path)
            {
                $dir_info['root']=$this -> transfer_path($src_path.DIRECTORY_SEPARATOR.$path);
                $dir_info['exclude_regex']=$exclude_regex;
                $dir_info['exclude_files_regex']=$exclude_files_regex;
                $dir_info['recursive']=true;
                $list[]=$dir_info;
            }
        }

        $src_path=$this -> transfer_path($src_path);
        $des_path=$this -> transfer_path($des_path);

        return $list;
    }

    public function get_content_dir($root)
    {
        $dir = str_replace( ABSPATH, '', WP_CONTENT_DIR );
        return $root.DIRECTORY_SEPARATOR.$dir;
    }

    public function get_upload_dir($root)
    {
        $upload_dir = wp_upload_dir();
        $dir = str_replace( ABSPATH, '', $upload_dir['basedir'] );
        return $root.DIRECTORY_SEPARATOR.$dir;
    }

    public function get_theme_dir($root)
    {
        $dir = str_replace( ABSPATH, '',get_theme_root() );
        return $root.DIRECTORY_SEPARATOR.$dir;
    }

    public function get_plugin_dir($root)
    {
        $dir = str_replace( ABSPATH, '',WP_PLUGIN_DIR );
        return $root.DIRECTORY_SEPARATOR.$dir;
    }

    private function transfer_path($path)
    {
        $path = str_replace('\\','/',$path);
        $values = explode('/',$path);
        return implode(DIRECTORY_SEPARATOR,$values);
    }

    public function set_db_connect_option($additional_db_options)
    {
        if(isset($additional_db_options['additional_database_check']) && $additional_db_options['additional_database_check'] == '1')
        {
            $this->task['db_connect']['use_additional_db'] = true;
            $this->task['db_connect']['dbuser'] = $additional_db_options['additional_database_info']['db_user'];
            $this->task['db_connect']['dbpassword'] = $additional_db_options['additional_database_info']['db_pass'];
            $this->task['db_connect']['dbname'] = $additional_db_options['additional_database_info']['db_name'];
            $this->task['db_connect']['dbhost'] = $additional_db_options['additional_database_info']['db_host'];
        }
        else
        {
            $this->task['db_connect']['use_additional_db'] = false;
        }
    }

    public function setup_mu($options)
    {
        global $wpdb;

        $this->task['path_current_site']=$options['path_current_site'];
        $this->task['main_site_id']=$options['main_site_id'];
        $this->task['mu_site']=$options['mu_site'];

        if($options['is_mu'])
        {
            $subsites = get_sites();
            $mu_exclude_table=array();
            $mu_upload_exclude=array();

            if($options['all_site'])
            {

            }
            else
            {
                foreach ($subsites as $subsite)
                {
                    $subsite_id = get_object_vars($subsite)["blog_id"];
                    if(array_key_exists($subsite_id,$options['mu_site_list']))
                    {
                        if($options['mu_site_list'][$subsite_id]['tables']==0)
                        {
                            if(!is_main_site($subsite_id))
                            {
                                $prefix=$wpdb->get_blog_prefix($subsite_id);
                                $mu_exclude_table=$this->get_table_list($prefix,$mu_exclude_table);
                            }
                        }

                        if($options['mu_site_list'][$subsite_id]['folders']==0)
                        {
                            if(!is_main_site($subsite_id))
                                $mu_upload_exclude[]=$this->get_upload_exclude_folder($subsite_id);
                        }
                    }
                    else
                    {
                        $prefix=$wpdb->get_blog_prefix($subsite_id);
                        $mu_exclude_table=$this->get_table_list($prefix,$mu_exclude_table);
                        if(!is_main_site($subsite_id))
                            $mu_upload_exclude[]=$this->get_upload_exclude_folder($subsite_id);
                    }
                }

                foreach ($mu_exclude_table as $table)
                {
                    $this->task['exclude_tables'][] = $table;
                }

                foreach ($mu_upload_exclude as $value)
                {
                    $this->task['exclude_regex'][] = '#^'.preg_quote($this -> transfer_path($value), '/').'#';
                }
            }
        }
        else if($options['is_mu_single'])
        {
            $mu_single_site_id=$options['mu_single_site_id'];

            $this->task['old_prefix']=$wpdb->get_blog_prefix($mu_single_site_id);

            $this->task['old_site_url']=get_site_url($mu_single_site_id);
            $this->task['old_home_url']=get_home_url($mu_single_site_id);

            $subsites = get_sites();
            $mu_exclude_table=array();
            $mu_upload_exclude=array();
            foreach ($subsites as $subsite)
            {
                $subsite_id = get_object_vars($subsite)["blog_id"];
                if($mu_single_site_id==$subsite_id)
                {
                    continue;
                }
                else
                {
                    $prefix=$wpdb->get_blog_prefix($subsite_id);
                    $mu_exclude_table=$this->get_table_list($prefix,$mu_exclude_table,false);
                    if(!is_main_site($subsite_id))
                        $mu_upload_exclude[]=$this->get_upload_exclude_folder($subsite_id);
                }
            }

            $upload_path=$this->get_upload_exclude_folder($mu_single_site_id);
            $this->task['mu_single_upload']=str_replace(ABSPATH,'',$upload_path);
            $this->task['mu_single_site_id']=$mu_single_site_id;

            $this->task['exclude_tables'][] =$wpdb->base_prefix.'site';
            $this->task['exclude_tables'][] =$wpdb->base_prefix.'sitemeta';
            $this->task['exclude_tables'][] =$wpdb->base_prefix.'blogs';
            $this->task['exclude_tables'][] =$wpdb->base_prefix.'blogmeta';

            foreach ($mu_exclude_table as $table)
            {
                $this->task['exclude_tables'][] = $table;
            }

            foreach ($mu_upload_exclude as $value)
            {
                $this->task['exclude_regex'][] = '#^'.preg_quote($this -> transfer_path($value), '/').'#';
            }
        }
    }

    public function get_table_list($prefix,$mu_exclude_table,$exclude_user=true)
    {
        global $wpdb;

        $sql=$wpdb->prepare("SHOW TABLES LIKE %s;", $wpdb->esc_like($prefix) . '%');
        $result = $wpdb->get_results($sql, OBJECT_K);
        foreach ($result as $table_name=>$value)
        {
            if($prefix==$wpdb->base_prefix)
            {
                if ( 1 == preg_match('/^' . $wpdb->base_prefix . '\d+_/', $table_name) )
                {

                }
                else
                {
                    if($table_name==$wpdb->base_prefix.'blogs'&&$exclude_user!==false)
                        continue;
                    if($exclude_user===false)
                    {
                        if($table_name==$wpdb->base_prefix.'users'||$table_name==$wpdb->base_prefix.'usermeta')
                            continue;
                    }
                    $mu_exclude_table[]=$table_name;
                }
            }
            else
            {
                $mu_exclude_table[]=$table_name;
            }
        }

        return $mu_exclude_table;
    }

    public function get_upload_exclude_folder($site_id)
    {
        $upload= $this->get_site_upload_dir($site_id);
        return $upload['basedir'];
    }

    public function get_site_upload_dir($site_id, $time = null, $create_dir = true, $refresh_cache = false)
    {
        static $cache = array(), $tested_paths = array();

        $key = sprintf( '%d-%s',$site_id, (string) $time );

        if ( $refresh_cache || empty( $cache[ $key ] ) ) {
            $cache[ $key ] = $this->_wp_upload_dir( $site_id,$time );
        }

        /**
         * Filters the uploads directory data.
         *
         * @since 2.0.0
         *
         * @param array $uploads Array of upload directory data with keys of 'path',
         *                       'url', 'subdir, 'basedir', and 'error'.
         */
        $uploads = apply_filters( 'upload_dir', $cache[ $key ] );

        if ( $create_dir ) {
            $path = $uploads['path'];

            if ( array_key_exists( $path, $tested_paths ) ) {
                $uploads['error'] = $tested_paths[ $path ];
            } else {
                if ( ! wp_mkdir_p( $path ) ) {
                    if ( 0 === strpos( $uploads['basedir'], ABSPATH ) ) {
                        $error_path = str_replace( ABSPATH, '', $uploads['basedir'] ) . $uploads['subdir'];
                    } else {
                        $error_path = basename( $uploads['basedir'] ) . $uploads['subdir'];
                    }

                    $uploads['error'] = sprintf(
                    /* translators: %s: directory path */
                        __( 'Unable to create directory %s. Is its parent directory writable by the server?' ),
                        esc_html( $error_path )
                    );
                }

                $tested_paths[ $path ] = $uploads['error'];
            }
        }

        return $uploads;
    }

    public function _wp_upload_dir($site_id, $time = null ) {
        $siteurl     = get_option( 'siteurl' );
        $upload_path = trim( get_option( 'upload_path' ) );

        if ( empty( $upload_path ) || 'wp-content/uploads' == $upload_path ) {
            $dir = WP_CONTENT_DIR . '/uploads';
        } elseif ( 0 !== strpos( $upload_path, ABSPATH ) ) {
            // $dir is absolute, $upload_path is (maybe) relative to ABSPATH
            $dir = path_join( ABSPATH, $upload_path );
        } else {
            $dir = $upload_path;
        }

        if ( ! $url = get_option( 'upload_url_path' ) ) {
            if ( empty( $upload_path ) || ( 'wp-content/uploads' == $upload_path ) || ( $upload_path == $dir ) ) {
                $url = WP_CONTENT_URL . '/uploads';
            } else {
                $url = trailingslashit( $siteurl ) . $upload_path;
            }
        }

        /*
         * Honor the value of UPLOADS. This happens as long as ms-files rewriting is disabled.
         * We also sometimes obey UPLOADS when rewriting is enabled -- see the next block.
         */
        if ( defined( 'UPLOADS' ) && ! ( is_multisite() && get_site_option( 'ms_files_rewriting' ) ) ) {
            $dir = ABSPATH . UPLOADS;
            $url = trailingslashit( $siteurl ) . UPLOADS;
        }

        // If multisite (and if not the main site in a post-MU network)
        if ( is_multisite() && ! ( is_main_network() && is_main_site($site_id) && defined( 'MULTISITE' ) ) ) {
            if ( ! get_site_option( 'ms_files_rewriting' ) ) {
                /*
                 * If ms-files rewriting is disabled (networks created post-3.5), it is fairly
                 * straightforward: Append sites/%d if we're not on the main site (for post-MU
                 * networks). (The extra directory prevents a four-digit ID from conflicting with
                 * a year-based directory for the main site. But if a MU-era network has disabled
                 * ms-files rewriting manually, they don't need the extra directory, as they never
                 * had wp-content/uploads for the main site.)
                 */

                if ( defined( 'MULTISITE' ) ) {
                    $ms_dir = '/sites/' . $site_id;
                } else {
                    $ms_dir = '/' . $site_id;
                }

                $dir .= $ms_dir;
                $url .= $ms_dir;
            } elseif ( defined( 'UPLOADS' ) && ! ms_is_switched() ) {
                /*
                 * Handle the old-form ms-files.php rewriting if the network still has that enabled.
                 * When ms-files rewriting is enabled, then we only listen to UPLOADS when:
                 * 1) We are not on the main site in a post-MU network, as wp-content/uploads is used
                 *    there, and
                 * 2) We are not switched, as ms_upload_constants() hardcodes these constants to reflect
                 *    the original blog ID.
                 *
                 * Rather than UPLOADS, we actually use BLOGUPLOADDIR if it is set, as it is absolute.
                 * (And it will be set, see ms_upload_constants().) Otherwise, UPLOADS can be used, as
                 * as it is relative to ABSPATH. For the final piece: when UPLOADS is used with ms-files
                 * rewriting in multisite, the resulting URL is /files. (#WP22702 for background.)
                 */

                if ( defined( 'BLOGUPLOADDIR' ) ) {
                    $dir = untrailingslashit( BLOGUPLOADDIR );
                } else {
                    $dir = ABSPATH . UPLOADS;
                }
                $url = trailingslashit( $siteurl ) . 'files';
            }
        }

        $basedir = $dir;
        $baseurl = $url;

        $subdir = '';
        if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
            // Generate the yearly and monthly dirs
            if ( ! $time ) {
                $time = current_time( 'mysql' );
            }
            $y      = substr( $time, 0, 4 );
            $m      = substr( $time, 5, 2 );
            $subdir = "/$y/$m";
        }

        $dir .= $subdir;
        $url .= $subdir;

        return array(
            'path'    => $dir,
            'url'     => $url,
            'subdir'  => $subdir,
            'basedir' => $basedir,
            'baseurl' => $baseurl,
            'error'   => false,
        );
    }

    public function set_time_limit()
    {
        //max_execution_time
        @ini_set('memory_limit', $this->task['setting']['staging_memory_limit']);
        @set_time_limit( $this->task['setting']['staging_max_execution_time']);
        $this->task['status']['timeout']=time();
        $this->update_task();
    }

    public function get_db_insert_count()
    {
        if(isset($this->task['setting']['staging_db_insert_count']))
            $db_insert_count=$this->task['setting']['staging_db_insert_count'];
        else
            $db_insert_count=WPVIVID_STAGING_DB_INSERT_COUNT_EX;
        return $db_insert_count;
    }

    public function get_db_replace_count()
    {
        if(isset($this->task['setting']['staging_db_replace_count']))
            $db_replace_count=$this->task['setting']['staging_db_replace_count'];
        else
            $db_replace_count=WPVIVID_STAGING_DB_REPLACE_COUNT_EX;
        return $db_replace_count;
    }
    
    public function get_time_limit()
    {
        return $this->task['setting']['staging_max_execution_time'];
    }

    public function get_max_resume_count()
    {
        return $this->task['setting']['staging_resume_count'];
    }

    public function get_status()
    {
        return $this->task['status'];
    }

    public function update_task_status($reset_start_time=false,$status='',$reset_timeout=false,$resume_count=false,$error='')
    {
        $this->task['status']['run_time']=time();
        if($reset_start_time)
            $this->task['status']['start_time']=time();
        if(!empty($status))
        {
            $this->task['status']['str']=$status;
        }
        if($reset_timeout)
            $this->task['status']['timeout']=time();
        if($resume_count!==false)
        {
            $this->task['status']['resume_count']=$resume_count;
        }

        if(!empty($error))
        {
            $this->task['status']['error']=$error;
        }

        $this->update_task();
    }

    public function update_task()
    {
        wp_cache_flush();
        $this->task['status']['run_time']=time();
        $this->options->update_option('wpvivid_staging_task_ex',$this->task);
    }

    public function get_progress()
    {
        $progress['main_percent']='0';
        $progress['doing']='';

        $finished_job=0;
        $total_jobs=count($this->task['jobs']);
        foreach ($this->task['jobs'] as $job)
        {
            if($job['finished']==1)
            {
                $finished_job++;
            }
        }

        if($total_jobs==0)
        {
            return $progress;
        }

        $i_progress=intval(($finished_job/$total_jobs)*100);
        $progress['main_percent']=$i_progress;

        $progress['doing']= $this->task['current_doing'];

        return $progress;
    }

    public function is_time_limit_exceeded()
    {
        $time_limit =isset($this->task['setting']['request_timeout'])?$this->task['setting']['request_timeout']:60;
        $time_taken = microtime(true) - $this->task['status']['start_time'];
        if($time_taken >= $time_limit)
        {
            return true;
        }

        return false;
    }

    public function is_task_canceled()
    {
        if($this->options->get_option('staging_task_cancel')==true)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function clean_tmp()
    {
        set_time_limit(120);
        $prefix=$this->task['new_prefix'];

        $db_instance=$this->get_des_db_instance();
        $sql=$db_instance->prepare("SHOW TABLES LIKE %s;", $db_instance->esc_like($prefix) . '%');
        $result = $db_instance->get_results($sql, OBJECT_K);
        if(!empty($result))
        {
            $db_instance->query( "SET foreign_key_checks = 0" );
            foreach ($result as $table_name=>$value)
            {
                $table['name']=$table_name;
                $db_instance->query( "DROP TABLE IF EXISTS {$table_name}" );
            }
        }

        $site_path=$this->task['des_path'];
        $home_path=untrailingslashit(ABSPATH);

        if($home_path!=$site_path)
        {
            if (file_exists($site_path))
            {
                if (!class_exists('WP_Filesystem_Base')) include_once(ABSPATH . '/wp-admin/includes/class-wp-filesystem-base.php');
                if (!class_exists('WP_Filesystem_Direct')) include_once(ABSPATH . '/wp-admin/includes/class-wp-filesystem-direct.php');

                $fs = new WP_Filesystem_Direct(false);
                $fs->rmdir($site_path, true);
            }
        }
    }
}