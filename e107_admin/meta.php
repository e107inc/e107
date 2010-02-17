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
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("T")) {
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'meta';
require_once("auth.php");

//$current_lang = ($sql->mySQLlanguage != "") ? $sql->mySQLlanguage : $pref['sitelanguage'];
$current_lang = e_LANGUAGE;

if (isset($_POST['metasubmit'])) {

	$tmp = $pref['meta_tag'];
	$langs = explode(",",e_LANLIST);
	foreach($langs as $lan){
		$meta_tag[$lan] = $tmp[$lan];
		$meta_diz[$lan] = $pref['meta_description'][$lan];
		$meta_keywords[$lan] = $pref['meta_keywords'][$lan];
		$meta_copyright[$lan] = $pref['meta_copyright'][$lan];
		$meta_author[$lan] = $pref['meta_author'][$lan];
	}

	$meta_tag[e_LANGUAGE] = strip_if_magic(chop($_POST['meta']));
	$meta_diz[e_LANGUAGE] = strip_if_magic(chop($_POST['meta_description']));
	$meta_keywords[e_LANGUAGE] = strip_if_magic(chop($_POST['meta_keywords']));
	$meta_copyright[e_LANGUAGE] = strip_if_magic(chop($_POST['meta_copyright']));
	$meta_author[e_LANGUAGE] = strip_if_magic(chop($_POST['meta_author']));

    $pref['meta_news_summary'] = $_POST['meta_news_summary'];
	$pref['meta_tag'] = $meta_tag;
	$pref['meta_description'] = $meta_diz;
	$pref['meta_keywords'] = $meta_keywords;
	$pref['meta_copyright'] = $meta_copyright;
	$pref['meta_author'] = $meta_author;

   /*
    if($pref['meta_tag'][e_LANGUAGE] == ""){
        unset($meta_tag[e_LANGUAGE]);
    }*/

	save_prefs();
	$message = METLAN_1;
}

if ($message) {
	$ns->tablerender(METLAN_4, "<div style='text-align:center'>".METLAN_1." (".e_LANGUAGE.").</div>");
}

$meta = $pref['meta_tag'];
$meta_diz = $pref['meta_description'];
$meta_keywords = $pref['meta_keywords'];
$meta_copyright = $pref['meta_copyright'];
$meta_author = $pref['meta_author'];

$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."' id='dataform'>
	<table style='".ADMIN_WIDTH."' class='fborder'>

	<tr>
    <td style='width:25%' class='forumheader3'>".METLAN_9."</td>
    <td style='width:75%' class='forumheader3'>
	<textarea class='tbox' title='meta_description' id='meta_description' name='meta_description' cols='70' rows='4' style='width:90%'>".$tp->toForm($meta_diz[e_LANGUAGE])."</textarea>
	</td>
	</tr>

	<tr>
	<td style='width:25%' class='forumheader3'>".METLAN_10."</td>
    <td style='width:75%' class='forumheader3'>
	<textarea class='tbox' title='meta_keywords' id='meta_keywords' name='meta_keywords' cols='70' rows='4' style='width:90%'>".$tp->toForm($meta_keywords[e_LANGUAGE])."</textarea>
	</td>
	</tr>

	<tr>
	<td style='width:25%' class='forumheader3'>".METLAN_11."</td>
    <td style='width:75%' class='forumheader3'>
	<input class='tbox' style='width:90%' size='70' type='text' name='meta_copyright' value='".$meta_copyright[e_LANGUAGE]."' />
	</td>
	</tr>

	<tr>
	<td style='width:25%' class='forumheader3'>".METLAN_13."</td>
    <td style='width:75%' class='forumheader3'>
	<input class='tbox' style='width:90%' size='70' type='text' name='meta_author' value=\"".$meta_author[e_LANGUAGE]."\" />
	</td>
	</tr>

	<tr>
	<td style='width:25%' class='forumheader3'>".METLAN_2.":
	<span class='smalltext'><br /><br />eg.
	&lt;meta name='author' content='your name' /&gt; </span>
	</td>
	<td style='width:75%' class='forumheader3'>
	<textarea class='tbox' title=\"eg. <meta name='author' content='your name' />\" id='meta' name='meta' cols='70'
	rows='10' style='width:90%' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>".str_replace("<","&lt;",$tp->toForm($meta[e_LANGUAGE]))."</textarea>
	<br />";
$text .= "</td>
</tr>

	<tr>
	<td style='width:25%' class='forumheader3'>".METLAN_12."</td>
    <td class='forumheader3' style='text-align:left;width:75%' >";
    $checked = ($pref['meta_news_summary']) ? "checked='checked'" : "";
	$text .= "
	<input type='checkbox' name='meta_news_summary' value='1' {$checked} />
	</td>
	</tr>

<tr><td colspan='2' style='text-align:center' class='forumheader'>

<input class='button' type='submit' name='metasubmit' value='".METLAN_3."' />
</td>
</tr>
</table>
</form>
</div>";



$ns -> tablerender(METLAN_8." (".e_LANGUAGE.")", $text);

require_once("footer.php");

?>