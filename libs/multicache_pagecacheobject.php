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


class MulticachePageCacheObject
{
	protected static $instance = null;
	protected $order_by = null;
	protected $order_dirn = null;
	protected $paged = null;
	protected $total_pages = null;
	protected $limit = null;
	protected $transient = null;
	protected $_total = null;
	protected $cache_standard = null;
	protected static $_ordering = null;
	protected static $_direction = null;
	protected static $_page_key_detail_ne = null;
	protected static $_page_key_detail_exception = null;
	
	public function __construct()
	{
		$this->order_by = $this->getOrder();
		$this->order_dirn = $this->getDirn();
		$this->paged = $this->getPaged();
		$this->limit = 20;
		$this->cache_standard = $this->getCacheStandard();
		//$this->setFilterFlags();
	}
	protected function getCacheStandard()
	{
		static $cache_std = null;
		if(isset($cache_std))
		{
			Return $cache_std;
		}
		$user_ID = get_current_user_id();
		$cache_std = get_transient('multicache_pcitype_filters'.$user_ID);
		Return $cache_std;
	}
	
	protected function getPaged()
	{
		$paged = absint(MulticacheUri::getInstance()->getVar('paged'));
		Return  !empty($paged) ? $paged : 1;
	
	}
	
	protected function getTransient()
	{
		$rel = 'pci';
		$user_ID = get_current_user_id();
		$this->transient = get_transient('multicache_filter_order_dir_'.$user_ID.'_'.$rel);
	}
	
	protected function getOrder()
	{
		if(!isset($this->transient))
		{
			$this->getTransient();
		}
		if(isset($this->transient['order']))
		{
			switch($this->transient['order'])
			{
				case 'id':
					$return = 'id';
					break;
				case 'url':
					$return = 'url';
					break;
				case 'views':
					$return = 'views';
					break;
				case 'cacheid':
					$return = 'cache_id';
					break;
				case 'type':
					$return = 'type';
					break;
				default:
					$return = 'id';
					break;
							
			}
				
		}
		Return isset($return)? $return : 'id';
	
	}
	
	protected function getDirn()
	{
		if(!isset($this->transient))
		{
			$this->getTransient();
		}
		$dir = isset($this->transient['dir']) && $this->transient['dir'] == 'asc'? 'asc':'desc';
		Return $dir;
	}
	
	
	public static function getInstance()
	{
		// Only create the object if it doesn't exist.
		if (empty(self::$instance))
		{
	
			self::$instance = new MulticachePageCacheObject();
		}
		return self::$instance;
	
	}
	
	
	public function getData()
	{
	
		//jimport('joomla.utilities.arrayhelper');
		self::$_ordering  = $this->order_by;//$ordering = $this->getState('list.ordering');
		self::$_direction = $this->order_dirn =='asc'? 1:0; ;//$direction = ($this->getState('list.direction') == 'asc') ? 1 : - 1;
		$cacheStandard = $this->cache_standard;//$this->getState('cacheStandard');
		require_once plugin_dir_path(__FILE__) . 'multicache_stat.php';
		
		$comp = new MulticacheStat();	
		$comp->prepareStat();
		$Allkeys = $comp->getAllKeys();
		//$comp = JModelLegacy::getInstance('stat', 'MulticacheModel');
		//$comp->prepareStat();
		//$Allkeys = $comp->getAllKeys();
		$page_keys = array();
		foreach ($Allkeys as $key)
		{
			if (stripos($key, '-page-') !== false)
			{
				$page_keys[] = $key;
			}
		}
	
		$pages = $this->getPagesfromkeys($page_keys);
	
		if ($cacheStandard == 1)
		{
			foreach ($pages as $key => $page)
			{
				if ($page['type'] == 'standard')
				{
					$obj[$key] = $page;
				}
			}
			$pages = $obj;
		}
		elseif ($cacheStandard == 2)
		{
			foreach ($pages as $key => $page)
			{
				if ($page['type'] == 'nonstandard')
				{
					$obj[$key] = $page;
				}
			}
			$pages = $obj;
		}
	
		$pages = $this->assignPageid($pages);
		if (empty($pages))
		{
			Return false;
		}
		usort($pages, 'self::cmp');
		$this->_total = count($pages);
		if ($this->_total > $this->limit)
		{
			$pages = array_slice($pages, ($this->paged-1)*$this->limit, $this->limit);
		}
	
		Return $pages;
	
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
	
	protected static function cmp($a, $b)
	{
	
		$direction = self::$_direction;
		$ordering = self::$_ordering;
		
	
		if ($direction == 1)
		{
	
			if ($a[$ordering] == $b[$ordering])
			{
				Return 0;
			}
			return ($a[$ordering] < $b[$ordering]) ? - 1 : 1;
		}
	
		if ($a[$ordering] == $b[$ordering])
		{
			Return 0;
		}
		return ($a[$ordering] > $b[$ordering]) ? - 1 : 1;
	
	}
	
	
	
	public  function getHitStats()
	{
		static $_quickstat = null;
		if(isset($_quickstat))
		{
			Return $_quickstat;
		}
	global $wpdb;
	$table = $wpdb->prefix.'multicache_items_stats';
	$query = "SELECT * FROM $table ORDER BY timestamp desc";
	$quick_stat = $wpdb->get_row($query , OBJECT);
	/*
		$db = JFactory::getDBO();
		$query = $db->getQuery('true');
		$query->select('*');
		$query->from($db->quoteName('#__multicache_items_stats'));
		$query->order($db->quotename('timestamp') . ' DESC');
		$db->setQuery($query);
		$quick_stat = $db->LoadObject();
		*/
	if(!isset($quick_stat))
	{
		$quick_stat = new stdClass();
	}
		if (($quick_stat->get_hits + $quick_stat->get_misses) > 0)
		{
			$quick_stat->getrate = $quick_stat->get_hits / ($quick_stat->get_hits + $quick_stat->get_misses);
		}
		else
		{
			$quick_stat->getrate = 0;
		}
		if (($quick_stat->delete_hits + $quick_stat->delete_misses) > 0)
		{
			$quick_stat->deleterate = $quick_stat->delete_hits / ($quick_stat->delete_hits + $quick_stat->delete_misses);
		}
		else
		{
			$quick_stat->deleterate = 0;
		}
		if(!empty($this->_total))
		{
			$quick_stat->total = $this->_total;
		}
	
		$_quickstat =  $quick_stat;
		Return $_quickstat;
	
	}
	
	
	protected function assignPageid($pages)
	{
	
		if (empty($pages))
		{
			Return false;
		}
		$page_id = 1;
		foreach ($pages as $key => $value)
		{
			$value['id'] = $page_id ++;
			$pages[$key] = $value;
		}
		Return $pages;
	
	}
	
	
	protected function getPagesfromkeys($keys)
	{
	
		$keys = array_flip($keys);
		global $wpdb;
		$table = $wpdb->prefix.'multicache_urlarray';
		$query = "SELECT url, cache_id , cache_id_alt, cache_id_alt_ext , views FROM $table";
		$keys_on_record = $wpdb->get_results($query , ARRAY_A);
		/*
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
	
		$query->select($db->quoteName('url'));
		$query->select($db->quoteName('cache_id'));
		$query->select($db->quoteName('cache_id_alt'));
		$query->select($db->quoteName('cache_id_alt_ext'));
		$query->select($db->quoteName('views'));
		$query->from($db->quoteName('#__multicache_urlarray'));
		$db->setQuery($query);
		$keys_on_record = $db->loadAssocList();
		*/
	
		$page_key_detail = array();
		$page_url_detail = array();
	
		foreach ($keys_on_record as $key => $value)
		{
			$cache_id_check = $value[cache_id];
			$cache_id_alt_check = $value[cache_id_alt];
			$cache_id_alt_ext_check = $value[cache_id_alt_ext];
			if (isset($keys[$cache_id_check]) || isset($keys[$cache_id_alt_check]) || isset($keys[$cache_id_alt_ext_check]))
			{
	
				$page_key_detail[$cache_id_check] = array(
						"url" => $value[url],
						"views" => $value[views],
						"cache_id" => $value[cache_id],
						"type" => "standard"
				);
			}
			$page_url_detail[$value[url]] = array(
					"url" => $value[url],
					"views" => $value[views],
					"cache_id" => $value[cache_id]
			);
		}
	
		$key_nonexistent = array_diff_key($keys, $page_key_detail);
		$array2 = $this->getPagedetailsfromcache($key_nonexistent, $page_url_detail);
		$array3 = !empty(self::$_page_key_detail_exception)? self::$_page_key_detail_exception : null;
		if (is_array($array2))
		{
			$page_details = array_merge($page_key_detail, $array2);
		}
		else
		{
			$page_details = $page_key_detail;
		}
		
		if (isset($array3) && is_array($array3))
		{
			$page_details = array_merge($page_details, $array3);
		}
		
		Return $page_details;
	
	}
	
	public function delete($cache_obj)
	{
	if(empty($cache_obj))
	{
		Return false;
	}
	require_once plugin_dir_path(__FILE__) . 'multicache_stat.php';
	$comp = new MulticacheStat();
	/*	$delete_cache_keys = JFactory::getApplication()->input->post->get('cid', array(), 'array');
		$comp = JModelLegacy::getInstance('stat', 'MulticacheModel');*/
		$comp->deleteKeys($cache_obj);
	
	}
	
	protected function getPagedetailsfromcache($keys, $url_details)
	{
	
		//JLoader::import('stat', JPATH_ADMINISTRATOR . '/components/com_multicache/models');
		//$comp = JModelLegacy::getInstance('stat', 'MulticacheModel');
		require_once plugin_dir_path(__FILE__) . 'multicache_stat.php';		
		$comp = new MulticacheStat();
		//$comp->prepareStat();
		//$Allkeys = $comp->getAllKeys();
		$page_key_detail_ne = array();
		foreach ($keys as $key => $value)
		{
			$page = $comp->getPageByKey($key);
			if (isset($url_details[strtolower($page)]))
			{
				self::$_page_key_detail_ne[$key] = array(
						"url" => $page,
						"views" => $url_details[strtolower($page)]['views'],
						"cache_id" => $key,
						"type" => "nonstandard"
				);
			}
			elseif ($page)
			{
				self::$_page_key_detail_exception[$key] = array(
						"url" => $page,
						"views" => 0,
						"cache_id" => $key,
						"type" => "exception"
				);
			}
		}
	
		Return self::$_page_key_detail_ne;
	
	}
	
}