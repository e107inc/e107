<?php
if($pref['log_activate']){
$text = "";
$date = date("Y-m-d");
$self = substr(strrchr($_SERVER['PHP_SELF'], "/"), 1);
$sql -> db_Select("stat_counter", "*", "counter_date='$date' AND counter_url='$self' ");
$row = $sql -> db_Fetch();
$text = COUNTER_L2.": ".($row['counter_total'] ? $row['counter_total'] : "0")." (unique:".($row['counter_unique'] ? $row['counter_unique'] : "0").")";
$temp = mysql_query("SELECT sum(counter_total) FROM ".MPREFIX."stat_counter WHERE counter_url='$self' ");
$temp = mysql_fetch_array($temp);
$total_page_views = $temp[0];
$temp = mysql_query("SELECT sum(counter_unique) FROM ".MPREFIX."stat_counter WHERE counter_url='$self' ");
$temp = mysql_fetch_array($temp);
$total_unique_views = $temp[0];
$text .= "<br />".COUNTER_L3.": ".($total_page_views ? $total_page_views : "0")." (unique:".($total_unique_views ? $total_unique_views : "0").")";
$sql -> db_Select("stat_counter", "*", "counter_url='$self' ORDER BY counter_total DESC");
$row = $sql -> db_Fetch();
$text .= "<br />".COUNTER_L4.": ".($row['counter_total'] ? $row['counter_total'] : "0")." (unique:".($row['counter_unique'] ? $row['counter_unique'] : "0").")";

$ns -> tablerender(COUNTER_L1, $text);
}

if(!$pref['log_activate'] && ADMIN){
	$text .= "<br /><br /><span class='smalltext'>".COUNTER_L5."</span><br />
	<a href='".e_ADMIN."log.php'>".COUNTER_L6."</a>";
	
	$ns -> tablerender(COUNTER_L1, $text);
}

?>