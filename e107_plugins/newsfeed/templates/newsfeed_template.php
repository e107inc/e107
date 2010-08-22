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

$NEWSFEED_MAIN_CAPTION = NFLAN_38;

$NEWSFEED_LIST_START = "
<table style='width: 100%;' class='fborder'>\n";

$NEWSFEED_LIST = "
<tr>
<td style='width: 30%;' class='forumheader3'>{FEEDNAME}</td>
<td style='width: 70%;' class='forumheader3'>{FEEDDESCRIPTION}</td>
</tr>\n";

$NEWSFEED_LIST_END = "
</table>\n";

$NEWSFEED_MAIN_START = "
<table style='width: 100%;' class='fborder'>
<tr>
<td class='forumheader'>{FEEDIMAGE} {FEEDTITLE}</td>
</tr>
<tr>
<td class='forumheader3'>
<ul>\n";

$NEWSFEED_MAIN = "
<li><b>{FEEDITEMLINK}</b> <span class='smalltext'>{FEEDITEMCREATOR}</span><br />{FEEDITEMTEXT}<br /><br /></li>\n";


$NEWSFEED_MAIN_END = "
</ul>
</td>
</tr>

<tr>
<td class='forumheader3' style='text-align: right;'><span class='smalltext'>{FEEDCOPYRIGHT} | {FEEDLASTBUILDDATE}</span></td>
</tr>

<tr>
<td class='forumheader3' style='text-align: center;'><span class='smalltext'>{BACKLINK}</span></td>
</tr>
</table>\n";


?>
