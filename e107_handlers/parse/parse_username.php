<?php
function parse_username($match,$referrer){
	return (USER) ? USERNAME : LAN_GUEST;
}
?>