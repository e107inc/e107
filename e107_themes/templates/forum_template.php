<?php

if(!$FORUM_MAIN_START){
	$FORUM_MAIN_START = "<div style='text-align:center'>\n<table style='width:95%' class='fborder' border='1'>\n<tr>\n<td colspan='2' style='width:60%; text-align:center' class='fcaption'>{FORUMTITLE}</td>\n<td style='width:10%; text-align:center' class='fcaption'>{THREADTITLE}</td>\n<td style='width:10%; text-align:center' class='fcaption'>{REPLYTITLE}</td>\n<td style='width:20%; text-align:center' class='fcaption'>{LASTPOSTITLE}</td>\n</tr>";
}
if(!$FORUM_MAIN_PARENT){   
         $FORUM_MAIN_PARENT = " <tr>\n<td colspan='5' class='forumheader'>{PARENTNAME} {PARENTSTATUS}</td>\n</tr>";   
 }   
 if(!$FORUM_MAIN_FORUM){   
         $FORUM_MAIN_FORUM = "<tr>\n<td style='width:5%; text-align:center' class='forumheader2'>{NEWFLAG}</td>\n<td style='width:55%' class='forumheader2'>{FORUMNAME}<br /><span class='smallblacktext'>{FORUMDESCRIPTION}</span></td>\n<td style='width:10%; text-align:center' class='forumheader3'>{THREADS}</td>\n<td style='width:10%; text-align:center' class='forumheader3'>{REPLIES}</td>\n<td style='width:20%; text-align:center' class='forumheader3'><span class='smallblacktext'>{LASTPOST}</span></td>\n</tr>";   
 }   
 if(!$FORUM_MAIN_END){   
         $FORUM_MAIN_END = "</table>\n<div class='spacer'>\n<table style='width:95%' class='fborder'>\n<tr>\n<td colspan='2' style='width:60%' class='fcaption'>{INFOTITLE}</td>\n</tr>\n<tr>\n<td rowspan='4' style='width:5%; text-align:center' class='forumheader3'>{LOGO}</td>\n<td style='width:auto' class='forumheader3'>{USERINFO}</td>\n</tr>\n<tr>\n<td style='width:95%' class='forumheader3'>{INFO}</td>\n</tr><tr>\n<td style='width:95%' class='forumheader3'>{FORUMINFO}</td>\n</tr>\n<tr>\n<td style='width:95%' class='forumheader3'>{USERLIST}</td>\n</tr>\n</table>\n</div>\n<div class='spacer'>\n<table class='fborder' style='width:95%'>\n<tr>\n<td class='forumheader3' style='text-align:center; width:33%'>{ICONKEY}</td>\n<td style='text-align:center; width:33%' class='forumheader3'>{SEARCH}</td>\n<td style='width:33%; text-align:center; vertical-align:middle' class='forumheader3'><span class='smallblacktext'>{PERMS}</span>\n</td>\n</tr>\n</table>\n</div>\n</div>";   
 }   
    
 if(!$FORUM_NEWPOSTS_START){   
         $FORUM_NEWPOSTS_START = "<div style='text-align:center'>\n<div class='spacer'>\n<table style='width:95%' class='fborder'>\n<tr>\n<td style='width:3%' class='fcaption'>&nbsp;</td>\n<td style='width:60%' class='fcaption'>{NEWTHREADTITLE}</td>\n<td style='width:27%; text-align:center' class='fcaption'>{POSTEDTITLE}</td>\n</tr>";   
 }   
    
 if(!$FORUM_NEWPOSTS_MAIN){   
         $FORUM_NEWPOSTS_MAIN .= "<tr>\n<td style='width:3%' class='forumheader3'>{NEWIMAGE}</td>\n<td style='width:60%' class='forumheader3'>{NEWSPOSTNAME}</td>\n<td style='width:27%; text-align:center' class='forumheader3'>{STARTERTITLE}</td>\n</tr>";   
 }   
    
 if(!$FORUM_NEWPOSTS_END){   
         $FORUM_NEWPOSTS_END .= "</table></div></div>";   
 }   
    
 if(!$FORUM_TRACK_START){   
         $FORUM_TRACK_START = "<div style='text-align:center'>\n<div class='spacer'>\n<table style='width:95%' class='fborder'>\n<tr>\n<td colspan='3' style='width:60%' class='fcaption'>{TRACKTITLE}</td>\n</tr>\n";   
    
 if(!$FORUM_TRACK_MAIN){   
         $FORUM_TRACK_MAIN = "<tr>   
         <td style='text-align:center; vertical-align:middle; width:6%'  class='forumheader3'>{NEWIMAGE}</td>   
                         <td style='vertical-align:middle; text-align:left; width:70%'  class='forumheader3'><span class='mediumtext'>{TRACKPOSTNAME}</span></td>   
                         <td style='vertical-align:middle; text-align:center; width:24%'  class='forumheader3'><span class='mediumtext'>{UNTRACK}</td>   
                         </tr>";   
                 }   
         }   
    
 if(!$FORUM_TRACK_END){   
         $FORUM_TRACK_END = "</table>\n</div>\n</div>";   
 } 


?>