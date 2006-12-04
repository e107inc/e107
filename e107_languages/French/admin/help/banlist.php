<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/help/banlist.php,v $
|     $Revision: 1.4 $
|     $Date: 2006-12-04 21:32:30 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

$caption = "Bannir des utilisateurs";
$text = "Vous pouvez bannir des utilisateurs de votre site à partir de cette page.<br />
Soit vous entrez l'adresse IP complète, soit vous n'utilisez qu'une partie pour bannir une plage d'adresses IP. Vous pouvez également entrer une adresse courriel pour interdire l'inscription de quelqu'un avec cette adresse sur votre site.<br /><br />
<strong>Bannir par adresse IP:</strong><br />
Entrer l'adresse IP 123.123.123.123 bloquera tous les utilisateurs qui tenteront de visiter votre site avec cette adresse IP.<br />
Entrer l'adresse IP 123.123.123.* bloquera l'accès au site de quiconque ayant une adresse comprise entre 123.123.123.0 et 123.123.123.255<br /><br />
<strong>Bannir par adresse courriel</strong><br />
Entrer l'adresse courriel foo@bar.com empêchera quiconque utilisant cette adresse de s'inscrire sur votre site.<br />
Entrer l'adresse courriel *@bar.com empêchera quiconque utilisant une adresse courriel du domaine bar.com de s'inscrire sur votre site.";
$ns -> tablerender($caption, $text);
?>
