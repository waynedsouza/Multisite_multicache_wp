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
define('_MULTICACHEWP_EXEC', '1');
if(file_exists(dirname(dirname(__FILE__)).'/libs/multicache_config.php'))
{
	require_once dirname(dirname(__FILE__)).'/libs/multicache_config.php';
}
else{
	die('Multicache config not initialised');
}


$config = new MulticacheConfig();
if(file_exists($config->absolute_path.'wp-load.php'))
{
	require_once $config->absolute_path.'wp-load.php';
}
else {
	die('Wp Not loading');
}
if(!current_user_can( 'manage_options' ))
{
	exit(0);
}
$cache_id = MulticacheHelper::validate_numtext($_REQUEST['c_id']);
if(empty($cache_id))
{
	exit(0);
}


//lets test
require_once plugin_dir_path(dirname(__FILE__)).'libs/multicache_stat.php';
//wp_set_current_user( 0);
//wp_set_auth_cookie( 0 );
$a = new MulticacheStat();
$p_view = $a->getPageByKeyRenderPage($cache_id);
if(empty($p_view))
{
	echo "No Object in memcache";
	exit(0);
}

echo highlight_string($p_view);
//$a->getNonSimulatedTest();
exit(0);
