<?php

$text = "<div id=\"Clock\" style=\"text-align:center\">&nbsp</div>";
$ns -> tablerender("Time", $text);

?>
<script type="text/javascript">
<!--
function tick() {
  var hours, minutes, seconds, ap;
  var intHours, intMinutes, intSeconds;
  var today;

  today = new Date();

  intHours = today.getHours();
  intMinutes = today.getMinutes();
  intSeconds = today.getSeconds();

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

  timeString = hours+minutes+seconds+ap;

  Clock.innerHTML = timeString;

  window.setTimeout("tick();", 100);
}

window.onload = tick;

//-->
</script>