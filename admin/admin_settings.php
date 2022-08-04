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
/*
 * array(6) { [0]=> string(36) "toplevel_page_multicache-config-menu"
 * [1]=> string(36) "toplevel_page_multicache-config-menu"
 * [2]=> string(47) "multicache_page_multicache-simulation-dashboard"
 * [3]=> string(43) "multicache_page_multicache-urlanalyzer-menu"
 * [4]=> string(49) "multicache_page_multicache-group-cache-clear-menu"
 * [5]=> string(52) "multicache_page_multicache-page-cache-inspector-menu"
 * }
 */
defined('_MULTICACHEWP_EXEC') or die();
require_once plugin_dir_path(__FILE__) . 'admin_config_page.php';
//avoid blank space on activation
if($GLOBALS['pagenow'] !=='plugins.php' || 1 )
{
require_once plugin_dir_path(__FILE__) . 'admin_ln_urls.php';
require_once plugin_dir_path(__FILE__) . 'admin_advancedsimulation.php';
require_once plugin_dir_path(__FILE__) . 'admin_pagecacheinspector.php';
require_once plugin_dir_path(__FILE__) . 'admin_network_administration.php';
}
require_once plugin_dir_path(__FILE__) . 'admin_group_cache_clear.php';
require_once plugin_dir_path(__FILE__) . 'admin_multisite_init.php';
//require_once plugin_dir_path(__FILE__) . 'admin_notices.php';
require_once plugin_dir_path(__FILE__) . 'multicache_helper.php';
require_once plugin_dir_path(__FILE__) . 'multicache_button_settings.php';


add_action('admin_menu', 'add_multicache_menu');
add_action('network_admin_menu', 'add_multicache_network_admin_menu');

function add_multicache_menu()
{
    // for settings menu
    // add_options_page( page_title, menu_title, capability, menu_slug, function)
    // add_options_page('Multicache Config', 'Multicache Config', 'manage_options', 'multicache_menu_config', 'multicache_config_menu');
    // add_menu_page( page_title, menu_title, capability, menu_slug, function,icon_url, position )
    $sfx_config = add_menu_page('Multicache', 'Multicache', 'manage_options', 'multicache-config-menu', 'multicache_config_menu', '');
    // creating submenus
    // add_submenu_page( parent_slug, page_title, menu_title, capability,menu_slug, function );
    add_submenu_page('multicache-config-menu', 'Multicache Config', 'Multicache Config', 'manage_options', 'multicache-config-menu', 'multicache_config_menu');
    add_submenu_page('multicache-config-menu', 'Simulation Dashboard', 'Simulation Dashboard', 'manage_options', 'multicache-simulation-dashboard', 'multicache_simulation_dashboard');
    add_submenu_page('multicache-config-menu', 'Url Analyzer', 'Url Analyzer', 'manage_options', 'multicache-urlanalyzer-menu', 'multicache_urlanalyzer_menu');
    add_submenu_page('multicache-config-menu', 'Group Cache Clear', 'Group Cache Clear', 'manage_options', 'multicache-group-cache-clear-menu', 'multicache_group_cache_clear_menu');
    add_submenu_page('multicache-config-menu', 'Page Cache Inspector', 'Page Cache Inspector', 'manage_options', 'multicache-page-cache-inspector-menu', 'multicache_page_cache_inspector_menu');
   
    add_action('admin_enqueue_scripts', 'initialise_admin_stylesandscripts');
    add_action('admin_footer-' . $sfx_config, 'render_inlineAdmin_scripts');
}

function add_multicache_network_admin_menu()
{
	//network admin page
	
		$sfx_config = add_menu_page('Network Administrtion', 'Multicache', 'manage_network_options', 'multicache-network-admin-menu', 'multicache_network_admin_menu', '');
		           add_submenu_page('multicache-network-admin-menu', 'Config', 'Config', 'manage_network_options', 'multicache-network-admin-menu', 'multicache_network_admin_menu');
		           add_submenu_page('multicache-network-admin-menu', 'Multicache', 'Network Cache', 'manage_network_options', 'multicache-network-cache-clear-menu', 'multicache_network_cache_clear_menu');
		// creating submenus
		// add_submenu_page( parent_slug, page_title, menu_title, capability,menu_slug, function );
		
		add_action('admin_enqueue_scripts', 'initialise_admin_stylesandscripts');
		add_action('admin_footer-' . $sfx_config, 'render_inlineAdmin_scripts');
		
	
}
function multicache_network_management_perform_tasks()
{
	
	$r = check_admin_referer('multicache_networkmanagement_admin','multicache_networkmanagement_nonce');
	
	$network_management = MulticacheFactory::getNetworkAdmin();
	
	$multicache_network_config = isset($_POST['multicache_network_config'])? $_POST['multicache_network_config']: null;
	$multicache_network_config = MulticacheHelper::validateArrayRecursive($multicache_network_config);

	$success = $network_management->processNetworkTasks($multicache_network_config);
	$success ==true?	MulticacheHelper::prepareMessageEnqueue(__('Successfully saved network settings','multicache-plugin')):MulticacheHelper::prepareMessageEnqueue(__('Save was not successful','multicache-plugin'));
	
	$page = MulticacheHelper::getPage($_POST['_wp_http_referer']);
	$page = !empty($page) && $page>=2? $page : 1;
	
	wp_redirect(
    add_query_arg(
        array( 'page' => 'multicache-network-admin-menu', 'updated' => 'true' , 'paged'=> $page, ),
        (is_multisite() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' ))
    )
);
	exit(0);
}
function multicache_network_cache_management_perform_tasks()
{
	
	if(isset($_POST['delete_cache'])){
		check_admin_referer('multicache_cache_admin','multicache_cache_control_nonce');
		$cache_object = MulticacheFactory::getCacheAdmin();
		$cache_object->clear_cache($_POST['cid']);
		$page = MulticacheHelper::getPage($_POST['_wp_http_referer']);
		$page = !empty($page) && $page>=2? $page : 1;
		$subfolder = MulticacheUri::getInstance($_POST['_wp_http_referer'])->getVar('subfolder');
		$back = MulticacheUri::getInstance($_POST['_wp_http_referer'])->getVar('back');
		$args = array();
		if(isset($subfolder))
		{
			$args['subfolder'] = $subfolder;
		}
		if(isset($back)){
			$args['back'] = $back;
		}
		if(isset($page)){
			$args['paged'] = $page;
		}
		$args['page'] = 'multicache-network-cache-clear-menu';
		$args['updated'] = true;
		
		wp_redirect(
				add_query_arg(
						$args,
						(is_multisite() ? network_admin_url( 'admin.php' ) : admin_url( 'admin.php' ))
						)
				);
	exit(0);
	}
	
}
add_action(
		'network_admin_edit_multicache_network_management',
		'multicache_network_management_perform_tasks'
		);
add_action(
		'network_admin_edit_multicache_network_cache_management',
		'multicache_network_cache_management_perform_tasks');

function render_inlineAdmin_scripts()
{
$protocol = isset($_SERVER['HTTPS'])? 'https://':'http://';
$ajax_url = admin_url('admin-ajax.php',$protocol);
$options = get_option('multicache_config_options');
$multicache_conduit_switch = $options['conduit_switch'];
    ?>
<script>
        jQuery(document).ready(function (){
        	//alert('done1'); 
        	//start
        	var ajaxurlmulticache = '<?php echo $ajax_url;?>'; 
        	if(jQuery('.multicache_message').length >0)
        	{
            	var t = {
                    	action:'multicache_messenger',
                        };
                jQuery.get(ajaxurlmulticache,t);
        	}
        	//jQuery('div.message').not('.multicache_message').hide(); moved to admin template
        	//stop
        	
        	var initMulAdmin = {
        			caching_multicache_config:"<?php echo $options['caching']; ?>",
        			cache_handler_multicache_config:"<?php echo $options['cache_handler']; ?>",
        			gtmetrix_api_budget_multicache_config:"<?php echo $options['gtmetrix_api_budget']; ?>",
        			gtmetrix_cycles_multicache_config:"<?php echo $options['gtmetrix_cycles']; ?>",
        			precache_factor_min_multicache_config:"<?php echo $options['precache_factor_min']; ?>",
        			precache_factor_max_multicache_config_chosen:"<?php echo $options['precache_factor_max']; ?>",
        			precache_factor_default_multicache_config:"<?php echo $options['precache_factor_default']; ?>",
        			targetpageloadtime_multicache_config:"<?php echo $options['targetpageloadtime']; ?>",
        			orphaned_scripts_multicache_config:"<?php echo $options['orphaned_scripts']; ?>",
        			orphaned_styles_loading_multicache_config_chosen:"<?php echo $options['orphaned_styles_loading']; ?>",
    		};   

            		
            var key;
    		for(key in initMulAdmin )
    		{
              var id = '#'+ key;
   			 jQuery(id).val(initMulAdmin[key]).trigger("chosen:updated");
    		//console.log(key +' '+ initMulAdmin[key] + 'logging the key' );
    		}
    		var init_jsExcludes = <?php echo json_encode(unserialize($options['excluded_components']));?>;
    		var init_cssExcludes = <?php echo json_encode(unserialize($options['cssexcluded_components']));?>;
    		var init_imgExcludes = <?php echo json_encode(unserialize($options['imgexcluded_components']));?>;
    		validateExcludes(init_jsExcludes,'js_excludes');
    		validateExcludes(init_cssExcludes,'css_excludes');
    		validateExcludes(init_imgExcludes,'img_excludes');
    		/*
        	jQuery('select.js_excludes').each(function(key , value){
            	var id = this.id;
            	var obj_name = id.replace('_excluded_components_multicache_config','')
            	if((typeof init_jsExcludes != "undefined") && init_jsExcludes.hasOwnProperty(obj_name) && init_jsExcludes[obj_name].length >= 1 )
                	{
                	jQuery(this).val(init_jsExcludes[obj_name]).trigger("chosen:updated");
        			//console.log('logging value' + init_jsExcludes[obj_name] + ' len1 ' + init_jsExcludes[obj_name].length );
            			}
            	else{
            		jQuery(this).val(0).trigger("chosen:updated");
            	}
            	//console.log('Pro settings '+ key + ' '+ value + ' id ' + id +  ' js excludes ' + JSON.stringify(init_jsExcludes) +' type of' +typeof init_jsExcludes+ ' '+ init_jsExcludes.jetpack_by_wordpress_com + ' ' + obj_name);
        	});
        	*/
				});
		function validateExcludes( obj, type )
		{
			var select_str = 'select.'+ type;
			jQuery(select_str).each(function(key , value){
            	var id = this.id;
            	var obj_name = id.replace('_excluded_components_multicache_config','')
            	obj_name = obj_name.replace('_imgexcluded_components_multicache_config','')
            	obj_name = obj_name.replace('_cssexcluded_components_multicache_config','');
            	//console.log('logging value'+ typeof obj + 'has own property' +obj.hasOwnProperty(obj_name) );
            	if((typeof obj != "undefined") && obj.hasOwnProperty(obj_name)  && obj[obj_name]['value'].length >=1 )
                	{
                	jQuery(this).val(obj[obj_name]['value']).trigger("chosen:updated");
        			console.log('IN logging value' + obj[obj_name]['value'] + ' len1 ' + obj[obj_name]['value'].length + ' type of '+ typeof obj );
            			}
            	else{
            		jQuery(this).val(0).trigger("chosen:updated");
            	}
            	console.log('Pro settings '+ key + ' '+ value + ' id ' + id + ' ' + type +'  '+ JSON.stringify(obj) +' type of' +typeof obj+ ' '+  ' ' + obj_name);
        	});
		}
	
        </script>
<?php

}


function initialise_admin_stylesandscripts($page)
{

    if (!($page == 'toplevel_page_multicache-config-menu' || strpos($page,'multicache') !== false))
    {
        Return;
    }
    // wp_register_style( $handle, $src, $deps, $ver, $media );
    $lurl = plugins_url('assets/' , dirname(__FILE__));
    wp_register_style('multicache_bootstrap.min.css', $lurl . 'css/multicache_bootstrap.min.css');
    
    wp_register_style('multicache_chosen.min.css', $lurl . 'css/multicache_chosen.min.css');
    wp_register_style('multicache_jquery.minicolors.css', $lurl . 'css/jquery.minicolors.css');
    wp_register_style('multicache_smoothness.jqueryui.css', $lurl . 'css/multicache_smoothness.jqueryui.css', array(
        'multicache_bootstrap.min.css',
        'multicache_chosen.min.css'
    ));
    // wp_register_script( $handle, $src, $deps, $ver, $in_footer );
    wp_register_script('multicache_bootstrap.min.js', $lurl . 'js/multicache_bootstrap.min.js', array(
        'jquery'
    ));
    wp_register_script('multicache_chosen.jquery.min.js', $lurl . 'js/multicache_chosen.jquery.min.js', array(
        'jquery'
    ));
    wp_register_script('multicache_jquery.minicolors.min.js', $lurl . 'js/jquery.minicolors.min.js', array(
    		'jquery'
    ));
    wp_register_script('multicache_admin_template.js', $lurl . 'js/multicache_admin_template.js', array(
        'jquery',
        'jquery-ui-tabs',
        'multicache_chosen.jquery.min.js',
        'jquery-ui-tooltip',
    	'multicache_jquery.minicolors.min.js'
    ), '', true);
    
    // ENQUEUE
    // wp_enqueue_style( $handle, $src, $deps, $ver, $media );
    wp_enqueue_style('multicache_bootstrap.min.css');
       wp_enqueue_style('multicache_chosen.min.css');
       wp_enqueue_style('multicache_jquery.minicolors.css');
    wp_enqueue_style('multicache_smoothness.jqueryui.css');
    
    // wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
    wp_enqueue_script('multicache_bootstrap.min.js');
    wp_enqueue_script('multicache_chosen.jquery.min.js');
    
    wp_enqueue_script('jquery-ui-tabs');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery-form');
    wp_enqueue_script('multicache_jquery.minicolors.min.js');
    wp_enqueue_script('multicache_admin_template.js');
    wp_enqueue_script('jquery-ui-tooltip');

}


//clear admin notices once displayed
function clear_multicache_admin_notice()
{
	 delete_transient('multicache_admin_notices');
	 delete_transient('multicache_admin_generic_notices');
	
}

add_action('wp_ajax_multicache_messenger' , 'clear_multicache_admin_notice');



function clear_multicache_auth_transients()
{
	$trans_name = 'multicache_auth_'.get_current_user_id();
	delete_transient($trans_name);
	//we need to establish that getuserid works on log out
}
add_action('wp_logout' , 'clear_multicache_auth_transients');
//end of clear admin notices
function multicache_notice_scrape_template_successful()
{
	echo "<div class='updated multicache_message message inline'> <p> template succesfully scraped. Please select the principle jQuery library and perform tweaks before saving </p > </div >";
}
function multicache_notice_scrape_template_failed()
{
	echo "<div class='error multicache_message  message inline'> <p> Scraping unsuccesful. Please check multicache logs for errors  </p > </div >";
}
function multicache_notice_scrape_css_successful()
{
	echo "<div class='updated multicache_message message inline'> <p> Css succesfully scraped </p > </div >";
}
function multicache_notice_scrape_css_failed()
{
	echo "<div class='error multicache_message  message inline'> <p>Css Scraping unsuccesful. Please check multicache logs for errors  </p > </div >";
}

function multicache_notice_google_authenticate_successful()
{
	$transients = get_transient('multicache_admin_notices');
	$transients = unserialize($transients);
	$nos =  $transients["message"]['google_authenticate'];
	echo "<div class='updated multicache_message message inline'> <p> Succesfully retreived $nos urls </p > </div >";
}
function multicache_notice_google_authenticate_failed()
{
	echo "<div class='error multicache_message  message inline'> <p>Google Authentication failed. Please check multicache logs for errors  </p > </div >";
}


function setAdminMessages()
{
	$transients = get_transient('multicache_admin_notices');
	$transients = unserialize($transients);
	if(empty($transients))
	{
		Return;
	}
	foreach($transients As $key => $value)
	{
		if($key == 'message')
		{
			continue;
		}
		$value= !empty($value)? 'successful' : 'failed';
		add_action('admin_notices', 'multicache_notice_'.$key.'_'.$value);
	}
	
	
	
}

function setMulticacheGenericMessages()
{
	$transients = get_transient('multicache_admin_generic_notices');
	//$transients = unserialize($transients); wp serializes by default
	
	if(empty($transients))
	{
		Return;
	}
	foreach($transients As $obj)
	{
		printMulticacheAdminMessages($obj['type'],$obj['message']);
	}
}
function printMulticacheAdminMessages($type , $message)
{
	$type = $type =='error'? 'error' : 'updated';
	echo "<div class='$type multicache_message  message inline'> <p> $message </p > </div >";
}
add_action('admin_notices', 'setMulticacheGenericMessages');
setAdminMessages();

function setFilterFlags()
{
	$filters = array();
	//results type ok
	if(!empty($_POST['filter_simflag_rt']))
	{
		$filters['filter_simflag_rt'] = MulticacheHelper::validate_text($_POST['filter_simflag_rt']['filter_simflag_rt']);
	}
	//completion ok
	if(!empty($_POST['filter_simflag_c']))
	{
		$filters['filter_simflag_c'] = MulticacheHelper::validate_num($_POST['filter_simflag_c']['filter_simflag_c']);
	}
	//tolerance ok
	if(!empty($_POST['filter_simflag_tol']))
	{
		$filters['filter_simflag_tol'] = MulticacheHelper::validate_num($_POST['filter_simflag_tol']['filter_simflag_tol']);
	}
	//cache type ok
	if(!empty($_POST['filter_simflag_ct']))
	{
		$filters['filter_simflag_ct'] = MulticacheHelper::validate_num($_POST['filter_simflag_ct']['filter_simflag_ct']);
	}
	//pages 
	if(isset($_POST['filter_simflag_page']['filter_simflag_page']))
	{
		$filters['filter_simflag_page'] = MulticacheHelper::validate_num($_POST['filter_simflag_page']['filter_simflag_page']);
	}
	//precache ok
	if(!empty($_POST['filter_simflag_precache']))
	{
		$filters['filter_simflag_precache'] = MulticacheHelper::validate_num($_POST['filter_simflag_precache']['filter_simflag_precache']);
	}
	//ccomp ok
	if(!empty($_POST['filter_simflag_ccomp']))
	{
		$filters['filter_simflag_ccomp'] = MulticacheHelper::validate_num($_POST['filter_simflag_ccomp']['filter_simflag_ccomp']);
	}
	
	//mode ok
	if(!empty($_POST['filter_simflag_opmode']))
	{
		$filters['filter_simflag_opmode'] = MulticacheHelper::validate_num($_POST['filter_simflag_opmode']['filter_simflag_opmode']);
	}
	
	//datefrom
	if(isset($_POST['datepicker']['adv_res_from']))
	{
		$filters['filter_date_from'] = MulticacheHelper::validate_num($_POST['datepicker']['adv_res_from']);
	}
	
	//dateto
	if(isset($_POST['datepicker']['adv_res_to']))
	{
		$filters['filter_date_to'] = MulticacheHelper::validate_num($_POST['datepicker']['adv_res_to']);
	}
	
	//cache type
	/*
	if(isset($_POST['filter_cache_type']))
	{
		$filters['filter_cache_type'] = MulticacheHelper::validate_num($_POST['filter_cache_type']['filter_cache_type']);
	}
	*/
	$user_ID = get_current_user_id();
	if(isset($_POST['filter_pcitype_fl']['filter_pcitype_fl']))
	{
		MulticacheHelper::log_error('Set transient called pci','filter-transients',$_POST);
		set_transient('multicache_pcitype_filters'.$user_ID, MulticacheHelper::validate_num($_POST['filter_pcitype_fl']['filter_pcitype_fl']), 3600);
	}
	
	if(!empty($filters) && $_POST['screen_name'] === 'msd')
	{
		MulticacheHelper::log_error('Set transient called','filter-transients',$_POST);
		set_transient('multicache_advsim_filters'.$user_ID, $filters, 3600);
	}
	/*
	if(!empty($filters) && $_POST['screen_name'] === 'gcc')
	{
		MulticacheHelper::log_error('Set transient called','filter-transients',$_POST);
		set_transient('multicache_gcc_seg_filters'.$user_ID, $filters, 600);
	}
	*/
	
}


function multicacheconfigController()
{
	if(is_multisite())
	{
		init_multisite_multicache();
	}
	if(isset($_POST["scrape_template"]))
	{
		check_admin_referer('multicache_plugin_save','multicache_form_control');
		$options = get_option('multicache_config_options');
		$url = !empty($_REQUEST['multicache_config_options']['default_scrape_url'])?$_REQUEST['multicache_config_options']['default_scrape_url']:$options['default_scrape_url'];
		$templater = MulticacheFactory::getTemplater();
		$success = $templater->scrapeJavascript($url);
		MulticacheHelper::saveTransient('scrape_template', $success);
		wp_redirect(admin_url().'admin.php?page=multicache-config-menu#page-js-tweaks');
		exit(0);		
	}

	if(isset($_POST["scrape_css"]))
	{
		
		check_admin_referer('multicache_plugin_save','multicache_form_control');
		$options = get_option('multicache_config_options');
		$url = !empty($_REQUEST['multicache_config_options']['css_scrape_url'])?$_REQUEST['multicache_config_options']['css_scrape_url']:$options['css_scrape_url'];
		$templater = MulticacheFactory::getTemplater();
		$success = $templater->scrapeCss($url);
		MulticacheHelper::saveTransient('scrape_css', $success);
		wp_redirect(admin_url().'admin.php?page=multicache-config-menu#page-css-tweaks');
		exit(0);
	
	}
	if(isset($_POST["google_authenticate"]))
	{
		check_admin_referer('multicache_plugin_save','multicache_form_control');
		//$options = get_option('multicache_config_options');
		$lnobject = MulticacheFactory::getLnObject();
		$nos = $lnobject->getGoogleAuth();
		$success = !empty($nos)? true :false;
		MulticacheHelper::saveTransient('google_authenticate', $success , $nos);
		wp_redirect(admin_url().'admin.php?page=multicache-config-menu#page-parta');
		exit(0);
			
	}
	if(isset($_GET['authg']) && isset($_GET['code']))
	{
		$lnobject = MulticacheFactory::getLnObject();
		$nos = $lnobject->getGoogleAuth();
		$success = !empty($nos)? true :false;
		MulticacheHelper::saveTransient('google_authenticate', $success , $nos);
	}
	
	if(isset($_POST['delete_cache'])){
		check_admin_referer('multicache_cache_admin','multicache_cache_control_nonce');
		$cache_object = MulticacheFactory::getCacheAdmin();
		$cache_object->clear_cache($_POST['cid']);
				
	}
	if( isset($_POST['filter_cache_type']['filter_cache_type'])){
		{
			$cache_filter = MulticacheHelper::validate_num($_POST['filter_cache_type']['filter_cache_type']);
			$user_ID = get_current_user_id();
			set_transient('multicache_cache_filter_'.$user_ID, $cache_filter ,3600);
			
		}
		
		}
		
		if(isset($_POST['delete_urls']))
		{
			check_admin_referer('multicache_lnurl_admin','multicache_lnurl_control_nonce');
			$lnurl = MulticacheFactory::getMulticacheUrls();
			$lnurl->delete($_POST['cid']);
		}
		if(isset($_POST['update_multicache']))
		{
			check_admin_referer('multicache_lnurl_admin','multicache_lnurl_control_nonce');
			$lnurl = MulticacheFactory::getMulticacheUrls();
			$lnurl->makeRegisterlnclass();
						
		}
		
		if(isset($_POST['delete_advres']))
		{
			check_admin_referer('multicache_advancedsim_admin','multicache_advancedsimulation_nonce');
			$advres = MulticacheFactory::getAdvancedSimulation();
			$advres->delete($_POST['cid']);
		}
		
		if(isset($_POST['delete_pci']))
		{
			check_admin_referer('multicache_pagecacheinspector_admin','multicache_pagecacheinspector_nonce');
			$pgcache = MulticacheFactory::getPageCacheObject();
			$pgcache->delete(MulticacheHelper::validate_google($_POST['cid']));
		}
		
		//a generic filter
		if(isset($_POST['filter_order']) && isset($_POST['filter_order_Dir']) && isset($_POST['screen_name']))
		{
			check_admin_referer('update-options','_wpnonce');
			$rel = MulticacheHelper::validate_text($_POST['screen_name']);
			//lets get a relative page for this setting
			
			$user_ID = get_current_user_id();
			
			$order_dir= array('order' =>MulticacheHelper::validate_text( $_POST['filter_order']), 'dir' => MulticacheHelper::validate_text( $_POST['filter_order_Dir']));
			set_transient('multicache_filter_order_dir_'.$user_ID.'_'.$rel, $order_dir ,3600);
		}
		
		$current = MulticacheUri::getInstance()->getVar('page');
		if($current === 'multicache-config-menu')
		{
			$conf = MulticacheFactory::getBlogConfig();
			$cache_handler = false !== $conf ? $conf->getC('cache_handler' , 'file') : 'file';
			if($cache_handler === 'fastcache')
			{
				$cache = Multicache::getInstance('')->cache->_getStorage();
				if(isset($cache) && $cache instanceof MulticacheStorageFastcache)
				{
					$issupported = $cache->_isSupported();
					if(empty($issupported))
					{
						MulticacheHelper::prepareMessageEnqueue(__('fastcache cache handler :- memcached failed/not supported. Please initialise memcached or change cache handler to file.' , 'multicache-plugin'));
					}
				}
			}
			
		}
		
	setFilterFlags();
}


add_action('admin_init', 'multicache_admin_init');

function multicache_admin_init()
{
	MulticacheFactory::loadPageScriptsCSS();
	MulticacheFactory::loadJsStrategy();
	multicacheconfigController();
	//testing for autoload to no
	//comes into play only on multisite
	$c_option = get_option('multicache_config_options');
	if(empty($c_option))
	{
		//only on multisite in single site this is supposed to be set already
		MulticacheHelper::loadConfigOptions();
		
	}
	
    register_setting('multicache_config_options', 'multicache_config_options', 'multicache_config_validate_options');
    
    /* register setting param1 - group name, param2 - option name same as get option, param 3 - optional function call back */
    // section pagesettings
    add_settings_section('multicache_config_generic_begin', '', 'multicache_begin_group', 'multicache-config-menu');
    add_settings_section('multicache_config_page_settings', '', 'multicache_config_page_settings_section_html', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_end', '', 'multicache_end_group', 'multicache-config-menu');
    // section page optimization
    add_settings_section('multicache_config_generic_begin2', '', 'multicache_begin_group', 'multicache-config-menu');
    // a half cuddle opening
    add_settings_section('multicache_config_generic_halfcuddleopen1', '', 'multicache_halfcuddleopen_group', 'multicache-config-menu');
    add_settings_section('multicache_config_page_optimisation', '', 'multicache_config_page_optimisation_section_html', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleclose1', '', 'multicache_halfcuddleclose_group', 'multicache-config-menu');
    // second half
    add_settings_section('multicache_config_generic_halfcuddleopen2', '', 'multicache_halfcuddleopen_group', 'multicache-config-menu');
    add_settings_section('multicache_config_page_optimisation_conditionsandparams', 'Testing Conditions & Parameters', 'multicache_config_page_optimisation_conditionsandparams_section_html', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleclose2', '', 'multicache_halfcuddleclose_group', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_end2', '', 'multicache_end_group', 'multicache-config-menu');
    // sectionpage part @author wayneds
    add_settings_section('multicache_config_generic_begin3', '', 'multicache_begin_group', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleopen3', '', 'multicache_halfcuddleopen_group', 'multicache-config-menu');
    add_settings_section('multicache_config_page_parta', __('Google Console Settings', 'multicache-plugin'), 'multicache_config_page_parta_section_html', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleclose3', '', 'multicache_halfcuddleclose_group', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleopen4', '', 'multicache_halfcuddleopen_group', 'multicache-config-menu');
    add_settings_section('multicache_config_page_parta_part2', '', 'multicache_config_page_parta_part2_section_html', 'multicache-config-menu');
    add_settings_section('multicache_config_page_parta_part2_xtd', __('Cart Advanced Settings', 'multicache-plugin'), 'multicache_config_page_parta__part2_xtd_section_html', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleclose4', '', 'multicache_halfcuddleclose_group', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_end3', '', 'multicache_end_group', 'multicache-config-menu');
    
    add_settings_section('multicache_config_generic_begin4', '', 'multicache_begin_group', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleopen5', '', 'multicache_halfcuddleopen_group', 'multicache-config-menu');
    add_settings_section('multicache_config_page_partb', '', 'multicache_config_page_partb_section_html', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleclose5', '', 'multicache_halfcuddleclose_group', 'multicache-config-menu');
    
    add_settings_section('multicache_config_generic_halfcuddleopen6', '', 'multicache_halfcuddleopen_group', 'multicache-config-menu');
    add_settings_section('multicache_config_page_partb_extended', 'Algorithm Settings', 'multicache_config_page_partb_extended_section_html', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleclose6', '', 'multicache_halfcuddleclose_group', 'multicache-config-menu');
    
    //tolerance settings
    add_settings_section('multicache_config_generic_halfcuddleopen30', '', 'multicache_halfcuddleopen_group', 'multicache-config-menu');
    add_settings_section('multicache_config_page_partb_extended_tolerance', 'Tolerance Settings', 'multicache_config_page_partb_tolerance_section_html', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleclose30', '', 'multicache_halfcuddleclose_group', 'multicache-config-menu');
    //end tolerance settings
    
    add_settings_section('multicache_config_generic_end4', '', 'multicache_end_group', 'multicache-config-menu');
    
    add_settings_section('multicache_config_generic_begin5', '', 'multicache_begin_group', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleopen7', '', 'multicache_halfcuddleopen_group', 'multicache-config-menu');
    add_settings_section('multicache_config_page_js_tweaks', '', 'multicache_config_page_js_tweaks_section_html', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleclose7', '', 'multicache_halfcuddleclose_group', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleopen8', '', 'multicache_halfcuddleopen_group', 'multicache-config-menu');
    add_settings_section('multicache_config_page_js_tweaks_part2', '', 'multicache_config_page_js_tweaks_part2_section_html', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleclose8', '', 'multicache_halfcuddleclose_group', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_fullcuddleopen1', '', 'multicache_fullcuddleopen_group', 'multicache-config-menu');
    add_settings_section('multicache_config_page_js_tweaks_individual_script_tweaks', '', 'multicache_config_page_js_tweaks_script_tweaks_section_html', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_fullcuddleclose1', '', 'multicache_fullcuddleclose_group', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_end5', '', 'multicache_end_group', 'multicache-config-menu');
    
    add_settings_section('multicache_config_generic_begin6', '', 'multicache_begin_group', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleopen9', '', 'multicache_halfcuddleopen_group', 'multicache-config-menu');
    add_settings_section('multicache_config_page_js_inclusion', '', 'multicache_config_page_js_inclusion_section_html', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleclose9', '', 'multicache_halfcuddleclose_group', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleopen10', '', 'multicache_halfcuddleopen_group', 'multicache-config-menu');
    add_settings_section('multicache_config_page_js_inclusion_plugin', 'Plugin Exclusion', 'multicache_config_page_js_inclusion_plugin_section_html', 'multicache-config-menu');
   
    add_settings_section('multicache_config_generic_halfcuddleclose10', '', 'multicache_halfcuddleclose_group', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_end6', '', 'multicache_end_group', 'multicache-config-menu');
    
    add_settings_section('multicache_config_generic_begin7', '', 'multicache_begin_group', 'multicache-config-menu');
    add_settings_section('multicache_config_page_css_tweaks', '', 'multicache_config_page_css_tweaks_section_html', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_fullcuddleopen1', '', 'multicache_fullcuddleopen_group', 'multicache-config-menu');
    add_settings_section('multicache_config_page_css_tweaks_individual_script_tweaks', '', 'multicache_config_page_css_tweaks_script_tweaks_section_html', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_fullcuddleclose1', '', 'multicache_fullcuddleclose_group', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_end7', '', 'multicache_end_group', 'multicache-config-menu');
    
    add_settings_section('multicache_config_generic_begin8', '', 'multicache_begin_group', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleopen20', '', 'multicache_halfcuddleopen_group', 'multicache-config-menu');
    add_settings_section('multicache_config_page_css_inclusion', '', 'multicache_config_page_css_inclusion_section_html', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleclose20', '', 'multicache_halfcuddleclose_group', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleopen21', '', 'multicache_halfcuddleopen_group', 'multicache-config-menu');
    add_settings_section('multicache_config_page_css_inclusion_plugin', 'Plugin Exclusion', 'multicache_config_page_css_inclusion_plugin_section_html', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleclose21', '', 'multicache_halfcuddleclose_group', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_end8', '', 'multicache_end_group', 'multicache-config-menu');
    
    add_settings_section('multicache_config_generic_begin9', '', 'multicache_begin_group', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleopen11', '', 'multicache_halfcuddleopen_group', 'multicache-config-menu');
    add_settings_section('multicache_config_page_image_tweaks', __('Lazy Load Settings', 'multicache-plugin'), 'multicache_config_page_image_tweaks_section_html', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleclose11', '', 'multicache_halfcuddleclose_group', 'multicache-config-menu');
    // subsetting for section lazyload exclusions
    add_settings_section('multicache_config_generic_halfcuddleopen12', '', 'multicache_halfcuddleopen_group', 'multicache-config-menu');
    add_settings_section('multicache_config_page_image_tweaks_exclusions', __('Lazy Load Exclusions', 'multicache-plugin'), 'multicache_config_page_image_tweaks_exclusions_section_html', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleclose12', '', 'multicache_halfcuddleclose_group', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleopen23', '', 'multicache_halfcuddleopen_group', 'multicache-config-menu');
    add_settings_section('multicache_config_page_image_tweaks_plugin_exclusions', __('Plugin Exclusions', 'multicache-plugin'), 'multicache_config_page_image_tweaks_plugin_exclusions_section_html', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_halfcuddleclose23', '', 'multicache_halfcuddleclose_group', 'multicache-config-menu');
    add_settings_section('multicache_config_generic_end9', '', 'multicache_end_group', 'multicache-config-menu');
    // fields
    /* param1 - html id tag multicache_config_main, param2 - tetle text to show in a h3, param3 - multicache_config_section_text a callback function to echo some text, param 4 - the settings page,param5 - $section */
    add_settings_field('multicache_config_caching_text_string', __('Caching', 'multicache-plugin'), 'multicache_config_setting_caching_input', 'multicache-config-menu', 'multicache_config_page_settings');
    add_settings_field('multicache_config_gzip_text_string', __('Gzip', 'multicache-plugin'), 'multicache_config_setting_gzip_input', 'multicache-config-menu', 'multicache_config_page_settings');
    add_settings_field('multicache_config_cache_handler_text_string', __('Cache Handler', 'multicache-plugin'), 'multicache_config_setting_cache_handler_input', 'multicache-config-menu', 'multicache_config_page_settings');
    add_settings_field('multicache_config_cache_time_text_string', __('Cache Time', 'multicache-plugin'), 'multicache_config_setting_cache_time_input', 'multicache-config-menu', 'multicache_config_page_settings');
    add_settings_field('multicache_config_multicache_persist_text_string', __('Multicache Persist', 'multicache-plugin'), 'multicache_config_setting_multicache_persist_input', 'multicache-config-menu', 'multicache_config_page_settings');
    add_settings_field('multicache_config_multicache_compression_text_string', __('Multicache Compression', 'multicache-plugin'), 'multicache_config_setting_multicache_compression_input', 'multicache-config-menu', 'multicache_config_page_settings');
    add_settings_field('multicache_config_multicache_host_text_string', __('Multicache Host', 'multicache-plugin'), 'multicache_config_setting_multicache_host_input', 'multicache-config-menu', 'multicache_config_page_settings');
    add_settings_field('multicache_config_multicache_port_text_string', __('Multicache Port', 'multicache-plugin'), 'multicache_config_setting_multicache_port_input', 'multicache-config-menu', 'multicache_config_page_settings');
    
    // optimization
    add_settings_field('multicache_config_gtmetrix_testing_text_string', __('GTMetrix Testing', 'multicache-plugin'), 'multicache_config_setting_gtmetrix_testing_input', 'multicache-config-menu', 'multicache_config_page_optimisation');
    add_settings_field('multicache_config_gtmetrix_api_budget_text_string', __('Tests per day', 'multicache-plugin'), 'multicache_config_setting_gtmetrix_api_budget_input', 'multicache-config-menu', 'multicache_config_page_optimisation');
    add_settings_field('multicache_config_gtmetrix_email_text_string', __('Email', 'multicache-plugin'), 'multicache_config_setting_gtmetrix_email_input', 'multicache-config-menu', 'multicache_config_page_optimisation');
    add_settings_field('multicache_config_gtmetrix_token_text_string', __('GTMetrix Token', 'multicache-plugin'), 'multicache_config_setting_gtmetrix_token_input', 'multicache-config-menu', 'multicache_config_page_optimisation');
    add_settings_field('multicache_config_gtmetrix_adblock_text_string', __('Adblock', 'multicache-plugin'), 'multicache_config_setting_gtmetrix_adblock_input', 'multicache-config-menu', 'multicache_config_page_optimisation');
    add_settings_field('multicache_config_gtmetrix_test_url_text_string', __('Url to test', 'multicache-plugin'), 'multicache_config_setting_gtmetrix_test_url_input', 'multicache-config-menu', 'multicache_config_page_optimisation');
    add_settings_field('multicache_config_gtmetrix_allow_simulation_text_string', __('Allow Simulation', 'multicache-plugin'), 'multicache_config_setting_gtmetrix_allow_simulation_input', 'multicache-config-menu', 'multicache_config_page_optimisation');
    add_settings_field('multicache_config_gtmetrix_simulation_advanced_text_string', __('Advanced Simulation', 'multicache-plugin'), 'multicache_config_setting_gtmetrix_simulation_advanced_input', 'multicache-config-menu', 'multicache_config_page_optimisation');
    add_settings_field('multicache_config_gtmetrix_jssimulation_parse_text_string', __('Simulation Parsing', 'multicache-plugin'), 'multicache_config_setting_gtmetrix_jssimulation_parse_input', 'multicache-config-menu', 'multicache_config_page_optimisation');
    add_settings_field('multicache_config_gtmetrix_cycles_text_string', __('Testing Cycles', 'multicache-plugin'), 'multicache_config_setting_gtmetrix_cycles_input', 'multicache-config-menu', 'multicache_config_page_optimisation');
    
    add_settings_field('multicache_config_precache_factor_min_string', __('Precache (min)', 'multicache-plugin'), 'multicache_config_setting_precache_factor_min_input', 'multicache-config-menu', 'multicache_config_page_optimisation_conditionsandparams');
    add_settings_field('multicache_config_precache_factor_max_string', __('Precache (max)', 'multicache-plugin'), 'multicache_config_setting_precache_factor_max_input', 'multicache-config-menu', 'multicache_config_page_optimisation_conditionsandparams');
    add_settings_field('multicache_config_precache_factor_default_string', __('Precache (default)', 'multicache-plugin'), 'multicache_config_setting_precache_factor_default_input', 'multicache-config-menu', 'multicache_config_page_optimisation_conditionsandparams');
    add_settings_field('multicache_config_ccomp_factor_min_string', __('Cache Compression (min)', 'multicache-plugin'), 'multicache_config_setting_ccomp_factor_min_input', 'multicache-config-menu', 'multicache_config_page_optimisation_conditionsandparams');
    add_settings_field('multicache_config_ccomp_factor_max_string', __('Cache Compression (max)', 'multicache-plugin'), 'multicache_config_setting_ccomp_factor_max_input', 'multicache-config-menu', 'multicache_config_page_optimisation_conditionsandparams');
    add_settings_field('multicache_config_ccomp_factor_step_string', __('Cache Compression (step)', 'multicache-plugin'), 'multicache_config_setting_ccomp_factor_step_input', 'multicache-config-menu', 'multicache_config_page_optimisation_conditionsandparams');
    add_settings_field('multicache_config_ccomp_factor_default_string', __('Cache Compression (default)', 'multicache-plugin'), 'multicache_config_setting_ccomp_factor_default_input', 'multicache-config-menu', 'multicache_config_page_optimisation_conditionsandparams');
    add_settings_field('multicache_config_cron_url', __('Cron Url', 'multicache-plugin'), 'multicache_config_setting_cron_url_input', 'multicache-config-menu', 'multicache_config_page_optimisation_conditionsandparams');
    
    add_settings_field('multicache_config_googleclientid', __('Google ClientId', 'multicache-plugin'), 'multicache_config_setting_googleclientid_input', 'multicache-config-menu', 'multicache_config_page_parta');
    add_settings_field('multicache_config_googleclientsecret', __('Google ClientSecret', 'multicache-plugin'), 'multicache_config_setting_googleclientsecret_input', 'multicache-config-menu', 'multicache_config_page_parta');
    add_settings_field('multicache_config_googleviewid', __('Google ViewId', 'multicache-plugin'), 'multicache_config_setting_googleviewid_input', 'multicache-config-menu', 'multicache_config_page_parta');
    add_settings_field('multicache_config_redirecturi', __('Redirect Uri', 'multicache-plugin'), 'multicache_config_setting_redirecturi_input', 'multicache-config-menu', 'multicache_config_page_parta');
    add_settings_field('multicache_config_googlestartdate', __('Start Date', 'multicache-plugin'), 'multicache_config_setting_googlestartdate_input', 'multicache-config-menu', 'multicache_config_page_parta');
    add_settings_field('multicache_config_googleenddate', __('End Date', 'multicache-plugin'), 'multicache_config_setting_googleenddate_input', 'multicache-config-menu', 'multicache_config_page_parta');
    add_settings_field('multicache_config_googlenumberurlscache', __('Number of urls', 'multicache-plugin'), 'multicache_config_setting_googlenumberurlscache_input', 'multicache-config-menu', 'multicache_config_page_parta');
    add_settings_field('multicache_config_multicachedistribution', __('Mode', 'multicache-plugin'), 'multicache_config_setting_multicachedistribution_input', 'multicache-config-menu', 'multicache_config_page_parta');
    
    add_settings_field('multicache_config_urlfilters', __('Url filters', 'multicache-plugin'), 'multicache_config_setting_urlfilters_input', 'multicache-config-menu', 'multicache_config_page_parta_part2');
    add_settings_field('multicache_config_frequency_distribution', __('f_distribution', 'multicache-plugin'), 'multicache_config_setting_frequency_distribution_input', 'multicache-config-menu', 'multicache_config_page_parta_part2');
    add_settings_field('multicache_config_natlogdist', __('natural logarithm', 'multicache-plugin'), 'multicache_config_setting_natlogdist_input', 'multicache-config-menu', 'multicache_config_page_parta_part2');
    
    add_settings_field('multicache_config_cartmode', __('Apply Cart Setting to', 'multicache-plugin'), 'multicache_config_setting_cartmode_input', 'multicache-config-menu', 'multicache_config_page_parta_part2_xtd');
    add_settings_field('multicache_config_cartmodeurlinclude', __('Specify Pages', 'multicache-plugin'), 'multicache_config_setting_cartmodeurlinclude_input', 'multicache-config-menu', 'multicache_config_page_parta_part2_xtd');
    add_settings_field('multicache_config_countryseg', __('Segregate By Country', 'multicache-plugin'), 'multicache_config_setting_countryseg_input', 'multicache-config-menu', 'multicache_config_page_parta_part2_xtd');
    
    // advanced
    add_settings_field('multicache_config_advanced_simulation_lock', __('Lock advanced test series', 'multicache-plugin'), 'multicache_config_setting_advanced_simulation_lock_input', 'multicache-config-menu', 'multicache_config_page_partb');
    add_settings_field('multicache_config_advanced_wp_cache', __('WP Advanced Cache', 'multicache-plugin'), 'multicache_config_setting_advanced_wp_cache_input', 'multicache-config-menu', 'multicache_config_page_partb');
    add_settings_field('multicache_config_additionalpagecacheurls', __('Additional Page Cache Urls', 'multicache-plugin'), 'multicache_config_setting_additionalpagecacheurls_input', 'multicache-config-menu', 'multicache_config_page_partb');
    add_settings_field('multicache_config_aforce_locking_off', __('Force Locking Off', 'multicache-plugin'), 'multicache_config_setting_force_locking_off_input', 'multicache-config-menu', 'multicache_config_page_partb');
    add_settings_field('multicache_config_conduit_switch', __('Conduit', 'multicache-plugin'), 'multicache_config_setting_conduit_switch_input', 'multicache-config-menu', 'multicache_config_page_partb');
    add_settings_field('multicache_config_conduit_nonce', __('Conduit Nonce Name', 'multicache-plugin'), 'multicache_config_setting_conduit_nonce_input', 'multicache-config-menu', 'multicache_config_page_partb');
    
    
    add_settings_field('multicache_config_minify_html', __('Minify Html', 'multicache-plugin'), 'multicache_config_setting_minify_html_input', 'multicache-config-menu', 'multicache_config_page_partb');
    add_settings_field('multicache_config_comment_cacheinvalidate', __('Comment Invalidation', 'multicache-plugin'), 'multicache_config_setting_comment_cacheinvalidation_input', 'multicache-config-menu', 'multicache_config_page_partb');
    //version1.0.0.2 positional_dontmovesrc and allow_multiple_orphaned
    //add_settings_field( $id, $title, $callback, $page, $section, $args ); 
    add_settings_field('multicache_config_positional_dontmovesrc', __('Positional Scripts', 'multicache-plugin'), 'multicache_config_setting_positional_dontmovesrc_input', 'multicache-config-menu', 'multicache_config_page_partb');
    add_settings_field('multicache_config_allow_multiple_orphaned', __('Allow multiple orphaned', 'multicache-plugin'), 'multicache_config_setting_allow_multiple_orphaned_input', 'multicache-config-menu', 'multicache_config_page_partb');
    add_settings_field('multicache_config_cache_user_loggedin', __('Cache Logged-in Users', 'multicache-plugin'), 'multicache_config_setting_cache_user_loggedin_input', 'multicache-config-menu', 'multicache_config_page_partb');
    add_settings_field('multicache_config_optimize_user_loggedin', __('Optimize for Logged-in Users', 'multicache-plugin'), 'multicache_config_setting_optimize_user_loggedin_input', 'multicache-config-menu', 'multicache_config_page_partb');
    add_settings_field('multicache_config_cache_query_urls', __('Cache Dynamic Urls', 'multicache-plugin'), 'multicache_config_setting_cache_query_urls_input', 'multicache-config-menu', 'multicache_config_page_partb');
    add_settings_field('multicache_config_optimize_query_urls', __('Optimize for Dynamic Urls', 'multicache-plugin'), 'multicache_config_setting_optimize_query_urls_input', 'multicache-config-menu', 'multicache_config_page_partb');
    
    add_settings_field('multicache_config_targetpageloadtime', __('Target Page Load Time', 'multicache-plugin'), 'multicache_config_setting_targetpageloadtime_input', 'multicache-config-menu', 'multicache_config_page_partb_extended');
    add_settings_field('multicache_config_algorithmavgloadtimeweight', __('Weightage Load Time', 'multicache-plugin'), 'multicache_config_setting_algorithmavgloadtimeweight_input', 'multicache-config-menu', 'multicache_config_page_partb_extended');
    add_settings_field('multicache_config_algorithmmodemaxbelowtimeweight', __('Weightage (mode)', 'multicache-plugin'), 'multicache_config_setting_algorithmmodemaxbelowtimeweight_input', 'multicache-config-menu', 'multicache_config_page_partb_extended');
    
    add_settings_field('multicache_config_algorithmvarianceweight', __('Weightage (variance)', 'multicache-plugin'), 'multicache_config_setting_algorithmvarianceweight_input', 'multicache-config-menu', 'multicache_config_page_partb_extended');
    add_settings_field('multicache_config_deployment_method', __('Deployment Method', 'multicache-plugin'), 'multicache_config_setting_deployment_method_input', 'multicache-config-menu', 'multicache_config_page_partb_extended');
    
    //tolerance settings
    add_settings_field('multicache_config_tolerance_switch', __('Highlight Tolerances', 'multicache-plugin'), 'multicache_config_setting_tolerance_switch_input', 'multicache-config-menu', 'multicache_config_page_partb_extended_tolerance');
    add_settings_field('multicache_config_tolerance_danger_color', __('Danger Color', 'multicache-plugin'), 'multicache_config_setting_tolerance_danger_color_input', 'multicache-config-menu', 'multicache_config_page_partb_extended_tolerance');
    add_settings_field('multicache_config_tolerance_warning_color', __('Warning Color', 'multicache-plugin'), 'multicache_config_setting_tolerance_warning_color_input', 'multicache-config-menu', 'multicache_config_page_partb_extended_tolerance');
    add_settings_field('multicache_config_tolerance_success_color', __('Success Color', 'multicache-plugin'), 'multicache_config_setting_tolerance_success_color_input', 'multicache-config-menu', 'multicache_config_page_partb_extended_tolerance');
    add_settings_field('multicache_config_tolerance_danger', __('Danger Tolerance', 'multicache-plugin'), 'multicache_config_setting_tolerance_danger_input', 'multicache-config-menu', 'multicache_config_page_partb_extended_tolerance');
    add_settings_field('multicache_config_tolerance_warning', __('Warning Tolerance', 'multicache-plugin'), 'multicache_config_setting_tolerance_warning_input', 'multicache-config-menu', 'multicache_config_page_partb_extended_tolerance');
    
    
    // Jstweaks Part 1
    add_settings_field('multicache_config_js_switch', __('Javascript Tweaks', 'multicache-plugin'), 'multicache_config_setting_js_switch_input', 'multicache-config-menu', 'multicache_config_page_js_tweaks');
    add_settings_field('multicache_config_default_scrape_url', __('Url to template', 'multicache-plugin'), 'multicache_config_setting_default_scrape_url_input', 'multicache-config-menu', 'multicache_config_page_js_tweaks');
    add_settings_field('multicache_config_principle_jquery_scope', __('Principal jQuery Scope', 'multicache-plugin'), 'multicache_config_principle_jquery_scope_method_input', 'multicache-config-menu', 'multicache_config_page_js_tweaks');
    add_settings_field('multicache_config_principle_jquery_scope_other', __('Custom jQuery Scope Variable', 'multicache-plugin'), 'multicache_config_setting_principle_jquery_scope_other_input', 'multicache-config-menu', 'multicache_config_page_js_tweaks');
    add_settings_field('multicache_config_dedupe_scripts', __('Remove Duplicate Scripts', 'multicache-plugin'), 'multicache_config_setting_dedupe_scripts_input', 'multicache-config-menu', 'multicache_config_page_js_tweaks');
    add_settings_field('multicache_config_defer_social', __('Defer Social', 'multicache-plugin'), 'multicache_config_setting_defer_social_input', 'multicache-config-menu', 'multicache_config_page_js_tweaks');
    add_settings_field('multicache_config_defer_advertisement', __('Defer Advertisement', 'multicache-plugin'), 'multicache_config_setting_defer_advertisement_input', 'multicache-config-menu', 'multicache_config_page_js_tweaks');
    add_settings_field('multicache_config_defer_async', __('Defer Async', 'multicache-plugin'), 'multicache_config_setting_defer_async_input', 'multicache-config-menu', 'multicache_config_page_js_tweaks');
    add_settings_field('multicache_config_maintain_preceedence', __('Maintain Preceedence', 'multicache-plugin'), 'multicache_config_setting_maintain_preceedence_input', 'multicache-config-menu', 'multicache_config_page_js_tweaks');
    add_settings_field('multicache_config_minimize_roundtrips', __('Minimize Roundtrips', 'multicache-plugin'), 'multicache_config_setting_minimize_roundtrips_input', 'multicache-config-menu', 'multicache_config_page_js_tweaks');
    
    // part 2
    add_settings_field('multicache_config_social_script_identifiers', __('Social Identifiers', 'multicache-plugin'), 'multicache_config_setting_social_script_identifiers_input', 'multicache-config-menu', 'multicache_config_page_js_tweaks_part2');
    add_settings_field('multicache_config_advertisement_script_identifiers', __('Advertisement Identifiers', 'multicache-plugin'), 'multicache_config_setting_advertisement_script_identifiers_input', 'multicache-config-menu', 'multicache_config_page_js_tweaks_part2');
    add_settings_field('multicache_config_pre_head_stub_identifiers', __('Head Tag Variants', 'multicache-plugin'), 'multicache_config_setting_pre_head_stub_identifiers_input', 'multicache-config-menu', 'multicache_config_page_js_tweaks_part2');
    add_settings_field('multicache_config_head_stub_identifiers', __('Closing Head Tag Variants', 'multicache-plugin'), 'multicache_config_setting_head_stub_identifiers_input', 'multicache-config-menu', 'multicache_config_page_js_tweaks_part2');
    add_settings_field('multicache_config_body_stub_identifiers', __('Body Tag Variants', 'multicache-plugin'), 'multicache_config_setting_body_stub_identifiers_input', 'multicache-config-menu', 'multicache_config_page_js_tweaks_part2');
    add_settings_field('multicache_config_footer_stub_identifiers', __('Closing Body Tag Variants', 'multicache-plugin'), 'multicache_config_setting_footer_stub_identifiers_input', 'multicache-config-menu', 'multicache_config_page_js_tweaks_part2');
    add_settings_field('multicache_config_js_comments', __('Include Comments', 'multicache-plugin'), 'multicache_config_setting_js_comments_input', 'multicache-config-menu', 'multicache_config_page_js_tweaks_part2');
    add_settings_field('multicache_config_compress_js', __('Minify js', 'multicache-plugin'), 'multicache_config_setting_compress_js_input', 'multicache-config-menu', 'multicache_config_page_js_tweaks_part2');
    add_settings_field('multicache_config_debug_mode', __('Debug Mode', 'multicache-plugin'), 'multicache_config_setting_debug_mode_input', 'multicache-config-menu', 'multicache_config_page_js_tweaks_part2');
    add_settings_field('multicache_config_orphaned_scripts', __('Orphaned Scripts', 'multicache-plugin'), 'multicache_config_setting_orphaned_scripts_input', 'multicache-config-menu', 'multicache_config_page_js_tweaks_part2');
    //ver1.0.02 add resultant async defers
    add_settings_field('multicache_config_resultant_async_js', __('Resultant Async', 'multicache-plugin'), 'multicache_config_setting_resultant_async_js_input', 'multicache-config-menu', 'multicache_config_page_js_tweaks_part2');
    add_settings_field('multicache_config_resultant_defer_js', __('Resultant Defer', 'multicache-plugin'), 'multicache_config_setting_resultant_defer_input', 'multicache-config-menu', 'multicache_config_page_js_tweaks_part2');
    // javascript exclusions
    add_settings_field('multicache_config_js_tweaker_url_include_exclude', __('Apply to urls', 'multicache-plugin'), 'multicache_config_setting_js_tweaker_url_include_exclude_input', 'multicache-config-menu', 'multicache_config_page_js_inclusion');
    add_settings_field('multicache_config_jst_urlinclude', __('Specify Pages', 'multicache-plugin'), 'multicache_config_setting_jst_urlinclude_input', 'multicache-config-menu', 'multicache_config_page_js_inclusion');
    add_settings_field('multicache_config_jst_query_include_exclude', __('Apply to query', 'multicache-plugin'), 'multicache_config_setting_jst_query_include_exclude_input', 'multicache-config-menu', 'multicache_config_page_js_inclusion');
    add_settings_field('multicache_config_jst_query_param', __('Query Params', 'multicache-plugin'), 'multicache_config_setting_jst_query_param_input', 'multicache-config-menu', 'multicache_config_page_js_inclusion');
    add_settings_field('multicache_config_jst_url_string', __('Url string exclude', 'multicache-plugin'), 'multicache_config_setting_jst_url_string_input', 'multicache-config-menu', 'multicache_config_page_js_inclusion');
    
    // css tweaks
    add_settings_field('multicache_config_css_switch', __('Css Switch', 'multicache-plugin'), 'multicache_config_setting_css_switch', 'multicache-config-menu', 'multicache_config_page_css_tweaks');
    add_settings_field('multicache_config_css_scrape_url', __('Scrape Url', 'multicache-plugin'), 'multicache_config_setting_css_scrape_url_input', 'multicache-config-menu', 'multicache_config_page_css_tweaks');
    add_settings_field('multicache_config_dedupe_css_styles', __('Dedupe Css links and styles', 'multicache-plugin'), 'multicache_config_setting_dedupe_css_styles_input', 'multicache-config-menu', 'multicache_config_page_css_tweaks');
    add_settings_field('multicache_config_css_maintain_preceedence', __('Maintain preceedence', 'multicache-plugin'), 'multicache_config_setting_css_maintain_preceedence_input', 'multicache-config-menu', 'multicache_config_page_css_tweaks');
    add_settings_field('multicache_config_group_css_styles', __('Group Css', 'multicache-plugin'), 'multicache_config_setting_group_css_styles_input', 'multicache-config-menu', 'multicache_config_page_css_tweaks');
    add_settings_field('multicache_config_compress_css', __('Minify Css', 'multicache-plugin'), 'multicache_config_setting_compress_css_input', 'multicache-config-menu', 'multicache_config_page_css_tweaks');
    add_settings_field('multicache_config_css_special_identifiers', __('Special css identifiers', 'multicache-plugin'), 'multicache_config_setting_css_special_identifiers_input', 'multicache-config-menu', 'multicache_config_page_css_tweaks');
    add_settings_field('multicache_config_css_comments', __('Include Comments', 'multicache-plugin'), 'multicache_config_setting_css_comments_input', 'multicache-config-menu', 'multicache_config_page_css_tweaks');
    add_settings_field('multicache_config_css_groupsasync', __('Groups async', 'multicache-plugin'), 'multicache_config_setting_css_groupsasync_input', 'multicache-config-menu', 'multicache_config_page_css_tweaks');
    add_settings_field('multicache_config_orphaned_styles_loading', __('Orphaned Styles', 'multicache-plugin'), 'multicache_config_setting_orphaned_styles_loading_input', 'multicache-config-menu', 'multicache_config_page_css_tweaks');
    // css excludions multicache_config_page_css_inclusion\
    add_settings_field('multicache_config_css_tweaker_url_include_exclude', __('Apply to urls', 'multicache-plugin'), 'multicache_config_setting_css_tweaker_url_include_exclude_input', 'multicache-config-menu', 'multicache_config_page_css_inclusion');
    add_settings_field('multicache_config_css_urlinclude', __('Specify Pages', 'multicache-plugin'), 'multicache_config_setting_css_urlinclude_input', 'multicache-config-menu', 'multicache_config_page_css_inclusion');
    add_settings_field('multicache_config_css_query_include_exclude', __('Apply to query', 'multicache-plugin'), 'multicache_config_setting_css_query_include_exclude_input', 'multicache-config-menu', 'multicache_config_page_css_inclusion');
    add_settings_field('multicache_config_css_query_param', __('Query Params', 'multicache-plugin'), 'multicache_config_setting_css_query_param_input', 'multicache-config-menu', 'multicache_config_page_css_inclusion');
    add_settings_field('multicache_config_css_url_string', __('Url string exclude', 'multicache-plugin'), 'multicache_config_setting_css_url_string_input', 'multicache-config-menu', 'multicache_config_page_css_inclusion');
    // lazyload multicache_config_page_image_tweaks
    add_settings_field('multicache_config_image_lazy_switch', __('Lazyload switch', 'multicache-plugin'), 'multicache_config_setting_image_lazy_switch_input', 'multicache-config-menu', 'multicache_config_page_image_tweaks');
    add_settings_field('multicache_config_image_lazy_image_selector_include_switch', __('Image Selectors', 'multicache-plugin'), 'multicache_config_setting_image_lazy_image_selector_include_switch', 'multicache-config-menu', 'multicache_config_page_image_tweaks');
    add_settings_field('multicache_config_image_lazy_image_selector_include_strings', __('Image Selectors', 'multicache-plugin'), 'multicache_config_setting_image_lazy_image_selector_include_strings_input', 'multicache-config-menu', 'multicache_config_page_image_tweaks');
    add_settings_field('multicache_config_image_lazy_image_selector_exclude_switch', __('Images Exclude', 'multicache-plugin'), 'multicache_config_setting_image_lazy_image_selector_exclude_switch_input', 'multicache-config-menu', 'multicache_config_page_image_tweaks');
    add_settings_field('multicache_config_image_lazy_image_selector_exclude_strings', __('Image Deselectors', 'multicache-plugin'), 'multicache_config_setting_image_lazy_image_selector_exclude_strings_input', 'multicache-config-menu', 'multicache_config_page_image_tweaks');
    // lazy load exclusions
    add_settings_field('multicache_config_imagestweaker_url_include_exclude', __('Apply to urls', 'multicache-plugin'), 'multicache_config_setting_imagestweaker_url_include_exclude_input', 'multicache-config-menu', 'multicache_config_page_image_tweaks_exclusions');
    add_settings_field('multicache_config_images_urlinclude', __('Specify Pages', 'multicache-plugin'), 'multicache_config_setting_images_urlinclude_input', 'multicache-config-menu', 'multicache_config_page_image_tweaks_exclusions');
    add_settings_field('multicache_config_images_query_include_exclude_strings', __('Apply to query', 'multicache-plugin'), 'multicache_config_setting_images_query_include_exclude_input', 'multicache-config-menu', 'multicache_config_page_image_tweaks_exclusions');
    add_settings_field('multicache_config_images_query_param', __('Query Params', 'multicache-plugin'), 'multicache_config_setting_images_query_param_input', 'multicache-config-menu', 'multicache_config_page_image_tweaks_exclusions');
    add_settings_field('multicache_config_images_url_string', __('Url string exclude', 'multicache-plugin'), 'multicache_config_setting_images_url_string_input', 'multicache-config-menu', 'multicache_config_page_image_tweaks_exclusions');

    /*
     * param1 - html id tag for the section,param 2 the text printed next to the field,param3 - the name of the callback that will echo the form field, param4 - the seeting pade on which to display, param5 - The section of the settings page in which to show the fi eld, as defi ned previously by the
     * add_settings_section() function call
     */
}


