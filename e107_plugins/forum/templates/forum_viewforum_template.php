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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/forum/templates/forum_viewforum_template.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-02-26 10:34:23 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
$FORUM_VIEW_START = "

	<div style='text-align:center'>
	<div class='spacer'>
	<table style='width:95%' class='fborder' >
	<tr>
	<td  colspan='2' class='fcaption'>{BREADCRUMB}</td>
	</tr>
	<tr>
	<td style='width:80%; vertical-align:middle'>&nbsp;<span class='mediumtext'>{FORUMTITLE}</span><br />{THREADPAGES}</td>
	<td style='width:20%; text-align:right'>
	{NEWTHREADBUTTON}
	</td></tr><tr>
	<td colspan='2'>

	<table style='width:100%' class='fborder'>
	<tr>
	<td style='width:3%' class='fcaption'>&nbsp;</td>
	<td style='width:47%' class='fcaption'>{THREADTITLE}</td>
	<td style='width:20%; text-align:center' class='fcaption'>{STARTERTITLE}</td>
	<td style='width:5%; text-align:center' class='fcaption'>{REPLYTITLE}</td>
	<td style='width:5%; text-align:center' class='fcaption'>{VIEWTITLE}</td>
	<td style='width:20%; text-align:center' class='fcaption'>{LASTPOSTITLE}</td>
	</tr>";


if (!$FORUM_VIEW_FORUM) {
	$FORUM_VIEW_FORUM = "
		<tr>
		<td style='vertical-align:middle; text-align:center; width:3%' class='forumheader3'>{ICON}</td>
		<td style='vertical-align:middle; text-align:left; width:47%'  class='forumheader3'>

		<table style='width:100%'>
		<tr>
		<td style='width:90%'><span class='mediumtext'><b>{THREADTYPE}<br />{THREADNAME}</b></span> <span class='smalltext'>{PAGES}</span></td>
		<td style='width:10%; white-space:nowrap;'>{ADMIN_ICONS}</td>
		</tr>
		</table>

		</td>

		<td style='vertical-align:top; text-align:center; width:20%' class='forumheader3'>{POSTER}<br />{THREADDATE}</td>
		<td style='vertical-align:center; text-align:center; width:5%' class='forumheader3'>{REPLIES}</td>
		<td style='vertical-align:center; text-align:center; width:5%' class='forumheader3'>{VIEWS}</td>
		<td style='vertical-align:top; text-align:center; width:20%' class='forumheader3'>{LASTPOST}</td>
		</tr>";
}

if (!$FORUM_VIEW_END) {
	$FORUM_VIEW_END = "
		</table>
		<table style='width:100%'>
		<tr>
		<td style='width:80%'><span class='mediumtext'>{THREADPAGES}</span>
		{FORUMJUMP}
		</td>
		<td style='width:20%; text-align:right'>
		{NEWTHREADBUTTON}
		</td>
		</tr>
		</table>
		</td>
		</tr>
		</table>
        </div>
		<div class='spacer'>
		<table class='fborder' style='width:95%'>
		<tr>
		<td style='vertical-align:center; width:50%' class='forumheader3'><span class='smalltext'>{MODERATORS}</span></td>
		<td style='vertical-align:center; width:50%' class='forumheader3'><span class='smalltext'>{BROWSERS}</span></td>
		</tr>
		</table>
		</div>
		<div class='spacer'>
		<table class='fborder' style='width:95%'>
		<tr>
		<td style='vertical-align:center; width:50%' class='forumheader3'>{ICONKEY}</td>
		<td style='vertical-align:center; text-align:center; width:50%' class='forumheader3'><span class='smallblacktext'>{PERMS}</span><br /><br />{SEARCH}
		</td>
		</tr>
		</table>
		</div>
		</div>
";
}

?>