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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/pdf/pdf.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:35:34 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
$qs = explode(".", e_QUERY);
if ($qs[0] == "") {
	header("location:".e_BASE."index.php");
	 exit;
}
$source = $qs[0];
$parms = $qs[1];

$lan_file = e_PLUGIN."pdf/languages/".e_LANGUAGE.".php";
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."pdf/languages/English.php");

define('FPDF_FONTPATH', 'font/');
require_once(e_PLUGIN."pdf/ufpdf.php");		//require the ufpdf class
require_once(e_PLUGIN."pdf/e107pdf.php");	//require the e107pdf class
$pdf = new e107PDF();

if(strpos($source,'plugin:') !== FALSE)
{
	$plugin = substr($source,7);
	if(file_exists(e_PLUGIN.$plugin."/e_emailprint.php"))
	{
		include_once(e_PLUGIN.$plugin."/e_emailprint.php");
		$text = print_item_pdf($parms);
		$pdf->makePDF($text);
	}
	else
	{
		echo "file missing.";
		exit;
	}
}
else
{
	
	if($source == 'news'){
		$con = new convert;
		$sql->db_Select("news", "*", "news_id='".intval($parms)."'");
		$row = $sql->db_Fetch(); 
		$news_body = $tp->toHTML($row['news_body'], TRUE);
		$news_extended = $tp->toHTML($row['news_extended'], TRUE);
		if ($row['news_author'] == 0){
			$a_name = "e107";
			$category_name = "e107 welcome message";
		}else{
			$sql->db_Select("news_category", "category_id, category_name", "category_id='".intval($row['news_category'])."'");
			list($category_id, $category_name) = $sql->db_Fetch();
			$sql->db_Select("user", "user_id, user_name", "user_id='".intval($row['news_author'])."'");
			list($a_id, $a_name) = $sql->db_Fetch();
		}
		$row['news_datestamp'] = $con->convert_date($row['news_datestamp'], "long");
	
		$row['news_title'] = $tp -> toHTML($row['news_title'], TRUE, 'parse_sc');

		//remove existing links from news title
		$search = array();
		$replace = array();
		$search[0] = "/\<a href=\"(.*?)\">(.*?)<\/a>/si";
		$replace[0] = '\\2';
		$search[1] = "/\<a href='(.*?)'>(.*?)<\/a>/si";
		$replace[1] = '\\2';
		$search[2] = "/\<a href='(.*?)'>(.*?)<\/a>/si";
		$replace[2] = '\\2';
		$search[3] = "/\<a href=&quot;(.*?)&quot;>(.*?)<\/a>/si";
		$replace[3] = '\\2';
		$search[4] = "/\<a href=&#39;(.*?)&#39;>(.*?)<\/a>/si";
		$replace[4] = '\\2';
		$row['news_title'] = preg_replace($search, $replace, $row['news_title']);

		$text = "
		<b>".$row['news_title']."</b><br />
		".$row['category_name']."<br />
		".$a_name.", ".$row['news_datestamp']."<br />
		<br />
		".$row['news_body']."<br />
		";

		if ($row['news_extended'] != ""){ $text .= "<br /><br />".$row['news_extended']; }
		if ($row['news_source'] != ""){ $text .= "<br /><br />".$row['news_source']; }
		if ($row['news_url'] != ""){ $text .= "<br />".$row['news_url']; }

		$text		= $text;					//define text
		$creator	= SITENAME;					//define creator
		$author		= $a_name;					//define author
		$title		= $row['news_title'];		//define title
		$subject	= $category_name;			//define subject
		$keywords	= "";						//define keywords

		//define url and logo to use in the header of the pdf file
		$url		= SITEURL."news.php?item.".$row['news_id'];

		//always return an array with the following data:
		$text = array($text, $creator, $author, $title, $subject, $keywords, $url);
		$pdf->makePDF($text);
	
	}

}

?>