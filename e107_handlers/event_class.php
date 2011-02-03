<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
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

class e107_event
{
	var $functions = array();
	var $includes = array();
	var $debug = FALSE;
	

	function e107_event()
	{
		// Fix for events triggered between plugins. 
		// Events will register regardinless of the load order.  
		
		if(varset($_SESSION['e_EVENT_functions']))
		{
			$this->functions = $_SESSION['e_EVENT_functions'];					
		}
		if(varset($_SESSION['e_EVENT_includes']))
		{
			$this->includes = $_SESSION['e_EVENT_includes'];					
		}			
	}
	
	/**
	 * Register an e107 Event
	 * @param object $eventname
	 * @param object $function
	 * @param object $include [optional]
	 * Include is required if the trigger is in a plugin that is loaded prior to the plugin that contains the function. 
	 * @return 
	 */
	function register($eventname, $function, $include='')
	{
		if(!$_SESSION) 
		{
			$this->logData("There is no _SESSION");
		}
				
		if(!isset($_SESSION['e_EVENT_functions'][$eventname])) // Notice removal
		{
			$_SESSION['e_EVENT_functions'][$eventname] = array();		
		}
		
		if(!isset($_SESSION['e_EVENT_includes'][$eventname])) // Notice removal
		{
			$_SESSION['e_EVENT_includes'][$eventname] = array();		
		}  
		
		if ($include!='')
		{
			$include = realpath($include); // make paths consistent to avoid duplicates
			// $this->includes[$eventname][] = $include;
			if(!in_array($include,$_SESSION['e_EVENT_includes'][$eventname]))
			{
				$_SESSION['e_EVENT_includes'][$eventname][] = $include;		
			}			
		}
		
		// $this->functions[$eventname][] = $function;
		if(!in_array($function,$_SESSION['e_EVENT_functions'][$eventname]))
		{
			$_SESSION['e_EVENT_functions'][$eventname][] = $function;
		}		
	}
	
	/**
	 * Trigger an e107 Event
	 * @param object $eventname
	 * @param object $data
	 * @return boolean
	 */
	function trigger($eventname, &$data)
	{
		if (isset($this -> includes[$eventname]))
		{
			foreach($this->includes[$eventname] as $evt_inc)
			{
				if (file_exists($evt_inc))
				{
					if(!include_once($evt_inc))
					{
						$this->logData("Couldn't Include file: ".$evt_inc);		
					}
					else
					{
						$this->logData("Included file: ".$evt_inc);		
					}
				}
				else
				{
					$this->logData("Couldn't find file: ".$evt_inc);	
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
					$this->logData($evt_func);
					if ($ret!='')
					{
						$this->logData("Stopped Processing during: ".$evt_func ."(".$ret.")");
						break;
					}
				}
			}
			
		}
		return (isset($ret) ? $ret : false);
	}
	
	/**
	 * Enable the event Log for debugging. 
	 * @return 
	 */
	function debug()
	{
		$this->debug = TRUE;
	}
	
	
	function logData($message)
	{
		if($this->debug == FALSE){ return; }
		if($fp = @fopen(e_HANDLER."event.log","a+"))
		{	
			$contents = @fwrite($fp, date('r')." :: ".$message ." \n");
			@fclose($fp);
		}			
	}
}
	
?>