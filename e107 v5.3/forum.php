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
$captionlinkcolour = "#fff";

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
			if($user == "."){
				$user = $user = "0.Anonymous";
			}
		}
		if($user == "."){
			$user = "0.Anonymous";
		}
		$subject = $aj -> tp($_POST['subject']);
		$post = $aj -> tp($_POST['post']);
		$lastpost = $user.".".time();
		$sql -> db_Insert("forum_t", "0, '".$subject."', '".$post."', '$forum_id', '".time()."', '0', '$user', 0, 1, '".time()."', 0 ");
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
		$sql -> db_Insert("forum_t", "0, '', '".$post."', '$forum_id', '".time()."', '".$thread_id."', '$user', 0, 1, '".time()."', 0 ");
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
		$post .= "<div style=\"text-align:right\">[ ".LAN_29." ".$datestamp." ]</div>";
		$sql -> db_Update("forum_t", "thread_name='".$subject."', thread_thread='".$post."' WHERE thread_id='$thread_id' ");
		header("location: forum.php?view.".$forum_id.".".$thread_id);
	}
}

if($_POST['edit'] == "Update Reply"){
	if($_POST['post'] == ""){
		$error = "<div style=\"text-align:center\">".LAN_28."</div>";
	}else{
		$post = $aj -> tp($_POST['post']);
		$datestamp = $gen->convert_date(time(), "forum");
		$post .= "<div style=\"text-align:right\">[ ".LAN_29." ".$datestamp." ]</div>";
		$sql -> db_Update("forum_t", "thread_thread='".$post."' WHERE thread_id='$thread_id' ");
		header("location: forum.php?view.".$forum_id.".".$_POST['thread_id']);
	}
}

if($action == "maar"){
$sql -> db_Select("forum_t", "*",  "thread_datestamp>'".USERLV."' ");
	while($row = $sql -> db_Fetch()){
		extract($row);
		$read .= ".".$thread_id.".";
	}
	$sql -> db_Update("user", "user_viewed='$read' WHERE user_id='".USERID."' ");
	header("location: forum.php");
}

require_once(HEADERF);
unset($text);
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

	echo "
<table style=\"width:100%\" class=\"fborder\">
<tr>
<td colspan=\"2\" style=\"width:60%; text-align:center\" class=\"fcaption\">".LAN_46."</td>
<td style=\"width:10%; text-align:center\" class=\"fcaption\">".LAN_47."</td>
<td style=\"width:10%; text-align:center\" class=\"fcaption\">".LAN_48."</td>
<td style=\"width:20%; text-align:center\" class=\"fcaption\">".LAN_49."</td>
</tr>";

	$forum_parents = $sql -> db_Select("forum", "*", "forum_parent='0' ");
	if($forum_parents == 0){
		$text .= "<tr><td>".LAN_51."</td></tr>";
	}else{
		$sql2 = new db; $sql3 = new db;
		while(list($forum_id, $forum_name, $forum_description, $forum_parent, $forum_datestamp, $forum_active, $forum_moderators, $forum_threads, $forum_replies) = $sql-> db_Fetch()){
			if($forum_active == 1){
				$text .= "<tr><td colspan=\"5\" class=\"forumheader\">".$forum_name."</td></tr>";
				$parent_inactive == FALSE;
			}else if($forum_active == 0){
				if($sql2 -> db_Select("forum", "*", "forum_parent='".$forum_id."' AND forum_active=1")){
					$text .= "<tr><td colspan=\"5\" class=\"forumheader\">".$forum_name." <b>(Inactive)</b></td></tr>";
				}
				$parent_inactive == TRUE;
				break;
			}else if($forum_active == 2 && USERCLASS_PRIVATEFORUM == TRUE){
				$text .= "<tr><td colspan=\"5\" class=\"forumheader\">".$forum_name." (Private Forums)</td></tr>";
			}

			$forums = $sql2 -> db_Select("forum", "*", "forum_parent='".$forum_id."' ");
			if($forums == 0){
				$text .= "<td colspan=\"5\" align=\"center\">".LAN_52."<br /><br /></td>";
			}else{
				while(list($forum_id, $forum_name, $forum_description, $parent, $forum_datestamp, $forum_active, $forum_moderators, $null, $null, $forum_lastpost) = $sql2-> db_Fetch()){

			$forum_threads = $sql3 -> db_Count("forum_t", "(*)", "WHERE thread_forum_id='$forum_id' AND thread_parent='0' ");
			$forum_replies = $sql3 -> db_Count("forum_t", "(*)", "WHERE thread_forum_id='$forum_id' AND thread_parent!='0' ");

			$newflag = FALSE;
			if($sql3 -> db_SELECT("forum_t", "*", "thread_forum_id='$forum_id' AND thread_datestamp > '".USERLV."' ")){
				while(list($nthread_id) = $sql3 -> db_Fetch()){
					if(!ereg("\.".$nthread_id."\.", USERVIEWED)){
						$newflag = TRUE;
					}
				}
			}

			if(($forum_active == 1 && $parent_inactive == FALSE) || ($forum_active == 2 && USERCLASS_PRIVATEFORUM == TRUE)){

				if($newflag == TRUE){
					$text .= "<tr><td style=\"width:5%; text-align:center\" class=\"forumheader2\"><img src=\"themes/shared/forum/new.png\" alt=\"\" /></td>";
				}else{
					$text .= "<tr><td style=\"width:5%; text-align:center\" class=\"forumheader2\"><img src=\"themes/shared/forum/nonew.png\" alt=\"\" /></td>";
				}
	$text .= "<td style=\"width:55%\" class=\"forumheader2\"><a href=\"forum.php?forum.".$forum_id."\">".$forum_name."</a><br /><span class=\"smallblacktext\">".$forum_description."</span></td>
	<td style=\"width:10%; text-align:center\" class=\"forumheader3\">".$forum_threads."</td>
	<td style=\"width:10%; text-align:center\" class=\"forumheader3\">".$forum_replies."</td>
	<td style=\"width:20%; text-align:center\" class=\"forumheader3\">";


						if($forum_threads == 0 && $forum_replies == 0){
							$text .= "No posts yet</td>";
						}else{
							$lp = explode(".", $forum_lastpost);
							if(ereg("[0-9]+", $lp[0])){
								$lastpost_author_id = $lp[0];
								$lastpost_author_name = $lp[1];
								$lastpost_datestamp = $lp[2];
								$lastpost_datestamp = $gen->convert_date($lastpost_datestamp, "forum");
								$text .= $lastpost_datestamp."<br /><a href=\"user.php?id.".$lastpost_author_id."\">".$lastpost_author_name."</a></td>";
							}
						}

						$text .= "</tr>";
					}
				}
			}
		}
	}
	$text .= "</table>";
	echo $text;


// info bar ...

	$text = "<br /><table style=\"width:100%\" class=\"fborder\">
<tr>
<td colspan=\"2\" style=\"width:60%\" class=\"fcaption\">".LAN_191."</td>
</tr>
<tr>
<td rowspan=\"2\" style=\"width:5%; text-align:center\" class=\"forumheader3\"><img src=\"themes/shared/forum/e.png\" alt=\"\" /></td>
";

	if(USER == TRUE){

		$total_new_threads = $sql -> db_Count("forum_t", "(*)", "WHERE thread_datestamp>'".USERLV."' ");
		if(USERVIEWED != ""){
			$tmp = explode("..", USERVIEWED);
			$total_read_threads = count($tmp);
		}else{
			$total_read_threads = 0;
		}

		$text .= "<td style=\"width:95%\" class=\"forumheader3\">
		".LAN_30." ".USERNAME."<br />";
		$sql -> db_Select("user", "*",  "user_name='".USERNAME."' ");
		$row = $sql -> db_Fetch();
		extract($row);
		$lastvisit_datestamp = $gen->convert_date($user_lastvisit, "long");
		$datestamp = $gen->convert_date(time(), "long");
		if(!$total_new_threads){
			$text .= LAN_31;
		}else if($total_new_threads == 1){
			$text .= LAN_32;
		}else{
			$text .= LAN_33." ".$total_new_threads." ".LAN_34." ";
		}
		$text .= LAN_35;
		if($total_new_threads == $total_read_threads && $total_new_threads !=0){
			$text .= LAN_198;
		}else if($total_read_threads != 0){
			$text .= " (".LAN_196.$total_read_threads.LAN_197.")";
		}

		$text .= "<br />
		".LAN_36." ".$lastvisit_datestamp."<br />
		".LAN_37." ".$datestamp." ".LAN_38;
	}else{
		$text .= "<td style=\"width:95%\" class=\"forumheader3\">";
		if(ANON == TRUE && USER == FALSE){
			$text .= LAN_43;
		}else if(ANON == TRUE){
			$text .= LAN_44;
		}else if(USER == FALSE){
			$text .= LAN_45;
		}
	}

	if(!$total_new_threads == $total_read_threads && $total_new_threads !=0){
		$text .= "<br /><a href=\"forum.php?maar\">".LAN_199."</a>";
	}
$text .= "</td></tr><tr><td style=\"width:95%\" class=\"forumheader3\">

".LAN_192.($total_topics+$total_replies)." ".strtolower(LAN_100).".<br />".LAN_42.": ".$total_members."<br />".LAN_41."<a href=\"user.php?id.".$nuser_id."\">".$nuser_name."</a>.<br />

</td>
</tr>
</table>";
echo $text;

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

	if($forum_active == 0){
		echo "<div style=\"text-align:center\">Forum inactive</div>";
		require_once(FOOTERF);
		exit;
	}else if($forum_active == 2 && USERCLASS_PRIVATEFORUM != TRUE){
		echo "<div style=\"text-align:center\">You do not have the correct permissions to view this forum. If you feel this is a mistake please contact the main site administrator.</div>";
		require_once(FOOTERF);
		exit;
	}

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

	$text = "<table style=\"width:100%\" class=\"fborder\">
	<tr>
	<td  colspan=\"2\" class=\"fcaption\">
	<a style=\"color:$captionlinkcolour\" href=\"index.php\">".SITENAME."</a> >> <a style=\"color:$captionlinkcolour\" href=\"forum.php\">Forums</a> >> <b>".$forum_name."</b>
	</td>
	</tr>
	<tr>
	<td style=\"width:50%; vertical-align:bottom\">moderators: $forum_moderators";
	
	if($pages != 0){
		$text .= "<br />Go to page ";
		for($c=0; $c < $pages; $c++){
			if($view*$c == $from){
				$text .= "<u>".($c+1)."</u> ";
			}else{
				$text .= "<a href=\"".$_SERVER['PHP_SELF']."?forum.".$forum_id.".".($view*$c)."\">".($c+1)."</a> ";
			}
		}
	}

	$text .= "</td>
	<td style=\"width:50%; text-align:right\">";

	if(ANON == TRUE || IsSet($_SESSION['userkey'])){
		$text .= "<a href=\"".$_SERVER['PHP_SELF']."?nt.".$forum_id.".".$thread_id."\"><img src=\"themes/shared/forum/newthread.png\" alt=\"\" style=\"border:0\" /></a>";
	}
	$text .= "</td></tr><tr>
	<td colspan=\"2\">";

	echo $text;

	echo "
<table style=\"width:100%\" class=\"fborder\">
<tr>
<td style=\"width:3%\" class=\"fcaption\">&nbsp;</td>
<td style=\"width:47%\" class=\"fcaption\">".LAN_53."</td>
<td style=\"width:20%; text-align:center\" class=\"fcaption\">".LAN_54."</td>
<td style=\"width:5%; text-align:center\" class=\"fcaption\">".LAN_55."</td>
<td style=\"width:5%; text-align:center\" class=\"fcaption\">".LAN_56."</td>
<td style=\"width:20%; text-align:center\" class=\"fcaption\">".LAN_57."</td>
</tr>
";

	if($topics == 0){
		echo "<td colspan=\"6\" style=\"text-align:center\" class=\"forumheader2\">".LAN_58."</td>";
	}else{
		$sql -> db_Select("forum_t", "*",  "thread_forum_id='".$forum_id."' AND thread_parent='0' ORDER BY thread_s DESC, thread_lastpost DESC, thread_datestamp DESC LIMIT $from, $view");
		while(list($thread_id, $thread_name, $thread_thread, $thread_forum_id, $thread_datestamp, $thread_parent, $thread_user, $thread_views, $thread_active, $thread_lastpost, $thread_s) = $sql -> db_Fetch()){
			$sql2 = new db; $sql3 = new db; $gen = new convert;
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
			

			echo "<tr>
			<td style=\"vertical-align:center; width:3%\" class=\"forumheader3\">";

			$newflag = FALSE;
			if($thread_datestamp > USERLV && (!ereg("\.".$thread_id."\.", USERVIEWED))){
				$newflag = TRUE;
			}else if($sql3 -> db_SELECT("forum_t", "*", "thread_parent='$thread_id' AND thread_datestamp > '".USERLV."' ")){
				while(list($nthread_id) = $sql3 -> db_Fetch()){
					if(!ereg("\.".$nthread_id."\.", USERVIEWED)){
						$newflag = TRUE;
					}
				}
			}
			$thread_datestamp = $gen->convert_date($thread_datestamp, "forum");
			if($thread_s == 1){
				if($thread_active == 0){
					echo "<img src=\"themes/shared/forum/stickyclosed.png\" alt=\"\" />";
				}else{
					echo "<img src=\"themes/shared/forum/sticky.png\" alt=\"\" />";
				}
			}else if($thread_active == 0){
				echo "<img src=\"themes/shared/forum/closed.png\" alt=\"\" />";
			}else if($newflag == TRUE){
				echo "<img src=\"themes/shared/forum/new.png\" alt=\"\" />";
			}else{
				echo "<img src=\"themes/shared/forum/nonew.png\" alt=\"\" />";
			}

			echo "</td><td style=\"vertical-align:center; text-align:left; width:47%\"  class=\"forumheader3\"><span class=\"mediumtext\"><a href=\"".$_SERVER['PHP_SELF']."?view.".$forum_id.".".$thread_id."\">".$thread_name."</a></span>";

			if(eregi(ADMINNAME, $forum_moderators)){
				echo "<div class=\"smalltext\" style=\"text-align:right\">moderator options: ";
				if($thread_s == 1){
					echo "[ <a href=\"admin/forum_conf.php?unstick.".$forum_id.".".$thread_id."\">Unstick</a> ]";
					if($thread_active == 1){
						echo "[ <a href=\"admin/forum_conf.php?close.".$forum_id.".".$thread_id."\">".LAN_200."</a> ]";
					}else{
						echo "[ <a href=\"admin/forum_conf.php?open.".$forum_id.".".$thread_id."\">".LAN_201."</a> ]";
					}
				}else{
					echo "[ <a href=\"admin/forum_conf.php?stick.".$forum_id.".".$thread_id."\">Stick</a> ]";
					if($thread_active == 1){
						echo "[ <a href=\"admin/forum_conf.php?close.".$forum_id.".".$thread_id."\">".LAN_200."</a> ]";
					}else{
						echo "[ <a href=\"admin/forum_conf.php?open.".$forum_id.".".$thread_id."\">".LAN_201."</a> ]";
					}
					echo "</div>";
				}
			}
			
			echo "</td>
<td style=\"vertical-align:top; text-align:center; width:20%\" class=\"forumheader3\">".$thread_datestamp."<br /><a href=\"user.php?id.".$post_author_id."\">".$post_author_name."</a></td>
<td style=\"vertical-align:center; text-align:center; width:5%\" class=\"forumheader3\">$replies</td>
<td style=\"vertical-align:center; text-align:center; width:5%\" class=\"forumheader3\">$thread_views</td>
<td style=\"vertical-align:top; text-align:center; width:20%\" class=\"forumheader3\">$lastreply</td>
</tr>";
		}
		echo "</table>";
	}

	$text = "<table style=\"width:100%\">
	<tr>
	<td style=\"width:50%\">";
	if($pages != 0){
		$text .= "Go to page ";
		for($c=0; $c < $pages; $c++){
			if($view*$c == $from){
				$text .= "<u>".($c+1)."</u> ";
			}else{
				$text .= "<a href=\"".$_SERVER['PHP_SELF']."?forum.".$forum_id.".".($view*$c)."\">".($c+1)."</a> ";
			}
		}
		$text .= "<br />";
	}
	$text .= forumjump();
	$text .= "</td>
	<td style=\"width:50%; text-align:right\">";

if(ANON == TRUE || IsSet($_SESSION['userkey'])){
	$text .= "<a href=\"".$_SERVER['PHP_SELF']."?nt.".$forum_id.".".$thread_id."\"><img src=\"themes/shared/forum/newthread.png\" alt=\"\" style=\"border:0\" /></a>";
}else{
	$text .= LAN_59;
}
	$text .= "</td>
	</tr>
	</table>";

	echo $text;
	echo "</td></tr></table>";
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

	echo "<div class=\"captiontext\">
	<a href=\"index.php\">".SITENAME."</a> -> <a href=\"forum.php\">Forums</a> >> <a href=\"forum.php?forum.".$forum_id."\"><b>".$forum_name."</b></a></div>";

	echo "
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."\" name=\"newthread\">\n
<table style=\"width:100%\" class=\"fborder\">
<tr><td colspan=\"3\" class=\"fcaption\">".LAN_60."</td></tr>";

	if(ANON == TRUE  && USER == FALSE){
		echo "<tr>
<td class=\"forumheader2\" style=\"width:20%\">".LAN_61."</td>
<td class=\"forumheader2\" colspan=\"2\">
<input class=\"tbox\" type=\"text\" name=\"anonname\" size=\"60\" value=\"$anonname\" maxlength=\"100\" />
</td>
</tr>";
	}

	echo "<tr>
<td class=\"forumheader2\" style=\"width:20%\">".LAN_62."</td>
<td class=\"forumheader2\" colspan=\"2\">
<input class=\"tbox\" type=\"text\" name=\"subject\" size=\"60\" value=\"$subject\" maxlength=\"100\" />
</td>
</tr>
<tr> 
<td class=\"forumheader2\" style=\"width:20%\">".LAN_63."</td>
<td class=\"forumheader2\" style=\"width:60%\">
<textarea class=\"tbox\" name=\"post\" cols=\"70\" rows=\"10\"></textarea>
<br />
<input class=\"fhelpbox\" type=\"text\" name=\"helpb\" size=\"90\" />
<br />
<input class=\"button\" type=\"button\" style=\"font-weight:bold; width: 35px\" value=\"b\" onclick=\"addtext('[b][/b]')\" onMouseOver=\"help('Bold text: [b]This text will be bold[/b]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" style=\"font-style:italic; width: 35px\" value=\"i\" onclick=\"addtext('[i][/i]')\" onMouseOver=\"help('Italic text: [i]This text will be italicised[/i]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" style=\"text-decoration: underline; width: 35px\" value=\"u\" onclick=\"addtext('[u][/u]')\" onMouseOver=\"help('Underline text: [u]This text will be underlined[/u]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" style=\"width: 35px\" value=\"img\" onclick=\"addtext('[img][/img]')\" onMouseOver=\"help('Insert image: [img]mypicture.jpg[/img]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" style=\"width: 35px\" value=\"cen\" onclick=\"addtext('[center][/center]')\" onMouseOver=\"help('Center align: [center]This text will be centered[/center]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"link\" onclick=\"addtext('[link=hyperlink url]hyperlink text[/link]')\" onMouseOver=\"help('Insert link: [link]http://mysite.com[/link] or  [link=http://yoursite.com]Visit My Site[/link]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" style=\"width: 35px\" value=\"code\" onclick=\"addtext('[code][/code]')\" onMouseOver=\"help('Code - preformatted text: [code]\$var = foobah;[/code]')\" onMouseOut=\"help('')\">
</td>

<td class=\"forumheader2\">
<div style=\"text-align:center\">Emoticons<br /><br />
<a href=\"javascript:addtext(':)')\"><img src=\"themes/shared/emoticons/smile.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext(':(')\"><img src=\"themes/shared/emoticons/frown.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext(':D')\"><img src=\"themes/shared/emoticons/grin.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext(':?')\"><img src=\"themes/shared/emoticons/confused.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext(':((')\"><img src=\"themes/shared/emoticons/cry.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('%-6')\"><img src=\"themes/shared/emoticons/special.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('X)')\"><img src=\"themes/shared/emoticons/dead.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext(':@')\"><img src=\"themes/shared/emoticons/gah.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('~:\(')\"><img src=\"themes/shared/emoticons/mad.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext(':!')\"><img src=\"themes/shared/emoticons/idea.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext(':|')\"><img src=\"themes/shared/emoticons/neutral.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('?!')\"><img src=\"themes/shared/emoticons/question.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('B)')\"><img src=\"themes/shared/emoticons/rolleyes.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('8)')\"><img src=\"themes/shared/emoticons/shades.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext(':O')\"><img src=\"themes/shared/emoticons/suprised.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext(':P')\"><img src=\"themes/shared/emoticons/tongue.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext(';)')\"><img src=\"themes/shared/emoticons/wink.png\" style=\"border:0\" alt=\"\" />

<a href=\"javascript:addtext('!ill')\"><img src=\"themes/shared/emoticons/ill.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('!amazed')\"><img src=\"themes/shared/emoticons/amazed.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('!cry')\"><img src=\"themes/shared/emoticons/cry.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('!dodge')\"><img src=\"themes/shared/emoticons/dodge.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('!alien')\"><img src=\"themes/shared/emoticons/alien.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('!heart')\"><img src=\"themes/shared/emoticons/heart.png\" style=\"border:0\" alt=\"\" />

</td>

</tr>\n
<tr style=\"vertical-align:top\"> 

<td colspan=\"3\" class=\"forumheader\" style=\"text-align:center\">
<input class=\"button\" type=\"submit\" name=\"newthread\" value=\"".LAN_64."\" />
</td>
</tr>
<tr>
<td colspan=\"3\"
<span class=\"smalltext\">
".LAN_10."
</span>
</td>
</tr>
</table>
</form>";


echo $text;

$text = "<table style=\"width:95%\">
<tr>
<td style=\"width:50%\">";
$text .= forumjump();
$text .= "</td></tr></table>";
echo $text;

}

//############################################################################################# view

if($action == "view"){

	$sql -> db_Update("forum_t", "thread_views=thread_views+1 WHERE thread_id='$thread_id' ");

	$sql -> db_Select("forum", "*", "forum_id='".$forum_id."' ");
	list($forum_id, $forum_name, $forum_description, $parent, $forum_datestamp, $forum_active, $forum_moderators) = $sql-> db_Fetch();
	$sql -> db_Select("forum_t", "*", "thread_id='".$thread_id."' ORDER BY thread_datestamp DESC ");
	list($thread_id, $thread_name, $thread_thread, $thread_forum_id, $thread_datestamp, $thread_parent, $thread_user, $thread_views, $thread_active) = $sql -> db_Fetch();

	
	//echo "<div class=\"captiontext\"><a href=\"index.php\">".SITENAME."</a> -> <a href=\"forum.php\">Forums</a> -> <a href=\"forum.php?forum.".$forum_id."\"><b>".$forum_name."</b></a> -><br /><b>".$thread_name."</b></div>

	echo "<table style=\"width:100%\" class=\"fborder\">
	<tr>
	<td  colspan=\"2\" class=\"fcaption\"><a style=\"color:$captionlinkcolour\" href=\"index.php\">".SITENAME."</a> -> <a style=\"color:$captionlinkcolour\" href=\"forum.php\">Forums</a> -> <a style=\"color:$captionlinkcolour\" href=\"forum.php?forum.".$forum_id."\">".$forum_name."</a> -> ".$thread_name."</td>
	
	</tr><tr><td style=\"width:50%; vertical-align:bottom\">";
	if(ADMIN == TRUE && eregi(ADMINNAME, $forum_moderators)){
		echo "<br />".LAN_65;
	}
	
	echo "</td><td style=\"width:50%; text-align:right\">";

	if(ANON == TRUE || IsSet($_SESSION['userkey'])){
		if($thread_active != 0){
			echo "<a href=\"".$_SERVER['PHP_SELF']."?rp.".$forum_id.".".$thread_id."\"><img src=\"themes/shared/forum/reply.png\" alt=\"\" style=\"border:0\" /></a>";
		}
		echo "<a href=\"".$_SERVER['PHP_SELF']."?nt.".$forum_id.".".$thread_id."\"><img src=\"themes/shared/forum/newthread.png\" alt=\"\" style=\"border:0\" /></a>";
	}

	echo "</td></tr><tr><td colspan=\"2\">";

	if($thread_active == 0){
		echo "<div class=\"mediumtext\"  style=\"text-align:center\"><b>".LAN_66."</b></div>";
	}

	$text = "<table style=\"width:100%\" class=\"fborder\">
	<tr>
	<td style=\"width:20%; text-align:center\" class=\"fcaption\">Author</td>
	<td style=\"width:80%; text-align:center\" class=\"fcaption\">Post</td>
	</tr>";


	$lp = explode(".", $thread_user);
	if(ereg("[0-9]+", $lp[0])){
		$post_author_id = $lp[0];
		$post_author_name = $lp[1];


	$lp = explode(".", $thread_user);
		if(ereg("[0-9]+", $lp[0])){
			$post_author_id = $lp[0];
			$post_author_name = $lp[1];
			if($post_author_name == ""){
				$sql2 -> db_Select("user", "*", "user_id='$post_author_id' ");
				list($null, $post_author_name) = $sql2 -> db_Fetch();
			}
		}
	}


//	$thread_thread = stripslashes(nl2br($thread_thread));
	$starter_count = $sql -> db_Count("forum_t", "(*)", " WHERE thread_user='$thread_user' OR thread_user='$post_author_name' ");
	
	$sql -> db_Select("user", "*", "user_name='".$post_author_name."' ");
	list($user_id, $user_name, $null, $user_sess_, $user_email, $user_homepage, $user_icq, $user_aim, $user_msn, $user_location, $user_birthday, $user_signature, $user_image, $user_timezone, $user_hideemail, $user_join, $user_lastvisit, $user_currentvisit, $user_lastpost, $user_chats, $user_comments, $user_forums, $user_ip, $user_ban, $user_prefs, $user_new_, $null, $user_visits)  = $sql -> db_Fetch();

	$text .= "
<tr> 
<td style=\"width:20%; vertical-align:top\" class=\"forumtable2\">";

$newflag = FALSE;
if($thread_datestamp > USERLV && (!ereg("\.".$thread_id."\.", USERVIEWED))){
	$newflag = TRUE;
}
if($newflag == TRUE){
	$text .= "<img src=\"themes/shared/forum/new.png\" alt=\"\" /><br />";
	$u_new .= ".".$thread_id.".";
}

$user_new = ereg_replace("\.".$thread_id."\.", ".", $user_new);
// ------------------------------------------------------------- thread starter info
$text .= "<div class=\"mediumtext\"><a href=\"user.php?id.".$post_author_id."\"><b>".$post_author_name."</b></a></div>";
if($user_image != ""){
	if(ereg("avatar_", $user_image)){
		$avatarlist[0] = "";
		$handle=opendir("themes/shared/avatars/");
		while ($file = readdir($handle)){
			if($file != "." && $file != ".."){
				$avatarlist[] = $file;
			}
		}
		$user_image = "themes/shared/avatars/".$avatarlist[substr(strrchr($user_image, "_"), 1)];
	}

	$text .= "<div class=\"spacer\">
<img src=\"".$user_image."\" alt=\"\" />
</div>";
}

$text .= "<div class=\"smallblacktext\">";
if($user_id == ""){
	$text .= LAN_194."<br /><br />";
}else if(eregi($user_name, $forum_moderators)){
	$text .= "<b><u>".LAN_193."</u></b><br /><br />";	
}else{
	$user_join = $gen->convert_date($user_join, "forum");
	$text .= LAN_195."#$user_id<br />joined $user_join<br /><br />";
}
$text .= "</div>";

$thread_datestamp = $gen->convert_date($thread_datestamp, "forum");
if($user_id != ""){
	$text .= LAN_67.": $starter_count";
}

$thread_thread = $aj -> tpa($thread_thread, $mode="off");

$text .= "</td>
<td colspan=\"2\" style=\"width:80%; vertical-align:top\" class=\"forumtable2\">
".$thread_thread;

if($user_signature != ""){
	$user_signature = $aj -> tpa($user_signature);
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
if($user_homepage != "" && $user_homepage != "http://"){
	$text .= "<a href=\"$user_homepage\"><img src=\"themes/shared/forum/website.png\" alt=\"\" style=\"border:0\" /></a> ";
}

if($post_author_name == USERNAME && $thread_active == 1){
	$text .= "<img src=\"themes/shared/generic/trans.gif\" alt=\"\" style=\"width:30px; height:1px\" />
<a href=\"forum.php?quote.".$forum_id.".".$thread_id."\"><img src=\"themes/shared/forum/quote.png\" alt=\"\" style=\"border:0\" /></a>
 <a href=\"forum.php?edit.".$forum_id.".".$thread_id."\"><img src=\"themes/shared/forum/edit.png\" alt=\"\" style=\"border:0\" /></a></td>";
}else if($thread_active == 1){
$text .= "<img src=\"themes/shared/generic/trans.gif\" alt=\"\" style=\"width:30px; height:1px\" />
<a href=\"forum.php?quote.".$forum_id.".".$thread_id."\"><img src=\"themes/shared/forum/quote.png\" alt=\"\" style=\"border:0\" /></a>";
}

$text .= "</td>
</tr>
";

$ta = $thread_active;

//@^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^@//

if(!$sql -> db_Select("forum_t", "*", "thread_parent='".$thread_id."' ORDER BY thread_datestamp ASC")){
	//$text .= "<div style=\"text-align:center\">".LAN_71."</div>";
}else{

	while(list($reply_id, $thread_name, $thread_thread, $thread_forum_id, $thread_datestamp, $thread_parent, $thread_user, $thread_views, $thread_active, $thread_lastpost, $thread_s) = $sql -> db_Fetch()){
		$thread_thread = stripslashes(nl2br($thread_thread));
		$sql2 = new db;

		$lp = explode(".", $thread_user);
		if(ereg("[0-9]+", $lp[0])){
			$post_author_id = $lp[0];
			$post_author_name = $lp[1];
			if($post_author_name == ""){
				$sql2 -> db_Select("user", "*", "user_id='$post_author_id' ");
				list($null, $post_author_name) = $sql2 -> db_Fetch();
			}
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
		$text .= "<tr><td style=\"width:20%; vertical-align:top\" class=\"$forumtable\">";


$newflag = FALSE;
if($thread_datestamp > USERLV && (!ereg("\.".$reply_id."\.", USERVIEWED))){
	$newflag = TRUE;
	$u_new .= ".".$reply_id.".";
}

		if($newflag == TRUE){
//			$user_new = ereg_replace("\.".$reply_id."\.", ".", $user_new);
			$text .= "<img src=\"themes/shared/forum/new.png\" alt=\"\" />";
		}
		$text .= "<div class=\"mediumtext\"><a href=\"user.php?id.".$post_author_id."\"><b>".$post_author_name."</b></a></div>";
if($user_image != ""){
	if(ereg("avatar_", $user_image)){
		$avatarlist[0] = "";
		$handle=opendir("themes/shared/avatars/");
		while ($file = readdir($handle)){
			if($file != "." && $file != ".."){
				$avatarlist[] = $file;
			}
		}
		$user_image = "themes/shared/avatars/".$avatarlist[substr(strrchr($user_image, "_"), 1)];
	}

	$text .= "<div class=\"spacer\">
<img src=\"".$user_image."\" alt=\"\" />
</div>";
}
$thread_datestamp = $gen->convert_date($thread_datestamp, "forum");
$thread_thread = $aj -> tpa($thread_thread);
$text .= "<div class=\"smallblacktext\">";
if(eregi($post_author_name, $forum_moderators)){
	$text .= "<b><u>".LAN_193."</u></b><br /><br />";
}else if($user_id == ""){
	$text .= LAN_194."<br /><br />";
}else{
	$user_join = $gen->convert_date($user_join, "forum");
	$text .= LAN_195."#$user_id<br />joined $user_join<br /><br />";
}
$text .= "</div>";
$text .= LAN_67.": $replier_count
</td>
<td colspan=\"2\" style=\"width:80%; vertical-align:top\" class=\"$forumtable\">
".$thread_thread;

if($user_signature != ""){
	$user_signature = $aj -> tpa($user_signature);
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
if($user_homepage != "" && $user_homepage != "http://"){
	$text .= "<a href=\"$user_homepage\"><img src=\"themes/shared/forum/website.png\" alt=\"\" style=\"border:0\" /></a> ";
}

if($post_author_name == USERNAME && $thread_active == 1){
	$text .= "<img src=\"themes/shared/generic/trans.gif\" alt=\"\" style=\"width:30px; height:1px\" />
<a href=\"forum.php?quote.".$forum_id.".".$reply_id."\"><img src=\"themes/shared/forum/quote.png\" alt=\"\" style=\"border:0\" /></a>
 <a href=\"forum.php?edit.".$forum_id.".".$reply_id."\"><img src=\"themes/shared/forum/edit.png\" alt=\"\" style=\"border:0\" /></a></td>";
}else if($thread_active == 1){
$text .= "<img src=\"themes/shared/generic/trans.gif\" alt=\"\" style=\"width:30px; height:1px\" />
<a href=\"forum.php?quote.".$forum_id.".".$reply_id."\"><img src=\"themes/shared/forum/quote.png\" alt=\"\" style=\"border:0\" /></a>";
}

$ta = $thread_active;

$text .= "</td>
</tr>";
	}
	$text .= "</table>";
}
echo $text;

	$text = "<table style=\"width:100%\">
	<tr>
	<td style=\"width:50%\">";
	$text .= forumjump();
	$text .= "</td>
	<td style=\"width:50%; text-align:right\">";

if($pref['anon_post'][1] == "1" || USER == TRUE){
	if($ta != 0){
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

	echo $text;
	
	echo "</td></tr></table>";	

//	$sql -> db_Update("user", "user_new='$user_new' WHERE user_sess='".session_id()."' ");

}

$u_new = USERVIEWED . $u_new;
if($u_new != ""){ $sql -> db_Update("user", "user_viewed='$u_new' WHERE user_id='".USERID."' "); }
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
		$post = "[quote=$post_author_name]".$post."[/quote]\n\n";
}

	echo "<div class=\"captiontext\">
	<a href=\"index.php\">".SITENAME."</a> -> <a href=\"forum.php\">Forums</a> -> <a href=\"forum.php?forum.".$forum_id."\">".$forum_name."</a> -> Re: ".$thread_name."</div>";

		if($action == "quote"){
			if($thread_parent == 0){
				$thread_parent = $thread_id;
			}

			echo "
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?rp.".$forum_id.".".$thread_parent."\" name=\"newthread\">\n
<table style=\"width:100%\" class=\"fborder\">";
		}else{
			echo "
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."\" name=\"newthread\">\n
<table style=\"width:100%\" class=\"fborder\">";
		}

echo "<tr><td colspan=\"3\" class=\"fcaption\">".LAN_75."</td></tr>";

if(ANON == true && USER == FALSE){
		echo "<tr>
<td class=\"forumheader2\" style=\"width:20%\">".LAN_61."</td>
<td class=\"forumheader2\" colspan=\"2\">
<input class=\"tbox\" type=\"text\" name=\"anonname\" size=\"60\" value=\"$anonname\" maxlength=\"100\" />
</td>
</tr>";
	}


echo "<tr>
<td class=\"forumheader2\" style=\"width:20%\">".LAN_73."</td>
<td class=\"forumheader2\" style=\"width:60%\">

<textarea class=\"tbox\" name=\"post\" cols=\"70\" rows=\"10\">$post</textarea>
<br />
<input class=\"fhelpbox\" type=\"text\" name=\"helpb\" size=\"80\" />
<br />
<input class=\"button\" type=\"button\" style=\"font-weight:bold; width: 35px\" value=\"b\" onclick=\"addtext('[b][/b]')\" onMouseOver=\"help('Bold text: [b]This text will be bold[/b]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" style=\"font-style:italic; width: 35px\" value=\"i\" onclick=\"addtext('[i][/i]')\" onMouseOver=\"help('Italic text: [i]This text will be italicised[/i]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" style=\"text-decoration: underline; width: 35px\" value=\"u\" onclick=\"addtext('[u][/u]')\" onMouseOver=\"help('Underline text: [u]This text will be underlined[/u]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" style=\"width: 35px\" value=\"img\" onclick=\"addtext('[img][/img]')\" onMouseOver=\"help('Insert image: [img]mypicture.jpg[/img]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" style=\"width: 35px\" value=\"cen\" onclick=\"addtext('[center][/center]')\" onMouseOver=\"help('Center align: [center]This text will be centered[/center]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"link\" onclick=\"addtext('[link=hyperlink url]hyperlink text[/link]')\" onMouseOver=\"help('Insert link: [link]http://mysite.com[/link] or  [link=http://yoursite.com]Visit My Site[/link]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" style=\"width: 35px\" value=\"code\" onclick=\"addtext('[code][/code]')\" onMouseOver=\"help('Code - preformatted text: [code]\$var = foobah;[/code]')\" onMouseOut=\"help('')\">


</td>

<td class=\"forumheader2\">
<div style=\"text-align:center\">Emoticons<br /><br />
<a href=\"javascript:addtext(':)')\"><img src=\"themes/shared/emoticons/smile.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext(':(')\"><img src=\"themes/shared/emoticons/frown.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext(':D')\"><img src=\"themes/shared/emoticons/grin.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext(':?')\"><img src=\"themes/shared/emoticons/confused.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext(':((')\"><img src=\"themes/shared/emoticons/cry.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('%-6')\"><img src=\"themes/shared/emoticons/special.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('X)')\"><img src=\"themes/shared/emoticons/dead.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext(':@')\"><img src=\"themes/shared/emoticons/gah.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('~:\(')\"><img src=\"themes/shared/emoticons/mad.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext(':!')\"><img src=\"themes/shared/emoticons/idea.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext(':|')\"><img src=\"themes/shared/emoticons/neutral.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('?!')\"><img src=\"themes/shared/emoticons/question.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('B)')\"><img src=\"themes/shared/emoticons/rolleyes.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('8)')\"><img src=\"themes/shared/emoticons/shades.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext(':O')\"><img src=\"themes/shared/emoticons/suprised.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext(':P')\"><img src=\"themes/shared/emoticons/tongue.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext(';)')\"><img src=\"themes/shared/emoticons/wink.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('!ill')\"><img src=\"themes/shared/emoticons/ill.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('!amazed')\"><img src=\"themes/shared/emoticons/amazed.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('!cry')\"><img src=\"themes/shared/emoticons/cry.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('!dodge')\"><img src=\"themes/shared/emoticons/dodge.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('!alien')\"><img src=\"themes/shared/emoticons/alien.png\" style=\"border:0\" alt=\"\" />
<a href=\"javascript:addtext('!heart')\"><img src=\"themes/shared/emoticons/heart.png\" style=\"border:0\" alt=\"\" />
</td>
</tr>


</tr>\n
<tr style=\"vertical-align:top\"> 

<td colspan=\"3\" class=\"forumheader\" style=\"text-align:center\">
<input class=\"button\" type=\"submit\" name=\"reply\" value=\"".LAN_74."\" />
</td>
</tr>
<tr>
<td colspan=\"3\">
<span class=\"smalltext\">
".LAN_10."
</span>
</td>
</tr>
</table>
</form>";


echo $text;

$text = "<table style=\"width:95%\">
<tr>
<td style=\"width:50%\">";
$text .= forumjump();
$text .= "</td></tr></table>";
echo $text;

}

//############################################################################################# edit

if($action == "edit"){

	$sql -> db_Select("forum", "*", "forum_id='".$forum_id."' ");
	list($forum_id, $forum_name, $forum_description, $parent, $forum_datestamp, $forum_active, $forum_moderators) = $sql-> db_Fetch();
	$sql -> db_Select("forum_t", "*", "thread_id='".$thread_id."' ");
	list($thread_id, $subject, $post, $thread_forum_id, $thread_datestamp, $thread_parent, $thread_user, $thread_views, $thread_active) = $sql -> db_Fetch();

	$text = "<div class=\"captiontext\">
	<a href=\"index.php\">".SITENAME."</a> -> <a href=\"forum.php\">Forums</a> >> <a href=\"forum.php?forum.".$forum_id."\">".$forum_name."</a> >> ".LAN_68." ";
	if($thread_parent == 0){
		$text .= LAN_53;
	}else{
		$text .= LAN_76;
	}
	$text .= "</div>";
	

	$post = stripslashes(eregi_replace("<div style=\"text-align:right\">\[.*", "", $post));
	$subject = $aj -> editparse($subject);
	$post = $aj -> editparse($post);


	$text .= "
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

$ns -> tablerender(LAN_68, $text);

}

//############################################################################################# functions

function forumjump(){
	$sql = new db;
	$sql -> db_Select("forum", "*", "forum_parent !=0 AND forum_active='1'");
	$c=0;
	$text .= "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
<p>
Jump: 
<select name=\"forumjump\" class=\"tbox\">";
	while(list($forum_id, $forum_name) = $sql -> db_Fetch()){
		$text .= "\n<option>".$forum_name."</option>";
	}
	$text .= "</select>
<input class=\"button\" type=\"submit\" name=\"fjsubmit\" value=\"Go\" />&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."#top\">Back to top</a>
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

if($action != "rp" && $action != "quote" && $action != "nt" && $action != "edit"){
$text = "<div style=\"text-align:center\">

<table style=\"width:100%\">
<tr>
<td style=\"width:50%; vertical-align:top\">

<table style=\"width:100%\">
<tr>
<td style=\"vertical-align:center; width:3%\"><img src=\"themes/shared/forum/new.png\" alt=\"\" /></td>
<td style=\"vertical-align:center\">".LAN_79."</td>

<td style=\"vertical-align:center; width:3%\"><img src=\"themes/shared/forum/nonew.png\" alt=\"\" /></td>
<td style=\"vertical-align:center\">".LAN_80."</td>
</tr>
<tr>

<td style=\"vertical-align:center; width:3%\"><img src=\"themes/shared/forum/sticky.png\" alt=\"\" /></td>
<td style=\"vertical-align:center\">".LAN_202."</td>


<td style=\"vertical-align:center; width:3%\"><img src=\"themes/shared/forum/stickyclosed.png\" alt=\"\" /></td>
<td style=\"vertical-align:center\">".LAN_203."</td>



</tr>
<tr>
<td style=\"vertical-align:center; width:3%\"><img src=\"themes/shared/forum/closed.png\" alt=\"\" /></td>
<td style=\"vertical-align:center\">".LAN_81."</td>

</tr>
</table>


</td>

<td style=\"width:50%; text-align:right; vertical-align:top\">";

if(USER == TRUE || (eregi(ADMINNAME, $forum_moderators)) || ANON == TRUE){
	$text .= LAN_204."<br />".LAN_206."<br />";
}else{
	$text .= LAN_205."<br />".LAN_207."<br />";
}

if(USER == TRUE || (eregi(ADMINNAME, $forum_moderators))){
	$text .= LAN_208."<br />".LAN_210."<br />";
}else{
	$text .= LAN_209."<br />".LAN_211."<br />";
}




$text .= "</td></tr>
</table>

</div>";
echo $text;
}

?>
<script type="text/javascript">
function addtext(sc){
	document.newthread.post.value += sc;
}
function help(help){
	document.newthread.helpb.value = help;
}
</script>
<?php
require_once(FOOTERF);
?>