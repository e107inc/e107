<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/poll/poll.php,v $
|     $Revision: 1.2 $
|     $Date: 2008-12-11 21:13:48 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
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