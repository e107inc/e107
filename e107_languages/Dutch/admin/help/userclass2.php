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

$caption = "Gebruikersklasse Hulp";
$text = "Je kunt op deze pagina gebruikersgroepen (klassen) aanmaken, bewerken of verwijderen.<br />Dit is nuttig om bepaalde onderdelen van je site af te schermen van de reguliere gebruikers. Je kunt bijvoorbeeld een TEST-klasse en een forum aanmaken en daarna het forum toewijzen aan de TEST-klasse waardoor alleen gebruikers die lid zijn van de TEST-klasse toegang krijgen tot het forum.<br /> Door het gebruik van Klassen kun je een alleen-voor-leden onderdeel van je site maken.";
$ns -> tablerender($caption, $text);

?>