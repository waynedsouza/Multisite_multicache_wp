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

if(is_multisite())
{
function multicache_classify_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
	
	$network = MulticacheFactory::getNetworkAdmin();
	$result = $network->classifyBlog($site_id, $blog_id);
	
}
add_action( 'wpmu_new_blog', 'multicache_classify_new_blog', 10, 6 );
}