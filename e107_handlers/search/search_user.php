<?php
// search module for User.

if($results = $sql -> db_Select("user", "user_id, user_name, user_email, user_homepage, user_location, user_signature", "user_name LIKE('%".$query."%') OR user_email LIKE('%".$query."%') OR user_homepage LIKE('%".$query."%') OR user_location LIKE('%".$query."%') OR user_signature LIKE('%".$query."%') ")){
	while(list($user_id, $user_name, $user_email, $user_homepage, $user_location, $user_signature) = $sql -> db_Fetch()){
		$user_name = parsesearch($user_name, $query);
		$user_email = parsesearch($user_email, $query);
		$user_homepage = parsesearch($user_homepage , $query);
		$user_signature = parsesearch($user_signature, $query);
		$user_location = parsesearch($user_location, $query);
		$text .= "<img src=\"".THEME."images/bullet2.gif\" alt=\"bullet\" /> <a href=\"user.php?id.".$user_id."\">".$user_name."</a><br />";
	}
}else{
	$text .= LAN_198;
}
?>