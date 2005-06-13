<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/content/languages/Swedish/lan_content_help.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-06-13 16:20:21 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

define("CONTENT_ADMIN_HELP_ORDER_1", "
<i>Denna sida visar alla befintliga kategorier och underkategorier.</i><br /><br />
<b>Detaljerad lista</b><br />Du ser kategori id och kategorinamnet. Du ser också flar alternativ för att hantera ordningen på kategorierna.<br />
<br />
<b>Förklaring av ikoner</b><br />
".CONTENT_ICON_ORDERALL." Hantera den globala ordningen på innehållet oavsett kategori.<br />
".CONTENT_ICON_ORDERCAT." Hantera ordningen på innehållet in den angivna kategorin.<br />
<img src='".e_IMAGE."admin_images/up.png' alt='' /> Uppåtpilen flyttar objektet ett steg upp i ordningen.<br />
<img src='".e_IMAGE."admin_images/down.png' alt='' /> Nedåtpilen flyttar objektet ett steg ner i ordningen.<br />
<br />
<b>Ordning</b><br />Här kan du manuellt sätta ordningen på alla kategorierna under denna värd. Du skall ändra värdena i valrutorna till ordningen du vill ha, och sedan klicka på uppdatera nedan för att spara den nya ordningen.<br />
");
define("CONTENT_ADMIN_HELP_ORDER_2", "
<i>Denna sida visar alla objekt från den kategori du valt.</i><br /><br />
<b>Detaljerad lista</b><br />Du ser innehållets ID, författare och rubrik. Du ser också ett flertal alternativ för att ändra ordningen på objekten.<br />
<br />
<b>Förklaring av ikoner</b><br />
<img src='".e_IMAGE."admin_images/up.png' alt='' /> Uppåtpilen flyttar objektet ett steg upp i ordningen.<br />
<img src='".e_IMAGE."admin_images/down.png' alt='' /> Nedåtpilen flyttar objektet ett steg ner i ordningen.<br />
<br />
<b>order</b><br />Här kan du manuellt sätta ordningen på alla kategorierna under denna värd. Du skall ändra värdena i valrutorna till ordningen du vill ha, och sedan klicka på uppdatera nedan för att spara den nya ordningen.<br />
");
define("CONTENT_ADMIN_HELP_ORDER_3", "
<i>Denna sida visar alla objekt under den huvudvärdkategorin du valt.</i><br /><br />
<b>Detaljerad lista</b><br />Du ser innehållets ID, författare och rubrik. Du ser också ett flertal alternativ för att ändra ordningen på objekten.<br />
<br />
<b>Förklaring av ikoner</b><br />
<img src='".e_IMAGE."admin_images/up.png' alt='' /> Uppåtpilen flyttar objektet ett steg upp i ordningen.<br />
<img src='".e_IMAGE."admin_images/down.png' alt='' /> Nedåtpilen flyttar objektet ett steg ner i ordningen.<br />
<br />
<b>order</b><br />Här kan du manuellt sätta ordningen på alla kategorierna under denna huvudvärd. Du skall ändra värdena i valrutorna till ordningen du vill ha, och sedan klicka på uppdatera nedan för att spara den nya ordningen.<br />
");



define("CONTENT_ADMIN_HELP_SUBMIT_1", "
<i>På denna sidan ser du en lista på alla objekt som sänts in av användare.</i><br /><br />
<b>Detaljerad lista</b><br />Du ser en lista på innehållsobjekten med deras ID, ikon, huvudvärd, rubrik [underrubrik], författare och alternativ.<br /><br />
<b>Alternativ</b><br />Du kan posta eller radera innehållsobjekt med de visade knapparna.
");



define("CONTENT_ADMIN_HELP_CAT_1", "
<i>Denna sida visar alla tillgängliga kategorier under kategorier.</i><br /><br />
<b>Detaljerad lista</b><br />Du ser en lista på alla underkategorier med deras ID, ikon, författare, kategori [underrubrik] och alternativ.<br />
<br />
<b>Förklaring av ikoner</b><br />
".CONTENT_ICON_EDIT." : För alla kategorier kan du klicka här för att redigera en kategori.<br />
".CONTENT_ICON_DELETE." : För alla kategorier kan du klicka här för att radera en kategori.<br />
".CONTENT_ICON_OPTIONS." : För endast huvudkategorin (högst upp på listan) kan du klicka på denna knappen för att sätta och/eller kontrollera alla alternativ.<br />
");
define("CONTENT_ADMIN_HELP_CAT_2", "
".CONTENT_ICON_CONTENTMANAGER_SMALL." : (Endast sajtadmin) För varje underkategori kan du klicka på denna knappen för att hantera de andra admins personliga hanterare.<br />
<br />
<b>Personlig hanterare</b><br />Du kan tilldela admins till särskilda kategorier. Genom att göra det kan dessa admins hantera sina personliga innehållsobjekt inom dessa kategorier, utanför adminsidan (content_manager.php).
");
define("CONTENT_ADMIN_HELP_CAT_3", "
<i>Denna sida låter dig skapa en ny kategori</i><br /><br />
VÄLJ ALLTID EN VÄRDKATEGORI INNAN DU FYLLER I DE ANDRA FÄLTEN!<br /><br />
Detta måste göras eftersom en del kategoriunika prefrenser måste laddas först i systemet.
");
define("CONTENT_ADMIN_HELP_CAT_4", "
<i>Denna sida visar redigeringsfomuläret för kategorier.</i><br /><br />
<b>Kategoriredigering</b><br />Du kan här redigera all information för denna (under)kategori och spara dina ändringar.
");
define("CONTENT_ADMIN_HELP_CAT_5", "
");
define("CONTENT_ADMIN_HELP_CAT_6", "
<i>Denna sida visar de alternativ du kan ändra för denna huvudvärd. Varje huvudvärd har sin egen uppsättning alternativ, så försäkra dig om att du sätter dem alla rätt.</i><br /><br />
<b>Standardvärden</b><br />Som standard är alla värden redan inställda i preferenserna när du tittar på denna sidan, men du kan ändra alla inställningarna till dina egna värden.<br /><br />
<b>Uppdelning i åtta sektioner</b><br />Alternativen är uppdelade i åtta huvudsektioner. Du ser de olika sektionerna i den högra menyn. Du kan klicka på någon av dem för att gå till inställningarna för respektive sektion.<br /><br />
<b>Skapa</b><br />I denna sektion kan du ange alternativ för skapandet av objekt på adminsidorna i admins ände.<br /><br />
<b>Skicka in</b><br />I denna sektion specificerar du alternativen för formuläret att skicka in innehåll med.<br /><br />
<b>Sökväg och tema</b><br />I denna sektion sätter du temat för denna huvudvärd samt anger sökvägar till var du sparat bilder för denna huvudvärd.<br /><br />
<b>Generellt</b><br />I denna sektion specificerar du de generella alternativen som skall användas genom alla innehållssidorna.<br /><br />
<b>Listsidor</b><br />I denna sektion specificerar du alternativsidorna där innehållet listas.<br /><br />
<b>Kategorisidor</b><br />Här specificerar du alternativen för hur kategorisidorna skall visas.<br /><br />
<b>Innehållssidor</b><br />I denna sektion anger du alternativen för hur innehållssidan skall visas.<br /><br />
<b>Meny</b><br />Här anger du alternativen för menyn till denna huvudvärd.<br /><br />
");
define("CONTENT_ADMIN_HELP_CAT_7", "
<i>På denna sidan kan du tilldela admins till den valda kategorin du klickat på</i><br /><br />
Tilldela admin från den vänstra kolumnen genom att klicka på namnet. Du kommer att se namnen flyttas till den högra kolumnen. Efter du sedan klickat på tilldela-knappen kommer administratörerna i den högra kolumnen att vara tilldelade till denna kategori.
");

define("CONTENT_ADMIN_HELP_ITEMCREATE_1", "
<b>Kategori</b><br />Vänligen välj en kategori från valrutan som du vill skapa innehåll i.<br />
");
define("CONTENT_ADMIN_HELP_ITEMCREATE_2", "
Välj alltid en kategori före du fyller i de andra fälten!<br />
Detta måste göras efterom varje huvudvärdkategori (och dess underkategorier) kan ha olika preferenser.<br /><br />
<b>Formulär för att skapa</b><br />Här kan du nu ange all information för detta objekt och skicka in det.<br /><br />
Var uppmärksam på att olika huvudvärdkategorier kan ha olika preferenser, och genom det kan ha flera fält tillgängliga för dig att fylla i.
");


define("CONTENT_ADMIN_HELP_ITEM_1", "
<i>Om du ännu inte lagt till några huvudvärdkategorier, vänligen gör det på sidan <a href='".e_SELF."?type.0.cat.create'>Skapa ny kategori</a>.</i><br /><br />
<b>Kategori</b><br />Välj en kategori från rullgardinsmenyn för att hantera innehållet i den kategorin.<br /><br />
Huvudvärdarna är visade i fetstil och har tillägget (ALLA) efter sig. Väljer du någon av dem kommer alla objekt under den huvudvärden att visas.<br /><br />
För varje huvudvärd visas alla underkategorier inklusive huvudvärden själv (dessa visas alla i ren text). Väljer du någon av kategorierna visas bara objekt under den kategorin.
");
define("CONTENT_ADMIN_HELP_ITEM_2", "
<b>Första bokstäverna</b><br />Om flera objekt med denna bokstav finns kommer bara objekten som börjar med den valda bokstaven att visas. Väjer du 'alla' knappen kommer en lista med alla objekt i denna kategori att visas.<br /><br />
<b>Detaljerad lista</b><br />Du ser en lista på alla objekt med dess ID, ikon, författare, rubrik [underrubrik] och alternativ.<br /><br />
<b>Förklaring av ikoner</b><br />
".CONTENT_ICON_EDIT." : Redigera objektet.<br />
".CONTENT_ICON_DELETE." : Radera objektet.<br />
");


define("CONTENT_ADMIN_HELP_ITEMEDIT_1", "
<b>Redigeringsformulär</b><br />Här kan du redigera all information för detta objekt och spara ändringarna.<br /><br />
Om du ändrar kategori för detta objekt till en annan huvudvärdkategori kommer du antagligen att vilja redigera objektet igen efter kategoribytet.<br />Detta eftersom den nya kategorin har en annan uppsättning preferenser att fylla i.
");

define("CONTENT_ADMIN_HELP_1", "Innehållashanterarans huvudhjälp");


define("CONTENT_ADMIN_HELP_ITEM_LETTERS", "Nedan kan du se begynnelsebokstäverna för alla rubriker i denna kategori.<br />Genom att klicka på någon av bokstäverna visas en lista med alla objekt som börjar med den valda bokstaven. Du kan också välja ALLA för att se alla objekt i denna kategorin.");


?>