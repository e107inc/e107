<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /event.php
|
|        ©Steve Dunstan 2001-2002
|        http://jalist.com
|        stevedunstan@jalist.com
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../../class2.php");

$ec_dir = e_PLUGIN."calendar_menu/";
$lan_file = $ec_dir."languages/".e_LANGUAGE.".php";
include(file_exists($lan_file) ? $lan_file : e_PLUGIN."calendar_menu/languages/English.php");

$handle=opendir($ec_dir."images/");
while ($file = readdir($handle)){
	if($file != "." && $file != ".." && $file != "templates" && $file != "shared" && $file != "/"){
		$imagelist[] = $file;
	}
}
closedir($handle);

$aj = new textparse();
// enter new category into db ------------------------------------------------------------------------



if(IsSet($_POST['ne_cat_create'])){
        if($_POST['ne_new_category'] != "" && $_POST['ne_new_category_icon'] != ""){
                $sql -> db_Insert("event_cat", " 0, '".$_POST['ne_new_category']."', '".$_POST['ne_new_category_icon']."' ");
                header("location:event.php?".$_POST['qs'].".m1");
        }else{
                header("location:event.php?".$_POST['qs'].".m3");
        }
}
// ----------------------------------------------------------------------------------------------------------
// enter new event into db ----------------------------------------------------------------------------
if(IsSet($_POST['ne_insert']) && USER == TRUE){
        if($_POST['ne_event'] != ""){

                $ev_start = mktime( $_POST['ne_hour'], $_POST['ne_minute'], 0, $_POST['ne_month'], $_POST['ne_day'], $_POST['ne_year']);
                $ev_end = mktime( $_POST['end_hour'], $_POST['end_minute'], 0, $_POST['end_month'], $_POST['end_day'], $_POST['end_year']);

                $ev_title = $aj -> formtpa($_POST['ne_title']);
                $ev_location = $aj -> formtpa($_POST['ne_location']);
                $ev_event = $aj -> formtpa($_POST['ne_event']);

                if($_POST['recurring'] == 1){
                        $rec_m = $_POST['ne_day'];
                        $rec_y = $_POST['ne_month'];
                }else{
                        $rec_m = "";
                        $rec_y = "";
                }

                $sql -> db_Insert("event", " 0, '$ev_start', '$ev_end', '".$_POST['allday']."', '".$_POST['recurring']."', '".time()."', '$ev_title', '$ev_location', '$ev_event', '".USERID.".".USERNAME."', '".$_POST['ne_email']."', '".$_POST['ne_category']."', '".$_POST['ne_thread']."', '$rec_m', '$rec_y' ");

                $qs = eregi_replace("ne.", "", $_POST['qs']);

                header("location:event.php?".$qs.".m4");
        }else{
                header("location:event.php?".$ev_start."..m3");
        }
}
// ----------------------------------------------------------------------------------------------------------
// update event in db ----------------------------------------------------------------------------------

if(IsSet($_POST['ne_update']) && USER == TRUE){
        if($_POST['ne_event'] != ""){

                $ev_start = mktime( $_POST['ne_hour'], $_POST['ne_minute'], 0, $_POST['ne_month'], $_POST['ne_day'], $_POST['ne_year']);
                $ev_end = mktime( $_POST['end_hour'], $_POST['end_minute'], 0, $_POST['end_month'], $_POST['end_day'], $_POST['end_year']);

                $ev_title = $aj -> formtpa($_POST['ne_title']);
                $ev_location = $aj -> formtpa($_POST['ne_location']);
                $ev_event = $aj -> formtpa($_POST['ne_event']);

                if($_POST['recurring'] == 1){
                        $rec_m = $_POST['ne_day'];
                        $rec_y = $_POST['ne_month'];
                }else{
                        $rec_m = "";
                        $rec_y = "";
                }

                $sql -> db_Update("event", "event_start='$ev_start', event_end='$ev_end', event_allday='".$_POST['allday']."', event_recurring='".$_POST['recurring']."', event_datestamp= '".time()."', event_title= '$ev_title', event_location='$ev_location', event_details='$ev_event', event_contact='".$_POST['ne_email']."', event_category='".$_POST['ne_category']."', event_thread='".$_POST['ne_thread']."', event_rec_m='$rec_m', event_rec_y='$rec_y' WHERE event_id='".$_POST['id']."' ");

                $qs = eregi_replace("ed.", "", $_POST['qs']);

                header("location:event.php?".$qs.".m5");
        }else{
                header("location:event.php?".$ev_start."..m3");
        }
}


// ----------------------------------------------------------------------------------------------------------

require_once(HEADERF);

if(IsSet($_POST['jump'])){
        $smarray = getdate(mktime(0,0,0,$_POST['jumpmonth'], 1, $_POST['jumpyear']));
        $month = $smarray['mon'];
        $year = $smarray['year'];
}else{
        $qs = explode(".", $_SERVER['QUERY_STRING']);
        $action = $qs[0];
        $ds = $qs[1];
        if($action == ""){
                $nowarray = getdate();
                $month = $nowarray['mon'];
                $year = $nowarray['year'];
        }else{
                $smarray = getdate($action);
                $month = $smarray['mon'];
                $year = $smarray['year'];
        }
}

if(IsSet($_POST['confirm'])){
        $sql -> db_Delete("event", "event_id='".$_POST['existing']."' ");
        $message = EC_LAN_51; //Event Deleted
}

if($action == "de"){
        $text = "<div style='text-align:center'>
        <b>".EC_LAN_48."</b>
<br /><br />
<form method='post' action='".$_SERVER['PHP_SELF']."'>
<input class='button' type='submit' name='cancel' value='".EC_LAN_49."' />
<input class='button' type='submit' name='confirm' value='".EC_LAN_50."' />
<input type='hidden' name='existing' value='".$qs[1]."'>
</form>
</div>";
$ns -> tablerender(EC_LAN_46, $text); // Confirm Delete Event

        require_once(FOOTERF);
        exit;
}
if(IsSet($_POST['cancel'])){
        $message = EC_LAN_47;  // Delete Cancelled
}


// set up data arrays ----------------------------------------------------------------------------------
$days = array(EC_LAN_18,EC_LAN_12,EC_LAN_13,EC_LAN_14,EC_LAN_15,EC_LAN_16,EC_LAN_17);
$dayslo = array('1st', '2nd', '3rd', '4th', '5th', '6th', '7th', '8th', '9th', '10th', '11th', '12th', '13th', '14th', '15th', '16th', '17th', '18th', '19th', '20th', '21st', '22nd', '23rd', '24th', '25th', '26th', '27th', '28th', '29th', '30th', '31st');
$months = array(EC_LAN_0,EC_LAN_1,EC_LAN_2,EC_LAN_3,EC_LAN_4,EC_LAN_5,EC_LAN_6,EC_LAN_7,EC_LAN_8,EC_LAN_9,EC_LAN_10,EC_LAN_11);
// ----------------------------------------------------------------------------------------------------------

if($qs[2] == "m1"){
        $message = EC_LAN_41; //"New category created.";
}else if($qs[2] == "m2"){
        $message = EC_LAN_42; //"Event cannot end before it starts.";
}else if($qs[2] == "m3"){
        $message = EC_LAN_43; //"You left required field(s) blank.";
}else if($qs[2] == "m4"){
        $message = EC_LAN_44; //"New event created and entered into database.";
}elseif($qs[2] == "m5"){
        $message = EC_LAN_45; // "Event updated in database.";
}

if(IsSet($message)){
        $ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

// enter new event form---------------------------------------------------------------------------------
if($action == "ne" || $action == "ed"){

        if($action == "ed"){
                $sql -> db_Select("event", "*", "event_id='".$qs[1]."' ");
                list($null, $ne_start, $ne_end, $allday, $recurring, $ne_datestamp, $ne_title, $ne_location, $ne_event, $ne_author, $ne_email, $ne_category, $ne_thread) = $sql-> db_Fetch();

                $smarray = getdate($ne_start);
                $ne_day = $smarray['mday'];
                $ne_month = $smarray['mon'];
                $ne_year = $smarray['year'];

                $ne_hour = $smarray['hours'];
                $ne_minute = $smarray['minutes'];

                $smarray = getdate($ne_end);
                $end_day = $smarray['mday'];
                $end_month = $smarray['mon'];
                $end_year = $smarray['year'];

                $end_hour = $smarray['hours'];
                $end_minute = $smarray['minutes'];

        }else{

                $smarray = getdate($qs[1]);
                $month = $smarray['mon'];
                $year = $smarray['year'];

                $ne_day = $smarray['mday'];
                $ne_month = $smarray['mon'];
                $ne_year = $smarray['year'];

                $ne_hour = $smarray['hours'];
                $ne_minute = $smarray['minutes'];
                $end_day = $smarray['mday'];
                $end_month = $smarray['mon'];
                $end_year = $smarray['year'];

                $end_hour = $smarray['hours'];
                $end_minute = $smarray['minutes'];
        }

        $text = "<form method='post' action='".$_SERVER['PHP_SELF']."'>
        <table style='width:100%' class='fborder'>

        <tr>
        <td colspan='2' class='fcaption'>";

        if($action == "ed"){
                $text .= EC_LAN_66;  // edit Event
        }else{
                $text .= EC_LAN_28; // Enter New Event
        }

        $text .= "</td>
        </tr>
        <tr>

        <td class='forumheader3' style='width:20%'>".EC_LAN_72." </td>
        <td class='forumheader3' style='width:80%'>
    <div class='forumheader3'>
        ".EC_LAN_67." <select name='ne_day' class='tbox'>";
        for($count=1; $count<=31; $count++){
                if($count == $ne_day){
                        $text .= "<option selected>".$count."</option>";
                }else{
                        $text .= "<option>".$count."</option>";
                }
        }
        $text .= "</select>
        <select name='ne_month' class='tbox'>";
        for($count=1; $count<=12; $count++){
                if($count == $ne_month){
                        $text .= "<option value='$count' selected>".$months[($count-1)]."</option>";
                }else{
                        $text .= "<option value='$count'>".$months[($count-1)]."</option>";
                }
        }
        $text .= "</select>
        <select name='ne_year' class='tbox'>";
        for($count=2002; $count<=2022; $count++){
                if($count == $ne_year){
                        $text .= "<option selected>".$count."</option>";
                }else{
                        $text .= "<option>".$count."</option>";
                }
        }
        $text .= "</select></div>&nbsp;&nbsp;".EC_LAN_73."
        <select name='end_day' class='tbox'>";
        for($count=1; $count<=31; $count++){
                if($count == $end_day){
                        $text .= "<option selected>".$count."</option>";
                }else{
                        $text .= "<option>".$count."</option>";
                }
        }
        $text .= "</select>
        <select name='end_month' class='tbox'>";
        for($count=1; $count<=12; $count++){
                if($count == $end_month){
                        $text .= "<option value='$count' selected>".$months[($count-1)]."</option>";
                }else{
                        $text .= "<option value='$count'>".$months[($count-1)]."</option>";
                }
        }
        $text .= "</select>
        <select name='end_year' class='tbox'>";
        for($count=2002; $count<=2022; $count++){
                if($count == $end_year){
                        $text .= "<option selected>".$count."</option>";
                }else{
                        $text .= "<option>".$count."</option>";
                }
        }
        $text .= "</select>



        </td>
        </tr>

        <tr>
        <td class='forumheader3' style='width:20%'>".EC_LAN_71 ." </td>
        <td class='forumheader3' style='width:80%'>

        ".EC_LAN_67." <select name='ne_hour' class='tbox'>";
        for($count="00"; $count<="23"; $count++){
                if($count == $ne_hour){
                        $text .= "<option selected>".$count."</option>";
                }else{
                        $text .= "<option>".$count."</option>";
                }
        }
        $text .= "</select>
        <select name='ne_minute' class='tbox'>";
        for($count="00"; $count<="59"; $count++){
                if($count == $ne_minute){
                        $text .= "<option selected>".$count."</option>";
                }else{
                        $text .= "<option>".$count."</option>";
                }
        }
        $text .= "</select>

        &nbsp;&nbsp;".EC_LAN_73." <select name='end_hour' class='tbox'>";
        for($count="00"; $count<="23"; $count++){
                if($count == $end_hour){
                        $text .= "<option selected>".$count."</option>";
                }else{
                        $text .= "<option>".$count."</option>";
                }
        }
        $text .= "</select>
        <select name='end_minute' class='tbox'>";
        for($count="00"; $count<="59"; $count++){
                if($count == $end_minute){
                        $text .= "<option selected>".$count."</option>";
                }else{
                        $text .= "<option>".$count."</option>";
                }
        }
        $text .= "</select>";

        if($allday == 1){
                $text .= "<br><input type='checkbox' name='allday' value='1'  checked>";
        }else{
                $text .= "<br><input type='checkbox' name='allday' value='1'>";
        }

        $text .= EC_LAN_64."

        </td>
        </tr>

        <tr>
        <td class='forumheader3' style='width:20%'>".EC_LAN_65 ."</td>
        <td class='forumheader3' style='width:80%'>";

        if($recurring == 1){
                $text .= "<input type='checkbox' name='recurring' value='1'  checked>";
        }else{
                $text .= "<input type='checkbox' name='recurring' value='1'>";
        }

        $text .= EC_LAN_63 . "

        </td>
        </tr>

        <tr>
        <td class='forumheader3' style='width:20%'>".EC_LAN_70 ."</td>
        <td class='forumheader3' style='width:80%'>
        <input class='tbox' type='text' name='ne_title' size='75' value='$ne_title' maxlength='200' />
        </td>
        </tr>


        <tr>
        <td class='forumheader3' style='width:20%'>".EC_LAN_52." </td>
        <td class='forumheader3' style='width:80%'>
        <select name='ne_category' class='tbox'>";
        $sql -> db_Select("event_cat");

        while($row = $sql -> db_Fetch()){
                extract($row);
                if($ne_category == $event_cat_id){
                        $text .= "<option value='$event_cat_id' selected>".$event_cat_name."</option>";
                }else{
                        $text .= "<option value='$event_cat_id'>".$event_cat_name."</option>";
                }
        }

        $text .= "</select>
        </td>
        </tr>";

        if(ADMIN == TRUE && $action != "ed"){
                $text .= "<tr>
        <td class='forumheader3' style='width:20%'>".EC_LAN_53." </td>
        <td class='forumheader3' style='width:80%'>".EC_LAN_54." <input class='tbox' type='text' name='ne_new_category' size='30' value='$ne_new_category' maxlength='100' /> ".EC_LAN_55." 
		<select class='tbox' name='ne_new_category_icon'>";
		$c=0;
		while($imagelist[$c]){
			$text .= "<option>".$imagelist[$c]."</option>";
			$c++;
		}
		
		
        $text .= "</select> <input class='button' type='submit' name='ne_cat_create' value='".EC_LAN_56."' />";
        }

        $text .= "</td>
        </tr>

        <tr>
        <td class='forumheader3' style='width:20%'>".EC_LAN_32." </td>
        <td class='forumheader3' style='width:80%'>
        <input class='tbox' type='text' name='ne_location' size='60' value='$ne_location' maxlength='200' />

        </td>
        </tr>

        <tr>
        <td class='forumheader3' style='width:20%'>".EC_LAN_57." </td>
        <td class='forumheader3' style='width:80%'>
        <textarea class='tbox' name='ne_event' cols='59' rows='8'>$ne_event</textarea>

        </td>
        </tr>

        <tr>
        <td class='forumheader3' style='width:20%'>".EC_LAN_58." </td>
        <td class='forumheader3' style='width:80%'>
        <input class='tbox' type='text' name='ne_thread' size='60' value='$ne_thread' maxlength='100' />

        </td>
        </tr>

        <tr>
        <td class='forumheader3' style='width:20%'>".EC_LAN_59." </td>
        <td class='forumheader3' style='width:80%'>
        <input class='tbox' type='text' name='ne_email' size='60' value='$ne_email' maxlength='150' />

        </td></tr>

        <tr>
        <td class='forumheader' colspan='2' style='text-align:center'>";
        if($action == "ed"){
                $text .= "<input class='button' type='submit' name='ne_update' value='".EC_LAN_60."' />
                                                <input type='hidden' name='id' value='".$qs[1]."'>";
        }else{




   $text .= "<input class='button' type='submit' name='ne_insert' value='".EC_LAN_28."' />";


        }

        $text .= "</td>
        </tr>
        </table>
        <input type='hidden' name='qs' value='".$_SERVER['QUERY_STRING']."'>
        <form>";
        echo $text;
        require_once(FOOTERF);
        exit;
}


// ----------------------------------------------------------------------------------------------------------

// show events-------------------------------------------------------------------------------------------
// get first and last days of month in unix format---------------------------------------------------
$monthstart= mktime(0,0,0,$month,1,$year);
$firstdayarray = getdate($monthstart);
$monthend = mktime(0,0,0,$month+1,0,$year);
$lastdayarray = getdate($monthend);
// ----------------------------------------------------------------------------------------------------------


// echo current month with links to previous/next months ----------------------------------------

$prevmonth = ($month-1);
$prevyear = $year;
if($prevmonth == 0){
        $prevmonth = 12;
        $prevyear = ($year-1);
}
$previous = mktime(0,0,0,$prevmonth,1, $prevyear);

$nextmonth = ($month+1);
$nextyear = $year;
if($nextmonth == 13){
        $nextmonth = 1;
        $nextyear = ($year+1);
}
$next = mktime(0,0,0,$nextmonth,1, $nextyear);

echo "<table style='width:100%' class='fborder'>
<tr>
<td class='forumheader' style='width:15%; text-align:left'><span class='defaulttext'><a href='event.php?".$previous."'><< ".$months[($prevmonth-1)]."</a></span></td>
<td class='fcaption' style='width:70%; text-align:center' class='mediumtext'><b>".$months[($month-1)]." ".$year."</b></td>
<td class='forumheader' style='width:15%; text-align:right'><span class='defaulttext'><a href='event.php?".$next."'> ".$months[($nextmonth-1)]." >></a></span> </td>
</tr>
</table>";

echo "<div style='text-align:center'>
<form method='post' action='".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."'>
".EC_LAN_34."
<select name='jumpmonth' class='tbox'>";
$count = 0;
while($months[$count]){
        echo "<option value='".($count+1)."'>".$months[$count]."</option>";
        $count++;
}
echo "</select>
<select name='jumpyear' class='tbox'>";
for($count=2002; $count<=2022; $count++){
        echo "<option>".$count."</option>";
}
echo "</select>
<input class='button' type='submit' name='jump' value='".EC_LAN_61."' />
</form>";

$todayarray = getdate();
$current_month = $todayarray['mon'];
$current_year = $todayarray['year'];
$current = mktime(0,0,0,$current_month, 1, $current_year);

$prop = mktime(0,0,0,$month, 1, $year);


         ##### Check for access.

  if($pref['eventpost_admin'][1] == 1 ){    // start admin preference activated.
 if(ADMIN == TRUE){ echo "<br /><div class='button' style='width:130px; height:15px'>- <a href='event.php?ne.".$prop."'>".EC_LAN_28."</a> -</div>";   }
   }     // end admin preference activated.



     if(!$pref['eventpost_admin'][1] == 1 ){  // start no admin preference
   if(USER == TRUE){ echo "<br /><span class='button' style='width:130px; height:15px'>- <a href='event.php?ne.".$prop."'>".EC_LAN_28."</a> -</span>";}
     }    // end no admin preference

if($month != $current_month || $year != $current_year){
        echo " <span class='button' style='width:130px; height:15px'>- <a href='event.php?$current'>".EC_LAN_40."</a> -</span>";
}

echo "</div><br />";


// get events from current month----------------------------------------------------------------------
$sql -> db_Select("event", "*", "(event_start>='$monthstart' AND event_start<= '$monthend') OR (event_rec_y='$month') ORDER BY event_start ASC");
$events = $sql -> db_Rows();
//echo "There are $events event(s) this month<br />";
$sql2 = new db;
echo "<table style='width:100%' class='fborder'>";
while($row = $sql -> db_Fetch()){
        extract($row);
//        echo $event_id.". ".$event_details;
        $evf = getdate($event_start);
        $tmp = $evf['mday'];
        $event_true[$tmp] = $event_category;

        $sql2 -> db_Select("event_cat", "*", "event_cat_id='".$event_category."' ");
        $icon = $sql2 -> db_Fetch();
        extract($icon);
        if($event_allday == 0){
                if($event_start > $event_end){
                        $event_end = $event_start;
                }
        }

        $startds =  ereg_replace(" 0", " ", date("l d F Y - H:i:s", $event_start));
        $endds =  ereg_replace(" 0", " ", date("l d F Y - H:i:s", $event_end));

        if($event_recurring == 1){
                $tmp = getdate($event_start);
                $tmpyear = $tmp['year'];
                $startds =  str_replace("$tmpyear", $year, $startds);
                $endds =  str_replace("$tmpyear", $year, $endds);
        }

        $lp = explode(".", $event_author);
        if(ereg("[0-9]+", $lp[0])){
                $event_author_id = $lp[0];
                $event_author_name = $lp[1];
        }

  ################## Changed by Cameron

  if ($event_cat_icon){
echo "<tr>
<td colspan='2' class='fcaption'><img style='border:0' src='".$ec_dir."images/".$event_cat_icon."' alt='' /> ".$event_title."</td>";

 }

 else {
echo "<tr>
<td colspan='2' class='fcaption'>".$event_title."</td>";
       }
 #############   end Change by Cameron.


echo  "</tr>
<tr>";
if($event_allday){
        echo "<td colspan='2' class='forumheader'><b>".EC_LAN_68."</b>: $startds</td>";
}else if($startds == $endds){
        echo "<td colspan='2' class='forumheader'><b>".EC_LAN_29."</b>: ".$startds."</td>";
}else{
        echo "<td style='width:50%' class='forumheader'><b>".EC_LAN_29."</b>: ".$startds."</td>
        <td style='width:50%' class='forumheader'><b>".EC_LAN_69."</b>: ".$endds."</td>";
}
echo "</tr>
<tr>
<td colspan='2' class='forumheader3'>".
$event_details."
</td>
</tr>

<tr>";
     ########## Changed by Cameron 2


if ($event_cat_icon){

echo "
<td style='width:50%' class='forumheader3'><b>".EC_LAN_30."</b> <img style='border:0' src='".$ec_dir."images/".$event_cat_icon."' alt='' width='12' height='12' /> ".$event_cat_name."</td>";
  }

  else {
 echo "
  <td style='width:50%' class='forumheader3'><b>".EC_LAN_30."</b> ".$event_cat_name."</td>";


  }

echo "<td style='width:50%' class='forumheader3'><b>".EC_LAN_32."</b> ";
if($event_location == ""){
        echo EC_LAN_38;

    # End of Changed by Cameron 2.

}else{
        echo $event_location."</td>";
}
echo "</tr>
<tr>
<td style='width:33%' class='forumheader3'><b>".EC_LAN_31."</b> <a href='".e_BASE."user.php?id.".$event_author_id."'>".$event_author_name."</a></td>
<td style='width:33%' class='forumheader3'><b>".EC_LAN_33."</b> ";
if($event_contact == ""){
        echo EC_LAN_38; // Not Specified ;
}else{
        echo "<a href='mailto:".$event_contact."'>".$event_contact."</a></td>";
}



echo "</tr>
<tr>
<td style='width:50%' class='forumheader'>".

($event_thread ? "<span class='smalltext'><a href='$event_thread'><img src='".e_IMAGE."forum/e.png' alt='' style='border:0' width='16' height='16' align='absmiddle'> ".EC_LAN_39."</a></span>" : "&nbsp;")."

</td>
<td style='width:50%' class='forumheader' style='text-align:right'>";

if(USERNAME == $event_author_name || (ADMIN == TRUE && ADMINPERMS <= 2)){
        echo "<span class='smalltext'>
        [ <a href='event.php?ed.".$event_id."'>".EC_LAN_35."</a> ] [ <a href='event.php?de.".$event_id."'>".EC_LAN_36."</a> ]
        </span>";
}

echo "</td>
        </tr>";

}
echo "</table>";
// -----------------------------------------------------------------------------------------------------------


$nextmonth = mktime(0,0,0,$month+1,1,$year);
$sql -> db_Select("event", "*", "event_start>='$nextmonth' ORDER BY event_start ASC LIMIT 0,10");
$num = $sql -> db_Rows();
echo "<br /><table style='width:100%' class='fborder'>
<tr>
<td colspan='2' class='forumheader'><span class='defaulttext'>".EC_LAN_62."</span></td>";
if($num != 0){
        while($events = $sql -> db_Fetch()){
                extract($events);
                $startds =  ereg_replace(" 0", " ", date("l d F Y @ H:i:s", $event_start));
                echo "<tr><td style='width:35%; vertical-align:top' class='forumheader3'><a href='event.php?".$event_start."'>".$startds."</a></td>
                <td style='width:65%' class='forumheader3'>".$event_details."</td></tr>";

        }
}else{
        echo "<tr><td colspan='2' class='forumheader3'>".EC_LAN_37."</td></tr>";
}

echo "</table>";


require_once(FOOTERF);
?>