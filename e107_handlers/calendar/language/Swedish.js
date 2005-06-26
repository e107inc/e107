/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|     Calendar SV language
|     Encoding: UTF-8
|     Distributed under the same terms as the calendar itself.
|
|     $Source: /cvs_backup/e107_langpacks/e107_handlers/calendar/language/Swedish.js,v $
|     $Revision: 1.1 $
|     $Date: 2005-06-26 11:12:25 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

// full day names
Calendar._DN = new Array
("Söndag",
 "Måndag",
 "Tisdag",
 "Onsdag",
 "Torsdag",
 "Fredag",
 "Lördag",
 "Söndag");

// Please note that the following array of short day names (and the same goes
// for short month names, _SMN) isn't absolutely necessary.  We give it here
// for exemplification on how one can customize the short day names, but if
// they are simply the first N letters of the full name you can simply say:
//
//   Calendar._SDN_len = N; // short day name length
//   Calendar._SMN_len = N; // short month name length
//
// If N = 3 then this is not needed either since we assume a value of 3 if not
// present, to be compatible with translation files that were written before
// this feature.

// short day names
Calendar._SDN = new Array
("Sön",
 "Mån",
 "Tis",
 "Ons",
 "Tor",
 "Fre",
 "Lör",
 "Sön");

// First day of the week. "0" means display Sunday first, "1" means display
// Monday first, etc.
Calendar._FD = 1;

// full month names
Calendar._MN = new Array
("Januari",
 "Februari",
 "Mars",
 "April",
 "Maj",
 "Juni",
 "Juli",
 "Augusti",
 "September",
 "Oktober",
 "November",
 "December");

// short month names
Calendar._SMN = new Array
("Jan",
 "Feb",
 "Mar",
 "Apr",
 "Maj",
 "Jun",
 "Jul",
 "Aug",
 "Sep",
 "Okt",
 "Nov",
 "Dec");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "Om kalendern";

Calendar._TT["ABOUT"] =
"DHTML Datum/Tid väljare\n" +
"(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" + // don't translate this this ;-)
"För senaste versionen, besök: http://www.dynarch.com/projects/calendar/\n" +
"Distribueras under GNU LGPL.  Se http://gnu.org/licenses/lgpl.html för detaljer." +
"\n\n" +
"Datumval:\n" +
"- Använd \xab, \xbb knapparna för att välja år\n" +
"- Använd " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " knapparna för månadsval\n" +
"- Håll ner musknappen på någon av ovanstående knappar för snabbare bläddring.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Val av tid:\n" +
"- Klicka på någon av delarna i klockan för att öka den\n" +
"- eller shift-klicka för att minska den\n" +
"- eller klicka och dra för snabbare bläddring.";

Calendar._TT["PREV_YEAR"] = "Föreg. år (håll för meny)";
Calendar._TT["PREV_MONTH"] = "Föreg. månad (håll för meny)";
Calendar._TT["GO_TODAY"] = "Till idag";
Calendar._TT["NEXT_MONTH"] = "Nästa månad (håll för meny)";
Calendar._TT["NEXT_YEAR"] = "Nästa år (håll för meny)";
Calendar._TT["SEL_DATE"] = "Välj datum";
Calendar._TT["DRAG_TO_MOVE"] = "Dra för att flytta";
Calendar._TT["PART_TODAY"] = " (idag)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "Visa %s först";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "Stäng";
Calendar._TT["TODAY"] = "Idag";
Calendar._TT["TIME_PART"] = "(Shift-)klicka eller dra för att ändra värde";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"] = "%a, %b %e";

Calendar._TT["WK"] = "vk";
Calendar._TT["TIME"] = "Tid:";
