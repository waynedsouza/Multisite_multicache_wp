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

//lets test
require_once plugin_dir_path(__FILE__).'simcontrol.php';
//wp_set_current_user( 0);
//wp_set_auth_cookie( 0 );
$a = new MulticacheSimcontrol();
$a->getSimulatedIterationTest();
$a->getNonSimulatedTest();
exit(0);
