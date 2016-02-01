<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }
if(!defined("USER_WIDTH")){ define("USER_WIDTH","width:95%"); }

if (!isset($FORUM_MAIN_START))
{
	$FORUM_MAIN_START = "<div style='text-align:center'>\n<div class='spacer'>\n<table style='".USER_WIDTH."' class='fborder table'>\n<tr>\n<th colspan='2' style='width:60%; text-align:center' class='fcaption'>{FORUMTITLE}</th>\n<th style='width:10%; text-align:center' class='fcaption'>{THREADTITLE}</th>\n<th style='width:10%; text-align:center' class='fcaption'>{REPLYTITLE}</th>\n<th style='width:20%; text-align:center' class='fcaption'>{LASTPOSTITLE}</th>\n</tr>";
}
if (!isset($FORUM_MAIN_PARENT))
{
	$FORUM_MAIN_PARENT = " <tr>\n<td colspan='5' class='forumheader'>{PARENTNAME} {PARENTSTATUS}</td>\n</tr>";
}
if (!isset($FORUM_MAIN_FORUM))
{
	$FORUM_MAIN_FORUM = "<tr>\n<td style='width:5%; text-align:center' class='forumheader2'>{NEWFLAG}</td>\n<td style='width:55%' class='forumheader2'>{FORUMNAME}<br /><span class='smallblacktext'>{FORUMDESCRIPTION}</span>{FORUMSUBFORUMS}</td>\n<td style='width:10%; text-align:center' class='forumheader3'>{THREADS}</td>\n<td style='width:10%; text-align:center' class='forumheader3'>{REPLIES}</td>\n<td style='width:20%; text-align:center' class='forumheader3'><span class='smallblacktext'>{LASTPOST}</span></td>\n</tr>";
}
if (!isset($FORUM_MAIN_END))
{
	$FORUM_MAIN_END = "</table></div>\n<div class='spacer'>\n<table style='".USER_WIDTH."' class='fborder table'>\n<tr>\n<td colspan='2' style='width:60%' class='fcaption'>{INFOTITLE}</td>\n</tr>\n<tr>\n<td rowspan='4' style='width:5%; text-align:center' class='forumheader3'>{LOGO}</td>\n<td style='width:auto' class='forumheader3'>{USERINFO}</td>\n</tr>\n<tr>\n<td style='width:95%' class='forumheader3'>{INFO}</td>\n</tr><tr>\n<td style='width:95%' class='forumheader3'>{FORUMINFO}</td>\n</tr>\n<tr>\n<td style='width:95%' class='forumheader3'>{USERLIST}<br />{STATLINK}</td>\n</tr>\n</table>\n</div>\n<div class='spacer'>\n<table class='fborder table' style='".USER_WIDTH."'>\n<tr>\n<td class='forumheader3' style='text-align:center; width:33%'>{ICONKEY}</td>\n<td style='text-align:center; width:33%' class='forumheader3'>{SEARCH}</td>\n<td style='width:33%; text-align:center; vertical-align:middle' class='forumheader3'><span class='smallblacktext'>{PERMS}</span>\n</td>\n</tr>\n</table>\n</div>\n</div>";
}

if (!isset($FORUM_NEWPOSTS_START))
{
	$FORUM_NEWPOSTS_START = "<div style='text-align:center'>\n<div class='spacer'>\n<table style='".USER_WIDTH."' class='fborder table'>\n<tr>\n<td style='width:3%' class='fcaption'>&nbsp;</td>\n<td style='width:60%' class='fcaption'>{NEWTHREADTITLE}</td>\n<td style='width:27%; text-align:center' class='fcaption'>{POSTEDTITLE}</td>\n</tr>";
}

if (!isset($FORUM_NEWPOSTS_MAIN))
{
	$FORUM_NEWPOSTS_MAIN = "<tr>\n<td style='width:3%' class='forumheader3'>{NEWIMAGE}</td>\n<td style='width:60%' class='forumheader3'>{NEWSPOSTNAME}</td>\n<td style='width:27%; text-align:center' class='forumheader3'>{STARTERTITLE}</td>\n</tr>";
}

if (!isset($FORUM_NEWPOSTS_END))
{
	$FORUM_NEWPOSTS_END = "</table></div></div>";
}

if (!isset($FORUM_TRACK_START))
{
	$FORUM_TRACK_START = "<div style='text-align:center'>\n<div class='spacer'>\n<table style='".USER_WIDTH."' class='fborder table'>\n<tr>\n<td colspan='3' style='width:60%' class='fcaption'>{TRACKTITLE}</td>\n</tr>\n";

	if (!isset($FORUM_TRACK_MAIN))
	{
		$FORUM_TRACK_MAIN = "<tr>
			<td style='text-align:center; vertical-align:middle; width:6%'  class='forumheader3'>{NEWIMAGE}</td>
			<td style='vertical-align:middle; text-align:left; width:70%'  class='forumheader3'><span class='mediumtext'>{TRACKPOSTNAME}</span></td>
			<td style='vertical-align:middle; text-align:center; width:24%'  class='forumheader3'><span class='mediumtext'>{UNTRACK}</span></td>
			</tr>";
	}
}

if (!isset($FORUM_TRACK_END))
{
	$FORUM_TRACK_END = "</table>\n</div>\n</div>";
}


/*
$FORUM_MAIN_START	= "<br />MAIN START";
$FORUM_MAIN_PARENT 	= "<br />MAIN PARENT";
$FORUM_MAIN_FORUM		= "<br />MAIN FORUM";
$FORUM_MAIN_END		= "<br />MAIN END";
$FORUM_NEWPOSTS_START	= "<br />NEWPOSTS-START";
$FORUM_NEWPOSTS_MAIN 	= "<br />NEWPOSTS-MAIN";
$FORUM_NEWPOSTS_END 	= "<br />NEWPOSTS END";
$FORUM_TRACK_START	= "<br />TRACK-START";
$FORUM_TRACK_MAIN	= "<br />TRACK-MAIN";
$FORUM_TRACK_END	= "<br />TRACK-END";
*/

// New in v2.x - requires a bootstrap theme be loaded.  


$FORUM_TEMPLATE['main']['start']			= "{FORUM_BREADCRUMB}
											<div class=''>

												<div class='form-group right'>
													{SEARCH}
												</div>
											</div>
											<div id='forum' >
											<table class='table table-striped table-bordered table-hover'>
											<colgroup>
											<col style='width:3%' />
											<col />
											<col class='hidden-xs' style='width:10%' />
											<col style='width:10%' />
											<col class='hidden-xs' style='width:20%' />
											</colgroup>
											<tr>
											<th colspan='5'>{FORUMTITLE}</th>
											</tr>";

$FORUM_TEMPLATE['main']['parent']			= 	"<tr>
											<th colspan='2'>{PARENTNAME} {PARENTSTATUS}</th>
											<th class='hidden-xs text-center'>".LAN_FORUM_0003."</th>
											<th class='text-center'>".LAN_FORUM_0002."</th>
											<th class='hidden-xs text-center'>".LAN_FORUM_0004."</th>
											</tr>";


$FORUM_TEMPLATE['main']['forum']			= 	"<tr>
											<td>{NEWFLAG}</td>
											<td>{FORUMNAME}<br /><small>{FORUMDESCRIPTION}</small>{FORUMSUBFORUMS}</td>
											<td class='hidden-xs text-center'>{REPLIESX}</td>
											<td class='text-center'>{THREADSX}</td>
											<td class='hidden-xs text-center'><small>{LASTPOSTUSER} {LASTPOSTDATE}</small></td>
											</tr>";

$FORUM_TEMPLATE['main']['end']				= "</table><div class='center'><small>{USERINFOX}</small></div></div>";



// Tracking
$FORUM_TEMPLATE['track']['start']       = "{FORUM_BREADCRUMB}<div id='forum-track'>
											<table class='table table-striped table-bordered table-hover'>
											<colgroup>
											<col style='width:5%' />
											<col />
											<col style='width:10%' />
											</colgroup>
											<thead>
											<tr>

												<th colspan='2'>".LAN_FORUM_1003."</th>
												<th class='text-center'>".LAN_FORUM_1020."</th>
												</tr>
											</thead>
											";

$FORUM_TEMPLATE['track']['item']        = "<tr>
											<td class='text-center'>{NEWIMAGE}</td>
											<td>{TRACKPOSTNAME}</td>
											<td class='text-center'>{UNTRACK}</td>
											</tr>";


$FORUM_TEMPLATE['track']['end']         = "</table>\n</div>";




/*
$FORUM_TEMPLATE['main-end']				.= "

<div class='center'>
	<small class='muted'>{PERMS}</small>
	</div>
<table style='".USER_WIDTH."' class='fborder table'>\n<tr>
<td colspan='2' style='width:60%' class='fcaption'>{INFOTITLE}</td>\n</tr>\n<tr>
<td rowspan='4' style='width:5%; text-align:center' class='forumheader3'>{LOGO}</td>
<td style='width:auto' class='forumheader3'>{USERINFO}</td>\n</tr>
<tr>\n<td style='width:95%' class='forumheader3'>{INFO}</td>\n</tr>
<tr>\n<td style='width:95%' class='forumheader3'>{FORUMINFO}</td>\n</tr>
<tr>\n<td style='width:95%' class='forumheader3'>{USERLIST}<br />{STATLINK}</td>\n</tr>\n</table>
";
*/












?>