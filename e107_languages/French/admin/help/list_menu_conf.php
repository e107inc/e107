<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). License GNU/PGL
| Traducteurs: communauté française e107
|     $Source: /cvsroot/e107/e107_langpacks/e107_languages/French/admin/help/list_menu_conf.php,v $
|     $Revision: 1.1 $
|     $Date: 2006/04/08 19:49:11 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
  $text = "Dans cette section, vous pouvez configurer 3 menus<br /><br />
  <strong> Menu Nouveaux articles</strong> <br />
  Entrez un numéro, par exemple '5', dans le premier champs pour afficher 5 articles, laissez vide pour tous les montrer. Vous configurez quel est le titre du lien pour les autres articles dans le second champs (si vous laissez le second champs vide il n'y aura pas de lien).<br />
  <strong> Menu Commentaires//Forums</strong> <br />
  Le nombre de commentaires par défaut est 5, le nombre de caractères par défaut est 10000. Le suffixe est utilisé pour les lignes trop longues : elles seront tronquées et le suffixe sera ajouté à la fin. '...';serait un bon choix.<br />";
  $ns -> tablerender("Aide", $text);
  ?>
