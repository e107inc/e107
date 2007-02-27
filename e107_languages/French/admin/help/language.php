<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/help/language.php,v $
|     $Revision: 1.4 $
|     $Date: 2007-02-27 01:57:03 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$text = "L'activation d'un nouveau langage permet d'avoir une version de votre contenu dans cette langue sur votre site.<hr />
Dans l'effort de vous fournir une traduction 100% francophone, tous les termes que vous retrouverez sur ce site seront uniquement en français.<br />
Certains de ces termes ne sont encore que peu utilisé au travers du web francophone, si vous préférez les modifier pour accompagner vos usagers
ou vos fantaisies, veuillez le faire depuis cette page :<form action='".e_LANGUAGEDIR.e_LANGUAGE."/".e_LANGUAGE."_config.php' style='text-align:center'>
<input type='submit' class='button' name='update_lan_French' value='Modifier les termes' /></form>

";
$ns -> tablerender("Aide Langages", $text);
?>