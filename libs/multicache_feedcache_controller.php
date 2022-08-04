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


class MulticacheFeedController extends MulticacheController
{

    protected $_id;

    protected $_group;

    protected $_locktest = null;

    protected $_force_locking_off = false;

    protected $_precache_factor = null;

    protected $_force_precache_off = null;

    protected static $_cloop = null;

    public $options;


    public function get($id = false, $subgroup = null, $user = 0, $group = 'feed', $check_buffers = true)
    {

        if (empty($id))
        {
            Return false;
        }
$blob = $this->cache->get($id , $group , $user);
if(empty($blob))
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
$content = unserialize($blob);
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


        return $content['body'];
    
    }

    public function store($data, $id, $subgroup = null, $user = 0, $group = 'feed', $check_buffers = true)
    {
    	// Get page data from the application object
    	if (empty($data) || empty($id))
    	{
    		Return false;
    	}
    	
    	$data = trim($data);
    	//get encoding
    	$char_set = get_option('blog_charset');
        //get content type
  	
    
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
    	$content['content-length'] = strlen($data);
    	$content['char_set'] = $char_set;
    	$content['content-type'] = !empty($content_type)? $content_type: 'xml';
    	$content['multicache-meta']= 'id - '.$id.' group - '.$group.' user - '.$user. ' wp grouping - '.MulticacheEventViewer::$_group;
    	$content['body'] = $data;
    	$result = $this->cache->store(serialize($content) , $id , $group , $user );
    	Return $result;
    	
       
        
       
    
    }

    protected function _noChange()
    {
        
        // test how 304 works in the hooks scenario; issuing exit or do we structure a return?
        header('HTTP/1.x 304 Not Modified', true);
        exit(0);
    
    }

  

  

}