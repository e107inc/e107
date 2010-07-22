<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/lan_installer.php,v $
 * $Revision: 1.25 $
 * $Date: 2009/07/19 17:00:47 $
 * $Author: marj_nl_fr $
 */
if (!defined('e107_INIT')) { exit(); }

define('LANINS_001', 'Installation de e107');


define('LANINS_002', 'Étape ');
define('LANINS_003', '1');
define('LANINS_004', 'Sélection de la langue');
define('LANINS_005', 'Choisissez la langue à utiliser lors de la procédure d’installation');
define('LANINS_006', 'Choisir la langue');

define('LANINS_007', '4');
define('LANINS_008', 'Contrôle des permissions des fichiers et des versions PHP et MySQL');
define('LANINS_009', 'Retestez les permissions de fichiers');
define('LANINS_010', 'Fichier non ouvert en écriture: ');
define('LANINS_010a', 'Dossier non ouvert en écriture: ');
define('LANINS_011', 'Erreur');
define('LANINS_012', 'Les fonctions MySQL ne semblent pas exister. Cela signifie probablement que l’extension MySQL de PHP n’est pas installée ou que à l’installation PHP n’a pas été compilé avec le support MySQL.');
define('LANINS_013', 'Votre numéro de version MySQL n’a pas pu être déterminée. Ce n’est pas une erreur grave, donc vous pouvez continuer. Mais gardez à l’esprit que e107 nécessite MySQL 3.23 ou supérieur pour fonctionner correctement.');
define('LANINS_014', 'Permissions de fichiers');
define('LANINS_015', 'Version PHP');
define('LANINS_016', 'MySQL');
define('LANINS_017', 'Passe');
define('LANINS_018', 'Assurez vous que tous les fichiers listés existent et soient accessibles en écriture pour le serveur. Cela implique souvent de faire un chmod 777 (ou 755, voir avec votre hébergeur) sur ceux-ci, mais les environnements varient. Contactez votre hébergeur si vous avez des problèmes.');
define('LANINS_019', 'La version de PHP installée sur votre serveur n’est pas en mesure d’exécuter e107. e107 exige une version PHP 4.3.0 ou supérieure pour fonctionner correctement. Mettez votre version PHP à jour, ou entrez en contact avec votre hébergeur pour une mise à jour.');
define('LANINS_020', 'Continuer');


define('LANINS_021', '2');
define('LANINS_022', 'Détails du serveur MySQL ');
define('LANINS_023', 'Entrez vos paramètres MySQL ici.

Si vous avez les permissions d’administrateur vous pouvez créer une nouvelle base de données en cochant la case, sinon vous devez créer une base de données ou en employer une pré-existante.

Si vous n’avez qu’une seule base de données utilisez un préfixe de sorte que d’autres scripts puissent partager la même base de données.
Si vous ne connaissez pas vos paramètres MySQL contactez votre hébergeur.');
define('LANINS_024', 'Serveur MySQL:');
define('LANINS_025', 'Nom d’utilisateur MySQL:');
define('LANINS_026', 'Mot de passe MySQL:');
define('LANINS_027', 'Base de données MySQL:');
define('LANINS_028', 'Créez la base de données?');
define('LANINS_029', 'Préfixe de la table:');
define('LANINS_030', 'Le serveur MySQL que vous voudriez voir employer pour e107. Il peut également inclure un numéro de port par exemple “hostname:port” ou un chemin vers un socket local par exemple “:/path/to/socket” pour le “localhost”.');
define('LANINS_031', 'Le nom d’utilisateur que vous souhaitez que e107 utilise pour se connecter à votre serveur MySQL');
define('LANINS_032', 'Le mot de passe pour l’utilisateur que vous venez juste d’entrer');
define('LANINS_033', 'La base de données MySQL dans laquelle vous souhaitez que e107 réside, fait parfois référence à un schéma.
Si vous, en tant que utilisateur, vous possédez les permissions pour créer une base de données, vous pouvez choisir de créer la base de données automatiquement si elle n’est pas déjà existante');
define('LANINS_034', 'Le préfixe que vous souhaitez employer pour e107 lors de la création des tables de e107. Utile pour l’installation multiple de e107 dans un seul schéma de base de données.');
define('LANINS_035', 'Continuer');


define('LANINS_036', '3');
define('LANINS_037', 'Vérification de Connexion MySQL');
define('LANINS_038', ' et de création de Base de données');
define('LANINS_039', 'Veuillez vous assurer d’avoir bien renseigné tous les champs, principalement le serveur MySQL, le nom d’utilisateur MySQL et la base de données MySQL (ceux-ci sont toujours exigés par le serveur MySQL)');
define('LANINS_040', 'Erreurs');
define('LANINS_041', 'e107 n’a pas été en mesure d’établir une connexion avec le serveur MySQL en utilisant les informations que vous avez introduites. Revenez à la page précédente et assurez vous que vos données sont correctes.');
define('LANINS_042', 'La connexion au serveur MySQL a été établie et vérifiée.');
define('LANINS_043', 'Impossible de créer la base de données, assurez vous d’avoir les permissions correctes pour créer des bases de données sur votre serveur.');
define('LANINS_044', 'Base de données créée avec succès.');
define('LANINS_045', 'Cliquez sur le bouton pour effectuer la prochaine étape.');

define('LANINS_046', '5');
define('LANINS_047', 'Détails administrateur');
define('LANINS_048', 'Revenez à l’étape précédente');
define('LANINS_049', 'Les deux mots de passe que vous avez entrés ne sont pas identiques. Veuillez retourner à l’étape précédente et réessayer.');
define('LANINS_050', 'Extension XML');
define('LANINS_051', 'Installé');
define('LANINS_052', 'Non Installé');
define('LANINS_053', 'e107 0.7.x nécessite que l’extension XML de PHP soit installée. Veuillez contacter votre hébergeur ou lisez les informations sur <a href="http://php.net/ref.xml">php.net/ref.xml</a> avant de continuer.');
define('LANINS_055', 'Confirmation de l’installation');

define('LANINS_056', '6');
define('LANINS_057', ' e107 a maintenant toutes les informations dont il a besoin pour accomplir son installation.

Veuillez cliquer sur le bouton pour créer les tables de la base de données et sauvegarder tous vos paramètres.

');
define('LANINS_058', '7');
define('LANINS_060', 'Impossible de lire le fichier de données SQL
Veuillez vous assurer que le fichier <strong>core_sql.php</strong> existe bien dans le répertoire <strong>/e107_admin/sql</strong>.');
define('LANINS_061', 'e107 n’a pas pu créer toutes les tables requises pour la base de données.
Nettoyez la base de données et rectifiez tous les problèmes avant d’essayer à nouveau.');

define('LANINS_062', '[b]Bienvenue sur votre nouveau site Web![/b]
e107 a été installé avec succès et est maintenant prêt à accepter du contenu.<br />Votre section d’administration est [link=e107_admin/admin.php]localisée ici[/link]. Cliquez ce lien pour vous y rendre. Vous devez vous connecter en utilisant le nom d’utilisateur et le mot de passe que vous avez entrés pendant la procédure d’installation.

[b]Support francophone[/b]
Page d’accueil e107 communauté française: [link=http://etalkers.tuxfamily.org/]http://etalkers.tuxfamily.org/[/link]

[b]Support anglophone[/b]
Page d’accueil e107: vous trouverez la FAQ et la documentation ici [link=http://e107.org/]http://e107.org[/link].
Forums: [link=http://e107.org/e107_plugins/forum/forum.php]http://e107.org/e107_plugins/forum/forum.php[/link].

[b]Téléchargements[/b]
Plugins: [link=http://plugins.e107.org]http://plugins.e107.org[/link].
Thèmes: [link=http://themes.e107.org]http://themes.e107.org[/link].

Merci d’essayer e107. Nous espérons qu’il satisfera aux exigences de votre site Web.
Vous pouvez supprimer ce message depuis votre section d’administration.');

define('LANINS_063', 'Bienvenu dans e107.');

define('LANINS_069', 'e107 a été installé avec succès!

Pour des raisons de sécurité vous devriez maintenant placer les permissions de nouveau à 644 sur le fichier <strong>e107_config.php</strong>.

Veuillez en outre supprimer install.php de votre serveur après avoir cliqué sur le bouton ci-dessous');
define('LANINS_070', 'e107 n’a pas pu sauvegarder le fichier principal de configuration sur votre serveur.

Veuillez vous assurer que le fichier <strong>e107_config.php</strong> a les permissions correctes');
define('LANINS_071', 'Finalisation de l’installation');

define('LANINS_072', 'Nom d’utilisateur admin');
define('LANINS_073', 'C’est le nom que vous utiliserez lors de la connexion au site. Si vous le désirez vous pouvez également l’utiliser comme nom d’affichage');
define('LANINS_074', 'Nom d’affichage admin');
define('LANINS_075', 'C’est le nom que vous voulez que vos utilisateurs voient s’afficher sur votre profil, le forum et autres.
Si vous souhaitez utiliser le même que votre nom de connexion, laissez ce champ en blanc.');
define('LANINS_076', 'Mot de passe admin');
define('LANINS_077', 'Entrer le mot de passe admin que vous souhaitez utiliser');
define('LANINS_078', 'Confirmation du mot de passe admin');
define('LANINS_079', 'Re-saisissez votre mot de passe admin pour confirmation');
define('LANINS_080', 'Email admin');
define('LANINS_081', 'Entrer votre adresse email');
define('LANINS_082', 'vous@votresite.com');

define('LANINS_083', 'Erreurs MySQL:');
define('LANINS_084', 'Impossible d’établir une connexion avec la base de données');
define('LANINS_085', 'Impossible de sélectionner la base:');

define('LANINS_086', 'Les champs nom d’utilisateur, mot de passe et email sont <strong>obligatoires</strong>. Retournez à la page précédente et assurez vous que les informations entrées sont correctes.');

define('LANINS_087', 'Divers');
define('LANINS_088', 'Accueil');
define('LANINS_089', 'Téléchargement');
define('LANINS_090', 'Membres');
define('LANINS_091', 'Soumettre une news');
define('LANINS_092', 'Contact');
define('LANINS_093', 'Donne l’accès aux menus privés');
define('LANINS_094', 'Exemple de groupe privé pour le forum');
define('LANINS_095', 'Contrôle intégrité');

define('LANINS_096', 'Derniers commentaires');
define('LANINS_097', '[lire plus…]');
//define('LANINS_098', 'Articles');
//define('LANINS_099', 'Page principale des articles…');
define('LANINS_100', 'Derniers postes Forum');
define('LANINS_101', 'Mettre à jour les paramètres menu');
define('LANINS_102', 'Date/Heure');
//define('LANINS_103', 'Chroniques');
//define('LANINS_104', 'Page principale des chroniques…');

define('LANINS_105', 'Un nom de base données ou préfixe commençant par quelque chiffres suivi de “e” ou “E” n’est pas acceptable.  <br />Un nom de base données ou préfixe ne peut pas être vide.');
define('LANINS_106', 'Attention! e107 n’a pas accès en écriture aux dossiers ou fichiers listés ci-dessous.<br /> Bien que cela ne gène en rien l’installation, certaines fonctionnalités nécessitant un accès en écriture à ces fichiers ne fonctionneront pas correctement. Si vous souhaitez les utiliser il vous faut modifier ces droits d’accès.');

// for v0.7.16+ only
define('LANINS_DB_UTF8_CAPTION', 'MySQL Charset:');
define('LANINS_DB_UTF8_LABEL',   'Forcer la BdD en UTF-8?');
define('LANINS_DB_UTF8_TOOLTIP', 'Si oui, le script d\'installation rendra la base de donnée compatible UTF-8 si possible. Les bases de données UTF-8 sont requises pour la prochaine version majeure de e107.');
