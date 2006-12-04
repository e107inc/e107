<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/help/filemanager.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-12-04 21:32:31 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$text = "Vous avez la possibilité de gérer les fichiers dans le dossier ".e_FILE." depuis cette page. Si vous obtenez des erreurs au sujet de permissions en uploadant, veuillez faire un CHMOD 777 sur le dossier où vous voulez envoyer le fichier.";
$ns -> tablerender("Gestionnaire de fichiers", $text);
?>
