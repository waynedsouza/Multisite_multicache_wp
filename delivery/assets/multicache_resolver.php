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
define('_MULTICACHEWP_EXEC', 1);
if(file_exists(dirname(dirname(dirname(__FILE__)))).'/libs/multicache_config.php')
{
	require_once dirname(dirname(dirname(__FILE__))).'/libs/multicache_config.php';
	require_once dirname(dirname(dirname(__FILE__))).'/libs/multicache_factory.php';
	require_once dirname(dirname(dirname(__FILE__))).'/libs/multicache_uri.php';
	require_once dirname(dirname(dirname(__FILE__))).'/libs/multicache.php';
}
else
{
	die('Multicache config not initialised');
}

$config = new MulticacheConfig();
/*
if(file_exists($config->absolute_path.'wp-load.php'))
{
	require_once $config->absolute_path.'wp-load.php';
}
else {
	die('Wp Not loading');
}
*/


$app = MulticacheFactory::getApplication();
//$config = JFactory::getConfig();
// test this on php 5.3
$sub_precache_factor =(int) (isset($config->sub_precache_factor) && $config->sub_precache_factor !== '') ? $config->sub_precache_factor : ( (isset($config->precache_factor) && $config->precache_factor !== '') ? $config->precache_factor :4);
$bodydata = null;
$v = $u = MulticacheURI::getInstance();
$u->setVar('cbypass', 'true');
$a_uri = $u->toString();
if(empty($config->caching) || !function_exists('curl_version'))
{
	header("Location: $a_uri" , true , 307);
	exit(0);
}
//set the IF NONE MATCH HERE
$etag_stored = md5( $v->toString(array('scheme','host','path')) );
$etag_stored_gzip = $etag_stored . '-gzip';
if (isset($_SERVER['HTTP_IF_NONE_MATCH']))
{

	$etag = stripslashes($_SERVER['HTTP_IF_NONE_MATCH']);

	if ($etag == $etag_stored_gzip || $etag == $etag_stored)
	{
		header('HTTP/1.x 304 Not Modified', true);
		exit(0);
	}
}

//endifnonmatch
$cache = MulticacheFactory::getCache('multicache_assets', '');
if(!isset($cache->options['cachebase']) )
{
	//multicache not set up 
	header("Location: $a_uri" , true , 307);
	exit(0);
}
//WP_PATH_MULTICACHECACHE
/*$f = MulticacheHelper::validate_numtext($_REQUEST['f']);
$g = MulticacheHelper::validate_numtext($_REQUEST['g']);
$h = MulticacheHelper::validate_numtext($_REQUEST['h']);*/
$f = $_REQUEST['f'];
$g = $_REQUEST['g'];
$h = $_REQUEST['h'];
if(!($f === 'css' || $f === 'js'))
{
	exit(0);
}
$id = $f . '.' . $g .'.' .$h;
$key = $config->secret . '_multicache_assets_' . $id;
$data = $cache->cache->get($key);

if($data !== false)
{
	$data = unserialize($data);
}

// cache Primer
if (empty($data))
{
    //$file_class = new JHTTP();
    $args = array('timeout'     => 30);
    //$file = wp_remote_get( $a_uri, $args ); //$file_class->get($a_uri);
    $file =  mresolver_get_page($a_uri );
    
    
    
    if ($file['http_code'] === 200)
    {
        $multicache_asset_obj = new stdClass();
        
        $multicache_asset_obj->date = gmdate('D, d M Y H:i:s \G\M\T', time());//$file['headers']['date'];
        $multicache_asset_obj->ETag = $etag_stored;//$file['headers']["etag"];
        $multicache_asset_obj->Last_Modified = gmdate('D, d M Y H:i:s \G\M\T', time());//$file['headers']["last-modified"];
        $multicache_asset_obj->Vary = 'Accept-Encoding,User-Agent';//$file['headers']["vary"];
        $multicache_asset_obj->Cache_Control = 'max-age=604800, public';//$file['headers']["cache-control"];
        $multicache_asset_obj->Expires = gmdate('D, d M Y H:i:s \G\M\T', time() + 86400) ; //$file['headers']["expires"];
        $multicache_asset_obj->Content_Type = $file["content_type"];
        $multicache_asset_obj->body = $file['content'];
        $multicache_asset_obj->body_gzip = gzencode($file['content'], $sub_precache_factor, FORCE_GZIP);
        $multicache_asset_obj->Content_Length = strlen($file['content']);
        $multicache_asset_obj->GContent_Length = strlen($multicache_asset_obj->body_gzip);
        $multicache_asset_obj->sub_precache = $sub_precache_factor;
        
         
        if (! empty($multicache_asset_obj->body))
        {
        	
            $cache->cache->store(serialize($multicache_asset_obj), $key);
        	$data = $multicache_asset_obj;
        }
    }
}

// $data should not be empty by now
// process
// 1. HTTP_IF_NONE_MATCH_
// 2. Header etag
// 3. get clients acceptable encodings
// 4. If $compress & not previously compressed
// 5. get supported encodings
// 6. if support gzip and isset body gzip set
// 7. set the content encoding header to gzip

if (! empty($data))
{
	/*
	$etag_stored = $data->ETag;
	$etag_stored_gzip = $etag_stored . '-gzip';
    if (isset($_SERVER['HTTP_IF_NONE_MATCH']))
    {
              
        $etag = stripslashes($_SERVER['HTTP_IF_NONE_MATCH']);
        
        if ($etag == $etag_stored_gzip || $etag == $etag_stored)
        {
            header('HTTP/1.x 304 Not Modified', true);
           exit(0);
        }
    }
    */
        $app->setHeader('ETag', $etag_stored, true);
        $compress = $app->get('gzip');
        $clientencodings = $app->encodings;//array_map('trim', explode(',', $app->client->acceptEncoding));
        
        if (! empty($clientencodings) && $compress && (bool) (extension_loaded('zlib') || ! ini_get('zlib.output_compression')) && ini_get('output_handler') != 'ob_gzhandler' )
        {
            
            $supported = array(
                'x-gzip' => 'gz',
                'gzip' => 'gz',
                'deflate' => 'deflate'
            );
            $encodings = array_intersect($clientencodings, array_keys($supported));
            
            if (isset($data->body_gzip) && in_array('gzip', $encodings))
            {
                // echo "from zipper";
                $bodydata = $data->body_gzip;
                $app->setHeader('Content-Type', $data->Content_Type);
                $app->setHeader('Content-Encoding', 'gzip');
                $app->setHeader('Content-Length', $data->GContent_Length);
            }
            elseif (! empty($clientencodings) && ! headers_sent()  && (connection_status() === CONNECTION_NORMAL))
            {
            	
                foreach ($clientencodings as $encoding)
                {
                    
                    // a $logical gate check here appears redundant
                    
                    $gzdata = gzencode($data->body, $sub_precache_factor, ($supported[$encoding] == 'gz') ? FORCE_GZIP : FORCE_DEFLATE);
                    
                    if ($gzdata === false)
                    {
                        continue;
                    }
                    $app->setHeader('Content-Type', $data->Content_Type);
                    $app->setHeader('Content-Encoding', $encoding);
                    if(isset($data->sub_precache) && $data->sub_precache === $sub_precache_factor)
                    {
                    	$app->setHeader('Content-Length', $data->GContent_Length);
                    }
                    // $app->setHeader('X-Content-Encoded-By', 'Joomla');
                    $bodydata = $gzdata;
                                        
                    break;
                }
            }
        }
    
    // we do not have browser cache details here
    
    if (isset($options) && $options['browsercache'] === 0)
    {
        $app->setHeader('Cache-Control', 'no-cache', false);
        $app->setHeader('Pragma', 'no-cache');
    }
    // not yet handled the case of no compress
    
    if (empty($bodydata))
    {
        $bodydata = $data->body;
        $app->setHeader('Content-Type', $data->Content_Type);
        if(isset($data->sub_precache) && $data->sub_precache === $sub_precache_factor)
        {
        	$app->setHeader('Content-Length', $data->Content_Length);
        }
    }
    $app->sendHeaders();
    echo $bodydata;
    // test1
    exit(0);
}
else
{
	header("Location: $a_uri" , true , 307);
	exit(0);
    //$app->redirect($a_uri);
}

function mresolver_get_page( $url)
{
	if ( !function_exists('curl_version') )
	{
		Return false;
	}
		$user_agent = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';

		$options = array(

				CURLOPT_CUSTOMREQUEST => "GET", // set request type post or get
				CURLOPT_POST => false, // set to GET
				CURLOPT_USERAGENT => $user_agent, // set user agent
				CURLOPT_COOKIEFILE => "cookie.txt", // set cookie file
				CURLOPT_COOKIEJAR => "cookie.txt", // set cookie jar
				CURLOPT_RETURNTRANSFER => true, // return web page
				CURLOPT_HEADER => false, // don't return headers
				CURLOPT_FOLLOWLOCATION => true, // follow redirects
				CURLOPT_ENCODING => "gzip,deflate", // handle only gzip & defalte encodings Joomla3
				CURLOPT_AUTOREFERER => true, // set referer on redirect
				CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
				CURLOPT_TIMEOUT => 120, // timeout on response
				CURLOPT_MAXREDIRS => 10
		); // stop after 10 redirects

		$ch = curl_init($url);
		curl_setopt_array($ch, $options);
		$content = curl_exec($ch);
		$err = curl_errno($ch);
		$errmsg = curl_error($ch);
		$header = curl_getinfo($ch);
		curl_close($ch);

		$header['errno'] = $err;
		$header['errmsg'] = $errmsg;
		$header['content'] = $content;
		return $header;
	}
	



