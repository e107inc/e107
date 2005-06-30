<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|
|	&#138;Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/


define("PAGE_NAME", "Lista Pozycji Nowości");

//all benaeth will be deprecated sometime
define("LIST_1", "Pozycje Nowości");
define("LIST_2", "niezatytułowany");
define("LIST_3", "Nowe od twojej ostatniej wizyty ...");
define("LIST_4", "Brak pozycji nowości.");
define("LIST_5", "Postów na czacie");
define("LIST_6", "zablokowany przez admina");
define("LIST_7", "Wypowiedzi na forum");
define("LIST_8", "Nowych użytkowników");
define("LIST_9", "Komentarzy");
define("LIST_10", "Pomysłów");
define("LIST_11", "Plików");
define("LIST_12", "Wiadomości");
define("LIST_13", "Bugtracker");
define("LIST_14", "Ankiet");
define("LIST_15", "FAQ");
define("LIST_16", "Błędów");
define("LIST_17", "Pomysłów");

define("LIST_13", "Profil");
define("LIST_14", "Artykuł");
define("LIST_15", "Zawartość");

define("LIST_18", "FAQ");
define("LIST_18", "Ankieta");
define("LIST_19", "Opisów");
define("LIST_20", "Bugtracker");
	
define("LIST_21", "Artykuły");
define("LIST_22", "Pomysłów");
//all above will be deprecated sometime

define("LIST_ADMIN_1", "ostatnio");
define("LIST_ADMIN_2", "aktualizuj ustawienia");
define("LIST_ADMIN_3", "ustawienia uaktualniono");
define("LIST_ADMIN_4", "sekcja");
define("LIST_ADMIN_5", "menu");
define("LIST_ADMIN_6", "strona");
define("LIST_ADMIN_7", "włacz");
define("LIST_ADMIN_8", "wyłacz");
define("LIST_ADMIN_9", "otwórz");
define("LIST_ADMIN_10", "zamknij");
define("LIST_ADMIN_11", "aktualizuj");
define("LIST_ADMIN_12", "wybierz");
define("LIST_ADMIN_13", "Witaj na stronie Ostatnich Zmian w serwisie ".SITENAME." ! Ta strona pokazuje ostanie zmiany dokonane w tym serwisie.");
define("LIST_ADMIN_14", "ostatnio dodane");
define("LIST_ADMIN_15", "od twojej ostatniej wizyty");
define("LIST_ADMIN_16", "Witaj na stronie ".SITENAME." ! Ta strona pokazuje ostanie zmiany dokonane w tym serwisie od Twojej ostatniej wizyty.");

define("LIST_ADMIN_SECT_1", "sekcje");
define("LIST_ADMIN_SECT_2", "wybierz, które sekcje mają być pokazane");
define("LIST_ADMIN_SECT_3", "");

define("LIST_ADMIN_SECT_4", "styl wyświetlania");
define("LIST_ADMIN_SECT_5", "wybierz, które sekcje mają być domyślnie otwarte");
define("LIST_ADMIN_SECT_6", "");

define("LIST_ADMIN_SECT_7", "autor");
define("LIST_ADMIN_SECT_8", "wybierz, sekcje w których notka o autorze ma być pokazywana");
define("LIST_ADMIN_SECT_9", "");

define("LIST_ADMIN_SECT_10", "kategoria");
define("LIST_ADMIN_SECT_11", "wybierz sekcje w których kategorie mają być pokazywane");
define("LIST_ADMIN_SECT_12", "");

define("LIST_ADMIN_SECT_13", "data");
define("LIST_ADMIN_SECT_14", "wybierz jeżeli data ma być pokazywana");
define("LIST_ADMIN_SECT_15", "");

define("LIST_ADMIN_SECT_16", "ilość pozycji");
define("LIST_ADMIN_SECT_17", "wybierz ile pozycji w danej sekcji ma być pokazywanych.");
define("LIST_ADMIN_SECT_18", "");

define("LIST_ADMIN_SECT_19", "kolejność pozycji");
define("LIST_ADMIN_SECT_20", "wybierz kolejność w sekcjach jaka ma być pokazywana");
define("LIST_ADMIN_SECT_21", "");

define("LIST_ADMIN_SECT_22", "ikona");
define("LIST_ADMIN_SECT_23", "wybierz ikonę dla każdej sekcji");
define("LIST_ADMIN_SECT_24", "");

define("LIST_ADMIN_SECT_25", "tytuł");
define("LIST_ADMIN_SECT_26", "podaj tytuł dla każdej sekcji");
define("LIST_ADMIN_SECT_27", "");


define("LIST_ADMIN_OPT_1", "ogólne");
define("LIST_ADMIN_OPT_2", "ostatnio w serwisie"); //recent page
define("LIST_ADMIN_OPT_3", "ostatnio w serwisie"); //recent menu
define("LIST_ADMIN_OPT_4", "nowa strona");
define("LIST_ADMIN_OPT_5", "nowe menu");
define("LIST_ADMIN_OPT_6", "opcje");

define("LIST_ADMIN_MENU_2", "ikona : domyślna");
define("LIST_ADMIN_MENU_3", "użyj domyślnej ikony tematycznej bullet, jeżeli żadna ikona nie jest przypisana lub jeżeli  ikona : użyj  zaznaczyłeś wyłączone");
define("LIST_ADMIN_MENU_4", "");

define("LIST_ADMIN_LAN_2", "tytuł");
define("LIST_ADMIN_LAN_3", "wpisz tytuł");
define("LIST_ADMIN_LAN_4", "");

define("LIST_ADMIN_LAN_5", "ikona : użyj");
define("LIST_ADMIN_LAN_6", "użyj ikon dla każdej sekcji");
define("LIST_ADMIN_LAN_7", "");

define("LIST_ADMIN_LAN_8", "znaki");
define("LIST_ADMIN_LAN_9", "wybierz ile znaków w nagłówku ma być pokazywane.");
define("LIST_ADMIN_LAN_10", "zostaw puste aby pokazywać pełen nagłówek");

define("LIST_ADMIN_LAN_11", "prefiks");
define("LIST_ADMIN_LAN_12", "wybierz prefiks dla zbyt długich wpisów w nagłówku");
define("LIST_ADMIN_LAN_13", "zostaw puste aby wyłączyć prefiks");

define("LIST_ADMIN_LAN_14", "data");
define("LIST_ADMIN_LAN_15", "wybierz format daty");
define("LIST_ADMIN_LAN_16", "Więcej informacji o formatowaniu daty, przeczytasz  <a href='http://www.php.net/manual/pl/function.strftime.php' rel='external'>na stronie php.net</a>");

define("LIST_ADMIN_LAN_17", "data dzisiejsza");
define("LIST_ADMIN_LAN_18", "wybierz format dzisiejszej daty");
define("LIST_ADMIN_LAN_19", "Więcej informacji o formatowaniu daty, przeczytasz <a href='http://www.php.net/manual/pl/function.strftime.php' rel='external'>na stronie php.net</a>");

define("LIST_ADMIN_LAN_20", "kolomny");
define("LIST_ADMIN_LAN_21", "wybierz ilość kolumn");
define("LIST_ADMIN_LAN_22", "określ ilość kolumn. Każda kolumna, których ilość określisz, będzie pokazywana oddzielnie.");

define("LIST_ADMIN_LAN_23", "tekst powitalny");
define("LIST_ADMIN_LAN_24", "wpisz tekst, który będzie widoczny na górze strony");
define("LIST_ADMIN_LAN_25", "");

define("LIST_ADMIN_LAN_26", "pokaż puste");
define("LIST_ADMIN_LAN_27", "ustaw, jeżeli wiadomość musi być pokazana gdy w sekcji brak rezultatów ");
define("LIST_ADMIN_LAN_28", "");

define("LIST_ADMIN_LAN_29", "ikona : domyślna");
define("LIST_ADMIN_LAN_30", "użyj domyślnej ikony tematycznej bullet, jeżeli żadna ikona nie jest przypisana lub jeżeli  ikona : użyj  zaznaczyłeś wyłączone");
define("LIST_ADMIN_LAN_31", "");

define("LIST_ADMIN_LAN_32", "timelapse:days");
define("LIST_ADMIN_LAN_33", "maximum of days users can look back");
define("LIST_ADMIN_LAN_34", "");
define("LIST_ADMIN_LAN_35", "days");

define("LIST_ADMIN_LAN_36", "timelapse");
define("LIST_ADMIN_LAN_37", "display a select box with number of days to look back?");
define("LIST_ADMIN_LAN_38", "");


define("LIST_MENU_1", "recent additions");
define("LIST_MENU_2", "by");
define("LIST_MENU_3", "on");
define("LIST_MENU_4", "in");
define("LIST_MENU_5", "days");
define("LIST_MENU_6", "view content for how may days?");
define("LIST_MENU_7", "");
define("LIST_MENU_8", "");
define("LIST_MENU_9", "");
define("LIST_MENU_10", "");
define("LIST_MENU_11", "");
define("LIST_MENU_12", "");
define("LIST_MENU_13", "");
define("LIST_MENU_14", "");
define("LIST_MENU_15", "");
define("LIST_MENU_16", "");
define("LIST_MENU_17", "");
define("LIST_MENU_18", "");
define("LIST_MENU_19", "");

define("LIST_NEWS_1", "news");
define("LIST_NEWS_2", "no news items");

define("LIST_COMMENT_1", "comments");
define("LIST_COMMENT_2", "no comments");
define("LIST_COMMENT_3", "news");
define("LIST_COMMENT_4", "faq");
define("LIST_COMMENT_5", "poll");
define("LIST_COMMENT_6", "docs");
define("LIST_COMMENT_7", "bugtrack");
define("LIST_COMMENT_8", "content");
define("LIST_COMMENT_9", "download");
define("LIST_COMMENT_10", "ideas");



define("LIST_DOWNLOAD_1", "downloads");
define("LIST_DOWNLOAD_2", "no downloads");

define("LIST_MEMBER_1", "members");
define("LIST_MEMBER_2", "no members");

define("LIST_CONTENT_1", "content");
define("LIST_CONTENT_2", "no content");

define("LIST_CHATBOX_1", "chatbox");
define("LIST_CHATBOX_2", "no chatbox posts");

define("LIST_CALENDAR_1", "calendar");
define("LIST_CALENDAR_2", "no calendar events");

define("LIST_LINKS_1", "links");
define("LIST_LINKS_2", "no links");

define("LIST_FORUM_1", "forum");
define("LIST_FORUM_2", "no forum posts");
define("LIST_FORUM_3", "views:");
define("LIST_FORUM_4", "replies:");
define("LIST_FORUM_5", "lastpost:");
define("LIST_FORUM_6", "on:");

?>
