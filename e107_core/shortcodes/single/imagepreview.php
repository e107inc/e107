<?php 
// $Id$

function imagepreview_shortcode($parm)
{
	list($name, $width, $height) = explode("|",$parm, 3);
	
	$name = rawurldecode(varset($name));//avoid warnings
	if(varset($width))
	{
		$width = " width: {$width};";
	}
	else $width = '';
	if(varset($height))
	{
		$height = " height: {$height};";
	}
	else $height = '';
		
	// array support
	if(strpos($name, '[')) //can't be first string
	{
		$matches = array(); 
		$search = $name;
		
		$tmp = explode('[', $name, 2);
		$name = $tmp[0]; unset($tmp);
		
		$path = '';

		if(isset($_POST[$name]) && is_array($_POST[$name]) && preg_match_all('#\[([\w]*)\]#', $search, $matches, PREG_PATTERN_ORDER))
		{
			$posted = $_POST[$name];
			foreach ($matches[1] as $varname) 
			{
				if(!isset($posted[$varname]))
				{
					break;
				}
				$posted = $posted[$varname];
			}
		}
		if($posted && is_string($posted)) 
			$path = e107::getParser()->replaceConstants($posted, 'full');
	}
	else  $path = (varset($_POST[$name])/* && deftrue('e_AJAX_REQUEST')*/) ? e107::getParser()->replaceConstants($_POST[$name], 'full') : '';
	$hide = '';
	
	if(!$path) 
	{
		$path = e_IMAGE_ABS."generic/blank.gif";
		$hide = ' style="display: none;"';
	}
	return "<a href='{$path}' rel='external' class='e-image-preview'{$hide}><img src='{$path}' alt=\"\" class='image-selector' style='{$width}{$height}' /></a>";
}