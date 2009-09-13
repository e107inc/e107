/*
+-----------------------------------------------------------------------------+
|     e107 website system - Language File
+-----------------------------------------------------------------------------+
|     Spolszczenie systemu e107 v0.7
|     Polskie wsparcie: http://e107.org.pl - http://e107poland.org
|
|     $Revision: 1.8 $
|     $Date: 2009-09-13 10:26:26 $
|     $Author: marcelis_pl $
|     $Source: /cvs_backup/e107_langpacks/e107_handlers/calendar/language/Polish.js,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_handlers/calendar/language/English.js rev. 1.2
+-----------------------------------------------------------------------------+
*/

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
("Niedziela",
 "Poniedziałek",
 "Wtorek",
 "Środa",
 "Czwartek",
 "Piątek",
 "Sobota",
 "Niedziela");

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
("Nie",
 "Pon",
 "Wto",
 "Śro",
 "Czw",
 "Pią",
 "Sob",
 "Nie");

// First day of the week. "0" means display Sunday first, "1" means display
// Monday first, etc.
Calendar._FD = 0;

// full month names
Calendar._MN = new Array
("Styczeń",
 "Luty",
 "Marzec",
 "Kwiecień",
 "Maj",
 "Czerwiec",
 "Lipiec",
 "Sierpień",
 "Wrzesień",
 "Październik",
 "Listopad",
 "Grudzień");

// short month names
Calendar._SMN = new Array
("Sty",
 "Lut",
 "Mar",
 "Kwi",
 "Maj",
 "Cze",
 "Lip",
 "Sie",
 "Wrz",
 "Paź",
 "Lis",
 "Gru");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "O Kalendarzu";

Calendar._TT["ABOUT"] =
"DHTML Date/Time Selector\n" +
"(c) dynarch.com 2002-2005 / Autor: Mihai Bazon\n" + // don't translate this this ;-)
"Najnowsza wersja dostępna jest pod adresem: http://www.dynarch.com/projects/calendar/\n" +
"Skrypt rozpowszechniany zgodnie z licencją GNU LGPL. Odwiedź stronę http://gnu.org/licenses/lgpl.html, aby uzyskać więcj informacji." +
"\n\n" +
"Wybór daty:\n" +
"- Użyj przycisków \xab, \xbb do wybrania roku\n" +
"- Użyj przycisków " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " do wybrania miesiąca\n" +
"- Przytrzymaj wciśnięty przycisk myszy nad jednym z wyżej wymienionych przycisków, aby uzyskać szybsze wybieranie.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Wybór czasu:\n" +
"- Kliknij na której z części godziny, aby ją zwiększyć\n" +
"- lub wciśnij Shift i jednoczenie kliknij, aby zmniejszyć\n" +
"- lub kliknij i przytrzymaj dla szybszego wyboru.";

Calendar._TT["PREV_YEAR"] = "Poprzedni rok";
Calendar._TT["PREV_MONTH"] = "Poprzedni miesiąc";
Calendar._TT["GO_TODAY"] = "Dzisiaj";
Calendar._TT["NEXT_MONTH"] = "Następny miesiąc";
Calendar._TT["NEXT_YEAR"] = "Następny rok";
Calendar._TT["SEL_DATE"] = "Wybierz datę";
Calendar._TT["DRAG_TO_MOVE"] = "Chwyć i upuć, aby przenieść";
Calendar._TT["PART_TODAY"] = " (dzisiaj)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "Wyświetlaj pierwszy %s";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "Zamknij";
Calendar._TT["TODAY"] = "Dzisiaj";
Calendar._TT["TIME_PART"] = "(Shift-)kliknij aby zmienić wartość";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"] = "%a, %e %b";

Calendar._TT["WK"] = "wk";
Calendar._TT["TIME"] = "Czas:";
