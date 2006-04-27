<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     Czech Language Pack for e107 Version 0.7
|     Copyright (c) 2006 - translation by Tomas Liska (fox),
|                        - czech language correction by Mirek Dvorak
|     e107 czech support: http://www.fox1.cz
+----------------------------------------------------------------------------+
*/

define("LANINS_001", "Instalace systému e107");


define("LANINS_002", "Krok instalace ");
define("LANINS_003", "1");
define("LANINS_004", "Výběr jazyka");
define("LANINS_005", "Vyberte si prosím jazyk, který chcete používat během instalace");
define("LANINS_006", "Nastavit jazyk");
define("LANINS_007", "4");
define("LANINS_008", "kontrola verzí PHP &amp; MySQL  / kontrola oprávnění přístupu k souborům");
define("LANINS_009", "Překontrolovat oprávnění přístupu k souborům");
define("LANINS_010", "Do souboru nelze zapsat: ");
define("LANINS_010a", "Do adresáře nelze zapsat: ");
define("LANINS_011", "Chyba");
define("LANINS_012", "Funkce MySQL neexistuje. Pravděpodobně není instalováno PHP rozšíření pro MySQL nebo nebyla instalace PHP kompilována s podporou MySQL."); // help for 012
define("LANINS_013", "Nemohu zjistit verzi MySQL. Nejde o fatální chybu, takže můžete pokračovat v instalaci, ale buďte si vědomi toho, že e107 vyžaduje MySQL >= 3.23, aby vše pracovalo korektně.");
define("LANINS_014", "Oprávnění přístupu k souborům");
define("LANINS_015", "Verze PHP");
define("LANINS_016", "MySQL");
define("LANINS_017", "PASS");
define("LANINS_018", "Ujistěte se, že všechny soubory existují a mají nastaveno právo zápisu pro server. Běžně to znamená nastavení pomocí CHMOD na 777, ale záleží na prostředí. Pokud máte problémy, kontaktujte správce svého hostingu.");
define("LANINS_019", "Verze instalovaná na Vašem serveru není dostatečná pro zajištění provozu e107. e107 vyžaduje PHP minimálně ve verzi 4.3.0. Nainstalujte si novější verzi nebo kontaktujte správce svého hostingu.");
define("LANINS_020", "Pokračovat v instalaci");
define("LANINS_021", "2");
define("LANINS_022", "Detaily serveru MySQL");
define("LANINS_023", "Vložte prosím údaje nastavení MySQL.

Pokud máte root práva, lze vytvořit databázi - zaškrtněte výběrové pole. Pokud práva roota nemáte, musíte vytvořit databázi sami a nebo použít již existující.

Pokud máte k dispozici pouze jednu databázi, použijte prefixi, aby mohly s databází pracovat i jiné skripty.
Pokud neznáte detaily nastavení MySQL serveru kontaktujte správce hostingu");
define("LANINS_024", "MySQL server:");
define("LANINS_025", "MySQL uživatel:");
define("LANINS_026", "MySQL heslo:");
define("LANINS_027", "MySQL databáze:");
define("LANINS_028", "Vytvořit databázi?");
define("LANINS_029", "Prefix pro tabulky:");
define("LANINS_030", "Server MySQL, který chcete použít pro e107. Může obsahovat i číslo portu, např. \"hostname:port\" nebo cestu k lokální zásuvce (socket), např. \":/cesta/k/zásuvce/\", v případě lokálního nastavení (localhost).");
define("LANINS_031", "Uživatelské jméno, které má e107 použít pro spojení se serverem MySQL");
define("LANINS_032", "Heslo k tomuto uživatelskému jménu");
define("LANINS_033", "Databáze, do které se má e107 umístit/nainstalovat, někdy též označováno jako schema. Pokud má uživatel práva pro vytvoření databáze, můžete zvolit, aby byla databáze vytvořena automaticky (v případě, že ještě neexistuje).");
define("LANINS_034", "Prefix, který má e107 používat při tvorbě tabulek databáze. To je užitečné hlavně pro vícero instalací e107 v rámci jednoho databázového schématu.");
define("LANINS_035", "Pokračovat");
define("LANINS_036", "3");
define("LANINS_037", "Ověření spojení s MySQL");
define("LANINS_038", " a tvorba databáze");
define("LANINS_039", "Ujistěte se, že jste vyplnili všechny údaje. Nejdůležitější jsou: jméno MySQL serveru, uživatelské jméno MySQL a heslo, název databáze MySQL (tyto údaje server MySQL vždy vyžaduje).");
define("LANINS_040", "Chyby");
define("LANINS_041", "S použitím vložených informací nemohl systém e107 navázat spojení se serverem MySQL. Vraťte se na předchozí stranu a ujistěte se, že jsou vložené údaje správné.");
define("LANINS_042", "Spojení se serverem bylo navázáno a ověřeno.");
define("LANINS_043", "Nelze vytvořit databázi. Ujistěte se prosím, že máte korektně nastavená práva pro vytváření databáze na serveru MySQL (create permission)");
define("LANINS_044", "Databáze byla úspěšně vytvořena.");
define("LANINS_045", "Klikněte na tlačítko, ať se dostaneme o krok dál.");
define("LANINS_046", "5");
define("LANINS_047", "Detaily správce");
define("LANINS_048", "Vrátit na poslední krok");
define("LANINS_049", "Hesla, která byla vložena nejsou stejná. Vraťte se prosím zpět a zkuste to znovu.");
define("LANINS_050", "XML rozšíření");
define("LANINS_051", "nainstalováno");
define("LANINS_052", "nenainstalováno");
define("LANINS_053", "e107 .700 vyžaduje nainstalované XML rozšíření PHP. Kontaktujte správce svého hostingu nebo si přečtěte informace na ");
define("LANINS_054", " dříve, než budete pokračovat");
define("LANINS_055", "Potvrzení instalace");
define("LANINS_056", "6");
define("LANINS_057", " e107 má nyní k dispozici všechny informace nezbytné pro instalaci.

Klikněte prosím na tlačítko. Bude vytvořena databáze a uložena všechna nastavení.

");
define("LANINS_058", "7");
define("LANINS_060", "Není možné přečíst soubor s daty pro sql.

Ujistěte se, že soubor <b>core_sql.php</b> je v adresáři <b>/e107_admin/sql</b> .");
define("LANINS_061", "e107 se nezdařilo vytvořit všechny tabulky databáze.
Před tím, než to zkusíte znovu, vyčistěte databázi a odstraňte všechny problémy.");

define("LANINS_062", "[b]Vítejte na svém novém webu![/b]
e107 je nainstalováno a připraveno.<br />Sekce pro správce je na [link=e107_admin/admin.php]této adrese[/link]. Kliknutím na odkaz do ní vstoupíte. Budete se muset přihlásit s použitím uživatelského jména a hesla, které jste vložili během instalace.

[b]Podpora[/b]
e107 domovská stránka (anglicky): [link=http://e107.org]http://e107.org[/link], kde najdete např. FAQ - často kladené otázky a dokumentaci.
Diskuse (anglicky): [link=http://e107.org/e107_plugins/forum/forum.php]http://e107.org/e107_plugins/forum/forum.php[/link]

[b]Ke stažení[/b]
Doplňky (plugins): [link=http://e107coders.org]http://e107coders.org[/link]
Témata (themes): [link=http://e107styles.org]http://e107styles.org[/link] | [link=http://e107themes.org]http://e107themes.org[/link]

Díky, že jste si vybrali e107. Doufáme, že naplní Vaše očekávání.
(Smazat tuto zprávu můžete v administrátorské sekci.)");

define("LANINS_063", "Vítejte v e107");

define("LANINS_069", "e107 bylo úspěšně nainstalováno!

Z bezpečnostních důvodů byste měli nastavit práva pro soubor <b>e107_config.php</b> zpět na 644.

Smažte také install.php a adresář e107_install po té co kliknete na tlačítko níže.
");
define("LANINS_070", "Systému e107 se nezdařilo uložit hlavní konfiguraci na Váš server.

Ujistěte se prosím, že soubor <b>e107_config.php</b> má korektně nastavena práva.");
define("LANINS_071", "Dokončuji instalaci");

define("LANINS_072", "Uživatelské jméno pro správce");
define("LANINS_073", "To je uživatelské jméno, které použijete k přihlášení do systému. Pokud jej chcete používat také jako své jméno pro zobrazení (display name)");
define("LANINS_074", "Jméno správce pro zobrazení");
define("LANINS_075", "Jméno, které bude zobrazováno uživatelům ve Vašem profilu, diskusích a dalších oblastech. Pokud chcete použít stejné jako je jméno uživatelské, nechte toto pole prázdné");
define("LANINS_076", "Heslo správce");
define("LANINS_077", "Sem zadejte heslo správce");
define("LANINS_078", "Potvrzení hesla správce");
define("LANINS_079", "Heslo správce vložte ještě jednou, pro potvrzení");
define("LANINS_080", "Email správce");
define("LANINS_081", "Vložte svou emailovou adresu");

define("LANINS_082", "uzivatel@domena.com");

?>