<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2014 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	Private messenger plugin - utility functions
 *
 */

use e107\Database\SqlFragment;

if (!defined('e107_INIT')) { exit; }

class pmbox_manager
{
	protected	$pmPrefs = array();
	protected 	$pmDB;

	public function __construct($prefs='')
	{
		$this->pmDB = e107::getDb();
		// $this->pmPrefs = $prefs;
		
		$this->pmPrefs = e107::pref('pm');
	}


	public function prefs()
	{
		return $this->pmPrefs;

	}


	/**
	 *	Get the box-related information for inbox or outbox - limits, message count etc
	 *	The information read from the DB is cached internally for efficiency
	 *
	 *	@param	string $which = inbox|outbox|clear
	 *
	 *	@return	array
	 *	
	 */
	function pm_getInfo($which = 'inbox')
	{
		static $pm_info;

		if('clear' == $which)
		{
			unset($pm_info['inbox']);
			unset($pm_info['outbox']);
			return;
		}

		if('inbox' == $which)
		{
			$infoQb = $this->pmDB->createQueryBuilder()
				->addSelect(SqlFragment::raw('count(pm.pm_id) AS total, SUM(pm.pm_size)/1024 size, SUM(pm.pm_read = 0) as unread'))
				->from('private_msg', 'pm')
				->where('pm.pm_to', USERID)
				->where('pm.pm_read_del', 0);
		}
		else
		{
			$infoQb = $this->pmDB->createQueryBuilder()
				->addSelect(SqlFragment::raw('count(pm.pm_from) AS total, SUM(pm.pm_size)/1024 size, SUM(pm.pm_read = 0) as unread'))
				->from('private_msg', 'pm')
				->where('pm.pm_from', USERID)
				->where('pm.pm_sent_del', 0);
		}

		if(!isset($pm_info[$which]['total']))
		{
			$pm_info[$which] = $infoQb->fetchRow();
			if ($which == 'inbox' && (!empty($this->pmPrefs['animate']) || !empty($this->pmPrefs['popup'])))
			{
				if($new = $this->pmDB->createQueryBuilder()->from('private_msg')
					->where('pm_sent', '>', USERLV)
					->where('pm_read', 0)
					->where('pm_to', USERID)
					->where('pm_read_del', '!=', 1)
					->count())
				{
					$pm_info['inbox']['new'] = $new;
				}
				else
				{
					$pm_info['inbox']['new'] = 0;
				}
			}
		}

		if(!isset($pm_info[$which]['limit']))
		{
			if(varset($this->pmPrefs['pm_limits'],0) > 0)
			{
				$limitQb = $this->pmDB->createQueryBuilder();
				if($this->pmPrefs['pm_limits'] == 1)
				{
					$limitQb->addSelect($limitQb->raw('MAX(gen_user_id) AS inbox_limit, MAX(gen_ip) as outbox_limit'));
				}
				else
				{
					$limitQb->addSelect($limitQb->raw('MAX(gen_intdata) AS inbox_limit, MAX(gen_chardata) as outbox_limit'));
				}
				$limitQb->from('generic')
					->where('gen_type', 'pm_limit')
					->whereIn('gen_datestamp', explode(',', USERCLASS_LIST));
				if($row = $limitQb->fetchRow())
				{
					$pm_info['inbox']['limit'] =  $row['inbox_limit'];
					$pm_info['outbox']['limit'] =  $row['outbox_limit'];
				}
				$pm_info['inbox']['limit_val'] = ($this->pmPrefs['pm_limits'] == 1 ? varset($pm_info['inbox']['total'],'') : varset($pm_info['inbox']['size'],''));
				if(!$pm_info['inbox']['limit'] || !$pm_info['inbox']['limit_val'])
				{
					$pm_info['inbox']['filled'] = 0;
				}
				else
				{
					$pm_info['inbox']['filled'] = number_format($pm_info['inbox']['limit_val']/$pm_info['inbox']['limit'] * 100, 2);
				}
				$pm_info['outbox']['limit_val'] = ($this->pmPrefs['pm_limits'] == 1 ? varset($pm_info['outbox']['total'],'') : varset($pm_info['outbox']['size'],''));
				if(!$pm_info['outbox']['limit'] || !$pm_info['outbox']['limit_val'])
				{
					$pm_info['outbox']['filled'] = 0;
				}
				else
				{
					$pm_info['outbox']['filled'] = number_format($pm_info['outbox']['limit_val']/$pm_info['outbox']['limit'] * 100, 2);
				}
			}
			else
			{
				if (!isset($pm_info['inbox']) || !is_array($pm_info['inbox'])) $pm_info['inbox'] = [];
				if (!isset($pm_info['outbox']) || !is_array($pm_info['outbox'])) $pm_info['outbox'] = [];

				$pm_info['inbox']['limit'] = '';
				$pm_info['outbox']['limit'] = '';
				$pm_info['inbox']['filled'] = '';
				$pm_info['outbox']['filled'] = '';
			}
		}
		return $pm_info;
	}

}

// Backward compat. fix.
function pm_getInfo($which = 'inbox')
{
	$pm = new pmbox_manager;

	return $pm->pm_getInfo($which);

}