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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/login_menu/login_menu.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:12:30 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
if(CORRUPT_COOKIE === TRUE){
        $text = "<div style='text-align:center'>".LOGIN_MENU_L7."<br /><br />\n<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."index.php?logout'>".LOGIN_MENU_L8."</a></div>";
        $ns -> tablerender(LOGIN_MENU_L9, $text);
}

$use_imagecode = ($pref['logcode'] && extension_loaded("gd"));
if($use_imagecode){
        require_once(e_HANDLER."secure_img_handler.php");
        $sec_img = new secure_image;
}

$sql2 = new db;
$text = "";
        if(USER == TRUE || ADMIN == TRUE){
                if(ADMIN == TRUE){
                        $adminfpage = (!$pref['adminstyle'] || $pref['adminstyle'] == "default" ? "admin.php" : $pref['adminstyle'].".php");
                        $text = ($pref['maintainance_flag']==1 ? "<div style='text-align:center'><strong>".LOGIN_MENU_L10."</strong></div><br />" : "" );
                        $text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_ADMIN.$adminfpage."'>".LOGIN_MENU_L11."</a><br />";
                }
                $text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."usersettings.php'>".LOGIN_MENU_L12."</a>\n<br />\n<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."user.php?id.".USERID."'>".LOGIN_MENU_L13."</a>\n<br />\n<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."?logout'>".LOGIN_MENU_L8."</a>";

                if(!$sql -> db_Select("online", "*", "online_ip='$ip' AND online_user_id='0' ")){
                        $sql -> db_Delete("online", "online_ip='$ip' AND online_user_id='0' ");
                }

                $new_total = 0;
                $time = USERLV;
                $new_news = $sql -> db_Select("news", "*", "news_datestamp>$time  ORDER BY news_datestamp DESC");
                while($row = $sql -> db_Fetch()){
                        extract($row);
                                if(!check_class($news_class)){
                                        $new_news = $new_news - 1;
                                }
                }
                $new_total = $new_total + $new_news;
                if(!$new_news){ $new_news = LOGIN_MENU_L26; }


                $new_articles = 0;
                $new_articles = $sql -> db_Select("content", "*", "content_datestamp>$time  ORDER BY content_datestamp DESC");
                while($row = $sql -> db_Fetch()){
                        extract($row);
                                if(!check_class($content_class)){
                                        $new_articles = $new_articles - 1;
                                }
                }
                $new_total = $new_total + $new_articles;
                if(!$new_articles){ $new_articles = LOGIN_MENU_L26; }

                $new_comments=0;
                if($comments = $sql -> db_Select("comments", "*", "comment_datestamp>$time ORDER BY comment_datestamp DESC ")){
                        while($row = $sql -> db_Fetch()){
                                extract($row);
                                switch($comment_type){
                                        case 0:        // news
                                                $sql2 -> db_Select("news", "*", "news_id=$comment_item_id ");
                                                $row = $sql2 -> db_Fetch(); extract($row);
                                                if(check_class($news_class)){
                                                        $new_comments++;
                                                }
                                        break;
                                        case 1:        //        article, review or content page
                                                $sql2 -> db_Select("content", "content_heading, content_type, content_class", "content_id=$comment_item_id ");
                                                $row = $sql2 -> db_Fetch();
                                                extract($row);
                                                if(check_class($content_class)){
                                                        $new_comments++;
                                                }
                                        break;
                                        case 2: //        downloads
                                                $mp = MPREFIX;
                                                $qry = "SELECT download_name, {$mp}download_category.download_category_class FROM {$mp}download LEFT JOIN {$mp}download_category ON {$mp}download.download_category={$mp}download_category.download_category_id WHERE {$mp}download.download_id={$comment_item_id}";
                                                $sql2 -> db_Select_gen($qry);
                                                $row = $sql2 -> db_Fetch();
                                                extract($row);
                                                if(check_class($download_category_class)){
                                                        $new_comments++;
                                                }
                                        break;
                                        case 3: //        faq
                                                $sql2 -> db_Select("faq", "faq_question", "faq_id=$comment_item_id ");
                                                $row = $sql2 -> db_Fetch(); extract($row);
                                                $new_comments++;
                                        break;
                                        case 4:        //        poll comment
                                                $sql2 -> db_Select("poll", "*", "poll_id=$comment_item_id ");
                                                $row = $sql2 -> db_Fetch(); extract($row);
                                                $new_comments++;
                                        break;
                                        case 6:        //        bugtracker
                                                $sql2 -> db_Select("bugtrack", "bugtrack_summary", "bugtrack_id=$comment_item_id ");
                                                $row = $sql2 -> db_Fetch(); extract($row);
                                                $new_comments++;
                                        break;
                                }
                        }
                }
                $handle=opendir(e_PLUGIN);
                        while(false !== ($file = readdir($handle))){
                        if($file != "." && $file != ".." && is_dir(e_PLUGIN.$file)){
                                $plugin_handle=opendir(e_PLUGIN.$file."/");
                                while(false !== ($file2 = readdir($plugin_handle))){
                                        if($file2 == "e_comment.php"){
                                                require_once(e_PLUGIN.$file."/".$file2);
                                                if($comment_type == $e_plug_table){
                                                        $new_comments++;
                                                        break 2;
                                                }
                                        }
                                }
                        }
                }
                $new_total = $new_total + $new_comments;
                if(!$new_comments){ $new_comments = LOGIN_MENU_L26; }
                $display_chats = TRUE;
                if($sql -> db_Select("menus", "menu_class", "menu_name='chatbox_menu' AND menu_location!='0'")){
                        list($menus['menu_class']) = $sql -> db_Fetch();
                        if(check_class($menus['menu_class'])){
                                $new_chat = $sql -> db_Count("chatbox", "(*)", "WHERE cb_datestamp>'".$time."' "); $new_total = $new_total + $new_chat;
                        }else{
                                $display_chats = FALSE;
                        }
                }else{
                        $display_chats = FALSE;
                }
                if(!$new_chat){ $new_chat = ($display_chats ? LOGIN_MENU_L26 : ""); }

                $new_forum = $sql -> db_Select("forum_t", "*", "thread_datestamp>$time ORDER BY thread_datestamp DESC");
                while($row = $sql -> db_Fetch()){
                        extract($row);
                        $sql2 -> db_Select("forum", "*", "forum_id=$thread_forum_id");
                        $row = $sql2 -> db_Fetch(); extract($row);
                        if(!check_class($forum_class)){
                                $new_forum = $new_forum - 1;
                        }
                }
                $new_total = $new_total + $new_forum;
                if(!$new_forum){ $new_forum = LOGIN_MENU_L26; }

                $new_users = $sql -> db_Count("user", "(*)", "WHERE user_join>'".$time."' "); $new_total = $new_total + $new_users;
                if(!$new_users){ $new_users = LOGIN_MENU_L26; }

                $text .= "<br /><br />\n<span class='smalltext'>\n".LOGIN_MENU_L25."<br />
                $new_news ".($new_news == 1 ? LOGIN_MENU_L14 : LOGIN_MENU_L15).",<br />";

                $text .= $new_articles ." ".($new_articles == 1 ? LOGIN_MENU_L29 : LOGIN_MENU_L30).",<br />";



                if($display_chats == TRUE){
                        $text .= $new_chat ." ".($new_chat == 1 ? LOGIN_MENU_L16 : LOGIN_MENU_L17).",<br />";
                }
                $text .= $new_comments ." ".($new_comments == 1 ? LOGIN_MENU_L18 : LOGIN_MENU_L19).",<br />
                $new_forum ".($new_forum == 1 ? LOGIN_MENU_L20 : LOGIN_MENU_L21).",<br />";
                $text .= $new_users." ".($new_users == 1 ? LOGIN_MENU_L22 : LOGIN_MENU_L23).".</span>";
                if($new_total){
                        $text .= "<br /><a href='".e_PLUGIN."list_new/new.php'>".LOGIN_MENU_L24."</a>";
                }
                $caption = (file_exists(THEME."images/login_menu.png") ? "<img src='".THEME."images/login_menu.png' alt='' /> ".LOGIN_MENU_L5." ".USERNAME : LOGIN_MENU_L5." ".USERNAME);
                $ns -> tablerender($caption, $text);
        }else{
                if(LOGINMESSAGE != ""){
                        $text = "<div style='text-align:center'>".LOGINMESSAGE."</div>";
                }
                $text .=  "<div style='text-align:center'>\n<form method='post' action='".e_SELF;
                if(e_QUERY){
                        $text .= "?".e_QUERY;
                }

                $text .= "'><p>\n".LOGIN_MENU_L1."<br />\n
                <input class='tbox' type='text' name='username' size='15' value='' maxlength='30' />\n
                <br />\n".LOGIN_MENU_L2."\n<br />\n
                <input class='tbox' type='password' name='userpass' size='15' value='' maxlength='20' />\n\n<br />\n
                ";
                if($use_imagecode){
                        $text .= "<input type='hidden' name='rand_num' value='".$sec_img -> random_number."'>";
                        $text .= $sec_img -> r_image();
                        $text .= "<br /><input class='tbox' type='text' name='code_verify' size='15' maxlength='20'><br />";
                }
                $text .= "
                <input class='button' type='submit' name='userlogin' value='".LOGIN_MENU_L28."' />\n\n
                <br />\n<input type='checkbox' name='autologin' value='1' /> ".LOGIN_MENU_L6;

                if($pref['user_reg']){
                        $text .= "<br /><br />";
                        if(!$pref['auth_method'] || $pref['auth_method'] == "e107"){
                                $text .= "[ <a href='".e_BASE.e_SIGNUP."'>".LOGIN_MENU_L3."</a> ]<br />[ <a href='".e_BASE."fpw.php'> ".LOGIN_MENU_L4."</a> ]";
                        }
                }
                $text .= "</p>
                </form>
                </div>";
                $caption = (file_exists(THEME."images/login_menu.png") ? "<img src='".THEME."images/login_menu.png' alt='' /> ".LOGIN_MENU_L5 : LOGIN_MENU_L5);
                $ns -> tablerender($caption, $text);
        }

?>