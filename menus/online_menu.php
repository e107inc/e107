<?php
$us = new online;
$rusers = $us ->refresh();

$users_on_page = $rusers[0];
$users_on_site = $rusers[1];
$regged_users_on_site = $rusers[2];
if($regged_users_on_site == ""){
	$regged_users_on_site = "none";
}

$text = LAN_176." ".$users_on_page."<br />
".LAN_177." ".$users_on_site."<br />";
if($pref['user_reg'][1] == 1){
	$text .= LAN_178." ".$regged_users_on_site;
}
$ns -> tablerender(LAN_179, $text);
?>