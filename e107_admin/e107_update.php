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
require_once ("../class2.php");

// include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'database';

require_once ("auth.php");
require_once ("update_routines.php");

new e107Update($dbupdate);

e107::getSession()->set('core-update-status', false); // reset update status.

require_once ("footer.php");


?>