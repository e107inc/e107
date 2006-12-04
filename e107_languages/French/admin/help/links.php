<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/help/links.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-12-04 21:32:31 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$caption = "Aide Liens";
$text = "Entrez les liens internes du site ici. Ces liens seront affichés dans le menu de navigation principal du site (barre de navigation)<br /><br />
 Le générateur de sous-menus est à utiliser uniquement pour les menus DHTML e107 (TreeMenu, UltraTreeMenu, eDynamicMenu, ypSlideMenu...).<br /><br /> Pour les liens externes, veuillez utilisez l'extension 'Page de Liens'.";
$ns -> tablerender($caption, $text);
?>
