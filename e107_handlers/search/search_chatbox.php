<?php
// search module for Chatbox search.

if($results = $sql -> db_Select("chatbox", "*", "(cb_nick REGEXP('".$query."') OR cb_message REGEXP('".$query."')) AND cb_blocked='0' ")){
	while(list($cb_id, $cb_nick, $cb_message, $cb_datestamp, $cb_blocked, $cb_ip) = $sql -> db_Fetch()){
		$cb_nick = eregi_replace("[0-9]+\.", "", $cb_nick);

		$cb_nick_ = parsesearch($cb_nick, $query);
		$cb_message_ = parsesearch($cb_message, $query);
		if(!$cb_nick_){
			$cb_nick_ = $cb_nick;
		}
		if(!$cb_message_){
			$cb_message_ = $cb_message;
		}
		$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <a href=\"chat.php?".$cb_id.".fs\">$cb_nick_</a><br />$cb_message_<br /><br />";
	}
}else{
	$text .= LAN_198;
}
?>