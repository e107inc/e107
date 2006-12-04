<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/help/ugflag.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-12-04 21:32:31 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$caption = "Aide Maintenance";
$text = "Si vous mettez à jour e107 ou si vous voulez que votre site soit indisponible pour quelque temps, cochez la case et vos visiteurs seront redirigés vers une page qui explique pourquoi votre site est indisponible.<br /><br /> Cochez la case de nouveau si vous voulez revenir à une situation normale.";
$ns -> tablerender($caption, $text);
?>
