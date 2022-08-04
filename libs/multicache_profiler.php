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

/*
 * To Instantiate Place this in Index.php
 * define('MULTICACHE_STARTTIME_',microtime(true));
if(!defined('MULTICACHEPROFILERDEBUG')
		&& file_exists($_SERVER['DOCUMENT_ROOT']. '/wp-content/plugins/multicache_wp/libs/multicache_factory.php'))
{
	define('_MULTICACHEWP_EXEC' , true);
	if(!class_exists('MulticacheFactory'))
	{
		include_once $_SERVER['DOCUMENT_ROOT']. '/wp-content/plugins/multicache_wp/libs/multicache_factory.php';
	}
	$GLOBALS['multicache_profiler'] = MulticacheFactory::getProfiler();
	$GLOBALS['multicache_profiler']->mark('startIndex');
	define('MULTICACHEPROFILERDEBUG',true);
}
 *
 * To Place a mark
 * defined('MULTICACHEPROFILERDEBUG') ? $GLOBALS['multicache_profiler']->mark('unique_mark_name'):null;
 *
 *
 *
 */
class MulticacheProfiler
{

    protected $start = 0;

    protected $prefix = '';

    protected $buffer = null;

    protected $marks = null;

    protected $previousTime = 0.0;

    protected $previousMem = 0.0;

    protected static $instances = array();

    public function __construct($prefix = '')
    {

        $this->start = microtime(1);
        $this->prefix = $prefix;
        $this->marks = array();
        $this->buffer = array();
    
    }

    public static function getInstance($prefix = '')
    {

        if (empty(self::$instances[$prefix]))
        {
            self::$instances[$prefix] = new MulticacheProfiler($prefix);
        }
        return self::$instances[$prefix];
    
    }

    public function mark($label)
    {

        $timetrack = microtime(true);
        $current = $timetrack - $this->start;
        $currentMem = memory_get_usage() / 1048576;
        $m = (object) array(
            'prefix' => $this->prefix,
            'timetrack' => $timetrack,
            'time' => ($current > $this->previousTime ? '+' : '-') . (($current - $this->previousTime) * 1000),
            'totalTime' => ($current * 1000),
            'memory' => ($currentMem > $this->previousMem ? '+' : '-') . ($currentMem - $this->previousMem),
            'totalMemory' => $currentMem,
            'label' => $label
        );
        $this->marks[] = $m;
        $mark = sprintf('%s %.3f seconds (%.3f); %0.2f MB (%0.3f) - %s', $m->prefix, $m->totalTime / 1000, $m->time / 1000, $m->totalMemory, $m->memory, $m->label);
        $this->buffer[] = $mark;
        $this->previousTime = $current;
        $this->previousMem = $currentMem;
        return $mark;
    
    }

    public static function getmicrotime()
    {

        list ($usec, $sec) = explode(' ', microtime());
        return ((float) $usec + (float) $sec);
    
    }

    public function getMemory()
    {

        return memory_get_usage();
    
    }

    public function getMarks()
    {

        return $this->marks;
    
    }

    public function getBuffer()
    {

        return $this->buffer;
    
    }

}