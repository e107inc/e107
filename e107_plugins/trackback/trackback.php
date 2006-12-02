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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/trackback/trackback.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:35:43 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
header('Content-Type: text/xml');
include(e_PLUGIN."trackback/trackbackClass.php");
$trackback = trackbackClass :: respondTrackback();

?>