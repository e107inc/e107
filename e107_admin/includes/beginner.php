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
|     $Source: /cvs_backup/e107_0.7/e107_admin/includes/beginner.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-03-27 17:10:25 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

$text = "<div style='text-align:center;vertical-align:middle'><br /><br />
	<table style='".ADMIN_WIDTH.";margin-top:auto;margin-bottom:auto' >";

	$newarray[27] = array(e_ADMIN."plugin.php", ADLAN_98, ADLAN_99, "Z", 2, E_16_PLUGMANAGER, E_32_PLUGMANAGER);

	$selection = array(19,10,15,22,5,17,7,25,27,23);

	foreach($selection as $id){
	$text .= render_links($newarray[$id][0],$newarray[$id][1],$newarray[$id][2],$newarray[$id][3],$newarray[$id][6],'beginner');
	}

$text .= "</table><br /></div>";

$ns->tablerender(ADLAN_47." ".ADMINNAME, $text);
echo admin_info();

?>