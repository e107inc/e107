<?php
/*
+---------------------------------------------------------------+
|	e107 Clock Menu
|	/clock_menu.php
|
|	Compatible with the e107 content management system
|		http://e107.org
|	
|	Originally written by jalist, modified for greater 
|	detail and cross browser compatiblity by Caveman
|	Last modified 19:11 08/04/2003
|	
|	Works with Mozilla 1.x, NS6, NS7, IE5, IE5.5, Opera 7
|	
|	Released under the terms and conditions  the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
$text = "<div id='Clock'>&nbsp;</div>";
if(!$clock_flat){
	$ns -> tablerender($menu_pref['clock_caption'], "<div style='text-align:center'>".$text."</div>");
}else{
	echo $text;
}

?>
<script type="text/javascript">
<!--

var DayNam = new Array(
"<?php echo isset($LAN_407)?$LAN_407:"Zondag"; ?>",
"<?php echo isset($LAN_401)?$LAN_401:"Maandag"; ?>",
"<?php echo isset($LAN_402)?$LAN_402:"Dinsdag"; ?>",
"<?php echo isset($LAN_403)?$LAN_403:"Woensdag"; ?>",
"<?php echo isset($LAN_404)?$LAN_404:"Donderdag"; ?>",
"<?php echo isset($LAN_405)?$LAN_405:"Vrijdag"; ?>",
"<?php echo isset($LAN_406)?$LAN_406:"Zaterdag"; ?>");

var MnthNam = new Array(
"<?php echo isset($LAN_411)?$LAN_411:"Januari"; ?>",
"<?php echo isset($LAN_412)?$LAN_412:"Februari"; ?>",
"<?php echo isset($LAN_413)?$LAN_413:"Maart"; ?>",
"<?php echo isset($LAN_414)?$LAN_414:"April"; ?>",
"<?php echo isset($LAN_415)?$LAN_415:"Mei"; ?>",
"<?php echo isset($LAN_416)?$LAN_416:"Juni"; ?>",
"<?php echo isset($LAN_417)?$LAN_417:"Juli"; ?>",
"<?php echo isset($LAN_418)?$LAN_418:"Augustus"; ?>",
"<?php echo isset($LAN_419)?$LAN_419:"September"; ?>",
"<?php echo isset($LAN_420)?$LAN_420:"Oktober"; ?>",
"<?php echo isset($LAN_421)?$LAN_421:"November"; ?>",
"<?php echo isset($LAN_422)?$LAN_422:"December"; ?>");

function tick() {
  var hours, minutes, seconds, ap;
  var intHours, intMinutes, intSeconds;  var today;
  today = new Date();
  intDay = today.getDay();
  intDate = today.getDate();
  intMonth = today.getMonth();
  intYear = today.getYear();
  intHours = today.getHours();
  intMinutes = today.getMinutes();
  intSeconds = today.getSeconds();
  timeString = DayNam[intDay]+" de "+intDate;
  if (intDate == 1 || intDate == 8 || intDate == 31 || intDate == 20 || intDate == 21 || intDate == 22 || intDate == 23 || intDate == 24 || intDate == 25 || intDate == 26 || intDate == 27 || intDate == 28 || intDate == 29 || intDate == 30) {
    timeString= timeString + "ste ";
  } else if (intDate == 2 || intDate == 7 || intDate == 9 || intDate == 19) {
    timeString= timeString + "de ";
  } else if (intDate == 3) {
    timeString= timeString + "de ";
  } else {
    timeString = timeString + "de ";
  } 
  if (intYear < 2000){
	intYear += 1900;
  }
  timeString = "Het is "+timeString+" van "+MnthNam[intMonth]+" "+intYear;
  if (intHours == 0) {
     hours = "12:";
     ap = "am.";
  } else if (intHours < 12) { 
     hours = intHours+":";
     ap = "am.";
  } else if (intHours == 12) {
     hours = "12:";
     ap = "pm.";
  } else {
     intHours = intHours - 12
     hours = intHours + ":";
     ap = "pm.";
  }
  if (intMinutes < 10) {
     minutes = "0"+intMinutes;
  } else {
     minutes = intMinutes;
  }
  if (intSeconds < 10) {
     seconds = ":0"+intSeconds;
  } else {
     seconds = ":"+intSeconds;
  }
  timeString = (document.all)? timeString+". "+hours+minutes+seconds+". "+ap:timeString+". <br>De tijd is "+hours+minutes+" "+ap;
  var clock = (document.all) ? document.all("Clock") : document.getElementById("Clock");
  clock.innerHTML = timeString;
  (document.all)?window.setTimeout("tick();", 1000):window.setTimeout("tick();", 6000);
}

tick();

//-->
</script>
