<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/article.php																	|
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
if(!$_SERVER['QUERY_STRING']){
	header("location:".$_SERVER['HTTP_REFERER']);
}else{
	$ar = explode(".", $_SERVER['QUERY_STRING']);
	$id = $ar[0];
	$page = $ar[1];

}
$aj = new textparse();
if(IsSet($_POST['jump'])){
	$ar = explode(".", $_POST['jump']);
	$id = $ar[0];
	$page = $ar[1];
}

if(!$sql -> db_Select("content", "*", " content_id='$id' ")){
	header("location:index.php");
}

if(IsSet($_POST['submit'])){
	$fp = new floodprotect;
	if($fp -> flood("comments", "comment_datestamp") == FALSE){
		header("location:index.php");
		die();
	}
	if(!$sql -> db_Select("comments", "*", "comment_comment='".$_POST['comment']."' AND comment_author='$author_name' AND comment_type='1' ")){
		if($comment != ""){
			if(USER != TRUE){
				if($_POST['author_name'] == ""){
					$author = "0.Anonymous";
				}else{
					
					$author = "0.". $aj -> tp($_POST['author_name']);
				}
			}else{
				$author = USERID.".".USERNAME;
				$sql -> db_Update("user", "user_comments=user_comments+1, user_lastpost='".time()."' WHERE user_id='".USERID."' ");
			}

			$comment = $aj -> tp($_POST['comment']);

			$sql -> db_Insert("comments", "0, '$id', '$author', '$author_email', '".time()."', '$comment', '0', '".getenv("REMOTE_ADDR")."', '1' ");

		}
	}
}

require_once(HEADERF);

$sql -> db_Select("content", "*", "content_id='$id' ");
list($content_id, $content_heading) = $sql-> db_Fetch();

$sql -> db_Select("content", "*", "content_heading='$content_heading' ");
list($content_id, $main_content_heading, $content_subheading, $content_content, $content_page, $content_datestamp, $content_author, $main_content_comment, $comment_parent, $content_type) = $sql-> db_Fetch();

if($content_type == 254 || $content_type == 255){
	if($content_page == 1){
		$text = $aj -> tpa($content_content, $mode="on");
	}else{
		$text = $aj -> tpa($content_content);
	}
	$ns -> tablerender($content_subheading, $text);
	require_once(FOOTERF);
	exit;
}

$main_content_heading = $aj -> tpa($main_content_heading);
$content_subheading = $aj -> tpa($content_subheading);
if($content_author == 0){
	$admin_email = "e107@jalist.com";
	$admin_name = "jalist";
}else{
	$sql -> db_Select("admin", "*", "admin_id='$content_author'");
	list($admin_id, $admin_name, $null, $admin_email) = $sql-> db_Fetch();
}
$obj = new convert;
$datestamp = $obj->convert_date($content_datestamp, "long");

$caption = $main_content_heading."<br />";
$content_content .= "<br />";
	if($content_subheading != ""){
		$text = "<i>".$content_subheading."</i>
<br />";
	}
$text .= "<i>by <a href=\"mailto:".$admin_email."\">".$admin_name."</a></i>
<br />
<span class=\"smalltext\">".
$datestamp."
</span>
<br /><br />";

$articlepages = explode("[newpage]",$content_content);
$totalpages = count($articlepages);
if($totalpages > 1){

	if($content_page == 1){
		$text .=  $aj -> tpa($articlepages[$page]."<br /><br />", $mode="on");
	}else{
		$text .=  $aj -> tpa($articlepages[$page]."<br /><br />");
	}

	if($page != 0){
		$text .= "<a href=\"article.php?$id.".($page-1)."\"><< </a>";
	}

	for($c=1; $c<= $totalpages; $c++){
		if($c == ($page+1)){
			$text .= "<u>$c</u>&nbsp;&nbsp;";
		}else{
			$text .= "<a href=\"article.php?$id.".($c-1)."\">$c</a>&nbsp;&nbsp;";
		}
	}

	if(($page+1) != $totalpages){
		$text .= "<a href=\"article.php?$id.".($page+1)."\">>> </a>";
	}

	$ns -> tablerender($main_content_heading.", page ".($page+1), $text);
}else{
	if($content_page == 1){
		$text .= $aj ->tpa($content_content, $mode="on");
	}else{
		$text .= $aj ->tpa($content_content);
	}
	$ns -> tablerender($main_content_heading, $text);
}

if($main_content_comment == 1 && ($page+1) == $totalpages){
	$sql2 = new db;
	$comment_total = $sql -> db_Select("comments", "*", "comment_item_id='$id' AND comment_type='1' ");
	if($comment_total != 0){
	$text = "";
	while(list($comment_id, $comment_item_id, $comment_author, $comment_author_email, $comment_datestamp, $comment_comment, $comment_blocked, $comment_ip) = $sql-> db_Fetch()){
		$fca = eregi_replace("[0-9]+\.", "", $comment_author);
			
		$author_total = $sql2 -> db_Count("comments", "(*)", " WHERE comment_author='".$fca."' OR comment_author='$comment_author' ");
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


if(ADMIN == TRUE){
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
	$ns -> tablerender(LAN_5, $text);
}

if($pref['user_reg'][1] == 1 && USER != TRUE && $pref['anon_post'][1] != "1"){
	$text = "<div style=\"text-align:center\">".LAN_6."</div>";
	$ns -> tablerender($text, "");
	require_once(FOOTERF);
	exit;
}

$text = "
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?".$id.".".$page."\">\n
<table style=\"width:95%\">";

if($pref['anon_post'][1] == "1" && !$_SESSION['userkey']){
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

$ns -> tablerender("Submit comment", $text);
}
require_once(FOOTERF);
?>