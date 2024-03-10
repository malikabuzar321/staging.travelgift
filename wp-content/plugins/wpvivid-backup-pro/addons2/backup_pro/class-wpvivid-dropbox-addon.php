<?php
/**
 * WPvivid addon: yes
 * Addon Name: wpvivid-backup-pro-all-in-one
 * Description: Pro
 * Version: 2.2.13
 * Need_init: yes
 * Interface Name: WPvivid_Dropbox_addon
 */
if (!defined('WPVIVID_BACKUP_PRO_PLUGIN_DIR'))
{
    die;
}

if(!defined('WPVIVID_REMOTE_DROPBOX')){
    define('WPVIVID_REMOTE_DROPBOX','dropbox');
}
if(!defined('WPVIVID_DROPBOX_DEFAULT_FOLDER'))
    define('WPVIVID_DROPBOX_DEFAULT_FOLDER','/');

//require_once WPVIVID_PLUGIN_DIR . '/includes/customclass/class-wpvivid-base-dropbox.php';

class WPvivid_Dropbox_addon extends WPvivid_Remote_addon
{
    private $options;
    private $upload_chunk_size = 2097152;
    private $download_chunk_size = 2097152;
    private $redirect_url = 'https://auth.wpvivid.com/dropbox_v3/';
    public $add_remote;
    public function __construct($options = array())
    {
        if(empty($options))
        {
            if(!defined('WPVIVID_INIT_STORAGE_TAB_DROPBOX'))
            {
                add_action('init', array($this, 'handle_auth_actions'));
                add_action('wp_ajax_wpvivid_dropbox_add_remote', array($this, 'finish_add_remote'));
                add_action('wpvivid_add_storage_page_dropbox', array($this, 'wpvivid_add_storage_page_dropbox'));
                add_action('wpvivid_delete_remote_token',array($this,'revoke'));
                add_filter('wpvivid_remote_register', array($this, 'init_remotes'),10);
                add_action('wpvivid_add_storage_page',array($this,'wpvivid_add_storage_page_dropbox'), 10);
                add_action('wpvivid_edit_remote_page',array($this,'wpvivid_edit_storage_page_dropbox'), 10);
                add_filter('wpvivid_get_out_of_date_remote',array($this,'wpvivid_get_out_of_date_dropbox'),10,2);
                add_filter('wpvivid_storage_provider_tran',array($this,'wpvivid_storage_provider_dropbox'),10);
                add_filter('wpvivid_get_root_path',array($this,'wpvivid_get_root_path_dropbox'),10);
                add_filter('wpvivid_pre_add_remote',array($this, 'pre_add_remote'),10,2);
                add_filter('wpvivid_remote_register', array($this, 'init_remotes'),11);
                define('WPVIVID_INIT_STORAGE_TAB_DROPBOX',1);
            }
        }else{
            $this -> options = $options;
        }
        $this->add_remote=false;
    }

    public function init_remotes($remote_collection)
    {
        require_once WPVIVID_PLUGIN_DIR . '/includes/customclass/class-wpvivid-base-dropbox.php';

        $remote_collection[WPVIVID_REMOTE_DROPBOX]='WPvivid_Dropbox_addon';
        return $remote_collection;
    }

    public function pre_add_remote($remote,$id)
    {
        if($remote['type']==WPVIVID_REMOTE_DROPBOX)
        {
            $remote['id']=$id;
        }

        return $remote;
    }

    public function test_connect()
    {
        return array('result' => WPVIVID_PRO_SUCCESS);
    }

    public function sanitize_options($skip_name='')
    {
        $ret['result']=WPVIVID_PRO_FAILED;

        if(!isset($this->options['name']))
        {
            $ret['error']="Warning: An alias for remote storage is required.";
            return $ret;
        }

        $this->options['name']=sanitize_text_field($this->options['name']);

        if(empty($this->options['name']))
        {
            $ret['error']="Warning: An alias for remote storage is required.";
            return $ret;
        }

        $remoteslist=WPvivid_Setting::get_all_remote_options();
        foreach ($remoteslist as $key=>$value)
        {
            if(isset($value['name'])&&$value['name'] == $this->options['name']&&$skip_name!=$value['name'])
            {
                $ret['error']="Warning: The alias already exists in storage list.";
                return $ret;
            }
        }
        $ret['result']=WPVIVID_PRO_SUCCESS;
        $ret['options']=$this->options;
        return $ret;
    }

    public function upload($task_id, $files, $callback = '')
    {
        global $wpvivid_plugin;

        $options = $this -> options;
        $dropbox = new Dropbox_Base($options);
        $ret=$dropbox->check_token();
        if($ret['result']=='failed')
        {
            return $ret;
        }
        $upload_job=WPvivid_taskmanager::get_backup_sub_task_progress($task_id,'upload',$this->options['id']);
        if(empty($upload_job))
        {
            $job_data=array();
            foreach ($files as $file)
            {
                $file_data['size']=filesize($file);
                $file_data['uploaded']=0;
                $job_data[basename($file)]=$file_data;
            }
            WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',$this->options['id'],WPVIVID_UPLOAD_UNDO,'Start uploading',$job_data);
            $upload_job=WPvivid_taskmanager::get_backup_sub_task_progress($task_id,'upload',$this->options['id']);
        }

        foreach ($files as $file){
            if(is_array($upload_job['job_data']) &&array_key_exists(basename($file),$upload_job['job_data']))
            {
                if($upload_job['job_data'][basename($file)]['uploaded']==1)
                    continue;
            }

            $ret=$dropbox->check_token();
            if($ret['result']=='failed')
            {
                return $ret;
            }

            $this -> last_time = time();
            $this -> last_size = 0;
            $wpvivid_plugin->wpvivid_log->WriteLog('Start uploading '.basename($file),'notice');
            $wpvivid_plugin->set_time_limit($task_id);
            if(!file_exists($file))
                return array('result' =>WPVIVID_PRO_FAILED,'error' =>$file.' not found. The file might has been moved, renamed or deleted. Please reload the list and verify the file exists.');
            $result = $this -> _put($task_id,$dropbox,$file,$callback);
            if($result['result'] !==WPVIVID_PRO_SUCCESS)
            {
                $wpvivid_plugin->wpvivid_log->WriteLog('Uploading '.basename($file).' failed.','notice');
                return $result;
            }
            $upload_job['job_data'][basename($file)]['uploaded']=1;
            $wpvivid_plugin->wpvivid_log->WriteLog('Finished uploading '.basename($file),'notice');
            WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',$this->options['id'],WPVIVID_UPLOAD_SUCCESS,'Uploading '.basename($file).' completed.',$upload_job['job_data']);
        }
        return array('result' =>WPVIVID_PRO_SUCCESS);
    }
    private function _put($task_id,$dropbox,$file,$callback)
    {
        global $wpvivid_plugin;
        $options = $this -> options;
        $path = '/'.$options['path'].'/'.basename($file);
        $wpvivid_plugin->wpvivid_log->WriteLog($path,'notice');
        $upload_job=WPvivid_taskmanager::get_backup_sub_task_progress($task_id,'upload',$this->options['id']);
        $this -> current_file_size = filesize($file);
        $this -> current_file_name = basename($file);

        if($this -> current_file_size > $this -> upload_chunk_size){
            $wpvivid_plugin->wpvivid_log->WriteLog('Creating upload session.','notice');
            WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',$this->options['id'],WPVIVID_UPLOAD_UNDO,'Start uploading '.basename($file).'.',$upload_job['job_data']);
            $result = $dropbox -> upload_session_start();
            if(isset($result['error_summary'])){
                return array('result'=>WPVIVID_PRO_FAILED,'error'=>$result['error_summary']);
            }
            $build_id = $result['session_id'];
            $result = $this -> large_file_upload($build_id,$file,$dropbox,$callback);
        }else{
            $wpvivid_plugin->wpvivid_log->WriteLog('Uploaded files are less than 2M.','notice');
            $result = $dropbox -> upload($path,$file);
            if(isset($result['error_summary'])){
                WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',$this->options['id'],WPVIVID_UPLOAD_FAILED,'Uploading '.basename($file).' failed.',$upload_job['job_data']);
                $result = array('result' => WPVIVID_PRO_FAILED,'error' => $result['error_summary']);
            }else{
                WPvivid_taskmanager::update_backup_sub_task_progress($task_id,'upload',$this->options['id'],WPVIVID_UPLOAD_SUCCESS,'Uploading '.basename($file).' completed.',$upload_job['job_data']);
                $result = array('result'=> WPVIVID_PRO_SUCCESS);
            }
        }
        return $result;
    }

    public function large_file_upload($session_id,$file,$dropbox,$callback){
        $fh = fopen($file,'rb');
        $offset = 0;
        $ret['result']=WPVIVID_PRO_SUCCESS;
        while(!feof($fh))
        {
            $data = fread($fh,$this -> upload_chunk_size);
            $ret = $this -> _upload_loop($session_id,$offset,$data,$dropbox);
            if($ret['result'] !== WPVIVID_PRO_SUCCESS)
                break;

            if((time() - $this -> last_time) >3)
            {
                if(is_callable($callback))
                {
                    call_user_func_array($callback,array(min($offset + $this -> upload_chunk_size,$this -> current_file_size),$this -> current_file_name,
                        $this->current_file_size,$this -> last_time,$this -> last_size));
                }
                $this -> last_size = $offset;
                $this -> last_time = time();
            }
            $offset += $this -> upload_chunk_size;
        }
        if($ret['result'] === WPVIVID_PRO_SUCCESS)
        {
            $options = $this -> options;
            $path = '/'.$options['path'].'/'.basename($file);
            $result = $dropbox -> upload_session_finish($session_id,$this -> current_file_size,$path);
            if(isset($result['error_summary']))
            {
                $ret = array('result' => WPVIVID_PRO_FAILED,'error' => $result['error_summary']);
            }else{
                $ret = array('result'=> WPVIVID_PRO_SUCCESS);
            }
        }
        fclose($fh);
        return $ret;
    }
    public function _upload_loop($session_id,&$offset,$data,$dropbox)
    {
        $result['result']=WPVIVID_PRO_SUCCESS;
        for($i =0;$i <WPVIVID_PRO_REMOTE_CONNECT_RETRY_TIMES; $i ++)
        {
            $result = $dropbox -> upload_session_append_v2($session_id,$offset,$data);
            if(isset($result['error_summary']))
            {
                if(strstr($result['error_summary'],'incorrect_offset'))
                {
                    $offset=$result['error']['correct_offset'];
                    $result = array('result' => WPVIVID_PRO_FAILED,'error' => 'Uploading '.$this -> current_file_name.' to Dropbox server failed. '.$result['error_summary']);
                    //return $result;
                }
                else
                {
                    $result = array('result' => WPVIVID_PRO_FAILED,'error' => 'Uploading '.$this -> current_file_name.' to Dropbox server failed. '.$result['error_summary']);
                }
            }
            else
            {
                return array('result' => WPVIVID_PRO_SUCCESS);
            }
        }
        return $result;
    }

    public function download($file, $local_path, $callback = '')
    {
        try {
            global $wpvivid_plugin;
            $wpvivid_plugin->wpvivid_download_log->WriteLog('Remote type: Dropbox.','notice');
            $this->current_file_name = $file['file_name'];
            $this->current_file_size = $file['size'];
            $options = $this->options;
            $dropbox = new Dropbox_Base($options);
            $ret=$dropbox->check_token();
            if($ret['result']=='failed')
            {
                return $ret;
            }
            if(isset($file['remote_path']))
            {
                $path = '/'.$options['path'].'/'.$file['remote_path'].'/'.basename($file['file_name']);
            }
            else
            {
                $path = '/'.$options['path'].'/'.basename($file['file_name']);
            }
            $file_path = trailingslashit($local_path) . $this->current_file_name;
            $start_offset = file_exists($file_path) ? filesize($file_path) : 0;
            $wpvivid_plugin->wpvivid_download_log->WriteLog('Create local file.','notice');
            $fh = fopen($file_path, 'a');
            $wpvivid_plugin->wpvivid_download_log->WriteLog('Downloading file ' . $file['file_name'] . ', Size: ' . $file['size'] ,'notice');
            while ($start_offset < $this->current_file_size) {
                $last_byte = min($start_offset + $this->download_chunk_size - 1, $this->current_file_size - 1);
                $headers = array("Range: bytes=$start_offset-$last_byte");
                $response = $dropbox->download($path, $headers);
                if (isset($response['error_summary'])) {
                    return array('result' => WPVIVID_PRO_FAILED, 'error' => 'Downloading ' . $path. ' failed.' . $response['error_summary']);
                }
                if (!fwrite($fh, $response)) {
                    return array('result' => WPVIVID_PRO_FAILED, 'error' => 'Downloading ' . $path . ' failed.');
                }
                clearstatcache();
                $state = stat($file_path);
                $start_offset = $state['size'];

                if ((time() - $this->last_time) > 3) {
                    if (is_callable($callback)) {
                        call_user_func_array($callback, array($start_offset, $this->current_file_name,
                            $this->current_file_size, $this->last_time, $this->last_size));
                    }
                    $this->last_size = $start_offset;
                    $this->last_time = time();
                }
            }
            @fclose($fh);

            if(filesize($file_path) === $file['size']){
                if($wpvivid_plugin->wpvivid_check_zip_valid()) {
                    $res = TRUE;
                }
                else{
                    $res = FALSE;
                }
            }
            else{
                $res = FALSE;
            }

            if ($res !== TRUE) {
                @unlink($file_path);
                return array('result' => WPVIVID_PRO_FAILED, 'error' => 'Downloading ' . $file['file_name'] . ' failed. ' . $file['file_name'] . ' might be deleted or network doesn\'t work properly. Please verify the file and confirm the network connection and try again later.');
            }
            return array('result' => WPVIVID_PRO_SUCCESS);
        }
        catch (Exception $error){
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            return array('result'=>WPVIVID_PRO_FAILED, 'error'=>$message);
        }
    }

    public function cleanup($files)
    {
        $options = $this -> options;
        $dropbox = new Dropbox_Base($options);
        $ret=$dropbox->check_token();
        if($ret['result']=='failed')
        {
            return $ret;
        }
        $path = '/'.$options['path'].'/';
        foreach ($files as $file)
        {
            $file_path=$path;
            if(is_array($file))
            {
                if(isset($file['remote_path']))
                {
                    $file_path=$path.$file['remote_path'].'/';
                }
                $file_name=$file['file_name'];
            }
            else
            {
                $file_name=$file;
            }
            $dropbox -> delete($file_path.$file_name);
        }
        return array('result'=>WPVIVID_PRO_SUCCESS);
    }

    public function handle_auth_actions()
    {
        if(isset($_GET['action']))
        {
            if($_GET['action'] === 'wpvivid_pro_dropbox_auth')
            {
                try {
                    $auth_id = uniqid('wpvivid-auth-');
                    $remote_options['auth_id']=$auth_id;
                    update_option('wpvivid_tmp_remote_options',$remote_options);
                    $state = apply_filters('wpvivid_get_admin_url', '') . 'admin.php?page='.sprintf('%s-remote', apply_filters('wpvivid_white_label_slug', 'wpvivid')).'&action=wpvivid_pro_dropbox_finish_auth&sub_page=cloud_storage_dropbox&auth_id='.$auth_id;
                    $url = Dropbox_Base::getUrl($this->redirect_url, $state);
                    header('Location: ' . filter_var($url, FILTER_SANITIZE_URL));
                }
                catch (Exception $e){
                    _e('<div class="notice notice-error"><p>'.$e->getMessage().'</p></div>');
                }
            }
            else if($_GET['action'] === 'wpvivid_pro_dropbox_finish_auth')
            {
                try {
                    $remoteslist = WPvivid_Setting::get_all_remote_options();
                    foreach ($remoteslist as $key => $value)
                    {
                        if (isset($value['auth_id']) && isset($_GET['auth_id']) && $value['auth_id'] == $_GET['auth_id'])
                        {
                            _e('<div class="notice notice-success is-dismissible"><p>You have authenticated the Dropbox account as your remote storage.</p></div>');
                            return;
                        }
                    }

                    $tmp_options=get_option('wpvivid_tmp_remote_options',false);
                    if($tmp_options===false)
                    {
                        return;
                    }
                    else
                    {
                        if($tmp_options['auth_id']===$_GET['auth_id'])
                        {
                            if(empty($_POST['code']))
                            {
                                if(empty($tmp_options['access_token']))
                                {
                                    header('Location: ' . apply_filters('wpvivid_get_admin_url', '') . 'admin.php?page=' . sprintf('%s-remote', apply_filters('wpvivid_white_label_slug', 'wpvivid')) . '&action=wpvivid_pro_dropbox_drive&result=error&resp_msg=' . 'Get Dropbox token failed.');

                                    return;
                                }
                            }
                            else
                            {
                                $tmp_options['type'] = WPVIVID_REMOTE_DROPBOX;
                                $tmp_options['access_token']= $_POST['code'];
                                $tmp_options['expires_in'] = $_POST['expires_in'];
                                $tmp_options['refresh_token'] = $_POST['refresh_token'];
                                update_option('wpvivid_tmp_remote_options',$tmp_options);
                            }
                            $this->add_remote=true;
                        }
                        else
                        {
                            return;
                        }
                    }
                }
                catch (Exception $e){
                    _e('<div class="notice notice-error"><p>'.$e->getMessage().'</p></div>');
                }
            }
            else if($_GET['action']=='wpvivid_pro_dropbox_drive')
            {
                try {
                    if (isset($_GET['result'])) {
                        if ($_GET['result'] == 'success') {
                            //add_action('show_notice', array($this, 'wpvivid_show_notice_add_dropbox_success'));
                            _e('<div class="notice notice-success is-dismissible"><p>You have authenticated the Dropbox account as your remote storage.</p></div>');
                        } else if ($_GET['result'] == 'error') {
                            //add_action('show_notice', array($this, 'wpvivid_show_notice_add_dropbox_error'));
                            global $wpvivid_plugin;
                            $wpvivid_plugin->wpvivid_handle_remote_storage_error($_GET['resp_msg'], 'Add Dropbox Remote');
                            _e('<div class="notice notice-error"><p>'.$_GET['resp_msg'].'</p></div>');
                        }
                    }
                }
                catch (Exception $e){
                    _e('<div class="notice notice-error"><p>'.$e->getMessage().'</p></div>');
                }
            }
        }
    }
    public function wpvivid_show_notice_add_dropbox_success(){
        _e('<div class="notice notice-success is-dismissible"><p>You have authenticated the Dropbox account as your remote storage.</p></div>');
    }
    public function wpvivid_show_notice_add_dropbox_error(){
        global $wpvivid_plugin;
        $wpvivid_plugin->wpvivid_handle_remote_storage_error($_GET['resp_msg'], 'Add Dropbox Remote');
        _e('<div class="notice notice-error"><p>'.$_GET['resp_msg'].'</p></div>');
    }

    public function wpvivid_add_storage_page_dropbox(){
        global $wpvivid_backup_pro;
        if($this->add_remote)
        {
            ?>
            <div id="storage_account_dropbox" class="storage-account-page">
                <div style="color:#8bc34a; padding: 0 10px 10px 0;">
                    <strong>Authentication is done, please continue to enter the storge information, then click 'Add Now' button to save it.</strong>
                </div>
                <div style="padding: 0 10px 10px 0;">
                    <strong>Enter Your Dropbox Information</strong>
                </div>
                <table class="wp-list-table widefat plugins" style="width:100%;">
                    <tbody>
                    <tr>
                        <td class="plugin-title column-primary">
                            <div class="wpvivid-storage-form">
                                <input type="text" class="regular-text" autocomplete="off" option="dropbox" name="name" placeholder="Enter a unique alias: e.g. Dropbox-001" onkeyup="value=value.replace(/[^a-zA-Z0-9\-_]/g,'')" />
                            </div>
                        </td>
                        <td class="column-description desc">
                            <div class="wpvivid-storage-form-desc">
                                <i>A name to help you identify the storage if you have multiple remote storage connected.</i>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="plugin-title column-primary">
                            <div class="wpvivid-storage-form">
                                <input type="text" class="regular-text" autocomplete="off" value="<?php echo sprintf(__('apps/%s backup restore', 'wpvivid'), apply_filters('wpvivid_white_label_display', 'Wpvivid')); ?>" readonly="readonly" />
                            </div>
                        </td>
                        <td class="column-description desc">
                            <div class="wpvivid-storage-form-desc">
                                <i><?php echo sprintf(__('A root directory in your Dropbox for holding all %s directories.', 'wpvivid'), apply_filters('wpvivid_white_label_display', 'WPvivid backup')); ?></i>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="plugin-title column-primary">
                            <div class="wpvivid-storage-form">
                                <input type="text" class="regular-text" autocomplete="off" option="dropbox" name="path" placeholder="Dropbox Folder" value="<?php esc_attr_e($wpvivid_backup_pro->func->swtich_domain_to_folder_name(home_url())); ?>" onkeyup="value=value.replace(/[^a-zA-Z0-9\-_/]/g,'')" />
                            </div>
                        </td>
                        <td class="column-description desc">
                            <div class="wpvivid-storage-form-desc">
                                <i>Customize the directory where you want to store backups. By default it takes your current website domain or url.</i>
                            </div>
                        </td>
                    </tr>

                    <!--<tr>
                        <td class="plugin-title column-primary">
                            <div class="wpvivid-storage-form">
                                <input type="text" class="regular-text wpvivid-remote-backup-retain" autocomplete="off" option="dropbox" name="backup_retain" value="30" />
                            </div>
                        </td>
                        <td class="column-description desc">
                            <div class="wpvivid-storage-form-desc">
                                <i>Total number of non-database only and non-incremental backup copies to be retained in this storage.</i>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="plugin-title column-primary">
                            <div class="wpvivid-storage-form">
                                <input type="text" class="regular-text wpvivid-remote-backup-db-retain" autocomplete="off" option="dropbox" name="backup_db_retain" value="30" />
                            </div>
                        </td>
                        <td class="column-description desc">
                            <div class="wpvivid-storage-form-desc">
                                <i>Total number of database backup copies to be retained in this storage.</i>
                            </div>
                        </td>
                    </tr>-->
                    <?php do_action('wpvivid_remote_storage_backup_retention', 'dropbox', 'add'); ?>

                    <tr>
                        <td class="plugin-title column-primary">
                            <div class="wpvivid-storage-select">
                                <label>
                                    <input type="checkbox" option="dropbox" name="default" checked />Set as the default remote storage.
                                </label>
                            </div>
                        </td>
                        <td class="column-description desc">
                            <div class="wpvivid-storage-form-desc">
                                <i>Once checked, all this sites backups sent to a remote storage destination will be uploaded to this storage by default.</i>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="plugin-title column-primary">
                            <div class="wpvivid-storage-form">
                                <input id="wpvivid_dropbox_auth" class="button-primary" type="submit" value="Add Now">
                            </div>
                        </td>
                        <td class="column-description desc">
                            <div class="wpvivid-storage-form-desc">
                                <i>Click the button to add the storage.</i>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <script>
                function wpvivid_check_dropbox_storage_alias(storage_alias)
                {
                    var bfind=true;
                    jQuery('#wpvivid_remote_storage_list tr').each(function (i)
                    {
                        jQuery(this).children('td').each(function (j)
                        {
                            if (j == 1)
                            {
                                if (jQuery(this).text() == storage_alias)
                                {
                                    bfind=false;
                                }
                            }
                        });
                    });

                    return bfind;
                }

                jQuery('#wpvivid_dropbox_auth').click(function()
                {
                    wpvivid_dropbox_auth();
                });

                function wpvivid_dropbox_auth()
                {
                    wpvivid_settings_changed = false;
                    var name='';
                    var path = '';
                    var backup_retain='';
                    var backup_db_retain='';
                    var backup_incremental_retain='';
                    var backup_rollback_retain='';
                    var bdefault = '0';
                    jQuery("input:checkbox[option=dropbox]").each(function(){
                        var key = jQuery(this).prop('name');
                        if(jQuery(this).prop('checked')) {
                            bdefault = '1';
                        }
                        else {
                            bdefault = '0';
                        }
                    });
                    jQuery('input:text[option=dropbox]').each(function()
                    {
                        var type = jQuery(this).prop('name');
                        if(type == 'name'){
                            name = jQuery(this).val();
                        }
                        if(type == 'path'){
                            path = jQuery(this).val();
                        }
                        if(type==='backup_retain')
                        {
                            backup_retain = jQuery(this).val();
                        }
                        if(type==='backup_db_retain')
                        {
                            backup_db_retain = jQuery(this).val();
                        }
                        if(type==='backup_incremental_retain')
                        {
                            backup_incremental_retain = jQuery(this).val();
                        }
                        if(type==='backup_rollback_retain')
                        {
                            backup_rollback_retain = jQuery(this).val();
                        }
                    });
                    if(name == '')
                    {
                        alert('Warning: An alias for remote storage is required.');
                    }
                    else if(wpvivid_check_dropbox_storage_alias(name) === false)
                    {
                        alert("Warning: The alias already exists in storage list.");
                    }
                    else if(path == '')
                    {
                        alert('The backup folder name cannot be empty.');
                    }
                    else if(path == '/')
                    {
                        alert('The backup folder name cannot be \'/\'.');
                    }
                    else if(backup_retain == '')
                    {
                        alert('Warning: You have not set the backup retention policy for this storage. Please set the policy or uncheck the option.');
                    }
                    else if(backup_db_retain == '')
                    {
                        alert('Warning: You have not set the backup retention policy for this storage. Please set the policy or uncheck the option.');
                    }
                    else if(backup_incremental_retain == ''){
                        alert('Warning: You have not set the backup retention policy for this storage. Please set the policy or uncheck the option.');
                    }
                    else if(backup_rollback_retain == ''){
                        alert('Warning: You have not set the backup retention policy for this storage. Please set the policy or uncheck the option.');
                    }
                    else
                    {
                        var ajax_data;
                        var remote_from = wpvivid_ajax_data_transfer('dropbox');
                        ajax_data = {
                            'action': 'wpvivid_dropbox_add_remote',
                            'remote': remote_from
                        };
                        jQuery('#wpvivid_dropbox_auth').css({'pointer-events': 'none', 'opacity': '0.4'});
                        jQuery('#wpvivid_remote_notice').html('');
                        wpvivid_post_request_addon(ajax_data, function (data)
                        {
                            try
                            {
                                var jsonarray = jQuery.parseJSON(data);
                                if (jsonarray.result === 'success')
                                {
                                    jQuery('#wpvivid_dropbox_auth').css({'pointer-events': 'auto', 'opacity': '1'});
                                    jQuery('input:text[option=dropbox]').each(function(){
                                        jQuery(this).val('');
                                    });
                                    jQuery('input:password[option=dropbox]').each(function(){
                                        jQuery(this).val('');
                                    });
                                    location.href='<?php echo apply_filters('wpvivid_white_label_page_redirect', 'admin.php?page=wpvivid-remote', 'wpvivid-remote').'&action=wpvivid_pro_dropbox_drive&sub_page=cloud_storage_dropbox&result=success'; ?>';
                                }
                                else if (jsonarray.result === 'failed')
                                {
                                    jQuery('#wpvivid_remote_notice').html(jsonarray.notice);
                                    jQuery('input[option=add-remote]').css({'pointer-events': 'auto', 'opacity': '1'});
                                }
                            }
                            catch (err)
                            {
                                alert(err);
                                jQuery('input[option=add-remote]').css({'pointer-events': 'auto', 'opacity': '1'});
                            }

                        }, function (XMLHttpRequest, textStatus, errorThrown)
                        {
                            var error_message = wpvivid_output_ajaxerror('adding the remote storage', textStatus, errorThrown);
                            alert(error_message);
                            jQuery('#wpvivid_dropbox_auth').css({'pointer-events': 'auto', 'opacity': '1'});
                        });
                    }
                }
            </script>
            <?php
        }
        else
        {
            ?>
            <div id="storage_account_dropbox" class="storage-account-page">
                <div style="padding: 0 10px 10px 0;">
                    <strong>To add Dropbox, please get Dropbox authentication first. Once authenticated, you will be redirected to this page, then you can add storage information and save it</strong>
                </div>
                <table class="wp-list-table widefat plugins" style="width:100%;">
                    <tbody>
                    <tr>
                        <td class="plugin-title column-primary">
                            <div class="wpvivid-storage-form">
                                <input onclick="wpvivid_dropbox_auth();" class="button-primary" type="submit" value="Authenticate with Dropbox">
                            </div>
                        </td>
                        <td class="column-description desc">
                            <div class="wpvivid-storage-form-desc">
                                <i>Click to get Dropbox authentication.</i>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div style="padding: 10px 0 0 0;">
                    <span>Tip: Get a 404 or 403 error after authorization? Please read this <a href="https://docs.wpvivid.com/http-403-error-authorizing-cloud-storage.html">doc</a>.</span>
                </div>
            </div>
            <script>
                function wpvivid_dropbox_auth()
                {
                    location.href='<?php echo apply_filters('wpvivid_white_label_page_redirect', 'admin.php?page=wpvivid-remote', 'wpvivid-remote').'&action=wpvivid_pro_dropbox_auth'; ?>';
                }
            </script>
            <?php
        }
    }

    public function wpvivid_edit_storage_page_dropbox()
    {
        do_action('wpvivid_remote_storage_js');
        ?>
        <div id="remote_storage_edit_dropbox" class="postbox storage-account-block remote-storage-edit" style="display:none; margin-bottom: 0;">
            <div style="padding: 0 10px 10px 0;">
                <strong>Enter Your Dropbox Information</strong>
            </div>
            <table class="wp-list-table widefat plugins" style="width:100%;">
                <tbody>
                <tr>
                    <td class="plugin-title column-primary">
                        <div class="wpvivid-storage-form">
                            <input type="text" class="regular-text" autocomplete="off" option="edit-dropbox" name="name" placeholder="Enter a unique alias: e.g. Dropbox-001" onkeyup="value=value.replace(/[^a-zA-Z0-9\-_]/g,'')" />
                        </div>
                    </td>
                    <td class="column-description desc">
                        <div class="wpvivid-storage-form-desc">
                            <i>A name to help you identify the storage if you have multiple remote storage connected.</i>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="plugin-title column-primary">
                        <div class="wpvivid-storage-form">
                            <input type="text" class="regular-text" autocomplete="off" option="edit-dropbox" name="path" placeholder="Dropbox Folder" onkeyup="value=value.replace(/[^a-zA-Z0-9\-_/]/g,'')" />
                        </div>
                    </td>
                    <td class="column-description desc">
                        <div class="wpvivid-storage-form-desc">
                            <i>Customize the directory where you want to store backups. By default it takes your current website domain or url.</i>
                            <!--<i><span>Specify a name for the folder where you want to store backups. Dropbox Folder Path:</span><span option="dropbox" name="path">
                                <?php
                                $root_path=apply_filters('wpvivid_get_root_path', WPVIVID_REMOTE_DROPBOX);
                                _e($root_path.WPVIVID_DROPBOX_DEFAULT_FOLDER);
                                ?>
                            </span></i>-->
                        </div>
                    </td>
                </tr>

                <!--<tr>
                    <td class="plugin-title column-primary">
                        <div class="wpvivid-storage-form">
                            <input type="text" class="regular-text wpvivid-remote-backup-retain" autocomplete="off" option="edit-dropbox" name="backup_retain" value="30" />
                        </div>
                    </td>
                    <td class="column-description desc">
                        <div class="wpvivid-storage-form-desc">
                            <i>Total number of non-database only and non-incremental backup copies to be retained in this storage.</i>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="plugin-title column-primary">
                        <div class="wpvivid-storage-form">
                            <input type="text" class="regular-text wpvivid-remote-backup-db-retain" autocomplete="off" option="edit-dropbox" name="backup_db_retain" value="30" />
                        </div>
                    </td>
                    <td class="column-description desc">
                        <div class="wpvivid-storage-form-desc">
                            <i>Total number of database backup copies to be retained in this storage.</i>
                        </div>
                    </td>
                </tr>-->
                <?php do_action('wpvivid_remote_storage_backup_retention', 'dropbox', 'edit'); ?>

                <tr>
                    <td class="plugin-title column-primary">
                        <div class="wpvivid-storage-form">
                            <input onclick="wpvivid_dropbox_update_auth();" class="button-primary" type="submit" value="Save Changes">
                        </div>
                    </td>
                    <td class="column-description desc">
                        <div class="wpvivid-storage-form-desc">
                            <i>Click the button to save the changes.</i>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <script>
            function wpvivid_dropbox_update_auth()
            {
                var name='';
                var path='';
                var backup_retain='';
                var backup_db_retain='';
                var backup_incremental_retain='';
                var backup_rollback_retain='';
                jQuery('input:text[option=edit-dropbox]').each(function()
                {
                    var key = jQuery(this).prop('name');
                    if(key==='name')
                    {
                        name = jQuery(this).val();
                    }
                    if(key==='path')
                    {
                        path = jQuery(this).val();
                    }
                    if(key==='backup_retain')
                    {
                        backup_retain = jQuery(this).val();
                    }
                    if(key==='backup_db_retain')
                    {
                        backup_db_retain = jQuery(this).val();
                    }
                    if(key==='backup_incremental_retain')
                    {
                        backup_incremental_retain = jQuery(this).val();
                    }
                    if(key==='backup_rollback_retain')
                    {
                        backup_rollback_retain = jQuery(this).val();
                    }
                });
                if(name == ''){
                    alert('Warning: An alias for remote storage is required.');
                }
                else if(path == '')
                {
                    alert('The backup folder name cannot be empty.');
                }
                else if(path == '/')
                {
                    alert('The backup folder name cannot be \'/\'.');
                }
                else if(backup_retain == ''){
                    alert('Warning: You have not set the backup retention policy for this storage. Please set the policy or uncheck the option.');
                }
                else if(backup_db_retain == ''){
                    alert('Warning: You have not set the backup retention policy for this storage. Please set the policy or uncheck the option.');
                }
                else if(backup_incremental_retain == ''){
                    alert('Warning: You have not set the backup retention policy for this storage. Please set the policy or uncheck the option.');
                }
                else if(backup_rollback_retain == ''){
                    alert('Warning: You have not set the backup retention policy for this storage. Please set the policy or uncheck the option.');
                }
                else {
                    wpvivid_edit_remote_storage();
                }
            }
        </script>
        <?php
    }

    public function revoke($id){
        $upload_options = WPvivid_Setting::get_option('wpvivid_upload_setting');
        if(array_key_exists($id,$upload_options) && $upload_options[$id] == WPVIVID_REMOTE_DROPBOX){
            $dropbox = new Dropbox_Base($upload_options);
            $ret=$dropbox->check_token();
            if($ret['result']=='failed')
            {
                return $ret;
            }
            $dropbox -> revoke();
        }
    }

    public function wpvivid_get_out_of_date_dropbox($out_of_date_remote, $remote)
    {
        if($remote['type'] == WPVIVID_REMOTE_DROPBOX){
            $root_path=apply_filters('wpvivid_get_root_path', $remote['type']);
            $out_of_date_remote = $root_path.'/'.$remote['path'];
        }
        return $out_of_date_remote;
    }

    public function wpvivid_storage_provider_dropbox($storage_type)
    {
        if($storage_type == WPVIVID_REMOTE_DROPBOX){
            $storage_type = 'Dropbox';
        }
        return $storage_type;
    }

    public function wpvivid_get_root_path_dropbox($storage_type){
        if($storage_type == WPVIVID_REMOTE_DROPBOX){
            $storage_type = 'apps/Wpvivid backup restore';
        }
        return $storage_type;
    }

    public function scan_folder_backup($folder_type)
    {
        global $wpvivid_plugin;

        $options = $this -> options;
        $dropbox = new Dropbox_Base($options);
        $ret=$dropbox->check_token();
        if($ret['result']=='failed')
        {
            return $ret;
        }
        $ret['path']=array();
        if($folder_type === 'Common'){
            $path=$options['path'];

            $response=$this->_scan_folder_backup($path,$dropbox);

            if($response['result']==WPVIVID_PRO_SUCCESS)
            {
                $ret['remote']= $response['backup'];
                $ret['path']=$response['path'];
            }
            else
            {
                $ret['remote']=array();
            }
        }
        else if($folder_type === 'Migrate'){
            $response=$this->_scan_folder_backup('migrate',$dropbox);

            if($response['result']==WPVIVID_PRO_SUCCESS)
            {
                $ret['migrate']= $response['backup'];
            }
            else
            {
                $ret['migrate']=array();
            }
        }
        else if($folder_type === 'Rollback'){
            $remote_folder=$this->options['path'].'/rollback';
            $response=$this->_scan_folder_backup($remote_folder,$dropbox);

            if($response['result']==WPVIVID_PRO_SUCCESS)
            {
                $ret['rollback']= $response['backup'];
            }
            else
            {
                $ret['rollback']=array();
            }
        }
        $ret['result']=WPVIVID_PRO_SUCCESS;
        return $ret;
    }

    public function scan_child_folder_backup($sub_path)
    {
        global $wpvivid_plugin;

        $options = $this -> options;
        $dropbox = new Dropbox_Base($options);
        $ret=$dropbox->check_token();
        if($ret['result']=='failed')
        {
            return $ret;
        }
        $path=$options['path'];

        $response=$this->_scan_child_folder_backup($path,$sub_path,$dropbox);

        if($response['result']==WPVIVID_PRO_SUCCESS)
        {
            $ret['remote']= $response['backup'];
        }
        else
        {
            $ret['remote']=array();
        }
        $ret['result']=WPVIVID_PRO_SUCCESS;
        return $ret;
    }

    public function _scan_folder_backup($path,$dropbox)
    {
        $endpoint = "https://api.dropboxapi.com/2/files/list_folder";
        $headers = array(
            "Content-Type: application/json"
        );

        $data['path']= '/'.$path;

        $postdata = json_encode($data);

        $response = $dropbox -> postRequest($endpoint, $headers, $postdata);
        if (isset($response['error_summary']))
        {
            return array('result' => WPVIVID_PRO_FAILED, 'error' => $response['error_summary']);
        }
        else
        {
            $ret['result']=WPVIVID_PRO_SUCCESS;
            $ret['backup']=array();
            $ret['path']=array();
            $files=array();
            if(isset($response['entries']))
            {
                foreach ($response['entries'] as $file)
                {
                    if(isset($file['.tag'])&&$file['.tag']=="folder")
                    {
                        $ret['path'][]=$file['name'];
                        //$ret_child=$this->_scan_child_folder_backup($path,$file['name'],$dropbox);
                        //if($ret_child['result']==WPVIVID_PRO_SUCCESS)
                        //{
                        //    $files= array_merge($files,$ret_child['files']);
                        //}
                    }
                    else if(isset($file['.tag'])&&$file['.tag']=="file")
                    {
                        $file_data['file_name']=$file['name'];
                        $file_data['size']=$file['size'];
                        $files[]=$file_data;
                    }
                }

                while($response['has_more'] == true)
                {
                    $endpoint_continue = "https://api.dropboxapi.com/2/files/list_folder/continue";
                    $cursor = $response['cursor'];

                    $data_continue['cursor']= $cursor;
                    $postdata_continue = json_encode($data_continue);

                    $response = $dropbox -> postRequest($endpoint_continue, $headers, $postdata_continue);

                    if (isset($response['error_summary']))
                    {
                        break;
                    }
                    else
                    {
                        if(isset($response['entries']))
                        {
                            foreach ($response['entries'] as $file)
                            {
                                if(isset($file['.tag'])&&$file['.tag']=="folder")
                                {
                                    $ret['path'][]=$file['name'];
                                }
                                else if(isset($file['.tag'])&&$file['.tag']=="file")
                                {
                                    $file_data['file_name']=$file['name'];
                                    $file_data['size']=$file['size'];
                                    $files[]=$file_data;
                                }
                            }
                        }
                    }
                }

            }
            if(!empty($files))
            {
                global $wpvivid_backup_pro;
                $ret['backup']=$wpvivid_backup_pro->func->get_backup($files);
            }

            return $ret;
        }
    }

    public function _scan_child_folder_backup($path,$sub_path,$dropbox)
    {
        $endpoint = "https://api.dropboxapi.com/2/files/list_folder";
        $headers = array(
            "Content-Type: application/json"
        );

        $data['path']= '/'.$path.'/'.$sub_path;

        $postdata = json_encode($data);

        $response = $dropbox -> postRequest($endpoint, $headers, $postdata);
        if (isset($response['error_summary']))
        {
            return array('result' => WPVIVID_PRO_FAILED, 'error' => $response['error_summary']);
        }
        else
        {
            $ret['result']=WPVIVID_PRO_SUCCESS;
            $ret['backup']=array();
            $ret['files']=array();
            if(isset($response['entries']))
            {
                foreach ($response['entries'] as $file)
                {
                    if(isset($file['.tag'])&&$file['.tag']=="folder")
                    {
                        continue;
                    }
                    else if(isset($file['.tag'])&&$file['.tag']=="file")
                    {
                        $file_data['file_name']=$file['name'];
                        $file_data['size']=$file['size'];
                        $file_data['remote_path']=$sub_path;
                        $ret['files'][]=$file_data;
                    }
                }
            }

            if(!empty($ret['files']))
            {
                global $wpvivid_backup_pro;
                $ret['backup']=$wpvivid_backup_pro->func->get_backup($ret['files']);
            }

            return $ret;
        }
    }

    public function delete_old_backup($backup_count,$db_count)
    {
        $options = $this -> options;
        $dropbox = new Dropbox_Base($options);
        $ret=$dropbox->check_token();
        if($ret['result']=='failed')
        {
            return $ret;
        }
        $path=$options['path'];

        $response=$this->_scan_folder_backup($path,$dropbox);

        if(isset($response['backup']))
        {
            $backups=$response['backup'];
            $folders=$response['path'];

            global $wpvivid_backup_pro;
            $files = $wpvivid_backup_pro->func->get_old_backup_files($backups,$backup_count,$db_count);
            $folders_count=apply_filters('wpvivid_get_backup_folders_count',0);
            $folders=$wpvivid_backup_pro->func->get_old_backup_folders($folders,$folders_count);
            foreach ($folders as $folder)
            {
                $child_response=$this->_scan_child_folder_backup($path,$folder,$dropbox);
                if(isset($child_response['files']))
                {
                    $files=array_merge($files,$child_response['files']);
                }
            }
            if(!empty($files))
            {
                $this->cleanup($files);
            }
            if(!empty($folders))
            {
                $this->cleanup($folders);
            }
        }

        $path=$this->options['path'].'/rollback';
        $response=$this->_scan_folder_backup($path,$dropbox);

        if(isset($response['backup']))
        {
            $backups=$response['backup'];

            global $wpvivid_backup_pro;
            $files = $wpvivid_backup_pro->func->get_old_backup_files($backups,$backup_count,$db_count);

            if(!empty($files))
            {
                $this->cleanup($files);
            }
        }

        $ret['result']=WPVIVID_PRO_SUCCESS;
        return $ret;
    }

    public function check_old_backups($backup_count,$db_count,$folder_type='Common')
    {
        $options = $this -> options;
        $dropbox = new Dropbox_Base($options);
        $ret=$dropbox->check_token();
        if($ret['result']=='failed')
        {
            return false;
        }
        if($folder_type=== 'Common')
        {
            $path=$options['path'];
        }
        else if($folder_type === 'Rollback')
        {
            $path=$this->options['path'].'/rollback';
        }
        else
        {
            return false;
        }

        $response=$this->_scan_folder_backup($path,$dropbox);

        if(isset($response['backup']))
        {
            $backups=$response['backup'];

            global $wpvivid_backup_pro;
            $files = $wpvivid_backup_pro->func->get_old_backup_files($backups,$backup_count,$db_count);
            if(!empty($files))
            {
                return true;
            }
            else if(isset($response['path'])&&$folder_type=== 'Common')
            {
                $folders=$response['path'];
                $folders_count=apply_filters('wpvivid_get_backup_folders_count',0);
                $folders=$wpvivid_backup_pro->func->get_old_backup_folders($folders,$folders_count);
                if(!empty($folders))
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    public function finish_add_remote()
    {
        global $wpvivid_backup_pro,$wpvivid_plugin;
        $wpvivid_backup_pro->ajax_check_security('wpvivid-can-mange-remote');
        try {
            if (empty($_POST) || !isset($_POST['remote']) || !is_string($_POST['remote'])) {
                die();
            }

            $tmp_remote_options =get_option('wpvivid_tmp_remote_options',array());
            delete_option('wpvivid_tmp_remote_options');
            if(empty($tmp_remote_options)||$tmp_remote_options['type']!==WPVIVID_REMOTE_DROPBOX)
            {
                die();
            }

            $json = $_POST['remote'];
            $json = stripslashes($json);
            $remote_options = json_decode($json, true);
            if (is_null($remote_options)) {
                die();
            }

            $remote_options['created']=time();
            $remote_options=array_merge($remote_options,$tmp_remote_options);

            $ret = $wpvivid_plugin->remote_collection->add_remote($remote_options);

            if ($ret['result'] == 'success') {
                $html = '';
                $html = apply_filters('wpvivid_add_remote_storage_list', $html);
                $ret['html'] = $html;
                $pic = '';
                $pic = apply_filters('wpvivid_schedule_add_remote_pic', $pic);
                $ret['pic'] = $pic;
                $dir = '';
                $dir = apply_filters('wpvivid_get_remote_directory', $dir);
                $ret['dir'] = $dir;
                $schedule_local_remote = '';
                $schedule_local_remote = apply_filters('wpvivid_schedule_local_remote', $schedule_local_remote);
                $ret['local_remote'] = $schedule_local_remote;
                $remote_storage = '';
                $remote_storage = apply_filters('wpvivid_remote_storage', $remote_storage);
                $ret['remote_storage'] = $remote_storage;
                $remote_select_part = '';
                $remote_select_part = apply_filters('wpvivid_remote_storage_select_part', $remote_select_part);
                $ret['remote_select_part'] = $remote_select_part;
                $default = array();
                $remote_array = apply_filters('wpvivid_archieve_remote_array', $default);
                $ret['remote_array'] = $remote_array;
                $success_msg = __('You have successfully added a remote storage.', 'wpvivid-backuprestore');
                $ret['notice'] = apply_filters('wpvivid_add_remote_notice', true, $success_msg);
            }
            else{
                $ret['notice'] = apply_filters('wpvivid_add_remote_notice', false, $ret['error']);
            }

        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        echo json_encode($ret);
        die();
    }

    public function delete_old_backup_ex($type,$backup_count,$db_count)
    {
        $options = $this -> options;
        $dropbox = new Dropbox_Base($options);
        $ret=$dropbox->check_token();
        if($ret['result']=='failed')
        {
            return $ret;
        }

        if($type=='Rollback')
        {
            $path=$this->options['path'].'/rollback';
            $response=$this->_scan_folder_backup($path,$dropbox);

            if(isset($response['backup']))
            {
                $backups=$response['backup'];

                global $wpvivid_backup_pro;
                $files = $wpvivid_backup_pro->func->get_old_backup_files($backups,$backup_count,$db_count);

                if(!empty($files))
                {
                    $this->cleanup($files);
                }
            }
        }
        else if($type=='Incremental')
        {
            $path=$options['path'];

            $response=$this->_scan_folder_backup($path,$dropbox);

            if(isset($response['path']))
            {
                $folders=$response['path'];

                global $wpvivid_backup_pro;
                $files =array();
                $folders_count=$backup_count;
                $folders=$wpvivid_backup_pro->func->get_old_backup_folders($folders,$folders_count);
                foreach ($folders as $folder)
                {
                    $child_response=$this->_scan_child_folder_backup($path,$folder,$dropbox);
                    if(isset($child_response['files']))
                    {
                        $files=array_merge($files,$child_response['files']);
                    }
                }
                if(!empty($files))
                {
                    $this->cleanup($files);
                }

                if(!empty($folders))
                {
                    $this->cleanup($folders);
                }
            }
        }
        else
        {
            $path=$options['path'];

            $response=$this->_scan_folder_backup($path,$dropbox);

            if(isset($response['backup']))
            {
                $backups=$response['backup'];

                global $wpvivid_backup_pro;
                $files = $wpvivid_backup_pro->func->get_old_backup_files($backups,$backup_count,$db_count);

                if(!empty($files))
                {
                    $this->cleanup($files);
                }
            }
        }

        $ret['result']=WPVIVID_PRO_SUCCESS;
        return $ret;
    }

    public function check_old_backups_ex($type,$backup_count,$db_count)
    {
        $options = $this -> options;
        $dropbox = new Dropbox_Base($options);
        $ret=$dropbox->check_token();
        if($ret['result']=='failed')
        {
            return false;
        }

        if($type=='Rollback')
        {
            $path=$this->options['path'].'/rollback';
            $response=$this->_scan_folder_backup($path,$dropbox);

            if(isset($response['backup']))
            {
                $backups=$response['backup'];

                global $wpvivid_backup_pro;
                $files = $wpvivid_backup_pro->func->get_old_backup_files($backups,$backup_count,$db_count);

                if(!empty($files))
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }
        else if($type=='Incremental')
        {
            $path=$options['path'];

            $response=$this->_scan_folder_backup($path,$dropbox);

            if(isset($response['path']))
            {
                $folders=$response['path'];

                global $wpvivid_backup_pro;
                $files =array();
                $folders_count=$backup_count;
                $folders=$wpvivid_backup_pro->func->get_old_backup_folders($folders,$folders_count);
                if(!empty($folders))
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }
        else
        {
            $path=$options['path'];

            $response=$this->_scan_folder_backup($path,$dropbox);

            if(isset($response['backup']))
            {
                $backups=$response['backup'];

                global $wpvivid_backup_pro;
                $files = $wpvivid_backup_pro->func->get_old_backup_files($backups,$backup_count,$db_count);

                if(!empty($files))
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }
        return false;
    }
}