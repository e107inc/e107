<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/poll/poll.php,v $
 * $Revision: 1.3 $
 * $Date: 2009-11-17 13:48:43 $
 * $Author: marj_nl_fr $
 */

require_once("../../class2.php");
if (!plugInstalled('poll')) 
{
	header("Location: ".e_BASE."index.php");
	exit;
}

require_once(HEADERF);

require_once(e_PLUGIN."poll/poll_menu.php");


require_once(FOOTERF);
exit;

?>