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

	if(!$sql -> db_Select("comments", "*", "comment_comment='".$_POST['comment']."' AND comment_item_id='$id' AND comment_type='0' ")){
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

			$comment = $aj -> tp($_POST['comment'], "off", 1);
			$ip = getip();
			if(!eregi("Invalid", $comment)){
				$sql -> db_Insert("comments", "0, '$id', '$author', '$author_email', '".time()."', '$comment', '0', '$ip', '0' ");
			}
		}
	}
}
require_once(HEADERF);

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
	$text = "";
	while(list($comment_id, $comment_item_id, $comment_author, $comment_author_email, $comment_datestamp, $comment_comment, $comment_blocked, $comment_ip) = $sql2-> db_Fetch()){
		$fca = eregi_replace("[0-9]+\.", "", $comment_author);

		$author_total = $sql -> db_Count("comments", "(*)", "WHERE comment_author='".$fca."' OR comment_author='$comment_author' ");
		$gen = new convert;
		$datestamp = $gen->convert_date($comment_datestamp, "short");
		$comment_author = $fca;
		$text .= "<table style=\"width:95%\">
<tr>
<td style=\"width:30%; vertical-align=top\">
<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> 
<span class=\"defaulttext\"><i>$comment_author</i></span>
<br />
<span class=\"smalltext\">on $datestamp
<br />
Comments: ".$author_total."
</span>
</td>
<td style=\"width:70%; vertical-align=top\">
<span class=\"mediumtext\">";

if($comment_blocked == 1){
	$text .= LAN_0;
}else{
	
	$text .= $aj -> tpa($comment_comment);
}	
	
$text .= "</span>
</td>";


if(ADMIN == TRUE && ADMINPERMS <=2){
	$text .= "<td style=\"text-align:right\">
<div class=\"smalltext\">";

if($comment_blocked == 1){
	$text .= "[<a href=\"admin/comment_conf.php?unblock-".$comment_id."-".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."\">".LAN_1."</a>] ";
}else{
	$text .= "[<a href=\"admin/comment_conf.php?block-".$comment_id."-".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."\">".LAN_2."</a>] ";
}
$text .= "[<a href=\"admin/comment_conf.php?delete-".$comment_id."-".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."\">".LAN_3."</a>] 
[<a href=\"admin/userinfo.php?".$comment_ip."\">".LAN_4."</a>]
</td>";
}


$text .= "</tr>
</table>
<br />";
	}
//	$text = $aj -> tpa($text);
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
?>