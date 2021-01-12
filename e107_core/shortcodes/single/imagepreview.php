<?php
// $Id$

function imagepreview_shortcode($parm)
{
	if(empty($parm))
	{
		return null;
	}

	list($name, $width, $height, $nothumb) = explode("|",$parm, 4);

	$name = rawurldecode(varset($name));//avoid warnings
	if(varset($width))
	{
		$width = intval($width);
	}
	else $width = 0;
	if(varset($height))
	{
		$height = intval($height);
	}
	else $height = 0;

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
		$path = '#';
		$thpath = e_IMAGE_ABS."generic/blank.gif";
		$hide = ' style="display: none;"';
	}
	else
	{
		$thpath = !varset($nothumb) ? e107::getParser()->thumbUrl($path, 'w='.$width.'h='.$height, true) : $path;
	}
	return "<a href='{$path}' rel='external shadowbox' class='e-image-preview'{$hide}><img src='{$thpath}' alt=\"\" class='image-selector' /></a>";
}