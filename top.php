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
|     $Source: /cvs_backup/e107_0.7/top.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:12:45 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
if(!e_QUERY){
        $action = "top";
        $subaction = "all";
        $from = 0;
        $view = 10;
}else{
        $tmp = explode(".", e_QUERY);
        $from = $tmp[0];
        $action = $tmp[1];
        $subaction = $tmp[2];
        $view = ($tmp[3] ? $tmp[3] : 10);
}
if($action == "top"){
        define("e_PAGETITLE", ": ".LAN_8);
}elseif($action == "active"){
        define("e_PAGETITLE", ": ".LAN_7);
}


require_once(HEADERF);
if($action == "active"){
        require_once(e_HANDLER."userclass_class.php");

        if($sql -> db_Select_gen("SELECT * FROM ".MPREFIX."forum_t, ".MPREFIX."forum WHERE ".MPREFIX."forum.forum_id=".MPREFIX."forum_t.thread_forum_id AND ".MPREFIX."forum_t.thread_parent=0 ORDER BY ".MPREFIX."forum_t.thread_views DESC LIMIT ".$from.", ".$view."")){
                $text = "<div style='text-align:center'>\n<table style='width:auto' class='fborder'>\n";
                if(!is_object($sql2)){
                        $sql2 = new db;
                }
                if(!is_object($gen)){
                        $gen = new convert;
                }

                $text .= "<tr>
                        <td style='width:5%' class='forumheader'>&nbsp;</td>
                        <td style='width:45%' class='forumheader'>".LAN_1."</td>
                        <td style='width:15%; text-align:center' class='forumheader'>".LAN_2."</td>
                        <td style='width:5%; text-align:center' class='forumheader'>".LAN_3."</td>
                        <td style='width:5%; text-align:center' class='forumheader'>".LAN_4."</td>
                        <td style='width:25%; text-align:center' class='forumheader'>".LAN_5."</td>
                        </tr>\n";

                while($row = $sql -> db_Fetch()){
                        extract($row);
                        if(check_class($forum_class)){
                                $sql2 -> db_Select("forum_t", "*", "thread_parent='$thread_id' ORDER BY thread_views DESC");
                                list($null, $null, $null, $null, $r_datestamp, $null, $r_user) = $sql2 -> db_Fetch();
                                $r_id = substr($r_user, 0, strpos($r_user, "."));
                                $r_name = substr($r_user, (strpos($r_user, ".")+1));

                                if(strstr($r_name, chr(1))){
                                        $tmp = explode(chr(1), $r_name);
                                        $r_name = $tmp[0];
                                }

                                $r_datestamp = $gen->convert_date($r_datestamp, "forum");

                                $post_author_id = substr($thread_user, 0, strpos($thread_user, "."));
                                $post_author_name = substr($thread_user, (strpos($thread_user, ".")+1));
                                if(strstr($post_author_name, chr(1))){
                                        $tmp = explode(chr(1), $post_author_name);
                                        $post_author_name = $tmp[0];
                                }
                                $post_author_name = ($post_author_id != 0 ? "<a href='".e_BASE."user.php?id.$post_author_id'>$post_author_name</a>" : $post_author_name);
                                $replies = $sql2 -> db_Select("forum_t", "*", "thread_parent=$thread_id");

                                $text .= "<tr>
                                <td style='width:5%; text-align:center' class='forumheader3'><img src='".e_IMAGE."forum/new_small.png' alt='' /></td>
                                <td style='width:45%' class='forumheader3'><b><a href='".e_BASE."forum_viewtopic.php?$forum_id.$thread_id'>$thread_name</a></b> <span class='smalltext'>(<a href='".e_BASE."forum_viewforum.php?$forum_id'>$forum_name</a>)</span></td>
                                <td style='width:15%; text-align:center' class='forumheader3'>$post_author_name</td>
                                <td style='width:5%; text-align:center' class='forumheader3'>$thread_views</td>
                                <td style='width:5%; text-align:center' class='forumheader3'>$replies</td>
                                <td style='width:25%; text-align:center' class='forumheader3'>".($replies ? "<b><a href='".e_BASE."user.php?id.$r_id'>$r_name</a></b><br /><span class='smalltext'>$r_datestamp</span>" : "-")."</td>
                                </tr>\n";
                        }
                }

                $text .= "</table>\n</div>";


                //$text = ($pref['nfp_layer'] ? "<div style='border : 0; padding : 4px; width : auto; height : ".$pref['nfp_layer_height']."px; overflow : auto; '>".$text."</div>" : $text);

                $ns -> tablerender(LAN_7, $text, "nfp");
                require_once(e_HANDLER."np_class.php");
                $ftotal = $sql -> db_Select("forum_t", "*", "thread_parent='0'");
                $ix = new nextprev("top.php", $from, $view, $ftotal, "", "active.forum.".$view."");
        }
}
if($action  == "top"){
        require_once(e_HANDLER."level_handler.php");
        define("IMAGE_rank_main_admin_image", ($pref['rank_main_admin_image'] && file_exists(THEME."forum/".$pref['rank_main_admin_image']) ? "<img src='".THEME."forum/".$pref['rank_main_admin_image']."' alt='' />" : "<img src='".e_IMAGE."forum/main_admin.png' alt='' />"));
        define("IMAGE_rank_admin_image", ($pref['rank_admin_image'] && file_exists(THEME."forum/".$pref['rank_admin_image']) ? "<img src='".THEME."forum/".$pref['rank_admin_image']."' alt='' />" : "<img src='".e_IMAGE."forum/admin.png' alt='' />"));
        define("IMAGE_rank_moderator_image", ($pref['rank_moderator_image'] && file_exists(THEME."forum/".$pref['rank_moderator_image']) ? "<img src='".THEME."forum/".$pref['rank_moderator_image']."' alt='' />" : "<img src='".e_IMAGE."forum/moderator.png' alt='' />"));

        if($subaction == "forum" || $subaction == "all"){
                $top_forum_posters = $sql -> db_Select("user", "*", "ORDER BY user_forums DESC LIMIT ".$from.", ".$view."", "no_where");
                $text = "
                <div style='text-align:center'>
                <table style='width:95%' class='fborder'>
                <tr>
                <td style='width:10%; text-align:center' class='forumheader3'>&nbsp;</td>
                <td style='width:50%' class='forumheader3'>".TOP_LAN_1."</td>
                <td style='width:10%; text-align:center' class='forumheader3'>".TOP_LAN_2."</td>
                <td style='width:30%; text-align:center' class='forumheader3'>".TOP_LAN_6."</td>
                </tr>\n";
                $counter=1 + $from;
                while($row = $sql -> db_Fetch()){
                        extract($row);
                        $ldata = get_level($user_id, $user_forums, $user_comments, $user_chats, $user_visits, $user_join, $user_admin, $user_perms, $pref);
                        $text .= "<tr>
                        <td style='width:10%; text-align:center' class='forumheader3'>$counter</td>
                        <td style='width:50%' class='forumheader3'><a href='".e_BASE."user.php?id.$user_id'>$user_name</a></td>
                        <td style='width:10%; text-align:center' class='forumheader3'>$user_forums</td>
                        <td style='width:30%; text-align:center' class='forumheader3'>".(strstr($ldata[0], "LAN") ? $ldata[1] : $ldata[0])."</td>
                        </tr>";
                        $counter++;
                }
                $text .= "</table>\n</div>";
                $ns -> tablerender(TOP_LAN_0, $text);
                if($subaction == "forum"){
                        require_once(e_HANDLER."np_class.php");
                        $ftotal = $sql -> db_Count("user", "(*)", "no_where");
                        $ix = new nextprev("top.php", $from, $view, $ftotal, "Forum Posts", "top.forum.".$view."");
                }
        }


        if($subaction == "comment" || $subaction == "all"){
                $top_forum_posters = $sql -> db_Select("user", "*", "ORDER BY user_comments DESC LIMIT 0, 10", "no_where");
                $text = "
                <div style='text-align:center'>
                <table style='width:95%' class='fborder'>
                <tr>
                <td style='width:10%; text-align:center' class='forumheader3'>&nbsp;</td>
                <td style='width:50%' class='forumheader3'>".TOP_LAN_1."</td>
                <td style='width:10%; text-align:center' class='forumheader3'>".TOP_LAN_4."</td>
                <td style='width:30%; text-align:center' class='forumheader3'>".TOP_LAN_6."</td>
                </tr>\n";
                $counter=1;
                while($row = $sql -> db_Fetch()){
                        extract($row);
                        $ldata = get_level($user_id, $user_forums, $user_comments, $user_chats, $user_visits, $user_join, $user_admin, $user_perms, $pref);
                        $text .= "<tr>
                        <td style='width:10%; text-align:center' class='forumheader3'>$counter</td>
                        <td style='width:50%' class='forumheader3'><a href='".e_BASE."user.php?id.$user_id'>$user_name</a></td>
                        <td style='width:10%; text-align:center' class='forumheader3'>$user_comments</td>
                        <td style='width:30%; text-align:center' class='forumheader3'>".(strstr($ldata[0], "LAN") ? $ldata[1] : $ldata[0])."</td>
                        </tr>";
                        $counter++;
                }
                $text .= "</table>\n</div>";
                $ns -> tablerender(TOP_LAN_3, $text);
        }

        if($subaction == "chat" || $subaction == "all"){
                $top_forum_posters = $sql -> db_Select("user", "*", "ORDER BY user_chats DESC LIMIT 0, 10", "no_where");
                $text = "
                <div style='text-align:center'>
                <table style='width:95%' class='fborder'>
                <tr>
                <td style='width:10%; text-align:center' class='forumheader3'>&nbsp;</td>
                <td style='width:20%' class='forumheader3'>".TOP_LAN_1."</td>
                <td style='width:10%; text-align:center' class='forumheader3'>".TOP_LAN_2."</td>
                <td style='width:30%; text-align:center' class='forumheader3'>".TOP_LAN_6."</td>
                </tr>\n";
                $counter=1;
                while($row = $sql -> db_Fetch()){
                        extract($row);
                        $ldata = get_level($user_id, $user_forums, $user_comments, $user_chats, $user_visits, $user_join, $user_admin, $user_perms, $pref);
                        $text .= "<tr>
                        <td style='width:10%; text-align:center' class='forumheader3'>$counter</td>
                        <td style='width:50%' class='forumheader3'><a href='".e_BASE."user.php?id.$user_id'>$user_name</a></td>
                        <td style='width:10%; text-align:center' class='forumheader3'>$user_chats</td>
                        <td style='width:30%; text-align:center' class='forumheader3'>".(strstr($ldata[0], "LAN") ? $ldata[1] : $ldata[0])."</td>
                        </tr>";
                        $counter++;
                }
                $text .= "</table>\n</div>";
                $ns -> tablerender(TOP_LAN_5, $text);
        }
}
require_once(FOOTERF);
?>