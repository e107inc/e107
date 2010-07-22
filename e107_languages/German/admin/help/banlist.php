<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvsroot/e107german/e107_0.7/e107_langpacks/e107_languages/German/admin/help/banlist.php,v $
|     $Revision: 1.2 $
|     $Date: 2010/01/30 13:46:37 $
|     $Author: lars78 $
|     $updated by: webmaster@e107cms.de (http://www.e107cms.de) $
|     $ UTF-8 encoded $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "User von Ihrer Seite verbannen";
$text = "Über dieses Menü können Sie User von Ihrer Seite verbannen.<br />
Entweder geben Sie die vollständige IP-Adresse an oder nutzen eine sogenannte Wildcard, um eine Reihe von IP-Adressen zu verbannen. Oder Sie können die E-Mail Adresse angeben, um User davon abzuhalten, sie auf Ihrer Seite zu registrieren.<br /><br />
<b>Verbannen über IP-Addresse:</b><br />
Wenn Sie z.B. die IP-Addresse 123.123.123.123 eingeben, werden die User mit der genannten IP gesperrt.<br />
Die Eingabe einer IP-Addresse mit einem oder mehreren Platzhaltern in den Endblöcken, so wie 123.123.123.* oder 214.098.*.*, wird jeden in diesem IP-Bereich daran hindern, Ihre Seite zu besuchen. 
(Beachten Sie, dass dort genau vier Gruppen von Zahlen oder Sternchen sein müssen)<br /><br />
<b>Verbanung via E-Mail Adresse</b><br />
Geben Sie die Adresse mane@email.de ein wird jeder, der die genannte E-Mail verwendet gesperrt und kann sich nicht registrieren. Das ist i.d.R. nur eine Person, bzw. ein Nutzerkreis, der Zugang zu den Registrierungsdaten hat.<br />
Geben Sie jetzt *@email.de ein, dann kann NIEMAND der seine E-Mail bei der genannten Domain hat sich registrieren! Bitte seien Sie deshalb umsichtig im Umgang mit den Sperrcodes.";
$ns -> tablerender($caption, $text);
?>
