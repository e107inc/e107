<?php 
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/help/frontpage.php,v $
 * $Revision: 1.9 $
 * $Date: 2008/06/30 22:32:47 $
 * $Author: marj_nl_fr $
 * @TODO: en v0.8 ...
 */

if (!defined('e107_INIT')) { exit; }

$caption = 'Page d\'accueil';
$text = 'Ici vous pouvez choisir ce que vous désirez afficher comme page d\'accueil pour le site. Par défaut ce sont les news.<br /><br />
En v0.8 il est également possible de spécifier si un membre doit être redirigé vers une page après sa connexion.<br /><br />
La liste des règles sont scannées en boucle jusqu\'à ce qu\'une concordance de groupes auxquels appartient le membre soit trouvée.<br />
La page d\'accueil correspondante ainsi que la page après connexion du membre sont ainsi déterminées.';

$ns -> tablerender($caption, $text);
