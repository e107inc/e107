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
|     $Source: /cvs_backup/e107_0.8/e107_handlers/event_class.php,v $
|     $Revision: 1.3 $
|     $Date: 2008-12-03 00:48:19 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
	
if (!defined('e107_INIT')) { exit; }

class e107_event
{
	var $functions = array();
	var $includes = array();
	 
	function register($eventname, $function, $include='')
	{
		if ($include!='')
		{
			$this->includes[$eventname][] = $include;
		}
		$this->functions[$eventname][] = $function;
	}
	 
	function trigger($eventname, &$data)
	{
		if (isset($this -> includes[$eventname]))
		{
			foreach($this->includes[$eventname] as $evt_inc)
			{
				if (file_exists($evt_inc))
				{
					include_once($evt_inc);
				}
			}
		}
		if (isset($this -> functions[$eventname]))
		{
			foreach($this->functions[$eventname] as $evt_func)
			{
				if (function_exists($evt_func))
				{
					$ret = $evt_func($data);
					if ($ret!='')
					{
						break;
					}
				}
			}
		}
		return (isset($ret) ? $ret : false);
	}
	function triggerAdminEvent($type, $parms=array())
	{
		global $pref;
		if(!is_array($parms))
		{
			$_tmp = parse_str($parms, $parms);
		}
		if(isset($pref['e_admin_events_list']) && is_array($pref['e_admin_events_list']))
		{
			foreach($pref['e_admin_events_list'] as $plugin)
			{
				if(plugInstalled($plugin))
				{
					$func = 'plugin_'.$plugin.'_admin_events';
					if(!function_exists($func))
					{
						$fname = e_PLUGIN.$plugin.'/e_admin_events.php';
						if(is_readable($fname))
						{
							include_once($fname);
						}
					}
					if(function_exists($func))
					{
						call_user_func($func, $type, $parms);
					}
				}
			} 
		}
	}
}
	
?>