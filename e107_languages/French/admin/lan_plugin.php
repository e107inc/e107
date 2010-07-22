<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/lan_plugin.php,v $
 * $Revision: 1.15 $
 * $Date: 2009/06/05 13:24:40 $
 * $Author: marj_nl_fr $
 */
if (!defined('e107_INIT')) { exit(); }

define('EPL_ADLAN_0', 'Installer');
define('EPL_ADLAN_1', 'Désinstaller');
define('EPL_ADLAN_2', 'Êtes-vous certain de vouloir désinstaller ce plugin?'); // obsolete
define('EPL_ADLAN_3', 'Confirmer la désinstallation'); // attr
define('EPL_ADLAN_4', 'Désinstallation annulée.'); // obsolete
define('EPL_ADLAN_5', 'La procédure d’installation créera de nouvelles entrées dans les préférences.'); // obsolete
define('EPL_ADLAN_6', '… cliquer ensuite ici pour commencer la procédure d’installation'); // obsolete
define('EPL_ADLAN_7', 'Les tables de la base de données ont été mises à jour avec succès.');
define('EPL_ADLAN_8', 'Les paramètres de préférences ont été créés avec succès.');
define('EPL_ADLAN_9', 'Les commandes SQL ont échouées. Assurez vous que toutes les mises à jour sont correctes.');
define('EPL_ADLAN_10', 'Nom'); // obsolete
define('EPL_ADLAN_11', 'Version');
define('EPL_ADLAN_12', 'Auteur');
define('EPL_ADLAN_13', 'Compatibilité');
define('EPL_ADLAN_14', 'Description');
define('EPL_ADLAN_15', 'Lire le fichier README pour plus d’informations'); // obsolete
define('EPL_ADLAN_16', 'Informations');
define('EPL_ADLAN_17', 'Plus d’infos…'); // obsolete
define('EPL_ADLAN_18', 'Impossible de créer les tables dans la base de données pour ce plugin.');
define('EPL_ADLAN_19', 'Les tables ont été créées avec succès dans la base de données.');
// define('EPL_ADLAN_20', 'Les préférences ont été créés avec succès.');
define('EPL_ADLAN_21', 'ce plugin est déjà installé.');
define('EPL_ADLAN_22', 'Installé');
define('EPL_ADLAN_23', 'Non installé');
define('EPL_ADLAN_24', 'mise à jour disponible');
define('EPL_ADLAN_25', 'Aucune installation requise');
define('EPL_ADLAN_26', '… cliquer ensuite ici pour commencer la procédure de désinstallation');
define('EPL_ADLAN_27', 'Impossible de supprimer ');
define('EPL_ADLAN_28', 'Les tables de la base de données ont été supprimées avec succès.');
define('EPL_ADLAN_29', 'Les paramètres de préférences ont été supprimés avec succès.');
define('EPL_ADLAN_30', 'veuillez le supprimer manuellement.');
define('EPL_ADLAN_31', 'Veuillez maintenant supprimer le dossier ');
define('EPL_ADLAN_32', 'et tous les fichiers à l’intérieur pour compléter la procédure de désinstallation.');
define('EPL_ADLAN_33', 'Le plugin a été installé avec succès.');
define('EPL_ADLAN_34', 'Le plugin a été mis à jour avec succès.');
define('EPL_ADLAN_35', 'Les paramètres du parser ont été correctement ajoutés.');  // obsolete
define('EPL_ADLAN_36', 'L’insertion du code de l’interpréteur à échoué.');  // obsolete
define('EPL_ADLAN_37', 'Télécharger un plugin sur le serveur (le fichier doit être au format .zip ou .tar.gz)');
define('EPL_ADLAN_38', 'Télécharger un plugin');
define('EPL_ADLAN_39', 'Ce fichier ne peut pas être importé tant que le dossier '.e_PLUGIN.' n’a pas les permissions correctes. Rendez le accessible en écriture et réimportez le fichier.');
define('EPL_ADLAN_40', 'Message Admin');
define('EPL_ADLAN_41', 'Ce fichier ne semble pas être une compression .zip ou .tar valide.');
define('EPL_ADLAN_42', 'Il semble y avoir une erreur, il est impossible de désarchiver ce fichier');
define('EPL_ADLAN_43', 'Votre plugin a bien été téléchargé sur le serveur et dézipé, veuillez faire défiler la fenêtre vers le bas pour le voir dans la liste.');
define('EPL_ADLAN_44', 'Le téléchargement et l’extraction automatique des plugins sont désactivés car votre répertoire plugins n’a pas les permissions correctes. Mettez le dossier '.e_PLUGIN.' accessible en écriture.');
define('EPL_ADLAN_45', 'Votre menu à bien été téléchargé et désarchivé sur le serveur. Afin de l’activer allez sur <a href="'.e_ADMIN.'menu.php"> votre page de menus</a>.');
define('EPL_ADLAN_46', 'erreur PCLZIP lors de l’extraction:');
define('EPL_ADLAN_47', 'erreur PCLTAR lors de l’extraction: ');
define('EPL_ADLAN_48', 'code:');
define('EPL_ADLAN_49', 'Tables non supprimées pendant la procédures de désinstallation en fonction de votre choix');

define('EPL_WEBSITE',   'Site Web');
define('EPL_NOINSTALL', 'Aucune installation n\'est requise. Il vous suffit d\'activer ce plugin depuis la gestion des menus. Pour le désinstaller, supprimer le dossier ');
define('EPL_DIRECTORY', '');
define('EPL_NOINSTALL_1', 'Aucune installation n\'est requise. Pour le désinstaller, supprimer le dossier ');
define('EPL_UPGRADE',   'Mettre à jour');

define('EPL_ADLAN_50', 'Commentaires supprimés avec succès.');

define('EPL_ADLAN_53', 'Le répertoire est protégé en écriture.');
define('EPL_ADLAN_54', 'Veuillez choisir une option pour désinstaller le plugin:');
define('EPL_ADLAN_55', 'Désinstaller le plugin');

define('EPL_ADLAN_57', 'Effacer les tables de ce plugin');
define('EPL_ADLAN_58', 'Si les tables ne sont pas enlevées, il sera possible de réinstallée ce plugin en conservant les données actuelles. La création de tables lors de la réinstallation échouera, ce qui est normal. Si plus tard vous voulez supprimer définitivement les tables vous devrez le faire manuellement.');
define('EPL_ADLAN_59', 'Effacer les fichiers de ce plugin');
define('EPL_ADLAN_60', 'e107 va tenter d’effacer tous les fichiers liés à ce plugin.');
// define('EPL_ADLAN_61', 'Confirmer la désinstallation');
define('EPL_ADLAN_62', 'Annuler la désinstallation'); // attr
define('EPL_ADLAN_63', 'Désinstaller:');
define('EPL_ADLAN_64', 'Dossier:');

// v0.8
define('EPL_ADLAN_70', 'Plugin nécessaire non installé:');
define('EPL_ADLAN_71', 'Nouvelle version nécessaire pour le plugin:');
define('EPL_ADLAN_72', 'Version:');
define('EPL_ADLAN_73', 'Extension PHP nécessaire non installée:');
define('EPL_ADLAN_74', 'Version PHP plus récente nécessaire:');
define('EPL_ADLAN_75', 'Version MySQL plus récente nécessaire:');
define('EPL_ADLAN_76', 'Erreur dans plugin.xml');
define('EPL_ADLAN_77', 'plugin.xml non trouvé');

define('LAN_UPGRADE_SUCCESSFUL', 'Mis à jour avec succès');
define('LAN_INSTALL_SUCCESSFUL', 'Installé avec succès');
// v0.8
define('LAN_INSTALL_FAIL', 'Installation non réussie');
