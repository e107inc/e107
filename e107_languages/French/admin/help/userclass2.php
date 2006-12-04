<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/help/userclass2.php,v $
|     $Revision: 1.6 $
|     $Date: 2006-12-04 21:32:31 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$caption = "Aide Groupe utilisateur";
$text = "Depuis cette page, vous pouvez créer/éditer/supprimer des groupes.<br />Cela est utile pour restreindre les utilisateurs à certaines parties de votre site. Par exemple, vous pouvez créer un groupe appelé TEST, puis créer un forum où seul les membres du groupe TEST sont autorisés à y accéder.";
$ns -> tablerender($caption, $text);
?>