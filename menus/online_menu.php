<?php
$us = new online;
$rusers = $us ->refresh();

$users_on_page = $rusers[0];
$users_on_site = $rusers[1];
$regged_users_on_site = $rusers[2];
$guests = $users_on_site - $regged_users_on_site;
if($regged_users_on_site == ""){
	$regged_users_on_site = "none";
}

$text = LAN_281.$guests."<br />";

if($pref['user_reg'][1] == 1){
	$text .= LAN_178.$rusers[3].", ".$regged_users_on_site."<br />";
}
$text .= LAN_176.$users_on_page;


//$text = LAN_176." ".$users_on_page."<br />
//".LAN_177." ".$users_on_site."<br />";
//if($pref['user_reg'][1] == 1){
//	$text .= LAN_178." ".$regged_users_on_site;
//}
$ns -> tablerender(LAN_179, $text);

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
class online {
	
	var $timeout = 120;

	function refresh(){
		/*
		# Refresh users online
		# - parameters		none
		# - return				user array
		# - scope					public
		*/
		$use = new db; $use2 = new db;
		$timestamp = time();
        $timeout = $timestamp - $this->timeout;
		$ip = getip();
		$use -> db_Delete("online", "online_timestamp < $timeout");
		$use -> db_Delete("online", "online_ip='$ip' ");

		if(USER != FALSE){
			$un = USERID.".".USERNAME;
			$use -> db_Insert("online", " '$timestamp', '1', '".$un."', '$ip', '".$_SERVER['PHP_SELF']."' ");
		}else{
			$un = "0";
			$use -> db_Insert("online", " '$timestamp', '0', '".$un."', '$ip', '".$_SERVER['PHP_SELF']."' ");
		}

		$ruser[0] = $use -> db_Count("online", "(*)", " WHERE online_location='".$_SERVER['PHP_SELF']."' ");
		$ruser[1] = $use -> db_Count("online");
	
		if($use -> db_Select("online", "*", "online_flag='1' ")){
			$ruser[3] = $use -> db_Rows();
			while(list($null, $null, $online_user_id) = $use-> db_Fetch()){
				$fca = explode(".", $online_user_id);
				$userid = $fca[0];
				$username = $fca[1];
				if($username == ""){
					$use2 -> db_Select("user", "*", "user_id='$userid' ");
					list($null, $username) = $use2 -> db_Fetch();
				}
				if(!eregi($username, $ruser[2])){
					$ruser[2] .= "<a href=\"user.php?id.".$userid."\">".$username."</a>&nbsp; ";
				}
			}
		}
		return $ruser;
	}
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

?>