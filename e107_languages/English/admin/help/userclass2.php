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

$caption = "User Class Help";
$text = "You can create or edit/delete existing classes from this page.<br />This is useful for restricting users to certain parts of your site. For example, you could create a class called TEST, then create a forum which only allowed users in the TEST class to access it.";
$ns -> tablerender($caption, $text);
?>