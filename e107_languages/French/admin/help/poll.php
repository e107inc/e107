<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/help/poll.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-12-04 21:32:31 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$text = "Configurez les sondages à partir de cette page. Entrez le titre du sondage et les options (réponses), faites un aperçu et si tout est bon cochez la case pour l'activer.<br /><br />Pour mettre le sondage sur la page d'accueil, allez à la section Menus et veillez à ce que le menu 'poll_menu' soit activé.";
$ns -> tablerender("Aide", $text);
?>
