<?php
$text = LAN_281.GUESTS_ONLINE."<br />";
if($pref['user_reg'][1] == 1){
	$text .= LAN_178.MEMBERS_ONLINE.(MEMBERS_ONLINE ? ", ": "").MEMBER_LIST."<br />";
}
$text .= LAN_176.ON_PAGE;

$ns -> tablerender(LAN_179, $text);

?>