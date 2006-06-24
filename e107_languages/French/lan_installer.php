<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). License GNU/PGL
| Traducteurs: communauté française e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/lan_installer.php,v $
|     $Revision: 1.5 $
|     $Date: 2006-06-24 00:02:56 $
|     $Author: e107coders $
+---------------------------------------------------------------+
*/
  define("LANINS_001", "Installation d'e107 ");
  define("LANINS_002", "Étape");
  define("LANINS_003", "1");
  define("LANINS_004", "Sélection de la langue");
  define("LANINS_005", "Choisissez s'il vous plaît la langue à utiliser lors de la procédure d'installation");
  define("LANINS_006", "Installez la Langue");
  define("LANINS_007", "2");
  define("LANINS_008", "Contrôle de PHP et des Versions MySQL / Contrôle des Permissions de Fichier");
  define("LANINS_009", "Retestez les Permissions de Fichiers");
  define("LANINS_010", "Fichier non ouvert en écriture: ");
  define("LANINS_010a", "Dossier non ouvert en écriture");
  define("LANINS_011", "Erreur");
  define("LANINS_012", "Les Fonctions MySQL ne semblent pas exister. Cela signifie probablement que l'extension MySQL PHP n'est pas installée ou n'est pas configurée correctement.");
  define("LANINS_013", "Votre numèro de version MySQL n'a pas pu être déterminée . Cela pourrait signifier que votre serveur MySQL est est inutilisable, ou refuse les connexions .");
  define("LANINS_014", "Permissions de Fichiers");
  define("LANINS_015", "Version PHP ");
  define("LANINS_016", "MySQL");
  define("LANINS_017", "PASS");
  define("LANINS_018", "Assurez vous que tous les fichiers listés existent et sont ouvert en écriture pour le serveur. Cela implique normalement CHMODING de ceux-ci en 777, mais les environnements varient - Entrez en contact avec votre administrateur d'hébergement si vous avez des problèmes.");
  define("LANINS_019", "La version de PHP installé sur votre serveur n'est pas capable d'exécuter e107. E107 exige une version PHP d'au moins 4.3.0 pour fonctionner correctement. Mettez à niveau votre version PHP, ou entrez en contact avec votre hôte pour une mise à niveau.");
  define("LANINS_020", "Continuer l'installation");
  define("LANINS_021", "3");
  define("LANINS_022", "Détails du Serveur MySQL ");
  define("LANINS_023", "SVP entrez vos paramètres MySQL ici.

  Si vous avez les permissions d'administrateur vous pouvez créer une nouvelle base de données
  en cochant dans la case, si non vous devez créer une base de données ou employer une
  pré-existante.
  Si vous n'avez qu'une seule base de données utilisez un préfixe de sorte que d'autres scripts puissent partager la même base de données.
  Si vous ne connaissez pas vos paramètres MySQL contactez votre hébergeur.");
  define("LANINS_024", "Serveur MySQL :");
  define("LANINS_025", "Nom d'utilisateur MySQL:");
  define("LANINS_026", "Mot de passe MySQL:");
  define("LANINS_027", "Base de données MySQL:");
  define("LANINS_028", "Créez la Base de données?");
  define("LANINS_029", " Préfix de la Table:");
  define("LANINS_030", "Le serveur MySQL que vous voudriez voir employer pour e107. Il peut également inclure un nombre de port par exemple  'hostname:port' ou un chemin vers un socket local par exemple ':/path/to/socket'pour l'hébergeur local.");
  define("LANINS_031", "Le nom d'utilsateur que vous souhaiteriez voir utiliser par e107 pour se connectez à votre serveur de MySQL");
  define("LANINS_032", "Le mot de passe pour l'utilisateur que vous venez juste d'enregistrer");
  define("LANINS_033", "La base de données  MySQL dans laquelle vous souhaiteriez voir e107 résider, fait parfois référence à un schéma.
  Si vous en tant qu'utilisateur vous possédez les permissions pour créer une base de données vous pouvez choisir de créer la base de données automatiquement si elle n'est pas déjà exsistante");
  define("LANINS_034", "Le préfixe que vous souhaitez employer pour e107 lors de la création des tables de e107. Utile pour l'installation multiple d'e107 dans un seul schéma de base de données.");
  define("LANINS_035", "Continuer");
  define("LANINS_036", "4");
  define("LANINS_037", "Vérification de Connexion MySQL  ");
  define("LANINS_038", " et de création de Base de données ");
  define("LANINS_039", "Veuillez vous assurer d'avoir complété d'une manière primordiale tous les champs, le serveur de MySQL, le nom d'utilisateur MySQL , la base de données de MySQL (ceux-ci sont toujours exigés par le serveur de MySQL)");
  define("LANINS_040", "Erreurs");
  define("LANINS_041", "e107 n'a pas été en mesure d'établir une connexion au serveur MySQL
  en utilisant les informations que vous avez introduites. Revenez svp à la dernière page et assurez vous que vos données soient correctes.");
  define("LANINS_042", "Le raccordement au serveur MySQL a été établi et vérifié.");
  define("LANINS_043", "Incapable de créer la base de données, assurez-svp vous d'avoir permissions correctes pour créer des bases de données sur votre serveur.");
  define("LANINS_044", "Base de données créée avec succès.");
  define("LANINS_045", "Cliquez svp sur le bouton pour procéder à la prochaine étape.");
  define("LANINS_046", "5");
  define("LANINS_047", "Détails Administrateur ");
  define("LANINS_048", "Revenez vers la Dernière étape");
  define("LANINS_049", "Les deux mots de passe que vous avez entrés ne sont pas identiques.Veuillez retourner à l'étape précédante et essayer encore.");
  define("LANINS_050", "Extension XML ");
  define("LANINS_051", "Installé");
  define("LANINS_052", "Pas Installé");
  define("LANINS_053", "e107 .700 exige l'installation de l'Extension de PHP XML. Veuillez entrer en contact avec votre centre serveur ou lisez l'information à ");
  define("LANINS_054a", " avant de continuer");
  define("LANINS_054", "<a href='http://php.net/manual/fr/ref.xml.php' target='_blank'>php.net</a>avant de continuer");
  define("LANINS_055", "Confirmation de l'installation");
  define("LANINS_056", "6");
  define("LANINS_057", " e107 a maintenant toute l'information qu'il a besoin pour accomplir son installation.

  Veuillez cliquer le bouton pour créer les tables de base de données et pour sauvegarder tous vos paramètres.

  ");
  define("LANINS_058", "7");
  define("LANINS_060", "Incapable de lire le fichier de données SQL
  Veuillez vous assurer que le fichier <strong>core_sql.php</strong> existe bien dans le répertoire <strong>/e107_admin/sql</strong> .");
  define("LANINS_061", "e107 n'a pas pu créer toutes les tables requise pour la base de données
  Svp nettoyez la base de données et rectifiez tous les problèmes avant d'essayer à nouveau.");

  define("LANINS_062", "[b]Bienvenue sur votre nouveau site Web ![/b]
  e107 a installé avec succès et est maintenant prêt à accepter du contenu.<br />Votre section d'administration est [link=e107_admin/admin.php]est localisée ici[/link], cliquez pour y aller dès maintenant. Vous devez vous connecter en utilisant le nom d'utilisateur et le mot de passe que vous avez entrés pendant la procédure d'installation.

  [b]Support[/b]
  e107 Page d'accueil: [link=http://e107.org]http://e107.org[/link], vous trouverez la FAQ et la documentation ici.
  Forums: [link=http://e107.org/e107_plugins/forum/forum.php]http://e107.org/e107_plugins/forum/forum.php[/link]

  [b]Téléchargements[/b]
  Extensions: [link=http://e107coders.org]http://e107coders.org[/link]
  Themes: [link=http://e107styles.org]http://e107styles.org[/link] | [link=http://e107themes.org]http://e107themes.org[/link]

  Merci d'essayer e107, nous espèront qu'il satisfera aux exigeances de votre site Web.
  (Vous pourrez supprimer ce message de votre section d'administration.)");
  define("LANINS_063", "e107 a installé avec succès et est maintenant prêt à accepter du contenu.");
  define("LANINS_069", "e107 a été installé avec succès  !

  Pour des raisons de sécurité vous devriez maintenant placer les permissions de nouveau à 644 sur le fichier <strong>e107_config.php</strong>.

  Veuillez en outre supprimer install.php et le répertoire e107_install de votre serveur après que vous ayez cliqué le bouton ci-dessous");
  define("LANINS_070", "e107 n'a pas pu sauvegarder le fichier principal de configuration sur votre serveur.

  Veuillez vous assurer le dossier de <strong>e107_config.php</strong> a les permissions correctes");
  define("LANINS_071", "Finalisation de l'Installation");
  define("LANINS_072", "Nom d'utilisateur de l'Administrateur");
  define("LANINS_073", "C'est le nom que vous utiliserez lors de la connexion dans le site. Si vous voulez vous pouver l'utiliser comme  nom d'affichage aussi");
  define("LANINS_074", "Nom d'affichage Admin");
  define("LANINS_075", "C'est le nom que vous voulez que vos utilisateurs voient s'afficher sur votre profil, le forum et autres secteurs.
  Si vous voulez utiliser le même que votre nom de connexion laisse en blanc alors ce champ.");
  define("LANINS_076", "Mot de passe Admin");
  define("LANINS_077", "SVP entrer le mot de passe admin que vous souhaitez utiliser");
  define("LANINS_078", "Confirmation Mot de passe Admin");
  define("LANINS_079", "SVP retaper votre mot de passe admin à nouveau comme confirmation");
  define("LANINS_080", "Courriel Admin");
  define("LANINS_081", "Entrer votre adresse courriel");
  define("LANINS_082", "vous@votresite.com");
  define("LANINS_083", "N'a pas pu établir de connexion avec le serveur Mysql. Détails des erreurs :");
  define("LANINS_084", "N'a pas pu créer de base de données. Détails des erreurs :");
  define("LANINS_085", "Aide");
  define("LANINS_086", "e107 Page d'accueil: http://e107.org");
  define("LANINS_087", "Forums: http://e107.org/forum.php");
  define("LANINS_088", "Téléchargements");
  define("LANINS_089", "Extensions: http://e107coders.org");
  define("LANINS_090", "Thèmes: http://e107themes.org");
  define("LANINS_091", "Bienvenue sur e107");
  define("LANINS_092", "Divers");
  define("LANINS_093", "Accueil");
  define("LANINS_094", "Téléchargements");
  define("LANINS_095", "Utilisateurs");
  define("LANINS_096", "Proposition d'Actualités");
  define("LANINS_097", "http://e107.org");
  define("LANINS_098", "v0.7CVS"); //$e107['e107_version']
  define("LANINS_099", "French"); //$pref['sitelanguage']
  define("LANINS_100", "Derniers commentaires");
  define("LANINS_101", "[ plus ... ]");
  define("LANINS_102", "Articles");
  define("LANINS_103", "Articles en page d'accueil ...");
  define("LANINS_104", "Derniers apports Forums");
  define("LANINS_105", "Mis à jour Paramètres Menus");
  define("LANINS_106", "Date / Horaire");
  define("LANINS_107", "Revue");
  define("LANINS_108", "Revues en page d'accueil ...");
  define("LANINS_109", "Campagne numéro une ...");
  define("LANINS_110", "MENUPRIVE");
  define("LANINS_111", "Droits d'accès aux menus privés");
  define("LANINS_112", "FORUMPRIVE1");
  define("LANINS_113", "Exemple de Groupe de Forum Privé");
  define("LANINS_114", "Créer les Tables Utilisateurs");
  define("LANINS_115_1", "Oui");
  define("LANINS_115_2", "Non");
  define("LANINS_116", "Préfix des Tables Utilisateurs");
  define("LANINS_117_1", "Le préfix que vous souhaitez pour e107_UDLH est à utitiser pour créer les tables e107_userdb . à utiliser pour les sites web multiple e107 sous une seule et même base de donnée.");
  define("LANINS_117_2", "Oui lorsque... Non quand...");
  define("LANINS_118",  "Impossible de lire le fichier sql<br /><br />Svp assurez-vous que le fichier <strong>user_sql.php</strong> existe dans le répertoire <strong>/e107_admin/sql</strong>");
  define("LANINS_119", "UDLH n'a pas pu créer les tables d'utilisateurs de bases de données utilisateurs.<br />Svp Vider les tables de données and rectifier tous les problèmes avant de rééssayer.");
  define("LANINS_120", "Vous avez décidé de NE PAS INSTALLER les Tables d'UTILISATEURS. br/><strong>Les Tables d'Utilisateurs n'ont donc pas été créés</strong>");
  ?>