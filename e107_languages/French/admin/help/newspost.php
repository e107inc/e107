<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/help/newspost.php,v $
 * $Revision: 1.10 $
 * $Date: 2008/07/26 21:15:50 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }

$text = '<strong>Général</strong><br />
La news est affichée sur la page principale. La suite est lisible après avoir cliqué sur le lien <q>Lire la suite</q>.<br /><br />
<strong>Titre uniquement</strong><br />
Choisissez cette option pour ne montrer que le titre sur la page d\'accueil (sous forme de lien pour voir la news complète).<br /><br />
<strong>Activation</strong><br />
Si vous configurez une date de début ou une date de fin, la news n\ est affichée qu\'entre ces 2 dates.';
$ns -> tablerender('Aide news', $text);
