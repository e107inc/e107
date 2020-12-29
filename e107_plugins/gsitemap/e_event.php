<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 * XXX HIGHLY EXPERIMENTAL AND SUBJECT TO CHANGE WITHOUT NOTICE. 
*/

if (!defined('e107_INIT')) { exit; }


class gsitemap_event // plugin-folder + '_event'
{

	/**
	 * Configure functions/methods to run when specific e107 events are triggered.
	 * For a list of events, please visit: http://e107.org/developer-manual/classes-and-methods#events
	 * Developers can trigger their own events using: e107::getEvent()->trigger('plugin_event',$array);
	 * Where 'plugin' is the folder of their plugin and 'event' is a unique name of the event.
	 * $array is data which is sent to the triggered function. eg. myfunction($array) in the example below.
	 *
	 * @return array
	 */
	function config()
	{

		$event = array();

		$event[] = array(
			'name'	=> "admin_ui_updated", /* when this is triggered... (@see http://e107.org/developer-manual/classes-and-methods#events) */
			'function'	=> "update", // ..run this function (see below).
		);

		return $event;

	}


	function update($data) // the method to run.
	{
	//	e107::getMessage()->addDebug("GSITEMAP TRIGGERRED!!") ;

		if(empty($data['plugin']) || empty($data['table']) || empty($data['newData']))
		{
			return null;
		}

		/** @var news_gsitemap $gsmap - could be another plugin too */
		if(!$gsmap = e107::getAddon($data['plugin'], 'e_gsitemap'))
		{
			return null;
		}

		if(!method_exists($gsmap, 'url'))
		{
			return null;
		}

		if(!$url = $gsmap->url($data['table'], $data['newData']))
		{
			return null;
		}

		$tp = e107::getParser();
		$id = (int) $data['id'];

		$update = array(
			'gsitemap_url' => $url,
			'WHERE' => "gsitemap_plugin = '".$tp->filter($data['plugin'])."' 
				AND gsitemap_table = '".$tp->filter($data['table'])."' 
				AND gsitemap_table_id = ".$id
		);

		// todo LAN
		$message = e107::getParser()->lanVars("Updating sitemap link for #[x] to [y]. ", array($id,$url), true);

		if(e107::getDb()->update('gsitemap', $update)!==false)
		{
			e107::getMessage()->addSuccess($message);
		}
		else
		{
			e107::getMessage()->addError($message);
		}

	}





} //end class

