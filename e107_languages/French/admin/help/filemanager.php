<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/help/filemanager.php,v $
 * $Revision: 1.6 $
 * $Date: 2008/07/20 13:04:24 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }

$text = 'Vous avez la possibilité de gérer les fichiers dans le dossier '.e_FILE.' depuis cette page. Si vous obtenez des erreurs au sujet de permissions en uploadant, veuillez faire un chmod 777 ou 755 sur le dossier où vous voulez envoyer le fichier (voir avec votre hébergeur pour plus de détails).';
$ns -> tablerender('Gestionnaire de fichiers', $text);
