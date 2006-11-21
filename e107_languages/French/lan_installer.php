<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). License GNU/PGL
| Traducteurs: communauté francophone e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/lan_installer.php,v $
|     $Revision: 1.8 $
|     $Date: 2006-11-21 05:46:19 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
  require_once('French.php'); //Added to get global variable used in this particular translation.
  define("LANINS_001", "Installation du Système de Gestion de Contenu e107 ");
  define("LANINS_002", "Étape ");
  define("LANINS_003", "1");
  define("LANINS_004", "Sélection du langage");
  define("LANINS_005", "Veuillez choisir le langage à utiliser lors de l'installation");
  define("LANINS_006", "Sélectionner le langage");
  define("LANINS_007", "4");
  define("LANINS_008", "Contrôle des versions de PHP et MySQL / Contrôle des permissions des fichiers");
  define("LANINS_009", "Retestez les permissions des fichiers");
  define("LANINS_010", "Fichier non accessible en écriture: ");
  define("LANINS_010a", "Répertoire non accessible en écriture:");
  define("LANINS_011", "Erreur");
  define("LANINS_012", "Les Fonctions MySQL ne semblent pas exister. Cela signifie probablement que l'extension MySQL pour PHP n'est pas installée ou que votre installation PHP n'est pas complilée avec un support pour MySQL.");
  define("LANINS_013", "Votre version MySQL n'a pas pu être déterminée. Cette erreur n'est pas fatale, donc veuillez continuer l'installation, mais soyez averti que e107 nécessite une version de MySQL >= 3.23 pour fonctionner correctement.");
  define("LANINS_014", "Permissions des fichiers");
  define("LANINS_015", "Version PHP ");
  define("LANINS_016", "MySQL");
  define("LANINS_017", "RÉUSSI");
  define("LANINS_018", "Assurez vous que tous les fichiers listés existent et sont accessibles en écriture par le serveur. Cela implique de régler les attributs à 777 grâce à la fonction CHMOD, mais les environnements varient - entrez en contact avec votre administrateur d'hébergement si vous avez des problèmes.");
  define("LANINS_019", "La version de PHP installée sur votre serveur n'est pas capable d'exécuter e107. e107 nécessite au moins la version PHP 4.3.0 pour fonctionner correctement. Mettez à niveau votre version PHP ou entrez en contact avec votre hôte pour une mise à niveau.");
  define("LANINS_020", "Continuer l&#39;installation");
  define("LANINS_021", "2");
  define("LANINS_022", "Détails du Serveur MySQL ");
  define("LANINS_023", "Veuillez entrez vos paramètres MySQL ici.<br />
  Si vous avez les permissions d'administrateur nécessaires, vous pouvez créer une nouvelle base de données
  en cochant dans la case, si non vous devrez créer une base de données ou en employer une
  pré-existante.
  Si vous n'avez qu'une seule base de données, utilisez un préfixe de sorte que d'autres scripts puissent partager la même base de données.
  Si vous ne connaissez pas vos paramètres MySQL, contactez votre hébergeur.");
  define("LANINS_024", "Serveur MySQL :");
  define("LANINS_025", "Identifiant MySQL:");
  define("LANINS_026", "Mot de passe MySQL:");
  define("LANINS_027", "Base de données MySQL:");
  define("LANINS_028", "Créer une base de données ?");
  define("LANINS_029", "Préfixe des Tables de données:");
  define("LANINS_030", "Le serveur MySQL que vous désirez employer pour e107. <br />Il peut également inclure un port particulier. Exemple : \"hostname:port\" ou le chemin vers un socket local, exemple : \":/path/to/socket\" pour l'hébergeur local (<i>localhost</i>).");
  define("LANINS_031", "L'identifiant utilisé pour se connecter à votre serveur MySQL");
  define("LANINS_032", "Le mot de passe de cet identifiant");
  define("LANINS_033", "La base de données MySQL où e107 résidera, fait parfois référence à un schéma.<br />Si l'identifiant que vous utilisez pour connecter e107 possède les permissions pour créer une base de données, vous pouvez choisir de créer la base de données automatiquement si elle n'existe pas déjà.");
  define("LANINS_034", "Le préfixe que vous souhaitez employer lors de la création des tables de données e107. Utile pour l'installation de multiples e107 dans un seul schéma de base de données.");
  define("LANINS_035", "Continuer");
  define("LANINS_036", "3");
  define("LANINS_037", "Vérification de la connexion MySQL  ");
  define("LANINS_038", " et création de la base de données ");
  define("LANINS_039", "Veuillez vous assurer d'avoir rempli tous les champs. Essentiel : le Serveur MySQL, l'Identifiant MySQL &amp; la Base de données MySQL (ceux-ci sont toujours exigés par le serveur MySQL)");
  define("LANINS_040", "Erreurs");
  define("LANINS_041", "e107 n'a pas été en mesure d'établir une connexion au serveur MySQL en utilisant les informations que vous avez introduites. Veuillez retourner à la dernière page et vous assurer que les informations sont correctes.");
  define("LANINS_042", "Connexion au serveur MySQL établie et vérifiée.");
  define("LANINS_043", "Impossibilité de créer la base de données, veuillez vous assurer d'avoir les permissions correctes pour créer des bases de données sur votre serveur.");
  define("LANINS_044", "Base de données créée avec succès !");
  define("LANINS_045", "Veuillez cliquer sur le bouton pour procéder à la prochaine étape.");
  define("LANINS_046", "5");
  define("LANINS_047", "Détails de l'Administrateur principal");
  define("LANINS_048", "Retourner à la Dernière étape");
  define("LANINS_049", "Les deux mots de passe entrés ne sont pas identiques. Veuillez retourner à l'étape précédente et essayer de nouveau.");
  define("LANINS_050", "Extension XML ");
  define("LANINS_051", "Installé");
  define("LANINS_052", "Non Installé");
  define("LANINS_053", "e107 .700 nécessite l'installation de l'extension XML de PHP. Veuillez entrer en contact avec votre hôte ou lisez l'information à ");
  define("LANINS_054", " avant de continuer");

  define("LANINS_055", "Confirmation de l'installation");
  define("LANINS_056", "6");
  define("LANINS_057", " e107 a maintenant toute les informations dont il a besoin pour compléter son installation.

  Veuillez cliquer le bouton pour créer les tables de la base de données et pour sauvegarder vos paramètres.

  ");
  define("LANINS_058", "7");
  define("LANINS_060", "Incapable de lire le fichier de données SQL
  Veuillez vous assurer que le fichier <strong>core_sql.php</strong> existe bien dans le répertoire <strong>/e107_admin/sql</strong> .");
  define("LANINS_061", "e107 n'a pas pu créer toutes les tables requise pour sa base de données.
  Veuillez nettoyer la base de données et rectifiez tous les problèmes avant d'essayer à nouveau.");

define("LANINS_062", "[b]Bienvenue sur votre nouveau site Web ![/b]
e107 a été installé avec succès et est maintenant prêt à accepter du contenu.<br />Votre section d&#39;administration est [link=e107_admin/admin.php]localisée ici[/link], cliquez pour y aller dès maintenant. Vous devez vous connecter en utilisant l&#39;identifiant et le mot de passe que vous avez entrés pendant la procédure d&#39;installation.

  [b]Support[/b]
  Page d&#39;accueil e107 : [link=http://e107.org]http://e107.org[/link], vous trouverez la FAQ et la documentation ici.
  Forums: [link=http://e107.org/e107_plugins/forum/forum.php]http://e107.org/e107_plugins/forum/forum.php[/link]

  [b]Téléchargements[/b]
  Extensions: [link=http://e107coders.org]http://e107coders.org[/link]
  Thèmes: [link=http://e107themes.org]http://e107themes.org[/link]

Merci d&#39;essayer le portail web e107, l&#39;équipe de développement espère qu&#39;il saura satisfaire aux exigences de votre site Web.
  
  nb. (Vous pourrez supprimer ce message à partir de votre section d&#39;administration.) "); 
  define("LANINS_063", "Bienvenue sur e107");
  define("LANINS_069", "e107 a été installé avec succès  !

  Pour des raisons de sécurité vous devriez maintenant régler les permissions du fichier <strong>e107_config.php</strong> à 644.

  Veuillez en outre <strong>supprimer le fichier install.php</strong> de votre serveur après avoir cliqué le bouton ci-dessous");
  define("LANINS_070", "e107 n'a pas pu sauvegarder le fichier principal de configuration sur votre serveur.

  Veuillez vous assurer le dossier de <strong>e107_config.php</strong> a les permissions correctes");
  define("LANINS_071", "Finalisation de l'Installation");
  define("LANINS_072", "Identifiant de l'Administrateur");
  define("LANINS_073", "C'est le nom que vous utiliserez pour vous connecter au site. Vous pouver aussi l'utiliser comme nom d'affichage");
  define("LANINS_074", "Nom d'affichage de l'Administrateur");
  define("LANINS_075", "C'est le nom que les utilisateurs verront s'afficher sur votre profil, le forum et les autres secteurs.<br />Si vous désirez utiliser votre identifiant, laissez ce champ vide.");
  define("LANINS_076", "Mot de passe Admin");
  define("LANINS_077", "Veuillez entrer le mot de passe que vous souhaitez utiliser");
  define("LANINS_078", "Confirmation Mot de passe Admin");
  define("LANINS_079", "Veuillez réécrire votre mot de passe afin de le confirmer");
  define("LANINS_080", "Courriel Admin");
  define("LANINS_081", "Entrez votre adresse courriel");
  define("LANINS_082", "vous@votresite.com");
  define("LANINS_083", "Erreur MySQL rapportée :");
  define("LANINS_084", "L'installeur n'a pas pu établir de connexion avec la base de données");
  define("LANINS_085", "L'installeur n'a pas pu sélectionner la base de données");
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
  define("LANINS_096", "Proposition d'".GLOBAL_LAN_NEWS_2."s");
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
  define("LANINS_116", "Préfixe des Tables Utilisateurs");
  define("LANINS_117_1", "Le préfixe que vous souhaitez pour e107_UDLH est à utitiser pour créer les tables e107_userdb . à utiliser pour les sites web multiple e107 sous une seule et même base de donnée.");
  define("LANINS_117_2", "Oui lorsque... Non quand...");
  define("LANINS_118",  "Impossible de lire le fichier sql<br /><br />Svp assurez-vous que le fichier <strong>user_sql.php</strong> existe dans le répertoire <strong>/e107_admin/sql</strong>");
  define("LANINS_119", "UDLH n'a pas pu créer les tables d'utilisateurs de bases de données utilisateurs.<br />Svp Vider les tables de données and rectifier tous les problèmes avant de rééssayer.");
  define("LANINS_120", "Vous avez décidé de NE PAS INSTALLER les Tables d'UTILISATEURS. br/><strong>Les Tables d'Utilisateurs n'ont donc pas été créés</strong>");
  ?>
