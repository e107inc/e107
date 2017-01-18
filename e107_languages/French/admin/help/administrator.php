<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/help/administrator.php,v $
 * $Revision: 1.5 $
 * $Date: 2008/06/16 15:03:43 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }

$text = 'Utilisez cette page pour éditer ou supprimer des administrateurs.<br /><br />L\'administrateur n\'a le droit d\'accéder qu\'aux fonctionnalités cochées.<br /><br />Pour créer un nouvel administrateur, rendez vous à la page de configuration des membres et conférez le statut d\'admin au membre désiré.';
$ns -> tablerender('Aide administrateurs', $text);
