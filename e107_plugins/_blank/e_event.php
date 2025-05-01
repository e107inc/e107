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


class _blank_event // plugin-folder + '_event'
{

	/**
	 * Configures and returns an array of events to be handled by the system.
	 * Each event in the array includes a unique name and the corresponding function to handle it.
	 *
	 * The events include:
	 * - Core events like "login".
	 * - Core plugin events like "user_forum_post_created".
	 * - Custom events from third-party plugins such as "customplugin_customevent".
	 * - Plugin-specific custom events like "_blank_customevent".
	 * - Class-based custom event handlers such as "_blankCustomEventClass::blankMethod".
	 *
	 * @return array Returns an array of event configurations, where each configuration includes
	 *               the event name and the associated function or class method to handle the event.
	 */
	function config()
	{

		$event = array();

		// Example 1: core event ("login")
		$event[] = array(
			'name'		=> "login", // when this event is triggered... (for core events, see http://e107.org/developer-manual/classes-and-methods#events)
			'function'	=> "myfunction", // ..run this function (see below). 
		);

		// Example 2: core plugin event ("user_forum_post_created")
		$event[] = array(
			'name'		=> "user_forum_post_created", // event triggered in the forum plugin when a user submits a new forum post 
			'function'	=> "myfunction", // ..run this function (see below). You can run the same function on different events. 
		);

		// Example 3: custom event of another third party plugin
		$event[] = array(
			'name'		=> "customplugin_customevent", // where "customplugin" is the plugin folder name of the third party plugin, and "customevent" is the event name that they triggered somehwere in their code (e107::getEvent()->trigger('customplugin_customevent', $data). 
			'function'	=> "anotherfunction", // ..run this function (see below).
		);

		// Example 4: custom event of the _blank plugin. 
		// Listen to _blank's own plugin event, this usually does not occur but is here for illustration purposes. 
		$event[] = array(
			'name'		=> "_blank_static_event", // "plugin_event" where 'plugin' is the plugin folder name (in this case "_blank") and "event" is a unique event name (in this case "customevent")
			'function'	=> "staticfunction", // ..run this function (see below).
		);

		// Example 5: Custom event of the _blank plugin with a separate class and static method.

		$event[] = array(
			'name'		=> "_blank_custom_class", // "plugin_event" where 'plugin' is the plugin folder name (in this case "_blank") and "event" is a unique event name (in this case "customevent")
			'function'	=> '_blankCustomEventClass::blankMethod', // ..run this function (see below).
		);
	
		return $event;
	}


	public function myfunction($data, $event) // the method to run.
	{
		// var_dump($data);
	}

	public function anotherfunction($data, $event) // the method to run.
	{
		// var_dump($data);
	}

	public static function staticfunction($data, $event) // the method to run.
	{
		return 'error in event: '.$event;
	}

} //end class


class _blankCustomEventClass
{

	public static function blankMethod($data, $event)
	{

		return "Blocking more triggers of: ".$event. " ".json_encode($data);;

	}




}