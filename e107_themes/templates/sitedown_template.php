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
|     $Source: /cvs_backup/e107_0.7/e107_themes/templates/sitedown_template.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-01-17 12:53:59 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

// ##### SITEDOWN TABLE -----------------------------------------------------------------
if(!$SITEDOWN_TABLE){
	$SITEDOWN_TABLE = "
	<html>
	<head>
		<title>{SITEDOWN_TABLE_PAGENAME}</title>
	</head>
	<body>
		<font style='font-size: 14px; color: black; font-family: Tahoma, Verdana, Arial, Helvetica; text-decoration: none'>
		<div style='text-align:center'><img src='".e_IMAGE."logo.png' alt='Logo' /></div>
		<hr />
		<br />
		<div style='text-align:center'>{SITEDOWN_TABLE_MAINTAINANCETEXT}</div>
	</body>
	</html>";
}
// ##### ------------------------------------------------------------------------------------------

?>