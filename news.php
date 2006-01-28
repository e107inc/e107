<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     © Steve Dunstan 2001-2006
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/news.php,v $
|     $Revision: 1.90.2.3 $
|     $Date: 2006-01-28 07:52:08 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/

require_once("class2.php");

if(file_exists(e_PLUGIN."news/news.php")) {
	require_once(e_PLUGIN."news/news.php");
} else {
	require_once(HEADERF);
	echo "No news posts";
	require_once(FOOTERF);
}
