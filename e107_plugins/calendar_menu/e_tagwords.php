<?php

if (!defined('e107_INIT')) { exit; }

class e_tagwords_calendar_menu
{
	function e_tagwords_calendar_menu()
	{
		$this->settings = array();

		$this->settings['plugin'] = "calendar_menu"; 
		$this->settings['table'] = "event"; 
		$this->settings['db_id'] = "event_id"; 
		$this->settings['caption'] = "calendar";	
	}

	function getLink($id)
	{
		global $tp;
		if($this->row=='')
		{
			$this->row = $this->getRecord($id);
		}
		$url = e_PLUGIN."calendar_menu/event.php?{$this->row['event_start']}.event.{$this->row['event_id']}";
		return "<a href='".$url."'>".$tp->toHTML($this->row['event_title'], TRUE, '')."</a>";
	}

	function getRecord($id)
	{
		global $sql;

		$this->row = '';
		
		$qry = "SELECT * FROM #event as e WHERE e.event_id='{$id}'";
		
		if($sql->db_Select_gen($qry))
		{
			$this->row=$sql->db_Fetch();
		}
		return $this->row;
	}
}

?>