<?php

$text = "<div id=\"Clock\" style=\"text-align:center\">&nbsp</div>";
$ns -> tablerender("Time", $text);

?>
<script type="text/javascript">
<!--

var DayNam = new Array(
"Sunday",
"Monday",
"Tuesday",
"Wednesday",
"Thursday",
"Friday",
"Saturday");

var MnthNam = new Array(
"January",
"February",
"March",
"April",
"May",
"June",
"July",
"August",
"September",
"October",
"November",
"December");

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
  timeString = DayNam[intDay]+" "+intDate;
  if (intDate == 1 || intDate == 21 || intDate == 31) {
    timeString= timeString + "st ";
  } else if (intDate == 2 || intDate == 22) {
    timeString= timeString + "nd ";
  } else if (intDate == 3 || intDate == 23) {
    timeString= timeString + "rd ";
  } else {
    timeString = timeString + "th ";
  } 
  if (intYear < 2000){
	intYear += 1900;
  }
  timeString = timeString+" of "+MnthNam[intMonth]+" "+intYear;
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
     minutes = "0"+intMinutes+":";
  } else {
     minutes = intMinutes+":";
  }
  if (intSeconds < 10) {
     seconds = "0"+intSeconds+" ";
  } else {
     seconds = intSeconds+" ";
  }
  timeString = timeString+"<br>"+hours+minutes+seconds+ap;
  Clock.innerHTML = timeString;
  window.setTimeout("tick();", 100);
}

window.onload = tick;

//-->
</script>