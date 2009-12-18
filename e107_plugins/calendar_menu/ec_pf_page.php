<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	Event calendar - generate lists
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/ec_pf_page.php,v $
 * $Revision: 1.9 $
 * $Date: 2009-12-18 20:49:55 $
 * $Author: e107steved $
 */
/*
|
| Generate a printer-friendly page of calendar events
| Query is: ec_pf_page.php?ssssss.eeeeee[[[.cat].template].output]
|
| Date format is yyyymmdd or yyyymm to make it easy to generate fixed queries.
| cat is a number corresponding to a category ID. '*' or blank gives all categories
| template determines output style ('*' selects the default template)
| output can be 'display' (default), 'print' or 'pdf'
|
| Mostly the template can use the EVENT and MAIL shortcodes - pretty much anything that
| uses $thisevent as a parameter. MAIL is best since it's never used elsewhere at the same time
+----------------------------------------------------------------------------+
*/

require_once('../../class2.php');
$e107 = e107::getInstance();
if (!$e107->isInstalled('calendar_menu')) header('Location: '.e_BASE.'index.php');
include_lan(e_PLUGIN.'calendar_menu/languages/'.e_LANGUAGE.'.php');
define('PAGE_NAME', EC_LAN_80);

require_once(e_PLUGIN.'calendar_menu/ecal_class.php');
$ecal_class = new ecal_class;


e107::getScParser();
require_once(e_PLUGIN.'calendar_menu/calendar_shortcodes.php');
setScVar('event_calendar_shortcodes', 'ecalClass', &$ecal_class);			// Give shortcodes a pointer to calendar class

$message = '';
unset($ec_qs);
if (e_QUERY) $ec_qs = explode('.', e_QUERY); 
else
{
  if (!isset($pref['eventpost_printlists']) || ($pref['eventpost_printlists'] == 0))
    header('location:'.SITEURL);   // If disabled, just go back to index page
}

if (isset($_POST['set_dates']) && isset($_POST['start_date']) && (isset($_POST['end_date'])))
{
	$ec_qs[0] = $_POST['start_date'];
	$ec_qs[1] = $_POST['end_date'];
	if (isset($_POST['event_cat_ids']))
	{
		$ec_qs[2] = $_POST['event_cat_ids'];
		if ($ec_qs[2] == 'all') $ec_qs[2] = '*';
	}
	if (isset($_POST['template_choice'])) $ec_qs[3] = $_POST['template_choice'];
}

if (!isset($ec_qs[3])) $ec_qs[3] = 'default';		// Template

if (isset($_POST['output_type'])) $ec_qs[4] = $_POST['output_type'];
if (!isset($ec_qs[4]) || (($ec_qs[4]) != 'print') && ($ec_qs[4] != 'pdf') ) $ec_qs[4] = 'display';



$cal_super = $ecal_class->cal_super;

// Get templates, since we may have to give a choice if we're displaying something
// Actually load three in order so they can accumulate, and give the option of overriding other settings
$EVENT_CAL_PDF_HEADER = array();
$EVENT_CAL_PDF_BODY   = array();
$EVENT_CAL_PDF_FOOTER = array();
if (is_readable(e_PLUGIN.'calendar_menu/ec_pf_template.php')) require_once(e_PLUGIN.'calendar_menu/ec_pf_template.php');
if (is_readable(e_PLUGIN.'calendar_menu/ec_pf_user_template.php')) require_once(e_PLUGIN.'calendar_menu/ec_pf_user_template.php');
if (is_readable(THEME.'ec_pf_template.php')) require_once(THEME.'ec_pf_template.php');

// Hard-coded alternatives
if (!count($EVENT_CAL_PDF_HEADER)) $EVENT_CAL_PDF_HEADER['default'] = '<br />';
if (!count($EVENT_CAL_PDF_BODY))   $EVENT_CAL_PDF_BODY['default']   = '{EC_MAIL_DATE_START} {EC_MAIL_TIME_START}  {EC_MAIL_TITLE}<br />';
if (!count($EVENT_CAL_PDF_FOOTER)) $EVENT_CAL_PDF_FOOTER['default'] = '<br />';
if (!count($EVENT_CAL_PDF_NAMES))  $ec_pdf_template = 'default';
// If one name only, we just assign that
if (count($EVENT_CAL_PDF_NAMES) == 1)
{
  $ec_pdf_template = array_pop(array_keys($EVENT_CAL_PDF_NAMES));
//  echo "Assign template: ".$ec_pdf_template."<br />";
}

$ec_enable_pdf = ($pref['eventpost_printlists'] > 1) && is_readable(e_PLUGIN."pdf/e107pdf.php");

if (!isset($ec_qs[0]) || !isset($ec_qs[1]))
{
// Put up a prompt to get the view period
  require_once(HEADERF);
	$cal_text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."'>
	<table style='".USER_WIDTH."' class='fborder'>
	<colgroup>
	<col style='width:60%;vertical-align:top;' />
	<col style='width:40%;vertical-align:top;' />
	</colgroup>";
	$cal_text .= 	"<tr>
	<td class='forumheader3'>".EC_LAN_153."</td>
	<td class='forumheader3' style='text_align:center'>";
	$cal_text .= gen_drop(FALSE)."</td>
	</tr><tr>
	<td class='forumheader3'>".EC_LAN_154."</td>
	<td class='forumheader3' style='text_align:center'>".gen_drop(TRUE)."</td>
	</tr><tr>
	<td class='forumheader3'>".EC_LAN_155."</td>
	<td class='forumheader3' style='text_align:center'>";
	$cal_text .= $e107->tp->parseTemplate('{EC_NAV_CATEGORIES=nosubmit}',TRUE);
	$cal_text .= "</td>
	</tr>";
	if (isset($EVENT_CAL_PDF_NAMES) && is_array($EVENT_CAL_PDF_NAMES) && (count($EVENT_CAL_PDF_NAMES) > 1))
	{  // Offer choice of templates
	  $cal_text .= "<tr>
	  <td class='forumheader3'>".EC_LAN_157."</td>
	  <td class='forumheader3' style='text_align:center'><select name='template_choice' class='tbox' style='width:140px;' >\n";
	  foreach($EVENT_CAL_PDF_NAMES as $ec_template_name => $ec_template_choice)
	  {
	    $cal_text .= "<option value='{$ec_template_name}'>{$ec_template_choice}</option>\n";
	  }
	  $cal_text .= "</select></td>
	  </tr>\n";
	}
	// Radio buttons to select output type
	$cal_text .= "<tr>
	<td class='forumheader3'>".EC_LAN_158."</td>
	<td class='forumheader3' style='text_align:center'>";
	$cal_text .= "
	<input type='radio' name='output_type' value='display' checked='checked' /> ".EC_LAN_159."<br />
	<input type='radio' name='output_type' value='print' /> ".EC_LAN_160."<br />";
	if ($ec_enable_pdf)
	{
	  $cal_text .= "<input type='radio' name='output_type' value='pdf' /> ".EC_LAN_161;
	}
	$cal_text .="</td></tr>";
	
	$cal_text .= "<tr><td colspan='2'  style='text-align:center' class='fcaption'><input class='button' type='submit' name='set_dates' value='".EC_LAN_156."' /></td></tr>";

	$cal_text .= "</table></form></div>";
  $ns->tablerender(EC_LAN_150, $cal_text);
  require_once(FOOTERF);
  exit;
}


if (!is_numeric($ec_start_date = decode_date($ec_qs[0],FALSE)))
{
  $message = $ec_start_date;
}
elseif (!is_numeric($ec_end_date = decode_date($ec_qs[1],TRUE)))
{
  $message = $ec_end_date;
}
elseif ($ec_start_date >= $ec_end_date)
{
  $message = EC_LAN_151;
}
elseif (($ec_end_date - $ec_start_date) > 366*86400)
{
  $message = EC_LAN_152;
}

// That's the vetting of the query done (as much as we'll do)
if ($message !== "")
{
  require_once(HEADERF);
  $ns->tablerender(EC_LAN_80, $message);
  require_once(FOOTERF);
  exit;
}

setScVar('event_calendar_shortcodes', 'ecalClass', &$ecal_class);			// Give shortcodes a pointer to calendar class
setScVar('event_calendar_shortcodes', 'catFilter', $cat_filter);			// Category filter

$ec_output_type = $ec_qs[4];
if (isset($ec_qs[5])) $ec_list_title = $ec_qs[5]; else $ec_list_title = EC_LAN_163;
$ec_list_title = str_replace('_',' ',$ec_list_title);

if (($ec_output_type == 'pdf') && !$ec_enable_pdf) $ec_output_type = 'display';
if ($ec_output_type == 'display') require_once(HEADERF);


// Allow a number of categories separated by a '&'
$cat_filter = 0;
$ec_category_list = EC_LAN_97;				// Displayable version of categories - default to 'all'
if (isset($ec_qs[2]) && ($ec_qs[2] != '*'))
{
  $ec_category_list = array();
  $temp = explode('&',$ec_qs[2]);
  foreach($temp as $t1)
  {
    if (!is_numeric($t1)) unset($t1);
  }
  
  // Now look up the category names in the database - check access rights at the same time
  $temp = array();   // Accumulate valid category IDs
  $cal_qry = "SELECT event_cat_id, event_cat_name FROM #event_cat WHERE find_in_set(event_cat_id, '{$ec_qs[2]}') ".$ecal_class->extra_query;
  if ($sql->db_Select_gen($cal_qry))
  {
    while ($thiscat = $sql->db_Fetch())
	{
	  $temp [] = $thiscat['event_cat_id'];
	  $ec_category_list[] = $thiscat['event_cat_name'];
	}
	$cat_filter = implode(',',$temp);	// Gives us a comma separated numeric set of categories
  }
  else
  {
    echo EC_LAN_100."<br /><br />";
	exit;
  }
}

// $ec_start_date - earliest date of period
// $ec_end_date - latest date of period

// We'll potentially need virtually all of the event-related fields, so get them regardless. Just cut back on category fields
$ev_list = $ecal_class->get_events($ec_start_date, $ec_end_date, FALSE, $cat_filter, TRUE, '*', 'event_cat_name,event_cat_icon');
// Now go through and multiply up any recurring records
  $tim_arr = array();
  foreach ($ev_list as $k=>$event)
  {
    if (is_array($event['event_start']))
	{
	  foreach ($event['event_start'] as $t)
	  {
	    $tim_arr[$t] = $k;
	  }
	}
	else
	{
	  $tim_arr[$event['event_start']] = $k;
	}
  }
  
  ksort($tim_arr);  // Sort into time order



if (isset($ec_qs[3])) $ec_pdf_template = $ec_qs[3];
if (!isset($ec_pdf_template) || !array_key_exists($ec_pdf_template,$EVENT_CAL_PDF_NAMES)) $ec_pdf_template = 'default';

/*
// These available to templates/shortcodes to pick up change of start day/month/year
global $ec_last_year, $ec_last_month, $ec_last_day, $ec_year_change, $ec_month_change, $ec_day_change;
global $ec_start_date, $ec_end_date, $ec_pdf_options;
global $ec_current_month, $thisevent_start_date, $thisevent_end_date ;
*/

setScVar('event_calendar_shortcodes', 'printVars', array('lt' => $ec_list_title, 'cat' => $ec_category_list, 'ot' => $ec_output_type,
				'sd' => $ec_start_date, 'ed' => $ec_end_date));					// Give shortcodes the event data

$ec_last_year = 0;
$ec_last_month = 0;
$ec_last_day = 0;

$cal_text = '';
$cal_totev = count($ev_list);
if ($cal_totev > 0)
{
	if (isset($ec_template_styles[$ec_pdf_template]) && is_array($ec_template_styles[$ec_pdf_template]))
	{
		$ec_current_overrides = $ec_template_styles[$ec_pdf_template];    // Possible array of codes to override standard $sc_style
		$sc_style = array_merge($sc_style,$ec_current_overrides);			// Override as necessary
	}

	// If printing, wrap in a form so the button works 
	if ($ec_output_type == 'print') $cal_text .= "<form action=''>\n";
	// Add header
	$cal_text .= $e107->tp->parseTemplate($EVENT_CAL_PDF_HEADER[$ec_pdf_template],TRUE);
	// Debug code
	//  echo "Start date: ".strftime("%d-%m-%Y %H:%M:%S",$ec_start_date)."<br />";
	//  echo "End date:   ".strftime("%d-%m-%Y %H:%M:%S",$ec_end_date)."<br />";
	//  echo "Template:   ".$ec_pdf_template,"<br />";
	//  echo "Header:     ".$EVENT_CAL_PDF_HEADER[$ec_pdf_template]."<br />";
	//  echo "Body:       ".$EVENT_CAL_PDF_BODY[$ec_pdf_template]."<br />";
	//  echo "Footer:     ".$EVENT_CAL_PDF_FOOTER[$ec_pdf_template]."<br />";
  
	foreach ($tim_arr as $tim => $ptr)
	{
		$ev_list[$ptr]['event_start'] = $tim;
		$thisevent = $ev_list[$ptr];
		// Decode dates into individual fields - we're bound to want them
		$thisevent_start_date = $ecal_class->gmgetdate($thisevent['event_start']);
		$thisevent_end_date   = $ecal_class->gmgetdate($thisevent['event_end']);

		$ec_year_change = ($ec_last_year != $thisevent_start_date['year']);
		$ec_month_change = ($ec_last_month != $thisevent_start_date['mon']);
		$ec_day_change   = ($ec_last_day != $thisevent_start_date['mday']);

		$cal_totev --;    // Can use this to modify inter-event gap
		setScVar('event_calendar_shortcodes', 'numEvents', $cal_totev);				// Number of events to display
		setScVar('event_calendar_shortcodes', 'event', $thisevent);					// Give shortcodes the event data
		setScVar('event_calendar_shortcodes', 'changeFlags', array('yc' => $ec_year_change, 'mc' => $ec_month_change, 'dc' => $ec_day_change));					// Give shortcodes the event data
		$cal_text .= $e107->tp->parseTemplate($EVENT_CAL_PDF_BODY[$ec_pdf_template],TRUE);
		  
		$ec_last_year = $thisevent_start_date['year'];
		$ec_last_month = $thisevent_start_date['mon'];
		$ec_last_day = $thisevent_start_date['mday'];
	}

// Add footer
  $cal_text .= $e107->tp->parseTemplate($EVENT_CAL_PDF_FOOTER[$ec_pdf_template],TRUE);
  if ($ec_output_type == 'print') $cal_text .= "</form>\n";
}
else
{
  $cal_text.= EC_LAN_148;
}

switch($ec_output_type)
{
	case 'display':
		$e107->ns->tablerender(EC_LAN_80, $cal_text, 'ec_pf_page');
		require_once (FOOTERF);
	break;
	
	case 'print':
		echo $cal_text;
		break;
	
	case 'pdf':
		//TODO find a way to pass initialisation options etc to PDF driver
		include_lan(e_PLUGIN.'pdf/languages/'.e_LANGUAGE.'.php');
//		define('FPDF_FONTPATH', 'font/');
		//require the ufpdf class
//		require_once (e_PLUGIN.'pdf/ufpdf.php');
		//require the e107pdf class
		require_once (e_PLUGIN.'pdf/e107pdf.php');
		$pdf = new e107PDF();
		//	$text = array($text, $creator, $author, $title, $subject, $keywords, $url);
		$text = array(
			$cal_text,
			'',
			'',
			EC_LAN_163,			// Title
			'',
			'',
			e_SELF.'?'.e_QUERY,	// URL
			''					// Page orientation
			);
		$pdf->makePDF($text);
		break;

}



// We're assuming $date_string is a string of digits
// Which could begin with 'now' or 'now+'
function decode_date($date_string, $last_day = FALSE)
{  // Decode a date string
	if (strpos($date_string, 'now') === 0)
	{
		// decode special dates
		$today = getdate();
		// Knock off the 'now'
		$date_string = trim(substr($date_string, 3));
		if (($date_string != '') && ($date_string[0] == '+'))
		{
			// Knock off the '+'
			$date_string = trim(substr($date_string, 1));
			if (is_numeric($date_string) && ($date_string >= 0) && ($date_string <= 12))
			{
				$today['mon'] += $date_string;
				if ($today['mon'] > 12)
				{
					$today['mon'] -= 12;
					$today['year'] += 1;
				}
			}
			else
			{
				return EC_LAN_149;
			}
		}
		$date_string = $today['year'].$today['mon'];
	}
	
	// Here, $date_string is a string of 5, 6 or 8 digits
	// use preg_match()
	if(preg_match('/^\d{5,8}$/D', $date_string))
	{
		$month = 0;
		$day   = 1;
		if (strlen($date_string) == 5)
			$date_string = substr_replace($date_string, '0', -1, 0);
		if (strlen($date_string) == 8)
		{
			$day = substr($date_string, -2, 2);
			if ($last_day)
				$day += 1;
		}
		elseif (strlen($date_string) == 6)
		{
			if ($last_day)
				$month = 1;
		}
		else
		{
			// Error
			return EC_LAN_149;
		}
		$month += substr($date_string, 4, 2);
		$year   = substr($date_string, 0, 4);
		$temp   = mktime(0, 0, 0, $month, $day, $year);
		// Always do this to get whole of last day
		if ($last_day)
			$temp -= 1;
		return $temp;
	}
	else
	{    // Error
		return EC_LAN_149;
	}
}


// Generate monthly drop-down - FALSE = first, TRUE = last
// For the first date we want beginning of previous year to end of current year
// For the last date we want end of next 
function gen_drop($drop_type)
{
	$text = "<select name='".($drop_type ? 'end_date' : 'start_date')."' class='tbox' style='width:140px;' >\n";
	if ($drop_type)
	{
		$start_date = strtotime('-3 months');
		$match_date = strtotime('+3 months');	// Propose 3-month list
	}
	else
	{
		$start_date = strtotime('-9 months');
	//	$match_date = strtotime('-1 months');
		$match_date = time();    // Use current month for start date
	}

	// Get date to be 1st of month
	$date = getdate($match_date);
	$match_date = mktime(0,0,0,$date['mon'],1,$date['year'],FALSE);
 
	for ($i = 0; $i < 24; $i++)
	{ 
		$sel_text = (($match_date == $start_date) ? "selected='selected'" : "");
		$date = getdate($start_date);
		$text .= "<option value = '{$date['year']}{$date['mon']}' {$sel_text}>{$date['month']} {$date['year']} </option>\n";
		$start_date = mktime(0,0,0,$date['mon']+1,1,$date['year'],FALSE);
	}
	$text .= "</select>\n";
	return $text;
}
?>
