<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). License GNU/PGL
| Traducteurs: communauté française e107
|     $Source: /cvsroot/e107/e107_langpacks/e107_languages/French/admin/help/article.php,v $
|     $Revision: 1.1 $
|     $Date: 2006/04/08 19:49:11 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
  $caption = "Aide Article";
  $text = "A partir de cette page vous pouvez ajouter des articles à une ou plusieurs pages.<br />
  Pour un article de plusieurs pages, séparez chaque page par le tag [newpage], par exemple <br /><code>Test1 [newline] Test2</code><br /> affichera un article de 2 pages avec 'Test1
  sur la première et 'Test2' sur la seconde.
  <br /><br />
  Si votre article contient des tags HTML que vous voulez préserver, encadrez le code avec les balises [html] [/html]. Par exemple, si vous avez entré le texte '&lt;table>&lt;tr>&lt;td>Hello &lt;/td>&lt;/tr>&lt;/table>' dans votre article, un tableau contenant le mot 'hello' sera affiché. Si vous entrez '[html]&lt;table>&lt;tr>&lt;td>Hello &lt;/td>&lt;/tr>&lt;/table>[/html]', vous verrez le code tel que vous l'avez entré et non le tableau que le code génère.";
  $ns -> tablerender($caption, $text);
  ?>
