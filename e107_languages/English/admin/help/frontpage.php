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
|     $Source: /cvs_backup/e107_0.8/e107_languages/English/admin/help/frontpage.php,v $
|     $Revision: 1.2 $
|     $Date: 2008-06-16 21:10:09 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Front Page Help";
$text = "From this screen you can choose what to display as the front page of your site, the default is news. You can also determine whether
	users are sent to a particular page after logging in.<br /><br />
	The list of rules are scanned in turn, until the class of the current user matches. This then determines the user's front (home) page, and also 
	the page he sees immediately after login.";
$ns -> tablerender($caption, $text);
?>