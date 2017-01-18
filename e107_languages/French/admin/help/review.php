<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). License GNU/PGL
| Traducteurs: communauté française e107
|     $Source: /cvsroot/e107/e107_langpacks/e107_languages/French/admin/help/review.php,v $
|     $Revision: 1.1 $
|     $Date: 2006/04/08 19:49:11 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
  $ns -> tablerender("Aide Chroniques", $text);
  $caption = "Aide Article";
  $text = "Les chroniques sont similaires aux articles mais elles ont besoin d';être listées dans leur propre menu.<br />
  Pour une chronique de plusieurs pages, séparez chaque page par le tag [newpage], par exemple <br /><code>Test1 [newline] Test2</code><br /> affichera une chroniques de 2 pages avec 'Test1
  sur la première et 'Test2' sur la seconde.
  <br /><br />
  Si votre chronique contient des tags HTML que vous voulez préserver, encadrez le code avec les balises [html] [/html]. Par exemple, si vous avez entré le texte '&lt;table>&lt;tr>&lt;td>Hello &lt;/td>&lt;/tr>&lt;/table>' dans votre chronique, un tableau contenant le mot 'hello' sera affiché. Si vous entrez '[html]&lt;table>&lt;tr>&lt;td>Hello &lt;/td>&lt;/tr>&lt;/table>[/html]', vous verrez le code tel que vous l'avez entré et non le tableau que le code génère.";
  $ns -> tablerender("Aide Chroniques", $text);
  ?>
