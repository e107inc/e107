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
|     $Source: /cvs_backup/e107_0.7/email.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:12:45 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);

$qs = explode(".", e_QUERY);
if($qs[0] == ""){ header("location:".e_BASE."index.php"); exit;}
$table = $qs[0];
$id = $qs[1];
$type = ($table == "news" ? "news item" : "article");

$comments = textparse::tpj($_POST['comment'],TRUE);
$author = textparse::tpj($_POST['author_name'],TRUE);
$email_send = check_email($_POST['email_send']);
if(IsSet($_POST['emailsubmit'])){
        if(!$email_send){
                 $error .= LAN_106;
         }
         if($comments == ""){
                 $message = LAN_188." ".SITENAME." (".SITEURL.")";
                if(USER == TRUE){
                        $message .= "\n\n".LAN_email_1." ".USERNAME;
                }else{
                        $message .= "\n\n".LAN_email_1." ".$author;
                }
         }
        $ip = getip();
        $message .= "\n\n".LAN_email_2." ".$ip."\n\n";

        if($table == "news"){
                $sql -> db_Select("news", "*", "news_id='$id' ");
                list($news_id, $news_title, $news_body, $news_extended, $news_datestamp, $news_author, $news_source, $news_url, $news_category, $news_allow_comments) = $sql-> db_Fetch();
                $message .= $comments."\n\n".$news_title."\n".$news_body."\n".$news_extended."\n\n".SITEURL.e_BASE."comment.php?comment.news.".$id;
        }else{
                $row = $sql -> db_Fetch();
                extract($row);
                $message .= $comments."\n\n".SITEURL.e_BASE."content.php?".$id."\n\n".$content_heading."\n".$content_subheading."\n".$content_content."\n\n";
        }
        if($error == ""){
                require_once(e_HANDLER."mail.php");
                if(sendemail($email_send, LAN_email_3.SITENAME, $message)){
                        $text = "<div style='text-align:center'>".LAN_10." ".$email_send."</div>";
                }else{
                        $text = "<div class='center'>".LAN_9."</div>";
                }
                $ns -> tablerender(LAN_11, $text);
        }else{
                $ns -> tablerender(LAN_12, "<div style='text-align:center'>".$error."</div>");
        }
}

$text = "<form method='post' action='".e_SELF."?$table.$id'>\n
<table class='defaulttable'>";

if(USER != TRUE){
        $text .= "<tr>
<td style='width:20%'>".LAN_7."</td>
<td style='width:80%'>
<input class='tbox' type='text' name='author_name' size='60' value='$author' maxlength='100' />
</td>
</tr>";
}
$text .= "<tr>
<td style='width:20%'>".LAN_8."</td>
<td style='width:80%'>
<textarea class='tbox' name='comment' cols='70' rows='4'>".($type == "news" ? LAN_188 : LAN_189)." ".SITENAME." (".SITEURL.")";
if(USER == TRUE){
        $text .= "\n\n".LAN_email_1." ".USERNAME;
}

$text .= "</textarea>
</td>
</tr>

<tr>
<td style='width:20%'>".LAN_187."</td>
<td style='width:80%'>
<input class='tbox' type='text' name='email_send' size='60' value='$email_send' maxlength='100' />
</td>
</tr>

<tr style='vertical-align:top'>
<td style='width:20%'></td>
<td style='width:80%'>
<input class='button' type='submit' name='emailsubmit' value='".($type == "news" ? LAN_186 : LAN_185)."' />
</td>
</tr>
</table>
</form>";

$ns -> tablerender(($type == "news" ? LAN_6 : LAN_5), $text);



require_once(FOOTERF);
?>