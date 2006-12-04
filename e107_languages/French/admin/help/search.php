<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/help/search.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-12-04 21:32:31 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$text = "Si votre version de serveur MySQL le permet vous pouvez commuter vers la méthode de tri MySQL qui est plus rapide que la méthode de tri PHP. Voir les préférences.<br /><br />
Si votre site inclus des idéogrammes (Japonais, Chinois, ...) vous devez utiliser la méthode PHP et désactiver la recherche par mots complets";
$ns -> tablerender ("Aide sur la Recherche", $text);
?>
