<?php
$text = "";
if($pref['log_activate'][1] == 1){
	require_once("plugins/log.php");
}else{
	return FALSE;
}
?>