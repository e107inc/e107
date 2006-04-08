<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). License GNU/PGL
| Traducteurs: communauté française e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/help/administrator.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-04-08 19:49:11 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
  if (!defined('e107_INIT')) { exit; }
  $caption = "Aide Administrateurs";
  $text = "Utilisez cette page pour entrer un nouvel administrateur, ou en supprimer.<br /> L'administrateur n'aura le droit d'accéder qu'aux fonctionnalités cochées<br /><br />
  Pour créer un nouvel administrateur, accédez à la page de configution admin et mettez à jour l'utilisateur existant en lui conférant le status admin.";
  $ns -> tablerender($caption, $text);
  ?>
