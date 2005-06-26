<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/content/languages/Swedish/lan_content_help.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-06-26 11:12:23 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

define("CONTENT_ADMIN_HELP_1", "Innehållashanterarans huvudhjälp");

define("CONTENT_ADMIN_HELP_ITEM_1", "<i>Om du ännu inte lagt till några huvudvärdkategorier, vänligen gör det på sidan <a href='".e_SELF."?type.0.cat.create'>Skapa ny kategori</a>.</i><br /><br /><b>Kategori</b><br />Välj en kategori från rullgardinsmenyn för att hantera innehållet i den kategorin.<br /><br />Huvudvärdarna är visade i fetstil och har tillägget (ALLA) efter sig. Väljer du någon av dem kommer alla objekt under den huvudvärden att visas.<br /><br />För varje huvudvärd visas alla underkategorier inklusive huvudvärden själv (dessa visas alla i ren text). Väljer du någon av kategorierna visas bara objekt under den kategorin.");

define("CONTENT_ADMIN_HELP_ITEM_2", "<b>Första bokstäverna</b><br />Om flera objekt med denna bokstav finns kommer bara objekten som börjar med den valda bokstaven att visas. Väjer du 'alla' knappen kommer en lista med alla objekt i denna kategori att visas.<br /><br /><b>Detaljerad lista</b><br />Du ser en lista på alla objekt med dess ID, ikon, författare, rubrik [underrubrik] och alternativ.<br /><br /><b>Förklaring av ikoner</b><br />".CONTENT_ICON_EDIT." : Redigera objektet.<br />".CONTENT_ICON_DELETE." : Radera objektet.<br />");

define("CONTENT_ADMIN_HELP_ITEMEDIT_1", "<b>Redigeringsformulär</b><br />Här kan du redigera all information för detta objekt och spara ändringarna.<br /><br />Om du ändrar kategori för detta objekt till en annan huvudvärdkategori kommer du antagligen att vilja redigera objektet igen efter kategoribytet.<br />Detta eftersom den nya kategorin har en annan uppsättning preferenser att fylla i.");

define("CONTENT_ADMIN_HELP_ITEMCREATE_1", "<b>Kategori</b><br />Vänligen välj en kategori från valrutan som du vill skapa innehåll i.<br />");

define("CONTENT_ADMIN_HELP_ITEMCREATE_2", "Välj alltid en kategori före du fyller i de andra fälten!<br />Detta måste göras efterom varje huvudvärdkategori (och dess underkategorier) kan ha olika preferenser.<br /><br /><b>Formulär för att skapa</b><br />Här kan du nu ange all information för detta objekt och skicka in det.<br /><br />Var uppmärksam på att olika huvudvärdkategorier kan ha olika preferenser, och genom det kan ha flera fält tillgängliga för dig att fylla i.");

define("CONTENT_ADMIN_HELP_CAT_1", "<i>Denna sida visar alla tillgängliga kategorier under kategorier.</i><br /><br /><b>Detaljerad lista</b><br />Du ser en lista på alla underkategorier med deras ID, ikon, författare, kategori [underrubrik] och alternativ.<br /><br /><b>Förklaring av ikoner</b><br />".CONTENT_ICON_EDIT." : För alla kategorier kan du klicka här för att redigera en kategori.<br />".CONTENT_ICON_DELETE." : För alla kategorier kan du klicka här för att radera en kategori.<br />".CONTENT_ICON_OPTIONS." : För endast huvudkategorin (högst upp på listan) kan du klicka på denna knappen för att sätta och/eller kontrollera alla alternativ.<br />");

define("CONTENT_ADMIN_HELP_CAT_2", "<i>Denna sida låter dig skapa en ny kategori</i><br /><br />Välj alltid värdkategori innan du fyller de andra fälten!<br /><br />Detta måste göras eftersom en del unika kategoripreferenser måste lassas först i systemet.<br /><br />Som standard visas kategorisidan för att skapa en ny huvudkategori.");

define("CONTENT_ADMIN_HELP_CAT_3", "<i>Denna sidan visar redigeringsformuläret för kategorier.</i><br /><br /><b>Kategoriredigeringsformulär</b><br />Nu kan du redigera all information för denna (under)kategori och spara dina ändringar.<br /><br />Om du vill byta värdplats för denna kategori, gör det först. Efter att kategorin är rätt placerad, redigera de andra fälten.");

define("CONTENT_ADMIN_HELP_ORDER_1", "<i>Denna sida visar alla befintliga kategorier och underkategorier.</i><br /><br /><b>Detaljerad lista</b><br />Du ser kategori id och kategorinamnet. Du ser också flar alternativ för att hantera ordningen på kategorierna.<br /><br /><b>Förklaring av ikoner</b><br />".CONTENT_ICON_ORDERALL." Hantera den globala ordningen på innehållet oavsett kategori.<br />".CONTENT_ICON_ORDERCAT." Hantera ordningen på innehållet in den angivna kategorin.<br /><img src='".e_IMAGE."admin_images/up.png' alt='' /> Uppåtpilen flyttar objektet ett steg upp i ordningen.<br /><img src='".e_IMAGE."admin_images/down.png' alt='' /> Nedåtpilen flyttar objektet ett steg ner i ordningen.<br /><br /><b>Ordning</b><br />Här kan du manuellt sätta ordningen på alla kategorierna under denna värd. Du skall ändra värdena i valrutorna till ordningen du vill ha, och sedan klicka på uppdatera nedan för att spara den nya ordningen.<br />");

define("CONTENT_ADMIN_HELP_ORDER_2", "<i>Denna sida visar alla objekt från den kategori du valt.</i><br /><br /><b>Detaljerad lista</b><br />Du ser innehållets ID, författare och rubrik. Du ser också ett flertal alternativ för att ändra ordningen på objekten.<br /><br /><b>Förklaring av ikoner</b><br /><img src='".e_IMAGE."admin_images/up.png' alt='' /> Uppåtpilen flyttar objektet ett steg upp i ordningen.<br /><img src='".e_IMAGE."admin_images/down.png' alt='' /> Nedåtpilen flyttar objektet ett steg ner i ordningen.<br /><br /><b>order</b><br />Här kan du manuellt sätta ordningen på alla kategorierna under denna värd. Du skall ändra värdena i valrutorna till ordningen du vill ha, och sedan klicka på uppdatera nedan för att spara den nya ordningen.<br />");

define("CONTENT_ADMIN_HELP_ORDER_3", "<i>Denna sida visar alla objekt under den huvudvärdkategorin du valt.</i><br /><br /><b>Detaljerad lista</b><br />Du ser innehållets ID, författare och rubrik. Du ser också ett flertal alternativ för att ändra ordningen på objekten.<br /><br /><b>Förklaring av ikoner</b><br /><img src='".e_IMAGE."admin_images/up.png' alt='' /> Uppåtpilen flyttar objektet ett steg upp i ordningen.<br /><img src='".e_IMAGE."admin_images/down.png' alt='' /> Nedåtpilen flyttar objektet ett steg ner i ordningen.<br /><br /><b>order</b><br />Här kan du manuellt sätta ordningen på alla kategorierna under denna huvudvärd. Du skall ändra värdena i valrutorna till ordningen du vill ha, och sedan klicka på uppdatera nedan för att spara den nya ordningen.<br />");

define("CONTENT_ADMIN_HELP_OPTION_1", "På denna kan du välja en värdkategori att sätta alternativ för, eller så kan du välja att ändra standardpreferenserna.<br /><br /><b>Förklaring av ikoner</b><br />".CONTENT_ICON_USER." : Länk till författarens profil<br />".CONTENT_ICON_LINK." : Länk till kategorin<br />".CONTENT_ICON_OPTIONS." : Redigera alternativen<br /><br /><br />
Standardpreferenserna används bara när du skapar en ny värd. Så när du skapar en ny värd kommer dessa standardalternativ att användas. Du kan ändra dessa för att försäkra dig om att nyskapade värdar omedelbart får en standarduppsättning alternativ och värden.
<br /><br />
Varje huvudvärd har sin egen uppsättning alternativ som är unika för den specifika huvudvärdkategorin");

define("CONTENT_ADMIN_HELP_OPTION_2", "
<i>Denna sida visar vilka alternativ du kan sätta för denna värd. Varje värd har sin egen specifika uppsättning alternativ, så försäkra dig om att du sätter alla korrekt.</i><br /><br />
<b>Standardvärden</b><br />Som standard finns alla värden och är redan uppdaterade i preferenserna när du öppnar denna sidan, men ändra värdena så de passar dina behov.<br /><br />
");

define("CONTENT_ADMIN_HELP_MANAGER_1", "På denna sidan ser du en lista över alla kategorier. Du kan använda den 'Personliga innehållshanteraren' för varje kategori genom att klicka på ikonen.<br /><br /><b>Förklaring av ikoner</b><br />".CONTENT_ICON_USER." : Länk till författarens profil<br />".CONTENT_ICON_LINK." : Länk till kategorin<br />".CONTENT_ICON_CONTENTMANAGER_SMALL." : Redigera de personliga innehållshanterarna<br />");

define("CONTENT_ADMIN_HELP_MANAGER_2", "<i>På denna sidan kan du tilldela användare till den valda kategorin du klickat på</i><br /><br /><b>Personlig hanterare</b><br />Du kan tilldela användare till vissa kategorier. Genom det kan användarna hantera sina personliga innehållsobjekt inom dessa kategorier utan att besöka adminsidan (content_manager.php).<br /><br />Tilldela användare från den vänstra kolumnen genom att klicka på deras namn, du kommer att se dessa namn flyttas till den högra kolumnen. Efter att du klickat på tilldelningsknappen kommer användarna i den högra kolumnen att vara tilldelade denna kategori.");

define("CONTENT_ADMIN_HELP_SUBMIT_1", "<i>På denna sidan ser du en lista på alla objekt som sänts in av användare.</i><br /><br /><b>Detaljerad lista</b><br />Du ser en lista på innehållsobjekten med deras ID, ikon, huvudvärd, rubrik [underrubrik], författare och alternativ.<br /><br /><b>Alternativ</b><br />Du kan posta eller radera innehållsobjekt med de visade knapparna.");

?>
