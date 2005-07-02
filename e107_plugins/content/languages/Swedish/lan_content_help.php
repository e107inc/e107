<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/content/languages/Swedish/lan_content_help.php,v $
|     $Revision: 1.4 $
|     $Date: 2005-07-02 10:35:04 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

//define("CONTENT_ADMIN_HELP_OPTION_2", "<i>Denna sida visar de alternativ du kan sätta för denna värd. Varje värd har sin egen uppsättning alternativ, så försäkra dig om att alla sätts korrekt.</i><br /><br />");
//<b>Standardvärden</b><br />Som standard finns alla värden och är uppdaterade i preferenser när du tittar på denna sidan, men du måste gå genom och ändra alla värden efter dina behov.<br /><br />
define("CONTENT_ADMIN_HELP_MANAGER_1", "På denna sida ser du en lista över alla kategorier. Du kan hantera den 'Personliga innehållshanteraren' för varje kategori genom att klicka på ikonen.<br /><br /><b>Förklaring av ikoner</b><br />".CONTENT_ICON_USER." : Länk till författarens profil<br />".CONTENT_ICON_LINK." : Länk till kategorin<br />".CONTENT_ICON_CONTENTMANAGER_SMALL." : Redigera personliga innehållshanterare<br />");

define("CONTENT_ADMIN_HELP_MANAGER_2", "<i>På denna sida kan du tilldela användare till den valda kategorin du har klickat</i><br /><br /><b>Personlig hanterare</b><br />Du kan tilldela användare till särskilda kategorier. Genom att göra det kan dessa användare hantera sina egna personliga innehållsobjekt utanför adminsektionen (content_manager.php).<br /><br />Tilldela användare genom att klicka på deras namn i den vänstra kolumnen, då ser du att namnet flyttas till den högra kolumnen. Efter att du klickat på tilldelningsknappen kommer användarna i den högra kolumnen att tilldelas rättigheten i denna kategorin.");

define("CONTENT_ADMIN_HELP_SUBMIT_1", "<i>På denna sidan ser du allt innehåll som sänts in av användare.</i><br /><br /><b>Detaljerad lista</b><br />Du ser en lista med dessa innehållsobjekt och deras id, ikon, värd, rubrik [underrubrik], författare och alternativ.<br /><br /><b>Alternativ</b><br />Du kan posta eller radera ett objekt med de visade knapparna.");



define("CONTENT_ADMIN_HELP_OPTION_DIV_1", "Denna sidan låter dig sätta alternativen för admins sida att skapa objekt.<br /><br />Du kan ange vilka sektioner som skall vara tillgängliga när admin (eller personlig hanterare) skapar ett nytt innehållsobjekt<br /><br /><b>Egna datataggar</b><br />Du kan tillåta att användare eller admin kan lägga till egna fält till objektet genom dessa egna datataggar. De egna fälten är tomma nyckel=>värde par. T.ex: Du kan lägga till ett nyckelfält för 'fotograf' och ange ett värde med 'alla foton av mig'. Båda nyckel och värde fält är tomma textrutor som kommer att visas i formuläret för att skapa innehåll.<br /><br /><b>Förinställda datataggar</b><br />Utöver egna datataggar kan du även skapa förinställda datataggar. Skillnaden är att i de förinställda datataggarna är nyckelvärdet redan angivet och användaren behöver bara fylla i värdefältet för taggen. I samma exempel som ovan kan 'fotografr' vara förinställt och användaren måste ange 'alla foton av mig'. Du kan välja elementtyp genom att välja i valrutan. I popup-fönstret kan du ange all information för den förinställda datataggen.<br />");

define("CONTENT_ADMIN_HELP_OPTION_DIV_2", "Skicka in alternativ har effekt på användarens formulär för att skicka in innehåll.<br /><br />Du kan ange vilka delar som skall vara tillgängliga för en användare som sänder in innehåll.<br /><br />".CONTENT_ADMIN_OPT_LAN_11.":<br />".CONTENT_ADMIN_OPT_LAN_12."");

define("CONTENT_ADMIN_HELP_OPTION_DIV_3", "I sökväg och tema alternativen anger du var bilder och filer skall sparas.<br /><br />Du kan också ange vilket tema som skall användas av denna värd. Du kan skapa ytterligare teman genom att kopiera (och byta namn) på hela 'default' katalogen i ditt mallbibliotek.<br /><br />Du kan ange ett standard layoutschema för nya innehållsobjekt. Du kan skapa nya layoutscheman genom att skapa en content_content_template_XXX.php fil i din 'templates/default' folder. Dessa layouter kan användas för att ge varje innehållsobjekt under denna värd egna layouter.<br /><br />");

define("CONTENT_ADMIN_HELP_OPTION_DIV_4", "De generella alternativen är de alternativ som används genom allt innehåll i hela innehållspluginen.");

define("CONTENT_ADMIN_HELP_OPTION_DIV_5", "TDessa alternativ har effekt på den personliga innehållshanteraren i innehållshanteringens admin area.<br /><br />".CONTENT_ADMIN_OPT_LAN_63."");

define("CONTENT_ADMIN_HELP_OPTION_DIV_6", "Dessa alternativ används i menyn för denna värd om du aktiverat menyn.<br /><br />".CONTENT_ADMIN_OPT_LAN_68."<br /><br />".CONTENT_ADMIN_OPT_LAN_118.":<br />".CONTENT_ADMIN_OPT_LAN_119."<br /><br />");

define("CONTENT_ADMIN_HELP_OPTION_DIV_7", "Förhandsgranskning av innehållsobjekt har effekt på en lite förhandsgranskning som är möjlig för varje objekt.<br /><br />Denna förhandsgranskning finns på ett flertal sidor som tidiagare sida, visa objekt i kategori sida och visa objekt från författares sida.<br /><br />".CONTENT_ADMIN_OPT_LAN_68."");

define("CONTENT_ADMIN_HELP_OPTION_DIV_8", "Kategorisidorna visar information om innehållskategorierna i denna värd.<br /><br />Det finns två distinkta areor närvarande:<br /><br />All kategorier:<br />Denna sida visar alla kategorier under denna värd<br /><br />Visa kategori sida:<br />Denna sida visar kategorin, eventuella underkategorier till kategorin och objekten i kategorin/underkategorierna<br />");

define("CONTENT_ADMIN_HELP_OPTION_DIV_9", "Innehållssidan visar innehållsobjektet.<br /><br />Du kan ange vilka sektioner att visa genom att markera/avmarkera i rutorna.<br /><br />Du kan visa e-postadressen till en författare som inte är medlem här.<br /><br />Du kan åsidosätta e-post/skriv ut/pdf ikonerna, betygssystemet och kommentarerna.<br /><br />".CONTENT_ADMIN_OPT_LAN_74."");

define("CONTENT_ADMIN_HELP_OPTION_DIV_10", "Författarsidan visar en lista med alla författare av innehåll under denna värd.<br /><br />Du kan ange vilka sektioner att visa genom att markera/avmarkera i rutorna.<br /><br />Du kan begränsa antal objekt att visa per sida.<br />");

define("CONTENT_ADMIN_HELP_OPTION_DIV_11", "Arkivsidan visar allt innehåll under denna värd.<br /><br />Du kan ange vilka sektioner att visa genom att markera/avmarkera i rutorna.<br /><br />Du kan ange om e-postadressen till en författare som inte är medlem här skall visas.<br /><br />Du kan begränsa antal objekt att visa per sida.<br /><br />".CONTENT_ADMIN_OPT_LAN_66."<br /><br />".CONTENT_ADMIN_OPT_LAN_68."");

define("CONTENT_ADMIN_HELP_OPTION_DIV_12", "Toppbetygssidan visa allt innehåll som blivit betygssatt av användare.<br /><br />Du kan ange vilka sektioner att visa genom att markera/avmarkera i rutorna.<br /><br />Du kan ange om e-postadressen till en författare som inte är medlem här skall visas.");

define("CONTENT_ADMIN_HELP_OPTION_DIV_13", "Topprankningssidan visar allt innhåll som har fått en rankning satt av författaren till innehållet.<br /><br />Du kan ange vilka sektioner att visa genom att markera/avmarkera i rutorna.<br /><br />Du kan ange om e-postadressen till en författare som inte är medlem här skall visas.");

?>
