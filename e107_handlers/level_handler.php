<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/level_handler.php,v $
|     $Revision: 1.10 $
|     $Date: 2005-06-28 21:31:30 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/
	
function get_level($user_id, $user_forums, $user_comments, $user_chats, $user_visits, $user_join, $user_admin, $user_perms, $pref, $fmod = "")
{
	 
	global $tp;

	if (!$user_id) {
		return FALSE;
	}
	if ($user_admin) {
		if ($user_perms == "0")
		{
			$data[0] = IMAGE_rank_main_admin_image."<br />";
			return($data);
		}
	}
	if($fmod === TRUE)
	{
		$data[0] = "<div class='spacer'>".IMAGE_rank_moderator_image."</div>";
		return($data);
	}
	if ($user_admin)
	{
			$data[0] = IMAGE_rank_admin_image."<br />";
			return($data);
	}
	$data[0] = "<span class='smalltext'>".LAN_195." #".$user_id."<br />";
	$data['userid'] = "<span class='smalltext'>".LAN_195." #".$user_id."<br />";
 
	$level_thresholds = ($pref['forum_thresholds'] ? explode(",", $pref['forum_thresholds']) : array(20, 100, 250, 410, 580, 760, 950, 1150, 1370, 1600));
 
	$level_images = explode(",", $pref['forum_images']);
	$level_names = explode(",", $pref['forum_levels']);
	if(!$pref['forum_images'])
	{
		if(!$level_names[0])
		{
			$level_images = array("lev1.png", "lev2.png", "lev3.png", "lev4.png", "lev5.png", "lev6.png", "lev7.png", "lev8.png", "lev9.png", "lev10.png");
		}
	}

	$daysregged = max(1, round((time() - $user_join) / 86400))."days";
	$level = ceil((($user_forums * 5) + ($user_comments * 5) + ($user_chats * 2) + $user_visits)/4);
	$ltmp = $level;
	 
	if ($level <= $level_thresholds[0]) {
		$rank = 0;
	}
	else if($level >= ($level_thresholds[0]+1) && $level <= $level_thresholds[1]) {
		$rank = 1;
	}
	else if($level >= ($level_thresholds[1]+1) && $level <= $level_thresholds[2]) {
		$rank = 2;
	}
	else if($level >= ($level_thresholds[2]+1) && $level <= $level_thresholds[3]) {
		$rank = 3;
	}
	else if($level >= ($level_thresholds[3]+1) && $level <= $level_thresholds[4]) {
		$rank = 4;
	}
	else if($level >= ($level_thresholds[4]+1) && $level <= $level_thresholds[5]) {
		$rank = 5;
	}
	else if($level >= ($level_thresholds[5]+1) && $level <= $level_thresholds[6]) {
		$rank = 6;
	}
	else if($level >= ($level_thresholds[6]+1) && $level <= $level_thresholds[7]) {
		$rank = 7;
	}
	else if($level >= ($level_thresholds[7]+1) && $level <= $level_thresholds[8]) {
		$rank = 8;
	}
	else if($level >= ($level_thresholds[8]+1)) {
		$rank = 9;
	}

	$data['pic'] = (file_exists(THEME."forum/".$level_images[$rank]) ? THEME."forum/".$level_images[$rank] : e_IMAGE."rate/".IMODE."/".$level_images[$rank]);
	$data['name'] = "[ ".$tp->toHTML($level_names[$rank], FALSE, 'defs')." ]";

	if($level_names[$rank])
	{
		$data[1] = "<div class='spacer'>{$data['name']}</div>";
		$img_title = "title='{$data['name']}'";
		$data['pic'] = "<img src='".$data['pic']."' alt='' {$img_title} />";
	}
	else
	{
		$data['pic'] = "<img src='".$data['pic']."' alt='' />";
		$data[1] = "<div class='spacer'>{$data['pic']}</div>";
	}
	return ($data);
}
	
	
	
?>