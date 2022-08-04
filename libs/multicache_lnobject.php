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

require_once plugin_dir_path(__FILE__) . 'multicache_oauth_client.php';
require_once plugin_dir_path(__FILE__) . 'multicache_storage_temp.php';

class MulticacheLnObject
{
	
	protected $_data = array();
	protected $_total = null;
	protected $code = null;
	protected $forceauth = null; 
	protected $_pagination = null;
	protected static $instance = null;
	protected static $options = null;
	protected static $storage = null;
	
	protected $expiration = 3600;
	//protected $googleclientid = null;
	
	public function __construct($data = null)
	{
		if(empty($data))
		{
		$this->_data = new stdClass();
		$this->_data = $this->setGoogleData();
		}
		else
		{
			$this->_data = $data;
		}
		
		$this->code = isset($_REQUEST['code'])? MulticacheHelper::validate_string($_REQUEST['code']) : null;
		$this->forceauth = isset($_REQUEST['forceauth'])? true :null;
		
	}
	
	
	public static function getInstance()
	{
		// Only create the object if it doesn't exist.
		if (empty(self::$instance))
		{
	
			self::$instance = new MulticacheLnObject();
		}
		return self::$instance;
	
	}
	
	public function getGoogleAuth()
	{
	
		//$session = JFactory::getSession(); WP is inherently stateless lets keep it that way
		
		//$app = JFactory::getApplication(); is this required?
	
		$API_URL = 'https://www.googleapis.com/analytics/v3/data/ga';//move this to a settable
	
		/* The form vars are absent during the google redirect hence this method is unreliable . Using sessions to add redundancy further use of db is to be ascertained as redundancy database support */
	/*
		$jfinput = $app->input->post->__call('getfilter', array(
				0 => 'jform',
				1 => null
		));
	
		if (! isset($jfinput))
		{
			$jfinput = $app->input->__call('getfilter', array(
					0 => 'jform',
					1 => null
			));
		}
	*/
		$debug = true;
		if (isset($_REQUEST['multicache_config_options']['googleclientid']) && isset($_REQUEST['multicache_config_options']['googleclientsecret']) && isset($_REQUEST['multicache_config_options']['googleviewid']))
		{
			$sess_lnparam = $this->_data;
			$this->transient_set('multicache_lnparam', $sess_lnparam);
			if($debug)
			{
				$transient = $this->transient_get('multicache_lnparam');
				MulticacheHelper::log_error('Saving to session equivalent- retreived post save','lnparams_oauth_cycle',$transient);
			}
		}
		else 
		{
			$session_lnparam = $this->transient_get('multicache_lnparam');
			if(isset($session_lnparam))
			{
				$this->_data =  $session_lnparam;//overides construct in second entry
				if($debug)
				{
					MulticacheHelper::log_error('Retreival of Saved session in else','lnparams_oauth_cycle_else',$session_lnparam);
				}
			}
		}
		/*
		if (isset($jfinput['googleclientid']) && isset($jfinput['googleclientsecret']) && isset($jfinput['googleviewid']))
		{
			$jinput = new stdClass();
			foreach ($jfinput as $key => $value)
			{
				$jinput->$key = $value;
			}
			$sess_lnparam = serialize($jinput);
			$session->set('multicache_lnparam', $sess_lnparam);
			// var_dump($sess_lnparam);exit;
		}
		else
		{
			// check the sessions
			$session_lnparam = $session->get('multicache_lnparam');
	
			if (isset($session_lnparam))
			{
	
				$jinput = unserialize($session_lnparam);
			}
		}
		*/
	
		/*
		 * else
		 	* {
		 	* $jinput = $this->getlnparams();//database redundant
		 	* }
		 *
		 */
	
		 $client_id = $this->_data->googleclientid;
	
		 $client_secret = $this->_data->googleclientsecret;
		 $account_id = $this->_data->googleviewid;
		 if (empty($client_id) || empty($client_secret) || empty($account_id) || $account_id == 'ga:')
		 {
		 	$credentails[] = empty($client_id) ? strtolower(__('client id' , 'multicache-plugin')) : '';
		 	$credentails[] = empty($client_secret) ? strtolower(__('client secret' ,'multicache-plugin')) : '';
		 	$credentails[] = empty($account_id) || $account_id == 'ga:' ? strtolower(__('view id','multicache-plugin')) : '';
	
		 	if (! empty($credentails))
		 	{
		 		$credentails = array_filter($credentails);
		 		$credentails = implode(',	', $credentails);
		 	}
		 	
		 	multicacheHelper::prepareMessageEnqueue(__('missing authentication credentials '.$credentails,'multicache-plugin'));
	
		 	wp_redirect(admin_url().'admin.php?page=multicache-config-menu#page-parta');
		 }
		 $startdate = $this->_data->googlestartdate;
		 $enddate = $this->_data->googleenddate;
		 $maxresults = $this->_data->googlenumberurlscache;
	//we need to work on this
		 $redirect_uri = admin_url().'admin.php?page=multicache-config-menu&authg=2';
		 $this->urlfilter = (int) $this->_data->urlfilters;
		 $code = $this->code;
		 $forceauth = $this->forceauth;
		 $authobj = new MulticacheOauthClient();
		 $authobj->setOption('authurl', 'https://accounts.google.com/o/oauth2/auth');
		 $authobj->setOption('tokenurl', 'https://accounts.google.com/o/oauth2/token');
		 $authobj->setOption('clientid', $client_id);
		 $authobj->setOption('clientsecret', $client_secret);
		 // $authobj->setOption('clientsecret', $account_id);
		 $authobj->setOption('scope', array(
		 		'https://www.googleapis.com/auth/analytics.readonly'
		 ));
		 $authobj->setOption('redirecturi', $redirect_uri);
		 $authobj->setOption('requestparams', array(
		 		'access_type' => 'offline',
		 		'approval_prompt' => 'auto'
		 ));
		 $authobj->setOption('sendheaders', true);
		 $authobj->setOption('userefresh', true);
		 // session_start();
	
		 if (isset($forceauth)) 
		 {
		 	$this->transient_set('googleoauth_access_code' , null);
		 	if($debug)
		 	{
		 		MulticacheHelper::log_error('force auth implemented','forceauth');
		 	}
		 	//$session->set('googleoauth_access_code', null);	
		 }
		 $session_google = $this->transient_get('googleoauth_access_code');//$session->get('googleoauth_access_code');
		// $session_google = unserialize($session_google_t);
		if($debug)
		{
			MulticacheHelper::log_error('Session Google initial retreival','session_google_1_lnobject',$session_google);
			MulticacheHelper::log_error('Session Google Is empty?','session_google_1_lnobject',empty($session_google));
		}
		 if(!empty($session_google))
		 {
		 	
		 $token_created = $session_google['created'];
		 $token_expires = $session_google['expires_in'];
		 if($debug)
		 {
		 	MulticacheHelper::log_error('Session Google not empty','session_google_1_lnobject');
		 	MulticacheHelper::log_error('Session Google token created','session_google_1_lnobject',$token_created);
		 	MulticacheHelper::log_error('Session Google token expires','session_google_1_lnobject',$token_expires);
		 	MulticacheHelper::log_error('Session Google access token','session_google_1_lnobject',$session_google["access_token"]);
		 }
		 }
		 $now = microtime(true);
		 if (empty($session_google) || !empty($session_google) && ($now > ($token_created + $token_expires)))
		 {
		 	$this->transient_set('googleoauth_access_code',null);
		 	//$session->set('googleoauth_access_code', null);
		 	try
		 	{
		 	$result = $access_token = $authobj->authenticate();
		 	$this->transient_set('googleoauth_access_code' , $access_token);
		 	$e_resp = $result->response->code;
		 	}
		 	catch(RuntimeException $e)
		 	{
		 		echo 'Message: ' .$e->getMessage();
		 		$e_resp  = $e->getCode();
		 	}
		 	
		 	//$session->set('googleoauth_access_code', $access_token);
		 	// $_SESSION['oauth_access_token'] = $access_token;
		 	if($debug)
		 	{
		 		$atr = $this->transient_get('googleoauth_access_code');
		 		MulticacheHelper::log_error('Method Authenticate: Access Token','lnpara_oauth_access_token_firstauth',$access_token);
		 		MulticacheHelper::log_error('Method Authenticate: Access Token retreived','lnpara_oauth_access_token_firstauth',$atr);
		 		if(isset($e_resp))
		 		{
		 			MulticacheHelper::log_error('Error Method Authenticate '.$e->getMessage().' error code -'.$e_resp,'lnpara_oauth_access_token_firstauth',$e);
		 		}
		 		
		 	}
		 }
		 else
		 {
		 	$authobj->setToken($this->transient_get('googleoauth_access_code'));
		 	if($debug)
		 	{
		 		$atr = $this->transient_get('googleoauth_access_code');
		 		MulticacheHelper::log_error('Else Condition authentication Access Token','lnparam_oauth_access_token_firstauth',$atr);
		  	}
		 	//$authobj->setToken($session->get('googleoauth_access_code'));
		 }
	
		 if (! $authobj->isAuthenticated())
		 {
		 	$token = $authobj->getToken();
		 	$token_created = $session_google['created'];
		 	$token_expires = $session_google['expires_in'];
		 	$now = microtime(true);
		 	
		 	if ($now > ($token_created + $token_expires))
		 	{
		 		$t = $now - ($token_created + $token_expires);
		 	    $e_message = 	__('COM_MULTICACHE_LNOBJECT_ERROR_TOKEN_EXPIRED','multicache-plugin') . '	' . $t . ' ' . __('COM_MULTICACHE_LNOBJECT_ERROR_DESC_SECONDS_AGO');
		 		MulticacheHelper::log_error('Authobj is not authenticated'.$e_message,'lnobject_notauthenticated_tokenexpirycheck');
		 	}
		 	if($debug)
		 	{
		 		MulticacheHelper::log_error('Authobj is not authenticated','lnobject_notauthenticated_tokenexpirycheck');
		 		MulticacheHelper::log_error('Authobj token created-expired-now-bool ','lnobject_notauthenticated_tokenexpirycheck',array($token_created,$token_expires,$now,(bool)$now > ($token_created + $token_expires)));
		 	}
		 	Return false;
		 }
	
		 if ($authobj->isAuthenticated())
		 {
	
		 	$access_token = $authobj->getToken();
		 	$params = array(
		 			'ids' => $account_id,
		 			'metrics' => 'ga:pageviews',
		 			'dimensions' => 'ga:pagePath',
		 			'sort' => '-ga:pageviews',
		 			'start-date' => $this->_data->googlestartdate,
		 			'end-date' => $this->_data->googleenddate,
		 			'max-results' => $this->_data->googlenumberurlscache,
		 			'access_token' => $access_token[access_token]
		 	);
	
		 	$authobj->setOption('getparam', http_build_query($params));
		 	$authobj->setOption('authmethod', 'get');
	if($debug){
		MulticacheHelper::log_error('Authobj is authenticated loop access token','lnparam_authisauthenticated',$access_token);
		MulticacheHelper::log_error('Authobj is authenticated loop params','lnparam_authisauthenticated',$params);
		MulticacheHelper::log_error('Authobj is authenticated authobj','lnparam_authisauthenticated',$authobj);
	}
	try
	{	 	
	$result = $authobj->query($API_URL, $params, array(), 'get');
	$e_resp = $result->response->code;
	}
	catch (RuntimeException $e)
	{
		//echo 'Message: ' .$e->getMessage();
		MulticacheHelper::prepareMessageEnqueue('Message: ' .$e->getMessage() );
		$e_resp  = $e->getCode();
	}
		 	if($debug)
		 	{
		 		
		 		
		 		MulticacheHelper::log_error('Authobj is authenticated loop $ e_resp','lnparam_authisauthenticated',$e_resp);
		 	}
	
		 	if ($e_resp != 200)
		 	{
		 		 		
		 			//$trans_name = 'multicache_auth_'.get_current_user_id();
		 			//delete_transient($trans_name);
		 			/*
		 			$session->clear('googleoauth_access_code');
		 			$session->set('googleoauth_access_code', null);
		 			$session->set('multicache_lnparam', null);
		 			$session->clear('multicache_lnparam');
		 			$app->close();
		 			*/
		 			// $this->setRedirect(JRoute::_('index.php?option=com_multicache&view=lnobject&code=0'));
		 			if($debug)
		 			{
		 				
		 				MulticacheHelper::log_error('Fatal error returning','lnparam_authisauthenticated',$e);
		 			}
		 		
		 		return;
		 	}
		 	
		 	$decoded_result = json_decode($result->body, true);
		 	
		 	if($debug && $this->_data->googlenumberurlscache <= 200)
		 	{
		 		MulticacheHelper::log_error('Success furnishing results','lnparam_authisauthenticated',$decoded_result);
		 	}
		 	elseif($debug)
		 	{
		 		MulticacheHelper::log_error('Success furnishing results not dumping trace as expected urls ='.$this->_data->googlenumberurlscache,'lnparam_authisauthenticated');
		 	}
		 	
		 	// it is important to derive the root from Jroute over JURI to keep case sensitivity in cases wher live-site settings differ
		 	// $siteroot = substr(JURI::root(), 0, -1);
		 	$siteroot = MulticacheUri::getInstance()->toString(array(
		 			"scheme",
		 			"host"
		 	));
	
		 	if (! stristr($siteroot, 'www.'))
		 	{
		 		$s_uri = MulticacheUri::getInstance();
		 		$siteroot2 = $s_uri->getScheme() . '://www.' . $s_uri->getHost();
		 	}
		 	else
		 	{
		 		$siteroot2 = str_ireplace('www.', '', $siteroot);
		 	}
		 	$rawurlarrayobj = array();
		 	if(empty($decoded_result))
		 	{
		 		if($debug)
		 		{
		 			MulticacheHelper::log_error(__('decoded object was empty - returning','multicache-plugin'),'lnparam_authisauthenticated',$decoded_result);
		 		}
		 		Return false;
		 	}
		 	foreach ($decoded_result['rows'] as $lobj)
		 	{
		 		$key = $siteroot . $lobj[0];
		 		$key2 = $siteroot2 . $lobj[0];
		 		$value = $lobj[1];
		 		if (isset($rawurlarrayobj[$key]))
		 		{
		 			$rawurlarrayobj[$key] = $rawurlarrayobj[$key] + $value;
		 		}
		 		else
		 		{
		 			$rawurlarrayobj[$key] = $value;
		 		}
	
		 		if (isset($rawurlarrayobj[$key2]))
		 		{
		 			$rawurlarrayobj[$key2] = $rawurlarrayobj[$key2] + $value;
		 		}
		 		else
		 		{
		 			$rawurlarrayobj[$key2] = $value;
		 		}
		 	}
		 	
		 	$urlobj = array();
	
		 	if ($this->_data->urlfilter == 1)
		 	{
	
		 		foreach ($rawurlarrayobj as $key => $value)
		 		{
		 			$newkey = strstr($key, '?', true);
		 			if ($newkey)
		 			{
	
		 				// check whether the key is already present in the object
		 				if (isset($urlobj[$newkey]))
		 				{
	
		 					$urlobj[$newkey] = $urlobj[$newkey] + $value;
		 				}
		 				else
		 				{
	
		 					$urlobj[$newkey] = $value;
		 				}
		 			}
		 			else
		 			{
	
		 				$urlobj[$key] = $value;
		 			}
		 		}
		 	}
		 	elseif ($this->_data->urlfilter == 2)
		 	{
		 		foreach ($rawurlarrayobj as $key => $value)
		 		{
		 			$newkey = strstr($key, '?', true);
		 			if (empty($newkey))
		 			{
		 				$urlobj[$key] = $value;
		 			}
		 		}
		 	}
		 	else
		 	{
		 		$urlobj = $rawurlarrayobj;
		 	}
	
		 	arsort($urlobj);
		 	$count = count($urlobj);
		 	$this->insertUarray($urlobj, $this->_data);
		 	// $message = "Google Authentication Success. ".$count." urls retrieved and replaced! ";
		 	$message = sprintf(__("COM_MULTICACHE_GOOGLE_AUTHENTICATION_SUCCESS", 'multicache-plugin'), $count);
		 	//$app->redirect('index.php?option=com_multicache&view=config&layout=edit&id=1#page-parta', $message, 'message');
		 	Return $count;
		 }
		 else
		 {
		 	$e_message = __('COM_MULTICACHE_LNOBJECT_NOTAUTHENTICATED','multicache-plugin');
		 	MulticacheHelper::log_error($e_message,'lnparam_authisauthenticated',$access_token);
		 	Return false;
		 }
	
	}
	
	protected function insertUarray($uarray, $params = NULL, $type = 'google')
    {

        if (isset($params->frequency_distribution))
        {
            $total_views = array_sum($uarray);
        }
        global $wpdb;
        $table = $wpdb->prefix.'multicache_urlarray';
        $where = array('type' => $type);
        $where_format = array('%s');
        $wpdb->delete( $table, $where, $where_format = null );
        /*
        $db = JFactory::getDbo();
        $query = $db->getQuery('true');
        $query->delete($db->quoteName('#__multicache_urlarray'));
        $query->where($db->quoteName('type') . ' = ' . $db->quote($type));
        $db->setQuery($query);
        $db->execute();
        */        
        // delete current goog objects
        $format = array('%s','%s','%s','%s','%d','%s','%s');
        foreach ($uarray as $key => $value)
        {
            $insertobj = array();
            $insertobj['url'] = $key;
            $insertobj['url_manifest'] = $key;
            $insertobj['cache_id'] = $this->getCacheid($key, 'page');
            $insertobj['cache_id_alt'] = $this->getCacheidAlt($key, 'page');
            $insertobj['views'] = $value;
            $insertobj['type'] = $type;
            $insertobj['created'] = date('Y-m-d');
            if (isset($params->frequency_distribution))
            {
                $insertobj['f_dist'] = $value / $total_views;
                $format[] = '%d';
            }
            if (isset($params->natlogdist))
            {
                $insertobj['ln_dist'] = log($value);
                $format[] = '%d';
            }
            $res = $wpdb->insert( $table, $insertobj, $format );
            //$res = $db->insertObject('#__multicache_urlarray', $insertobj);
        }
    
    }
	
	protected function setGoogleData()	{
		$data = new stdClass();
		if (isset($_REQUEST['multicache_config_options']['googleclientid']))
		{
			$data->googleclientid = MulticacheHelper::validate_google($_REQUEST['multicache_config_options']['googleclientid']) ;
		}
		else
		{
			$option = $this->getOptionsBase();
			$data->googleclientid = $option['googleclientid'];
		}
	
		if (isset($_REQUEST['multicache_config_options']['googleclientsecret']))
		{
			$data->googleclientsecret = MulticacheHelper::validate_google($_REQUEST['multicache_config_options']['googleclientsecret']) ;
		}
		else
		{
			$option = $this->getOptionsBase();
			$data->googleclientsecret = $option['googleclientsecret'];
			
		}
	
		if (isset($_REQUEST['multicache_config_options']['googleviewid']))
		{
			$data->googleviewid = MulticacheHelper::validate_google($_REQUEST['multicache_config_options']['googleviewid']) ;
			if(preg_match('~^[0-9]+$~', $data->googleviewid))
			{
				$data->googleviewid = 'ga:'.$data->googleviewid;
			}
		}
		else
		{
			$option = $this->getOptionsBase();
			$data->googleviewid = $option['googleviewid'];
		}
	
		if (isset($_REQUEST['multicache_config_options']['googlestartdate']))
		{
			$data->googlestartdate = MulticacheHelper::validate_string($_REQUEST['multicache_config_options']['googlestartdate']) ;
		}
		else
		{
			$option = $this->getOptionsBase();
			$data->googlestartdate = $option['googlestartdate'];
		}
	
		if (isset($_REQUEST['multicache_config_options']['googleenddate']))
		{
			$data->googleenddate = MulticacheHelper::validate_string($_REQUEST['multicache_config_options']['googleenddate']) ;
		}
		else
		{
			$option = $this->getOptionsBase();
			$data->googleenddate = $option['googleenddate'];
		}
	
		if (isset($_REQUEST['multicache_config_options']['googlenumberurlscache']))
		{
			$data->googlenumberurlscache = MulticacheHelper::validate_num($_REQUEST['multicache_config_options']['googlenumberurlscache']) ;
		}
		else
		{
			$option = $this->getOptionsBase();
			$data->googlenumberurlscache = $option['googlenumberurlscache'];
		}
	
		if (isset($_REQUEST['multicache_config_options']['urlfilters']))
		{
			$data->urlfilter = MulticacheHelper::validate_num($_REQUEST['multicache_config_options']['urlfilters']) ;
		}
		else
		{
			$option = $this->getOptionsBase();
			$data->urlfilter = $option['urlfilters'];
		}
	
		if (isset($_REQUEST['multicache_config_options']['frequency_distribution']))
		{
			$data->frequency_distribution = MulticacheHelper::validate_num($_REQUEST['multicache_config_options']['frequency_distribution']) ;
		}
		else
		{
			$option = $this->getOptionsBase();
			$data->frequency_distribution = $option['frequency_distribution'];
		}
		
		
		if (isset($_REQUEST['multicache_config_options']['natlogdist']))
		{
			$data->natlogdist = MulticacheHelper::validate_num($_REQUEST['multicache_config_options']['natlogdist']) ;
		}
		else
		{
			$option = $this->getOptionsBase();
			$data->natlogdist = $option['natlogdist'];
		}
		Return $data;
	}
	
	protected function getOptionsBase()
	{
		if(!isset(self::$options))
		{
			self::$options = get_option('multicache_config_options');
		}
	
		Return self::$options;
	}
	
	protected function transient_set($name , $value)
	{
		/*
		 * NB: An add action is placed presumably to detroy transients(by uid) whether created or no
		 */
		
		$trans_name = 'multicache_auth_'.get_current_user_id();
		$transient = get_transient($trans_name);//php 5.3 compatible
		if(empty($transient))
		{
		$transient = serialize(array($name => $value));
		set_transient($trans_name,$transient,$this->expiration);
		}
		else
		{
		$transient = unserialize($transient);
		$transient[$name] = $value;	
		$transient = serialize($transient);
		set_transient($trans_name,$transient,$this->expiration);
		}
			
	}
	
	protected function transient_get($name )
	{
		$trans_name = 'multicache_auth_'.get_current_user_id();
		$transient = get_transient($trans_name);
		$transient = unserialize($transient);
		Return $transient[$name];		
	}
	
	protected function transient_destroy($name)
	{
		$trans_name = 'multicache_auth_'.get_current_user_id();
		$transient = get_transient($trans_name);
		unset($transient[$name]);
		set_transient($trans_name,$transient,$this->expiration);
	}
	
	protected function getCacheid($key , $type)
	{
		if(!isset(self::$storage))
		{
			self::$storage = new MulticacheStoragetemp();
		}
		
		Return self::$storage->getCacheid($key, $type);
		
	}
	
	protected function getCacheidAlt($key , $type)
	{
	if(!isset(self::$storage))
		{
			self::$storage = new MulticacheStoragetemp();
		}
		$cacheidalt = self::$storage->getCacheidAlternate($key, $type);
		Return $cacheidalt['alternate'];
	}
}