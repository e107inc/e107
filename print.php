<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/template.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the	
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");

$qs = explode(".", e_QUERY);
if($qs[0] == ""){ header("location:".e_BASE."index.php"); exit;}
$table = $qs[0];
$id = $qs[1];
$con = new convert;
$aj = new textparse;
if($table == "news"){
	$sql -> db_Select("news", "*", "news_id='$id' ");
	$row = $sql -> db_Fetch(); extract($row);
	$news_body = $aj -> tpa($news_body);
	$news_extended = $aj -> tpa($news_extended);
	if($news_author == 0){
		$a_name = "e107";
		$category_name = "e107 welcome message";
	}else{
		$sql -> db_Select("news_category", "*", "category_id='$news_category' ");
		list($category_id, $category_name) = $sql-> db_Fetch();
		$sql -> db_Select("user", "*", "user_id='$news_author' ");
		list($a_id, $a_name) = $sql-> db_Fetch();
	}
	$news_datestamp = $con -> convert_date($news_datestamp, "long");

	$text = "<font style='font-size: 11px; color: black; font-family: tahoma, verdana, arial, helvetica; text-decoration: none'>
	<div style='text-align:center'>
	<img src='".e_IMAGE."logo.png' alt='Logo' />
	</div>
	<hr />
	<br />
	<b>".LAN_135.": ".$news_title."</b>
	<br />
	(".LAN_86." ".$category_name.")
	<br />
	".LAN_94." ".$a_name."<br />
	".$news_datestamp."
	<br /><br />".
	$news_body;
	if($news_extended != ""){ $text .= "<br /><br />".$news_extended; }
	if($news_source != ""){ $text .= "<br /><br />".$news_source; }
	if($news_url != ""){ $text .= "<br />".$news_url; }

	$text .= "<br /><br /><hr />".
	LAN_303.SITENAME."
	<br />
	( http://".$_SERVER['HTTP_HOST'].e_HTTP."comment.php?".$news_id." )
	</font>";
	
}else{
	$sql -> db_Select("content", "*", "content_id='$id' ");
	$row = $sql -> db_Fetch();
	extract($row);
	$content_heading = $aj -> tpa($content_heading);
	$content_subheading = $aj -> tpa($content_subheading);
	$content_content = ereg_replace("\{EMAILPRINT\}|\[newpage\]", "", $aj -> tpa($content_content));
	$sql -> db_Select("user", "*", "user_id='$content_author' ");
	list($a_id, $a_name) = $sql-> db_Fetch();
	$content_datestamp = $con -> convert_date($content_datestamp, "long");
	$text = "<font style='FONT-SIZE: 11px; COLOR: black; FONT-FAMILY: Tahoma, Verdana, Arial, Helvetica; TEXT-DECORATION: none'>
	<div style='text-align:center'>
	<img src='".e_IMAGE."logo.png' alt='Logo' />
	</div>
	<hr />
	<br />
	<b>".LAN_304.$content_heading."</b>
	<br />
	".LAN_305.$content_subheading."
	<br />
	by ".$a_name."<br />
	".$content_datestamp."
	<br /><br />".
	$content_content."
	<br /><br /><hr />
	".LAN_306.SITENAME."
	<br />
	( http://".$_SERVER['HTTP_HOST'].e_HTTP."article.php?article.".$content_id." )
	</font>";
}
echo $text;

echo "<br /><br /><div style='text-align:center'><form><input type='button' value='".LAN_307."' onClick='window.print()'></form></div>";

?>