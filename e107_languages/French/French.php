<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/French.php,v $
 * $Revision: 1.20 $
 * $Date: 2009/07/19 17:00:47 $
 * $Author: marj_nl_fr $
 *
 * @TODO:   bidouille ET_e107_Version_7 pour CORE_LAN13
 *
 *          égalepent à utiliser pour les autres fichiers
 * (ET_e107_Version_7 === true
 *    ? define('LA_CONSTANTE', 'texte pour v0.7')
 *    : define('LA_CONSTANTE', 'texte pour v0.8')
 *    );
 */
// ajout eTalkers pour différencier v0.7 de v0.8
if(!defined('e_ADMIN')) { exit(); }

if (is_readable(e_ADMIN.'ver.php'))
{
	include(e_ADMIN.'ver.php');
}
if(version_compare($e107info['e107_version'], '0.8', '<'))
{
    // e107 v0.7
    define('ET_e107_Version_7', true);
}
else
{
    // e107 v0.8
    define('ET_e107_Version_7', false);
}
// fin de l'ajout

// traduction normale

setlocale(LC_ALL,   'fr_FR.UTF-8', 'fr_FR.utf8', 'fr_FR@euro', 'fra_fra.utf8', 'fr_FR', 'fr');
define('CORE_LC',   'fr');
define('CORE_LC2',  'fr');

define('CHARSET',   'utf-8');
define('CORE_LAN1', 'Erreur: le thème requis est manquant.\n\nChangez le thème utilisé dans les préférences (administration) ou uploadez les fichiers du thème actuel sur le serveur.');

//v.616
//define('CORE_LAN2', ' $1 inscrit:');
//define('CORE_LAN3', 'fichier attaché désactivé');

//v0.7+
define('CORE_LAN4', 'Veuillez effacer le fichier install.php de votre serveur.');
define('CORE_LAN5', 'Sinon cela représente un risque non négligeable pour votre site!!!');

// v0.7.6
define('CORE_LAN6', 'La protection anti-inondation (flood) à été activée sur ce site. Si vous persévérez d’effectuer des requêtes trop fréquentes vous risquez d’être banni.');
define('CORE_LAN7', 'Le noyau tente de restaurer la sauvegarde automatique.');
define('CORE_LAN8', 'Erreur préférences noyau');
define('CORE_LAN9', 'Le noyau n’a pas pût restaurer la sauvegarde automatique. Exécution arrêtée.');
define('CORE_LAN10', 'Cookie corrompu détecté. Déconnexion.');

// Footer
define('CORE_LAN11', 'Temps d’exécution: ');
define('CORE_LAN12', 's, dont ');
(ET_e107_Version_7 === true
    ? define('CORE_LAN13', ' pour les requêtes. ')
    : define('CORE_LAN13', '% pour les requêtes. ')
    );
define('CORE_LAN14', '%2.3f CPU sec (chargement %2.2f%%, initialisation %2.3f). Horloge: '); //*** v0.8
define('CORE_LAN15', 'requêtes BdD: ');
define('CORE_LAN16', 'utilisation mémoire: ');

// img.bb
define('CORE_LAN17', '[images désactivées]');
define('CORE_LAN18', 'image: ');

define('CORE_LAN_B',  'o');
define('CORE_LAN_KB', 'ko');
define('CORE_LAN_MB', 'Mo');
define('CORE_LAN_GB', 'Go');
define('CORE_LAN_TB', 'To');


define('LAN_WARNING',    'Attention!');
define('LAN_ERROR',      'Erreur');
define('LAN_ANONYMOUS',  'Anonyme');
define('LAN_EMAIL_SUBS', '-email-');
