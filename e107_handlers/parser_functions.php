<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/e107_handlers/parser_functions.php
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
@include(e_LANGUAGEDIR.e_LAN."/lan_parser_functions.php");
@include(e_LANGUAGEDIR."English/lan_parser_functions.php");
function e107core_parse($match,$referrer){
	global $pref;
	if($match[1] == "EMAILTO"){
		$image = (file_exists(THEME."forum/email.png")) ? "<img src='".THEME."forum/email.png' alt='' style='border:0' />" : "<img src='".e_IMAGE."forum/email.png' alt='' style='border:0' />";
		if(is_numeric($match[2])){
			if(!$pref['emailusers']){return $match[0];}
			return "<a href='".e_BASE."emailmember.php?id.{$match[2]}'>{$image}</a>";
		} else {
			return "<a href='mailto:{$match[2]}'>{$image}</a>";
		}
	}
	if($match[1] == "PROFILE"){
		$image = (file_exists(THEME."forum/profile.png")) ? "<img src='".THEME."forum/profile.png' alt='' style='border:0' />" : "<img src='".e_IMAGE."forum/profile.png' alt='' style='border:0' />";
		return "<a href='".e_BASE."user.php?id.{$match[2]}'>{$image}</a>";
	}
	if($match[1] == "PICTURE"){
		if(is_numeric($match[3])){
			if($match[3] == USERID){
				$image = USERSESS;
			} else {
				$sql2 = new db;
				$sql2 -> db_Select("user","user_sess","user_id = '{$match[3]}'");
				$row = $sql2 -> db_Fetch();
				$image=$row['user_sess'];
				echo "[{$image}]";
			}
		} elseif($match[3]) {
			$image=$match[3];
		} else {
			$image=USERSESS;
		}
      if($image && file_exists(e_FILE."public/avatars/".$image)){
      	return "<img src='".e_FILE."public/avatars/{$image}' alt='' />";
      } else {
      	return "";
      }
	}

	if($match[1] == "AVATAR"){
		if(is_numeric($match[3])){
			if($match[3] == USERID){
				$image = USERIMAGE;
			} else {
				$sql2 = new db;
				$sql2 -> db_Select("user","user_image","user_id = '{$match[3]}'");
				$row = $sql2 -> db_Fetch();
				$image=$row['user_image'];
			}
		} elseif($match[3]) {
			$image=$match[3];
		} else {
			$image = USERIMAGE;
		}
		if($image){
			require_once(e_HANDLER."avatar_handler.php");
			return "<img src='".avatar($image)."' alt='' />";
		} else {
			return "";
		}
	}
	if($match[1] == "USERNAME"){
		if(USER){
			return USERNAME;
		} else {
			return LAN_GUEST;
		}
	}
}

?>
