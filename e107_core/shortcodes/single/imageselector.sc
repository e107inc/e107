
// $Id$
//FIXME - full rewrite, backward compatible

$sql = e107::getDb();
$tp = e107::getParser();

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

if(trim($default{0})=="{")
{
	$pvw_default = $tp->replaceConstants($default, 'abs');
	$path = ""; // remove the default path if a constant is used.
}

$scaction = varsettrue($scaction, 'all');
$text = '';
$name_id = e107::getForm()->name2id($name);

//Get Select Box Only!
if($scaction === 'select' || $scaction === 'all')
{
	require_once(e_HANDLER."file_class.php");
	$fl = new e_file;


	$recurse = ($subdirs) ? $subdirs : 0;
	$imagelist = array();

	foreach($paths as $pths)
	{
		$imagelist[$tp->createConstants($pths, 'mix')]= $fl->get_files($pths,'\.jpg|\.gif|\.png|\.JPG|\.GIF|\.PNG|\.jpeg|\.JPEG|\.svg|\.SVG', 'standard', $recurse);
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

	$text .= "<select{$multi}{$tabindex}{$class} name='{$name}' id='{$name_id}' onchange=\"replaceSC('imagepreview={$name}|{$width}|{$height}',this.form,'{$name_id}_prev'); \">
	<option value=''>".$label."</option>\n";

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

				if (!$filter || ($filter && preg_match('~'.$filter.'~', $dir.$icon['fname'])))
				{

					$pth = ($fullpath) ? $tp->createConstants($icon['path'],'rel') : $dir;
					$selected = ($default == $pth.$icon['fname'] || $pth.$default == $pth.$icon['fname']) ? " selected='selected'" : "";
					$text .= "<option value='{$pth}{$icon['fname']}'{$selected}>&nbsp;&nbsp;&nbsp;{$dir}{$icon['fname']}</option>\n";
				}
			}
		}
		$text .= '</optgroup>';
	}
	$text .= "</select>";


	if($scaction === 'select') return $text;
}

$hide = '';
if(!$pvw_default)
{
	if($default)
	{
		$test = pathinfo($default);
		if('.' == $test['dirname'])
		{
			// file only, add absolute path
			$path = $tp->createConstants($path, 1);
			$path = $tp->replaceConstants($path, 'abs');
			$pvw_default = $path.$default;
		}
		else
		{
			// path, convert to absolute path
			$pvw_default = $tp->createConstants($default, 1);
			$pvw_default = $tp->replaceConstants($pvw_default, 'abs');
		}
	}
	else
	{
		$pvw_default = e_IMAGE_ABS."generic/blank.gif";
		$hide = ' style="display: none;"';
	}
}

$text .= "<div class='imgselector-container' id='{$name_id}_prev'>";
if(varset($click_target))
{
   $pre		= varset($click_prefix);
   $post 	= varset($click_postfix);
   $text .= "<a href='#'{$hide} title='Select' onclick='addtext(\"{$pre}\"+document.getElementById(\"{$name_id}\").value+\"{$post}\", true);document.getElementById(\"{$name_id}\").selectedIndex = -1;return false;'>";
}
else
{
	$text .= "<a href='{$pvw_default}'{$hide} rel='external' title='Preview {$pvw_default}' class='e-image-preview'>";
}
if(vartrue($height)) $height = intval($height);
if(vartrue($width)) $width = intval($width);
$thpath = isset($parms['nothumb']) || $hide ? $pvw_default : $tp->thumbUrl($pvw_default, 'w='.$width.'&h='.$height, true);
$text .= "<img src='{$thpath}' alt='$pvw_default' class='image-selector' /></a>";

$text .= "</div>\n";

return "\n\n<!-- Start Image Selector [{$scaction}] -->\n\n".$text."\n\n<!-- End Image Selector [{$scaction}] -->\n\n";