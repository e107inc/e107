<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/article.php
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
require_once(HEADERF);
require_once("classes/comment_class.php");

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

$cobj = new comment;
if(IsSet($_POST['commentsubmit'])){
	$cobj -> enter_comment($_POST['author_name'], $_POST['comment'], "content", $id);
}

if(!$sql -> db_Select("content", "*", "content_id='$id' ")){
	die();
}else{
	list($content_id, $main_content_heading, $content_subheading, $content_content, $content_page, $content_datestamp, $content_author, $main_content_comment, $comment_parent, $content_type) = $sql-> db_Fetch();

	if($page == 255){
		echo "Content page";
		//	content page
		$text = $aj -> tpa($content_content, $mode="off");
		$caption = $aj -> tpa($content_subheading);
		$ns -> tablerender($caption, $text);
		unset($text);
		if($main_content_comment){
			if($comment_total = $sql -> db_Select("comments", "*",  "comment_item_id='$id' AND comment_type='1' ORDER BY comment_datestamp")){
				while($row = $sql -> db_Fetch()){
					$text .= $cobj -> render_comment($row, $user_id, $user_name, $datestamp, $user_image, $user_comments, $user_join, $user_signature, $comment_comment, $comment_blocked, $unblock, $block, $delete, $userinfo);
				}
				$ns -> tablerender(LAN_5, $text);
			}
			$cobj -> form_comment();
		}
		require_once(FOOTERF);
		exit;
	}
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
	unset($text);
	if($comment_total = $sql -> db_Select("comments", "*",  "comment_item_id='$id' AND comment_type='1' ORDER BY comment_datestamp")){
		while($row = $sql -> db_Fetch()){
			$text .= $cobj -> render_comment($row);
		}
		$ns -> tablerender(LAN_5, $text);
	}
	$cobj -> form_comment();
}

require_once(FOOTERF);
?>