<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/help/cache.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-12-04 21:32:30 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$caption = "Système de cache";
$text = "Si le cache est activé, la fluidité de votre site sera sensiblement augmentée et le nombre de requêtes SQL vers la base de données réduite.<br /><br /><strong>IMPORTANT: Si vous êtes en train de modifier le thème, désactivez le cache sinon aucune modification ne sera visible.</strong>";
$ns -> tablerender($caption, $text);
?>
