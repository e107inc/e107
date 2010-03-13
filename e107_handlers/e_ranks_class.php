<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $URL$
 * $Id$
 */

if (!defined('e107_INIT')) { exit; }

class e_ranks
{
	private $ranks;
	private $userRanks;

	public function __construct($force=true)
	{
		$this->ranks = array();
		$this->userRanks = array();

		$e107 = e107::getInstance();
		//Check to see if we can get it from cache
		if($force == false && ($this->ranks = $e107->ecache->retrieve_sys('nomd5_user_ranks')))
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
				while($row = $e107->sql->db_Fetch())
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


	public function getRankData()
	{
		return $this->ranks;
	}

	private function _getImage(&$info)
	{
		$img = $info['image'];
		if($info['lan_pfx'] && strpos('_', $info['image']))
		{
			$_tmp = explode('_', $info['image'], 2);
			$img = e_LANGUAGE.'_'.$_tmp[1];
		}
		return e_IMAGE_ABS.'ranks/'.$img;
	}

	function getRanks($userId)
	{
		$e107 = e107::getInstance();
		if(!$userId && USER) { $userId = USERID; }
		if(isset($this->userRanks[$userId]))
		{
			return $this->userRanks[$userId];
		}

		$ret = array();
		$userData = get_user_data($userId);
		if($userData['user_admin'])
		{
			if($userData['user_perms'] == '0')
			{
				//Main Site Admin
				$data['special'] = "<img src='".$this->_getImage($this->ranks['special'][1])."' /><br />";
			}
			else
			{
				//Site Admin
				$data['special'] = "<img src='".$this->_getImage($this->ranks['special'][2])."' /><br />";
			}
		}

		$userData['user_daysregged'] = max(1, round((time() - $userData['user_join']) / 86400));
		$level = $this->_calcLevel($userData);

		$lastRank = count($this->ranks['data']) - 1;
		$rank = false;
		if($level <= $this->ranks['data'][0]['thresh'])
		{
			$rank = 0;
		}
		elseif($level >= $this->ranks['data'][$lastRank]['thresh'])
		{
			$rank = $lastRank;
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
			$data['pic'] = "<img {$img_title} src='".$this->_getImage($this->ranks['data'][$rank])."' /><br />";
		}
		$this->userRanks[$userId] = $data;
		return $data;
	}

	private function _calcLevel(&$userData)
	{
		$forumCount = varset($userData['user_plugin_forum_posts'], 0) * 5;
		$commentCount = $userData['user_comments'] * 5;
		$chatCount = $userData['user_chats'] * 2;
		$visitCount = $userData['user_visits'];

		return ceil(($forumCount + $commentCount + $chatCount + $visitCount) / 4);

		/*
		global $pref;
		$value = 0;
		$calc = $pref['ranks_calc'];
		$search = array();
		$replace = array();
		foreach(explode(',', $pref['ranks_flist']) as $f)
		{
			$search[] = '{'.$f.'}';
			$replace[] = $userData['user_'.$f];
		}
		$_calc = trim(str_replace($search, $replace, $calc));
		if($_calc == '') { return 0; }
		$calc = '$userLevelValue = '.$_calc.';';
		$value = eval($calc);
		return $value;
		*/
	}

}


?>