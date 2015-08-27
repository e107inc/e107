<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc 
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/print.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
//include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);

e107::coreLan('print');

/*
$HEADER="";
$FOOTER="";
$CUSTOMHEADER = "";
$CUSTOMFOOTER = "";
*/

$qs = explode(".", e_QUERY,2);
if ($qs[0] == "") {
	header("location:".e_BASE."index.php");
	 exit;
}
define('e_IFRAME', true); 

$source = $qs[0];
$parms = varset($qs[1],'');
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
	$newsUrl = e107::getUrl()->create('news/view/item', $row, 'full=1'); 
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
		list($category_id, $category_name) = $sql->db_Fetch(MYSQL_NUM);
		$sql->db_Select("user", "user_id, user_name", "user_id='{$news_author}'");
		list($a_id, $a_name) = $sql->db_Fetch(MYSQL_NUM);
	}
	$news_datestamp = $con->convert_date($news_datestamp, "long");
	$print_text = "<span style=\"font-size: 13px; color: black; font-family: tahoma, verdana, arial, helvetica; text-decoration: none\">
	<b>".LAN_PRINT_135.$news_title."</b>
	<br />
	(".LAN_PRINT_86." ".$tp->toHTML($category_name,FALSE,"defs").")
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
	( ".$newsUrl." )
	";
	
	

}


if(defined("TEXTDIRECTION") && TEXTDIRECTION == "rtl"){
	$align = 'right';
}else{
	$align = 'left';
}

// Header down here to give us a chance to set a page title
require_once(HEADERF);

//temporary solution - object of future cahges
if(is_readable(THEME.'print_template.php'))
{
	include_once(THEME.'print_template.php');
	echo $tp->parseTemplate($PRINT_TEMPLATE);
}
else 
{
	echo "
		<div style='background-color:white'>
		<div style='text-align:".$align."'>".$tp->parseTemplate("{LOGO}", TRUE)."</div><hr /><br />
		<div style='text-align:".$align."'>".$print_text."</div><br /><br />
		<form action=''><div class='hidden-print' style='text-align:center'><input class='btn btn-primary ' type='button' value='".LAN_PRINT_307."' onclick='window.print()' /></div></form></div>";
}
require_once(FOOTERF);

?>