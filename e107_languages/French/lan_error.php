<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/lan_error.php,v $
 * $Revision: 1.11 $
 * $Date: 2009/06/05 13:24:40 $
 * $Author: marj_nl_fr $
 */
if (!defined('e107_INIT')) { exit(); }

define('PAGE_NAME', 'Erreur');

define('LAN_ERROR_1', 'Erreur 401: Permission refusée.');
define('LAN_ERROR_2', 'Vous n’avez pas la permission d’accéder à cette URL ou au lien demandé.');
define('LAN_ERROR_3', 'Merci d’informer l’administrateur si vous pensez que l’erreur n’a pas lieu d’être.');
define('LAN_ERROR_4', 'Erreur 403: L’authentification a échouée.');
define('LAN_ERROR_5', 'L’URL que vous avez demandé(e) requiert un nom d’utilisateur et un mot de passe corrects. Soit une des deux informations fournies est incorrecte, soit votre navigateur ne supporte pas cette fonctionnalité.');
define('LAN_ERROR_6', 'Merci d’informer l’administrateur si vous pensez que l’erreur n’a pas lieu d’être.');
define('LAN_ERROR_7', 'Erreur 404: Document non trouvé');
define('LAN_ERROR_9', 'Merci d’informer l’administrateur si vous pensez que l’erreur n’a pas lieu d’être.');
define('LAN_ERROR_10', 'Erreur 500: Entête mal formé');
define('LAN_ERROR_11', 'Le serveur a rencontré une erreur interne ou un problème de configuration, votre demande ne peut aboutir.');
define('LAN_ERROR_12', 'Merci d’informer l’administrateur si vous pensez que l’erreur n’a pas lieu d’être.');
define('LAN_ERROR_13', 'Erreur inconnue');
define('LAN_ERROR_14', 'Le serveur a rencontré une erreur');
define('LAN_ERROR_15', 'Merci d’informer l’administrateur si vous pensez que l’erreur n’a pas lieu d’être.');
define('LAN_ERROR_16', 'Vous avez tenté sans succès d’accéder');
define('LAN_ERROR_17', 'à été enregistré.');
define('LAN_ERROR_18', 'Apparemment, vous avez été redirigé ici par');
define('LAN_ERROR_19', 'Malheureusement, le lien vers cette adresse est périmé ou totalement erroné.');
define('LAN_ERROR_20', 'Cliquez ici pour retourner à la page principale');

define('LAN_ERROR_21', 'l’URL demandée n’a pas pu être trouvée sur ce serveur. le lien que vous avez suivi est probablement périmé.');
define('LAN_ERROR_22', 'Cliquer ici pour aller à la page de recherche de ce site');
define('LAN_ERROR_23', 'Votre tentative d’accès à ');
define('LAN_ERROR_24', ' a échouée.');

// 0.7.6
define('LAN_ERROR_25', '[1]: Impossible de lire les paramètres du noyau depuis la base de données. Ces paramètres existent mais ne peuvent être désérialisés. Tentative de rétablissement par les paramètres en backup…');
define('LAN_ERROR_26', '[2]: Impossible de lire les paramètres du noyau depuis la base de données. Paramètres inexistant.');
define('LAN_ERROR_27', '[3]: Paramètres du noyau sauvegardés. Le backup est actif.');
define('LAN_ERROR_28', '[4]: Pas de backup du noyau disponible. Vérififez que votre base de données n’est pas vide ni corrompue. Sinon lancez l’outil de <a href="'.e_FILE.'resetcore/resetcore.php">réinitialisation</a> afin de rétablir les paramètres par défaut.<br /> Après rétablissement de vos paramètres, veillez à en effectuer une sauvegarde via le panneau admin &gt; base de données.');
define('LAN_ERROR_29', '[5]: Certains champs n’ont pas été saisis. Renseignés tous les champs requis puis ré-envoyez le formulaire.');
define('LAN_ERROR_30', '[6]: Impossible de former une connexion valide avec MySQL. Vérifiez que votre fichier e107_config.php contient les bonnes informations.');
define('LAN_ERROR_31', '[7]: MySQL est en service mais la base de données ('.$mySQLdefaultdb.') n’a pas pût être connectée.<br />Vérifiez si elle existe et si le fichier de configuration contient les bonnes informations.');
define('LAN_ERROR_32', 'Afin de finaliser votre mise à jour, copiez le texte suivant dans votre fichier e107_config.php:');


define('LAN_ERROR_33', 'Erreur de traitement! Vous deviez logiquement être redirigé vers la page d’accueil.');
define('LAN_ERROR_34', 'Erreur inconnue! Merci d’informer l’administrateur du site que vous avez rencontré ceci:');

define('LAN_ERROR_35', 'Erreur 400: requête incorrecte');
define('LAN_ERROR_36', 'Suite à une erreur dans votre requête, le serveur ne peut vous fournir cette page.');
define('LAN_ERROR_37', ''); // dans le genre inutile

// v0.8
define('LAN_ERROR_38', 'Désolé. Le site est temporairement inaccessible.');
define('LAN_ERROR_39', 'Veuillez revenir dans quelque minutes.');
define('LAN_ERROR_40', 'Si le problème persiste, merci de contacter un administrateur du site.');
define('LAN_ERROR_41', 'L’erreur est:');
define('LAN_ERROR_42', 'Informations complémentaires au sujet de l’erreur: ');
define('LAN_ERROR_43', 'Site temporairement inaccessible.');
define('LAN_ERROR_44', ''); // dans le genre inutile
