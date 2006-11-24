<?php
/*
+-----------------------------------------------------------------------------+
|     e107 website system - Language File.
+-----------------------------------------------------------------------------+
|     Spolszczenie systemu e107 v0.7
|     Polskie wsparcie: http://e107.org.pl - http://e107poland.org
|
|     $Revision: 1.10 $
|     $Date: 2006-11-24 15:38:24 $
|     $Author: marcelis_pl $
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/admin/lan_users_extended.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/admin/lan_users_extended.php rev. 1.17
+-----------------------------------------------------------------------------+
*/
 
define("EXTLAN_1", "Nazwa");
define("EXTLAN_2", "Podgląd");
define("EXTLAN_3", "Wartość");
define("EXTLAN_4", "Wyma<br />gane");
define("EXTLAN_5", "Mogą stosować");
define("EXTLAN_6", "Wgląd dostępny dla");
define("EXTLAN_7", "Edycja dostępna dla");
define("EXTLAN_8", "Akcja");
define("EXTLAN_9", "Dodatkowe pola userów");

define("EXTLAN_10", "Nazwa pola");
define("EXTLAN_11", "Ta nazwa pola zostanie przypisana tabeli, musi więc się różnić od pozostałych"); // This is the name of the field as stored in the table, it must be unique from any other
define("EXTLAN_12", "Wyświetlana nazwa pola");
define("EXTLAN_13", "Ta nazwa pola będzie wyświetlana na stronie");
define("EXTLAN_14", "Typ pola");
define("EXTLAN_15", "Zawartość pola");
define("EXTLAN_16", "Domyślna wartość");
define("EXTLAN_17", "Wpisz wszystkie możliwe wartości w liniach <br /> Zobacz pomoc dla tabel BD.");
define("EXTLAN_18", "Wymagane");
define("EXTLAN_19", "Użytkownicy będą zmuszeni do wpisania wartości w tym polu podczas aktualizacji swoich ustawień.");
define("EXTLAN_20", "Określa, którzy użytkownicy będą mieli dostęp do tego pola.");
define("EXTLAN_21", "Określa, kto zobaczy to pole w ustawieniach użytkownika.");
define("EXTLAN_22", "Określa, kto będzie mógł zobaczyć tą wartość na stronie użytkowników. <br />UWAGA: Ustawienie tego na 'Tylko do odczytu' spowoduje, że będzie to tylko widoczne dla administratorów oraz zarejestrowanych użytkowników.");
define("EXTLAN_23", "Dodaj dodatkowe pole");
define("EXTLAN_24", "Aktualizuj dodatkowe pole");
define("EXTLAN_25", "Przenieś na dół");
define("EXTLAN_26", "Przenieś do góry");
define("EXTLAN_27", "Potwierdź usunięcie");
define("EXTLAN_28", "Aktualnie nie ma zdefiniowanych pól");
define("EXTLAN_29", "Dodatkowe pola użytkownika zostało zapisane.");
define("EXTLAN_30", "Dodatkowe pole zostało usunięte");
// define("EXTLAN_31", "Dodatkowe pole menu");
// define("EXTLAN_32", "Dodatkowa strona");
define("EXTLAN_33", "Anuluj edycję");
define("EXTLAN_34", "Dodatkowe pola");
define("EXTLAN_35", "Kategorie");
define("EXTLAN_36", "Brak przypisanych kategorii");
define("EXTLAN_37", "Brak zdefiniowanych kategorii");
define("EXTLAN_38", "Nazwa kategorii");
define("EXTLAN_39", "Dodaj kategorię");
define("EXTLAN_40", "Kategoria została utworzona");
define("EXTLAN_41", "Kategoria została usunięta");
define("EXTLAN_42", "Aktualizuj kategorię");
define("EXTLAN_43", "Kategoria została zaktualizowana");
define("EXTLAN_44", "Kategoria");
define("EXTLAN_45", "Dodaj nowe pole");
define("EXTLAN_46", "Pomoc");
define("EXTLAN_47", "Dodaj nowy parametr");
define("EXTLAN_48", "Dodaj nową wartość");
define("EXTLAN_49", "Zezwól użytkownikowi na ukrycie");
define("EXTLAN_50", "Ustawienie na tak, pozwoli użytkownikowi ukryć tą wartość przed innymi użytkownikami (nie dotyczy administracji)");
define("EXTLAN_51", "Tu możesz wpisywać ważne parametry zgodne ze standardem w3c<br />ie <i><b>class='tbox' size='40' maxlength='80'</i></b>");
define("EXTLAN_52", "Walidacja kodu wyrażeń regularnych");
define("EXTLAN_53", "Wprowadź kod wyrażeń regularnych, który będzie potrzebny do wykonania porównania poprawności zapisu <br />**ograniczniki wyrażeń regularnych są wymagane**");  // Enter the regex code that will need to be matched to make it a valid entry <br />**regex delimiters are required**");
define("EXTLAN_54", "Komunikat niepowodzenia wyrażeń regularnych");
define("EXTLAN_55", "Wpisz komunikat błędu, który będzie pokazywany, jeśli walidacja wyrażeń regularnych zawiedzie.");
define("EXTLAN_56", "Wcześniej zdefiniowane pola");
define("EXTLAN_57", "Aktywowane");
define("EXTLAN_58", "Nie aktywowane");
define("EXTLAN_59", "Aktywuj");
define("EXTLAN_60", "Dezaktywuj");
define("EXTLAN_61", "Brak");

define("EXTLAN_62", "Tabela");
define("EXTLAN_63", "Id pola");
define("EXTLAN_64", "Wyświetl wartość");

define("EXTLAN_65", "Nie - Nie będzie wyświetlana na stronie rejestracyjnej");
define("EXTLAN_66", "Tak - Będzie wyświetlana na stronie rejestracyjnej");
define("EXTLAN_67", "Nie - Wyświetl na stronie rejestracyjnej");

define("EXTLAN_68", "Pole:");
define("EXTLAN_69", "zostało aktywowane");
define("EXTLAN_70", "BŁĄD!! Pole:");
define("EXTLAN_71", "nie zostało aktywowane!");
define("EXTLAN_72", "zostało deaktywowane");
define("EXTLAN_73", "nie zostało deaktywowane!");
define("EXTLAN_74", "jest zarezerwowaną nazwą pola i nie może być urzyta.");

//textbox
define("EXTLAN_HELP_1", "<b><i>Parametry:</i></b><br />size - rozmiar pola<br />maxlength - maksymalna długość pola<br /><br />class - klasa stylu css<br />style - string stylu css<br /><br />regex - walidacja kodu wyrażeń regularnych<br />regexfail - treść nieudanej walidacji"); // style - css style string
//radio buttons
define("EXTLAN_HELP_2", "Tu będzie treść pomocy dla przycisków");
//dropdown
define("EXTLAN_HELP_3", "Tu będzie treść pomocy dla rozwijalnej listy");
//db field
define("EXTLAN_HELP_4", "<b><i>Wartości:</i></b><br />Tu powinny być ZAWSZE trzy ustalone wartości:<br /><ol><li>tabela BD</li><li>pole zawierające znacznik id</li><li>pole zawierające wartość</li></ol><br />");
//textarea
define("EXTLAN_HELP_5", "Tu będzie treść pomocy dla obszary tekstu");
//integer
define("EXTLAN_HELP_6", "Tu będzie treść pomocy dla pól liczbowych");
//date
define("EXTLAN_HELP_7", "Tu będzie treść pomocy dla pól z datą");

?>
