<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Banlist export
 *
 */

require_once(__DIR__.'/../class2.php');
if (!getperms('4')) 
{
	e107::redirect('admin');
	exit;
}

/*
Output a selection of data from the banlist table arequire_once(__DIR__.'/../class2.php');s a CSV
Selection data:
  $_POST['ban_types'] - array of 0..9
  $_POST['ban_separator'] - 1 or 2
  $_POST['ban_quote'] - 1,2,3
*/
require_once(e_HANDLER.'iphandler_class.php');		// This is probably already loaded in class2.php

// Uncomment what's going on to get debug data
//define('CSV_DEBUG',TRUE);

$separator_char = array(1 => ',', 2 => '|');
$quote_char = array(1 => '', 2 => "'", 3 => '"');


$format_array = array(
  'banlist_ip' => 1,
  'banlist_datestamp' => "%Y%m%d_%H%M%S",
  'banlist_banexpires' => "%Y%m%d_%H%M%S",
  'banlist_bantype' => 1,
  'banlist_reason' => 1,
  'banlist_notes' => 1
);

$banSep = (int)  varset($_POST['ban_separator']);
$banQuote = (int) varset($_POST['ban_quote']);
$use_separator = varset($separator_char[$banSep],$separator_char[1]);
$use_quote = varset($quote_char[$banQuote],$quote_char[2]);


$type_list = '';

if (!empty($_POST['ban_types']))
{
	$validBanTypes = banlistManager::getValidReasonList();
	$spacer = '';
	foreach($_POST['ban_types'] as $b)
	{
		$b = trim($b);
		if (is_numeric($b) && in_array($b, $validBanTypes))
		{
			$type_list .= $spacer.($b);
			$spacer = ',';
		}
	}
}

$filename = 'banlist_'.e107::getParser()->toDate(time(), "%Y%m%d_%H%M%S").'.csv';

if ($error_string = do_export($filename, $type_list, $format_array, $use_separator, $use_quote))
{
// Need to report an error here
	echo "Error report: {$error_string}<br />";
}
banlist_adminlog('06','File: '.$filename.'<br />'.$error_string);


function do_export($filename, $type_list='',$format_array=array(), $sep = ',', $quot = '"')
{
	$sql = e107::getDb();
	$tp = e107::getParser();
	$export_text = '';
	$qry = "SELECT * FROM `#banlist` ";
	if ($type_list != '') $qry .= " WHERE`banlist_bantype` IN ({$type_list})";
	if (!$sql->gen($qry)) return 'No data: '.$qry;
	while ($row = $sql->fetch())
	{
		$line = '';
		$spacer = '';
		foreach ($format_array as $f => $v)
		{
		  switch ($f)
		  {
			case 'banlist_ip' :
			case 'banlist_bantype' :
			case 'banlist_reason' :
			case 'banlist_notes' :
			  $line .= $spacer.$quot.$row[$f].$quot;
			  break;
			case 'banlist_datestamp' :
			case 'banlist_banexpires' :
			  if ($row[$f]) $line .= $spacer.$quot.$tp->toDate($row[$f], $v).$quot; else $line .= $spacer.$quot.'0'.$quot;
			  break;
		  }
		  $spacer = $sep;
		}
		$export_text .= $line."\n";
	}

	if (defined('CSV_DEBUG'))
	{
		$export_text .=  'Summary data: <br />';
		$export_text .= 'File: '.$filename.'<br />';
		$export_text .= 'Types: '.$type_list.'<br />';
		$export_text .= 'Query: '.$qry.'<br />';
		echo str_replace("\n","<br />",$export_text);
	}
	else
	{
		if(headers_sent())
		{
		  return "Cannot output file - some data already sent<br /><br />";
		}

		//Secure https check
		if (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT']=='contype') header('Pragma: public');
		if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'],'MSIE'))
			header('Content-Type: application/force-download');
		else
			header('Content-Type: application/octet-stream');
		header('Content-Length: '.strlen($export_text));
		header('Content-disposition: attachment; filename="'.$filename.'"');
		echo $export_text;
	}
}



// Log event to admin log
function banlist_adminlog($msg_num='00', $woffle='')
{
	e107::getLog()->add('BANLIST_'.$msg_num, $woffle, E_LOG_INFORMATIVE, '');
}


?>