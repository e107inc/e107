<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/forum/templates/forum_post_template.php,v $
 * $Revision: 1.4 $
 * $Date: 2009-11-17 13:48:43 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }
if(!defined("USER_WIDTH")){ define("USER_WIDTH","width:95%"); }

// the user box and subject box are not always displayed, therefore we need to define them /in case/ they are, if not they'll be ignored.

if(!$userbox)
{
$userbox = "<tr>
<td class='forumheader2' style='width:20%'>".LAN_61."</td>
<td class='forumheader2' style='width:80%'>
<input class='tbox' type='text' name='anonname' size='71' value='".$anonname."' maxlength='20' style='width:95%' />
</td>
</tr>";
}

if(!$subjectbox)
{
$subjectbox = "<tr>
<td class='forumheader2' style='width:20%'>".LAN_62."</td>
<td class='forumheader2' style='width:80%'>
<input class='tbox' type='text' name='subject' size='71' value='".$subject."' maxlength='100' style='width:95%' />
</td>
</tr>";
}

// the poll is optional, be careful when changing the values here, only change if you know what you're doing ...
if(!$poll_form)
{
	if(is_readable(e_PLUGIN.'poll/poll_class.php')) {
		require_once(e_PLUGIN.'poll/poll_class.php');
		$pollo = new poll;
		$poll_form = $pollo -> renderPollForm('forum');
	}
}

// finally, file attach is optional, again only change this if you know what you're doing ...
if(!$fileattach)
{
$fileattach = "
<tr>
	<td colspan='2' class='nforumcaption2'>".($pref['image_post'] ? LAN_390 : LAN_416)."</td>
</tr>
<tr>
	<td style='width:20%' class='forumheader3'>".LAN_392."</td>
	<td style='width:80%' class='forumheader3'>".LAN_393." | ".$allowed_filetypes." |<br />".LAN_394."<br />".LAN_395.": ".($max_upload_size ? $max_upload_size.LAN_396 : ini_get('upload_max_filesize'))."
		<br />
		<div id='fiupsection'>
		<span id='fiupopt'>
			<input class='tbox' name='file_userfile[]' type='file' size='47' />
		</span>
		</div>
		<input class='button' type='button' name='addoption' value='".LAN_417."' onclick=\"duplicateHTML('fiupopt','fiupsection')\" />
	</td>
</tr>
";
//</td>
//</tr>
}
// If the upload directory is not writable, we need to alert the user about this.
if(!$fileattach_alert)
{
	$fileattach_alert = "
	<tr>
		<td colspan='2' class='nforumcaption2'>".($pref['image_post'] ? LAN_390 : LAN_416)."</td>
	</tr>
	<tr>
		<td colspan='2' class='forumheader3'>".LAN_FORUM_1."</td>
	</tr>\n";
}
// ------------

if(!$FORUMPOST)
{
$FORUMPOST = "
<div style='text-align:center'>
<div class='spacer'>
{FORMSTART}
<table style='".USER_WIDTH."' class='fborder'>
<tr>
<td colspan='2' class='fcaption'>{BACKLINK}
</td>
</tr>
{USERBOX}
{SUBJECTBOX}
<tr>
<td class='forumheader2' style='width:20%'>{POSTTYPE}</td>
<td class='forumheader2' style='width:80%'>
{POSTBOX}<br />
{EMAILNOTIFY}<br />
{NOEMOTES}<br />
{POSTTHREADAS}
</td>
</tr>
{POLL}
{FILEATTACH}

<tr style='vertical-align:top'>
<td colspan='2' class='forumheader' style='text-align:center'>
{BUTTONS}
</td>
</tr>
</table>
{FORMEND}

<table style='".USER_WIDTH."'>
<tr>
<td>
{FORUMJUMP}
</td>
</tr>
</table>
</div></div>
";
}

if(!$FORUMPOST_REPLY)
{
$FORUMPOST_REPLY = "
<div style='text-align:center'>
<div class='spacer'>
{FORMSTART}
<table style='".USER_WIDTH."' class='fborder'>
<tr>
<td colspan='2' class='fcaption'>{BACKLINK}
</td>
</tr>
{USERBOX}
{SUBJECTBOX}
<tr>
<td class='forumheader2' style='width:20%'>{POSTTYPE}</td>
<td class='forumheader2' style='width:80%'>
{POSTBOX}<br />
{EMAILNOTIFY}<br />
{NOEMOTES}<br />
{POSTTHREADAS}
</td>
</tr>

{POLL}

{FILEATTACH}

<tr style='vertical-align:top'>
<td colspan='2' class='forumheader' style='text-align:center'>
{BUTTONS}
</td>
</tr>
</table>
{FORMEND}

<table style='".USER_WIDTH."'>
<tr>
<td>
{FORUMJUMP}
</td>
</tr>
</table>
</div></div>
<div style='text-align:center'>
{THREADTOPIC}
{LATESTPOSTS}
</div>
";
}

if(!$LATESTPOSTS_START)
{
$LATESTPOSTS_START = "
<table style='".USER_WIDTH."' class='fborder'>
<tr>
<td colspan='2' class='fcaption' style='vertical-align:top'>".
LAN_101."{LATESTPOSTSCOUNT}".LAN_102."
</td>
</tr>";
}

if(!$LATESTPOSTS_POST)
{
$LATESTPOSTS_POST = "
<tr>
<td class='forumheader3' style='width:20%;vertical-align:top'><b>{POSTER}</b></td>
<td class='forumheader3' style='width:80%'>
	<div class='smallblacktext' style='text-align:right'>".IMAGE_post2." ".LAN_322."{THREADDATESTAMP}</div>
	{POST}
</td>
</tr>
";
}

if(!$LATESTPOSTS_END)
{
$LATESTPOSTS_END = "
</table>
";
}

if(!$THREADTOPIC_REPLY)
{
$THREADTOPIC_REPLY = "
<table style='".USER_WIDTH."' class='fborder'>
<tr>
	<td colspan='2' class='fcaption' style='vertical-align:top'>".LAN_100."</td>
</tr>
<tr>
	<td class='forumheader3' style='width:20%;vertical-align:top'><b>{POSTER}</b></td>
	<td class='forumheader3' style='width:80%'>
		<div class='smallblacktext' style='text-align:right'>".IMAGE_post2." ".LAN_322."{THREADDATESTAMP}</div>{POST}
	</td>
</tr>
</table>
";
}

$FORUM_CRUMB['sitename']['value'] = "<a class='forumlink' {SITENAME_HREF}>{SITENAME}</a>";
$FORUM_CRUMB['sitename']['sep'] = " :: ";

$FORUM_CRUMB['forums']['value'] = "<a class='forumlink' {FORUMS_HREF}>{FORUMS_TITLE}</a>";
$FORUM_CRUMB['forums']['sep'] = " :: ";

$FORUM_CRUMB['parent']['value'] = "{PARENT_TITLE}";
$FORUM_CRUMB['parent']['sep'] = " :: ";

$FORUM_CRUMB['subparent']['value'] = "<a class='forumlink' {SUBPARENT_HREF}>{SUBPARENT_TITLE}</a>";
$FORUM_CRUMB['subparent']['sep'] = " :: ";

$FORUM_CRUMB['forum']['value'] = "<a class='forumlink' {FORUM_HREF}>{FORUM_TITLE}</a>";
$FORUM_CRUMB['forum']['sep'] = " :: ";

$FORUM_CRUMB['thread']['value'] = "{THREAD_TITLE}";

?>