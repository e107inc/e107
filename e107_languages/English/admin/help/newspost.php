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

$caption = "Newspost Help";
$text = "<b>General</b><br />
Body will be displayed on the main page; extended will be readable by clicking a 'Read More' link.
<br />
<br />
<b>Show title only</b>
<br />
Enable this to show the news title only on front page, with clickable link to full story.
<br /><br />
<b>Activation</b>
<br />
If you set a start and/or end date your news item will only be displayed between these dates.
";
$ns -> tablerender($caption, $text);
?>