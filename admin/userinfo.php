<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/admin/userinfo.php													|
|																						|
|	©Steve Dunstan 2001-2002										|
|	http://jalist.com															|
|	stevedunstan@jalist.com											|
|																						|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).				|
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(ADMINPERMS != 0 && ADMINPERMS != 1 && ADMINPERMS != 2){
	header("location:../index.php");
}
require_once("auth.php");

if(!$_SERVER['QUERY_STRING']){
	$text = "<div style=\"text-align:center\">Unable to find poster's IP address - no information is availble.</div>";
	$ns -> tablerender("Error", $text);
	require_once("footer.php");
	exit;
}else{
	$ipd = $_SERVER['QUERY_STRING'];
}

if(IsSet($ipd)){
	$obj = new convert;
	$sql -> db_Select("chatbox", "*", "cb_ip='$ipd' LIMIT 0,20");
	$host = gethostbyaddr($ipd);
	$text = "Messages posted from IP address <b>".$ipd."</b> [ Host: $host ]<br />
	<i><a href=\"banlist.php?".$ipd."\">Click here to transfer IP address to admin ban page</a></i>
	
	<br /><br />";
	while(list($cb_id, $cb_nick, $cb_message, $cb_datestamp, $cb_blocked, $cb_ip ) = $sql-> db_Fetch()){
		$datestamp = $obj->convert_date($cb_datestamp, "short");
		$text .= "<img src=\"../".THEME."images/bullet2.gif\" alt=\"bullet\" />
<span class=\"defaulttext\"><i>".$cb_nick."</i></span>\n<div class=\"mediumtext\">".$datestamp."<br />".
$cb_message."
</div><br />";
	}

	$text .= "<hr />";

	$sql -> db_Select("comments", "*", "comment_ip='$ipd' LIMIT 0,20");
	while(list($comment_id, $comment_item_id, $comment_author, $comment_author_email, $comment_datestamp, $comment_comment, $comment_blocked, 	$comment_ip) = $sql-> db_Fetch()){
		$datestamp = $obj->convert_date($comment_datestamp, "short");
		$text .= "<img src=\"../".THEME."images/bullet2.gif\" alt=\"bullet\" />
<span class=\"defaulttext\"><i>".$comment_author."</i></span>\n<div class=\"mediumtext\">".$datestamp."<br />".
$comment_comment."</div><br />";
	}

}

$ns -> tablerender("<div style=\"text-align:center\">User Information</div>", $text);

require_once("footer.php");
?>	