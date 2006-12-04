<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). Licence GNU/GPL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/newsfeed/languages/French.php,v $
|     $Revision: 1.10 $
|     $Date: 2006-12-04 21:37:07 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
define("NFLAN_01", "Ressources d'".GLOBAL_LAN_NEWS_2."s");
define("NFLAN_02", "Cette extension récupére les ressources d'".GLOBAL_LAN_NEWS_1."s d'autres sites Web et les affiche selon vos préférences");
define("NFLAN_03", "Configurer les Ressources d'".GLOBAL_LAN_NEWS_2."s");
define("NFLAN_04", "L'extension Ressources d'".GLOBAL_LAN_NEWS_2."s a été installé avec succès. Pour ajouter une ressource d'".GLOBAL_LAN_NEWS_1." et configurer l'extension, retournez sur la page principale d'administration et cliquez sur l'icône ressources d'".GLOBAL_LAN_NEWS_1." de votre section Extension.");
define("NFLAN_05", "Éditer");
define("NFLAN_06", "Supprimer");
define("NFLAN_07", "Ressources d'".GLOBAL_LAN_NEWS_2."s existantes");
define("NFLAN_08", "Page d'accueil des Ressources d'".GLOBAL_LAN_NEWS_2."s");
define("NFLAN_09", "Création d'une ressource d'".GLOBAL_LAN_NEWS_1."s");
define("NFLAN_10", "URL vers la ressource rss");
define("NFLAN_11", "Chemin d'accès image");
define("NFLAN_12", "Activation");
define("NFLAN_13", "Nul part (désactivé)");
define("NFLAN_14", "Uniquement dans un menu");
define("NFLAN_15", "Créer la ressource");
define("NFLAN_16", "Mise à jour de la ressource d'".GLOBAL_LAN_NEWS_1."s");
define("NFLAN_17", "Entrez 'default' (sans les guillemets) dans la case pour utiliser l'image définie avec la ressource. Pour utiliser votre propre image, entrez le chemin complet vers celle-ci, ou laissez blanc si vous ne voulez pas d'images.");
define("NFLAN_18", "Intervalle de la mise à jour en secondes");
define("NFLAN_19", "Exemple: 3600. La ressource d'".GLOBAL_LAN_NEWS_1."s sera mise à jour toutes les heures.");
define("NFLAN_20", "Seulement sur la page principale des ressources d'".GLOBAL_LAN_NEWS_1."s");
define("NFLAN_21", "Dans les deux: menu et page principale des ressources d'".GLOBAL_LAN_NEWS_1."s");
define("NFLAN_22", "Choisissez où vous souhaitez voir s'afficher les ressources");
define("NFLAN_23", "Ressource d'".GLOBAL_LAN_NEWS_2."s ajoutée dans la base de données.");
define("NFLAN_24", "Champ(s) requis resté(s) vide(s).");
define("NFLAN_25", "Ressource d'".GLOBAL_LAN_NEWS_2."s mise à jour dans la base de données.");
define("NFLAN_26", "Intervalle de mise à jour");
define("NFLAN_27", "Options");
define("NFLAN_28", "URL");
define("NFLAN_29", "Ressources d'".GLOBAL_LAN_NEWS_1."s disponibles");
define("NFLAN_30", "Nom de la ressource");
define("NFLAN_31", "Retour à la liste des ressources");
define("NFLAN_32", "Aucune ressource avec ce numéro d'identification n'a pu être trouvée.");
define("NFLAN_33", "Date de publication: ");
define("NFLAN_34", "Inconnu");
define("NFLAN_35", "Posté par");
define("NFLAN_36", "Description");
define("NFLAN_37", "Brève description de la ressource, entrez 'default' pour utiliser la description définie dans la ressource");
define("NFLAN_38", "Titres");
define("NFLAN_39", "Détails");
define("NFLAN_40", "Ressource d'".GLOBAL_LAN_NEWS_2."s supprimée");
define("NFLAN_41", "Aucune Ressource d'".GLOBAL_LAN_NEWS_1."s définies pour le moment...");
define("NFLAN_42", "<strong>»</strong> <u>Nom de la ressource:</u><br />
	Le nom identifiant la ressource, peut être tout ce que vous souhaitez.
	<br /><br />
	<strong>»</strong> <u>URL vers la ressource rss :</u><br />
	L'adresse de la ressource rss
	<br /><br />
	<strong>»</strong> <u>Chemin vers l'image:</u><br />
	Si la ressource contient une image définie, entrez 'default' pour pouvoir l'utiliser. Pour employer votre propre image, entrer le chemin complet vers celle-ci. ou laisser blanc pour n'avoir aucune image.
	<br /><br />
	<strong>&raquo</strong> <u>Description:</u><br />
	Entrez une courte description de la ressource, où 'default' pour utiliser la description définie dans la ressource (s'il y  en a une).
	<br /><br />
	<strong>»</strong> <u>L'intervalle de la mise à jour en secondes:</u><br />
	Le nombre de secondes qui s'écoulent avant la mise à jour de la ressources, par exemple, 1800: 30 minutes, 3600: une heure.
	<br /><br />
	<strong>»</strong> <u>Activation:</u><br />
	Où vous désirez que les ressources d'".GLOBAL_LAN_NEWS_1."s soient affichés, si vous voulez les afficher dans un menu ressources vous devrez activer le menu ressources d'".GLOBAL_LAN_NEWS_1."s dans <a href='".e_ADMIN."menus.php'>la page de menus </a>.
	<br /><br />Pour une liste des ressources disponibles, voir <a href='http://www.syndic8.com/' rel='external'>syndic8.com</a> ou <a href='http://feedfinder.feedster.com/index.php' rel='external'>feedster.com</a> ou encore http://pretty-rss.snyke.com/Annuaire_RSS/Annuaire_RSS.html.");
define("NFLAN_43", "Aide RSS");
define("NFLAN_44", "Cliquer pour visionner");
define("NFLAN_45", "Nombre d'items affichés dans le menu");
define("NFLAN_46", "Nombre d'items affichés dans la page principale");
define("NFLAN_47", "0 ou vide pour tout afficher");
define("NFLAN_48", "Impossible de sauver la ligne dans la base de données.");
define("NFLAN_49", "Impossible de désérialiser les données RSS. Syntaxe non standard utilisée");


?>
