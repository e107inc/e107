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
|     $Revision: 1.11 $
|     $Date: 2005-09-06 19:34:04 $
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

$current_lang = ($sql->mySQLlanguage != "") ? $sql->mySQLlanguage : $pref['sitelanguage'];

if (isset($_POST['metasubmit'])) {

	$tmp = $pref['meta_tag'];
	$langs = explode(",",e_LANLIST);
	foreach($langs as $lan){
		$meta_tag[$lan] = $tmp[$lan];
		$meta_diz[$lan] = $pref['meta_description'][$lan];
		$meta_keywords[$lan] = $pref['meta_keywords'][$lan];
		$meta_copyright[$lan] = $pref['meta_copyright'][$lan];
	}

	$meta_tag[$current_lang] = chop($_POST['meta']);
	$meta_diz[$current_lang] = chop($_POST['meta_description']);
	$meta_keywords[$current_lang] = chop($_POST['meta_keywords']);
	$meta_copyright[$current_lang] = chop($_POST['meta_copyright']);

	$pref['meta_tag'] = $meta_tag;
	$pref['meta_description'] = $meta_diz;
	$pref['meta_keywords'] = $meta_keywords;
	$pref['meta_copyright'] = $meta_copyright;
   /*
    if($pref['meta_tag'][$current_lang] == ""){
        unset($meta_tag[$current_lang]);
    }*/

	save_prefs();
	$message = METLAN_1;
}

if ($message) {
	$ns->tablerender(METLAN_4, "<div style='text-align:center'>".METLAN_1." (".$current_lang.").</div>");
}

$meta = $pref['meta_tag'];
$meta_diz = $pref['meta_description'];
$meta_keywords = $pref['meta_keywords'];
$meta_copyright = $pref['meta_copyright'];

$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."' id='dataform'>
	<table style='".ADMIN_WIDTH."' class='fborder'>

	<tr>
    <td style='width:25%' class='forumheader3'>".METLAN_9."</td>
    <td style='width:75%' class='forumheader3'>
	<input class='tbox style='width:90%' size='70' type='text' name='meta_description' value='".$meta_diz[$current_lang]."' />
	</td>
	</tr>

	<tr>
	<td style='width:25%' class='forumheader3'>".METLAN_10."</td>
    <td style='width:75%' class='forumheader3'>
	<input class='tbox style='width:90%' size='70' type='text' name='meta_keywords' value='".$meta_keywords[$current_lang]."' />
	</td>
	</tr>

	<tr>
	<td style='width:25%' class='forumheader3'>".METLAN_11."</td>
    <td style='width:75%' class='forumheader3'>
	<input class='tbox style='width:90%' size='70' type='text' name='meta_copyright' value='".$meta_copyright[$current_lang]."' />
	</td>
	</tr>

	<tr>
	<td style='width:25%' class='forumheader3'>".METLAN_2.": </td>
	<td style='width:75%' class='forumheader3'>
	<textarea class='tbox' id='meta' name='meta' cols='70'
	rows='10' style='width:90%' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>".str_replace("&#039;","'",$meta[$current_lang])."</textarea>
	<br />";
$text .= "</td>
</tr>

<tr><td colspan='2' style='text-align:center' class='forumheader'>

<input class='button' type='submit' name='metasubmit' value='".METLAN_3."' />
</td>
</tr>
</table>
</form>
</div>";



$ns -> tablerender(METLAN_8." (".$current_lang.")", $text);

require_once("footer.php");

?>