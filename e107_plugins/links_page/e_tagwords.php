<?php

if (!defined('e107_INIT')) { exit; }

class e_tagwords_links_page
{
	function e_tagwords_links_page()
	{
		$this->settings = array();

		$this->settings['plugin'] = "links_page";
		$this->settings['table'] = "links_page";
		$this->settings['db_id'] = "link_id";
		$this->settings['caption'] = "links page";
	}

	function getLink($id)
	{
		global $tp;
		if($this->row=='')
		{
			$this->row = $this->getRecord($id);
		}
		$url = e_PLUGIN."links_page/links.php?view.{$this->row['link_id']}";
		return "<a href='".$url."'>".$tp->toHTML($this->row['link_name'], TRUE, '')."</a>";
	}

	function getRecord($id)
	{
		global $sql;

		$this->row = '';
		
		$qry = "SELECT l.*
		FROM #links_page as l
		WHERE l.link_id='{$id}' AND l.link_class REGEXP '".e_CLASS_REGEXP."' ";
		
		if($sql->db_Select_gen($qry))
		{
			$this->row=$sql->db_Fetch();
		}
		return $this->row;
	}
}

?>