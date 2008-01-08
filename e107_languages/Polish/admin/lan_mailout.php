<?php
/*
+-----------------------------------------------------------------------------+
|     e107 website system - Language File.
+-----------------------------------------------------------------------------+
|     Spolszczenie systemu e107 v0.7
|     Polskie wsparcie: http://e107.org.pl - http://e107poland.org
|
|     $Revision: 1.14 $
|     $Date: 2008-01-08 19:25:40 $
|     $Author: marcelis_pl $
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/admin/lan_mailout.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/admin/lan_mailout.php rev. 1.14
+-----------------------------------------------------------------------------+
*/
 
define("PRFLAN_52", "Zapisz zmiany");
define("PRFLAN_63", "Wyślij testowego emaila");
define("PRFLAN_64", "Kliknięcie na przycisk spowoduje wysłanie emaila na adres głównego administratora strony."); // Clicking button will send test email to main admin email address
define("PRFLAN_65", "Kliknij, aby wysłać email do");
define("PRFLAN_66", "Testowa wiadomość email ze strony");
define("PRFLAN_67", "To jest testowa wiadomość email. Otrzymanie jej świadczy o tym, że ustawienia email są poprawnie skonfigurowane!\n\nPozdrowienia\ne107 website system.");
define("PRFLAN_68", "Email nie został wysłany. Prawdopodobnie serwer hostingowy nie jest poprawnie skonfigurowany do wysyłania wiadomości email. Proszę spróbować ponownie używając funkcji SMTP. Możesz również skontaktować się z administratorem hosta i poinformować go o zaistniałym problemie. Być może będzie potrzebne sprawdzenie konfiguracji serwera pod względem ustawień usług email oraz sendmail.");
define("PRFLAN_69", "Wiadomość email została wysłana pomyślnie, proszę sprawdzić swoją skrzynkę odbiorczą.");
define("PRFLAN_70", "Metoda wysyłania emaili");
define("PRFLAN_71", "Jeśli nie wiesz, pozostaw jako php");
define("PRFLAN_72", "Serwer SMTP");
define("PRFLAN_73", "Użytk. SMTP");
define("PRFLAN_74", "Hasło SMTP");
define("PRFLAN_75", "Email nie został wysłany. Proszę wykonać przegląd ustawień SMTP lub odblokować protokół SMTP i sprawdzić ponownie.");

define("MAILAN_01","Od");
define("MAILAN_02","Adres email");
define("MAILAN_03","Do");
define("MAILAN_04","Kopia do");
define("MAILAN_05","Ukryta kopia do");
define("MAILAN_06","Temat");
define("MAILAN_07","Załącznik");
define("MAILAN_08","Wyślij emaila");
define("MAILAN_09","Użyj stylu tematu");
define("MAILAN_10","Subskrynenci");
define("MAILAN_11","Wstaw zmienne");
define("MAILAN_12","Wszyscy zarejestrowani użytkownicy");
define("MAILAN_13","Wszyscy nieautoryzowani użytkownicy");
define("MAILAN_14","Do wysyłania dużej liczby wiadomości email wskazane jest używanie funkcji SMTP - możesz to ustawić w poniższych preferencjach.");
define("MAILAN_15","Wysyłanie wiadomości email");

define("MAILAN_16","użytkownik");
define("MAILAN_17","link do rejestracji");
define("MAILAN_18","id użytkownika");
define("MAILAN_19","Brak adresu email administratora strony. Proszę sprawdzić ustawienia i spróbować ponownie.");
define("MAILAN_20","Śceżka dostępu do <i>sendmail</i>");
define("MAILAN_21","Zapisane wiadomości email");
define("MAILAN_22","Aktualnie nie ma zapisanych pozycji"); // There are currently no saved entries
define("MAILAN_23","grupa użytkowników: ");
define("MAILAN_24", "email(e) są gotowe do wysłania");

define("MAILAN_25", "Pauza");
define("MAILAN_26", "Zatrzymaj zbiorcze wysyłanie poczty co każde");
define("MAILAN_27", "emaile");
define("MAILAN_28", "Czas trwania pauzy");
define("MAILAN_29", "sekund(y)");
define("MAILAN_30", "Więcej niż 30 sekund może spowodować przekroczenie czasu oczekiwania przeglądarki");
define("MAILAN_31", "Przetwarzanie emaila zwrotnego");
define("MAILAN_32", "Adres email");
define("MAILAN_33", "Skrzynka odbiorcza");
define("MAILAN_34", "Nazwa konta");
define("MAILAN_35", "Hasło");
define("MAILAN_36", "Usuń maile zwrotne po sprawdzeniu");

define("MAILAN_37", "Dalej");
define("MAILAN_38", "Anuluj");
define("MAILAN_39", "Korespondencja elektroniczna");
define("MAILAN_40", "Musisz zmienić nazwę pliku <b>e107.htaccess</b> na <b>.htaccess</b> w katalogu");
define("MAILAN_41", "przed wysyłaniem poczty z tej strony.");
define("MAILAN_42", "Ostrzeżenie");
define("MAILAN_43", "Nazwa użytkownika");
define("MAILAN_44", "Login użytkownika");
define("MAILAN_45", "Email użytkownika");
define("MAILAN_46", "Dobierz użytkowników według reguły<br />");
define("MAILAN_47", "zawiera");
define("MAILAN_48", "równa się");
define("MAILAN_49", "Id");
define("MAILAN_50", "Autor");
define("MAILAN_51", "Temat");
define("MAILAN_52", "Ostatnia modyfikacja");
define("MAILAN_53", "Administratorzy");
define("MAILAN_54", "Do siebie");
define("MAILAN_55", "Grupa użytkowników");
define("MAILAN_56", "Wyślij pocztę");
define("MAILAN_57", "Utrzymaj sesję SMTP w działaniu");
define("MAILAN_58", "Wystąpił problem z załącznikiem:");
define("MAILAN_59", "Postęp wysyłania poczty");
define("MAILAN_60", "Wysyłanie...");
define("MAILAN_61", "Nie ma oczekujących emaili do wysłania."); // There are no remaining emails to be sent
define("MAILAN_62", "Emaile wysłane:");
define("MAILAN_63", "Emaile niewysłane:");
define("MAILAN_64", "Całkowity czas wysyłania:"); // Total time elapsed
define("MAILAN_65", "sekundy");
define("MAILAN_66", "Anulowanie zostało zakończone pomyślnie");
define("MAILAN_67", "Używaj 'POP przed uwierzytelnianiem SMTP'");

?>
