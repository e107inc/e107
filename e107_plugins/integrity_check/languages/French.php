<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.7.11
 * à supprimer en 0.8
 *
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_plugins/integrity_check/languages/French.php,v $
 * $Revision: 1.8 $
 * $Date: 2008/07/20 13:04:25 $
 * $Author: marj_nl_fr $
 */

define("Integ_01", "La sauvegarde a réussie");
define("Integ_02", "La sauvegarde a échouée");
define("Integ_03", "Fichiers manquants:");
define("Integ_04", "Erreurs CRC:");
define("Integ_05", "Impossible d'ouvrir le fichier…");
define("Integ_06", "Contrôle de l'intégrité des fichiers");
define("Integ_07", "Aucun fichier disponible");
define("Integ_08", "Contrôle Intégrité");
define("Integ_09", "Créer le fichier SFV");
define("Integ_10", "Le dossier choisi <u>ne sera pas</u > sauvegardé dans le fichier CRC.");
define("Integ_11", "Nom du fichier:");
define("Integ_12", "Créer le fichier SFV");
define("Integ_13", "Vérification Intégrité");
define("Integ_14", "Création SFV impossible car le dossier".e_PLUGIN."integrity_check/<strong>{output}</strong> n'est pas ouvert en écriture. Veuillez mettre les droits en écriture (chmod, souvent à 777) ou créez le!");
define("Integ_15", "Tous les dossiers ont été vérifiés et sont OK!");
define("Integ_16", "Aucun fichier CRC disponible pour le noyau");
define("Integ_17", "Aucun fichier CRC disponible pour les plugins");
define("Integ_18", "Créer le fichier CRC pour les plugins");
define("Integ_19", "Fichier Checksum du Noyau");
define("Integ_20", "Fichier Checksum des Plugins");
define("Integ_21", "Sélectionner le plugin pour lequel vous voulez créer un fichier CRC.");
define("Integ_22", "Utilisation de gzip");
define("Integ_23", "Vérifier uniquement les thèmes installés");
define("Integ_24", "Console d'administration");
define("Integ_25", "Page d'accueil");
define("Integ_26", "Charger le site avec l'entête normal");
define("Integ_27", "UTILISER L'INSPECTEUR DE FICHIERS POUR VÉRIFIER LES FICHIERS DU NOYAU");
//define("Integ_29", "<br /><br /><strong>*<u>CRC-ERREUR:</u></strong><br/> ceux-ci sont des erreurs checksum et il y a deux raisons possibles à ceci:<br/> - vous avez changé quelque chose dans le fichier mentionné ainsi il n'a pas plus la même longueur que l'original.<br/> - le dossier mentionné est corrompu, vous devez le ré-uploder!");
// un fichier langue ne doit PAS contenir de html.

define("Integ_30", "Pour réduire l'utilisation CPU, vous pouvez faire une vérification de 1 à 10 étapes.");
define("Integ_31", "étapes: ");
define("Integ_32", "Un fichier nommé <strong>log_crc.txt</strong> se trouve dans votre dossier crc. Effacez le SVP! (ou rafraichissez l'écran)");
define("Integ_33", "Un fichier nommé <strong>log_miss.txt</strong> se trouve dans votre dossier crc. Effacez le SVP! (ou rafraichissez l'écran)");
define("Integ_34", "Votre dossier crc n'est pas accessible en écriture!");
define("Integ_35", "Pour les raisons suivantes, vous ne pouvez choisir que la <strong>1ère</strong> étape:");
define("Integ_36", "Cliquez ici, si vous ne voulez pas attendre 5 secondes jusqu'à la prochaine étape:");
define("Integ_37", "Cliquer moi");
define("Integ_38", "Encore <u><i>{counts}</i></u> passe(s) à faire…");
define("Integ_39", "Veuillez supprimer le fichier:<br />".e_PLUGIN."integrity_check/<u><i>do_core_file.php</i></u>!<br /> il est périmé et n'a jamais été conçu pour la version publique…");

?>