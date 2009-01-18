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
|     $Source: /cvs_backup/e107_0.8/e107_handlers/level_handler.php,v $
|     $Revision: 1.8 $
|     $Date: 2009-01-18 16:47:41 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

function get_level($user_id, $user_forums, $user_comments, $user_chats, $user_visits, $user_join, $user_admin, $user_perms, $pref, $fmod = "")
{

	global $tp, $imode;

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

	$data['pic'] = (file_exists(THEME."forum/".$level_images[$rank]) ? THEME."forum/".$level_images[$rank] : e_IMAGE."packs/".$imode."/rate/".$level_images[$rank]);
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

class e107UserRank
{

	var $ranks = array();

	function e107UserRank()
	{
		$this->_loadRankData();
	}

	function getRankData()
	{
		return $this->ranks;
	}

	function _loadRankData($force=false)
	{
		$e107 = e107::getInstance();
		//Check to see if we can get it from cache
		if($force == false && ($ranks = $e107->ecache->retrieve_sys('nomd5_user_ranks')))
		{
			$this->ranks = $e107->arrayStorage->ReadArray($ranks);
		}
		else
		{
			//force is true, or cache doesn't exist, or system cache disabled, let's get it from table
			$this->ranks = array();
			if($e107->sql->db_Select('generic', '*', "gen_type = 'user_rank_data' ORDER BY gen_intdata ASC"))
			{
				$i=0;
				while($row = $e107->sql->db_Fetch(MYSQL_ASSOC))
				{
					$tmp = array();
					$tmp['name'] = $row['gen_ip'];
					$tmp['thresh'] = $row['gen_intdata'];
					$tmp['lan_pfx'] = $row['gen_user_id'];
					$tmp['image'] = $row['gen_chardata'];
					$tmp['id'] = $row['gen_id'];
					if($row['gen_datestamp'])
					{
						$this->ranks['special'][$row['gen_datestamp']] = $tmp;
					}
					else
					{
						$this->ranks['data'][$i++] = $tmp;
					}
				}
			}
			$e107->ecache->set_sys('nomd5_user_ranks', $e107->arrayStorage->WriteArray($this->ranks, false));
		}
	}

	function _getImage($info)
	{
		if($info['lan_pfx'])
		{
			$_tmp = explode('_', $info['image'], 2);
			return e_LANGUAGE.'_'.$tmp[1];
		}
		return $info['image'];
	}

	function getRanks($userId)
	{
		$e107 = e107::getInstance();
		if(!$userId && USER) { $userId = USERID; }
		if($ret = getcachedvars('userRankInfo_'.$userId)) { return $ret; }

		$ret = array();
		$userData = get_user_data($userId);
		if($userData['user_admin'])
		{
			if($userData['user_perms'] == '0')
			{
				//Main Site Admin
				$ret['special'] = "<img src='".$this->_getImage($this->ranks['special'][1]['image'])."' /><br />";
			}
			else
			{
				//Site Admin
				$ret['special'] = "<img src='".$this->_getImage($this->ranks['special'][2]['image'])."' /><br />";
			}
		}

		$userData['daysregged'] = max(1, round((time() - $userData['user_join']) / 86400));
		$level = $this->_calcLevel($userData);

		$lastRank = count($this->ranks['data']) - 1;
		$rank = false;
		if($level <= $this->ranks['data'][0]['thresh'])
		{
			$rank = 0;
		}
		elseif($level >= $this->ranks['data'][$lastRank]['thresh'])
		{
			$rank = 9;
		}
		else
		{
			for($i=0; $i < $lastRank; $i++)
			{
				if($level >= $this->ranks['data'][$i]['thresh'] && $level < $this->ranks['data'][($i+1)]['thresh'])
				{
					$rank = $i;
					break;
				}
			}
		}
		if($rank !== false)
		{
			$data['name'] = '[ '.$e107->tp->toHTML($this->ranks['data'][$rank]['name'], FALSE, 'defs').' ]';
			$img_title = ($this->ranks['data'][$rank]['name'] ? "title='".$this->ranks['data'][$rank]['name']."'" : '');
			$data['pic'] = "<img {$img_title} src='".$this->_getImage($this->ranks['data'][$rank]['image'])."' /><br />";

			cachevars('userRankInfo_'.$userId, $data);
			return $data;
		}
	}

	function _calcLevel(&$userData)
	{
		var_dump($this->ranks['config']);
		$value = 0;
		$calc = $this->ranks['config']['calc'];
		$search = array();
		$replace = array();
		foreach($this->ranks['config']['fields'] as $f)
		{
			$search[] = $f['name'];
			$replace[] = $userData[$f['name']];
		}
		$calc = str_replace($search, $replace, $calc);
		$value = eval($calc);
		return $value;
	}

}


?>