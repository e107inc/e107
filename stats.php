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

$maxwidth = 400;

$dep = new dbFunc;

$total_page_views = $dep -> dbCount("SELECT sum(counter_unique) FROM ".MUSER."stat_counter");
$row= $dep -> dbFetch();
$text = "<b>".LAN_124."</b>".$total_page_views."<br />";

$total_page_views = $dep -> dbCount("SELECT sum(counter_total) FROM ".MUSER."stat_counter");
$row= $dep -> dbFetch();
$text .= "<b>".LAN_125."</b>".$total_page_views."<br />";


$c=0;
$dep -> dbQuery("SELECT counter_url, sum(counter_unique) FROM ".MUSER."stat_counter GROUP BY counter_url");
$text .= "<br /><b>".LAN_126."</b><br />";
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
while($data1[$c][0]){
	$width = $data1[$c][0]/$w;
	$text .= "<img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2.gif\" width=\"".$width."\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /> ".
	$data1[$c][0]." - ".$data1[$c][1]."
<br />
";
	$c++;
}


$c=0;
$dep -> dbQuery("SELECT counter_url, sum(counter_total) FROM ".MUSER."stat_counter GROUP BY counter_url");
$text .= "<br /><b>Total views by page: </b><br />";
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
while($data2[$c][0]){
	$width = $data2[$c][0]/$w;
	$text .= "<img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2.gif\" width=\"".$width."\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /> ".
	$data2[$c][0]." - ".$data2[$c][1]."
	<br />";
	$c++;
}

$c=0;
$dep -> dbQuery("SELECT info_name, SUM(info_count) FROM ".MUSER."stat_info WHERE info_type='1' GROUP BY info_name");
$text .= "<br /><b>".LAN_128."</b><br />";
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


$c=0;
$dep -> dbQuery("SELECT info_name, SUM(info_count) AS Total FROM ".MUSER."stat_info WHERE info_type='2' GROUP BY info_name");
$text .= "<br /><b>".LAN_129."</b><br />";
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
while($data4[$c][0]){
	$width = $data4[$c][0]/$w;
	$text .= "<img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2.gif\" width=\"".$width."\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /> ".
	$data4[$c][0]." - ".$data4[$c][1]."
	<br />";
	$c++;
}


$c=0;
$dep -> dbQuery("SELECT info_name, SUM(info_count) AS Total FROM ".MUSER."stat_info WHERE info_type='4' GROUP BY info_name");
$text .= "<br /><b>".LAN_130."</b><br />";
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
while($data6[$c][0]){
	$width = $data6[$c][0]/$w;
	$text .= "<img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2.gif\" width=\"".$width."\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /> ".
	$data6[$c][0]." - ".$data6[$c][1]."
	<br />";
	$c++;
}

$c=0;
$dep -> dbQuery("SELECT info_name, SUM(info_count) AS Total FROM ".MUSER."stat_info WHERE info_type='6' GROUP BY info_name");
$text .= "<br /><b>".LAN_131."</b><br />";
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
while($data7[$c][0]){
	$width = $data7[$c][0]/$w;
	$text .= "<img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2.gif\" width=\"".$width."\" height=\"8\" alt=\"\" /><img src=\"".THEME."images/bar2edge.gif\" width=\"1\" height=\"8\" alt=\"\" /> ".
	$data7[$c][0]." - ".$data7[$c][1]."
	<br />";
	$c++;

}

$ns -> tablerender("<div style=\"text-align:center\">".LAN_132."</div>", $text);

require_once(FOOTERF);
?>