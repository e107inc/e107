<?php 
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/help/frontpage.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-12-04 21:32:31 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$caption = "Page d'accueil";
$text = "Vous pouvez choisir ce que vous désirez afficher comme page d'accueil de votre site. Par défaut, ce sont les ".GLOBAL_LAN_NEWS_1."s.";
$ns -> tablerender($caption, $text);
?>
