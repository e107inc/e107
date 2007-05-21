<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/lan_users_extended.php,v $
|     $Revision: 1.8 $
|     $Date: 2007-05-21 10:09:56 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
define("EXTLAN_1", "Nom");
define("EXTLAN_2", "Prévisualisation");
define("EXTLAN_3", "Valeur");
define("EXTLAN_4", "Requis");
define("EXTLAN_5", "Applicable");
define("EXTLAN_6", "Accès en lecture");
define("EXTLAN_7", "Accès en écriture");
define("EXTLAN_8", "Action");
define("EXTLAN_9", "Champs étendus du profil");
define("EXTLAN_10", "Nom du champ ");
define("EXTLAN_11", "C'est le nom du champ tel que stocké dans la table, il doit être unique et ne pas être employé dans la table principale des usagers");
define("EXTLAN_12", "Champ texte");
define("EXTLAN_13", "C'est le nom du champ affiché dans les pages");
define("EXTLAN_14", "Type de champ :");
define("EXTLAN_15", "Paramètres du type de champ");
define("EXTLAN_16", "Valeur par défaut");
define("EXTLAN_17", "Entrez une valeur par case (boite texte)<br/>Pour une table BdD voir l'aide.");
define("EXTLAN_18", "Requis");
define("EXTLAN_19", "Les utilisateurs devront obligatoirement entrer une valeur dans ce champ en mettant leurs paramètres à jour.");
define("EXTLAN_20", "Déterminez pour quels utilisateurs ce champ doit s'appliquer.");
define("EXTLAN_21", "Détermine qui verra ce champ dans ses paramètres utilisateur.");
define("EXTLAN_22", "Détermine qui peut voir la valeur (dans la page profil, les forums, etc.).<br />NOTE: Le paramètre 'Lecture Seule' la rendra visible uniquement pour le membre concerné et les admins.");
define("EXTLAN_23", "Ajouter le champ");
define("EXTLAN_24", "Mettre à jour");
define("EXTLAN_25", "déplacer vers le bas");
define("EXTLAN_26", "déplacer vers le haut");
define("EXTLAN_27", "Confirmer la suppression");
define("EXTLAN_28", "Aucun champ étendu de profil défini");
define("EXTLAN_29", "Champ étendu de profil sauvegardé.");
define("EXTLAN_30", "Champ étendu de profil supprimé");
define("EXTLAN_33", "Annuler Édition");
define("EXTLAN_34", "Champs étendus de profil");
define("EXTLAN_35", "Catégories");
define("EXTLAN_36", "Pas de catégorie assignée");
define("EXTLAN_37", "Pas de catégorie définie");
define("EXTLAN_38", "Nom de catégorie");
define("EXTLAN_39", "Ajouter une catégorie");
define("EXTLAN_40", "Catégorie créée");
define("EXTLAN_41", "Catégorie supprimée");
define("EXTLAN_42", "Mettre à jour la catégorie");
define("EXTLAN_43", "Catégorie mise à jour");
define("EXTLAN_44", "Catégorie");
define("EXTLAN_45", "Ajouter un nouveau champ");
define("EXTLAN_46", "Aide");
define("EXTLAN_47", "Ajouter un nouveau paramètre");
define("EXTLAN_48", "Ajouter une nouvelle valeur");
define("EXTLAN_49", "Autoriser les utilisateurs à cacher");
define("EXTLAN_50", "Établir sur OUI autorisera l'utilisateur à cacher cette valeur aux non-admins");
define("EXTLAN_51", "Tous les paramètres w3c valides peuvent être introduits ici<br />ie <i><strong>class='tbox' size='40' maxlength='80'</i></strong>");
define("EXTLAN_52", "Code de validation REGEX");
define("EXTLAN_53", "Entrer le code de l'Expression Régulière (<i>REGEX</i>) auquel l'entrée devra correspondre pour être valide.<br />**les délimiteurs regex sont requis**");
define("EXTLAN_54", "Texte d'échec REGEX");
define("EXTLAN_55", "Entrer un message d'erreur. Cela s'affichera si la validation regex a échouée.");
define("EXTLAN_56", "Champs prédéfinis");
define("EXTLAN_57", "Activé");
define("EXTLAN_58", "Non Activé");
define("EXTLAN_59", "Activé");
define("EXTLAN_60", "Désactivé");
define("EXTLAN_61", "Aucun");
define("EXTLAN_62", "Choisissez une table");
define("EXTLAN_63", "Choisissez un champ ID");
define("EXTLAN_64", "Choisissez une valeur d'affichage");
define("EXTLAN_65", "Non - Non affiché sur la page d'inscription");
define("EXTLAN_66", "Oui - Affiché sur la page d'inscription");
define("EXTLAN_67", "Non - Affiché sur la page d'inscription");
define("EXTLAN_68", "Le champ:");
define("EXTLAN_69", "est activé");
define("EXTLAN_70", "ERREUR!! Champ:");
define("EXTLAN_71", "n'a pas été activé!");
define("EXTLAN_72", "est désactivé");
define("EXTLAN_73", "n'a pas été désactivé!");
define("EXTLAN_74", "est un nom de champ réservé et ne peut être utilisé.");
define("EXTLAN_75", "Une erreur s'est produite en ajoutant un champ à la base de données.");
define("EXTLAN_76", "Caractères invalides dans le nom du champ - seuls A-Z, a-z, 0-9, '_' sont permis.");
define("EXTLAN_77", "Catégorie non supprimée - vous devez d'abord effacer les champs dans la catégorie :");
define("EXTLAN_HELP_1", "<strong><i>Paramètres:</i></strong><br />size: taille du champ<br />maxlength: longueur max du champ<br /><br />class: classe CSS du champ<br />style: style CSS de la chaîne<br /><br />regex: code REGEX de validation<br />regexfail - texte d'erreur de la validation");
define("EXTLAN_HELP_2", "Entrez le nom des options dans le champ 'Valeur' - un champ par option. Ajouter de nouveaux champs selon vos besoins.");
define("EXTLAN_HELP_3", "Entrez le nom des options dans le champ 'Valeur' - un champ par option. Ajouter de nouveaux champs selon vos besoins.");
define("EXTLAN_HELP_4", "<strong><i>Valeurs:</i></strong><br />Il doit TOUJOURS y avoir trois valeurs:<br /><ol><li>une table BdD</li><li>un champ ID (identifiant)</li><li>une valeur d'affichage</li></ol><br />");
define("EXTLAN_HELP_5", "Défini une zone de texte libre. (Gérez la taille dans le champ '".EXTLAN_15."' selon vos besoins.");
define("EXTLAN_HELP_6", "Permettre aux utilisateurs d'entrer une valeur numérique");
define("EXTLAN_HELP_7", "Requiert des utilisateurs d'entrer une date");
define("EXTLAN_HELP_8", "Permet aux utilisateurs de sélectionner un des langages installés");


?>