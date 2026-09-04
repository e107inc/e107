<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration - e107 System Update
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/e107_update.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/
define("e_MINIMAL",true);
define('e_ADMIN_UPDATE', true); // used in class2.php
require_once (__DIR__."/../class2.php");
if (!getperms('0'))
{
	e107::redirect('admin');
	exit;
}
// include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'database';

require_once ("auth.php");

$updateIsPost = (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'POST');

if(!$updateIsPost && defined('e_TOKEN') && empty($_GET['e-token']))
{
	header('HTTP/1.1 403 Forbidden', true, 403);

	e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_e107_update.php');
	e107::getMessage()->addError(defset('LAN_UPDATE_REFUSED_TOKEN_MISSING', 'Invalid or missing security token.'));
	e107::getRender()->tablerender(defset('LAN_UPDATE_56', 'System Update'), e107::getMessage()->render());

	require_once ("footer.php");
	exit;
}

require_once ("update_routines.php");

new e107Update($dbupdate);

e107::getSession()->set('core-update-status', false); // reset update status.
e107::getSession()->set('core-update-checked', false); // and check again, since running the update changed the answer.

require_once ("footer.php");


