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
+----------------------------------------------------------------------------+
*/

$FORUMPOLLPOSTED =
"<table style='width:100%' class='fborder'>
<tr>
<td class='fcaption' colspan='2'>".LAN_133."</td>
</tr><tr>
<td style='text-align:right; vertical-align:center; width:20%' class='forumheader2'>".IMAGE_e."&nbsp;</td>
<td style='vertical-align:center; width:80%' class='forumheader2'>
<br />".LAN_413."<br />
<span class='defaulttext'><a class='forumlink' href='".e_PLUGIN."forum/forum_viewtopic.php?".$thread_id."'>".LAN_414."</a><br />
<a class='forumlink' href='".e_PLUGIN."forum/forum_viewforum.php?".$forum_id."'>".LAN_326."</a></span><br /><br />
</td></tr></table>";

$FORUMTHREADPOSTED = "
<table style='width:100%' class='fborder'>
<tr>
<td class='fcaption' colspan='2'>".LAN_133."</td>
</tr><tr>
<td style='text-align:right; vertical-align:center; width:20%' class='forumheader2'>".IMAGE_e."&nbsp;</td>
<td style='vertical-align:center; width:80%' class='forumheader2'>
<br />".LAN_324."<br />
<span class='defaulttext'><a href='".e_PLUGIN."forum/forum_viewtopic.php?{$iid}.last'>".LAN_325."</a><br />
<a href='".e_PLUGIN."forum/forum_viewforum.php?".$forum_id."'>".LAN_326."</a></span><br /><br />
</td></tr></table>";


$FORUMREPLYPOSTED = "
<table style='width:100%' class='fborder'>
<tr>
<td class='fcaption' colspan='2'>".LAN_133."</td>
</tr><tr>
<td style='text-align:right; vertical-align:center; width:20%' class='forumheader2'>".IMAGE_e."&nbsp;</td>
<td style='vertical-align:center; width:80%' class='forumheader2'>
<br />".LAN_415."<br />
<span class='defaulttext'><a href='".e_PLUGIN."forum/forum_viewtopic.php?{$iid}.last'>".LAN_325."</a><br />
<a href='".e_PLUGIN."forum/forum_viewforum.php?".$forum_id."'>".LAN_326."</a></span><br /><br />
</td></tr></table>";

?>