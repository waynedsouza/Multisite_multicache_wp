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

if(file_exists(plugin_dir_path(__FILE__).'multicache_storage_temp.php'))
{
	require_once plugin_dir_path(__FILE__).'multicache_storage_temp.php';
}
/*
if(file_exists(plugin_dir_path(__FILE__).'pagescripts.php'))
{
	require_once plugin_dir_path(__FILE__).'pagescripts.php';
}
if(file_exists(plugin_dir_path(__FILE__).'pagecss.php'))
{
	require_once plugin_dir_path(__FILE__).'pagecss.php';
}
*/

if(file_exists(plugin_dir_path(dirname(__FILE__)).'simcontrol/libs/multicache_loadinstruction.php'))
{
	require_once plugin_dir_path(dirname(__FILE__)).'simcontrol/libs/multicache_loadinstruction.php';
}

require_once plugin_dir_path(dirname(__FILE__)).'simcontrol/simcontrol.php';
require_once plugin_dir_path(__FILE__).'compression_libs/multicachecssoptimize.php';
require_once plugin_dir_path(__FILE__).'compression_libs/multicachejsoptimize.php';
//JLoader::import('simcontrol', JPATH_ROOT . '/components/com_multicache/models');

class MulticacheStrategy
{
	protected static $_principle_jquery_scope = null;
	protected static $instance = null;
	protected static $_jscomments = null;
    protected static $_css_comments = null; 
    protected static $_mediaVersion = null;
    protected static $_excluded_components = null;
    protected static $_excluded_components_css = null;
    protected static $_excluded_components_img = null;
    protected static $_signature_hash_css = null;                     
    protected static $_duplicates_css = null;
    protected static $_delayable_segment_css = null;
    protected static $_delayable_segment = null;
    protected static $_groups_css = null;
    protected static $_atimports_prop = null;
    protected static $_temp_group = null;
    protected static $_cdn_segment = null;
    protected static $_cdn_segment_css = null;
    protected static $_delayed_noscript = null;
    protected static $_groups_loaded_css = null;
    protected static $_loadsections_css = null;
    protected static $_signature_hash = null;
    protected static $_social_segment = null;
    protected static $_duplicates = null;
    protected static $_advertisement_segment = null;
    protected static $_async_segment = null;
    protected static $_groups = null;
    protected static $_loadsections = null;
    protected static $_groups_loaded = null;
    protected static $_promises = null;
	protected $error_log = null; 
	
	//version1/0.0.2
	protected static $_dontmovesignature_hash = null;	
	protected static $_dontmoveurls = null;	
	protected static $_dontmove_items = null;
	protected static $_unset_hash = null;
	
	const DOUBLE_QUOTE_STRING = '"(?>(?:\\\\.)?[^\\\\"]*+)+?(?:"|(?=$))';
	// regex for single quoted string
	const SINGLE_QUOTE_STRING = "'(?>(?:\\\\.)?[^\\\\']*+)+?(?:'|(?=$))";
	// regex for block comments
	const BLOCK_COMMENTS = '/\*(?>[^/\*]++|//|\*(?!/)|(?<!\*)/)*+\*/';
	// regex for line comments
	const LINE_COMMENTS = '//[^\r\n]*+';
	
	const URI = '(?<=url)\([^)]*+\)';
	
	public function construct()
	{
		$this->debug = true;
		$this->error_log = 'strategy-error';
	}
	
	public static function getInstance()
	{
		// Only create the object if it doesn't exist.
		if (empty(self::$instance))
		{
	
			self::$instance = new MulticacheStrategy();
		}
		return self::$instance;
	
	}
	
	public function initialise($input_array)
	{
	
		//$app = JFactory::getApplication();
		$input = MulticacheHelper::toObject($input_array);
		$options = get_option('multicache_config_options');
		$options_system_params = get_option('multicache_system_params');
		$options_system_params['advanced_simulation_lock']= empty($input->advanced_simulation_lock)? false: true;
		
		//wp_advanced_cache
		if(!empty($input->indexhack))
		{
			MulticacheHelper::prepareAdvancedCacheforInstall();
		}
		else 
		{
			MulticacheHelper::checkAdvancedCacheSetting();
		}
		
		
		$last_test = $this->getLastTest();
		
		
	
		if (! empty($last_test) && $input->gtmetrix_api_budget != $last_test->max_tests )
		{
			$this->resetMaxTests($input->gtmetrix_api_budget, $last_test->id);
		}
		
		$last_testgroup = $this->getlastTestGroup();
		if (! empty($last_testgroup) && $input->gtmetrix_cycles != $last_testgroup->cycles)
		{
			if ($last_testgroup->cycles_complete < $input->gtmetrix_cycles)
			{
				$this->resetGroupCycles($input, $last_testgroup->id);
			}
			else
			{
				MulticacheHelper::prepareMessageEnqueue(__('Cannot reset simulation at this stage','multicache-plugin'));
				if($this->debug)
				{
					MulticacheHelper::log_error(__('Cannot reset simulation at this stage','multicache-plugin'),$this->error_log);
				}
			}
		}
	
		$input->simulation_advanced = empty($input->simulation_advanced) ? 0 : 1;
	
		$bypass = 1;
		//lets assume at this stage that multicache plugin config resides in options table 
		//multicache_system_params
		
		$sim_lock = $options_system_params['lock_sim_control'];
		//$sim_lock = json_decode(JPluginHelper::getPlugin('system', 'multicache')->params)->lock_sim_control;
		if (! empty($sim_lock))
		{
			$bypass = 0;
			if (empty($input->advanced_simulation_lock) && ! empty($input->simulation_advanced))
			{
				$bypass = 1;
				MulticacheHelper::prepareMessageEnqueue(__('Config is devolving changes to run in advanced tests mode'),'updated');
				if($this->debug)
				{
					MulticacheHelper::log_error(__('Config is devolving changes to run in advanced tests mode','multicache-plugin'),$this->error_log);
				}
				
			}
		}
		$debug_mode = ! empty($input->debug_mode) ? serialize($input->default_scrape_url) : null;
	
		MulticacheHelper::setJsSwitch($input , $input->js_switch, $input->conduit_switch, $input->gtmetrix_testing, $input->simulation_advanced, $input->js_comments, $debug_mode, $input->orphaned_scripts, $input->css_switch, $input->css_comments, $input->compress_css, $input->minify_html, $input->compress_js, $input->orphaned_styles_loading, $input->image_lazy_switch);
	//testing area start
		
		
	//stop
		if (! isset(self::$_principle_jquery_scope))
		{
			$this->setprincipleJqueryscopeoperator($input);
		}
	
		if (! isset(self::$_jscomments))
		{
			self::$_jscomments = $input->js_comments;
		}
		if (! isset(self::$_css_comments))
		{
			self::$_css_comments = $input->css_comments;
		}
		self::$_mediaVersion = MulticacheHelper::getMediaFormat();
		/*
		if (empty($input->id))
		{
			$jinput = JFactory::getApplication()->input;
			if (empty($jinput))
			{
				$input->id = $jinput->getInt('id');
			}
			else
			{
				$input->id = 1;
			}
		}
		*/
		// basic requirements for testing
		if ($input->gtmetrix_testing)
		{
			if (empty($input->gtmetrix_email))
			{
				MulticacheHelper::prepareMessageEnqueue(__('GtMetrix email is absent','multicache-plugin'));
				if($this->debug)
				{
					MulticacheHelper::log_error('GtMetrix email is absent',$this->error_log);
				}
				//$app->enqueueMessage(JText::_('COM_MULTICACHE_CONFIG_GTMETRIX_EMAIL_ABSENT'), 'notice');
			}
			if (empty($input->gtmetrix_token))
			{
				MulticacheHelper::prepareMessageEnqueue(__('GtMetrix token is absent','multicache-plugin'));
				if($this->debug)
				{
					MulticacheHelper::log_error('GtMetrix token is absent',$this->error_log);
				}
				//$app->enqueueMessage(JText::_('COM_MULTICACHE_CONFIG_GTMETRIX_TOKEN_ABSENT'), 'notice');
			}
		}
		/*
		 * place for template vary
		 */
	
		if ($input->gtmetrix_allow_simulation)
		{
	
			$base_url = strtolower(substr(MulticacheUri::root(), 0, - 1));
			$test_url = strtolower($input->gtmetrix_test_url);
			$same_domain = stripos($test_url, $base_url);
			if ($test_url == $base_url)
			{
				$input->gtmetrix_test_url = strtolower(MulticacheUri::root());
			}
			if ( $same_domain === false)
			{
				$input->gtmetrix_allow_simulation = 0;
				MulticacheHelper::prepareMessageEnqueue(__('Simulation turned off. Error url differs from domain','multicache-plugin'));
				if($this->debug)
				{
					MulticacheHelper::log_error('Simulation turned off. Error url differs from domain',$this->error_log);
				}
				
				//$app->enqueueMessage(JText::_('COM_MULTICACHE_URL_DIFFERS_FROM_DOMAIN_SIMULATION_TURNED_OFF'), 'error');
			}
		}
	//$return = MulticacheHelper::toArray($input);
	//Return $return;
		if (substr($input->googleviewid, 0, 3) != "ga:" && preg_match('/^[0-9]*$/', $input->googleviewid, $match))
		{
	
			$input->googleviewid = "ga:" . $input->googleviewid;
		}
	/*
		if (! empty($input->social_script_identifiers))
		{
			$social_script_raw = $input->social_script_identifiers;
			$social_script_array = preg_split('/[\s,\n]+/', $social_script_raw);
			$input->social_script_identifiers = json_encode($social_script_array);
		}
		if (! empty($input->advertisement_script_identifiers))
		{
			$advertisement_script_raw = $input->advertisement_script_identifiers;
			$advertisement_script_array = preg_split('/[\s,\n]+/', $advertisement_script_raw);
			$input->advertisement_script_identifiers = json_encode($advertisement_script_array);
		}
	*/
		/*
		if (! empty($input->pre_head_stub_identifiers))
		{
			$pre_head_stub_identifier_raw = $input->pre_head_stub_identifiers;
			// $pre_head_stub_identifier_array = preg_split('/>/'.iUs , $pre_head_stub_identifier_raw ,PREG_SPLIT_DELIM_CAPTURE);
			$pre_head_stub_identifier_array = $this->clean_stub(explode('>', $pre_head_stub_identifier_raw));
			// $pre_head_stub_identifier_array = $this->clean_stub($pre_head_stub_identifier_array);
	
			$input->pre_head_stub_identifiers = json_encode($pre_head_stub_identifier_array);
		}
		if (! empty($input->head_stub_identifiers))
		{
			$head_stub_identifier_raw = $input->head_stub_identifiers;
			// $head_stub_identifier_array = preg_split('/[\s,\n]+/' , $head_stub_identifier_raw);
			$head_stub_identifier_array = $this->clean_stub(explode('>', $head_stub_identifier_raw));
			$input->head_stub_identifiers = json_encode($head_stub_identifier_array);
		}
		if (! empty($input->body_stub_identifiers))
		{
			$body_stub_identifier_raw = $input->body_stub_identifiers;
			// $body_stub_identifier_array = preg_split('/[\s,\n]+/' , $body_stub_identifier_raw);
			$body_stub_identifier_array = $this->clean_stub(explode('>', $body_stub_identifier_raw));
			$input->body_stub_identifiers = json_encode($body_stub_identifier_array);
		}
		if (! empty($input->footer_stub_identifiers))
		{
			$footer_stub_identifiers_raw = $input->footer_stub_identifiers;
			// $footer_stub_identifier_array = preg_split('/[\s,\n]+/' , $footer_stub_identifiers_raw);
			$footer_stub_identifier_array = $this->clean_stub(explode('>', $footer_stub_identifiers_raw));
			$input->footer_stub_identifiers = json_encode($footer_stub_identifier_array);
		}
	
		
		if (! empty($input->additionalpagecacheurls))
		{
			$base_url = strtolower(str_ireplace(array(
					'http://',
					'www.'
			), '', substr(MulticacheUri::root(), 0, - 1)));
			$urls_raw = $input->additionalpagecacheurls;
			$url_array = preg_split('/[\s,\n]+/', $urls_raw);
			foreach ($url_array as $key => $url)
			{
				$exists = $this->checkUrldburlArray($url, 'google');
				if ($exists || ! stristr($url, $base_url))
				{
					unset($url_array[$key]);
				}
			}
			$this->clearTable();
			$url_string = json_encode($url_array); // this is stored to config
	
			foreach ($url_array as $key => $url)
			{
				$exists = $this->checkUrldburlArray($url, 'manual');
	
				if (! $exists && stristr($url, $base_url))
				{
	
					$this->storeUrlArray($url);
				}
			}
	
			$input->additionalpagecacheurls = $url_string;
		}
		// start
		
		
		if (! empty($input->css_special_identifiers))
		{
			$css_special_identifiers_raw = $input->css_special_identifiers;
			$css_special_identifiers_array = preg_split('/[\s,\n]+/', $css_special_identifiers_raw);
			$input->css_special_identifiers = json_encode($css_special_identifiers_array);
		}
		// stop
	*/
		
		//return MulticacheHelper::toArray($input);
		
		if (! empty($input->cartmodeurlinclude_raw))
		{
			$carturls_array = preg_split('/[\s,\n]+/', $input->cartmodeurlinclude_raw);
			$carturls_array = MulticacheHelper::makeWinSafeArray($carturls_array);
			//$input->cartmodeurlinclude = json_encode($carturls_array);
		}
		/*
		if (! empty($input->cartsessionvariables))
		{
			$cart_session_var_array = preg_split('/[\s\n]+/', $input->cartsessionvariables);
			$input->cartsessionvariables = json_encode($cart_session_var_array);
		}
	
		if (! empty($input->cartdifferentiators))
		{
			$cart_diff_var_array = preg_split('/[\s\n,]+/', $input->cartdifferentiators);
			$input->cartdifferentiators = json_encode($cart_diff_var_array);
		}
	*/
		MulticacheHelper::prepareCartObject($carturls_array, $input->countryseg, null, $input->cartmode, $input->multicachedistribution);
		
		
		if (! empty($input->jst_urlinclude_raw))
		{
			$jst_urlinclude = preg_split('/[\s\n,]+/', $input->jst_urlinclude_raw);
			$jst_urlinclude = ! empty($jst_urlinclude) ? array_filter($jst_urlinclude) : $jst_urlinclude;
			$jst_urlinclude = MulticacheHelper::makeWinSafeArray($jst_urlinclude);
			//$input->jst_urlinclude = json_encode($jst_urlinclude);
		}
		
		if (! empty($input->jst_query_param_raw))
		{
			$jst_query_param = preg_split('/[\s\n,]+/', $input->jst_query_param_raw);
			$jst_query_param = ! empty($jst_query_param) ? array_filter($jst_query_param) : $jst_query_param;
			$jst_query_param = MulticacheHelper::makeWinSafeArray($jst_query_param);
			//$input->jst_query_param = json_encode($jst_query_param);
		}
		
		if (! empty($input->jst_url_string_raw))
		{
			$jst_url_string = preg_split('/[\s\n,]+/', $input->jst_url_string_raw);
			$jst_url_string = ! empty($jst_url_string) ? array_filter($jst_url_string) : $jst_url_string;
			$jst_url_string = MulticacheHelper::makeWinSafeArray($jst_url_string);
			//$input->jst_url_string = json_encode($jst_url_string);
		}
		
		// css start
		if (! empty($input->css_urlinclude_raw))
		{
			$css_urlinclude = preg_split('/[\s\n,]+/', $input->css_urlinclude_raw);
			$css_urlinclude = ! empty($css_urlinclude) ? array_filter($css_urlinclude) : $css_urlinclude;
			$css_urlinclude = MulticacheHelper::makeWinSafeArray($css_urlinclude);
			//$input->css_urlinclude = json_encode($css_urlinclude);
		}
		if (! empty($input->css_query_param_raw))
		{
			$css_query_param = preg_split('/[\s\n,]+/', $input->css_query_param_raw);
			$css_query_param = ! empty($css_query_param) ? array_filter($css_query_param) : $css_query_param;
			$css_query_param = MulticacheHelper::makeWinSafeArray($css_query_param);
			//$input->css_query_param = json_encode($css_query_param);
		}
		if (! empty($input->css_url_string_raw))
		{
			$css_url_string = preg_split('/[\s\n,]+/', $input->css_url_string_raw);
			$css_url_string = ! empty($css_url_string) ? array_filter($css_url_string) : $css_url_string;
			$css_url_string = MulticacheHelper::makeWinSafeArray($css_url_string);
			//$input->css_url_string = json_encode($css_url_string);
		}
		// css end
		// img start
		if (! empty($input->images_urlinclude_raw))
		{
			$img_urlinclude = preg_split('/[\s\n,]+/', $input->images_urlinclude_raw);
			$img_urlinclude = ! empty($img_urlinclude) ? array_filter($img_urlinclude) : $img_urlinclude;
			$img_urlinclude = MulticacheHelper::makeWinSafeArray($img_urlinclude);
			//$input->images_urlinclude = json_encode($img_urlinclude);
		}
		if (! empty($input->images_query_param_raw))
		{
			$img_query_param = preg_split('/[\s\n,]+/', $input->images_query_param_raw);
			$img_query_param = ! empty($img_query_param) ? array_filter($img_query_param) : $img_query_param;
			$img_query_param = MulticacheHelper::makeWinSafeArray($img_query_param);
			//$input->images_query_param = json_encode($img_query_param);
		}
		if (! empty($input->images_url_string_raw))
		{
			$img_url_string = preg_split('/[\s\n,]+/', $input->images_url_string_raw);
			$img_url_string = ! empty($img_url_string) ? array_filter($img_url_string) : $img_url_string;
			$img_url_string = MulticacheHelper::makeWinSafeArray($img_url_string);
			//$input->images_url_string = json_encode($img_url_string);
		}
		// img end
		// img params begin
		if (! empty($input->image_lazy_container_strings_raw))
		{
			// this will only accept newline to differentiate
			$image_lazy_container_strings = preg_split('/[\n]+/', $input->image_lazy_container_strings_raw);
			$image_lazy_container_strings = ! empty($image_lazy_container_strings) ? array_filter($image_lazy_container_strings) : $image_lazy_container_strings;
			$image_lazy_container_strings = MulticacheHelper::makeWinSafeArray($image_lazy_container_strings);
			//$input->image_lazy_container_strings = json_encode($image_lazy_container_strings);
		}
		if (! empty($input->image_lazy_image_selector_include_strings_raw))
		{
			// this will only accept newline to differentiate
			$image_lazy_image_selector_include_strings = preg_split('/[\n]+/', $input->image_lazy_image_selector_include_strings_raw);
			$image_lazy_image_selector_include_strings = ! empty($image_lazy_image_selector_include_strings) ? array_filter($image_lazy_image_selector_include_strings) : $image_lazy_image_selector_include_strings;
			$image_lazy_image_selector_include_strings = MulticacheHelper::makeWinSafeArray($image_lazy_image_selector_include_strings);
			//$input->image_lazy_image_selector_include_strings = json_encode($image_lazy_image_selector_include_strings);
		}
		if (! empty($input->image_lazy_image_selector_exclude_strings_raw))
		{
			// this will only accept newline to differentiate
			$image_lazy_image_selector_exclude_strings = preg_split('/[\n]+/', $input->image_lazy_image_selector_exclude_strings_raw);
			$image_lazy_image_selector_exclude_strings = ! empty($image_lazy_image_selector_exclude_strings) ? array_filter($image_lazy_image_selector_exclude_strings) : $image_lazy_image_selector_exclude_strings;
			$image_lazy_image_selector_exclude_strings = MulticacheHelper::makeWinSafeArray($image_lazy_image_selector_exclude_strings);
			//$input->image_lazy_image_selector_exclude_strings = json_encode($image_lazy_image_selector_exclude_strings);
		}
		//version1.0.0.2
		//positional_dontmovesrc
		if (! empty($input->positional_dontmovesrc_raw))
		{
			$positional_dontmovesrc = preg_split('/[\s\n,]+/', $input->positional_dontmovesrc_raw);
			$positional_dontmovesrc = ! empty($positional_dontmovesrc) ? array_filter($positional_dontmovesrc) : $positional_dontmovesrc;
			//$input->jst_urlinclude = json_encode($jst_urlinclude);
		}
		
		if (! empty($positional_dontmovesrc))
		{
			$positional_dontmovesrc = MulticacheHelper::checkPositionalUrls($positional_dontmovesrc);
			$positional_dontmovesrc = array_flip($positional_dontmovesrc);
			//this $_dontmoveurls will also be populated from setDontLoad
				self::$_dontmoveurls = $positional_dontmovesrc;
			
		}
		//version1.0.0.2
		//allow_multiple_orphaned
		if (! empty($input->allow_multiple_orphaned_raw))
		{
			$allow_multiple_orphaned = preg_split('/[\s\n,]+/', $input->allow_multiple_orphaned_raw);
			$allow_multiple_orphaned = ! empty($allow_multiple_orphaned) ? array_filter($allow_multiple_orphaned) : $allow_multiple_orphaned;
			//$input->jst_urlinclude = json_encode($jst_urlinclude);
			$allow_multiple_orphaned = MulticacheHelper::checkPositionalUrls($allow_multiple_orphaned);
			$allow_multiple_orphaned = (isset($allow_multiple_orphaned[0]) && $allow_multiple_orphaned[0] == - 1) ? - 1 : array_flip($allow_multiple_orphaned);
		}
		
		
		//version1.0.0.2
		//resultant_async
		
		//version1.0.0.2
		//resultant_defer
		
		//var_dump($input);exit;
		$extended_params = array();
		$extended_params['positional_dontmovesrc'] = isset($positional_dontmovesrc)? $positional_dontmovesrc : null;
		$extended_params['allow_multiple_orphaned'] = isset($allow_multiple_orphaned)? $allow_multiple_orphaned :null;
		$extended_params['resultant_async'] = isset($input->resultant_async_js) ? $input->resultant_async_js : null;
		$extended_params['resultant_defer'] = isset($input->resultant_defer_js) ? $input->resultant_defer_js : null;
		
		//css extended params
		$extended_params['css_groupsasync'] = isset($input->css_groupsasync) ? $input->css_groupsasync : null;
		
		
		
		$img_script_lazy = MulticacheHelper::prepareScriptLazy($input , self::$_principle_jquery_scope);
		$img_style_lazy = MulticacheHelper::prepareStylelazy();
		$params_lazyload = MulticacheHelper::prepareLazyloadParams($input, $img_script_lazy, $img_style_lazy);
		/*
		 * WP has a quirk compared to Joomla there is no way to add script declarations  
		 * to the head and ensure its loaded after the libraries. We will use a workaround.
		 * This is not that great a method but it ensures versatility of the script and style
		 * and does not add expensive regex. The quirk is thatstrategy will need to be rescraped to get 
		 * this in one load 
		 */
		MulticacheHelper::RegisterLazyload($img_script_lazy);
		MulticacheHelper::RegisterLazyload( $img_style_lazy ,'style');
		// img params end
		/*
		 * Te JSTexclude object allows for Javascript tweaker to exclude ceratin templates eg mobile templates
		 * or format vary
		 * urlswitch - 0 - all pages
		 * urlswitch - 1 - these pages
		 * urlswitch - 2 - not these pages
		 * queryswitch 0 - off
		 * query switch 1 - inclusion
		 * query switch 2 - exclusion
		*/
		$this->makeExcludedComponentslist($input); // initialize the self::$_excluded_components
		$jst_exclude_object = MulticacheHelper::PrepareJSTexcludes(
				$input->js_tweaker_url_include_exclude, 
				$input->jst_query_include_exclude,
				$jst_urlinclude,
				$jst_query_param, 
				self::$_excluded_components,
				$jst_url_string);
		$css_exclude_object = MulticacheHelper::PrepareJSTexcludes(
				$input->css_tweaker_url_include_exclude,
				$input->css_query_include_exclude,
				$css_urlinclude,
				$css_query_param, 
				self::$_excluded_components_css,
				$css_url_string
				);
		$img_exclude_object = MulticacheHelper::PrepareJSTexcludes(
				$input->imagestweaker_url_include_exclude,
				$input->images_query_include_exclude, 
				$img_urlinclude, 
				$img_query_param,
				self::$_excluded_components_img,
				$img_url_string
				);
		$input->excluded_components = ! empty(self::$_excluded_components) ? serialize(self::$_excluded_components) : null;
		$input->cssexcluded_components = ! empty(self::$_excluded_components_css) ? serialize(self::$_excluded_components_css) : null;
		$input->imgexcluded_components = ! empty(self::$_excluded_components_img) ? serialize(self::$_excluded_components_img) : null;
		
		 $this->prepare_Config($input);
		 $this->prepare_JSvars($input);
		 
		 if ($input->simulation_advanced && ! class_exists('MulticachePageScripts'))
		 {
	     MulticacheHelper::prepareMessageEnqueue(__('Advanced simulation requires javascript tweaks to be initialised','multicache-plugin'));
		 	//$app->enqueueMessage(JText::_('COM_MULTICACHE_ADVANCED_SIMULATION_REQUIRES_JAVASCRIPT_TWEAKER_TO_BE_INITIALISED'), 'notice');
		 }
		 if(is_multisite())
		 {
		 	$s_loc = MulticacheHelper::resolveMultiSiteStrategyloc();
		 	if($s_loc->simcontrol_location_check )
		 	{
		 		$f = $s_loc->simcontrol_location.'multicache_loadinstruction.php';
		 		if(is_file($f)  && ! class_exists('MulticacheLoadinstruction'))
		 		{
		 			require_once $f;
		 		}
		 		
		 	}
		 }
		 if ($input->simulation_advanced && ! class_exists('MulticacheLoadinstruction'))
		 {
		 	MulticacheHelper::prepareMessageEnqueue(__("Advanced simulation not initialized, please ensure the simcron url is cron'd","multicache-plugin"));
		 	//$app->enqueueMessage(JText::_('COM_MULTICACHE_ADVANCED_SIMULATION_REQUIRES_JAVASCRIPT_TWEAKER_TO_BE_INITIALISED'), 'notice');
		 }
		 $stubs = MulticacheHelper::prepareStubs($input);
		 
		 
		 
		
		// $prev_img_switch_state = property_exists('JsStrategy', 'img_switch') ? JsStrategy::$img_switch : null;
		 if (empty($input->css_switch) 
		 		&& empty($input->js_switch) 
		 		/*&& (! empty($input->image_lazy_switch) || (empty($input->image_lazy_switch) && !empty($prev_img_switch_state)) )*/)
		 {
		 	$return = MulticacheHelper::writeJsCacheStrategy(null, null, null, $stubs, null, null, null, null, null, $input->image_lazy_switch, $img_exclude_object, $params_lazyload);
		 }
		 
		 if ($input->css_switch /*&& $options['css_switch']*/)
		 {
		 	$this->performCssOptimization($input, $css_exclude_object, $img_exclude_object, $params_lazyload , $extended_params);
		 }
		 
		 if ($input->js_switch /*&& $options['js_switch']*/)
		 {
		 	
		 	$page_new_script = $this->prepareNonTableElements();
		 	
		 	if (empty($page_new_script))
		 	{
		 		MulticacheHelper::prepareMessageEnqueue(__('Javascript strategy could not be initialized, please ensure the template is scraped and initialized.','multicache-plugin'));
		 		if($this->debug)
		 		{
		 			MulticacheHelper::log_error(__('Javascript strategy could not be initialized, please ensure the template is scraped and initialized. ','multicache-plugin'),$this->error_log);
		 		}
	
		 		//$app->enqueueMessage(JText::_('COM_MULTICACHE_ERROR_PAGESCRIPT_PREPARENONTABLEELEMENTS_EMPTY'), 'warning');
		 		Return false;
		 	}
		 	$success = $this->validatePageScript($page_new_script, $input);
		 	
		 	if (! $success)
		 	{
		 		Return false;
		 	}
		 	$page_new_script = $this->setIgnore($page_new_script);
		 	$page_new_script = $this->setDontLoad($page_new_script);
		 	//ConfirmIgnoreDontLoad - missing check with Joomla
		 	//version1.0.0.2
		 	$page_new_script = $this->ConfirmIgnoreDontLoad($page_new_script);
		 	
		 	$page_new_script = $this->setCDNtosignature($page_new_script);
		 	$page_new_script = $this->setSignatureHash($page_new_script); // also sets duplicate flag
		 	
		 	/* NOTE IMPORTANT WE CALL TABLE->value instead of saved valiues as we want the immediate action selected versus the previous state */
		 	// theoretically this is where the ignore segs come in
		 	if ($input->maintain_preceedence)
		 	{
		 		$page_new_script = $this->performPreceedenceModeration($page_new_script);
		 	}
		 	/* DUPLICATE HANDLERS */
		 	if (! $input->dedupe_scripts)
		 	{
		 		$page_new_script = $this->placeDuplicatesBack($page_new_script);
		 	}
		 	if ($input->dedupe_scripts)
		 	{
		 		$page_new_script = $this->removeDuplicateScripts($page_new_script);
		 	}
		 	/* PREPARE DELAYABLE WITHOUT DELAY LAG FOR NON PRECEEDENCE */
		 	if (! $input->maintain_preceedence)
		 	{
		 		$page_new_script = $this->prepareDelayable($page_new_script, $input);
		 	}
		 	
		 	/* DEFER ADVERTISEMENTS - ads cannot be delayed unless specifically warranted */
		 	if ($input->defer_advertisement)
		 	{
		 		$page_new_script = $this->deferAdvertisement($page_new_script);
		 	}
		 	/* DEFER ASYNC - async cannot be delayed unless specifically waranted */
		 	if ($input->defer_async)
		 	{
		 		$page_new_script = $this->deferAsync($page_new_script);
		 	}
		 	/* PREPARE DELAYABLE WITH DELAY LAG FOR PRECEEDENCE */
		 	if ($input->maintain_preceedence)
		 	{
		 		$page_new_script = $this->prepareDelayable($page_new_script, $input);
		 	}
		 	
		 	/* DEFER SOCIAL */
		 	// A Social can be delayed heance it takes a lesser priority to delayable
	
		 	if ($input->defer_social)
		 	{
		 		$page_new_script = $this->deferSocial($page_new_script);
		 	}
		 	//version1.0.0.2 add self::$_dontmove_items
		 	// INTERCEPTION POINT
		 	// PoINT TO NOTE always pass storePageScripts with null as first value to maintain the original array. Conversely pass original array with all other values set to null to reset.
		 	MulticacheHelper::storePageScripts(null, $page_new_script, self::$_duplicates, self::$_social_segment, self::$_advertisement_segment, self::$_async_segment, self::$_delayable_segment , self::$_dontmove_items); //
		 	$simcontrol_object = array();
		 	$simcontrol_object['working_script_array'] = $page_new_script;
		 	$simcontrol_object['social'] = self::$_social_segment;
		 	$simcontrol_object['advertisements'] = self::$_advertisement_segment;
		 	$simcontrol_object['async'] = self::$_async_segment;
		 	$simcontrol_object['delayable'] = self::$_delayable_segment;
		 	$this->correctSignatureHash(self::$_advertisement_segment); // correction for 2nd time flow
		 	$this->correctSignatureHash(self::$_duplicates);
		 	$this->correctSignatureHash(self::$_social_segment);
		 	$this->correctSignatureHash(self::$_async_segment);
		 	$this->correctDelaySignatureHash(self::$_delayable_segment);
		 	
		 	// trial:
		 	$page_new_script = $this->moderateDefaultLoadsections($page_new_script); // optimizing for userability
		 	
		 	/*as of version1.0.1.3 we need to accomadate
		 	 * resultant async and resultant defers to this extent
		 	 * when these are set we wish to combine the delay callable to the group
		 	 * code in order that we need not use async timers.
		 	 * To this end the delay peice is performed before the minimize roundtrips
		 	 */
		 	$this->makeDelaycode();
		 	$this->segregatePlaceDelay($input);
		 	// end trial
		 	if ($input->minimize_roundtrips)
		 	{
		 		// GROUPS CONSIST OF ONLY INTERNAL SOURCE & PEICES OF CODE HENCE WE DO NOT alias the CDN's as it makes no sense to pull the entire source from a cdn //and paste it in an inline script defeating the very purpose of a CDN
		 		$page_new_script = $this->assignGroups($page_new_script);
		 		$this->initialiseGroupHash($page_new_script);
		 		$this->combineGroupCode($input);
		 		//ver1.0.0.3 ammend
		 		if(!empty($extended_params['resultant_async']) || !empty($extended_params['resultant_defer']))
		 		{
		 			$this->combineDelayloadUrlToGroup($input);
		 			if ($input->css_switch)
		 			{
		 				$this->combineCssDelayloadUrlToGroup($input);
		 			}
		 		}
		 		//version 1.0.0.2 added $extended_params
		 		$this->prepareGrouploadableUrl($extended_params);
		 		$this->writeGroupCode($input); // writes Group js scripts that will be loaded from JSCacheStrategy in operation mode flag $success if failed
		 	}
		 	//old coment marked for removal in next version-4
		 	// Although I dont see a benefit at this stage Delay will alias to CDN's
		 	/*
		 	$this->makeDelaycode();
		 	$this->segregatePlaceDelay($input);
		 	*/
		 	// $page_new_script = $this->moderateDefaultLoadsections($page_new_script);//all remaining defaults are moved to closing header tag int(2)
		 	//version1.0.0.2 need to pass $extended_params		 	
		 	$this->prepareLoadsections($page_new_script, $input , $extended_params);
		 	//
		 	$this->combineSectionFooter(self::$_advertisement_segment);
		 	$this->combineSectionFooter(self::$_social_segment);
		 	$this->combineSectionFooter(self::$_async_segment);
		 	$this->combineMAU();
		 	if ($input->conduit_switch)
		 	{
		 		// $this->combineConduitFooter();
		 	}
		 	//ver1.0.0.3
		 	
		 	$this->combineDelay($extended_params);
		 	
		 	if ($input->css_switch)
		 	{
		 		$this->combineCssDelayToScript($extended_params);
		 	}
		 	
		 	
		 	$return = MulticacheHelper::writeJsCacheStrategy(
		 			self::$_signature_hash,
		 			self::$_loadsections, 
		 			$input->js_switch, 
		 			$stubs, 
		 			$jst_exclude_object, 
		 			self::$_signature_hash_css, 
		 			self::$_loadsections_css, 
		 			$input->css_switch, 
		 			$css_exclude_object,
		 			$input->image_lazy_switch, 
		 			$img_exclude_object, 
		 			$params_lazyload ,
		 			self::$_dontmovesignature_hash,
		 			self::$_dontmoveurls, 
		 			/*self::$_allow_multiple_orphaned ,appended to extended params*/ 
		 			$extended_params
		 			);
	
		 	if (! empty($input->simulation_advanced) && !empty($input->gtmetrix_allow_simulation))
		 	{
		 		$this->prepareSimulationControl($simcontrol_object, $bypass);
		 	}
		 	
		 	Return MulticacheHelper::toArray($input);
		 }
	
		 else
		 {
		 	
		 	// $return = MulticacheHelper::writeJsCacheStrategy(null, null, $input->js_switch, $stubs, $jst_exclude_object, self::$_signature_hash_css, self::$_loadsections_css, $input->css_switch, $css_exclude_object, $input->image_lazy_switch, $img_exclude_object, $params_lazyload);
		 	/* becomes redudant when we inject the js_switch into plugin params lets maintain this for structure */
	
		 	if (! empty($input->simulation_advanced) && !empty($input->gtmetrix_allow_simulation))
		 	{
		 		 $this->prepareSimulationControl($simcontrol_object, $bypass);
		 	}
		 }
		 Return MulticacheHelper::toArray($input);
	}
	/*
	 * NON DB Related functions
	 */
	protected function setprincipleJqueryscopeoperator($table)
	{
	
		//$app = JFactory::getApplication();
		if (isset($table->principle_jquery_scope) && $table->principle_jquery_scope == 0)
		{
			self::$_principle_jquery_scope = "jQuery";
		}
		elseif (isset($table->principle_jquery_scope) && $table->principle_jquery_scope == 1)
		{
			self::$_principle_jquery_scope = "$";
		}
		elseif (isset($table->principle_jquery_scope) && $table->principle_jquery_scope == 2)
		{
			if (! empty($table->principle_jquery_scope_other))
			{
				self::$_principle_jquery_scope = trim($table->principle_jquery_scope_other);
			}
			else
			{
				MulticacheHelper::clearAllNotices();
				MulticacheHelper::prepareMessageEnqueue(__('jQuery scope not defined','multicache-plugin'));
				if($this->debug)
				{
					MulticacheHelper::log_error('jQuery scope not defined',$this->error_log);
				}
				wp_redirect(admin_url().'admin.php?page=multicache-config-menu#page-js-tweaks');
				exit;
				/*				
				$app->enqueueMessage(JText::_('COM_MULTICACHE_CLASS_MULTICACHE_PAGE_SCRIPTS_JQUERY_SCOPE_NOT_DEFINED'), 'warning');
				$app->redirect('index.php?option=com_multicache&view=config&layout=edit&id=1#page-js-tweaks');
				*/
			}
		}
		else
		{
			MulticacheHelper::clearAllNotices();
			MulticacheHelper::prepareMessageEnqueue(__('jQuery scope error','multicache-plugin'));
			if($this->debug)
			{
			MulticacheHelper::log_error('jQuery scope error',$this->error_log);
			}
			wp_redirect(admin_url().'admin.php?page=multicache-config-menu#page-js-tweaks');
			exit;
			//$app->enqueueMessage(JText::_('COM_MULTICACHE_CLASS_MULTICACHE_PAGE_SCRIPTS_JQUERY_SCOPE_ERROR'), 'error');
			//$app->redirect('index.php?option=com_multicache&view=config&layout=edit&id=1#page-js-tweaks');
		}
	
	}
	/*
	 * DB Related functions
	 */
	protected function getLastTest()
	{
	global $wpdb;
	$table = $wpdb->prefix.'multicache_advanced_test_results';
	Return $wpdb->get_row("Select * FROM $table ORDER BY id desc");
	}
	protected function resetMaxTests($budget, $id)
	{
		global $wpdb;
		$table = $wpdb->prefix.'multicache_advanced_test_results';
		$data = array('id' => $id,'max_tests' => $budget);
		$where = array('id' => $id);
		$format = array('%d','%d');
		$where_format = array('%d');
		$wpdb->update( $table, $data, $where, $format , $where_format  );
	
	}
	
	protected function getlastTestGroup()
	{
		global $wpdb;
		$table = $wpdb->prefix.'multicache_advanced_testgroups';
		Return $wpdb->get_row("Select * FROM $table ORDER BY id desc");
		}
		protected function resetGroupCycles($tbl, $id)
		{
		
			//$app = JFactory::getApplication();
			$min_precache = (int) $tbl->precache_factor_min;
			$max_precache = (int) $tbl->precache_factor_max;
			$min_cachecompression = (float) $tbl->gzip_factor_min; // $min_gzip -> $min_cachecompression
			$max_cachecompression = (float) $tbl->gzip_factor_max; // $max_gzip -> $max_cachecompression
			$step_cachecompression = (float) $tbl->gzip_factor_step; // $step_gzip -> $step_cachecompression
		
			$precache_sequences = ($max_precache - $min_precache) + 1;
			$step_cachecompression = empty($step_cachecompression) ? 1 : $step_cachecompression; // filtering the input for 0
			$cachecompression_sequences = (int) (($max_cachecompression - $min_cachecompression) / $step_cachecompression);
			$cachecompression_sequences = ($cachecompression_sequences <= 1) ? 1 : $cachecompression_sequences;
			if ($tbl->simulation_advanced)
			{
				if (class_exists('MulticacheLoadinstruction') && property_exists('MulticacheLoadinstruction', 'loadinstruction'))
				{
					$load_states = count(MulticacheLoadinstruction::$loadinstruction);
					$expected_tests = $cachecompression_sequences * $precache_sequences * $load_states * $tbl->gtmetrix_cycles;
				}
				else
				{
					if($this->debug)
					{
						MulticacheHelper::log_error(__('Expected tests stat inaacurate, advanced simulation mode incomplete','multicache-plugin'),$this->error_log);
					}
					
				$expected_tests = $cachecompression_sequences * $precache_sequences * $tbl->gtmetrix_cycles;
				}
			}
			else
			{
				$expected_tests = $cachecompression_sequences * $precache_sequences * $tbl->gtmetrix_cycles;
			}
		global $wpdb;
		$table = $wpdb->prefix.'multicache_advanced_testgroups';
		$data = array('id' => $id,'expected_tests' => $expected_tests, 'cycles' =>$tbl->gtmetrix_cycles);
		$where = array('id' => $id);
		$format = array('%d','%d','%d');
		$where_format = array('%d');
		$wpdb->update( $table, $data, $where, $format , $where_format  );
		/*
			$db = JFactory::getDBo();
			$updateObject = new stdClass();
			$updateObject->id = $id;
			$updateObject->expected_tests = $expected_tests;
			$updateObject->cycles = $tbl->gtmetrix_cycles;
			$result = $db->updateObject('#__multicache_advanced_testgroups', $updateObject, 'id');
			*/
		
		}
		
		protected function makeExcludedComponentslist($input)
		{
			$plugins = MulticacheHelper::getAllPlugins();
		
		$excluded_components = unserialize($input->excluded_components);
		self::$_excluded_components = $this->preparePluginInfo($excluded_components,$plugins);
		
		
		$excluded_components_css = unserialize($input->cssexcluded_components);
		self::$_excluded_components_css = $this->preparePluginInfo($excluded_components_css,$plugins);
		
		$excluded_components_img = unserialize($input->imgexcluded_components);
		self::$_excluded_components_img = $this->preparePluginInfo($excluded_components_img,$plugins);
		
		}
		
		protected function preparePluginInfo($exclude, $plugins)
		{
			if(empty($exclude))
			{
				Return false;
			}
			$stub = array();
			foreach($exclude As $key => $value)
			{
				if(isset($plugins[$key]))
				{
					$stub[$key] = array('name'=> $key,'value' => $value,'path' => $plugins[$key][rel_path],'display_name' =>$plugins[$key]['display_name']);
				}
			}
			if(empty($stub))
			{
				Return null;
			}
			Return $stub;
		}
		
		protected function prepare_Config($obj)
		{
			//at time of writing network does not pass through here nevertheless
		$check1 = is_multisite() && !MulticacheHelper::isNetwork();
			if($check1)
			{
				$loc_obj = MulticacheHelper::resolveMultiSiteStrategyloc();
				if(!$loc_obj || !$loc_obj->strategy_blogfolder_check)
				{
					MulticacheHelper::prepareMessageEnqueue(__('loc object not created','multicache-plugin'),'config-issue');
				}
				$namespace = MulticacheHelper::resolveMultisiteStrategyNamespace();
				if(!$namespace)
				{
					MulticacheHelper::prepareMessageEnqueue(__('namespace object not created','multicache-plugin'),'config-issue');
				}
				//lets load the respective config
				if(isset($loc_obj) && is_file($loc_obj->strategy_blogConfig_loc))
				{
					require_once $loc_obj->strategy_blogConfig_loc;
					$config = new $namespace->config_name;
				}
				
				
			}
			if(!isset($config) && file_exists(plugin_dir_path(__FILE__) . 'multicache_config.php'))
			{
			require_once plugin_dir_path(__FILE__) . 'multicache_config.php';
			$config = new MulticacheConfig();
			}			
		    
			
			$config->cache_handler = $obj->cache_handler;
			$config->storage = 'fastcache';
			$config->debug = $obj->debug_mode;
			$config->gzip = $obj->gzip;
			$config->caching = $obj->caching;
			//addedver-1.0.0.3
			$config->cache_user_loggedin = $obj->cache_user_loggedin;
			$config->optimize_user_loggedin = $obj->optimize_user_loggedin;
			$config->cache_query_urls = $obj->cache_query_urls;
			$config->optimize_query_urls = $obj->optimize_query_urls;
			//
			$config->cachetime = $obj->cachetime;
			$config->multicache_persist = $obj->multicache_persist;
			$config->multicache_compress = $obj->multicache_compress;
			$config->multicache_server_host = $obj->multicache_server_host;
			$config->multicache_server_port = $obj->multicache_server_port;
			$config->indexhack = $obj->indexhack;
			$config->precache_factor = $obj->precache_factor_default;
			$config->ccomp_factor = $obj->ccomp_factor_default;
			$config->force_locking_off = $obj->force_locking_off;
			$config->multicachedistribution = $obj->multicachedistribution;
			$config->cache_comment_invalidation = $obj->cache_comment_invalidation;
			//secret
			if(!property_exists($config , 'secret'))
			{
				$config->secret = MulticacheHelper::getNewBlogHash();
			}
			if (isset($obj->force_precache_off) && $obj->force_precache_off == 1)
			{
				$config->multicacheprecacheswitch = true;
			}
			elseif (empty($obj->force_precache_off))
			{
				$config->multicacheprecacheswitch = null;
			}
			if(defined('ABSPATH')  && strcmp($config->absolute_path,ABSPATH) !== 0)
			{
				
				$config->absolute_path = ABSPATH;
				MulticacheHelper::prepareMessageEnqueue(__('Updated new absolute path','multicache-plugin').' - '.ABSPATH,'updated');
			}
			
				if(is_multisite())
				{
					$cache_sub_path = MulticacheHelper::resolveCacheSubPath();
					if(defined('WP_CONTENT_DIR'))
					{
						$config->cache_path = WP_CONTENT_DIR . '/cache/'.$cache_sub_path;
						
					}
					elseif(defined('ABSPATH'))
					{
						$config->cache_path = ABSPATH . 'wp-content/cache/'.$cache_sub_path;
					}
				}
				//the old equation
				elseif(((isset($config->cache_path) && $config->cache_path != WP_CONTENT_DIR . '/cache/')
							|| !isset($config->cache_path))) {
					
						$config->cache_path = WP_CONTENT_DIR . '/cache/';
					
				}
				elseif(defined('ABSPATH') && ((isset($config->cache_path) && $config->cache_path != ABSPATH . 'wp-content/cache/') || !isset($config->cache_path)))
				{
					$config->cache_path = ABSPATH . 'wp-content/cache/';
				}
			
			/*
			if(defined('WP_CONTENT_DIR') 
					&& ((isset($config->cache_path) && $config->cache_path != WP_CONTENT_DIR . '/cache/')
							|| !isset($config->cache_path)))
			{
				
				$config->cache_path = WP_CONTENT_DIR . '/cache/';
			}
			elseif(defined('ABSPATH') && ((isset($config->cache_path) && $config->cache_path != ABSPATH . 'wp-content/cache/') || !isset($config->cache_path)))
			{
				
				$config->cache_path = ABSPATH . 'wp-content/cache/';
			}
			*/
			$f_dir = plugin_dir_path(dirname(__FILE__));//php ver 5.3 suppor
			if(!empty($f_dir) && ((isset($config->plugin_dir_path) && $config->plugin_dir_path != $f_dir) || !isset($config->plugin_dir_path) ))
			{
				$config->plugin_dir_path = plugin_dir_path(dirname(__FILE__));
				MulticacheHelper::prepareMessageEnqueue(__('Updated plugin directory','multicache-plugin').' - '.plugin_dir_path(dirname(__FILE__)),'updated');
			}
			if($check1)
			{
				$multisite_obj = MulticacheHelper::resolveMultiSiteStrategyloc();
				if(false !== $multisite_obj)
				{
				$config->blog_id = $multisite_obj->blog_id;
				$config->site_id = $multisite_obj->currentsite_id;
							
				}
				
			}
			if(MulticacheHelper::isNetwork())
			{
				$config->is_network = true;
			}
			
			$config = MulticacheHelper::validateConfig($config);
			
			// $this->writeConfigFile($registry);
			if($check1 && false !== $multisite_obj && false !== $namespace)
			{
				$success = MulticacheHelper::writeToConfig( $config , false , true ,false, false ,false ,
						$multisite_obj->strategy_blogConfig_loc , $namespace->config_name);
				if(!$success)
				{
					MulticacheHelper::prepareMessageEnqueue(__('failed to write config for blog -'.$config->blog_id,'multicache-plugin'));
				}
				Return;
			}
			MulticacheHelper::writeToConfig($config ,false,true);
			
		
		}
		
		protected function prepare_JSvars($obj)
		{
		
			if (! empty($obj->default_scrape_url) && $obj->default_scrape_url != strtolower(MulticacheUri::root()))
			{
		
				// 1st check that they are of the same domain
				$base_url = strtolower(substr(MulticacheUri::root(), 0, - 1));
				$scrape_url = strtolower($obj->default_scrape_url);
				$same_domain = stripos($scrape_url, $base_url);
				if ($same_domain === false)
				{
					$obj->default_scrape_url = MulticacheUri::root();
				}
			}
		
		}
		
		protected function performCssOptimization($table, $css_exclude_object = null, $img_exclude_object = null, $params_lazyload = null , $ext_params = null)
		{
		
			//$app = JFactory::getApplication();
			$css_array = $this->prepareNonTableCssElements();
			//MulticacheHelper::log_error('Post preparenontableelements','spl-error-1',$css_array);
			$spl_condition = null;
			if (property_exists('MulticachePageCss', 'delayed'))
			{
				$del_flg = MulticachePageCss::$delayed;
				if (! empty($del_flg) && property_exists('MulticachePageCss', 'working_css_array'))
				{
					$wsa = MulticachePageCss::$working_css_array;
					if (empty($wsa))
					{
						$spl_condition = true;
					}
				}
			}
		
			if (empty($css_array) && ! isset($spl_condition))
			{
				MulticacheHelper::prepareMessageEnqueue(__('CSS combining and minification could not be initialized, please ensure the css template is scraped','multicache-plugin'));
				if($this->debug)
				{
					MulticacheHelper::log_error(__('PrepareNonTableelements returned empty','multicache-plugin'),$this->error_log);
				}
				//$app->enqueueMessage(JText::_('COM_MULTICACHE_ERROR_PAGESCSS_PREPARENONTABLEELEMENTS_EMPTY'), 'warning');
				Return false;
			}
			// ignore's ignore behaviour will treat css as orphaned and load
			$css_array = $this->setCssIgnore($css_array);
			// dont loads - dont loads behaviour will remove the css if present
			$css_array = $this->setDontLoadCss($css_array);
			// store cdns and their sigs
			$css_array = $this->setCDNtosignatureCss($css_array);
			// mark duplicates
			$css_array = $this->setSignatureHashCss($css_array);
			if ($table->css_maintain_preceedence)
			{
				// iterating through a jsarray here to avoid code duplication
				// ensures preceedence does not moderate the default loadsections
				$css_array = $this->performPreceedenceModeration($css_array);
			}
			/* DUPLICATE HANDLERS */
			// to be tested
			if (! $table->dedupe_css_styles)
			{
				$css_array = $this->placeDuplicatesBack($css_array, 'css', 'MulticachePageCss');
			}
			if ($table->dedupe_css_styles)
			{
				$css_array = $this->removeDuplicateCss($css_array);
			}
			// mark for delay css. Prepare the delayable object.
			// institutes the $_delayable_segment_css object
			$css_array = $this->prepareDelayableCss($css_array, $table);
		
			MulticacheHelper::storePageCss(null, $css_array, self::$_duplicates_css, self::$_delayable_segment_css);
			// correction for 2nd time flow
			$this->correctSignatureHashCss(self::$_duplicates_css);
			$this->correctDelaySignatureHashCss(self::$_delayable_segment_css);
			$css_array = $this->moderateDefaultLoadsections($css_array);
		
			if ($table->group_css_styles)
			{
				/*
				 * in css we will attempt to group everything.
				 * If somethings is not to be grouped it should be explicitely indicated.
				 * css groups are distinguioshed by loadsection primarily
				 * css groups are distinguished by group number secondarily.
				 */
		
				$css_array = $this->assignCssGroups($css_array, $table);
				$this->initialiseCssGroupHash($css_array);
				$this->combineCssGroupCode($table);
				$this->prepareCssGrouploadableUrl();
				$this->writeGroupCssCode($table);
			}
			$this->makeCssDelaycode();
			$this->segregatePlaceCssDelay($table);
		
			$this->prepareCssLoadsections($css_array, $table , $ext_params);
			$this->correctCssLoadsectionAturl();
			// if js tweaks is off then we will combine delay to css elements
			// delay is performed though scripts hence if js tweaks is on it is better to perform
			// the delay at the lag end before prepare cache strategy. This enables delay scripts to align to normal scripts and
			// prevents blocking css elements
			
			if (! $table->js_switch)
			{
				$this->combineCssDelay($ext_params);
				$stubs = MulticacheHelper::prepareStubs($table);
				MulticacheHelper::writeJsCacheStrategy(null, null, null, $stubs, null, self::$_signature_hash_css, self::$_loadsections_css, $table->css_switch, $css_exclude_object, $table->image_lazy_switch, $img_exclude_object, $params_lazyload);
			}
		
		}
		
		protected function prepareNonTableCssElements()
		{
		
			//$app = JFactory::getApplication();
			$page_css_object = $this->getRelevantPageCss(); // ATTENTION THIS SETTING IS RELATED TO VIEW: Better still to align by signatures but that would not give the option to the user to retain duplicate scripts
			if(empty($page_css_object))
			{
				Return false;
			}
		
			// get the array keys
			/*
			 * array(7) { [0]=> string(11) "loadsection" [1]=> string(5) "delay" [2]=> string(10) "delay_type" [3]=> string(8) "cdnalias" [4]=> string(6) "ignore" [5]=> string(8) "grouping" [6]=> string(12) "group_number" }
			 */
			$template_csskeys = $this->getTemplateCssKeys($page_css_object);
		
			//$jinput = JFactory::getApplication()->input;
			$css_loadsection_input = MulticacheHelper::filterInputPost();
			//$css = $_REQUEST['multicache_config_css_tweak_options'];
			//var_dump($css_loadsection_input,'<br><br>',$css);exit;
			foreach ($page_css_object as $key => $obj)
			{
				
		
				foreach ($template_csskeys as $template_csskey)
				{
					$key_state_tag_css = 'css' . $template_csskey . '_' . $key;
					$current_state = $css_loadsection_input[$key_state_tag_css] ;//$jinput->get($key_state_tag_css);
		
					if (isset($current_state) && $current_state != $obj[$template_csskey] )
					{
						$page_css_object[$key][$template_csskey] = $current_state;
					}
					
				
				}
		
				// attach the cdn url or reset the key
				
				$cdn_key_css = $css_loadsection_input['csscdnalias_' . $key];//$jinput->get('com_multicache_csscdnalias_' . $key);
				$cdn_url_css = $css_loadsection_input['cdn_url_css_' . $key];//$jinput->getHtml('cdn_url_css_' . $key);
				if (! empty($cdn_key_css))
				{
		
					if (! empty($cdn_url_css))
					{
						$page_css_object[$key]['cdn_url_css'] = $cdn_url_css;
					}
					else
					{
						$page_css_object[$key]['cdnaliascss'] = 0;
						$page_css_object[$key]['cdn_url_css'] = null;
					}
				}
				// special case to clear out cdn url
				if (empty($cdn_key_css) && ! empty($cdn_url_css))
				{
					$page_css_object[$key]['cdn_url_css'] = null;
				}
				
				//if grouping and group numbers are not assigned || assign defaults
				$page_css_object[$key]['grouping']     = isset($page_css_object[$key]['grouping']) ? $page_css_object[$key]['grouping'] : true;
				$page_css_object[$key]['group_number'] = isset($page_css_object[$key]['group_number']) ? $page_css_object[$key]['group_number'] :0;
			}
		
			Return $page_css_object;
		
		}
		
		
		protected function getRelevantPageCss()
		{
		
			//$app = JFactory::getApplication();
			if (! class_exists('MulticachePageCss'))
			{
				MulticacheHelper::prepareMessageEnqueue(__('PageCss class does not exist - please ensure css is scraped','multicache-plugin'));
				if($this->debug)
				{
					MulticacheHelper::log_error(__('PageCss class does not exist','multicache-plugin'),$this->error_log);
				}
		
				Return false;
			}
		
			if (property_exists('MulticachePageCss', 'working_css_array'))
			{
		
				$pagecss = MulticachePageCss::$working_css_array;
			}
			elseif (property_exists('MulticachePageCss', 'original_css_array'))
			{
				$pagecss = MulticachePageCss::$original_css_array;
			}
			else
			{
				// register error Multicache Class exists with no proerties
				MulticacheHelper::prepareMessageEnqueue(__('Class PageCss has no defined properties - getrelevantcss','multicache-plugin'));
				if($this->debug){
					MulticacheHelper::log_error(__('Class PageCss has no defined properties','multicache-plugin'),$this->error_log);
				}
				
				Return false;
			}
		
			Return $pagecss;
		
		}
		
		protected function getTemplateCssKeys($page_css_object)
		{
		
			$template_cssobject = MulticacheHelper::getPageCssObject($page_css_object);
			$template_cssobject = $template_cssobject->CssTransposeObject;
			if (empty($template_cssobject))
			{
				Return false;
			}
			$template_csskeys = array();
			foreach ($template_cssobject as $key => $value)
			{
				$template_csskeys[] = $key;
			}
			Return $template_csskeys;
		
		}
		
		protected function setCssIgnore($css_array)
		{
		
			foreach ($css_array as $key => $css)
			{
				if (! empty($css["ignore"]))
				{
					unset($css_array[$key]); // signature_hash need not be updated as the operation is performed before its setting.
				}
			}
			Return $css_array;
		
		}
		
		protected function setDontLoadCss($cssarray)
		{
		
			foreach ($cssarray as $key => $css)
			{
				if ($css["loadsection"] >= 5)
				{
					$sig = $css["signature"];
					$alt_sig = $css["alt_signature"];
					if (isset($sig))
					{
						self::$_signature_hash_css[$sig] = true;
					}
					if (isset($alt_sig))
					{
						self::$_signature_hash_css[$alt_sig] = true;
					}
					unset($cssarray[$key]);
				}
			}
			Return $cssarray;
		
		}
		
		protected function setCDNtosignatureCss($cssarray)
		{
			// get all cdn signatures
			foreach ($cssarray as $key => $css)
			{
				if (! empty($css["cdn_url_css"]))
				{
					$sig = $css["signature"];
					self::$_cdn_segment_css[$sig] = $css["cdn_url_css"];
				}
			}
			foreach ($cssarray as $key => $css)
			{
				$sig = $css["signature"];
				if (isset(self::$_cdn_segment_css[$sig]) && empty($css["cdn_url_css"]))
				{
					$cssarray[$key]["cdnaliascss"] = 1;
					$cssarray[$key]["cdn_url_css"] = self::$_cdn_segment_css[$sig];
				}
			}
			Return $cssarray;
		
		}
		
		protected function setSignatureHashCss($cssarray)
		{
		
			foreach ($cssarray as $key => $css)
			{
				$sig = $css["signature"];
				$alt_sig = $css["alt_signature"];
				if (isset(self::$_signature_hash_css[$sig]))
				{
					$cssarray[$key]["duplicate"] = true;
				}
				self::$_signature_hash_css[$sig] = true;
				if (isset($alt_sig) && ! isset(self::$_signature_hash_css[$alt_sig]))
				{
					self::$_signature_hash_css[$alt_sig] = true;
				}
			}
			Return $cssarray;
		
		}
		
		protected function performPreceedenceModeration($jsarray)
		{
		
			$cur_load_sec = 0;
			foreach ($jsarray as $key => $js)
			{
				if ($js["loadsection"] > $cur_load_sec && $js["loadsection"] < 5)
				{
					$cur_load_sec = $js["loadsection"];
				}
				$js["loadsection"] = $cur_load_sec;
				$jsarray[$key] = $js;
			}
			Return $jsarray;
		
		}
		
		protected function placeDuplicatesBack($jsarray, $type = 'script', $class_name = "MulticachePageScripts")
		{
		
			$check_property = $type == 'script' ? property_exists($class_name, 'original_script_array') : property_exists($class_name, 'original_css_array');
			if (! (class_exists($class_name) && $check_property && property_exists($class_name, 'duplicates')))
			{
				Return $jsarray;
			}
			$duplicates_array = $class_name::$duplicates;
			$original_array = $type == 'script' ? $class_name::$original_script_array : $class_name::$original_css_array; // basis for indexing
			$newjsarray = array();
			foreach ($original_array as $key => $orig)
			{
		
				if (! isset($jsarray[$key]))
				{
					$newjsarray[$key] = isset($duplicates_array[$key]) ? $duplicates_array[$key] : $orig;
				}
				else
				{
					$newjsarray[$key] = $jsarray[$key];
				}
			}
		
			Return $newjsarray;
		
		}
		
		protected function placeDelayedCode($grp, $tbl)
		{
		
			if (empty($grp["items"]) || empty($grp["delay_callable_url"]))
			{
				Return false;
			}
			//$app = JFactory::getApplication();
		
			// start
			if (! isset(self::$_principle_jquery_scope))
			{
				self::$_principle_jquery_scope = "jQuery";
			}
			$begin_comment = "/* Begin delay prepared by Multicache for " . $grp["delay_callable_url"] . "	*/";
			// $code_string = $begin_comment;
			if (! empty(self::$_jscomments))
			{
				$code_string = $begin_comment;
			}
		
			foreach ($grp["items"] as $key => $group)
			{
				$sig = $group["signature"];
				//promises checktype flag for deep embeding
				$chckTypeFLAG = false;
		
				if (isset(self::$_cdn_segment[$sig]) && (bool) self::$_cdn_segment[$sig] == true)
				{
					$url = self::$_cdn_segment[$sig];
					$c_string = '
' . self::$_principle_jquery_scope . '.getScript( "' . $url . '", function() {
		
		
}).fail(function() {
		
    console.log("loading failed in ' . $url . '" );
		
		
        });
		
';
					if(!empty($group['promises']))
					{
						$c_string = $this->preparePromise($group , $c_string);
					}
					$code_string .= $c_string;
				}
		
				elseif (isset($group["internal"]) && $group["internal"] == true)
				{
					// this is src and internal
					// as were callingafter delay no need to curl ;Contrary its a double getscropt hence we will curl
					$url = $group["absolute_src"];
					$url = MulticacheHelper::checkCurlable($url);
					if (isset(self::$_mediaVersion))
					{
						$url_temp = $url;
						$j_uri = MulticacheUri::getInstance($url);
						$j_uri->setVar('mediaFormat', self::$_mediaVersion);
						$url = $j_uri->toString();
					}
		
					$begin_comment = "/* Inserted by Multicache InternalDelay source code insert	url-" . $url . "	 */";
					$end_comment = "/* end Multicache InternalDelay insert */";
					$curl_obj = MulticacheHelper::get_web_page($url);
					if ($curl_obj["http_code"] == 200)
					{
						if ($tbl->compress_js)
						{
							$int_content = MulticacheJSOptimize::process($curl_obj["content"]);
						}
						else
						{
							$int_content = $curl_obj["content"];
						}
						// $c_string .= $begin_comment . MulticacheHelper::clean_code(trim($curl_obj["content"])) . $end_comment;
						$c_string = ! empty(self::$_jscomments) ? $begin_comment . MulticacheHelper::clean_code(trim($int_content)) . $end_comment : MulticacheHelper::clean_code(trim($int_content));
						if(!empty($group['promises']))
						{
							$c_string = $this->preparePromise($group , $c_string );
						}
					}
					else
					{
						// register error
		
						$e_message = __('Could not create the delay code. curl error response: ','multicache-plugin') . $curl_obj["errmsg"];
						MulticacheHelper::prepareMessageEnqueue($e_message , 'error');
						//$app->enqueueMessage(JText::_('COM_MULTICACHE_ERROR_PAGESCRIPT_INTERNALDELAY_CURL_ERROR') . $e_message . ' response- ' . $curl_obj["http_code"], 'warning');
						Return false;
					}
					
					$code_string .= $c_string;
				}
				elseif (isset($group["internal"]) && $group["internal"] == false)
				{
					$url = $group["src"];
					$c_string = '
' . self::$_principle_jquery_scope . '.getScript( "' . $url . '", function() {
		';
					if(!empty($group['promises']) && !empty($group['checktype']) && empty($group['mau']))
					{
						$c_string .= $this->successEmbedPromise($group);
						
						$chckTypeFLAG = true;
					}
$c_string .= '
		
		
}).fail(function() {
		';
if(!empty($group['promises']) && !empty($group['checktype']) && empty($group['mau']))
{
	$c_string .= $this->failEmbedPromise($group);
	
	$chckTypeFLAG = true;
}
$c_string .= '		
    console.log("loading failed in ' . $url . '");
		
		
        });
		
';
					
					if(!empty($group['promises']))
					{
					$c_string = $this->preparePromise($group , $c_string , $chckTypeFLAG);
					}
					$code_string .= $c_string;
					
					
				}
				elseif (! isset($group["internal"]) && $group["code"])
				{
					//version1.0.0.2
					$unserialized_code = unserialize($group["serialized_code"]);
					$code = ! empty($unserialized_code) ? $unserialized_code : $group["code"];
					
					$begin_comment = "
                /* Multicache Insert for  code   " . str_replace("'", "", str_replace('"', "", substr($group["code"], 0, 10))) . " */
";
		
					$end_comment = "
		
/* end insert of code 	  " . str_replace("'", "", str_replace('"', "", substr($group["code"], 0, 10))) . " */";
					// unserialize and tie code here
					/*if (isset($group["serialized_code"]))
					{*/
					//version1.0.0.2
					if (! empty($code))
					{
						if ($tbl->compress_js)
						{
							//$unserialized_code = MulticacheJSOptimize::process(unserialize($group["serialized_code"]));
							//version1.0.0.2
							$unserialized_code = MulticacheJSOptimize::process($code);
						}
						else
						{
							//$unserialized_code = unserialize($group["serialized_code"]);
							//version1.0.0.2
							$unserialized_code = $code;
						}
						// $code_string .= $begin_comment . MulticacheHelper::clean_code(trim(unserialize($group["serialized_code"]))) . $end_comment;
						$temp_string =  ! empty(self::$_jscomments) ? $begin_comment . MulticacheHelper::clean_code(trim($unserialized_code)) . $end_comment : MulticacheHelper::clean_code(trim($unserialized_code));
						if(!empty($group['promises']))
						{
							$temp_string = $this->preparePromise($group , $temp_string);
						}
						$code_string .= $temp_string;
					}
					else
					{
						// register error
		MulticacheHelper::prepareMessageEnqueue(__('Encountered an unknown script type while creating delay code.' , 'multicache-plugin'));
						//$app->enqueueMessage(JText::_('COM_MULTICACHE_CLASS_MULTICACHE_PAGE_SCRIPTS_URL_NOT_INTERNAL_NOT_CODE_INDELAY_ERROR'), 'error');
						Return false;
					}
				}
			}
			$end_comment = "/* End of  delay prepared by Multicache for " . $grp["delay_callable_url"] . "	*/";
			// $code_string .= $end_comment;
			if (! empty(self::$_jscomments))
			{
				$code_string .= $end_comment;
			}
			
			ob_start();
			echo $code_string;
			$buffer = ob_get_clean();
			
			$return = MulticacheHelper::writeJsCache($buffer, $grp["delay_callable_url"], true);
		
			Return $return;
		
		}
		protected function successEmbedPromise($grp)
		{
			if(empty($grp))
			{
				Return '';
			}
			if(!isset(self::$_promises))
			{
				$this->initiatePromise();
			}
			
            $success_string = $this->getSuccessString($grp);
			Return $success_string;
		}
		protected function getSuccessString($grp = null)
		{
			$debug = false;
			$p_count = $this->getPromiseCount();
			$success_string = <<<PROMISE
			var c = 'undefined' !== p_func_$p_count.checkMate.checkType();
			if(typeof resolve !== 'undefined'  && c ){
					resolve(10);
		}
else if(c){
 p_func_$p_count.thenback();
}
else if(typeof reject !== 'undefined'){
reject();
}
else
{
PROMISE;
			if($debug)
			{
				$success_string .=  <<<PROMISE
alert('p_func_$p_count deep embed failed');
PROMISE;
			}
			$success_string .=  <<<PROMISE
}			
PROMISE;
			Return $success_string;
		}
		protected function failEmbedPromise($grp)
		{
			if(empty($grp))
			{
				Return '';
			}
			if(!isset(self::$_promises))
			{
				$this->initiatePromise();
			}
			//$p_count = $this->getPromiseCount();
			$failed_string = $this->getfailedstring();
			Return $failed_string;
		}
		protected function getfailedstring()
		{
			$failed_string = <<<PROMISE
			if(typeof reject !== 'undefined' ){
					reject();
		}
PROMISE;
			Return $failed_string;
		}
		//$chckTypeFLAG is set to true for deep embed checks
		protected function preparePromise($object , $callback , $chckTypeFLAG = false , $extend_src = false)
		{
			if(empty($object))
			{
				Return $object;
			}
			if(!isset(self::$_promises))
			{				
				$this->initiatePromise();
			}
			$debug = false;

			$p_count = $this->getPromiseCount();
			$promise_func = 'p_func_'.$p_count;
			$promise_string =  <<<PROMISE
			$promise_func = {
 'init' :  function (resolve , reject){
PROMISE;
			if($debug)
			{
				$promise_string .=  <<<PROMISE
alert('fulfilling promise $promise_func resolve ' + resolve + ' reject ' + reject);
alert('here promise ' + $p_count);
PROMISE;
			}
			
			if(!empty($extend_src))
			{
				$load_src = $object['src'];
				$promise_string .=  <<<PROMISE
$promise_func.loadsource('$load_src');
PROMISE;
			}
			else{
      $promise_string .=  <<<PROMISE
$promise_func.callback(resolve , reject);
PROMISE;
			}
			if(!empty($object['mau']))
			{
				$this->setMau();
				$mau_time = !empty($object['mautime'])?$object['mautime'] :30;
				$promise_string .=  <<<PROMISE
           multicache_MAU(resolve,reject ,$promise_func.checkMate, $mau_time);
PROMISE;
			}elseif(!$chckTypeFLAG)
				{
					$promise_string .=  $this->getSuccessString();
				}
				
$promise_string .=  <<<PROMISE

},
 'then' : function(data) {
PROMISE;
			if($debug)
			{
				$promise_string .=  <<<PROMISE
alert('Got data! Promise$p_count fulfilled.' + data);
alert('typeof ' + $promise_func.checkMate.name + ' ' + $promise_func.checkMate.checkType());
PROMISE;
			}
			$promise_string .=  <<<PROMISE
   
   $promise_func.thenback();
   
  },
  'error': function(error) {
PROMISE;
			if($debug)
			{
				$promise_string .=  <<<PROMISE
alert('Promise $promise_func rejected.');
alert(error.message);
PROMISE;
			}
			$promise_string .=  <<<PROMISE
   
  },
 'catch': function(e) { 
PROMISE;
			if($debug)
			{
				$promise_string .=  <<<PROMISE
alert('catch: ', e);
PROMISE;
			}
			$promise_string .=  <<<PROMISE
 
  },
  'checkMate' : {
PROMISE;
if(!empty($object['checktype']))
{
	$c_type = $object['checktype'];
  $promise_string .=  <<<PROMISE
  checkType : function(){
              return typeof $c_type;
              },
              name : '$c_type'
                },
PROMISE;
} else{
  	$promise_string .=  <<<PROMISE
  checkType : function(){
              return true;
  },
               name : ''
  	
                },
PROMISE;
  	
  }
  $promise_string .=  <<<PROMISE
  'callback' : function(resolve , reject){
PROMISE;
			if($debug)
			{
				$promise_string .=  <<<PROMISE
alert('confirm executing callback');
PROMISE;
			}
			
  if(empty($extend_src))
  {
  $promise_string .=  <<<PROMISE
  $callback
PROMISE;
  }
  $promise_string .=  <<<PROMISE
  
  },
 
PROMISE;
  if(!empty($extend_src))
  {
  	$promise_string .=  <<<PROMISE
  'loadsource' : function(s){
  js = document.createElement('script');
  js.src = s; 
  js.async = true;
  var ajs = document.getElementsByTagName('script')[0];
  ajs.parentNode.insertBefore(js, ajs);
PROMISE;
			if($debug)
			{
				$promise_string .=  <<<PROMISE
alert('srcipt insert succesful ' + s);
PROMISE;
			}
			$promise_string .=  <<<PROMISE
  
  },
PROMISE;
  }
 if(!empty($object['thenBack']))
{
	$t_back = trim($object['thenBack']);
	ob_start();
	echo $t_back;
	$t_back = ob_get_clean();
  $promise_string .=  <<<PROMISE
  'thenback' : function(){
PROMISE;
			if($debug)
			{
				$promise_string .=  <<<PROMISE
alert('executing then back func');
PROMISE;
			}
			$promise_string .=  <<<PROMISE
  
  $t_back
  }
PROMISE;
} else{
  	$promise_string .=  <<<PROMISE
  'thenback' : function(){
PROMISE;
			if($debug)
			{
				$promise_string .=  <<<PROMISE
alert('executing then back empty func');
PROMISE;
			}
			$promise_string .=  <<<PROMISE
  }
PROMISE;
  	
  }
  $promise_string .=  <<<PROMISE
  
};
PROMISE;
  $promise_body = <<<PROMISE
  if(window.Promise){
var promise_$p_count = new Promise($promise_func.init);
promise_$p_count.then($promise_func.then , $promise_func.error).catch($promise_func.catch);
  }
  else
  		{
  		$promise_func.callback();
  		}
  
PROMISE;
  $promise_body = $promise_string . $promise_body;
  $promise_body = MulticacheJSOptimize::process($promise_body);
  $this->incrementPromiseCount();
  Return $promise_body;
		}
		protected function initiatePromise()
		{
			if(isset(self::$_promises))
			{
				Return;
			}
			self::$_promises = array();
			self::$_promises['count'] = 0;
			self::$_promises['load_mau'] = false;
			
		}
		protected function setMau()
		{
			if(!isset(self::$_promises))
			{
				Return false;
			}
		self::$_promises['load_mau'] = true;
		}
		protected function getPromiseCount()
		{
			if(!isset(self::$_promises))
			{
				Return false;
			}
			Return self::$_promises['count'];
		}
		protected function incrementPromiseCount()
		{
			if(!isset(self::$_promises))
			{
				Return false;
			}
			self::$_promises['count']++;
		}
		protected function removeDuplicateCss($cssarray)
		{
		
			foreach ($cssarray as $key => $css)
			{
		
				if (isset($cssarray[$key]["duplicate"]))
				{
					self::$_duplicates_css[$key] = $cssarray[$key]; // store duplicates incase its called back
					unset($cssarray[$key]);
				}
			}
		
			Return $cssarray;
		
		}
		
		protected function prepareDelayableCss($cssarray, $table)
		{
		
			$preceedence = isset($table->css_maintain_preceedence) ? $table->css_maintain_preceedence : false;
		
			$delay_lag = false;
			$delayable = null;
			$stored_delayable = $this->loadProperty('delayed', 'MulticachePageCss');
		
			foreach ($cssarray as $key => $css)
			{
				// reset the delay lag if new delay type
				if (! empty($cssarray[$key]["delay"]))
				{
					$delay_lag = false;
				}
				if (! empty($css["delay"]) || $delay_lag) // here we use !empty as a direct equivalent of isset && =true
				{
					if (! $delay_lag)
					{
						$delay_type = $cssarray[$key]["delay_type"];
					}
					else
					{
						$cssarray[$key]["delay_type"] = $delay_type; // aligning the outer and inner keys
					}
					$delayable[$delay_type]["items"][$key] = $cssarray[$key];
		
					unset($cssarray[$key]);
		
					$delay_lag = $preceedence ? true : false;
				}
			}
		
			if (isset($delayable) && isset($stored_delayable))
			{
		
				$delayable = array_replace_recursive($stored_delayable, $delayable); // changed from array_merge_recirsive to maintain keys
			}
			elseif (isset($stored_delayable) && ! isset($delayable))
			{
				$delayable = $stored_delayable;
			}
		
			if (! empty($delayable))
			{
				// lets sort this in two parts to ensure sorting
				$mousemove = $delayable['mousemove']['items'];
				$scroll = $delayable['scroll']['items'];
				$async = $delayable['async']['items'];
				if (isset($mousemove))
				{
					ksort($mousemove);
					$delayable['mousemove']['items'] = $mousemove;
				}
				if (isset($scroll))
				{
					ksort($scroll);
					$delayable['scroll']['items'] = $scroll;
				}
				if (isset($async))
				{
					ksort($async);
					$delayable['async']['items'] = $async;
				}
		
				self::$_delayable_segment_css = $delayable;
			}
			Return $cssarray;
		
		}
		
		protected function correctSignatureHashCss($obj)
		{
		
			if (empty($obj))
			{
				Return false;
			}
		
			foreach ($obj as $key => $css)
			{
				$sig = $css["signature"];
				$alt_sig = $css["alt_signature"];
				if (! isset(self::$_signature_hash_css[$sig]))
				{
					self::$_signature_hash_css[$sig] = true;
				}
				if (isset($alt_sig) && ! isset(self::$_signature_hash_css[$alt_sig]))
				{
		
					self::$_signature_hash_css[$alt_sig] = true;
				}
			}
			Return true;
		
		}
		
		protected function correctDelaySignatureHashCss($delay_obj)
		{
		
			if (empty($delay_obj))
			{
				Return false;
			}
		
			foreach ($delay_obj as $object)
			{
		
				$this->correctSignatureHashCss($object["items"]);
			}
		
		}
		
		protected function moderateDefaultLoadsections($jsarray)
		{
		
			foreach ($jsarray as $key => $js)
			{
				if (isset($js["loadsection"]) && $js["loadsection"] == 0)
				{
					$jsarray[$key]["loadsection"] = 2;
				}
			}
			Return $jsarray;
		
		}
		

		protected function isGroupWorthy($cssarray, $key)
		{
		
			foreach ($cssarray as $key2 => $css)
			{
				if ($key == $key2)
				{
					continue;
				}
				// check loadsections && group_number
				if ($cssarray[$key]["loadsection"] == $css["loadsection"] && $cssarray[$key]["group_number"] == $css["group_number"])
				{
					Return true;
				}
			}
			Return false;
		
		}
		
		protected function assignCssGroups($cssarray, $table)
		{
		
			foreach ($cssarray as $key => $css)
			{
		
				// exclude cdns
				if (! $cssarray[$key]["grouping"] || $cssarray[$key]["loadsection"] >= 5)
				{
		
					continue;
				}
				if ($table->css_maintain_preceedence)
				{
					// if any one side is internal but not library group but not cdn
					// we need to account for missing keys due to setIgnore and dontLoad operations
					$prev_key = $this->getPreviousCssKey($key, $cssarray);
					$next_key = $this->getNextCssKey($key, $cssarray);
					if (((! empty($prev_key) || $prev_key === 0) && isset($cssarray[$prev_key]) && // following rules only if the previous key exists
							($cssarray[$prev_key]["loadsection"] == $cssarray[$key]["loadsection"])) || // dont group varying loadsections
							// should not be a cdn cdnalias
							// can be grouped with next key
							((! empty($next_key) || $next_key === 0) && isset($cssarray[$next_key]) && ($cssarray[$next_key]["loadsection"] == $cssarray[$key]["loadsection"])))
							// checking next internal
					{
		
						if ($css["group_number"] != 0)
						{
		
							$group_code = "group-" . $css["loadsection"] . "-sub-" . $css["group_number"];
						}
						else
						{
							$group_code = "group-" . $css["loadsection"];
						}
		
						$cssarray[$key]["group"] = $group_code;
					}
				}
				else
				{
					$group_worthy = $this->isGroupWorthy($cssarray, $key);
					if ($group_worthy)
					{
						if ($css["group_number"] != 0)
						{
		
							$group_code = "group-" . $css["loadsection"] . "-sub-" . $css["group_number"];
						}
						else
						{
							$group_code = "group-" . $css["loadsection"];
						}
						$cssarray[$key]["group"] = $group_code;
					}
				}
			}
		
			Return $cssarray;
		
		}
		
		protected function getNextCssKey($key, $cssarray)
		{
		
			if (empty($cssarray))
			{
				Return false;
			}
			$max_key = max(array_keys($cssarray));
			$key ++;
			while ($key <= $max_key)
			{
				if (isset($cssarray[$key]) && ! empty($cssarray[$key]["grouping"]))
				{
					Return $key;
				}
				$key ++;
			}
			Return false;
		
		}
		
		protected function getPreviousCssKey($key, $cssarray)
		{
		
			$key --;
			while ($key >= 0)
			{
				if (isset($cssarray[$key]) && ! empty($cssarray[$key]["grouping"]))
				{
					Return $key;
				}
				$key --;
			}
			Return false;
		
		}
		
		protected function initialiseCssGroupHash($cssarray)
		{
		
			foreach ($cssarray as $key => $value)
			{
				if (isset($cssarray[$key]["group"]))
				{
					$group_number = $cssarray[$key]["group"];
		
					self::$_groups_css[$group_number]["name"] = $group_number;
					self::$_groups_css[$group_number]["url"] = null; // this is the raw url
					self::$_groups_css[$group_number]["callable_url"] = null; // a getScript code with url embed
					self::$_groups_css[$group_number]["css_tag_url"] = null; // a script taged url
		
					self::$_groups_css[$group_number]["combined_code"] = null;
					self::$_groups_css[$group_number]["success"] = null;
		
					self::$_groups_css[$group_number]["items"][] = $value;
				}
			}
		
		}
		
		protected function combineCssGroupCode($tbl)
		{
		
			if (empty(self::$_groups_css))
			{
				Return false;
			}
		
			foreach (self::$_groups_css as $group_name => $group)
			{
				self::$_groups_css[$group_name]["combined_code"] = $this->getCombinedCssCode($group["items"], $tbl);
				self::$_groups_css[$group_name]["success"] = ! empty(self::$_groups_css[$group_name]["combined_code"]) ? true : false;
			}
		
		}
		
		protected function getCombinedCssCode($grp, $tbl)
		{
		
			if (empty($grp))
			{
				Return false;
			}
			//$app = JFactory::getApplication();
		
			foreach ($grp as $key => $group)
			{
		
				$begin_comment = "/* Inserted by MulticacheGroup Css insert	key-" . $key . "	rank-" . $group["rank"] . "  src-" . substr($group["src"], 0, 10) . " */";
				$end_comment = "/* end MulticacheGroup Css insert */";
				$begin_comment_code = "/* Inserted by MulticacheGroup Css  inline insert	key-" . $key . "	rank-" . $group["rank"] . "   */";
				$end_comment_code = "/* end MulticacheGroup Css inline insert */";
		
				if (! empty($group["href"]))
				{
					// actual question here is is this source or is this code
					// source the code and place it here
					// ensure it ends with ;
					$url = ! empty($group["absolute_src"]) ? $group["absolute_src"] : $group["href_clean"];
					$url = MulticacheHelper::checkCurlable($url);
					if (isset(self::$_mediaVersion))
					{
						$c_uri = MulticacheUri::getInstance($url);
						$c_uri->setVar('mediaFormat', self::$_mediaVersion);
						$url = $c_uri->toString();
					}
		
					$curl_obj = MulticacheHelper::get_web_page($url);
					
					// lets try again with href
					if ($curl_obj["http_code"] != 200)
					{
						if (! empty($group["href"]))
						{
							$url_temp = $group["href"];
							$url = MulticacheHelper::Checkurl($url_temp, self::$_mediaVersion);
							$url = MulticacheHelper::checkCurlable($url);
							$curl_obj = MulticacheHelper::get_web_page($url);
						}
					}
					//testversion1.0.0.4
					if ($curl_obj["http_code"] != 200)
					{
						if (! empty($group["href"]))
						{
							$url_temp = $group["href"];
							$url = MulticacheHelper::Checkurl($url_temp, self::$_mediaVersion);
							$url = MulticacheHelper::checkCurlable($url);
							$curl_obj = MulticacheHelper::get_web_page($url ,true);
						}
					}
					//end test
					//var_dump($url , $curl_obj);exit;
					if ($curl_obj["http_code"] == 200)
					{
						// start experiment to replace backgroundimages with absolute urls
						// echo "experiment<br>";
						$abs_content = $this->replaceAtImports($curl_obj["content"], $group);
						$abs_content = $this->replaceImgUrls($abs_content, $group);
		
						// end experiment
						// $code_string .= $begin_comment . MulticacheHelper::clean_code(trim($curl_obj["content"])) . $end_comment;
						if ($tbl->compress_css)
						{
							$ret_content = trim(MulticacheCSSOptimize::optimize($abs_content));
						}
						else
						{
							$ret_content = $abs_content;
						}
		
						$code_string .= ! empty(self::$_css_comments) ? $begin_comment . $ret_content . $end_comment : trim($ret_content);
					}
					else
					{
						// register error
		
						$e_message = "	" . $curl_obj["errmsg"] . " uri- " . $url;
						MulticacheHelper::prepareMessageEnqueue(__('PageCss getCombinedCode curl error','multicache-plugin'),'error');
						if($this->debug){
							MulticacheHelper::log_error(__('PageCss getCombinedCode curl error','multicache-plugin','error'),$this->error_log,$e_message);
						}
						//$app->enqueueMessage(JText::_('COM_MULTICACHE_ERROR_PAGECSS_GETCOMBINECODE_CURL_ERROR') . $e_message . '   response-' . $curl_obj["http_code"], 'warning');
						Return false;
					}
				}
				else
				{
					// unserialize and tie code here
					if (! empty($group["serialized_code"]))
					{
						// Not required for style tags
						// $this->replaceImgUrls(unserialize($group["serialized_code"]) , $group);
						// $code_string .= $begin_comment_code . MulticacheHelper::clean_code(trim(unserialize($group["serialized_code"]))) . $end_comment_code;
						if ($tbl->compress_css)
						{
							$unserialized_code = MulticacheCSSOptimize::optimize(unserialize($group["serialized_code"]));
						}
						else
						{
							$unserialized_code = unserialize($group["serialized_code"]);
						}
		
						$code_string .= ! empty(self::$_css_comments) ? $begin_comment_code . trim($unserialized_code) . $end_comment_code : trim($unserialized_code);
					}
					else
					{
						// register error
						MulticacheHelper::prepareMessageEnqueue(__('PageCss Group not href code empty','multicache-plugin'),'error');
						if($this->debug)
						{
							MulticacheHelper::log_error(__('PageCss Group not href code empty','multicache-plugin'),$this->error_log);
						}
						//$app->enqueueMessage(JText::_('COM_MULTICACHE_ERROR_PAGECSS_GROUP_NOT_HREF_CODE_EMPTY_ROOT_SCRIPT_DETECT_ERROR'), 'warning');
						Return false;
					}
				}
			}
		
			Return serialize($code_string);
		
		}
		
		
		protected function replaceAtImports($content, $group)
		{
		
			$pattern = '~(?>[/@]?[^/@]*+(?:/\*(?>\*?[^\*]*+)*?\*/)?)*?\K(?:@import[^;}]++;?|\K$)~i';
			$replacedContent = preg_replace_callback($pattern, 'self::repAtImports', $content);
			Return $replacedContent;
		
		}
		
		protected static function repAtImports($matches)
		{
		
			if (empty($matches[0]))
			{
				Return $matches[0];
			}
			$url_pattern = '~(?:http:|https:|)\/\/[^\'"]+~';
			preg_match($url_pattern, $matches[0], $url_matches);
			self::$_atimports_prop[] = $url_matches[0];
			Return '';
		
		}
		
		protected function replaceImgUrls($content, $group)
		{
		
			self::$_temp_group = $group;
			$e = self::DOUBLE_QUOTE_STRING . '|' . self::SINGLE_QUOTE_STRING . '|' . self::BLOCK_COMMENTS . '|' . self::LINE_COMMENTS;
			$replacedContent = preg_replace_callback("#(?>[(]?[^('\"/]*+(?:{$e}|/)?)*?(?:(?<=url)\(\s*+\K['\"]?((?<!['\"])[^\s)]*+|(?<!')[^\"]*+|[^']*+)['\"]?|\K$)#i", 'self::replaceImages', $content);
			/*
			 * $sCorrectedContent = preg_replace_callback(
			 * "#(?>[(]?[^('\"/]*+(?:{$e}|/)?)*?(?:(?<=url)\(\s*+\K['\"]?((?<!['\"])[^\s)]*+|(?<!')[^\"]*+|[^']*+)['\"]?|\K$)#i",
			 * function ($aMatches) use ($aUrl, $obj)
			 * {
			 * return $obj->_correctUrlCB($aMatches, $aUrl);
			 * }, $sContent);
			*/
		
			Return $replacedContent;
		
		}
		
		protected static function replaceImages($matches)
		{
			// need to test the preg_match of last arguement
			if (! isset($matches[1]) || $matches[1] == '' || preg_match('#^(?:\(|/(?:/|\*))#', $matches[0]))
			{
				return $matches[0];
			}
			$imageurl = $matches[1];
			if (preg_match('#^data:#', $imageurl))
			{
				Return $matches[0];
			}
		
			$grp = self::$_temp_group;
			// handling external scripts
			if (! empty($grp["href"]) && isset($grp["internal"]) && $grp["internal"] == false)
			{
				$base_uri = $grp["href_clean"];
		
				$complete_image = self::resolveAbsolute($base_uri, $imageurl);
			}
			elseif (! empty($grp["href"]) && isset($grp["internal"]) && $grp["internal"] == true)
			{
				$base_uri = $grp["absolute_src"];
				$complete_image = self::resolveAbsolute($base_uri, $imageurl);
			}
			elseif (empty($grp["href"]) && ! isset($grp["internal"]) && ! empty($grp["serialized_code"]))
			{
				// redundant as we do not need to do this for style tags
				$base_uri = MulticacheUri::base();
				$complete_image = self::resolveAbsolute($base_uri, $imageurl);
			}
		
			// var_dump(preg_match('#^(?:\(|/(?:/|\*))#', $imageurl));?
		
			// var_dump(preg_match('#^/|://#', $imageurl));//matches an relative/ absolute url
		
			// var_dump(preg_match('#(?<!\\\\)[\s\'"(),]#', $imageurl));
			/*
			 * echo "<br> compelet image , image";
			 * var_dump($complete_image, $imageurl);
			 * echo "<br>dumping url<br>";
			 * var_dump($grp["href"], $grp["absolute_src"],$base_uri);
			 */
		
			Return $complete_image;
		
		}
		
		protected static function resolveAbsolute($url, $image_url)
		{
		
			$base1_uri = $base2_uri = $base3_uri = $base4_uri = null;
			$base_uri = $url;
		
			$uri_instance = MulticacheUri::getInstance($base_uri);
			$base0_uri = $uri_instance->toString(array(
					"scheme",
					"host"
			));
			$base_uri_array = explode('/', $base_uri);
			$nextpath = $uri_instance->getPath();
		
			if ($nextpath)
			{
				array_pop($base_uri_array);
				$base1_uri = implode('/', $base_uri_array); // one down
				$nextpath = MulticacheUri::getInstance($base1_uri)->getPath();
			}
		
			if (isset($nextpath))
			{
				array_pop($base_uri_array);
				$base2_uri = implode('/', $base_uri_array); // two down
				$nextpath = MulticacheUri::getInstance($base2_uri)->getPath();
			}
			if (isset($nextpath))
			{
				$exists = array_pop($base_uri_array);
				$base3_uri = implode('/', $base_uri_array); // three down
				$nextpath = MulticacheUri::getInstance($base3_uri)->getPath();
			}
			if (isset($nextpath))
			{
				$exists = array_pop($base_uri_array);
				$base4_uri = implode('/', $base_uri_array); // four down
				// $nextpath = MulticacheUri::getInstance($base4_uri)->getPath();
			}
		
			// start rules
			// Reference : http://www.ietf.org/rfc/rfc3986
			// assumption http://a/b/c/d;p?q
			// lets handle Normal cases of image uri
			// case 1: /image
			// case2 : image tested :confirmed
			// case 3: ../image
			// case4 : ./image
			// case 5a: ..image
			// case5b : .image
			// case 6a : ../..image
			// case 6b : ../../image
			// case 7a : // absolute url
			// case 7b : http:// absolute url
			// case 7c :https://
			// case 8b ../../../image
			// case 8a ../../..image
			if (strpos($image_url, '//') === 0 || strpos($image_url, 'http://') === 0 || strpos($image_url, 'https://') === 0)
			{
				$complete_image = $image_url; // absolute urls
			}
			elseif (strpos($image_url, '/') === 0)
			{
				// case 1:
				// tested:simulation
				$complete_image = $base0_uri . $image_url;
			}
			elseif (strpos($image_url, '../../../') === 0)
			{
		
				// case: "../../" = "http://a/" case 8b
				// tested:simulation
				$complete_image = $base0_uri . substr($image_url, 8);
			}
			elseif (strpos($image_url, '../../..') === 0)
			{
		
				// case: "../../" = "http://a/" case 8a
				// tested:simulation
				$complete_image = $base0_uri . '/' . substr($image_url, 8);
			}
			elseif (strpos($image_url, '../../') === 0)
			{
		
				// case: "../../" = "http://a/" case 6b
				// tested:simulation
				$complete_image = isset($base3_uri) ? $base3_uri . substr($image_url, 5) : $base0_uri . substr($image_url, 5);
			}
			elseif (preg_match('/^\.\.\/\.\.[A-Za-z0-9]+/', $image_url))
			{
		
				// case: "../.." = "http://a/" case 6a
				// tested:simulation
				$complete_image = isset($base3_uri) ? $base3_uri . '/' . substr($image_url, 5) : $base0_uri . '/' . substr($image_url, 5);
			}
			elseif (strpos($image_url, './') === 0)
			{
				// case: "./" = "http://a/b/c/" case 4
				// tested:simulation
				$complete_image = isset($base1_uri) ? $base1_uri . substr($image_url, 1) : $base0_uri . substr($image_url, 1);
			}
			elseif (strpos($image_url, '../') === 0)
			{
				// case: "../" = "http://a/b/" case 3
				// tested:simulation
				$complete_image = isset($base2_uri) ? $base2_uri . substr($image_url, 2) : $base0_uri . substr($image_url, 2);
			}
			elseif (preg_match('/^\.[A-Za-z0-9]+/', $image_url))
			{
				// "." = "http://a/b/c/" case 5b
				// tested:simulation
				$complete_image = isset($base1_uri) ? $base1_uri . '/' . substr($image_url, 1) : $base0_uri . '/' . substr($image_url, 1);
			}
			elseif (preg_match('/^\.\.[A-Za-z0-9]+/', $image_url))
			{
				// ".." = "http://a/b/" case 5a
				// tested:simulation
				$complete_image = isset($base2_uri) ? $base2_uri . '/' . substr($image_url, 2) : $base0_uri . '/' . substr($image_url, 2);
			}
			elseif (preg_match('/^[A-Za-z0-9]+/', $image_url))
			{
		
				// case 2 : "g" = "http://a/b/c/g"
				// tested:confirmed
				$complete_image = isset($base1_uri) ? $base1_uri . '/' . $image_url : $base0_uri . '/' . $image_url;
			}
			Return $complete_image;
		
		}
		
		protected function prepareCssGrouploadableUrl()
		{
		
			if (! isset(self::$_groups_css))
			{
				Return false;
			}
		
			foreach (self::$_groups_css as $key => $grp)
			{
				if ($grp["success"])
				{
		
					self::$_groups_css[$key]["url"] = MulticacheHelper::getCsscodeUrl($key, "raw_url", self::$_principle_jquery_scope, self::$_mediaVersion);
					self::$_groups_css[$key]["callable_url"] = MulticacheHelper::getCsscodeUrl($key, "link_url", self::$_principle_jquery_scope, self::$_mediaVersion);
					self::$_groups_css[$key]["css_tag_url"] = MulticacheHelper::getCsscodeUrl($key, "link_url", self::$_principle_jquery_scope, self::$_mediaVersion);
				}
			}
		
		}
		
		protected function writeGroupCssCode($tbl)
		{
		
			if (! isset(self::$_groups_css))
			{
				Return false;
			}
		
			foreach (self::$_groups_css as $key => $grp)
			{
				if ($grp["success"])
				{
					$file_name = $grp["name"] . ".css";
					if ($tbl->compress_css)
					{
						$ret_content = trim(MulticacheCSSOptimize::optimize(unserialize($grp["combined_code"])));
					}
					else
					{
						$ret_content = trim(unserialize($grp["combined_code"]));
					}
					$success = MulticacheHelper::writeCssCache($ret_content, $file_name, $tbl->css_switch);
					self::$_groups_css[$key]["success"] = ! empty($success) ? true : false;
				}
			}
		
		}
		
		protected function makeCssDelaycode()
		{
			// writes the first level js to be called by the main page
			if (empty(self::$_delayable_segment_css))
			{
				Return false;
			}
		
			foreach (self::$_delayable_segment_css as $key => $value)
			{
				$delay_code = MulticacheHelper::getCssdelaycode($key, self::$_principle_jquery_scope, self::$_mediaVersion); // initialises the delay code
		
				if (! empty($delay_code))
				{
					self::$_delayable_segment_css[$key]["delay_executable_code"] = $delay_code["code"];
					self::$_delayable_segment_css[$key]["delay_callable_url"] = $delay_code["url"];
				}
			}
		
		}
		
		protected function segregatePlaceCssDelay($tbl)
		{
		
			if (empty(self::$_delayable_segment_css))
			{
				Return false;
			}
			//$app = JFactory::getApplication();
			//
			
			foreach (self::$_delayable_segment_css as $key_delaytype => $delay_seg)
			{
				if ($key_delaytype == 'async')
				{
		
					continue;
				}
				$success = $this->placeCssDelayedCode($delay_seg, $tbl);
				if ($success)
				{
					self::$_delayable_segment_css[$key_delaytype]["success"] = true;
				}
				else
				{
					self::$_delayable_segment_css[$key_delaytype]["success"] = false;
					MulticacheHelper::clearAllNotices();
					MulticacheHelper::prepareMessageEnqueue(__('PageCss failed to place delay','multicache-plugin'). $key_delaytype,'error');
					if($this->debug){
						MulticacheHelper::log_error(__('PageCss failed to place delay','multicache-plugin'). $key_delaytype,$this->error_log);
					}
					//$app->enqueueMessage(JText::_('COM_MULTICACHE_CLASS_MULTICACHE_PAGE_CSS_PLACE_DELAY_FAILED') . $key_delaytype, 'error');
					wp_redirect(admin_url().'admin.php?page=multicache-config-menu#page-css-tweaks');
					exit;//$app->redirect('index.php?option=com_multicache&view=config&layout=edit&id=1#page-js-tweaks');
				}
			}
			
			if (isset(self::$_delayable_segment_css["async"]))
			{
				$success = $this->placeCssAsyncInlineCode(self::$_delayable_segment_css["async"], $tbl);
				$this->prepareNoscriptAsync(self::$_delayable_segment_css["async"], $tbl);
				if ($success)
				{
					self::$_delayable_segment_css["async"]["inline_async"] = true;
				}
			}
			
		
		}
		
		protected function placeCssDelayedCode($grp, $tbl)
		{
		
			if (empty($grp["items"]) || empty($grp["delay_callable_url"]))
			{
				Return false;
			}
			//$app = JFactory::getApplication();
		
			// start
			if (! isset(self::$_principle_jquery_scope))
			{
				self::$_principle_jquery_scope = "jQuery";
			}
			$begin_comment = "<!-- Begin delay prepared by MulticacheCss for " . $grp["delay_callable_url"] . "	-->";
			// $code_string = $begin_comment;
			if (! empty(self::$_css_comments))
			{
				$code_string = $begin_comment;
			}
		
			foreach ($grp["items"] as $key => $group)
			{
				$sig = $group["signature"];
		
				if (isset(self::$_cdn_segment[$sig]) && (bool) self::$_cdn_segment[$sig] == true)
				{
					$url = self::$_cdn_segment[$sig];
		
					$c_string = MulticacheHelper::getCsslinkUrl($url, 'link_url', self::$_mediaVersion);
					$code_string .= $c_string;
					$link = '<link type="text/css" href="' . $url . '" />';
					$serialized = serialize($link);
					self::$_delayed_noscript .= $link;
				}
				elseif (! isset($group["internal"]) && ! empty($group["code"]))
				{
					$begin_comment = "
                <!-- Multicache Insert for  code   " . str_replace("'", "", str_replace('"', "", substr($group["code"], 0, 10))) . " -->
";
		
					$end_comment = "
		
<!-- end insert of code 	  " . str_replace("'", "", str_replace('"', "", substr($group["code"], 0, 10))) . " -->";
					// unserialize and tie code here
					if (isset($group["serialized_code"]))
					{
						// $code_string .= $begin_comment . MulticacheHelper::clean_code(trim(unserialize($group["serialized_code"]))) . $end_comment;
						// $code_string .= ! empty(self::$_css_comments) ? $begin_comment . '<style type="text/css">' . trim(unserialize($group["serialized_code"])) . '</style>' . $end_comment : '<style type="text/css">' . trim(unserialize($group["serialized_code"])) . '</style>';
						if ($tbl->compress_css)
						{
		                    $c_string = trim(MulticacheCSSOptimize::optimize(unserialize($group["serialized_code"])));
							$code_string .= ! empty(self::$_css_comments) ? $begin_comment . '<style type="text/css">' . $c_string  . '</style>' . $end_comment : '<style type="text/css">' . $c_string . '</style>';
						}
						else
						{
							$c_string = trim(unserialize($group["serialized_code"]));
							$code_string .= ! empty(self::$_css_comments) ? $begin_comment . '<style type="text/css">' . $c_string . '</style>' . $end_comment : '<style type="text/css">' . $c_string . '</style>';
						}
						self::$_delayed_noscript .= '<style>' . $c_string . '</style>';
					}
					else
					{
						// register error
						MulticacheHelper::prepareMessageEnqueue(__('PageCss url not internal, not code in delay','multicache-plugin'),'error');
						if($this->debug)
						{
							MulticacheHelper::log_error(__('PageCss url not internal, not code in delay','multicache-plugin'),$this->error_log);
						}
						
		
						//$app->enqueueMessage(JText::_('COM_MULTICACHE_CLASS_MULTICACHE_PAGE_CSS_URL_NOT_INTERNAL_NOT_CODE_INDELAY_ERROR'), 'error');
						Return false;
					}
				}
		
				elseif (isset($group["href"]))
				{
					if (preg_match('/[^a-zA-Z0-9\/\:\?\#\.]/', $group["href"]) && empty($group["internal"]))
					{
						$url = $group["href"];
						$c_string = MulticacheHelper::getCsslinkUrl($url, 'plain_url', self::$_mediaVersion);
					}
					else
					{
						$url = isset($group["absolute_src"]) ? $group["absolute_src"] : $group["href_clean"];
						$c_string = MulticacheHelper::getCsslinkUrl($url, 'link_url', self::$_mediaVersion);
					}
		
					$code_string .= $c_string;
					self::$_delayed_noscript .= unserialize($group["serialized"]);
				}
			}
			
			$end_comment = "<!-- End of Css delay prepared by Multicache for " . $grp["delay_callable_url"] . "	-->";
			// $code_string .= $end_comment;
			if (! empty(self::$_css_comments))
			{
				$code_string .= $end_comment;
			}
			ob_start();
			echo $code_string;
			$buffer = ob_get_clean();
			$return = MulticacheHelper::writeCssCache($buffer, $grp["delay_callable_url"], true);
		
			Return $return;
		
		}
		
		protected function placeCssAsyncInlineCode($grp, $tbl)
		{
		
			if (empty($grp["items"]) || empty($grp["delay_callable_url"]))
			{
				Return false;
			}
			//$app = JFactory::getApplication();
			$has_inline = null;
			$inline_async = '';
			foreach ($grp["items"] as $key => $group)
			{
				if (empty($group["serialized_code"]))
				{
					continue;
				}
				$has_inline = true;
				$inline_async .= unserialize($group["serialized_code"]);
			}
			if (isset($has_inline) && ! empty($inline_async))
			{
				if ($tbl->compress_css)
				{
					$inline_async = MulticacheCSSOptimize::optimize($inline_async);
				}
		
				ob_start();
				echo $inline_async;
				$buffer = ob_get_clean();
				$return = MulticacheHelper::writeCssCache($buffer, $grp["delay_callable_url"], true);
				Return $return;
			}
		
			Return false;
		
		}
		
		protected function prepareNoscriptAsync($grp, $tbl)
		{
		
			if (empty($grp["items"]))
			{
				Return false;
			}
			//$app = JFactory::getApplication();
			foreach ($grp["items"] as $key => $group)
			{
				$sig = $group["signature"];
		
				if (isset(self::$_cdn_segment[$sig]) && (bool) self::$_cdn_segment[$sig] == true)
				{
					$url = self::$_cdn_segment[$sig];
		            $link = '<link type="text/css" href="' . $url . '" />';
					$serialized = serialize($link);
					self::$_delayed_noscript .= $link;
				}
				elseif (! isset($group["internal"]) && ! empty($group["code"]))
				{
		
					// unserialize and tie code here
					if (isset($group["serialized_code"]))
					{
						$inline_async = unserialize($group["serialized_code"]);
						if ($tbl->compress_css)
						{
							$inline_async = MulticacheCSSOptimize::optimize($inline_async);
						}
						self::$_delayed_noscript .= '<style>' . $inline_async . '</style>';
					}
					else
					{
						// register error
		MulticacheHelper::prepareMessageEnqueue(__('NoScript Async error PageCss not internal not code','multicache-plugin'),'error');
		if($this->debug){
		MulticacheHelper::log_error(__('NoScript Async error PageCss not internal not code','multicache-plugin'),$this->error_log);
		}
						//$app->enqueueMessage(JText::_('COM_MULTICACHE_CLASS_MULTICACHE_PAGE_CSS_URL_NOT_INTERNAL_NOT_CODE_NOSCRIPTASYNC_ERROR'), 'error');
						Return false;
					}
				}
		
				elseif (isset($group["href"]))
				{
		
					self::$_delayed_noscript .= unserialize($group["serialized"]);
				}
			}
		
		}
		
		protected function prepareCssLoadSections($cssarray, $tbl , $param)
		{
		
			self::$_loadsections_css[1] = $this->getCssLoadSection(1, $cssarray, $tbl, $param);
			self::$_loadsections_css[2] = $this->getCssLoadSection(2, $cssarray, $tbl, $param);
			self::$_loadsections_css[3] = $this->getCssLoadSection(3, $cssarray, $tbl, $param);
			self::$_loadsections_css[4] = $this->getCssLoadSection(4, $cssarray, $tbl, $param);
		
		}
		
		protected function correctCssLoadsectionAturl()
		{
		
			if (empty(self::$_atimports_prop))
			{
				Return;
			}
			foreach (self::$_atimports_prop as $impurls)
			{
				$links .= MulticacheHelper::getCsslinkUrl($impurls, 'plain_url');
			}
			// if loadsection 1 is empty put in 2
			$load_content = '';
			if (empty(self::$_loadsections_css[1]))
			{
				$load_content = ! empty(self::$_loadsections_css[2]) ? $links . unserialize(self::$_loadsections_css[2]) : $links;
				self::$_loadsections_css[2] = serialize($load_content);
			}
			else
			{
				$load_content = $links . unserialize(self::$_loadsections_css[1]);
				self::$_loadsections_css[1] = serialize($load_content);
			}
		
		}
		
		protected function combineCssDelay( $params_extended = null)
		{
			//if were not using jstweaks were ombining css delay scripts here itself
			
			if (empty(self::$_delayable_segment_css) && empty($params_extended))
			{
				Return false;
			}
			$delay = null;
			$delay_async = null;
			$delay_async_head = null; 
			$delay_async_foot = null;
			$load_string = "";
			$n_script = '';
			if (isset(self::$_delayable_segment_css["scroll"]) || isset(self::$_delayable_segment_css["mousemove"]))
			{
				$delay = self::$_principle_jquery_scope . "( document ).ready(function() {";
				foreach (self::$_delayable_segment_css as $delay_type_key => $delay_obj)
				{
					if ($delay_type_key == 'async')
					{
						continue;
					}
					if (! empty($delay_obj["delay_executable_code"]))
					{
						$delay .= unserialize($delay_obj["delay_executable_code"]);
					}
				}
				$delay .= "});";
				// we need to code in the async delay call
				
				// $ds = MulticacheHelper::getloadableCodeScript( $delay ,false );
			}
		
			
			if (isset(self::$_delayable_segment_css["async"]))
			{
				// lets get the src bits
				$async_src_bits = MulticacheHelper::getAsyncSrcbits(self::$_delayable_segment_css["async"]);
				$delay_async_head = unserialize(self::$_delayable_segment_css["async"]["delay_executable_code"]);
				$delay_async_head = MulticacheJSOptimize::process($delay_async_head);
				$delay_async_head = MulticacheHelper::getloadableCodeScript($delay_async_head, false, true);
				$delay_async_foot = MulticacheJSOptimize::process($async_src_bits);
				$delay_async_foot = MulticacheHelper::getloadableCodeScript($delay_async_foot, false, true);
				
			}
			if(!empty($params_extended['css_groupsasync']))
			{
				 
				$group_async_src_bits = MulticacheHelper::getGroupAsyncSrcbits(self::$_groups_css);
				
				if(!isset($delay_async_head))
				{
				$group_async_head = MulticacheHelper::getCssdelaycode('async' , null);
				
				$group_async_head = unserialize($group_async_head['code']);
				$group_async_head = MulticacheJSOptimize::process($group_async_head);
				$group_async_head = !empty($group_async_head) ? MulticacheHelper::getloadableCodeScript($group_async_head, false, true) : null;
				
				}
				$group_async_foot = $group_async_src_bits['inline_code'];
				$group_async_foot = MulticacheJSOptimize::process($group_async_foot);
				$group_async_foot_noscript = $group_async_src_bits['noscript'];
				$group_async_foot = !empty($group_async_foot) ? MulticacheHelper::getloadableCodeScript($group_async_foot, false, true) : null;
				
				//
			}
			
			$loadsections = self::$_loadsections_css;
			
			if (isset($delay_async_head) 
					||(isset($params_extended['css_groupsasync']) 
					&& !empty($group_async_head)))
			{
				$load_string_head = "";
				$head_segment = $loadsections[2];
				if (! empty($head_segment))
				{
					
					$load_string_head = unserialize($head_segment);
				}
				if (isset($delay_async_head))
				{
					$load_string_head .= $delay_async_head;
		
					self::$_loadsections_css[2] = serialize($load_string_head);
				}
				elseif(isset($params_extended['css_groupsasync']) 
					&& !empty($group_async_head))
					{
						$load_string_head .= $group_async_head;
						
						self::$_loadsections_css[2] = serialize($load_string_head);
						
				}
			}
			
			$footer_segment = $loadsections[4];
			// we can choose this point to load asyn delay type in head
			
			if (! empty($footer_segment))
			{
				$load_string = unserialize($footer_segment);
			}
			// we'll need to maintain get loadable code script here, as the css delays are executed through javascript
		
			if (isset($delay))
			{
				
				$delay = MulticacheJSOptimize::process($delay);
				
				
				$load_string .= MulticacheHelper::getloadableCodeScript($delay, false , true);
				
			}
			if (isset($delay_async_foot))
			{
				
				$load_string .= $delay_async_foot;
			}
			if (!empty($group_async_foot))
			{
				$load_string .= $group_async_foot;
			}
			
			if (! empty(self::$_delayed_noscript))
			{
				
				$n_script .= self::$_delayed_noscript;
				
				
				
			}
			if(!empty($group_async_foot_noscript))
			{
				$n_script .= $group_async_foot_noscript;
				
			}
			if(!empty($n_script))
			{
				$n_script = MulticacheCSSOptimize::optimize($n_script);
				$n_script = MulticacheHelper::noscriptWrap($n_script , true);
				
				$load_string .= $n_script;
			}
		
			if (empty($load_string))
			{
				Return false;
			}
		
			self::$_loadsections_css[4] = serialize($load_string);
			
			Return true;
		
		}
		
		protected function loadProperty($property_name, $class_name = "MulticachePageScripts")
		{
		
			if (! class_exists($class_name))
			{
				Return null;
			}
		
			if (! property_exists($class_name, $property_name))
			{
				Return null;
			}
			Return $class_name::$$property_name;
		
		}
		
		protected function getCssLoadSection($section, $cssarray_obj, $tbl , $param)
		{
		
			//$app = JFactory::getApplication();
		
			foreach ($cssarray_obj as $obj)
			{
		
				if ($obj["loadsection"] != $section)
				{
					continue;
				}
		
				if (isset($obj["group"]) && (bool) ($group_name = $obj["group"]) == true && isset(self::$_groups_css[$group_name]["success"]) && self::$_groups_css[$group_name]["success"] == true)
				{
		
					if (! isset(self::$_groups_loaded_css[$group_name]) && empty($param['css_groupsasync']))
					{
		
						$load_string .= unserialize(self::$_groups_css[$group_name]["css_tag_url"]); //
		
						/*
						 * OTHER OPTIONS
						 *
						 * $load_string .= MulticacheHelper::getloadableSourceScript(self::$_groups[$group_name]["url"] , false);
						 *
						 * $load_string .= MulticacheHelper::getloadableSourceScript(unserialize( self::$_groups[$group_name]["callable_url"]) , false);
						*/
						self::$_groups_loaded_css[$group_name] = true;
					}
		
					continue;
				}
		
				$sig = $obj["signature"];
				if (isset(self::$_cdn_segment_css[$sig]) && (bool) self::$_cdn_segment_css[$sig] == true)
				{
		
					$load_string .= MulticacheHelper::getCsslinkUrl(self::$_cdn_segment[$sig], 'link_url', self::$_mediaVersion);
				}
				// if href else code
				elseif (! empty($obj["href"]))
				{
		
					// if obj int use only absolute
					if ($obj["internal"])
					{
		
						$load_string .= MulticacheHelper::getCsslinkUrl($obj["absolute_src"], 'link_url', self::$_mediaVersion);
					}
					elseif (! $obj["internal"])
					{
		
						// redundancy delared on purpose to maintain elseif formats
						// external source
						$load_string .= MulticacheHelper::getCsslinkUrl($obj["href"], 'link_url', self::$_mediaVersion);
					}
					/*
					 * MORE ELSEIF CAN COME HERE TO ENTERTAIN ALIAS LOADING ETC.
					 */
				}
				elseif (! empty($obj["code"]))
				{
					$unserialized_code = unserialize($obj["serialized_code"]);
					$code = ! empty($unserialized_code) ? $unserialized_code : $obj["code"];
					//
					if ($tbl->compress_css)
					{
						$load_string .= MulticacheHelper::getloadableCodeCss(MulticacheCSSOptimize::optimize($code), null, null, true);
					}
					else
					{
						$load_string .= MulticacheHelper::getloadableCodeCss($code);
					}
				}
				else
				{
		MulticacheHelper::prepareMessageEnqueue(__('Css loadsection undefined','multicache-plugin'),'error');
		if($this->debug)
		{
			MulticacheHelper::log_error(__('Css loadsection undefined','multicache-plugin'), $this->error_log);
		}
					//$app->enqueueMessage(JText::_('COM_MULTICACHE_CLASS_MULTICACHE_PAGE_CSS_LOADSECTION_UNDEFINED_CSS_TYPE_ERROR'), 'error');
				}
			}
			if (empty($load_string))
			{
		
				Return false;
			}
		
			Return serialize($load_string);
		
		}
		
	//END CSS methods
	
		//Begin JS Methods
		
		protected function prepareNonTableElements()
		{
		
			//$app = JFactory::getApplication();
			$page_script_object = $this->getRelevantPageScript(); // ATTENTION THIS SETTING IS RELATED TO VIEW: Better still to align by signatures but that would not give the option to the user to retain duplicate scripts
			if(empty($page_script_object))
			{
				Return false;
			}
		
			// get the array keys
			$template_keys = $this->getTemplateKeys($page_script_object);
		    $loadsection_input = MulticacheHelper::filterInputPost('multicache_config_script_tweak_options');
			//$jinput = JFactory::getApplication()->input;
			foreach ($page_script_object as $key => $obj)
			{
		
				foreach ($template_keys as $template_key)
				{
					$key_state_tag =  $template_key . '_' . $key;
					$current_state = $loadsection_input[$key_state_tag];//$jinput->get($key_state_tag);
		
					if (isset($current_state) && $current_state != $obj[$template_key])
					{
						$page_script_object[$key][$template_key] = $current_state;
					}
				}
		
				// attach the cdn url or reset the key
				
				$ident = $loadsection_input['ident_' . $key];
				$checkType = $loadsection_input['checkType_' . $key];
				$thenBack = $loadsection_input['thenBack_' . $key];
				$mautime = $loadsection_input['mautime_' . $key];
				
				
				
				if(!empty($ident))
				{
					$page_script_object[$key]['ident'] = $ident;
				}
				if(!empty($checkType))
				{
					$page_script_object[$key]['checktype'] = $checkType;
				}
				if(!empty($mautime))
				{
					$mautime = $mautime <30 || $mautime >=1000 ? 30 : $mautime;
					
					$page_script_object[$key]['mautime'] = $mautime;
				}
				if(!empty($thenBack))
				{
					if(strpos($thenBack , "'")!== false)
					{
						$thenBack = preg_replace("~\\\'~","'",$thenBack);
						
					}
					if(strpos($thenBack , '"')!== false)
					{
						$thenBack = preg_replace('~\\\"~','"',$thenBack);
						
					}
					
					$page_script_object[$key]['thenBack'] = $thenBack;
					$page_script_object[$key]['thenBack_json'] = json_encode($thenBack);
					$page_script_object[$key]['thenBack_serialized'] = serialize(htmlentities($thenBack));
					
				}
				$cdn_key = $loadsection_input['cdnalias_' . $key]; //$jinput->get('com_multicache_cdnalias_' . $key);
				$cdn_url = $loadsection_input['cdn_url_' . $key];// $jinput->getHtml('cdn_url_' . $key);
				if (! empty($cdn_key))
				{
		
					if (! empty($cdn_url))
					{
						$page_script_object[$key]['cdn_url'] = $cdn_url;
					}
					else
					{
						$page_script_object[$key]['cdnalias'] = 0;
						$page_script_object[$key]['cdn_url'] = null;
					}
				}
				// special case to clear out cdn url
				if (empty($cdn_key) /*&& ! empty($cdn_url)*/)
				{
					$page_script_object[$key]['cdn_url'] = null;
				}
			}
		
			Return $page_script_object;
		
		}
		
		protected function getRelevantPageScript()
		{
		
			//$app = JFactory::getApplication();
			if (! class_exists('MulticachePageScripts'))
			{
				MulticacheHelper::prepareMessageEnqueue(__('PageScripts class does not exist - please scrape template','multicache-plugin'));
				if($this->debug)
				{
					MulticacheHelper::log_error(__('PageScripts class does not exist','multicache-plugin'),$this->error_log);
				}
				//$message = JText::_('COM_MULTICACHE_CLASS_MULTICACHE_PAGE_SCRIPTS_DOESNOTEXIST');
				//$app->enqueueMessage($message, 'notice');
		
				Return false;
			}
		
			if (property_exists('MulticachePageScripts', 'working_script_array'))
			{
		
				$pagescripts = MulticachePageScripts::$working_script_array;
			}
			elseif (property_exists('MulticachePageScripts', 'original_script_array'))
			{
				$pagescripts = MulticachePageScripts::$original_script_array;
			}
			else
			{
				
				MulticacheHelper::prepareMessageEnqueue(__('PageScripts class has no defined properties','multicache-plugin'));
				if($this->debug)
				{
					MulticacheHelper::log_error(__('PageScripts class has no defined properties','multicache-plugin'),$this->error_log);
				}
				// register error Multicache Class exists with no proerties
				//$message = JText::_('COM_MULTICACHE_CLASS_MULTICACHE_PAGE_SCRIPTS_HASNODEFINEDPROPERTIES');
				//$app->enqueueMessage($message, 'error');
				Return false;
			}
		
			Return $pagescripts;
		
		}
		
		
		protected function getTemplateKeys($page_script_object)
		{
		
			$template_object = MulticacheHelper::getPageScriptObject($page_script_object);
			$template_object = $template_object->pagetransposeobject;
			if (empty($template_object))
			{
				Return false;
			}
			$template_keys = array();
			foreach ($template_object as $key => $value)
			{
				$template_keys[] = $key;
			}
			Return $template_keys;
		
		}
		
		
		protected function validatePageScript($page_new_script, $table)
		{
		
			$success = $this->validateLibrary($page_new_script);
		
			if (! $success)
			{
				Return false;
			}
			/*
			 * Any other validation here else you can return the success flg without requiring the if return structure
			 */
			// principle library cannot be delayed
			// in preceedence mode prior to principl library cannot be delayed
			$success = $this->applyDelayRules($page_new_script, $table);
			if (! $success)
			{
				Return false;
			}
			Return true;
		
		}
		
		
		protected function validateLibrary($page_new_script)
		{
		
			//$app = JFactory::getApplication();
			$library = false;
			$library_count = 0;
			foreach ($page_new_script as $key => $obj)
			{
				if (! empty($obj["library"]))
				{
					$library = true;
					$library_count ++;
				}
				if (! empty($obj["library"]) && ! empty($obj["ignore"]))
				{
					MulticacheHelper::clearAllNotices();
					MulticacheHelper::prepareMessageEnqueue(__('Principle library cannot be set to ignore','multicache-plugin'));
					if($this->debug)
					{
						MulticacheHelper::log_error(__('Principle library cannot be set to ignore','multicache-plugin'),$this->error_log);
					}
					//$app->enqueueMessage(JText::_('COM_MULTICACHE_ERROR_PAGESCRIPT_LIBRARY_CANNOTBEIGNORED'), 'warning');
					//$app->redirect('index.php?option=com_multicache&view=config&layout=edit&id=1#page-js-tweaks');
					wp_redirect(admin_url().'admin.php?page=multicache-config-menu#page-js-tweaks');
					exit;
				}
		
				if (! empty($obj["library"]) && $obj["loadsection"] >= 5)
				{
					MulticacheHelper::clearAllNotices();
					MulticacheHelper::prepareMessageEnqueue(__('Principle library cannot be unloaded','multicache-plugin'));
					if($this->debug)
					{
						MulticacheHelper::log_error(__('Principle library cannot be unloaded','multicache-plugin'),$this->error_log);
					}
					//$app->enqueueMessage(JText::_('COM_MULTICACHE_ERROR_PAGESCRIPT_LIBRARY_CANNOTBEUNLOADED'), 'warning');
					//$app->redirect('index.php?option=com_multicache&view=config&layout=edit&id=1#page-js-tweaks');
					wp_redirect(admin_url().'admin.php?page=multicache-config-menu#page-js-tweaks');
					exit;
				}
			}
			if (! $library || $library_count == 0)
			{
				MulticacheHelper::clearAllNotices();
				MulticacheHelper::prepareMessageEnqueue(__('Principle library not set','multicache-plugin'));
				if($this->debug)
				{
					MulticacheHelper::log_error(__('Principle library not set','multicache-plugin'),$this->error_log);
				}
				//$app->enqueueMessage(JText::_('COM_MULTICACHE_ERROR_PAGESCRIPT_LIBRARY_NOTSET'), 'warning');
				// if($this->getParam(0)->js_switch)
				//$app->redirect('index.php?option=com_multicache&view=config&layout=edit&id=1#page-js-tweaks');
				wp_redirect(admin_url().'admin.php?page=multicache-config-menu#page-js-tweaks');
				exit;
			}
			if ($library_count > 1)
			{
				MulticacheHelper::clearAllNotices();
				MulticacheHelper::prepareMessageEnqueue(__('Only one principle library is allowed','multicache-plugin'));
				if($this->debug)
				{
					MulticacheHelper::log_error(__('Only one principle library is allowed','multicache-plugin'),$this->error_log);
				}
				//$app->enqueueMessage(JText::_('COM_MULTICACHE_ERROR_PAGESCRIPT_LIBRARY_ONLYREQUIRESPRIMARYLIBRARY'), 'error');
				// if($this->getParam(0)->js_switch)
				//$app->redirect('index.php?option=com_multicache&view=config&layout=edit&id=1#page-js-tweaks');
				wp_redirect(admin_url().'admin.php?page=multicache-config-menu#page-js-tweaks');
				exit;
			}
			Return true;
		
		}
		
		
		protected function applyDelayRules($page_new_script, $table)
		{
		
			//$app = JFactory::getApplication();
			// principle library cannot be delayed
			// in preceedence mode prior to principl library cannot be delayed
			$preceedence = $table->maintain_preceedence ? $table->maintain_preceedence : null;
			$delay_set = false;
		
			foreach ($page_new_script as $key => $obj)
			{
				if (! empty($obj["delay"]))
				{
					$delay_set = true;
				}
				if ($preceedence && ! empty($obj["library"]) && $delay_set)
				{
		
					// throw error scripts before primary library cannot be delayed in preceedence mode
					MulticacheHelper::prepareMessageEnqueue(__('Precedence Rules: Scripts before library cannot be delayed','multicache-plugin'));
					if($this->debug)
					{
						MulticacheHelper::log_error(__('Precedence Rules: Scripts before library cannot be delayed','multicache-plugin'),$this->error_log);
					}
					//$app->enqueueMessage(JText::_('COM_MULTICACHE_ERROR_PAGESCRIPT_APPLYDELAYRULES_SCRIPTSBEFORELIBRARY_CANNOTDELAY'), 'error');
					Return false;
				}
				elseif (! $preceedence && ! empty($obj["library"]) && ! empty($obj["delay"]))
				{
					MulticacheHelper::prepareMessageEnqueue(__('Principle library cannot be delayed','multicache-plugin'));
					if($this->debug)
					{
						MulticacheHelper::log_error(__('Principle library cannot be delayed','multicache-plugin'),$this->error_log);
					}
					// throw error the priimary library cannot be delayed
					//$app->enqueueMessage(JText::_('COM_MULTICACHE_ERROR_PAGESCRIPT_APPLYDELAYRULES_PRINCIPLELIBRARY_CANNOTDELAY'), 'error');
					Return false;
				}
			}
			Return true;
		
		}
		//version1.0.0.2 ConfirmIgnoreDontLoad
		protected function ConfirmIgnoreDontLoad($jsarray)
		{
		
			foreach ($jsarray as $key => $js)
			{
				$sig = $js["signature"];
				if (isset(self::$_unset_hash[$sig]))
				{
					// were unsetting duplicated scripts that have either been ignored or set to dontload
					// this allows selecting any one script for ignore or dontload
		
					unset($jsarray[$key]); // signature_hash need not be updated as the operation is performed before its setting.
				}
			}
			Return $jsarray;
		
		}
		
		protected function setIgnore($jsarray)
		{
		
			foreach ($jsarray as $key => $js)
			{
				if (! empty($js["ignore"]))
				{
					//version1.0.0.2
					$sig = $js["signature"];
					self::$_unset_hash[$sig] = 1;
					
					unset($jsarray[$key]); // signature_hash need not be updated as the operation is performed before its setting.
				}
			}
			Return $jsarray;
		
		}
		
		
		protected function setDontLoad($jsarray)
		{
			//version1.0.0.2
		//delete this comment after accounting for the following
			$stored_dontmove_items = $this->loadProperty('dontmove');
			
			
			//start block version1.0.0.2
			if (isset($stored_dontmove_items))
			{
				// extracting the url and hash
				foreach ($stored_dontmove_items as $key => $dontmove)
				{
					$sig = $dontmove["signature"];
					$alt_sig = $dontmove["alt_signature"];
					if (isset($sig))
					{
						self::$_dontmovesignature_hash[$sig] = true;
					}
					if (isset($alt_sig))
					{
						self::$_dontmovesignature_hash[$alt_sig] = true;
					}
					$src = $dontmove['src'];
					if (! empty($src))
					{
						$cleaned_src = str_replace(array(
								'https',
								'http',
								'://',
								'//',
								'www.'
						), '', $src);
						self::$_dontmoveurls[$cleaned_src] = 1;
					}
				}
				self::$_dontmove_items = $stored_dontmove_items;
			}
			
			//end block version1.0.0.2
			
			
			foreach ($jsarray as $key => $js)
			{
				//version1.0.0.2 block changes
				/*
				if ($js["loadsection"] >= 5)
				{
					$sig = $js["signature"];
					$alt_sig = $js["alt_signature"];
					if (isset($sig))
					{
						self::$_signature_hash[$sig] = true;
					}
					if (isset($alt_sig))
					{
						self::$_signature_hash[$alt_sig] = true;
					}
					unset($jsarray[$key]);
				}
				*/
				//version1.0.0.2 block begin
				if ($js["loadsection"] == 5)
				{
					$sig = $js["signature"];
					$alt_sig = $js["alt_signature"];
					if (isset($sig))
					{
						self::$_signature_hash[$sig] = true;
					}
					if (isset($alt_sig))
					{
						self::$_signature_hash[$alt_sig] = true;
					}
					self::$_unset_hash[$sig] = 1;
					unset($jsarray[$key]);
				}
				else if ($js["loadsection"] >= 6)
				{
					$sig = $js["signature"];
					$alt_sig = $js["alt_signature"];
					if (isset($sig))
					{
						self::$_dontmovesignature_hash[$sig] = true;
					}
					if (isset($alt_sig))
					{
						self::$_dontmovesignature_hash[$alt_sig] = true;
					}
					$src = $js['src'];
					if (! empty($src))
					{
						$cleaned_src = str_replace(array(
								'https',
								'http',
								'://',
								'//',
								'www.'
						), '', $src);
						self::$_dontmoveurls[$cleaned_src] = 1;
					}
					self::$_dontmove_items[] = $jsarray[$key];
					self::$_unset_hash[$sig] = 1;
					unset($jsarray[$key]);
				}
				//version1.0.0.2 block end
			}
			Return $jsarray;
		
		}
		
		protected function setCDNtosignature($jsarray)
		{
			// get all cdn signatures
			foreach ($jsarray as $key => $js)
			{
				if (! empty($js["cdn_url"]))
				{
					$sig = $js["signature"];
					self::$_cdn_segment[$sig] = $js["cdn_url"];
				}
			}
			foreach ($jsarray as $key => $js)
			{
				$sig = $js["signature"];
				if (isset(self::$_cdn_segment[$sig]) && empty($js["cdn_url"]))
				{
					$jsarray[$key]["cdnalias"] = 1;
					$jsarray[$key]["cdn_url"] = self::$_cdn_segment[$sig];
				}
			}
			Return $jsarray;
		
		}
		
		protected function setSignatureHash($jsarray)
		{
		
			foreach ($jsarray as $key => $js)
			{
				$sig = $js["signature"];
				$alt_sig = $js["alt_signature"];
				if (isset(self::$_signature_hash[$sig]))
				{
					$jsarray[$key]["duplicate"] = true;
				}
				self::$_signature_hash[$sig] = true;
				if (isset($alt_sig) && ! isset(self::$_signature_hash[$alt_sig]))
				{
					self::$_signature_hash[$alt_sig] = true;
				}
			}
			Return $jsarray;
		
		}
		
		
		protected function removeDuplicateScripts($jsarray)
		{
		
			foreach ($jsarray as $key => $js)
			{
		
				if (isset($jsarray[$key]["duplicate"]))
				{
					self::$_duplicates[$key] = $jsarray[$key]; // store duplicates incase its called back
					unset($jsarray[$key]);
				}
			}
		
			Return $jsarray;
		
		}
		
		protected function prepareDelayable($jsarray, $table)
		{
		
			$preceedence = isset($table->maintain_preceedence) ? $table->maintain_preceedence : false;
		
			$delay_lag = false;
			$delayable = null;
			$stored_delayable = $this->loadProperty('delayed');
			foreach ($jsarray as $key => $js)
			{
				// reset the delay lag if new delay type
				if (! empty($jsarray[$key]["delay"]))
				{
					$delay_lag = false;
				}
				if (! empty($js["delay"]) || $delay_lag) // here we use !empty as a direct equivalent of isset && =true
				{
					if (! $delay_lag)
					{
						$delay_type = $jsarray[$key]["delay_type"];
					}
					else
					{
						$jsarray[$key]["delay_type"] = $delay_type; // aligning the outer and inner keys
					}
					$delayable[$delay_type]["items"][$key] = $jsarray[$key];
		
					unset($jsarray[$key]);
		
					$delay_lag = $preceedence ? true : false;
				}
			}
		
			if (isset($delayable) && isset($stored_delayable))
			{
		
				$delayable = array_replace_recursive($stored_delayable, $delayable); // changed from array_merge_recirsive to maintain keys
			}
			elseif (isset($stored_delayable) && ! isset($delayable))
			{
				$delayable = $stored_delayable;
			}
		
			if (! empty($delayable))
			{
				// lets sort this in two parts to ensure sorting
				$mousemove = $delayable['mousemove']['items'];
				$scroll = $delayable['scroll']['items'];
				$onLoad = $delayable['onload']['items'];
				if (isset($mousemove))
				{
					ksort($mousemove);
					$delayable['mousemove']['items'] = $mousemove;
				}
				if (isset($scroll))
				{
					ksort($scroll);
					$delayable['scroll']['items'] = $scroll;
				}
				if (isset($onLoad))
				{
					ksort($onLoad);
					$delayable['onload']['items'] = $onLoad;
				}
		
				self::$_delayable_segment = $delayable;
			}
			Return $jsarray;
		
		}
		
		protected function deferAdvertisement($jsarray)
		{
		
			$advertisement = null;
			$stored_advertisement = $this->loadProperty('advertisements');
			foreach ($jsarray as $key => $js)
			{
				if (! empty($js["advertisement"])) // here we use !empty as a direct equivalent of isset && =true
				{
					$advertisement[$key] = $jsarray[$key];
					unset($jsarray[$key]);
				}
			}
			if (isset($advertisement) && isset($stored_advertisement))
			{
				$advertisement = array_replace($stored_advertisement, $advertisement);
			}
			elseif (isset($stored_advertisement) && ! isset($advertisement))
			{
				$advertisement = $stored_advertisement;
			}
		
			if (! empty($advertisement))
			{
				ksort($advertisement);
				self::$_advertisement_segment = $advertisement;
			}
			Return $jsarray;
		
		}
		
		protected function deferAsync($jsarray)
		{
		
			foreach ($jsarray as $key => $js)
			{
				if (! empty($js["async"])) // here we use !empty as a direct equivalent of isset && =true
				{
					self::$_async_segment[$key] = $jsarray[$key];
					unset($jsarray[$key]);
				}
			}
		
			Return $jsarray;
		
		}
		
		protected function deferSocial($jsarray)
		{
		
			$social = null;
			$stored_social = $this->loadProperty('social');
			foreach ($jsarray as $key => $js)
			{
				if (! empty($js["social"]) && empty($js["delay"])) // here we use !empty as a direct equivalent of isset && =true
				{
					$social[$key] = $jsarray[$key];
					unset($jsarray[$key]);
				}
			}
		
			if (isset($social) && isset($stored_social))
			{
				$social = array_replace($stored_social, $social);
			}
			elseif (isset($stored_social) && ! isset($social))
			{
				$social = $stored_social;
			}
		
			if (! empty($social))
			{
				ksort($social);
				self::$_social_segment = $social;
			}
			Return $jsarray;
		
		}
		
		
		protected function correctSignatureHash($obj)
		{
		
			if (empty($obj))
			{
				Return false;
			}
		
			foreach ($obj as $key => $js)
			{
				$sig = $js["signature"];
				$alt_sig = $js["alt_signature"];
				if (! isset(self::$_signature_hash[$sig]))
				{
					self::$_signature_hash[$sig] = true;
				}
				if (isset($alt_sig) && ! isset(self::$_signature_hash[$alt_sig]))
				{
		
					self::$_signature_hash[$alt_sig] = true;
				}
			}
			Return true;
		
		}
		
		protected function correctDelaySignatureHash($delay_obj)
		{
		
			if (empty($delay_obj))
			{
				Return false;
			}
		
			foreach ($delay_obj as $object)
			{
		
				$this->correctSignatureHash($object["items"]);
			}
		
		}
		
		protected function assignGroups($jsarray)
		{
		
			foreach ($jsarray as $key => $js)
			{
		
				// exclude libraries
				if ($jsarray[$key]["library"])
				{
					$skip_increment_group = true;
					continue;
				}
				// exclude cdns
				if ($jsarray[$key]["cdnalias"])
				{
					$skip_increment_group = true;
					continue;
				}
				// block external scripts from being in groups abs_internal = $jsarray[$key]["internal"] || $jsarray[$key]["code"]
				if (! ($jsarray[$key]["internal"] || $jsarray[$key]["code"]))
				{
					$skip_increment_group = true;
					continue;
				}
		
				// if any one side is internal but not library group but not cdn
				// we need to account for missing keys due to setIgnore and dontLoad operations
				$prev_key = $this->getPreviousKey($key, $jsarray);
				$next_key = $this->getNextKey($key, $jsarray);
				if (((! empty($prev_key) || $prev_key === 0) && isset($jsarray[$prev_key]) && // following rules only if the previous key exists
						! $jsarray[$prev_key]["library"] && // dont group with libraries
						($jsarray[$prev_key]["loadsection"] == $jsarray[$key]["loadsection"]) && // dont group varying loadsections
						($jsarray[$prev_key]["internal"] || $jsarray[$prev_key]["code"]) && // should not be external link
						! $jsarray[$prev_key]["cdnalias"]) || // should not be a cdn cdnalias
						// can be grouped with next key
						((! empty($next_key) || $next_key === 0) && isset($jsarray[$next_key]) && ! $jsarray[$next_key]["library"] && ($jsarray[$next_key]["loadsection"] == $jsarray[$key]["loadsection"]) && ($jsarray[$next_key]["internal"] || $jsarray[$next_key]["code"]) && // checking next internal
								! $jsarray[$next_key]["cdnalias"]))
				{
					// initialise the group counter
					if (! isset($group_counter))
					{
						$group_counter = 0;
					}
					if ($skip_increment_group)
					{
						// increment the group counter
						// reset the flag
						$group_counter ++;
						$skip_increment_group = false;
					}
					$group_code = "group-" . $group_counter;
		
					$jsarray[$key]["group"] = $group_code;
				}
			}
		
			Return $jsarray;
		
		}
		
		
		protected function getPreviousKey($key, $jsarray)
		{
		
			$key --;
			while ($key >= 0)
			{
				if (isset($jsarray[$key]))
				{
					Return $key;
				}
				$key --;
			}
			Return false;
		
		}
		
		protected function getNextKey($key, $jsarray)
		{
		
			if (empty($jsarray))
			{
				Return false;
			}
			$max_key = max(array_keys($jsarray));
			$key ++;
			while ($key <= $max_key)
			{
				if (isset($jsarray[$key]))
				{
					Return $key;
				}
				$key ++;
			}
			Return false;
		
		}
		
		//version1.0.0.3
		protected function combineCssDelayloadUrlToGroup($tbl)
		{
			if (empty(self::$_delayable_segment_css) || empty(self::$_groups)
					||!(isset(self::$_delayable_segment_css["scroll"])
							|| isset(self::$_delayable_segment_css["mousemove"]))
					)
			{
				Return false;
			}
			$index = count(self::$_groups);
			$group = false;
			//check for succesful combinations
			while($index){
				$max_group = "group-" .$index;
				if(self::$_groups[$max_group]["success"] === true)
				{
					$group = $max_group;
					break;
				}
				$index--;
			}
			if(false === $group)
			{
				Return false;
			}
			$combined_code = unserialize(self::$_groups[$group]['combined_code']);
			if(false === $combined_code)
			{
				Return false;
			}
			$delay = null;
			//$delay_async = null;dealing with asynchro css separately as it is not jquery dependent
			 
			$delay = self::$_principle_jquery_scope . "( document ).ready(function() {";
			foreach (self::$_delayable_segment_css as $delay_type_key => $delay_obj)
			{
				if ($delay_type_key == 'async')
				{
					continue;
				}
				if (! empty($delay_obj["delay_executable_code"]))
				{
					$delay .= unserialize($delay_obj["delay_executable_code"]);
				}
			}
			$delay .= "});";
			if ($tbl->compress_js)
			{
				$delay = trim(MulticacheJSOptimize::process($delay));
			}
			$combined_code .= $delay;
			$serialized_combined_code = serialize($combined_code);
			if(false === $serialized_combined_code)
			{
				Return false;
			}
			self::$_groups[$group]['combined_code'] = $serialized_combined_code;
			self::$_delayable_segment_css["scroll"]["resultant_async_defer_loaded"] = true;
			self::$_delayable_segment_css["mousemove"]["resultant_async_defer_loaded"] = true;
			 
		}
		protected function combineDelayloadUrlToGroup($tbl)
		{
			if (empty(self::$_delayable_segment) || empty(self::$_groups))
			{
				Return false;
			}
			$index = count(self::$_groups);
			$group = false;
			//check for succesful combinations
			while($index){
				$max_group = "group-" .$index;
				if(self::$_groups[$max_group]["success"] === true)
				{
					$group = $max_group;
					break;
				}
				$index--;
			}
			if(false === $group)
			{
				Return false;
			}
			$combined_code = unserialize(self::$_groups[$group]['combined_code']);
			if(false === $combined_code)
			{
				Return false;
			}
			$delay = self::$_principle_jquery_scope . "( document ).ready(function() {";
			foreach (self::$_delayable_segment as $delay_type_key => $delay_obj)
			{
				if($delay_type_key == 'onload')
				{
					continue;
				}
				if (! empty($delay_obj["delay_executable_code"]))
				{
					$delay .= unserialize($delay_obj["delay_executable_code"]);
				}
			}
			$delay .= "});";
			if ($tbl->compress_js)
			{
				$delay = trim(MulticacheJSOptimize::process($delay));
			}
			$combined_code .= $delay;
			$serialized_combined_code = serialize($combined_code);
			if(false === $serialized_combined_code)
			{
				Return false;
			}
			self::$_groups[$group]['combined_code'] = $serialized_combined_code;
			self::$_delayable_segment["scroll"]["resultant_async_defer_loaded"] = true;
			self::$_delayable_segment["mousemove"]["resultant_async_defer_loaded"] = true;
		
		}
		protected function initialiseGroupHash($jsarray)
		{
		
			foreach ($jsarray as $key => $value)
			{
				if (isset($jsarray[$key]["group"]))
				{
					$group_number = $jsarray[$key]["group"];
		
					self::$_groups[$group_number]["name"] = $group_number;
					self::$_groups[$group_number]["url"] = null; // this is the raw url
					self::$_groups[$group_number]["callable_url"] = null; // a getScript code with url embed
					self::$_groups[$group_number]["script_tag_url"] = null; // a script taged url
		
					self::$_groups[$group_number]["combined_code"] = null;
					self::$_groups[$group_number]["success"] = null;
		
					self::$_groups[$group_number]["items"][] = $value;
				}
			}
		
		}
		
		protected function combineGroupCode($tbl)
		{
		
			if (empty(self::$_groups))
			{
				Return false;
			}
			foreach (self::$_groups as $group_name => $group)
			{
				self::$_groups[$group_name]["combined_code"] = $this->getCombinedCode($group["items"], $tbl);
				self::$_groups[$group_name]["success"] = ! empty(self::$_groups[$group_name]["combined_code"]) ? true : false;
			}
		
		}
		
		protected function getCombinedCode($grp, $tbl)
		{
		
			if (empty($grp))
			{
				Return false;
			}
			//$app = JFactory::getApplication();
		
			foreach ($grp as $key => $group)
			{
		
				$begin_comment = "/* Inserted by MulticacheReduceRoundtrips source code insert	key-" . $key . "	rank-" . $group["rank"] . "  src-" . substr($group["src"], 0, 10) . " */";
				$end_comment = "/* end MulticacheRoundtrip insert */";
				$begin_comment_code = "/* Inserted by MulticacheReduceRoundtrips  code insert	key-" . $key . "	rank-" . $group["rank"] . "   */";
				$end_comment_code = "/* end MulticacheRoundtrip code insert */";
		
				if ($group["internal"])
				{
					// actual question here is is this source or is this code
					// source the code and place it here
					// ensure it ends with ;
					$url = $group["absolute_src"];
		
					if (isset(self::$_mediaVersion))
					{
						$url_temp = $url;
						$j_uri = MulticacheUri::getInstance($url);
						$j_uri->setVar('mediaFormat', self::$_mediaVersion);
						$url = $j_uri->toString();
					}
					$url = MulticacheHelper::checkCurlable($url);
					$curl_obj = MulticacheHelper::get_web_page($url);
					if ($curl_obj["http_code"] == 200)
					{
						// $code_string .= $begin_comment . MulticacheHelper::clean_code(trim($curl_obj["content"])) . $end_comment;
						if ($tbl->compress_js)
						{
							$ret_content = trim(MulticacheJSOptimize::process($curl_obj["content"]));
						}
						else
						{
							$ret_content = $curl_obj["content"];
						}
						$c_string = ! empty(self::$_jscomments) ? $begin_comment . MulticacheHelper::clean_code(trim($ret_content)) . $end_comment : MulticacheHelper::clean_code(trim($ret_content));
						if(!empty($group['promises']))
						{
							$c_string = MulticacheHelper::clean_code(trim($ret_content));
							$c_string = $this->preparePromise($group , $c_string);
						}
						$code_string .= $c_string;
						
					}
					else
					{
						// register error
		
						
						MulticacheHelper::prepareMessageEnqueue(__('PageScript getCombined curl error','multicache-plugin'). $curl_obj["errmsg"] . " uri- " . $url);
						if($this->debug)
						{
							MulticacheHelper::log_error(__('PageScript getCombined curl error','multicache-plugin'),$this->error_log,$curl_obj);
						}
						//$app->enqueueMessage(JText::_('COM_MULTICACHE_ERROR_PAGESCRIPT_GETCOMBINECODE_CURL_ERROR') . $e_message . '   response-' . $curl_obj["http_code"], 'warning');
						Return false;
					}
				}
				else
				{
					// unserialize and tie code here
					if (! empty($group["serialized_code"]))
					{
						//v ersion1.0.0.2
						// issue with blank space before code
						$unserialized_code = unserialize($group["serialized_code"]);
						$code = ! empty($unserialized_code) ? $unserialized_code : $group["code"];
						// end issue
						if ($tbl->compress_js)
						{
							//$unserialized_code = MulticacheJSOptimize::process(unserialize($group["serialized_code"]));
							//version1.0.0.2
							$unserialized_code = MulticacheJSOptimize::process($code);
						}
						else
						{
							//$unserialized_code = unserialize($group["serialized_code"]);
							//version1.0.0.2
							$unserialized_code = $code; // maintain structure for two versions
						}
						// $code_string .= $begin_comment_code . MulticacheHelper::clean_code(trim(unserialize($group["serialized_code"]))) . $end_comment_code;
						$c_string = ! empty(self::$_jscomments) ? $begin_comment_code . MulticacheHelper::clean_code(trim($unserialized_code)) . $end_comment_code : MulticacheHelper::clean_code(trim($unserialized_code));
						if(!empty($group['promises']))
						{
							$c_string = MulticacheHelper::clean_code(trim($unserialized_code));
							$c_string = $this->preparePromise($group , $c_string);
						}
						$code_string .= $c_string; 
						
					}
					else
					{
						MulticacheHelper::prepareMessageEnqueue(__('PageScript url does not exist, code empty','multicache-plugin'));
						if($this->debug)
						{
							MulticacheHelper::log_error(__('PageScript url does not exist, code empty','multicache-plugin'),$this->error_log);
						}
						// register error
						//$app->enqueueMessage(JText::_('COM_MULTICACHE_ERROR_PAGESCRIPT_GROUP_NOT_INTERNAL_CODE_EMPTY_ROOT_SCRIPT_DETECT_ERROR'), 'warning');
						Return false;
					}
				}
			}
		
			Return serialize($code_string);
		
		}
		
		
		protected function prepareGrouploadableUrl($params = null)
		{
		
			if (! isset(self::$_groups))
			{
				Return false;
			}
		
			foreach (self::$_groups as $key => $grp)
			{
				if ($grp["success"])
				{
		
					self::$_groups[$key]["url"] = MulticacheHelper::getJScodeUrl($key, "raw_url", self::$_principle_jquery_scope, self::$_mediaVersion);
					self::$_groups[$key]["callable_url"] = MulticacheHelper::getJScodeUrl($key, null, self::$_principle_jquery_scope, self::$_mediaVersion);
					self::$_groups[$key]["script_tag_url"] = MulticacheHelper::getJScodeUrl($key, "script_url", self::$_principle_jquery_scope, self::$_mediaVersion ,$params);
				}
			}
		
		}
		
		
		protected function writeGroupCode($tbl)
		{
		
			if (! isset(self::$_groups))
			{
				Return false;
			}
		
			foreach (self::$_groups as $key => $grp)
			{
				if ($grp["success"])
				{
					$file_name = $grp["name"] . ".js";
					if ($tbl->compress_js)
					{
						$unserialized_group_code = MulticacheJSOptimize::process(unserialize($grp["combined_code"]));
					}
					else
					{
						$unserialized_group_code = unserialize($grp["combined_code"]);
					}
		
					$success = MulticacheHelper::writeJsCache($unserialized_group_code, $file_name, $tbl->js_switch);
					self::$_groups[$key]["success"] = ! empty($success) ? true : false;
				}
			}
		
		}
		
		
		protected function makeDelaycode()
		{
			// writes the first level js to be called by the main page
			if (empty(self::$_delayable_segment))
			{
				Return false;
			}
		
			foreach (self::$_delayable_segment as $key => $value)
			{
				if($key == 'onload')
				{
					continue;
				}
				$delay_code = MulticacheHelper::getdelaycode($key, self::$_principle_jquery_scope, self::$_mediaVersion); // initialises the delay code
		
				if (! empty($delay_code))
				{
					self::$_delayable_segment[$key]["delay_executable_code"] = $delay_code["code"];
					self::$_delayable_segment[$key]["delay_callable_url"] = $delay_code["url"];
				}
			}
			//for onload delay
			if(isset(self::$_delayable_segment['onload']))
			{
				$multicache_exec_code = MulticacheHelper::getonLoadexecCode(self::$_delayable_segment['onload']['items']);
				$delay_code = MulticacheHelper::getonLoadDelay($multicache_exec_code);
				if(empty($delay_code))
				{
					self::$_delayable_segment['onload']["delay_executable_code"] = null;
					self::$_delayable_segment['onload']["delay_callable_url"] = null;
					self::$_delayable_segment['onload']["delay_callable_url"] = false;
				}
				self::$_delayable_segment['onload']["delay_executable_code"] = $delay_code;
				self::$_delayable_segment['onload']["delay_callable_url"] = null;
				self::$_delayable_segment['onload']["delay_callable_url"] = true;
			}
		
		}
		
		protected function segregatePlaceDelay($tbl)
		{
		
			if (empty(self::$_delayable_segment))
			{
				Return false;
			}
		
			//$app = JFactory::getApplication();
			//
			foreach (self::$_delayable_segment as $key_delaytype => $delay_seg)
			{
				if($key_delaytype == 'onload')
				{
					continue;
				}
				$success = $this->placeDelayedCode($delay_seg, $tbl);
				if ($success)
				{
					self::$_delayable_segment[$key_delaytype]["success"] = true;
				}
				else
				{
					self::$_delayable_segment[$key_delaytype]["success"] = false;
					MulticacheHelper::clearAllNotices();
					MulticacheHelper::prepareMessageEnqueue(__('PageScripts place delay failed','multicache-plugin'));
					if($this->debug)
					{
						MulticacheHelper::log_error(__('PageScripts place delay failed','multicache-plugin'),$this->error_log);
					}
					
					wp_redirect(admin_url().'admin.php?page=multicache-config-menu#page-js-tweaks');
					//$app->enqueueMessage(JText::_('COM_MULTICACHE_CLASS_MULTICACHE_PAGE_SCRIPTS_PLACE_DELAY_FAILED') . $key_delaytype, 'error');
					//$app->redirect('index.php?option=com_multicache&view=config&layout=edit&id=1#page-js-tweaks');
					exit;
				}
			}
			//compress onload code if required
		if($tbl->compress_js && isset(self::$_delayable_segment['onload']["delay_executable_code"]))
		{
			$onload_code = unserialize(self::$_delayable_segment['onload']["delay_executable_code"]);
			if(!$onload_code)
			{
				Return;
			}
			$onload_code = MulticacheJSOptimize::process($onload_code) ;
			$onload_code = serialize($onload_code);
			self::$_delayable_segment['onload']["delay_executable_code"] = $onload_code;
		}
		}
		
		protected function prepareLoadSections($jsarray, $tbl , $params)
		{
		
			self::$_loadsections[1] = $this->getLoadSection(1, $jsarray, $tbl, $params);
			self::$_loadsections[2] = $this->getLoadSection(2, $jsarray, $tbl, $params);
			self::$_loadsections[3] = $this->getLoadSection(3, $jsarray, $tbl, $params);
			self::$_loadsections[4] = $this->getLoadSection(4, $jsarray, $tbl, $params);
		
		}
		
		protected function getLoadSection($section, $jsarray_obj, $tbl , $params = null)
		{
		
			//$app = JFactory::getApplication();
		
			foreach ($jsarray_obj as $obj)
			{
		
				if ($obj["loadsection"] != $section)
				{
					continue;
				}
		
				if (isset($obj["group"]) && (bool) ($group_name = $obj["group"]) == true && isset(self::$_groups[$group_name]["success"]) && self::$_groups[$group_name]["success"] == true)
				{
		
					if (! isset(self::$_groups_loaded[$group_name]))
					{
		
						$load_string .= unserialize(self::$_groups[$group_name]["script_tag_url"]); //
		
						/*
						 * OTHER OPTIONS
						 *
						 * $load_string .= MulticacheHelper::getloadableSourceScript(self::$_groups[$group_name]["url"] , false);
						 *
						 * $load_string .= MulticacheHelper::getloadableSourceScript(unserialize( self::$_groups[$group_name]["callable_url"]) , false);
						*/
						self::$_groups_loaded[$group_name] = true;
					}
		
					continue;
				}
		
				$sig = $obj["signature"];
				if (isset(self::$_cdn_segment[$sig]) && (bool) self::$_cdn_segment[$sig] == true)
				{
					if(!empty($obj['promises']))
					{
						$alias_grp = $obj;
						$alias_grp['src'] = self::$_cdn_segment[$sig];
						
						$c_string = $this->preparePromise($alias_grp , '', false ,true );
						$load_string .= MulticacheHelper::getloadableCodeScript($c_string , $obj["async"],true,  $params);
						
					}
					else
					{
					$load_string .= MulticacheHelper::getloadableSourceScript(self::$_cdn_segment[$sig], $obj["async"], $params);
					}
				}
				// if src else code
				elseif (! empty($obj["src"]))
				{
		
					// if obj int use only absolute
					if ($obj["internal"])
					{
						if(!empty($obj['promises']))
						{
							$alias_grp = $obj;
							$alias_grp['src'] = $obj["absolute_src"];
							$c_string = $this->preparePromise($alias_grp , '', false ,true );
							$load_string .= MulticacheHelper::getloadableCodeScript($c_string , $obj["async"],true,  $params);
							
						}
						else{
		
						$load_string .= MulticacheHelper::getloadableSourceScript($obj["absolute_src"], $obj["async"], $params);
						}
					}
					elseif (! $obj["internal"])
					{
						// redundancy delared on purpose to maintain elseif formats
						// external source
						if(!empty($obj['promises']))
						{
						$alias_grp = $obj;
						$c_string = $this->preparePromise($alias_grp , '', false ,true );
						$load_string .= MulticacheHelper::getloadableCodeScript($c_string , $obj["async"],true,  $params);
						}
						else{
						
						$load_string .= MulticacheHelper::getloadableSourceScript($obj["src"], $obj["async"], $params);
						}
					}
					/*
					 * MORE ELSEIF CAN COME HERE TO ENTERTAIN ALIAS LOADING ETC.
					 */
				}
				elseif (! empty($obj["code"]))
				{
					//version1.0.0.2 issues with serialization
					$unserialized_code = unserialize($obj["serialized_code"]);
					$code = ! empty($unserialized_code) ? $unserialized_code : $obj["code"];
					if(!empty($obj['promises']))
					{
						$alias_grp = $obj;
						$c_string = $this->preparePromise($alias_grp , $code, false  );
						$code = MulticacheHelper::getloadableCodeScript($c_string , $obj["async"],true,  $params);
					}
					
					if ($tbl->compress_js)
					{
						//$load_string .= MulticacheHelper::getloadableCodeScript(MulticacheJSOptimize::process(unserialize($obj["serialized_code"])), $obj["async"], true, $params);
						  $load_string .= MulticacheHelper::getloadableCodeScript(MulticacheJSOptimize::process($code) , $obj["async"], true, $params);
					}
					else
					{
						//$load_string .= MulticacheHelper::getloadableCodeScript($obj["serialized_code"], $obj["async"]);
						  $load_string .= MulticacheHelper::getloadableCodeScript($code  , $obj["async"], true, $params);
					}
				}
				else
				{
					MulticacheHelper::prepareMessageEnqueue(__('PageScripts loadsection undefined','multicache-plugin'));
					if($this->debug)
					{
						MulticacheHelper::log_error(__('PageScripts loadsection undefined','multicache-plugin'),$this->error_log);
					}
					//$app->enqueueMessage(JText::_('COM_MULTICACHE_CLASS_MULTICACHE_PAGE_SCRIPTS_LOADSECTION_UNDEFINED_SCRIPT_TYPE_ERROR'), 'error');
				}
			}
			if (empty($load_string))
			{
		
				Return false;
			}
			Return serialize($load_string);
		
		}
		
		
		protected function combineSectionFooter($object)
		{
		
			if (empty($object))
			{
				Return false;
			}
			$loadsections = self::$_loadsections;
			$footer_segment = $loadsections[4];
			/* NOTe This is arbitrary- for more sophistication we can point to end and then take the key.end($loadsections);$key = key($loadsections); */
			// get the load string
			if (! empty($footer_segment))
			{
				$load_string = unserialize($footer_segment);
			}
		
			foreach ($object as $obj)
			{
				/* NOTE: We have not provided for Advertisements/SOCIAL to be loaded for CDN. We need to set exceptioon handlers for ads & social that are marked to cdn */
				if (! empty($obj["src"]))
				{
					if ($obj["internal"])
					{
						$load_string .= MulticacheHelper::getloadableSourceScript($obj["absolute_src"], $obj["async"]);
					}
					elseif (! $obj["internal"])
					{
						$load_string .= MulticacheHelper::getloadableSourceScript($obj["src"], $obj["async"]);
					}
				}
				else
				{
		
					$load_string .= MulticacheHelper::getloadableCodeScript($obj["serialized_code"], $obj["async"]);
				}
			}
			if (empty($load_string))
			{
				Return false;
			}
			self::$_loadsections[4] = serialize($load_string);
			Return true;
		
		}
		
		protected function combineMAU()
		{
			if(!isset(self::$_promises) || empty(self::$_loadsections) || true !== self::$_promises['load_mau'])
			{
				Return false;
			}
			$loadsections = self::$_loadsections;
			
			if(is_array($loadsections))
			{
				$loadsections = array_filter($loadsections);
			}
			$all_keys = array_keys($loadsections);
			$mau_section = isset($all_keys) && is_array($all_keys) ? min($all_keys) : 2;
			$mau_string = MulticacheHelper::getMAU();
			$mau_string = MulticacheJSOptimize::process($mau_string);
			$mau_string = MulticacheHelper::getloadableCodeScript($mau_string , false ,true);
			$l_section = $loadsections[$mau_section];
			
			$l_section_unser = unserialize($l_section);
			if(!$l_section_unser)
			{
				Return false;
			}
			$l_section_unser = $mau_string . $l_section_unser;
			$l_l_sec = serialize($l_section_unser);
			if(!$l_l_sec)
			{
				Return false;
			}
			//try another unserialize
			$test_lsec = unserialize($l_l_sec);
			if(!$test_lsec)
			{
				Return false;
			}
			
			self::$_loadsections[$mau_section] = $l_l_sec;
			Return true;
		}
		protected function combineDelay($params)
		{
		
			if (empty(self::$_delayable_segment))
			{
				Return false;
			}
			if(isset(self::$_delayable_segment["scroll"]["resultant_async_defer_loaded"])
					&& !isset(self::$_delayable_segment["onload"]))
					{
				Return false;
			         }
			         
			$delay = '';
		    if(!isset(self::$_delayable_segment["scroll"]["resultant_async_defer_loaded"])
		    		&& (!empty(self::$_delayable_segment["scroll"]) ||(!empty(self::$_delayable_segment["mousemove"]))))
		    {
			$delay = self::$_principle_jquery_scope . "( document ).ready(function() {";
			foreach (self::$_delayable_segment as $delay_type_key => $delay_obj)
			{
				if($delay_type_key == 'onload' )
				{
					continue;
				}
				if (! empty($delay_obj["delay_executable_code"]))
				{
					$delay .= unserialize($delay_obj["delay_executable_code"]);
				}
			}
			$delay .= "});";
		    }
		    if(!empty(self::$_delayable_segment['onload']))
		    {
		    foreach (self::$_delayable_segment as $delay_type_key => $delay_obj)
		    {
		    	if($delay_type_key == 'scroll' || $delay_type_key == 'mousemove')
		    	{
		    		continue;
		    	}
		    	if (! empty($delay_obj["delay_executable_code"]))
		    	{
		    		$delay .= unserialize($delay_obj["delay_executable_code"]);
		    	}
		    }
		    }
		    if(empty($delay))
		    {
		    	Return false;
		    }
			//wrap delay abandoned
			//$delay = MulticacheHelper::wrapDelay( $delay , self::$_principle_jquery_scope );
			$delay = serialize($delay); // just to make it compatible with earlier processes
			// $ds = MulticacheHelper::getloadableCodeScript( $delay ,false );
			$loadsections = self::$_loadsections;
			$footer_segment = $loadsections[4];
			if (! empty($footer_segment))
			{
				$load_string = unserialize($footer_segment);
			}
		
			$load_string .= MulticacheHelper::getloadableCodeScript($delay, false , null , $params);
			if (empty($load_string))
			{
				Return false;
			}
			self::$_loadsections[4] = serialize($load_string);
			Return true;
		
		}
		
		protected function combineCssDelayToScript($params_extended = null)
		{
		
			if (empty(self::$_delayable_segment_css) && empty($params_extended))
			{
				Return false;
			}
			$delay = null;
			$delay_async = null;
			$delay_async_head = null;
			$delay_async_foot = null;
			$load_string = "";
			$n_script = '';
			//ver1.0.0.3 ammend
			/*if (isset(self::$_delayable_segment_css["scroll"]) || isset(self::$_delayable_segment_css["mousemove"]))*/
			if (    (
					isset(self::$_delayable_segment_css["scroll"])
					|| isset(self::$_delayable_segment_css["mousemove"])
					)
					&&!isset(self::$_delayable_segment_css["scroll"]["resultant_async_defer_loaded"])
					)
			{
				$delay = self::$_principle_jquery_scope . "( document ).ready(function() {";
				foreach (self::$_delayable_segment_css as $delay_type_key => $delay_obj)
				{
					if ($delay_type_key == 'async')
					{
						continue;
					}
					if (! empty($delay_obj["delay_executable_code"]))
					{
						$delay .= unserialize($delay_obj["delay_executable_code"]);
					}
				}
				$delay .= "});";
				// we need to code in the async delay call
				
				// $ds = MulticacheHelper::getloadableCodeScript( $delay ,false );
			}
		
			
			if (isset(self::$_delayable_segment_css["async"]))
			{
				// lets get the src bits
				$async_src_bits = MulticacheHelper::getAsyncSrcbits(self::$_delayable_segment_css["async"]);
				$delay_async_head = unserialize(self::$_delayable_segment_css["async"]["delay_executable_code"]);
				$delay_async_head = MulticacheJSOptimize::process($delay_async_head);
				$delay_async_head = MulticacheHelper::getloadableCodeScript($delay_async_head, true, true);
				$delay_async_foot = MulticacheJSOptimize::process($async_src_bits);
				$delay_async_foot = MulticacheHelper::getloadableCodeScript($delay_async_foot, true, true);
			}
			if(!empty($params_extended['css_groupsasync']))
			{
				
				$group_async_src_bits = MulticacheHelper::getGroupAsyncSrcbits(self::$_groups_css);
			
				if(!isset($delay_async_head))
				{
					$group_async_head = MulticacheHelper::getCssdelaycode('async' , null);
			
					$group_async_head = unserialize($group_async_head['code']);
					$group_async_head = MulticacheJSOptimize::process($group_async_head);
					$group_async_head = !empty($group_async_head) ? MulticacheHelper::getloadableCodeScript($group_async_head, true, true) : null;
			
				}
				$group_async_foot = $group_async_src_bits['inline_code'];
				$group_async_foot = MulticacheJSOptimize::process($group_async_foot);
				$group_async_foot_noscript = $group_async_src_bits['noscript'];
				$group_async_foot = !empty($group_async_foot) ? MulticacheHelper::getloadableCodeScript($group_async_foot, true, true) : null;
			
				//
			}
				
			$loadsections = self::$_loadsections; // were combining the script to jstweaks script here
			if (isset($delay_async_head) 
					||(isset($params_extended['css_groupsasync']) 
					&& !empty($group_async_head)))
			{
				$load_string_head = "";
				$head_segment = $loadsections[2];
				if (! empty($head_segment))
				{
					$load_string_head = unserialize($head_segment);
				}
				if (isset($delay_async_head))
				{
					$load_string_head .= $delay_async_head;
					self::$_loadsections[2] = serialize($load_string_head);
				}
				elseif(isset($params_extended['css_groupsasync'])
						&& !empty($group_async_head))
				{
					$load_string_head .= $group_async_head;
				
					self::$_loadsections[2] = serialize($load_string_head);
				
				}
			}
			$footer_segment = $loadsections[4];
			// we can choose this point to load asyn delay type in head
			
			if (! empty($footer_segment))
			{
				$load_string = unserialize($footer_segment);
			}
			// we'll need to maintain get loadable code script here, as the css delays are executed through javascript
		
			if (isset($delay))
			{
				$delay = MulticacheJSOptimize::process($delay);
				$load_string .= MulticacheHelper::getloadableCodeScript($delay, false , true);
			}
			if (isset($delay_async_foot))
			{
				$load_string .= $delay_async_foot;
			}
			if (!empty($group_async_foot))
			{
				$load_string .= $group_async_foot;
			}
			if (! empty(self::$_delayed_noscript))
			{
				$n_script .= self::$_delayed_noscript;
			}
			
			if(!empty($group_async_foot_noscript))
			{
				$n_script .= $group_async_foot_noscript;
			
			}
			if(!empty($n_script))
			{
				$n_script = MulticacheCSSOptimize::optimize($n_script);
				$n_script = MulticacheHelper::noscriptWrap($n_script , true);
			
				$load_string .= $n_script;
			}
			
		
			if (empty($load_string))
			{
				Return false;
			}
			self::$_loadsections[4] = serialize($load_string);
			Return true;
		
		}
		
		
		////End JS Methods
		
		//Begin Simulation Methods
		
		protected function prepareSimulationControl($SIMOBJ, $LOCK_FLAG)
		{
		
			if (empty($LOCK_FLAG))
			{
				Return; // not empty is a lock
			}
			//$app = JFactory::getApplication();
		
			//$comp = JModelLegacy::getInstance('Simcontrol', 'MulticacheModel');
			$comp = new MulticacheSimcontrol();
		
			$prepared = $comp->getSimcontrol($SIMOBJ);
			if (! $prepared)
			{
				MulticacheHelper::prepareMessageEnqueue(__('Prepare Simulation Control failed'),'error');
				if($this->debug)
				{
				MulticacheHelper::log_error(__('Prepare Simulation Control failed'),$this->error_log);
				}
				//$app = JFactory::getApplication();
				//$app->enqueueMessage(JText::_('COM_MULTICACHE_PREPARE_SIMULATION_CONTROL_FAILED'), 'error');
			}
		
		}
		
//last closes class		
}