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
|     $Revision: 1.1 $
|     $Date: 2005-05-01 04:37:02 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
// This menu can be called from a cron job - see readme.rtf
$ec_dir = e_PLUGIN . "calendar_menu/";
$caldb = new DB;
$cal_emaildb = new DB;
// Check if we are going to do the notify

$cal_starttime = mktime(0, 0, 0, date("n"), date("d"), date("Y"));
// Firstly for dates ahead
// Do future events
$cal_args = "select * from " . MPREFIX . "event left join " . MPREFIX . "event_cat on event_category=event_cat_id where event_cat_subs>0 and 
event_cat_last < " . $cal_starttime . " and 
event_cat_ahead > 0 and 
event_start >= (" . $cal_starttime . "+(86400*(event_cat_ahead))) and
event_start <= (" . $cal_starttime . "+(86400*(event_cat_ahead+1))) and
find_in_set(event_cat_notify,'1,3')";
#print $cal_args;
if ($caldb->db_Select_gen($cal_args))
{
    require_once(e_HANDLER . "mail.php");

    while ($cal_row = $caldb->db_Fetch())
    {
        extract($cal_row);
        $cal_emaildb->db_Update("event_cat", "event_cat_last=" . time() . " where event_cat_id=" . $event_cat_id); 

        if ($event_cat_force > 0)
        { 
            // Force email to members of this class

            $cal_emilargs = "select * from " . MPREFIX . "user where user_class regexp '^$event_cat_class,' or user_class regexp ',$event_cat_class,'  or user_class regexp ',$event_cat_class'"; 

            if ($cal_emaildb->db_Select_gen($cal_emilargs)) 
                {
                    while ($cal_emrow = $cal_emaildb->db_Fetch())
                {
                    extract($cal_emrow);
                    $cal_msg = $event_title . "\n\n" . $event_cat_msg1;
                    sendemail($user_email, $pref['eventpost_mailsubject'], $cal_msg, $user_name, $pref['eventpost_mailaddress'], $pref['eventpost_mailfrom']); 

                } 
            } 
        } 
        else
        { 
            // only subscribers
            $cal_emilargs = "select * from " . MPREFIX . "user left join " . MPREFIX . "event_subs on user_id=event_userid where " . MPREFIX . "event_subs.event_cat='$event_category'";
#print $cal_emilargs;
            if ($cal_emaildb->db_Select_gen($cal_emilargs))
            {
                while ($cal_emrow = $cal_emaildb->db_Fetch())
                {
                    extract($cal_emrow);
                    $cal_msg = $event_title . "\n\n" . $event_cat_msg1;
                    sendemail($user_email, $pref['eventpost_mailsubject'], $cal_msg, $user_name, $pref['eventpost_mailaddress'], $pref['eventpost_mailfrom']);
                } 
            } 
        } 
    } // while    
} 
// then for today
// Check if we are going to do the notify
// Firstly for dates ahead
$cal_starttime = mktime(0, 0, 0, date("n"), date("d"), date("Y"));
$cal_args = "select * from " . MPREFIX . "event left join " . MPREFIX . "event_cat on event_category=event_cat_id where event_cat_subs>0 and 
event_cat_today < " . $cal_starttime . " and 
event_start >= (" . $cal_starttime . ") and
event_start <= (86400+" . $cal_starttime . ") and
find_in_set(event_cat_notify,'2,3')";
if ($caldb->db_Select_gen($cal_args))
{
    require_once(e_HANDLER . "mail.php");
    while ($cal_row = $caldb->db_Fetch())
    {
        extract($cal_row);
        $cal_emaildb->db_Update("event_cat", "event_cat_today=" . time() . " where event_cat_id=" . $event_cat_id);
        if ($event_cat_force > 0)
        { 
            // Force email to members of this class

            $cal_emilargs = "select * from " . MPREFIX . "user where user_class regexp '^$event_cat_class,' or user_class regexp ',$event_cat_class,'  or user_class regexp ',$event_cat_class'"; 

            if ($cal_emaildb->db_Select_gen($cal_emilargs))
                {
                    while ($cal_emrow = $cal_emaildb->db_Fetch())
                {
                    extract($cal_emrow);
                    $cal_msg = $event_title . "\n\n" . $event_cat_msg2;
                    sendemail($user_email, $pref['eventpost_mailsubject'], $cal_msg, $user_name, $pref['eventpost_mailaddress'], $pref['eventpost_mailfrom']); 

                } 
            } 
        } 
        else
        { 
            // only subscribers
            $cal_emilargs = "select * from " . MPREFIX . "user left join " . MPREFIX . "event_subs on user_id=event_userid where " . MPREFIX . "event_subs.event_cat='$event_category'";
            if ($cal_emaildb->db_Select_gen($cal_emilargs))
            {
                while ($cal_emrow = $cal_emaildb->db_Fetch())
                {
                    extract($cal_emrow);
                    $cal_msg = $event_title . "\n\n" . $event_cat_msg2;
                    sendemail($user_email, $pref['eventpost_mailsubject'], $cal_msg, $user_name, $pref['eventpost_mailaddress'], $pref['eventpost_mailfrom']); 

                } 
            } 
        } 
    } // while    
} 
#print "done";
?>