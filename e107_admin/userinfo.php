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
|     $Source: /cvs_backup/e107_0.7/e107_admin/userinfo.php,v $
|     $Revision: 1.4 $
|     $Date: 2005-01-18 16:11:32 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("4")){ header("location:".e_BASE."index.php"); exit;}
$e_sub_cat = 'users';
require_once("auth.php");

if(!e_QUERY){
        $text = "<div style=\"text-align:center\">".USFLAN_1."</div>";
        $ns -> tablerender(USFLAN_2, $text);
        require_once("footer.php");
        exit;
}else{
        $ipd = e_QUERY;
}

if(isset($ipd)){
        $obj = new convert;
        $sql -> db_Select("chatbox", "*", "cb_ip='$ipd' LIMIT 0,20");
        $host = gethostbyaddr($ipd);
        $text = USFLAN_3." <b>".$ipd."</b> [ ".USFLAN_4.": $host ]<br />
        <i><a href=\"banlist.php?".$ipd."\">".USFLAN_5."</a></i>

        <br /><br />";
        while(list($cb_id, $cb_nick, $cb_message, $cb_datestamp, $cb_blocked, $cb_ip ) = $sql-> db_Fetch()){
                $datestamp = $obj->convert_date($cb_datestamp, "short");
                $post_author_id = substr($cb_nick, 0, strpos($cb_nick, "."));
                $post_author_name = substr($cb_nick, (strpos($cb_nick, ".")+1));
                $text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" />
<span class=\"defaulttext\"><i>".$post_author_name." (".USFLAN_6.": ".$post_author_id.")</i></span>\n<div class=\"mediumtext\">".$datestamp."<br />".
$cb_message."
</div><br />";
        }

        $text .= "<hr />";

        $sql -> db_Select("comments", "*", "comment_ip='$ipd' LIMIT 0,20");
        while(list($comment_id, $comment_item_id, $comment_author, $comment_author_email, $comment_datestamp, $comment_comment, $comment_blocked,         $comment_ip) = $sql-> db_Fetch()){
                $datestamp = $obj->convert_date($comment_datestamp, "short");
                $post_author_id = substr($comment_author, 0, strpos($comment_author, "."));
                $post_author_name = substr($comment_author, (strpos($comment_author, ".")+1));
                $text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" />
<span class=\"defaulttext\"><i>".$post_author_name." (".USFLAN_6.": ".$post_author_id.")</i></span>\n<div class=\"mediumtext\">".$datestamp."<br />".
$comment_comment."</div><br />";
        }

}

$ns -> tablerender(USFLAN_7, $text);

require_once("footer.php");
?>