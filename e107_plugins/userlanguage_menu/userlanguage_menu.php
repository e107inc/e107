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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/userlanguage_menu/userlanguage_menu.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:35:43 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

require_once(e_HANDLER."file_class.php");
	$fl = new e_file;
	$lanlist = $fl->get_dirs(e_LANGUAGEDIR);
	sort($lanlist);

	$action = (e_QUERY && !$_GET['elan']) ? e_SELF."?".e_QUERY : e_SELF;
	$text = "<form method='post' action='".$action."'>
		<div style='text-align:center'>
		<select name='sitelanguage' class='tbox' >";
	foreach($lanlist as $langval)
	{
		$selected ="";
		if($langval == USERLAN || ($langval == $pref['sitelanguage'] && USERLAN == ""))
		{
			$selected = "selected='selected'";
		}
		$text .= "<option value='".$langval."' $selected>".$langval."</option>\n ";
	}

	$text .= "</select>";
	$text .= "<br /><br /><input class='button' type='submit' name='setlanguage' value='".UTHEME_MENU_L1."' />";
	$text .= "</div></form>	";

$ns->tablerender(UTHEME_MENU_L2, $text, 'user_lan');

?>