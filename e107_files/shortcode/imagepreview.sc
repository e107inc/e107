
// $Id: imagepreview.sc,v 1.2 2008-12-17 17:27:07 secretr Exp $
global $e107;

list($name, $width, $height) = explode("|",$parm, 3);

$name = varset($name);//avoid warnings
if(varset($width))
{
	$width = " width: {$width};";
}
if(varset($height))
{
	$height = " width: {$height};";
}

$path = (varset($_POST[$name]) && defsettrue('e_AJAX_REQUEST')) ? $e107->tp->replaceConstants($_POST[$name], 'full') : e_IMAGE_ABS."generic/blank.gif";

return "<img src='{$path}' alt=\"\" class='image-selector' style='{$width}{$height}' />";
