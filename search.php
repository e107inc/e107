<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	search.php V53b2
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
+---------------------------------------------------------------+
| 03/12/02
| + search no longer case sensitive (added by McFly)
+---------------------------------------------------------------+
+---------------------------------------------------------------+
| 02/23/03
| + search made modular allows you to create your own functions 
|   for searching custom tables (Rene Mensink)
+---------------------------------------------------------------+
*/
require_once("class2.php");

if(IsSet($_POST['searchquery']) && $_POST['searchtype'] == "0"){ header("location:http://www.google.com/search?q=".$_POST['searchquery']); exit; }

require_once(HEADERF);

$search_array = array(
	1 => "search_news.php", 
	2 => "search_comment.php", 
	3 => "search_article.php", 
	4 => "search_review.php", 
	5 => "search_content.php", 
	6 => "search_chatbox.php", 
	7 => "search_links.php", 
	8 => "search_forum.php", 
	9 => "search_user.php", 
	99 => "all"
);

$search_qtype = array(
	1 => LAN_98, 
	2 => LAN_99, 
	3 => LAN_100, 
	4 => LAN_190, 
	5 => "Content", 
	6 => LAN_101, 
	7 => LAN_102, 
	8 => LAN_103, 
	9 => LAN_140, 
	99 => "All categories"
);

$con = new convert;
if(!$refpage = substr($_SERVER['HTTP_REFERER'], (strrpos($_SERVER['HTTP_REFERER'], "/")+1))){ $refpage = "index.php"; }

if(IsSet($_POST['searchquery'])){ $query = $_POST['searchquery']; }

if($_POST['searchtype']){
	$searchtype = $_POST['searchtype'];
}else{

	// as no searchtype has been specified we need to find out what page the query has been executed from ...
	// $refpage holds referring page believe it or not

	if(eregi("news.php", $refpage)){ $searchtype = 1; }
	if(eregi("comment.php", $refpage)){ $searchtype = 2; }
	if(eregi("chat.php", $refpage)){ $searchtype = 6; }
	if(eregi("links.php", $refpage)){ $searchtype = 7; }
	if(eregi("forum", $refpage)){ $searchtype = 8; }
	if(eregi("user.php", $refpage)){ $searchtype = 9; }

	if(eregi("article.php", $refpage)){
		preg_match("/\?(.*?)\./", $refpage, $result);
		$sql -> db_Select("content", "*", "content_id='".$result[1]."'");
		$row = $sql -> db_Fetch(); extract($row);
		if($content_type == 0){ $searchtype = 3; }
		if($content_type == 3){ $searchtype = 4; }
		if($content_type == 1){ $searchtype = 5; }
	}

	if(!$searchtype){ $searchtype = 1; }
	
}

$text = "<div style='text-align:center'><form method='post' action='search.php'>
<p>
Search for <input class='tbox' type='text' name='searchquery' size='20' value='$query' maxlength='50' />
&nbsp;in <select name='searchtype' class='tbox'>";

// add you own types here

$text .= ($searchtype == 1 ? "<option value='1' selected>News</option>" : "<option value='1'>News</option>");
$text .= ($searchtype == 2 ? "<option value='2' selected>Comments</option>" : "<option value='2'>Comments</option>");
$text .= ($searchtype == 3 ? "<option value='3' selected>Articles</option>" : "<option value='3'>Articles</option>");
$text .= ($searchtype == 4 ? "<option value='4' selected>Reviews</option>" : "<option value='4'>Reviews</option>");
$text .= ($searchtype == 5 ? "<option value='5' selected>Content</option>" : "<option value='5'>Reviews</option>");
$text .= ($searchtype == 6 ? "<option value='6' selected>Chatbox</option>" : "<option value='6'>Chatbox</option>");
$text .= ($searchtype == 7 ? "<option value='7' selected>Links</option>" : "<option value='7'>Links</option>");
$text .= ($searchtype == 8 ? "<option value='8' selected>Forum</option>" : "<option value='8'>Forum</option>");
$text .= ($searchtype == 9 ? "<option value='9' selected>Members</option>" : "<option value='9'>Users</option>");

//	uncomment if you use the event calendar ...
//	$text .= ($searchtype == 9 ? "<option value='9' selected>Event Calendar</option>" : "<option value='9'>Event Calendar</option>");

$text .= ($searchtype == 99 ? "<option value='99' selected>All Categories</option>" : "<option value='99'>All Categories</option>");

$text .= "
<option value='0'>Google</option>
</select>
<input class='button' type='submit' name='searchsubmit' value='".LAN_180."' />
</p>
</form></div>";

$ns -> tablerender("Search ".SITENAME, $text);
// only search when a query is filled.
if($_POST['searchquery'] && $searchtype != 99){
	unset($text);
	require_once("classes/search/".$search_array[$searchtype]);
	$ns -> tablerender("Searching ".$search_qtype[$searchtype]." :: matches: ".$results, $text);
}else if($searchtype == 99){
	foreach ($search_array as $key => $value){
		if($key != 99){
			unset($text);
			require_once("classes/search/".$search_array[$key]);
			$ns -> tablerender("Searching ".$search_qtype[$key]." :: matches: ".$results, $text);
		}
	}
}

function parsesearch($text, $match){
	$text = strip_tags($text);
	$temp = stristr($text,$match);
	$pos = strlen($text)-strlen($temp);
	if($pos < 70){
		$text = "...".substr($text, 0, 100)."...";
	}else{
		$text = "...".substr($text, ($pos-70), 140)."...";
	}
	$text = eregi_replace($match, "<u><b>$match</b></u>", $text);	
	return($text);
}

require_once(FOOTERF);
?>