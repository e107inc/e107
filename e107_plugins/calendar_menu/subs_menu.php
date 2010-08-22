<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
| 09.07.06 - Mods by steved:
|	General restructuring to use common routines
| 	Support for sending emails on previous day.
|	Logging capability
| 	Debugging option
|
| 11.07.06 - Adjustment to logging messages
| 12.07.06 - More adjustment to logging messages
| 15.07.06 - Adjustment to 'tomorrow' query
| 17.07.06 - More adjustment to 'tomorrow' query
|
| 04.10.06 - Mods to mailout to allow mix of voluntary and forced subs to the same event
| 24.10.06 - Change DB names so works as a menu
| 25.10.06 - Logging selectively disabled when run as menu
| 27.10.06 - Update queries to new structure, don't email banned users
| 31.10.06 - Attempt to optimise query better
| 01.11.06 - More refinements on query
| 05.11.06 - More refinement on query - ignores midnight at end of day.   **** BANG ****
|
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

// This menu can be called from a cron job - see readme.rtf
$run_from_menu = function_exists("parseheader");		// Use this to suppress logging in 'through' path
$ec_dir = e_PLUGIN . "calendar_menu/";
// Check if we are going to do the notify

$debug_level = 0;			// Set to 1 or 2 to suppress actual sending of emails

if (($debug_level > 0) && e_QUERY)
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

$log_requirement = 0;		// Logging required 0=none, 1=summary, 2=detailed
if (isset($pref['eventpost_emaillog'])) $log_requirement = $pref['eventpost_emaillog'];
if ($debug_level >= 2) $log_requirement = 2;	// Force full logging if debug

if ($log_requirement > 0)
{
  $log_filename = $ec_dir.'log/calendar_mail.txt';
  if (!$run_from_menu)
  {
    if (!($handle = fopen($log_filename, 'a'))) $log_requirement = 0;
    if (fwrite($handle,"\r\n\r\nMail subscriptions run started at ".date("D j M Y G:i:s")) === false)  $log_requirement = 0;
    fclose($handle);
  }
}

// Start with the 'in advance' emails
$cal_args = "select * from #event left join #event_cat on event_category=event_cat_id where (event_cat_subs>0 OR event_cat_force_class != '') and 
event_cat_last < " . intval($cal_starttime) . " and 
event_cat_ahead > 0 and 
event_start >= (" . intval($cal_starttime) . "+(86400*(event_cat_ahead))) and
event_start <  (" . intval($cal_starttime) . "+(86400*(event_cat_ahead+1))) and
find_in_set(event_cat_notify,'1,3,5,7')";

send_mailshot($cal_args, 'Advance',1);



// then for today
//$cal_starttime = mktime(0, 0, 0, date("n"), date("d"), date("Y"));
$cal_args = "select * from #event left join #event_cat on event_category=event_cat_id where (event_cat_subs>0 OR event_cat_force_class != '') and 
event_cat_today < " . intval($cal_starttime) . " and 
event_start >= (" . intval($cal_starttime) . ") and
event_start <  (86400+" . intval($cal_starttime) . ") and
find_in_set(event_cat_notify,'2,3,6,7')";

send_mailshot($cal_args, 'today',2);


// Finally do 'day before' emails
$cal_args = "select * from #event left join #event_cat on event_category=event_cat_id where (event_cat_subs>0 OR event_cat_force_class != '') and 
event_cat_today < " . intval($cal_starttime) . " and 
event_start >= (" . intval($cal_starttime) ." + 86400 ) and
event_start <  (" . intval($cal_starttime) ." + 172800) and
find_in_set(event_cat_notify,'4,5,6,7')";

send_mailshot($cal_args, 'tomorrow',2);


if (($log_requirement > 0) && (!$run_from_menu))
{
  if (!($handle = fopen($log_filename, 'a'))) $log_requirement = 0;
  if (fwrite($handle," .. completed at ".date("D j M Y G:i:s")."\r\n") === false) $log_requirement = 0;
  fclose($handle);
}

// Done


/*
  Function to actually send a mailshot
*/
function send_mailshot($cal_query, $shot_type, $msg_num)
{
  global $sql, $sql2;
  global $log_requirement, $log_filename, $debug_level;
  global $pref;
  global $run_from_menu;
  
  if (($log_requirement > 1)  && (!$run_from_menu))
  {
    if (!$handle = fopen($log_filename, 'a')) $log_requirement = 0;
    if (fwrite($handle,"\r\n  Starting emails for ".$shot_type." at ".date("D j M Y G:i:s")) === false)  $log_requirement = 0;
    if ($debug_level >= 2)
    {
      if (fwrite($handle,"\r\n    Query is: ".$cal_query."\r\n") === false) $log_requirement = 0;
    }
  }

if ($num_cat_proc = $sql->db_Select_gen($cal_query))
  {  // Got at least one event to process here
    if ($log_requirement > 1)
    {
      if ($run_from_menu) if (!($handle = fopen($log_filename, 'a'))) $log_requirement = 0;
      if (fwrite($handle," - ".$num_cat_proc." categories found to process\r\n") === false)  $log_requirement = 0;
    }
    require_once(e_HANDLER . "mail.php");
    while ($cal_row = $sql->db_Fetch())
    {  // Process one event at a time
      extract($cal_row);
		
      if ($log_requirement > 1)
	  {
		  if (fwrite($handle,"    Processing event: ".$event_title." \r\n") === false)  $log_requirement = 0;
	  }
		
	  if ($msg_num == 1)
        $sql2->db_Update("event_cat", "event_cat_last=" . time() . " where event_cat_id=" . intval($event_cat_id));
	  else
        $sql2->db_Update("event_cat", "event_cat_today=" . time() . " where event_cat_id=" . intval($event_cat_id));


// Start of next try on query
// Four cases for the query:
//	1. No forced mailshots - based on event_subs table only									Need INNER JOIN
//	2. Forced mailshot to members - send to all users (don't care about subscriptions)		Don't need JOIN
// 	3. Forced mailshot to group of members - based on user table only						Don't need JOIN
//	4. Forced mailshot to group, plus optional subscriptions - use the lot!    				Need LEFT JOIN
// (Always block sent to banned members)
	  $manual_subs = (isset($pref['eventpost_asubs']) && ($pref['eventpost_asubs'] == '1'));
	  $subs_fields = '';
	  $subs_join = '';
	  $where_clause = '';
	  $group_clause = '';
	  
	  
	  if ($event_cat_force_class != e_UC_MEMBER)
	  {  // Cases 1, 3, 4 (basic query does for case 2)
	  
	    if ((!$event_cat_force_class) || ($manual_subs))
	    {  // Cases 1 & 4 - need to join with event_subs database
	      $subs_fields = ", es.* ";
		  if ($event_cat_force_class) $subs_join = "LEFT"; else $subs_join = "INNER";
	      $subs_join   .= " join #event_subs AS es on u.user_id=es.event_userid ";
		  $where_clause = " es.event_cat='".intval($event_category)."' ";
		  $group_clause = " GROUP BY u.user_id";
	    }

	    if ($event_cat_force_class)
		{  // cases 3 and 4 - ... and check for involuntary subscribers
		    if ($where_clause) $where_clause .= " OR ";
		    if ($event_cat_force_class == e_UC_ADMIN)
		    {
		      $where_clause .= "(u.user_admin = '1' )";
		    }
		    else
		    {
	          $where_clause .= "find_in_set('".intval($event_cat_force_class)."', u.user_class)";
		    }
		}

	    if ($where_clause) $where_clause = ' AND ('.$where_clause.' ) ';
	  }   // End of cases 1, 3, 4
	  
	  $cal_emilargs = "SELECT u.user_id, u.user_class, u.user_email, u.user_name, u.user_ban, u.user_admin{$subs_fields}
		  from #user AS u {$subs_join}
		  WHERE u.user_ban = '0' {$where_clause} {$group_clause}";
		  

        if ($debug_level >= 2)
		{
		  if (fwrite($handle,"\r\n    Email selection query is: ".$cal_emilargs."\r\n") === false) $log_requirement = 0;
		}
        if ($num_shots = $sql2->db_Select_gen($cal_emilargs))
        {
            if ($log_requirement > 1)
			{
			  if (fwrite($handle," - ".$num_shots." emails found to send\r\n") === false)  $log_requirement = 0;
			}
            while ($cal_emrow = $sql2->db_Fetch())
            {
              extract($cal_emrow);
			  if ($msg_num == 1)
                $cal_msg = $event_title . "\n\n" . $event_cat_msg1;
			  else
                $cal_msg = $event_title . "\n\n" . $event_cat_msg2;
              if ($debug_level == 0) $send_result = sendemail($user_email, $pref['eventpost_mailsubject'], $cal_msg, $user_name, $pref['eventpost_mailaddress'], $pref['eventpost_mailfrom']); 
			  if ($log_requirement > 1)
			  {
			    $log_string = "      Send to: ".$user_email." Name: ".$user_name;
				if ($debug_level > 0) 
				  { $log_string .= " *DEBUG*
"; 					} 
				else 
				  { $log_string .= " Result = ".$send_result."
"; 					}
			    if (fwrite($handle,$log_string) === false) $log_requirement = 0;
			  }
            } 
        } 
    } // while    
    if ($log_requirement > 1)
    {
	  if (fwrite($handle,"  Completed emails for ".$shot_type." at ".date("D j M Y G:i:s")."\r\n") === false)  $log_requirement = 0;
	  fclose($handle);
	}
  }
} 



?>