<?php
if(!eregi("\?poll", $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING'])){
	require_once(e_BASE."plugins/poll.php");
}
?>