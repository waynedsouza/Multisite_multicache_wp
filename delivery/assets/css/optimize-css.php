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
//exit;
const DOUBLE_QUOTE_STRING = '"(?>(?:\\\\.)?[^\\\\"]*+)+?(?:"|(?=$))';
    // regex for single quoted string
    const SINGLE_QUOTE_STRING = "'(?>(?:\\\\.)?[^\\\\']*+)+?(?:'|(?=$))";
    // regex for block comments
    const BLOCK_COMMENTS = '/\*(?>[^/\*]++|//|\*(?!/)|(?<!\*)/)*+\*/';
    // regex for line comments
    const LINE_COMMENTS = '//[^\r\n]*+';

    const URI = '(?<=url)\([^)]*+\)';
$names = array(
'group-1.css',
'group-2.css'
);
$target = array(
'gazette/images/ico-watch.gif',
'gazette/images/ico-arrow.gif',
'gazette/images/ico-comm.gif',
'gazette/images/ico-arcfeed.gif',
'themes/gazette/images/fleche1.png',
'gazette/images/fleche2.png',
'nice-login-register-widget/images/NLW25x23.png',
'themes/gazette/styles/default/ico-catlist.gif',
);
$notfound = array();
$root = $_SERVER["DOCUMENT_ROOT"];
$dir = dirname(__FILE__);

if(empty($names))
{
exit;
}
foreach($names As $name)
{
//$path = $root .'/media/com_multicache/assets/css/csscache/' . $name;
$path = $dir .'/csscache/' . $name;
$fe = file_exists($path);
if(!$fe)continue;
$contents = @file_get_contents($path);
if(false === $contents )
{
	
	continue;
}
$contents = makeBaseContents($contents , $dir);
//$path_enhanced = $root .'/media/com_multicache/assets/css/csscache/' .'enhanced-'. $name;
$path_enhanced = $dir .'/csscache/'  .'enhanced-'. $name;

$success = writeToFile($path_enhanced ,$contents );
var_dump($success);

if(!empty($notfound))
{
	echo "<h3>404 Not Found</h3>";
	print_r($notfound);
}
}

function writeTofile( $path , $data)
{

$fileopen = @fopen($path, "wb");
        
        if ($fileopen)
        {
            $len = strlen($data);
            @fwrite($fileopen, $data, $len);
            $written = true;
        }
        
        if ($written && ($data == file_get_contents($path)))
        {
            return true;
        }
        Return false;
}

function makeBaseContents($content , $root)
{
$e = DOUBLE_QUOTE_STRING . '|' . SINGLE_QUOTE_STRING . '|' . BLOCK_COMMENTS . '|' . LINE_COMMENTS;

        $replacedContent = preg_replace_callback("#(?>[(]?[^('\"/]*+(?:{$e}|/)?)*?(?:(?<=url)\(\s*+\K['\"]?((?<!['\"])[^\s)]*+|(?<!')[^\"]*+|[^']*+)['\"]?|\K$)#i", 'replaceImages', $content);
        
        Return $replacedContent;
}

function replaceImages($match)
{
global $target;
global $root;
global $dir;
global $notfound;
$found = false;
foreach($target As $tgt)
{
if(strpos($match[0] , $tgt) !== false)
{
$found = true;
break;
}
}
if(!$found)return $match[0];
if(strpos( $match[0] , 'http://') === false && strpos( $match[0] , 'https://') === false)
{
	
if(strpos($match[0] , '/') === 0)
{
$path = $root . $match[0];
}
elseif(preg_match('~^[a-zA-Z]~' , $match[0]))
{
$path = $root .'/'. $match[0];
}
}
else {
	
	$path = $match[0];
}
$c = @file_get_contents($path);

if($c === false)
{
$notfound[] = $path;
return $match[0];
}
$type = pathinfo($path, PATHINFO_EXTENSION);
$base64 = 'data:image/' . $type . ';base64,' . base64_encode($c);
return $base64;
}