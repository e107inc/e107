<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/stats.php																	|
|																						|
|	©Steve Dunstan 2001-2002										|
|	http://jalist.com															|
|	stevedunstan@jalist.com											|
|																						|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).				|
+---------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);
$month = array("Null", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
$maxwidth = 400;

$dep = new dbFunc;

$dep -> dbQuery("SELECT * FROM ".MUSER."stat_counter ORDER BY counter_date");
$row = $dep -> dbFetch();
$tmp = explode("-", $row['counter_date']);

$logstart = $tmp[2]." ".$month[$tmp[1]]." ".$tmp[0];

$text = "<b>Logging began:</b> ".$logstart."<br />";

$action = $_SERVER['QUERY_STRING'];


$total_page_views = $dep -> dbCount("SELECT sum(counter_unique) FROM ".MUSER."stat_counter");
$row = $dep -> dbFetch();
$text .= "<b>Unique views by page:</b> ".$total_page_views."<br />";

$total_page_views = $dep -> dbCount("SELECT sum(counter_total) FROM ".MUSER."stat_counter");
$row = $dep -> dbFetch();
$text .= "<b>Total site views:</b> ".$total_page_views."<br />";
// ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
$c=0;
$dep -> dbQuery("SELECT counter_url, sum(counter_unique) FROM ".MUSER."stat_counter GROUP BY counter_url");
$text .= "<br /><b>Unique views by page</b>";
if($action == 1){
	$text .= " (all)";
}else{
	$text .= " (top 10)";
}
$text .= "<br />";

while($row= $dep -> dbFetch()){
	$data1[$c][0] = $row[1];
	$data1[$c][1] = substr($row[0], 1);
	$c++;
}
if($c){
	rsort($data1);
}
$w = 1;
while($data1[0][0] / $w > $maxwidth){
	$w++;
}

$c=0;

if($action == 1){
	while($data1[$c][0]){
		$width = $data1[$c][0]/$w;
		$text .= "<img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2.gif\" width=\"".$width."\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /> ".
		$data1[$c][0]." - ".$data1[$c][1]."<br />";
		$c++;
	}
}else{
	for($r=0; $r<=9; $r++){
		$width = $data1[$c][0]/$w;
		$text .= "<img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2.gif\" width=\"".$width."\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /> ".
		$data1[$c][0]." - ".$data1[$c][1]."<br />";
		$c++;
	}
	$text .= "<a href=\"".$_SERVER['PHP_SELF']."?1\">View all</a><br />";
}
// ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
$c=0;
$dep -> dbQuery("SELECT counter_url, sum(counter_total) FROM ".MUSER."stat_counter GROUP BY counter_url");
$text .= "<br /><b>Total views by page</b>";
if($action == 2){
	$text .= " (all)";
}else{
	$text .= " (top 10)";
}
$text .= "<br />";

while($row= $dep -> dbFetch()){

	$data2[$c][0] = $row[1];
	$data2[$c][1] = substr($row[0], 1);
	$c++;
}
if($c){
	rsort($data2);
}
$w = 1;
while($data2[0][0] / $w > $maxwidth){
	$w++;
}

$c=0;
if($action == 2){
	while($data2[$c][0]){
		$width = $data2[$c][0]/$w;
		$text .= "<img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2.gif\" width=\"".$width."\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /> ".
		$data2[$c][0]." - ".$data2[$c][1]."<br />";
		$c++;
	}
}else{
	for($r=0; $r<=9; $r++){
		$width = $data2[$c][0]/$w;
		$text .= "<img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2.gif\" width=\"".$width."\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /> ".
		$data2[$c][0]." - ".$data2[$c][1]."<br />";
		$c++;
	}
	$text .= "<a href=\"".$_SERVER['PHP_SELF']."?2\">View all</a><br />";
}
// ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
$c=0;
$dep -> dbQuery("SELECT info_name, SUM(info_count) FROM ".MUSER."stat_info WHERE info_type='1' GROUP BY info_name");
$text .= "<br /><b>".LAN_128."</b>";
if($action == 3){
	$text .= " (all)";
}else{
	$text .= " (top 10)";
}
$text .= "<br />";
while($row= $dep -> dbFetch()){

	$data3[$c][0] = $row[1];
	$data3[$c][1] = $row[0];
	$c++;
}
if($c){
	rsort($data3);
}
$w = 1;
while($data3[0][0] / $w > $maxwidth){
	$w++;
}

$c=0;
if($action == 3){
	while($data3[$c][0]){
		$width = $data3[$c][0]/$w;
		if($data3[$c][1] == "Internet Explorer"){
			$data3[$c][1] = "IE";
		}
		$text .= "<img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2.gif\" width=\"".$width."\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /> ".
		$data3[$c][0]." - ".$data3[$c][1]."
		<br />";
		$c++;
	}
}else{
	for($r=0; $r<=9; $r++){
		$width = $data3[$c][0]/$w;
		if($data3[$c][1] == "Internet Explorer"){
			$data3[$c][1] = "IE";
		}
		$text .= "<img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2.gif\" width=\"".$width."\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /> ".
		$data3[$c][0]." - ".$data3[$c][1]."
		<br />";
		$c++;
	}
	$text .= "<a href=\"".$_SERVER['PHP_SELF']."?3\">View all</a><br />";
}
// ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
$c=0;
$dep -> dbQuery("SELECT info_name, SUM(info_count) AS Total FROM ".MUSER."stat_info WHERE info_type='2' GROUP BY info_name");
$text .= "<br /><b>".LAN_129."</b>";
if($action == 4){
	$text .= " (all)";
}else{
	$text .= " (top 10)";
}
$text .= "<br />";
while($row= $dep -> dbFetch()){

	$data4[$c][0] = $row[1];
	$data4[$c][1] = $row[0];
	$c++;
}
if($c){
	rsort($data4);
}
$w = 1;
while($data4[0][0] / $w > $maxwidth){
	$w++;
}
$c=0;
if($action == 4){
	while($data4[$c][0]){
		$width = $data4[$c][0]/$w;
		$text .= "<img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2.gif\" width=\"".$width."\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /> ".
		$data4[$c][0]." - ".$data4[$c][1]."
		<br />";
		$c++;
	}
}else{
	for($r=0; $r<=9; $r++){
		$width = $data4[$c][0]/$w;
		$text .= "<img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2.gif\" width=\"".$width."\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /> ".
		$data4[$c][0]." - ".$data4[$c][1]."
		<br />";
		$c++;
	}
	$text .= "<a href=\"".$_SERVER['PHP_SELF']."?4\">View all</a><br />";
}
// ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
$c=0;
$dep -> dbQuery("SELECT info_name, SUM(info_count) AS Total FROM ".MUSER."stat_info WHERE info_type='4' AND info_name!= 'Network' AND info_name!='Commercial Users' AND info_name!='Education' AND info_name!='Government' GROUP BY info_name");
$text .= "<br /><b>".LAN_130."</b>";
if($action == 5){
	$text .= " (all)";
}else{
	$text .= " (top 10)";
}
$text .= "<br />";
while($row= $dep -> dbFetch()){

	$data6[$c][0] = $row[1];
	$data6[$c][1] = $row[0];
	$c++;
}
if($c){
	rsort($data6);
}
$w = 1;
while($data6[0][0] / $w > $maxwidth){
	$w++;
}
$c=0;
if($action == 5){
	while($data6[$c][0]){
		$width = $data6[$c][0]/$w;
		$text .= "<img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2.gif\" width=\"".$width."\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /> ".
		$data6[$c][0]." - ".$data6[$c][1]."
		<br />";
		$c++;
	}
}else{
	for($r=0; $r<=9; $r++){
		$width = $data6[$c][0]/$w;
		$text .= "<img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2.gif\" width=\"".$width."\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /> ".
		$data6[$c][0]." - ".$data6[$c][1]."
		<br />";
		$c++;
	}
	$text .= "<a href=\"".$_SERVER['PHP_SELF']."?5\">View all</a><br />";
}
// ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
$c=0;
$dep -> dbQuery("SELECT info_name, SUM(info_count) AS Total FROM ".MUSER."stat_info WHERE info_type='6' GROUP BY info_name");
$text .= "<br /><b>".LAN_131."</b>";
if($action == 6){
	$text .= " (all)";
}else{
	$text .= " (top 10)";
}
$text .= "<br />";
while($row= $dep -> dbFetch()){
	$data7[$c][0] = $row[1];
	$data7[$c][1] = $row[0];
	$c++;
}
if($c){
	rsort($data7);
}
$w = 1;
while($data7[0][0] / $w > $maxwidth){
	$w++;
}
$c=0;
if($action == 6){
	while($data7[$c][0]){
		$width = $data7[$c][0]/$w;
		$text .= "<img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2.gif\" width=\"".$width."\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /> ".
		$data7[$c][0]." - ".$data7[$c][1]."
		<br />";
		$c++;
	}
}else{
	for($r=0; $r<=9; $r++){
		$width = $data7[$c][0]/$w;
		$text .= "<img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2.gif\" width=\"".$width."\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /> ".
		$data7[$c][0]." - ".$data7[$c][1]."
		<br />";
		$c++;
	}
	$text .= "<a href=\"".$_SERVER['PHP_SELF']."?6\">View all</a><br />";
}
// ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

$c=0;
$dep -> dbQuery("SELECT info_name, SUM(info_count) AS Total FROM ".MUSER."stat_info WHERE info_type='5' GROUP BY info_name");
$text .= "<br /><b>Screen resolutions</b>";
if($action == 7){
	$text .= " (all)";
}else{
	$text .= " (top 10)";
}
$text .= "<br />";
while($row= $dep -> dbFetch()){
	$data8[$c][0] = $row[1];
	$data8[$c][1] = $row[0];
	$c++;
}
if($c){
	rsort($data8);
}
$w = 1;
while($data8[0][0] / $w > $maxwidth){
	$w++;
}
$c=0;
if($action == 7){
	while($data8[$c][0]){
		$width = $data8[$c][0]/$w;
		$text .= "<img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2.gif\" width=\"".$width."\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /> ".
		$data8[$c][0]." - ".$data8[$c][1]."
		<br />";
		$c++;
	}
}else{
	for($r=0; $r<=9; $r++){
		$width = $data8[$c][0]/$w;
		$text .= "<img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2.gif\" width=\"".$width."\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /> ".
		$data8[$c][0]." - ".$data8[$c][1]."
		<br />";
		$c++;
	}
	$text .= "<a href=\"".$_SERVER['PHP_SELF']."?7\">View all</a><br />";
}


$ns -> tablerender("<div style=\"text-align:center\">".LAN_132."</div>", $text);

require_once(FOOTERF);
?>