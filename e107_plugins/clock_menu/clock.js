<!--

function tick(e107_datepref,e107_dateformat,e107_datesuff1,e107_datesuff2,e107_datesuff3,e107_datesuff4) {
  if(e107_datepref=='undefined'){e107_datepref = '';}
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
  timeString = DayNam[intDay]+" "+e107_datepref+" "+intDate;
  if (intDate == 1 || intDate == 21 || intDate == 31) {
    timeString= timeString + e107_datesuff1 + " ";
  } else if (intDate == 2 || intDate == 22) {
    timeString= timeString  + e107_datesuff2 + " ";
  } else if (intDate == 3 || intDate == 23) {
    timeString= timeString  + e107_datesuff3 + " ";
  } else {
    timeString = timeString  + e107_datesuff4 + " ";
  } 
  if (intYear < 2000){
	intYear += 1900;
  }
  timeString = timeString+" "+MnthNam[intMonth]+" "+intYear;
  if(e107_dateformat == 1){
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
  }else{
    if (intHours < 10) {
       hours = "0" + intHours + ":";
    } else {
       hours = intHours + ":";
    }
    ap = '';
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
  timeString = (document.all)? timeString+", "+hours+minutes+seconds+" "+ap:timeString+" "+hours+minutes+" "+ap;
  var clock = (document.all) ? document.all("Clock") : document.getElementById("Clock");
  clock.innerHTML = timeString;
  (document.all)?window.setTimeout("tick('"+e107_datepref+"','"+e107_dateformat+"','"+e107_datesuff1+"','"+e107_datesuff2+"','"+e107_datesuff3+"','"+e107_datesuff4+"');", 1000):window.setTimeout("tick('"+e107_datepref+"','"+e107_dateformat+"','"+e107_datesuff1+"','"+e107_datesuff2+"','"+e107_datesuff3+"','"+e107_datesuff4+"');", 6000);
}

////tick();

//-->