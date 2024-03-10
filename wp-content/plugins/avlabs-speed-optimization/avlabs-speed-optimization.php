<?php
/*
Plugin Name: Avlabs Speed Optimization
Plugin URI: https://avikalabs.com
Description: Minify, Combine JS/CSS 
Version: 1.0.0
Author: Vikas Sharma
Author URI: https://avikalabs.com
License: GPL2
*/

defined( 'ABSPATH' ) || exit;


define('AVLABS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) ) ;
define('AVLABS_PLUGIN_URL', plugin_dir_url( __FILE__ ) ) ;
define('AVLABS_PLUGIN_SLUG' , basename(AVLABS_PLUGIN_DIR)) ;

if ( ! defined( 'AVLABS_CACHE_ROOT_PATH' ) ) {
	define( 'AVLABS_CACHE_ROOT_PATH', WP_CONTENT_DIR . '/avlabs-cache/' );
}

define('AVLABS_JS_DIR', AVLABS_CACHE_ROOT_PATH.'js/') ;

define('AVLABS_FONTS_PRELOAD_DIR', AVLABS_CACHE_ROOT_PATH.'fonts-preload/') ;
define('AVLABS_CSS_SECONDARY_DIR', AVLABS_CACHE_ROOT_PATH . 'css-secondary/' ) ;

if ( ! defined( 'AVLABS_CACHE_ROOT_URL' ) ) {
	define( 'AVLABS_CACHE_ROOT_URL', WP_CONTENT_URL . '/avlabs-cache/' );
}


define('AVLABS_FONTS_PRELOAD_URL', AVLABS_CACHE_ROOT_URL.'fonts-preload/' ) ;
define('AVLABS_CSS_SECONDARY_URL', AVLABS_CACHE_ROOT_URL.'css-secondary/' ) ;
define('AVLABS_JS_URL', AVLABS_CACHE_ROOT_URL.'js/' ) ;

if ( ! defined( 'CHMOD_AVLABS_CACHE_DIRS' ) ) {
	define( 'CHMOD_AVLABS_CACHE_DIRS', 0755 ); 
}

if ( ! defined( 'CHMOD_AVLABS_CACHE_FILES' ) ) {
	define( 'CHMOD_AVLABS_CACHE_FILES', 0644 ); 
}


if(is_admin())
{
    require_once AVLABS_PLUGIN_DIR .'admin/admin-settings.php' ;
}

/// Optimization starts ///
class AvlabsSpeedOptimization
{
    ////// Common Global Variable /////
    protected $optimization_settings;
    protected $file_system ;

    //// JS Global Variable ////
    protected $all_scripts ;
    protected $primary_scripts;
    protected $primary_script_index ;
    protected $primary_script_suffix= '_avlabs_primary_script' ;
    protected $primary_script_url;
    protected $primary_js_sort_order;
    protected $primary_js_merge;
    protected $secondary_scripts;
    protected $secondary_script_url;
    protected $secondary_js_sort_order;
    protected $secondary_js_merge;

    //// CSS Global Variable ////
    protected $main_css_url;
    protected $primary_css;
    protected $secondary_css;
    protected $critical_css_node;

    // Auto updates 
    
    public function __construct()
    {
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
        $this->file_system = new WP_Filesystem_Direct( new StdClass() );
        /// Create all cache folders and files 
        if(!$this->file_system->is_dir(AVLABS_CACHE_ROOT_PATH))
        {
            $this->file_system->mkdir(AVLABS_CACHE_ROOT_PATH,CHMOD_AVLABS_CACHE_DIRS);
            $this->file_system->put_contents(AVLABS_CACHE_ROOT_PATH.'index.php' , '<?php //Silence is Golden',CHMOD_AVLABS_CACHE_FILES ) ;

        }

        if(!$this->file_system->is_dir(AVLABS_JS_DIR))
        {
            $this->file_system->mkdir(AVLABS_JS_DIR,CHMOD_AVLABS_CACHE_DIRS);
        }

        if(!$this->file_system->is_dir(AVLABS_FONTS_PRELOAD_DIR))
        {
            $this->file_system->mkdir(AVLABS_FONTS_PRELOAD_DIR,CHMOD_AVLABS_CACHE_DIRS);
        }

        if(!$this->file_system->is_dir(AVLABS_CSS_SECONDARY_DIR))
        {
            $this->file_system->mkdir(AVLABS_CSS_SECONDARY_DIR,CHMOD_AVLABS_CACHE_DIRS);
        }

        add_action('plugins_loaded', array( $this, 'do_speed_optimizaion' ) , 100) ;
        
    }


    public function do_speed_optimizaion()
    {   
        if(!defined( 'WP_ROCKET_FILE') )
        {
            return ;
        }
        else
        {
            if(class_exists('\WP_Rocket\Buffer\Tests'))
            {
                $rocket_is_cache_enabled = new \WP_Rocket\Buffer\Tests(
                    new \WP_Rocket\Buffer\Config(
                    [
                        'config_dir_path' => WP_CONTENT_DIR . '/wp-rocket-config/',
                    ]
                    )
                );
    
                if(!$rocket_is_cache_enabled->can_init_process())
                {
                    // Don't do avlabs optimization if Rocket is not cache is not enabled on this page 
                    return ;
                }
            }
            else
            {
                return;
            }
        }

        $this->optimization_settings = get_option( 'avlabs_speed_opt_settings' );
    
        if(empty($this->optimization_settings))
        {
            return ;
        }

        if(!empty($this->optimization_settings['exclude_urls']))
        {
            
            $exclude_urls = $this->optimization_settings['exclude_urls'] ;
            if(!empty($exclude_urls))
            {
                $request_uri = $_SERVER['REQUEST_URI'] ;
                $request_uri = explode( '?', $request_uri );
                $request_uri = reset( $request_uri ); 
                if (is_array($exclude_urls) || is_object($exclude_urls)) {
                    foreach($exclude_urls as $url)
                    {
                        if(strpos($request_uri, $url)!== false)
                        {
                            return;
                        }
                    }
                }
            }
            
        }

        require_once AVLABS_PLUGIN_DIR .'includes/jsmin.php' ;
        
        //Pre Optimization
        add_filter('rocket_buffer', array( $this,'pre_optimization'), 0) ;
       
        // Sanitize rocket html 
        add_filter('rocket_buffer', array( $this,'sanitize_rocket_html'),40) ;
        
        //CSS Optimization
        add_action( 'wp_print_styles', array( $this,'do_enqueue_styles'), 9999 );
        add_filter('rocket_css_url', array( $this,'rocket_css_url'),100,2) ;
        add_filter('rocket_buffer', array( $this,'critical_css_before_rocket'), 50) ;
        add_filter('rocket_buffer', array( $this,'critical_css_after_rocket'), 10) ;
        add_filter('rocket_buffer', array( $this,'bg_none_css'), 105) ;
        //add_filter('rocket_buffer', array( $this,'preload_css'), 106) ;
        add_filter('rocket_buffer', array( $this,'preload_fonts'), 107) ;
        add_filter('rocket_buffer', array( $this,'css_optimization'), 100) ;
        
        //JS Optimization
        add_filter( 'rocket_minify_excluded_external_js', array( $this,'exclude_external_js') );

        add_filter('rocket_excluded_inline_js_content' , array( $this,'excluded_js_inline'),100) ;
        
        add_filter( 'rocket_exclude_js', array( $this,'exclude_js_files') );
        add_action( 'wp_enqueue_scripts', array( $this,'do_enqueue_scripts'), 9999 );
        add_filter('rocket_js_url', array( $this,'rocket_js_url'),100,2) ;
        add_filter('rocket_buffer', array( $this,'js_optimization'), 99) ;
        
        //Post Optimization 
        add_filter('rocket_buffer', array( $this,'post_optimization'),200) ;
    }


    public function is_external_script($url)
    {
        $url_array = parse_url($url);
        return !empty($url_array['host']) && strcasecmp($url_array['host'], $_SERVER['HTTP_HOST']);
    }

    public function get_file_path($url)
    {
        $site_url =  site_url();
        if(strpos($url ,$site_url) !== false)
        {
            $url =  str_replace($site_url,'', $url);
            $url_array = parse_url($url);
            return str_replace('//', '/' , ABSPATH . $url_array['path'] );
        }
        else
        {
            $url_array = parse_url($url);
            return $_SERVER["DOCUMENT_ROOT"] . $url_array['path'] ;
        }
    }

    /// Pre Optimization //
    public function pre_optimization($html)
    {
        $pre_html_manipulations = $this->optimization_settings['html_manipulation'];
        if (is_array($pre_html_manipulations) || is_object($pre_html_manipulations)) {
            foreach($pre_html_manipulations as $html_manipulation)
            {
                if($html_manipulation['action']=='pre')
                {
                    $html = str_replace($html_manipulation['from'], $html_manipulation['to'] , $html) ;
                }    
            }
        }
        return $html;
    }

    /// Sanitize rocket html for better css/js optimization
    public function sanitize_rocket_html($html)
    {
        // Replace rocket-critical-css with avlabs-rocket-critical-css
        $html = str_replace(array('id="rocket-critical-css"', 'id=\'rocket-critical-css\'' , 'id=rocket-critical-css' ),array('id="avlabs-rocket-critical-css"', 'id="avlabs-rocket-critical-css"' , 'id="avlabs-rocket-critical-css"' ) , $html ) ;
        /// exclude css link from noscript tag
        preg_match_all('/(<noscript>(.|\n)*?<\/noscript>)/' , $html , $noscripts,PREG_SET_ORDER);
        if (is_array($noscripts) || is_object($noscripts)) {
            foreach($noscripts as $noscript)
            {
                $noscript_new_tag = str_replace('<link', '<link data-avlabs-exclude-css="1" ', $noscript[0] ) ;
                $html = str_replace($noscript[0],$noscript_new_tag , $html ) ;
            }
        }
        
        ///exclude media=print and include rest of the links for optimization 
        $pattern  = '(<link[^>]*[^>]*>)';
        
        preg_match_all( '/' . $pattern . '/Umsi', $html , $all_styles, PREG_SET_ORDER );
        //$html='';
        if (is_array($all_styles) || is_object($all_styles)) {
            foreach($all_styles as $style)
            {
                /// first check if the link is for a css
                
                if((strpos($style[0],'.css' )!== false || strpos($style[0],'fonts.googleapis.com/css' )!== false )  && strpos($style[0] , 'data-avlabs-exclude-css')===false ) 
                {
                
                    $style_new_tag = str_replace(array('onload=' , 'rel=\'preload\'' , 'rel="preload"' , 'rel=preload' , 'as=\'style\'' , 'as="style"' , 'as=style' ) , array('data-onload=' , 'rel="stylesheet"' , 'rel="stylesheet"' , 'rel="stylesheet"' , '' , '' , ''  ) , $style[0]  );
                    
                    if((strpos($style_new_tag,'media=\'print\'' )=== false) && 
                        (strpos($style_new_tag,'media="print"' )=== false) &&  
                        (strpos($style_new_tag,'media=print' )=== false)
                    )
                    {
                        $style_new_tag = str_replace('<link', '<link data-avlabs-css="1" ', $style_new_tag );
                    }

                    // add /> at the end of string if its > only 
                    if((strpos($style_new_tag,'/>' )=== false) )
                    {
                        $style_new_tag = str_replace('>', '/>', $style_new_tag );
                    }

                    $html = str_replace($style[0],$style_new_tag , $html ) ;
                }   
                
            }
        }
    
        return $html ;

    }

    //// Start of CSS Optimization Section
    public function do_enqueue_styles()
    {
        
        $dequeue_styles = $this->optimization_settings['css_dequeue_handler'] ;
        //$enqueue_styles,$rocket_config_class ;
        $enqueue_styles = $this->optimization_settings['css_enqueue_handler'] ;
    
        if(!empty($dequeue_styles))
        {
            if (is_array($dequeue_styles) || is_object($dequeue_styles)) {
                foreach($dequeue_styles as $style)
                {
                    wp_dequeue_style($style) ;
                    wp_deregister_style($style) ;
                }
            }
        }
        
        if(!empty($enqueue_styles))
        {
            if (is_array($enqueue_styles) || is_object($enqueue_styles)) {
                foreach($enqueue_styles as  $style)
                {

                    wp_register_style($style['handler_name'],AVLABS_CSS_SECONDARY_URL.$style['file']);
                    wp_enqueue_style($style['handler_name'],AVLABS_CSS_SECONDARY_URL.$style['file']);
                }
            }
        }
        
    }

    public function rocket_css_url($url, $original_url='')
    {
        if(!empty($original_url))
            return ;

        $this->main_css_url = $url ;
        return $url;

    }

    public function build_primary_secondary_css($html)
    {   
        
        $is_primary_css = false;
        $styles =array();
        $unique_styles =array();
        
        $pattern  = '(<link[^>]*data-avlabs-css[^>]*>)';
        
        preg_match_all( '/' . $pattern . '/Umsi', $html , $all_styles, PREG_SET_ORDER );
        
        if (is_array($all_styles) || is_object($all_styles)) {
            foreach ( $all_styles as $style ) 
            {
                preg_match('/<link(.*?)href\s*=\s*[\'"]\s*?([^\'"]+(?:\?[^\'"]*)?)(.*?)\/>/', $style[0], $temp_style);
                $styles[] = array( 0 => $temp_style[0] , 2=>$temp_style[2]);
                
            }
        }
        if (is_array($styles) || is_object($styles)) {
            foreach ( $styles as $style ) 
            {
                
                $is_primary_css = false;
                if (is_array($this->primary_css_tokens) || is_object($this->primary_css_tokens)) {
                    foreach($this->primary_css_tokens as $css)
                    {
                        if(strpos($style[2],$css )!==false)
                        {
                            $is_primary_css = true;
                            $this->primary_css[] = $style[2] ;
                            break;
                        }
                        
                    }
                }

                if(!$is_primary_css)
                {
                    //if(strpos($style[2],'fonts.googleapis.com') === false)
                    {
                        $this->secondary_css[] = $style[2] ;
                    }
                }
                
                $html = str_replace( $style[0], '', $html );
            }
        }

        if (is_array($this->exclude_css) || is_object($this->exclude_css)) {
            foreach($this->exclude_css as $which =>$css_to_be_excluded)
            {

                if($which == 'primary')
                {
                    if (is_array($css_to_be_excluded) || is_object($css_to_be_excluded)) {
                        foreach($css_to_be_excluded as $css)
                        {
                            if (is_array($this->primary_css) || is_object($this->primary_css)) {
                                foreach($this->primary_css as $key => $primary_css)
                                {
                                    if(strpos($primary_css, $css) !==false )
                                    {
                                        unset($this->primary_css[$key]);
                                        break; 
                                    }
                                }
                            }
                            
                        }
                    }
                }
                elseif($which == 'secondary')
                {
                    if (is_array($css_to_be_excluded) || is_object($css_to_be_excluded)) {
                        foreach($css_to_be_excluded as $css)
                        {
                            if (is_array($this->secondary_css) || is_object($this->secondary_css)) {
                                foreach($this->secondary_css as $key => $secondary_css)
                                {
                                    if(strpos($secondary_css, $css) !==false )
                                    {
                                        unset($this->secondary_css[$key]);
                                        break; 
                                    }
                                }
                            }
                            
                        }
                    }
                }

            }
        }
        return $html;  
    }
    
    public function manipulate_main_css()
    {
       
        
        $main_css_file = $this->get_file_path($this->main_css_url);
        if(file_exists($main_css_file))
        {
            if(!empty($this->css_manipulations))
            {
                $main_css_content = file_get_contents($main_css_file);
                if (is_array($this->css_manipulations) || is_object($this->css_manipulations)) {
                    foreach($this->css_manipulations as $from => $to)
                    {
                        $main_css_content = str_replace($from , $to,$main_css_content  ) ;
                    }
                }

                file_put_contents($main_css_file ,$main_css_content  ) ;
                
            }
            
        }
    }
        
    public function defer_css()
    {
        
        $load_primary_css_in_milliseconds = (!empty($this->optimization_settings['css_primary_load_time'])) ? $this->optimization_settings['css_primary_load_time'] : 300 ;

        $load_secondary_css_in_milliseconds = (!empty($this->optimization_settings['css_scondary_load_time'])) ? $this->optimization_settings['css_scondary_load_time'] : 10000;

        $script ='
        var avlabs_load_css_immediately =false ;
        var avlabs_primary_css = [] ;
        var avlabs_secondary_css = [] ;
        var avlabs_primary_css_to_be_loaded =[];
        var avlabs_secondary_css_to_be_loaded=[];
        var primary_css_timer, secondary_css_timer;';
        if (is_array($this->primary_css) || is_object($this->primary_css)){
        foreach($this->primary_css as $key => $css_script)
        {
            $script .='
        avlabs_primary_css.push(\''.$css_script.'\');';
        }}

        if (is_array($this->secondary_css) || is_object($this->secondary_css)){
        foreach($this->secondary_css as $key => $css_script)
        {
            $script .='
        avlabs_secondary_css.push(\''.$css_script.'\');';
        }}
        
        $script .='

        
        function avlabs_css_loader( which,callback) 
        {
            if(which==\'primary\' )
            {
                if(avlabs_primary_css_to_be_loaded==\'\')
                {
                    return;
                }
                else
                {
                    //clearInterval
                    if( typeof primary_css_timer !== "undefined" )
                    {
                            clearInterval(primary_css_timer)
                    }
                    scripts = avlabs_primary_css_to_be_loaded;
                }
                
            }

            if(which==\'secondary\' )
            {
                if(avlabs_secondary_css_to_be_loaded==\'\')
                {
                    return;
                }
                else
                {
                    //clearInterval
                    if( typeof secondary_css_timer !== "undefined" )
                    {
                        clearInterval(secondary_css_timer)
                    }
                    scripts = avlabs_secondary_css_to_be_loaded;
                }
                
            }    

            var count = scripts.length;
            function urlCallback(url) {
                return function () {
                    
                    console.log(url + \' was loaded (\' + --count + \' more \'+which+\' css remaining).\');
                    if (count < 1) {
                        //callback();
                        if(which ==\'primary\' && avlabs_load_css_immediately==true)
                        {
                            avlabs_secondary_css_to_be_loaded = avlabs_secondary_css;  
                        }
                        
                    }
                };
            }
            var avlabs_atfcss = document.getElementById(\''.$this->critical_css_node.'\');
            
            function loadScript(url) {
                var css = document.createElement("link");
                css.rel = "stylesheet";
                css.href = url;
                css.media = "all";
                css.type = "text/css";
                css.onload = urlCallback(url);
                
                //document.getElementsByTagName(\'head\')[0].appendChild(css);
                avlabs_atfcss.parentNode.insertBefore(css,avlabs_atfcss.nextSibling);
                
            }       

            for (var i = 0; i<scripts.length; i++) {
                loadScript(scripts[i]);
            }
        };
        
        if(avlabs_primary_css!=\'\')
        {
            setTimeout(function(){
            avlabs_primary_css_to_be_loaded = avlabs_primary_css;
            }
        ,'.$load_primary_css_in_milliseconds.');
            if( typeof primary_css_timer === "undefined" )
            {
                primary_css_timer = setInterval(function(){
                    avlabs_css_loader(\'primary\', function() {
                    });
                },200);
            }
        }

        if(avlabs_secondary_css !=\'\' )
        {
            setTimeout(function(){
                avlabs_secondary_css_to_be_loaded = avlabs_secondary_css;
            }
        ,'.$load_secondary_css_in_milliseconds.');
            if( typeof secondary_css_timer === "undefined" )
            {
                secondary_css_timer = setInterval(function(){
                    avlabs_css_loader(\'secondary\', function() {
                    });
                },200);
            }
        }

        function avlabs_clear_timeout_load_css_script()
        {
            if(avlabs_primary_css!=\'\' )
            {
                if(avlabs_primary_css_to_be_loaded ==\'\')
                {
                    avlabs_primary_css_to_be_loaded = avlabs_primary_css;
                    avlabs_load_css_immediately=true;
                }
                else
                {
                    avlabs_secondary_css_to_be_loaded = avlabs_secondary_css;
                }
                    
            }
            else if(avlabs_secondary_css!=\'\')
            {
                avlabs_secondary_css_to_be_loaded = avlabs_secondary_css;
            }

        }

        window.addEventListener("scroll", function(){
            avlabs_clear_timeout_load_css_script();
        });

        window.addEventListener("mousemove", function(){ 
            avlabs_clear_timeout_load_css_script();
        });

        window.addEventListener("touchstart", function(){ 
            avlabs_clear_timeout_load_css_script();
        }); 
            
        '  ;

            return $script ;
    }


    public function preload_fonts($html)
    {
       
        $preload_fonts = '';
        //// Get all preload fonts
        $preload_fonts_temp =  array_slice(scandir(AVLABS_FONTS_PRELOAD_DIR), 2);  
        
        if(!empty($preload_fonts_temp))
        {
            if (is_array($preload_fonts_temp) || is_object($preload_fonts_temp)){
                foreach($preload_fonts_temp as $key=> $fonts)
                {
                    if(strpos($fonts, 'index.php')===false)
                    {

                        $fonts_link = AVLABS_FONTS_PRELOAD_URL.$fonts ;
                    
                        $preload_fonts .='<link rel="preload" href="'.$fonts_link.'" as="font" crossorigin />' ;
                    }
                }
            }
        }
        
        $html = preg_replace( '#</title>#iU', '</title>'. $preload_fonts  , $html, 1 );
        
        return $html ;
    }

    public function bg_none_css($html)
    {
        $bg_none_css = (!empty($this->optimization_settings['css_bg_none'])) ? $this->optimization_settings['css_bg_none'] : '' ;
        $html = preg_replace( '#</title>#iU', '</title><style id="avlabs-lazy-load-bg">'. $bg_none_css.'</style>'  , $html, 1 );
        
        return $html ;
    }

    public function critical_css_before_rocket($html)
    {
        $pattern  = '<body(.*)class=[\'|"](.*)[\'|"]>';
        preg_match_all( '/' . $pattern . '/Umsi', $html , $body, PREG_SET_ORDER );
        
        //// GET CRITICAL CSS 
        $custom_critical_css = (!empty($this->optimization_settings['critical_css_before_rocket'])) ? $this->optimization_settings['critical_css_before_rocket'] : '' ;
        $critical_css_for_post = ''; 
        $critical_css_for_body_class_arr = $this->optimization_settings['critical_css_for_body_class'] ;
        
        if(!empty($critical_css_for_body_class_arr))
        {
            if (is_array($critical_css_for_body_class_arr) || is_object($critical_css_for_body_class_arr)) {
                foreach($critical_css_for_body_class_arr as $critical_css)
                {
                    
                    if(!empty($body) && !empty($body[0]) && !empty($body[0][2]))
                    {
                        $classes_arr = explode(',', $critical_css['classes']) ;
                    
                        if(!empty($classes_arr))
                        {
                            if (is_array($classes_arr) || is_object($classes_arr)){
                                foreach($classes_arr as $class)
                                {
                                    if(strpos($body[0][2] , trim($class))!==false)
                                    {
                                        $critical_css_for_post  .=  $critical_css['css'];
                                        break;
                                    }  
                                }
                            }
                        }
                    
                    }
                
                }
            }
        }

        $custom_critical_css ='<style id="avlabs-custom-critical-css-before-rocket">'. $critical_css_for_post. $custom_critical_css. '</style>';
        $html = preg_replace( '#</title>#iU', '</title>'.  $custom_critical_css , $html, 1 );
        return $html ;
    }

    public function critical_css_after_rocket($html)
    {
        //// GET CRITICAL CSS 
        $custom_critical_css = (!empty($this->optimization_settings['critical_css_after_rocket'])) ? $this->optimization_settings['critical_css_after_rocket'] : '' ;
        

        $custom_critical_css ='<style id="avlabs-custom-critical-css-after-rocket">'. $custom_critical_css .'</style>';
        $html = preg_replace( '#</title>#iU', '</title>'.  $custom_critical_css , $html, 1 );
        return $html ;
    }

    public function css_optimization($html)
    {
        
        if(strpos( $html, 'id="avlabs-rocket-critical-css"' ) !== false 
            ||  
            strpos( $html ,'id=\'avlabs-rocket-critical-css\'' ) !== false
            ||  
            strpos( $asohtmlft_html, 'id=avlabs-rocket-critical-css') !== false
        )
        {
            $this->critical_css_node = 'avlabs-rocket-critical-css';
        }
        else
        {
            $this->critical_css_node = 'avlabs-custom-critical-css-before-rocket';
        }
        // remove load event from all css
        $html = $this->build_primary_secondary_css($html);
    
        $html = str_replace('</body>', '<script>'.$this->defer_css().'</script></body>',$html);
        return $html ;
    } 
    //// End of CSS Optimization Section


    //// Start of Javascript Optimization Section

    public function exclude_external_js($excluded_external )
    {
        
        $excluded_external_arr = $this->optimization_settings['exclude_external_js_files'];

        $excluded_external = array_merge( $excluded_external, $excluded_external_arr 
        );
        
        return $excluded_external ;
    }

    public function exclude_js_files($excluded_files )
    {
        
        $exclude_js_files_arr = $this->optimization_settings['exclude_js_files'];

        $excluded_files = array_merge( $excluded_files, $exclude_js_files_arr 
        );
        
        return $excluded_files ;
    }

    public function excluded_js_inline($excluded_inline)
    {
        $exclude_inline_js = $this->optimization_settings['exclude_inline_js'];

        $excluded_inline = array_merge( $excluded_inline, $exclude_inline_js );
        
        return $excluded_inline ;
    }

    public function do_enqueue_scripts()
    {
        $dequeue_scripts = $this->optimization_settings['dequeue_handler'] ;
        $enqueue_scripts = $this->optimization_settings['enqueue_handler'] ;
            
        if(!empty($dequeue_scripts))
        {
            if (is_array($dequeue_scripts) || is_object($dequeue_scripts)){
                foreach($dequeue_scripts as $script)
                {
                    wp_dequeue_script($script) ;
                    wp_deregister_script($script) ;
                }
            }
        }
        
        if(!empty($enqueue_scripts))
        {
            if (is_array($enqueue_scripts) || is_object($enqueue_scripts)){
                foreach($enqueue_scripts as  $script)
                {

                    wp_register_script($script['handler_name'],AVLABS_JS_URL.$script['file']);
                    wp_enqueue_script( $script['handler_name'],AVLABS_JS_URL.$script['file']) ;
                }
            }
        }
        
        // enqueue pre/post primary/secondary scripts 
        if(file_exists(AVLABS_JS_DIR.'pre-primary.js'))
        {
            wp_register_script('avlabs-pre-primary',AVLABS_JS_URL.'pre-primary.js');
            wp_register_script('avlabs-pre-primary',AVLABS_JS_URL.'pre-primary.js');
        }

        if(file_exists(AVLABS_JS_DIR.'post-primary.js'))
        {
            wp_register_script('avlabs-post-primary',AVLABS_JS_URL.'post-primary.js');
            wp_register_script('avlabs-post-primary',AVLABS_JS_URL.'post-primary.js');
        }

        if(file_exists(AVLABS_JS_DIR.'pre-secondary.js'))
        {
            wp_register_script('avlabs-pre-secondary',AVLABS_JS_URL.'pre-secondary.js');
            wp_register_script('avlabs-pre-secondary',AVLABS_JS_URL.'pre-secondary.js');
        }

        if(file_exists(AVLABS_JS_DIR.'post-secondary.js'))
        {
            wp_register_script('avlabs-post-secondary',AVLABS_JS_URL.'post-secondary.js');
            wp_register_script('avlabs-post-secondary',AVLABS_JS_URL.'post-secondary.js');
        }
    }

    public function rocket_js_url($url, $original_url='')
    {
        
        if(!empty($original_url))
            return ;
        $this->secondary_script_url = $url ;
        $url_parts = parse_url($url);
        $ext = pathinfo($url_parts['path'], PATHINFO_EXTENSION);
        $secondary_js_base_name = basename( $this->secondary_script_url ) ;
        $js_cache_url = str_replace($secondary_js_base_name , '', $this->secondary_script_url );
        $this->primary_script_url =$js_cache_url ."PMD5" ;

        if (preg_match( '/[-.]min\.'.$ext.'/iU', $url ) ) 
        {
            $this->primary_script_url .= $this->primary_script_suffix . '.min.' . $ext ;
        }
        else
        {
            $this->primary_script_url .= $this->primary_script_suffix . '.' . $ext ;
        }
        
        return $url;
    }

    public function set_urls_of_scripts_at_key($html)
    {
        if (is_array($this->primary_js_sort_order) || is_object($this->primary_js_sort_order)){
            foreach($this->primary_js_sort_order as $key => $val)
            {
                if($val =='primary_script_position' )
                {
                    $this->primary_script_index  = $key ;
                    $this->primary_js_sort_order[$key] = $this->primary_script_url ;
                    break;
                }
            }
        }    

        if (is_array($this->secondary_js_sort_order) || is_object($this->secondary_js_sort_order)){
            foreach($this->secondary_js_sort_order as $key => $val)
            {
                if($val =='secondary_script_position' )
                {
                    $this->secondary_js_sort_order[$key] = $this->secondary_script_url ;
                    break;
                }
            }
         }   
        
        if (is_array($this->secondary_js_merge) || is_object($this->secondary_js_merge)){
            foreach($this->secondary_js_merge as $key => $val)
            {
                if($val =='secondary_script_position' )
                {
                    $this->secondary_js_merge[$key] = $this->secondary_script_url ;
                    break;
                }
            }
        }
        
        if (is_array($this->secondary_js_sort_order) || is_object($this->secondary_js_sort_order)){
            foreach($this->secondary_js_sort_order as $key => $val)
            {
                if($val =='secondary_script_position' )
                {
                    $this->secondary_js_sort_order[$key] = $this->secondary_script_url ;
                    break;
                }
            }
        }

        return $html;
    }

    public function build_all_scripts($html)
    {
       
        $pattern  = '<script.*<\/script>';
        preg_match_all( '/' . $pattern . '/Umsi', $html , $scripts, PREG_SET_ORDER );
        $this->all_scripts[100]='';
        $this->all_scripts = array_map( 
            function($script)
            {
                preg_match( '/<script\s+([^>]+[\s\'"])?src\s*=\s*[\'"]\s*?([^\'"]+\.js(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>/Umsi', $script[0], $matches );

                if ( isset( $matches[2] ) ) 
                {
                    if($this->is_external_script($matches[2])) 
                    {
                        $script=    [   
                                        'type'=> 'external',
                                        'url' =>  $matches[2],
                                        'tag'=> $script[0]
                                    ] ;
                        return $script ;
                    }

                    $file_path = $this->get_file_path( $matches[2] );

                    if ( ! $file_path ) {
                        return;
                    }

                    $script =   [
                                    'type'    => 'file',
                                    'content' => $file_path,
                                    'url' =>  $matches[2],
                                    'tag'=> $script[0]
                                ];
                    return $script;
                } 
                else 
                {
                    preg_match( '/<script\b(?<attrs>[^>]*)>(?:\/\*\s*<!\[CDATA\[\s*\*\/)?\s*(?<content>[\s\S]*?)\s*(?:\/\*\s*\]\]>\s*\*\/)?<\/script>/msi', $script[0], $matches_inline );

                    $matches_inline = array_merge(
                        [
                            'attrs'   => '',
                            'content' => '',
                        ],
                        $matches_inline
                    );
                    if(!empty($matches_inline['content']))    
                    {
                        $script =   [
                                    'type'    => 'inline',
                                    'content' => $matches_inline['content'],
                                    'tag'=> $script[0]
                                ];
                    } 
                    else
                    {
                        preg_match( '/<script\s+([^>]+[\s\'"])?src\s*=\s*[\'"]\s*?([^\'"]+(?:\?[^\'"]*)?)\s*?[\'"]([^>]+)?\/?>/Umsi', $script[0], $matches );

                        if($this->is_external_script($matches[2])) 
                        {
                            $script=    [   
                                            'type'=> 'external',
                                            'url' =>  $matches[2],
                                            'tag'=> $script[0]
                                        ] ;
                            return $script ;
                        }

                        $file_path = $this->get_file_path( $matches[2] );

                        if ( ! $file_path ) {
                            return;
                        }

                        $script =   [
                                        'type'    => 'file',
                                        'content' => $file_path,
                                        'url' =>  $matches[2],
                                        'tag'=> $script[0]
                                    ];
                        
                    }   
                    return $script ;
                }

                
            }
            , $scripts );

        array_filter($this->all_scripts); 
        return $html ;  
    }

    public function merge_primary_scripts($html)
    {
        $combined_minified_primary_scripts = '' ;
        $is_script_found = false ;
        
        if (is_array($this->primary_js_merge) || is_object($this->primary_js_merge)) {
            foreach($this->primary_js_merge as $js)
            {
                $is_script_found = false ;
                if (is_array($this->all_scripts) || is_object($this->all_scripts)){
                    foreach($this->all_scripts as $key =>$script)
                    {
                        if ( $script['type']=='file' && false !== strpos( $script['url'], $js ) )
                        {
                            $script_content = file_get_contents($script['content']) ;
                            if(strpos($combined_minified_secondary_scripts ,  $script_content) ===false)
                            {
                                if(!empty($script_content))
                                {
                                    $combined_minified_primary_scripts .= ($script_content) ;
                                    if(substr( $script_content, -1) !=';')
                                    {
                                        $combined_minified_secondary_scripts .= ';' ;
                                    }
                                }
                            }

                            $is_script_found = true ;
                        }
                        elseif($script['type']=='inline' && false !== strpos( $script['content'], $js ))
                        {
                            $script_content = rtrim( $script['content'], ";\n\t\r" ) ;
                            if(strpos($combined_minified_secondary_scripts ,  $script_content) ===false)
                            {
                                if(!empty($script_content))
                                {
                                    $combined_minified_primary_scripts .=($script_content) ;
                                    if(substr( $script_content, -1) !=';')
                                    {
                                        $combined_minified_secondary_scripts .= ';' ;
                                    }
                                }
                            }
                            $is_script_found = true ;
                        }

                        if($is_script_found)
                        {
                            $html = str_replace( $script['tag'], '', $html );
                            unset($this->all_scripts[$key]);
                            break;
                        }
                    }
                }
            }
        }

        
        $primary_script_md5 = md5($combined_minified_primary_scripts) ;
        $primary_script_path = $this->get_file_path( $this->primary_script_url);
        
        $this->primary_script_url = str_replace('PMD5',$primary_script_md5 ,$this->primary_script_url) ;
        $primary_script_path = str_replace('PMD5',$primary_script_md5 ,$primary_script_path) ;
        
        $this->primary_js_sort_order[$this->primary_script_index] = $this->primary_script_url ; 
        
        file_put_contents($primary_script_path,$combined_minified_primary_scripts);
        $this->all_scripts[] =  [
                                    'type'=>'file',
                                    'content'=>$primary_script_path,
                                    'url'=> $this->primary_script_url,
                                ];
        return $html;
    }

    public function merge_secondary_scripts($html)
    {
        
        $secondary_script_path = $this->get_file_path( $this->secondary_script_url);
        $secondary_script_content = file_get_contents($secondary_script_path);
        $combined_minified_secondary_scripts = '' ;
        $is_script_found = false ;
        
        if (is_array($this->secondary_js_merge) || is_object($this->secondary_js_merge)){
        foreach($this->secondary_js_merge as $js)
        {
            $is_script_found = false ;
            if (is_array($this->all_scripts) || is_object($this->all_scripts)){
            foreach($this->all_scripts as $key =>$script)
            {
                if ( $script['type']=='file' && false !== strpos( $script['url'], $js ) )
                {
                    $script_content = file_get_contents($script['content']) ;
                    if($script['url'] != $this->secondary_script_url)
                    {
                        if(strpos($secondary_script_content ,  $script_content) ===false)
                        {
                            if(!empty($script_content))
                            {
                                $combined_minified_secondary_scripts .= $script_content ;
                                if(substr( $script_content, -1) !=';')
                                {
                                    $combined_minified_secondary_scripts .= ';' ;
                                }
                            }
                        }
                    }else {
                            if(!empty($script_content))
                            {
                                $combined_minified_secondary_scripts .= $script_content ;
                                if(substr( $script_content, -1) !=';')
                                {
                                    $combined_minified_secondary_scripts .= ';' ;
                                }
                            }
                    }
                    
                    $is_script_found = true ;
                }
                elseif($script['type']=='inline' && false !== strpos( $script['content'], $js ))
                {
                    $script_content = rtrim( $script['content'], ";\n\t\r" ) ;
                    if(strpos($secondary_script_content ,  $script_content) ===false)
                    {
                        if(!empty($script_content))
                        {
                            $combined_minified_secondary_scripts .= $script_content;
                            if(substr( $script_content, -1) !=';')
                            {
                                $combined_minified_secondary_scripts .= ';' ;
                            }
                        }
                    }
                    $is_script_found = true ;
                }

                if($is_script_found)
                {
                    $html = str_replace( $script['tag'], '', $html );
                    if($script['url'] != $this->secondary_script_url)
                    {
                        unset($this->all_scripts[$key]);
                    }
                    break;
                }
            }}
        }}
        
        file_put_contents($secondary_script_path,$combined_minified_secondary_scripts);
        if(file_exists($secondary_script_path.'.gz'))
        {
            unlink($secondary_script_path.'.gz');
        }
        return $html;
    }

    public function do_script_manipulation($html)
    {
        
        $script_manipulations = $this->optimization_settings['script_manipulation'];
        
        if(!empty($script_manipulations))
        {
            $primary_script_path = $this->get_file_path( $this->primary_script_url); 
            $secondary_script_path = $this->get_file_path( $this->secondary_script_url); 
            $primary_script_content = file_get_contents($primary_script_path) ;
            $secondary_script_content = file_get_contents($secondary_script_path) ; 

            if (is_array($script_manipulations) || is_object($script_manipulations)) {
            foreach($script_manipulations as $manipulation)
            {
                if($manipulation['file']=='primary')
                {
                    $do_manipulation_on = & $primary_script_content;
                }
                else
                {
                    $do_manipulation_on = & $secondary_script_content;
                }    
            
                switch($manipulation['action'])
                {
                    case 'move-start':
                        {
                            if(strpos($do_manipulation_on,$manipulation['destination']) === false)
                            {
                                $do_manipulation_on = str_replace($manipulation['source'] , '', $do_manipulation_on);
                                $do_manipulation_on = $manipulation['destination'] .  $do_manipulation_on;
                            }
                            break ;
                        }
                    case 'move-last':
                        {
                            if(strpos($do_manipulation_on,$manipulation['destination']) === false)
                            {
                                $do_manipulation_on = str_replace($manipulation['source'] , '', $do_manipulation_on);
                                $do_manipulation_on .= $manipulation['destination'] ;
                            }
                            break ;
                        }
                    case 'replace-in-place':
                        {
                            $do_manipulation_on = str_replace($manipulation['source'] , $manipulation['destination'], $do_manipulation_on);
                            
                            break ;
                        }
                }
            }
        }
        
            file_put_contents($primary_script_path,$primary_script_content);
            file_put_contents($secondary_script_path,$secondary_script_content);
        }
        return $html ;
    }

    public function build_primary_scripts($html)
    {

           if (is_array($this->primary_js_sort_order) || is_object($this->primary_js_sort_order))    {
        foreach($this->primary_js_sort_order as $js)
        {
            $is_script_found = false ;
            if (is_array($this->all_scripts) || is_object($this->all_scripts)){
            foreach($this->all_scripts as $key =>$script)
            {
                if ( false !== strpos( $script['url'], $js)  )
                {
                    $this->primary_scripts[] = $script['url'];
                    $is_script_found=true;
                }
                if($is_script_found)
                {
                    $html = str_replace( $script['tag'], '', $html );
                    unset($this->all_scripts[$key]);
                    break;
                }
            }
        }
        }
    }

        return $html;
    }

    public function build_secondary_scripts($html)
    {
               
        if (is_array($this->secondary_js_sort_order) || is_object($this->secondary_js_sort_order)) {
        foreach($this->secondary_js_sort_order as $js)
        {
            $is_script_found = false ;
            if (is_array($this->all_scripts) || is_object($this->all_scripts)) {
            foreach($this->all_scripts as $key =>$script) 
            {
                if ( false !== strpos( $script['url'], $js)  )
                {
                    $this->secondary_scripts[] = $script['url'];
                    $is_script_found=true;
                }
                if($is_script_found)
                {
                    $html = str_replace( $script['tag'], '', $html );
                    unset($this->all_scripts[$key]);
                    break;
                }
            }
        }
        }
    }
        return $html;
        
    }

    public function do_scripts_url_manipulation($html)
    {
                
        $script_url_manipulations = $this->optimization_settings['script_url_manipulation'] ;

        if (is_array($script_url_manipulations) || is_object($script_url_manipulations)) {
        foreach($script_url_manipulations as $manipulations)
        {
            $which = $manipulations['file'];
            if($which == 'primary')
                {

                    if (is_array($this->primary_scripts) || is_object($this->primary_scripts)) {
                    foreach($this->primary_scripts as $key=>$script)
                    {
                        if(strpos($script, $manipulations['from'])!== false)
                        {
                            $this->primary_scripts[$key] = str_replace($manipulations['from'], $manipulations['to'],$this->primary_scripts[$key]  ) ;
                        }
                    
                    }
                }
                }
                else
                {
                    if (is_array($this->secondary_scripts) || is_object($this->secondary_scripts)) {
                    foreach($this->secondary_scripts as $key=>$script)
                    {
                        if(strpos($script, $manipulations['from'])!== false)
                        {
                            $this->secondary_scripts[$key] = str_replace($manipulations['from'], $manipulations['to'],$this->secondary_scripts[$key]  ) ;
                        }
                    }
                }
                }

        }
    }
        return $html ;
    }

    public function defer_js()
    {
        
        $load_primary_js_in_milliseconds = (!empty($this->optimization_settings['primary_load_time'])) ? $this->optimization_settings['primary_load_time'] : 1500 ;

        $load_secondary_js_in_milliseconds = (!empty($this->optimization_settings['scondary_load_time'])) ? $this->optimization_settings['scondary_load_time'] : 10000;

        $load_bg_none_in_milliseconds = (!empty($this->optimization_settings['css_bg_load_time'])) ? $this->optimization_settings['css_bg_load_time'] : 2000;

        $logo_div = (!empty($this->optimization_settings['logo_div'])) ? $this->optimization_settings['logo_div'] : '';

        $script ='
        var avlabs_load_scripts_immediately =false ;
        var avlabs_primary_scripts = [] ;
        var avlabs_secondary_scripts = [] ;
        var avlabs_primary_scripts_to_be_loaded =[];
        var avlabs_secondary_scripts_to_be_loaded=[];
        var primary_script_timer, secondary_script_timer;';
       
        if (is_array($this->primary_scripts) || is_object($this->primary_scripts)) {
        foreach($this->primary_scripts as $key => $js_script)
        {
            $script .='
        avlabs_primary_scripts.push(\''.$js_script.'\');';
        }
    }

        if (is_array($this->secondary_scripts) || is_object($this->secondary_scripts)) {
        foreach($this->secondary_scripts as $key => $js_script)
        {
            $script .='
        avlabs_secondary_scripts.push(\''.$js_script.'\');';
        }
    }

        
        if($logo_div!='')
        {
            $script .=' 
        var logo_div = document.getElementsByClassName("'.$logo_div.'")
        
        if(logo_div != null && logo_div.length > 0 )
        {
            var all_logos = logo_div[0].getElementsByTagName("img");
        
            if(all_logos != null && all_logos.length > 0 )
            {
                for(i=0 ; i < all_logos.length ;i++ )
                {
                    src = all_logos[i].getAttribute("data-lazy-src") ;
                    if( src != \'\' && src!=null)
                    {
                        all_logos[i].setAttribute("src", src) ;
                    }

                }
            }
        } ';
        }
        
        $script .='
        setTimeout(function(){
        if( document.getElementById(\'avlabs-lazy-load-bg\') !== null )
        {
            bg_none_css = document.getElementById(\'avlabs-lazy-load-bg\').innerHTML;
                document.getElementById(\'avlabs-lazy-load-bg\').innerHTML = bg_none_css.replaceAll("!important",\'\') ;
        }},'. $load_bg_none_in_milliseconds .');
                
        function avlabs_scripts_loader( which,callback) 
        {
            if(which==\'primary\' )
            {
                if(avlabs_primary_scripts_to_be_loaded==\'\')
                {
                    return;
                }
                else
                {
                   
                    //clearInterval
                    if( typeof primary_script_timer !== "undefined" )
                    {
                        clearInterval(primary_script_timer)
                    }
                    scripts = avlabs_primary_scripts_to_be_loaded;
                }
                
            }

            if(which==\'secondary\' )
            {
                if(avlabs_secondary_scripts_to_be_loaded==\'\')
                {
                    return;
                }
                else
                {
                    //clearInterval
                    if( typeof secondary_script_timer !== "undefined" )
                    {
                        clearInterval(secondary_script_timer)
                    }
                    scripts = avlabs_secondary_scripts_to_be_loaded;
                }
                
            }    

            var count = scripts.length;
            function urlCallback(url) {
                return function () {
                    
                    console.log(url + \' was loaded (\' + --count + \' more \'+which+\' scripts remaining).\');
                    if (count < 1) {
                        //callback();
                        if(which ==\'primary\' && avlabs_load_scripts_immediately==true)
                        {
                            avlabs_secondary_scripts_to_be_loaded = avlabs_secondary_scripts;  
                        }
                        if(document.createEvent){
                            var evt = document.createEvent("MutationEvents"); 
                            evt.initMutationEvent("DOMContentLoaded", true, true, document, "", "", "", 0); 
                            document.dispatchEvent(evt);
                        }
                        if (typeof jQuery != \'undefined\') {
                            jQuery(window).trigger( \'load\' ); 
                            jQuery(window).trigger( \'resize\' );
                        }
                    }
                };
            }

            function loadScript(url) {
                var s = document.createElement(\'script\');
                s.setAttribute(\'src\', url);
                s.onload = urlCallback(url);
                s.async=false;
                document.head.appendChild(s);
            }

            for (var i = 0; i<scripts.length; i++) {
                loadScript(scripts[i]);
            }
        };
        
        if(avlabs_primary_scripts!=\'\')
        {
            primary_time_out = setTimeout(function(){
            avlabs_primary_scripts_to_be_loaded = avlabs_primary_scripts;
            }
        ,'.$load_primary_js_in_milliseconds.');
            if( typeof primary_script_timer === "undefined" )
            {
                primary_script_timer = setInterval(function(){
                    avlabs_scripts_loader(\'primary\', function() {
                    });
                },200);
            }
            
        }

        if(avlabs_secondary_scripts !=\'\' )
        {
            secondary_time_out = setTimeout(function(){
                avlabs_secondary_scripts_to_be_loaded = avlabs_secondary_scripts;
            }
        ,'.$load_secondary_js_in_milliseconds.');
            if( typeof secondary_script_timer === "undefined" )
            {
                secondary_script_timer = setInterval(function(){
                    avlabs_scripts_loader(\'secondary\', function() {
                    });
                },200);
            }
        }

        function avlabs_clear_timeout_load_js_script()
        {
            if(avlabs_primary_scripts!=\'\' )
            {
                if(avlabs_primary_scripts_to_be_loaded ==\'\')
                {
                    avlabs_primary_scripts_to_be_loaded = avlabs_primary_scripts;
                    avlabs_load_scripts_immediately=true;
                }
                else
                {
                    avlabs_secondary_scripts_to_be_loaded = avlabs_secondary_scripts;
                }
                    
            }
            else if(avlabs_secondary_scripts!=\'\')
            {
                avlabs_secondary_scripts_to_be_loaded = avlabs_secondary_scripts;
            }

        }

        window.addEventListener("scroll", function(){
            avlabs_clear_timeout_load_js_script();
        });

        window.addEventListener("mousemove", function(){ 
            avlabs_clear_timeout_load_js_script();
        });

        window.addEventListener("touchstart", function(){ 
            avlabs_clear_timeout_load_js_script();
        }); 
            
        '  ;

            return $script ;
    }

    public function js_optimization($html)
    {
    
        // Get options from worpdress table and set into global
        $this->primary_js_sort_order = $this->optimization_settings['primary_sort'];
        $this->primary_js_merge = $this->optimization_settings['primary_merge'];

        $this->secondary_js_sort_order = $this->optimization_settings['secondary_sort'];
        $this->secondary_js_merge = $this->optimization_settings['secondary_merge'];
        
        $html = $this->set_urls_of_scripts_at_key($html) ;
        $html = $this->build_all_scripts($html);
        $html = $this->merge_primary_scripts($html);
        $html = $this->merge_secondary_scripts($html);
        $html = $this->do_script_manipulation($html);
        $html = $this->build_primary_scripts($html);
        $html = $this->build_secondary_scripts($html);
        $html = $this->do_scripts_url_manipulation($html);
        
        $html = str_replace('</body>', '<script>'.$this->defer_js().'</script></body>',$html);
        return $html ;
    }

    //// End of Javascript Optimization Section 

    /// Post Optimization //
    public function post_optimization($html)
    {
        $pre_html_manipulations = $this->optimization_settings['html_manipulation'];

        if (is_array($pre_html_manipulations) || is_object($pre_html_manipulations)) {
            foreach($pre_html_manipulations as $html_manipulation)
            {
                if($html_manipulation['action']=='post')
                {
                    $html = str_replace($html_manipulation['from'], $html_manipulation['to'] , $html) ;
                }    
            }
        }
        return $html;
    }

    
}// End of main class 

// Create Instance of Avalabs Speed Optimization 
new AvlabsSpeedOptimization();