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
|     $Source: /cvs_backup/e107_0.7/e107_themes/e107v4a/forum_template.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-03-20 22:46:03 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/

$FORUMSTART = "
<table style='width:100%' class='nforumholder' cellpadding='0' cellspacing='0'>
<tr>
<td  colspan='2' class='nforumcaption'>{BACKLINK}</td>
</tr>
<tr>
<td class='nforumcaption2' colspan='2'>
<table cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td>{NEXTPREV}</td>
<td style='text-align:right'>&nbsp;{TRACK}&nbsp;</td>
</tr>
</table>
</td>
</tr>
</table>
<br />

<table style='width:100%'>
<tr>
<td style='width:80%'><div class='mediumtext'><img src='".e_PLUGIN."forum/images/e.png' alt='' style='vertical-align:middle' /> <b>{THREADNAME}</b></div><br />{GOTOPAGES}</td>
<td style='width:20%; vertical-align:bottom;'>{BUTTONS}</td>
</tr>
</table>


<table style='width:100%' class='nforumholder' cellpadding='0' cellspacing='0'>
<tr>
<td style='width:20%; text-align:center' class='nforumcaption2'>\n".LAN_402."\n</td>\n<td style='width:80%; text-align:center' class='nforumcaption2'>\n".LAN_403."\n</td>
</tr>
</table>";

$FORUMTHREADSTYLE = "
<div class='spacer'>
<table style='width:100%' class='nforumholder' cellpadding='0' cellspacing='0'>
<tr>
<td class='nforumcaption3' style='vertical-align:middle; width:20%;'>\n{NEWFLAG}\n{POSTER}\n</td>
<td class='nforumcaption3' style='vertical-align:middle; width:80%;'>
<table cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='smallblacktext'>\n{THREADDATESTAMP}\n</td>
<td style='text-align:right'>\n{EMAILITEM} {PRINTITEM} {REPORTIMG}\n{EDITIMG}\n{QUOTEIMG}\n</td>
</tr>
</table>
</td>
</tr>
<tr>
<td class='nforumthread' style='vertical-align:top'>\n{AVATAR}\n<span class='smalltext'>\n{CUSTOMTITLE}\n{LEVEL}\n{MEMBERID}\n{JOINED}\n{LOCATION}\n{POSTS}\n</span>\n</td>
<td class='nforumthread' style='vertical-align:top'>\n{POST}\n{SIGNATURE}\n</td>
</tr>
<tr>
<td class='nforumthread2'>\n<span class='smallblacktext'>\n{TOP}\n</span>\n</td>
<td class='nforumthread2' style='vertical-align:top'>
<table cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td>\n{PROFILEIMG}\n {EMAILIMG}\n {WEBSITEIMG}\n {PRIVMESSAGE}\n</td>
<td style='text-align:right'>\n{MODOPTIONS}\n</td>
</tr>
</table>
</td>
</tr>
</table>
</div>";

$FORUMEND = "
<div class='spacer'>
<div class='forumheader'>
{GOTOPAGES}
</div>
<br />
<table style='width:100%' class='nforumholder' cellpadding='0' cellspacing='0'>
<tr>
<td style='width:50%; text-align:left; vertical-align:top' class='nforumthread'><b>{MODERATORS}</b><br />{FORUMJUMP}</td>
<td style='width:50%; text-align:right; vertical-align:top' class='nforumthread'>{BUTTONS}</td>
</tr>
</table>
</div>
<div class='spacer'>
<table style='width:100%' class='nforumholder' cellpadding='0' cellspacing='0'>
<tr>
<td style='text-align:center' class='nforumthread2'>
{QUICKREPLY}
</td>
</tr>
</table>
<div class='nforumdisclaimer' style='text-align:center'>Forum theme loosely based on <a href='http://www.invisionpower.com/'>Invision Power Board</a></div>
</div>";


$FORUM_MAIN_START = "<div style='text-align:center'>";

$FORUM_MAIN_PARENT = "<div class='spacer'>\n<table style='width:100%' class='nforumholder' cellpadding='0' cellspacing='0'>\n<tr>\n<td colspan='5' class='nforumcaption'>{PARENTNAME} {PARENTSTATUS}</td>\n</tr>
<tr>\n<td colspan='2' style='width:60%; text-align:center' class='nforumcaption2'>{FORUMTITLE}</td>\n<td style='width:10%; text-align:center' class='nforumcaption2'>{THREADTITLE}</td>\n<td style='width:10%; text-align:center' class='nforumcaption2'>{REPLYTITLE}</td>\n<td style='width:20%; text-align:center' class='nforumcaption2'>{LASTPOSTITLE}</td>\n</tr>\n";

$FORUM_MAIN_PARENT_END = "</table></div>";

$FORUM_MAIN_FORUM = "<tr>\n<td style='width:5%; text-align:center' class='nforumcaption3'>{NEWFLAG}</td>\n<td style='width:55%' class='nforumcaption3'>{FORUMNAME}<br /><span class='smallblacktext'>{FORUMDESCRIPTION}</span></td>\n<td style='width:10%; text-align:center' class='nforumthread'>{THREADS}</td>\n<td style='width:10%; text-align:center' class='nforumthread'>{REPLIES}</td>\n<td style='width:20%; text-align:center' class='nforumthread'><span class='smallblacktext'>{LASTPOST}</span></td>\n</tr>";

$FORUM_MAIN_END = "<div class='spacer'>\n<table style='width:100%' class='fborder'>\n<tr>\n<td colspan='2' style='width:60%' class='nforumcaption2'>{INFOTITLE}</td>\n</tr>\n<tr>\n<td rowspan='4' style='width:5%; text-align:center' class='forumheader3'>{LOGO}</td>\n<td style='width:100%' class='forumheader3'>{USERINFO}</td>\n</tr>\n<tr>\n<td style='width:auto' class='forumheader3'>{INFO}</td>\n</tr>\n<tr>\n<td style='width:100%' class='forumheader3'>{FORUMINFO}</td>\n</tr>\n<tr>\n<td style='width:100%' class='forumheader3'>{USERLIST}</td>\n</tr>\n</table>\n</div>\n<div class='spacer'>\n<table class='fborder' style='width:100%'>\n<tr>\n<td class='forumheader3' style='text-align:center; width:33%'>{ICONKEY}</td>\n<td style='text-align:center; width:33%' class='forumheader3'>{SEARCH}</td>\n<td style='width:33%; text-align:center; vertical-align:middle' class='forumheader3'><span class='smallblacktext'>{PERMS}</span>\n</td>\n</tr>\n</table>\n</div>\n<div class='nforumdisclaimer' style='text-align:center'>Forum theme loosely based on <a href='http://www.invisionpower.com/'>Invision Power Board</a></div></div>";



$FORUM_VIEW_START = "
<table style='width:100%' class='nforumholder' cellpadding='0' cellspacing='0'>
<tr>
<td  colspan='2' class='nforumcaption'>{BREADCRUMB}</td>
</tr>
</table>

<table style='width:100%'><tr>
<td style='width:80%'><div class='mediumtext'><img src='".e_PLUGIN."forum/images/e.png' style='vertical-align:middle' alt='' /> <b>{FORUMTITLE} Forum</b></div>{THREADPAGES}</td>
<td style='width:20%; text-align:right; vertical-align:bottom;'>
{NEWTHREADBUTTON}
</td>
</tr>
</table>

<table style='width:100%' class='nforumholder' cellpadding='0' cellspacing='0'>
<tr>
<td style='width:3%' class='nforumcaption2'>&nbsp;</td>
<td style='width:47%' class='nforumcaption2'>{THREADTITLE}</td>
<td style='width:20%; text-align:center' class='nforumcaption2'>{STARTERTITLE}</td>
<td style='width:5%; text-align:center' class='nforumcaption2'>{REPLYTITLE}</td>
<td style='width:5%; text-align:center' class='nforumcaption2'>{VIEWTITLE}</td>
<td style='width:20%; text-align:center' class='nforumcaption2'>{LASTPOSTITLE}</td>
</tr>";



$FORUM_VIEW_FORUM = "
<tr>
<td style='vertical-align:middle; text-align:center; width:3%' class='nforumview1'>{ICON}</td>
<td style='vertical-align:middle; text-align:left; width:47%'  class='nforumview1'>

<table style='width:100%'>
<tr>
<td style='width:90%'><span class='mediumtext'><b>{THREADNAME}</b></span> <span class='smalltext'>{PAGES}</span></td>
<td style='width:10%; white-space:nowrap;'>{ADMIN_ICONS}</td>
</tr>
</table>
</td>

<td style='vertical-align:top; text-align:center; width:20%' class='nforumview2'><span class='smalltext'><b>{POSTER}</b><br />{THREADDATE}</span></td>
<td style='vertical-align:center; text-align:center; width:5%' class='nforumview2'><span class='smalltext'>{REPLIES}</span></td>
<td style='vertical-align:center; text-align:center; width:5%' class='nforumview2'><span class='smalltext'>{VIEWS}</span></td>
<td style='vertical-align:top; text-align:center; width:20%' class='nforumview2'><span class='smalltext'>{LASTPOST}</span></td>
</tr>";


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


<div class='spacer'>
<table style='width:100%' class='nforumholder' cellpadding='0' cellspacing='0'>
<tr>
<td style='vertical-align:center; width:50%' class='nforumview3'><span class='smalltext'>{MODERATORS}</span></td>
<td style='text-align:right; vertical-align:center; width:50%' class='nforumview3'><span class='smalltext'>{BROWSERS}</span></td>
</tr>

<tr>
<td style='vertical-align:center; width:50%' class='nforumview4'>{ICONKEY}</td>
<td style='vertical-align:center; text-align:center; width:50%' class='nforumview4'>{PERMS}<br /><br />{SEARCH}
</td>
</tr>
</table>
</div>

<div class='nforumdisclaimer' style='text-align:center'>Forum theme loosely based on <a href='http://www.invisionpower.com/'>Invision Power Board</a></div>";



?>