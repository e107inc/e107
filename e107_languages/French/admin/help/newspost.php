<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/help/newspost.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-12-04 21:32:31 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$text = "<strong>Général</strong><br />
L'".GLOBAL_LAN_NEWS_1." sera affichée sur la page principale, la suite sera lisible après avoir cliqué sur le lien 'Lire la suite'.
<br />
<br />
<strong>Titre Seulement</strong>
<br />
Choisissez cela pour ne montrer que le titre sur la page d'accueil (sous forme de lien pour voir l'".GLOBAL_LAN_NEWS_2." complète).
<br /><br />
<strong>Activation</strong>
<br />
Si vous configurez une date de début et/ou une date de fin, l'".GLOBAL_LAN_NEWS_1." ne sera affichée qu'entre ces 2 dates.
";
$ns -> tablerender("Aide ".GLOBAL_LAN_NEWS_2."", $text);
?>
