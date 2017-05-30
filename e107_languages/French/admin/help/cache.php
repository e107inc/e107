<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/help/cache.php,v $
 * $Revision: 1.10 $
 * $Date: 2008/06/30 22:32:47 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }

$text = 'Si le cache est activé, la fluidité du site est sensiblement augmentée et le nombre de requêtes SQL vers la base de données réduit.<br /><br /><strong>IMPORTANT: Si vous êtes en train de modifier le thème, désactivez le cache afin que les modifications soient visibles.</strong>';
$ns -> tablerender('Système de cache', $text);
