<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté française e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/help/administrator.php,v $
|     $Revision: 1.4 $
|     $Date: 2006-12-04 21:32:30 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Aide Administrateurs";
$text = "Utilisez cette page pour éditer ou supprimer des administrateurs.<br /><br />L'administrateur n'aura le droit d'accéder qu'aux fonctionnalités cochées.<br /><br />Pour créer un nouvel administrateur, rendez vous à la page de configuration des Membres et conférez le statut d'admin à l'utilisateur désiré.";
$ns -> tablerender($caption, $text);
?>