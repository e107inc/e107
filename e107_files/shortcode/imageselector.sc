
// $Id: imageselector.sc,v 1.11 2009-11-28 15:31:08 secretr Exp $
//FIXME - full rewrite, backward compatible
global $sql,$parm,$tp;

if(strstr($parm,"="))
{  // query style parms.
	parse_str($parm, $parms);
	extract($parms);
}
else
{        // comma separated parms.
	list($name,$path,$default,$width,$height,$multiple,$label,$subdirs,$filter,$fullpath,$click_target,$click_prefix,$click_postfix,$tabindex,$class) = explode(",",$parm);
}

$paths = explode("|",$path);

if(trim($default[0])=="{")
{
	$pvw_default = $tp->replaceConstants($default, 'abs');
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
		$imagelist[$tp->createConstants($pths, 1)]= $fl->get_files($pths,'\.jpg|\.gif|\.png|\.JPG|\.GIF|\.PNG', 'standard', $recurse);
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
	$class = varset($class) ? " class='{$class}'" : " class='tbox imgselector'";

	$text .= "<select{$multi}{$tabindex}{$class} name='{$name}' id='{$name}' onchange=\"replaceSC('imagepreview={$name}|{$width}|{$height}',this.form,'{$name}_prev'); \">
	<option value=''>".$label."</option>\n";

	require_once(e_HANDLER.'admin_handler.php');
	foreach($imagelist as $imagedirlabel => $icons)
	{

		$text .= "<optgroup label='".$tp->replaceConstants($imagedirlabel, TRUE)."'>";
		if(empty($icons))  $text .= "<option value=''>Empty</option>\n";
		else
		{
			$icons = multiarray_sort($icons, 'fname');

			foreach($icons as $icon)
			{
				$dir = str_replace($paths,"",$icon['path']);

				if(!$filter || ($filter && ereg($filter,$dir.$icon['fname'])))
				{

					$pth = ($fullpath) ? $tp->createConstants($icon['path'],1) : $dir;
					$selected = ($default == $pth.$icon['fname'] || $pth.$default == $pth.$icon['fname']) ? " selected='selected'" : "";
					$text .= "<option value='{$pth}{$icon['fname']}'{$selected}>&nbsp;&nbsp;&nbsp;{$dir}{$icon['fname']}</option>\n";
				}
			}
		}
		$text .= '</optgroup>';
	}
	$text .= "</select>";


	if($scaction == 'select') return $text;
}

$hide = '';
if(!$pvw_default)
{
	$pvw_default = ($default) ? $path.$default : e_IMAGE_ABS."generic/blank.gif";
	$hide = 'display: none;';
}


$text .= "<div class='imgselector-container' id='{$name}_prev'>";
if(varset($click_target))
{
   $pre		= varset($click_prefix);
   $post 	= varset($click_postfix);
   $text .= "<a href='#' onclick='addtext(\"{$pre}\"+document.getElementById(\"{$name}\").value+\"{$post}\", true);document.getElementById(\"{$name}\").selectedIndex = -1;return false;'>";
}
else
{
	$text .= "<a href='{$pvw_default}' rel='external' class='e-image-preview'>";
}
if(vartrue($height)) $height = "height:{$height};";
if(vartrue($width)) $width = "width:{$width}; ";
$text .= "<img src='{$pvw_default}' alt='' style='{$width}{$height}{$hide}' /></a>";

$text .= "</div>\n";

return "\n\n<!-- Start Image Selector [{$scaction}] -->\n\n".$text."\n\n<!-- End Image Selector [{$scaction}] -->\n\n";