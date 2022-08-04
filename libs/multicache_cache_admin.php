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

require_once plugin_dir_path(__FILE__).'multicache.php';
require_once plugin_dir_path(__FILE__).'multicache_stat.php';

class MulticacheCacheAdmin
{
	
	protected $_data = array();
	protected $_total = null;
	protected $_pagination = null;	
	protected $_file_count = null;	
	protected $_file_size = null;
	protected $_cache_type = 0;	
	
	protected static $instance = null;
	protected static $_ordering = null;	
	protected static $_direction = null;
	
	
	
	public function __construct()
	{
		$this->_pagination = new stdClass();
		$this->_pagination->limit = $this->initialisePagination('limit');		
		$this->_pagination->start  = $this->initialisePagination('start');
		$this->_cache_type = $this->getCacheType();
		
	}
	public function getPaginationObject()
	{
		$pagination = array();
		$pagination['total_pages'] = ceil($this->_total/$this->limit);
		$pagination['current_page'] = $this->paged;
		$pagination['order'] = $this->order_by =='cache_id'? 'cacheid':$this->order_by;
		$pagination['direction'] = $this->order_dirn;
		$pagination['cache_standard'] = $this->cache_standard!=''? (int)$this->cache_standard:$this->cache_standard;
		Return $pagination;
	}
	
	protected function getCacheType()
	{
		$user_ID = get_current_user_id();
		$cache_filter =(int) get_transient('multicache_cache_filter_'.$user_ID);
		if($cache_filter <=2 && $cache_filter >=0)
		{
			Return $cache_filter;
		}
		Return 0;
		
	}
	protected function initialisePagination($tag)
	{
		switch($tag)
		{
			case 'limit':
				$return = 20;
				break;
			case 'start':
				$return = 0;
				break;	
		}
		Return $return;
	}
	public static function getInstance()
	{
		// Only create the object if it doesn't exist.
		if (empty(self::$instance))
		{
	
			self::$instance = new MulticacheCacheAdmin();
		}
		return self::$instance;
	
	}
	protected function setPagination()
	{
		
		if(null !== ($page = MulticacheUri::getInstance()->getVar('paged')))
		{
		$this->_pagination->start = $page >=1 ? ($page-1) * $this->_pagination->limit : 0;	
		}
		
		//$this->_pagination = new stdClass();
		//$this->_pagination->limit = $this->initialisePagination('limit');
		//$this->_pagination->start  = $this->initialisePagination('start');
	}
	protected function setPaginationTotal()
	{
		if(isset($this->_total))
		{
			$this->_pagination->total_pages = ceil($this->_total/$this->_pagination->limit);
		}
		$this->_pagination->current_page = $this->_pagination->start<= $this->_pagination->total_pages? $this->_pagination->start :$this->_pagination->total_pages; 
	}
	public function getCacheAdminObject()
	{
		$this->setPagination();
		$cache_admin_object = new stdClass();
		$cache_admin_object->_data = $b = $this->getGroupCacheData();
		$this->setPaginationTotal();
		$cache_admin_object->stat = $this->getHitStats();
		$cache_admin_object->total = count($b);
		$cache_admin_object->cache_type = $this->_cache_type;
		$cache_admin_object->back = $this->getisBack();
		$cache_admin_object->pagination = $this->_pagination;
		
		
		
		Return $cache_admin_object;
	}
	protected function getisBack()
	{
		$multicache_networkadmin_loc = get_transient('multicache_networkadmin_loc');
		if(false === $multicache_networkadmin_loc)
		{
			return false;
		}
		end($multicache_networkadmin_loc);
		$key = key($multicache_networkadmin_loc);
		if(!empty($multicache_networkadmin_loc[$key]['back']))
		{
			Return $multicache_networkadmin_loc[$key]['back'];
		}
		Return false;
	}
	protected function getGroupCacheData()
    {

        //$config = MulticacheFactory::getConfig();
    	$config = MulticacheFactory::getBlogConfig();
        $cache_handler_flag = false !== $config &&  $config->getC('storage') == 'fastcache' ? 1 : 0;
        
        if (empty($this->_data))
        {
            $cache = $this->getCache()->cache;
            $data = $cache->getAll();
            //$cachetypefilter = $this->getState('cacheType');
            $cachetypefilter = $this->_cache_type; //2 - memcache ; 1 - file cache
            
            if ($data != false)
            {
                if ($cachetypefilter == 2 && $cache_handler_flag)
                {
                    foreach ($data as $key => $value)
                    {
                       if(stripos($key,'_filecache') === false)
                        {
                            $temp[$key] = $value;
                        }
                    }
                    $this->_data = $data = $temp;
                }
                elseif ($cachetypefilter == 1 && $cache_handler_flag)
                {
                    
                    foreach ($data as $key => $value)
                    {
                        if (stripos($key, '_filecache') !== false)
                        {
                            $temp[$key] = $value;
                        }
                      
                    }
                    $this->_data = $data = $temp;
                }
                else
                {
                    $this->_data = $data;
                }
                $this->_total = count($data);
                
                if ($this->_total)
                {
                    
                    foreach ($data as $key => $value)
                    {
                        $this->_file_count += $value->count;
                        $this->_file_size += ($value->size * $this->_file_count);
                    }
                    
                    // Apply custom ordering
                    $ordering = 'group';//$this->getState('list.ordering');
                    $direction = 1;//($this->getState('list.direction') == 'asc') ? 1 : - 1;
                    self::$_ordering = $ordering;
                    self::$_direction = $direction;
                    
                    //jimport('joomla.utilities.arrayhelper');
                    $this->_data = MulticacheHelper::sortObjects($data, $ordering, $direction);
                    // usort($data, 'self::cmp');
                    $this->_data = $data;
                    // Apply custom pagination
                    if ($this->_total > $this->_pagination->limit && $this->_pagination->limit)
                    {
                        $this->_data = array_slice($this->_data, $this->_pagination->start, $this->_pagination->limit);
                    }
                }
            }
            else
            {
                $this->_data = array();
            }
        }
        return $this->_data;
    
    }
    protected function isValidCachePath($path)
    {
    	static $is_valid;
    	if(isset($is_valid[$path]))
    	{
    		Return $is_valid[$path];
    	}
    	$ms_obj= MulticacheFactory::getResolvedMultiSiteObj();
    	$default_cache_path= false ===$ms_obj->blog?  WP_CONTENT_DIR .'/cache/' : WP_CONTENT_DIR .'/cache/'. $ms_obj->blog . '-'. $ms_obj->site .'/';
    	$is_valid['path'] = strpos($path , $default_cache_path)===0 && is_dir($path)? true: false;
    	Return $is_valid['path'];
    }
    
   protected  function getCache()
    {

        //$conf = MulticacheFactory::getConfig();
    	$conf = MulticacheFactory::getBlogConfig();
    	if(is_multisite())
    	{
    		//in case config s not properly acticated we dont want the whole cache to feature
    	$ms_obj= MulticacheFactory::getResolvedMultiSiteObj();
    	$default_cache_path= false ===$ms_obj->blog?  WP_CONTENT_DIR .'/cache/' : WP_CONTENT_DIR .'/cache/'. $ms_obj->blog . '-'. $ms_obj->site .'/';
    	
    	if(is_network_admin() && null !== ($subfolder = MulticacheUri::getInstance()->getVar('subfolder')))
    	{
    		
    		$network_admin_loc = array();
    		$location_name = null;
    		if (false ===($network_admin_loc = get_transient('multicache_networkadmin_loc')))
    		{
    			$current_location = $default_cache_path;
    			
    		}
    		else {
    			end($network_admin_loc);
    			$key = key($network_admin_loc);
    			
    			$current_location =isset($network_admin_loc[$key]['path']) && $this->isValidCachePath($network_admin_loc[$key]['path']) ?  $network_admin_loc[$key]['path']: $default_cache_path;
    			$location_name = isset($network_admin_loc[$key]['location'])  ?  $network_admin_loc[$key]['location']: null;
    			
    		}
    		
    		if(strpos($subfolder , '_file_cache') !== false)
    		{
    			
    			$sub_path = preg_replace('~_file_cache$~six' , '' ,$subfolder );
    			$trial_location = $current_location  . $sub_path . '/';
    			
    			if($location_name !==$sub_path && $trial_location !== $default_cache_path &&  $this->isValidCachePath($trial_location))
    			{
    				$back_loc = $current_location;
    				$current_location = $trial_location;
    				$network_admin_loc[] = array(
    						'location' => $sub_path,
    						'path'=> $current_location,
    						'back'=> array('name' => $sub_path, 'location' => $back_loc),    						
    				) ;
    				
    				set_transient('multicache_networkadmin_loc' ,$network_admin_loc , 3600 );
    				
    			}
    			$default_cache_path = $current_location;
    			
    			
    		}
    		
    	}
    	elseif(is_network_admin() && null !== ($back = MulticacheUri::getInstance()->getVar('back')))
    	{
    		
    		$network_admin_loc = get_transient('multicache_networkadmin_loc');
    		
    		if(false === $network_admin_loc)
    		{
    			$default_cache_path=   WP_CONTENT_DIR .'/cache/' ; 
    		}
    		else {
    			end($network_admin_loc);
    			$key = key($network_admin_loc);
    			$default_cache_path = $network_admin_loc[$key]['path'];
    			$back_name = $network_admin_loc[$key]['back']['name'];
    			     if($back_name ===$back )
    			       {
    			        $back_loc = $network_admin_loc[$key]['back']['location'];
    			        $default_cache_path = $back_loc ;
    		         	unset($network_admin_loc[$key]);
    		        	$network_admin_loc = array_filter($network_admin_loc);
    		            	if(!empty($network_admin_loc))
    		                	{
    			                	set_transient('multicache_networkadmin_loc' ,$network_admin_loc , 3600 );
    		                 	}
    		                	else 
    		                	{
    			                 	delete_transient('multicache_networkadmin_loc');
    		                 	}
    			        }
    			       
    		}
    	}
    	elseif(null === $subfolder && !isset($_POST['delete_cache']))
    	{
    		
    		delete_transient('multicache_networkadmin_loc');
    	}
    	elseif(isset($_POST['delete_cache']))
    	{
    		$network_admin_loc = get_transient('multicache_networkadmin_loc');
    		if(false !== $network_admin_loc)
    		{
    		end($network_admin_loc);
    		$key = key($network_admin_loc);
    		$default_cache_path = $network_admin_loc[$key]['path'];
    		}
    	}
    	
    	}
    	
        $options = array(
            'defaultgroup' => '',
            'storage' => false !== $conf ? $conf->getC('storage', 'fastcache') : 'fastcache',
            'caching' => true,
            'cachebase' => false !== $conf ? $conf->getC('cache_path',$default_cache_path  ):WP_CONTENT_DIR .'/cache/',
        );
        
        $cache = Multicache::getInstance('', $options);
        
        return $cache;
    
    }
    
    protected function getHitStats()
    {
    	$ping = new MulticacheStat();
    	$ping->prepareStat();
    //lets ensure stats are updated here
    //end stats update
    global $wpdb;
    $table = $wpdb->prefix.'multicache_items_stats';
    $query = "SELECT * FROM $table ORDER BY timestamp desc";
    $quick_stat = $wpdb->get_row($query);
    /*
    	$db = JFactory::getDBO();
    	$query = $db->getQuery('true');
    	$query->select('*');
    	$query->from($db->quoteName('#__multicache_items_stats'));
    	$query->order($db->quotename('timestamp') . ' DESC');
    	$db->setQuery($query);
    	$quick_stat = $db->LoadObject();
    	*/
    	if (empty($quick_stat))
    	{
    		Return false;
    	}
    	if (($quick_stat->get_hits + $quick_stat->get_misses) > 0)
    	{
    		$quick_stat->getrate = $quick_stat->get_hits / ($quick_stat->get_hits + $quick_stat->get_misses);
    	}
    	else
    	{
    		$quick_stat->getrate = 0;
    	}
    	if (($quick_stat->delete_hits + $quick_stat->delete_hits))
    	{
    		$quick_stat->deleterate = $quick_stat->delete_hits / ($quick_stat->delete_hits + $quick_stat->delete_hits);
    	}
    	else
    	{
    		$quick_stat->deleterate = 0;
    	}
    	$quick_stat->filesize = $this->_file_size;
    	$quick_stat->filecount = $this->_file_count;
    
    	Return $quick_stat;
    
    }
    
    public function clean($group = '')
    {
    
    	$cache = $this->getCache()->cache;
    	
    	$cache->clean($group);
    
    }
    
    public function clear_cache($array)
    {
    if(empty($array))
    {
    	Return false;
    }
    	foreach ($array as $group)
    	{
    		$this->clean($group);
    	}
    
    }
    
}