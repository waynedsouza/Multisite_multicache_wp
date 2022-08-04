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
require_once plugin_dir_path(__FILE__) . 'multicache_factory.php';

class MulticacheStat
{

    protected static $_dbmain = null;

    protected static $_dbadmin = null;

    protected $_persistent = false;

    protected $_compress = 0;

    public function __construct($config = array())
    {

        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                'id',
                'a.id',
                'group_id',
                'a.group_id'
            );
        }
    
    }

    public function getPageByKey($Page_key)
    {

        $memcache_started = $this->startmemcacheinstance();
        if ($memcache_started)
        {
            $page_object = self::$_dbmain->get($Page_key);
            
            $this->endmemcacheinstance();
        }
        if (! empty($page_object))
        {
            $page_object = unserialize($page_object);
            $multicache_meta = isset($page_object['multicache_meta']) ? $page_object['multicache_meta'] :null;
            if(isset($multicache_meta['id']))
            {
            	Return $multicache_meta['id'];
            }
            //$search = '#<base href=\"([^"]*)\"\s*\/>#';
            //preg_match($search, $page_object[body], $match);
            //Return $match[1];
        }
        Return false;
    
    }

    public function getPageByKeyRenderPage($Page_key)
    {

        $memcache_started = $this->startmemcacheinstance();
        if ($memcache_started)
        {
            $page_object = self::$_dbmain->get($Page_key);
            
            $this->endmemcacheinstance();
        }
        /*
         * if(!empty($page_object)){
         * $page_object = unserialize($page_object);
         * $search ='#<base href=\"([^"]*)\"\s*\/>#';
         * preg_match($search ,$page_object[body] ,$match);
         * Return $match[1];
         * }
         * Return false;
         */
        Return $page_object;
    
    }

    public function getAllKeys()
    {
    	$config = MulticacheFactory::getBlogConfig();
        $memcached_started = $this->startmemcachedinstance();
        if ($memcached_started)
        {
            //$config = MulticacheFactory::getConfig();
            $this->_hash = false !== $config ? md5($config->getC('secret')): '';
            $Allkeys = self::$_dbadmin->getAllKeys();
            
            foreach ($Allkeys as $del => $key)
            {
                if (stripos($key, $this->_hash) === false)
                {
                    unset($Allkeys[$del]);
                }
            }
        }
        else
        {
            $Allkeys = $this->pluckAllKeys();
        }
        if (is_object(self::$_dbadmin))
        {
            $this->endmemcachedinstance();
        }
        
        Return $Allkeys;
    
    }

    public function prepareStat()
    {

        $memcached_started = $this->startmemcachedinstance();
        $memcache_started = $this->startmemcacheinstance();
        $this->cleardb('wp_multicache_itemscache');
        if ($memcached_started)
        {
            $Allkeys = self::$_dbadmin->getAllKeys();
            
            $this->storetodb($Allkeys, 'wp_multicache_itemscache');
        }
        
        if ($memcache_started)
        {
            $items_items = self::$_dbmain->getStats('items');
            $this->cleardb('wp_multicache_items');
            $this->storetodbItems($items_items, 'wp_multicache_items');
            $items_stats = self::$_dbmain->getStats();
            $this->storetodbItemsstats($items_stats, 'wp_multicache_items_stats');
            $items_slabs = self::$_dbmain->getStats('slabs');
            $this->cleardb('wp_multicache_items_slabs');
            $this->storetodbItemsslabs($items_slabs, 'wp_multicache_items_slabs');
            
            $slab = $this->getstatslabs();
            $this->processcachesizes($slab);
        }
    
    }

    public function deleteKeys($cache_keys)
    {

        $memcached_started = $this->startmemcachedinstance();
        if ($memcached_started)
        {
            foreach ($cache_keys as $key => $cache_key)
            {
                
                self::$_dbadmin->delete($cache_key);
            }
            if (is_object(self::$_dbadmin))
            {
                $this->endmemcachedinstance();
            }
            Return true;
        }
        $memcache_started = $this->startmemcacheinstance();
        if ($memcache_started)
        {
            foreach ($cache_keys as $key => $cache_key)
            {
                self::$_dbmain->delete($cache_key);
            }
            if (is_object(self::$_dbmain))
            {
                $this->endmemcacheinstance();
            }
            Return true;
        }
        Return false;
    
    }
    
    // start methods
    protected function processcachesizes($slabs)
    {

        $cachedump_slab = array();
        foreach ($slabs as $slab_size)
        {
            $cachedump_slab[$slab_size] = self::$_dbmain->getStats('cachedump', $slab_size);
        }
        foreach ($cachedump_slab as $slab_size => $key_params)
        {
            foreach ($key_params as $cache_key => $cache_params)
            {
                if ($id = $this->keyExistsdb($cache_key))
                {
                    
                    $this->updateParamsdb($id, $cache_key, $slab_size, $cache_params);
                }
                else
                {
                    
                    $this->insertParamsdb($cache_key, $slab_size, $cache_params);
                }
            }
        }
    
    }

    protected function keyExistsdb($c_key)
    {

        global $wpdb;
        $table = $wpdb->prefix.'multicache_itemscache';
        $query = "SELECT id FROM $table WHERE item = '$c_key' ";
        $key_obj = $wpdb->get_var($query);
        /*
         * $db = JFactory::getDbo();
         * $query = $db->getQuery(true);
         * $query->select($db->quoteName('id'));
         * $query->from($db->quoteName('#__multicache_itemscache'));
         * $query->where($db->quoteName('item') . ' = ' . $db->quote($c_key));
         * $db->setQuery($query);
         * $key_obj = $db->loadObject();
         */
        
        // Return $key_obj->id;
        Return $key_obj;
    
    }

    protected function updateParamsdb($id, $c_key, $slab_size, $c_params)
    {

        global $wpdb;
        $mstimestamp = microtime(true);
        $nowtime = date('Y-m-d');
        $cdarray = explode('-', $c_key);
        $format = array(
            '%d',
            '%s',
            '%d',
            '%s',
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s'
        );
        $data = array(
            'id' => $id,
            'item' => $c_key,
            'size' => $c_params[0],
            'expiration' => $c_params[1],
            'itemnumber' => $slab_size,
            'mstimestamp' => $mstimestamp,
            'nowtimestamp' => $nowtime,
            'sitehash' => $cdarray[0],
            'type' => trim(preg_replace('/\[.*/' . s, '', strval($cdarray[1]))),
            'mgroup' => ! empty($cdarray[2]) ? $cdarray[2] : trim(preg_replace('/\[.*/' . s, '', strval($cdarray[1])))
        );
        $table = "wp_multicache_itemscache";
        $where = array(
            'id' => $id
        );
        $where_format = array(
            '%d'
        );
        $wpdb->update($table, $data, $where, $format, $where_format);
    
        /*
         * $db = JFactory::getDbo();
         * $nowtime = date('Y-m-d');
         * $mstimestamp = microtime(true);
         * $cdarray = explode('-', $c_key);
         * $updateObj = new stdClass();
         * $updateObj->id = $id;
         * $updateObj->item = $c_key;
         * $updateObj->size = $c_params[0];
         * $updateObj->expiration = $c_params[1];
         * $updateObj->itemnumber = $slab_size;
         * $updateObj->mstimestamp = $mstimestamp;
         * $updateObj->nowtimestamp = $nowtime;
         * $updateObj->sitehash = $cdarray[0];
         */
        // $updateObj->type = trim(preg_replace('/\[.*/' . s, '', strval($cdarray[1])));
        // / $updateObj->mgroup = ! empty($cdarray[2]) ? $cdarray[2] : trim(preg_replace('/\[.*/' . s, '', strval($cdarray[1])));
        // $result = $db->updateObject('#__multicache_itemscache', $updateObj, 'id');
    }

    protected function insertParamsdb($c_key, $slab_size, $c_params)
    {

        global $wpdb;
        // $db = JFactory::getDbo();
        $nowtime = date('Y-m-d');
        $mstimestamp = microtime(true);
        $cdarray = explode('-', $c_key);
        $data = array(
            'item' => $c_key,
            'size' => $c_params[0],
            'expiration' => $c_params[1],
            'itemnumber' => $slab_size,
            'mstimestamp' => $mstimestamp,
            'nowtimestamp' => $nowtime,
            'sitehash' => $cdarray[0],
            'type' => trim(preg_replace('/\[.*/' . s, '', strval($cdarray[1]))),
            'mgroup' => ! empty($cdarray[2]) ? $cdarray[2] : trim(preg_replace('/\[.*/' . s, '', strval($cdarray[1])))
        );
        $table = 'wp_multicache_itemscache';
        $format = array(
            '%s',
            '%d',
            '%s',
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s'
        );
        $wpdb->insert($table, $data, $format);
    
        /*
         * $insertObj = new stdClass();
         * $insertObj->item = $c_key;
         * $insertObj->size = $c_params[0];
         * $insertObj->expiration = $c_params[1];
         * $insertObj->itemnumber = $slab_size;
         * $insertObj->mstimestamp = $mstimestamp;
         * $insertObj->nowtimestamp = $nowtime;
         * $insertObj->sitehash = $cdarray[0];
         */
        // $insertObj->type = trim(preg_replace('/\[.*/' . s, '', strval($cdarray[1])));
        // $insertObj->mgroup = ! empty($cdarray[2]) ? $cdarray[2] : trim(preg_replace('/\[.*/' . s, '', strval($cdarray[1])));
        // $result = $db->insertObject('#__multicache_itemscache', $insertObj);
    }

    protected function getstatslabs()
    {

        global $wpdb;
        $query = "SELECT itemnumber FROM wp_multicache_items WHERE 1";
        /*
         * $db = JFactory::getDbo();
         * $query = $db->getQuery('true');
         * $query->select($db->quoteName('itemnumber'));
         * $query->from($db->quoteName('wp_multicache_items'));
         * $query->where($db->quote('1'));
         * $db->setQuery($query);
         * Return $db->loadColumn();
         */
        Return $wpdb->get_col($query);
    
    }

    protected function startmemcachedinstance()
    {

       // $config = MulticacheFactory::getConfig();
    	$config = MulticacheFactory::getBlogConfig();
        
        if (! (class_exists('Memcached') && extension_loaded('memcached')))
        {
            $errormessage = __('memcached not loaded');
            $this->loaderrorlogger($errormessage);
            Return False;
        }
        if (self::$_dbadmin === null)
        :
            $server = array();
            $server['host'] = false !== $config ? $config->getC('multicache_server_host', 'localhost') : 'localhost';
            $server['port'] = false !== $config ? $config->getC('multicache_server_port', 11211) : 11211;
            self::$_dbadmin = new Memcached();
            endif;
        $memcachedtest = self::$_dbadmin->addServer($server['host'], $server['port']);
        if (! $memcachedtest)
        {
            
            $errormessage = __('Memcached test failed . memcached not present');
            //throw new RuntimeException('MULTICACHE_STAT_MEMCACHED_SERVER_CONNECT_FAILED', 404);
            $this->loaderrorlogger($errormessage);
            Return False;
        }
        Return $memcachedtest;
    
    }

    protected function startmemcacheinstance()
    {

        //$config = MulticacheFactory::getConfig();
    	$config = MulticacheFactory::getBlogConfig();
        
        if (! (class_exists('Memcache') && extension_loaded('memcache')))
        {
            
            $errormessage = __('Memcache not loaded');
            $this->loaderrorlogger($errormessage);
            Return False;
        }
        if (self::$_dbmain === null)
        :
            $server = array();
            $server['host'] = false !== $config ? $config->getC('multicache_server_host', 'localhost') : 'localhost';
            $server['port'] = false !== $config ? $config->getC('multicache_server_port', 11211) : 11211;
            self::$_dbmain = new Memcache();
        

        endif;
        $memcachetest = self::$_dbmain->addServer($server['host'], $server['port']);
        if (! $memcachetest)
        {
            $errormessage = __('Memcache test failed(stat). memcache not present');
           // throw new RuntimeException('MULTICACHE_STAT_MEMCACHE_SERVER_CONNECT_FAILED', 404);
            $this->loaderrorlogger($errormessage);
            Return False;
        }
        Return $memcachetest;
    
    }

    protected function endmemcachedinstance()
    {

        if (self::$_dbadmin != null)
        :
            self::$_dbadmin->quit();
            self::$_dbadmin = null;
        





















                    endif;
    
    }

    protected function endmemcacheinstance()
    {

        if (self::$_dbmain != null)
        :
            self::$_dbmain->close();
            self::$_dbmain = null;
        




















        endif;
    
    }

    protected function cleardb($table)
    {

        global $wpdb;
        $q = 'TRUNCATE TABLE ' . $table;
        $wpdb->query($q);
    
    }

    protected function storetodb($mem_keys, $table)
    {

        global $wpdb;
        // $db = JFactory::getDbo();
        // $query = $db->getQuery(true);
        // $insertObj = new stdClass();
        $format = array(
            '%s'
        );
        foreach ($mem_keys as $mem_key)
        {
            $data = array(
                'item' => $mem_key
            );
            $wpdb->insert($table, $data, $format);
            // $insertObj->item = $mem_key;
            // $result = $db->insertObject($table, $insertObj);
        }
    
    }

    protected function storetodbItems($items, $table)
    {

        global $wpdb;
        // $db = JFactory::getDbo();
        if (empty($items))
        {
            Return false;
        }
        // $insertObj = new stdClass();
        $format = array(
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d'
        );
        foreach ($items[items] as $key => $item)
        {
            $data = array(
                'itemnumber' => $key,
                'numberofelements' => $item['number'],
                'age' => $item['age'],
                'evicted' => $item['evicted'],
                'evicted_nonzero' => $item['evicted_nonzero'],
                'evicted_time' => $item['evicted_time'],
                'outofmemory' => $item['outofmemory'],
                'tailrepairs' => $item['tailrepairs'],
                'reclaimed' => $item['reclaimed'],
                'expired_unfetched' => $item['expired_unfetched'],
                'evicted_unfetched' => $item['evicted_unfetched']
            );
            $wpdb->insert($table, $data, $format);
            /*
             * $insertObj->itemnumber = $key;
             * $insertObj->numberofelements = $item['number'];
             * $insertObj->age = $item['age'];
             * $insertObj->evicted = $item['evicted'];
             * $insertObj->evicted_nonzero = $item['evicted_nonzero'];
             * $insertObj->evicted_time = $item['evicted_time'];
             * $insertObj->outofmemory = $item['outofmemory'];
             * $insertObj->tailrepairs = $item['tailrepairs'];
             * $insertObj->reclaimed = $item['reclaimed'];
             * $insertObj->expired_unfetched = $item['expired_unfetched'];
             * $insertObj->evicted_unfetched = $item['evicted_unfetched'];
             * $result = $db->insertObject($table, $insertObj);
             */
        }
    
    }

    protected function storetodbItemsstats($items, $table)
    {

        if (empty($items))
        {
            Return false;
        }
        global $wpdb;
        // $db = JFactory::getDbo();
        /*
         * $insertObj = new stdClass();
         * $insertObj->pid = $items['pid'];
         * $insertObj->uptime = $items['uptime'];
         * $insertObj->time = $items['time'];
         * $insertObj->version = $items['version'];
         * $insertObj->libevent = $items['libevent'];
         * $insertObj->pointer_size = $items['pointer_size'];
         * $insertObj->rusage_user = $items['rusage_user'];
         * $insertObj->rusage_system = $items['rusage_system'];
         * $insertObj->curr_connections = $items['curr_connections'];
         * $insertObj->total_connections = $items['total_connections'];
         * $insertObj->connection_structures = $items['connection_structures'];
         * $insertObj->reserved_fds = $items['reserved_fds'];
         * $insertObj->cmd_get = $items['cmd_get'];
         * $insertObj->cmd_set = $items['cmd_set'];
         * $insertObj->cmd_flush = $items['cmd_flush'];
         * $insertObj->cmd_touch = $items['cmd_touch'];
         * $insertObj->get_hits = $items['get_hits'];
         * $insertObj->get_misses = $items['get_misses'];
         * $insertObj->delete_misses = $items['delete_misses'];
         * $insertObj->delete_hits = $items['delete_hits'];
         * $insertObj->incr_misses = $items['incr_misses'];
         * $insertObj->incr_hits = $items['incr_hits'];
         * $insertObj->decr_misses = $items['decr_misses'];
         * $insertObj->decr_hits = $items['decr_hits'];
         * $insertObj->cas_misses = $items['cas_misses'];
         * $insertObj->cas_hits = $items['cas_hits'];
         * $insertObj->cas_badval = $items['cas_badval'];
         * $insertObj->touch_hits = $items['touch_hits'];
         * $insertObj->touch_misses = $items['touch_misses'];
         * $insertObj->auth_cmds = $items['auth_cmds'];
         * $insertObj->auth_errors = $items['auth_errors'];
         * $insertObj->bytes_read = $items['bytes_read'];
         * $insertObj->bytes_written = $items['bytes_written'];
         * $insertObj->limit_maxbytes = $items['limit_maxbytes'];
         * $insertObj->accepting_conns = $items['accepting_conns'];
         * $insertObj->listen_disabled_num = $items['listen_disabled_num'];
         * $insertObj->threads = $items['threads'];
         * $insertObj->conn_yields = $items['conn_yields'];
         * $insertObj->hash_power_level = $items['hash_power_level'];
         * $insertObj->hash_bytes = $items['hash_bytes'];
         * $insertObj->hash_is_expanding = $items['hash_is_expanding'];
         * $insertObj->bytes = $items['bytes'];
         * $insertObj->curr_items = $items['curr_items'];
         * $insertObj->total_items = $items['total_items'];
         * $insertObj->expired_unfetched = $items['expired_unfetched'];
         * $insertObj->evicted_unfetched = $items['evicted_unfetched'];
         * $insertObj->evictions = $items['evictions'];
         * $insertObj->reclaimed = $items['reclaimed'];
         */
        $format = array(
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%f',
            '%f',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d'
        );
        $data = array(
            'pid' => $items['pid'],
            'uptime' => $items['uptime'],
            'time' => $items['time'],
            'version' => $items['version'],
            'libevent' => $items['libevent'],
            'pointer_size' => $items['pointer_size'],
            'rusage_user' => $items['rusage_user'],
            'rusage_system' => $items['rusage_system'],
            'curr_connections' => $items['curr_connections'],
            'total_connections' => $items['total_connections'],
            'connection_structures' => $items['connection_structures'],
            'reserved_fds' => $items['reserved_fds'],
            'cmd_get' => $items['cmd_get'],
            'cmd_set' => $items['cmd_set'],
            'cmd_flush' => $items['cmd_flush'],
            'cmd_touch' => $items['cmd_touch'],
            'get_hits' => $items['get_hits'],
            'get_misses' => $items['get_misses'],
            'delete_misses' => $items['delete_misses'],
            'delete_hits' => $items['delete_hits'],
            'incr_misses' => $items['incr_misses'],
            'incr_hits' => $items['incr_hits'],
            'decr_misses' => $items['decr_misses'],
            'decr_hits' => $items['decr_hits'],
            'cas_misses' => $items['cas_misses'],
            'cas_hits' => $items['cas_hits'],
            'cas_badval' => $items['cas_badval'],
            'touch_hits' => $items['touch_hits'],
            'touch_misses' => $items['touch_misses'],
            'auth_cmds' => $items['auth_cmds'],
            'auth_errors' => $items['auth_errors'],
            'bytes_read' => $items['bytes_read'],
            'bytes_written' => $items['bytes_written'],
            'limit_maxbytes' => $items['limit_maxbytes'],
            'accepting_conns' => $items['accepting_conns'],
            'listen_disabled_num' => $items['listen_disabled_num'],
            'threads' => $items['threads'],
            'conn_yields' => $items['conn_yields'],
            'hash_power_level' => $items['hash_power_level'],
            'hash_bytes' => $items['hash_bytes'],
            'hash_is_expanding' => $items['hash_is_expanding'],
            'bytes' => $items['bytes'],
            'curr_items' => $items['curr_items'],
            'total_items' => $items['total_items'],
            'expired_unfetched' => $items['expired_unfetched'],
            'evicted_unfetched' => $items['evicted_unfetched'],
            'evictions' => $items['evictions'],
            'reclaimed' => $items['reclaimed']
        );
        
        $wpdb->insert($table, $data, $format);
    
    }

    protected function storetodbItemsslabs($items, $table)
    {

        global $wpdb;
        // $db = JFactory::getDbo();
        $format = array(
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s'
        );
        // $insertObj = new stdClass();
        foreach ($items as $key => $item)
        {
            /*
             * $insertObj->slab_id = $key;
             *
             * $insertObj->chunk_size = isset($item["chunk_size"]) ? $item["chunk_size"] : null;
             * $insertObj->chunks_per_page = isset($item["chunks_per_page"]) ? $item["chunks_per_page"] : null;
             * $insertObj->total_pages = isset($item["total_pages"]) ? $item["total_pages"] : null;
             * $insertObj->total_chunks = isset($item["total_chunks"]) ? $item["total_chunks"] : null;
             * $insertObj->used_chunks = isset($item["used_chunks"]) ? $item["used_chunks"] : null;
             * $insertObj->free_chunks = isset($item["free_chunks"]) ? $item["free_chunks"] : null;
             * $insertObj->free_chunks_end = isset($item["free_chunks_end"]) ? $item["free_chunks_end"] : null;
             * $insertObj->mem_requested = isset($item["mem_requested"]) ? $item["mem_requested"] : null;
             * $insertObj->get_hits = isset($item["get_hits"]) ? $item["get_hits"] : null;
             * $insertObj->cmd_set = isset($item["cmd_set"]) ? $item["cmd_set"] : null;
             * $insertObj->delete_hits = isset($item["delete_hits"]) ? $item["delete_hits"] : null;
             * $insertObj->incr_hits = isset($item["incr_hits"]) ? $item["incr_hits"] : null;
             * $insertObj->decr_hits = isset($item["decr_hits"]) ? $item["decr_hits"] : null;
             * $insertObj->cas_hits = isset($item["cas_hits"]) ? $item["cas_hits"] : null;
             * $insertObj->cas_badval = isset($item["cas_badval"]) ? $item["cas_badval"] : null;
             * $insertObj->touch_hits = isset($item["touch_hits"]) ? $item["touch_hits"] : null;
             * $result = $db->insertObject($table, $insertObj);
             */
            
            $data = array(
                'chunk_size' => isset($item["chunk_size"]) ? $item["chunk_size"] : null,
                'chunks_per_page' => isset($item["chunks_per_page"]) ? $item["chunks_per_page"] : null,
                'total_pages' => isset($item["total_pages"]) ? $item["total_pages"] : null,
                'total_chunks' => isset($item["total_chunks"]) ? $item["total_chunks"] : null,
                'used_chunks' => isset($item["used_chunks"]) ? $item["used_chunks"] : null,
                'free_chunks' => isset($item["free_chunks"]) ? $item["free_chunks"] : null,
                'free_chunks_end' => isset($item["free_chunks_end"]) ? $item["free_chunks_end"] : null,
                'mem_requested' => isset($item["mem_requested"]) ? $item["mem_requested"] : null,
                'get_hits' => isset($item["get_hits"]) ? $item["get_hits"] : null,
                'cmd_set' => isset($item["cmd_set"]) ? $item["cmd_set"] : null,
                'delete_hits' => isset($item["delete_hits"]) ? $item["delete_hits"] : null,
                'incr_hits' => isset($item["incr_hits"]) ? $item["incr_hits"] : null,
                'decr_hits' => isset($item["decr_hits"]) ? $item["decr_hits"] : null,
                'cas_hits' => isset($item["cas_hits"]) ? $item["cas_hits"] : null,
                'cas_badval' => isset($item["cas_badval"]) ? $item["cas_badval"] : null,
                'touch_hits' => isset($item["touch_hits"]) ? $item["touch_hits"] : null
            );
            $wpdb->insert($table, $data, $format);
        }
    
    }

    protected function loaderrorlogger($emessage = null)
    {
    	MulticacheHelper::log_error(__($emessage , 'multicache-plugin') , 'stat-error');
/*
        if (! defined('LOGGER_READY'))
        {
            JLog::addLogger(array(
                'text_file' => 'multicache.stat.errors.php'
            ), JLog::ALL, array(
                'memcached'
            ));
            define('LOGGER_READY', TRUE);
        }
        if (! empty($emessage))
        {
            JLog::add(JText::_($emessage), JLog::NOTICE);
        }
        */
    
    }

    protected function getSizeObject()
    {

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName('mgroup'));
        $query->select(' AVG(' . $db->quoteName('size') . ') As sz ');
        $query->from($db->quoteName('#__multicache_itemscache'));
        $query->where($db->quoteName('mgroup') . '  !=  ' . $db->quote(''));
        $query->group($db->quoteName('mgroup'));
        $db->setQuery($query);
        Return $db->loadObjectlist();
    
    }
    // end methods
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.state');
        
        return parent::getStoreId($id);
    
    }

    protected function getListQuery()
    {

        $sim_flag = $this->getState('filter.simflag');
        $complete_flag = $this->getState('filter.completeflag');
        $tolerance_flag = $this->getState('filter.toleranceflag');
        
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->Select($this->getState('list.select', 'a.*'));
        $query->from($db->quoteName('#__multicache_test_results') . ' As a');
        if ($sim_flag)
        {
            $sim_var = ($sim_flag == 'simulation') ? 'simulation' : 'off';
            $query->where($db->quoteName('simulation') . ' = ' . $db->quote($sim_var));
        }
        if ($complete_flag)
        {
            $complete_var = ($complete_flag == 'show_only_complete') ? 'complete' : NULL;
            if ($complete_var)
            {
                $query->where($db->quoteName('status') . ' = ' . $db->quote($complete_var));
            }
            else
            {
                $query->where($db->quoteName('status') . ' != ' . $db->quote('complete'));
            }
        }
        
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        $query->order($db->escape($orderCol . '  ' . $orderDirn));
        
        return $query;
    
    }

    protected function pluckAllKeys()
    {

        //$config = MulticacheFactory::getConfig();
    	$config = MulticacheFactory::getBlogConfig();
        $this->_hash =false !== $config? md5($config->getC('secret')) : '';
        global $wpdb;
        $query = "SELECT item FROM wp_multicache_itemscache WHERE sitehash LIKE '$this->_hash'";
        $tmpobj = $wpdb->get_results($query, OBJECT);
        // $db = JFactory::getDbo();
        // $query = $db->getQuery(true);
        // $query->select($db->quoteName('item'));
        // $query->from($db->quoteName('#__multicache_itemscache'));
        // $query->where($db->quoteName('sitehash') . ' LIKE ' . $db->quote($this->_hash));
        // $db->setQuery($query);
        // $tmpobj = $db->loadObjectlist();
        $key_array = array();
        foreach ($tmpobj as $obj)
        {
            $key_array[] = $obj->item;
        }
        
        Return $key_array;
    
    }

}