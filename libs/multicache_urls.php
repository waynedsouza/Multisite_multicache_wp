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
// stems from add settings section
// Draw the section header
defined('_MULTICACHEWP_EXEC') or die();


class MulticacheUrls
{
	protected static $instance = null;
	
	protected $order_by = null;
	protected $order_dirn = null;
	protected $paged = null;
	protected $total_pages = null;
	protected $limit = null;
	protected $transient = null;
	
	public function __construct()
	{
		$this->order_by = $this->getOrder();
		$this->order_dirn = $this->getDirn();
		$this->paged = $this->getPaged();
		$this->limit = 20;
	}
	
	protected function getTransient()
	{
		$rel = 'alu';
		$user_ID = get_current_user_id();
		$this->transient = get_transient('multicache_filter_order_dir_'.$user_ID.'_'.$rel);
	}
	
	protected function getPaged()
	{
		$paged = absint(MulticacheUri::getInstance()->getVar('paged'));
		Return  !empty($paged) ? $paged : 1;
		
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
				case 'freq':
				    $return = 'f_dist';
				    break;
				case 'log':
					$return = 'ln_dist';
					break;
				case 'type':
					$return ='type';
					break;
				case 'date':
					$return = 'created';
					break;
				default:
					$return = 'views';

			}
			
		}
		Return isset($return)? $return : 'views';
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
	
	
	protected function getReverseOrder($a)
	{
		switch($a)
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
				case  'f_dist':
				    $return = 'freq';
				    break;
				case  'ln_dist':
					$return = 'log';
					break;
				case 'type':
					$return ='type';
					break;
				case  'created':
					$return = 'date';
					break;
				default:
					$return = 'views';
		}
		Return isset($return)? $return: 'id';
	}
	
	
public static function getInstance()
{
	// Only create the object if it doesn't exist.
	if (empty(self::$instance))
	{

		self::$instance = new MulticacheUrls();
	}
	return self::$instance;

}
	
	
	
	public function getUrlStats()
	{
	global $wpdb;
	$table = $wpdb->prefix.'multicache_urlarray';
	$query = "SELECT Count('type') As count,type FROM $table GROUP BY type";
	$result = $wpdb->get_results($query , OBJECT);
	/*
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->Select('Count(' . $db->quoteName('type') . '  ) As count');
		$query->Select($db->quoteName('type'));
		$query->from($db->quoteName('#__multicache_urlarray'));
		$query->group($db->quoteName('type'));
		$db->setQuery($query);
		$result = $db->loadObjectlist();
		*/
		$typecount = array();
		foreach ($result as $obj)
		{
			$typecount[$obj->type] = $obj->count;
		}
		return $typecount;
	
	}
	
	public function getUrlItems()
	{
		static $_url_items = null;
		if(isset($_url_items))
		{
			Return $_url_items;
		}
		global $wpdb;
		$table = $wpdb->prefix.'multicache_urlarray';
		$sql = "SELECT id FROM $table ";
		$sum_obj = $wpdb->get_results($sql , OBJECT);
		$this->total_pages = !empty($this->limit) && !empty($wpdb->num_rows)?ceil($wpdb->num_rows / $this->limit):1;
		$offset = $this->limit * ($this->paged -1);		
		$query = "SELECT * FROM $table ORDER BY $this->order_by $this->order_dirn LIMIT $this->limit OFFSET $offset";
		$result = $wpdb->get_results($query , OBJECT);
		$url_items = new stdClass();
		$url_items->items = $result;
		$url_items->pagination = array('total_pages' => $this->total_pages,'current_page' => $this->paged);
		$url_items->order = $this->getReverseOrder($this->order_by) ;
		$url_items->direction = $this->order_dirn ;
		$_url_items = $url_items;
		Return $_url_items;
	}
	
	public function delete($pks)
	{
	if(empty($pks))
	{
		Return false;
	}
		//$app = JFactory::getApplication();
		if(! current_user_can('manage_options'))
		{
			MulticacheHelper::prepareMessageEnqueue(__('Not authorised to delete urls','multicache-plugin'));
			Return false;
		}
	
	 global $wpdb;
	 $table = $wpdb->prefix.'multicache_urlarray';
		//$db = JFactory::getDBO();
	
		foreach ($pks as $i => $pk)
		{
	
			//$query = $db->getQuery('true');
			$where = array('id' => $pk);
			$where_format = array('%d');
			$wpdb->delete( $table, $where, $where_format = null );
			/*$conditions = array(
					$db->quoteName('id') . ' = ' . $pk
			);
			$query->delete($db->quoteName('#__multicache_urlarray'));
			$query->where($conditions);
	
			$db->setQuery($query);
	
			$result = $db->execute();
			*/
		}
	
	}
	
	public function makeRegisterlnclass()
	{
	global $wpdb;
	$config = MulticacheFactory::getBlogConfig();
	$secret = false !== $config ? $config->getC('secret') : '';
	$table = $wpdb->prefix.'multicache_urlarray';
		$lnparams = get_option('multicache_config_options');
		$audit_string = 'audit-multicachedistribution-secret-' . $secret;
		$debug_string = 'fastcache-debug';
		$query = "SELECT DISTINCT url FROM $table ORDER BY views desc";
		$uobj = $wpdb->get_col($query);
		/*
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT ' . $db->quoteName('url'));
		$query->from($db->quoteName('#__multicache_urlarray'));
		$query->order($db->quoteName('views') . ' DESC');
		$db->setQuery($query);
		$uobj = $db->loadColumn();
		*/
		if (empty($uobj))
		{
			Return false;
		}
		$urlarray = array();
		foreach ($uobj as $url_key)
		{
			// in hammered mode we allow all variants of a particular url
			if ($lnparams['multicachedistribution'] == '3')
			{
				$urlarray[strtolower($url_key)] = 1;
			}
			else
			{
				$urlarray[$url_key] = 1;
			}
		}
		$urlarray[$audit_string] = $lnparams->multicachedistribution . '-' . date('Y-m-d');
		$urlarray[$debug_string] = ! empty($lnparams->debug_mode) ? 1 : null;
		$success = MulticacheHelper::registerLOGnormal($urlarray);
		Return $success;
	
	}
	
	

	
}