<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/template.php																|
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

$qs = explode(".", $_SERVER['QUERY_STRING']);
if($qs[0] == ""){ header("location:index.php"); }
$table = $qs[0];
$id = $qs[1];
$field = $table."_id";
$con = new convert;


if($table == "news"){
	$sql -> db_Select("news", "*", "news_id='$id' ");
	list($news_id, $news_title, $news_body, $news_extended, $news_datestamp, $news_author, $news_source, $news_url, $news_category) = $sql-> db_Fetch();
	$news_body = nl2br($news_body);
	$news_extended = nl2br($news_extended);
	if($news_author == 0){
		$a_name = "e107";
		$category_name = "e107 welcome message";
	}else{
		$sql -> db_Select("news_category", "*", "category_id='$news_category' ");
		list($category_id, $category_name) = $sql-> db_Fetch();
		$sql -> db_Select("admin", "*", "admin_id='$news_author' ");
		list($a_id, $a_name) = $sql-> db_Fetch();
		$news_datestamp = $con -> convert_date($news_datestamp, "long");

		$text = "<font style=\"FONT-SIZE: 11px; COLOR: black; FONT-FAMILY: Tahoma, Verdana, Arial, Helvetica; TEXT-DECORATION: none\">
		<div style=\"text-align:center\">
		<img src=\"themes/shared/logo.png\" alt=\"Logo\" />
		</div>
		<hr />
		<br />
		<b>News Title: ".$news_title."</b>
		<br />
		(Category: ".$category_name.")
		<br />
		by ".$a_name."<br />
		posted on ".$news_datestamp."
		<br /><br />".
		$news_body;

		if($news_extended != ""){ $text .= "<br /><br />".$news_extended; }
		if($news_source != ""){ $text .= "<br /><br />".$news_source; }
		if($news_url != ""){ $text .= "<br />".$news_url; }

		$text .= "<br /><br /><hr />
		This news item is from ".SITENAME."
		<br />
		(".SITEURL."/comment.php?".$news_id.")
		</font>";
	}
}else{
	$sql -> db_Select("content", "*", "content_id='$id' ");
	$row = $sql -> db_Fetch();
	extract($row);
	$content_content = nl2br($content_content);
	$sql -> db_Select("admin", "*", "admin_id='$content_author' ");
	list($a_id, $a_name) = $sql-> db_Fetch();
	$content_datestamp = $con -> convert_date($content_datestamp, "long");
	$text = "<font style=\"FONT-SIZE: 11px; COLOR: black; FONT-FAMILY: Tahoma, Verdana, Arial, Helvetica; TEXT-DECORATION: none\">
	<div style=\"text-align:center\">
	<img src=\"themes/shared/logo.png\" alt=\"Logo\" />
	</div>
	<hr />
	<br />
	<b>Article Title: ".$content_heading."</b>
	<br />
	Subheading: ".$content_subheading."
	<br />
	by ".$a_name."<br />
	posted on ".$content_datestamp."
	<br /><br />".
	$content_content."
	<br /><br /><hr />
	This article is from ".SITENAME."
	<br />
	(".SITEURL."/article.php?".$content_id.".0)
	</font>";
}
echo $text;

?>