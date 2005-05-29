<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        lan_install.php Polish-utf-8 language file 
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
|
|        Translation Updated by: jacek on the 12th Sep 2004
+---------------------------------------------------------------+
*/

define("INSLAN1", "Faza instalacji");
define("INSLAN2", "Sprawdzanie wersji PHP / mySQL oraz uprawnień do plików i katalogów");
define("INSLAN3", "Wersja PHP");
define("INSLAN4", "Niewłaściwa");
define("INSLAN5", "Masz zainstalowaną wersję PHP niezgodną z e107");
define("INSLAN6", "Wykonanie skryptu zatrzymane.");
define("INSLAN7", "Właściwa");
define("INSLAN8", "Wersja mySQL");
define("INSLAN9", "e107 nie mógł ustalić wersji mySQL.");
define("INSLAN10", "Uprawnienie do plików");
define("INSLAN11", "nie ma uprawnień do zapisu");
define("INSLAN12", "folder nie ma uprawnień do zapisu");
define("INSLAN13", "Upewnij się, że uprawnienia do plików i folderów wskazanych powyżej zostały ustawione prawidłowo<br />na Twoim serwerze. Uprawnienia powinny być ustawione na wartość 777. Aby zmienić uprawnienia, <br />naciśnij prawym przyciskiem myszy na nazwę pliku w oknie Twojego klienta FTP i wybierz CHMOD, <br />albo Set File Permissions, po czym wpisz 777. Jeśli otworzy się okno dialogowe z polami do zaznaczania, <br />wówczas zaznacz wszystkie pola.<br /><br />Po zmianie uprawnień uruchom ponownie skrypt instalacyjny.");
define("INSLAN14", "e107 - instalacja");
define("INSLAN15", "BŁˇD POWAŻNY");
define("INSLAN16", "Chociaż nie było możliwe ustalenie stanu Twojego serwera mySQL<br />
przejdź do następnego etapu.");
define("INSLAN17", "Przejdź dalej");
define("INSLAN18", "Sprawdź ponownie uprawnienia");
define("INSLAN19", "Wszystkie testy serwera przebiegły pomyślnie. Kliknij na guzik, aby przejść do następnego etapu");
define("INSLAN20", "informacja o mySQL");
define("INSLAN21", "Wprowadź tutaj Twoje ustawienia mySQL.<br />Jeśli podany użytkownik ma odpowiednie uprawnienia (root), możesz zaznaczyć pole utworzenia nowej bazy danych, jeśli nie - <br />musisz w inny sposób utworzyć bazę danych, lub posłużyć się istniejącą bazą. <br />Jeśli możesz korzystać tylko z jednej bazy danych, użyj przedrostka, dzięki czemu inne programy będą mogły korzystać z tej samej bazy.<br />Jeśli nie znasz szczegółów połączenia ze swoją bazą mySQL, skontaktuj się z Twoim dostawcą usług.");
define("INSLAN22", "Serwer mySQL");
define("INSLAN23", "Użytkownik mySQL");
define("INSLAN24", "Hasło mySQL");
define("INSLAN25", "Baza danych mySQL");
define("INSLAN26", "Prefix do tabel");
define("INSLAN27", "Błąd");
define("INSLAN28", "Wystąpił błąd");
define("INSLAN29", "Nie wypełniłeś wszystkich wymaganych pól, wprowadź ponownie dane o mySQL");
define("INSLAN30", "e107 nie był w stanie nawiązać połączenia z mySQL przy użyciu podanych przez Ciebie informacji.<br />Powróć do poprzedniej strony i upewnij się, że podane informacje są prawidłowe.");
define("INSLAN31", "Sprawdzanie mySQL");
define("INSLAN32", "Połączenie z mySQL nawiązane i sprawdzone.");
define("INSLAN33", "Próbuję utworzyć bazę danych");
define("INSLAN34", "Nie mogę utworzyć bazy danych, upewnij się, że masz uprawnienia do tworzenia baz danych na tym serwerze.");
define("INSLAN35", "Baza danych została utworzona.");
define("INSLAN36", "Kliknij na guzik, aby przejść do następnego etapu.");
define("INSLAN37", "Powrót do poprzedniej strony");
define("INSLAN38", "Informacje o administratorze");
define("INSLAN39", "Wprowadź tutaj nazwę użytkownika, hasło i adres email dla głównego administratora serwisu e107.<br />
Dane te posłużą do uzyskania dostępu do obszaru administratora Twojego serwisu.<br />
Umieść te informacje w bezpiecznym miejscu, ponieważ w razie ich zagubienia<br />
odzyskanie nie jest możliwe.");
define("INSLAN40", "Nazwa Administratora");
define("INSLAN41", "Hasło Administratora");
define("INSLAN42", "Ponownie hasło");
define("INSLAN43", "Adres email Admina");
define("INSLAN44", "Nie wypełniłeś(aś) wszystkich wymaganych pól, wprowadź ponownie dane Administratora.");
define("INSLAN45", "Hasło i powtórka nie są identyczne, wpisz ponownie.");
define("INSLAN46", "prawdopodobnie nie jest prawidłowym adresem email, wpisz ponownie.");
define("INSLAN47", "Gratuluję.Wszystko gotowe!");
define("INSLAN48", "e107 ma w tej chwili wszystkie informacje potrzebne do zakończenia instalacji.<br />
Kliknij na guzik, aby utworzyć tablice w bazie danych i zapisać Twoje ustawienia.");
define("INSLAN49", "e107 nie był w stanie zapisać głównego pliku konfiguracyjnego na Twoim serwerze<br />
Upewnij się, że plik &lt;b&gt;e107_config.php<br />
ma odpowiednio ustawione uprawnienia");
define("INSLAN50", "Instalacja Zakończona!");
define("INSLAN51", "Wszystkie czynności wykonane");
define("INSLAN52", "e107 został zainstalowany!<br />Ze względu na bezpieczeństwo powinieneś teraz przywrócić uprawnienia pliku <br /><b>e107_config.php</b> do wartości 644.<br />Skasuj też plik /install.php z serwera - po kliknięciu na guzik poniżej.");
define("INSLAN53", "Kliknij tutaj, aby przejść do swojego nowego serwisu!");
define("INSLAN54", "Nie mogę odczytać pliku tworzenia sql<br /><br />Upewnij się, że plik o nazwie <b>core_sql.php</b> istnieje w folderze <b>/e107_admin/sql</b>.");
define("INSLAN55", "e107 nie był w stanie utworzyć wszystkich wymaganych tablic w bazie danych.<br />Zanim spróbujesz ponownie wyczyść bazę danych i sprawdź, czy nie masz problemów z korzystaniem z niej.");
define("INSLAN56", "Witaj w swoim nowym serwisie!");
define("INSLAN57", "e107 został prawidłowo zainstalowany i jest gotowy do używania.");
define("INSLAN58", "tutaj znajdziesz FAQ i dokumentację.");
define("INSLAN58pl", "aby otrzymać więcej pomocy w użytkowaniu tego serwisu ,zapraszam do odwiedzin polskiego serwisu e107");
define("INSLAN59", "Dziękujemy za korzystanie z e107, mamy nadzieję, że spełni wymagania Twojego serwisu.\\n<b>(Możesz skasować ten komunikat ze swojego panelu administracyjnego.)</b>");
define("INSLAN60", "zaznacz, aby utworzyć");
define("INSLAN61", "folder");
define("INSLAN62", "lub");
define("INSLAN63", "Błąd dostępu do pliku");
define("INSLAN64", "Ten plik został utworzony przez skrypt instalacyjny.");
define("INSLAN65", "e107 wymaga wersji 4.1.0");
define("INSLAN66", "Jeżeli instalujesz na localhost - swoim komputerze, musisz zaktualizować ");
define("INSLAN67", "nową wersję PHP, zobacz ");
define("INSLAN68", "więcej.Jeżeli ");
define("INSLAN69", "próbujesz zainstalować e107 na dzierżawionym serwerze musisz skontaktować się");
define("INSLAN70", "z administratorem serwera i poprosić o zainstalowanie nowej wersji PHP.");
define("INSLAN71", "Po zainstalowaniu nowej wersji PHP wykonaj ten skrypt ponownie.");
define("INSLAN72", "Może to oznaczać, że mySQL nie jest zainstalowane, lub w tej chwili nie jest włączone,lub");
define("INSLAN73", "też Twoja wersja mySQL nie odpowiada prawidłowo na zapytanie o numer wersji");
define("INSLAN74", "(zdarza się to przy wersji v5.x). Jeśli następny etap instalacji nie powiedzie się,");
define("INSLAN75", "musisz sprawdzić status swojego serwera mySQL.");
define("INSLAN76", "Twój panel administracyjny");
define("INSLAN77", "znajduje się tutaj");
define("INSLAN78", "przejdź tam teraz. Będziesz potrzebował zalogować się używając nazwy i hasła podanych w czasie instalacji.");


?>