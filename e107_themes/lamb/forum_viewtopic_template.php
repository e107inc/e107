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
|     $Source: /cvs_backup/e107_0.7/e107_themes/lamb/forum_viewtopic_template.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-01-30 15:59:25 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
        $FORUMSTART = "
<table style='width:100%' class='nforumholder' cellpadding=0 cellspacing=0>
<tr>	
<td  colspan='2' class='nforumcaption'>{BACKLINK}</td>
</tr>
<tr>
<td class='nforumcaption2' colspan='2'>
<table cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='smalltext'>{NEXTPREV}</td>
<td style='text-align:right'>&nbsp;{TRACK}&nbsp;</td>
</tr>
</table>
</td>
</tr>
</table>
<br />

<table style='width:100%'>
<tr>
<td style='width:80%'><div class='mediumtext'><img src='".e_IMAGE."forum/e.png' style='vertical-align:middle' /> <b>{THREADNAME}</b></div><br />{GOTOPAGES}</td>
<td style='width:20%; vertical-align:bottom;'>{BUTTONS}</td>
</tr>
</table>


<table style='width:100%' class='nforumholder' cellpadding=0 cellspacing=0>
<tr>
<td style='width:20%; text-align:center' class='nforumcaption2'>\n".LAN_402."\n</td>\n<td style='width:80%; text-align:center' class='nforumcaption2'>\n".LAN_403."\n</td>
</tr>
</table>";
       $FORUMTHREADSTYLE = "
<div class='spacer'>
<table style='width:100%' class='nforumholder' cellpadding=0 cellspacing=0>
<tr>
<td class='nforumcaption3' style='vertical-align:middle; width:20%;'>\n{NEWFLAG}\n{POSTER}\n</td>
<td class='nforumcaption3' style='vertical-align:middle; width:80%;'>
<table cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='smallblacktext'>\n{THREADDATESTAMP}\n</td>
<td style='text-align:right'>\n{REPORTIMG}\n{EDITIMG}\n{QUOTEIMG}\n</td>
</tr>
</table>
</td>
</tr>	
<tr>
<td class='nforumthread' style='vertical-align:top'>\n{AVATAR}\n<span class='smalltext'>\n{LEVEL}\n{MEMBERID}\n{JOINED}\n{POSTS}\n</span>\n</td>
<td class='nforumthread' style='vertical-align:top'>\n{POST}\n{SIGNATURE}\n</td>
</tr>		
<tr>
<td class='nforumthread2'>\n<span class='smallblacktext'>\n{TOP}\n</span>\n</td>
<td class='nforumthread2' style='vertical-align:top'>
<table cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td>\n{PROFILEIMG}\n {EMAILIMG}\n {WEBSITEIMG}\n {PRIVMESSAGE}\n</td>
<td style='text-align:right'>\n{MODOPTIONS}\n</td>
</tr>
</table>
</td>
</tr>	
</table>
</div>";
        $FORUMEND = "
<div class='spacer'>
<table style='width:100%' class='nforumholder' cellpadding=0 cellspacing=0>
<tr>
<td style='width:50%; text-align:left; vertical-align:top' class='nforumthread'><b>{MODERATORS}</b><br />{FORUMJUMP}</td>
<td style='width:50%; text-align:right; vertical-align:top' class='nforumthread'>{BUTTONS}</td>
</tr>
</table>
</div>
<div class='spacer'>
<table style='width:100%' class='nforumholder' cellpadding=0 cellspacing=0>
<tr>
<td style='text-align:center' class='nforumthread2'>{QUICKREPLY}</td>
</tr>
</table>
<div class='nforumdisclaimer' style='text-align:center'>Powered by <b>e107 Forum System</b></div>
</div>";
       $FORUMREPLYSTYLE = "
<div class='spacer'>
<table style='width:100%' class='nforumholder' cellpadding=0 cellspacing=0>
<tr>
<td class='nforumreplycaption' style='vertical-align:middle; width:20%;'>\n{NEWFLAG}\n{POSTER}\n</td>
<td class='nforumreplycaption' style='vertical-align:middle; width:80%;'>
<table cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='smallblacktext'>\n{THREADDATESTAMP}\n</td>
<td style='text-align:right'>\n{REPORTIMG}\n{EDITIMG}\n{QUOTEIMG}\n</td>
</tr>
</table>
</td>
</tr>	
<tr>
<td class='nforumreply' style='vertical-align:top'>\n{AVATAR}\n<span class='smalltext'>\n{LEVEL}\n{MEMBERID}\n{JOINED}\n{POSTS}\n</span>\n</td>
<td class='nforumreply' style='vertical-align:top'>\n{POST}\n{SIGNATURE}\n</td>
</tr>		
<tr>
<td class='nforumreply2'>\n<span class='smallblacktext'>\n{TOP}\n</span>\n</td>
<td class='nforumreply2' style='vertical-align:top'>
<table cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td>\n{PROFILEIMG}\n {EMAILIMG}\n {WEBSITEIMG}\n {PRIVMESSAGE}\n</td>
<td style='text-align:right'>\n{MODOPTIONS}\n</td>
</tr>
</table>
</td>
</tr>		
</table>
</div>";

?>