
// $Id: imagepreview.sc,v 1.3 2009-11-18 09:32:31 secretr Exp $
global $e107;

list($name, $width, $height) = explode("|",$parm, 3);

$name = varset($name);//avoid warnings
if(varset($width))
{
	$width = " width: {$width};";
}
if(varset($height))
{
	$height = " height: {$height};";
}

$path = (varset($_POST[$name]) && defsettrue('e_AJAX_REQUEST')) ? $e107->tp->replaceConstants($_POST[$name], 'full') : e_IMAGE_ABS."generic/blank.gif";
return "<a href='{$path}' rel='external' class='e-image-preview'><img src='{$path}' alt=\"\" class='image-selector' style='{$width}{$height}' /></a>";
