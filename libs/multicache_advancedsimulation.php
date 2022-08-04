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

if(file_exists(plugin_dir_path(dirname(__FILE__)) . 'simcontrol/libs/multicache_loadinstruction.php'))
{
	require_once plugin_dir_path(dirname(__FILE__)) . 'simcontrol/libs/multicache_loadinstruction.php';
}

class MulticacheAdvancedSimulation
{
	protected static $instance = null;
	protected $order_by = null;
	protected $order_dirn = null;
	protected $paged = null;
	protected $total_pages = null;
	protected $limit = null;
	protected $transient = null;
	protected $simflag = null;
	protected $completeflag = null;
	protected $toleranceflag = null;
	protected $datefrom = null;
	protected $dateto = null;
	protected $handlersflag = null;
	protected $testpagesflag = null;
	protected $precacheflag = null;
	protected $lzfactorflag = null;
	protected $hammerflag = null;
	
	
	public function __construct()
	{
		$this->order_by = $this->getOrder();
		$this->order_dirn = $this->getDirn();
		$this->paged = $this->getPaged();
		$this->limit = 20;
		$this->setFilterFlags();
	}
	protected function getPaged()
	{
		$paged = absint(MulticacheUri::getInstance()->getVar('paged'));
		Return  !empty($paged) ? $paged : 1;
	
	}
	
	protected function getTransient()
	{
		$rel = 'msd';
		$user_ID = get_current_user_id();
		$this->transient = get_transient('multicache_filter_order_dir_'.$user_ID.'_'.$rel);
	}
	
	protected function getOrder()
	{
		if(!isset($this->transient))
		{
			$this->getTransient();
		}
		if(isset($this->transient['order']))
		{
			switch($this->transient['order'])
			{
				case 'id':
					$return = 'id';
					break;
				case 'date':
					$return = 'date_of_test';
					break;
				case 'plt':
					$return = 'page_load_time';
					break;
				case 'hlt':
				    $return = 'html_load_time';
				    break;
				case 'reports':
					$return = 'report_url';
					break;
				case 'pre':
					$return ='precache_factor';
					break;
				case 'fast':
					$return = 'cache_compression_factor';
					break;
				case 'speed':
					$return = 'pagespeed_score';
					break;
				case 'yslow':
					$return = 'yslow_score';
					break;
				case 'pagee':
					$return = 'page_elements';
					break;
				case 'htmlsize':
					$return = 'html_bytes';
					break;
				case 'pagesize':
					$return = 'page_bytes';
					break;
					
			}
			
		}
		Return isset($return)? $return : 'id';
		
	}
	
	protected function getReverseOrder($a)
	{
		switch($a)
		{
			case 'id':
				$return = 'id';
				break;
			case 'date_of_test':
				$return = 'date';
				break;
			case 'page_load_time':
				$return = 'plt' ;
				break;
			case 'html_load_time':
				$return = 'hlt';
				break;
			case  'report_url':
				$return = 'reports';
				break;
			case  'precache_factor':
				$return ='pre';
				break;
			case  'cache_compression_factor':
				$return = 'fast';
				break;
			case 'pagespeed_score':
				$return = 'speed';
				break;
			case  'yslow_score':
				$return = 'yslow';
				break;
			case 'page_elements':
				$return = 'pagee';
				break;
			case  'html_bytes':
				$return = 'htmlsize';
				break;
			case  'page_bytes':
				$return = 'pagesize';
				break;
					
		}
		Return isset($return)? $return: 'id';
	}
	
	protected function getDirn()
	{
		if(!isset($this->transient))
		{
			$this->getTransient();
		}
		$dir = isset($this->transient['dir']) && $this->transient['dir'] == 'asc'? 'asc':'desc';
		Return $dir;
	}
	
	
	public static function getInstance()
	{
		// Only create the object if it doesn't exist.
		if (empty(self::$instance))
		{
	
			self::$instance = new MulticacheAdvancedSimulation();
		}
		return self::$instance;
	
	}
	
	public function delete($pks)
	{
		if(empty($pks))
		{
			Return false;
		}
	
		//$app = JFactory::getApplication();
	
		if(! current_user_can('manage_options'))
		{
			MulticacheHelper::prepareMessageEnqueue(__('Not authorised to delete results','multicache-plugin'));
			Return false;
		}
		global $wpdb;
		$table = $wpdb->prefix.'multicache_advanced_test_results';
		foreach ($pks as $i => $pk)
		{
			$where = array('id' => $pk);
			$where_format = array('%d');
			$wpdb->delete( $table, $where, $where_format = null );
	/*
			$query = $db->getQuery('true');
			$conditions = array(
					$db->quoteName('id') . ' = ' . $pk
			);
			$query->delete($db->quoteName('#__multicache_advanced_test_results'));
			$query->where($conditions);
	
			$db->setQuery($query);
	
			$result = $db->execute();
			*/
		}
	
	}
	public function getFilterableStat()
	{
		static $filterables = null;
		if(isset($filterables))
		{
			Return $filterables;
		}
		global $wpdb;
		$table = $wpdb->prefix.'multicache_advanced_test_results';
		$filter = new stdClass();
		$filter->precache = $this->getDistprecacheoptions();
		$filter->ccomp = $this->getDistlzoptions();
		$filter->distribution = $this->prepareDistributions($this->getDistmdistribution());
		$filter->pages = $this->getDistpages();
		$filter->preset = $this->getPreset();
		$filterables = $filter;
		Return $filterables;
	}
	
	protected function prepareDistributions( $a)
	{
		if(empty($a))
		{
			Return false;
		}
		$distribution = array();
		foreach($a As $val)
		{
			switch($val)
			{
				case 3:
					$dist = __('HammerMode','multicache-plugin');
					break;
				case 2: 
					$dist = __('LHM','multicache-plugin');
					break;
				case 1:
					$dist = __('MultiAdmin','multicache-plugin');
					break;
				case 0:
					$dist = __('CartMode','multicache-plugin');
					break;
				default:
					$dist = __('na','multicache-plugin');
				
			}
			$distribution[$val] = $dist;
		}
		Return $distribution;
	}
	
	protected function getPreset()
	{
		$preset = new stdClass();
		$preset->precache = isset($this->precacheflag)? (int) $this->precacheflag : '';
		$preset->ccomp = isset($this->lzfactorflag) ?(int) $this->lzfactorflag : '';
		$preset->distribution = isset($this->hammerflag)? (int)$this->hammerflag: '';
		$preset->pages = isset($this->testpagesflag)?(int) $this->testpagesflag : '';
		$preset->datefrom = isset($this->datefrom)? $this->datefrom : '';
		$preset->dateto = isset($this->dateto)? $this->dateto : '';
		$preset->results_type = $this->simflag;
		$preset->complete = (int) $this->completeflag;
		$preset->tolerance =(int) $this->toleranceflag;
		$preset->cache_type =(int) $this->handlersflag;
		Return $preset;
	}
	public function getDistcachehandlers()
	{
	global $wpdb;
	$table = $wpdb->prefix.'multicache_advanced_test_results';
	$query = "SELECT DISTINCT cache_handler FROM $table";
	$result = $wpdb->get_col($query);
	/*
		$db = JFactory::getDbo();
		$query = $db->getQuery('true');
		$query->select('DISTINCT ' . $db->quoteName('cache_handler'));
		$query->from($db->quoteName('#__multicache_advanced_test_results'));
		$query->where($db->quoteName('cache_handler') . ' != ' . $db->quote(''));
		$db->setQuery($query);
		$result = $db->loadColumn();
		*/
		Return $result;
	
	}
	

	
	protected function getDistpages()
	{
		static $distpages = null;
		if(isset($distpages))
		{
			Return $distpages;
		}
	global $wpdb;
	$table = $wpdb->prefix.'multicache_advanced_test_results';
	$query = "SELECT DISTINCT test_page FROM $table";
	$result = $wpdb->get_col($query);
	/*
		$db = JFactory::getDbo();
		$query = $db->getQuery('true');
		$query->select('DISTINCT ' . $db->quoteName('test_page'));
		$query->from($db->quoteName('#__multicache_advanced_test_results'));
		$db->setQuery($query);
		$result = $db->loadColumn();
		*/
	$distpages = $result;
		Return $distpages;
	
	}
	
	protected function getDistprecacheoptions()
	{
	global $wpdb;
	$table= $wpdb->prefix.'multicache_advanced_test_results';
	$query = "SELECT DISTINCT precache_factor FROM $table";
	$result= $wpdb->get_col($query);
	/*
		$db = JFactory::getDbo();
		$query = $db->getQuery('true');
		$query->select('DISTINCT ' . $db->quoteName('precache_factor'));
		$query->from($db->quoteName('#__multicache_advanced_test_results'));
		$db->setQuery($query);
		$result = $db->loadColumn();
		*/
		Return $result;
	
	}
	
	protected function getDistlzoptions()
	{
		static $lz_options= null;
		if(isset($lz_options))
		{
			Return $lz_options;
		}
		global $wpdb;
		$table = $wpdb->prefix.'multicache_advanced_test_results';
		$query = "SELECT DISTINCT cache_compression_factor FROM $table";
		$result = $wpdb->get_col($query);
		/*
			$db = JFactory::getDbo();
			$query = $db->getQuery('true');
			$query->select('DISTINCT ' . $db->quoteName('cache_compression_factor'));
			$query->from($db->quoteName('#__multicache_advanced_test_results'));
			$db->setQuery($query);
			$result = $db->loadColumn();
		*/
		$lz_options = $result;
		Return $lz_options;
	
	}
	
	protected function getDistmdistribution()
	{
	global $wpdb;
	$table = $wpdb->prefix.'multicache_advanced_test_results';
	$query = "SELECT DISTINCT hammer_mode FROM $table";
	$result = $wpdb->get_col($query);
	/*
		$db = JFactory::getDbo();
		$query = $db->getQuery('true');
		$query->select('DISTINCT ' . $db->quoteName('hammer_mode'));
		$query->from($db->quoteName('#__multicache_advanced_test_results'));
		$db->setQuery($query);
		$result = $db->loadColumn();
		*/
		Return $result;
	
	}
	
	public function getASItems()
	{
		static $_AS_items = null;
		if(isset($_AS_items))
		{
			Return $_AS_items;
		}
		global $wpdb;
		$table = $wpdb->prefix.'multicache_advanced_test_results';
		$where = $this->makeWhereClause();
		$sql = "SELECT id FROM $table ".$where;
		$sum_obj = $wpdb->get_results($sql , OBJECT);
		$this->total_pages = !empty($this->limit) && !empty($wpdb->num_rows)? (int) ceil($wpdb->num_rows / $this->limit):1;
		//special condition to filter to page 1
		
		if(isset($this->paged) && $this->paged > $this->total_pages)
		{
			$u = MulticacheUri::getInstance();
			$u->setVar('paged', $this->total_pages);
		    wp_redirect( $u->toString());
		}
		$offset = $this->limit * ($this->paged-1);
		
		
		$query = "SELECT * FROM $table ".$where." ORDER BY $this->order_by $this->order_dirn LIMIT $this->limit OFFSET $offset";
		$result = $wpdb->get_results($query , OBJECT);
		$url_items = new stdClass();
		$url_items->items = $result;
		$url_items->pagination = array('total_pages' => $this->total_pages,'current_page' => $this->paged);
		$url_items->order = $this->getReverseOrder($this->order_by) ;
		$url_items->direction = $this->order_dirn ;		
		$_AS_items = $url_items;
		Return $_AS_items;
	}
	
	protected function setFilterFlags()
	{
		$user_ID = get_current_user_id();
		$filter_flags = get_transient('multicache_advsim_filters'.$user_ID);
		
		if(empty($filter_flags))
		{
			Return false;
		}
		$this->simflag       = isset($filter_flags['filter_simflag_rt']) ? $filter_flags['filter_simflag_rt'] : null;
		$this->completeflag  = isset($filter_flags['filter_simflag_c'])  ? $filter_flags['filter_simflag_c']  : null;
		$this->toleranceflag = isset($filter_flags['filter_simflag_tol'])? $filter_flags['filter_simflag_tol']: null;
		$this->datefrom      = isset($filter_flags['filter_date_from']) && $filter_flags['filter_date_from'] !==''  ? $filter_flags['filter_date_from']  : null;
		$this->dateto        = isset($filter_flags['filter_date_to']) && $filter_flags['filter_date_to'] !== ''   ? $filter_flags['filter_date_to']    : null;
		$this->handlersflag  = isset($filter_flags['filter_simflag_ct']) ? $filter_flags['filter_simflag_ct'] : null;
		//NB testpages need an empty check to distinguish between 0 & '' when cast to int
		$this->testpagesflag = isset($filter_flags['filter_simflag_page']) && $filter_flags['filter_simflag_page'] !=='' ? $filter_flags['filter_simflag_page'] : null;
		$this->precacheflag  = isset($filter_flags['filter_simflag_precache']) && $filter_flags['filter_simflag_precache'] !== '' ? $filter_flags['filter_simflag_precache'] : null;
		$this->lzfactorflag  = isset($filter_flags['filter_simflag_ccomp']) && $filter_flags['filter_simflag_ccomp'] !== '' ? $filter_flags['filter_simflag_ccomp'] : null;
		$this->hammerflag    = isset($filter_flags['filter_simflag_opmode']) && $filter_flags['filter_simflag_opmode']!=='' ? $filter_flags['filter_simflag_opmode'] : null;
		
	}
	
	protected function makeWhereClause()
	{
		$multicacheconfig = get_option('multicache_config_options'); //$this->getMulticacheConfig();
		$params = json_decode($multicacheconfig['tolerance_params']);
		$ccomp_step = $multicacheconfig['ccomp_factor_step'];
		
		$conditions = array();
		$conditions['sim_flag'] = $this->simflag;
		$conditions['complete_flag'] = $this->completeflag ;
		$conditions['tolerance_flag'] = $this->toleranceflag;
		$conditions['datefrom_flag'] = $this->datefrom ;
		$conditions['dateto_flag'] = $this->dateto ;
		$conditions['handlers_flag'] = $this->handlersflag ;
		$conditions['testpages_flag'] = $this->testpagesflag ;
		$conditions['precache_flag'] =$this->precacheflag ;
		$conditions['lz_factor_flag'] =$this->lzfactorflag;
		$conditions['hammer_flag'] = $this->hammerflag;
		
		
		
		$WHERE_CLAUSE = array();
		if (! empty($conditions['sim_flag']))
		{
			$sim_var = ($conditions['sim_flag'] == 'simulation') ? 'simulation' : 'off';
			//$query->where($db->quoteName('simulation') . ' = ' . $db->quote($sim_var));
			$WHERE_CLAUSE[] = " simulation = '$sim_var' ";
		}
		if (! empty($conditions['complete_flag']))
		{
			$complete_var = ($conditions['complete_flag'] == '2') ? 'complete' : NULL;
			if ($complete_var)
			{
				//$query->where($db->quoteName('status') . ' = ' . $db->quote($complete_var));
				$WHERE_CLAUSE[] = " status = '$complete_var' ";
			}
			else
			{
				//$query->where($db->quoteName('status') . ' != ' . $db->quote('complete'));
				$WHERE_CLAUSE[] = " status != 'complete' ";
			}
		}
		if (! empty($conditions['tolerance_flag']))
		{
		
				
		
			if ($conditions['tolerance_flag'] == '1')
			{
				//$query->where($db->quoteName('page_load_time') . ' > ' . $db->quote($params->danger_tolerance_factor * $multicacheconfig->targetpageloadtime * 1000));
				//$query->where($db->quoteName('status') . ' =' . $db->quote('complete'));
				$t = $params->danger_tolerance_factor * $multicacheconfig["targetpageloadtime"] * 1000;
				$WHERE_CLAUSE[] = " page_load_time > '$t' ";
				//$WHERE_CLAUSE[] = " status = 'complete' ";
			}
			elseif ($conditions['tolerance_flag'] == '2')
			{
				//$query->where($db->quoteName('page_load_time') . ' < ' . $db->quote($params->danger_tolerance_factor * $multicacheconfig->targetpageloadtime * 1000));
				//$query->where($db->quoteName('page_load_time') . ' > ' . $db->quote($params->warning_tolerance_factor * $multicacheconfig->targetpageloadtime * 1000));
				//$query->where($db->quoteName('status') . ' = ' . $db->quote('complete'));
				$t = $params->danger_tolerance_factor * $multicacheconfig[targetpageloadtime] * 1000;
				$u = $params->warning_tolerance_factor * $multicacheconfig[targetpageloadtime] * 1000;
				$WHERE_CLAUSE[] = " page_load_time < '$t' ";
				$WHERE_CLAUSE[] = " page_load_time > '$u' ";
				//$WHERE_CLAUSE[] = " status = 'complete' ";
			}
			elseif ($conditions['tolerance_flag'] == '3')
			{
				//$query->where($db->quoteName('page_load_time') . ' < ' . $db->quote($multicacheconfig->targetpageloadtime * 1000));
				//$query->where($db->quoteName('status') . ' = ' . $db->quote('complete'));
				$r = $multicacheconfig[targetpageloadtime] * 1000;
				$WHERE_CLAUSE[] = " page_load_time < '$r' ";
				//$WHERE_CLAUSE[] = " status = 'complete'";
			}
			elseif ($conditions['tolerance_flag'] == '4')
			{
				//$query->where($db->quoteName('page_load_time') . ' > ' . $db->quote($multicacheconfig->targetpageloadtime * 1000));
				//$query->where($db->quoteName('page_load_time') . ' < ' . $db->quote($params->warning_tolerance_factor * $multicacheconfig->targetpageloadtime * 1000));
				//$query->where($db->quoteName('status') . ' = ' . $db->quote('complete'));
				$s = $multicacheconfig["targetpageloadtime"] * 1000;
				$t = $params->warning_tolerance_factor * $multicacheconfig["targetpageloadtime"] * 1000;
				$WHERE_CLAUSE[] = " page_load_time > '$s' ";
				$WHERE_CLAUSE[] = " page_load_time < '$t' ";
				//$WHERE_CLAUSE[] = ' status = "complete" ';
			}
			// $query->where($db->quoteName('simulation') .' = '. $db->quote($sim_var));
		}
		if (! empty($conditions['datefrom_flag']))
		{
			$convert_date = strtotime($conditions['datefrom_flag']);
			$converted_date = date("Y-m-d", $convert_date);
			//$query->where($db->quoteName('test_date') . ' >= ' . $db->quote($converted_date));
			$WHERE_CLAUSE[] = " DATE(test_date) >= '$converted_date' ";
		}
		if (! empty($conditions['dateto_flag']))
		{
			$convert_date = strtotime($conditions['dateto_flag']);
			$converted_date = date("Y-m-d", $convert_date);
			//$query->where($db->quoteName('test_date') . ' <= ' . $db->quote($converted_date));
			$WHERE_CLAUSE[] = " DATE(test_date) <= '$converted_date' ";
		}
		if (! empty($conditions['handlers_flag']))
		{
		$handle = $conditions['handlers_flag'] == '1'? 'fastcache':'file';
			//$query->where($db->quoteName('cache_handler') . ' = ' . $db->quote($handlers_flag));
			$WHERE_CLAUSE[] = " cache_handler = '$handle' ";
		}
		
		if (isset($conditions['testpages_flag']) && $conditions['testpages_flag'] !='')
		{
		$pages = $this->getDistpages();
		$page = $pages[$conditions['testpages_flag']];
			//$query->where($db->quoteName('test_page') . ' = ' . $db->quote($testpages_flag));
			$WHERE_CLAUSE[] = " test_page = '$page' ";
		}
		
		if (isset($conditions['precache_flag']))
		{
			//$precache_flag != ''
		
			//$query->where($db->quoteName('precache_factor') . ' = ' . $db->quote($precache_flag));
			$WHERE_CLAUSE[] = " precache_factor = '$conditions[precache_flag]' ";
		}
		
		if (isset($conditions['lz_factor_flag'] ))
		{
			$lz = $this->getDistlzoptions();
			$lzoption  = $lz[$conditions['lz_factor_flag']];
			$step_pre  = (float) $lzoption - 0.1 * (float) $ccomp_step;
			$step_post = (float) $lzoption + 0.1 * (float) $ccomp_step;
			//$query->where($db->quoteName('cache_compression_factor') . ' < ' . $db->quote($step_post));
			//$query->where($db->quoteName('cache_compression_factor') . ' > ' . $db->quote($step_pre));
			//$lz_factor_flag != ''
		
			//$query->where($db->quoteName('cache_compression_factor') . ' = ' . $db->quote($lz_factor_flag));
			$WHERE_CLAUSE[] = " cache_compression_factor < '$step_post' ";
			$WHERE_CLAUSE[] = " cache_compression_factor > '$step_pre' ";
		}
		if (isset($conditions['hammer_flag']) && $conditions['hammer_flag'] != '')
		{
			//$query->where($db->quoteName('hammer_mode') . ' = ' . $db->quote($hammer_flag));
			$WHERE_CLAUSE[] = " hammer_mode = '$conditions[hammer_flag]' ";
		}
		/*
		if (! isset($filters))
		{
			//$query->where($db->quoteName('status') . ' = ' . $db->quote('complete'));
			$WHERE_CLAUSE[] = " status = 'complete' ";
		}
		*/
		$where='';
		if(!empty($WHERE_CLAUSE))
		{
			$where = ' WHERE '.implode(' AND ', $WHERE_CLAUSE);
		}
		
		
		Return $where;
		
	}
	
	public function getglobalStat()
	{
	
		$sim_flag = $this->simflag;//$this->getState('filter.simflag');
		$complete_flag = $this->completeflag ;//$this->getState('filter.completeflag');
		$tolerance_flag = $this->toleranceflag;//$this->getState('filter.toleranceflag');
		$datefrom_flag = $this->datefrom ;//$this->getState('filter.datefrom');
		$dateto_flag = $this->dateto ;//$this->getState('filter.dateto');
		$handlers_flag = $this->handlersflag ;//$this->getState('filter.handlersflag');
		$testpages_flag = $this->testpagesflag ;// $this->getState('filter.testpagesflag');
		$precache_flag =$this->precacheflag ;// $this->getState('filter.precacheflag');
		$lz_factor_flag =$this->lzfactorflag;// $this->getState('filter.lzfactorflag');
		$hammer_flag = $this->hammerflag;// $this->getState('filter.hammerflag');
		$filters = ($sim_flag != '') || ($complete_flag != '') || ($datefrom_flag != '') || ($dateto_flag != '') || ($handlers_flag != '') || ($testpages_flag != '') || ($precache_flag != '') || ($lz_factor_flag != '') || ($hammer_flag != '') ? true : null;
		global $wpdb;
		$table = $wpdb->prefix.'multicache_advanced_test_results';
		$where = $this->makeWhereClause();
		$query = "SELECT AVG(page_load_time) As average_page_load_time,VARIANCE(page_load_time) As variance_page_load_time, MIN(page_load_time) as minimum_page_load_time,MAX(page_load_time) as maximum_page_load_time, STDDEV(page_load_time) as standarddeviation_page_load_time FROM $table ".$where;
	
		$statobj = $wpdb->get_row($query , OBJECT);
		//$db->setQuery($query);
		//$statobj = $db->loadObject();
	
		Return $statobj;
	
	}
	
	protected function getLastTestGroup()
	{
		global $wpdb;
		$table = $wpdb->prefix.'multicache_advanced_testgroups';
		$query = "SELECT * FROM $table ORDER BY id desc";
		Return $wpdb->get_row($query , OBJECT);
	/*
		$db = JFactory::getDBO();
		$query = $db->getQuery('true');
		$query->select('*');
		$query->from($db->quoteName('#__multicache_advanced_testgroups'));
		$query->order($db->quoteName('id') . '  DESC');
		$db->setQuery($query);
		Return $db->LoadObject();
		*/
	
	}
	
	protected function getTestsbyGroup($id)
	{
		if(!isset($id))
		{
			Return false;
		}
		global $wpdb;
		$table = $wpdb->prefix.'multicache_advanced_test_results';
		$query = "SELECT * FROM $table WHERE group_id = '$id'";
		Return $wpdb->get_results($query , OBJECT);
	/*
		$db = JFactory::getDBO();
		$query = $db->getQuery('true');
		$query->select('*');
		$query->from($db->quoteName('#__multicache_advanced_test_results'));
		$query->where($db->quoteName('group_id') . ' = ' . $db->quote($id));
	
		$query->order($db->quoteName('id') . '  DESC');
		$db->setQuery($query);
		Return $db->LoadObjectList();
		*/
	
	}
	
	public function getTestGroupStats()
	{
	
		//$app = JFactory::getApplication();
		$test_group = $this->getLastTestGroup();
		$multicacheconfig = get_option('multicache_config_options');//$this->getMulticacheConfig();
		$testgroup_stats = new stdClass();
		$testgroup_stats->cycles = $test_group->cycles;
		$testgroup_stats->cycles_complete = $test_group->cycles_complete;
		$testgroup_stats->expected_tests = $test_group->expected_tests;
		$testgroup_stats->advanced = $multicacheconfig['simulation_advanced'] ? 'advanced' : 'normal';
		$testgroup_stats->start_time = $test_group->start_time;
		$testgroup_stats->group_id = $test_group->id;
		$testgroup_stats->remaining_tests = $test_group->expected_tests; // init incase test info is not available
		$test_info = $this->getTestsbyGroup($testgroup_stats->group_id);
	
		if (empty($test_info))
		{
			$testgroup_stats->remaining_tests = __('Updating..','multicache-plugin');
			$testgroup_stats->testsperday =  __('Updating..','multicache-plugin');
			$testgroup_stats->expected_end_date =  __('Updating..','multicache-plugin');
			Return $testgroup_stats;
		}
		foreach ($test_info as $key => $test)
		{
	
			if ($test->status == 'complete')
			{
	
				$tests_complete[$key] = $test;
			}
		}
	
		if ($multicacheconfig['simulation_advanced'] != 1 && $test_group->advanced == 'advanced')
		{
			$min_precache = (int) $multicacheconfig['precache_factor_min'];
			$max_precache = (int) $multicacheconfig['precache_factor_max'];
			$min_cachecompression = (float) $multicacheconfig['ccomp_factor_min']; // $min_gzip -> $min_cachecompression
			$max_cachecompression = (float) $multicacheconfig['ccomp_factor_max']; // $max_gzip -> $max_cachecompression
			$step_cachecompression = (float) $multicacheconfig['ccomp_factor_step']; // $step_gzip -> $step_cachecompression
	
			$precache_sequences = ($max_precache - $min_precache) + 1;
			$step_cachecompression = empty($step_cachecompression) ? 1 : $step_cachecompression; // filtering the input for 0
			$cachecompression_sequences = (int) (($max_cachecompression - $min_cachecompression) / $step_cachecompression);
			$cachecompression_sequences = ($cachecompression_sequences <= 1) ? 1 : $cachecompression_sequences;
	
			$testgroup_stats->remaining_tests = $cachecompression_sequences * $precache_sequences * $multicacheconfig['gtmetrix_cycles'];
			$testgroup_stats->testsperday = $multicacheconfig['gtmetrix_api_budget'];
			if ($testgroup_stats->testsperday)
			{
				$expectedendtime = microtime(true) + ($testgroup_stats->remaining_tests / $testgroup_stats->testsperday) * 24 * 60 * 60;
				$testgroup_stats->expected_end_date = date("l jS F ", $expectedendtime);
			}
			else
			{
				$testgroup_stats->expected_end_date = 'na';
			}
	
			Return $testgroup_stats;
		}
	
		if ($multicacheconfig['simulation_advanced'] == 1 && $test_group->advanced == 'normal')
		{
	
			if (class_exists('MulticacheLoadInstruction') && property_exists('MulticacheLoadInstruction', 'loadinstruction'))
			{
				$min_precache = (int) $multicacheconfig['precache_factor_min'];
				$max_precache = (int) $multicacheconfig['precache_factor_max'];
				$min_cachecompression = (float) $multicacheconfig['ccomp_factor_min']; // $min_gzip -> $min_cachecompression
				$max_cachecompression = (float) $multicacheconfig['ccomp_factor_max']; // $max_gzip -> $max_cachecompression
				$step_cachecompression = (float) $multicacheconfig['ccomp_factor_step']; // $step_gzip -> $step_cachecompression
	
				$precache_sequences = ($max_precache - $min_precache) + 1;
				$step_cachecompression = empty($step_cachecompression) ? 1 : $step_cachecompression; // filtering the input for 0
				$cachecompression_sequences = (int) (($max_cachecompression - $min_cachecompression) / $step_cachecompression);
				$cachecompression_sequences = ($cachecompression_sequences <= 1) ? 1 : $cachecompression_sequences;
				$load_states = empty(MulticacheLoadinstruction::$loadinstruction) ? 1 : count(MulticacheLoadinstruction::$loadinstruction);
				$testgroup_stats->testsperday = $multicacheconfig['gtmetrix_api_budget'];
	
				$testgroup_stats->remaining_tests = $cachecompression_sequences * $precache_sequences * $multicacheconfig['gtmetrix_cycles'] * $load_states;
				if ($testgroup_stats->testsperday)
				{
					$expectedendtime = microtime(true) + ($testgroup_stats->remaining_tests / $testgroup_stats->testsperday) * 24 * 60 * 60;
					$testgroup_stats->expected_end_date = date("l jS F ", $expectedendtime);
				}
				else
				{
					$testgroup_stats->expected_end_date = 'na';
				}
	
				Return $testgroup_stats;
			}
			$testgroup_stats->remaining_tests = __('Updating..','multicache-plugin');
			$testgroup_stats->testsperday =  __('Updating..','multicache-plugin');
			$testgroup_stats->expected_end_date =  __('Updating..','multicache-plugin');
			Return $testgroup_stats;
		}
		$testgroup_stats->remaining_tests = $test_group->expected_tests - count($tests_complete);
	
		$testgroup_stats->testsperday = $test_info[0]->max_tests;
		if ($testgroup_stats->remaining_tests)
		{
			$expectedendtime = microtime(true) + ($testgroup_stats->remaining_tests / $testgroup_stats->testsperday) * 24 * 60 * 60;
			$testgroup_stats->expected_end_date = date("l jS F ", $expectedendtime);
		}
		else
		{
			$testgroup_stats->expected_end_date = 'na';
		}
	
		Return $testgroup_stats;
	
	}
}