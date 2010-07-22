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

setlocale(LC_ALL,     'nl_NL.utf8', 'nld_nld.utf8', 'nld.utf8', 'nl.utf8', 'nl');
define('CORE_LC',     'nl');
define('CORE_LC2',    'NL');
define('CHARSET',     'utf-8'); // for a true multi-language site. :)
define('CORE_LAN1',   'Fout: Theme ontbreekt.\n\n Wijzig het te gebruiken theme in de theme manager (in het beheerscherm) of upload de bestanden van het huidige theme naar de server.'); // \n OK in ''

//v.616
 //obsolete define('CORE_LAN2',   ' $1 schreef:'); // $1 represents the username.
 //obsolete define('CORE_LAN3',   'Bestandbijlage gedeactiveerd');

//v0.7+
define('CORE_LAN4',   'Verwijder install.php van je server, ');
define('CORE_LAN5',   'als je dat niet doet bestaat er een beveiligingsrisico voor je website');

// v0.7.6
define('CORE_LAN6',   'Let op: De &quot;flood&quot; bescherming op deze site is geactiveerd, als je doorgaat met het te vaak/snel (flooden) opvragen van pagina´s op deze site loop je de kans een blokkade te krijgen.');
define('CORE_LAN7',   'Core probeert de voorkeuren vanuit de automatische backup te herstellen.');
define('CORE_LAN8',   'Core voorkeuren Fout');
define('CORE_LAN9',   'Core kon niet worden hersteld vanuit de automatische backup. Uitvoering gestopt.');
define('CORE_LAN10',  'Corrupt cookie gedetecteerd - uitgelogd.');

// Footer
define('CORE_LAN11',  'Opbouwtijd: ');
define('CORE_LAN12',  ' sec, ');
define('CORE_LAN13',  ' van dat voor queries. ');
define('CORE_LAN14',  '');			// Used in 0.8
define('CORE_LAN15',  'DB queries: ');
define('CORE_LAN16',  'Geheugengebruik: ');

// img.bb
define('CORE_LAN17',  '[afbeeldings weergave gedeactiveerd]');
define('CORE_LAN18',  'Afbeelding:');

define('CORE_LAN_B',  'B');
define('CORE_LAN_KB', 'kB');
define('CORE_LAN_MB', 'MB');
define('CORE_LAN_GB', 'GB');
define('CORE_LAN_TB', 'TB');

define('LAN_WARNING',    'Waarschuwing!');
define('LAN_ERROR',      'Fout');
define('LAN_ANONYMOUS',  'Anoniem');
define('LAN_EMAIL_SUBS', '-email-');

?>