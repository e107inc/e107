<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc 
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/download.php,v $
|     $Revision$ 
|     $Date$
|     $Author$
|
+----------------------------------------------------------------------------+
*/

require_once("class2.php");

$query = (e_QUERY) ? "?".str_replace("&amp;","&",e_QUERY) : "";

e107::getRedirect()->go(e_PLUGIN."download/download.php".$query,true);

//require_once(e_PLUGIN."download/download.php");

exit();

?>