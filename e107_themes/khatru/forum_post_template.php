<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }
if(!defined("USER_WIDTH")){ define("USER_WIDTH","width:95%"); }

// the user box and subject box are not always displayed, therefore we need to define them /in case/ they are, if not they'll be ignored.

if(!isset($userbox))
{
$userbox = "<tr>
<td class='forumheader2' style='width:20%'>".LAN_61."</td>
<td class='forumheader2' style='width:80%'>
<input class='tbox' type='text' name='anonname' size='71' value='".$anonname."' maxlength='20' />
</td>
</tr>";
}


if(!isset($subjectbox))
{
$subjectbox = "<tr>
<td class='forumheader2' style='width:20%'>".LAN_62."</td>
<td class='forumheader2' style='width:80%'>
<input class='tbox' type='text' name='subject' size='71' value='".$subject."' maxlength='100' />
</td>
</tr>";
}



// the poll is optional, be careful when changing the values here, only change if you know what you're doing ...
if(!isset($poll_form))
{
	if(is_readable(e_PLUGIN."poll/poll_class.php")) {
		require_once(e_PLUGIN."poll/poll_class.php");
		$pollo = new poll;
		$poll_form = $pollo -> renderPollForm("forum");
	}
}


// finally, file attach is optional, again only change this if you know what you're doing ...
if(!isset($fileattach))
{
$fileattach = "<tr><td colspan='2' class='nforumcaption2'>".($pref['image_post'] ? LAN_390 : LAN_416)."</td></tr>
<tr><td style='width:20%' class='forumheader3'>".LAN_392."</td>
<td style='width:80%' class='forumheader3'>".LAN_393." | ".$allowed_filetypes." |<br />".LAN_394."<br />".LAN_395.": ".($pref['upload_maxfilesize'] ? $pref['upload_maxfilesize'].LAN_396 : ini_get('upload_max_filesize'))."
<br />

<div id='fiupsection'>
<span id='fiupopt'><input class='tbox' name='file_userfile[]' type='file' size='47' /></span>
</div>
<input class='button' type='button' name='addoption' value='".LAN_417."' onclick=\"duplicateHTML('fiupopt','fiupsection')\" />
</td>
</tr>
</td>
</tr>";
}

// ------------


if(!isset($FORUMPOST))
{
$FORUMPOST = "
{FORMSTART}
".BOXOPEN."{BACKLINK}".BOXMAIN."

<div style='text-align:center'>
<table style='width:100%' class='fborder'>
{USERBOX}
{SUBJECTBOX}
<tr>
<td class='forumheader2' style='width:20%; vertical-align:top;'>{POSTTYPE}</td>
<td style='width:80%'>
{POSTBOX}<br />{EMAILNOTIFY}<br />{POSTTHREADAS}
</td>
</tr>
{POLL}
{FILEATTACH}
<tr style='vertical-align:top'>
<td colspan='2' class='nforumcaption2' style='text-align:center'>
{BUTTONS}
</table>
{FORMEND}
</div>
{FORUMJUMP}".BOXCLOSE;
}



if(!isset($FORUMPOST_REPLY))
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
{POSTBOX}<br />{EMAILNOTIFY}<br />{POSTTHREADAS}
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


if(!isset($LATESTPOSTS_START))
{
$LATESTPOSTS_START = "
<table style='".USER_WIDTH."' class='fborder'>
<tr>
<td colspan='2' class='fcaption' style='vertical-align:top'>".
LAN_101."{LATESTPOSTSCOUNT}".LAN_102."
</td>
</tr>";
}

if(!isset($LATESTPOSTS_POST))
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

if(!isset($LATESTPOSTS_END))
{
$LATESTPOSTS_END = "
</table>
";
}

if(!isset($THREADTOPIC_REPLY))
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


if (!isset($FORUMTHREADPOSTED))
{$FORUMTHREADPOSTED = 
BOXOPEN.LAN_133.BOXMAIN."
<table style='width:100%'>
<tr>
<td style='text-align:right; vertical-align:middle; width:20%' class='forumheader2'>".IMAGE_e."&nbsp;</td>
<td style='vertical-align:middle; width:80%' class='forumheader2'>
<br />".LAN_324."<br />
<span class='defaulttext'><a href='".e_PLUGIN."forum/forum_viewtopic.php?".$thread_id."'>".LAN_325."</a><br />
<a href='".e_PLUGIN."forum/forum_viewforum.php?".$forum_id."'>".LAN_326."</a></span><br /><br />
</td></tr></table>".BOXCLOSE;
}


if (!isset($FORUMREPLYPOSTED))
{
$FORUMREPLYPOSTED = 
BOXOPEN.LAN_133.BOXMAIN."
<table style='width:100%'>
<tr>
<td style='text-align:right; vertical-align:middle; width:20%' class='forumheader2'>".IMAGE_e."&nbsp;</td>
<td style='vertical-align:middle; width:80%' class='forumheader2'>
<br />".LAN_324."<br />
<span class='defaulttext'><a href='".e_PLUGIN."forum/forum_viewtopic.php?{$iid}.last'>".LAN_325."</a><br />
<a href='".e_PLUGIN."forum/forum_viewforum.php?".$forum_id."'>".LAN_326."</a></span><br /><br />
</td></tr></table>".BOXCLOSE;
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