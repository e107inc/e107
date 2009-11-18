<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin administration - newsfeeds
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/trackback/trackback.php,v $
 * $Revision: 1.3 $
 * $Date: 2009-11-18 01:06:01 $
 * $Author: e107coders $
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