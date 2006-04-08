<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). License GNU/PGL
| Traducteurs: communauté française e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/help/fileinspector.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-04-08 19:49:11 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
  $text = "<div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
  <img src='".e_IMAGE."fileinspector/file_core.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;Fichier du Noyau</div>
  <div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
  <img src='".e_IMAGE."fileinspector/file_check.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;Fichier du Noyau (Intégrité Passé)</div>
  <div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
  <img src='".e_IMAGE."fileinspector/file_warning.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;Fichier du Noyau (Intégrité en Échec)</div>
  <div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
  <img src='".e_IMAGE."fileinspector/file_missing.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;Fichier du Noyau Absent</div>
  <div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
  <img src='".e_IMAGE."fileinspector/file_unknown.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;Fichier du Noyau Aucun</div>
  <div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
  <img src='".e_IMAGE."fileinspector/file_missing.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;Fichier Noyau Manquant</div>
  <div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
  <img src='".e_IMAGE."fileinspector/file_old.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;Fichier Ancien Noyau</div>
  <div style='margin-left: 0px; margin-bottom: 1px; margin-top: 2px; vertical-align: top; white-space: nowrap'>
  <img src='".e_IMAGE."fileinspector/file_unknown.png' alt='".$dir."' style='margin-left: 3px; width: 16px; height: 16px' />&nbsp;Fichier non Noyau</div>";
  $ns -> tablerender("File Key", $text);
  $text = "L'inspecteur de fichier balaye et analyse les fichiers sur votre serveur. 
  Quand l'inspecteur rencontre un fichier du noyau e107 il l'examine pour assurer l'uniformité du fichier pour s'assurer qu'il ne soit pas corrompu.";
  if ($pref['developer']) {
  $text .= "<br /><br />
  L'outil de recherche de chaines de concordances supplémentaires (mode développeur seulement) vous permet de parcourir les fichiers sur votre serveur pour des chaînes de caractères utilisant des expressions régulières. 
  Le moteur regex utilisé pour php<a href='http://php.net/pcre'>PCRE</a> 
   (fonctions preg_*), Entrez donc votre requête comme #modèle#des modificateurs dans les champs ainsi fournis.";
  }
  $ns -> tablerender("Aide Inspecteur Fichiers", $text);
  ?>
