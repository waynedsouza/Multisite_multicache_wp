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
require_once dirname(__FILE__) . '/multicache.php';

class MulticacheController
{

    public $cache;

    public $options;

    public function __construct($options)
    {

        $this->cache = new Multicache($options);
        $this->options = & $this->cache->_options;
        // Overwrite default options with given options
        foreach ($options as $option => $value)
        {
            if (isset($options[$option]))
            {
                $this->options[$option] = $options[$option];
            }
        }
    
    }

    public static function getInstance($type = 'page', $options = array())
    {

        $type = strtolower(preg_replace('/[^A-Z0-9_\.-]/i', '', $type));
        $class = 'Multicache' . ucfirst($type) . 'Controller';
        if (! class_exists($class))
        {
            // Search for the class file in the JCache include paths.
            $path = dirname(__FILE__) . '/multicache_' . strtolower($type) . 'cache_controller.php';
            if (file_exists($path))
            {
                include_once $path;
            }
            else
            {
                // throw new RuntimeException('Unable to load Cache Controller: ' . $type, 500);
            }
        }
        return new $class($options);
    
    }
    
    

}