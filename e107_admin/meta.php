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
|     $Source: /cvs_backup/e107_0.7/e107_admin/meta.php,v $
|     $Revision: 1.8 $
|     $Date: 2005-02-08 08:21:06 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("C")) {
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'meta';
require_once("auth.php");

if (isset($_POST['metasubmit'])) {

	$pref['meta_tag'] = $tp->toForm($_POST['meta']);
	save_prefs();
	$message = METLAN_1;
}

if ($message) {
	$ns->tablerender(METLAN_4, "<div
		style='text-align:center'>".METLAN_1.".</div>");
}

$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."' id='dataform'>
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>

	<td style='width:20%' class='forumheader3'>".METLAN_2.": </td>
	<td style='width:80%' class='forumheader3'>
	<textarea class='tbox' id='meta' name='meta' cols='70'
	rows='10' style='width:90%' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>".$tp->toForm($pref['meta_tag'],TRUE)."</textarea>
	<br />";
$text .= "
	<input class='button' type='button' value='description'
	onclick=\"addtext('<meta name=\'description\' content=\'".METLAN_5."\' />\\n')\" />

	<input class='button' type='button' value='keywords'
	onclick=\"addtext('<meta name=\'keywords\' content=\'".METLAN_6."\' />\\n')\" />

	<input class='button' type='button' value='copyright'
	onclick=\"addtext('<meta name=\'copyright\' content=\'".METLAN_7."\' />\\n')\" />
</td>
</tr>

<tr><td colspan='2' style='text-align:center' class='forumheader'>

<input class='button' type='submit' name='metasubmit'

value='".METLAN_3."' />
</td>
</tr>
</table>
</form>
</div>";



$ns -> tablerender(METLAN_8, $text);

require_once("footer.php");

?>