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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/pm_menu/help.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-01-27 19:53:13 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
$text = "
	<b>&raquo;</b> <u>Plugin Title:</u>
	Sets title displayed in the menu caption.  Set it to PMLAN_PM to use the language file.
	<br /><br />
	<b>&raquo;</b> <u>Restrict PM to:</u>
	Allows you to restrict the use of the PM system.
	<br />As userclasses are added to the system, this list will auto-populate.  The 'Everyone' and
	'Members only' options have the same functionality.
	<br /><br />
	<b>&raquo;</b> <u>Send email notifications:</u>
	If set to yes, it will email the recipient of a PM that a PM has been delivered.
	";
$ns->tablerender("PM Help", $text);
?>