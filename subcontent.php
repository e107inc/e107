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
|     $Source: /cvs_backup/e107_0.8/subcontent.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:33:09 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");

//redirection to new content management plugin if it is installed
if ($sql -> db_Select("plugin", "*", "plugin_path = 'content' AND plugin_installflag = '1' ")){ 
	header("location:".e_PLUGIN."content/content_submit.php");
} else {
	header("location:".e_BASE."index.php");
}
	
?>