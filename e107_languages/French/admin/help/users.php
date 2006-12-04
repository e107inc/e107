<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/help/users.php,v $
|     $Revision: 1.5 $
|     $Date: 2006-12-04 21:32:31 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$text = "Cette page permet de modérer les utilisateurs enregistrés. Vous pouvez modifier leurs paramètres, leur donner le statut d'administrateur, les intégrer dans un groupe, etc.";
$ns -> tablerender("Aide Utilisateurs", $text);
unset($text);
?>