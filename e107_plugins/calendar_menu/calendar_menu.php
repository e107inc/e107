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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/calendar_menu/calendar_menu.php,v $
|     $Revision: 1.2 $
|     $Date: 2004-12-11 17:06:25 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

$ec_dir = e_PLUGIN."calendar_menu/";
//$lan_file = $ec_dir."languages/".e_LANGUAGE.".php";
//include(file_exists($lan_file) ? $lan_file : e_PLUGIN."calendar_menu/languages/English.php");

$datearray = getdate();
$current_day = $datearray['mday'];
$current_month = $datearray['mon'];
$current_year = $datearray['year'];

// get first and last days of month in unix format---------------------------------------------------
$monthstart= mktime(0,0,0,$current_month,1,$current_year);
$firstdayarray = getdate($monthstart);
$monthend = mktime(0,0,0,$current_month+1,0,$current_year);
$lastdayarray = getdate($monthend);
// ----------------------------------------------------------------------------------------------------------

// get events from current month----------------------------------------------------------------------

$sql -> db_Select("event", "*", "(event_start>='$monthstart' AND event_start<= '$monthend') OR (event_rec_y='$current_month')");
$events = $sql -> db_Rows();
$event_true=array();
while($row = $sql -> db_Fetch()){
        extract($row);
        $evf = getdate($event_start);
        $tmp = $evf['mday'];
        $event_true[$tmp] = $event_category;
}
// -----------------------------------------------------------------------------------------------------------

// set up arrays for calender display ------------------------------------------------------------------
$week = Array(EC_LAN_25,EC_LAN_19,EC_LAN_20,EC_LAN_21,EC_LAN_22,EC_LAN_23,EC_LAN_24);
$months = Array(EC_LAN_0,EC_LAN_1,EC_LAN_2,EC_LAN_3,EC_LAN_4,EC_LAN_5,EC_LAN_6,EC_LAN_7,EC_LAN_8,EC_LAN_9,EC_LAN_10,EC_LAN_11);
$calendar_title = "<a class='forumlink' href='".$ec_dir."calendar.php'>".$months[$datearray[mon]-1]." ".$current_year."</a>";
// -----------------------------------------------------------------------------------------------------------

$text = "<div style='text-align:center'>";
if($events){
        $text .= EC_LAN_26 . ": ".$events;
}else{
        $text .= EC_LAN_27;
}

$start = $monthstart;

$text .= "<br /><br />
<table cellpadding='0' cellspacing='1' style='width:95%' class='fborder'><tr>";

foreach($week as $day){
        $text .= "<td class='forumheader' style='text-align:center'><span class='smalltext'>".$day."</span></td>";
}
$text .= "</tr><tr >";

$thismonth = $datearray['mon'];
$thisday = $datearray['mday'];

for($c=0; $c<$firstdayarray['wday']; $c++){
                $text .= "<td class='forumheader3' style='text-align:center'><br /></td>";
}
$loop = $firstdayarray['wday'];
for($c=1; $c<=31; $c++){

        $dayarray = getdate($start+(($c-1)*86400));

        if($dayarray['mon'] == $thismonth){
                if($thisday == $c){
                        $text .=  "<td class='indent' style='text-align:center'>";
                }else{
                        $text .="<td class='forumheader3' style='text-align:center'>";
                }

                if(array_key_exists($c,$event_true) && $event_true[($c)]){
                        $sql -> db_Select("event_cat", "*", "event_cat_id='".$event_true[($c)]."' ");
                        $icon = $sql -> db_Fetch();
                        extract($icon);
                        $img = "<img style='border:0' src='".$ec_dir."images/".$event_cat_icon."' alt='' height='10' width='10'/>";
                }else{
                                        $img = $c;
                                }

                $linkut = mktime(0 ,0 ,0 ,$dayarray['mon'], $c, $datearray['year']);

                $text .="<a href='".$ec_dir."event.php?".$linkut.".one'>$img</a>";

                if($thisday == $c){
                }

                $text .= "</td>\n";

                $loop++;
                if($loop == 7){
                        $loop = 0;
                        $text .= "</tr><tr>";
                }
        }
}

for($a=($loop+1); $a<=7; $a++){
        $text .="<td>&nbsp;</td>";
}

$text .= "</tr></table></div>";
$ns -> tablerender($calendar_title, $text);
?>