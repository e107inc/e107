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
|     $Source: /cvs_backup/e107_0.7/e107_admin/ugflag.php,v $
|     $Revision: 1.6 $
|     $Date: 2005-02-05 07:04:11 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("9")) {
	header("location:".e_BASE."index.php");
	 exit;
}
$e_sub_cat = 'maintain';
require_once(e_HANDLER."ren_help.php");

if (isset($_POST['updatesettings'])) {
	$aj = new textparse;
	$pref['maintainance_flag'] = $_POST['maintainance_flag'];
	$pref['maintainance_text'] = $aj->formtpa($_POST['maintainance_text']);
	save_prefs();
	header("location:".e_SELF."?u");
	exit;
}

require_once("auth.php");

if (e_QUERY == "u") {
	$ns->tablerender("", "<div style='text-align:center'><b>".UGFLAN_1.".</b></div>");
}

$maintainance_flag = $pref['maintainance_flag'];

$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."' id='dataform'>
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>
	<td style='width:30%' class='forumheader3'>".UGFLAN_2.": </td>
	<td style='width:70%' class='forumheader3'>";


if ($maintainance_flag == 1) {
	$text .= "<input type='checkbox' name='maintainance_flag' value='1'  checked='checked' />";
} else {
	$text .= "<input type='checkbox' name='maintainance_flag' value='1' />";
}

$text .= "</td>
	</tr>

	<tr>
	<td style='width:30%' class='forumheader3'>".UGFLAN_5."<br /><span class='smalltext'>".UGFLAN_6."</span></td>
	<td style='width:70%' class='forumheader3'>
	<textarea id='maintainance_text' class='tbox' name='maintainance_text' cols='59' rows='10' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>".$pref['maintainance_text']."</textarea>
	</td>
	</tr>

	<tr style='vertical-align:top'>
	<td colspan='2'  style='text-align:center' class='forumheader3'>
	".display_help("",2)."
	</td>
	</tr>


	<tr style='vertical-align:top'>
	<td colspan='2'  style='text-align:center' class='forumheader'>
	<input class='button' type='submit' name='updatesettings' value='".UGFLAN_3."' />
	</td>
	</tr>
	</table>
	</form>
	</div>";

$ns->tablerender(UGFLAN_4, $text);
require_once("footer.php");

?>