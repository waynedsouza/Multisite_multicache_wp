<?php
/**
 * Plugin Name: MulticacheWP
 * Plugin URI: http://onlinemarketingconsultants.in
 * Description: High Performance fastcache Controller
 * Version: 1.0.0.6
 * Author: Wayne DSouza
 * Author URI: http://onlinemarketingconsultants.in
 * License: GNU PUBLIC LICENSE see license.txt
 */
defined('ABSPATH') or die();

if (! defined('_MULTICACHEWP_EXEC'))
{
define('_MULTICACHEWP_EXEC', '1');
}
if (! defined('WP_PATH_MULTICACHECACHE'))
{
    define('WP_PATH_MULTICACHECACHE', ABSPATH . 'wp-content/cache/');
}

//var_dump($GLOBALS['pagenow']);
require_once plugin_dir_path(__FILE__) . 'libs/multicache_factory.php';
require_once plugin_dir_path(__FILE__) . 'libs/multicache_uri.php';
require_once plugin_dir_path(__FILE__) . 'libs/multicache_mandate_tasks.php';
//selective deactivation
$multicache_conf = MulticacheFactory::getBlogConfig();
if(empty($multicache_conf) 
		|| (isset($multicache_conf) 
				&& null === $multicache_conf->getC('cache_handler')
				&& !is_admin()
				//prevent activation lockout
				//&& !($active = $multicache_conf->getC('network_activate'))
				//&& !is_network_admin()
				)
		|| (isset($multicache_conf)  && "0" === $multicache_conf->getC('network_activate'))
		|| isset($multicache_conf) &&   'network'  === $multicache_conf->getC('name')&& !is_network_admin() )
{
	Return;
}


require_once plugin_dir_path(__FILE__) . 'libs/multicache_comment_handler.php';
require_once plugin_dir_path(__FILE__) . 'libs/multicache_application.php';
require_once plugin_dir_path(__FILE__) . 'admin/multicache_helper.php';
if (is_admin() || $GLOBALS['pagenow'] ==='wp-login.php' || 0)
{
	
	require_once plugin_dir_path(__FILE__) . 'admin/admin_settings.php';
	
}

require_once plugin_dir_path(__FILE__) . 'libs/multicache.php';

//require_once plugin_dir_path(__FILE__) . 'admin/multicache_scrape_template.php';


$multicache_options = array(
    'defaultgroup' => 'page',
    'subgroup' => false,
    'user' => false,
	'network_optimization' => '0' === $multicache_conf->getC('network_optimize')? false: true,
    'browsercache' => 1, // need to get this from admin
    'caching' => $multicache_conf->getC('network_caching')!=='0' && $multicache_conf->getC('caching') >= 1? true:false,
	'cache_user_loggedin' => $multicache_conf->getC('cache_user_loggedin' , 0) == 1? true:false,
	'optimize_user_loggedin' =>$multicache_conf->getC('optimize_user_loggedin' , 0) == 1? true:false,
	'cache_query_urls' => $multicache_conf->getC('cache_query_urls' , 0) == 1? true:false,
	'optimize_query_urls' => $multicache_conf->getC('optimize_query_urls' , 0) == 1? true:false,
);
//MulticacheHelper::log_error('params check ' , 'params check ',$multicache_options);
$multicache_page_cache = Multicache::getInstance('page', $multicache_options);

// var_dump($multicache_cache);
// exit();
class MulticacheEventViewer
{

    /*
     * A static class to document the events in a form similar to an observer model
     */
    public static $_contents = null;

    public static $_start_level = null;

    public static $_end_level = null;

    public static $_user = null;

    public static $_group = null;

    public static $_id = null;

}

function multicache_plugin_textdomain()
{

    load_plugin_textdomain('multicache-plugin', FALSE, basename(dirname(__FILE__)) . '/languages/');

}

add_action('plugins_loaded', 'multicache_plugin_textdomain');
// start routine main

if(!is_admin() && $GLOBALS['pagenow'] !=='wp-login.php')
{
	add_action( 'wp_enqueue_scripts', 'multicache_frontend_scripts_and_styles' );
	add_action( 'wp_footer', 'render_inlineMulticache_scripts' );
    /*
     * if (defined('WP_USE_THEMES') && WP_USE_THEMES && $backend != 1)
     * {
     */
     $uri_instance_multicache = MulticacheUri::getInstance();
     if(strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET')
     {
     	Return;
     }
     if(stripos($uri_instance_multicache->toString(),'xmlrpc.php') !== false)
     {
     	return;
     }
    add_action('init' , 'multicache_get_cached_page',2);//use 100 for woocommerce
    add_action('init', 'multicache_start_page', 3);//use 101 for woo commerce
    
    add_action('shutdown', 'multicache_end_page', - 1);
    /* } */
}


function getGroupMulticache()
{

    $group = array();
    if (is_search())
    {
        $group['search'] = 1;
    }
    if (is_page())
    {
        $group['page'] = 1;
    }
    if (is_archive())
    {
        $group['archive'] = 1;
    }
    if (is_tag())
    {
        $group['tag'] = 1;
    }
    if (is_single())
    {
        $group['single'] = 1;
    }
    if (is_category())
    {
        $group['category'] = 1;
    }
    if (is_front_page())
    {
        $group['front_page'] = 1;
    }
    if (is_home())
    {
        $group['home'] = 1;
    }
    if (is_author())
    {
        $group['author'] = 1;
    }
    if (is_feed())
    {
        $group['feed'] = 1;
    }
    
    Return $group;

}

function getUserMulticache()
{
static $_user = null;
if(isset($_user))
{
	Return $_user;
}
    $user = array();
    $user["id"] = wp_get_current_user();
   
    $_user = $user;
    Return $_user;

}

function multicache_get_cached_page()
{
if(strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET')
{
	Return;
}

if(defined('DONOTCACHEPAGE'))
{
	Return;
}
//no cache for set carts
global $multicache_conf;
if($multicache_conf->getC('multicachedistribution') === '0')
{
	global $woocommerce;
	if(isset($woocommerce->session) && null !== $woocommerce->session->get('cart'))
	{
		//cart is set - no cache
		Return;
	}
}
	global $multicache_page_cache;
	$user = getUserMulticache();
	$id = MulticacheUri::getInstance()->toString();
	if(strpos($id ,'/wp-content/' ) !== false
			|| strpos($id ,'/wp-includes/' ) !== false 
			|| strpos($id ,'/images/' ) !== false)
	{
		/*
		 * We do not want to cache application pages 
		 * example a plugin curls a web page both head and body tags will be available
		 * /wp-admin/ is taken care of in is_admin()
		 */
		Return;
	}

	$c_obj = $multicache_page_cache->get($id ,null,  $user['id']->ID,'page');
	//var_dump($c_obj);
      if($c_obj !== false)
      {
        $app = MulticacheFactory::getApplication();
        $app->setBody($c_obj);
        /*multicache profiler Collect results*/
        $multicache_page_cache->markEndTime('lastRender');
        /*multicache profiler Collect results end scheme*/
       
        echo $app->toString($app->get('gzip' , true)); 
      exit;
       }
	
}

function multicache_start_page()
{
	if(strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET')
	{
		Return;
	}
	if(defined('DONOTCACHEPAGE'))
	{
		Return;
	}
	//no cache for set carts
	global $multicache_conf;
	if($multicache_conf->getC('multicachedistribution') === '0')
	{
		global $woocommerce;
		if(isset($woocommerce->session) && null !== $woocommerce->session->get('cart'))
		{
			//cart is set - no cache
			Return;
		}
	}
    // lets get the user
    // get the type of page
    $id = MulticacheUri::getInstance()->toString();
	if(strpos($id ,'/wp-content/' ) !== false
			|| strpos($id ,'/wp-includes/' ) !== false
			|| strpos($id ,'/images/' ) !== false)
	{
		/*
		 * We do not want to cache application pages
		 * example a plugin curls a web page both head and body tags will be available
		 * /wp-admin/ is taken care of in is_admin()
		 */
		Return;
	}
    ob_start('multicache_callback_ob');
    MulticacheEventViewer::$_start_level = ob_get_level();

}

function multicache_callback_ob($content)
{
     global $multicache_options;
    $sub_group = getGroupMulticache();
    $user = getUserMulticache();
    /*$match = preg_match('~(<\/html>|<\/rss>|<\/feed>|<\/urlset|<\?xml)~i', $content);*/
    /*$match2 = preg_match('#^(?><?[^<]*+)+?<html(?><?[^<]*+)+?<head(?><?[^<]*+)+?</head(?>' . '<?[^<]*+)+?<body(?><?[^<]*+)+?</body(?><?[^<]*+)+?</html.*+$#i', $content);*/
    if(!isset($uri_instance_multicache))
    {
    $uri_instance_multicache = MulticacheUri::getInstance() ;
    }
    if (/*$match || $match2 ||*/ 1)
    {
       
        
        MulticacheEventViewer::$_group = $sub_group;
        MulticacheEventViewer::$_user = $user;
        MulticacheEventViewer::$_id = $uri_instance_multicache->toString();
        //$content = !isset($sub_group['feed'])? $content . "<!--MulticachePlugin SavePageToCache-->" :$content;
        
        $feed = isset($sub_group['feed']) ? 'feed': $sub_group;
        if($feed !== 'feed')
        {
       
     $q = $uri_instance_multicache->getQuery(true);
    
    
       
       
       if($multicache_options['network_optimization'] && !(false === $multicache_options['optimize_query_urls'] 
       		   && !empty($q))
       		&&
       		!(false === $multicache_options['optimize_user_loggedin'] 
       		   && isset($user['id']->id) && $user['id']->id !== 0 ))
       {
        $tweaker = MulticacheFactory::getTweaker();
        try {
        	$content = $tweaker->performStrategy($content);
        } catch (Exception $e) {
        	MulticacheHelper::log_error($e->getMessage() , 'ALERTtweakerRuntime');
        }
       }
        $content = $content . "<!--MulticachePlugin SavePageToCache subgroup: ".serialize($sub_group)." user: ".$user['id']->ID." id : ".MulticacheUri::getInstance()->toString()." -->";
        
        }
        
       
        MulticacheEventViewer::$_contents = $content;
      
    }
    Return $content;

}

function multicache_end_page()
{
	if(strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET')
	{
		Return;
	}
	if(defined('DONOTCACHEPAGE'))
	{
		Return;
	}
	//no cache for set carts
	global $multicache_conf;
	if($multicache_conf->getC('multicachedistribution') === '0')
	{
		global $woocommerce;
		if(isset($woocommerce->session) && null !== $woocommerce->session->get('cart'))
		{
			//cart is set - no cache
			Return;
		}
	}
    ob_get_flush();
    // ob_get_clean(); gets the buffer without the comment MulticachePlugin SavePageToCache but requires one more echo else blank screen.NB. Samuel Marshall also adds a ob_start extra
   // var_dump(MulticacheEventViewer::$_id, MulticacheEventViewer::$_user, MulticacheEventViewer::$_group, MulticacheEventViewer::$_contents);
    global $multicache_page_cache;
    global $multicache_options;
    //do not cache logged in users
    $user = getUserMulticache();
    if(false === $multicache_options['cache_user_loggedin']
    		&& isset($user['id']->id) && $user['id']->id !== 0 )
    {
    Return;	
    }
    //do not cache query urls
    $uri_instance_multicache = MulticacheUri::getInstance();
    $q = $uri_instance_multicache->getQuery(true);
    if(false === $multicache_options['cache_query_urls'] &&
    		!empty($q))
    {
    	Return;
    }
    //$tweaker = MulticacheFactory::getTweaker();
    $s_group = MulticacheEventViewer::$_group;
    $feed = isset($s_group['feed']) ? 'feed': $s_group;
    
    /*
    if($feed !=='feed')
    {
    MulticacheEventViewer::$_contents = $tweaker->performStrategy(MulticacheEventViewer::$_contents);
    }
    */
    
    $a = $multicache_page_cache->store(MulticacheEventViewer::$_contents ,
    		$uri_instance_multicache->toString(),
    		$feed,
    		MulticacheEventViewer::$_user['id']->ID
    );
    //MulticacheHelper::log_error('storing object','cache-store',$a);
    

}

function multicache_activate()
{

    require_once plugin_dir_path(__FILE__) . 'admin/multicache_activate.php';
    MulticacheConfigure::InitMulticacheConfigAdd();
    $success = MulticacheConfigure::initMulticacheConfig();
    if (is_wp_error($success))
    {
        $error_string = $success->get_error_message();
        error_log($error_string);
    }
    //lets ensure the cache directory is present
    MulticacheConfigure::addCachedir();
    

}

register_activation_hook(__FILE__, 'multicache_activate');

function multicache_deactivate()
{

    require_once plugin_dir_path(__FILE__) . 'admin/multicache_activate.php';
    MulticacheConfigure::DeactivateMulticacheConfig();

}
register_deactivation_hook(__FILE__, 'multicache_deactivate');

function multicache_uninstall()
{

    require_once plugin_dir_path(__FILE__) . 'admin/multicache_activate.php';
    MulticacheConfigure::DeactivateMulticacheConfig();
    MulticacheConfigure::eraseDB();

}

register_uninstall_hook(__FILE__, 'multicache_uninstall');

add_filter('comment_form','multicache_handle_comments');

//start conduit
function render_inlineMulticache_scripts()
{
	$protocol = isset($_SERVER['HTTPS'])? 'https://':'http://';
	$ajax_url = admin_url('admin-ajax.php',$protocol);
	$options = get_option('multicache_config_options');
	$multicache_conduit_switch = $options['conduit_switch'];

	if(empty($multicache_conduit_switch))
	{
		Return;
	}
	?>
<!-- start conduit -->
<script>
function performConduit() {
    for (var e = document.getElementsByTagName("input"), t = e.length, n = (new RegExp("[0-9a-f]{10}"), !1), a = 0; t > a; a++) "action" == e[a].name && "login" == e[a].value && (n = !0);
    if (n) {
        var o;
        o = window.XMLHttpRequest ? new XMLHttpRequest : new ActiveXObject("Microsoft.XMLHTTP");
        var d = '<?php echo $ajax_url;?>' + "?action=multicache_conduit&atomic=" + Math.round(1e5 * Math.random());
        //console.log('logging retreival uri' + d);
        o.open("GET", d, !0), o.send()
    }
    o.onreadystatechange = function() {
        if (4 == o.readyState && 200 == o.status)
            for (var e = JSON.parse(o.responseText), t = e.t, n = document.getElementsByTagName("input"), a = n.length, d = new RegExp("[0-9a-f]{10}"), r = 0; a > r; r++) {
                var i = n[r],
                    m = d.exec(i.value);
                m && (i.setAttribute("old_value", i.value), i.value = t)
            }
       // console.log('retreived o val '+ o);
    }
}("complete" == document.readyState || "loaded" == document.readyState || "interactive" == document.readyState) && performConduit(), document.addEventListener("DOMContentLoaded", function() {
    performConduit()
});
</script>
<!-- end conduit -->
<?php 

}

//end conduit

function conduit_multicache_login_nonce()
{
	$options = get_option('multicache_config_options');
	$nonce = wp_create_nonce( $options['conduit_nonce_name']);
	echo json_encode(array(
			'w' => 'byt56tyuiopoiuytvgbnhgftdrts54',
			'q' => md5($nonce),
			't' => $nonce
	));
	exit;
}

add_action('wp_ajax_nopriv_multicache_conduit' , 'conduit_multicache_login_nonce');

function multicache_frontend_scripts_and_styles()
{
	//multicache_lazy
	if(isset($_REQUEST['multicachetask']) || isset($_REQUEST['multicachecsstask']))
	{
		Return false;
	}
	$tweaker = MulticacheFactory::getTweaker();
	$loader = $tweaker->getMulticacheLazyScriptsAndStyles();
	$p_speed = $loader['pagespeed_ad'];
	if(!empty($loader))		
	{
		$l_url = plugins_url('delivery/assets/' , __FILE__);
		if(!empty($loader['style']))
		{
		wp_register_style('multicache_lazyload.min.css', $l_url . 'css/m_ll.css');
		}
		if(!empty($loader['script']))
		{
			//version 1.0.0.2 loading async versions
			//deactivated the lazyload is now bundled testing
			if(0 && (!empty($p_speed['resultant_defer']) || !empty($p_speed['resultant_async'])))
			{
		wp_register_script('multicache_lazyload_lib.min.js', $l_url . 'js/jquery.lazyloadad.js', array(
				'jquery'
		));
		
		wp_register_script('multicache_lazyload.min.js', $l_url . 'js/m_llad.js', array(
				'jquery',
				'multicache_lazyload_lib.min.js'
		));
			}
			else 
			{
				wp_register_script('multicache_lazyload_lib.min.js', $l_url . 'js/jquery.lazyload.js', array(
						'jquery'
				));
				/*
				wp_register_script('multicache_lazyload.min.js', $l_url . 'js/m_ll.js', array(
						'jquery',
						'multicache_lazyload_lib.min.js'
				));
				*/
			}
		}
		if(!empty($loader['style']))
		{
		wp_enqueue_style('multicache_lazyload.min.css');
		}
		if(!empty($loader['script']))
		{
		wp_enqueue_script('multicache_lazyload_lib.min.js');
		//testing combined version
		//wp_enqueue_script('multicache_lazyload.min.js');
		}
	}
	
	function multicache_DOnotCache()
	{
		static $dont_cache;
		if(isset($dont_cache))
		{
			return $dont_cache;
		}
		$dont_cache = false;
		switch (true) {
			case defined('DONOTCACHEPAGE'):
			case is_admin():
			case defined('PHP_SAPI') && PHP_SAPI === 'cli':
			case defined('SID') && SID != '':
			case defined('DOING_CRON'):
			case defined('APP_REQUEST'):
			case defined('XMLRPC_REQUEST'):
			case defined('WP_ADMIN'):
			case (defined('SHORTINIT') && SHORTINIT):
			case strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET':
	
				$dont_cache = true ;
		}
		$id = MulticacheUri::getInstance()->toString();
		if(strpos($id ,'/wp-content/' ) !== false
				|| strpos($id ,'/wp-includes/' ) !== false
				|| strpos($id ,'/images/' ) !== false)
		{
			/*
			 * We do not want to cache application pages
			 * example a plugin curls a web page both head and body tags will be available
			 * /wp-admin/ is taken care of in is_admin()
			 */
			$dont_cache = true ;
		}
	
		Return $dont_cache;
	}

}