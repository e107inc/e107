<?php
$date = date("Y-m-d");
$sql -> db_Select("stat_counter", "*", "counter_date='$date' AND counter_url='".$_SERVER['PHP_SELF']."' ");
$row = $sql -> db_Fetch();
$text = "Today: ".$row['counter_total']." (unique: ".$row['counter_unique'].")";
$dep = new dbfunc;
$total_page_views = $dep -> dbCount("SELECT sum(counter_total) FROM ".MUSER."stat_counter WHERE counter_url='".$_SERVER['PHP_SELF']."' ");
$total_unique_views = $dep -> dbCount("SELECT sum(counter_unique) FROM ".MUSER."stat_counter WHERE counter_url='".$_SERVER['PHP_SELF']."' ");
$text .= "<br />Total ever: $total_page_views (unique: $total_unique_views)";
$sql -> db_Select("stat_counter", "*", "counter_url='".$_SERVER['PHP_SELF']."' ORDER BY counter_total DESC");
$row = $sql -> db_Fetch();
$text .= "<br />Record: ".$row['counter_total']." (unique: ".$row['counter_unique'].")";
$ns -> tablerender("Counter", $text);
?>