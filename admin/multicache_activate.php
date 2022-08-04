<?php

/**
 * MulticacheWP
 * http://www.multicache.org
 * High Performance fastcache Controller
 * Version: 1.0.0.6
 * Author: Wayne DSouza
 * Author URI: http://onlinemarketingconsultants.in
 * License: GNU PUBLIC LICENSE see license.txt
 */
class MulticacheConfigure
{

    public static $config_options = array(
        'caching' => 0,
        'cache_handler' => 'file',
    	'storage' => 'fastcache',
        'cachetime' => 1440,
        'multicache_persist' => 1,
        'multicache_compress' => 1,
        'multicache_server_host' => 'localhost',
        'multicache_server_port' => '11211',
        'created' => '',
        'gtmetrix_testing' => 0,
        'gtmetrix_api_budget' => 20,
        'gtmetrix_email' => '',
        'gtmetrix_token' => '',
        'gtmetrix_adblock' => true,
        'gtmetrix_test_url' => '',
        'gtmetrix_allow_simulation' => false,
        'simulation_advanced' => false,
        'jssimulation_parse' => true,
        'gtmetrix_cycles' => 1,
        'precache_factor_min' => 0,
        'precache_factor_max' => 9,
        'precache_factor_default' => 2,
        'ccomp_factor_min' => 0,
        'ccomp_factor_max' => 1,
        'ccomp_factor_step' => 0.1,
        'ccomp_factor_default' => 0.22,
        'googleclientid' => '',
        'googleclientsecret' => '',
        'googleviewid' => '',
        'googlestartdate' => '',
        'googleenddate' => '',
        'googlenumberurlscache' => 200,
        'multicachedistribution' => 3,
        'additionalpagecacheurls' => '',
        'force_locking_off' => 1,
        'indexhack' => 0,
        'conduit_switch' => 0,
    	'conduit_nonce_name' => 'sp-security-nonce',
        'minify_html' => true,
    	'cache_user_loggedin' => 0,
    	'optimize_user_loggedin' => 0,
    	'cache_query_urls' => 1,
    	'optimize_query_urls' => 1,
        'targetpageloadtime' => 3,
        'algorithmavgloadtimeweight' => 0.4,
        'algorithmmodemaxbelowtimeweight' => 0.4,
        'algorithmvarianceweight' => 0.2,
        'urlfilters' => 1,
        'frequency_distribution' => 1,
        'natlogdist' => 1,
        'deployment_method' => 3,
        'cartsessionvariables' => '',
        'cartdifferentiators' => '',
        'cartmode' => 0,
        'cartmodeurlinclude' => '',
    	'countryseg' => 0,
        'js_switch' => 0,
        'default_scrape_url' => '',
        'social_script_identifiers' => '',
        'advertisement_script_identifiers' => '',
        'pre_head_stub_identifiers' => '',
        'head_stub_identifiers' => '</head>',
        'body_stub_identifiers' => '',
        'footer_stub_identifiers' => '</body>',
        'principle_jquery_scope' => 0,
        'principle_jquery_scope_other' => '',
        'dedupe_scripts' => 1,
        'defer_social' => 1,
        'defer_advertisement' => 1,
        'defer_async' => 0,
        'maintain_preceedence' => 1,
        'minimize_roundtrips' => 1,
        'js_comments' => 1,
        'compress_js' => 1,
        'debug_mode' => 0,
        'advanced_simulation_lock' => 1,
        'js_tweaker_url_include_exclude' => 0,
        'jst_urlinclude' => '',
        'jst_query_include_exclude' => 0,
        'jst_query_param' => '',
        'orphaned_scripts' => 4,
        'excluded_components' => '',
        'jst_url_string' => '',
        'force_precache_off' => 0,
        'css_switch' => 0,
        'css_scrape_url' => '',
        'dedupe_css_styles' => 1,
        'css_maintain_preceedence' => 0,
        'group_css_styles' => 1,
        'compress_css' => 1,
        'css_special_identifiers' => '',
        'css_comments' => 1,
        'orphaned_styles_loading' => 2,
        'css_tweaker_url_include_exclude' => 0,
        'css_urlinclude' => '',
        'css_query_include_exclude' => 0,
        'css_query_param' => '',
        'cssexcluded_components' => '',
        'css_url_string' => '',
        'image_lazy_switch' => 0,
        'image_lazy_container_switch' => 0,
        'image_lazy_container_strings' => '',
        'image_lazy_image_selector_include_switch' => 0,
        'image_lazy_image_selector_include_strings' => '',
        'image_lazy_image_selector_exclude_switch' => 0,
        'image_lazy_image_selector_exclude_strings' => '',
        'imagestweaker_url_include_exclude' => 0,
        'images_urlinclude' => '',
        'images_query_include_exclude' => 0,
        'images_query_param' => '',
        'images_url_string' => '',
        'imgexcluded_components' => '',
    	'cache_comment_invalidation' => 1
    );

    protected static $config_vars = array(
        'cache_handler' => 'file',
        
        'cachetime' => '1440',
        
        'caching' => '1',
    		
    	'cache_user_loggedin' => 0,
    		
    	'optimize_user_loggedin' => 0,
    		
    	'cache_query_urls' => 1,
    		
    	'optimize_query_urls' => 1,
        
        'debug' => '0',
        
        'ccomp' => '1',
        
        'lifetime' => '15',
        
        'live_site' => '',
        
        'secret' => '',
        
        'multicacheeditmode' => '1',
        
        'multicachelock' => '0',
        
        'multicache_persist' => '1',
        
        'multicache_compress' => '1',
        
        'multicache_server_host' => 'localhost',
        
        'multicache_server_port' => '11211',
        
        'multicachedebug' => '0',
        
        'multicachedebuggrp' => '',
        
        'multicachedistribution' => '2',
        
        'ccomp_factor' => '0.1',
        
        'precache_factor' => '5',
        
        'multicache_server_port2' => '11233',
        
        'force_locking_off' => '0',
    		
    	'cache_comment_invalidation' => 1,
    		
    	'absolute_path' => null,
    		
    	'plugin_dir_path' => null
    );

    public static function initMulticacheConfig()
    {

        $dir = dirname(dirname(__FILE__));
        require_once (ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
        require_once (ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');
        $multicache_fsd = new WP_Filesystem_Direct(__FILE__);
        @set_time_limit(300);
        $check_is_writable = array();
        if ($multicache_fsd->exists($dir . '/libs/multicache_config.php'))
        {
            Return;
        }
        $config_vars = self::$config_vars;
        ob_start();
        echo "<?php

/**
 * MulticacheWP
 * http://www.multicache.org
 * High Performance fastcache Controller
 * Version: 1.0.0.6
 * Author: Wayne DSouza
 * Author URI: http://onlinemarketingconsultants.in
 * License: GNU PUBLIC LICENSE see license.txt
 */
defined('_MULTICACHEWP_EXEC') or die();

class MulticacheConfig
 {
        ";
        $cl_buf = ob_get_clean();
        foreach ($config_vars as $config_keys => $config_val)
        {
            if ($config_keys == 'secret')
            {
                $config_val = md5(MulticacheUri::root() . date('Y-m-d', strtotime('-1 year')));
            }
            elseif ($config_keys == 'live_site')
            {
                $config_val = MulticacheUri::root();
            }
            elseif ($config_keys == 'absolute_path')
            {
            	$config_val = defined('ABSPATH') ? ABSPATH : null;
            }
            elseif ($config_keys == 'plugin_dir_path')
            {
            	$f_dir = plugin_dir_path(dirname(__FILE__)); //php ver 5.3.0
            	$config_val = !empty($f_dir)? $f_dir: null;
            }
            ob_start();
            echo "\npublic \$$config_keys = '$config_val';";
            $cl_buf .= ob_get_clean();
        }
        ob_start();
        echo "

 }";
        $cl_buf .= ob_get_clean();
        $cl_buf = str_ireplace("\x0D", "", $cl_buf);
        $dir = $dir . '/libs/';
        $filename = 'multicache_config.php';
        if (! $multicache_fsd->put_contents($dir . $filename, $cl_buf, 0644))
        {
            $result = new WP_Error('failed to write multicache config', __('Multicacheconfig could not install this is usually due to inconsistent file permissions.'), $dir . $filename);
            return $result;
        }
        
        $multicache_fsd->chmod($dir . $filename, 0444);
        if ($multicache_fsd->getchmod($dir . $filename) == '444')
        {
            Return true;
        }
        
        Return false;
    
    }
    
    public static function addCachedir()
    {
    	if(!defined('ABSPATH'))
    	{
    		return false;
    	}
    	$success = true;
    	$cache_dir = ABSPATH .'wp-content/cache';
    	if (! is_dir($cache_dir))
    	{
    	
    		// Make sure the index file is there
    		$indexFile = $cache_dir . '/index.html';
    		$success = @mkdir($cache_dir) && file_put_contents($indexFile, '<!DOCTYPE html><title></title>');
    	}
    	
    	Return $success ;
    }

    protected static function getConfigOptions()
    {

        $config_options = self::$config_options;
        $config_options['created'] = date('Y-m-d H:i:s');
        $config_options['gtmetrix_test_url'] = MulticacheUri::root();
        $config_options['googlestartdate'] = date('Y-m-d', strtotime('-1 year'));
        $config_options['googleenddate'] = date('Y-m-d');
        $config_options['social_script_identifiers'] = '';/*MulticacheHelper::processSocialAdIndicators('FB.init
        			assets.pinterest.com
        			platform.twitter.com
        			plusone.js
        		');*/
        $config_options['advertisement_script_identifiers'] = '';/*MulticacheHelper::processSocialAdIndicators('adsbygoogle
        			pagead/js/adsbygoogle.js');*/
        
        $config_options['default_scrape_url'] = strtolower(MulticacheUri::root());
        $config_options['css_scrape_url'] = strtolower(MulticacheUri::root());
        $config_options['pre_head_stub_identifiers'] = MulticacheHelper::encodePregSplit('<head>') ;
        $config_options['head_stub_identifiers'] = MulticacheHelper::encodePregSplit('</head>
        		</HEAD>') ;
        $config_options['body_stub_identifiers'] = MulticacheHelper::encodePregSplit('<body>
        		<body class="someclass">') ;
        $config_options['footer_stub_identifiers'] = MulticacheHelper::encodePregSplit('</body>
        		</BODY>') ;
        $config_options['tolerance_params'] = '{"tolerance_highlighting":1,"danger_tolerance_factor":3,"danger_tolerance_color":"#a94442","warning_tolerance_factor":2.5,"warning_tolerance_color":"#8a6d3b","success_tolerance_color":"#468847"}';
        Return $config_options;
    
    }
    
    

    public static function InitMulticacheConfigAdd($default = null)
    {
//NB we want to ensure options dont autoload
        if (! isset($default))
        {
        	$o_exists = get_option('multicache_config_options');
        	if(!empty($o_exists))
        	{
        		delete_option('multicache_config_options');
        	}
           $success= add_option('multicache_config_options', self::getConfigOptions(), '', 'no'); // autoload set to no -> dont load options on every page load
           if(empty($success))
           {
           	update_option('multicache_config_options' , self::getConfigOptions(), 'no');
           }
        }
        else
        {
          $success=  add_option('multicache_config_options', $default, '', 'no'); // autoload set to no -> dont load options on every page load
          if(empty($success))
          {
          	update_option('multicache_config_options', $default, 'no');
          }
        }
       
        self::addMulticacheUrlArrayTodb();
        self::addMulticacheAdvCCompTodb();
        self::addMulticacheAdvLoadinsTodb();
        self::addMulticacheAdvPrecacheFTodb();
        self::addMulticacheAdvTestGroupsTodb();
        self::addMulticacheAdvTestResultsTodb();
        self::addMulticacheItemsTodb();
        self::addMulticacheItemsSlabsTodb();
        self::addMulticacheItemsStatsTodb();
        
    
    }
    
    public static function addMulticacheUrlArrayTodb()
    {
    	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    	global $wpdb;
    	$tablename = $wpdb->prefix . "multicache_urlarray";
    	$sql = "CREATE TABLE `$tablename` (    			
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL,
  `url_manifest` varchar(255) NOT NULL,
  `cache_id` varchar(255) NOT NULL,
  `cache_id_alt` varchar(255) NOT NULL,
  `cache_id_alt_ext` varchar(255) NOT NULL,
  `views` int(11) NOT NULL,
  `f_dist` double NOT NULL,
  `ln_dist` float NOT NULL,
  `type` varchar(255) NOT NULL,
  `created` date NOT NULL,
  PRIMARY KEY (`id`)
    	)ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
    	
    	dbDelta($sql);
    	
    }
    public static function addMulticacheAdvCCompTodb()
    {
    	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    	global $wpdb;
    	$tablename = $wpdb->prefix . "multicache_advanced_ccomp_factor_base";
    	$sql = "CREATE TABLE `$tablename` ( 
    	`id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `loadinstruc_state` varchar(255) NOT NULL,
  `avg_load_time` int(11) NOT NULL,
  `var_load_time` int(11) NOT NULL,
  `ccomp_factor` float NOT NULL,
  `loadtime_score` int(11) NOT NULL,
  `loadvar_score` float NOT NULL,
  `statmode` int(11) NOT NULL,
  `statmode_score` int(11) NOT NULL,
  `total_score` float NOT NULL,
  PRIMARY KEY (`id`))ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
    	dbDelta($sql);
    }
    
    public static function eraseDB()
    {
    	//require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    	global $wpdb;
    	$tablename = $wpdb->prefix . "multicache_advanced_ccomp_factor_base";
    	$tablename1 = $wpdb->prefix . "multicache_urlarray";
    	$tablename2 = $wpdb->prefix . "multicache_advanced_loadinstruction_base";
    	$tablename3 = $wpdb->prefix . "multicache_advanced_precache_factor";
    	$tablename4 = $wpdb->prefix . "multicache_advanced_testgroups";
    	$tablename5 = $wpdb->prefix . "multicache_advanced_test_results";
    	$tablename6 = $wpdb->prefix . "multicache_items";
    	$tablename7 = $wpdb->prefix . "multicache_items_slabs";
    	$tablename8 = $wpdb->prefix . "multicache_items_stats";
    	$sql = "DROP TABLE IF EXISTS `$tablename`,`$tablename1`,`$tablename2`,`$tablename3`,`$tablename4`,`$tablename5`,`$tablename6`,`$tablename7`,`$tablename8` ";
    	//dbDelta($sql);
    	$wpdb->query($sql);
    }
    public static function addMulticacheAdvLoadinsTodb()
    {
    	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    	global $wpdb;
    	$tablename = $wpdb->prefix . "multicache_advanced_loadinstruction_base";
    	$sql = "CREATE TABLE `$tablename` (
    	`id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `loadinstruc_state` varchar(255) NOT NULL,
  `avg_load_time` int(11) NOT NULL,
  `var_load_time` int(11) NOT NULL,
  `precache_factor` int(11) NOT NULL,
  `loadtime_score` int(11) NOT NULL,
  `loadvar_score` float NOT NULL,
  `statmode` int(11) NOT NULL,
  `statmode_score` int(11) NOT NULL,
  `total_score` float NOT NULL,
  PRIMARY KEY (`id`)
    	)ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
    	dbDelta($sql);
    }
    public static function addMulticacheAdvPrecacheFTodb()
    {
    	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    	global $wpdb;
    	$tablename = $wpdb->prefix . "multicache_advanced_precache_factor";
    	$sql = "CREATE TABLE `$tablename` (
    	`id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `loadinstruc_state` varchar(255) NOT NULL,
  `avg_load_time` int(11) NOT NULL,
  `var_load_time` int(11) NOT NULL,
  `precache_factor` int(11) NOT NULL,
  `loadtime_score` int(11) NOT NULL,
  `loadvar_score` float NOT NULL,
  `statmode` int(11) NOT NULL,
  `statmode_score` int(11) NOT NULL,
  `total_score` float NOT NULL,
  PRIMARY KEY (`id`)
    	)";
    	dbDelta($sql);
    }
    public static function addMulticacheAdvTestGroupsTodb()
    {
    	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    	global $wpdb;
    	$tablename = $wpdb->prefix . "multicache_advanced_testgroups";
    	$sql = "CREATE TABLE `$tablename` (
    	  `id` int(11) NOT NULL AUTO_INCREMENT,
  `test_page` varchar(255) NOT NULL,
  `cycles` int(11) NOT NULL,
  `cycles_complete` int(11) NOT NULL,
  `expected_tests` int(11) NOT NULL,
  `advanced` varchar(255) NOT NULL,
  `start_date` varchar(255) NOT NULL,
  `end_date` varchar(255) NOT NULL,
  `start_time` varchar(255) NOT NULL,
  `end_time` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `best_load_time` varchar(255) NOT NULL,
  `blt_precache_factor` int(11) NOT NULL,
  `blt_cache_compression_factor` float NOT NULL,
  `blt_loadinstruc_state` varchar(255) NOT NULL,
  `avg_load_time` varchar(255) NOT NULL,
  `variance_on_load_time` varchar(255) NOT NULL,
  `algorithm_precache_factor` int(11) NOT NULL,
  `algorithm_cache_compression_factor` float NOT NULL,
  `algorithm_loadinstruc_state` varchar(255) NOT NULL,
  `pf_assoc_alt` int(11) NOT NULL,
  `pf_assoc_var` int(11) NOT NULL,
  `ccf_assoc_alt` int(11) NOT NULL,
  `ccf_assoc_var` int(11) NOT NULL,
  `loadinstruc_assoc_alt` varchar(255) NOT NULL,
  `loadinstruc_assoc_var` varchar(255) NOT NULL,
  `loaded_precache_factor` int(11) NOT NULL,
  `loaded_cache_compression_factor` float NOT NULL,
  `loaded_loadinstruc_state` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
    	)ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
    	dbDelta($sql);
    }
    public static function addMulticacheAdvTestResultsTodb()
    {
    	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    	global $wpdb;
    	$tablename = $wpdb->prefix . "multicache_advanced_test_results";
    	$sql = "CREATE TABLE `$tablename` (
    	  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `date_of_test` varchar(255) NOT NULL,
  `mtime` double NOT NULL,
  `max_tests` int(11) NOT NULL,
  `current_test` int(11) NOT NULL,
  `loadinstruc_key` int(11) NOT NULL,
  `loadinstruc_state` varchar(255) NOT NULL,
  `precache_factor` int(11) NOT NULL,
  `cache_compression_factor` float NOT NULL,
  `page_load_time` int(11) NOT NULL,
  `html_bytes` int(11) NOT NULL,
  `page_elements` int(11) NOT NULL,
  `report_url` varchar(255) NOT NULL,
  `html_load_time` int(11) NOT NULL,
  `page_bytes` int(11) NOT NULL,
  `pagespeed_score` int(11) NOT NULL,
  `yslow_score` int(11) NOT NULL,
  `test_id` varchar(255) NOT NULL,
  `test_page` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `simulation` varchar(255) NOT NULL,
  `advanced` varchar(255) NOT NULL,
  `test_date` datetime NOT NULL,
  `cache_handler` varchar(255) NOT NULL,
  `hammer_mode` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `gzip_factor` (`cache_compression_factor`),
  KEY `simulation` (`simulation`)
    	)ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
    	dbDelta($sql);
    }
    
    public static function addMulticacheItemsTodb()
    {
    	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    	global $wpdb;
    	$tablename = $wpdb->prefix . "multicache_items";
    	$sql = "CREATE TABLE `$tablename` (
    	 `id` int(11) NOT NULL AUTO_INCREMENT,
  `itemnumber` int(11) NOT NULL,
  `numberofelements` int(11) NOT NULL,
  `age` int(11) NOT NULL,
  `evicted` int(11) NOT NULL,
  `evicted_nonzero` int(11) NOT NULL,
  `evicted_time` int(11) NOT NULL,
  `outofmemory` int(11) NOT NULL,
  `tailrepairs` int(11) NOT NULL,
  `reclaimed` int(11) NOT NULL,
  `expired_unfetched` int(11) NOT NULL,
  `evicted_unfetched` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `mstimestamp` varchar(255) NOT NULL,
  `nowtimestamp` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)    
    	)ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
    	dbDelta($sql);
    }
    
    public static function addMulticacheItemsSlabsTodb()
    {
    	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    	global $wpdb;
    	$tablename = $wpdb->prefix . "multicache_items_slabs";
    	$sql = "CREATE TABLE `$tablename` (
    
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slab_id` int(11) NOT NULL,
  `chunk_size` int(11) NOT NULL,
  `chunks_per_page` int(11) NOT NULL,
  `total_pages` int(11) NOT NULL,
  `total_chunks` int(11) NOT NULL,
  `used_chunks` int(11) NOT NULL,
  `free_chunks` int(11) NOT NULL,
  `free_chunks_end` int(11) NOT NULL,
  `mem_requested` varchar(255) NOT NULL,
  `get_hits` varchar(255) NOT NULL,
  `cmd_set` varchar(255) NOT NULL,
  `delete_hits` varchar(255) NOT NULL,
  `incr_hits` varchar(255) NOT NULL,
  `decr_hits` varchar(255) NOT NULL,
  `cas_hits` varchar(255) NOT NULL,
  `cas_badval` varchar(255) NOT NULL,
  `touch_hits` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
    
    	)ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
    	dbDelta($sql);
    }
    
    public static function addMulticacheItemsStatsTodb()
    {
    	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    	global $wpdb;
    	$tablename = $wpdb->prefix . "multicache_items_stats";
    	$sql = "CREATE TABLE `$tablename` (
    
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `uptime` varchar(255) NOT NULL,
  `time` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `libevent` varchar(255) NOT NULL,
  `pointer_size` int(11) NOT NULL,
  `rusage_user` float NOT NULL,
  `rusage_system` float NOT NULL,
  `curr_connections` int(11) NOT NULL,
  `total_connections` int(11) NOT NULL,
  `connection_structures` int(11) NOT NULL,
  `reserved_fds` int(11) NOT NULL,
  `cmd_get` int(11) NOT NULL,
  `cmd_set` int(11) NOT NULL,
  `cmd_flush` int(11) NOT NULL,
  `cmd_touch` int(11) NOT NULL,
  `get_hits` int(11) NOT NULL,
  `get_misses` int(11) NOT NULL,
  `delete_hits` int(11) NOT NULL,
  `delete_misses` int(11) NOT NULL,
  `incr_hits` int(11) NOT NULL,
  `incr_misses` int(11) NOT NULL,
  `decr_hits` int(11) NOT NULL,
  `decr_misses` int(11) NOT NULL,
  `cas_hits` int(11) NOT NULL,
  `cas_misses` int(11) NOT NULL,
  `cas_badval` int(11) NOT NULL,
  `touch_hits` int(11) NOT NULL,
  `touch_misses` int(11) NOT NULL,
  `auth_cmds` int(11) NOT NULL,
  `auth_errors` int(11) NOT NULL,
  `bytes_read` int(11) NOT NULL,
  `bytes_written` int(11) NOT NULL,
  `limit_maxbytes` int(22) NOT NULL,
  `accepting_conns` int(11) NOT NULL,
  `listen_disabled_num` int(11) NOT NULL,
  `threads` int(11) NOT NULL,
  `conn_yields` int(11) NOT NULL,
  `hash_power_level` int(11) NOT NULL,
  `hash_bytes` int(11) NOT NULL,
  `hash_is_expanding` int(11) NOT NULL,
  `bytes` int(11) NOT NULL,
  `curr_items` int(11) NOT NULL,
  `total_items` int(11) NOT NULL,
  `expired_unfetched` int(11) NOT NULL,
  `evicted_unfetched` int(11) NOT NULL,
  `evictions` int(11) NOT NULL,
  `reclaimed` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
    	)ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
    	dbDelta($sql);
    }

    public static function DeactivateMulticacheConfig()
    {

        delete_option('multicache_config_options');
    
    }
    
    public static function loadConfigOptions()
    {
    	$mul_site = is_multisite();
    	if($mul_site)
    	{
    		$blog_id = get_current_blog_id();
    		$blog_info = get_blog_details( $blog_id );
    		$site_url = $blog_info->siteurl;
    	}
    	$config_options = self::$config_options;
    	$config_options['created'] = date('Y-m-d H:i:s');
    	$config_options['gtmetrix_test_url'] =empty($mul_site) ? MulticacheUri::root() : $site_url;
    	$config_options['googlestartdate'] = date('Y-m-d', strtotime('-1 year'));
    	$config_options['googleenddate'] = date('Y-m-d');
    	/*$config_options['social_script_identifiers'] = '';/*MulticacheHelper::processSocialAdIndicators('FB.init
    	assets.pinterest.com
    	platform.twitter.com
    	plusone.js
    	');*//*
    	$config_options['advertisement_script_identifiers'] = '';/*MulticacheHelper::processSocialAdIndicators('adsbygoogle
    	pagead/js/adsbygoogle.js');*/
    	
    	$config_options['default_scrape_url'] = empty($mul_site) ? strtolower(MulticacheUri::root()) : $site_url;
    	$config_options['css_scrape_url'] = empty($mul_site) ? strtolower(MulticacheUri::root()): $site_url;
    	/*$config_options['pre_head_stub_identifiers'] = MulticacheHelper::encodePregSplit('<head>') ;
    	$config_options['head_stub_identifiers'] = MulticacheHelper::encodePregSplit('</head>
        		</HEAD>') ;
    	$config_options['body_stub_identifiers'] = MulticacheHelper::encodePregSplit('<body>
        		<body class="someclass">') ;
    	$config_options['footer_stub_identifiers'] = MulticacheHelper::encodePregSplit('</body>
        		</BODY>') ;*/
    	$config_options['tolerance_params'] = '{"tolerance_highlighting":1,"danger_tolerance_factor":3,"danger_tolerance_color":"#a94442","warning_tolerance_factor":2.5,"warning_tolerance_color":"#8a6d3b","success_tolerance_color":"#468847"}';
        $success= add_option('multicache_config_options', $config_options, '', 'no'); // autoload set to no -> dont load options on every page load
           if(empty($success))
           {
           	update_option('multicache_config_options' , $config_options, 'no');
           }
    	 
    	
    }

}