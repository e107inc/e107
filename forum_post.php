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
|     $Source: /cvs_backup/e107_0.7/forum_post.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:12:45 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

require_once("class2.php");
if(IsSet($_POST['fjsubmit'])){
        header("location:".e_BASE."forum_viewforum.php?".$_POST['forumjump']);
        exit;
}
require_once(e_HANDLER."ren_help.php");
require_once(e_HANDLER."mail.php");
$gen = new convert;
$aj = new textparse();
$fp = new floodprotect;

if(!e_QUERY){
        header("Location:".e_BASE."forum.php");
        exit;
}else{
        $tmp = explode(".", e_QUERY);
        $action = $tmp[0]; $forum_id = $tmp[1]; $thread_id = $tmp[2];
}

// check if user can post to this forum ...

if($sql -> db_Select("forum", "*", "forum_id=$forum_id")){
        $row = $sql -> db_Fetch(); extract($row);
        if(!check_class($forum_class)){
                $ns -> tablerender(LAN_20, "<div style='text-align:center'>".LAN_399."</div>");
                require_once(FOOTERF);
                exit;
        }
}

//        end

$ip = getip();
if($sql -> db_Select("tmp", "*",  "tmp_ip='$ip' ")){
        $row = $sql -> db_Fetch();
        $tmp = explode("^", $row['tmp_info']);
        $action = $tmp[0];
        $anonname = $tmp[1];
        $subject = $tmp[2];
        $post = $tmp[3];
        $sql -> db_Delete("tmp", "tmp_ip='$ip' ");
}

if(ANON == FALSE && USER == FALSE){
        $text .= "<div style='text-align:center'>".LAN_45." <a href='".e_SIGNUP."'>".LAN_411."</a> ".LAN_412."</div>";
        $ns -> tablerender(LAN_20, $text);
        require_once(FOOTERF);
        exit;
}

$sql -> db_Select("forum", "*", "forum_id='".$forum_id."' ");
$row = $sql-> db_Fetch(); extract($row);
$fname = $row['forum_name'];
if($forum_class == e_UC_READONLY && !ADMIN){
        $text .= "<div style='text-align:center'>".LAN_398."</div>";
        $ns -> tablerender(LAN_20, $text);
        require_once(FOOTERF);
        exit;
}


if($thread_id){
        $sql -> db_Select("forum_t", "*", "thread_id='".$thread_id."' ");
        $row = $sql-> db_Fetch(); extract($row);
}

if($action != "nt" && !$thread_active){
        $ns -> tablerender(LAN_20, "<div style='text-align:center'>".LAN_397."</div>");
        require_once(FOOTERF);
        exit;
}
if($action == "cp"){
        define("e_PAGETITLE", LAN_01." / ".$fname." / ".$row['thread_name']);
}else{
        define("e_PAGETITLE", LAN_01." / ".$fname." / ".($action == "rp" ? LAN_02.$row['thread_name'] : LAN_03));
}
require_once(HEADERF);
// -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

if(IsSet($_POST['addoption']) && $_POST['option_count'] < 10){
        $_POST['option_count']++;
        $anonname = $aj -> formtpa($_POST['anonname']);
        $subject = $aj -> formtpa($_POST['subject']);
        $post = $aj -> formtpa($_POST['post']);
}

if(IsSet($_POST['submitpoll'])){
        require_once(e_HANDLER."poll_class.php");
        $poll = new poll;
        $poll -> submit_poll(0, $_POST['poll_title'], $_POST['poll_option'], $_POST['activate'], 0, $forum_id, "forum");

        require_once(HEADERF);
        echo "<table style='width:100%' class='fborder'>
        <tr>
        <td class='fcaption' colspan='2'>".LAN_133."</td>
        </tr><tr>
        <td style='text-align:right; vertical-align:center; width:20%' class='forumheader2'><img src='".e_IMAGE."forum/e.png' alt='' />&nbsp;</td>
        <td style='vertical-align:center; width:80%' class='forumheader2'>
        <br />".LAN_384."<br />

        <span class='defaulttext'><a class='forumlink' href='".e_BASE."forum_viewtopic.php?".$thread_forum_id.".".$thread_id."'>".LAN_325."</a><br />
        <a class='forumlink' href='".e_BASE."forum_viewforum.php?".$forum_id."'>".LAN_326."</a></span><br /><br />
        </td></tr></table>";
        require_once(FOOTERF);
        exit;
}


if(IsSet($_POST['fpreview'])){
        if(USER ? $poster = USERNAME : $poster = ($_POST['anonname'] ? $_POST['anonname'] : LAN_311));
        $postdate = $gen->convert_date(time(), "forum");

        if(!$pref['html_post']){
                $tsubject = str_replace("<", "&lt;", $tsubject);str_replace(">", "&gt;", $tsubject);
                $tpost = str_replace("<", "&lt;", $tpost);str_replace(">", "&gt;", $tpost);
        }

        $tsubject = $aj -> tpa($_POST['subject']);
        $tpost = $aj -> tpa($_POST['post']);

        if($_POST['poll_title'] != ""){
                require_once(e_HANDLER."poll_class.php");
                $poll = new poll;
                $poll -> render_poll($_POST['existing'], $_POST['poll_title'], $_POST['poll_option'], array($votes), "preview", "forum");
                $count=0;
                while($_POST['poll_option'][$count]){
                        $_POST['poll_option'][$count] = $aj -> formtpa($_POST['poll_option'][$count]);
                        $count++;
                }
                $_POST['poll_title'] = $aj -> formtpa($_POST['poll_title']);
        }

        $text = "<div style='text-align:center'>
        <table style='width:95%' class='fborder'>
        <tr>
        <td colspan='2' class='fcaption' style='vertical-align:top'>".LAN_323;
        $text .= ($action != "nt" ? "</td>" : " ( ".LAN_62.$tsubject." )</td>");
        $text .= "<tr>
        <td class='forumheader3' style='width:20%' style='vertical-align:top'><b>".$poster."</b></td>
        <td class='forumheader3' style='width:80%'>
        <div class='smallblacktext' style='text-align:right'><img src='".e_IMAGE."forum/post.png' alt='' /> ".LAN_322.$postdate."</div>".$tpost."</td>
        </tr>
        </table>
        </div>";

        $ns -> tablerender(LAN_323, $text);


        $anonname = ($_POST['anonname'] ? $aj -> formtpa($_POST['anonname'], "public") : LAN_311);
        $post = (ADMIN ? $aj -> formtpa($_POST['post']) : $aj -> formtpa($_POST['post'], "public"));
        $subject = (ADMIN ? $aj -> formtpa($_POST['subject']) : $aj -> formtpa($_POST['subject'], "public"));

        if($action == "edit"){
                if($_POST['subject'] ? $action = "nt" : $action = "reply");
                $eaction = TRUE;
        }else if($action == "quote"){
                $action = "reply";
                $eaction = FALSE;
        }
}

if(IsSet($_POST['newthread'])){

        if(trim(chop($_POST['subject'])) == "" || trim(chop($_POST['post'])) == ""){
                message_handler("ALERT", 5);
        }else{
                if($fp -> flood("forum_t", "thread_datestamp") == FALSE){
                        header("location: ".e_BASE."index.php");
                        exit;
                }

                if(USER){
                        $user = USERID.".".USERNAME;
                }else{
                        if(!$user = getuser($_POST['anonname'])){
                                require_once(HEADERF);
                                $ns -> tablerender(LAN_20, LAN_310);
                                $ip = getip();
                                $sql -> db_Delete("tmp", "tmp_ip='$ip' ");
                                $tmpdata = "newthread^".$_POST['anonname']."^".$_POST['subject']."^".$_POST['post'];
                                $sql -> db_Insert("tmp", "'$ip', '".time()."', '$tmpdata' ");
                                loginf();
                                require_once(FOOTERF);
                                exit;
                        }
                }

                if($file_userfile['error'] != 4){
                        require_once(e_HANDLER."upload_handler.php");
                        if($uploaded = file_upload(e_FILE."public/", "attachment")){
                                $_POST['post'] .= "\n\n".(strstr($uploaded[0]['type'], "image") ? "[img]".e_FILE."public/".$uploaded[0]['name']."[/img] \n<span class='smalltext'>[ ".$uploaded[0]['name']." ]</span>" : "[file=".e_FILE."public/".$uploaded[0]['name']."]".$uploaded[0]['name']."[/file]");
                        }
                }

                $post = (ADMIN ? $aj -> formtpa($_POST['post']) : $aj -> formtpa($_POST['post'], "public"));
                $subject = (ADMIN ? $aj -> formtpa($_POST['subject']) : $aj -> formtpa($_POST['subject'], "public"));

                if($sql -> db_Select("forum_t", "*", "thread_thread='$post'")){
                        $ns -> tablerender(LAN_20, "<div style='text-align:center'>".LAN_389."</div>");
                        require_once(FOOTERF);
                        exit;
                }

                $email_notify = ($_POST['email_notify'] ? 99 : 1);
                if(strstr($user, chr(1))){
                        $tmp = explode(chr(1), $user);
                        $lastpost = $tmp[0].".".time();
                }else{
                        $lastpost = $user.".".time();
                }

                if($_POST['poll_title'] != "" && $_POST['poll_option'][0] != "" && $_POST['poll_option'][1] != ""){
                        $subject = "[".LAN_402."] ".$subject;
                }

                if($_POST['threadtype'] == 2){
                        $subject = "[".LAN_403."] ".$subject;
                }else if($_POST['threadtype'] == 1){
                        $subject = "[".LAN_404."] ".$subject;
                }

                $iid = $sql -> db_Insert("forum_t", "0, '".$subject."', '".$post."', '$forum_id', '".time()."', '0', '$user', 0, $email_notify, '".time()."', '".$_POST['threadtype']."' ");
                $sql -> db_Update("forum", "forum_threads=forum_threads+1, forum_lastpost='$lastpost' WHERE forum_id='$forum_id' ");
                $sql -> db_Update("user", "user_forums=user_forums+1, user_viewed='".USERVIEWED.".{$iid}.' WHERE user_id='".USERID."' ");

                $sql -> db_Select("forum_t", "*", "thread_thread='$post' ");
                $row = $sql -> db_Fetch(); extract($row);

                if($_POST['poll_title'] != "" && $_POST['poll_option'][0] != "" && $_POST['poll_option'][1] != ""){
                        require_once(e_HANDLER."poll_class.php");
                        $poll = new poll;
                        $poll -> submit_poll(0, $_POST['poll_title'], $_POST['poll_option'], $_POST['activate'], 0, $thread_id, "forum");
                }
                                if($pref['forum_redirect']){
                                        redirect("".e_BASE."forum_viewtopic.php?".$thread_forum_id.".".$thread_id.".".$pages."#$iid");
                                }else{

                                        require_once(HEADERF);
                                        echo "<table style='width:100%' class='fborder'>
                                        <tr>
                                        <td class='fcaption' colspan='2'>".LAN_133."</td>
                                        </tr><tr>
                                        <td style='text-align:right; vertical-align:center; width:20%' class='forumheader2'><img src='".e_IMAGE."forum/e.png' alt='' />&nbsp;</td>
                                        <td style='vertical-align:center; width:80%' class='forumheader2'>
                                        <br />".LAN_324."<br />
                                        <span class='defaulttext'><a href='".e_BASE."forum_viewtopic.php?".$thread_forum_id.".".$thread_id."#$iid'>".LAN_325."</a><br />
                                        <a href='".e_BASE."forum_viewforum.php?".$forum_id."'>".LAN_326."</a></span><br /><br />
                                        </td></tr></table>";
                                        clear_cache("newforumposts");
                                        require_once(FOOTERF);
                                        exit;
                                }
        }
}

if(IsSet($_POST['reply'])){
        if(!$_POST['post']){
                message_handler("ALERT", 5);
        }else{
                if($fp -> flood("forum_t", "thread_datestamp") == FALSE){
                        header("location: ".e_BASE."index.php");
                        exit;
                }

                if(USER){
                        $user = USERID.".".USERNAME;
                }else{
                        if(!$user = getuser($_POST['anonname'])){
                                require_once(HEADERF);
                                $ns -> tablerender(LAN_20, LAN_310);
                                $tmpdata = "reply.".$_POST['anonname'].".".$_POST['subject'].".".$_POST['post'];
                                $sql -> db_Insert("tmp", "'$ip', '".time()."', '$tmpdata' ");
                                loginf();
                                require_once(FOOTERF);
                                exit;
                        }
                }

                if($file_userfile['error'] != 4){
                        require_once(e_HANDLER."upload_handler.php");
                        if($uploaded = file_upload(e_FILE."public/", "attachment")){
                                $_POST['post'] .= "\n\n".(strstr($uploaded[0]['type'], "image") ? "[img]".e_FILE."public/".$uploaded[0]['name']."[/img] \n<span class='smalltext'>[ ".$uploaded[0]['name']." ]</span>" : "[file=".e_FILE."public/".$uploaded[0]['name']."]".$uploaded[0]['name']."[/file]");
                        }
                }

                $post = (ADMIN ? $aj -> formtpa($_POST['post']) : $aj -> formtpa($_POST['post'], "public"));
                $subject = (ADMIN ? $aj -> formtpa($_POST['subject']) : $aj -> formtpa($_POST['subject'], "public"));

                if($sql -> db_Select("forum_t", "*", "thread_thread='$post' AND thread_id='$thread_id' ")){
                        $ns -> tablerender(LAN_20, "<div style='text-align:center'>".LAN_389."</div>");
                        require_once(FOOTERF);
                        exit;
                }

                if(strstr($user, chr(1))){
                        $tmp = explode(chr(1), $user);
                        $lastpost = $tmp[0].".".time();
                }else{
                        $lastpost = $user.".".time();
                }

                $sql -> db_Select("forum_t", "*", "thread_id='$thread_id' ");
                $row = $sql-> db_Fetch(); extract($row);
                if($thread_parent){
                        $thread_id = $thread_parent;
                }

                $iid = $sql -> db_Insert("forum_t", "0, '', '".$post."', '$forum_id', '".time()."', '".$thread_id."', '$user', 0, 1, '".time()."', 0 ");
                $sql -> db_Update("forum_t",  "thread_lastpost='".time()."' WHERE thread_id='$thread_id' ");
                $sql -> db_Update("forum", "forum_replies=forum_replies+1, forum_lastpost='$lastpost' WHERE forum_id='$forum_id' ");
                $sql -> db_Update("user", "user_forums=user_forums+1,user_viewed='".USERVIEWED.$iid.".' WHERE user_id='".USERID."' ");

                if($thread_active == 99){
                        $datestamp = $gen->convert_date(time(), "long");
                        $email_name = substr($thread_user, (strpos($thread_user, ".")+1));
                        $sql -> db_Select("user", "*", "user_name='$email_name' ");
                        $row = $sql -> db_Fetch(); extract($row);
                        $poster = ereg_replace("^[0-9]+\.", "", $user);
                        $message = LAN_384.SITENAME.".\n\n".LAN_382.$gen->convert_date(time(), "long")."\n".LAN_94.": ".$poster."\n\n".LAN_385.stripslashes($_POST['post'])."\n\n".LAN_383."\n\n".SITEURL."forum_viewtopic.php?".$forum_id.".".$thread_id;
                        sendemail($user_email, $pref['forum_eprefix']." '".$thread_name."', ".LAN_381.SITENAME, $message);
                }
                if($sql -> db_Select("user", "*", "user_realm REGEXP('-".$thread_id."-') ")){
                        while($row = $sql -> db_Fetch()){
                                extract($row);
                                $poster = ereg_replace("^[0-9]+\.", "", $user);
                                $message = LAN_385.SITENAME.".\n\n".LAN_382.$gen->convert_date(time(), "long")."\n".LAN_94.": ".$poster."\n\n".LAN_385.stripslashes($_POST['post'])."\n\n".LAN_383."\n\n".SITEURL."forum_viewtopic.php?".$forum_id.".".$thread_id;
                                sendemail($user_email, $pref['forum_eprefix']." '".$thread_name."', ".LAN_381.SITENAME, $message);
                        }

                }


                $replies = $sql -> db_Count("forum_t", "(*)", "WHERE thread_parent='".$thread_id."'");
                $pref['forum_postspage'] = ($pref['forum_postspage'] ? $pref['forum_postspage'] : 10);
                $pages = ((ceil($replies/$pref['forum_postspage']) -1) * $pref['forum_postspage']);
                                if($pref['forum_redirect']){
                                        redirect("".e_BASE."forum_viewtopic.php?".$thread_forum_id.".".$thread_id.".".$pages."#$iid");
                                }else{

                                        require_once(HEADERF);
                                        $text = "<table style='width:96%' class='fborder'>
                                        <tr>
                                        <td class='fcaption' colspan='2'>".LAN_133."</td>
                                        </tr><tr>
                                        <td style='text-align:right; vertical-align:center; width:20%' class='forumheader2'><img src='".e_IMAGE."forum/e.png' alt='' />&nbsp;</td>
                                        <td style='vertical-align:center; width:80%' class='forumheader2'>
                                        <br />".LAN_324."<br />

                                        <span class='defaulttext'><a href='".e_BASE."forum_viewtopic.php?".$thread_forum_id.".".$thread_id.".".$pages."#$iid'>".LAN_325."</a><br />
                                        <a href='".e_BASE."forum_viewforum.php?".$forum_id."'>".LAN_326."</a></span><br /><br />
                                        </td></tr></table>";
                                        if($pref['forum_enclose']){ $ns -> tablerender($pref['forum_title'], $text); }else{ echo $text; }
                                        clear_cache("newforumposts");
                                        require_once(FOOTERF);
                                        exit;
                                }
        }
}

if(IsSet($_POST['update_thread'])){

        if(!$_POST['subject'] || !$_POST['post']){
                $error = "<div style='text-align:center'>".LAN_27."</div>";
        }else{
                if(!isAuthor($thread_id)){
                        $ns -> tablerender(LAN_95, "<div style='text-align:center'>".LAN_96."</div>");
                        require_once(FOOTERF);
                        exit;
                }
                $post = $aj -> formtpa($_POST['post']."\n<span class='smallblacktext'>[ ".LAN_29." ".$datestamp." ]</span>", "public");
                $subject = $aj -> formtpa($_POST['subject'], "public");

                $datestamp = $gen->convert_date(time(), "forum");
                $sql -> db_Update("forum_t", "thread_name='".$subject."', thread_thread='".$post."', thread_s='".$_POST['threadtype']."' WHERE thread_id='$thread_id' ");
                clear_cache("newforumposts");
                header("location:".e_BASE."forum_viewtopic.php?".$forum_id.".".$thread_id);
                exit;
        }
}

if(IsSet($_POST['update_reply'])){

        if(!$_POST['post']){
                $error = "<div style='text-align:center'>".LAN_27."</div>";
        }else{
                if(!isAuthor($thread_id)){
                        $ns -> tablerender(LAN_95, "<div style='text-align:center'>".LAN_96."</div>");
                        require_once(FOOTERF);
                        exit;
                }
                $datestamp = $gen->convert_date(time(), "forum");
                $post = $aj -> formtpa($_POST['post']."\n<span class='smallblacktext'>[ ".LAN_29." ".$datestamp." ]</span>", "public");

                $sql -> db_Update("forum_t", "thread_thread='".$post."' WHERE thread_id=".$thread_id);
                clear_cache("newforumposts");

                $sql -> db_Select("forum_t", "*", "thread_id=$thread_id");
                $row = $sql -> db_Fetch(); extract($row);

                $replies = $sql -> db_Count("forum_t", "(*)", "WHERE thread_parent='".$thread_parent."'");

                $pref['forum_postspage'] = ($pref['forum_postspage'] ? $pref['forum_postspage'] : 10);
                $pages = ((ceil($replies/$pref['forum_postspage']) -1) * $pref['forum_postspage']);

                header("location:".e_BASE."forum_viewtopic.php?".$forum_id.".".$_POST['thread_id'].($pages ? ".$pages" : ""));
                exit;
        }
}

if($error){        $ns -> tablerender(LAN_20, $error); }

if($action == "edit" || $action == "quote"){
        $sql -> db_Select("forum", "*", "forum_id='".$forum_id."' ");
        $row = $sql-> db_Fetch(); extract($row);
        $sql -> db_Select("forum_t", "*", "thread_id='".$thread_id."' ");
        $row = $sql-> db_Fetch("no_strip"); extract($row);

        $post_author_id = substr($thread_user, 0, strpos($thread_user, "."));
        if($action == "edit"){
                if($post_author_id != USERID && !ADMIN){
                        $ns -> tablerender(LAN_95, "<div style='text-align:center'>".LAN_96."</div>");
                        require_once(FOOTERF);
                        exit;
                }
        }

        //&lt;span class=&#39;smallblacktext&#39;>[ Edited Mon Jan 05 2004, 12:28PM ]&lt;/span>

        $subject = $thread_name;
        $post = $aj -> editparse($thread_thread);
        $post = ereg_replace("&lt;span class=&#39;smallblacktext&#39;.*\span\>", "", $post);
        if($action == "quote"){
                $post_author_name = substr($thread_user, (strpos($thread_user, ".")+1));
                if(strstr($post_author_name, chr(1))){
                                                $tmp = explode(chr(1), $post_author_name);
                                                $post_author_name = $tmp[0];
                                        }
                $post = "[quote=$post_author_name]".$post."[/quote]\n";
                $eaction = FALSE;
                $action = "reply";
        }else{
                $eaction = TRUE;
                if($thread_parent ? $action = "reply" : $action = "nt");
        }
}


// -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------


if($action != "cp"){
$text = "<div style='text-align:center'>
<form enctype='multipart/form-data' method='post' action='".e_SELF."?".e_QUERY."' name='dataform'>
<table style='width:95%' class='fborder'>
<tr><td colspan='2' class='fcaption'><a class='forumlink' href='".e_BASE."forum.php'>".LAN_405."</a> -> <a class='forumlink' href='".e_HTTP."forum_viewforum.php?".$forum_id."'>".$forum_name."</a> -> ";

if($action == "nt"){
        $text .= ($eaction ? LAN_77 : LAN_60);
}else{
        $text .= ($eaction ? LAN_78 : LAN_406." ".$thread_name);
}

$text .= "</td></tr>";

if(ANON == TRUE  && USER == FALSE){
        $text .= "<tr>
<td class='forumheader2' style='width:20%'>".LAN_61."</td>
<td class='forumheader2' style='width:80%'>
<input class='tbox' type='text' name='anonname' size='71' value='".$anonname."' maxlength='20' />
</td>
</tr>";
}

if($action == "nt"){
        $text .= "<tr>
<td class='forumheader2' style='width:20%'>".LAN_62."</td>
<td class='forumheader2' style='width:80%'>
<input class='tbox' type='text' name='subject' size='71' value='".$subject."' maxlength='100' />
</td>
</tr>";
}

$text .= "<tr>
<td class='forumheader2' style='width:20%'>";
$text .= ($action == "nt" ? LAN_63 : LAN_73);

$text .= "</td>
<td class='forumheader2' style='width:80%'>
<textarea class='tbox' name='post' cols='70' rows='10' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>".$aj -> formtparev($post)."</textarea>
<br />\n".ren_help(2);

$text .= "<br />";
require_once(e_HANDLER."emote.php");
$text .= r_emote();

if($pref['email_notify'] && $action == "nt"){
        $text .= "
        <span class='defaulttext'>".LAN_380."</span>".
        ($_POST['email_notify'] ? "<input type='checkbox' name='email_notify' value='1' checked>" : "<input type='checkbox' name='email_notify' value='1'>");

}

if(ADMIN && getperms("5") && $action == "nt"){

        $text .= "<br />
        <span class='defaulttext'>
        ".LAN_400."
        <input name='threadtype' type='radio' value='0'".(!$_POST['threadtype'] ? "checked" : "").">".LAN_1."
        &nbsp;
        <input name='threadtype' type='radio' value='1'".($_POST['threadtype'] == 1 ? "checked" : "").">".LAN_2."
        &nbsp;
        <input name='threadtype' type='radio' value='2'".($_POST['threadtype'] == 2 ? "checked" : "").">".LAN_3."
        </span>";
}



if($action == "nt" && $pref['forum_poll'] && !eregi("edit", e_QUERY)){
        $text .= "</td>
        </tr>
        <tr>
        <td colspan='2' class='fcaption'>".LAN_4."</td>

        </tr>
        <tr>

        <td colspan='2' class='forumheader3'>
        <span class='smalltext'>".LAN_386."
        </td>
        </tr>

        <tr>
        <td style='width:20%' class='forumheader3'><div class='normaltext'>".LAN_5."</div></td>
        <td style='width:80%'class='forumheader3'>
        <input class='tbox' type='text' name='poll_title' size='70' value=\"".$aj -> tpa($_POST['poll_title'])."\" maxlength='200' />";

        $option_count = ($_POST['option_count'] ? $_POST['option_count'] : 1);
        $text .= "<input type='hidden' name='option_count' value='$option_count'>";

        for($count=1; $count<=$option_count; $count++){
                $var = "poll_option_".$count;
                $option = stripslashes($$var);
                $text .= "<tr>
        <td style='width:20%' class='forumheader3'>".LAN_391." ".$count.":</td>
        <td style='width:80%' class='forumheader3'>
        <input class='tbox' type='text' name='poll_option[]' size='60' value=\"".$aj -> tpa($_POST['poll_option'][($count-1)])."\" maxlength='200' />";
                if($option_count == $count){
                        $text .= " <input class='button' type='submit' name='addoption' value='".LAN_6."' /> ";
                }
                $text .= "</td></tr>";
        }


        $text .= "<tr>
        <td style='width:20%' class='forumheader3'>".LAN_7."</td>
        <td class='forumheader3'>";
        $text .= ($_POST['activate'] == 9 ? "<input name='activate' type='radio' value='9' checked>".LAN_8."<br />" : "<input name='activate' type='radio' value='9'>".LAN_8."<br />");
        $text .= ($_POST['activate'] == 10 ? "<input name='activate' type='radio' value='10' checked>".LAN_9."<br />" : "<input name='activate' type='radio' value='10'>".LAN_9."<br />");

        $text .= "</td>
        </tr>";
}


if($pref['forum_attach'] && !eregi("edit", e_QUERY) && FILE_UPLOADS && check_class($pref['upload_class'])){
        $text .= "<tr>
        <td colspan='2' class='fcaption'>".LAN_390."</td>
        </tr>
        <tr>
        <td style='width:20%' class='forumheader3'>".LAN_392."</td>
        <td style='width:80%' class='forumheader3'>

        ".LAN_393." | ".str_replace("\n", " | ", $pref['upload_allowedfiletype'])." |<br />".LAN_394."<br />".LAN_395.": ".($pref['upload_maxfilesize'] ? $pref['upload_maxfilesize'].LAN_396 : ini_get('upload_max_filesize'))."<br />


        <input class='tbox' name='file_userfile[]' type='file' size='47'>
        </td>
        </tr>

        ";
}





$text .= "<tr style='vertical-align:top'>

<td colspan='2' class='forumheader' style='text-align:center'>
<input class='button' type='submit' name='fpreview' value='".LAN_323."' /> ";

if($action != "nt"){
                $text .= ($eaction ? "<input class='button' type='submit' name='update_reply' value='".LAN_78."' />" : "<input class='button' type='submit' name='reply' value='".LAN_74."' />");
}else{
        $text .= ($eaction ? "<input class='button' type='submit' name='update_thread' value='".LAN_77."' />" : "<input class='button' type='submit' name='newthread' value='".LAN_64."' />");
}
$text .= "</td>
</tr>
<input type='hidden' name='thread_id' value='$thread_parent'>
</table>
</form>
</div>";


$text .= "<table style='width:95%'>
<tr>
<td style='width:50%'>";
$text .= forumjump();
$text .= "</td></tr></table><br />";

}

if($action == "rp" || $action == "cp"){
                $id = $thread_id;
        $sql -> db_Select("forum_t", "*", "thread_id = '$thread_id' ");
        $row = $sql-> db_Fetch("no_strip"); extract($row);
        $post_author_name = substr($thread_user, (strpos($thread_user, ".")+1));
        $tmp = explode(chr(1), $post_author_name);
        $post_author_name = $tmp[0];
        $thread_datestamp  = $gen->convert_date($thread_datestamp , "forum");
        $thread_name = $aj -> tpa($thread_name, $mode="off");
        $thread_thread = $aj -> tpa($thread_thread, $mode="off");
                $replies = $sql -> db_Count("forum_t" ,"(*)", "WHERE thread_parent='$id'");
                $replies_t = ($replies >= 10 ? "10" : $replies);
        $text .= "<div style='text-align:center'>".($action == "rp" ? "<div style='border:0;padding-right:2px;width:auto;height:400px;overflow:auto;'>": "")."
        <table style='width:97%' class='fborder'>
        <tr>
        <td colspan='2' class='fcaption' style='vertical-align:top'>".($action == "rp" ? LAN_100."</td></tr>" : $thread_name."</td></tr>");
        $text .= "<tr>
        <td class='forumheader3' style='width:20%' style='vertical-align:top'><b>".$post_author_name."</b></td>
        <td class='forumheader3' style='width:80%'>
        <div class='smallblacktext' style='text-align:right'><img src='".e_IMAGE."forum/post.png' alt='' /> ".LAN_322.$thread_datestamp."</div>".$thread_thread."</td>
        </tr>".($action == "rp"  && $replies ? "
        </table>
                <br />
        <table style='width:97%' class='fborder'>
                <tr><td colspan='2' class='fcaption' style='vertical-align:top'>".LAN_101.$replies_t.LAN_102."</td></tr>" : "");
                $query = ($action == "cp" ? "thread_parent=$id ORDER by thread_datestamp" : "thread_parent=$id ORDER by thread_datestamp DESC LIMIT 0,10 ");
                if($replies){
                        $sql -> db_Select("forum_t", "*", $query);
                        while($row = $sql-> db_Fetch("no_strip")){ extract($row);
                        $post_author_name = substr($thread_user, (strpos($thread_user, ".")+1));
                        if(strstr($post_author_name, chr(1))){
                                $tmp = explode(chr(1), $post_author_name);
                                $post_author_name = $tmp[0];
                        }
                        $thread_datestamp  = $gen->convert_date($thread_datestamp , "forum");
                        $thread_name = $aj -> tpa($thread_name, $mode="off");
                        $thread_thread = $aj -> tpa($thread_thread, $mode="off");
                        $text .= "<tr>
                        <td class='forumheader3' style='width:20%' style='vertical-align:top'><b>".$post_author_name."</b></td>
                        <td class='forumheader3' style='width:80%'>
                        <div class='smallblacktext' style='text-align:right'><img src='".e_IMAGE."forum/post.png' alt='' /> ".LAN_322.$thread_datestamp."</div>".$thread_thread."</td>
                        </tr>";
                        }
                }
        $text .= ($action == "rp" && $replies > 10 ? "<tr>
                <td class='forumheader3'  colspan='2'><a href='javascript:open_window(\"forum_post.php?cp.".$forum_id.".".$id."\",\"full\")'>".LAN_103."</a></td>
                        </tr>" : "")."
                </table>
        </div>".($action == "rp" ? "</div>" : "");
}

if($pref['forum_enclose']){ $ns -> tablerender($pref['forum_title'], $text); }else{ echo $text; }

function isAuthor($thread){
        global $sql;
        $sql -> db_Select("forum_t", "thread_user", "thread_id='".$thread."' ");
        $row = $sql-> db_Fetch("no_strip");
        $post_author_id = substr($row[0], 0, strpos($row[0], "."));
        return ($post_author_id == USERID || ADMIN === TRUE);
}

function getuser($name){
        global $sql, $aj;
        $ip = getip();
        if(USER){
                $name = USERID.".".USERNAME;
                $sql -> db_Update("user", "user_chats=user_chats+1, user_lastpost='".time()."' WHERE user_id='".USERID."' ");
        }else if(!$name){
                // anonymous guest
                $name = "0.".LAN_311.chr(1).$ip;
        }else{
                //$sql = new db;
                if($sql -> db_Select("user", "*", "user_name='$name' ")){
                        $ip = getip();
                        if($sql -> db_Select("user", "*", "user_name='$name' AND user_ip='$ip' ")){
                                list($cuser_id, $cuser_name) = $sql-> db_Fetch();
                                $name = $cuser_id.".".$cuser_name;
                        }else{
                                return FALSE;
                        }
                }else{
                        $name = "0.".substr($aj -> formtpa($_POST['anonname'], "public"), 0, 20).chr(1).$ip;
                }
        }
        return $name;
}
function loginf(){
        $text .=  "<div style='text-align:center'>
<form method='post' action='".e_SELF."?".e_QUERY."'><p>
".LAN_16."<br />
<input class='tbox' type='text' name='username' size='15' value='' maxlength='20' />\n
<br />
".LAN_17."
<br />
<input class='tbox' type='password' name='userpass' size='15' value='' maxlength='20' />\n
<br />
<input class='button' type='submit' name='userlogin' value='".LAN_10."' />\n
<br />
<input type='checkbox' name='autologin' value='1' /> ".LAN_11."
<br /><br />
[ <a href='".e_BASE."signup.php'>".LAN_174."</a> ]<br />[ <a href='fpw.php'>".LAN_212."</a> ]
</p>
</form>
</div>";
$ns = new e107table;
$ns -> tablerender(LAN_175, $text);
}
function forumjump(){
        global $sql;
        $sql -> db_Select("forum", "*", "forum_parent !=0 AND forum_class!='255' ");
        $text .= "<form method='post' action='".e_SELF."'><p>".LAN_401.": <select name='forumjump' class='tbox'>";
        while($row = $sql -> db_Fetch()){
                extract($row);
                if(($forum_class && check_class($forum_class)) || ($forum_class == 254 && USER) || !$forum_class){
                        $text .= "\n<option value='".$forum_id."'>".$forum_name."</option>";
                }
        }
        $text .= "</select> <input class='button' type='submit' name='fjsubmit' value='".LAN_387."' /></p></form>";
        return $text;
}
function redirect($url)
{
        global $ns;
        if (@preg_match('/Microsoft|WebSTAR|Xitami/', getenv('SERVER_SOFTWARE')))
        {
                header('Refresh: 0; URL=' .$url);
                $text = "<div style='text-align:center'>".LAN_408."<a href='".$url."'> ".LAN_409." </a>".LAN_410."</div>";
                $ns -> tablerender(LAN_407, $text);
                require_once(FOOTERF);
                exit;
        }

        header('Location: ' . $url);
        exit;
}require_once(FOOTERF);
?>