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

if(file_exists(dirname(__FILE__) . '/cartmulticache.php'))
{
	require_once dirname(__FILE__) . '/cartmulticache.php';
}
require_once dirname(__FILE__) . '/multicache_storage.php';

class MulticacheStoragetemp extends MulticacheStorage
{
	public $_cacheid = NULL;
	public function __construct($options = array())
	{
	
		parent::__construct($options);
	
	}
	
	public function getCacheid($id, $group)
	{
	
		$this->_cacheid = $this->_getCacheid($id, $group);
		Return $this->_cacheid;
	
	}
	
	public function getCacheidAlternate($id, $group)
	{
	
		$this->_cacheid = $this->_getAlternateCacheId($id, $group);
		Return $this->_cacheid;
	
	}
	
	public function getSecret()
	{
	
		$config = MulticacheFactory::getBlogConfig();
		$this->_hash = false !== $config ? md5($config->getC('secret')) : '';
		Return $this->_hash;
	
	}
	
    protected function _getCacheId($id, $group, $user = 0, $subgroup = null)
    {

        
        $name = md5($this->_application . '-' . $id . '-' . $user . '-' . $subgroup . '-' . $this->_language);
        $this->rawname = $this->_hash . '-' . $name;
        $cache_id =  $this->_hash . '-cache-'  . $group . '-' . $name;
        return $cache_id;
    
    }
	
	protected function _getCacheIdb($id, $group, $user = 0, $subgroup = null)
	{
	global $woocommerce;
		if ($group != 'page' || !isset($woocommerce) || !isset($woocommerce->session))
		{
			Return $this->_getCacheId($id, $group, $user , $subgroup );
			/*
			$name = md5($this->_application . '-' . $id . '-' . $this->_language);
			$this->rawname = $this->_hash . '-' . $name;
			return $this->_hash . '-cache-' . $group . '-' . $name;
			*/
		}
		if (property_exists('CartMulticache', 'vars'))
		{
			$cartobj = CartMulticache::$vars;
		}
	
		if ($cartobj[cart_mode] == 0 || ($cartobj[cart_mode] == 1 && isset($cartobj['urls'][MulticacheUri::current()])) || ($cartobj[cart_mode] == 2 && ! isset($cartobj['urls'][MulticacheUri::current()])))
		{
			//$session = JFactory::getApplication()->getSession();
			$cart_diff_obj = null;
			// set session registry & cat_vars
			
			$cart_diff_obj = array();
			$cart_diff_obj['client_currency'] = $woocommerce->session->get('client_currency');
			$cart_diff_obj['client_currency_language'] = $woocommerce->session->get('client_currency_language');
			
			if(!empty($cartobj['countryseg']) && function_exists('wc_get_customer_default_location'))
			{
			$a = wc_get_customer_default_location();
			$cart_diff_obj['country'] = $a['country'];
			}
	/*
			if (isset($cartobj["cart_diff_vars"]))
			{
				//$registry_vars = $session->get('registry');
				$cart_diffs = $cartobj["cart_diff_vars"];
	
				foreach ($cart_diffs as $key => $value)
				{
	
					$cart_diff_obj[] = $registry_vars->get($key);
				}
			}
	
			if (isset($cartobj['session_vars']))
			{
				$cartsessions = $cartobj['session_vars'];
	
				foreach ($cartsessions as $key => $namespace)
				{
					$cart_diff_obj[] = $session->get($key, null, $namespace);
				}
			}
	*/
			// end of setting session & registry & cat vars
	
			if (! empty($cart_diff_obj))
			{
				$name = md5($this->_application . '-' . serialize($cart_diff_obj) . '-' . $id . '-' . $user . '-' . $subgroup . '-' . $this->_language);
				
			}
			else
			{
				$name = md5($this->_application . '-' . $id . '-' . $user . '-' . $subgroup . '-' . $this->_language);
				
			}
		}
		else
		{
			$name = md5($this->_application . '-' . $id . '-' . $user . '-' . $subgroup . '-' . $this->_language);
		}
		$this->rawname = $this->_hash . '-' . $name;
	
		return $this->_hash . '-cache-'  . $group . '-' . $name;
	
	}
	
	
	protected function _getAlternateCacheId($id, $group = 'page', $user = 0, $subgroup = null)
	{
		$cache_idarray = array();
		if ($group == 'page')
		{
			$cache_idarray['alternate'] = $this->_getCacheIdb($id, $group , $user , $subgroup);
			$cache_idarray['original']  = $this->_getCacheId($id, $group , $user , $subgroup);
		}
		else
		{
			$cache_idarray['original']  = $this->_getCacheId($id, $group , $user , $subgroup);
		}
		
		Return $cache_idarray;
	}
	
	/*
	protected function _getAlternateCacheId($id, $group = 'page', $user = 0, $subgroup = null)
	{
	
		$cache_idarray = array();
		
		if ($group == 'page')
		{
	
			//$session = JFactory::getApplication()->getSession();
			//$registry = $session->get('registry');
			if (property_exists('CartMulticache', 'vars'))
			{
				$cartobj = CartMulticache::$vars;
				$cart_diff_obj = null;
				if (isset($cartobj["cart_diff_vars"]))
				{
					$registry_vars = $session->get('registry');
					$cart_diffs = $cartobj["cart_diff_vars"];
	
					foreach ($cart_diffs as $key => $value)
					{
	
						$cart_diff_obj[] = $registry_vars->get($key);
					}
				}
	
				if (isset($cartobj['session_vars']))
				{
					$cartsessions = $cartobj['session_vars'];
	
					foreach ($cartsessions as $key => $namespace)
					{
						$cart_diff_obj[] = $session->get($key, null, $namespace);
					}
				}
				if (! empty($cart_diff_obj))
				{
					$name = md5($this->_application . '-' . serialize($cart_diff_obj) . '-' . $id . '-' . $this->_language);
					$cache_idarray['alternate'] = $this->_hash . '-cache-' . $group . '-' . $name;
				}
			}
	
			$name = md5($this->_application . '-' . $id . '-' . $user . '-' . $subgroup . '-' . $this->_language);
			$cache_idarray['original'] = ($user === 0) ? $this->_hash . '-cache-' . $subgroup . '-' . $group . '-' . $name : $this->_hash . '-cache-' . '-user-' . $user . '-subgroup-' . $subgroup . '-' . $group . '-' . $name;
			
			Return $cache_idarray;
		}
		else
		{
			//will have to modify this basis its non page usage
			$name =md5($this->_application . '-' . $id . '-' . $user . '-' . $subgroup . '-' . $this->_language);
			$cache_idarray['original'] = ($user === 0) ? $this->_hash . '-cache-' . $subgroup . '-' . $group . '-' . $name : $this->_hash . '-cache-' . '-user-' . $user . '-subgroup-' . $subgroup . '-' . $group . '-' . $name;
			
			
		}
	
		Return $cache_idarray;
	
	}
	*/
}