<?php
/*
+-----------------------------------------------------------------------------+
|     e107 website system - Language File
+-----------------------------------------------------------------------------+
|     Spolszczenie systemu e107
|     e107 Polish Team
|     Polskie wsparcie: http://e107pl.org
|
|     $Revision: 1.16 $
|     $Date: 2009-09-13 10:26:27 $
|     $Author: marcelis_pl $
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/admin/lan_mailout.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/admin/lan_mailout.php rev. 1.16
+-----------------------------------------------------------------------------+
*/
 
define("PRFLAN_52", "Zapisz zmiany");
define("PRFLAN_63", "Wyślij testowego e-maila");
//define("PRFLAN_64", "Kliknięcie przycisku spowoduje wysłanie e-maila na adres głównego administratora strony."); // Clicking button will send test email to main admin email address
define("PRFLAN_65", "Kliknij, aby wysłać e-mail do");
define("PRFLAN_66", "Testowa wiadomość e-mail -");
define("PRFLAN_67", "To jest testowa wiadomość e-mail. Otrzymanie jej świadczy o tym, że ustawienia poczty są poprawnie skonfigurowane!\n\nPozdrowienia,\ne107 website system.");
define("PRFLAN_68", "E-mail nie został wysłany. Prawdopodobnie serwer hostingowy nie jest poprawnie skonfigurowany do wysyłania wiadomości e-mail. Proszę spróbować ponownie używając funkcji SMTP. Możesz również skontaktować się z administratorem hosta i poinformować go o zaistniałym problemie. Być może będzie potrzebne sprawdzenie konfiguracji serwera pod względem ustawień usług e-mail oraz sendmail.");
define("PRFLAN_69", "Wiadomość e-mail została wysłana pomyślnie, sprawdź swoją skrzynkę odbiorczą.");
define("PRFLAN_70", "Metoda wysyłania poczty");
define("PRFLAN_71", "Jeśli nie wiesz, pozostaw php");
define("PRFLAN_72", "Serwer SMTP");
define("PRFLAN_73", "Użytk. SMTP");
define("PRFLAN_74", "Hasło SMTP");
define("PRFLAN_75", "E-mail nie został wysłany. Sprawdź swoje ustawienia SMTP lub odblokuj protokół SMTP i spróbuj ponownie.");

define("MAILAN_01","Od");
define("MAILAN_02","E-mail");
define("MAILAN_03","Do");
define("MAILAN_04","Kopia do");
define("MAILAN_05","Ukryta kopia do");
define("MAILAN_06","Temat");
define("MAILAN_07","Załącznik");
define("MAILAN_08","Wyślij e-mail");
define("MAILAN_09","Użyj stylu motywu graficznego");
define("MAILAN_10","Subskrynenci");
define("MAILAN_11","Wstaw zmienne");
define("MAILAN_12","Wszyscy zarejestrowani użytkownicy");
define("MAILAN_13","Wszyscy nieautoryzowani użytkownicy");
define("MAILAN_14","Do wysyłania dużej liczby wiadomości e-mail wskazane jest używanie protokołu SMTP - można to ustawić w poniższych preferencjach.");
define("MAILAN_15","Wysyłanie wiadomości e-mail");

define("MAILAN_16","nick");
define("MAILAN_17","odnośnik weryfikacyjny");
define("MAILAN_18","ID użytkownika");
define("MAILAN_19","Brak adresu e-mail administratora strony. Sprawdź swoje ustawienia i spróbuj ponownie.");
define("MAILAN_20","Śceżka dostępu do <i>sendmail</i>");
define("MAILAN_21","Zapisane wiadomości e-mail");
define("MAILAN_22","Aktualnie nie ma zapisanych pozycji"); // There are currently no saved entries
define("MAILAN_23","klasa użytkowników: ");
define("MAILAN_24", "e-mail(e) są gotowe do wysłania");

define("MAILAN_25", "Pauza");
define("MAILAN_26", "Zatrzymaj zbiorcze wysyłanie poczty co każde");
define("MAILAN_27", "e-maile");
define("MAILAN_28", "Czas trwania pauzy");
define("MAILAN_29", "sekund(y)");
define("MAILAN_30", "Więcej niż 30 sekund może spowodować przekroczenie czasu oczekiwania przeglądarki");
define("MAILAN_31", "Przetwarzanie poczty odbitej");
define("MAILAN_32", "Adres e-mail");
define("MAILAN_33", "Serwer poczty przychodzącej");
define("MAILAN_34", "Nazwa konta");
define("MAILAN_35", "Hasło");
define("MAILAN_36", "Usuń odbitą pocztę po sprawdzeniu");

define("MAILAN_37", "Dalej");
define("MAILAN_38", "Anuluj");
define("MAILAN_39", "Korespondencja elektroniczna");
define("MAILAN_40", "Musisz zmienić nazwę pliku <b>e107.htaccess</b> na <b>.htaccess</b> w katalogu");
define("MAILAN_41", "przed wysyłaniem poczty z tej strony.");
define("MAILAN_42", "Ostrzeżenie");
define("MAILAN_43", "Nick");
define("MAILAN_44", "Login");
define("MAILAN_45", "E-mail");
define("MAILAN_46", "Dobierz użytkowników wg reguły<br />");
define("MAILAN_47", "zawiera");
define("MAILAN_48", "równa się");
define("MAILAN_49", "ID");
define("MAILAN_50", "Autor");
define("MAILAN_51", "Temat");
define("MAILAN_52", "Ostatnia modyfikacja");
define("MAILAN_53", "Administratorzy");
define("MAILAN_54", "Do siebie");
define("MAILAN_55", "Klasa użytkowników");
define("MAILAN_56", "Wyślij pocztę");
define("MAILAN_57", "Utrzymaj sesję SMTP w działaniu");
define("MAILAN_58", "Wystąpił problem z załącznikiem:");
define("MAILAN_59", "Postęp wysyłania poczty");
define("MAILAN_60", "Wysyłanie...");
define("MAILAN_61", "Nie ma oczekujących e-maili do wysłania."); // There are no remaining emails to be sent
define("MAILAN_62", "E-maile wysłane:");
define("MAILAN_63", "E-maile niewysłane:");
define("MAILAN_64", "Całkowity czas wysyłania:"); // Total time elapsed
define("MAILAN_65", "sekundy");
define("MAILAN_66", "Anulowano pomyślnie");
define("MAILAN_67", "Używaj 'POP przed uwierzytelnianiem SMTP'");
define('MAILAN_68', 'Adres testowy');

?>