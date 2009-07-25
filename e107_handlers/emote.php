<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_handlers/emote.php,v $
|     $Revision: 1.5 $
|     $Date: 2009-07-25 07:54:34 $
|     $Author: marj_nl_fr $
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

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
		//TODO CSS class
		$str .= "\n<a href=\"javascript:addtext('$value',true)\"><img src='$key' alt='' /></a> ";
	}

	return "<div class='spacer'>".$str."</div>";
}

?>