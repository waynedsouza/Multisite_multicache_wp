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
// stems from add settings section
// Draw the section header
defined('_MULTICACHEWP_EXEC') or die();

function init_multisite_multicache()
{
	$blog_id = get_current_blog_id();
	//init the db split
	global $wpdb;
	$query = "SHOW TABLES LIKE '". $wpdb->prefix  . "multicache_items'";
	
	//$query = "SHOW TABLES LIKE '". $wpdb->prefix  . "multicache_items'";
	$exists = $wpdb->get_results($query , OBJECT);
	if(empty($exists))
	{
		init_db_split();
	}
	
}

function init_db_split()
{
	//$blog_id = get_current_blog_id();
	//init the db split
	global $wpdb;
	$query[] = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix .  "multicache_advanced_ccomp_factor_base LIKE ".$wpdb->base_prefix."multicache_advanced_ccomp_factor_base";
	$query[] = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix  . "multicache_advanced_loadinstruction_base LIKE ".$wpdb->base_prefix."multicache_advanced_loadinstruction_base";
	$query[] = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix  . "multicache_advanced_precache_factor LIKE ".$wpdb->base_prefix."multicache_advanced_precache_factor";
	$query[] = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix  . "multicache_advanced_testgroups LIKE ".$wpdb->base_prefix."multicache_advanced_testgroups";
	$query[] = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix  . "multicache_advanced_test_results LIKE ".$wpdb->base_prefix."multicache_advanced_test_results";
	$query[] = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix  . "multicache_items LIKE ".$wpdb->base_prefix."multicache_items";
	$query[] = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix  . "multicache_items_slabs LIKE ".$wpdb->base_prefix."multicache_items_slabs";
	$query[] = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix  . "multicache_items_stats LIKE ".$wpdb->base_prefix."multicache_items_stats";
	$query[] = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix  . "multicache_urlarray LIKE ".$wpdb->base_prefix."multicache_urlarray";
	
	foreach($query As $k=>$q)
	{
		 $wpdb->get_results($q , OBJECT);
	}

}