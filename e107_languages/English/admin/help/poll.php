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
|     $Source: /cvs_backup/e107_0.8/e107_languages/English/admin/help/poll.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:34:43 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "You set polls/surveys from this page, just type in the poll title and options, preview it and if all looks ok tick the box to make it active.<br /><br />
To see the poll, go to your menus page and make sure poll_menu is activated.";

$ns -> tablerender("Polls", $text);
?>