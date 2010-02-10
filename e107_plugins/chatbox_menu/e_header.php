<?php
// $Id$   
if (!defined('e107_INIT')) { exit; }

if($eMenuActive['chatbox_menu'] && ($pref['cb_layer']==2))
{
	$eplug_js[] = e_FILE_ABS."e_ajax.php";
}


?>