<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). License GNU/PGL
| Traducteurs: communauté française e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/help/banlist.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-04-08 19:49:11 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
  $caption = "Bannir des utilisateurs";
  $text = "Vous pouvez bannir des utilisateurs de votre site à partir de cette page.<br />
  Soit vous entrez l'adresse IP complète, soit vous entrez une partie pour faire un rang d'adresses IP. Vous pouvez aussi entrer une adresse courriel pour interdire l'inscription de quelqu'un.<br /><br />
  <strong>Bannir par adresse IP :</strong><br />
  Entrer l'adresse IP 123.123.123.123 bloquera tous les utilisateurs qui tenteront de visiter votre site avec cette adresse IP.<br />
  Entrer l'adresse IP 123.123.123.* bloquera n'importe quel personne avec l'adresse comprise entre 123.123.123.0 et 123.123.123.255<br /><br />
  <strong>Bannir par adresse courriel</strong><br />
  Entrer l'adresse courriel foo@bar.com bloquera n'importe quel personne qui s'inscrira avec cette adresse sur votre site.<br />
  Entrer l'adresse courriel *@bar.com bloquera n'importe quelle personne qui s'inscrit sur votre site avec un courriel du domaine bar.com.";
  $ns -> tablerender($caption, $text);
  ?>
