<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/lan_admin_log.php,v $
 * $Revision: 1.7 $
 * $Date: 2009/06/05 13:24:39 $
 * $Author: marj_nl_fr $
 */
if (!defined('e107_INIT')) { exit(); }

// not in v0.8
define('LAN_ADMINLOG_0',  'Journal administration');   // RL_LAN_030
define('LAN_ADMINLOG_1',  'Date');                       // RL_LAN_019
define('LAN_ADMINLOG_2',  'Titre');
define('LAN_ADMINLOG_3',  'Description');
define('LAN_ADMINLOG_4',  'IP Membre');
define('LAN_ADMINLOG_5',  'ID Membre');
define('LAN_ADMINLOG_6',  'Icône d’information');
define('LAN_ADMINLOG_7',  'Message d’information');
define('LAN_ADMINLOG_8',  'Icône de notification');
define('LAN_ADMINLOG_9',  'Message de notification');
define('LAN_ADMINLOG_10', 'Icône d’alerte');
define('LAN_ADMINLOG_11', 'Message d’alerte');
define('LAN_ADMINLOG_12', 'Icône d’erreur fatale');
define('LAN_ADMINLOG_13', 'Message d’erreur fatale');

// in v0.8
define('RL_LAN_001', 'Système de logs');
define('RL_LAN_002', 'Logs circulaires');
define('RL_LAN_003', 'Maintenance d’enregistrement de l’audit utilisateurs ');
//define('RL_LAN_004', 'Admin/Rolling Log Upgraded');
define('RL_LAN_005', 'Configurer les logs circulaires');
define('RL_LAN_006', 'Options mises à jour');
define('RL_LAN_007', 'Options d’enregistrement de l’audit utilisateurs');
define('RL_LAN_008', 'Logs circulaires actifs:');
define('RL_LAN_009', 'Logs circulaires. Nombre de jours de l’historique');
define('RL_LAN_010', 'Mettre les options à jour');
define('RL_LAN_011', 'Configuration des logs circulaires');
define('RL_LAN_012', 'Options filtres');
define('RL_LAN_013', 'Date et heure de début');
define('RL_LAN_014', 'Date et heure de fin');
define('RL_LAN_015', 'filtre ID membre');
define('RL_LAN_016', 'Vide pour aucun, zéro pour les invités');
define('RL_LAN_017', 'Aucune entrée correspondant à ce filtre');
define('RL_LAN_018', 'Rafraichir');
define('RL_LAN_019', 'Date');
define('RL_LAN_020', 'IP');
define('RL_LAN_021', 'ID');
define('RL_LAN_022', 'Membre');
define('RL_LAN_023', 'Type');
define('RL_LAN_024', 'Depuis');
define('RL_LAN_025', 'Titre évènement');
define('RL_LAN_026', 'Groupe pour lequel les actions utilisateurs sont enregistrées');
define('RL_LAN_027', 'Options');
define('RL_LAN_028', 'Mettre les filtres à jour');
define('RL_LAN_029', 'Filtre de type d’évènements');
define('RL_LAN_030', 'Journal administration');
define('RL_LAN_031', 'Actions à enregistrer');
define('RL_LAN_032', 'Pri');		// Event importance
define('RL_LAN_033', 'Plus d’informations');
define('RL_LAN_044', 'Évènements à afficher par page');
define('RL_LAN_045', 'Supprimer les évènements antérieurs à ');
define('RL_LAN_046', ' jours');
define('RL_LAN_047', 'Confirmez la suppression des évènements antérieurs à ');
define('RL_LAN_048', 'Maintenance des logs admin');
define('RL_LAN_049', 'Supprimer les anciennes entrées');
define('RL_LAN_050', 'Erreur de paramètre. rien n’a été supprimé.');
define('RL_LAN_051', 'Confirmez la suppression');
define('RL_LAN_052', 'Log admin');
define('RL_LAN_053', 'Audit utilisateurs');
define('RL_LAN_054', 'Rien à supprimer ou erreur BdD.');
define('RL_LAN_055', 'Annuler');
define('RL_LAN_056', 'Rien à supprimer.');
define('RL_LAN_057', '. Les évènements antérieurs à --OLD-- ont été supprimés (--NUM-- entrées).');
define('RL_LAN_058', 'Filtre de priorité:');
define('RL_LAN_059', 'Filtre appelant:');
define('RL_LAN_060', 'Filtre adresses IP:');
define('RL_LAN_061', 'Un joker (*) en fin est autorisé');
define('RL_LAN_062', 'Log audit utilisateurs');
define('RL_LAN_063', 'Paramètres audit utilisateurs mis à jour');
define('RL_LAN_064', 'Applicable à tous les logs');
define('RL_LAN_065', 'Confirmer la suppression des évènements de l’audit utilisateurs plus anciens que ');
define('RL_LAN_066', 'Supprimer les évènements de l’audit utilisateurs plus anciens que ');
define('RL_LAN_067', 'Historique téléchargement');
define('RL_LAN_068', 'ID Tlc');
define('RL_LAN_069', 'Nom du téléchargement');


// Messages for checkbox options in audit log - correspond to audit log event codes (20 consecutive values reserved)
define('RL_LAN_071', 'Enregistrement membre (ignore le paramètre groupe ci-dessus)');
define('RL_LAN_072', 'Confirmation enregistrement par email (ignore le paramètre groupe ci-dessus)');
define('RL_LAN_073', '(dé)connexions');
define('RL_LAN_075', 'Modification du nom d’affichage');
define('RL_LAN_076', 'Modification du mot de passe');
define('RL_LAN_077', 'Modification de l’adresse email');
define('RL_LAN_078', 'Réinitialisation mot de passe');
define('RL_LAN_079', 'Modification des paramètres d’un autre membre');
define('RL_LAN_080', 'Ajout rapide de membre');
// Intentional gap
define('RL_LAN_090', 'ID téléchargement');
define('RL_LAN_091', 'Détails horaires');
define('RL_LAN_092', 'Tranche horaire');
define('RL_LAN_093', '(min.)');
define('RL_LAN_094', 'Analyse temporelle détaillée');
define('RL_LAN_095', 'Logs à inclure');
define('RL_LAN_096', 'Diff');
define('RL_LAN_097', 'Heure');
define('RL_LAN_098', 'Source');
define('RL_LAN_099', 'Commentaires');
define('RL_LAN_100', 'CID');			// Comment ID field
define('RL_LAN_101', 'PID');
define('RL_LAN_102', 'ID');
define('RL_LAN_103', 'Sujet');
define('RL_LAN_104', 'UID');
define('RL_LAN_105', 'Auteur');
define('RL_LAN_106', 'Type');
define('RL_LAN_107', 'Commentaire');
define('RL_LAN_108', 'Blo');				// Comment blocked
define('RL_LAN_109', 'Vér');				// Comment locked
define('RL_LAN_110', 'Sup');			// Delete column
define('RL_LAN_111', 'Supprimer les items sélectionnés');
define('RL_LAN_112', '--NUMBER-- commentaires supprimés');
define('RL_LAN_113', 'Erreur lors de la suppression de commentaires!');
define('RL_LAN_114', 'Vider les filtres');
define('RL_LAN_115', 'Admin');
define('RL_LAN_116', 'Emplacement');
define('RL_LAN_117', 'CptPg');
define('RL_LAN_118', 'Drapeau');
define('RL_LAN_119', 'Actif');
define('RL_LAN_120', 'Membres en ligne');
