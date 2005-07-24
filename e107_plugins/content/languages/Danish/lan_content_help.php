<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/content/languages/Danish/lan_content_help.php,v $
|     $Revision: 1.7 $
|     $Date: 2005-07-24 11:39:08 $
|     $Author: e107dk $
+----------------------------------------------------------------------------+
*/

define("CONTENT_ADMIN_HELP_1", "Indholds Håndtering Hjælp Område");

define("CONTENT_ADMIN_HELP_ITEM_1", "<i>hvis du endnu ikke har opretet hoved forælder kategorier, så gør det på <a href='".e_SELF."?cat.create'>Opret Ny Kategori</a> siden.</i><br /><br /><b>kategori</b><br />vælg en kategori fra rullemenuen for at håndtere indhold for den kategori.<br /><br />Ved at vælge en hoved forælder fra rullemenuen vises alle indholds emner i den hoved kategori.<br />Ved at vælge en under kategori vises kun indholdet i den valgte underkategori.<br /><br />Du kan også bruge menuen til højre til at vise alle indholds emner for en bestemt kategori.");

define("CONTENT_ADMIN_HELP_ITEM_2", "<b>første bogstaver</b><br />hvis flere indholds emner starter med bogstaver af content_heading er til stede, vil du se knapper til at vælge kun de indholds emner startende med det bogstav. Ved valg af 'alle' knappen vises en liste indeholdende alle indholds emner i den kategori.<br /><br /><b>detalje liste</b><br />Du ser en liste af alle indholds emner med deres id, ikon, forfatter, overskrift [under overskrift] og indstillinger.<br /><br /><b>forklaring af ikoner</b><br />".CONTENT_ICON_USER." : link til forfatter profil<br />".CONTENT_ICON_LINK." : link til indholds emnet<br />".CONTENT_ICON_EDIT." : rediger indholds emnet<br />".CONTENT_ICON_DELETE." : slet indholds emnet<br />");

define("CONTENT_ADMIN_HELP_ITEMEDIT_1", "<b>rediger formular</b><br />du kan nu redigere alle informationerne for dette indholds emne og tilføje dine ændringer.<br /><br />Hvis du behøver at ændre kategorien for dette indholds emne, så gør venligst dette først. Efter du har valgt den korrekte kategori, ændrer eller opret evt. felter der er tilstede, før du kan tilføje ændringerne.");

define("CONTENT_ADMIN_HELP_ITEMCREATE_1", "<b>kategori</b><br />vælg venligst en kategori fra boksen for at oprette dit indholds emne for.<br />");

define("CONTENT_ADMIN_HELP_ITEMCREATE_2", "<b>oprettelses formular</b><br />du kan levere alle informationer til dette indholds emne og oprette det.<br /><br />Vær opmærksom på at forskellige hoved forælder kategorier kan have hver deres sæt indstillinger; forskellige felter kan være tilgængelige for dig at udfylde. Derfor er du altid nød til at vælge en kategori før du udfylder andre felter !");

define("CONTENT_ADMIN_HELP_CAT_1", "<i>denne side viser alle kategorier og underkategorier der er tilstede.</i><br /><br /><b>detaljret liste</b><br />Du ser en liste over alle underkategorier med deres id, ikon, forfatter, kategori [underoverskrift] og egenskaber.<br /><br /><b>forklaring af ikoner</b><br />".CONTENT_ICON_USER." : link til forfatterens profil<br />".CONTENT_ICON_LINK." : link til kategorien<br />".CONTENT_ICON_EDIT." : rediger kategorien<br />".CONTENT_ICON_DELETE." : slet kategorien<br />");

define("CONTENT_ADMIN_HELP_CAT_2", "<i>denne side tillader at oprette en ny kategori</i><br /><br />Vælg altid en forælder kategori før du udfylder de andre felter !<br /><br />Dette skal gøres, fordi nogle unikke kategori egenskaber skal indlæses i systemet.<br /><br />Som standard vises kategori siden for at oprette en ny hovedkategori.");

define("CONTENT_ADMIN_HELP_CAT_3", "<i>denne side viser rediger kategori formularen.</i><br /><br /><b>rediger kategori formular</b><br />du kan nu redigere alle informationer for denne (under)kategori og tilføje dine ændringer.<br /><br />Hvis du ønsker at ændre forælder beliggenhed for denne kategori, så gør venligst dette først. Efter du har indstillet den kategori rediger alle andre felter.");

define("CONTENT_ADMIN_HELP_ORDER_1", "<i>denne side viser alle kategorier og underkategorier der er tilstede.</i><br /><br /><b>detaljeret liste</b><br />du ser kategori id og kategori navn. du ser også adskillige muligheder til at håndtere sorteringen af kategorierne.<br /><br /><b>forklaring af ikoner</b><br />".CONTENT_ICON_USER." : link til forfatter profilen<br />".CONTENT_ICON_LINK." : link til kategorien<br />".CONTENT_ICON_ORDERALL." : håndter den generelle sortering af indhold uanset kategori.<br />".CONTENT_ICON_ORDERCAT." : håndter sorteringen af indholds emner i den enkelte kategori.<br />".CONTENT_ICON_ORDER_UP." : op knappen tillader dig at flytte et indholds emne en plads op i ordnen.<br />".CONTENT_ICON_ORDER_DOWN." : ned knappen lader dig at flytte et indholds emne en plads ned i ordnen.<br /><br /><b>sortering</b><br />her kan du manuelt indstille ordnen af alle kategorier i denne forælder. Du skal ændre værdierne i boksene for den orden du ønsker og så trykke på opdater knappen nedenfor for at gemme den nye sortering.<br />");

define("CONTENT_ADMIN_HELP_ORDER_2", "<i>denne side viser alle indholds emner fra den kategori du har valgt.</i><br /><br /><b>detaljeret liste</b><br />du ser indholds id, indholds forfatter og indholds overskrift. du ser også adskillige muligheder til at håndtere sorteringen af indholds emnerne.<br /><br /><b>forklaring af ikoner</b><br />".CONTENT_ICON_USER." : link til forfatter profilen<br />".CONTENT_ICON_LINK." : link til indholds emne<br />".CONTENT_ICON_ORDER_UP." : op knappen lader dig flytte et indholds emne en plads op i ordnen.<br />".CONTENT_ICON_ORDER_DOWN." : ned knappen lader dig flytte et indholds emne en plads ned i ordnen.<br /><br /><b>sortering</b><br />her kan du manuelt indstille ordnen af alle kategorier i denne forælder. Du skal ændre værdierne i boksene for den orden du ønsker og så trykke på opdater knappen nedenfor for at gemme den nye sortering.<br />");

define("CONTENT_ADMIN_HELP_ORDER_3", "<i>denne side viser alle indholds emner fra den hoved kategori forælder du har valgt.</i><br /><br /><b>detaljeret liste</b><br />du ser indholds id, indholds forfatter og indholds overskrift. du ser også adskillige muligheder til at håndtere sorteringen af indholds emnerne.<br /><br /><b>forklaring af ikoner</b><br />".CONTENT_ICON_USER." : link til forfatter profilen<br />".CONTENT_ICON_LINK." : link til indholds emne<br />".CONTENT_ICON_ORDER_UP." : op knappen lader dig flytte et indholds emne en plads op i ordnen.<br />".CONTENT_ICON_ORDER_DOWN." : ned knappen lader dig flytte et indholds emne en plads ned i ordnen.<br /><br /><b>sortering</b><br />her kan du manuelt indstille ordnen af alle kategorier i hver forælder. Du skal ændre værdierne i boksene for den orden du ønsker og så trykke på opdater knappen nedenfor for at gemme den nye sortering.<br />");

define("CONTENT_ADMIN_HELP_OPTION_1", "På denne side kan du vælge en hoved kategori forælder til at indstille egenskaber for, eller du kan vælge at redigere standard indstillingerne.<br /><br /><b>forklaring af ikoner</b><br />".CONTENT_ICON_USER." : link til forfatter profilen<br />".CONTENT_ICON_LINK." : link til kategorien<br />".CONTENT_ICON_OPTIONS." : rediger egenskaber<br /><br /><br />
Standard indstillingerne bruges kun når du opretter en ny hoved forælder. Så når du opretter en hoved forælder vil disse standard indstillinger blive gemt. Du kan ændre disse for at sikre at nyligt oprettede hoved forældre allerede har et bestemt sæt muligheder tilstede.
<br /><br />
Hver hoved forælder har sit eget sæt indstillinger, der er unikke til den bestemte hoved kategori forælder");


//define("CONTENT_ADMIN_HELP_OPTION_2", "<i>this page shows the options you can set for this main parent. Each main parent has their own specific set of options, so be sure to set them all correctly.</i><br /><br />");
//<b>default values</b><br />By default all values are present and already updated in the preferences when you view this page, but change any setting to your own standards.<br /><br />
define("CONTENT_ADMIN_HELP_MANAGER_1", "På denne side kan du se en liste over alle kategorier. Du kan håndtere 'personlig indholds håndtering' for hver kategori ved at klikke på ikonet.<br /><br /><b>forklaring af ikoner</b><br />".CONTENT_ICON_USER." : link til forfatter profilen<br />".CONTENT_ICON_LINK." : link til kategorien<br />".CONTENT_ICON_CONTENTMANAGER_SMALL." : rediger personlig indholds håndtering<br />");

define("CONTENT_ADMIN_HELP_MANAGER_2", "<i>på denne side kan du tildele brugere til den kategori du har klikket på</i><br /><br /><b>personlig håndtering</b><br />du kan tildele brugere til bestemte kategorier. Ved at gøre detteo, kan disse brugere håndtere deres personlige indholds emner inden for disse kategorier uden for admin siden (content_manager.php).<br /><br />Tildel brugere fra venstre kolonne ved at klikke på deres navn. du vil se nevnet flyttes til den højre kolonne. Efter du klikker på tildel knappen vil brugerne i den højre kolonne være tildelt til denne kategori.");

define("CONTENT_ADMIN_HELP_SUBMIT_1", "<i>På denne side kan du se en liste over alle indholds emner der er tilføjet af brugere.</i><br /><br /><b>detaljeret liste</b><br />Du ser en liste over disse indholds emner med deres id, ikon, hoved forælder, overskrift [underoverskrift], forfatter og egenskaber.<br /><br /><b>egenskaber</b><br />du kan oprette eller slette et indholds emne ved hjælp af de viste knapper.");



define("CONTENT_ADMIN_HELP_OPTION_DIV_1", "denne side lader dig indstille egenskaber for admin opret emne siden.<br /><br />Du kan definere hvilke sektioner der er tilgængelige når en admin (eller personlig indholds bestyrer) opretter et nyt indholds emne<br /><br /><b>specielle data tags</b><br />du kan tillade en bruger eller admin at tilføje yderligere felter til indholds emnet ved hjælp af disse specielle data tags. Disse ekstra felter er tomme nøgle=>værdi par. For eksempel: kan du tilføje en nøgle for 'fotograf' og her levere værdi felt med 'alle fotos af mig'. Både nøgle og værdi felterne er tomme tekst felter der vil være til stede i opret formularen.<br /><br /><b>forudindstilte data tags</b><br />bortset fra de specielle data tags, du kan bestemme forudindstillede data tags. Forskellen er i forudindstillede data tags, er nøglen allerede givet og brugeren skal kun levere værdi feltet for det forudindstillede. I samme eksempel som 'fotograf' ovenfor kan forud defineres, og brugeren skal levere 'alle fotos af mig'. Du kan vælge element typen ved at vælge egenskaben i valgboksen. I popup vinduet, kan du levere alle de informationer for det forudindstillede data tag.<br />");

define("CONTENT_ADMIN_HELP_OPTION_DIV_2", "Tilføj egenskaberne har effekt på bruger tilføj formularen for indholds emner.<br /><br />Du kan definere hvilke sektioner der er tilgængelige for en bruger ved tilføjelsen af et indholds emne.<br /><br />".CONTENT_ADMIN_OPT_LAN_11.":<br />".CONTENT_ADMIN_OPT_LAN_12."");

define("CONTENT_ADMIN_HELP_OPTION_DIV_3", "I Sti og Tema Egenskaberne kan du definere hvor billeder og filer gemmes.<br /><br />du kan definere hvilket tema der skal bruges af denne hoved forælder. Du kan oprette yderligere temaer ved kopiere (og omdøbe) hele 'default' mappen i din templates mappe.<br /><br />Du kan definere et standard layout skema for nye indholds emner. Du kan oprette nye layout skemaer ved at oprette en content_content_template_XXX.php fil i din 'templates/default' mappe. Disse layouts kan bruges til at give hvert indholds emne i denne hoved forælder et anderledes layout.<br /><br />");

define("CONTENT_ADMIN_HELP_OPTION_DIV_4", "De Generelle Egenskaber er indstillinger der bruges gennem alle indholds siderne fra indholds håndterings plugin.");

define("CONTENT_ADMIN_HELP_OPTION_DIV_5", "Disse egenskaber har effekt på det Personlge Indholds Håndtering område på indholds håndtering admin området.<br /><br />".CONTENT_ADMIN_OPT_LAN_63."");

define("CONTENT_ADMIN_HELP_OPTION_DIV_6", "Disse egenskaber bruges i Menuen for denne hoved forælder hvis du har aktiveret menuen.<br /><br />".CONTENT_ADMIN_OPT_LAN_68."<br /><br />".CONTENT_ADMIN_OPT_LAN_118.":<br />".CONTENT_ADMIN_OPT_LAN_119."<br /><br />");

define("CONTENT_ADMIN_HELP_OPTION_DIV_7", "Indholds Emne Fremvisning egenskaber har effekt på den lille fremvisning der gives for et indholds emne.<br /><br />Denne fremvisning gives på flere sider, lige som seneste nyt siden, visningen emner i kategori side og visninger af emner af forfatter side.<br /><br />".CONTENT_ADMIN_OPT_LAN_68."");

define("CONTENT_ADMIN_HELP_OPTION_DIV_8", "Kategori Sider viser informationer om indholds kategorier i denne hoved forælder.<br /><br />Der er to udprægede områder tilstede:<br /><br />alle kategorier side:<br />denne side viser alle kategorierne i denne hoved forælder<br /><br />vis kategori side:<br />denne side viser kategori emne, valgfrit underkategorierne i den kategori og indholds emner i den kategori eller disse kategorier<br />");

define("CONTENT_ADMIN_HELP_OPTION_DIV_9", "Indholds Siden viser Indholds Emne.<br /><br />du kan definere hvilke sektioner der skal vises ved at markere/afmarkere boksene.<br /><br />du kan vise emailadressen på en ikke-medlem forfatter.<br /><br />du kan tilsidesætte email/udskriv/pdf ikoner, bedømmelses systemet og kommentarerne.<br /><br />".CONTENT_ADMIN_OPT_LAN_74."");

define("CONTENT_ADMIN_HELP_OPTION_DIV_10", "Forfatter Siden viser en liste med alle unikke forfattere af indholds emnerne i denne hoved forælder.<br /><br />du kan definere hvilke sektioner der skal vises ved at markere/afmarkere boksene.<br /><br />Du kan begrænse antal emner pr. side.<br />");

define("CONTENT_ADMIN_HELP_OPTION_DIV_11", "Arkiv Siden viser alle indholds emner i hoved forælderen.<br /><br />du kan definere hvilke sektioner der skal vises ved at markere/afmarkere boksene.<br /><br />du kan vise emailadressen på en ikke-medlem forfatter.<br /><br />Du kan begrænse antal emner der skal vises pr. side.<br /><br />".CONTENT_ADMIN_OPT_LAN_66."<br /><br />".CONTENT_ADMIN_OPT_LAN_68."");

define("CONTENT_ADMIN_HELP_OPTION_DIV_12", "Top Bedømt Siden viser alle indholds emner der er blevet bedømt af  brugere.<br /><br />Du kan vælge de sektioner der skal vises ved at markere boksene.<br /><br />Du kan også definere om emailadressen til en ikke-medlem forfatter skal vises.");

define("CONTENT_ADMIN_HELP_OPTION_DIV_13", "Top Score Siden viser alle indholds emner der har fået en score af forfatteren til indholds emnet.<br /><br />You can choose the sections to display by checking the boxes.<br /><br />Also you can define if the emailaddress of a non-member author will be displayed.");

?>