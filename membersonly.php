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
|     $Source: /cvs_backup/e107_0.8/membersonly.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
require_once("class2.php");

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);

if(is_readable(THEME."membersonly_template.php"))
{
	require_once(THEME."membersonly_template.php");
}
else
{
	require_once(e_CORE."templates/membersonly_template.php");
}


define('e_IFRAME',true);

include_once(HEADERF);

echo $MEMBERSONLY_BEGIN;
$ns->tablerender($MEMBERSONLY_CAPTION, $MEMBERSONLY_TABLE, 'membersonly'); 
echo $MEMBERSONLY_END;

?>
