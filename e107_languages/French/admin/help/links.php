<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/help/links.php,v $
 * $Revision: 1.7 $
 * $Date: 2008/07/26 21:15:50 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }

$text = 'Entrez les liens du site ici. Les liens sont affichés dans le menu de navigation principal du site (barre de navigation)<br /><br />
Le générateur de sous-menus est à utiliser uniquement pour les menus DHTML e107 (TreeMenu, UltraTreeMenu, eDynamicMenu, ypSlideMenu…).<br /><br />Pour les autres liens, utilisez le plugin <q>Page de liens</q>.';
$ns -> tablerender('Aide liens', $text);
