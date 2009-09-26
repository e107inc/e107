<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/integrity_check/languages/French.php,v $
|     $Revision: 1.6 $
|     $Date: 2009-09-26 15:20:56 $
|     $Author: yarodin $
+---------------------------------------------------------------+
*/
define("Integ_01", "La sauvegarde a réussie");
define("Integ_02", "La sauvegarde a échouée");
define("Integ_03", "Fichiers manquants:"); 
define("Integ_04", "Erreurs CRC:");
define("Integ_05", "Impossible d'ouvrir le fichier...");
define("Integ_06", "Contrôle Intégrité des Fichiers ");  
define("Integ_07", "Aucun fichier disponible");  
define("Integ_08", "Contrôle de l&#39;Intégrité");  
define("Integ_09", "Créer le fichier SFV");
define("Integ_10", "Le dossier choisi <u>ne sera pas</u > sauvegardé dans le fichier CRC.");
define("Integ_11", "Nom du fichier:");  
define("Integ_12", "Créer le fichier SFV");
define("Integ_13", "Vérification - Intégrité");  
define("Integ_14", "Création SFV impossible car le dossier".e_PLUGIN."integrity_check/<strong>{output}</strong> n'est pas ouvert en écriture. Veuillez mettre les droits en écriture à 777 (CHMOD) ou créez le!");
define("Integ_15", "Tous les dossiers ont été vérifiés et sont OK!");
define("Integ_16", "Aucun fichier CRC du noyau disponible");  
define("Integ_17", "Aucun fichier CRC des extensions disponible");  
define("Integ_18", "Créer le fichier CRC de l'extension");  
define("Integ_19", "Fichier Checksum du Noyau");
define("Integ_20", "Fichier Checksum des Extensions");  
define("Integ_21", "Sélectionnez l'extension pour laquelle vous voulez créer un fichier CRC.");  
define("Integ_22", "Utiliser gzip ");  
define("Integ_23", "Vérifier uniquement les thèmes installés");
define("Integ_24", "Console d'administration");
define("Integ_25", "Page d'accueil");  
define("Integ_26", "Charger le site avec l'en-tête normale");  
define("Integ_27", "UTILISER L'INSPECTEUR DE FICHIERS POUR VÉRIFIER LES FICHIERS DU NOYAU");
//define("Integ_29", "<br /><br /><strong>*<u>CRC-ERREUR:</u></strong><br/> ceux-ci sont des erreurs checksum et il y a deux raisons possibles à ceci:<br/> - vous avez changé quelque chose dans le fichier mentionné ainsi il n'a pas plus la même longueur que l'original.<br/> - le dossier mentionné est corrompu, vous devez le ré-uploder!");
// un fichier langue ne doit PAS contenir de html.
define("Integ_30", "Pour éviter de trop charger le serveur, vous pouvez faire une vérification des 1 - 10 étapes.");  
define("Integ_31", "Étapes: ");   
define("Integ_32", "Un fichier nommé <strong>log_crc.txt</strong> se trouve dans votre dossier crc. Effacez le SVP! (ou rafraichissez l'écran)");
define("Integ_33", "Un fichier nommé <strong>log_miss.txt</strong> se trouve dans votre dossier crc. Effacez le SVP! (ou rafraichissez l'écran)");
define("Integ_34", "Le répertoire /integrity_check/crc n'est pas ouvert en écriture!");  
define("Integ_35", "En raison des causes suivantes, vous ne pouvez effectuer que la <strong>1ère</strong> étape:");  
define("Integ_36", "Si vous ne voulez pas attendre 5 secondes jusqu'à la prochaine étape:");  
define("Integ_37", "Cliquer ici");  
define("Integ_38", "Encore <u><i>{counts}</i></u> vérifications pour terminer...");  
define("Integ_39", "Veuillez supprimer le fichier:<br />".e_PLUGIN."integrity_check/<u><i>do_core_file.php</i></u>!<br /> il est périmé et n'a jamais été conçu pour la version publique...");
  //define("Integ_PLUG1", "Integrity-Check-Extension (Contrôle de l'intégrité) est une extension qui vérifie l'intégralité de vos dossiers, aidant à trouver les dossiers corrompus.");  
  //define("Integ_PLUG2", "Integrity-Check-Extension (Contrôle de l'intégrité) à été installé avec succès!");  
  //define("Integ_PLUG3", "Integrity-Check-Extension (Contrôle de l'intégrité) à été mis à jour avec succès !");
?>
