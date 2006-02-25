/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|     Calendar SV language
|     Encoding: UTF-8
|     Distributed under the same terms as the calendar itself.
|
|     $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_handlers/calendar/language/Swedish.js,v $
|     $Revision: 1.2 $
|     $Date: 2006-02-25 13:24:52 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

// full day names
Calendar._DN = new Array
("S&ouml;ndag",
 "M&aring;ndag",
 "Tisdag",
 "Onsdag",
 "Torsdag",
 "Fredag",
 "L&ouml;rdag",
 "S&ouml;ndag");

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
("S&ouml;n",
 "M&aring;n",
 "Tis",
 "Ons",
 "Tor",
 "Fre",
 "L&ouml;r",
 "S&ouml;n");

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
"DHTML Datum/Tid v&auml;ljare\n" +
"(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" + // don't translate this this ;-)
"F&ouml;r senaste versionen, bes&ouml;k: http://www.dynarch.com/projects/calendar/\n" +
"Distribueras under GNU LGPL.  Se http://gnu.org/licenses/lgpl.html f&ouml;r detaljer." +
"\n\n" +
"Datumval:\n" +
"- Anv&auml;nd \xab, \xbb knapparna f&ouml;r att v&auml;lja &aring;r\n" +
"- Anv&auml;nd " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " knapparna f&ouml;r m&aring;nadsval\n" +
"- H&aring;ll ner musknappen p&aring; n&aring;gon av ovanst&aring;ende knappar f&ouml;r snabbare bl&auml;ddring.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Val av tid:\n" +
"- Klicka p&aring; n&aring;gon av delarna i klockan f&ouml;r att &ouml;ka den\n" +
"- eller shift-klicka f&ouml;r att minska den\n" +
"- eller klicka och dra f&ouml;r snabbare bl&auml;ddring.";

Calendar._TT["PREV_YEAR"] = "F&ouml;reg. &aring;r (h&aring;ll f&ouml;r meny)";
Calendar._TT["PREV_MONTH"] = "F&ouml;reg. m&aring;nad (h&aring;ll f&ouml;r meny)";
Calendar._TT["GO_TODAY"] = "Till idag";
Calendar._TT["NEXT_MONTH"] = "N&auml;sta m&aring;nad (h&aring;ll f&ouml;r meny)";
Calendar._TT["NEXT_YEAR"] = "N&auml;sta &aring;r (h&aring;ll f&ouml;r meny)";
Calendar._TT["SEL_DATE"] = "V&auml;lj datum";
Calendar._TT["DRAG_TO_MOVE"] = "Dra f&ouml;r att flytta";
Calendar._TT["PART_TODAY"] = " (idag)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "Visa %s f&ouml;rst";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "St&auml;ng";
Calendar._TT["TODAY"] = "Idag";
Calendar._TT["TIME_PART"] = "(Shift-)klicka eller dra f&ouml;r att &auml;ndra v&auml;rde";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"] = "%a, %b %e";

Calendar._TT["WK"] = "vk";
Calendar._TT["TIME"] = "Tid:";
