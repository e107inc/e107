<?php
$date = date("Y-m-d");
$sql -> db_Select("stat_counter", "*", "counter_date='$date' AND counter_url='".$_SERVER['PHP_SELF']."' ");
$row = $sql -> db_Fetch();
$text = "Today: ".($row['counter_total'] ? $row['counter_total'] : "0")." (unique:".($row['counter_unique'] ? $row['counter_unique'] : "0").")";
$temp = mysql_query("SELECT sum(counter_total) FROM ".MUSER."stat_counter WHERE counter_url='".$_SERVER['PHP_SELF']."' ");
$temp = mysql_fetch_array($temp);
$total_page_views = $temp[0];
$temp = mysql_query("SELECT sum(counter_unique) FROM ".MUSER."stat_counter WHERE counter_url='".$_SERVER['PHP_SELF']."' ");
$temp = mysql_fetch_array($temp);
$total_unique_views = $temp[0];
$text .= "<br />Ever: ".($total_page_views ? $total_page_views : "0")." (unique:".($total_unique_views ? $total_unique_views : "0").")";
$sql -> db_Select("stat_counter", "*", "counter_url='".$_SERVER['PHP_SELF']."' ORDER BY counter_total DESC");
$row = $sql -> db_Fetch();
$text .= "<br />Record: ".($row['counter_total'] ? $row['counter_total'] : "0")." (unique:".($row['counter_unique'] ? $row['counter_unique'] : "0").")";
$ns -> tablerender("Counter", $text);
?>