<?php
/*
+---------------------------------------------------------------+
|        e107 website content management system French Language File
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
|        Last Modified: 2016/01/20 17:59:34
|
|        $Author: Olivier Troccaz $
+---------------------------------------------------------------+
*/
define("LANINS_001", "Installation e107");
define("LANINS_002", "Étape");
define("LANINS_003", "1");
define("LANINS_004", "Sélection de la langue");
define("LANINS_005", "Veuillez choisir la langue à utiliser pendant l'installation");
define("LANINS_007", "4");
define("LANINS_008", "Contrôle des versions de PHP et MySQL / vérification des autorisations de fichiers");
define("LANINS_009", "Testez à nouveau les autorisations de fichiers");
define("LANINS_010", "Fichier non accessible en écriture :");
define("LANINS_010a", "Dossier non accessible en écriture :");
define("LANINS_012", "Les fonctions MySQL ne semblent pas exister. Cela signifie probablement que l'extension My SQL de PHP n'est pas installée ou que votre installation de PHP n'a pas été compilée avec le support MySQL.");
define("LANINS_013", "Votre numéro de version MySQL n’a pas pu être déterminé. Ce n'est pas une erreur fatale, alors, veuillez continuer l'installation, mais notez bien qu'e107 requiert MySQL 3.23 ou supérieure pour fonctionner correctement.");
define("LANINS_014", "Autorisations de fichiers");
define("LANINS_015", "Version de PHP");
define("LANINS_017", "PASSE");
define("LANINS_018", "Assurez-vous que tous les fichiers répertoriés existent et sont accessibles en écriture par le serveur. Cela implique normalement de faire un CHMOD 777 sur ceux-ci, mais les environnements varient - contactez votre hébergeur si vous avez des problèmes.");
define("LANINS_019", "La version PHP installée sur votre serveur n'est pas en mesure d'exécuter e107. e107 nécessite une version PHP au moins ". MIN_PHP_VERSION. " pour s'exécuter correctement. Mettez à niveau votre version PHP ou contacter votre hébergeur pour une mise à niveau.");
define("LANINS_021", "2");
define("LANINS_022", "Détails du serveur MySQL");
define("LANINS_023", "Veuillez entrer vos paramètres MySQL ici.

Si vous avez les permissions d'administrateur, vous pouvez créer une nouvelle base de données en cochant la case, si non, vous devez créer une base de données ou en utiliser une pré-existante.

Si vous n'avez qu'une seule base de données, utilisez un préfixe afin que les autres scripts puissent partager la même base de données.
Si vous ne connaissez pas vos paramètres MySQL, contactez votre hébergeur.");
define("LANINS_024", "Serveur MySQL :");
define("LANINS_025", "Nom d'utilisateur MySQL :");
define("LANINS_026", "Mot de passe MySQL :");
define("LANINS_027", "Base de données MySQL :");
define("LANINS_028", "Créer la base de données ?");
define("LANINS_029", "Préfixe de table :");
define("LANINS_030", "Le serveur MySQL que vous voulez qu'e107 utilise. Il peut également inclure un numéro de port, par exemple 'hostname: port' ou un chemin d'accès à un socket local par exemple ':/path/to/socket' pour l'hôte local.");
define("LANINS_031", "Le nom d'utilisateur que vous souhaitez qu'e107 utilise pour se connecter à votre serveur MySQL");
define("LANINS_032", "Le mot de passe pour l'utilisateur que vous venez juste d'entrer. Il ne doit pas contenir de guillemets simples ou doubles.");
define("LANINS_033", "La base de données MySQL dans laquelle vous souhaitez qu'e107 réside, parfois référencée à un schéma. Elle doit commencer par une lettre. Si l'utilisateur dispose des autorisations pour créer une base de données, vous pouvez opter pour la création automatique de la base de données si elle n'existe pas déjà.");
define("LANINS_034", "Le préfixe que vous souhaitez qu'e107 utilise pour créer les tables d'e107. Utile pour des installations multiples d'e107 dans le schéma d'une seule base de données.");
define("LANINS_036", "3");
define("LANINS_037", "Vérification de la connexion MySQL");
define("LANINS_038", " et création de la base de données");
define("LANINS_039", "Veuillez vous assurer d'avoir bien renseigné tous les champs, surtout, le serveur MySQL, le nom d'utilisateur MySQL et la base de données MySQL (ceux-ci sont toujours requis par le serveur MySQL)");
define("LANINS_040", "Erreurs");
define("LANINS_041", "e107 n'a pas pu établir une connexion au serveur MySQL en utilisant les informations que vous avez entrées. Veuillez revenir à la page précédente et assurez vous que les informations sont correctes.");
define("LANINS_042", "Connexion au serveur MySQL établie et vérifiée.");
define("LANINS_043", "Impossible de créer la base de données, veuillez vous assurer que vous avez les autorisations appropriées pour créer des bases de données sur votre serveur.");
define("LANINS_044", "Base de données créée avec succès.");
define("LANINS_045", "Veuillez cliquer sur le bouton pour passer à l'étape suivante.");
define("LANINS_046", "5");
define("LANINS_047", "Détails de l'administrateur");
define("LANINS_048", "Extension EXIF");
define("LANINS_049", "Les deux mots de passe que vous avez entrés ne sont pas les mêmes. Veuillez retourner à l’étape précédente et réessayez.");
define("LANINS_050", "Extension XML");
define("LANINS_051", "Installé");
define("LANINS_052", "Pas installé");
define("LANINS_055", "Confirmation de l'installation");
define("LANINS_056", "6");
define("LANINS_057", "e107 a maintenant toutes les informations nécessaires pour terminer l'installation.
Veuillez cliquez sur le bouton pour créer les tables de la base de données et enregistrer tous vos paramètres.");
define("LANINS_058", "7");
define("LANINS_060", "Impossible de lire le fichier de donnéesSQL.
Veuillez vous assurer que le fichier [b]core_sql.php[/b] existe dans le répertoire [b]/e107_core/sql[/b].");
define("LANINS_061", "e107 n'a pas pu créer toutes les tables requises pour la base de données.
Veuillez effacer la base de données et corriger les problèmes avant de réessayer.");
define("LANINS_069", "e107 a été installé avec succès !

Pour des raisons de sécurité, vous devez maintenant définir les permissions de nouveau à 644 sur le fichier [b]e107_config.php[/b].

Veuillez aussi supprimer le fichier install.php de votre serveur après avoir cliqué sur le bouton ci-dessous.");
define("LANINS_070", "e107 n'a pas pu enregistrer le fichier de configuration principal sur votre serveur.

Veuillez vous assurer que le fichier [b]e107_config.php[/b] dispose des autorisations appropriées");
define("LANINS_071", "Installation complète");
define("LANINS_072", "Nom d'utilisateur administrateur");
define("LANINS_073", "C'est le nom que vous utiliserez pour vous connecter au site. Si vous le souhaitez, vous pouvez également l’utiliser comme nom d’affichage");
define("LANINS_074", "Nom d'affichage administrateur");
define("LANINS_076", "Mot de passe admin");
define("LANINS_077", "Veuillez saisir le mot de passe administrateur que vous souhaitez utiliser");
define("LANINS_078", "Confirmation du mot de passe administrateur");
define("LANINS_079", "Veuillez ressaisir le mot de passe administrateur pour confirmation");
define("LANINS_080", "Mail de l'administrateur");
define("LANINS_081", "Entrez votre adresse mail");
define("LANINS_083", "MySQL a signalé une erreur :");
define("LANINS_084", "Le programme d'installation n'a pas pu établir une connexion à la base de données");
define("LANINS_085", "Le programme d'installation n'a pas pu sélectionner la base de données :");
define("LANINS_086", "Les champs nom d’utilisateur administrateur, mot de passe et mail sont [b]obligatoires[/b]. Veuille revenir à la page précédente et s'assurez vous que les informations sont correctement renseignées.");
define("LANINS_105", "Un nom de base de données ou préfixe commençant par quelques chiffres suivi de 'e' ou 'E' n'est pas acceptable");
define("LANINS_106", "AVERTISSEMENT - e107 n'a pas accès en écriture aux répertoires et/ou fichiers répertoriés. Bien que cela ne gène en rien l'inastallation d'e107, certaines fonctionnalités nécessitant un accès en écriture à ces fichiers ne fonctionneront pas correctement. 
Si vous souhaitez les utiliser, il vous faudra modifier ces droits d’accès.");
define("LANINS_107", "Nom du site web");
define("LANINS_108", "Mon site web");
define("LANINS_109", "Thème de site web");
define("LANINS_111", "Inclure le contenu/la configuration");
define("LANINS_112", "Reproduire rapidement l'apparence de l'aperçu du thème ou de la démonstration. (Si disponible)");
define("LANINS_113", "Veuillez entrer un nom de site web");
define("LANINS_114", "Veuillez sélectionner un thème");
define("LANINS_115", "Nom du thème");
define("LANINS_116", "Type de thème");
define("LANINS_117", "Préférences du site web");
define("LANINS_118", "Installer des extensions");
define("LANINS_119", "Installer toutes les extensions qui peuvent être nécessaires au thème.");
define("LANINS_120", "8");
define("LANINS_121", "Le fichier e107_config.php existe déj�");
define("LANINS_122", "Vous avez probablement une installation déjà existante");
define("LANINS_123", "Optionel : votre nom public ou alias. Laissez vide pour utiliser le nom d'utilisateur");
define("LANINS_124", "Veuillez choisir un mot de passe d'au moins 8 caractères");
define("LANINS_125", "e107 a été installé avec succès !");
define("LANINS_126", "Pour des raisons de sécurité, vous devez maintenant définir les permissions de nouveau à 644 sur le fichier e107_config.php.");
define("LANINS_127", "La base de données [x] existe déjà. L'écraser ? (toutes les données seront perdues)");
define("LANINS_128", "Écraser");
define("LANINS_129", "Base de données introuvable.");
define("LANINS_134", "Installation");
define("LANINS_135", "de");
define("LANINS_136", "Suppression de la base de données existante");
define("LANINS_137", "Base de données existante trouvée");
define("LANINS_141", "Veuillez renseigner le formulaire avec vos paramètres MySQL. Si vous ne connaissez pas ces informations, veuillez contacter votre hébergeur. Vous pouvez survoler chacun de ces champs afin d'obtenir des informations complémentaires.");
define("LANINS_142", "IMPORTANT : veuillez renommer le fichier e107.htaccess en .htaccess");
define("LANINS_144", "IMPORTANT : veuillez copier/coller le contenu de [b]e107.htaccess[/b] dans votre fichier [b].htaccess[/b]. Attention à ne PAS écraser des données potentiellement  existantes.");
define("LANINS_145", "e107 v2.x nécessite que PHP [x] soit installé. Veuillez contacter votre hébergeur ou bien consultez les informations sur [y] avant de poursuivre.");


?>