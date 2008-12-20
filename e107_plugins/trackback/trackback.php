<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin administration - newsfeeds
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/trackback/trackback.php,v $
 * $Revision: 1.2 $
 * $Date: 2008-12-20 22:32:36 $
 * $Author: e107steved $
 *
*/
require_once("../../class2.php");
if (!plugInstalled('trackback'))
{
	exit();
}
header('Content-Type: text/xml');
include(e_PLUGIN."trackback/trackbackClass.php");
$trackback = trackbackClass :: respondTrackback();

?>