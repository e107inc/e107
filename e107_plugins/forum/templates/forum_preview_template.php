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
if(!defined("USER_WIDTH")){ define("USER_WIDTH","width:95%;margin-left:auto;margin-right:auto"); }

$FORUM_PREVIEW = "<div style='text-align:center'>
<table style='".USER_WIDTH."' class='fborder table'>
<tr>
	<td colspan='2' class='fcaption' style='vertical-align:top'>".LAN_FORUM_3005.
	($action != "nt" ? "</td>" : " ( ".LAN_FORUM_3011.": ".$tsubject." )</td>")."
<tr>
	<td class='forumheader3' style='width:20%; vertical-align:top'><b>".$poster."</b></td>
	<td class='forumheader3' style='width:80%'><div class='smallblacktext' style='text-align:right'>".IMAGE_post2." ".$postdate."</div>".$tpost."</td>
</tr>
</table>
</div>";

?>