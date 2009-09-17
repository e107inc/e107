<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     �Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_handlers/event_class.php,v $
|     $Revision: 1.7 $
|     $Date: 2009-09-17 00:13:39 $
|     $Author: e107coders $
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
			// $called = getcachedvars('admin_events_called');
			$called = e107::getRegistry('core/cachedvars/admin_events_called', false);
			if(!is_array($called)) { $called = array(); }
			foreach($pref['e_admin_events_list'] as $plugin)
			{
				if(e107::isInstalled($plugin))
				{
					$func = 'plugin_'.$plugin.'_admin_events';
					if(!function_exists($func))
					{
						$fname = e_PLUGIN.$plugin.'/e_admin_events.php';
						if(is_readable($fname)) { include_once($fname); }
					}
					if(function_exists($func))
					{
						$event_func = call_user_func($func, $type, $parms);
						if ($event_func && function_exists($event_func) && !in_array($event_func, $called))
						{
							$called[] = $event_func;
							// cachevars('admin_events_called', $called);
							e107::setRegistry('core/cachedvars/admin_events_called', $called);
							call_user_func($event_func);
						}
					}
				}
			}
		}
	}

	/*
	* triggerHook trigger a hooked in element
	*   four methods are allowed hooks: form, create, update, delete
	*   form : return array('caption'=>'', 'text'=>'');
	*   create, update, delete : return string message
	* @param array $data array containing
	* @param string $method form,insert,update,delete
	* @param string $table the table name of the calling plugin
	* @param int $id item id of the record
	* @param string $plugin identifier for the calling plugin
	* @param string $function identifier for the calling function
	* @return string $text string of rendered html, or message from db handler
	*/
	function triggerHook($data='')
	{
		global $pref;

		$text = '';
		if(isset($pref['e_event_list']) && is_array($pref['e_event_list']))
		{
			foreach($pref['e_event_list'] as $hook)
			{
				if(e107::isInstalled($hook))
				{
					if(is_readable(e_PLUGIN.$hook."/e_event.php"))
					{
						require_once(e_PLUGIN.$hook."/e_event.php");
						$name = "e_event_{$hook}";
						if(class_exists($name))
						{
							$class = new $name();
							
							switch($data['method'])
							{
								//returns array('caption'=>'', 'text'=>'');
								case 'form':
									if(method_exists($class, "event_{$data['method']}"))
									{
										$text[] = $class->event_form($data);
									}
									break;
								//returns string message
								case 'create':
								case 'update':
								case 'delete':
									if(method_exists($class, "event_{$data['method']}"))
									{
										$text .= call_user_func(array($class, "event_{$data['method']}"), $data);
									}
									break;
							}
						}
					}
				}
			}
		}
		return $text;
	}
}

?>