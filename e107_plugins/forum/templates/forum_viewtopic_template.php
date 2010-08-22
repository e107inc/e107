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
if(!defined("USER_WIDTH")){ define("USER_WIDTH","width:95%;margin-left:auto;margin-right:auto"); }


$sc_style['LASTEDIT']['pre'] = "<br /><br /><span class='smallblacktext'>[ ".LAN_29." ";
$sc_style['LASTEDIT']['post'] = " ]</span>";

$sc_style['LEVEL']['pre'] = "<div class='spacer'>";
$sc_style['LEVEL']['post'] = "</div>";

$sc_style['ANON_IP']['pre'] = "<br /><div class='smalltext'>";
$sc_style['ANON_IP']['post'] = "</div>";

$sc_style['USER_EXTENDED']['location.text_value']['mid'] = ": ";
$sc_style['USER_EXTENDED']['location.text_value']['post'] = "<br />";

$FORUMSTART = "<a id='top'></a><div style='text-align:center'>
	<div class='spacer'>
	<table style='".USER_WIDTH."' class='fborder'>
	<tr>
	<td class='fcaption'>
	{BACKLINK}
	</td>
	<td class='fcaption' style='text-align: right'>
	<div class='smalltext'>&nbsp;
	{TRACK}
	</div>
	</td>
	<td class='fcaption' style='text-align: right'>
	<span class='smalltext'>
	{NEXTPREV}
	</span>
	</td>
	</tr>
	<tr>
	<td class='forumheader' colspan='3'>
	{THREADNAME}
	</td>
	</tr>
	</table>
	</div>

	<table style='".USER_WIDTH."'>
	<tr>
	<td style='width:60%; text-align: left'>
	{GOTOPAGES}
	</td>
	<td style='width:40%; text-align:right; white-space: nowrap'>
	{BUTTONS}
	</td>
	</tr>
	<tr>
	<td style='width:60%; text-align: left'>
	<div class='spacer'>
	{MODERATORS}
	</div>
	</td>
	<td style='width:40%; text-align:right'>
	{THREADSTATUS}
	</td>
	</tr>
	</table>

	<div class='spacer'>
	<table style='".USER_WIDTH."' class='fborder'>
	<tr>
	<td style='width:20%; text-align:center' class='fcaption'>
	".LAN_402."
	</td>
	<td style='width:80%; text-align:center' class='fcaption'>
	".LAN_403."
	</td>
	</tr>";

$FORUMTHREADSTYLE = "<tr>
	<td class='forumheader' style='vertical-align:middle'>
	{NEWFLAG}
	{POSTER}
	{ANON_IP}
	</td>
	<td class='forumheader' style='vertical-align:middle'>
	<table cellspacing='0' cellpadding='0' style='width:100%'>
	<tr>
	<td class='smallblacktext'>
	{THREADDATESTAMP}
	</td>
	<td style='text-align:right'>
	{REPORTIMG}{EDITIMG}{QUOTEIMG}
	</td>
	</tr>
	</table>
	</td>
	</tr>
	<tr>
	<td class='forumheader3' style='vertical-align:top'>
	{CUSTOMTITLE}
	{AVATAR}
	<div class='smalltext'>
	{LEVEL=special}
	{LEVEL=pic}
	{LEVEL=userid}
	{JOINED}
	{USER_EXTENDED=location.text_value}
	{POSTS}
	</div>
	</td>
	<td class='forumheader3' style='vertical-align:top'>{POLL}
	{POST}
	{LASTEDIT}
	{SIGNATURE}
	</td>
	</tr>
	<tr>
	 <td class='finfobar'>
	<span class='smallblacktext'>
	{TOP}
	</span>
	</td>
	<td class='finfobar' style='vertical-align:top'>
	<table cellspacing='0' cellpadding='0' style='width:100%'>
	<tr>
	<td>
	{PROFILEIMG}
	 {EMAILIMG}
	 {WEBSITEIMG}
	 {PRIVMESSAGE}
	</td>
	<td style='text-align:right'>
	{MODOPTIONS}
	</td>
	</tr>
	</table>
	</td>
	</tr>
	<tr>
	<td colspan='2'>
	</td>
	</tr>";

$FORUMEND = "<tr><td colspan='2' class='forumheader3' style='text-align:center'>{QUICKREPLY}</td></tr></table></div>

	<table style='".USER_WIDTH."'>
	<tr>
	<td style='width:80%'><span class='mediumtext'>{GOTOPAGES}&nbsp;</span>
	</td>
	<td style='width:20%; text-align: right; white-space: nowrap'>
	{BUTTONS}
	</td>
	</tr>
	<tr>
	<td colspan ='2'>
	{FORUMJUMP}
	</td>
	</tr>
	</table>
	</div>

	<div style='text-align:center' class='spacer'>
	<a href='".e_PLUGIN."rss_menu/rss.php?8.1.".e_QUERY."'><img src='".e_PLUGIN."rss_menu/images/rss1.png' alt='".LAN_431."' style='vertical-align: middle; border: 0;' /></a> <a href='".e_PLUGIN."rss_menu/rss.php?8.2.".e_QUERY."'><img src='".e_PLUGIN."rss_menu/images/rss2.png' alt='".LAN_432."' style='vertical-align: middle; border: 0;' /></a> <a href='".e_PLUGIN."rss_menu/rss.php?8.3.".e_QUERY."'><img src='".e_PLUGIN."rss_menu/images/rss3.png' alt='".LAN_433."' style='vertical-align: middle; border: 0;' /></a></div><div class='nforumdisclaimer' style='text-align:center'>Powered by <b>e107 Forum System</b></div>";

$FORUMREPLYSTYLE = "<tr>
	<td class='forumheader' style='vertical-align:middle'>
	{NEWFLAG}
	{POSTER}
	{ANON_IP}
	</td>
	<td class='forumheader' style='vertical-align:middle'>
	<table cellspacing='0' cellpadding='0' style='width:100%'>
	<tr>
	<td class='smallblacktext'>
	{THREADDATESTAMP}
	</td>
	<td style='text-align:right'>
	{REPORTIMG}{EDITIMG}{QUOTEIMG}
	</td>
	</tr>
	</table>
	</td>
	</tr>
	<tr>
	<td class='forumheader3' style='vertical-align:top'>
	{CUSTOMTITLE}
	{AVATAR}
	<div class='smalltext'>
	{LEVEL=special}
	{LEVEL=pic}
	{LEVEL=userid}
	{JOINED}
	{USER_EXTENDED=location.text_value}
	{POSTS}
	</div>
	</td>
	<td class='forumheader3' style='vertical-align:top'>{POST}
	{LASTEDIT}
	{SIGNATURE}
	</td>
	</tr>
	<tr>
	 <td class='finfobar'>
	<span class='smallblacktext'>
	{TOP}
	</span>
	</td>
	<td class='finfobar' style='vertical-align:top'>
	<table cellspacing='0' cellpadding='0' style='width:100%'>
	<tr>
	<td>
	{PROFILEIMG}
	 {EMAILIMG}
	 {WEBSITEIMG}
	 {PRIVMESSAGE}
	</td>
	<td style='text-align:right'>
	{MODOPTIONS}
	</td>
	</tr>
	</table>
	</td>
	</tr>
	<tr>
	<td colspan='2'>
	</td>
	</tr>";

$FORUM_CRUMB['sitename']['value'] = "<a class='forumlink' {SITENAME_HREF}>{SITENAME}</a>";
$FORUM_CRUMB['sitename']['sep'] = " :: ";

$FORUM_CRUMB['forums']['value'] = "<a class='forumlink' {FORUMS_HREF}>{FORUMS_TITLE}</a>";
$FORUM_CRUMB['forums']['sep'] = " :: ";

$FORUM_CRUMB['parent']['value'] = "{PARENT_TITLE}";
$FORUM_CRUMB['parent']['sep'] = " :: ";

$FORUM_CRUMB['subparent']['value'] = "<a class='forumlink' {SUBPARENT_HREF}>{SUBPARENT_TITLE}</a>";
$FORUM_CRUMB['subparent']['sep'] = " :: ";

$FORUM_CRUMB['forum']['value'] = "<a class='forumlink' {FORUM_HREF}>{FORUM_TITLE}</a>";

?>