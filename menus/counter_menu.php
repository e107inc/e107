<?php
// Counter function by que
function get_count($url){
	$cnt = new db;
	$date = date("Y-m-d");
	$cnt -> db_Select("stat_counter", "*", "counter_date='$date' AND counter_url='$url' ");
	$row = $cnt -> db_Fetch();
	$text = "Page Hits today: ".$row['counter_total']."<br />(unique: ".$row['counter_unique'].")<br />";
 
	$cnt -> db_Select("stat_counter", "*", "counter_url='$url' ");
	while($row = $cnt -> db_Fetch()){
		$unique_ever += $row[2];
		$total_ever += $row[3];
	}
	$text .= "Page Hits ever: $total_ever<br />(unique: $unique_ever)<br />";
	$ns = new table;         
	$ns -> tablerender("Counter", $text);
}
get_count($_SERVER['PHP_SELF']);
?>