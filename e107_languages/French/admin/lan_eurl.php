<?php
/*
+---------------------------------------------------------------+
|        e107 website content management system French Language File
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
|        Last Modified: 2016/02/01 15:22:26
|
|        $Author: Olivier Troccaz $
+---------------------------------------------------------------+
*/
define("LAN_EURL_NAME", "Gérer les URL du site");
define("LAN_EURL_NAME_CONFIG", "Profils");
define("LAN_EURL_NAME_ALIASES", "Alias");
define("LAN_EURL_NAME_SETTINGS", "Paramètres généraux");
define("LAN_EURL_NAME_HELP", "Aide");
define("LAN_EURL_EMPTY", "La liste est vide");
define("LAN_EURL_LEGEND_CONFIG", "Choisir le profil URL par zone du site");
define("LAN_EURL_LEGEND_ALIASES", "Configurer les alias d'URL de base par profil URL");
define("LAN_EURL_DEFAULT", "Par défaut");
define("LAN_EURL_PROFILE", "Profil");
define("LAN_EURL_INFOALT", "Informations");
define("LAN_EURL_PROFILE_INFO", "Informations du profil non disponibles");
define("LAN_EURL_LOCATION", "Emplacement du profil");
define("LAN_EURL_LOCATION_NONE", "Fichier de configuration non disponible");
define("LAN_EURL_FORM_HELP_DEFAULT", "Alias lorsque dans la langue par défaut.");
define("LAN_EURL_FORM_HELP_ALIAS_0", "La valeur par défaut est");
define("LAN_EURL_FORM_HELP_ALIAS_1", "Alias lorsque dans");
define("LAN_EURL_FORM_HELP_EXAMPLE", "URL de base");
define("LAN_EURL_ERR_ALIAS_MODULE", "Impossible d'enregistrer les alias '%1\$s' - il y a un profil URL système portant le même nom. Veuillez choisir une autre valeur d'alias pour profil URL du système '%2\$s'");
define("LAN_EURL_SURL_UPD", "  URL SEF ont été mises à jour.");
define("LAN_EURL_SURL_NUPD", "  URL SEF n'ont pas été mises à jour.");
define("LAN_EURL_SETTINGS_PATHINFO", "Supprimer le nom du fichier de l'URL");
define("LAN_EURL_SETTINGS_MAINMODULE", "Espace de noms racine associé");
define("LAN_EURL_SETTINGS_MAINMODULE_HELP", "Choisissez quelle zone de site sera connectée avec l'URL de base de votre site. Exemple : quand Articles est votre espace de nom racine http://votresite.fr/Articles-Elément-Titre sera associé avec les articles (la page de l'élément à voir sera résolue)");
define("LAN_EURL_SETTINGS_REDIRECT", "Rediriger vers le système de page introuvable");
define("LAN_EURL_SETTINGS_REDIRECT_HELP", "Si défini à faux, la page 'non trouvée' sera directement rendue (sans redirection du navigateur)");
define("LAN_EURL_SETTINGS_SEFTRANSLATE", "Type de création de chaîne SEF automatisée");
define("LAN_EURL_SETTINGS_SEFTRANSLATE_HELP", "Choisir comment sera assemblée la chaîne SEF lorsque c'est construit automatiquement à partir d'un titre (par exemple dans les articles, des pages personnalisées, etc.)");
define("LAN_EURL_SETTINGS_SEFTRTYPE_NONE", "Simplement, sécurisez-le");
define("LAN_EURL_SETTINGS_SEFTRTYPE_DASHL", "tirets-dash-en-minuscule");
define("LAN_EURL_SETTINGS_SEFTRTYPE_DASHC", "Tirets-Dash-Première-Lettre-En-Capitale");
define("LAN_EURL_SETTINGS_SEFTRTYPE_DASH", "Tirets-dash-avec-aucun-changement-de-casse");
define("LAN_EURL_SETTINGS_SEFTRTYPE_UNDERSCOREL", "tirets_underscore_en_minuscule");
define("LAN_EURL_SETTINGS_SEFTRTYPE_UNDERSCOREC", "Tirets_Underscore_Première_Lettre_En_Capitale");
define("LAN_EURL_SETTINGS_SEFTRTYPE_UNDERSCORE", "Tirets_underscore_avec_aucun_changement_de_casse");
define("LAN_EURL_SETTINGS_SEFTRTYPE_PLUSL", "séparateur+plus+en+minuscule");
define("LAN_EURL_SETTINGS_SEFTRTYPE_PLUSC", "Séparateur+Plus+Première+Lettre+En+Capitale");
define("LAN_EURL_SETTINGS_SEFTRTYPE_PLUS", "Séparateur+plus+avec+aucun+changement+de+casse");
define("LAN_EURL_MODREWR_DESCR", "Supprime les noms de fichier de script d'entrée (index.php/) de vos URL. Vous aurez besoin de mod_rewrite installé et en cours d'exécution sur votre serveur (Apache Web Server). Après l'activation de ce paramètre, allez dans le dossier racine de votre site, renommez htaccess.txt en .htaccess et modifiez la directive <em>'RewriteBase'</em> si nécessaire.");
define("LAN_EURL_MENU", "URL de site");
define("LAN_EURL_MENU_CONFIG", "Profils d'URL");
define("LAN_EURL_MENU_ALIASES", "Alias");
define("LAN_EURL_MENU_SETTINGS", "Paramètres");
define("LAN_EURL_MENU_HELP", "Aide");
define("LAN_EURL_MENU_PROFILES", "Profils");
define("LAN_EURL_UC", "En construction");
define("LAN_EURL_CORE_MAIN", "Espace de nom racine du site - alias non utilisé");
define("LAN_EURL_FRIENDLY", "Convivial");
define("LAN_EURL_LEGACY", "URL directes héritées.");
define("LAN_EURL_REWRITE_LABEL", "URL conviviales");
define("LAN_EURL_REWRITE_DESCR", "Moteur de recherche et URL conviviales.");
define("LAN_EURL_CORE_NEWS", "Articles");
define("LAN_EURL_NEWS_REWRITEF_LABEL", "Complète les URL conviviales (aucune performance et la plupart de l'environnement)");
define("LAN_EURL_NEWS_REWRITEF_DESCR", "");
define("LAN_EURL_NEWS_REWRITE_LABEL", "URL conviviales sans ID (aucune performance, plus convivial)");
define("LAN_EURL_NEWS_REWRITE_DESCR", "Montre l'analyse et l'assemblage manuel d'un lien.");
define("LAN_EURL_NEWS_REWRITEX_LABEL", "URL conviviales avec ID (performance sensée)");
define("LAN_EURL_NEWS_REWRITEX_DESCR", "Montre l'analyse et l'assemblage automatique d'un lien en se basant sur des règles de base prédéfinies.");
define("LAN_EURL_CORE_USER", "Utilisateurs");
define("LAN_EURL_USER_REWRITE_LABEL", "URL conviviales");
define("LAN_EURL_USER_REWRITE_DESCR", "Moteur de recherche et URL conviviales.");
define("LAN_EURL_CORE_PAGE", "Pages personnalisées");
define("LAN_EURL_PAGE_SEF_LABEL", "URL conviviales avec ID (performance)");
define("LAN_EURL_PAGE_SEF_DESCR", "Moteur de recherche et URL conviviales.");
define("LAN_EURL_PAGE_SEFNOID_LABEL", "URL conviviales sans ID (aucune performance, plus convivial)");
define("LAN_EURL_PAGE_SEFNOID_DESCR", "Moteur de recherche et URL conviviales.");
define("LAN_EURL_CORE_SEARCH", "Recherche");
define("LAN_EURL_SEARCH_DEFAULT_LABEL", "URL de recherche par défaut");
define("LAN_EURL_SEARCH_DEFAULT_DESCR", "URL directe héritée.");
define("LAN_EURL_SEARCH_REWRITE_LABEL", "URL conviviale");
define("LAN_EURL_SEARCH_REWRITE_DESCR", "");
define("LAN_EURL_CORE_SYSTEM", "Système");
define("LAN_EURL_SYSTEM_DEFAULT_LABEL", "URL du système par défaut");
define("LAN_EURL_SYSTEM_DEFAULT_DESCR", "URL pour les pages telles que 'Non trouvé', 'Accès refusé', etc.");
define("LAN_EURL_SYSTEM_REWRITE_LABEL", "URL système conviailes");
define("LAN_EURL_SYSTEM_REWRITE_DESCR", "URL pour les pages telles que 'Non trouvé', 'Accès refusé', etc.");
define("LAN_EURL_CORE_INDEX", "Page d'accueil");
define("LAN_EURL_CORE_INDEX_INFO", "La page d'accueil ne peut pas avoir d'alias.");
define("LAN_EURL_REBUILD", "Reconstruire");


?>