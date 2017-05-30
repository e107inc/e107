<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). License GNU/PGL
| Traducteurs: communauté française e107
|     $Source: /cvsroot/e107/e107_langpacks/e107_languages/French/admin/help/forum.php,v $
|     $Revision: 1.1 $
|     $Date: 2006/04/08 19:49:11 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
  $caption = "Aide Forums";
  $text = "<strong>Général</strong><br />
  Utilisez cette page pour créer ou éditer des forums.<br />
  <br />
  <strong>Catégories/Forums</strong><br />
  Une catégorie est un titre sous lequel des forums sont affichés, cela simplifie la mise en page et rend la navigation beaucoup plus simple pour vos visiteurs.
  <br /><br />
  <strong>Accessibilité</strong>
  <br />
  Vous pouvez configurer vos forums pour qu'ils ne soient accessibles qu';à certains visiteurs. Dès que vous avez confgiuré le 'groupe';des visiteurs, vous pouvez cocher la case pour n'autoriser que ce groupe à accèder à ce forum. Vous pouvez le faire avec des catégories ou des forums de la même façon.
  <br /><br />
  <strong>Modérateurs</strong>
  
  <br />
  Cochez les noms des administrateurs listés pour leur donner le statut de modérateur du forum. L'administrateur doit avoir la permission de modérer des forums pour être listé ici.
  <br /><br />
  <strong>Grades</strong>
  <br />
  Configurez les grades d'utilisateur ici. Si le champ 'image';est rempli, une image sera utilisée. Pour utiliser les nom, entrez les et vérifiez bien que les champs 'nom' correspondants sont vides.<br />Le 'threshold' est le nombre de points dont l'utilisateur à besoin pour accéder au niveau suivant.";
  $ns -> tablerender($caption, $text);
  unset($text);
  ?>
