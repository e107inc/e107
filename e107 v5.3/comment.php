<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/class.php																	|
|																						|
|	©Steve Dunstan 2001-2002										|
|	http://jalist.com															|
|	stevedunstan@jalist.com											|
|																						|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).				|
+---------------------------------------------------------------+
*/
require_once("class2.php");
$aj = new textparse;
if(!$_SERVER['QUERY_STRING']){ header("location:".$_SERVER['HTTP_REFERER']); }else{ $id = $_SERVER['QUERY_STRING']; }

if(!$sql -> db_Select("news", "*", "news_id='$id' ")){
	header("location:index.php");
}else{
	list($news_id, $news_title, $news_body, $news_extended, $news_datestamp, $news_author, $news_source, $news_url, $news_category, $news_allow_comments) = $sql-> db_Fetch();
	if($news_allow_comments == 1){
		header("location:index.php");
	}
}
if(IsSet($_POST['submit'])){
	
	$fp = new floodprotect;
	if($fp -> flood("comments", "comment_datestamp") == FALSE){
		header("location:index.php");
		die();
	}
	
	$comment = $aj -> tp($_POST['comment'], "off");

	if(!$sql -> db_Select("comments", "*", "comment_comment='".$comment."' AND comment_item_id='$id' AND comment_type='0' ")){
		if($_POST['comment'] != ""){
			if(USER != TRUE){
				if($_POST['author_name'] == ""){
					$author = "0.Anonymous";
				}else{
					
					$author = "0.". $aj -> tp($_POST['author_name'], "off", 1);
				}
			}else{
				$author = USERID.".".USERNAME;
				$sql -> db_Update("user", "user_comments=user_comments+1, user_lastpost='".time()."' WHERE user_id='".USERID."' ");
			}

			$ip = getip();
			if(!$sql -> db_Insert("comments", "0, '$id', '$author', '$author_email', '".time()."', '$comment', '0', '$ip', '0' ")){
				$search = array("\"", "'", "\\");
				$replace = array("&quot;", "&#39;", "&#92;");
				$news_title = str_replace($search, $replace, $comment);
				if(!$sql -> db_Insert("comments", "0, '$id', '$author', '$author_email', '".time()."', '$comment', '0', '$ip', '0' ")){
					$emessage = "<b>Error!</b> Was unable to enter your comment into the database - please retype leaving out any non-standard characters.";
				}
			}
		}
	}
}
require_once(HEADERF);

if($emessage != ""){
	$ns -> tablerender("Error!!!!!", $emessage);
}

$sql -> db_Select("news", "*", "news_id='$id' ");
list($news_id, $news_title, $news_body, $news_extended, $news_datestamp, $news_author, $news_source, $news_url, $news_category, $news_allow_comments) = $sql-> db_Fetch();
$sql2 = new db;
$sql2 -> db_Select("news_category", "*", "category_id='$news_category' ");
list($category_id, $category_name, $category_icon) = $sql2-> db_Fetch();
$comment_total = $sql2 -> db_Select("comments", "*",  "comment_item_id='$news_id' AND comment_type='0' ORDER BY comment_datestamp");
		
$news_title=stripslashes($news_title);
$news_body=stripslashes($news_body);
		
$ix = new news;
$ix -> render_newsitem($news_id, $news_title, $news_body, $news_extended, $news_source, $news_url, $news_author, $comment_total, $category_id, $news_datestamp, $news_allow_comments);

if($comment_total != 0){
	unset($text);
	while(list($comment_id, $comment_item_id, $comment_author, $comment_author_email, $comment_datestamp, $comment_comment, $comment_blocked, $comment_ip) = $sql2-> db_Fetch()){

		// get available vars
		$comment_author = eregi_replace("[0-9]+\.", "", $comment_author);
		$gen = new convert;
		$datestamp = $gen->convert_date($comment_datestamp, "short");
		$sql -> db_Select("user", "*", "user_name='$comment_author'");
		$row = $sql -> db_Fetch();
		extract($row);
		if($user_image != ""){
			if(ereg("avatar_", $user_image)){
				$avatarlist[0] = "";
				$handle=opendir("themes/shared/avatars/");
				while ($file = readdir($handle)){
					if($file != "." && $file != ".."){
						$avatarlist[] = $file;
					}
				}
				closedir($handle);
				$user_image = "themes/shared/avatars/".$avatarlist[substr(strrchr($user_image, "_"), 1)];
			}
		}
		$user_join = $gen->convert_date($user_join, "short");
		 

// commentstyle -----------------------------------------------------------------------------------------------------

		$unblock = "[<a href=\"admin/comment_conf.php?unblock-".$comment_id."-".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."\">".LAN_1."</a>]";
		$block = "[<a href=\"admin/comment_conf.php?block-".$comment_id."-".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."\">".LAN_2."</a>] ";
		$delete = "[<a href=\"admin/comment_conf.php?delete-".$comment_id."-".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."\">".LAN_3."</a>] ";
		$userinfo = "[<a href=\"admin/userinfo.php?".$comment_ip."\">".LAN_4."</a>]";

		if($COMMENTSTYLE == ""){
			$COMMENTSTYLE = "<table style=\"width:95%\">
			<tr>
			<td style=\"width:30%; vertical-align=top\">
			<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> 
			<span class=\"defaulttext\"><i>
			{USERNAME}
			</i></span>
			<br />
			<span class=\"smalltext\">on 
			{TIMEDATE}
			<br />
			Comments: 
			{COMMENTS}
			</span>
			</td>
			<td style=\"width:70%; vertical-align=top\">
			<span class=\"mediumtext\">
			{COMMENT}
			</span>
			</td>
			<td style=\"text-align:right\"><div class=\"smalltext\">
			{ADMINOPTIONS}
			</td></tr></table><br />";
		}
		parsecomment($COMMENTSTYLE);
	}

// end commentstyle -----------------------------------------------------------------------------------------------------

	$ns -> tablerender(LAN_5, $text);
}

if($pref['user_reg'][1] == 1 && !$_SESSION['userkey'] && $pref['anon_post'][1] != "1"){
	$text = "<div style=\"text-align:center\">".LAN_6."</div>";
	$ns -> tablerender($text, "");
	require_once(FOOTERF);
	exit;
}

$text = "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?$id\">\n
<table style=\"width:95%\">";

if($pref['anon_post'][1] == "1" && USER == FALSE){
	$text .= "<tr>
<td style=\"width:20%\">".LAN_7."</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"author_name\" size=\"60\" value=\"$author_name\" maxlength=\"100\" />
</td>
</tr>";
}

$text .= "<tr> 
<td style=\"width:20%\">".LAN_8."</td>
<td style=\"width:80%\">
<textarea class=\"tbox\" name=\"comment\" cols=\"70\" rows=\"10\"></textarea>
</td>
</tr>\n
<tr style=\"vertical-align:top\"> 
<td style=\"width:20%\"></td>
<td style=\"width:80%\">
<input class=\"button\" type=\"submit\" name=\"submit\" value=\"".LAN_9."\" />
<br />
<br />
<span class=\"smalltext\">
".LAN_10."
</span>
</td>
</tr>
</table>
</form>";

$ns -> tablerender(LAN_9, $text);

require_once(FOOTERF);

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function parsecomment($LAYOUT){
	global $text;
	$tmp = explode("\n", $LAYOUT);
	for($c=0; $c < count($tmp); $c++){ 
		if(ereg("{|}", $tmp[$c])){
			$str = checklayoutc($tmp[$c]);
		}else{
			$text .=  $tmp[$c];
		}
	}
}
function checklayoutc($str){
	global $text, $user_id, $user_name, $datestamp, $user_image, $user_comments, $user_join, $user_signature, $comment_comment, $comment_blocked, $unblock, $block, $delete, $userinfo;
	if(strstr($str, "USERNAME")){
		$text .= "<a href=\"user.php?".$user_id."\">".$user_name."</a>\n";
	}else if(strstr($str, "TIMEDATE")){
		$text .= $datestamp;
	}else if(strstr($str, "AVATAR")){
		$text .= "<img src=\"".$user_image."\" alt=\"\" />";
	}else if(strstr($str, "COMMENTS")){
		$text .= $user_comments;
	}else if(strstr($str, "COMMENT")){
		if($comment_blocked == 1){
			$text .= LAN_0;
		}else{
			$aj = new textparse;
			$text .= $aj -> tpa($comment_comment);
		}	
	}else if(strstr($str, "SIGNATURE")){
		$text .= $user_signature;
	}else if(strstr($str, "JOINED")){
		$text .= $user_join;
	}else if(strstr($str, "ADMINOPTIONS")){
		if(ADMIN == TRUE && getperms("B")){
			if($comment_blocked == 1){
				$text .= $unblock;
			}else{
				$text .= $block;
			}
			$text .= $delete.$userinfo."</td>";
		}
	}
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
?>