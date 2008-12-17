
// $Id: imageselector.sc,v 1.8 2008-12-17 17:27:07 secretr Exp $
//FIXME - full rewrite, backward compatible
global $sql,$parm,$tp;

if(strstr($parm,"="))
{  // query style parms.
	parse_str($parm, $tmp);
	extract($tmp);
}
else
{        // comma separated parms.
	list($name,$path,$default,$width,$height,$multiple,$label,$subdirs,$filter,$fullpath,$click_target,$click_prefix,$click_postfix,$tabindex,$class) = explode(",",$parm);
}

$paths = explode("|",$path);

if(trim($default[0])=="{")
{
	$pvw_default = $tp->replaceConstants($default);
	$path = ""; // remove the default path if a constant is used.
}

$scaction = varsettrue($scaction, 'all');
$text = '';

//Get Select Box Only!
if($scaction == 'select' || $scaction == 'all')
{
	require_once(e_HANDLER."file_class.php");
	$fl = new e_file;


	$recurse = ($subdirs) ? $subdirs : 0;
	$imagelist = array();

	foreach($paths as $pths)
	{
		$imagelist += $fl->get_files($pths,'\.jpg|\.gif|\.png|\.JPG|\.GIF|\.PNG', 'standard', $recurse);
	}

	if($imagelist)
	{
		sort($imagelist);
	}

	if(!$fullpath && (count($paths) > 1))
	{
		$fullpath = TRUE;
	}

	$multi = ($multiple == "TRUE" || $multiple == "1") ? " multiple='multiple' style='height:{$height}'" : "";//style='float:left'
	$width = ($width) ? $width : "0";
	$height = ($height) ? $height : "0";
	$label = ($label) ? $label : " -- -- ";
	$tabindex = varset($tabindex) ? " tabindex='{$tabindex}'" : '';
	$class = varset($class) ? " class='{$class}'" : " class='tbox'";

	$text .= "<select{$multi}{$tabindex}{$class} name='{$name}' id='{$name}' onchange=\"replaceSC('imagepreview={$name}|{$width}|{$height}',this.form,'{$name}_prev'); \">
	<option value=''>".$label."</option>\n";
	foreach($imagelist as $icon)
	{
		$dir = str_replace($paths,"",$icon['path']);

		if(!$filter || ($filter && ereg($filter,$dir.$icon['fname'])))
		{
			$pth = ($fullpath) ? $tp->createConstants($icon['path'],1) : $dir;
			$selected = ($default == $pth.$icon['fname']) ? " selected='selected'" : "";
			$text .= "<option value='{$pth}{$icon['fname']}'{$selected}>{$dir}{$icon['fname']}</option>\n";
		}

	}
	$text .= "</select>";


	if($scaction == 'select') return $text;
}

if(!$pvw_default)
{
	$pvw_default = ($default) ? $path.$default : e_IMAGE_ABS."generic/blank.gif";
}

$text .= "<div class='imgselector-container' id='{$name}_prev'>";
if(varset($click_target))
{
   $pre		= varset($click_prefix);
   $post 	= varset($click_postfix);
   $text .= "<a href='#' onclick='addtext(\"{$pre}\"+document.getElementById(\"{$name}\").value+\"{$post}\", true);document.getElementById(\"{$name}\").selectedIndex = -1;return false;'>";
}
$text .= "<img src='{$pvw_default}' alt='' style='width:{$width};height:{$height}' />";
if(varset($click_target))
{
   $text .= "</a>";
}
$text .= "</div>\n";

return "\n\n<!-- Start Image Selector [{$scaction}] -->\n\n".$text."\n\n<!-- End Image Selector [{$scaction}] -->\n\n";