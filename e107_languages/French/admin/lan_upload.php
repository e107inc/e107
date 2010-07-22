<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/lan_upload.php,v $
 * $Revision: 1.8 $
 * $Date: 2009/06/05 13:24:40 $
 * $Author: marj_nl_fr $
 */
if (!defined('e107_INIT')) { exit(); }

define("UPLLAN_1", "Upload supprimé de la liste.");
define("UPLLAN_2", "Paramètres sauvegardés dans la base de données");
define("UPLLAN_3", "ID upload");

define("UPLLAN_5", "Proposé par");
define("UPLLAN_6", "Email");
define("UPLLAN_7", "Site Internet");
define("UPLLAN_8", "Nom du fichier");

define("UPLLAN_9", "Version");
define("UPLLAN_10", "Fichier");
define("UPLLAN_11", "Taille du fichier");
define("UPLLAN_12", "Capture d'écran");
define("UPLLAN_13", "Description");
define("UPLLAN_14", "Démonstration");

define("UPLLAN_16", "Copier dans les news");
define("UPLLAN_17", "supprimer de la liste");
define("UPLLAN_18", "Voir les détails");
define("UPLLAN_19", "Il n'y a aucun upload public non vérifié");
define("UPLLAN_20", "Il");
define("UPLLAN_21", "upload(s) public(s) non vérifié(s)");
define("UPLLAN_22", "ID");
define("UPLLAN_23", "Nom");
define("UPLLAN_24", "Type du Fichier");
define("UPLLAN_25", "Activer les Uploads?");
define("UPLLAN_26", "Aucun upload public ne sera permis si l'upload est désactivé");
define("UPLLAN_27", "uploads publics non modérés");

define("UPLLAN_29", "Type de stockage");
define("UPLLAN_30", "Choisissez comment stocker les fichiers uploadés, soit comme des fichiers normaux sur le serveur, soit en binaire dans votre base de données.<br /><strong>Noter que</strong> le binaire n'est approprié que pour des fichiers de taille inférieure à 500 ko");
define("UPLLAN_31", "Fichier");
define("UPLLAN_32", "Binaire");
define("UPLLAN_33", "Taille maximale du fichier");
define("UPLLAN_34", "Taille maximale de l'upload en Octets. Laisser vide pour vous conformer aux paramètres du php.ini de votre serveur");
define("UPLLAN_35", "Type de fichiers autorisés");
define("UPLLAN_36", "Veuillez entrer un seul type par ligne");
define("UPLLAN_37", "Groupe autorisé");
define("UPLLAN_38", "Sélectionner quels utilisateurs peuvent uploader des fichiers");
define("UPLLAN_39", "Envoyer");
define("UPLLAN_41", "Veuillez prendre note que l'upload de fichiers est désactivé dans votre php.ini, il ne sera pas possible d'uploader des fichiers tant que votre configuration n'est pas à On.");
define("UPLLAN_42", "Actions");
define("UPLLAN_43", "Uploads");
define("UPLLAN_44", "Upload");
define("UPLLAN_45", "Confirmation de la suppression de l'upload suivant:");
define("UPLAN_COPYTODLM", "copier dans le gestionnaire de téléchargements");
define("UPLAN_IS", "a ");
define("UPLAN_ARE", "ont ");
define("UPLAN_COPYTODLS", "Copier dans Téléchargements");
define("UPLLAN_48", "Pour des raisons de sécurité les types de fichiers autorisés ont été déplacés de la base de données dans un fichier plat placé dans votre répertoire admin. Pour l'utiliser, renommer le fichier e107_admin/filetypes_.php sous le nom e107_admin/filetypes.php et  ajoutez-y une liste d'extensions de type de fichier délimitée par des virgules. Vous ne devriez pas permettre l'upload de .html, .txt, etc..., car un intrus pourrait uploader un fichier de ce type contenant du javascript dangereux.  Évidemment, vous ne devriez pas permettre l'upload de fichier .php ou tout autre type de script exécutable.");

?>