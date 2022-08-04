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

class MulticacheBuffers extends Multicache
{

    public function __construct($options)
    {

        parent::__construct($options);
    
    }

    public static function getBuffers($data, $options = array())
    {

        $app = MulticacheFactory::getApplication();
        $app->allowCache(true);
        // $document = JFactory::getDocument();
        $body = null;
        // $excluded_options = $app->input->get('option', null);//unreliable
        $precache_off = $options["force_precache_off"];
        
        // Get the document head out of the cache.
        /*
         * if (isset($options['mergehead']) && $options['mergehead'] == 1 && isset($data['head']) && ! empty($data['head']))
         * {
         * $document->mergeHeadData($data['head']);
         * }
         * elseif (isset($data['head']) && method_exists($document, 'setHeadData'))
         * {
         * $document->setHeadData($data['head']);
         * }
         */
        
        // Get the document MIME encoding out of the cache
        /*
         * if (isset($data['mime_encoding']))
         * {
         * $document->setMimeEncoding($data['mime_encoding'], true);
         * }
         */
        
        // If the pathway buffer is set in the cache data, get it.
        /*
         * if (isset($data['pathway']) && is_array($data['pathway']))
         * {
         * // Push the pathway data into the pathway object.
         * $pathway = $app->getPathWay();
         * $pathway->setPathway($data['pathway']);
         * }
         */
        
        // If a module buffer is set in the cache data, get it.
        /*
         * if (isset($data['module']) && is_array($data['module']))
         * {
         * // Iterate through the module positions and push them into the document buffer.
         * foreach ($data['module'] as $name => $contents)
         * {
         * $document->setBuffer($contents, 'module', $name);
         * }
         * }
         */
        
        // Set cached headers.
        if (isset($data['headers']) && $data['headers'])
        {
            foreach ($data['headers'] as $header)
            {
                $app->setHeader($header['name'], $header['value']);
            }
        }
        // hack #1 - gzipbodyhack
        $client_encodings_string = $app->acceptEncoding;
        $common_checks = ! headers_sent() && $app->get('gzip' , true);
        $oetag = $options['user'] ==0? $options["etag"]: $options["etag"].'-'.substr(md5('user'-$user),5,4);
        if (! empty($data['body_gzip']) && stripos($client_encodings_string, 'gzip') !== false && $common_checks && ! isset($precache_off))
        {
            $app->setBody($data['body_gzip']); //
            $app->setHeader('Content-Encoding', 'gzip');
            $app->setHeader('ETag', $oetag, true);
           // $app->setHeader('Content-Length', $data['GContent_Length']);
            echo $app->toString(false);
            $options['obj']->closeLoop();
            $options['obj']->markEndTime('preGzipRender');
            $app->close();
        }
        // The following code searches for a token in the cached page and replaces it with the
        // proper token.
        elseif (isset($data['body']))
        {
            
            // $token = JSession::getFormToken();
            // $search = '#<input type="hidden" name="[0-9a-f]{32}" value="1" />#';
            // $replacement = '<input type="hidden" name="' . $token . '" value="1" />';
            
            // $data['body'] = preg_replace($search, $replacement, $data['body']);
            $body = $data['body'];
            $client_encodings = array_map('trim', explode(',', $client_encodings_string));
            $supported = array(
                'x-gzip' => 'gz',
                'gzip' => 'gz',
                'deflate' => 'deflate'
            );
            $encodings = array_intersect($client_encodings, array_keys($supported));
            if (! empty($encodings) && $common_checks && (connection_status() === CONNECTION_NORMAL))
            {
                foreach ($encodings as $encoding)
                {
                    $gzdata = gzencode($data['body'], $options["precache_factor"], ($supported[$encoding] == 'gz') ? FORCE_GZIP : FORCE_DEFLATE);
                    if ($gzdata === false)
                    {
                        continue;
                    }
                    
                    $app->setHeader('Content-Encoding', $encoding);
                    $app->setBody($gzdata);
                    $app->setHeader('ETag', $oetag, true);
                    /*
                    if(isset($data['precache_factor']) && $data['precache_factor'] === $options["precache_factor"])
                    {
                    	$app->setHeader('Content-Length', $data['GContent_Length']);
                    }
                    */
                    echo $app->toString(false);
                    $options['obj']->closeLoop();
                    $options['obj']->markEndTime('gzipjustpreparedRender');
                    $app->close();
                }
            }
            
            /*
             * aligning the non zipped content length
            if(isset($data['Content_Length']))
            {
            	$app->setHeader('Content-Length', $data['Content_Length']);
            }
            */
            
            
            
            return $body;
        }
        
        // Get the document body out of the cache.
        
        return false;
    
    }

    public static function setBuffers($data, $options = array())
    {

        $cached['body'] = $data;
        $cached['multicache_meta'] = $options;
        if (isset($options['makegzip']))
        {
            
            if (isset($options['precache_factor']))
            {
                $precache_factor = $options['precache_factor'];
            }
            else
            {
                $precache_factor = 7;
            }
            $cached['body_gzip'] = gzencode($data, $precache_factor, FORCE_GZIP);
            $cached['precache_factor'] = $precache_factor;
            $cached['Content_Length'] = strlen($data);
            $cached['GContent_Length'] = strlen($cached['body_gzip']);
        }
        return $cached;
    
    }

}