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
|     $Source: /cvs_backup/e107_0.7/e107_languages/English/admin/help/newspost.php,v $
|     $Revision$
|     $Date$
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