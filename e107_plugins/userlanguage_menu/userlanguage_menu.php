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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/userlanguage_menu/userlanguage_menu.php,v $
|     $Revision: 1.7 $
|     $Date: 2005-02-10 00:30:03 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once(e_HANDLER."file_class.php");
	$fl = new e_file;
	$reject = array('.','..','/','CVS','thumbs.db','*._$');
	$lanlist = $fl->get_files(e_LANGUAGEDIR,"",$reject);
	sort($lanlist);

	$text = "<form method='post' action='".e_SELF."'>
		<div style='text-align:center'>
		<select name='sitelanguage' class='tbox' >";

	foreach($lanlist as $langval) {
		$langname = $langval;
		$langval = ($langval['dir'] == $pref['sitelanguage']) ? "" : $langval['dir'];
		$selected = ($langval == USERLAN) ? "selected='selected'" : "";
		$text .= "<option value='".$langval."' $selected>".$langname['dir']."</option>\n ";
	}

	$text .= "</select>";
	$text .= "<br /><br /><input class='button' type='submit' name='setlanguage' value='".UTHEME_MENU_L1."' />";
	$text .= "</div></form>	";

$ns->tablerender(UTHEME_MENU_L2, $text, 'user_lan');

?>