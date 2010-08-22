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

$text = "You can separate your news items into different categories, and allow visitors to display only the news items in those categories. <br /><br />Upload your news icon images either ".e_THEME."-yourtheme-/images/ or themes/shared/newsicons/.";
$ns -> tablerender("News Category Help", $text);
?>