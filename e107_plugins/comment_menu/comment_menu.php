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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/comment_menu/comment_menu.php,v $
|     $Revision: 1.4 $
|     $Date: 2004-12-13 13:20:43 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
if(!is_object($aj)){ $aj = new textparse; }
if($cache = $e107cache->retrieve("newcomments")){
        $text = $aj -> formtparev($cache);
}else{


        if(!$sql -> db_Select("comments", "*", "comment_id ORDER BY comment_datestamp DESC LIMIT 0, ".$menu_pref['comment_display'])){
                $text = "<span class='mediumtext'>".CM_L1."</span>";
        }else{
                $text = "<span class='smalltext'>";
                $sql2 = new db;
                while($row = $sql-> db_Fetch()){
                        extract($row);

                        $poster = substr($comment_author, (strpos($comment_author, ".")+1));
                        $gen = new convert;
                        $datestamp = $gen->convert_date($comment_datestamp, "short");

                        $comment_comment = $aj -> tpa($comment_comment);

                        if($pref['cb_linkreplace']){
                                $comment_comment .= " ";
                                $comment_comment = preg_replace("#\>(.*?)\</a\>[\s]#si", ">".$pref['cb_linkc']."</a> ", $comment_comment);
                                $comment_comment = $aj -> tpa(strip_tags($comment_comment));
                        }

                        if(!eregi("<a href|<img|&#", $thread_thread)){
                                $message_array = explode(" ", $comment_comment);
                                for($i=0; $i<=(count($message_array)-1); $i++){
                                        if(strlen($message_array[$i]) > 20){
                                                $message_array[$i] = preg_replace("/([^\s]{20})/", "$1<br />", $message_array[$i]);
                                        }
                                }
                                $comment_comment = implode(" ", $message_array);
                        }
                        if(strlen($comment_comment) > $menu_pref['comment_characters']){
                                $comment_comment = substr($comment_comment, 0, $menu_pref['comment_characters']).$menu_pref['comment_postfix'];
                        }

                        if($comment_type == "0"){
                                $sql2 -> db_Select("news", "news_title, news_class", "news_id = $comment_item_id");
                                $row = $sql2 -> db_Fetch(); extract($row);
                                if(!$news_class){
                                        $text .= "<img src='".THEME."images/bullet2.gif' alt='' /> <a href='".e_BASE."comment.php?comment.news.$comment_item_id'><b>".$poster."</b> on ".$datestamp."</a>";
                                        if($menu_pref['comment_title']) {
                                                $text .= "<br /> [ Re: <i>$news_title</i> ]";
                                        }
                                        $text .= "<br />".($comment_blocked ? CM_L2 : $comment_comment)."<br /><br />";
                                }
                        }
                        if($comment_type == "1"){
                                $sql2 -> db_Select("content", "*", "content_id=$comment_item_id");
                                $row = $sql2 -> db_Fetch(); extract($row);
                                if($content_type == 0){
                                        $tmp = "<img src='".THEME."images/bullet2.gif' alt='' /> <a href='".e_BASE."content.php?article.$comment_item_id'><b>".$poster."</b> on ".$datestamp."</a>";
                                }else if($content_type == 3){
                                        $tmp = "<img src='".THEME."images/bullet2.gif' alt='' /> <a href='".e_BASE."content.php?review.$comment_item_id'><b>".$poster."</b> on ".$datestamp."</a>";
                                }else if($content_type == 1){
                                        $tmp = "<img src='".THEME."images/bullet2.gif' alt='' /> <a href='".e_BASE."content.php?content.$comment_item_id'><b>".$poster."</b> on ".$datestamp."</a>";
                                }
                                if($menu_pref['comment_title']) {
                                        $sql2 -> db_Select("content", "content_heading", "content_id=$comment_item_id");
                                        list($article_title) = $sql2->db_Fetch();
                                        $tmp .= "<br /> [ Re: <i>$article_title</i> ]";
                                }
                                $tmp .= "<br />".($comment_blocked ? CM_L2 : $comment_comment)."<br /><br />";

                                if(check_class($content_class)){
                                        $text .=$tmp;
                                }

                        }
                        if($comment_type_ == "2"){
                                        //This code is not tested... [edwin]
                                $text .= "<img src='".THEME."images/bullet2.gif' alt='' /> <a href='".e_BASE."download_comment.php?comment.news.$comment_item_id_.'>download - <b>".$poster."</b> on ".$datestamp."</a><br />".($comment_blocked ? CM_L2 : $comment_comment)."<br /><br />";
                        }
                }

                $text = "</span>".preg_replace("/\<br \/\>$/", "", $text);
                if($pref['cachestatus']){
                        $cache = $aj -> formtpa($text, "admin");
                        $e107cache->set("newcomments", $cache);
                }
        }
}
$ns -> tablerender($menu_pref['comment_caption'], $text, 'comment');
?>
