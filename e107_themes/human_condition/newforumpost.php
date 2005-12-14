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
|     $Source: /cvs_backup/e107_0.7/e107_themes/human_condition/newforumpost.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-12-14 19:28:52 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$NEWFORUMPOSTSTYLE_HEADER = "
<!-- newforumposts -->\n<ul type='square'>\n";

$NEWFORUMPOSTSTYLE_MAIN = "<li><span class='smalltext'>{THREAD} ".LAN_7." {POSTER} [ ".LAN_3.": {VIEWS}, ".LAN_4.": {REPLIES}, ".LAN_5.": {LASTPOST} ]\n</span>\n</li>\n";

$NEWFORUMPOSTSTYLE_FOOTER = "<br /><br />\n</ul><span class='smalltext'>".LAN_6.": <b>{TOTAL_TOPICS}</b> | ".LAN_4.": <b>{TOTAL_REPLIES}</b> | ".LAN_3.": <b>{TOTAL_VIEWS}</b></span>";


?>