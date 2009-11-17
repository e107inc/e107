<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     �Steve Dun.an 2001-2002
|     http://e107.org
|     jali.@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/subs_menu.php,v $
|     $Revision: 1.6 $
|     $Date: 2009-11-17 07:40:43 $
|     $Author: e107coders $
|
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

// This menu is best called from a cron job - see readme.pdf
global $ec_default_msg_1, $ec_default_msg_2, $ec_log_requirement, $ec_debug_level, $ec_run_from_menu, $tp;


global $ecal_class;
if (!is_object($ecal_class))
{
  require_once('ecal_class.php');
  $ecal_class = new ecal_class;
}

// Work out whether we're being called as a menu (i.e. within a displayed page) or not
$ec_run_from_menu = (defined('USER_AREA') && USER_AREA) || (defined('ADMIN_AREA') && ADMIN_AREA);
//echo ($ec_run_from_menu == TRUE ? "Run from menu" : "Standalone")."<br />";

if ($ec_run_from_menu)
{
  if ($cacheData = $e107cache->retrieve("nomd5_cal_subs",$ecal_class->max_cache_time, TRUE)) exit;
}

include_lan(e_PLUGIN."calendar_menu/languages/".e_LANGUAGE.".php");		// May be needed for mailouts

if (!isset($calendar_shortcodes)) require(e_PLUGIN."calendar_menu/calendar_shortcodes.php");
if (is_readable(THEME."ec_mailout_template.php")) 
{  // Has to be require
  require(THEME."ec_mailout_template.php");
}
else 
{
  require(e_PLUGIN."calendar_menu/ec_mailout_template.php");
}


$ec_debug_level = 0;			// Set to 1 or 2 to suppress actual sending of emails
$ec_default_msg_1 = "";
$ec_default_msg_2 = "";


if (($ec_debug_level > 0) && e_QUERY)
{  // Run with query of ?dd-mm[-yy] to test specific date
  list($day,$month,$year) = explode("-",e_QUERY);
  if (!isset($year) || ($year == 0)) $year = date("Y");
  $cal_starttime = mktime(0,0,0,$month,$day,$year);
  echo "Debug run for {$day}-{$month}-{$year}<br />";
}
else
{  // Normal operation
  $cal_starttime = mktime(0, 0, 0, date("n"), date("d"), date("Y"));
}


$ec_log_requirement = 0;		// Logging required 0=none, 1=summary, 2=detailed
if (isset($pref['eventpost_emaillog'])) $ec_log_requirement = $pref['eventpost_emaillog'];
if ($ec_debug_level >= 2) $ec_log_requirement = 2;	// Force full logging if debug


function subs_log_a_line($log_text,$close_after = FALSE, $log_always = FALSE)
{
  global $ec_log_requirement, $ec_run_from_menu;
  if ($ec_log_requirement == 0) return;
  if ($ec_run_from_menu && ($log_always == FALSE)) return;
//  echo "Logging: ".$log_text."<br />";
  static $handle = NULL;
  $log_filename = e_MEDIA."logs/calendar_mail.txt";
  if ($handle == NULL)
  {
    if (!($handle = fopen($log_filename, "a"))) 
	{ // Problem creating file?
	  echo "File open failed!<br />";
	  $ec_log_requirement = 0; 
	  return; 
	}
  }
  
  if (fwrite($handle,$log_text) == FALSE) 
  {
    $ec_log_requirement = 0;
	echo "File write failed!<br />";
  }
  
  if ($close_after)
  {
    fclose($handle);
	$handle = NULL;
  }
}


// Start of the 'real' code
subs_log_a_line("\r\n\r\nMail subscriptions run started at ".date("D j M Y G:i:s"),TRUE,FALSE);



// Start with the 'in advance' emails
$cal_args = "select * from #event left join #event_cat on event_category=event_cat_id where (event_cat_subs>0 OR event_cat_force_class>0) and 
event_cat_last < " . intval($cal_starttime) . " and 
event_cat_ahead > 0 and 
event_start >= (" . intval($cal_starttime) . "+(86400*(event_cat_ahead))) and
event_start <  (" . intval($cal_starttime) . "+(86400*(event_cat_ahead+1))) and
find_in_set(event_cat_notify,'1,3,5,7')";

ec_send_mailshot($cal_args, 'Advance',1, $calendar_shortcodes);



// then for today
$cal_args = "select * from #event left join #event_cat on event_category=event_cat_id where (event_cat_subs>0 OR event_cat_force_class>0) and 
event_cat_today < " . intval($cal_starttime) . " and 
event_start >= (" . intval($cal_starttime) . ") and
event_start <  (86400+" . intval($cal_starttime) . ") and
find_in_set(event_cat_notify,'2,3,6,7')";

ec_send_mailshot($cal_args, 'today',2, $calendar_shortcodes);


if ($ec_debug_level == 0)
{
// Finally do 'day before' emails (its an alternative to 'today' emails)
$cal_args = "select * from #event left join #event_cat on event_category=event_cat_id where (event_cat_subs>0 OR event_cat_force_class>0) and 
event_cat_today < " . intval($cal_starttime) . " and 
event_start >= (" . intval($cal_starttime) ." + 86400 ) and
event_start <  (" . intval($cal_starttime) ." + 172800) and
find_in_set(event_cat_notify,'4,5,6,7')";
}
else
{
// Finally do 'day before' emails (its an alternative to 'today' emails)
$cal_args = "select * from #event left join #event_cat on event_category=event_cat_id where (event_cat_subs>0 OR event_cat_force_class>0) and 
event_start >= (" . intval($cal_starttime) ." + 86400 ) and
event_start <  (" . intval($cal_starttime) ." + 172800) and
find_in_set(event_cat_notify,'4,5,6,7')";
}

ec_send_mailshot($cal_args, 'tomorrow',2, $calendar_shortcodes);


subs_log_a_line("\r\n .. completed at ".date("D j M Y G:i:s")."\r\n",TRUE,FALSE);

// This stops the mailout running again until first access of tomorrow
if ($ec_run_from_menu)
{
  $e107cache->set("nomd5_cal_subs", time(),TRUE);
}

// Done



// Function called to load in default templates (messages) if required - only accesses database once
function ec_load_default_messages()
{
  global $sql2, $ec_default_msg_1, $ec_default_msg_2;
  if (($ec_default_msg_1 != "") && ($ec_default_msg_2 != "")) return;
  if ($sql2->db_Select("event_cat", "*", "event_cat_name = '".EC_DEFAULT_CATEGORY."' "))
  { 
    if ($row = $sql2->db_Fetch())
	{
	  $ec_default_msg_1 = $row['event_cat_msg1'];
	  $ec_default_msg_2 = $row['event_cat_msg2'];
	}
  }
  // Put in generic message rather than nothing - will help flag omission
  if ($ec_default_msg_1 == "") $ec_default_msg_1 = EC_LAN_146;
  if ($ec_default_msg_2 == "") $ec_default_msg_2 = EC_LAN_147;
}

/*
  Function to actually send a mailshot
*/
function ec_send_mailshot($cal_query, $shot_type, $msg_num, $calendar_shortcodes)
{
  global $sql, $sql2;
  global  $ec_debug_level, $ec_log_requirement;
  global $pref, $tp, $thisevent;
  global $ec_default_msg_1, $ec_default_msg_2;
  
  if ($ec_log_requirement > 1)
  {
	subs_log_a_line("\r\n  Starting emails for ".$shot_type." at ".date("D j M Y G:i:s"),FALSE,FALSE);
    if ($ec_debug_level >= 2) subs_log_a_line("\r\n    Query is: ".$cal_query."\r\n",FALSE,FALSE);
  }

  if  ($num_cat_proc = $sql->db_Select_gen($cal_query))
  {  // Got at least one event to process here
    if ($ec_log_requirement > 1)
      subs_log_a_line(" - ".$num_cat_proc." categories found to process\r\n",FALSE,TRUE);

    require_once(e_HANDLER . "mail.php");
    while ($cal_row = $sql->db_Fetch())
    {  // Process one event at a time
	  $thisevent = $cal_row;    // Used for shortcodes
      extract($cal_row);
		
      subs_log_a_line("    Processing event: ".$event_title." \r\n",FALSE,TRUE);

	  // Note that event processed, and generate the email
	  if ($msg_num == 1)
	  {
        $sql2->db_Update("event_cat", "event_cat_last=" . time() . " where event_cat_id=" . intval($event_cat_id));
//        $cal_msg = $event_title . "\n\n" . $event_cat_msg1;
        $cal_msg = $event_cat_msg1;
		if (trim($cal_msg) == "") 
		{
		  ec_load_default_messages();
		  $cal_msg = $ec_default_msg_1;
		}
	  }
	  else
	  {
        $sql2->db_Update("event_cat", "event_cat_today=" . time() . " where event_cat_id=" . intval($event_cat_id));
//        $cal_msg = $event_title . "\n\n" . $event_cat_msg2;
        $cal_msg = $event_cat_msg2;
		if (trim($cal_msg) == "") 
		{
		  ec_load_default_messages();
		  $cal_msg = $ec_default_msg_2;
		}
	  }
	  // Parsing the template here means we can't use USER-related shortcodes
	  // Main ones which are relevant: MAIL_DATE_START, MAIL_TIME_START, MAIL_DATE_END,
	  //	MAIL_TIME_END, MAIL_TITLE, MAIL_DETAILS, MAIL_CATEGORY, MAIL_LOCATION, 
	  //	MAIL_CONTACT, MAIL_THREAD (maybe). Also MAIL_LINK, MAIL_SHORT_DATE 
	  // Best to strip entities here rather than at entry - handles old events as well
	  $cal_title = html_entity_decode($tp -> parseTemplate($pref['eventpost_mailsubject'], FALSE, $calendar_shortcodes),ENT_QUOTES,CHARSET);
	  $cal_msg = html_entity_decode($tp -> parseTemplate($cal_msg, FALSE, $calendar_shortcodes),ENT_QUOTES,CHARSET);
		//	  $cal_msg = str_replace("\r","\n",$cal_msg);
	  
			// Four cases for the query:
			//	1. No forced mailshots - based on event_subs table only									Need INNER JOIN
			//	2. Forced mailshot to members - send to all users (don't care about subscriptions)		Don't need JOIN
			// 	3. Forced mailshot to group of members - based on user table only						Don't need JOIN
			//	4. Forced mailshot to group, plus optional subscriptions - use the lot!    				Need LEFT JOIN
			// (Always block sent to banned members)
	  $manual_subs = (isset($pref['eventpost_asubs']) && ($pref['eventpost_asubs'] == '1'));
	  $subs_fields = '';
	  $subs_join = '';
			$whereClause = '';
	  $group_clause = '';
	  
	  if ($event_cat_force_class != e_UC_MEMBER)
	  {  // Cases 1, 3, 4 (basic query does for case 2)
	    if ((!$event_cat_force_class) || ($manual_subs))
	    {  // Cases 1 & 4 - need to join with event_subs database
	      $subs_fields = ", es.* ";
		  if ($event_cat_force_class) $subs_join = "LEFT"; else $subs_join = "INNER";
	      $subs_join   .= " join #event_subs AS es on u.user_id=es.event_userid ";
					$whereClause = " es.event_cat='".intval($event_category)."' ";
		  $group_clause = " GROUP BY u.user_id";
	    }

	    if ($event_cat_force_class)
		{  // cases 3 and 4 - ... and check for involuntary subscribers
					if ($whereClause) $whereClause .= " OR ";
		    if ($event_cat_force_class == e_UC_ADMIN)
		    {
						$whereClause .= "(u.user_admin = 1 )";
		    }
		    else
		    {
						$whereClause .= "find_in_set('".intval($event_cat_force_class)."', u.user_class)";
//						$forced_regexp = "'(^|,)(".intval($event_cat_force_class).")(,|$)'";
//						$whereClause .= " (u.user_class REGEXP '".$forced_regexp."') ";
						$group_clause = " GROUP BY u.user_id";
		    }
		}

				if ($whereClause) $whereClause = ' AND ('.$whereClause.' ) ';
	  }   // End of cases 1, 3, 4
	  
	  $cal_emilargs = "SELECT u.user_id, u.user_class, u.user_email, u.user_name, u.user_ban, u.user_admin{$subs_fields}
		  from #user AS u {$subs_join}
			  WHERE u.user_ban = '0' {$whereClause} {$group_clause}";
		  

        if ($ec_debug_level >= 2)
		{
		  subs_log_a_line("\r\n    Email selection query is: ".$cal_emilargs."\r\n",FALSE,TRUE);
		}
        if ($num_shots = $sql2->db_Select_gen($cal_emilargs))
        {
          subs_log_a_line(" - ".$num_shots." emails found to send\r\n",FALSE,TRUE);

          while ($cal_emrow = $sql2->db_Fetch())
          {
            extract($cal_emrow);
            if ($ec_debug_level == 0) 
			    $send_result = sendemail($user_email, $cal_title, $cal_msg, $user_name, $pref['eventpost_mailaddress'], $pref['eventpost_mailfrom']); 
			  else
			    $send_result = " **DEBUG**";
			  if ($ec_log_requirement > 1)
			  {
						subs_log_a_line("      Send to ".$user_id.':'.$user_email." Name: ".$user_name." Result = ".$send_result."\r\n",FALSE,TRUE);
			  }
            } 
        } 
    } // while    
    if ($ec_log_requirement > 1)
    {
	  subs_log_a_line("  Completed emails for ".$shot_type." at ".date("D j M Y G:i:s")."\r\n",TRUE,TRUE);
	}
  }
} 



?>