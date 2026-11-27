<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }


/**
 *
 */
class e_ranks
{
	public $ranks;
	private $userRanks;
	private $imageFolder;

	/**
	 * @param $force
	 */
	public function __construct($force = false)
	{
		$this->ranks = array();
		$this->userRanks = array();
		$this->imageFolder = is_dir(THEME.'images/ranks') ? THEME_ABS.'images/ranks/' : e_IMAGE_ABS.'ranks/';


		$ecache = e107::getCache();
		$sql = e107::getDb();
		//Check to see if we can get it from cache
		if($force == false && ($ranks = $ecache->retrieve_sys('nomd5_user_ranks')))
		{
			$this->ranks = e107::unserialize($ranks);
		}
		else
		{
			//force is true, or cache doesn't exist, or system cache disabled, let's get it from table
			$this->ranks = array();
			if($sql->select('generic', '*', "gen_type = 'user_rank_data' ORDER BY gen_intdata ASC"))
			{
				$i=1;
				while($row = $sql->fetch())
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
			$ecache->set_sys('nomd5_user_ranks', e107::serialize($this->ranks, false));
		}

		if(empty($this->ranks))
		{
			$this->setDefaultRankData();
		}

		$this->fillMissingSpecialRanks();
	}

	/**
	 * @return void
	 */
	protected function setDefaultRankData()
	{
		$this->ranks = $this->defaultRankData();
	}

	/**
	 * @return void
	 */
	private function fillMissingSpecialRanks()
	{
		$special = varset($this->ranks['special'], array());

		if(isset($special[1], $special[2], $special[3]))
		{
			return;
		}

		$defaults = $this->defaultRankData();
		$this->ranks['special'] = $special + $defaults['special'];
	}

	/**
	 * @return array{data: array<int, array<string, mixed>>, special: array<int, array<string, mixed>>}
	 */
	private function defaultRankData()
	{
		e107::coreLan('userclass');

		$ranks = array('data' => array(), 'special' => array());

		foreach(array(20, 100, 250, 410, 580, 760, 950, 1150, 1370, 1371) as $index => $thresh)
		{
			$level = $index + 1;
			$ranks['data'][$level] = array('name' => '', 'thresh' => $thresh, 'image' => 'lev'.$level.'.png', 'lan_pfx' => '', 'id' => $level);
		}

		$specials = array(
			1 => array(UC_LAN_6, 'English_main_admin.png'),
			2 => array(UC_LAN_5, 'English_admin.png'),
			3 => array(UC_LAN_7, 'English_moderator.png'),
		);

		foreach($specials as $special => $rank)
		{
			$ranks['special'][$special] = array('name' => $rank[0], 'thresh' => $special, 'image' => $rank[1], 'lan_pfx' => '1', 'id' => $special + 10);
		}

		return $ranks;
	}


	/**
	 * @return array
	 */
	public function getRankData()
	{
		return $this->ranks;
	}

	/**
	 * @param $info
	 * @return string
	 */
	private function _getImage(&$info)
	{
		$img = $info['image'];
		if($info['lan_pfx'] && strpos('_', $info['image']))
		{
			$_tmp = explode('_', $info['image'], 2);
			$img = e_LANGUAGE.'_'.$_tmp[1];
		}
		return $this->imageFolder.$img;
	}


	/**
	 * @param $info
	 * @return mixed|string
	 */
	private function _getName(&$info)
	{
		if(!isset($info['name_parsed'])) $info['name_parsed'] = e107::getParser()->toHTML($info['name'], FALSE, 'defs');
		return $info['name_parsed'];
	}

	// TODO - custom ranks (e.g. forum moderator)

	/**
	 * @param $userId
	 * @param $moderator
	 * @return array|mixed
	 */
	function getRanks($userId, $moderator = false)
	{
		$e107 = e107::getInstance();
		if(!$userId && USER) { $userId = USERID; }
		if(isset($this->userRanks[$userId]))
		{
			return $this->userRanks[$userId];
		}

		$ret = array();
		if(is_array($userId))
		{
			$userData = $userId;
			$userId = $userData['user_id'];
		}
		else
		{
			$userData = e107::getSystemUser($userId)->getData(); //get_usXer_data($userId);
		}

		if(!empty($userData['user_admin']))
		{
			if($userData['user_perms'] == '0')
			{
				//Main Site Admin
				$data['special'] = "<img src='".$this->_getImage($this->ranks['special'][1])."' alt='".$this->_getName($this->ranks['special'][1])."' title='".$this->_getName($this->ranks['special'][1])."' />";
				$data['name'] = $this->_getName($this->ranks['special'][1]);
			}
			else
			{
				//Site Admin
				$data['special'] = "<img src='".$this->_getImage($this->ranks['special'][2])."' alt='".$this->_getName($this->ranks['special'][2])."' title='".$this->_getName($this->ranks['special'][2])."' />";
				$data['name'] = $this->_getName($this->ranks['special'][2]);
			}
		}
		elseif($moderator)
		{
			$data['special'] = "<img src='".$this->_getImage($this->ranks['special'][3])."' alt='".$this->_getName($this->ranks['special'][3])."' title='".$this->_getName($this->ranks['special'][3])."' />";
			$data['name'] = $this->_getName($this->ranks['special'][3]);
		}

		$userData['user_daysregged'] = max(1, round((time() - $userData['user_join']) / 86400));
		$level = $this->_calcLevel($userData);

		$lastRank = count($this->ranks['data']);
		$rank = false;
		if($level <= varset($this->ranks['data'][1]['thresh']))
		{
			$rank = 1;
		}
		elseif($level >= $this->ranks['data'][$lastRank]['thresh'])
		{
			$rank = $lastRank;
		}
		else
		{
			for($i=1; $i <= $lastRank; $i++)
			{
				if($level >= $this->ranks['data'][$i]['thresh'] && $level < $this->ranks['data'][($i+1)]['thresh'])
				{
					$rank = $i + 1;
					break;
				}
			}
		}
		if($rank !== false)
		{
			if(!isset($data['name']))
			{
				$data['name'] = $this->_getName($this->ranks['data'][$rank]);
			}
			$img_title = ($this->ranks['data'][$rank]['name'] ? " alt='{$data['name']}' title='{$data['name']}'" : ' alt = ""');
			$data['pic'] = "<img {$img_title} src='".$this->_getImage($this->ranks['data'][$rank])."'{$img_title} />";
		}

		$data['value'] = $rank;

		$this->userRanks[$userId] = $data;

		return $data;
	}

	// TODO - custom ranking by array key - e.g. user_comments only

	/**
	 * @param $userData
	 * @return float
	 */
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


