<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/help/upload.php,v $
 * $Revision: 1.6 $
 * $Date: 2008/06/23 21:59:39 $
 * $Author: marj_nl_fr $
 */
 
if (!defined('e107_INIT')) { exit; }

if(ET_e107_Version_7 === true)
{
    $text = 'Ici vous pouvez autoriser/refuser que les utilisateurs téléchargent des fichiers, et contrôler les téléchargements de fichiers.';
}
else
{
    if (e_QUERY) list($action,$junk) = explode('.',e_QUERY); else $action = 'list';

    switch ($action)
    {
        case 'filetypes' :
            $text = 'Les types de fichiers et la taille maximale autorisée sont définis par groupes. Ces options génèrent le fichier '.e_UPLOAD_TEMP_DIR.e_SAVE_FILETYPES.', qui doit être copier ou déplacé dans le dossier '.e_ADMIN_ABS.' et renommé en '.e_READ_FILETYPES.' avant d\'être utilisable.<br />
            Seulement une définition par groupe est autorisée.<br />
            Notez que ces définitions s\'applique pour l\'ensemble du site, même si les uploads sont désactivés.';
        break;
        case 'options' :
            $text = 'Le système d\'uploads peut être entièrement désactivé depuis ici.<br />
            Le type de stockage fichier est généralement le plus approprié, sinon la taille maximale de fichier est limitée à 500ko.<br />
            La taille maximale de fichier prend le pas sur toute définition de filetypes.xml.<br />
            Les uploads peuvent être restreints à un groupe particulier, mais ces paramètres s\'appliquent à d\'autres zones du site où ils peuvent être autorisés tel les news et formulaires.';
        break;
        case 'view' :
        default :
            $text = 'Cette page liste les uploads proposés. Vous pouvez choisir de les supprimer, les transférer en téléchargement ou les poster en news.';
    }
}
$ns -> tablerender('Aide téléchargements', $text);
