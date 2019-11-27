<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @DEPRECATED FILE
 */

if (!defined('e107_INIT')) { exit; }
/*
function r_emote()
{
	global $sysprefs, $pref, $tp;

	if (!is_object($tp->e_emote))
	{
		require_once(e_HANDLER.'emote_filter.php');
		$tp->e_emote = new e_emoteFilter;
	}
	
	$str = '';
	foreach($tp->e_emote->emotes as $key => $value)		// filename => text code
	{
		$key = str_replace("!", ".", $key);					// Usually '.' was replaced by '!' when saving
		$key = preg_replace("#_(\w{3})$#", ".\\1", $key);	// '_' followed by exactly 3 chars is file extension
		$key = e_IMAGE."emotes/" . $pref['emotepack'] . "/" .$key;		// Add in the file path

		$value2 = substr($value, 0, strpos($value, " "));
		$value = ($value2 ? $value2 : $value);
		$value = ($value == '&|') ? ':((' : $value;
		$value = " ".$value." ";
		//TODO CSS class
		$str .= "\n<a href=\"javascript:addtext('$value',true)\"><img src='$key' alt='' /></a> ";
	}

	return "<div class='spacer'>".$str."</div>";
}
*/
?>