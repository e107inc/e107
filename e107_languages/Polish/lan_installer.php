<?php
/*
+-----------------------------------------------------------------------------+
|     e107 website system - Language File.
+-----------------------------------------------------------------------------+
|     Spolszczenie systemu e107 v0.7
|     Polskie wsparcie: http://e107.org.pl - http://e107poland.org
|
|     $Revision: 1.5 $
|     $Date: 2007-10-23 17:03:44 $
|     $Author: marcelis_pl $
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/lan_installer.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/lan_installer.php rev. 1.19
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
define("LANINS_009", "Ponów test uprawnień plików");
define("LANINS_010", "Plik niezapisywalny: ");
define("LANINS_010a", "Folder niezapisywalny: ");
define("LANINS_011", "Błąd");
define("LANINS_012", "Prawdopodobnie serwer hostujący nie obsługuje funkcji MySQL. Możliwe, że rozszerzenie MySQL PHP nie jest zainstalowane lub Twoja instalacja PHP nie została skompilowane ze wsparciem dla MySQL."); // pomoc dla 012
define("LANINS_013", "Nie mogę ustalić wersji MySQL. To nie jest błąd krytyczny, więc proszę kontynuować instalację, ale proszę być świadomym tego, że system e107 wymaga MySQL w wersji 3.23 lub nowszej, aby funkcjonować poprawnie.");
define("LANINS_014", "Uprawnienia plików");
define("LANINS_015", "Wersja PHP");
define("LANINS_016", "MySQL");
define("LANINS_017", "OK");
define("LANINS_018", "Upewnij się, że na pliki i foldery wskazane powyżej nadałeś odpowiednie uprawnienia. Atrybuty powinny być ustawione na wartość 777. <br />Aby zmienić uprawnienia plików, przejdź do okna klienta FTP, a następnie kliknij prawym przyciskiem myszy na pliku i wybierz CHMOD (<i>Set File Permissions</i>), po czym wpisz tam wartość 777. Jeżeli natomiast wyświetli się okno dialogowe z polami do wyboru, to zaznacz wszystkie opcje i potwierdź swój wybór.<br /><br />Po zmianie uprawnień kontynuuj instalację skryptu, lecz jeśli napotkasz na jakieś utrudnienia skontaktuj się ze swoim administratorem serwera.");
define("LANINS_019", "Wersja PHP zainstalowana na serwerze hostującym nie nadaje się do uruchamiania skryptu e107. System e107 do poprawnego działania wymaga PHP od wersji 4.3.0 wzwyż. Zaktualizuj wersję PHP lub skontaktuj się z usługodawcą, aby go poinformować, że do poprawnego działania Twojego serwisu wymagana jest nowsza wersja PHP.");
define("LANINS_020", "Zacznij instalację");
define("LANINS_021", "2");
define("LANINS_022", "Konfiguracja połączenia z serwerem MySQL");
define("LANINS_023", "Poniżej proszę wpisać dane niezbędne do poprawnego połączenia z bazą danych MySQL.

Jeśli masz na serwerze uprawnienia <i>root</i> (głównego administratora) lub masz nadane uprawnienia do tworzenia baz danych, możesz zaznaczyć pole utworzenia nowej bazy. Jeśli nie masz wspomnianych uprawnień, musisz utworzyć bazę danych w jakiś inny sposób lub posłużyć się już istniejącą bazą.

Jeśli masz tylko jedną bazę danych użyj prefiksu, dzięki któremu inne skrypty będą mogły korzystać z tej samej bazy.

Jeśli nie znasz dokładnych danych niezbędnych do połączenia z Twoim kontem MySQL, skontaktuj się z administratorem serwera, na którym hostowana będzie strona.");
define("LANINS_024", "Host MySQL:");
define("LANINS_025", "Użytkownik:");
define("LANINS_026", "Hasło:");
define("LANINS_027", "Baza danych:");
define("LANINS_028", "Utwórz nową bazę");
define("LANINS_029", "Prefiks:");
define("LANINS_030", "Adres serwera MySQL (najczęściej localhost), na którym chcesz zainstalować system e107. Adres może zawierać numer portu, np. \"nazwahosta:port\", lub ścieżkę do lokalnych zasobów serwera, np. \":/ścieżka/do/zasobów\".");
define("LANINS_031", "Wpisz nazwę użytkownika, którą system e107 będzie używał do łączenia się z serwerem MySQL");
define("LANINS_032", "Wpisz hasło użytkownika MySQL");
define("LANINS_033", "Wpisz nazwę baza danych MySQL, którą wybrałeś do instalacji skryptu e107. Jeśli jeszcze jej nie utworzyłeś, a posiadasz odpowiednie uprawnienia, wpisz sugerowaną nazwę i zaznacz pole obok - baza danych zostanie utworzona automatycznie.");
define("LANINS_034", "Prefiks (przedrostek) do tabel zawartych w bazie danych systemu e107. Jest przydatny, jeżeli instalujesz więcej niż jeden skrypt w tej samej bazie.");
define("LANINS_035", "Dalej");
define("LANINS_036", "3");
define("LANINS_037", "Weryfikacja połączenia z MySQL");
define("LANINS_038", " i tworzenie bazy danych");
define("LANINS_039", "Proszę upewnić się, że wszystkie najważniejsze pola zostały wypełnione - Host MySQL, użytkownik i baza danych są zawsze wymagane do uzyskania połączenia z serwerem MySQL)");
define("LANINS_040", "Błąd");
define("LANINS_041", "System e107 nie mógł nawiązać połączenia z serwerem MySQL używając podanych informacji. Wróć do poprzedniej strony i upewnij się, że wpisane dane są poprawne.");
define("LANINS_042", "Połączenie z serwerem MySQL nawiązane i sprawdzone.");
define("LANINS_043", "Nie mogę utworzyć bazy danych. Proszę upewnić się, że masz poprawne uprawnienia do tworzenia bazy danych na wskazanym serwerze.");
define("LANINS_044", "Baza danych utworzona pomyślnie.");
define("LANINS_045", "Proszę kliknąć na przycisk, aby przejść do następnego etapu.");
define("LANINS_046", "5");
define("LANINS_047", "Specyfikacja administratora");
define("LANINS_048", "Wróć do poprzedniego etapu");
define("LANINS_049", "Wpisane hasła nie są identyczne. Proszę wrócić i spróbować ponownie.");
define("LANINS_050", "Rozszerzenie XML");
define("LANINS_051", "Zainstalowane");
define("LANINS_052", "Niezainstalowane");
define("LANINS_053", "e107 .700 wymaga zainstalowanego rozszerzenia PHP XML. Proszę skontaktować się z administratorem hosta lub przeczytać więcej informacji ");
define("LANINS_054", " przed kontynuacją");
define("LANINS_055", "Potwierdzenie instalacji");
define("LANINS_056", "6");
define("LANINS_057", " e107 ma teraz wszystkie informacje, które są konieczne do ukończenia instalacji.

Proszę kliknąć na przycisk, aby utworzyć tabele bazy danych i zapisać wszystkie ustawienia.

");
define("LANINS_058", "7");
define("LANINS_060", "Nie można odczytać pliku z danymi sql

Proszę upewnić się, że plik <b>core_sql.php</b> znajduje się w folderze <b>/e107_admin/sql</b>.");
define("LANINS_061", "e107 nie mógł utworzyć wszystkich wymaganych tabel bazy danych.
Proszę wyczyścić bazę danych i rozwiązać wszystkie problemy przed ponowną próbą.");
define("LANINS_062", "[b]Witaj na swojej nowej stronie internetowej![/b]
System e107 został pomyślnie zainstalowany i jest gotowy do aktualizacji zawartości strony.<br />Twoja sekcja administracyjna (panel administratora) jest dostępna [link=e107_admin/admin.php]pod tym linkiem[/link] - kliknij, aby teraz tam przejść. Do logowania użyj loginu i hasła administratora, które wpisałeś podczas procesu instalacji.

[b]Wsparcie[/b]
Oficjalna strona e107: [link=http://e107.org]http://e107.org[/link], znajdziesz tutaj FAQ najczęściej zadawane pytania) oraz dokumentację.
Forum: [link=http://e107.org/e107_plugins/forum/forum.php]http://e107.org/e107_plugins/forum/forum.php[/link]

[b]Strony polskiego wsparcia[/b]
Serwis e107poland.org: [link=http://e107poland.org]http://e107poland.org[/link] - download, newsy, FAQ, forum i inne.
Serwis e107.org.pl: [link=http://e107.org.pl]http://e107.org.pl[/link] - strona zespołu [i]e107pl Dev Team[/i] - newsy, download, forum, bugtracker, dokumentacja e107 itp.

[b]Download[/b]
Pluginy: [link=http://e107coders.org]http://e107coders.org[/link]
Tematy: [link=http://e107themes.org]http://e107themes.org[/link]

Dziękujemy za korzystanie z e107, mamy nadzieję, że spełni on Twoje potrzeby i będziesz zadowolony z jego użytkowania.
(Możesz usunąć tego newsa za pomocą panelu administracyjnego.)");

define("LANINS_063", "Witaj w e107");
define("LANINS_069", "e107 został pomyślnie zainstalowany!

Ze względu na bezpieczeństwo przywróć uprawnienia pliku <b>e107_config.php</b> do wartości 644 (CHMOD 644).

Proszę również, po kliknięciu na poniższy przycisk, usunąć z serwera plik <i>install.php</i>
");
define("LANINS_070", "e107 nie mógł zapisać głównego pliku konfiguracyjnego na obecnym serwerze.

Proszę upewnić się, że plik <b>e107_config.php</b> posiada prawidłowe uprawnienia");
define("LANINS_071", "Finalizacja instalacji");

define("LANINS_072", "Login");
define("LANINS_073", "Login administratora, posłuży Ci do logowania się na stronę oraz sekcji Panel administratora. Jeśli chcesz możesz go również używać jako nazwy administratora.");
define("LANINS_074", "Wyświetlana nazwa");
define("LANINS_075", "To nazwa, pod którą będziesz widoczny na forum, czacie, w komentarzach itp. Będzie ona również wyświetlana w Twoim profilu. Pozostaw to pole puste, jeśli jako nazwy administratora chcesz używać loginu.");
define("LANINS_076", "Hasło");
define("LANINS_077", "Proszę wpisać hasło administratora, którym będziesz się posługiwał podczas logowania w serwisie.");
define("LANINS_078", "Potwierdź");
define("LANINS_079", "Proszę powtórzyć hasło administratora.");
define("LANINS_080", "Email");
define("LANINS_081", "Wpisz swój adres email.");

define("LANINS_082", "nazwa@twojastrona.pl");

// Lepsze zgłaszanie listy tworzonych błędów
define("LANINS_083", "Zgłoszenie błędu MySQL:");
define("LANINS_084", "Instalator nie mógł nawiązać połączenia z bazą danych");
define("LANINS_085", "Instalator nie mógł wybrać bazy danych:");

define("LANINS_086", "Pola - nazwa administratora, hasło administratora oraz adres email administratora - są <b>wymagane</b>. Proszę powrócić do ostatniej strony i upewnić się, że informacje zostały poprawnie wprowadzone.");

define("LANINS_087", "Różne");
define("LANINS_088", "Strona główna");
define("LANINS_089", "Download");
define("LANINS_090", "Użytkownicy");
define("LANINS_091", "Dodaj newsa");
define("LANINS_092", "Kontakt");
define("LANINS_093", "Nadaje dostęp do prywatnych menu");
define("LANINS_094", "Przykładowa grupa użytkowników forum prywatnego");
define("LANINS_095", "Sprawdzanie integralności");

define("LANINS_096", 'Ostatnie komentarze');
define("LANINS_097", '[więcej ...]');
define("LANINS_098", 'Artykuły');
define("LANINS_099", 'Strona startowa artykułów ...');
define("LANINS_100", 'Ostatnie posty na forum');
define("LANINS_101", 'Zapisz ustawienia menu');
define("LANINS_102", 'Data / Czas');
define("LANINS_103", 'Odwiedzin');
define("LANINS_104", 'Odwiedź stronę startową ...');

define("LANINS_105", '');
define("LANINS_106", '');
?>

