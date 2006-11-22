<?php 
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). License GNU/PGL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/lan_users_extended.php,v $
|     $Revision: 1.5 $
|     $Date: 2006-11-22 12:13:44 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
  define("EXTLAN_1", "Nom");
  define("EXTLAN_2", "Type");
  define("EXTLAN_3", "Valeur");
  define("EXTLAN_4", "Requis");
  define("EXTLAN_5", "Applicable");
  define("EXTLAN_6", "Accès en lecture");
  define("EXTLAN_7", "Accès en écriture");
  define("EXTLAN_8", "Action");
  define("EXTLAN_9", "Champs étendus du profil");
  define("EXTLAN_10", "Nom du champ ");
  define("EXTLAN_11", "C'est le nom du champ qui sera stocké dans la table MySQL, il doit être différent de tout autre champ déjà stocké.");
  define("EXTLAN_12", "Titre du champ ");
  define("EXTLAN_13", "C'est le nom du champ tel qu'il sera affiché dans les pages rendues (profil, etc.)");
  define("EXTLAN_14", "Type de champ :");
  define("EXTLAN_15", "Paramètres du Type de champ");
  define("EXTLAN_16", "Valeur par défaut");
  define("EXTLAN_17", "Entrez chaque valeurs disponibles dans une case différente<br/>Voyez l'aide à droite pour les tables MySQL.");
  define("EXTLAN_18", "Requis");
  define("EXTLAN_19", "Les utilisateurs devront entrer une valeur obligatoirement dans ce champ en mettant à jour leur profil.");
  define("EXTLAN_20", "Déterminez à quels utilisateurs ce champ s'appliquera.");
  define("EXTLAN_21", "Ceci déterminera qui pourra modifier ce champ dans les paramètres du profil de l'utilisateur.");
  define("EXTLAN_22", "Ceci déterminera qui peut voir la valeur dans la profil de l'utilisateur.");
  define("EXTLAN_23", "Ajouter le champ");
  define("EXTLAN_24", "Mettre à jour");
  define("EXTLAN_25", "déplacer en bas");
  define("EXTLAN_26", "déplacer en haut");
  define("EXTLAN_27", "Confirmer la suppression");
  define("EXTLAN_28", "Aucun champ étendu de profil défini");
  define("EXTLAN_29", "Champ étendu de profil sauvegardée.");
  define("EXTLAN_30", "Champ étendu de profil supprimé");
  //  define("EXTLAN_31", "Menu Champ étendu de profil");
  //  define("EXTLAN_32", "Page d'Acceuil Champs étendu de profil ");
  define("EXTLAN_33", "Annuler Édition");
  define("EXTLAN_34", "Champs étendu de profil");
  define("EXTLAN_35", "Catégories");
  define("EXTLAN_36", "Pas de catégorie assignée");
  define("EXTLAN_37", "Pas de catégorie définie");
  define("EXTLAN_38", "Nom de catégorie");
  define("EXTLAN_39", "Ajouter une catégorie");
  define("EXTLAN_40", "Catégorie créée");
  define("EXTLAN_41", "Catégorie suprimée");
  define("EXTLAN_42", "Mettre à jour la catégorie");
  define("EXTLAN_43", "Catégorie mise à jour");
  define("EXTLAN_44", "Catégorie");
  define("EXTLAN_45", "Ajouter un nouveau champ");
  define("EXTLAN_46", "Aide");
  define("EXTLAN_47", "Ajouter un nouveau paramètre");
  define("EXTLAN_48", "Ajouter une nouvelle valeur");
  define("EXTLAN_49", "Autoriser les utilisateurs à cacher");
  define("EXTLAN_50", "Établir sur OUI autorisera l'utilisateur à cacher cette valeur aux non-administrateurs du site.");
  define("EXTLAN_51", "Tous les paramètres w3c valides peuvent être introduits ici<br />ie <i><strong>class='tbox' size='40' maxlength='80'</i></strong>");
  define("EXTLAN_52", "code validation regex");
  define("EXTLAN_53", "Entrer le code de l'Expression Régulière (<i>REGEX</i>) auquel l'entrée devra correspondre pour être valide.<br />**les délimiteurs regex sont requis**");
  define("EXTLAN_54", "Texte d'échec regex");
  define("EXTLAN_55", "Entrer un message d'erreur. Cela s'affichera si la validation regex a échouée.");
  define("EXTLAN_56", "Champs prédéfinis");
  define("EXTLAN_57", "Activé");
  define("EXTLAN_58", "Non Activé");
  define("EXTLAN_59", "Activé");
  define("EXTLAN_60", "Désactivé");
  define("EXTLAN_61", "Aucun");
  define("EXTLAN_62", "Choisissez une Table");
  define("EXTLAN_63", "Choisissez un champ Id");
  define("EXTLAN_64", "Choisissez une valeur d'affichage");
  define("EXTLAN_65", "Non - Ne sera pas affiché sur la page d'inscription");
  define("EXTLAN_66", "Oui - Sera affiché sur la page d'inscription");
  define("EXTLAN_67", "Non - Sera affiché sur la page d'inscription");
define("EXTLAN_68", "Champ:");
define("EXTLAN_69", "a été activé");
define("EXTLAN_70", "ERREUR!! Champ:");
define("EXTLAN_71", "n'a pas été activé!");
define("EXTLAN_72", "a été désactivé");
define("EXTLAN_73", "n'a pas été désactivé!");
define("EXTLAN_74", "est un nom de champ réservé et ne peut être utilisé.");
  //textbox
  define("EXTLAN_HELP_1", "<strong><i>Paramètres:</i></strong><br />size - Taille du champs<br />maxlength - longueur maximum du champs<br /><br />class - css class du champs<br />style - css style string<br /><br />regex - code de validation regex<br />regexfail - texte d'échec de validation");
  //radio buttons
  define("EXTLAN_HELP_2", "Ce sera le texte d'aide des boutons radio");
  //dropdown
  define("EXTLAN_HELP_3", "Ce sera le texte d'aide des listes déroulante");
  //db field
  define("EXTLAN_HELP_4", "<strong><i>Valeurs:</i></strong><br />Il devrait TOUJOURS y avoir trois valeurs à donner :<br /><ol><li>dbtable</li><li>field containing id</li><li>field containing value</li></ol><br />");
  //textarea
  define("EXTLAN_HELP_5", "Ce sera le texte d'aide des zones de texte");
  //integer
  define("EXTLAN_HELP_6", "Ce sera le texte d'aide au sujet des entiers");
  //date
  define("EXTLAN_HELP_7", "Ce sera le texte d'aide au sujet des dates ");
  define("EXTLAN_PRE1", "Résidence");
  define("EXTLAN_PRE2", "AIM");
  define("EXTLAN_PRE3", "ICQ");
  define("EXTLAN_PRE4", "Yahoo!");
  define("EXTLAN_PRE5", "Page d'accueil");
  ?>
