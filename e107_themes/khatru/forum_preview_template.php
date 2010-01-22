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
|     $Source: /cvs_backup/e107_0.7/e107_themes/khatru/forum_preview_template.php,v $
|     $Revision: 1.3 $
|     $Date: 2010-01-22 12:24:16 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$FORUM_PREVIEW = 
($action != "nt" ? "" : " ( ".LAN_62.$tsubject." )")."
<table style='width:100%'>
<td style='width:20%' style='vertical-align:top'><b>".$poster."</b></td>
<td style='width:80%'>
<div class='smallblacktext' style='text-align:right'>".IMAGE_post2." ".LAN_322.$postdate."</div><br />".$tpost."</td>
</tr>
</table>";
	
?>