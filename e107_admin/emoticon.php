<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /admin/emoticon_conf.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("F")) {
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'emoticon';

require_once(e_HANDLER."file_class.php");
$fl = new e_file;




if (isset($_POST['updatesettings'])) {
	while (list($id, $name) = each($_POST['emote_code'])) {
		$emote[] = array($tp->toDB($name) => $_POST['emote_text'][$id]);
	}
	$sysprefs->setArray('emote');
	if ($_POST['smiley_activate'] != $pref['smiley_activate']) {
		$e107cache->clear();
	}
	$pref['smiley_activate'] = $_POST['smiley_activate'];
	save_prefs();
	header("location:".e_ADMIN."emoticon.php?u");
	exit;
}

$emote = $sysprefs->getArray('emote');



if (!is_array($emote)) {
	$tmp = setdefault();
	$sysprefs->set($tmp, 'emote');
	$emote = $sysprefs->getArray('emote');
}

if (isset($_POST['addemote'])) {
	if ($_POST['emote_new_code'] && $_POST['emote_new_image']) {
		$emote[count($emote)] = array($tp->toDB($_POST['emote_new_code']) => $_POST['emote_new_image']);
		$sysprefs->setArray('emote');
		header("location:".e_ADMIN."emoticon.php?v");
		exit;
	}
}

$tmp = explode(".", e_QUERY);

if ($tmp[0] == "del") {
	if (!e_REFERER_SELF) {
		exit;
	}
	unset($emote[$tmp[1]]);
	$newemote = array_values($emote);
	$emote = $newemote;
	$tmp = addslashes(serialize($emote));
	$sql->db_Update("core", "e107_value='$tmp' WHERE e107_name='emote' ");
	header("location:".e_ADMIN."emoticon.php?w");
	exit;
}

require_once("auth.php");

if (e_QUERY == "u") {
	$ns->tablerender("", "<div style='text-align:center'><b>".EMOLAN_1."</b></div>");
}

if (e_QUERY == "v") {
	$ns->tablerender("", "<div style='text-align:center'><b>".EMOLAN_2."</b></div>");
}

if (e_QUERY == "w") {
	$ns->tablerender("", "<div style='text-align:center'><b>".EMOLAN_3."</b></div>");
}

$smiley_activate = $pref['smiley_activate'];

$text = "<div style='text-align:center'><div style='padding : 1px; ".ADMIN_WIDTH."; margin-left: auto; margin-right: auto;'>
	<form method='post' action='".e_SELF."'>
	<table class='fborder' style='width:99%'>
	<tr>
	<td style='width:30%' class='forumheader3'>".EMOLAN_4.": </td>
	<td colspan='4' class='forumheader3'>";
$text .= ($pref['smiley_activate'] ? "<input type='checkbox' name='smiley_activate' value='1'  checked='checked' />" : "<input type='checkbox' name='smiley_activate' value='1' />");
$text .= "
	</td>
	</tr>
	<tr>
	<td class='forumheader3' style='vertical-align:top'>".EMOLAN_5."</td>
	<td style='width:100%'>

	<div style='padding:1px; width:100%; height:300px; overflow:auto; margin-left:auto; margin-right:auto'>
	<table class='fborder' style='width:100%'>";

	foreach ($emote as $c => $value) {
	while (list($short, $name) = @each($emote[$c])) {
		$text .= "
			<tr>
			<td style='width:20%; text-align:center' class='forumheader3'><input class='tbox' type='text' name='emote_code[]' size='15' value='".$tp->toForm($short)."' maxlength='20' /></td>
			<td style='width:20%; text-align:center' class='forumheader3'><input class='tbox' type='text' name='emote_text[]' size='15' value='".$tp->toForm($name)."' maxlength='20' /></td>
			<td style='width:10%; text-align:center' class='forumheader3'><img src='".e_IMAGE."emoticons/$name' alt='' style='vertical-align:absmiddle' /></td>
			<td style='width:30%' class='forumheader3'>[ <a href='".e_SELF."?del.$c'>".EMOLAN_6."</a> ]</td>
			</tr>
		";
		$c++;
		$names[] = $name;
	}
}

$emotelist = $fl->get_files(e_IMAGE."emoticons/","",$names);

$text .= "

</table>
</div>
</td></tr>
<tr>
	<td colspan='5' style='text-align:center' class='forumheader'>
	<input class='button' type='submit' name='updatesettings' value='".EMOLAN_7."' />
	</td>
	</tr>
	</table>
	</form>
	</div>

	<div style='margin-left:auto;margin-right:auto;text-align:center; ".ADMIN_WIDTH."'>
	<form method='post' action='".e_SELF."'>
	<table class='fborder' style='width:99%'>
	<tr>
	<td style='width:30%' class='forumheader3'>".EMOLAN_8.": </td>
	<td class='forumheader3'><input class='tbox' type='text' name='emote_new_code' size='15' value='$name' maxlength='20' /></td>
	</tr>

	<tr>
	<td style='width:30%' class='forumheader3'>".EMOLAN_9.": </td>
	<td class='forumheader3'><input class='tbox' type='text' id='emote_new_image' name='emote_new_image' size='30' value='$name' maxlength='100' />
	<input class='button' type ='button' style='cursor:hand' size='30' value='".LCLAN_39."' onclick='expandit(this)' />
	<div id='emoicn' style='padding:3px;display:none'>";
	foreach ($emotelist as $key => $icon){
		$text .= "<a href=\"javascript:insertext('".$icon['fname']."','emote_new_image','emoicn')\"><img src='".e_IMAGE."emoticons/".$icon['fname']."' style='border:0' alt='' /></a> ";
	}
	$text .= "</div>
	</td>
	</tr>

	<tr>
	<td colspan='2' style='text-align:center' class='forumheader'>
	<input class='button' type='submit' name='addemote' value='".EMOLAN_10."' />


	</td>
	</tr>
	</table>
	</form>
	</div></div>";

$ns->tablerender(EMOLAN_11, $text);
require_once("footer.php");

function setdefault() {
	$sql = new db;
	$tmp = 'a:60:{i:0;a:1:{s:2:"&|";s:7:"cry.png";}i:1;a:1:{s:3:"&-|";s:7:"cry.png";}i:2;a:1:{s:3:"&o|";s:7:"cry.png";}i:3;a:1:{s:3:":((";s:7:"cry.png";}i:4;a:1:{s:3:"~:(";s:7:"mad.png";}i:5;a:1:{s:4:"~:o(";s:7:"mad.png";}i:6;a:1:{s:4:"~:-(";s:7:"mad.png";}i:7;a:1:{s:2:":)";s:9:"smile.png";}i:8;a:1:{s:3:":o)";s:9:"smile.png";}i:9;a:1:{s:3:":-)";s:9:"smile.png";}i:10;a:1:{s:2:":(";s:9:"frown.png";}i:11;a:1:{s:3:":o(";s:9:"frown.png";}i:12;a:1:{s:3:":-(";s:9:"frown.png";}i:13;a:1:{s:2:":D";s:8:"grin.png";}i:14;a:1:{s:3:":oD";s:8:"grin.png";}i:15;a:1:{s:3:":-D";s:8:"grin.png";}i:16;a:1:{s:2:":?";s:12:"confused.png";}i:17;a:1:{s:3:":o?";s:12:"confused.png";}i:18;a:1:{s:3:":-?";s:12:"confused.png";}i:19;a:1:{s:3:"%-6";s:11:"special.png";}i:20;a:1:{s:2:"x)";s:8:"dead.png";}i:21;a:1:{s:3:"xo)";s:8:"dead.png";}i:22;a:1:{s:3:"x-)";s:8:"dead.png";}i:23;a:1:{s:2:"x(";s:8:"dead.png";}i:24;a:1:{s:3:"xo(";s:8:"dead.png";}i:25;a:1:{s:3:"x-(";s:8:"dead.png";}i:26;a:1:{s:2:":@";s:7:"gah.png";}i:27;a:1:{s:3:":o@";s:7:"gah.png";}i:28;a:1:{s:3:":-@";s:7:"gah.png";}i:29;a:1:{s:2:":!";s:8:"idea.png";}i:30;a:1:{s:3:":o!";s:8:"idea.png";}i:31;a:1:{s:3:":-!";s:8:"idea.png";}i:32;a:1:{s:2:":|";s:11:"neutral.png";}i:33;a:1:{s:3:":o|";s:11:"neutral.png";}i:34;a:1:{s:3:":-|";s:11:"neutral.png";}i:35;a:1:{s:2:"?!";s:12:"question.png";}i:36;a:1:{s:2:"B)";s:12:"rolleyes.png";}i:37;a:1:{s:3:"Bo)";s:12:"rolleyes.png";}i:38;a:1:{s:3:"B-)";s:12:"rolleyes.png";}i:39;a:1:{s:2:"8)";s:10:"shades.png";}i:40;a:1:{s:3:"8o)";s:10:"shades.png";}i:41;a:1:{s:3:"8-)";s:10:"shades.png";}i:42;a:1:{s:2:":O";s:12:"suprised.png";}i:43;a:1:{s:3:":oO";s:12:"suprised.png";}i:44;a:1:{s:3:":-O";s:12:"suprised.png";}i:45;a:1:{s:2:":p";s:10:"tongue.png";}i:46;a:1:{s:3:":op";s:10:"tongue.png";}i:47;a:1:{s:3:":-p";s:10:"tongue.png";}i:48;a:1:{s:2:":P";s:10:"tongue.png";}i:49;a:1:{s:3:":oP";s:10:"tongue.png";}i:50;a:1:{s:3:":-P";s:10:"tongue.png";}i:51;a:1:{s:2:";)";s:8:"wink.png";}i:52;a:1:{s:3:";o)";s:8:"wink.png";}i:53;a:1:{s:3:";-)";s:8:"wink.png";}i:54;a:1:{s:4:"!ill";s:7:"ill.png";}i:55;a:1:{s:7:"!amazed";s:10:"amazed.png";}i:56;a:1:{s:4:"!cry";s:7:"cry.png";}i:57;a:1:{s:6:"!dodge";s:9:"dodge.png";}i:58;a:1:{s:6:"!alien";s:9:"alien.png";}i:59;a:1:{s:6:"!heart";s:9:"heart.png";}}';
	$sql->db_Insert("core", "'emote', '$tmp' ");
	return $tmp;
}


?>