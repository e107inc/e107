<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/help/custommenu.php,v $
|     $Revision: 1.4 $
|     $Date: 2006-12-04 21:32:31 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$text = "À partir de cette page, vous pouvez créer des menus personnifiés avec votre propre contenu dedans.<br /><br /><strong>Attention</strong> : pour utiliser cette fonctionnalité, vous devez mettre un CHMOD de 777 sur le dossier /e107_menus/custom/.
<br /><br />
<i>Nom de fichier</i> : le nom du menu, le menu sera sauvegardé sous le nom ';votre_nom.php'; dans le dossier des menus<br />
<i>Titre</i> : le texte à afficher dans la barre de titre du menu<br />
<i>Texte</i> : les données qui seront affichées dans le menu, peut être du texte, des images ...";

$ns -> tablerender("Aide", $text);
?>
