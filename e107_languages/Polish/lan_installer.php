<?php

define("LANINS_001", "Instalacja e107");


define("LANINS_002", "Etap ");
define("LANINS_003", "1");
define("LANINS_004", "Wybór Języka");
define("LANINS_005", "Proszę wybrać język w celu dalszej kontunuacji ");
define("LANINS_006", "Język");
define("LANINS_007", "2");
define("LANINS_008", "Sprawdzanie wersji PHP / mySQL oraz uprawnień do plików i katalogów");
define("LANINS_009", "Retest Uprawnień Do Plików");
define("LANINS_010", "nie ma uprawnień do zapisu: ");
define("LANINS_011", "Błąd");
define("LANINS_012", "Funkcje MySQL  wydaje się że nie istnieją. Prawdopodobnie rozszerzenie MySQL PHP nie jest zainstalowane lub nie jest skonfigurowany poprawnie."); // help for 012
define("LANINS_013", "Nie znany numer Twojej wersji MySQL ,oznacza to że serwer jest wyłączony lub nie można nawiązać połączenie.");
define("LANINS_014", "Uprawnienia do plików");
define("LANINS_015", "Wersja PHP");
define("LANINS_016", "MySQL");
define("LANINS_017", "Zaliczone");
define("LANINS_018", "Upewnij się, że uprawnienia do plików i folderów wskazanych powyżej zostały ustawione prawidłowo na Twoim serwerze.  Uprawnienia powinny być ustawione na wartość 777. Aby zmienić uprawnienia, <br />naciśnij prawym przyciskiem myszy na nazwę pliku w oknie Twojego klienta FTP i wybierz CHMOD, <br />albo Set File Permissions, po czym wpisz 777. Jeśli otworzy się okno dialogowe z polami do zaznaczania, <br />wówczas zaznacz wszystkie pola.<br /><br />Po zmianie uprawnień uruchom ponownie skrypt instalacyjny.");
define("LANINS_019", "Wersja PHP zainstalowana na Twoim serwerze nie nadaje się do e107. System e107 do poprawnego działania wymaga wersji PHP od 4.3.0. Zaktualizuj wersję PHP, lub skontaktuj się z usługodawcą w celu aktualizacji.");
define("LANINS_020", "Kontynuacja Instalacji");
define("LANINS_021", "3");
define("LANINS_022", "Serwer mySQL");
define("LANINS_023", "Wprowadź tutaj Twoje ustawienia mySQL.

Jeśli podany użytkownik ma odpowiednie uprawnienia (root), możesz zaznaczyć pole utworzenia nowej bazy danych, jeśli nie - musisz w inny sposób utworzyć bazę danych, lub posłużyć się istniejącą bazą.
Jeśli możesz korzystać tylko z jednej bazy danych, użyj przedrostka, dzięki czemu inne skrypty będą mogły korzystać z tej samej bazy.
Jeśli nie znasz szczegółów połączenia ze swoją bazą mySQL, skontaktuj się z Twoim dostawcą usług.");
define("LANINS_024", "Serwer mySQL:");
define("LANINS_025", "Użytkownik mySQL:");
define("LANINS_026", "Hasło mySQL:");
define("LANINS_027", "Baza danych mySQL:");
define("LANINS_028", "Czy utworzyć Bazę Danych ?");
define("LANINS_029", "Prefix do tabel:");
define("LANINS_030", "Serwer MySQL który chcesz użyć do e107 może zawierać numer portu np. \"hostname:port\" lub ścieżkę do lokalnych zasobów np. \":/path/to/socket\" na localhost.");
define("LANINS_031", "Wpisz nazwę użytkownika którą system e107 będzie łączył się z serwerem MySQL");
define("LANINS_032", "Hasło użytkownika już podałeś");
define("LANINS_033", "Baza Danych MySQL ,którą wybrałeś dla e107 czasami bywa schematyczna.Jeżeli użytkownik ma prawa utworzenia nowej bazy danych, może utworzyć ją automatycznie jeżeli nie istnieje.");
define("LANINS_034", "Przedrostek do tabel zawartych w bazie danych systemu e107. Przydatne jeżeli instalujesz więcej systemów e107.");
define("LANINS_035", "Kontynuacja");
define("LANINS_036", "4");
define("LANINS_037", "Sprawdzanie połączenia z mySQL");
define("LANINS_038", " oraz Utworzenie Bazy Danych");
define("LANINS_039", "Upewnij się że wszystkie dane które wpisałeś w polach są podane prawidłowo,Serwer MySQL, Nazwa Uytkownika MySQL oraz Baza Danych MySQL (Są one wymagane przez serwer MySQL)");
define("LANINS_040", "Błędy");
define("LANINS_041", "e107 nie był w stanie nawiązać połączenia z mySQL przy użyciu podanych przez Ciebie informacji. Powróć do poprzedniej strony i upewnij się, że podane informacje są prawidłowe.");
define("LANINS_042", "Połączenie z mySQL nawiązane i sprawdzone.");
define("LANINS_043", "Nie mogę utworzyć bazy danych, upewnij się, że masz uprawnienia do tworzenia baz danych na tym serwerze.");
define("LANINS_044", "Baza danych została utworzona.");
define("LANINS_045", "Kliknij na button, aby przejść do następnego etapu.");
define("LANINS_046", "5");
define("LANINS_047", "Informacje o administratorze");
define("LANINS_048", "Powrót do poprzedniej strony");
define("LANINS_049", "Hasło i powtórka nie są identyczne, wpisz ponownie.");
define("LANINS_050", "Rozszerzenie XML");
define("LANINS_051", "Zainstalowane");
define("LANINS_052", "Nie zainstalowane");
define("LANINS_053", "e107 .700 wymaga rozszerzenia . Skontaktuj się z usługodawcą lub przeczytaj więcej ");
define("LANINS_054", " przed kontynuowaniem");
define("LANINS_055", "Potwierdzenie Instalacji");
define("LANINS_056", "6");
define("LANINS_057", " e107 ma w tej chwili wszystkie informacje potrzebne do zakończenia instalacji.

Kliknij w button, aby utworzyć tablice w bazie danych i zapisać swoje ustawienia.

");
define("LANINS_058", "7");
define("LANINS_060", "Nie można odczytać pliku z danymi sql

Sprawdź czy plik <b>core_sql.php</b> istnieje w katalogu <b>/e107_admin/sql</b>.");
define("LANINS_061", "e107 nie mógł utworzyć wszystkich wymaganych tabel w bazie danych.
Zanim spróbujesz ponownie wyczyść bazę danych i sprawdź, czy nie masz problemów z korzystaniem z niej.");
define("LANINS_062", "Witaj w swoim nowym serwisie!");
define("LANINS_063", "e107 został prawidłowo zainstalowany i jest gotowy do używania.");
define("LANINS_064", "Twój panel administracyjny ");
define("LANINS_065", "znajduje się tutaj");
define("LANINS_066", "przejdź tam teraz. Będziesz potrzebował zalogować się używając nazwy i hasła podanych w czasie instalacji.");
define("LANINS_067", "tutaj znajdziesz FAQ i dokumentację.");
define("LANINS_068", "Dziękujemy za korzystanie z e107, mamy nadzieję, że spełni wymagania Twojego serwisu.\n(Możesz skasować ten komunikat ze swojego panelu administracyjnego.)\n\n<b>Ta wersja systemu e107 jest jeszcze betą i radzimy używać jej z rozwagą.</b>");
define("LANINS_069", "e107 został prawidłowo zainstalowany !

Ze względu na bezpieczeństwo powinieneś teraz przywrócić uprawnienia pliku <b>e107_config.php</b> do wartości 644.

Skasuj też plik /install.php z serwera - zaraz po kliknięciu na button poniżej.
");
define("LANINS_070", "e107 nie był w stanie zapisać głównego pliku konfiguracyjnego na Twoim serwerze.

Upewnij się, że plik <b>e107_config.php</b> ma odpowiednio ustawione uprawnienia");
define("LANINS_071", "Kończenie Instalacji");

define("LANINS_072", "Nazwa Administratora");
define("LANINS_073", "Ta nazwa będzie używana przez ciebie w celu zalogowania się.Jeżeli chcesz możesz ustawić również jako pokazywaną nazwę Administratora");
define("LANINS_074", "Pokazywana Nazwa Administratora");
define("LANINS_075", "Taką nazwę użytkownicy będą widzieli w Twoim profilu użytkownika.Pozostaw puste jeżeli chcesz używać nazwy logowania.");
define("LANINS_076", "Hasło Administratora");
define("LANINS_077", "Wpisz swoje hasło jakie chcesz używać");
define("LANINS_078", "Potwierdzenie Hasła Administratora");
define("LANINS_079", "W celu potwierdzenia zgodności, wpisz hasło jeszcze raz");
define("LANINS_080", "Adres e-mail Administratora");
define("LANINS_081", "Wpisz adres Administratora");

?>
