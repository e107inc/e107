<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/stats.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);



if(!$pref['log_activate'][1]){
	if(ADMIN){
		$text = "<div style=\"text-align:center\">Logging is not activated, to activate go to your admin section, click on Logger and tick the Activate Logging/Counter checkbox.</div>";
	}else{
		$text = "<div style=\"text-align:center\">The features on this page have been disabled.</div>";
	}
	$ns -> tablerender("Stats", $text);
	require_once(FOOTERF);
	exit;
}


$maxwidth = 400;

$dep = new dbFunc;

$dep -> dbQuery("SELECT * FROM ".MPREFIX."stat_counter ORDER BY counter_date");
$row = $dep -> dbFetch();
$tmp = explode("-", $row['counter_date']);
$tmp2 = getdate(mktime(0, 0, 0, $tmp[1], $tmp[2], $tmp[0]));

$logstart = $tmp[2]." ".$tmp2['month']." ".$tmp[0];
$text = "<b>Logging began:</b> ".$logstart."<br />";
$action = e_QUERY;

$total_page_views = $dep -> dbCount("SELECT sum(counter_unique) FROM ".MPREFIX."stat_counter");
$row = $dep -> dbFetch();
$text .= "<b>Unique views by page:</b> ".$total_page_views."<br />";

$total_page_views = $dep -> dbCount("SELECT sum(counter_total) FROM ".MPREFIX."stat_counter");
$row = $dep -> dbFetch();
$text .= "<b>Total site views:</b> ".$total_page_views."<br />";
// ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
$c=0;
$dep -> dbQuery("SELECT counter_url, sum(counter_unique) FROM ".MPREFIX."stat_counter GROUP BY counter_url");
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
	$text .= "<a href=\"".e_SELF."?1\">View all</a><br />";
}
// ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
$c=0;
$dep -> dbQuery("SELECT counter_url, sum(counter_total) FROM ".MPREFIX."stat_counter GROUP BY counter_url");
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
	$text .= "<a href=\"".e_SELF."?2\">View all</a><br />";
}
// ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
// last 10 visitors

$con = new convert;
if($action == 8 && ADMIN == TRUE){
	$text .= "<br /><b>Last ".$pref['log_lvcount'][1]." unique visitors (all)</b><br />";
	$sql -> db_Select("stat_last", "*", "ORDER BY stat_last_date DESC", "no_where");
}else{
	$text .= "<br /><b>Last 10 unique visitors</b><br />";
	$sql -> db_Select("stat_last", "*", "ORDER BY stat_last_date DESC LIMIT 0,10", "no_where");
}
	
while(list($stat_last_date, $stat_last_info) = $sql-> db_Fetch()){
	$datestamp = $con -> convert_date($stat_last_date, "long");
	$text .= "<span class=\"smalltext\">".$datestamp.":</span><br /> ".$stat_last_info."<br /><br />";
}

if(ADMIN == TRUE && $pref['log_lvcount'][1] >10){
	$text .= "<a href=\"".e_SELF."?8\">View all</a><br />";
}

// ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
$c=0;
$dep -> dbQuery("SELECT info_name, SUM(info_count) FROM ".MPREFIX."stat_info WHERE info_type='1' GROUP BY info_name");
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
	$text .= "<a href=\"".e_SELF."?3\">View all</a><br />";
}
// ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
$c=0;
$dep -> dbQuery("SELECT info_name, SUM(info_count) AS Total FROM ".MPREFIX."stat_info WHERE info_type='2' GROUP BY info_name");
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
	$text .= "<a href=\"".e_SELF."?4\">View all</a><br />";
}
// ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
$c=0;
$dep -> dbQuery("SELECT info_name, SUM(info_count) AS Total FROM ".MPREFIX."stat_info WHERE info_type='4' AND info_name!= 'Network' AND info_name!='Commercial Users' AND info_name!='Education' AND info_name!='Government' GROUP BY info_name");
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
	$text .= "<a href=\"".e_SELF."?5\">View all</a><br />";
}
// ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
$c=0;
$dep -> dbQuery("SELECT info_name, SUM(info_count) AS Total FROM ".MPREFIX."stat_info WHERE info_type='6' GROUP BY info_name");
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
	$text .= "<a href=\"".e_SELF."?6\">View all</a><br />";
}
// ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

$c=0;
$dep -> dbQuery("SELECT info_name, SUM(info_count) AS Total FROM ".MPREFIX."stat_info WHERE info_type='5' GROUP BY info_name");
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
	$text .= "<a href=\"".e_SELF."?7\">View all</a><br />";
}


$ns -> tablerender("<div style=\"text-align:center\">".LAN_132."</div>", $text);

require_once(FOOTERF);

class dbfunc{

	// NOTE: This class is now depracated, kept in for third-party plugin compatibilty.

	var $mySQLserver;
	var $mySQLuser;
	var $mySQLpassword;
	var $mySQLdefaultdb;
	var $mySQLaccess;
	var $mySQLresult;
	var $mySQLrows;
	var $mySQLerror;

	function dbRows(){
		$rows = $this->mySQLrows = @mysql_num_rows($this->mySQLresult);
		return $rows;
		$this->dbError("dbRows");
	}

	function dbCount($query){
		if($this->mySQLresult = @mysql_query($query)){
			$rows = $this->mySQLrows = @mysql_fetch_array($this->mySQLresult);
			return $rows[0];
		}else{
			$this->dbError("dbCount ($query)");
		}
	}

	function dbQuery($query){
		if($this->mySQLresult = @mysql_query($query)){
			$this->dbError("dbQuery");
			return $this->dbRows();
		}else{
			$this->dbError("dbQuery ($query)");
			return FALSE;
		}
	}

	function dbFetch(){
		if($row = @mysql_fetch_array($this->mySQLresult)){
			$this->dbError("dbFetch");
			return $row;
		}else{
			$this->dbError("dbFetch");
			return FALSE;
		}
	}
	function dbError($from){
		if($error_message = @mysql_error()){
			if($this->mySQLerror == TRUE){
				echo "<b>mySQL Error!</b> Function: $from. [".@mysql_errno()." - $error_message]<br />";
				return $error_message;
			}
		}
	}
	function dbInsert($query){
		return $this->mySQLresult = mysql_query($query);
	}
}

?>