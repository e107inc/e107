<?php 
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/help/news_category.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-12-04 21:32:31 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$text = "Vous pouvez classer vos ".GLOBAL_LAN_NEWS_1."s dans différentes catégories et permettre aux visiteurs de n'afficher que celles correspondant à certaines catégories. <br /><br />Enregistrez vos images dans le dossier ".e_THEME."-votretheme-/images/ ou dans e107_images/shared/newsicons/.";
$ns -> tablerender("Aide Catégories d'".GLOBAL_LAN_NEWS_1."", $text);
?>
