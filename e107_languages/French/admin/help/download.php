<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/help/download.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-12-04 21:32:31 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$text = "Envoyez vos fichiers dans le dossier ".e_FILE."downloads, vos images dans le dossier ".e_FILE."downloadimages et vos vignettes dans le dossier ".e_FILE."downloadthumbs.
<br /><br />
Pour proposer un téléchargement, créez d'abord une catégorie, puis créez ensuite une sous-catégorie de cette catégorie mère. Vous pourrez finalement rendre le téléchargement disponible en le plaçant dans cette sous-catégorie.";
$ns -> tablerender("Aide", $text);
?>
