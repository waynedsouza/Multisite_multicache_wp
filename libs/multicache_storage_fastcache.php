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

require_once dirname(__FILE__) . '/multicache_storage.php';
/*
if(file_exists(dirname(__FILE__) . '/multicacheurlarray.php'))
{
	require_once dirname(__FILE__) . '/multicacheurlarray.php';
}
*/
if(file_exists(dirname(__FILE__) . '/multicache_storage_temp.php'))
{
	require_once dirname(__FILE__) . '/multicache_storage_temp.php';
}


// avoid solo loading errors
if (class_exists('MulticacheStoragetemp'))
{

    class MulticacheFastcache extends MulticacheStoragetemp
    {
    
    }
}
else
{

    class MulticacheFastcache extends MulticacheStorage
    {
    
    }
}

class MulticacheStorageFastcache extends MulticacheFastcache
{

    protected $_root;

    protected static $_db = null;

    protected static $_dbadmin = null;

    protected $_persistent = false;

    protected $_compress = 0;

    protected $_multicacheurlarray = null;

    protected $_lz_factor = 0;

    protected $_config = null;
    
    protected $ms_obj = null;

    public function __construct($options = array())
    {

        parent::__construct($options);
        
        if (! defined("FASTCACHEVARMULTICACHEPERSIST"))
        {
            $this->defFastcacheVars();
        }
        
        if (self::$_db === null)
        {
            $this->getConnection();
        }
        $this->_root = $options['cachebase'];
        
        if(is_multisite() && !is_dir($this->_root))
        {
        $this->prepareMultisiteCacheRoot();
        }
    
    }
    protected function prepareMultisiteCacheRoot()
    {
    	
    	if(defined('WP_CONTENT_DIR'))
    	{
    		$cache_base_generic = WP_CONTENT_DIR .'/cache/';
    	}
    	elseif(defined('ABSPATH'))
    	{
    		$cache_base_generic = ABSPATH . 'wp-content/cache/';
    	}
    	else
    	{
    		$cache_base_generic =dirname(dirname(dirname(dirname(__FILE__)))).'/cache/';
    	}
    	$diff = str_replace( $cache_base_generic ,'', $this->_root);
    	$parts = explode('/' ,$diff);
    	if(!empty($parts) && is_array($parts))
    	{
    		$this->makeDirMultisite($cache_base_generic .$parts[0]);
    		$this->makeDirMultisite($cache_base_generic .$parts[0].'/' .$parts[1]);
    	}
    	
    }
    
    
    protected function makeDirMultisite( $dir)
    {
    	if (! is_dir($dir))
    	{
    	
    		// Make sure the index file is there
    		$indexFile = $dir . '/index.html';
    		
    		@mkdir($dir) && file_put_contents($indexFile, '<!DOCTYPE html><title></title>');
    		
    	}
    	
    }
    protected function defFastcacheVars()
    {
if(!defined('MEMCACHE_COMPRESSED'))
{
	define('MEMCACHE_COMPRESSED' , 2);
}
        if ($this->_config === null)
        {
            $this->_config  = MulticacheFactory::getBlogConfig();
        }
        if(is_multisite())
        {
        	$this->ms_obj = MulticacheFactory::getResolvedMultiSiteObj();
        }
        if(null !== $this->ms_obj)
        {
        $urlarray_classname =   'MulticacheUrlArray' . $this->ms_obj->namespace;
        $urlarray_loc = $this->ms_obj->strategy_loc . 'multicacheurlarray.php';
        }
        else {
        	$urlarray_classname =   'MulticacheUrlArray';
        	$urlarray_loc = dirname(__FILE__) . '/multicacheurlarray.php';
        }
        if(file_exists($urlarray_loc))
        {
        	require_once $urlarray_loc;
        }
       
        if ($this->_multicacheurlarray === null && class_exists($urlarray_classname))
        {
            
            $this->_multicacheurlarray = $urlarray_classname::$urls; // moving to static property to test time for load
        }
        
        if (class_exists('MulticacheStoragetemp'))
        {
            define('FASTCACHEVARMULTICACHESTORAGETEMP', true);
        }
        else
        {
            define('FASTCACHEVARMULTICACHESTORAGETEMP', false);
        }
        
        define('FASTCACHEVARMULTICACHEPERSIST', $this->_config->getC('multicache_persist', true));
        define('FASTCACHEVARMULTICACHECOMPRESS', $this->_config->getC('multicache_compress', false) == false ? 0 : MEMCACHE_COMPRESSED);
        define('FASTCACHEVARMULTICACHELZFACTOR', $this->_config->getC('lz_factor', false));
        define('FASTCACHEVARMULTICACHESERVERHOST', $this->_config->getC('multicache_server_host', 'localhost'));
        define('FASTCACHEVARMULTICACHESERVERPORT', $this->_config->getC('multicache_server_port', 11211));
        define('FASTCACHEVARMULTICACHEDISTRIBUTION', $this->_config->getC('multicachedistribution', 0));
        define('FASTCACHEVARMULTICACHECACHETIME', $this->_config->getC('cachetime', 1440));
        define('FASTCACHEVARMULTICACHEFORCELOCKINGOFF', $this->_config->getC('force_locking_off', true));
        define('FASTCACHEVARMULTICACHE_DEBUG', $this->_multicacheurlarray['fastcache-debug']);
        define('FASTCACHEVAR_CACHEHANDLER', $this->_config->getC('cache_handler', 'fastcache') === 'fastcache' ? true : false);
        if (isset($this->_multicacheurlarray[strtolower(MulticacheUri::current())]))
        {
            define('FASTCACHEVARMULTICACHELOWERURLISSET', true);
        }
        else
        {
            define('FASTCACHEVARMULTICACHELOWERURLISSET', false);
        }
        if (isset($this->_multicacheurlarray[MulticacheUri::current()]))
        {
            define('FASTCACHEVARMULTICACHEUAURLISSET', true);
        }
        else
        {
            define('FASTCACHEVARMULTICACHEUAURLISSET', false);
        }
        if ((extension_loaded('memcache') && class_exists('Memcache')))
        {
            define('FASTCACHEVARMULTICACHE_MEMCACHEREADY', true);
        }
        else
        {
            define('FASTCACHEVARMULTICACHE_MEMCACHEREADY', false);
        }
    
    }

    protected function getConnection()
    {

        if (! FASTCACHEVARMULTICACHE_MEMCACHEREADY || ! FASTCACHEVAR_CACHEHANDLER)
        {
        	if(!defined('MULTICACHE_MEMCACHE_READY_TESTED'))
        	{
        	define('MULTICACHE_MEMCACHE_READY_TESTED' , false);
        	}
            return false;
        }
        
        // $this->_persistent = $this->_config->get('multicache_persist', true);
        $this->_persistent = FASTCACHEVARMULTICACHEPERSIST;
        // $this->_compress = $this->_config->get('multicache_compress', false) == false ? 0 : MEMCACHE_COMPRESSED;
        $this->_compress = FASTCACHEVARMULTICACHECOMPRESS;
        
        // $this->_lz_factor = $this->_config->get('gzip_factor', false);
        $this->_lz_factor = FASTCACHEVARMULTICACHELZFACTOR;
        
        $server = array();
        // $server['host'] = $this->_config->get('multicache_server_host', 'localhost');
        $server['host'] = FASTCACHEVARMULTICACHESERVERHOST;
        // $server['port'] = $this->_config->get('multicache_server_port', 11211);
        $server['port'] = FASTCACHEVARMULTICACHESERVERPORT;
        
        self::$_db = new Memcache();
        self::$_db->addServer($server['host'], $server['port'], $this->_persistent);
        // compression on fastlz - default is 1.3 or 23% herein 20% and thres @ 2000:: in simulations 0 gzip factor is joomla default render
        if ($this->_lz_factor)
        {
            self::$_db->setCompressThreshold(2000, $this->_lz_factor);
        }
        
        $memcachetest = @self::$_db->connect($server['host'], $server['port']);
        
        if ($memcachetest == false)
        {
            // throw new RuntimeException('Could not connect to memcache server', 404);
        	if(!defined('MULTICACHE_MEMCACHE_READY_TESTED'))
        	{
            define('MULTICACHE_MEMCACHE_READY_TESTED' , false);
        	}
            return;
        }
        if(!defined('MULTICACHE_MEMCACHE_READY_TESTED'))
        {
        define('MULTICACHE_MEMCACHE_READY_TESTED' , true);
        }
        
        return;
    
    }

    public function get($id, $group, $checkTime = true, $user = 0, $subgroup = null)
    {
        
        // $modemem = $this->_config->get('multicachedistribution', 0);
        $modemem = FASTCACHEVARMULTICACHEDISTRIBUTION;
        $c_h = FASTCACHEVAR_CACHEHANDLER && MULTICACHE_MEMCACHE_READY_TESTED;
        
        if ($modemem == 3)
        :
            // hammered pagespeed allows all variants for a particular url. strtolower
            if ($c_h && ! $user && ($group != "page" || FASTCACHEVARMULTICACHELOWERURLISSET))
            :
               // $cache_id = $this->_getCacheId($id, $group, $user, $subgroup);
                $cache_id = $this->_getCacheId($id, $group, $user);
                $back = self::$_db->get($cache_id);
                return $back;
            
            
            else
            :
                $back = $this->getfilecache($id, $group, $checkTime, $user, $subgroup);
            
                Return $back;
            endif;
        
        
        elseif ($modemem == 2)
        :
            if ($c_h && FASTCACHEVARMULTICACHEUAURLISSET)
            :
               // $cache_id = $this->_getCacheId($id, $group, $user, $subgroup);
                $cache_id = $this->_getCacheId($id, $group, $user);
                $back = self::$_db->get($cache_id);
                return $back;
            
            
            else
            :
                $back = $this->getfilecache($id, $group, $checkTime, $user, $subgroup);
                Return $back;
            endif;
        
        
        elseif ($modemem == 1)
        :
            if ($c_h && ! is_admin() && ($group != "page" || FASTCACHEVARMULTICACHEUAURLISSET))
            :
                //$cache_id = $this->_getCacheId($id, $group, $user, $subgroup);
            $cache_id = $this->_getCacheId($id, $group, $user);
                $back = self::$_db->get($cache_id);
                return $back;
            
            
            else
            :
                $back = $this->getfilecache($id, $group, $checkTime, $user, $subgroup);
                Return $back;
            endif;
        
        
        else
        :
            if ($c_h && $group != "page" || FASTCACHEVARMULTICACHEUAURLISSET):
                if (FASTCACHEVARMULTICACHESTORAGETEMP)
                :
                    //$cache_id = $this->_getCacheIdb($id, $group, $user, $subgroup);
                $cache_id = $this->_getCacheIdb($id, $group, $user);
                
                
                else
                :
                   // $cache_id = $this->_getCacheId($id, $group, $user, $subgroup);
                $cache_id = $this->_getCacheId($id, $group, $user);
                endif;
                
                $back = self::$_db->get($cache_id);
                return $back;
            
            
            else
            :
                $back = $this->getfilecache($id, $group, $checkTime, $user, $subgroup);
                Return $back;
            endif;
        endif;
        //
        
        //$cache_id = $this->_getCacheId($id, $group, $user, $subgroup);
        $cache_id = $this->_getCacheId($id, $group, $user);
        $back = self::$_db->get($cache_id);
        return $back;
    
    }

    public function store($id, $group, $data, $user = 0, $subgroup = null)
    {
        
        // $modemem = $this->_config->get('multicachedistribution', 0);
        $modemem = FASTCACHEVARMULTICACHEDISTRIBUTION;
        $c_h = FASTCACHEVAR_CACHEHANDLER && MULTICACHE_MEMCACHE_READY_TESTED;
        if ($modemem == 3)
        :
            if ($c_h && ! $user && ($group != "page" || FASTCACHEVARMULTICACHELOWERURLISSET))
            :
            
            
            else
            :
                $status = $this->putinfilecache($id, $group, $data, $user, $subgroup);
                Return $status;
            endif;
        
        
        elseif ($modemem == 2)
        :
            if ($c_h && FASTCACHEVARMULTICACHEUAURLISSET)
            :
            
            
            else
            :
                $status = $this->putinfilecache($id, $group, $data, $user, $subgroup);
                Return $status;
            endif;
        
        
        elseif ($modemem == 1)
        :
            if ($c_h && ! is_admin() && ($group != "page" || FASTCACHEVARMULTICACHEUAURLISSET))
            :
            
            
            else
            :
                $status = $this->putinfilecache($id, $group, $data, $user, $subgroup);
                Return $status;
            endif;
        
        
        else
        :
            if ($c_h && $group != "page" || FASTCACHEVARMULTICACHEUAURLISSET)
            :
            
            
            else
            :
                $status = $this->putinfilecache($id, $group, $data, $user, $subgroup);
                Return $status;
            endif;
        endif;
        
        if ($modemem == 0 && FASTCACHEVARMULTICACHESTORAGETEMP)
        {
            //$cache_id = $this->_getCacheIdb($id, $group, $user, $subgroup);
            $cache_id = $this->_getCacheIdb($id, $group, $user);
        }
        else
        {
           // $cache_id = $this->_getCacheId($id, $group, $user, $subgroup);
            $cache_id = $this->_getCacheId($id, $group, $user);
        }
        
        // $lifetime = (int) $this->_config->get('cachetime', 15);
        $lifetime = (int) FASTCACHEVARMULTICACHECACHETIME;
        if ($this->_lifetime == $lifetime)
        {
            $this->_lifetime = $lifetime * 60;
        }
        // $this->_compress = $this->_config->get('multicache_compress', false) == false ? 0 : MEMCACHE_COMPRESSED;
        
        $this->_compress = FASTCACHEVARMULTICACHECOMPRESS;
        
        $result = self::$_db->add($cache_id, $data, $this->_compress, $this->_lifetime);
        
        if (! $result)
        {
            if (FASTCACHEVARMULTICACHE_DEBUG)
            {
                $errormessage = sprintf(__('LIB_FASTCACHE_COM_MULTICACHE_ADDFAILED_TRYING_RESET_STORE', 'multicache-plugin'), $id, $group, $cache_id, $this->_lifetime);
                $this->loaderrorlogger($errormessage);
            }
            if (! self::$_db->replace($cache_id, $data, $this->_compress, $this->_lifetime))
            {
                $result = self::$_db->set($cache_id, $data, $this->_compress, $this->_lifetime);
                if (FASTCACHEVARMULTICACHE_DEBUG && ! $result)
                {
                    
                    $errormessage = sprintf(__('LIB_FASTCACHE_COM_MULTICACHE_RESETANDSTORE_FAILEDASWELL', 'multicache-plugin'), $id, $group, $cache_id);
                    $this->loaderrorlogger($errormessage);
                }
            }
        }
        
        return true;
    
    }

    public function getAll()
    {

        parent::getAll();
        
        // JLoader::import('stat', JPATH_ADMINISTRATOR . '/components/com_multicache/models');
        require_once plugin_dir_path(__FILE__) . 'multicache_stat.php';
        // $comp = JModelLegacy::getInstance('stat', 'MulticacheModel');
        $comp = new MulticacheStat();
        $comp->prepareStat();
        $Allkeys = $comp->getAllKeys();
        
        $sizeobject = $this->getSizeObject();
        $data = $sizestandards = array();
        foreach ($sizeobject as $obj)
        {
            
            $gp = $obj->mgroup;
            $sz = $obj->sz;
            $sizestandards[$gp] = $sz;
        }
        if (FASTCACHEVARMULTICACHE_DEBUG)
        {
            $message = __('fastcache get all called', 'multicache-plugin');
            $this->loaderrorlogger($message, 'message');
        }
        if (empty($Allkeys))
        {
            if (FASTCACHEVARMULTICACHE_DEBUG)
            {
                $message = __('fastcache getall called no keys received');
                $this->loaderrorlogger($message);
            }
           // Return false;
        }
        
        if(!empty($Allkeys))
        {
        $secret = $this->_hash;
        $countelements = array();
        
        foreach ($Allkeys as $key)
        {
            $narray = explode('-', $key);
            if ($narray !== false && $narray[0] == $secret && $narray[1] == 'cache')
            {
                $group = $narray[2];
                $countelements[$group] = isset($countelements[$group]) ? ++ $countelements[$group] : 1;
                if (! isset($data[$group]))
                {
                    $item = new MulticacheStorageHelper($group);
                }
                else
                {
                    $item = $data[$group];
                }
                $item->updateSize(((int) $sizestandards[$group]) / 1024);
                
                $data[$group] = $item;
            }
        }
        }
       
        // startfile
        $path = $this->_root;
        $folders = $this->_folders($path);
        $f_c_data_raw = array();
        foreach ($folders as $folder)
        {
            $files = $this->_filesInFolder($path . '/' . $folder);
            $item = new MulticacheStorageHelper($folder);
            foreach ($files as $file)
            {
                $item->updateSize(filesize($path . '/' . $folder . '/' . $file) / 1024);
            }
            $f_c_data_raw[$folder] = $item;
        }
        //top level files
        $tl_files = $this->_filesInFolder($path . '/' );
        $tl_item = new MulticacheStorageHelper('top_level_multicache');
        foreach($tl_files As $key => $file)
        {
        	if(strpos($file,'index') !== false)
        	{
        		unset($tl_files[$key]);
        		continue;
        	}
        	$tl_item->updateSize(filesize($path . '/'  . $file) / 1024);
        }
        if(!empty($tl_files))
        {
        	$f_c_data_raw['top_level_folder'] = $tl_item;
        }
        //end top level files
        // return $data;
        // end file
        // $this->loadfileStorageClass();
        /*
         * $options = array(
         * 'cachebase' => $this->_root
         * );
         */
        // $f_c_data_raw = new JCacheStorageFile($options);
        foreach ($f_c_data_raw as $key => $obj)
        {
            $obj->group = $obj->group . '_file_cache';
            $f_c_data[$key . '_filecache'] = $obj;
        }
        
        if (! empty($data) && ! empty($f_c_data))
        {
            $data = array_merge($data, $f_c_data);
        }
        elseif (empty($data) && ! empty($f_c_data))
        {
            $data = $f_c_data;
        }
        Return $data;
    
    }
/*
 *     public function getAll()
    {

        parent::getAll();
        
        JLoader::import('stat', JPATH_ADMINISTRATOR . '/components/com_multicache/models');
        $comp = JModelLegacy::getInstance('stat', 'MulticacheModel');
        $comp->prepareStat();
        $Allkeys = $comp->getAllKeys();
        
        $sizeobject = $this->getSizeObject();
        $data = $sizestandards = array();
        foreach ($sizeobject as $obj)
        {
            
            $gp = $obj->mgroup;
            $sz = $obj->sz;
            $sizestandards[$gp] = $sz;
        }
        if (FASTCACHEVARMULTICACHE_DEBUG)
        {
            $message = JText::_('LIB_FASTCACHE_GET_ALL_CALLED_MESSAGE');
            $this->loaderrorlogger($message, 'message');
        }
        if (empty($Allkeys))
        {
            if (FASTCACHEVARMULTICACHE_DEBUG)
            {
                $message = JText::_('LIB_FASTCACHE_MULTICACHE_GETALL_NO_KEYS_RECEIVED');
                $this->loaderrorlogger($message);
            }
            // start proc added July 10: its better to split this into a file handler subroutine
            $data = array();
            $this->loadfileStorageClass();
            $options = array(
                'cachebase' => $this->_root
            );
            $f_c_data_raw = new JCacheStorageFile($options);
            foreach ($f_c_data_raw->getAll() as $key => $obj)
            {
                $obj->group = $obj->group . '_file_cache';
                $f_c_data[$key . '_filecache'] = $obj;
            }
            
            if (! empty($data) && ! empty($f_c_data))
            {
                $data = array_merge($data, $f_c_data);
            }
            elseif (empty($data) && ! empty($f_c_data))
            {
                $data = $f_c_data;
            }
            Return $data;
            // end proc
            Return false;
        }
        $secret = $this->_hash;
        $countelements = array();
        
        foreach ($Allkeys as $key)
        {
            $narray = explode('-', $key);
            if ($narray !== false && $narray[0] == $secret && $narray[1] == 'cache')
            {
                $group = $narray[2];
                $countelements[$group] = isset($countelements[$group]) ? ++ $countelements[$group] : 1;
                if (! isset($data[$group]))
                {
                    $item = new JCacheStorageHelper($group);
                }
                else
                {
                    $item = $data[$group];
                }
                $item->updateSize(((int) $sizestandards[$group]) / 1024);
                
                $data[$group] = $item;
            }
        }
        if ($memcachedtest)
        {
            $this->endmemcachedinstance();
        }
        
        $this->loadfileStorageClass();
        $options = array(
            'cachebase' => $this->_root
        );
        $f_c_data_raw = new JCacheStorageFile($options);
        foreach ($f_c_data_raw->getAll() as $key => $obj)
        {
            $obj->group = $obj->group . '_file_cache';
            $f_c_data[$key . '_filecache'] = $obj;
        }
        
        if (! empty($data) && ! empty($f_c_data))
        {
            $data = array_merge($data, $f_c_data);
        }
        elseif (empty($data) && ! empty($f_c_data))
        {
            $data = $f_c_data;
        }
        Return $data;
    
    }
 */
    public function gc()
    {

        $result = true;
        // Files older than lifeTime get deleted from cache
        $files = $this->_filesInFolder($this->_root, '', true, true, array(
            '.svn',
            'CVS',
            '.DS_Store',
            '__MACOSX',
            'index.html'
        ));
        foreach ($files as $file)
        {
            $time = @filemtime($file);
            if (($time + $this->_lifetime) < $this->_now || empty($time))
            {
                $result |= @unlink($file);
            }
        }
        return $result;
    
    }

    public function remove($id, $group, $user = 0, $subgroup = null)
    {
    	$c_h = FASTCACHEVAR_CACHEHANDLER && MULTICACHE_MEMCACHE_READY_TESTED;
        // $this->loadfileStorageClass();
        $options = array(
            'cachebase' => $this->_root
        );
        
        // $f_remove = new JCacheStorageFile($options);
        $success = $this->removeFile($id, $group, $user, $subgroup);
        
        //$cache_id = $this->_getCacheId($id, $group, $user, $subgroup);
        $cache_id = $this->_getCacheId($id, $group, $user);
        // blocked till we settle what is required for cart to work
        if ($group == 'page' && $c_h)
        {
            
            $flag = 0;
            $cache_id_arr = $this->_getAlternateCacheId($id, $group);
            foreach ($cache_id_arr as $cache_id_a)
            {
                if (! empty($cache_id_a))
                {
                    $success_flag = self::$_db->delete($cache_id_a);
                    $flag = $flag || $success_flag;
                }
            }
            Return $flag || $success;
        }
        //
        if (isset($cache_id) && $c_h)
        {
            
            $flg = self::$_db->delete($cache_id);
            return $success || $flg;
        }
        if (FASTCACHEVARMULTICACHE_DEBUG)
        {
            $emessage = sprintf(__('LIB_FASTCACHE_COM_MULTICACHE_REMOVEFAILED_ON_GROUP', 'multicache-plugin'), $id, $group);
            $this->loaderrorlogger($emessage, 'notice');
        }
        Return false || $success;
    
    }

    protected function removeFile($id, $group, $user = 0, $subgroup = null)
    {

        $path = $this->_getFilePath($id, $group, $user, $subgroup);
        if (! @unlink($path))
        {
            return false;
        }
        return true;
    
    }

    public function clean($group, $mode = null)
    {
        
        // @copyright Copyright (C) 2015 OnlineMarketingConsultants.in Coder:WD
        if (stripos($group, '_file_cache') !== false)
        {
            $group = str_ireplace('_file_cache', '', $group);
            $return = $this->_fileclean($group, $mode);
            Return $return;
            // filestart
            
            // fileend
            /*
             * $this->loadfileStorageClass();
             * $group = str_ireplace('_file_cache', '', $group);
             * $options = array(
             * 'cachebase' => $this->_root
             * );
             * $f_clean = new JCacheStorageFile($options);
             * $f_clean->clean($group, $mode);
             * Return;
             */
        }
        if ($mode == 'both')
        {
            
            $return = $this->_fileclean($group, $mode);
        }
        if (FASTCACHEVARMULTICACHE_DEBUG)
        {
            
            $e_message = __('LIB_FASTCACHE_COM_MULTICACHE_CACHE_CLEAN_CALLED' . $group . ' - ' . $mode);
            $this->loaderrorlogger($e_message);
        }
        $memcachedtest = $this->startmemcachedinstance();
        if ($memcachedtest)
        {
            $Allkeys = self::$_dbadmin->getAllKeys();
        }
        else
        {
            require_once plugin_dir_path(__FILE__) . 'multicache_stat.php';
            // $comp = JModelLegacy::getInstance('stat', 'MulticacheModel');
            $comp = new MulticacheStat();
            $comp->prepareStat();
            $Allkeys = $comp->getAllKeys();
            // JLoader::import('stat', JPATH_ADMINISTRATOR . '/components/com_multicache/models');
            // $comp = JModelLegacy::getInstance('stat', 'MulticacheModel');
            // $comp->prepareStat();
            
            // $Allkeys = $this->pluckAllKeys();
        }
        
        if (empty($Allkeys))
        {
            Return True;
        }
        
        if ($mode == 'notgroup')
        {
            
            foreach ($Allkeys as $key)
            {
                if (strpos($key, $group) !== false)
                :
                
                
                else
                :
                    if ($memcachedtest)
                    :
                        self::$_dbadmin->delete($key, 0);
                    
                    
                    else
                    :
                        self::$_db->delete($key, 0);
                    endif;
                endif;
            }
        }
        else
        {
            foreach ($Allkeys as $key)
            {
                if (strpos($key, $group) !== false)
                :
                    if ($memcachedtest)
                    :
                        self::$_dbadmin->delete($key, 0);
                    
                    
                    else
                    :
                        self::$_db->delete($key, 0);
                    endif;
                




                endif;
            }
        }
        $this->endmemcachedinstance();
        
        Return true;
    
    }

    /**
     * Test to see if the cache storage is available.
     *
     * @return boolean True on success, false otherwise.
     *        
     * @since 12.1
     */
    public static function isSupported()
    {

        if ((extension_loaded('memcache') && class_exists('Memcache')) != true)
        {
            return false;
        }
        
        $config = MulticacheFactory::getBlogConfig();
        $host = false !== $config ?  $config->getC('multicache_server_host', 'localhost') : 'localhost';
        $port = false !== $config ?  $config->getC('multicache_server_port', 11211) : 11211;
        
        $memcache = new Memcache();
        $memcachetest = @$memcache->connect($host, $port);
        
        if (! $memcachetest)
        {
            return false;
        }
        else
        {
            return true;
        }
    
    }
    
    public function _isSupported()
    {
    	Return self::isSupported();
    }

    public function lock($id, $group, $locktime)
    {

        if (FASTCACHEVARMULTICACHEFORCELOCKINGOFF /*$this->_config->get('force_locking_off',true)*/ )
        {
            Return false;
        }
        // Return false;
        $returning = new stdClass();
        $returning->locklooped = false;
        
        $looptime = $locktime * 10;
        
        //$cache_id = $this->_getCacheId($id, $group, $user, $subgroup);
        $cache_id = $this->_getCacheId($id, $group, $user);
        
        $data_lock = self::$_db->add($cache_id . '_lock', 1, false, $locktime);
        
        if ($data_lock === false)
        {
            
            $lock_counter = 0;
            
            // Loop until you find that the lock has been released.
            // That implies that data get from other thread has finished
            while ($data_lock === false)
            {
                
                if ($lock_counter > $looptime)
                {
                    $returning->locked = false;
                    $returning->locklooped = true;
                    break;
                }
                
                usleep(100);
                $data_lock = self::$_db->add($cache_id . '_lock', 1, false, $locktime);
                $lock_counter ++;
            }
        }
        $returning->locked = $data_lock;
        
        return $returning;
    
    }

    public function unlock($id, $group = null)
    {
        // Return false;
        //$cache_id = $this->_getCacheId($id, $group, $user, $subgroup) . '_lock';
    	$cache_id = $this->_getCacheId($id, $group, $user) . '_lock';
        
        return self::$_db->delete($cache_id);
    
    }

    protected function lockindex()
    {

        Return false;
    
    }

    protected function unlockindex()
    {

        Return false;
    
    }

    protected function putinfilecache($id, $group, $data, $user = 0, $subgroup = null)
    {
    if(!isset($this->_root))
     {
     	MulticacheHelper::log_error('cache base not defined','fatal-cachebase');
	    Return false;
     }
        $written = false;
       // $path = $this->_getFilePath($id, $group, $user, $subgroup);
        $path = $this->_getFilePath($id, $group, $user);
        if (file_exists($path) && empty($user))
        {
        	//added empty($user). In wp if user logged in he can change the content we want those immediate changes to reflect
            // echo "file exists in $path";
            Return true;
        }
        $die = '<?php die("Access Denied"); ?>#x#';
        
        $data = $die . $data;
        
        $_fileopen = @fopen($path, "wb");
        
        if ($_fileopen)
        {
            $len = strlen($data);
            @fwrite($_fileopen, $data, $len);
            $written = true;
        }
        
        if ($written && ($data == file_get_contents($path)))
        {
            return true;
        }
        else
        {
            return false;
        }
    
    }

    protected function _getFilePath($id, $group, $user = 0, $subgroup = null)
    {

        if (FASTCACHEVARMULTICACHEDISTRIBUTION == 0 && FASTCACHEVARMULTICACHESTORAGETEMP /*$this->_config->get('multicachedistribution', 0) == 0*/ )
        {
            $name = $this->_getCacheIdb($id, $group, $user, $subgroup);
        }
        else
        {
            $name = $this->_getCacheId($id, $group, $user, $subgroup);
        }
        
        // $dir = isset($subgroup) ? $this->_root . '/' . 'multicache-' . $subgroup : $this->_root . '/' . 'multicache-' . $group;
        $dir = isset($subgroup) ? $this->_root . 'multicache-' . $subgroup : $this->_root . 'multicache-' . $group;
        
        // If the folder doesn't exist try to create it
        if (! is_dir($dir))
        {
            
            // Make sure the index file is there
            $indexFile = $dir . '/index.html';
            @mkdir($dir) && file_put_contents($indexFile, '<!DOCTYPE html><title></title>');
            
        }
        
        // Make sure the folder exists
        if (! is_dir($dir))
        {
            return false;
        }
        return $dir . '/' . $name . '.php';
    
    }

    protected function getfilecache($id, $group, $checkTime = true, $user = 0, $subgroup = null)
    {

        $data = false;
        
        //$path = $this->_getFilePath($id, $group, $user, $subgroup);
        $path = $this->_getFilePath($id, $group, $user);
        
        if ($checkTime == false || ($checkTime == true && $this->_checkExpire($id, $group, $user, $subgroup) === true))
        {
            if (file_exists($path))
            {
                $data = file_get_contents($path);
                if ($data)
                {
                    // Remove the initial die() statement
                    $data = str_replace('<?php die("Access Denied"); ?>#x#', '', $data);
                }
            }
            
            return $data;
        }
        else
        {
            return false;
        }
    
    }

    protected function _checkExpire($id, $group, $user = 0, $subgroup = null)
    {

        $path = $this->_getFilePath($id, $group, $user, $subgroup);
        
        // Check prune period
        if (file_exists($path))
        {
            $time = @filemtime($path);
            if (($time + $this->_lifetime) < $this->_now || empty($time))
            {
                @unlink($path);
                return false;
            }
            return true;
        }
        return false;
    
    }

    protected function loaderrorlogger($emessage = null)
    {

        error_log($emessage);
    
    }

    protected function getSizeObject()
    {

        global $wpdb;
        $query = "SELECT mgroup,AVG(size) As sz FROM wp_multicache_itemscache WHERE mgroup != '' GROUP BY mgroup";
        Return $wpdb->get_results($query, OBJECT);
    
        /*
         * $db = JFactory::getDbo();
         * $query = $db->getQuery(true);
         * $query->select($db->quoteName('mgroup'));
         * $query->select(' AVG(' . $db->quoteName('size') . ') As sz ');
         * $query->from($db->quoteName('#__multicache_itemscache'));
         * $query->where($db->quoteName('mgroup') . ' != ' . $db->quote(''));
         * $query->group($db->quoteName('mgroup'));
         * $db->setQuery($query);
         * Return $db->loadObjectlist();
         */
    }

    protected function startmemcachedinstance()
    {

        $config = MulticacheFactory::getBlogConfig();
        
        if (! (class_exists('Memcached') && extension_loaded('memcached')))
        {
            if (FASTCACHEVARMULTICACHE_DEBUG)
            {
                
                $errormessage = __('The Memcached extension could not be loaded in the fastcache library', 'multicache-plugin');
                MulticacheHelper::log_error($errormessage ,'fastcache-lib');
            }
            Return False;
        }
        if (self::$_dbadmin === null)
        :
            $server = array();
            $server['host'] = false !== $config ? $config->getC('multicache_server_host', 'localhost') : 'localhost';
            $server['port'] = false !== $config ? $config->getC('multicache_server_port', 11211) : 11211;
            self::$_dbadmin = new Memcached();
            $memcachedtest = self::$_dbadmin->addServer($server['host'], $server['port']);
        

        endif;
        if (! $memcachedtest)
        {
            if (FASTCACHEVARMULTICACHE_DEBUG)
            {
                $errormessage = __('Memcached test failed in startmemcachedinstance of fastcache lib' , 'multicache-plugin');
                
                MulticacheHelper::log_error($errormessage ,'fastcache-lib');
            }
            Return False;
        }
        
        Return $memcachedtest;
    
    }

    protected function endmemcachedinstance()
    {

        if (self::$_dbadmin != null)
        :
            self::$_dbadmin->quit();
            self::$_dbadmin = null;
        











        endif;
    
    }

    protected function loadfileStorageClass()
    {

        if (! class_exists('JCacheStorageFile'))
        {
            JLoader::register('JCacheStorageFile', dirname(__FILE__) . '/file.php');
        }
    
    }

    protected function _folders($path, $filter = '.', $recurse = false, $fullpath = false, $exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX'), $excludefilter = array('^\..*'))
    {

        $arr = array();
        // Check to make sure the path valid and clean
        $path = $this->_cleanPath($path);
        // Is the path a folder?
        if (! is_dir($path) && FASTCACHEVARMULTICACHE_DEBUG)
        {
            error_log('filesystem error path is not a folder');
            
            return false;
        }
        // Read the source directory
        if (! ($handle = @opendir($path)))
        {
            return $arr;
        }
        if (count($excludefilter))
        {
            $excludefilter_string = '/(' . implode('|', $excludefilter) . ')/';
        }
        else
        {
            $excludefilter_string = '';
        }
        while (($file = readdir($handle)) !== false)
        {
            if (($file != '.') && ($file != '..') && (! in_array($file, $exclude)) && (empty($excludefilter_string) || ! preg_match($excludefilter_string, $file)))
            {
                $dir = $path . '/' . $file;
                $isDir = is_dir($dir);
                if ($isDir)
                {
                    // Removes filtered directories
                    if (preg_match("/$filter/", $file))
                    {
                        if ($fullpath)
                        {
                            $arr[] = $dir;
                        }
                        else
                        {
                            $arr[] = $file;
                        }
                    }
                    if ($recurse)
                    {
                        if (is_int($recurse))
                        {
                            $arr2 = $this->_folders($dir, $filter, $recurse - 1, $fullpath, $exclude, $excludefilter);
                        }
                        else
                        {
                            $arr2 = $this->_folders($dir, $filter, $recurse, $fullpath, $exclude, $excludefilter);
                        }
                        $arr = array_merge($arr, $arr2);
                    }
                }
            }
        }
        closedir($handle);
        return $arr;
    
    }

    protected function _filesInFolder($path, $filter = '.', $recurse = false, $fullpath = false, $exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX'), $excludefilter = array('^\..*', '.*~'))
    {

        $arr = array();
        // Check to make sure the path valid and clean
        $path = $this->_cleanPath($path);
        // Is the path a folder?
        if (! is_dir($path) && FASTCACHEVARMULTICACHE_DEBUG)
        {
            error_log('fif error path is not a folder');
            
            return false;
        }
        // Read the source directory.
        if (! ($handle = @opendir($path)))
        {
            return $arr;
        }
        if (count($excludefilter))
        {
            $excludefilter = '/(' . implode('|', $excludefilter) . ')/';
        }
        else
        {
            $excludefilter = '';
        }
        while (($file = readdir($handle)) !== false)
        {
            if (($file != '.') && ($file != '..') && (! in_array($file, $exclude)) && (! $excludefilter || ! preg_match($excludefilter, $file)))
            {
                $dir = $path . '/' . $file;
                $isDir = is_dir($dir);
                if ($isDir)
                {
                    if ($recurse)
                    {
                        if (is_int($recurse))
                        {
                            $arr2 = $this->_filesInFolder($dir, $filter, $recurse - 1, $fullpath);
                        }
                        else
                        {
                            $arr2 = $this->_filesInFolder($dir, $filter, $recurse, $fullpath);
                        }
                        $arr = array_merge($arr, $arr2);
                    }
                }
                else
                {
                    if (preg_match("/$filter/", $file))
                    {
                        if ($fullpath)
                        {
                            $arr[] = $path . '/' . $file;
                        }
                        else
                        {
                            $arr[] = $file;
                        }
                    }
                }
            }
        }
        closedir($handle);
        return $arr;
    
    }

    protected function _cleanPath($path, $ds = DIRECTORY_SEPARATOR)
    {

        $path = trim($path);
        if (empty($path))
        {
            $path = $this->_root;
        }
        else
        {
            // Remove double slashes and backslahses and convert all slashes and backslashes to DIRECTORY_SEPARATOR
            $path = preg_replace('#[/\\\\]+#', $ds, $path);
        }
        return $path;
    
    }

    protected function _deleteFolder($path)
    {
        // Sanity check
        if (! $path || ! is_dir($path) || empty($this->_root))
        {
            if (FASTCACHEVARMULTICACHE_DEBUG)
            {
                error_log('FILESYSTEM_ERROR_DELETE_BASE_DIRECTORY');
            }
            
            return false;
        }
        $path = $this->_cleanPath($path);
        // Check to make sure path is inside cache folder, we do not want to delete Joomla root!
        $pos = strpos($path, $this->_cleanPath($this->_root));
        if ($pos === false || $pos > 0)
        {
            if (FASTCACHEVARMULTICACHE_DEBUG)
            {
                error_log('FILESYSTEM_ERROR_PATH_IS_NOT_A_FOLDER');
            }
            
            return false;
        }
        // Remove all the files in folder if they exist; disable all filtering
        $files = $this->_filesInFolder($path, '.', false, true, array(), array());
        if (! empty($files) && ! is_array($files))
        {
            if (@unlink($files) !== true)
            {
                return false;
            }
        }
        elseif (! empty($files) && is_array($files))
        {
            foreach ($files as $file)
            {
                $file = $this->_cleanPath($file);
                // In case of restricted permissions we zap it one way or the other
                // as long as the owner is either the webserver or the ftp
                if (@unlink($file))
                {
                    // Do nothing
                }
                else
                {
                    $filename = basename($file);
                    if (FASTCACHEVARMULTICACHE_DEBUG)
                    {
                        error_log('FILESYSTEM_DELETE_FAILED' . $filename);
                    }
                    
                    return false;
                }
            }
        }
        // Remove sub-folders of folder; disable all filtering
        $folders = $this->_folders($path, '.', false, true, array(), array());
        foreach ($folders as $folder)
        {
            if (is_link($folder))
            {
                // Don't descend into linked directories, just delete the link.
                if (@unlink($folder) !== true)
                {
                    return false;
                }
            }
            elseif ($this->_deleteFolder($folder) !== true)
            {
                return false;
            }
        }
        // In case of restricted permissions we zap it one way or the other
        // as long as the owner is either the webserver or the ftp
        if (@rmdir($path))
        {
            $ret = true;
        }
        else
        {
            if (FASTCACHEVARMULTICACHE_DEBUG)
            {
                error_log('FILESYSTEM_ERROR_FOLDER_DELETE' . $path);
            }
            
            $ret = false;
        }
        return $ret;
    
    }

    protected function _fileclean($group, $mode = null)
    {

        $return = true;
        
        $folder = $group;
        if (trim($folder) == '' && $mode != 'outer')
        {
            $mode = 'notgroup';
        }
        if (trim($folder) == 'top_level_multicache' && $mode != 'outer')
        {
        	$mode = 'tl_clean';
        }
        switch ($mode)
        {
            case 'outer'://check this op
               // $outer_path = preg_replace('~/$~', '', $this->_root);
               $outer_path = rtrim( $this->_root, '/\\' );
                $files = $this->_filesInFolder($outer_path, '', true, true, array(
                    '.svn',
                    'CVS',
                    '.DS_Store',
                    '__MACOSX',
                    'index.html',
                	                ));
                $cp = $this->_cleanPath($this->_root);
                
                foreach ($files as $file)
                {
                    
                    if (is_file($file) && strpos($file, $cp) === 0)
                    {
                        $return |= @unlink($file);
                    }
                }
                
                break;
            case 'tl_clean':
            	$files = $this->_filesInFolder($this->_root);
            	$cp = $this->_cleanPath($this->_root);
            	foreach ($files as $key => $file)
            	{
            	if(strpos($file,'index') !== false)
            	{
            		unset($files[$key]);
            		continue;
            	}
            	$fpath_file = $this->_root .$file;
            		if (is_file($fpath_file) && strpos($fpath_file, $cp) === 0)
            		{
            			$return |= @unlink($fpath_file);
            		}
            	}
            	break;
            case 'notgroup':
                $folders = $this->_folders($this->_root);
                for ($i = 0, $n = count($folders); $i < $n; $i ++)
                {
                    if ($folders[$i] != $folder)
                    {
                        $return |= $this->_deleteFolder($this->_root . '/' . $folders[$i]);
                    }
                }
                break;
            case 'group':
            default:
                if (is_dir($this->_root . '/' . $folder))
                {
                    $return = $this->_deleteFolder($this->_root . '/' . $folder);
                }
                break;
        }
        return $return;
    
    }

}