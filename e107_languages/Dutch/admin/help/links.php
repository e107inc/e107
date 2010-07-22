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

$text = "Maak hier de links aan welke getoond worden in je hoofdmenu(s). Je kan kiezen uit de main of alt, de main links zullen worden getoond in je hoofdlink menu, de alt links kunnen worden gebruikt voor een andere linkzone mits ondersteund door je theme.<br /><br />
Voor andere links kun je de Linkspagina plugin gebruiken waarop externe weblinks kunnen worden getoond.<br />Zie <a href='http://wiki.e107.org/?title=Links_Page'>Using the Link page plugin on your site</a> voor een uitleg van alle functies van deze Linkspagina plugin.Deze plugin vindt je in je Pluginbeheer";

$ns -> tablerender("Links Hulp", $text);

?>