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
|     $Source: /cvs_backup/e107_0.8/e107_languages/English/admin/help/forum.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:34:42 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Forum Help";
$text = "<b>General</b><br />
Use this screen to create or edit your forums<br />
<br />
<b>Parents/Forums</b><br />
A parent is a heading that other forums are displayed under, this makes layout simpler and makes navigating around your forums much simpler for visitors.
<br /><br />
<b>Accessibility</b>
<br />
You can set your forums to only be accessible to certain visitors. Once you have set the 'class' of the visitors you can tick the 
class to only allow those visitors access to the forum. You can set parents or individual forums up in this way.
<br /><br />
<b>Moderators</b>
<br />
Tick the names of the listed administrators to give them moderator status on the forum. The administrator must have forum moderation permissions to be listed here.
<br /><br />
<b>Ranks</b>
<br />
Set your user ranks from here. If the image fields are filled in, images will be used, to use rank names enter the names and make sure the corresponding rank image field is blank.<br />The threshold is the number of points the user needs to gain before his level changes.";
$ns -> tablerender($caption, $text);
unset($text);
?>