<?php
/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id: iconpicker.php,v 1.3 2009-07-16 08:15:35 e107coders Exp $
 *
 * Image picker shortcode
 *
*/

function iconpicker_shortcode($parm)
{
	require_once (e_HANDLER."file_class.php");
	require_once(e_HANDLER.'admin_handler.php');

	$e107 = &e107::getInstance();

	$fl = new e_file();
	$parms = array();

	parse_str($parm, $parms);

	if(!varset($parms['path']))
	{
		$parms['path'] = e_IMAGE."icons/";
		$parms['path_omit'] = e_IMAGE."icons/";
	}
	$parms['path'] = explode('|', $parms['path']);

	$iconlist = array();
	foreach($parms['path'] as $iconpath)
	{
		$tmp = $fl->get_files($iconpath, '\.jpg|\.gif|\.png|\.JPG|\.GIF|\.PNG');
		if($tmp)
		{
			$iconlist += $tmp;
		}
		unset($tmp);
	}
	$iconlist = multiarray_sort($iconlist, 'fname');

	$tmp = array(16, 32, 48, 64, 128);
	$tmp1 = array();
	$name = varset($parms['id']);

	global $iconpool,$tp;

	$iconlist = $iconpool;  // this overrides most of the code above - needs reviewing.

    foreach($iconlist as $folder)
	{

	  //	$filepath = varsettrue($parms['path_omit']) ? str_replace(explode('|', $parms['path_omit']), "", $icon['path'].$icon['fname']) : $e107->tp->createConstants($icon['path'], 1).$icon['fname'];
	 //	$filepath_abs = str_replace(array(e_IMAGE, e_FILE, e_PLUGIN), array(e_IMAGE_ABS, e_FILE_ABS, e_PLUGIN_ABS), $icon['path'].$icon['fname']);

     	foreach($folder as $icon)
		{
			$filepath = $icon;
	 		$filepath_abs = $tp->replaceConstants($icon);
			$icon_file = basename($filepath_abs);

			$str = "<a href='#{$filepath}' title='{$filepath}' onclick=\"e107Helper.insertText('{$filepath}','{$name}','{$name}-iconpicker'); return false; \"><img class='icon picker list%%size%%' src='{$filepath_abs}' alt='{$icon_file}' /></a>";

			foreach ($tmp as $isize)
			{
				if(strpos($icon_file, '_'.$isize.'.') !== false)
				{

					$tmp1[$isize] = varset($tmp1[$isize]).str_replace('%%size%%', ' S'.$isize, $str);
					continue 2;
				}
			}
		   	$tmp1['other'] = varset($tmp1['other']).$str;//other
		}
	}






    /*foreach($iconlist as $icon)
	{

		$filepath = varsettrue($parms['path_omit']) ? str_replace(explode('|', $parms['path_omit']), "", $icon['path'].$icon['fname']) : $e107->tp->createConstants($icon['path'], 1).$icon['fname'];
		$filepath_abs = str_replace(array(e_IMAGE, e_FILE, e_PLUGIN), array(e_IMAGE_ABS, e_FILE_ABS, e_PLUGIN_ABS), $icon['path'].$icon['fname']);
		$str = "<a href='#{$filepath}' title='{$filepath}' onclick=\"e107Helper.insertText('{$filepath}','{$name}','{$name}-iconpicker'); return false; \"><img class='icon picker list%%size%%' src='{$filepath_abs}' alt='{$filepath}' /></a>";

		foreach ($tmp as $isize)
		{

			if(strpos($icon['fname'], '_'.$isize.'.') !== false)
			{

				$tmp1[$isize] = varset($tmp1[$isize]).str_replace('%%size%%', ' S'.$isize, $str);
				continue 2;
			}
		}
		$tmp1['other'] = varset($tmp1['other']).$str;//other
	}*/

	return $tmp1 ? '<div id="'.$name.'-iconpicker-ajax"><div class="field-spacer iconpicker">'.str_replace('%%size%%', '', implode('</div><div class="field-spacer iconpicker">', $tmp1)).'</div></div>' : '';

}
?>