<?php

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

if (! defined('MULTICACHE_ERROR_LOGS'))
{
	define('MULTICACHE_ERROR_LOGS', dirname(dirname(__FILE__)).'/logs/');
}
//require_once plugin_dir_path(dirname(__FILE__)) . 'libs/Services_WTF_Test.php';
/*
if(file_exists(dirname(dirname(__FILE__)) . '/libs/pagescripts.php'))
{
require_once dirname(dirname(__FILE__)) . '/libs/pagescripts.php';
}
if(file_exists(dirname(dirname(__FILE__)) . '/libs/pagecss.php'))
{
require_once dirname(dirname(__FILE__)) . '/libs/pagecss.php';
}
if(file_exists(dirname(dirname(__FILE__)) . '/libs/jscachestrategy.php'))
{
require_once dirname(dirname(__FILE__)) . '/libs/jscachestrategy.php';
}
*/
/*
JLoader::register('Services_WTF_Test', JPATH_COMPONENT . '/lib/');

JLoader::register('MulticachePageScripts', JPATH_ROOT . '/administrator/components/com_multicache/lib/pagescripts.php');
JLog::addLogger(array(
    'text_file' => 'errors.php'
), JLog::ALL, array(
    'error'
));
JLoader::register('JsStrategy', JPATH_ROOT . '/administrator/components/com_multicache/lib/jscachestrategy.php');

use Joomla\Registry\Registry;
*/

/**
 * Multicache helper.
 */
class MulticacheHelper
{

    protected static $_unique_script = null;
    protected static $_unique_css = null;
    protected static $_debug = null;
    
    protected static  $tagBlacklist = array(
		'applet',
		'body',
		'bgsound',
		'base',
		'basefont',
		'embed',
		'frame',
		'frameset',
		'head',
		'html',
		'id',
		'iframe',
		'ilayer',
		'layer',
		'link',
		'meta',
		'name',
		'object',
		'script',
		'style',
		'title',
		'xml'
	);
    
    protected $xssAuto = 1 ;
    protected $tagsMethod = 0;
    protected static $sortCase = null;
    protected static $sortDirection = null;
    protected static $sortKey = null;
    protected static $sortLocale = null;
    
    

    /**
     * Gets a list of the actions that can be performed.
     *
     * @return JObject
     * @since 1.6
     */
    /*
     * Validations
     */
    public static function validate_text($input)
    {
    	Return preg_replace('~[^a-zA-Z]~','',$input);
    }
    public static function validate_numtext($input)
    {
    	Return preg_replace('~[^a-zA-Z0-9\$\._\:\#-]~','',$input);
    }
    public static function validate_textbox($input)
    {
    	$a = preg_replace('~[^a-zA-Z0-9\s\:\/\?\:\#\.\'\"\\n<>\=_-]~','',$input);
    	//replace < if not <head <body or </head or </body
    	$a = preg_replace('~<(?!(head|body|/head|/body))[^>]*>~','',$a);
    
    
    	Return $a;
    }
    public static function validateStubs($input)
    {
    	$stub_raw = $input;
    	// $pre_head_stub_identifier_array = preg_split('/>/'.iUs , $pre_head_stub_identifier_raw ,PREG_SPLIT_DELIM_CAPTURE);
    	$stub_array = self::clean_stub(explode('>', $stub_raw));
    	// $pre_head_stub_identifier_array = $this->clean_stub($pre_head_stub_identifier_array);
    	
    	$input = json_encode($stub_array);
    	
    	Return $input;
    }
    protected static function clean_stub($stub_array)
    {
    
    	$stub_array = array_filter($stub_array);
    	$ret_array = array();
    	foreach ($stub_array as $key => $stub)
    	{
    		$ret_array[] = trim($stub) . ">";
    	}
    	Return $ret_array;
    
    }
    public static function validate_google($input ,$type = 'default')
    {
    	$a = preg_replace('~[^a-zA-Z0-9\._\:-]~','',$input);
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
    public static function validate_host($input)
    {
    	//$-_.+!*'(),{}|\\^~[]`<>#%";/?:@&=
    	Return preg_replace('~[^a-zA-Z0-9\.\:\;\\\@\^\%\!\$\+\*\',\~\(\){}\|\[\]\`\"&\=\/\?\#_-]~','',$input);
    }
    public static function validate_mail($input)
    {
    	Return preg_replace('~[^a-zA-Z0-9\.\:\/\?\#\@_\+-]~','',$input);
    }
    public static function validate_num($input)
    {
    	Return preg_replace('~[^0-9\.-]~','',$input);
    }
    //authentic function
    public static function validate_integer($input)
    {
    	preg_match('/-?[0-9]+/', (string) $input, $matches);
				$result = @ (int) $matches[0];
    }
    public static function validate_cart($input)
    {
    	Return preg_replace('~[^a-zA-Z\$\s\;\\n_-]~','',$input);
    }
    public static function validateArrayRecursive($array)
    {
    	if(empty($array) || !is_array($array))
    	{
    		Return false;
    	}
    	foreach($array As $key=>$value)
    	{
    		
    		$key_r = preg_replace('~[^a-zA-Z0-9]~','',$key);
    		if($key !=$key_r)
    		{    			   			
    			unset($array[$key]);
    		}
    		
    			$key = $key_r;
    		
    	
    		if(is_array($value))
    		{
    			$array[$key] = self::validateArrayRecursive($value);
    		}
    		else {
    			$array[$key] = preg_replace('~[^a-zA-Z0-9]~','',$value);
    		    			   			
    		}
    	}
    
    	Return $array;
    }
    public static function validate_homeurl($u)
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
    
    //authentic function
    public static function validate_string($u)
    {
    	$u = (string) $u;
    	$u = self::decode_string($u);
    	Return (string)self::_remove($u);
    }
    
    protected static function decode_string($source)
	{
		static $ttr;
		if (!is_array($ttr))
		{
			// Entity decode
			$trans_tbl = get_html_translation_table(HTML_ENTITIES, ENT_COMPAT, 'ISO-8859-1');
			foreach ($trans_tbl as $k => $v)
			{
				$ttr[$v] = utf8_encode($k);
			}
		}
		$source = strtr($source, $ttr);
		// Convert decimal
		$source = preg_replace_callback('/&#(\d+);/m', function($m)
		{
			return utf8_encode(chr($m[1]));
		}, $source
		);
		// Convert hex
		$source = preg_replace_callback('/&#x([a-f0-9]+);/mi', function($m)
		{
			return utf8_encode(chr('0x' . $m[1]));
		}, $source
		);
		return $source;
	}
	public static function getPage($item)
	{
		
		if(empty($item)){
			
			return false;
		}
		$s=preg_match('~paged=([0-9]+)~' , $item , $match);
		
		if(!$s){
			Return false;
		}
		Return $match[1];
	}
	protected static function _remove($source)
	{
		$loopCounter = 0;
		// Iteration provides nested tag protection
		while ($source != self::_cleanTags($source))
		{
			$source = self::_cleanTags($source);
			$loopCounter++;
		}
		return $source;
	}
	
	protected static function _cleanTags($source)
	{
		// First, pre-process this for illegal characters inside attribute values
		$source = self::_escapeAttributeValues($source);
		// In the beginning we don't really have a tag, so everything is postTag
		$preTag = null;
		$postTag = $source;
		$currentSpace = false;
		// Setting to null to deal with undefined variables
		$attr = '';
		// Is there a tag? If so it will certainly start with a '<'.
		$tagOpen_start = strpos($source, '<');
		while ($tagOpen_start !== false)
		{
			// Get some information about the tag we are processing
			$preTag .= substr($postTag, 0, $tagOpen_start);
			$postTag = substr($postTag, $tagOpen_start);
			$fromTagOpen = substr($postTag, 1);
			$tagOpen_end = strpos($fromTagOpen, '>');
			// Check for mal-formed tag where we have a second '<' before the first '>'
			$nextOpenTag = (strlen($postTag) > $tagOpen_start) ? strpos($postTag, '<', $tagOpen_start + 1) : false;
			if (($nextOpenTag !== false) && ($nextOpenTag < $tagOpen_end))
			{
				// At this point we have a mal-formed tag -- remove the offending open
				$postTag = substr($postTag, 0, $tagOpen_start) . substr($postTag, $tagOpen_start + 1);
				$tagOpen_start = strpos($postTag, '<');
				continue;
			}
			// Let's catch any non-terminated tags and skip over them
			if ($tagOpen_end === false)
			{
				$postTag = substr($postTag, $tagOpen_start + 1);
				$tagOpen_start = strpos($postTag, '<');
				continue;
			}
			// Do we have a nested tag?
			$tagOpen_nested = strpos($fromTagOpen, '<');
			if (($tagOpen_nested !== false) && ($tagOpen_nested < $tagOpen_end))
			{
				$preTag .= substr($postTag, 0, ($tagOpen_nested + 1));
				$postTag = substr($postTag, ($tagOpen_nested + 1));
				$tagOpen_start = strpos($postTag, '<');
				continue;
			}
			// Let's get some information about our tag and setup attribute pairs
			$tagOpen_nested = (strpos($fromTagOpen, '<') + $tagOpen_start + 1);
			$currentTag = substr($fromTagOpen, 0, $tagOpen_end);
			$tagLength = strlen($currentTag);
			$tagLeft = $currentTag;
			$attrSet = array();
			$currentSpace = strpos($tagLeft, ' ');
			// Are we an open tag or a close tag?
			if (substr($currentTag, 0, 1) == '/')
			{
				// Close Tag
				$isCloseTag = true;
				list ($tagName) = explode(' ', $currentTag);
				$tagName = substr($tagName, 1);
			}
			else
			{
				// Open Tag
				$isCloseTag = false;
				list ($tagName) = explode(' ', $currentTag);
			}
			/*
			 * Exclude all "non-regular" tagnames
			 * OR no tagname
			 * OR remove if xssauto is on and tag is blacklisted
			 */
			if ((!preg_match("/^[a-z][a-z0-9]*$/i", $tagName)) || (!$tagName) || ((in_array(strtolower($tagName), self::$tagBlacklist)) && (self::$xssAuto)))
			{
				$postTag = substr($postTag, ($tagLength + 2));
				$tagOpen_start = strpos($postTag, '<');
				// Strip tag
				continue;
			}
			/*
			 * Time to grab any attributes from the tag... need this section in
			 * case attributes have spaces in the values.
			 */
			while ($currentSpace !== false)
			{
				$attr = '';
				$fromSpace = substr($tagLeft, ($currentSpace + 1));
				$nextEqual = strpos($fromSpace, '=');
				$nextSpace = strpos($fromSpace, ' ');
				$openQuotes = strpos($fromSpace, '"');
				$closeQuotes = strpos(substr($fromSpace, ($openQuotes + 1)), '"') + $openQuotes + 1;
				$startAtt = '';
				$startAttPosition = 0;
				// Find position of equal and open quotes ignoring
				if (preg_match('#\s*=\s*\"#', $fromSpace, $matches, PREG_OFFSET_CAPTURE))
				{
					$startAtt = $matches[0][0];
					$startAttPosition = $matches[0][1];
					$closeQuotes = strpos(substr($fromSpace, ($startAttPosition + strlen($startAtt))), '"') + $startAttPosition + strlen($startAtt);
					$nextEqual = $startAttPosition + strpos($startAtt, '=');
					$openQuotes = $startAttPosition + strpos($startAtt, '"');
					$nextSpace = strpos(substr($fromSpace, $closeQuotes), ' ') + $closeQuotes;
				}
				// Do we have an attribute to process? [check for equal sign]
				if ($fromSpace != '/' && (($nextEqual && $nextSpace && $nextSpace < $nextEqual) || !$nextEqual))
				{
					if (!$nextEqual)
					{
						$attribEnd = strpos($fromSpace, '/') - 1;
					}
					else
					{
						$attribEnd = $nextSpace - 1;
					}
					// If there is an ending, use this, if not, do not worry.
					if ($attribEnd > 0)
					{
						$fromSpace = substr($fromSpace, $attribEnd + 1);
					}
				}
				if (strpos($fromSpace, '=') !== false)
				{
					// If the attribute value is wrapped in quotes we need to grab the substring from
					// the closing quote, otherwise grab until the next space.
					if (($openQuotes !== false) && (strpos(substr($fromSpace, ($openQuotes + 1)), '"') !== false))
					{
						$attr = substr($fromSpace, 0, ($closeQuotes + 1));
					}
					else
					{
						$attr = substr($fromSpace, 0, $nextSpace);
					}
				}
				// No more equal signs so add any extra text in the tag into the attribute array [eg. checked]
				else
				{
					if ($fromSpace != '/')
					{
						$attr = substr($fromSpace, 0, $nextSpace);
					}
				}
				// Last Attribute Pair
				if (!$attr && $fromSpace != '/')
				{
					$attr = $fromSpace;
				}
				// Add attribute pair to the attribute array
				$attrSet[] = $attr;
				// Move search point and continue iteration
				$tagLeft = substr($fromSpace, strlen($attr));
				$currentSpace = strpos($tagLeft, ' ');
			}
			// Is our tag in the user input array?
			$tagFound = in_array(strtolower($tagName), self::$tagsArray);
			// If the tag is allowed let's append it to the output string.
			if ((!$tagFound && self::$tagsMethod) || ($tagFound && !self::$tagsMethod))
			{
				// Reconstruct tag with allowed attributes
				if (!$isCloseTag)
				{
					// Open or single tag
					$attrSet = self::_cleanAttributes($attrSet);
					$preTag .= '<' . $tagName;
					for ($i = 0, $count = count($attrSet); $i < $count; $i++)
					{
					$preTag .= ' ' . $attrSet[$i];
					}
					// Reformat single tags to XHTML
					if (strpos($fromTagOpen, '</' . $tagName))
					{
					$preTag .= '>';
					}
					else
					{
					$preTag .= ' />';
					}
					}
					// Closing tag
					else
						{
						$preTag .= '</' . $tagName . '>';
						}
						}
						// Find next tag's start and continue iteration
						$postTag = substr($postTag, ($tagLength + 2));
						$tagOpen_start = strpos($postTag, '<');
		}
		// Append any code after the end of tags and return
		if ($postTag != '<')
			{
				$preTag .= $postTag;
				}
				return $preTag;
	}
	
	protected static  function _escapeAttributeValues($source)
	{
		$alreadyFiltered = '';
		$remainder = $source;
		$badChars = array('<', '"', '>');
		$escapedChars = array('&lt;', '&quot;', '&gt;');
		// Process each portion based on presence of =" and "<space>, "/>, or ">
		// See if there are any more attributes to process
		while (preg_match('#<[^>]*?=\s*?(\"|\')#s', $remainder, $matches, PREG_OFFSET_CAPTURE))
		{
			// Get the portion before the attribute value
			$quotePosition = $matches[0][1];
			$nextBefore = $quotePosition + strlen($matches[0][0]);
			// Figure out if we have a single or double quote and look for the matching closing quote
			// Closing quote should be "/>, ">, "<space>, or " at the end of the string
			$quote = substr($matches[0][0], -1);
			$pregMatch = ($quote == '"') ? '#(\"\s*/\s*>|\"\s*>|\"\s+|\"$)#' : "#(\'\s*/\s*>|\'\s*>|\'\s+|\'$)#";
			// Get the portion after attribute value
			if (preg_match($pregMatch, substr($remainder, $nextBefore), $matches, PREG_OFFSET_CAPTURE))
			{
				// We have a closing quote
				$nextAfter = $nextBefore + $matches[0][1];
			}
			else
			{
				// No closing quote
				$nextAfter = strlen($remainder);
			}
			// Get the actual attribute value
			$attributeValue = substr($remainder, $nextBefore, $nextAfter - $nextBefore);
			// Escape bad chars
			$attributeValue = str_replace($badChars, $escapedChars, $attributeValue);
			$attributeValue = self::_stripCSSExpressions($attributeValue);
			$alreadyFiltered .= substr($remainder, 0, $nextBefore) . $attributeValue . $quote;
			$remainder = substr($remainder, $nextAfter + 1);
		}
		// At this point, we just have to return the $alreadyFiltered and the $remainder
		return $alreadyFiltered . $remainder;
	}
    protected static function _stripCSSExpressions($source)
	{
		// Strip any comments out (in the form of /*...*/)
		$test = preg_replace('#\/\*.*\*\/#U', '', $source);
		// Test for :expression
		if (!stripos($test, ':expression'))
		{
			// Not found, so we are done
			$return = $source;
		}
		else
		{
			// At this point, we have stripped out the comments and have found :expression
			// Test stripped string for :expression followed by a '('
			if (preg_match_all('#:expression\s*\(#', $test, $matches))
			{
				// If found, remove :expression
				$test = str_ireplace(':expression', '', $test);
				$return = $test;
			}
		}
		return $return;
	}
	
	
    public static function log_error($error_message , $file = 'errors', $error_obj = null)
    {
    	$error_file = MULTICACHE_ERROR_LOGS.$file.'.log';
    	$date = date('Y-m-d  H:i:s');
    	$error_message = "\n".$date.' 	'.__($error_message,'multicache-plugin');
    	if(isset($error_obj))
    	{
    		$error_message = $error_message.'	'.var_export($error_obj, true);
    	}
    	error_log($error_message, 3 , $error_file);
    	
    } 
    
    public static function encodePregSplit($input)
    {
    	Return json_encode(preg_split('/[\s,\n]+/',$input));
    }
    public static function decodePrepareMulticacheTextAreas($obj)
{
	if(empty($obj))
	{
		Return false;
	}
	
		$decoded_obj = json_decode($obj, true);
		if(empty($decoded_obj))
		{
			Return false;
		}
		$decoded_obj = array_filter($decoded_obj);
		Return  implode("\n", $decoded_obj);
		 
	
}

public static function saveTransient($label , $flag , $message = null, $transname = 'multicache_admin_notices')
{
	//retreive the transname
	$transient = get_transient($transname);
	$transient = unserialize($transient);
	if(!isset($transient))
	{
		$transient = array();
		$transient[$label] = $flag;
		$transient["message"][$label] = $message;
		set_transient($transname,serialize($transient) , 60);
		Return;
	}
	
	$transient[$label] = $flag;
	$transient["message"][$label] = $message;
	set_transient($transname, serialize($transient) ,60);
		
}



public static function prepareMessageEnqueue( $message , $type = 'error',$transname = 'multicache_admin_generic_notices')
{
	$transient = get_transient($transname);
	//$transient = unserialize($transient);
	if(empty($transient))
	{
		$transient[] = array('type' => $type,'message' =>$message);
		set_transient($transname,$transient , 60);
		Return;
	}
	
	foreach($transient As $saved_message)
	{
		//lets not duplicate messages
		if($saved_message['message'] == $message)
		{
			return;
		}
	}
	$transient[]  = array('type' => $type,'message' =>$message);
	set_transient($transname,$transient , 60);
}
    
    /*
    public static function getActions()
    {

        $user = JFactory::getUser();
        $result = new JObject();

        $assetName = 'com_multicache';

        $actions = array(
            'core.admin',
            'core.manage',
            'core.create',
            'core.edit',
            'core.edit.own',
            'core.edit.state',
            'core.delete'
        );

        foreach ($actions as $action)
        {
            $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;

    }

    /**
     * Configure the Linkbar.
     */
    /*
    public static function addSubmenu($vName = '')
    {

        JHtmlSidebar::addEntry(JText::_('COM_MULTICACHE_TITLE_ADVANCED_SIMULATION_MULTICACHE_DASHBOARD'), 'index.php?option=com_multicache&view=advancedsimulation', $vName == 'advancedsimulation');

        JHtmlSidebar::addEntry(JText::_('COM_MULTICACHE_TITLE_MULTICACHE_URL_DASHBOARD'), 'index.php?option=com_multicache&view=urls', $vName == 'urls');
        JHtmlSidebar::addEntry(JText::_('COM_MULTICACHE_TITLE_MULTICACHE_GROUP_CACHE'), 'index.php?option=com_multicache&view=multicache', $vName == 'multicache');
        JHtmlSidebar::addEntry(JText::_('COM_MULTICACHE_TITLE_MULTICACHE_PAGE_CACHE'), 'index.php?option=com_multicache&view=pagecache', $vName == 'pagecache');

    }

    /**
     * Get a list of filter options for the application clients.
     *
     * @return array An array of JHtmlOption elements.
     */
    /*
    public static function getClientOptions()
    {
        // Build the filter options.
        $options = array();
        $options[] = JHtml::_('select.option', '0', JText::_('JSITE'));
        $options[] = JHtml::_('select.option', '1', JText::_('JADMINISTRATOR'));
        return $options;

    }

    public static function getHammerOptions()
    {

        $options = array();
        $options[] = JHtml::_('select.option', '0', JText::_('COM_MULTICACHE_MCD_CART_MODE'));
        $options[] = JHtml::_('select.option', '1', JText::_('COM_MULTICACHE_MCD_MULTIADMIN_MODE'));
        $options[] = JHtml::_('select.option', '2', JText::_('COM_MULTICACHE_MCD_PAGE_STRICT'));
        $options[] = JHtml::_('select.option', '3', JText::_('COM_MULTICACHE_MCD_PAGE_HIGH_HAMMERED'));
        return $options;

    }

    public static function setLockOff()
    {

        $app = JFactory::getApplication();
        $plugin = JPluginHelper::getPlugin('system', 'multicache');
        $extensionTable = JTable::getInstance('extension');
        $pluginId = $extensionTable->find(array(
            'element' => 'multicache',
            'folder' => 'system'
        ));
        if (! isset($pluginId))
        {
            $app->enqueueMessage(JText::_('COM_MULTICACHE_REQUIRES_MULTICACHE_PLUGIN'), 'error');
            Return false;
        }
        $pluginRow = $extensionTable->load($pluginId);
        $params = new JRegistry($plugin->params);
        $params->set('lock_sim_control', FALSE);
        $extensionTable->bind(array(
            'params' => $params->toString()
        ));
        if (! $extensionTable->check())
        {
            $app->enqueueMessage(JText::_('COM_MULTICACHE_HELPER_SETLOCKOFF_FAILED_TABLECHECK') . '  ' . $extensionTable->getError(), 'error');
            return false;
        }
        if (! $extensionTable->store())
        {
            $app->enqueueMessage(JText::_('COM_MULTICACHE_HELPER_SETLOCKOFF_FAILED_TABLECHECK') . '  ' . $extensionTable->getError(), 'error');
            return false;
        }

    }

    public static function getSimObj()
    {
        // Build the filter options.
        $options = array();
        $options[] = JHtml::_('select.option', 'simulation', JText::_('COM_MULTICACHE_OPTIONS_SIMULATION'));
        $options[] = JHtml::_('select.option', 'fixed', JText::_('COM_MULTICACHE_OPTIONS_FIXED'));
        return $options;

    }

    public static function getComponentOptions()
    {

        $options = array();
        $options[] = JHtml::_('select.option', '0', JText::_('COM_MULTICACHE_CONFIG_COMPONENT_OPTION_BLANK_LABEL'));
        $options[] = JHtml::_('select.option', '1', JText::_('COM_MULTICACHE_CONFIG_COMPONENT_OPTION_EXCLUDE_LABEL'));
        return $options;

    }

    public static function getCompleteFlag()
    {
        // Build the filter options.
        $options = array();
        $options[] = JHtml::_('select.option', 'show_inprogress', JText::_('COM_MULTICACHE_OPTIONS_SHOW_INPROGRESS'));
        $options[] = JHtml::_('select.option', 'show_only_complete', JText::_('COM_MULTICACHE_OPTIONS_SHOW_COMPLETE'));
        return $options;

    }

    public static function getTolerances()
    {
        // Build the filter options.
        $options = array();
        $options[] = JHtml::_('select.option', 'show_danger_tolerance', JText::_('COM_MULTICACHE_OPTIONS_TOLERANCE_SHOW_DANGER'));
        $options[] = JHtml::_('select.option', 'show_warning_tolerance', JText::_('COM_MULTICACHE_OPTIONS_TOLERANCE_SHOW_WARNING'));
        $options[] = JHtml::_('select.option', 'show_success_tolerance', JText::_('COM_MULTICACHE_OPTIONS_TOLERANCE_SHOW_SUCCESS'));
        $options[] = JHtml::_('select.option', 'show_unhighlighted_tolerance', JText::_('COM_MULTICACHE_OPTIONS_TOLERANCE_SHOW_UNHIGHLIGHTED'));
        return $options;

    }

    public static function getCacheStandardOptions()
    {

        $options = array();
        $options[] = JHtml::_('select.option', '1', JText::_('COM_MULTICACHE_CACHESTANDARDOPTIONS_STANDARD_LABEL'));
        $options[] = JHtml::_('select.option', '2', JText::_('COM_MULTICACHE_CACHESTANDARDOPTIONS_NONSTANDARD_LABEL'));
        return $options;

    }

    public static function getCacheTypes()
    {
        // Build the filter options.
        $options = array();
        $options[] = JHtml::_('select.option', '1', JText::_('COM_MULTICACHE_OPTION_CACHE_TYPE_FILE'));
        $options[] = JHtml::_('select.option', '2', JText::_('COM_MULTICACHE_OPTION_CACHE_TYPE_MEMCACHE'));
        return $options;

    }
*/
    public static function getUniqueScripts($page_scripts_array , $type = 'script')
    {

        $unique_scripts = array();
        foreach ($page_scripts_array as $page_script)
        {
            $sig = $page_script["signature"];
            $unique_scripts[$sig] = $page_script;
        }
        $prop = '_unique_'.$type;
        self::$$prop = $unique_scripts;
        Return count($unique_scripts);

    }
/*
    public static function getUniqueScriptAsArray()
    {

        Return isset(self::$_unique_script) ? self::$_unique_script : false;

    }
*/
    public static function getPageCssObject($page_css)
    {

        if (empty($page_css))
        {
            Return false;
        }
        $page_css_obj = array();
        $clean_code = array(
            "'",
            '"',
            " ",
            ";"
        );

        foreach ($page_css as $key => $css)
        {

            $page_css_obj[$key]["name"] = ! empty($css["href"]) ? (string) $css["href"] : (string) strip_tags(substr(str_replace($clean_code, '', $css["code"]), 0, 120));
            $page_css_obj[$key]["signature"] = $css["signature"];

            // get loadsection options
           // $options = self::getLoadSectionOptions();
            $selected = isset($css["loadsection"]) ? $css["loadsection"] : 0;
            //$attribs = 'class=" com_multicache_cssloadsection " style="width:110px;"'; // make this out of params
            $title_tag = __('Choose where to load this style','multicache-plugin');
            $loadsection[$key] = makeSelectButtonNumeric('multicache_config_css_tweak_options', 'cssloadsection_' . $key, $selected                   , false,            $title_tag      , '0'        ,   '5'         , '1'           , array( 0 => 'default', 1 => 'Head Open', 2 => 'Head', 3 => 'Body Open', 4 => 'Footer' , 5 => 'Dont Load'),null,' tweak_selectors multicache_cssloadsection');
            $page_css_obj[$key]["loadsection"]  = makeSelectButtonNumeric('multicache_config_css_tweak_options', 'cssloadsection_' . $key, $selected                   , false,            $title_tag      , '0'        ,   '5'         , '1'           , array( 0 => 'default', 1 => 'Head Open', 2 => 'Head', 3 => 'Body Open', 4 => 'Footer' , 5 => 'Dont Load'),null,' tweak_selectors multicache_cssloadsection');
           // $loadsection[$key] = JHTML::_('select.genericlist', $options, 'com_multicache_cssloadsection_' . $key, $attribs, 'value', 'text', $selected, false, false);
            //$page_css_obj[$key]["loadsection"] = JHTML::_('select.genericlist', $options, 'com_multicache_cssloadsection_' . $key, $attribs, 'value', 'text', $selected, false, false);
            if (isset($selected))
            {
                $page_css_obj[$key]["params"]["loadsection"] = $selected;
            }

            // get delay options
            //$options = self::getGenericYesNo();
            $selected = isset($css["delay"]) ? $css["delay"] : null;
            $title_tag = __('Select yes to delay this style','multicache-plugin');
            $delay[$key] = makeSelectButton('multicache_config_css_tweak_options', 'cssdelay_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag, null , ' col-md-1 ');
            $page_css_obj[$key]["delay"] = makeSelectButton('multicache_config_css_tweak_options', 'cssdelay_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag, null , ' col-md-1 ');
            //$delay[$key] = JHTML::_('select.genericlist', $options, 'com_multicache_cssdelay_' . $key, $attribs, 'value', 'text', $selected, false, false);
            //$page_css_obj[$key]["delay"] = JHTML::_('select.genericlist', $options, 'com_multicache_cssdelay_' . $key, $attribs, 'value', 'text', $selected, false, false);

            // set the delay params
            if (! empty($selected))
            {
                $page_css_obj[$key]["params"]["delay"] = $selected;
            }

            // get delay type
            //$options = self::getCssDelayTypes();
            $selected = !empty($css["delay_type"]) ? $css["delay_type"] : 'async';
            //$attribs = 'style="width:120px;"'; // make this out of params
            $title_tag = __('Choose the type of delay','multicache-plugin');
            //$delaytype[$key] = makeSelectButton('multicache_config_css_tweak_options', 'cssdelay_type_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'async'  ),1=>array('key' => 1,'val' => 'mousemove'),2=>array('key' => 2,'val' => 'scroll')), true,       $title_tag, null , ' col-md-1 ');
           // $page_css_obj[$key]["delay_type"] = makeSelectButton('multicache_config_css_tweak_options', 'cssdelay_type_' . $key,  $selected , array( 0 => array('key' => 'async','val' => 'async'  ),1=>array('key' => 'mousemove','val' => 'mousemove'),2=>array('key' => 'scroll','val' => 'scroll')), true,       $title_tag, null , ' col-md-1 ');
            $delaytype[$key] = makeDelayButton('multicache_config_css_tweak_options', 'cssdelay_type_' . $key,  $selected , false, $title_tag,  array(
			'async' => 'Async',
			'mousemove' => 'Mouse move',
			'scroll' => 'Scroll',
							));
            $page_css_obj[$key]["delay_type"] = makeDelayButton('multicache_config_css_tweak_options', 'cssdelay_type_' . $key,  $selected , false, $title_tag,  array(
			'async' => 'Async',
			'mousemove' => 'Mouse move',
			'scroll' => 'Scroll',
							));
            //$delaytype[$key] = JHTML::_('select.genericlist', $options, 'com_multicache_cssdelay_type_' . $key, $attribs, 'value', 'text', $selected, false, false);
            //$page_css_obj[$key]["delay_type"] = JHTML::_('select.genericlist', $options, 'com_multicache_cssdelay_type_' . $key, $attribs, 'value', 'text', $selected, false, false);

            // load CDN alias
           // $options = self::getGenericYesNo();
            $selected = isset($css["cdnalias"]) ? $css["cdnalias"] : null;
            //$attribs = 'style="width:60px;"'; // make this out of params
            $cdnAlias[$key] = makeSelectButton('multicache_config_css_tweak_options', 'csscdnalias_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag, null , ' col-md-1 ');
            $page_css_obj[$key]["cdnAlias"] = makeSelectButton('multicache_config_css_tweak_options', 'csscdnalias_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag, null , ' col-md-1 ');

            //$cdnAlias[$key] = JHTML::_('select.genericlist', $options, 'com_multicache_csscdnalias_' . $key, $attribs, 'value', 'text', $selected, false, false);
            //$page_css_obj[$key]["cdnAlias"] = JHTML::_('select.genericlist', $options, 'com_multicache_csscdnalias_' . $key, $attribs, 'value', 'text', $selected, false, false);
            // place the cdn_url only if it exists
            if (isset($css["cdn_url_css"]))
            {
                $page_css_obj[$key]["params"]["cdn_url_css"] = $css["cdn_url_css"];
            }
            // get ignore options
            //$options = self::getGenericYesNo();
            $selected = isset($css["ignore"]) ? $css["ignore"] : null;
            $title_tag = __('Choose to ignore this script','multicache-plugin');
            $ignore[$key] = makeSelectButton('multicache_config_css_tweak_options', 'cssignore_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag, null , ' col-md-1 ');
            $page_css_obj[$key]["ignore"] = makeSelectButton('multicache_config_css_tweak_options', 'cssignore_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag, null , ' col-md-1 ');
            //$ignore[$key] = JHTML::_('select.genericlist', $options, 'com_multicache_cssignore_' . $key, $attribs, 'value', 'text', $selected, false, false);
            //$page_css_obj[$key]["ignore"] = JHTML::_('select.genericlist', $options, 'com_multicache_cssignore_' . $key, $attribs, 'value', 'text', $selected, false, false);
            // get grouping options
            //$options = self::getGenericYesNo();
            $isInternal = (isset($css["internal"]) && $css["internal"] == "internal") ? 1 : 0;
            $splIdentifiers = (isset($css["attributes"]) && ! empty($css["attributes"])) ? 1 : 0;
            // $selected = isset($css["grouping"]) ? $css["grouping"] : ((! $isInternal || $splIdentifiers) ? 1 : 0);
            $selected = isset($css["grouping"]) ? $css["grouping"] : 1;
            $title_tag = __('Choose whether to group this css','multicache-plugin');
            $grouping[$key] = makeSelectButton('multicache_config_css_tweak_options', 'cssgrouping_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag, null , ' col-md-1 grouping_selector');
            $page_css_obj[$key]["grouping"] = makeSelectButton('multicache_config_css_tweak_options', 'cssgrouping_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag, null , ' col-md-1 grouping_selector');
            //$grouping[$key] = JHTML::_('select.genericlist', $options, 'com_multicache_cssgrouping_' . $key, $attribs, 'value', 'text', $selected, false, false);
            //$page_css_obj[$key]["grouping"] = JHTML::_('select.genericlist', $options, 'com_multicache_cssgrouping_' . $key, $attribs, 'value', 'text', $selected, false, false);

            // get group number
            //$options = self::getCssGroupNumber();
            //$attribs = 'style="width:120px;"'; // make this out of params
            $selected = isset($css["group_number"]) ? $css["group_number"] : null;
            $group_number[$key] = makeSelectButtonNumeric('multicache_config_css_tweak_options', 'cssgroup_number_' . $key, $selected                   , false,            $title_tag      , '0'        ,   '10'         , '1'           , array( 0 => 'default', 1 => 'group-1', 2 => 'group-2', 3 => 'group-3', 4 => 'group-4' , 5 => 'group-5',6 => 'group-6',7 => 'group-7',8 => 'group-8',9 => 'group-9',10 => 'group-10'),null,' tweak_selectors');
            $page_css_obj[$key]["group_number"] = makeSelectButtonNumeric('multicache_config_css_tweak_options', 'cssgroup_number_' . $key, $selected                   , false,            $title_tag      , '0'        ,   '10'         , '1'           , array( 0 => 'default', 1 => 'group-1', 2 => 'group-2', 3 => 'group-3', 4 => 'group-4' , 5 => 'group-5',6 => 'group-6',7 => 'group-7',8 => 'group-8',9 => 'group-9',10 => 'group-10'),null,' tweak_selectors');
            //$group_number[$key] = JHTML::_('select.genericlist', $options, 'com_multicache_cssgroup_number_' . $key, $attribs, 'value', 'text', $selected, false, false);
            //$page_css_obj[$key]["group_number"] = JHTML::_('select.genericlist', $options, 'com_multicache_cssgroup_number_' . $key, $attribs, 'value', 'text', $selected, false, false);
        }

        $CssTransposeObject = new stdClass();
        $CssTransposeObject->loadsection = $loadsection;
        $CssTransposeObject->delay = $delay;
        $CssTransposeObject->delay_type = $delaytype;
        $CssTransposeObject->cdnalias = $cdnAlias;
        $CssTransposeObject->ignore = $ignore;
        $CssTransposeObject->grouping = $grouping;
        $CssTransposeObject->group_number = $group_number;

        $CssScriptObject = new stdClass();
        $CssScriptObject->cssobject = $page_css_obj;
        $CssScriptObject->CssTransposeObject = $CssTransposeObject;

        Return $CssScriptObject;

    }

    public static function prepareScriptLazy($input , $jquery_scope = "jQuery")
    {
if(!empty($input->resultant_async_js) || !empty($input->resultant_defer_js))
{
	//$inline_code = 'function blazyload(){' . $jquery_scope . '(function(){' . $jquery_scope . '("img.multicache_lazy").show().lazyload()})}function timeoutlazy(e,n,o,d){if(o="undefined"==typeof o?1:++o,d="undefined"==typeof d?1e4:d,n="undefined"==typeof n?10:n,e="undefined"==typeof e?"blazyload":e,("undefined"==typeof window.' . $jquery_scope . '||"undefined"==typeof window.MULTICACHELAZYLOADED)&&"undefined"==typeof window.LAZYLOADCALLED&&d>=o)setTimeout(function(){console.log("routeBinit run - "+o+" time -"+n),timeoutlazy(e,n,o)},n);else{if("undefined"==typeof window.LAZYLOADCALLED){var i="undefined"==typeof window.LAZYLOADCALLED?"undefined":"defined",f="undefined"==typeof window.LAZYLOADCALLED?"undefined":"defined";console.log("calling lazy..... "+typeof window.LAZYLOADCALLED+" a- "+i+" b- "+f),blazyload(),window.LAZYLOADCALLED=!0}console.log("rolling in else")}}timeoutlazy("blazyload",1);';
	$inline_code = '"undefined"!=typeof ' . $jquery_scope . '?' . $jquery_scope . '(function(){' . $jquery_scope . '("img.multicache_lazy").show().lazyload()}):"undefined"!=typeof $&&$(function(){$("img.multicache_lazy").show().lazyload()});';
}
else 
{
        $inline_code = $jquery_scope . '(function() {' . $jquery_scope . '("img.multicache_lazy").show().lazyload();});';
}
       

        //
        Return serialize($inline_code);

    }

    public static function prepareStylelazy()
    {

        $inline_code = '.multicache_lazy {display: none;}';
        Return serialize($inline_code);

    }

    public static function prepareLazyloadParams($tbl, $script, $style)
    {

        $params = array();
        $params["llswitch"] = $tbl->image_lazy_switch;
        $params["container_selector_switch"] = $tbl->image_lazy_container_switch;
        $params["container_rules"] = ! empty($tbl->image_lazy_container_strings) ? serialize(self::decodeObj($tbl->image_lazy_container_strings)) : null;
        $params["img_selectors_switch"] = $tbl->image_lazy_image_selector_include_switch;
        $params["img_selector_rules"] = ! empty($tbl->image_lazy_image_selector_include_strings) ? serialize(self::decodeObj($tbl->image_lazy_image_selector_include_strings)) : null;
        $params["image_deselector_switch"] = $tbl->image_lazy_image_selector_exclude_switch;
        $params["image_deselector_rules"] = ! empty($tbl->image_lazy_image_selector_exclude_strings) ? serialize(self::decodeObj($tbl->image_lazy_image_selector_exclude_strings)) : null;
        $params["ll_script"] = $script; // pre serialized
        $params["ll_style"] = $style; // pre serialized
        $params['md_script'] = array('lib' => md5(self::getloadableSourceScript(plugins_url('delivery/assets/js/jquery.lazyload.js' , dirname(__FILE__)))) ,'exec' => md5(self::getloadableCodeScript($script)));
        $params['md_style'] = array(exec => md5(self::getloadableCodeCss($style)));
        Return serialize($params);

    }

    protected static function decodeObj($obj)
    {

        $obj = json_decode($obj);
        Return $obj;

    }
    
    public static function getAllPlugins()
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
    	return $plugins_urls_path;
    }
    
    public static function makeWinSafeArray($array)
    {
    
    	If (empty($array) || !is_array($array))
    	{
    		Return $array;
    	}
    
    	$array = array_map(array(
    			'self',
    			'removeCarriage'
    	), $array);
    	$array = array_filter($array);
    	Return array_map('trim', $array);
    
    }
    
    protected static function removeCarriage($s1)
    {
    
    	Return preg_replace('~\\r~', '', $s1);
    
    }
    public static function getPageScriptObject($page_scripts_array)
    {

        if (empty($page_scripts_array))
        {
            Return false;
        }
        $page_obj = array();
        $clean_code = array(
            "'",
            '"',
            " ",
            ";"
        );

        foreach ($page_scripts_array as $key => $script)
        {

            $page_obj[$key]["name"] = ! empty($script["src"]) ? (string) $script["src"] : (string) strip_tags(substr(str_replace($clean_code, '', $script["code"]), 0, 120));
            $page_obj[$key]["signature"] = $script["signature"];

            // get library options

            //$options = self::getGenericYesNo();
            $selected = isset($script["library"]) ? $script["library"] : 0;
            //$attribs = 'style="width:60px;"'; // make this out of params
            //fn - makeSelectButton($option,                      $opvar  , $get_var, $compare_var = array(0=>array('key' =>1,'val'=>1),1=>array('key'=>0,'val'=>0)), $required = false, $title_tag = '', $third_param = null)
            //ex - makeSelectButton('multicache_config_options', 'caching', $caching,    array( 0 => array('key' => 0,'val' => 'Off'  ),1=>array('key' => 1,'val' => 'On')), true,       $title_tag);
            $title_tag = __('Select only if principle library','multicache-plugin');
            $library[$key] = makeSelectButton('multicache_config_script_tweak_options', 'library_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag , null , ' col-md-1 ');
            $page_obj[$key]["library"] = makeSelectButton('multicache_config_script_tweak_options', 'library_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag , null , ' col-md-1 '); 
            //$library[$key] = JHTML::_('select.genericlist', $options, 'com_multicache_library_' . $key, $attribs, 'value', 'text', $selected, false, false);
            //$page_obj[$key]["library"] = JHTML::_('select.genericlist', $options, 'com_multicache_library_' . $key, $attribs, 'value', 'text', $selected, false, false);

            if (isset($selected))
            {
                $page_obj[$key]["params"]["library"] = $selected;
            }
            // get loadsection options
           // $options = self::getLoadSectionOptions();
            $selected = isset($script["loadsection"]) ? $script["loadsection"] : 0;
            //$attribs = 'class=" com_multicache_loadsection " style="width:110px;"'; // make this out of params
            //fn - makeSelectButtonNumeric($option,                        $opvar          , $get_var = '20'            , $required = false, $title_tag = '', $start = '0', $stop = '100', $interval = '1', $labels = null                                                                      , $third_param = null , $class_adds = null )
            //ex -  makeSelectButtonNumeric('multicache_config_options', 'orphaned_scripts',                              $multicache_orphaned_scripts, false,            $title_tag      , '0'        ,   '4'         , '1'           , array( 0 => 'leave as is', 1 => 'Head Open', 2 => 'Head', 3 => 'Body', 4 => 'Footer',' tweak_selectors'));
            $title_tag = __('Choose where to load this script','multicache-plugin');
            $loadsection[$key] = makeSelectButtonNumeric('multicache_config_script_tweak_options', 'loadsection_' . $key, $selected                   , false,            $title_tag      , '0'        ,   '6'         , '1'           , array( 0 => 'default', 1 => 'Head Open', 2 => 'Head', 3 => 'Body Open', 4 => 'Footer' , 5 => 'Dont Load' , 6 => 'Dont Move'),null,' tweak_selectors multicache_loadsection');
            $page_obj[$key]["loadsection"] = makeSelectButtonNumeric('multicache_config_script_tweak_options', 'loadsection_' . $key, $selected                   , false,            $title_tag      , '0'        ,   '6'         , '1'           , array( 0 => 'default', 1 => 'Head Open', 2 => 'Head', 3 => 'Body Open', 4 => 'Footer' , 5 => 'Dont Load', 6 => 'Dont Move'),null,' tweak_selectors multicache_loadsection');
            //$loadsection[$key] = JHTML::_('select.genericlist', $options, 'com_multicache_loadsection_' . $key, $attribs, 'value', 'text', $selected, false, false);
            //$page_obj[$key]["loadsection"] = JHTML::_('select.genericlist', $options, 'com_multicache_loadsection_' . $key, $attribs, 'value', 'text', $selected, false, false);
            if (isset($selected))
            {
                $page_obj[$key]["params"]["loadsection"] = $selected;
            }

            // get advertisement options
           // $options = self::getGenericYesNo();
            $selected = isset($script["advertisement"]) ? $script["advertisement"] : null;
           // $attribs = 'style="width:60px;"'; // make this out of params
            $title_tag = __('If this is an advertisement script select yes','multicache-plugin');
            $advertisement[$key] = makeSelectButton('multicache_config_script_tweak_options', 'advertisement_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag , null , ' col-md-1 ');
            $page_obj[$key]["advertisement"] = makeSelectButton('multicache_config_script_tweak_options', 'advertisement_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag , null , ' col-md-1');
            //$advertisement[$key] = JHTML::_('select.genericlist', $options, 'com_multicache_advertisement_' . $key, $attribs, 'value', 'text', $selected, false, false);
            //$page_obj[$key]["advertisement"] = JHTML::_('select.genericlist', $options, 'com_multicache_advertisement_' . $key, $attribs, 'value', 'text', $selected, false, false);

            // get social options
            //$options = self::getGenericYesNo();
            $selected = isset($script["social"]) ? $script["social"] : null;
            $title_tag = __('If this is a social script select yes','multicache-plugin');
            $social[$key] = makeSelectButton('multicache_config_script_tweak_options', 'social_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag, null , ' col-md-1 ');
            $page_obj[$key]["social"] = makeSelectButton('multicache_config_script_tweak_options', 'social_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag, null , ' col-md-1 ');
            //$social[$key] = JHTML::_('select.genericlist', $options, 'com_multicache_social_' . $key, $attribs, 'value', 'text', $selected, false, false);
            //$page_obj[$key]["social"] = JHTML::_('select.genericlist', $options, 'com_multicache_social_' . $key, $attribs, 'value', 'text', $selected, false, false);

            // get delay options
           // $options = self::getGenericYesNo();
            $selected = isset($script["delay"]) ? $script["delay"] : null;
            $title_tag = __('Select yes to delay this script','multicache-plugin');
            $delay[$key] = makeSelectButton('multicache_config_script_tweak_options', 'delay_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag, null , ' col-md-1 ');
            $page_obj[$key]["delay"] = makeSelectButton('multicache_config_script_tweak_options', 'delay_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag, null , ' col-md-1 ');
            //$delay[$key] = JHTML::_('select.genericlist', $options, 'com_multicache_delay_' . $key, $attribs, 'value', 'text', $selected, false, false);
            //$page_obj[$key]["delay"] = JHTML::_('select.genericlist', $options, 'com_multicache_delay_' . $key, $attribs, 'value', 'text', $selected, false, false);

            // set the delay params
            if (! empty($selected))
            {
                $page_obj[$key]["params"]["delay"] = $selected;
            }

            // get delay type
            //$options = self::getDelayTypes();
            $selected = isset($script["delay_type"]) ? $script["delay_type"] : 'mousemove';
            //$attribs = 'style="width:120px;"'; // make this out of params
            $title_tag = __('Choose the type of delay','multicache-plugin');
            //$delaytype[$key] = makeDelayButton('multicache_config_script_tweak_options', 'delay_type_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'mousemove'  ),1=>array('key' => 1,'val' => 'scroll')), true,       $title_tag, null , ' col-md-1 ');
            //$page_obj[$key]["delay_type"]= makeDelayButton('multicache_config_script_tweak_options', 'delay_type_' . $key,  $selected , array( 0 => array('key' => 'mousemove','val' => 'mousemove'  ),1=>array('key' => 'scroll','val' => 'scroll')), true,       $title_tag, null , ' col-md-1 ');
            $delaytype[$key] =  makeDelayButton('multicache_config_script_tweak_options', 'delay_type_' . $key ,  $selected , false, $title_tag,  array(
			'mousemove' => 'Mouse move',
			'scroll' => 'Scroll',
            'onload' => 'onLoad',
							));
            $page_obj[$key]["delay_type"]=  makeDelayButton('multicache_config_script_tweak_options', 'delay_type_' . $key ,  $selected , false, $title_tag,  array(
			'mousemove' => 'Mouse move',
			'scroll' => 'Scroll',
            'onload' => 'onLoad',
							));
            if (isset($script["delay_type"]))
            {
            	$page_obj[$key]["params"]["delay_type"] = $selected;
            }
            if (isset($script["ident"]))
            {
            	$page_obj[$key]["params"]["ident"] = $script["ident"];
            }
            
            //promises
            $selected = isset($script["promises"]) ? $script["promises"] : null;
            // $attribs = 'style="width:60px;"'; // make this out of params
            $title_tag = __('Wrap in promise','multicache-plugin');
            $promises[$key] = makeSelectButton('multicache_config_script_tweak_options', 'promises_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag , null , ' col-md-1 ');
            $page_obj[$key]["promises"] = makeSelectButton('multicache_config_script_tweak_options', 'promises_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag , null , ' col-md-1');
            if (isset($script["promises"]))
            {
            	$page_obj[$key]["params"]["promises"] = $selected;
            }
            //MAU promises
            $selected = isset($script["mau"]) ? $script["mau"] : null;
            // $attribs = 'style="width:60px;"'; // make this out of params
            $title_tag = __('Incorporate MAU multicache Async Utility','multicache-plugin');
            $mau[$key] = makeSelectButton('multicache_config_script_tweak_options', 'mau_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag , null , ' col-md-1 ');
            $page_obj[$key]["mau"] = makeSelectButton('multicache_config_script_tweak_options', 'mau_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag , null , ' col-md-1');
            if (isset($script["mau"]))
            {
            	$page_obj[$key]["params"]["mau"] = $selected;
            }
            //$delaytype[$key] = JHTML::_('select.genericlist', $options, 'com_multicache_delay_type_' . $key, $attribs, 'value', 'text', $selected, false, false);
            //$page_obj[$key]["delay_type"] = JHTML::_('select.genericlist', $options, 'com_multicache_delay_type_' . $key, $attribs, 'value', 'text', $selected, false, false);
            
            if (isset($script["checktype"]))
            {
            	$page_obj[$key]["params"]["checktype"] = $script["checktype"];
            }
        if (isset($script["thenBack"]))
            {
            	$page_obj[$key]["params"]["thenBack"] = $script["thenBack"];
            }
            if (isset($script["mautime"]))
            {
            	$page_obj[$key]["params"]["mautime"] = $script["mautime"];
            }
            // load CDN alias
           // $options = self::getGenericYesNo();
            $selected = isset($script["cdnalias"]) ? $script["cdnalias"] : null;
           // $attribs = 'style="width:60px;"'; // make this out of params
            $title_tag = __('Choose to substitute this script with a cdn','multicache-plugin');
            $cdnAlias[$key] = makeSelectButton('multicache_config_script_tweak_options', 'cdnalias_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag, null , ' col-md-1 ');
            $page_obj[$key]["cdnAlias"] = makeSelectButton('multicache_config_script_tweak_options', 'cdnalias_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag, null , ' col-md-1 ');
            //$cdnAlias[$key] = JHTML::_('select.genericlist', $options, 'com_multicache_cdnalias_' . $key, $attribs, 'value', 'text', $selected, false, false);
            //$page_obj[$key]["cdnAlias"] = JHTML::_('select.genericlist', $options, 'com_multicache_cdnalias_' . $key, $attribs, 'value', 'text', $selected, false, false);
            // place the cdn_url only if it exists
            if (isset($script["cdn_url"]))
            {
                $page_obj[$key]["params"]["cdn_url"] = $script["cdn_url"];
            }
            // get ignore options
           // $options = self::getGenericYesNo();
            $selected = isset($script["ignore"]) ? $script["ignore"] : null;
            $title_tag = __('Choose to ignore this script','multicache-plugin');
            $ignore[$key] = makeSelectButton('multicache_config_script_tweak_options', 'ignore_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag, null , ' col-md-1 ');
            $page_obj[$key]["ignore"] = makeSelectButton('multicache_config_script_tweak_options', 'ignore_' . $key,  $selected , array( 0 => array('key' => 0,'val' => 'No'  ),1=>array('key' => 1,'val' => 'Yes')), true,       $title_tag, null , ' col-md-1 ');
            //$ignore[$key] = JHTML::_('select.genericlist', $options, 'com_multicache_ignore_' . $key, $attribs, 'value', 'text', $selected, false, false);
            //$page_obj[$key]["ignore"] = JHTML::_('select.genericlist', $options, 'com_multicache_ignore_' . $key, $attribs, 'value', 'text', $selected, false, false);
        }

        $pageTransposeObject = new stdClass();
        $pageTransposeObject->library = $library;
        $pageTransposeObject->loadsection = $loadsection;
        $pageTransposeObject->advertisement = $advertisement;
        $pageTransposeObject->social = $social;
        $pageTransposeObject->delay = $delay;
        $pageTransposeObject->delay_type = $delaytype;
        $pageTransposeObject->promises = $promises;
        $pageTransposeObject->mau = $mau;
        $pageTransposeObject->cdnalias = $cdnAlias;
        $pageTransposeObject->ignore = $ignore;

        $pageScriptObject = new stdClass();
        $pageScriptObject->pageobject = $page_obj;
        $pageScriptObject->pagetransposeobject = $pageTransposeObject;

        Return $pageScriptObject;

    }

    public static function PrepareJSTexcludes($url_include_switch, $query_include_switch, $urls, $queries, $component_excludes, $url_string_excludes)
    {

        if (empty($urls) && empty($queries) && empty($component_excludes) && empty($url_string_excludes))
        {
            Return null;
        }
        $excurl = null;
        $q_val = null;

        if (! empty($urls))
        {
            $excurl = array();
            foreach ($urls as $url)
            {
                $excurl[$url] = 1;
            }
        }
        if (! empty($queries))
        {
            $q_val = array();
            foreach ($queries as $query)
            {
                $split = explode("=", $query);
                $key = $split[0];
                $value = isset($split[1]) ? $split[1] : 1;
                $q_val[$key][$value] = 1;
            }
        }

        $jsexcludes = new stdClass();
        $jsexcludes->urlswitch = $url_include_switch;
        $jsexcludes->queryswitch = $query_include_switch;
        $jsexcludes->settings = array(
            "url_switch" => $url_include_switch,
            "query_switch" => $query_include_switch
        );
        $jsexcludes->url = isset($excurl) ? $excurl : null;
        $jsexcludes->query = isset($q_val) ? $q_val : null;
        $jsexcludes->url_strings = ! empty($url_string_excludes) ? $url_string_excludes : null;
        $jsexcludes->component = ! empty($component_excludes) ? $component_excludes : null;
        Return $jsexcludes;

    }

    /*
     * public static function PrepareCSSexcludes($url_include_switch, $query_include_switch, $urls, $queries, $component_excludes, $url_string_excludes)
     * {
     *
     * if (empty($urls) && empty($queries) && empty($component_excludes) && empty($url_string_excludes))
     * {
     * Return null;
     * }
     * $excurl = null;
     * $q_val = null;
     *
     * if (! empty($urls))
     * {
     * $excurl = array();
     * foreach ($urls as $url)
     * {
     * $excurl[$url] = 1;
     * }
     * }
     * if (! empty($queries))
     * {
     * $q_val = array();
     * foreach ($queries as $query)
     * {
     * $split = explode("=", $query);
     * $key = $split[0];
     * $value = isset($split[1]) ? $split[1] : 1;
     * $q_val[$key][$value] = 1;
     * }
     * }
     *
     * $cssexcludes = new stdClass();
     * $cssexcludes->urlswitch = $url_include_switch;
     * $cssexcludes->queryswitch = $query_include_switch;
     * $cssexcludes->settings = array(
     * "url_switch" => $url_include_switch,
     * "query_switch" => $query_include_switch
     * );
     * $cssexcludes->url = isset($excurl) ? $excurl : null;
     * $cssexcludes->query = isset($q_val) ? $q_val : null;
     * $cssexcludes->url_strings = ! empty($url_string_excludes) ? $url_string_excludes : null;
     * $cssexcludes->component = ! empty($component_excludes) ? $component_excludes : null;
     * Return $cssexcludes;
     *
     * }
     */
     /*
    public static function getAllComponents()
    {

        $ignorable = array(
            'com_admin',
            'com_cache',
            'com_categories',
            'com_checkin',
            'com_config',
            'com_cpanel',
            'com_installer',
            'com_languages',
            'com_media',
            'com_menus',
            'com_messages',
            'com_modules',
            'com_plugins',
            'com_redirect',
            'com_templates'
        );

        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('element')
            ->from('#__extensions')
            ->where('type = ' . $db->quote('component'))
            ->where('enabled = 1');
        $db->setQuery($query);
        $result = $db->loadColumn();
        if (isset($result) && is_array($result))
        {
            $result = array_diff($result, $ignorable);
        }

        return $result;

    }
    */
    // The Cart Object is not prepared in the frontend helper as it is asumed to be activated in backend
    public static function prepareCartObject($urls, $countryseg = null, $cart_diff = null, $cart_mode = null, $distribution = null)
    {

        if (empty($urls))
        {
            Return false;
        }
        if (is_array($urls))
        {
            $cart_urls = array();
            foreach ($urls as $url)
            {
                $cart_urls[$url] = 1;
            }
        }
        else
        {
            $cart_urls = null;
        }
        $cart_urls = preg_replace('/\s/', '', var_export($cart_urls, true));
        $cart_urls = str_replace(',)', ')', $cart_urls);
/*
        $session_items = null;
        if (! empty($session_vars) && is_array($session_vars))
        {
            $session_vars = array_filter($session_vars);
            if (! empty($session_vars))
            {
                foreach ($session_vars as $key => $var)
                {
                    if (! empty($var))
                    {
                        $parts = (explode(',', $var));
                        $session_items[$parts[0]] = $parts[1];
                    }
                }
            }
        }

        // $session_items = array_filter($session_items);

        $session_items = preg_replace('/\s/', '', var_export($session_items, true));
        $session_items = str_replace(',)', ')', $session_items);

        $diff_vars = null;
        if (! empty($cart_diff) && is_array($cart_diff))
        {
            $cart_diff = array_filter($cart_diff);
            if (! empty($cart_diff))
            {
                foreach ($cart_diff as $key => $diff_v)
                {

                    if (! empty($diff_v))
                    {
                        $diff_vars[$diff_v] = 1;
                    }
                }
            }
        }

        $diff_vars = preg_replace('/\s/', '', var_export($diff_vars, true));
        $diff_vars = str_replace(',)', ')', $diff_vars);
*/
        $settings = array(
            'cart_mode' => $cart_mode,
            'distribution' => $distribution,
        	'countryseg' => $countryseg
        );
        $settings = preg_replace('/\s/', '', var_export($settings, true));
        $settings = str_replace(',)', ')', $settings);
        $cart_mode = var_export($cart_mode, true);
        $countryseg = var_export($countryseg , true);
        $distribution = var_export($distribution, true);

        ob_start();
        echo "<?php
/**
 * MulticacheWP
 * http://www.multicache.org
 * High Performance fastcache Controller
 * Version: MU 1.0.0.2
 * Author: Wayne DSouza
 * Author URI: http://onlinemarketingconsultants.in
 * License: GNU PUBLIC LICENSE see license.txt
 */
defined('_MULTICACHEWP_EXEC') or die();
class CartMulticache{

public static \$vars = array('urls' => " . trim($cart_urls) . ",  'countryseg' => " . trim($countryseg) . " ,  'cart_mode' => " . trim($cart_mode) . " , 'distribution' => " . trim($distribution) . ");




public static \$cart_settings	= " . trim($settings) . ";
}
?>";
        $cl_buf = ob_get_clean();
        $cl_buf = serialize($cl_buf);

        $dir = dirname(plugin_dir_path(__FILE__)).'/libs';
        $filename = 'cartmulticache.php';
        $success = self::writefileTolocation($dir, $filename, $cl_buf);
        Return $success;

    }

    public static function prepareStubs($tbl)
    {

        if (! empty($tbl->pre_head_stub_identifiers))
        {
            $head_open = json_decode($tbl->pre_head_stub_identifiers);
        }
        else
        {
            $head_open = array(
                "<head>"
            );
        }
        if (! empty($tbl->head_stub_identifiers))
        {
            $head = json_decode($tbl->head_stub_identifiers);
        }
        else
        {
            $head = array(
                "</head>"
            );
        }
        if (! empty($tbl->body_stub_identifiers))
        {
            $body = json_decode($tbl->body_stub_identifiers);
        }
        else
        {
            $body = array(
                "<body>"
            );
        }
        if (! empty($tbl->footer_stub_identifiers))
        {
            $footer = json_decode($tbl->footer_stub_identifiers);
        }
        else
        {
            $footer = array(
                "</body>"
            );
        }

        $stub_identifiers = array(
            "head_open" => $head_open,
            "head" => $head,
            "body" => $body,
            "footer" => $footer
        );
        Return serialize($stub_identifiers);

    }

    public static function setJsSwitch($input , $js_switch, $conduit_switch = null, $testing_switch = null, $advanced = null, $js_comments = null, $debug_mode, $orphaned = null, $css_switch, $css_comments, $compress_css, $minify_html, $compress_js, $orphaned_styles_loading, $img_tweaks , $css_groups_async = false)
    {

        //$app = JFactory::getApplication();
        //$plugin = JPluginHelper::getPlugin('system', 'multicache');
        /*
        $extensionTable = JTable::getInstance('extension');
        $pluginId = $extensionTable->find(array(
            'element' => 'multicache',
            'folder' => 'system'
        ));
        if (! isset($pluginId))
        {
            $app->enqueueMessage(JText::_('COM_MULTICACHE_REQUIRES_MULTICACHE_PLUGIN'), 'error');
            Return false;
        }
        $pluginRow = $extensionTable->load($pluginId);
        $params = new JRegistry($plugin->params);
        
        $params->set('js_switch', $js_switch);
        $params->set('css_switch', $css_switch);
        $params->set('css_comments', $css_comments);
        $params->set('compress_css', $compress_css);
        $params->set('minify_html', $minify_html);
        $params->set('compress_js', $compress_js);
        $params->set('img_tweaks', $img_tweaks);
        */
        
        $options_system_params = get_option('multicache_system_params');
        
        $options_system_params['js_switch'] = $js_switch;
        $options_system_params['css_switch'] = $css_switch;
        $options_system_params['css_comments'] = $css_comments;
        $options_system_params['compress_css'] = $compress_css;
        $options_system_params['minify_html'] = $minify_html;
        $options_system_params['compress_js'] = $compress_js;
        $options_system_params['img_tweaks'] = $img_tweaks;
        $options_system_params['debug_mode'] = $debug_mode;
        $options_system_params['advanced_simulation_lock']= empty($input->advanced_simulation_lock)? false: true;
        $options_system_params['css_groupsasync'] = $css_groups_async;
        
        if (isset($conduit_switch))
        {
        	//$params->set('conduit_switch', $conduit_switch);
        	$options_system_params['conduit_switch'] = $conduit_switch;
        }
        if (isset($testing_switch))
        {
        	//$params->set('testing_switch', $testing_switch);
        	$options_system_params['testing_switch'] = $testing_switch;
        }
        if (isset($advanced))
        {
        	//$params->set('js_advanced', $advanced);
        	$options_system_params['js_advanced'] = $advanced;
        }
        if (isset($js_comments))
        {
        	//$params->set('js_comments', $js_comments);
        	$options_system_params['js_comments'] = $js_comments;
        }
        if (isset($orphaned))
        {
        	//$params->set('js_orphaned', $orphaned);
        	$options_system_params['js_orphaned'] =  $orphaned;
        }
        if (isset($orphaned_styles_loading))
        {
        	//$params->set('orphaned_styles_loading', $orphaned_styles_loading);
        	$options_system_params['orphaned_styles_loading'] = $orphaned_styles_loading;
        }
        //N.B: IMPORTANT multicache_system_params should always have autoload true
        $result = update_option('multicache_system_params', $options_system_params);
             

        Return $result;

    }
    protected static function specify($a)
    {
    	$str_len = strlen($a);
    	
    	if($str_len <= 5)
    	{
    		Return null;
    	}
    	Return $a;
    }
    protected static function getIdent($obj)
    {
    	if(!empty($obj['ident']))    	{
    		
    	Return 	$obj['ident'];
    	}
    	if(!empty($obj['src']))
    	{
    	 $a = 	$obj['src'];
    	 $a = str_replace(array('http://' ,'https://') , '' , $a);
    	 $a = preg_replace('~[\?\'"].*~' , '' , $a);
    	 Return $a;
    	}
    	else
    	{
    	 $a = $obj['code'];
    	}
    	$p = array_filter(array_map("self::specify" ,preg_split('~[\s\'\"]~' , $a ,-1, PREG_SPLIT_NO_EMPTY)));
    	$ln = array_map('strlen' , $p);
    	$max = max($ln);
    	$key = array_search($max,$ln);
    	Return $p[$key];
    }
    public static function getonLoadexecCode($obj)
    {
    	$combine_instruc = 'false';//hardcoding this value for nowv-1.0.0.4
    	if(empty($obj))
    	{
    		Return false;
    	}
    	$cl_buf = "";
    	$k = 0;
    	$m = count($obj) - 1;
    	foreach($obj As $key => $value)
    	{
    		$src_code = !empty($value['src'])? 'src' : 'code';
    		
    		if($src_code == 'src')
    		{
    			$name = preg_replace('~[^a-zA-Z]~','',$value['src']);
    		}
    		else{
    			$name = preg_replace('~[^a-zA-Z]~','',$value['code']);
    		}
    		$name = substr($name , -5 , 5);
    		$name = $name. '_multicache_ol_delay';
    		$ident = self::getIdent($value);
    		$type = $src_code == 'src'? 'src' :'text';
    		$string = $src_code == 'src'? $value['src'] : json_encode($value['code']);
    		$combine = $src_code == 'src'? 'null' : $combine_instruc;
    		ob_start();
    		echo '
    		 '. $k .' : {
    				"name"   : "'.$name.'",
    				"ident"  : "'.$ident.'",
    				"type"   : "'.$type.'",
    				';
    		if($src_code == 'src')
    		{
    			echo '"string" : "'.$string.'",';
    		}
    		else{
    			echo '"string" : '.$string.',';
    		}
    		echo '		
    				"combine": '.$combine.'
    				     }';
    		if($k< $m)
    		{
    			echo ",
    					";
    		}
    		$cl_buf .= ob_get_clean();
    		$k++;
    	}
    	Return $cl_buf;
    }
    
    public static function getonLoadDelay($multicache_exec_code)
    {
    	if(empty($multicache_exec_code))
    	{
    		Return false;
    	}
    	ob_start();
    	echo '
    			var elementPresent=function(e,c,t){for(var n=0;n<e.length;n++)if(elem_sub=e[n][t],(-1===elem_sub.indexOf("multicache_exec_code")||-1===elem_sub.indexOf("elementPresent"))&&""!=elem_sub&&(iof=elem_sub.indexOf(c),-1!==iof))return!0;return!1},multicache_exec_code={'.$multicache_exec_code.'};!function(e,c,n){function i(){var e,t=(Object.keys(multicache_exec_code).length,c.getElementsByTagName(n)),i="",o=function(e,t,i,o,a){o=c.createElement(n),"text"===a?(o.text=e,o.id=t,o.setAttribute("async","")):(o.src=e,o.id=t,o.setAttribute("async","")),i.parentNode.insertBefore(o,i)};for(var a in multicache_exec_code){var l=elementPresent(t,multicache_exec_code[a].ident,multicache_exec_code[a].type);l||("text"===multicache_exec_code[a].type?multicache_exec_code[a].combine===!1?o(multicache_exec_code[a].string,multicache_exec_code[a].name,t[0],e,multicache_exec_code[a].type):i+=multicache_exec_code[a].string:"src"===multicache_exec_code[a].type&&o(multicache_exec_code[a].string,multicache_exec_code[a].name,t[0],e,multicache_exec_code[a].type))}""!==i&&o(i,"m_comb",t[0],e,"text")}t=typeof n,e.addEventListener?(console.log("adding event listner load"),e.addEventListener("load",i,!1)):e.attachEvent&&(console.log("attaching event"),e.attachEvent("onload",i))}(window,document,"script"),console.log("end");
    			';
    	/*
    	echo "
    			var  elementPresent = function(u , b , t)
{
console.log('u -' + u + ' b - ' + b + ' t - ' + t);
for(var j = 0 ; j< u.length; j++){  
 elem_sub = u[j][t];
 //skip this script
  if( elem_sub.indexOf('multicache_exec_code') !== -1
  && elem_sub.indexOf('elementPresent') !== -1){
  console.log('skipping');
  continue;
  }   
  if(elem_sub == ''){
  continue;
  }
  iof = elem_sub.indexOf(b);
  console.log(iof + ' ' +  elem_sub + ' ' + b);
  if(iof !== -1){
   console.log(' elem_sub ' +  elem_sub + ' b val ' + b);
  return true;
    } 
  }
  return false;
}
var multicache_exec_code = {
    			".$multicache_exec_code."
    };
console.log('start');
(function(w, d, s) {
console.log('in');
t = typeof s;
console.log('type of s' + t);
  function multicache_odstart(){
   var code_string = '';
   var len_loadable = Object.keys(multicache_exec_code).length;
   var js, fjs = d.getElementsByTagName(s);
   var combined_code = '';
   var multicache_load = function(c_u_string, id , u ,js , t) {
    console.log('executing multicache_odstart');
    js = d.createElement(s);
    if(t === 'text'){
       js.text = c_u_string;
       js.id = id;js.setAttribute('async', '');
    }
    else
    {
      js.src = c_u_string; js.id = id;js.setAttribute('async', '');
    }
	 console.log(' end code ' + js);
	 u.parentNode.insertBefore(js, u);
  	};
   //for code
   for(var q in multicache_exec_code )
   {
   console.log('mexec ' + multicache_exec_code[q].name);
   var p = elementPresent(fjs , multicache_exec_code[q].ident , multicache_exec_code[q].type);
    alert('p is ' + p) ;
   if(!p)
   {   
   if(multicache_exec_code[q].type === 'text' )
   {
      if(multicache_exec_code[q].combine === false)
      {
        multicache_load(multicache_exec_code[q].string , multicache_exec_code[q].name , fjs[0] , js , multicache_exec_code[q].type );
      }
      else{
        combined_code += multicache_exec_code[q].string;
      }
   }
   else if(multicache_exec_code[q].type === 'src')
   {
     multicache_load(multicache_exec_code[q].string , multicache_exec_code[q].name , fjs[0] , js , multicache_exec_code[q].type);
   }
   }
    }
  if(combined_code !=='')
  {
   multicache_load(combined_code , 'm_comb' , fjs[0] , js , 'text');
  }

  }
  if (w.addEventListener) 
  {
  console.log('adding event listner load');
  w.addEventListener('load', multicache_odstart , false);
  }
  else if (w.attachEvent) 
  { 
  console.log('attaching event');
  w.attachEvent('onload',multicache_odstart);
  }
}(window, document, 'script'));
    			";*/
    	$cl_buf = ob_get_clean();
    	Return serialize($cl_buf);
    }
    public static function getdelaycode($delay_type, $jquery_scope = "jQuery", $mediaFormat)
    {

        //$app = JFactory::getApplication();
        // $delay_type = self::extractDelayType($delay_array);
        // $base_url = '//' . str_replace("http://", "", strtolower(substr(JURI::root(), 0, - 1)) . '/media/com_multicache/assets/js/jscache/');
       // $base_url = JURI::root() . 'media/com_multicache/assets/js/jscache/';
    	if(is_multisite())
    	{
    		$dir = plugin_dir_url(dirname(__FILE__)).'delivery/assets/js/jscache/';
    		$object = self::resolveLocMul($dir , '');
    		$base_url = $object['path']['path'] .'/';
    	}
    	else {
        $base_url = plugin_dir_url(dirname(__FILE__)).'delivery/assets/js/jscache/';
    	}
        // $base_url = '//' . str_replace("http://", "", strtolower(substr(JURI::root(), 0, - 1)) . '/administrator/components/com_multicache/assets/js/jscache/');
        if ($delay_type == "scroll")
        {
            $name = "onscrolldelay.js";
            $url = $base_url . $name;
            $inline_code = 'var url_' . $delay_type . ' = "' . $url . '?mediaFormat=' . $mediaFormat . '";var script_delay_' . $delay_type . '_counter = 0;var max_trys_' . $delay_type . ' = 3;var inter_lock_' . $delay_type . '= 1;' . $jquery_scope . '(window).scroll(function(event) {/*alert("count "+script_delay_' . $delay_type . '_counter);*/console.log("count " + script_delay_' . $delay_type . '_counter);console.log("scroll detected" );if(!inter_lock_' . $delay_type . '){return;/*an equivalent to continue*/}++script_delay_' . $delay_type . '_counter;if(script_delay_' . $delay_type . '_counter <=  max_trys_' . $delay_type . ') {inter_lock_' . $delay_type . ' = 0;' . $jquery_scope . '( this).unbind( event );console.log("unbind");' . $jquery_scope . '.getScript( url_' . $delay_type . ', function() {console.log("getscrpt call on ' . $delay_type . '" );script_delay_' . $delay_type . '_counter =  max_trys_' . $delay_type . '+1;}).fail(function(jqxhr, settings, exception) {/*alert("loading failed" + url_' . $delay_type . ');*/console.log("loading failed in " + url_' . $delay_type . ' +" trial "+ script_delay_' . $delay_type . '_counter);console.log("exception -"+ exception);console.log("settings -"+ settings);console.log("jqxhr -"+ jqxhr);inter_lock_' . $delay_type . ' = 1;});}else{/* alert("giving up");*/' . $jquery_scope . '( this).unbind( "scroll" );console.log("failed scroll loading  "+ url_' . $delay_type . '+"  giving up" );}});';
            
        }
        elseif ($delay_type == "mousemove")
        {
            $name = "onmousemovedelay.js";
            $url = $base_url . $name;
            $inline_code = 'var url_' . $delay_type . ' = "' . $url . '?mediaFormat=' . $mediaFormat . '";var script_delay_' . $delay_type . '_counter = 0;var max_trys_' . $delay_type . ' = 3;var inter_lock_' . $delay_type . '= 1;' . $jquery_scope . '(window).on("mousemove" ,function( event ) {/*alert("count "+script_delay_counter);*/console.log("count " + script_delay_' . $delay_type . '_counter);console.log("mousemove detected" );if(!inter_lock_' . $delay_type . '){return;/*an equivalent to continue*/}++script_delay_' . $delay_type . '_counter;if(script_delay_' . $delay_type . '_counter <= max_trys_' . $delay_type . ') {inter_lock_' . $delay_type . ' = 0;' . $jquery_scope . '( this).unbind( event );console.log("unbind");' . $jquery_scope . '.getScript( url_' . $delay_type . ', function() {console.log("getscrpt call on ' . $delay_type . '" );script_delay_' . $delay_type . '_counter =  max_trys_' . $delay_type . ';}).fail(function(jqxhr, settings, exception) {/*alert("loading failed" + url_' . $delay_type . ');*/console.log("loading failed in " + url_' . $delay_type . '  +" trial "+ script_delay_' . $delay_type . '_counter);console.log("exception -"+ exception);console.log("settings -"+ settings);console.log("jqxhr -"+ jqxhr);inter_lock_' . $delay_type . ' = 1;});}else{/* alert("giving up");*/' . $jquery_scope . '( this).unbind( "mousemove" );console.log("failed loading "+ url_' . $delay_type . '+"  giving up" );}});';
            
            
        }
        else
        {
            self::prepareMessageEnqueue(__('JS Delay encountered an unlisted delay type while preparing delay code'), 'error');
        }

        $obj["code"] = serialize($inline_code);
        $obj["url"] = $name;
        

        Return $obj;

    }
//NOT TESTED
    public static function getCssdelaycode($delay_type, $jquery_scope = "jQuery", $mediaFormat = false , $params = false)
    {

        //$app = JFactory::getApplication();
        // $delay_type = self::extractDelayType($delay_array);
        /*
         * $base_url = '//' . str_replace(array(
         * "http://",
         * "https://"
         * ), array(
         * "",
         * ""
         * ), strtolower(substr(JURI::root(), 0, - 1)) . '/media/com_multicache/assets/css/csscache/');
         */
        //$base_url = JURI::root() . 'media/com_multicache/assets/css/csscache/';
    	if(is_multisite())
    	{
    		$dir = plugin_dir_url(dirname(__FILE__)).'delivery/assets/css/csscache/';
    		$object = self::resolveLocMul($dir , '');
    		$base_url = $object['path']['path'] .'/';
    	}
    	else {
        $base_url = plugin_dir_url(dirname(__FILE__)).'delivery/assets/css/csscache/';
    	}
        if ($delay_type == "scroll")
        {
            $name = "onscrolldelay.html";
            $url = $base_url . $name;
            $inline_code = 'var css_url_' . $delay_type . ' = "' . $url . '?mediaFormat=' . $mediaFormat . '";var css_delay_' . $delay_type . '_counter = 0;var css_max_trys_' . $delay_type . ' = 3;var css_inter_lock_' . $delay_type . '= 1;' . $jquery_scope . '(window).scroll(function(event) {/*alert("count "+css_delay_' . $delay_type . '_counter);*/console.log("count " + css_delay_' . $delay_type . '_counter);console.log("scroll detected" );if(!css_inter_lock_' . $delay_type . '){return;/*an equivalent to continue*/}++css_delay_' . $delay_type . '_counter;if(css_delay_' . $delay_type . '_counter <=  css_max_trys_' . $delay_type . ') {css_inter_lock_' . $delay_type . ' = 0;' . $jquery_scope . '( this).unbind( event );console.log("unbind");' . $jquery_scope . '.ajax({url:  css_url_' . $delay_type . ',type: "GET",dataType: "html",cache: !1,success: function(t){console.log("getscrpt call on ' . $delay_type . '");css_delay_' . $delay_type . '_counter = css_max_trys_' . $delay_type . '+1;' . $jquery_scope . '("body").append(t);}}).fail(function(jqxhr, settings, exception) {console.log("loading failed in " + css_url_' . $delay_type . ' + " trial " + css_delay_' . $delay_type . '_counter);console.log("exception -"+ exception);console.log("settings -"+ settings);css_inter_lock_' . $delay_type . ' = 1;});}else{/* alert("giving up");*/' . $jquery_scope . '( this).unbind( "scroll" );console.log("failed scroll loading  "+ css_url_' . $delay_type . '+"  giving up" );}});';
            
            
        }
        elseif ($delay_type == "mousemove")
        {
            $name = "onmousemovedelay.html";
            $url = $base_url . $name;
            // $inline_code = 'var url_' . $delay_type . ' = "' . $url . '?mediaFormat=' . $mediaFormat . '";var script_delay_' . $delay_type . '_counter = 0;var max_trys_' . $delay_type . ' = 3;var inter_lock_' . $delay_type . '= 1;' . $jquery_scope . '(window).on("mousemove" ,function( event ) {/*alert("count "+script_delay_counter);*/console.log("count " + script_delay_' . $delay_type . '_counter);console.log("mousemove detected" );if(!inter_lock_' . $delay_type . '){return;/*an equivalent to continue*/}++script_delay_' . $delay_type . '_counter;if(script_delay_' . $delay_type . '_counter <= max_trys_' . $delay_type . ') {inter_lock_' . $delay_type . ' = 0;' . $jquery_scope . '( this).unbind( event );console.log("unbind");' . $jquery_scope . '.getScript( url_' . $delay_type . ', function() {console.log("getscrpt call on ' . $delay_type . '" );script_delay_' . $delay_type . '_counter = max_trys_' . $delay_type . ';}).fail(function() {/*alert("loading failed" + url_' . $delay_type . ');*/console.log("loading failed in " + url_' . $delay_type . ' +" trial "+ script_delay_' . $delay_type . '_counter);inter_lock_' . $delay_type . ' = 1;});}else{/* alert("giving up");*/' . $jquery_scope . '( this).unbind( "mousemove" );console.log("failed loading "+ url_' . $delay_type . '+" giving up" );}});';
            $inline_code = 'var css_url_' . $delay_type . ' = "' . $url . '?mediaFormat=' . $mediaFormat . '";var css_delay_' . $delay_type . '_counter = 0;var css_max_trys_' . $delay_type . ' = 3;var css_inter_lock_' . $delay_type . '= 1;' . $jquery_scope . '(window).on("mousemove" ,function( event ) {/*alert("count "+css_delay_' . $delay_type . '_counter);*/console.log("count " + css_delay_' . $delay_type . '_counter);console.log("mousemove detected" );if(!css_inter_lock_' . $delay_type . '){return;/*an equivalent to continue*/}++css_delay_' . $delay_type . '_counter;if(css_delay_' . $delay_type . '_counter <= css_max_trys_' . $delay_type . ') {css_inter_lock_' . $delay_type . ' = 0;' . $jquery_scope . '( this).unbind( event );console.log("unbind");' . $jquery_scope . '.ajax({url:  css_url_' . $delay_type . ',type: "GET",dataType: "html",cache: !1,success: function(t){console.log("getscrpt call on ' . $delay_type . '");css_delay_' . $delay_type . '_counter = css_max_trys_' . $delay_type . ';' . $jquery_scope . '("body").append(t);}}).fail(function(jqxhr, settings, exception) {console.log("loading failed in " + css_url_' . $delay_type . ' + " trial " + css_delay_' . $delay_type . '_counter);console.log("exception -"+ exception);console.log("settings -"+ settings);console.log("jqxhr -"+ jqxhr);css_inter_lock_' . $delay_type . ' = 1;});}else{/* alert("giving up");*/' . $jquery_scope . '( this).unbind( "mousemove" );console.log("failed mousemove loading "+ css_url_' . $delay_type . '+"  giving up" );}});';
            
        }
        elseif ($delay_type == "async")
        {
            $name = "async.css";
            $url = $base_url . $name;
            // $inline_code = 'var url_' . $delay_type . ' = "' . $url . '?mediaFormat=' . $mediaFormat . '";var script_delay_' . $delay_type . '_counter = 0;var max_trys_' . $delay_type . ' = 3;var inter_lock_' . $delay_type . '= 1;' . $jquery_scope . '(window).on("mousemove" ,function( event ) {/*alert("count "+script_delay_counter);*/console.log("count " + script_delay_' . $delay_type . '_counter);console.log("mousemove detected" );if(!inter_lock_' . $delay_type . '){return;/*an equivalent to continue*/}++script_delay_' . $delay_type . '_counter;if(script_delay_' . $delay_type . '_counter <= max_trys_' . $delay_type . ') {inter_lock_' . $delay_type . ' = 0;' . $jquery_scope . '( this).unbind( event );console.log("unbind");' . $jquery_scope . '.getScript( url_' . $delay_type . ', function() {console.log("getscrpt call on ' . $delay_type . '" );script_delay_' . $delay_type . '_counter = max_trys_' . $delay_type . ';}).fail(function() {/*alert("loading failed" + url_' . $delay_type . ');*/console.log("loading failed in " + url_' . $delay_type . ' +" trial "+ script_delay_' . $delay_type . '_counter);inter_lock_' . $delay_type . ' = 1;});}else{/* alert("giving up");*/' . $jquery_scope . '( this).unbind( "mousemove" );console.log("failed loading "+ url_' . $delay_type . '+" giving up" );}});';
            //$inline_code = 'function loadCSS(e,n,o,t){"use strict";var d=window.document.createElement("link"),i=n||window.document.getElementsByTagName("script")[0],s=window.document.styleSheets;return d.rel="stylesheet",d.href=e,d.media="only x",t&&(d.onload=t),i.parentNode.insertBefore(d,i),d.onloadcssdefined=function(n){for(var o,t=0;t<s.length;t++)s[t].href&&s[t].href.indexOf(e)>-1&&(o=!0);o?n():setTimeout(function(){d.onloadcssdefined(n)})},d.onloadcssdefined(function(){d.media=o||"all"}),d}';
            if(2 === $params['css_groupsasync'])
            {
            	 
            	//$inline_code = 'window.MULTICACHEASYNCOLEVENT=!1,window.MULTICACHEASYNCOLLOADED=[],window.MULTICACHEASYNCOLSTACK=[],window.MULTICACHEASYNCOLLOADSTACK=[],window.MULTICACHEASYNCOLLOADSTACK_B=[],window.MULTICACHEASYNCOLCOUNTER=0;var loadCSS=function(e,n){("undefined"==typeof window.MULTICACHEASYNCNONOLEVENT&&!1===window.MULTICACHEASYNCOLEVENT||"undefined"!=typeof window.MULTICACHEASYNCNONOLEVENT&&"undefined"==typeof window.MULTICACHEASYNCNONOLEND)&&-1===window.MULTICACHEASYNCOLSTACK.indexOf(e)?(window.MULTICACHEASYNCOLSTACK.push(e),"undefined"!=typeof n&&(window.MULTICACHEASYNCOLLOADSTACK_B[e]=n)):-1===window.MULTICACHEASYNCOLLOADED.indexOf(e)&&-1===window.MULTICACHEASYNCOLSTACK.indexOf(e)?multicacheLoadSingle(e):console.log("bounced "+e)},multicacheLoadSingle=function(e,n){var o=++window.MULTICACHEASYNCOLCOUNTER,i="p_func_"+o;"undefined"==typeof window.MULTICACHEBAIL&&(window[i]={name:i+"_object",data:[],listner:function(e,n){window[i].data.ref_obj=e,window[i].CSSloaded(n+e.href+e.nodeType+e.media,window[i])},CSSloaded:function(e,n){n.initialised=1},checkMate:{checkType:function(){return typeof window[i].initialised},name:i+"_init_check"},nopromise_callback:function(){d.onload=function(){"all"!==this.media&&window[i].listner(this,"onload listener NOPROMISE 1")},d.addEventListener&&d.addEventListener("load",function(){window[i].listner(this,"onload listener NO PROMISE 2")},!1),d.onreadystatechange=function(){var e=d.readyState;("loaded"===e||"complete"===e)&&(d.onreadystatechange=null,window[i].listner(this,"onload listener NO PROMISE 3"))},multicache_MAU(window[i].nopromise_resolve,window[i].nopromise_reject,window[i].checkMate,30,void 0,void 0,i)},nopromise_resolve:function(){var e=window[i].data,n=window.MULTICACHEASYNCOLLOADSTACK_B[e.ref_obj.href];void 0!==n?multicache_MAU(window[i].callback,window[i].callback,{checkType:function(){return"undefined"}},n,1,3,i):"undefined"!=typeof e.ref_obj&&window[i].callback()},nopromise_reject:function(){console.log("in no promise reject"+i)},init:function(e,n){d.onload=function(){"all"!==this.media&&window[i].listner(this,"onload listener 1 ")},d.addEventListener&&d.addEventListener("load",function(){window[i].listner(this,"onload listener 2")},!1),d.onreadystatechange=function(){var e=d.readyState;("loaded"===e||"complete"===e)&&(d.onreadystatechange=null,window[i].listner(this,"onload listener 3"))},multicache_MAU(e,n,window[i].checkMate,30,void 0,void 0,i)},callback:function(){var e=window[i].data;"undefined"!=typeof e.ref_obj&&(e.ref_obj.media="all")},then:function(e){var n=window[i].data,o=window.MULTICACHEASYNCOLLOADSTACK_B[n.ref_obj.href];void 0!==o?multicache_MAU(window[i].callback,window[i].callback,{checkType:function(){return"undefined"}},o,1,3,i):"undefined"!=typeof n.ref_obj&&window[i].callback()},error:function(e){console.log("Promise rejected."+i),console.log(e.message);var n=window[i].data,o=window.MULTICACHEASYNCOLLOADSTACK_B[n.ref_obj.href];void 0!==o?multicache_MAU(window[i].callback,window[i].callback,{checkType:function(){return"undefined"}},o,1,3,i):"undefined"!=typeof n.ref_obj&&window[i].callback()},"catch":function(e){console.log("catch "+i),console.log("catch: ",e);var n=window[i].data,o=window.MULTICACHEASYNCOLLOADSTACK_B[n.ref_obj.href];void 0!==o?multicache_MAU(window[i].callback,window[i].callback,{checkType:function(){return"undefined"}},o,1,3,i):"undefined"!=typeof n.ref_obj&&window[i].callback()}}),n="undefined"==typeof n?document.getElementsByTagName("script")[0]:n;var d=window.document.createElement("link");if(d.rel="stylesheet",d.href=e,d.media="only x","undefined"==typeof window.MULTICACHEBAIL)if(window.Promise){window[i].data.ref_obj=d;var t="promise_"+o;window[t]=new Promise(window[i].init),window[t].then(window[i].then,window[i].error)["catch"](window[i]["catch"])}else window[i].nopromise_callback();else d.media="all";a=n.parentNode.insertBefore(d,n),window.MULTICACHEASYNCOLLOADSTACK.push(d)},loadStackMulticache=function(e){i=document.getElementsByTagName("script")[0],s=document.styleSheets;for(var n in e)multicacheLoadSingle(e[n],i),window.MULTICACHEASYNCOLLOADED.push(e[n])};!function(e,n,o){var i=function(){console.log("BAILING");var e=typeof window.MULTICACHEBAIL;if(window.MULTICACHEBAIL=1,"undefined"===e)for(var n in window.MULTICACHEASYNCOLLOADSTACK)"all"!==window.MULTICACHEASYNCOLLOADSTACK[n].media&&(window.MULTICACHEASYNCOLLOADSTACK[n].media="all")},d=function(){i(),this.removeEventListener("scroll",arguments.callee)},t=function(){i(),this.removeEventListener&&this.removeEventListener("mousemove",arguments.callee),this.detachEvent&&this.detachEvent("onmousemove",arguments.callee)},a=function(){window.MULTICACHEASYNCOLEVENT=!0,loadStackMulticache(window.MULTICACHEASYNCOLSTACK)};e.addEventListener?("undefined"==typeof window.MULTICACHEASYNCNONOLEVENT&&e.addEventListener("load",a,!1),e.addEventListener("scroll",d,!1),e.addEventListener("mousemove",t,!1)):e.attachEvent&&("undefined"==typeof window.MULTICACHEASYNCNONOLEVENT&&e.attachEvent("onload",a),e.attachEvent("onscroll",d),e.attachEvent("onmousemove",t))}(window,document,"script");';
            	$inline_code = 'window.MULTICACHEASYNCOLEVENT=!1,window.MULTICACHEASYNCOLLOADED=[],window.MULTICACHEASYNCOLSTACK=[],window.MULTICACHEASYNCOLLOADSTACK=[],window.MULTICACHEASYNCOLLOADSTACK_B=[],window.MULTICACHEASYNCOLCOUNTER=0;var loadCSS=function(e,n){("undefined"==typeof window.MULTICACHEASYNCNONOLEVENT&&!1===window.MULTICACHEASYNCOLEVENT||"undefined"!=typeof window.MULTICACHEASYNCNONOLEVENT&&"undefined"==typeof window.MULTICACHEASYNCNONOLEND)&&-1===window.MULTICACHEASYNCOLSTACK.indexOf(e)?(window.MULTICACHEASYNCOLSTACK.push(e),"undefined"!=typeof n&&(window.MULTICACHEASYNCOLLOADSTACK_B[e]=n)):-1===window.MULTICACHEASYNCOLLOADED.indexOf(e)&&-1===window.MULTICACHEASYNCOLSTACK.indexOf(e)?multicacheLoadSingle(e):console.log("bounced "+e)},multicacheLoadSingle=function(e,n){var o=++window.MULTICACHEASYNCOLCOUNTER,i="p_func_"+o;"undefined"==typeof window.MULTICACHEBAIL&&(window[i]={name:i+"_object",data:[],listner:function(e,n){window[i].data.ref_obj=e,window[i].CSSloaded(n+e.href+e.nodeType+e.media,window[i])},CSSloaded:function(e,n){n.initialised=1},checkMate:{checkType:function(){return typeof window[i].initialised},name:i+"_init_check"},nopromise_callback:function(){d.onload=function(){"all"!==this.media&&window[i].listner(this,"onload listener NOPROMISE 1")},d.addEventListener&&d.addEventListener("load",function(){window[i].listner(this,"onload listener NO PROMISE 2")},!1),d.onreadystatechange=function(){var e=d.readyState;("loaded"===e||"complete"===e)&&(d.onreadystatechange=null,window[i].listner(this,"onload listener NO PROMISE 3"))},multicache_MAU(window[i].nopromise_resolve,window[i].nopromise_reject,window[i].checkMate,30,void 0,void 0,i)},nopromise_resolve:function(){var e=window[i].data,n=window.MULTICACHEASYNCOLLOADSTACK_B[e.ref_obj.href];void 0!==n?multicache_MAU(window[i].callback,window[i].callback,{checkType:function(){return"undefined"}},n,1,3,i):"undefined"!=typeof e.ref_obj&&window[i].callback()},nopromise_reject:function(){console.log("in no promise reject"+i),window[i].gracefulExit()},init:function(e,n){d.onload=function(){"all"!==this.media&&window[i].listner(this,"onload listener 1 ")},d.addEventListener&&d.addEventListener("load",function(){window[i].listner(this,"onload listener 2")},!1),d.onreadystatechange=function(){var e=d.readyState;("loaded"===e||"complete"===e)&&(d.onreadystatechange=null,window[i].listner(this,"onload listener 3"))},multicache_MAU(e,n,window[i].checkMate,30,void 0,void 0,i)},callback:function(){var e=window[i].data;"undefined"!=typeof e.ref_obj&&(e.ref_obj.media="all")},then:function(e){var n=window[i].data,o=window.MULTICACHEASYNCOLLOADSTACK_B[n.ref_obj.href];void 0!==o?multicache_MAU(window[i].callback,window[i].callback,{checkType:function(){return"undefined"}},o,1,3,i):"undefined"!=typeof n.ref_obj&&window[i].callback()},error:function(e){console.log("Promise rejected."+i),console.log(e.message),window[i].gracefulExit()},gracefulExit:function(){console.log("graceful exit");var e=window[i].data,n=window.MULTICACHEASYNCOLLOADSTACK_B[e.ref_obj.href];void 0!==n?multicache_MAU(window[i].callback,window[i].callback,{checkType:function(){return"undefined"}},n,1,3,i):"undefined"!=typeof e.ref_obj&&window[i].callback()},"catch":function(e){console.log("catch "+i),console.log("catch: ",e),window[i].gracefulExit()}}),n="undefined"==typeof n?document.getElementsByTagName("script")[0]:n;var d=window.document.createElement("link");if(d.rel="stylesheet",d.href=e,d.media="only x","undefined"==typeof window.MULTICACHEBAIL)if(window.Promise){window[i].data.ref_obj=d;var t="promise_"+o;window[t]=new Promise(window[i].init),window[t].then(window[i].then,window[i].error)["catch"](window[i]["catch"])}else window[i].nopromise_callback();else d.media="all";a=n.parentNode.insertBefore(d,n),window.MULTICACHEASYNCOLLOADSTACK.push(d)},loadStackMulticache=function(e){i=document.getElementsByTagName("script")[0],s=document.styleSheets;for(var n in e)multicacheLoadSingle(e[n],i),window.MULTICACHEASYNCOLLOADED.push(e[n])};!function(e,n,o){var i=function(){console.log("BAILING");var e=typeof window.MULTICACHEBAIL;if(window.MULTICACHEBAIL=1,"undefined"===e)for(var n in window.MULTICACHEASYNCOLLOADSTACK)"all"!==window.MULTICACHEASYNCOLLOADSTACK[n].media&&(window.MULTICACHEASYNCOLLOADSTACK[n].media="all");"undefined"==typeof window.MULTICACHEASYNCNONOLEVENT&&!1===window.MULTICACHEASYNCOLEVENT?a():"undefined"!=typeof window.MULTICACHEASYNCNONOLEVENT&&"undefined"==typeof window.MULTICACHEASYNCNONOLEND&&loadStackMulticache(window.MULTICACHEASYNCOLSTACK)},d=function(){i(),this.removeEventListener("scroll",arguments.callee)},t=function(){i(),this.removeEventListener&&this.removeEventListener("mousemove",arguments.callee),this.detachEvent&&this.detachEvent("onmousemove",arguments.callee)},a=function(){window.MULTICACHEASYNCOLEVENT=!0,loadStackMulticache(window.MULTICACHEASYNCOLSTACK)};e.addEventListener?("undefined"==typeof window.MULTICACHEASYNCNONOLEVENT&&e.addEventListener("load",a,!1),e.addEventListener("scroll",d,!1),e.addEventListener("mousemove",t,!1)):e.attachEvent&&("undefined"==typeof window.MULTICACHEASYNCNONOLEVENT&&e.attachEvent("onload",a),e.attachEvent("onscroll",d),e.attachEvent("onmousemove",t)),function(){if("undefined"==typeof window.MULTICACHEASYNCNONOLEVENT&&!1===window.MULTICACHEASYNCOLEVENT)var e={checkType:function(){return!0!==window.MULTICACHEASYNCOLEVENT?"undefined":1},name:"multicache_system_ol"};else if("undefined"!=typeof window.MULTICACHEASYNCNONOLEVENT&&"undefined"==typeof window.MULTICACHEASYNCNONOLEND)var e={checkType:function(){return typeof window.MULTICACHEASYNCNONOLEND},name:"multicache_system_async"};if(window.Promise&&"undefined"!=typeof e){var n=new Promise(function(n,o){multicache_MAU(n,o,e,30,void 0,100,e.name)});n.then(function(){})["catch"](i)}else"undefined"!=typeof e&&multicache_MAU(function(){},i,e,30,void 0,100,e.name)}()}(window,document,"script");';
            }
            else
            {
            	 
            	//$inline_code = 'window.MULTICACHEASYNCNONOLEVENT=!0,window.MULTICACHEASYNCOLEVENT=!1,window.MULTICACHEASYNCOLLOADED=[],window.MULTICACHEASYNCOLSTACK=[],window.MULTICACHEASYNCOLLOADSTACK=[],window.MULTICACHEASYNCOLLOADSTACK_B=[],window.MULTICACHEASYNCOLCOUNTER=0;var loadCSS=function(e,n){("undefined"==typeof window.MULTICACHEASYNCNONOLEVENT&&!1===window.MULTICACHEASYNCOLEVENT||"undefined"!=typeof window.MULTICACHEASYNCNONOLEVENT&&"undefined"==typeof window.MULTICACHEASYNCNONOLEND)&&-1===window.MULTICACHEASYNCOLSTACK.indexOf(e)?(window.MULTICACHEASYNCOLSTACK.push(e),"undefined"!=typeof n&&(window.MULTICACHEASYNCOLLOADSTACK_B[e]=n)):-1===window.MULTICACHEASYNCOLLOADED.indexOf(e)&&-1===window.MULTICACHEASYNCOLSTACK.indexOf(e)?multicacheLoadSingle(e):console.log("bounced "+e)},multicacheLoadSingle=function(e,n){var o=++window.MULTICACHEASYNCOLCOUNTER,i="p_func_"+o;"undefined"==typeof window.MULTICACHEBAIL&&(window[i]={name:i+"_object",data:[],listner:function(e,n){window[i].data.ref_obj=e,window[i].CSSloaded(n+e.href+e.nodeType+e.media,window[i])},CSSloaded:function(e,n){n.initialised=1},checkMate:{checkType:function(){return typeof window[i].initialised},name:i+"_init_check"},nopromise_callback:function(){d.onload=function(){"all"!==this.media&&window[i].listner(this,"onload listener NOPROMISE 1")},d.addEventListener&&d.addEventListener("load",function(){window[i].listner(this,"onload listener NO PROMISE 2")},!1),d.onreadystatechange=function(){var e=d.readyState;("loaded"===e||"complete"===e)&&(d.onreadystatechange=null,window[i].listner(this,"onload listener NO PROMISE 3"))},multicache_MAU(window[i].nopromise_resolve,window[i].nopromise_reject,window[i].checkMate,30,void 0,void 0,i)},nopromise_resolve:function(){var e=window[i].data,n=window.MULTICACHEASYNCOLLOADSTACK_B[e.ref_obj.href];void 0!==n?multicache_MAU(window[i].callback,window[i].callback,{checkType:function(){return"undefined"}},n,1,3,i):"undefined"!=typeof e.ref_obj&&window[i].callback()},nopromise_reject:function(){console.log("in no promise reject"+i)},init:function(e,n){d.onload=function(){"all"!==this.media&&window[i].listner(this,"onload listener 1 ")},d.addEventListener&&d.addEventListener("load",function(){window[i].listner(this,"onload listener 2")},!1),d.onreadystatechange=function(){var e=d.readyState;("loaded"===e||"complete"===e)&&(d.onreadystatechange=null,window[i].listner(this,"onload listener 3"))},multicache_MAU(e,n,window[i].checkMate,30,void 0,void 0,i)},callback:function(){var e=window[i].data;"undefined"!=typeof e.ref_obj&&(e.ref_obj.media="all")},then:function(e){var n=window[i].data,o=window.MULTICACHEASYNCOLLOADSTACK_B[n.ref_obj.href];void 0!==o?multicache_MAU(window[i].callback,window[i].callback,{checkType:function(){return"undefined"}},o,1,3,i):"undefined"!=typeof n.ref_obj&&window[i].callback()},error:function(e){console.log("Promise rejected."+i),console.log(e.message);var n=window[i].data,o=window.MULTICACHEASYNCOLLOADSTACK_B[n.ref_obj.href];void 0!==o?multicache_MAU(window[i].callback,window[i].callback,{checkType:function(){return"undefined"}},o,1,3,i):"undefined"!=typeof n.ref_obj&&window[i].callback()},"catch":function(e){console.log("catch "+i),console.log("catch: ",e);var n=window[i].data,o=window.MULTICACHEASYNCOLLOADSTACK_B[n.ref_obj.href];void 0!==o?multicache_MAU(window[i].callback,window[i].callback,{checkType:function(){return"undefined"}},o,1,3,i):"undefined"!=typeof n.ref_obj&&window[i].callback()}}),n="undefined"==typeof n?document.getElementsByTagName("script")[0]:n;var d=window.document.createElement("link");if(d.rel="stylesheet",d.href=e,d.media="only x","undefined"==typeof window.MULTICACHEBAIL)if(window.Promise){window[i].data.ref_obj=d;var t="promise_"+o;window[t]=new Promise(window[i].init),window[t].then(window[i].then,window[i].error)["catch"](window[i]["catch"])}else window[i].nopromise_callback();else d.media="all";a=n.parentNode.insertBefore(d,n),window.MULTICACHEASYNCOLLOADSTACK.push(d)},loadStackMulticache=function(e){i=document.getElementsByTagName("script")[0],s=document.styleSheets;for(var n in e)multicacheLoadSingle(e[n],i),window.MULTICACHEASYNCOLLOADED.push(e[n])};!function(e,n,o){var i=function(){console.log("BAILING");var e=typeof window.MULTICACHEBAIL;if(window.MULTICACHEBAIL=1,"undefined"===e)for(var n in window.MULTICACHEASYNCOLLOADSTACK)"all"!==window.MULTICACHEASYNCOLLOADSTACK[n].media&&(window.MULTICACHEASYNCOLLOADSTACK[n].media="all")},d=function(){i(),this.removeEventListener("scroll",arguments.callee)},t=function(){i(),this.removeEventListener&&this.removeEventListener("mousemove",arguments.callee),this.detachEvent&&this.detachEvent("onmousemove",arguments.callee)},a=function(){window.MULTICACHEASYNCOLEVENT=!0,loadStackMulticache(window.MULTICACHEASYNCOLSTACK)};e.addEventListener?("undefined"==typeof window.MULTICACHEASYNCNONOLEVENT&&e.addEventListener("load",a,!1),e.addEventListener("scroll",d,!1),e.addEventListener("mousemove",t,!1)):e.attachEvent&&("undefined"==typeof window.MULTICACHEASYNCNONOLEVENT&&e.attachEvent("onload",a),e.attachEvent("onscroll",d),e.attachEvent("onmousemove",t))}(window,document,"script");';
            	$inline_code = 'window.MULTICACHEASYNCNONOLEVENT=!0,window.MULTICACHEASYNCOLEVENT=!1,window.MULTICACHEASYNCOLLOADED=[],window.MULTICACHEASYNCOLSTACK=[],window.MULTICACHEASYNCOLLOADSTACK=[],window.MULTICACHEASYNCOLLOADSTACK_B=[],window.MULTICACHEASYNCOLCOUNTER=0;var loadCSS=function(e,n){("undefined"==typeof window.MULTICACHEASYNCNONOLEVENT&&!1===window.MULTICACHEASYNCOLEVENT||"undefined"!=typeof window.MULTICACHEASYNCNONOLEVENT&&"undefined"==typeof window.MULTICACHEASYNCNONOLEND)&&-1===window.MULTICACHEASYNCOLSTACK.indexOf(e)?(window.MULTICACHEASYNCOLSTACK.push(e),"undefined"!=typeof n&&(window.MULTICACHEASYNCOLLOADSTACK_B[e]=n)):-1===window.MULTICACHEASYNCOLLOADED.indexOf(e)&&-1===window.MULTICACHEASYNCOLSTACK.indexOf(e)?multicacheLoadSingle(e):console.log("bounced "+e)},multicacheLoadSingle=function(e,n){var o=++window.MULTICACHEASYNCOLCOUNTER,i="p_func_"+o;"undefined"==typeof window.MULTICACHEBAIL&&(window[i]={name:i+"_object",data:[],listner:function(e,n){window[i].data.ref_obj=e,window[i].CSSloaded(n+e.href+e.nodeType+e.media,window[i])},CSSloaded:function(e,n){n.initialised=1},checkMate:{checkType:function(){return typeof window[i].initialised},name:i+"_init_check"},nopromise_callback:function(){d.onload=function(){"all"!==this.media&&window[i].listner(this,"onload listener NOPROMISE 1")},d.addEventListener&&d.addEventListener("load",function(){window[i].listner(this,"onload listener NO PROMISE 2")},!1),d.onreadystatechange=function(){var e=d.readyState;("loaded"===e||"complete"===e)&&(d.onreadystatechange=null,window[i].listner(this,"onload listener NO PROMISE 3"))},multicache_MAU(window[i].nopromise_resolve,window[i].nopromise_reject,window[i].checkMate,30,void 0,void 0,i)},nopromise_resolve:function(){var e=window[i].data,n=window.MULTICACHEASYNCOLLOADSTACK_B[e.ref_obj.href];void 0!==n?multicache_MAU(window[i].callback,window[i].callback,{checkType:function(){return"undefined"}},n,1,3,i):"undefined"!=typeof e.ref_obj&&window[i].callback()},nopromise_reject:function(){console.log("in no promise reject"+i),window[i].gracefulExit()},init:function(e,n){d.onload=function(){"all"!==this.media&&window[i].listner(this,"onload listener 1 ")},d.addEventListener&&d.addEventListener("load",function(){window[i].listner(this,"onload listener 2")},!1),d.onreadystatechange=function(){var e=d.readyState;("loaded"===e||"complete"===e)&&(d.onreadystatechange=null,window[i].listner(this,"onload listener 3"))},multicache_MAU(e,n,window[i].checkMate,30,void 0,void 0,i)},callback:function(){var e=window[i].data;"undefined"!=typeof e.ref_obj&&(e.ref_obj.media="all")},then:function(e){var n=window[i].data,o=window.MULTICACHEASYNCOLLOADSTACK_B[n.ref_obj.href];void 0!==o?multicache_MAU(window[i].callback,window[i].callback,{checkType:function(){return"undefined"}},o,1,3,i):"undefined"!=typeof n.ref_obj&&window[i].callback()},error:function(e){console.log("Promise rejected."+i),console.log(e.message),window[i].gracefulExit()},gracefulExit:function(){console.log("graceful exit");var e=window[i].data,n=window.MULTICACHEASYNCOLLOADSTACK_B[e.ref_obj.href];void 0!==n?multicache_MAU(window[i].callback,window[i].callback,{checkType:function(){return"undefined"}},n,1,3,i):"undefined"!=typeof e.ref_obj&&window[i].callback()},"catch":function(e){console.log("catch "+i),console.log("catch: ",e),window[i].gracefulExit()}}),n="undefined"==typeof n?document.getElementsByTagName("script")[0]:n;var d=window.document.createElement("link");if(d.rel="stylesheet",d.href=e,d.media="only x","undefined"==typeof window.MULTICACHEBAIL)if(window.Promise){window[i].data.ref_obj=d;var t="promise_"+o;window[t]=new Promise(window[i].init),window[t].then(window[i].then,window[i].error)["catch"](window[i]["catch"])}else window[i].nopromise_callback();else d.media="all";a=n.parentNode.insertBefore(d,n),window.MULTICACHEASYNCOLLOADSTACK.push(d)},loadStackMulticache=function(e){i=document.getElementsByTagName("script")[0],s=document.styleSheets;for(var n in e)multicacheLoadSingle(e[n],i),window.MULTICACHEASYNCOLLOADED.push(e[n])};!function(e,n,o){var i=function(){console.log("BAILING");var e=typeof window.MULTICACHEBAIL;if(window.MULTICACHEBAIL=1,"undefined"===e)for(var n in window.MULTICACHEASYNCOLLOADSTACK)"all"!==window.MULTICACHEASYNCOLLOADSTACK[n].media&&(window.MULTICACHEASYNCOLLOADSTACK[n].media="all");"undefined"==typeof window.MULTICACHEASYNCNONOLEVENT&&!1===window.MULTICACHEASYNCOLEVENT?a():"undefined"!=typeof window.MULTICACHEASYNCNONOLEVENT&&"undefined"==typeof window.MULTICACHEASYNCNONOLEND&&loadStackMulticache(window.MULTICACHEASYNCOLSTACK)},d=function(){i(),this.removeEventListener("scroll",arguments.callee)},t=function(){i(),this.removeEventListener&&this.removeEventListener("mousemove",arguments.callee),this.detachEvent&&this.detachEvent("onmousemove",arguments.callee)},a=function(){window.MULTICACHEASYNCOLEVENT=!0,loadStackMulticache(window.MULTICACHEASYNCOLSTACK)};e.addEventListener?("undefined"==typeof window.MULTICACHEASYNCNONOLEVENT&&e.addEventListener("load",a,!1),e.addEventListener("scroll",d,!1),e.addEventListener("mousemove",t,!1)):e.attachEvent&&("undefined"==typeof window.MULTICACHEASYNCNONOLEVENT&&e.attachEvent("onload",a),e.attachEvent("onscroll",d),e.attachEvent("onmousemove",t)),function(){if("undefined"==typeof window.MULTICACHEASYNCNONOLEVENT&&!1===window.MULTICACHEASYNCOLEVENT)var e={checkType:function(){return!0!==window.MULTICACHEASYNCOLEVENT?"undefined":1},name:"multicache_system_ol"};else if("undefined"!=typeof window.MULTICACHEASYNCNONOLEVENT&&"undefined"==typeof window.MULTICACHEASYNCNONOLEND)var e={checkType:function(){return typeof window.MULTICACHEASYNCNONOLEND},name:"multicache_system_async"};if(window.Promise&&"undefined"!=typeof e){var n=new Promise(function(n,o){multicache_MAU(n,o,e,30,void 0,100,e.name)});n.then(function(){})["catch"](i)}else"undefined"!=typeof e&&multicache_MAU(function(){},i,e,30,void 0,100,e.name)}()}(window,document,"script");';
            }
            
        }
        else
        {
            self::prepareMessageEnqueue(__('Css delay met an unlisted delay type'), 'error');
            
        }

        $obj["code"] = serialize($inline_code);
        $obj["url"] = $name;

        Return $obj;

    }

    public static function get_web_page($url , $docurl = false)
    {

        if (strpos($url, '//') === 0)
        {
            $url = 'http:' . $url;
        }

        
        
            if (function_exists('wp_remote_get') && empty($docurl) )
            {


                $ret = wp_remote_get($url , array('timeout' => 60));
                if(is_wp_error($ret))
                {
                	error_log('wp_remote_get scrape error -'.$ret->get_error_message().' url-'.$url, 3 ,MULTICACHE_ERROR_LOGS.'multicache_helper_errors.log');
                	Return false;
                }
                
                $page = array();
                if (! empty($ret))
                {
                    foreach ($ret as $key => $obj)
                    {
                        if ($key == 'body')
                        {
                            $key_name = 'content';
                        }
                        elseif ($key == 'headers')
                        {
                        	$page["content-type"] = $obj["content-type"];
                        	continue;
                        }
                        elseif ($key == 'response')
                        {
                            $key_name = 'http_code';
                            $page[$key_name] = $obj["code"];
                            $page["errmsg"] = $obj["message"];
                            continue;
                        }
                        else
                        {
                            $key_name = $key;
                        }

                        $page[$key_name] = $obj;
                        
                    }

                    Return $page;
                }
            }
            
          
            
            if ( function_exists('curl_version') )
            {
        $user_agent = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';

        $options = array(

            CURLOPT_CUSTOMREQUEST => "GET", // set request type post or get
            CURLOPT_POST => false, // set to GET
            CURLOPT_USERAGENT => $user_agent, // set user agent
            CURLOPT_COOKIEFILE => "cookie.txt", // set cookie file
            CURLOPT_COOKIEJAR => "cookie.txt", // set cookie jar
            CURLOPT_RETURNTRANSFER => true, // return web page
            CURLOPT_HEADER => false, // don't return headers
            CURLOPT_FOLLOWLOCATION => true, // follow redirects
            CURLOPT_ENCODING => "gzip,deflate", // handle only gzip & defalte encodings Joomla3
            CURLOPT_AUTOREFERER => true, // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120, // timeout on connect
            CURLOPT_TIMEOUT => 120, // timeout on response
            CURLOPT_MAXREDIRS => 10
        ); // stop after 10 redirects

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);
        curl_close($ch);

        $header['errno'] = $err;
        $header['errmsg'] = $errmsg;
        $header['content'] = $content;
        return $header;
            }
            else
            {
            	error_log('CURL_DOESNOTEXIST function wp_remote_get does not exist', 3 ,MULTICACHE_ERROR_LOGS.'multicache_helper_errors.log');
            	Return false;
            }

    }
    
    public static function processSocialAdIndicators($social)
    {
    	Return json_encode(preg_split('/[\s,\n]+/', self::validate_textbox($social))) ;
    	 
    }
/*
    public static function establish_factors($precache_factor, $gzip_factor)
    {

        require_once (JPATH_CONFIGURATION . '/configuration.php');
        // Create a JConfig object
        $config = new JConfig();
        $config->gzip_factor = $gzip_factor;
        $config->precache_factor = $precache_factor;
        $registry = new Registry();
        $registry->loadObject($config);
        self::writeConfigFile($registry);

    }
*/
    public static function writeToConfig( $config , 
    		$init = false , $bypass_multi = false ,
    		$tag = false, $dir = false , $filename = false 
    		, $config_path = false , $namespace = false)
    {
    	      
        $dir = $dir === false ? plugin_dir_path(dirname(__FILE__)).'libs' : $dir;
        $filename = $filename === false? 'multicache_config.php': $filename;
        if(is_multisite() && !$bypass_multi)
        {
        	$object = self::resolveLocMul($dir , $filename);
        	
        	if(empty($object))
        	{
        		Return false;
        	}
        	foreach($object As $key => $path)
        	{
        		$a = self::writeToConfig($config, $init,  true,$path['tag'] , $path['path'] , $filename);
        
        	}
        	
        	Return;
        }
        else {
        	$dir = $dir .'/';
        }
        /*
        echo "<br>main<br>";
        echo $dir.$filename;
        echo "<br>tag  ";
        */
        //config path resolution
        $config_path = false === $config_path? $dir.$filename :$config_path; 
       // var_dump($tag);
        
        
        require_once (ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
        require_once (ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');
        $multicache_fsd = new WP_Filesystem_Direct(__FILE__);
        @set_time_limit(300);
        $check_is_writable = array();
       $is_writable =  $multicache_fsd->chmod($config_path, 0644);
       if(!empty(self::$_debug))
       {
       	self::log_error('Is writable flag test','config_write',$is_writable);
       }
        /*
        if ($multicache_fsd->exists($dir . '/libs/multicache_config.php'))
        {
            Return;
        }
        */
        $config_vars = $config;
        $class_name = empty($tag)? 'MulticacheConfig' : 'MulticacheConfig' . $tag;
        $class_name = !empty($namespace)? $namespace :$class_name;
        ob_start();
        echo "<?php

/**
 * MulticacheWP
 * http://www.multicache.org
 * High Performance fastcache Controller
 * Version: MU1.0.0.2
 * Author: Wayne DSouza
 * Author URI: http://onlinemarketingconsultants.in
 * License: GNU PUBLIC LICENSE see license.txt
 */
defined('_MULTICACHEWP_EXEC') or die();

class $class_name
 {
        ";
        $cl_buf = ob_get_clean();
        foreach ($config_vars as $config_keys => $config_val)
        {
            if($init){
        	if ($config_keys == 'secret')
            {
                $config_val = md5(MulticacheUri::root() . date('Y-m-d', strtotime('-1 year')));
            }
            
            elseif ($config_keys == 'live_site')
            {
                $config_val = MulticacheUri::root();
            }
            }
            if(isset($config_val)):
            ob_start();
            echo "\npublic \$$config_keys = '$config_val';";
            $cl_buf .= ob_get_clean();
            elseif(is_null($config_val)):
            ob_start();
            echo "\npublic \$$config_keys = null;";
            $cl_buf .= ob_get_clean();
            endif;
        }
        ob_start();
        echo "

 }";
        $cl_buf .= ob_get_clean();
        $cl_buf = str_ireplace("\x0D", "", $cl_buf);
        /*
       echo "<br>Writing to config<br>";
        var_dump($config_path);
        echo "<br><br>";
        var_dump($config);
        exit;
        */
        
       
        if (! $multicache_fsd->put_contents($config_path, $cl_buf, 0644))
        {
            $result = new WP_Error('failed to write multicache config', __('Multicacheconfig could not install this is usually due to inconsistent file permissions.'), $config_path);
            return $result;
        }
        
        $multicache_fsd->chmod($config_path, 0444);
        if ($multicache_fsd->getchmod($config_path) == '444')
        {
            Return true;
        }
        
        Return false;
    

    }
    
    
    public static function writeToConfigMultisite( $config , $path , $namespace = false)
    {
    	 
   // echo "<h2>In Multisite writer</h2>";
   /* $dir = str_replace('multicache_config.php','', $path);
    if(is_dir($dir))
    {
    	echo "is directory".$dir; 
    }
    else {
    	echo "NOT directory".$dir;
    }*/
    
    
    	require_once (ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
    	require_once (ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');
    	$multicache_fsd = new WP_Filesystem_Direct(__FILE__);
    	@set_time_limit(300);
    	$check_is_writable = array();
    	$is_writable =  $multicache_fsd->chmod($path , 0644);
    	//echo "<br>is writable</br>";
    	//var_dump($is_writable);
    	if(!empty(self::$_debug))
    	{
    		self::log_error('Multisite Is writable flag test','config_write',$is_writable);
    	}
    	
    	$config_vars = $config;
    	$class_name = empty($namespace)? 'MulticacheConfig' : $namespace;
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
    	* newtork admin config
    	*/
    	defined('_MULTICACHEWP_EXEC') or die();
    
    	class $class_name
    	{
    	";
    	$cl_buf = ob_get_clean();
    	foreach ($config_vars as $config_keys => $config_val)
    	{
    		if($init){
    			if ($config_keys == 'secret')
    			{
    				$config_val = md5(MulticacheUri::root() . date('Y-m-d', strtotime('-1 year')));
    			}
    
    			elseif ($config_keys == 'live_site')
    			{
    				$config_val = MulticacheUri::root();
    			}
    		}
    		if(isset($config_val)):
    		ob_start();
    		echo "\npublic \$$config_keys = '$config_val';";
    		$cl_buf .= ob_get_clean();
    		elseif(is_null($config_val)):
    		ob_start();
    		echo "\npublic \$$config_keys = null;";
    		$cl_buf .= ob_get_clean();
    		endif;
    	}
    	ob_start();
    	echo "
    
 }";
    	$cl_buf .= ob_get_clean();
    	$cl_buf = str_ireplace("\x0D", "", $cl_buf);
    	
    	$filename = 'multicache_config.php';
    	if (! $multicache_fsd->put_contents($path, $cl_buf, 0644))
    	{
    		$result = new WP_Error('failed to write multicache config', __('Multicacheconfig could not install this is usually due to inconsistent file permissions.'), $path);
    		return $result;
    	}
    
    	$multicache_fsd->chmod($path, 0444);
    	if ($multicache_fsd->getchmod($path) == '444')
    	{
    		Return true;
    	}
    
    	Return false;
    
    
    }

    public static function clean_cache($group = null, $id = null , $user = 0)
    {

        $id2 = substr($id, - 1) != '/' ? $id . '/' : null;
        $id3 = substr($id, - 1) == '/' ? substr_replace($id, '', - 1) : null;

        $cache = self::getCache()->cache;
        if (! empty($group))
        {
            $cache->clean($group, 'both');
            // $cache->clean($group . '_file_cache');
        }
        if (! empty($id))
        {
            $cache->remove($id, 'page');
            // two variants
            if (isset($id2))
            {
                $cache->remove($id2, 'page');
            }
            if (isset($id3))
            {
                $cache->remove($id3, 'page');
            }
        }
       // $cache->clean('_system', 'both');
       // $cache->clean('com_config', 'both');
       /*
        $cache = self::getCache(true);
        if (! empty($group))
        {
            $cache->clean($group, 'both');
            // $cache->clean($group . '_file_cache');
        }
        if (! empty($id))
        {
            $cache->remove($id, 'page');
            // two variants
            if (isset($id2))
            {
                $cache->remove($id2, 'page');
            }
            if (isset($id3))
            {
                $cache->remove($id3, 'page');
            }
        }
        $cache->clean('_system', 'both');
        $cache->clean('com_config', 'both');
        */

    }

    public static function registerLOGnormal($u)
    {

        if (empty($u))
        {
            Return false;
        }
       // $app = JFactory::getApplication();
        $u = preg_replace('/\s/', '', var_export($u, true));
        $u = str_replace(',)', ')', $u);
        if(is_multisite())
        {
        	$namespace = self::resolveMultisiteStrategyNamespace()->namespace;
        	
        }
        $class_name = isset($namespace)? 'MulticacheUrlArray'.$namespace:'MulticacheUrlArray'; 
        ob_start();
        echo "<?php


/**
 * MulticacheWP
 * http://www.multicache.org
 * High Performance fastcache Controller
 * Version: MU 1.0.0.2
 * Author: Wayne DSouza
 * Author URI: http://onlinemarketingconsultants.in
 * License: GNU PUBLIC LICENSE see license.txt
 */


defined('_MULTICACHEWP_EXEC') or die;



class $class_name{

public static \$urls = " . trim($u) . ";


}
?>";
        $cl_buf = serialize(ob_get_clean());
        $dir = dirname(plugin_dir_path(__FILE__)).'/libs';
        $filename = 'multicacheurlarray.php';
        $success = self::writefileTolocation($dir, $filename, $cl_buf);
        if ($success)
        {
        	self::prepareMessageEnqueue(__('Successfully registered Urls','multicache-plugin') ,'updated');
            //$app->enqueueMessage(JText::_('COM_MULTICACHE_REGISTERURLARRAY_SUCCESS_MESSAGE'), 'message');
        }
        else
        {
        	self::prepareMessageEnqueue(__("Registering Url's failed","multicache-plugin"));
           // $app->enqueueMessage(JText::_('COM_MULTICACHE_REGISTERURLARRAY_FAILED_MESSAGE'), 'warning');
        }
        Return $success;

    }

    public static function storePageCss($u = null, $v = null, $duplicates = null, $delayed = null)
    {

        $original_set = isset($u) ? true : null;

        if (empty($u))
        {
            if (! class_exists('MulticachePageCss'))
            {
                Return false;
            }
            if (property_exists('MulticachePageCss', 'original_css_array'))
            {
                $u = MulticachePageCss::$original_css_array;
            }
        }
        $u = var_export($u, true); // the original script array -> to be used only when the working script array is not present-> no variables should be changed from the time of scrping
        if (! $original_set)
        {
            $v = self::setinitialiseScriptpeice($v, 'working_css_array', 'MulticachePageCss');
            $duplicates = self::setinitialiseScriptpeice($duplicates, 'duplicates', 'MulticachePageCss');

            $async = self::setinitialiseScriptpeice($async, 'async', 'MulticachePageCss');
            $delayed = self::setinitialiseScriptpeice($delayed, 'delayed', 'MulticachePageCss');
        }

        ob_start();
        echo "<?php

/**
 * MulticacheWP
 * http://www.multicache.org
 * High Performance fastcache Controller
 * Version: MU 1.0.0.2
 * Author: Wayne DSouza
 * Author URI: http://onlinemarketingconsultants.in
 * License: GNU PUBLIC LICENSE see license.txt
 */


defined('_MULTICACHEWP_EXEC') or die;
class MulticachePageCss{

public static \$original_css_array  = " . trim($u) . ";

";
        $cl_buf = ob_get_clean();
        if (! empty($v) && ! $original_set)
        {
            ob_start();
            echo "


public static \$working_css_array  = " . trim($v) . ";

";
            $cl_buf .= ob_get_clean();
        }
        // start duplicate writerender
        if (! empty($duplicates) && ! $original_set)
        {

            ob_start();
            echo "


public static \$duplicates  = " . trim($duplicates) . ";

";
            $cl_buf .= ob_get_clean();
        }
        // end duplicate writerender

        // start social writerender
        if (! empty($social) && ! $original_set)
        {

            ob_start();
            echo "


public static \$social  = " . trim($social) . ";

";
            $cl_buf .= ob_get_clean();
        }
        // end social writerender

        // start advertisements writerender
        if (! empty($advertisements) && ! $original_set)
        {

            ob_start();
            echo "


public static \$advertisements  = " . trim($advertisements) . ";

";
            $cl_buf .= ob_get_clean();
        }
        // end advertisements writerender

        // start async writerender
        if (! empty($async) && ! $original_set)
        {

            ob_start();
            echo "


public static \$async  = " . trim($async) . ";

";
            $cl_buf .= ob_get_clean();
        }
        // end async writerender

        // start delayed writerender
        if (! empty($delayed) && ! $original_set)
        {

            ob_start();
            echo "


public static \$delayed  = " . trim($delayed) . ";

";
            $cl_buf .= ob_get_clean();
        }
        // end delayed writerender

        // closing tags
        ob_start();
        echo "
        }
?> ";
        $cl_buf .= ob_get_clean();
        $cl_buf = serialize(trim($cl_buf));
       $dir = dirname(plugin_dir_path(__FILE__)).'/libs';
        $filename = 'pagecss.php';
        $success = self::writefileTolocation($dir, $filename, $cl_buf);
        
        Return $success;

    }
//version1.0.0.2, $dontmove = null
    public static function storePageScripts($u = null, $v = null, $duplicates = null, $social = null, $advertisements = null, $async = null, $delayed = null, $dontmove = null)
    {

        $original_set = isset($u) ? true : null;

        if (empty($u))
        {
            if (! class_exists('MulticachePageScripts'))
            {
                Return false;
            }
            if (property_exists('MulticachePageScripts', 'original_script_array'))
            {
                $u = MulticachePageScripts::$original_script_array;
            }
        }
        $u = var_export($u, true); // the original script array -> to be used only when the working script array is not present-> no variables should be changed from the time of scrping
        if (! $original_set)
        {
            $v = self::setinitialiseScriptpeice($v, 'working_script_array');
            $duplicates = self::setinitialiseScriptpeice($duplicates, 'duplicates');
            $social = self::setinitialiseScriptpeice($social, 'social');
            $advertisements = self::setinitialiseScriptpeice($advertisements, 'advertisements');
            $async = self::setinitialiseScriptpeice($async, 'async');
            $delayed = self::setinitialiseScriptpeice($delayed, 'delayed');
            //version1.0.0.2
            $dontmove = self::setinitialiseScriptpeice($dontmove, 'dontmove');
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
 * Please make changes in the control panel. This class may be overwritten
 */


defined('_MULTICACHEWP_EXEC') or die;
class MulticachePageScripts{

public static \$original_script_array  = " . trim($u) . ";

";
        $cl_buf = ob_get_clean();
        if (! empty($v) && ! $original_set)
        {
            ob_start();
            echo "


public static \$working_script_array  = " . trim($v) . ";

";
            $cl_buf .= ob_get_clean();
        }
        // start duplicate writerender
        if (! empty($duplicates) && ! $original_set)
        {

            ob_start();
            echo "


public static \$duplicates  = " . trim($duplicates) . ";

";
            $cl_buf .= ob_get_clean();
        }
        // end duplicate writerender

        // start social writerender
        if (! empty($social) && ! $original_set)
        {

            ob_start();
            echo "


public static \$social  = " . trim($social) . ";

";
            $cl_buf .= ob_get_clean();
        }
        // end social writerender

        // start advertisements writerender
        if (! empty($advertisements) && ! $original_set)
        {

            ob_start();
            echo "


public static \$advertisements  = " . trim($advertisements) . ";

";
            $cl_buf .= ob_get_clean();
        }
        // end advertisements writerender

        // start async writerender
        if (! empty($async) && ! $original_set)
        {

            ob_start();
            echo "


public static \$async  = " . trim($async) . ";

";
            $cl_buf .= ob_get_clean();
        }
        // end async writerender

        // start delayed writerender
        if (! empty($delayed) && ! $original_set)
        {

            ob_start();
            echo "


public static \$delayed  = " . trim($delayed) . ";

";
            $cl_buf .= ob_get_clean();
        }
        // end delayed writerender
//version1.0.0.2
        // start dontmove writerender
        if (! empty($dontmove) && ! $original_set)
        {
        
        	ob_start();
        	echo "
        
        
public static \$dontmove  = " . trim($dontmove) . ";
        
";
        	$cl_buf .= ob_get_clean();
        }
        // end delayed writerender
        // closing tags
        ob_start();
        echo "
        }
 ";
        $cl_buf .= ob_get_clean();
        $cl_buf = serialize(trim($cl_buf));
        $dir = dirname(plugin_dir_path(__FILE__)).'/libs';
       
        $filename = 'pagescripts.php';
        $success = self::writefileTolocation($dir, $filename, $cl_buf);
        // $success = self::writePageScripts(serialize($cl_buf));
        Return $success;

    }

    public static function writeJsCache($obj, $filename, $tblswitch = 1)
    {

        //$app = JFactory::getApplication();
        //$dir = JPATH_SITE . '/media/com_multicache/assets/js/jscache'; // JPATH_ADMINISTRATOR .
    	$dir =  plugin_dir_path(dirname(__FILE__)).'delivery/assets/js/jscache';
        if (! is_dir($dir))
        {

            // Make sure the index file is there
            $indexFile = $dir . '/index.html';
            @mkdir($dir) && file_put_contents($indexFile, '<!DOCTYPE html><title></title>');
        }
        if (! is_dir($dir))
        {
            Return false;
        }
if(is_multisite())
{
	$object = self::resolveLocMul($dir , $filename);

	$link = plugins_url( str_replace(dirname(dirname(__FILE__)).'/','' ,$object[path][path] ).'/'. $filename . '?mediaFormat=' . self::getMediaFormat(), dirname(__FILE__));
	
	$alink = '<a href="' . $link . '" target="_blank" class="btn btn-mini btn-success" title="click to open in new window">' . $filename . '</a>';
}
else {
       $link = plugins_url( 'delivery/assets/js/jscache/'. $filename . '?mediaFormat=' . self::getMediaFormat(), dirname(__FILE__));
       $alink = '<a href="' . $link . '" target="_blank" class="btn btn-mini btn-success" title="click to open in new window">' . $filename . '</a>';
}

        $cl_buf = serialize($obj);
        $success = self::writefileTolocation($dir, $filename, $cl_buf);
        if ($success && $tblswitch)
        {
        	self::prepareMessageEnqueue(__('Succesfully created optimized js file','multicache-plugin'). $alink,'updated');
            //$app->enqueueMessage(JText::_('COM_MULTICACHE_JS_CACHE_WRITE_SUCCESS_MESSAGE') . '  	' . $alink, 'message');
            return true;
        }
        Return false;

    }

    public static function writeCssCache($obj, $filename, $tblswitch = 1)
    {

        //$app = JFactory::getApplication();
        //$dir = JPATH_SITE . '/media/com_multicache/assets/css/csscache'; // JPATH_ADMINISTRATOR .
    	$dir =  plugin_dir_path(dirname(__FILE__)).'delivery/assets/css/csscache';
        if (! is_dir($dir))
        {

            // Make sure the index file is there
            $indexFile = $dir . '/index.html';
            @mkdir($dir) && file_put_contents($indexFile, '<!DOCTYPE html><title></title>');
        }
        if (! is_dir($dir))
        {
        	if(!empty(self::$_debug))
        	{
        		self::log_error(__('Could not create css delivery directory','multicache-plugin'),'delivery');
        	}
            Return false;
        }
        if(is_multisite())
        {
        	$object = self::resolveLocMul($dir , $filename);
        
        	$link = plugins_url( str_replace(dirname(dirname(__FILE__)).'/','' ,$object[path][path] ).'/'. $filename . '?mediaFormat=' . self::getMediaFormat(), dirname(__FILE__));
        
        	$alink = '<a href="' . $link . '" target="_blank" class="btn btn-mini btn-success" title="click to open in new window">' . $filename . '</a>';
        }
        else {
         $link = plugins_url( 'delivery/assets/css/csscache/'. $filename . '?mediaFormat=' . self::getMediaFormat(), dirname(__FILE__));
       // $link = JURI::root() . 'media/com_multicache/assets/css/csscache/' . $filename . '?mediaFormat=' . self::getMediaFormat();

        $alink = '<a href="' . $link . '" target="_blank" class="btn btn-mini btn-success" title="click to open in new window">' . $filename . '</a>';
        	
        }
       
        $cl_buf = serialize($obj);
        $success = self::writefileTolocation($dir, $filename, $cl_buf);
        if ($success && $tblswitch)
        {
        	self::prepareMessageEnqueue(__('Succesfully created optimized css file','multicache-plugin'). $alink,'updated');
            //$app->enqueueMessage(JText::_('COM_MULTICACHE_CSS_CACHE_WRITE_SUCCESS_MESSAGE') . '  	' . $alink, 'message');
            return true;
        }
        Return false;

    }

    public static function writeJsCacheStrategy($signature_hash, $loadsection, $switch = null, $stubs = null, $JSTexclude = null, $signature_hash_css, $loadsection_css, $switch_css, $CSSexclude = null, $switch_img, $IMGexclude, $lazy_load_params ,
    		$dontmove_hash = null ,
    		$dontmove_urls = null ,  
    		$extended_params = null)
    {
/*
 * input params
 * $signature_hash,
 * $loadsection, 
 * $switch = null,
 * $stubs = null,
 * $JSTexclude = null,
 * $signature_hash_css, 
 * $loadsection_css, 
 * $switch_css, 
 * $CSSexclude = null, 
 * $switch_img, 
 * $IMGexclude, 
 * $lazy_load_params , 
 * $dontmove_hash = null receives self::$_dontmovesignature_hash,
 * $dontmove_urls = null, receives self::$_dontmoveurls,  
 * $extended_params self::$_allow_multiple_orphaned ,appended to extended params
 */
        //$app = JFactory::getApplication();
    	if(is_multisite())
    	{
    		$namespace = self::resolveMultisiteStrategyNamespace()->namespace;
    		 
    	}
    	$class_name = isset($namespace)? 'JsStrategy'.$namespace:'JsStrategy';
    	
        if (empty($signature_hash))
        {

            if (class_exists($class_name) && method_exists($class_name, 'getJsSignature'))
            {
                $signature_hash = $class_name::getJsSignature();
            }
            else
            {
                $signature_hash = null;
            }
        }
        if (empty($signature_hash_css))
        {

            if (property_exists($class_name, 'cssSignaturehash'))
            {
                $signature_hash_css = $class_name::$cssSignaturehash;
            }
            else
            {
                $signature_hash_css = null;
            }
        }
        //disabling the next check as we want the status of our flags even when theyre off
        if (empty($signature_hash) && empty($signature_hash_css) && empty($switch_img) && 0)
        {
        	self::prepareMessageEnqueue(__('Could not create strategy , signatures empty','multicache-plugin'),'error');
        	if(!empty(self::$_debug))
        	{
        		self::log_error(__('Could not create stratehy , signatures empty','multicache-plugin'),'jscachestrategy');
        	}
            //$app->enqueueMessage('COM_MULTICACHE_ADMIN_HELPER_COULDNOTCREATESTRATEGY_SIGNATURES_EMPTY', 'notice');
            Return false;
        }
        if (empty($loadsection))
        {
            if (class_exists($class_name) && method_exists($class_name, 'getLoadSection'))
            {
                $loadsection = $class_name::getLoadSection();
            }
            else
            {
                $loadsection = null;
            }
        }
        if (empty($loadsection_css))
        {
            if (property_exists($class_name, 'loadSectionCss'))
            {
                $loadsection_css = $class_name::$loadSectionCss;
            }
            else
            {
                $loadsection_css = null;
            }
        }
        if (0 && empty($loadsection) && empty($loadsection_css) && empty($switch_img))
        {
        	self::prepareMessageEnqueue(__('Could not create strategy , loadsections empty','multicache-plugin'),'error');
        	if(!empty(self::$_debug))
        	{
        		self::log_error(__('Could not create strategy , loadsections empty','multicache-plugin'),'jscachestrategy');
        	}
            //$app->enqueueMessage('COM_MULTICACHE_ADMIN_HELPER_COULDNOTCREATESTRATEGY_LOADSECTIONS_EMPTY', 'notice');
            Return false;
        }
        $signature_hash = preg_replace('/\s/', '', var_export($signature_hash, true));
        $signature_hash = str_replace(',)', ')', $signature_hash);
        $signature_hash_css = preg_replace('/\s/', '', var_export($signature_hash_css, true));
        $signature_hash_css = str_replace(',)', ')', $signature_hash_css);
        $loadsection = var_export($loadsection, true);
        $loadsection_css = var_export($loadsection_css, true);
        $switch = var_export($switch, true);
        $switch_css = var_export($switch_css, true);
        $switch_img = var_export($switch_img, true);
        $lazy_load_params = ! empty($switch_img) ? var_export(unserialize($lazy_load_params), true) : null;
        //version1.0.0.2
        $dontmove_hash = preg_replace('/\s/', '', var_export($dontmove_hash, true));
        $dontmove_hash = str_replace(',)', ')', $dontmove_hash);
        $dontmove_urls = preg_replace('/\s/', '', var_export($dontmove_urls, true));
        $dontmove_urls = str_replace(',)', ')', $dontmove_urls);
        
        //version1.0.0.2
        $allow_multiple_orphaned = $extended_params['allow_multiple_orphaned'];
        if ($allow_multiple_orphaned != - 1)
        {
        	$allow_multiple_orphaned = preg_replace('/\s/', '', var_export($allow_multiple_orphaned, true));
        	$allow_multiple_orphaned = str_replace(',)', ')', $allow_multiple_orphaned);
        }
        //version1.0.0.2
        $pagespeed_strategy = array();
        if(isset($extended_params['resultant_async']))
        {
        	$pagespeed_strategy['resultant_async'] = $extended_params['resultant_async'];
        }
        if(isset($extended_params['resultant_defer']))
        {
        	$pagespeed_strategy['resultant_defer'] = $extended_params['resultant_defer'];
        }
        $pagespeed_strategy = preg_replace('/\s/', '', var_export($pagespeed_strategy, true));
         
        // $lazy_load_params = isset($lazy_load_params) ? preg_replace('/\s/', '', $lazy_load_params) : null;
        // the last step removes blanks from inbetween serilized params causing it to fail
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
        // css excludes
        if (! empty($CSSexclude->url))
        {
            $CSSurl = preg_replace('/\s/', '', var_export($CSSexclude->url, true));
            $CSSurl = str_replace(',)', ')', $CSSurl);
        }
        if (! empty($CSSexclude->query))
        {
            $CSSquery = preg_replace('/\s/', '', var_export($CSSexclude->query, true));
            $CSSquery = str_replace(',)', ')', $CSSquery);
        }
        if (! empty($CSSexclude->settings))
        {
            $CSSsettings = var_export($CSSexclude->settings, true);
        }
        if (! empty($CSSexclude->component))
        {
            $CSScomponents = preg_replace('/\s/', '', var_export($CSSexclude->component, true));
            $CSScomponents = str_replace(',)', ')', $CSScomponents);
        }
        if (! empty($CSSexclude->url_strings))
        {
            $CSSurlstrings = preg_replace('/\s/', '', var_export($CSSexclude->url_strings, true));
            $CSSurlstrings = str_replace(',)', ')', $CSSurlstrings);
        }
        // end css excludes
        // img excludes
        if (! empty($IMGexclude->url))
        {
            $IMGurl = preg_replace('/\s/', '', var_export($IMGexclude->url, true));
            $IMGurl = str_replace(',)', ')', $IMGurl);
        }
        if (! empty($IMGexclude->query))
        {
            $IMGquery = preg_replace('/\s/', '', var_export($IMGexclude->query, true));
            $IMGquery = str_replace(',)', ')', $IMGquery);
        }
        if (! empty($IMGexclude->settings))
        {
            $IMGsettings = var_export($IMGexclude->settings, true);
        }
        if (! empty($IMGexclude->component))
        {
            $IMGcomponents = preg_replace('/\s/', '', var_export($IMGexclude->component, true));
            $IMGcomponents = str_replace(',)', ')', $IMGcomponents);
        }
        if (! empty($IMGexclude->url_strings))
        {
            $IMGurlstrings = preg_replace('/\s/', '', var_export($IMGexclude->url_strings, true));
            $IMGurlstrings = str_replace(',)', ')', $IMGurlstrings);
        }
        // end img excludes
        ob_start();
        echo "<?php

/**
 * MulticacheWP
 * http://www.multicache.org
 * High Performance fastcache Controller
 * Version: MU 1.0.0.2
 * Author: Wayne DSouza
 * Author URI: http://onlinemarketingconsultants.in
 * License: GNU PUBLIC LICENSE see license.txt
 */
defined('_MULTICACHEWP_EXEC') or die();
        		
class $class_name{
            ";
        $cl_buf = ob_get_clean();
        if (! empty($switch))
        {
            ob_start();
            echo "
public static \$js_switch = " . $switch . "	;
    ";
            $cl_buf .= ob_get_clean();
        }
        if (! empty($switch_css))
        {
            ob_start();
            echo "
public static \$css_switch = " . $switch_css . "	;
    ";
            $cl_buf .= ob_get_clean();
        }
        if (isset($switch_img))
        {
            ob_start();
            echo "
public static \$img_switch = " . $switch_img . "	;
    ";
            $cl_buf .= ob_get_clean();
        }
        //version1.0.0.2 pagespeed
        if (! empty($pagespeed_strategy))
        {
        	ob_start();
        	echo "
public static \$pagespeed_strategy = " . $pagespeed_strategy . "	;
    ";
        	$cl_buf .= ob_get_clean();
        }
        //end pagespeed
        if (! empty($stubs))
        {
            ob_start();
            echo "
public static \$stubs = " . $stubs . " ;
 ";
            $cl_buf .= ob_get_clean();
        }

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
        // startcssexcludes
        if (! empty($CSSexclude->settings) && (! empty($CSSexclude->url) || ! empty($CSSexclude->query)))
        {
            ob_start();
            echo "
public static \$CSSsetting = " . $CSSsettings . ";
  ";
            $cl_buf .= ob_get_clean();
        }
        if (! empty($CSSexclude->url))
        {
            ob_start();
            echo "
public static \$CSSCludeUrl = " . $CSSurl . ";
  ";
            $cl_buf .= ob_get_clean();
        }

        if (! empty($CSSexclude->query))
        {

            ob_start();
            echo "
public static \$CSSCludeQuery = " . $CSSquery . ";
  ";
            $cl_buf .= ob_get_clean();
        }
        if (! empty($CSSexclude->component))
        {

            ob_start();
            echo "
public static \$CSSexcluded_components = " . $CSScomponents . ";
  ";
            $cl_buf .= ob_get_clean();
        }
        if (! empty($CSSexclude->url_strings))
        {

            ob_start();
            echo "
public static \$CSSurl_strings = " . $CSSurlstrings . ";
  ";
            $cl_buf .= ob_get_clean();
        }

        // endcssexcludes
        // startimgexcludes
        if (! empty($IMGexclude->settings) && (! empty($IMGexclude->url) || ! empty($IMGexclude->query)))
        {
            ob_start();
            echo "
public static \$IMGsetting = " . $IMGsettings . ";
  ";
            $cl_buf .= ob_get_clean();
        }
        if (! empty($IMGexclude->url))
        {
            ob_start();
            echo "
public static \$IMGCludeUrl = " . $IMGurl . ";
  ";
            $cl_buf .= ob_get_clean();
        }

        if (! empty($IMGexclude->query))
        {

            ob_start();
            echo "
public static \$IMGCludeQuery = " . $IMGquery . ";
  ";
            $cl_buf .= ob_get_clean();
        }
        if (! empty($IMGexclude->component))
        {

            ob_start();
            echo "
public static \$IMGexcluded_components = " . $IMGcomponents . ";
  ";
            $cl_buf .= ob_get_clean();
        }
        if (! empty($IMGexclude->url_strings))
        {

            ob_start();
            echo "
public static \$IMGurl_strings = " . $IMGurlstrings . ";
  ";
            $cl_buf .= ob_get_clean();
        }

        if (isset($lazy_load_params))
        {
            ob_start();
            echo "
public static \$img_tweak_params = " . $lazy_load_params . "	;
    ";
            $cl_buf .= ob_get_clean();
        }
        // endimgexcludes

        if (! empty($signature_hash))
        {

            ob_start();
            echo "
public static function getJsSignature(){
\$sigss = " . trim($signature_hash) . ";
Return \$sigss;
}";
            $cl_buf .= ob_get_clean();
        }
        if (! empty($loadsection))
        {

            ob_start();
            echo "
public static function getLoadSection(){
\$loadsec = " . trim($loadsection) . ";
Return \$loadsec;
}";
            $cl_buf .= ob_get_clean();
        }
        if (! empty($signature_hash_css))
        {

            ob_start();
            echo "
public static \$sig_css = " . trim($signature_hash_css) . ";";

            $cl_buf .= ob_get_clean();
        }
        //version1.0.0.2 start
        if (! empty($dontmove_hash))
        {
        
        	ob_start();
        	echo "
public static \$dontmove_js = " . trim($dontmove_hash) . ";";
        
        	$cl_buf .= ob_get_clean();
        }
        //version1.0.0.2
        if (! empty($dontmove_urls))
        {
        	ob_start();
        	echo "
public static \$dontmove_urls_js = " . trim($dontmove_urls) . ";";
        
        	$cl_buf .= ob_get_clean();
        }
        //version1.0.0.2
        if (! empty($allow_multiple_orphaned))
        {
        	ob_start();
        	echo "
public static \$allow_multiple_orphaned = " . trim($allow_multiple_orphaned) . ";";
        
        	$cl_buf .= ob_get_clean();
        }
        // stop
        if (! empty($loadsection_css))
        {

            ob_start();
            echo "
public static \$loadsec_css = " . trim($loadsection_css) . ";";

            $cl_buf .= ob_get_clean();
        }
        ob_start();
        echo "
}
?>";
        $cl_buf .= ob_get_clean();

        $cl_buf = serialize($cl_buf);
        $dir = plugin_dir_path(dirname(__FILE__)).'libs/';//JPATH_COMPONENT . '/lib';
        $filename = 'jscachestrategy.php';
        $success = self::writefileTolocation($dir, $filename, $cl_buf);
        Return $success;

    }

    public static function getJScodeUrl($key, $type = null, $jquery_scope = "jQuery", $media = "default" , $params = null)
    {

        // $base_url = '//' . str_replace("http://", "", strtolower(substr(JURI::root(), 0, - 1)) . '/media/com_multicache/assets/js/jscache/');
        //$base_url = JURI::root() . 'media/com_multicache/assets/js/jscache/';
        if(is_multisite())
        {
        	$object = self::resolveLocMul(plugins_url( 'delivery/assets/js/jscache/', dirname(__FILE__)) , $key);
        	$base_url = $object['path']['path'] .'/';
        	
        	if (isset($type) && $type == "raw_url")
        	{
        		Return  $base_url. $key . ".js?mediaVersion=" . $media;
        	}
        	
        	if (isset($type) && $type == "script_url")
        	{
        		//version1.0.0.2
        		$script = '<script src="' . $base_url . $key . '.js?mediaVersion=' . $media . '"   type="text/javascript" ';
        		//$script = '<script src="' . $base_url . $key . '.js?mediaVersion=' . $media . '"   type="text/javascript" ></script>';
        		 
        		//version1.0.0.2
        		if (isset($params) && ! empty($params['resultant_async']))
        		{
        			$script .= ' async ';
        		}
        		if (isset($params) && ! empty($params['resultant_defer']))
        		{
        			$script .= ' defer ';
        		}
        		$script .= '></script>';
        		
        		Return serialize($script);
        	}
        	
        	$url = $jquery_scope . '.getScript(' . '"' . $base_url . $key . '.js?mediaVersion=' . $media . '"' . ');';
        	
        	
        	
        	Return serialize($url);
        }
        $base_url = plugins_url( 'delivery/assets/js/jscache/', dirname(__FILE__));
        if (isset($type) && $type == "raw_url")
        {
            Return $base_url . $key . ".js?mediaVersion=" . $media;
        }
        // script_url

        if (isset($type) && $type == "script_url")
        {
           //version1.0.0.2
        	 $script = '<script src="' . $base_url . $key . '.js?mediaVersion=' . $media . '"   type="text/javascript" ';
           //$script = '<script src="' . $base_url . $key . '.js?mediaVersion=' . $media . '"   type="text/javascript" ></script>';
           
        	 //version1.0.0.2
        	 if (isset($params) && ! empty($params['resultant_async']))
        	 {
        	 	$script .= ' async ';
        	 }
        	 if (isset($params) && ! empty($params['resultant_defer']))
        	 {
        	 	$script .= ' defer ';
        	 }
        	 $script .= '></script>';
            Return serialize($script);
        }
        $url = $jquery_scope . '.getScript(' . '"' . $base_url . $key . '.js?mediaVersion=' . $media . '"' . ');';
        
 

        Return serialize($url);

    }

    public static function getCsscodeUrl($key, $type = null, $jquery_scope = "jQuery", $media = "default")
    {

        /*
         * $base_url = '//' . str_replace(array(
         * "http://",
         * "https://"
         * ), array(
         * "",
         * ""
         * ), strtolower(substr(JURI::root(), 0, - 1)) . '/media/com_multicache/assets/css/csscache/');
         */
    
        //$base_url = MulticacheUri::root() . 'media/com_multicache/assets/css/csscache/';
        if(is_multisite())
        {
        	$object = self::resolveLocMul(plugins_url( 'delivery/assets/css/csscache/', dirname(__FILE__)) , $key);
        	$base_url = $object['path']['path'] .'/';
        	if (isset($type) && $type == "raw_url")
        	{
        		Return $base_url . $key . ".css?mediaVersion=" . $media;
        	}
        	if (isset($type) && $type == "link_url")
        	{
        		$css_link = '<link href="' . $base_url . $key . '.css?mediaVersion=' . $media . '" rel="stylesheet" type="text/css" />';
        		Return serialize($css_link);
        	}
        	if (isset($type) && $type == "html_url")
        	{
        		$html_link = $base_url . $key . ".html?mediaVersion=" . $media;
        		Return $html_link;
        	}
        	$url = $jquery_scope . '.getScript(' . '"' . $base_url . $key . '.css?mediaVersion=' . $media . '"' . ');';
        	
        	Return serialize($url);
        }
    	$base_url = plugins_url( 'delivery/assets/css/csscache/', dirname(__FILE__));
        
        if (isset($type) && $type == "raw_url")
        {
            Return $base_url . $key . ".css?mediaVersion=" . $media;
        }
        // link_url

        if (isset($type) && $type == "link_url")
        {
            $css_link = '<link href="' . $base_url . $key . '.css?mediaVersion=' . $media . '" rel="stylesheet" type="text/css" />';
            Return serialize($css_link);
        }
        if (isset($type) && $type == "html_url")
        {
            $html_link = $base_url . $key . ".html?mediaVersion=" . $media;
            Return $html_link;
        }
        $url = $jquery_scope . '.getScript(' . '"' . $base_url . $key . '.css?mediaVersion=' . $media . '"' . ');';

        Return serialize($url);

    }

    public static function noscriptWrap($link , $unserialized = false)
    {
 if(true === $unserialized)
 {
 	Return '<noscript>' . $link . '</noscript>';
 }
        Return '<noscript>' . unserialize($link) . '</noscript>';

    }

    public static function getCsslinkUrl($urlname, $type = 'link_url', $media = "default")
    {
    	if(strpos($urlname , '//') === 0 || (strpos($urlname , 'font') !== false && strpos($urlname , 'family=') !== false ))
    	{
    		$type = "plain_url";
    	}
        // link_url
        // well need to ensure that were adding the query format right here
        if (isset($type) && $type == "link_url")
        {
            $uri = MulticacheUri::getInstance($urlname);
            $uri->setVar('mediaVersion', $media);
            $css_link = '<link href="' . $uri->toString() . '" rel="stylesheet" type="text/css"/>';
        }
        elseif (isset($type) && $type == "plain_url")
        {
            $css_link = '<link href="' . $urlname . '" rel="stylesheet" type="text/css"/>';
        }
        Return $css_link;

    }

    public static function getMediaFormat($length = 4)
    {

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i ++)
        {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;

    }

    public static function clean_code($code_string)
    {

        if (! empty($code_string) && substr($code_string, - 1, 1) != ";")
        {
            $code_string = $code_string . ";";
        }
        Return $code_string;

    }

    public static function getloadableSourceScript($script_bit, $async = false, $params = false)
    {

        if (empty($script_bit))
        {
            Return false;
        }
        $tag = '<script src="' . $script_bit . '"  type="text/javascript"';
        /*
        if ($async)
        {
            Return '<script src="' . $script_bit . '" async type="text/javascript"></script>';
        }
        */
        //version1.0.0.2
        if ($async || ! empty($params['resultant_async']))
        {
        	$tag .= ' async ';
        }
        if (! empty($params['resultant_defer']))
        {
        	$tag .= ' defer ';
        }
        $tag .= '></script>';
        Return $tag;
        //Return '<script src="' . $script_bit . '" type="text/javascript"></script>';

    }
public static function wrapDelay($code , $jquery_scope = "jQuery")
{
	$delay_code = 'function MulticacheCallDelay(){'.$code.'}function asyncMulticacheDelay(e,n,i,l){if(i="undefined"==typeof i?1:++i,l="undefined"==typeof l?1e4:l,n="undefined"==typeof n?10:n,e="undefined"==typeof e?"MulticacheCallDelay":e,"undefined"==typeof window.'.  $jquery_scope  .'&&"undefined"==typeof window.MULTICACHEDELAYCALLED&&l>=i)setTimeout(function(){console.log("delayrouteBinit run - "+i+" time -"+n),asyncMulticacheDelay(e,n,i)},n);else{if("undefined"==typeof window.MULTICACHEDELAYCALLED){var d="undefined"==typeof window.MULTICACHEDELAYCALLED?"undefined":"defined",o="undefined"==typeof window.MULTICACHEDELAYCALLED?"undefined":"defined";console.log("calling delay..... "+typeof window.MULTICACHEDELAYCALLED+" a- "+d+" b- "+o),MulticacheCallDelay(),window.MULTICACHEDELAYCALLED=!0}console.log("rolling in else delay")}}asyncMulticacheDelay("MulticacheCallDelay",1);';
	Return $delay_code;
}

public static function getMAU()
{
	$debug = false;

	$mau = <<<MAU
var multicache_MAU = function (zfunc_a, zfunc_b, check_func, time , count , max , name)
    { 
	count =  typeof count === 'undefined'? 1: ++count;
	max = typeof max === 'undefined'? 10: max;
	time = typeof time === 'undefined' ? 30 :time;	
  zfunc_a = typeof zfunc_a === 'undefined'? reject(Error('resolve func not defined')) : zfunc_a;
  zfunc_b = typeof zfunc_b === 'undefined'? reject(Error('reject func not defined ')) : zfunc_b;
MAU;
	if($debug)
	{
		$mau .= <<<MAU
console.log('checktype mau  name- ' +  name + ' isdefined- ' + check_func.checkType() + '  checkname- '+ check_func.name + ' count-' +count + ' max ' + max);
MAU;
	}
		$mau .= <<<MAU
	if(  'undefined' === check_func.checkType() && count<= max){		  
		setTimeout(function(){ 
MAU;
	if($debug)
	{
		$mau .= <<<MAU
console.log('routeB run - ' + count + ' time -' + time + 'name -' + name );
MAU;
	}
		$mau .= <<<MAU
multicache_MAU(zfunc_a, zfunc_b,check_func, time , count , max , name);
			}, time);
		
	}
	else if(count<= max)
		{
MAU;
	if($debug)
	{
		$mau .= <<<MAU
console.log(mau passsed typeof '+check_func.name + '  ' +  check_func.checkType());
MAU;
	}
		$mau .= <<<MAU
 zfunc_a(10);
		}
    else{
MAU;
	if($debug)
	{
		$mau .= <<<MAU
console.log('mau failed reject '+check_func.name + '  ' +  check_func.checkType());
MAU;
	}
		$mau .= <<<MAU
zfunc_b();
   
    }
	}
MAU;
	   //$mau = 'var multicache_MAU=function(e,n,f,d,t,c){t="undefined"==typeof t?1:++t,c="undefined"==typeof c?6:c,d="undefined"==typeof d?30:d,e="undefined"==typeof e?reject(Error("resolve func not defined")):e,n="undefined"==typeof n?reject(Error("reject func not defined ")):n,"undefined"===f.checkType()&&c>=t?setTimeout(function(){multicache_MAU(e,n,f,d,t)},d):c>=t?e(10):n()};';
		//deprecated
		//$mau = 'var multicache_MAU=function(e,n,o,c,t,f){t="undefined"==typeof t?1:++t,f="undefined"==typeof f?30:f,c="undefined"==typeof c?30:c,e="undefined"==typeof e?reject(Error("resolve func not defined")):e,n="undefined"==typeof n?reject(Error("reject func not defined ")):n,console.log("checktype mau"+o.checkType()+" "+t),"undefined"===o.checkType()&&f>=t?setTimeout(function(){console.log("routeB run - "+t+" time -"+c),multicache_MAU(e,n,o,c,t)},c):f>=t?(console.log("mau passsed typeof "+o.name+"  "+o.checkType()),e(10)):(console.log("mau failed reject alert"),n())};';
		//short mau
		$mau = 'var multicache_MAU=function(e,n,f,d,t,c,i){t="undefined"==typeof t?1:++t,c="undefined"==typeof c?10:c,d="undefined"==typeof d?30:d,e="undefined"==typeof e?reject(Error("resolve func not defined")):e,n="undefined"==typeof n?reject(Error("reject func not defined ")):n,"undefined"===f.checkType()&&c>=t?setTimeout(function(){multicache_MAU(e,n,f,d,t,c,i)},d):c>=t?e(10):n()};';
		//development mau
		//$mau = 'var multicache_MAU=function(e,n,c,o,t,d,f){t="undefined"==typeof t?1:++t,d="undefined"==typeof d?10:d,o="undefined"==typeof o?30:o,e="undefined"==typeof e?reject(Error("resolve func not defined")):e,n="undefined"==typeof n?reject(Error("reject func not defined ")):n,console.log("checktype mau  name- "+f+" isdefined- "+c.checkType()+"  checkname- "+c.name+" count-"+t+" max "+d),"undefined"===c.checkType()&&d>=t?setTimeout(function(){console.log("routeB run - "+t+" time -"+o+"name -"+f),multicache_MAU(e,n,c,o,t,d,f)},o):d>=t?(console.log("mau passsed typeof "+c.name+"  "+c.checkType()),e(10)):(console.log("mau failed reject "+c.name+"  "+c.checkType()),n())};';
		Return $mau;		
}
    public static function getloadableCodeScript($code_bit, $async = false, $unserialized = null, $params = null)
    {

        if (empty($code_bit))
        {
            Return false;
        }
        //version1.0.0.2
        $tag = '<script type="text/javascript"';
        /*
        if ($async && ! isset($unserialized))
        {
            Return '<script  async type="text/javascript">' . unserialize($code_bit) . '</script>';
        }
        if ($async && isset($unserialized))
        {
            Return '<script  async type="text/javascript">' . $code_bit . '</script>';
        }
        if (! $async && isset($unserialized))
        {
            Return '<script   type="text/javascript">' . $code_bit . '</script>';
        }
        Return '<script  type="text/javascript">' . unserialize($code_bit) . '</script>';
        */
        if (isset($params) && ! empty($params['resultant_async']) || $async)
        {
        	$tag .= ' async';
        }
        if (isset($params) && ! empty($params['resultant_defer']))
        {
        	$tag .= ' defer';
        }
        $tag .= '>';
        if (! isset($unserialized))
        {
        	$tag .= unserialize($code_bit);
        }
        elseif (isset($unserialized))
        {
        	$tag .= $code_bit;
        }
        
        $tag .= '</script>';
        
        Return $tag;
    }

    public static function getloadableCodeCss($code_bit, $media = null, $scoped = null, $serialized = null)
    {

        if (empty($code_bit))
        {
            Return false;
        }

        if (isset($media) && ! isset($scoped))
        {
            Return '<style   type="text/css" media="' . $media . '">' . unserialize($code_bit) . '</style>';
        }
        elseif (isset($media) && isset($scoped))
        {
            Return '<style  scoped  type="text/css" media="' . $media . '">' . unserialize($code_bit) . '</style>';
        }
        elseif (! isset($media) && isset($scoped))
        {
            Return '<style  scoped  type="text/css" >' . unserialize($code_bit) . '</style>';
        }

        if (isset($serialized))
        {

            Return '<style  type="text/css">' . $code_bit . '</style>';
        }
        Return '<style  type="text/css">' . unserialize($code_bit) . '</style>';

    }
/*
    public static function checkComponentParams()
    {

        $app = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_multicache');
        $params_d = (array) json_decode($params);
        if (! empty($params_d))
        {
            Return;
        }
        $extensionTable = JTable::getInstance('extension');
        $componentId = $extensionTable->find(array(
            'element' => 'com_multicache',
            'type' => 'component'
        ));
        $params->set('tolerance_highlighting', 1);
        $params->set('danger_tolerance_factor', 3);
        $params->set('danger_tolerance_color', '#a94442');
        $params->set('warning_tolerance_factor', 2.5);
        $params->set('warning_tolerance_color', '#8a6d3b');
        $params->set('success_tolerance_color', '#468847');
        $extensionTable->load($componentId);
        $extensionTable->bind(array(
            'params' => $params->toString()
        ));
        if (! $extensionTable->check())
        {
            $app->enqueueMessage(JText::_('COM_MULTICACHE_HELPER_CHECKCOMPPARAM_FAILED_TABLECHECK') . '  ' . $extensionTable->getError(), 'error');
            return false;
        }
        if (! $extensionTable->store())
        {
            $app->enqueueMessage(JText::_('COM_MULTICACHE_HELPER_CHECKCOMPPARAM_FAILED_TABLESTORE') . '  ' . $extensionTable->getError(), 'error');
            return false;
        }

    }

    protected static function extractDelayType($delay)
    {

        $type = null;
        foreach ($delay as $key => $value)
        {
            if (! empty($value["delay_type"]))
            {
                $type = $value["delay_type"];
                break;
            }
        }
        Return $type;

    }

    protected static function getGenericYesNo()
    {

        $options = array();
        $options[] = JHtml::_('select.option', 0, JText::_('JNo'));
        $options[] = JHtml::_('select.option', 1, JText::_('JYes'));
        return $options;

    }

    protected static function getLoadSectionOptions()
    {

        $options = array();
        $options[] = JHtml::_('select.option', 0, JText::_('COM_MULTICACHE_LOADSECTION_0_DEFAULT_LABEL'));
        $options[] = JHtml::_('select.option', 1, JText::_('COM_MULTICACHE_LOADSECTION_1_PREHEAD_LABEL'));
        $options[] = JHtml::_('select.option', 2, JText::_('COM_MULTICACHE_LOADSECTION_2_HEAD_LABEL'));
        $options[] = JHtml::_('select.option', 3, JText::_('COM_MULTICACHE_LOADSECTION_3_BODY_LABEL'));
        $options[] = JHtml::_('select.option', 4, JText::_('COM_MULTICACHE_LOADSECTION_4_FOOTER_LABEL'));
        $options[] = JHtml::_('select.option', 5, JText::_('COM_MULTICACHE_LOADSECTION_5_DONTLOAD_LABEL'));
        return $options;

    }
*/
    protected static function getCache($admin = NULL)
    {

        //$conf = JFactory::getConfig();
    	//$conf = MulticacheFactory::getConfig();
    	$conf = MulticacheFactory::getBlogConfig();

        $options = array(
            'defaultgroup' => '',
            'storage' => false !== $conf ? $conf->getC('storage', 'fastcache'): 'fastcache',
            'caching' => true,
            'cachebase' => false !== $conf && null !==$conf->getC('cache_path')? $conf->getC('cache_path') :WP_CONTENT_DIR .'/cache/',
        );

        //$cache = JCache::getInstance('', $options);
        $cache = Multicache::getInstance('', $options);

        return $cache;

    }

    public static function Checkurl($url, $mediaVersion)
    {

        if (preg_match('/[^a-zA-Z0-9\/\:\?\#\.]/', $url))
        {
            if (strpos($url, '//') === 0)
            {
                $url = is_ssl()? 'https:' . $url :'http:' . $url;
            }
        }
        else
        {
            $c_uri = MulticacheUri::getInstance($url);
            $c_uri->setVar('mediaFormat', $mediaVersion);
            $url = $c_uri->toString();
        }

        Return $url;

    }
    public static function checkPositionalUrls($array)
    {
    
    	if (empty($array))
    	{
    		Return false;
    	}
    
    	foreach ($array as $key => $arr_bit)
    	{
    		$array[$key] = str_replace(array(
    				'https',
    				'http',
    				'://',
    				'//',
    				'www.'
    		), '', $arr_bit);
    	}
    
    	Return $array;
    
    }
/*
    protected static function getCssDelayTypes()
    {

        $options = array();
        $options[] = JHtml::_('select.option', 'async', JText::_('COM_MULTICACHE_CSS_DELAY_TYPE_OPTION_ASYNC'));
        $options[] = JHtml::_('select.option', 'mousemove', JText::_('COM_MULTICACHE_CSS_DELAY_TYPE_OPTION_MOUSEMOVE'));
        $options[] = JHtml::_('select.option', 'scroll', JText::_('COM_MULTICACHE_CSS_DELAY_TYPE_OPTION_SCROLL'));
        return $options;

    }
*/
    public static function getGroupAsyncSrcbits($group, $params = false)
    {
    	if(empty($group))
    	{
    		Return false;
    	}
    	$inline_code = '';
    	$link_code = '';
    	$excluded_code = '';
    	$x_group = !empty($params['groups_async_exclude'])? $params['groups_async_exclude'] : false;
    	$d_group = !empty($params['css_groupsasync_delay'])? $params['css_groupsasync_delay'] : false;
    	foreach($group As $key => $grp)
    	{
    		$excluded_flag = false;
    		if(false !== $x_group)
    		{
    			foreach($x_group As $k => $name)
    			{
    		
    				if((string)trim($name) === (string)$key)
    				{
    					$excluded_flag = true;
    					break;
    				}
    			}
    		}
    		if($grp['success'] === true && false === $excluded_flag)
    		{
    			//$inline_code .= 'loadCSS( "' . $grp['url'] . '" );';
    			//$link_code   .= '<link href="' . $grp['url'] . '" rel="stylesheet" type="text/css" />';
    			if(isset($d_group[$key])){
    				$inline_code .= 'loadCSS( "' . $grp['url']  . '" ,'.$d_group[$key].' );';
    			}
    			else{
    				$inline_code .= 'loadCSS( "' . $grp['url'] . '" );';
    			}
    			$link_code   .= '<link href="' . $grp['url'] . '" rel="stylesheet" type="text/css" />';
    		}
    		else
    		{
    			$excluded_code .= '<link href="' . $grp['url'] . '" rel="stylesheet" type="text/css" />';
    		}
    	}
    	//int for wp
    	if( 1 === $params['css_groupsasync'] && !empty($inline_code))
    	{
    		$inline_code .= 'window.MULTICACHEASYNCNONOLEND = 1;loadStackMulticache(window.MULTICACHEASYNCOLSTACK);';
    	}
    	$return = array();
    	$return['inline_code'] =$inline_code;
    	$return['noscript'] = $link_code;
    	$return['excluded_code'] = $excluded_code;
    	Return $return;
    }
    public static function getAsyncSrcbits($async_delay)
    {

        if (empty($async_delay["items"]))
        {
            Return false;
        }
        $src_bits_async = '';
        foreach ($async_delay["items"] as $key => $item)
        {
        	$c_alias = !empty($item['cdnalias']) && !empty($item['cdn_url_css']);
            /*if (! empty($item["serialized_code"]) || empty($item["href_clean"]))*/
        	if(!$c_alias && (! empty($item["serialized_code"]) || empty($item["href_clean"] )))
            {
                continue; // we should have already integrated inline code to async.css by now
            }
            if(!empty($item['cdnalias']) && !empty($item['cdn_url_css']))
            {
            	$src_bit = $item['cdn_url_css'];
            }
            elseif (preg_match('/[^a-zA-Z0-9\/\:\?\#\.]/', $item["href"]) && empty($item["internal"]))
            {
                $src_bit = $item["href"];
            }
            else
            {
                $src_bit = ! empty($item["absolute_src"]) ? $item["absolute_src"] : (! empty($item["href_clean"]) ? $item["href_clean"] : $item["href"]);
            }
            $src_bit_inline = 'loadCSS( "' . $src_bit . '" );';
            $src_bits_async .= $src_bit_inline;
        }
        if (isset($async_delay["inline_async"]) && $async_delay["inline_async"] == true)
        {
           // $base_url = '//' . str_replace("http://", "", strtolower(substr(MulticacheUri::root(), 0, - 1)) . '/media/com_multicache/assets/css/csscache/');
        	if(is_multisite())
        	{
        		$dir = plugin_dir_url(dirname(__FILE__)).'delivery/assets/css/csscache/';
        		$object = self::resolveLocMul($dir , '');
        		$base_url = $object['path']['path'] .'/';
        		$base_url = '//' . str_replace(array("http://","https://"), "",strtolower($base_url));
        	}
        	else
        	{
        	$base_url = '//' . str_replace(array("http://","https://"), "", strtolower(plugins_url( 'delivery/assets/css/csscache/', dirname(__FILE__))));
        	}
            $inline_async = $base_url . $async_delay["delay_callable_url"];
            $inline_async = 'loadCSS( "' . $inline_async . '" );';
            $src_bits_async .= $inline_async;
            
        }
        Return $src_bits_async;

    }
/*
    protected static function getDelayTypes()
    {

        $options = array();

        $options[] = JHtml::_('select.option', 'mousemove', JText::_('COM_MULTICACHE_DELAY_TYPE_OPTION_MOUSEMOVE'));
        $options[] = JHtml::_('select.option', 'scroll', JText::_('COM_MULTICACHE_DELAY_TYPE_OPTION_SCROLL'));
        return $options;

    }

    protected static function getCssGroupNumber()
    {

        $options = array();

        $options[] = JHtml::_('select.option', '0', JText::_('COM_MULTICACHE_CSSGROUPNUMBER_OPTION_GROUPDEFAULT_LABEL'));
        $options[] = JHtml::_('select.option', '1', JText::_('COM_MULTICACHE_CSSGROUPNUMBER_OPTION_GROUPONE_LABEL'));
        $options[] = JHtml::_('select.option', '2', JText::_('COM_MULTICACHE_CSSGROUPNUMBER_OPTION_GROUPTWO_LABEL'));
        $options[] = JHtml::_('select.option', '3', JText::_('COM_MULTICACHE_CSSGROUPNUMBER_OPTION_GROUPTHREE_LABEL'));
        $options[] = JHtml::_('select.option', '4', JText::_('COM_MULTICACHE_CSSGROUPNUMBER_OPTION_GROUPFOUR_LABEL'));
        $options[] = JHtml::_('select.option', '5', JText::_('COM_MULTICACHE_CSSGROUPNUMBER_OPTION_GROUPFIVE_LABEL'));
        $options[] = JHtml::_('select.option', '6', JText::_('COM_MULTICACHE_CSSGROUPNUMBER_OPTION_GROUPSIX_LABEL'));
        $options[] = JHtml::_('select.option', '7', JText::_('COM_MULTICACHE_CSSGROUPNUMBER_OPTION_GROUPSEVEN_LABEL'));
        $options[] = JHtml::_('select.option', '8', JText::_('COM_MULTICACHE_CSSGROUPNUMBER_OPTION_GROUPEIGHT_LABEL'));
        $options[] = JHtml::_('select.option', '9', JText::_('COM_MULTICACHE_CSSGROUPNUMBER_OPTION_GROUPNINE_LABEL'));
        $options[] = JHtml::_('select.option', '10', JText::_('COM_MULTICACHE_CSSGROUPNUMBER_OPTION_GROUPTEN_LABEL'));
        return $options;

    }
*/
    protected static function setinitialiseScriptpeice($name, $property, $classname = "MulticachePageScripts")
    {
        // $name-> $advertisements
        // $property -> advertisements
        // initialise the advertisements array
        if (isset($name))
        {
            $name = var_export($name, true);
        }
        else
        {
            // set it in the specific case it exists ie..everything is set except social
            if (! class_exists($classname))
            {
                Return false;
            }

            if (property_exists($classname, $property))
            {

                $name = $classname::$$property;
                $name = var_export($name, true);
            }
        }
        // end initialise the advertisements array
        Return $name;

    }
/*
    protected static function writeConfigFile(JRegistry $config)
    {

        jimport('joomla.filesystem.path');
        jimport('joomla.filesystem.file');

        // Set the configuration file path.
        $file = JPATH_CONFIGURATION . '/configuration.php';

        // Get the new FTP credentials.
        $ftp = JClientHelper::getCredentials('ftp', true);

        $app = JFactory::getApplication();

        // Attempt to make the file writeable if using FTP.
        if (! $ftp['enabled'] && JPath::isOwner($file) && ! JPath::setPermissions($file, '0644'))
        {
            $app->enqueueMessage(JText::_('COM_CONFIG_ERROR_CONFIGURATION_PHP_NOTWRITABLE'), 'notice');
        }

        // Attempt to write the configuration file as a PHP class named JConfig.
        $configuration = $config->toString('PHP', array(
            'class' => 'JConfig',
            'closingtag' => false
        ));

        if (! JFile::write($file, $configuration))
        {
            throw new RuntimeException(JText::_('COM_CONFIG_ERROR_WRITE_FAILED'));
        }

        // Attempt to make the file unwriteable if using FTP.
        if (! $ftp['enabled'] && JPath::isOwner($file) && ! JPath::setPermissions($file, '0444'))
        {
            $app->enqueueMessage(JText::_('COM_MULTICACHE_ERROR_CONFIGURATION_PHP_NOTUNWRITABLE'), 'notice');
        }

        return true;

    }
*/
    public static function RegisterLazyload($code , $type = 'script')
    {
    	if(empty($code))
    	{
    		Return false;
    	}
    	if($type == 'script')
    	{
    		$dir = plugin_dir_path(dirname(__FILE__)).'delivery/assets/js';
    		$filename = 'm_ll.js';
    	}
    	elseif($type == 'style')
    	{
    		$dir = plugin_dir_path(dirname(__FILE__)).'delivery/assets/css';
    		$filename = 'm_ll.css';
    	}
    	Return self::writefileTolocation($dir, $filename, $code ,true);
    }
    public static function loadConfigOptions()
    {
    	    	
    	require_once plugin_dir_path(__FILE__) . 'multicache_activate.php';
    	MulticacheConfigure::loadConfigOptions();
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
    public static function resolveCacheSubPath()
    {
    	$ms_obj = MulticacheFactory::getResolvedMultiSiteObj();
    	Return 'multisite_multicache/'.$ms_obj->blog.'-'.$ms_obj->site.'/';
    }
    public static function resolveMultiSiteStrategyloc()
    {
    	static $multisite_strategy_loc = null;
    	if(isset($multisite_strategy_loc))
    	{
    		Return $multisite_strategy_loc;
    	}
    	$obj = new stdClass();
    	//get Multisite strategy
    	$obj->strategy_loc = dirname(dirname(__FILE__)).'/libs/multisite_strategy';
    	//get site
    	$obj->current_site =  get_current_site();
    	if(!$obj->current_site)
    	{
    		Return false;
    	}
    	$obj->currentsite_id = $obj->current_site->id;
    	$obj->currentsite_folder = self::ensureNamespace($obj->current_site->domain);
    	 
    	//get blog
    	$obj->blog_id = get_current_blog_id();
    	if(!$obj->blog_id)
    	{
    		Return false;
    	}
    	$obj->blog_info = get_blog_details( $obj->blog_id );
    	if(!$obj->blog_info)
    	{
    		Return false;
    	}
    	/*
    	if(isset($obj->blog_info->home) && 0)
    	{
    		$obj->live_site = $obj->blog_info->home;
    		$obj->live_site_stripped = str_replace(array('https://',
    				'http://',
    				'www.',    				
    		),'' , $obj->blog_info->home );
    	}
    	elseif(isset($obj->blog_info->siteurl) && 0)
    	{
    		$obj->live_site = $obj->blog_info->siteurl;
    		$obj->live_site_stripped = str_replace(array('https://',
    				'http://',
    				'www.',
    		),'' , $obj->blog_info->home );
    	}
    	*/
    	$domain = $obj->blog_info->domain;
    	$host_name = strtolower(str_replace(array('.www.','www.'),array('.',''),$domain));
    	if( defined('SUBDOMAIN_INSTALL') && SUBDOMAIN_INSTALL===true)
    	{
    		
    		preg_match('~^(?:([^\.]+)\.)?([^.]+\..+)~six' ,$host_name,$domains );
    		$blog = !empty($domains[1])? preg_replace('~[^a-zA-Z0-9]~' , '',$domains[1]): 'main';
    		$site = preg_replace('~[^a-zA-Z0-9]~' , '',$domains[2]);
    		$obj->blog_path = '/'. $blog.'/';
    		$obj->install_type = 'subdomain';
    		$obj->live_site = is_ssl()? 'https://'. $domain : 'http://'.$domain;
    		$obj->live_site_stripped = $host_name;
    		
    	}
    	elseif(defined('PATH_CURRENT_SITE') && PATH_CURRENT_SITE !=='/')
    	{
    		$folder_string = PATH_CURRENT_SITE;
    		$a = preg_quote($folder_string);
    		$search = '~'.$a.'(.*)~';
    		preg_match( $search ,$obj->blog_info->path ,$m);
    		$path = '/'. $m[1];
    		$path_stripped =  $m[1];
    		$obj->blog_path = $path === '/'? '/main/' : $path ;
    		$obj->install_type = 'subfolder';
    		$obj->sub_folder = PATH_CURRENT_SITE;
    		$obj->live_site = is_ssl()? 'https://'. $domain . $obj->sub_folder .$path_stripped : 'http://'.$domain . $obj->sub_folder . $path_stripped;
    		$obj->live_site_stripped = $host_name . $obj->sub_folder . $path_stripped;
    	}
    	else {
    		$obj->blog_path = $obj->blog_info->path === '/'? '/main/' : $obj->blog_info->path ;
    		$path_stripped = $obj->blog_info->path;
    		$obj->install_type = 'folder';
    		$obj->live_site = is_ssl()? 'https://'. $domain .$path_stripped : 'http://'.$domain . $path_stripped;
    		$obj->live_site_stripped = $host_name . $obj->sub_folder . $path_stripped;
    	}
    	
    	$obj->strategy_sitefolder_loc = $obj->strategy_loc .'/' .$obj->currentsite_folder;
    	$obj->strategy_blogfolder_loc = $obj->strategy_loc .'/' .$obj->currentsite_folder.$obj->blog_path ;
    	$obj->strategy_blogConfig_loc = $obj->strategy_loc .'/' .$obj->currentsite_folder.$obj->blog_path.'multicache_config.php';
    	$obj->simcontrol_location     = dirname(dirname(__FILE__)).'/simcontrol/libs/multisite_strategy/'.$obj->currentsite_folder.$obj->blog_path ;
    	
    	//3 imp tasks
    	self::makeMultisiteFolder($obj->strategy_loc );
    	self::makeMultisiteFolder($obj->strategy_sitefolder_loc);
    	self::makeMultisiteFolder($obj->strategy_blogfolder_loc);
    	$obj->strategy_folder_check = is_dir($obj->strategy_loc);
    	$obj->strategy_sitefolder_check = is_dir($obj->strategy_sitefolder_loc);
    	$obj->strategy_blogfolder_check = is_dir($obj->strategy_blogfolder_loc);
    	$obj->simcontrol_location_check = is_dir($obj->simcontrol_location);
    	$multisite_strategy_loc = $obj;
    	
    	
    	
    	Return $multisite_strategy_loc;
    }
    protected static function ensureNamespace($obj)
    {
    	if(empty($obj)){
    		Return false;
    	}
    	$obj = str_replace(array('www.','.'),'',$obj);
    	$obj = preg_replace('~[^a-zA-Z0-9]~','',$obj);
    	Return $obj;
    	 
    }
    public static function resolveMultisiteStrategyNamespace()
    {
    	static $namespace;
    	if(isset($namespace))
    	{
    		Return $namespace;
    	}
    	$loc_object = self::resolveMultiSiteStrategyloc();
    	if(!$loc_object)
    	{
    		Return false;
    	}
    	$obj = new stdClass();
    	$obj->namespace = 'S'.self::ensureNamespace($loc_object->currentsite_folder) .'Bl'.self::ensureNamespace($loc_object->blog_path);
    	$obj->config_name = 'MulticacheConfig' . $obj->namespace;
    	$namespace = $obj;
    	
    	
    	Return $namespace;
    	
    }
    public static function createMultisiteDir($dir)
    {
    	self::makeMultisiteFolder($dir );
    }
    
    public static function isHashSame()
    {
    	if(is_multisite())
    	{
    		Return false;
    	}
    	Return true;
    }
    protected static function resolveLocMul($dir , $filename)
    {
    	if(!is_multisite())
    	{
    		return false;
    	}
    	$hash = md5($dir.$filename);
    	static $resolvemul;
    	if(isset($resolvemul[$hash]))
    	{
    		Return $resolvemul[$hash];
    	}
    	    	
    	$dir = rtrim( $dir, '/\\' );
    	$dir = $dir . '/multisite_strategy';
    	self::makeMultisiteFolder($dir  );
    	$current_site = get_current_site();
    	//current site object is not updated when switched to blog
    	$blog_id = get_current_blog_id();
    	$blog_info = get_blog_details( $blog_id );
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
    		
    	}
    	
    	
    	
    	
    	$tid = $s1.'Bl'.$blog_id;
    	$obj['path'] = array('path' =>$b_path , 'tag'=>$tpath);
    	$obj['id'] = array('path' =>$b_idpath , 'tag' => $tid);
    	$resolvemul[$hash] = $obj;
    	Return $resolvemul[$hash];
    	//end
    	
    	
    	//$blog_info = get_blog_details( $blog_id );
    	
    	//var_dump($blog_info);exit;
    }
    
    public static function writeExtStrategy($dir, $filename, $contents)
    {
    	self::writefileTolocation($dir, $filename, $contents);
    }
    
    protected static function writefileTolocation($dir, $filename, $contents , $bypass_multi = false)
    {
    
if(is_multisite() && !$bypass_multi)
{
	$object = self::resolveLocMul($dir , $filename);
	if(empty($object))
	{
		Return false;
	}
	foreach($object As $key => $path)
	{
		$a[] = self::writefileTolocation($path['path'], $filename, $contents , true);
	}
	Return $a;
}
else{
        $file = $dir . '/' . $filename;
}
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
//echo "<br><br>writing to path ".$file."<br><br> <br>";

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
    
    public static function renderViewConfigPageCss()
    {
    	$options = get_option('multicache_config_options');
    	$css_switch = $options['css_switch'];
    	if(empty($css_switch))
    	{
    		Return false;
    	}
    	
    	if (! class_exists('MulticachePageCss'))
    	{
    		$message = __('PageCss class does not exist, please scrape css','multicache-plugin');
    		self::prepareMessageEnqueue($message ,'error');
    		//$app->enqueueMessage($message, 'notice');
    	
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
    		$message = __('PageCss Class does not have any defined properties','multicache-plugin');
            self::log_error($message);
    		Return false;
    	}
    	
    	$csstemplatepage = self::getPageCssObject($pagecss);
    	if(empty($csstemplatepage))
    	{
    		Return false;
    	}
    	$css_stats = new stdClass();
            $css_stats->total_css = property_exists('MulticachePageCss', 'original_css_array') ? count(MulticachePageCss::$original_css_array): 0;
            $css_stats->unique_css = self::getUniqueScripts($pagecss , 'css');
            $css_stats->duplicate_css = (int) $css_stats->total_css - (int) $css_stats->unique_css;
            
            $css_render = self::makeCssRenderable($csstemplatepage->cssobject , $css_stats);
            if (property_exists('MulticachePageCss', 'delayed'))
            {
               	$css_delayed = MulticachePageCss::$delayed;
            	$css_delayed = self::collateDelayed($css_delayed);            
            	$css_defered_render = self::makeCssDelayRenderable($css_delayed);            
            	$css_render .= $css_defered_render;
            }
            
            Return $css_render;
    }
    
    
    public static function renderViewConfigPageScripts()
    {
    	     $options = get_option('multicache_config_options'); 
    	     $js_switch = $options['js_switch'];
    	     if(empty($js_switch))
    	     {
    	     	Return false;
    	     }  
        if (! class_exists('MulticachePageScripts'))
        {
            $message = __('PageScripts class does not exist, please scrape the template','multicache-plugin');
            self::log_error($message);
                        
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
            // register error Multicache Class exists with no proerties
            $message = __('PageScripts Class does not have any defined properties','multicache-plugin');
            self::log_error($message);
            Return false;
        }
        $templatepage = self::getPageScriptObject($pagescripts);
        if(empty($templatepage))
        {
        	Return false;
        }
        $stats = new stdClass();
        $stats->total_scripts = property_exists('MulticachePageScripts', 'original_script_array') ? count(MulticachePageScripts::$original_script_array): 0;
        $stats->unique_scripts = self::getUniqueScripts($pagescripts);
        $stats->duplicate_scripts = (int) $stats->total_scripts - (int) $stats->unique_scripts;
        $segment = new stdClass();
        $segment->segments = self::getSegments($templatepage->pagetransposeobject);
        $segment->segment_peices = count($templatepage->pageobject);
        $segment->unique_script_array = self::$_unique_script;
        $script_render = self::makeSriptRenderable($templatepage->pageobject , $stats);
        //start defferred processing
        $defered_scripts = new stdClass();
        // get social
        if ( property_exists('MulticachePageScripts', 'social'))
        {
        	$social_post_segregation = MulticachePageScripts::$social;
        	$defered_scripts->social_post_segregation = $social_post_segregation;
        }
        // get advertisements
        if ( property_exists('MulticachePageScripts', 'advertisements'))
        {
        	$advertisements_post_segregation = MulticachePageScripts::$advertisements;
        	$defered_scripts->advertisements_post_segregation = $advertisements_post_segregation;
        }
        // get async
        if ( property_exists('MulticachePageScripts', 'async'))
        {
        	$async_post_segregation = MulticachePageScripts::$async;
        	$defered_scripts->async_post_segregation = $async_post_segregation;
        }
        if ( property_exists('MulticachePageScripts', 'delayed'))
        {
        	$delayed_post_segregation = MulticachePageScripts::$delayed;
        	$defered_scripts->delayed = self::collateDelayed($delayed_post_segregation);
        }
        if (! empty($defered_scripts))
        {
        	$script_defered_render = self::makeDeferDelayRenderable($defered_scripts);
        	$script_render .= $script_defered_render;
        }
        Return $script_render;
    }
    
    
    protected static function makeSriptRenderable($script_object , $stat)
    {
if(empty($script_object))
{
	Return false;
}
        $library_key = self::getLibraryKey($script_object); // var_dump($library_class);exit;
                                                             // do we show the loadsection reset button-> only if any one loadsection is set
        $loadsection_class = self::getIsloadsectionClass($script_object);
        
        $loadsection_reset_button = '<button class="btn btn-danger btn-mini offset10 ' . $loadsection_class . '" id="reset_loadsection" title="' . ucfirst(__("Reset Loadsection","multicache-plugin")) . '">reset loadsection</button>';
        
        $script_stat = '<h6><ul class="list-inline list-unstyled" style="list-style: none;"><li class="list-unstyled">Total Scripts - '.$stat->total_scripts.'</li><li class="list-unstyled">Unique Scripts - '.$stat->unique_scripts.'</li></ul></h6>';
        
        $Sno = 0;
        foreach ($script_object as $key => $obj)
        {
            // var_dump( $obj);//exit;
            $Sno ++;
            $cdn_url_default = isset($obj["params"]["cdn_url"]) ? $obj["params"]["cdn_url"] : "";
            $cdn_url_class = isset($obj["params"]["cdn_url"]) ? "" : " hidden";
            $ident_class =   isset($obj["params"]["delay"]) && isset($obj["params"]["delay_type"]) && $obj["params"]["delay"] === '1' && $obj["params"]["delay_type"]=== 'onload' ? "block" : "none";
           
            $mau_display   = isset($obj["params"]["promises"])  && $obj["params"]["promises"] === '1'  ? "block" : "none";
            $mautime_display   = isset($obj["params"]["promises"])  && $obj["params"]["promises"] === '1' && isset($obj["params"]["mau"]) && $obj["params"]["mau"]==='1'  ? "block" : "none";
            $checktype_display = isset($obj["params"]["promises"])  && $obj["params"]["promises"] === '1'  ? "block" : "none";
            $thenBack_display  = isset($obj["params"]["promises"])  && $obj["params"]["promises"] === '1'  ? "block" : "none";
            $ident_default = isset($obj["params"]["ident"]) ? $obj["params"]["ident"] : "";
            $checktype_default = isset($obj["params"]["checktype"]) ? $obj["params"]["checktype"] : "";
            $thenBack_default = isset($obj["params"]["thenBack"]) ? $obj["params"]["thenBack"] : "";
            $mautime_default = isset($obj["params"]["mautime"]) ? $obj["params"]["mautime"] : "";
            if (isset($library_key))
            {
                $library_class = ($key == $library_key) ? '' : ' invisible';
            }
            else
            {
                $library_class = "";
            }
            
            $delay_type = (isset($obj["params"]["delay"])) ? "block" : "none";
            $o_name = ! empty($obj['name']) ? substr($obj['name'], 0, 120) : '';
            $script_layout .= '<div class="content-fluid paddle col-md-12" id="' . $obj["signature"] . '_page_script" >

              <div class="row-fluid center-block margin-buffer" style="max-width:100%;">
                <div class="col-md-1">' . $Sno . '</div>
    		<div class="col-md-3" style="word-wrap: break-word;"><p class="text-left">' . ucfirst(__("Script","multicache-plugin")) . ' : ' . $o_name . '</p></div>

    		<!--library -->
    		<div class="col-md-2 hasTooltip library_selector ' . $library_class . '" title="' . __("Select the principle jQuery library","multicache-plugin") . '">' . ucfirst(__("library","multicache-plugin")) . ' : ' . $obj["library"] . '</div>

    		<!-- load Section -->
    		<div class="col-md-3 hasTooltip loadsection_selector" title="' . __("Choose where to load this script","multicache-plugin") . '">' . ucfirst(__("Load Section","multicache-plugin")) . ' : ' . $obj["loadsection"] . '</div>

    		<!-- Advertisement-->
    		<div class="col-md-3 hasTooltip advertisement_selector" title="' . __("Select if advertisement","multicache-plugin") . '">' . ucfirst(__("Advertisement","multicache-plugin")) . ' : ' . $obj["advertisement"] . '</div>

    		<!-- Social -->
    		<div class="col-md-2 hasTooltip social_selector" title="' . __("Select if social","multicache-plugin") . '">' . ucfirst(__("Social","multicache-plugin")) . ' : ' . $obj["social"] . '</div>

    		</div>
    		 <div class="row-fluid center-block margin-buffer">
    		<!-- Delay -->
    		<div class="col-md-2 hasTooltip delay_selector offset2" title="' . __("Choose to delay this script","multicache-plugin") . '">' . ucfirst(__("Delay")) . ' : ' . $obj["delay"] . '</div>

    		<!-- Delay Type -->
    		<div class="col-md-3 hasTooltip delaytype_selector" style="display:' . $delay_type . ';" title="' . __("Type of delay to execute","multicache-plugin") . '">' . ucfirst(__("Delay Type","multicache-plugin")) . ' : ' . $obj["delay_type"] . '</div>
    				
    		<!--ident-->
    		<div id="multicache_ident_' . $key . '" class="col-md-3 hasTooltip ident_selector center-block " style="display:' . $ident_class . ';"  title="' . __("Specify an identifier","multicache-plugin") . '">
    				<div class="col-md-7 ">' . ucfirst(__("Unique Identifier","multicache-plugin")) . ' :
    	</div><input aria-invalid="false" name="multicache_config_script_tweak_options[ident_' . $key . ']"	 value="' . $ident_default . '" class="input-sm col-sm-4" type="text" pattern="[\w:\/\.\?\=-]+">
    	</div><!--closes multicache_ident -->
           <!-- PROMISES -->
    			
    		<div class="col-md-3 hasTooltip promises_selector offset2" title="' . __("Wrap in Promise","multicache-plugin") . '">' . ucfirst(__("WrapPromise")) . ' : ' . $obj["promises"] . '</div>	
    		<div class="col-md-2 hasTooltip mau_selector offset2" title="' . __("Incorporate MulticacheAsyncUtility","multicache-plugin") . '" style="display:' . $mau_display . ';">' . ucfirst(__("MAU")) . ' : ' . $obj["mau"] . '</div>
    		<!--mau time-->
    		<div id="multicache_mautime_' . $key . '" class="col-md-2 hasTooltip mautime_selector center-block " style="display:' . $mautime_display . ';"  title="' . __("Async Utility Timeout","multicache-plugin") . '">
    				<div class="col-md-7  ">' . ucfirst(__("timeout","multicache-plugin")) . ' :
    	</div><input aria-invalid="false" name="multicache_config_script_tweak_options[mautime_' . $key . ']"	 value="' . $mautime_default . '" class="input-sm col-sm-4" type="text" pattern="[0-9]{2,3}">
    	</div>
    				<!--checktype-->
    				<div id="multicache_checktype_' . $key . '" class="col-md-4 hasTooltip checktype_selector center-block " style="display:' . $checktype_display . ';"  title="' . __("Specify a checkType","multicache-plugin") . '">
    				<div class="col-md-4  ">' . ucfirst(__("checkType","multicache-plugin")) . ' :
    	</div><input aria-invalid="false" name="multicache_config_script_tweak_options[checkType_' . $key . ']"	 value="' . $checktype_default . '" class="input-sm col-sm-6" type="text" pattern="[\w:\/\.\?\=-]+">
    	</div><!--closes multicache_checkType -->
    			<!--thenBack-->
    				<div id="multicache_thenBack_' . $key . '" class="col-md-5 hasTooltip thenBack_selector center-block " style="display:' . $thenBack_display . ';"  title="' . __("Specify a to call if the promise is fulfilled","multicache-plugin") . '">
    				<div class="col-md-5 ">' . ucfirst(__("thenBack Callback","multicache-plugin")) . ' :
    	</div><input aria-invalid="false"  name="multicache_config_script_tweak_options[thenBack_' . $key . ']"	 value="' . htmlentities($thenBack_default) . '" class="input-lg " type="text" ">
    	</div><!--closes multicache_checkType -->
    	   <!-- END OF PROMISES -->
    		<!-- CDN ALIAS -->
    		<div class="col-md-2 hasTooltip cdnalias_selector" title="' . __("Enables cdn substitution","multicache-plugin") . '">' . ucfirst(__("cdn","multicache-plugin")) . ' : ' . $obj["cdnAlias"] . '</div>

    		<!--IGNORE -->
    		<div class="col-md-2 hasTooltip " title="' . __("Choose to ignore","multicache-plugin") . '">' . ucfirst(__("Ignore","multicache-plugin")) . ' : ' . $obj["ignore"] . '</div>
    				
    		</div>
    		<div class="row-fluid center-block ' . $cdn_url_class . '">
    		

    	<!-- CDN URL-->

    	<div id="cdn_urld_' . $key . '" class="col-md-12 hasTooltip cdnurl_selector center-block ' . $cdn_url_class . '" title="' . __("Specify cdn url","multicache-plugin") . '"><div class="col-md-1 col-md-offset-2">' . ucfirst(__("Cdn Url","multicache-plugin")) . ' :
    	</div><input aria-invalid="false" name="multicache_config_script_tweak_options[cdn_url_' . $key . ']"	 value="' . $cdn_url_default . '" class="col-md-8 " type="text" pattern="(https?:)?//.+">
    	</div>
</div>
    </div>';
        }
        $script_layout = $script_stat . $loadsection_reset_button . $script_layout;
        Return $script_layout;
    
    }
    
    
    protected static function collateDelayed($delayed)
    {
    
    	if (empty($delayed))
    	{
    		Return false;
    	}
    	$delay = null;
    	foreach ($delayed as $type => $obj)
    	{
    
    		foreach ($obj as $item => $del)
    		{
    			if ($item == "items")
    			{
    				foreach ($del as $key => $value)
    				{
    					$delay[$key] = $value;
    				}
    			}
    		}
    	}
    
    	Return $delay;
    
    }
    
    
    
    protected static function makeDeferDelayRenderable($defered_object)
    {
    
    	$defered_delayed_script = '';
    	if (! empty($defered_object->social_post_segregation))
    	{
    		// make the social object
    		$social_script = '<div class="social_defered"><legend> ' . __("Social -deferred","multicache-plugin") . '</legend>';
    		$social_script .= self::makeRenderable($defered_object->social_post_segregation, 'social') . '</div>';
    		$defered_delayed_script .= $social_script;
    	}
    
    	if (! empty($defered_object->advertisements_post_segregation))
    	{
    		// make the advertisement object
    		$advertisement_script = '<div class="advertisement_defered"><legend> ' . __("Advertisements - deferred","multicache-plugin") . '</legend>';
    		$advertisement_script .= self::makeRenderable($defered_object->advertisements_post_segregation, 'advertisements') . '</div>';
    		$defered_delayed_script .= $advertisement_script;
    	}
    	if (! empty($defered_object->async_post_segregation))
    	{
    		// make the async object
    		$async_script = '<div class="async_defered"><legend> ' . __("Async","multicache-plugin") . '</legend>';
    		$async_script .= self::makeRenderable($defered_object->async_post_segregation, 'async') . '</div>';
    		$defered_delayed_script .= $async_script;
    	}
    	if (! empty($defered_object->delayed))
    	{
    
    		// make the delay object
    		$delayed_script = '<div class="async_defered"><legend> ' . __("Delayed Scripts") . '</legend>';
    		$delayed_script .= self::makeRenderable($defered_object->delayed, 'delayed') . '</div>';
    		$defered_delayed_script .= $delayed_script;
    	}
    
    	Return $defered_delayed_script;
    
    }
    
    protected static function makeRenderable($script_objects, $type)
    {
    
    	$Sno = 0;
    	$clean_code = array(
    			"'",
    			'"',
    			" ",
    			";"
    	);
    	foreach ($script_objects as $key => $obj)
    	{
    		$Sno ++;
    		if ($type == "delayed")
    		{
    			$delay_type_indicator = '<div class="col-md-3 offset2"><p class="text-left">' . ucfirst(__("Delay Type","multicache-plugin")) . ' : ' . $obj["delay_type"] . '</p></div>';
    		}
    		else
    		{
    			$delay_type_indicator = "";
    		}
    		$name = ! empty($obj["src"]) ? (string) substr($obj["src"], 0, 120) : (string) strip_tags(substr(str_replace($clean_code, '', $obj["code"]), 0, 120));
    
    		$renderable .= '<div class="content-fluid paddle container col-md-12" id="' . $obj["signature"] . '_page_script" >
          <div class="row-fluid center-block margin-buffer">
                        <div class="col-md-1">' . $Sno . '</div>
                        <div class="col-md-3" style="word-wrap: break-word;"><p class="text-left">' . ucfirst(__("Script","multicache-plugin")) . ' : ' . $name . '</p></div>
      ' . $delay_type_indicator . '
          </div>
    
     </div>';
    	}
    	Return $renderable;
    
    }
    
    protected static  function getLibraryKey($obj)
    {
if(empty($obj))
{
	return false;
}
        $library_key = null;
        foreach ($obj as $key => $object)
        {
            if (! empty($object["params"]["library"]))
            {
                $library_key = $key;
                break;
            }
        }
        Return $library_key;
    
    }
    
    protected static function getIsloadsectionClass($obj)
    {
    
    	if (empty($obj))
    	{
    		Return false;
    	}
    	$loadsection_class = 'hidden';
    	foreach ($obj as $key => $object)
    	{
    
    		if ($object["params"]["loadsection"] != 0)
    		{
    			$loadsection_class = '';
    			break;
    		}
    	}
    	Return $loadsection_class;
    
    }
    
    protected static function getSegments($obj)
    {
    
    	$segments = array();
    	foreach ($obj as $key => $segment)
    	{
    		$segments[] = $key;
    	}
    	Return $segments;
    
    }
    
    protected static function makeCssRenderable($css_object , $css_stats)
    {
    
    	if (empty($css_object))
    	{
    		Return false;
    	}
    	$loadsection_class = self::getIsloadsectionClass($css_object);
    
    	$loadsection_reset_button = '<button class="btn btn-danger btn-mini offset10 ' . $loadsection_class . '" id="reset_cssloadsection" title="' . ucfirst(__("Reset css loadsection")) . '">reset loadsection</button>';
    	$css_stat = '<h6>	<ul class="list-inline list-unstyled" style="list-style: none;"><li class="list-unstyled">Total Css - '.$css_stats->total_css.'</li><li class="list-unstyled">Unique Css - '.$css_stats->unique_css.'</li></ul></h6>';
    
    	$Sno = 0;
    	foreach ($css_object as $key => $obj)
    	{
    		// var_dump( $obj);//exit;
    		$Sno ++;
    		$cdn_url_default = isset($obj["params"]["cdn_url_css"]) ? $obj["params"]["cdn_url_css"] : "";
    		$cdn_url_class = isset($obj["params"]["cdn_url_css"]) ? "" : " hidden";
    		if (isset($library_key))
    		{
    			$library_class = ($key == $library_key) ? '' : ' invisible';
    		}
    		else
    		{
    			$library_class = "";
    		}
    
    		$delay_type_style = (isset($obj["params"]["delay"])) ? "block" : "none";
    		$css_layout .= '<div class="content-fluid paddle col-md-12" id="' . $obj["signature"] . '_page_css" >
    
              <div class="row-fluid center-block margin-buffer">
                <div class="col-md-1">' . $Sno . '</div>
    		<div class="col-md-3" style="word-wrap: break-word;"><p class="text-left">' . ucfirst(__("Css","multicache-plugin")) . ' : ' . $obj["name"] . '</p></div>
    
    		<!-- load Section -->
    		<div class="col-md-3 hasTooltip loadsection_selector" title="' . __("Choose where to load the css style","multicache-plugin") . '">' . ucfirst(__("Loadsection","multicache-plugin")) . ' : ' . $obj["loadsection"] . '</div>
    
    		<!-- grouping -->
    		<div class="col-md-2 hasTooltip grouping_selector" title="' . __("Choose whether to group this css","multicache-plugin") . '">' . ucfirst(__("Group")) . ' : ' . $obj["grouping"] . '</div>
    
    		<!-- group_number -->
    		<div class="col-md-3 hasTooltip group_number_selector "  title="' . __("Optional segregation of grouped css code","multicache-plugin") . '">' . ucfirst(__("Group levels","multicache-plugin")) . ' : ' . $obj["group_number"] . '</div>
    
    		</div>
    		 <div class="row-fluid center-block margin-buffer">
    		<!-- Delay -->
    		<div class="col-md-2 hasTooltip delay_selector_css offset2" title="' . __("Choose whether to delay css","multicache-plugin") . '">' . ucfirst(__("Delay")) . ' : ' . $obj["delay"] . '</div>
    
    		<!-- Delay Type -->
    		<div class="col-md-3 hasTooltip delaytype_selector_css" style="display:' . $delay_type_style . ';" title="' . __("Choose a delay type","multicache-plugin") . '">' . ucfirst(__("Delay Type")) . ' : ' . $obj["delay_type"] . '</div>
    
    		<!-- CDN ALIAS -->
    		<div class="col-md-2 hasTooltip cdnalias_selector_css" title="' . __("Choose to load from cdn") . '">' . ucfirst(__("Cdn Alias","multicache-plugin")) . ' : ' . $obj["cdnAlias"] . '</div>
    
    		<!--IGNORE -->
    		<div class="col-md-2 hasTooltip " title="' . __("Choose whether to ignore this css","multicache-plugin") . '">' . ucfirst(__("Ignore","multicache-plugin")) . ' : ' . $obj["ignore"] . '</div>
    		</div>
    		<div class="row-fluid center-block ' . $cdn_url_class . '">
    
    	<!-- CDN URL-->
    
    	<div id="cdn_urld_css' . $key . '" class="col-md-12 hasTooltip cdnurl_selector_css center-block ' . $cdn_url_class . '" title="' . __("Specify the complete cdn url") . '"><div class="col-md-1 col-md-offset-2">' . ucfirst(__("cdn url")) . ' :
    	</div><input aria-invalid="false" name="multicache_config_css_tweak_options[cdn_url_css_' . $key . ']" id="cdn_url_css_' . $key . '" value="' . $cdn_url_default . '" class="col-md-8 " type="text" pattern="(https?:)?//.+">
    	</div>
</div>
    </div>';
    	}
    	$css_layout = $css_stat.$loadsection_reset_button . $css_layout;
    	Return $css_layout;
    
    }
    
    protected static function makeCssDelayRenderable($defered_object)
    {
    
    	$defered_delayed_script = '';
    	if (! empty($defered_object))
    	{
    
    		// make the delay object
    		$delayed_script = '<div class="async_defered"><legend> ' . __("Delayed Css","multicache-plugin") . '</legend>';
    		$delayed_script .= self::makeDelayCssRenderable($defered_object, 'delayed') . '</div>';
    		$defered_delayed_script .= $delayed_script;
    	}
    
    	Return $defered_delayed_script;
    
    }
    
    protected function makeDelayCssRenderable($css_objects, $type)
    {
    
    	$Sno = 0;
    	$clean_code = array(
    			"'",
    			'"',
    			" ",
    			";"
    	);
    	foreach ($css_objects as $key => $obj)
    	{
    		$Sno ++;
    		if ($type == "delayed")
    		{
    			$delay_type_indicator = '<div class="col-md-3 offset2"><p class="text-left">' . ucfirst(__("delay type","Multicache-plugin")) . ' : ' . $obj["delay_type"] . '</p></div>';
    		}
    		else
    		{
    			$delay_type_indicator = "";
    		}
    		$name = ! empty($obj["href"]) ? (string) $obj["href_clean"] : (string) strip_tags(substr(str_replace($clean_code, '', $obj["code"]), 0, 120));
    
    		$renderable .= '<div class="content-fluid paddle col-md-12 container" id="' . $obj["signature"] . '_page_css" >
      <div class="row-fluid center-block margin-buffer">
      <div class="col-md-1">' . $Sno . '</div>
      <div class="col-md-3" style="word-wrap: break-word;"><p class="text-left">' . ucfirst(__("Script","multicache-plugin")) . ' : ' . $name . '</p></div>
      ' . $delay_type_indicator . '
      </div>
    
      </div>';
    	}
    	Return $renderable;
    
    }
    
  public static function toObject($a)
    {
    	    if (is_array($a) ) 
    	    {
        foreach($a as $k => $v) 
        {
            if (is_integer($k))
             {
                // only need this if you want to keep the array indexes separate
                // from the object notation: eg. $o->{1}
                //$a['index'][$k] = $this->toObject($v);
            }
            else {
                $a[$k] = self::toObject($v);
            }
        }

        return (object) $a;
               }

    // else maintain the type of $a
    return $a;
    }
    
    public static function toArray($a)
    {
    	if (is_object($a) )
    	{
    		foreach($a as $k => $v)
    		{
    			if (is_integer($k))
    			{
    				// only need this if you want to keep the array indexes separate
    				// from the object notation: eg. $o->{1}
    				//$a['index'][$k] = $this->toObject($v);
    			}
    			else {
    				$a->$k = self::toArray($v);
    			}
    		}
    
    		return (array) $a;
    	}
    
    	// else maintain the type of $a
    	return $a;
    }
    
    public static function filterInputPost($var = 'multicache_config_css_tweak_options')
    {
    	if(!isset($_REQUEST[$var]))
    	{
    		Return false;
    	}
    	$b = $_REQUEST[$var];
    	$result = array();
    	if(is_array($b))
    	{
    		foreach($b As $key => $value)
    		{
    			if(strpos($key , 'checkType_') !== false || strpos($key , 'thenBack_') !== false)
    			{
    				$new_key = $key;
    				$new_value = $value;
    				$result[$new_key] = $new_value;
    				continue;
    			}
    			$new_key = preg_replace('~[^\w:\/\.\?\=-]~','',$key);
    			$new_value = strpos($key,'_url') !==false ? filter_var( self::validate_host($value) , FILTER_SANITIZE_URL): preg_replace('~[^\w:\/\.\?\=-]~','',$value);
    			/*
    			if(strpos($key,'url') !==false)
    			{
    				$new_value = filter_var( $value , FILTER_SANITIZE_URL);
    			}
    			else {
    			$new_value = preg_replace('~[^a-zA-Z0-9_-]~','',$value);
    			}
    			*/
    			$result[$new_key] = $new_value;
    		}
    	}
    	
    	Return !empty($result) ? $result : null;
    }
    
    public static function clearAllNotices()
    {
    	$notices = array('multicache_admin_generic_notices','multicache_admin_notices');
    	foreach($notices As $notice)
    	{
    		delete_transient($notice);
    	}
    }
    
    public static function sortObjects(&$a, $k, $direction = 1, $caseSensitive = true, $locale = false)
    {
    	if (!is_array($locale) || !is_array($locale[0]))
    	{
    		$locale = array($locale);
    	}
    
    	self::$sortCase = (array) $caseSensitive;
    	self::$sortDirection = (array) $direction;
    	self::$sortKey = (array) $k;
    	self::$sortLocale = $locale;
    
    	usort($a, array(__CLASS__, '_sortObjects'));
    
    	self::$sortCase = null;
    	self::$sortDirection = null;
    	self::$sortKey = null;
    	self::$sortLocale = null;
    
    	return $a;
    }
    
    
    protected static function _sortObjects(&$a, &$b)
    {
    	$key = self::$sortKey;
    
    	for ($i = 0, $count = count($key); $i < $count; $i++)
    	{
    	if (isset(self::$sortDirection[$i]))
    	{
    	$direction = self::$sortDirection[$i];
    	}
    
    		if (isset(self::$sortCase[$i]))
    		{
    		$caseSensitive = self::$sortCase[$i];
    	}
    
    		if (isset(self::$sortLocale[$i]))
    			{
    				$locale = self::$sortLocale[$i];
    			}
    
    			$va = $a->$key[$i];
    			$vb = $b->$key[$i];
    
    			if ((is_bool($va) || is_numeric($va)) && (is_bool($vb) || is_numeric($vb)))
    			{
    			$cmp = $va - $vb;
    	}
    	elseif ($caseSensitive)
    	{
    	$cmp = self::strcmp($va, $vb, $locale);
    	}
    	else
    	{
    	$cmp = self::strcasecmp($va, $vb, $locale);
    	}
    
    	if ($cmp > 0)
    		{
    
    			return $direction;
    	}
    
    	if ($cmp < 0)
    	{
    	return -$direction;
    	}
    	}
    
    	return 0;
    	}
    
    
    
    
    		protected static function strcmp($str1, $str2, $locale = false)
    		{
    		if ($locale)
    		{
    		// Get current locale
    		$locale0 = setlocale(LC_COLLATE, 0);
    
    		if (!$locale = setlocale(LC_COLLATE, $locale))
    		{
    		$locale = $locale0;
    		}
    
    		// See if we have successfully set locale to UTF-8
    		if (!stristr($locale, 'UTF-8') && stristr($locale, '_') && preg_match('~\.(\d+)$~', $locale, $m))
    		{
    		$encoding = 'CP' . $m[1];
    }
    elseif (stristr($locale, 'UTF-8') || stristr($locale, 'utf8'))
    	{
    	$encoding = 'UTF-8';
    	}
    		else
    			{
    			$encoding = 'nonrecodable';
    	}
    
    	// If we successfully set encoding it to utf-8 or encoding is sth weird don't recode
    	if ($encoding == 'UTF-8' || $encoding == 'nonrecodable')
    	{
    	return strcoll($str1, $str2);
    	}
    	else
    	{
    	return strcoll(self::transcode($str1, 'UTF-8', $encoding), self::transcode($str2, 'UTF-8', $encoding));
    	}
    	}
    	else
    	{
    			return strcmp($str1, $str2);
    	}
    	}
    
    
    
protected static function strcasecmp($str1, $str2, $locale = false)
{
    	if ($locale)
    	{
    			// Get current locale
    			$locale0 = setlocale(LC_COLLATE, 0);
    
    			if (!$locale = setlocale(LC_COLLATE, $locale))
    	            {
    	              $locale = $locale0;
                  	}
    
    		// See if we have successfully set locale to UTF-8
    		    if (!stristr($locale, 'UTF-8') && stristr($locale, '_') && preg_match('~\.(\d+)$~', $locale, $m))
    		        {
    		          $encoding = 'CP' . $m[1];
                     }
                elseif (stristr($locale, 'UTF-8') || stristr($locale, 'utf8'))
                     {
                        $encoding = 'UTF-8';
                      }
                 else
                     {
                        $encoding = 'nonrecodable';
                      }
    
    
           if ($encoding == 'UTF-8' || $encoding == 'nonrecodable')
          {
            return strcoll(utf8_strtolower($str1), utf8_strtolower($str2));
           }
            else
           {
    return strcoll(
             self::transcode(utf8_strtolower($str1), 'UTF-8', $encoding),
             self::transcode(utf8_strtolower($str2), 'UTF-8', $encoding)
    	        	);
             }
        }
    	else
    	{
    	return utf8_strcasecmp($str1, $str2);
    }
    }
    
    public static function convertBytes($bytes, $unit = 'auto', $precision = 2)
    {
    	// No explicit casting $bytes to integer here, since it might overflow
    	// on 32-bit systems
    	$precision = (int) $precision;
    
    	if (empty($bytes))
    	{
    		return 0;
    	}
    
    	$unitTypes = array('b', 'kb', 'MB', 'GB', 'TB', 'PB');
    
    	// Default automatic method.
    	$i = floor(log($bytes, 1024));
    
    	// User supplied method:
    	if ($unit !== 'auto' && in_array($unit, $unitTypes))
    	{
    		$i = array_search($unit, $unitTypes, true);
    	}
    
    	// TODO Allow conversion of units where $bytes = '32M'.
    
    	return round($bytes / pow(1024, $i), $precision) . ' ' . $unitTypes[$i];
    }
    
    public static function establish_factors($precache_factor, $ccomp_factor , $m_s = false)
    {
    	if(is_multisite() && !empty($m_s))
    	{
    		$config_path = $m_s['strategy']['config_loc'];
    		$config_name = $m_s['strategy']['config_name'];
    		if($m_s['strategy']['config_exists'])
    		{
    			require_once $config_path;
    		}
    		if(class_exists($config_name))
    		{
    			$config = new $config_name;
    			$config->ccomp_factor = $ccomp_factor;
    			$config->precache_factor = $precache_factor;
    			$config->author = 'establish_factors_multisite';
    			self::writeToConfig($config , false , true , false , false , false , $config_path  , $config_name );
    			Return true;
    		}
    		
    		self::log_error(__('Multisite Config error in establish factors ' , 'multicache-plugin') ,'config-rewrite', $c);
    		Return false;
    	}
    require_once plugin_dir_path(dirname(__FILE__)).'libs/'.'multicache_config.php';
    	//require_once (JPATH_CONFIGURATION . '/configuration.php');
    	// Create a JConfig object
    	$config = new MulticacheConfig();
    	$config->ccomp_factor = $ccomp_factor;
    	$config->precache_factor = $precache_factor;
    	$config->author = 'establish_factors';
    	
    	//self::writeConfigFile($registry);
    	self::writeToConfig($config);
    
    }
    
    public static function getNewBlogHash()
    {
    	$hash_same = MulticacheHelper::isHashSame();
    	if($hash_same)
    	{
    	$hash = md5(MulticacheUri::root() . date('Y-m-d', strtotime('-1 year')));
    	}
    	else
    	{
    	$blog_id = $blog_id = get_current_blog_id();
    	$hash = md5(MulticacheUri::root().$blog_id . date('Y-m-d', strtotime('-1 year')));
    	}
    	Return $hash;
    }
    public static function loadNetworkConfig()
    {
    	$c_file = dirname(dirname(__FILE__)).'/libs/multicache_config.php';
    	if(is_file($c_file))
    	{
    		require_once $c_file;
    		$conf = new MulticacheConfig();
    		Return $conf;
    	}
    	Return false;
    }
    public static function validateConfig(/*MulticacheConfig*/ $config)
    {
    	if(empty($config))
    	{
    		Return false;
    	}
    	$hash_same = MulticacheHelper::isHashSame();
    	$c = $config;
    	$queue = '';
    	foreach($config As $config_keys => $config_val)
    	{
    		
    		if ($config_keys == 'secret' && empty($config_val))
    		{
    			$config->secret = self::getNewBlogHash();
    			$queue .=' secret '; 
    		}
    		elseif ($config_keys == 'live_site'  && (empty($config_val) ||is_multisite()))
    		{
    			if(is_multisite())
    			{
    			$obj = self::resolveMultiSiteStrategyloc();	
    			$obj = empty($obj)? MulticacheUri::root() : $obj->live_site;
    			
    			}
    			else {
    			$config->live_site = MulticacheUri::root();
    			}
    			$queue .=' live_site ';
    		}
    		elseif ($config_keys == 'absolute_path' && empty($config_val))
    		{
    			$config->absolute_path = defined('ABSPATH') ? ABSPATH : null;
    			$queue .=' absolute_path ';
    		}
    		elseif ($config_keys == 'plugin_dir_path' && empty($config_val))
    		{
    			$f_dir = plugin_dir_path(dirname(__FILE__));//workarounds for earlier php 
    			$config->plugin_dir_path = !empty($f_dir) ? $f_dir : null;
    			$queue .=' plugin_dir_path ';
    		}
    		elseif ($config_keys == 'precache_factor' && empty($config_val) && strcmp($config_val, '') === 0 )
    		{
    			$config->precache_factor = 2;
    			$queue .=' precache_factor ';
    		}
    		elseif ($config_keys == 'ccomp_factor' && empty($config_val) && strcmp($config_val, '') === 0 )
    		{
    			$config->ccomp_factor = 0.22;
    			$queue .=' ccomp_factor ';
    		}
    		
    		if ($config_keys == 'secret' && !empty($config_val) && is_multisite() && !$hash_same && !self::isNetwork())
    		{
    			
    			$network_config = self::loadNetworkConfig();
    			$newtork_secret = false !== $network_config? $network_config->secret: false;
    			$config->secret = $newtork_secret == $config_val? self::getNewBlogHash() : $config_val;
    			$queue .=' secret changed  '.$config_val;
    		}
    		if(!empty($queue))
    		{
    			self::log_error(__('Config error ' , 'multicache-plugin') ,'config-rewrite', $c);
    		}
    		
    	}
    	Return $config;
    }
    public static function isNetwork()
    {
    	$uri = MulticacheUri::getInstance();
    	$path = $uri->getPath();
    	$u_split = explode('/',$path);
    	if($u_split[1]=='wp-admin' && $u_split[2]=='network')
    	{
    		return true;
    	}
    	Return false;
    }
    public static function checkCurlable($u)
    {
    	if(strpos(trim($u) , 'http://') !== 0 && strpos(trim($u) , 'https://') !== 0)
    	{
    		//check for //
    		if(strpos($u, '//') === 0)
    		{
    			$u = is_ssl()?  'https:'.$u: 'http:'.$u;
    		}
    		elseif(preg_match('~^[a-zA-Z0-9]+~' , $u))
    		{
    			$u = is_ssl()?  'https://'.$u: 'http://'.$u;
    		}
    	}
    	Return $u;
    }
    
    public static function getTolerances()
    {
    	static $tolerances = null;
    	if(isset($tolerances))
    	{
    		Return $tolerances;
    	}
    	$options = get_option('multicache_config_options');
    	$tolerance_params = $options['tolerance_params'];
    	if(empty($tolerance_params))
    	{
    		Return false;
    	}
    	$tolerances = json_decode($tolerance_params);
    	Return $tolerances;
    }
    
    public static function getoAuthTransients()
    {
    	static $trans = null;
    	if(isset($trans))
    	{
    		Return $trans;
    	}
    	$trans_name = 'multicache_auth_'.get_current_user_id();
    	$transient = get_transient($trans_name);
    	if(empty($transient))
    	{
    		Return false;
    	}
    	$trans = unserialize($transient);
    	Return $trans;
    }
    
    protected static function renamebackup($file , $newname = 'advanced-cache-backup.php' , $old_bit ='advanced-cache.php' )
    {
    	$oldname = $file;
    	$newname = str_ireplace( $old_bit , $newname  ,$file);
    	Return rename($oldname, $newname);
    }
    
    public static function checkAdvancedCacheSetting()
    {
    	$install_path = defined('WP_CONTENT_DIR')? WP_CONTENT_DIR. '/advanced-cache.php' : null;
    	if(file_exists($install_path))
    	{
    		require_once $install_path;
    		if(class_exists('Multicache_AdvancedCache'))
    		{
    			unlink($install_path);
    			$install_path = str_ireplace('advanced-cache.php','advanced-cache-backup.php',$install_path);
    			if(file_exists($install_path))
    			{
    				self::renamebackup($install_path , 'advanced-cache.php' , 'advanced-cache-backup.php');
    			}
    		}
    		
    	}
    }
    
    public static function prepareAdvancedCacheforInstall()
    {
    	$install_path = defined('WP_CONTENT_DIR')? WP_CONTENT_DIR. '/advanced-cache.php' : null;
    	$plugin_path = plugin_dir_path(dirname(__FILE__));
    	$factory_lib_path = $plugin_path . 'libs/multicache_factory.php';
    	$cache_lib_path   = $plugin_path . 'libs/multicache.php';
    	$config_lib_path  = $plugin_path . 'libs/multicache_config.php';
    	$m_uri_lib_path    = $plugin_path . 'libs/multicache_uri.php';
    	
    	if(!file_exists($factory_lib_path) || !file_exists($cache_lib_path) || !file_exists($config_lib_path) || !file_exists($m_uri_lib_path))
    	{
    		self::prepareMessageEnqueue(__('Advanced Cache cannot install, config not ready' , 'multicache-plugin'),'error');
    		Return;
    	}
    	if(!isset($install_path))
    	{
    		if(defined('WP_CONTENT_DIR'))
    		{
    			$install_path = WP_CONTENT_DIR  .'/advanced-cache.php';
    		}
    		elseif(defined('ABSPATH'))
    		{
    			$install_path = ABSPATH .'wp-content/advanced-cache.php';
    		} 
    		else 
    		{
    			self::log_error(__('prepareAdvanced Cache failed to install paths not defined' , 'multicache-plugin'),'advanced-cache-install-error');
    		}
    	}
    	//check whether file exists
    	if(file_exists($install_path))
    	{
    		require_once $install_path;
    		if(class_exists('Multicache_AdvancedCache'))
    		{
    			Return;
    		}
    			//the file is of another cache
    			$renamed = self::renamebackup($install_path);
    			if(!$renamed)
    			{
    				self::log_error(__('prepareAdvancedCache: ADvanced cache already exists cannot backup' , 'multicache-plugin'),'advanced-cache-install-error');
    				Return;
    			}	
    	}
    	
    	$advancedcache_content = "<?php

/**
 * MulticacheWP
 * http://www.multicache.org
 * High Performance fastcache Controller
 * Version: MU 1.0.0.2
 * Author: Wayne DSouza
 * Author URI: http://onlinemarketingconsultants.in
 * License: GNU PUBLIC LICENSE see license.txt
 */
defined('ABSPATH') or die();

if (! defined('_MULTICACHEWP_EXEC'))
{
define('_MULTICACHEWP_EXEC', '1');
}";
    	$advancedcache_content .= "
    			if(file_exists('$factory_lib_path'))
{
require_once   '$factory_lib_path';
require_once   '$cache_lib_path';
require_once   '$m_uri_lib_path';
}
    			";
    	$advancedcache_content .= "
class Multicache_AdvancedCache
{
	
public static function multicache_get_open_cached()
{
if(strtoupper(\$_SERVER['REQUEST_METHOD']) !== 'GET')
{
	Return;
}

switch (true) {
	case defined('DONOTCACHEPAGE'):
	case is_admin():
	case defined('PHP_SAPI') && PHP_SAPI === 'cli':
	case defined('SID') && SID != '':
	case defined('DOING_CRON'):
    case defined('DOING_AJAX'):
	case defined('APP_REQUEST'):
	case defined('XMLRPC_REQUEST'):
	case defined('WP_ADMIN'):
	case (defined('SHORTINIT') && SHORTINIT):
		
		return ;
}
\$uri = MulticacheUri::getInstance();
\$id = \$uri->toString();
if(        strpos(\$id ,'/wp-content/' ) !== false
		|| strpos(\$id ,'/wp-includes/' ) !== false
		|| strpos(\$id ,'/images/' ) !== false
		|| strpos(\$id ,'/robots.txt') !== false
		|| strpos(\$id , '/xmlrpc.php') !== false
		|| strpos(\$id , '/wp-json/') !== false
		|| strpos(\$id , '/wp-login.php') !== false
    	|| strpos(\$id , '_is_ajax_call=') !== false
		|| strpos(\$id , '_request_ver=') !== false
    			)
{
	/*
	 * We do not want to cache application pages
	 * example a plugin curls a web page both head and body tags will be available
	 * /wp-admin/ is taken care of in is_admin()
	 */
	Return;
}
\$multicache_conf = MulticacheFactory::getBlogConfig();
 if(empty(\$multicache_conf) 
		|| (isset(\$multicache_conf) 
				&& null === \$multicache_conf->getC('cache_handler')
				&& !is_admin()
				
				)
		|| (isset(\$multicache_conf)  && '0' === \$multicache_conf->getC('network_activate'))
		|| isset(\$multicache_conf) &&   'network'  === \$multicache_conf->getC('name')&& !is_network_admin() )
{
	Return;
}
if( null !== (\$dirs = \$multicache_conf->getC('bp_dirs')))
{
	foreach(\$dirs As \$dir)
	{
		if(strpos(\$id , \$dir)!== false)
		{
			Return;
		}
			
	}
		
}    			
\$query = \$uri->getQuery(true);
if(isset(\$query['preview']) && \$query['preview'] == true )
{
	return ;
}
if(isset(\$query['_wpnonce']) || isset(\$query['rest_route'])  )
{
	return;
}
if(isset(\$_COOKIE))
{
//were not checking for logged in users here as we do not have the user id so we simply pass logged in users to the next stage of verification
	foreach (array_keys(\$_COOKIE) as \$cookie_name) {
		if (strpos(\$cookie_name, 'wordpress_logged_in') === 0)
			return ;
	}
}

    			
//no cache for set carts

if(\$multicache_conf->getC('multicachedistribution') === '0')
{
	/*
	global \$woocommerce;
	if(isset(\$woocommerce->session) && null !== \$woocommerce->session->get('cart'))
	{
		//cart is set - no cache
		Return;
	}
	*/
	Return;
}
\$multicache_options = array(
		'defaultgroup' => 'page',
		'subgroup' => false,
		'user' => false,
		'browsercache' => 1, // need to get this from admin
		'caching' => \$conf->getC('caching') >= 1? true:false
);
\$multicache_page_cache = Multicache::getInstance('page', \$multicache_options);
	
	\$user_id = 0;
	

	\$c_obj = \$multicache_page_cache->get(\$id ,null,  \$user_id,'page');
	//var_dump(\$c_obj);
      if(\$c_obj !== false)
      {
       \$app = MulticacheFactory::getApplication();
        \$app->setBody(\$c_obj);
       
        echo \$app->toString(\$app->get('gzip' , true)); 
      exit;
       }
	
}
}
Multicache_AdvancedCache::multicache_get_open_cached();";
    	ob_start();
    	echo $advancedcache_content;
    	$cl_buf = ob_get_clean();
    	$cl_buf = serialize(trim($cl_buf));
    	
    	$dir = str_ireplace('/advanced-cache.php' , '' , $install_path);
    	$filename = 'advanced-cache.php';
    	//need to revisit this for multisite
    	$success = self::writefileTolocation($dir, $filename, $cl_buf , true);
    	
    	Return $success;    	
    	
    	
    }
    //test this on multisite
    public static function get_bp_directories($c = false)
    {
    	if(!function_exists('bp_core_get_directory_page_ids'))
    	{
    		return false;
    	}
    	$pages = bp_core_get_directory_page_ids();
    	if(empty($pages))
    	{
    		Return false;
    	}
    	$bp_pages = array();
    	if(!empty($c->sub_folderinstall))
    	{
    		//
    		$search = '~'.$c->sub_folderinstall.'([^\/\s]+/)~six';
    	}
    	else {
    		$search =   '~((?<!\/)/[^\/\s]+/)~six';
    	}
    	foreach($pages As $key => $page_id)
    	{
    		$page_string = get_permalink($page_id);
    		preg_match( $search, $page_string , $m);
    		if(!empty($m[1]))
    		{
    			$m[1] = substr($m[1],0 , 1) !== '/'? '/'.$m[1]: $m[1];
    		}
    
    		$bp_pages[$key] = !empty($m[1]) ? $m[1] : null;
    	}
    	Return $bp_pages;
    }
}
