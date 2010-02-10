<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Tagwords shim for event calendar
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/e_tagwords.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

/**
 *	e107 Event calendar plugin
 *
 *	@package	e107_plugins
 *	@subpackage	event_calendar
 *	@version 	$Id$;
 */

if (!defined('e107_INIT')) { exit; }

class e_tagwords_calendar_menu
{
	function e_tagwords_calendar_menu()
	{
		$this->settings = array();

		$this->settings['plugin'] = 'calendar_menu';
		$this->settings['table'] = 'event'; 
		$this->settings['db_id'] = 'event_id';
		$this->settings['caption'] = 'calendar';
	}

	function getLink($id)
	{
		if($this->row=='')
		{
			if ($this->row = $this->getRecord($id))
			{
				$url = e_PLUGIN."calendar_menu/event.php?{$this->row['event_start']}.event.{$this->row['event_id']}";
				return "<a href='".$url."'>".e107::getParser()->toHTML($this->row['event_title'], TRUE, '')."</a>";
			}
		}
		return '';
	}


	function getRecord($id)
	{
		$sql = e107::getDb();

		$this->row = '';
		$qry = "SELECT * FROM #event as e WHERE e.event_id='{$id}'";
		
		if($sql->db_Select_gen($qry))
		{
			$this->row=$sql->db_Fetch();
			return $this->row;
		}
		return FALSE;
	}
}

?>