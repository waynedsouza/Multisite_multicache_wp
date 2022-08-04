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
require_once dirname(__FILE__) . '/multicache_buffers.php';

class MulticachePageController extends MulticacheController
{

    protected $_id;

    protected $_group;

    protected $_locktest = null;

    protected $_force_locking_off = false;

    protected $_precache_factor = null;

    protected $_force_precache_off = null;

    protected static $_cloop = null;

    public $options;


    public function get($id = false, $subgroup = null, $user = 0, $group = 'page', $check_buffers = true)
    {

        if (empty($id))
        {
            Return false;
        }
        /*
         * force locking off :cleaner variant would be to set in JCache;
         * however JCache cannot be overwritten unless JLoader introduced in index before JApplicationCMS which first calls
         * JFactory::getCache() through JComponentHelper; If you're reading this you can set the construct of Jcache to locking => false.
         * Some of the documentation to support locking off can be found on the memcached forums in particular that memcached is not atomic
         * in its state and can never assure you of a lock. The Joomla locking variant tends to malfunction in case of high load on
         * high page access sites. Particularly the wait threads for lock to unlock when there is no lock in the first place.
         */
        $config = MulticacheFactory::getBlogConfig();       
        $this->_force_locking_off = $config->getC('force_locking_off', true);
        $cachehit = preg_replace('~[^a-z]~', '', $_REQUEST["cachehit"]);
        if (! empty($this->options[locking]) && $this->_force_locking_off)
        {
            $this->options[locking] = false;
        }
        $this->_precache_factor = false !== $config ?  $config->getC('precache_factor', 6) : 6;
        $this->_precache_factor = empty($this->_precache_factor ) && strcmp($this->_precache_factor , '')===0 ? 2:(int)$this->_precache_factor;
        
        
        $this->_force_precache_off = false !== $config? $config->getC('multicacheprecacheswitch', null) : null;
        
        // If the etag matches the page id ... set a no change header and exit : utilize browser cache
        // June 16 2015 added user get guest so that we get a fresh page on login and dont stay lgged out
        // we're not willing to return false as id and group are set at the end
        if (! headers_sent() && isset($_SERVER['HTTP_IF_NONE_MATCH']) && ! $user)
        {
            
            $etag = stripslashes($_SERVER['HTTP_IF_NONE_MATCH']);
            if ($etag == $id)
            {
                $browserCache = isset($this->options['browsercache']) ? $this->options['browsercache'] : false;
                if ($browserCache)
                {
                    $this->_noChange();
                }
            }
        }
        
        // We got a cache hit... set the etag header and echo the page data
        $data = $this->cache->get($id, $group, $user, $subgroup);
        
        if ($this->options[storage] == 'fastcache' && ! $this->options[locking])
        {
        }
        else
        {
            
            $this->_locktest = new stdClass();
            $this->_locktest->locked = null;
            $this->_locktest->locklooped = null;
            
            if ($data === false)
            {
                
                $this->_locktest = $this->cache->lock($id, $group);
                if ($this->_locktest->locked == true && $this->_locktest->locklooped == true)
                {
                    $data = $this->cache->get($id, $group, $user, $subgroup);
                }
            }
        }
        if ($data !== false)
        {
            $data = unserialize(trim($data));
            if(isset($data['group']) && $data['group'] ==='feed')
            {

            	$return = $this->getFeed($data);
            	Return $return;
            }
            self::$_cloop = array(
                'id' => $id,
                'group' => $group,
                'locked' => $this->_locktest->locked,
                'cache_obj' => $this->cache
            );
            $data = MulticacheBuffers::getBuffers($data, array(
                'precache_factor' => $this->_precache_factor,
                'force_precache_off' => $this->_force_precache_off,
                'etag' => $id,
            	'user' =>$user,
            	'obj' => $this
            ));
            $oetag = $user ==0? $id: $id.'-'.substr(md5('user'-$user),5,4);
            $this->_setEtag($oetag);
            if ($this->_locktest->locked == true)
            {
                $this->cache->unlock($id, $group);
            }
            return $data;
        }
        elseif (WP_DEBUG_LOG && $cachehit === 'true')
        {
            $sim_id = preg_replace('~[^a-zA-Z0-9]~', '', $_REQUEST['multicachesimulation']);
            error_log('Multicache: Page not in cache' . $sim_id . ' ' . MulticacheUri::getInstance()->toString());
        }
        
        // Set id and group placeholders
        $this->_id = $id;
        $this->_group = $group;
        
        return false;
    
    }

    public function store($data, $id, $subgroup = null, $user = 0, $group = 'page', $check_buffers = true)
    {
    	$conf = MulticacheFactory::getBlogConfig();
        // Get page data from the application object
        if (empty($data) || empty($id))
        {
            Return false;
        }
        $precache_factor = isset($this->_precache_factor) ? $this->_precache_factor: false !== $conf ? $conf->getC('precache_factor', 6): 6;
        $precache_factor = empty($precache_factor) && strcmp($precache_factor,'') === 0? 2 : (int)$precache_factor;
        
        //start feed
        if(isset($subgroup) && $subgroup =='feed')
        {
        	$return = $this->storeFeed($data , $id, null ,  $user = 0);
        	Return $return;
        }
        //end feed
        
        if ($data)
        {
            if ($check_buffers)
            {
                $data = MulticacheBuffers::setBuffers($data, array(
                    'makegzip' => 1,
                    'precache_factor' => $precache_factor,
                		'sub_group'=> $subgroup, 
                		'user' => $user,
                		'id' => $id
                ));
            }
            // validate this locking method and see if we can option to not lock by default
            if (isset($this->locking) && isset($this->_locktest->locked) && $this->locking && $this->_locktest->locked == false)
            {
                $this->_locktest = $this->cache->lock($id, $group, $user, $subgroup);
            }
            
            $success = $this->cache->store(serialize($data), $id, $group, $user, $subgroup);
            
            if (isset($this->locking) && isset($this->_locktest->locked) && $this->locking && $this->_locktest->locked == true)
            {
                $this->cache->unlock($id, $group);
            }
            
            return $success;
        }
        return false;
    
    }

    protected function _noChange()
    {
        
        // test how 304 works in the hooks scenario; issuing exit or do we structure a return?
        header('HTTP/1.x 304 Not Modified', true);
        exit(0);
    
    }

    public function closeLoop()
    {

        if (! isset(self::$_cloop))
        {
            Return false;
        }
        
        if (self::$_cloop['locked'] == true)
        {
            self::$_cloop['cache_obj']->unlock(self::$_cloop['id'], self::$_cloop['group']);
        }
    
    }
public function markEndTime($mark = 'aftercache')
{
	
	 if (defined('MULTICACHEPROFILERPATH') && file_exists(MULTICACHEPROFILERPATH)
	 && defined('MULTICACHE_STARTTIME_')
	 && ((($endtime = microtime(true) - MULTICACHE_STARTTIME_) >= 0.01)
	 /*|| $_SERVER['REQUEST_METHOD'] != 'GET'*/))
	 {
	 if (! class_exists('MulticacheFactory'))
	 {
	 include_once MULTICACHEPROFILERPATH;
	 }
	 
	 $error_file = 'multicache_cacheplugin_optimization.log';
	 $error_message = "\n" . $date . ' ' . ' PAGE TOOK ' . $endtime . ' to render ';
	 MULTICACHEPROFILERDEBUG ? $GLOBALS['multicache_profiler']->mark($mark):null;
	 $extra_message = MULTICACHEPROFILERDEBUG? $GLOBALS['multicache_profiler']: '';
	
	 MulticacheFactory::loadErrorLogger($error_message, $extra_message, '', $error_file);
	 }
	 /*multicache profiler Collect results end scheme*/
}
    protected function _setEtag($etag)
    {

        MulticacheFactory::getApplication()->setHeader('ETag', $etag, true);
    
    }
    protected function storeFeed($data, $id, $subgroup = null, $user = 0, $group = 'page', $check_buffers = true)
    {
    	/*were calling all groups page this is to make for an effective retreival 
    	 * as all types are known during storage but none is known during retreival.
    	 * This is by design to enable fastest retreival without querying objects or db
    	 */
    	if (empty($data) || empty($id))
    	{
    		Return false;
    	}
    	
    	$data = trim($data);
    	$char_set = get_option('blog_charset');
    	$xml_feed = $match = preg_match('~(<\/feed>|<\/urlset|<\?xml)~i', $content);
    	if($xml_feed)
    	{
    		if(preg_match('~xmlns=(\'|")http://www.w3.org/[0-9]{4}/Atom("|\')~',$data))
    		{
    			$content_type = 'atom';
    		}
    		else
    		{
    			$content_type = 'xml';
    		}
    	}
    	$match_rdf = preg_match('~(<\/rdf:RDF)~i', $content);
    	if($match_rdf)
    	{
    		$content_type = 'rdf';
    	}
    	$match_rss = preg_match('~(<\/rss)~i', $content);
    	if($match_rss)
    	{
    		$content_type ='rss';
    	}
    	 
    	//<rdf:RDF
    	//get content length
    	
    	$content = array();
    	$content['group'] = 'feed';//NB that this is an internal group
    	$content['content-length'] = strlen($data);
    	$content['char_set'] = $char_set;
    	$content['content-type'] = !empty($content_type)? $content_type: 'xml';
    	$content['multicache-meta']= 'id - '.$id.' group - '.$group.' user - '.$user. ' wp grouping - '.serialize(MulticacheEventViewer::$_group);
    	$content['body'] = $data;
    	$result = $this->cache->store(serialize($content) , $id , $group , $user );
    	Return $result;
    	 
    }
    
    protected function getFeed($content)
    {
    	if(empty($content))
    	{
    		Return false;
    	}
    	$types = array(
    			'rss'  => 'application/rss+xml',
    			'rss-http'  => 'text/xml',
    			'atom' => 'application/atom+xml',
    			'rdf'  => 'application/rdf+xml',
    			'xml' => 'text/xml'
    	);
    	
    	$app = MulticacheFactory::getApplication();
    	//get the charset
    	$charset = isset($content['char_set'])? $content['char_set'] : 'utf-8';
    	//set the content type header
    	$content_type = isset($content[content-type])? $content[content-type] :'xml';
    	$type_flag = isset($types[$content_type]) ? $types[$content_type] : 'text/xml';
    	if(!empty($type_flag) && !empty($charset))
    	{
    		$type_flag = $type_flag . '; charset="'. $charset.'"';
    	}
    	
    	//set the length
    	$c_length = isset($content['content-length'])? $content['content-length'] : strlen(trim($content['body']));
    	
    	$app->setHeader('Content-Type' , $type_flag);
    	$app->setHeader('Content-Length' , $c_length );
    	$app->setBody($content['body']);    	 
    	echo $app->toString(false);
    	exit(0);
    	return $content['body'];
    	
    	 
    	
    }

}