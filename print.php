<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/print.php,v $
|     $Revision: 1.4 $
|     $Date: 2005-02-07 18:10:18 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
$qs = explode(".", e_QUERY);
if ($qs[0] == "") {
	header("location:".e_BASE."index.php");
	 exit;
}
$source = $qs[0];
$parms = $qs[1];

if(strpos($source,'plugin:') !== FALSE)
{
	$plugin = substr($source,7);
	if(file_exists(e_PLUGIN.$plugin."/e_emailprint.php"))
	{
		include_once(e_PLUGIN.$plugin."/e_emailprint.php");
		$text = print_item($parms);
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
	$sql->db_Select("news", "*", "news_id='$parms'");
	$row = $sql->db_Fetch(); 
	extract($row);
	$news_body = $tp->toHTML($news_body, TRUE);
	$news_extended = $tp->toHTML($news_extended, TRUE);
	if ($news_author == 0)
	{
		$a_name = "e107";
		$category_name = "e107 welcome message";
	}
	else
	{
		$sql->db_Select("news_category", "category_id, category_name", "category_id='$news_category'");
		list($category_id, $category_name) = $sql->db_Fetch();
		$sql->db_Select("user", "user_id, user_name", "user_id='$news_author'");
		list($a_id, $a_name) = $sql->db_Fetch();
	}
	$news_datestamp = $con->convert_date($news_datestamp, "long");
	$text = "<font style=\"font-size: 11px; color: black; font-family: tahoma, verdana, arial, helvetica; text-decoration: none\">
	<b>".LAN_135.": ".$news_title."</b>
	<br />
	(".LAN_86." ".$category_name.")
	<br />
	".LAN_94." ".$a_name."<br />
	".$news_datestamp."
	<br /><br />".
	$news_body;

	if ($news_extended != ""){ $text .= "<br /><br />".$news_extended; }
	if ($news_source != ""){ $text .= "<br /><br />".$news_source; }
	if ($news_url != ""){ $text .= "<br />".$news_url; }
	 
	$text .= "<br /><br /><hr />".
	LAN_303.SITENAME."
	<br />
	( http://".$_SERVER[HTTP_HOST].e_HTTP."comment.php?comment.news.".$news_id." )
	</font>";
}

echo "
	<div style=\"text-align:center\">
	<img src=\"".e_IMAGE."logo.png\" alt=\"Logo\" />
	</div>
	<hr />
	<br />
	";
echo $text;
echo "<br /><br /><div style='text-align:center'><form><input type='button' value='".LAN_307."' onClick='window.print()'></form></div>";
	
?>