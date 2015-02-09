<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * $URL$
 * $Id$
 */


if (!defined('e107_INIT')) { exit; }

class e107_event
{
	var $functions = array();
	var $includes = array();

	/**
	 * Register event
	 * 
	 * @param string $eventname
	 * @param array|string $function [class_name, method_name] or function name
	 * @param string $include [optional] include path 
	 * @return void
	 */
	function register($eventname, $function, $include='')
	{
		$this->includes[$eventname] = array();
		if(!isset($this->functions[$eventname]) || !in_array($function, $this->functions[$eventname]))
		{
			if (!empty($include))
			{
				$this->includes[$eventname][] = $include;
			}
			$this->functions[$eventname][] = $function;
		}
	}



	function debug()
	{
		
		print_a($this->functions);
		print_a($this->includes);	
		
	}


	/**
	 * Trigger event
	 * TODO - admin log for failed callback attempts?
	 * 
	 * @param string $eventname
	 * @param mixed $data
	 * @return mixed
	 */
	function trigger($eventname, $data='')
	{
		/*if (isset($this->includes[$eventname]))
		{
			foreach($this->includes[$eventname] as $evt_inc)
			{
				if (file_exists($evt_inc))
				{
					include_once($evt_inc);
				}
			}
		}*/
		if (isset($this->functions[$eventname]))
		{
			foreach($this->functions[$eventname] as $i => $evt_func)
			{
				$location = '';
				if(isset($this->includes[$eventname][$i])) //no checks
				{
					$location = $this->includes[$eventname][$i];
					e107_include_once($location); 
					unset($this->includes[$eventname][$i]);
				}
				if(is_array($evt_func)) //class, method
				{
					$class = $evt_func[0];
					$method = $evt_func[1];
						
					try
					{
					
						$tmp = new $class($eventname);
						$ret = $tmp->{$method}($data, $eventname); //let callback know what event is calling it
						unset($tmp);
						if (!empty($ret))
						{
							break;
						}
					}
					catch(Exception $e)
					{
						e107::getLog()->add('Event Trigger failed',array('name'=>$eventname,'location'=>$location,'class'=>$class,'method'=>$method,'error'=>$e),E_LOG_WARNING,'EVENT_01'); 
						continue;
					}
				}
				elseif (function_exists($evt_func))
				{
					$ret = $evt_func($data, $eventname); //let callback know what event is calling it
					if (!empty($ret))
					{
						break;
					}
				}
				
				e107::getLog()->add('Event Trigger failed',array('name'=>$eventname,'location'=>$location,'function'=>$evt_func),E_LOG_WARNING,'EVENT_01'); 
				
			}
		}
		return (isset($ret) ? $ret : false);
	}




	/**
	 * @Deprecated
	 */
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
		$text = ''; 
		$e_event_list = e107::getPref('e_event_list');
		
		if(is_array($e_event_list))
		{
			foreach($e_event_list as $hook)
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
										$ret = $class->event_form($data);
										
										if(!isset($ret[0]))
										{
											$text[$hook][0] = $ret;		
										}
										else 
										{
											$text[$hook] = $ret;
										}
										
										
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