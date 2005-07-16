<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/content/languages/Danish/lan_content_help.php,v $
|     $Revision: 1.4 $
|     $Date: 2005-07-16 12:58:09 $
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

?>