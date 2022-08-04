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

class MulticacheTemplater
{
	
	protected static $instance = null;
	protected $social_script_identifiers = null;
	protected $advertisement_script_identifiers = null;
	protected $css_special_identifiers = null;
    protected static $_scraped_page_content = null;
    protected static $_css_scraped_page_content = null;

public function __construct()
{
	// If a input object is given use it.
	if (!isset($this->social_script_identifiers))
	{
		$this->social_script_identifiers = $this->getSocialScriptIndentifiers();
	}
	if(!isset($this->advertisement_script_identifiers))
	{
		$this->advertisement_script_identifiers = $this->getAdvertisementIdentifiers();
		
	}
	if(!isset($this->css_special_identifiers))
	{
		$this->css_special_identifiers = $this->getCssSpecialIdentifiers();
	
	}

	
}
protected  function getSocialScriptIndentifiers()
{
	$s_indicators = $_REQUEST['multicache_config_options']['social_script_identifiers'];//php ver 5.3 support
	if(!empty($s_indicators))
	{
		$social = MulticacheHelper::processSocialAdIndicators($s_indicators);
	}
	else {
		$options = get_option('multicache_config_options');
		$social = $options['social_script_identifiers'];
	}
	Return $social;
}

protected function getAdvertisementIdentifiers()
{
	$s_indicators = $_REQUEST['multicache_config_options']['advertisement_script_identifiers'];//php 5.3 support
	if(!empty($s_indicators))
	{
		$advertisement = MulticacheHelper::processSocialAdIndicators($s_indicators);
	}
	else {
		$options = get_option('multicache_config_options');
		$advertisement = $options['advertisement_script_identifiers'];
	}
	Return $advertisement;
	
}

protected function getCssSpecialIdentifiers()
{
	$s_indicators = $_REQUEST['multicache_config_options']['css_special_identifiers']; //php ver 5.3 support
	if(!empty($s_indicators))
	{
		$css_special_identifiers = MulticacheHelper::processSocialAdIndicators($s_indicators);
	}
	else {
		$options = get_option('multicache_config_options');
		$css_special_identifiers = $options['css_special_identifiers'];
	}
	Return $css_special_identifiers;
}

public static function getInstance()
{
	// Only create the object if it doesn't exist.
	if (empty(self::$instance))
	{

		self::$instance = new MulticacheTemplater();
	}
	return self::$instance;

}
public function scrapeJavascript($url)
{
	
	
	if(empty($url))
	{
		MulticacheHelper::log_error('Scrape Javascript url not set' , 'multicache_templater_errors');
		Return false;
	}
	$uri = MulticacheUri::getInstance($url);
	$uri->setVar('multicachetask', MulticacheHelper::getMediaFormat());
	$c_url = MulticacheHelper::checkCurlable($uri->toString());
	$scraped_page = MulticacheHelper::get_web_page($c_url);
	
	if ($scraped_page["http_code"] != 200)
	{
		MulticacheHelper::log_error('ScrapeJavascript:SCRAPE URL NOTRETREIVED' , 'multicache_templater_errors');
		Return false;
	}
	if (strpos($scraped_page["content"], 'Loaded by MulticachePlugin') !== false )
	{
		MulticacheHelper::log_error('ScrapeJavascript:ERROR_SCRAPE_PAGE_LOOP' , 'multicache_templater_errors');
		Return false;
		
	}
	self::$_scraped_page_content = $scraped_page["content"];
	$jsArray = $this->getAllScripts();
	$jsArray = $this->setSocialIndicators($jsArray);
	$jsArray = $this->setAdvertisementIndicators($jsArray);
	$success = MulticacheHelper::storePageScripts($jsArray);
	Return $success;
/*
	$app = MulticacheFactory::getApplication();
	$jinput = $app->input->post->__call('getfilter', array(
			0 => 'jform',
			1 => null
	));
	if (! empty($jinput["default_scrape_url"]))
	{
		$this->scrape_url = $jinput["default_scrape_url"];
	}
	else
	{
		$jinput = $app->input->__call('getfilter', array(
				0 => 'jform',
				1 => null
		));
		$this->scrape_url = ! empty($jinput["default_scrape_url"]) ? $jinput["default_scrape_url"] : null;
	}
	if ($this->getParam(0)->default_scrape_url != $this->scrape_url)
	{
		// lets set a session var
		$session = JFactory::getSession();
		$sess_dsu = serialize($this->scrape_url);
		$session->set('multicache_scrape_url_default', $sess_dsu);
	}

	if (empty($this->scrape_url))
	{
		$this->scrape_url = $this->getParam(0)->default_scrape_url;
	}

	if (empty($this->scrape_url))
	{

		$app->enqueueMessage(JText::_('COM_MULTICACHE_ERROR_SCRAPE_URL_NOTSET'), 'warning');
		Return false;
	}
	$uri = JURI::getInstance($this->scrape_url);
	$uri->setVar('multicachetask', MulticacheHelper::getMediaFormat());

	$this->scraped_page = MulticacheHelper::get_web_page($uri->toString());
	if ($this->scraped_page["http_code"] == 200)
	{
	}
	else
	{
		$app->enqueueMessage(JText::_('COM_MULTICACHE_ERROR_SCRAPE_URL_NOTRETREIVED') . $this->scraped_page["http_code"], 'notice');
		Return false;
	}
	if (strstr($this->scraped_page["content"], 'Loaded by MulticachePlugin') || strstr($this->scraped_page["content"], '
/administrator/components/com_multicache/'))
	{
		$app->enqueueMessage(JText::_('COM_MULTICACHE_ERROR_SCRAPE_PAGE_LOOP'), 'error');
		Return false;
	}
	self::$_scraped_page_content = $this->scraped_page["content"];
	$this->jsArray = $this->getAllScripts();
	$this->jsArray = $this->setSocialIndicators($this->jsArray);
	$this->jsArray = $this->setAdvertisementIndicators($this->jsArray);

	$success = MulticacheHelper::storePageScripts($this->jsArray);

	Return $success;
	*/

}

public function scrapeCss($url)
{

	if(empty($url))
	{
		
		MulticacheHelper::log_error('Scrape CSS error url not set' , 'multicache_templater_errors');
		Return false;
	}
	$uri = MulticacheUri::getInstance($url);
	$uri->setVar('multicachetask', MulticacheHelper::getMediaFormat());
	$c_url = MulticacheHelper::checkCurlable($uri->toString());
	$scraped_page = MulticacheHelper::get_web_page($c_url);
	
	if ($scraped_page["http_code"] != 200)
	{
		MulticacheHelper::log_error('ScrapeCss:SCRAPE URL NOTRETREIVED' , 'multicache_templater_errors');
		Return false;
	}
	if (strpos($scraped_page["content"], 'Loaded by MulticachePlugin') !== false )
	{
		MulticacheHelper::log_error( 'ScrapeCss:ERROR_SCRAPE_PAGE_LOOP', 'multicache_templater_errors');
		Return false;
	
	}
	self::$_css_scraped_page_content = $scraped_page["content"];
	$cssArray = $this->getAllCss();
	//$cssArray = $this->setSocialIndicators($cssArray);
	//$cssArray = $this->setAdvertisementIndicators($cssArray);
	$success = MulticacheHelper::storePageCss($cssArray);
	Return $success;
}

protected function getAllScripts()
{

	$page_body = self::$_scraped_page_content;
	$search = '~(?><?[^<]*+(?:<!--(?>-?[^-]*+)*?-->)?)*?\\K(?:(?:<script(?= (?> [^\\s>]*+[\\s] (?(?=type= )type=["\']?(?:text|application)/javascript ) )*+ [^\\s>]*+> )(?:(?> [^\\s>]*+\\s )+? (?>src)=["\']?( (?<!["\']) [^\\s>]*+| (?<!\') [^"]*+ | [^\']*+ ))?[^>]*+>( (?> <?[^<]*+ )*? )</script>)|\\K$)~six';
	preg_match_all($search, $page_body, $matches);
	$jsarray = array();
	foreach ($matches[0] as $key => $match)
	{
		if (empty($match))
		{
			continue;
		}
		$jscode = new stdClass();
		// $source_link = $this->findSource($match, $key);
		$source_link = ! empty($matches[1][$key]) ? $matches[1][$key] : '';
		$source_link_clean = null; // initiating source link
		$source_link_cmp = strtolower($source_link);
		// removing query and fragments from the source url

		// we cant do a string to lower here. If the source has a link in uppercase a 404 will be issued
		// lets make a comparitive
		// $code = $this->findCode($match, $key);
		$code = ! empty($matches[2][$key]) ? $matches[2][$key] : '';
		$async = self::findAsync($match, $key);
		$absolute_link = null;
		/*
		 * store contents of the script to implement remove, replace or alias methods
		 *
		 */
		$serialized = serialize($match);
		$serialized_code = (bool) $code ? serialize($code) : NULL;
/*
		if (strstr($source_link_cmp, 'com_multicache/assets/js/conduit'))
		{
			// continue;//only required for hard coded conduit
		}
		*/
		if (! empty($source_link_cmp) && strpos($source_link_cmp, '//') === 0)
		{
			$host = MulticacheUri::getInstance()->getHost();
			// check whether the uri contains the host name
			if (! strstr(strtolower($source_link_cmp), strtolower($host)))
			{
				$internal = false;
			}
			elseif (strpos(strtolower($source_link_cmp), strtolower(host)) === 2)
			{
				$internal = true;
			}
			else
			{
				// we treat all remainder urls as external. This will avoid grouping in doubtful cases
				$internal = false;
			}
		}
		elseif (! empty($source_link_cmp))
		{

			$internal = MulticacheUri::isInternal($source_link);
		}
		else
		{
			$internal = null;
		}
		// moderator for certain cases
		if ($internal === false && ! empty($source_link_cmp) && strpos(strtolower($source_link_cmp), strtolower(substr(MulticacheUri::root(), 0, - 1))) === 0)
		{
			$internal = true;
		}
		// additional checks for internal
		// type1:https://domain.com
		// type2://domain.com
		if (! $internal)
		{
			$type1 = 'https://' . MulticacheUri::getInstance()->getHost();
			$type2 = '//' . MulticacheUri::getInstance()->getHost();
			$type3 = 'http://' . MulticacheUri::getInstance()->getHost();
			if (strpos($source_link_cmp, $type1) === 0 || strpos($source_link_cmp, $type2) === 0 || strpos($source_link_cmp, $type3) === 0)
			{
				$internal = true;
			}
		}
		// end additional checks for internal

		if ($internal)
		{
			$host = MulticacheUri::getInstance()->getHost();
			$scheme = MulticacheUri::getInstance()->getScheme();
			$uri_object = MulticacheUri::getInstance($source_link);
			$source_uri = $uri_object->toString(array(
					'scheme',
					'host',
					'path'
			));

			/*
			 * $unfeathered_root = strtolower(str_replace(array(
			 * "https://",
			 * "http://",
			 * "//",
			 * "/"
			 * ), "", JURI::root()));
			*/
			
			if (! stristr($source_link_cmp, $host))
			{
				if (strpos($source_link_cmp, '/') === 0 /*substr($source_link, 0, 1) == '/'*/)
				{
					$absolute_link = $scheme . '://' . $host . $source_uri;

					// $absolute_link = strtolower(substr(JURI::root(), 0, - 1)) . $source_link;//issues in folder loaded installations
				}
				else
				{
					$absolute_link = $scheme . '://' . $host . '/' . $source_uri;

					// $absolute_link = strtolower(JURI::root()) . $source_link;
				}
			}
			else // added feb 20th making absolute links for absolute internal links just to standardize.look out for issues
			{
				if(strpos($source_uri,'http') !==0 && preg_match('~^[a-zA-Z0-9]~',$source_uri))
				{
					$absolute_link = '//'. $source_uri;
				}
				else 
				{
				$absolute_link = $source_uri;
				}
			}
		}
		else
		{
			$absolute_link = null;
		}
		// lets make an alt_signature for internal scripts as Joomla has a habit of either adding or removing a /
		if ($internal && (bool) $source_link && stripos($source_link_cmp, 'http') !== 0 && strpos($source_link_cmp, '//') !== 0)
		{

			if (strpos($source_link_cmp, '/') === 0)
			{
				$search = array(
						'src="/',
						"src='/"
				);
				$replace = array(
						'src="',
						"src='"
				);
				$alt_match = str_replace($search, $replace, $match);
			}
			else
			{

				$search = array(
						'src="',
						"src='"
				);
				$replace = array(
						'src="/',
						"src='/"
				);
				$alt_match = str_replace($search, $replace, $match);
				$alt_serialized = serialize($alt_match);
				$alt_signature = md5($alt_serialized);
			}
			$alt_serialized = serialize($alt_match);
			$alt_signature = md5($alt_serialized);
		}
		else
		{
			$alt_signature = null;
		}
		// start
		// making this code compatible with the css counterpart
		// original code
		/*
		 * if (! empty($source_link))
		 	* {
		 	* $uri_object = JURI::getInstance($source_link);
		 	* $source_link_clean = $uri_object->toString(array(
		 	* 'scheme',
		 	* 'host',
		 	* 'path'
		 	* ));
		 	* }
		 */
		 // substitute code
		 if (! empty($source_link))
		 {

		 	// workaround for //
		 	$t_flag = false;
		 	$js_source_link_t = $source_link;
		 	if (strpos($js_source_link_t, '//') === 0)
		 	{
		 		$js_source_link_t = "http:" . $js_source_link_t;
		 		$t_flag = true;
		 	}
		 	$uri_object = MulticacheUri::getInstance($js_source_link_t);
		 	$source_link_clean = $uri_object->toString(array(
		 			'scheme',
		 			'host',
		 			'path'
		 	));
		 	if (! empty($t_flag))
		 	{
		 		$source_link_clean = substr($source_link_clean, 5);
		 	}
		 }

		 // end of sub
		 // its not right to judge an external link by our scheme hence we leave them as it is for //
		 /*
		  * if (! $internal && strpos($source_link_clean, '//') === 0)
		  	* {
		  	* $source_link_clean = JURI::getInstance()->getScheme() . '://' . substr($source_link_clean, 2);
		  	* }
		  */

		  // stop
		  $jsarray[$key] = array(
		  		"src" => $source_link,
		  		"src_clean" => $source_link_clean,
		  		"code" => $code,
		  		"async" => $async,
		  		"serialized" => $serialized,
		  		"signature" => md5($serialized),
		  		"alt_signature" => $alt_signature,
		  		"rank" => $key,
		  		"quoted" => preg_quote($match),
		  		"library" => null,
		  		"social" => null,
		  		"advertisement" => null,
		  		"loadsection" => 0,
		  		"preceedence" => true,
		  		"serialized_code" => $serialized_code,
		  		"internal" => $internal,
		  		"absolute_src" => $absolute_link,
		  		"delay" => null,
		  		"delay_type" => null
		  );
	}

	Return $jsarray;

}

protected function getAllCss()
{
	$page_body = self::$_css_scraped_page_content;
	
	$search = '~(?><?[^<]*+(?:<!--(?>-?[^-]*+)*?-->)?)*?\\K(?:(?:<link(?= (?>[^\\s>]*+[\\s] (?!(?:itemprop|disabled|type=(?!  ["\']?text/css)|rel=(?!["\']?stylesheet))))*+[^\\s>]*+>)(?>[^\\s>]*+\\s)+?(?>href)=["\']?((?<!["\'])[^\\s>]*+|(?<!\')[^"]*+| [^\']*+)[^>]*+>)|(?:<style(?:(?!(?:type=(?!["\']?text/css))|(?:scoped))[^>])*>((?><?[^<]+)*?)</style>)|\\K$)~six';
	
	preg_match_all($search, $page_body, $matches);
	$cssarray = array();
	
	foreach ($matches[0] as $key => $match)
	{
		if (empty($match))
		{
			continue;
		}
		$csscode = new stdClass();
		$css_source_link = ! empty($matches[1][$key]) ? $matches[1][$key] : null;
		$css_source_link_clean = null;
		$css_source_link_cmp = strtolower($css_source_link);
	
		// we cant do a string to lower here. If the source has a link in uppercase a 404 will be issued
		// lets make a comparitive
		$css_code = ! empty($matches[2][$key]) ? $matches[2][$key] : null;
	
		$spl_identifiers = $this->findSpecialCssIdentifiers($match);
	
		$absolute_link = null;
	
		/*
		 * store contents of the script to implement remove, replace or alias methods
		 *
		 */
	
		$serialized = serialize($match);
		$serialized_code = isset($css_code) ? serialize($css_code) : NULL;
	
		if (! empty($css_source_link_cmp) && strpos($css_source_link_cmp, '//') === 0)
		{
			$host = MulticacheUri::getInstance()->getHost();
			// check whether the uri contains the host name
			if (! strstr($css_source_link_cmp, strtolower($host)))
			{
				$internal = false;
			}
			elseif (strpos($css_source_link_cmp, strtolower(host)) === 2)
			{
				$internal = true;
			}
			else
			{
				// we treat all remainder urls as external. This will avoid grouping in doubtful cases
				$internal = false;
			}
		}
		elseif (! empty($css_source_link_cmp))
		{
	
			$internal = MulticacheUri::isInternal($css_source_link);
		}
		else
		{
			$internal = null;
		}
		// moderator for certain cases
		if ($internal === false && ! empty($css_source_link_cmp) && strpos($css_source_link_cmp, strtolower(substr(MulticacheUri::root(), 0, - 1))) === 0)
		{
			$internal = true;
		}
		// additional checks for internal
		// type1:https://domain.com
		// type2://domain.com
		if (! $internal)
		{
			$type1 = 'https://' . MulticacheUri::getInstance()->getHost();
			$type2 = '//' . MulticacheUri::getInstance()->getHost();
			$type3 = 'http://' . MulticacheUri::getInstance()->getHost();
			if (strpos($css_source_link_cmp, $type1) === 0 || strpos($css_source_link_cmp, $type2) === 0 || strpos($css_source_link_cmp, $type3) === 0)
			{
				$internal = true;
			}
		}
		// end additional checks for internal
	
		if ($internal)
		{
			$host = MulticacheUri::getInstance()->getHost();
			$scheme = MulticacheUri::getInstance()->getScheme();
			// create a uri object to remove queries & fragments
			$uri_object = MulticacheUri::getInstance($css_source_link);
			$css_source_uri = $uri_object->toString(array(
					'scheme',
					'host',
					'path'
			));
	
			/*
			 * $unfeathered_root = strtolower(str_replace(array(
			 * "https://",
			 * "http://",
			 * "//",
			 * "/"
			 * ), "", JURI::root()));
			*/
	
			if (! stristr($css_source_link_cmp, $host))
			{
				if (strpos($css_source_link_cmp, '/') === 0 /*substr($source_link, 0, 1) == '/'*/)
				{
					$absolute_link = $scheme . '://' . $host . $css_source_uri; // $css_source_link;
					// $absolute_link = strtolower(substr(JURI::root(), 0, - 1)) . $source_link;//issues in folder loaded installations
				}
				else
				{
					$absolute_link = $scheme . '://' . $host . '/' . $css_source_uri; // $css_source_link;
					// $absolute_link = strtolower(JURI::root()) . $source_link;
				}
			}
			else // added feb 20th making absolute links for absolute internal links just to standardize.look out for issues
			{
				
				if(strpos($css_source_uri,'http') !==0 && preg_match('~^[a-zA-Z0-9]~',$css_source_uri))
				{
					$absolute_link = '//'. $css_source_uri;
				}
				else
				{
					$absolute_link = $css_source_uri;
				}
			}
		}
		else
		{
			$absolute_link = null;
		}
		// lets make an alt_signature for internal scripts as Joomla has a habit of either adding or removing a /
		if ($internal && (bool) $css_source_link && stripos($css_source_link_cmp, 'http') !== 0 && strpos($css_source_link_cmp, '//') !== 0)
		{
	
			if (strpos($css_source_link_cmp, '/') === 0)
			{
				$search = array(
						'href="/',
						"href='/"
				);
				$replace = array(
						'href="',
						"href='"
				);
				$alt_match = str_replace($search, $replace, $match);
			}
			else
			{
	
				$search = array(
						'href="',
						"href='"
				);
				$replace = array(
						'href="/',
						"href='/"
				);
				$alt_match = str_replace($search, $replace, $match);
				$alt_serialized = serialize($alt_match);
				$alt_signature = md5($alt_serialized);
			}
			$alt_serialized = serialize($alt_match);
			$alt_signature = md5($alt_serialized);
		}
		else
		{
			$alt_signature = null;
		}
	
		// removing query and fragments from the source url
		if (! empty($css_source_link))
		{
	
			// workaround for //
			$t_flag = false;
			$css_source_link_t = $css_source_link;
			if (strpos($css_source_link_t, '//') === 0)
			{
				$css_source_link_t = "http:" . $css_source_link_t;
				$t_flag = true;
			}
			$uri_object = MulticacheUri::getInstance($css_source_link_t);
			$css_source_link_clean = $uri_object->toString(array(
					'scheme',
					'host',
					'path'
			));
			if (! empty($t_flag))
			{
				$css_source_link_clean = substr($css_source_link_clean, 5);
			}
		}
		/*
		 * Technically we cant judge the external uri scheme by our internal settings so lets maintain this as //
		 * if (! $internal && strpos($css_source_link_clean, '//') === 0)
		 	* {
		 	* $css_source_link_clean = JURI::getInstance()->getScheme() . '://' . substr($css_source_link_clean, 2);
		 	* }
		 */
		 $cssarray[$key] = array(
		 		"href" => $css_source_link,
		 		"href_clean" => $css_source_link_clean,
		 		"code" => $css_code,
		 		"serialized" => $serialized,
		 		"signature" => md5($serialized),
		 		"alt_signature" => $alt_signature,
		 		"rank" => $key,
		 		"quoted" => preg_quote($match),
		 		"attributes" => $spl_identifiers,
		 		"loadsection" => 0,
		 		"preceedence" => true,
		 		"serialized_code" => $serialized_code,
		 		"internal" => $internal,
		 		"absolute_src" => $absolute_link,
		 		"delay" => null,
		 		"delay_type" => null
		 );
	}
	
	Return $cssarray;
	
}


protected function findSpecialCssIdentifiers($sub_match)
{
$css_spl_identifiers = json_decode($this->css_special_identifiers);
	
	if (empty($css_spl_identifiers))
	{
		Return null;
	}
	
	$css_spl_identifiers = array_filter($css_spl_identifiers);
	$identifiers = array();
	foreach ($css_spl_identifiers as $css_identifier)
	{
		$ss = "#" . $css_identifier . "=(?(?=[\"\'])(?:[\"\']([^\"\']+))|(\w+))#i";

		preg_match($ss, $sub_match, $attributes);
		$identifiers[] = $attributes;

		// preg_match('#media=(?(?=["\'])(?:["\']([^"\']+))|(\w+))#i', $sub_match, $identifiers2);
	}
	if (empty($identifiers))
	{
		Return null;
	}
	$identifiers = array_filter($identifiers);
	Return $identifiers;

}
protected function findAsync($m, $k = null)
{

	$search = "#\sasync[^>]*>#";
	$mflag = preg_match($search, $m, $source_match);

	Return $mflag;

}

protected function setSocialIndicators($js_array)
{
	// get all social indicators
	$social_indicators = json_decode($this->social_script_identifiers);
	if(is_array($social_indicators))
	{
		$social_indicators = array_filter($social_indicators);
	}
	if(empty($social_indicators) || empty($js_array))
	{
		Return $js_array;
	}
	foreach ($js_array as $key => $js)
	{
		foreach ($social_indicators as $social_indicator)
		{
			$social_indicator = trim($social_indicator);
			if (strpos($js['src'], $social_indicator) || strpos($js['code'], $social_indicator))
			{
				$js_array[$key]['social'] = 1;
			}
		}
	}

	Return $js_array;

}

protected function setAdvertisementIndicators($js_array)
{
	// get all social indicators
	$advertisement_indicators = json_decode($this->advertisement_script_identifiers);
	if(is_array($advertisement_indicators))
	{
		$advertisement_indicators = array_filter($advertisement_indicators);
	}
	if(empty($advertisement_indicators) || empty($js_array))
	{
		Return $js_array;
	}
	foreach ($js_array as $key => $js)
	{
		foreach ($advertisement_indicators as $advertisement_indicator)
		{
			$advertisement_indicator = trim($advertisement_indicator);
			if (strpos($js['src'], $advertisement_indicator) || strpos($js['code'], $advertisement_indicator))
			{
				$js_array[$key]['advertisement'] = 1;
			}
		}
	}

	Return $js_array;

}

}
