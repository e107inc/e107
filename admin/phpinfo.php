<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/phpinfo.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("0")){ header("location:".e_HTTP."index.php"); }
require_once("auth.php");

phpinfo();

require_once("footer.php");
?>	