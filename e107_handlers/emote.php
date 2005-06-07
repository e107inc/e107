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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/emote.php,v $
|     $Revision: 1.11 $
|     $Date: 2005-06-07 19:50:59 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
function r_emote()
{
	global $sysprefs, $pref;
	if(!$pref['emotepack'])	$pref['emotepack'] = "default";
	$emotes = $sysprefs->getArray("emote_".$pref['emotepack']);
	foreach($emotes as $key => $value)
	{
		$key = str_replace("!", ".", $key);
		$key = preg_replace("#_(\w{3})$#", ".\\1", $key);
		$key = e_IMAGE."emotes/" . $pref['emotepack'] . "/" .$key;

		$value2 = substr($value, 0, strpos($value, " "));
		$value = ($value2 ? $value2 : $value);
		$str .= "\n<a href=\"javascript:addtext('$value',true)\"><img src='$key' style='border:0; padding-top:2px;' alt='' /></a> ";
	}

	return "<div class='spacer'>".$str."</div>";
}

?>