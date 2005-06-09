<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/content/languages/Dutch/lan_content_help.php,v $
|     $Revision: 1.4 $
|     $Date: 2005-06-09 21:22:10 $
|     $Author: mijnheer $
+----------------------------------------------------------------------------+
*/

define("CONTENT_ADMIN_HELP_ORDER_1", "
<i>deze pagina toont alle aanwezige categorieën en subcategorieën.</i><br /><br />
<b>detail overzicht</b><br />hier zie je de categorie id en de categorienaam. ook tref je verschillende opties aan om de volgorde van de categorieën te wijzigen.<br /><br />
<b>uitleg van de gebruikte pictogrammen</b><br />
".CONTENT_ICON_ORDERALL." beheer de algemene volgorde van de contentonderwerpen, ongeacht hun categorie.<br />
".CONTENT_ICON_ORDERCAT." beheer de volgorde van de content onderwerpen in de betreffende categorie.<br />
<img src='".e_IMAGE."admin_images/up.png' alt='' /> de omhoog knop laat je een content onderwerp één positie omhoog verplaatsen.<br />
<img src='".e_IMAGE."admin_images/down.png' alt='' /> de omlaag knop laat je een content onderwerp één positie omlaag plaatsen.<br />
<br />
<b>volgorde</b><br />hier kun je handmatig de volgorde van alle categorieën in elke ouder bepalen. Je moet de waarde in de selectievelden wijzigen in de gewenste waarde en daarna druk je op de Bijwerken knop hieronder om de nieuwe volgorde vast te leggen.<br />");
define("CONTENT_ADMIN_HELP_ORDER_2", "<i>deze pagina toont alle content onderwerpen in de geselecteerde categorie.</i><br /><br />
<b>detail overzicht</b><br />je ziet het content id, de content auteur en de content kop. ook staan hier verschillende opties om de volgorde van de content onderwerpen te wijzigen.<br />
<br /><b>uitleg over de gebruikte pictogrammen</b><br />
<img src='".e_IMAGE."admin_images/up.png' alt='' /> de omhoog knop laat je een content onderwerp één positie omhoog verplaatsen.<br />
<img src='".e_IMAGE."admin_images/down.png' alt='' /> de omlaag knop laat je een content onderwerp één positie omlaag plaatsen.<br />
<br /><b>volgorde</b><br />hier kun je handmatig de volgorde van alle categorieën in elke ouder bepalen. Je moet de waarde in de selectievelden wijzigen in de gewenste waarde en daarna druk je op de Bijwerken knop hieronder om de nieuwe volgorde vast te leggen.<br />");
define("CONTENT_ADMIN_HELP_ORDER_3", "
<i>deze pagina toont alle content onderwerpen in de geselecteerde oudercategorie.</i><br /><br />
<b>detail overzicht</b><br />je ziet het content id, de content auteur en de content kop. ook staan hier verschillende opties om de volgorde van de content onderwerpen te wijzigen.<br />
<br /><b>uitleg over de gebruikte pictogrammen</b><br />
<img src='".e_IMAGE."admin_images/up.png' alt='' /> de omhoog knop laat je een content onderwerp één positie omhoog verplaatsen.<br />
<img src='".e_IMAGE."admin_images/down.png' alt='' /> de omlaag knop laat je een content onderwerp één positie omlaag verplaatsen.<br />
<br /><b>volgorde</b><br />hier kun je handmatig de volgorde van alle categorieën in elke ouder bepalen. Je moet de waarde in de selectievelden wijzigen in de gewenste waarde en daarna druk je op de Bijwerken knop hieronder om de nieuwe volgorde vast te leggen.<br />");

define("CONTENT_ADMIN_HELP_SUBMIT_1", "<i>Op deze pagina zie je een overzicht van alle door gebruikers aangemelde content onderwerpen.</i><br /><br />
<b>detail overzicht</b><br />Je ziet hier een overzicht van deze content onderwerpen met hun id, pictogram, hoofd ouder, kop [onderkop], auteur en opties.<br /><br /><b>opties</b><br />je kunt een content onderwerp met behulp van de knoppen plaatsen of verwijderen.");


define("CONTENT_ADMIN_HELP_CAT_1", "<i>deze pagina toont alle aanwezige categorieën en subcategorieën.</i><br /><br />
<b>detail overzicht</b><br />Je ziet een overzicht met alle subcategorieën met hun id, pictogram, auteur, categorie [onderkop] en opties.<br >
<br />
<b>uitleg over de gebruikte pictogrammen</b><br />
".CONTENT_ICON_EDIT." : voor alle categorieën kun je deze knop gebruiken om de categorie wilt bewerken.<br />
".CONTENT_ICON_DELETE." : voor alle categorieën kun je deze knop gebruiken om de categorie wilt verwijderen.<br />
".CONTENT_ICON_OPTIONS." : alleen voor de hoofdcategorie (bovenan de lijst) kun je de knop gebruiken om alle opties in te stellen en beheren.<br />");
define("CONTENT_ADMIN_HELP_CAT_2", "".CONTENT_ICON_CONTENTMANAGER_SMALL." : (alleen hoofdbeheerder) voor elke subcategorie kun je via de knop het Persoonlijke Beheer voor andere beheerders regelen.<br />
<br /><b>persoonlijke beheerder</b><br />je kunt beheerders toewijzen aan bepaalde categorieën. Hierdoor kunnen deze beheerders hun persoonlijke content voor deze categorieën beheren zonder het beheerscherm te moeten gebruiken (content_manager.php).");
define("CONTENT_ADMIN_HELP_CAT_3", "<i>op deze pagina kun je een nieuwe categorie maken</i><br /><br />
KIES ALTIJD EERST DE OUDERCATEGORIE VOORDAT JE DE ANDERE VELDEN INVULT !<br /><br />
Dit moet je doen omdat sommige unieke categorieinformatie eerst in het formulier moet worden geladen.");
define("CONTENT_ADMIN_HELP_CAT_4", "<i>deze pagina toont het formulier om categorieën te bewerken.</i><br /><br />
<b>bewerk categorie formulier</b><br />je kunt nu alle gegevens voor deze (sub)categorie bewerken en de wijzigingen aanmelden.");
define("CONTENT_ADMIN_HELP_CAT_5", "");
define("CONTENT_ADMIN_HELP_CAT_6", "<i>deze pagina toont de opties die je voor de hoofdouder in kunt stellen. Iedere hoofdouder heeft een eigen set specifieke opties, let er dus op om ze allemaal goed in te stellen.</i><br /><br />
<b>standaardwaarden</b><br />By default all values are present and already updated in the preferences when you view this page, but change any setting to your own standards.<br /><br />
<b>verdeling in acht secties</b><br />the options are divided into eight main sections. You see the different section in the right menu. you can click on them to go to the specific set of options for that section.<br /><br />
<b>creëren</b><br />in this section you can specify options for the creation of content items on the admin pages on the admins end.<br /><br />
<b>aanmelden</b><br />in this section you can specify options for the submit form of content items.<br /><br />
<b>pad en thema</b><br />in this section you can set a theme for this main parent, and provide path locations to where you have stored your images for this main parent.<br /><br /><b>general</b><br />in this section you can specify general options to use throughout all the content pages.<br /><br />
<b>overzicht pagina's</b><br />in this section you can specify options pages, where content items are listed.<br /><br />
<b>categoriepagina's</b><br />in this section you can specify options how to show the category pages.<br /><br />
<b>content pagina's</b><br />in this section you can specify options how to show the content item page.<br /><br />
<b>menu</b><br />in this section you can specify options for the menu of this main parent.<br /><br />
");
define("CONTENT_ADMIN_HELP_CAT_7", "<i>on this page you can assign admins to the selected category you have clicked</i><br /><br />
Assign admin from the left colomn by clicking their name. you will see these names move to the right colomn. After clicking the assign button the admins in the right colomn are assigned to this category.");

define("CONTENT_ADMIN_HELP_ITEMCREATE_1", "<b>categorie</b><br />please select a category from the select box to create your content item for.<br />");
define("CONTENT_ADMIN_HELP_ITEMCREATE_2", "always select a category before you fill in other fields !<br />
this needs to be done, because each main parent category (and subcategories in it) can have different preferences.<br /><br />
<b>creation form</b><br />you can now provide all information for this content item and submit it.<br /><br />
Be aware of the fact that diffenent main parent categories can have a different set of preferences, and therefore can have more fields available for you to fill in.");

define("CONTENT_ADMIN_HELP_ITEM_1", "<i>if you have not yet added main parent categories, please do so at the <a href='".e_SELF."?type.0.cat.create'>Creëren nieuwe categorie</a> page.</i><br /><br />
<b>categorie</b><br />select a category from the pulldown menu to manage content for that category.<br /><br />
the main parents are shown in bold and have the (ALL) extenstion behind them. selecting one of these will show all items from this main parent.<br /><br />
for each main parent all the subcategories are shown including the main parent category itself (these are all shown in plain text). Selecting on of these categories will shown all items from that category only.");
define("CONTENT_ADMIN_HELP_ITEM_2", "<b>eerste letters</b><br />if multiple content item starting letters of the content_heading are present, you will see buttons to select only those content items starting with that letter. Selecting the 'all' button will show a list containing all content items in this category.<br /><br />
<b>detail overzicht</b><br />You see a list of all content items with their id, icon, author, heading [subheading] and options.<br /><br />
<b>uitleg van de gebruikte pictogrammen</b><br />
".CONTENT_ICON_EDIT." : bewerk het contentonderwerp.<br />".CONTENT_ICON_DELETE." : verwijder het contentonderwerp.<br />");

define("CONTENT_ADMIN_HELP_ITEMEDIT_1", "<b>bewerk formulier</b><br />you can now edit all information for this content item and submit your changes.<br /><br />
if you change the category of this content item to another main parent category, you probably want to re-edit this item after the category change.<br />Because you change the main parent category other preferences may be available to fill in.");

define("CONTENT_ADMIN_HELP_1", "Contentbeheer hulp");

define("CONTENT_ADMIN_HELP_ITEM_LETTERS", "Below you see the distinct letters of the content heading for all items in this category.<br />By clicking on one of the letters you will see a list of all items starting with the selected letter. You can also choose the ALL button to display all items in this category.");


define("CONTENT_ADMIN_HELP_OPTION_1", "On this page you can select a main parent category to set options for, or you can choose to edit the default preferences.<br /><br /><b>uitleg van de gebruikte pictogrammen</b><br />".CONTENT_ICON_USER." : link naar het auteursprofiel<br />".CONTENT_ICON_LINK." : link naar de categorie<br />".CONTENT_ICON_OPTIONS." : bewerk de opties<br />");
define("CONTENT_ADMIN_HELP_OPTION_2", "
define("CONTENT_ADMIN_HELP_MANAGER_1", "On this page you see a list of all categories. You can manage the 'personal content manager' for each category by clicking the icon.<br /><br /><b>uitleg van de gebruikte pictogrammen</b><br />".CONTENT_ICON_USER." : link naar het auteursprofiel<br />".CONTENT_ICON_LINK." : link naar de categorie<br />".CONTENT_ICON_CONTENTMANAGER_SMALL." : bewerk de persoonlijke content beheerders<br />");
define("CONTENT_ADMIN_HELP_MANAGER_2", "<i>on this page you can assign users to the selected category you have clicked</i><br /><br /><b>personal manager</b><br />you can assign users to certain categories. In doing so, these users can manage their personal content items within these categories from outside of the admin page (content_manager.php).<br /><br />Assign users from the left colomn by clicking their name. you will see these names move to the right colomn. After clicking the assign button the users in the right colomn are assigned to this category.");

?>