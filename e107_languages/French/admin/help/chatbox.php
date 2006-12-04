<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/help/chatbox.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-12-04 21:32:30 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$caption = "Aide Chatbox";
$text = "Configurez vos paramètres pour la Chatbox ici.<br />Si la case 'Remplacer les liens' est cochée, tous les liens entrés dans la chatbox seront remplacés par ce texte, cela réduira les longs liens qui causent des problèmes d'affichage. 'Wordwrap' coupera automatiquement les textes plus longs que la longueur spécifiée.";
$ns -> tablerender($caption, $text);
?>
