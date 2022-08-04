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
if(file_exists(plugin_dir_path(__FILE__).'libs/multicache_loadinstruction.php'))
{
require_once plugin_dir_path(__FILE__).'libs/multicache_loadinstruction.php';
}
*/
/*
JLoader::register('Loadinstruction', JPATH_COMPONENT . '/lib/loadinstruction.php');
JLoader::register('JsStrategySimControl', JPATH_ROOT . '/administrator/components/com_multicache/lib/jscachestrategy_simcontrol.php');
JLog::addLogger(array(
		'text_file' => 'errors.php'
), JLog::ALL, array(
		'error'
));
*/
class MulticacheHelperSimcontrol
{
	protected static $config;
	
	protected $data;
	
	public function __construct($data = null)
	{
	
		$this->data = new stdClass();
	
		if (is_array($data) || is_object($data))
		{
			$this->bindData($this->data, $data);
		}
		elseif (! empty($data) && is_string($data))
		{
			$this->loadString($data);
		}
	
	}
	public static function writeLoadInstructions($preset, $loadinstruction, $working_instruction, $original_instruction, $pagescripts , $mul_strat = false)
	{
	
		if (! isset($pagescripts['working_script_array']))
		{
			Return false;
		}
		if (empty($preset))
		{
			if (! class_exists('MulticacheLoadinstruction'))
			{
				Return false;
			}
			if (property_exists('MulticacheLoadinstruction', 'preset'))
			{
				$preset = MulticacheLoadinstruction::$preset;
			}
		}
	
		if (empty($loadinstruction))
		{
			if (! class_exists('MulticacheLoadinstruction'))
			{
				Return false;
			}
			if (property_exists('MulticacheLoadinstruction', 'loadinstruction'))
			{
				$loadinstruction = MulticacheLoadinstruction::$loadinstruction;
			}
		}
	
		if (empty($working_instruction))
		{
			if (! class_exists('MulticacheLoadinstruction'))
			{
				Return false;
			}
			if (property_exists('MulticacheLoadinstruction', 'working_instruction'))
			{
				$working_instruction = MulticacheLoadinstruction::$working_instruction;
			}
		}
	
		if (empty($original_instruction))
		{
			if (! class_exists('MulticacheLoadinstruction'))
			{
				Return false;
			}
			if (property_exists('MulticacheLoadinstruction', 'original_instruction'))
			{
				$original_instruction = MulticacheLoadinstruction::$original_instruction;
			}
		}
	
		$preset = var_export($preset, true);
		$loadinstruction = var_export($loadinstruction, true);
		$working_instruction = var_export($working_instruction, true);
		$original_instruction = var_export($original_instruction, true);
		$working_script_array = var_export($pagescripts['working_script_array'], true);
		$social = ! empty($pagescripts['social']) ? var_export($pagescripts['social'], true) : null;
		$advertisements = ! empty($pagescripts['advertisements']) ? var_export($pagescripts['advertisements'], true) : null;
		$async = ! empty($pagescripts['async']) ? var_export($pagescripts['async'], true) : null;
		$delayed = ! empty($pagescripts['delayed']) ? var_export($pagescripts['delayed'], true) : null;
	
		ob_start();
		echo "<?php

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
	
	

class MulticacheLoadinstruction{
	
";
		$cl_buf = ob_get_clean();
		if (! empty($preset))
		{
			ob_start();
			echo "
	
public static \$preset  = " . trim($preset) . ";
	
";
			$cl_buf .= ob_get_clean();
		}
	
		if (! empty($loadinstruction))
		{
	
			ob_start();
			echo "
	
	
public static \$loadinstruction  = " . trim($loadinstruction) . ";
	
";
			$cl_buf .= ob_get_clean();
		}
	
		if (! empty($working_instruction))
		{
	
			ob_start();
			echo "
	
	
public static \$working_instruction  = " . trim($working_instruction) . ";
	
";
			$cl_buf .= ob_get_clean();
		}
	
		if (! empty($original_instruction))
		{
	
			ob_start();
			echo "
	
	
public static \$original_instruction  = " . trim($original_instruction) . ";
	
";
			$cl_buf .= ob_get_clean();
		}
	
		// start
		if (! empty($working_script_array))
		{
	
			ob_start();
			echo "
	
	
public static \$working_script_array  = " . $working_script_array . ";
	
";
			$cl_buf .= ob_get_clean();
		}
	
		if (! empty($social))
		{
	
			ob_start();
			echo "
	
	
public static \$social  = " . $social . ";
	
";
			$cl_buf .= ob_get_clean();
		}
	
		if (! empty($advertisements))
		{
	
			ob_start();
			echo "
	
	
public static \$advertisements  = " . $advertisements . ";
	
";
			$cl_buf .= ob_get_clean();
		}
	
		if (! empty($async))
		{
	
			ob_start();
			echo "
	
	
public static \$async  = " . $async . ";
	
";
			$cl_buf .= ob_get_clean();
		}
	
		if (! empty($delayed))
		{
	
			ob_start();
			echo "
	
	
public static \$delayed  = " . $delayed . ";
	
";
			$cl_buf .= ob_get_clean();
		}
		// end
	
		ob_start();
		echo "
        }
        ";
		$cl_buf .= ob_get_clean();
	
		$cl_buf = serialize($cl_buf);
	
		//$dir = JPATH_ROOT . '/components/com_multicache/lib';
		if(is_multisite())
		{
			$dir = $mul_strat['strategy']['simulation_lib_loc'];
			$dir = rtrim( $dir, '/\\' );
		}
		else {
		$dir = plugin_dir_path(__FILE__).'libs/';
		}
		$filename = 'multicache_loadinstruction.php';
		$success = self::writefileTolocation($dir, $filename, $cl_buf);
		if($success)
		{
			//testing refer to issue#1 on simcontrol.php
			$options_system_params = get_option('multicache_system_params');
			$options_system_params['new_load_instruction'] = true;
			update_option('multicache_system_params', $options_system_params);
		}
		Return $success;
		// $success = self::writeLoadinstructionset(serialize($cl_buf));
	}
	
	public static function setJsSimulation($sim = 1, $advanced = 'normal', $load_state = null)
	{
	
		$advanced = ($advanced == 'advanced') ? 1 : NULL;
		$options_system_params = get_option('multicache_system_params');
		$options_system_params['js_simulation'] = $sim;
		$options_system_params['js_advanced'] = $advanced;
		if (isset($load_state))
		{
			if ($load_state == 0)
			{
				//$params->set('js_loadinstruction', null);
				$options_system_params['js_loadinstruction'] = null;
			}
			else
			{
				//$params->set('js_loadinstruction', $load_state);
				$options_system_params['js_loadinstruction'] = $load_state;
			}
		}
		$result  = update_option('multicache_system_params', $options_system_params);
		
		Return $result;
	
		/*$app = JFactory::getApplication();
		$plugin = JPluginHelper::getPlugin('system', 'multicache');
		$extensionTable = JTable::getInstance('extension');
		$pluginId = $extensionTable->find(array(
				'element' => 'multicache',
				'folder' => 'system'
		));
		$pluginRow = $extensionTable->load($pluginId);
		$params = new JRegistry($plugin->params);
		$params->set('js_simulation', $sim);
		$params->set('js_advanced', $advanced);
		if (isset($load_state))
		{
			if ($load_state == 0)
			{
				$params->set('js_loadinstruction', null);
			}
			else
			{
				$params->set('js_loadinstruction', $load_state);
			}
		}
		$extensionTable->bind(array(
				'params' => $params->toString()
		));
		if (! $extensionTable->check())
		{
			$app->setError('lastcreatedate: check: ' . $extensionTable->getError());
			return false;
		}
		if (! $extensionTable->store())
		{
			$app->setError('lastcreatedate: store: ' . $extensionTable->getError());
			return false;
		}
		*/
	
	}
	public static function lockSimControl($lock = 0)
	{
		$options_system_params = get_option('multicache_system_params');
		
		if (! empty($lock))
		{
			$options_system_params['lock_sim_control'] = true;
			//$params->set('lock_sim_control', TRUE);
		}
		else
		{
			//$params->set('lock_sim_control', false);
			$options_system_params['lock_sim_control'] = false;
		}
		$result = update_option('multicache_system_params', $options_system_params);
		if($lock)
		{
			//WP returns false if there is no change hence we need to test 
			//whether the lock flag is set or not and return the same
			//no ammends yet for lock=0
			$options_system_params = get_option('multicache_system_params');
			$result = $options_system_params['lock_sim_control'];
		}
	/*
		$app = JFactory::getApplication();
		$plugin = JPluginHelper::getPlugin('system', 'multicache');
		$extensionTable = JTable::getInstance('extension');
		$pluginId = $extensionTable->find(array(
				'element' => 'multicache',
				'folder' => 'system'
		));
		$pluginRow = $extensionTable->load($pluginId);
		$params = new JRegistry($plugin->params);
		if (! empty($lock))
		{
			$params->set('lock_sim_control', TRUE);
		}
		else
		{
			$params->set('lock_sim_control', false);
		}
		$extensionTable->bind(array(
				'params' => $params->toString()
		));
		if (! $extensionTable->check())
		{
			$app->setError('lastcreatedate: check: ' . $extensionTable->getError());
			return false;
		}
		if (! $extensionTable->store())
		{
			$app->setError('lastcreatedate: store: ' . $extensionTable->getError());
			return false;
		}
		*/
		Return $result;
	
	}
	
	protected static function writefileTolocation($dir, $filename, $contents)
	{
	
		//$app = JFactory::getApplication();
		//jimport('joomla.filesystem.path');
		//jimport('joomla.filesystem.file');
	
		$file = $dir . '/' . $filename;
		//$ftp = JClientHelper::getCredentials('ftp', true);
	
		// Attempt to make the file writeable if using FTP.
		/*
		 if (! $ftp['enabled'] && file_exists($file) && JPath::isOwner($file) && ! JPath::setPermissions($file, '0644'))
		 {
		 $app->enqueueMessage(JText::_('COM_MULTICACHE_HELPER_ERROR_WRITEFILETOLOCATION_FILELOC_NOTWRITABLE'), 'warning');
		 $emessage = "COM_MULTICACHE_HELPER_ERROR_WRITEFILETOLOCATION_FILELOC_NOTWRITABLE";
		 JLog::add(JText::_($emessage) . '	' . $file, JLog::WARNING);
		 }
		 */
		$class_path = unserialize($contents);
		$class_path = str_ireplace("\x0D", "", $class_path);
	
		//start wp write
		require_once (ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
		require_once (ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');
		$multicache_fsd = new WP_Filesystem_Direct(__FILE__);
		@set_time_limit(300);
		$check_is_writable = array();
		$a = $multicache_fsd->chmod($file, 0644);
	
		if (! $multicache_fsd->put_contents($file, $class_path, 0644))
		{
			$result = new WP_Error('failed to write '.$file, __('Multicacheconfig could prepare core classes.'), $file);
			return $result;
		}
		Return true;
		//end wp write
		/*
		 if (! JFile::write($file, $class_path))
		 {
		 throw new RuntimeException(JText::_('COM_MULTICACHE_ERROR_WRITE_FILETOLOCATION_FAILED') . '	' . $file);
		 $emessage = "COM_MULTICACHE_ERROR_WRITE_FILETOLOCATION_FAILED";
		 JLog::add(JText::_($emessage) . '	' . $file, JLog::ERROR);
		 }
	
		 // Attempt to make the file unwriteable if using FTP.
		 if (! $ftp['enabled'] && JPath::isOwner($file) && ! JPath::setPermissions($file, '0444'))
		 {
		 $app->enqueueMessage(JText::_('COM_MULTICACHE_WRITEFILETOLOCATION_ERROR_NOTUNWRITABLE') . '	' . $file, 'warning');
		 $emessage = "COM_MULTICACHE_WRITEFILETOLOCATION_ERROR_NOTUNWRITABLE";
		 JLog::add(JText::_($emessage) . '	' . $file, JLog::WARNING);
		 }
	
		 return true;
		 */
	
	}
	
	public static function transitSimulation($status , $id , $table = 'multicache_advanced_testgroups' )
	{
		if(empty($status) || empty($id))
		{
			Return false;
		}
		global $wpdb;
		$tablename = $wpdb->prefix.$table;
		$data = array('status' => $status);
		$where = array('id' => $id);
		$format = array('%s');
		$w_format = array('%d');
		$result = $wpdb->update($tablename , $data , $where , $format , $w_format);
		Return $result;
	}
	
	public static function transitSimulationMT($status  , $id , $mtime =0, $table = 'multicache_advanced_test_results' )
	{
		if(empty($status) || empty($id) )
		{
			Return false;
		}
		global $wpdb;
		$tablename = $wpdb->prefix.$table;
		$mtime = empty($mtime)? microtime(true):$mtime;				
		$data = array('mtime' => $mtime , 'status' => $status );
		$where = array('id' => $id );
		$format = array('%s' , '%s');
		$w_format = array('%d');
		$result = $wpdb->update($tablename , $data , $where ,$format, $w_format);
		Return $result;
	}
	
	public static function recordTest($data  ,$where , $format,$w_format , $table = 'multicache_advanced_test_results' )
	{
		if(empty($data) || empty($where) )
		{
			Return false;
		}
		global $wpdb;
		$tablename = $wpdb->prefix.$table;
		
		
		$result = $wpdb->update($tablename , $data , $where ,$format, $w_format);
		Return $result;
	}
	
	public static function recordResults($update , $where, $format , $w_format , $table = 'multicache_advanced_test_results')
	{
		If(empty($update))
		{
		Return false;	
		}
		$data = array();
		foreach($update As $key => $value)
		{
			$data[$key] = $value;
		}
		global $wpdb;
		$tablename = $wpdb->prefix.$table;
		$result = $wpdb->update($tablename , $data , $where , $format , $w_format);
		 
	}
	
	public static function startTest($insertObj , $table = 'multicache_advanced_test_results')
	{
		if(empty($insertObj))
		{
			Return false;
		}
		global $wpdb;
		$tablename = $wpdb->prefix.$table;
		$data = array();
		$format = array();
		foreach($insertObj As $key => $obj)
		{
			$data[$key] = $obj;
			switch($key)
			{
				case 'date_of_test':
				case 'mtime':
				case 'test_page':
				case 'status':
				case 'simulation':
				case 'test_date':
				case 'cache_compression_factor':
					$format[] = '%s';
					break;
					
				case 'max_tests':
				case 'current_test':
				case 'precache_factor':
				
				$format[] = '%d';
					break;
				default:
				MulticacheHelper::log_error('Undefined case for startTest function '.$key,'serious-error',$insertObj);
				
			}
		}
		//check matches data to format
		$result = $wpdb->insert($tablename , $data , $format);
	}
	
	public static function recordFactor($insertObj , $table = 'multicache_advanced_precache_factor')
	{
		if(empty($insertObj))
		{
			Return false;
		}
		global $wpdb;
		$tablename = $wpdb->prefix.$table;
		$data = array();
		$format = array();
		foreach($insertObj As $key => $obj)
		{
			$data[$key] = $obj;
			switch($key)
			{
				case 'date_of_test':
				case 'mtime':
				case 'test_page':
				case 'status':
				case 'simulation':
				case 'test_date':
				case 'loadinstruc_state':
				case 'cache_compression_factor':
				case 'avg_load_time':
				case 'var_load_time':
				case 'loadtime_score':
				case 'loadvar_score':
				case 'statmode':
				case 'statmode_score':
				case 'total_score':
				case 'ccomp_factor':
					
					$format[] = '%s';
					break;
						
				case 'max_tests':
				case 'current_test':
				case 'precache_factor':
				
				case 'group_id':
				
					$format[] = '%d';
					break;
				default:
					MulticacheHelper::log_error('Undefined case for startTest function '.$key,'serious-error',$insertObj);
	
			}
		}
		//check matches data to format
		$result = $wpdb->insert($tablename , $data , $format);
	}
	
	
	public static function writeJsCacheStrategyMain($signature_hash, $loadsection, $switch, $load_state, $stubs = null, $JSTexclude = null , $ms_obj = false)
	{
		$comparitive_object = self::getComparitiveStrategy($ms_obj);
		if(is_multisite())
		{
			$class_name = $ms_obj['strategy']['jscachestrategy_namespace'];
		}
		else {
			$class_name = 'JsStrategy';
		}
		if (empty($signature_hash) || empty($loadsection) || ! isset($switch) || ! isset($load_state))
		{
			Return false;
		}
	
		$signature_hash = preg_replace('/\s/', '', var_export($signature_hash, true));
		$signature_hash = str_replace(',)', ')', $signature_hash);
		$loadsection = var_export($loadsection, true);
		$load_state = isset($load_state) ? var_export($load_state, true) : null;
		$stubs = var_export($stubs, true);
		if (! empty($JSTexclude->url))
		{
			$JSTurl = preg_replace('/\s/', '', var_export($JSTexclude->url, true));
			$JSTurl = str_replace(',)', ')', $JSTurl);
		}
		if (! empty($JSTexclude->query))
		{
			$JSTquery = preg_replace('/\s/', '', var_export($JSTexclude->query, true));
			$JSTquery = str_replace(',)', ')', $JSTquery);
		}
		if (! empty($JSTexclude->settings))
		{
			$JSTsettings = var_export($JSTexclude->settings, true);
		}
		if (! empty($JSTexclude->component))
		{
			$JSTcomponents = preg_replace('/\s/', '', var_export($JSTexclude->component, true));
			$JSTcomponents = str_replace(',)', ')', $JSTcomponents);
		}
		if (! empty($JSTexclude->url_strings))
		{
			$JSTurlstrings = preg_replace('/\s/', '', var_export($JSTexclude->url_strings, true));
			$JSTurlstrings = str_replace(',)', ')', $JSTurlstrings);
		}
	
		ob_start();
		echo "<?php
        /**
 * MulticacheWP
 * http://www.multicache.org
 * High Performance fastcache Controller
 * Version: 1.0.0.6
 * Author: Wayne DSouza
 * Author URI: http://onlinemarketingconsultants.in
 * License: GNU PUBLIC LICENSE see license.txt
 */
defined('_MULTICACHEWP_EXEC') or die();
				
class $class_name{
public static \$js_switch = " . $switch . ";
	
public static \$simulation_id = " . $load_state . "	;
	
public static \$stubs = " . $stubs . " ;
     ";
		$cl_buf = ob_get_clean();
		if (! empty($JSTexclude->settings) && (! empty($JSTexclude->url) || ! empty($JSTexclude->query)))
		{
			ob_start();
			echo "
public static \$JSTsetting = " . $JSTsettings . ";
  ";
			$cl_buf .= ob_get_clean();
		}
		if (! empty($JSTexclude->url))
		{
			ob_start();
			echo "
public static \$JSTCludeUrl = " . $JSTurl . ";
  ";
			$cl_buf .= ob_get_clean();
		}
	
		if (! empty($JSTexclude->query))
		{
	
			ob_start();
			echo "
public static \$JSTCludeQuery = " . $JSTquery . ";
  ";
			$cl_buf .= ob_get_clean();
		}
		if (! empty($JSTexclude->component))
		{
	
			ob_start();
			echo "
public static \$JSTexcluded_components = " . $JSTcomponents . ";
  ";
			$cl_buf .= ob_get_clean();
		}
		if (! empty($JSTexclude->url_strings))
		{
	
			ob_start();
			echo "
public static \$JSTurl_strings = " . $JSTurlstrings . ";
  ";
			$cl_buf .= ob_get_clean();
		}
		//$comparitive_object
		if(!empty($comparitive_object))
		{
			foreach($comparitive_object As $key => $val)
			{
				$val = var_export($val , true);
				ob_start();
				echo "
public  static  \$". $key." = ". $val .";
						";
				$cl_buf .= ob_get_clean();
		
			}
		}
		ob_start();
		echo "
	
	
	
public static function getJsSignature(){
\$sigss = " . trim($signature_hash) . ";
Return \$sigss;
}
	
	
public static function getLoadSection(){
\$loadsec = " . trim($loadsection) . ";
Return \$loadsec;
}
	
	
}
?>";
		$cl_buf .= ob_get_clean();
		$cl_buf = serialize($cl_buf);
	
		//$dir = JPATH_ADMINISTRATOR . '/components/com_multicache/lib';
		if(is_multisite())
		{
			$dir = $ms_obj['strategy']['location'];
			$dir = rtrim( $dir, '/\\' );
		}
		else{
			$dir = plugin_dir_path(dirname(__FILE__)).'libs/';//JPATH_ADMINISTRATOR . '/components/com_multicache/lib';
		}
		//$dir = plugin_dir_path(dirname(__FILE__)).'libs/';//JPATH_ADMINISTRATOR . '/components/com_multicache/lib';
		$filename = 'jscachestrategy.php';
		$success = self::writefileTolocation($dir, $filename, $cl_buf);
		Return $success;
	
	}
	protected static function getComparitiveStrategy( $ms_obj)
	{
		if(is_multisite() && $ms_obj['strategy']['jscachestrategy_exists'])
		{
			require_once $ms_obj['strategy']['jscachestrategy_loc'];
			$class_name = $ms_obj['strategy']['jscachestrategy_namespace'];
		}
		else
		{
			require_once dirname(dirname(__FILE__)).'/libs/jscachestrategy.php';
			$class_name = 'JsStrategy';
		}
		if(class_exists($class_name))
		{
			$class_vars = get_class_vars ($class_name);
			//var_dump($class_vars);echo "<br><br>";
			$obj = new stdClass();
			foreach($class_vars As $key=>$val)
			{
				switch($key)
				{
					case 'js_switch':
					case 'simulation_id':
					case 'stubs':
					case 'JSTsetting':
					case 'JSTexclude':
					case 'JSTCludeUrl':
					case 'JSTCludeQuery':
					case 'JSTexcluded_components':
					case 'JSTurl_strings':
					continue 2;
					break;
					default:
					break;
				}
				//var_dump($key , $val);echo "<br><br>";
				$obj->$key = $val;
			}
			Return $obj;
		}
		Return false;
	}
	
	public static function writeJsCacheStrategy($signature_hash, $loadsection, $switch = null, $load_state = null, $stubs = null, $JSTexclude = null ,$ms_obj = false)
	{
		$comparitive_object = self::getComparitiveStrategy($ms_obj);
		if(is_multisite())
		{
			$class_name = $ms_obj['strategy']['JsStrategySimControl_namespace'];
		}
		else {
			$class_name = 'JsStrategySimControl';
		}
	
		if (empty($signature_hash))
		{
			if (! class_exists($class_name))
			{
				Return false;
			}
			if (method_exists($class_name, 'getJsSignature'))
			{
				$signature_hash = $class_name::getJsSignature();
			}
			else
			{
				$signature_hash = null;
			}
		}
		if (empty($loadsection))
		{
			if (! class_exists($class_name))
			{
				Return false;
			}
			if (method_exists($class_name, 'getLoadSection'))
			{
				$loadsection = $class_name::getLoadSection();
			}
			else
			{
				$loadsection = null;
			}
		}
	
		//$file = JPATH_ADMINISTRATOR . '/components/com_multicache/lib/jscachestrategy_simcontrol.php';
		$signature_hash = preg_replace('/\s/', '', var_export($signature_hash, true));
		$signature_hash = str_replace(',)', ')', $signature_hash);
		$loadsection = var_export($loadsection, true);
		$load_state = isset($load_state) ? var_export($load_state, true) : null;
	
		$stubs = var_export($stubs, true);
		if (! empty($JSTexclude->url))
		{
			$JSTurl = preg_replace('/\s/', '', var_export($JSTexclude->url, true));
			$JSTurl = str_replace(',)', ')', $JSTurl);
		}
		if (! empty($JSTexclude->query))
		{
			$JSTquery = preg_replace('/\s/', '', var_export($JSTexclude->query, true));
			$JSTquery = str_replace(',)', ')', $JSTquery);
		}
		if (! empty($JSTexclude->settings))
		{
			$JSTsettings = var_export($JSTexclude->settings, true);
		}
		if (! empty($JSTexclude->component))
		{
			$JSTcomponents = preg_replace('/\s/', '', var_export($JSTexclude->component, true));
			$JSTcomponents = str_replace(',)', ')', $JSTcomponents);
		}
		if (! empty($JSTexclude->url_strings))
		{
			$JSTurlstrings = preg_replace('/\s/', '', var_export($JSTexclude->url_strings, true));
			$JSTurlstrings = str_replace(',)', ')', $JSTurlstrings);
		}
	
		ob_start();
		echo "<?php
/**
 * MulticacheWP
 * http://www.multicache.org
 * High Performance fastcache Controller
 * Version: 1.0.0.6
 * Author: Wayne DSouza
 * Author URI: http://onlinemarketingconsultants.in
 * License: GNU PUBLIC LICENSE see license.txt
 */
defined('_MULTICACHEWP_EXEC') or die();
				
class $class_name{
public static \$js_switch = " . $switch . "	;
	
public static \$simulation_id = " . $load_state . "	;
	
	
public static \$stubs = " . $stubs . " ;
    ";
		$cl_buf = ob_get_clean();
		if (! empty($JSTexclude->settings) && (! empty($JSTexclude->url) || ! empty($JSTexclude->query)))
		{
			ob_start();
			echo "
public static \$JSTsetting = " . $JSTsettings . ";
  ";
			$cl_buf .= ob_get_clean();
		}
		if (! empty($JSTexclude->url))
		{
			ob_start();
			echo "
public static \$JSTCludeUrl = " . $JSTurl . ";
  ";
			$cl_buf .= ob_get_clean();
		}
	
		if (! empty($JSTexclude->query))
		{
	
			ob_start();
			echo "
public static \$JSTCludeQuery = " . $JSTquery . ";
  ";
			$cl_buf .= ob_get_clean();
		}
		if (! empty($JSTexclude->component))
		{
	
			ob_start();
			echo "
public static \$JSTexcluded_components = " . $JSTcomponents . ";
  ";
			$cl_buf .= ob_get_clean();
		}
		if (! empty($JSTexclude->url_strings))
		{
	
			ob_start();
			echo "
public static \$JSTurl_strings = " . $JSTurlstrings . ";
  ";
			$cl_buf .= ob_get_clean();
		}
		//$comparitive_object
		if(!empty($comparitive_object))
		{
			foreach($comparitive_object As $key => $val)
			{
				$val = var_export($val , true);
				ob_start();
				echo " 
public  static  \$". $key." = ". $val .";
						";
				$cl_buf .= ob_get_clean();
				
			}
		}
		ob_start();
		echo "
	
	
public static function getJsSignature(){
\$sigss = " . trim($signature_hash) . ";
Return \$sigss;
}
	
	
public static function getLoadSection(){
\$loadsec = " . trim($loadsection) . ";
Return \$loadsec;
}
	
	
}
?>";
		$cl_buf .= ob_get_clean();
		$cl_buf = serialize($cl_buf);
	
		//$dir = JPATH_ADMINISTRATOR . '/components/com_multicache/lib';
		if(is_multisite())
		{
			$dir = $ms_obj['strategy']['location'];
			$dir = rtrim( $dir, '/\\' );
		}
		else{
		$dir = plugin_dir_path(dirname(__FILE__)).'libs/';//JPATH_ADMINISTRATOR . '/components/com_multicache/lib';
		}
		$filename = 'jscachestrategy_simcontrol.php';
		$success = self::writefileTolocation($dir, $filename, $cl_buf);
		Return $success;
	
	}
	
	public static function largeIntCompare($a , $b , $s=null ) 
	{
		// check if they're valid positive numbers, extract the whole numbers and decimals
		if(!preg_match("~^\+?(\d+)(\.\d+)?$~", $a, $match1)
		|| !preg_match("~^\+?(\d+)(\.\d+)?$~", $b, $match2))
		{
			return false;
		}
	
		// remove leading zeroes from whole numbers
		$a = ltrim($match1[1],'0');
		$b = ltrim($match2[1],'0');
	
		// first, we can just check the lengths of the numbers, this can help save processing time
		// if $a is longer than $b, return 1.. vice versa with the next step.
		if(strlen($a)>strlen($b))
		{
			return 1;
		}
		else
		 {
			if(strlen($a)<strlen($b))
			{
				return -1;
			}
	
			// if the two numbers are of equal length, we check digit-by-digit
			else {
	
				// remove ending zeroes from decimals and remove point
				$decimal1 = isset( $match1[2] ) ? rtrim( substr( $match1[2] ,1 ) ,'0' ) :'';
				$decimal2 = isset( $match2[2] ) ? rtrim( substr( $match2[2] ,1 ) ,'0' ) :'';
	
				// scaling if defined
				if($s!== null) 
				{
					$decimal1 = substr($decimal1 ,0 , $s);
					$decimal2 = substr($decimal2 ,0 , $s);
				}
	
				// calculate the longest length of decimals
				$DLen = max( strlen($decimal1) , strlen($decimal2) );
	
				// append the padded decimals onto the end of the whole numbers
				$a .=str_pad( $decimal1 ,$DLen, '0');
				$b .=str_pad( $decimal2 ,$DLen, '0');
	
				// check digit-by-digit, if they have a difference, return 1 or -1 (greater/lower than)
				for($i=0;$i<strlen($a);$i++) 
				{
					if((int)$a{$i}>(int)$b{$i})
					{
						return 1;
					}
					else
						if((int)$a{$i}<(int)$b{$i})
						{
							return -1;
						}
				}
	
				// if the two numbers have no difference (they're the same).. return 0
				return 0;
			}
		}
	}
	
	public static function getJScodeUrl($load_state, $key, $type = null, $jquery_scope = "$", $media = "default")
	{
	
		//$base_url = '//' . str_replace("http://", "", strtolower(substr(JURI::root(), 0, - 1)) . '/media/com_multicache/assets/js/jscache/');
		$base_url = plugins_url( 'delivery/assets/js/jscache/', dirname(__FILE__));
		if (isset($type) && $type == "raw_url")
		{
			Return $base_url . $key . '-' . $load_state . ".js?mediaVersion=" . $media;
		}
		// script_url
	
		if (isset($type) && $type == "script_url")
		{
			$script = '<script src="' . $base_url . $key . '-' . $load_state . '.js?mediaVersion=' . $media . '"   type="text/javascript" ></script>';
			Return serialize($script);
		}
		$url = $jquery_scope . '.getScript(' . '"' . $base_url . $key . '-' . $load_state . '.js?mediaVersion=' . $media . '"' . ');';
	
		Return serialize($url);
	
	}
	
	public static function getdelaycode($delay_type, $jquery_scope = "$", $mediaFormat)
	{
	
		//$app = JFactory::getApplication();
		// $delay_type = self::extractDelayType($delay_array);
		//$base_url = '//' . str_replace("http://", "", strtolower(substr(JURI::root(), 0, - 1)) . '/media/com_multicache/assets/js/jscache/');
		$base_url = plugin_dir_url(dirname(__FILE__)).'delivery/assets/js/jscache/';
		if ($delay_type == "scroll")
		{
			$name = "simcontrol_onscrolldelay.js";
			$url = $base_url . $name;
			$inline_code = '
                            var url_' . $delay_type . ' = "' . $url . '?mediaFormat=' . $mediaFormat . '";var script_delay_' . $delay_type . '_counter = 0;var max_trys_' . $delay_type . ' = 3;var inter_lock_' . $delay_type . '= 1;' . $jquery_scope . '(window).scroll(function(event) {/*alert("count "+script_delay_' . $delay_type . '_counter);*/console.log("count " + script_delay_' . $delay_type . '_counter);console.log("scroll detected" );if(!inter_lock_' . $delay_type . '){return;/*an equivalent to continue*/}++script_delay_' . $delay_type . '_counter;if(script_delay_' . $delay_type . '_counter <=  max_trys_' . $delay_type . ') {inter_lock_' . $delay_type . ' = 0;'                                                      . $jquery_scope . '( this).unbind( event );console.log("unbind");' . $jquery_scope . '.getScript( url_' . $delay_type . ', function() {console.log("getscrpt call on ' . $delay_type . '" );script_delay_' . $delay_type . '_counter =  max_trys_' . $delay_type . '+1;}).fail(function(jqxhr, settings, exception) {/*alert("loading failed" + url_' . $delay_type . ');*/console.log("loading failed in " + url_' . $delay_type . ' +" trial "+ script_delay_' . $delay_type . '_counter);console.log("exception -"+ exception);console.log("settings -"+ settings);console.log("jqxhr -"+ jqxhr);inter_lock_' . $delay_type . ' = 1;});}else{/* alert("giving up");*/' . $jquery_scope . '( this).unbind( "scroll" );console.log("failed scroll loading  "+ url_' . $delay_type . '+"  giving up" );}});';
			
		}
		elseif ($delay_type == "mousemove")
		{
			$name = "simcontrol_onmousemovedelay.js";
			$url = $base_url . $name;
			$inline_code = '
var url_' . $delay_type . ' = "' . $url . '?mediaFormat=' . $mediaFormat . '";var script_delay_' . $delay_type . '_counter = 0;var max_trys_' . $delay_type . ' = 3;var inter_lock_' . $delay_type . '= 1;' . $jquery_scope . '(window).on("mousemove" ,function( event ) {/*alert("count "+script_delay_counter);*/console.log("count " + script_delay_' . $delay_type . '_counter);console.log("mousemove detected" );if(!inter_lock_' . $delay_type . '){return;/*an equivalent to continue*/}++script_delay_' . $delay_type . '_counter;if(script_delay_' . $delay_type . '_counter <= max_trys_' . $delay_type . ') {inter_lock_' . $delay_type . ' = 0;' . $jquery_scope . '( this).unbind( event );console.log("unbind");' . $jquery_scope . '.getScript( url_' . $delay_type . ', function() {console.log("getscrpt call on ' . $delay_type . '" );script_delay_' . $delay_type . '_counter =  max_trys_' . $delay_type . ';}).fail(function(jqxhr, settings, exception) {/*alert("loading failed" + url_' . $delay_type . ');*/console.log("loading failed in " + url_' . $delay_type . '  +" trial "+ script_delay_' . $delay_type . '_counter);console.log("exception -"+ exception);console.log("settings -"+ settings);console.log("jqxhr -"+ jqxhr);inter_lock_' . $delay_type . ' = 1;});}else{/* alert("giving up");*/' . $jquery_scope . '( this).unbind( "mousemove" );console.log("failed loading "+ url_' . $delay_type . '+"  giving up" );}});';
			
		}
		else
		{
			self::prepareMessageEnqueue(__('Simcontrol getDelayCode JS Delay encountered an unlisted delay type while preparing delay code'), 'error');
			//$app->enqueueMessage(JText::_('COM_MULTICACHE_ERROR_JQUERY_DELAY_TYPE_UNLISTED_CONDITION'), 'notice');
		}
	
		$obj["code"] = serialize($inline_code);
		$obj["url"] = $name;
	
		Return $obj;
	
	}
	
	protected static function makeMultisiteFolder($dir )
	{
		if (! is_dir($dir))
		{
			 
			// Make sure the index file is there
			$indexFile = $dir . '/index.html';
			@mkdir($dir) && file_put_contents($indexFile, '<!DOCTYPE html><title></title>');
			if (! is_dir($dir))
			{
				self::log_error(__('Multisite failed to create location','multicache-plugin'),'multisite-errors',$dir);
			}
		}
	}
	public static function loadSinglePrerequisites()
	{
		if(file_exists(plugin_dir_path(dirname(__FILE__)) . 'libs/pagescripts.php'))
		{
			require_once plugin_dir_path(dirname(__FILE__)) . 'libs/pagescripts.php';
		}
				
		if(file_exists(plugin_dir_path(dirname(__FILE__)) . 'libs/jscachestrategy.php'))
		{
			require_once plugin_dir_path(dirname(__FILE__)) . 'libs/jscachestrategy.php';
		}
				
		if(file_exists(plugin_dir_path(__FILE__) . 'multicache_loadinstruction.php'))
		{
			require_once plugin_dir_path(__FILE__) . 'multicache_loadinstruction.php';
		}
	}
	public static function getBlogConfig($id = false)
	{
		/*static $conf;
		if(isset($conf))
		{
			Return $conf;
		}
		*/
		$config = is_multisite()? self::resolveConfig($id):self::getConfig();
		$config = ($config)? self::getConfig(): $config;
		if(empty($config))
		{
			Return false;
		}
		$conf = $config;
		Return $conf;
	}
	public static function resolveConfig( $id)
	{
	
		$ms_obj = self::resolveLocMulbyID($id);
	
		if(false !== $ms_obj && is_file($ms_obj['strategy']['config_loc']))
		{
	
			$config = self::getConfig($ms_obj['strategy']['config_loc'] ,'PHP',$ms_obj['strategy']['namespace'] );
	
		}
		else {
			if(is_multisite() && !is_admin())
			{
			 Return false;
			}
			$config = self::getConfig();
		}
	
		Return $config;
		//$namespace = empty($u_split[1])? '': 'S' $u_split[1];
	
	}
	public static function getConfig($file = null, $type = 'PHP', $namespace = '')
	{
	
		if (! self::$config)
		{
			if ($file === null)
			{
				$file = dirname(dirname(__FILE__)) . '/libs/multicache_config.php';
			}
	
			self::$config = self::createConfig($file, $type, $namespace);
		}
		return self::$config;
	
	}
	
	public function getC($c_key, $default = null)
	{
	
		if (empty(self::$config) && ! isset($default))
		{
			Return false;
		}
	
		$conf_value = isset(self::$config->data->$c_key) ? self::$config->data->$c_key: null;
		if (! isset($conf_value))
		{
			if (isset($default))
			{
				Return $default;
			}
			Return null;
		}
		Return $conf_value;
	
	}
	
	protected static function createConfig($file, $type = 'PHP', $namespace = '')
	{
		;
		if (is_file($file))
		{
			include_once $file;
		}
	
		$register = new MulticacheHelperSimcontrol();
		// Sanitize the namespace.
		$namespace = ucfirst((string) preg_replace('/[^a-zA-Z0-9_]/i', '', $namespace));
		 
		// Build the config name.
		$name = 'MulticacheConfig' . $namespace;
		// Handle the PHP configuration type.
		if ($type == 'PHP' && class_exists($name))
		{
			// Create the JConfig object
			$config = new $name();
	
			// Load the configuration values into the registry
			$register->loadObject($config);
		}
		return $register;
	
	}
	
	protected function loadObject($object)
	{
	
		$this->bindData($this->data, $object);
	
	}
	protected function bindData($parent, $data)
	{
		// Ensure the input data is an array.
		if (is_object($data))
		{
			$data = get_object_vars($data);
		}
		else
		{
			$data = (array) $data;
		}
		foreach ($data as $k => $v)
		{
			if ((is_array($v) && self::isAssociative($v)) || is_object($v))
			{
				$parent->$k = new stdClass();
				$this->bindData($parent->$k, $v);
			}
			else
			{
				$parent->$k = $v;
			}
		}
	
	}
	protected static function isAssociative($array)
	{
	
		if (is_array($array))
		{
			foreach (array_keys($array) as $k => $v)
			{
				if ($k !== $v)
				{
					return true;
				}
			}
		}
		return false;
	
	}
	public  static function resolveLocMulbyID($blog_id)
	{
		if(!is_multisite())
		{
			return false;
		}
		$hash = $blog_id;
		static $resolvemul;
		if(isset($resolvemul[$hash]))
		{
			Return $resolvemul[$hash];
		}
		$blog_info = get_blog_details( $blog_id );
		$dir = dirname(__FILE__).'/libs';
		$dir = rtrim( $dir, '/\\' );
		$dir = $dir . '/multisite_strategy';
		self::makeMultisiteFolder($dir  );
		$current_site = get_current_site();
		//current site object is not updated when switched to blog
		
		
		$obj = array();
		if(defined('SUBDOMAIN_INSTALL') && SUBDOMAIN_INSTALL===true)
		{
			$domain = $blog_info->domain;
			$host = str_replace(array('.www.','www.'),array('.',''),$domain);
			preg_match('~^(?:([^\.]+)\.)?([^.]+\..+)~six' ,$host,$domains );
			$blog = !empty($domains[1])? preg_replace('~[^a-zA-Z0-9]~' , '',$domains[1]): 'main';
			$site = preg_replace('~[^a-zA-Z0-9]~' , '',$domains[2]);
			$current_site_path = $dir . '/' .$site;
			self::makeMultisiteFolder($current_site_path );
			$b_path = $current_site_path.'/'.$blog;
			self::makeMultisiteFolder($b_path );
			$b_idpath = $current_site_path .'/'.$blog_id;
			self::makeMultisiteFolder($b_idpath );
			$s1 = 'S'.$site;
			$tpath = $s1.'Bl'.$blog;
			//start
			$strategy_location = dirname(dirname(__FILE__)).'/libs/multisite_strategy/'.$site;
			$strategy_location .= '/'.$blog.'/';
			//stop
	
		}
		else{
			$current_blog_path = $blog_info->path;
			$current_site_id = $current_site->id;
			if(defined('PATH_CURRENT_SITE') && PATH_CURRENT_SITE !=='/')
			{
				$folder_string = PATH_CURRENT_SITE;
				$a = preg_quote($folder_string);
				$search = '~'.$a.'(.*)~';
				preg_match( $search ,$current_blog_path ,$m);
				$current_blog_path = '/'. $m[1];
				 
			}
			$current_site_domain = str_replace(array('www.','.'),'',$current_site->domain);
			$current_site_domain = preg_replace('~[^a-zA-Z0-9]~','',$current_site_domain);
			//relative site path
			$current_site_path = $dir . '/' .$current_site_domain;
			self::makeMultisiteFolder($current_site_path );
			//relative blog path
			$b_path = $current_blog_path==='/'? $current_site_path .'/'.'main' : $current_site_path .rtrim( $current_blog_path, '/\\' );
			self::makeMultisiteFolder($b_path );
			$b_idpath = $current_site_path .'/'.$blog_id;
			self::makeMultisiteFolder($b_idpath );
	
			$s1 = 'S'.$current_site_domain;
			$tpath =$current_blog_path==='/'? $s1.'Blmain': $s1.'Bl'.preg_replace('~[^a-zA-Z0-9]~','',$current_blog_path);
	$strategy_location = dirname(dirname(__FILE__)).'/libs/multisite_strategy/'.$current_site_domain;
	$strategy_location .= $current_blog_path==='/'? '/main/': $current_blog_path;
		}
		 
		 
		 
		 
		$tid = $s1.'Bl'.$blog_id;
		/*$obj['path'] = array('path' =>$b_path , 'tag'=>$tpath);
		$obj['id'] = array('path' =>$b_idpath , 'tag' => $tid);*/
		$obj['strategy'] = array('location' => $strategy_location,
				'exists' => is_dir($strategy_location),
				'config_loc'=> $strategy_location.'multicache_config.php',
				'config_exists' => is_file($strategy_location.'multicache_config.php'),
				'namespace' => $tpath,
				'config_name' => 'MulticacheConfig'.$tpath,
				'simulation_lib_loc' => $b_path .'/',
				'pagescripts_loc' => $strategy_location.'pagescripts.php',
				'pagescripts_exist'=> is_file($strategy_location.'pagescripts.php'),
				'pagecss_loc'=> $strategy_location.'pagecss.php',
				'pagecss_exists'=>is_file($strategy_location.'pagecss.php'),
				'jscachestrategy_loc' => $strategy_location.'jscachestrategy.php',
				'jscachestrategy_exists' => is_file($strategy_location.'jscachestrategy.php'),
				'jscachestrategy_namespace'=>'JsStrategy'.$tpath,
				'loadinstruction_loc' => $b_path.'/multicache_loadinstruction.php',
				'loadinstruction_exists' => is_file($b_path.'/multicache_loadinstruction.php'),
				'JsStrategySimControl_loc'=> $strategy_location.'jscachestrategy_simcontrol.php',
				'JsStrategySimControl_exists' => is_file($strategy_location.'jscachestrategy_simcontrol.php'),
				'JsStrategySimControl_namespace' => 'JsStrategySimControl'.$tpath,
		);
		if($obj['strategy']['config_exists'])
		{
			require_once $obj['strategy']['config_loc'];
		}
		if($obj['strategy']['pagescripts_exist'])
		{
			require_once $obj['strategy']['pagescripts_loc'];
		}
		if($obj['strategy']['pagecss_exists'])
		{
			require_once $obj['strategy']['pagecss_loc'];
		}
		if($obj['strategy']['jscachestrategy_exists'])
		{
			require_once $obj['strategy']['jscachestrategy_loc'];
		}
		if($obj['strategy']['loadinstruction_exists'])
		{
			require_once $obj['strategy']['loadinstruction_loc'];
		}
		if($obj['strategy']['JsStrategySimControl_exists'])
		{
			require_once $obj['strategy']['JsStrategySimControl_loc'];
		}
		$obj['strategy']['config_loaded'] = class_exists($obj['strategy']['config_name']);
		$obj['strategy']['pagescripts_loaded'] = class_exists('MulticachePageScripts');
		$obj['strategy']['pagecss_loaded'] = class_exists('MulticachePageCss');
		$obj['strategy']['jscachestrategy_loaded'] = class_exists($obj['strategy']['jscachestrategy_namespace']);
		$obj['strategy']['loadinstruction_loaded'] = class_exists('MulticacheLoadinstruction');
		$obj['strategy']['jsstrategysimcontrol_loaded'] = class_exists($obj['strategy']['JsStrategySimControl_namespace']);
		
		$resolvemul[$hash] = $obj;
		Return $resolvemul[$hash];
		//end
		 
		 
		//$blog_info = get_blog_details( $blog_id );
		 
		//var_dump($blog_info);exit;
	}
}