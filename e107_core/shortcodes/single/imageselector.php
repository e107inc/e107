<?php
// $Id: imageselector.sc 11438 2010-03-16 17:00:44Z secretr $
//FIXME - full rewrite, backward compatible

function imageselector_shortcode($parm = '', $mod = '')
{

	$sql = e107::getDb('imageselector.sc');
	$tp = e107::getParser();

	if(empty($parm))
	{
		return null;
	}

	if (strpos($parm, "=") !== false)
	{ // query style parms.
		parse_str($parm, $parms);
		extract($parms);
	}
	else
	{ // comma separated parms.
		list($name, $path, $default, $width, $height, $multiple, $label, $subdirs, $filter, $fullpath, $click_target, $click_prefix, $click_postfix, $tabindex, $class) = explode(",", $parm);
	}

	
	$paths = explode("|", $path);

	if (trim($default[0]) == "{")
	{
		$pvw_default = $tp->replaceConstants($default, 'abs');
		$path = ""; // remove the default path if a constant is used.
	}

	$scaction = vartrue($scaction, 'all');
	$text = '';
	$name_id = e107::getForm()->name2id($name);
	$imagelist = array();
	
	//Get Select Box Only!
	if ($scaction == 'select' || $scaction == 'all')
	{
		// Media manager support
		if(!empty($parms['media']))
		{
			$qry = "SELECT * FROM `#core_media` WHERE media_userclass IN (".USERCLASS_LIST.") ";
			$qry .= vartrue($parms['media']) && $parms['media'] !== 'all' ? " AND media_category='".$tp->toDB($parms['media'])."' " : " AND `media_category` NOT REGEXP '_icon_16|_icon_32|_icon_48|_icon_64' ";
			$qry .= " AND media_url REGEXP '\.jpg$|\.png$|\.gif$|\.jpeg$|\.svn$|\.JPG$|\.PNG$|\.GIF$|\.jpeg$|\.SVN$' ORDER BY media_name";
			// FIXME - media_type=image?
			if($sql->gen($qry))
			{
				while($row = $sql->fetch())
				{
					//$imagelist[$row['media_category']][$row['media_url']] = $row['media_name']. " (".$row['media_dimensions'].") ";
					$imagelist[$row['media_category']][] = array('path' => $row['media_url'], 'fname' => $row['media_name']. " (".$row['media_dimensions'].") ");
				}
	
				asort($imagelist);
			}
		}
		else
		{
			//require_once(e_HANDLER."file_class.php");
			$fl = e107::getFile(false);
	
			$recurse = ($subdirs) ? $subdirs : 0;
			foreach ($paths as $pths)
			{
				$imagelist[$tp->createConstants($pths, 'mix')] = $fl->get_files($pths, '\.jpg|\.gif|\.png|\.JPG|\.GIF|\.PNG|\.jpeg|\.JPEG|\.svn|\.SVN', 'standard', $recurse);
			}
	
			if (!$fullpath && (count($paths) > 1))
			{
				$fullpath = TRUE;
			}
		}

		$multi = ($multiple == "TRUE" || $multiple == "1") ? " multiple='multiple' style='height:{$height}'" : ""; //style='float:left'
		$width = (vartrue($width)) ? $width : "0";
		$height = (vartrue($height)) ? $height : "0";
		$label = ($label) ? $label : " -- -- ";
		$tabindex = varset($tabindex) ? " tabindex='{$tabindex}'" : '';
		$class = varset($class) ? " class='{$class}'" : " class='tbox imgselector'";
		
		if(!e_AJAX_REQUEST)
		{
			$text .= '<div id="'.$name_id.'_cont">';
		}

		$text .= "\n<select{$multi}{$tabindex}{$class} name='{$name}' id='{$name_id}' onchange=\"replaceSC('imagepreview={$name}|{$width}|{$height}',this.form,'{$name_id}_prev'); \">
		<option value=''>".$label."</option>\n";


		foreach ($imagelist as $imagedirlabel => $icons)
		{
			if(!vartrue($parms['media'])) $imagedirlabel = str_replace('../', '', $tp->replaceConstants($imagedirlabel));
			$text .= "<optgroup label='".$imagedirlabel."'>\n";
			if (empty($icons)) $text .= "<option value=''>Empty</option>\n";
			else
			{
				$icons = multiarray_sort($icons, 'fname');
				
				foreach ($icons as $icon)
				{
					$dir = str_replace($paths, "", $icon['path']); 
					// echo "dir=".$icon['path'];

					if (!$filter || ($filter && preg_match('~'.$filter.'~', $dir.$icon['fname'])))
					{
						$pth = $dir;
						
					//	if($fullpath) // returns nothing if fullpath is FALSE;
						{
							if(!vartrue($parms['media']))
							{
								$pth = $tp->createConstants($icon['path'], 'rel');
								$_value = $pth.$icon['fname'];
								$_label = $dir.$icon['fname'];
								$selected = ($default == $_value || $pth.$default == $_value) ? " selected='selected'" : "";
								
							}
							else
							{
								// convert e.g. {e_MEDIA}images/ to {e_MEDIA_IMAGES}
								$pth = $tp->createConstants($tp->replaceConstants($icon['path']), 'rel');
								$_value = $pth;
								$_label = $icon['fname'];
								$selected = ($default == $_value) ? " selected='selected'" : "";
							}
						}
						
						$text .= "<option value='{$_value}'{$selected}>&nbsp;&nbsp;&nbsp;{$_label}</option>\n";
					}
				}
			}
			$text .= "</optgroup>\n";
		}
		$text .= "</select>";
		$text .= "<a href='#'  onclick=\"replaceSC('imageselector=".rawurlencode($parm)."&amp;saction=select',\$('{$name_id}').up('form'),'{$name_id}_cont'); return false;\">refresh</a>";
		if(!e_AJAX_REQUEST) $text .= '</div>';

		if ($scaction == 'select') return $text;
	}

	$hide = '';
	if (!$pvw_default)
	{
		if ($default)
		{
			$test = pathinfo($default);
			if ('.' == $test['dirname'])
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
	if (varset($click_target))
	{
		$pre = varset($click_prefix);
		$post = varset($click_postfix);
		$text .= "<a href='#'{$hide} title='Select' onclick='addtext(\"{$pre}\"+document.getElementById(\"{$name_id}\").value+\"{$post}\", true);document.getElementById(\"{$name_id}\").selectedIndex = -1;return false;'>";
	}
	else
	{
		$text .= "<a href='{$pvw_default}'{$hide} rel='external shadowbox' title='Preview {$pvw_default}' class='e-image-preview'>";
	}
	if (vartrue($height)) $height = intval($height);
	if (vartrue($width)) $width = intval($width);
	$thpath = isset($parms['nothumb']) || $hide ? $pvw_default : $tp->thumbUrl($pvw_default, 'w='.$width.'&h='.$height, true);
	$text .= "<img src='{$thpath}' alt='$pvw_default' class='image-selector' /></a>";

	$text .= "</div>\n";

	return "\n\n<!-- Start Image Selector [{$scaction}] -->\n\n".$text."\n\n<!-- End Image Selector [{$scaction}] -->\n\n";
}