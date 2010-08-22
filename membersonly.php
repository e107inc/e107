<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/
require_once("class2.php");

include_lan(e_LANGUAGEDIR.e_LANGUAGE."/lan_membersonly.php");

if(is_readable(THEME."membersonly_template.php"))
{
	require_once(THEME."membersonly_template.php");
}
else
{
	require_once(e_THEME."templates/membersonly_template.php");
}

$HEADER=""; 
$FOOTER=""; 

include_once(HEADERF);

echo $MEMBERSONLY_BEGIN;
$ns->tablerender($MEMBERSONLY_CAPTION, $MEMBERSONLY_TABLE); 
echo $MEMBERSONLY_END;

?>
