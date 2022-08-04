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

function multicache_handle_comments()
{
	wp_nonce_field('multicache-url-'.MulticacheUri::getInstance()->toString(),'multicache_comment_n');
	echo '<input type="hidden" name="multicache_comment_u" value="'.MulticacheUri::getInstance()->toString().'" />';
}

function multicache_comment_post_redirect($a)
{
	$conf = MulticacheFactory::getBlogConfig();
	if(isset($_REQUEST['multicache_comment_n']) && isset($_REQUEST['_wp_http_referer']))
	{
		$uri = trim(MulticacheHelper::validate_host(home_url($_REQUEST['_wp_http_referer'])));
		//check_admin_referer('multicache-url-'.$uri ,'multicache_comment_n' );
		
		if(!wp_verify_nonce($_REQUEST['multicache_comment_n'] ,'multicache-url-'.$uri ))
		{
			Return $a;
		}
		$comment_id = get_comment_ID();
		$invalidation_mode = false !== $conf ?  $conf->getC('cache_comment_invalidation', 0) : 0;
		if($invalidation_mode == 1)
		{
			$cache = MulticacheFactory::getCache('page')->cache;
			$user_obj = getUserMulticache();
			$user = !empty($user_obj)? $user_obj['id']->ID : 0;
			$delete = $cache->remove($uri ,'page' , $user);
			
		}
		elseif($invalidation_mode == 2)
		{
			$cache_time = false !== $conf ? $conf->getC('cachetime' ,3600) : 3600;
			$invalidation_object = get_transient('multicache_invalidation_object');
			$invalidation_object = unserialize($invalidation_object);
			if(empty($invalidation_object))
			{
				$invalidation_object = array();
			}
			/*
			 * Assumption: every comment has only one url that it was posted from
			 * 
			 */
			if(!isset($invalidation_object[$comment_id]))
			{
				$invalidation_object[$comment_id] = $uri;
			}
			
			set_transient('multicache_invalidation_object',serialize($invalidation_object) , (int)$cache_time);
		}
	}
	Return $a;
}
add_filter('comment_post_redirect','multicache_comment_post_redirect');



function multicache_invalidate_comment($new_status, $old_status, $comment) {
	
	$conf = MulticacheFactory::getBlogConfig();	
	if($old_status != $new_status) {
		if($new_status == 'approved') {
			$comment_id = $comment->comment_ID;
			$comment_post_id = $comment->comment_post_ID;
			$perma_link = get_permalink($comment_post_id);
			$user = (int) $comment->user_id;
			$invalidation_object = get_transient('multicache_invalidation_object');
			$invalidation_object = unserialize($invalidation_object);
			
			$delete_keys = array();
			if(!empty($invalidation_object))
			{
				$u = $invalidation_object[$comment_id];
				$delete_keys[$u] = 1;	
				
			}
			unset($invalidation_object[$comment_id]);
			if(!empty($invalidation_object))
			{
				$cache_time = false !== $conf ? $conf->getC('cachetime' ,3600) : 3600;
				set_transient('multicache_invalidation_object',serialize($invalidation_object) , (int)$cache_time);
			}
			else
			{
				delete_transient('multicache_invalidation_object');
			}
			$delete_keys[$perma_link] = 1;
			
			if(empty($delete_keys))
			{
				Return;
			}
			
			$cache = MulticacheFactory::getCache('page')->cache;
			
			
			foreach($delete_keys As $key =>$del)
			{
				$delete = $cache->remove($key ,'page' , $user);// were only interested in public pages here
				
			}
		}
	}
}
add_action('transition_comment_status', 'multicache_invalidate_comment', 10, 3);

/*
 * Priority 1
 */
/*
function multicache_preprocess_comment($a)
{
	echo '<h2>multicache_preprocess_comment</h2>';
	var_dump($a);
	echo "here first";exit;
	//Return $a;
}
add_filter('preprocess_comment','multicache_preprocess_comment');

/*
 * 
 */

/*
 * Priority 2 multicache_pre_comment_approved
 */
/*
function multicache_pre_comment_approved($a)
{
echo '<h2>multicache_pre_comment_approved</h2>';
var_dump($a);
//Return $a;
echo "here second";exit;
}
add_filter('pre_comment_approved','multicache_pre_comment_approved');

/*
 * priority 3  multicache_comment_moderation_subject
 */
/*
function multicache_comment_moderation_subject($a)
{
	echo '<h2>multicache_comment_moderation_subject</h2>';
	var_dump($a);
	echo "here third";exit;
	//Return $a;
}
add_filter('comment_moderation_subject','multicache_comment_moderation_subject');


/*
 * In page generation
 */
/*
function multicache_comments_array($a)
{
	echo '<h2>multicache_comments_array</h2>';
	var_dump($a);
	Return $a;
}
add_filter('comments_array','multicache_comments_array');
//output type
 /*multicache_comments_array
array(2) { [0]=> object(stdClass)#1366 (15) { ["comment_ID"]=> string(4) "2075" ["comment_post_ID"]=> string(3) "242" ["comment_author"]=> string(2) "Wd" ["comment_author_email"]=> string(20) "wayndsouza@gmail.com" ["comment_author_url"]=> string(14) "http://y9i.com" ["comment_author_IP"]=> string(15) "117.193.103.100" ["comment_date"]=> string(19) "2015-08-05 18:57:01" ["comment_date_gmt"]=> string(19) "2015-08-05 18:57:01" ["comment_content"]=> string(23) "This is a test response" ["comment_karma"]=> string(1) "0" ["comment_approved"]=> string(1) "0" ["comment_agent"]=> string(65) "Mozilla/5.0 (Windows NT 6.0; rv:39.0) Gecko/20100101 Firefox/39.0" ["comment_type"]=> string(0) "" ["comment_parent"]=> string(1) "0" ["user_id"]=> string(1) "0" } [1]=> object(stdClass)#1363 (15) { ["comment_ID"]=> string(4) "2080" ["comment_post_ID"]=> string(3) "242" ["comment_author"]=> string(2) "Wd" ["comment_author_email"]=> string(20) "wayndsouza@gmail.com" ["comment_author_url"]=> string(14) "http://y9i.com" ["comment_author_IP"]=> string(12) "59.93.29.139" ["comment_date"]=> string(19) "2015-08-06 15:44:35" ["comment_date_gmt"]=> string(19) "2015-08-06 15:44:35" ["comment_content"]=> string(21) "hello this is a check" ["comment_karma"]=> string(1) "0" ["comment_approved"]=> string(1) "1" ["comment_agent"]=> string(65) "Mozilla/5.0 (Windows NT 6.0; rv:39.0) Gecko/20100101 Firefox/39.0" ["comment_type"]=> string(0) "" ["comment_parent"]=> string(1) "0" ["user_id"]=> string(1) "0" } }
*/



 //Carries the link information of the originating comment page to redirect too post comment processing
 


