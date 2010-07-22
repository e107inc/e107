<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * non compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/lan_mailout.php,v $
 * $Revision: 1.16 $
 * $Date: 2009/06/05 13:24:40 $
 * $Author: marj_nl_fr $
 */
if (!defined('e107_INIT')) { exit(); }

if(ET_e107_Version_7 === true)
{
    define('PRFLAN_52', 'Sauvegarder les modifications');
    define('PRFLAN_63', 'Envoi mail de test');
//    define('PRFLAN_64', 'Cliquer le bouton envoi un mail de test à l’adresse email de l’administrateur principal.');
    define('PRFLAN_65', 'Cliquer pour envoyer le mail à');
    define('PRFLAN_66', 'Test email de');
    define('PRFLAN_67', 'Ceci est un mail de test. Il semble que vos paramètres email sont corrects!

    Salutations,
    du système de site Web e107.');
    define('PRFLAN_68', 'Le mail n’a pas pu être envoyé. Il semblerait que votre serveur ne soit pas correctement configuré pour envoyer des emails. Veuillez essayer d’utiliser le SMTP, ou contacter votre hébergeur et demander lui de vous envoyer leurs paramètres d’envoi d’emails ou de serveur d’email.');
    define('PRFLAN_69', 'Le mail a été envoyé avec succès. Merci de contrôler votre boite mail.');
    define('PRFLAN_70', 'Activer le SMTP');
    define('PRFLAN_71', 'cocher pour utiliser le serveur SMTP pour envoyer des emails');
    define('PRFLAN_72', 'Serveur SMTP');
    define('PRFLAN_73', 'Nom d’utilisateur SMTP');
    define('PRFLAN_74', 'Mot de passe SMTP');
    define('PRFLAN_75', 'L’email n’as pas pu être envoyé. Veuillez vérifier vos paramètres SMTP, ou désactiver le SMTP et essayer de nouveau.');

    define('MAILAN_01', 'De (pseudo)');
    define('MAILAN_02', 'De (email)');
    define('MAILAN_03', 'À');
    define('MAILAN_04', 'Cc');
    define('MAILAN_05', 'Ccl');
    define('MAILAN_06', 'Sujet');
    define('MAILAN_07', 'Fichier attaché');
    define('MAILAN_08', 'Envoyer le mail');
    define('MAILAN_09', 'Utilise un style de thème');
    define('MAILAN_10', 'Utilisateurs inscrits');
    define('MAILAN_11', 'Insérer des variables');
    define('MAILAN_12', 'Tous les membres');
    define('MAILAN_13', 'Tous les membres non vérifiés');
    define('MAILAN_14', 'Il est recommandé d’activer le <a href="prefs.php">SMTP</a> pour des envois d’emails en masse.<br /><br />');
    define('MAILAN_15', 'Envoi des mails');

    define('MAILAN_16', 'Nom d’utilisateur');
    define('MAILAN_17', 'Lien d’enregistrement');
    define('MAILAN_18', 'ID utilisateur');
    define('MAILAN_19', 'Il n’y a aucune adresse électronique pour l’admin du site. Vérifiez vos préférences et essayez à nouveau.');
    define('MAILAN_20', 'Chemin de sendmail');
    define('MAILAN_21', 'Entrées email en masse');
    define('MAILAN_22', 'Il n’y a aucune sauvegarde d’emails pour le moment.');
    define('MAILAN_23', 'Groupe de membres: ');
    define('MAILAN_24', 'email(s) prêt à être envoyé(s)');

    define('MAILAN_25', 'Pause');
    define('MAILAN_26', 'Pause durant les envois en masse toutes les');
    define('MAILAN_27', 'emails');
    define('MAILAN_28', 'Durée de la pause');
    define('MAILAN_29', 'secondes');
    define('MAILAN_30', 'Attention: une pause supérieure à 30 secondes pourraient conduire à une déconnexion et n’est donc pas recommandée.');
    define('MAILAN_31', 'Emails retours en cours');
    define('MAILAN_32', 'Email');
    define('MAILAN_33', 'Emails arrivés');
    define('MAILAN_34', 'Nom compte');
    define('MAILAN_35', 'Mot de passe');
    define('MAILAN_36', 'Effacer les emails non délivrés après vérification');

    define('MAILAN_37', 'Exécuter');
    define('MAILAN_38', 'Annuler');
    define('MAILAN_39', 'Mailing');
    define('MAILAN_40', 'Vous devez renommer <b>e107.htaccess</b> en <b>.htaccess</b> dans');
    define('MAILAN_41', 'avant de pouvoir envoyer un message depuis cette page.');
    define('MAILAN_42', 'Attention');
    define('MAILAN_43', 'Nom d’utilisateur');
    define('MAILAN_44', 'Pseudo');
    define('MAILAN_45', 'Email');
    define('MAILAN_46', 'Utilisateur');
    define('MAILAN_47', '(contient)');
    define('MAILAN_48', '(est égal à)');
    define('MAILAN_49', 'Id');
    define('MAILAN_50', 'Auteur');
    define('MAILAN_51', 'Sujet');
    define('MAILAN_52', 'Dernière modif');
    define('MAILAN_53', 'Admins');
    define('MAILAN_54', 'Soi même');
    define('MAILAN_55', 'Groupe de membres');
    define('MAILAN_56', 'Envoyer le mail');
    define('MAILAN_57', 'Conserver la session SMTP active (“alive”)');
    define('MAILAN_58', 'Problème avec les fichiers joints:');
    define('MAILAN_59', 'Mailing en cours');
    define('MAILAN_60', 'Envoi…');
    define('MAILAN_61', 'Tous les mail ont été envoyer.');
    define('MAILAN_62', 'Emails envoyés:');
    define('MAILAN_63', 'Emails non envoyés:');
    define('MAILAN_64', 'Temps requis pour l’envoi:');
    define('MAILAN_65', 'secondes');
    define('MAILAN_66', 'Annulation réussie');
    define('MAILAN_67', 'Utiliser l’authentification “POP avant SMTP”');
    define('MAILAN_68', 'Adresse test');
}
else
{
    define('LAN_MAILOUT_01', 'De (pseudo)');
    define('LAN_MAILOUT_02', 'De (email)');
    define('LAN_MAILOUT_03', 'À');
    define('LAN_MAILOUT_04', 'Cc');
    define('LAN_MAILOUT_05', 'Ccl');
    define('LAN_MAILOUT_06', 'Sujet');
    define('LAN_MAILOUT_07', 'Fichier attaché');
    define('LAN_MAILOUT_08', 'Envoyer le mail');
    define('LAN_MAILOUT_09', 'Utilise un style de thème');
    define('LAN_MAILOUT_10', 'Utilisateurs inscrits');
    define('LAN_MAILOUT_11', 'Insérer des variables');
    define('LAN_MAILOUT_12', 'Tous les membres');
    define('LAN_MAILOUT_13', 'Tous les membres non vérifiés ');
//    define('LAN_MAILOUT_14', 'Il est recommandé d’activer le <a href="prefs.php">SMTP</a> pour des envois d’email en masse.<br /><br />');
    define('LAN_MAILOUT_15', 'Envoi des mails');
    define('LAN_MAILOUT_16', 'Nom d’utilisateur');
    define('LAN_MAILOUT_17', 'Lien d’enregistrement');
    define('LAN_MAILOUT_18', 'ID utilisateur');
    define('LAN_MAILOUT_19', 'Il n’y a aucune adresse électronique pour l’admin du site. Vérifiez vos préférences et essayez à nouveau.');
    define('LAN_MAILOUT_20', 'Chemin de sendmail');
    define('LAN_MAILOUT_21', 'Entrées emails en masse');
    define('LAN_MAILOUT_22', 'Il n’y a aucune sauvegarde d’emails pour le moment.');
    define('LAN_MAILOUT_23', 'Groupe de membres: ');
    define('LAN_MAILOUT_24', 'email(s) prêt à être envoyé(s)');

    define('LAN_MAILOUT_25', 'Contrôles des envois en masse.');
    define('LAN_MAILOUT_26', 'Pause durant les envois en masse toutes les');
    define('LAN_MAILOUT_27', 'emails pour ');
    define('LAN_MAILOUT_28', 'Enregistrer les modifications');
    define('LAN_MAILOUT_29', 'secondes');
    define('LAN_MAILOUT_30', 'Une pause supérieure à 30 secondes pourraient conduire à une déconnexion et n’est donc pas recommandée.');
    define('LAN_MAILOUT_31', 'Emails retours en cours');
    define('LAN_MAILOUT_32', 'Email');
    define('LAN_MAILOUT_33', 'Emails arrivés');
    define('LAN_MAILOUT_34', 'Nom compte');
    define('LAN_MAILOUT_35', 'Mot de passe');
    define('LAN_MAILOUT_36', 'Effacer les emails non délivrés après vérification');

    define('LAN_MAILOUT_37', 'Exécuter');
    define('LAN_MAILOUT_38', 'Annuler');
    define('LAN_MAILOUT_39', 'Mailing');
    define('LAN_MAILOUT_40', 'Vous devez renommer <b>e107.htaccess</b> en <b>.htaccess</b> dans');
    define('LAN_MAILOUT_41', 'avant de pouvoir envoyer un message depuis cette page.');
    define('LAN_MAILOUT_42', 'Attention');
    define('LAN_MAILOUT_43', 'Nom d’utilisateur');
    define('LAN_MAILOUT_44', 'Pseudo');
    define('LAN_MAILOUT_45', 'Email');
    define('LAN_MAILOUT_46', 'Utilisateur');
    define('LAN_MAILOUT_47', '(contient)');
    define('LAN_MAILOUT_48', '(est égal à)');
    define('LAN_MAILOUT_49', 'ID');
    define('LAN_MAILOUT_50', 'Auteur');
    define('LAN_MAILOUT_51', 'Sujet');
    define('LAN_MAILOUT_52', 'Dernière modif');
    define('LAN_MAILOUT_53', 'Admins');
    define('LAN_MAILOUT_54', 'Soi même');
    define('LAN_MAILOUT_55', 'Groupe de membres');
    define('LAN_MAILOUT_56', 'Envoyer un mail');
    define('LAN_MAILOUT_57', 'Envoi de mails en masse via SMTP');
    define('LAN_MAILOUT_58', 'Problème avec les fichiers joints:');
    define('LAN_MAILOUT_59', 'Mailing en cours');
    define('LAN_MAILOUT_60', 'Envoi…');
    define('LAN_MAILOUT_61', 'Tous les mail ont été envoyer.');
    define('LAN_MAILOUT_62', 'Emails envoyés:');
    define('LAN_MAILOUT_63', 'Emails non envoyés:');
    define('LAN_MAILOUT_64', 'Temps requis pour l’envoi:');
    define('LAN_MAILOUT_65', 'secondes');
    define('LAN_MAILOUT_66', 'Annulation réussie');
    define('LAN_MAILOUT_67', 'Le mail n’a pu être envoyé. Vérifiez vos paramètres SMTP ou choisissez une autre méthode.');
//------------------------------

    define('LAN_MAILOUT_68', 'Ajout de membres');
    define('LAN_MAILOUT_69', 'correspondants, avec ');
    define('LAN_MAILOUT_70', ' dupliqués supprimés.');
    define('LAN_MAILOUT_71', 'Total emails à envoyer');
    define('LAN_MAILOUT_72', 'Journal des publipostages');
    define('LAN_MAILOUT_73', 'Aucun journal');
    define('LAN_MAILOUT_74', 'Journal uniquement (pas d’envoi)');
    define('LAN_MAILOUT_75', 'Journal et envoi');
    define('LAN_MAILOUT_76', 'Ajouter les infos email dans le journal');
    define('LAN_MAILOUT_77', 'Sources adresse email Supplémentaire');
    define('LAN_MAILOUT_78', 'États des publipostages');
    define('LAN_MAILOUT_79', 'Aucun publipostage à afficher.');
    define('LAN_MAILOUT_80', 'Date');
    define('LAN_MAILOUT_81', 'Le mail à été envoyé avec succès. Vérifiez votre boite aux lettres.');
    define('LAN_MAILOUT_82', 'Compte de départ');
    define('LAN_MAILOUT_83', 'Reste à envoyer');
    define('LAN_MAILOUT_84', 'ID mail');
    define('LAN_MAILOUT_85', 'Auteur');
    define('LAN_MAILOUT_86', 'Ré-envoyer');
    define('LAN_MAILOUT_87', 'Serveur SMTP');
    define('LAN_MAILOUT_88', 'Nom d’utilisateur SMTP');
    define('LAN_MAILOUT_89', 'Mot de Passe SMTP');
    define('LAN_MAILOUT_90', 'Caractéristiques SMTP');
    define('LAN_MAILOUT_91', 'POP avant SMTP');
    define('LAN_MAILOUT_92', 'SSL');
    define('LAN_MAILOUT_93', 'TLS');
    define('LAN_MAILOUT_94', '(Utiliser SSL pour gmail/googlemail)');
    define('LAN_MAILOUT_95', 'Utiliser VERP pour les envois en masse');
    define('LAN_MAILOUT_96', 'aucun');
    define('LAN_MAILOUT_97', 'Emails enregistrés');
    define('LAN_MAILOUT_98', 'Entrées orphelines');
    define('LAN_MAILOUT_99', 'Confirmer la nouvelle tentative du publipostage');
    define('LAN_MAILOUT_100', 'Message');
    define('LAN_MAILOUT_101', 'Détail email');
    define('LAN_MAILOUT_102', 'Détail du publipostage');
    define('LAN_MAILOUT_103', 'Résultats des envois');
    define('LAN_MAILOUT_104', 'Aucun envoi ou erreur lors de la sauvegarde des résultats.');
    define('LAN_MAILOUT_105', 'Détails des 10 premières erreurs');
    define('LAN_MAILOUT_106', 'Le mail n’a pas pu être envoyé. Il semblerait que votre serveur ne soit pas correctement configuré pour envoyer des emails. Veuillez essayer d’utiliser le SMTP, ou contacter votre hébergeur et demander lui de vous envoyer leurs paramètres d’envoi d’emails et de serveur d’email.');
    define('LAN_MAILOUT_107', 'à');
    define('LAN_MAILOUT_108', 'Résultat');
    define('LAN_MAILOUT_109', 'Afficher les détails');
    define('LAN_MAILOUT_110', 'Envoi mail de test');
//    define('LAN_MAILOUT_111', 'Cliquer le bouton envoi un mail de test à l’adresse email de l’administrateur principal (selon les préférences du site).');
    define('LAN_MAILOUT_112', 'Cliquer pour envoyer le mail à');
    define('LAN_MAILOUT_113', 'Test email de');
    define('LAN_MAILOUT_114', 'Ceci est un mail de test. Il semble que vos paramètres email sont corrects!

    Salutations,
    du système de site Web e107.');
    define('LAN_MAILOUT_115', 'Méthode de mailing');
    define('LAN_MAILOUT_116', 'Dans le doute laissez PHP');
    define('LAN_MAILOUT_117', 'terminé');
    define('LAN_MAILOUT_118', 'Cliquer sur “Exécuter” pour lancer la procédure. Cliquer sur “Annuler” pour la stopper. Une fois terminé rendez-vous sur une autre page. Les emails non envoyés sont listés dans “États des publipostages”.');
    define('LAN_MAILOUT_119', 'Journal uniquement (avec les erreurs)');
    define('LAN_MAILOUT_120', 'Type de compte');
    define('LAN_MAILOUT_121', 'POP3 standard');
    define('LAN_MAILOUT_122', 'POP3, TLS désactivé');
    define('LAN_MAILOUT_123', 'POP3 avec TLS');
    define('LAN_MAILOUT_124', 'IMAP');
    define('LAN_MAILOUT_125', 'Adresse test');
}
