<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");

$qs = explode(".", $_SERVER['QUERY_STRING']);
$action = $qs[0];
$forum_id = $qs[1];
$thread_id = $qs[2];

$user = USERID.".".USERNAME;
if($pref['anon_post'][1] == "1"){ define(ANON, TRUE); }else{ define(ANON, FALSE); }
if($pref['user_reg'][1] == "1"){ define(REG, TRUE); }else{ define(REG, FALSE); }

$gen = new convert;
$aj = new textparse();
$fp = new floodprotect;

if(IsSet($_POST['newthread'])){
	if($_POST['subject'] == "" || $_POST['post'] == ""){
		$error = "<div style=\"text-align:center\">".LAN_27."</div>";
	}else{
		if($fp -> flood("forum_t", "thread_datestamp") == FALSE){
			header("location:index.php");
			die();
		}
		if(USER != TRUE){
			if($_POST['anonname'] == ""){
				$user = "0.Anonymous";
			}else{
				$user = "0.".$_POST['anonname'];
			}
		}else{
			$user = USERID.".".USERNAME;
		}
		if($user == "."){
			$user = "0.Anonymous";
		}
		$subject = $aj -> tp($_POST['subject']);
		$post = $aj -> tp($_POST['post']);
		$lastpost = $user.".".time();
		$sql -> db_Insert("forum_t", "0, '".$subject."', '".$post."', '$forum_id', '".time()."', '0', '$user', 0, 1, '".time()."' ");
		$sql -> db_Update("forum", "forum_threads=forum_threads+1, forum_lastpost='$lastpost' WHERE forum_id='$forum_id' ");
		$sql -> db_Update("user", "user_forums=user_forums+1 WHERE user_id='".USERID."' ");
		header("location: forum.php?forum.".$forum_id);
	}
}

if(IsSet($_POST['fjsubmit'])){
	$sql -> db_Select("forum", "*", "forum_name='".$_POST['forumjump']."' ");
	list($forum_id) = $sql-> db_Fetch();
	header("location: forum.php?forum.".$forum_id);
}

if(IsSet($_POST['reply'])){
	if($_POST['post'] == ""){
		$error = "<div style=\"text-align:center\">".LAN_28."</div>";
	}else{
		if($fp -> flood("forum_t", "thread_datestamp") == FALSE){
			header("location:index.php");
			die();
		}
		if(USER != TRUE){
			if($_POST['anonname'] == ""){
				$user = "0.Anonymous";
			}else{
				$user = "0.".$_POST['anonname'];
			}
		}else{
			$user = USERID.".".USERNAME;
		}
		$post = $aj -> tp($_POST['post']);
		$lastpost = $user.".".time();
		$sql -> db_Insert("forum_t", "0, '', '".$post."', '$forum_id', '".time()."', '".$thread_id."', '$user', 0, 1, '".time()."' ");
		$sql -> db_Update("forum_t",  "thread_lastpost='".time()."' WHERE thread_id='$thread_id' ");
		$sql -> db_Update("forum", "forum_replies=forum_replies+1, forum_lastpost='$lastpost' WHERE forum_id='$forum_id' ");
		$sql -> db_Update("user", "user_forums=user_forums+1 WHERE user_id='".USERID."' ");
		header("location: forum.php?view.".$forum_id.".".$thread_id);
	}
}

if($_POST['edit'] == "Update Thread"){
	if($_POST['subject'] == "" || $_POST['post'] == ""){
		$error = "<div style=\"text-align:center\">".LAN_27."</div>";
	}else{
		$subject = $aj -> tp($_POST['subject']);
		$post = $aj -> tp($_POST['post']);
		$datestamp = $gen->convert_date(time(), "forum");
		$post = $_POST['post']."<div style=\"text-align:right\">[ ".LAN_29." ".$datestamp." ]</div>";
		$sql -> db_Update("forum_t", "thread_name='".$subject."', thread_thread='".$post."' WHERE thread_id='$thread_id' ");
		header("location: forum.php?view.".$forum_id.".".$thread_id);
	}
}

if($_POST['edit'] == "Update Reply"){
	if($_POST['post'] == ""){
		$error = "<div style=\"text-align:center\">".LAN_28."</div>";
	}else{
		$post = ereg_replace("\r", "<br />", $_POST['post']);
		$post = addslashes($post);
		$datestamp = $gen->convert_date(time(), "forum");
		$post = $_POST['post']."<div style=\"text-align:right\">[ ".LAN_29." ".$datestamp." ]</div>";
		$sql -> db_Update("forum_t", "thread_thread='".$post."' WHERE thread_id='$thread_id' ");
		header("location: forum.php?view.".$forum_id.".".$_POST['thread_id']);
	}
}

require_once(HEADERF);

if(USERNAME == TRUE){
	$tmp=explode("^", $user_new);
	$new_threads = $tmp[0];
	$new_parents = $tmp[1];
	$tmp=explode(".", $new_threads);
	$total_new_threads = count($tmp)-1;
}

if($error != ""){
	$ns -> tablerender("<div style=\"text-align:center\">".LAN_20."</div>", $error);
}




//############################################################################################# render forums
if(!$_SERVER['QUERY_STRING']){

	$total_topics = $sql -> db_Count("forum_t", "(*)", " WHERE thread_parent='0' ");
	$total_replies = $sql -> db_Count("forum_t", "(*)", " WHERE thread_parent!='0' ");
	$total_members = $sql -> db_Count("user");
	$newest_member = $sql -> db_Select("user", "*", "ORDER BY user_join DESC LIMIT 0,1", $mode="no_where");
	list($nuser_id, $nuser_name)  = $sql -> db_Fetch();

	$text = "<table style=\"95%\">
	<tr>
	<tr> 
	<td colspan=\"2\" style=\"text-align:center\"><img src=\"themes/shared/forum/forumlogo.png\" alt=\"\" /></td>
	</tr>
	<tr>
	<td style=\"width:50%; vertical-align:top\">";
	if(USER == TRUE){

		$text .= LAN_30." ".USERNAME."<br />";
		$lastvisit_datestamp = $gen->convert_date(USERLASTVISIT, "long");
		$datestamp = $gen->convert_date(time(), "long");
		if(!$total_new_threads){
			$text .= LAN_31;
		}else if($total_new_threads == 1){
			$text .= LAN_32;
		}else{
			$text .= LAN_33." ".$total_new_threads." ".LAN_34." ";
		}
		$text .= LAN_35."<br />
		".LAN_36." ".$lastvisit_datestamp."<br />
		".LAN_37." ".$datestamp." ".LAN_38."
		</td>
		<td style=\"width:50%; text-align:right\">$total_topics ".LAN_39.", $total_replies ".LAN_40."<br />
		".LAN_41."<a href=\"user.php?id.".$nuser_id."\">".$nuser_name."</a>.<br />
		".LAN_42." ".$total_members.".</td>
		</tr>
		<tr> 
		<td colspan=\"2\" style=\"text-align:center\">";
		
		if(ANON == TRUE && USER == FALSE){
			$text .= LAN_43;
		}else if(ANON == TRUE){
			$text .= LAN_44;
		}else if(USER == FALSE){
			$text .= LAN_45;
		}
		
		$text .= "</td>

		</tr>
		</table>";

	}else{
		$text = "<div style=\"text-align:center\"><img src=\"themes/shared/forum/forumlogo.png\" alt=\"\" /></div>";
	}

	$ns -> tablerender("", $text);


	$caption = "<table width=\"95%\" style=\"text-align:center\">
<tr> 
<td style=\"width:3%\">&nbsp;</td>
<td style=\"width:47%\"><b>".LAN_46."</b></td>
<td style=\"width:7%; text-align:center\"><b>".LAN_47."</b></td>
<td style=\"width:7%; text-align:center\"><b>".LAN_48."</b></td>
<td style=\"width:23%; text-align:center\"><b>".LAN_49."</b></td>
<td style=\"width:20%; text-align:center\"><b>".LAN_50."</b></td>
</tr>
</table>";

	$text = "<table width=\"95%\">";

	$forum_parents = $sql -> db_Select("forum", "*", "forum_parent='0' ");
	if($forum_parents == 0){
		$text .= "<tr><td>".LAN_51."</td></tr>";
	}else{
		while(list($forum_id, $forum_name, $forum_description, $forum_parent, $forum_datestamp, $forum_active, $forum_moderators, $forum_threads, $forum_replies) = $sql-> db_Fetch()){

			$text .= "\n<tr><td colspan=\"6\" class=\"fcaption\">".$forum_name."</td></tr>";
			$sql2 = new db;
			$forums = $sql2 -> db_Select("forum", "*", "forum_parent='".$forum_id."' ");
			if($forums == 0){
				$text .= "<td colspan=\"5\" align=\"center\">".LAN_52."<br /><br /></td>";
			}else{
				while(list($forum_id, $forum_name, $forum_description, $parent, $forum_datestamp, $forum_active, $forum_moderators, $forum_threads, $forum_replies, $forum_lastpost) = $sql2-> db_Fetch()){

					if(getnew($forum_id, $user_new)){
						$text .= "<tr><td style=\"vertical-align:center\"><img src=\"themes/shared/forum/new.png\" alt=\"\"></td>";
					}else{
						$text .= "<tr><td>&nbsp;</td>";
					}
$text .= "<td style=\"vertical-align:top; width:47%\"><span class=\"mediumtext\"><a href=\"forum.php?forum.".$forum_id."\">".$forum_name."</a></span><br />".$forum_description."</td>
<td style=\"vertical-align:top; text-align:center; width:7%\">".$forum_threads."</td>
<td style=\"vertical-align:top; text-align:center; width:7%\">".$forum_replies."</td>
<td style=\"vertical-align:top; text-align:center; width:23%\">";


					if($forum_threads == 0 && $forum_replies == 0){
						$text .= "No posts yet";
					}else{
						$lp = explode(".", $forum_lastpost);
						if(ereg("[0-9]+", $lp[0])){
							$lastpost_author_id = $lp[0];
							$lastpost_author_name = $lp[1];
							$lastpost_datestamp = $lp[2];
							$lastpost_datestamp = $gen->convert_date($lastpost_datestamp, "forum");
							$text .= $lastpost_datestamp."<br /><a href=\"user.php?id.".$lastpost_author_id."\">".$lastpost_author_name."</a>";
						}
					}

					$text .= "</td>
<td style=\"vertical-align:top; text-align:center; width:20%\">".$forum_moderators."</td>
</tr>";
				}
			}
			$text .= "<tr> 
<td colspan=\"5\">&nbsp;<br /></td></tr>";
		}
	}
	$text .= "</table>";
	$ns -> tablerender($caption, $text);
}
//############################################################################################# render threads
if($action == "forum"){
	$view=15;
	if(!$thread_id){
		$from = 0;
	}else{
		$from = $thread_id;
	}
	$sql -> db_Select("forum", "*", "forum_id='".$forum_id."' ");
	list($forum_id, $forum_name, $forum_description, $parent, $forum_datestamp, $forum_active, $forum_moderators) = $sql-> db_Fetch();

	$topics = $sql -> db_Count("forum_t", "(*)", " WHERE thread_forum_id='".$forum_id."' AND thread_parent='0' ");

	if($topics > $view){
		$a = $topics/$view;
		$r = explode(".", $a);
		if($r[1] != 0){
			$pages = ($r[0]+1);
		}else{
			$pages = $r[0];
		}
	}else{
		$pages = 0;
	}

	$text = "<div style=\"text-align:center\"><img src=\"themes/shared/forum/forumlogo.png\" alt=\"\" /></div>
	<br />
	<a href=\"index.php\">".SITENAME."</a> >> <a href=\"forum.php\">Forums</a> >> ".$forum_name;
	if($pages != 0){
		$text .= "<br />Got to page ";
		for($c=0; $c < $pages; $c++){
			if($view*$c == $from){
				$text .= "<u>".($c+1)."</u> ";
			}else{
				$text .= "<a href=\"".$_SERVER['PHP_SELF']."?forum.".$forum_id.".".($view*$c)."\">".($c+1)."</a> ";
			}
		}
	}
	
	$ns -> tablerender("", $text);

	$caption = "<table width=\"95%\">
<tr>
<td style=\"width:3%\" class=\"mediumtext\"></td>
<td style=\"width:47%\" class=\"mediumtext\">".LAN_53."</td>
<td style=\"width:20%; text-align:center\" class=\"mediumtext\">".LAN_54."</td>
<td style=\"width:5%; text-align:center\" class=\"mediumtext\">".LAN_55."</td>
<td style=\"width:5%; text-align:center\" class=\"mediumtext\">".LAN_56."</td>
<td style=\"width:20%; text-align:center\" class=\"mediumtext\">".LAN_57."</td>
</tr>
</table>";

	$sql -> db_Select("forum_t", "*",  "thread_forum_id='".$forum_id."' AND thread_parent='0' ORDER BY thread_lastpost DESC, thread_datestamp DESC LIMIT $from, $view");
	if($topics == 0){
		$ns -> tablerender("<div style=\"text-align:center\"><a href=\"forum.php\">Forums</a> >> ".$forum_name."</div>", "<div style=\"text-align:center\">".LAN_58."</div>");
	}else{

		$text = "<table style=\"width:95%; text-align:center\" class=\"forumtable1\">";

		while(list($thread_id, $thread_name, $thread_thread, $thread_forum_id, $thread_datestamp, $thread_parent, $thread_user, $thread_views, $thread_active) = $sql -> db_Fetch()){

		

			$sql2 = new db;
			$replies = $sql2 -> db_Count("forum_t", "(*)", " WHERE thread_parent='$thread_id' ");
			if($replies != 0){
				$sql2 -> db_Select("forum_t", "*", "thread_parent='$thread_id' ORDER BY thread_datestamp DESC");
				list($null, $null, $null, $null, $r_datestamp, $null, $r_user) = $sql2 -> db_Fetch();
				$rp = explode(".", $r_user);
				if(ereg("[0-9]+", $rp[0])){
					$r_id = $rp[0];
					$r_name = $rp[1];
				}
				$r_datestamp = $gen->convert_date($r_datestamp, "forum");
				$lastreply = $r_datestamp."<br /><a href=\"user.php?id.".$r_id."\">".$r_name."</a>";
			}else{
				$replies = "None";
				$lastreply = " - ";
			}

			$lp = explode(".", $thread_user);
			if(ereg("[0-9]+", $lp[0])){
				$post_author_id = $lp[0];
				$post_author_name = $lp[1];
			}
			$thread_datestamp = $gen->convert_date($thread_datestamp, "forum");

			$text .= "<tr>
			<td style=\"vertical-align:center; width:3%\" class=\"forumtable2\">";
			if($thread_active == 0){
				$text .= "<img src=\"themes/shared/forum/closed.png\" alt=\"\" />";

			}else if(ereg("\.".$thread_id."\.", $user_new)){
				$text .= "<img src=\"themes/shared/forum/new.png\" alt=\"\" />";
			}else{
				$text .= "<img src=\"themes/shared/forum/nonew.png\" alt=\"\" />";
			}

			$text .= "</td><td style=\"vertical-align:center; text-align:left; width:47%\"  class=\"forumtable2\"><span class=\"mediumtext\"><a href=\"".$_SERVER['PHP_SELF']."?view.".$forum_id.".".$thread_id."\">".$thread_name."</a></span>";

			if(eregi(ADMINNAME, $forum_moderators)){
				if($thread_active == 1){
					$text .= "<br /><span class=\"smalltext\">[ moderator: <a href=\"admin/forum_conf.php?close.".$forum_id.".".$thread_id."\">close this thread</a> ]</span>";
				}else{
					$text .= "<br /><span class=\"smalltext\">[ moderator: <a href=\"admin/forum_conf.php?reopen.".$forum_id.".".$thread_id."\">reopen this thread</a> ]</span>";
				}
			}
			
			
			$text .= "</td>
<td style=\"vertical-align:top; text-align:center; width:20%\" class=\"forumtable2\">".$thread_datestamp."<br /><a href=\"user.php?id.".$post_author_id."\">".$post_author_name."</a></td>
<td style=\"vertical-align:center; text-align:center; width:5%\" class=\"forumtable2\">$replies</td>
<td style=\"vertical-align:center; text-align:center; width:5%\" class=\"forumtable2\">$thread_views</td>
<td style=\"vertical-align:top; text-align:center; width:20%\" class=\"forumtable2\">$lastreply</td>
</tr>";

		}
		$text .= "</table>";
		$ns -> tablerender($caption, $text);
	}

	$text = "<table style=\"width:95%\">
	<tr>
	<td style=\"width:50%\">";
	$text .= forumjump();
	$text .= "</td>
	<td style=\"width:50%; text-align:right\">";

if(ANON == TRUE || IsSet($_COOKIE['userkey'])){
	$text .= "<a href=\"".$_SERVER['PHP_SELF']."?nt.".$forum_id.".".$thread_id."\"><img src=\"themes/shared/forum/newthread.png\" alt=\"\" style=\"border:0\" /></a>";
}else{
	$text .= LAN_59;
}
	$text .= "</td>
	</tr>
	</table>";

	$ns -> tablerender("", $text);

}
//############################################################################################# new thread

if($action == "nt"){

	if(ANON == FALSE && USER == FALSE){
		$text .= LAN_45;
		$ns -> tablerender("Error!", $text);
		require_once(FOOTERF);
		exit;
	}
	
	$sql -> db_Select("forum", "*", "forum_id='".$forum_id."' ");
	list($forum_id, $forum_name, $forum_description, $parent, $forum_datestamp, $forum_active, $forum_moderators) = $sql-> db_Fetch();

	$text = "<div style=\"text-align:center\"><img src=\"themes/shared/forum/forumlogo.png\" alt=\"\" /></div>
	<br />
	<a href=\"index.php\">".SITENAME."</a> -> <a href=\"forum.php\">Forums</a> >> <a href=\"forum.php?forum.".$forum_id."\">".$forum_name."</a> >> ".LAN_60;
	$ns -> tablerender("", $text);

	$text = "
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."\">\n
<table style=\"width:95%\">";

	if(ANON == true  && USERNAME == FALSE){
		$text .= "<tr>
<td style=\"width:20%\">".LAN_61."</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"anonname\" size=\"60\" value=\"$anonname\" maxlength=\"100\" />
</td>
</tr>";
	}

	$text .= "<tr>
<td style=\"width:20%\">".LAN_62."</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"subject\" size=\"60\" value=\"$subject\" maxlength=\"100\" />
</td>
</tr>
<tr> 
<td style=\"width:20%\">".LAN_63."</td>
<td style=\"width:80%\">
<textarea class=\"tbox\" name=\"post\" cols=\"70\" rows=\"10\"></textarea>
</td>
</tr>\n
<tr style=\"vertical-align:top\"> 
<td style=\"width:20%\"></td>
<td style=\"width:80%\">
<input class=\"button\" type=\"submit\" name=\"newthread\" value=\"".LAN_64."\" />
<br />
<br />
<span class=\"smalltext\">
".LAN_10."
</span>
</td>
</tr>
</table>
</form>";


$ns -> tablerender(LAN_60, $text);

$text = "<table style=\"width:95%\">
<tr>
<td style=\"width:50%\">";
$text .= forumjump();
$text .= "</td></tr></table>";
$ns -> tablerender("", $text);

}

//############################################################################################# view

if($action == "view"){

	$sql -> db_Update("forum_t", "thread_views=thread_views+1 WHERE thread_id='$thread_id' ");

	$sql -> db_Select("forum", "*", "forum_id='".$forum_id."' ");
	list($forum_id, $forum_name, $forum_description, $parent, $forum_datestamp, $forum_active, $forum_moderators) = $sql-> db_Fetch();
	$sql -> db_Select("forum_t", "*", "thread_id='".$thread_id."' ORDER BY thread_datestamp DESC ");
	list($thread_id, $thread_name, $thread_thread, $thread_forum_id, $thread_datestamp, $thread_parent, $thread_user, $thread_views, $thread_active_m) = $sql -> db_Fetch();

	$text = "<div style=\"text-align:center\"><img src=\"themes/shared/forum/forumlogo.png\" alt=\"\" /></div>
	<br />
	<a href=\"index.php\">".SITENAME."</a> -> <a href=\"forum.php\">Forums</a> -> <a href=\"forum.php?forum.".$forum_id."\">".$forum_name."</a> -> ".$thread_name;
	if(ADMIN == TRUE){
		$text .= "<br />".LAN_65;
	}

	if($thread_active_m == 0){
		$text .= "<br /><br /><div class=\"mediumtext\"  style=\"text-align:center\"><b>".LAN_66."</b></div>";
	}

	$ns -> tablerender("", $text);

	$lp = explode(".", $thread_user);
	if(ereg("[0-9]+", $lp[0])){
		$post_author_id = $lp[0];
		$post_author_name = $lp[1];
	}

	$thread_thread = stripslashes(nl2br($thread_thread));
	$starter_count = $sql -> db_Count("forum_t", "(*)", " WHERE thread_user='$thread_user' OR thread_user='$post_author_name' ");
	
	
	$thread_datestamp = $gen->convert_date($thread_datestamp, "forum");
	$sql -> db_Select("user", "*", "user_name='".$post_author_name."' ");
	list($user_id, $user_name, $null, $user_sess_, $user_email, $user_homepage, $user_icq, $user_aim, $user_msn, $user_location, $user_birthday, $user_signature, $user_image, $user_timezone, $user_hideemail, $user_join, $user_lastvisit, $user_currentvisit, $user_lastpost, $user_chats, $user_comments, $user_forums, $user_ip, $user_ban, $user_prefs, $user_new_, $null, $user_visits)  = $sql -> db_Fetch();

	$text = "<table style=\"width:96%\" class=\"forumtable1\">
<tr> 
<td style=\"width:20%; vertical-align:top\" class=\"forumtable2\">";

if(ereg("\.".$thread_id."\.", $new_threads)){
	$text .= "<img src=\"themes/shared/forum/new.png\" alt=\"\" /><br />";
}
$user_new = ereg_replace("\.".$thread_id."\.", ".", $user_new);
$text .= "<div class=\"mediumtext\"><a href=\"user.php?id.".$post_author_id."\"><b>".$post_author_name."</b></a></div>";
if($user_image != ""){
	$text .= "<div class=\"spacer\">
<img src=\"".$user_image."\" alt=\"\" />
</div>";
}
if($user_id != ""){
	$text .= LAN_67.": $starter_count";
}

$thread_thread = $aj -> tpa($thread_thread, $mode="on");

$text .= "</td>
<td colspan=\"2\" style=\"width:80%; vertical-align:top\" class=\"forumtable2\">
".$thread_thread;

if($user_signature != ""){
	$text .= "<br />
<hr style=\"width:30%; text-align:left\" />
".$user_signature;
}

if(eregi(ADMINNAME, $forum_moderators)){
	$text .= "<br />
	<div style=\"text-align:right\">
	[ moderator - <a href=\"forum.php?edit.".$forum_id.".".$thread_id."\">".LAN_68."</a> - 
	<a href=\"admin/forum_conf.php?delete.".$forum_id.".".$thread_id."\">".LAN_69."</a> -
	<a href=\"admin/forum_conf.php?move.".$forum_id.".".$thread_id."\">".LAN_70."</a> ]
	</div>";
}

$text .= "</td>

</tr>
<tr>
<td style=\"width:20%; vertical-align:center\" class=\"forumtable2\"><img src=\"themes/shared/forum/post.png\" alt=\"\" /> $thread_datestamp</td>
<td style=\"width:60%; vertical-align:top\" class=\"forumtable2\">";
if($user_id != ""){
	$text .= "<a href=\"user.php?id.".$user_id."\"><img src=\"themes/shared/forum/profile.png\" alt=\"\" style=\"border:0\" /></a> ";
}


if($user_hideemail != 1 && $user_id != ""){
	$text .= "<a href=\"mailto:$user_email\"><img src=\"themes/shared/forum/email.png\" alt=\"\" style=\"border:0\" /></a> ";
}
if($user_homepage != ""){
	$text .= "<a href=\"$user_homepage\"><img src=\"themes/shared/forum/website.png\" alt=\"\" style=\"border:0\" /></a> ";
}

if($post_author_name == USERNAME && $thread_active_m == 1){
	$text .= "<img src=\"themes/shared/generic/trans.gif\" alt=\"\" style=\"width:30px; height:1px\" />
<a href=\"forum.php?quote.".$forum_id.".".$thread_id."\"><img src=\"themes/shared/forum/quote.png\" alt=\"\" style=\"border:0\" /></a>
 <a href=\"forum.php?edit.".$forum_id.".".$thread_id."\"><img src=\"themes/shared/forum/edit.png\" alt=\"\" style=\"border:0\" /></a></td>";
}else if($thread_active_m == 1){
$text .= "<img src=\"themes/shared/generic/trans.gif\" alt=\"\" style=\"width:30px; height:1px\" />
<a href=\"forum.php?quote.".$forum_id.".".$thread_id."\"><img src=\"themes/shared/forum/quote.png\" alt=\"\" style=\"border:0\" /></a>";
}

$text .= "</td>
</tr>
</table>";
	$ns -> tablerender("", $text);

if(!$sql -> db_Select("forum_t", "*", "thread_parent='".$thread_id."' ORDER BY thread_datestamp ASC")){
	$text = "<div style=\"text-align:center\">".LAN_71."</div>";
}else{
	$text = "<table style=\"width:96%\" class=\"forumtable1\">";
	while(list($reply_id, $thread_name, $thread_thread, $thread_forum_id, $thread_datestamp, $thread_parent, $thread_user, $thread_views, $thread_active) = $sql -> db_Fetch()){
		$thread_thread = stripslashes(nl2br($thread_thread));
		$sql2 = new db;

		$lp = explode(".", $thread_user);
		if(ereg("[0-9]+", $lp[0])){
			$post_author_id = $lp[0];
			$post_author_name = $lp[1];
		}

		if($ft){
			$ft=0;
			$forumtable = "forumtable2";
		}else{
			$ft=1;
			$forumtable = "forumtable3";
		}

		$sql2 -> db_Select("user", "*", "user_name='".$post_author_name."' ");
		list($user_id, $user_name, $null, $user_sess_, $user_email, $user_homepage, $user_icq, $user_aim, $user_msn, $user_location, $user_birthday, $user_signature, $user_image, $user_timezone, $user_hideemail, $user_join, $user_lastvisit, $user_currentvisit, $user_lastpost, $user_chats, $user_comments, $user_forums, $user_ip, $user_ban, $user_prefs, $user_new_, $null, $user_visits)  = $sql2 -> db_Fetch();

		$pan = $user_id.".".$post_author_name;

		$replier_count = $sql2 -> db_Count("forum_t", "(*)", " WHERE thread_user='$thread_user'  OR thread_user='$pan' ");

		$thread_datestamp = $gen->convert_date($thread_datestamp, "forum");
		$text .= "<tr><td style=\"width:20%; vertical-align:top\" class=\"$forumtable\">";

		if(ereg("\.".$reply_id."\.", $new_threads)){
			$user_new = ereg_replace("\.".$reply_id."\.", ".", $user_new);
			$text .= "<img src=\"themes/shared/forum/new.png\" alt=\"\" />";
		}
		$text .= "<div class=\"mediumtext\"><a href=\"user.php?id.".$post_author_id."\"><b>".$post_author_name."</b></a></div>";
if($user_image != ""){
	$text .= "<div class=\"spacer\"><img src=\"".$user_image."\" alt=\"\" /></div>";
}

$thread_thread = $aj -> tpa($thread_thread);

$text .= LAN_67.": $replier_count
</td>
<td colspan=\"2\" style=\"width:80%; vertical-align:top\" class=\"$forumtable\">
".$thread_thread;

if($user_signature != ""){
	$text .= "<br />
<hr style=\"width:30%; text-align:left\" />
".$user_signature;
}

if(eregi(ADMINNAME, $forum_moderators)){
	$text .= "<br />
	<div style=\"text-align:right\">
	[ moderator - <a href=\"forum.php?edit.".$forum_id.".".$reply_id."\">edit</a> - 
	<a href=\"admin/forum_conf.php?delete.".$forum_id.".".$reply_id."\">delete</a> ]
	</div>";
}

$text .= "
</td>

</tr>
<tr>
<td style=\"width:20%; vertical-align:center\" class=\"$forumtable\"><img src=\"themes/shared/forum/post.png\" alt=\"\" /> $thread_datestamp</td>
<td style=\"width:60%; vertical-align:top\" class=\"$forumtable\">";

if($user_id != ""){
	$text .= "<a href=\"user.php?id.".$user_id."\"><img src=\"themes/shared/forum/profile.png\" alt=\"\" style=\"border:0\" /></a> ";
}
if($user_hideemail != 1 && $user_id != ""){
	$text .= "<a href=\"mailto:$user_email\"><img src=\"themes/shared/forum/email.png\" alt=\"\" style=\"border:0\" /></a> ";
}
if($user_homepage != ""){
	$text .= "<a href=\"$user_homepage\"><img src=\"themes/shared/forum/website.png\" alt=\"\" style=\"border:0\" /></a> ";
}

if($post_author_name == USERNAME && $thread_active_m == 1){
	$text .= "<img src=\"themes/shared/generic/trans.gif\" alt=\"\" style=\"width:30px; height:1px\" />
<a href=\"forum.php?quote.".$forum_id.".".$reply_id."\"><img src=\"themes/shared/forum/quote.png\" alt=\"\" style=\"border:0\" /></a>
 <a href=\"forum.php?edit.".$forum_id.".".$reply_id."\"><img src=\"themes/shared/forum/edit.png\" alt=\"\" style=\"border:0\" /></a></td>";
}else if($thread_active_m == 1){
$text .= "<img src=\"themes/shared/generic/trans.gif\" alt=\"\" style=\"width:30px; height:1px\" />
<a href=\"forum.php?quote.".$forum_id.".".$reply_id."\"><img src=\"themes/shared/forum/quote.png\" alt=\"\" style=\"border:0\" /></a>";
}

$text .= "</td>
</tr>";
	}
	$text .= "</table>";
}
$ns -> tablerender("", $text);

	$text = "<table style=\"width:95%\">
	<tr>
	<td style=\"width:50%\">";
	$text .= forumjump();
	$text .= "</td>
	<td style=\"width:50%; text-align:right\">";

if($pref['anon_post'][1] == "1" || IsSet($_COOKIE['userkey'])){
	if($thread_active_m == 1){
	$text .= "<a href=\"".$_SERVER['PHP_SELF']."?rp.".$forum_id.".".$thread_id."\"><img src=\"themes/shared/forum/reply.png\" alt=\"\" style=\"border:0\" /></a>
 <a href=\"".$_SERVER['PHP_SELF']."?nt.".$forum_id.".".$thread_id."\"><img src=\"themes/shared/forum/newthread.png\" alt=\"\" style=\"border:0\" /></a>";
	}else{
		$text .= "<a href=\"".$_SERVER['PHP_SELF']."?nt.".$forum_id.".".$thread_id."\"><img src=\"themes/shared/forum/newthread.png\" alt=\"\" style=\"border:0\" /></a>";
	}
}else{
	$text .= LAN_59;
}
	$text .= "</td>
	</tr>
	</table>";

	$ns -> tablerender("", $text);
	
	$sql -> db_Update("user", "user_new='$user_new' WHERE user_sess='".session_id()."' ");

}

//############################################################################################# reply

if($action == "rp"  || $action == "quote"){

	if(ANON == FALSE && USER == FALSE){
		$text .= LAN_45;
		$ns -> tablerender("Error!", $text);
		require_once(FOOTERF);
		exit;
	}

	$sql -> db_Select("forum", "*", "forum_id='".$forum_id."' ");
	list($forum_id, $forum_name, $forum_description, $parent, $forum_datestamp, $forum_active, $forum_moderators) = $sql-> db_Fetch();
	$sql -> db_Select("forum_t", "*", "thread_id='".$thread_id."' ");
	list($thread_id, $thread_name, $thread_thread, $thread_forum_id, $thread_datestamp, $thread_parent, $thread_user, $thread_views, $thread_active) = $sql -> db_Fetch();

	if($action == "quote"){
		$post = stripslashes(eregi_replace("<div style=\"text-align:right\">\[.*", "", $thread_thread));
		$lp = explode(".", $thread_user);
		if(ereg("[0-9]+", $lp[0])){
			$post_author_id = $lp[0];
			$post_author_name = $lp[1];
		}
		$sql2 = new db;
		$sql2 -> db_Select("user", "*",  "user_name='".$post_author_name."' ");
		list($user_id, $user_name, $null, $user_sess_, $user_email, $user_homepage, $user_icq, $user_aim, $user_msn, $user_location, $user_birthday, $user_signature, $user_image, $user_timezone, $user_hideemail, $user_join, $user_lastvisit, $user_currentvisit, $user_lastpost, $user_chats, $user_comments, $user_forums, $user_ip, $user_ban, $user_prefs, $user_visits)  = $sql2 -> db_Fetch();
		$post = "[i]".LAN_72." ".$post_author_name." ...\n".$post."[/i]\n\n";
}

	$text = "<div style=\"text-align:center\"><img src=\"themes/shared/forum/forumlogo.png\" alt=\"\" /></div>
	<br />
	<a href=\"index.php\">".SITENAME."</a> -> <a href=\"forum.php\">Forums</a> -> <a href=\"forum.php?forum.".$forum_id."\">".$forum_name."</a> -> Re: ".$thread_name;
	$ns -> tablerender("", $text);

		if($action == "quote"){
			if($thread_parent == 0){
				$thread_parent = $thread_id;
			}

			$text .= "
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?rp.".$forum_id.".".$thread_parent."\">\n
<table style=\"width:95%\">";
		}else{
			$text .= "
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."\">\n
<table style=\"width:95%\">";
		}


if(ANON == true && USERNAME == FALSE){
		$text .= "<tr>
<td style=\"width:20%\">".LAN_61."</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"anonname\" size=\"60\" value=\"$anonname\" maxlength=\"100\" />
</td>
</tr>";
	}


$text .= "<tr>
<td style=\"width:20%\">".LAN_73."</td>
<td style=\"width:80%\">

<textarea class=\"tbox\" name=\"post\" cols=\"70\" rows=\"10\">$post</textarea>
</td>
</tr>\n
<tr style=\"vertical-align:top\"> 
<td style=\"width:20%\"></td>
<td style=\"width:80%\">
<input class=\"button\" type=\"submit\" name=\"reply\" value=\"".LAN_74."\" />
<br />
<br />
<span class=\"smalltext\">
".LAN_10."
</span>
</td>
</tr>
</table>
</form>";


$ns -> tablerender(LAN_75, $text);

$text = "<table style=\"width:95%\">
<tr>
<td style=\"width:50%\">";
$text .= forumjump();
$text .= "</td></tr></table>";
$ns -> tablerender("", $text);

}

//############################################################################################# edit

if($action == "edit"){

	$sql -> db_Select("forum", "*", "forum_id='".$forum_id."' ");
	list($forum_id, $forum_name, $forum_description, $parent, $forum_datestamp, $forum_active, $forum_moderators) = $sql-> db_Fetch();
	$sql -> db_Select("forum_t", "*", "thread_id='".$thread_id."' ");
	list($thread_id, $subject, $post, $thread_forum_id, $thread_datestamp, $thread_parent, $thread_user, $thread_views, $thread_active) = $sql -> db_Fetch();

	$text = "<div style=\"text-align:center\"><img src=\"themes/shared/forum/forumlogo.png\" alt=\"\" /></div>
	<br />
	<a href=\"index.php\">".SITENAME."</a> -> <a href=\"forum.php\">Forums</a> >> <a href=\"forum.php?forum.".$forum_id."\">".$forum_name."</a> >> ".LAN_68." ";
	if($thread_parent == 0){
		$text .= LAN_53;
	}else{
		$text .= LAN_76;
	}
	$ns -> tablerender("", $text);

	$post = stripslashes(eregi_replace("<div style=\"text-align:right\">\[.*", "", $post));


	$text = "
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."\">\n
<table style=\"width:95%\">";
	if($thread_parent == 0){
		$text .= "<tr>
<td style=\"width:20%\">".LAN_62."</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"subject\" size=\"60\" value=\"$subject\" maxlength=\"100\" />
</td>
</tr>";
	}
		$text .= "<tr> 
<td style=\"width:20%\">".LAN_63."</td>
<td style=\"width:80%\">
<textarea class=\"tbox\" name=\"post\" cols=\"70\" rows=\"10\">$post</textarea>
</td>
</tr>\n
<tr style=\"vertical-align:top\"> 
<td style=\"width:20%\"></td>
<td style=\"width:80%\">";
		if($thread_parent == 0){
			$text .= "<input class=\"button\" type=\"submit\" name=\"edit\" value=\"".LAN_77."\" />";
		}else{
			$text .= "<input class=\"button\" type=\"submit\" name=\"edit\" value=\"".LAN_78."\" />
<input type=\"hidden\" name=\"thread_id\" value=\"$thread_parent\">";
		}
		$text .= "<br />
<br />
<span class=\"smalltext\">
".LAN_10."
</span>
</td>
</tr>
</table>
</form>";

$ns -> tablerender("<div style=\"text-align:center\"><a href=\"forum.php\">Forums</a> >> <a href=\"forum.php?forum.".$forum_id."\">".$forum_name."</a> >> ".LAN_68."</div>", $text);

}

//############################################################################################# functions

function forumjump(){
	$sql = new db;
	$sql -> db_Select("forum", "*", "forum_parent !=0");
	$c=0;
	$text .= "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
<p>
Jump: 
<select name=\"forumjump\" class=\"tbox\">";
	while(list($forum_id, $forum_name) = $sql -> db_Fetch()){
		$text .= "\n<option>".$forum_name."</option>";
	}
	$text .= "</select>
<input class=\"button\" type=\"submit\" name=\"fjsubmit\" value=\"Go\" />
</p>
</form>";
	return $text;
}

function getnew($forum_id, $user_new){
	$sql3 = new db;		
	$sql3 -> db_Select("forum_t", "*", "thread_forum_id='".$forum_id."' ");
	while(list($thread_id_) = $sql3 -> db_Fetch()){
		if(ereg("\.".$thread_id_."\.", $user_new)){
			return TRUE;
		}
	}
	return FALSE;
}

$text = "<div style=\"text-align:center\">
<img src=\"themes/shared/forum/new.png\" alt=\"\" /> ".LAN_79."
<img src=\"themes/shared/generic/trans.gif\" alt=\"\" width=\"20px\" />
<img src=\"themes/shared/forum/nonew.png\" alt=\"\" />".LAN_80."
<img src=\"themes/shared/generic/trans.gif\" alt=\"\" width=\"20px\" />
<img src=\"themes/shared/forum/closed.png\" alt=\"\" /> ".LAN_81."
</div>";

$ns -> tablerender("", $text);

require_once(FOOTERF);

?>