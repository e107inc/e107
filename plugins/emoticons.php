<?
/*
+---------------------------------------------------------------+
|	e107 website system
|	/plugins/emoticons.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

function emoticons($str){
	$sql = new db;
	$sql -> db_Select("core", "*", "e107_name='emote'");
	$row = $sql -> db_Fetch(); extract($row);
	$emote = unserialize($e107_value);
	$c=0;
	while(list($code, $name) = @each($emote[$c])){
//		$code = " ".$code;
		$str = str_replace(" ".$code, "<img src=\"".e_BASE."themes/shared/emoticons/$name\" alt=\"\" style=\"vertical-align:absmiddle\" />", $str);
		$str = str_replace($code." ", "<img src=\"".e_BASE."themes/shared/emoticons/$name\" alt=\"\" style=\"vertical-align:absmiddle\" />", $str);
		$c++;
	}
	return $str;
}
?>