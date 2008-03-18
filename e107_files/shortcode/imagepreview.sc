// $Id: imagepreview.sc,v 1.1 2008-03-18 00:39:02 e107coders Exp $
global $tp;
list($name,$width,$height,$img_path) = explode("|",$parm);

if(!$width)
{
	$width = "32px";
}
if(!$height)
{
	$height = "32px";
}

$path = ($_POST[$name] && $_POST['ajax_used']) ? $img_path.$tp->replaceConstants($_POST[$name]) : e_IMAGE_ABS."generic/blank.gif";
return "<img src='".$path."' alt=\"\" style='border: 0px; width: {$width}; height: {$height}' />";
