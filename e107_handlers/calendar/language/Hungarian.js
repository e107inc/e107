// ** I18N

// Calendar EN language
// Author: Mihai Bazon, <mihai_bazon@yahoo.com>
// Encoding: any
// Distributed under the same terms as the calendar itself.

// For translators: please use UTF-8 if possible.  We strongly believe that
// Unicode is the answer to a real internationalized world.  Also please
// include your contact information in the header, as can be seen above.

// full day names
Calendar._DN = new Array
("Vasárnap",
 "Hétfő",
 "Kedd",
 "Szerda",
 "Csütörtök",
 "Péntek",
 "Szombat",
 "Vasárnap");

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
("V",
 "H",
 "K",
 "Sze",
 "Cs",
 "P",
 "Szo",
 "V");

// First day of the week. "0" means display Sunday first, "1" means display
// Monday first, etc.
Calendar._FD = 0;

// full month names
Calendar._MN = new Array
("Január",
 "Február",
 "Március",
 "Április",
 "Május",
 "Június",
 "Július",
 "Augusztus",
 "Szeptember",
 "Október",
 "November",
 "December");

// short month names
Calendar._SMN = new Array
("Jan",
 "Febr",
 "Márc",
 "Ápr",
 "Máj",
 "Jún",
 "Júl",
 "Aug",
 "Szept",
 "Okt",
 "Nov",
 "Dec");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "A naptárról";

Calendar._TT["ABOUT"] =
"DHTML Date/Time Selector\n" +
"(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n" + // don't translate this this ;-)
"A legfrissebb verzió megtekintéséhez látogass ide: http://www.dynarch.com/projects/calendar/\n" +
"Kiadva a GNU LGPL alatt.  A részletek megtekintése itt http://gnu.org/licenses/lgpl.html ." +
"\n\n" +
"Dátum kiválasztás:\n" +
"- Használd a \xab, \xbb gombokat az év kiválasztásához\n" +
"- Használd a " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " gombot a hónap kiválasztásához\n" +
"- Tartsd az egér gombot bármelyik gomb felett a gyorsabb kiválasztáshoz.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Idő kiválasztása:\n" +
"- Katt az idő bármely részére a növeléséhez\n" +
"- vagy Shift-katt a csökkentéshez\n" +
"- vagy katt és fogd a gyorsabb kiválasztáshoz.";

Calendar._TT["PREV_YEAR"] = "Előző év (tartsd a menühöz)";
Calendar._TT["PREV_MONTH"] = "Előző hónap (tartsd a menühöz)";
Calendar._TT["GO_TODAY"] = "Ugrás mai napra";
Calendar._TT["NEXT_MONTH"] = "Következő hónap (tartsd a menühöz)";
Calendar._TT["NEXT_YEAR"] = "Következő év (tartsd a menühöz)";
Calendar._TT["SEL_DATE"] = "Dátum kiválasztása";
Calendar._TT["DRAG_TO_MOVE"] = "Fogd a mozgatáshoz";
Calendar._TT["PART_TODAY"] = " (ma)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "%s elsőként megjelenít";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "Bezár";
Calendar._TT["TODAY"] = "Ma";
Calendar._TT["TIME_PART"] = "(Shift-)Katt vagy fogd az érték megváltoztatásához";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"] = "%a, %b %e";

Calendar._TT["WK"] = "wk";
Calendar._TT["TIME"] = "Idő:";
