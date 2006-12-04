<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/help/notify.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-12-04 21:32:31 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

  $text = "Alerter par courriel lorsque des événements e107 se produisent.<br /><br />
  Par exemple, mettre l'option 'IP bannis pour inondation de site' au groupe 'Admin' 
  et tous les administrateurs recevront un courriel si votre site est inondé.<br /><br />
  Vous pouvez également alerter vos utilisateurs lorsqu'une ".GLOBAL_LAN_NEWS_1." est ajoutée.<br /><br />
  Si vous voulez que les alertes par courriel soient envoyées à une adresse électronique alternative - 
  sélectionnez l'option 'Courriel' et remplissez le champ avec l'adresse électronique.";
  $ns -> tablerender("Aide Alertes", $text);
?>
