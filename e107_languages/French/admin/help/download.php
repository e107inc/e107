<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/help/download.php,v $
 * $Revision: 1.7 $
 * $Date: 2008/06/16 15:03:43 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }

$text = 'Envoyez vos fichiers dans le dossier '.e_FILE.'downloads, vos images dans le dossier '.e_FILE.'downloadimages et vos vignettes dans le dossier '.e_FILE.'downloadthumbs.
<br /><br />
Pour proposer un téléchargement, créez d\'abord une catégorie et ensuite une sous-catégorie de cette catégorie mère. Vous pouvez finalement rendre le téléchargement disponible en le plaçant dans cette sous-catégorie.';
$ns -> tablerender('Aide', $text);
