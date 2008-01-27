<?php
// $Id: e_header.php,v 1.2 2008-01-27 11:12:59 e107coders Exp $   
if (!defined('e107_INIT')) { exit; }

if($eMenuActive['chatbox_menu'] && ($pref['cb_layer']==2))
{
	$eplug_js[] = e_FILE_ABS."e_ajax.php";
}


?>