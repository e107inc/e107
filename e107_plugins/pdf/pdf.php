<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin - PDF Generator
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/pdf/pdf.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/
require_once('../../class2.php');
$e107 = e107::getInstance();
if (!$e107->isInstalled('calendar_menu') || !e_QUERY) header('Location: '.e_BASE.'index.php');

$qs = explode('.', e_QUERY,2);
$source = $qs[0];
$parms = varset($qs[1],'');

//include_lan(e_PLUGIN.'pdf/languages/'.e_LANGUAGE.'_admin_pdf.php');

//require_once(e_PLUGIN.'pdf/tcpdf.php');		//require the ufpdf class
require_once(e_PLUGIN.'pdf/e107pdf.php');	//require the e107pdf class
$pdf = new e107PDF();

if(strpos($source,'plugin:') !== FALSE)
{
	$plugin = substr($source,7);
	if(file_exists(e_PLUGIN.$plugin.'/e_emailprint.php'))
	{
		include_once(e_PLUGIN.$plugin.'/e_emailprint.php');
		if (function_exists('print_item_pdf'))
		{
			$text = print_item_pdf($parms);
			$pdf->makePDF($text);
		}
		else
		{
			echo 'PDF generation not supported in this section';
		}
	}
	else
	{
		echo 'file missing: '.e_PLUGIN.$plugin.'/e_emailprint.php';
		exit;
	}
}
else
{
	if($source == 'news')
	{
		$con = new convert;
		$sql->db_Select('news', '*', 'news_id='.intval($parms));
		$row = $sql->db_Fetch(); 
		$news_body = $tp->toHTML($row['news_body'], TRUE);
		$news_extended = $tp->toHTML($row['news_extended'], TRUE);
		if ($row['news_author'] == 0)
		{
			$a_name = 'e107';
			$category_name = 'e107 welcome message';
		}
		else
		{
			$sql->db_Select('news_category', 'category_id, category_name', 'category_id='.intval($row['news_category']));
			list($category_id, $category_name) = $sql->db_Fetch();
			$sql->db_Select('user', 'user_id, user_name', 'user_id='.intval($row['news_author']));
			list($a_id, $a_name) = $sql->db_Fetch(MYSQL_NUM);
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
		$keywords	= '';						//define keywords

		//define url and logo to use in the header of the pdf file
		$url		= SITEURL.'news.php?item.'.$row['news_id'];

		//always return an array with the following data:
		$text = array($text, $creator, $author, $title, $subject, $keywords, $url);
		$pdf->makePDF($text);
	}
}

?>