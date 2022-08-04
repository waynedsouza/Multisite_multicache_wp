<?php

/**
 * MulticacheWP
 * uri: http://onlinemarketingconsultants.in
 * Description: High Performance fastcache Controller
 * Version: 1.0.0.6
 * Author: Wayne DSouza
 * Author URI: http://onlinemarketingconsultants.in
 * License: GNU PUBLIC LICENSE see license.txt
 */
defined('_MULTICACHEWP_EXEC') or die();

require_once dirname(__FILE__) . '/multicache.php';
require_once dirname(__FILE__) . '/multicache_application.php';

class MulticacheFactory
{

    public static $config = null;
    
    protected static $conf = null;

    protected $data;

    protected static $application = null;
    
    protected static $templater = null;
    
    protected static $tweaker = null;
    
    protected static $lnobject = null;
    
    protected static $strategy = null;
    
    protected static $cache = null;
    
    protected static $cache_admin = null;
    
    protected static $network_admin = null;
    
    protected static $multicacheurls = null;
    
    protected static $multicacheuri = null;
    
    protected static $profiler = null;
    
    protected static $advancedsimulation = null;
    
    protected static $pagecacheobject = null;

    public function __construct($data = null)
    {

        $this->data = new stdClass();
        
        if (is_array($data) || is_object($data))
        {
            $this->bindData($this->data, $data);
        }
        elseif (! empty($data) && is_string($data))
        {
            $this->loadString($data);
        }
    
    }
    public static function getResolvedMultiSiteObj()
    {
    	//does not create directories
    	Return self::resolveMultisiteStrategyloc();
    }
    public static function setBlogConfig($config)
    {
    	//used by simcontrol to force a blog id config
    	if(is_object($config))
    	{
    		self::$conf = self::$config = $config;
    	}
    }
    
    public static function getBlogConfig()
    {
    	
    	if(isset(self::$conf))
    	{
    		Return self::$conf;
    	}
    	$config = is_multisite()? self::resolveConfig():self::getConfig();
    	$config = ($config)? self::getConfig(): $config;
    	if(empty($config))
    	{
    		Return false;
    	}
    	self::$conf = $config;
    	Return self::$conf;
    }
    
    protected static function getHostPathNames()
    {
    	$uri = MulticacheUri::getInstance();
    	$host = $uri->getHost();
    	$path = $uri->getPath();
    	$host_name = strtolower(str_replace(array('.www.','www.'),array('.',''),$host));
    	$obj = new stdClass();
    	$obj->host = $host;
    	$obj->path = $path;
    	$obj->host_name = $host_name;
    	
    	if( defined('SUBDOMAIN_INSTALL') && SUBDOMAIN_INSTALL===true)
    	{
    		//subdomains
    		preg_match('~^(?:([^\.]+)\.)?([^.]+\..+)~six' ,$host_name,$domains );
    		$blog = !empty($domains[1])? preg_replace('~[^a-zA-Z0-9]~' , '',$domains[1]): 'main';
    		$site = preg_replace('~[^a-zA-Z0-9]~' , '',$domains[2]);
    		$obj->site_as_dir = $site;
    		$obj->blog_as_dir = $blog;
    		
    	}
    	else
    	{
    		$obj->site_as_dir = strtolower(preg_replace('~[^a-zA-Z0-9]~', '', $host_name));
    	}
    	
    	Return $obj;
    }
    protected static function getSubdomainResolution()
    {
    	
    	$obj = new stdClass();
    	
    	$host_path_names = self::getHostPathNames();
    	$site = $host_path_names->site_as_dir;
    	$blog = $host_path_names->blog_as_dir;
    	$path = $host_path_names->path;
    	//check for backend network admin        
    	$u_split = explode('/',$path);
    	$obj->site = $site;
    	$obj->install_type = 'subdomain';
    	$obj->live_site = $host_path_names->host_name;
    	if($u_split[1] == 'wp-admin' && $u_split[2] == 'network' )
    	{
    		$obj->blog = false;
    		$obj->config_loc = dirname(__FILE__) .'/multicache_config.php';
    		$obj->strategy_loc = false;
    		$obj->namespace = '';
    	}
    	else{
    		$obj->blog = $blog;
    		$obj->config_loc = dirname(__FILE__) . '/multisite_strategy/'.$site.'/'.$blog.'/multicache_config.php';
    		$obj->strategy_loc = dirname(__FILE__) . '/multisite_strategy/'.$site.'/'.$blog.'/';
    		$obj->namespace = 'S'.$site . 'Bl' . $blog;
    	}
    	
    		Return $obj;
    }
    protected static function resolveMultisiteStrategyloc()
    {
    	if(!is_multisite())
    	{
    		Return false;
    	}
    	static $mul_obj;
    	if(isset($mul_obj))
    	{
    		Return $mul_obj;
    	}
    	if( defined('SUBDOMAIN_INSTALL') && SUBDOMAIN_INSTALL===true)
    	{
    		$obj = self::getSubdomainResolution();
    	}
    	else {
    	$host_path_names = self::getHostPathNames();
       	$site = $host_path_names->site_as_dir;
    	$path = $host_path_names->path;
    	//check for the possible cases of folderwise blognames
    	
    	$obj = new stdClass();
    	$obj->site =$site;
    	//adjusting for path installs
    	if(defined('PATH_CURRENT_SITE') && PATH_CURRENT_SITE !=='/')
    	{
    		$folder_string = PATH_CURRENT_SITE;
    		$a = preg_quote($folder_string);
    		$search = '~'.$a.'(.*)~';
    		preg_match( $search ,$path ,$m);
    		$path = '/'. $m[1];
    		$obj->install_type = 'subfolder';
    		$obj->sub_folder = PATH_CURRENT_SITE;
    		
    	}
    	$obj->install_type = !isset($obj->install_type)? 'folder': $obj->install_type;
    	$u_split = explode('/',$path);
    	//$i = defined('PATH_CURRENT_SITE') && PATH_CURRENT_SITE !=='/'? 2:1;
    	if(!empty($u_split[1]) && !($u_split[1]=='wp-admin' && $u_split[2]=='network'))
    	{
    		if($u_split[1]=='wp-admin')
    		{
    			$p_formation = 'main';
    			
    		}
    		else {
    			$p_formation = preg_replace('~[^a-zA-Z0-9]~' , '',$u_split[1]);
    			$p_formation = strtolower($p_formation);
    			$lookfordir = dirname(__FILE__) . '/multisite_strategy/'.$site.'/'.$p_formation;
    			//accomadate urls for main blog.
    			/*
    			 * NOTE: In order to activate main blog it is required for a check process
    			 * to ensure that sub blogs have their respective folders. This avoids conflicts 
    			 * between the main blogs and sub blogs. 
    			 * At this time it appears sub blog activation can be immediate.
    			 * for very large networks we will need to ensure break in sub blog activations.
    			 * MOST IMPORTANT: ENSURE FOLDERS FOR DEACTIVATED BLOGS!! ELSE STRATEGY WILL NOT 
    			 * BE ABLE TO DISTINGUISH THEM FROM MAIN AND BLOG OWNER WILL BE HELPLESS!
    			 * NOTE: We need to accomadate folder installations
    			 */
    			if(!is_dir($lookfordir))
    			{
    				$p_formation = 'main';
    			}
    			
    		}   		
    		
    		$obj->blog = $p_formation;
    		$obj->config_loc = dirname(__FILE__) . '/multisite_strategy/'.$site.'/'.$p_formation.'/multicache_config.php';
    		$obj->strategy_loc = dirname(__FILE__) . '/multisite_strategy/'.$site.'/'.$p_formation.'/';
    		$obj->namespace = 'S'.$site . 'Bl' . $p_formation;
    	}
    	elseif(empty($u_split[1]))
    	{
    		$obj->blog = 'main';
    		$obj->config_loc = dirname(__FILE__) . '/multisite_strategy/'.$site.'/main/multicache_config.php';
    		$obj->strategy_loc = dirname(__FILE__) . '/multisite_strategy/'.$site.'/main/';
    		$obj->namespace = 'S'.$site . 'Blmain';
    	}
    	elseif($u_split[1]=='wp-admin' && $u_split[2]=='network')
    	{
    		$obj->blog = false;
    		$obj->config_loc = dirname(__FILE__) .'/multicache_config.php';
    		$obj->strategy_loc = false;
    		$obj->namespace = '';
    		
    	}
    	else{
    		throw new RuntimeException('Unhandled resolution '.$uri->toString(), 404);
    	}
    	if($obj->install_type === 'subfolder')
    	{
    	$obj->live_site = false !== $obj->blog?  $host_path_names->host_name . $obj->sub_folder .$obj->blog .'/' :$host_path_names->host_name . $obj->sub_folder  .'main/' ;
    	}
    	else{
    		$obj->live_site = false !== $obj->blog?  $host_path_names->host_name .'/' .$obj->blog .'/' :$host_path_names->host_name . '/main/' ;
    	}
    	}
    	
    	$mul_obj = $obj;
    	//var_dump($mul_obj);exit;
    	
    	Return $mul_obj;
    }
    
public static function resolveConfig()
{
		
	$ms_obj = self::resolveMultisiteStrategyloc();
	
	if(false !== $ms_obj && is_file($ms_obj->config_loc))
	{
		
		$config = self::getConfig($ms_obj->config_loc ,'PHP',$ms_obj->namespace );
		
	}
	else {
		if(is_multisite() && !is_admin())
		{
		 Return false;
		}
		$config = self::getConfig();
	}
	
	Return $config;
	//$namespace = empty($u_split[1])? '': 'S' $u_split[1];
		
}
    public static function getConfig($file = null, $type = 'PHP', $namespace = '')
    {

        if (! self::$config)
        {
            if ($file === null)
            {
                $file = dirname(__FILE__) . '/multicache_config.php';
            }
            
            self::$config = self::createConfig($file, $type, $namespace);
        }
        return self::$config;
    
    }

    public function getC($c_key, $default = null)
    {

        if (empty(self::$config) && ! isset($default))
        {
            Return false;
        }
      
        $conf_value = isset(self::$config->data->$c_key) ? self::$config->data->$c_key: null;
        if (! isset($conf_value))
        {
            if (isset($default))
            {
                Return $default;
            }
            Return null;
        }
        Return $conf_value;
    
    }

    public function setC($c_key, $value)
    {

        if (empty(self::$config))
        {
            Return false;
        }
        self::$config->data->$c_key = $value;
        if (! isset(self::$config->data->$c_key))
        {
            
            Return false;
        }
        Return true;
    
    }

    public static function getApplication()
    {

        if (! self::$application)
        {
            
            self::$application = MulticacheApplication::getInstance();
        }
        
        return self::$application;
    
    }
    
    public static function getTemplater()
    {
    	require_once dirname(__FILE__) . '/multicache_templater.php';
    
    	if (! self::$templater)
    	{
    
    		self::$templater = MulticacheTemplater::getInstance();
    	}
    
    	return self::$templater;
    
    }
    
    public static function getTweaker()
    {
    	require_once dirname(__FILE__) . '/multicache_tweaker.php';
    
    	if (! self::$tweaker)
    	{
    
    		self::$tweaker = MulticacheTweaker::getInstance();
    	}
    
    	return self::$tweaker;
    
    }
    
    public static function getLnObject()
    {
    	require_once dirname(__FILE__) . '/multicache_lnobject.php';
    
    	if (! self::$lnobject)
    	{
    
    		self::$lnobject = MulticacheLnObject::getInstance();
    	}
    
    	return self::$lnobject;
    
    }
    
    public static function getMulticacheUrls()
    {
    	require_once dirname(__FILE__) . '/multicache_urls.php';
    
    	if (! self::$multicacheurls)
    	{
    
    		self::$multicacheurls = MulticacheUrls::getInstance();
    	}
    
    	return self::$multicacheurls;
    
    }
    
    public static function getMulticacheURI()
    {
    
    	require_once dirname(__FILE__) . '/multicache_uri.php';
    
    	if (! self::$multicacheuri)
    	{
    
    		self::$multicacheuri = MulticacheUri::getInstance();
    	}
    
    	return self::$multicacheuri;
    
    }
    
    public static function getStrategy()
    {
    	require_once dirname(__FILE__) . '/multicache_strategy.php';
    
    	if (! self::$strategy)
    	{
    
    		self::$strategy = MulticacheStrategy::getInstance();
    	}
    
    	return self::$strategy;
    
    }
    
    public static function getCacheAdmin()
    {
    	require_once dirname(__FILE__) . '/multicache_cache_admin.php';
    
    	if (! self::$cache_admin)
    	{
    
    		self::$cache_admin = MulticacheCacheAdmin::getInstance();
    	}
    
    	return self::$cache_admin;
    
    }
    public static function getNetworkAdmin()
    {
    	require_once dirname(__FILE__) . '/multicache_network_admin.php';
    
    	if (! self::$network_admin)
    	{
    
    		self::$network_admin = MulticacheNetworkAdmin::getInstance();
    	}
    
    	return self::$network_admin;
    
    }
    
    public static function getAdvancedSimulation()
    {
    	require_once dirname(__FILE__) . '/multicache_advancedsimulation.php';
    	if (! self::$advancedsimulation)
    	{
    
    		self::$advancedsimulation = MulticacheAdvancedSimulation::getInstance();
    	}
    
    	return self::$advancedsimulation;
    
    }
    
    public static function getPageCacheObject()
    {
    	require_once dirname(__FILE__) . '/multicache_pagecacheobject.php';
    
    	if (! self::$pagecacheobject)
    	{
    
    		self::$pagecacheobject = MulticachePageCacheObject::getInstance();
    	}
    
    	return self::$pagecacheobject;
    
    }
    
    public static function getCache($group = '', $handler = '', $storage = 'fastcache')
    {
    	$hash = md5($group . $handler . $storage);
    	if (isset(self::$cache[$hash]))
    	{
    		return self::$cache[$hash];
    	}
    	//$handler = ($handler == 'function') ? 'callback' : $handler;
    	$options = array('defaultgroup' => $group);
    	if (isset($storage))
    	{
    		$options['storage'] = $storage;
    	}
    	$cache = Multicache::getInstance($handler, $options);
    	self::$cache[$hash] = $cache;
    	return self::$cache[$hash];
    }

    protected static function createConfig($file, $type = 'PHP', $namespace = '')
    {
;
        if (is_file($file))
        {
            include_once $file;
        }
        
        $register = new MulticacheFactory();
        // Sanitize the namespace.
        $namespace = ucfirst((string) preg_replace('/[^a-zA-Z0-9_]/i', '', $namespace));
       
        // Build the config name.
        $name = 'MulticacheConfig' . $namespace;
        // Handle the PHP configuration type.
        if ($type == 'PHP' && class_exists($name))
        {
            // Create the JConfig object
            $config = new $name();
            
            // Load the configuration values into the registry
            $register->loadObject($config);
        }
        return $register;
    
    }

    protected function loadObject($object)
    {

        $this->bindData($this->data, $object);
    
    }

    protected function bindData($parent, $data)
    {
        // Ensure the input data is an array.
        if (is_object($data))
        {
            $data = get_object_vars($data);
        }
        else
        {
            $data = (array) $data;
        }
        foreach ($data as $k => $v)
        {
            if ((is_array($v) && self::isAssociative($v)) || is_object($v))
            {
                $parent->$k = new stdClass();
                $this->bindData($parent->$k, $v);
            }
            else
            {
                $parent->$k = $v;
            }
        }
    
    }

    protected static function isAssociative($array)
    {

        if (is_array($array))
        {
            foreach (array_keys($array) as $k => $v)
            {
                if ($k !== $v)
                {
                    return true;
                }
            }
        }
        return false;
    
    }
    
    /*
     * UNCOMMENT ERROR LOGGER FOR DEBUGGING ONLY
     */
    public static function loadJsStrategy( $return_namespace = false , $strategy_location = false , $load_simulation = false)
    {
    	static $loaded , $name , $location;
    	if(isset($loaded) && $return_namespace)
    	{
    		Return $name;
       	}
       	if(isset($loaded) && $strategy_location)
       	{
       		Return $location;
       	}
       	if(!isset($loaded))
       	{
    	
    	if(is_multisite())
    	{
    		$ms_obj = self::resolveMultisiteStrategyloc();
    		$file_jsstrategy = $ms_obj->strategy_loc . 'jscachestrategy.php';
    		
    		if(is_file($file_jsstrategy))
    		{
    			require_once $file_jsstrategy;
    			$loaded = true;
    		}
    		if($load_simulation)
    		{
    			$file_jssimulation = $ms_obj->strategy_loc . 'jscachestrategy_simcontrol.php';
    			if(is_file($file_jssimulation))
    			{
    				require_once $file_jssimulation;    				
    			}
    		}
    		$name = $ms_obj->namespace;
    		$location = $ms_obj->strategy_loc;
    		
    	}
    	else
    	{
    		if(is_file(plugin_dir_path(__FILE__).'jscachestrategy.php'))
    		{
    			require_once plugin_dir_path(__FILE__).'jscachestrategy.php';
    			$loaded = true;
    		}
    		if($load_simulation)
    		{
    			//$file_jssimulation = $ms_obj->strategy_loc . 'jscachestrategy_simcontrol.php';
    			if(is_file(plugin_dir_path(__FILE__).'jscachestrategy_simcontrol.php'))
    			{
    				require_once plugin_dir_path(__FILE__).'jscachestrategy_simcontrol.php';
    			}
    		}
    		$name = '';
    		$location = dirname(__FILE__) .'/';
    		
    	}
    	
    	if($return_namespace)
    	{
    		Return $name;
    	}
    	if($strategy_location)
    	{
    		Return $location;
    	}
       	}
    }
    public static function loadPageScriptsCSS()
    {
    	if(class_exists('MulticachePageScripts'))
    	{
    		Return;
    	}
    	if(is_multisite())
    	{
    		$ms_obj = self::resolveMultisiteStrategyloc();
    		$file_js = $ms_obj->strategy_loc . 'pagescripts.php';
    		$file_css = $ms_obj->strategy_loc . 'pagecss.php';
    		if(is_file($file_js))
    		{
    			require_once $file_js;
    		}
    		if(is_file($file_css))
    		{
    			require_once $file_css;
    		}
    	}
    	else
    	{
    	if(is_file(plugin_dir_path(__FILE__).'pagescripts.php'))
           {
	         require_once plugin_dir_path(__FILE__).'pagescripts.php';
           }
    	}
    }
    public static function getProfiler($prefix = '')
    {
    
    	require_once dirname(__FILE__) . '/multicache_profiler.php';
    
    	if (! self::$profiler[$prefix])
    	{
    
    		self::$profiler[$prefix] = MulticacheProfiler::getInstance($prefix);
    	}
    
    	return self::$profiler[$prefix];
    
    }
    public static function loadErrorLogger($message = '', $extra_message = '', $type = '', $error_file = 'multicache_factory_error_logger.log')
    {
    
    
    	$error_dir = dirname(dirname(__FILE__));
    	$uri = self::getMulticacheURI(); // this load MulticacheUri if not already present
    	$root = MulticacheUri::root();
    	$uri_context = $uri->toString();
    	$config = self::getConfig();
    	
    	    	
    	$error_file = $error_dir .'/logs/'. $error_file;
    	if (@filesize($error_file) >= 104857600)
    	{
    		Return;
    	}
    	$date = date('Y-m-d  H:i:s');
    
    	$s_vars = '  ua -' . $_SERVER['HTTP_USER_AGENT'] . '   ip - ' . $_SERVER['REMOTE_ADDR'];
    	$server_vars = $s_vars;
    	if ($_SERVER['REQUEST_METHOD'] != 'GET')
    	{
    		$request_vars = var_export($_REQUEST, true);
    		$request_vars .= var_export($_POST, true);
    		$request_vars .= var_export($_SERVER, true);
    	}
    	else
    	{
    		$request_vars = 'na';
    	}
    
    	if (! empty($extra_message))
    	{
    		$extra_message = var_export($extra_message, true);
    	}
    	$error_message = "\n" . $date . ' 	' . ' ' . $message . '  url-' . $uri_context . ' useragent-' . $s_vars . ' POST REQUEST' . $request_vars . '   extra message' . $extra_message;

    	error_log($error_message, 3, $error_file);
    
    }

}