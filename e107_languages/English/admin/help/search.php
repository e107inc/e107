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

$text = "If your MySQL server version supports it you can switch 
to the MySQL sort method which is faster than the PHP sort method. See preferences.<br /><br />
If your site includes Ideographic languages such as Chinese and Japanese you must 
use the PHP sort method and switch whole word matching off.";
$ns -> tablerender("Search Help", $text);
?>