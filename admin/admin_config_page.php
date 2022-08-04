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


$multicache_config_admin_pages = array(
		'counter' => 0,
		'page' => array(
				0 => 'page-settings',
				1 => 'page-optimisation',
				2 => 'page-parta',
				3 => 'page-partb',
				4 => 'page-js-tweaks',
				5 => 'page-js-inclusion',
				6 => 'page-css-tweaks',
				7 => 'page-css-inclusion',
				8 => 'page-image-tweaks'
		)
);

function multicache_config_menu()
{

$options = get_option('multicache_config_options');
$js_switch = $options['js_switch'];
$css_switch = $options['css_switch'];
	// adapted from config.html.php
	?>
<div class="wrap container-fluid">

	
<div id="submission_message"></div>

	<form id="multicache_config_form" action="options.php" method="post">
	<?php wp_nonce_field('multicache_plugin_save','multicache_form_control');?>
		<input type="submit"
			value="<?php  esc_attr_e('Save' , 'multicache-plugin');?>" name="save"
			class="button button-primary" />
			<input id="multicache_google_auth_button" type="submit" name="google_authenticate"
		value="<?php  esc_attr_e('googleAuthenticate' );?>"
		class="button button-primary"  />
		<?php if(!empty($js_switch)):?>
		<input id="multicache_scrape_template_button" type="submit" name="scrape_template"
		value="<?php esc_attr_e('Scrape Template');?>"
		class="button button-primary" /> 
		<?php endif;
		if(!empty($css_switch)):?>
		<input id="multicache_scrape_css_button" type="submit" name="scrape_css"
		value="<?php esc_attr_e('Scrape Css');?>"
		class="button button-primary"  />
		<?php endif;?>
		<div id="submission_message_inform"></div>
		<div id="tabs" class="form-horizontal">

			<ul class="nav nav-tabs" id="myTabTabs">

				<li class="active"><a href="#page-settings" data-toggle="tab">Settings</a></li>

				<li class=""><a href="#page-optimisation" data-toggle="tab">Optimization</a></li>

				<li class=""><a href="#page-parta" data-toggle="tab">Page Distribution</a></li>

				<li class=""><a href="#page-partb" data-toggle="tab">Advanced</a></li>

				<li class=""><a href="#page-js-tweaks" data-toggle="tab">Javascript Tweaks</a></li>

				<li class=""><a href="#page-js-inclusion" data-toggle="tab">Js Exclusions</a></li>

				<li class=""><a href="#page-css-tweaks" data-toggle="tab">Css Tweaks</a></li>

				<li class=""><a href="#page-css-inclusion" data-toggle="tab">Css Exclusions</a></li>

				<li class=""><a href="#page-image-tweaks" data-toggle="tab">Image Tweaks</a></li></ul>
			<!-- 			<div id="page-settings" class="tab-pane active"> -->
			<?php do_settings_sections('multicache-config-menu'); ?>
			<!-- moved to settings section<div id="page-settings" class="tab-pane active">-->
			<!-- start page-settings -->
				<?php settings_fields('multicache_config_options'); ?>

				<!-- end page-settings -->



		</div>
		<!-- closes form horizonatl div -->

	</form>

	<!--  test use cases bootstrap -->
	<!--
	<button type="button" class="btn btn-default">Default</button>
	<button type="button" class="btn btn-primary col-md-6">Primary</button>
	<button type="button" class="btn btn-success">Success</button>
	<button type="button" class="btn btn-info">Info</button>
	<button type="button" class="btn btn-warning">Warning</button>
	<button type="button" class="btn btn-danger">Danger</button>
	<button type="button" class="btn btn-link">Link</button>
	<span class="label label-default">Default</span> <span
		class="label label-primary">Primary</span> <span
		class="label label-success">Success</span> <span
		class="label label-info">Info</span> <span class="label label-warning">Warning</span>
	<span class="label label-danger">Danger</span>
	<h1>Badges</h1>
</div>
<p>
	<a href="#">Inbox <span class="badge">42</span></a>
</p>
<ul class="nav nav-pills" role="tablist">
	<li role="presentation" class="active"><a href="#">Home <span
			class="badge">42</span></a></li>
	<li role="presentation"><a href="#">Profile</a></li>
	<li role="presentation"><a href="#">Messages <span class="badge">3</span></a></li>
</ul>
<div class="page-header">
	<h1>Alerts</h1>
</div>
<div class="alert alert-success" role="alert">
	<strong>Well done!</strong> You successfully read this important alert
	message.
</div>
<div class="alert alert-info" role="alert">
	<strong>Heads up!</strong> This alert needs your attention, but it's
	not super important.
</div>
<div class="alert alert-warning" role="alert">
	<strong>Warning!</strong> Best check yo self, you're not looking too
	good.
</div>
<div class="alert alert-danger" role="alert">
	<strong>Oh snap!</strong> Change a few things up and try submitting
	again.
</div>-->
	<!-- end test use cases bootstrap -->
</div>
<!--closes main wrap-->
<?php

}

// stemming from register settings
//start replacement
function multicache_config_validate_options($input)
{
	//cron url negates all input
	$input["cron_url"] = plugins_url('simcontrol/simcron.php' , dirname(__FILE__));
	$input["redirecturi"] = admin_url('admin.php?page=multicache-config-menu&authg=2');
	
	$multicache_gzip = MulticacheHelper::validate_num($input["gzip"]);
	$input["gzip"] = ($multicache_gzip ==1 )? 1:0;
	// caching
	$caching = MulticacheHelper::validate_num($input["caching"]);
	$input["caching"] = ($caching ==1 )? 1:0;

	$cache_handler = MulticacheHelper::validate_text($input["cache_handler"]);
	$input["cache_handler"] = $cache_handler == 'fastcache'? 'fastcache':'file';
	//cachetime
	$cachetime = MulticacheHelper::validate_num($input["cachetime"]);
	if(empty($cachetime))
	{
		add_settings_error(
				'multicache_config_cache_time_text_string',
				'multicache_config_cache_time_error',
				'cache time error',
				'error'
		);
		$input["cachetime"] = 60;
	}
	else{
		$input["cachetime"] = $cachetime;
	}
	//persist
	$multicache_persist = MulticacheHelper::validate_num($input["multicache_persist"]);
	$input["multicache_persist"] = $multicache_persist == 0? 0:1;

	$multicache_compress = MulticacheHelper::validate_num($input["multicache_compress"]);
	$input["multicache_compress"] = $multicache_compress == 0? 0:1;


	$multicache_server_host = MulticacheHelper::validate_host($input["multicache_server_host"]);
	$input["multicache_server_host"] = empty($multicache_server_host)? 'localhost': $multicache_server_host;

	$multicache_server_port = MulticacheHelper::validate_num($input["multicache_server_port"]);
	$input["multicache_server_port"] = empty($multicache_server_port) ? '11211' : $multicache_server_port;

	//gtmetrix_testing
	$multicache_gtmetrix_testing = MulticacheHelper::validate_num($input["gtmetrix_testing"]);
	$input["gtmetrix_testing"] = $multicache_gtmetrix_testing == 1? 1:0;

	//gtmetrix_api_budget
	$multicache_gtmetrix_api_budget = MulticacheHelper::validate_num($input["gtmetrix_api_budget"]);
	$input["gtmetrix_api_budget"] = is_numeric($multicache_gtmetrix_api_budget)?$multicache_gtmetrix_api_budget:20;

	$multicache_gtmetrix_email = MulticacheHelper::validate_mail($input["gtmetrix_email"]);
	$input["gtmetrix_email"] = empty($multicache_gtmetrix_email)?'':$multicache_gtmetrix_email;

	$multicache_gtmetrix_token = MulticacheHelper::validate_numtext($input["gtmetrix_token"]);
	$input["gtmetrix_token"] = empty($multicache_gtmetrix_token)?'':$multicache_gtmetrix_token;

	$multicache_gtmetrix_adblock = MulticacheHelper::validate_num($input["gtmetrix_adblock"]);
	$input["gtmetrix_adblock"] = empty($multicache_gtmetrix_adblock)?0:1;

	$input["gtmetrix_test_url"] =  MulticacheHelper::validate_homeurl($input["gtmetrix_test_url"]);

	$multicache_gtmetrix_allow_simulation = MulticacheHelper::validate_num($input["gtmetrix_allow_simulation"]);
	$input["gtmetrix_allow_simulation"] = empty($multicache_gtmetrix_allow_simulation)?0:1;

	$multicache_jssimulation_parse = MulticacheHelper::validate_num($input["jssimulation_parse"]);
	$input["jssimulation_parse"] = empty($multicache_jssimulation_parse)?0:1;
	
	$multicache_simulation_advanced = MulticacheHelper::validate_num($input["simulation_advanced"]);
	$input["simulation_advanced"] = empty($multicache_simulation_advanced)?0:1;

	$multicache_gtmetrix_cycles = MulticacheHelper::validate_num($input["gtmetrix_cycles"]);
	$input["gtmetrix_cycles"] = is_numeric($multicache_gtmetrix_cycles) ? $multicache_gtmetrix_cycles:1;

	$multicache_precache_factor_min = MulticacheHelper::validate_num($input["precache_factor_min"]);
	$input["precache_factor_min"] = ($multicache_precache_factor_min > 9 || $multicache_precache_factor_min<0)? 0 :$multicache_precache_factor_min;

	$multicache_precache_factor_max = MulticacheHelper::validate_num($input["precache_factor_max"]);
	$input["precache_factor_max"] = ($multicache_precache_factor_max > 9 || $multicache_precache_factor_max < 0)? 9 :$multicache_precache_factor_max;

	if($multicache_precache_factor_max < $multicache_precache_factor_min)
	{
		add_settings_error(
				'multicache_config_max_less_than_min',
				'multicache_config_max_less_than_min',
				'precache max less than min',
				'error'
		);
		$input["precache_factor_max"] = $multicache_precache_factor_min;
	}


	$multicache_precache_factor_default = MulticacheHelper::validate_num($input["precache_factor_default"]);
	$input["precache_factor_default"] = ($multicache_precache_factor_default > 9 || $multicache_precache_factor_default < 0)? 2 :$multicache_precache_factor_default;


	$multicache_ccomp_factor_min =floatval(MulticacheHelper::validate_num($input["ccomp_factor_min"])) ;
	$input["ccomp_factor_min"] = ($multicache_ccomp_factor_min > 1 || $multicache_ccomp_factor_min < 0)? 0 :$multicache_ccomp_factor_min;

	$multicache_ccomp_factor_max =floatval(MulticacheHelper::validate_num($input["ccomp_factor_max"])) ;
	$input["ccomp_factor_max"] = ($multicache_ccomp_factor_max > 1 || $multicache_ccomp_factor_max < 0)? 1 :$multicache_ccomp_factor_max;

	if($multicache_ccomp_factor_max < $multicache_ccomp_factor_min)
	{
		add_settings_error(
				'multicache_config_ccomp_max_less_than_min',
				'multicache_config_ccomp_max_less_than_min',
				'Cache compression max less than min',
				'error'
		);
		$input["ccomp_factor_max"] = 1;
	}

	$multicache_ccomp_factor_step =floatval(MulticacheHelper::validate_num($input["ccomp_factor_step"])) ;
	$input["ccomp_factor_step"] = ($multicache_ccomp_factor_step>0.5 || $multicache_ccomp_factor_step<0)? 0.1 :$multicache_ccomp_factor_step;


	$multicache_ccomp_factor_default =floatval(MulticacheHelper::validate_num($input["ccomp_factor_default"])) ;
	$input["ccomp_factor_default"] = ($multicache_ccomp_factor_default > 1 || $multicache_ccomp_factor_default < 0)? 0.22 :$multicache_ccomp_factor_default;


	$multicache_googleclientid =MulticacheHelper::validate_google($input["googleclientid"] ,'clientid') ;
	$input["googleclientid"] = empty($multicache_googleclientid)? '' :$multicache_googleclientid;

	$multicache_googleclientsecret =MulticacheHelper::validate_google($input["googleclientsecret"],'clientsecret') ;
	$input["googleclientsecret"] = empty($multicache_googleclientsecret)? '' :$multicache_googleclientsecret;

	$multicache_googleviewid =MulticacheHelper::validate_google($input["googleviewid"] , 'viewid') ;
	$input["googleviewid"] = empty($multicache_googleviewid)? '' :$multicache_googleviewid;


	$multicache_googlestartdate =MulticacheHelper::validate_num($input["googlestartdate"]) ;
	$input["googlestartdate"] = empty($multicache_googlestartdate)? date('Y-m-d', strtotime('-1 year')) :$multicache_googlestartdate;

	$multicache_googleenddate =MulticacheHelper::validate_num($input["googleenddate"]) ;
	$input["googleenddate"] = empty($multicache_googleenddate)? date('Y-m-d') :$multicache_googleenddate;


	$multicache_googlenumberurlscache =MulticacheHelper::validate_num($input["googlenumberurlscache"]) ;
	$input["googlenumberurlscache"] = empty($multicache_googlenumberurlscache)? 200 :$multicache_googlenumberurlscache;


	$multicache_multicachedistribution =MulticacheHelper::validate_num($input["multicachedistribution"]) ;
	$input["multicachedistribution"] = ($multicache_multicachedistribution> 3)? 3 :$multicache_multicachedistribution;

	
	$input["urlfilters"] = MulticacheHelper::validate_num($input["urlfilters"]) ;
	 


	$multicache_frequency_distribution =MulticacheHelper::validate_num($input["frequency_distribution"]) ;
	$input["frequency_distribution"] = empty($multicache_frequency_distribution)? 0 :$multicache_frequency_distribution;

	$multicache_natlogdist =MulticacheHelper::validate_num($input["natlogdist"]) ;
	$input["natlogdist"] = empty($multicache_natlogdist)? 0 :$multicache_natlogdist;

	$multicache_advanced_simulation_lock =MulticacheHelper::validate_num($input["advanced_simulation_lock"]) ;
	$input["advanced_simulation_lock"] = empty($multicache_advanced_simulation_lock)? 0 :$multicache_advanced_simulation_lock;

	$multicache_additionalpagecacheurls =MulticacheHelper::validate_textbox($input["additionalpagecacheurls"]) ;
	$input["additionalpagecacheurls"] = performPageUrlChecks($multicache_additionalpagecacheurls);
	
	$multicache_force_locking_off = MulticacheHelper::validate_num($input["force_locking_off"]) ;
	$input["force_locking_off"] = $multicache_force_locking_off == 0? 0: 1;
	
	/*
	$multicache_indexhack = MulticacheHelper::validate_num($input["indexhack"]) ;
	$input["indexhack"] = $multicache_indexhack == 0? 0: 1;
	*/
	//wp_advanced_cache
	$multicache_indexhack = MulticacheHelper::validate_num($input["wp_advanced_cache"]) ;
	$input["indexhack"] = $multicache_indexhack == 0 ? 0: 1;
	
	$multicache_conduit_switch = MulticacheHelper::validate_num($input["conduit_switch"]) ;
	$input["conduit_switch"] = $multicache_conduit_switch == 0? 0: 1;
	
	
	
	$multicache_conduit_nonce = MulticacheHelper::validate_google($input["conduit_nonce_name"]) ;
	$input["conduit_nonce_name"] = empty($multicache_conduit_nonce)? 'sp-security-nonce' :$multicache_conduit_nonce;
	//were using sp-security-nonce of nice login as a role model here
	
	
	$multicache_minify_html = MulticacheHelper::validate_num($input["minify_html"]) ;
	$input["minify_html"] = $multicache_minify_html == 0? 0: 1;
	
	//version1.0.0.2 
	$input["positional_dontmovesrc_raw"] = MulticacheHelper::validate_textbox($input["positional_dontmovesrc"]) ;
	$multicache_positional_dontmovesrc =MulticacheHelper::validate_textbox($input["positional_dontmovesrc"]) ;
	$input["positional_dontmovesrc"] = json_encode(preg_split('/[\s,\n]+/', $multicache_positional_dontmovesrc));
	
	//version1.0.0.2
	$input["allow_multiple_orphaned_raw"] = MulticacheHelper::validate_textbox($input["allow_multiple_orphaned"]) ;
	$multicache_allow_multiple_orphaned =MulticacheHelper::validate_textbox($input["allow_multiple_orphaned"]) ;
	$input["allow_multiple_orphaned"] = json_encode(preg_split('/[\s,\n]+/', $multicache_allow_multiple_orphaned));
	
	
	$multicache_targetpageloadtime = MulticacheHelper::validate_num($input["targetpageloadtime"]) ;
	$input["targetpageloadtime"] = (int) $multicache_targetpageloadtime ;
	
	
	$multicache_algorithmavgloadtimeweight =(float) MulticacheHelper::validate_num($input["algorithmavgloadtimeweight"]) ;
	$input["algorithmavgloadtimeweight"] = (float) number_format($multicache_algorithmavgloadtimeweight, 2, '.', '');
	
	
	$multicache_algorithmmodemaxbelowtimeweight =(float) MulticacheHelper::validate_num($input["algorithmmodemaxbelowtimeweight"]) ;
	$input["algorithmmodemaxbelowtimeweight"] = (float) number_format($multicache_algorithmmodemaxbelowtimeweight, 2, '.', '');
	
	
	$multicache_algorithmvarianceweight =(float) MulticacheHelper::validate_num($input["algorithmvarianceweight"]) ;
	$input["algorithmvarianceweight"] = (float) number_format($multicache_algorithmvarianceweight, 2, '.', '');
	
	
	
	$multicache_frequency_distribution = MulticacheHelper::validate_num($input["frequency_distribution"]) ;
	$input["frequency_distribution"] = $multicache_frequency_distribution == 0? 0: 1;
	
	$multicache_natlogdist = MulticacheHelper::validate_num($input["natlogdist"]) ;
	$input["natlogdist"] = $multicache_natlogdist == 0? 0: 1;
	
	
	$multicache_deployment_method = MulticacheHelper::validate_num($input["deployment_method"]) ;
	$input["deployment_method"] = (int) $multicache_deployment_method ;
	
	//we will need to sort this when we integrate the cart from woocommerce
	$input["cartsessionvariables"] = MulticacheHelper::validate_cart($input["cartsessionvariables"]) ;
	$input["cartdifferentiators"] = MulticacheHelper::validate_cart($input["cartdifferentiators"]) ;
	
	//
	$multicache_cartmode = MulticacheHelper::validate_num($input["cartmode"]) ;
	$input["cartmode"] = (int) $multicache_cartmode ;
	
	//cart settings
	$input["cartmodeurlinclude_raw"] = MulticacheHelper::validate_textbox($input["cartmodeurlinclude"]) ;
	$multicache_cartmodeurlinclude =MulticacheHelper::validate_textbox($input["cartmodeurlinclude"]) ;
	$input["cartmodeurlinclude"] = json_encode(preg_split('/[\s,\n]+/', $multicache_cartmodeurlinclude));
	$input["cartmodeurlinclude"] = MulticacheHelper::makeWinSafeArray($input["cartmodeurlinclude"]);
	//end cart settings
	
	/*
	$multicache_cartmodeurlinclude =MulticacheHelper::validate_textbox($input["cartmodeurlinclude"]) ;
	$input["cartmodeurlinclude"] = json_encode(preg_split('/[\s,\n]+/', $multicache_cartmodeurlinclude));
	*/
	$multicache_countryseg = MulticacheHelper::validate_num($input["countryseg"]);
	$input["countryseg"] = $multicache_countryseg == 0? 0: 1;
	$multicache_js_switch =MulticacheHelper::validate_num($input["js_switch"]) ;
	$input["js_switch"] = $multicache_js_switch == 0? 0: 1;
	
	
	$input["default_scrape_url"] = MulticacheHelper::validate_homeurl($input["default_scrape_url"]);
	$input["advertisement_script_identifiers"] = MulticacheHelper::processSocialAdIndicators($input["advertisement_script_identifiers"]);
/*	
	$multicache_advertisement_script_identifiers =MulticacheHelper::validate_textbox($input["advertisement_script_identifiers"]) ;
	$input["advertisement_script_identifiers"] = json_encode(preg_split('/[\s,\n]+/', $multicache_advertisement_script_identifiers));
*/
	$input["social_script_identifiers"] = MulticacheHelper::processSocialAdIndicators($input["social_script_identifiers"]);
	/*
	$multicache_social_script_identifiers =MulticacheHelper::validate_textbox($input["social_script_identifiers"]) ;
	$input["social_script_identifiers"] = json_encode(preg_split('/[\s,\n]+/', $multicache_social_script_identifiers));
	*/
	
	
	
	$input["pre_head_stub_identifiers"] =MulticacheHelper::validateStubs($input["pre_head_stub_identifiers"]) ;
	//$input["pre_head_stub_identifiers"] = json_encode(preg_split('/[\s,\n]+/', $multicache_pre_head_stub_identifiers));

	$input["head_stub_identifiers"] =MulticacheHelper::validateStubs($input["head_stub_identifiers"]) ;
	//$input["head_stub_identifiers"] = json_encode(preg_split('/[\s,\n]+/', $multicache_head_stub_identifiers));

	$input["body_stub_identifiers"] =MulticacheHelper::validateStubs($input["body_stub_identifiers"]) ;
	//$input["body_stub_identifiers"] = json_encode(preg_split('/[\s,\n]+/', $multicache_body_stub_identifiers));
	
	
	$input["footer_stub_identifiers"] =MulticacheHelper::validateStubs($input["footer_stub_identifiers"]) ;
	//$input["footer_stub_identifiers"] = json_encode(preg_split('/[\s,\n]+/', $multicache_footer_stub_identifiers));
	
	
	$multicache_principle_jquery_scope = MulticacheHelper::validate_num($input["principle_jquery_scope"]) ;
	$input["principle_jquery_scope"] = (int) $multicache_principle_jquery_scope ;
	
	
	$multicache_principle_jquery_scope_other = MulticacheHelper::validate_numtext($input["principle_jquery_scope_other"]) ;
	$input["principle_jquery_scope_other"] = (string) $multicache_principle_jquery_scope_other ;
//dedupe_scripts
	$multicache_dedupe_scripts =MulticacheHelper::validate_num($input["dedupe_scripts"]) ;
	$input["dedupe_scripts"] = $multicache_dedupe_scripts == 0? 0: 1;
//defer_social
	$multicache_defer_social =MulticacheHelper::validate_num($input["defer_social"]) ;
	$input["defer_social"] = $multicache_defer_social == 0? 0: 1;
//defer_advertisement
	$multicache_defer_advertisement =MulticacheHelper::validate_num($input["defer_advertisement"]) ;
	$input["defer_advertisement"] = $multicache_defer_advertisement == 0? 0: 1;
//defer_async
	$multicache_defer_async =MulticacheHelper::validate_num($input["defer_async"]) ;
	$input["defer_async"] = $multicache_defer_async == 0? 0: 1;
//maintain_preceedence
	$multicache_maintain_preceedence =MulticacheHelper::validate_num($input["maintain_preceedence"]) ;
	$input["maintain_preceedence"] = $multicache_maintain_preceedence == 0? 0: 1;
//minimize_roundtrips
	$multicache_minimize_roundtrips =MulticacheHelper::validate_num($input["minimize_roundtrips"]) ;
	$input["minimize_roundtrips"] = $multicache_minimize_roundtrips == 0? 0: 1;
//js_comments
	$multicache_js_comments =MulticacheHelper::validate_num($input["js_comments"]) ;
	$input["js_comments"] = $multicache_js_comments == 0? 0: 1;
//compress_js
	$multicache_compress_js =MulticacheHelper::validate_num($input["compress_js"]) ;
	$input["compress_js"] = $multicache_compress_js == 0? 0: 1;
//debug_mode
	$multicache_debug_mode =MulticacheHelper::validate_num($input["debug_mode"]) ;
	$input["debug_mode"] = $multicache_debug_mode == 0? 0: 1;
//ver1.0.0.2 resultant async and defer
//resultant async 
	$multicache_resultant_async_js =MulticacheHelper::validate_num($input["resultant_async_js"]) ;
	$input["resultant_async_js"] = $multicache_resultant_async_js == 0? 0: 1;
//resultant defer
	$multicache_resultant_defer_js =MulticacheHelper::validate_num($input["resultant_defer_js"]) ;
	$input["resultant_defer_js"] = $multicache_resultant_defer_js == 0? 0: 1;
//advanced_simulation_lock
	$multicache_advanced_simulation_lock =MulticacheHelper::validate_num($input["advanced_simulation_lock"]) ;
	$input["advanced_simulation_lock"] = $multicache_advanced_simulation_lock == 0? 0: 1;
	//orphaned_scripts
	$multicache_orphaned_scripts = MulticacheHelper::validate_num($input["orphaned_scripts"]) ;
	$input["orphaned_scripts"] = (int) $multicache_orphaned_scripts ;
//js_tweaker_url_include_exclude

	$multicache_js_tweaker_url_include_exclude = MulticacheHelper::validate_num($input["js_tweaker_url_include_exclude"]) ;
	$input["js_tweaker_url_include_exclude"] = (int) $multicache_js_tweaker_url_include_exclude ;
//jst_urlinclude
	$input["jst_urlinclude_raw"] = MulticacheHelper::validate_textbox($input["jst_urlinclude"]) ;
	$multicache_jst_urlinclude =MulticacheHelper::validate_textbox($input["jst_urlinclude"]) ;
	$input["jst_urlinclude"] = json_encode(preg_split('/[\s,\n]+/', $multicache_jst_urlinclude));
	$input["jst_urlinclude"] = MulticacheHelper::makeWinSafeArray($input["jst_urlinclude"]);
	
//jst_query_include_exclude
	$multicache_jst_query_include_exclude = MulticacheHelper::validate_num($input["jst_query_include_exclude"]) ;
	$input["jst_query_include_exclude"] = (int) $multicache_jst_query_include_exclude ;

	$input["jst_query_param_raw"] = MulticacheHelper::validate_textbox($input["jst_query_param"]) ;
	$multicache_jst_query_param =MulticacheHelper::validate_textbox($input["jst_query_param"]) ;
	$input["jst_query_param"] = json_encode(preg_split('/[\s,\n]+/', $multicache_jst_query_param));
	$input["jst_query_param"] = MulticacheHelper::makeWinSafeArray($input["jst_query_param"]);
	//excluded_components
	$input["excluded_components"] = prepareMulticacheExcludedComponents($input["excluded_components"]);
	//jst_url_string
	$input["jst_url_string_raw"] = MulticacheHelper::validate_textbox($input["jst_url_string"]) ;
	$multicache_jst_url_string =MulticacheHelper::validate_textbox($input["jst_url_string"]) ;
	$input["jst_url_string"] = json_encode(preg_split('/[\s,\n]+/', $multicache_jst_url_string));
	$input["jst_url_string"] = MulticacheHelper::makeWinSafeArray($input["jst_url_string"]);
	//force_precache_off
	$multicache_force_precache_off =MulticacheHelper::validate_num($input["force_precache_off"]) ;
	$input["force_precache_off"] = $multicache_force_precache_off == 0? 0: 1;
//css_switch
	$multicache_css_switch =MulticacheHelper::validate_num($input["css_switch"]) ;
	$input["css_switch"] = $multicache_css_switch == 0 ? 0: 1;
	
	$input["css_scrape_url"] = MulticacheHelper::validate_homeurl($input["css_scrape_url"]);
	
	//dedupe_css_styles
	$multicache_dedupe_css_styles =MulticacheHelper::validate_num($input["dedupe_css_styles"]) ;
	$input["dedupe_css_styles"] = $multicache_dedupe_css_styles == 0 ? 0: 1;
	//css_maintain_preceedence
	$multicache_css_maintain_preceedence =MulticacheHelper::validate_num($input["css_maintain_preceedence"]) ;
	$input["css_maintain_preceedence"] = $multicache_css_maintain_preceedence == 0 ? 0: 1;
	//group_css_styles
	$multicache_group_css_styles =MulticacheHelper::validate_num($input["group_css_styles"]) ;
	$input["group_css_styles"] = $multicache_group_css_styles == 0 ? 0: 1;
	//compress_css
	$multicache_compress_css =MulticacheHelper::validate_num($input["compress_css"]) ;
	$input["compress_css"] = $multicache_compress_css == 0 ? 0: 1;
	
	//css_special_identifiers
	$multicache_css_special_identifiers =MulticacheHelper::validate_textbox($input["css_special_identifiers"]) ;
	$input["css_special_identifiers"] = json_encode(preg_split('/[\s,\n]+/', $multicache_css_special_identifiers));
	$input["css_special_identifiers"] = MulticacheHelper::makeWinSafeArray($input["css_special_identifiers"]);
	
	//css_comments
	$multicache_css_comments =MulticacheHelper::validate_num($input["css_comments"]) ;
	$input["css_comments"] = $multicache_css_comments == 0 ? 0: 1;
	//css_groupsasync
	$multicache_css_groupsasync =MulticacheHelper::validate_num($input["css_groupsasync"]) ;
	$input["css_groupsasync"] = $multicache_css_groupsasync == 0 ? 0: 1;
	//orphaned_styles_loading
	$multicache_orphaned_styles_loading = MulticacheHelper::validate_num($input["orphaned_styles_loading"]) ;
	$input["orphaned_styles_loading"] = (int) $multicache_orphaned_styles_loading ;
	
	//css_tweaker_url_include_exclude
	$multicache_css_tweaker_url_include_exclude = MulticacheHelper::validate_num($input["css_tweaker_url_include_exclude"]) ;
	$input["css_tweaker_url_include_exclude"] = (int) $multicache_css_tweaker_url_include_exclude ;
	
	//css_urlinclude
	$input["css_urlinclude_raw"] = MulticacheHelper::validate_textbox($input["css_urlinclude"]) ;
	$multicache_css_urlinclude =MulticacheHelper::validate_textbox($input["css_urlinclude"]) ;
	$input["css_urlinclude"] = json_encode(preg_split('/[\s,\n]+/', $multicache_css_urlinclude));
	$input["css_urlinclude"] = MulticacheHelper::makeWinSafeArray($input["css_urlinclude"]);
	//css_query_include_exclude
	$multicache_css_query_include_exclude = MulticacheHelper::validate_num($input["css_query_include_exclude"]) ;
	$input["css_query_include_exclude"] = (int) $multicache_css_query_include_exclude ;
	
	//css_query_param
	$input["css_query_param_raw"] = MulticacheHelper::validate_textbox($input["css_query_param"]) ;
	$multicache_css_query_param =MulticacheHelper::validate_textbox($input["css_query_param"]) ;
	$input["css_query_param"] = json_encode(preg_split('/[\s,\n]+/', $multicache_css_query_param));
	$input["css_query_param"] = MulticacheHelper::makeWinSafeArray($input["css_query_param"]);
	//cssexcluded_components
	$input["cssexcluded_components"] = prepareMulticacheExcludedComponents($input["cssexcluded_components"]);
	
	//css_url_string
	$input["css_url_string_raw"] = MulticacheHelper::validate_textbox($input["css_url_string"]) ;
	$multicache_css_url_string =MulticacheHelper::validate_textbox($input["css_url_string"]) ;
	$input["css_url_string"] = json_encode(preg_split('/[\s,\n]+/', $multicache_css_url_string));
	$input["css_url_string"] = MulticacheHelper::makeWinSafeArray($input["css_url_string"]);
	
	//image_lazy_switch
	$multicache_image_lazy_switch =MulticacheHelper::validate_num($input["image_lazy_switch"]) ;
	$input["image_lazy_switch"] = $multicache_image_lazy_switch == 0 ? 0: 1;
	
	//image_lazy_container_strings
	//image_lazy_container_switch - remove this appears to be part of the phpquery construct
	$input["image_lazy_container_switch"] = null;//$multicache_image_lazy_container_switch == 0 ? 0: 1;
	$input["image_lazy_container_strings"] = null;
	
	//image_lazy_image_selector_include_switch
	//image selector switch
	$multicache_image_lazy_image_selector_include_switch =MulticacheHelper::validate_num($input["image_lazy_image_selector_include_switch"]) ;
	$input["image_lazy_image_selector_include_switch"] = $multicache_image_lazy_image_selector_include_switch == 0 ? 0: 1;
	
	//image_lazy_image_selector_include_strings
	$input["image_lazy_image_selector_include_strings_raw"] = MulticacheHelper::validate_textbox($input["image_lazy_image_selector_include_strings"]) ;
	$multicache_image_lazy_image_selector_include_strings =MulticacheHelper::validate_textbox($input["image_lazy_image_selector_include_strings"]) ;
	$input["image_lazy_image_selector_include_strings"] = json_encode(preg_split('/[\s,\n]+/', $multicache_image_lazy_image_selector_include_strings));
	$input["image_lazy_image_selector_include_strings"] = MulticacheHelper::makeWinSafeArray($input["image_lazy_image_selector_include_strings"]);
	
	//image_lazy_image_selector_exclude_switch
	$multicache_image_lazy_image_selector_exclude_switch =MulticacheHelper::validate_num($input["image_lazy_image_selector_exclude_switch"]) ;
	$input["image_lazy_image_selector_exclude_switch"] = $multicache_image_lazy_image_selector_exclude_switch == 0 ? 0: 1;
	
	//image_lazy_image_selector_exclude_strings
	$input["image_lazy_image_selector_exclude_strings_raw"]= MulticacheHelper::validate_textbox($input["image_lazy_image_selector_exclude_strings"]) ;
	$multicache_image_lazy_image_selector_exclude_strings = MulticacheHelper::validate_textbox($input["image_lazy_image_selector_exclude_strings"]) ;
	$input["image_lazy_image_selector_exclude_strings"] = json_encode(preg_split('/[\s,\n]+/', $multicache_image_lazy_image_selector_exclude_strings));
	$input["image_lazy_image_selector_exclude_strings"] = MulticacheHelper::makeWinSafeArray($input["image_lazy_image_selector_exclude_strings"]);
	//imagestweaker_url_include_exclude
	$multicache_imagestweaker_url_include_exclude = MulticacheHelper::validate_num($input["imagestweaker_url_include_exclude"]) ;
	$input["imagestweaker_url_include_exclude"] = (int) $multicache_imagestweaker_url_include_exclude ;
	
	//images_urlinclude
	$input["images_urlinclude_raw"] = MulticacheHelper::validate_textbox($input["images_urlinclude"]) ;
	$multicache_images_urlinclude = MulticacheHelper::validate_textbox($input["images_urlinclude"]) ;
	$input["images_urlinclude"] = json_encode(preg_split('/[\s,\n]+/', $multicache_images_urlinclude));
	$input["images_urlinclude"] = MulticacheHelper::makeWinSafeArray($input["images_urlinclude"]);
	
	//images_query_include_exclude
	$multicache_images_query_include_exclude =MulticacheHelper::validate_num($input["images_query_include_exclude"]) ;
	$input["images_query_include_exclude"] = (int) $multicache_images_query_include_exclude;
	
	//images_query_param
	$input["images_query_param_raw"] = MulticacheHelper::validate_textbox($input["images_query_param"]) ;
	$multicache_images_query_param = MulticacheHelper::validate_textbox($input["images_query_param"]) ;
	$input["images_query_param"] = json_encode(preg_split('/[\s,\n]+/', $multicache_images_query_param));
	$input["images_query_param"] = MulticacheHelper::makeWinSafeArray($input["images_query_param"]);
	
	//images_url_string
	$input["images_url_string_raw"] = MulticacheHelper::validate_textbox($input["images_url_string"]) ;
	$multicache_images_url_string = MulticacheHelper::validate_textbox($input["images_url_string"]) ;
	$input["images_url_string"] = json_encode(preg_split('/[\s,\n]+/', $multicache_images_url_string));
	$input["images_url_string"] = MulticacheHelper::makeWinSafeArray($input["images_url_string"]);
	
	//imgexcluded_components
	$input["imgexcluded_components"] = prepareMulticacheExcludedComponents($input["imgexcluded_components"]);
	//lets place strategy here 
	$strategy = MulticacheFactory::getStrategy();
	$result = $strategy->initialise($input);
	$input = !empty($result) && is_array($result)? $result : $input;//we do not want to corrupt input incase of return false
	//we'll use this point to handle situations that arise

	//end strategy
	//unset raw
	foreach($input As $key => $val)
	{
		if(substr($key , -4 , 4 ) === '_raw')
		{
		unset($input[$key]);
		}
	}
	
	//place tolerance options
	$multicacheconfigtolerance = $input['multicacheconfigtolerance'];
	$tolerance_params = new stdClass();
	$tolerance_params->tolerance_highlighting = !empty($multicacheconfigtolerance['tolerance_switch']) ? 1 : 0;
	$tolerance_params->danger_tolerance_factor = isset($multicacheconfigtolerance['danger_tolerance_factor']) ? MulticacheHelper::validate_num( $multicacheconfigtolerance['danger_tolerance_factor']) :3;
	$tolerance_params->danger_tolerance_color = isset($multicacheconfigtolerance['danger_tolerance_color']) ? MulticacheHelper::validate_numtext($multicacheconfigtolerance['danger_tolerance_color'] )    :'#a94442';
	$tolerance_params->warning_tolerance_factor = isset($multicacheconfigtolerance['warning_tolerance_factor']) ? MulticacheHelper::validate_num($multicacheconfigtolerance['warning_tolerance_factor']) : 2.5;
	$tolerance_params->warning_tolerance_color = isset($multicacheconfigtolerance['warning_tolerance_color']) ? MulticacheHelper::validate_numtext($multicacheconfigtolerance['warning_tolerance_color']) : '#8a6d3b';
	$tolerance_params->success_tolerance_color = isset($multicacheconfigtolerance['success_tolerance_color']) ? MulticacheHelper::validate_numtext($multicacheconfigtolerance['success_tolerance_color']) : '#468847';
	$input['tolerance_params'] = json_encode($tolerance_params);
	unset($input['multicacheconfigtolerance']);
	
	

	Return $input;

}
function prepareMulticacheExcludedComponents($input)
{
	
	if(empty($input) || !is_array($input))
	{
		Return null;
	}
	$excluded_components = array();
	foreach($input As $plugin => $status)
	{
		if(!empty($status))
		{
			$excluded_components[$plugin] = $status;
		}
	}
	Return serialize($excluded_components);
	
}


//__('Please enter a valid duration','multicache_plugin')
/*
function multicache_validate_text($input)
{
	Return preg_replace('~[^a-zA-Z]~','',$input);
}

function multicache_validate_numtext($input)
{
	Return preg_replace('~[^a-zA-Z0-9]~','',$input);
}

function multicache_validate_textbox($input)
{
	 $a = preg_replace('~[^a-zA-Z0-9\s\:\/\?\:\#\.\'\"\\n<>\=]~','',$input);
	 //replace < if not <head <body or </head or </body
	$a = preg_replace('~<(?!(head|body|/head|/body))[^>]*>~','',$a); 
	
	 
	 Return $a;
}

function multicache_validate_google($input ,$type = 'default')
{
	$a = preg_replace('~[^a-zA-Z0-9\.-_\:]~','',$input);
	if($a != $input)
	{
		//register the error
		add_settings_error(
				'multicache_config_'.$type,
				'multicache_config_'.$type,
				'Error in Google input '.$type,
				'error'
		);
	}
	Return $a;
}

function multicache_validate_host($input)
{
	Return preg_replace('~[^a-zA-Z0-9\.\:\/\?\#_-]~','',$input);
}

function multicache_validate_mail($input)
{
	Return preg_replace('~[^a-zA-Z0-9\.\:\/\?\#\@_\+-]~','',$input);
}

function multicache_validate_num($input)
{
	Return preg_replace('~[^0-9\.-]~','',$input);
}

function multicache_validate_cart($input)
{
	Return preg_replace('~[^a-zA-Z\$\s\;\\n_-]~','',$input);
}

function multicache_validate_homeurl($u)
{
	$u = preg_replace('~[^a-zA-Z0-9\.\:\/\?\#_-]~','',$u);//\:\/\?\#_-\.
	$url = MulticacheUri::root();
	$search= array('http://','https://','www.');
	$u_normalized = str_replace($search,'',$u);
	$url_normalized = str_replace($search,'',$url);
	if(stripos($u_normalized,$url_normalized) ===false)
	{
		Return $url;
	}
	else {
		Return $u;
	}
}
*/
//stop replacement
function  performPageUrlChecks($multicache_additionalpagecacheurls)
{
	if(is_multisite())
	{
		//$ms_obj = MulticacheFactory::getResolvedMultiSiteObj();
		$ms_obj = MulticacheHelper::resolveMultiSiteStrategyloc();
		$base_url = $ms_obj->live_site_stripped;
		
	}
	else {
	$base_url = strtolower(str_ireplace(array(
			'http://',
			'https://',
			'www.'
	), '', untrailingslashit(MulticacheUri::root())));
	}
	$url_array = preg_split('/[\s,\n]+/', $multicache_additionalpagecacheurls);
	foreach ($url_array as $key => $url)
	{
		//setting a prefernce for google data over manual here
		$exists = multicache_checkUrldburlArray($url, 'google');
		if ($exists ||  stripos($url, $base_url) === false)
		{
			unset($url_array[$key]);
		}
	}
	clearMulticacheTable();
	$url_string = json_encode($url_array);
	foreach ($url_array as $key => $url)
	{
		$exists = multicache_checkUrldburlArray($url, 'manual');
	
		if (! $exists && stripos($url, $base_url) !== false)
		{
	
			multicacheStoreUrlArray($url);
		}
	}
	Return $url_string;
}

function multicacheStoreUrlArray($useg)
{
	
	global $wpdb;
	$cache_id_array = getMulticacheCache_id($useg);
	$tbl = $wpdb->prefix.'multicache_urlarray';
	$data = array('url' => $useg, 'cache_id' => $cache_id_array['original'] , 'cache_id_alt' =>$cache_id_array['alternate'],type=>'manual','created' => date('Y-m-d'));
	$format = array('%s','%s','%s','%s','%s');
	$wpdb->insert($tbl,$data,$format);
	
	
	if($wpdb->insert_id === false ){
		error_log('Error storing urlarray - admin_config_page');
		//register the error
		add_settings_error(
				'multicache_config_store_urlarray_manual',
				'multicache_config_store_urlarray_manual',
				'Error storing url array ',
				'error'
		);
	}
	Return;
	
}
 function getMulticacheCache_id($url, $group = 'page')
{
	require_once dirname(plugin_dir_path(__FILE__)) . '/libs/multicache_storage_temp.php';
	$obj = new MulticacheStoragetemp();
	$cache_id = $obj->getCacheidAlternate($url, $group);
	Return $cache_id;

}
 function clearMulticacheTable($tbl = 'multicache_urlarray', $type = 'manual')
{
	global $wpdb;
	$tbl = $wpdb->prefix . $tbl;
	$wpdb->delete( $tbl, array('type'=>$type ), array('%s'));
}

function multicache_checkUrldburlArray($u, $type = 'google')
{
global $wpdb;

$query = "SELECT id FROM wp_multicache_urlarray WHERE url = '$u' AND type = '$type'";
$result = $wpdb->get_var($query);
	return (bool) $result;

}


function multicache_begin_group()
{

	global $multicache_config_admin_pages;
	$page_number = $multicache_config_admin_pages['counter'];
	?>
<!-- <?php echo "inserted by group begin ".$multicache_config_admin_pages['page'][$page_number]?> -->
<div
	id="<?php echo $multicache_config_admin_pages['page'][$page_number];?>"
	class="tab-pane active">
    <?php
    if (++ $multicache_config_admin_pages['counter'] > 8)
    {
        $multicache_config_admin_pages['counter'] = 0;
    }

}

function multicache_end_group()
{

    ?>
    </div>
<!-- <?php echo "end group begin "?> -->
<?php

}
function multicache_config_page_settings_section_html()
{
    
    // echo '<p> Enter your settings here.stems from add settings section </p> ';
}

function multicache_config_page_optimisation_section_html()
{

}

function multicache_config_page_optimisation_conditionsandparams_section_html()
{

}

function multicache_config_page_parta_section_html()
{

}

function multicache_config_page_parta__part2_xtd_section_html()
{
	
}

function multicache_config_page_parta_part2_section_html()
{

}

function multicache_config_page_partb_section_html()
{

}

function multicache_config_page_partb_extended_section_html()
{

}



function multicache_config_page_js_tweaks_section_html()
{

}

function multicache_config_page_js_tweaks_part2_section_html()
{

}

function multicache_config_page_partb_tolerance_section_html()
{
	
}

function multicache_config_setting_advanced_wp_cache_input()
{
	$options = get_option('multicache_config_options');
	$multicache_wp_advanced_cache = isset($options['indexhack']) ? $options['indexhack'] : 0;
	echo makeRadioButton('multicache_config_options', 'wp_advanced_cache', $multicache_wp_advanced_cache);
	$info_tag = __('Choose to activate WP advanced Cache. Pls place WP_CACHE = true in config.php to fully activate', 'multicache-plugin');
	?><span class="glyphicon glyphicon-info-sign"
		title="<?php echo $info_tag;?>"> </span>
	<?php
}

function multicache_config_setting_tolerance_switch_input()
{
	$tolerance_options = MulticacheHelper::getTolerances();
	$tolerance_switch = !empty($tolerance_options) ? $tolerance_options->tolerance_highlighting : 0;
	echo makeRadioButton('multicache_config_options[multicacheconfigtolerance]', 'tolerance_switch', $tolerance_switch );
	$info_tag = __('Choose to use colors to highlight tolerances of advanced sim results', 'multicache-plugin');
	?><span class="glyphicon glyphicon-info-sign"
		title="<?php echo $info_tag;?>"> </span>
	<?php
		
}

function multicache_config_setting_tolerance_danger_input()
{
	$tolerance_options = MulticacheHelper::getTolerances();
	$danger_tolerance_factor = !empty($tolerance_options) ? $tolerance_options->danger_tolerance_factor : 3;
	$title_tag = __('Highlights failed tests', 'multicache-plugin');
	$info_tag = __('Highlights failed tests. Set as a multiple of page load time.', 'multicache-plugin');
	echo makeInputButton('multicache_config_options[multicacheconfigtolerance]', 'danger_tolerance_factor', $danger_tolerance_factor, true, $title_tag , 'required  multicache-inputbox col-md-6');
	?>
	
	<span class="glyphicon glyphicon-info-sign"
		title="<?php echo $info_tag;?>"> </span>
	<?php
		
}
function multicache_config_setting_tolerance_danger_color_input()
{
	$tolerance_options = MulticacheHelper::getTolerances();
	$danger_tolerance_color = !empty($tolerance_options) ? $tolerance_options->danger_tolerance_color : '#a94442';
	$title_tag = __('Choose the color to highlight tests that fall into the danger tolerance', 'multicache-plugin');
	$info_tag = __('Choose the color to highlight tests that fall into the danger tolerance.', 'multicache-plugin');
	echo makeColorInput('multicache_config_options[multicacheconfigtolerance]' , 'danger_tolerance_color' , $danger_tolerance_color  , $title_tag  );
	?>
		
		<span class="glyphicon glyphicon-info-sign"
			title="<?php echo $info_tag;?>"> </span>
		<?php
}
function multicache_config_setting_tolerance_warning_input()
{
	$tolerance_options = MulticacheHelper::getTolerances();
	$warning_tolerance_factor = !empty($tolerance_options) ? $tolerance_options->warning_tolerance_factor : 2.5;
	$title_tag = __('Highlight tests whose loadtimes are out of tolerance but fall before danger', 'multicache-plugin');
	$info_tag = __('Highlight tests whose loadtimes are out of tolerance but fall before danger.', 'multicache-plugin');
	echo makeInputButton('multicache_config_options[multicacheconfigtolerance]', 'warning_tolerance_factor', $warning_tolerance_factor, true, $title_tag , 'required  multicache-inputbox col-md-6');
	?>
		
		<span class="glyphicon glyphicon-info-sign"
			title="<?php echo $info_tag;?>"> </span>
		<?php
}
function multicache_config_setting_tolerance_warning_color_input()
{
	$tolerance_options = MulticacheHelper::getTolerances();
	$warning_tolerance_color = !empty($tolerance_options) ? $tolerance_options->warning_tolerance_color : '#8a6d3b';
	$title_tag = __('Pick the color to highlight tests that fall into the warning tolerance', 'multicache-plugin');
	$info_tag = __('Pick the color to highlight tests that fall into the warning tolerance.', 'multicache-plugin');
	echo makeColorInput('multicache_config_options[multicacheconfigtolerance]' , 'warning_tolerance_color' , $warning_tolerance_color  , $title_tag  );
	?>
			
			<span class="glyphicon glyphicon-info-sign"
				title="<?php echo $info_tag;?>"> </span>
			<?php
}

function multicache_config_setting_tolerance_success_color_input()
{
	$tolerance_options = MulticacheHelper::getTolerances();
	$success_tolerance_color = !empty($tolerance_options) ? $tolerance_options->success_tolerance_color : '#468847';
	$title_tag = __('Pick the color to highlight tests that fall into the warning tolerance', 'multicache-plugin');
	$info_tag = __('Pick the color to highlight tests that fall into the warning tolerance.', 'multicache-plugin');
	echo makeColorInput('multicache_config_options[multicacheconfigtolerance]' , 'success_tolerance_color' , $success_tolerance_color  , $title_tag  );
	?>
				
				<span class="glyphicon glyphicon-info-sign"
					title="<?php echo $info_tag;?>"> </span>
				<?php
	
}

function multicache_config_setting_cartmode_input()
{

	$options = get_option('multicache_config_options');
	$multicache_cartmode = $options['cartmode'];
	$title_tag = __('Use this if only a few pages display cart and forms.', 'multicache-plugin');
	$info_tag = __('Use this if only a few pages display cart and forms.', 'multicache-plugin');
	echo makeMultiRadioButton('multicache_config_options', 'cartmode', $multicache_cartmode, array(
			0 => 'All Pages',
			1 => 'These Pages',
			2 => 'Not these pages'
	), 3);
	?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php
	

}

function multicache_config_setting_cartmodeurlinclude_input()
{


	$options = get_option('multicache_config_options');
	$multicache_cartmodeurlinclude = $options['cartmodeurlinclude'];
	$multicache_cartmodeurlinclude =MulticacheHelper::decodePrepareMulticacheTextAreas($multicache_cartmodeurlinclude);

	$title_tag = __('Specify the page url http://yoursite.com/somepage.html', 'multicache-plugin');
	$info_tag = __('Specify the page url http://www.yoursite.com/somepage.html', 'multicache-plugin');
	echo makeTextButton('multicache_config_options', 'cartmodeurlinclude', $multicache_cartmodeurlinclude);
	?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_countryseg_input()
{

	$options = get_option('multicache_config_options');
	$multicache_countryseg = isset($options['countryseg']) ? $options['countryseg'] : '0';
	echo makeRadioButton('multicache_config_options', 'countryseg', $multicache_countryseg);
	$info_tag = __('Choose to segregate by country', 'multicache-plugin');
	?><span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}



function multicache_config_page_js_tweaks_script_tweaks_section_html()
{
  $script_tweaks = MulticacheHelper::renderViewConfigPageScripts();
  if(!empty($script_tweaks))
  {
  	echo '<h3>'. __('Individual Script Tweaks' , 'multicache-plugin').'</h3>';
  	echo $script_tweaks;
  }
}
function multicache_config_page_css_tweaks_script_tweaks_section_html()
{
	$css_tweaks = MulticacheHelper::renderViewConfigPageCss();
	if(!empty($css_tweaks))
	{
		echo '<h3>'. __('Individual Css Tweaks' , 'multicache-plugin').'</h3>';
		echo $css_tweaks;
	}
}

function multicache_config_page_js_inclusion_section_html()
{

}

function multicache_config_page_css_tweaks_section_html()
{

}

function multicache_config_page_css_inclusion_section_html()
{

}

function multicache_config_page_image_tweaks_section_html()
{

}
// sub function for lazyload exclusions
function multicache_config_page_image_tweaks_exclusions_section_html()
{

}

// stems from add settings field
// Display and fill the form field
function multicache_config_setting_caching_input()
{
    
    // get option 'text_string' value from the database
    $options = get_option('multicache_config_options');
    
    $caching = $options['caching'];
    
    $title_tag = __('Set Caching On/off', 'multicache-plugin');
    $info_tag = __('Set Caching On/off. Turning this option on will enable page caching on your website', 'multicache-plugin');
    
    echo makeSelectButton('multicache_config_options', 'caching', $caching, array(
        0 => array(
            'key' => 0,
            'val' => 'Off'
        ),
        1 => array(
            'key' => 1,
            'val' => 'On'
        )
    ), true, $title_tag);
    
    ?>


<span class="glyphicon glyphicon-info-sign"
	title="<?php echo  __('Set Caching On/off','multicache-plugin');?>"> </span>

<?php

}

function multicache_config_setting_cache_handler_input()
{
    // get option 'text_string' value from the database
    $options = get_option('multicache_config_options');
    $cache_handler = $options['cache_handler'];
    $title_tag = __('Choose a cache handler', 'multicache-plugin');
    $info_tag = __('Choose a cache handler.Choosing file will save cached pages to files.If you decide to choose fastcache please ensure that memcache is already installed', 'multicache-plugin');
    
    echo makeSelectButton('multicache_config_options', 'cache_handler', $cache_handler, array(
        0 => array(
            'key' => 'file',
            'val' => 'file'
        ),
        1 => array(
            'key' => 'fastcache',
            'val' => 'fastcache'
        )
    ), true, $title_tag);
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_cache_time_input()
{

    $options = get_option('multicache_config_options');
    $cache_duration = $options['cachetime'];
    $title_tag = __('Set cache expiry in minutes', 'multicache-plugin');
    $info_tag = __('Set cache expiry in minutes. Memcache pages may be viewd with the page cache analyzer and file cache pages may be viwed as groups only.', 'multicache-plugin');
    echo makeInputButton('multicache_config_options', 'cachetime', $cache_duration, true, $title_tag);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_multicache_persist_input()
{

    $options = get_option('multicache_config_options');
    $multicache_persist = $options['multicache_persist'];
    // $multicache_persist = 0;
    
    echo makeRadioButton('multicache_config_options', 'multicache_persist', $multicache_persist);
    $info_tag = __('Choose to turn on persistent memcache in the multicache controller', 'multicache-plugin');
    ?><span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}
// mul comp onwards
function multicache_config_setting_multicache_compression_input()
{

    $options = get_option('multicache_config_options');
    $multicache_compression = $options['multicache_compress'];
    // $multicache_persist = 0;
    
    echo makeRadioButton('multicache_config_options', 'multicache_compress', $multicache_compression);
    $info_tag = __('Choose to turn on fastLZ compression in memcache', 'multicache-plugin');
    ?><span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_multicache_host_input()
{

    $options = get_option('multicache_config_options');
    $multicache_host = ! empty($options['multicache_server_host']) ? $options['multicache_server_host'] : '127.0.0.1';
    $title_tag = __('Set the memcache host', 'multicache-plugin');
    $info_tag = __('Set the memcache host', 'multicache-plugin');
    echo makeInputButton('multicache_config_options', 'multicache_server_host', $multicache_host, true, $title_tag);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_multicache_port_input()
{

    $options = get_option('multicache_config_options');
    $multicache_port = ! empty($options['multicache_server_port']) ? $options['multicache_server_port'] : '11211';
    $title_tag = __('Set the memcache port', 'multicache-plugin');
    $info_tag = __('Set the memcache port', 'multicache-plugin');
    echo makeInputButton('multicache_config_options', 'multicache_server_port', $multicache_port, true, $title_tag);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}
// Multicache Optimisation Controls
function multicache_config_setting_gtmetrix_testing_input()
{

    $options = get_option('multicache_config_options');
    $multicache_gtmetrix = isset($options['gtmetrix_testing']) ? $options['gtmetrix_testing'] : '0';
    echo makeRadioButton('multicache_config_options', 'gtmetrix_testing', $multicache_gtmetrix);
    $info_tag = __('Choose to turn on GtMetrix automated testing. Requires cron as indicated in cron url below.', 'multicache-plugin');
    ?><span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

// start
function multicache_config_setting_gtmetrix_api_budget_input()
{
    // get option 'text_string' value from the database
    $options = get_option('multicache_config_options');
    $gtmetrix_api_budget = ! empty($options['gtmetrix_api_budget']) ? $options['gtmetrix_api_budget'] : '20';
    $title_tag = __('Control the API budget', 'multicache-plugin');
    $info_tag = __('Control the API budget on your credited tests per day', 'multicache-plugin');
    
    echo makeSelectButtonNumeric('multicache_config_options', 'gtmetrix_api_budget', $gtmetrix_api_budget, false, $title_tag);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_gtmetrix_email_input()
{

    $options = get_option('multicache_config_options');
    $multicache_email = $options['gtmetrix_email'];
    $title_tag = __('Enter GtMetrix Auth Email', 'multicache-plugin');
    $info_tag = __('Enter your GtMetrix credentials email', 'multicache-plugin');
    echo makeInputButton('multicache_config_options', 'gtmetrix_email', $multicache_email, false, $title_tag, ' multicache-embedded');
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_gtmetrix_token_input()
{

    $options = get_option('multicache_config_options');
    $multicache_gtmetrix_token = $options['gtmetrix_token'];
    $title_tag = __('Enter your GTMetrix token', 'multicache-plugin');
    $info_tag = __('Enter your GtMetrix credentials GTMetrix token', 'multicache-plugin');
    echo makeInputButton('multicache_config_options', 'gtmetrix_token', $multicache_gtmetrix_token, false, $title_tag, ' multicache-embedded');
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_gtmetrix_adblock_input()
{

    $options = get_option('multicache_config_options');
    $multicache_gtmetrix_adblock = $options['gtmetrix_adblock'];
    echo makeRadioButton('multicache_config_options', 'gtmetrix_adblock', $multicache_gtmetrix_adblock);
    $info_tag = __('GtMetrix offers a feature that prevents ads from being loaded during testing', 'multicache-plugin');
    ?><span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_gtmetrix_test_url_input()
{

    $options = get_option('multicache_config_options');
    $multicache_gtmetrix_test_url = ! empty($options['gtmetrix_test_url']) ? $options['gtmetrix_test_url'] : MulticacheUri::root();
    $title_tag = __('Set the url to test', 'multicache-plugin');
    $info_tag = __('Set the url to conduct GTMetrix tests upon', 'multicache-plugin');
    echo makeInputButton('multicache_config_options', 'gtmetrix_test_url', $multicache_gtmetrix_test_url, false, $title_tag, ' multicache-embedded');
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_gtmetrix_allow_simulation_input()
{

    $options = get_option('multicache_config_options');
    $multicache_gtmetrix_allow_simulation = $options['gtmetrix_allow_simulation'];
    echo makeRadioButton('multicache_config_options', 'gtmetrix_allow_simulation', $multicache_gtmetrix_allow_simulation);
    $info_tag = __('Select yes to perform simulation testing or select no to perform regular non-simulation tests', 'multicache-plugin');
    ?><span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_gtmetrix_simulation_advanced_input()
{

    $options = get_option('multicache_config_options');
    $multicache_simulation_advanced = ($options['simulation_advanced'] == 1) ? 1 : 0;
    $title_tag = __('Choose to perform an Advanced Simulation Iterative test', 'multicache-plugin');
    $info_tag = __('Choose to perform an Advanced Simulation Iterative test. Advanced simulation involves loadinstructions that load scripts and styles in various parts of your webpage to optimize load times', 'multicache-plugin');
    echo makeCheckBox('multicache_config_options', 'simulation_advanced', $multicache_simulation_advanced);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_gtmetrix_jssimulation_parse_input()
{

    $options = get_option('multicache_config_options');
    $multicache_jssimulation_parse = $options['jssimulation_parse'];
    $title_tag = __('Choose how advanced simulation loadinstructions should be parsed', 'multicache-plugin');
    $info_tag = __('Choose how advanced simulation loadinstructions should be parsed. Use query parsing only for debugging.', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'jssimulation_parse', $multicache_jssimulation_parse, array(
        0 => 1,
        1 => 0
    ), __('Internal', 'multicache-plugin'), __('Url query', 'multicache-plugin'));
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_gtmetrix_cycles_input()
{
    // get option 'text_string' value from the database
    $options = get_option('multicache_config_options');
    $gtmetrix_gtmetrix_cycles = ! empty($options['gtmetrix_cycles']) ? $options['gtmetrix_cycles'] : '1';
    $title_tag = __('Choose the number of testing cycles', 'multicache-plugin');
    $info_tag = __('Choose the number of testing cycles. N.B: Choosing many cycles may lead to extended duration of completion, please check the dashboard for the expected end date of testing', 'multicache-plugin');
    
    echo makeSelectButtonNumeric('multicache_config_options', 'gtmetrix_cycles', $gtmetrix_gtmetrix_cycles, false, $title_tag, '1', '10', '1');
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}
// Testing Conditions and Parameters
// start
function multicache_config_setting_precache_factor_min_input()
{
    // get option 'text_string' value from the database
    $options = get_option('multicache_config_options');
    $multicache_precache_factor_min = ($options['precache_factor_min'] >= 0 && $options['precache_factor_min'] <= 9) ? $options['precache_factor_min'] : 0;
    
    $title_tag = __('Choose the minimum value for precache compression', 'multicache-plugin');
    $info_tag = __('Choose the minimum value for precache compression. Only required in simulation mode. To overide current precache please use the default settings', 'multicache-plugin');
    
    echo makeSelectButtonNumeric('multicache_config_options', 'precache_factor_min', $multicache_precache_factor_min, false, $title_tag, '0', '9', '1');
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_precache_factor_max_input()
{
    // get option 'text_string' value from the database
    $options = get_option('multicache_config_options');
    $multicache_precache_factor_max = ($options['precache_factor_max'] >= 0 && $options['precache_factor_max'] <= 9) ? $options['precache_factor_max'] : 9;
    
    $title_tag = __('Choose the minimum value for precache compression', 'multicache-plugin');
    $info_tag = __('Choose the minimum value for precache compression. Only required in simulation mode. To overide current precache please use the default settings', 'multicache-plugin');
    
    echo makeSelectButtonNumeric('multicache_config_options', 'precache_factor_max', $multicache_precache_factor_max, false, $title_tag, '0', '9', '1');
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_precache_factor_default_input()
{
    
    // get option 'text_string' value from the database
    $options = get_option('multicache_config_options');
    $multicache_precache_factor_default = ($options['precache_factor_default'] >= 0 && $options['precache_factor_default'] <= 9) ? $options['precache_factor_default'] : 2;
    
    $title_tag = __('Choose the default precache factor', 'multicache-plugin');
    $info_tag = __('Choose the default precache factor. Any value set here will overide the current factor.', 'multicache-plugin');
    
    echo makeSelectButtonNumeric('multicache_config_options', 'precache_factor_default', $multicache_precache_factor_default, false, $title_tag, '0', '9', '1');
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_ccomp_factor_min_input()
{

    $options = get_option('multicache_config_options');
    $multicache_ccomp_factor_min = (isset($options['ccomp_factor_min']) && $options['ccomp_factor_min'] >= 0 && $options['ccomp_factor_min'] <= 1) ? $options['ccomp_factor_min'] : 0;
    $title_tag = __('Choose the minimum level of memcache compression', 'multicache-plugin');
    $info_tag = __('Choose the minimum level of memcache compression. Algorithm is fastLz', 'multicache-plugin');
    echo makeInputButton('multicache_config_options', 'ccomp_factor_min', $multicache_ccomp_factor_min, false, $title_tag, ' multicache-embedded');
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_ccomp_factor_max_input()
{

    $options = get_option('multicache_config_options');
    $multicache_ccomp_factor_max = (isset($options['ccomp_factor_max']) && $options['ccomp_factor_max'] >= 0 && $options['ccomp_factor_max'] <= 1) ? $options['ccomp_factor_max'] : 1;
    $title_tag = __('Choose the maximum level of memcache compression', 'multicache-plugin');
    $info_tag = __('Choose the maximum level of memcache compression. Algorithm is fastLz', 'multicache-plugin');
    echo makeInputButton('multicache_config_options', 'ccomp_factor_max', $multicache_ccomp_factor_max, false, $title_tag, ' multicache-embedded');
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_ccomp_factor_step_input()
{

    $options = get_option('multicache_config_options');
    $multicache_ccomp_factor_step = (isset($options['ccomp_factor_step']) && $options['ccomp_factor_step'] >= 0 && $options['ccomp_factor_step'] <= 1) ? $options['ccomp_factor_step'] : 0.1;
    $title_tag = __('Choose the incremental steps fro compression', 'multicache-plugin');
    $info_tag = __('Choose the incremental steps fro compression. Default 0.1', 'multicache-plugin');
    echo makeInputButton('multicache_config_options', 'ccomp_factor_step', $multicache_ccomp_factor_step, false, $title_tag, ' multicache-embedded');
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_ccomp_factor_default_input()
{

    $options = get_option('multicache_config_options');
    $multicache_ccomp_factor_default = (isset($options['ccomp_factor_default']) && $options['ccomp_factor_default'] >= 0 && $options['ccomp_factor_default'] <= 1) ? $options['ccomp_factor_default'] : 0.22;
    $title_tag = __('Choose the default Cache Compression', 'multicache-plugin');
    $info_tag = __('Choose the default Cache Compression. Default 0.22', 'multicache-plugin');
    echo makeInputButton('multicache_config_options', 'ccomp_factor_default', $multicache_ccomp_factor_default, false, $title_tag, ' multicache-embedded');
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_cron_url_input()
{
if(is_multisite())
{
	$blog_id = get_current_blog_id();
	$url = 'simcontrol/simcron.php?blogid='.$blog_id;
}
else {
	$url = 'simcontrol/simcron.php';
}
    $multicache_cron_url = plugins_url($url , dirname(__FILE__));
    $title_tag = __('Cron this url for continuous assesment and simulation based optimisations', 'multicache-plugin');
    $info_tag = __('Cron this url for continuous assesment and simulation based optimisations', 'multicache-plugin');
    echo makeInputButton('multicache_config_options', 'cron_url', $multicache_cron_url, false, $title_tag, ' multicache-embedded');
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_googleclientid_input()
{
	$transient = MulticacheHelper::getoAuthTransients();
	if(!empty($transient))
	{
		if(isset($transient['multicache_lnparam']) && is_object($transient['multicache_lnparam']))
		{
			if(!empty($transient['multicache_lnparam']->googleclientid))
			{
				$multicache_googleclientid = $transient['multicache_lnparam']->googleclientid;
			}
		}
	}
	if(!isset($multicache_googleclientid))
	{
    $options = get_option('multicache_config_options');
    $multicache_googleclientid = ! empty($options['googleclientid']) ? $options['googleclientid'] : '';
	}
    $title_tag = __('Set your Google ClientId', 'multicache-plugin');
    $info_tag = __('Set your Google ClientId. The Google ClientId is obtained from the Google API Console at https://code.google.com/apis/console', 'multicache-plugin');
    echo makeInputButton('multicache_config_options', 'googleclientid', $multicache_googleclientid, false, $title_tag, ' multicache-embedded');
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_googleclientsecret_input()
{
	$transient = MulticacheHelper::getoAuthTransients();
	if(!empty($transient))
	{
		if(isset($transient['multicache_lnparam']) && is_object($transient['multicache_lnparam']))
		{
			if(!empty($transient['multicache_lnparam']->googleclientsecret))
			{
				$multicache_googleclientsecret = $transient['multicache_lnparam']->googleclientsecret;
			}
		}
	}
	if(!isset($multicache_googleclientsecret))
	{
    $options = get_option('multicache_config_options');
    $multicache_googleclientsecret = ! empty($options['googleclientsecret']) ? $options['googleclientsecret'] : '';
	}
    $title_tag = __('Set your Google ClientSecret', 'multicache-plugin');
    $info_tag = __('Set your Google ClientSecret. The Google ClientSecret is obtained from the Google API Console at https://code.google.com/apis/console', 'multicache-plugin');
    echo makeInputButton('multicache_config_options', 'googleclientsecret', $multicache_googleclientsecret, false, $title_tag, ' multicache-embedded');
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_googleviewid_input()
{
	$transient = MulticacheHelper::getoAuthTransients();
	if(!empty($transient))
	{
		if(isset($transient['multicache_lnparam']) && is_object($transient['multicache_lnparam']))
		{
			if(!empty($transient['multicache_lnparam']->googleviewid))
			{
				$multicache_googleviewid = $transient['multicache_lnparam']->googleviewid;
			}
		}
	}
	if(!isset($multicache_googleviewid))
	{
    $options = get_option('multicache_config_options');
    $multicache_googleviewid = ! empty($options['googleviewid']) ? $options['googleviewid'] : '';
	}
    $title_tag = __('Set your GoogleAnalytics ViewId', 'multicache-plugin');
    $info_tag = __('Set your GoogleAnalytics ViewId. The Google Analytics ViewId is obtained from the Google Analytics Administraion dashboard', 'multicache-plugin');
    echo makeInputButton('multicache_config_options', 'googleviewid', $multicache_googleviewid, false, $title_tag, ' multicache-embedded');
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_redirecturi_input()
{

    $multicache_redirecturi = admin_url('admin.php?page=multicache-config-menu&authg=2');
    $title_tag = __('The redirect uri to be placed in the Google console', 'multicache-plugin');
    $info_tag = __('The redirect uri to be placed in the Google console. Post authentication Google redirects flow to this uri', 'multicache-plugin');
    echo makeInputButton('multicache_config_options', 'redirecturi', $multicache_redirecturi, false, $title_tag, ' multicache-embedded');
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_googlestartdate_input()
{

    $options = get_option('multicache_config_options');
    $multicache_googlestartdate = $options['googlestartdate'];
    $title_tag = __('Set start date to cull Gooogle Analytics records', 'multicache-plugin');
    $info_tag = __('Set start date to cull Gooogle Analytics records', 'multicache-plugin');
    echo makeDateButton('multicache_config_options', 'googlestartdate', $multicache_googlestartdate, $title_tag);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_googleenddate_input()
{

    $options = get_option('multicache_config_options');
    $multicache_googleenddate = $options['googleenddate'];
    $title_tag = __('Set end date to cull Gooogle Analytics records', 'multicache-plugin');
    $info_tag = __('Set end date to cull Gooogle Analytics records', 'multicache-plugin');
    echo makeDateButton('multicache_config_options', 'googleenddate', $multicache_googleenddate, $title_tag);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_googlenumberurlscache_input()
{

    $options = get_option('multicache_config_options');
    $multicache_googlenumberurlscache = ! empty($options['googlenumberurlscache']) ? $options['googlenumberurlscache'] : 200;
    $title_tag = __('Set the number of pages to be primed to memcache', 'multicache-plugin');
    $info_tag = __('Set the number of pages to be primed to memcache. In practice expect about 0.7 ~ 1.5 times this number to be extracted from google as multicache negotiates various aspects of these urls', 'multicache-plugin');
    echo makeInputButton('multicache_config_options', 'googlenumberurlscache', $multicache_googlenumberurlscache, false, $title_tag, ' multicache-embedded');
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}
/*
function multicache_config_setting_multicachedistribution_input()
{

    $options = get_option('multicache_config_options');
    $multicache_multicachedistribution = $options['multicachedistribution'];
    $title_tag = __('Choose the mode of operation', 'multicache-plugin');
    $info_tag = __('Choose the mode of operation.', 'multicache-plugin');
    echo makeMultiRadioButton('multicache_config_options', 'multicachedistribution', $multicache_multicachedistribution, array(
        3 => 'HammeredPageSpeed',
        2 => 'LHM',
        1 => 'MultiAdmin',
        0 => 'Cart'
    ), 4);
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}
*/
function multicache_config_setting_multicachedistribution_input()
{

	$options = get_option('multicache_config_options');
	$multicache_multicachedistribution = $options['multicachedistribution'];

	$title_tag = __('Choose the mode of operation', 'multicache-plugin');
	$info_tag = __('Choose the mode of operation.', 'multicache-plugin');

	echo makeSelectButtonNumeric('multicache_config_options', 'multicachedistribution', $multicache_multicachedistribution , false, $title_tag, '0', '3', '1', array(
			0 => 'Cart',
			1 => 'MultiAdmin',
			2 => 'LHM',
			3 => 'HammeredPageSpeed',
				));

	?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

/*
function multicache_config_setting_urlfilters_input()
{

      $options = get_option('multicache_config_options');
    $multicache_urlfilters = $options['urlfilters'];
    $title_tag = __('The urls from google may contain query paramteres, this controls their usage', 'multicache-plugin');
    $info_tag = __('The urls from google may contain query paramteres, this controls their usage.', 'multicache-plugin');
    echo makeMultiRadioButton('multicache_config_options', 'urlfilters', $multicache_urlfilters, array(
        2 => 'Filter Off',
        1 => 'Remove Query',
        0 => 'Remove Query Urls'
    ), 3);
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php
	

}
*/

function multicache_config_setting_urlfilters_input()
{

	$options = get_option('multicache_config_options');
	$multicache_urlfilters = $options['urlfilters'];

	$title_tag = __('The urls from google may contain query paramteres, this controls their usage', 'multicache-plugin');
    $info_tag = __('The urls from google may contain query paramteres, this controls their usage.', 'multicache-plugin');

	echo makeSelectButtonNumeric('multicache_config_options', 'urlfilters', $multicache_urlfilters, false, $title_tag, '0', '2', '1', array(
			0 => 'Filter Off',
			1 => 'Remove Query',
			2 => 'Remove Query Urls',
			
	));

	?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_frequency_distribution_input()
{

    $options = get_option('multicache_config_options');
    $multicache_frequency_distribution = $options['frequency_distribution'];
    $title_tag = __('Calculates the frequency distribution of url page spectrum', 'multicache-plugin');
    $info_tag = __('Calculates the frequency distribution of url page spectrum.', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'frequency_distribution', $multicache_frequency_distribution);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_natlogdist_input()
{

    $options = get_option('multicache_config_options');
    $multicache_natlogdist = $options['natlogdist'];
    $title_tag = __('Calculates the natural logirithm of the url page spectrum', 'multicache-plugin');
    $info_tag = __('Calculates the natural logirithm of the url page spectrum', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'natlogdist', $multicache_natlogdist);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

// Advanced
function multicache_config_setting_advanced_simulation_lock_input()
{

    $options = get_option('multicache_config_options');
    $multicache_advanced_simulation_lock = $options['advanced_simulation_lock'];
    $title_tag = __('Prevents accidental changes in advance test series', 'multicache-plugin');
    $info_tag = __('Prevents accidental changes in advance test series', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'advanced_simulation_lock', $multicache_advanced_simulation_lock);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_gzip_input()
{
	$options = get_option('multicache_config_options');
	$multicache_gzip = $options['gzip'];
	$title_tag = __('Choose to turn on Gzip', 'multicache-plugin');
	$info_tag = __('Choose to turn on Gzip', 'multicache-plugin');
	echo makeSelectButton('multicache_config_options', 'gzip', $multicache_gzip, array(
        0 => array(
            'key' => 0,
            'val' => 'Off'
        ),
        1 => array(
            'key' => 1,
            'val' => 'On'
        )
    ), true, $title_tag);
    
    ?>


<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $title_tag;?>"> </span>

<?php
		
}

function multicache_config_setting_additionalpagecacheurls_input()
{

    
 $options = get_option('multicache_config_options');
    $multicache_additionalpagecacheurls = $options['additionalpagecacheurls'];
    $multicache_additionalpagecacheurls =MulticacheHelper::decodePrepareMulticacheTextAreas($multicache_additionalpagecacheurls);
  
    $title_tag = __('Enter the urls whose pages are to be stored in memcache', 'multicache-plugin');
    $info_tag = __('Enter the urls whose pages are to be stored in memcache', 'multicache-plugin');
    echo makeTextButton('multicache_config_options', 'additionalpagecacheurls', $multicache_additionalpagecacheurls);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_force_locking_off_input()
{

    $options = get_option('multicache_config_options');
    $multicache_force_locking_off = $options['force_locking_off'];
    $title_tag = __('Choose to turn of cache locking', 'multicache-plugin');
    $info_tag = __('Choose to turn off cache locking', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'force_locking_off', $multicache_force_locking_off);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_conduit_switch_input()
{

    $options = get_option('multicache_config_options');
    $multicache_conduit_switch = $options['conduit_switch'];
    $title_tag = __('Choose to turn on conduit', 'multicache-plugin');
    $info_tag = __('Choose to turn on conduit. Conduit updates form tokens via ajax calls.', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'conduit_switch', $multicache_conduit_switch);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_conduit_nonce_input()
{
	$options = get_option('multicache_config_options');
	$nonce_name = $options['conduit_nonce_name'];
	$title_tag = __('Nonce name', 'multicache-plugin');
	$info_tag = __('The default name for the nice login register is provided as an example.', 'multicache-plugin');
	echo makeInputButton('multicache_config_options', 'conduit_nonce_name', $nonce_name, false, $title_tag,' multicache-embedded');
	?>
	
	<span class="glyphicon glyphicon-info-sign"
		title="<?php echo $info_tag;?>"> </span>
	<?php
}

function multicache_config_setting_minify_html_input()
{

    $options = get_option('multicache_config_options');
    $multicache_minify_html = $options['minify_html'];
    $title_tag = __('Choose to minify html', 'multicache-plugin');
    $info_tag = __('Choose to minify html', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'minify_html', $multicache_minify_html);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}


function multicache_config_setting_comment_cacheinvalidation_input()
{

	$options = get_option('multicache_config_options');
	$multicache_cacheinvalidation = $options['cache_comment_invalidation'];
	$title_tag = __('Choose how multicache should invalidate fresh comments', 'multicache-plugin');
	$info_tag = __('Comment invalidation consumes highest load with Immediate and least with none. Queue is also an acceptable option, this controls their usage.', 'multicache-plugin');
	echo makeMultiRadioButton('multicache_config_options', 'cache_comment_invalidation', $multicache_cacheinvalidation, array(
			0 => 'None',
			1 => 'Immediate',
			2 => 'Queue'
	), 3);
	?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php
	

}
//version1.0.0.2 
function multicache_config_setting_positional_dontmovesrc_input()
{

	$options = get_option('multicache_config_options');
	$multicache_positional_dontmovesrc = $options['positional_dontmovesrc'];
	$multicache_positional_dontmovesrc =MulticacheHelper::decodePrepareMulticacheTextAreas($multicache_positional_dontmovesrc);
	$title_tag = __('Specify the src or src bit of scripts that should not be moved, one per line', 'multicache-plugin');
	$info_tag = __('Specify the src or src bit of scripts that should not be moved, one per line. eg. googletagservices.com/tag/js/gpt.js
			googletag.cmd.push', 'multicache-plugin');
	echo makeTextButton('multicache_config_options', 'positional_dontmovesrc', $multicache_positional_dontmovesrc);
	?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}
//version1.0.0.2 
function multicache_config_setting_allow_multiple_orphaned_input()
{

	$options = get_option('multicache_config_options');
	$multicache_allow_multiple_orphaned = $options['allow_multiple_orphaned'];
	$multicache_allow_multiple_orphaned =MulticacheHelper::decodePrepareMulticacheTextAreas($multicache_allow_multiple_orphaned);
	$title_tag = __('Specify the src string bits or -1 for all , to allow duplication in orphaned scripts, one per line', 'multicache-plugin');
	$info_tag = __('Specify the src string bits or -1 for all , to allow duplication in orphaned scripts, one per line', 'multicache-plugin');
	echo makeTextButton('multicache_config_options', 'allow_multiple_orphaned', $multicache_allow_multiple_orphaned);
	?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}
//cache logged-in users
function multicache_config_setting_cache_user_loggedin_input()
{

	$options = get_option('multicache_config_options');
	$multicache_cache_cache_user_loggedin = $options['cache_user_loggedin'];
	$title_tag = __('Cache Loggedin users', 'multicache-plugin');
	$info_tag = __('Choose to cache logged in users. In certain setting you may wish not to cache logged in users esp. in settings involving shopping carts and transactions', 'multicache-plugin');
	echo makeRadioButton('multicache_config_options', 'cache_user_loggedin', $multicache_cache_cache_user_loggedin);

	?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}
//optimize user logged in 
function multicache_config_setting_optimize_user_loggedin_input()
{

	$options = get_option('multicache_config_options');
	$multicache_cache_optimize_user_loggedin = $options['optimize_user_loggedin'];
	$title_tag = __('Optimize for Loggedin users', 'multicache-plugin');
	$info_tag = __('Optimize for logged in users. A single switch to turn off all tweaks for logged in users', 'multicache-plugin');
	echo makeRadioButton('multicache_config_options', 'optimize_user_loggedin', $multicache_cache_optimize_user_loggedin);

	?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

//cache dynamic urls
function multicache_config_setting_cache_query_urls_input()
{

	$options = get_option('multicache_config_options');
	$multicache_cache_cache_query_urls = $options['cache_query_urls'];
	$title_tag = __('Cache Dynamic Urls', 'multicache-plugin');
	$info_tag = __('Choose to cache daynamic urls. In certain setting you may wish not to cache dynamic urls esp. in settings involving shopping carts and transactions', 'multicache-plugin');
	echo makeRadioButton('multicache_config_options', 'cache_query_urls', $multicache_cache_cache_query_urls);

	?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}
//optimize dynamic urls
function multicache_config_setting_optimize_query_urls_input()
{

	$options = get_option('multicache_config_options');
	$multicache_cache_optimize_query_urls = $options['optimize_query_urls'];
	$title_tag = __('Optimize for Dynamic Urls', 'multicache-plugin');
	$info_tag = __('Optimize for Dynamic Urls. A single switch to turn off all tweaks for dynamic urls', 'multicache-plugin');
	echo makeRadioButton('multicache_config_options', 'optimize_query_urls', $multicache_cache_optimize_query_urls);

	?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_targetpageloadtime_input()
{

    $options = get_option('multicache_config_options');
    $multicache_targetpageloadtime = ($options['targetpageloadtime'] >= 3 && $options['targetpageloadtime'] <= 8) ? $options['targetpageloadtime'] : 3;
    
    $title_tag = __('Choose the target page load time.', 'multicache-plugin');
    $info_tag = __('Choose the target page load time. Used for algorithimic purposes only', 'multicache-plugin');
    
    echo makeSelectButtonNumeric('multicache_config_options', 'targetpageloadtime', $multicache_targetpageloadtime, false, $title_tag, '3', '8', '1');
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_algorithmavgloadtimeweight_input()
{

    $options = get_option('multicache_config_options');
    $multicache_algorithmavgloadtimeweight = $options['algorithmavgloadtimeweight'];
    $title_tag = __('Weightage load time', 'multicache-plugin');
    $info_tag = __('Weightage load time. Note the sum of all weights should be 1', 'multicache-plugin');
    echo makeInputButton('multicache_config_options', 'algorithmavgloadtimeweight', $multicache_algorithmavgloadtimeweight, false, $title_tag, ' multicache-embedded');
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_algorithmmodemaxbelowtimeweight_input()
{

    $options = get_option('multicache_config_options');
    $multicache_algorithmmodemaxbelowtimeweight = $options['algorithmmodemaxbelowtimeweight'];
    $title_tag = __('Weightage (Mode)', 'multicache-plugin');
    $info_tag = __('Weightage (Mode). Note the sum of all weights should be 1', 'multicache-plugin');
    echo makeInputButton('multicache_config_options', 'algorithmmodemaxbelowtimeweight', $multicache_algorithmmodemaxbelowtimeweight, false, $title_tag, ' multicache-embedded');
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_algorithmvarianceweight_input()
{

    $options = get_option('multicache_config_options');
    $multicache_algorithmvarianceweight = $options['algorithmvarianceweight'];
    $title_tag = __('Weightage (Variances)', 'multicache-plugin');
    $info_tag = __('Weightage (Variances). Note the sum of all weights should be 1', 'multicache-plugin');
    echo makeInputButton('multicache_config_options', 'algorithmvarianceweight', $multicache_algorithmvarianceweight, false, $title_tag, ' multicache-embedded');
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}
/*
function multicache_config_setting_deployment_method_input()
{

      $options = get_option('multicache_config_options');
    $multicache_deployment_method = $options['deployment_method'];
    $title_tag = __('Post simulation testing the following method will be deployed and simulation turned off', 'multicache-plugin');
    $info_tag = __('Post simulation testing the following method will be deployed and simulation turned off.', 'multicache-plugin');
    echo makeMultiRadioButton('multicache_config_options', 'deployment_method', $multicache_deployment_method, array(
        3 => 'Algorithm',
    	2 => 'BLT',
        1 => 'Default',
        0 => 'None'
    ), 4);
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}
*/
function multicache_config_setting_deployment_method_input()
{

	$options = get_option('multicache_config_options');
	$multicache_deployment_method = $options['deployment_method'];

	$title_tag = __('Post simulation testing the following method will be deployed and simulation turned off', 'multicache-plugin');
    $info_tag = __('Post simulation testing the following method will be deployed and simulation turned off.', 'multicache-plugin');

	echo makeSelectButtonNumeric('multicache_config_options', 'deployment_method', $multicache_deployment_method, false, $title_tag, '0', '3', '1', array(
			3 => 'Algorithm',
			2 => 'BestLoadTime',
			1 => 'Default',
			0 => 'None',
			
	));

	?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}
// jstweaks
function multicache_config_setting_js_switch_input()
{

    $options = get_option('multicache_config_options');
    $multicache_js_switch = $options['js_switch'];
    $title_tag = __('Turn on js tweaks', 'multicache-plugin');
    $info_tag = __('Turn on javascript tweaks', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'js_switch', $multicache_js_switch);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_default_scrape_url_input()
{

    $options = get_option('multicache_config_options');
    $multicache_default_scrape_url = $options['default_scrape_url'];
    $title_tag = __('A typical url to base js strategy', 'multicache-plugin');
    $info_tag = __('A typical url to base js strategy.', 'multicache-plugin');
    echo makeInputButton('multicache_config_options', 'default_scrape_url', $multicache_default_scrape_url, false, $title_tag, ' multicache-embedded');
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}
/*
function multicache_config_principle_jquery_scope_method_input()
{

      $options = get_option('multicache_config_options');
    $multicache_principle_jquery_scope = $options['principle_jquery_scope'];
    $title_tag = __("The principle jQuery library's scope", 'multicache-plugin');
    $info_tag = __("The principle jQuery library's scope", 'multicache-plugin');
    echo makeMultiRadioButton('multicache_config_options', 'principle_jquery_scope', $multicache_principle_jquery_scope, array(
        0 => 'jQuery',
        1 => '$',
        2 => 'Other'
    ), 3);
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}
*/
function multicache_config_principle_jquery_scope_method_input()
{

	 $options = get_option('multicache_config_options');
    $multicache_principle_jquery_scope = $options['principle_jquery_scope'];
    $title_tag = __("The principle jQuery library's scope", 'multicache-plugin');
    $info_tag = __("The principle jQuery library's scope", 'multicache-plugin');
	echo makeSelectButtonNumeric('multicache_config_options', 'principle_jquery_scope', $multicache_principle_jquery_scope, false, $title_tag, '0', '2', '1', array(
			0 => 'jQuery',
			1 => '$',
			2 => 'Other',
			
	));

	?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_principle_jquery_scope_other_input()
{

    $options = get_option('multicache_config_options');
    $multicache_principle_jquery_scope_other = $options['principle_jquery_scope_other'];
    $title_tag = __('Enter a custom jQuery scope variable', 'multicache-plugin');
    $info_tag = __('Enter a custom jQuery scope variable', 'multicache-plugin');
    echo makeInputButton('multicache_config_options', 'principle_jquery_scope_other', $multicache_principle_jquery_scope_other, false, $title_tag, ' multicache-embedded');
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_dedupe_scripts_input()
{

    $options = get_option('multicache_config_options');
    $multicache_dedupe_scripts = $options['dedupe_scripts'];
    $title_tag = __('Choose to dedupe scripts', 'multicache-plugin');
    $info_tag = __('Choose to dedupe scripts', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'dedupe_scripts', $multicache_dedupe_scripts);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_defer_social_input()
{

    $options = get_option('multicache_config_options');
    $multicache_defer_social = $options['defer_social'];
    $title_tag = __('Choose to ddfer social scripts', 'multicache-plugin');
    $info_tag = __('Choose to defer social scripts', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'defer_social', $multicache_defer_social);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_defer_advertisement_input()
{

    $options = get_option('multicache_config_options');
    $multicache_defer_advertisement = $options['defer_advertisement'];
    $title_tag = __('Choose to defer the rendering of advertisement scripts', 'multicache-plugin');
    $info_tag = __('Choose to defer the rendering of advertisement scripts', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'defer_advertisement', $multicache_defer_advertisement);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_defer_async_input()
{

    $options = get_option('multicache_config_options');
    $multicache_defer_async = $options['defer_async'];
    $title_tag = __('Choose todefer asychronous scripts', 'multicache-plugin');
    $info_tag = __('Choose todefer asychronous scripts', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'defer_async', $multicache_defer_async);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_maintain_preceedence_input()
{

    $options = get_option('multicache_config_options');
    $multicache_maintain_preceedence = $options['maintain_preceedence'];
    $title_tag = __('Choose to maintain preceedence of javascript', 'multicache-plugin');
    $info_tag = __('Choose to maintain preceedence of javascript', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'maintain_preceedence', $multicache_maintain_preceedence);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_minimize_roundtrips_input()
{

    $options = get_option('multicache_config_options');
    $multicache_minimize_roundtrips = $options['minimize_roundtrips'];
    $title_tag = __('Choose to group internal js by loadsctions', 'multicache-plugin');
    $info_tag = __('Choose to group internal js by loadsctions', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'minimize_roundtrips', $multicache_minimize_roundtrips);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_social_script_identifiers_input()
{

    $options = get_option('multicache_config_options');
    $multicache_social_script_identifiers = $options['social_script_identifiers'];
    $multicache_social_script_identifiers =MulticacheHelper::decodePrepareMulticacheTextAreas($multicache_social_script_identifiers);
  
    $title_tag = __('Weightage (Variances)', 'multicache-plugin');
    $info_tag = __('Weightage (Variances). Note the sum of all weights should be 1', 'multicache-plugin');
    echo makeTextButton('multicache_config_options', 'social_script_identifiers', $multicache_social_script_identifiers);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_advertisement_script_identifiers_input()
{

    $options = get_option('multicache_config_options');
    $multicache_advertisement_script_identifiers = $options['advertisement_script_identifiers'];
    $multicache_advertisement_script_identifiers =MulticacheHelper::decodePrepareMulticacheTextAreas($multicache_advertisement_script_identifiers);
   
    $title_tag = __('Place any unique bit of dvertisement code that may be used to distinguish advertisement scripts', 'multicache-plugin');
    $info_tag = __('Place any unique bit of dvertisement code that may be used to distinguish advertisement scripts', 'multicache-plugin');
    echo makeTextButton('multicache_config_options', 'advertisement_script_identifiers', $multicache_advertisement_script_identifiers);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}
/*
 * Moved to MulticacheHelper
function decodePrepareMulticacheTextAreas($obj)
{
	if(empty($obj))
	{
		Return false;
	}
	
		$decoded_obj = json_decode($obj, true);
		$decoded_obj = array_filter($decoded_obj);
		Return  implode("\n", $decoded_obj);
		 
	
}
*/
function multicache_config_setting_pre_head_stub_identifiers_input()
{

    $options = get_option('multicache_config_options');
    $multicache_pre_head_stub_identifiers = $options['pre_head_stub_identifiers'];
    $multicache_pre_head_stub_identifiers =MulticacheHelper::decodePrepareMulticacheTextAreas($multicache_pre_head_stub_identifiers);
   if(empty($multicache_pre_head_stub_identifiers))
    {
    	$multicache_pre_head_stub_identifiers = '<head>';
    	    }
    $title_tag = __('Opening Head Tag variants may be placed one per line', 'multicache-plugin');
    $info_tag = __('Opening Head Tag variants may be placed one per line', 'multicache-plugin');
    echo makeTextButton('multicache_config_options', 'pre_head_stub_identifiers', $multicache_pre_head_stub_identifiers);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_head_stub_identifiers_input()
{

    $options = get_option('multicache_config_options');
    $multicache_head_stub_identifiers = $options['head_stub_identifiers'];
    $multicache_head_stub_identifiers =MulticacheHelper::decodePrepareMulticacheTextAreas($multicache_head_stub_identifiers);
    $title_tag = __('List the closing head tags here', 'multicache-plugin');
    $info_tag = __('List the closing head tags here', 'multicache-plugin');
    echo makeTextButton('multicache_config_options', 'head_stub_identifiers', $multicache_head_stub_identifiers);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_body_stub_identifiers_input()
{

    $options = get_option('multicache_config_options');
    $multicache_body_stub_identifiers = $options['body_stub_identifiers'];
    $multicache_body_stub_identifiers =MulticacheHelper::decodePrepareMulticacheTextAreas($multicache_body_stub_identifiers);
    $title_tag = __('If the template manifests variants of body tags they may be listed here', 'multicache-plugin');
    $info_tag = __('If the template manifests variants of body tags they may be listed here', 'multicache-plugin');
    echo makeTextButton('multicache_config_options', 'body_stub_identifiers', $multicache_body_stub_identifiers);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_footer_stub_identifiers_input()
{

    $options = get_option('multicache_config_options');
    $multicache_footer_stub_identifiers = $options['footer_stub_identifiers'];
    $multicache_footer_stub_identifiers =MulticacheHelper::decodePrepareMulticacheTextAreas($multicache_footer_stub_identifiers);
    $title_tag = __('If the template manifests variants of closing body tags they may be listed here', 'multicache-plugin');
    $info_tag = __('If the template manifests variants of closing body tags they may be listed here', 'multicache-plugin');
    echo makeTextButton('multicache_config_options', 'footer_stub_identifiers', $multicache_footer_stub_identifiers );
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_js_comments_input()
{

    $options = get_option('multicache_config_options');
    $multicache_js_comments = $options['js_comments'];
    $title_tag = __('Turn on comments to help debug.', 'multicache-plugin');
    $info_tag = __('Turn on comments to help debug. Outputs comments for the various js being combined allowing you to understand which aspect has loaded', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'js_comments', $multicache_js_comments);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_compress_js_input()
{

    $options = get_option('multicache_config_options');
    $multicache_compress_js = $options['compress_js'];
    $title_tag = __('Choose to minify js', 'multicache-plugin');
    $info_tag = __('Choose to minify js', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'compress_js', $multicache_compress_js);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_debug_mode_input()
{

    $options = get_option('multicache_config_options');
    $multicache_debug_mode = $options['debug_mode'];
    $title_tag = __('Choose to set debug mode', 'multicache-plugin');
    $info_tag = __('Choose to set debug mode. In this mode fastcache debug will be set and will log debug queries to error_log. In advanced simulation mode the load instruction will be printed on the top of the site. Do not use this mode in production.', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'debug_mode', $multicache_debug_mode);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_orphaned_scripts_input()
{

    $options = get_option('multicache_config_options');
    $multicache_orphaned_scripts = $options['orphaned_scripts'];
    
    $title_tag = __('Choose where to load orphaned scripts.', 'multicache-plugin');
    $info_tag = __('Choose where to load orphaned scripts.', 'multicache-plugin');
    
    echo makeSelectButtonNumeric('multicache_config_options', 'orphaned_scripts', $multicache_orphaned_scripts, false, $title_tag, '0', '4', '1', array(
        0 => 'leave as is',
        1 => 'Head Open',
        2 => 'Head',
        3 => 'Body',
        4 => 'Footer'
    ));
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

//ver1.0.0.2 resultant async and defer added
function multicache_config_setting_resultant_async_js_input()
{

	$options = get_option('multicache_config_options');
	$multicache_resultant_async_js = $options['resultant_async_js'];
	$title_tag = __('Choose to add an async to resultant js', 'multicache-plugin');
	$info_tag = __('Choose to add an async to resultant js, a google pagespeed recommendation. N.B: Does not always play nice on layouts', 'multicache-plugin');
	echo makeRadioButton('multicache_config_options', 'resultant_async_js', $multicache_resultant_async_js);

	?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}
function multicache_config_setting_resultant_defer_input()
{

	$options = get_option('multicache_config_options');
	$multicache_resultant_defer_js = $options['resultant_defer_js'];
	$title_tag = __('Choose to add a defer to resultant js', 'multicache-plugin');
	$info_tag = __('Choose to add a defer to resultant js. A Google PageSpeed recommendation. N.B:Does not always play nice on layouts', 'multicache-plugin');
	echo makeRadioButton('multicache_config_options', 'resultant_defer_js', $multicache_resultant_defer_js);

	?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}
// jst excludes
function multicache_config_setting_js_tweaker_url_include_exclude_input()
{

      $options = get_option('multicache_config_options');
    $multicache_js_tweaker_url_include_exclude = $options['js_tweaker_url_include_exclude'];
    $title_tag = __('Choose to apply JS tweaker settings to a few urls, or exclude from a few urls', 'multicache-plugin');
    $info_tag = __('Choose to apply JS tweaker settings to a few urls, or exclude from a few urls.', 'multicache-plugin');
    echo makeMultiRadioButton('multicache_config_options', 'js_tweaker_url_include_exclude', $multicache_js_tweaker_url_include_exclude, array(
        0 => 'All',
        1 => 'These Pages',
        2 => 'Not These Pages'
    ), 3);
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_jst_urlinclude_input()
{

    $options = get_option('multicache_config_options');
    $multicache_jst_urlinclude = $options['jst_urlinclude'];
    $multicache_jst_urlinclude =MulticacheHelper::decodePrepareMulticacheTextAreas($multicache_jst_urlinclude);
    $title_tag = __('Specify the complete url ', 'multicache-plugin');
    $info_tag = __('Specify the complete url eg.http://mysite.com or http://www.mysite.com', 'multicache-plugin');
    echo makeTextButton('multicache_config_options', 'jst_urlinclude', $multicache_jst_urlinclude , 3 , 20 );
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_jst_query_include_exclude_input()
{

      $options = get_option('multicache_config_options');
    $multicache_jst_query_include_exclude = $options['jst_query_include_exclude'];
    $title_tag = __('Choose to apply JS tweaker settings to a few query params', 'multicache-plugin');
    $info_tag = __('Choose to apply JS tweaker settings to a few query params or exclude from a few query params.', 'multicache-plugin');
    echo makeMultiRadioButton('multicache_config_options', 'jst_query_include_exclude', $multicache_jst_query_include_exclude, array(
        0 => 'Off',
        1 => 'Inclusion',
        2 => 'Exclusion'
    ), 3);
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_jst_query_param_input()
{

    $options = get_option('multicache_config_options');
    $multicache_jst_query_param = $options['jst_query_param'];
    $multicache_jst_query_param =MulticacheHelper::decodePrepareMulticacheTextAreas($multicache_jst_query_param);
    $title_tag = __('Specify the query param and value', 'multicache-plugin');
    $info_tag = __('Specify the query param and value', 'multicache-plugin');
    echo makeTextButton('multicache_config_options', 'jst_query_param', $multicache_jst_query_param , 3 , 20);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_jst_url_string_input()
{

    $options = get_option('multicache_config_options');
    $multicache_jst_url_string = $options['jst_url_string'];
    $multicache_jst_url_string =MulticacheHelper::decodePrepareMulticacheTextAreas($multicache_jst_url_string);
    $title_tag = __('Any string appearing in a url that multicache has to exclude', 'multicache-plugin');
    $info_tag = __('Any string appearing in a url that multicache has to exclude', 'multicache-plugin');
    echo makeTextButton('multicache_config_options', 'jst_url_string', $multicache_jst_url_string , 3 , 20);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}
// plugins exclusion
function multicache_config_page_js_inclusion_plugin_section_html()
{

    if (! function_exists('get_plugins'))
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $all_plugins = get_plugins();
   
    foreach ($all_plugins as $path => $plugin)
    {
    	$display_name = $plugin["Name"];
        $name = preg_replace('~[^a-zA-Z0-9]~','_',trim(strtolower($display_name)));
        $p_uri = plugins_url('', $path);
        $plugins_urls_path[$name] = array(
            'path_uri' => $p_uri,
            'dir' => $path,
            'rel_path' => str_ireplace(MulticacheUri::root(), '', $p_uri),
        		'display_name' => $display_name
        );
    }
    // $plugins_urls_path
    $options = get_option('multicache_config_options');
    $options = unserialize($options["excluded_components"]);
    
    $labels = array(
        0 => 'default',
        1 => 'Exclude Script',
        2 => 'Exclude Page'
    );
    foreach ($plugins_urls_path as $name => $plug)
    {
        if ($name == 'multicachewp')
        {
            Continue;
        }
        ?>
<div class="exclude-group col-md-10">
	<div class="exclude-label col-md-6"><?php echo  $plug["display_name"];?></div>
	<div class="exclude-switch col-md-4">
        <?php
        $state = isset($options[$name]) ? $options[$name]['value'] : 0;
        $class_adds = " js_excludes";
        $third_param = $name;
        $title_tag = __('Choose to exclude ' . $name, 'multicache-plugin');
        $info_tag = __('Choose to exclude ' . $name, 'multicache-plugin');
        
        echo makeSelectButtonNumeric('multicache_config_options', 'excluded_components', $state, false, $title_tag, '0', '2', '1', $labels, $third_param,$class_adds);
        ?></div>
	<!-- closes control switch -->
</div>
<!-- closses group -->

<?php
    }

}

// css tweaks
function multicache_config_setting_css_switch()
{

    $options = get_option('multicache_config_options');
    $multicache_css_switch = $options['css_switch'];
    $title_tag = __('Choose to turn on css tweaker', 'multicache-plugin');
    $info_tag = __('Choose to turn on css tweaker.', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'css_switch', $multicache_css_switch);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_css_scrape_url_input()
{

    $options = get_option('multicache_config_options');
    $multicache_css_scrape_url = $options['css_scrape_url'];
    $title_tag = __('Scrape Url', 'multicache-plugin');
    $info_tag = __('Scrape Url', 'multicache-plugin');
    echo makeInputButton('multicache_config_options', 'css_scrape_url', $multicache_css_scrape_url, false, $title_tag);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_dedupe_css_styles_input()
{

    $options = get_option('multicache_config_options');
    $multicache_dedupe_css_styles = $options['dedupe_css_styles'];
    $title_tag = __('Choose to dedupe css links and styles', 'multicache-plugin');
    $info_tag = __('Choose to dedupe css links and styles', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'dedupe_css_styles', $multicache_dedupe_css_styles);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_css_maintain_preceedence_input()
{

    $options = get_option('multicache_config_options');
    $multicache_css_maintain_preceedence = $options['css_maintain_preceedence'];
    $title_tag = __('Maintain Preceedence', 'multicache-plugin');
    $info_tag = __('Maintain Preceedence.', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'css_maintain_preceedence', $multicache_css_maintain_preceedence);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_group_css_styles_input()
{

    $options = get_option('multicache_config_options');
    $multicache_group_css_styles = $options['group_css_styles'];
    $title_tag = __('Choose to set debug mode', 'multicache-plugin');
    $info_tag = __('Choose to set debug mode. In this mode fastcache debug will be set and will log debug queries to error_log. In advanced simulation mode the load instruction will be printed on the top of the site. Do not use this mode in production.', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'group_css_styles', $multicache_group_css_styles);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_compress_css_input()
{

    $options = get_option('multicache_config_options');
    $multicache_compress_css = $options['compress_css'];
    $title_tag = __('Choose to minify css', 'multicache-plugin');
    $info_tag = __('Choose to minify css.', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'compress_css', $multicache_compress_css);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_css_special_identifiers_input()
{

    $options = get_option('multicache_config_options');
    $multicache_css_special_identifiers = $options['css_special_identifiers'];
    $multicache_css_special_identifiers =MulticacheHelper::decodePrepareMulticacheTextAreas($multicache_css_special_identifiers);
    $title_tag = __('Specify the query param and value', 'multicache-plugin');
    $info_tag = __('Specify the query param and value', 'multicache-plugin');
    echo makeTextButton('multicache_config_options', 'css_special_identifiers', $multicache_css_special_identifiers);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_css_comments_input()
{

    $options = get_option('multicache_config_options');
    $multicache_css_comments = $options['css_comments'];
    $title_tag = __('Choose to include comments', 'multicache-plugin');
    $info_tag = __('Choose to include comments to help debug', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'css_comments', $multicache_css_comments);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_css_groupsasync_input()
{
	$options = get_option('multicache_config_options');
	$multicache_css_groupsasync = $options['css_groupsasync'];
	$title_tag = __('load combined css asynchronously', 'multicache-plugin');
	$info_tag = __('load combined css asynchronously', 'multicache-plugin');
	echo makeRadioButton('multicache_config_options', 'css_groupsasync', $multicache_css_groupsasync);
	
	?>
	<span class="glyphicon glyphicon-info-sign"
		title="<?php echo $info_tag;?>"> </span>
	
	<?php
		
}
function multicache_config_setting_orphaned_styles_loading_input()
{

    $options = get_option('multicache_config_options');
    $multicache_orphaned_styles_loading = $options['orphaned_styles_loading'];
    
    $title_tag = __('Choose where to load orphaned scripts.', 'multicache-plugin');
    $info_tag = __('Choose where to load orphaned scripts.', 'multicache-plugin');
    
    echo makeSelectButtonNumeric('multicache_config_options', 'orphaned_styles_loading', $multicache_orphaned_styles_loading, false, $title_tag, '0', '4', '1', array(
        0 => 'Default',
        1 => 'Head Open',
        2 => 'Head',
        3 => 'Body',
        4 => 'Footer'
    ));
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}
// css exclusions
function multicache_config_setting_css_tweaker_url_include_exclude_input()
{

      $options = get_option('multicache_config_options');
    $multicache_css_tweaker_url_include_exclude = $options['css_tweaker_url_include_exclude'];
    $title_tag = __('Choose to apply css tweaker settings to a few urls or exclude from a fw urls', 'multicache-plugin');
    $info_tag = __('Choose to apply css tweaker settings to a few urls or exclude from a fw urls.', 'multicache-plugin');
    echo makeMultiRadioButton('multicache_config_options', 'css_tweaker_url_include_exclude', $multicache_css_tweaker_url_include_exclude, array(
        0 => 'All',
        1 => 'These Pages',
        2 => 'Not These Pages'
    ), 3);
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_css_urlinclude_input()
{

    $options = get_option('multicache_config_options');
    $multicache_css_urlinclude = $options['css_urlinclude'];
    $multicache_css_urlinclude =MulticacheHelper::decodePrepareMulticacheTextAreas($multicache_css_urlinclude);
    $title_tag = __('Specify the complete page url', 'multicache-plugin');
    $info_tag = __('Specify the complete page url. eg. http://mysite.com or http://www.mysite.com', 'multicache-plugin');
    echo makeTextButton('multicache_config_options', 'css_urlinclude', $multicache_css_urlinclude , 3 , 20 );
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_css_query_include_exclude_input()
{

      $options = get_option('multicache_config_options');
    $multicache_css_query_include_exclude = $options['css_query_include_exclude'];
    $title_tag = __('Choose to apply css tweaker settings to a few query param urls or exclude from them', 'multicache-plugin');
    $info_tag = __('Choose to apply css tweaker settings to a few query param urls or exclude from them.', 'multicache-plugin');
    echo makeMultiRadioButton('multicache_config_options', 'css_query_include_exclude', $multicache_css_query_include_exclude, array(
        0 => 'Off',
        1 => 'Inclusion',
        2 => 'Exclusion'
    ), 3);
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_css_query_param_input()
{

    $options = get_option('multicache_config_options');
    $multicache_css_query_param = $options['css_query_param'];
    $multicache_css_query_param =MulticacheHelper::decodePrepareMulticacheTextAreas($multicache_css_query_param);
    $title_tag = __('Choose to include or exclude query params from css tweaks', 'multicache-plugin');
    $info_tag = __('Choose to apply these settings to pages with a few query params or exclude from pages with a few query params', 'multicache-plugin');
    echo makeTextButton('multicache_config_options', 'css_query_param', $multicache_css_query_param , 3 , 20);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_css_url_string_input()
{

    $options = get_option('multicache_config_options');
    $multicache_css_url_string = $options['css_url_string'];
    $multicache_css_url_string =MulticacheHelper::decodePrepareMulticacheTextAreas($multicache_css_url_string);
    $title_tag = __('Specify any part of a url that has to be excluded in css tweaks ', 'multicache-plugin');
    $info_tag = __('Specify any part of a url that has to be excluded in css tweaks', 'multicache-plugin');
    echo makeTextButton('multicache_config_options', 'css_url_string', $multicache_css_url_string , 3 , 20);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_page_css_inclusion_plugin_section_html()
{

	if (! function_exists('get_plugins'))
	{
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$all_plugins = get_plugins();
	 
	foreach ($all_plugins as $path => $plugin)
	{
		$display_name = $plugin["Name"];
		$name = preg_replace('~[^a-zA-Z0-9]~','_',trim(strtolower($display_name)));
		$p_uri = plugins_url('', $path);
		$plugins_urls_path[$name] = array(
				'path_uri' => $p_uri,
				'dir' => $path,
				'rel_path' => str_ireplace(MulticacheUri::root(), '', $p_uri),
				'display_name' => $display_name
		);
	}
	// $plugins_urls_path
	$options = get_option('multicache_config_options');
	$options = unserialize($options["cssexcluded_components"]);

	$labels = array(
			0 => 'default',
			1 => 'Exclude Script',
			2 => 'Exclude Page'
	);
	foreach ($plugins_urls_path as $name => $plug)
	{
		if ($name == 'multicachewp')
		{
			Continue;
		}
		?>
<div class="exclude-group col-md-10">
	<div class="exclude-label col-md-6"><?php echo  $plug["display_name"];?></div>
	<div class="exclude-switch col-md-4">
        <?php
        $state = isset($options[$name]) ? $options[$name]['value'] : 0;
        $class_adds = " css_excludes";
        $third_param = $name;
        $title_tag = __('Choose to exclude ' . $name, 'multicache-plugin');
        $info_tag = __('Choose to exclude ' . $name, 'multicache-plugin');
        
        echo makeSelectButtonNumeric('multicache_config_options', 'cssexcluded_components', $state, false, $title_tag, '0', '2', '1', $labels, $third_param,$class_adds);
        ?></div>
	<!-- closes control switch -->
</div>
<!-- closses group -->

<?php
    }

}

// lazy load
function multicache_config_setting_image_lazy_switch_input()
{

    $options = get_option('multicache_config_options');
    $multicache_image_lazy_switch = $options['image_lazy_switch'];
    $title_tag = __('Choose to turn on lazyloading', 'multicache-plugin');
    $info_tag = __('Choose to turn on lazyloading.', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'image_lazy_switch', $multicache_image_lazy_switch);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_image_lazy_image_selector_include_switch()
{

    $options = get_option('multicache_config_options');
    $multicache_image_lazy_image_selector_include_switch = $options['image_lazy_image_selector_include_switch'];
    $title_tag = __("Choose to include images by id or class", 'multicache-plugin');
    $info_tag = __("Choose to include images by id or class", 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'image_lazy_image_selector_include_switch', $multicache_image_lazy_image_selector_include_switch);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_image_lazy_image_selector_include_strings_input()
{

    $options = get_option('multicache_config_options');
    $multicache_image_lazy_image_selector_include_strings = $options['image_lazy_image_selector_include_strings'];
    $multicache_image_lazy_image_selector_include_strings =MulticacheHelper::decodePrepareMulticacheTextAreas($multicache_image_lazy_image_selector_include_strings);
    $title_tag = __("Specify image id's or classes to be included", 'multicache-plugin');
    $info_tag = __('Specify the query param and value', 'multicache-plugin');
    echo makeTextButton('multicache_config_options', 'image_lazy_image_selector_include_strings', $multicache_image_lazy_image_selector_include_strings , 3 , 20);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_image_lazy_image_selector_exclude_switch_input()
{

    $options = get_option('multicache_config_options');
    $multicache_image_selector_exclude_switch = $options['image_lazy_image_selector_exclude_switch'];
    $title_tag = __('Choose to exclude images by id or class', 'multicache-plugin');
    $info_tag = __('Choose to exclude images by id or class', 'multicache-plugin');
    echo makeRadioButton('multicache_config_options', 'image_lazy_image_selector_exclude_switch', $multicache_image_selector_exclude_switch);
    
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_image_lazy_image_selector_exclude_strings_input()
{

    $options = get_option('multicache_config_options');
    $multicache_image_lazy_image_selector_exclude_strings = $options['image_lazy_image_selector_exclude_strings'];
    $multicache_image_lazy_image_selector_exclude_strings =MulticacheHelper::decodePrepareMulticacheTextAreas($multicache_image_lazy_image_selector_exclude_strings);
    $title_tag = __("Specify the id's or classes that should not be lazy loaded", 'multicache-plugin');
    $info_tag = __("Specify the id's or classes that should not be lazy loaded", 'multicache-plugin');
    echo makeTextButton('multicache_config_options', 'image_lazy_image_selector_exclude_strings', $multicache_image_lazy_image_selector_exclude_strings ,3 ,20);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_imagestweaker_url_include_exclude_input()
{

      $options = get_option('multicache_config_options');
    $multicache_imagestweaker_url_include_exclude = $options['imagestweaker_url_include_exclude'];
    $title_tag = __('Choose to apply lazyload to only a few urls or exclude from a few urls', 'multicache-plugin');
    $info_tag = __('Choose to apply lazyload to only a few urls or exclude from a few urls.', 'multicache-plugin');
    echo makeMultiRadioButton('multicache_config_options', 'imagestweaker_url_include_exclude', $multicache_imagestweaker_url_include_exclude, array(
        0 => 'All',
        1 => 'These Pages',
        2 => 'Not These Pages'
    ), 3);
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_images_urlinclude_input()
{

    $options = get_option('multicache_config_options');
    $multicache_images_urlinclude = $options['images_urlinclude'];
    $multicache_images_urlinclude =MulticacheHelper::decodePrepareMulticacheTextAreas($multicache_images_urlinclude);
    $title_tag = __('Specify the complete url', 'multicache-plugin');
    $info_tag = __('Specify the complete url eg.http://mysite.com', 'multicache-plugin');
    echo makeTextButton('multicache_config_options', 'images_urlinclude', $multicache_images_urlinclude , 3 , 20);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_images_query_include_exclude_input()
{

      $options = get_option('multicache_config_options');
    $multicache_images_query_include_exclude = $options['images_query_include_exclude'];
    $title_tag = __('Choose to apply lazyload exclusions to a few params or exclude from a few params', 'multicache-plugin');
    $info_tag = __('Choose to apply lazyload exclusions to a few params or exclude from a few params.', 'multicache-plugin');
    echo makeMultiRadioButton('multicache_config_options', 'images_query_include_exclude', $multicache_images_query_include_exclude, array(
        0 => 'Off',
        1 => 'Inclusion',
        2 => 'Exclusion'
    ), 3);
    ?>
<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>

<?php

}

function multicache_config_setting_images_query_param_input()
{

    $options = get_option('multicache_config_options');
    $multicache_images_query_param = $options['images_query_param'];
    $multicache_images_query_param =MulticacheHelper::decodePrepareMulticacheTextAreas($multicache_images_query_param);
    $title_tag = __('Specify the query param and value', 'multicache-plugin');
    $info_tag = __('Specify the query param and value', 'multicache-plugin');
    echo makeTextButton('multicache_config_options', 'images_query_param', $multicache_images_query_param , 3 , 20);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}

function multicache_config_setting_images_url_string_input()
{

    $options = get_option('multicache_config_options');
    $multicache_images_url_string = $options['images_url_string'];
    $multicache_images_url_string =MulticacheHelper::decodePrepareMulticacheTextAreas($multicache_images_url_string);
    $title_tag = __('Specify the url string to be excluded', 'multicache-plugin');
    $info_tag = __('Specify the url string to be excluded', 'multicache-plugin');
    echo makeTextButton('multicache_config_options', 'images_url_string', $multicache_images_url_string , 3 , 20);
    ?>

<span class="glyphicon glyphicon-info-sign"
	title="<?php echo $info_tag;?>"> </span>
<?php

}




function multicache_config_page_image_tweaks_plugin_exclusions_section_html()
{

	if (! function_exists('get_plugins'))
	{
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$all_plugins = get_plugins();
	 
	foreach ($all_plugins as $path => $plugin)
	{
		$display_name = $plugin["Name"];
		$name = preg_replace('~[^a-zA-Z0-9]~','_',trim(strtolower($display_name)));
		$p_uri = plugins_url('', $path);
		$plugins_urls_path[$name] = array(
				'path_uri' => $p_uri,
				'dir' => $path,
				'rel_path' => str_ireplace(MulticacheUri::root(), '', $p_uri),
				'display_name' => $display_name
		);
	}
	// $plugins_urls_path
	$options = get_option('multicache_config_options');
	$options = unserialize($options["imgexcluded_components"]);

	$labels = array(
			0 => 'default',
			1 => 'Exclude Script',
			2 => 'Exclude Page'
	);
	foreach ($plugins_urls_path as $name => $plug)
	{
		if ($name == 'multicachewp')
		{
			Continue;
		}
		?>
<div class="exclude-group col-md-10">
	<div class="exclude-label col-md-6"><?php echo  $plug["display_name"];?></div>
	<div class="exclude-switch col-md-4">
        <?php
        $state = isset($options[$name]) ? $options[$name]['value'] : 0;
        $class_adds = " img_excludes";
        $third_param = $name;
        $title_tag = __('Choose to exclude ' . $name, 'multicache-plugin');
        $info_tag = __('Choose to exclude ' . $name, 'multicache-plugin');
        
        echo makeSelectButtonNumeric('multicache_config_options', 'imgexcluded_components', $state, false, $title_tag, '0', '2', '1', $labels, $third_param,$class_adds);
        ?></div>
	<!-- closes control switch -->
</div>
<!-- closses group -->

<?php
    }

}

// stop