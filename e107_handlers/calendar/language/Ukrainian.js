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
("Неділя",
 "Понеділок",
 "Вівторок",
 "Середа",
 "Четвер",
 "П'ятниця",
 "Субота",
 "Неділя");

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
("Нед",
 "Пон",
 "Втр",
 "Сер",
 "Чтв",
 "Птн",
 "Суб",
 "Нед");

// First day of the week. "0" means display Sunday first, "1" means display
// Monday first, etc.
Calendar._FD = 1;

// full month names
Calendar._MN = new Array
("Січень",
 "Лютий",
 "Березень",
 "Квітень",
 "Травень",
 "Червень",
 "Липень",
 "Серпень",
 "Вересень",
 "Жовтень",
 "Листопад",
 "Грудень");

// short month names
Calendar._SMN = new Array
("Січ",
 "Лют",
 "Бер",
 "Кві",
 "Тра",
 "Чер",
 "Лип",
 "Сер",
 "Вер",
 "Жов",
 "Лис",
 "Гру");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "Відносно календаря";

Calendar._TT["ABOUT"] =
"Перебирач DHTML Дата/Час\n" +
"(c) dynarch.com 2002-2005 / Автор: Mihai Bazon\n" + // don't translate this this ;-)
"Остання версія на: http://www.dynarch.com/projects/calendar/\n" +
"Поширюється під GNU LGPL.  Дивитися докладніше http://gnu.org/licenses/lgpl.html." +
"\n\n" +
"Вибір дати:\n" +
"- Використовуйте ці кнопки \xab, \xbb для вибору року\n" +
"- Використовуйте ці кнопки " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " для місяців\n" +
"- Натисніть на ці кнопки для швидкого вибору.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Визначення часу:\n" +
"- Натисніть на години для їх зростання на 1\n" +
"- або натисніть з Shift для їх зменьщення на 1\n" +
"- або натисніть і протягніть для швидкої зміни.";

Calendar._TT["PREV_YEAR"] = "Минул. рік (тримати для меню)";
Calendar._TT["PREV_MONTH"] = "Минул. місяць (тримати для меню)";
Calendar._TT["GO_TODAY"] = "Перейти до цього дня";
Calendar._TT["NEXT_MONTH"] = "Наст. місяць (тримати для меню)";
Calendar._TT["NEXT_YEAR"] = "Наст. рік (тримати для меню)";
Calendar._TT["SEL_DATE"] = "Вибір дати";
Calendar._TT["DRAG_TO_MOVE"] = "Протягніть для заміни";
Calendar._TT["PART_TODAY"] = " (Сьогодні)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "Показати %s перших";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "Закрити";
Calendar._TT["TODAY"] = "Сьогодні";
Calendar._TT["TIME_PART"] = "(Shift-)Натисніть або протягніть для зміни значення";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"] = "%a, %b %e";

Calendar._TT["WK"] = "тиж";
Calendar._TT["TIME"] = "Час:";
