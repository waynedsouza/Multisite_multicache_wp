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
 * Generic Buttons
 */
function makeDateButton($option, $opvar, $get_var, $title_tag , $class = null)
{
 $class_name = isset($class) ? "input-medium hasTooltip ".$class : "input-medium hasTooltip ";
	$tag = '<input type="text" title="" name="' . $option . '[' . $opvar . ']" id="' . $option . '_' . $opvar . '" value="' . $get_var . '" maxlength="45" class="'.$class_name.'" data-original-title="' . $title_tag . '" aria-invalid="false">';
	// $tag .= '<button type="button" class="btn" id="' . $option . '_' . $opvar . '_img"><span class="icon-calendar"></span></button>';
	Return $tag;

}

function makeTextButton($option, $opvar, $get_var , $rows = 1,  $cols = 10)
{

	$tag = '<textarea name="' . $option . '[' . $opvar . ']" id="multicache_' . $opvar . '" cols="'.$cols.'" rows="'.$rows.'" aria-invalid="false">' . $get_var . '</textarea>';
	Return $tag;

}

function makeSelectButtonNumeric($option, $opvar, $get_var = '20', $required = false, $title_tag = '', $start = '0', $stop = '100', $interval = '1', $labels = null, $third_param = null , $class_adds = null ,$placeholder = null)
{

	if (isset($labels))
	{
		foreach ($labels as $key => $val)
		{
			$labels[$key] = __($val, 'multicache-plugin');
		}
	}
	$start = (int) $start;
	$stop = (int) $stop;
	$interval = (int) $interval;
	$get_var = (int) $get_var;
	$class = isset($class_adds) ? "chzn-chosen col-md-2 ".$class_adds:"chzn-chosen col-md-2";

	$tag = isset($third_param) ? '<select id="' . $third_param . '_' . $opvar . '_multicache_config"	name="' . $option . '[' . $opvar . '][' . $third_param . ']"
	size="6"  class="'.$class.'" style="width:153px;"' : '<select id="' . $opvar . '_multicache_config"	name="' . $option . '[' . $opvar . ']"
	size="6"  class="'.$class.'" style="width:153px;"';
	if (! empty($title_tag))
	{
		$tag .= ' title="' . $title_tag . '"';
	}
	if (! empty($required))
	{
		$tag .= 'required="required"';
	}
	if(isset($placeholder))
	{
		$tag .= ' data-placeholder="'.$placeholder.'" ';
	}
	$tag .= '  aria-required="true">';
	for ($i = $start; $i <= $stop; $i += $interval)
	{
		$lab = isset($labels[$i]) ? $labels[$i] : $i;
		if ($i == $get_var)
		{
			$tag .= '	<option selected="selected" value="' . $i . '" >' . $lab . '</option>';
		}
		else
		{
			$tag .= '	<option value="' . $i . '" >' . $lab . '</option>';
		}
	}

	$tag .= '  </select>';
	Return $tag;

}


function makeSelectButtonTextual($option, $opvar, $get_var = '20', $required = false, $title_tag = '',  $labels = null, $third_param = null , $class_adds = null ,$placeholder = null)
{

	if (!empty($labels))
	{
		foreach ($labels as $key => $val)
		{
			$labels[$key] = __($val, 'multicache-plugin');
		}
	}
	
	
	$class = isset($class_adds) ? "chzn-chosen col-md-2 ".$class_adds:"chzn-chosen col-md-2";

	$tag = isset($third_param) ? '<select id="' . $third_param . '_' . $opvar . '_multicache_config"	name="' . $option . '[' . $opvar . '][' . $third_param . ']"
	size="6"  class="'.$class.'" style="width:153px;"' : '<select id="' . $opvar . '_multicache_config"	name="' . $option . '[' . $opvar . ']"
	size="6"  class="'.$class.'" style="width:153px;"';
	if (! empty($title_tag))
	{
		$tag .= ' title="' . $title_tag . '"';
	}
	if (! empty($required))
	{
		$tag .= 'required="required"';
	}
	if(isset($placeholder))
	{
		$tag .= ' data-placeholder="'.$placeholder.'" ';
	}
	$tag .= '  aria-required="true">';
	
	if(isset($placeholder))
	{
		$tag .= '	<option  value="" ></option>';
	}
	
	if(!empty($labels))
	{
	foreach($labels As $key => $value)
	{
		if ($key === $get_var)
		{
			$tag .= '	<option selected="selected" value="' . $key . '" >' . $value . '</option>';
		}
		else
		{
			$tag .= '	<option value="' . $key . '" >' .$value . '</option>';
		}
	}
	}
	


	$tag .= '  </select>';
	Return $tag;

}

function makeCheckBox($option, $opvar, $get_var , $value = 1)
{
	$checked = !empty($get_var) ? ' checked ': '';
	$tag = '<input type="checkbox" name="' . $option . '[' . $opvar . ']" id="multicache_' . $opvar . '" value="' . $value . '" aria-invalid="false" '.$checked.' >';
	Return $tag;

}

function makeMultiRadioButton($option, $opvar, $get_var, $label = array(0=>'Yes',1=>'No'), $way_no = 2)
{

	$tag = '<fieldset id="' . $option . '[' . $opvar . ']"';

	if ($way_no > 2)
	{
		$tag .= 'class="radio btn-group-multi ">';
	}
	else
	{
		$tag .= 'class="radio btn-group btn-group-yesno">';
	}
	for ($i = 0; $i < $way_no; $i ++)
	{
	$styletype= '';
	if ($i == 0)
	{
	$classtype = ' first-button ';
		$styletype ="border-top-right-radius: 0px; 	border-bottom-right-radius: 0px;";
        }
        elseif ($i == ($way_no - 1))
		{
		$classtype = ' last-button ';
	}
	else
	{
		$classtype = ' square-button ';
	}
	$tag .= '<input type="radio" id="' . $option . '_' . $opvar . $i . '"
	name="' . $option . '[' . $opvar . ']" value="' . ($way_no - 1 - $i) . '"';

	if ($way_no-1-$i == $get_var)
	{
	$tag .= "checked=\"checked\"";
	}

	$tag .= '><label for="' . $option . '_' . $opvar . $i . '"';

	if ($way_no-1-$i == $get_var)
		{
		$tag .= "class=\"btn active btn-success " . $classtype . "\"";

        }
        else
        {
        $tag .= "class=\"btn " . $classtype . "\"";
	}
	$tag .= 'style="'.$styletype.'">' . $label[$way_no - 1 - $i] . '
</label>';
		}
		$tag .= '</fieldset>';
		Return $tag;

	}

	function makeRadioButton($option, $opvar, $get_var, $compare_var = array(0=>1,1=>0), $yes = 'Yes', $no = 'No', $val_yes = 1, $val_no = 0)
	{

	$tag = '<fieldset id="' . $option . '[' . $opvar . ']"
	class="radio btn-group btn-group-yesno">
	<input type="radio" id="' . $option . '_' . $opvar . '0"
	name="' . $option . '[' . $opvar . ']" value="' . $val_yes . '"';
	if ($get_var == $compare_var[0]) $tag .= "checked=\"checked\"";
    $tag .= '><label for="' . $option . '_' . $opvar . '0"';

			if ($get_var == $compare_var[0])
			{
			$tag .= "class=\"btn active btn-success\"";
	}
	else
	{
	$tag .= "class=\"btn\"";
	}

    $tag .= 'style="border-top-left-radius: 4px; border-bottom-left-radius: 4px;">' . $yes . '
	</label>
	<input type="radio" id="' . $option . '_' . $opvar . '1"	name="' . $option . '[' . $opvar . ']" value="' . $val_no . '" ';

    if ($get_var == $compare_var[1]) $tag .= "checked=\"checked\"";
    $tag .= '>
<label for="' . $option . '_' . $opvar . '1"	';
    if ($get_var == $compare_var[1])
    {
    $tag .= "class=\"btn active btn-danger\"";
    }
    		else
    			{
        $tag .= "class=\"btn \"";
	}
	$tag .= 'class="btn btn-normal" 	style="border-top-right-radius: 4px; border-bottom-right-radius: 4px;">' . $no . '</label>
	</fieldset>';
	Return $tag;

        }
        
        function makeColorInput($option , $opvar , $getvar , $title_tag = null, $classadds = ' minicolors minicolors-input input-sm')
        {
        	$tag = '<input type="text" 
name="'.$option.'['.$opvar.']" 
id="'.$option.'_'.$opvar.'" 
value="'.$getvar.'" 
placeholder="#rrggbb" 
class="'.$classadds.'" ';
        	if (! empty($title_tag))
        	{
        		$tag .= ' title="' . $title_tag . '"';
        	}
$tag .= ' data-position="right" '; 
$tag .= ' data-control="hue"/>';
        	Return $tag;
        }

function makeSelectButton($option, $opvar, $get_var, $compare_var = array(0=>array('key' =>1,'val'=>1),1=>array('key'=>0,'val'=>0)), $required = false, $title_tag = '', $third_param = null , $width_class = ' col-md-2')
{
	$style_tag = null;
	if(strpos($width_class,'col-md-1') !== false)
	{
		$style_tag = ' style="width:75px;"';
	}
    $tag = isset($third_param) ? '<select id="' . $third_param . '_' . $opvar . '_multicache_config"	name="' . $option . '[' . $opvar . '][' . $third_param . ']"
    		class="chzn-chosen '.$width_class.'"' : '<select id="' . $opvar . '_multicache_config"	name="' . $option . '[' . $opvar . ']"
    		class="chzn-chosen '.$width_class.'"';
    		if (! empty($title_tag))
    {
        $tag .= ' title="' . $title_tag . '"';
        }
    if (! empty($required))
    {
        $tag .= 'required="required"';
    }
    $tag .= isset($style_tag)? $style_tag : '';
    $tag .= '  aria-required="true">';

    if ($get_var == $compare_var[0]['key'])
    		{
    		$tag .= '	<option value="' . $compare_var[0]['key'] . '" selected="selected">' . $compare_var[0]['val'] . '</option>
	<option value="' . $compare_var[1]['key'] . '">' . $compare_var[1]['val'] . '</option>';
        }
        else
        {
        	$tag .= '  <option value="' . $compare_var[0]['key'] . '">' . $compare_var[0]['val'] . '</option>
        	<option value="' . $compare_var[1]['key'] . '" selected="selected">' . $compare_var[1]['val'] . '</option>';
        }

        	$tag .= '  </select>';
        			Return $tag;

        }

        function makeInputButton($option, $opvar, $get_var, $required = false, $title_tag = '', $class_args = ' multicache-inputbox', $width = '')
        	{

        	$tag = '<input type="text" name="' . $option . '[' . $opvar . ']"
        	id="multicache_' . $opvar . '" value="' . $get_var . '" ';
        	if (empty($width) && $class_args == ' multicache-inputbox')
    {
        $tag .= ' class="required ' . $class_args . ' col-md-2"';
        	}
        		elseif (! empty($class_args) && empty($width) && $class_args != ' multicache-inputbox')
        		{
        $tag .= ' class="required ' . $class_args . '" ';
        	}
        	elseif (! empty($width))
        	{
        	$tag .= ' class="required ' . $class_args . '" style="width:' . $width . '"';
        	}
        	else
        	{
        	$tag .= ' class="required ' . $class_args . '"';
        	}

    if (! empty($title_tag))
        	{
        	$tag .= ' title="' . $title_tag . '"';
        	}
        	if (! empty($required))
        	{
        	$tag .= ' required="required" ';
        	}
        			$tag .= ' aria-required="true"	autocomplete="off" aria-invalid="false" />';
        			Return $tag;

        	}
        	
        	function makeDelayButton($option, $opvar, $get_var , $required = false, $title_tag = '',  $labels = null, $third_param = null , $class_adds = null ,$placeholder = null)
        	{
        	
        		if (isset($labels))
        		{
        			foreach ($labels as $key => $val)
        			{
        				$labels[$key] = __($val, 'multicache-plugin');
        			}
        		}
        		
        		$class = isset($class_adds) ? "chzn-chosen col-md-2 ".$class_adds:"chzn-chosen col-md-2";
        	
        		$tag = isset($third_param) ? '<select id="' . $third_param . '_' . $opvar . '_multicache_config"	name="' . $option . '[' . $opvar . '][' . $third_param . ']"
	size="6"  class="'.$class.'" style="width:153px;"' : '<select id="' . $opvar . '_multicache_config"	name="' . $option . '[' . $opvar . ']"
	size="6"  class="'.$class.'" style="width:153px;"';
        		if (! empty($title_tag))
        		{
        			$tag .= ' title="' . $title_tag . '"';
        		}
        		if (! empty($required))
        		{
        			$tag .= 'required="required"';
        		}
        		if(isset($placeholder))
        		{
        			$tag .= ' data-placeholder="'.$placeholder.'" ';
        		}
        		$tag .= '  aria-required="true">';
        		foreach ($labels As $key => $val)
        		{
        			
        			if ($key == $get_var)
        			{
        				$tag .= '	<option selected="selected" value="' . $key . '" >' . $val . '</option>';
        			}
        			else
        			{
        				$tag .= '	<option value="' . $key . '" >' . $val . '</option>';
        			}
        		}
        	
        		$tag .= '  </select>';
        		Return $tag;
        	
        	}

/*
 * Cuddles
 */
        	

        	function multicache_halfcuddleopen_group()
        	{
        	
        		?>
        	<div class="halfcuddle col-md-6">
        	    <?php
        	
        	}
        	
        	function multicache_halfcuddleclose_group()
        	{
        	
        	    ?>
        	    </div>
        	<?php
        	
        	}
        	
        	function multicache_fullcuddleopen_group()
        	{
        	
        	    ?>
        	<div class="halfcuddle col-md-12">
        	        <?php
        	
        	}
        	
        	function multicache_fullcuddleclose_group()
        	{
        	
        	    ?>
        	    </div>
        	<?php
        	
        	}
        	
        	        	


