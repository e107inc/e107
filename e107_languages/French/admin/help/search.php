<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/help/search.php,v $
 * $Revision: 1.6 $
 * $Date: 2008/06/30 22:32:47 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }

$text = 'Si votre version de serveur MySQL le permet vous pouvez commuter vers la méthode de tri MySQL qui est plus rapide que la méthode de tri PHP. Voir les préférences.<br /><br />
Si des idéogrammes (japonais, chinois, …) sont utilisés dans le site, vous devez utiliser la méthode PHP et désactiver la recherche par mots complets.';
$ns -> tablerender ('Aide sur la recherche', $text);
