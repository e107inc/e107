<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/
	
if (!defined('e107_INIT')) { exit; }

function get_level($user_id, $user_forums, $user_comments, $user_chats, $user_visits, $user_join, $user_admin, $user_perms, $pref, $fmod = "")
{
	 
	global $tp;

	if (!$user_id) {
		return FALSE;
	}
	if($fmod === TRUE)
	{
		$data['special'] = "<div class='spacer'>".IMAGE_rank_moderator_image."</div>";
		$data[0] = "<div class='spacer'>".IMAGE_rank_moderator_image."</div>";
	}
	if ($user_admin)
	{
		if ($user_perms == "0")
		{
			$data['special'] = IMAGE_rank_main_admin_image."<br />";
			$data[0] = IMAGE_rank_main_admin_image."<br />";
		}
		else
		{
			$data['special'] = IMAGE_rank_admin_image."<br />";
			$data[0] = IMAGE_rank_admin_image."<br />";
		}
	}
	$data[0] = "<span class='smalltext'>".LAN_195." #".$user_id."</span><br />";
	$data['userid'] = "<span class='smalltext'>".LAN_195." #".$user_id."</span><br />";
 
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

	if($data['special']) { $data[0] = $data['special'];}
	return ($data);
}
	
	
	
?>