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
|     $Source: /cvs_backup/e107_0.7/e107_themes/lamb/forum_post_template.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-01-29 18:06:35 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

// the user box and subject box are not always displayed, therefore we need to define them /in case/ they are, if not they'll be ignored.

$userbox = "<tr>
<td class='forumheader2' style='width:20%'>".LAN_61."</td>
<td class='forumheader2' style='width:80%'>
<input class='tbox' type='text' name='anonname' size='71' value='".$anonname."' maxlength='20' />
</td>
</tr>";

$subjectbox = "<tr>
<td class='forumheader2' style='width:20%'>".LAN_62."</td>
<td class='forumheader2' style='width:80%'>
<input class='tbox' type='text' name='subject' size='71' value='".$subject."' maxlength='100' />
</td>
</tr>";

// the poll is optional, be careful when changing the values here, only change if you know what you're doing ...

$poll = "<tr>
<td colspan='2' class='nforumcaption2'>".LAN_4."</td>
</tr>
<tr>
<td colspan='2' class='forumheader3'>
<span class='smalltext'>".LAN_386."
</td>
</tr>
<tr><td style='width:20%' class='forumheader3'><div class='normaltext'>".LAN_5."</div></td><td style='width:80%'class='forumheader3'><input class='tbox' type='text' name='poll_title' size='70' value=\"".$aj->tpa($_POST['poll_title'])."\" maxlength='200' />";
	 
$option_count = ($_POST['option_count'] ? $_POST['option_count'] : 1);
$poll .= "<input type='hidden' name='option_count' value='$option_count'>";
	 
for($count = 1; $count <= $option_count; $count++) {
	$var = "poll_option_".$count;
	$option = stripslashes($$var);
	$poll .= "<tr><td style='width:20%' class='forumheader3'>".LAN_391." ".$count.":</td><td style='width:80%' class='forumheader3'><input class='tbox' type='text' name='poll_option[]' size='60' value=\"".$aj->tpa($_POST['poll_option'][($count-1)])."\" maxlength='200' />";
	if ($option_count == $count) {
		$poll .= " <input class='button' type='submit' name='addoption' value='".LAN_6."' /> ";
	}
	$poll .= "</td></tr>";
}
$poll .= "<tr><td style='width:20%' class='forumheader3'>".LAN_7."</td><td class='forumheader3'>";
$poll .= ($_POST['activate'] == 9 ? "<input name='activate' type='radio' value='9' checked>".LAN_8."<br />" : "<input name='activate' type='radio' value='9'>".LAN_8."<br />");
$poll .= ($_POST['activate'] == 10 ? "<input name='activate' type='radio' value='10' checked>".LAN_9."<br />" : "<input name='activate' type='radio' value='10'>".LAN_9."<br />");
$poll .= "</td>\n</tr>";

// finally, file attach is optional, again only change this if you know what you're doing ...

$fileattach = "<tr><td colspan='2' class='nforumcaption2'>".LAN_390."</td></tr><tr><td style='width:20%' class='forumheader3'>".LAN_392."</td><td style='width:80%' class='forumheader3'>".LAN_393." | ".str_replace("\n", " | ", $pref['upload_allowedfiletype'])." |<br />".LAN_394."<br />".LAN_395.": ".($pref['upload_maxfilesize'] ? $pref['upload_maxfilesize'].LAN_396 : ini_get('upload_max_filesize'))."<br /><input class='tbox' name='file_userfile[]' type='file' size='47'>\n</td>\n</tr>\n</td>\n</tr>\n";


// ------------

$FORUMPOST = "
<div style='text-align:center'>
{FORMSTART}
<table style='width:100%' class='fborder'>
<tr>
<td colspan='2' class='nforumcaption'>{BACKLINK}
</td>
</tr>
{USERBOX}
{SUBJECTBOX}
<tr>
<td class='forumheader2' style='width:20%'>{POSTTYPE}</td>
<td class='forumheader2' style='width:80%'>
{POSTBOX}<br />{EMOTES}<br />{EMAILNOTIFY}<br />{POSTTHREADAS}
</td>
</tr>

{POLL}

{FILEATTACH}

<tr style='vertical-align:top'>
<td colspan='2' class='forumheader' style='text-align:center'>
{BUTTONS}
</table>
{FORMEND}
</div>
{FORUMJUMP}
";
	
?>