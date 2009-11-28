
// $Id: imagepreview.sc,v 1.4 2009-11-28 15:31:08 secretr Exp $
list($name, $width, $height) = explode("|",$parm, 3);

$name = varset($name);//avoid warnings
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
$path = (varset($_POST[$name]) && defsettrue('e_AJAX_REQUEST')) ? e107::getParser()->replaceConstants($_POST[$name], 'full') : '';
$hide = '';

if(!$path) 
{
	$path = e_IMAGE_ABS."generic/blank.gif";
	$hide = 'display: none;';
}
return "<a href='{$path}' rel='external' class='e-image-preview'><img src='{$path}' alt=\"\" class='image-selector' style='{$width}{$height}{$hide}' /></a>";