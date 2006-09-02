<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dun.an 2001-2002
|     http://e107.org
|     jali.@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/calendar_menu/subs_menu.php,v $
|     $Revision: 1.4 $
|     $Date: 2006-09-02 21:41:18 $
|     $Author: e107coders $
|
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
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

// This menu can be called from a cron job - see readme.rtf
$ec_dir = e_PLUGIN . "calendar_menu/";
$caldb = new DB;
$cal_emaildb = new DB;
// Check if we are going to do the notify

$cal_starttime = mktime(0, 0, 0, date("n"), date("d"), date("Y"));

$debug_level = 0;			// Set to 1 or 2 to suppress actual sending of emails

$log_requirement = 0;		// Logging required 0=none, 1=summary, 2=detailed
if (isset($pref['eventpost_emaillog'])) $log_requirement = $pref['eventpost_emaillog'];
if ($debug_level >= 2) $log_requirement = 2;	// Force full logging if debug

if ($log_requirement > 0)
{
  $log_filename = $ec_dir.'log/calendar_mail.txt';
  if (!$handle = fopen($log_filename, 'a')) $log_requirement = 0;
  if (fwrite($handle,"\r\n\r\nMail subscriptions run started at ".date("D j M Y G:i:s")) === false)  $log_requirement = 0;
  fclose($handle);
}

// Start with the 'in advance' emails
$cal_args = "select * from " . MPREFIX . "event left join " . MPREFIX . "event_cat on event_category=event_cat_id where event_cat_subs>0 and 
event_cat_last < " . $cal_starttime . " and 
event_cat_ahead > 0 and 
event_start >= (" . $cal_starttime . "+(86400*(event_cat_ahead))) and
event_start <= (" . $cal_starttime . "+(86400*(event_cat_ahead+1))) and
find_in_set(event_cat_notify,'1,3,5,7')";

send_mailshot($cal_args, 'Advance',1);



// then for today
//$cal_starttime = mktime(0, 0, 0, date("n"), date("d"), date("Y"));
$cal_args = "select * from " . MPREFIX . "event left join " . MPREFIX . "event_cat on event_category=event_cat_id where event_cat_subs>0 and 
event_cat_today < " . intval($cal_starttime) . " and 
event_start >= (" . intval($cal_starttime) . ") and
event_start <= (86400+" . intval($cal_starttime) . ") and
find_in_set(event_cat_notify,'2,3,6,7')";

send_mailshot($cal_args, 'today',2);


// Finally do 'day before' emails
$cal_args = "select * from " . MPREFIX . "event left join " . MPREFIX . "event_cat on event_category=event_cat_id where event_cat_subs>0 and 
event_cat_today < " . intval($cal_starttime) . " and 
event_start >= (" . intval($cal_starttime) ." + 86400 ) and
event_start <= (" . intval($cal_starttime) ." + 172800) and
find_in_set(event_cat_notify,'4,5,6,7')";

send_mailshot($cal_args, 'tomorrow',2);

if ($log_requirement > 0)
{
  if (!$handle = fopen($log_filename, 'a')) $log_requirement = 0;
  if (fwrite($handle," .. completed at ".date("D j M Y G:i:s")."\r\n") === false) $log_requirement = 0;
  fclose($handle);
}

// Done

function send_mailshot($cal_query, $shot_type, $msg_num)
{
  global $caldb, $cal_emaildb;
  global $log_requirement, $log_filename, $debug_level;
  global $pref;
  
  if ($log_requirement > 1)
  {
    if (!$handle = fopen($log_filename, 'a')) $log_requirement = 0;
    if (fwrite($handle,"\r\n  Starting emails for ".$shot_type." at ".date("D j M Y G:i:s")) === false)  $log_requirement = 0;
    if ($debug_level >= 2)
    {
      if (fwrite($handle,"\r\n    Query is: ".$cal_query."\r\n") === false) $log_requirement = 0;
    }
  }

if ($num_cat_proc = $caldb->db_Select_gen($cal_query))
  {
    if ($log_requirement > 1)
    {
      if (fwrite($handle," - ".$num_cat_proc." categories found to process\r\n") === false)  $log_requirement = 0;
    }
    require_once(e_HANDLER . "mail.php");
    while ($cal_row = $caldb->db_Fetch())
    {
        extract($cal_row);
		
        if ($log_requirement > 1)
		{
		  if (fwrite($handle,"    Processing event: ".$event_title." \r\n") === false)  $log_requirement = 0;
		}
		
		if ($msg_num == 1)
          $cal_emaildb->db_Update("event_cat", "event_cat_last=" . time() . " where event_cat_id=" . intval($event_cat_id));
		else
          $cal_emaildb->db_Update("event_cat", "event_cat_today=" . time() . " where event_cat_id=" . intval($event_cat_id));
        if ($event_cat_force > 0)
        {     // Force email to members of this class
          $cal_emilargs = "select * from " . MPREFIX . "user where user_class regexp '^".intval($event_cat_class).",' or user_class regexp ',".intval($event_cat_class).",'  or user_class regexp ',".intval($event_cat_class)."'"; 
        } 
        else
        {    // only subscribers
          $cal_emilargs = "select * from " . MPREFIX . "user left join " . MPREFIX . "event_subs on user_id=event_userid where " . MPREFIX . "event_subs.event_cat='".intval($event_category)."'";
		}
        if ($cal_emaildb->db_Select_gen($cal_emilargs))
        {
            while ($cal_emrow = $cal_emaildb->db_Fetch())
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
				  "; } 
				else 
				  { $log_string .= " Result = ".$send_result."
				  "; }
			    if (fwrite($handle,$log_string) === false) $log_requirement = 0;
			  }
            } 
        } 
    } // while    
  }
  if ($log_requirement > 1)
  {
  if (fwrite($handle,"  Completed emails for ".$shot_type." at ".date("D j M Y G:i:s")."\r\n") === false)  $log_requirement = 0;
  fclose($handle);
  }
} 



?>