<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/lan_users_extended.php,v $
 * $Revision: 1.14 $
 * $Date: 2009/06/05 13:24:40 $
 * $Author: marj_nl_fr $
 */
if (!defined('e107_INIT')) { exit(); }

define('EXTLAN_1', 'Nom');
define('EXTLAN_2', 'Prévisualisation');
define('EXTLAN_3', 'Valeur');
define('EXTLAN_4', 'Requis');
define('EXTLAN_5', 'Applicable');
define('EXTLAN_6', 'Accès en lecture');
define('EXTLAN_7', 'Accès en écriture');
define('EXTLAN_8', 'Action');
define('EXTLAN_9', 'Champs étendus du profil');

define('EXTLAN_10', 'Nom du champ');
define('EXTLAN_11', 'C’est le nom du champ tel que stocké dans la table. Il doit être unique et ne pas être utiliser dans la table “user”.');
define('EXTLAN_12', 'Champ texte');
define('EXTLAN_13', 'C’est le nom du champ affiché dans les pages');
define('EXTLAN_14', 'Type de champ');
define('EXTLAN_15', 'Paramètres du type de champ');
define('EXTLAN_16', 'Valeur par défaut');
define('EXTLAN_17', 'Entrez une valeur par ligne (zone de texte)<br/>Pour une table BdD voir l’aide.');
define('EXTLAN_18', 'Requis');
define('EXTLAN_19', 'Les utilisateurs devront obligatoirement entrer une valeur dans ce champ en mettant leurs paramètres à jour.');
define('EXTLAN_20', 'Déterminez pour quels utilisateurs ce champ doit s’appliquer.');
define('EXTLAN_21', 'Détermine qui verra ce champ dans ses paramètres utilisateur.');
define('EXTLAN_22', 'Détermine qui peut voir la valeur dans la page membre<br />Note: le paramètre “lecture seule” la rendra visible uniquement pour le membre concerné et les admins.');
define('EXTLAN_23', 'Ajouter le champ');
define('EXTLAN_24', 'Mettre à jour');
define('EXTLAN_25', 'déplacer vers le bas');
define('EXTLAN_26', 'déplacer vers le haut');
define('EXTLAN_27', 'Confirmer la suppression');
define('EXTLAN_28', 'Aucun champ étendu de profil défini');
define('EXTLAN_29', 'Champ étendu de profil sauvegardé.');
define('EXTLAN_30', 'Champ étendu de profil supprimé');
//define('EXTLAN_31', 'Menu Champ étendu de profil');
//define('EXTLAN_32', 'Page d’accueil Champs étendus de profil');
define('EXTLAN_33', 'Annuler');
define('EXTLAN_34', 'Champs étendus de profil');
define('EXTLAN_35', 'Catégories');
define('EXTLAN_36', 'Pas de catégorie assignée');
define('EXTLAN_37', 'Pas de catégorie définie');
define('EXTLAN_38', 'Nom de catégorie');
define('EXTLAN_39', 'Ajouter une catégorie');
define('EXTLAN_40', 'Catégorie créée');
define('EXTLAN_41', 'Catégorie supprimée');
define('EXTLAN_42', 'Mettre à jour la catégorie');
define('EXTLAN_43', 'Catégorie mise à jour');
define('EXTLAN_44', 'Catégorie');
define('EXTLAN_45', 'Ajouter un nouveau champ');
define('EXTLAN_46', 'Aide');
define('EXTLAN_47', 'Ajouter un nouveau paramètre');
define('EXTLAN_48', 'Ajouter une nouvelle valeur');
define('EXTLAN_49', 'Autoriser les utilisateur à cacher');
define('EXTLAN_50', 'Positionner sur OUI autorisera l’utilisateur à cacher cette valeur aux non-admins');
define('EXTLAN_51', 'Tous paramètre w3c valide doit être entré ici<br />ex: <strong><i>class="tbox" size="40" maxlength="80"</i></strong>');
define('EXTLAN_52', 'Code de validation regex');
define('EXTLAN_53', 'Entrer le code regex (expression régulière) qui permettra de vérifier la validité de l’insertion<br />**les délimiteurs de regex sont nécessaires**');
define('EXTLAN_54', ' Texte d’erreur regex');
define('EXTLAN_55', 'Entrer un message d’erreur qui s’affichera si la validation regex échoue.');
define('EXTLAN_56', 'Champs prédéfinis');
define('EXTLAN_57', 'Activé');
define('EXTLAN_58', 'Non activé');
define('EXTLAN_59', 'Activé');
define('EXTLAN_60', 'Désactivé');
define('EXTLAN_61', 'Aucun');


define('EXTLAN_62', 'Choisissez une table');
define('EXTLAN_63', 'Choisissez un champ ID');
define('EXTLAN_64', 'Choisissez une valeur d’affichage');


define('EXTLAN_65', 'Non. Non affiché sur la page d’inscription');
define('EXTLAN_66', 'Oui. Affiché sur la page d’inscription');
define('EXTLAN_67', 'Non. Affiché sur la page d’inscription');

define('EXTLAN_68', 'Le champ:');
define('EXTLAN_69', 'est activé');
define('EXTLAN_70', 'Erreur! Champ:');
define('EXTLAN_71', 'n’a pas été activé!');
define('EXTLAN_72', 'est désactivé');
define('EXTLAN_73', 'n’a pas été désactivé!');
define('EXTLAN_74', 'est un nom de champs réservé et ne peut être utilisé.');
define('EXTLAN_75', 'Erreur lors de l’ajout dans la base de données.');
define('EXTLAN_76', 'Caractères incorrectes en nom de champs! Seul A-Z, a-z, 0-9 et _ sont autorisés.');
define('EXTLAN_77', 'Catégorie non supprimée. Les champs de cette catégorie doivent d’abord être effacés: ');
define('EXTLAN_78', 'Impossible de trouver --FILE-- nécessaire pour créer la table de données');


//textbox
define('EXTLAN_HELP_1', '<strong><i>Paramètres:</i></strong><br />size: taille du champ<br />maxlength: longueur maximale du champ<br /><br />class: classe CSS du champ<br />style: style CSS de la chaine<br /><br />regex: code de validation de l’expression régulière<br />regexfail: texte d’erreur de la validation.');
//radio buttons
define('EXTLAN_HELP_2', 'Entrez le texte pour chaque option dans la case “Valeur”. Ajoutez de nouvelles cases si besoin.');
//dropdown
define('EXTLAN_HELP_3', 'Entrez le texte pour chaque option de la liste déroulante dans la case “Valeur”. Ajoutez de nouvelles cases si besoin.');
//db field
define('EXTLAN_HELP_4', '<strong><i>Valeurs:</i></strong><br />Il doit <b>toujours</b> y avoir trois valeurs:<br /><ol><li>une table BdD</li><li>un champ ID (identifiant)</li><li>une valeur d’affichage</li></ol><br />');

//textarea
define('EXTLAN_HELP_5', 'Défini une zone de texte. Donnez la taille dans '.EXTLAN_15.' comme désiré.');
//integer
define('EXTLAN_HELP_6', 'Permet à l’utilisateur d’entrer une valeur numérique.');
//date
define('EXTLAN_HELP_7', 'Demande à l’utilisateur de sélectionner une date.');
// Language
define('EXTLAN_HELP_8', 'Permet à l’utilisateur de sélectionner une des langues installées.');
// Predefined list
define('EXTLAN_HELP_9', 'Spécifie une liste prédéfinie. La valeur du champ donne le type de liste (actuellement seul “fuseau horaire” est une entré valide).');
