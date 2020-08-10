<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/log/stats_csv.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

//define('CSV_DEBUG', TRUE);


require_once('../../class2.php');
if (!getperms('P')) 
{
	e107::redirect();
	exit;
}


if (e_QUERY) 
{
  $sl_qs = explode('.', e_QUERY);
}
$action = varset($sl_qs[0],'config');
$params = varset($sl_qs[1],'');
if (($action != 'export') || $params) 
{
	e107::redirect();
	exit;
}

e107::includeLan(e_PLUGIN.'log/languages/'.e_LANGUAGE.'_admin_log.php');		// LANs may be needed for error messages


// List of the non-page-based info which is gathered - historically only 'all-time' stats, now we support monthly as well. (Here, only keys are used for validation)
$stats_list = array('statBrowser'=>ADSTAT_L6,'statOs'=>ADSTAT_L7,'statScreen'=>ADSTAT_L8,'statDomain'=>ADSTAT_L9,'statReferer'=>ADSTAT_L10,'statQuery'=>ADSTAT_L11);

$separator_char = array(1 => ',', 2 => '|');
$quote_char = array(1 => '', 2 => "'", 3 => '"');


//---------------------------------------------
//		Export data file
//---------------------------------------------
$export_filter = '';		// can be 'LIKE', 'REGEX', or simple equality
$export_type = $tp->toDB(varset($_POST['export_type'],'page'));				// Page data or one of the other bits of info
$export_date = intval(varset($_POST['export_date'],1));
$export2_date = intval(varset($_POST['export2_date'],3));
$export_year = intval(varset($_POST['export_year'],date('Y')));
$export_month = intval(varset($_POST['export_month'],date('m')));
$export_day = intval(varset($_POST['export_day'],date('j')));
$export_char = varset($_POST['export_char'], 1);
$export_quote = varset($_POST['export_quote'], 1);
$export_stripurl = varset($_POST['export_stripurl'], 0);

if (isset($_POST['create_export']) && $action == 'export')
{
  $first_date = 0;
  $last_date = 0;
  $date_error = FALSE;
  if ($export_type == 'page')
  {
    switch ($export_date)
    {
    case '1' :		//	Single day
	  $first_date = gmmktime(0,0,0,$export_month,$export_day,$export_year);
	  $last_date = $first_date+86399;
	  $export_filter = " `log_id`='".date("Y-m-j",$first_date)."'";
	  break;
    case '2' :		// Daily for a month
	  $first_date = gmmktime(0,0,0,$export_month,1,$export_year);
	  $last_date = gmmktime(0,0,0,$export_month+1,1,$export_year) - 1;
	  $export_filter = " LEFT(`log_id`,8)='".gmstrftime("%Y-%m-",$first_date)."'";
	  break;
    case '3' :		// Monthly for a Year
	  $first_date = gmmktime(0,0,0,1,1,$export_year);
	  $last_date = gmmktime(0,0,0,1,1,$export_year+1) - 1;
	  $export_filter = " LENGTH(`log_id`)=7 AND LEFT(`log_id`,5)='".gmstrftime("%Y-",$first_date)."'";
	  break;
    case '4' :		// Accumulated
	case '5' :
	  $export_filter = "`log_id`='pageTotal'";
	  $date_error = 'ignore';
	  break;
    }
  }
  else
  {  // Calculate strings for non-page sources
	$prefix_len = 0;
	$export_date = $export2_date;
	if (isset($stats_list[$export_type]))
	{
	  $prefix_len = strlen($export_type) + 1;
      switch ($export2_date)
      {
      case '3' :		// Monthly for a Year
		if ($prefix_len > 0)
		{
	      $first_date = gmmktime(0,0,0,1,1,$export_year);
	      $last_date = gmmktime(0,0,0,1,1,$export_year+1) - 1;
	      $export_filter = " LENGTH(`log_id`)='".($prefix_len + 7)."' AND LEFT(`log_id`,".($prefix_len + 5).")='".$export_type.":".gmstrftime("%Y-",$first_date)."'";
		}
	    break;
      case '4' :		// Accumulated
	    $export_filter = " `log_id`='".$export_type."'";
		$date_error = 'ignore';
	    break;
      }
	}
	else
	{
	  $message = ADSTAT_L54;
	}
  }
  if (($date_error != 'ignore') && (($first_date == 0) || ($last_date == 0) || $date_error))
  {
    $message = ADSTAT_L47;
  }
  else
  {	// Actually do export
    $message = export_stats($export_type, $export_date, $export_filter, $first_date, $last_date, $separator_char[$export_char], $quote_char[$export_quote], $export_stripurl);
  } 
}


if (isset($message) && $message) 
{
  require_once(e_ADMIN."auth.php");
  $ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
  require_once(e_ADMIN."footer.php");
}

//---------------------------------------------
//		Generate export statistics
//---------------------------------------------
// All but page data returns a single level array
// $export_type - base type of stats - 'page' for page data, or one of the 'generic' types
// $export_date - date range/interval type - 1 = single day, 2 = daily for a month, 3 = monthly for a year, 4 = 'all-time'
// $export_filter - DB filter to return the required records
// $first_date, $last_date - date range
// $separator = ',', - character placed between entries
// $quote_char = character placed around entries
// $strip_url - if true, just outputs page names
// $target=''
function  export_stats($export_type, $export_date, $export_filter, $first_date, $last_date, $separator = ',', $quote_char = '', $strip_url = 0, $target='')
{
  $stat_types = array( 1 => 'day', 2 => 'month', 3 => 'year', 4 => 'alltime', 5 => 'all_detail');
  global $sql, $e107;
  $sql -> db_Select("logstats", "*", "{$export_filter} ");

  $export_text = '';				// Accumulate output string
  $export_array = array();
  $process_mode = 0;
  $values_per_entry = 1;
  $values_per_row = 1;
  $empty_data = 0;
  
  $replace_url = $e107->http_path;
  if (strpos($e107->http_path,'www.'))
  {
    $replace_url = array($e107->http_path,str_replace('www.','',$e107->http_path));
  }
  
  if ($export_type == 'page') 
  {
    $values_per_entry = 2;
	$empty_data = array('ttlv' => 0, 'unqv' => 0);
  }
  else
  {
    $strip_url = 0;		// Only get URLs in page data!
  }
  $filename = $stat_types[$export_date]."_";
  switch ($export_date)
  {
    case 1 : 
	  $filename .= $export_type.'_day_'.date('Ymd',$first_date);
	  break;
    case 2 : 
	  $filename .= $export_type.'_mon_'.date('Ymd',$first_date).'_'.date('Ymd',$last_date);
	  $values_per_row = 31;
	  break;
    case 3 : $filename .= $export_type.'_year_'.date('Ym',$first_date).'_'.date('Ym',$last_date);
	  $values_per_row = 12;
	  break;
    case 4 : 
//	  $filename .= $export_type.'_alltime';
	  $filename .= $export_type;
	  break;
    case 5 : 
//	  $filename .= $export_type.'_alltime';
	  $filename .= $export_type;
	  break;
  }
  $filename .= '.csv';
  if (defined('CSV_DEBUG')) $export_text .= "export stats to {$filename}<br />";
  
  while($row = $sql -> db_Fetch())
  {	// Process one DB entry
    $date_id = substr($row['log_id'],strrpos($row['log_id'],'-')+1);	// Numeric value of date being processed (not always valid)
	if (!is_numeric($date_id)) $date_id = 0;
    if (defined('CSV_DEBUG')) $export_text .= "Reading: ".$row['log_id']."  Date value: {$date_id}<br />";
	if (($export_type == 'page') && (($export_date == 1) || ($export_date == 2)))
	{	// The daily page data files have a different format to the rest
	  list($daily, $unique, $db_data) = explode(chr(1),$row['log_data']);
	  $db_data = explode(chr(1),$db_data);		// Individual entries
	  $process_mode = 1;
	}
	else
	{
	  $db_data = unserialize($row['log_data']);
	}
	
	foreach ($db_data as $k => $db_v)
	{
	  if ($process_mode == 1)
	  {
	    list($url, $total, $unique) = explode('|', $db_v);
	  }
	  elseif ($export_type == 'page')
	  {
	    if ($export_date == 4)
		{
	      $url = $k;				// - the key here is the page URL without any query part
		}
		else
		{
		  $url = $db_v['url'];
		}
		$total = $db_v['ttlv'];
		$unique = $db_v['unqv'];
	  }
	  else
	  {
	    $url = $k;			// Will actually be browser type or similar
		$total = $db_v;
		$unique = 0;
	  }
	  
	  if ($strip_url)
	  {
		$url = str_replace($replace_url,"",$url);	// We really just want a relative path. Strip out the root bit
	  }
	  // At this point we've identified a URL (or browser type, etc) and one or two values (two values for page data, one for other?). Add to an array
	  // Monthly stats:
	  // For the page data, each array entry has a key of the url and a value which is an array with two keys - ['ttlv'] (total accesses) and ['unqv'] (unique accesses)
	  //					the first entry has the url 'TOTAL'
	  // All-time stats:
	  //  Page data has array entries with three keys: 'url', 'ttlv', 'unqv'
	
// Work with an array where each entry is an array with up to 31 values. The key of each entry is page name, browser type etc. Within the value we have numeric keys corresponding 
// to months 1..12, or days 1..31, or zero for all-time stats and single day stats. For most stats the value will be within these keys; for page data we have a further level
// of arrays with keys ['ttlv'] and ['unqv']
//		echo $total.", ".$unique.", ".$url."<br />";
		if (!isset($export_array[$url][$date_id]))
		{  // Need to create an array
		  if ($values_per_row == 1)
		  {
		    $export_array[$url][$date_id] = $empty_data;
		  }
		  else
		  {
		    for ($i = 1; $i <= $values_per_row; $i++)
			{
		      $export_array[$url][$i] = $empty_data;
			}
		  }
		}

	  if ($values_per_entry == 1)
	  {
	    $export_array[$url][$date_id] = $total;
	  }
	  else
	  {
	    $export_array[$url][$date_id]['ttlv'] = $total;
	    $export_array[$url][$date_id]['unqv'] = $unique;
	  }
	}
  }
  
  foreach ($export_array as $url => $data)
  {
    $export_text .= $quote_char.$url.$quote_char;
	foreach ($data as $day => $values)
	{
	  if (is_array($values))
	  {
	    foreach ($values as $info => $val)
		{
		  if (!($val)) $val = 0;
		  $export_text .= $separator.$quote_char.$val.$quote_char;
		}
	  }
	  else
	  {
		if (!($val)) $val = 0;
	    $export_text .= $separator.$quote_char.$values.$quote_char;
	  }
	}
	$export_text .= "\n";
  }

  if (defined('CSV_DEBUG'))
  {
    $export_text .=  "Summary data: <br />";
	echo str_replace("\n","<br />",$export_text);
  }
  else
  {
	if(headers_sent())
	{
	  return "Cannot output file - some data sent<br /><br />";
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

  return;
}



