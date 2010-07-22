<?php
/*
 * Dutch Language File for the
 *   e107 website system (http://e107.org).
 * Released under the terms and conditions of the
 *   GNU General Public License v3 (http://gnu.org).
 * $HeadURL$
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit(); }

$text = "In dit scherm kun je de bestanden in de map e107_files/ beheren. Als je bij het uploaden foutmeldingen over onvoldoende rechten ziet, dan is de map niet beschrijfbaar. Zorg ervoor dat de map de juiste schrijfrechten heeft (CHMOD is systeem afhankelijk, hou het zo laag mogelijk). Voor meer informatie en de mogelijkheden wat betreft CHMOD verwijzen wij je naar je hostings provider.";
$ns -> tablerender("Bestandsbeheer Hulp", $text);

?>