<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté française e107
|   $Source: /cvsroot/touchatou/e107_french/e107_plugins/content/languages/French/lan_content_help.php,v $
|   $Revision: 1.9 $
|   $Date: 2007/08/12 20:24:33 $
|   $Author: marj_nl_fr $
+---------------------------------------------------------------+
*/

define("CONTENT_ADMIN_HELP_1", "Aide du Gestionnaire de contenu");

define("CONTENT_ADMIN_HELP_ITEM_1", "<i>Si vous n'avez pas encore créé de catégorie principale, faites le à la page <a href='".e_SELF."?cat.create'>Créer une nouvelle catégorie</a>.</i><br /><br /><strong>Catégorie</strong><br />Sélectionnez une catégorie dans le menu déroulant pour gérer le contenu de cette catégorie.<br /><br />La sélection d'une catégorie principale dans le menu déroulant affiche tous les items de cette catégorie principale.<br />La sélection d'une sous-catégorie affiche uniquement les items de la sous-catégorie indiquée.<br /><br />Vous pouvez également employer le menu de droite pour consulter tous les items de la catégorie indiquée."); //***

define("CONTENT_ADMIN_HELP_ITEM_2", "<strong>Choix par initiales</strong><br />Si plusieurs contenus débute avec la même lettre, des boutons de sélection de l'initiale de ces articles s'affichent. La sélection du bouton 'tous' affichera une liste de tous les articles de cette catégorie.<br /><br /><strong> Liste détaillée</strong><br / > Vous verrez une liste de tous les articles contenus avec leurs ID (identifiants), icônes, auteurs, titres [sous-titres] et leurs options.
<br /><br /><strong>Explications des icônes</strong><br />".CONTENT_ICON_USER.": lien vers le profil de l'auteur<br />".CONTENT_ICON_LINK.": lien vers le contenu<br />".CONTENT_ICON_EDIT.": édition du contenu<br />".CONTENT_ICON_DELETE.": suppression du contenu<br />");//***

define("CONTENT_ADMIN_HELP_ITEMEDIT_1", "<strong>Formulaire d'édition</strong><br />Vous pouvez éditer toutes les informations concernant ce contenu et mettre vos modifications à jour.<br /><br />Si vous désirez changer la catégorie de cet item, faites le en premier lieu. Après avoir choisi la catégorie correcte, modifier ou ajoutez les champs actuels, avant d'enregistrer vos modifications."); //***

define("CONTENT_ADMIN_HELP_ITEMCREATE_1", "<strong>Catégorie</strong><br />Sélectionnez une catégorie dans la case de sélection pour créer votre item.<br />"); //***

define("CONTENT_ADMIN_HELP_ITEMCREATE_2", "<strong>Formulaire de création</strong><br />Vous pouvez maintenant saisir les informations pour cet item et enregistrer.<br /><br />Soyez conscient du fait que les différentes catégories principales peuvent avoir des préférences différentes. De champs différents doivent alors être renseignés. C'est pourquoi vous devez toujours choisir une catégorie avant de remplir les autres champs!"); //***

define("CONTENT_ADMIN_HELP_CAT_1", "<i>Cette page affiche toutes les catégories et sous-catégories actuelles.</i><br /><br /><strong>Liste détaillée</strong><br />Une liste détaillée de toutes les sous-catégories et de leurs ID, icône, auteur, catégorie [sous-titre] et options<br /><br /><strong>Explications des icônes</strong><br />".CONTENT_ICON_USER.": lien vers le profil de l'auteur<br />".CONTENT_ICON_LINK.": lien vers la catégorie<br />".CONTENT_ICON_EDIT.": éditer la catégorie<br />".CONTENT_ICON_DELETE.": supprimer la catégorie<br />"); //***

define("CONTENT_ADMIN_HELP_CAT_2", "<i>Cette page vous permet de créer une nouvelle catégorie</i><br /><br />Choisissez toujours la catégorie parente avant de remplir d'autres champs!<br /><br />Cela doit être effectuer en premier car des préférences spécifiques à certaines catégories doivent être chargées par le système.<br /><br />Par défaut, la page de création de catégorie est affichée afin de créez une nouvelle catégorie principale."); //***

define("CONTENT_ADMIN_HELP_CAT_3", "<i>Cette page affiche le formulaire d'édition des catégories.</i><br /><br /><strong>Edition de catégorie</</strong><br />vous pouvez éditer les informations pour cette (sous) catégorie et enregistrer vos changements.<br /><br /> Si vous désirez changer de catégorie mère, faites en premier. Une fois la catégorie correcte enregistrée vous pouvez éditer les autres champs."); //***

define("CONTENT_ADMIN_HELP_ORDER_1", "<i>Cette page affiche toutes les catégories et sous-catégories actuelles.</i><br /><br /><strong>Liste détaillée</strong><br />Vous pouvez voir l'ID et le nom de la catégorie. Ainsi que plusieurs options pour gérer l'ordre de ces catégories.<br /><br /><strong>Explications des icônes</strong><br />".CONTENT_ICON_USER.": lien vers le profil de l'auteur<br />".CONTENT_ICON_LINK.": lien vers la catégorie<br />".CONTENT_ICON_ORDERALL.": gère l'ordre global des items de contenu de la catégorie spécifiée.<br />".CONTENT_ICON_ORDERCAT.": gère l'ordre des items de contenu de la catégorie spécifiée.<br />".CONTENT_ICON_ORDER_UP.": le bouton 'vers le haut' permet de déplacer un item vers le haut.<br />".CONTENT_ICON_ORDER_DOWN.": le bouton 'vers le le bas' permet de déplacer un item vers le bas.<br /><br /><strong>Ordre</strong><br />Vous pouvez manuellement ordonner les catégories de chaque catégorie parente. Changez les valeurs dans les listes de sélection pour ordonner selon votre souhait et cliquez le bouton de mise à jour afin de sauvegarder le nouvel ordre.<br />"); //***

define("CONTENT_ADMIN_HELP_ORDER_2", "<i>Cette page affiche tous les articles de la catégorie que vous avez sélectionné.</i><br /><br /><strong>Liste détaillée</strong><br />Elle affiche l'ID, l'auteur et le titre du contenu. Plusieurs options gérant l'ordre des contenus sont disponibles<br /><br /><strong>Explications des icônes</strong><br />".CONTENT_ICON_USER.": lien vers le profil de l'auteur<br />".CONTENT_ICON_LINK.": lien vers le contenu<br />".CONTENT_ICON_ORDER_UP.": le bouton 'vers le haut' permet de déplacer un item vers le haut.<br />".CONTENT_ICON_ORDER_DOWN.": le bouton 'vers le le bas' permet de déplacer un item vers le bas.<br /><br /><strong>Ordre</strong><br />Vous pouvez ordonner manuellement les catégories de chaque catégorie mère. Changez les valeurs dans les listes de sélection pour ordonner selon votre souhait et cliquez le bouton de mise à jour afin de sauvegarder le nouvel ordre.<br />"); //***

define("CONTENT_ADMIN_HELP_ORDER_3", "<i>Cette page affiche les items de contenus de la catégorie sélectionnée.</i><br /><br /><strong>Liste détaillée</strong><br />Elle affiche l'ID, l'auteur et le titre du contenu. Plusieurs options gérant l'ordre des contenus sont disponibles<br /><br /><strong>Explications des icônes</strong><br />".CONTENT_ICON_USER.": lien vers le profil de l'auteur<br />".CONTENT_ICON_LINK.": lien vers le contenu<br />".CONTENT_ICON_ORDER_UP.": le bouton 'vers le haut' permet de déplacer un contenu au dessus du précédent dans la liste.<br />".CONTENT_ICON_ORDER_DOWN.": le bouton 'vers le le bas' permet de déplacer un item vers le bas.<br /><br /><strong>Ordre</strong><br />Vous pouvez ordonner manuellement les catégories de chaque catégorie mère.Changez les valeurs dans les listes de sélection pour ordonner selon votre souhait et cliquez le bouton de mise à jour afin de sauvegarder le nouvel ordre.<br />"); //***

define("CONTENT_ADMIN_HELP_OPTION_1", "Sur cette page vous pouvez sélectionner une catégorie principale pour changer ses options ou pour changer les préférences par défaut.<br /><br /><strong>Explications des icônes</strong><br />".CONTENT_ICON_USER.": lien vers le profil de l'auteur<br />".CONTENT_ICON_LINK.": lien vers la catégorie<br />".CONTENT_ICON_OPTIONS.": éditer les options<br /><br /><br />Les préférences par défaut sont employées uniquement lors de la création de nouvelles catégories principales. Ainsi, lorsque vous créez une nouvelle catégorie principale, ces préférences par défaut seront utilisées. Vous pouvez les changer afin de vous assurer que les catégories principales nouvellement créées aient ces préférences.
<br /><br />
Chaque catégorie principale a son propre jeu d'options. Elles sont uniques pour cette catégorie.<br /><br />
<strong>Héritage</strong>La case à cocher Héritage permet de remplacer les options individuelles de chaque catégorie principale par les préférences par défaut."); //***

define("CONTENT_ADMIN_HELP_MANAGER_1", "Cette page liste toutes les catégories. Vous pouvez gérer les options du 'Gestionnaire personnel de contenu' pour chacune des catégories en cliquant l'icône.<br /><br /><strong>Explications des icônes</strong><br />".CONTENT_ICON_USER.": lien vers le profil de l'auteur<br />".CONTENT_ICON_LINK.": lien vers la catégorie<br />".CONTENT_ICON_CONTENTMANAGER_SMALL.": gestionnaire personnel de contenu pour cette catégorie.<br />"); //***

define("CONTENT_ADMIN_HELP_MANAGER_2", "<i>Sur cette page vous pouvez assigner des groupes à la catégorie choisie</i><br /><br /><strong> Gestionnaire personnel de contenu</strong><br />Vous pouvez sélectionner des groupes pour différents types de gestionnaires personnels de contenu. actuellement il existe 3 types de gestionnaires:<br /><br />
Appobation des propositions: les membres de ce groupes peuvent approuver des articles proposés<br /><br />Gestionnaire personnel: les membres de ce groupes peuvent gérer leurs articles personnel<br /><br />Responsable de catégorie: les membres de ce groupes peuvent gérer tous les items de cette catégorie."); //***

define("CONTENT_ADMIN_HELP_SUBMIT_1", "<i> Cette page liste les items proposés par les membres.</i><br /><br /><strong>Liste détaillée</strong><br />Une liste des contenus proposés avec l'ID, l'icône, la catégorie principale, le titre [le sous-titre], l'auteur et les options.<br /><br /><strong>Options</strong><br />Vous pouvez poster ou supprimer un item en utilisant les boutons."); //***

define("CONTENT_ADMIN_HELP_OPTION_DIV_1", "Cette page vous permet de gérer les options de création de contenu pour les administrateurs (ou via le gestionnaire personnel).<br /><br />Vous pouvez définir quelles sections sont disponibles lorsque un admin veut créer un nouvel item.<br /><br />
<strong>Étiquettes de données personnalisées</strong><br />
Vous pouvez autoriser à ajouter des champs facultatifs au contenu en utilisant ces étiquettes de données personnalisées. Ces champs facultatifs ont une clef vide = > valeur paire . Par exemple: vous pourriez ajouter un champ clef pour 'photographe' et remplir le champ de valeur 'toutes mes photos'.
Tant cette clef, que les champs de valeurs seront des cases à texte qui apparaitront vide dans le formulaire de création.
<br /><br /><strong>Étiquettes de données prédéfinies</strong><br />En plus des étiquettes de données personnalisées, vous pouvez fournir des étiquettes de données prédéfinies.
La différence est que dans les étiquettes de données prédéfinies, on donne le champ clef au départ ainsi l'utilisateur devra uniquement renseigner le champ de valeur pour ce champ prédéfinies. Dans le même exemple que ci-dessus le 'photographe' peut être prédéterminé et l'utilisateur devra remplir 'toutes mes photos'. Vous pouvez choisir le type d'élément en choisissant une option dans la case de sélection. Dans la fenêtre contextuelle (Popup), vous pouvez remplir toute l'information nécessaire à l'étiquette de données prédéfinies.<br />");

define("CONTENT_ADMIN_HELP_OPTION_DIV_2", "Ces options affectent le contenu proposé par l'utilisateur.<br /><br /> Vous pouvez y définir les options qui seront disponibles lors de la création de contenus à proposer par les utilisateurs.<br /><br />".CONTENT_ADMIN_OPT_LAN_11.":<br />".CONTENT_ADMIN_OPT_LAN_12."");

define("CONTENT_ADMIN_HELP_OPTION_DIV_3", "Dans ces options, vous pouvez définir où les images et les fichiers sont stockés..<br /><br />Vous pouvez définir quel thème sera employé par cette catégorie principale. Vous pouvez créer des thèmes supplémentaires en copiant (et renommant) la totalité du répertoire 'default' dans votre répertoire de templates.<br /><br />Vous pouvez définir un schéma d'affichage par défaut pour vos contenus. Vous pouvez créer de nouveau schéma d'affichage en créant un fichier content_content_template_<br />XXX.php dans votre répertoire 'templates/default'. Ces schémas d'affichage peuvent être employés pour donner à chaque contenu dans cette catégorie un format d'affichage différent.<br /><br />");

define("CONTENT_ADMIN_HELP_OPTION_DIV_4", "Ces options sont employés dans toutes les pages du plugin.");

define("CONTENT_ADMIN_HELP_OPTION_DIV_5", "<i>Ces options ont un effet sur la section 'Gestionnaire personnel' à l'intérieur de la section d'administration du gestionnaire de contenu.</i><br /><br />".CONTENT_ADMIN_OPT_LAN_63."");

define("CONTENT_ADMIN_HELP_OPTION_DIV_6", "Ces options sont utilisés dans le Menu de cette catégorie si vous avez activé son menu.<br /><br />".CONTENT_ADMIN_OPT_LAN_68."<br /><br />".CONTENT_ADMIN_OPT_LAN_118.":<br />".CONTENT_ADMIN_OPT_LAN_119."<br /><br />");

define("CONTENT_ADMIN_HELP_OPTION_DIV_7", "Ces options affectent les détails aperçus des contenus.<br /><br />Cet aperçu est donné sur plusieurs pages, comme la page des nouveautés, la page voir les items d'une catégorie et la page des contenus des auteurs.<br /><br />".CONTENT_ADMIN_OPT_LAN_68."");

define("CONTENT_ADMIN_HELP_OPTION_DIV_8", "Les pages catégories affichent les informations sur les catégories de contenu qui sont DANS la catégorie principale.<br /><br />Deux zones distinctes sont présentes:<br /><br /><strong>Page liste des catégories:</strong><br />content.php?cat.list.#<br />Cette page affiche toutes les catégories d'un parent (une catégorie principale)<br /><br /><strong>Page aperçu d'une catégorie:</strong><br />content.php?cat.#<br />Cette page affiche tous les items d'une catégorie, optionnellement les sous-catégories et leurs items.<br />");

define("CONTENT_ADMIN_HELP_OPTION_DIV_9", "Les Pages de Contenus montre le contenu.<br /><br />Vous pouvez définir quelles sections seront montrées en cochant/décochant les boites.<br /><br />Vous pouvez montrer l'adresse email d'un auteur non-membre.<br /><br />Vous pouvez choisir l'affichage ou non des icônes email/print/pdf, de l'évaluation et des commentaires.<br /><br />".CONTENT_ADMIN_OPT_LAN_74."");

define("CONTENT_ADMIN_HELP_OPTION_DIV_10", "La page Auteur montre une liste de tous les auteurs de contenu de cette catégorie principale.<br /><br />Vous pouvez définir quelles sections seront montrées en cochant/décochant les boites.<br /><br />Vous pouvez limiter le nombre d'items à montrer par page.<br />");

define("CONTENT_ADMIN_HELP_OPTION_DIV_11", "La page Archive montre tous les contenus de cette catégorie principale.<br /><br />Vous pouvez définir quelles sections seront affichées en cochant/décochant les boites.<br /><br />Vous pouvez montrer l'adresse email d'un auteur non-membre.<br /><br />Vous pouvez limiter le nombre d'items à montrer par page.<br /><br />".CONTENT_ADMIN_OPT_LAN_66."<br /><br />".CONTENT_ADMIN_OPT_LAN_68."");

define("CONTENT_ADMIN_HELP_OPTION_DIV_12", "La page<strong>Top Évaluation</strong> affiche tous les contenus qui ont été évalués par les usagers.<br /><br />Vous pouvez définir quelles sections seront affichées en cochant/décochant les boites.<br /><br />Vous pouvez montrer l'adresse email d'un auteur non-membre.");

define("CONTENT_ADMIN_HELP_OPTION_DIV_13", "La page des<strong>Top Qualités</strong> affiche tous les contenus qui ont reçus un score de leur auteur.<br /><br />Vous pouvez définir quelles sections seront affichées en cochant/décochant les boites.<br /><br />Vous pouvez montrer l'adresse email d'un auteur non-membre.");

define("CONTENT_ADMIN_HELP_OPTION_DIV_14", "Cette page permet de définir des options pour pour la page admin de création des catégories.<br /><br />Vous pouvez définir quelles sections sont disponibles quand un admin (ou personne habilitée) crée une nouvelle catégorie.");

?>