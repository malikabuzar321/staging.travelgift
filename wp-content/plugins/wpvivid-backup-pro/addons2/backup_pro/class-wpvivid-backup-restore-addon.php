<?php

/**
 * WPvivid addon: yes
 * Addon Name: wpvivid-backup-pro-all-in-one
 * Description: Pro
 * Version: 2.2.13
 * Need_init: yes
 * Interface Name: WPvivid_BackupList_addon
 */
if (!defined('WPVIVID_BACKUP_PRO_PLUGIN_DIR'))
{
    die;
}
if ( ! class_exists( 'WP_List_Table' ) )
{
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WPvivid_Backup_List extends WP_List_Table
{
    public $page_num;
    public $backup_list;
    public $soft_content;
    public $is_localhost;

    public function __construct( $args = array() )
    {
        parent::__construct(
            array(
                'plural' => 'backup',
                'screen' => 'backup'
            )
        );
        $this->soft_content=false;
        $this->is_localhost=true;
    }

    protected function get_table_classes()
    {
        return array( 'widefat striped wpvivid-backup-list' );
    }

    public function print_column_headers( $with_id = true )
    {
        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

        if (!empty($columns['cb'])) {
            static $cb_counter = 1;
            $columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __('Select All') . '</label>'
                . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox"/>';
            $cb_counter++;
        }

        foreach ( $columns as $column_key => $column_display_name )
        {
            $class = array( 'manage-column', "column-$column_key" );

            if ( in_array( $column_key, $hidden ) )
            {
                $class[] = 'hidden';
            }

            if ( $column_key === $primary )
            {
                $class[] = 'column-primary';
            }

            if ( $column_key === 'cb' )
            {
                $class[] = 'wpvivid-check-column';
            }

            $tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
            $scope = ( 'th' === $tag ) ? 'scope="col"' : '';
            $id    = $with_id ? "id='$column_key'" : '';

            if ( ! empty( $class ) )
            {
                $class = "class='" . join( ' ', $class ) . "'";
            }

            echo "<$tag $scope $id $class>$column_display_name</$tag>";
        }
    }

    public function get_columns()
    {
        $columns = array();
        $columns['cb'] = __( 'cb', 'wpvivid' );
        $columns['wpvivid_backup'] = __( 'Backup', 'wpvivid' );
        $columns['wpvivid_comment'] =__( 'Comment', 'wpvivid'  );
        $columns['wpvivid_size'] =__( 'Size', 'wpvivid'  );
        $columns['wpvivid_content'] = __( 'Content', 'wpvivid' );
        if($this->is_localhost)
        {
            if( apply_filters('wpvivid_current_user_can',true,'wpvivid-can-mange-download-localhost-backup'))
            {
                $columns['wpvivid_download'] = __( 'Download', 'wpvivid'  );
                $columns['wpvivid_restore'] = __( 'Restore', 'wpvivid'  );
            }
        }
        else
        {
            if( apply_filters('wpvivid_current_user_can',true,'wpvivid-can-mange-download-remote-backup'))
            {
                $columns['wpvivid_download'] = __( 'Download', 'wpvivid'  );
                $columns['wpvivid_restore'] = __( 'Restore', 'wpvivid'  );
            }
        }

        $columns['wpvivid_delete'] = __( 'Delete', 'wpvivid'  );
        return $columns;
    }

    public function column_cb( $backup )
    {
        $html='<input type="checkbox"/>';
        echo $html;
    }

    public function _column_wpvivid_backup( $backup )
    {
        $upload_title = '';
        if ($backup['type'] == 'Migration' || $backup['type'] == 'Upload')
        {
            if ($backup['type'] == 'Migration')
            {
                $upload_title = 'Received Backup: ';
            } else if ($backup['type'] == 'Upload')
            {
                $upload_title = 'Uploaded Backup: ';
            }
        }

        if ($backup['type'] === 'Incremental')
        {
            if($backup['content'] === 'Database Only')
            {
                $type_display = 'DB Incr.';
            }
            else
            {
                $type_display = 'File Incr.';
            }
        }
        else
        {
            $type_display = $backup['type'];
        }

        if (empty($backup['lock']))
        {
            $lock_class = 'dashicons-unlock';
        }
        else {
            if ($backup['lock'] == 0)
            {
                $lock_class = 'dashicons-unlock';
            } else {
                $lock_class = 'dashicons-lock';
            }
        }

        $backups_lock=WPvivid_Setting::get_option('wpvivid_remote_backups_lock');
        if(isset($backups_lock[$backup['key']]))
        {
            $lock_class = 'dashicons-lock';
        }

        //$offset=get_option('gmt_offset');
        //$localtime = $backup['create_time'] + $offset * 60 * 60;
        $localtime = $backup['create_time'];

        $offset=get_option('gmt_offset');
        $utc_time = $backup['create_time'] - $offset * 60 * 60;

        $log_name = basename($backup['log']);

        $html='<td class="tablelistcolumn">
                    <div style="float:left;padding:0 10px 10px 0;">
                        <div style="float: left; margin-right: 2px;"><strong>' . $upload_title . '</strong></div>
                        <div class="backuptime" style="float: left;" title="UTC:'.date('M-d-Y H:i', $utc_time).'">' . __(date('M-d-Y H:i', $localtime)) . '</div>
                        <div style="clear: both;"></div>
                        <div class="common-table">
                            <span class="dashicons '.$lock_class.' wpvivid-dashicons-blue wpvivid-lock" style="cursor:pointer;"></span>
                            <span style="margin:0 5px 0 0; opacity: 0.5;">|</span> <span>Type: </span><span>'.$type_display.'</span><span class="backuptype" style="display: none;">'.$backup['type'].'</span>
							<span style="margin:0 0 0 5px; opacity: 0.5;">|</span>
							<span class="dashicons dashicons-welcome-write-blog wpvivid-dashicons-blue"></span>
						    <span style="cursor:pointer; margin:0;" onclick="wpvivid_backup_open_log(\''.$log_name.'\');">Log</span>
                        </div>
                    </div>
                </td>';
        echo $html;
    }

    public function _column_wpvivid_content( $backup )
    {
        $html='<td class="tablelistcolumn">
                    <div class="wpvivid-backup-content" style="padding:14px 10px 10px 0; cursor: pointer;" type-string="'.$backup['content_detail'].'">
                        <span class="dashicons dashicons-visibility wpvivid-dashicons-green"></span><span>Details</span>
                    </div>
                </td>';
        echo $html;
    }

    public function _column_wpvivid_comment( $backup )
    {
        if(isset($backup['backup_prefix']) && !empty($backup['backup_prefix']))
        {
            $backup_prefix = $backup['backup_prefix'];
        }
        else{
            $backup_prefix = 'N/A';
        }
        $html='<td class="tablelistcolumn">
                    <div class="comment" style="padding:14px 10px 10px 0;">'.$backup_prefix.'</div>
                </td>';
        echo $html;
    }

    public function _column_wpvivid_size( $backup )
    {
        $size=0;
        foreach ($backup['backup']['files'] as $file)
        {
            $size+=$file['size'];
        }
        $size=size_format($size,2);
        $html='<td class="tablelistcolumn">
                    <div class="size" style="padding:14px 10px 10px 0;">'.$size.'</div>
                </td>';
        echo $html;
    }

    public function _column_wpvivid_download( $backup )
    {
        $html='<td class="tablelistcolumn" style="min-width:100px;">
                    <div class="wpvivid-download" style="float:left;padding:10px 10px 10px 0;">
                        <div style="cursor:pointer;" title="Prepare to download the backup">
                            <span class="dashicons dashicons-arrow-down-alt wpvivid-dashicons-blue"></span><span>Download</span>
                        </div>
                    </div>
                </td>';
        echo $html;
    }

    public function _column_wpvivid_restore( $backup )
    {
        $html='<td class="tablelistcolumn" style="min-width:100px;">
                    <div>
                      <div class="wpvivid-restore" style="cursor:pointer;float:left;padding:10px 0 10px 0;">
                            <span class="dashicons dashicons-update wpvivid-dashicons-green"></span><span>Restore</span>
                       </div>
                    </div>
                </td>';
        echo $html;
    }

    public function _column_wpvivid_delete( $backup )
    {
        $html='<td class="tablelistcolumn">
                    <div class="backuplist-delete-backup" style="cursor:pointer;padding:10px 0 10px 0;">
                        <span class="dashicons dashicons-trash wpvivid-dashicons-grey"></span>
                    </div>
                </td>';
        echo $html;
    }

    public function set_backup_list($backup_list,$page_num=1,$soft_content=false)
    {
        $this->backup_list=$backup_list;
        $this->page_num=$page_num;
        $this->soft_content=$soft_content;
    }

    public function get_pagenum()
    {
        if($this->page_num=='first')
        {
            $this->page_num=1;
        }
        else if($this->page_num=='last')
        {
            $this->page_num=$this->_pagination_args['total_pages'];
        }
        $pagenum = $this->page_num ? $this->page_num : 0;

        if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
        {
            $pagenum = $this->_pagination_args['total_pages'];
        }

        return max( 1, $pagenum );
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $total_items =sizeof($this->backup_list);

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => 10,
            )
        );
    }

    public function has_items()
    {
        return !empty($this->backup_list);
    }

    public function display_rows()
    {
        $this->_display_rows($this->backup_list,$this->soft_content);
    }

    public function get_backup_content($backup)
    {
        $ret['content_detail'] = 'Please download it to localhost for identification.';
        $ret['content'] = 'All';
        $has_db = false;
        $has_file = false;
        $type_list = array();
        $ismerge = false;
        //ismerge ( not all )
        if(isset($backup['backup']['files']))
        {
            foreach ($backup['backup']['files'] as $key => $value)
            {
                $file_name = $value['file_name'];
                if(WPvivid_backup_pro_function::is_wpvivid_db_backup($file_name))
                {
                    $has_db = true;
                    if(!in_array('Database', $type_list)) {
                        $type_list[] = 'Database';
                    }
                }
                else if(WPvivid_backup_pro_function::is_wpvivid_themes_backup($file_name))
                {
                    $has_file = true;
                    if(!in_array('Themes', $type_list)) {
                        $type_list[] = 'Themes';
                    }
                }
                else if(WPvivid_backup_pro_function::is_wpvivid_plugin_backup($file_name))
                {
                    $has_file = true;
                    if(!in_array('Plugins', $type_list)) {
                        $type_list[] = 'Plugins';
                    }
                }
                else if(WPvivid_backup_pro_function::is_wpvivid_uploads_backup($file_name))
                {
                    $has_file = true;
                    if(!in_array('wp-content/uploads', $type_list)) {
                        $type_list[] = 'wp-content/uploads';
                    }
                }
                else if(WPvivid_backup_pro_function::is_wpvivid_content_backup($file_name))
                {
                    $has_file = true;
                    if(!in_array('wp-content', $type_list)) {
                        $type_list[] = 'wp-content';
                    }
                }
                else if(WPvivid_backup_pro_function::is_wpvivid_core_backup($file_name))
                {
                    $has_file = true;
                    if(!in_array('Wordpress Core', $type_list)) {
                        $type_list[] = 'Wordpress Core';
                    }
                }
                else if(WPvivid_backup_pro_function::is_wpvivid_other_backup($file_name))
                {
                    $has_file = true;
                    if(!in_array('Additional Folder', $type_list)) {
                        $type_list[] = 'Additional Folder';
                    }
                }
                else if(WPvivid_backup_pro_function::is_wpvivid_additional_db_backup($file_name))
                {
                    $has_file = true;
                    if(!in_array('Additional Database', $type_list)) {
                        $type_list[] = 'Additional Database';
                    }
                }
                else if(WPvivid_backup_pro_function::is_wpvivid_all_backup($file_name))
                {
                    $ismerge = true;
                }
            }
        }
        //all
        if($ismerge)
        {
            $backup_id = $backup['key'];
            $backup = WPvivid_Backuplist::get_backup_by_id($backup_id);
            $backup_item = new WPvivid_Backup_Item($backup);
            $files=$backup_item->get_files(false);
            $files_info=array();
            foreach ($files as $file)
            {
                $files_info[$file]=$backup_item->get_file_info($file);
            }
            $info=array();
            foreach ($files_info as $file_name=>$file_info)
            {
                if(isset($file_info['has_child']))
                {
                    if(isset($file_info['child_file']))
                    {
                        foreach ($file_info['child_file'] as $child_file_name=>$child_file_info)
                        {
                            if(isset($child_file_info['file_type']))
                            {
                                $info['type'][] = $child_file_info['file_type'];
                            }
                        }
                    }
                }
                else {
                    if(isset($file_info['file_type']))
                    {
                        $info['type'][] = $file_info['file_type'];
                    }
                }
            }

            if(isset($info['type']))
            {
                foreach ($info['type'] as $backup_content)
                {
                    if ($backup_content === 'databases')
                    {
                        $has_db = true;
                        if(!in_array('Database', $type_list))
                        {
                            $type_list[] = 'Database';
                        }
                    }
                    if($backup_content === 'themes')
                    {
                        $has_file = true;
                        if(!in_array('Themes', $type_list))
                        {
                            $type_list[] = 'Themes';
                        }
                    }
                    if($backup_content === 'plugin')
                    {
                        $has_file = true;
                        if(!in_array('Plugins', $type_list))
                        {
                            $type_list[] = 'Plugins';
                        }
                    }
                    if($backup_content === 'upload')
                    {
                        $has_file = true;
                        if(!in_array('wp-content/uploads', $type_list))
                        {
                            $type_list[] = 'wp-content/uploads';
                        }
                    }
                    if($backup_content === 'wp-content')
                    {
                        $has_file = true;
                        if(!in_array('wp-content', $type_list))
                        {
                            $type_list[] = 'wp-content';
                        }
                    }
                    if($backup_content === 'wp-core')
                    {
                        $has_file = true;
                        if(!in_array('Wordpress Core', $type_list))
                        {
                            $type_list[] = 'Wordpress Core';
                        }
                    }
                    if($backup_content === 'custom')
                    {
                        $has_file = true;
                        if(!in_array('Additional Folder', $type_list))
                        {
                            $type_list[] = 'Additional Folder';
                        }
                    }
                    if($backup_content === 'additional_databases')
                    {
                        $has_file = true;
                        if(!in_array('Additional Database', $type_list))
                        {
                            $type_list[] = 'Additional Database';
                        }
                    }
                }
            }
        }

        if($has_db){
            $type_string = implode(",", $type_list);
            $ret['content_detail'] = $type_string;
            $ret['content'] = 'Database Only';
        }
        if($has_file){
            $type_string = implode(",", $type_list);
            $ret['content_detail'] = $type_string;
            $ret['content'] = 'WordPress Files Only';
        }
        if($has_db && $has_file){
            $type_string = implode(",", $type_list);
            $ret['content_detail'] = $type_string;
            $ret['content'] = 'Database & WordPress Files';
        }
        if(!$has_db && !$has_file)
        {
            if(isset($files) && !empty($files))
            {
                foreach ($files as $file)
                {
                    if (WPvivid_backup_pro_function::is_wpvivid_backup($file))
                    {
                        if (WPvivid_backup_pro_function::is_wpvivid_db_backup($file))
                        {
                            $has_db = true;
                            $type_list[] = 'Database';
                        } else {
                            $has_file = true;
                        }
                    }
                }
            }
            if($has_db && !$has_file){
                $type_string = implode(",", $type_list);
                $ret['content_detail'] = $type_string;
                $ret['content'] = 'Database Only';
            }
            else {
                $ret['content_detail'] = 'Please download it to localhost for identification.';
                $ret['content'] = 'All';
            }
        }
        return $ret;
    }

    private function _display_rows($backup_list,$soft_content=false)
    {
        $page=$this->get_pagenum();

        $page_backup_list=array();
        $temp_page_backup_list=array();

        foreach ( $backup_list as $key=>$backup)
        {
            $backup['key']=$key;
            $content_info = $this->get_backup_content($backup);
            $backup['content']=$content_info['content'];
            $backup['content_detail']=$content_info['content_detail'];
            $page_backup_list[$key]=$backup;
        }

        if($soft_content)
        {
            usort($page_backup_list, function ($a, $b)
            {
                if($a['content']!=$b['content'])
                {
                    if($a['content']=='All'||$a['content']=='Database & WordPress Files')
                    {
                        return -1;
                    }
                    else if($a['content']=='WordPress Files Only'&&$b['content']=='Database Only')
                    {
                        return -1;
                    }
                    else if($a['content']=='WordPress Files Only'&&$b['content']!='Database Only')
                    {
                        return 1;
                    }
                    else if($a['content']=='Database Only')
                    {
                        return 1;
                    }
                    else
                    {
                        if ($a['create_time'] == $b['create_time'])
                        {
                            return 0;
                        }

                        if($a['create_time'] > $b['create_time'])
                        {
                            return -1;
                        }
                        else
                        {
                            return 1;
                        }
                    }
                }
                else
                {
                    if ($a['create_time'] == $b['create_time'])
                    {
                        return 0;
                    }

                    if($a['create_time'] > $b['create_time'])
                    {
                        return -1;
                    }
                    else
                    {
                        return 1;
                    }
                }
            });
        }
        else
        {
            usort($page_backup_list, function ($a, $b)
            {
                if ($a['create_time'] == $b['create_time'])
                {
                    return 0;
                }

                if($a['create_time'] > $b['create_time'])
                {
                    return -1;
                }
                else
                {
                    return 1;
                }
            });
        }


        $count=0;
        while ( $count<$page )
        {
            $temp_page_backup_list = array_splice( $page_backup_list, 0, 10);
            $count++;
        }

        foreach ( $temp_page_backup_list as $key=>$backup)
        {
            //$backup['key']=$key;
            $this->single_row($backup);
        }
    }

    public function single_row($backup)
    {
        $row_style = 'display: table-row;';
        $class='';
        if ($backup['type'] == 'Migration' || $backup['type'] == 'Upload')
        {
            $class .= 'wpvivid-upload-tr';
        }
        ?>
        <tr style="<?php echo $row_style?>" class='wpvivid-backup-row <?php echo $class?>' id="<?php echo $backup['key'];?>">
            <?php $this->single_row_columns( $backup ); ?>
        </tr>
        <?php
    }

    protected function pagination( $which )
    {
        if ( empty( $this->_pagination_args ) )
        {
            return;
        }

        $total_items     = $this->_pagination_args['total_items'];
        $total_pages     = $this->_pagination_args['total_pages'];
        $infinite_scroll = false;
        if ( isset( $this->_pagination_args['infinite_scroll'] ) )
        {
            $infinite_scroll = $this->_pagination_args['infinite_scroll'];
        }

        if ( 'top' === $which && $total_pages > 1 )
        {
            $this->screen->render_screen_reader_content( 'heading_pagination' );
        }

        $output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

        $current              = $this->get_pagenum();

        $page_links = array();

        $total_pages_before = '<span class="paging-input">';
        $total_pages_after  = '</span></span>';

        $disable_first = $disable_last = $disable_prev = $disable_next = false;

        if ( $current == 1 ) {
            $disable_first = true;
            $disable_prev  = true;
        }
        if ( $current == 2 ) {
            $disable_first = true;
        }
        if ( $current == $total_pages ) {
            $disable_last = true;
            $disable_next = true;
        }
        if ( $current == $total_pages - 1 ) {
            $disable_last = true;
        }

        if ( $disable_first ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='first-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'First page' ),
                '&laquo;'
            );
        }

        if ( $disable_prev ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='prev-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Previous page' ),
                '&lsaquo;'
            );
        }

        if ( 'bottom' === $which ) {
            $html_current_page  = $current;
            $total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
        } else {
            $html_current_page = sprintf(
                "%s<input class='current-page' id='current-page-selector-backuplist' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label for="current-page-selector-backuplist" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
                $current,
                strlen( $total_pages )
            );
        }
        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[]     = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

        if ( $disable_next ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='next-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Next page' ),
                '&rsaquo;'
            );
        }

        if ( $disable_last ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='last-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'Last page' ),
                '&raquo;'
            );
        }

        $pagination_links_class = 'pagination-links';
        if ( ! empty( $infinite_scroll ) ) {
            $pagination_links_class .= ' hide-if-js';
        }
        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages ) {
            $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
            $page_class = ' no-pages';
        }
        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

        echo $this->_pagination;
    }

    protected function display_tablenav( $which ) {
        $css_type = '';
        if ( 'top' === $which ) {
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
            $css_type = 'margin: 0 0 10px 0';
        }
        else if( 'bottom' === $which ) {
            $css_type = 'margin: 10px 0 0 0';
        }

        $total_pages     = $this->_pagination_args['total_pages'];
        if ( $total_pages >1)
        {
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php esc_attr_e($css_type); ?>">
                <?php
                $this->extra_tablenav( $which );
                $this->pagination( $which );
                ?>

                <br class="clear" />
            </div>
            <?php
        }
    }
}

class WPvivid_Content_List extends WP_List_Table
{
    public $page_num;
    public $content_list;

    public function __construct( $args = array() )
    {
        parent::__construct(
            array(
                'plural' => 'contents',
                'screen' => 'contents'
            )
        );
    }

    protected function get_table_classes()
    {
        return array( 'widefat striped' );
    }

    public function print_column_headers( $with_id = true )
    {
        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

        if (!empty($columns['cb'])) {
            static $cb_counter = 1;
            $columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __('Select All') . '</label>'
                . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox"/>';
            $cb_counter++;
        }

        foreach ( $columns as $column_key => $column_display_name )
        {
            $class = array( 'manage-column', "column-$column_key" );

            if ( in_array( $column_key, $hidden ) )
            {
                $class[] = 'hidden';
            }

            if ( $column_key === $primary )
            {
                $class[] = 'column-primary';
            }

            if ( $column_key === 'cb' )
            {
                $class[] = 'check-column';
            }

            $tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
            $scope = ( 'th' === $tag ) ? 'scope="col"' : '';
            $id    = $with_id ? "id='$column_key'" : '';

            if ( ! empty( $class ) )
            {
                $class = "class='" . join( ' ', $class ) . "'";
            }

            echo "<$tag $scope $id $class>$column_display_name</$tag>";
        }
    }

    public function get_columns()
    {
        $columns = array();
        $columns['wpvivid_file'] = __( 'Backup Content', 'wpvivid' );
        return $columns;
    }

    public function _column_wpvivid_file( $content )
    {
        $html='<td class="tablelistcolumn">
                    <div>
                        '.$content.'
                    </div>
              </td>';
        echo $html;
    }

    public function set_files_list($content_list,$page_num=1)
    {
        $this->content_list=$content_list;
        $this->page_num=$page_num;
    }

    public function get_pagenum()
    {
        if($this->page_num=='first')
        {
            $this->page_num=1;
        }
        else if($this->page_num=='last')
        {
            $this->page_num=$this->_pagination_args['total_pages'];
        }
        $pagenum = $this->page_num ? $this->page_num : 0;

        if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
        {
            $pagenum = $this->_pagination_args['total_pages'];
        }

        return max( 1, $pagenum );
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $total_items =sizeof($this->content_list);

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => 10,
            )
        );
    }

    public function has_items()
    {
        return !empty($this->content_list);
    }

    public function display_rows()
    {
        $this->_display_rows($this->content_list);
    }

    private function _display_rows($content_list)
    {
        $page=$this->get_pagenum();

        $page_content_list=array();
        $count=0;
        while ( $count<$page )
        {
            $page_content_list = array_splice( $content_list, 0, 10);
            $count++;
        }
        foreach ( $page_content_list as $key=>$content)
        {
            $this->single_row($content);
        }
    }

    public function single_row($content)
    {
        ?>
        <tr>
            <?php $this->single_row_columns( $content ); ?>
        </tr>
        <?php
    }

    protected function pagination( $which )
    {
        if ( empty( $this->_pagination_args ) )
        {
            return;
        }

        $total_items     = $this->_pagination_args['total_items'];
        $total_pages     = $this->_pagination_args['total_pages'];
        $infinite_scroll = false;
        if ( isset( $this->_pagination_args['infinite_scroll'] ) )
        {
            $infinite_scroll = $this->_pagination_args['infinite_scroll'];
        }

        if ( 'top' === $which && $total_pages > 1 )
        {
            $this->screen->render_screen_reader_content( 'heading_pagination' );
        }

        $output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

        $current              = $this->get_pagenum();

        $page_links = array();

        $total_pages_before = '<span class="paging-input">';
        $total_pages_after  = '</span></span>';

        $disable_first = $disable_last = $disable_prev = $disable_next = false;

        if ( $current == 1 ) {
            $disable_first = true;
            $disable_prev  = true;
        }
        if ( $current == 2 ) {
            $disable_first = true;
        }
        if ( $current == $total_pages ) {
            $disable_last = true;
            $disable_next = true;
        }
        if ( $current == $total_pages - 1 ) {
            $disable_last = true;
        }

        if ( $disable_first ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='first-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'First page' ),
                '&laquo;'
            );
        }

        if ( $disable_prev ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='prev-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Previous page' ),
                '&lsaquo;'
            );
        }

        if ( 'bottom' === $which ) {
            $html_current_page  = $current;
            $total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
        } else {
            $html_current_page = sprintf(
                "%s<input class='current-page' id='current-page-selector-filelist' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label for="current-page-selector-filelist" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
                $current,
                strlen( $total_pages )
            );
        }
        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[]     = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

        if ( $disable_next ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='next-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Next page' ),
                '&rsaquo;'
            );
        }

        if ( $disable_last ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='last-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'Last page' ),
                '&raquo;'
            );
        }

        $pagination_links_class = 'pagination-links';
        if ( ! empty( $infinite_scroll ) ) {
            $pagination_links_class .= ' hide-if-js';
        }
        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages ) {
            $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
            $page_class = ' no-pages';
        }
        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

        echo $this->_pagination;
    }

    protected function display_tablenav( $which ) {
        $css_type = '';
        if ( 'top' === $which ) {
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
            $css_type = 'margin: 0 0 10px 0';
        }
        else if( 'bottom' === $which ) {
            $css_type = 'margin: 10px 0 0 0';
        }

        $total_pages     = $this->_pagination_args['total_pages'];
        if ( $total_pages >1)
        {
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php esc_attr_e($css_type); ?>">
                <?php
                $this->extra_tablenav( $which );
                $this->pagination( $which );
                ?>

                <br class="clear" />
            </div>
            <?php
        }
    }

    public function display()
    {
        $singular = $this->_args['singular'];

        $this->display_tablenav( 'top' );

        $this->screen->render_screen_reader_content( 'heading_list' );
        ?>
        <table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
            <thead>
            <tr>
                <?php $this->print_column_headers(); ?>
            </tr>
            </thead>

            <tbody id="the-list"
                <?php
                if ( $singular ) {
                    echo " data-wp-lists='list:$singular'";
                }
                ?>
            >
            <?php $this->display_rows_or_placeholder(); ?>
            </tbody>

        </table>
        <?php
        $this->display_tablenav( 'bottom' );
    }
}

class WPvivid_Files_List_Ex extends WP_List_Table
{
    public $page_num;
    public $file_list;
    public $backup_id;

    public function __construct( $args = array() )
    {
        parent::__construct(
            array(
                'plural' => 'files',
                'screen' => 'files'
            )
        );
    }

    protected function get_table_classes()
    {
        return array( 'widefat striped' );
    }

    public function print_column_headers( $with_id = true )
    {
        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

        if (!empty($columns['cb'])) {
            static $cb_counter = 1;
            $columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __('Select All') . '</label>'
                . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox"/>';
            $cb_counter++;
        }

        foreach ( $columns as $column_key => $column_display_name )
        {
            $class = array( 'manage-column', "column-$column_key" );

            if ( in_array( $column_key, $hidden ) )
            {
                $class[] = 'hidden';
            }

            if ( $column_key === $primary )
            {
                $class[] = 'column-primary';
            }

            if ( $column_key === 'cb' )
            {
                $class[] = 'check-column';
            }

            $tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
            $scope = ( 'th' === $tag ) ? 'scope="col"' : '';
            $id    = $with_id ? "id='$column_key'" : '';

            if ( ! empty( $class ) )
            {
                $class = "class='" . join( ' ', $class ) . "'";
            }

            echo "<$tag $scope $id $class>$column_display_name</$tag>";
        }
    }

    public function get_columns()
    {
        $columns = array();
        $columns['wpvivid_file'] = __( 'File', 'wpvivid' );
        return $columns;
    }

    public function _column_wpvivid_file( $file )
    {
        $html='<td class="tablelistcolumn">
                    <div style="padding:0 0 10px 0;">
                        <span>'. $file['key'].'</span>
                    </div>
                    <div class="wpvivid-download-status" style="padding:0;">';
        if($file['status']=='completed')
        {
            $html.='<span>'.__('File Size: ', 'wpvivid').'</span><span class="wpvivid-element-space-right wpvivid-download-file-size">'.$file['size'].'</span><span class="wpvivid-element-space-right">|</span><span class=" wpvivid-element-space-right wpvivid-ready-download"><a style="cursor: pointer;">Download</a></span>';
        }
        else if($file['status']=='file_not_found')
        {
            $html.='<span>' . __('File not found', 'wpvivid') . '</span>';
        }
        else if($file['status']=='need_download')
        {
            $html.='<span>'.__('File Size: ', 'wpvivid').'</span><span class="wpvivid-element-space-right wpvivid-download-file-size">'.$file['size'].'</span><span class="wpvivid-element-space-right">|</span><span class="wpvivid-element-space-right"><a class="wpvivid-download" style="cursor: pointer;">Prepare to Download</a></span>';
        }
        else if($file['status']=='running')
        {
            $html.='<div class="wpvivid-element-space-bottom">
                        <span class="wpvivid-element-space-right">Retriving (remote storage to web server)</span><span class="wpvivid-element-space-right">|</span><span>File Size: </span><span class="wpvivid-element-space-right wpvivid-download-file-size">'.$file['size'].'</span><span class="wpvivid-element-space-right">|</span><span>Downloaded Size: </span><span>'.$file['downloaded_size'].'</span>
                    </div>
                    <div style="width:100%;height:10px; background-color:#dcdcdc;">
                        <div style="background-color:#0085ba; float:left;width:'.$file['progress_text'].'%;height:10px;"></div>
                    </div>';
        }
        else if($file['status']=='timeout')
        {
            $html.='<div class="wpvivid-element-space-bottom">
                        <span>Download timeout, please retry.</span>
                    </div>
                    <div>
                        <span>'.__('File Size: ', 'wpvivid').'</span><span class="wpvivid-element-space-right wpvivid-download-file-size">'.$file['size'].'</span><span class="wpvivid-element-space-right">|</span><span class="wpvivid-element-space-right"><a class="wpvivid-download" style="cursor: pointer;">Prepare to Download</a></span>
                    </div>';
        }
        else if($file['status']=='error')
        {
            $html.='<div class="wpvivid-element-space-bottom">
                        <span>'.$file['error'].'</span>
                    </div>
                    <div>
                        <span>'.__('File Size: ', 'wpvivid').'</span><span class="wpvivid-element-space-right wpvivid-download-file-size">'.$file['size'].'</span><span class="wpvivid-element-space-right">|</span><span class="wpvivid-element-space-right"><a class="wpvivid-download" style="cursor: pointer;">Prepare to Download</a></span>
                    </div>';
        }

        $html.='</div></td>';
        echo $html;
        //size
    }

    public function set_files_list($file_list,$backup_id,$page_num=1)
    {
        $this->file_list=$file_list;
        $this->backup_id=$backup_id;
        $this->page_num=$page_num;
    }

    public function get_pagenum()
    {
        if($this->page_num=='first')
        {
            $this->page_num=1;
        }
        else if($this->page_num=='last')
        {
            $this->page_num=$this->_pagination_args['total_pages'];
        }
        $pagenum = $this->page_num ? $this->page_num : 0;

        if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
        {
            $pagenum = $this->_pagination_args['total_pages'];
        }

        return max( 1, $pagenum );
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $total_items =sizeof($this->file_list);

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => 10,
            )
        );
    }

    public function has_items()
    {
        return !empty($this->file_list);
    }

    public function display_rows()
    {
        $this->_display_rows($this->file_list);
    }

    private function _display_rows($file_list)
    {
        $page=$this->get_pagenum();

        $page_file_list=array();
        $count=0;
        while ( $count<$page )
        {
            $page_file_list = array_splice( $file_list, 0, 10);
            $count++;
        }
        foreach ( $page_file_list as $key=>$file)
        {
            $file['key']=$key;
            $this->single_row($file);
        }
    }

    public function single_row($file)
    {
        ?>
        <tr slug="<?php echo $file['key']?>" type="common">
            <?php $this->single_row_columns( $file ); ?>
        </tr>
        <?php
    }

    protected function pagination( $which )
    {
        if ( empty( $this->_pagination_args ) )
        {
            return;
        }

        $total_items     = $this->_pagination_args['total_items'];
        $total_pages     = $this->_pagination_args['total_pages'];
        $infinite_scroll = false;
        if ( isset( $this->_pagination_args['infinite_scroll'] ) )
        {
            $infinite_scroll = $this->_pagination_args['infinite_scroll'];
        }

        if ( 'top' === $which && $total_pages > 1 )
        {
            $this->screen->render_screen_reader_content( 'heading_pagination' );
        }

        $output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

        $current              = $this->get_pagenum();

        $page_links = array();

        $total_pages_before = '<span class="paging-input">';
        $total_pages_after  = '</span></span>';

        $disable_first = $disable_last = $disable_prev = $disable_next = false;

        if ( $current == 1 ) {
            $disable_first = true;
            $disable_prev  = true;
        }
        if ( $current == 2 ) {
            $disable_first = true;
        }
        if ( $current == $total_pages ) {
            $disable_last = true;
            $disable_next = true;
        }
        if ( $current == $total_pages - 1 ) {
            $disable_last = true;
        }

        if ( $disable_first ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='first-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'First page' ),
                '&laquo;'
            );
        }

        if ( $disable_prev ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='prev-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Previous page' ),
                '&lsaquo;'
            );
        }

        if ( 'bottom' === $which ) {
            $html_current_page  = $current;
            $total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
        } else {
            $html_current_page = sprintf(
                "%s<input class='current-page' id='current-page-selector-filelist' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label for="current-page-selector-filelist" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
                $current,
                strlen( $total_pages )
            );
        }
        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[]     = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

        if ( $disable_next ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='next-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Next page' ),
                '&rsaquo;'
            );
        }

        if ( $disable_last ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='last-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'Last page' ),
                '&raquo;'
            );
        }

        $pagination_links_class = 'pagination-links';
        if ( ! empty( $infinite_scroll ) ) {
            $pagination_links_class .= ' hide-if-js';
        }
        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages ) {
            $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
            $page_class = ' no-pages';
        }
        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

        echo $this->_pagination;
    }

    protected function display_tablenav( $which ) {
        $css_type = '';
        if ( 'top' === $which ) {
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
            $css_type = 'margin: 0 0 10px 0';
        }
        else if( 'bottom' === $which ) {
            $css_type = 'margin: 10px 0 0 0';
        }

        $total_pages     = $this->_pagination_args['total_pages'];
        if ( $total_pages >1)
        {
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php esc_attr_e($css_type); ?>">
                <?php
                $this->extra_tablenav( $which );
                $this->pagination( $which );
                ?>

                <br class="clear" />
            </div>
            <?php
        }
    }

    public function display()
    {
        $singular = $this->_args['singular'];

        $this->display_tablenav( 'top' );

        $this->screen->render_screen_reader_content( 'heading_list' );
        ?>
        <table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
            <thead>
            <tr>
                <?php $this->print_column_headers(); ?>
            </tr>
            </thead>

            <tbody id="the-list"
                <?php
                if ( $singular ) {
                    echo " data-wp-lists='list:$singular'";
                }
                ?>
            >
            <?php $this->display_rows_or_placeholder(); ?>
            </tbody>

        </table>
        <?php
        $this->display_tablenav( 'bottom' );
    }
}

class WPvivid_Incremental_List extends WP_List_Table
{
    public $page_num;
    public $incremental_list;

    public function __construct( $args = array() )
    {
        parent::__construct(
            array(
                'plural' => 'incremental',
                'screen' => 'incremental'
            )
        );
    }

    protected function get_table_classes()
    {
        return array( 'widefat striped' );
    }

    public function print_column_headers( $with_id = true )
    {
        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

        if (!empty($columns['cb'])) {
            static $cb_counter = 1;
            $columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __('Select All') . '</label>'
                . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox"/>';
            $cb_counter++;
        }

        foreach ( $columns as $column_key => $column_display_name )
        {
            $class = array( 'manage-column', "column-$column_key" );

            if ( in_array( $column_key, $hidden ) )
            {
                $class[] = 'hidden';
            }

            if ( $column_key === $primary )
            {
                $class[] = 'column-primary';
            }

            if ( $column_key === 'cb' )
            {
                $class[] = 'check-column';
            }

            $tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
            $scope = ( 'th' === $tag ) ? 'scope="col"' : '';
            $id    = $with_id ? "id='$column_key'" : '';

            if ( ! empty( $class ) )
            {
                $class = "class='" . join( ' ', $class ) . "'";
            }

            echo "<$tag $scope $id $class>$column_display_name</$tag>";
        }
    }

    public function get_columns()
    {
        $columns = array();
        $columns['wpvivid_incremental_backup_date'] = __( 'Backup Cycle Start Time', 'wpvivid' ) . '
                <span class="dashicons dashicons-editor-help wpvivid-dashicons-editor-help wpvivid-tooltip">
                    <div class="wpvivid-bottom">
                        <!-- The content you need -->
                        <p>Every folder below stores the incremental backups of the current backup cycle.</p>
                        <i></i> <!-- do not delete this line -->
                    </div>
                </span>';
        $columns['wpvivid_incremental_backup_action'] = __( 'Action', 'wpvivid' );
        return $columns;
    }

    public function _column_wpvivid_incremental_backup_date($incremental)
    {
        $time = date('F d, Y', $incremental['path']);

        $html='<td class="tablelistcolumn" style="width: 95%;">
                    <div>
                        <span class="dashicons dashicons-portfolio wpvivid-dashicons-orange"></span>
                        <span>'.$time.'</span>
                    </div>
               </td>';
        echo $html;
    }

    public function _column_wpvivid_incremental_backup_action($incremental)
    {
        $html='<td class="tablelistcolumn"><div class="wpvivid-incremental-child" style="padding:0; width: 5%;">';
        $html.='<input type="button" value="scan" />';
        $html.='</div></td>';
        echo $html;
    }

    public function set_incremental_list($incremental_list,$page_num=1)
    {
        $this->incremental_list=$incremental_list;
        $this->page_num=$page_num;
    }

    public function get_pagenum()
    {
        if($this->page_num=='first')
        {
            $this->page_num=1;
        }
        else if($this->page_num=='last')
        {
            $this->page_num=$this->_pagination_args['total_pages'];
        }
        $pagenum = $this->page_num ? $this->page_num : 0;

        if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
        {
            $pagenum = $this->_pagination_args['total_pages'];
        }

        return max( 1, $pagenum );
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $total_items =sizeof($this->incremental_list);

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => 5,
            )
        );
    }

    public function has_items()
    {
        return !empty($this->incremental_list);
    }

    public function display_rows()
    {
        $this->_display_rows($this->incremental_list);
    }

    private function _display_rows($incremental_list)
    {
        $page=$this->get_pagenum();

        $page_incremental_list=array();
        $count=0;
        while ( $count<$page )
        {
            $page_incremental_list = array_splice( $incremental_list, 0, 5);
            $count++;
        }
        foreach ( $page_incremental_list as $key=>$incremental)
        {
            $this->single_row($incremental);
        }
    }

    public function single_row($incremental)
    {
        ?>
        <tr id="<?php echo $incremental['og_path']; ?>">
            <?php $this->single_row_columns( $incremental ); ?>
        </tr>
        <?php
    }

    protected function pagination( $which )
    {
        if ( empty( $this->_pagination_args ) )
        {
            return;
        }

        $total_items     = $this->_pagination_args['total_items'];
        $total_pages     = $this->_pagination_args['total_pages'];
        $infinite_scroll = false;
        if ( isset( $this->_pagination_args['infinite_scroll'] ) )
        {
            $infinite_scroll = $this->_pagination_args['infinite_scroll'];
        }

        if ( 'top' === $which && $total_pages > 1 )
        {
            $this->screen->render_screen_reader_content( 'heading_pagination' );
        }

        $output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

        $current              = $this->get_pagenum();

        $page_links = array();

        $total_pages_before = '<span class="paging-input">';
        $total_pages_after  = '</span></span>';

        $disable_first = $disable_last = $disable_prev = $disable_next = false;

        if ( $current == 1 ) {
            $disable_first = true;
            $disable_prev  = true;
        }
        if ( $current == 2 ) {
            $disable_first = true;
        }
        if ( $current == $total_pages ) {
            $disable_last = true;
            $disable_next = true;
        }
        if ( $current == $total_pages - 1 ) {
            $disable_last = true;
        }

        if ( $disable_first ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='first-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'First page' ),
                '&laquo;'
            );
        }

        if ( $disable_prev ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='prev-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Previous page' ),
                '&lsaquo;'
            );
        }

        if ( 'bottom' === $which ) {
            $html_current_page  = $current;
            $total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
        } else {
            $html_current_page = sprintf(
                "%s<input class='current-page' id='current-page-selector-filelist' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label for="current-page-selector-filelist" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
                $current,
                strlen( $total_pages )
            );
        }
        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[]     = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

        if ( $disable_next ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='next-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Next page' ),
                '&rsaquo;'
            );
        }

        if ( $disable_last ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='last-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'Last page' ),
                '&raquo;'
            );
        }

        $pagination_links_class = 'pagination-links';
        if ( ! empty( $infinite_scroll ) ) {
            $pagination_links_class .= ' hide-if-js';
        }
        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages ) {
            $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
            $page_class = ' no-pages';
        }
        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

        echo $this->_pagination;
    }

    protected function display_tablenav( $which ) {
        $css_type = '';
        if ( 'top' === $which ) {
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
            $css_type = 'margin: 0 0 10px 0';
        }
        else if( 'bottom' === $which ) {
            $css_type = 'margin: 10px 0 0 0';
        }

        $total_pages     = $this->_pagination_args['total_pages'];
        if ( $total_pages >1)
        {
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php esc_attr_e($css_type); ?>">
                <?php
                $this->extra_tablenav( $which );
                $this->pagination( $which );
                ?>

                <br class="clear" />
            </div>
            <?php
        }
    }

    public function display()
    {
        $singular = $this->_args['singular'];

        $this->display_tablenav( 'top' );

        $this->screen->render_screen_reader_content( 'heading_list' );
        ?>
        <table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
            <thead>
            <tr>
                <?php $this->print_column_headers(); ?>
            </tr>
            </thead>

            <tbody id="the-list"
                <?php
                if ( $singular ) {
                    echo " data-wp-lists='list:$singular'";
                }
                ?>
            >
            <?php $this->display_rows_or_placeholder(); ?>
            </tbody>

        </table>
        <?php
        $this->display_tablenav( 'bottom' );
    }
}

class WPvivid_Incremental_Files_Download_List extends WP_List_Table
{
    public $page_num;
    public $file_list;

    public function __construct( $args = array() )
    {
        parent::__construct(
            array(
                'plural' => 'imcremental_files_download',
                'screen' => 'imcremental_files_download'
            )
        );
    }

    protected function get_table_classes()
    {
        return array( 'widefat' );
    }

    public function print_column_headers( $with_id = true )
    {
        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

        if (!empty($columns['cb'])) {
            static $cb_counter = 1;
            $columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __('Select All') . '</label>'
                . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox"/>';
            $cb_counter++;
        }

        foreach ( $columns as $column_key => $column_display_name )
        {
            $class = array( 'manage-column', "column-$column_key" );

            if ( in_array( $column_key, $hidden ) )
            {
                $class[] = 'hidden';
            }

            if ( $column_key === $primary )
            {
                $class[] = 'column-primary';
            }

            if ( $column_key === 'cb' )
            {
                $class[] = 'check-column';
            }

            $tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
            $scope = ( 'th' === $tag ) ? 'scope="col"' : '';
            $id    = $with_id ? "id='$column_key'" : '';

            if ( ! empty( $class ) )
            {
                $class = "class='" . join( ' ', $class ) . "'";
            }

            echo "<$tag $scope $id $class>$column_display_name</$tag>";
        }
    }

    public function set_files_list($file_list,$page_num=1)
    {
        $this->file_list=$file_list;
        $this->page_num=$page_num;
    }

    public function get_columns()
    {
        $columns = array();
        $columns['wpvivid_create_date'] = __( 'Creation Date', 'wpvivid' );
        $columns['wpvivid_type'] = __( 'Type', 'wpvivid' );
        $columns['wpvivid_size'] =__( 'Size', 'wpvivid'  );
        $columns['wpvivid_download'] = __( 'Download', 'wpvivid'  );
        return $columns;
    }

    public function _column_wpvivid_create_date( $file )
    {
        $date = date('M-d-Y H:i', $file['create_time']);
        $html='<td class="row-title">'.$date.'</td>';
        echo $html;
    }

    public function _column_wpvivid_type( $file )
    {
        $html='<td>Incremental</td>';
        echo $html;
    }

    public function _column_wpvivid_size( $file )
    {
        $html='<td>'.$file['size'].'</td>';
        echo $html;
    }

    public function _column_wpvivid_download( $file )
    {
        $html='<td style="cursor: pointer;">';
        if($file['status']=='completed'){
            $html.='<span class="wpvivid-rectangle wpvivid-green wpvivid-ready-download" style="cursor: pointer;">Download</span>';
        }
        else if($file['status']=='file_not_found'){
            $html.='<span>File not found</span>';
        }
        else if($file['status']=='need_download') {
            $html.='<span class="wpvivid-rectangle wpvivid-green wpvivid-download" style="cursor: pointer;">Prepare to Download</span>';
        }
        else if($file['status']=='running'){
            $html.='<span class="wpvivid-rectangle wpvivid-green wpvivid-download" style="pointer-events: none; opacity: 0.4;">Prepare to Download</span>';
        }
        else if($file['status']=='timeout'){
            $html.='<span class="wpvivid-rectangle wpvivid-green wpvivid-download" style="cursor: pointer;">Prepare to Download</span>';
        }
        else if($file['status']=='error'){
            $html.='<span class="wpvivid-rectangle wpvivid-green wpvivid-download" style="cursor: pointer;">Prepare to Download</span>';
        }
        $html.='</td>';
        echo $html;
    }

    public function get_pagenum()
    {
        if($this->page_num=='first')
        {
            $this->page_num=1;
        }
        else if($this->page_num=='last')
        {
            $this->page_num=$this->_pagination_args['total_pages'];
        }
        $pagenum = $this->page_num ? $this->page_num : 0;

        if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
        {
            $pagenum = $this->_pagination_args['total_pages'];
        }

        return max( 1, $pagenum );
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $total_items =sizeof($this->file_list);

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => 10,
            )
        );
    }

    public function has_items()
    {
        return !empty($this->file_list);
    }

    public function display_rows()
    {
        $this->_display_rows($this->file_list,$this->soft_content);
    }

    private function _display_rows($file_list)
    {
        $page=$this->get_pagenum();

        $page_file_list=array();
        $count=0;
        while ( $count<$page )
        {
            $page_file_list = array_splice( $file_list, 0, 10);
            $count++;
        }
        foreach ( $page_file_list as $key=>$file)
        {
            $this->single_row($file);
        }
    }

    public function single_row($file)
    {
        ?>
        <tr slug="<?php echo $file['file_name']?>" type="incremental">
            <?php $this->single_row_columns( $file ); ?>
        </tr>
        <?php
    }

    protected function pagination( $which )
    {
        if ( empty( $this->_pagination_args ) )
        {
            return;
        }

        $total_items     = $this->_pagination_args['total_items'];
        $total_pages     = $this->_pagination_args['total_pages'];
        $infinite_scroll = false;
        if ( isset( $this->_pagination_args['infinite_scroll'] ) )
        {
            $infinite_scroll = $this->_pagination_args['infinite_scroll'];
        }

        if ( 'top' === $which && $total_pages > 1 )
        {
            $this->screen->render_screen_reader_content( 'heading_pagination' );
        }

        $output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

        $current              = $this->get_pagenum();

        $page_links = array();

        $total_pages_before = '<span class="paging-input">';
        $total_pages_after  = '</span></span>';

        $disable_first = $disable_last = $disable_prev = $disable_next = false;

        if ( $current == 1 ) {
            $disable_first = true;
            $disable_prev  = true;
        }
        if ( $current == 2 ) {
            $disable_first = true;
        }
        if ( $current == $total_pages ) {
            $disable_last = true;
            $disable_next = true;
        }
        if ( $current == $total_pages - 1 ) {
            $disable_last = true;
        }

        if ( $disable_first ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='first-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'First page' ),
                '&laquo;'
            );
        }

        if ( $disable_prev ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='prev-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Previous page' ),
                '&lsaquo;'
            );
        }

        if ( 'bottom' === $which ) {
            $html_current_page  = $current;
            $total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
        } else {
            $html_current_page = sprintf(
                "%s<input class='current-page' id='current-page-selector-filelist' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label for="current-page-selector-filelist" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
                $current,
                strlen( $total_pages )
            );
        }
        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[]     = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

        if ( $disable_next ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='next-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Next page' ),
                '&rsaquo;'
            );
        }

        if ( $disable_last ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='last-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'Last page' ),
                '&raquo;'
            );
        }

        $pagination_links_class = 'pagination-links';
        if ( ! empty( $infinite_scroll ) ) {
            $pagination_links_class .= ' hide-if-js';
        }
        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages ) {
            $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
            $page_class = ' no-pages';
        }
        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

        echo $this->_pagination;
    }

    protected function display_tablenav( $which ) {
        $css_type = '';
        if ( 'top' === $which ) {
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
            $css_type = 'margin: 0 0 10px 0';
        }
        else if( 'bottom' === $which ) {
            $css_type = 'margin: 10px 0 0 0';
        }

        $total_pages     = $this->_pagination_args['total_pages'];
        if ( $total_pages >1)
        {
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php esc_attr_e($css_type); ?>">
                <?php
                $this->extra_tablenav( $which );
                $this->pagination( $which );
                ?>

                <br class="clear" />
            </div>
            <?php
        }
    }

    public function display()
    {
        foreach ($this->file_list as $file_name => $file_info){
            if(preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}-[0-9]{2}-[0-9]{2}/',$file_name,$matches)) {
                $backup_date=$matches[0];
                $time_array=explode('-',$backup_date);
                if(sizeof($time_array)>4){
                    $time=$time_array[0].'-'.$time_array[1].'-'.$time_array[2].' '.$time_array[3].':'.$time_array[4];
                    $file_info['create_time']=strtotime($time);
                }
            }
            else {
                $file_info['create_time']=0;
            }
            $this->file_list[$file_name]['create_time']=$file_info['create_time'];
            $this->file_list[$file_name]['file_name']=$file_name;
        }

        usort($this->file_list, function ($a, $b)
        {
            if ($a['create_time'] == $b['create_time'])
            {
                return 0;
            }

            if($a['create_time'] > $b['create_time'])
            {
                return -1;
            }
            else
            {
                return 1;
            }
        });
        $last_array = array_pop($this->file_list);

        $singular = $this->_args['singular'];

        $this->display_tablenav( 'top' );

        $this->screen->render_screen_reader_content( 'heading_list' );
        ?>
        <table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
            <thead>
            <tr>
                <?php $this->print_column_headers(); ?>
            </tr>

            <tr slug="<?php echo $last_array['file_name']; ?>" type="incremental">
                <td class="row-title"><?php esc_attr_e( date('M-d-Y H:i', $last_array['create_time']) ); ?></td>
                <td><?php esc_attr_e( 'Full Backup', 'WpAdminStyle' ); ?></td>
                <td><?php esc_attr_e( $last_array['size'] ); ?></td>
                <td style="cursor: pointer">
                    <?php
                    if($last_array['status']=='completed'){
                        ?>
                        <span class="wpvivid-rectangle wpvivid-green wpvivid-ready-download">Download</span>
                        <?php
                    }
                    else if($last_array['status']=='file_not_found'){
                        ?>
                        <span>File not found</span>
                        <?php
                    }
                    else if($last_array['status']=='need_download') {
                        ?>
                        <span class="wpvivid-rectangle wpvivid-green wpvivid-download">Prepare to Download</span>
                        <?php
                    }
                    else if($last_array['status']=='running'){
                        ?>
                        <span class="wpvivid-rectangle wpvivid-green wpvivid-download" style="pointer-events: none; opacity: 0.4;">Prepare to Download</span>
                        <?php
                    }
                    else if($last_array['status']=='timeout'){
                        ?>
                        <span class="wpvivid-rectangle wpvivid-green wpvivid-download">Prepare to Download</span>
                        <?php
                    }
                    else if($last_array['status']=='error'){
                        ?>
                        <span class="wpvivid-rectangle wpvivid-green wpvivid-download">Prepare to Download</span>
                        <?php
                    }
                    else{
                        ?>
                        <span class="wpvivid-rectangle wpvivid-green wpvivid-ready-download">Download</span>
                        <?php
                    }
                    ?>
                </td>
            </tr>

            </thead>


            <tbody id="the-list"
                <?php
                if ( $singular ) {
                    echo " data-wp-lists='list:$singular'";
                }
                ?>
            >
            <?php $this->display_rows_or_placeholder(); ?>
            </tbody>

        </table>
        <?php
        $this->display_tablenav( 'bottom' );
    }

    public function display_rows_or_placeholder() {
        if ( $this->has_items() ) {
            $this->display_rows();
        } else {
            echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
            _e( 'There is no \'Incremental Backup\' created.' );
            echo '</td></tr>';
        }
    }
}

class WPvivid_Incremental_Files_Restore_List extends WP_List_Table
{
    public $page_num;
    public $versions_list;
    public $backup_id;

    public function __construct( $args = array() )
    {
        parent::__construct(
            array(
                'plural' => 'imcremental_files_restore',
                'screen' => 'imcremental_files_restore'
            )
        );
    }

    protected function get_table_classes()
    {
        return array( 'widefat' );
    }

    public function print_column_headers( $with_id = true )
    {
        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

        if (!empty($columns['cb'])) {
            static $cb_counter = 1;
            $columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __('Select All') . '</label>'
                . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox"/>';
            $cb_counter++;
        }

        foreach ( $columns as $column_key => $column_display_name )
        {
            $class = array( 'manage-column', "column-$column_key" );

            if ( in_array( $column_key, $hidden ) )
            {
                $class[] = 'hidden';
            }

            if ( $column_key === $primary )
            {
                $class[] = 'column-primary';
            }

            if ( $column_key === 'cb' )
            {
                $class[] = 'check-column';
            }

            $tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
            $scope = ( 'th' === $tag ) ? 'scope="col"' : '';
            $id    = $with_id ? "id='$column_key'" : '';

            if ( ! empty( $class ) )
            {
                $class = "class='" . join( ' ', $class ) . "'";
            }

            echo "<$tag $scope $id $class>$column_display_name</$tag>";
        }
    }

    public function set_versions($versions_list,$page_num=1)
    {
        $this->versions_list=$versions_list;
        $this->page_num=$page_num;
    }

    public function get_columns()
    {
        $columns = array();
        $columns['wpvivid_create_date'] = __( 'Creation Date', 'wpvivid' );
        $columns['wpvivid_type'] = __( 'Type', 'wpvivid' );
        $columns['wpvivid_size'] =__( 'Size', 'wpvivid'  );
        $columns['wpvivid_restore'] = __( 'Restore', 'wpvivid'  );
        return $columns;
    }

    public function _column_wpvivid_create_date( $version )
    {
        $html='<td class="row-title">'.$version['date'].'</td>';
        echo $html;
    }

    public function _column_wpvivid_type( $version )
    {
        $html='<td>Incremental</td>';
        echo $html;
    }

    public function _column_wpvivid_size( $version )
    {
        $html='<td>'.$version['size'].'</td>';
        echo $html;
    }

    public function _column_wpvivid_restore( $version )
    {
        $html='<td style="cursor: pointer;">';
        $html.='<span class="wpvivid-rectangle wpvivid-green" onclick="wpvivid_select_restore_version('.$version['version'].');" style="cursor: pointer;">Restore</span>';
        $html.='</td>';
        echo $html;
    }

    public function get_pagenum()
    {
        if($this->page_num=='first')
        {
            $this->page_num=1;
        }
        else if($this->page_num=='last')
        {
            $this->page_num=$this->_pagination_args['total_pages'];
        }
        $pagenum = $this->page_num ? $this->page_num : 0;

        if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
        {
            $pagenum = $this->_pagination_args['total_pages'];
        }

        return max( 1, $pagenum );
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $total_items =sizeof($this->versions_list);

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => 10,
            )
        );
    }

    public function has_items()
    {
        return !empty($this->versions_list);
    }

    public function display_rows()
    {
        $this->_display_rows($this->versions_list);
    }

    private function _display_rows($versions_list)
    {
        $page=$this->get_pagenum();

        $page_file_list=array();
        $count=0;
        while ( $count<$page )
        {
            $page_file_list = array_splice( $versions_list, 0, 10);
            $count++;
        }
        foreach ( $page_file_list as $key=>$file)
        {
            $this->single_row($file);
        }
    }

    public function single_row($version)
    {
        ?>
        <tr slug="<?php echo $version['version']?>" type="incremental">
            <?php $this->single_row_columns( $version ); ?>
        </tr>
        <?php
    }

    protected function pagination( $which )
    {
        if ( empty( $this->_pagination_args ) )
        {
            return;
        }

        $total_items     = $this->_pagination_args['total_items'];
        $total_pages     = $this->_pagination_args['total_pages'];
        $infinite_scroll = false;
        if ( isset( $this->_pagination_args['infinite_scroll'] ) )
        {
            $infinite_scroll = $this->_pagination_args['infinite_scroll'];
        }

        if ( 'top' === $which && $total_pages > 1 )
        {
            $this->screen->render_screen_reader_content( 'heading_pagination' );
        }

        $output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

        $current              = $this->get_pagenum();

        $page_links = array();

        $total_pages_before = '<span class="paging-input">';
        $total_pages_after  = '</span></span>';

        $disable_first = $disable_last = $disable_prev = $disable_next = false;

        if ( $current == 1 ) {
            $disable_first = true;
            $disable_prev  = true;
        }
        if ( $current == 2 ) {
            $disable_first = true;
        }
        if ( $current == $total_pages ) {
            $disable_last = true;
            $disable_next = true;
        }
        if ( $current == $total_pages - 1 ) {
            $disable_last = true;
        }

        if ( $disable_first ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='first-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'First page' ),
                '&laquo;'
            );
        }

        if ( $disable_prev ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='prev-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Previous page' ),
                '&lsaquo;'
            );
        }

        if ( 'bottom' === $which ) {
            $html_current_page  = $current;
            $total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
        } else {
            $html_current_page = sprintf(
                "%s<input class='current-page' id='current-page-selector-filelist' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label for="current-page-selector-filelist" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
                $current,
                strlen( $total_pages )
            );
        }
        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[]     = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

        if ( $disable_next ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='next-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Next page' ),
                '&rsaquo;'
            );
        }

        if ( $disable_last ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='last-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'Last page' ),
                '&raquo;'
            );
        }

        $pagination_links_class = 'pagination-links';
        if ( ! empty( $infinite_scroll ) ) {
            $pagination_links_class .= ' hide-if-js';
        }
        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages ) {
            $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
            $page_class = ' no-pages';
        }
        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

        echo $this->_pagination;
    }

    protected function display_tablenav( $which ) {
        $css_type = '';
        if ( 'top' === $which ) {
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
            $css_type = 'margin: 0 0 10px 0';
        }
        else if( 'bottom' === $which ) {
            $css_type = 'margin: 10px 0 0 0';
        }

        $total_pages     = $this->_pagination_args['total_pages'];
        if ( $total_pages >1)
        {
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php esc_attr_e($css_type); ?>">
                <?php
                $this->extra_tablenav( $which );
                $this->pagination( $which );
                ?>

                <br class="clear" />
            </div>
            <?php
        }
    }

    public function display()
    {
        usort($this->versions_list, function ($a, $b)
        {
            if ($a['version'] == $b['version'])
            {
                return 0;
            }

            if($a['version'] > $b['version'])
            {
                return -1;
            }
            else
            {
                return 1;
            }
        });
        $last_array = array_pop($this->versions_list);


        $singular = $this->_args['singular'];

        $this->display_tablenav( 'top' );

        $this->screen->render_screen_reader_content( 'heading_list' );
        ?>
        <table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
            <thead>
            <tr>
                <?php $this->print_column_headers(); ?>
            </tr>

            <tr slug="<?php echo $last_array['version']; ?>" type="incremental">
                <td class="row-title"><?php esc_attr_e($last_array['date']); ?></td>
                <td><?php esc_attr_e('Full Backup'); ?></td>
                <td><?php esc_attr_e($last_array['size']); ?></td>
                <td style="cursor: pointer;"><span class="wpvivid-rectangle wpvivid-green" onclick="wpvivid_select_restore_version(<?php echo $last_array['version']; ?>);">Restore</span></td>
            </tr>

            </thead>

            <tbody id="the-list"
                <?php
                if ( $singular ) {
                    echo " data-wp-lists='list:$singular'";
                }
                ?>
            >
            <?php $this->display_rows_or_placeholder(); ?>
            </tbody>

        </table>
        <?php
        $this->display_tablenav( 'bottom' );
    }

    public function display_rows_or_placeholder() {
        if ( $this->has_items() ) {
            $this->display_rows();
        } else {
            echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
            _e( 'There is no \'Incremental Backup\' created.' );
            echo '</td></tr>';
        }
    }
}

class WPvivid_Versions_List extends WP_List_Table
{
    public $page_num;
    public $versions_list;
    public $backup_id;

    public function __construct( $args = array() )
    {
        parent::__construct(
            array(
                'plural' => 'versions',
                'screen' => 'versions'
            )
        );
    }

    protected function get_table_classes()
    {
        return array( 'widefat striped' );
    }

    public function print_column_headers( $with_id = true )
    {
        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

        if (!empty($columns['cb'])) {
            static $cb_counter = 1;
            $columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __('Select All') . '</label>'
                . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox"/>';
            $cb_counter++;
        }

        foreach ( $columns as $column_key => $column_display_name )
        {
            $class = array( 'manage-column', "column-$column_key" );

            if ( in_array( $column_key, $hidden ) )
            {
                $class[] = 'hidden';
            }

            if ( $column_key === $primary )
            {
                $class[] = 'column-primary';
            }

            if ( $column_key === 'cb' )
            {
                $class[] = 'check-column';
            }

            $tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
            $scope = ( 'th' === $tag ) ? 'scope="col"' : '';
            $id    = $with_id ? "id='$column_key'" : '';

            if ( ! empty( $class ) )
            {
                $class = "class='" . join( ' ', $class ) . "'";
            }

            echo "<$tag $scope $id $class>$column_display_name</$tag>";
        }
    }

    public function get_columns()
    {
        $columns = array();
        $columns['wpvivid_backup_date'] = __( 'Date', 'wpvivid' );
        $columns['wpvivid_backup_size'] = __( 'Size', 'wpvivid' );
        $columns['wpvivid_backup_btn'] = __( 'Action', 'wpvivid' );
        return $columns;
    }

    public function _column_wpvivid_backup_date( $version )
    {
        $html='<td class="tablelistcolumn" style="width: 45%;"><div style="padding:0;">';
        $html.='<span class="wpvivid-element-space-right wpvivid-download-file-size">'.$version['date'].'</span>';
        $html.='</div></td>';
        echo $html;
    }

    public function _column_wpvivid_backup_size( $version )
    {
        $html='<td class="tablelistcolumn" style="width: 45%;"><div style="padding:0;">';
        $html.='<span class="wpvivid-element-space-right">'.$version['size'].'</span>';
        $html.='</div></td>';
        echo $html;
    }

    public function _column_wpvivid_backup_btn( $version )
    {
        $html='<td class="tablelistcolumn" style="width: 10%;"><div style="padding:0;">';
        $html.='<input type="button" value="Restore" onclick="wpvivid_select_restore_version('.$version['version'].');"/>';
        $html.='</div></td>';
        echo $html;
    }

    public function set_versions($versions_list,$page_num=1)
    {
        $this->versions_list=$versions_list;
        $this->page_num=$page_num;
    }

    public function get_pagenum()
    {
        if($this->page_num=='first')
        {
            $this->page_num=1;
        }
        else if($this->page_num=='last')
        {
            $this->page_num=$this->_pagination_args['total_pages'];
        }
        $pagenum = $this->page_num ? $this->page_num : 0;

        if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] )
        {
            $pagenum = $this->_pagination_args['total_pages'];
        }

        return max( 1, $pagenum );
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $total_items =sizeof($this->versions_list);

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => 10,
            )
        );
    }

    public function has_items()
    {
        return !empty($this->versions_list);
    }

    public function display_rows()
    {
        $this->_display_rows($this->versions_list);
    }

    private function _display_rows($versions_list)
    {
        $page=$this->get_pagenum();

        $page_list=array();
        $count=0;
        while ( $count<$page )
        {
            $page_list = array_splice( $versions_list, 0, 10);
            $count++;
        }
        foreach ( $page_list as $version)
        {
            $this->single_row($version);
        }
    }

    public function single_row($version)
    {
        ?>
        <tr version="<?php echo $version['version']; ?>">
            <?php $this->single_row_columns( $version ); ?>
        </tr>
        <?php
    }

    protected function pagination( $which )
    {
        if ( empty( $this->_pagination_args ) )
        {
            return;
        }

        $total_items     = $this->_pagination_args['total_items'];
        $total_pages     = $this->_pagination_args['total_pages'];
        $infinite_scroll = false;
        if ( isset( $this->_pagination_args['infinite_scroll'] ) )
        {
            $infinite_scroll = $this->_pagination_args['infinite_scroll'];
        }

        if ( 'top' === $which && $total_pages > 1 )
        {
            $this->screen->render_screen_reader_content( 'heading_pagination' );
        }

        $output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

        $current              = $this->get_pagenum();

        $page_links = array();

        $total_pages_before = '<span class="paging-input">';
        $total_pages_after  = '</span></span>';

        $disable_first = $disable_last = $disable_prev = $disable_next = false;

        if ( $current == 1 ) {
            $disable_first = true;
            $disable_prev  = true;
        }
        if ( $current == 2 ) {
            $disable_first = true;
        }
        if ( $current == $total_pages ) {
            $disable_last = true;
            $disable_next = true;
        }
        if ( $current == $total_pages - 1 ) {
            $disable_last = true;
        }

        if ( $disable_first ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='first-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'First page' ),
                '&laquo;'
            );
        }

        if ( $disable_prev ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='prev-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Previous page' ),
                '&lsaquo;'
            );
        }

        if ( 'bottom' === $which ) {
            $html_current_page  = $current;
            $total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
        } else {
            $html_current_page = sprintf(
                "%s<input class='current-page' id='current-page-selector-filelist' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label for="current-page-selector-filelist" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
                $current,
                strlen( $total_pages )
            );
        }
        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[]     = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

        if ( $disable_next ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='next-page button' value='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                $current,
                __( 'Next page' ),
                '&rsaquo;'
            );
        }

        if ( $disable_last ) {
            $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
        } else {
            $page_links[] = sprintf(
                "<div class='last-page button'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></div>",
                __( 'Last page' ),
                '&raquo;'
            );
        }

        $pagination_links_class = 'pagination-links';
        if ( ! empty( $infinite_scroll ) ) {
            $pagination_links_class .= ' hide-if-js';
        }
        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages ) {
            $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
            $page_class = ' no-pages';
        }
        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

        echo $this->_pagination;
    }

    protected function display_tablenav( $which ) {
        $css_type = '';
        if ( 'top' === $which ) {
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
            $css_type = 'margin: 0 0 10px 0';
        }
        else if( 'bottom' === $which ) {
            $css_type = 'margin: 10px 0 0 0';
        }

        $total_pages     = $this->_pagination_args['total_pages'];
        if ( $total_pages >1)
        {
            ?>
            <div class="tablenav <?php echo esc_attr( $which ); ?>" style="<?php esc_attr_e($css_type); ?>">
                <?php
                $this->extra_tablenav( $which );
                $this->pagination( $which );
                ?>

                <br class="clear" />
            </div>
            <?php
        }
    }

    public function display()
    {
        $singular = $this->_args['singular'];

        $this->display_tablenav( 'top' );

        $this->screen->render_screen_reader_content( 'heading_list' );
        ?>
        <table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
            <thead>
            <tr>
                <?php $this->print_column_headers(); ?>
            </tr>
            </thead>

            <tbody id="the-list"
                <?php
                if ( $singular ) {
                    echo " data-wp-lists='list:$singular'";
                }
                ?>
            >
            <?php $this->display_rows_or_placeholder(); ?>
            </tbody>

        </table>
        <?php
        $this->display_tablenav( 'bottom' );
    }
}

class WPvivid_BackupList_addon
{
    public $main_tab;
    public $log_tab;

    public function __construct()
    {
        add_filter('wpvivid_get_dashboard_menu', array($this, 'get_dashboard_menu'), 10, 2);
        add_filter('wpvivid_get_dashboard_screens', array($this, 'get_dashboard_screens'), 10);
        add_filter('wpvivid_get_toolbar_menus', array($this, 'get_toolbar_menus'),11);
        //filters
        add_filter('wpvivid_archieve_remote_array', array($this, 'archieve_remote_array'), 11);
        add_filter('wpvivid_get_backuplist',array($this,'get_backup_list'), 11, 2);
        add_filter('wpvivid_get_backuplist_by_id',array($this,'get_backup_list_by_id'), 11, 2);
        add_filter('wpvivid_get_backuplist_item', array($this, 'get_backuplist_item'), 11, 2);
        add_filter('wpvivid_get_backuplist_name',array($this,'get_backuplist_name'),10);
        add_filter('wpvivid_check_remove_restore_database', array($this, 'wpvivid_check_remove_restore_database'), 11, 2);
        add_filter('wpvivid_backup_list_addon', array($this, 'wpvivid_backup_list_addon'), 11);
        add_filter('wpvivid_get_backup_data_by_task',array($this,'get_backup_data_by_task'),10, 2);

        //actions
        add_action('wpvivid_update_backup',array($this, 'update_backup_item'),11, 3);

        //ajax
        add_action('wp_ajax_wpvivid_achieve_local_backup', array($this, 'achieve_local_backup'));
        add_action('wp_ajax_wpvivid_set_security_lock_ex',array( $this,'set_security_lock_ex'));
        add_action('wp_ajax_wpvivid_delete_local_backup', array($this, 'delete_local_backup'));
        add_action('wp_ajax_wpvivid_delete_local_backup_array', array($this, 'delete_local_backup_array'));

        add_action('wp_ajax_wpvivid_achieve_remote_backup', array($this, 'achieve_remote_backup'));
        add_action('wp_ajax_wpvivid_set_remote_security_lock_ex',array( $this,'set_remote_security_lock_ex'));
        add_action('wp_ajax_wpvivid_delete_remote_backup', array($this, 'delete_remote_backup'));
        add_action('wp_ajax_wpvivid_delete_remote_backup_array', array($this, 'delete_remote_backup_array'));

        add_action('wp_ajax_wpvivid_archieve_incremental_remote_folder_list', array($this, 'archieve_incremental_remote_folder_list'));
        add_action('wp_ajax_wpvivid_achieve_incremental_child_path', array($this, 'achieve_incremental_child_path'));

        add_action('wp_ajax_wpvivid_init_download_page_ex',array($this,'init_download_page_ex'));
        add_action('wp_ajax_wpvivid_get_download_page_ex',array($this,'get_download_page_ex'));
        add_action('wp_ajax_wpvivid_get_download_progress',array($this,'get_download_progress'));
        add_action('wp_ajax_wpvivid_get_download_incremental_progress',array($this, 'get_download_incremental_progress'));
        add_action('wp_ajax_wpvivid_download_backup_ex',array($this,'download_backup_ex'));
        add_action('wp_ajax_wpvivid_download_all_backup_ex', array($this, 'download_all_backup_ex'));

        add_action('wp_ajax_wpvivid_backup_content_display', array($this, 'backup_content_display'));

        add_filter('wpvivid_get_role_cap_list',array($this, 'get_caps'));
        add_action('admin_head', array($this, 'my_admin_custom_styles'));
    }

    public function my_admin_custom_styles()
    {
        ?>
        <style type="text/css">
            .updates-table tbody td.wpvivid-check-column, .widefat tbody th.wpvivid-check-column, .widefat tfoot td.wpvivid-check-column, .widefat thead td.wpvivid-check-column {
                padding: 11px 0 0 3px;
            }
            .widefat tfoot td.wpvivid-check-column, .widefat thead td.wpvivid-check-column {
                padding-top: 4px;
                vertical-align: middle;
            }
            .widefat .wpvivid-check-column {
                width: 2.2em;
                padding: 6px 0 25px;
                vertical-align: top;
            }
        </style>
        <?php
    }

    public function get_caps($cap_list)
    {
        $cap['slug']='wpvivid-can-mange-backup';
        $cap['display']='Backups Manager & Restoration';
        $cap['index']=8;
        $cap['icon']='<span class="dashicons dashicons-database wpvivid-dashicons-grey"></span>';
        $cap['menu_slug']=strtolower(sprintf('%s-backup-and-restore', apply_filters('wpvivid_white_label_slug', 'wpvivid')));

        $cap_list[$cap['slug']]=$cap;

        $cap['slug']='wpvivid-can-mange-local-backup';
        $cap['display']='Manage backups in localhost & Restore backups';
        $cap['index']=9;
        $cap['menu_slug']=strtolower(sprintf('%s-localhost-backup-and-restore', apply_filters('wpvivid_white_label_slug', 'wpvivid')));
        $cap['icon']='<strong>-----</strong>';
        $cap_list[$cap['slug']]=$cap;

        $cap['slug']='wpvivid-can-mange-remote-backup';
        $cap['display']='Manage backups in remote & Restore backups';
        $cap['index']=10;
        $cap['menu_slug']=strtolower(sprintf('%s-remote-backup-and-restore', apply_filters('wpvivid_white_label_slug', 'wpvivid')));
        $cap['icon']='<strong>-----</strong>';
        $cap_list[$cap['slug']]=$cap;

        $cap['slug']='wpvivid-can-mange-download-localhost-backup';
        $cap['display']='Download & restore backups from localhost';
        $cap['index']=11;
        $cap['menu_slug']=strtolower(sprintf('%s-download-localhost-backup', apply_filters('wpvivid_white_label_slug', 'wpvivid')));
        $cap['icon']='<strong>-----</strong>';
        $cap_list[$cap['slug']]=$cap;

        $cap['slug']='wpvivid-can-mange-download-remote-backup';
        $cap['display']='Download & restore backups from remote storage';
        $cap['index']=12;
        $cap['menu_slug']=strtolower(sprintf('%s-download-remote-backup', apply_filters('wpvivid_white_label_slug', 'wpvivid')));
        $cap['icon']='<strong>-----</strong>';
        $cap_list[$cap['slug']]=$cap;

        return $cap_list;
    }

    public function get_dashboard_screens($screens)
    {
        $screen['menu_slug']='wpvivid-backup-and-restore';
        $screen['screen_id']='wpvivid-plugin_page_wpvivid-backup-and-restore';
        $screen['is_top']=false;
        $screens[]=$screen;
        return $screens;
    }

    public function get_dashboard_menu($submenus,$parent_slug)
    {
        $display = apply_filters('wpvivid_get_menu_capability_addon', 'menu_backup_restore');
        if($display)
        {
            $submenu['parent_slug'] = $parent_slug;
            $submenu['page_title'] = apply_filters('wpvivid_white_label_display', 'Backups & Restoration');
            $submenu['menu_title'] = 'Backups & Restoration';

            /*
            if( apply_filters('wpvivid_is_user_super_admin',true))
            {
                $submenu['capability'] = 'administrator';
            }
            else {
                $submenu['capability'] = 'wpvivid-can-mange-backup';
            }
             else if( apply_filters('wpvivid_current_user_can',true,'wpvivid-can-mange-local-backup'))
            {
                $submenu['capability'] = 'wpvivid-can-mange-local-backup';
            }
            else if( apply_filters('wpvivid_current_user_can',true,'wpvivid-can-mange-remote-backup'))
            {
                $submenu['capability'] = 'wpvivid-can-mange-remote-backup';
            }
             */

            $submenu['capability'] = apply_filters("wpvivid_menu_capability","administrator","wpvivid-backup-and-restore");

            $submenu['menu_slug'] = strtolower(sprintf('%s-backup-and-restore', apply_filters('wpvivid_white_label_slug', 'wpvivid')));
            $submenu['index'] = 7;
            $submenu['function'] = array($this, 'init_page');
            $submenus[$submenu['menu_slug']] = $submenu;
        }
        return $submenus;
    }

    public function get_toolbar_menus($toolbar_menus)
    {
        $admin_url = apply_filters('wpvivid_get_admin_url', '');
        $display = apply_filters('wpvivid_get_menu_capability_addon', 'menu_backup_restore');
        if($display) {
            $menu['id'] = 'wpvivid_admin_menu_backup_restore';
            $menu['parent'] = 'wpvivid_admin_menu';
            $menu['title'] = 'Backups & Restoration';
            $menu['tab'] = 'admin.php?page=' . apply_filters('wpvivid_white_label_plugin_name', 'wpvivid-backup-and-restore');
            $menu['href'] = $admin_url . 'admin.php?page=' . apply_filters('wpvivid_white_label_plugin_name', 'wpvivid').'-backup-and-restore';

            /*
            if( apply_filters('wpvivid_is_user_super_admin',true))
            {
                $menu['capability'] = 'administrator';
            }
            else {
                $menu['capability'] = 'wpvivid-can-mange-backup';
            }

             else if( apply_filters('wpvivid_current_user_can',true,'wpvivid-can-mange-local-backup'))
            {
                $menu['capability'] = 'wpvivid-can-mange-local-backup';
            }
            else if( apply_filters('wpvivid_current_user_can',true,'wpvivid-can-mange-remote-backup'))
            {
                $menu['capability'] = 'wpvivid-can-mange-remote-backup';
            }
             */

            $menu['capability'] = apply_filters("wpvivid_menu_capability","administrator","wpvivid-backup-and-restore");

            $menu['index'] = 7;
            $toolbar_menus[$menu['parent']]['child'][$menu['id']] = $menu;
        }
        return $toolbar_menus;
    }

    /***** backup and restore filters begin *****/
    public function archieve_remote_array($remote_array){
        $remoteslist=WPvivid_Setting::get_all_remote_options();
        foreach ($remoteslist as $key => $value){
            if($key === 'remote_selected')
            {
                continue;
            }
            if(isset($value['custom_path']))
            {
                if(isset($value['root_path'])){
                    $path = $value['path'].$value['root_path'].$value['custom_path'];
                }
                else{
                    $path = $value['path'].'wpvividbackuppro/'.$value['custom_path'];
                }
            }
            else
            {
                $path = $value['path'];
            }
            $remote_array[$key]['path'] = $path;
        }
        return $remote_array;
    }

    public function get_backup_list($list, $list_name){
        if($list_name == ''){
            $list_name = 'wpvivid_backup_list';
        }
        $list = WPvivid_Setting::get_option($list_name);
        $list = WPvivid_Backuplist::sort_list($list);
        return $list;
    }

    public function get_backup_list_by_id($list, $id){
        $list = WPvivid_Setting::get_option('wpvivid_backup_list');
        foreach ($list as $k=>$backup)
        {
            if ($id == $k)
            {
                $ret['list_name'] = 'wpvivid_backup_list';
                $ret['list_data'] = $list;
                return $ret;
            }
        }

        $list = WPvivid_Setting::get_option('wpvivid_staging_list');
        foreach ($list as $k=>$backup)
        {
            if ($id == $k)
            {
                $ret['list_name'] = 'wpvivid_staging_list';
                $ret['list_data'] = $list;
                return $ret;
            }
        }

        $list = WPvivid_Setting::get_option('wpvivid_migrate_list');
        foreach ($list as $k=>$backup)
        {
            if ($id == $k)
            {
                $ret['list_name'] = 'wpvivid_migrate_list';
                $ret['list_data'] = $list;
                return $ret;
            }
        }
        return false;
    }

    public function get_backuplist_item($backup,$key){
        $list = WPvivid_Setting::get_option('wpvivid_backup_list');
        foreach ($list as $k=>$backup)
        {
            if ($key == $k)
            {
                return $backup;
            }
        }

        $list = WPvivid_Setting::get_option('wpvivid_staging_list');
        foreach ($list as $k=>$backup)
        {
            if ($key == $k)
            {
                return $backup;
            }
        }

        $list = WPvivid_Setting::get_option('wpvivid_migrate_list');
        foreach ($list as $k=>$backup)
        {
            if ($key == $k)
            {
                return $backup;
            }
        }
        return false;
    }

    public function get_backuplist_name($lists)
    {
        $lists[]='wpvivid_remote_list';
        return $lists;
    }

    public function wpvivid_check_remove_restore_database($check_is_remove, $option){
        if(isset($option['remove_additional_database']) && !empty($option['remove_additional_database'])){
            foreach ($option['remove_additional_database'] as $database_name => $status){
                if($option['database'] === $database_name){
                    $check_is_remove = true;
                }
            }
        }
        return $check_is_remove;
    }

    public function wpvivid_backup_list_addon($json){
        $default = array();
        $remote_list = get_option('wpvivid_remote_list', $default);
        $json['data']['wpvivid_remote_list'] = $remote_list;

        $select_remote_id = get_option('wpvivid_select_list_remote_id', '');
        $json['data']['wpvivid_select_list_remote_id'] = $select_remote_id;
        return $json;
    }

    public function get_backup_data_by_task($backup_data,$task){
        $prefix='';
        if(isset($backup_data['backup']['files']))
        {
            foreach ($backup_data['backup']['files'] as $file)
            {
                if(preg_match('#^.*_wpvivid-#',$file['file_name'],$matches))
                {
                    $prefix=$matches[0];
                    $prefix=substr($prefix,0,strlen($prefix)-strlen('_wpvivid-'));
                    break;
                }
            }
        }

        $backup_data['backup_prefix']=$prefix;
        return $backup_data;
    }
    /***** backup and restore filters end *****/

    /***** backup and restore actions begin *****/
    public function update_backup_item($id,$key,$data){
        $list = WPvivid_Setting::get_option('wpvivid_backup_list');
        if(array_key_exists($id,$list))
        {
            $list[$id][$key]=$data;
            WPvivid_Setting::update_option('wpvivid_backup_list',$list);
        }

        $list = WPvivid_Setting::get_option('wpvivid_staging_list');
        if(array_key_exists($id,$list))
        {
            $list[$id][$key]=$data;
            WPvivid_Setting::update_option('wpvivid_staging_list',$list);
        }

        $list = WPvivid_Setting::get_option('wpvivid_migrate_list');
        if(array_key_exists($id,$list))
        {
            $list[$id][$key]=$data;
            WPvivid_Setting::update_option('wpvivid_migrate_list',$list);
        }
    }
    /***** backup and restore actions end *****/

    /***** backup and restore userfule function begin *****/
    public function wpvivid_tran_backup_time_to_local($value)
    {
        $backup_time=$value['create_time'];
        if(isset($value['backup']['files'])){
            foreach ($value['backup']['files'] as $file_info){
                if(preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}-[0-9]{2}-[0-9]{2}/',$file_info['file_name'],$matches))
                {
                    $backup_date=$matches[0];
                }
                else
                {
                    $backup_date=$value['create_time'];
                }

                $time_array=explode('-',$backup_date);
                if(sizeof($time_array)>4){
                    $time=$time_array[0].'-'.$time_array[1].'-'.$time_array[2].' '.$time_array[3].':'.$time_array[4];
                    $backup_time=strtotime($time);
                }
                break;
            }
        }
        return $backup_time;
    }

    public function get_backup_path($backup_item, $file_name)
    {
        $path = $backup_item->get_local_path() . $file_name;

        if (file_exists($path)) {
            return $path;
        }
        else{
            $local_setting = get_option('wpvivid_local_setting', array());
            if(!empty($local_setting))
            {
                $path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $local_setting['path'] . DIRECTORY_SEPARATOR . $file_name;
            }
            else {
                $path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'wpvividbackups' . DIRECTORY_SEPARATOR . $file_name;
            }
        }
        return $path;
    }

    public function get_backup_url($backup_item, $file_name)
    {
        $path = $backup_item->get_local_path() . $file_name;

        if (file_exists($path)) {
            return $backup_item->get_local_url() . $file_name;
        }
        else{
            $local_setting = get_option('wpvivid_local_setting', array());
            if(!empty($local_setting))
            {
                $url = content_url().DIRECTORY_SEPARATOR.$local_setting['path'].DIRECTORY_SEPARATOR.$file_name;
            }
            else {
                $url = content_url().DIRECTORY_SEPARATOR.'wpvividbackups'.DIRECTORY_SEPARATOR.$file_name;
            }
        }
        return $url;
    }
    /***** backup and restore userfule function end *****/

    /***** backup and restore ajax begin *****/
    public function achieve_local_backup()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security('wpvivid-can-mange-backup');
        try
        {
            if(isset($_POST['folder']) && !empty($_POST['folder']))
            {
                $backup_folder = $_POST['folder'];

                if(isset($_POST['incremental_type'])){
                    $incremental_type = $_POST['incremental_type'];
                }
                else{
                    $incremental_type = 'incremental_file';
                }

                $html='';
                $backuplist=WPvivid_Backuplist::get_backuplist('wpvivid_backup_list');
                if($backup_folder === 'wpvivid')
                {
                    $localbackuplist=array();
                    foreach ($backuplist as $key=>$value)
                    {
                        $value['create_time'] = $this->wpvivid_tran_backup_time_to_local($value);
                        if($value['type'] === 'Rollback' || $value['type'] === 'Incremental' || $value['type'] === 'Cron' || $value['type'] === 'Upload' || $value['type'] === 'Migration')
                        {
                            continue;
                        }
                        else
                        {
                            $localbackuplist[$key]=$value;
                        }
                    }

                    $table=new WPvivid_Backup_List();
                    $table->is_localhost=true;
                    if(isset($_POST['page']))
                    {
                        $table->set_backup_list($localbackuplist,$_POST['page']);
                    }
                    else
                    {
                        $table->set_backup_list($localbackuplist);
                    }

                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                }
                elseif($backup_folder === 'wpvivid_schedule')
                {
                    $localbackuplist=array();
                    foreach ($backuplist as $key=>$value)
                    {
                        $value['create_time'] = $this->wpvivid_tran_backup_time_to_local($value);
                        if($value['type'] === 'Rollback' || $value['type'] === 'Incremental' || $value['type'] === 'Manual' || $value['type'] === 'Upload' || $value['type'] === 'Migration')
                        {
                            continue;
                        }
                        else
                        {
                            $localbackuplist[$key]=$value;
                        }
                    }

                    $table=new WPvivid_Backup_List();
                    $table->is_localhost=true;
                    if(isset($_POST['page']))
                    {
                        $table->set_backup_list($localbackuplist,$_POST['page']);
                    }
                    else
                    {
                        $table->set_backup_list($localbackuplist);
                    }

                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                }
                elseif($backup_folder === 'wpvivid_uploaded')
                {
                    $localbackuplist=array();
                    foreach ($backuplist as $key=>$value)
                    {
                        $value['create_time'] = $this->wpvivid_tran_backup_time_to_local($value);
                        if($value['type'] === 'Rollback' || $value['type'] === 'Incremental' || $value['type'] === 'Manual' || $value['type'] === 'Cron')
                        {
                            continue;
                        }
                        else
                        {
                            $localbackuplist[$key]=$value;
                        }
                    }

                    $table=new WPvivid_Backup_List();
                    $table->is_localhost=true;
                    if(isset($_POST['page']))
                    {
                        $table->set_backup_list($localbackuplist,$_POST['page']);
                    }
                    else
                    {
                        $table->set_backup_list($localbackuplist);
                    }

                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                }
                elseif($backup_folder === 'rollback')
                {
                    $rollbackuplist=array();
                    foreach ($backuplist as $key=>$value)
                    {
                        $value['create_time'] = $this->wpvivid_tran_backup_time_to_local($value);
                        if($value['type'] === 'Rollback')
                        {
                            $rollbackuplist[$key]=$value;
                        }
                    }

                    $table=new WPvivid_Backup_List();
                    $table->is_localhost=true;
                    if(isset($_POST['page']))
                    {
                        $table->set_backup_list($rollbackuplist,$_POST['page']);
                    }
                    else
                    {
                        $table->set_backup_list($rollbackuplist);
                    }
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                }
                elseif($backup_folder === 'incremental'){
                    $incrementallist=array();
                    foreach ($backuplist as $key=>$value){
                        $value['create_time'] = $this->wpvivid_tran_backup_time_to_local($value);
                        if($value['type'] === 'Incremental') {
                            $backup_type = 'incremental_file';
                            if(count($value) > 1){
                                $backup_type = 'incremental_file';
                            }
                            foreach ($value['backup']['files'] as $file){
                                if(WPvivid_backup_pro_function::is_wpvivid_db_backup($file['file_name'])) {
                                    $backup_type = 'incremental_database';
                                }
                            }
                            if($backup_type === $incremental_type){
                                $incrementallist[$key]=$value;
                            }
                        }
                    }

                    $table=new WPvivid_Backup_List();
                    $table->is_localhost=true;
                    if(isset($_POST['page'])) {
                        $table->set_backup_list($incrementallist,$_POST['page'],true);
                    }
                    else {
                        $table->set_backup_list($incrementallist,1,true);
                    }
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                }
                elseif($backup_folder === 'all_backup'){
                    $allbackuplist=array();
                    foreach ($backuplist as $key=>$value)
                    {
                        $value['create_time'] = $this->wpvivid_tran_backup_time_to_local($value);
                        $allbackuplist[$key]=$value;
                    }

                    $table=new WPvivid_Backup_List();
                    $table->is_localhost=true;
                    if(isset($_POST['page']))
                    {
                        $table->set_backup_list($allbackuplist,$_POST['page']);
                    }
                    else
                    {
                        $table->set_backup_list($allbackuplist);
                    }

                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                }
                $ret['result']='success';
                $ret['html']=$html;
                echo json_encode($ret);
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function set_security_lock_ex()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security('wpvivid-can-mange-backup');
        try
        {
            if (isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_string($_POST['backup_id']) && isset($_POST['lock']))
            {
                $backup_id = sanitize_key($_POST['backup_id']);
                if ($_POST['lock'] == 0 || $_POST['lock'] == 1)
                {
                    $lock = $_POST['lock'];
                } else {
                    $lock = 0;
                }

                $backup = WPvivid_Backuplist::get_backuplist_by_id($backup_id);
                if($backup !== false)
                {
                    $list = $backup['list_data'];
                    if (array_key_exists($backup_id, $list))
                    {
                        $ret['result'] = 'success';
                        if ($lock == 1)
                        {
                            $list[$backup_id]['lock'] = 1;
                        }
                        else {
                            if (array_key_exists('lock', $list[$backup_id]))
                            {
                                unset($list[$backup_id]['lock']);
                            }
                        }
                        WPvivid_Setting::update_option($backup['list_name'], $list);
                    }
                    else
                    {
                        $ret['result'] = 'failed';
                        $ret['error']='backup not found';
                    }
                }
                else
                {
                    $ret['result'] = 'failed';
                    $ret['error']='backup not found';
                }

                echo json_encode($ret);
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        die();
    }

    public function delete_local_backup()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security('wpvivid-can-mange-backup');
        try
        {
            if (isset($_POST['backup_id']) && !empty($_POST['backup_id']))
            {
                $backup_id = sanitize_key($_POST['backup_id']);
                $backup=WPvivid_Backuplist::get_backup_by_id($backup_id);
                if(!$backup)
                {
                    $ret['result']='failed';
                    $ret['error']=__('Retrieving the backup(s) information failed while deleting the selected backup(s). Please try again later.', 'wpvivid');
                    echo json_encode($ret);
                    die();
                }
                $backup_item=new WPvivid_Backup_Item($backup);
                $files=$backup_item->get_files();
                foreach ($files as $file)
                {
                    if (file_exists($file))
                    {
                        @unlink($file);
                    }
                }
                WPvivid_Backuplist::delete_backup($backup_id);

                $backup_folder = $_POST['folder'];
                $html='';
                $backuplist=WPvivid_Backuplist::get_backuplist('wpvivid_backup_list');
                if($backup_folder === 'wpvivid')
                {
                    $localbackuplist=array();
                    foreach ($backuplist as $key=>$value)
                    {
                        $value['create_time'] = $this->wpvivid_tran_backup_time_to_local($value);
                        if($value['type'] === 'Rollback' || $value['type'] === 'Incremental' || $value['type'] === 'Cron' || $value['type'] === 'Upload' || $value['type'] === 'Migration')
                        {
                            continue;
                        }
                        else
                        {
                            $localbackuplist[$key]=$value;
                        }
                    }

                    $table=new WPvivid_Backup_List();
                    $table->is_localhost=true;
                    if(isset($_POST['page']))
                    {
                        $table->set_backup_list($localbackuplist,$_POST['page']);
                    }
                    else
                    {
                        $table->set_backup_list($localbackuplist);
                    }

                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                }
                elseif($backup_folder === 'wpvivid_schedule')
                {
                    $localbackuplist=array();
                    foreach ($backuplist as $key=>$value)
                    {
                        $value['create_time'] = $this->wpvivid_tran_backup_time_to_local($value);
                        if($value['type'] === 'Rollback' || $value['type'] === 'Incremental' || $value['type'] === 'Manual' || $value['type'] === 'Upload' || $value['type'] === 'Migration')
                        {
                            continue;
                        }
                        else
                        {
                            $localbackuplist[$key]=$value;
                        }
                    }

                    $table=new WPvivid_Backup_List();
                    $table->is_localhost=true;
                    if(isset($_POST['page']))
                    {
                        $table->set_backup_list($localbackuplist,$_POST['page']);
                    }
                    else
                    {
                        $table->set_backup_list($localbackuplist);
                    }

                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                }
                elseif($backup_folder === 'wpvivid_uploaded')
                {
                    $localbackuplist=array();
                    foreach ($backuplist as $key=>$value)
                    {
                        $value['create_time'] = $this->wpvivid_tran_backup_time_to_local($value);
                        if($value['type'] === 'Rollback' || $value['type'] === 'Incremental' || $value['type'] === 'Manual' || $value['type'] === 'Cron')
                        {
                            continue;
                        }
                        else
                        {
                            $localbackuplist[$key]=$value;
                        }
                    }

                    $table=new WPvivid_Backup_List();
                    $table->is_localhost=true;
                    if(isset($_POST['page']))
                    {
                        $table->set_backup_list($localbackuplist,$_POST['page']);
                    }
                    else
                    {
                        $table->set_backup_list($localbackuplist);
                    }

                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                }
                elseif($backup_folder === 'rollback')
                {
                    $rollbackuplist=array();
                    foreach ($backuplist as $key=>$value)
                    {
                        $value['create_time'] = $this->wpvivid_tran_backup_time_to_local($value);
                        if($value['type'] === 'Rollback')
                        {
                            $rollbackuplist[$key]=$value;
                        }
                    }

                    $table=new WPvivid_Backup_List();
                    $table->is_localhost=true;
                    if(isset($_POST['page']))
                    {
                        $table->set_backup_list($rollbackuplist,$_POST['page']);
                    }
                    else
                    {
                        $table->set_backup_list($rollbackuplist);
                    }
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                }
                elseif($backup_folder === 'incremental')
                {
                    if(isset($_POST['incremental_type'])){
                        $incremental_type = $_POST['incremental_type'];
                    }
                    else{
                        $incremental_type = 'incremental_file';
                    }

                    $incrementallist=array();
                    foreach ($backuplist as $key=>$value){
                        $value['create_time'] = $this->wpvivid_tran_backup_time_to_local($value);
                        if($value['type'] === 'Incremental') {
                            $backup_type = 'incremental_file';
                            if(count($value) > 1){
                                $backup_type = 'incremental_file';
                            }
                            foreach ($value['backup']['files'] as $file){
                                if(WPvivid_backup_pro_function::is_wpvivid_db_backup($file['file_name'])) {
                                    $backup_type = 'incremental_database';
                                }
                            }
                            if($backup_type === $incremental_type){
                                $incrementallist[$key]=$value;
                            }
                        }
                    }

                    /*$rollbackuplist=array();
                    foreach ($backuplist as $key=>$value)
                    {
                        $value['create_time'] = $this->wpvivid_tran_backup_time_to_local($value);
                        if($value['type'] === 'Incremental')
                        {
                            $rollbackuplist[$key]=$value;
                        }
                    }*/

                    $table=new WPvivid_Backup_List();
                    $table->is_localhost=true;
                    if(isset($_POST['page']))
                    {
                        $table->set_backup_list($incrementallist,$_POST['page'],true);
                    }
                    else
                    {
                        $table->set_backup_list($incrementallist,1,true);
                    }
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                }
                elseif($backup_folder === 'all_backup'){
                    $allbackuplist=array();
                    foreach ($backuplist as $key=>$value)
                    {
                        $value['create_time'] = $this->wpvivid_tran_backup_time_to_local($value);
                        $allbackuplist[$key]=$value;
                    }

                    $table=new WPvivid_Backup_List();
                    $table->is_localhost=true;
                    if(isset($_POST['page']))
                    {
                        $table->set_backup_list($allbackuplist,$_POST['page']);
                    }
                    else
                    {
                        $table->set_backup_list($allbackuplist);
                    }

                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                }
                $ret['result']='success';
                $ret['html']=$html;

                echo json_encode($ret);
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function delete_local_backup_array()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security('wpvivid-can-mange-backup');
        try
        {
            if (isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_array($_POST['backup_id']))
            {
                $backup_ids = $_POST['backup_id'];
                $ret = array();
                foreach ($backup_ids as $backup_id)
                {
                    $backup=WPvivid_Backuplist::get_backup_by_id($backup_id);
                    if(!$backup)
                    {
                        continue;
                    }
                    $backup_item=new WPvivid_Backup_Item($backup);
                    $files=$backup_item->get_files();
                    foreach ($files as $file)
                    {
                        if (file_exists($file))
                        {
                            @unlink($file);
                        }
                    }
                    WPvivid_Backuplist::delete_backup($backup_id);
                }
                $backup_folder = $_POST['folder'];
                $html='';
                $backuplist=WPvivid_Backuplist::get_backuplist('wpvivid_backup_list');
                if($backup_folder === 'wpvivid')
                {
                    $localbackuplist=array();
                    foreach ($backuplist as $key=>$value)
                    {
                        $value['create_time'] = $this->wpvivid_tran_backup_time_to_local($value);
                        if($value['type'] === 'Rollback' || $value['type'] === 'Incremental' || $value['type'] === 'Cron' || $value['type'] === 'Upload' || $value['type'] === 'Migration')
                        {
                            continue;
                        }
                        else
                        {
                            $localbackuplist[$key]=$value;
                        }
                    }

                    $table=new WPvivid_Backup_List();
                    $table->is_localhost=true;
                    if(isset($_POST['page']))
                    {
                        $table->set_backup_list($localbackuplist,$_POST['page']);
                    }
                    else
                    {
                        $table->set_backup_list($localbackuplist);
                    }

                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                }
                elseif($backup_folder === 'wpvivid_schedule')
                {
                    $localbackuplist=array();
                    foreach ($backuplist as $key=>$value)
                    {
                        $value['create_time'] = $this->wpvivid_tran_backup_time_to_local($value);
                        if($value['type'] === 'Rollback' || $value['type'] === 'Incremental' || $value['type'] === 'Manual' || $value['type'] === 'Upload' || $value['type'] === 'Migration')
                        {
                            continue;
                        }
                        else
                        {
                            $localbackuplist[$key]=$value;
                        }
                    }

                    $table=new WPvivid_Backup_List();
                    $table->is_localhost=true;
                    if(isset($_POST['page']))
                    {
                        $table->set_backup_list($localbackuplist,$_POST['page']);
                    }
                    else
                    {
                        $table->set_backup_list($localbackuplist);
                    }

                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                }
                elseif($backup_folder === 'wpvivid_uploaded')
                {
                    $localbackuplist=array();
                    foreach ($backuplist as $key=>$value)
                    {
                        $value['create_time'] = $this->wpvivid_tran_backup_time_to_local($value);
                        if($value['type'] === 'Rollback' || $value['type'] === 'Incremental' || $value['type'] === 'Manual' || $value['type'] === 'Cron')
                        {
                            continue;
                        }
                        else
                        {
                            $localbackuplist[$key]=$value;
                        }
                    }

                    $table=new WPvivid_Backup_List();
                    $table->is_localhost=true;
                    if(isset($_POST['page']))
                    {
                        $table->set_backup_list($localbackuplist,$_POST['page']);
                    }
                    else
                    {
                        $table->set_backup_list($localbackuplist);
                    }

                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                }
                elseif($backup_folder === 'rollback')
                {
                    $rollbackuplist=array();
                    foreach ($backuplist as $key=>$value)
                    {
                        $value['create_time'] = $this->wpvivid_tran_backup_time_to_local($value);
                        if($value['type'] === 'Rollback')
                        {
                            $rollbackuplist[$key]=$value;
                        }
                    }

                    $table=new WPvivid_Backup_List();
                    $table->is_localhost=true;
                    if(isset($_POST['page']))
                    {
                        $table->set_backup_list($rollbackuplist,$_POST['page']);
                    }
                    else
                    {
                        $table->set_backup_list($rollbackuplist);
                    }
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                }
                elseif($backup_folder === 'incremental')
                {
                    if(isset($_POST['incremental_type'])){
                        $incremental_type = $_POST['incremental_type'];
                    }
                    else{
                        $incremental_type = 'incremental_file';
                    }

                    $incrementallist=array();
                    foreach ($backuplist as $key=>$value){
                        $value['create_time'] = $this->wpvivid_tran_backup_time_to_local($value);
                        if($value['type'] === 'Incremental') {
                            $backup_type = 'incremental_file';
                            if(count($value) > 1){
                                $backup_type = 'incremental_file';
                            }
                            foreach ($value['backup']['files'] as $file){
                                if(WPvivid_backup_pro_function::is_wpvivid_db_backup($file['file_name'])) {
                                    $backup_type = 'incremental_database';
                                }
                            }
                            if($backup_type === $incremental_type){
                                $incrementallist[$key]=$value;
                            }
                        }
                    }

                    /*$rollbackuplist=array();
                    foreach ($backuplist as $key=>$value)
                    {
                        $value['create_time'] = $this->wpvivid_tran_backup_time_to_local($value);
                        if($value['type'] === 'Incremental')
                        {
                            $rollbackuplist[$key]=$value;
                        }
                    }*/

                    $table=new WPvivid_Backup_List();
                    $table->is_localhost=true;
                    if(isset($_POST['page']))
                    {
                        $table->set_backup_list($incrementallist,$_POST['page'],true);
                    }
                    else
                    {
                        $table->set_backup_list($incrementallist,1,true);
                    }
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                }
                elseif($backup_folder === 'all_backup'){
                    $allbackuplist=array();
                    foreach ($backuplist as $key=>$value)
                    {
                        $value['create_time'] = $this->wpvivid_tran_backup_time_to_local($value);
                        $allbackuplist[$key]=$value;
                    }

                    $table=new WPvivid_Backup_List();
                    $table->is_localhost=true;
                    if(isset($_POST['page']))
                    {
                        $table->set_backup_list($allbackuplist,$_POST['page']);
                    }
                    else
                    {
                        $table->set_backup_list($allbackuplist);
                    }

                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $html = ob_get_clean();
                }
                $ret['result']='success';
                $ret['html']=$html;
                echo json_encode($ret);
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function achieve_remote_backup()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security('wpvivid-can-mange-backup');
        try
        {
            if(isset($_POST['remote_id']) && !empty($_POST['remote_id']) && isset($_POST['folder']) && !empty($_POST['folder']))
            {
                set_time_limit(120);
                $remoteslist = WPvivid_Setting::get_all_remote_options();
                $remote_id = $_POST['remote_id'];
                $remote_folder = $_POST['folder'];

                if (empty($remote_id))
                {
                    $ret['result'] = 'failed';
                    $ret['error'] = 'Failed to post remote stroage id. Please try again.';
                    echo json_encode($ret);
                    die();
                }

                if (empty($remote_folder))
                {
                    $ret['result'] = 'failed';
                    $ret['error'] = 'Failed to post remote storage folder. Please try again.';
                    echo json_encode($ret);
                    die();
                }

                WPvivid_Setting::update_option('wpvivid_select_list_remote_id', $remote_id);
                WPvivid_Setting::update_option('wpvivid_remote_list', array());
                $remote_option = $remoteslist[$remote_id];

                global $wpvivid_plugin;
                $remote = $wpvivid_plugin->remote_collection->get_remote($remote_option);

                if (!method_exists($remote, 'scan_folder_backup'))
                {
                    $ret['result'] = 'failed';
                    $ret['error'] = 'The selected remote storage does not support scanning.';
                    echo json_encode($ret);
                    die();
                }

                if($remote_folder === 'Incremental')
                {
                    $remote_folder = 'Common';
                }

                if(isset($_POST['incremental_path'])&&!empty($_POST['incremental_path']))
                {
                    $incremental_path=$_POST['incremental_path'];
                    $ret = $remote->scan_child_folder_backup($incremental_path);
                }
                else
                {
                    $ret = $remote->scan_folder_backup($remote_folder);
                }

                if ($ret['result'] == WPVIVID_PRO_SUCCESS)
                {
                    global $wpvivid_backup_pro;
                    $wpvivid_backup_pro->func->rescan_remote_folder_set_backup($remote_id, $ret);
                }

                $ret['local_cache_files_size'] = apply_filters('wpvivid_get_local_cache_files_size', 0);


                $list=WPvivid_Backuplist::get_backuplist('wpvivid_remote_list');
                $remote_list=array();

                foreach ($list as $key=>$item)
                {
                    if($item['type']==$remote_folder)
                    {
                        $remote_list[$key]=$item;
                    }
                }

                $table=new WPvivid_Backup_List();
                $table->is_localhost=false;
                if(isset($_POST['page']))
                {
                    if(isset($_POST['incremental_path'])&&!empty($_POST['incremental_path']))
                    {
                        $table->set_backup_list($remote_list,$_POST['page'],true);
                    }
                    else
                    {
                        $table->set_backup_list($remote_list,$_POST['page']);
                    }

                }
                else
                {
                    if(isset($_POST['incremental_path'])&&!empty($_POST['incremental_path']))
                    {
                        $table->set_backup_list($remote_list,1,true);
                    }
                    else
                    {
                        $table->set_backup_list($remote_list,1);
                    }

                }
                $table->prepare_items();
                ob_start();
                $table->display();
                $ret['html'] = ob_get_clean();

                $ret['incremental_list'] = false;
                if(isset($ret['path']) && !empty($ret['path']))
                {
                    $path_list = array();
                    foreach ($ret['path'] as $path) {
                        if (preg_match('/.*_.*_.*_to_.*_.*_.*$/', $path)){
                            $og_path=$path;
                            $path = preg_replace("/_to_.*_.*_.*/", "", $path);
                            $path = preg_replace("/_/", "-", $path);
                            $path = strtotime($path);
                            $temp['og_path']=$og_path;
                            $temp['path']=$path;
                            $path_list[] = $temp;
                        }
                    }

                    uasort ($path_list,function($a, $b) {
                        if($a['path']>$b['path']) {
                            return -1;
                        }
                        else if($a['path']===$b['path']) {
                            return 0;
                        }
                        else {
                            return 1;
                        }
                    });

                    $table = new WPvivid_Incremental_List();
                    $table->set_incremental_list($path_list);
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $ret['incremental_list'] = ob_get_clean();
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function set_remote_security_lock_ex(){
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security('wpvivid-can-mange-backup');

        try
        {
            if (isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_string($_POST['backup_id']) && isset($_POST['lock']))
            {
                $backup_id = sanitize_key($_POST['backup_id']);
                if ($_POST['lock'] == 0 || $_POST['lock'] == 1)
                {
                    $lock = $_POST['lock'];
                } else {
                    $lock = 0;
                }

                $backup_lock=WPvivid_Setting::get_option('wpvivid_remote_backups_lock');

                if($lock)
                {
                    $backup_lock[$backup_id]=1;
                }
                else
                {
                    unset($backup_lock[$backup_id]);
                }

                WPvivid_Setting::update_option('wpvivid_remote_backups_lock',$backup_lock);

                $ret['result'] = 'success';

                echo json_encode($ret);
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function delete_remote_backup()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security('wpvivid-can-mange-backup');
        try
        {
            if (isset($_POST['backup_id']) && !empty($_POST['backup_id']))
            {
                $backup_id = sanitize_key($_POST['backup_id']);
                $backup=WPvivid_Backuplist::get_backup_by_id($backup_id);
                if(!$backup)
                {
                    $ret['result']='failed';
                    $ret['error']=__('Retrieving the backup(s) information failed while deleting the selected backup(s). Please try again later.', 'wpvivid');
                    echo json_encode($ret);
                    die();
                }

                $backup_item=new WPvivid_Backup_Item($backup);

                $files=array();
                if(isset($backup['backup']['files']))
                {
                    //file_name
                    foreach ($backup['backup']['files'] as $file)
                    {
                        $files[]=$file;

                        if (file_exists($this->get_backup_path($backup_item, $file['file_name'])))
                        {
                            @unlink($this->get_backup_path($backup_item, $file['file_name']));
                        }
                    }
                }
                else
                {
                    $files=$backup_item->get_files();
                    foreach ($files as $file)
                    {
                        if (file_exists($file))
                        {
                            @unlink($file);
                        }
                    }
                }

                WPvivid_Backuplist::delete_backup($backup_id);

                if(!empty($backup['remote']))
                {
                    foreach($backup['remote'] as $remote)
                    {
                        WPvivid_downloader::delete($remote,$files);
                    }
                }

                $remote_folder = $_POST['folder'];
                $ret['local_cache_files_size'] = apply_filters('wpvivid_get_local_cache_files_size', 0);
                $list=WPvivid_Backuplist::get_backuplist('wpvivid_remote_list');
                $remote_list=array();

                $is_incremental_folder = false;
                if($remote_folder === 'Incremental'){
                    $is_incremental_folder = true;
                    $remote_folder = 'Common';
                }

                foreach ($list as $key=>$item)
                {
                    if(!$is_incremental_folder){
                        if($item['type']==$remote_folder)
                        {
                            $remote_list[$key]=$item;
                        }
                    }
                    else{
                        if(isset($_POST['incremental_type'])){
                            $incremental_type = $_POST['incremental_type'];
                        }
                        else{
                            $incremental_type = 'incremental_file';
                        }
                        $backup_type = 'incremental_file';
                        if(count($item['backup']['files']) > 1){
                            $backup_type = 'incremental_file';
                        }
                        else{
                            foreach ($item['backup']['files'] as $file){
                                if(WPvivid_backup_pro_function::is_wpvivid_db_backup($file['file_name'])) {
                                    $backup_type = 'incremental_database';
                                }
                            }
                        }
                        if($backup_type === $incremental_type) {
                            $remote_list[$key] = $item;
                            $remote_list[$key]['type'] = 'Incremental';
                        }
                    }
                }

                $table=new WPvivid_Backup_List();
                $table->is_localhost=false;
                if(isset($_POST['page']))
                {
                    if(isset($_POST['incremental_path'])&&!empty($_POST['incremental_path']))
                    {
                        $table->set_backup_list($remote_list,$_POST['page'],true);
                    }
                    else
                    {
                        $table->set_backup_list($remote_list,$_POST['page']);
                    }
                }
                else
                {
                    if(isset($_POST['incremental_path'])&&!empty($_POST['incremental_path']))
                    {
                        $table->set_backup_list($remote_list,1,true);
                    }
                    else
                    {
                        $table->set_backup_list($remote_list,1);
                    }
                }
                $table->prepare_items();
                ob_start();
                $table->display();
                $ret['html'] = ob_get_clean();
                $ret['result']='success';
                echo json_encode($ret);
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function delete_remote_backup_array()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security('wpvivid-can-mange-backup');
        try
        {
            if (isset($_POST['backup_id']) && !empty($_POST['backup_id']))
            {
                $backup_ids = $_POST['backup_id'];
                foreach ($backup_ids as $backup_id)
                {
                    @set_time_limit(45);
                    $backup_id = sanitize_key($backup_id);
                    $backup=WPvivid_Backuplist::get_backup_by_id($backup_id);
                    if(!$backup)
                    {
                        continue;
                    }
                    $backup_item=new WPvivid_Backup_Item($backup);

                    $files=array();
                    if(isset($backup['backup']['files']))
                    {
                        //file_name
                        foreach ($backup['backup']['files'] as $file)
                        {
                            $files[]=$file;

                            if (file_exists($this->get_backup_path($backup_item, $file['file_name'])))
                            {
                                @unlink($this->get_backup_path($backup_item, $file['file_name']));
                            }
                        }
                    }
                    else
                    {
                        $files=$backup_item->get_files();
                        foreach ($files as $file)
                        {
                            if (file_exists($file))
                            {
                                @unlink($file);
                            }
                        }
                    }

                    WPvivid_Backuplist::delete_backup($backup_id);
                    if(!empty($backup['remote']))
                    {
                        foreach($backup['remote'] as $remote)
                        {
                            WPvivid_downloader::delete($remote,$files);
                        }
                    }
                }

                $remote_folder = $_POST['folder'];
                $ret['local_cache_files_size'] = apply_filters('wpvivid_get_local_cache_files_size', 0);
                $list=WPvivid_Backuplist::get_backuplist('wpvivid_remote_list');
                $remote_list=array();

                $is_incremental_folder = false;
                if($remote_folder === 'Incremental'){
                    $is_incremental_folder = true;
                    $remote_folder = 'Common';
                }

                foreach ($list as $key=>$item)
                {
                    if(!$is_incremental_folder){
                        if($item['type']==$remote_folder)
                        {
                            $remote_list[$key]=$item;
                        }
                    }
                    else{
                        if(isset($_POST['incremental_type'])){
                            $incremental_type = $_POST['incremental_type'];
                        }
                        else{
                            $incremental_type = 'incremental_file';
                        }
                        $backup_type = 'incremental_file';
                        if(count($item['backup']['files']) > 1){
                            $backup_type = 'incremental_file';
                        }
                        else{
                            foreach ($item['backup']['files'] as $file){
                                if(WPvivid_backup_pro_function::is_wpvivid_db_backup($file['file_name'])) {
                                    $backup_type = 'incremental_database';
                                }
                            }
                        }
                        if($backup_type === $incremental_type) {
                            $remote_list[$key] = $item;
                            $remote_list[$key]['type'] = 'Incremental';
                        }
                    }
                }

                $table=new WPvivid_Backup_List();
                $table->is_localhost=false;
                if(isset($_POST['page']))
                {
                    if(isset($_POST['incremental_path'])&&!empty($_POST['incremental_path']))
                    {
                        $table->set_backup_list($remote_list,$_POST['page'],true);
                    }
                    else
                    {
                        $table->set_backup_list($remote_list,$_POST['page']);
                    }
                }
                else
                {
                    if(isset($_POST['incremental_path'])&&!empty($_POST['incremental_path']))
                    {
                        $table->set_backup_list($remote_list,1,true);
                    }
                    else
                    {
                        $table->set_backup_list($remote_list,1);
                    }
                }
                $table->prepare_items();
                ob_start();
                $table->display();
                $ret['html'] = ob_get_clean();
                $ret['result']='success';
                echo json_encode($ret);
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function archieve_incremental_remote_folder_list()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security('wpvivid-can-mange-backup');
        try
        {
            if(isset($_POST['remote_id']) && !empty($_POST['remote_id']) && isset($_POST['folder']) && !empty($_POST['folder']))
            {
                set_time_limit(120);
                $remoteslist = WPvivid_Setting::get_all_remote_options();
                $remote_id = $_POST['remote_id'];
                $remote_folder = $_POST['folder'];

                if (empty($remote_id))
                {
                    $ret['result'] = 'failed';
                    $ret['error'] = 'Failed to post remote stroage id. Please try again.';
                    echo json_encode($ret);
                    die();
                }

                if (empty($remote_folder))
                {
                    $ret['result'] = 'failed';
                    $ret['error'] = 'Failed to post remote storage folder. Please try again.';
                    echo json_encode($ret);
                    die();
                }

                WPvivid_Setting::update_option('wpvivid_select_list_remote_id', $remote_id);
                WPvivid_Setting::update_option('wpvivid_remote_list', array());
                $remote_option = $remoteslist[$remote_id];

                global $wpvivid_plugin;
                $remote = $wpvivid_plugin->remote_collection->get_remote($remote_option);

                if (!method_exists($remote, 'scan_folder_backup'))
                {
                    $ret['result'] = 'failed';
                    $ret['error'] = 'The selected remote storage does not support scanning.';
                    echo json_encode($ret);
                    die();
                }

                $ret = $remote->scan_folder_backup($remote_folder);
                if ($ret['result'] == WPVIVID_PRO_SUCCESS)
                {
                    global $wpvivid_backup_pro;
                    $wpvivid_backup_pro->func->rescan_remote_folder_set_backup($remote_id, $ret);
                }

                $ret['local_cache_files_size'] = apply_filters('wpvivid_get_local_cache_files_size', 0);

                $ret['incremental_list'] = false;
                if(isset($ret['path']) && !empty($ret['path'])){
                    $path_list = array();
                    foreach ($ret['path'] as $path) {
                        if (preg_match('/.*_.*_.*_to_.*_.*_.*$/', $path)){
                            $og_path=$path;
                            $path = preg_replace("/_to_.*_.*_.*/", "", $path);
                            $path = preg_replace("/_/", "-", $path);
                            $path = strtotime($path);
                            $temp['og_path']=$og_path;
                            $temp['path']=$path;
                            $path_list[] = $temp;
                        }
                    }

                    uasort ($path_list,function($a, $b) {
                        if($a['path']>$b['path']) {
                            return -1;
                        }
                        else if($a['path']===$b['path']) {
                            return 0;
                        }
                        else {
                            return 1;
                        }
                    });

                    $table = new WPvivid_Incremental_List();
                    if(isset($_POST['page']))
                    {
                        $table->set_incremental_list($path_list,$_POST['page']);
                    }
                    else
                    {
                        $table->set_incremental_list($path_list);
                    }
                    $table->prepare_items();
                    ob_start();
                    $table->display();
                    $ret['incremental_list'] = ob_get_clean();
                }

                echo json_encode($ret);
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function achieve_incremental_child_path()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security('wpvivid-can-mange-backup');
        try
        {
            if(isset($_POST['remote_id']) && !empty($_POST['remote_id']) && isset($_POST['incremental_path']) && !empty($_POST['incremental_path']))
            {
                set_time_limit(120);
                $remoteslist = WPvivid_Setting::get_all_remote_options();
                $remote_id = $_POST['remote_id'];
                $incremental_path = $_POST['incremental_path'];

                if (empty($remote_id))
                {
                    $ret['result'] = 'failed';
                    $ret['error'] = 'Failed to post remote stroage id. Please try again.';
                    echo json_encode($ret);
                    die();
                }

                if (empty($incremental_path))
                {
                    $ret['result'] = 'failed';
                    $ret['error'] = 'Failed to post remote storage incremental path. Please try again.';
                    echo json_encode($ret);
                    die();
                }

                WPvivid_Setting::update_option('wpvivid_select_list_remote_id', $remote_id);
                WPvivid_Setting::update_option('wpvivid_remote_list', array());
                $remote_option = $remoteslist[$remote_id];

                global $wpvivid_plugin;
                $remote = $wpvivid_plugin->remote_collection->get_remote($remote_option);

                if (!method_exists($remote, 'scan_folder_backup'))
                {
                    $ret['result'] = 'failed';
                    $ret['error'] = 'The selected remote storage does not support scanning.';
                    echo json_encode($ret);
                    die();
                }

                $ret = $remote->scan_child_folder_backup($incremental_path);

                if ($ret['result'] == WPVIVID_PRO_SUCCESS)
                {
                    global $wpvivid_backup_pro;
                    $wpvivid_backup_pro->func->rescan_remote_folder_set_backup($remote_id, $ret);
                }

                $ret['local_cache_files_size'] = apply_filters('wpvivid_get_local_cache_files_size', 0);

                if(isset($_POST['incremental_type'])){
                    $incremental_type = $_POST['incremental_type'];
                }
                else{
                    $incremental_type = 'incremental_file';
                }

                $list=WPvivid_Backuplist::get_backuplist('wpvivid_remote_list');
                $remote_list=array();

                foreach ($list as $key=>$item)
                {
                    if($item['type']=='Common')
                    {
                        $backup_type = 'incremental_file';
                        if(count($item['backup']['files']) > 1){
                            $backup_type = 'incremental_file';
                        }
                        else{
                            foreach ($item['backup']['files'] as $file){
                                if(WPvivid_backup_pro_function::is_wpvivid_db_backup($file['file_name'])) {
                                    $backup_type = 'incremental_database';
                                }
                            }
                        }
                        if($backup_type === $incremental_type) {
                            $remote_list[$key] = $item;
                            $remote_list[$key]['type'] = 'Incremental';
                        }
                    }
                }

                $table=new WPvivid_Backup_List();
                $table->is_localhost=false;
                if(isset($_POST['page']))
                {
                    $table->set_backup_list($remote_list,$_POST['page'],true);
                }
                else
                {
                    $table->set_backup_list($remote_list,1,true);
                }
                $table->prepare_items();
                ob_start();
                $table->display(true);
                $ret['html'] = ob_get_clean();

                echo json_encode($ret);
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }

        die();
    }

    public function init_download_page_ex()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security('wpvivid-can-mange-backup');
        try {
            if (isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_string($_POST['backup_id'])) {
                $backup_id = sanitize_key($_POST['backup_id']);
                $backup = WPvivid_Backuplist::get_backup_by_id($backup_id);
                if ($backup === false) {
                    $ret['result'] = WPVIVID_PRO_FAILED;
                    $ret['error'] = 'backup id not found';
                    echo json_encode($ret);
                    die();
                }

                $backup_item = new WPvivid_Backup_Item($backup);

                $backup_files = $backup_item->get_download_backup_files($backup_id);
                if ($backup_files['result'] == WPVIVID_PRO_SUCCESS) {
                    $ret['result'] = WPVIVID_PRO_SUCCESS;

                    $remote = $backup_item->get_remote();

                    foreach ($backup_files['files'] as $file) {
                        $path = $this->get_backup_path($backup_item, $file['file_name']);
                        //$path = $backup_item->get_local_path() . $file['file_name'];
                        if (file_exists($path)) {
                            if (filesize($path) == $file['size']) {
                                if (WPvivid_taskmanager::get_download_task_v2($file['file_name']))
                                    WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                                $ret['files'][$file['file_name']]['status'] = 'completed';
                                $ret['files'][$file['file_name']]['size'] = size_format(filesize($path), 2);
                                $ret['files'][$file['file_name']]['download_path'] = $path;
                                $download_url = $this->get_backup_url($backup_item, $file['file_name']);
                                $ret['files'][$file['file_name']]['download_url'] = $download_url;

                                continue;
                            }
                        }

                        $ret['files'][$file['file_name']]['size'] = size_format($file['size'], 2);

                        if (empty($remote)) {
                            $ret['files'][$file['file_name']]['status'] = 'file_not_found';
                        } else {
                            $task = WPvivid_taskmanager::get_download_task_v2($file['file_name']);
                            if ($task === false) {
                                $ret['files'][$file['file_name']]['status'] = 'need_download';
                            } else {
                                $ret['result'] = WPVIVID_PRO_SUCCESS;
                                if ($task['status'] === 'running') {
                                    $ret['files'][$file['file_name']]['status'] = 'running';
                                    $ret['files'][$file['file_name']]['progress_text'] = $task['progress_text'];
                                    if (file_exists($path)) {
                                        $ret['files'][$file['file_name']]['downloaded_size'] = size_format(filesize($path), 2);
                                    } else {
                                        $ret['files'][$file['file_name']]['downloaded_size'] = '0';
                                    }
                                } elseif ($task['status'] === 'timeout') {
                                    $ret['files'][$file['file_name']]['status'] = 'timeout';
                                    $ret['files'][$file['file_name']]['progress_text'] = $task['progress_text'];
                                    WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                                } elseif ($task['status'] === 'completed') {
                                    $ret['files'][$file['file_name']]['status'] = 'completed';
                                    WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                                } elseif ($task['status'] === 'error') {
                                    $ret['files'][$file['file_name']]['status'] = 'error';
                                    $ret['files'][$file['file_name']]['error'] = $task['error'];
                                    WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                                }
                            }
                        }
                    }
                } else {
                    $ret = $backup_files;
                }
                if(isset($_POST['backup_type']) && $_POST['backup_type'] === 'Incremental'){
                    if(count($ret['files']) === 1){
                        foreach ($ret['files'] as $file_name => $file_info){
                            if(WPvivid_backup_pro_function::is_wpvivid_db_backup($file_name)){
                                $html = '';
                                $create_time = '';
                                if(isset($_POST['backup_time'])) {
                                    $create_time = '<p><span class="dashicons dashicons-arrow-right wpvivid-dashicons-grey"></span><span><strong>Creation Date:</strong></span><span>'.$_POST['backup_time'].'</span>';
                                }
                                $download_btn = '<span class="wpvivid-ready-download-incremental-db" slug="'.$file_name.'" style="cursor: pointer;"><a href="#" style="cursor: pointer;">Download</a></span>';
                                if($file_info['status']=='completed'){
                                    $download_btn='<span class="wpvivid-ready-download-incremental-db" slug="'.$file_name.'" style="cursor: pointer;"><a href="#" style="cursor: pointer;">Download</a></span>';
                                }
                                else if($file_info['status']=='file_not_found'){
                                    $download_btn='<span slug="'.$file_name.'"><a href="#" style="cursor: pointer;">File not found</a></span>';
                                }
                                else if($file_info['status']=='need_download') {
                                    $download_btn='<span class="wpvivid-download-incremental-db" slug="'.$file_name.'" style="cursor: pointer;"><a href="#" style="cursor: pointer;">Prepare to Download</a></span>';
                                }
                                else if($file_info['status']=='running'){
                                    $download_btn='<span class="wpvivid-download-incremental-db" slug="'.$file_name.'" style="pointer-events: none; opacity: 0.4;"><a href="#" style="cursor: pointer;">Prepare to Download</a></span>';
                                }
                                else if($file_info['status']=='timeout'){
                                    $download_btn='<span class="wpvivid-download-incremental-db" slug="'.$file_name.'" style="cursor: pointer;"><a href="#" style="cursor: pointer;">Prepare to Download</a></span>';
                                }
                                else if($file_info['status']=='error'){
                                    $download_btn='<span class="wpvivid-download-incremental-db" slug="'.$file_name.'" style="cursor: pointer;"><a href="#" style="cursor: pointer;">Prepare to Download</a></span>';
                                }

                                $html = '<div>
										    <h2><span class="dashicons dashicons-list-view wpvivid-dashicons-blue"></span>
										    <span>Download database from the <code>Database Backup</code></span></h2>	
										</div>
										<div class="wpvivid-one-coloum wpvivid-workflow wpvivid-clear-float" style="margin-bottom:1em;">
											<p><strong>The Database Backup Details:</strong>
											'.$create_time.'
											<p><span class="dashicons dashicons-arrow-right wpvivid-dashicons-grey"></span><span><strong>Type:</strong></span><span>Full Backup</span>
											<p><span class="dashicons dashicons-arrow-right wpvivid-dashicons-grey"></span><span><strong>Content:</strong></span><span>Database</span>
											<p><span class="dashicons dashicons-arrow-right wpvivid-dashicons-grey"></span><span><strong>Comment:</strong></span><span>'.$_POST['backup_comment'].'</span>
											<p><span class="dashicons dashicons-arrow-right wpvivid-dashicons-grey"></span><span><strong>Size:</strong></span><span>'.$file_info['size'].'</span><span> | </span>'.$download_btn.'
										</div>';
                                $ret['html'] = $html;
                            }
                            else{
                                $html = '';
                                $imcremental_file_list = new WPvivid_Incremental_Files_Download_List();
                                $imcremental_file_list->set_files_list($ret['files']);
                                $imcremental_file_list->prepare_items();
                                ob_start();
                                if(isset($_POST['backup_time'])){
                                    $html='<div id="wpvivid_download_backup_info">
                                        <h2>
                                            <span class="dashicons dashicons-portfolio wpvivid-dashicons-orange"></span>
                                            <span>Download backups from the <code id="wpvivid_download_backup_type">Incremental Backup Cycle</code> created on <span id="wpvivid_download_backup_time">'.$_POST['backup_time'].'</span></span>
                                        </h2>
                                    </div>';
                                }

                                $imcremental_file_list->display();
                                $ret['html'] = ob_get_clean();
                                $ret['html'] = $html.$ret['html'];
                            }
                        }
                    }
                    else{
                        $html = '';
                        $imcremental_file_list = new WPvivid_Incremental_Files_Download_List();
                        $imcremental_file_list->set_files_list($ret['files']);
                        $imcremental_file_list->prepare_items();
                        ob_start();
                        if(isset($_POST['backup_time'])){
                            $html='<div id="wpvivid_download_backup_info">
                                        <h2>
                                            <span class="dashicons dashicons-portfolio wpvivid-dashicons-orange"></span>
                                            <span>Download backups from the <code id="wpvivid_download_backup_type">Incremental Backup Cycle</code> created on <span id="wpvivid_download_backup_time">'.$_POST['backup_time'].'</span></span>
                                        </h2>
                                    </div>';
                        }

                        $imcremental_file_list->display();
                        $ret['html'] = ob_get_clean();
                        $ret['html'] = $html.$ret['html'];
                    }
                    $ret['backup_type'] = 'incremental';
                }
                else{
                    $files_list = new WPvivid_Files_List_Ex();

                    $files_list->set_files_list($ret['files'], $backup_id);
                    $files_list->prepare_items();
                    ob_start();
                    $files_list->display();
                    $ret['html'] = ob_get_clean();
                    $ret['backup_type'] = 'general';
                }


                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function get_download_page_ex()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security('wpvivid-can-mange-backup');
        try {
            if (isset($_POST['backup_id']) && !empty($_POST['backup_id']) && is_string($_POST['backup_id'])) {
                if (isset($_POST['page'])) {
                    $page = $_POST['page'];
                } else {
                    $page = 1;
                }

                $backup_id = sanitize_key($_POST['backup_id']);
                $backup = WPvivid_Backuplist::get_backup_by_id($backup_id);
                if ($backup === false) {
                    $ret['result'] = WPVIVID_PRO_FAILED;
                    $ret['error'] = 'backup id not found';
                    echo json_encode($ret);
                    die();
                }

                $backup_item = new WPvivid_Backup_Item($backup);

                $backup_files = $backup_item->get_download_backup_files($backup_id);

                if ($backup_files['result'] == WPVIVID_PRO_SUCCESS) {
                    $ret['result'] = WPVIVID_PRO_SUCCESS;

                    $remote = $backup_item->get_remote();

                    foreach ($backup_files['files'] as $file) {
                        $path = $this->get_backup_path($backup_item, $file['file_name']);
                        //$path = $backup_item->get_local_path() . $file['file_name'];
                        if (file_exists($path)) {
                            if (filesize($path) == $file['size']) {
                                if (WPvivid_taskmanager::get_download_task_v2($file['file_name']))
                                    WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                                $ret['files'][$file['file_name']]['status'] = 'completed';
                                $ret['files'][$file['file_name']]['size'] = size_format(filesize($path), 2);
                                $ret['files'][$file['file_name']]['download_path'] = $path;
                                $download_url = $this->get_backup_url($backup_item, $file['file_name']);
                                $ret['files'][$file['file_name']]['download_url'] = $download_url;

                                continue;
                            }
                        }
                        $ret['files'][$file['file_name']]['size'] = size_format($file['size'], 2);

                        if (empty($remote)) {
                            $ret['files'][$file['file_name']]['status'] = 'file_not_found';
                        } else {
                            $task = WPvivid_taskmanager::get_download_task_v2($file['file_name']);
                            if ($task === false) {
                                $ret['files'][$file['file_name']]['status'] = 'need_download';
                            } else {
                                $ret['result'] = WPVIVID_PRO_SUCCESS;
                                if ($task['status'] === 'running') {
                                    $ret['files'][$file['file_name']]['status'] = 'running';
                                    $ret['files'][$file['file_name']]['progress_text'] = $task['progress_text'];
                                    if (file_exists($path)) {
                                        $ret['files'][$file['file_name']]['downloaded_size'] = size_format(filesize($path), 2);
                                    } else {
                                        $ret['files'][$file['file_name']]['downloaded_size'] = '0';
                                    }
                                } elseif ($task['status'] === 'timeout') {
                                    $ret['files'][$file['file_name']]['status'] = 'timeout';
                                    $ret['files'][$file['file_name']]['progress_text'] = $task['progress_text'];
                                    WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                                } elseif ($task['status'] === 'completed') {
                                    $ret['files'][$file['file_name']]['status'] = 'completed';
                                    WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                                } elseif ($task['status'] === 'error') {
                                    $ret['files'][$file['file_name']]['status'] = 'error';
                                    $ret['files'][$file['file_name']]['error'] = $task['error'];
                                    WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                                }
                            }
                        }
                    }
                } else {
                    $ret = $backup_files;
                }

                if (isset($_POST['backup_type'])) {
                    $backup_type = $_POST['backup_type'];
                } else {
                    $backup_type = 'general';
                }

                if($backup_type === 'general')
                {
                    $files_list = new WPvivid_Files_List_Ex();
                    $files_list->set_files_list($ret['files'], $backup_id, $page);
                }
                else
                {
                    $files_list = new WPvivid_Incremental_Files_Download_List();
                    $files_list->set_files_list($ret['files'], $page);
                }


                $files_list->prepare_items();
                ob_start();
                $files_list->display();
                $ret['html'] = ob_get_clean();

                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function get_download_progress(){
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security('wpvivid-can-mange-backup');
        try {
            if (isset($_POST['backup_id'])) {
                $backup_id = sanitize_key($_POST['backup_id']);
                $ret['result'] = WPVIVID_PRO_SUCCESS;
                $ret['files'] = array();
                $ret['need_update'] = false;

                $backup = WPvivid_Backuplist::get_backup_by_id($backup_id);
                if ($backup === false) {
                    $ret['result'] = WPVIVID_PRO_FAILED;
                    $ret['error'] = 'backup id not found';
                    return $ret;
                }

                $backup_item = new WPvivid_Backup_Item($backup);

                $backup_files = $backup_item->get_download_backup_files($backup_id);

                foreach ($backup_files['files'] as $file) {
                    $path = $this->get_backup_path($backup_item, $file['file_name']);
                    //$path = $backup_item->get_local_path() . $file['file_name'];
                    if (file_exists($path)) {
                        $downloaded_size = size_format(filesize($path), 2);
                    } else {
                        $downloaded_size = '0';
                    }
                    $file['size'] = size_format($file['size'], 2);

                    $task = WPvivid_taskmanager::get_download_task_v2($file['file_name']);
                    if ($task === false) {
                        $ret['files'][$file['file_name']]['status'] = 'need_download';
                        $ret['files'][$file['file_name']]['html'] = '<div class="wpvivid-element-space-bottom">
                                                                        <span class="wpvivid-element-space-right">Retriving (remote storage to web server)</span><span class="wpvivid-element-space-right">|</span><span>File Size: </span><span class="wpvivid-element-space-right">' . $file['size'] . '</span><span class="wpvivid-element-space-right">|</span><span>Downloaded Size: </span><span>0</span>
                                                                   </div>
                                                                   <div style="width:100%;height:10px; background-color:#dcdcdc;">
                                                                        <div style="background-color:#0085ba; float:left;width:0%;height:10px;"></div>
                                                                   </div>';
                        $ret['need_update'] = true;
                    } else {
                        if ($task['status'] === 'running') {
                            $ret['files'][$file['file_name']]['status'] = 'running';
                            $ret['files'][$file['file_name']]['progress_text'] = $task['progress_text'];
                            $ret['files'][$file['file_name']]['html'] = '<div class="wpvivid-element-space-bottom">
                                                                            <span class="wpvivid-element-space-right">Retriving (remote storage to web server)</span><span class="wpvivid-element-space-right">|</span><span>File Size: </span><span class="wpvivid-element-space-right">' . $file['size'] . '</span><span class="wpvivid-element-space-right">|</span><span>Downloaded Size: </span><span>' . $downloaded_size . '</span>
                                                                        </div>
                                                                        <div style="width:100%;height:10px; background-color:#dcdcdc;">
                                                                            <div style="background-color:#0085ba; float:left;width:' . $task['progress_text'] . '%;height:10px;"></div>
                                                                        </div>';
                            $ret['need_update'] = true;
                        } elseif ($task['status'] === 'timeout') {
                            $ret['files'][$file['file_name']]['status'] = 'timeout';
                            $ret['files'][$file['file_name']]['progress_text'] = $task['progress_text'];
                            $ret['files'][$file['file_name']]['html'] = '<div class="wpvivid-element-space-bottom">
                                                                            <span>Download timeout, please retry.</span>
                                                                         </div>
                                                                         <div>
                                                                            <span>' . __('File Size: ', 'wpvivid') . '</span><span class="wpvivid-element-space-right">' . $file['size'] . '</span><span class="wpvivid-element-space-right">|</span><span class="wpvivid-element-space-right"><a class="wpvivid-download" style="cursor: pointer;">Prepare to Download</a></span>
                                                                        </div>';
                            WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                        } elseif ($task['status'] === 'completed') {
                            $ret['files'][$file['file_name']]['status'] = 'completed';
                            $ret['files'][$file['file_name']]['html'] = '<span>' . __('File Size: ', 'wpvivid') . '</span><span class="wpvivid-element-space-right">' . $file['size'] . '</span><span class="wpvivid-element-space-right">|</span><span class="wpvivid-element-space-right wpvivid-ready-download"><a style="cursor: pointer;">Download</a></span>';
                            WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                        } elseif ($task['status'] === 'error') {
                            $ret['files'][$file['file_name']]['status'] = 'error';
                            $ret['files'][$file['file_name']]['error'] = $task['error'];
                            $ret['files'][$file['file_name']]['html'] = '<div class="wpvivid-element-space-bottom">
                                                                            <span>' . $task['error'] . '</span>
                                                                         </div>
                                                                         <div>
                                                                            <span>' . __('File Size: ', 'wpvivid') . '</span><span class="wpvivid-element-space-right">' . $file['size'] . '</span><span class="wpvivid-element-space-right">|</span><span class="wpvivid-element-space-right"><a class="wpvivid-download" style="cursor: pointer;">Prepare to Download</a></span>
                                                                         </div>';
                            WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                        }
                    }
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function get_download_incremental_progress(){
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security('wpvivid-can-mange-backup');
        try{
            if (isset($_POST['backup_id']) && isset($_POST['type'])) {
                $backup_id = sanitize_key($_POST['backup_id']);
                $type = sanitize_key($_POST['type']);
                $ret['result'] = WPVIVID_PRO_SUCCESS;
                $ret['files'] = array();
                $ret['need_update'] = false;

                $backup = WPvivid_Backuplist::get_backup_by_id($backup_id);
                if ($backup === false) {
                    $ret['result'] = WPVIVID_PRO_FAILED;
                    $ret['error'] = 'backup id not found';
                    return $ret;
                }

                $backup_item = new WPvivid_Backup_Item($backup);

                $backup_files = $backup_item->get_download_backup_files($backup_id);
                foreach ($backup_files['files'] as $file) {
                    $path = $this->get_backup_path($backup_item, $file['file_name']);
                    //$path = $backup_item->get_local_path() . $file['file_name'];
                    if (file_exists($path)) {
                        $downloaded_size = size_format(filesize($path), 2);
                    } else {
                        $downloaded_size = '0';
                    }
                    $file['size'] = size_format($file['size'], 2);

                    $task = WPvivid_taskmanager::get_download_task_v2($file['file_name']);
                    if ($task === false) {
                        $ret['files'][$file['file_name']]['status'] = 'need_download';
                        if($type === 'file'){
                            $ret['files'][$file['file_name']]['html'] = '<td colspan="4">
                                                                             <p>
                                                                                 <span class="wpvivid-span-progress">
                                                                                    <span class="wpvivid-span-processed-progress" style="width: 0;"></span>
                                                                                 </span>
                                                                             </p>
                                                                             <p>
                                                                                 <span class="wpvivid-download-progress-text" style="font-size: 10px;"><i>Downloading backup from remote storage to web server, 0% completed.</i></span>
                                                                             </p>
                                                                         </td>';
                        }
                        else{
                            $ret['files'][$file['file_name']]['html'] = '
                                                                         <span class="dashicons dashicons-arrow-right wpvivid-dashicons-grey"></span>
                                                                         <span><code>Downloading backup from remote storage to web server, 0% completed.</code></span>
                                                                         ';
                        }
                        $ret['need_update'] = true;
                    } else {
                        if ($task['status'] === 'running') {
                            $ret['files'][$file['file_name']]['status'] = 'running';
                            $ret['files'][$file['file_name']]['progress_text'] = $task['progress_text'];
                            if (filter_var($task['progress_text'], FILTER_VALIDATE_INT) === false) {
                                $progress = 0;
                            }
                            else {
                                $progress = $task['progress_text'];
                            }
                            if($type === 'file') {
                                $ret['files'][$file['file_name']]['html'] = '<td colspan="4">
                                                                                 <p>
                                                                                     <span class="wpvivid-span-progress">
                                                                                        <span class="wpvivid-span-processed-progress" style="width: ' . $progress . '%"></span>
                                                                                     </span>
                                                                                 </p>
                                                                                 <p>
                                                                                    <span class="wpvivid-download-progress-text" style="font-size: 10px;"><i>Downloading backup from remote storage to web server, '.$progress.'% completed.</i></span>
                                                                                 </p>
                                                                             </td>';
                            }
                            else{
                                $ret['files'][$file['file_name']]['html'] = '
                                                                             <span class="dashicons dashicons-arrow-right wpvivid-dashicons-grey"></span>
                                                                             <span><code>Downloading backup from remote storage to web server, '.$progress.'% completed.</code></span>
                                                                             ';
                            }
                            $ret['need_update'] = true;
                        } elseif ($task['status'] === 'timeout') {
                            $ret['files'][$file['file_name']]['status'] = 'timeout';
                            $ret['files'][$file['file_name']]['progress_text'] = $task['progress_text'];
                            if($type === 'file') {
                                $ret['files'][$file['file_name']]['html'] = '<td colspan="4">
                                                                                 <p><span>Download timeout, please retry.</span></p>
                                                                             </td>';
                            }
                            else{
                                $ret['files'][$file['file_name']]['html'] = '
                                                                             <span class="dashicons dashicons-arrow-right wpvivid-dashicons-grey"></span>
                                                                             <span><code>Download timeout, please retry.</code></span>
                                                                             ';
                            }
                            WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                        } elseif ($task['status'] === 'completed') {
                            $ret['files'][$file['file_name']]['status'] = 'completed';
                            if($type === 'file') {
                                $ret['files'][$file['file_name']]['html'] = '<td colspan="4">
                                                                                 <p>
                                                                                     <span class="wpvivid-span-progress">
                                                                                        <span class="wpvivid-span-processed-progress" style="width: 100%;"></span>
                                                                                     </span>
                                                                                 </p>
                                                                                 <p>
                                                                                    <span class="wpvivid-download-progress-text" style="font-size: 10px;"><i>Download completed.</i></span>
                                                                                 </p>
                                                                             </td>';
                            }
                            else{
                                $ret['files'][$file['file_name']]['html'] = '
                                                                             <span class="dashicons dashicons-arrow-right wpvivid-dashicons-grey"></span>
                                                                             <span><code>Downloading backup from remote storage to web server, 100% completed.</code></span>
                                                                             ';
                            }
                            WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                        } elseif ($task['status'] === 'error') {
                            $ret['files'][$file['file_name']]['status'] = 'error';
                            $ret['files'][$file['file_name']]['error'] = $task['error'];
                            if($type === 'file') {
                                $ret['files'][$file['file_name']]['html'] = '<td colspan="4">
                                                                                 <p><span>' . $task['error'] . '</span></p>
                                                                             </td>';
                            }
                            else{
                                $ret['files'][$file['file_name']]['html'] = '
                                                                             <span class="dashicons dashicons-arrow-right wpvivid-dashicons-grey"></span>
                                                                             <span><code>'.$task['error'].'</code></span>
                                                                             ';
                            }
                            WPvivid_taskmanager::delete_download_task_v2($file['file_name']);
                        }
                    }
                }
                echo json_encode($ret);
            }
        }
        catch (Exception $error) {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }

    public function download_backup_ex(){
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security('wpvivid-can-mange-backup');
        try
        {
            if (isset($_REQUEST['backup_id']) && isset($_REQUEST['file_name']))
            {
                $backup_id = sanitize_key($_REQUEST['backup_id']);
                $file_name = $_REQUEST['file_name'];
                $backup=WPvivid_Backuplist::get_backup_by_id($backup_id);
                if($backup===false)
                {
                    $ret['result']=WPVIVID_PRO_FAILED;
                    $ret['error']='backup id not found';
                    echo json_encode($ret);
                    die();
                }

                $backup_item=new WPvivid_Backup_Item($backup);
                //$path=$backup_item->get_local_path().$file_name;
                $path = $this->get_backup_path($backup_item, $file_name);
                if ($path !== false)
                {
                    if (file_exists($path))
                    {
                        if (session_id())
                            session_write_close();

                        $size = filesize($path);
                        if (!headers_sent())
                        {
                            header('Content-Description: File Transfer');
                            header('Content-Type: application/zip');
                            header('Content-Disposition: attachment; filename="' . basename($path) . '"');
                            header('Cache-Control: must-revalidate');
                            header('Content-Length: ' . $size);
                            header('Content-Transfer-Encoding: binary');
                        }

                        /*$memory_limit = @ini_get('memory_limit');
                        $unit = strtoupper(substr($memory_limit, -1));
                        if ($unit == 'K')
                        {
                            $memory_limit_tmp = intval($memory_limit) * 1024;
                        }
                        else if ($unit == 'M')
                        {
                            $memory_limit_tmp = intval($memory_limit) * 1024 * 1024;
                        }
                        else if ($unit == 'G')
                        {
                            $memory_limit_tmp = intval($memory_limit) * 1024 * 1024 * 1024;
                        }
                        else{
                            $memory_limit_tmp = intval($memory_limit);
                        }
                        if ($memory_limit_tmp < 256 * 1024 * 1024)
                        {
                            @ini_set('memory_limit', '256M');
                        }*/

                        @ini_set( 'memory_limit', '1024M' );

                        if ($size < 1024 * 1024 * 60) {
                            ob_end_clean();
                            readfile($path);
                            exit;
                        } else {
                            ob_end_clean();
                            $download_rate = 1024 * 10;
                            $file = fopen($path, "r");
                            while (!feof($file)) {
                                @set_time_limit(20);
                                // send the current file part to the browser
                                print fread($file, round($download_rate * 1024));
                                // flush the content to the browser
                                ob_flush();
                                flush();
                                // sleep one second
                                sleep(1);
                            }
                            fclose($file);
                            exit;
                        }
                    }
                }
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        $admin_url = apply_filters('wpvivid_get_admin_url', '');
        echo __('file not found. please <a href="'.$admin_url.'admin.php?page='.apply_filters('wpvivid_white_label_slug', WPVIVID_PRO_PLUGIN_SLUG).'">retry</a> again.');
        die();
    }

    public function download_all_backup_ex()
    {
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security('wpvivid-can-mange-backup');
        try
        {
            if (isset($_REQUEST['backup_id']))
            {
                $backup_id = sanitize_key($_REQUEST['backup_id']);
                $backup=WPvivid_Backuplist::get_backup_by_id($backup_id);
                if($backup===false)
                {
                    $ret['result']=WPVIVID_PRO_FAILED;
                    $ret['error']='backup id not found';
                    echo json_encode($ret);
                    die();
                }

                @set_time_limit(300);
                $backup_file_array=array();
                foreach($backup['backup']['files'] as $files)
                {
                    $backup_file_array[]=WP_CONTENT_DIR.DIRECTORY_SEPARATOR.WPvivid_Setting::get_backupdir().DIRECTORY_SEPARATOR.$files['file_name'];
                }

                $zip_file_name=WP_CONTENT_DIR.DIRECTORY_SEPARATOR.WPvivid_Setting::get_backupdir().DIRECTORY_SEPARATOR.'wpvivid-all-backups.zip';

                if(file_exists($zip_file_name))
                    @unlink($zip_file_name);

                $use_temp_size=16;
                $replace_path=WP_CONTENT_DIR.DIRECTORY_SEPARATOR.WPvivid_Setting::get_backupdir();
                if (!class_exists('WPvivid_PclZip'))
                    include_once WPVIVID_BACKUP_PRO_PLUGIN_DIR . 'includes/zip/class-wpvivid-pclzip.php';
                if (!class_exists('WPvivid_PclZip'))
                {
                    $ret['result']=WPVIVID_PRO_FAILED;
                    $ret['error']='Class PclZip is not detected. Please update or reinstall your WordPress.';
                    echo json_encode($ret);
                    die();
                }
                $archive = new WPvivid_PclZip($zip_file_name);
                $ret = $archive -> add($backup_file_array,WPVIVID_PCLZIP_OPT_REMOVE_PATH,$replace_path,WPVIVID_PCLZIP_CB_PRE_ADD,'wpvivid_function_per_add_callback',WPVIVID_PCLZIP_OPT_NO_COMPRESSION,WPVIVID_PCLZIP_OPT_TEMP_FILE_THRESHOLD,$use_temp_size);
                if(!$ret)
                {
                    $ret['result']=WPVIVID_PRO_FAILED;
                    $ret['error']=$archive->errorInfo(true);
                    echo json_encode($ret);
                    die();
                }
                $size=filesize($zip_file_name);
                if($size===false)
                {
                    $size=size_format(disk_free_space(dirname($zip_file_name)),2);
                    $ret['result']=WPVIVID_PRO_FAILED;
                    $ret['error']='The file compression failed while backing up becuase of '.$zip_file_name.' file not found. Please try again. The available disk space: '.$size.'.';
                    echo json_encode($ret);
                    die();
                }
                else if($size==0)
                {
                    $size=size_format(disk_free_space(dirname($zip_file_name)),2);
                    $ret['result']=WPVIVID_PRO_FAILED;
                    $ret['error']='The file compression failed while backing up. The size of '.$zip_file_name.' file is 0. Please make sure there is an enough disk space to backup. Then try again. The available disk space: '.$size.'.';
                    echo json_encode($ret);
                    die();
                }

                $path=$zip_file_name;
                if (file_exists($path))
                {
                    if (session_id())
                        session_write_close();

                    $size = filesize($path);
                    if (!headers_sent())
                    {
                        header('Content-Description: File Transfer');
                        header('Content-Type: application/zip');
                        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
                        header('Cache-Control: must-revalidate');
                        header('Content-Length: ' . $size);
                        header('Content-Transfer-Encoding: binary');
                    }

                    $memory_limit = @ini_get('memory_limit');
                    $unit = strtoupper(substr($memory_limit, -1));
                    if ($unit == 'K')
                    {
                        $memory_limit_tmp = intval($memory_limit) * 1024;
                    }
                    else if ($unit == 'M')
                    {
                        $memory_limit_tmp = intval($memory_limit) * 1024 * 1024;
                    }
                    else if ($unit == 'G')
                    {
                        $memory_limit_tmp = intval($memory_limit) * 1024 * 1024 * 1024;
                    }
                    else{
                        $memory_limit_tmp = intval($memory_limit);
                    }
                    if ($memory_limit_tmp < 256 * 1024 * 1024)
                    {
                        @ini_set('memory_limit', '256M');
                    }

                    if ($size < 1024 * 1024 * 60) {
                        ob_end_clean();
                        readfile($path);
                        exit;
                    } else {
                        ob_end_clean();
                        $download_rate = 1024 * 10;
                        $file = fopen($path, "r");
                        while (!feof($file)) {
                            @set_time_limit(20);
                            // send the current file part to the browser
                            print fread($file, round($download_rate * 1024));
                            // flush the content to the browser
                            ob_flush();
                            flush();
                            // sleep one second
                            sleep(1);
                        }
                        fclose($file);
                        exit;
                    }
                }
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
            die();
        }
        $admin_url = apply_filters('wpvivid_get_admin_url', '');
        echo __('file not found. please <a href="'.$admin_url.'admin.php?page='.apply_filters('wpvivid_white_label_slug', WPVIVID_PRO_PLUGIN_SLUG).'">retry</a> again.');
        die();
    }

    function wpvivid_function_per_add_callback($p_event, &$p_header)
    {
        if(!file_exists($p_header['filename'])){
            return 0;
        }
        return 1;
    }

    public function backup_content_display(){
        global $wpvivid_backup_pro;
        $wpvivid_backup_pro->ajax_check_security('wpvivid-can-mange-backup');
        try
        {
            if(isset($_POST['backup_content_list']) && !empty($_POST['backup_content_list'])){
                $backup_content_list = $_POST['backup_content_list'];
                $content_list=new WPvivid_Content_List();
                $content_list->set_files_list($backup_content_list);
                $content_list->prepare_items();
                ob_start();
                $content_list->display();
                $ret['html'] = ob_get_clean();
                $ret['result'] = 'success';
                echo json_encode($ret);
            }
        }
        catch (Exception $error)
        {
            $message = 'An exception has occurred. class: '.get_class($error).';msg: '.$error->getMessage().';code: '.$error->getCode().';line: '.$error->getLine().';in_file: '.$error->getFile().';';
            error_log($message);
            echo json_encode(array('result'=>'failed','error'=>$message));
        }
        die();
    }
    /***** backup and restore ajax end *****/

    public function init_page()
    {
        if(isset($_REQUEST['restore']))
        {
            do_action('wpvivid_output_restore_page');
            return;
        }
        ?>
        <div class="wrap wpvivid-canvas">
            <div id="icon-options-general" class="icon32"></div>
            <h1><?php esc_attr_e( apply_filters('wpvivid_white_label_display', 'WPvivid').' Plugins - Backups & Restoration', 'wpvivid' ); ?></h1>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <!-- main content -->
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <div class="wpvivid-backup">
                                <div class="wpvivid-welcome-bar wpvivid-clear-float">
                                    <div class="wpvivid-welcome-bar-left">
                                        <p><span class="dashicons dashicons-update-alt wpvivid-dashicons-large wpvivid-dashicons-green"></span><span class="wpvivid-page-title">Backup Manager & Restoration</span></p>
                                        <span class="about-description">The page allows you to browse and manage all your backups, upload backups and restore the website from backups.</span>
                                    </div>
                                    <div class="wpvivid-welcome-bar-right">
                                        <p></p>
                                        <div style="float:right;">
                                            <span>Local Time:</span>
                                            <span>
                                                <a href="<?php esc_attr_e(apply_filters('wpvivid_get_admin_url', '').'options-general.php'); ?>">
                                                    <?php
                                                    $offset=get_option('gmt_offset');
                                                    echo date("l, F-d-Y H:i",time()+$offset*60*60);
                                                    ?>
                                                </a>
                                            </span>
                                            <span class="dashicons dashicons-editor-help wpvivid-dashicons-editor-help wpvivid-tooltip">
                                                <div class="wpvivid-left">
                                                    <!-- The content you need -->
                                                    <p>Clicking the date and time will redirect you to the WordPress General Settings page where you can change your timezone settings.</p>
                                                    <i></i> <!-- do not delete this line -->
                                                </div>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="wpvivid-nav-bar wpvivid-clear-float">
                                        <span class="dashicons dashicons-lightbulb wpvivid-dashicons-orange"></span>
                                        <span> Please <strong>do not</strong> change the backup file name, otherwise, the plugin will <strong>be unable to</strong> recognize the backup to perform restore or migration.</span>
                                    </div>
                                </div>

                                <div class="wpvivid-canvas wpvivid-clear-float">
                                    <?php
                                    if(!class_exists('WPvivid_Tab_Page_Container_Ex'))
                                        include_once WPVIVID_BACKUP_PRO_PLUGIN_DIR . 'includes/class-wpvivid-tab-page-container-ex.php';
                                    $this->main_tab=new WPvivid_Tab_Page_Container_Ex();

                                    $first_tab=false;
                                    $args['span_class']='dashicons dashicons-admin-home wpvivid-dashicons-orange';
                                    $args['span_style']='color:orange; padding-right:0.5em;margin-top:0.1em;';
                                    $args['div_style']='padding-top:0;display:block;';
                                    $args['is_parent_tab']=0;

                                    if( apply_filters('wpvivid_current_user_can',true,'wpvivid-can-mange-local-backup') && apply_filters('wpvivid_current_user_can',true,'wpvivid-can-mange-backup') )
                                    {
                                        $first_tab=true;
                                        $tabs['localhost_backuplist']['title']='Localhost';
                                        $tabs['localhost_backuplist']['slug']='localhost_backuplist';
                                        $tabs['localhost_backuplist']['callback']=array($this, 'output_localhost_backuplist');
                                        $tabs['localhost_backuplist']['args']=$args;
                                    }

                                    $args['span_class']='dashicons dashicons-cloud wpvivid-dashicons-blue';
                                    $args['span_style']='padding-right:0.5em;margin-top:0.1em;';
                                    $args['div_style']='padding-top:0;';
                                    $args['is_parent_tab']=0;

                                    if( apply_filters('wpvivid_current_user_can',true,'wpvivid-can-mange-remote-backup') && apply_filters('wpvivid_current_user_can',true,'wpvivid-can-mange-backup') )
                                    {
                                        if(!$first_tab)
                                        {
                                            $args['div_style']='padding-top:0;display:block;';
                                        }
                                        $tabs['remote_backuplist']['title']='Remote Storage';
                                        $tabs['remote_backuplist']['slug']='remote_backuplist';
                                        $tabs['remote_backuplist']['callback']=array($this, 'output_remote_backuplist');
                                        $tabs['remote_backuplist']['args']=$args;
                                    }

                                    $args['div_style']='padding-top:0;';
                                    $args['span_class']='dashicons dashicons-upload wpvivid-dashicons-green';

                                    if( apply_filters('wpvivid_current_user_can',true,'wpvivid-can-mange-local-backup') && apply_filters('wpvivid_current_user_can',true,'wpvivid-can-mange-backup') )
                                    {
                                        $tabs['upload_backup']['title']='Upload';
                                        $tabs['upload_backup']['slug']='upload_backup';
                                        $tabs['upload_backup']['callback']=array($this, 'output_upload_backup');
                                        $tabs['upload_backup']['args']=$args;
                                    }

                                    $args['span_class']='dashicons dashicons-welcome-write-blog wpvivid-dashicons-grey';
                                    $tabs['backup_log_list']['title']='Logs';
                                    $tabs['backup_log_list']['slug']='backup_log_list';
                                    $tabs['backup_log_list']['callback']=array($this, 'output_backup_log_list');
                                    $tabs['backup_log_list']['args']=$args;

                                    $args['span_class']='dashicons dashicons-arrow-down-alt wpvivid-dashicons-grey';
                                    $args['can_delete']=1;
                                    $args['hide']=1;
                                    $tabs['download_backup']['title']='Download';
                                    $tabs['download_backup']['slug']='download_backup';
                                    $tabs['download_backup']['callback']=array($this, 'output_download_backup');
                                    $tabs['download_backup']['args']=$args;

                                    $args['span_class']='dashicons dashicons-image-rotate wpvivid-dashicons-grey';
                                    $args['can_delete']=1;
                                    $args['hide']=1;
                                    $tabs['restore_backup']['title']='Restore';
                                    $tabs['restore_backup']['slug']='restore_backup';
                                    $tabs['restore_backup']['callback']=array($this, 'output_restore_backup');
                                    $tabs['restore_backup']['args']=$args;

                                    $args['span_class']='';
                                    $args['span_style']='';
                                    $args['can_delete']=1;
                                    $args['hide']=1;
                                    $tabs['view_detail']['title']='Content';
                                    $tabs['view_detail']['slug']='view_detail';
                                    $tabs['view_detail']['callback']=array($this, 'output_view_detail');
                                    $tabs['view_detail']['args']=$args;

                                    $args['span_class']='';
                                    $args['span_style']='';
                                    $args['can_delete']=1;
                                    $args['hide']=1;
                                    $tabs['open_log']['title']='Logs';
                                    $tabs['open_log']['slug']='open_log';
                                    $tabs['open_log']['callback']=array($this, 'output_open_log');
                                    $tabs['open_log']['args']=$args;

                                    foreach ($tabs as $key=>$tab)
                                    {
                                        $this->main_tab->add_tab($tab['title'],$tab['slug'],$tab['callback'], $tab['args']);
                                    }

                                    $this->main_tab->display();
                                    ?>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- sidebar -->
                    <?php
                    do_action( 'wpvivid_backup_pro_add_sidebar' );
                    ?>

                </div>
            </div>
        </div>
        <script>
            jQuery(document).ready(function($) {
                <?php
                if(isset($_REQUEST['localhost_backuplist']))
                {
                ?>
                jQuery( document ).trigger( '<?php echo $this->main_tab->container_id ?>-show',[ 'localhost_backuplist', 'localhost_backuplist' ]);
                <?php
                }
                ?>

                <?php
                if(isset($_REQUEST['remote_backuplist']))
                {
                ?>
                jQuery( document ).trigger( '<?php echo $this->main_tab->container_id ?>-show',[ 'remote_backuplist', 'remote_backuplist' ]);
                <?php
                }
                ?>

                <?php
                if(isset($_REQUEST['localhost_allbackuplist']))
                {
                ?>
                jQuery( document ).trigger( '<?php echo $this->main_tab->container_id ?>-show',[ 'localhost_backuplist', 'localhost_backuplist' ]);
                jQuery( document ).trigger( 'wpvivid_update_local_all_backup');
                <?php
                }
                ?>

                <?php
                if(isset($_REQUEST['log']))
                {
                $log=$_REQUEST['log'];
                ?>
                jQuery( document ).trigger( '<?php echo $this->main_tab->container_id ?>-show',[ 'backup_log_list', 'backup_log_list' ]);
                wpvivid_open_log("<?php echo $log ?>","backup_log_list");
                <?php
                }
                ?>

                <?php
                if(isset($_REQUEST['local_restore']))
                {
                    ?>
                    var restoredata = '<?php echo $_REQUEST['restoredata']; ?>';
                    <?php
                }
                ?>

                <?php
                if(isset($_REQUEST['remote_restore']))
                {
                    ?>
                    var restoredata = '<?php echo $_REQUEST['restoredata']; ?>';
                    <?php
                }
                else
                {
                    ?>
                    wpvivid_get_remote_backup_folder();
                    <?php
                }
                ?>
            });
        </script>
        <?php
    }

    public function output_localhost_backuplist()
    {
        $backupdir=WPvivid_Setting::get_backupdir();
        ?>
        <div class="wpvivid-one-coloum wpvivid-workflow wpvivid-clear-float" style="margin-bottom:1em;">
            <span class="dashicons dashicons-portfolio wpvivid-dashicons-orange"></span>
            <span>Backup Directory:</span><span><code><?php _e(WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$backupdir); ?></code></span>
            <span> | </span>
            <span><a href="<?php echo apply_filters('wpvivid_white_label_page_redirect', 'admin.php?page=wpvivid-setting', 'wpvivid-setting'); ?>">rename</a></span>
        </div>

        <div class="wpvivid-two-col" style="float:left; margin-bottom:1em;">
            <div>
                <span style="float: left; padding: 4px 4px 0 0;">Display</span>
                <span style="float: left;">
                    <select id="wpvivid_select_local_backup_folder" onchange="wpvivid_select_local_folder();">
                        <option value="all_backup" selected="selected">All Backups</option>
                        <option value="wpvivid">Manual Backups</option>
                        <option value="wpvivid_schedule">Scheduled Backups</option>
                        <option value="wpvivid_uploaded">Uploaded Backups</option>
                        <option value="rollback">Rollback backups</option>
                        <option value="incremental">Incremental</option>
                    </select>
                </span>
                <span style="float: left;">
                    <select id="wpvivid_select_local_incremental_backup" onchange="wpvivid_get_local_incremental_backup();" style="display: none;">
                        <option value="incremental_file" selected="selected">file backups</option>
                        <option value="incremental_database">database backups</option>
                    </select>
                </span>
                <span class="spinner is-active" id="wpvivid_local_backup_scaning" style="float: left; display: none;"></span>
                <span class="dashicons dashicons-editor-help wpvivid-dashicons-editor-help wpvivid-tooltip wpvivid-tooltip-padding-top">
                    <div class="wpvivid-bottom">
                        <!-- The content you need -->
                        <p>Choose the backups you want to browse.</p>
                        <i></i> <!-- do not delete this line -->
                    </div>
                </span>
                <div style="clear: both;"></div>
            </div>
        </div>

        <div class="wpvivid-two-col" style="margin-bottom:1em;">
            <div class="wpvivid-float-right">
                <input type="submit" class="button-primary" id="wpvivid_rescan_local_folder_btn" value="Scan uploaded backup or received backup" onclick="wpvivid_rescan_local_folder();" />
                <span class="dashicons dashicons-editor-help wpvivid-dashicons-editor-help wpvivid-tooltip wpvivid-tooltip-padding-top">
                    <div class="wpvivid-bottom">
                        <!-- The content you need -->
                        <p>Generally you won't need this function, unless you upload or receive backups from a remote site to perform restoration or migration.</p>
                        <i></i> <!-- do not delete this line -->
                    </div>
                </span>
            </div>
        </div>
        <div style="clear: both;"></div>

        <div class="wpvivid-local-remote-backup-list wpvivid-element-space-bottom" id="wpvivid_backup_list">
            <?php
            $backuplist=WPvivid_Backuplist::get_backuplist('wpvivid_backup_list');
            $rollbackuplist=array();
            foreach ($backuplist as $key=>$value)
            {
                $value['create_time'] = $this->wpvivid_tran_backup_time_to_local($value);
                //if($value['type'] !== 'Rollback' && $value['type'] !== 'Incremental' && $value['type'] !== 'Cron' && $value['type'] !== 'Upload')
                //{
                    $rollbackuplist[$key]=$value;
                //}
            }
            $table=new WPvivid_Backup_List();
            $table->is_localhost=true;
            $table->set_backup_list($rollbackuplist);
            $table->prepare_items();
            $table->display();
            ?>
        </div>

        <div>
            <input class="button-primary" id="wpvivid-delete-localhost-array" type="submit" value="Delete the selected backups">
        </div>

        <script>
            jQuery('#wpvivid_backup_list').on("click",'.first-page',function() {
                wpvivid_get_local_backup_folder('first');
            });

            jQuery('#wpvivid_backup_list').on("click",'.prev-page',function() {
                var page=parseInt(jQuery(this).attr('value'));
                wpvivid_get_local_backup_folder(page-1);
            });

            jQuery('#wpvivid_backup_list').on("click",'.next-page',function() {
                var page=parseInt(jQuery(this).attr('value'));
                wpvivid_get_local_backup_folder(page+1);
            });

            jQuery('#wpvivid_backup_list').on("click",'.last-page',function() {
                wpvivid_get_local_backup_folder('last');
            });

            jQuery('#wpvivid_backup_list').on("keypress", '.current-page', function(){
                if(event.keyCode === 13){
                    var page = jQuery(this).val();
                    wpvivid_get_local_backup_folder(page);
                }
            });

            function wpvivid_select_local_folder(){
                var value = jQuery('#wpvivid_select_local_backup_folder').val();
                if(value === 'rollback' || value === 'incremental')
                {
                    if(value === 'incremental'){
                        jQuery('#wpvivid_select_local_incremental_backup').show();
                    }
                    else{
                        jQuery('#wpvivid_select_local_incremental_backup').hide();
                    }
                    jQuery('#wpvivid_scan_local_backup').hide();
                }
                else
                {
                    jQuery('#wpvivid_select_local_incremental_backup').hide();
                    jQuery('#wpvivid_scan_local_backup').show();
                }
                wpvivid_get_local_backup_folder();
            }

            function wpvivid_get_local_incremental_backup(){
                wpvivid_get_local_backup_folder();
            }

            function wpvivid_get_local_backup_folder(page=0) {
                if(page==0)
                {
                    page =jQuery('#wpvivid_backup_list').find('.current-page').val();
                }

                var value = jQuery('#wpvivid_select_local_backup_folder').val();
                if(value === 'rollback' || value === 'incremental')
                {
                    if(value === 'incremental'){
                        jQuery('#wpvivid_select_local_incremental_backup').show();
                    }
                    else{
                        jQuery('#wpvivid_select_local_incremental_backup').hide();
                    }
                    jQuery('#wpvivid_scan_local_backup').hide();
                }
                else
                {
                    jQuery('#wpvivid_select_local_incremental_backup').hide();
                    jQuery('#wpvivid_scan_local_backup').show();
                }

                var incremental_type = jQuery('#wpvivid_select_local_incremental_backup').val();
                jQuery('#wpvivid_local_backup_scaning').show();
                var ajax_data = {
                    'action': 'wpvivid_achieve_local_backup',
                    'folder': value,
                    'incremental_type': incremental_type,
                    'page':page
                };
                wpvivid_post_request_addon(ajax_data, function (data)
                {
                    jQuery('#wpvivid_local_backup_scaning').hide();
                    jQuery('#wpvivid_backup_list').html('');
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            jQuery('#wpvivid_backup_list').html(jsonarray.html);
                        }
                        else
                        {
                            alert(jsonarray.error);
                        }
                    }
                    catch (err)
                    {
                        alert(err);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    jQuery('#wpvivid_local_backup_scaning').hide();
                    var error_message = wpvivid_output_ajaxerror('achieving backup', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            jQuery('#wpvivid_backup_list').on('click', '.wpvivid-lock', function(){
                var Obj=jQuery(this);
                var backup_id=Obj.closest('tr').attr('id');
                if(Obj.hasClass('dashicons-lock'))
                {
                    var lock=0;
                }
                else
                {
                    var lock=1;
                }
                var ajax_data= {
                    'action': 'wpvivid_set_security_lock_ex',
                    'backup_id': backup_id,
                    'lock': lock
                };
                wpvivid_post_request_addon(ajax_data, function(data)
                {
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            if(lock)
                            {
                                Obj.removeClass('dashicons-unlock');
                                Obj.addClass('dashicons-lock');
                            }
                            else
                            {
                                Obj.removeClass('dashicons-lock');
                                Obj.addClass('dashicons-unlock');
                            }
                        }
                    }
                    catch(err){
                        alert(err);
                    }
                }, function(XMLHttpRequest, textStatus, errorThrown)
                {
                    var error_message = wpvivid_output_ajaxerror('setting up a lock for the backup', textStatus, errorThrown);
                    alert(error_message);
                });
            });

            jQuery('#wpvivid_backup_list').on("click",'.backuplist-delete-backup',function() {
                var Obj=jQuery(this);
                var backup_id=Obj.closest('tr').attr('id');
                var page =jQuery('#wpvivid_backup_list').find('.current-page').val();
                var value = jQuery('#wpvivid_select_local_backup_folder').val();
                var descript = '<?php _e('Are you sure to remove this backup? This backup will be deleted permanently.', 'wpvivid'); ?>';

                var ret = confirm(descript);
                if(ret === true)
                {
                    var incremental_type = jQuery('#wpvivid_select_local_incremental_backup').val();
                    var ajax_data = {
                        'action': 'wpvivid_delete_local_backup',
                        'backup_id': backup_id,
                        'folder': value,
                        'incremental_type': incremental_type,
                        'page':page
                    };
                    wpvivid_post_request_addon(ajax_data, function(data)
                    {
                        try
                        {
                            var jsonarray = jQuery.parseJSON(data);
                            if (jsonarray.result === 'success')
                            {
                                jQuery('#wpvivid_backup_list').html(jsonarray.html);
                            }
                            else if(jsonarray.result === 'failed')
                            {
                                alert(jsonarray.error);
                            }
                        }
                        catch(err){
                            alert(err);
                        }

                    }, function(XMLHttpRequest, textStatus, errorThrown) {
                        var error_message = wpvivid_output_ajaxerror('deleting the backup', textStatus, errorThrown);
                        alert(error_message);
                    });
                }
            });

            jQuery('#wpvivid-delete-localhost-array').click(function() {
                var delete_backup_array = new Array();
                var count = 0;

                var page =jQuery('#wpvivid_backup_list').find('.current-page').val();
                var folder = jQuery('#wpvivid_select_local_backup_folder').val();
                jQuery('#wpvivid_backup_list .wpvivid-backup-row input').each(function (i)
                {
                    if(jQuery(this).prop('checked'))
                    {
                        delete_backup_array[count] =jQuery(this).closest('tr').attr('id');
                        count++;
                    }
                });
                if( count === 0 )
                {
                    alert('<?php _e('Please select at least one item.','wpvivid'); ?>');
                }
                else
                {
                    var descript = '<?php _e('Are you sure to remove the selected backups? These backups will be deleted permanently.', 'wpvivid'); ?>';

                    var ret = confirm(descript);
                    if (ret === true)
                    {
                        var incremental_type = jQuery('#wpvivid_select_local_incremental_backup').val();
                        var ajax_data = {
                            'action': 'wpvivid_delete_local_backup_array',
                            'backup_id': delete_backup_array,
                            'folder': folder,
                            'incremental_type': incremental_type,
                            'page':page
                        };

                        wpvivid_post_request_addon(ajax_data, function (data)
                        {
                            try
                            {
                                var jsonarray = jQuery.parseJSON(data);
                                if (jsonarray.result === 'success')
                                {
                                    jQuery('#wpvivid_backup_list').html(jsonarray.html);
                                }
                                else if(jsonarray.result === 'failed')
                                {
                                    alert(jsonarray.error);
                                }
                            }
                            catch(err){
                                alert(err);
                            }
                        }, function (XMLHttpRequest, textStatus, errorThrown) {
                            var error_message = wpvivid_output_ajaxerror('deleting the backup', textStatus, errorThrown);
                            alert(error_message);
                        });
                    }
                }
            });

            function wpvivid_rescan_local_folder() {
                var ajax_data = {
                    'action': 'wpvivid_addon_rescan_local_folder'
                };
                jQuery('#wpvivid_rescan_local_folder_btn').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#wpvivid_scanning_local_folder').addClass('is-active');
                wpvivid_post_request_addon(ajax_data, function (data)
                {
                    jQuery('#wpvivid_rescan_local_folder_btn').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#wpvivid_scanning_local_folder').removeClass('is-active');
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        if(typeof jsonarray.incomplete_backup !== 'undefined' && jsonarray.incomplete_backup.length > 0)
                        {
                            var incomplete_count = jsonarray.incomplete_backup.length;
                            alert('Failed to scan '+incomplete_count+' backup zips, the zips can be corrupted during creation or download process. Please check the zips.');
                        }
                        jQuery( document ).trigger( 'wpvivid_update_local_upload_backup');
                    }
                    catch(err) {
                        alert(err);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    jQuery('#wpvivid_rescan_local_folder_btn').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#wpvivid_scanning_local_folder').removeClass('is-active');
                    var error_message = wpvivid_output_ajaxerror('scanning backup list', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            function wpvivid_backup_open_log(log) {
                location.href='<?php echo apply_filters('wpvivid_white_label_page_redirect', 'admin.php?page=wpvivid-backup-and-restore', 'wpvivid-backup-and-restore').'&log='; ?>'+log;
            }

            function click_dismiss_restore_check_notice(obj){
                wpvivid_display_restore_check = false;
                jQuery(obj).parent().remove();
                jQuery('#wpvivid_restore_check').hide();
            }

            function wpvivid_control_backup_select(obj, backup_list_name)
            {
                if(jQuery(obj).prop('checked'))
                {
                    jQuery('#'+backup_list_name+' tbody tr th input').each(function()
                    {
                        jQuery(this).prop('checked', true);
                    });

                    jQuery('#'+backup_list_name+' tbody tr').each(function()
                    {
                        jQuery(this).children('th').each(function (j)
                        {
                            if (j == 0) {
                                if(jQuery(this).parent().children('td').eq(0).find('.common-table').find('span').eq(0).hasClass('dashicons-unlock'))
                                {
                                    jQuery(this).closest('tr').find('th input').prop('checked', true);
                                }
                                else
                                {
                                    jQuery(this).closest('tr').find('th input').prop('checked', false);
                                }
                            }
                        });
                    });
                }
                else
                {
                    jQuery('#'+backup_list_name+' tbody tr').each(function ()
                    {
                        jQuery(this).children('th').each(function (j)
                        {
                            if (j == 0)
                            {
                                jQuery(this).find("input[type=checkbox]").prop('checked', false);
                            }
                        });
                    });
                }
            }

            jQuery('#wpvivid_backup_list').on('click', 'thead tr td input', function()
            {
                wpvivid_control_backup_select(jQuery(this), 'wpvivid_backup_list');
            });

            jQuery('#wpvivid_backup_list').on('click', 'tfoot tr td input', function()
            {
                wpvivid_control_backup_select(jQuery(this), 'wpvivid_backup_list');
            });

            jQuery(document).ready(function($) {
                jQuery(document).on('wpvivid_update_local_backup', function(event)
                {
                    jQuery('#wpvivid_select_local_backup_folder').val('wpvivid');
                    wpvivid_get_local_backup_folder();
                });

                jQuery(document).on('wpvivid_update_local_upload_backup', function(event)
                {
                    jQuery('#wpvivid_select_local_backup_folder').val('wpvivid_uploaded');
                    wpvivid_get_local_backup_folder();
                });

                jQuery(document).on('wpvivid_update_local_all_backup', function(event)
                {
                    jQuery('#wpvivid_select_local_backup_folder').val('all_backup');
                    wpvivid_get_local_backup_folder();
                });
            });
        </script>
        <?php
    }

    public function output_remote_backuplist()
    {
        $remoteslist=WPvivid_Setting::get_all_remote_options();
        $has_remote = false;
        foreach ($remoteslist as $key => $value)
        {
            if($key === 'remote_selected')
            {
                continue;
            }
            else{
                $has_remote = true;
            }
        }

        $select_remote_id=get_option('wpvivid_select_list_remote_id', '');
        $path = '';
        if($select_remote_id==''){
            $first_remote_path = 'Common';
            foreach ($remoteslist as $key=>$value)
            {
                if($key === 'remote_selected')
                {
                    continue;
                }
                if(isset($value['custom_path']))
                {
                    if(isset($value['root_path'])){
                        $path = trailingslashit($value['path']).trailingslashit($value['root_path']).$value['custom_path'];
                    }
                    else{
                        $path = trailingslashit($value['path']).'wpvividbackuppro/'.$value['custom_path'];
                    }
                }
                else
                {
                    $path = $value['path'];
                }
                if($first_remote_path === 'Common'){
                    $first_remote_path = $path;
                }
            }
            $path = $first_remote_path;
        }
        else{
            if (isset($remoteslist[$select_remote_id]))
            {
                if(isset($remoteslist[$select_remote_id]['custom_path']))
                {
                    if(isset($remoteslist[$select_remote_id]['root_path'])){
                        $path = trailingslashit($remoteslist[$select_remote_id]['path']).trailingslashit($remoteslist[$select_remote_id]['root_path']). $remoteslist[$select_remote_id]['custom_path'];
                    }
                    else{
                        $path = trailingslashit($remoteslist[$select_remote_id]['path']).'wpvividbackuppro/'. $remoteslist[$select_remote_id]['custom_path'];
                    }
                }
                else
                {
                    $path = $remoteslist[$select_remote_id]['path'];
                }
            }
            else {
                $path='Common';
            }
        }
        $remote_storage_option = '';
        foreach ($remoteslist as $key=>$value)
        {
            if($key === 'remote_selected')
            {
                continue;
            }
            $value['type']=apply_filters('wpvivid_storage_provider_tran', $value['type']);
            $remote_storage_option.='<option value="'.$key.'">'.$value['type'].' -> '.$value['name'].'</option>';
        }

        if($has_remote){
            $default = array();
            $remote_array = apply_filters('wpvivid_archieve_remote_array', $default);
            ?>
            <div class="wpvivid-one-coloum wpvivid-workflow wpvivid-clear-float" style="margin-bottom:1em;">
                <span class="dashicons dashicons-portfolio wpvivid-dashicons-orange"></span>
                <span>Cloud Storage Directory:</span><span><code id="wpvivid_remote_folder"><?php _e($path); ?></code></span>
            </div>
            <div style="margin-bottom:1em;">
                <span style="float: left;">
                    <select id="wpvivid_select_remote_storage" onchange="wpvivid_select_remote_storage_folder();">
                        <?php _e($remote_storage_option); ?>
                    </select>
                </span>
                <span style="float: left;">
                    <select id="wpvivid_select_remote_folder" onchange="wpvivid_select_remote_storage_folder();">
                        <option value="Common"><?php _e($path); ?></option>
                        <option value="Migrate">Migration</option>
                        <option value="Rollback">Rollback</option>
                        <option value="Incremental">Incremental</option>
                    </select>
                </span>
                <span style="float: left;">
                    <select id="wpvivid_select_remote_incremental_backup" style="display: none;">
                        <option value="incremental_file" selected="selected">File backups</option>
                        <option value="incremental_database">Database backups</option>
                    </select>
                </span>
                <span style="float: left;"><input class="button-primary" id="wpvivid_sync_remote_folder" type="submit" value="Scan" onclick="wpvivid_select_remote_folder();" style="pointer-events: auto; opacity: 1;"></span>
                <span class="spinner is-active" id="wpvivid_remote_backup_scaning" style="float: left; display: none;"></span>
                <span class="dashicons dashicons-editor-help wpvivid-dashicons-editor-help wpvivid-tooltip wpvivid-tooltip-padding-top">
                    <div class="wpvivid-bottom">
                        <!-- The content you need -->
                        <p>Choose remote storage to display backups you want to browse.</p>
                        <i></i> <!-- do not delete this line -->
                    </div>
                </span>
                <div style="clear: both;"></div>
            </div>
            <div class="wpvivid-two-col" style="margin-bottom:1em;">
                <div class="wpvivid-float-right"></div>
            </div>
            <div style="clear: both;"></div>

            <div class="wpvivid-local-remote-backup-list wpvivid-element-space-bottom" id="wpvivid_incremental_path_list"></div>
            <div class="wpvivid-local-remote-backup-list wpvivid-element-space-bottom" id="wpvivid_remote_backups_list">
                <?php
                $remoteslist=WPvivid_Setting::get_all_remote_options();
                $has_remote = false;
                foreach ($remoteslist as $key => $value)
                {
                    if($key === 'remote_selected')
                    {
                        continue;
                    }
                    else {
                        $has_remote = true;
                    }
                }
                $remote_backup_list=array();
                if($has_remote)
                {
                    $list=WPvivid_Backuplist::get_backuplist('wpvivid_remote_list');
                    foreach ($list as $key=>$item)
                    {
                        if($item['type']=='Common')
                        {
                            $remote_backup_list[$key]=$item;
                        }
                    }
                }

                $table=new WPvivid_Backup_List();
                $table->is_localhost=false;
                $table->set_backup_list($remote_backup_list);
                $table->prepare_items();
                $table->display();
                ?>
            </div>

            <div>
                <input class="button-primary" id="wpvivid-delete-remote-array" type="submit" value="Delete the selected backups">
            </div>
            <?php
        }
        else{
            ?>
            <div class="quickstart-storage-setting" style="margin-bottom: 10px;">
                <div style="padding: 10px 0;">
                    <span style="margin-right: 0;">There is no remote storage available, please </span>
                    <a href="<?php echo 'admin.php?page='.strtolower(sprintf('%s-remote', apply_filters('wpvivid_white_label_slug', 'wpvivid')));?>" style="cursor: pointer;">connect to</a> <span>at least one first.</span>
                </div>
            </div>
            <?php
        }
        ?>
        <script>
            var remote_list_array = {};
            var m_remote_folder='';
            var m_incremental_remote_folder = '';
            function wpvivid_select_remote_storage_folder(){
                var value = jQuery('#wpvivid_select_remote_folder').val();
                var remote_id = jQuery('#wpvivid_select_remote_storage').val();
                var common_folder = '';
                var rollback_folder = '';
                var incremental_folder = '';
                jQuery.each(remote_list_array, function(index, value){
                    if(remote_id === index){
                        common_folder = value.path;
                        rollback_folder = common_folder + "/rollback";
                        incremental_folder = common_folder + "/incremental";
                    }
                });
                jQuery('option[value=Common]').text(common_folder);
                jQuery('#wpvivid_incremental_path_list').hide();
                jQuery('#wpvivid_select_remote_incremental_backup').hide();
                if(value === 'Common'){
                    jQuery('#wpvivid_remote_folder').html(common_folder);
                }
                else if(value === 'Migrate'){
                    jQuery('#wpvivid_remote_folder').html('migrate');
                }
                else if(value === 'Rollback'){
                    jQuery('#wpvivid_remote_folder').html(rollback_folder);
                }
                else if(value === 'Incremental'){
                    jQuery('#wpvivid_remote_folder').html(incremental_folder);
                    jQuery('#wpvivid_select_remote_incremental_backup').show();
                }
            }

            jQuery('#wpvivid_remote_backups_list').on("click",'.first-page',function() {
                if(m_remote_folder === 'Incremental'){
                    wpvivid_get_remote_incremental_backup('first');
                }
                else{
                    wpvivid_get_remote_backup_folder('first');
                }
            });

            jQuery('#wpvivid_remote_backups_list').on("click",'.prev-page',function() {
                var page=parseInt(jQuery(this).attr('value'));
                if(m_remote_folder === 'Incremental'){
                    wpvivid_get_remote_incremental_backup(page-1);
                }
                else{
                    wpvivid_get_remote_backup_folder(page-1);
                }
            });

            jQuery('#wpvivid_remote_backups_list').on("click",'.next-page',function() {
                var page=parseInt(jQuery(this).attr('value'));
                if(m_remote_folder === 'Incremental'){
                    wpvivid_get_remote_incremental_backup(page+1);
                }
                else{
                    wpvivid_get_remote_backup_folder(page+1);
                }
            });

            jQuery('#wpvivid_remote_backups_list').on("click",'.last-page',function() {
                if(m_remote_folder === 'Incremental'){
                    wpvivid_get_remote_incremental_backup('last');
                }
                else{
                    wpvivid_get_remote_backup_folder('last');
                }
            });

            jQuery('#wpvivid_remote_backups_list').on("keypress", '.current-page', function(){
                if(event.keyCode === 13){
                    var page = jQuery(this).val();
                    if(m_remote_folder === 'Incremental'){
                        wpvivid_get_remote_incremental_backup(page);
                    }
                    else{
                        wpvivid_get_remote_backup_folder(page);
                    }
                }
            });

            function wpvivid_get_remote_backup_folder(page=0) {
                var is_page_turn = true;
                if(page==0)
                {
                    is_page_turn = false;
                    page =jQuery('#wpvivid_remote_backups_list').find('.current-page').val();
                }
                var remote_id = jQuery('#wpvivid_select_remote_storage').val();
                var remote_folder = jQuery('#wpvivid_select_remote_folder').val();
                var is_incremental = false;
                m_remote_folder=remote_folder;
                if(remote_folder === 'Incremental'){
                    //remote_folder = 'Common';
                    is_incremental = true;
                    var ajax_data = {
                        'action': 'wpvivid_achieve_remote_backup',
                        'remote_id': remote_id,
                        'folder': remote_folder,
                        //'incremental_path': m_incremental_remote_folder,
                        'page':page
                    };
                }
                else{
                    var ajax_data = {
                        'action': 'wpvivid_achieve_remote_backup',
                        'remote_id': remote_id,
                        'folder': remote_folder,
                        'page':page
                    };
                }
                jQuery('#wpvivid_sync_remote_folder').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#wpvivid_remote_backups_list').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#wpvivid_remote_backup_scaning').show();
                jQuery('.wpvivid-remote-sync-error').hide();
                wpvivid_post_request_addon(ajax_data, function (data)
                {
                    jQuery('#wpvivid_sync_remote_folder').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#wpvivid_remote_backups_list').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#wpvivid_remote_backup_scaning').hide();
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if(jsonarray !== null) {
                            if (jsonarray.result === 'success') {
                                if(is_page_turn){
                                    jQuery('#wpvivid_remote_backups_list').html(jsonarray.html);
                                    jQuery('#wpvivid-delete-remote-array').show();
                                }
                                else if(!is_incremental) {
                                    jQuery('#wpvivid_incremental_path_list').hide();
                                    jQuery('#wpvivid_incremental_path_list').html('');
                                    jQuery('#wpvivid_remote_backups_list').html(jsonarray.html);
                                    jQuery('#wpvivid-delete-remote-array').show();
                                }
                                else{
                                    jQuery('#wpvivid_incremental_path_list').show();
                                    jQuery('#wpvivid_incremental_path_list').html(jsonarray.incremental_list);
                                    jQuery('#wpvivid_remote_backups_list').html('');
                                    jQuery('#wpvivid-delete-remote-array').hide();
                                }
                            }
                            else {
                                jQuery('.wpvivid-remote-sync-error').show();
                                jQuery('.wpvivid-remote-sync-error').html(jsonarray.error);
                                jQuery('#wpvivid_remote_backups_list').html('');
                                jQuery('#wpvivid-delete-remote-array').hide();
                            }
                        }
                        else{
                            jQuery('#wpvivid_remote_backups_list').html('');
                            jQuery('#wpvivid-delete-remote-array').hide();
                        }
                    }
                    catch (err)
                    {
                        jQuery('.wpvivid-remote-sync-error').show();
                        jQuery('.wpvivid-remote-sync-error').html(err);
                        jQuery('#wpvivid_remote_backups_list').html('');
                        jQuery('#wpvivid-delete-remote-array').hide();
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    jQuery('#wpvivid_remote_backups_list').html('');
                    jQuery('#wpvivid-delete-remote-array').hide();
                    jQuery('#wpvivid_sync_remote_folder').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#wpvivid_remote_backup_scaning').hide();
                    var error_message = wpvivid_output_ajaxerror('achieving backup', textStatus, errorThrown);
                    jQuery('.wpvivid-remote-sync-error').show();
                    jQuery('.wpvivid-remote-sync-error').html(error_message);
                });
            }

            function wpvivid_get_remote_incremental_backup(page=0){
                var is_page_turn = true;
                if(page==0)
                {
                    is_page_turn = false;
                    page =jQuery('#wpvivid_remote_backups_list').find('.current-page').val();
                }

                var incremental_path = m_incremental_remote_folder;
                var remote_id = jQuery('#wpvivid_select_remote_storage').val();
                var incremental_type = jQuery('#wpvivid_select_remote_incremental_backup').val();
                var ajax_data = {
                    'action': 'wpvivid_achieve_incremental_child_path',
                    'remote_id': remote_id,
                    'incremental_path': incremental_path,
                    'incremental_type': incremental_type,
                    'page':page
                };
                jQuery('#wpvivid_sync_remote_folder').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#wpvivid_remote_backups_list').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('.wpvivid-incremental-child').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#wpvivid_remote_backup_scaning').show();
                jQuery('.wpvivid-remote-sync-error').hide();
                wpvivid_post_request_addon(ajax_data, function (data){
                    jQuery('#wpvivid_sync_remote_folder').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#wpvivid_remote_backups_list').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('.wpvivid-incremental-child').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#wpvivid_remote_backup_scaning').hide();
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if(jsonarray !== null) {
                            if (jsonarray.result === 'success') {
                                jQuery('#wpvivid_remote_backups_list').html(jsonarray.html);
                                jQuery('#wpvivid-delete-remote-array').show();
                            }
                            else {
                                jQuery('.wpvivid-remote-sync-error').show();
                                jQuery('.wpvivid-remote-sync-error').html(jsonarray.error);
                                jQuery('#wpvivid_remote_backups_list').html('');
                                jQuery('#wpvivid-delete-remote-array').hide();
                            }
                        }
                        else{
                            jQuery('#wpvivid_remote_backups_list').html('');
                            jQuery('#wpvivid-delete-remote-array').hide();
                        }
                    }
                    catch (err)
                    {
                        jQuery('.wpvivid-remote-sync-error').show();
                        jQuery('.wpvivid-remote-sync-error').html(err);
                        jQuery('#wpvivid_remote_backups_list').html('');
                        jQuery('#wpvivid-delete-remote-array').hide();
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    jQuery('#wpvivid_remote_backups_list').html('');
                    jQuery('#wpvivid-delete-remote-array').hide();
                    jQuery('#wpvivid_sync_remote_folder').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('.wpvivid-incremental-child').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#wpvivid_remote_backup_scaning').hide();
                    var error_message = wpvivid_output_ajaxerror('achieving backup', textStatus, errorThrown);
                    jQuery('.wpvivid-remote-sync-error').show();
                    jQuery('.wpvivid-remote-sync-error').html(error_message);
                });

            }

            jQuery('#wpvivid_remote_backups_list').on("click",'.wpvivid-lock',function() {
                var Obj=jQuery(this);
                var backup_id=Obj.closest('tr').attr('id');
                if(Obj.hasClass('dashicons-lock'))
                {
                    var lock=0;
                }
                else
                {
                    var lock=1;
                }

                var ajax_data= {
                    'action': 'wpvivid_set_remote_security_lock_ex',
                    'backup_id': backup_id,
                    'lock': lock
                };
                wpvivid_post_request_addon(ajax_data, function(data)
                {
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            if(lock)
                            {
                                Obj.removeClass('dashicons-unlock');
                                Obj.addClass('dashicons-lock');
                            }
                            else
                            {
                                Obj.removeClass('dashicons-lock');
                                Obj.addClass('dashicons-unlock');
                            }
                        }
                    }
                    catch(err){
                        alert(err);
                    }
                }, function(XMLHttpRequest, textStatus, errorThrown)
                {
                    var error_message = wpvivid_output_ajaxerror('setting up a lock for the backup', textStatus, errorThrown);
                    alert(error_message);
                });
            });

            jQuery('#wpvivid_remote_backups_list').on("click",'.backuplist-delete-backup',function() {
                var Obj=jQuery(this);
                var backup_id=Obj.closest('tr').attr('id');
                var page =jQuery('#wpvivid_remote_backups_list').find('.current-page').val();
                var descript = '<?php _e('Are you sure to remove this backup? This backup will be deleted permanently.', 'wpvivid'); ?>';
                var ret = confirm(descript);
                if(ret === true)
                {
                    var incremental_type = jQuery('#wpvivid_select_remote_incremental_backup').val();
                    var ajax_data = {
                        'action': 'wpvivid_delete_remote_backup',
                        'backup_id': backup_id,
                        'folder': m_remote_folder,
                        'incremental_type': incremental_type,
                        'page':page
                    };
                    wpvivid_post_request_addon(ajax_data, function(data)
                    {
                        try
                        {
                            var jsonarray = jQuery.parseJSON(data);
                            if (jsonarray.result === 'success')
                            {
                                jQuery('#wpvivid_remote_backups_list').html(jsonarray.html);
                                jQuery('#wpvivid-delete-remote-array').show();
                            }
                            else if(jsonarray.result === 'failed')
                            {
                                alert(jsonarray.error);
                            }
                        }
                        catch(err){
                            alert(err);
                        }

                    }, function(XMLHttpRequest, textStatus, errorThrown) {
                        var error_message = wpvivid_output_ajaxerror('deleting the backup', textStatus, errorThrown);
                        alert(error_message);
                    });
                }
            });

            jQuery('#wpvivid-delete-remote-array').click(function() {
                var delete_backup_array = new Array();
                var count = 0;
                //
                var page =jQuery('#wpvivid_remote_backups_list').find('.current-page').val();
                var folder = jQuery('#wpvivid_select_local_backup_folder').val();
                jQuery('#wpvivid_remote_backups_list .wpvivid-backup-row input').each(function (i)
                {
                    if(jQuery(this).prop('checked'))
                    {
                        delete_backup_array[count] =jQuery(this).closest('tr').attr('id');
                        count++;
                    }
                });
                if( count === 0 )
                {
                    alert('<?php _e('Please select at least one item.','wpvivid'); ?>');
                }
                else
                {
                    var descript = '<?php _e('Are you sure to remove the selected backups? These backups will be deleted permanently from your hosting (localhost) and remote storages.', 'wpvivid'); ?>';

                    var ret = confirm(descript);
                    if (ret === true)
                    {
                        var incremental_type = jQuery('#wpvivid_select_remote_incremental_backup').val();
                        var ajax_data = {
                            'action': 'wpvivid_delete_remote_backup_array',
                            'backup_id': delete_backup_array,
                            'folder': m_remote_folder,
                            'incremental_type': incremental_type,
                            'page':page
                        };

                        wpvivid_post_request_addon(ajax_data, function (data)
                        {
                            try
                            {
                                var jsonarray = jQuery.parseJSON(data);
                                if (jsonarray.result === 'success')
                                {
                                    jQuery('#wpvivid_remote_backups_list').html(jsonarray.html);
                                    jQuery('#wpvivid-delete-remote-array').show();
                                }
                                else if(jsonarray.result === 'failed')
                                {
                                    alert(jsonarray.error);
                                }
                            }
                            catch(err){
                                alert(err);
                            }
                        }, function (XMLHttpRequest, textStatus, errorThrown) {
                            var error_message = wpvivid_output_ajaxerror('deleting the backup', textStatus, errorThrown);
                            alert(error_message);
                        });
                    }
                }
            });

            function wpvivid_select_remote_folder(){
                wpvivid_get_remote_backup_folder();
            }

            jQuery('#wpvivid_incremental_path_list').on("click", '.prev-page', function(){
                var page=parseInt(jQuery(this).attr('value'));
                wpvivid_archieve_incremental_remote_folder_list(page-1);
            });

            jQuery('#wpvivid_incremental_path_list').on("click", '.next-page', function(){
                var page=parseInt(jQuery(this).attr('value'));
                wpvivid_archieve_incremental_remote_folder_list(page+1);
            });

            jQuery('#wpvivid_incremental_path_list').on("click", '.last-page', function(){
                wpvivid_archieve_incremental_remote_folder_list('last');
            });

            jQuery('#wpvivid_incremental_path_list').on("keypress", '.current-page', function(){
                if(event.keyCode === 13){
                    var page = jQuery(this).val();
                    wpvivid_archieve_incremental_remote_folder_list(page);
                }
            });

            function wpvivid_archieve_incremental_remote_folder_list(page=0){
                if(page==0)
                {
                    page =jQuery('#wpvivid_incremental_path_list').find('.current-page').val();
                }

                var remote_id = jQuery('#wpvivid_select_remote_storage').val();
                var remote_folder = 'Common';
                var ajax_data = {
                    'action': 'wpvivid_archieve_incremental_remote_folder_list',
                    'remote_id': remote_id,
                    'folder': remote_folder,
                    'page':page
                };
                jQuery('#wpvivid_sync_remote_folder').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#wpvivid_remote_backups_list').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#wpvivid_remote_backup_scaning').show();
                jQuery('.wpvivid-remote-sync-error').hide();
                wpvivid_post_request_addon(ajax_data, function (data)
                {
                    jQuery('#wpvivid_sync_remote_folder').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#wpvivid_remote_backups_list').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#wpvivid_remote_backup_scaning').hide();
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if(jsonarray !== null) {
                            if (jsonarray.result === 'success') {
                                jQuery('#wpvivid_incremental_path_list').show();
                                jQuery('#wpvivid_incremental_path_list').html(jsonarray.incremental_list);
                                jQuery('#wpvivid_remote_backups_list').html('');
                                jQuery('#wpvivid-delete-remote-array').hide();
                            }
                            else {
                                jQuery('.wpvivid-remote-sync-error').show();
                                jQuery('.wpvivid-remote-sync-error').html(jsonarray.error);
                                jQuery('#wpvivid_remote_backups_list').html('');
                                jQuery('#wpvivid-delete-remote-array').hide();
                            }
                        }
                        else{
                            jQuery('#wpvivid_remote_backups_list').html('');
                            jQuery('#wpvivid-delete-remote-array').hide();
                        }
                    }
                    catch (err)
                    {
                        jQuery('.wpvivid-remote-sync-error').show();
                        jQuery('.wpvivid-remote-sync-error').html(err);
                        jQuery('#wpvivid_remote_backups_list').html('');
                        jQuery('#wpvivid-delete-remote-array').hide();
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown)
                {
                    jQuery('#wpvivid_remote_backups_list').html('');
                    jQuery('#wpvivid-delete-remote-array').hide();
                    jQuery('#wpvivid_sync_remote_folder').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#wpvivid_remote_backup_scaning').hide();
                    var error_message = wpvivid_output_ajaxerror('achieving backup', textStatus, errorThrown);
                    jQuery('.wpvivid-remote-sync-error').show();
                    jQuery('.wpvivid-remote-sync-error').html(error_message);
                });
            }

            jQuery('#wpvivid_incremental_path_list').on('click', '.wpvivid-incremental-child', function(){
                var incremental_path = jQuery(this).closest('tr').attr('id');
                var remote_id = jQuery('#wpvivid_select_remote_storage').val();
                var incremental_type = jQuery('#wpvivid_select_remote_incremental_backup').val();
                m_incremental_remote_folder = incremental_path;
                var ajax_data = {
                    'action': 'wpvivid_achieve_incremental_child_path',
                    'remote_id': remote_id,
                    'incremental_path': incremental_path,
                    'incremental_type': incremental_type
                };
                jQuery('#wpvivid_sync_remote_folder').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#wpvivid_remote_backups_list').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('.wpvivid-incremental-child').css({'pointer-events': 'none', 'opacity': '0.4'});
                jQuery('#wpvivid_remote_backup_scaning').show();
                jQuery('.wpvivid-remote-sync-error').hide();
                wpvivid_post_request_addon(ajax_data, function (data){
                    jQuery('#wpvivid_sync_remote_folder').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#wpvivid_remote_backups_list').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('.wpvivid-incremental-child').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#wpvivid_remote_backup_scaning').hide();
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if(jsonarray !== null) {
                            if (jsonarray.result === 'success') {
                                jQuery('#wpvivid_remote_backups_list').html(jsonarray.html);
                                jQuery('#wpvivid-delete-remote-array').show();
                            }
                            else {
                                jQuery('.wpvivid-remote-sync-error').show();
                                jQuery('.wpvivid-remote-sync-error').html(jsonarray.error);
                                jQuery('#wpvivid_remote_backups_list').html('');
                                jQuery('#wpvivid-delete-remote-array').hide();
                            }
                        }
                        else{
                            jQuery('#wpvivid_remote_backups_list').html('');
                            jQuery('#wpvivid-delete-remote-array').hide();
                        }
                    }
                    catch (err)
                    {
                        jQuery('.wpvivid-remote-sync-error').show();
                        jQuery('.wpvivid-remote-sync-error').html(err);
                        jQuery('#wpvivid_remote_backups_list').html('');
                        jQuery('#wpvivid-delete-remote-array').hide();
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    jQuery('#wpvivid_remote_backups_list').html('');
                    jQuery('#wpvivid-delete-remote-array').hide();
                    jQuery('#wpvivid_sync_remote_folder').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('.wpvivid-incremental-child').css({'pointer-events': 'auto', 'opacity': '1'});
                    jQuery('#wpvivid_remote_backup_scaning').hide();
                    var error_message = wpvivid_output_ajaxerror('achieving backup', textStatus, errorThrown);
                    jQuery('.wpvivid-remote-sync-error').show();
                    jQuery('.wpvivid-remote-sync-error').html(error_message);
                });
            });

            jQuery('#wpvivid_remote_backups_list').on('click', 'thead tr td input', function()
            {
                wpvivid_control_backup_select(jQuery(this), 'wpvivid_remote_backups_list');
            });

            jQuery('#wpvivid_remote_backups_list').on('click', 'tfoot tr td input', function()
            {
                wpvivid_control_backup_select(jQuery(this), 'wpvivid_remote_backups_list');
            });

            jQuery(document).ready(function (){
                var select_remote_id = '<?php echo $select_remote_id; ?>';
                if(select_remote_id === ''){
                    jQuery('#wpvivid_select_remote_storage option:not(:selected)');
                }
                else{
                    <?php
                    if(array_key_exists($select_remote_id,$remoteslist)){
                    ?>
                    jQuery('select option[value='+select_remote_id+']').attr("selected",true);
                    <?php
                    }
                    else{
                    ?>
                    jQuery('#wpvivid_select_remote_storage option:not(:selected)');
                    <?php
                    }
                    ?>
                }

                <?php
                $default = array();
                $remote_array = apply_filters('wpvivid_archieve_remote_array', $default);
                foreach ($remote_array as $key => $value) {
                ?>
                var key = '<?php echo $key; ?>';
                remote_list_array[key] = Array();
                remote_list_array[key]['path'] = '<?php echo $value['path']; ?>';
                <?php
                }
                ?>

                jQuery(document).on('wpvivid_update_remote_backup', function(event)
                {
                    wpvivid_get_remote_backup_folder();
                });
            });
        </script>
        <?php
    }

    public function output_upload_backup()
    {
        $backupdir=WPvivid_Setting::get_backupdir();
        ?>
        <div class="wpvivid-one-coloum wpvivid-workflow wpvivid-clear-float" style="margin-bottom:1em;">
            <span class="dashicons dashicons-portfolio wpvivid-dashicons-orange"></span>
            <span>Backup Directory:</span><span><code><?php _e(WP_CONTENT_DIR.DIRECTORY_SEPARATOR.$backupdir); ?></code></span>
            <span> | </span>
            <span><a href="<?php echo apply_filters('wpvivid_white_label_page_redirect', 'admin.php?page=wpvivid-setting', 'wpvivid-setting'); ?>">rename</a></span>
        </div>
        <div style="clear: both;"></div>
        <div style="display: block;" id="wpvivid_backup_uploader">
            <?php
            Wpvivid_BackupUploader_addon::upload_meta_box();
            ?>
        </div>
        <?php
    }

    public function output_backup_log_list()
    {
        $log_list = new WPvivid_Log_addon();
        $log_list->output_backup_restore_log_list();
        ?>
        <?php
        ?>
        <script>
            var wpvivid_backup_restore_type = '';
            var wpvivid_backup_restore_result = '';
            jQuery('#wpvivid_backup_log_list').on('click', '#wpvivid_search_backup_restore_log_btn', function(){
                wpvivid_backup_restore_type=jQuery('#wpvivid_backup_restore_log_type').val();
                wpvivid_backup_restore_result=jQuery('#wpvivid_backup_restore_log_result').val();

                if(wpvivid_backup_restore_type=='0')
                {
                    wpvivid_backup_restore_type='';
                }

                if(wpvivid_backup_restore_result=='0')
                {
                    wpvivid_backup_restore_result='';
                }
                wpvivid_log_change_page('first','backup&restore&transfer','wpvivid_backup_log_list');
            });

            function wpvivid_open_log(log,slug) {
                var ajax_data = {
                    'action':'wpvivid_view_log_ex',
                    'log': log
                };

                wpvivid_post_request_addon(ajax_data, function(data)
                {
                    jQuery('#wpvivid_read_log_content').html("");
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === "success")
                        {
                            jQuery( document ).trigger( '<?php echo $this->main_tab->container_id ?>-show',[ 'open_log', slug ]);
                            var log_data = jsonarray.data;
                            while (log_data.indexOf('\n') >= 0)
                            {
                                var iLength = log_data.indexOf('\n');
                                var log = log_data.substring(0, iLength);
                                log_data = log_data.substring(iLength + 1);
                                var insert_log = "<div style=\"clear:both;\">" + log + "</div>";
                                jQuery('#wpvivid_read_log_content').append(insert_log);
                            }
                        }
                        else
                        {
                            jQuery('#wpvivid_read_log_content').html(jsonarray.error);
                        }
                    }
                    catch(err)
                    {
                        alert(err);
                        var div = "Reading the log failed. Please try again.";
                        jQuery('#wpvivid_read_log_content').html(div);
                    }
                }, function(XMLHttpRequest, textStatus, errorThrown)
                {
                    var error_message = wpvivid_output_ajaxerror('export the previously-exported settings', textStatus, errorThrown);
                    alert(error_message);
                });
            }

            function wpvivid_download_log(log) {
                location.href =ajaxurl+'?_wpnonce='+wpvivid_ajax_object_addon.ajax_nonce+'&action=wpvivid_download_log&log='+log;
            }

            function wpvivid_log_change_page(page,type,log_contenter) {
                var ajax_data = {
                    'action':'wpvivid_get_log_list_page',
                    'page': page,
                    'type': type,
                    'backup_restore_type': wpvivid_backup_restore_type,
                    'backup_restore_result': wpvivid_backup_restore_result
                };

                wpvivid_post_request_addon(ajax_data, function(data)
                {
                    var jsonarray = jQuery.parseJSON(data);
                    if (jsonarray.result === 'success')
                    {
                        jQuery('#'+log_contenter).html(jsonarray.rows);
                    }
                    else
                    {
                        alert(jsonarray.error);
                    }
                    if(wpvivid_backup_restore_type == ''){
                        wpvivid_backup_restore_type = '0';
                    }
                    if(wpvivid_backup_restore_result == ''){
                        wpvivid_backup_restore_result = '0';
                    }
                    jQuery('#wpvivid_backup_restore_log_type').val(wpvivid_backup_restore_type);
                    jQuery('#wpvivid_backup_restore_log_result').val(wpvivid_backup_restore_result);
                }, function(XMLHttpRequest, textStatus, errorThrown)
                {
                    if(wpvivid_backup_restore_type == ''){
                        wpvivid_backup_restore_type = '0';
                    }
                    if(wpvivid_backup_restore_result == ''){
                        wpvivid_backup_restore_result = '0';
                    }
                    jQuery('#wpvivid_backup_restore_log_type').val(wpvivid_backup_restore_type);
                    jQuery('#wpvivid_backup_restore_log_result').val(wpvivid_backup_restore_result);
                    var error_message = wpvivid_output_ajaxerror('changing log page', textStatus, errorThrown);
                    alert(error_message);
                });
            }
        </script>
        <?php
    }

    public function output_view_detail()
    {
        ?>
        <div style="margin-top: 10px;">
            <div id="wpvivid_init_content_info">
                <div style="float: left; height: 20px; line-height: 20px; margin-top: 4px;">Reading the backup contents</div>
                <div class="spinner" style="float: left;"></div>
                <div style="clear: both;"></div>
            </div>
            <div class="wpvivid-element-space-bottom" id="wpvivid_backup_content_list"></div>
        </div>
        <script>
            jQuery('#wpvivid_backup_list').on("click",'.wpvivid-backup-content',function() {
                var Obj=jQuery(this);
                var type_string = Obj.attr('type-string');
                wpvivid_show_backup_content_page(type_string, 'localhost_backuplist');
            });

            jQuery('#wpvivid_remote_backups_list').on("click",'.wpvivid-backup-content',function() {
                var Obj=jQuery(this);
                var type_string = Obj.attr('type-string');
                wpvivid_show_backup_content_page(type_string, 'remote_backuplist');
            });

            function wpvivid_show_backup_content_page(type_string, list_from) {
                jQuery( document ).trigger( '<?php echo $this->main_tab->container_id ?>-show',[ 'view_detail', list_from ]);
                var type_array = type_string.split(",");
                var ajax_data = {
                    'action': 'wpvivid_backup_content_display',
                    'backup_content_list': type_array
                };

                jQuery('#wpvivid_backup_content_list').html('');
                jQuery('#wpvivid_init_content_info').show();
                jQuery('#wpvivid_init_content_info').find('.spinner').addClass('is-active');
                var retry = '<input type="button" class="button button-primary" value="Read Again" onclick="wpvivid_show_backup_content_page(\''+type_string+'\', \''+list_from+'\');" />';
                wpvivid_post_request_addon(ajax_data, function (data) {
                    jQuery('#wpvivid_init_content_info').hide();
                    jQuery('#wpvivid_init_content_info').find('.spinner').removeClass('is-active');
                    try {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success') {
                            jQuery('#wpvivid_backup_content_list').html(jsonarray.html);
                        }
                        else {
                            alert(jsonarray.error);
                            jQuery('#wpvivid_backup_content_list').html(retry);
                        }
                    }
                    catch (err) {
                        alert(err);
                        jQuery('#wpvivid_backup_content_list').html(retry);
                    }
                }, function (XMLHttpRequest, textStatus, errorThrown) {
                    jQuery('#wpvivid_init_content_info').hide();
                    jQuery('#wpvivid_init_content_info').find('.spinner').removeClass('is-active');
                    var error_message = wpvivid_output_ajaxerror('changing base settings', textStatus, errorThrown);
                    alert(error_message);
                    jQuery('#wpvivid_backup_content_list').html(retry);
                });
            }
        </script>
        <?php
    }

    public function output_open_log()
    {
        ?>
        <div class="postbox restore_log" id="wpvivid_read_log_content"></div>
        <?php
    }

    public function output_download_backup()
    {
        ?>
        <div id="wpvivid_init_download_info">
            <div style="float: left; height: 20px; line-height: 20px; margin-top: 4px;">Initializing the download info</div>
            <div class="spinner" style="float: left;"></div>
            <div style="clear: both;"></div>
        </div>
        <div class="wpvivid-element-space-bottom" id="wpvivid_files_list"></div>
        <script>
            var wpvivid_download_files_list = wpvivid_restore || {};
            wpvivid_download_files_list.backup_id='';
            wpvivid_download_files_list.backup_type='general';
            wpvivid_download_files_list.wpvivid_download_file_array = Array();
            wpvivid_download_files_list.wpvivid_download_lock_array = Array();
            wpvivid_download_files_list.init=function(backup_id) {
                wpvivid_download_files_list.backup_id=backup_id;
                wpvivid_download_files_list.wpvivid_download_file_array.splice(0, wpvivid_download_files_list.wpvivid_download_file_array.length);
            };

            wpvivid_download_files_list.add_download_queue=function(filename) {
                var download_file_size = jQuery("[slug='"+filename+"']").find('.wpvivid-download-status').find('.wpvivid-download-file-size').html();
                var tmp_html = '<div class="wpvivid-element-space-bottom">' +
                    '<span class="wpvivid-element-space-right">Retriving (remote storage to web server)</span><span class="wpvivid-element-space-right">|</span><span>File Size: </span><span class="wpvivid-element-space-right">'+download_file_size+'</span><span class="wpvivid-element-space-right">|</span><span>Downloaded Size: </span><span>0</span>' +
                    '</div>' +
                    '<div style="width:100%;height:10px; background-color:#dcdcdc;">' +
                    '<div style="background-color:#0085ba; float:left;width:0%;height:10px;"></div>' +
                    '</div>';
                jQuery("[slug='"+filename+"']").find('.wpvivid-download-status').html(tmp_html);
                if(jQuery.inArray(filename, wpvivid_download_files_list.wpvivid_download_file_array) === -1) {
                    wpvivid_download_files_list.wpvivid_download_file_array.push(filename);
                }
                var ajax_data = {
                    'action': 'wpvivid_prepare_download_backup',
                    'backup_id':wpvivid_download_files_list.backup_id,
                    'file_name':filename
                };
                wpvivid_post_request_addon(ajax_data, function(data)
                {
                }, function(XMLHttpRequest, textStatus, errorThrown)
                {
                }, 0);

                wpvivid_download_files_list.check_queue();
            };

            wpvivid_download_files_list.check_queue=function() {
                if(jQuery.inArray(wpvivid_download_files_list.backup_id, wpvivid_download_files_list.wpvivid_download_lock_array) !== -1){
                    return;
                }
                var ajax_data = {
                    'action': 'wpvivid_get_download_progress',
                    'backup_id':wpvivid_download_files_list.backup_id,
                };
                wpvivid_download_files_list.wpvivid_download_lock_array.push(wpvivid_download_files_list.backup_id);
                wpvivid_post_request_addon(ajax_data, function(data)
                {
                    wpvivid_download_files_list.wpvivid_download_lock_array.splice(jQuery.inArray(wpvivid_download_files_list.backup_id, wpvivid_download_files_list.wpvivid_download_file_array),1);
                    var jsonarray = jQuery.parseJSON(data);
                    if (jsonarray.result === 'success')
                    {
                        jQuery.each(jsonarray.files,function (index, value)
                        {
                            if(jQuery.inArray(index, wpvivid_download_files_list.wpvivid_download_file_array) !== -1) {
                                if(value.status === 'timeout' || value.status === 'completed' || value.status === 'error'){
                                    wpvivid_download_files_list.wpvivid_download_file_array.splice(jQuery.inArray(index, wpvivid_download_files_list.wpvivid_download_file_array),1);
                                }
                                wpvivid_download_files_list.update_item(index, value);
                            }
                        });

                        //if(jsonarray.need_update)
                        if(wpvivid_download_files_list.wpvivid_download_file_array.length > 0)
                        {
                            setTimeout(function()
                            {
                                wpvivid_download_files_list.check_queue();
                            }, 3000);
                        }
                    }
                }, function(XMLHttpRequest, textStatus, errorThrown)
                {
                    wpvivid_download_files_list.wpvivid_download_lock_array.splice(jQuery.inArray(wpvivid_download_files_list.backup_id, wpvivid_download_files_list.wpvivid_download_file_array),1);
                    setTimeout(function()
                    {
                        wpvivid_download_files_list.check_queue();
                    }, 3000);
                }, 0);
            };

            wpvivid_download_files_list.update_item=function(index,file) {
                jQuery("[slug='"+index+"']").find('.wpvivid-download-status').html(file.html);
            };

            wpvivid_download_files_list.download_now=function(filename) {
                location.href =ajaxurl+'?_wpnonce='+wpvivid_ajax_object_addon.ajax_nonce+'&action=wpvivid_download_backup_ex&backup_id='+wpvivid_download_files_list.backup_id+'&file_name='+filename;
            };

            wpvivid_download_files_list.add_incremental_download_queue=function(filename, type){
                if(type === 'file'){
                    var download_file_size = jQuery("[slug='"+filename+"']").find('td:eq(2)').html();
                    jQuery("[class='"+filename+"-wpvivid-download-progress']").remove();
                    var download_text = '<tr class="'+filename+'-wpvivid-download-progress">' +
                        '<td colspan="4">' +
                        '<p>' +
                        '<span class="wpvivid-span-progress">' +
                        '<span class="wpvivid-span-processed-progress" style="width: 0;"></span>' +
                        '</span>' +
                        '</p>' +
                        '<p>' +
                        '<span class="wpvivid-download-progress-text" style="font-size: 10px;"><i>Downloading backup from remote storage to web server, 0% completed.</i></span>' +
                        '</p>' +
                        '</td>' +
                        '</tr>';
                    jQuery("[slug='"+filename+"']").after(download_text);
                    jQuery("[class='"+filename+"-wpvivid-download-progress']").find('.wpvivid-span-processed-progress').css('width', '0%');
                }
                else{
                    jQuery("[class='"+filename+"-wpvivid-download-progress']").remove();
                    var download_text = '<p class="'+filename+'-wpvivid-download-progress"><span class="dashicons dashicons-arrow-right wpvivid-dashicons-grey"></span><span><code>Downloading backup from remote storage to web server, 0% completed.</code></span></p>';
                    jQuery("[slug='"+filename+"']").closest('div').append(download_text);
                }

                if(jQuery.inArray(filename, wpvivid_download_files_list.wpvivid_download_file_array) === -1) {
                    wpvivid_download_files_list.wpvivid_download_file_array.push(filename);
                }
                var ajax_data = {
                    'action': 'wpvivid_prepare_download_backup',
                    'backup_id':wpvivid_download_files_list.backup_id,
                    'file_name':filename
                };
                wpvivid_post_request_addon(ajax_data, function(data)
                {
                }, function(XMLHttpRequest, textStatus, errorThrown)
                {
                }, 0);

                wpvivid_download_files_list.check_incremental_queue(type);
            };

            wpvivid_download_files_list.check_incremental_queue=function(type){
                if(jQuery.inArray(wpvivid_download_files_list.backup_id, wpvivid_download_files_list.wpvivid_download_lock_array) !== -1){
                    return;
                }
                var ajax_data = {
                    'action': 'wpvivid_get_download_incremental_progress',
                    'backup_id':wpvivid_download_files_list.backup_id,
                    'type':type
                };
                wpvivid_download_files_list.wpvivid_download_lock_array.push(wpvivid_download_files_list.backup_id);
                wpvivid_post_request_addon(ajax_data, function(data)
                {
                    wpvivid_download_files_list.wpvivid_download_lock_array.splice(jQuery.inArray(wpvivid_download_files_list.backup_id, wpvivid_download_files_list.wpvivid_download_file_array),1);
                    var jsonarray = jQuery.parseJSON(data);
                    if (jsonarray.result === 'success')
                    {
                        jQuery.each(jsonarray.files,function (index, value)
                        {
                            if(jQuery.inArray(index, wpvivid_download_files_list.wpvivid_download_file_array) !== -1) {
                                if(value.status === 'timeout' || value.status === 'completed' || value.status === 'error'){
                                    wpvivid_download_files_list.wpvivid_download_file_array.splice(jQuery.inArray(index, wpvivid_download_files_list.wpvivid_download_file_array),1);
                                    if(type === 'file'){
                                        jQuery("[slug='"+index+"']").find('.wpvivid-download').css({'pointer-events': 'auto', 'opacity': '1'});
                                    }
                                    else{
                                        jQuery("[slug='"+index+"']").css({'pointer-events': 'auto', 'opacity': '1'});
                                    }
                                    if(value.status === 'completed'){
                                        if(type === 'file'){
                                            jQuery("[slug='"+index+"']").find('.wpvivid-download').html('Download');
                                            jQuery("[slug='"+index+"']").find('.wpvivid-download').addClass('wpvivid-ready-download');
                                            jQuery("[slug='"+index+"']").find('.wpvivid-download').removeClass('wpvivid-download');
                                        }
                                        else{
                                            jQuery("[slug='"+index+"']").html('<a href="#" style="cursor: pointer;">Download</a>');
                                            jQuery("[slug='"+index+"']").addClass('wpvivid-ready-download-incremental-db');
                                            jQuery("[slug='"+index+"']").removeClass('wpvivid-download-incremental-db');
                                        }
                                    }
                                }
                                else if(value.status === 'running'){
                                    jQuery("[slug='"+index+"']").find('.wpvivid-download').css({'pointer-events': 'none', 'opacity': '0.4'});

                                    if(type === 'file'){
                                        if(!jQuery('#wpvivid_files_list tr').hasClass(index+'-wpvivid-download-progress')){
                                            var download_text = '<tr class="'+index+'-wpvivid-download-progress">' +
                                                '<td colspan="4">' +
                                                '<p>' +
                                                '<span class="wpvivid-span-progress">' +
                                                '<span class="wpvivid-span-processed-progress" style="width: 0;"></span>' +
                                                '</span>' +
                                                '</p>' +
                                                '<p>' +
                                                '<span class="wpvivid-download-progress-text" style="font-size: 10px;"><i>Downloading backup from remote storage to web server, 0% completed.</i></span>' +
                                                '</p>' +
                                                '</td>' +
                                                '</tr>';
                                            jQuery("[slug='"+index+"']").after(download_text);
                                        }
                                    }
                                    else{
                                        if(!jQuery("[slug='"+index+"']").closest('div').find('p').hasClass(index+'-wpvivid-download-progress')){
                                            var download_text = '<p class="'+index+'-wpvivid-download-progress"><span class="dashicons dashicons-arrow-right wpvivid-dashicons-grey"></span><span><code>Downloading backup from remote storage to web server, 0% completed.</code></span>';
                                            jQuery("[slug='"+index+"']").closest('div').append(download_text);
                                        }
                                    }
                                }
                                wpvivid_download_files_list.update_incremental_item(index, value, type);
                            }
                        });

                        //if(jsonarray.need_update)
                        if(wpvivid_download_files_list.wpvivid_download_file_array.length > 0)
                        {
                            setTimeout(function()
                            {
                                wpvivid_download_files_list.check_incremental_queue(type);
                            }, 3000);
                        }
                    }
                }, function(XMLHttpRequest, textStatus, errorThrown)
                {
                    wpvivid_download_files_list.wpvivid_download_lock_array.splice(jQuery.inArray(wpvivid_download_files_list.backup_id, wpvivid_download_files_list.wpvivid_download_file_array),1);
                    setTimeout(function()
                    {
                        wpvivid_download_files_list.check_incremental_queue(type);
                    }, 3000);
                }, 0);
            };

            wpvivid_download_files_list.update_incremental_item=function(index, file, type) {
                jQuery("[class='"+index+"-wpvivid-download-progress']").html(file.html);
                if(type === 'file'){
                    jQuery("[class='"+index+"-wpvivid-download-progress']").find('.wpvivid-span-processed-progress').css('width', file.progress_text+'%');
                }
            };

            jQuery('#wpvivid_backup_list').on("click",'.wpvivid-download',function() {
                var Obj=jQuery(this);
                var backup_id=Obj.closest('tr').attr('id');
                var backup_type=Obj.closest('tr').find('.backuptype').html();
                var backup_time=Obj.closest('tr').find('.backuptime').html();
                var backup_comment=Obj.closest('tr').find('.comment').html();
                wpvivid_init_download_page(backup_id, 'localhost_backuplist', backup_type, backup_time, backup_comment);
            });

            jQuery('#wpvivid_remote_backups_list').on("click",'.wpvivid-download',function() {
                var Obj=jQuery(this);
                var backup_id=Obj.closest('tr').attr('id');
                var backup_type=Obj.closest('tr').find('.backuptype').html();
                var backup_time=Obj.closest('tr').find('.backuptime').html();
                var backup_comment=Obj.closest('tr').find('.comment').html();
                wpvivid_init_download_page(backup_id, 'remote_backuplist', backup_type, backup_time, backup_comment);
            });

            jQuery('#wpvivid_files_list').on("click",'.wpvivid-download',function() {
                var Obj=jQuery(this);
                var file_name=Obj.closest('tr').attr('slug');
                var file_type=Obj.closest('tr').attr('type');
                if(file_type === 'incremental'){
                    var file_type=Obj.closest('tr').attr('type');
                    jQuery(this).css({'pointer-events': 'none', 'opacity': '0.4'});
                    wpvivid_download_files_list.add_incremental_download_queue(file_name, 'file');
                }
                else{
                    wpvivid_download_files_list.add_download_queue(file_name);
                }
            });

            jQuery('#wpvivid_files_list').on("click",'.wpvivid-ready-download',function() {
                var Obj=jQuery(this);
                var file_name=Obj.closest('tr').attr('slug');
                wpvivid_download_files_list.download_now(file_name);
            });

            jQuery('#wpvivid_files_list').on('click', '.wpvivid-download-incremental-db',function(){
                var Obj=jQuery(this);
                var file_name=Obj.attr('slug');
                Obj.css({'pointer-events': 'none', 'opacity': '0.4'});
                wpvivid_download_files_list.add_incremental_download_queue(file_name, 'database');
            });

            jQuery('#wpvivid_files_list').on('click', '.wpvivid-ready-download-incremental-db',function(){
                var Obj=jQuery(this);
                var file_name=Obj.attr('slug');
                wpvivid_download_files_list.download_now(file_name);
            });

            function wpvivid_init_download_page(backup_id, list_from, backup_type, backup_time, backup_comment) {
                jQuery( document ).trigger( '<?php echo $this->main_tab->container_id ?>-show',[ 'download_backup', list_from ]);
                var ajax_data = {
                    'action':'wpvivid_init_download_page_ex',
                    'backup_id':backup_id,
                    'backup_type':backup_type,
                    'backup_time':backup_time,
                    'backup_comment':backup_comment
                };
                jQuery('#wpvivid_files_list').html('');
                jQuery('#wpvivid_init_download_info').show();
                jQuery('#wpvivid_init_download_info').find('.spinner').addClass('is-active');
                var retry = '<input type="button" class="button button-primary" value="Retry the initialization" onclick="wpvivid_init_download_page(\''+backup_id+'\', \''+list_from+'\', \''+backup_type+'\', \''+backup_time+'\', \''+backup_comment+'\');" />';
                wpvivid_post_request_addon(ajax_data, function(data)
                {
                    jQuery('#wpvivid_init_download_info').hide();
                    jQuery('#wpvivid_init_download_info').find('.spinner').removeClass('is-active');
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            wpvivid_download_files_list.init(backup_id);
                            var need_check_queue = false;
                            jQuery.each(jsonarray.files,function (index, value)
                            {
                                if(value.status === 'running'){
                                    if(jQuery.inArray(index, wpvivid_download_files_list.wpvivid_download_file_array) === -1) {
                                        wpvivid_download_files_list.wpvivid_download_file_array.push(index);
                                        need_check_queue = true;
                                    }
                                }
                            });
                            if(need_check_queue) {
                                if(backup_type === 'Incremental'){
                                    var incremental_type = jQuery('#wpvivid_remote_backups_list tr[id='+backup_id+']').find('.wpvivid-backup-content').attr('type-string');
                                    if(incremental_type === 'Database'){
                                        wpvivid_download_files_list.check_incremental_queue('database');
                                    }
                                    else{
                                        wpvivid_download_files_list.check_incremental_queue('file');
                                    }
                                }
                                else{
                                    wpvivid_download_files_list.check_queue();
                                }
                            }
                            jQuery('#wpvivid_files_list').html(jsonarray.html);

                            wpvivid_download_files_list.backup_type=jsonarray.backup_type;
                        }
                        else{
                            alert(jsonarray.error);
                            jQuery('#wpvivid_files_list').html(retry);
                        }
                    }
                    catch(err)
                    {
                        alert(err);
                        jQuery('#wpvivid_files_list').html(retry);
                    }
                },function(XMLHttpRequest, textStatus, errorThrown)
                {
                    jQuery('#wpvivid_init_download_info').hide();
                    jQuery('#wpvivid_init_download_info').find('.spinner').removeClass('is-active');
                    var error_message = wpvivid_output_ajaxerror('initializing download information', textStatus, errorThrown);
                    alert(error_message);
                    jQuery('#wpvivid_files_list').html(retry);
                });
            }

            jQuery('#wpvivid_files_list').on("click",'.first-page',function() {
                wpvivid_download_change_page('first');
            });

            jQuery('#wpvivid_files_list').on("click",'.prev-page',function() {
                var page=parseInt(jQuery(this).attr('value'));
                wpvivid_download_change_page(page-1);
            });

            jQuery('#wpvivid_files_list').on("click",'.next-page',function() {
                var page=parseInt(jQuery(this).attr('value'));
                wpvivid_download_change_page(page+1);
            });

            jQuery('#wpvivid_files_list').on("click",'.last-page',function() {
                wpvivid_download_change_page('last');
            });

            jQuery('#wpvivid_files_list').on("keypress", '.current-page', function(){
                if(event.keyCode === 13){
                    var page = jQuery(this).val();
                    wpvivid_download_change_page(page);
                }
            });

            function wpvivid_download_change_page(page) {
                var backup_id=wpvivid_download_files_list.backup_id;
                var backup_type=wpvivid_download_files_list.backup_type;
                var ajax_data = {
                    'action':'wpvivid_get_download_page_ex',
                    'backup_id':backup_id,
                    'backup_type':backup_type,
                    'page':page
                };

                jQuery('#wpvivid_files_list').html('');
                jQuery('#wpvivid_init_download_info').show();
                jQuery('#wpvivid_init_download_info').find('.spinner').addClass('is-active');

                wpvivid_post_request_addon(ajax_data, function(data)
                {
                    jQuery('#wpvivid_init_download_info').hide();
                    jQuery('#wpvivid_init_download_info').find('.spinner').removeClass('is-active');
                    try
                    {
                        var jsonarray = jQuery.parseJSON(data);
                        if (jsonarray.result === 'success')
                        {
                            jQuery('#wpvivid_files_list').html(jsonarray.html);
                        }
                        else{
                            alert(jsonarray.error);
                        }
                    }
                    catch(err)
                    {
                        alert(err);
                    }
                },function(XMLHttpRequest, textStatus, errorThrown)
                {
                    jQuery('#wpvivid_init_download_info').hide();
                    jQuery('#wpvivid_init_download_info').find('.spinner').removeClass('is-active');
                    var error_message = wpvivid_output_ajaxerror('initializing download information', textStatus, errorThrown);
                    alert(error_message);
                });
            }
        </script>
        <?php
    }

    public function output_restore_backup()
    {
        ?>
        <script>
            jQuery('#wpvivid_backup_list').on("click",'.wpvivid-restore',function()
            {
                var Obj=jQuery(this);
                var backup_id=Obj.closest('tr').attr('id');
                location.href="<?php echo apply_filters('wpvivid_white_label_page_redirect', 'admin.php?page=wpvivid-backup-and-restore', 'wpvivid-backup-and-restore').'&restore=1&backup_id='; ?>"+backup_id;
            });

            jQuery('#wpvivid_remote_backups_list').on("click",'.wpvivid-restore',function() {

                var Obj=jQuery(this);
                var backup_id=Obj.closest('tr').attr('id');
                location.href="<?php echo apply_filters('wpvivid_white_label_page_redirect', 'admin.php?page=wpvivid-backup-and-restore', 'wpvivid-backup-and-restore').'&restore=1&backup_id='; ?>"+backup_id;
            });
        </script>
        <?php
    }
}