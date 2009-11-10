<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     (C)Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/print.php,v $
|     $Revision: 1.16 $
|     $Date: 2009-11-10 11:52:31 $
|     $Author: marj_nl_fr $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
$HEADER="";
$FOOTER="";
$CUSTOMHEADER = "";
$CUSTOMFOOTER = "";


$qs = explode(".", e_QUERY);
if ($qs[0] == "") {
	header("location:".e_BASE."index.php");
	 exit;
}
$source = $qs[0];
$parms = intval($qs[1]);
unset($qs);


if(strpos($source,'plugin:') !== FALSE)
{
	$plugin = substr($source,7);
	if(file_exists(e_PLUGIN.$plugin."/e_emailprint.php"))
	{
		include_once(e_PLUGIN.$plugin."/e_emailprint.php");
		$print_text = print_item($parms);
//		define("e_PAGETITLE", $plugin);
	}
	else
	{
		echo "file missing.";
		exit;
	}
}
else
{
	$con = new convert;
	$sql->db_Select("news", "*", "news_id='{$parms}'");
	$row = $sql->db_Fetch();
	extract($row);
	define("e_PAGETITLE", $news_title);
	$news_body = $tp->toHTML($news_body, TRUE, 'BODY');
	$news_extended = $tp->toHTML($news_extended, TRUE, 'BODY');
	if ($news_author == 0)
	{
		$a_name = "e107";
		$category_name = "e107 welcome message";
	}
	else
	{
		$sql->db_Select("news_category", "category_id, category_name", "category_id='{$news_category}'");
		list($category_id, $category_name) = $sql->db_Fetch();
		$sql->db_Select("user", "user_id, user_name", "user_id='{$news_author}'");
		list($a_id, $a_name) = $sql->db_Fetch();
	}
	$news_datestamp = $con->convert_date($news_datestamp, "long");
	$print_text = "<span style=\"font-size: 13px; color: black; font-family: tahoma, verdana, arial, helvetica; text-decoration: none\">
	<b>".LAN_PRINT_135.": ".$news_title."</b>
	<br />
	(".LAN_PRINT_86." ".$tp->toHTML($category_name, FALSE, "defs").")
	<br />
	".LAN_PRINT_94." ".$a_name."<br />
	".$news_datestamp."
	<br /><br />".
	$news_body;

	if ($news_extended != ""){ $print_text .= "<br /><br />".$news_extended; }
	if ($news_source != ""){ $print_text .= "<br /><br />".$news_source; }
	if ($news_url != ""){ $print_text .= "<br />".$news_url; }

	$print_text .= "<br /><br /></span><hr />".
	LAN_PRINT_303.SITENAME."
	<br />
	( http://".$_SERVER['HTTP_HOST'].e_HTTP."news.php?extend.".$news_id." )
	";
}


if(defined("TEXTDIRECTION") && TEXTDIRECTION == "rtl"){
	$align = 'right';
}else{
	$align = 'left';
}

// Header down here to give us a chance to set a page title
require_once(HEADERF);

echo "
<div style='background-color:white'>
<div style='text-align:".$align."'>".$tp->parseTemplate("{LOGO}", TRUE)."</div><hr /><br />
<div style='text-align:".$align."'>".$print_text."</div><br /><br />
<form action=''><div style='text-align:center'><input type='button' value='".LAN_PRINT_307."' onclick='window.print()' /></div></form></div>";

require_once(FOOTERF);

?>