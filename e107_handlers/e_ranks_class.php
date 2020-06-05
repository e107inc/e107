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

class e_ranks
{
	public $ranks;
	private $userRanks;
	private $imageFolder;

	public function __construct($force = false)
	{
		$this->ranks = array();
		$this->userRanks = array();
		$this->imageFolder = is_dir(THEME.'images/ranks') ? THEME_ABS.'images/ranks/' : e_IMAGE_ABS.'ranks/';


		$e107 = e107::getInstance();
		$sql = e107::getDb();
		//Check to see if we can get it from cache
		if($force == false && ($ranks = $e107->ecache->retrieve_sys('nomd5_user_ranks')))
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
			$e107->ecache->set_sys('nomd5_user_ranks', $e107->arrayStorage->WriteArray($this->ranks, false));
		}

		// defaults
		if(empty($this->ranks))
		{
			$this->setDefaultRankData();
		}
	}

	protected function setDefaultRankData()
	{
		e107::coreLan('userclass');

			$this->ranks = array('data' => array(), 'special' => array());
			$this->ranks['data'][1] = array(
				'name' => '',
				'thresh' => 20,
				'image' => 'lev1.png',
				'lan_pfx' => '',
				'id' => 1,
			);
			$this->ranks['data'][2] = array(
				'name' => '',
				'thresh' => 100,
				'image' => 'lev2.png',
				'lan_pfx' => '',
				'id' => 2,
			);
			$this->ranks['data'][3] = array(
				'name' => '',
				'thresh' => 250,
				'image' => 'lev3.png',
				'lan_pfx' => '',
				'id' => 3,
			);
			$this->ranks['data'][4] = array(
				'name' => '',
				'thresh' => 410,
				'image' => 'lev4.png',
				'lan_pfx' => '',
				'id' => 4,
			);
			$this->ranks['data'][5] = array(
				'name' => '',
				'thresh' => 580,
				'image' => 'lev5.png',
				'lan_pfx' => '',
				'id' => 5,
			);
			$this->ranks['data'][6] = array(
				'name' => '',
				'thresh' => 760,
				'image' => 'lev6.png',
				'lan_pfx' => '',
				'id' => 6,
			);
			$this->ranks['data'][7] = array(
				'name' => '',
				'thresh' => 950,
				'image' => 'lev7.png',
				'lan_pfx' => '',
				'id' => 7,
			);
			$this->ranks['data'][8] = array(
				'name' => '',
				'thresh' => 1150,
				'image' => 'lev8.png',
				'lan_pfx' => '',
				'id' => 8,
			);
			$this->ranks['data'][9] = array(
				'name' => '',
				'thresh' => 1370,
				'image' => 'lev9.png',
				'lan_pfx' => '',
				'id' => 9,
			);
			$this->ranks['data'][10] = array(
				'name' => '',
				'thresh' => 1371,
				'image' => 'lev10.png',
				'lan_pfx' => '',
				'id' => 10,
			);
			// special
			$this->ranks['special'][1] = array(
				'name' => UC_LAN_6,
				'thresh' => 1,
				'image' => 'English_main_admin.png',
				'lan_pfx' => '1',
				'id' => 11,
			);
			$this->ranks['special'][2] = array(
				'name' => UC_LAN_5,
				'thresh' => 2,
				'image' => 'English_admin.png',
				'lan_pfx' => '1',
				'id' => 12,
			);
			$this->ranks['special'][3] = array(
				'name' => UC_LAN_7,
				'thresh' => 3,
				'image' => 'English_moderator.png',
				'lan_pfx' => '1',
				'id' => 13,
			);
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
		return $this->imageFolder.$img;
	}


	private function _getName(&$info)
	{
		if(!isset($info['name_parsed'])) $info['name_parsed'] = e107::getParser()->toHTML($info['name'], FALSE, 'defs');
		return $info['name_parsed'];
	}

	// TODO - custom ranks (e.g. forum moderator)
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

		if($userData['user_admin'])
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
		if($level <= $this->ranks['data'][0]['thresh'])
		{
			$rank = 1;
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


