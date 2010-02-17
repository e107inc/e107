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
|     $Source: /cvs_backup/e107_0.7/e107_themes/khatru/forum_posted_template.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$FORUMPOLLPOSTED =
BOXOPEN.LAN_133.BOXMAIN."
<table style='width:100%' class='fborder'>
<tr>
<td style='text-align:right; vertical-align:middle; width:20%'>".IMAGE_e."&nbsp;</td>

<td style='vertical-align:middle; width:80%'>

<br /><b>".LAN_413."</b><br />
<span class='defaulttext'><a href='".e_PLUGIN."forum/forum_viewtopic.php?".$thread_id."'>".LAN_414."</a><br />
<a href='".e_PLUGIN."forum/forum_viewforum.php?".$forum_id."'>".LAN_326."</a></span><br /><br />
</td></tr></table>".BOXCLOSE;

$FORUMTHREADPOSTED = 
BOXOPEN.LAN_133.BOXMAIN."
<table style='width:100%' class='fborder'>
<tr>
<td style='text-align:right; vertical-align:middle; width:20%'>".IMAGE_e."&nbsp;</td>

<td style='vertical-align:middle; width:80%'>

<br /><b>".LAN_324."</b><br />
".(defined("F_MESSAGE") ? F_MESSAGE."<br />" : "")."
<span class='defaulttext'><a href='".e_PLUGIN."forum/forum_viewtopic.php?{$iid}.last'>".LAN_325."</a><br />
<a href='".e_PLUGIN."forum/forum_viewforum.php?".$forum_id."'>".LAN_326."</a></span><br /><br />
</td></tr></table>".BOXCLOSE;


$FORUMREPLYPOSTED = 
BOXOPEN.LAN_133.BOXMAIN."
<table style='width:100%' class='fborder'>
<tr>
<td style='text-align:right; vertical-align:middle; width:20%'>".IMAGE_e."&nbsp;</td>

<td style='vertical-align:middle; width:80%'>

<br /><b>".LAN_415."</b><br />
".(defined("F_MESSAGE") ? F_MESSAGE."<br />" : "")."
<span class='defaulttext'><a href='".e_PLUGIN."forum/forum_viewtopic.php?{$iid}.last'>".LAN_325."</a><br />
<a href='".e_PLUGIN."forum/forum_viewforum.php?".$forum_id."'>".LAN_326."</a></span><br /><br />
</td></tr></table>".BOXCLOSE;

?>