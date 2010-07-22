<?php
/*
+-----------------------------------------------------------------------------+
|     e107 website system - Language File
+-----------------------------------------------------------------------------+
|     Spolszczenie systemu e107
|     e107 Polish Team
|     Polskie wsparcie: http://e107pl.org
|
|     $Revision: 1.12 $
|     $Date: 2010/01/31 18:39:07 $
|     $Author: marcelis_pl $
|     $Source: /cvsroot/e107pl/e107_main/0.7_PL_strict_utf8/e107_languages/Polish/lan_installer.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/lan_installer.php rev. 1.27
+-----------------------------------------------------------------------------+
*/
 
define("LANINS_001", "Instalacja e107");

define("LANINS_002", "Etap ");
define("LANINS_003", "1");
define("LANINS_004", "Wybór języka");
define("LANINS_005", "Proszę wybrać język, który będzie używany w dalszym procesie instalacji");
define("LANINS_006", "Ustaw język");
define("LANINS_007", "4");
define("LANINS_008", "Sprawdzenie wersji PHP, MySQL oraz uprawnień plików");
define("LANINS_009", "Testuj ponownie uprawnienia plików");
define("LANINS_010", "Plik niezapisywalny: ");
define("LANINS_010a", "Folder niezapisywalny: ");
define("LANINS_011", "Błąd");
define("LANINS_012", "Prawdopodobnie serwer hostujący nie obsługuje funkcji MySQL. Możliwe, że rozszerzenie MySQL PHP nie jest zainstalowane lub Twoja instalacja PHP nie została skompilowane ze wsparciem dla MySQL."); // pomoc dla 012
define("LANINS_013", "Nie można określić numer wersji MySQL. To nie jest błąd krytyczny, więc proszę kontynuować instalację. Należy jednak pamiętać, że e107 wymaga MySQL w wersji >= 3,23, aby funkcjonować prawidłowo.");
define("LANINS_014", "Uprawnienia plików");
define("LANINS_015", "Wersja PHP");
define("LANINS_016", "MySQL");
define("LANINS_017", "OK");
define("LANINS_018", "Upewnij się, że wszystkie wymienione pliki istnieją i posiadają odpowiednie uprawnienia. Atrybuty powinny być ustawione na wartość 777, lecz zależy to od systemu operacyjnego - skontaktuj się z administratorem serwera, jeżeli napotkasz na jakiekolwiek problemy.");
define("LANINS_019", "Wersja PHP zainstalowany na serwerze nie jest zdolna do uruchomienia skryptu e107. e107 wymaga PHP w wersji co najmniej 4.3.0, aby działać poprawnie. Albo zaktualizuj wersję PHP, albo skontaktuj się z administratorem serwera i poinformuj o potrzebie aktualizacji.");
define("LANINS_020", "Zacznij instalację");
define("LANINS_021", "2");
define("LANINS_022", "Konfiguracja połączenia z serwerem MySQL");
define("LANINS_023", "Poniżej proszę wpisać dane niezbędne do poprawnego połączenia z bazą danych MySQL.

Jeśli masz na serwerze uprawnienia <i>root</i> (głównego administratora), możesz utworzyć nową bazę danych zaznaczając pole wyboru. Jeśli nie masz wspomnianych uprawnień, musisz utworzyć bazę danych w jakiś inny sposób lub posłużyć się już istniejącą bazą.

Jeśli masz tylko jedną bazę danych użyj prefiksu, dzięki któremu inne skrypty będą mogły korzystać z tej samej bazy.

Jeśli nie znasz dokładnych danych swojego serwera MySQL, skontaktuj się z administratorem usługi hostingowej.");
define("LANINS_024", "Serwer MySQL:");
define("LANINS_025", "Użytkownik MySQL:");
define("LANINS_026", "Hasło MySQL:");
define("LANINS_027", "Baza danych MySQL:");
define("LANINS_028", "Utwórz nową");
define("LANINS_029", "Prefiks:");
define("LANINS_030", "Adres serwera MySQL (najczęściej localhost), na którym chcesz zainstalować system e107. Adres może zawierać numer portu, np. “nazwahosta:port”, lub ścieżkę do lokalnych zasobów serwera, np. \":/ścieżka/do/zasobów\".");
define("LANINS_031", "Wpisz nazwę użytkownika, którą system e107 będzie używał do łączenia się z serwerem MySQL");
define("LANINS_032", "Wpisz hasło użytkownika MySQL");
define("LANINS_033", "Wpisz nazwę baza danych MySQL w której chcesz zainstalować e107, czasami zwana jest również jako schemat. Jeśli użytkownnik posiada uprawnienia do tworzenia baz danych, możesz zaznaczyć opcję automatycznego utworzenia bazy danych, jeśli takowa jeszcze nie istnieje.");
define("LANINS_034", "Prefiks wykorzystywany przy tworzeniu tabel e107. Przydatny dla wielu instalacji e107 w jednym schemacie bazy danych.");
define("LANINS_035", "Dalej");
define("LANINS_036", "3");
define("LANINS_037", "Weryfikacja połączenia z MySQL");
define("LANINS_038", " i tworzenie bazy danych");
define("LANINS_039", "Proszę upewnić się, że wszystkie najważniejsze pola zostały wypełnione - serwer MySQL, użytkownik MySQL i baza danych MySQL (są zawsze wymagane do uzyskania połączenia z serwerem MySQL)");
define("LANINS_040", "Błąd");
define("LANINS_041", "System e107 nie mógł nawiązać połączenia z serwerem MySQL używając podanych informacji. Proszę wrócić do poprzedniej strony i upewnić się, że wpisane dane są poprawne.");
define("LANINS_042", "Połączenie z serwerem MySQL nawiązane i sprawdzone.");
define("LANINS_043", "Nie można utworzyć bazy danych, należy upewnić się, że uprawnienia do tworzenia baz danych na serwerze są poprawne.");
define("LANINS_044", "Utworzono bazę danych.");
define("LANINS_045", "Proszę kliknąć na przycisk, aby przejść do następnego etapu.");
define("LANINS_046", "5");
define("LANINS_047", "Dane administratora");
define("LANINS_048", "Wróć do poprzedniego etapu");
define("LANINS_049", "Wprowadzone hasła nie są takie same. Proszę wrócić i spróbować ponownie.");
define("LANINS_050", "Rozszerzenie XML");
define("LANINS_051", "Zainstalowane");
define("LANINS_052", "Niezainstalowane");
define("LANINS_053", "e107 0.7.x wymaga zainstalowanego rozszerzenia PHP XML. Przed kontynuacją, proszę skontaktować się z administratorem serwera lub zapoznać się z informacjami zawartymi na stronie <a href='http://php.net/manual/pl/ref.xml.php' target='_blank'>php.net</a>");
//not used: define("LANINS_054", " przed kontynuacją");
define("LANINS_055", "Potwierdzenie instalacji");
define("LANINS_056", "6");
define("LANINS_057", " e107 posiada obecnie wszystkie potrzebne informacje, aby zakończyć instalację.

Proszę kliknąć przycisk, aby utworzyć tabel bazy danych i zapisać wszystkie ustawienia.

");
define("LANINS_058", "7");
define("LANINS_060", "Nie można odczytać pliku z danymi sql

Proszę upewnić się, że plik <b>core_sql.php</b> znajduje się w folderze <b>/e107_admin/sql</b>.");
define("LANINS_061", "e107 nie był w stanie utworzyć wszystkich wymaganych tabel bazy danych. 
Proszę wyczyścić bazę danych i rozwiązać wszystkie problemy przed ponowną próbą.");
define("LANINS_062", "[b]Witamy na nowej stronie internetowej![/b]
System e107 został pomyślnie zainstalowany i jest gotowy do aktualizacji zawartości strony.<br />Twoja sekcja administracyjna (panel administratora) jest dostępna [link=e107_admin/admin.php]pod tym odnośnikiem[/link] - kliknij, aby teraz tam przejść. Do logowania użyj loginu i hasła administratora, które wpisałeś podczas procesu instalacji.

[b]Wsparcie[/b]
Oficjalna strona e107: [link=http://e107.org]http://e107.org[/link], znajdziesz tutaj FAQ najczęściej zadawane pytania) oraz dokumentację.
Forum: [link=http://e107.org/e107_plugins/forum/forum.php]http://e107.org/e107_plugins/forum/forum.php[/link]
Społecznoć programistów wtyczek: [link=http://www.e107coders.org]http://e107coders.org[/link]

[b]Strony polskiego wsparcia[/b]
Serwis e107pl.org: [link=http://e107.org.pl]http://e107.org.pl[/link] - serwis e107 Polska - newsy, download, forum, dokumentacja e107 itp.

[b]Pliki do pobrania[/b]
Wtyczki: [link=http://plugins.e107.org]http://plugins.e107.org[/link]
Motywy: [link=http://themes.e107.org]http://themes.e107.org[/link]

Dziękujemy za wypróbowanie e107, mamy nadzieję, że spełnia on twoje potrzeby.
(Możesz usunąć tę wiadomość z poziomu panelu administratora.)");

define("LANINS_063", "Witamy w e107");
define("LANINS_069", "e107 został pomyślnie zainstalowany!

Ze względu na bezpieczeństwo przywróć uprawnienia pliku <b>e107_config.php</b> do wartości 644 (CHMOD 644).

Po kliknięciu na poniższy przycisk, usuń również z serwera plik <i>install.php</i>
");
define("LANINS_070", "E107 nie był w stanie zapisać głównego pliku konfiguracyjnego na serwerze.

Proszę upewnić się, że plik <b>e107_config.php</b> posiada prawidłowe uprawnienia");
define("LANINS_071", "Finalizacja instalacji");

define("LANINS_072", "Login");
define("LANINS_073", "Jest to nazwa, która będzie używana do logowania w serwisie. Można ją również używać jako nicka administratora.");
define("LANINS_074", "Nick");
define("LANINS_075", "Jest to nazwa, która zostanie wyświetlona użytkownikom w Twoim profilu, forach i innych obszarach. Pozostaw to pole puste, jeśli jako nicka chcesz używać loginu.");
define("LANINS_076", "Hasło");
define("LANINS_077", "Proszę wpisać hasło administratora, którym będziesz się posługiwał podczas logowania w serwisie.");
define("LANINS_078", "Potwierdź");
define("LANINS_079", "Proszę powtórzyć hasło administratora.");
define("LANINS_080", "E-mail");
define("LANINS_081", "Wpisz swój adres e-mail.");

define("LANINS_082", "nazwa@twojastrona.pl");

// Lepsze zgłaszanie listy tworzonych błędów
define("LANINS_083", "MySQL zgłosił błąd:");
define("LANINS_084", "Instalator nie mógł nawiązać połączenia z bazą danych");
define("LANINS_085", "Instalator nie mógł wybrać bazy danych:");

define("LANINS_086", "Pola - login administratora, hasło administratora oraz adres e-mail administratora są <b>wymagane</b>. Proszę powrócić do ostatniej strony i upewnić się, że informacje zostały poprawnie wprowadzone.");

define("LANINS_087", "Różne");
define("LANINS_088", "Strona główna");
define("LANINS_089", "Pobieranie");
define("LANINS_090", "Użytkownicy");
define("LANINS_091", "Dodaj newsa");
define("LANINS_092", "Kontakt");
define("LANINS_093", "Nadaje dostęp do prywatnych menu");
define("LANINS_094", "Przykładowa klasa użytkowników forum prywatnego");
define("LANINS_095", "Sprawdzanie integralności");

define("LANINS_096", 'Ostatnie komentarze');
define("LANINS_097", '[więcej ...]');
//define("LANINS_098", 'Artykuły');
//define("LANINS_099", 'Strona główna artykułów ...');
define("LANINS_100", 'Ostatnie posty na forum');
define("LANINS_101", 'Zapisz ustawienia menu');
define("LANINS_102", 'Data / Czas');
//define("LANINS_103", 'Recenzje');
//define("LANINS_104", 'Strona główna recenzji ...');

define("LANINS_105", 'Rozpoczynanie nazwy bazy danych lub prefiksu cyfrą, a następnie literą “e” lub “E” jest niedozwolone.<br />Nazwa bazy danych lub prefiks nie może być pusta.');
define("LANINS_106", 'OSTRZEŻENIE - e107 nie może zapisywać do katalogów i/lub wylistować plików. Jeśli nie zatrzyma to procesu instalacji e107, będzie to oznaczało, że niektóre funkcje są niedostępne. 
				Aby używać tych funkcji, należy zmienić uprawnienia plików.');


// for v0.7.16+ only
define('LANINS_DB_UTF8_CAPTION', 'Kodowanie znaków dla MySQL:');
define('LANINS_DB_UTF8_LABEL',   'Wymusić kodowanie UTF-8 dla bazy danych?');
define('LANINS_DB_UTF8_TOOLTIP', 'Jeśli jest ustawione i będzie możliwość, skrypt instalacyjny uczyni bazę danych kompatybilną z kodowaniem UTF-8. Kodowanie bazy danych w UTF-8 jest wymagane dla następnej wersji e107.');

?>