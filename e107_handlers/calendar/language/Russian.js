// ** I18N

// Calendar RU language
// Author: yarodin, <yarodin@gmail.com>
// Encoding: UTF-8
// Distributed under the same terms as the calendar itself.

// For translators: please use UTF-8 if possible.  We strongly believe that
// Unicode is the answer to a real internationalized world.  Also please
// include your contact information in the header, as can be seen above.

// full day names
Calendar._DN = new Array
("Воскресенье",
 "Понедельник",
 "Вторник",
 "Среда",
 "Четверг",
 "Пятница",
 "Суббота",
 "Воскресенье");

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
("Вск",
 "Пон",
 "Втр",
 "Срд",
 "Чтв",
 "Птн",
 "Суб",
 "Вск");

// First day of the week. "0" means display Sunday first, "1" means display
// Monday first, etc.
Calendar._FD = 1;

// full month names
Calendar._MN = new Array
("Январь",
 "Февраль",
 "Март",
 "Апрель",
 "Май",
 "Июнь",
 "Июль",
 "Август",
 "Сентябрь",
 "Октябрь",
 "Ноябрь",
 "Декабрь");

// short month names
Calendar._SMN = new Array
("Янв",
 "Фев",
 "Мар",
 "Апр",
 "Май",
 "Июн",
 "Июл",
 "Авг",
 "Сен",
 "Окт",
 "Ноя",
 "Дек");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "О календаре";

Calendar._TT["ABOUT"] =
"DHTML Селектор Дата/Время\n" +
"(c) dynarch.com 2002-2005 / Автор: Mihai Bazon\n" + // don't translate this ;-)
"Последние версии доступны на: http://www.dynarch.com/projects/calendar/\n" +
"Распространяется под GNU LGPL.  Подробнее на http://gnu.org/licenses/lgpl.html " +
"\n\n" +
"Выбор даты:\n" +
"- Используйте кнопки \xab, \xbb для выбора года\n" +
"- Используйте кнопки " + String.fromCharCode(0x2039) + ", " + String.fromCharCode(0x203a) + " для выбора месяца\n" +
"- Держите нажатой кнопку мыши на любой из кнопок расположенных выше для быстрого выбора.";
Calendar._TT["ABOUT_TIME"] = "\n\n" +
"Выбор времени:\n" +
"- Щелкните по любой из частей времени для увеличения значения\n" +
"- или Shift-щелчек для уменьшения it\n" +
"- или шелчек и перемещение для быстрого выбора.";

Calendar._TT["PREV_YEAR"] = "Пред. год (задержать для меню)";
Calendar._TT["PREV_MONTH"] = "Пред. месяц (задержать для меню)";
Calendar._TT["GO_TODAY"] = "Перейти до сегодняшнего дня";
Calendar._TT["NEXT_MONTH"] = "Следующий месяц (задержать для меню)";
Calendar._TT["NEXT_YEAR"] = "Следующий год (задержать для меню)";
Calendar._TT["SEL_DATE"] = "Выберите дату";
Calendar._TT["DRAG_TO_MOVE"] = "Перетащить для замены";
Calendar._TT["PART_TODAY"] = " (сегодня)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "Показать %s первым";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "1,7";

Calendar._TT["CLOSE"] = "Закрыть";
Calendar._TT["TODAY"] = "Сегодня";
Calendar._TT["TIME_PART"] = "(Shift-)шелчек или перетащить для изменения значения";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%Y-%m-%d";
Calendar._TT["TT_DATE_FORMAT"] = "%a, %b %e";

Calendar._TT["WK"] = "нед";
Calendar._TT["TIME"] = "Время:";
