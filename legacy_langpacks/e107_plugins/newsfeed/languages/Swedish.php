<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_plugins/newsfeed/languages/Swedish.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-04-19 09:33:06 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

define("NFLAN_01", "Nyhetsfl&ouml;den");
define("NFLAN_02", "Denna plugin h&auml;mtar rss fl&ouml;den fr&aring;n andra webbsajter och visar dem enligt dina inst&auml;llningar");
define("NFLAN_03", "Konfigurera nyhetsfl&ouml;den");
define("NFLAN_04", "Nyhetsfl&ouml;dens plugin har installerats. F&ouml;r att l&auml;gga till fl&ouml;den och konfigurera, g&aring; tillbaka till admins huvudsida och klicka p&aring; ikonen f&ouml;r nyhetsfl&ouml;den i plugin sektionen.");
define("NFLAN_05", "Redigera");
define("NFLAN_06", "Radera");
define("NFLAN_07", "Befintliga nyhetsfl&ouml;den");
define("NFLAN_08", "Nyhetsfl&ouml;dens f&ouml;rstasida");
define("NFLAN_09", "Skapa nyhetsfl&ouml;de");
define("NFLAN_10", "URL till rss k&auml;lla");
define("NFLAN_11", "S&ouml;kv&auml;g till bild");
define("NFLAN_12", "Aktivering");
define("NFLAN_13", "Ingenstans (inaktiv)");
define("NFLAN_14", "Endast i meny");
define("NFLAN_15", "Skapa nyhetsfl&ouml;de");
define("NFLAN_16", "Uppdatera nyhetsfl&ouml;de");
define("NFLAN_17", "ange 'default' i rutan f&ouml;r att anv&auml;nda bilden definierad i fl&ouml;det. F&ouml;r att anv&auml;nda en egen bild, ange hela s&ouml;kv&auml;gen till bilden, l&auml;mna tomt f&ouml;r ingen bild.");
define("NFLAN_18", "Uppdateringsintervall i sekunder");
define("NFLAN_19", "t.ex. 3600: fl&ouml;det uppdateras varje timma");
define("NFLAN_20", "Endast p&aring; nyhetsfl&ouml;dens huvudsida");
define("NFLAN_21", "I b&aring;de meny och p&aring; nyhetsfl&ouml;dens sida");
define("NFLAN_22", "v&auml;lj var du vill visa nyhetsfl&ouml;det");
define("NFLAN_23", "Fl&ouml;de tillagt i databasen.");
define("NFLAN_24", "N&ouml;dv&auml;ndigt f&auml;lt l&auml;mnat tomt.");
define("NFLAN_25", "Fl&ouml;de uppdaterat i databasen.");
define("NFLAN_26", "Uppdateringsintervall");
define("NFLAN_27", "Alternativ");
define("NFLAN_28", "URL");
define("NFLAN_29", "Tillg&auml;ngliga nyhetsfl&ouml;den");
define("NFLAN_30", "Fl&ouml;dets namn");
define("NFLAN_31", "Tillbaka till nyhetsfl&ouml;deslista");
define("NFLAN_32", "Inget fl&ouml;de med det identifikationsnumret kan hittas.");
define("NFLAN_33", "Datum publicerat: ");
define("NFLAN_34", "ok&auml;nt");
define("NFLAN_35", "postat av ");
define("NFLAN_36", "Beskrivning");
define("NFLAN_37", "kort beskrivning av fl&ouml;det, ange 'default' f&ouml;r att anv&auml;nda beskrivningen i fl&ouml;det");
define("NFLAN_38", "Rubriker");
define("NFLAN_39", "Detaljer");
define("NFLAN_40", "Nyhetsfl&ouml;de raderat");
define("NFLAN_41", "Inga nyhetsfl&ouml;den definierade &auml;nnu");

define("NFLAN_42", "&lt;b&gt;&#187;&lt;/b&gt; &lt;u&gt;Fl&ouml;desnamn:&lt;/u&gt;
	Namn f&ouml;r att identifiera fl&ouml;det, kan vara vad du vill.
	<br /><br />
	&lt;b&gt;&#187;&lt;/b&gt; &lt;u&gt;URL till rss fl&ouml;de:&lt;/u&gt;
	Adressen till rss fl&ouml;det
	<br /><br />
	&lt;b&gt;&#187;&lt;/b&gt; &lt;u&gt;S&ouml;kv&auml;g till bild:&lt;/u&gt;
	Om fl&ouml;det har en bild definierat i sig, ange 'default' f&ouml;r att anv&auml;nda den. F&ouml;r att anv&auml;nda en egen bild, ange hela s&ouml;kv&auml;gen till den. L&auml;mna tomt f&ouml;r att inte visa n&aring;gon bild alls.
	<br /><br />
	&lt;b&gt;&#187;&lt;/b&gt; &lt;u&gt;Beskrivning:&lt;/u&gt;
	Ange en kort beskrivning av fl&ouml;det, eller 'default' f&ouml;r att anv&auml;nda beskrivningen definierad i fl&ouml;det (om det finns n&aring;gon).
	<br /><br />
	&lt;b&gt;&#187;&lt;/b&gt; &lt;u&gt;Uppdateringsintervall i sekunder:&lt;/u&gt;
	Antal sekunder mellan uppdateringarna av fl&ouml;det, t.ex 1800: 30 minuter, 3600: en timma.
	<br /><br />
	&lt;b&gt;&#187;&lt;/b&gt; &lt;u&gt;Aktivering:&lt;/u&gt;
	Var du vill att fl&ouml;det skall visas, f&ouml;r att se fl&ouml;desmenyn m&aring;ste du aktivera nyhetsfl&ouml;desmenyn p&aring; &lt;a href='".e_ADMIN."menus.php'&gt;menysidan&lt;/a&gt;.
	<br /><br />F&ouml;r en bra exempellista &ouml;ver diverse fl&ouml;den, se &lt;a href='http://www.syndic8.com/' rel='external'&gt;syndic8.com&lt;/a&gt; eller &lt;a href='http://feedfinder.feedster.com/index.php' rel='external'&gt;feedster.com&lt;/a&gt;");
define("NFLAN_43", "Nyhetsfl&ouml;den hj&auml;lp");
define("NFLAN_44", "klicka f&ouml;r att se");

?>
