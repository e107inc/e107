<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/forum/templates/forum_viewforum_template.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }
if(!defined("USER_WIDTH")){ define("USER_WIDTH","width:95%"); }

if (!$FORUM_VIEW_START)
{
$FORUM_VIEW_START = "

	<div style='text-align:center'>
	<div class='spacer'>
	<table style='".USER_WIDTH."' class='fborder' >
	<tr>
	<td class='fcaption'>{BREADCRUMB}</td>
	</tr>
	{SUBFORUMS}
	<tr>
	<td style='width:80%' class='forumheader'>
	<span class='mediumtext'>{FORUMTITLE}</span></td>
	</tr>
	</table>
	</div>

	<table style='".USER_WIDTH."'>
	<tr>
	<td style='width:80%'>
	<span class='mediumtext'>{THREADPAGES}</span>
	</td>
	<td style='width:20%; text-align:right'>
	{NEWTHREADBUTTON}
	</td>
	</tr>
	</table>

	<div class='spacer'>
	<table style='".USER_WIDTH."' class='fborder'>
	<tr>
	<td style='width:3%' class='fcaption'>&nbsp;</td>
	<td style='width:47%' class='fcaption'>{THREADTITLE}</td>
	<td style='width:20%; text-align:center' class='fcaption'>{STARTERTITLE}</td>
	<td style='width:5%; text-align:center' class='fcaption'>{REPLYTITLE}</td>
	<td style='width:5%; text-align:center' class='fcaption'>{VIEWTITLE}</td>
	<td style='width:20%; text-align:center' class='fcaption'>{LASTPOSTITLE}</td>
	</tr>";
}

if(!$FORUM_VIEW_START_CONTAINER)
{
	$FORUM_VIEW_START_CONTAINER = "
	<div style='text-align:center'>
	<div class='spacer'>
	<table style='".USER_WIDTH."' class='fborder' >
	<tr>
	<td class='fcaption'>{BREADCRUMB}</td>
	</tr>
	{SUBFORUMS}
	</table>
	</div>
	";
}


if (!$FORUM_VIEW_FORUM) {
	$FORUM_VIEW_FORUM = "
		<tr>
		<td style='vertical-align:middle; text-align:center; width:3%' class='forumheader3'>{ICON}</td>
		<td style='vertical-align:middle; text-align:left; width:47%' class='forumheader3'>

		<table style='width:100%'>
		<tr>
		<td style='width:90%'><span class='mediumtext'><b>{THREADTYPE}{THREADNAME}</b></span><br /><span class='smalltext'>{PAGES}</span></td>
		<td style='width:10%; white-space:nowrap;'>{ADMIN_ICONS}</td>
		</tr>
		</table>

		</td>

		<td style='vertical-align:middle; text-align:center; width:20%' class='forumheader3'>{POSTER}<br />{THREADDATE}</td>
		<td style='vertical-align:middle; text-align:center; width:5%' class='forumheader3'>{REPLIES}</td>
		<td style='vertical-align:middle; text-align:center; width:5%' class='forumheader3'>{VIEWS}</td>
		<td style='vertical-align:middle; text-align:center; width:20%' class='forumheader3'>{LASTPOST}</td>
		</tr>";
}

if (!$FORUM_VIEW_FORUM_STICKY) {
	$FORUM_VIEW_FORUM_STICKY = "
		<tr>
		<td style='vertical-align:middle; text-align:center; width:3%' class='forumheader3'>{ICON}</td>
		<td style='vertical-align:middle; text-align:left; width:47%' class='forumheader3'>

		<table style='width:100%'>
		<tr>
		<td style='width:90%'><span class='mediumtext'><b>{THREADTYPE}{THREADNAME}</b></span> <span class='smalltext'>{PAGES}</span></td>
		<td style='width:10%; white-space:nowrap;'>{ADMIN_ICONS}</td>
		</tr>
		</table>

		</td>

		<td style='vertical-align:middle; text-align:center; width:20%' class='forumheader3'>{POSTER}<br />{THREADDATE}</td>
		<td style='vertical-align:middle; text-align:center; width:5%' class='forumheader3'>{REPLIES}</td>
		<td style='vertical-align:middle; text-align:center; width:5%' class='forumheader3'>{VIEWS}</td>
		<td style='vertical-align:middle; text-align:center; width:20%' class='forumheader3'>{LASTPOST}</td>
		</tr>";
}

if (!$FORUM_VIEW_FORUM_ANNOUNCE) {
	$FORUM_VIEW_FORUM_ANNOUNCE = "
		<tr>
		<td style='vertical-align:middle; text-align:center; width:3%' class='forumheader3'>{ICON}</td>
		<td style='vertical-align:middle; text-align:left; width:47%' class='forumheader3'>

		<table style='width:100%'>
		<tr>
		<td style='width:90%'><span class='mediumtext'><b>{THREADTYPE}{THREADNAME}</b></span> <span class='smalltext'>{PAGES}</span></td>
		<td style='width:10%; white-space:nowrap;'>{ADMIN_ICONS}</td>
		</tr>
		</table>

		</td>

		<td style='vertical-align:middle; text-align:center; width:20%' class='forumheader3'>{POSTER}<br />{THREADDATE}</td>
		<td style='vertical-align:middle; text-align:center; width:5%' class='forumheader3'>{REPLIES}</td>
		<td style='vertical-align:middle; text-align:center; width:5%' class='forumheader3'>{VIEWS}</td>
		<td style='vertical-align:middle; text-align:center; width:20%' class='forumheader3'>{LASTPOST}</td>
		</tr>";
}

if (!$FORUM_VIEW_END) {
	$FORUM_VIEW_END = "
		</table>
		</div>

		<table style='".USER_WIDTH."'>
		<tr>
		<td style='width:80%'><span class='mediumtext'>{THREADPAGES}</span>
		</td>
		<td style='width:20%; text-align:right'>
		{NEWTHREADBUTTON}
		</td>
		</tr>
		<tr>
		<td colspan ='2'>
		{FORUMJUMP}
		</td>
		</tr>
		</table>

		<div class='spacer'>
		<table class='fborder' style='".USER_WIDTH."'>
		<tr>
		<td style='vertical-align:middle; width:50%' class='forumheader3'><span class='smalltext'>{MODERATORS}</span></td>
		<td style='vertical-align:middle; width:50%' class='forumheader3'><span class='smalltext'>{BROWSERS}</span></td>
		</tr>
		</table>
		</div>

		<div class='spacer'>
		<table class='fborder' style='".USER_WIDTH."'>
		<tr>
		<td style='vertical-align:middle; width:50%' class='forumheader3'>{ICONKEY}</td>
		<td style='vertical-align:middle; text-align:center; width:50%' class='forumheader3'><span class='smallblacktext'>{PERMS}</span><br /><br />{SEARCH}
		</td>
		</tr>
		</table>
		</div>
		</div>
		<div class='spacer'>";
		/* hardcoded deprecated rss links
		<div style='text-align:center;'>
		<a href='".e_PLUGIN."rss_menu/rss.php?11.1.".e_QUERY."'><img src='".e_PLUGIN."rss_menu/images/rss1.png' alt='".LAN_431."' style='vertical-align: middle; border: 0;' /></a>
		<a href='".e_PLUGIN."rss_menu/rss.php?11.2.".e_QUERY."'><img src='".e_PLUGIN."rss_menu/images/rss2.png' alt='".LAN_432."' style='vertical-align: middle; border: 0;' /></a>
		<a href='".e_PLUGIN."rss_menu/rss.php?11.3.".e_QUERY."'><img src='".e_PLUGIN."rss_menu/images/rss3.png' alt='".LAN_433."' style='vertical-align: middle; border: 0;' /></a>
		</div>
		*/
		$FORUM_VIEW_END .= "
		<div class='nforumdisclaimer' style='text-align:center'>Powered by <b>e107 Forum System</b></div>
		</div>
";
}


if(!$FORUM_VIEW_END_CONTAINER)
{
	$FORUM_VIEW_END_CONTAINER = "
		<table style='".USER_WIDTH."'>
		<tr>
		<td colspan ='2'>
		{FORUMJUMP}
		</td>
		</tr>
		</table>
		<div class='nforumdisclaimer' style='text-align:center'>Powered by <b>e107 Forum System</b></div></div>
";
}


if (!$FORUM_VIEW_SUB_START)
 {
	$FORUM_VIEW_SUB_START = "
	<tr>
	<td colspan='2'>
		<br />
		<div>
		<table style='width:100%'>
		<tr>
			<td class='fcaption' style='width: 5%'>&nbsp;</td>
			<td class='fcaption' style='width: 45%'>".FORLAN_20."</td>
			<td class='fcaption' style='width: 10%'>".FORLAN_21."</td>
			<td class='fcaption' style='width: 10%'>".LAN_55."</td>
			<td class='fcaption' style='width: 30%'>".FORLAN_22."</td>
		</tr>
	";
}

if (!$FORUM_VIEW_SUB) {
	$FORUM_VIEW_SUB = "
	<tr>
		<td class='forumheader3' style='text-align:center'>{NEWFLAG}</td>
		<td class='forumheader3' style='text-align:left'><b>{SUB_FORUMTITLE}</b><br />{SUB_DESCRIPTION}</td>
		<td class='forumheader3' style='text-align:center'>{SUB_THREADS}</td>
		<td class='forumheader3' style='text-align:center'>{SUB_REPLIES}</td>
		<td class='forumheader3' style='text-align:center'>{SUB_LASTPOST}</td>
	</tr>
	";
}

if (!$FORUM_VIEW_SUB_END) {
	$FORUM_VIEW_SUB_END = "
	</table><br /><br />
	</div>
	</td>
	</tr>
	";
}

if (!$FORUM_IMPORTANT_ROW) {
	$FORUM_IMPORTANT_ROW = "<tr><td class='forumheader'>&nbsp;</td><td colspan='5'  class='forumheader'><span class='mediumtext'><b>".LAN_411."</b></span></td></tr>";
}

if (!$FORUM_NORMAL_ROW) {
	$FORUM_NORMAL_ROW = "<tr><td class='forumheader'>&nbsp;</td><td colspan='5'  class='forumheader'><span class='mediumtext'><b>".LAN_412."</b></span></td></tr>";
}

$FORUM_CRUMB['sitename']['value'] = "<a class='forumlink' {SITENAME_HREF}>{SITENAME}</a>";
$FORUM_CRUMB['sitename']['sep'] = " :: ";

$FORUM_CRUMB['forums']['value'] = "<a class='forumlink' {FORUMS_HREF}>{FORUMS_TITLE}</a>";
$FORUM_CRUMB['forums']['sep'] = " :: ";

$FORUM_CRUMB['parent']['value'] = "{PARENT_TITLE}";
$FORUM_CRUMB['parent']['sep'] = " :: ";

$FORUM_CRUMB['subparent']['value'] = "<a class='forumlink' {SUBPARENT_HREF}>{SUBPARENT_TITLE}</a>";
$FORUM_CRUMB['subparent']['sep'] = " :: ";

$FORUM_CRUMB['forum']['value'] = "{FORUM_TITLE}";

?>