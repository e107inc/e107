<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/help/notify.php,v $
 * $Revision: 1.12 $
 * $Date: 2009/02/02 22:01:02 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit(); }

$text = 'Notifier par email quand des évènements e107 se produisent.<br /><br />
Par exemple: mettre “IP bannie pour flood du site” sur groupe “Admin” et tous les administrateurs recevront un email quand le site est floodé.<br /><br />
Vous pouvez également alerter vos membres lorsqu’une news a été postée.<br /><br />
Si vous voulez que les alertes par email soient envoyées à une autre adresse email, sélectionnez l’option “Email” et remplissez le champ avec l’adresse email.';

$ns -> tablerender('Aide alertes', $text);
